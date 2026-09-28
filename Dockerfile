FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        unzip \
        libpq-dev \
        libonig-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql mbstring \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html

EXPOSE 10000

CMD ["sh", "-c", "PORT=${PORT:-10000}; sed -ri 's/^Listen .*/Listen '${PORT}'/' /etc/apache2/ports.conf; sed -ri 's/<VirtualHost \\*:[0-9]+>/<VirtualHost *:'${PORT}'>/' /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
