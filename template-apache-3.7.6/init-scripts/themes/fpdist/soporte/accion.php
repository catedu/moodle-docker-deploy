<?php
    require_once(__DIR__ . '/../config.php');
    //////////////////////////////
    // Funciones
    //////////////////////////////
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
    $nombre_docente = htmlspecialchars($_POST["nombre_docente"]);
    $pape_docente = htmlspecialchars($_POST["pape_docente"]);
    $sape_docente = htmlspecialchars($_POST["sape_docente"]);
    $dni_docente = htmlspecialchars($_POST["dni_docente"]);
    $email_docente = htmlspecialchars($_POST["email_docente"]);
    $modulo1_docente = htmlspecialchars($_POST["modulo1_docente"]);
    $modulo2_docente = htmlspecialchars($_POST["modulo2_docente"]);
    $modulo3_docente = htmlspecialchars($_POST["modulo3_docente"]);
    $otros_docente = htmlspecialchars($_POST["otros_docente"]);
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
                                            <h2>Soporte - Petición procesada</h2>
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
<?php
    //////////////////////////////
    // Envío email al usuario con copia de su solicitud original
    //////////////////////////////
    $date = date('d-m-Y H:i:s');
    
    $toUser = new stdClass();
    $toUser->email = "fp@catedu.es";
    $toUser->firstname = $nombre_solicitante;
    $toUser->lastname = $pape_solicitante;
    $toUser->maildisplay = true;
    $toUser->id = -99; 
    
    $subject = 'Nueva incidencia - FP a distancia Aragón';
    
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

    $fromUser = new stdClass();
    $fromUser->firstname = null;
    $fromUser->lastname = null;
    $fromUser->email = '<>';
    $fromUser->maildisplay = true;
    $fromUser->id = -99;

    $exito = email_to_user($toUser, $fromUser, $subject, $body);

    if ( $exito ){
        echo "Enviado con ÉXITO";
    }else{
        echo "NO enviado";
    }
    echo "<br/><br/>";
    
    //////////////////////////////
    // Contacto con RedMine para crear la incidencia
    //////////////////////////////
    
    /* GET example
    $url = "https://soportearagon.catedu.es/projects.json";

    $client = curl_init($url);
    curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($client);
    $result = json_decode($response);

    echo 'response: ' . $response . '<br/><br/>';
    echo 'result: ' . $result . '<br/><br/>';
    */

    $url = "https://soportearagon.catedu.es/issues.json";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($curl, CURLOPT_POST, 1);
    $issue =  '
    <?xml version="1.0"?>
    <issue>
      <project_id>12</project_id>
      <subject>PROBANDO ANDO: '.procesaMotivo($motivo).'</subject>
      <description>('.procesaRol($rol).') '.$nombre_solicitante.' '.$pape_solicitante.'</description>
      <priority_id>2</priority_id>
      <custom_fields type="array">
          <custom_field id="1" name="owner-email">
              <value>'.$email_solicitante.'</value>
          </custom_field>
      </custom_fields>
      <assign_to_id>873</assign_to_id>
    </issue>';
    /*<?xml version="1.0" encoding="UTF-8"?>
    <issue>
        <id>3637</id>
        <project id="12" name="FP a Distancia"/>
        <tracker id="3" name="Soporte"/>
        <status id="1" name="Nueva"/>
        <priority id="4" name="Urgente"/>
        <author id="873" name="Pablo Ruiz"/>
        <subject>Acceso a plataforma. Problemas con el usuario y contraseña</subject>
        <description>(ERROR) Vanesa Puig</description>
        <start_date/>
        <due_date/>
        <done_ratio>0</done_ratio>
        <is_private>false</is_private>
        <estimated_hours/><total_estimated_hours/>
        <created_on>2021-06-07T11:01:31Z</created_on>
        <updated_on>2021-06-07T11:01:31Z</updated_on>
        <closed_on/>
    </issue>*/
    curl_setopt($curl, CURLOPT_POSTFIELDS, $issue );
    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "xxx:xxx");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    echo 'result: ' . $result . '<br/><br/>';
    curl_close($curl);
?>
    </body>
</html>