#!/bin/bash
set -e

echo "🚀 Starting Auth Service..."

# Ensure environment variables are exported
export DATABASE_URL="${DATABASE_URL}"
export APP_ENV="${APP_ENV:-prod}"
export APP_SECRET="${APP_SECRET}"
export PORT="${PORT:-8080}"

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

# Create .env.local file for Symfony to read runtime environment variables
echo "📝 Creating .env.local for runtime environment..."

# Ensure we're in the app directory
cd /var/www/html

# Create .env.local file (Symfony DotEnv will read this)
cat > .env.local << ENVEOF
APP_ENV=${APP_ENV}
APP_SECRET=${APP_SECRET}
DATABASE_URL=${DATABASE_URL}
CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-*}
ENVEOF

echo "📝 Created .env.local at: $(pwd)/.env.local"
echo "📝 File exists: $(test -f .env.local && echo 'YES' || echo 'NO')"
echo "📝 Content preview:"
echo "DATABASE_URL: ${DATABASE_URL:0:40}..."
echo "APP_ENV: ${APP_ENV}"

# Clear cache FIRST (before database check)
echo "🧹 Clearing Symfony cache..."
rm -rf var/cache/prod/*
# Don't warmup - let Symfony build cache at runtime with actual env vars
php bin/console cache:clear --env=prod --no-warmup --no-optional-warmers 2>&1 || true

# Wait for database to be ready via Doctrine
echo "⏳ Testing Doctrine connection..."
echo "📋 Checking if Symfony can see DATABASE_URL..."
php bin/console debug:container --parameters | grep -i database || echo "⚠️  DATABASE_URL not found in container"
echo ""
echo "📋 Checking Doctrine configuration before connection..."
php bin/console debug:config doctrine dbal 2>&1 | head -30

MAX_RETRIES=10
RETRY_COUNT=0
until php bin/console doctrine:query:sql "SELECT 1" 2>&1; do
  RETRY_COUNT=$((RETRY_COUNT+1))
  if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "❌ Doctrine connection failed after $MAX_RETRIES attempts"
    echo ""
    echo "📋 Re-running connection test for debugging..."
    php /var/www/html/test-db-connection.php
    echo ""
    echo "📋 Full Doctrine DBAL configuration:"
    php bin/console debug:config doctrine dbal
    echo ""
    echo "📋 Trying verbose connection..."
    php bin/console doctrine:query:sql "SELECT 1" -vvv
    exit 1
  fi
  echo "⚠️  Doctrine unavailable - sleeping (attempt $RETRY_COUNT/$MAX_RETRIES)"
  sleep 3
done

echo "✅ Database is ready!"

# Run migrations
echo "🗄️ Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Skip cache warmup - runtime will build at runtime with correct env vars
echo "✅ Skipping cache warmup - will build at runtime"

# Set correct permissions
chown -R www-data:www-data /var/www/html/var

# Start Apache
echo "🌐 Starting Apache on port ${PORT:-8080}..."
exec apache2-foreground

