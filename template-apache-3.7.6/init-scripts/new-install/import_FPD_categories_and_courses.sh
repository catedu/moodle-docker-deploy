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
# procedures
set_studies_to_centre(){
    echo "** Setting studies ${2} to centre ${1}"
    for STUDY in "${STUDIES[@]}"
    do
        echo "*** Processing study: $STUDY"
        ID_STUDY=`echo $STUDY | cut -d "-" -f 1`
        NAME_STUDY=`echo $STUDY | cut -d "-" -f 2-3`
        CATEGORY_STUDY=`moosh category-create -p ${1} -v 1 -d "${ID_STUDY}" "${NAME_STUDY}"`
        case $STUDY in
            "639-ADG201 - Gestión Administrativa ")
                COURSES=( 
                        "5364-Comunicación empresarial y atención al cliente ( Distancia )" 
                        "5373-Formación y orientación laboral ( Distancia )" 
                        "5365-Operaciones administrativas de compra-venta ( Distancia )" 
                        "5368-Técnica contable ( Distancia )" 
                        "5367-Tratamiento informático de la información ( Distancia )" 
                        "5119-Empresa en el aula ( Distancia )" 
                        "5114-Empresa y Administración ( Distancia )" 
                        "5122-Formación en centros de trabajo ( Distancia )" 
                        "5111-Inglés  ( Distancia ) Global" 
                        "5117-Operaciones administrativas de recursos humanos ( Distancia )" 
                        "5120-Operaciones auxiliares de gestión de tesorería ( Distancia )" 
                        "5118-Tratamiento de la documentación contable ( Distancia )" 
                    )
            ;;
            "627-ADG301 - Administración y Finanzas")
                COURSES=( 
                        "5297-Comunicación y atención al cliente ( Distancia )"
                        "5403-Formación y Orientación Laboral ( Distancia )"
                        "5194-Gestión de la documentación jurídica y empresarial ( Distancia )"
                        "5193-Inglés ( Distancia )"
                        "5295-Ofimática y proceso de la información ( Distancia )"
                        "5296-Proceso integral de la actividad comercial ( Distancia )"
                        "5294-Recursos humanos y responsabilidad social corporativa ( Distancia )"
                        "5101-Contabilidad y fiscalidad ( Distancia )"
                        "5152-Formación en centros de trabajo ( Distancia )"
                        "5099-Gestión de recursos humanos ( Distancia )"
                        "5100-Gestión financiera ( Distancia )"
                        "5148-Gestión logística y comercial ( Distancia )"
                        "5150-Proyecto de administración y finanzas  ( Distancia )"
                        "5149-Simulación empresarial ( Distancia )"
                    )
            ;;
            "750-ADG302 - ADG302 - Asistencia a la Dirección")
                COURSES=(
                        "7855-Comunicación y atención al cliente ( Distancia )"
                        "8491-Formación y orientación laboral ( Distancia )"
                        "7851-Gestión de la documentación jurídica y empresarial ( Distancia )"
                        "7862-Inglés ( Distancia )"
                        "7853-Ofimática y proceso de la información ( Distancia )"
                        "7854-Proceso integral de la actividad comercial ( Distancia )"
                        "7852-Recursos humanos y responsabilidad social corporativa ( Distancia )"
                        "7861-Formación en centros de trabajo ( Distancia )"
                        "7871-Gestión avanzada de la información ( Distancia )"
                        "7870-Organización de eventos empresariales ( Distancia )"
                        "7869-Protocolo empresarial ( Distancia )"
                        "7872-Proyecto de asistencia a la dirección ( Distancia )"
                        "7863-Segunda lengua extranjera: Francés ( Distancia )"
                        "10793-Segunda lengua extranjera: Italiano"
                    )
            ;;
            "83-COM301 - Comercio Internacional")
                COURSES=(
                        "5417-Formación y Orientación Laboral ( Distancia )"
                        "5409-Gestión administrativa del comercio internacional ( Distancia )"
                        "5407-Gestión económica y financiera de la empresa ( Distancia )"
                        "5405-Inglés ( Distancia )"
                        "5408-Logística de almacenamiento ( Distancia )"
                        "5406-Transporte internacional de mercancías ( Distancia )"
                        "5163-Comercio digital internacional  ( Distancia )"
                        "5161-Financiación internacional ( Distancia )"
                        "5166-Formación en centros de trabajo ( Distancia )"
                        "5159-Marketing internacional ( Distancia )"
                        "5162-Medios de pago internacionales ( Distancia )"
                        "5160-Negociación internacional ( Distancia )"
                        "5164-Proyecto de comercio internacional ( Distancia )"
                        "5158-Sistema de información de mercados ( Distancia )"
                    )
            ;;
            "738-COM302 - Gestión de Ventas y Espacios Comerciales")
                COURSES=(
                        "8412-Formación y orientación laboral ( Distancia )"
                        "7909-Gestión económica y financiera de la empresa ( Distancia )"
                        "7913-Inglés ( Distancia )"
                        "7910-Logística de almacenamiento ( Distancia )"
                        "7908-Marketing digital ( Distancia )"
                        "7907-Políticas de marketing ( Distancia )"
                        "7917-Escaparatismo y diseño de espacios comerciales ( Distancia )"
                        "7916-Formación en centros de trabajo ( Distancia )"
                        "7918-Gestión de productos y promociones en el punto de venta ( Distancia )"
                        "7926-Investigación comercial ( Distancia )"
                        "7925-Logística de aprovisionamiento ( Distancia )"
                        "7919-Organización de equipos de ventas ( Distancia )"
                        "7928-Proyecto de gestión de ventas y espacios comerciales ( Distancia )"
                        "7920-Técnicas de venta y negociación ( Distancia )"
                    )
            ;;
            "85-COM303 - Transporte y Logística")
                COURSES=(
                        "5430-Formación y orientación laboral ( Distancia )"
                        "5426-Gestión administrativa del comercio internacional ( Distancia )"
                        "5422-Gestión económica y financiera de la empresa de transporte y logística ( Distancia )"
                        "5419-Inglés ( Distancia )"
                        "5424-Logística de almacenamiento ( Distancia )"
                        "5421-Transporte internacional de mercancías ( Distancia )"
                        "5198-Comercialización del transporte y la logística ( Distancia )"
                        "5206-Formación en centros de trabajo ( Distancia )"
                        "5195-Gestión administrativa del transporte y la logística ( Distancia )"
                        "5200-Logística de aprovisionamiento ( Distancia )"
                        "5203-Organización del transporte de mercancías ( Distancia )"
                        "5202-Organización del transporte de viajeros ( Distancia )"
                        "5204-Proyecto de transporte y logística ( Distancia )"
                    )
            ;;
            "624-ELE202 - Instalaciones Eléctricas y Automáticas")
                COURSES=(
                        "5335-Automatismos industriales ( Distancia )"
                        "5337-Electrotecnia ( Distancia )"
                        "5344-Formación y orientación laboral ( Distancia )"
                        "5338-Instalaciones eléctricas interiores. ( Distancia )"
                        "5342-Instalaciones solares fotovoltaicas ( Distancia )"
                        "4977-Electrónica ( Distancia )"
                        "4986-Empresa e iniciativa emprendedora ( Distancia )"
                        "4987-Formación en centros de trabajo ( Distancia )"
                        "4981-Infraestructuras comunes de telecomunicaciones en viviendas y edificios ( Distancia )"
                        "4980-Instalaciones de distribución ( Distancia )"
                        "4982-Instalaciones domóticas. ( Distancia )"
                        "4984-Máquinas eléctricas ( Distancia )"
                    )
            ;;
            "645-HOT301 - Agencias de Viajes y Gestión de Eventos")
                COURSES=( 
                        "5456-Destinos turísticos ( Distancia )"
                        "5463-Dirección de entidades de intermediación turística ( Distancia )"
                        "5447-Estructura del mercado turístico ( Distancia )"
                        "5458-Formación y orientación laboral ( Distancia )"
                        "5457-Recursos turísticos ( Distancia )"
                        "5234-Empresa e iniciativa emprendedora ( Distancia )"
                        "5235-Formación en centros de trabajo ( Distancia )"
                        "5236-Gestión de productos turísticos ( Distancia )"
                        "5225-Inglés  ( Distancia ) Global"
                        "5224-Marketing turístico ( Distancia )"
                        "5223-Protocolo y relaciones públicas ( Distancia )"
                        "5239-Proyecto de agencias de viajes y gestión de eventos ( Distancia )"
                        "5228-Segunda lengua extranjera: Francés ( Distancia ) Global"
                        "5237-Venta de servicios turísticos ( Distancia )"
                    )
            ;;
            "627-IFC201 - Sistemas Microinformáticos y Redes")
                COURSES=( 
                        "5349-Aplicaciones ofimáticas ( Distancia )"
                        "5355-Formación y orientación laboral ( Distancia )"
                        "5359-Lengua extranjera profesional: inglés 1 ( Distancia )"
                        "5347-Montaje y mantenimiento de equipos ( Distancia )"
                        "5351-Redes locales ( Distancia )"
                        "5348-Sistemas operativos monopuesto ( Distancia )"
                        "4995-Aplicaciones Web ( Distancia )"
                        "4997-Empresa e iniciativa emprendedora ( Distancia )"
                        "4998-Formación en centros de trabajo ( Distancia )"
                        "5001-Lengua extranjera profesional: inglés 2 ( Distancia )"
                        "4993-Seguridad informática ( Distancia )"
                        "4994-Servicios en red ( Distancia )"
                        "4991-Sistemas operativos en red ( Distancia )"
                    )
            ;;
            "643-IFC301 - Administración de Sistemas Informáticos en Red")
                COURSES=( 
                        "5283-Formación y orientación laboral  ( Distancia )"
                        "5274-Fundamentos de hardware.  ( Distancia )"
                        "5275-Gestión de bases de datos.  ( Distancia )"
                        "5272-Implantación de sistemas operativos.  ( Distancia )"
                        "5286-Lengua extranjera profesional: inglés 1  ( Distancia )"
                        "5276-Lenguajes de marcas y sistemas de gestión de información.  ( Distancia )"
                        "5273-Planificación y administración de redes.  ( Distancia )"
                        "5054-Administración de sistemas gestores de bases de datos. ( Distancia )"
                        "5051-Administración de sistemas operativos. ( Distancia )"
                        "5058-Empresa e iniciativa emprendedora ( Distancia )"
                        "5059-Formación en centros de trabajo ( Distancia )"
                        "5053-Implantación de aplicaciones web. ( Distancia )"
                        "5061-Lengua extranjera profesional: inglés 2 ( Distancia )"
                        "5056-Proyecto de administración de sistemas informáticos en red.  ( Distancia )"
                        "5055-Seguridad y alta disponibilidad. ( Distancia )"
                        "5052-Servicios de red e Internet. ( Distancia )"
                    )
            ;;
            "681-IFC302 - Desarrollo de Aplicaciones Multiplataforma")
                COURSES=( 
                        "5290-Bases de datos ( Distancia )"
                        "5293-Entornos de desarrollo ( Distancia )"
                        "5173-Formación y Orientación Laboral ( Distancia )"
                        "5176-Lengua Extranjera profesional: Inglés 1 ( Distancia )"
                        "5288-Lenguajes de marcas y sistemas de gestión de información ( Distancia )"
                        "5291-Programación ( Distancia )"
                        "5289-Sistemas informáticos ( Distancia )"
                        "5066-Acceso a datos ( Distancia )"
                        "5068-Desarrollo de interfaces ( Distancia )"
                        "5074-Empresa e iniciativa emprendedora ( Distancia )"
                        "5075-Formación en centros de trabajo ( Distancia )"
                        "5077-Lengua Extranjera profesional: Inglés 2 ( Distancia )"
                        "5070-Programación de servicios y procesos ( Distancia )"
                        "5069-Programación multimedia y dispositivos móviles ( Distancia )"
                        "5072-Proyecto de desarrollo de aplicaciones multiplataforma ( Distancia )"
                        "5071-Sistemas de gestión empresarial ( Distancia )"
                    )
            ;;
            "682-IFC303 - Desarrollo de Aplicaciones WEB")
                COURSES=( 
                        "5180-Bases de datos ( Distancia )"
                        "5182-Entornos de desarrollo ( Distancia )"
                        "5188-Formación y orientación laboral ( Distancia )"
                        "5191-Lengua Extranjera profesional: Inglés 1 ( Distancia )"
                        "5178-Lenguajes de marcas y sistemas de gestión de información ( Distancia )"
                        "5181-Programación ( Distancia )"
                        "5179-Sistemas informáticos ( Distancia )"
                        "5083-Desarrollo web  en entorno cliente ( Distancia )"
                        "5084-Desarrollo web  en entorno servidor ( Distancia )"
                        "5085-Despliegue de aplicaciones web ( Distancia )"
                        "5086-Diseño de interfaces Web ( Distancia )"
                        "5089-Empresa e iniciativa emprendedora ( Distancia )"
                        "5090-Formación en centros de trabajo ( Distancia )"
                        "5092-Lengua Extranjera profesional: Inglés 2 ( Distancia )"
                        "5087-Proyecto de desarrollo de aplicaciones Web ( Distancia )"
                    )
            ;;
            "745-IMS302 - Producción de Audiovisuales y Espectáculos ")
                COURSES=( 
                        "9333-Formación y orientación laboral ( Distancia )"
                        "7941-Lengua extranjera profesional: Inglés 1 ( Distancia )"
                        "7929-Medios técnicos audiovisuales y escénicos ( Distancia )"
                        "7930-Planificación de proyectos audiovisuales ( Distancia )"
                        "7933-Planificación de proyectos de espectáculos y eventos ( Distancia )"
                        "7935-Recursos expresivos audiovisuales y escénicos ( Distancia )"
                        "7950-Administración y promoción de audiovisuales y espectáculos ( Distancia )"
                        "7952-Empresa e iniciativa emprendedora ( Distancia )"
                        "7940-Formación en centros de trabajo ( Distancia )"
                        "7945-Gestión de proyectos de cine, video y multimedia ( Distancia )"
                        "7948-Gestión de proyectos de espectáculos y eventos ( Distancia )"
                        "7946-Gestión de proyectos de televisión y radio ( Distancia )"
                        "7942-Lengua extranjera profesional: Inglés 2 ( Distancia )"
                        "7951-Proyecto de producción de audiovisuales y espectáculos ( Distancia )"
                    )
            ;;
            "122-QUI301 - Laboratorio de Análisis y de Control de Calidad")
                COURSES=( 
                        "5256-Análisis químicos ( Distancia )"
                        "5259-Ensayos fisicoquímicos ( Distancia )"
                        "5260-Ensayos microbiológicos ( Distancia )"
                        "5266-Formación y orientación laboral ( Distancia )"
                        "5270-Lengua Extranjera profesional: inglés 1 ( Distancia )"
                        "5255-Muestreo y preparación de la muestra ( Distancia )"
                        "5031-Análisis instrumental ( Distancia )"
                        "5036-Calidad y seguridad en el laboratorio ( Distancia ) Global"
                        "5041-Empresa e iniciativa emprendedora ( Distancia )"
                        "5035-Ensayos biotecnológicos ( Distancia )"
                        "5032-Ensayos físicos ( Distancia )"
                        "5042-Formación en centros de trabajo ( Distancia )"
                        "5045-Lengua Extranjera profesional: inglés 2 ( Distancia )"
                        "5039-Proyecto de laboratorio de análisis y de control de calidad ( Distancia )"
                    )
            ;;
            "630-SAN202 - Farmacia y Parafarmacia")
                COURSES=( 
                        "5324-Anatomofisiología  y Patología básicas ( Distancia )"
                        "5327-Dispensación de productos farmacéuticos ( Distancia )"
                        "5325-Disposición y venta de productos ( Distancia )"
                        "5332-Formación y orientación laboral ( Distancia )"
                        "5326-Oficina de Farmacia ( Distancia )"
                        "5329-Operaciones básicas de laboratorio ( Distancia )"
                        "5323-Primeros auxilios ( Distancia )"
                        "4969-Dispensación de productos parafarmacéuticos ( Distancia )"
                        "4974-Empresa e iniciativa emprendedora ( Distancia )"
                        "4975-Formación en centros de trabajo ( Distancia )"
                        "4971-Formulación magistral ( Distancia )"
                        "4972-Promoción de la salud  ( Distancia )"
                    )
            ;;
            "618-SAN203 - Emergencias Sanitarias")
                COURSES=( 
                        "5319-Anatomofisiología y patología básicas ( Distancia )"
                        "5316-Apoyo psicológico en situaciones de emergencia ( Distancia )"
                        "5313-Atención sanitaria inicial en situaciones de emergencia ( Distancia )"
                        "5312-Dotación sanitaria ( Distancia )"
                        "5315-Evacuación y traslado de pacientes ( Distancia )"
                        "5320-Formación y orientación laboral ( Distancia )"
                        "5310-Mantenimiento mecánico preventivo del vehículo ( Distancia )"
                        "4955-Atención sanitaria especial en situaciones de emergencia ( Distancia )"
                        "4962-Empresa e iniciativa emprendedora ( Distancia )"
                        "4963-Formación en centros de trabajo ( Distancia )"
                        "4952-Logística sanitaria en emergencias ( Distancia )"
                        "4958-Planes de emergencia y dispositivos de riesgos previsibles ( Distancia )"
                        "4959-Tele emergencia ( Distancia )"
                    )
            ;;
            "687-SSC201 - Atención a Personas en situación de Dependencia")
                COURSES=( 
                        "5381-Apoyo domiciliario ( Distancia )"
                        "5382-Atención sanitaria ( Distancia )"
                        "5379-Atención y apoyo psicosocial ( Distancia )"
                        "5378-Características y necesidades de las personas en situación de dependencia ( Distancia )"
                        "5384-Formación y orientación laboral ( Distancia )"
                        "5375-Primeros auxilios ( Distancia )"
                        "5128-Apoyo a la comunicación ( Distancia )"
                        "5131-Atención higiénica ( Distancia )"
                        "5125-Destrezas sociales ( Distancia )"
                        "5133-Empresa e iniciativa emprendedora ( Distancia )"
                        "5134-Formación en centros de trabajo ( Distancia )"
                        "5124-Organización de la atención a las personas en situación de dependencia ( Distancia )"
                        "5135-Teleasistencia ( Distancia )"
                    )
            ;;
            "140-SSC302 - Educación Infantil (Formación Profesional)")
                COURSES=( 
                        "5433-Autonomía personal y salud infantil. ( Distancia )"
                        "5436-Desarrollo cognitivo y motor. ( Distancia )"
                        "5432-Didáctica de la Educación Infantil. ( Distancia )"
                        "5434-El juego infantil y su metodología. ( Distancia )"
                        "5442-Formación y orientación laboral. ( Distancia )"
                        "5445-Lengua extranjera profesional: inglés 1 ( Distancia )"
                        "5441-Primeros auxilios. ( Distancia )"
                        "5212-Desarrollo socioafectivo. ( Distancia )"
                        "5218-Empresa e iniciativa emprendedora. ( Distancia )"
                        "5210-Expresión y comunicación. ( Distancia )"
                        "5219-Formación en centros de trabajo ( Distancia )"
                        "5213-Habilidades sociales. ( Distancia )"
                        "5214-Intervención con familias y atención a menores en riesgo social. ( Distancia )"
                        "5221-Lengua extranjera profesional: inglés 2 ( Distancia )"
                        "5215-Proyecto de atención a la infancia. ( Distancia )"
                    )
            ;;
            "768-SSC303 - Integración Social")
                COURSES=( 
                        "7878-Apoyo a la intervención educativa ( Distancia )"
                        "7874-Contexto de la intervención social ( Distancia )"
                        "8339-Formación y orientación laboral ( Distancia )"
                        "7875-Inserción sociolaboral ( Distancia )"
                        "7884-Lengua extranjera profesional: Inglés 1 ( Distancia )"
                        "7877-Mediación comunitaria ( Distancia )"
                        "7882-Primeros auxilios ( Distancia )"
                        "7879-Promoción de la autonomía personal ( Distancia )"
                        "7892-Atención a las unidades de convivencia ( Distancia )"
                        "7902-Empresa e iniciativa emprendedora ( Distancia )"
                        "7889-Formación en centros de trabajo ( Distancia )"
                        "7793-Formación y orientación laboral  ( Nocturno, 2º )"
                        "7899-Habilidades sociales ( Distancia )"
                        "7885-Lengua extranjera profesional: Inglés 2 ( Distancia )"
                        "7897-Metodología de la intervención social ( Distancia )"
                        "7901-Proyecto de integración social ( Distancia )"
                        "7896-Sistemas aumentativos y alternativos de comunicación ( Distancia )"
                    )
            ;;
        esac
        # set courses to category
        set_modules_to_study $CATEGORY_STUDY $COURSES
        # set format topics to all courses of the category
        echo "Cambio la configuración de los cursos de la categoría ${CATEGORY_STUDY} a formato topics"
        moosh course-config-set category ${CATEGORY_STUDY} format topics
    done    
}

