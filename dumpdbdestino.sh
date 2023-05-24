#!/bin/bash
set -eu

usage () {
    echo 'usage: dropdbdestino.sh [-y] -b server_destination(moodlefileserver) -i "identity_ssh_file" -u "dirsite" '
    echo "help: dropdbdestino.sh -h"
}

showHelp () {
    echo 'usage: dropdbdestino.sh [-y] -b server_destination(moodlefileserver) -i "identity_ssh_file" -u "dirsite" '
    echo "Options:"
    echo "-y -> Yes all questions"
    echo "-b -> drop its db at server_destination (moodlefileserver)"
    echo "-i -> Identity file (private key) to connect with remote server"
    echo "-u -> Site that use this db. Only accept installdir"
    echo "-h this message"
    echo "Backup moodle site and DB to migrate in /var/backup_migratedb/ "
    echo "DB in source server NOT delete...do it !!!"
}

get_parameter(){
    while getopts ":yb:i:u:h" opt; do
        case $opt in
            b)
                DBSERVER="${OPTARG}"
                ! ping -c 2 "${OPTARG}" > /dev/null 2>&1 && \
                { echo "# $(basename $0): Check DB ip addr!"; usage; exit 1;}
            ;;
            y)
                YES=true
            ;;
            i)
                IDENTITY_FILE="${OPTARG}"
                [ ! -r "${IDENTITY_FILE}" ] && \
                { echo "# $(basename $0): Identity File its not readeable!"; usage; exit 1;}
            ;;
            u)
                WORKDIR="${OPTARG##*//}"
                [ ! -d "${WORKDIR}" ] && \
                { echo "# $(basename $0): Dont exist that site!"; usage; exit 1;}
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
    [ -z ${DBSERVER+x} ] && { echo "# $(basename $0): You must to indicate a server db destination"; usage; exit 1;}
    [ -z ${IDENTITY_FILE+x} ] && { echo "# $(basename $0): You must to indicate a identity file to connect to another server"; usage; exit 1;}
    [ -z ${WORKDIR+x} ] && { echo "# $(basename $0): You must to indicate the moodle site that works with this db"; usage; exit 1;}
    
    # Test sucsessfull connection
    ! remote_command "exit" > /dev/null 2>&1 && \
    { echo "# $(basename $0): Cannot connect with destination server ${DBSERVER}. Identity problem?"; usage; exit 1;}
    
    return 0
    
}

remote_command(){
    [ -z "$1" ] && return 1
    ssh -o StrictHostKeyChecking=no -p 22987 -i "${IDENTITY_FILE}" "${REMOTEUSER}@${DBSERVER}" "$1"
}


install_pkg() {
    
    if ! dpkg -s "${@:1}" >/dev/null 2>&1; then
        sudo apt-get install -yq "${@:1}"
    fi
}

rollback(){
    echo "rollback $STEP"
    case $STEP in
        end)
            ##Nothting to do
            echo "# $(basename $0) - exit: All ok...enjoy ${WORKDIR}!!"
            return 0
        ;;
        # delete)
        #     echo "# $(basename $0) - exit (delete): All ok... Error after deleting"
        # ;;
        backup)
            echo "# $(basename $0) - exit (backup): Error at deleting, review DB for $WORKDIR"
            return 0  
        ;;
        init)
            echo "# $(basename $0) - exit: I've not started yet!!"
            return 0
        ;;
        *)
            echo "# $(basename $0) - exit: Step Unknow to $WORKDIR. Review"
            return 1
        ;;
    esac

    echo "# $(basename $0) - exit"
}

## STEPS: init, stopservice,backup,migrate,upremoteservice, end
STEP="init"

# Parameters
LOCALROOT=$(pwd)
YES=false
APIOVH="api-ovh"
## Remote user with sudo in destination
REMOTEUSER="debian"
REMOTEROOT="/var/moodle-docker-deploy"

get_parameter "$@"

trap 'rollback' INT TERM EXIT

# Load WORKDIR .env
set -a; [ -f "${WORKDIR}/.env" ] && . "${WORKDIR}/.env"; set +a

## END Parameters

install_pkg mariadb-client rsync

## Backup
echo "# $(basename $0) - Backup DB..."
BACKUPDIR="/var/backup_migratedb"
# Read .env variables in moodle destination
MYSQL_ROOT_PASSWORD_DESTINATION=$(remote_command "grep 'MYSQL_ROOT_PASSWORD=' ${REMOTEROOT}/.env | cut -d '=' -f2 | tr -d '\"'")
MOODLE_DB_HOST_DESTINATION=$(remote_command "grep 'MOODLE_DB_HOST=' ${REMOTEROOT}/.env | cut -d '=' -f2 | tr -d '\"'")

remote_command "mysqldump --lock-tables=false --user \"${MOODLE_MYSQL_USER}\" --password=\"${MOODLE_MYSQL_PASSWORD}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --databases \"${MOODLE_DB_NAME}\" > \"${BACKUPDIR}/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql\"" || { echo "# $(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

STEP="backup"

### DELETE ###
# echo "# $(basename $0) - Deleting DB in destination server ${DBSERVER}..."
### OJO QUE NO TENEMOS PERMISO....SOLO DESDE LA MAQUINA MOODLE CORRESPONDIENTE!
# delete database and user
# remote_command "mysql --user=root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --execute=\"DROP DATABASE ${MOODLE_DB_NAME}; DROP USER '${MOODLE_MYSQL_USER}'@'192.168.1.%'\"" || \
# { echo "# - ERROR at delete $WORKDIR DB..."; exit 1; }
# STEP="delete"

echo "# $(basename $0) - INFO: Remember DELETE Backup in source server moodle: ${BACKUPDIR}"

STEP="end"
