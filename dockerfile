# Dockerfile – Laravel + Apache cho Render

FROM php:8.2-apache

# Bật mod_rewrite
RUN a2enmod rewrite

# Cài các thư viện cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip

# Cài composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ source vào container
COPY . .

# Set DocumentRoot = public (QUAN TRỌNG)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Cài dependencies PHP
RUN composer install --no-dev --optimize-autoloader

# Tạo APP_KEY và cache
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Fix quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
