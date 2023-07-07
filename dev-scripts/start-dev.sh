#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


BUILD="0"
DOWN="0"

for i in "$@"
do
case $i in
    -b|--build)
            BUILD="1"
	    shift
    ;;
    -d|--down)
            DOWN="1"
	    shift
    ;;
    -h|--help)
    	    echo "
	start-dev.sh [-b] [-d]

    -b   | --build      builda a imagem Docker
    -d   | --down       executa o docker-compose down antes do docker-compose run
    -h   | --help       Imprime esta mensagem de ajuda
    "
    	    exit
    ;;
esac
done

NAME=mapas-base

if [ $BUILD = "1" ]; then
   docker-compose build --no-cache --pull
fi

if [ $DOWN = "1" ]; then
   docker-compose down
   docker rm $NAME
fi


docker-compose run --name=$NAME --service-ports  mapas

docker-compose down
cd $CDIR
