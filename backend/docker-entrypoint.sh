#!/bin/bash
set -e

echo "Starting Backend Service..."

PORT="${PORT:-80}"
if ! echo "$PORT" | grep -Eq '^[0-9]+$'; then
  echo "Invalid PORT: '$PORT'"
  exit 1
fi

# (Optional) Run migrations - keep if you want
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

# Configure Apache to listen on Render PORT
echo "Listen ${PORT}" > /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true

echo "Starting web server..."
exec apache2-foreground