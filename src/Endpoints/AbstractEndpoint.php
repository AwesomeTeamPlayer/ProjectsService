<?php

namespace Endpoints;

use Validator\ValidationResult;

abstract class AbstractEndpoint
{
	abstract protected function validate(array $data): ValidationResult;
	abstract protected function run(array $data);
	/**
	 * @param array $data
	 *
	 * @return mixed
	 *
	 * @throws InvalidDataException
	 */
	public function execute(array $data)
	{
		$validationResult = $this->validate($data);
		if ($validationResult->isValid() === false) {
			throw new InvalidDataException($validationResult->errorsTexts());
		}
		return $this->run($data);
	}
}
