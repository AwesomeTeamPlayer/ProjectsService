<?php

namespace Adapters;

use Carbon\Carbon;
use Domain\ValueObjects\Project;
use mysqli;

class MysqlProjectsRepository implements ProjectsRepositoryInterface
{
	/**
	 * @var mysqli
	 */
	protected $dbConnection;

	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	public function __construct(
		mysqli $dbConnection,
		ProjectsUsersRepositoryInterface $projectsUsersRepository
	)
	{
		$this->dbConnection = $dbConnection;
		$this->projectsUsersRepository = $projectsUsersRepository;
	}

	public function insert(Project $project): bool
	{
		$sqlQuery = "
			INSERT INTO projects (id, name, type, is_archived, created_at) 
			VALUES 
			(
				'" . $project->getId() . "', 
				'" . $project->getName() . "', 
				'" . $project->getType() . "', 
				'" . (int) $project->isArchived() . "', 
				'" . $project->getCreatedAt()->toDateTimeString() . "
			');
		";

		$inserted = $this->dbConnection->query($sqlQuery);

		return $inserted;
	}

	public function update(Project $project): bool
	{
		$sqlQuery = "
			UPDATE projects SET 
				name='" . $project->getName() . "', 
				type='" . $project->getType() . "'
			WHERE
				id = '" . $project->getId() . "'
		";

		return $this->dbConnection->query($sqlQuery);
	}

	/**
	 * @param string $projectId
	 * @return Project
	 *
	 * @throws ProjectDoesNotExistException
	 */
	public function getProject(string $projectId): Project
	{
		$sqlQuery = "
			SELECT * FROM projects WHERE id = '" . $projectId. "';
		";

		$results = $this->dbConnection->query($sqlQuery);
		if ($results->num_rows === 0) {
			throw new ProjectDoesNotExistException();
		}

		$userIds = $this->projectsUsersRepository->getOrderedUsersByProjectId($projectId);

		foreach ($results as $result){
			return new Project(
				$result['id'],
				$result['name'],
				(int) $result['type'],
				$result['is_archived'] === '1',
				Carbon::parse($result['created_at']),
				$userIds
			);
		}
	}

	public function archivedProject(string $projectId): bool
	{
		$sqlQuery = "
			UPDATE projects SET 
				is_archived = TRUE
			WHERE
				id = '" . $projectId . "' AND
				is_archived = FALSE
		";

		$this->dbConnection->query($sqlQuery);
		return $this->dbConnection->affected_rows === 1;
	}

	public function unarchivedProject(string $projectId): bool
	{
		$sqlQuery = "
			UPDATE projects SET 
				is_archived = FALSE
			WHERE
				id = '" . $projectId . "' AND
				is_archived = TRUE
		";

		$this->dbConnection->query($sqlQuery);
		return $this->dbConnection->affected_rows === 1;
	}

	/**
	 * @param string $userId
	 * @param bool[] $archived
	 * @param int $limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param string $order
	 *
	 * @return Project[]
	 */
	public function getAllProjects(string $userId, array $archived, int $limit, int $offset, string $orderBy, string $order): array
	{
		foreach ($archived as $key => $value) {
			if ($value) {
				$archived[$key] = 'TRUE';
			} else {
				$archived[$key] = 'FALSE';
			}
		}

		$sqlQuery = "
			SELECT 
			 	projects.*
			FROM projects 
				JOIN projects_users ON projects.id = projects_users.project_id 
			WHERE 
				projects.is_archived IN (" . implode(',', $archived) . ") AND
				projects_users.user_id = '" . $userId . "'
			ORDER BY
				" . $this->convertOrderBy($orderBy) . " " . $order . "
			LIMIT " . $limit . " OFFSET " . $offset . "
		";

		$results = $this->dbConnection->query($sqlQuery);

		$projects = [];
		while ($row = $results->fetch_assoc()) {
			$userIds = $this->projectsUsersRepository->getOrderedUsersByProjectId($row['id']);

			$projects[] = new Project(
				$row['id'],
				$row['name'],
				(int) $row['type'],
				$row['is_archived'] === '1',
				Carbon::parse($row['created_at']),
				$userIds
			);
		}

		return $projects;
	}

	private function convertOrderBy(string $orderBy): string
	{
		if ($orderBy === 'createdAt') {
			return 'created_at';
		}

		return $orderBy;
	}

	public function countAllProjects(string $userId, array $archived): int
	{
		foreach ($archived as $key => $value) {
			if ($value) {
				$archived[$key] = 'TRUE';
			} else {
				$archived[$key] = 'FALSE';
			}
		}

		$sqlQuery = "
			SELECT 
			 	COUNT(projects.id) as count
			FROM projects 
				JOIN projects_users ON projects.id = projects_users.project_id 
			WHERE 
				projects.is_archived IN (" . implode(',', $archived) . ") AND
				projects_users.user_id = '" . $userId . "'
		";

		$results = $this->dbConnection->query($sqlQuery);
		while ($row = $results->fetch_assoc()){
			return (int) $row['count'];
		}

		return 0;
	}

}
