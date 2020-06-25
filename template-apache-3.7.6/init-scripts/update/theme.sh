#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code, previous export should be made!)


# Load sensitive data or configurable data from a .env file
# export $(grep -E -v '^#' /init-scripts/.env | xargs)


# Config theme snap, already imported via plugins.sh
echo >&2 "No theme configuration made!"