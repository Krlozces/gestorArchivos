# Usa una imagen base de PHP 8.2 con Apache
FROM php:8.2-apache

# Instala dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
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

# Configura Apache
RUN a2enmod rewrite

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos del proyecto
COPY . .

# Copia el archivo .env.example a .env si .env no existe
RUN cp -n .env.example .env || true

# Instala dependencias de Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Genera la key de la aplicación
RUN php artisan key:generate

# Configura los permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Expone el puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
