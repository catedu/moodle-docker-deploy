#!/bin/bash
set -eu

usage () {
    echo 'usage: migrateMoodle.sh [-p] [-y] -i "identity_ssh_file" -u "url|dirsite" -s "server_destination"'
    echo "help: migrateMoodle.sh -h"
}

showHelp () {
    echo 'usage: migrateMoodle.sh [-b] [-y] -i "identity_ssh_file" -u "url|dirsite" -s "server_destination"'
    echo "Options:"
    echo "-b -> By defautl, DB its moved to new DB Server. With -b preserve DB in actual Server"
    echo "-y -> Yes all questions"
    echo "-i -> Identity file (private key) to connect with remote server"
    echo "-u -> Site to upgrade. Accept url or installdir"
    echo "-s -> New server to allocate moodle"
    echo "-h this message"
    echo "Backup moodle site and DB to migrate in /var/backup_migrate/ "
    echo "DB in source server NOT delete...do it !!!"
}

get_parameter(){
    while getopts ":pyi:u:s:h" opt; do
        case $opt in
            p)
                DBSTATIC=true
            ;;
            y)
                YES=true
            ;;
            i)
                IDENTITY_FILE="${OPTARG}"
                [ ! -r "${IDENTITY_FILE}" ] && \
                { echo "$(basename $0): Identity File its not readeable!"; usage; exit 1;}
            ;;
            u)
                WORKDIR="${OPTARG##*//}"
                [ ! -d "${WORKDIR}" ] && \
                { echo "$(basename $0): Dont exist Site to upgrade!"; usage; exit 1;}
                WORKDIR=${WORKDIR%\/}
            ;;
            s)
                ## Check new IP Server
                NEWSERVER="${OPTARG}"
                ! ping -c 2 "${OPTARG}" > /dev/null 2>&1 && \
                { echo "$(basename $0): Check ip addr (-s option) its active!"; usage; exit 1;}
                
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
    [ -z ${NEWSERVER+x} ] && { echo "$(basename $0): You must to indicate a server destination"; usage; exit 1;}
    [ -z ${IDENTITY_FILE+x} ] && { echo "$(basename $0): You must to indicate a identity file to connect to another server"; usage; exit 1;}
    [ -z ${WORKDIR+x} ] && { echo "$(basename $0): You must to indicate a moodle site to move"; usage; exit 1;}
    
    # Test sucsessfull connection
    ! remote_command "exit" > /dev/null 2>&1 && \
    { echo "$(basename $0): Cannot connect with destination server ${NEWSERVER}. Identity problem?"; usage; exit 1;}
    
    return 0
    
}
remote_command(){
    [ -z "$1" ] && return 1
    ssh -o StrictHostKeyChecking=no -i "${IDENTITY_FILE}" "${REMOTEUSER}@${NEWSERVER}" "$1"
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

up_remote_service(){
    remote_command "cd '${WORKDIR}' && docker-compose up -d"
}

yq() { docker run --rm -i -v "${PWD}":/workdir mikefarah/yq yq "$@"; }

rollback(){
    case $STEP in
        end)
            ##Nothting to do
            echo "$(basename $0) - exit: All ok...enjoy ${WORKDIR} moodle in ${NEWSERVER}!!"
            return 0
        ;;
        upremoteservice)
            ##Nothting to do
            echo "$(basename $0) - exit: All ok...enjoy ${WORKDIR} moodle in ${NEWSERVER}, but problems to clean LOCAL instance!!"
            return 0
        ;;
        migrate)
            # No remote_up ....clean remote and restore ovh ?
            echo "$(basename $0) - exit: Migrate"
            
            (cd "${WORKDIR}" && docker-compose down || true)
            
            echo "$(basename $0) - exit: Restore DB"
            mysqldump --user root --password=${MYSQL_ROOT_PASSWORD} --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" < ${BACKUPDIR}/${WORKDIR}_db.sql > /dev/null || { echo "$(basename $0) - exit: Restore DB ${WORKDIR} FAIL!"; return 1; }
            
            echo "$(basename $0) - exit: Restore Files"
            sudo rsync -a "${BACKUPDIR}/${WORKDIR}/" "${WORKDIR}/" || \
            { echo "$(basename $0) - exit: RESTORE FILES ${MOODLE_URL} FAIL!"; return 1; }
        ;;
        backup)
            echo "$(basename $0) - exit: Remove backup"
            [ -d "${BACKUPDIR}" ] && rm -f "${BACKUPDIR}" || \
            echo "$(basename $0) - exit: CLEAN BACKUP FAIL!"
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
    echo "$(basename $0) - exit: Up local service"
    up_service
}

create_name_dns(){
    echo "Creating domain to moodle in Aeducar Universe: ${1##*//}"
    [ -d "${APIOVH}" ] || { echo "No code to create domain: ${APIOVH}!!"; return 1; }
    (cd "${APIOVH}" && node createSubdomain.js "${1}") || return 1
    sleep 10
}

