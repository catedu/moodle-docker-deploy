<?php
    require_once(__DIR__ . '/../config.php');
    require_once('secret.php');
    //////////////////////////////
    // Funciones
    //////////////////////////////
    function asignarIncidenciaA($rol, $motivo, $ciclo){
        echo 'rol: ' . $rol . ', motivo: ' . $motivo . ', ciclo: ' . $ciclo . ', codeCoordinacion: ' . $codeCoordinacion . ', idUserAdmin: ' . $idUserAdmin . ', $userRedmine: '. $userRedmine .'<br/>';
        if($motivo == "1"){//"Plataforma caída";
            return $idUserAdmin;
        }
        // 2-"Acceso a plataforma. Problemas con el usuario y contraseña";
        // 3-"Acceso a los contenidos o módulos";
        elseif($motivo == "2" || $motivo == "3" ){
            switch ($rol) {
                case "CPIFP Bajo Aragón : Desarrollo de Aplicaciones Multiplataforma":
                    return $idUserCoordinacion_CPIFP_Bajo_Aragon_DAM;
                    break;
                case "CPIFP Corona de Aragón : Administración y Finanzas (ADFI)":
                    return $idUserCoordinacion_CPIFP_Corona_de_Aragon_ADFI;
                    break;
                case "CPIFP Corona de Aragón : Laboratorio de Análisis y de Control de Calidad (LACC)":
                    return $idUserCoordinacion_CPIFP_Corona_de_Aragon_LACC;
                    break;
                case "CPIFP Corona de Aragón : Asistencia a la dirección":
                    return $idUserCoordinacion_CPIFP_Corona_de_Aragon_AD;
                    break;
                case "CPIFP Corona de Aragón : Oferta Parcial":
                    return $idUserCoordinacion_CPIFP_Corona_de_Aragon_OP;
                    break;
                case "CPIFP Los Enlaces : Comercio Internacional":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_CI;
                    break;
                case "CPIFP Los Enlaces : Desarrollo de Aplicaciones Web (DAW)":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_DAW;
                    break;
                case "CPIFP Los Enlaces : Sistemas Microinformáticos (SMR)":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_SMR;
                    break;
                case "CPIFP Los Enlaces : Transporte y Logística (TL)":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_TL;
                    break;
                case "CPIFP Los Enlaces : Gestión de ventas y espacios comerciales":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_GVEC;
                    break;
                case "CPIFP Los Enlaces : Producción de audiovisuales y espectáculos":
                    return $idUserCoordinacion_CPIFP_Los_Enlaces_PAE;
                    break;
                case "CPIFP Montearagón : Atención a Personas en Situación de Dependencia (APSD)_IES Montearagón":
                    return $idUserCoordinacion_CPIFP_Montearagon_APSD;
                    break;
                case "CPIFP Montearagón : Oferta Parcial  Atención a Personas en Situación de Dependencia (APSD)":
                    return $idUserCoordinacion_CPIFP_Montearagon_OP;
                    break;
                case "CPIFP Pirámide : Instalaciones Eléctricas y Automáticas":
                    return $idUserCoordinacion_CPIFP_Piramide_IAE;
                    break;
                case "CPIFP Pirámide : Oferta_Parcial_IEA":
                    return $idUserCoordinacion_CPIFP_Piramide_OP;
                    break;
                case "IES Avempace : Educación Infantil (IES Avempace)":
                    return $idUserCoordinacion_IES_Avempace_EI;
                    break;
                case "IES Luis Buñuel : Atención a Personas en Situación de Dependencia_(APSD)_IES Luis Buñuel":
                    return $idUserCoordinacion_IES_Luis_Bunuel_APSD;
                    break;
                case "IES Luis Buñuel : Oferta_Parcial_ASPSD":
                    return $idUserCoordinacion_IES_Luis_Bunuel_OP;
                    break;
                case "IES María Moliner : Integración social":
                    return $idUserCoordinacion_IES_Maria_Moliner_IS;
                    break;
                case "IES Martínez Vargas  : Educación Infantil (IES Martínez Vargas)":
                    return $idUserCoordinacion_IES_Martinez_Vargas_EI;
                    break;
                case "IES Miralbueno : Agencias de viajes y gestión de eventos(AVGE)":
                    return $idUserCoordinacion_IES_Miralbueno_AVGE;
                    break;
                case "IES Pablo Serrano : Administración de Sistemas Informáticos en Red (ASIR)":
                    return $idUserCoordinacion_IES_Pablo_Serrano_ASIR;
                    break;
                case "IES Río Gállego : Farmacia y Parafarmacia":
                    return $idUserCoordinacion_IES_Rio_Gallego_FP;
                    break;
                case "IES Río Gállego : Emergencias Sanitarias":
                    return $idUserCoordinacion_IES_Rio_Gallego_ES;
                    break;
                case "IES Río Gállego : Oferta Parcial Farmacia y Parafarmacia":
                    return $idUserCoordinacion_IES_Rio_Gallego_OP;
                    break;
                case "IES Santa Emerenciana : Gestión Administrativa IES Santa Emerenciana":
                    return $idUserCoordinacion_IES_Santa_Emerenciana_GA;
                    break;
                case "IES Santa Emerenciana : Oferta Parcial Gestión Administrativa IES Santa Emerenciana":
                    return $idUserCoordinacion_IES_Santa_Emerenciana_OP;
                    break;
                case "IES Sierra de Guara : Gestión Administrativa IES Sierra de Guara":
                    return $idUserCoordinacion_IES_Sierra_de_Guara_GA;
                    break;
                case "IES Tiempos Modernos : Gestión Administrativa IES Tiempos Modernos":
                    return $idUserCoordinacion_IES_Tiempos_Modernos_GA;
                    break;
                case "IES Vega del Turia: Emergencias sanitarias":
                    return $idUserCoordinacion_IES_Vega_del_Turia_ES;
                    break;
                case "IES Vega del Turia: Oferta Parcial Emergencias Sanitarias":
                    return $idUserCoordinacion_IES_Vega_del_Turia_OP;
                    break;
            }
        }
        // 4-"Dar de alta/baja profesorado";
        // 5-"Otros";
        elseif($motivo == "4" || $motivo == "5" ){//
            return $idUserFP;
        }
        //Por defecto lo envío a FP y que ellos decidan
        return $idUserFP;
    }

    function procesaRol($rol){
        switch ($rol) {
            case "e":
                return "Estudiante";
                break;
            case "p":
                return "Profesorado";
                break;
            case "c":
                return "Coordinadores/as";
                break;
            case "o":
                return "Otros";
                break;
            default:
                return "ERROR";
        }
    }

    function procesaMotivo($motivo){
        switch ($motivo) {
            case "1":
                return "Plataforma caída";
                break;
            case "2":
                return "Acceso a plataforma. Problemas con el usuario y contraseña";
                break;
            case "3":
                return "Acceso a los contenidos o módulos";
                break;
            case "4":
                return "Dar de alta/baja profesorado";
                break;
            case "5":
                return "Otros";
                break;
            default:
                return "ERROR";
        }
    }
    //////////////////////////////
    // Recojo parámetros del form
    //////////////////////////////
    $rol = htmlspecialchars($_POST["rol"]);
    $nombre_solicitante = htmlspecialchars($_POST["nombre_solicitante"]);
    $pape_solicitante = htmlspecialchars($_POST["pape_solicitante"]);
    $sape_solicitante = htmlspecialchars($_POST["sape_solicitante"]);
    $email_solicitante = htmlspecialchars($_POST["email_solicitante"]);
    $ciclo = htmlspecialchars($_POST["ciclo"]);
    $motivo = htmlspecialchars($_POST["motivo"]);
    // docente a crear
    $cod_coordinacion = htmlspecialchars($_POST["cod_coordinacion"]);
    $nombre_docente = htmlspecialchars($_POST["nombre_docente"]);
    $pape_docente = htmlspecialchars($_POST["pape_docente"]);
    $sape_docente = htmlspecialchars($_POST["sape_docente"]);
    $dni_docente = htmlspecialchars($_POST["dni_docente"]);
    $email_docente = htmlspecialchars($_POST["email_docente"]);
    $modulo1_docente = htmlspecialchars($_POST["modulo1_docente"]);
    $modulo2_docente = htmlspecialchars($_POST["modulo2_docente"]);
    $modulo3_docente = htmlspecialchars($_POST["modulo3_docente"]);
    $otros_docente = htmlspecialchars($_POST["otros_docente"]);

    //////////////////////////////
    // Si se quiere crear/borrar un nuevo docente hay que comprobar si
    //////////////////////////////
    $accesoPermitido = true;
    if ($rol == "c" && $motivo == "4") {
        if($cod_coordinacion != $codeCoordinacion){
            $accesoPermitido = false;
        }
    }

    //////////////////////////////
    // Creo variables iniciales
    //////////////////////////////
    if($accesoPermitido){
        $date = date('d-m-Y H:i:s');

        $body = 'Hola ' . $nombre_solicitante . ',<br/>';
        $body .= 'su incidencia realizada el ' . $date . ' ha sido recogida en nuestro sistema. La misma contiene la siguiente información:<br/>';
        $body .= '<ul>';
        $body .= '<li><b>Rol</b>: ' . procesaRol($rol) . '</li>';
        $body .= '<li><b>Nombre solicitante</b>: ' . $nombre_solicitante . '</li>';
        $body .= '<li><b>1er apellido solicitante</b>: ' . $pape_solicitante . '</li>';
        $body .= '<li><b>2º apellido solicitante</b>: ' . $sape_solicitante . '</li>';
        $body .= '<li><b>E-mail solicitante</b>: ' . $email_solicitante . '</li>';
        $body .= '<li><b>Ciclo</b>: ' . $ciclo . '</li>';
        $body .= '<li><b>Motivo/Problema</b>: ' . procesaMotivo($motivo) . '</li>';
        if ($rol == "c" && $motivo == "4") {
            $body .= '<li><b>Nombre de docente a crear</b>: ' . $nombre_docente . '</li>';
            $body .= '<li><b>1er apellido de docente a crear</b>: ' . $pape_docente . '</li>';
            $body .= '<li><b>2º apellido de docente a crear</b>: ' . $sape_docente . '</li>';
            $body .= '<li><b>DNI/NIE de docente a crear</b>: ' . $dni_docente . '</li>';
            $body .= '<li><b>E-mail de docente a crear</b>: ' . $email_docente . '</li>';
            $body .= '<li><b>Módulo 1</b>: ' . $modulo1_docente . '</li>';
            $body .= '<li><b>Módulo 2</b>: ' . $modulo2_docente . '</li>';
            $body .= '<li><b>Módulo 3</b>: ' . $modulo3_docente . '</li>';
            $body .= '<li><b>Otros</b>: ' . $otros_docente . '</li>';
        }
        $body .= '</ul>';
        $body .= 'No conteste a este correo electrónico puesto que se trata de una cuenta desatendida y automatizada<br/>';
        $body .= 'Saludos<br/><br/>';
        $body .= 'FP distancia Aragón';
    //////////////////////////////
    // Contacto con RedMine para crear la incidencia
    //////////////////////////////

        $url = "https://soportearagon.catedu.es/issues.json";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($curl, CURLOPT_POST, 1);
        $asignarA = asignarIncidenciaA($rol, $motivo, $ciclo);
        echo '$asignarA: ' . $asignarA . '</br>';
        $issue =  '
        <?xml version="1.0"?>
        <issue>
        <project_id>'.$projectId.'</project_id>
        <subject>PROBANDO ANDO: '.procesaMotivo($motivo).'</subject>
        <description>'.$body.'</description>
        <priority_id>2</priority_id>
        <custom_fields type="array">
            <custom_field id="1" name="owner-email">
                <value>'.$email_solicitante.'</value>
            </custom_field>
        </custom_fields>
        <assigned_to_id>'. $asignarA .'</assigned_to_id>
        </issue>';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $issue );
        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $userRedmine.":".$passRedmine);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        //echo 'resultado: ' . $result . '<br/><br/>';
        $respuesta = json_decode($result, true);
        $incidenciaCreada = $respuesta["issue"];
        //echo '$incidenciaCreada: '. $incidenciaCreada;
        $incidenciaCreadaId = $incidenciaCreada["id"];
        //echo '$incidenciaCreadaId: '. $incidenciaCreadaId;

        $exitoCreandoIncidencia = false;
        if (isset($incidenciaCreadaId) && $incidenciaCreadaId !== '') {
            $exitoCreandoIncidencia = true;
        }

        //////////////////////////////
        // Envío email al usuario con copia de su solicitud original
        //////////////////////////////
        if( $exitoCreandoIncidencia ){
            
            $toUser = new stdClass();
            $toUser->email = "fp@catedu.es";
            $toUser->firstname = $nombre_solicitante;
            $toUser->lastname = $pape_solicitante;
            $toUser->maildisplay = true;
            $toUser->id = -99; 
            
            $subject = 'Nueva incidencia - FP a distancia Aragón';
            
            $body = 'Hola ' . $nombre_solicitante . ',<br/>';
            $body .= 'su incidencia realizada el ' . $date . ' ha sido recogida en nuestro sistema con el id <strong>'. $incidenciaCreadaId .'</strong>. La misma contiene la siguiente información:<br/>';
            $body .= '<ul>';
            $body .= '<li><b>Rol</b>: ' . procesaRol($rol) . '</li>';
            $body .= '<li><b>Nombre solicitante</b>: ' . $nombre_solicitante . '</li>';
            $body .= '<li><b>1er apellido solicitante</b>: ' . $pape_solicitante . '</li>';
            $body .= '<li><b>2º apellido solicitante</b>: ' . $sape_solicitante . '</li>';
            $body .= '<li><b>E-mail solicitante</b>: ' . $email_solicitante . '</li>';
            $body .= '<li><b>Ciclo</b>: ' . $ciclo . '</li>';
            $body .= '<li><b>Motivo/Problema</b>: ' . procesaMotivo($motivo) . '</li>';
            if ($rol == "c" && $motivo == "4") {
                $body .= '<li><b>Nombre de docente a crear</b>: ' . $nombre_docente . '</li>';
                $body .= '<li><b>1er apellido de docente a crear</b>: ' . $pape_docente . '</li>';
                $body .= '<li><b>2º apellido de docente a crear</b>: ' . $sape_docente . '</li>';
                $body .= '<li><b>DNI/NIE de docente a crear</b>: ' . $dni_docente . '</li>';
                $body .= '<li><b>E-mail de docente a crear</b>: ' . $email_docente . '</li>';
                $body .= '<li><b>Módulo 1</b>: ' . $modulo1_docente . '</li>';
                $body .= '<li><b>Módulo 2</b>: ' . $modulo2_docente . '</li>';
                $body .= '<li><b>Módulo 3</b>: ' . $modulo3_docente . '</li>';
                $body .= '<li><b>Otros</b>: ' . $otros_docente . '</li>';
            }
            $body .= '</ul>';
            $body .= 'No conteste a este correo electrónico puesto que se trata de una cuenta desatendida y automatizada<br/>';
            $body .= 'Saludos<br/><br/>';
            $body .= 'FP distancia Aragón';

            $fromUser = new stdClass();
            $fromUser->firstname = null;
            $fromUser->lastname = null;
            $fromUser->email = '<>';
            $fromUser->maildisplay = true;
            $fromUser->id = -99;

            $exitoEnviandoEmail = email_to_user($toUser, $fromUser, $subject, $body);
        }
    }
