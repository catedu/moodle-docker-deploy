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
FPD_ADMIN_USER_ID=$(moosh user-create --password "${FPD_PASSWORD}" --email "${FPD_EMAIL}" --digest 2 --city Aragón --country ES --firstname fp --lastname distancia admin2)
moosh config-set siteadmins 2,"${FPD_ADMIN_USER_ID}"

# Crear rol y usuario de inspección
echo "Creating inspeccion role and configuring it..."
INSPECCION_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -a manager -n "Inspeccion" inspeccion)

# set permissions to inspeccion role
moosh role-import -f /init-scripts/themes/fpdist/roles/role-inspeccion.xml

# Creating user
INSPECCION_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email inspeccion@educa.aragon.es --digest 2 --city Aragón --country ES --firstname Inspección --lastname Inspección profinspector)

# Assiging user to r
moosh user-assign-system-role profinspector inspeccion

# Crear rol de jefaturas y usuarios
echo "Creating jefatura-estudios role and configuring it..."
JEFATURA_ROLE_ID=$(moosh role-create -d "Los usuarios con rol de inspección tienen acceso a determinados informes" -c system,category,course,block -n "Jefatura de estudios" jefatura-estudios)

# Setting permissions to jefatura de estudios role
moosh role-import -f /init-scripts/themes/fpdist/roles/role-jefatura-estudios.xml

# Creating users
JE_SG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES Sierra de Guara" prof_je_sg)
JE_SE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES SANTA EMERENCIANA" prof_je_se)
JE_TM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES TIEMPOS MODERNOS" prof_je_tm)
JE_LE_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP LOS ENLACES" prof_je_le)
JE_CA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP CORONA DE ARAGÓN" prof_je_ca)
JE_PI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP PIRÁMIDE" prof_je_pi)
JE_SB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP SAN BLAS" prof_je_sb)
JE_MI_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MIRALBUENO" prof_je_mi)
JE_PS_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES PABLO SERRANO" prof_je_ps)
JE_BA_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP BAJO ARAGÓN" prof_je_ba)
JE_RG_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES RÍO GÁLLEGO" prof_je_rg)
JE_VT_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES VEGA DEL TURIA" prof_je_vt)
JE_LB_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES LUIS BUÑUEL" prof_je_lb)
JE_MO_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "CPIFP MONTEARAGON" prof_je_mo)
JE_MV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARTÍNEZ VARGAS" prof_je_mv)
JE_AV_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES AVEMPACE" prof_je_av)
JE_MM_USER_ID=$(moosh user-create --password "${MANAGER_PASSWORD}" --email jefaturas@educa.aragon.es --digest 2 --city Aragón --country ES --firstname "Jefatura de estudios" --lastname "IES MARÍA MOLINER" prof_je_mm)

#############################################################################################
# Creo las categorías:
#############################################################################################
echo "Creating structure for categories..."

ID_CATEGORY_miscelanea=1
ID_CATEGORY_general=$(moosh category-create -p 0 -v 1 -d "general" "General")
ID_CATEGORY_app=$(moosh category-create -p 0 -v 1 -d "app" "NO BORRAR - APP MOVIL")

ID_CATEGORY_sg=$(moosh category-create -p 0 -v 1 -d "22002521" "IES SIERRA DE GUARA")
ID_CATEGORY_sg_ga=$(moosh category-create -p "${ID_CATEGORY_sg}" -v 1 -d "ADG201" "Gestión Administrativa")

ID_CATEGORY_se=$(moosh category-create -p 0 -v 1 -d "44003211" "IES SANTA EMERENCIANA")
ID_CATEGORY_se_ga=$(moosh category-create -p "${ID_CATEGORY_se}" -v 1 -d "ADG201" "Gestión Administrativa")

ID_CATEGORY_tm=$(moosh category-create -p 0 -v 1 -d "50010511" "IES TIEMPOS MODERNOS")
ID_CATEGORY_tm_ga=$(moosh category-create -p "${ID_CATEGORY_tm}" -v 1 -d "ADG201" "Gestión Administrativa")

ID_CATEGORY_se_ga=$(moosh category-create -p "${ID_CATEGORY_se}" -v 1 -d "ADG201" "Gestión Administrativa")

