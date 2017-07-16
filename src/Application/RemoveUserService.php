<?php

namespace Application;

use Adapters\EventsRepositoryInterface;
use Adapters\Exceptions\ProjectUserPairDoesNotExistException;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\ValueObjects\Event;

class RemoveUserService
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
	 * @param EventsRepositoryInterface $eventsRepository
	 */
	public function __construct(
		ProjectsUsersRepositoryInterface $projectsUsersRepository,
		EventsRepositoryInterface $eventsRepository
	)
	{
		$this->projectsUsersRepository = $projectsUsersRepository;
		$this->eventsRepository = $eventsRepository;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 */
	public function removeUser(int $userId, int $projectId)
	{
		if ($this->tryRemoveUser($userId, $projectId)) {
			$this->eventsRepository->push(
				new Event(
					'RemoveUserFromProject',
					new \DateTime(),
					[
						'userId' => $userId,
						'projectId' => $projectId,
					]
				)
			);

			return true;
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return bool
	 */
	private function tryRemoveUser(int $userId, int $projectId) : bool
	{
		try {
			$this->projectsUsersRepository->removeUser($userId, $projectId);
		} catch (ProjectUserPairDoesNotExistException $exception) {
			return false;
		}
		return true;
	}

}
