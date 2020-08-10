#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code)
#
# example command: moosh config-set name value <plugin>
# Plugin list with [--release <build version>] if different from last
# hack: last one does never get active, so install one more

# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)

# GET PLUGIN LIST

echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"


# INSTALL PLUGINS (them is installed in theme.sh)

echo >&2 "Installing plugins..."
moosh plugin-install -d theme_moove
moosh plugin-install -d format_tiles
moosh plugin-install -d mod_jitsi
moosh plugin-install -d mod_hvp
moosh plugin-install -d block_xp
moosh plugin-install -d availability_xp
moosh plugin-install -d report_benchmark
moosh plugin-install -d booktool_wordimport
moosh plugin-install -d local_mail
# for moodle 3.8
# moosh-plugin-install -d tool_opcache
moosh plugin-install -d block_configurable_reports
moosh plugin-install -d report_coursestats # this one (last one) fails, needs to get activated on screen
echo >&2 "Plugins installed!"


#  CONFIGURE PLUGINS

echo >&2 "Configuring plugins..."
echo >&2 "Configuring configurable_reports..."
set +x # to get info 
moosh config-set cron_hour 1 block_configurable_reports
moosh config-set cron_minute 15 block_configurable_reports
moosh config-set crrepository jleyva/moodle-configurable_reports_repository block_configurable_reports
moosh config-set dbhost ${MOODLE_DB_HOST} block_configurable_reports
moosh config-set dbname ${MOODLE_DB_NAME} block_configurable_reports
moosh config-set dbuser ${MOODLE_DB_USER} block_configurable_reports
moosh config-set dbpass ${MOODLE_DB_PASSWORD} block_configurable_reports
moosh config-set reportlimit 5000 block_configurable_reports
moosh config-set reporttableui datatables block_configurable_reports
moosh config-set sharedsqlrepository jleyva/moodle-custom_sql_report_queries block_configurable_reports
moosh config-set sqlsecurity 1 block_configurable_reports
moosh config-set sqlsyntaxhighlight 1 block_configurable_reports

echo >&2 "Configuring jitsi..."
moosh config-set jitsi_livebutton 1
moosh config-set jitsi_shareyoutube 1
moosh config-set jitsi_id nameandsurname
moosh config-set jitsi_finishandreturn 1
moosh config-set jitsi_sesionname 0,1,2
moosh config-set jitsi_domain meet.jit.si
moosh config-set jitsi_watermarklink https://jitsi.org
moosh config-set jitsi_channellastcam 4

echo >&2 "Configuring h5p..."
moosh config-set enable_save_content_state 1 mod_hvp
moosh config-set enable_lrs_content_types 1 mod_hvp
moosh config-set allowframembedding 1

echo >&2 "Configuring format_tiles..."
moosh config-set hovercolour "\#ff7000" format_tiles
moosh config-set format tiles moodlecourse
moosh config-set followthemecolour 1 format_tiles
moosh config-set tilecolour1 "\#6e0e0f" format_tiles
moosh config-set colourname1 "Granate corporativo" format_tiles
moosh config-set tilecolour2 "\#4EA399" format_tiles
moosh config-set colourname2 "Aguamarina" format_tiles
moosh config-set tilecolour3 "\#854EA3" format_tiles
moosh config-set colourname3 "Morado" format_tiles
moosh config-set tilecolour4 "\#4A5B8C" format_tiles
moosh config-set colourname4 "Azul" format_tiles
moosh config-set tilecolour5 "\#4F9949" format_tiles
moosh config-set colourname5 "Verde" format_tiles
moosh config-set tilecolour6 "\#EA0009" format_tiles
moosh config-set colourname6 "Rojo" format_tiles
moosh config-set modalresources pdf,url,html format_tiles
moosh config-set showprogresssphototiles 0 format_tiles
moosh config-set showseczerocoursewide 1 format_tiles
moosh config-set allowphototiles 1 format_tiles

echo >&2 "Configuring block_xp..."
moosh config-set blocktitle "¡Sube de nivel!" block_xp

echo >&2 "Configuring local_mail..."
moosh config-set maxfiles 5 local_mail
moosh config-set maxbytes 2097152 local_mail
moosh config-set enablebackup 1 local_mail
moosh config-set legacynav 0 local_nav
moosh role-update-capability student local/mail:mailsamerole prevent 1

set -x
echo >&2 "Plugins configurated!"