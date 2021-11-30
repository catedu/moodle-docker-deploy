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

#
# Functions
# 
actions_asociated_to_plugin(){
    echo "Actions for plugin ${1}..."
    case ${1} in
        "mod_hvp")
            #Comento las siguientes líneas ya que han desaparecido esas capacidades.
            #echo "Permitir a todos los profesores instalar módulos h5p"
            #moosh role-update-capability editingteacher mod/hvp:updatelibraries allow 1
            #moosh role-update-capability editingteacher mod/hvp:installrecommendedh5plibraries allow 1
            #moosh role-update-capability editingteacher mod/hvp:userestrictedlibraries allow 1
            echo "Updating default notification preferences for h5p"
            moosh config-set  message_provider_mod_hvp_confirmation_loggedin    popup,airnotifier  message
            moosh config-set  message_provider_mod_hvp_confirmation_loggedoff    popup,airnotifier  message
            moosh config-set  message_provider_mod_hvp_submission_loggedin    popup   message
            moosh config-set  message_provider_mod_hvp_submission_loggedoff    popup   message
            echo "Configuring h5p..."
            moosh config-set enable_save_content_state 1 mod_hvp
            moosh config-set enable_lrs_content_types 1 mod_hvp
            ;;
        "local_mail")
            echo "Configuring local_mail..."
            moosh config-set maxfiles 5 local_mail
            moosh config-set maxbytes 2097152 local_mail
            moosh config-set enablebackup 1 local_mail
            echo "Updating default notification preferences for local_mail"
            moosh config-set  message_provider_local_mail_mail_loggedin    popup   message
            moosh config-set  message_provider_local_mail_mail_loggedoff    popup   message
            ;;
        "mod_jitsi")
            echo "Configuring jitsi..."
            moosh config-set jitsi_livebutton 1
            moosh config-set jitsi_shareyoutube 1
            moosh config-set jitsi_id nameandsurname
            moosh config-set jitsi_finishandreturn 1
            moosh config-set jitsi_sesionname 0,1,2
            moosh config-set jitsi_domain meet.jit.si
            moosh config-set jitsi_watermarklink https://jitsi.org
            moosh config-set jitsi_channellastcam 4
            ;;
        "filter_wiris")
            echo "Configuring filter_wiris..."
            moosh config-set editor_enable '1' filter_wiris
            moosh config-set chem_editor_enable '1' filter_wiris
            moosh config-set allow_editorplugin_active_course '0' filter_wiris
            moosh config-set imageservicehost 'www.wiris.net' filter_wiris
            moosh config-set imageservicepath '/demo/editor/render' filter_wiris
            moosh config-set imageserviceprotocol 'https' filter_wiris
            moosh config-set rendertype 'php' filter_wiris
            moosh config-set imageformat 'svg' filter_wiris
            moosh config-set pluginperformance '1' filter_wiris
            moosh config-set editormodalwindowfullscreen '0' filter_wiris
            moosh config-set access_provider_enabled '0' filter_wiris
            sudo chmod o-w filter/wiris
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

            if [[ "${SCHOOL_TYPE}" = "FPD" ]];
                then
                    echo "TO-DO"
                else
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
            fi
            
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
        *)
            echo "No additional actions for plugin ${1}"
            ;;
    esac
    echo "Done with actions asociated to plugin ${1}."
}

#
# main
#

# GET PLUGIN LIST

echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"


# INSTALL PLUGINS (theme is installed in theme.sh)

echo >&2 "Installing plugins..."
echo "Moodle's version: ${VERSION}"
VERSION_MINOR=$(echo ${VERSION} | cut -d. -f1,2)
echo "Moodle's minor version: ${VERSION_MINOR}"

PLUGINS=( )

if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        PLUGINS=( 
                "theme_moove" 
                "format_tiles"
                "block_xp"
                "availability_xp"
                "report_benchmark" # 02/03/2021 report_benchmark is not available for 3.10
                "booktool_wordimport"
                "local_mail"
                "block_configurable_reports"
                "report_coursestats" # this one (last one) fails, needs to get activated on screen
                                    # 02/03/2021 report_coursestats is not available for 3.10
                "report_coursesize" # 02/03/2021 report_coursesize is not available for 3.10
                "block_grade_me" # 02/03/2021 block_grade_me is not available for 3.10
                "quizaccess_onesession"
                "mod_choicegroup"
                "block_completion_progress"
                "atto_fullscreen"
                "atto_morebackcolors"
                "atto_morefontcolors"
                "qtype_gapfill"
                "mod_pdfannotator"
                "mod_pdfannotator"
                # for moodle 3.8 "tool_opcache"
        )
    else
        PLUGINS=( 
                "theme_moove" 
                "format_tiles"
                "mod_jitsi"
                #"mod_hvp" #Itsn't necessary for 3.10.4 Moodle version
                "block_xp"
                "availability_xp"
                # "report_benchmark"
                "booktool_wordimport"
                "local_mail"
                "block_configurable_reports"
                "report_coursestats" # this one (last one) fails, needs to get activated on screen
                # "report_coursesize"
                # "atto_wiris"
                # "filter_wiris"
                # "block_grade_me" #It has some problems with 3.10.4 Moodle version. It'r installed from afterInstall.py script
                "quizaccess_onesession"
                "mod_choicegroup"
                "mod_pdfannotator"
                "mod_pdfannotator"
                # for moodle 3.8 "tool_opcache"
        )
fi

for PLUGIN in "${PLUGINS[@]}"
do
    moosh plugin-list | grep ${PLUGIN} | grep ${VERSION_MINOR} >/dev/null  && moosh plugin-install -d ${PLUGIN} && actions_asociated_to_plugin ${PLUGIN} || echo "${PLUGIN} is not available for ${VERSION_MINOR}"
done

echo >&2 "Plugins installed!"


#  CONFIGURE PLUGINS

echo "Configuring plugins..."

echo "Configuring configurable_reports..."
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

echo "Configuring h5p..."
moosh config-set allowframembedding 1

echo "Configuring format_tiles..."
moosh config-set format tiles moodlecourse

#
moosh config-set legacynav 0 local_nav

# Prohibit to write to each other
echo "Prohibit to write to each other"
moosh role-update-capability student moodle/user:viewdetails prohibit 1
moosh role-update-capability student local/mail:mailsamerole prohibit 1

set -x
echo "Plugins configurated!"
