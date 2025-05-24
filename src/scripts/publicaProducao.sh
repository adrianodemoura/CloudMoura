#!/bin/bash

ssh -p 2225 ubuntu@cm.deskfacil.com "
    cd ~/CloudMoura ; 
    git pull ;

    if [ ! -f docker-compose.yml ]; then 
        cp .infra/docker/docker-compose.yml . 
        sed -i \"s/user: \\\"1000:1000\\\"/user: \\\"$(id -u):$(id -g)\\\"/\" docker-compose.yml
    fi

    if [ ! -f src/.env ]; then cp .infra/env/.env src/.env ; fi

    if [ ! -f src/.env.local ]; then cp .infra/env/.env.local src/.env.local ; fi

    src/scripts/build.sh
"