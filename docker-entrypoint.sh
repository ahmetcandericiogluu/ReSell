#!/bin/bash
set -e

echo "==> Starting ReSell application..."

# Wait for database
echo "==> Waiting for database..."
sleep 5

# Run migrations
echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "No migrations to run"

# Clear and warm cache
echo "==> Warming cache..."
php bin/console cache:clear --env=prod --no-warmup || true
php bin/console cache:warmup --env=prod || true

# Set PORT default
export PORT=${PORT:-10000}

echo "==> Starting web server on port $PORT..."
exec php -S 0.0.0.0:$PORT -t public

