FROM php:8.3-fpm

RUN apt update && apt install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    netbase \
    curl \
    whois \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    mysqli \
    && rm -rf /var/lib/apt/lists/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]