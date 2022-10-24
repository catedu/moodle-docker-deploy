##########################################3
# En la variable centres indicar el listado de centros
# En el main indicar los centros y que estudios tiene cada uno
# En el procedimiento set_studies_to_centre establecer los ciclos y qué módulos tiene
##########################################3
CENTRES=( 
        "22002521-IES SIERRA DE GUARA" 
        "44003211-IES SANTA EMERENCIANA" 
        "50010511-IES TIEMPOS MODERNOS" 
        "50010314-CPIFP LOS ENLACES" 
        "50018829-CPIFP CORONA DE ARAGÓN" 
        "22010712-CPIFP PIRÁMIDE" 
        "44003028-CPIFP SAN BLAS"
        "50010156-IES MIRALBUENO" 
        "50010144-IES PABLO SERRANO" 
        "44010537-CPIFP BAJO ARAGÓN" 
        "50009567-IES RÍO GÁLLEGO" 
        "44003235-IES VEGA DEL TURIA" 
        "50008460-IES LUIS BUÑUEL" 
        "22002491-CPIFP MONTEARAGON" 
        "22004611-IES MARTÍNEZ VARGAS" 
        "50009348-IES AVEMPACE" 
        "50008642-IES MARÍA MOLINER"
    )
STUDIES=( )
COURSES=( )

#
# functions
#

