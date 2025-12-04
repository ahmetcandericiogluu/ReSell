# Use official PHP image with Apache
FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ensure symfony/runtime and dotenv are installed
RUN composer require symfony/runtime symfony/dotenv --no-scripts --no-interaction || true

# Apache configuration
RUN a2enmod rewrite headers && \
    sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Add Apache vhost configuration for Symfony
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Update Apache to listen on PORT env variable (Render requirement)
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 8080

# Set permissions (must be after composer install)
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log && \
    chown -R www-data:www-data /var/www/html/var

# Copy and set entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Start with entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

