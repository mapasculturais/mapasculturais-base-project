#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

docker exec -it $(docker-compose -f docker-compose.local.yml ps -q mapas) sh /var/www/scripts/shell.sh

cd $CDIR