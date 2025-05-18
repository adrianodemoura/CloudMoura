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

# Criar diretório e definir permissões
RUN mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

WORKDIR /var/www/html

# Adicionar script de inicialização
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER www-data

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]