<?php

use GuzzleHttp\Psr7\Uri;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;

class DefaultEndpointTest extends AbstractEndToEndTest
{
	public function test_default_endpoint()
	{
		$request = new Request(
			'GET',
			new Uri('http://google.com/'),
			new Headers(),
			[],
			[],
			new RequestBody()
		);
		$response = new Response();

		$this->app->process($request, $response);

		$this->assertEquals(
			'{"type":"projects-service","config":{"redis":{"host":"127.0.0.1","port":5672,"login":"guest","password":"guest","channel":"events"},"mysql":{"host":"127.0.0.1","port":3306,"login":"root","password":"root","database":"testdb"}}}',
			$response->getBody()->__toString()
		);
	}
}
