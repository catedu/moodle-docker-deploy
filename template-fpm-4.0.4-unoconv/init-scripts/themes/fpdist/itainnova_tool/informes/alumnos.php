<?php
require_once('../libreria/checkAdmin.php');
ob_implicit_flush(true);
//header('Content-Encoding: none;');
require_once('../../config.php');

define(STATS, 'stats');
define(FOROS, 'foros');
define(VISITAS_MODULO, 'visitasm');
define(EVALUACIONES_REALIZADAS, 'test');
define(EVALUACIONES_PENDIENTES, 'testp');
define(RECURSOS_REALIZADOS, 'recursos');
define(RECURSOS_PENDIENTES, 'recursosp');

$urlBodyTemplate = "../Fragmentos_Mail/informe_actividad_alumnos/body.html";
$editor1 = file_get_contents($urlBodyTemplate);
$title = "Informe de actividad a los alumnos";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
$PAGE->set_pagelayout('embedded');
echo $OUTPUT->header();
$step = required_param('step', PARAM_INT);
//Obtenemos el listado de asignaturas por alumno

$sql_alumno_asignaturas = "SELECT user.id as userid,user.username, ppropio.ppropio as password, 
user.firstname, user.lastname, user.email,GROUP_CONCAT(DISTINCT(course.id)) as courses
FROM {user} user
JOIN {role_assignments} role_assignments ON user.id = role_assignments.userid
JOIN {context} context ON role_assignments.contextid = context.id
JOIN ppropio ON user.id=ppropio.idd
JOIN {user_info_data} user_info_data ON user.id = user_info_data.userid
JOIN {user_enrolments} user_enrolments on user_enrolments.userid = user.id
JOIN {enrol} enrol ON enrol.id = user_enrolments.enrolid
JOIN {course} course ON course.id = enrol.courseid
WHERE user.suspended=0
AND user.firstname not like '%\_%'
AND user.id not in (SELECT courseid FROM {itainnova_log} WHERE source = :source AND logdate = :logdate )
AND role_assignments.roleid in (5)
AND enrol.status = 0
AND context.instanceid not in
(SELECT course.id FROM {course} course WHERE category in (0,18,26,27,38,52,53,55) OR course.shortname like '%\_TU' )
AND course.shortname not like '%\_TU'
AND course.shortname <> 'AYUDA'
GROUP BY userid,user.username,password,user.firstname,user.lastname,user.email
ORDER BY email";

//Consulta para las pruebas usando cualquier usuario comentar el envio
// AND user.email='abrahamruiz2002@gmail.com'


$usuarios = $DB->get_records_sql($sql_alumno_asignaturas, array('source' => basename(__FILE__), 'logdate' => date('Y-m-d')));
$total = count($usuarios);
$usuarios = array_slice($usuarios, 0, $step, true);

?>

<div style="width:90%;margin-left:10%;">
	<h2> Generando informes. <div style="display:inline" id="pending"><?= $total ?></div> usuarios pendientes</h2>
</div>

<?php

