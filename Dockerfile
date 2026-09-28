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
    && chmod +x /var/www/html/render-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html

EXPOSE 10000

CMD ["/var/www/html/render-entrypoint.sh"]
