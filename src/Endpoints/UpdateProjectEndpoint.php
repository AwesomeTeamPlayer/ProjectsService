<?php

namespace Endpoints;

use Adapters\ProjectsRepositoryInterface;
use Adapters\ProjectsUsersRepositoryInterface;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Validator\ArrayValidator;
use Validator\IsIntegerValidator;
use Validator\IsNotNullValidator;
use Validator\IsSetValidator;
use Validator\IsStringValidator;
use Validator\SetValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;
use Validators\ProjectIdExistsValidator;

class UpdateProjectEndpoint extends AbstractEndpoint
{
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
		ProjectsUsersRepositoryInterface $projectsUsersRepository,
		ProjectsRepositoryInterface $projectsRepository,
		EventSender $eventSender
	)
	{
		$this->projectsUsersRepository = $projectsUsersRepository;
		$this->projectsRepository = $projectsRepository;
		$this->eventSender = $eventSender;
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
			'name' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsStringValidator(),
				new StringLengthValidator(1, 200)
			]),
			'type' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsIntegerValidator(),
			]),
			'userIds' => new ValidatorsCollection([
				new IsNotNullValidator(),
				new IsSetValidator(),
				new SetValidator([
					new IsStringValidator(),
					new StringLengthValidator(10, 10),
				])
			])
		];
		$arrayValidator = new ArrayValidator();
		return $arrayValidator->validateArray($validators, $data, false);
	}

	protected function run(array $data)
	{
		$oldProject = $this->projectsRepository->getProject($data['projectId']);

		$project = new Project(
			$data['projectId'],
			$data['name'],
			$data['type'],
			false,
			Carbon::now(),
			[]
		);
		$this->projectsRepository->update($project);

		if ($oldProject->getName() !== $project->getName()) {
			$this->eventSender->sendProjectNameUpdatedEvent($project->getId());
		}

		if ($oldProject->getType() !== $project->getType()) {
			$this->eventSender->sendProjectTypeUpdatedEvent($project->getId());
		}

		$userIdsHaveToBe = $data['userIds'];
		$saved = $this->projectsUsersRepository->getOrderedUsersByProjectId($project->getId());

		$toRemove = array_diff($saved, $userIdsHaveToBe);
		$userIdsHaveToBe = array_diff($userIdsHaveToBe, $saved);

		foreach ($toRemove as $userIdToRemove) {
			$this->projectsUsersRepository->removeUser($userIdToRemove, $project->getId());
			$this->eventSender->sendUserFromProjectRemovedEvent($project->getId(), $userIdToRemove);
		}

		foreach ($userIdsHaveToBe as $userId) {
			$this->projectsUsersRepository->addUser($userId, $project->getId());
			$this->eventSender->sendUserToProjectAddedEvent($project->getId(), $userId);
		}

		return $project->getId();
	}
}