?>

<!DOCTYPE html>
    <html  dir="ltr" lang="es" xml:lang="es">
    <head>
        <title>FP a distancia - Aragón</title>
        <link rel="shortcut icon" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/pluginfile.php/1/theme_moove/favicon/1615997395/FAVICON11.ico" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="moodle, FP a distancia - Aragón" />
        <link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/yui_combo.php?rollup/3.17.2/yui-moodlesimple-min.css" /><script id="firstthemesheet" type="text/css">/** Required in order to fix style inclusion problems in IE with YUI **/</script><link rel="stylesheet" type="text/css" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/styles.php/moove/1615997395_1/all" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
        <link rel="stylesheet" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/theme/moove/style/aragon/aragon-wrapper.css" type="text/css">
        
        
    </head>
    <body  id="page-site-index" class="format-site course path-site chrome dir-ltr lang-es yui-skin-sam yui3-skin-sam test-adistanciafparagon-es pagelayout-frontpage course-1 context-2 notloggedin ">
        <section  class="ita-sectionguia aragob_header_wrapper">
            <!-- aragob bar  -->
            <div class="aragob_header"></div>
            <!-- fin aragon bar -->
        </section>
        <nav class="navbar nav-inicio">
        </nav>
        <div id="page" class="container-fluid">
            <div id="page-header" class="frontpage-guest-header">
                <div class="d-flex flex-wrap">
                    <div id="page-navbar">
                        <nav>
                            <ol class="breadcrumb"><li class="breadcrumb-item"><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/" >Página Principal</a></li>
                                <li class="breadcrumb-item"><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/soporte/" >Soporte</a></li>
                            </ol>    
                        </nav>
                    </div>
                </div>
                <div id="page-content" class="row pb-3 d-print-block">
                    <div id="region-main-box" class="col-12">
                        <section id="region-main">
                            <div class="card">
                                <div class="card-body">
                                    <!--span class="notifications" id="user-notifications"></span-->
                                    <div role="main">
                                        <span id="maincontent"></span>
                                        <div class="settingsform">
