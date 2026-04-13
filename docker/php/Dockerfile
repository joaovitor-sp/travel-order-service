FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    librabbitmq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN pecl install amqp \
    && docker-php-ext-enable amqp

RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
 && chown -R www-data:www-data /var/www \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Configuração do Opcache
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Configuração do Xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