//Recorremos cada usuario
foreach ($usuarios as $user) {

	// Si el usuario no está activo pasamos a la siguiente iteración
	if($user->suspended != 0) continue;

	echo "<p><i id='$user->userid' style='visibility:hidden' class='fa fa-file-text-o' aria-hidden='true'></i> 
		<span style='display:inline-block; width: 15px;'></span>$user->firstname $user->lastname ($user->email)</p>";
	
	ob_end_flush();
	flush();
	ini_set('max_execution_time', 0); //0=NOLIMIT
	
	$data = array();
	$cursos = explode(',', $user->courses);
	
	// echo '<p>USUARIO: '.$user->username.'</p>';

	//Numero de conexiones totales a la plataforma
	$num_conexiones = $DB->count_records('logstore_standard_log', array('action' => 'loggedin', 'userid' => $user->userid));
	$data[$curso->fullname][STATS]['Num. de conexiones a la plataforma'] = $num_conexiones;
	unset($num_conexiones);

	//Cada curso del alumno
	foreach ($cursos as $courseid) {
		
		//Obtenemos el nombre
		$curso = get_course($courseid);

		//Numero de conexiones totales al curso en concreto
		$num_visitas_curso = $DB->count_records('logstore_standard_log', array('target' => 'course', 'action' => 'viewed', 'userid' => $user->userid, 'courseid' => $courseid));
		$data[$curso->fullname][STATS]['Num. Visitas al curso'] = $num_visitas_curso;
		unset($num_visitas_curso);

		// echo '<p>CURSO: '.$curso->fullname.'</p>';

		// Con esta función podemos recuperar toda la información relacionada con el curso
		// $modinfo = get_fast_modinfo($curso);
		// print_object($modinfo);

		//Para cada seccion
		$secciones = $DB->get_records('course_sections', array('course' => $courseid, 'visible' => 1));

		foreach ($secciones as $seccion) {

			$sql_modulos = "SELECT cm.*, mods.name AS nombre
								FROM {course_modules} cm
								JOIN {modules} mods
								ON cm.module = mods.id
								WHERE cm.course = :course
								AND cm.section = :section
								AND mods.visible = 1";

			$course_modules = $DB->get_records_sql($sql_modulos, array('course' => $courseid, 'section' => $seccion->id, 'visible' => 1));
			// $nombre_seccion = filter_var($seccion->summary, FILTER_SANITIZE_STRING);
			// echo "<p>$nombre_seccion</p>";

			foreach ($course_modules as $modulo) {

				// Recuperamos la info del módulo
				// $cm = $modinfo->get_cm($modulo->id);
				
				switch ($modulo->nombre) {
					case 'assign': #1

						$sql_tarea = "SELECT status
								FROM {assign} a
								JOIN {assign_submission} s
								ON a.id = s.assignment
								WHERE a.id = :instance
								AND s.userid = :userid";

						$estado_tarea = $DB->get_records_sql($sql_tarea, array('instance' => $modulo->instance, 'userid' => $user->userid));
						$nombre_tarea = $DB->get_records('assign', array('id' => $modulo->instance));
						$nombre_tarea = current($nombre_tarea)->name;

						switch (current($estado_tarea)->status) {
							case 'submitted':
								$estado_tarea = 'Entregada';
								$data[$curso->fullname][EVALUACIONES_REALIZADAS][$nombre_tarea] = $estado_tarea;
								break;
							case 'new':
							case null:
								$estado_tarea = 'Pendiente ';
								$plazo = current($DB->get_records('assign', array('id' => $modulo->instance)));
								if ($plazo->cutoffdate != 0) {
									$estado_tarea .= 'hasta: ' . date("d-m-Y H:i:s", $plazo->cutoffdate);
								} elseif ($plazo->duedate != 0) {
									$estado_tarea .= 'hasta: ' . date("d-m-Y H:i:s", $plazo->duedate);
								}
								$data[$curso->fullname][EVALUACIONES_PENDIENTES][$nombre_tarea] = $estado_tarea;
								break;
							case 'draft':
								$estado_tarea = 'Borrador ';
								$plazo = current($DB->get_records('assign', array('id' => $modulo->instance)));
								if ($plazo->cutoffdate != 0) {
									$estado_tarea .= 'hasta: ' . date("d-m-Y H:i:s", $plazo->cutoffdate);
								} elseif ($plazo->duedate != 0) {
									$estado_tarea .= 'hasta: ' . date("d-m-Y H:i:s", $plazo->duedate);
								}
								$data[$curso->fullname][EVALUACIONES_PENDIENTES][$nombre_tarea] = $estado_tarea;
								break;
						}
						unset($estado_tarea);
						unset($nombre_tarea);
						break;
					case 'choice': #6
						$sql_respuestas_choice = "SELECT count(a.id)
							FROM {choice_answers} a
							JOIN {choice} c ON a.choiceid = c.id
							WHERE c.id = :instance
							AND   a.userid = :userid";
						$respondido = $DB->record_exists_sql($sql_respuestas_choice, array('instance' => $modulo->instance, 'userid' => $user->id));
						$choice = $DB->get_records('choice', array('id' => $module->instance));
						$nombre_choice = current($choice)->name;
						if ($respondido) {
							$data[$curso->fullname][EVALUACIONES_REALIZADAS][$nombre_choice] = 'Respondido';
						} else {
							$estado_choice = "Pendiente ";
							if ($choice->timeclose > 0) {
								$estado_choice .= 'hasta: ' . date("d-m-Y H:i:s", $choice->timeclose);
							}
							$data[$curso->fullname][EVALUACIONES_PENDIENTES][$nombre_choice] = $estado_choice;
						}
						unset($respondido);
						unset($choice);
						unset($nombre_choice);
						unset($estado_choice);
						break;
					case 'data': #7
						$nombre_data = $DB->get_records('data', array('id' => $modulo->instance));
						$nombre_data = current($nombre_data)->name;
						$sql_data = "SELECT COUNT(r.dataid)
							FROM {data_records} r
							JOIN {data} d ON  r.dataid = d.id
							WHERE d.id = :instance
							AND r.userid = :userid ";
						$num_escrito = $DB->count_records_sql($sql_data, array('instance' => $modulo->instance, 'userid' => $user->userid));
						if ($num_escrito) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_data] = $num_escrito;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_data] = 0;;
						}
						unset($nombre_data);
						unset($num_escrito);
						break;
					case 'folder': #9
						$visitas_carpeta = $DB->count_records('logstore_standard_log', array('component' => 'mod_folder', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						$nombre_carpeta = $DB->get_records('folder', array('id' => $modulo->instance));
						$nombre_carpeta = current($nombre_carpeta)->name;
						if ($visitas_carpeta > 0) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_carpeta] = $visitas_carpeta;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_carpeta] = 0;
						}
						unset($visitas_carpeta);
						unset($nombre_carpeta);
						//echo "<p>$nombre_carpeta V:$visitas_carpeta</p>";
						break;
					case 'forum': #10
						$visitas_foro = $DB->count_records('logstore_standard_log', array('component' => 'mod_forum', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						$nombre_foro = $DB->get_records('forum', array('id' => $modulo->instance));
						$nombre_foro = current($nombre_foro)->name;
						$sql_num_post_foro = " SELECT COUNT(message) AS num
							FROM {forum_posts} p
							JOIN {forum_discussions} d ON d.id = p.discussion
							WHERE d.forum = :instance
							AND p.userid = :userid;";
						$post_foro = $DB->count_records_sql($sql_num_post_foro, array('instance' => $modulo->instance, 'userid' => $user->userid));
						$data[$curso->fullname][FOROS][$nombre_foro] = "$visitas_foro visitas y $post_foro posts";

						// echo "<p>Foro: $nombre_foro V:$visitas_foro P:$post_foro</p>";

						unset($post_foro);
						unset($nombre_foro);
						unset($visitas_foro);
						break;
					case 'glossary': #11
						$nombre_glosario = $DB->get_records('glossary', array('id' => $modulo->instance));
						$nombre_glosario = current($nombre_glosario)->name;
						$num_visitas_glosario = $DB->count_records('logstore_standard_log', array('component' => 'mod_glossary', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						if ($num_visitas_glosario > 0) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_glosario] = $num_visitas_glosario;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_glosario] = 0;
						}

						// echo "<p>$nombre_glosario V:$num_visitas_glosario</p>";

						unset($num_visitas_glosario);
						unset($nombre_glosario);
						break;
					case 'lesson': #14
						$sql_intentos_lesson = "SELECT l.id
							FROM  {lesson_attempts} a
							JOIN  {lesson} l ON a.lessonid=l.id
							WHERE l.id = :instance
							AND   a.userid = :userid";
						$sql_nota_lesson = "SELECT g.grade
							FROM  {lesson_grades} g
							JOIN  {lesson} l ON g.lessonid = l.id
							WHERE l.id = :instance
							AND   g.userid = :userid
							ORDER BY g.completed DESC LIMIT 1";
						$nombre_lesson = $DB->get_records('lesson', array('id' => $modulo->instance));
						$nombre_lesson = current($nombre_lesson)->name;
						if ($DB->record_exists_sql($sql_intentos_lesson, array('instance' => $modulo->instance, 'userid' => $user->userid))) {
							$nota = $DB->get_records_sql($sql_nota_lesson, array('instance' => $modulo->instance, 'userid' => $user->userid));
							$nota = (current($nota)->grade != null) ? ' Nota: ' . number_format(current($nota)->grade, 2) : '';
							$data[$curso->fullname][EVALUACIONES_REALIZADAS][$nombre_lesson] = $nota;
						} else {
							$data[$curso->fullname][EVALUACIONES_PENDIENTES][$nombre_lesson] = 'Pendiente';
						}
						break;
					case 'page': #18
						$nombre_pagina = $DB->get_records('page', array('id' => $modulo->instance));
						$nombre_pagina = current($nombre_pagina)->name;
						$num_visitas_pagina = $DB->count_records('logstore_standard_log', array('component' => 'mod_page', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						if ($num_visitas_pagina) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_pagina] = $num_visitas_pagina;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_pagina] = 0;
						}
						break;
					case 'quiz': #19
						$quiz = $DB->get_records('quiz', array('id' => $modulo->instance));
						$nombre_quiz = current($quiz)->name;
						$sql_quiz_grades = "SELECT g.grade as grade
							FROM  {quiz_grades} g
							JOIN  {quiz} q ON g.quiz=q.id
							WHERE q.id = :instance
							AND   g.userid = :userid";
						$nota = $DB->get_records_sql($sql_quiz_grades, array('instance' => $modulo->instance, 'userid' => $user->userid));
						if (count($nota) > 0) {
							$data[$curso->fullname][EVALUACIONES_REALIZADAS][$nombre_quiz] = number_format(current($nota)->grade, 2);
						} else {
							$estado_quiz = 'Pendiente';
							if ($quiz->timeclose > 0) {
								$estado_quiz = 'hasta ' . date("d-m-Y H:i:s", $quiz->timeclose);
							}
							$data[$curso->fullname][EVALUACIONES_PENDIENTES][$nombre_quiz] = $estado_quiz;
						}
						unset($quiz);
						unset($nombre_quiz);
						unset($nota);
						unset($estado_quiz);
						//echo "<p>$nombre_quiz: $nota</p>";
						break;
					case 'resource': #20
						$nombre_recurso = $DB->get_records('resource', array('id' => $modulo->instance));
						$nombre_recurso = current($nombre_recurso)->name;
						$num_visitas_recurso = $DB->count_records('logstore_standard_log', array('component' => 'mod_resource', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						if ($num_visitas_recurso) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_recurso] = $num_visitas_recurso;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_recurso] = 0;
						}
						// echo "<p>$nombre_recurso V:$num_visitas_recurso</p>";
						break;

					case 'scorm': #21
						$var = '';
						$nombre_scorm = $DB->get_records('scorm', array('id' => $modulo->instance));
						$nombre_scorm = current($nombre_scorm)->name;
						$sql_scorm = "SELECT COUNT(t.id)
							FROM  {scorm_scoes} s
							JOIN  {scorm_scoes_track} t ON t.scoid=s.id
							WHERE t.userid = :userid
							AND   s.scorm = :instance
							AND   element = :element
							AND   value = :value ";
						$accedido = $DB->count_records_sql($sql_scorm, array('userid' => $user->userid, 'instance' => $modulo->instance, 'element' => 'cmi.core.lesson_status', 'value' => 'completed'));
						if ($accedido) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_scorm] = 'Revisada';
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_scorm] = '';
						}
						//echo "<p>$nombre_scorm: $accedido</p>";
						break;

					case 'url': #24
						$nombre_url = $DB->get_records('url', array('id' => $modulo->instance));
						$nombre_url = current($nombre_url)->name;
						$num_visitas_url = $DB->count_records('logstore_standard_log', array('component' => 'mod_url', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						if ($num_visitas_url) {
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_url] = $num_visitas_url;
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_url] = '';
						}
						break;
					case 'wiki': #25
						$sql_wiki_aportaciones = "SELECT COUNT(p.id)
							FROM  {wiki_versions} v
							JOIN  {wiki_pages} p    ON v.pageid = p.id
							JOIN  {wiki_subwikis} s ON s.id = p.subwikiid
							JOIN  {wiki} w          ON s.wikiid = w.id
							WHERE v.userid = :userid
							AND   w.id = :instance";
						
						$num_visitas_wiki = $DB->count_records('logstore_standard_log', array('component' => 'mod_wiki', 'action' => 'viewed', 'userid' => $user->userid, 'contextinstanceid' => $modulo->id));
						$nombre_wiki = $DB->get_records('wiki', array('id' => $modulo->instance));
						$nombre_wiki = current($nombre_wiki)->name;

						if ($num_visitas_wiki) {
							$num_aportaciones_wiki = $DB->count_records_sql($sql_wiki_aportaciones, array('userid' => $user->userid, 'instance' => $modulo->instance));
							$data[$curso->fullname][RECURSOS_REALIZADOS][$nombre_wiki] = "$num_visitas_wiki visitas y $num_aportaciones_wiki aportaciones";
						} else {
							$data[$curso->fullname][RECURSOS_PENDIENTES][$nombre_wiki] = 0;
						}
						break;
					default:
						$recurso = current($DB->get_records('modules', array('id' => $modulo->module)));
						// echo '<p><strong>ATENCI&Oacute;N:</strong> Se ha omitido el recurso ' . $recurso->name . ' id:' . $modulo->module . '</p>';
					break;
				} //END SWITCH MODULO
			} //END FOREACH MODULO
			
		} //END FOREACH SECCION
	} //END FOREACH CURSO

	require_once('../libreria/mpdf7/autoload.php');
	$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
	$mpdf->shrink_tables_to_fit = 1; //no resize
	//Lo ciframos con una clave de 128 bits, para evitar ediciones por parte de los alumnos.
	//Permitimos copiar e imprimir
	//https://mpdf.github.io/reference/mpdf-functions/setprotection.html
	$mpdf->SetProtection(array('copy', 'print', 'print-highres'), '', '1nf0rm3.2107', 128);
	//$mpdf->debug = true;
	// Load a stylesheet

	// The parameter 1 tells that this is css/style only and no body/html/text
	$stylesheet = file_get_contents('./css/alumnos.css');
	$mpdf->WriteHTML($stylesheet, 1);

	$mpdf->WriteHTML("<h1 align='center'>Plataforma de Formaci&oacute;n Profesional a Distancia del Gobierno de Arag&oacute;n</h1>");
	$mpdf->WriteHTML("<br><h2 align='center'>Informe de seguimiento</h2>");
	$mpdf->WriteHTML('<div style="position: absolute; margin-top: auto; margin-left: auto; margin-right: auto; margin-bottom: auto;"><img src="./images/portada_informe.png" width="90%" /></div>');
	$mpdf->WriteHTML("<div style='position: fixed; left: 0mm; bottom: 0mm;'>
		<table class='tablaDatos' align='left'>
		<tbody><tr><td><strong>Nombre:</strong></td><td>$user->firstname</td></tr>
		<tr><td><strong>Apellidos:</strong></td><td>$user->lastname</td></tr>
		<tr><td><strong>Fecha informe:</strong></td><td>" . date('d-m-Y') . "</td></tr>
		</tbody></table></div>");

	$pass = $DB->get_records_sql('SELECT ppropio FROM ppropio WHERE idd = :userid', array('userid' => $user->userid));
	$pass = (current($pass)->ppropio);

	$mpdf->WriteHTML("
		<div style='position: fixed; right: 0mm; bottom: 0mm;'>
		<table class='tablaDatos' align='left'>
		<tbody><tr><td><strong>Usuario:</strong></td><td>$user->username</td></tr>
		<tr><td><strong>Contrase&ntilde;a:</strong></td><td>$pass</td></tr>
		<tr><td><strong>Email:</strong></td><td>$user->email</td></tr>
		</tbody></table></div>");

	foreach ($data as $nombre => $curso) {
		$mpdf->addPage();
		$mpdf->WriteHTML("<table align='left' class='curso'>
			<tbody><tr><td class='td-curso'><strong>Curso</strong></td><td>$nombre</td></tr>
			</tbody></table>");
		$mpdf->SetColumns(2, "justify", 5);
		//Imprimimos los STATS
		$mpdf->WriteHTML("<table align='left'><tbody>");
		$mpdf->WriteHTML("<tr><th>Estad&iacute;sticas</th><th>Valor</th></tr>");
		$row = 0;
		foreach ($curso[STATS] as $stat => $value) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$stat</strong></td><td>$value</td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		$mpdf->AddColumn();
		//Imprimimos los FOROS
		$mpdf->WriteHTML("<table align='left' class='tabla'><tbody>");
		$mpdf->WriteHTML("<tr><th>Foros</th><th>Actividad</th></tr>");
		$row = 0;
		foreach ($curso[FOROS] as $forum => $value) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$forum</strong></td><td>$value</td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		//Imprimimos los EVALUACIONES_REALIZADAS
		$mpdf->SetColumns(0);
		$mpdf->SetColumns(2, "justify", 5);
		$mpdf->WriteHTML("<table align='left' class='tabla'><tbody>");
		$mpdf->WriteHTML("<tr><th>Evaluaciones completadas</th><th>Nota</th></tr>");
		$row = 0;
		foreach ($curso[EVALUACIONES_REALIZADAS] as $ev => $state) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$ev</strong></td><td>$state</td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		$mpdf->AddColumn();
		//Imprimimos los EVALUACIONES_PENDIENTES
		$mpdf->WriteHTML("<table align='left' class='tabla'><tbody>");
		$mpdf->WriteHTML("<tr><th>Evaluaciones sin completar</th><th>Estado</th></tr>");
		$row = 0;
		foreach ($curso[EVALUACIONES_PENDIENTES] as $ev => $state) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$ev</strong></td><td>$state</td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		$mpdf->SetColumns(0);
		$mpdf->SetColumns(2, "justify", 5);
		//Imprimimos los RECURSOS_REALIZADOS
		$mpdf->WriteHTML("<table align='left' class='tabla'><tbody>");
		$mpdf->WriteHTML("<tr><th>Recursos que has visitado</th><th>Visitas</th></tr>");
		$row = 0;
		foreach ($curso[RECURSOS_REALIZADOS] as $recurso => $state) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$recurso</strong></td><td>$state</td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		$mpdf->AddColumn();
		//Imprimimos los RECURSOS_PENDIENTES
		$mpdf->WriteHTML("<table align='left' class='tabla'><tbody>");
		$mpdf->WriteHTML("<tr><th>Recursos que a&uacute;n no has visitado</th></tr>");
		//var_dump($curso[RECURSOS_PENDIENTES]);
		$row = 0;
		foreach ($curso[RECURSOS_PENDIENTES] as $recurso => $state) {
			$mpdf->WriteHTML("<tr class='tr-" . $row % 2 . "'><td class='td-1'><strong>$recurso</strong></td></tr>");
			$row++;
		}
		$mpdf->WriteHTML("</tbody></table>");
		$mpdf->SetColumns(0);
	}

	$nombre_fic = 'alumnos/' . $user->userid . '_' . date("d-m-Y") . '.pdf';
	$mpdf->Output($nombre_fic, \Mpdf\Output\Destination::FILE);

	unset($data);
	$total--;
	echo "<script>
		document.getElementById('$user->userid').style.visibility = 'visible';
		document.getElementById('pending').innerHTML='$total';
	</script>";

} //END FOREACH USER


/***************************
 ********* EMAILING ********
 ***************************/

require("../libreria/PHPMailer_5.2/PHPMailerAutoload.php");
$errors = 0;
reset($usuarios);
//Enviamos los correos
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->IsHTML(true);
$mail->Host = "smtp.aragon.es";
$mail->SMTPSecure = "tls";
$mail->SMTPAuth = true;
// credenciales usuario
$mail->Username = "fpdistancia@aragon.es";
$mail->Password = "xxx";
$mail->From = "fpdistancia@aragon.es";
$mail->FromName = "Administrador de Plataforma FP";
$mail->Subject = 'Plataforma FP: Informe de seguimiento ' . date("d-m-Y");
foreach ($usuarios as $user) {
	
	// Si el usuario está activo enviamos el correo
	if($user->suspended == 0){

		$contenido_email = '<p>Estimad@ ' . utf8_decode($user->firstname) . '</p>' . $editor1;
		ob_end_flush();
		flush();

		$mail->AddAddress($user->email);
		
		//RECORDAR BLOQUEAR PARA LAS PRUEBAS
		//$mail->AddAddress('admin.moodlefpdistancia@aragon.es');
		//$mail->AddAddress('cpina@itainnova.es');
		

		$mail->addAttachment('./alumnos/' . $user->userid . '_' . date("d-m-Y") . '.pdf', '', 'base64', 'application/pdf', 'attachment');
		$mail->Body = $contenido_email;
		$enviado = false;
		for ($i = 0; $i < 10 && !$enviado; $i++) {
			$enviado = $mail->Send();
		}
		
		if (!$enviado) {
			echo "$mail->ErrorInfo";
			$logentry = new stdClass();
			$logentry->source   = basename(__FILE__);
			$logentry->courseid = $user->userid;
			$logentry->log      = 'ERROR: ' . $user->firstname . ' ' . $user->lastname;
			$logentry->logdate  = date('Y-m-d');
			$logentry->logtime  = date('H:i:s');
			$lastinsertid = $DB->insert_record('itainnova_log', $logentry, false);
			echo "<script>
				document.getElementById('$user->userid').className = 'fa fa-exclamation';
			</script>";
			$errors++;
		} else {
			$logentry = new stdClass();
			$logentry->source   = basename(__FILE__);
			$logentry->courseid = $user->userid;
			$logentry->log      = $user->email;
			$logentry->logdate  = date('Y-m-d');
			$logentry->logtime  = date('H:i:s');
			$lastinsertid = $DB->insert_record('itainnova_log', $logentry, false);
			echo "<script>
				document.getElementById('$user->userid').className = 'fa fa-envelope';
			</script>";
		}
		$mail->ClearAddresses();
		$mail->ClearAllRecipients();
		$mail->clearAttachments();
	}
}

//Si quedan informes por generar, refrescamos
if ((count($usuarios) > count($batch)) && $errors == 0) {
	echo '<meta http-equiv="refresh" content="0">';
}

echo $OUTPUT->footer();
