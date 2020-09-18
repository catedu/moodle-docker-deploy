#!/bin/bash
set -eu

usage () {
    echo 'usage: upgradeMoodle.sh [-p] [-y] [-e env-update] -u "url|dirsite" -d "upgrade_version_template"'
    echo "help: upgradeMoodle.sh -h"
}

showHelp () {
    echo 'usage: upgradeMoodle.sh [-p] [-y] [-e env-update] -u "url|dirsite" -d "upgrade_version_template"'
    echo "Options:"
    echo "-p -> By defautl, moodle-code dir its delete. With -p preserve this directory"
    echo "-y -> Yes all questions"
    echo "-e -> Add or modify env site variables"
    echo "-u -> site to upgrade. Accept url or installdir"
    echo "-d -> directory template to upgrade"
    echo "-h this message"
}

get_parameter(){
    while getopts ":pye:u:d:h" opt; do
        case $opt in
            p)
                PRESERVE=true
            ;;
            y)
                YES=true
            ;;
            e)
                ENVUPDATE="${OPTARG}"
                [ ! -f "${ENVUPDATE}" ] && \
                { echo "$(basename $0): Dont exist env file to update!"; usage; exit 1;}
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
    [ -z ${WORKDIR+x} ] && { echo "$(basename $0): You must to indicate a directory to upgrade"; usage; exit 1;}
    return 0
    
}


install_pkg() {
    
    if ! dpkg -s "${@:1}" >/dev/null 2>&1; then
        sudo apt-get install -yq "${@:1}"
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
            sudo rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
            { echo "$(basename $0) - exit: RESTORE FILES ${MOODLE_URL} FAIL!"; return 1; }
        ;;
        backup)
            echo "$(basename $0) - exit: Restore Files"
            sudo rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
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

merge_envs(){
    # merge_envs env1 env2
    # Results in env1 with env2 differences, and print merged file o error
    
    tmpfile=$(mktemp tmpenvs.XXXXXX)
    # Base is in general .env
    if [ -z "${1}" ] || ! [ -f  "${1}" ]; then
        echo "No correct first env provided to merge"
        return 1
    fi
    if [ -z "${2}" ] || ! [ -f  "${2}" ]; then
        echo "No correct second env provided to merge"
        return 1
    fi
    
    cp "${1}" "${tmpfile}" || return 1
    
    # env customize deploy
    while read -r line; do
        if ! [[ "$line" =~ ^$|#.* ]] && [[ "$line" =~ .*=.* ]]; then
            if grep -q "${line%%=*}=" "${tmpfile}" >/dev/null 2>&1; then
                sed -iu "s/${line%%=*}=.*/$line/" "${tmpfile}"
            else
                echo "$line" >> "${tmpfile}"
            fi
        fi
    done < "${2}"
    tee ${1} < "${tmpfile}" && rm -f "${tmpfile}"
}

trap 'rollback' INT TERM EXIT
## STEPS: init, stopservice,backup,template, end
STEP="init"

# Define Backup dir dinamically by date. Change if its necessary
BACKUPDIR="/var/backup_upgrade/$(date +%Y-%m-%d--%H-%M)"
mkdir -p "${BACKUPDIR}" || { echo "$(basename $0) - init: Problems to create ${BACKUPDIR} backup"; exit 1; }

# Parameters
PRESERVE=false
YES=false
MOODLECODEDIR="moodle-code"
get_parameter "$@"
# WORKDIR -> Site Directory || TEMPLATEUDIR -> New template to apply

# Load general .env for run backup
set -a; [ -f .env ] && . .env; set +a

# Load WORKDIR .env (override general values)
set -a; [ -f "${WORKDIR}/.env" ] && . "${WORKDIR}/.env"; set +a

NEWVERSION=$(yq r "${TEMPLATEUDIR}/docker-compose.yml" services.moodle.image | cut -d: -f2 | cut -d- -f1)

[[ "${VERSION}" > "${NEWVERSION}" ]] && \
{ echo "Do you want to downgrade moodle? WTF??"; exit 1; }
[[ "${VERSION}" = "${NEWVERSION}" ]] || \
( $YES || (read -r -p "Do you want to make UPRGADE? Its not a UPDATE...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 0

install_pkg mariadb-client rsync

## Stopservice
if (cd "${WORKDIR}" && docker-compose stop web moodle && echo "Y" | docker-compose rm web moodle); then
    echo "$(basename $0) - stop services: Deploy ${WORKDIR} DOWN!"
    elif (cd "${WORKDIR}" && [ -z "$(docker-compose ps -q)" ] ); then
    # Service stopped before
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} DOWN BEFORE!"
    ( $YES || (read -r -p "Do you want to continue UPDATE?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
else
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"
    ( $YES || (read -r -p "Do you want to continue UPDATE?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
fi
STEP="stopservice"

## Backup
echo "$(basename $0) - Backup DB..."
mysqldump --user root --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > ${BACKUPDIR}/${WORKDIR}_db.sql || { echo "$(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

echo "$(basename $0) - Backup Files..."
sudo rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "$(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
STEP="backup"

## Template
# Delete moodle-code or not
if ! $PRESERVE && ( $YES || (read -r -p "Delete ${MOODLECODEDIR}? [s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )); then
    sudo rm -rf "${WORKDIR:?}/${MOODLECODEDIR}" && echo "$(basename $0) - Clean: ${MOODLECODEDIR} Deleted !" || \
    { echo "$(basename $0) - Clean: ${MOODLECODEDIR} Deleted FAIL!"; exit 1; }
fi
# Upgrade skel
rsync -av --copy-links "${TEMPLATEUDIR}"/ "${WORKDIR}" || { echo "$(basename $0) - template: Copy upgrade ${WORKDIR} FAIL!"; exit 1; }
# Upgrade new general variables if its indicated
[ -n "${ENVUPDATE+x}" ] && merge_envs "${WORKDIR}/.env" "${ENVUPDATE}" > /dev/null
STEP="template"

## Upgrade .env file with new INSTALL_TYPE before up services
if [[ "${VERSION}" = "${NEWVERSION}" ]]; then
    # update
    sed  -iu --follow-symlinks 's/INSTALL_TYPE.*/INSTALL_TYPE=update/g' "${WORKDIR}/.env"
else
    # upgrade
    sed  -iu --follow-symlinks 's/INSTALL_TYPE.*/INSTALL_TYPE=upgrade/g' "${WORKDIR}.env"
fi

sed  -iu --follow-symlinks "s/VERSION.*/VERSION=${NEWVERSION}/g" "${WORKDIR}/.env"

# Load  .env (changes)
set -a; [ -f "${WORKDIR}/.env" ] && . "${WORKDIR}/.env"; set +a

## Up services
up_service

STEP="end"
