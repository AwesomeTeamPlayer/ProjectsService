<?php

namespace Application;

use Adapters\EventsRepositoryInterface;
use Adapters\Exceptions\DuplicateProjectUserPairException;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\ValueObjects\Event;

class AddUserService
{
	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	/**
	 * @var EventsRepositoryInterface
	 */
	private $eventsRepository;

	/**
	 * @param ProjectsUsersRepositoryInterface $projectsUsersRepository
	 * @param EventsRepositoryInterface $EventsRepository
	 */
	public function __construct(
		ProjectsUsersRepositoryInterface $projectsUsersRepository,
		EventsRepositoryInterface $EventsRepository
	)
	{
		$this->projectsUsersRepository = $projectsUsersRepository;
		$this->eventsRepository = $EventsRepository;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 */
	public function addUser(int $userId, int $projectId)
	{
		if ($this->tryAddUser($userId, $projectId)) {
			$this->eventsRepository->push(
				new Event(
					'AddedUserToProject',
					new \DateTime(),
					[
						'userId' => $userId,
						'projectId' => $projectId,
					]
				)
			);
		}
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return bool
	 */
	private function tryAddUser(int $userId, int $projectId) : bool
	{
		try {
			$this->projectsUsersRepository->addUser($userId, $projectId);
		} catch (DuplicateProjectUserPairException $exception) {
			return false;
		}
		return true;
	}

}
