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

	public function setUp()
	{
		$this->mysqli = new mysqli('127.0.0.1', 'root', 'root', 'testdb', 13306);
		$this->mysqli->query('CREATE TABLE projects (
		    id VARCHAR(10) NOT NULL,
		    name VARCHAR(100) NOT NULL,
		    type INT NOT NULL,
		    is_archived BOOL NOT NULL DEFAULT false,
		    created_at DATETIME NOT NULL,
		    PRIMARY KEY (id)
		);');

		$this->mysqli->query('CREATE TABLE projects_users (
		    project_id VARCHAR(10) NOT NULL,
		    user_id VARCHAR(10) NOT NULL
		);');
		$this->mysqli->query('CREATE UNIQUE INDEX projects_users_unique_index ON projects_users (project_id, user_id);');

		$this->connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
		$this->channel = $this->connection->channel();
		$this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
	}

	public function tearDown()
	{
		$this->mysqli->query('DROP TABLE projects;');
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
}
