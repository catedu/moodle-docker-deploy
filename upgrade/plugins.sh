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
export $(grep -E -v '^#' /init-scripts/.env | xargs)

# GET PLUGIN LIST

echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"


# INSTALL PLUGINS (them is installed in theme.sh)

echo >&2 "Installing plugins..."
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


#  CONFIGURE PLUGINS

echo >&2 "Configuring plugins..."
echo >&2 "Configuring configurable_reports..."
set +x # to get info 
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
set -x
echo >&2 "Plugins configurated!"
