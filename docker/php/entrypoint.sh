#!/bin/sh
set -e

# Aguardar alguns segundos para garantir que o volume foi montado
sleep 2

# Executar o PHP-FPM
exec "$@"
