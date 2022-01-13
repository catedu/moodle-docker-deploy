<?php
// error_reporting(E_ALL);

require_once('libreria/checkAdmin.php');

function makeButton($link, $text)
{
	$result = '<a class="btn btn-info btn-lg active pull-right" style="width:25vw;" role="button" href="' . $link . '">' . $text . '</a>';
	return $result;
}

$PAGE->set_pagelayout('admin');
$title = 'ITAINNOVA Tools';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
echo $OUTPUT->header();
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
?>

<h2><?= $title ?></h2>

<?php
$num_antiguos = 0;
foreach (glob("*.xlsx") as $excel) {
	if (filectime($excel) < mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"))) {
		if (isset($_POST['deleteexcel']) && $_POST['deleteexcel'] == 1) {
			unlink($excel);
		} else {
			$num_antiguos++;
		}
	}
}
if ($num_antiguos > 0) {
	?>
	<form method="post" action="index.php">
		<div class="alert alert-warning">
			<strong>¡Aviso!</strong> Hay <?= $num_antiguos ?> fichero(s) Excel creado(s) hace una semana

			<input type="hidden" name="deleteexcel" value="1">
			<button style="margin-left:10%" type="submit"><b>Borrar </b><i class="fa fa-trash" aria-hidden="true"></i></button>
	</form>
	</div>
<?php
}
$num_antiguos = 0;
foreach (glob("*.zip") as $zip) {
	if (filectime($zip) < gmmktime(0, 0, 0, date("m"), date("d") - 7, date("Y"))) {
		if (isset($_POST['deletezip']) && $_POST['deletezip'] == 1) {
			unlink($zip);
		} else {
			$num_antiguos++;
		}
	}
}

if ($num_antiguos > 0) {
	?>
	<form method="post" action="index.php">
		<div class="alert alert-warning">
			<strong>¡Aviso!</strong> Hay <?= $num_antiguos ?> fichero(s) Zip creado(s) hace una semana

			<input type="hidden" name="deletezip" value="1">
			<button style="margin-left:10%" type="submit"><b>Borrar </b><i class="fa fa-trash" aria-hidden="true"></i></button>
	</form>
	</div>
<?php
}
$num_antiguos = 0;
foreach (glob("informe_profesores_pdf/*/*.pdf", GLOB_BRACE) as $pdf) {
	if (filectime($pdf) < gmmktime(0, 0, 0, date("m"), date("d") - 7, date("Y"))) {
		if (isset($_POST['deletepdf']) && $_POST['deletepdf'] == 1) {
			unlink($pdf);
		} else {
			$num_antiguos++;
		}
	}
}

if ($num_antiguos > 0) {
	?>
	<form method="post" action="index.php">
		<div class="alert alert-warning">
			<strong>¡Aviso!</strong> Hay <?= $num_antiguos ?> fichero(s) PDF creado(s) hace una semana

			<input type="hidden" name="deletepdf" value="1">
			<button style="margin-left:10%" type="submit"><b>Borrar </b><i class="fa fa-trash" aria-hidden="true"></i></button>
	</form>
	</div>
<?php
}

$table = new html_table();
$table->head = array('Opciones', ' ');
$table->data = array(
	array(
		'Mensaje bienvenida a alumnos.<p style="color:red">Cuidado uso de GET ?email=xxx@xxx.xx</p>',
		makeButton("bienvenida_cursos_alumnos.php", "Enviar mensajes")
	),
	array(
		'Mensaje bienvenida a profesores',
		makeButton("bienvenida_cursos_profesores.php", "Enviar mensajes")
	),
	array(
		'Informe quincenal de actividad en la plataforma que se envia al alumno por email',
		makeButton("envio_masivo_nota.php", "Envio de Datos de Evaluación")
	),
	array(
		'Informe en PDF del seguimiento del profesor, para Educación',
		makeButton("informe_profesores_pdf.php", "Informe Profesores PDF")
	),
	array(
		'Informe en EXCEL enviado por correo al profesor de las estadísticas de sus alumnos. <p style="color:red">Cuidado uso de GET ?curso=000</p>',
		makeButton("excel_profesor_acceso_alumnos.php", "Correo a Profesor Excel datos de sus alumnos")
	),
	array(
		'Visualizador de contrase&ntilde;as',
		makeButton("passwords.php", "Revisar contrase&ntilde;as")
	),
	//array("Datos de Encuestas",
	//makeButton("encuesta_datos.php","Obtener datos de las diferentes encuestas")),
	//array("Datos de Encuestas a Excel",
	//makeButton("encuesta_datos_Excel.php","Obtener datos de las diferentes encuestas")),
	//array("Datos encuesta a profesores Excel",
	//makeButton("encuesta_profesores_Excel.php","Obtener datos de las diferentes encuestas")),
	//array("Informe de actividad de usuarios por curso",
	//makeButton("makecoursereport.php","Crear informe")),
	array(
		"Editar preguntas frecuentes",
		makeButton("FAQ/edit.php", "Editar FAQ")
	)
);
echo html_writer::table($table);

$table = new html_table();
$table->head = array('Encuestas', ' ');
$table->data = array(
	array("Informe de Encuestas",	makeButton("informes/informe_encuesta.php", "Generar informe")),
	array("Encuesta de satisfacci&oacute;n a <strong>alumnos</strong>",	makeButton(glob('encuesta_a*', GLOB_ONLYDIR)[0], "Ver encuesta de alumnos")),
	array("Encuesta de satisfacci&oacute;n a <strong>profesores</strong>",	makeButton(glob('encuesta_p*', GLOB_ONLYDIR)[0], "Ver encuesta de profesores")),
);
echo html_writer::table($table);

$table = new html_table();
$table->head = array('Informes de actividad', ' ');
$table->data = array(
	array('Informe a Coordinadores', makeButton('informes/#coordinadores', 'Coordinadores')),
	array('Informe a los profesores', makeButton('informes/#profesores-editar', 'Profesores')),
	array('Informe a los alumnos', makeButton('informes/#alumnos-editar', 'Alumnos'))
);
echo html_writer::table($table);

/**
 * Gestion de Alumnos
 */
$table = new html_table();
$table->head = array('Gestión de alumnos SIGAD', ' ');
$table->data = array(
	array('Importar listado alumnos SIGAD', makeButton('gestion_alumnos/index.php?tab=importarsigad', 'Importar')),
	array('Listado alumnos Importados SIGAD', makeButton('gestion_alumnos/index.php?tab=listadosigad', 'Listado')),
);
echo html_writer::table($table);

echo $OUTPUT->footer();
?>