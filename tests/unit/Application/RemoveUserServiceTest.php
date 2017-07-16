<?php

namespace Application;

use Adapters\EventsRepositoryInterface;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\ValueObjects\Event;
use PHPUnit\Framework\TestCase;

class RemoveUserServiceTest extends TestCase
{
	public function test_removeUser()
	{
		$userId = 123;
		$projectId = 456;

		$projectUserRepository = $this->getMockBuilder(ProjectsUsersRepositoryInterface::class)
			->setMethods(['addUser', 'removeUser', 'getUsersByProjectId', 'getProjectsByUserId', 'checkUserAccess'])
			->getMock();
		$projectUserRepository->method('removeUser')->willReturnCallback(
			function($userIdFromArgument, $projectIdFromArgument) use ($userId, $projectId)
			{
				$this->assertEquals($userId, $userIdFromArgument);
				$this->assertEquals($projectId, $projectIdFromArgument);
			}
		);

		$eventRepository = $this->getMockBuilder(EventsRepositoryInterface::class)
			->setMethods(['push'])
			->getMock();
		$eventRepository->method('push')->willReturnCallback(
			function(Event $event) use ($userId, $projectId)
			{
				$this->assertEquals('RemoveUserFromProject', $event->name());
				$this->assertEquals([
					'userId' => $userId,
					'projectId' => $projectId,
				], $event->data());
			}
		);

		$removeUserService = new RemoveUserService(
			$projectUserRepository,
			$eventRepository
		);
		$removeUserService->removeUser($userId, $projectId);
	}
}
