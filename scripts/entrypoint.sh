#!/bin/sh

# Database might not be immediately available
echo "Waiting for database..."
until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));" 2>/dev/null; do
  sleep 2
done

echo "Database is up - executing migrations"
php scripts/migrate.php

echo "Installing/Updating dependencies..."
composer install --no-interaction --optimize-autoloader

echo "Starting application..."
exec "$@"
