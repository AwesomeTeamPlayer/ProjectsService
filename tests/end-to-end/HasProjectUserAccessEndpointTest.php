<?php

class HasProjectUserAccessEndpointTest extends AbstractEndToEndTest
{
	public function test_create_pair_without_parameters()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number","userId":"Must be a number"}',
			$this->makeRequest('GET', '/users/hasAccess')
		);
	}

	public function test_create_pair_with_string_userId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number","userId":"Must be a number"}',
			$this->makeRequest('GET', '/users/hasAccess?user_id=123abc')
		);
	}

	public function test_create_pair_with_empty_userId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number","userId":"Must be a number"}',
			$this->makeRequest('GET', '/users/hasAccess?user_id')
		);
	}

	public function test_create_pair_with_string_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number","userId":"Must be a number"}',
			$this->makeRequest('GET', '/users/hasAccess?project_id=123abc')
		);
	}

	public function test_create_pair_with_empty_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number","userId":"Must be a number"}',
			$this->makeRequest('GET', '/users/hasAccess?project_id')
		);
	}

	public function test_successful_when_user_has_no_access()
	{
		$this->assertEquals(
			'{"hasAccess":false}',
			$this->makeRequest('GET', '/users/hasAccess?user_id=123&project_id=999')
		);
	}

	public function test_successful_when_user_has_access()
	{
		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$this->assertEquals(
			'{"hasAccess":true}',
			$this->makeRequest('GET', ' /users/hasAccess?user_id=123&project_id=999')
		);
	}
}
