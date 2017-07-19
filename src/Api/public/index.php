<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../vendor/autoload.php';

$applicationConfig = new \Api\ApplicationConfig(
	[
		'redis' => [
			'host' => '127.0.0.1',
			'port' => 5672,
			'login' => 'guest',
			'password' => 'guest',
			'channel' => 'events',
		],
		'mysql' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'login' => 'root',
			'password' => 'root',
			'database' => 'testdb',
		],
	]
);
$applicationBuilder = new \Api\ApplicationBuilder();

$applicationBuilder->build($applicationConfig)->run();
