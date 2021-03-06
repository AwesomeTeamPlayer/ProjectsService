<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Endpoints\CreateProjectEndpoint;
use Endpoints\InvalidDataException;
use Endpoints\RemoveUsersFromProjectEndpoint;

class RemoveUsersFromProjectEndpointTest extends AbstractEndToEndTest
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
	private $removeUsersFromProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
		$this->removeUsersFromProjectEndpoint = new RemoveUsersFromProjectEndpoint(
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
			$this->removeUsersFromProjectEndpoint->execute($data);
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
					'userIds' => 'This value does not exist.',
				],
			],
			[
				[
					'projectId' => '',
					'userIds' => ''
				],
				[
					'projectId' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <10, 10>.'
					],
					'userIds' => [
						'IsSetValidator' => 'Given value is not a set.'
					]
				],
			],
			[
				[
					'projectId' => '123',
					'userIds' => []
				],
				[
					'projectId' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <10, 10>.'
					],
				],
			],
		];
	}

	public function test_add_users_to_non_existing_project()
	{
		try {
			$this->removeUsersFromProjectEndpoint->execute([
				'projectId' => 'project__1',
				'userIds' => ['user_____1', 'user_____2']
			]);
		} catch (InvalidDataException $exception)
		{
			$this->assertEquals([
				'projectId' => [
					'ProjectIdExistsValidator' => 'Project does not exist.'
				]
			], $exception->getErrorTexts());
		}
	}

	public function test_correct()
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				'project__1',
				'name',
				123,
				false,
				Carbon::now(),
				[]
			)
		);
		$this->mysqlProjectsUsersRepository->addUser('user_____1', 'project__1');
		$this->mysqlProjectsUsersRepository->addUser('user_____2', 'project__1');

		$result = $this->removeUsersFromProjectEndpoint->execute([
			'projectId' => 'project__1',
			'userIds' => ['user_____1', 'user_____3']
		]);
		$this->assertTrue($result);

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId('project__1');
		$this->assertEquals(['user_____2'], $userIds);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.removed', ['projectId', 'userId']);
		$this->assertEquals('user_____1', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->assertNull($message);
	}
}
