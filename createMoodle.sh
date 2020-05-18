#!/bin/bash
set -eu
# Initial Deploy
# Ver. 0.1 - bash
#
# Two options
# 1- one instance: createMoodle.sh -e mail -l language -n "full_name" -u "url" [--internaldb] )
# 2- -f file: CSV - several instances

# Load env variables:

# Alternative mode
#export $(grep -E -v '^#' .env | xargs)
set -a
[ -f .env ] && . .env
set +a

usage () {
    echo 'usage: createMoodle.sh [-e mail_admin] [-l es|fr|..] [-n "full_name"] -u "url" short_name'
    echo "help: createMoodle.sh -h"
}

showHelp () {
    echo 'usage: createMoodle.sh [-e mail_admin] [-l es|fr|..] [-n "full_name"] -u "url" short_name'
    echo "Options:"
    echo "-e -> administrator email. soportecatedu@educa.aragon.es by default"
    echo "-l -> default language. es by default"
    echo "-n -> Full Name Site. AEduca de Mi Centro by default"
    echo "-u -> url moodle: https://sitie.domain.com"
    echo "-i use internal db docker. External db by default"
    echo "-h this message"
}

get_parameter(){
    while getopts ":o:e:n:u:a:h" opt; do
        case $opt in
            e)
                [[ "${OPTARG}" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$ ]] || \
                { echo "Incorrect email..."; usage; exit 1;}
                MOODLE_ADMIN_EMAIL="${OPTARG}"
            ;;
            l)
                [[ "${OPTARG}" =~ ^[a-z]{2}$ ]] || \
                { echo "Incorrect language format..."; usage; exit 1;}
                MOODLE_LANG="${OPTARG}"
            ;;
            n)
                MOODLE_SITE_FULLNAME="${OPTARG}"
            ;;
            u)
                [[ "${OPTARG}" =~ ^https?://[A-Za-z0-9._]+$ ]] || \
                { echo "Incorrect url format..."; usage; exit 1;}
                MOODLE_URL="${OPTARG}"
                check_url "${MOODLE_URL}" ||  { echo "$(basename $0): The URL doesn't match with the current ip"; usage; exit 1; }
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
    [ -z ${MOODLE_URL+x} ] && { echo "$(basename $0): You must to indicate a url to moodle"; usage; exit 1;}
    
    # Arguments
    shift "$((OPTIND-1))"
    for var in "$@"; do
        if [[ "${var:0:1}" = "-" ]]; then
            usage
            exit 0
        fi
    done
    set +u
    [ -z "${1}" ] && { echo "You must to indicate a short_name"; usage; exit 1;}
    set -u
    MOODLE_SITE_NAME="${1}"
}

check_url(){
    PUBLIC_IP=$(curl https://ipinfo.io/ip 2>/dev/null)
    # Mac users alternative :-)
    # VIRTUALHOST="${MOODLE_URL##*//}"
    # NAME_IP=$(ping -c 1 "${VIRTUALHOST}" | awk -F '[()]' '/PING/{print $2}')
    NAME_IP=$(getent hosts ${1##*//} | awk '{ print $1 }' 2>/dev/null)
    [ "${PUBLIC_IP}" = "${NAME_IP}" ]
}

check_create_dir_exist(){
    if [ -d "${1}" ]; then
        echo "Caution: Deploy Duplicate!!. Directory $1 exists"
        # Comment to continue (override docker-compose, for upgrade)
        exit 1
    else
        mkdir "${1}" && cp -r template/* "${1}"
    fi
}

get_parameter "$@"

VIRTUALHOST="${MOODLE_URL##*//}"

# generate data for mysql connection
# db and user are the same for simplicity, taken url without _ or -
# for example: www.pre-school.catedu.com gets converted to pre_school_catedu_com

MOODLE_MYSQL_PASSWORD=$(cat /dev/urandom | LC_CTYPE=C tr -dc 'a-zA-Z0-9' | fold -w 8 | head -n 1)
MOODLE_DB=$(echo "${VIRTUALHOST}" | sed 's/\./_/g'| sed 's/-/_/g')
MOODLE_MYSQL_USER=${MOODLE_DB}

# create dir and copy data:
check_create_dir_exist "${VIRTUALHOST}"

# create database, user and grants
mysql --user="root" --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="CREATE DATABASE ${MOODLE_DB} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER ${MOODLE_MYSQL_USER} IDENTIFIED BY '${MOODLE_MYSQL_PASSWORD}'; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON ${MOODLE_DB}.* to '${MOODLE_MYSQL_USER}'@'%'"


if [ ! -f "${VIRTUALHOST}/.env" ]; then
    cat > "${VIRTUALHOST}/.env" << EOF


# for reverse nginx proxy:
VIRTUAL_HOST=${VIRTUALHOST}
SSL_EMAIL=${SSL_EMAIL}
SSL_PROXY=true
MOODLE_URL=${MOODLE_URL}

# for database connection:
MOODLE_DB_HOST=${MOODLE_DB_HOST}
MOODLE_DB_NAME=${MOODLE_DB}
MOODLE_MYSQL_USER=${MOODLE_MYSQL_USER}
MOODLE_MYSQL_PASSWORD=${MOODLE_MYSQL_PASSWORD}


# for installing moodle, user data:
MOODLE_ADMIN_USER=${MOODLE_ADMIN_USER}
MOODLE_ADMIN_PASSWORD=${MOODLE_ADMIN_PASSWORD}
MOODLE_ADMIN_EMAIL=${MOODLE_ADMIN_EMAIL}
MOODLE_LANG=${MOODLE_LANG}
MOODLE_SITE_NAME=${MOODLE_SITE_NAME}
MOODLE_SITE_FULLNAME=${MOODLE_SITE_FULLNAME}

EOF
    
fi

echo "DEPLOY ${MOODLE_URL} CREATED!"

#up_services
(cd "${VIRTUALHOST}" && docker-compose up -d) \
&& echo "DEPLOY ${MOODLE_URL} UP!" || echo "DEPLOY ${MOODLE_URL} FAIL!"

# TO-DO
# - Mandar un correo al MOODLE_ADMIN_EMAIL????
# - También deberíamos tener claro si hacemos importación de datos y como
