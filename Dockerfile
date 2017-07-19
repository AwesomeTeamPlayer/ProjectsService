FROM php:7.1

ADD . /app
WORKDIR /app

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt -y install php7.0-mbstring
RUN apt -y install php-bcmath
