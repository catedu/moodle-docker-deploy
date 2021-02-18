#!/bin/bash
set -eu

usage () {
    echo 'usage: deleteMoodle.sh [-y] [-b] -u "dirsite"'
    echo "help: deleteMoodle.sh -h"
}

showHelp () {
    echo 'usage: deleteMoodle.sh [-y] [-b] -u "dirsite"'
    echo "Options:"
    echo "-y -> Yes all questions"
    echo "-b -> By default, DB its not removed to new DB Server. With -b, remove DB"
    echo "-u -> Site to upgrade. Only accept installdir"
    echo "-h this message"
    echo "Backup moodle site and DB in /var/backup_migrate/ "
}

get_parameter(){
    while getopts ":ybu:h" opt; do
        case $opt in
            b)
                DELETEDB=true
            ;;
            y)
                YES=true
            ;;
            u)
                WORKDIR="${OPTARG##*//}"
                [ ! -d "${WORKDIR}" ] && \
                { echo "# $(basename $0): Dont exist Site to delete!"; usage; exit 1;}
                WORKDIR=${WORKDIR%\/}
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
    [ -z ${WORKDIR+x} ] && { echo "# $(basename $0): You must to indicate a moodle site to delete"; usage; exit 1;}
    
    return 0
    
}

install_pkg() {
    
    if ! dpkg -s "${@:1}" >/dev/null 2>&1; then
        sudo apt-get install -yq "${@:1}"
    fi
}

# Parameters
LOCALROOT=$(pwd)
YES=false
APIOVH="api-ovh"
DELETEDB=false
get_parameter "$@"

BACKUPDIR="/var/backup_delete/$(date +%Y-%m-%d--%H-%M)__${WORKDIR}"
sudo mkdir -p "${BACKUPDIR}" && sudo chown debian:debian "${BACKUPDIR}" || { echo "# $(basename $0) - init: Problems to create ${BACKUPDIR} backup"; exit 1; }

# Load WORKDIR .env
set -a; [ -f "${WORKDIR}/.env" ] && . "${WORKDIR}/.env"; set +a

## END Parameters

install_pkg mariadb-client rsync

## Stopservice
if (cd "${WORKDIR}" && docker-compose down); then
    echo "# $(basename $0) - stop services: Deploy ${WORKDIR} DOWN!"
else
    echo "# $(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"
    ( $YES || (read -r -p "Do you want to continue DELETE?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
fi

## Backup ##
echo "# $(basename $0) - Backup DB..."
mysqldump --lock-tables=false --user ${MOODLE_MYSQL_USER} --password="${MOODLE_MYSQL_PASSWORD}" --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > ${BACKUPDIR}/${WORKDIR}_db.sql || { echo "# $(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

echo "# $(basename $0) - Backup Files..."
if grep "${LOCALROOT}/${WORKDIR}/moodle-data/repository/cursosministerio" /proc/mounts >/dev/null; then
    sudo umount "${LOCALROOT}/${WORKDIR}/moodle-data/repository/cursosministerio"
fi
sudo rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "# $(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
## End Backup

## Delete ###
echo "# $(basename $0) - Deleting source moodle..."
sudo rm -rf "${WORKDIR}" ||  echo "# - ERROR to clean source moodle directory"

## Clean DB?
if ${DELETEDB} ; then
    # Check if I can!....its my DB server?
    MYMOODLE_DB_SERVER=$(grep 'MOODLE_DB_HOST=' ${LOCALROOT}/.env | cut -d '=' -f2)
    echo "# $(basename $0) - Delete DB and USER in source DB server"
    if [ "${MOODLE_DB_HOST}" = "${MYMOODLE_DB_SERVER}" ]; then
        MYSQL_ROOT_PASSWORD=$(grep 'MYSQL_ROOT_PASSWORD=' ${LOCALROOT}/.env | cut -d '"' -f2)
        mysql --user=root --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="DROP DATABASE ${MOODLE_DB_NAME}; DROP USER '${MOODLE_MYSQL_USER}'@'192.168.1.%'" || \
        echo "# - ERROR at Delete DB and User in DB server"
    else
        echo "# - INFO: I CANT DELETE DB ${MOODLE_DB_NAME} and USER ${MOODLE_MYSQL_USER} in ${MOODLE_DB_HOST}!"
    fi
    
fi
## End Delete

## Delete DNS ##
echo "# $(basename $0) - Change DNS..."
(cd "${APIOVH}" && node deleteSubdomain.js "https://${VIRTUAL_HOST}") \
|| echo "# - ERROR to delete DNS..."
### End DNS

echo "# $(basename $0) - INFO: Remember DELETE Backup in source server moodle: ${BACKUPDIR}"

