#!/usr/bin/env bash

php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear

php artisan reverb:start --host=0.0.0.0 --port=8080 &

php -S 0.0.0.0:$PORT -t public