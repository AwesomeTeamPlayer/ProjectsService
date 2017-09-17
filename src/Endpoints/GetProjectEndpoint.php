<?php

namespace Endpoints;

use Adapters\ProjectsRepositoryInterface;
use Validator\ArrayValidator;
use Validator\IsNotNullValidator;
use Validator\IsStringValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;
use Validators\ProjectIdExistsValidator;

class GetProjectEndpoint extends AbstractEndpoint
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
			'projectId' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new StringLengthValidator(10, 10),
				new ProjectIdExistsValidator($this->projectsRepository)
			]),
		];
		$arrayValidator = new ArrayValidator();
		return $arrayValidator->validateArray($validators, $data, false);
	}

	protected function run(array $data)
	{
		$project = $this->projectsRepository->getProject($data['projectId']);

		return [
			"projectId" => $project->getId(),
			"name" => $project->getName(),
			"type" => $project->getType(),
			"isArchived" => $project->isArchived(),
			"createdAt" => $project->getCreatedAt()->toIso8601String(),
			"userIds" => $project->getUserIds()
		];
	}
}
