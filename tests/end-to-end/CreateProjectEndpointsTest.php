<?php


use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Domain\EventSender;
use Domain\ProjectUniqueIdGenerator;
use Endpoints\CreateProjectEndpoint;
use Endpoints\InvalidDataException;

class CreateProjectEndpointsTest extends AbstractEndToEndTest
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
	private $createProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsRepository = new MysqlProjectsRepository($this->mysqli);
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->createProjectEndpoint = new CreateProjectEndpoint(
			new ProjectUniqueIdGenerator(),
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
			$this->createProjectEndpoint->execute($data);
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
					'name' => 'This value does not exist.',
					'type' => 'This value does not exist.'
				],
			],
			[
				[
					'name' => ''
				],
				[
					'name' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <1, 200>.'
					],
					'type' => 'This value does not exist.'
				],
			],
			[
				[
					'name' => '',
					'type' => 'aaa'
				],
				[
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

	public function test_correct_without_users()
	{
		$projectId = $this->createProjectEndpoint->execute([
			'name' => 'Project Name',
			'type' => 123,
			'userIds' => []
		]);

		$project = $this->mysqlProjectsRepository->getProject($projectId);

		$this->assertEquals($projectId, $project->getId());
		$this->assertEquals('Project Name', $project->getName());
		$this->assertEquals(123, $project->getType());

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId, 0, 20);
		$this->assertEquals([], $userIds);
	}

	public function test_correct_with_users()
	{
		$projectId = $this->createProjectEndpoint->execute([
			'name' => 'Project Name',
			'type' => 123,
			'userIds' => ['user_1', 'user_2']
		]);

		$project = $this->mysqlProjectsRepository->getProject($projectId);

		$this->assertEquals($projectId, $project->getId());
		$this->assertEquals('Project Name', $project->getName());
		$this->assertEquals(123, $project->getType());

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId, 0, 20);
		$this->assertEquals(['user_1', 'user_2'], $userIds);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.created', ['projectId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_1', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_2', json_decode($message->getBody(), true)['userId']);
	}
}
