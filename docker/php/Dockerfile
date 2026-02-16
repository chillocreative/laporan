# =============================================================================
# Sistem Pelaporan - PHP 8.4 FPM Dockerfile (Production)
# =============================================================================

FROM php:8.4-fpm AS base

LABEL maintainer="Sistem Pelaporan <admin@sistempelaporan.gov.my>"
LABEL description="PHP 8.4 FPM for Sistem Pelaporan"

# ---------------------------------------------------------------------------
# Environment
# ---------------------------------------------------------------------------
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/tmp/composer

# ---------------------------------------------------------------------------
# Install system dependencies
# ---------------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Build tools
    build-essential \
    # Required for GD
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    # Required for ZIP
    libzip-dev \
    zip \
    unzip \
    # Required for intl
    libicu-dev \
    # Required for bcmath (no extra deps)
    # General utilities
    curl \
    git \
    # Supervisor (for queue worker container)
    supervisor \
    # MySQL client for health checks
    default-mysql-client \
    # Process management
    procps \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Install PHP extensions
# ---------------------------------------------------------------------------
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        pcntl \
        exif

# ---------------------------------------------------------------------------
# Install Redis extension via PECL
# ---------------------------------------------------------------------------
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && pecl clear-cache

# ---------------------------------------------------------------------------
# PHP-FPM health check script
# ---------------------------------------------------------------------------
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# ---------------------------------------------------------------------------
# Copy PHP configuration
# ---------------------------------------------------------------------------
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Custom PHP settings for production
RUN { \
    echo '[PHP]'; \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
    echo 'max_execution_time = 120'; \
    echo 'max_input_time = 60'; \
    echo 'max_input_vars = 3000'; \
    echo 'expose_php = Off'; \
    echo 'date.timezone = Asia/Kuala_Lumpur'; \
    echo ''; \
    echo '[opcache]'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
    echo 'opcache.interned_strings_buffer = 16'; \
    echo 'opcache.max_accelerated_files = 10000'; \
    echo 'opcache.revalidate_freq = 0'; \
    echo 'opcache.validate_timestamps = 0'; \
    echo 'opcache.save_comments = 1'; \
    echo 'opcache.jit = tracing'; \
    echo 'opcache.jit_buffer_size = 64M'; \
    echo ''; \
    echo '[Session]'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.cookie_secure = 1'; \
    echo 'session.cookie_samesite = Lax'; \
    echo 'session.use_strict_mode = 1'; \
} > "$PHP_INI_DIR/conf.d/99-production.ini"

# PHP-FPM pool tuning
RUN { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 50'; \
    echo 'pm.start_servers = 5'; \
    echo 'pm.min_spare_servers = 5'; \
    echo 'pm.max_spare_servers = 35'; \
    echo 'pm.max_requests = 500'; \
    echo 'pm.status_path = /status'; \
    echo 'ping.path = /ping'; \
    echo 'ping.response = pong'; \
    echo 'request_terminate_timeout = 120s'; \
} > /usr/local/etc/php-fpm.d/zz-production.conf

# ---------------------------------------------------------------------------
# Install Composer
# ---------------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Set working directory
# ---------------------------------------------------------------------------
WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# Copy application code
# ---------------------------------------------------------------------------
COPY --chown=www-data:www-data . /var/www/html

# ---------------------------------------------------------------------------
# Install Composer dependencies (production)
# ---------------------------------------------------------------------------
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    && composer dump-autoload --optimize

# ---------------------------------------------------------------------------
# Set permissions
# ---------------------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# ---------------------------------------------------------------------------
# Create required storage directories
# ---------------------------------------------------------------------------
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    && chown -R www-data:www-data storage

# ---------------------------------------------------------------------------
# Switch to non-root user
# ---------------------------------------------------------------------------
USER www-data

# ---------------------------------------------------------------------------
# Expose PHP-FPM port
# ---------------------------------------------------------------------------
EXPOSE 9000

CMD ["php-fpm"]
