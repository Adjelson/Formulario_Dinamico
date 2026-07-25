FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libonig-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip gd opcache \
    && a2enmod rewrite headers expires deflate \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/dynamic-forms.ini

RUN mkdir -p storage/uploads storage/covers storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 750 storage

EXPOSE 80
CMD ["apache2-foreground"]
