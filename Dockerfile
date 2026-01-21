# Multi-stage Dockerfile for Laravel 12 + Filament v5 WBS Application
# Optimized for production deployment

# ============================================
# Stage 1: Composer Dependencies
# ============================================
FROM composer:2 AS builder-composer

WORKDIR /app

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install production dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Copy application code
COPY . .

# Run composer scripts (without dev dependencies)
RUN composer dump-autoload --optimize

# ============================================
# Stage 2: NPM Build
# ============================================
FROM node:20-alpine AS builder-npm

WORKDIR /app

# Copy package files for layer caching
COPY package.json package-lock.json ./

# Install npm dependencies
RUN npm ci

# Copy application code and Vite config
COPY . .
COPY --from=builder-composer /app/vendor ./vendor

# Build Vite assets for production
RUN npm run build

# ============================================
# Stage 3: Production Runtime
# ============================================
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pdo_mysql \
        pcntl \
        zip

# Install Redis extension (optional, for future use)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy application from builder stages
COPY --from=builder-composer --chown=www-data:www-data /app ./
COPY --from=builder-npm --chown=www-data:www-data /app/public/build ./public/build

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch to www-data user for security
USER www-data

# Expose PHP-FPM port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --retries=3 --start-period=40s \
    CMD php artisan inspire || exit 1

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]
