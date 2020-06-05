#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (if new parameters needed, expected to be empty)



# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)

# Config smtp
echo >&2 "Configuring smtp..."
set -x
moosh config-set smtphosts ${SMTP_HOSTS}
moosh config-set smtpsecure 
moosh config-set smtpauthtype LOGIN
moosh config-set smtpuser ${SMTP_USER}
moosh config-set smtppass ${SMTP_PASSWORD}
moosh config-set smtpmaxbulk ${SMTP_MAXBULK}
moosh config-set noreplyaddress ${NO_REPLY_ADDRESS}


# Config webservices
echo >&2 "Configuring webservices..."
moosh config-set enablewebservices 1
moosh config-set enablemobilewebservice 1

# Config blog
echo >&2 "Configuring blog..."
moosh config-set enableblogs 1

# Config mobile notifications
echo >&2 "Configuring mobile notifications..."
moosh config-set airnotifierurl https://messages.moodle.net
moosh config-set airnotifierport 443
moosh config-set airnotifiermobileappname com.moodle.moodlemobile
moosh config-set airnotifierappname commoodlemoodlemobile

# If we want to set the time when students receive mails
# moosh config-set digestmailtime 18

# Set languages
echo >&2 "Configuring languages..."
# moosh config-set doclang es
# moosh config-set lang es
moosh config-set country ES
moosh config-set timezone Europe/Madrid


# Config navigation
echo >&2 "Configuring navitation..."
moosh config-set defaulthomepage 0
moosh config-set searchincludeallcourses 1


# Enable cron through web browser
echo >&2 "Configuring cron through web browser..."
moosh config-set cronremotepassword ${CRON_BROWSER_PASS}

# Badges config
echo >&2 "Configuring badges..."
moosh config-set badges_defaultissuercontact ${MOODLE_MANAGER}

# Users config
echo >&2 "Configuring users..."
moosh config-set enablegravatar 1
# moosh config-set guestloginbutton 1


# import categories and courses
echo >&2 "Importing categories and courses..."
/init-scripts/${INSTALL_TYPE}/import_${SCHOOL_TYPE}_categories_and_courses.sh

# Change language configuration
