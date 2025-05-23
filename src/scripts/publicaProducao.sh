#!/bin/bash
ssh -p 2225 ubuntu@cm.deskfacil.com "
    cd ~/CloudMoura ; 
    git pull ;
    docker-compose run --rm composer install
    "
# docker-compose run --rm composer install
# docker-compose run --rm composer dump-autoload
# docker-compose down -v
# docker-compose up -d