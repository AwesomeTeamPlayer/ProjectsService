<?php

namespace Api;

use Adapters\EventsRepositoryInterface;
use Adapters\MysqlProjectsUsersRepository;
use Adapters\ProjectsUsersRepositoryInterface;
use Adapters\RabbitMqEventsRepository;
use Api\RequestValidators\AddUserRequestValidator;
use Api\RequestValidators\HasUserAccessToProjectRequestValidator;
use Application\AddUserService;
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
		$projectsUsersRepository = $this->buildProjectsUsersRepository($applicationConfig);
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

		$hasUserAccessToProjectEndpoint = new HasUserAccessToProjectEndpoint(
			new HasUserAccessToProjectRequestValidator(),
			$projectsUsersRepository
		);

		$app->get('/users/hasAccess', function (Request $request, Response $response, $args) use ($hasUserAccessToProjectEndpoint) {
			return $hasUserAccessToProjectEndpoint->run($request, $response);
		});

		$app->put('/users', function (Request $request, Response $response, $args) use ($addUserEndpoint) {
			return $addUserEndpoint->run($request, $response);
		});

		$app->get('/', function (Request $request, Response $response, $args) use ($applicationConfig) {
			return $response->withJson(
				[
					'type' => 'projects-service',
					'config' => $applicationConfig->getArray()
				]
			);
		});

		return $app;
	}

	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return ProjectsUsersRepositoryInterface
	 */
	private function buildProjectsUsersRepository(ApplicationConfig $applicationConfig): ProjectsUsersRepositoryInterface
	{
		return new MysqlProjectsUsersRepository(
			new mysqli(
				$applicationConfig->getMysqlHost(),
				$applicationConfig->getMysqlUser(),
				$applicationConfig->getMysqlPassword(),
				$applicationConfig->getMysqlDatabase(),
				$applicationConfig->getMysqlPort()
			)
		);
	}

	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return EventsRepositoryInterface
	 */
	private function buildEventRepository(ApplicationConfig $applicationConfig) : EventsRepositoryInterface
	{
		$connection = new AMQPStreamConnection(
			$applicationConfig->getRabbitmqHost(),
			$applicationConfig->getRabbitmqPort(),
			$applicationConfig->getRabbitmqUser(),
			$applicationConfig->getRabbitmqPassword()
		);
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
}
