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
        moosh -n plugin-install -d -f --release 2020110900 "theme_moove" 
        moosh -n plugin-install -d "format_tiles"
        moosh -n plugin-install -d "mod_jitsi"
        moosh -n plugin-install -d "mod_hvp"
        moosh -n plugin-install -d "block_xp"
        moosh -n plugin-install -d "availability_xp"
        moosh -n plugin-install -d "booktool_wordimport"
        moosh -n plugin-install -d "block_configurable_reports"
        moosh -n plugin-install -d "report_coursestats" 
        moosh -n plugin-install -d "quizaccess_onesession"
        moosh -n plugin-install -d "mod_choicegroup"
        moosh -n plugin-install -d "mod_board"
        moosh -n plugin-install -d -f --release 2021051702 "block_grade_me"
        moosh -n plugin-install -d -f --release 2021113000 "mod_pdfannotator" 
        moosh -n plugin-install -d -f --release 2017121407 "local_mail"
        moosh -n plugin-install -d -f "atto_fontsize"
        moosh -n plugin-install -d -f "atto_fontfamily"
        moosh -n plugin-install -d -f "atto_fullscreen"
        moosh -n plugin-install -d -f "tool_migratehvp2h5p"
        moosh -n plugin-uninstall "atto_styles"
        fi

for PLUGIN in "${PLUGINS[@]}"
do
    moosh -n plugin-install -d ${PLUGIN} 
done


echo >&2 "Plugins installed!"


