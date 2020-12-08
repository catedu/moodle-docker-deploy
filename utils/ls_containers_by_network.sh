#!/bin/bash

[ -z "$1" ] && echo "Debes indicar el nombre de una red" && exit 1

docker network inspect "$1" | jq -r '.[].Containers' |  jq .[].Name |  sed -e 's/^"//' -e 's/"$//'
