#!/bin/bash
docker pull hacklab/mapasculturais:latest
git pull

git submodule update

docker-compose build --no-cache

./stop.sh
./start.sh
