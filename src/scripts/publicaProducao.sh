#!/bin/bash

ssh -p 2225 ubuntu@cm.deskfacil.com "
    cd ~/CloudMoura ; 
    git pull ;

    if [ ! -f docker-compose.yml ]; then cp .infra/docker/docker-compose.yml . ; fi

    if [ ! -f .env ]; then cp .infra/docker/.env . ; fi
    if [ ! -f .env.local ]; then cp .infra/docker/.env.local . ; fi
    
    src/scripts/build.sh
    "