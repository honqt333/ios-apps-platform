# ===========================================================
# Backend Dockerfile (Laravel 12 + PHP 8.3 + Nginx + Supervisor)
# ===========================================================
FROM php:8.3-fpm-alpine AS base

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    autoconf \
    g++ \
    make \
    linux-headers

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    gd \
    intl \
    opcache \
    bcmath \
    exif \
    pcntl

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || true

# Copy the rest
COPY backend/ .

# Run scripts
RUN composer dump-autoload --optimize

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Nginx config
COPY docker/backend/nginx.conf /etc/nginx/nginx.conf

# Supervisor
COPY docker/backend/supervisord.conf /etc/supervisord.conf

# Entry
COPY docker/backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