<?php
    //////////////////////////////
    // comprobaciones para informar a los usuarios del éxito/fallo de su comunicación
    //////////////////////////////
    $h3 = '';
    if( !$accesoPermitido ){
        $h3 = 'Acceso no permitido a coordinación. Solicite la clave al departamento';
    }elseif($exitoCreandoIncidencia && $exitoEnviandoEmail){
        $h3 =  'Incidencia ' . $incidenciaCreadaId . ' creada. Se le ha enviado un email con copia de la misma.';
    }elseif ($exitoCreandoIncidencia && !$exitoEnviandoEmail) {
        $h3 =  'Incidencia ' . $incidenciaCreadaId . ' creada pero ha fallado el envío de un email a su cuenta con copia de la misma. NO se le podrá comunicar la resolución de la misma o realizar consultas adicionales.';
    }else{
        $h3 =  'Ha fallado la creación de la incidencia. Vuelva a intentarlo.';
    }
?>
                                            <h2>Soporte</h2>
                                            <h3><?php echo $h3 ?></h3>
                                            <p>La información recogida es la siguiente:</p>
                                            <ul>
                                                <li><b>Rol</b>: <?php echo procesaRol($rol); ?></li>
                                                <li><b>Nombre solicitante</b>: <?php echo htmlentities($nombre_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>1er apellido solicitante</b>: <?php echo htmlentities($pape_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>2º apellido solicitante</b>: <?php echo htmlentities($sape_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>E-mail solicitante</b>: <?php echo htmlentities($email_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Ciclo</b>: <?php echo htmlentities($ciclo, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Motivo/Problema</b>: <?php echo procesaMotivo($motivo); ?></li>
<?php
if ($rol == "c" && $motivo == "4") {
?>
                                                <li><b>Nombre de docente a crear</b>: <?php echo htmlentities($nombre_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>1er apellido de docente a crear</b>: <?php echo htmlentities($pape_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>2º apellido de docente a crear</b>: <?php echo htmlentities($sape_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>DNI/NIE de docente a crear</b>: <?php echo htmlentities($dni_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>E-mail de docente a crear </b>: <?php echo htmlentities($email_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Módulo 1</b>: <?php echo htmlentities($modulo1_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Módulo 2</b>: <?php echo htmlentities($modulo2_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Módulo 3</b>: <?php echo htmlentities($modulo3_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li><b>Otros</b>: <?php echo htmlentities($otros_docente, ENT_QUOTES, "UTF-8"); ?></li>
<?php
}
?>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- end of .card-body -->
                            </div> <!-- card -->
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <script>
            
        </script>
    </body>
</html>