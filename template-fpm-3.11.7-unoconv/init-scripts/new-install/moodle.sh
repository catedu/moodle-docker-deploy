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

# # Config mobile notifications
# echo >&2 "Configuring mobile notifications..."
# moosh config-set airnotifierurl https://messages.moodle.net
# moosh config-set airnotifierport 443
# moosh config-set airnotifiermobileappname com.moodle.moodlemobile
# moosh config-set airnotifierappname commoodlemoodlemobile

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
moosh config-set defaulthomepage 1
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
moosh config-set badges_defaultissuercontact ${MOODLE_ADMIN_EMAIL}
moosh config-set badges_defaultissuername "Plataforma Aeducar"

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

# # Disabling messaging
# moosh config-set messaging 0

# Site Policyhandler
moosh config-set sitepolicyhandler tool_policy
moosh config-set contactdataprotectionofficer 1 tool_dataprivacy
moosh config-set showdataretentionsummary 0 tool_dataprivacy

# Creating moodle-manager
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "For FP distancia we don't create gestorae user"
        echo "For FP distancia we don't create gestora role"
    else
        echo >&2 "Creating moodle-manager gestorae and giving grants..."
        GESTORAE_USER_ID=`moosh user-create --password ${MANAGER_PASSWORD} --email ${MOODLE_MANAGER} --digest 2 --city Aragón --country ES --firstname Gestorae --lastname Aeducar gestorae`
        GESTORAE_ROLE_ID=`moosh role-create -a manager gestora`
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
        moosh role-update-capability gestora moodle/role:manage prohibit 1 
        moosh role-update-capability gestora moodle/role:manage prohibit 1 
        moosh role-update-capability gestora moodle/role:manage prohibit 1 
        moosh role-update-capability gestora moodle/role:manage prohibit 1 
        moosh role-update-capability gestora moodle/role:manage prohibit 1 
        moosh role-update-capability gestora moodle/course:renameroles prohibit 1

        moosh config-set dporoles 9 tool_dataprivacy
        moosh user-assign-system-role gestorae gestora
fi

# Creating moodle-asesoria-admin
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "For FP distancia we don't create asesoria user"
    else
        echo >&2 "Creating moodle-manager gestorae and giving grants..."
        ASESORIA_USER_ID=`moosh user-create --password ${ASESORIA_PASSWORD} --email ${ASESORIA_EMAIL} --digest 2 --city Aragón --country ES --firstname Asesoría --lastname Aeducar asesoria`
        # 2 es admin 
        moosh config-set siteadmins 2,${ASESORIA_USER_ID}
fi

# Creating parent role
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "For FP distancia we don't create parent role"
    else
        echo "Creating parent role and configuring it..."
        FAMILIAR_ROLE_ID=`moosh role-create -d "Los familiares solo pueden acceder ciertos datos del progreso de sus hijos" -n "Familiar" familiar`
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

        echo >&2 "Running dangerous sql commads... " $'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243'$'\360\237\222\243' 
        echo >&2 "The first one will work... "
        # 9 gestora 10 familiar 
        # TODO: cambiar 9,10 por la variable correspondiente
        moosh sql-run "INSERT INTO mdl_role_allow_assign(roleid,allowassign) VALUES(9,10)"
fi

# Create FPD needed users
if [[ "${SCHOOL_TYPE}" = "FPD" ]]; 
    then
        echo "Creating admin user for FP..."
        FPD_ADMIN_USER_ID=$(moosh user-create --password "${FPD_PASSWORD}" --email "${FPD_EMAIL}" --digest 2 --city Aragón --country ES --firstname fp --lastname distancia admin2)
        moosh config-set siteadmins 2,"${ASESORIA_USER_ID}","${FPD_ADMIN_USER_ID}"
        # users for mobile app area created in import_FPD_categories_and_courses.sh in order to enrol them into the demo course for market stores
fi

# Crear rol y usuario de inspección
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "Creating inspeccion role and configuring it..."
        INSPECCION_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -a manager -c system,category,course,block -n "Inspeccion" inspeccion)
        
        # set permissions to inspeccion role
        moosh role-import -f role-inspeccion.xml

        # Creating user
        INSPECCION_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email inspeccion@educa.aragon.es --digest 2 --city Aragón --country ES --firstname Inspección --lastname Inspección profinspector)

        # Assiging user to r
        moosh user-assign-system-role profinspector inspeccion
