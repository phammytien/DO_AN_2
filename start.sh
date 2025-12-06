#!/bin/bash

# Clear caches (just in case)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize:clear || true

# Run migrations (optional)
php artisan migrate --force || true

# Start Apache
apache2-foreground
