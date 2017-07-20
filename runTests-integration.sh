#!/usr/bin/env bash

#docker run -d --name mysql-for-tests -p="3306:3306" -e MYSQL_ROOT_PASSWORD=root -eMYSQL_DATABASE=testdb mysql:8.0.1
#docker run -d --name rabbitmq-for-tests -p="5672:5672" rabbitmq:3.6.10

## I know it's not perfect solution
## TODO: improve that
for i in {1..12}
do
    echo $i
    sleep 5
done

# how to check is working
#docker exec rabbitmq-for-tests service --status-all | grep 'rabbitmq-server' | grep '+' | wc -l

./vendor/bin/phpunit ./tests/integration

#docker stop mysql-for-tests
#docker stop rabbitmq-for-tests
#
#docker rm mysql-for-tests
#docker rm rabbitmq-for-tests
