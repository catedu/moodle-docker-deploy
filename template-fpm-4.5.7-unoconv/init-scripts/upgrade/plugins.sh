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

PLUGINS=( )

if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        PLUGINS=( 
                "theme_moove" 
                "format_tiles"
                # "mod_jitsi" FP will use blackboard LTI
                # "mod_hvp" is in the core of moodle >= 3.9 and FPD uses 3.10.1
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
                "mod_pdfannotator"
		"mod_pdfannotator"
                # for moodle 3.8 "tool_opcache"
        )
    else
        echo >&2 "Not FPD..."
        PLUGINS=( 
                "theme_moove" 
                "format_tiles"
                "mod_jitsi" 
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
		        "atto_fontsize" # No disponible para 4.0
                "atto_fontfamily" # No disponible para 4.1
                "atto_fullscreen"
                "qtype_gapfill"
                "mod_attendance"
                "mod_checklist"
                "mod_checklist" #repito porque si no el último plugin no termina de instalarse ok
        )
        
        #Forzamos la instalación de plugins de versiones anteriores:
        moosh plugin-install -f atto_fontfamily
        moosh plugin-install -f atto_fontsize
        
        moosh plugin-uninstall "tool_migratehvp2h5p"
        moosh plugin-uninstall "mod_hvp"
        moosh plugin-uninstall "block_configurable_reports"
        moosh plugin-uninstall "report_coursestats" 
        moosh plugin-uninstall "atto_morefontcolors"

        moosh module-manage show bigbluebuttonbn
        moosh module-manage hide jitsi
    fi

for PLUGIN in "${PLUGINS[@]}"
do
    moosh plugin-install -d ${PLUGIN} 
done


echo >&2 "Plugins installed!"


