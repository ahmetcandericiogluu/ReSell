#!/bin/bash
set -e

echo "🚀 Starting ReSell application..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done

echo "✅ Database is ready!"

# Run migrations
echo "🗄️ Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear and warm up cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Start PHP built-in server
echo "🌐 Starting web server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
