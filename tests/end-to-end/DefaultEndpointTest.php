<?php

class DefaultEndpointTest extends AbstractEndToEndTest
{
	public function test_default_endpoint()
	{
		$this->assertEquals(
			'{"type":"projects-service","config":{"rabbitmq":{"host":"127.0.0.1","port":15672,"user":"guest","password":"guest","channel":"events"},"mysql":{"host":"127.0.0.1","port":13306,"user":"root","password":"root","database":"testdb"}},"status":{"is_connected":{"MySQL":true,"RabbitMQ":true}}}',
			$this->makeRequest('GET', '/')
		);
	}
}
