#!/bin/bash
set -e

echo "Starting Messaging Service (Dev)..."

cd /var/www/messaging

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction
fi

# Wait for database
echo "Waiting for database..."
sleep 3

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

# Clear cache
php bin/console cache:clear 2>&1 || true

echo "Starting PHP development server on port 8000..."
exec php -S 0.0.0.0:8000 -t public

