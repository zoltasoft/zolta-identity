#!/bin/sh
set -eu

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage
if [ ! -e public/storage ]; then
  ln -s ../storage/app/public public/storage
fi

exec "$@"
