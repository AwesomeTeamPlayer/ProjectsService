<?php

use Api\ApplicationBuilder;
use Api\ApplicationConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Slim\App;

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
				'redis' => [
					'host' => '127.0.0.1',
					'port' => 5672,
					'login' => 'guest',
					'password' => 'guest',
					'channel' => 'events',
				],
				'mysql' => [
					'host' => '127.0.0.1',
					'port' => 3306,
					'login' => 'root',
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

		$this->channel->close();
		$this->connection->close();
	}
}
