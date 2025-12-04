FROM php:8.2-apache

# Bật rewrite
RUN a2enmod rewrite

# Cài extension PHP
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source (không copy .env)
COPY . .

# Đổi DocumentRoot về public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Install Laravel (không chạy script)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Tạo storage link
RUN php artisan storage:link || true

# Chmod
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD apache2-foreground
