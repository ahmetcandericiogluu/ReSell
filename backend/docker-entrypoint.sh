#!/bin/bash
set -e

echo "Starting Backend Service..."

# Clear and warm up cache
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod 2>&1 || true
php bin/console cache:warmup --env=prod 2>&1 || true

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

echo "Starting web server..."
exec apache2-foreground
