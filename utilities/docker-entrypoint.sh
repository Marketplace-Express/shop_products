#!/usr/bin/env bash

# Copy PHP extensions configurations to container
echo "Copying php extensions to container ..."
cp -a php_extensions/. /usr/local/etc/php/conf.d/

echo "Run migrations ..."
if [[ "${UNIT_TEST}" ]]; then
  phalcon migration run --migrations=tests/migrations/
else
  phalcon migration run --migrations=app/migrations/
fi

exec "$@"