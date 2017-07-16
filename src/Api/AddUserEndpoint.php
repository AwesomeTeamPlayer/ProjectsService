<?php

namespace Api;

use Api\RequestValidators\AddUserRequestValidator;
use Application\AddUserService;
use Slim\Http\Request;
use Slim\Http\Response;

class AddUserEndpoint
{
	/**
	 * @var AddUserService
	 */
	private $addUserService;

	/**
	 * @var AddUserRequestValidator
	 */
	private $addUserRequestValidator;

	/**
	 * @param AddUserService $addUserService
	 * @param AddUserRequestValidator $addUserRequestValidator
	 */
	public function __construct(
		AddUserService $addUserService,
		AddUserRequestValidator $addUserRequestValidator
	)
	{
		$this->addUserService = $addUserService;
		$this->addUserRequestValidator = $addUserRequestValidator;
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return Response
	 */
	public function run(Request $request, Response $response) : Response
	{
		$errors = $this->addUserRequestValidator->validate($request->getBody());
		if (empty($errors) === false) {
			return $response->withJson($errors);
		}

		$json = json_decode($request->getBody(), true);
		$userId = (int) $json['userId'];
		$projectId = (int) $json['projectId'];

		$added = $this->addUserService->addUser($userId, $projectId);
		if ($added) {
			return $response->withJson(['status' => 'created']);
		}

		return $response->withJson(['status' => 'not created']);
	}
}
