#!/bin/bash

[ -z "$1" ] && echo "Debes indicar el nombre de una red" && exit 1
# Incializamos fichero de log con los contenedores
echo -n "" > "tmp_container_disconnect_$1"

for CONT in $(docker network inspect "$1" | jq -r '.[].Containers' |  jq .[].Name | sed -e 's/^"//' -e 's/"$//'); do
    echo "$CONT" >> "tmp_container_disconnect_$1"
    echo "CONTAINER: $CONT disconnect"; docker network disconnect "$1" "$CONT";
done
