#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

NAME=mapas-base

docker exec -it $NAME sh /var/www/scripts/shell.sh

cd $CDIR