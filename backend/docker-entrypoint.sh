#!/bin/bash
set -e

echo "Starting Backend Service..."

# Render copies files to /app, not /var/www/html
APP_DIR="/app"
if [ -d "/app/bin" ]; then
    APP_DIR="/app"
elif [ -d "/var/www/html/bin" ]; then
    APP_DIR="/var/www/html"
else
    echo "ERROR: Cannot find application directory!"
    echo "Checking /app:" && ls -la /app 2>/dev/null || echo "/app not found"
    echo "Checking /var/www/html:" && ls -la /var/www/html 2>/dev/null || echo "/var/www/html not found"
    exit 1
fi

echo "Using APP_DIR: $APP_DIR"
cd "$APP_DIR"

# Create .env.local from environment variables
echo "APP_ENV=${APP_ENV:-prod}" > .env.local
echo "APP_SECRET=${APP_SECRET}" >> .env.local
echo "DATABASE_URL=${DATABASE_URL}" >> .env.local
echo "CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-*}" >> .env.local
echo "R2_ENDPOINT=${R2_ENDPOINT}" >> .env.local
echo "R2_REGION=${R2_REGION}" >> .env.local
echo "R2_BUCKET=${R2_BUCKET}" >> .env.local
echo "R2_ACCESS_KEY_ID=${R2_ACCESS_KEY_ID}" >> .env.local
echo "R2_SECRET_ACCESS_KEY=${R2_SECRET_ACCESS_KEY}" >> .env.local
echo "R2_PUBLIC_BASE_URL=${R2_PUBLIC_BASE_URL}" >> .env.local

echo "Created .env.local"

# Clear cache
rm -rf var/cache/prod/* 2>/dev/null || true
php bin/console cache:clear --env=prod --no-warmup 2>&1 || echo "Cache clear skipped"

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

echo "Database check complete!"

# Run migrations
php bin/console doctrine:migrations:sync-metadata-storage --no-interaction 2>&1 || true
php bin/console doctrine:migrations:version --add --all --no-interaction 2>&1 || true
php bin/console doctrine:schema:update --force --no-interaction 2>&1 || true

# Ensure var directory exists and set permissions
mkdir -p "$APP_DIR/var/cache" "$APP_DIR/var/log"
chown -R www-data:www-data "$APP_DIR/var" 2>/dev/null || true

# Update Apache document root to /app/public
sed -ri -e "s|/var/www/html/public|$APP_DIR/public|g" /etc/apache2/sites-available/*.conf 2>/dev/null || true
sed -ri -e "s|/var/www/html|$APP_DIR|g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf 2>/dev/null || true

echo "Starting Apache on port 8080..."
exec /usr/sbin/apache2ctl -D FOREGROUND
