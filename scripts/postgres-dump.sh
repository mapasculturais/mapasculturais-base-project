#!/bin/bash

DUMP_FOLDER="$1"
DUMP_NAME="`date +%HH`"

containers=$(docker ps | grep -E 'postgres|postgis' | awk '{print $11}')

#
# Dump das bases no container
#
for container in $containers;
do
    filename="${DUMP_NAME}.sql.gz";
    dumpname="${container%-1-*}";
    dumpfolder="${DUMP_FOLDER}/${dumpname}"
    mkdir -p "$dumpfolder"

    pushd "$dumpfolder"
    docker exec  "$container" \
        sh -c 'pg_dump -U mapas --no-owner -d mapas' \
        | gzip -c > $filename

    popd;
done