<?php

namespace Endpoints;

use Adapters\ProjectsRepositoryInterface;
use Adapters\ProjectsUsersRepositoryInterface;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ProjectUniqueIdGenerator;
use Domain\ValueObjects\Project;
use Validator\ArrayValidator;
use Validator\IsIntegerValidator;
use Validator\IsNotNullValidator;
use Validator\IsStringValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;

class CreateProjectEndpoint extends AbstractEndpoint
{
	/**
	 * @var ProjectUniqueIdGenerator
	 */
	private $projectUniqueIdGenerator;

	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	/**
	 * @var ProjectsRepositoryInterface
	 */
	private $projectsRepository;

	/**
	 * @var EventSender
	 */
	private $eventSender;

	public function __construct(
		ProjectUniqueIdGenerator $projectUniqueIdGenerator,
		ProjectsUsersRepositoryInterface $projectsUsersRepository,
		ProjectsRepositoryInterface $projectsRepository,
		EventSender $eventSender
	)
	{
		$this->projectUniqueIdGenerator = $projectUniqueIdGenerator;
		$this->projectsUsersRepository = $projectsUsersRepository;
		$this->projectsRepository = $projectsRepository;
		$this->eventSender = $eventSender;
	}

	protected function validate(array $data): ValidationResult
	{
		$validators = [
			'name' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new StringLengthValidator(1, 200)
			]),
			'type' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsIntegerValidator(),
			]),
//			'userIds' => new ValidatorsCollection([
//				new IsNotNullValidator(),
//				//  todo: check if all user ID are strings with 10 characters
//			])
		];
		$arrayValidator = new ArrayValidator();
		return $arrayValidator->validateArray($validators, $data, false);
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
		$this->projectsRepository->insert($project);
		$this->eventSender->sendProjectCreatedEvent($project->getId());

		foreach ($data['userIds'] as $userId) {
			$this->projectsUsersRepository->addUser($userId, $project->getId());
			$this->eventSender->sendUserToProjectAddedEvent($project->getId(), $userId);
		}

		return $project->getId();
	}
}
