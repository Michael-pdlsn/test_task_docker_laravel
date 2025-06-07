#!/bin/bash

echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R ug+rw /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

if [ ! -f /var/www/html/vendor/autoload.php ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction
fi

# Create APP_KEY if it does not exist
if ! grep -q '^APP_KEY=.\+' /var/www/html/.env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Waiting 5s for MySQL to be ready..."
sleep 5

echo "Running migrations..."
php artisan migrate --force

exec "$@"
