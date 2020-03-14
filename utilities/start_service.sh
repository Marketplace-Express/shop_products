#!/usr/bin/env bash
# Products service
# Install dependencies and run workers
php composer.phar install && \

echo 'y' | app/common/library/vendor/bin/phalcon migration --action=run --config=app/config/config.yml --migrations=app/migrations/