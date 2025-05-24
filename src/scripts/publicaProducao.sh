#!/bin/bash

ssh -p 2225 ubuntu@cm.deskfacil.com 'cd ~/CloudMoura ; git pull ; src/scripts/build.sh'