ID_CATEGORY_le=$(moosh category-create -p 0 -v 1 -d "50010314" "CPIFP LOS ENLACES")
ID_CATEGORY_le_smr=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IFC201" "Sistemas Microinformáticos y Redes")
ID_CATEGORY_le_ac=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM201" "Actividades Comerciales")
ID_CATEGORY_le_ci=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM301" "Comercio Internacional")
ID_CATEGORY_le_gvec=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM302" "Gestión de Ventas y Espacios Comerciales")
ID_CATEGORY_le_tl=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "COM303" "Transporte y Logística")
ID_CATEGORY_le_daw=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IFC303" "Desarrollo de Aplicaciones WEB")
ID_CATEGORY_le_pae=$(moosh category-create -p "${ID_CATEGORY_le}" -v 1 -d "IMS302" "Producción de Audiovisuales y Espectáculos")

ID_CATEGORY_ca=$(moosh category-create -p 0 -v 1 -d "50018829" "CPIFP CORONA DE ARAGÓN")
ID_CATEGORY_ca_ad=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "ADG302" "Asistencia a la Dirección")
ID_CATEGORY_ca_af=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "ADG301" "Administración y Finanzas")
ID_CATEGORY_ca_lacc=$(moosh category-create -p "${ID_CATEGORY_ca}" -v 1 -d "QUI301" "Laboratorio de Análisis y de Control de Calidad")

ID_CATEGORY_pi=$(moosh category-create -p 0 -v 1 -d "22010712" "CPIFP PIRÁMIDE")
ID_CATEGORY_pi_iea=$(moosh category-create -p "${ID_CATEGORY_pi}" -v 1 -d "ELE202" "Instalaciones Eléctricas y Automáticas")

ID_CATEGORY_sb=$(moosh category-create -p 0 -v 1 -d "44003028" "CPIFP SAN BLAS")
ID_CATEGORY_sb_eca=$(moosh category-create -p "${ID_CATEGORY_sb}" -v 1 -d "SEA301" "Educación y Control Ambiental")

ID_CATEGORY_mi=$(moosh category-create -p 0 -v 1 -d "50010156" "IES MIRALBUENO")
ID_CATEGORY_mi_avge=$(moosh category-create -p "${ID_CATEGORY_mi}" -v 1 -d "HOT301" "Agencias de Viajes y Gestión de Eventos")

ID_CATEGORY_ps=$(moosh category-create -p 0 -v 1 -d "50010144" "IES PABLO SERRANO")
ID_CATEGORY_ps_asir=$(moosh category-create -p "${ID_CATEGORY_ps}" -v 1 -d "IFC301" "Administración de Sistemas Informáticos en Red")

ID_CATEGORY_ba=$(moosh category-create -p 0 -v 1 -d "44010537" "CPIFP BAJO ARAGÓN")
ID_CATEGORY_ba_dam=$(moosh category-create -p "${ID_CATEGORY_ba}" -v 1 -d "IFC301" "Desarrollo de Aplicaciones Multiplataforma")

ID_CATEGORY_rg=$(moosh category-create -p 0 -v 1 -d "50009567" "IES RÍO GÁLLEGO")
ID_CATEGORY_rg_sti=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "ELE304" "Sistemas de Telecomunicaciones e Informáticos")
ID_CATEGORY_rg_fp=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "SAN202" "Farmacia y Parafarmacia")
ID_CATEGORY_rg_es=$(moosh category-create -p "${ID_CATEGORY_rg}" -v 1 -d "SAN203" "Emergencias Sanitarias")

ID_CATEGORY_vt=$(moosh category-create -p 0 -v 1 -d "44003235" "IES VEGA DEL TURIA")
ID_CATEGORY_vt_es=$(moosh category-create -p "${ID_CATEGORY_vt}" -v 1 -d "SAN203" "Emergencias Sanitarias")

ID_CATEGORY_lb=$(moosh category-create -p 0 -v 1 -d "50008460" "IES LUIS BUÑUEL")
ID_CATEGORY_lb_apsd=$(moosh category-create -p "${ID_CATEGORY_lb}" -v 1 -d "SSC201" "Atención a Personas en situación de Dependencia")

ID_CATEGORY_mo=$(moosh category-create -p 0 -v 1 -d "22002491" "CPIFP MONTEARAGON")
ID_CATEGORY_mo_apsd=$(moosh category-create -p "${ID_CATEGORY_mo}" -v 1 -d "SSC201" "Atención a Personas en situación de Dependencia")

