#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code, previous export should be made!)


# Load sensitive data or configurable data from a .env file
# export $(grep -E -v '^#' /init-scripts/.env | xargs)


# Config theme snap, already imported via plugins.sh
echo >&2 "Exporting theme..."

moosh -n config-set theme moove
moosh -n theme-settings-export --themename moove
cp moove*.tar.gz /init-scripts/themes/
moosh -n theme-settings-import --targettheme moove moove*tar.gz

# Lanzamos script expect para evitar la interacción en el proceso upgrade de moodle
echo '#!/usr/bin/expect -f' >> actualiza
echo 'set timeout -1' >> actualiza
echo 'spawn php /var/www/html/admin/cli/upgrade.php' >> actualiza
echo 'expect "(para no)"' >> actualiza 
echo 'send -- "s"' >> actualiza
echo 'send -- "\r"' >> actualiza
# echo 'expect "(means no)"' >> actualiza 
# echo 'send -- "y"' >> actualiza
# echo 'send -- "\r"' >> actualiza
echo 'expect eof' >> actualiza
chmod 744 actualiza
./actualiza

# Actualizar theme_moove y de format_tiles a su última versión 
moosh -n plugin-list
moosh -n plugin-install -d --release 2021051700 theme_moove
moosh -n plugin-install -d --release 2020080613 format_tiles

# Copiar nueva plantilla footer sin div amarillo moodle
cp /init-scripts/themes/footer.mustache /var/www/html/theme/moove/templates




