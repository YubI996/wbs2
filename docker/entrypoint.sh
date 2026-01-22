#!/bin/bash
set -e

echo "========================================"
echo "WBS v2 - Starting Application"
echo "========================================"

cd /var/www/html

# Wait for database
echo "Waiting for database..."
timeout=60
while ! php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    timeout=$((timeout - 1))
    if [ $timeout -le 0 ]; then
        echo "Database connection timeout, proceeding anyway..."
        break
    fi
    sleep 1
done
echo "Database ready!"

# Generate key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force
    fi
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration warning (may already exist)"

# Cache config for production
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan filament:upgrade || true
fi

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "========================================"
echo "Application ready!"
echo "========================================"

exec "$@"
