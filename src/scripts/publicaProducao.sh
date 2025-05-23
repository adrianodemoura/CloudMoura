#!/bin/bash
ssh -p 2225 ubuntu@cm.deskfacil.com "
    cd ~/CloudMoura ; 
    git pull ;
    docker-compose down -v ;
    docker-compose build ;
    docker-compose up -d ;
    docker-compose run --rm composer install ;
    docker-compose run --rm composer dump-autoload
    "