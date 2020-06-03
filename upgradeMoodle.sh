#!/bin/bash
set -eu
# Initial Deploy
# Ver. 0.1 - bash
#
# Upgrade site by url or by install_directory.
# upgradeMoodle.sh [-p] [-y] ( -u "url" | -d "directory_upgrade" )

usage () {
    echo 'usage: upgradeMoodle.sh [-p] [-y] -u "site" -d "upgrade_version_template"'
    echo "help: upgradeMoodle.sh -h"
}

showHelp () {
    echo 'usage: upgradeMoodle.sh -u "url" -d "upgrade_version_template"'
    echo "Options:"
    echo "-p -> By defautl, moodle-code dir its delete. With -p preserve this directory"
    echo "-y -> Yes all questions"
    echo "-u -> site to upgrade. Accept url or installdir"
    echo "-d -> directory template to upgrade"
    echo "-h this message"
}

get_parameter(){
    while getopts ":pyu:d:h" opt; do
        case $opt in
            p)
                PRESERVE=true
            ;;
            y)
                YES=true
            ;;
            u)
                WORKDIR="${OPTARG##*//}"
                [ ! -d "${WORKDIR}" ] && \
                { echo "$(basename $0): Dont exist Site to upgrade!"; usage; exit 1;}
                WORKDIR=${WORKDIR%\/}
            ;;
            d)
                [ ! -d "${OPTARG}" ] && \
                { echo "$(basename $0): Check Template Upgrade Directory!"; usage; exit 1;}
                TEMPLATEUDIR="${OPTARG%\/}"
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
    [ -z ${TEMPLATEUDIR+x} ] && { echo "$(basename $0): You must to indicate a directory upgrade template"; usage; exit 1;}
    [ -z ${WORKDIR+x} ] && { echo "$(basename $0): You must to indicate a directory upgrade template"; usage; exit 1;}
    return 0
    
}


install_pkg() {
    
    if ! dpkg -s "${@:1}" >/dev/null 2>&1; then
        apt-get install -yq "${@:1}"
    fi
}

up_service(){
    if (cd "${WORKDIR}" && docker-compose up -d); then
        echo "DEPLOY ${MOODLE_URL} UP!"; return 0
    else
        echo "DEPLOY ${MOODLE_URL} UP FAIL!"; return 1
    fi
}

yq() { docker run --rm -i -v "${PWD}":/workdir mikefarah/yq yq "$@"; }

rollback(){
    case $STEP in
        end)
            ##Nothting to do
            echo "$(basename $0) - exit: All ok...enjoy ${NEWVERSION} moodle!!"
            return 0
        ;;
        template)
            (cd "${WORKDIR}" && docker-compose down || true)
            
            echo "$(basename $0) - exit: Restore DB"
            mysqldump --user root --password=${MYSQL_ROOT_PASSWORD} --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" < ${BACKUPDIR}/${WORKDIR}_db.sql > /dev/null || { echo "$(basename $0) - exit: Restore DB ${WORKDIR} FAIL!"; return 1; }
            
            echo "$(basename $0) - exit: Restore Files"
            rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
            { echo "$(basename $0) - exit: RESTORE FILES ${MOODLE_URL} FAIL!"; return 1; }
        ;;
        backup)
            echo "$(basename $0) - exit: Restore Files"
            rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
            { echo "$(basename $0) - exit: RESTORE FILES ${MOODLE_URL} FAIL!"; return 1; }
        ;;
        stopservice)
        ;;
        
        init)
            echo "$(basename $0) - exit: I've not started yet!!"
            return 0
        ;;
        *)
            echo "$(basename $0) - exit: Step Unknow to $WORKDIR. Review"
            return 1
        ;;
    esac
    # Commonn tasks if its possible to start
    echo "$(basename $0) - exit: Up services"
    up_service
}

trap 'rollback' INT TERM EXIT
## STEPS: init, stopservice,backup,template, end
STEP="init"

# Define Backup dir dinamically by date. Change if its necessary
BACKUPDIR=$(date +%Y-%m-%d--%H-%M)
mkdir -p ${BACKUPDIR} || { echo "$(basename $0) - init: Problems to create ${BACKUPDIR} backup"; exit 1; }

# Parameters
PRESERVE=false
YES=false
MOODLECODEDIR="moodle-code"
get_parameter "$@"

# Load general .env
export $(grep -E -v '^#' .env | xargs)

# Load WORKDIR .env (override general values)
export $(grep -E -v '^#' "${WORKDIR}/.env" | xargs)

NEWVERSION=$(yq r "${TEMPLATEUDIR}/docker-compose.yml" services.moodle.image | cut -d: -f2 | cut -d- -f1)

install_pkg mariadb-client rsync

## Stopservice
if (cd "${WORKDIR}" && docker-compose down); then
    echo "$(basename $0) - stop services: Deploy ${WORKDIR} DOWN!"
    elif (cd "${WORKDIR}" && [ -z "$(docker-compose ps -q)" ] ); then
    # Service stopped before
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} DOWN BEFORE!"
    exit 1
else
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"
    exit 1
fi
STEP="stopservice"

## Backup
echo "$(basename $0) - Backup DB..."
mysqldump --user root --password=${MYSQL_ROOT_PASSWORD} --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > ${BACKUPDIR}/${WORKDIR}_db.sql || { echo "$(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

echo "$(basename $0) - Backup Files..."
rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "$(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
STEP="backup"

## Template
# Delete moodle-code
if ! $PRESERVE && ( $YES || (read -r -p "Delete ${MOODLECODEDIR}? [s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )); then
    rm -rf "${WORKDIR:?}/${MOODLECODEDIR}" && echo "$(basename $0) - Clean: ${MOODLECODEDIR} Deleted !" || \
    { echo "$(basename $0) - Clean: ${MOODLECODEDIR} Deleted FAIL!"; exit 1; }
fi
# Upgrade skel
cp -rf ${TEMPLATEUDIR}/* "${WORKDIR}" || { echo "$(basename $0) - template: Copy upgrade ${WORKDIR} FAIL!"; exit 1; }

STEP="template"

## Upgrade .env file?
if [ "${VERSION}" = "${NEWVERSION}" ]; then
    # update
    sed  -i --follow-symlinks 's/INSTALL_TYPE.*/INSTALL_TYPE=update/g' "${WORKDIR}/.env"
else
    # upgrade
    sed  -i --follow-symlinks 's/INSTALL_TYPE.*/INSTALL_TYPE=upgrade/g' "${WORKDIR}.env"
fi

sed  -i --follow-symlinks "s/VERSION.*/VERSION=${NEWVERSION}/g" "${WORKDIR}/.env"

## Up services
up_service

STEP="end"

# TO-DO
# - Mandar un correo al MOODLE_ADMIN_EMAIL????
# - También deberíamos tener claro si hacemos importación de datos y cómo
