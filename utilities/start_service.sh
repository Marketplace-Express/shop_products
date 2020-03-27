#!/usr/bin/env bash
# Products service
# Install dependencies and run workers
php composer.phar config -g github-oauth.github.com 3f6fd65b0d7958581f549b862ee49af9db1bcdf1 && \
php composer.phar install --no-cache

# Make symlink to Phalcon bin
ln -fs /var/www/html/app/common/library/vendor/bin/phalcon /usr/local/bin && \

echo 'y' | phalcon migration --action=run --migrations=app/migrations/

curl -s http://${GATEWAY_IP}:${CONFIG_SERVICE_PORT}/ -o app/config/remote_config.json >> /dev/null