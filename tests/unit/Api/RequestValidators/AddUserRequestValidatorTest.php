<?php

namespace Api\RequestValidators;

use PHPUnit\Framework\TestCase;

class AddUserRequestValidatorTest extends TestCase
{
	/**
	 * @dataProvider dataProvider
	 */
	public function test_validate($requestBody, $expectedOutput)
	{
		$validator = new AddUserRequestValidator();
		$response = $validator->validate($requestBody);

		$this->assertEquals($expectedOutput, $response);
	}

	public function dataProvider()
	{
		return [
			[
				'{"userId":123,"projectId":456}',
				[]
			],
			[
				'{"userId":"123","projectId":"456"}',
				[]
			],
			[
				'{"userId":"123aa","projectId":"456"}',
				[ 'userId' => 'Must be a number' ]
			],
			[
				'{"userId":"123","projectId":"456abc"}',
				[ 'projectId' => 'Must be a number' ]
			],
			[
				'{"userId":"123abc","projectId":"456abc"}',
				[
					'userId' => 'Must be a number',
					'projectId' => 'Must be a number'
				]
			],
			[
				'{"userId":"abc","projectId":"abc"}',
				[
					'userId' => 'Must be a number',
					'projectId' => 'Must be a number'
				]
			],
			[
				'{"userId":123}',
				[
					'projectId' => 'Is required'
				]
			],
			[
				'{"projectId":123}',
				[
					'userId' => 'Is required'
				]
			],
			[
				'{}',
				[
					'projectId' => 'Is required',
					'userId' => 'Is required',
				]
			],
			[
				'{"a":"b"}',
				[
					'projectId' => 'Is required',
					'userId' => 'Is required',
				]
			],
			[
				'{"azsdfsdf',
				[
					'json' => 'Incorrect JSON'
				]
			],
			[
				'abcdef',
				[
					'json' => 'Incorrect JSON'
				]
			],
		];
	}
}
