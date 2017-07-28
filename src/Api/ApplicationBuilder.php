<?php

namespace Api;

use Adapters\EventsRepositoryInterface;
use Adapters\MysqlProjectsUsersRepository;
use Adapters\ProjectsUsersRepositoryInterface;
use Adapters\RabbitMqEventsRepository;
use Api\RequestValidators\AddUserRequestValidator;
use Api\RequestValidators\HasUserAccessToProjectRequestValidator;
use Api\RequestValidators\RemoveUserRequestValidator;
use Application\AddUserService;
use Application\RemoveUserService;
use mysqli;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ApplicationBuilder
{
	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return App
	 */
	public function build(ApplicationConfig $applicationConfig) : App
	{
		$mysqli = $this->getMysqli($applicationConfig);
		$amqp = $this->getAmqpStreamConnection($applicationConfig);

		$projectsUsersRepository = new MysqlProjectsUsersRepository($mysqli);
		$eventsRepository = $this->buildEventRepository($applicationConfig);

		$app = new App(new Container(
			[
				'settings' => [
					'displayErrorDetails' => true,
				],
			]
		));

		$addUserEndpoint = new AddUserEndpoint(
			new AddUserService(
				$projectsUsersRepository,
				$eventsRepository
			),
			new AddUserRequestValidator()
		);

		$removeUserEndpoint = new RemoveUserEndpoint(
			new RemoveUserService(
				$projectsUsersRepository,
				$eventsRepository
			),
			new RemoveUserRequestValidator()
		);

		$hasUserAccessToProjectEndpoint = new HasUserAccessToProjectEndpoint(
			new HasUserAccessToProjectRequestValidator(),
			$projectsUsersRepository
		);

		$getProjectsByUserEndpoint = new GetProjectsByUserEndpoint(
			$projectsUsersRepository
		);

		$getUsersByProjectEndpoint = new GetUsersByProjectEndpoint(
			$projectsUsersRepository
		);

		$app->get('/users/hasAccess', function (Request $request, Response $response, $args) use ($hasUserAccessToProjectEndpoint) {
			return $hasUserAccessToProjectEndpoint->run($request, $response);
		});

		$app->put('/users', function (Request $request, Response $response, $args) use ($addUserEndpoint) {
			return $addUserEndpoint->run($request, $response);
		});

		$app->get('/users', function (Request $request, Response $response, $args) use ($getUsersByProjectEndpoint) {
			return $getUsersByProjectEndpoint->run($request, $response);
		});

		$app->get('/projects', function (Request $request, Response $response, $args) use ($getProjectsByUserEndpoint) {
			return $getProjectsByUserEndpoint->run($request, $response);
		});

		$app->delete('/users', function (Request $request, Response $response, $args) use ($removeUserEndpoint) {
			return $removeUserEndpoint->run($request, $response);
		});

		$app->get('/', function (Request $request, Response $response, $args) use ($applicationConfig, $mysqli, $amqp) {
			return $response->withJson(
				[
					'type' => 'projects-service',
					'config' => $applicationConfig->getArray(),
					'status' => [
						'is_connected'=> [
							'MySQL' => $mysqli->ping(),
							'RabbitMQ' => $amqp->isConnected(),
						],
					],
				]
			);
		});

		return $app;
	}

	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return mysqli
	 */
	private function getMysqli(ApplicationConfig $applicationConfig) : mysqli
	{
		return new mysqli(
			$applicationConfig->getMysqlHost(),
			$applicationConfig->getMysqlUser(),
			$applicationConfig->getMysqlPassword(),
			$applicationConfig->getMysqlDatabase(),
			$applicationConfig->getMysqlPort()
		);
	}

	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return EventsRepositoryInterface
	 */
	private function buildEventRepository(ApplicationConfig $applicationConfig) : EventsRepositoryInterface
	{
		$connection = $this->getAmqpStreamConnection($applicationConfig);
		$channel = $connection->channel();
		$channel->queue_declare(
			$applicationConfig->getRabbitmqChannel(),
			false,
			false,
			false,
			false
		);

		return new RabbitMqEventsRepository(
			$channel,
			$applicationConfig->getRabbitmqChannel()
		);
	}

	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return AMQPStreamConnection
	 */
	private function getAmqpStreamConnection(ApplicationConfig $applicationConfig) : AMQPStreamConnection
	{
		return new AMQPStreamConnection(
			$applicationConfig->getRabbitmqHost(),
			$applicationConfig->getRabbitmqPort(),
			$applicationConfig->getRabbitmqUser(),
			$applicationConfig->getRabbitmqPassword()
		);
	}
}
