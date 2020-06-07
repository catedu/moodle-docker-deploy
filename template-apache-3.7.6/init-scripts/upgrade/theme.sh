#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code, previous export should be made!)


# Load sensitive data or configurable data from a .env file
# export $(grep -E -v '^#' /init-scripts/.env | xargs)


# Config theme snap, already imported via plugins.sh
echo >&2 "Exporting theme..."

moosh config-set theme moove
moosh theme-settings-export --themename moove
cp moove*.tar.gz /init-scripts/themes/
moosh theme-settings-import --targettheme moove moove*tar.gz
php /var/www/html/admin/cli/upgrade.php
