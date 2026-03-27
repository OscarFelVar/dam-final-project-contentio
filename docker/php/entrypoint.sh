#!/bin/sh
set -e

echo "[entrypoint] Warming up Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan event:cache
echo "[entrypoint] Cache ready. Starting php-fpm..."

exec php-fpm
