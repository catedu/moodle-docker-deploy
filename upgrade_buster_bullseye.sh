#!/bin/bash
# Script de actualización segura de un servidor de Debian 10 a Debian 11
# Basado en la inforamción de actualización de Debian.
# https://www.debian.org/releases/bullseye/amd64/release-notes/ch-upgrading.es.html

###
## Acciones previas
###
## 0. Comprobar copias realizadas y terminadas.
## 1. Parar los servicios
# for SITE in *.es; do cd $SITE;echo -e "** SITE: $SITE **\n";docker compose down; cd ..;done
## 2. Comprobar los que no se han parado y parar si es necesario
## 3. Actualizar debian 10 base

## Correr éste script

###
## Acciones posteriores
###
## 1. Actualizar imagen de nginx, letsencrypt y sysdig-agent
## 2. Levantar todos los servicios
# for SITE in *.es; do cd $SITE;echo -e "** SITE: $SITE **\n";docker compose up -d; cd ..;done
# Levantar los servicios que no se hubieran levantado con lo anterior.
## nginx, letsencrypt y sysdig-agent

####
## Comprobaciones Previas
####
echo -e "#####\n## COMPROABCIONES PRE_UPRGADE ##\n######"
[ "$(lsb_release -cs)" = "buster" ] || {
    echo "El equipo no es es Debian 10 buster...salimos"
    exit 0
}
echo -e "\n\t## Distribución buster -> ok ##\n"

[ "$(uname -r)" = "4.19.0-26-amd64" ] || {
    echo "El equipo debian 10 NO está actualizado (y reiniciado)...realiza la actualización (apt update && apt dist-upgrade) y reinicia"
    exit 0
}
echo -e "\n\t## Buster actualizado -> ok ##\n"

# ## Posibles incompatibilidades de red. Es posible que no se soporte en buster el tema del 70-peristent-net.rules
# ### https://www.debian.org/releases/buster/amd64/release-notes/ch-information.es.html#migrate-interface-names

# ### Se puede comprobar las interfaces de red con nombres "antiguos"
# #echo /sys/class/net/[ew]*
# INTERFACES=$(
#     cd /sys/class/net/
#     ls -d [ew]*
# )
# ### Comprobar los archivos que usan dichas intefaces
# for IFACE in $INTERFACES; do
#     echo "Modificando Interfaz = $IFACE"
#     NEWIFACE=$(udevadm test-builtin net_id /sys/class/net/${IFACE} 2>/dev/null | grep ID_NET_NAME_PATH | awk -F "=" '{ print $2 }')
#     FILES=$(rgrep -w "$IFACE" /etc)
#     for FILE in $FILES; do
#         sed --follow-symlinks -i "s/${IFACE}/${NEWIFACE}/g" $FILE
#     done
# done
# [ "$INTERFACES" != "" ] && update-initramfs -u && reboot

apt-get -y install apt-forktracer aptitude || {
    echo "No puedo obtener apt-froktracer"
    exit 1
}
echo -e "\n\t## Revisando Unifi ##\n"
UNIFIVERSION=$(dpkg -l | grep unifi | awk '{print $3}')
[ "$UNIFIVERSION" != "" ] && [ "$UNIFIVERSION" != "7.1.66-17875-1" ] && {
    echo "Servidor con unifi instalado version obsoleta....revisar antes de continuar"
    exit 1
}

## Eliminar ficheros obsoletos y restos de configuración
# No eliminamos paquetes "obsoletos" ya que parecen ser necesarios para el upgrade en Debian 10
# echo -e "\n\t## Purgando paquetes obsoletos ##\n"
# apt-get -y install aptitude || {
#     echo "No puedo obtener apitude"
#     exit 1
# }
# aptitude -y purge '~o' || {
#     echo "No puedo purgar paquetes obsoletos....comprobar"
#     exit 1
# }
echo -e "\n\t## LISTADO restos de archivos de configuración ##\n"
find /etc -name '*.dpkg-*' -o -name '*.ucf-*' -o -name '*.merge-error'
echo -e "\n\t## FIN LISTADO restos de archivos de configuración ##\n"

## Comprobar paquetes que no son de la distribución...pueden dar problemas a la hora de actualizar (¿¿unifi por ejemplo NO tiene problema??)
echo -e "\n\t## Comprobando paquetes NODEBIAN ##\n"
PKSNODIST=$(apt-forktracer | grep -v 'containerd' | grep -v 'docker' | grep -v 'mariadb' | grep -v 'mysql-common' | grep -v 'nodejs' | grep -v 'linux-image' | sort)
[ "$PKSNODIST" != "" ] && {
    echo "Revisar paquetes antes de upgrade en Servidor: ${PKSNODIST}"
    exit 1
}
echo -e "\n\t## Paquetes sin instalar? ##\n"
apt-get -f install || {
    echo "Queda pendiente algo por instalar"
    exit 1
}
echo -e "\n\t## Auditando DPKG ##\n"
dpkg --audit || {
    echo "Error en la auditoría de dpkg"
    exit 1
}
echo -e "\n\t## Comprobando paquetes CONGELADOS ##"
PKSHOLD=$(aptitude search "~ahold")
[ "$PKSHOLD" != "" ] && {
    echo "Revisar paquetes antes de upgrade en Servidor"
    exit 1
}
echo -e "\n\t## COMPROBACIONES PREVIAS -> OK ##\n"

####
## FIN Comprobaciones Previas
####

## Modificamos el fichero de Sources
echo -e "\n\t## COMPACTANDO EL FICHERO SOURCES.LIST ##\n"
SOURCEFILE="/etc/apt/sources.list"
[ -f "$SOURCEFILE" ] && cp "$SOURCEFILE" "$SOURCEFILE".debian10
cat >"$SOURCEFILE" <<EOF
deb http://deb.debian.org/debian/ bullseye main contrib non-free
deb-src http://deb.debian.org/debian/ bullseye main contrib non-free

deb http://security.debian.org/debian-security bullseye-security main contrib 
deb-src http://security.debian.org/debian-security bullseye-security main contrib 

# bullseye-updates, to get updates before a point release is made;
# see https://www.debian.org/doc/manuals/debian-reference/ch02.en.html#_updates_and_backports
deb http://deb.debian.org/debian/ bullseye-updates main contrib
deb-src http://deb.debian.org/debian/ bullseye-updates main contrib

# docker
deb [arch=amd64] https://download.docker.com/linux/debian bullseye stable

EOF
[ -f /etc/apt/sources.list.d/nodesource.list ] && rm -f /etc/apt/sources.list.d/nodesource.list

apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 605C66F00D6C9793 \
    0E98404D386FA1D9 648ACFD622F3D138
apt-get update || {
    echo "Fallo al actualizar el sources.list"
    exit 1
}
echo -e "\n\t## SOURCESMODIFCADO ##\n"


echo -e "\n\t## ACTUALIZACIÓN MASIVA... ##\n"
apt full-upgrade
echo -e "\n\t######    FIN DEL UPGRADE...BE HAPPY...######"
###### Fin UPGRADE #####

