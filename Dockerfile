FROM php:8.3-cli
COPY . /usr/src/ppm
COPY php.ini /usr/local/etc/php
WORKDIR /usr/src/ppm
RUN php builders/in_linux.php


