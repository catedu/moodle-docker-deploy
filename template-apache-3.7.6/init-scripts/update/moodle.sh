#!/bin/bash
# WHEN:
# - new-install
# - update (just to fix a bug!)
# - upgrade (if new parameters needed, expected to be empty)
​
​
​
# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)
​
echo >&2 "set value of max_file_size by default in courses"
moosh config-set maxbytes 52428800
​
​
echo >&2 "Adding Mentees block (Acceso Familias)"
moosh block-add category 1 mentees site-index side-pre 0
moosh sql-run "update mdl_block_instances SET parentcontextid=1, configdata='Tzo4OiJzdGRDbGFzcyI6MTp7czo1OiJ0aXRsZSI7czoxNToiQWNjZXNvIEZhbWlsaWFzIjt9' WHERE blockname='mentees'"