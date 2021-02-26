ARG version=cli
FROM php:$version

COPY . /var/www
WORKDIR /var/www

RUN apt-get update
RUN apt-get install -y zip unzip zlib1g-dev
RUN if [[ `php-config --vernum` -ge 73000 ]]; then docker-php-ext-install zip; fi
RUN docker-php-ext-install pcntl
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer install
