#!/bin/sh
set -e

echo "🚀 Starting Backend (DEV)..."

cd /app

# Install dependencies if needed
if [ ! -d "vendor" ]; then
  echo "📦 Installing dependencies..."
  composer install --no-interaction
fi

# Wait for database
echo "⏳ Waiting for database..."
sleep 5

# Run migrations
echo "🗄️ Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

# Start PHP development server
echo "🌐 Starting PHP server on port 8000..."
exec php -S 0.0.0.0:8000 -t public/

