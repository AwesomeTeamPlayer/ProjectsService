<?php

namespace Domain;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class EventSender
{
	/**
	 * @var AMQPChannel
	 */
	private $channel;

	/**
	 * @var string
	 */
	private $exchangeName;

	public function __construct(AMQPChannel $channel, string $exchangeName)
	{
		$this->channel = $channel;
		$this->exchangeName = $exchangeName;
	}

	public function sendProjectCreatedEvent(string $projectId)
	{
		$this->publishEvent(
			'project.created',
			[
				'projectId' => $projectId
			]
		);
	}

	public function sendProjectNameUpdatedEvent(string $projectId)
	{
		$this->publishEvent(
			'project.name.updated',
			[
				'projectId' => $projectId
			]
		);
	}

	public function sendProjectTypeUpdatedEvent(string $projectId)
	{
		$this->publishEvent(
			'project.type.updated',
			[
				'projectId' => $projectId
			]
		);
	}

	public function sendUserToProjectAddedEvent(string $projectId, string $userId)
	{
		$this->publishEvent(
			'project.user.added',
			[
				'projectId' => $projectId,
				'userId' => $userId,
			]
		);
	}

	public function sendUserFromProjectRemovedEvent(string $projectId, string $userId)
	{
		$this->publishEvent(
			'project.user.removed',
			[
				'projectId' => $projectId,
				'userId' => $userId,
			]
		);
	}

	public function sendProjectArchivedEvent(string $projectId)
	{
		$this->publishEvent(
			'project.archived',
			[
				'projectId' => $projectId,
			]
		);
	}

	public function sendProjectUnarchivedEvent(string $projectId)
	{
		$this->publishEvent(
			'project.unarchived',
			[
				'projectId' => $projectId,
			]
		);
	}

	private function publishEvent(string $routingKey, array $event)
	{
		$headers = new AMQPTable([]);
		$headers->set('occurred-at', time());

		$message = new AMQPMessage(json_encode($event));
		$message->set('application_headers', $headers);

		$this->channel->basic_publish($message, $this->exchangeName, $routingKey);
	}
}
