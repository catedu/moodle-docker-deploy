#!/bin/bash
##########################################
#                                                       MUY IMPORTANTE
#                                                       MUY IMPORTANTE
#                                                       MUY IMPORTANTE
# MUY IMPORTANTE                  MUY IMPORTANTE
#     MUY IMPORTANTE          MUY IMPORTANTE
#         MUY IMPORTANTE  MUY IMPORTANTE
#     MUY IMPORTANTE          MUY IMPORTANTE
# MUY IMPORTANTE                  MUY IMPORTANTE
#                                                       MUY IMPORTANTE
#                                                       MUY IMPORTANTE
#                                                       MUY IMPORTANTE
#
# Los IDs de las categorias y cursos son invariables
# NO deben modificarse entre despliegues para mantener la compatibilidad
# con plugin de videollamadas y edición de contenidos
##########################################

echo >&2 "Importing categories and courses..."

#############################################################################################
# Creo los usuarios, roles,... específicos de FPD:
#############################################################################################
echo "Creating users, roles,... of PFD"

# Create admin user for FPD

echo "Creating admin user for FP..."
FPD_ADMIN_USER_ID=$(moosh user-create --password "${FPD_PASSWORD}" --email "${FPD_EMAIL}" --digest 2 --city Aragón --country ES --firstname fp --lastname distancia admin2 | grep -o '[0-9]*' | tail -1)
moosh config-set siteadmins 2,"${FPD_ADMIN_USER_ID}"

# Crear rol y usuario de inspección
echo "Creating inspeccion role and configuring it..."
INSPECCION_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -a manager -n "Inspeccion" inspeccion | grep -o '[0-9]*' | tail -1)

# set permissions to inspeccion role
moosh role-import -f /init-scripts/themes/fpdist/roles/role-inspeccion.xml

# Creating user
INSPECCION_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email inspeccion@educa.aragon.es --digest 2 --city Aragón --country ES --firstname Inspección --lastname Inspección profinspector | grep -o '[0-9]*' | tail -1)

# Assiging user to r
moosh user-assign-system-role profinspector inspeccion

# Crear rol de jefaturas y usuarios
echo "Creating jefatura-estudios role and configuring it..."
JEFATURA_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -c system,category,course,block -n "Jefatura de estudios" jefatura-estudios | grep -o '[0-9]*' | tail -1)

# Setting permissions to jefatura de estudios role
moosh role-import -f /init-scripts/themes/fpdist/roles/role-jefatura-estudios.xml

# Creating users
JE_SG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES Sierra de Guara" prof_je_sg | grep -o '[0-9]*' | tail -1)
JE_SE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES SANTA EMERENCIANA" prof_je_se | grep -o '[0-9]*' | tail -1)
JE_TM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES TIEMPOS MODERNOS" prof_je_tm | grep -o '[0-9]*' | tail -1)
JE_LE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP LOS ENLACES" prof_je_le | grep -o '[0-9]*' | tail -1)
JE_CA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP CORONA DE ARAGÓN" prof_je_ca | grep -o '[0-9]*' | tail -1)
JE_PI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP PIRÁMIDE" prof_je_pi | grep -o '[0-9]*' | tail -1)
JE_SB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP SAN BLAS" prof_je_sb | grep -o '[0-9]*' | tail -1)
JE_MI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MIRALBUENO" prof_je_mi | grep -o '[0-9]*' | tail -1)
JE_PS_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES PABLO SERRANO" prof_je_ps | grep -o '[0-9]*' | tail -1)
JE_BA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP BAJO ARAGÓN" prof_je_ba | grep -o '[0-9]*' | tail -1)
JE_RG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES RÍO GÁLLEGO" prof_je_rg | grep -o '[0-9]*' | tail -1)
JE_VT_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES VEGA DEL TURIA" prof_je_vt | grep -o '[0-9]*' | tail -1)
JE_LB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES LUIS BUÑUEL" prof_je_lb | grep -o '[0-9]*' | tail -1)
JE_MO_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP MONTEARAGON" prof_je_mo | grep -o '[0-9]*' | tail -1)
JE_MV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARTÍNEZ VARGAS" prof_je_mv | grep -o '[0-9]*' | tail -1)
JE_AV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES AVEMPACE" prof_je_av | grep -o '[0-9]*' | tail -1)
JE_MM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARÍA MOLINER" prof_je_mm | grep -o '[0-9]*' | tail -1)

#############################################################################################
# Creo las categorías:
#############################################################################################
echo "Creating structure for categories..."

