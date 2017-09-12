#!/usr/bin/env bash

which nc >/dev/null || { apt-get update && apt-get install -y netcat; }
while ! nc -z ${ES_HOST} ${ES_PORT}; do
  echo >&2 "Waiting for elasticsearch to be ready" && sleep 1
done
php init/es-index.php
php init/es-mapping.php
php init/es-data.php
exec php -S 0.0.0.0:8080
