#!/bin/bash

get_ip_node_exporter(){
    local IPNAME="$1"
    COMMAND="docker network inspect monitor_monitor-net | jq -r '.[].Containers' | jq '.[] | select(.Name|startswith(\"monitor_node-exporter\"))' | jq '.IPv4Address' | sed -e 's/^\"//' -e 's/\"$//'"
    
    ssh -p 22987 debian@$IPNAME $COMMAND
}

backupprod="54.36.107.95"
moodle1prod="51.210.33.231"
moodle2prod="51.210.209.100"
moodle3prod="51.210.112.223"
moodle4prod="51.210.209.224"

echo "backup = $(get_ip_node_exporter $backupprod)"
echo "moodle1 = $(get_ip_node_exporter $moodle1prod)"
#echo "moodle2 = $(get_ip_node_exporter $moodle2prod)"
echo "moodle3 = $(get_ip_node_exporter $moodle3prod)"
echo "moodle4 = $(get_ip_node_exporter $moodle4prod)"

