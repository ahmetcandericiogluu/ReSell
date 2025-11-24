#!/bin/bash
set -e

echo "Starting ReSell application..."

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

# Warm up cache
echo "Warming up cache..."
php bin/console cache:warmup --env=prod || true

echo "Starting web server..."
exec "$@"

