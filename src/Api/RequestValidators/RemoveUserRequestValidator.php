<?php

namespace Api\RequestValidators;

use Validator\IsNumberValidator;

class RemoveUserRequestValidator
{
	/**
	 * @param string $requestBody
	 *
	 * @return array
	 */
	public function validate(string $requestBody) : array
	{
		$json = json_decode($requestBody, true);
		if ($json === null) {
			return [
				'json' => 'Incorrect JSON',
			];
		}

		$errors = [];
		$isNumberValidator = new IsNumberValidator();

		if (array_key_exists('userId', $json) === false){
			$errors['userId'] = 'Is required';
		} else if ($isNumberValidator->valid($json['userId']) > 0) {
			$errors['userId'] = 'Must be a number';
		}

		if (array_key_exists('projectId', $json) === false){
			$errors['projectId'] = 'Is required';
		} else if ($isNumberValidator->valid($json['projectId']) > 0) {
			$errors['projectId'] = 'Must be a number';
		}

		return $errors;
	}
}
