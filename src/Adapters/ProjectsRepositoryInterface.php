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

	public function unarchivedProject(string $projectId): bool;

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
	public function getAllProjects(string $userId, array $archived, int $limit, int $offset, string $orderBy, string $order): array;

	/**
	 * @param string $userId
	 * @param bool[] $archived
	 *
	 * @return int
	 */
	public function countAllProjects(string $userId, array $archived): int;

}
