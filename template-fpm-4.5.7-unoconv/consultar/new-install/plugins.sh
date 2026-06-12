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

####################
# functions        #
####################
actions_asociated_to_plugin(){
    echo "Executing actions associated to plugin ${1} in environment ${SCHOOL_TYPE}..."
    case ${1} in
        "local_mail")
            echo "Configuring local_mail..."
            moosh config-set maxfiles 5 local_mail
            moosh config-set maxbytes 2097152 local_mail
            moosh config-set enablebackup 1 local_mail
            echo "Updating default notification preferences for local_mail"
            moosh config-set  message_provider_local_mail_mail_loggedin    popup   message
            moosh config-set  message_provider_local_mail_mail_loggedoff    popup   message
            ;;
        "block_grade_me")
            echo "Configuring block_grade_me..."
            moosh config-set block_grade_me_maxcourses 10
            moosh config-set block_grade_me_enableassign 1
            moosh config-set block_grade_me_enableassignment 1
            moosh config-set block_grade_me_enablequiz 1
            ;;
        "format_tiles")
            echo "Configuring format_tiles..."
            moosh config-set hovercolour "\#ff7000" format_tiles
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
            #desactivamos la navegación javascript en moodle4
            moosh -n config-set usejavascriptnav 0 format_tiles
            moosh -n config-set phototilesaltstyle 1 format_tiles
            moosh -n config-set phototiletitletransarency 0.3 format_tiles
            moosh config-set modalresources pdf,url,html format_tiles
            moosh config-set showprogresssphototiles 0 format_tiles
            moosh config-set showseczerocoursewide 1 format_tiles
            moosh config-set allowphototiles 1 format_tiles
            ;;
        "block_xp")
            echo "Configuring block_xp..."
            moosh config-set blocktitle "¡Sube de nivel!" block_xp
            ;;
        "mod_pdfannotator")
            echo "Configuring mod_pdfannotator..."
            moosh config-set usevotes 1 mod_pdfannotator
            ;;
        "mod_board")
            moosh config-set new_column_icon fa-plus mod_board
            moosh config-set new_note_icon fa-plus mod_board
            moosh config-set media_selection 1 mod_board
            moosh config-set post_max_length 250 mod_board
            moosh config-set history_refresh 60 mod_board
            ;;
        *)
            echo "No additional actions for plugin ${1}"
            ;;
    esac
    echo "Done with actions asociated to plugin ${1}."
}

####################
# main             #
####################

set +x # to get info 

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
         "local_mail"
         "block_grade_me"
         "block_completion_progress"
         "mod_pdfannotator"
         "qtype_gapfill"
         "mod_attendance"
         "mod_checklist"
        )

for PLUGIN in "${PLUGINS[@]}"
do
    moosh plugin-list | grep ${PLUGIN} | grep ${VERSION_MINOR} >/dev/null  && echo "trying to install ${PLUGIN} ..."  && moosh plugin-install -d ${PLUGIN} && actions_asociated_to_plugin ${PLUGIN} || echo "${PLUGIN} is not available for ${VERSION_MINOR}"
done

echo >&2 "Plugins installed!"


#  CONFIGURE PLUGINS
echo "Configuring plugins..."

# Prohibit to write to each other
echo "Prohibit to write to each other"
moosh role-update-capability student moodle/user:viewdetails prohibit 1 # ok
moosh role-update-capability student local/mail:mailsamerole prohibit 1 # ok

set -x
echo "Plugins configurated!"
