#!/bin/bash
# This script contains commands that should be executed first 
# time the containers goes up or after upgrades to update database

# example command: moosh config-set name value <plugin>

# Plugin list with [--release <build version>] if different from last
# hack: last one does never get active, so install one more


echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"				


echo >&2 "Installing plugins..."
moosh plugin-install -d theme_snap
moosh plugin-install -d mod_bigbluebuttonbn
moosh plugin-install -d mod_hvp
moosh plugin-install -d block_xp
moosh plugin-install -d availability_xp 
echo >&2 "Plugins installed!"

moosh config-set theme snap
moosh theme-settings-import snap_settings.tar.gz
# moosh config-set bigbluebuttonbn_server_url 2.2.2.2
# moosh config-set bigbluebuttonbn_shared_secret thisIsMySecret
