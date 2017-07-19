<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;

class EndToEnd extends TestCase
{
	/**
	 * @var mysqli
	 */
	private $mysqli;

	/**
	 * @var AMQPStreamConnection
	 */
	private $connection;

	/**
	 * @var AMQPChannel
	 */
	private $channel;

	/**
	 * @var string
	 */
	const QUEUE_NAME = 'events';

	public function setUp()
	{
		$this->mysqli = new mysqli('127.0.0.1', 'root', 'root', 'testdb', 3306);
		$this->mysqli->query('CREATE TABLE projects_users (project_id INT NOT NULL,user_id INT NOT NULL);');
		$this->mysqli->query('CREATE UNIQUE INDEX projects_users_unique_index ON projects_users (project_id, user_id);');

		$this->connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
		$this->channel = $this->connection->channel();
		$this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
	}

	public function tearDown()
	{
		$this->mysqli->query('DROP TABLE projects_users;');
		$this->mysqli->close();

		$this->channel->close();
		$this->connection->close();
	}

	public function test()
	{
		$this->assertTrue(true);
	}
}
