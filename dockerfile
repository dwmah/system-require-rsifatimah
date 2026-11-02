# Tahap 1: Build aset frontend (Vite / Node)
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Tahap 2: Setup PHP-FPM & Nginx
FROM serversideup/php:8.3-fpm-nginx-alpine

USER root
RUN install-php-extensions gd intl bcmath zip pdo_mysql

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

USER www-data
RUN composer install --no-interaction --no-dev --optimize-autoloader

ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=true
