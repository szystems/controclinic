#!/bin/sh
set -e

echo "==> Worker waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
while ! nc -z "${DB_HOST}" "${DB_PORT:-3306}" 2>/dev/null; do
    sleep 2
done

echo "==> Worker waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
while ! nc -z "${REDIS_HOST}" "${REDIS_PORT:-6379}" 2>/dev/null; do
    sleep 2
done

echo "==> Worker ready. Executing: $*"
exec "$@"
