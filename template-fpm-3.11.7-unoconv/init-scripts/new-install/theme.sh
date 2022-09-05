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
        echo "  restaurando tema"
        cp /init-scripts/themes/fpdist/moove*tar.gz /var/www/html/
        moosh theme-settings-import --targettheme moove moove*tar.gz
        echo "  copiando imágenes y logos"
        cp -R /init-scripts/themes/fpdist/style /var/www/html/theme/moove
        echo "  copiando plantillas"
        cp /init-scripts/themes/fpdist/inc_start.mustache /var/www/html/theme/moove/templates
        cp /init-scripts/themes/fpdist/header.mustache /var/www/html/theme/moove/templates
        cp /init-scripts/themes/fpdist/footer.mustache /var/www/html/theme/moove/templates
        echo "  copiando política de privacidad"
        cp /init-scripts/themes/fpdist/politica-privacidad.php /var/www/html/politica-privacidad.php
        echo "  configurando página principal"
        moosh config-set frontpage none
        # set the default img for courses in lists. Doesn't modify course header
        echo "  estableciendo imagen por defecto para los cursos"
        cp /init-scripts/themes/fpdist/img/default_course.jpg /var/www/html/theme/moove/pix/
        cp /init-scripts/themes/fpdist/img/default_coursesummary.jpg /var/www/html/theme/moove/pix/
        # soporte
        echo "  soporte"
        mkdir /var/www/html/soporte/
        cp -R /init-scripts/themes/fpdist/soporte /var/www/html/soporte
        cp /var/www/html/soporte/secret-sample.php /var/www/html/soporte/secret.php 
        # marketing
        echo "  marketing"
        cp /init-scripts/themes/fpdist/frontpage_marketing.mustache /var/www/html/theme/moove/templates
        # itainnova_tool (encuestas)
        echo "  itainnova_tool"
        cp -R /init-scripts/themes/fpdist/itainnova_tool /var/www/html/
    else
        echo "... for not FPD..."
        cp /init-scripts/themes/*tar.gz /var/www/html/        
        moosh plugin-list
	    moosh plugin-install -d -f --release 2020110900 theme_moove
	    moosh theme-settings-import --targettheme moove moove*tar.gz
        cp /init-scripts/themes/footer.mustache /var/www/html/theme/moove/templates
        cp /init-scripts/themes/booFont/* /var/www/html/theme/moove/fonts/
        moosh config-set scss "#page-site-index.notloggedin {
            .frontpage-marketing {
                margin: -1em 0 2em 0;
            }
        }
        .frontpage-guest-header {
        min-height: 0px !important;
        }

        input[value|='CC'] {
            display: none !important;
        }

        input[value|='Para'] {
            display: none !important;
        }

        input[value|='Responder Todos'] {
            display: none !important;
        }

        /* remove search when not logged in */
        body.notloggedin .moove-search-input { display: none;}

        #mooveslideshow { display: none;}

        .frontpage-guest-header #mooveslideshow {
        margin-top: 0px !important;
        display: block !important;
        max-height: 450px;
        overflow: hidden;
        }
        .logo img { height: 40px; }
        #page-site-index.slideshow #loginbox {
            display: none !important;
        }


        @media (min-width: 992px) {
            a.btn-login-top {
                display: block !important;
                margin-top: 18px;
            }
        }

        body.coursepresentation-cover {
            #page {
                padding-top: 10px !important;
            }
            .headerbkg { 
                position: static !important;
        
            }
        .withimage {
            background-position: left !important;
        }
        }

        #top-footer h3 { display: none; }

        /* no remove logo for small size, if not loggedin, otherwise hide it to fit all icons */ 
        @media (max-width: 575px) {
            .logo img { display: none !important; }
            body.notloggedin .logo img {
            width: 150px !important;
            height: auto !important;
            position: absolute !important;
            top: 25px !important;
            display: block !important;
        }
        span.logo {
            display: block !important;
            width: 100%;
        }
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
        " theme_moove
fi
echo >&2 "Theme configured."
