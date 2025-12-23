#!/bin/bash
set -e

echo "=== DEBUG: Starting Backend Service ==="
echo "PWD: $(pwd)"
echo "Files in current dir:"
ls -la
echo "=== END DEBUG ==="

cd /var/www/html

echo "=== DEBUG: After cd /var/www/html ==="
echo "PWD: $(pwd)"
echo "Files:"
ls -la
echo "bin/ contents:"
ls -la bin/ 2>/dev/null || echo "bin/ not found"
echo "=== END DEBUG ==="

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
php bin/console cache:clear --env=prod --no-warmup 2>&1 || echo "Cache clear failed, continuing..."

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
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var

echo "Starting Apache..."
exec /usr/sbin/apache2ctl -D FOREGROUND
