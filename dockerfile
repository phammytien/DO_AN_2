FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libonig-dev libxml2-dev zip libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Set Apache document root to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy project
COPY . /var/www/html

WORKDIR /var/www/html

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

# FIX: xoá cache Laravel tránh lỗi “Target class [config] does not exist”
RUN rm -f bootstrap/cache/config.php
RUN rm -f bootstrap/cache/packages.php
RUN rm -f bootstrap/cache/services.php

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Storage symlink
RUN php artisan storage:link || true

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
