FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

FROM php:8.3-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS linux-headers icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install bcmath intl mbstring pdo_mysql zip \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . ./
COPY --from=vendor /app/vendor ./vendor
COPY docker/laravel/entrypoint.sh /usr/local/bin/zolta-api-entrypoint

RUN chmod +x /usr/local/bin/zolta-api-entrypoint \
    && composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["zolta-api-entrypoint"]
CMD ["php-fpm"]