ID_CATEGORY_miscelanea=1
ID_CATEGORY_general=$(moosh category-create -p 0 -v 1 -d "general" "General" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_app=$(moosh category-create -p 0 -v 1 -d "app" "NO BORRAR - APP MOVIL" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_sg=$(moosh category-create -p 0 -v 1 -d "22002521" "IES SIERRA DE GUARA" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_sg_ga=$(moosh category-create -p "${ID_CATEGORY_sg}" -v 1 -d "ADG201" "Gestión Administrativa" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_se=$(moosh category-create -p 0 -v 1 -d "44003211" "IES SANTA EMERENCIANA" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_se_ga=$(moosh category-create -p "${ID_CATEGORY_se}" -v 1 -d "ADG201" "Gestión Administrativa" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_tm=$(moosh category-create -p 0 -v 1 -d "50010511" "IES TIEMPOS MODERNOS" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_tm_ga=$(moosh category-create -p "${ID_CATEGORY_tm}" -v 1 -d "ADG201" "Gestión Administrativa" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_se_ga=$(moosh category-create -p "${ID_CATEGORY_se}" -v 1 -d "ADG201" "Gestión Administrativa" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_le=$(moosh category-create -p 0 -v 1 -d "50010314" "CPIFP LOS ENLACES" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_smr=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IFC201" "Sistemas Microinformáticos y Redes" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_ac=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM201" "Actividades Comerciales" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_ci=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM301" "Comercio Internacional" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_gvec=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM302" "Gestión de Ventas y Espacios Comerciales" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_tl=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM303" "Transporte y Logística" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_daw=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IFC303" "Desarrollo de Aplicaciones WEB" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_le_pae=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IMS302" "Producción de Audiovisuales y Espectáculos" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_ca=$(moosh category-create -p 0 -v 1 -d "50018829" "CPIFP CORONA DE ARAGÓN" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_ca_ad=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "ADG302" "Asistencia a la Dirección" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_ca_af=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "ADG301" "Administración y Finanzas" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_ca_lacc=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "QUI301" "Laboratorio de Análisis y de Control de Calidad" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_pi=$(moosh category-create -p 0 -v 1 -d "22010712" "CPIFP PIRÁMIDE" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_pi_iea=$(moosh category-create -p "${ID_CATEGORY_pi}" -v 1 -d "ELE202" "Instalaciones Eléctricas y Automáticas" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_sb=$(moosh category-create -p 0 -v 1 -d "44003028" "CPIFP SAN BLAS" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_sb_eca=$(moosh category-create -p "${ID_CATEGORY_sb}" -v 1 -d "SEA301" "Educación y Control Ambiental" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_mi=$(moosh category-create -p 0 -v 1 -d "50010156" "IES MIRALBUENO" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_mi_avge=$(moosh category-create -p "${ID_CATEGORY_mi}" -v 1 -d "HOT301" "Agencias de Viajes y Gestión de Eventos" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_ps=$(moosh category-create -p 0 -v 1 -d "50010144" "IES PABLO SERRANO" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_ps_asir=$(moosh category-create -p "${ID_CATEGORY_ps}" -v 1 -d "IFC301" "Administración de Sistemas Informáticos en Red" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_ba=$(moosh category-create -p 0 -v 1 -d "44010537" "CPIFP BAJO ARAGÓN" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_ba_dam=$(moosh category-create -p "${ID_CATEGORY_ba}" -v 1 -d "IFC301" "Desarrollo de Aplicaciones Multiplataforma" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_rg=$(moosh category-create -p 0 -v 1 -d "50009567" "IES RÍO GÁLLEGO" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_rg_sti=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "ELE304" "Sistemas de Telecomunicaciones e Informáticos" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_rg_fp=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "SAN202" "Farmacia y Parafarmacia" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_rg_es=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "SAN203" "Emergencias Sanitarias" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_vt=$(moosh category-create -p 0 -v 1 -d "44003235" "IES VEGA DEL TURIA" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_vt_es=$(moosh category-create -p "${ID_CATEGORY_vt}" -v 1 -d "SAN203" "Emergencias Sanitarias" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_lb=$(moosh category-create -p 0 -v 1 -d "50008460" "IES LUIS BUÑUEL" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_lb_apsd=$(moosh category-create -p "${ID_CATEGORY_lb}" -v 1 -d "SSC201" "Atención a Personas en situación de Dependencia" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_mo=$(moosh category-create -p 0 -v 1 -d "22002491" "CPIFP MONTEARAGON" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_mo_apsd=$(moosh category-create -p "${ID_CATEGORY_mo}" -v 1 -d "SSC201" "Atención a Personas en situación de Dependencia" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_mv=$(moosh category-create -p 0 -v 1 -d "22004611" "IES MARTÍNEZ VARGAS" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_mv_ei=$(moosh category-create -p "${ID_CATEGORY_mv}" -v 1 -d "SSC302" "Educación Infantil (Formación Profesional)" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_av=$(moosh category-create -p 0 -v 1 -d "50009348" "IES AVEMPACE" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_av_ei=$(moosh category-create -p "${ID_CATEGORY_av}" -v 1 -d "SSC302" "Educación Infantil (Formación Profesional)" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_mm=$(moosh category-create -p 0 -v 1 -d "50008642" "IES MARÍA MOLINER" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_mm_is=$(moosh category-create -p "${ID_CATEGORY_mm}" -v 1 -d "SSC303" "Integración Social" | grep -o '[0-9]*' | tail -1)

ID_CATEGORY_cd=$(moosh category-create -p 0 -v 1 -d "50020125" "Campus Digital FP" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_smr=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "IFC201" "Sistemas Microinformáticos y Redes" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_asir=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "IFC301" "Administración de Sistemas Informáticos en Red" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_dam=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "IFC302" "Desarrollo de Aplicaciones Multiplataforma" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_daw=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "IFC303" "Desarrollo de Aplicaciones WEB" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_iabd=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "CESIFC02" "Inteligencia Artificial y Big Data" | grep -o '[0-9]*' | tail -1)
ID_CATEGORY_cd_ceti=$(moosh category-create -p "${ID_CATEGORY_cd}" -v 1 -d "CESIFC01" "Ciberseguridad en Entornos de las Tecnologías de la Información" | grep -o '[0-9]*' | tail -1)




#############################################################################################
# A los usuarios jefes de estudios les cambio su campo personalizado para que tengan el valor correspondiente a su categoría
#############################################################################################

# Añadir el campo personalizado a los usuarios y asignar a cada jefe de estudios el suyo 
echo "Creating custom fields for jefatura estudios..."
# # Creo el campo personalizado
moosh userprofilefields-import /init-scripts/themes/fpdist/custom-fields/user_profile_fields.csv

# # Asignar a cada usuario el valor que le corresponde en el campo personalizado
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_SG_USER_ID, 1, $ID_CATEGORY_sg, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_SE_USER_ID, 1, $ID_CATEGORY_se, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_TM_USER_ID, 1, $ID_CATEGORY_tm, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_LE_USER_ID, 1, $ID_CATEGORY_le, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_CA_USER_ID, 1, $ID_CATEGORY_ca, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_PI_USER_ID, 1, $ID_CATEGORY_pi, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_SB_USER_ID, 1, $ID_CATEGORY_sb, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_MI_USER_ID, 1, $ID_CATEGORY_mi, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_PS_USER_ID, 1, $ID_CATEGORY_ps, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_BA_USER_ID, 1, $ID_CATEGORY_ba, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_RG_USER_ID, 1, $ID_CATEGORY_rg, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_VT_USER_ID, 1, $ID_CATEGORY_vt, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_LB_USER_ID, 1, $ID_CATEGORY_lb, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_MO_USER_ID, 1, $ID_CATEGORY_mo, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_MV_USER_ID, 1, $ID_CATEGORY_mv, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_AV_USER_ID, 1, $ID_CATEGORY_av, 0)"
moosh sql-run "INSERT INTO mdl_user_info_data (userid, fieldid, data, dataformat) values ($JE_MM_USER_ID, 1, $ID_CATEGORY_mm, 0)"


#############################################################################################
# Creo las cohortes
#############################################################################################
echo "Creating cohorts..."

moosh cohort-create -d "alumnado" -i alumnado -c "${ID_CATEGORY_general}" "alumnado"
moosh cohort-create -d "profesorado" -i profesorado -c "${ID_CATEGORY_general}" "profesorado"
moosh cohort-create -d "coordinacion" -i coordinacion -c "${ID_CATEGORY_general}" "coordinacion"
moosh cohort-create -d "jefaturas" -i jefaturas -c "${ID_CATEGORY_general}" "jefaturas"

moosh cohort-create -d "22002491-SSC201" -i 22002491-SSC201 -c "${ID_CATEGORY_mo}" "22002491-SSC201"
moosh cohort-create -d "22002521-ADG201" -i 22002521-ADG201 -c "${ID_CATEGORY_sg}" "22002521-ADG201"
moosh cohort-create -d "22004611-SSC302" -i 22004611-SSC302 -c "${ID_CATEGORY_mv}" "22004611-SSC302"
moosh cohort-create -d "22010712-ELE202" -i 22010712-ELE202 -c "${ID_CATEGORY_pi}" "22010712-ELE202"
moosh cohort-create -d "44003028-SEA301" -i 44003028-SEA301 -c "${ID_CATEGORY_sb}" "44003028-SEA301"
moosh cohort-create -d "44003211-ADG201" -i 44003211-ADG201 -c "${ID_CATEGORY_se}" "44003211-ADG201"
moosh cohort-create -d "44003235-SAN203" -i 44003235-SAN203 -c "${ID_CATEGORY_vt}" "44003235-SAN203"
moosh cohort-create -d "44010537-IFC302" -i 44010537-IFC302 -c "${ID_CATEGORY_ba}" "44010537-IFC302"
moosh cohort-create -d "50008460-SSC201" -i 50008460-SSC201 -c "${ID_CATEGORY_lb}" "50008460-SSC201"
moosh cohort-create -d "50008642-SSC303" -i 50008642-SSC303 -c "${ID_CATEGORY_mm}" "50008642-SSC303"
moosh cohort-create -d "50009348-SSC302" -i 50009348-SSC302 -c "${ID_CATEGORY_av}" "50009348-SSC302"
moosh cohort-create -d "50009567-ELE304" -i 50009567-ELE304 -c "${ID_CATEGORY_rg}" "50009567-ELE304"
moosh cohort-create -d "50009567-SAN202" -i 50009567-SAN202 -c "${ID_CATEGORY_rg}" "50009567-SAN202"
moosh cohort-create -d "50009567-SAN203" -i 50009567-SAN203 -c "${ID_CATEGORY_rg}" "50009567-SAN203"
moosh cohort-create -d "50010144-IFC301" -i 50010144-IFC301 -c "${ID_CATEGORY_ps}" "50010144-IFC301"
moosh cohort-create -d "50010156-HOT301" -i 50010156-HOT301 -c "${ID_CATEGORY_mi}" "50010156-HOT301"
moosh cohort-create -d "50010314-COM201" -i 50010314-COM201 -c "${ID_CATEGORY_le}" "50010314-COM201"
moosh cohort-create -d "50010314-COM301" -i 50010314-COM301 -c "${ID_CATEGORY_le}" "50010314-COM301"
moosh cohort-create -d "50010314-COM302" -i 50010314-COM302 -c "${ID_CATEGORY_le}" "50010314-COM302"
moosh cohort-create -d "50010314-COM303" -i 50010314-COM303 -c "${ID_CATEGORY_le}" "50010314-COM303"
moosh cohort-create -d "50010314-IFC201" -i 50010314-IFC201 -c "${ID_CATEGORY_le}" "50010314-IFC201"
moosh cohort-create -d "50010314-IFC303" -i 50010314-IFC303 -c "${ID_CATEGORY_le}" "50010314-IFC303"
moosh cohort-create -d "50010314-IMS302" -i 50010314-IMS302 -c "${ID_CATEGORY_le}" "50010314-IMS302"
moosh cohort-create -d "50010511-ADG201" -i 50010511-ADG201 -c "${ID_CATEGORY_tm}" "50010511-ADG201"
moosh cohort-create -d "50018829-ADG301" -i 50018829-ADG301 -c "${ID_CATEGORY_ca}" "50018829-ADG301"
moosh cohort-create -d "50018829-ADG302" -i 50018829-ADG302 -c "${ID_CATEGORY_ca}" "50018829-ADG302"
moosh cohort-create -d "50018829-QUI301" -i 50018829-QUI301 -c "${ID_CATEGORY_ca}" "50018829-QUI301"

#############################################################################################
# Añado a la cohorte de jefatura de estudios a los diferentes usuarios de jefes de estudios
#############################################################################################
echo "Adding jefatura users to cohort jefaturas..."

moosh cohort-enrol -u "${JE_SG_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_SE_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_TM_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_LE_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_CA_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_PI_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_SB_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_MI_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_PS_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_BA_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_RG_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_VT_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_LB_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_MO_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_MV_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_AV_USER_ID}" "jefaturas"
moosh cohort-enrol -u "${JE_MM_USER_ID}" "jefaturas"


#############################################################################################
# Creo los cursos intentando restaurar su contenido
#############################################################################################

# IMPORTANTE (Lee abajo)
# IMPORTANTE (Lee abajo)
# IMPORTANTE (Lee abajo)
# La siguiente lista de cursos NO puede ser modificada en su orden. Si un curso desaparece se cambiará 
# el 1 del final por un 0. Si se añaden nuevos cursos se añadirán al final, nunca 
# junto a los de su centro o estudio pues eso cambiaría el orden
# IMPORTANTE (Lee arriba)
# IMPORTANTE (Lee arriba)
# IMPORTANTE (Lee arriba)

# "category*shortname*fullname*visible"
COURSES=( 
    "ID_CATEGORY_cd_smr*50020125-IFC201-627t*SMR - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-5351*SMR - Redes locales*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-5349*SMR - Aplicaciones ofimáticas*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-5348*SMR - Sistemas operativos monopuesto*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-5347*SMR - Montaje y mantenimiento de equipos*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-4998*SMR - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-4995*SMR - Aplicaciones web*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-4994*SMR - Servicios en red*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-4993*SMR - Seguridad informática*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-4991*SMR - Sistemas operativos en red*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16724*SMR - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16715*SMR - Proyecto intermodular*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16713*SMR - Módulo profesional optativo*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16711*SMR - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16697*SMR - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16695*SMR - Inglés profesional (GM)*1"
    "ID_CATEGORY_cd_smr*50020125-IFC201-16693*SMR - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-643t*ASIR - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5276*ASIR - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5275*ASIR - Gestión de bases de datos*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5274*ASIR - Fundamentos de hardware*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5273*ASIR - Planificación y administración de redes*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5272*ASIR - Implantación de sistemas operativos*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5059*ASIR - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5056*ASIR - Proyecto de administración de sistemas informáticos en red LOE*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5055*ASIR - Seguridad y alta disponibilidad*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5054*ASIR - Administración de sistemas gestores de bases de datos*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5053*ASIR - Implantación de aplicaciones web*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5052*ASIR - Servicios de red e Internet*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-5051*ASIR - Administración de sistemas operativos*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16762*ASIR - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16756*ASIR - Proyecto intermodular de administración de sistemas informáticos en red*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16754*ASIR - Módulo profesional optativo*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16752*ASIR - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16739*ASIR - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16737*ASIR - Inglés profesional*1"
    "ID_CATEGORY_cd_asir*50020125-IFC301-16728*ASIR - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-681t*DAM - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5293*DAM - Entornos de desarrollo*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5291*DAM - Programación*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5290*DAM - Bases de datos*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5289*DAM - Sistemas informáticos*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5288*DAM - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5075*DAM - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5072*DAM - Proyecto de desarrollo de aplicaciones multiplataforma LOE*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5071*DAM - Sistemas de gestión empresarial*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5070*DAM - Programación de servicios y procesos*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5069*DAM - Programación multimedia y dispositivos móviles*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5068*DAM - Desarrollo de interfaces*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-5066*DAM - Acceso a datos*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16801*DAM - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16796*DAM - Proyecto intermodular de desarrollo de aplicaciones multiplataforma*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16789*DAM - Módulo profesional optativo*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16787*DAM - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16773*DAM - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16771*DAM - Inglés profesional*1"
    "ID_CATEGORY_cd_dam*50020125-IFC302-16767*DAM - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-682t*DAW - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5182*DAW - Entornos de desarrollo*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5181*DAW - Programación*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5180*DAW - Bases de datos*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5179*DAW - Sistemas informáticos*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5178*DAW - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5090*DAW - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5087*DAW - Proyecto de desarrollo de aplicaciones web LOE*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5086*DAW - Diseño de interfaces web*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5085*DAW - Despliegue de aplicaciones web*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5084*DAW - Desarrollo web  en entorno servidor*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-5083*DAW - Desarrollo web  en entorno cliente*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16835*DAW - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16833*DAW - Proyecto intermodular de desarrollo de aplicaciones web*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16831*DAW - Módulo profesional optativo*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16829*DAW - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16811*DAW - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16809*DAW - Inglés profesional*1"
    "ID_CATEGORY_cd_daw*50020125-IFC303-16805*DAW - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-873t*CETI - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14344*CETI - Normativa de ciberseguridad*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14343*CETI - Hacking ético*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14342*CETI - Análisis forense informático*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14341*CETI - Puesta en producción segura*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14340*CETI - Bastionado de redes y sistemas*1"
    "ID_CATEGORY_cd_ceti*50020125-CESIFC01-14339*CETI - Incidentes de ciberseguridad*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-866t*IABD - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-14349*IABD - Programación de Inteligencia Artificial*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-14348*IABD - Sistemas de aprendizaje automático*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-14347*IABD - Big Data aplicado*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-14346*IABD - Sistemas de Big Data*1"
    "ID_CATEGORY_cd_iabd*50020125-CESIFC02-14345*IABD - Modelos de Inteligencia Artificial*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-19541*RSN - Fundamentos y configuración inicial de servicios en la nube*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-19539*RSN - Despliegue de servicios administrados en la nube*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-19537*RSN - Administración de redes en la nube*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-19535*RSN - Administración de recursos de computación en la nube*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-19533*RSN - Administración de bases de datos y almacenamiento en la nube*1"
    "ID_CATEGORY_cd_rsn*50020125-CESIFC04-1052t*RSN - Coordinación - Tutoría*1"
    "ID_CATEGORY_cd_dalp*50020125-CESIFC05-19549*DALP - Programación orientada a objetos*1"
    "ID_CATEGORY_cd_dalp*50020125-CESIFC05-19547*DALP - Estructuras de control en Python*1"
    "ID_CATEGORY_cd_dalp*50020125-CESIFC05-19545*DALP - Entornos y sintaxis en Python*1"
    "ID_CATEGORY_cd_dalp*50020125-CESIFC05-19543*DALP - Análisis de datos con Python*1"
    "ID_CATEGORY_cd_dalp*50020125-CESIFC05-1053t*DALP - Coordinación - Tutoría*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-681t*DAM - Coordinación - Tutoría*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5293*DAM - Entornos de desarrollo*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5291*DAM - Programación*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5290*DAM - Bases de datos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5289*DAM - Sistemas informáticos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5288*DAM - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5075*DAM - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5072*DAM - Proyecto de desarrollo de aplicaciones multiplataforma LOE*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5071*DAM - Sistemas de gestión empresarial*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5070*DAM - Programación de servicios y procesos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5069*DAM - Programación multimedia y dispositivos móviles*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5068*DAM - Desarrollo de interfaces*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5066*DAM - Acceso a datos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16801*DAM - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16796*DAM - Proyecto intermodular de desarrollo de aplicaciones multiplataforma*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16789*DAM - Módulo profesional optativo*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16787*DAM - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16773*DAM - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16771*DAM - Inglés profesional*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-16767*DAM - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7872*AD - Proyecto de asistencia a la dirección LOE*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7871*AD - Gestión avanzada de la información*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7870*AD - Organización de eventos empresariales*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7869*AD - Protocolo empresarial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7863*AD - Segunda lengua extranjera: Francés*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7861*AD - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7855*AD - Comunicación y atención al cliente*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7854*AD - Proceso integral de la actividad comercial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7853*AD - Ofimática y proceso de la información*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7852*AD - Recursos humanos y responsabilidad social corporativa*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7851*AD - Gestión de la documentación jurídica y empresarial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-750t*AD - Coordinación - Tutoría*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14743*AD - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14739*AD - Proyecto intermodular de asistencia a la dirección*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14732*AD - Módulo profesional optativo*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14730*AD - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14719*AD - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14717*AD - Inglés profesional*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-14713*AD - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-79t*AF - Coordinación - Tutoría*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5297*AF - Comunicación y atención al cliente*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5296*AF - Proceso integral de la actividad comercial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5295*AF - Ofimática y proceso de la información*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5294*AF - Recursos humanos y responsabilidad social corporativa*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5194*AF - Gestión de la documentación jurídica y empresarial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5152*AF - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5150*AF - Proyecto de administración y finanzas LOE*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5149*AF - Simulación empresarial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5148*AF - Gestión logística y comercial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5101*AF - Contabilidad y fiscalidad*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5100*AF - Gestión financiera*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5099*AF - Gestión de recursos humanos*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14709*AF - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14704*AF - Proyecto intermodular de administración y finanzas*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14702*AF - Módulo profesional optativo*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14700*AF - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14681*AF - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14679*AF - Inglés profesional*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-14675*AF - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5260*LACC - Ensayos microbiológicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5259*LACC - Ensayos fisicoquímicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5256*LACC - Análisis químicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5255*LACC - Muestreo y preparación de la muestra*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5042*LACC - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5039*LACC - Proyecto de laboratorio de análisis y de control de calidad LOE*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5036*LACC - Calidad y seguridad en el laboratorio Global*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5035*LACC - Ensayos biotecnológicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5032*LACC - Ensayos físicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5031*LACC - Análisis instrumental*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17909*LACC - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17907*LACC - Proyecto intermodular de laboratorio de análisis y de control de calidad*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17905*LACC - Módulo profesional optativo*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17903*LACC - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17889*LACC - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17887*LACC - Inglés profesional*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-17881*LACC - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-122t*LACC - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_ac*50010314-COM201-700t*AC - Coordinación  - Tutoría*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15365*AC - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15360*AC - Proyecto intermodular*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15358*AC - Módulo profesional optativo*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15356*AC - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15344*AC - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15342*AC - Inglés profesional (GM)*1"
    "ID_CATEGORY_le_ac*50010314-COM201-15335*AC - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13954*AC - Comercio electrónico*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13953*AC - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13952*AC - Gestión de un pequeño comercio*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13951*AC - Servicios de atención comercial*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13950*AC - Técnicas de almacén*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13949*AC - Venta técnica*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13948*AC - Aplicaciones informáticas para el comercio*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13947*AC - Dinamización del punto de venta*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13945*AC - Gestión de compras*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13943*AC - Marketing en la actividad comercial*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13942*AC - Procesos de venta*1"
    "ID_CATEGORY_le_ci*50010314-COM301-83t*CI - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5409*CI - Gestión administrativa del comercio internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5408*CI - Logística de almacenamiento*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5407*CI - Gestión económica y financiera de la empresa*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5406*CI - Transporte internacional de mercancías*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5166*CI - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5164*CI - Proyecto de comercio internacional LOE*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5163*CI - Comercio digital internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5162*CI - Medios de pago internacionales*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5161*CI - Financiación internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5160*CI - Negociación internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5159*CI - Marketing internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5158*CI - Sistema de información de mercados*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15407*CI - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15403*CI - Proyecto intermodular de comercio internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15399*CI - Módulo profesional optativo*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15392*CI - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15382*CI - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15380*CI - Inglés profesional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-15373*CI - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7928*GVEC - Proyecto de gestión de ventas y espacios comerciales LOE*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7926*GVEC - Investigación comercial*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7925*GVEC - Logística de aprovisionamiento*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7920*GVEC - Técnicas de venta y negociación*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7919*GVEC - Organización de equipos de ventas*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7918*GVEC - Gestión de productos y promociones en el punto de venta*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7917*GVEC - Escaparatismo y diseño de espacios comerciales*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7916*GVEC - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7910*GVEC - Logística de almacenamiento*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7909*GVEC - Gestión económica y financiera de la empresa*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7908*GVEC - Marketing digital*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7907*GVEC - Políticas de marketing*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-738t*GVEC - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15441*GVEC - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15439*GVEC - Proyecto intermodular de gestión de ventas y espacios comerciales*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15435*GVEC - Módulo profesional optativo*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15428*GVEC - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15418*GVEC - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15414*GVEC - Inglés profesional*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-15409*GVEC - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_le_tl*50010314-COM303-85t*TL - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5426*TL - Gestión administrativa del comercio internacional*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5424*TL - Logística de almacenamiento*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5422*TL - Gestión económica y financiera de la empresa de transporte y logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5421*TL - Transporte internacional de mercancías*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5206*TL - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5204*TL - Proyecto de transporte y logística LOE*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5203*TL - Organización del transporte de mercancías*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5202*TL - Organización del transporte de viajeros*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5200*TL - Logística de aprovisionamiento*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5198*TL - Comercialización del transporte y la logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5195*TL - Gestión administrativa del transporte y la logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15479*TL - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15477*TL - Proyecto intermodular de transporte y logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15470*TL - Módulo profesional optativo*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15465*TL - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15454*TL - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15452*TL - Inglés profesional*1"
    "ID_CATEGORY_le_tl*50010314-COM303-15445*TL - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7951*PAE - Proyecto de producción de audiovisuales y espectáculos LOE*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7950*PAE - Administración y promoción de audiovisuales y espectáculos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7948*PAE - Gestión de proyectos de espectáculos y eventos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7946*PAE - Gestión de proyectos de televisión y radio*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7945*"PAE - Gestión de proyectos de cine* video y multimedia"*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7940*PAE - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7935*PAE - Recursos expresivos audiovisuales y escénicos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7933*PAE - Planificación de proyectos de espectáculos y eventos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7930*PAE - Planificación de proyectos audiovisuales*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7929*PAE - Medios técnicos audiovisuales y escénicos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-745t*PAE - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17377*PAE - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17375*PAE - Proyecto intermodular de producción de audiovisuales y espectáculos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17373*PAE - Módulo profesional optativo*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17371*PAE - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17353*PAE - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17351*PAE - Inglés profesional*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-17349*PAE - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-627t*SMR - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5351*SMR - Redes locales*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5349*SMR - Aplicaciones ofimáticas*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5348*SMR - Sistemas operativos monopuesto*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5347*SMR - Montaje y mantenimiento de equipos*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4998*SMR - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4995*SMR - Aplicaciones Web*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4994*SMR - Servicios en red*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4993*SMR - Seguridad informática*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4991*SMR - Sistemas operativos en red*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16724*SMR - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16715*SMR - Proyecto intermodular*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16713*SMR - Módulo profesional optativo*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16711*SMR - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16697*SMR - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16695*SMR - Inglés profesional (GM)*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-16693*SMR - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-682t*DAW - Coordinación - Tutoría*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5182*DAW - Entornos de desarrollo*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5181*DAW - Programación*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5180*DAW - Bases de datos*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5179*DAW - Sistemas informáticos*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5178*DAW - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5090*DAW - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5087*DAW - Proyecto de desarrollo de aplicaciones Web LOE*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5086*DAW - Diseño de interfaces Web*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5085*DAW - Despliegue de aplicaciones web*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5084*DAW - Desarrollo web  en entorno servidor*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5083*DAW - Desarrollo web  en entorno cliente*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16835*DAW - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16833*DAW - Proyecto intermodular de desarrollo de aplicaciones web*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16831*DAW - Módulo profesional optativo*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16829*DAW - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16811*DAW - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16809*DAW - Inglés profesional*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-16805*DAW - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-624t*IEA - Coordinación - Tutoría*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5338*IEA - Instalaciones eléctricas interiores.*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5337*IEA - Electrotecnia*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5335*IEA - Automatismos industriales*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4987*IEA - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4984*IEA - Máquinas eléctricas*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4982*IEA - Instalaciones domóticas.*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4981*IEA - Infraestructuras comunes de telecomunicaciones en viviendas y edificios*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4980*IEA - Instalaciones de distribución*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15596*IEA - sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15594*IEA - Proyecto intermodular*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15592*IEA - Módulo profesional optativo*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15588*IEA - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15575*IEA - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15571*IEA - Inglés profesional (GM)*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-15564*IEA - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-12360*IEA - Instalaciones solares fotovoltaicas*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-12359*IEA - Electrónica*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-757t*ECA - Coordinación - Tutoría*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18409*ECA - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18407*ECA - Proyecto intermodular de educación y control ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18405*ECA - Módulo profesional optativo*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18403*ECA - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18387*ECA - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18385*ECA - Inglés profesional*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-18379*ECA - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12353*ECA - Técnicas de educación ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12352*ECA - Proyecto de educación y control ambiental LOE*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12350*ECA - Habilidades sociales*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12349*ECA - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12347*ECA - Desenvolvimiento en el medio*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12346*ECA - Actividades humanas y problemática ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12345*ECA - Actividades de uso público*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12344*ECA - Programas de educación ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12343*ECA - Métodos y productos cartográficos*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12342*ECA - Medio natural*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12339*ECA - Estructura y dinámica del medio ambiente*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12338*ECA - Gestión ambiental*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5441*EI - Primeros auxilios*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5436*EI - Desarrollo cognitivo y motor*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5434*EI - El juego infantil y su metodología*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5433*EI - Autonomía personal y salud infantil*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5432*EI - Didáctica de la Educación Infantil*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5219*EI - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5215*EI - Proyecto de atención a la infancia LOE*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5214*EI - Intervención con familias y atención a menores en riesgo social*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5213*EI - Habilidades sociales*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5212*EI - Desarrollo socioafectivo*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5210*EI - Expresión y comunicación*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18619*EI - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18617*EI - Proyecto intermodular de atención a la infancia*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18615*EI - Módulo profesional optativo*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18613*EI - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18599*EI - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18597*EI - Inglés profesional*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-18593*EI - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-140t*EI - Coordinación - Tutoría*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-687t*APSD - Coordinación - Tutoría*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5382*APSD - Atención sanitaria*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5381*APSD - Apoyo domiciliario*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5379*APSD - Atención y apoyo psicosocial*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5378*APSD - Características y necesidades de las personas en situación de dependencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5375*APSD - Primeros auxilios*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5135*APSD - Teleasistencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5134*APSD - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5131*APSD - Atención higiénica*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5128*APSD - Apoyo a la comunicación*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5125*APSD - Destrezas sociales*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5124*APSD - Organización de la atención a las personas en situación de dependencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18526*APSD - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18524*APSD - Proyecto intermodular*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18520*APSD - Módulo profesional optativo*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18518*APSD - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18506*APSD - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18504*APSD - Inglés profesional (GM)*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-18502*APSD - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7901*IS - Proyecto de integración social LOE*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7899*IS - Habilidades sociales*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7897*IS - Metodología de la intervención social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7896*IS - Sistemas aumentativos y alternativos de comunicación*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7892*IS - Atención a las unidades de convivencia*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7889*IS - Formación en centros de trabajo FCT*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7882*IS - Primeros auxilios*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7879*IS - Promoción de la autonomía personal*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7878*IS - Apoyo a la intervención educativa*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7877*IS - Mediación comunitaria*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7875*IS - Inserción sociolaboral*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7874*IS - Contexto de la intervención social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-768t*IS - Coordinación - Tutoría*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18653*IS - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18649*IS - Proyecto intermodular de integración social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18647*IS - Módulo profesional optativo*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18643*IS - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18631*IS - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18627*IS - Inglés profesional*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-18623*IS - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5441*EI - Primeros auxilios*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5436*EI - Desarrollo cognitivo y motor*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5434*EI - El juego infantil y su metodología*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5433*EI - Autonomía personal y salud infantil*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5432*EI - Didáctica de la Educación Infantil*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5219*EI - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5215*EI - Proyecto de atención a la infancia LOE*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5214*EI - Intervención con familias y atención a menores en riesgo social*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5213*EI - Habilidades sociales*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5212*EI - Desarrollo socioafectivo*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5210*EI - Expresión y comunicación*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18619*EI - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18617*EI - Proyecto intermodular de atención a la infancia*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18615*EI - Módulo profesional optativo*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18613*EI - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18599*EI - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18597*EI - Inglés profesional*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-18593*EI - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-140t*EI - Coordinación - Tutoría*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-645t*AVGE - Coordinación - Tutoría*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5463*AVGE - Dirección de entidades de intermediación turística*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5457*AVGE - Recursos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5456*AVGE - Destinos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5447*AVGE - Estructura del mercado turístico*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5239*AVGE - Proyecto de agencias de viajes y gestión de eventos LOE*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5237*AVGE - Venta de servicios turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5236*AVGE - Gestión de productos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5235*AVGE - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5228*AVGE - Segunda lengua extranjera: Francés Global*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5224*AVGE - Marketing turístico*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5223*AVGE - Protocolo y relaciones públicas*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16488*AVGE - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16484*AVGE - Proyecto intermodular de agencias de viajes y gestión de eventos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16482*AVGE - Módulo profesional optativo*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16478*AVGE - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16470*AVGE - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16468*AVGE - Inglés profesional*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-16462*AVGE - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-643t*ASIR - Coordinación - Tutoría*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5276*ASIR - Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5275*ASIR - Gestión de bases de datos*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5274*ASIR - Fundamentos de hardware*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5273*ASIR - Planificación y administración de redes*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5272*ASIR - Implantación de sistemas operativos*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5059*ASIR - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5056*ASIR - Proyecto de administración de sistemas informáticos en red LOE*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5055*ASIR - Seguridad y alta disponibilidad*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5054*ASIR - Administración de sistemas gestores de bases de datos*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5053*ASIR - Implantación de aplicaciones web*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5052*ASIR - Servicios de red e Internet*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5051*ASIR - Administración de sistemas operativos*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16762*ASIR - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16756*ASIR - Proyecto intermodular de administración de sistemas informáticos en red*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16754*ASIR - Módulo profesional optativo*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16752*ASIR - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16739*ASIR - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16737*ASIR - Inglés profesional*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-16728*ASIR - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-630t*FP - Coordinación - Tutoría*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5329*FP - Operaciones básicas de laboratorio*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5327*FP - Dispensación de productos farmacéuticos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5326*FP - Oficina de Farmacia*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5325*FP - Disposición y venta de productos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5324*FP - Anatomofisiología  y Patología básicas*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5323*FP - Primeros auxilios*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4975*FP - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4972*FP - Promoción de la salud*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4971*FP - Formulación magistral*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4969*FP - Dispensación de productos parafarmacéuticos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-18015*FP - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-18013*FP - Proyecto intermodular*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-18009*FP - Módulo profesional optativo*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-18007*FP - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-17991*FP - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-17989*FP - Inglés profesional*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-17983*FP - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-618t*ES - Coordinación - Tutoría*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5319*ES - Anatomofisiología y patología básicas*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5316*ES - Apoyo psicológico en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5315*ES - Evacuación y traslado de pacientes*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5313*ES - Atención sanitaria inicial en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5312*ES - Dotación sanitaria*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5310*ES - Mantenimiento mecánico preventivo del vehículo*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4963*ES - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4959*ES - Tele emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4958*ES - Planes de emergencia y dispositivos de riesgos previsibles*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4955*ES - Atención sanitaria especial en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4952*ES - Logística sanitaria en emergencias*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18052*ES - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18050*ES - Proyecto intermodular*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18046*ES - Módulo profesional optativo*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18042*ES - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18034*ES - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18032*ES - Inglés profesional*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-18025*ES - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-97t*STI - Coordinación  - Tutoría*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15786*STI - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15778*STI - Proyecto intermodular de sistemas de telecomunicaciones e informáticos*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15776*STI - Módulo profesional optativo*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15774*STI - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15766*STI - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15764*STI - Inglés profesional*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-15758*STI - Digitalización aplicada a los sectores productivos (GS)*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13941*STI - Proyecto de sistemas de telecomunicaciones e informáticos LOE*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13940*STI - Sistemas integrados y hogar digital*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13939*STI - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13937*STI - Redes telemáticas*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13936*STI - Sistemas de producción audiovisual*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13935*STI - Sistemas de radiocomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13933*STI - Técnicas y procesos en infraestructuras de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13932*STI - Configuración de infraestructuras de sistemas de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13930*STI - Sistemas de telefonía fija y móvil*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13929*STI - Elementos de sistemas de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13928*STI - Sistemas informáticos y redes locales*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13926*STI - Gestión de proyectos de instalaciones de telecomunicaciones*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-639t*GA - Coordinación - Tutoría*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5368*GA - Técnica contable*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5367*GA - Tratamiento informático de la información*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5365*GA - Operaciones administrativas de compra - venta*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5364*GA - Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5122*GA - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5120*GA - Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5119*GA - Empresa en el aula*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5118*GA - Tratamiento de la documentación contable*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5117*GA - Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5114*GA - Empresa y Administración*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14665*GA - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14663*GA - Proyecto intermodular*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14657*GA - Módulo profesional optativo*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14655*GA - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14642*GA - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14640*GA - Inglés profesional (GM)*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-14636*GA - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-639t*GA - Coordinación - Tutoría*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5368*GA - Técnica contable*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5367*GA - Tratamiento informático de la información*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5365*GA - Operaciones administrativas de compra - venta*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5364*GA - Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5122*GA - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5120*GA - Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5119*GA - Empresa en el aula*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5118*GA - Tratamiento de la documentación contable*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5117*GA - Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5114*GA - Empresa y Administración*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14665*GA - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14663*GA - Proyecto intermodular*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14657*GA - Módulo profesional optativo*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14655*GA - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14642*GA - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14640*GA - Inglés profesional (GM)*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-14636*GA - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-873t*CETI - Coordinación - Tutoría*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14344*CETI - Normativa de ciberseguridad.*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14343*CETI - Hacking ético*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14342*CETI - Análisis forense informático*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14341*CETI - Puesta en producción segura*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14340*CETI - Bastionado de redes y sistemas*1"
    "ID_CATEGORY_sg_ceti*22002521-CESIFC01-14339*CETI - Incidentes de ciberseguridad*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-639t*GA - Coordinación - Tutoría*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5368*GA - Técnica contable*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5367*GA - Tratamiento informático de la información*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5365*GA - Operaciones administrativas de compra - venta*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5364*GA - Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5122*GA - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5120*GA - Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5119*GA - Empresa en el aula*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5118*GA - Tratamiento de la documentación contable*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5117*GA - Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5114*GA - Empresa y Administración*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14663*GA - Proyecto intermodular*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14657*GA - Módulo profesional optativo*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14655*GA - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14642*GA - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14640*GA - Inglés profesional (GM)*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-14636*GA - Digitalización aplicada a los sectores productivos (GM)*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-618t*ES - Coordinación - Tutoría*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5319*ES - Anatomofisiología y patología básicas*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5316*ES - Apoyo psicológico en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5315*ES - Evacuación y traslado de pacientes*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5313*ES - Atención sanitaria inicial en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5312*ES - Dotación sanitaria*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5310*ES - Mantenimiento mecánico preventivo del vehículo*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4963*ES - Formación en centros de trabajo LOE*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4959*ES - Tele emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4958*ES - Planes de emergencia y dispositivos de riesgos previsibles*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4955*ES - Atención sanitaria especial en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4952*ES - Logística sanitaria en emergencias*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18052*ES - Sostenibilidad aplicada al sistema productivo*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18050*ES - Proyecto intermodular*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18046*ES - Módulo profesional optativo*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18042*ES - IPE II - Itinerario personal para la empleabilidad II*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18034*ES - IPE I - Itinerario personal para la empleabilidad I*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18032*ES - Inglés profesional*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-18025*ES - Digitalización aplicada a los sectores productivos (GM)*1"
)

echo "***** Processing courses..."
for COURSE in "${COURSES[@]}"
do
    echo "***** Processing line ${COURSE}"
    CATEGORY=$(echo "${COURSE}" | cut -d '*' -f 1)
    SHORTNAME=$(echo "${COURSE}" | cut -d '*' -f 2)
    FULLNAME=$(echo "${COURSE}" | cut -d '*' -f 3)
    VISIBLE=$(echo "${COURSE}" | cut -d '*' -f 4)
    echo "CATEGORY '${CATEGORY}' - SHORTNAME '${SHORTNAME}' - FULLNAME '${FULLNAME}' - VISIBLE '${VISIBLE}'"
    COURSE_ID=""
    
    if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz" ]; then
        # Si no existe el curso, lo creo
        echo "***** The course /var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz doesn't exist, creating empty course ${COURSE} into category ${CATEGORY}"
        COURSE_ID=$(moosh course-create --category "${!CATEGORY}" --fullname "${FULLNAME}" --description "${FULLNAME}" "${SHORTNAME}" | grep -o '[0-9]*' | tail -1)
        moosh course-config-set course "${COURSE_ID}" fullname "${FULLNAME}"
    else
        # Si existe el curso lo restauro
        echo "***** Restoring /var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz course to category ${CATEGORY}"
        RESTORE_OUTPUT=$(moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz "${!CATEGORY}")
        COURSE_ID=$(echo "${RESTORE_OUTPUT}" | grep "^Restoring" | sed 's/.*): //' | cut -d',' -f1)
        # Configuro full y short names por si al restaurar había datos erróneos en origen
        moosh course-config-set course "${COURSE_ID}" shortname "${SHORTNAME}"
        moosh course-config-set course "${COURSE_ID}" fullname "${FULLNAME}"
    fi
    moosh course-config-set course "${COURSE_ID}" visible "${VISIBLE}"
    # TODO: valorar si los que no son visible los borro una vez creados <- verificar no afecta a los IDs

    # matriculo en el curso de ayuda a las cohortes alumnado, profesorado, coordinacion y jefaturas
    if [[ ${SHORTNAME} == 'ayuda' ]]; 
    then
        COHORT=$(echo "${SHORTNAME}" | cut -d '-' -f 1,2)
        echo "****** Enrolling the cohorts alumnado, profesorado, coordinacion and jefaturas into the course_id ${COURSE_ID}"
        moosh cohort-enrol -c "${COURSE_ID}" "alumnado"
        moosh cohort-enrol -c "${COURSE_ID}" "profesorado"
        moosh cohort-enrol -c "${COURSE_ID}" "coordinacion"
        moosh cohort-enrol -c "${COURSE_ID}" "jefaturas"
    fi

    # matriculo en el curso de profesorado a las cohortes profesorado, coordinacion y jefaturas
    if [[ ${SHORTNAME} == 'profesorado' ]]; 
    then
        COHORT=$(echo "${SHORTNAME}" | cut -d '-' -f 1,2)
        echo "****** Enrolling the cohorts profesorado, coordinacion and jefaturas into the course_id ${COURSE_ID}"
        moosh cohort-enrol -c "${COURSE_ID}" "profesorado"
        moosh cohort-enrol -c "${COURSE_ID}" "coordinacion"
        moosh cohort-enrol -c "${COURSE_ID}" "jefaturas"
    fi

    # matriculo en el curso de coordinacion a las cohortes coordinacion y jefaturas
    if [[ ${SHORTNAME} == 'coordinacion' ]]; 
    then
        COHORT=$(echo "${SHORTNAME}" | cut -d '-' -f 1,2)
        echo "****** Enrolling the cohorts coordinacion and jefaturas into the course_id ${COURSE_ID}"
        moosh cohort-enrol -c "${COURSE_ID}" "coordinacion"
        moosh cohort-enrol -c "${COURSE_ID}" "jefaturas"
    fi

    # matriculo en el curso de marketplaces a los usuarios que nos piden desde la app
    if [[ ${SHORTNAME} == 'marketplaces' ]]; 
    then
        COHORT=$(echo "${SHORTNAME}" | cut -d '-' -f 1,2)
        echo "****** Creating and enrolling the users for marketplaces into the course_id ${COURSE_ID}"
        FPD_APP_USER_STUDENT_ID=$(moosh user-create --password "${APP_PASSWORD}" --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname student --lastname demoapp demoapp | grep -o '[0-9]*' | tail -1)
        FPD_APP_USER_TEACHER_ID=$(moosh user-create --password "${APP_TEACHER_PASSWORD}" --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname teacher --lastname demoapp profesor1 | grep -o '[0-9]*' | tail -1)

        moosh course-enrol -r editingteacher -i "${COURSE_ID}" "${FPD_APP_USER_TEACHER_ID}"
        moosh course-enrol -r student -i "${COURSE_ID}" "${FPD_APP_USER_STUDENT_ID}"
    fi

    # si el cod_ensenanza contiene una t al final (es una tutoría) entonces matriculo a la cohorte en ese curso
    if [[ ${SHORTNAME} == *t ]]; 
    then
        COHORT=$(echo "${SHORTNAME}" | cut -d '-' -f 1,2)
        echo "****** Enrolling the cohort ${COHORT} into the course_id ${COURSE_ID}"
        moosh cohort-enrol -c "${COURSE_ID}" "${COHORT}"
    fi

    # Matricular a jefes de estudios en los cursos en base al ID centro del shortname
    if [[ ${SHORTNAME} == *-*-* ]];
    then
        CODCENTRO=$(echo "${SHORTNAME}" | cut -d '-' -f 1)
        case "${CODCENTRO}" in
            "22002521") # IES Sierra de Guara
                echo "****** Enrolling the user ${JE_SG_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_SG_USER_ID}"
                ;;
            "44003211") # IES SANTA EMERENCIANA
                echo "****** Enrolling the user ${JE_SE_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_SE_USER_ID}"
                ;;
            "50010511") # IES TIEMPOS MODERNOS
                echo "****** Enrolling the user ${JE_TM_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_TM_USER_ID}"
                ;;
            "50010314") # CPIFP LOS ENLACES
                echo "****** Enrolling the user ${JE_LE_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_LE_USER_ID}"
                ;;
            "50018829") # CPIFP CORONA DE ARAGÓN
                echo "****** Enrolling the user ${JE_CA_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_CA_USER_ID}"
                ;;
            "22010712") # CPIFP PIRÁMIDE
                echo "****** Enrolling the user ${JE_PI_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_PI_USER_ID}"
                ;;
            "44003028") # CPIFP SAN BLAS
                echo "****** Enrolling the user ${JE_SB_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_SB_USER_ID}"
                ;;
            "50010156") # IES MIRALBUENO
                echo "****** Enrolling the user ${JE_MI_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_MI_USER_ID}"
                ;;
            "50010144") # IES PABLO SERRANO
                echo "****** Enrolling the user ${JE_PS_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_PS_USER_ID}"
                ;;
            "44010537") # CPIFP BAJO ARAGÓN
                echo "****** Enrolling the user ${JE_BA_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_BA_USER_ID}"
                ;;
            "50009567") # IES RÍO GÁLLEGO
                echo "****** Enrolling the user ${JE_RG_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_RG_USER_ID}"
                ;;
            "44003235") # IES VEGA DEL TURIA
                echo "****** Enrolling the user ${JE_VT_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_VT_USER_ID}"
                ;;
            "50008460") # IES LUIS BUÑUEL
                echo "****** Enrolling the user ${JE_LB_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_LB_USER_ID}"
                ;;
            "22002491") # CPIFP MONTEARAGON
                echo "****** Enrolling the user ${JE_MO_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_MO_USER_ID}"
                ;;
            "22004611") # IES MARTÍNEZ VARGAS
                echo "****** Enrolling the user ${JE_MV_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_MV_USER_ID}"
                ;;
            "50009348") # IES AVEMPACE
                echo "****** Enrolling the user ${JE_AV_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_AV_USER_ID}"
                ;;
            "50008642") # IES MARÍA MOLINER
                echo "****** Enrolling the user ${JE_MM_USER_ID} into the course_id ${COURSE_ID} with role jefatura-estudios"
                moosh course-enrol -r jefatura-estudios -i "${COURSE_ID}" "${JE_MM_USER_ID}"
                ;;
        esac
    fi
done

echo >&2 "... importing categories and courses. Done!"