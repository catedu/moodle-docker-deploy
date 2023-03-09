<?php
require_once('../../config.php');

//set_time_limit(0);
//error_reporting(E_ALL);
//ob_implicit_flush(TRUE);
//ob_end_flush();
//require("./libreria/BD_debug.php");

require("../libreria/PHPMailer_5.2/PHPMailerAutoload.php");

$urlHeadTemplate = "../Fragmentos_Mail/envio_encuesta/body.html";
$editor1 = file_get_contents($urlHeadTemplate);

$PAGE->set_pagelayout('admin');
$title = 'Envio encuesta a alumnos';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
if(has_capability('moodle/site:config', $coursecontext)) {
	require_login();
	//Si ha llegado aquí, es admin
	//Inicialmente esta a 20 lo modificamos
	set_time_limit(400000);
	if (!$_POST){
		?>
		<center><h1><?=$title?></h1></center>
		<h2>Cuerpo del mensaje</h2>
		<script type="text/javascript" src="../libreria/ckeditor/ckeditor.js"></script>
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
			<h2>Opciones</h2>
			<div  style="width:90%;margin-left:5%;margin-bottom:10px">
				<p>Introduce las direcciones de correos electr&oacute;nicos a los que deseas enviar el mensaje <strong><u>separados por comas</u></strong>.<br><em>(Por defecto a todos los alumnos)</em></p>
				<input style="width:100%" type="text" id="correos" name="correos" placeholder="Por defecto todos" oninput="checkCorreos()">
			</div>
			<center><button class="btn btn-default btn-lg" type="submit">Enviar <i class="fa fa-paper-plane" aria-hidden="true"></i></button></center>
		</form>		
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
			//POST
			$mail = new PHPMailer();

			$mail->IsSMTP();
			$mail->Host = "smtp.aragon.es";
			$mail->SMTPSecure = "tls";
			$mail->SMTPAuth = true;
			// credenciales usuario
			$mail->Username = "admin.moodlefpdistancia@aragon.es";
			$mail->Password = "DG.GSb3J";
			$mail->From = "admin.moodlefpdistancia@aragon.es";
			$mail->FromName = "Administrador de Plataforma FP";
			$mail->Subject = "Encuestas de satisfaccion";
			$mail->IsHTML(true);

			$sqlalumnos =
			//"SELECT DISTINCT user.id as id,username,firstname, lastname, email
			//FROM {user} user
			//WHERE user.email in ('cpina@itainnova.es')";
			
			"SELECT DISTINCT user.id as id,username,firstname, lastname, email
			FROM 	{role_assignments} role_assignments,
					{user} user,
					{context} context
			WHERE roleid = 5 AND
				user.email NOT LIKE '%demo_alu.es' AND
				user.deleted = 0 AND
				user.suspended = 0 AND
			 	role_assignments.userid = user.id AND
				role_assignments.contextid = context.id AND
				context.contextlevel = 50 AND
				context.instanceid NOT IN (SELECT course.id FROM {course} course WHERE category in (0,18,26,27,38,52,53,58) OR shortname like '%\_TU')";

			if($_POST['correos']!=""){
				$correos = filter_var($_POST['correos'],FILTER_SANITIZE_MAGIC_QUOTES);
				$sqlalumnos.=" AND email in (";
				$array = explode(",",$correos);
				for($i=0;$i<count($array);$i++){
					$array[$i]=filter_var($array[$i],FILTER_SANITIZE_EMAIL);
					if(!filter_var($array[$i],FILTER_VALIDATE_EMAIL)){
						//Si no es un email valido, lo quitamos
						$array[$i]='';
					}
				}
				$sqlalumnos.="'".implode("','",$array)."')";
			}
			
			$cuerpo_correo=$_POST['editor1'];
			
			$listado_alumnos = $DB->get_records_sql($sqlalumnos);
			$num_enviados = 0;
			foreach($listado_alumnos as $alumno){
				//$contenido_email="Estimad@ <b>".utf8_decode($alumno->firstname." ".$alumno->lastname)."</b>: <br><br>";
				$contenido_email=$cuerpo_correo;
				$add=$alumno->email;
				//$add = 'admin.moodlefpdistancia@aragon.es';
				//$add = 'cpina@itainnova.es';
				$mail->AddAddress($add);
				$mail->Body = $contenido_email;
				$mail->IsHTML(true);
				$enviado = false;
				for($i = 0; $i < 3 && !$enviado; $i++){
					$enviado = $mail->Send();
				}
				$mail->ClearAddresses();
				$mail->ClearAllRecipients();
				if(!$enviado){
					echo "<p>Error al enviar correo de ".$add."</p>";
					echo '<p>'.$mail->ErrorInfo.'</p>';
					echo "<br>";
				}
				$logentry = new stdClass();
				$logentry->source   = basename(__FILE__);
				$logentry->courseid = $courseid;
				if(!$enviado){
					$logentry->log      = 'ERROR: '.$add;
				}else{
					$logentry->log      = $add;
				}
				$logentry->logdate  = date('Y-m-d');
				$logentry->logtime  = date('H:i:s');
				$lastinsertid = $DB->insert_record('itainnova_log', $logentry, false);
				$num_enviados++;
				echo "<p>$num_enviados : Enviado correctamente a $alumno->email</p>";
				ob_flush();


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