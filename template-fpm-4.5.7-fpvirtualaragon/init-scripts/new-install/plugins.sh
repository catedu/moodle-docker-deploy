#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code)
#
# Lee el catalogo de plugins desde /init-scripts/plugins.json y las variables
# PLUGIN_* del .env del sitio. Filtra por SCHOOL_TYPE e INSTALL_TYPE.

# Cargar helpers
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SCRIPT_DIR}/../lib/plugins-lib.sh"

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
        "block_grade_me")
            echo "Configuring block_grade_me..."
            moosh config-set block_grade_me_maxcourses 10
            moosh config-set block_grade_me_enableassign 1
            moosh config-set block_grade_me_enableassignment 1
            moosh config-set block_grade_me_enablequiz 1
            ;;
        "format_tiles")
            echo "Configuring format_tiles..."

            if [[ "${SCHOOL_TYPE}" = "FPD" ]]; then
                # TODO: configuracion especifica FPD
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
                #desactivamos la navegacion javascript en moodle4
                moosh -n config-set usejavascriptnav 0 format_tiles
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
        "mod_board")
            moosh config-set new_column_icon fa-plus mod_board
            moosh config-set new_note_icon fa-plus mod_board
            moosh config-set media_selection 1 mod_board
            moosh config-set post_max_length 250 mod_board
            moosh config-set history_refresh 60 mod_board
            ;;
        "block_configurable_reports")
            echo "Configuring configurable_reports..."
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
            ;;
        "local_educaaragon")
            echo "Configuring local_educaaragon..."
            php /init-scripts/new-install/educaaragon_setup.php
            ;;
        "mod_googlemeet")
            echo "Configuring mod_googlemeet..."
            echo "No additional automatic configuration required."
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

# Google Meet: elegir entre fork hyukudan (moderno) o legacy (ronefel).
# A diferencia de Moodle-Docker, aqui no se clona en build-time; se asume que
# el codigo correspondiente ya esta presente en moodle-code.
if [ "${PLUGIN_MOD_GOOGLEMEET_LEGACY:-false}" = "true" ]; then
    echo >&2 "Google Meet legacy (ronefel) seleccionado. Reemplazando mod/googlemeet..."
    if [ -d /var/www/html/mod/googlemeet_legacy ]; then
        rm -rf /var/www/html/mod/googlemeet
        cp -a /var/www/html/mod/googlemeet_legacy /var/www/html/mod/googlemeet
        PLUGIN_MOD_GOOGLEMEET=false
    else
        echo >&2 "WARNING: /var/www/html/mod/googlemeet_legacy no existe. No se puede activar legacy."
    fi
elif [ "${PLUGIN_MOD_GOOGLEMEET:-false}" = "true" ]; then
    # Hyukudan seleccionado: limpiar legacy para no ocupar espacio
    rm -rf /var/www/html/mod/googlemeet_legacy
fi

# GET PLUGIN LIST

echo >&2 "Downloading plugin list..."
moosh plugin-list >/dev/null
echo >&2 "Plugin list downloaded!"

# INSTALL PLUGINS (theme is installed in theme.sh)

echo >&2 "Installing plugins..."
echo "Moodle's version: ${VERSION}"
VERSION_MINOR=$(echo ${VERSION} | cut -d. -f1,2)
echo "Moodle's minor version: ${VERSION_MINOR}"

# Mostrar resumen antes de empezar
plugins_show_summary "${SCHOOL_TYPE}" "new-install"

# Iterar sobre los plugins habilitados para este SCHOOL_TYPE e INSTALL_TYPE
while IFS= read -r PLUGIN; do
    [ -z "$PLUGIN" ] && continue

    echo ""
    echo "===> Processing plugin: ${PLUGIN}"

    # Instalar via moosh -n si esta disponible para esta version
    if moosh -n plugin-list | grep "^${PLUGIN} " | grep "${VERSION_MINOR}" >/dev/null; then
        echo "trying to install ${PLUGIN} ..."
        moosh -n plugin-install -d ${PLUGIN} || echo "${PLUGIN} already present or install skipped"
    else
        echo "${PLUGIN} is not available in remote list for ${VERSION_MINOR}, checking local..."
    fi

    # Ejecutar acciones asociadas (configuracion post-instalacion)
    actions_asociated_to_plugin ${PLUGIN}
    LAST_PLUGIN="${PLUGIN}"
