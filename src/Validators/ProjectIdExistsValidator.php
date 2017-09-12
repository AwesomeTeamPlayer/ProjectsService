<?php

namespace Validators;

use Adapters\ProjectDoesNotExistException;
use Adapters\ProjectsRepositoryInterface;
use Validator\ValidatorInterface;

class ProjectIdExistsValidator implements ValidatorInterface
{
	/**
	 * @var int
	 */
	const PROJECT_EXISTS = 0;

	/**
	 * @var int
	 */
	const PROJECT_DOES_NOT_EXIST = 1;

	/**
	 * @var ProjectsRepositoryInterface
	 */
	private $projectsRepository;

	/**
	 * @param ProjectsRepositoryInterface $projectsRepository
	 */
	public function __construct(ProjectsRepositoryInterface $projectsRepository)
	{
		$this->projectsRepository = $projectsRepository;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int -   0 if VALUE is correct
	 *                 > 0 otherwise. Returned value specifies type of error.
	 */
	public function valid($value): int
	{
		try {
			$this->projectsRepository->getProject($value);
		} catch (ProjectDoesNotExistException $exception)
		{
			return self::PROJECT_DOES_NOT_EXIST;
		}

		return self::PROJECT_EXISTS;
	}

	/**
	 * @param int $validationResult
	 *
	 * @return string
	 */
	public function errorText(int $validationResult): string
	{
		switch ($validationResult) {
			case self::PROJECT_EXISTS:
				return 'Ok';
			case self::PROJECT_DOES_NOT_EXIST:
				return 'Project does not exist.';
		}
	}

	/**
	 * Returns unique name of the validator.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return 'ProjectIdExistsValidator';
	}
}
