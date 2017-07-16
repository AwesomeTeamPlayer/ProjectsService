<?php

namespace Application;

use Adapters\EventsRepositoryInterface;
use Adapters\Exceptions\DuplicateProjectUserPairException;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\ValueObjects\Event;
use PHPUnit\Framework\TestCase;

class AddUserServiceTest extends TestCase
{
	public function test_addUser_when_user_is_created()
	{
		$userId = 123;
		$projectId = 456;

		$projectUserRepository = $this->getMockBuilder(ProjectsUsersRepositoryInterface::class)
			->setMethods(['addUser', 'removeUser', 'getUsersByProjectId', 'getProjectsByUserId', 'checkUserAccess'])
			->getMock();
		$projectUserRepository->method('addUser')->willReturnCallback(
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
				$this->assertEquals('AddedUserToProject', $event->name());
				$this->assertEquals([
					'userId' => $userId,
					'projectId' => $projectId,
				], $event->data());
			}
		);

		$addUserService = new AddUserService(
			$projectUserRepository,
			$eventRepository
		);
		$addingUserResult = $addUserService->addUser($userId, $projectId);
		$this->assertTrue($addingUserResult);
	}

	public function test_addUser_when_user_is_not_created()
	{
		$userId = 123;
		$projectId = 456;

		$projectUserRepository = $this->getMockBuilder(ProjectsUsersRepositoryInterface::class)
			->setMethods(['addUser', 'removeUser', 'getUsersByProjectId', 'getProjectsByUserId', 'checkUserAccess'])
			->getMock();
		$projectUserRepository->method('addUser')->willThrowException(new DuplicateProjectUserPairException);

		$eventRepository = $this->getMockBuilder(EventsRepositoryInterface::class)
			->setMethods(['push'])
			->getMock();

		$addUserService = new AddUserService(
			$projectUserRepository,
			$eventRepository
		);
		$addingUserResult = $addUserService->addUser($userId, $projectId);
		$this->assertFalse($addingUserResult);
	}
}
