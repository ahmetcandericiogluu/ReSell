#!/bin/bash
set -e

echo "Starting Listing Service..."

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
ELASTICSEARCH_URL=${ELASTICSEARCH_URL:-}
ELASTICSEARCH_API_KEY=${ELASTICSEARCH_API_KEY:-}
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

# Run migrations only (no schema:update to avoid dropping shared tables)
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 || true

# Add missing columns manually (safe approach for microservices)
echo "Checking for missing columns..."
php bin/console doctrine:query:sql "ALTER TABLE listings ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP DEFAULT NULL" 2>&1 || true
php bin/console doctrine:query:sql "ALTER TABLE listings ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT NULL" 2>&1 || true

# Elasticsearch: Check if index exists, create only if missing
if [ -n "$ELASTICSEARCH_URL" ]; then
    echo "Checking Elasticsearch index..."
    # Extract host from URL (remove credentials if present)
    ES_HOST=$(echo "$ELASTICSEARCH_URL" | sed -E 's|https?://([^@]*@)?||' | sed 's|/.*||')
    ES_PROTOCOL=$(echo "$ELASTICSEARCH_URL" | grep -oE '^https?')
    
    # Check if index exists
    INDEX_EXISTS=$(curl -s -o /dev/null -w "%{http_code}" -u "elastic:$(echo $ELASTICSEARCH_URL | sed -E 's|.*://[^:]*:([^@]*)@.*|\1|')" "${ES_PROTOCOL}://${ES_HOST}/listings_v1" 2>/dev/null || echo "000")
    
    if [ "$INDEX_EXISTS" = "200" ]; then
        echo "Elasticsearch index already exists, skipping reindex"
    else
        echo "Elasticsearch index not found, running initial reindex..."
        php bin/console listings:reindex --recreate 2>&1 || echo "Reindex failed, will retry on next deploy"
    fi
else
    echo "ELASTICSEARCH_URL not set, skipping Elasticsearch setup"
fi

# Set permissions
chown -R www-data:www-data "$APP_DIR/var" 2>/dev/null || true

# Update Apache config if using /app
if [ "$APP_DIR" = "/app" ]; then
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/sites-available/*.conf 2>/dev/null || true
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/apache2.conf 2>/dev/null || true
    sed -ri -e 's|/var/www/html|/app|g' /etc/apache2/sites-enabled/*.conf 2>/dev/null || true
    export APACHE_DOCUMENT_ROOT="$APP_DIR/public"
fi

# Configure Apache to listen on PORT (default 80)
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    echo "Configuring Apache to listen on port $PORT..."
    sed -ri -e "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf 2>/dev/null || true
    sed -ri -e "s/:80>/:$PORT>/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true
    sed -ri -e "s/:80>/:$PORT>/g" /etc/apache2/sites-enabled/*.conf 2>/dev/null || true
fi

echo "Starting web server on port ${PORT:-8080}..."
# Try Apache first, fallback to PHP built-in server
if command -v apache2-foreground >/dev/null 2>&1; then
    exec apache2-foreground
elif [ -x /usr/local/bin/apache2-foreground ]; then
    exec /usr/local/bin/apache2-foreground
else
    echo "Apache not found, using PHP built-in server..."
    exec php -S 0.0.0.0:${PORT:-8080} -t "$APP_DIR/public"
fi
