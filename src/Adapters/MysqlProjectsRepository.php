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

		$userIds = $this->projectsUsersRepository->getOrderedUsersByProjectId($projectId, 0, 200);

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
}
