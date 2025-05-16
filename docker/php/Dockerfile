FROM php:8-fpm-alpine

# Instalar extensões necessárias
RUN set -ex; \
    apk add --no-cache \
        sqlite \
        sqlite-dev \
        libzip-dev \
        zip \
        unzip \
        shadow; \
    # Instalar extensões PHP
    docker-php-ext-install \
        pdo_sqlite \
        zip

# Aumentar limite de upload e configurar PHP
COPY php.ini /usr/local/etc/php/conf.d/uploads.ini
COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

# Adicionar script de inicialização para configurar permissões
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]