#!/bin/sh
set -e

cd /app

if [ ! -d vendor ]; then
  composer install --prefer-dist --no-interaction
fi

mkdir -p var/cache var/log
chmod -R 777 var || true

if [ "${APP_RUN_MIGRATIONS:-1}" = "1" ]; then
  php bin/console doctrine:migrations:migrate --no-interaction || true
fi

exec "$@"
