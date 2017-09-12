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
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 *
	 * @throws ProjectUserPairDoesNotExistException
	 */
	public function removeUser(int $userId, int $projectId);

	/**
	 * @param string $projectId
	 *
	 * @return string[]
	 */
	public function getUsersByProjectId(string $projectId): array;

	/**
	 * @param int $userId
	 *
	 * @return int[]
	 */
	public function getProjectsByUserId(int $userId) : array;

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return bool
	 */
	public function checkUserAccess(int $userId, int $projectId) : bool;
}
