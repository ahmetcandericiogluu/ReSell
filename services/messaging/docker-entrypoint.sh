#!/bin/bash
set -e

echo "Starting Messaging Service..."

APP_DIR="/var/www/html"
cd "$APP_DIR"

# Create .env.local file for Symfony
cat > .env.local << ENVEOF
APP_ENV=${APP_ENV:-prod}
APP_SECRET=${APP_SECRET}
JWT_SECRET=${JWT_SECRET}
DATABASE_URL=${DATABASE_URL}
CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-*}
AUTH_SERVICE_URL=${AUTH_SERVICE_URL}
LISTING_SERVICE_URL=${LISTING_SERVICE_URL}
PUSHER_APP_ID=${PUSHER_APP_ID:-}
PUSHER_KEY=${PUSHER_KEY:-}
PUSHER_SECRET=${PUSHER_SECRET:-}
PUSHER_CLUSTER=${PUSHER_CLUSTER:-eu}
ENVEOF

echo "Created .env.local"

# Clear and warm up cache
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod 2>&1 || true
php bin/console cache:warmup --env=prod 2>&1 || true

# Wait for database
max_retries=15
retry=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  retry=$((retry + 1))
  if [ $retry -ge $max_retries ]; then
    echo "Database connection failed, continuing..."
    break
  fi
  echo "Waiting for database... ($retry/$max_retries)"
  sleep 2
done

echo "Database is ready!"

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

# Set permissions
chown -R www-data:www-data "$APP_DIR/var" 2>/dev/null || true

# Configure Apache to listen on PORT (default 80)
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    echo "Configuring Apache to listen on port $PORT..."
    sed -ri -e "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf 2>/dev/null || true
    sed -ri -e "s/:80>/:$PORT>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true
fi

echo "Starting web server on port ${PORT:-80}..."
exec apache2-foreground

