#!/bin/bash
set -e

echo "Starting Backend Service..."

# Clear cache
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod --no-warmup 2>&1 || true

# Run migrations (fails gracefully if DB not ready)
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

echo "Starting web server..."
exec apache2-foreground
