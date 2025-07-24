FROM php:8.2-cli

# Instalar dependencias necesarias y Composer
RUN apt-get update && apt-get install -y unzip curl git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . .

# Instalar dependencias de Laravel
RUN composer install



# Crear directorios necesarios con permisos correctos
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar el archivo de entorno
COPY .env .env

# Limpiar configuración cacheada y generar clave de aplicación
RUN php artisan config:clear && php artisan key:generate

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