# Crea la subcategoría para el estudio dentro de la categoría del centro dado
# y establece los cursos que el estudio en cuestión tiene
# $1 id category of the centre
# $2 cod centre
# $3 studies of the centre
set_studies_to_centre(){
    echo "** Setting studies ${3} to centre ${1} (cod_centre: ${2})"
    COD_CENTRE=`echo ${2}`
    for STUDY in "${STUDIES[@]}"
    do
        echo "*** Processing study: $STUDY"
        ID_STUDY=`echo $STUDY | cut -d "-" -f 1`
        NAME_STUDY=`echo $STUDY | cut -d "-" -f 2`
        CATEGORY_STUDY=`moosh category-create -p ${1} -v 1 -d "${ID_STUDY}" "${NAME_STUDY}"`
        moosh cohort-create -d "${COD_CENTRE}-${ID_STUDY}" -i ${COD_CENTRE}-${ID_STUDY} -c ${1} "${COD_CENTRE}-${ID_STUDY}"
        case $STUDY in
            "ADG201-Gestión Administrativa ")
                COURSES=( 
                        "639t-Coordinación - Tutoría"
                        "5364-Comunicación empresarial y atención al cliente" 
                        "5373-Formación y orientación laboral" 
                        "5365-Operaciones administrativas de compra-venta" 
                        "5368-Técnica contable" 
                        "5367-Tratamiento informático de la información" 
                        "5119-Empresa en el aula" 
                        "5114-Empresa y Administración" 
                        "5122-Formación en centros de trabajo" 
                        "5111-Inglés  Global" 
                        "5117-Operaciones administrativas de recursos humanos" 
                        "5120-Operaciones auxiliares de gestión de tesorería" 
                        "5118-Tratamiento de la documentación contable" 
                    )
            ;;
            "ADG301-Administración y Finanzas")
                COURSES=( 
                        "79t-Coordinación - Tutoría"
                        "5297-Comunicación y atención al cliente"
                        "5403-Formación y Orientación Laboral"
                        "5194-Gestión de la documentación jurídica y empresarial"
                        "5193-Inglés"
                        "5295-Ofimática y proceso de la información"
                        "5296-Proceso integral de la actividad comercial"
                        "5294-Recursos humanos y responsabilidad social corporativa"
                        "5101-Contabilidad y fiscalidad"
                        "5152-Formación en centros de trabajo"
                        "5099-Gestión de recursos humanos"
                        "5100-Gestión financiera"
                        "5148-Gestión logística y comercial"
                        "5150-Proyecto de administración y finanzas "
                        "5149-Simulación empresarial"
                    )
            ;;
            "ADG302-Asistencia a la Dirección")
                COURSES=(
                        "750t-Coordinación - Tutoría"
                        "7855-Comunicación y atención al cliente"
                        "8491-Formación y orientación laboral"
                        "7851-Gestión de la documentación jurídica y empresarial"
                        "7862-Inglés"
                        "7853-Ofimática y proceso de la información"
                        "7854-Proceso integral de la actividad comercial"
                        "7852-Recursos humanos y responsabilidad social corporativa"
                        "7861-Formación en centros de trabajo"
                        "7871-Gestión avanzada de la información"
                        "7870-Organización de eventos empresariales"
                        "7869-Protocolo empresarial"
                        "7872-Proyecto de asistencia a la dirección"
                        "7863-Segunda lengua extranjera: Francés"
                    )
            ;;
            "COM201-Actividades Comerciales")
                COURSES=(
                        "700t-Coordinación - Tutoría"
                        "13948-Aplicaciones informáticas para el comercio"
                        "13947-Dinamización del punto de venta"
                        "13946-Formación y orientación laboral"
                        "13945-Gestión de compras"
                        "13944-Inglés"
                        "13943-Marketing en la actividad comercial"
                        "13942-Procesos de venta"
                    )
            ;;
            "COM301-Comercio Internacional")
                COURSES=(
                        "83t-Coordinación - Tutoría"
                        "5417-Formación y Orientación Laboral"
                        "5409-Gestión administrativa del comercio internacional"
                        "5407-Gestión económica y financiera de la empresa"
                        "5405-Inglés"
                        "5408-Logística de almacenamiento"
                        "5406-Transporte internacional de mercancías"
                        "5163-Comercio digital internacional "
                        "5161-Financiación internacional"
                        "5166-Formación en centros de trabajo"
                        "5159-Marketing internacional"
                        "5162-Medios de pago internacionales"
                        "5160-Negociación internacional"
                        "5164-Proyecto de comercio internacional"
                        "5158-Sistema de información de mercados"
                    )
            ;;
            "COM302-Gestión de Ventas y Espacios Comerciales")
                COURSES=(
                        "738t-Coordinación - Tutoría"
                        "8412-Formación y orientación laboral"
                        "7909-Gestión económica y financiera de la empresa"
                        "7913-Inglés"
                        "7910-Logística de almacenamiento"
                        "7908-Marketing digital"
                        "7907-Políticas de marketing"
                        "7917-Escaparatismo y diseño de espacios comerciales"
                        "7916-Formación en centros de trabajo"
                        "7918-Gestión de productos y promociones en el punto de venta"
                        "7926-Investigación comercial"
                        "7925-Logística de aprovisionamiento"
                        "7919-Organización de equipos de ventas"
                        "7928-Proyecto de gestión de ventas y espacios comerciales"
                        "7920-Técnicas de venta y negociación"
                    )
            ;;
            "COM303-Transporte y Logística")
                COURSES=(
                        "85t-Coordinación - Tutoría"
                        "5430-Formación y orientación laboral"
                        "5426-Gestión administrativa del comercio internacional"
                        "5422-Gestión económica y financiera de la empresa de transporte y logística"
                        "5419-Inglés"
                        "5424-Logística de almacenamiento"
                        "5421-Transporte internacional de mercancías"
                        "5198-Comercialización del transporte y la logística"
                        "5206-Formación en centros de trabajo"
                        "5195-Gestión administrativa del transporte y la logística"
                        "5200-Logística de aprovisionamiento"
                        "5203-Organización del transporte de mercancías"
                        "5202-Organización del transporte de viajeros"
                        "5204-Proyecto de transporte y logística"
                    )
            ;;
            "ELE202-Instalaciones Eléctricas y Automáticas")
                COURSES=(
                        "624t-Coordinación - Tutoría"
                        "5335-Automatismos industriales"
                        "5337-Electrotecnia"
                        "5344-Formación y orientación laboral"
                        "5338-Instalaciones eléctricas interiores."
                        "12360-Instalaciones solares fotovoltaicas"
                        "12359-Electrónica"
                        "4986-Empresa e iniciativa emprendedora"
                        "4987-Formación en centros de trabajo"
                        "4981-Infraestructuras comunes de telecomunicaciones en viviendas y edificios"
                        "4980-Instalaciones de distribución"
                        "4982-Instalaciones domóticas."
                        "4984-Máquinas eléctricas"
                    )
            ;;
            "ELE304-Sistemas de Telecomunicaciones e Informáticos")
                COURSES=(
                        "97t-Coordinación - Tutoría"
                        "13932-Configuración de infraestructuras de sistemas de telecomunicaciones"
                        "13929-Elementos de sistemas de telecomunicaciones"
                        "13931-Formación y orientación laboral"
                        "13926-Gestión de proyectos de instalaciones de telecomunicaciones"
                        "13927-Lengua Extranjera  profesional: Inglés 1"
                        "13930-Sistemas de telefonía fija y móvil"
                        "13928-Sistemas informáticos y redes locales"
                        "13933-Técnicas y procesos en infraestructuras de telecomunicaciones"
                        "13934-Empresa e iniciativa emprendedora"
                        "13939-Formación en centros de trabajo"
                        "13938-Lengua Extranjera profesional: Inglés 2"
                        "13941-Proyecto de Sistemas de Telecomunicaciones e Informáticos"
                        "13937-Redes telemáticas"
                        "13936-Sistemas de producción audiovisual"
                        "13935-Sistemas de radiocomunicaciones"
                        "13940-Sistemas integrados y hogar digital"
                    )
            ;;
            "HOT301-Agencias de Viajes y Gestión de Eventos")
                COURSES=( 
                        "645t-Coordinación - Tutoría"
                        "5456-Destinos turísticos"
                        "5463-Dirección de entidades de intermediación turística"
                        "5447-Estructura del mercado turístico"
                        "5458-Formación y orientación laboral"
                        "5457-Recursos turísticos"
                        "5234-Empresa e iniciativa emprendedora"
                        "5235-Formación en centros de trabajo"
                        "5236-Gestión de productos turísticos"
                        "5225-Inglés  Global"
                        "5224-Marketing turístico"
                        "5223-Protocolo y relaciones públicas"
                        "5239-Proyecto de agencias de viajes y gestión de eventos"
                        "5228-Segunda lengua extranjera: Francés Global"
                        "5237-Venta de servicios turísticos"
                    )
            ;;
            "IFC201-Sistemas Microinformáticos y Redes")
                COURSES=( 
                        "627t-Coordinación - Tutoría"
                        "5349-Aplicaciones ofimáticas"
                        "5355-Formación y orientación laboral"
                        "5359-Lengua extranjera profesional: inglés 1"
                        "5347-Montaje y mantenimiento de equipos"
                        "5351-Redes locales"
                        "5348-Sistemas operativos monopuesto"
                        "4995-Aplicaciones Web"
                        "4997-Empresa e iniciativa emprendedora"
                        "4998-Formación en centros de trabajo"
                        "5001-Lengua extranjera profesional: inglés 2"
                        "4993-Seguridad informática"
                        "4994-Servicios en red"
                        "4991-Sistemas operativos en red"
                    )
            ;;
            "IFC301-Administración de Sistemas Informáticos en Red")
                COURSES=( 
                        "643t-Coordinación - Tutoría"
                        "5283-Formación y orientación laboral "
                        "5274-Fundamentos de hardware. "
                        "5275-Gestión de bases de datos. "
                        "5272-Implantación de sistemas operativos. "
                        "5286-Lengua extranjera profesional: inglés 1 "
                        "5276-Lenguajes de marcas y sistemas de gestión de información. "
                        "5273-Planificación y administración de redes. "
                        "5054-Administración de sistemas gestores de bases de datos."
                        "5051-Administración de sistemas operativos."
                        "5058-Empresa e iniciativa emprendedora"
                        "5059-Formación en centros de trabajo"
                        "5053-Implantación de aplicaciones web."
                        "5061-Lengua extranjera profesional: inglés 2"
                        "5056-Proyecto de administración de sistemas informáticos en red. "
                        "5055-Seguridad y alta disponibilidad."
                        "5052-Servicios de red e Internet."
                    )
            ;;
            "IFC302-Desarrollo de Aplicaciones Multiplataforma")
                COURSES=( 
                        "681t-Coordinación - Tutoría"
                        "5290-Bases de datos"
                        "5293-Entornos de desarrollo"
                        "5173-Formación y Orientación Laboral"
                        "5176-Lengua Extranjera profesional: Inglés 1"
                        "5288-Lenguajes de marcas y sistemas de gestión de información"
                        "5291-Programación"
                        "5289-Sistemas informáticos"
                        "5066-Acceso a datos"
                        "5068-Desarrollo de interfaces"
                        "5074-Empresa e iniciativa emprendedora"
                        "5075-Formación en centros de trabajo"
                        "5077-Lengua Extranjera profesional: Inglés 2"
                        "5070-Programación de servicios y procesos"
                        "5069-Programación multimedia y dispositivos móviles"
                        "5072-Proyecto de desarrollo de aplicaciones multiplataforma"
                        "5071-Sistemas de gestión empresarial"
                    )
            ;;
            "IFC303-Desarrollo de Aplicaciones WEB")
                COURSES=( 
                        "682t-Coordinación - Tutoría"
                        "5180-Bases de datos"
                        "5182-Entornos de desarrollo"
                        "5188-Formación y orientación laboral"
                        "5191-Lengua Extranjera profesional: Inglés 1"
                        "5178-Lenguajes de marcas y sistemas de gestión de información"
                        "5181-Programación"
                        "5179-Sistemas informáticos"
                        "5083-Desarrollo web  en entorno cliente"
                        "5084-Desarrollo web  en entorno servidor"
                        "5085-Despliegue de aplicaciones web"
                        "5086-Diseño de interfaces Web"
                        "5089-Empresa e iniciativa emprendedora"
                        "5090-Formación en centros de trabajo"
                        "5092-Lengua Extranjera profesional: Inglés 2"
                        "5087-Proyecto de desarrollo de aplicaciones Web"
                    )
            ;;
            "IMS302-Producción de Audiovisuales y Espectáculos ")
                COURSES=( 
                        "745t-Coordinación - Tutoría"
                        "9333-Formación y orientación laboral"
                        "7941-Lengua extranjera profesional: Inglés 1"
                        "7929-Medios técnicos audiovisuales y escénicos"
                        "7930-Planificación de proyectos audiovisuales"
                        "7933-Planificación de proyectos de espectáculos y eventos"
                        "7935-Recursos expresivos audiovisuales y escénicos"
                        "7950-Administración y promoción de audiovisuales y espectáculos"
                        "7952-Empresa e iniciativa emprendedora"
                        "7940-Formación en centros de trabajo"
                        "7945-Gestión de proyectos de cine, video y multimedia"
                        "7948-Gestión de proyectos de espectáculos y eventos"
                        "7946-Gestión de proyectos de televisión y radio"
                        "7942-Lengua extranjera profesional: Inglés 2"
                        "7951-Proyecto de producción de audiovisuales y espectáculos"
                    )
            ;;
            "QUI301-Laboratorio de Análisis y de Control de Calidad")
                COURSES=( 
                        "122t-Coordinación - Tutoría"
                        "5256-Análisis químicos"
                        "5259-Ensayos fisicoquímicos"
                        "5260-Ensayos microbiológicos"
                        "5266-Formación y orientación laboral"
                        "5270-Lengua Extranjera profesional: inglés 1"
                        "5255-Muestreo y preparación de la muestra"
                        "5031-Análisis instrumental"
                        "5036-Calidad y seguridad en el laboratorio Global"
                        "5041-Empresa e iniciativa emprendedora"
                        "5035-Ensayos biotecnológicos"
                        "5032-Ensayos físicos"
                        "5042-Formación en centros de trabajo"
                        "5045-Lengua Extranjera profesional: inglés 2"
                        "5039-Proyecto de laboratorio de análisis y de control de calidad"
                    )
            ;;
            "SAN202-Farmacia y Parafarmacia")
                COURSES=( 
                        "630t-Coordinación - Tutoría"
                        "5324-Anatomofisiología  y Patología básicas"
                        "5327-Dispensación de productos farmacéuticos"
                        "5325-Disposición y venta de productos"
                        "5332-Formación y orientación laboral"
                        "5326-Oficina de Farmacia"
                        "5329-Operaciones básicas de laboratorio"
                        "5323-Primeros auxilios"
                        "4969-Dispensación de productos parafarmacéuticos"
                        "4974-Empresa e iniciativa emprendedora"
                        "4975-Formación en centros de trabajo"
                        "4971-Formulación magistral"
                        "4972-Promoción de la salud "
                    )
            ;;
            "SAN203-Emergencias Sanitarias")
                COURSES=( 
                        "618t-Coordinación - Tutoría"
                        "5319-Anatomofisiología y patología básicas"
                        "5316-Apoyo psicológico en situaciones de emergencia"
                        "5313-Atención sanitaria inicial en situaciones de emergencia"
                        "5312-Dotación sanitaria"
                        "5315-Evacuación y traslado de pacientes"
                        "5320-Formación y orientación laboral"
                        "5310-Mantenimiento mecánico preventivo del vehículo"
                        "4955-Atención sanitaria especial en situaciones de emergencia"
                        "4962-Empresa e iniciativa emprendedora"
                        "4963-Formación en centros de trabajo"
                        "4952-Logística sanitaria en emergencias"
                        "4958-Planes de emergencia y dispositivos de riesgos previsibles"
                        "4959-Tele emergencia"
                    )
            ;;
            "SSC201-Atención a Personas en situación de Dependencia")
                COURSES=( 
                        "687t-Coordinación - Tutoría"
                        "5381-Apoyo domiciliario"
                        "5382-Atención sanitaria"
                        "5379-Atención y apoyo psicosocial"
                        "5378-Características y necesidades de las personas en situación de dependencia"
                        "5384-Formación y orientación laboral"
                        "5375-Primeros auxilios"
                        "5128-Apoyo a la comunicación"
                        "5131-Atención higiénica"
                        "5125-Destrezas sociales"
                        "5133-Empresa e iniciativa emprendedora"
                        "5134-Formación en centros de trabajo"
                        "5124-Organización de la atención a las personas en situación de dependencia"
                        "5135-Teleasistencia"
                    )
            ;;
            "SSC302-Educación Infantil (Formación Profesional)")
                COURSES=( 
                        "140t-Coordinación - Tutoría"
                        "5433-Autonomía personal y salud infantil."
                        "5436-Desarrollo cognitivo y motor."
                        "5432-Didáctica de la Educación Infantil."
                        "5434-El juego infantil y su metodología."
                        "5442-Formación y orientación laboral."
                        "5445-Lengua extranjera profesional: inglés 1"
                        "5441-Primeros auxilios."
                        "5212-Desarrollo socioafectivo."
                        "5218-Empresa e iniciativa emprendedora."
                        "5210-Expresión y comunicación."
                        "5219-Formación en centros de trabajo"
                        "5213-Habilidades sociales."
                        "5214-Intervención con familias y atención a menores en riesgo social."
                        "5221-Lengua extranjera profesional: inglés 2"
                        "5215-Proyecto de atención a la infancia."
                    )
            ;;
            "SSC303-Integración Social")
                COURSES=( 
                        "768t-Coordinación - Tutoría"
                        "7878-Apoyo a la intervención educativa"
                        "7874-Contexto de la intervención social"
                        "8339-Formación y orientación laboral"
                        "7875-Inserción sociolaboral"
                        "7884-Lengua extranjera profesional: Inglés 1"
                        "7877-Mediación comunitaria"
                        "7882-Primeros auxilios"
                        "7879-Promoción de la autonomía personal"
                        "7892-Atención a las unidades de convivencia"
                        "7902-Empresa e iniciativa emprendedora"
                        "7889-Formación en centros de trabajo"
                        "7793-Formación y orientación laboral  ( Nocturno, 2º )" # duplicado. Crear pero ocultar para mantener IDs intactos
                        "7899-Habilidades sociales"
                        "7885-Lengua extranjera profesional: Inglés 2"
                        "7897-Metodología de la intervención social"
                        "7901-Proyecto de integración social"
                        "7896-Sistemas aumentativos y alternativos de comunicación"
                    )
            ;;
            "SEA301-Educación y Control Ambiental")
                COURSES=( 
                        "757t-Coordinación - Tutoría"
                        "12339-Estructura y dinámica del medio ambiente"
                        "12340-Formación y orientación laboral"
                        "12338-Gestión ambiental"
                        "12341-Lengua extranjera profesional: inglés, 1"
                        "12342-Medio natural"
                        "12343-Métodos y productos cartográficos"
                        "12344-Programas de educación ambiental"
                        "12345-Actividades de uso público"
                        "12346-Actividades humanas y problemática ambiental"
                        "12347-Desenvolvimiento en el medio"
                        "12348-Empresa e iniciativa emprendedora"
                        "12349-Formación en centros de trabajo"
                        "12350-Habilidades sociales"
                        "12351-Lengua extranjera profesional: inglés, 2"
                        "12352-Proyecto de educación y control ambiental"
                        "12353-Técnicas de educación ambiental"
                    )
            ;;
        esac
        # set courses to category
        set_modules_to_study ${CATEGORY_STUDY} ${COD_CENTRE} ${ID_STUDY} ${COURSES}
        # set format topics to all courses of the category
        echo "Cambio la configuración de los cursos de la categoría ${CATEGORY_STUDY} a formato topics"
        moosh course-config-set category ${CATEGORY_STUDY} format topics
    done
}

