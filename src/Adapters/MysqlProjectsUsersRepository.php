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
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return void
	 *
	 * @throws DuplicateProjectUserPairException
	 */
	public function addUser(string $userId, string $projectId)
	{
		$sqlQuery = "
			INSERT INTO projects_users (project_id, user_id) VALUES ('" . $projectId . "', '" . $userId . "');
		";

		if ($this->dbConnection->query($sqlQuery) === false) {
			throw new DuplicateProjectUserPairException();
		}
	}

	/**
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return void
	 *
	 * @throws ProjectUserPairDoesNotExistException
	 */
	public function removeUser(string $userId, string $projectId)
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
	 * @param string $projectId
	 *
	 * @return int
	 */
	public function countUsers(string $projectId): int
	{
		$sqlQuery = "
			SELECT count(user_id) as count FROM projects_users WHERE project_id = '" . $projectId. "';
		";

		$results = $this->dbConnection->query($sqlQuery);
		while ($row = $results->fetch_assoc()){
			return (int) $row['count'];
		}

		return 0;
	}

	/**
	 * @param string $projectId
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return string[]
	 */
	public function getOrderedUsersByProjectId(string $projectId, int $offset, int $limit): array
	{
		$sqlQuery = "
			SELECT * FROM projects_users WHERE project_id = '" . $projectId . "' ORDER BY user_id LIMIT " . $limit . " OFFSET " . $offset .";
		";

		$results = $this->dbConnection->query($sqlQuery);
		if ($results->num_rows === 0) {
			return [];
		}

		$usersIds = [];
		while ($row = $results->fetch_assoc()){
			$usersIds[] = $row['user_id'];
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
			$projectsIds[] = (int) $result['project_id'];
		}

		return $projectsIds;
	}

	/**
	 * @param string $userId
	 * @param string $projectId
	 *
	 * @return bool
	 */
	public function checkUserAccess(string $userId, string $projectId): bool
	{
		$sqlQuery = "
			SELECT * FROM projects_users WHERE project_id = '" . $projectId . "' AND user_id = '" . $userId . "' LIMIT 1;
		";

		$result = $this->dbConnection->query($sqlQuery);
		return $result->num_rows === 1;
	}
}
