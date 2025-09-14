#!/bin/bash

# Exit on any error
set -e

echo "Starting Laravel application setup..."
echo "Environment: ${APP_ENV:-not_set}"
echo "Debug mode: ${APP_DEBUG:-not_set}"
echo "Using .env file: $(ls -la /var/www/html/.env* 2>/dev/null || echo 'No .env files found')"

# Wait for database to be ready
echo "Waiting for database connection..."
echo "Database config: Host=${DB_HOST:-localhost}, Port=${DB_PORT:-3306}, Database=${DB_DATABASE:-laravel}"

# Test network connectivity first
echo "Testing network connectivity to database host..."
if command -v nc >/dev/null 2>&1; then
    if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
        echo "Network connection to ${DB_HOST}:${DB_PORT} successful"
    else
        echo "WARNING: Cannot reach ${DB_HOST}:${DB_PORT} - network connectivity issue"
    fi
else
    echo "netcat not available, skipping network test"
fi

# Test database connection with timeout
max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if php /usr/local/bin/test-db.php; then
        echo "Database connection established!"
        break
    fi

    attempt=$((attempt + 1))
    echo "Database not ready, attempt $attempt/$max_attempts..."

    if [ $attempt -eq $max_attempts ]; then
        echo "ERROR: Failed to connect to database after $max_attempts attempts"
        echo "Please check your database configuration:"
        echo "Host: ${DB_HOST}"
        echo "Port: ${DB_PORT}"
        echo "Database: ${DB_DATABASE}"
        echo "Username: ${DB_USERNAME}"
        exit 1
    fi

    sleep 3
done

# Database migrations are now handled manually
# To run migrations manually, execute:
# docker exec -it <container_name> php artisan migrate
echo "Skipping automatic migrations - run manually when needed"

# Run Laravel optimizations
echo "Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Set proper permissions
echo "Setting permissions..."

# Create necessary cache directories
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# Set ownership
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Set permissions
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Ensure cache directory has proper permissions
chmod -R 775 /var/www/html/storage/framework/cache
chmod -R 775 /var/www/html/storage/framework/cache/data

echo "Laravel setup completed. Starting services..."

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
