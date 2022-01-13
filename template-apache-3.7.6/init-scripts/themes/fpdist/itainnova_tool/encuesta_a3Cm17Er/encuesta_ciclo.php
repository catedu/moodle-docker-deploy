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

require_once('../../config.php');

$sql_get_encuesta_id =
"SELECT MAX(encuesta) AS encuesta
FROM encuesta
WHERE SUBSTRING(encuesta,9) = 'a' ";

$sql_nombre_ciclo =
"	SELECT name
FROM {course_categories}
WHERE id = :id_ciclo";

$sql_get_preguntas =
"	SELECT id,encuesta,fase,orden,texto,tipo
FROM encuesta
WHERE encuesta = :encuesta and fase = :fase
ORDER BY orden";

$sql_get_preguntas_por_tipo =
"	SELECT id,encuesta,fase,orden,texto,tipo
FROM encuesta
WHERE encuesta = :encuesta and fase = :fase and tipo = :tipo
ORDER BY orden";

if (($_POST)):
	//Obtenemos el identificador de la última encuesta que se está realizando para los alumnos
	$encuesta = current($DB->get_records_sql($sql_get_encuesta_id))->encuesta;

	//Obtenemos los valores de ciclo y centro pasados por un hidden
	$id_ciclo = explode("-",$_POST['estudios'])[1];
	echo "id_ciclo: " . $id_ciclo . "<br>";
	$id_centro = explode("-",$_POST['estudios'])[0];
	echo "id_centro: " . $id_centro . "<br>";
	$id_encuesta = filter_var($_POST['id_encuesta'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);


	$nombreciclo = current($DB->get_records_sql($sql_nombre_ciclo,array('id_ciclo'=>$id_ciclo)))->name;
	?>
	<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
	<center><h1 style="font-weight: bold;" >Ciclo: <?php echo $nombreciclo; ?></h3></center>
		<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
		<h3 style="font-weight: bold;" >PLANIFICACI&Oacute;N Y ORGANIZACI&Oacute;N DEL CICLO</h2>
	<center>
	<p>Indica tu grado de acuerdo con las siguientes afirmaciones en una escala de 0 a 10,
		cuyos extremos son: 0= M&iacute;nimo acuerdo, 10= M&aacute;ximo acuerdo,  el grado o nivel de cumplimiento, grado de acuerdo y nivel de satisfacci&oacute;n con los siguientes aspectos
	</p></br>
	</center>
	<div class="well" style='text-align:left;padding:10px;background:#FEFEFE'>
		<form action="encuesta_modulo.php" method="post" name="form_encuesta" onSubmit="return validar(this)">
			<?php // Creamos el formulario leyendolo de la BD.
			$sqlinsert = $DB->get_records_sql($sql_get_preguntas,array('encuesta'=>$encuesta,'fase'=>1));
			$fin_cabecera="";
			foreach($sqlinsert as $rowsql)
			switch ($rowsql->tipo):
				case 'titulo':?>
				<?=$fin_cabecera?>
				<h2><?=$rowsql->texto?></h2></br>
				<div class="table-responsive">
					<table class="table table-hover table-bordered table-condensed">
						<tr style="background:#f5f5f5">
							<th></th>
							<?php	for($i = 0;$i<=10;$i++)://Imprimimos la puntuación 0 -> 10?>
								<th style="text-align:center"><b><?=$i?></b></th>
							<?php	endfor;
							$fin_cabecera="</table></div></br>";?>
						</tr>
						<?php
						break;
						case 'respuesta':?>
						<tr><td align='center|justify' style="width:60%" ><?=$rowsql->texto?></br></br></td>
							<!--checkboxes-->
							<?php	for ($i = 0; $i < 11; $i++):?>
								<td  style="text-align:center"> <input  type='radio' name='#<?=$rowsql->encuesta."-".$id_ciclo."-".$rowsql->id?>' id='<?=$rowsql->id?>' value='<?=$i?>' >  </td>
							<?php endfor;?>
						</tr>
						<?php	break;endswitch;?>
					</table>
				</div>
			</br>
			<br>
			<br>
			<center>
				<input type='submit' value='Siguiente >>' class='boton' accesskey='s'>
			</center>
			<input type="hidden" name="id_genero" id="id_genero" value="<?php echo "$encuesta-$id_ciclo-1-".$_POST['genero']; ?>">
			<input type="hidden" name="id_centro" id="id_centro" value="<?php echo "$encuesta-$id_ciclo-2-$id_centro"; ?>">
			<input type="hidden" name="id_ciclo" id="id_ciclo" value="<?php echo "$encuesta-$id_ciclo-3-$id_ciclo"; ?>">
			<input type="hidden" name="id_encuesta" id="id_encuesta" value="<?php echo $_POST['id_encuesta']; ?>">
		</form>
	</div>
<?php else:?>
	<meta HTTP-EQUIV="Refresh" CONTENT="3;URL=https://www.adistanciafparagon.es">
	<center><h1 style="font-size: 16pt;margin-top:5px;font-weight: bold;">NO EXISTE CICLO en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>
<?php endif;?>
</div>
<script language='JavaScript'>
function validar(form) { //verifica que haya llenado los campos
	var valor= true;
	var puntuado = false;
	var pregunta;
	//Para cada pregunta recorremos los checkboxes y comprobamos que se ha elegido un valor
	<?php
	$sqlinsert = $DB->get_records_sql($sql_get_preguntas_por_tipo,array('encuesta'=>$encuesta,'tipo'=>'respuesta','fase'=>1));

	foreach($sqlinsert as $rowsql):?>
	puntuado = false;
	pregunta = document.getElementsByName('#<?php echo $rowsql->encuesta.'-'.$id_ciclo."-".$rowsql->id?>');
	for ( var i = 0; i < pregunta.length && valor && !puntuado; i++) {
		if(pregunta[i].checked) puntuado = true;
	}
	if(!puntuado) valor=false;
	<?php	endforeach;?>
	if(!valor) alert('Por favor rellene TODAS las preguntas');
	return (valor);
}
</script>
<?=$OUTPUT->footer()?>
