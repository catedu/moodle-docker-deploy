<?php

error_reporting(E_ALL);
require_once("../../config.php");
global $DB;
$PAGE->set_pagelayout('base');
$title = "Formulario de Incidencias";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();

// MOTIVOS DE INCIDENCIA
$motivos = array(
	'1' => array(
		'motivo' => 'Problemas de acceso a la Plataforma ( error de conexión ,fallos de la Plataforma..)',
		'email' => 'fpdistancia@aragon.es',
		'corto' => '[Acceso a la Plataforma]'
	),
	'2' => array(
		'motivo' => 'Matrículas (errores/cambios, etc…) ',
		'email' => 'fpdistancia@aragon.es',
		'corto' => '[Matrículas]'
	),
	'3' => array(
		'motivo' => 'Materiales y contenido de los módulos',
		'email' => 'fpdistancia@aragon.es',
		'corto' => '[Materiales y contenido]'
	),
	'4' => array(
		'motivo' => 'Apoyo a la docencia (uso de las herramientas de la Plataforma...)',
		'email' => 'admin.moodlefpdistancia@aragon.es',
		'corto' => '[Apoyo a la docencia]'
	),
	'5' => array(
		'motivo' => 'Apoyo  al alumn@ (uso de las herramientas de la Plataforma...)',
		'email' => 'admin.moodlefpdistancia@aragon.es',
		'corto' => ' [Apoyo al alumn@]'
	)
);

?>

<script>
	var validate = function() {
		var form = document.getElementById("formulario");

		if (form.nombre.value.length < 3) {
			alert("Debe rellenar Nombre");
			return false;
		}
		if (form.apellidos.value.length < 4) {
			alert("Debe rellenar Apellido");
			return false;
		}
		if (form.email.value.length < 4) {
			alert("Debe rellenar Email");
			return false;
		}
		if (form.aConsulta.value == "...") {
			alert("Debe rellenar ciclo");
			return false;
		}
		if (form.bConsulta.value == "...") {
			alert("Debe rellenar el motivo");
			return false;
		}
		if (form.comentario.value.length < 4) {
			alert("Debe rellenar Comentario");
			return false;
		}
		return true;
	}
