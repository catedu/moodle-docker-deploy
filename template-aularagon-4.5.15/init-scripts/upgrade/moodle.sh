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

echo >&2 "moodle.sh done"