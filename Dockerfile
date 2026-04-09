FROM php:8.4-fpm-alpine

# Install dependencies for PHP extensions and Composer
RUN apk add --no-cache curl-dev ${PHPIZE_DEPS} \
    && docker-php-ext-install pdo pdo_mysql curl \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Run composer installation
RUN composer update --no-dev --optimize-autoloader --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data /app

# Entrypoint script to run migrations
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