# Crea o restaura los cursos en la categoría dada
# $1 idCategory of the study
# $2 cod centre
# $3 cod estudy
# $4 array of courses to create or restore into the category
set_modules_to_study(){
    echo "set_modules_to_study()"
    echo "**** Setting courses ${4} to study category ${1} (cod_centre ${2}) (cod_study ${3} )"
    COD_CENTRE=`echo ${2}`
    COD_STUDY=`echo ${3}`
    for COURSE in "${COURSES[@]}"
    do
        echo "***** Setting course ${COURSE} to study category ${1} (cod_centre ${2} cod_study ${3})"
        COD_ENSENANZA=`echo "${COURSE}" | cut -d '-' -f 1`
        NAME_ENSENANZA=`echo "${COURSE}" | cut -d '-' -f 2`
        COURSE_ID=""
        
        if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}.mbz" ]; then
            echo "***** The course /var/www/moodledata/repository/mbzs_curso_anterior/${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}.mbz doesn't exist, creating empty course ${COURSE} into category ${1}"
            COURSE_ID=`moosh course-create --category ${1} --fullname "${NAME_ENSENANZA}" --description "${COURSE}" "${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}"`
        else
            echo "***** Loading /var/www/moodledata/repository/mbzs_curso_anterior/${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}.mbz course to category ${1}"
            COURSE_ID=`moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}.mbz ${1}`
            COURSE_ID=`echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2`
            moosh course-config-set course ${COURSE_ID} shortname "${COD_CENTRE}-${COD_STUDY}-${COD_ENSENANZA}"
            moosh course-config-set course ${COURSE_ID} fullname "${NAME_ENSENANZA}"
        fi

        # si el cod_ensenanza contiene una t matricular en el curso a través de la cohorte
        if [[ ${COD_ENSENANZA} == *t ]]; 
        then
            echo "****** Enrolling the cohort ${COD_CENTRE}-${COD_STUDY} into the course_id ${COURSE_ID}  "
            moosh cohort-enrol -c ${COURSE_ID} "${COD_CENTRE}-${COD_STUDY}"
        fi

    done
}

