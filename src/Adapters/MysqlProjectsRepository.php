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

	public function __construct(mysqli $dbConnection)
	{
		$this->dbConnection = $dbConnection;
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

	public function getProject(string $projectId): Project
	{
		$sqlQuery = "
			SELECT * FROM projects WHERE id = '" . $projectId. "';
		";

		$results = $this->dbConnection->query($sqlQuery);
		foreach ($results as $result){
			return new Project(
				$result['id'],
				$result['name'],
				(int) $result['type'],
				(bool) $result['is_archived'],
				Carbon::parse($result['created_at'])
			);
		}
	}
}
