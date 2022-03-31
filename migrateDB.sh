#!/bin/bash
set -eu

usage () {
    echo 'usage: migrateDB.sh [-y] -b server_destination(moodlefileserver) -i "identity_ssh_file" -u "dirsite" '
    echo "help: migrateDB.sh -h"
}

showHelp () {
    echo 'usage: migrateDB.sh [-y] -b server_destination(moodlefileserver) -i "identity_ssh_file" -u "dirsite" '
    echo "Options:"
    echo "-y -> Yes all questions"
    echo "-b -> move DB to another server_destination (moodlefileserver)"
    echo "-i -> Identity file (private key) to connect with remote server"
    echo "-u -> Site that use this db. Only accept installdir"
    echo "-h this message"
    echo "Backup moodle site and DB to migrate in /var/backup_migrate/ "
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

up_service(){
    if (cd "${WORKDIR}" && docker-compose up -d); then
        echo "DEPLOY ${MOODLE_URL} UP!"; return 0
    else
        echo "DEPLOY ${MOODLE_URL} UP FAIL!"; return 1
    fi
    # Restore bind mount cursos
    $CURSOSMIN && sudo mount -o bind "${LOCALROOT}/zz_cursos_cidead ${LOCALROOT}/${WORKDIR}/moodle-data/repository/cursosministerio"
}

rollback(){
    echo "rollback $STEP"
    case $STEP in
        end)
            ##Nothting to do
            echo "# $(basename $0) - exit: All ok...enjoy ${WORKDIR} with db in ${DBSERVER}!!"
            return 0
        ;;
        upserviceremotedb)
            ##Nothting to do
            echo "# $(basename $0) - exit: All ok...enjoy ${WORKDIR} db in ${DBSERVER}, but problems to clean LOCAL instance!!"
            return 0
        ;;
        migrate|backup)
            echo "# $(basename $0) - exit (backup): Remove backup local"
            [ -d "${BACKUPDIR}" ] && sudo rm "${BACKUPDIR}/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql" || \
            echo "# $(basename $0) - exit: CLEAN BACKUP FAIL!"
            
            echo "# $(basename $0) - exit (backup): Remove moodle and db in destination"
            remote_command "mysql --user=root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --execute=\"DROP DATABASE ${MOODLE_DB_NAME}; DROP USER '${MOODLE_MYSQL_USER}'@'192.168.1.%'\"" || \
                { echo "# - ERROR: at Drop DB an USER in SERVERDB destination..."; } 
            sed -i --follow-symlinks 's/MOODLE_DB_HOST.*/MOODLE_DB_HOST='"${MOODLE_DB_HOST}"'/g' ${LOCALROOT}/${WORKDIR}/.env || { echo "# - ERROR update .env: Update host_db in .env ${WORKDIR} FAIL!"; exit 1; }       
        ;;
        stopservice)
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

    echo "# $(basename $0) - exit: UP local service"
    
    up_service
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

## Stopservice
if (cd "${WORKDIR}" && docker-compose down); then
    echo "# $(basename $0) - stop services: Deploy ${WORKDIR} DOWN!"
else
    echo "# $(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"
    ( $YES || (read -r -p "Do you want to continue MIGRATEDB?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
fi
STEP="stopservice"

## Backup
echo "# $(basename $0) - Backup DB..."
BACKUPDIR="/var/backup_migratedb"
mysqldump --lock-tables=false --user "${MOODLE_MYSQL_USER}" --password="${MOODLE_MYSQL_PASSWORD}" --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > "${BACKUPDIR}/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql" || { echo "# $(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

STEP="backup"

### MIGRATE ###
echo "# $(basename $0) - Recreating DB in destination server ${DBSERVER}..."
## Create DB
# Read .env variables in moodle destination
MYSQL_ROOT_PASSWORD_DESTINATION=$(remote_command "grep 'MYSQL_ROOT_PASSWORD=' ${REMOTEROOT}/.env | cut -d '=' -f2 | tr -d '\"'")
MOODLE_DB_HOST_DESTINATION=$(remote_command "grep 'MOODLE_DB_HOST=' ${REMOTEROOT}/.env | cut -d '=' -f2 | tr -d '\"'")

### OJO QUE NO TENEMOS PERMISO....SOLO DESDE LA MAQUINA MOODLE CORRESPONDIENTE!
# create database, user and grants
remote_command "mysql --user=root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --execute=\"CREATE DATABASE ${MOODLE_DB_NAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; CREATE USER '${MOODLE_MYSQL_USER}'@'192.168.1.%' IDENTIFIED BY '${MOODLE_MYSQL_PASSWORD}'; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON ${MOODLE_DB_NAME}.* to '${MOODLE_MYSQL_USER}'@'192.168.1.%'\"" || \
{ echo "# - ERROR at create $WORKDIR DB..."; exit 1; }

# Restore DB in DB server destination
scp -o StrictHostKeyChecking=no -P 22987 -i "${IDENTITY_FILE}" "${BACKUPDIR}/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql" "${REMOTEUSER}@${DBSERVER}:/tmp/" > /dev/null && \
remote_command "mysql --user root --password='${MYSQL_ROOT_PASSWORD_DESTINATION}' --host='${MOODLE_DB_HOST_DESTINATION}' < '/tmp/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql'" || { echo "# - ERROR: Restore DB in remote server ${MOODLE_DB_HOST_DESTINATION} of ${WORKDIR} FAIL!"; exit 1; }
remote_command "rm -f '/tmp/${WORKDIR}_db_$(date +%Y-%m-%d--%H-%M).sql'" || true

# Update env pointing new db at destination
## DB_HOST. DB_PASSWORD, DB_NAME y DB_USER its the same
sed -i --follow-symlinks 's/MOODLE_DB_HOST.*/MOODLE_DB_HOST='"${MOODLE_DB_HOST_DESTINATION}"'/g' ${LOCALROOT}/${WORKDIR}/.env || { echo "# - ERROR update .env: Update host_db in .env ${WORKDIR} FAIL!"; exit 1; }

STEP="migrate"

## Up service
echo "# $(basename $0) - UP service after db migration..."
up_service || { echo "# - ERROR to run moodle instance with db migrated"; exit 1; }
STEP="upserviceremotedb"

## Clean origin
# Delete DB and USER in DB server if I can!....its my DB server?
# MYMOODLE_DB_SERVER=$(grep 'MOODLE_DB_HOST=' "${LOCALROOT}/.env" | cut -d '=' -f2 | tr -d '"')
# echo "# $(basename $0) - Delete DB and USER in source DB server"
# if [ "${MOODLE_DB_HOST}" = "${MYMOODLE_DB_SERVER}" ]; then
#     MYSQL_ROOT_PASSWORD=$(grep 'MYSQL_ROOT_PASSWORD=' "${LOCALROOT}/.env" | cut -d '=' -f2 | tr -d '"')
#     mysql --user=root --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="DROP DATABASE ${MOODLE_DB_NAME}; DROP USER '${MOODLE_MYSQL_USER}'@'192.168.1.%'" || \
#     echo "# - ERROR at Delete DB and User in DB server"
# else
#         echo "# - INFO: I CANT DELETE DB ${MOODLE_DB_NAME} and USER ${MOODLE_MYSQL_USER} in ${MOODLE_DB_HOST}"
# fi

echo "# $(basename $0) - INFO: Remember DELETE Backup in source server moodle: ${BACKUPDIR}"

STEP="end"
