#!/bin/bash

docker-compose -f docker-compose.prod.yml logs -f --tail=10
