<?php

namespace Adapters;

interface ProjectsUsersRepositoryInterface
{
	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 */
	public function addUser(int $userId, int $projectId);

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 */
	public function removeUser(int $userId, int $projectId);

	/**
	 * @param int $projectId
	 *
	 * @return int[]
	 */
	public function getUsersByProjectId(int $projectId) : array;

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
