# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci || npm install
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm run build

# ==========================================
# Stage 2: PHP Application Container
# ==========================================
FROM php:8.2-fpm-alpine

WORKDIR /var/www

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    bash \
    netcat-openbsd

# Install official PHP extension installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install PHP extensions required by Laravel
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy composer files and install PHP dependencies (without dev packages)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Copy the entire Laravel application
COPY . .

# Copy compiled frontend assets from frontend-builder stage
COPY --from=frontend-builder /app/public/build ./public/build

# Re-run composer autoload optimization
RUN composer dump-autoload --optimize --no-dev

# Setup entrypoint script and permissions
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

