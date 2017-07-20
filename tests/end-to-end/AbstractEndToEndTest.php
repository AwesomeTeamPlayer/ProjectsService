<?php

use Api\ApplicationBuilder;
use Api\ApplicationConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

abstract class AbstractEndToEndTest extends TestCase
{
	/**
	 * @var string
	 */
	const QUEUE_NAME = 'events';

	/**
	 * @var mysqli
	 */
	protected $mysqli;

	/**
	 * @var AMQPStreamConnection
	 */
	protected $connection;

	/**
	 * @var AMQPChannel
	 */
	protected $channel;

	/**
	 * @var App
	 */
	protected $app;

	public function setUp()
	{
		$this->mysqli = new mysqli('127.0.0.1', 'root', 'root', 'testdb', 3306);
		$this->mysqli->query('CREATE TABLE projects_users (project_id INT NOT NULL,user_id INT NOT NULL);');
		$this->mysqli->query('CREATE UNIQUE INDEX projects_users_unique_index ON projects_users (project_id, user_id);');

		$this->connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
		$this->channel = $this->connection->channel();
		$this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);

		$applicationBuilder = new ApplicationBuilder();
		$applicationConfig = new ApplicationConfig(
			[
				'rabbitmq' => [
					'host' => '127.0.0.1',
					'port' => 5672,
					'user' => 'guest',
					'password' => 'guest',
					'channel' => 'events',
				],
				'mysql' => [
					'host' => '127.0.0.1',
					'port' => 3306,
					'user' => 'root',
					'password' => 'root',
					'database' => 'testdb',
				],
			]
		);
		$this->app = $applicationBuilder->build($applicationConfig);
	}

	public function tearDown()
	{
		$this->mysqli->query('DROP TABLE projects_users;');
		$this->mysqli->close();

		sleep(1);
		$this->clearQueue();
		$this->channel->close();
		$this->connection->close();
	}

	private function clearQueue()
	{
		do {
			$message = $this->channel->basic_get(self::QUEUE_NAME, true);
		} while ($message !== null);
	}

	protected function makeRequest($method, $path, $bodyContent = '')
	{
		$options = array(
			'REQUEST_METHOD' => $method,
			'REQUEST_URI'    => $path
		);

		$env = Environment::mock(array_merge($options, []));
		$uri = Uri::createFromEnvironment($env);
		$headers = Headers::createFromEnvironment($env);
		$cookies = [];
		$serverParams = $env->all();
		$body = new RequestBody();

		if ($bodyContent !== '') {
			$body->write($bodyContent);
		}

		$request  = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
		$response = new Response();

		ob_start();
		$response = $this->app->process($request, $response);
		ob_end_flush();
		return (string) $response->getBody();
	}
}
