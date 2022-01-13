<?php
error_reporting(E_ALL);
global $DB;
require_once("../../config.php");
$PAGE->set_pagelayout('base');
$title = "Encuesta ".(date('Y')-1)." - ".date('Y');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();
require("../libreria/class.utilidades.php");

//Obtenemos los centros desde la base de datos de moodle
$sql_curso_centro =
"	SELECT  cursos.id as curso_id,centros.name as centro , cursos.name as curso, centros.id as centro_id
FROM {course_categories} centros
INNER JOIN {course_categories} cursos on centros.id = cursos.parent
WHERE centros.coursecount = 0
AND cursos.id not in (57,58,59,62,63,64,65)
ORDER BY centros.name ASC;";

$estudios = $DB->get_records_sql($sql_curso_centro);
?>
<script language="JavaScript">
function validar(form) { //verifica que haya llenado los campos
	valor=true;
	if (form.genero.value=="Genero Vacio") {
		alert("Debe rellenar el género");
		valor=false;
	}
	if (form.estudios.value=="default") {
		alert("Debe seleccionar el centro y el curso");
		valor=false;
	}
	if (form.participado.value=="Vacio") {
		alert("Debe rellenar la pregunta de participación");
		valor=false;
	}
	return (valor);
}
</script>

<div style="text-align:center;width:90%; margin:0 auto; padding-top:20px">
	<?php	if ((!$_POST)):?>
		<h1 style="font-weight: bold;padding-bottom:10px;" >CUESTIONARIO DE SATISFACCI&Oacute;N DEL PROFESORADO </h1>
		<div  style='font-size:14pt' class="alert alert-success">
			<p>Este cuestionario tiene la finalidad de recoger tu opini&oacute;n sobre algunos aspectos del ciclo que impartes.</br>
				Por favor contesta con sinceridad. Tus respuestas son muy importantes, puesto que se pretende conocer el grado de satisfacci&oacute;n del profesorado. Las respuestas del cuestionario son an&oacute;nimas y confidenciales.
			</p>
		</div>
		<div style='padding:10px;font-size:14pt'>
			<h2>TU OPINI&Oacute;N ES IMPORTANTE. ¡PARTICIPA!</h2>
			<p><strong>La cumplimentaci&oacute;n de cada cuestionario es AN&Oacute;NIMA y no te llevar&aacute; m&aacute;s de 5 minutos.</strong></p>
			<p>Gracias por tu colaboraci&oacute;n. Es de gran utilidad para mejorar la organizaci&oacute;n de nuestros cursos.</p>
		</div>
		<center>
			<div class="well" style='text-align:left;padding:10px;padding:10px;width:90%'>
				<h4>DATOS ESTADISTICOS</h4>
				<p>Estos datos s&oacute;lo se utilizar&aacute;n con fines estad&iacute;sticos. ITAINNOVA te garantiza la confidencialidad en el tratamiento de los mismos.
				<form action="encuesta_p_ciclo.php" method="post" name="form_encuesta" onSubmit="return validar(this)">
					<h2>1.- G&eacute;nero</h2>
					<div style='margin-left:50px;'>
						<select id='genero' name='genero' >
							<option value="Genero Vacio">Elija G&eacute;nero...</option>
							<option value="Hombre">Hombre</option>
							<option value="Mujer">Mujer</option>
						</select>
					</div>
					<h2>2.- &iquest;Hab&iacute;as participado antes en alg&uacute;n tipo de formaci&oacute;n a distancia?:</h2>
					<div style='margin-left:50px;'>
						<select id='participado' name='participado' >
							<option value="Vacio">...</option>
							<option value="Si">S&iacute;</option>
							<option value="No">No</option>
						</select>
					</div>
					<h2>3.- Centro y el curso en el que impartes:</h2>
					<div style='margin-left:50px;'>
						<select id='estudios' name='estudios' style="width:90%">
							<option value="default">Elija Centro y curso...</option>
							<?php	foreach($estudios as $estudio):?>
								<option value="<?=(($estudio->centro_id*1000)+($estudio->curso_id))?>"><?=$estudio->centro.' : '.$estudio->curso?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div style='margin-left:50px;'>
						<br><br><center><input type='submit' value='Siguiente >>' class='boton' accesskey='s'></center>
						<input type="hidden" name="id_encuesta" id="id_encuesta" value="<?php echo getRandomCode(); ?>">
					</div>
				</form>
			</div>
		</center>
	<?php else: ?>
		<META HTTP-EQUIV="Refresh" CONTENT="3;URL=https://www.adistanciafparagon.es">
			<center><h1 style="font-size: 16pt;margin-top:5px;font-weight: bold;">Usuario no autorizado en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>
		<?php endif;?>
	</div>
	<?=$OUTPUT->footer()?>
