#
# Shop products service Dockerfile
# Copyrights to MilesChou <github.com/MilesChou>, fizzka <github.com/fizzka>
# Edited by Wajdi Jurry <github.com/wajdijurry>
#
FROM php:7.3-fpm

LABEL maintainer="MilesChou <github.com/MilesChou>, fizzka <github.com/fizzka>"

ARG PSR_VERSION=0.7.0
ARG PHALCON_VERSION=3.4.4
ARG PHALCON_EXT_PATH=php7/64bits

RUN set -xe && \
        # Download PSR, see https://github.com/jbboehr/php-psr
        curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz && \
        tar xzf ${PWD}/v${PSR_VERSION}.tar.gz && \
        # Download Phalcon
        curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz && \
        tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz && \
        docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
            ${PWD}/php-psr-${PSR_VERSION} \
            ${PWD}/cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH} \
        && \
        # Remove all temp files
        rm -r \
            ${PWD}/v${PSR_VERSION}.tar.gz \
            ${PWD}/php-psr-${PSR_VERSION} \
            ${PWD}/v${PHALCON_VERSION}.tar.gz \
            ${PWD}/cphalcon-${PHALCON_VERSION} && \
        # Install environment dependencies
        apt-get -y update && \
        apt-get install -y libfreetype6-dev libpng-dev libjpeg-dev libcurl4-gnutls-dev libyaml-dev libicu-dev libzip-dev unzip && \
        # Install required PHP extensions
        docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
        docker-php-ext-configure gd --with-freetype-dir=/usr/include/ \
                                         --with-png-dir=/usr/include/ \
                                         --with-jpeg-dir=/usr/include/ && \
        docker-php-ext-install intl gettext gd bcmath zip pdo_mysql sockets && \
        # Install extra extensions
        echo '' | pecl install redis mongodb xdebug
# Return working directory to its default state
WORKDIR /src
# Copy project files to container
ADD . ./
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Install dependencies
RUN rm -rf app/vendor composer.lock && \
    composer clearcache && \
    composer config -g github-oauth.github.com 3f6fd65b0d7958581f549b862ee49af9db1bcdf1 && \
    composer install --ignore-platform-reqs
# Create symlink for phalcon bin
RUN ln -fs /src/app/vendor/bin/phalcon /usr/local/bin
# Rin migrations
RUN echo 'y' | phalcon migration --action=run --migrations=app/migrations/
ENTRYPOINT ["/bin/bash", "utilities/docker-entrypoint.sh"]