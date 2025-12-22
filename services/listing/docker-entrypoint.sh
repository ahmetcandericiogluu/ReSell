#!/bin/bash
set -e

echo "🚀 Starting Listing Service..."

# Ensure we're in the app directory
cd /var/www/html

# Export environment variables
export APP_ENV="${APP_ENV:-prod}"
export DATABASE_URL="${DATABASE_URL}"

echo "📋 Environment: APP_ENV=$APP_ENV"

# Create .env.local file for Symfony
cat > .env.local << ENVEOF
APP_ENV=${APP_ENV}
APP_SECRET=${APP_SECRET}
DATABASE_URL=${DATABASE_URL}
CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-*}
ENVEOF

# Clear and warmup cache
echo "🧹 Clearing cache..."
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod --no-warmup 2>&1 || true

# Wait for database to be ready
echo "⏳ Waiting for database..."
max_retries=15
retry=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  retry=$((retry + 1))
  if [ $retry -ge $max_retries ]; then
    echo "⚠️ Database connection failed after $max_retries attempts, continuing anyway..."
    break
  fi
  echo "Waiting for database... ($retry/$max_retries)"
  sleep 2
done

echo "✅ Database is ready!"

# Run migrations
echo "🗄️ Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Load fixtures (categories) - only if categories table is empty
echo "📦 Loading fixtures..."
php bin/console doctrine:fixtures:load --no-interaction --append || true

# Set correct permissions
chown -R www-data:www-data /var/www/html/var

# Start Apache
echo "🌐 Starting Apache..."
exec apache2-foreground

