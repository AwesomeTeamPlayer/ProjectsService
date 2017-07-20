<?php

namespace Api;

use Api\RequestValidators\RemoveUserRequestValidator;
use Application\RemoveUserService;
use Slim\Http\Request;
use Slim\Http\Response;

class RemoveUserEndpoint
{
	/**
	 * @var RemoveUserService
	 */
	private $removeUserService;

	/**
	 * @var RemoveUserRequestValidator
	 */
	private $removeUserRequestValidator;

	/**
	 * @param RemoveUserService $removeUserService
	 * @param RemoveUserRequestValidator $removeUserRequestValidator
	 */
	public function __construct(
		RemoveUserService $removeUserService,
		RemoveUserRequestValidator $removeUserRequestValidator
	)
	{
		$this->removeUserService = $removeUserService;
		$this->removeUserRequestValidator = $removeUserRequestValidator;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return Response
	 */
	public function run(Request $request, Response $response) : Response
	{
		$errors = $this->removeUserRequestValidator->validate($request->getBody());
		if (empty($errors) === false) {
			return $response->withJson($errors);
		}

		$json = json_decode($request->getBody(), true);
		$userId = (int) $json['userId'];
		$projectId = (int) $json['projectId'];

		$removed = $this->removeUserService->removeUser($userId, $projectId);
		if ($removed) {
			return $response->withJson(['status' => 'removed']);
		}

		return $response->withJson(['status' => 'not removed']);
	}
}
