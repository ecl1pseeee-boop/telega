# --- Stage 1: Front-end ---
FROM node:20-slim AS frontend

WORKDIR /app

# Копируем файлы зависимостей
COPY package.json package-lock.json ./

# Устанавливаем Node-зависимости
RUN npm ci

# Копируем конфигурацию Vite
COPY . .

RUN npm run build


# --- Stage 2: Back-end---
# Используем PHP-FPM
FROM php:8.5-fpm

# Системные пакеты
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev \
    zip unzip libzip-dev autoconf g++ make \
    && rm -rf /var/lib/apt/lists/*

# PHP-расширения
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure zip && docker-php-ext-install zip

RUN pecl install redis && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем зависимости Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# Копируем остальной PHP код
COPY . .

RUN mkdir -p /var/www/html/public/build

# Копируем из фронтенда
COPY --from=frontend /app/public/build /var/www/html/public/build

# ВАЖНО: Проверим наличие манифеста и права
RUN chown -R www-data:www-data /var/www/html/public/build && \
    chmod -R 755 /var/www/html/public/build


RUN mkdir -p /var/www/html/bootstrap/cache && \
    mkdir -p /var/www/html/storage/framework/views && \
    mkdir -p /var/www/html/storage/framework/cache && \
    mkdir -p /var/www/html/storage/framework/sessions && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Оптимизация Laravel
RUN composer dump-autoload --optimize

# Права доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
