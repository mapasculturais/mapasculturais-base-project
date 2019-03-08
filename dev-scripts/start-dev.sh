#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


BUILD="0"
DOWN="0"
SLEEP_TIME="0"

if [ ! -d "../docker-data/postgres" ]; then
  SLEEP_TIME=15
fi

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

if [ $BUILD = "1" ]; then
   docker-compose -f docker-compose.local.yml build
fi

if [ $DOWN = "1" ]; then
   docker-compose -f docker-compose.local.yml down
fi

rm -rf ../docker-data/pcache-cron.log
touch ../docker-data/pcache-cron.log

docker-compose -f docker-compose.local.yml run --service-ports  mapas

docker-compose -f docker-compose.local.yml down
cd $CDIR
