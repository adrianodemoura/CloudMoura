#!/bin/bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d --force-recreate
docker-compose run --rm composer install
docker-compose run --rm composer dump-autoload
