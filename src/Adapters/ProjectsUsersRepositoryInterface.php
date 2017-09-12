<?php

namespace Adapters;

use Adapters\Exceptions\DuplicateProjectUserPairException;
use Adapters\Exceptions\ProjectUserPairDoesNotExistException;

interface ProjectsUsersRepositoryInterface
{
	/**
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return void
	 *
	 * @throws DuplicateProjectUserPairException
	 */
	public function addUser(string $userId, string $projectId);

	/**
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return void
	 *
	 * @throws ProjectUserPairDoesNotExistException
	 */
	public function removeUser(string $userId, string $projectId);

	/**
	 * @param string $projectId
	 *
	 * @return int
	 */
	public function countUsers(string $projectId): int;

	/**
	 * @param string $projectId
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return string[]
	 */
	public function getOrderedUsersByProjectId(string $projectId, int $offset, int $limit): array;

	/**
	 * @param int $userId
	 *
	 * @return int[]
	 */
	public function getProjectsByUserId(int $userId) : array;

	/**
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return bool
	 */
	public function checkUserAccess(string $userId, string $projectId) : bool;
}
