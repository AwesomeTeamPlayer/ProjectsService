<?php

namespace Adapters;

use Domain\ValueObjects\Event;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;

class RabbitMqEventsRepositoryTest extends TestCase
{
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
		$this->connection = new AMQPStreamConnection('127.0.0.1', 15672, 'guest', 'guest');
		$this->channel = $this->connection->channel();
		$this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
	}

	public function tearDown()
	{
		$this->channel->close();
		$this->connection->close();
	}

	public function test_push()
	{
		$date = new \DateTime();
		$date->setTimestamp(1500479226);

		$repository = new RabbitMqEventsRepository($this->channel, self::QUEUE_NAME);
		$repository->push(new Event(
			'SuperName',
			$date,
			[
				'a' => 'b',
				'c' => [
					'd', 'e'
				],
			]
		));

		$message = $this->channel->basic_get(self::QUEUE_NAME, true);
		$this->assertNotNull($message);
		$this->assertEquals(
			'{"name":"SuperName","occuredAt":"2017-07-19T15:47:06+00:00","data":{"a":"b","c":["d","e"]}}',
			$message->getBody()
		);
	}
}
