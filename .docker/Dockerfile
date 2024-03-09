# Image BASE
FROM node:21-alpine AS node

FROM php:8.3-fpm-alpine AS base

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk --no-cache add \
    # Install PHP extensions dependencies
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
    # Enable Php extensions
    && docker-php-ext-install pdo pdo_pgsql intl mbstring zip

# Image DEV
FROM base AS dev

RUN apk --no-cache add \
    curl \
    bash \
    autoconf \
    # Install tools
    git \
    nano \
    vim \
    # Update linux headers (required for xdebug installation)
    && apk add --update --no-cache linux-headers \
    # Install Xdebug
    && apk --update --no-cache add autoconf g++ make \
    && pecl install -f xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --purge autoconf g++ make \
    # Install Symfony CLI
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash \
    && apk add --no-cache symfony-cli

# Image TEST
FROM base AS test

RUN apk add --update --no-cache linux-headers \
    && apk --update --no-cache add autoconf g++ make autoconf \
    && pecl install -f xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --purge autoconf g++ make \
    # Install Symfony CLI
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | sh \
    && apk add --no-cache symfony-cli

# Image PROD
FROM base AS prod

RUN docker-php-ext-install opcache
