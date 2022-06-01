#!/bin/bash

# Copying new footer template without yellow moodle div
echo >&2 "Updating footer.mustache..."
cp /init-scripts/themes/footer.mustache /var/www/html/theme/moove/templates

# Fixing error with contacts at moove
echo >&2 "Updating mypublic.mustache..."
cp /init-scripts/themes/mypublic.mustache /var/www/html/theme/moove/templates

#Updating Escolar typography
echo >&2 "Updating Escolar typography..."
cp /init-scripts/themes/booFont/Boo.* /var/www/html/theme/moove/fonts/





