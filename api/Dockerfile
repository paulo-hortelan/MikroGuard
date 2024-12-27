FROM phpswoole/swoole:6.0-php8.4-alpine

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
    zip unzip

RUN docker-php-ext-install bcmath sockets pcntl pdo_mysql zip

RUN if ! php -m | grep -q redis; then \
    pecl install redis && docker-php-ext-enable redis; \
    fi

WORKDIR /var/www

CMD ["php", "artisan", "octane:start", "--watch", "--poll", "--host=0.0.0.0"]