</script>
<?php
require("../libreria/PHPMailer_5.2/PHPMailerAutoload.php");
require_once('recaptchalib.php');
$sitekey = "6LdzCS4UAAAAAM2ORZsrjbEte-U8HOqkfgZj8Kl8";
$secretkey = "6LdzCS4UAAAAAA9c8Av3PXCXCpy4_H31jx5G_yNG";
if (!$_POST) {
	?>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<div style="width:90%;margin-left:5%">
		<center>
			<h2>FORMULARIO DE ATENCI&Oacute;N DE INCIDENCIAS</h2>
		</center>
		<p style="text-align: justify;">Desde el Equipo de la Plataforma de Formaci&oacute;n Profesional a distancia del Gobierno de Arag&oacute;n queremos darte el mejor servicio posible ante cualquier incidencia que te surja.<br><br>
			En el Bloque de "AYUDA" de este Portal , encontrar&aacute;s varios enlaces con informaci&oacute;n &uacute;til que te puede ayudar a resolver las principales dudas o incidencias t&eacute;cnicas que te surjan. Te recomendamos el apartado de <a href="../FAQ/" target="_blank"><b>Preguntas frecuentes </b><i class="fa fa-external-link" aria-hidden="true"></i></a> . donde encontrar&aacute;s resueltas las dudas e incidencias m&aacute;s comunes.
			<br><br><br>¿No has podido resolver tu duda o incidencia t&eacute;cnica?<br>
			Si no has encontrado la respuesta que buscabas, contacta con nosotros a trav&eacute;s de este formulario. Resolveremos tu consulta en la mayor brevedad posible.<br></p>
		<br>
		<form method="post" name="formulario" id="formulario" onsubmit="return validate()" enctype="multipart/form-data">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon" style="width:90px">Nombre</div>
					<input name="nombre" id="nombre" type="text" class="form-control" id="exampleInputAmount" placeholder="Escribe aqu&iacute; tu nombre" style="height:28px;border-color:#ccc">
					<div class="input-group-addon">Obligatorio</div>
				</div>
			</div>

			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon" style="width:90px">Apellidos</div>
					<input name="apellidos" id="apellidos" type="text" class="form-control" placeholder="Escribe aqu&iacute; tus apellidos" style="height:28px;border-color:#ccc">
					<div class="input-group-addon">Obligatorio</div>
				</div>
			</div>

			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon" style="width:90px">Correo</div>
					<input name="email" id="email" type="text" class="form-control" placeholder="correo@ejemplo.com" style="height:28px;border-color:#ccc">
					<div class="input-group-addon">Obligatorio</div>
				</div>
			</div>

			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon" style="width:90px">Ciclo</div>
					<select name="aConsulta" id="aConsulta" class="form-control">
						<option value="...">Selecciona el centro</option>
						<?php
							$sql_curso_centro = "SELECT cursos.name as curso,centros.name as centro
							FROM mdl_course_categories centros
							INNER JOIN mdl_course_categories cursos on centros.id = cursos.parent
							WHERE centros.coursecount = 0
							ORDER BY centros.name ASC;";
							//$estudios = mysql_query($sql_curso_centro, $conexion);
							$estudios = $DB->get_records_sql($sql_curso_centro);
							//while($estudio = mysql_fetch_assoc($estudios))
							foreach ($estudios as $estudio) {
								$opcion = $estudio->centro . ': ' . $estudio->curso;
								echo "<!--$opcion-->";
								echo '<option value="' . $estudio->centro . ' : ' . $estudio->curso . '">' . $estudio->centro . ' : ' . $estudio->curso . '</option>';
							} ?>
					</select>
					<div class="input-group-addon">Obligatorio</div>
				</div>
			</div>

			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon" style="width:90px">Motivo</div>
					<select name="bConsulta" id="bConsulta" class="form-control">
						<option value="...">Selecciona el motivo</option>
						<?php
							foreach ($motivos as $ind => $motivo) {
								echo '<option value="' . $ind . '">' . $motivo['motivo'] . '</option>';
							}
							?>
					</select>
					<div class="input-group-addon">Obligatorio</div>
				</div>
			</div>
			<?php /*
				<div class="form-group">
					<div class="input-group" style="width:100%">
						<div class="input-group-addon" style="width:90px;">Adjunto</div>
						<div class="form-control" style="height:43px;width:100%">
							<input type="file" name="uploaded_file" id="uploaded_file" />
					</div>
					</div>
				</div>
				*/ ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 style="font-size:14px" class="panel-title" placeholder="Escribe aqu&iacute; tu mensaje">Comentario <div style="float:right">Obligatorio</div>
					</h3>
				</div>
				<div class="input-group input-block-level">
					<textarea name="comentario" id="comentario" class="form-control" style="resize:vertical;min-height:90px" rows="3"></textarea>
				</div>
			</div>
			<center>
				<div class="g-recaptcha" data-sitekey="<?= $sitekey ?>"></div><br>
				<button type="submit" class="btn btn-default">Enviar Consulta <i class="fa fa-paper-plane fa-lg" aria-hidden="true"></i></button>
			</center>
		</form>

	</div>
<?php
} else {
	//comprobamos el captcha
	$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretkey&response=" . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
	$obj = json_decode($response);
	if ($obj->success == true) {

		$motivo = [];
		if (!empty($_POST['bConsulta'])) {
			$motivo = $motivos[$_POST['bConsulta']];
		}

		$mail = new PHPMailer();

		$mail->IsSMTP();
		$mail->Host = "smtp.aragon.es";
		$mail->SMTPSecure = "tls";
		$mail->SMTPAuth = true;
		$mail->IsHTML(true);
		//capturar ip
		$ipvisitante = $_SERVER["REMOTE_ADDR"];
        
		// credenciales usuario
		include "./secret/crenciales.php";
		$mail->AddReplyTo(utf8_decode($_POST["email"]), utf8_decode($_POST["nombre"]) . " " . utf8_decode($_POST["apellidos"]));
		$mail->FromName = utf8_decode($_POST["nombre"]) . " " . utf8_decode($_POST["apellidos"]);
		$mail->Subject = "Incidencia: ". $motivo['corto']. " " . utf8_decode($_POST["aConsulta"]);

		if (!empty($motivo['email'])) $mail->AddAddress($motivo['email']);
		if (isset($motivo['emailCP'])) $mail->AddCC($motivo['emailCP']);

		$body = "<hr> <h2><center>" . utf8_decode($_POST["nombre"]) . " " . utf8_decode($_POST["apellidos"]);
		$body .= " matriculad@ en " . utf8_decode($_POST["aConsulta"]) . " escribi&oacute; el " . date("d-m-Y") . " a las " . date("H:i:s") . "</center></h2><hr><br>";
		$body .= utf8_decode($_POST["comentario"]) . "<br><hr>";

		//if (isset($_FILES['uploaded_file'])) {
		//	$mail->AddAttachment($_FILES['uploaded_file']['tmp_name'],
		//       		         $_FILES['uploaded_file']['name']);
		//}
		
		$mail->Body = $body;
		
		$enviado = $mail->Send();
		if (!$enviado) {
			echo "<p>Error al enviar correo de " . $add . "</p>";
			echo '<p>' . $mail->ErrorInfo . '</p>';
			echo "<br>";
		} else {
			echo "<br><center><h2>Consulta enviada<br>";
			echo "Muchas Gracias, resolveremos tu consulta en el menor tiempo posible</h2></center>\n";
		}
	} else {
		echo "<br><center><h2>Error al enviar<br>";
		echo "El captcha no es v&aacute;lido, pruebe otra vez.</h2></center>\n";
	}
}
echo $OUTPUT->footer();
?>