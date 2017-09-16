<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Endpoints\ArchiveProjectEndpoint;
use Endpoints\InvalidDataException;

class ArchiveProjectEndpointTest extends AbstractEndToEndTest
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
	 * @var ArchiveProjectEndpoint
	 */
	private $archiveProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsUsersRepository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->mysqlProjectsRepository = new MysqlProjectsRepository(
			$this->mysqli,
			$this->mysqlProjectsUsersRepository
		);
		$this->archiveProjectEndpoint = new ArchiveProjectEndpoint(
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
			$this->archiveProjectEndpoint->execute($data);
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
			$this->archiveProjectEndpoint->execute([
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

	public function test_when_project_is_archived()
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				'1234567890',
				'Name',
				123,
				true,
				Carbon::now(),
				[]
			)
		);

		$archived = $this->archiveProjectEndpoint->execute([
			'projectId' => '1234567890',
		]);

		$this->assertFalse($archived);

		$message = $this->getMessage();
		$this->assertNull($message);
	}

	public function test_when_project_is_not_archived()
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

		$archived = $this->archiveProjectEndpoint->execute([
			'projectId' => '1234567890'
		]);

		$this->assertTrue($archived);

		$project = $this->mysqlProjectsRepository->getProject('1234567890');
		$this->assertTrue($project->isArchived());

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.archived', ['projectId']);

		$message = $this->getMessage();
		$this->assertNull($message);
	}
}
