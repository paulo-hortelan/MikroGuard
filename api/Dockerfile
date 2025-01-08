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

WORKDIR /var/www

CMD ["php", "artisan", "octane:start", "--watch", "--poll", "--host=0.0.0.0"]
