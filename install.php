<?php

echo "\n\nStart installation";

$host = getenv('MYSQL_HOST');
$port = (int) getenv('MYSQL_PORT');
$user = getenv('MYSQL_LOGIN');
$password = getenv('MYSQL_PASSWORD');
$database = getenv('MYSQL_DATABASE');

$mysqli = new mysqli(
	$host,
	$user,
	$password,
	$database,
	$port
);


$dirPath = __DIR__ . '/migrations';
foreach (scandir($dirPath) as $fileName) {
	if ($fileName === '.' || $fileName === '..') {
		continue;
	}

	$mysqli->multi_query(file_get_contents($dirPath . $fileName));
}

echo "Installation finished successfully\n\n";

