FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    zip \
    curl \
    libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && docker-php-ext-install pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*


# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Копирование файлов проекта
COPY . .

# Установка зависимостей PHP (Composer)
RUN composer install --no-interaction --optimize-autoloader

CMD ["php-fpm"]