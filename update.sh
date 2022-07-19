#!/bin/bash
docker pull hacklab/mapasculturais:latest
git pull

git submodule update

docker-compose -f docker-compose.prod.yml build --no-cache

./stop.sh
./start.sh
