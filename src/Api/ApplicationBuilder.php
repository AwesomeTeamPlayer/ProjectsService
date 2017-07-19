<?php

namespace Api;

use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class ApplicationBuilder
{
	/**
	 * @param ApplicationConfig $applicationConfig
	 *
	 * @return App
	 */
	public function build(ApplicationConfig $applicationConfig) : App
	{
		$app = new App(new Container(
			[
				'settings' => [
					'displayErrorDetails' => true,
				],
			]
		));

		$app->get('/', function (Request $request, Response $response, $args) use ($applicationConfig) {
			$response->write(json_encode(
				[
					'type' => 'projects-service',
					'config' => $applicationConfig->getArray()
				]
			));
		});

		return $app;
	}
}
