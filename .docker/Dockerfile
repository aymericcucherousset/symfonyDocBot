# Image BASE
FROM php:8.3-fpm-alpine AS base

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apk --no-cache add \
    # Install PHP extensions dependencies
    postgresql-dev \
    oniguruma-dev \
    # Enable Php extensions
    && docker-php-ext-install pdo pdo_pgsql intl mbstring

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
    
# Image PROD
FROM base AS prod

RUN docker-php-ext-install opcache
