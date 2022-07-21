#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

NAME=mapas

docker exec -it $MAPAS bash

cd $CDIR