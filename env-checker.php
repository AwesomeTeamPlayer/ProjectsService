#!/usr/bin/env php
<?php

/*
 * This file is responsible for checking test env status.
 * If env is ready this command will return code = 0.
 * If env is NOT ready this command will return code = 1.
 */

$mysqli = @(new mysqli('127.0.0.1', 'root', 'root', 'testdb', 13306));

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
