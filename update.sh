#!/bin/bash
docker pull hacklab/mapasculturais:latest
git pull --recurse-submodules

git submodule update

docker-compose build --no-cache

./stop.sh
./start.sh
