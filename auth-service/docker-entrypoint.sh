#!/bin/bash
set -e

echo "🚀 Starting Auth Service..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
MAX_RETRIES=30
RETRY_COUNT=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  RETRY_COUNT=$((RETRY_COUNT+1))
  if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "❌ Database connection failed after $MAX_RETRIES attempts"
    exit 1
  fi
  echo "Database is unavailable - sleeping (attempt $RETRY_COUNT/$MAX_RETRIES)"
  sleep 3
done

echo "✅ Database is ready!"

# Run migrations
echo "🗄️ Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear and warm up cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Set correct permissions
chown -R www-data:www-data /var/www/html/var

# Start Apache
echo "🌐 Starting Apache on port ${PORT:-8080}..."
exec apache2-foreground

