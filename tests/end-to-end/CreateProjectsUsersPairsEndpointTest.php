<?php

class CreateProjectsUsersPairsEndpointTest extends AbstractEndToEndTest
{
	public function test_create_pair_with_empty_body()
	{
		$this->assertEquals(
			'{"json":"Incorrect JSON"}',
			$this->makeRequest('PUT', '/users', '')
		);
	}

	public function test_create_pair_with_incorrect_body()
	{
		$this->assertEquals(
			'{"json":"Incorrect JSON"}',
			$this->makeRequest('PUT', '/users', '123abc')
		);
	}

	public function test_create_pair_with_empty_json()
	{
		$this->assertEquals(
			'{"userId":"Is required","projectId":"Is required"}',
			$this->makeRequest('PUT', '/users', '{}')
		);
	}

	public function test_create_pair_without_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Is required"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'userId' => 123 ]))
		);
	}

	public function test_create_pair_without_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Is required"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 123 ]))
		);
	}

	public function test_create_pair_with_string_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => '123abc']))
		);
	}

	public function test_create_pair_with_empty_string_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => '']))
		);
	}

	public function test_create_pair_with_string_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => '999abc', 'userId' => 123]))
		);
	}

	public function test_create_pair_with_empty_string_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => '', 'userId' => 123]))
		);
	}

	public function test_successful_create_pair()
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

	public function test_create_pair_and_check_different_project()
	{
		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$this->assertEquals(
			'{"hasAccess":false}',
			$this->makeRequest('GET', ' /users/hasAccess?user_id=123&project_id=100')
		);
	}

	public function test_create_pair_and_check_different_user()
	{
		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$this->assertEquals(
			'{"hasAccess":false}',
			$this->makeRequest('GET', ' /users/hasAccess?user_id=100&project_id=999')
		);
	}
}
