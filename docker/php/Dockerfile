FROM php:8.3-fpm-alpine

# Dependencias del sistema operativo (Alpine Linux)
# postgresql-dev  → cabeceras para compilar pdo_pgsql
# libzip-dev      → para la extensión zip (paquetes Composer)
# libxml2-dev     → para xml y dom (requeridos por Laravel)
# oniguruma-dev   → para mbstring (procesado UTF-8)
# git/curl/unzip  → herramientas que Composer necesita internamente
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    curl

# Extensiones PHP requeridas por Laravel 11
# pdo + pdo_pgsql → conexión a PostgreSQL
# mbstring        → manipulación de strings UTF-8
# zip             → manejo de archivos comprimidos
# bcmath          → aritmética de precisión arbitraria
# xml + dom       → procesado XML
# fileinfo        → detección de tipos MIME (Laravel Storage)
# pcntl           → control de procesos (Laravel queues)
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    bcmath \
    xml \
    dom \
    fileinfo \
    pcntl

# Composer 2.7 copiado desde su imagen oficial (multi-stage build)
# Así no instalamos Composer manualmente — usamos la versión oficial
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

# Puerto 9000: FastCGI (PHP-FPM escucha aquí, Nginx le envía peticiones)
EXPOSE 9000
CMD ["php-fpm"]
