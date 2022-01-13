<?php
	require_once('../config.php');
	require("./libreria/PHPMailer_5.2/PHPMailerAutoload.php");

	global $USER;
	global $DB;
	$contador = 0;

	set_time_limit(4000000000);
	//Comprobamos que es el usuario administrador el que ejecuta esta función
	if ($USER->id == 2) {

		// Configuración e instanciación $mail
		require_once("./libreria/PHPMailer_5.2/PHPMailerConfig.php");
		
		$sqlalumno = "SELECT mdl_user.id,username, email, firstname, lastname, ppropio
			FROM mdl_user right join mdl_role_assignments ON mdl_user.id=mdl_role_assignments.userid left join mdl_context ON mdl_role_assignments.contextid=mdl_context.id
			INNER JOIN ppropio ON mdl_user.id=ppropio.idd
			WHERE mdl_user.firstname not like '%\_%'";

		//WHERE firstaccess = 0 and timecreated > 1506808800 and email like '%@%' and lastname <> '_ALU' and username not like 'anon%'"; //NO HAN ACCEDIDO
		//if (isset($_GET['email'])) {
		//	$sqlalumno .= " AND email='" . $_GET['email'] . "' ";
		//} else {
			$sqlalumno .= " AND ((mdl_user.id>18980) and (mdl_user.id<18990))";	
		//}

		$sqlalumno .= " AND mdl_context.contextlevel=50
			AND mdl_role_assignments.roleid in (5)
			AND mdl_context.instanceid not in (SELECT mdl_course.id FROM mdl_course WHERE category in (0,18,26,27,38,52,53,55) OR shortname like '%\_TU' )
			GROUP BY username,email, firstname, lastname, ppropio ORDER BY email
			LIMIT 0,1400";

		$listado_alumnos = $DB->get_records_sql($sqlalumno);
		foreach ($listado_alumnos as $alumno) {
			$contenido_email =  "<p>Buenos d&iacute;as,</p>";
			$contenido_email .= "<p>os comunicamos que <strong>las pruebas de carga del d&iacute;a 7 de mayo, a las 16.00 y 18.00 horas quedan anuladas.</strong></p>";
			$contenido_email .= "<p>Pod&eacute;is acceder a vuestros examenes en las fechas indicadas por vuestros profesores.</p>";
			$contenido_email .= "<p>Un saludo<p>";
			
			
			$contenido_email =  "p>Buenos d&iacute;as.</p>";
$contenido_email .= "<p>Se ha comprobado en las ultimas horas que una actualizaci&oacute;n del antivirus AVAST, bloquea el acceso a la Plataforma de Formaci&oacute;n a Distancia.</p>";
$contenido_email .= "<p>Si os aparece el siguiente mensaje, <strong>'hemos anulado de forma segura la conexión de fp.distancia.aragon.es porque estaba infectada por URL:Blacklist'</strong>, no es un error de la plataforma,
 sino del antivirus, por lo que la &ucate;nica soluci&oacute;n que podemos dar, es que durante la realizaci&oacute;n del examan se desactive el antivirus y se vuelva a activar a su finalizacion, 
 os env&iacute;amos imagen, es muy sencillo, simplemente clickando el bot&oacaute;n derecho en la parte intferior derecha de vuestros ordenadores, en el icono de AVAST:
 <img src="error_avast.png" alt="Error AVAST"></a></p>";

$contenido_email .= "<p>Un saludo.</p>";

$contenido_email .= "<p><strong>P.D.</strong>Acordaros de reactivarlo al terminar el ex&acute;men.</p>";
$contenido_email .= "<p>Si no ve bien este correo, puede hacer <a href='https://www.adistanciafparagon.es/itainnova_tool/FAQ/'>click para visualizarlo</a></p>";

					
			
			$mail->Subject = "[Plataforma FP] Prueba EXAMEN - ANULADA -";
			
			//$add = $alumno->email;
            $add ="cpina@itainnova.es";
			//$add="rmonton@itainnova.es";
            //$add="rzamanillo@itainnova.es";			

			$mail->AddAddress($add);
			$mail->Body = $contenido_email;
			$mail->IsHTML(true);
			$enviado = false;
			for ($i = 0; $i < 2 && !$enviado; $i++) {
				$enviado = $mail->Send();
			}
			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
			if (!$enviado) {
				echo "<p><strong>Error</strong> al enviar correo de " . $add . "</p>";
				//echo '<p>'.$mail->ErrorInfo.'</p>';
			} else {
				$contador += 1;
				echo $contador . " " . $alumno->id . " Enviado " . $add . "<br>";
			}
		}
		echo "<center><h2>TERMINADO</h2></center>";
	} else {
		echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"3;URL=https://www.adistanciafparagon.es\">";
		echo "<center><h1 style=\"font-size: 16pt;margin-top:5px;font-weight: bold;\">Usuario no autorizado en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>";
	}
