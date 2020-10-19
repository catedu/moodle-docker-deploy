#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (if new parameters needed, expected to be empty)



# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)

# Config site
moosh config-set forcetimezone Europe/Madrid
moosh config-set calendar_site_timeformat %H:%M
moosh config-set calendar_startwday 1
moosh config-set debugdisplay 0
moosh config-set frontpage 6
moosh config-set frontpageloggedin 5,0




# Config smtp
echo >&2 "Configuring smtp..."
set -x
moosh config-set smtphosts ${SMTP_HOSTS}
moosh config-set smtpsecure tls
moosh config-set smtpauthtype LOGIN
moosh config-set smtpuser ${SMTP_USER}
moosh config-set smtppass ${SMTP_PASSWORD}
moosh config-set smtpmaxbulk ${SMTP_MAXBULK}
moosh config-set noreplyaddress ${NO_REPLY_ADDRESS}

# Authentication
moosh config-set authloginviaemail 0
moosh config-set allowguestmymoodle 0
moosh config-set allowaccountssameemail 1
moosh config-set guestloginbutton 0

# Licenses
moosh config-set sitedefaultlicense cc-nc-sa


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
moosh config-set doclang es
moosh config-set lang es
moosh config-set country ES
moosh config-set timezone Europe/Madrid


# Config navigation
echo >&2 "Configuring navitation..."
moosh config-set defaulthomepage 0
moosh config-set searchincludeallcourses 1
moosh config-set navshowfullcoursenames 1
moosh config-set navshowcategories 0
moosh config-set navshowallcourses 1
moosh config-set navsortmycoursessort idnumber
moosh config-set navcourselimit 20
moosh config-set linkadmincategories 0
moosh config-set linkcoursesections 0
moosh config-set navshowfrontpagemods 0
moosh config-set frontpageloggedin 5,0



# Enable cron through web browser
echo >&2 "Configuring cron through web browser..."
moosh config-set cronremotepassword ${CRON_BROWSER_PASS}
moosh config-set cronclionly 0

# Badges config
echo >&2 "Configuring badges..."
moosh config-set badges_defaultissuercontact ${MOODLE_MANAGER}
moosh config-set badges_defaultissuername gestorae

# Users config
echo >&2 "Configuring users..."
moosh config-set enablegravatar 1
moosh config-set enableportfolios 1
moosh config-set defaultpreference_maildisplay 0
moosh config-set defaultpreference_maildigest 2
moosh config-set defaultpreference_trackforums 1
moosh config-set hiddenuserfields email
moosh config-set showuseridentity username
moosh config-set block_online_users_timetosee 10
# moosh config-set guestloginbutton 1

# statistics
moosh config-set enablestats 1

# feeds
moosh config-set enablerssfeeds 1

# courses
moosh config-set enableglobalsearch 1
moosh config-set enablecourserequests 1
moosh config-set courserequestnotify \$\@ALL@$
moosh config-set searchincludeallcourses 0

# Completion
moosh config-set completiondefault 0

# grades
moosh config-set gradeexport ods,txt,xml
moosh config-set gradepointmax 10
moosh config-set grade_aggregation 10
moosh config-set grade_aggregations_visible 0,10,13
moosh config-set grade_report_showquickfeedback 1
moosh config-set grade_report_user_rangedecimals 2
moosh config-set gradepointdefault 10

# themes
moosh config-set allowthemechangeonurl 1

# Disabling messaging
moosh config-set messaging 0

# Site Policyhandler
moosh config-set sitepolicyhandler tool_policy
moosh config-set contactdataprotectionofficer 1 tool_dataprivacy
moosh config-set showdataretentionsummary 0 tool_dataprivacy

