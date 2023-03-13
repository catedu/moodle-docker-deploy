#!/bin/bash

#Uso el nombre completo del fichero tar.gz para evitar ambigüedades con el de versiones anteriores
cp /init-scripts/themes/moove_settings_1678709978.tar.gz /var/www/html/        
moosh theme-settings-import --targettheme moove moove_settings_1678709978.tar.gz
cp /init-scripts/themes/frontpage.mustache /var/www/html/theme/moove/templates
cp /init-scripts/themes/booFont/* /var/www/html/theme/moove/fonts/

#Quitamos la imagen de fondo de la página de login. Añadido para moodle4.
moosh config-set loginbgimg '' theme_moove
moosh config-set brandcolor '#457b9d' theme_moove

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

echo >&2 "Theme configured."




