<?php

namespace Adapters;

use Domain\ValueObjects\Event;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqEventsRepository implements EventsRepositoryInterface
{
	/**
	 * @var AMQPChannel
	 */
	private $channel;

	/**
	 * @param AMQPChannel $AMQPChannel
	 */
	public function __construct(AMQPChannel $channel)
	{
		$this->channel = $channel;
	}

	/**
	 * @param Event $event
	 *
	 * @return void
	 */
	public function push(Event $event)
	{
		$message = new AMQPMessage($event->toJson());
		$this->channel->basic_publish($message);
	}
}
