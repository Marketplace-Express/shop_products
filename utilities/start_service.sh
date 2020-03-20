#!/usr/bin/env bash
# Products service
# Install dependencies and run workers
php composer.phar install && \

# Make symlink to Phalcon bin
ln -fs /var/www/html/app/common/library/vendor/bin/phalcon /usr/local/bin && \

echo 'y' | phalcon migration --action=run --migrations=app/migrations/