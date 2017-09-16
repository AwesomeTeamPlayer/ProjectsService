<?php

namespace Adapters;

use Domain\ValueObjects\Project;

interface ProjectsRepositoryInterface
{
	public function insert(Project $project): bool;

	public function update(Project $project): bool;

	/**
	 * @param string $projectId
	 * @return Project
	 *
	 * @throws ProjectDoesNotExistException
	 */
	public function getProject(string $projectId): Project;

	public function archivedProject(string $projectId): bool;

}
