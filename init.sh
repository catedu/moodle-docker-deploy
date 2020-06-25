#!/bin/bash
# This script contains commands that should be executed first 
# time the containers goes up or after upgrades to update database


# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)


FILES="/init-scripts/${INSTALL_TYPE}/moodle.sh
/init-scripts/${INSTALL_TYPE}/plugins.sh
/init-scripts/${INSTALL_TYPE}/theme.sh"
for f in $FILES
do
	# after installation we disable this files so no exec is done after container restart
	if [ -x "$f" ]; then
		echo >&2 "$f executing..."
		$f
		echo >&2 "$f executed!"
	else
		echo >&2 "$f skipped, no x permission"
	fi
done

#TODO import moodle book
#TODO config repositories
#TODO install language packages and tune them

echo All done