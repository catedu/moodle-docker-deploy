#!/bin/bash
set -eu
# Initial Deploy
# Ver. 0.1 - bash
#
# Two options
# 1- one instance: createMoodle.sh -e mail -l language -n "full_name" -u "url" )
# 2- -f file: CSV - several instances

# Load env variables:
set -a
[ -f .env ] && . .env
set +a

usage () {
    echo 'usage: createMoodle.sh [-e mail_admin] [-l es|fr|..] [-n "full_name"] -t type -u "url" short_name'
    echo "help: createMoodle.sh -h"
}

showHelp () {
    echo 'usage: createMoodle.sh [-e mail_admin] [-l es|fr|..] [-n "full_name"] -t type -u "url" short_name'
    echo "Options:"
    echo "-t -> moodle school type. CEIP|CPI|IES"
    echo "-e -> administrator email or .env value by default"
    echo "-l -> default language or .env value by default"
    echo "-n -> Full Name Site. or .env value by default"
    echo "-u -> url moodle: https://site.domain.com"
    echo "-h this message"
}

get_parameter(){
    while getopts ":e:l:n:u:t:h" opt; do
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
                #check_url "${MOODLE_URL}" ||  { echo "$(basename $0): The URL doesn't match with the current ip"; usage; exit 1; }
                check_url "${MOODLE_URL}" ||  { echo "$(basename $0): The URL doesn't match with the current ip"; exit 1; }
            ;;
            t)
                SCHOOL_TYPE=""
                [[ "${OPTARG}" =~ ^[Cc][Ee][Ii][Pp] ]] && SCHOOL_TYPE="CEIP"
                [[ "${OPTARG}" =~ ^[Cc][Pp][Ii] ]] && SCHOOL_TYPE="CPI"
                [[ "${OPTARG}" =~ ^[Ii][Ee][Ss] ]] && SCHOOL_TYPE="IES"
                [[ "${OPTARG}" =~ ^[Vv][Aa][Cc][Ii][Oo] ]] && SCHOOL_TYPE="VACIO"
                [[ "${SCHOOL_TYPE}" = "" ]] && \
                { echo "Incorrect school type..."; usage; exit 1;}
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
    [ -z ${SCHOOL_TYPE+x} ] && { echo "$(basename $0): You must to indicate a school type to moodle"; usage; exit 1;}
    
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

create_name_dns(){
    echo "Creating domain to moodle in Aeducar Universe: ${1##*//}"
    [ -d "${APIOVH}" ] || { echo "No code to create domain: ${APIOVH}!!"; return 1; }
    (cd "${APIOVH}" && node createSubdomain.js "${1}") || return 1
    sleep 40
}

