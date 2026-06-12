#!/bin/bash


# GET PLUGIN LIST
echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"

# INSTALL PLUGINS (theme is installed in theme.sh)
echo >&2 "Installing plugins..."
echo "Moodle's version: ${VERSION}"
VERSION_MINOR=$(echo ${VERSION} | cut -d. -f1,2)
echo "Moodle's minor version: ${VERSION_MINOR}"

PLUGINS=(
        "theme_moove"
        "format_tiles"
        "block_xp"
        "availability_xp"
        "booktool_wordimport"
        "quizaccess_onesession"
        "mod_choicegroup"
        "mod_board"
        "block_grade_me"
        "mod_pdfannotator"
        "block_completion_progress"
        "local_mail"
        "qtype_gapfill"
        "mod_attendance"
        "mod_checklist"
        )

        #Forzamos la instalación de plugins de versiones anteriores:
        moosh module-manage show bigbluebuttonbn
    fi

for PLUGIN in "${PLUGINS[@]}"
do
    moosh plugin-install -d ${PLUGIN} 
done


echo >&2 "Plugins installed!"


