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
		//	$sqlalumno .= " AND ((mdl_user.id>18980) and (mdl_user.id<18990))";	
		//}

		$sqlalumno .= " AND mdl_context.contextlevel=50
			AND mdl_role_assignments.roleid in (5)
			AND mdl_context.instanceid not in (SELECT mdl_course.id FROM mdl_course WHERE category in (0,18,26,27,38,52,53,55) OR shortname like '%\_TU' )
			GROUP BY username,email, firstname, lastname, ppropio ORDER BY email
			LIMIT 701,1400";

		$listado_alumnos = $DB->get_records_sql($sqlalumno);
		foreach ($listado_alumnos as $alumno) {
			$contenido_email =  "<p>Buenos d&iacute;as " . utf8_decode($alumno->firstname . " " . $alumno->lastname) .",</p>";
			$contenido_email .= "<p><strong>Para evitar cualquier problema</strong> en la ejecuci&oacute;n de los ex&aacute;menes en los pr&oacute;ximos d&iacute;as, vamos a realizar <strong>una serie de pruebas</strong> para comprobar el funcionamiento de la plataforma con una carga elevada de usuarios. Para ello, pedimos tu colaboraci&oacute;n en una de las pruebas.</p>";
			$contenido_email .= "<p>Necesitar&aacute;s como mucho <strong>30 minutos, y deber&aacute;s entrar justo el d&iacute;a 7 de mayo a las 18.00 horas</strong>. Si ese d&iacute;a no puedes entar, no pasa nada, haremos 2 pruebas, y en cada una os convocamos a m&aacute;s del triple de usuarios que entrareis en la Plataforma de los estimados en los ex&aacute;manes convocados para este fin de curso. De esta manera, se podr&aacute; comunicar a AST (alojan el Servidor de la Plataforma) cualquier necesidad o problema con el Servidor.</p>";
            $contenido_email .= "<ul>
								<li>1. <strong>Entra en la Plataforma el d&iacute;a 7 a las 18.00 horas con tus datos de acceso:</strong>
									<ul><li><strong>usuario:</strong> " . $alumno->username . "</li><li><strong>password:</strong> " . $alumno->ppropio . "</li></ul></li>
                                <li>2. <strong>Entra en el Espacio Ayuda, en <a href='https://www.adistanciafparagon.es/mod/quiz/view.php?id=115446' target='_blank'>Test para prueba de carga</a>.</strong> Es un cuestionario con 11 preguntas que completar&aacute;s en menos de 30 minutos.</li>
								</ul>";
			$contenido_email .= "<p>Comienza el sprint final de un curso intenso, por ello, queremos aprovechar este mensaje para felicitaros y desearos <strong>!&Aacute;nimo y MUCHA SUERTE!</strong> en la recta final ;-). Estas &uacute;ltimas semanas han sido un perido especial para tod@s . En muchos casos, hab&eacute;is sacrificado horas de sue&ntilde;o y familia para acabar el curso.</p>
								<p><strong>Muchas gracias por tu colaboraci&oacute;n</strong>. Para cualquier duda, recordad que estamos a vuestra disposici&oacute;n....<strong>junt@s lo conseguiremos</strong> !!!
								<p>Un saludo.</p>
								<p>Equipo de Fp a Distancia.</p>";
			
			$mail->Subject = "[Plataforma FP] Prueba EXAMEN";
			
			$add = $alumno->email;
            //$add ="cpina@itainnova.es";
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


