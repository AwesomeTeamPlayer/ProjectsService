<?php

echo "\n\nStart installation\n\n";

$host = getenv('MYSQL_HOST');
$port = (int) getenv('MYSQL_PORT');
$user = getenv('MYSQL_LOGIN');
$password = getenv('MYSQL_PASSWORD');
$database = getenv('MYSQL_DATABASE');

echo " - Host: " . $host . "\n";
echo " - Port: " . $port . "\n";
echo " - User: " . $user . "\n";
echo " - Password: " . $password . "\n";
echo " - Database: " . $database . "\n";

while(true) {
	sleep(1);
	echo "Try no " . $i . "\n\n";

	$mysqli = new mysqli(
		$host,
		$user,
		$password,
		$database,
		$port
	);


	if ($mysqli->connect_errno) {
		echo "Can not connect to the database";
		continue;
	}

	if ($mysqli->ping() === false) {
		echo "Can not send a ping to the database";
		continue;
	}

	$dirPath = __DIR__ . '/migrations/';
	foreach (scandir($dirPath) as $fileName) {
		if ($fileName === '.' || $fileName === '..') {
			continue;
		}

		$mysqli->multi_query(file_get_contents($dirPath . $fileName));
	}

	echo "\n\nInstallation finished successfully\n\n";
	break;
}
