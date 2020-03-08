#
# Shop products service Dockerfile
# Copyrights to MilesChou <github.com/MilesChou>, fizzka <github.com/fizzka>
# Edited by Wajdi Jurry <github.com/wajdijurry>
#
FROM php:7.3-apache

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
            ${PWD}/cphalcon-${PHALCON_VERSION} \
        && php -m
RUN mkdir /etc/shop
COPY ./utilities/setup_service.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/setup_service.sh
RUN sh /usr/local/bin/setup_service.sh
COPY ./utilities/shop_products_workers.conf /etc/supervisor/conf.d