ID_CATEGORY_mv=$(moosh category-create -p 0 -v 1 -d "22004611" "IES MARTÍNEZ VARGAS")
ID_CATEGORY_mv_ei=$(moosh category-create -p "${ID_CATEGORY_mv}" -v 1 -d "SSC302" "Educación Infantil (Formación Profesional)")

ID_CATEGORY_av=$(moosh category-create -p 0 -v 1 -d "50009348" "IES AVEMPACE")
ID_CATEGORY_av_ei=$(moosh category-create -p "${ID_CATEGORY_av}" -v 1 -d "SSC302" "Educación Infantil (Formación Profesional)")

ID_CATEGORY_mm=$(moosh category-create -p 0 -v 1 -d "50008642" "IES MARÍA MOLINER")
ID_CATEGORY_mm_is=$(moosh category-create -p "${ID_CATEGORY_mm}" -v 1 -d "SSC303" "Integración Social")

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
    "ID_CATEGORY_general*ayuda*Curso de ayuda*1"
    "ID_CATEGORY_general*profesorado*Curso de Sala de profesorado*1"
    "ID_CATEGORY_general*coordinacion*Curso de Sala de coordinación*1"
    "ID_CATEGORY_app*marketplaces*Curso de verificación marketplaces NO BORRAR*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-639t*Coordinación - Tutoría*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5364*Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5373*Formación y orientación laboral*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5365*Operaciones administrativas de compra*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5368*Técnica contable*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5367*Tratamiento informático de la información*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5119*Empresa en el aula*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5114*Empresa y Administración*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5122*Formación en centros de trabajo*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5111*Inglés  Global*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5117*Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5120*Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_sg_ga*22002521-ADG201-5118*Tratamiento de la documentación contable*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-639t*Coordinación - Tutoría*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5364*Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5373*Formación y orientación laboral*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5365*Operaciones administrativas de compra*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5368*Técnica contable*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5367*Tratamiento informático de la información*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5119*Empresa en el aula*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5114*Empresa y Administración*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5122*Formación en centros de trabajo*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5111*Inglés  Global*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5117*Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5120*Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_se_ga*44003211-ADG201-5118*Tratamiento de la documentación contable*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-639t*Coordinación - Tutoría*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5364*Comunicación empresarial y atención al cliente*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5373*Formación y orientación laboral*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5365*Operaciones administrativas de compra*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5368*Técnica contable*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5367*Tratamiento informático de la información*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5119*Empresa en el aula*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5114*Empresa y Administración*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5122*Formación en centros de trabajo*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5111*Inglés  Global*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5117*Operaciones administrativas de recursos humanos*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5120*Operaciones auxiliares de gestión de tesorería*1"
    "ID_CATEGORY_tm_ga*50010511-ADG201-5118*Tratamiento de la documentación contable*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-627t*Coordinación - Tutoría*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5349*Aplicaciones ofimáticas*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5355*SMR - Formación y orientación laboral*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5359*Lengua extranjera profesional: inglés 1*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5347*Montaje y mantenimiento de equipos*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5351*Redes locales*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5348*Sistemas operativos monopuesto*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4995*Aplicaciones Web*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4997*SMR - Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4998*Formación en centros de trabajo*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-5001*Lengua extranjera profesional: inglés 2*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4993*Seguridad informática*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4994*Servicios en red*1"
    "ID_CATEGORY_le_smr*50010314-IFC201-4991*Sistemas operativos en red*1"
    "ID_CATEGORY_le_ac*50010314-COM201-700t*Coordinación  - Tutoría*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13948*Aplicaciones informáticas para el comercio*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13947*Dinamización del punto de venta*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13946*AC - Formación y orientación laboral*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13945*Gestión de compras*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13944*Inglés*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13943*Marketing en la actividad comercial*1"
    "ID_CATEGORY_le_ac*50010314-COM201-13942*Procesos de venta*1"
    "ID_CATEGORY_le_ci*50010314-COM301-83t*Coordinación_CI - Tutoría*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5417*Formación y Orientación Laboral*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5409*Gestión administrativa del comercio internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5407*Gestión económica y financiera de la empresa*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5405*Inglés*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5408*Logística de almacenamiento*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5406*Transporte internacional de mercancías*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5163*Comercio digital internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5161*Financiación internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5166*CI - Formación en centros de trabajo*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5159*Marketing internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5162*Medios de pago internacionales*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5160*Negociación internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5164*Proyecto de comercio internacional*1"
    "ID_CATEGORY_le_ci*50010314-COM301-5158*Sistema de información de mercados*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-738t*Coordinación_GVEC - Tutoría*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-8412*GVEC - Formación y orientación laboral*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7909*Gestión económica y financiera de la empresa*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7913*Inglés*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7910*Logística de almacenamiento*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7908*Marketing digital*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7907*Políticas de marketing*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7917*Escaparatismo y diseño de espacios comerciales*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7916*Formación en centros de trabajo*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7918*Gestión de productos y promociones en el punto de venta*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7926*Investigación comercial*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7925*Logística de aprovisionamiento*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7919*Organización de equipos de ventas*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7928*Proyecto de gestión de ventas y espacios comerciales*1"
    "ID_CATEGORY_le_gvec*50010314-COM302-7920*Técnicas de venta y negociación*1"
    "ID_CATEGORY_le_tl*50010314-COM303-85t*Coordinación - Tutoría*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5430*TL - Formación y orientación laboral*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5426*Gestión administrativa del comercio internacional*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5422*Gestión económica y financiera de la empresa de transporte y logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5419*Inglés*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5424*Logística de almacenamiento*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5421*Transporte internacional de mercancías*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5198*Comercialización del transporte y la logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5206*Formación en centros de trabajo*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5195*Gestión administrativa del transporte y la logística*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5200*Logística de aprovisionamiento*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5203*Organización del transporte de mercancías*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5202*Organización del transporte de viajeros*1"
    "ID_CATEGORY_le_tl*50010314-COM303-5204*Proyecto de transporte y logística*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-682t*Coordinación - Tutoría*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5180*Bases de datos*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5182*Entornos de desarrollo*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5188*DAW - Formación y orientación laboral*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5191*Lengua Extranjera profesional: Inglés 1*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5178*Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5181*Programación*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5179*Sistemas informáticos*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5083*Desarrollo web  en entorno cliente*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5084*Desarrollo web  en entorno servidor*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5085*Despliegue de aplicaciones web*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5086*Diseño de interfaces Web*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5089*DAW - Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5090*Formación en centros de trabajo*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5092*Lengua Extranjera profesional: Inglés 2*1"
    "ID_CATEGORY_le_daw*50010314-IFC303-5087*Proyecto de desarrollo de aplicaciones Web*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-745t*Coordinación - Tutoría*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-9333*PAE - Formación y orientación laboral*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7941*Lengua extranjera profesional: Inglés 1*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7929*Medios técnicos audiovisuales y escénicos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7930*Planificación de proyectos audiovisuales*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7933*Planificación de proyectos de espectáculos y eventos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7935*Recursos expresivos audiovisuales y escénicos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7950*Administración y promoción de audiovisuales y espectáculos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7952*PAE - Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7940*Formación en centros de trabajo*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7945*Gestión de proyectos de cine, video y multimedia*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7948*Gestión de proyectos de espectáculos y eventos*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7946*Gestión de proyectos de televisión y radio*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7942*Lengua extranjera profesional: Inglés 2*1"
    "ID_CATEGORY_le_pae*50010314-IMS302-7951*Proyecto de producción de audiovisuales y espectáculos*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-750t*Coordinación - Tutoría*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7855*Comunicación y atención al cliente*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-8491*Formación y orientación laboral*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7851*Gestión de la documentación jurídica y empresarial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7862*Inglés*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7853*Ofimática y proceso de la información*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7854*Proceso integral de la actividad comercial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7852*Recursos humanos y responsabilidad social corporativa*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7861*Formación en centros de trabajo*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7871*Gestión avanzada de la información*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7870*Organización de eventos empresariales*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7869*Protocolo empresarial*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7872*Proyecto de asistencia a la dirección*1"
    "ID_CATEGORY_ca_ad*50018829-ADG302-7863*Segunda lengua extranjera: Francés*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-79t*Coordinación - Tutoría*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5297*Comunicación y atención al cliente*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5403*Formación y Orientación Laboral*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5194*Gestión de la documentación jurídica y empresarial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5193*Inglés*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5295*Ofimática y proceso de la información*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5296*Proceso integral de la actividad comercial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5294*Recursos humanos y responsabilidad social corporativa*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5101*Contabilidad y fiscalidad*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5152*Formación en centros de trabajo*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5099*Gestión de recursos humanos*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5100*Gestión financiera*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5148*Gestión logística y comercial*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5150*Proyecto de administración y finanzas*1"
    "ID_CATEGORY_ca_af*50018829-ADG301-5149*Simulación empresarial*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-122t*Coordinación - Tutoría*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5256*Análisis químicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5259*Ensayos fisicoquímicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5260*Ensayos microbiológicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5266*Formación y orientación laboral*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5270*Lengua Extranjera profesional: inglés 1*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5255*Muestreo y preparación de la muestra*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5031*Análisis instrumental*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5036*Calidad y seguridad en el laboratorio Global*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5041*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5035*Ensayos biotecnológicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5032*Ensayos físicos*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5042*Formación en centros de trabajo*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5045*Lengua Extranjera profesional: inglés 2*1"
    "ID_CATEGORY_ca_lacc*50018829-QUI301-5039*Proyecto de laboratorio de análisis y de control de calidad*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-624t*Coordinación - Tutoría*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5335*Automatismos industriales*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5337*Electrotecnia*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5344*Formación y orientación laboral*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-5338*Instalaciones eléctricas interiores.*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-12360*Instalaciones solares fotovoltaicas*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-12359*Electrónica*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4986*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4987*Formación en centros de trabajo*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4981*Infraestructuras comunes de telecomunicaciones en viviendas y edificios*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4980*Instalaciones de distribución*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4982*Instalaciones domóticas.*1"
    "ID_CATEGORY_pi_iea*22010712-ELE202-4984*Máquinas eléctricas*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-757t*Coordinación - Tutoría*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12339*Estructura y dinámica del medio ambiente*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12340*Formación y orientación laboral*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12338*Gestión ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12341*Lengua extranjera profesional: inglés, 1*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12342*Medio natural*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12343*Métodos y productos cartográficos*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12344*Programas de educación ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12345*Actividades de uso público*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12346*Actividades humanas y problemática ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12347*Desenvolvimiento en el medio*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12348*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12349*Formación en centros de trabajo*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12350*Habilidades sociales*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12351*Lengua extranjera profesional: inglés, 2*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12352*Proyecto de educación y control ambiental*1"
    "ID_CATEGORY_sb_eca*44003028-SEA301-12353*Técnicas de educación ambiental*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-645t*Coordinación - Tutoría*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5456*Destinos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5463*Dirección de entidades de intermediación turística*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5447*Estructura del mercado turístico*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5458*Formación y orientación laboral*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5457*Recursos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5234*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5235*Formación en centros de trabajo*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5236*Gestión de productos turísticos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5225*Inglés  Global*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5224*Marketing turístico*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5223*Protocolo y relaciones públicas*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5239*Proyecto de agencias de viajes y gestión de eventos*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5228*Segunda lengua extranjera: Francés Global*1"
    "ID_CATEGORY_mi_avge*50010156-HOT301-5237*Venta de servicios turísticos*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-643t*Coordinación - Tutoría*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5283*Formación y orientación laboral*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5274*Fundamentos de hardware.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5275*Gestión de bases de datos.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5272*Implantación de sistemas operativos.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5286*Lengua extranjera profesional: inglés 1*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5276*Lenguajes de marcas y sistemas de gestión de información.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5273*Planificación y administración de redes.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5054*Administración de sistemas gestores de bases de datos.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5051*Administración de sistemas operativos.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5058*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5059*Formación en centros de trabajo*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5053*Implantación de aplicaciones web.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5061*Lengua extranjera profesional: inglés 2*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5056*Proyecto de administración de sistemas informáticos en red.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5055*Seguridad y alta disponibilidad.*1"
    "ID_CATEGORY_ps_asir*50010144-IFC301-5052*Servicios de red e Internet.*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-681t*Coordinación - Tutoría*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5290*Bases de datos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5293*Entornos de desarrollo*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5173*Formación y Orientación Laboral*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5176*Lengua Extranjera profesional: Inglés 1*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5288*Lenguajes de marcas y sistemas de gestión de información*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5291*Programación*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5289*Sistemas informáticos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5066*Acceso a datos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5068*Desarrollo de interfaces*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5074*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5075*Formación en centros de trabajo*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5077*Lengua Extranjera profesional: Inglés 2*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5070*Programación de servicios y procesos*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5069*Programación multimedia y dispositivos móviles*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5072*Proyecto de desarrollo de aplicaciones multiplataforma*1"
    "ID_CATEGORY_ba_dam*44010537-IFC302-5071*Sistemas de gestión empresarial*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-97t*Coordinación  - Tutoría*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13932*Configuración de infraestructuras de sistemas de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13929*Elementos de sistemas de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13931*Formación y orientación laboral*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13926*Gestión de proyectos de instalaciones de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13927*Lengua Extranjera  profesional: Inglés 1*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13930*Sistemas de telefonía fija y móvil*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13928*Sistemas informáticos y redes locales*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13933*Técnicas y procesos en infraestructuras de telecomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13934*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13939*Formación en centros de trabajo*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13938*Lengua Extranjera profesional: Inglés 2*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13941*Proyecto de Sistemas de Telecomunicaciones e Informáticos*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13937*Redes telemáticas*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13936*Sistemas de producción audiovisual*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13935*Sistemas de radiocomunicaciones*1"
    "ID_CATEGORY_rg_sti*50009567-ELE304-13940*Sistemas integrados y hogar digital*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-630t*Coordinación - Tutoría*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5324*Anatomofisiología  y Patología básicas*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5327*Dispensación de productos farmacéuticos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5325*Disposición y venta de productos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5332*Formación y orientación laboral*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5326*Oficina de Farmacia*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5329*Operaciones básicas de laboratorio*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-5323*Primeros auxilios*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4969*Dispensación de productos parafarmacéuticos*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4974*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4975*Formación en centros de trabajo*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4971*Formulación magistral*1"
    "ID_CATEGORY_rg_fp*50009567-SAN202-4972*Promoción de la salud*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-618t*Coordinación - Tutoría*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5319*Anatomofisiología y patología básicas*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5316*Apoyo psicológico en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5313*Atención sanitaria inicial en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5312*Dotación sanitaria*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5315*Evacuación y traslado de pacientes*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5320*Formación y orientación laboral*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-5310*Mantenimiento mecánico preventivo del vehículo*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4955*Atención sanitaria especial en situaciones de emergencia*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4962*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4963*Formación en centros de trabajo*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4952*Logística sanitaria en emergencias*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4958*Planes de emergencia y dispositivos de riesgos previsibles*1"
    "ID_CATEGORY_rg_es*50009567-SAN203-4959*Tele emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-618t*Coordinación - Tutoría*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5319*Anatomofisiología y patología básicas*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5316*Apoyo psicológico en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5313*Atención sanitaria inicial en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5312*Dotación sanitaria*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5315*Evacuación y traslado de pacientes*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5320*Formación y orientación laboral*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-5310*Mantenimiento mecánico preventivo del vehículo*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4955*Atención sanitaria especial en situaciones de emergencia*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4962*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4963*Formación en centros de trabajo*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4952*Logística sanitaria en emergencias*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4958*Planes de emergencia y dispositivos de riesgos previsibles*1"
    "ID_CATEGORY_vt_es*44003235-SAN203-4959*Tele emergencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-687t*Coordinación - Tutoría*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5381*Apoyo domiciliario*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5382*Atención sanitaria*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5379*Atención y apoyo psicosocial*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5378*Características y necesidades de las personas en situación de dependencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5384*Formación y orientación laboral*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5375*Primeros auxilios*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5128*Apoyo a la comunicación*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5131*Atención higiénica*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5125*Destrezas sociales*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5133*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5134*Formación en centros de trabajo*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5124*Organización de la atención a las personas en situación de dependencia*1"
    "ID_CATEGORY_lb_apsd*50008460-SSC201-5135*Teleasistencia*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-687t*Coordinación - Tutoría*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5381*Apoyo domiciliario*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5382*Atención sanitaria*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5379*Atención y apoyo psicosocial*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5378*Características y necesidades de las personas en situación de dependencia*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5384*Formación y orientación laboral*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5375*Primeros auxilios*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5128*Apoyo a la comunicación*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5131*Atención higiénica*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5125*Destrezas sociales*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5133*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5134*Formación en centros de trabajo*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5124*Organización de la atención a las personas en situación de dependencia*1"
    "ID_CATEGORY_mo_apsd*22002491-SSC201-5135*Teleasistencia*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-140t*Coordinación - Tutoría*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5433*Autonomía personal y salud infantil.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5436*Desarrollo cognitivo y motor.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5432*Didáctica de la Educación Infantil.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5434*El juego infantil y su metodología.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5442*Formación y orientación laboral.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5445*Lengua extranjera profesional: inglés 1*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5441*Primeros auxilios.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5212*Desarrollo socioafectivo.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5218*Empresa e iniciativa emprendedora.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5210*Expresión y comunicación.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5219*Formación en centros de trabajo*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5213*Habilidades sociales.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5214*Intervención con familias y atención a menores en riesgo social.*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5221*Lengua extranjera profesional: inglés 2*1"
    "ID_CATEGORY_mv_ei*22004611-SSC302-5215*Proyecto de atención a la infancia.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-140t*Coordinación - Tutoría*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5433*Autonomía personal y salud infantil.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5436*Desarrollo cognitivo y motor.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5432*Didáctica de la Educación Infantil.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5434*El juego infantil y su metodología.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5442*Formación y orientación laboral.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5445*Lengua extranjera profesional: inglés 1*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5441*Primeros auxilios.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5212*Desarrollo socioafectivo.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5218*Empresa e iniciativa emprendedora.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5210*Expresión y comunicación.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5219*Formación en centros de trabajo*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5213*Habilidades sociales.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5214*Intervención con familias y atención a menores en riesgo social.*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5221*Lengua extranjera profesional: inglés 2*1"
    "ID_CATEGORY_av_ei*50009348-SSC302-5215*Proyecto de atención a la infancia.*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-768t*Coordinación - Tutoría*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7878*Apoyo a la intervención educativa*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7874*Contexto de la intervención social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-8339*Formación y orientación laboral*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7875*Inserción sociolaboral*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7884*Lengua extranjera profesional: Inglés 1*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7877*Mediación comunitaria*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7882*Primeros auxilios*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7879*Promoción de la autonomía personal*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7892*Atención a las unidades de convivencia*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7902*Empresa e iniciativa emprendedora*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7889*Formación en centros de trabajo*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7793*Formación y orientación laboral ERRÓNEO*0"
    "ID_CATEGORY_mm_is*50008642-SSC303-7899*Habilidades sociales*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7885*Lengua extranjera profesional: Inglés 2*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7897*Metodología de la intervención social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7901*Proyecto de integración social*1"
    "ID_CATEGORY_mm_is*50008642-SSC303-7896*Sistemas aumentativos y alternativos de comunicación*1"
    "ID_CATEGORY_miscelanea*moodle-para-fp-distancia*Moodle para FP a distancia. Digitalización A.O*1"
    "ID_CATEGORY_miscelanea*pruebas*Pruebas*1"
    "ID_CATEGORY_miscelanea*actualizacion-moodle-fp-distancia*Actualización - Moodle para FP a distancia*1"
    "ID_CATEGORY_miscelanea*STI_Inglés_1*(STI) Lengua Extranjera profesional: Inglés 1*1"
    "ID_CATEGORY_miscelanea*Interno*Pruebas_Internas*1"
    "ID_CATEGORY_miscelanea*restauraciones*Restauraciones*1"

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
        COURSE_ID=$(moosh course-create --category "${!CATEGORY}" --fullname "${FULLNAME}" --description "${FULLNAME}" "${SHORTNAME}")
    else
        # Si existe el curso lo restauro
        echo "***** Restoring /var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz course to category ${CATEGORY}"
        COURSE_ID=$(moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/${SHORTNAME}.mbz "${!CATEGORY}")
        COURSE_ID=$(echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2)
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
        FPD_APP_USER_STUDENT_ID=$(moosh user-create --password "${APP_PASSWORD}" --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname student --lastname demoapp demoapp)
        FPD_APP_USER_TEACHER_ID=$(moosh user-create --password "${APP_TEACHER_PASSWORD}" --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname teacher --lastname demoapp profesor1)

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