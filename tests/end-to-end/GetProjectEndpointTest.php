<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Endpoints\GetProjectEndpoint;
use Endpoints\UnarchiveProjectEndpoint;
use Endpoints\InvalidDataException;

class GetProjectEndpointTest extends AbstractEndToEndTest
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
	 * @var UnarchiveProjectEndpoint
	 */
	private $getProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
		$this->getProjectEndpoint = new GetProjectEndpoint(
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
			$this->getProjectEndpoint->execute($data);
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
				],
			],
			[
				[
					'projectId' => '123',
				],
				[
					'projectId' => [
						'StringLengthValidator' => 'Given string has to have length in given inclusive range <10, 10>.',
					],
				],
			],
		];
	}

	public function test_when_project_does_not_exists()
	{
		try {
			$this->getProjectEndpoint->execute([
				'projectId' => '1234567890',
			]);
		} catch (InvalidDataException $exception) {
			$this->assertEquals($exception->getErrorTexts(), [
				'projectId' => [
					'ProjectIdExistsValidator' => 'Project does not exist.',
				]
			]);
		}
	}

	public function test_when_project_is_not_unarchived()
	{
		$now = Carbon::now();

		$this->mysqlProjectsRepository->insert(
			new Project(
				'1234567890',
				'Name',
				123,
				true,
				$now,
				[]
			)
		);

		$this->mysqlProjectsUsersRepository->addUser('user_1', '1234567890');
		$this->mysqlProjectsUsersRepository->addUser('user_2', '1234567890');
		$this->mysqlProjectsUsersRepository->addUser('user_3', '1234567890');

		$projectArray = $this->getProjectEndpoint->execute([
			'projectId' => '1234567890'
		]);

		$this->assertEquals([
			"projectId" => '1234567890',
			"name" => 'Name',
			"type" => 123,
			"isArchived" => true,
			"createdAt" => $now->toIso8601String(),
			"userIds" => ['user_1', 'user_2', 'user_3']
		], $projectArray);

		$message = $this->getMessage();
		$this->assertNull($message);
	}
}
