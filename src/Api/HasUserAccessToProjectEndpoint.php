<?php

namespace Api;

use Adapters\ProjectsUsersRepositoryInterface;
use Api\RequestValidators\HasUserAccessToProjectRequestValidator;
use Slim\Http\Request;
use Slim\Http\Response;

class HasUserAccessToProjectEndpoint
{
	/**
	 * @var HasUserAccessToProjectRequestValidator
	 */
	private $hasUserAccessToProjectRequestValidator;

	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	/**
	 * @param HasUserAccessToProjectRequestValidator $hasUserAccessToProjectRequestValidator
	 * @param ProjectsUsersRepositoryInterface $projectsUsersRepository
	 */
	public function __construct(
		HasUserAccessToProjectRequestValidator $hasUserAccessToProjectRequestValidator,
		ProjectsUsersRepositoryInterface $projectsUsersRepository
	)
	{
		$this->hasUserAccessToProjectRequestValidator = $hasUserAccessToProjectRequestValidator;
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
		$errors = $this->hasUserAccessToProjectRequestValidator->validate($request);
		if (empty($errors) === false) {
			return $response->withJson($errors);
		}

		$userId = (int) $request->getQueryParam('user_id');
		$projectId = (int) $request->getQueryParam('project_id');

		$hasUserAccess = $this->projectsUsersRepository->checkUserAccess($userId, $projectId);
		return $response->withJson([ 'hasAccess' => $hasUserAccess ]);
	}
}
