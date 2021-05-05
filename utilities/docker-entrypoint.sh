#!/usr/bin/env bash

# Copy PHP extensions configurations to container
echo "Copying php extensions to container ..."
cp -a php_extensions/. /usr/local/etc/php/conf.d/

php -r "var_dump($_ENV['MYSQL_HOST']);"
exit

echo "Run migrations ..."
phalcon migration --action=run --migrations=app/migrations/

exec "$@"