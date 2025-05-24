#!/bin/bash

ssh -p 2225 ubuntu@cm.deskfacil.com "
    cd ~/CloudMoura ; 
    git pull ;
    cp .infra/docker/docker-compose.yml docker-compose.yml ;
    src/scripts/build.sh
    "