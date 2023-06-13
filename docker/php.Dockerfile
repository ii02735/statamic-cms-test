FROM php:8.1-cli

RUN apt-get update
RUN apt-get install git libmcrypt-dev libz-dev libpng-dev libzip-dev zip unzip -y

RUN apt-get update && apt-get install -y libmcrypt-dev \
    && pecl install mcrypt-1.0.5 \
    && docker-php-ext-enable mcrypt 

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN docker-php-ext-install gd zip

COPY docker/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY --from=composer /usr/bin/composer /usr/bin/composer
