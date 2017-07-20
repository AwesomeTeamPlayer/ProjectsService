<?php

class GetProjectsEndpointTest extends AbstractEndToEndTest
{
	public function test_get_projects_without_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('GET', '/projects', '')
		);
	}

	public function test_get_projects_with_empty_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('GET', '/projects?user_id', '')
		);
	}

	public function test_get_projects_with_string_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('GET', '/projects?user_id=abc', '')
		);
	}

	public function test_get_projects_with_projects_list_is_empty()
	{
		$this->assertEquals(
			'[]',
			$this->makeRequest('GET', '/projects?user_id=999', '')
		);
	}

	public function test_get_projects_with_projects_list_is_not_empty()
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
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 888, 'userId' => 2]))
		);

		$this->assertEquals(
			'[100,999]',
			$this->makeRequest('GET', '/projects?user_id=1', '')
		);
	}
}
