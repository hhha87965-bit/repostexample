FROM php:8.1-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Очистка кэша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание рабочей директории
WORKDIR /var/www/html

# Установка прав для пользователя www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Создание папки для сессий и кэша
RUN mkdir -p /var/www/html/writable/cache \
    && mkdir -p /var/www/html/writable/logs \
    && mkdir -p /var/www/html/writable/session \
    && chown -R www-data:www-data /var/www/html/writable

EXPOSE 9000