fi

# Crear rol de jefaturas y usuarios
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "Creating jefatura-estudios role and configuring it..."
        JEFATURA_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -a manager -c system,category,course,block -n "Jefatura de estudios" jefatura-estudios)

        # Setting permissions to jefatura de estudios role
        moosh role-import -f role-jefatura-estudios.xml

        # Creating users
        JE_SG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@education.catedu.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES Sierra de Guara" prof_je_sg)
        JE_SE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES SANTA EMERENCIANA" prof_je_se)
        JE_TM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES TIEMPOS MODERNOS" prof_je_tm)
        JE_LE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP LOS ENLACES" prof_je_le)
        JE_CA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP CORONA DE ARAGÓN" prof_je_ca)
        JE_PI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP PIRÁMIDE" prof_je_pi)
        JE_SB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP SAN BLAS" prof_je_sb)
        JE_MI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MIRALBUENO" prof_je_mi)
        JE_PS_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES PABLO SERRANO" prof_je_ps)
        JE_BA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP BAJO ARAGÓN" prof_je_ba)
        JE_RG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES RÍO GÁLLEGO" prof_je_rg)
        JE_VT_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES VEGA DEL TURIA" prof_je_vt)
        JE_LB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES LUIS BUÑUEL" prof_je_lb)
        JE_MO_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP MONTEARAGON" prof_je_mo)
        JE_MV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARTÍNEZ VARGAS" prof_je_mv)
        JE_AV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES AVEMPACE" prof_je_av)
        JE_MM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARÍA MOLINER" prof_je_mm)

        # Settings users to role
        moosh user-assign-system-role prof_je_sg jefatura-estudios
        moosh user-assign-system-role prof_je_se jefatura-estudios
        moosh user-assign-system-role prof_je_tm jefatura-estudios
        moosh user-assign-system-role prof_je_le jefatura-estudios
        moosh user-assign-system-role prof_je_ca jefatura-estudios
        moosh user-assign-system-role prof_je_pi jefatura-estudios
        moosh user-assign-system-role prof_je_sb jefatura-estudios
        moosh user-assign-system-role prof_je_mi jefatura-estudios
        moosh user-assign-system-role prof_je_ps jefatura-estudios
        moosh user-assign-system-role prof_je_ba jefatura-estudios
        moosh user-assign-system-role prof_je_rg jefatura-estudios
        moosh user-assign-system-role prof_je_vt jefatura-estudios
        moosh user-assign-system-role prof_je_lb jefatura-estudios
        moosh user-assign-system-role prof_je_mo jefatura-estudios
        moosh user-assign-system-role prof_je_mv jefatura-estudios
        moosh user-assign-system-role prof_je_av jefatura-estudios
        moosh user-assign-system-role prof_je_mm jefatura-estudios
fi

#Updates made at the beginning of the course after the first creation of instances

moosh sql-run "INSERT INTO mdl_scale (name, scale, description) VALUES('Aptitud','No apta, Apta','Escala Aeducar1')"

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

#Updates made on 09-17-20 and brought for new creations: expand the default file upload size to 50MB (the server expands to 192) and add Family Access block
echo >&2 "set value of max_file_size by default in courses"
moosh config-set maxbytes 201326592

if [[ "${SCHOOL_TYPE}" = "FPD" ]]; 
    then
        echo "FP a distancia doesn't requiere Mentees block"
    else
        echo >&2 "Adding Mentees block (Acceso Familias)"
        moosh block-add category 1 mentees site-index side-pre 0
        moosh sql-run "update mdl_block_instances SET parentcontextid=1, configdata='Tzo4OiJzdGRDbGFzcyI6MTp7czo1OiJ0aXRsZSI7czoxNToiQWNjZXNvIEZhbWlsaWFzIjt9' WHERE blockname='mentees'"
fi

echo >&2 "Blocking firstname and lastname edition"
moosh config-set field_lock_firstname unlockedifempty auth_manual
moosh config-set field_lock_lastname unlockedifempty auth_manual

