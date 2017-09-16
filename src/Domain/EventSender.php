<?php

namespace Domain;

use Carbon\Carbon;
use Domain\ValueObjects\Project;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

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

	public function sendProjectCreatedEvent(Project $project)
	{
		$name = 'project.created';
		$event = $this->buildEvent($name, [
			"projectId" => $project->getId(),
		    "name" => $project->getName(),
		    "type" => $project->getType(),
		    "createdAt" => $project->getCreatedAt()->toIso8601String(),
		]);

		$this->publishEvent($name, $event);
	}

	public function sendProjectUpdatedEvent(Project $project)
	{
		$event = $this->buildEvent('project.updated', [
			"projectId" => $project->getId(),
			"name" => $project->getName(),
			"type" => $project->getType(),
		]);
	}

	public function sendUserToProjectAddedEvent(string $projectId, string $userId)
	{
		$name = 'project.user.added';
		$event = $this->buildEvent($name, [
			"projectId" => $projectId,
			"userId" => $userId,
		]);

		$this->publishEvent($name, $event);
	}

	public function sendUserFromProjectRemovedEvent(string $projectId, string $userId)
	{
		$name = 'project.user.removed';
		$event = $this->buildEvent($name, [
			"projectId" => $projectId,
			"userId" => $userId,
		]);

		$this->publishEvent($name, $event);
	}

	public function sendProjectArchivedEvent(string $projectId)
	{
		$event = $this->buildEvent('project.archived', [
			"projectId" => $projectId,
		]);
	}

	public function sendProjectUnarchivedEvent(string $projectId)
	{
		$event = $this->buildEvent('project.unarchived', [
			"projectId" => $projectId,
		]);
	}

	private function buildEvent(string $name, array $data)
	{
		return [
			'name' => $name,
			'occurredAt' => Carbon::now()->toIso8601String(),
			'data' => $data,
		];
	}

	private function publishEvent(string $routingKey, array $event)
	{
		$message = new AMQPMessage(json_encode($event));
		$this->channel->basic_publish($message, $this->exchangeName, $routingKey);
	}
}