check_url(){
    
    ## Create NAMEIP if not exists
    getent hosts ${1##*//} >/dev/null 2>&1 || create_name_dns "${1}" || return 1
    
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
        elif ! [ -d ./template ]; then
        echo "Caution: No template deploy in this Directory. Nothing to do"
        exit 1
    else
        mkdir "${1}" && cp -rL template/* "${1}"
    fi
}

yq() { docker run --rm -i -v "${PWD}":/workdir mikefarah/yq yq "$@"; }

#Api Ovh generate Dir by default
APIOVH="api-ovh"

get_parameter "$@"

VIRTUALHOST="${MOODLE_URL##*//}"

VERSION=$(yq r template/docker-compose.yml services.moodle.image | cut -d: -f2 | cut -d- -f1)
[ "$VERSION" = "" ] && echo "Unable to get version...but I continue..."

# generate data for mysql connection
# db and user are the same for simplicity, taken url without _ or -
# for example: www.pre-school.catedu.com gets converted to pre_school_catedu_com

MOODLE_MYSQL_PASSWORD=$(cat /dev/urandom | LC_CTYPE=C tr -dc 'a-zA-Z0-9' | fold -w 8 | head -n 1)
MOODLE_DB=$(echo "${VIRTUALHOST}" | sed 's/\./_/g'| sed 's/-/_/g')
MOODLE_MYSQL_USER=${MOODLE_DB}

# create dir and copy data:
check_create_dir_exist "${VIRTUALHOST}"

# create database, user and grants
mysql --user="root" --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="CREATE DATABASE ${MOODLE_DB} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; CREATE USER '${MOODLE_MYSQL_USER}'@'192.168.1.%' IDENTIFIED BY '${MOODLE_MYSQL_PASSWORD}'; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON ${MOODLE_DB}.* to '${MOODLE_MYSQL_USER}'@'192.168.1.%'" || \
{ echo "Error at create $VIRTUALHOST DB..."; exit 1; }


if [ ! -f "${VIRTUALHOST}/.env" ]; then
    cat > "${VIRTUALHOST}/.env" << EOF


# for reverse nginx proxy:
VIRTUAL_HOST="${VIRTUALHOST}"
SSL_EMAIL="${SSL_EMAIL}"
SSL_PROXY=true
MOODLE_URL="${MOODLE_URL}"

# for database connection:
MOODLE_DB_HOST="${MOODLE_DB_HOST}"
MOODLE_DB_NAME="${MOODLE_DB}"
MOODLE_MYSQL_USER="${MOODLE_MYSQL_USER}"
MOODLE_MYSQL_PASSWORD="${MOODLE_MYSQL_PASSWORD}"


# for installing moodle, user data:
MOODLE_ADMIN_USER="${MOODLE_ADMIN_USER}"
MOODLE_ADMIN_PASSWORD="${MOODLE_ADMIN_PASSWORD}"
MOODLE_ADMIN_EMAIL="${MOODLE_ADMIN_EMAIL}"
MOODLE_LANG=${MOODLE_LANG}
MOODLE_SITE_NAME="${MOODLE_SITE_NAME}"
MOODLE_SITE_FULLNAME="${MOODLE_SITE_FULLNAME}"

## init-scripts
INSTALL_TYPE=new-install
SCHOOL_TYPE=${SCHOOL_TYPE}
VERSION="${VERSION}"

SMTP_HOSTS="${SMTP_HOSTS}"
SMTP_USER="${SMTP_USER}"
SMTP_PASSWORD="${SMTP_PASSWORD}"
SMTP_MAXBULK=${SMTP_MAXBULK}
NO_REPLY_ADDRESS="${NO_REPLY_ADDRESS}"

CRON_BROWSER_PASS="${CRON_BROWSER_PASS}"
MOODLE_MANAGER="${MOODLE_MANAGER}"
MANAGER_PASSWORD="${MANAGER_PASSWORD}"
ASESORIA_PASSWORD="${ASESORIA_PASSWORD}"
ASESORIA_EMAIL="${ASESORIA_EMAIL}"

EOF
    
fi

echo "DEPLOY ${MOODLE_URL} CREATED!"

# Update enviroment variables in execution to run docker-compose...
# Docker-compose help:
# When you set the same environment variable in multiple files, here’s the priority used:
# Compose file
# Shell environment variables
# Environment file
# Dockerfile
# Variable is not defined

set -a; [ -f "${VIRTUALHOST}/.env" ] && . "${VIRTUALHOST}/.env"; set +a

#up_services
if (cd "${VIRTUALHOST}" && docker-compose up -d); then
    echo "DEPLOY ${MOODLE_URL} UP!"
else
    echo "DEPLOY ${MOODLE_URL} FAIL!"; exit 1
fi

#make repository dir and mount it
[ ! -d ${VIRTUALHOST}/moodle-data/repository/cursosministerio ] && sudo mkdir -p ${VIRTUALHOST}/moodle-data/repository/cursosministerio && sudo chown -R www-data:www-data ${VIRTUALHOST}/moodle-data/repository
! grep ${VIRTUALHOST} /proc/mounts >/dev/null && sudo mount -o bind /var/moodle-docker-deploy/zz_cursos_cidead /var/moodle-docker-deploy/${VIRTUALHOST}/moodle-data/repository/cursosministerio 

# TO-DO
# - Send email to MOODLE_ADMIN_EMAIL????
