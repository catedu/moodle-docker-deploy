#!/bin/bash

cp /init-scripts/themes/frontpage.mustache /var/www/html/theme/moove/templates
cp /init-scripts/themes/booFont/* /var/www/html/theme/moove/fonts/

echo >&2 "Theme configured."




