#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done

echo "Database is ready!"

# Run migrations
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Start the server
exec "$@"

