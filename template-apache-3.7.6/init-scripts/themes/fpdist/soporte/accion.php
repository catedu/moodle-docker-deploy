<?php
    session_start();

    require_once(__DIR__ . '/../config.php');
    require_once('secret.php');

    $captchaCorrecto = FALSE;

    if(isset($_POST['captcha_challenge']) && $_POST['captcha_challenge'] == $_SESSION['captcha_text']) {
        $captchaCorrecto = TRUE;
    }else{
        $captchaCorrecto = FALSE;
    }

    //////////////////////////////
    // Funciones
    //////////////////////////////
    function getIPAddress() {  
        //whether ip is from the share internet  
         if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
                    $ip = $_SERVER['HTTP_CLIENT_IP'];  
            }  
        //whether ip is from the proxy  
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
         }  
    //whether ip is from the remote address  
        else{  
                 $ip = $_SERVER['REMOTE_ADDR'];  
         }  
         return $ip;  
    }  
    
    function asignarIncidenciaA($rol, $motivo, $ciclo){
              
        if($motivo == "1"){//"Plataforma caída";
            return $GLOBALS["idUserAdmin"];
        }
        // 2-"Acceso a plataforma. Problemas con el usuario y contraseña";
        // 3-"Acceso a los contenidos o módulos";
        elseif($motivo == "2" || $motivo == "3" ){
            switch ($ciclo) {
                case "CPIFP Bajo Aragón: Desarrollo de Aplicaciones Multiplataforma":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Bajo_Aragon_DAM"];
                    break;
                case "CPIFP Corona de Aragón: Administración y Finanzas":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Corona_de_Aragon_ADFI"];
                    break;
                case "CPIFP Corona de Aragón: Laboratorio de Análisis y de Control de Calidad":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Corona_de_Aragon_LACC"];
                    break;
                case "CPIFP Corona de Aragón: Asistencia a la dirección":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Corona_de_Aragon_AD"];
                    break;
                case "CPIFP Los Enlaces: Comercio Internacional":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_CI"];
                    break;
                case "CPIFP Los Enlaces: Desarrollo de Aplicaciones Web":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_DAW"];
                    break;
                case "CPIFP Los Enlaces: Sistemas Microinformáticos":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_SMR"];
                    break;
                case "CPIFP Los Enlaces: Transporte y Logística":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_TL"];
                    break;
                case "CPIFP Los Enlaces: Gestión de ventas y espacios comerciales":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_GVEC"];
                    break;
                case "CPIFP Los Enlaces: Producción de audiovisuales y espectáculos":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Los_Enlaces_PAE"];
                    break;
                case "CPIFP Montearagón: Atención a Personas en Situación de Dependencia":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Montearagon_APSD"];
                    break;
                case "CPIFP Pirámide: Instalaciones Eléctricas y Automáticas":
                    return $GLOBALS["idUserCoordinacion_CPIFP_Piramide_IAE"];
                    break;
                case "IES Avempace: Educación Infantil":
                    return $GLOBALS["idUserCoordinacion_IES_Avempace_EI"];
                    break;
                case "IES Luis Buñuel: Atención a Personas en Situación de Dependencia":
                    return $GLOBALS["idUserCoordinacion_IES_Luis_Bunuel_APSD"];
                    break;
                case "IES María Moliner: Integración social":
                    return $GLOBALS["idUserCoordinacion_IES_Maria_Moliner_IS"];
                    break;
                case "IES Martínez Vargas: Educación Infantil":
                    return $GLOBALS["idUserCoordinacion_IES_Martinez_Vargas_EI"];
                    break;
                case "IES Miralbueno: Agencias de viajes y gestión de eventos":
                    return $GLOBALS["idUserCoordinacion_IES_Miralbueno_AVGE"];
                    break;
                case "IES Pablo Serrano: Administración de Sistemas Informáticos en Red":
                    return $GLOBALS["idUserCoordinacion_IES_Pablo_Serrano_ASIR"];
                    break;
                case "IES Río Gállego: Farmacia y Parafarmacia":
                    return $GLOBALS["idUserCoordinacion_IES_Rio_Gallego_FP"];
                    break;
                case "IES Río Gállego: Emergencias Sanitarias":
                    return $GLOBALS["idUserCoordinacion_IES_Rio_Gallego_ES"];
                    break;
                case "CPIFP San Blas: Educación y Control Ambiental":
                    return $GLOBALS["idUserCoordinacion_CPIFP_San_Blas_ECA"];
                    break;
                case "IES Santa Emerenciana: Gestión Administrativa":
                    return $GLOBALS["idUserCoordinacion_IES_Santa_Emerenciana_GA"];
                    break;
                case "IES Sierra de Guara: Gestión Administrativa":
                    return $GLOBALS["idUserCoordinacion_IES_Sierra_de_Guara_GA"];
                    break;
                case "IES Tiempos Modernos: Gestión Administrativa":
                    return $GLOBALS["idUserCoordinacion_IES_Tiempos_Modernos_GA"];
                    break;
                case "IES Vega del Turia: Emergencias sanitarias":
                    return $GLOBALS["idUserCoordinacion_IES_Vega_del_Turia_ES"];
                    break;
            }
        }
        // 4-"Dar de alta/baja profesorado";
        // 5-"Otros";
        elseif($motivo == "4" || $motivo == "5" || $motivo == "6" ){//
            return $GLOBALS["idUserFP"];
        }
        //Por defecto lo envío a FP y que ellos decidan
        return $GLOBALS["idUserFP"];
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
            case "6":
                return "Cambio/Actualización de materiales";
                break;
            default:
                return "ERROR";
        }
    }

    function procesaPrioridad($motivo){
        switch ($motivo) {
            case "1": //Plataforma caída
                return "2";
                break;
            case "2"://Acceso a plataforma. Problemas con el usuario y contraseña
                return "2";
                break;
            case "3"://Acceso a los contenidos o módulos
                return "2";
                break;
            case "4"://Dar de alta/baja profesorado
                return "3";
                break;
            case "5"://Otros
                return "2";
                break;
            case "6"://Cambio/Actualización de materiales
                return "2";
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
    $tipo_modificacion = htmlspecialchars($_POST["tipo_modificacion"]);
    $nombre_docente = htmlspecialchars($_POST["nombre_docente"]);
    $pape_docente = htmlspecialchars($_POST["pape_docente"]);
    $sape_docente = htmlspecialchars($_POST["sape_docente"]);
    $dni_docente = htmlspecialchars($_POST["dni_docente"]);
    $email_docente = htmlspecialchars($_POST["email_docente"]);
    $modulo1_docente = htmlspecialchars($_POST["modulo1_docente"]);
    $modulo2_docente = htmlspecialchars($_POST["modulo2_docente"]);
    $modulo3_docente = htmlspecialchars($_POST["modulo3_docente"]);
    //correcciones materiales
    $modulo_afectado = htmlspecialchars($_POST["modulo_afectado"]);
    $explicacion_modulo_afectado = htmlspecialchars($_POST["explicacion_modulo_afectado"]);
    $otros_modulo_afectado = htmlspecialchars($_POST["otros_modulo_afectado"]);
    //
    $otros = htmlspecialchars($_POST["otros"]);
    //
    $captcha = htmlspecialchars($_POST["captcha"]);
    $token = htmlspecialchars($_POST["token"]);
    $adjunto = htmlspecialchars($_POST["adjunto"]);
    // Compruebo que el captcha es correcto
    /*echo("captcha: ". $captcha);
    echo("en sesion: ". $_SESSION["captcha"]);
    if($captcha != ""){
        if($captcha != $_SESSION["captcha"]){
            echo("<br>captcha incorrecto");
            //header("Location: ../index.php?error=captcha");
            //exit();
        }
    }*/

    //////////////////////////////
    // Si se quiere crear/borrar un nuevo docente hay que comprobar si se tiene el permiso
    //////////////////////////////
    $accesoPermitido = true;

    if ($rol == "c" && $motivo == "4") {
        if($cod_coordinacion != $codeCoordinacionPrivate){
            $accesoPermitido = false;
        }
    }

    //////////////////////////////
    // Antes de procesar miro si campos obligatorios están rellenos para evitar envío masivo de navegadores que se saltan required
    //////////////////////////////

    $camposObligatoriosRellenos = true;
    if($nombre_solicitante == "" || $pape_solicitante == "" || $email_solicitante == "" ){
        $camposObligatoriosRellenos = false;
    }



    if($accesoPermitido && $camposObligatoriosRellenos && $captchaCorrecto){
        //////////////////////////////
        // Creo variables iniciales
        //////////////////////////////
        $date = date('d-m-Y H:i:s');
        $ip = getIPAddress();  

        $descriptionRedmine = '*' . $nombre_solicitante . '* *' . $pape_solicitante . '* ha enviado el ' . $date . ' desde la IP ' . $ip . ' una incidencia con la siguiente información:\n';
        $descriptionRedmine .= '\n';
        $descriptionRedmine .= '- *Rol* : ' . procesaRol($rol) . '\n';
        $descriptionRedmine .= '- *Nombre solicitante* : ' . $nombre_solicitante . '\n';
        $descriptionRedmine .= '- *1er apellido solicitante* : ' . $pape_solicitante . '\n';
        $descriptionRedmine .= '- *2º apellido solicitante* : ' . $sape_solicitante . '\n';
        $descriptionRedmine .= '- *E-mail solicitante* : ' . $email_solicitante . '\n';
        $descriptionRedmine .= '- *Ciclo* : ' . $ciclo . '\n';
        $descriptionRedmine .= '- *Motivo/Problema* : ' . procesaMotivo($motivo) . '\n';
        if ($motivo == "6") {
            $descriptionRedmine .= '- *Módulo profesional afectado* : ' . $modulo_afectado . '\n';
            $descriptionRedmine .= '- *Explicación detallada del cambio, actualización o error detectado* (Incluye la información de manera que pueda ser fácilmente identificable: URL, número de unidad del módulo, párrafo concreto, etc.): ' . $explicacion_modulo_afectado . '\n';
            $descriptionRedmine .= '- *Otros comentarios* (Por ejemplo, puedes comentar cuál crees que sería la solución a ese error, etc.): ' . $otros_modulo_afectado . '\n';
        }else if ($rol == "c" && $motivo == "4") {
            $descriptionRedmine .= '- *Tipo de modificación* : ' . $tipo_modificacion . '\n';
            $descriptionRedmine .= '- *Nombre de docente a modificar* : ' . $nombre_docente . '\n';
            $descriptionRedmine .= '- *1er apellido de docente a modificar* : ' . $pape_docente . '\n';
            $descriptionRedmine .= '- *2º apellido de docente a modificar* : ' . $sape_docente . '\n';
            $descriptionRedmine .= '- *DNI\nIE de docente a modificar* : ' . $dni_docente . '\n';
            $descriptionRedmine .= '- *E-mail de docente a modificar* : ' . $email_docente . '\n';
            $descriptionRedmine .= '- *Módulo 1* : ' . $modulo1_docente . '\n';
            $descriptionRedmine .= '- *Módulo 2* : ' . $modulo2_docente . '\n';
            $descriptionRedmine .= '- *Módulo 3* : ' . $modulo3_docente . '\n';
        }
        $descriptionRedmine .= '- *Explicación de la situación* : ' . $otros . '\n';
        //$descriptionRedmine .= '- *captcha en form* : ' . $captcha . '\n';
        //$descriptionRedmine .= '- *captcha en sesion* : ' . $_SESSION["captcha"] . '\n';

        //////////////////////////////
        // Contacto con RedMine para crear la incidencia
        //////////////////////////////
        $url = "https://soportearagon.catedu.es/issues.json";
        $asignarA = asignarIncidenciaA($rol, $motivo, $ciclo);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($curl, CURLOPT_POST, 1);
        $issue =  '
        <?xml version="1.0"?>
        <issue>
        <project_id>'.$projectId.'</project_id>
        <subject>'.procesaMotivo($motivo).'</subject>';

        if($token != ""){
            $issue .= '
            <uploads type="array">
              <upload>
                <token>' . $token . '</token>
                <filename>' . $adjunto . '</filename>
                <description>Fichero adjunto</description>
                <content_type>image/png</content_type>
              </upload>
            </uploads>';
        }

        $issue .= '<description><![CDATA['.$descriptionRedmine.']]></description>
        <priority_id>'.procesaPrioridad($motivo).'</priority_id>
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
            $toUser->email = $email_solicitante;
            $toUser->firstname = $nombre_solicitante;
            $toUser->lastname = $pape_solicitante;
            $toUser->maildisplay = true;
            $toUser->id = -99; 
            
            $subject = 'Nueva incidencia - FP a distancia Aragón';
            
            $cuerpo = 'Hola ' . $nombre_solicitante . ',<br/>';
            $cuerpo .= 'su incidencia realizada el ' . $date . ' ha sido recogida en nuestro sistema con el id <strong>'. $incidenciaCreadaId .'</strong>. La misma contiene la siguiente información:<br/>';
            $cuerpo .= '<ul>';
            $cuerpo .= '<li><b>Rol</b>: ' . procesaRol($rol) . '</li>';
            $cuerpo .= '<li><b>Nombre solicitante</b>: ' . $nombre_solicitante . '</li>';
            $cuerpo .= '<li><b>1er apellido solicitante</b>: ' . $pape_solicitante . '</li>';
            $cuerpo .= '<li><b>2º apellido solicitante</b>: ' . $sape_solicitante . '</li>';
            $cuerpo .= '<li><b>E-mail solicitante</b>: ' . $email_solicitante . '</li>';
            $cuerpo .= '<li><b>Ciclo</b>: ' . $ciclo . '</li>';
            $cuerpo .= '<li><b>Motivo/Problema</b>: ' . procesaMotivo($motivo) . '</li>';
            if($motivo == "6"){
                $cuerpo .= '<li><b>Módulo profesional afectado</b>: ' . $modulo_afectado . '</li>';
                $cuerpo .= '<li><b>Explicación detallada del cambio, actualización o error detectado</b>: ' . $explicacion_modulo_afectado . '</li>';
                $cuerpo .= '<li><b>Otros comentarios</b>: ' . $otros_modulo_afectado . '</li>';
            }else if ($rol == "c" && $motivo == "4") {
                $cuerpo .= '<li><b>Tipo de modificación</b>: ' . $tipo_modificacion . '</li>';
                $cuerpo .= '<li><b>Nombre de docente a modificar</b>: ' . $nombre_docente . '</li>';
                $cuerpo .= '<li><b>1er apellido de docente a modificar</b>: ' . $pape_docente . '</li>';
                $cuerpo .= '<li><b>2º apellido de docente a modificar</b>: ' . $sape_docente . '</li>';
                $cuerpo .= '<li><b>DNI/NIE de docente a modificar</b>: ' . $dni_docente . '</li>';
                $cuerpo .= '<li><b>E-mail de docente a modificar</b>: ' . $email_docente . '</li>';
                $cuerpo .= '<li><b>Módulo 1</b>: ' . $modulo1_docente . '</li>';
                $cuerpo .= '<li><b>Módulo 2</b>: ' . $modulo2_docente . '</li>';
                $cuerpo .= '<li><b>Módulo 3</b>: ' . $modulo3_docente . '</li>';
            }
            $cuerpo .= '<li><b>Explicación de la situación</b>: ' . $otros . '</li>';
            $cuerpo .= '</ul>';
            $cuerpo .= 'No conteste a este correo electrónico puesto que se trata de una cuenta desatendida y automatizada<br/>';
            $cuerpo .= 'Saludos<br/><br/>';
            $cuerpo .= 'FP distancia Aragón';

            $fromUser = new stdClass();
            $fromUser->firstname = null;
            $fromUser->lastname = null;
            $fromUser->email = '<>';
            $fromUser->maildisplay = true;
            $fromUser->id = -99;

            $exitoEnviandoEmail = email_to_user($toUser, $fromUser, $subject, $cuerpo);
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
        
        <!-- Matomo -->
        <script type="text/javascript">
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u="https://analytics.catedu.es/";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '1']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
        })();
        </script>
        <!-- End Matomo Code -->
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
    }elseif(!$camposObligatoriosRellenos){
        $h3 =  'Debe rellenar todos los campos obligatorios. Incidencia NO procesada.';
    }elseif(!$captchaCorrecto){
        $h3 =  'El código de captcha no es correcto. Incidencia NO procesada.';
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
                                            <div class="settingsform">
<?php
    if( $exitoCreandoIncidencia ){
?>
                                            <p>La información recogida es la siguiente:</p>
                                            <ul>
                                                <li>Rol</b>: <?php echo procesaRol($rol); ?></li>
                                                <li>Nombre solicitante</b>: <?php echo htmlentities($nombre_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>1er apellido solicitante</b>: <?php echo htmlentities($pape_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>2º apellido solicitante</b>: <?php echo htmlentities($sape_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>E-mail solicitante</b>: <?php echo htmlentities($email_solicitante, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Ciclo</b>: <?php echo htmlentities($ciclo, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Motivo/Problema</b>: <?php echo procesaMotivo($motivo); ?></li>
<?php
        if ($motivo == "6"){
?>
                                                <li>Módulo profesional afectado</b>: <?php echo procesaMotivo($motivo); ?></li>
                                                <li>Explicación detallada del cambio, actualización o error detectado</b>: <?php echo procesaMotivo($motivo); ?></li>
                                                <li>Otros comentarios</b>: <?php echo procesaMotivo($motivo); ?></li>
<?php
        }else if ($rol == "c" && $motivo == "4") {
?>
                                                <li>Tipo de modificación</b>: <?php echo htmlentities($tipo_modificacion, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Nombre de docente a modificar</b>: <?php echo htmlentities($nombre_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>1er apellido de docente a modificar</b>: <?php echo htmlentities($pape_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>2º apellido de docente a modificar</b>: <?php echo htmlentities($sape_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>DNI/NIE de docente a modificar</b>: <?php echo htmlentities($dni_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>E-mail de docente a modificar </b>: <?php echo htmlentities($email_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Módulo 1</b>: <?php echo htmlentities($modulo1_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Módulo 2</b>: <?php echo htmlentities($modulo2_docente, ENT_QUOTES, "UTF-8"); ?></li>
                                                <li>Módulo 3</b>: <?php echo htmlentities($modulo3_docente, ENT_QUOTES, "UTF-8"); ?></li>
<?php
        }
?>
                                                <li>Explicación de la situación</b>: <?php echo htmlentities($otros, ENT_QUOTES, "UTF-8"); ?></li>
                                            </ul>
<?php
    }else{
?>
                                            <p class="box py-3 generalbox alert alert-error alert alert-danger">
                                                Ha fallado la creación de la incidencia
                                            </p>
<?php
    }
?>

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