echo >&2 "Blocking guest users watching forum messages"
moosh role-update-capability guest mod/forum:viewdiscussion prohibit 1

#Update default notification configuration for users. Popup instead of email.
echo >&2 "Updating default notification preferences"
moosh config-set message_provider_enrol_self_expiry_notification_loggedin email,airnotifier message
moosh config-set message_provider_enrol_self_expiry_notification_loggedoff email,airnotifier message
moosh config-set message_provider_mod_assign_assign_notification_loggedin popup,airnotifier message
moosh config-set message_provider_mod_assign_assign_notification_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_feedback_message_loggedin popup,airnotifier message
moosh config-set message_provider_mod_feedback_message_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_feedback_submission_loggedin popup,airnotifier message
moosh config-set message_provider_mod_feedback_submission_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_forum_digests_loggedin popup,airnotifier message
moosh config-set message_provider_mod_forum_digests_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_forum_posts_loggedin popup,airnotifier message
moosh config-set message_provider_mod_forum_posts_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_hvp_confirmation_loggedin popup,airnotifier message
moosh config-set message_provider_mod_hvp_confirmation_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_lesson_graded_essay_loggedin popup,airnotifier message
moosh config-set message_provider_mod_lesson_graded_essay_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_pdfannotator_forwardedquestion_loggedin email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_forwardedquestion_loggedoff email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newanswer_loggedin email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newanswer_loggedoff email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newquestion_loggedin email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newquestion_loggedoff email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newreport_loggedin email,airnotifier message
moosh config-set message_provider_mod_pdfannotator_newreport_loggedoff email,airnotifier message
moosh config-set message_provider_mod_quiz_attempt_overdue_loggedin popup,airnotifier message
moosh config-set message_provider_mod_quiz_attempt_overdue_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_quiz_confirmation_loggedin popup,airnotifier message
moosh config-set message_provider_mod_quiz_confirmation_loggedoff popup,airnotifier message
moosh config-set message_provider_mod_quiz_submission_loggedin popup,airnotifier message
moosh config-set message_provider_mod_quiz_submission_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_badgecreatornotice_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_badgecreatornotice_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_badgerecipientnotice_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_badgerecipientnotice_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_competencyplancomment_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_competencyplancomment_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_competencyusercompcomment_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_competencyusercompcomment_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_coursecompleted_loggedin email,airnotifier message
moosh config-set message_provider_moodle_coursecompleted_loggedoff email,airnotifier message
moosh config-set message_provider_moodle_courserequestapproved_loggedin email,airnotifier message
moosh config-set message_provider_moodle_courserequestapproved_loggedoff email,airnotifier message
moosh config-set message_provider_moodle_courserequestrejected_loggedin email,airnotifier message
moosh config-set message_provider_moodle_courserequestrejected_loggedoff email,airnotifier message
moosh config-set message_provider_moodle_gradenotifications_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_gradenotifications_loggedoff popup,email,airnotifier message
moosh config-set message_provider_moodle_instantmessage_loggedin popup,airnotifier message
moosh config-set message_provider_moodle_instantmessage_loggedoff popup,airnotifier message
moosh config-set message_provider_moodle_messagecontactrequests_loggedin airnotifier message
moosh config-set message_provider_moodle_messagecontactrequests_loggedoff airnotifier message
moosh config-set message_provider_tool_dataprivacy_contactdataprotectionofficer_loggedin popup,email,airnotifier message
moosh config-set message_provider_tool_dataprivacy_contactdataprotectionofficer_loggedoff popup,email,airnotifier message
moosh config-set message_provider_tool_dataprivacy_datarequestprocessingresults_loggedin popup,email,airnotifier message
moosh config-set message_provider_tool_dataprivacy_datarequestprocessingresults_loggedoff popup,email,airnotifier message
moosh config-set message_provider_tool_messageinbound_invalidrecipienthandler_loggedin popup,airnotifier message
moosh config-set messmailmessage_provider_tool_messageinbound_invalidrecipienthandler_loggedoff popup,airnotifier message
moosh config-set message_provider_tool_messageinbound_messageprocessingerror_loggedoff popup,airnotifier message

