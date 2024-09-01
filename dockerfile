# Establecer la imagen base
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar el contenido del proyecto
COPY . .

# Instalar las dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Copiar el archivo de entorno de producción
COPY .env.example .env

# Generar la clave de la aplicación
RUN php artisan key:generate

# Ejecutar las migraciones y los seeders (si es necesario)
RUN php artisan migrate --force

# Cambiar los permisos de la carpeta de almacenamiento y bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exponer el puerto 9000 y ejecutar PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