done < <(plugins_list_enabled "${SCHOOL_TYPE}" "new-install")

# Workaround: el ultimo plugin de moosh a veces no termina de activarse.
# Reprocesamos el ultimo plugin de la lista si existe.
if [ -n "${LAST_PLUGIN:-}" ]; then
    echo ""
    echo "===> Re-processing last plugin: ${LAST_PLUGIN}"
    if moosh -n plugin-list | grep "^${LAST_PLUGIN} " | grep "${VERSION_MINOR}" >/dev/null; then
        moosh -n plugin-install -d ${LAST_PLUGIN} || echo "${LAST_PLUGIN} already present or install skipped"
    fi
    actions_asociated_to_plugin ${LAST_PLUGIN}
fi

echo >&2 "Plugins installed!"


#  CONFIGURE PLUGINS
echo "Configuring plugins..."

# Configuracion global de Atto: solo si algun plugin Atto esta habilitado
if plugin_is_enabled "atto_fontsize" || plugin_is_enabled "atto_fontfamily" || plugin_is_enabled "atto_fullscreen"; then
    echo "Configuring editor_atto..."

    if [[ "${SCHOOL_TYPE}" = "FPD" ]]; then
        moosh config-set toolbar "collapse = collapse
style1 = title, fontsize, fontfamily, fontcolor, backcolor, bold, italic
list = unorderedlist, orderedlist
links = link
files = image, media, recordrtc, managefiles
h5p = h5p
style2 = underline, strike, subscript, superscript
align = align
indent = indent
insert = equation, charmap, table, clear
undo = undo
accessibility = accessibilitychecker, accessibilityhelper
other = html, fullscreen" editor_atto

        echo "Configuring atto_fontfamily..."
        moosh config-set fontselectlist "Arial=Arial, Helvetica, sans-serif;
Times=Times New Roman, Times, serif;
Courier=Courier New, Courier, mono;
Georgia=Georgia, Times New Roman, Times, serif;
Verdana=Verdana, Geneva, sans-serif;
Trebuchet=Trebuchet MS, Helvetica, sans-serif;
Escolar=Boo;" atto_fontfamily
    else
        #Forzamos la instalacion de plugins de versiones anteriores:
        moosh plugin-install -f atto_fontfamily
        moosh plugin-install -f atto_fontsize

        moosh config-set toolbar "collapse = collapse
style1 = title, fontsize, fontfamily, fontcolor, backcolor, bold, italic
list = unorderedlist, orderedlist
links = link
files = image, media, recordrtc, managefiles
h5p = h5p
style2 = underline, strike, subscript, superscript
align = align
indent = indent
insert = equation, charmap, table, clear
undo = undo
accessibility = accessibilitychecker, accessibilityhelper
other = html, fullscreen" editor_atto

        moosh config-set fontselectlist "Arial=Arial, Helvetica, sans-serif;
Times=Times New Roman, Times, serif;
Courier=Courier New, Courier, mono;
Georgia=Georgia, Times New Roman, Times, serif;
Verdana=Verdana, Geneva, sans-serif;
Trebuchet=Trebuchet MS, Helvetica, sans-serif;
Escolar=Boo;" atto_fontfamily

        moosh module-manage show bigbluebuttonbn
    fi
fi

# Prohibit to write to each other
echo "Prohibit to write to each other"
moosh role-update-capability student moodle/user:viewdetails prohibit 1 # ok
moosh role-update-capability student local/mail:mailsamerole prohibit 1 # ok

set -x
echo "Plugins configurated!"
