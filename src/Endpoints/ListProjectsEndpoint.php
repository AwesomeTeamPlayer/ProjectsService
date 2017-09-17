<?php

namespace Endpoints;

use Adapters\ProjectsRepositoryInterface;
use Validator\ArrayValidator;
use Validator\IsIntegerValidator;
use Validator\IsNotNullValidator;
use Validator\IsNumberGreaterOrEqualValidator;
use Validator\IsStringValidator;
use Validator\IsValueFromSetValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;

class ListProjectsEndpoint extends AbstractEndpoint
{
	/**
	 * @var ProjectsRepositoryInterface
	 */
	private $projectsRepository;

	public function __construct(
		ProjectsRepositoryInterface $projectsRepository
	)
	{
		$this->projectsRepository = $projectsRepository;
	}

	protected function validate(array $data): ValidationResult
	{
		$validators = [
			'userId' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new StringLengthValidator(10, 10),
			]),
			'page' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsIntegerValidator(),
				new IsNumberGreaterOrEqualValidator(0)
			]),
			'limit' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsIntegerValidator(),
				new IsNumberGreaterOrEqualValidator(1)
			]),
			'filter' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new IsValueFromSetValidator(["all", "archived", "unarchived"])
			]),
			'orderBy' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new IsValueFromSetValidator(["name", "createdAt", "type"])
			]),
			'order' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new IsValueFromSetValidator(["desc", "asc"])
			]),
		];
		$arrayValidator = new ArrayValidator();
		return $arrayValidator->validateArray($validators, $data, false);
	}

	protected function run(array $data)
	{
		$archived = [];
		if ($data['filter'] === 'archived' || $data['filter'] === 'all') {
			$archived[] = true;
		}
		if ($data['filter'] === 'unarchived' || $data['filter'] === 'all') {
			$archived[] = false;
		}

		$projects = $this->projectsRepository->getAllProjects(
			$data['userId'],
			$archived,
			(int) $data['limit'],
			(int) $data['limit'] * (int) $data['page'],
			$data['orderBy'],
			$data['order']
		);

		$list = [];
		foreach ($projects as $project) {
			$list[] = [
				"projectId" => $project->getId(),
				"name" => $project->getName(),
				"type" => $project->getType(),
				"isArchived" => $project->isArchived(),
				"createdAt" => $project->getCreatedAt()->toIso8601String(),
				"userIds" => $project->getUserIds()
			];
		}

		return [
			'list' => $list,
			'countTotal' => $this->projectsRepository->countAllProjects($data['userId'], $archived),
		];
	}
}
