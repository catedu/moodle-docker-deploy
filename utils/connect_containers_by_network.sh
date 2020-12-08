#!/bin/bash

[ -z "$1" ] && echo "Debes indicar el nombre de una red" && exit 1

while IFS='' read -r CONT || [ -n "${CONT}" ]; do
    echo "Conectando $CONT"; docker network connect "$1" "$CONT"
done < "tmp_container_disconnect_$1"