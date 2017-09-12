<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Endpoints\CreateProjectEndpoint;
use Endpoints\InvalidDataException;
use Endpoints\UpdateProjectEndpoint;

class UpdateProjectEndpointsTest extends AbstractEndToEndTest
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
	 * @var CreateProjectEndpoint
	 */
	private $updateProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsRepository = new MysqlProjectsRepository($this->mysqli);
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->updateProjectEndpoint = new UpdateProjectEndpoint(
			$this->mysqlProjectsUsersRepository,
			$this->mysqlProjectsRepository,
			new EventSender($this->channel)
		);
	}

	/**
	 * @dataProvider dataProvider_test_with_incorrect_data
	 */
	public function test_with_incorrect_data($data, $expectedException)
	{
		try {
			$this->updateProjectEndpoint->execute($data);
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
					'projectId' => 'This value does not exist.',
					'name' => 'This value does not exist.',
					'type' => 'This value does not exist.'
				],
			],
			[
				[
					'name' => ''
				],
				[
					'projectId' => 'This value does not exist.',
					'name' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <1, 200>.'
					],
					'type' => 'This value does not exist.'
				],
			],
			[
				[
					'projectId' => '123',
					'name' => '',
					'type' => 'aaa'
				],
				[
					'projectId' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <10, 10>.',
					],
					'name' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <1, 200>.'
					],
					'type' => [
						'IsIntegerValidator' => 'Given value has to be an integer.'
					]
				],
			],
		];
	}

	public function test_when_project_does_not_exists()
	{
		try {
			$this->updateProjectEndpoint->execute([
				'projectId' => '1234567890',
				'name' => 'Project Name',
				'type' => 123,
				'userIds' => []
			]);
		} catch (InvalidDataException $exception) {
			$this->assertEquals($exception->getErrorTexts(), [
				'projectId' => [
					'ProjectIdExistsValidator' => 'Project does not exist.',
				]
			]);
		}
	}

	public function test_correct_with_users()
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				'1234567890',
				'Name',
				123,
				false,
				['user_1', 'user_2']
			)
		);

		$projectId = $this->updateProjectEndpoint->execute([
			'projectId' => '1234567890',
			'name' => 'New name',
			'type' => 456,
			'userIds' => ['user_11', 'user_12']
		]);

		$project = $this->mysqlProjectsRepository->getProject($projectId);

		$this->assertEquals($projectId, $project->getId());
		$this->assertEquals('New name', $project->getName());
		$this->assertEquals(456, $project->getType());

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId);
		$this->assertEquals(['user_1', 'user_2'], $userIds);

//		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
//		$this->assertEquals('project.created', $message->delivery_info['routing_key']);
//		$this->assertEquals('project.created', json_decode($message->getBody(), true)['name']);
//		$this->assertEquals([
//			'name' => 'Project Name',
//			'type' => 123,
//		], array_diff_key(json_decode($message->getBody(), true)['data'], ['projectId' => '', 'createdAt' => '']));
//		$this->assertEquals([
//			'projectId', 'name', 'type', 'createdAt'
//		], array_keys(json_decode($message->getBody(), true)['data'] ));
//
//		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
//		$this->assertEquals('project.user.added', $message->delivery_info['routing_key']);
//		$this->assertEquals('project.user.added', json_decode($message->getBody(), true)['name']);
//		$this->assertEquals('user_1', json_decode($message->getBody(), true)['data']['userId']);
//		$this->assertEquals([
//			'projectId', 'userId'
//		], array_keys(json_decode($message->getBody(), true)['data'] ));
//
//		$message = $this->channel->basic_get(AbstractEndToEndTest::QUEUE_NAME, true);
//		$this->assertEquals('project.user.added', $message->delivery_info['routing_key']);
//		$this->assertEquals('project.user.added', json_decode($message->getBody(), true)['name']);
//		$this->assertEquals('user_2', json_decode($message->getBody(), true)['data']['userId']);
//		$this->assertEquals([
//			'projectId', 'userId'
//		], array_keys(json_decode($message->getBody(), true)['data'] ));
	}
}
