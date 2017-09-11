<?php

namespace Endpoints;

use Carbon\Carbon;
use Domain\ProjectUniqueIdGenerator;
use Domain\ValueObjects\Project;
use Validator\ArrayValidator;
use Validator\IsIntegerValidator;
use Validator\IsNotNullValidator;
use Validator\IsStringValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;

class CreateEndpoint extends AbstractEndpoint
{
	/**
	 * @var ProjectUniqueIdGenerator
	 */
	private $projectUniqueIdGenerator;

	protected function validate(array $data): ValidationResult
	{
		$validators = [
			'name' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new StringLengthValidator(10, 10)
			]),
			'type' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsIntegerValidator(),
			]),
			'userIds' => new ValidatorsCollection([
				new IsNotNullValidator(),
				//  todo: check if all user ID are strings with 10 characters
			])
		];
		$arrayValidator = new ArrayValidator();
		$arrayValidator->validateArray($validators, $data);
	}

	protected function run(array $data)
	{
		$project = new Project(
			$this->projectUniqueIdGenerator->generateUniqueId(),
			$data['name'],
			$data['type'],
			false,
			Carbon::now()
		);
		$projectsRepository->insert($project);

		foreach ($data['userIds'] as $userId) {
			$projectsUsersRepository->insert($project->getId(), $userId);
		}



		return "aaa";
	}
}
