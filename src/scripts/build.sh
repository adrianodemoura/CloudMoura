#!/bin/bash

if [ ! -f docker-compose.yml ]; then 
    cp .infra/docker/docker-compose.yml . ;
    sed -i "s/user: \"1000:1000\"/user: \"$(id -u):$(id -u)\"/g" docker-compose.yml ;
fi

if [ ! -f src/.env ]; then cp .infra/env/.env src/.env ; fi

if [ ! -f src/.env.local ]; then cp .infra/env/.env.local src/.env.local ; fi

docker-compose down -v
docker-compose build --no-cache
docker-compose up -d --force-recreate
docker-compose run --rm composer install
docker-compose run --rm composer dump-autoload --no-dev
