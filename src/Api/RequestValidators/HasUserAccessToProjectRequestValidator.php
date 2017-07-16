<?php

namespace Api\RequestValidators;

use Slim\Http\Request;
use Validator\IsNumberValidator;

class HasUserAccessToProjectRequestValidator
{
	/**
	 * @param Request $request
	 *
	 * @return array
	 */
	public function validate(Request $request) : array
	{
		$errors = [];
		$isNumberValidator = new IsNumberValidator();

		$projectId = $request->getQueryParam('project_id');
		$isValid = $isNumberValidator->valid($projectId);
		if ($isValid > 0) {
			$errors['projectId'] = 'Must be a number';
		}

		$userId = $request->getQueryParam('user_id');
		$isValid = $isNumberValidator->valid($userId);
		if ($isValid > 0) {
			$errors['userId'] = 'Must be a number';
		}

		return $errors;
	}
}