# 
# main
# 

# Creo la categoría general con los 3 cursos que colgarán de ella
ID_CATEGORY=`moosh category-create -p 0 -v 1 -d "general" "General"`

moosh cohort-create -d "alumnado" -i alumnado -c ${ID_CATEGORY} "alumnado"
moosh cohort-create -d "profesorado" -i profesorado -c ${ID_CATEGORY} "profesorado"
moosh cohort-create -d "coordinacion" -i coordinacion -c ${ID_CATEGORY} "coordinacion"
if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/ayuda.mbz" ]; then
    echo "creating empty course ayuda"
    COURSE_ID=`moosh course-create --category ${ID_CATEGORY} --fullname "Curso de ayuda" --description "Curso de ayuda" ayuda`
else
    echo "restoring course ayuda"
    COURSE_ID=`moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/ayuda.mbz ${ID_CATEGORY}`
    COURSE_ID=`echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2`
    moosh course-config-set course ${COURSE_ID} shortname ayuda
    moosh course-config-set course ${COURSE_ID} fullname "Curso de ayuda"
fi
moosh cohort-enrol -c ${COURSE_ID} "alumnado"
moosh cohort-enrol -c ${COURSE_ID} "profesorado"
moosh cohort-enrol -c ${COURSE_ID} "coordinacion"
# Set access for guest users to the course: 0 means permitted 1 means prohibited
moosh sql-run "UPDATE mdl_enrol set status = 0 WHERE enrol = 'guest' AND courseid = ${COURSE_ID}"

