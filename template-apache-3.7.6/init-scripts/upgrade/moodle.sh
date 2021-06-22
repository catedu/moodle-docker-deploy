#!/bin/bash
# WHEN: 
# - new-install
# - update (just to fix a bug!)
# - upgrade (if new parameters needed, expected to be empty)



# Load sensitive data or configurable data from a .env file
export $(grep -E -v '^#' /init-scripts/.env | xargs)


echo >&2 "Activating Messaging in Moodle general configuration"
moosh -n config-set messaging 1

echo >&2 "Activating Mobile configuration for push notifications"
#El centro necesitará activar las salidas de mensaje por móvil
moosh -n config-set airnotifierurl "https://bma.messages.moodle.net"
#moosh -n config-set airnotifierport 443
moosh -n config-set airnotifiermobileappname "es.aragon.aeducar"
moosh -n config-set airnotifierappname "esaragonaeducar"
moosh -n config-set airnotifieraccesskey "d1f92a7a2d7a665bd3179e8f9f6d94f7"
#Habilitar actividades sigilosas
moosh -n config-set allowstealth 1