#!/usr/bin/env php
<?php

/*
 * This file is responsible for checking test env status.
 * If env is ready this command will return code = 0.
 * If env is NOT ready this command will return code = 1.
 */

$host = $argv[1];
$port = (int) $argv[2];
$username = $argv[3];
$password = $argv[4];
$database = $argv[5];

$mysqli = @(new mysqli($host, $username, $password, $database, $port));

if ($mysqli->connect_errno) {
    echo " - Database is not ready";
	exit(1);
}

if ($mysqli->ping()) {
	echo " - Database is ready";
} else {
	echo " - Can not ping database";
	exit(1);
}

$mysqli->close();

//  todo: check RabbitMQ connection

exit(0);
