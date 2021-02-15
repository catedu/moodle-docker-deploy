#!/bin/bash
set -eu

usage () {
    echo 'usage: migrateMoodle.sh [-y] [-b server_db_destination] -i "identity_ssh_file" -u "dirsite" -s "server_destination"'
    echo "help: migrateMoodle.sh -h"
}

showHelp () {
    echo 'usage: migrateMoodle.sh [-y] [-b server_db_destination] -i "identity_ssh_file" -u "dirsite" -s "server_destination"'
    echo "Options:"
    echo "-y -> Yes all questions"
    echo "-b -> By default, DB its not moved to new DB Server. With -b, move DB to another db server"
    echo "-i -> Identity file (private key) to connect with remote server"
    echo "-u -> Site to upgrade. Only accept installdir"
    echo "-s -> New server to allocate moodle"
    echo "-h this message"
    echo "Backup moodle site and DB to migrate in /var/backup_migrate/ "
    echo "DB in source server NOT delete...do it !!!"
}

get_parameter(){
    while getopts ":yb:i:u:s:h" opt; do
        case $opt in
            b)
                DBSERVER="${OPTARG}"
                ! ping -c 2 "${OPTARG}" > /dev/null 2>&1 && \
                { echo "$(basename $0): Check DB ip addr (-b option) its active!"; usage; exit 1;}
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
    # Restore bind mount cursos
    $CURSOSMIN && sudo mount -o bind "/var/moodle-docker-deploy/zz_cursos_cidead /var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio"
}

change_dns(){
    if [ "$1" = "yes" ]; then
        echo "$(basename $0) - Delete old local DNS..."
        (cd "${APIOVH}" && node deleteSubdomain.js "https://${VIRTUALHOST}") || echo " - ERROR to delete DNS..."
        echo "$(basename $0) - Create new remote DNS..."
        remote_command "cd ${REMOTEROOT}/${APIOVH} && node createSubdomain.js \"https://${VIRTUALHOST}\"" || echo " - ERROR to create DNS..."
    else
        echo "$(basename $0) - Delete remote DNS..."
        remote_command "cd ${REMOTEROOT}/${APIOVH} && node deleteSubdomain.js \"https://${VIRTUALHOST}\"" || echo " - ERROR to delete DNS remote..."
        echo "$(basename $0) - Create local DNS..."
        (cd "${APIOVH}" && node createSubdomain.js "https://${VIRTUALHOST}") || echo " - ERROR to create DNS..."
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
            # No remote_up ....clean remote manual? and restore ovh ?
            echo "$(basename $0) - exit (migrate): No up in destination server..."
            
            echo "$(basename $0) - exit (migrate): Deleting moodle and db in destination"
            remote_command "[ -d ${REMOTEROOT}/${WORKDIR} ] && sudo rm -rf ${REMOTEROOT}/${WORKDIR}" || \
            { echo " - ERROR: delete WORKDIR in destination fail!...continue"; }
            
            remote_command "mysql --user=root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --execute=\"DROP DATABASE ${MOODLE_DB}; DROP USER '${MOODLE_MYSQL_USER}'@'192.168.1.%'\"" || \
            { echo " - ERROR: at Drop DB an USER in SERVERDB destination..."; exit 1; }
            
            change_dns "no"
        ;;
        backup)
            echo "$(basename $0) - exit (backup): Remove backup"
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

## STEPS: init, stopservice,backup,migrate,upremoteservice, end
STEP="init"

# Parameters
YES=false
APIOVH="api-ovh"
## Remote user with sudo in destination
REMOTEUSER="debian"
REMOTEROOT="/var/moodle-docker-deploy"
CURSOSMIN=false

get_parameter "$@"
# WORKDIR -> Site Directory
trap 'rollback' INT TERM EXIT

# Test If Destination Directory exists
remote_command "[ -d $REMOTEROOT/$WORKDIR ]" && { echo "$(basename $0) - rsync data: $WORKDIR exists at destination!"; exit 1; }

