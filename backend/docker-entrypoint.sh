#!/bin/bash
set -e

echo "Starting Backend Service..."

# Detect app directory (Render uses /app, Docker uses /var/www/html)
if [ -f "/app/bin/console" ]; then
    APP_DIR="/app"
elif [ -f "/var/www/html/bin/console" ]; then
    APP_DIR="/var/www/html"
else
    echo "ERROR: Cannot find application!"
    echo "Checking /app:" && ls -la /app 2>/dev/null || echo "/app not found"
    echo "Checking /var/www/html:" && ls -la /var/www/html 2>/dev/null || echo "/var/www/html not found"
    exit 1
fi

echo "Using APP_DIR: $APP_DIR"
cd "$APP_DIR"

# Create .env.local file for Symfony
cat > .env.local << ENVEOF
APP_ENV=${APP_ENV:-prod}
APP_SECRET=${APP_SECRET}
DATABASE_URL=${DATABASE_URL}
CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-*}
R2_ENDPOINT=${R2_ENDPOINT}
R2_REGION=${R2_REGION}
R2_BUCKET=${R2_BUCKET}
R2_ACCESS_KEY_ID=${R2_ACCESS_KEY_ID}
R2_SECRET_ACCESS_KEY=${R2_SECRET_ACCESS_KEY}
R2_PUBLIC_BASE_URL=${R2_PUBLIC_BASE_URL}
ENVEOF

echo "Created .env.local"

# Clear cache
rm -rf var/cache/prod/*
php bin/console cache:clear --env=prod --no-warmup 2>&1 || true

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

# Update Apache config if using /app
if [ "$APP_DIR" = "/app" ]; then
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/sites-available/*.conf 2>/dev/null || true
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/apache2.conf 2>/dev/null || true
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/sites-enabled/*.conf 2>/dev/null || true
    # Set DocumentRoot via environment
    export APACHE_DOCUMENT_ROOT="$APP_DIR/public"
fi

echo "Starting Apache..."
# Find and run apache
if command -v apache2-foreground >/dev/null 2>&1; then
    exec apache2-foreground
elif [ -x /usr/local/bin/apache2-foreground ]; then
    exec /usr/local/bin/apache2-foreground
elif command -v apachectl >/dev/null 2>&1; then
    exec apachectl -D FOREGROUND
elif command -v httpd >/dev/null 2>&1; then
    exec httpd -D FOREGROUND
elif [ -x /usr/sbin/apache2 ]; then
    . /etc/apache2/envvars
    exec /usr/sbin/apache2 -D FOREGROUND
else
    echo "ERROR: No Apache found!"
    which apache2 httpd apachectl 2>/dev/null || true
    exit 1
fi
