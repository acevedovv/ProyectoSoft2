# Dockerfile - Laravel con php artisan serve
FROM php:8.4.1-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip zip curl libzip-dev libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar todo el proyecto Laravel
COPY . .

# Instalar dependencias de Laravel
RUN composer install

# Exponer el puerto donde correrá php artisan serve
EXPOSE 8000

# Comando para levantar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
