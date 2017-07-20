<?php

class GetUsersEndpointTest extends AbstractEndToEndTest
{
	public function test_get_users_without_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('GET', '/users', '')
		);
	}

	public function test_get_users_with_empty_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('GET', '/users?project_id', '')
		);
	}

	public function test_get_users_with_string_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('GET', '/users?project_id=abc', '')
		);
	}

	public function test_get_users_with_projects_list_is_empty()
	{
		$this->assertEquals(
			'[]',
			$this->makeRequest('GET', '/users?project_id=999', '')
		);
	}

	public function test_get_users_with_projects_list_is_not_empty()
	{
		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 1]))
		);

		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 100, 'userId' => 1]))
		);

		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 2]))
		);

		$this->assertEquals(
			'[1,2]',
			$this->makeRequest('GET', '/users?project_id=999', '')
		);
	}
}
