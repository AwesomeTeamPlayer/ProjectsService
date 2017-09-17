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
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
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
					'type' => 'This value does not exist.',
					'userIds' => 'This value does not exist.',
				],
			],
			[
				[
					'name' => '',
					'userIds' => ''
				],
				[
					'name' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <1, 200>.'
					],
					'type' => 'This value does not exist.',
					'userIds' => [
						'IsSetValidator' => 'Given value is not a set.'
					]
				],
			],
			[
				[
					'name' => '',
					'type' => 'aaa',
					'userIds' => []
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

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId);
		$this->assertEquals([], $userIds);
	}

	public function test_correct_with_users()
	{
		$projectId = $this->createProjectEndpoint->execute([
			'name' => 'Project Name',
			'type' => 123,
			'userIds' => ['user_____1', 'user_____2']
		]);

		$project = $this->mysqlProjectsRepository->getProject($projectId);

		$this->assertEquals($projectId, $project->getId());
		$this->assertEquals('Project Name', $project->getName());
		$this->assertEquals(123, $project->getType());

		$userIds = $this->mysqlProjectsUsersRepository->getOrderedUsersByProjectId($projectId);
		$this->assertEquals(['user_____1', 'user_____2'], $userIds);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.created', ['projectId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_____1', json_decode($message->getBody(), true)['userId']);

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.user.added', ['projectId', 'userId']);
		$this->assertEquals('user_____2', json_decode($message->getBody(), true)['userId']);
	}
}
