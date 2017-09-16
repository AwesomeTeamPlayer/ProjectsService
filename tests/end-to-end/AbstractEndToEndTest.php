<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;

abstract class AbstractEndToEndTest extends TestCase
{
	/**
	 * @var string
	 */
	const QUEUE_NAME = 'to_source_listener';

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

	protected function getMessage()
	{
		return $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
	}

	protected function checkMessage($message, $routingKey, $requiredKeysInJson)
	{
		$this->assertEquals($routingKey, $message->delivery_info['routing_key']);
		$this->assertGreaterThan(0, $message->get('application_headers')->getNativeData()['occurred-at']);
		$this->assertEquals($requiredKeysInJson, array_keys(json_decode($message->getBody(), true)));
	}
}
