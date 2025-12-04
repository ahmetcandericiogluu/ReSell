FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first (for better caching)
COPY composer.json composer.lock symfony.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-progress

# Copy application files
COPY . .

# Run post-install scripts
RUN composer dump-autoload --optimize

# Create var directory and set permissions
RUN mkdir -p var/cache var/log && \
    chmod -R 777 var/

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port (will be overridden by PORT env var)
EXPOSE 8080

ENV PORT=8080
ENV APP_ENV=prod

# Start command
ENTRYPOINT ["docker-entrypoint.sh"]

