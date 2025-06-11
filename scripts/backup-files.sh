#!/bin/bash
PROJECT_FOLDER="$1"
BACKUP_FOLDER="$2"

mkdir -p "$BACKUP_FOLDER/docker-data/private-files"
mkdir -p "$BACKUP_FOLDER/docker-data/public-files"
mkdir -p "$BACKUP_FOLDER/docker-data/logs"

rsync -ar "$PROJECT_FOLDER/docker-data/private-files" "$BACKUP_FOLDER/docker-data/private-files"
rsync -ar "$PROJECT_FOLDER/docker-data/public-files" "$BACKUP_FOLDER/docker-data/public-files"
rsync -ar "$PROJECT_FOLDER/docker-data/logs" "$BACKUP_FOLDER/docker-data/logs"
cp "$PROJECT_FOLDER/.env" "$BACKUP_FOLDER/.env"