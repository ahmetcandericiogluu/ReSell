#!/bin/bash

# Exit on error
set -e

echo "🔧 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🗄️ Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "🧹 Clearing cache..."
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

echo "✅ Build completed successfully!"
