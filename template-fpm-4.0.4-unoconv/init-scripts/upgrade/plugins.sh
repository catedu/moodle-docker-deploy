#!/bin/bash


# GET PLUGIN LIST
echo >&2 "Downloading plugin list..."
moosh -n  plugin-list >/dev/null
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
                "local_mail"
		        # "atto_fontsize" No disponible para 4.0
                "atto_fontfamily"
                "atto_fullscreen"
                "qtype_gapfill"
                "mod_attendance"
                "mod_checklist"
                "mod_checklist" #repito porque si no el último plugin no termina de instalarse ok
        )
        moosh -n plugin-uninstall "tool_migratehvp2h5p"
        moosh -n plugin-uninstall "mod_hvp"
        moosh -n plugin-uninstall "block_configurable_reports"
        moosh -n plugin-uninstall "report_coursestats" 
        moosh -n plugin-uninstall "block_completion_progress"
        moosh -n plugin-uninstall "atto_morefontcolors"
fi

for PLUGIN in "${PLUGINS[@]}"
do
    moosh -n plugin-install -d ${PLUGIN} 
done


echo >&2 "Plugins installed!"


