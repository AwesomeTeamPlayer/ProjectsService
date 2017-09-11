<?php

namespace Domain;

use Carbon\Carbon;
use Domain\ValueObjects\Project;

class EventSender
{
	public function sendProjectCreatedEvent(Project $project)
	{
		$event = $this->buildEvent('project.created', [
			"projectId" => $project->getId(),
		    "name" => $project->getName(),
		    "type" => $project->getType(),
		    "createdAt" => $project->getCreatedAt()->toIso8601String(),
		]);
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
		$event = $this->buildEvent('project.user.added', [
			"projectId" => $projectId,
			"userId" => $userId,
		]);
	}

	public function sendUserFromProjectRemovedEvent(string $projectId, string $userId)
	{
		$event = $this->buildEvent('project.user.removed', [
			"projectId" => $projectId,
			"userId" => $userId,
		]);
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
}
