<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
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
	 * @var UpdateProjectEndpoint
	 */
	private $updateProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
		$this->updateProjectEndpoint = new UpdateProjectEndpoint(
			$this->mysqlProjectsUsersRepository,
			$this->mysqlProjectsRepository,
			new EventSender($this->channel, 'events')
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
				Carbon::now(),
				[]
			)
		);

		$this->mysqlProjectsUsersRepository->addUser('user_1', '1234567890');
		$this->mysqlProjectsUsersRepository->addUser('user_2', '1234567890');

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

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId, 0, 20);
		$this->assertEquals(['user_11', 'user_12'], $userIds);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.name.updated', ['projectId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.removed', ['projectId', 'userId']);
		$this->assertEquals('user_1', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.removed', ['projectId', 'userId']);
		$this->assertEquals('user_2', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_11', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_12', json_decode($message->getBody(), true)['userId']);
	}
}
