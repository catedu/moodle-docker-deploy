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

# We should load env and download plugin list if we use this file!!!!
echo >&2 "No plugin configuration made!"



# GET PLUGIN LIST

# echo >&2 "Downloading plugin list..."
# moosh plugin-list >/dev/null
# echo >&2 "Plugin list downloaded!"


# INSTALL PLUGINS (them is installed in theme.sh)

# echo >&2 "Installing plugins..."
# moosh plugin-install -d <plugin-name>
# echo >&2 "Plugins installed!"


#  CONFIGURE PLUGINS

# echo >&2 "Configuring plugins..."
# echo >&2 "Configuring configurable_reports..."
# set +x # to get info 
# moosh config-set ....
# set -x
# echo >&2 "Plugins configurated!"
