#!/bin/bash

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Running migrations..."
php artisan migrate --force || true

echo "Starting Apache..."
apache2-foreground
