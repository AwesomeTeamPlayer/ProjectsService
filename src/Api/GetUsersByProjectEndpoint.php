<?php

namespace Api;

use Adapters\ProjectsUsersRepositoryInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Validator\IsNumberValidator;

class GetUsersByProjectEndpoint
{
	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	/**
	 * @param $projectsUsersRepository
	 */
	public function __construct(
		ProjectsUsersRepositoryInterface $projectsUsersRepository
	)
	{
		$this->projectsUsersRepository = $projectsUsersRepository;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return Response
	 */
	public function run(Request $request, Response $response) : Response
	{
		$projectId = $request->getQueryParam('project_id');

		$isNumberValidator = new IsNumberValidator();
		$isValid = $isNumberValidator->valid($projectId);

		if ($isValid > 0) {
			return $response->withJson(['projectId' => 'Must be a number']);
		}

		$projectId = (int) $projectId;
		$usersIds = $this->projectsUsersRepository->getUsersByProjectId($projectId);

		return $response->withJson($usersIds);
	}
}
