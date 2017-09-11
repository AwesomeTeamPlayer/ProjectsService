<?php

namespace Endpoints;

class InvalidDataException extends \Exception
{
	/**
	 * @var array
	 */
	private $errorTexts;
	/**
	 * @param array $errorTexts
	 */
	public function __construct(array $errorTexts)
	{
		$this->errorTexts = $errorTexts;
	}
	/**
	 * @return array
	 */
	public function getErrorTexts(): array
	{
		return $this->errorTexts;
	}
}
