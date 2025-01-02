# Use the official PHP 8.2 FPM image
FROM php:8.2-fpm

# Install system dependencies, configure, and install PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    pkg-config \
    build-essential \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install -j20 \
    gd \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    exif \
    pcntl \
    bcmath \
    soap \
    sockets \
    zip \
    curl

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/bin/composer

# Install PHPUnit globally
RUN composer global require phpunit/phpunit ^10.0 \
    && ln -s /root/.composer/vendor/bin/phpunit /usr/local/bin/phpunit

# Set working directory
WORKDIR /var/www/html

# Copy project files (if you already have a project)
COPY . .

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
