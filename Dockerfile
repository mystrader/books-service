FROM php:8.4-cli

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www