BACKUPDIR="/var/backup_migrate/$(date +%Y-%m-%d--%H-%M)__${WORKDIR}"
sudo mkdir -p "${BACKUPDIR}" && sudo chown debian:debian "${BACKUPDIR}" || { echo "$(basename $0) - init: Problems to create ${BACKUPDIR} backup"; exit 1; }

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
grep "/var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio" /proc/mounts >/dev/null \
&& CURSOSMIN=true && sudo umount /var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio
sudo rsync -a "${WORKDIR%\/}" ${BACKUPDIR} || { echo "$(basename $0) - backup: Backup Files ${WORKDIR} FAIL!"; exit 1; }
$CURSOSMIN && remote_command "[ -d ${REMOTEROOT}/zz_cursos_cidead ] && sudo mount -o bind /var/moodle-docker-deploy/zz_cursos_cidead /var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio"

STEP="backup"

### MIGRATE ###
## Migrate moodle dir
sudo rsync -azv --rsync-path="sudo rsync" -e "ssh -o StrictHostKeyChecking=no -i ${IDENTITY_FILE}" "${WORKDIR%\/}" ${REMOTEUSER}@${REMOTEROOT}/ || { echo "$(basename $0) - migrate moodle dir: rsync moodle-dir ${WORKDIR} FAIL!"; exit 1; }

if [ -n "${DBSERVER}" ]; then
    echo "$(basename $0) - Recreating DB in destination server ${DBSERVER}..."
    ## Create DB
    # Read .env variables in moodle destination
    MYSQL_ROOT_PASSWORD_DESTINATION=$(remote_command grep 'MYSQL_ROOT_PASSWORD=' ${REMOTEROOT}/.env | cut -d '=' -f2)
    MOODLE_DB_HOST_DESTINATION=$(remote_command grep 'MOODLE_DB_HOST=' ${REMOTEROOT}/.env | cut -d '=' -f2)
    
    ### OJO QUE NO TENEMOS PERMISO....SOLO DESDE LA MAQUINA MOODLE CORRESPONDIENTE!
    # create database, user and grants
    remote_command "mysql --user=root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" --execute=\"CREATE DATABASE ${MOODLE_DB} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; CREATE USER '${MOODLE_MYSQL_USER}'@'192.168.1.%' IDENTIFIED BY '${MOODLE_MYSQL_PASSWORD}'; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON ${MOODLE_DB}.* to '${MOODLE_MYSQL_USER}'@'192.168.1.%'\"" || \
    { echo " - ERROR at create $WORKDIR DB..."; exit 1; }
    
    # Restore DB in DB server destination
    remote_command "mysql --user root --password=\"${MYSQL_ROOT_PASSWORD_DESTINATION}\" --host=\"${MOODLE_DB_HOST_DESTINATION}\" < ${BACKUPDIR}/${WORKDIR}_db.sql" || { echo "$(basename $0) - restore DB: Restore DB in remote server ${MOODLE_DB_HOST_DESTINATION} of ${WORKDIR} FAIL!"; exit 1; }
    
    
    # Update env at destination
    ## DB_HOST. DB_PASSWORD, DB_NAME y DB_USER its the same
    remote_command "sed -i --follow-symlinks 's/MOODLE_DB_HOST.*/MOODLE_DB_HOST=\"${MOODLE_DB_HOST_DESTINATION}\"/g' ${REMOTEROOT}/${WORKDIR}/.env" || { echo "$(basename $0) - update .env: Update .env ${WORKDIR} in destiantionn FAIL!"; exit 1; }
fi

STEP="migrate"

### Change DNS ###
echo "$(basename $0) - Change DNS..."
change_dns yes
### END Change

## Up remote services
up_remote_service || { echo " - ERROR to run moodle instance in remote"; exit 1; }
STEP="upremoteservice"

if [ -n "${DBSERVER}" ]; then
    # Disable DB access to user in source server
    echo "$(basename $0) - Disable DB in source DB server"
    mysql --user="root" --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="REVOKE ALL ON ${MOODLE_DB}.* FROM to '${MOODLE_MYSQL_USER}'" || \
    { echo " - ERROR at revoke DB privilegies to user from source moodle server"; exit 1; }
    
    echo "$(basename $0) - INFO: Remember DELETE DB in source server"
    
fi
## Clean origin
echo "$(basename $0) - Deleting source moodle..."
grep /var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio /proc/mounts >/dev/null \
&& sudo umount "/var/moodle-docker-deploy/${WORKDIR}/moodle-data/repository/cursosministerio"
sudo rm -rf "${WORKDIR}" || { echo " - ERROR to clean source moodle directory"; exit 1; }
echo "$(basename $0) - INFO: Remember DELETE Backup in source server moodle: ${BACKUPDIR}"

STEP="end"