# Creating moodle-manager
echo >&2 "Creating moodle-manager gestorae and giving grants..."
moosh user-create --password ${MANAGER_PASSWORD} --email ${MOODLE_MANAGER} --digest 2 --city Aragón --country ES --firstname Gestorae --lastname Aeducar gestorae
moosh role-create -a manager gestora
moosh role-update-capability gestora enrol/flatfile:manage allow 1
moosh role-update-capability gestora enrol/flatfile:unenrol allow 1
moosh role-update-capability gestora repository/upload:view allow 1
moosh role-update-capability gestora mod/forum:allowforcesubscribe allow 1
moosh role-update-capability gestora atto/recordrtc:recordvideo allow 1
moosh role-update-capability gestora atto/recordrtc:recordaudio allow 1
moosh role-update-capability gestora tool/dataprivacy:managedataregistry allow 1
moosh role-update-capability gestora tool/dataprivacy:managedatarequests allow 1
moosh role-update-capability gestora tool/dataprivacy:managedataregistry allow 1
moosh role-update-capability gestora moodle/webservice:createmobiletoken allow 1
moosh role-update-capability gestora block/tags:myaddinstance allow 1
moosh role-update-capability gestora block/starredcourses:myaddinstance allow 1
moosh role-update-capability gestora block/mentees:myaddinstance allow 1
moosh config-set dporoles 9 tool_dataprivacy
moosh user-assign-system-role gestorae gestora

# Creating moodle-asesoria-admin
echo >&2 "Creating moodle-manager gestorae and giving grants..."
moosh user-create --password ${ASESORIA_PASSWORD} --email ${ASESORIA_EMAIL} --digest 2 --city Aragón --country ES --firstname Asesoría --lastname Aeducar asesoria
moosh config-set siteadmins 2,4

# Creating parent role
echo >&2 "Creating parent role and configuring it..."
moosh role-create -d "Los familiares solo pueden acceder ciertos datos del progreso de sus hijos" -n "Familiar" familiar
moosh role-update-contextlevel --course-off familiar
moosh role-update-contextlevel --system-off familiar
moosh role-update-contextlevel --category-off familiar
moosh role-update-contextlevel --activity-off familiar
moosh role-update-contextlevel --block-off familiar
moosh role-update-capability familiar moodle/user:viewalldetails allow 1
moosh role-update-capability familiar moodle/user:viewdetails allow 1
moosh role-update-capability familiar moodle/user:readuserblogs allow 1
moosh role-update-capability familiar moodle/user:readuserposts allow 1
moosh role-update-capability familiar moodle/user:viewuseractivitiesreport allow 1
moosh role-update-capability familiar moodle/user:editprofile allow 1
moosh role-update-capability familiar tool/policy:acceptbehalf allow 1

# Prohibit to write to each other
echo >&2 "Prohibit to write to each other"
moosh role-update-capability student moodle/user:viewdetails prohibit 1
moosh role-update-capability student local/mail:mailsamerole prohibit 1

echo >&2 "Running dangerous sql commads... " $'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243' 
echo >&2 "The first one will work... "
moosh sql-run "INSERT INTO mdl_role_allow_assign(roleid,allowassign) VALUES(9,10)"

# import categories and courses
echo >&2 "Importing categories and courses..."
/init-scripts/${INSTALL_TYPE}/import_${SCHOOL_TYPE}_categories_and_courses.sh


#Updates made at the beginning of the course after the first creation of instances

moosh sql-run "INSERT INTO mdl_scale (name, scale, description) VALUES('Aptitud','Apta, No apta','Escala Aeducar1')"

echo >&2 "Creando usuarios estudiantes del 1 al 10"
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante1" --lastname "Uno" estudiante1
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante2" --lastname "Dos" estudiante2
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante3" --lastname "Tres" estudiante3
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante4" --lastname "Cuatro" estudiante4
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante5" --lastname "Cinco" estudiante5
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante6" --lastname "Seis" estudiante6
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante7" --lastname "Siete" estudiante7
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante8" --lastname "Ocho" estudiante8
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante9" --lastname "Nueve" estudiante9
moosh user-create --password estudiante --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Estudiante10" --lastname "Diez" estudiante10

echo >&2 "Permitir a todos los profesores instalar módulos h5p"
moosh role-update-capability editingteacher mod/hvp:updatelibraries allow 1
moosh role-update-capability editingteacher mod/hvp:installrecommendedh5plibraries allow 1
moosh role-update-capability editingteacher mod/hvp:userestrictedlibraries allow 1

#Updates made on 09-17-20 and brought for new creations: expand the default file upload size to 50MB (the server expands to 192) and add Family Access block
echo >&2 "set value of max_file_size by default in courses"
moosh config-set maxbytes 52428800


