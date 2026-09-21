FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        netbase \
        whois \
        libicu-dev \
    && docker-php-ext-install intl \
    && rm -rf /var/lib/apt/lists/*

# Composer from the official image instead of running an unverified installer script.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
# Strip CRLF in case the repo was checked out on Windows without .gitattributes applied.
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
