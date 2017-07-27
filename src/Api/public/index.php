<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../vendor/autoload.php';

$applicationConfig = new \Api\ApplicationConfig(
	[
		'rabbitmq' => [
			'host' => urlencode(getenv('RABBIT_HOST')),
			'port' => (int) urlencode(getenv('RABBIT_PORT')),
			'user' => urlencode(getenv('RABBIT_LOGIN')),
			'password' => urlencode(getenv('RABBIT_PASSWORD')),
			'channel' => urlencode(getenv('RABBIT_CHANNEL')),
		],
		'mysql' => [
			'host' => urlencode(getenv('MYSQL_HOST')),
			'port' => (int) urlencode(getenv('MYSQL_PORT')),
			'user' => urlencode(getenv('MYSQL_LOGIN')),
			'password' => urlencode(getenv('MYSQL_PASSWORD')),
			'database' => urlencode(getenv('MYSQL_DATABASE')),
		],
	]
);
$applicationBuilder = new \Api\ApplicationBuilder();

$applicationBuilder->build($applicationConfig)->run();
