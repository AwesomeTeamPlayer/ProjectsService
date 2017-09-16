<?php

use Adapters\MysqlProjectsRepository;
use Carbon\Carbon;
use Domain\EventSender;
use Domain\ValueObjects\Project;
use Endpoints\UnarchiveProjectEndpoint;
use Endpoints\InvalidDataException;

class UnarchiveProjectEndpointTest extends AbstractEndToEndTest
{
	/**
	 * @var MysqlProjectsRepository
	 */
	private $mysqlProjectsRepository;

	/**
	 * @var UnarchiveProjectEndpoint
	 */
	private $unarchiveProjectEndpoint;

	public function setUp()
	{
		parent::setUp();
		$this->mysqlProjectsRepository = new MysqlProjectsRepository($this->mysqli);
		$this->unarchiveProjectEndpoint = new UnarchiveProjectEndpoint(
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
			$this->unarchiveProjectEndpoint->execute($data);
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
			$this->unarchiveProjectEndpoint->execute([
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

	public function test_when_project_is_unarchived()
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				'1234567890',
				'Name',
				123,
				false,
				Carbon::now()
			)
		);

		$unarchived = $this->unarchiveProjectEndpoint->execute([
			'projectId' => '1234567890',
		]);

		$this->assertFalse($unarchived);

		$message = $this->getMessage();
		$this->assertNull($message);
	}

	public function test_when_project_is_not_unarchived()
	{
		$this->mysqlProjectsRepository->insert(
			new Project(
				'1234567890',
				'Name',
				123,
				true,
				Carbon::now()
			)
		);

		$unarchived = $this->unarchiveProjectEndpoint->execute([
			'projectId' => '1234567890'
		]);

		$this->assertTrue($unarchived);

		$project = $this->mysqlProjectsRepository->getProject('1234567890');
		$this->assertFalse($project->isArchived());

		$message = $this->getMessage();
		$this->checkMessage($message, 'project.unarchived', ['projectId']);

		$message = $this->getMessage();
		$this->assertNull($message);
	}
}
