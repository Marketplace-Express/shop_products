#!/usr/bin/env bash
# Products service
# Install required tools
apt-get -y update \
&& apt-get install -y libfreetype6-dev libpng-dev libjpeg-dev libcurl4-gnutls-dev libyaml-dev libicu-dev libzip-dev unzip supervisor
# Install required PHP extensions
docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd
docker-php-ext-configure gd --with-freetype-dir=/usr/include/ \
                                   --with-png-dir=/usr/include/ \
                                   --with-jpeg-dir=/usr/include/
docker-php-ext-install intl gettext gd bcmath zip pdo_mysql sockets

# Install extra extensions
echo '' | pecl install redis mongodb yaml
echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini
echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini
echo "extension=yaml.so" > /usr/local/etc/php/conf.d/yaml.ini
