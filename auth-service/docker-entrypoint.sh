#!/bin/bash
set -e

echo "🚀 Starting Auth Service..."

# Debug: Print environment
echo "📋 Environment Debug:"
echo "DATABASE_URL: ${DATABASE_URL:0:30}... (truncated for security)"
echo "APP_ENV: $APP_ENV"
echo "PORT: $PORT"
echo ""

# Run detailed connection test first
echo "🔍 Running detailed connection test..."
php /var/www/html/test-db-connection.php
TEST_RESULT=$?

if [ $TEST_RESULT -eq 0 ]; then
  echo "✅ Direct PDO connection successful!"
else
  echo "❌ Direct PDO connection failed! Check logs above."
  exit 1
fi

echo ""

# Wait for database to be ready via Doctrine
echo "⏳ Testing Doctrine connection..."
MAX_RETRIES=10
RETRY_COUNT=0
until php bin/console doctrine:query:sql "SELECT 1" 2>&1; do
  RETRY_COUNT=$((RETRY_COUNT+1))
  if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "❌ Doctrine connection failed after $MAX_RETRIES attempts"
    echo "📋 Re-running connection test for debugging..."
    php /var/www/html/test-db-connection.php
    exit 1
  fi
  echo "⚠️  Doctrine unavailable - sleeping (attempt $RETRY_COUNT/$MAX_RETRIES)"
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

