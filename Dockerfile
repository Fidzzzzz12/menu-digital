# Gunakan PHP 8.2 FPM sebagai base image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions yang dibutuhkan Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy semua file aplikasi
COPY . .

# Install dependencies Laravel (production mode)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Set permissions untuk storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Expose port 8080 (Railway default)
EXPOSE 8080

# Script untuk start nginx + php-fpm
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]