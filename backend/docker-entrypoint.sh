#!/bin/bash
set -e

echo "🚀 Starting ReSell application..."

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
php /app/test-db-connection.php
TEST_RESULT=$?

if [ $TEST_RESULT -eq 0 ]; then
  echo "✅ Direct PDO connection successful!"
else
  echo "❌ Direct PDO connection failed! Check logs above."
  exit 1
fi

echo ""

# Create .env.local.php for Symfony to read runtime environment variables
echo "📝 Creating .env.local.php for runtime environment..."
cat > .env.local.php << 'EOF'
<?php
return [
    'APP_ENV' => $_ENV['APP_ENV'] ?? 'prod',
    'APP_SECRET' => $_ENV['APP_SECRET'] ?? '',
    'DATABASE_URL' => $_ENV['DATABASE_URL'] ?? '',
    'CORS_ALLOW_ORIGIN' => $_ENV['CORS_ALLOW_ORIGIN'] ?? '*',
    'R2_ENDPOINT' => $_ENV['R2_ENDPOINT'] ?? '',
    'R2_REGION' => $_ENV['R2_REGION'] ?? '',
    'R2_BUCKET' => $_ENV['R2_BUCKET'] ?? '',
    'R2_ACCESS_KEY_ID' => $_ENV['R2_ACCESS_KEY_ID'] ?? '',
    'R2_SECRET_ACCESS_KEY' => $_ENV['R2_SECRET_ACCESS_KEY'] ?? '',
    'R2_PUBLIC_BASE_URL' => $_ENV['R2_PUBLIC_BASE_URL'] ?? '',
];
EOF

# Clear cache FIRST (before database check)
echo "🧹 Clearing Symfony cache..."
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod --no-warmup || true

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
    php /app/test-db-connection.php
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

# Warm up cache
echo "🔥 Warming up cache..."
php bin/console cache:warmup --env=prod

# Start PHP built-in server
echo "🌐 Starting web server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
