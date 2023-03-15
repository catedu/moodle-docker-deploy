#!/bin/bash

# Load sensitive data or configurable data from a .env file
export $(grep -E -v '^#' /init-scripts/.env | xargs)

# Lanzamos script expect para evitar la interacción en el proceso upgrade de moodle
echo '#!/usr/bin/expect -f' >> actualiza
echo 'set timeout -1' >> actualiza
echo 'spawn php /var/www/html/admin/cli/upgrade.php' >> actualiza
echo 'expect "(para no)"' >> actualiza 
echo 'send -- "s"' >> actualiza
echo 'send -- "\r"' >> actualiza
# Si el idioma de la plataforma por defecto fuese inglés, entonces:
# echo 'expect "(means no)"' >> actualiza 
# echo 'send -- "y"' >> actualiza
# echo 'send -- "\r"' >> actualiza
echo 'expect eof' >> actualiza
chmod 744 actualiza
./actualiza

#Añadido para moodle4
echo >&2 "Deactivate coure enddate by default"
moosh config-set courseenddateenabled 0 moodlecourse

echo >&2 "Topic format by default"
moosh config-set format topics moodlecourse

echo >&2 "Activating time limit at assign activities"
moosh config-set enabletimelimit 1 assign

echo >&2 "Max file size 192MB"
moosh config-set maxbytes 201326592

echo >&2 "Activating android and ios app link"
moosh -n config-set iosappid '1586956480' tool_mobile
moosh -n config-set androidappid 'es.aragon.aeducar' tool_mobile

echo >&2 "Deactivating analytics"
moosh config-set enableanalytics 0

echo >&2 "moodle.sh done"