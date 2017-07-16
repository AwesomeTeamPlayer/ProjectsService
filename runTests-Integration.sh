#!/usr/bin/env bash

docker run -d --name mysql-for-tests -p="3306:3306" -e MYSQL_ROOT_PASSWORD=root mysql:8.0.1
docker run -d --hostname my-rabbit --name rabbitmq-for-tests rabbitmq:3.6.10

./vendor/bin/phpunit ./tests/integration

docker stop mysql-for-tests
docker stop rabbitmq-for-tests

docker rm mysql-for-tests
docker rm rabbitmq-for-tests