if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/profesorado.mbz" ]; then
    echo "creating empty course profesorado"
    COURSE_ID=`moosh course-create --category ${ID_CATEGORY} --fullname "Curso de Sala de profesorado" --description "Curso de Sala de profesorado" profesorado`
else
    echo "restoring profesorado course"
    COURSE_ID=`moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/profesorado.mbz ${ID_CATEGORY}`
    COURSE_ID=`echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2`
    moosh course-config-set course ${COURSE_ID} shortname profesorado
    moosh course-config-set course ${COURSE_ID} fullname "Curso de Sala de profesorado"
fi
moosh cohort-enrol -c ${COURSE_ID} "profesorado"
moosh cohort-enrol -c ${COURSE_ID} "coordinacion"

if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/coordinacion.mbz" ]; then
    echo "creating empty course coordinacion"
    COURSE_ID=`moosh course-create --category ${ID_CATEGORY} --fullname "Curso de Sala de coordinacion" --description "Curso de Sala de coordinacion" coordinacion`
else
    echo "restoring coordinación course"
    COURSE_ID=`moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/coordinacion.mbz ${ID_CATEGORY}`
    COURSE_ID=`echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2`
    moosh course-config-set course ${COURSE_ID} shortname coordinacion
    moosh course-config-set course ${COURSE_ID} fullname "Curso de Sala de coordinación"
