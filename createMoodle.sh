#!/bin/bash

# Deberíamos recoger los siguientes datos:

# MOODLE_ADMIN_USER=adminuser
# MOODLE_ADMIN_PASSWORD=adminpassword
# MOODLE_ADMIN_EMAIL=example@example.com
# MOODLE_LANG=en
# MOODLE_SITE_NAME=MySite
# MOODLE_SITE_FULLNAME=This is my awesome site

# deberíamos generar un usuario de conexión a bbdd y un nombre en base al nombre del centro
# y una contraseña aleatoria

# load database variables
set -a
[ -f .env ] && . .env
set +a
# create database, user and grants 
mysql --user="root" --password="${MYSQL_ROOT_PASSWORD}" --host="${MOODLE_DB_HOST}" --execute="CREATE DATABASE test DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER pepito IDENTIFIED BY 'password'; GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,CREATE TEMPORARY TABLES,DROP,INDEX,ALTER ON moodle.* to 'pepito'@'%'"



# MOODLE_DB_HOST=db
# MOODLE_DB_NAME=moodle
# MOODLE_MYSQL_USER=dbuser
# MOODLE_MYSQL_PASSWORD=dbpassword

# una vez hecho el deploy deberíamos mandar un correo al MOODLE_ADMIN_EMAIL????
# también deberíamos tener claro si hacemos importación de datos y como

