<?php

require __DIR__ . '/../vendor/autoload.php';

class EndpointsHandler
{
	public $error = null;

	public function create($name, $type, $userIds)
	{
		return $this->execute(new CreateEndpoint(), [
			'name' => $name,
			'type' => $type,
			'userIds' => $userIds,
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
$methods = new EndpointsHandler();
$server = new JsonRpc\Server($methods);
$server->setObjectsAsArrays();
$server->receive();
