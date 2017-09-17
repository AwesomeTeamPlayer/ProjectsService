<?php

use Adapters\MysqlProjectsRepository;
use Adapters\MysqlProjectsUsersRepository;
use Adapters\ProjectsRepositoryInterface;
use Adapters\ProjectsUsersRepositoryInterface;
use Domain\EventSender;
use Domain\ProjectUniqueIdGenerator;
use Endpoints\AbstractEndpoint;
use Endpoints\AddUsersToProjectEndpoint;
use Endpoints\ArchiveProjectEndpoint;
use Endpoints\CreateProjectEndpoint;
use Endpoints\GetProjectEndpoint;
use Endpoints\InvalidDataException;
use Endpoints\ListProjectsEndpoint;
use Endpoints\RemoveUsersFromProjectEndpoint;
use Endpoints\UnarchiveProjectEndpoint;
use Endpoints\UpdateProjectEndpoint;
use PhpAmqpLib\Connection\AMQPStreamConnection;

require __DIR__ . '/../vendor/autoload.php';

class EndpointsHandler
{
	public $error = null;

	/**
	 * @var ProjectUniqueIdGenerator
	 */
	private $projectUniqueIdGenerator;

	/**
	 * @var ProjectsUsersRepositoryInterface
	 */
	private $projectsUsersRepository;

	/**
	 * @var ProjectsRepositoryInterface
	 */
	private $projectsRepository;

	/**
	 * @var EventSender
	 */
	private $eventSender;

	public function __construct(
		ProjectUniqueIdGenerator $projectUniqueIdGenerator,
		ProjectsUsersRepositoryInterface $projectsUsersRepository,
		ProjectsRepositoryInterface $projectsRepository,
		EventSender $eventSender
	)
	{
		$this->projectUniqueIdGenerator = $projectUniqueIdGenerator;
		$this->projectsUsersRepository = $projectsUsersRepository;
		$this->projectsRepository = $projectsRepository;
		$this->eventSender = $eventSender;
	}

	public function createProject($name, $type, $userIds)
	{
		return $this->execute(new CreateProjectEndpoint(
			$this->projectUniqueIdGenerator,
			$this->projectsUsersRepository,
			$this->projectsRepository,
			$this->eventSender
		), [
			'name' => $name,
			'type' => $type,
			'userIds' => $userIds,
		]);
	}

	public function updateProject($projectId, $name, $type, $userIds)
	{
		return $this->execute(new UpdateProjectEndpoint(
			$this->projectsUsersRepository,
			$this->projectsRepository,
			$this->eventSender
		), [
			'projectId' => $projectId,
			'name' => $name,
			'type' => $type,
			'userIds' => $userIds,
		]);
	}

	public function addUsersToProject($projectId, $userIds)
	{
		return $this->execute(new AddUsersToProjectEndpoint(
			$this->projectsUsersRepository,
			$this->projectsRepository,
			$this->eventSender
		), [
			'projectId' => $projectId,
			'userIds' => $userIds,
		]);
	}

	public function removeUsersFromProject($projectId, $userIds)
	{
		return $this->execute(new RemoveUsersFromProjectEndpoint(
			$this->projectsUsersRepository,
			$this->projectsRepository,
			$this->eventSender
		), [
			'projectId' => $projectId,
			'userIds' => $userIds,
		]);
	}

	public function listProjects($userId, $page, $limit, $filter, $orderBy, $order)
	{
		return $this->execute(new ListProjectsEndpoint(
			$this->projectsRepository
		), [
			'userId' => $userId,
			'page' => $page,
			'limit' => $limit,
			'filter' => $filter,
			'orderBy' => $orderBy,
			'order' => $order
		]);
	}

	public function getProject($projectId)
	{
		return $this->execute(new GetProjectEndpoint(
			$this->projectsRepository
		), [
			'projectId' => $projectId
		]);
	}

	public function archiveProject($projectId)
	{
		return $this->execute(new ArchiveProjectEndpoint(
			$this->projectsRepository,
			$this->eventSender
		), [
			'projectId' => $projectId
		]);
	}

	public function unarchiveProject($projectId)
	{
		return $this->execute(new UnarchiveProjectEndpoint(
			$this->projectsRepository,
			$this->eventSender
		), [
			'projectId' => $projectId
		]);
	}

	private function execute(AbstractEndpoint $endpointsObject, array $data)
	{
		try {
			return $endpointsObject->execute($data);
		} catch (InvalidDataException $exception) {
			$this->error = [
				'code' => -32000,
				'message' => 'Invalid data',
				'data' => $exception->getErrorTexts()
			];
		}
	}
}

$mysqlHost = getenv('MYSQL_HOST');
$mysqlPort = (int) getenv('MYSQL_PORT');
$mysqlUser = getenv('MYSQL_USER');
$mysqlPassword = getenv('MYSQL_PASSWORD');
$mysqlDatabase = getenv('MYSQL_DATABASE');

$rabbitMqHost = getenv('RABBITMQ_HOST');
$rabbitMqPort = (int) getenv('RABBITMQ_PORT');
$rabbitMqUser = getenv('RABBITMQ_USER');
$rabbitMqPassword = getenv('RABBITMQ_PASSWORD');
$rabbitMqExchangeName = getenv('RABBITMQ_EXCHANGE_NAME');

$mysqli = new mysqli($mysqlHost, $mysqlPort, $mysqlUser, $mysqlPassword, $mysqlDatabase);

$projectUniqueIdGenerator = new ProjectUniqueIdGenerator();
$projectsUsersRepository = new MysqlProjectsUsersRepository($mysqli);
$projectsRepository = new MysqlProjectsRepository($mysqli, $projectsUsersRepository);

$connection = new AMQPStreamConnection(
	$rabbitMqHost,
	$rabbitMqPort,
	$rabbitMqUser,
	$rabbitMqPassword
);
$channel = $this->connection->channel();

$eventSender = new EventSender($channel, $rabbitMqExchangeName );

$methods = new EndpointsHandler(
	$projectUniqueIdGenerator,
	$projectsUsersRepository,
	$projectsRepository,
	$eventSender
);
$server = new JsonRpc\Server($methods);
$server->setObjectsAsArrays();
$server->receive();
