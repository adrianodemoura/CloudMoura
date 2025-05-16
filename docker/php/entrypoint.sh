#!/bin/sh
set -e

# Obter o UID e GID do diretório de volume montado
HOST_UID=$(stat -c '%u' /var/www/html)
HOST_GID=$(stat -c '%g' /var/www/html)

# Criar ou modificar grupo para corresponder ao GID do host
if getent group $HOST_GID > /dev/null; then
    GROUP_NAME=$(getent group $HOST_GID | cut -d: -f1)
else
    GROUP_NAME=hostgroup
    addgroup -g $HOST_GID $GROUP_NAME
fi

# Garantir que www-data pertence ao grupo do host para permissões adequadas
adduser www-data $GROUP_NAME

# Executar o comando fornecido (normalmente php-fpm)
exec "$@"
