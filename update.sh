#!/bin/bash
git pull hacklab/mapasculturais:latest

git submodule update

docker compose build --no-cache --pull

./stop.sh
./start.sh
