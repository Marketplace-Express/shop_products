#!/usr/bin/env bash
#
# Products service
#

# Make symlink to Phalcon bin
ln -fs /var/www/html/app/common/library/vendor/bin/phalcon /usr/local/bin && \

echo 'y' | phalcon migration --action=run --migrations=app/migrations/

curl -s http://${GATEWAY_IP}:${CONFIG_SERVICE_PORT}/ -o app/config/remote_config.json >> /dev/null