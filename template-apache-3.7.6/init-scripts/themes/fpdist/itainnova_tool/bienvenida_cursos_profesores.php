<?php
require_once('../config.php');
//require("./libreria/BD_debug.php");
require("./libreria/PHPMailer_5.2/PHPMailerAutoload.php");
$urlHeadTemplate = "Fragmentos_Mail/bienvenida_cursos_profesores/head.html";
$urlTailTemplate = "Fragmentos_Mail/bienvenida_cursos_profesores/tail.html";
$editor1 = file_get_contents($urlHeadTemplate);
$editor2 = file_get_contents($urlTailTemplate);

global $DB;
$PAGE->set_pagelayout('admin');
$title = 'Mensaje de bienvenida a profesores';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();

//Comprobamos que es el usuario administrador el que ejecuta esta funci�n
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
if(has_capability('moodle/site:config', $coursecontext)) {
  require_login();
  //Si ha llegado aquí, es admin
  if (!($_POST)){
    ?>
    <center><h1><?=$title?></h1></center>
    <h2>Cabeza del mensaje</h2>
    <script type="text/javascript" src="./libreria/ckeditor/ckeditor.js"></script>
    <form method="post" onsubmit="checkCorreos()">
      <div style="width:90%;margin-left:5%;margin-bottom:10px">
        <p>Plantilla por defecto: <a href="<?php echo $urlHeadTemplate;?>"><?php echo $urlHeadTemplate;?></a></p>
        <textarea cols="80" id="editor1" name="editor1" rows="10">
          <?php echo $editor1;?>
        </textarea>
        <script type="text/javascript">
        CKEDITOR.replace ("editor1");
        </script>
      </div>
      <h2>Datos acceso</h2>
      <div  style="width:90%;margin-left:5%;margin-bottom:10px">
        <input type="checkbox" name="acceso" value=true checked> Incluir datos de acceso del usuario<br>
        <p style="margin-left:5%"><em>Esto incluir&aacute; <b>nombre de usuario, contrase&nacute;a y correo electr&oacute;nico</b></em>
        </div>
        <h2>Cola del mensaje</h2>
        <div  style="width:90%;margin-left:5%;margin-bottom:10px">
          <p>Plantilla por defecto: <a href="<?php echo $urlTailTemplate;?>"><?php echo $urlTailTemplate;?></a></p>
          <textarea cols="80" id="editor2" name="editor2" rows="10">
            <?php echo $editor2;?>
          </textarea>
          <script type="text/javascript">
          CKEDITOR.replace ("editor2");
          </script>
        </div>
        <h2>Opciones</h2>
        <div  style="width:90%;margin-left:5%;margin-bottom:10px">
          <p>Introduce las direcciones de correos electr&oacute;nicos a los que deseas enviar el mensaje <strong><u>separados por comas</u></strong>.<br><em>(Por defecto a todos los profesores)</em></p>
          <input style="width:100%" type="text" id="correos" name="correos" placeholder="Por defecto todos" oninput="checkCorreos()">
        </div>
        <center><button class="btn btn-default btn-lg" type="submit">Enviar <i class="fa fa-paper-plane" aria-hidden="true"></i></button></center>
        <script>
        function checkCorreos(){
          var correos = document.getElementById("correos");
          var num_comas = (correos.value.match(/,/g) || []).length;
          var num_dir = (correos.value.match(/@/g) || []).length;
          var num_espacios = (correos.value.match(/ /g) || []).length;
          var diferencia = (num_dir-num_comas);
          var check;
          if( (diferencia==0 || diferencia==1)  && num_espacios==0 && num_dir>0){
            correos.style.color="black";
            check=true;
          }else{
            correos.style.color="red";
            check=false;
          }
          return check;
        }
        </script>
        <?php
      }else{
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->Host = "smtp.aragon.es";
        $mail->SMTPSecure = "tls";
        $mail->SMTPAuth = true;
        // credenciales usuario
				include "./secret/crenciales.php";
        $mail->Subject = "NUEVOS DATOS DE ACCESO - Curso 2020-2021";
        $mail->IsHTML(true);

        $sqlprofesores = 'SELECT DISTINCT mdl_user.id as id,username,firstname, lastname, email
        FROM mdl_role_assignments, mdl_user WHERE mdl_role_assignments.userid = mdl_user.id AND roleid in (3,18,19)';
        if($_POST['correos']!=""){
          $correos = filter_var($_POST['correos'],FILTER_SANITIZE_MAGIC_QUOTES);
          $sqlprofesores.=" AND email in (";
            $array = explode(",",$correos);
            for($i=0;$i<count($array);$i++){
            $array[$i]=filter_var($array[$i],FILTER_SANITIZE_EMAIL);
            if(!filter_var($array[$i],FILTER_VALIDATE_EMAIL)){
            //Si no es un email valido, lo quitamos
            $array[$i]='';
            }
            }
            $sqlprofesores.="'".implode("','",$array)."')";
          }
          $listado_profesores = $DB->get_records_sql($sqlprofesores);
          foreach($listado_profesores as $profesor){
            $contenido_email="<p align='left'>Estimad@: ".utf8_decode($profesor->firstname." ".$profesor->lastname)."</p>";

            $contenido_email.=$_POST['editor1'];

            if($_POST['acceso']==true){

              $sqlpassword = "SELECT * from ppropio where idd=".$profesor->id;
              $password = $DB->get_records_sql($sqlpassword);
              $contenido_email.="<li><b>Usuario: </b>".$profesor->username."</li>";
              $contenido_email.="<li><b>Contrase&ntilde;a: </b>".current($password)->ppropio."";
              $contenido_email.="<li><b>Email: </b>".$profesor->email."</li></ul>";
            }

            $contenido_email.=$_POST['editor2'];
            $add=$profesor->email;
            
			//$add="rzamanillo@itainnova.es";
			//$add="cpina@itainnova.es";
            $mail->AddAddress($add);
            $mail->Body = $contenido_email;
            $mail->IsHTML(true);
            $enviado = false;
            for($i = 0; $i < 2 && !$enviado; $i++){
              $enviado = $mail->Send();
            }
            $mail->ClearAddresses();
            $mail->ClearAllRecipients();
            $mail->clearAttachments();
            if(!$enviado){
              echo "<p><strong>Error</strong> al enviar correo de ".$add."</p>";
              //echo '<p>'.$mail->ErrorInfo.'</p>';
              echo "<br>";
            }else{
              echo '<p>Enviado correctamente a '.$add.'</p>';
            }
          }
          ?>
          <center><h2>Mensajes enviados.</h2></center>
          <?php
        }
      }else{
        //Si llega aquí, no es admin
        echo "<center><h1 style='color:red'>Usuario no autorizado</h1></br>";
        echo "<p>Contacte con su administrador del sitio para mas informaci&oacute;n.</p></center>";
      }
      echo $OUTPUT->footer();
      ?>
