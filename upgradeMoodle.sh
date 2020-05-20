#!/bin/bash
set -eu
# Initial Deploy
# Ver. 0.1 - bash
#
# Upgrade site by url or by install_directory.
# upgradeMoodle.sh ( -u "url" | -d "installdir" )

## .env source
# # for reverse nginx proxy:
# VIRTUAL_HOST=nacho.aeducar.es
# SSL_EMAIL=juandacorreo@gmail.com
# SSL_PROXY=true
# MOODLE_URL=https://nacho.aeducar.es

# # for database connection:
# MOODLE_DB_HOST=92.222.130.252
# MOODLE_DB_NAME=nacho_aeducar_es
# MOODLE_MYSQL_USER=nacho_aeducar_es
# MOODLE_MYSQL_PASSWORD=cXv9MebY


# # for installing moodle, user data:
# MOODLE_ADMIN_USER=admin
# MOODLE_ADMIN_PASSWORD=camb1ameperoYA
# MOODLE_ADMIN_EMAIL=sannacho@gmail.com
# MOODLE_LANG=es
# MOODLE_SITE_NAME=nacho
# MOODLE_SITE_FULLNAME=AEducAR de pruebas nacho

# Load env variables:
export $(grep -E -v '^#' .env | xargs)

# Define Backup dir dinamically by date. Change if its necessary
BACKUPDIR=$(date +%Y-%m-%d--%H-%M)

usage () {
    echo 'usage: upgradeMoodle.sh -u "site" -d "upgrade_version"'
    echo "help: upgradeMoodle.sh -h"
}

showHelp () {
    echo 'usage: upgradeMoodle.sh -u "url" -d "upgrade_version"'
    echo "Options:"
    echo "-u -> site to upgrade. Accept url or installdir"
    echo "-d -> directory template to upgrade"
    echo "-h this message"
}

get_parameter(){
    while getopts ":u:d:h" opt; do
        case $opt in
            u)
                WORKDIR="${OPTARG##*//}"
                [ ! -d "${WORKDIR}" ] && \
                { echo "$(basename $0): Dont exist Site to upgrade!"; usage; exit 1;}
            ;;
            d)
                [ ! -d "${OPTARG}" ] && \
                { echo "$(basename $0): Check Template Upgrade Directory!"; usage; exit 1;}
                TEMPLATEUDIR="${OPTARG}"
            ;;
            h)
                showHelp
                exit 0
            ;;
            \?)
                echo "Invalid option: -${OPTARG}" >&2
                exit 1
            ;;
            :)
                echo "Option -${OPTARG} requiere a field" >&2
                exit 1
            ;;
        esac
    done
    
    
    # Mandatory options
    [ -z ${TEMPLATEUDIR} ] && { echo "$(basename $0): You must to indicate a directory upgrade template"; usage; exit 1;}
    [ -z ${WORKDIR} ] && { echo "$(basename $0): You must to indicate a directory upgrade template"; usage; exit 1;}
    
}


install_pkg() {
    check_root
    
    if ! dpkg -s "${@:1}" >/dev/null 2>&1; then
        apt-get install -yq "${@:1}"
    fi
}

up_service(){
    (cd "${WORKDIR}" && docker-compose up -d) && \
    { echo "DEPLOY ${MOODLE_URL} UP!"; return 0; } || { echo "DEPLOY ${MOODLE_URL} UP FAIL!"; return 1;}
}

rollback(){
    case $STEP in
        end)
            ##Nothting to do
            return 0
        ;;
        backup|template)
            (cd "${WORKDIR}" && docker-compose down || true)
            echo "$(basename $0) - exit: Restore Files"
            rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
            { echo "$(basename $0) - exit: RESTORE FILES ${MOODLE_URL} FAIL!"; return 1; }
            echo "$(basename $0) - exit: Up services"
            up_service
        ;;
        stopservice)
            echo "$(basename $0) - exit: Up services"
            up_service
        ;;
        
        init)
            echo "$(basename $0) - exit: Nothing to do"
            return 0
        ;;
        *)
            echo "$(basename $0) - exit: Step Unknow to $WORKDIR. Review"
            return 1
        ;;
    esac
}

get_parameter "$@"

# Load general .env
export $(grep -E -v '^#' .env | xargs)

# Load WORKDIR .env (override general values)
export $(grep -E -v '^#' "${WORKDIR}/.env" | xargs)

trap 'rollback' INT TERM EXIT
## STEPS: init, stopservice,backup,template
STEP="init"
install_pkg mariadb-client rsync

## Stopservice
(cd "${WORKDIR}" && docker-compose down) && echo "$(basename $0) - stop services: Deploy ${WORKDIR} down!" || \
{ echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"; exit 1; }
STEP="stopservice"

## Backup
echo "$(basename $0) - Backup DB..."
mysqldump --user root --password=${MYSQL_ROOT_PASSWORD} --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > ${BACKUPDIR}/${WORKDIR}/db.sql || { echo "$(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

echo "$(basename $0) - Backup Files..."
rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "$(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
STEP="backup"

## Template
# Upgrade skel
cp -rf ${TEMPLATEUDIR}/* "${WORKDIR}" || { echo "$(basename $0) - template: Copy upgrade ${WORKDIR} FAIL!"; exit 1; }
## Upgrade .env file?

STEP="template"

## Up services
up_service

STEP="end"

# TO-DO
# - Mandar un correo al MOODLE_ADMIN_EMAIL????
# - También deberíamos tener claro si hacemos importación de datos y cómo
