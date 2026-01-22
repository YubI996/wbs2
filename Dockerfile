# ==============================================
# WBS v2 - Production Dockerfile
# Laravel 12 + Filament v3
# ==============================================

# Stage 1: Composer Dependencies
FROM composer:2 AS composer-builder

RUN apk add --no-cache icu-dev && docker-php-ext-install intl

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize

# Stage 2: NPM Build
FROM node:20-alpine AS npm-builder

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY --from=composer-builder /app .
RUN npm run build

# Stage 3: Production Image
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    mariadb-client \
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

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy app from builders
COPY --from=composer-builder /app ./
COPY --from=npm-builder /app/public/build ./public/build

# Setup directories
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
