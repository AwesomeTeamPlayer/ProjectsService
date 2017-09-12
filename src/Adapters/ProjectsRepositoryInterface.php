<?php

namespace Adapters;

use Domain\ValueObjects\Project;

interface ProjectsRepositoryInterface
{
	public function insert(Project $project): bool;

	public function getProject(string $projectId): Project;

}
