<?php

namespace Api;

use Adapters\ProjectsUsersRepositoryInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Validator\IsNumberValidator;

class GetProjectsByUserEndpoint
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
		$userId = $request->getQueryParam('user_id');

		$isNumberValidator = new IsNumberValidator();
		$isValid = $isNumberValidator->valid($userId);

		if ($isValid > 0) {
			return $response->withJson(['userId' => 'Must be a number']);
		}

		$userId = (int) $userId;
		$projectIds = $this->projectsUsersRepository->getProjectsByUserId($userId);

		return $response->withJson($projectIds);
	}
}