set_modules_to_study(){
    echo "**** Setting courses ${2} to category ${1}"
    for COURSE in "${COURSES[@]}"
    do
        COD_ENSENANZA=`echo "${COURSE}" | cut -d '-' -f 1`
        if [ ! -f "/var/www/moodledata/repository/cursosministerio/${COD_ENSENANZA}.mbz" ]; then
            echo "***** The course /var/www/moodledata/repository/cursosministerio/${COD_ENSENANZA}.mbz doesn't exist"
        else
            echo "***** Loading /var/www/moodledata/repository/cursosministerio/${COD_ENSENANZA}.mbz course to category ${1}"
            COURSE_ID=`moosh course-restore /var/www/moodledata/repository/cursosministerio/${COD_ENSENANZA}.mbz ${1}`
            COURSE_ID=`echo "${COURSE_ID}" | tail -n 1 | cut -d ':' -f 2 | cut -d ' ' -f 2`
            moosh course-config-set course ${COURSE_ID} shortname to-do
            moosh course-config-set course ${COURSE_ID} fullname "${COURSE}"
        fi
        
    done    
}

# main
for CENTRE in "${CENTRES[@]}"
do
    echo "* Processing centre: $CENTRE"
    ID_CENTRE=`echo "${CENTRE}" | cut -d '-' -f 1`
    NAME_CENTRE=`echo "${CENTRE}" | cut -d '-' -f 2`
    CATEGORY_IES=`moosh category-create -p 0 -v 1 -d "${ID_CENTRE}" "${NAME_CENTRE}"`
    case $CENTRE in
        "22002521-IES SIERRA DE GUARA")
            STUDIES=( "639-ADG201 - Gestión Administrativa " )
        ;;
        "44003211-IES SANTA EMERENCIANA")
            STUDIES=("639-ADG201 - Gestión Administrativa ")
        ;;
        "50010511-IES TIEMPOS MODERNOS")
            STUDIES=("639-ADG201 - Gestión Administrativa ")
        ;;
        "50010314-CPIFP LOS ENLACES")
            STUDIES=( 
                    "627-IFC201 - Sistemas Microinformáticos y Redes" 
                    "738-COM302 - Gestión de Ventas y Espacios Comerciales" 
                    "85-COM303 - Transporte y Logística" 
                    "682-IFC303 - Desarrollo de Aplicaciones WEB" 
                    "745-IMS302 - Producción de Audiovisuales y Espectáculos " 
                )
        ;;
        "50018829-CPIFP CORONA DE ARAGÓN")
            STUDIES=( 
                    "83-COM301 - Comercio Internacional"
                    "750-ADG302 - Asistencia a la Dirección" 
                    "79-ADG301 - Administración y Finanzas" 
                    "122-QUI301 - Laboratorio de Análisis y de Control de Calidad" 
                )
        ;;
        "22010712-CPIFP PIRÁMIDE")
            STUDIES=( 
                    "624-ELE202 - Instalaciones Eléctricas y Automáticas"
                )
        ;;
        "50010156-IES MIRALBUENO")
            STUDIES=( 
                    "645-HOT301 - Agencias de Viajes y Gestión de Eventos"
                )
        ;;
        "50010144-IES PABLO SERRANO")
            STUDIES=( 
                    "643-IFC301 - Administración de Sistemas Informáticos en Red"
                )
        ;;
        "44010537-CPIFP BAJO ARAGÓN")
            STUDIES=( 
                    "681-IFC302 - Desarrollo de Aplicaciones Multiplataforma"
                )
        ;;
        "50009567-IES RÍO GÁLLEGO")
            STUDIES=( 
                    "630-SAN202 - Farmacia y Parafarmacia"
                    "618-SAN203 - Emergencias Sanitarias"
                )
        ;;
        "44003235-IES VEGA DEL TURIA")
            STUDIES=( 
                    "618-SAN203 - Emergencias Sanitarias"
                )
        ;;
        "50008460-IES LUIS BUÑUEL")
            STUDIES=( 
                    "687-SSC201 - Atención a Personas en situación de Dependencia"
                )
        ;;
        "22002491-CPIFP MONTEARAGON")
            STUDIES=( 
                    "687-SSC201 - Atención a Personas en situación de Dependencia"
                )
        ;;
        "22004611-IES MARTÍNEZ VARGAS")
            STUDIES=( 
                    "140-SSC302 - Educación Infantil (Formación Profesional)"
                )
        ;;
        "50009348-IES AVEMPACE")
            STUDIES=( 
                    "140-SSC302 - Educación Infantil (Formación Profesional)"
                )
        ;;
        "50008642-IES MARÍA MOLINER")
            STUDIES=( 
                    "768-SSC303 - Integración Social"
                )
        ;;
    esac
    # set studies category to centre category
    set_studies_to_centre $CATEGORY_IES $STUDIES
done