check_url(){
    
    ## Create NAMEIP if not exists
    getent hosts ${1##*//} >/dev/null 2>&1 || create_name_dns "${1}" || return 1
}

trap 'rollback' INT TERM EXIT
## STEPS: init, stopservice,backup,template, end
STEP="init"

# Parameters
DBSTATIC=false
YES=false
APIOVH="api-ovh"
## Remote user with sudo in destination
REMOTEUSER="debian"
REMOTEROOT="/var/moodle-docker-deploy"

get_parameter "$@"
# WORKDIR -> Site Directory

# Test If Destination Directory exists
remote_command "[ -d $REMOTEROOT/$WORKDIR ]" && { echo "$(basename $0) - rsync data: $WORKDIR exists at destination!"; exit 1; }

BACKUPDIR="/var/backup_migrate/$(date +%Y-%m-%d--%H-%M)__${WORKDIR}"
sudo mkdir -p "${BACKUPDIR}" && chown debian:debian "${BACKUPDIR}" || { echo "$(basename $0) - init: Problems to create ${BACKUPDIR} backup"; exit 1; }

# Load general .env for run backup
set -a; [ -f .env ] && . .env; set +a

# Load WORKDIR .env (override general values)
set -a; [ -f "${WORKDIR}/.env" ] && . "${WORKDIR}/.env"; set +a

install_pkg mariadb-client rsync

## Stopservice
if (cd "${WORKDIR}" && docker-compose stop web moodle && echo "Y" | docker-compose rm web moodle); then
    echo "$(basename $0) - stop services: Deploy ${WORKDIR} DOWN!"
    elif (cd "${WORKDIR}" && [ -z "$(docker-compose ps -q)" ] ); then
    # Service stopped before
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} DOWN BEFORE!"
    ( $YES || (read -r -p "Do you want to continue MIGRATE?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
else
    echo "$(basename $0) - stop services: DEPLOY ${WORKDIR} FAIL at DOWN!"
    ( $YES || (read -r -p "Do you want to continue MIGRATE?...[s/N] " RESP && [[ "$RESP" =~ ^([sS]|[sS][iI]|[yY][eE][sS]|[yY])$ ]] )) || exit 1
fi
STEP="stopservice"

## Backup
echo "$(basename $0) - Backup DB..."
mysqldump --user root --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --databases "${MOODLE_DB_NAME}" > ${BACKUPDIR}/${WORKDIR}_db.sql || { echo "$(basename $0) - backup: Backup DB ${WORKDIR} FAIL!"; exit 1; }

echo "$(basename $0) - Backup Files..."
sudo rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "$(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
STEP="backup"

### MIGRATE ###
## Migrate moodle dir
sudo rsync -azv --rsync-path="sudo rsync" -e "ssh -o StrictHostKeyChecking=no -i ${IDENTITY_FILE}" "${WORKDIR%\/}" ${REMOTEUSER}@${REMOTEROOT}/ || { echo "$(basename $0) - migrate moodle dir: rsync moodle-dir ${WORKDIR} FAIL!"; exit 1; }

if ! ${DBSTATIC}; then
    ## Create DB
    # Read .env variables in moodle destination
    MYSQL_ROOT_PASSWORD_DESTINATION=$(remote_command grep 'MYSQL_ROOT_PASSWORD=' ${REMOTEROOT}/.env | cut -d '=' -f2)
    MOODLE_DB_HOST_DESTINATION=$(remote_command grep 'MOODLE_DB_HOST=' ${REMOTEROOT}/.env | cut -d '=' -f2)
    mysql --user root --password="${MYSQL_ROOT_PASSWORD_DESTINATION}" --host="${MOODLE_DB_HOST_DESTINATION}" < ${BACKUPDIR}/${WORKDIR}_db.sql || { echo "$(basename $0) - restore DB: Restore DB in remote server ${MOODLE_DB_HOST_DESTINATION} of ${WORKDIR} FAIL!"; exit 1; }
    # Update env at destination
    remote_command "sed -i -n --follow-symlinks 's/MOODLE_DB_HOST.*/MOODLE_DB_HOST=\"jamon\"/g' ${REMOTEROOT}/${WORKDIR}/.env" | { echo "$(basename $0) - update .env: Update .env ${WORKDIR} in destiantionn FAIL!"; exit 1; }
fi

### Change DNS ###

##########################################

### END Change
STEP="migrate"

## Up remote services
up_remote_service || { echo "Error to run moodle instance in remote"; exit 1; }
STEP="upremoteservice"

if ! ${DBSTATIC}; then
    # Disable DB access to user in source server
    ##
    mysql --user="root" --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="REVOKE ALL ON ${MOODLE_DB}.* FROM to '${MOODLE_MYSQL_USER}; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON ${MOODLE_DB}.* to '${MOODLE_MYSQL_USER}'@'localhost'" || \
    { echo "Error at revoke DB privilegies to user from source moodle server"; exit 1; }
fi
## Clean origin???
# Delete moodle-dir, DB and user on SOURCE?
sudo rm -rf "${WORKDIR}" || { echo "Error to clean source moodle directory"; exit 1; }

STEP="end"