fi
moosh cohort-enrol -c ${COURSE_ID} "coordinacion"

moosh course-config-set category ${ID_CATEGORY} format topics

# Creo el curso que necesitan para los marketstore de la app y matriculo en el curso a los usuarios correspondientes de prueba
echo "creando lo necesario para apps móviles"
ID_CATEGORY=`moosh category-create -p 0 -v 1 -d "app" "NO BORRAR - APP MOVIL"`
if [ ! -f "/var/www/moodledata/repository/mbzs_curso_anterior/marketplaces.mbz" ]; then
    COURSE_ID=`moosh course-create --category ${ID_CATEGORY} --fullname "Curso de verificación marketplaces NO BORRAR" --description "Curso de verificación marketplaces NO BORRAR" marketplaces`
else
    COURSE_ID=`moosh course-restore /var/www/moodledata/repository/mbzs_curso_anterior/marketplaces.mbz ${ID_CATEGORY}`
fi
echo "Creando y matriculando usuarios de testeo de la APP"
FPD_APP_USER_STUDENT_ID=`moosh user-create --password ${APP_PASSWORD} --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname student --lastname demoapp demoapp`
FPD_APP_USER_TEACHER_ID=`moosh user-create --password ${APP_TEACHER_PASSWORD} --email alumnado@education.catedu.es --digest 2 --city Aragón --country ES --firstname teacher --lastname demoapp profesor1`

