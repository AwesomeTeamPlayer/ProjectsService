<?php

namespace Endpoints;

use Adapters\Exceptions\ProjectUserPairDoesNotExistException;
use Adapters\ProjectsRepositoryInterface;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\EventSender;
use Validator\ArrayValidator;
use Validator\IsNotNullValidator;
use Validator\IsSetValidator;
use Validator\IsStringValidator;
use Validator\SetValidator;
use Validator\StringLengthValidator;
use Validator\ValidationResult;
use Validator\ValidatorsCollection;
use Validators\ProjectIdExistsValidator;

class RemoveUsersFromProjectEndpoint extends AbstractEndpoint
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
		$projectId = $data['projectId'];

		foreach ($data['userIds'] as $userId) {
			try {
				$this->projectsUsersRepository->removeUser($userId, $projectId);
				$this->eventSender->sendUserFromProjectRemovedEvent($projectId, $userId);
			} catch (ProjectUserPairDoesNotExistException $exception) {
			}
		}

		return true;
	}
}
