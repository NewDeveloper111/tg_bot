FROM php:8.3.3-fpm-alpine3.18

RUN set -eux \
    && apk --no-cache update \
    && apk add --no-cache autoconf \
    curl postgresql-dev \
    && docker-php-ext-install -j$(nproc) pgsql \
    && true
RUN docker-php-ext-install bcmath

ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
