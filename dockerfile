FROM php:8.2-fpm

   # Instala dependencias y extensiones
   RUN apt-get update && apt-get install -y \
       nginx \
       libpng-dev \
       libjpeg-dev \
       libfreetype6-dev \
       zip \
       unzip \
       git \
       libzip-dev \
       libicu-dev \
       && docker-php-ext-configure gd --with-freetype --with-jpeg \
       && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl exif bcmath

   # Instala Composer
   RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

   # Configura Nginx
   COPY nginx.conf /etc/nginx/sites-available/default

   WORKDIR /var/www/html

   COPY . .

   RUN cp -n .env.example .env || true
   RUN composer install --no-interaction --prefer-dist --optimize-autoloader
   RUN php artisan key:generate
   RUN chown -R www-data:www-data storage bootstrap/cache
   RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

   EXPOSE 80

   CMD service nginx start && php-fpm
   
