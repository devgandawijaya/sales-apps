# ==============================================================================
# Multi-Branch Sales & Distribution System (GT & MT Channels)
# Production-ready Dockerfile for Laravel 11 on PHP 8.3-FPM
# ==============================================================================

FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Set environment arguments
ARG PUID=1000
ARG PGID=1000

# Install system dependencies and build tools
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip \
    libzip-dev \
    zip \
    unzip \
    icu-libs \
    icu-dev \
    libpq \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache \
        gd

# Install Redis extension via PECL
RUN pecl install -o -f redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Remove build-only dependencies to keep image lean
RUN apk del $PHPIZE_DEPS postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Configure PHP & Opcache
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Create non-root user matching host UID/GID
RUN addgroup -g ${PGID} www && \
    adduser -D -u ${PUID} -G www -s /bin/bash www

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose PHP-FPM port
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