echo >&2 "Adding Mentees block (Acceso Familias)"
moosh block-add category 1 mentees site-index side-pre 0
moosh sql-run "update mdl_block_instances SET parentcontextid=1, configdata='Tzo4OiJzdGRDbGFzcyI6MTp7czo1OiJ0aXRsZSI7czoxNToiQWNjZXNvIEZhbWlsaWFzIjt9' WHERE blockname='mentees'"

#Update default notification configuration for users. Popup instead of email.
echo >&2 "Updating default notification preferences"
moosh config-set  message_provider_local_mail_mail_loggedin    popup   message
moosh config-set  message_provider_local_mail_mail_loggedoff    popup   message
moosh config-set  message_provider_mod_assign_assign_notification_loggedin    popup   message
moosh config-set  message_provider_mod_assign_assign_notification_loggedoff    popup   message
moosh config-set  message_provider_mod_feedback_message_loggedin    popup   message
moosh config-set  message_provider_mod_feedback_message_loggedoff    popup   message
moosh config-set  message_provider_mod_feedback_submission_loggedin    popup   message
moosh config-set  message_provider_mod_feedback_submission_loggedoff    popup   message
moosh config-set  message_provider_mod_forum_digests_loggedin    popup   message
moosh config-set  message_provider_mod_forum_digests_loggedoff    popup   message
moosh config-set  message_provider_mod_forum_posts_loggedin    popup,airnotifier  message
moosh config-set  message_provider_mod_forum_posts_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_mod_hvp_confirmation_loggedin    popup,airnotifier  message
moosh config-set  message_provider_mod_hvp_confirmation_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_mod_hvp_submission_loggedin    popup   message
moosh config-set  message_provider_mod_hvp_submission_loggedoff    popup   message
moosh config-set  message_provider_mod_lesson_graded_essay_loggedin    popup,airnotifier  message
moosh config-set  message_provider_mod_lesson_graded_essay_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_mod_quiz_attempt_overdue_loggedin    popup,airnotifier  message
moosh config-set  message_provider_mod_quiz_attempt_overdue_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_mod_quiz_confirmation_loggedin    popup,airnotifier  message
moosh config-set  message_provider_mod_quiz_confirmation_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_mod_quiz_submission_loggedin    popup   message
moosh config-set  message_provider_mod_quiz_submission_loggedoff    popup   message
moosh config-set  message_provider_moodle_badgecreatornotice_loggedin    popup   message
moosh config-set  message_provider_moodle_badgecreatornotice_loggedoff    popup   message
moosh config-set  message_provider_moodle_badgerecipientnotice_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_moodle_competencyplancomment_loggedin    popup   message
moosh config-set  message_provider_moodle_competencyplancomment_loggedoff    popup   message
moosh config-set  message_provider_moodle_competencyusercompcomment_loggedin    popup   message
moosh config-set  message_provider_moodle_competencyusercompcomment_loggedoff    popup   message
moosh config-set  message_provider_moodle_insights_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_moodle_instantmessage_loggedoff    popup   message
moosh config-set  message_provider_moodle_messagecontactrequests_loggedoff    popup,airnotifier  message
moosh config-set  message_provider_moodle_notices_loggedin    popup   message
moosh config-set  message_provider_moodle_notices_loggedoff    popup   message
moosh config-set  message_provider_tool_messageinbound_invalidrecipienthandler_loggedin    popup   message
moosh config-set  message_provider_tool_messageinbound_invalidrecipienthandler_loggedoff    popup   message
moosh config-set  message_provider_tool_messageinbound_messageprocessingerror_loggedin    popup   message
moosh config-set  message_provider_tool_messageinbound_messageprocessingerror_loggedoff    popup   message
moosh config-set  message_provider_tool_messageinbound_messageprocessingsuccess_loggedin    popup   message
moosh config-set  message_provider_tool_messageinbound_messageprocessingsuccess_loggedoff    popup   message
moosh config-set  message_provider_tool_monitor_notification_loggedin    popup   message
moosh config-set  message_provider_tool_monitor_notification_loggedoff    popup   message

