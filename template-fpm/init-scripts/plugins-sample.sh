#!/bin/bash
# This script contains commands that should be executed first 
# time the containers goes up or after upgrades to update database

# example command: moosh -n config-set name value <plugin>

# Plugin list with [--release <build version>] if different from last
# hack: last one does never get active, so install one more


# Get plugin list

echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"

# Load sensitive data or configurable data from a .env file
export $(grep -E -v '^#' /init-scripts/.env | xargs)


echo >&2 "Installing plugins..."
moosh plugin-install -d theme_snap
moosh plugin-install -d theme_moove
moosh plugin-install -d format_tiles
moosh plugin-install -d mod_bigbluebuttonbn
moosh plugin-install -d mod_hvp
moosh plugin-install -d block_xp
moosh plugin-install -d availability_xp
moosh plugin-install -d report_benchmark
# for moodle 3.8
# moosh-plugin-install -d tool_opcache
moosh plugin-install -d block_configurable_reports
moosh plugin-install -d report_coursestats # this one (last one) fails, needs to get activated on screen
echo >&2 "Plugins installed!"


# Config smtp
echo >&2 "Configuring smtp..."
set -x
moosh config-set tool_generator_users_password ${TOOL_GENERATOR_PASSWORD}
moosh config-set smtphosts ${SMTP_HOSTS}
moosh config-set smtpsecure 
moosh config-set smtpauthtype LOGIN
moosh config-set smtpuser ${SMTP_USER}
moosh config-set smtppass ${SMTP_PASSWORD}
moosh config-set smtpmaxbulk ${SMTP_MAXBULK}
moosh config-set noreplyaddress ${NO_REPLY_ADDRESS}

# Config configurable_reports
echo >&2 "Configuring configurable_reports..."
moosh config-set cron_hour 0
moosh config-set cron_minute 0
moosh config-set crrepository jleyva/moodle-configurable_reports_repository block_configurable_reports
moosh config-set dbhost db block_configurable_reports
moosh config-set dbname moodle block_configurable_reports
moosh config-set dbpass dbpassword block_configurable_reports
moosh config-set dbuser dbuser block_configurable_reports
moosh config-set reportlimit 5000 block_configurable_reports
moosh config-set reporttableui datatables block_configurable_reports
moosh config-set sharedsqlrepository jleyva/moodle-custom_sql_report_queries block_configurable_reports
moosh config-set sqlsecurity 1 block_configurable_reports
moosh config-set sqlsyntaxhighlight 1 block_configurable_reports

# Config webservices #TODO check config
moosh -n config-set enablewebservices 1 core
moosh -n config-set enablemobilewebservice 1 core 

# Config mobile notifications #TODO check config
moosh -n config-set airnotifierurl https://messages.moodle.net
moosh -n config-set airnotifierport 443
moosh -n config-set airnotifiermobileappname com.moodle.moodlemobile
moosh -n config-set airnotifierappname commoodlemoodlemobile
moosh -n config-set airnotifieraccesskey #TODO

set +x

# Config theme snap
echo >&2 "Configuring theme..."

moosh config-set theme moove
# import theme settings:
# script needs to be in /var/www/html and name like theme_xxxx
# it can't have info about directories: ./  so next line is not valid and I have to "hack it"
# tar -zcf snap_settings.tar.gz -C /init-scripts/snap_settings .

# find /init-scripts/snap_settings -type f -printf "%f\n" | xargs tar -zcf snap_settings.tar.gz -C /init-scripts/snap_settings
# moosh -n theme-settings-import --targettheme snap snap_settings.tar.gz
cp /init-scripts/*tar.gz /var/www/html/
moosh -n theme-settings-import --targettheme moove *tar.gz


# moosh -n config-set bigbluebuttonbn_server_url 2.2.2.2
# moosh -n config-set bigbluebuttonbn_shared_secret thisIsMySecret
