#!/bin/bash
set -e

APP_DIR="/var/www/html"
cd "$APP_DIR"

echo "Starting service..."

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"

PORT="${PORT:-80}"
if ! echo "$PORT" | grep -Eq '^[0-9]+$'; then
  echo "Invalid PORT: '$PORT'"
  exit 1
fi

echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

# Clear and warmup cache for production
rm -rf var/cache/prod/* 2>/dev/null || true
php bin/console cache:clear --env=prod 2>&1 || true
php bin/console cache:warmup --env=prod 2>&1 || true

# Set permissions
chown -R www-data:www-data var 2>/dev/null || true

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

echo "Starting Apache on port ${PORT} (APP_ENV=${APP_ENV}, APP_DEBUG=${APP_DEBUG})..."
exec apache2-foreground
