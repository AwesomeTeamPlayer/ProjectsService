<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Carbon\Carbon;
use Domain\ValueObjects\Project;
use Endpoints\ListProjectsEndpoint;
use Endpoints\InvalidDataException;

class ListProjectsEndpointTest extends AbstractEndToEndTest
{
	/**
	 * @var MysqlProjectsRepository
	 */
	private $mysqlProjectsRepository;

	/**
	 * @var MysqlProjectsUsersRepository
	 */
	private $mysqlProjectsUsersRepository;

	/**
	 * @var ListProjectsEndpoint
	 */
	private $listProjectsEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
		$this->listProjectsEndpoint = new ListProjectsEndpoint(
			$this->mysqlProjectsRepository
		);
	}

	/**
	 * @dataProvider dataProvider_test_with_incorrect_data
	 */
	public function test_with_incorrect_data($data, $expectedException)
	{
		try {
			$this->listProjectsEndpoint->execute($data);
		} catch (InvalidDataException $exception) {
			$this->assertEquals($exception->getErrorTexts(), $expectedException);
		}
	}

	public function dataProvider_test_with_incorrect_data()
	{
		return [
			[
				[],
				[
					'userId' => 'This value does not exist.',
					'page' => 'This value does not exist.',
					'limit' => 'This value does not exist.',
					'filter' => 'This value does not exist.',
					'orderBy' => 'This value does not exist.',
					'order' => 'This value does not exist.',
				],
			],
			[
				[
					'userId' => '',
					'page' => '',
					'limit' => '',
					'filter' => '',
					'orderBy' => '',
					'order' => '',
				],
				[
					'userId' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <10, 10>.'
					],
					'page' => [
						'IsIntegerValidator' => 'Given value has to be an integer.'

					],
					'limit' => [
						'IsIntegerValidator' => 'Given value has to be an integer.'
					],
					'filter' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: all,archived,unarchived.'
					],
					'orderBy' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: name,createdAt,type.'
					],
					'order' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: desc,asc.'
					],
				],
			],
			[
				[
					'userId' => '0123456789',
					'page' => -2,
					'limit' => 0,
					'filter' => 'incorrect filter value',
					'orderBy' => 'aaa',
					'order' => 'bbb',
				],
				[
					'page' => [
						'IsNumberGreaterOrEqualValidator' => 'Given number has to be greater or equal 0.'
					],
					'limit' => [
						'IsNumberGreaterOrEqualValidator' => 'Given number has to be greater or equal 1.'
					],
					'filter' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: all,archived,unarchived.'
					],
					'orderBy' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: name,createdAt,type.'
					],
					'order' => [
						'IsValueFromSetValidator' => 'Given value is incorrect. We accept only those values: desc,asc.'
					],
				],
			],
		];
	}

	public function test_when_project_does_not_exists()
	{
		$result = $this->listProjectsEndpoint->execute([
			'userId' => '0123456789',
			'page' => 0,
			'limit' => 10,
			'filter' => 'all',
			'orderBy' => 'name',
			'order' => 'asc'
		]);

		$this->assertEquals(
			[
				'list' => [],
				'countTotal' => 0
			],
			$result
		);
	}

	public function test_when_project_for_specific_user_does_not_exists()
	{
		$this->createProject('project__1', 'Name', 123, true, ['user_____1']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => '0123456789',
			'page' => 0,
			'limit' => 10,
			'filter' => 'all',
			'orderBy' => 'name',
			'order' => 'asc'
		]);

		$this->assertEquals(
			[
				'list' => [],
				'countTotal' => 0
			],
			$result
		);
	}

	public function test_when_projects_for_specific_user_exist()
	{
		$this->createProject('project__1', 'Name 1', 123, true, ['user_____1']);
		$this->createProject('project__2', 'Name 2', 123, false, ['user_____2', 'user_____3']);
		$this->createProject('project__3', 'Name 3', 45, false, ['user_____1']);
		$this->createProject('project__4', 'Name 4', 45, false, ['user_____3', 'user_____1']);
		$this->createProject('project__5', 'Name 5', 123, false, ['user_____2']);
		$this->createProject('project__6', 'Name 6', 45, true, ['user_____2']);
		$this->createProject('project__7', 'Name 7', 123, false, ['user_____2']);
		$this->createProject('project__8', 'Name 8', 123, true, ['user_____2']);
		$this->createProject('project__9', 'Name 9', 45, false, ['user_____2']);
		$this->createProject('project_10', 'Name 10', 123, false, ['user_____2']);
		$this->createProject('project_11', 'Name 11', 123, false, ['user_____2']);
		$this->createProject('project_12', 'Name 12', 678, false, ['user_____2']);
		$this->createProject('project_13', 'Name 13', 123, false, ['user_____2']);
		$this->createProject('project_14', 'Name 14', 45, false, ['user_____2']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____2',
			'page' => 0,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'name',
			'order' => 'asc'
		]);

		$this->assertCount(5, $result['list']);
		$this->assertEquals(11, $result['countTotal']);

		$this->assertEquals('project_10', $result['list'][0]['projectId']);
		$this->assertEquals('Name 10', $result['list'][0]['name']);
		$this->assertEquals(['user_____2'], $result['list'][0]['userIds']);

		$this->assertEquals('project_11', $result['list'][1]['projectId']);
		$this->assertEquals('Name 11', $result['list'][1]['name']);
		$this->assertEquals(['user_____2'], $result['list'][1]['userIds']);

		$this->assertEquals('project_12', $result['list'][2]['projectId']);
		$this->assertEquals('Name 12', $result['list'][2]['name']);
		$this->assertEquals(['user_____2'], $result['list'][2]['userIds']);

		$this->assertEquals('project_13', $result['list'][3]['projectId']);
		$this->assertEquals('Name 13', $result['list'][3]['name']);
		$this->assertEquals(['user_____2'], $result['list'][3]['userIds']);

		$this->assertEquals('project_14', $result['list'][4]['projectId']);
		$this->assertEquals('Name 14', $result['list'][4]['name']);
		$this->assertEquals(['user_____2'], $result['list'][4]['userIds']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____2',
			'page' => 1,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'name',
			'order' => 'asc'
		]);

		$this->assertCount(5, $result['list']);
		$this->assertEquals(11, $result['countTotal']);

		$this->assertEquals('project__2', $result['list'][0]['projectId']);
		$this->assertEquals('Name 2', $result['list'][0]['name']);
		$this->assertEquals(['user_____2', 'user_____3'], $result['list'][0]['userIds']);

		$this->assertEquals('project__5', $result['list'][1]['projectId']);
		$this->assertEquals('Name 5', $result['list'][1]['name']);
		$this->assertEquals(['user_____2'], $result['list'][1]['userIds']);

		$this->assertEquals('project__6', $result['list'][2]['projectId']);
		$this->assertEquals('Name 6', $result['list'][2]['name']);
		$this->assertEquals(['user_____2'], $result['list'][2]['userIds']);

		$this->assertEquals('project__7', $result['list'][3]['projectId']);
		$this->assertEquals('Name 7', $result['list'][3]['name']);
		$this->assertEquals(['user_____2'], $result['list'][3]['userIds']);

		$this->assertEquals('project__8', $result['list'][4]['projectId']);
		$this->assertEquals('Name 8', $result['list'][4]['name']);
		$this->assertEquals(['user_____2'], $result['list'][4]['userIds']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____2',
			'page' => 2,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'name',
			'order' => 'asc'
		]);

		$this->assertCount(1, $result['list']);
		$this->assertEquals(11, $result['countTotal']);

		$this->assertEquals('project__9', $result['list'][0]['projectId']);
		$this->assertEquals('Name 9', $result['list'][0]['name']);
		$this->assertEquals(['user_____2'], $result['list'][0]['userIds']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____2',
			'page' => 0,
			'limit' => 5,
			'filter' => 'archived',
			'orderBy' => 'name',
			'order' => 'desc'
		]);

		$this->assertCount(2, $result['list']);
		$this->assertEquals(2, $result['countTotal']);

		$this->assertEquals('Name 8', $result['list'][0]['name']);
		$this->assertEquals('Name 6', $result['list'][1]['name']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____3',
			'page' => 0,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'type',
			'order' => 'asc'
		]);

		$this->assertCount(2, $result['list']);
		$this->assertEquals(2, $result['countTotal']);

		$this->assertEquals('Name 4', $result['list'][0]['name']);
		$this->assertEquals('Name 2', $result['list'][1]['name']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user_____3',
			'page' => 123,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'type',
			'order' => 'asc'
		]);

		$this->assertCount(0, $result['list']);
		$this->assertEquals(2, $result['countTotal']);

		$result = $this->listProjectsEndpoint->execute([
			'userId' => 'user___111',
			'page' => 0,
			'limit' => 5,
			'filter' => 'all',
			'orderBy' => 'type',
			'order' => 'asc'
		]);

		$this->assertCount(0, $result['list']);
		$this->assertEquals(0, $result['countTotal']);
	}

	private function createProject(string $projectId, string $name, int $type, bool $isArchived, array $userIds)
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				$projectId,
				$name,
				$type,
				$isArchived,
				Carbon::now(),
				[]
			)
		);

		foreach ($userIds as $userId) {
			$this->mysqlProjectsUsersRepository->addUser($userId, $projectId);
		}
	}
}
