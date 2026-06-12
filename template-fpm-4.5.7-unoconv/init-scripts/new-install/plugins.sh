#!/bin/bash
# WHEN: 
# - new-install
# - upgrade (remove moodle-code)
#
# example command: moosh config-set name value <plugin>
# Plugin list with [--release <build version>] if different from last
# hack: last one does never get active, so install one more

# Load sensitive data or configurable data from a .env file
#export $(grep -E -v '^#' /init-scripts/.env | xargs)


set +x # to get info 



# INSTALL PLUGINS (theme is installed in theme.sh)

echo >&2 "Installing plugins..."

#=======
#theme_moove

cp -r /init-scripts/plugins/moove  /var/www/html/theme/
chown -R www-data:www-data /var/www/html/theme/moove
php admin/cli/upgrade.php --non-interactive

moosh config-set theme moove
cp /init-scripts/themes/*tar.gz /var/www/html/
moosh theme-settings-import --targettheme moove moove*tar.gz
moosh config-set loginbgimg '' theme_moove
moosh config-set brandcolor '#457b9d' theme_moove
cp /init-scripts/themes/frontpage.mustache /var/www/html/theme/moove/templates
cp /init-scripts/themes/booFont/* /var/www/html/theme/moove/fonts/
#Añadido desde madeby para moodle4.1
        moosh config-set scss "
        input[value|='CC'] {
            display: none !important;
        }

        input[value|='Para'] {
            display: none !important;
        }

        input[value|='Responder Todos'] {
            display: none !important;
        }

        @font-face {
        font-family: 'Boo';
        src: url([[font:theme|Boo.eot]]);
        src: url([[font:theme|Boo.eot]]) format('embedded-opentype'),
        url([[font:theme|Boo.woff]]) format('woff'),
        url([[font:theme|Boo.woff2]]) format('woff2'),
        url([[font:theme|Boo.ttf]]) format('truetype'),
        url([[font:theme|Boo.svg]]) format('svg');
        font-weight: normal;
        font-style: normal;
        }
        .madeby {
            display: none;
        }
        .contact {
            display: none;
        }
        .socialnetworks {
            display: none;
        }
        .supportemail {
            display: none;
        }
        .path-login {
            #page {
            max-width: 100%;
            }
            .login-container {
            .login-logo {
                justify-content: center;
            }
            }
            .login-identityprovider-btn.facebook {
            background-color: $facebook-color;
            color: #fff;
            }
}
        " theme_moove

echo >&2 "Plugin theme_moove installed!"
#===========

#=========
#format_tiles
cp -r /init-scripts/plugins/tiles  /var/www/html/course/format
chown -R www-data:www-data /var/www/html/course/format/tiles
php admin/cli/upgrade.php --non-interactive
moosh config-set tilecolour1 457b9d format_tiles
moosh config-set usejavascriptnav 0 format_tiles     #desactivamos la navegación javascript en moodle4
moosh config-set phototiletitletransarency 0.3 format_tiles
moosh config-set showprogresssphototiles 0 format_tiles
moosh config-set showseczerocoursewide 1 format_tiles
echo >&2 "Plugin format_tiles installed!"

#===========

#==========
#local_mail
cp -r /init-scripts/plugins/mail  /var/www/html/local
chown -R www-data:www-data /var/www/html/local/mail
php admin/cli/upgrade.php --non-interactive
moosh role-update-capability student local/mail:mailsamerole prohibit 1  #Prohibit to write to each other
moosh config-set maxfiles 5 local_mail
moosh config-set maxbytes 2097152 local_mail
moosh config-set  message_provider_local_mail_mail_loggedin    popup   message
moosh config-set  message_provider_local_mail_mail_loggedoff    popup   message  
echo >&2 "Plugin local_mail installed!"
#==========

#==========
#block_xp; availability_xp
cp -r /init-scripts/plugins/xp  /var/www/html/blocks
chown -R www-data:www-data /var/www/html/blocks/xp
cp -r /init-scripts/plugins/availability_xp/xp  /var/www/html/availability/condition
chown -R www-data:www-data /var/www/html/availability/condition/xp
php admin/cli/upgrade.php --non-interactive
moosh config-set blocktitle "¡Sube de nivel!" block_xp
echo >&2 "Plugins block_xp and availability_xp installed!"
#==========

#==========
#block_completion_progress
cp -r /init-scripts/plugins/completion_progress  /var/www/html/blocks
chown -R www-data:www-data /var/www/html/blocks/completion_progress
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin block_completion_progress installed!"
#==========

#==========
#grade_me
cp -r /init-scripts/plugins/grade_me  /var/www/html/blocks
chown -R www-data:www-data /var/www/html/blocks/grade_me
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin block_grade_me installed!"
#==========

#==========
#mod_board
cp -r /init-scripts/plugins/board  /var/www/html/mod
chown -R www-data:www-data /var/www/html/mod/board
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin mod_board installed!"
#==========

#==========
#mod_pdfannotator
cp -r /init-scripts/plugins/pdfannotator  /var/www/html/mod
chown -R www-data:www-data /var/www/html/mod/pdfannotator
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin mod_pdfannotator installed!"
#==========

#==========
#mod_choicegroup
cp -r /init-scripts/plugins/choicegroup  /var/www/html/mod
chown -R www-data:www-data /var/www/html/mod/choicegroup
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin mod_choicegroup installed!"
#==========

#==========
#mod_checklist
cp -r /init-scripts/plugins/checklist  /var/www/html/mod
chown -R www-data:www-data /var/www/html/mod/checklist
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin mod_checklist installed!"
#==========

#==========
#mod_attendance
cp -r /init-scripts/plugins/attendance  /var/www/html/mod
chown -R www-data:www-data /var/www/html/mod/attendance
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin mod_attendance installed!"
#==========

#==========
#booktool_wordimport
cp -r /init-scripts/plugins/wordimport  /var/www/html/mod/book/tool
chown -R www-data:www-data /var/www/html/mod/book/tool/wordimport
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin booktool_wordimport installed!"
#==========

#==========
#tiny plugins 
cp -r /init-scripts/plugins/tiny/fontsize  /var/www/html/lib/editor/tiny/plugins
chown -R www-data:www-data /var/www/html/lib/editor/tiny/plugins
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugins tiny_fontsize installed!"

cp -r /init-scripts/plugins/tiny/fontcolor  /var/www/html/lib/editor/tiny/plugins
chown -R www-data:www-data /var/www/html/lib/editor/tiny/plugins
php admin/cli/upgrade.php --non-interactive
moosh config-set textcolors '[{"name":"Negro","value":"#000000FF"},{"name":"Rojo","value":"#FF0000FF"},{"name":"Azul","value":"#0000FFFF"},{"name":"Verde","value":"#00AA00FF"},{"name":"Amarillo","value":"#FFFF00FF"},{"name":"Naranja","value":"#FFA500FF"},{"name":"Gris","value":"#666666FF"}]' tiny_fontcolor
moosh config-set backgroundcolors '[{"name":"Amarillo","value":"#FFF9A6FF"},{"name":"Azul","value":"#D8ECFFFF"},{"name":"Verde","value":"#DFFFD6FF"},{"name":"Rosa","value":"#FFE4EAFF"},{"name":"Gris","value":"#F2F2F2FF"},{"name":"Naranja","value":"#FFE8CCFF"}]' tiny_fontcolor
moosh config-set textcolorpicker 1 tiny_fontcolor
moosh config-set backgroundcolorpicker 1 tiny_fontcolor
echo >&2 "Plugins tiny_fontcolor installed!"


cp -r /init-scripts/plugins/tiny/wordimport  /var/www/html/lib/editor/tiny/plugins
chown -R www-data:www-data /var/www/html/lib/editor/tiny/plugins
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugins tiny_wordimport installed!"

cp -r /init-scripts/plugins/tiny/fontfamily  /var/www/html/lib/editor/tiny/plugins
chown -R www-data:www-data /var/www/html/lib/editor/tiny/plugins
php admin/cli/upgrade.php --non-interactive
moosh config-set fonts $'
Arial
Verdana
Tahoma
Trebuchet MS
Times New Roman
Georgia
Garamond
Courier New
Brush Script MT
Boo
  
' tiny_fontfamily 

#Activamos grabación de pantalla
moosh config-set  allowedtypes audio,video,screen tiny_recordrtc

echo >&2 "Plugins tiny installed!"
#==========

#==========
#quizaccess_onesession
cp -r /init-scripts/plugins/onesession  /var/www/html/mod/quiz/accessrule
chown -R www-data:www-data /var/www/html/mod/quiz/accessrule/onesession
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin quizaccess_onesession installed!"
#==========


#==========
#qtype_gapfill
cp -r /init-scripts/plugins/gapfill  /var/www/html/question/type
chown -R www-data:www-data /var/www/html/question/type/gapfill
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin qtype_gapfill installed!"
#==========

#==========
#qtype_matrix
cp -r /init-scripts/plugins/matrix  /var/www/html/question/type
chown -R www-data:www-data /var/www/html/question/type/matrix
php admin/cli/upgrade.php --non-interactive
echo >&2 "Plugin qtype_gapmatrix installed!"
#==========

moosh module-manage show bigbluebuttonbn
moosh config-set bigbluebuttonbn_server_url 'https://rp-catedu.api.rna1.blindsidenetworks.com/bigbluebutton/api/'
moosh config-set bigbluebuttonbn_shared_secret 'ca9415d0acf0842f5070b1d29b0bef35'

echo >&2 "Plugins installed and configurated!"

set -x
  
