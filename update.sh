#!/bin/bash
git pull --recurse-submodules

git submodule update

docker compose build --no-cache --pull

./stop.sh
./start.sh
