<?php

namespace Api\RequestValidators;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;

class HasUserAccessToProjectRequestValidatorTest extends TestCase
{
	/**
	 * @dataProvider dataProvider
	 */
	public function test_validate($request, $excpectedErrorsArray)
	{
		$hasUserAccessToProjectRequestValidator = new HasUserAccessToProjectRequestValidator();
		$response = $hasUserAccessToProjectRequestValidator->validate($request);

		$this->assertEquals($excpectedErrorsArray, $response);
	}

	public function dataProvider()
	{
		return [
			[
				new Request(
					'GET',
					new Uri('http://google.com/?user_id=123&project_id=456'),
					new Headers(),
					[],
					[],
					new RequestBody()
				),
				[],
			],
			[
				new Request(
					'GET',
					new Uri('http://google.com/?user_id=123'),
					new Headers(),
					[],
					[],
					new RequestBody()
				),
				[
					'projectId' => 'Must be a number'
				],
			],
			[
				new Request(
					'GET',
					new Uri('http://google.com/?project_id=456'),
					new Headers(),
					[],
					[],
					new RequestBody()
				),
				[
					'userId' => 'Must be a number'
				],
			],
			[
				new Request(
					'GET',
					new Uri('http://google.com/'),
					new Headers(),
					[],
					[],
					new RequestBody()
				),
				[
					'userId' => 'Must be a number',
					'projectId' => 'Must be a number',
				],
			]
		];
	}
}
