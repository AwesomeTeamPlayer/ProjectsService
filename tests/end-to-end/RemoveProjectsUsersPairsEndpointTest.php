<?php

class RemoveProjectsUsersPairsEndpointTest extends AbstractEndToEndTest
{
	public function test_remove_pair_with_empty_body()
	{
		$this->assertEquals(
			'{"json":"Incorrect JSON"}',
			$this->makeRequest('DELETE', '/users', '')
		);
	}

	public function test_remove_pair_with_incorrect_body()
	{
		$this->assertEquals(
			'{"json":"Incorrect JSON"}',
			$this->makeRequest('DELETE', '/users', '123abc')
		);
	}

	public function test_remove_pair_with_empty_json()
	{
		$this->assertEquals(
			'{"userId":"Is required","projectId":"Is required"}',
			$this->makeRequest('DELETE', '/users', '{}')
		);
	}

	public function test_remove_pair_without_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Is required"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'userId' => 123 ]))
		);
	}

	public function test_remove_pair_without_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Is required"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 123 ]))
		);
	}

	public function test_remove_pair_with_string_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 999, 'userId' => '123abc']))
		);
	}

	public function test_remove_pair_with_empty_string_userId_parameter()
	{
		$this->assertEquals(
			'{"userId":"Must be a number"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 999, 'userId' => '']))
		);
	}

	public function test_remove_pair_with_string_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => '999abc', 'userId' => 123]))
		);
	}

	public function test_remove_pair_with_empty_string_projectId_parameter()
	{
		$this->assertEquals(
			'{"projectId":"Must be a number"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => '', 'userId' => 123]))
		);
	}

	public function test_delete_pair_when_pair_does_not_exist()
	{
		$this->assertEquals(
			'{"hasAccess":false}',
			$this->makeRequest('GET', ' /users/hasAccess?user_id=123&project_id=999')
		);

		$this->assertEquals(
			'{"status":"not removed"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
		$this->assertNull($message);
	}

	public function test_successful_remove_pair()
	{

		$this->assertEquals(
			'{"status":"created"}',
			$this->makeRequest('PUT', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$this->assertEquals(
			'{"hasAccess":true}',
			$this->makeRequest('GET', ' /users/hasAccess?user_id=123&project_id=999')
		);

		$this->assertEquals(
			'{"status":"removed"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		$this->assertEquals(
			'{"status":"not removed"}',
			$this->makeRequest('DELETE', '/users', json_encode([ 'projectId' => 999, 'userId' => 123]))
		);

		sleep(1);

		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
		$this->assertEquals([
			'name' => 'AddedUserToProject',
			'data' => [
				'userId' => 123,
				'projectId' => 999
			],
		], array_diff_key(json_decode($message->getBody(), true), ['occuredAt' => '']));

		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
		$this->assertEquals([
			'name' => 'RemovedUserFromProject',
			'data' => [
				'userId' => 123,
				'projectId' => 999
			],
		], array_diff_key(json_decode($message->getBody(), true), ['occuredAt' => '']));

		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
		$this->assertNull($message);
	}
}