moosh course-enrol -r editingteacher -i ${COURSE_ID} ${FPD_APP_USER_TEACHER_ID}
moosh course-enrol -r student -i ${COURSE_ID} ${FPD_APP_USER_STUDENT_ID}

# creo la estructura de centros > ciclos > módulos

for CENTRE in "${CENTRES[@]}"
do
    echo "* Processing centre: ${CENTRE}"
    ID_CENTRE=`echo "${CENTRE}" | cut -d '-' -f 1`
    NAME_CENTRE=`echo "${CENTRE}" | cut -d '-' -f 2`
    CATEGORY_IES=`moosh category-create -p 0 -v 1 -d "${ID_CENTRE}" "${NAME_CENTRE}"`
    case $CENTRE in
        "22002521-IES SIERRA DE GUARA")
            STUDIES=( "ADG201-Gestión Administrativa " )
        ;;
        "44003211-IES SANTA EMERENCIANA")
            STUDIES=("ADG201-Gestión Administrativa ")
        ;;
        "50010511-IES TIEMPOS MODERNOS")
            STUDIES=("ADG201-Gestión Administrativa ")
        ;;
        "50010314-CPIFP LOS ENLACES")
            STUDIES=( 
                    "IFC201-Sistemas Microinformáticos y Redes" 
                    "COM201-Actividades Comerciales"
                    "COM301-Comercio Internacional"
                    "COM302-Gestión de Ventas y Espacios Comerciales" 
                    "COM303-Transporte y Logística" 
                    "IFC303-Desarrollo de Aplicaciones WEB" 
                    "IMS302-Producción de Audiovisuales y Espectáculos " 
                )
        ;;
        "50018829-CPIFP CORONA DE ARAGÓN")
            STUDIES=( 
                    "ADG302-Asistencia a la Dirección" 
                    "ADG301-Administración y Finanzas" 
                    "QUI301-Laboratorio de Análisis y de Control de Calidad" 
                )
        ;;
        "22010712-CPIFP PIRÁMIDE")
            STUDIES=( 
                    "ELE202-Instalaciones Eléctricas y Automáticas"
                )
        ;;
        "44003028-CPIFP SAN BLAS")
            STUDIES=( 
                    "SEA301-Educación y Control Ambiental"
                )
        ;;
        "50010156-IES MIRALBUENO")
            STUDIES=( 
                    "HOT301-Agencias de Viajes y Gestión de Eventos"
                )
        ;;
        "50010144-IES PABLO SERRANO")
            STUDIES=( 
                    "IFC301-Administración de Sistemas Informáticos en Red"
                )
        ;;
        "44010537-CPIFP BAJO ARAGÓN")
            STUDIES=( 
                    "IFC302-Desarrollo de Aplicaciones Multiplataforma"
                )
        ;;
        "50009567-IES RÍO GÁLLEGO")
            STUDIES=( 
                    "ELE304-Sistemas de Telecomunicaciones e Informáticos"
                    "SAN202-Farmacia y Parafarmacia"
                    "SAN203-Emergencias Sanitarias"
                )
        ;;
        "44003235-IES VEGA DEL TURIA")
            STUDIES=( 
                    "SAN203-Emergencias Sanitarias"
                )
        ;;
        "50008460-IES LUIS BUÑUEL")
            STUDIES=( 
                    "SSC201-Atención a Personas en situación de Dependencia"
                )
        ;;
        "22002491-CPIFP MONTEARAGON")
            STUDIES=( 
                    "SSC201-Atención a Personas en situación de Dependencia"
                )
        ;;
        "22004611-IES MARTÍNEZ VARGAS")
            STUDIES=( 
                    "SSC302-Educación Infantil (Formación Profesional)"
                )
        ;;
        "50009348-IES AVEMPACE")
            STUDIES=( 
                    "SSC302-Educación Infantil (Formación Profesional)"
                )
        ;;
        "50008642-IES MARÍA MOLINER")
            STUDIES=( 
                    "SSC303-Integración Social"
                )
        ;;
    esac
    # set studies category to centre category
    set_studies_to_centre ${CATEGORY_IES} ${ID_CENTRE} ${STUDIES}
done