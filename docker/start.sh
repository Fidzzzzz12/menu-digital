#!/bin/bash

# Start PHP-FPM di background
php-fpm -D

# Jalankan Laravel optimize commands
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Nginx di foreground
nginx -g 'daemon off;'