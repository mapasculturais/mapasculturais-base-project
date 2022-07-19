#!/bin/bash

docker-compose -f docker-compose.prod.yml exec db psql -U mapas -d mapas
