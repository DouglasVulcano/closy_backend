# Use PHP 8.3 FPM (mais eficiente que Apache)
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and Nginx
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    netcat-openbsd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh
COPY docker/fix-permissions.sh /usr/local/bin/fix-permissions.sh
COPY docker/test-db.php /usr/local/bin/test-db.php

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/app/temp \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/log/nginx \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/log/supervisor \
    && chmod +x /usr/local/bin/start.sh /usr/local/bin/fix-permissions.sh /usr/local/bin/test-db.php

# Expose port 80
EXPOSE 80

# Start application with initialization script
CMD ["/usr/local/bin/start.sh"]
