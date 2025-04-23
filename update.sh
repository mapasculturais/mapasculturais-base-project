#!/bin/bash
git pull hacklab/mapasculturais:7.5.40

git submodule update

docker compose build --no-cache --pull

./stop.sh
./start.sh
