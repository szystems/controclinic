#!/bin/sh
set -e

echo "==> Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
while ! nc -z "${DB_HOST}" "${DB_PORT:-3306}" 2>/dev/null; do
    sleep 2
done
echo "==> MySQL is ready."

php artisan storage:link --force --relative || php artisan storage:link --force || true
php artisan migrate --force

echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Setting storage permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Ready. Executing: $*"
exec "$@"
