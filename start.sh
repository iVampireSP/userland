#!/bin/bash

touch /app/storage/logs/laravel.log && tail -f /app/storage/logs/laravel.log > /proc/1/fd/2 &

php artisan $@