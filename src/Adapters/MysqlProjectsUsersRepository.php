<?php

namespace Adapters;

use Adapters\Exceptions\DuplicateProjectUserPairException;
use Adapters\Exceptions\ProjectUserPairDoesNotExistException;
use mysqli;

class MysqlProjectsUsersRepository implements ProjectsUsersRepositoryInterface
{
	/**
	 * @var mysqli
	 */
	private $dbConnection;

	/**
	 * @param mysqli $dbConnection
	 */
	public function __construct(mysqli $dbConnection)
	{
		$this->dbConnection = $dbConnection;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 *
	 * @throws DuplicateProjectUserPairException
	 */
	public function addUser(int $userId, int $projectId)
	{
		$sqlQuery = "
			INSERT INTO projects_users (project_id, user_id) VALUES ('" . $projectId . "', '" . $userId . "');
		";

		if ($this->dbConnection->query($sqlQuery) === false) {
			throw new DuplicateProjectUserPairException();
		}
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return void
	 *
	 * @throws ProjectUserPairDoesNotExistException
	 */
	public function removeUser(int $userId, int $projectId)
	{
		if ($this->checkUserAccess($userId, $projectId) === false){
			throw new ProjectUserPairDoesNotExistException();
		}

		$sqlQuery = "
			DELETE FROM projects_users WHERE project_id = '" . $projectId . "' AND user_id = '" . $userId . "' LIMIT 1;
		";

		$this->dbConnection->query($sqlQuery);
	}

	/**
	 * @param int $projectId
	 *
	 * @return int[]
	 */
	public function getUsersByProjectId(int $projectId): array
	{
		$sqlQuery = "
			SELECT user_id FROM projects_users WHERE project_id = '" . $projectId . "';
		";

		$usersIds = [];
		$results = $this->dbConnection->query($sqlQuery);
		foreach ($results as $result){
			$usersIds[] = $result['user_id'];
		}

		return $usersIds;
	}

	/**
	 * @param int $userId
	 *
	 * @return int[]
	 */
	public function getProjectsByUserId(int $userId): array
	{
		$sqlQuery = "
			SELECT project_id FROM projects_users WHERE user_id = '" . $userId. "';
		";

		$projectsIds = [];
		$results = $this->dbConnection->query($sqlQuery);
		foreach ($results as $result){
			$projectsIds[] = $result['project_id'];
		}

		return $projectsIds;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 *
	 * @return bool
	 */
	public function checkUserAccess(int $userId, int $projectId): bool
	{
		$sqlQuery = "
			SELECT * FROM projects_users WHERE project_id = '" . $projectId . "' AND user_id = '" . $userId . "' LIMIT 1;
		";

		$result = $this->dbConnection->query($sqlQuery);
		return $result->num_rows === 1;
	}
}
