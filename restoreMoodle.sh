#!/bin/bash
set -eu

# Initial Deploy
# Ver. 0.1 - bash

# Load env variables:
set -a
[ -f .env ] && . .env
set +a

usage () {
    echo 'usage: restoreMoodle.sh -u "url" short_name'
    echo "help: restoreMoodle.sh -h"
}

showHelp () {
    echo 'usage: restoreMoodle.sh -u "url" short_name'
    echo "Options:"
    echo "-u -> url moodle: https://site.domain.com"
    echo "-h this message"
}

get_parameter(){
    while getopts ":u:h" opt; do
        case $opt in
            u)
                [[ "${OPTARG}" =~ ^https?://[A-Za-z0-9._]+$ ]] || \
                { echo "Incorrect url format..."; usage; exit 1;}
                MOODLE_URL="${OPTARG}"
                echo  "URL::: $MOODLE_URL"
                        check_url "${MOODLE_URL}" ||  { echo "$(basename $0): The URL doesn't match with the current ip"; exit 1; }
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
    echo "hola $MOODLE_SITE_NAME"    
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


###########################################################################################

#Api Ovh generate Dir by default
APIOVH="api-ovh"

get_parameter "$@"

VIRTUALHOST="${MOODLE_URL##*//}"


# Update enviroment variables in execution to run docker-compose...
# Docker-compose help:
# When you set the same environment variable in multiple files, here’s the priority used:
# Compose file
# Shell environment variables
# Environment file
# Dockerfile
# Variable is not defined

set -a; [ -f "${VIRTUALHOST}/.env" ] && . "${VIRTUALHOST}/.env"; set +a



