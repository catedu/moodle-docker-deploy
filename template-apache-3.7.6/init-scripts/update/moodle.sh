#!/bin/bash
# WHEN:
# - new-install
# - update (just to fix a bug!)
# - upgrade (if new parameters needed, expected to be empty)



# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)

#Actualizaciones realizadas el 17-09-20: ampliar tamaño de subida archivo por defecto a 50MB (el servidor se amplía hasta 192) y añadir bloque Acceso Familias
#echo >&2 "set value of max_file_size by default in courses"
#moosh config-set maxbytes 52428800


#echo >&2 "Adding Mentees block (Acceso Familias)"
#moosh block-add category 1 mentees site-index side-pre 0
#moosh sql-run "update mdl_block_instances SET parentcontextid=1, configdata='Tzo4OiJzdGRDbGFzcyI6MTp7czo1OiJ0aXRsZSI7czoxNToiQWNjZXNvIEZhbWlsaWFzIjt9' WHERE blockname='mentees'"

#Actualización en la que se modifica la configuración de notificaciones por defecto, para que notifique por popup en lugar de por email
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
