#!/bin/bash
set -e

echo "==================================="
echo "WBS v2 Application Initialization"
echo "==================================="

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
RETRIES=30
until mysql -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1 || [ $RETRIES -eq 0 ]; do
  echo "Waiting for MySQL server, $((RETRIES--)) remaining attempts..."
  sleep 2
done

if [ $RETRIES -eq 0 ]; then
  echo "ERROR: MySQL connection failed!"
  exit 1
fi

echo "✓ MySQL is ready!"

# Check if APP_KEY is set, generate if not
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
  echo "Generating application key..."
  php artisan key:generate --force
  echo "✓ Application key generated!"
else
  echo "✓ Application key already set"
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force
echo "✓ Migrations completed!"

# Create storage symlink
if [ ! -L /var/www/html/public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link
  echo "✓ Storage symlink created!"
else
  echo "✓ Storage symlink already exists"
fi

# Clear and optimize caches for production
if [ "${APP_ENV}" = "production" ]; then
  echo "Optimizing application for production..."

  echo "- Clearing caches..."
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear

  echo "- Caching configuration..."
  php artisan config:cache

  echo "- Caching routes..."
  php artisan route:cache

  echo "- Caching views..."
  php artisan view:cache

  echo "- Running Filament upgrade..."
  php artisan filament:upgrade

  echo "✓ Production optimization completed!"
else
  echo "Running in ${APP_ENV} mode - skipping cache optimization"
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
echo "✓ Permissions set!"

echo "==================================="
echo "Initialization completed successfully!"
echo "Starting PHP-FPM..."
echo "==================================="

# Execute the main command (PHP-FPM)
exec "$@"
