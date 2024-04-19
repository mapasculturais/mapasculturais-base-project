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
    -d   | --down       executa o docker compose down antes do docker compose run
    -h   | --help       Imprime esta mensagem de ajuda
    "
    	    exit
    ;;
esac
done
if [ $BUILD = "1" ]; then
   docker compose build --no-cache --pull
fi

if [ $DOWN = "1" ]; then
   docker compose down
fi

mkdir -p docker-data/assets
mkdir -p docker-data/logs
mkdir -p docker-data/private-files
mkdir -p docker-data/public-files
mkdir -p docker-data/saas-files

touch docker-data/logs/app.log

chown -R www-data: docker-data/assets docker-data/logs docker-data/private-files docker-data/public-files docker-data/saas-files

docker compose run --service-ports mapas

docker compose down
cd $CDIR
