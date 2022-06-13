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
		if (isset($_GET['email'])) {
			$sqlalumno .= " AND email='" . $_GET['email'] . "' ";
		} else {
			//$sqlalumno.=" AND (mdl_user.id > 19263 AND mdl_user.id <= 19501 )";
			//$sqlalumno.=" AND (mdl_user.id > 19501 AND mdl_user.id <= 19800 )";
			//$sqlalumno.=" AND (mdl_user.id > 19800 AND mdl_user.id <= 20200 )";
			//$sqlalumno.=" AND (mdl_user.id > 20200 AND mdl_user.id <= 20600 )";
			//$sqlalumno.=" AND (mdl_user.id > 20600 AND mdl_user.id <= 21000 )";
			//$sqlalumno.=" AND (mdl_user.id > 21000 AND mdl_user.id <= 21400 )";
			//$sqlalumno.=" AND (mdl_user.id > 21400 AND mdl_user.id <= 21870 )";
			//$sqlalumno.=" AND (mdl_user.id > 21876 AND mdl_user.id <= 22157 )";
			//$sqlalumno.=" AND (mdl_user.id > 22160 AND mdl_user.id <= 22259 )";
			$sqlalumno.=" AND (mdl_user.id > 22259 AND mdl_user.id <= 22273 )";
			
			//$sqlalumno .= " AND (mdl_user.id=19042)"; //19029
			
		}

		$sqlalumno .= " AND mdl_context.contextlevel=50
			AND mdl_role_assignments.roleid in (5)
			AND mdl_context.instanceid not in (SELECT mdl_course.id FROM mdl_course WHERE category in (0,18,26,27,38,52,53,55) OR shortname like '%\_TU' )
			GROUP BY username,email, firstname, lastname, ppropio ORDER BY email";

		$listado_alumnos = $DB->get_records_sql($sqlalumno);
		foreach ($listado_alumnos as $alumno) {
			$contenido_email = "Estimad@ " . utf8_decode($alumno->firstname . " " . $alumno->lastname);
			$contenido_email .= "<p><br>Bienvenid@ a la plataforma de <strong><a href='https://www.adistanciafparagon.es' target='_blank'>Formaci&oacute;n Profesional a Distancia del Gobierno de Arag&oacute;n.</a></strong></p>
								<p>Deseamos que la formaci&oacute;n que vas a recibir sea de tu agrado y te ayude a mejorar tus competencias profesionales. Este ciclo se desarrollar&aacute; en modalidad online, lo que te permitir&aacute; gestionar tu propio
								aprendizaje, eligiendo tu horario y ritmo de estudio, contando siempre con el apoyo del tutor. Deseamos que este curso te resulte productivo y beneficioso para tu futuro desarrollo profesional.</p>
								<p><br>Estos son los <strong>datos necesarios para acceder a la Plataforma:</strong></p>
								<p>URL de la Plataforma:<a href='https://www.adistanciafparagon.es' targer='_blank'>adistanciafparagon.es</a></p>
								<ul><li>Usuario: " . $alumno->username . "</li>
								<li>Contrase&ntilde;a: " . $alumno->ppropio . "</li></ul>";
			$contenido_email .= "<br><b>RECOMENDACIONES ANTES DE EMPEZAR</b>
								<br><br>
								<ol>
									<li>
										Antes de empezar, familiar&iacute;zate con la plataforma. En este link encontrar&aacute;s la <strong><a href='https://www.adistanciafparagon.es/pluginfile.php/99220/mod_resource/content/4/Guia%20de%20usuario%20Moodle%20FP.pdf' > Gu&iacutea de usuario </a></strong>donde te explicamos como utilizarla y como encontrar la informaci&oacute;n que necesitas.
										En los pr&oacuteximos d&iacute;as, encontrar&aacutes la misma informaci&oacute;n en formato v&iacute;deo en el espacio <strong><a href='https://www.youtube.com/playlist?list=PL4Qs5FwWJg3FOcN2kHGWEuQEqu9613D4b' target='_blank'>V&iacute;deos Tutoriales</a></strong>. 
										Este verano hemos hecho muchos cambios, y por esa raz&oacute;n, los v&iacute;deos que ves ahora pertenecen a la versi&oacute;n antigua de la plataforma. A partir de la semana que viene, iremos actualizandolos. Para acceder a esta gu&iacute;a y videotutoriales puedes hacerlo tambi&eacute;n
										desde el espacio <strong><a href='https://www.adistanciafparagon.es/course/view.php?id=587' target='_blank'>Ayuda y videotutoriales</a></strong>, es el aula virtual donde pueden consultarse las dudas m&aacute;s frecuentes del uso de la Plataforma.
									</li>
									<br>
									<li>
										Para comprobar los cursos en lo que est&aacute;s matriculado, accede al apartado de la portada de la Plataforma&gt; <strong><a href='https://www.adistanciafparagon.es/my/' target='_blank'>Mis cursos.</a></strong>
									</li>
									<li>
										<ul>
											<li>
												Si existe alg&uacute;n error, comunica la incidencia a trav&eacute;s del <a href='https://www.adistanciafparagon.es/itainnova_tool/incidencia/incidencia.php' target='_blank'><strong>Formulario de Incidencias</strong> </a>situado en la portada de la Plataforma.
											</li>
											<li>
												<p><strong><a dir='ltr' title='Mi entorno de aprendizaje' href='https://youtu.be/ccQs-7dmnIU' target='_blank'>Mi entorno de aprendizaje</a> </strong>- Duraci&oacute;n: 2 minutos, 14 segundos.</p>
												<p><a href='https://youtu.be/ccQs-7dmnIU'><img src='https://img.youtube.com/vi/ccQs-7dmnIU/hqdefault.jpg' alt='' data-ytimg='1' width='196'/></a></p>
											</li>
										</ul>
									</li>
									<li>Tienes acceso al aula 'tutor&iacute;a', el espacio desde donde el coordinador de cada ciclo realizar&aacute; las comunicaciones oficiales (calendarios de ex&aacute;menes, horarios de tutorias, tramites administrativos...) para
										ese ciclo y desde el que podr&aacute;s contactar con el resto de compa&ntilde;eros y profesores del ciclo ( Mi correo o <a href='https://www.adistanciafparagon.es/message/index.php' target='_blank' >Mensajeria interna</a>).
									</li>
								</ol>
								<p><br>Quedando a tu disposici&oacute;n ante cualquier duda, comentario o sugerencia, te saluda atentamente:</p>
								<p><br>El Equipo de la Plataforma de Formaci&oacute;n Profesional a distancia del Gobierno de Arag&oacute;n.</p>";

			$mail->Subject = "DATOS DE ACCESO - Curso 2020-2021";
			$add = $alumno->email;
			
			//$add="rmonton@itainnova.es";

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
