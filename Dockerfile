# HMS on Render.
#
# Render has no native PHP runtime, so the app ships as a container. Apache serves
# public/ directly; there is no Node build step because the views load their CSS and
# JS from CDNs rather than through Vite.

FROM php:8.2-apache

# pdo_pgsql for Supabase; gd for image handling; zip for the spreadsheet import.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql gd zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Laravel serves from public/, not the project root. Written literally rather than
# as ${APACHE_DOCUMENT_ROOT}: Apache's expansion of environment variables inside its
# own config is unreliable, and when it fails the failure is silent.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/html/public/>!g' /etc/apache2/apache2.conf \
    && printf '<Directory /var/www/html/public/>\n    Options -Indexes +FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# The listening port is set by the entrypoint from $PORT, because Render assigns it
# at runtime and it is not always 80.

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies first: this layer is cached unless composer.json/lock change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache config/routes/views at boot rather than at build time — the environment
# variables that config caching bakes in are not present during the build.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
