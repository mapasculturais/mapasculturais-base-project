#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

NAME=mapas-base

docker exec -w /var/www/src -i $NAME bash -c "pnpm install --recursive && pnpm run watch"

cd $CDIR
