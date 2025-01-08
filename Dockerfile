#Vue App
FROM node:erbium as vuejs
RUN mkdir -p /opt/app
COPY frontend/ /opt/app
RUN rm -rf /opt/app/node_modules
WORKDIR /opt/app
RUN npm install && npm run build

#Server Dependencies
FROM composer:latest as vendor
WORKDIR /app
COPY api/composer.json composer.json
COPY api/composer.lock composer.lock
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

#Image
FROM phpswoole/swoole:6.0-php8.4-alpine as base
LABEL authors="David Smith <david@xterm.me>"

# Install required packages and PHP extensions
RUN apk add --no-cache \
    linux-headers \
    nodejs \
    npm \
    libzip-dev \
    libpq-dev \
    mysql-client \
    autoconf \
    build-base \
    zip unzip \
    sqlite

# Install PHP extensions
RUN docker-php-ext-install bcmath sockets pcntl pdo_mysql zip

# Install Redis extension if not already installed
RUN if ! php -m | grep -q redis; then \
    pecl install redis && docker-php-ext-enable redis; \
    fi

# Verify that the zip extension is installed and enabled
RUN php -m | grep zip

# Copy application files
COPY --chown=www-data:www-data api /opt/app
COPY --chown=www-data:www-data --from=vendor /app/vendor/ /opt/app/vendor
COPY --chown=www-data:www-data --from=vuejs /opt/app/dist/ /opt/app/public

# Clean up and set permissions
RUN rm -f /opt/app/.env
RUN rm -rf /opt/app/storage/*
RUN chown -R www-data:www-data /opt/app/storage
RUN chmod +x /opt/app/docker/start.sh

WORKDIR /opt/app

EXPOSE 8000

VOLUME ["/opt/app/storage"]

ENTRYPOINT ["docker/start.sh"]
