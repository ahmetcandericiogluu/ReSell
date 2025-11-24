#!/usr/bin/env bash
# exit on error
set -o errexit

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear cache
php bin/console cache:clear --env=prod --no-debug

# Install assets
php bin/console asset-map:compile

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