# Para FPD quitar insignias (enablebadges)
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        moosh config-set enablebadges 0
fi

# Para FPD quitar analítica (enableanalytics)
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        moosh config-set enableanalytics 0
fi

#Update capability student configuration for avoiding emails between them
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "For FP distancia we allow students view other students profile" 
    else
        moosh role-update-capability student moodle/user:viewdetails prohibit 1
fi

# Set specific configuration for FPD
if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        # duplicate activities
        moosh role-update-capability teacher moodle/restore:restoretargetimport allow 1
        moosh role-update-capability teacher moodle/backup:backuptargetimport allow 1
        # avoid changing short name, used for automations
        moosh role-update-capability teacher moodle/course:changeshortname prohibit 1
        moosh role-update-capability teacher moodle/course:changefullname prohibit 1
        # avoid access to repositories
        moosh role-update-capability teacher repository/contentbank:accessgeneralcontent prohibit 1
        # avoid manual unenrolments for teachers
        moosh role-update-capability teacher enrol/cohort:config prohibit 1
        moosh role-update-capability teacher enrol/database:config prohibit 1
        moosh role-update-capability teacher enrol/guest:config prohibit 1
        moosh role-update-capability teacher enrol/imsenterprise:config prohibit 1
        moosh role-update-capability teacher enrol/lti:unenrol prohibit 1
        moosh role-update-capability teacher enrol/manual:unenrol prohibit 1
        moosh role-update-capability teacher enrol/paypal:manage prohibit 1
        moosh role-update-capability teacher enrol/self:config prohibit 1
        moosh role-update-capability teacher enrol/self:unenrol prohibit 1
        moosh role-update-capability teacher enrol/fee:manage prohibit 1
        moosh role-update-capability teacher enrol/manual:manage prohibit 1
        moosh role-update-capability teacher enrol/cohort:unenrol prohibit 1
        moosh role-update-capability teacher enrol/manual:unenrolself prohibit 1

fi

# #unoconv
# if [[ "${SCHOOL_TYPE}" = "FPD" ]];
#     then
#         echo "For FP distancia we don't install unoconv package" #Problemas de rendimiento, cuelgan imagen
#     else
#         echo "Installing unoconv package"
#         apt-get update
#         apt-get install unoconv -y
#         mkdir ../.config
#         chown -R www-data:www-data ../.config
# fi

echo >&2 "Updating default HTTP configuration"
moosh config-set getremoteaddrconf 1

echo >&2 "Activating Messaging in Moodle general configuration"
moosh -n config-set messaging 1

echo >&2 "Activating Mobile configuration for push notifications"
if [[ "${SCHOOL_TYPE}" = "FPD" ]]; 
    then
        #El centro necesitará activar las salidas de mensaje por móvil
        moosh -n config-set airnotifierurl "https://bma.messages.moodle.net"
        #moosh -n config-set airnotifierport 443
        moosh -n config-set airnotifiermobileappname "es.aragon.fpdistancia"
        moosh -n config-set airnotifierappname "esaragonfpdistancia"
        moosh -n config-set airnotifieraccesskey "1e6698fd71bad502044c09a4f547f65c"
    else
        #El centro necesitará activar las salidas de mensaje por móvil
        moosh -n config-set airnotifierurl "https://bma.messages.moodle.net"
        #moosh -n config-set airnotifierport 443
        moosh -n config-set airnotifiermobileappname "es.aragon.aeducar"
        moosh -n config-set airnotifierappname "esaragonaeducar"
        moosh -n config-set airnotifieraccesskey "d1f92a7a2d7a665bd3179e8f9f6d94f7"
fi


#Habilitar actividades sigilosas
echo >&2 "Activating allowstealth activities"
moosh -n config-set allowstealth 1

#Habilitar MoodleNet
echo >&2 "Activating MoodleNet"
moosh -n config-set enablemoodlenet 1 tool_moodlenet
moosh -n config-set activitychooseractivefooter tool_moodlenet

#Habilitar descarga de curso
echo >&2 "Activating Course Content Download"
moosh -n config-set downloadcoursecontentallowed 1