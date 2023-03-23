#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (remove moodle-code, previous export should be made!)


# Load sensitive data or configurable data from a .env file
# export $(grep -E -v '^#' /init-scripts/.env | xargs)


# Config theme snap, already imported via plugins.sh
echo "Configuring theme..."

moosh config-set theme moove
# import theme settings:
# script needs to be in /var/www/html and name like theme_xxxx
# it can't have info about directories: ./  so next line is not valid and I have to "hack it"
# tar -zcf snap_settings.tar.gz -C /init-scripts/snap_settings .

# find /init-scripts/snap_settings -type f -printf "%f\n" | xargs tar -zcf snap_settings.tar.gz -C /init-scripts/snap_settings
# moosh theme-settings-import --targettheme snap snap_settings.tar.gz


if [[ "${SCHOOL_TYPE}" = "FPD" ]];
    then
        echo "... for FPD..."
        # echo "  restaurando tema"
        # cp /init-scripts/themes/fpdist/moove*tar.gz /var/www/html/
        # moosh theme-settings-import --targettheme moove moove*tar.gz
        # echo "  copiando imágenes y logos"
        # cp -R /init-scripts/themes/fpdist/style /var/www/html/theme/moove
        # echo "  copiando plantillas"
        # cp /init-scripts/themes/fpdist/inc_start.mustache /var/www/html/theme/moove/templates
        # cp /init-scripts/themes/fpdist/header.mustache /var/www/html/theme/moove/templates
        cp /init-scripts/themes/fpdist/footer.mustache /var/www/html/theme/moove/templates
        
        echo "  copiando política de privacidad"
        cp /init-scripts/themes/fpdist/politica-privacidad.php /var/www/html/politica-privacidad.php
        
        # echo "  configurando página principal"
        # moosh config-set frontpage none
        
        # # set the default img for courses in lists. Doesn't modify course header
        # echo "  estableciendo imagen por defecto para los cursos"
        # cp /init-scripts/themes/fpdist/img/default_course.jpg /var/www/html/theme/moove/pix/default_course.jpg
        # cp /init-scripts/themes/fpdist/img/default_coursesummary.jpg /var/www/html/theme/moove/pix/default_coursesummary.jpg
        
        # # soporte
        echo "  soporte"
        mkdir /var/www/html/soporte/
        cp -R /init-scripts/themes/fpdist/soporte /var/www/html/soporte
        cp /init-scripts/themes/fpdist/soporte/secret-sample.php /var/www/html/soporte/secret.php 
        
        # images
        echo "  images"
    else
        echo "... for not FPD..."     
        cp /init-scripts/themes/*tar.gz /var/www/html/        
	    moosh theme-settings-import --targettheme moove moove*tar.gz
        cp /init-scripts/themes/frontpage.mustache /var/www/html/theme/moove/templates
        cp /init-scripts/themes/booFont/* /var/www/html/theme/moove/fonts/
        
        #Quitamos la imagen de fondo de la página de login. Añadido para moodle4.
        moosh config-set loginbgimg '' theme_moove
        moosh config-set brandcolor '#457b9d' theme_moove

        #Añadido desde madeby para moodle4
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
fi
echo >&2 "Theme configured."
