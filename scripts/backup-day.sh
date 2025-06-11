#!/bin/bash
DAY=`date +%d`
DUMP_FOLDER="$1"
DUMP_NAME="00H"

containers=$(docker ps | grep -E 'postgres|postgis' | awk '{print $11}')

#
# Dump das bases no container
#
for container in $containers;
do
    filename="${DUMP_NAME}.sql.gz";
    dumpname="${container%-1-*}";
    dumpfolder="${DUMP_FOLDER}/${dumpname}"
    cp $dumpfolder/$filename $dumpfolder/$DAY.sql.gz
done
