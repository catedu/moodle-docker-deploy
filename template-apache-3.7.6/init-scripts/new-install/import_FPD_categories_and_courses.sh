# categories
moosh category-create -p 0 -v 1 "IES Sierra de Guara"
moosh category-create -p 2 -v 1 -d "ADG201" "Gestión Administrativa"
moosh category-create -p 0 -v 1 "IES Santa Emerenciana"
moosh category-create -p 4 -v 1 -d "ADG201" "Gestión Administrativa"
moosh category-create -p 0 -v 1 "IES Tiempos Modernos"
moosh category-create -p 6 -v 1 -d "ADG201" "Gestión Administrativa"
moosh category-create -p 0 -v 1 "CPIFP Pirámide"
moosh category-create -p 8 -v 1 -d "ELE202" "Instalaciones Eléctricas y Automáticas"
moosh category-create -p 0 -v 1 "CPIFP Los Enlaces"
# the next is the 11
moosh category-create -p 10 -v 1 -d "IFC201" "Sistemas Microinformáticos y Redes"
moosh category-create -p 10 -v 1 -d "COM301" "Comercio Internacional"
moosh category-create -p 10 -v 1 -d "COM302" "Gestión de Ventas y Espacios Comerciales"
moosh category-create -p 10 -v 1 -d "COM303" "Transporte y Logística"
moosh category-create -p 10 -v 1 -d "IFC303" "Desarrollo de Aplicaciones Web"
moosh category-create -p 10 -v 1 -d "IMS302" "Producción de Audiovisuales y Espectáculos"
moosh category-create -p 0 -v 1 "IES Río Gállego"
moosh category-create -p 17 -v 1 -d "SAN202" "Farmacia y Parafarmacia"
moosh category-create -p 17 -v 1 -d "SAN203" "Emergencias Sanitarias"
moosh category-create -p 0 -v 1 "IES Vega del Turia"
# the next is the 21st
moosh category-create -p 20 -v 1 -d "SAN203" "Emergencias Sanitarias"
moosh category-create -p 0 -v 1 "CPIFP Montearagón"
moosh category-create -p 22 -v 1 -d "SSC201" "Atención a Personas en Situación de Dependencia"
moosh category-create -p 0 -v 1 "IES Luis Buñuel"
moosh category-create -p 24 -v 1 -d "SSC201" "Atención a Personas en Situación de Dependencia"
moosh category-create -p 0 -v 1 "CPIFP Corona de Aragón"
moosh category-create -p 26 -v 1 -d "ADG301" "Administración y Finanzas"
moosh category-create -p 26 -v 1 -d "ADG302" "Asistencia a la Dirección"
moosh category-create -p 26 -v 1 -d "QUI301" "Laboratorio de Análisis y de Control de Calidad"
moosh category-create -p 0 -v 1 "IES Miralbueno"
## the next is the 31st
moosh category-create -p 30 -v 1 -d "HOT301" "Agencias de Viajes y Gestión de Eventos"
moosh category-create -p 0 -v 1 "IES Pablo Serrano"
moosh category-create -p 32 -v 1 -d "IFC301" "Administración de Sistemas Informáticos en Red"
moosh category-create -p 0 -v 1 "CPIFP Bajo Aragón"
moosh category-create -p 34 -v 1 -d "IFC302" "Desarrollo de Aplicaciones Multiplataforma"
moosh category-create -p 0 -v 1 "IES Martínez Vargas"
moosh category-create -p 36 -v 1 -d "SSC302" "Educación Infantil"
moosh category-create -p 0 -v 1 "IES Avempace"
moosh category-create -p 38 -v 1 -d "SSC302" "Educación Infantil"
moosh category-create -p 0 -v 1 "IES María Moliner"
# the next is te 41st
moosh category-create -p 40 -v 1 -d "SSC303" "Integración Social"
moosh category-create -p 0 -v 1 "Proyectos de trabajo"
moosh category-create -p 0 -v 1 "Sala de Profesorado"
# courses
# moosh course-restore /init-scripts/mbzs/3-biologia-primero-eso-ies.mbz 3
# moosh course-config-set course 2 shortname biologia-primero-eso
# moosh course-config-set course 2 fullname "Biología y Geología 1º"

# courses format configuration
moosh course-config-set category 3 format topics
moosh course-config-set category 5 format topics
moosh course-config-set category 7 format topics
moosh course-config-set category 9 format topics
moosh course-config-set category 11 format topics
moosh course-config-set category 12 format topics
moosh course-config-set category 13 format topics
moosh course-config-set category 14 format topics
moosh course-config-set category 15 format topics
moosh course-config-set category 16 format topics
moosh course-config-set category 18 format topics
moosh course-config-set category 19 format topics
moosh course-config-set category 21 format topics
moosh course-config-set category 23 format topics
moosh course-config-set category 25 format topics
moosh course-config-set category 27 format topics
moosh course-config-set category 28 format topics
moosh course-config-set category 29 format topics
moosh course-config-set category 31 format topics
moosh course-config-set category 33 format topics
moosh course-config-set category 35 format topics
moosh course-config-set category 37 format topics
moosh course-config-set category 39 format topics
moosh course-config-set category 41 format topics
moosh course-config-set category 43 format topics