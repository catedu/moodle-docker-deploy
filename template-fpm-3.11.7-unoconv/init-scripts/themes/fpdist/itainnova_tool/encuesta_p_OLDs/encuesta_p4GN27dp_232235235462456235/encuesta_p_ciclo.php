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

$sql_ciclo=
"SELECT name
FROM {course_categories}
WHERE id = :id";

$sql_get_encuesta_id =
"SELECT MAX(encuesta) as encuesta
FROM encuesta
WHERE SUBSTRING(encuesta,9) = 'p' ";

$sql_get_encuesta_optativa =
"	SELECT id
FROM encuesta
WHERE texto = 'Herramientas de comunicación'
AND encuesta = (SELECT MAX(encuesta) as encuesta	FROM encuesta	WHERE SUBSTRING(encuesta,9) = 'p' )";

$sql_get_preguntas_por_tipo =
"	SELECT id,encuesta,fase,orden,texto,tipo
FROM encuesta
WHERE encuesta = :encuesta and fase = :fase and tipo = :tipo
ORDER BY orden";

$sql_get_preguntas =
"	SELECT id,encuesta,fase,orden,texto,tipo
FROM encuesta
WHERE encuesta = :encuesta and fase = :fase
ORDER BY orden";

if (($_POST)):
	//Obtenemos los valores de ciclo y centro pasados por un hidden
	$id_ciclo = (filter_var($_POST['estudios'],FILTER_SANITIZE_NUMBER_INT)%1000);
	$id_centro = intval(filter_var($_POST['estudios'],FILTER_SANITIZE_NUMBER_INT)/1000);
	$nombreciclo = current($DB->get_records_sql($sql_ciclo,array('id'=>$id_ciclo)))->name;
	//Obtenemos el identificador de la última encuesta que se está realizando para los profesores
	$encuesta = current($DB->get_records_sql($sql_get_encuesta_id))->encuesta;
	//Obtenemos el id para hacer las preguntas optativas
	$id_encuesta_optativa = current($DB->get_records_sql($sql_get_encuesta_optativa))->id;
	?>
	<script language='JavaScript'>
	function validar(form) { //verifica que haya llenado los campos
		var valor= true;
		var puntuado = false;
		var pregunta;
		//Para cada pregunta recorremos los checkboxes y comprobamos que se ha elegido un valor
		<?php
		$validar_encuestas = $DB->get_records_sql($sql_get_preguntas_por_tipo,array('encuesta' => $encuesta,'fase'=>1,'tipo'=>'respuesta'));
		foreach ($validar_encuestas as $validar_encuesta) :
			if($validar_encuesta->id<$id_encuesta_optativa):?>
			puntuado = false;
			pregunta = document.getElementsByName('#<?php echo $encuesta.'-'.$id_ciclo."-".$validar_encuesta->id?>');
			for ( var i = 0; i < pregunta.length && valor && !puntuado; i++) {
				if(pregunta[i].checked) puntuado = true;
			}
			if(!puntuado) valor=false;
			<?php
		endif;
	endforeach;?>
	if(!valor) alert('Por favor rellene las preguntas');
	return (valor);
}
function clearChecks(radioName) {
	var radio = document.getElementsByName(radioName);
	for(var x=0;x<radio.length;x++) {
		radio[x].checked = false;
	}
}
</script>
<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
<center><h1 style="font-weight: bold;" >Ciclo: <?php echo $nombreciclo; ?></h1></center>
<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
<h3 style="font-weight: bold;" >PLANIFICACI&Oacute;N Y ORGANIZACI&Oacute;N DEL CICLO</h2>
<center><p>Indica tu grado de acuerdo con las siguientes afirmaciones en una escala de 0 a 10,
	cuyos extremos son: 0= M&iacute;nimo acuerdo, 10= M&aacute;ximo acuerdo,  el grado o nivel de cumplimiento, grado de acuerdo y nivel de satisfacci&oacute;n con los siguientes aspectos
</p></center></br>
<div class="well" style='text-align:left;padding:10px;background:#FEFEFE'>
	<form action="encuesta_p_modulo.php" method="post" name="form_encuesta" onSubmit="return validar(this)">
		<?php // Creamos el formulario leyendolo de la BD.
		$preguntas = $DB->get_records_sql($sql_get_preguntas,array('encuesta'=>$encuesta,'fase'=>1));
		$fin_cabecera="";
		foreach($preguntas as $pregunta):
			if($pregunta->tipo=="titulo"):?>
			<h3><?=$pregunta->texto?></h3></br>
		<?php	elseif($pregunta->tipo=="pregunta"):?>
			<h2><?=$pregunta->texto?> </h2></br>
			<div class="table-responsive">
				<table class="table table-hover table-bordered table-condensed">
					<tr style="background:#f5f5f5">
						<th></th>
						<?php for($i=0;$i<=10;$i++):?>
							<th style='text-align:center'><b><?=$i?></b></th>
						<?php endfor;
						if($pregunta->id>=$id_encuesta_optativa):?>
						<th style='text-align:center'><b>--</b></th> </tr>
					<?php endif;?>
				</tr>
			<?php elseif($pregunta->tipo=="respuesta"):?>
				<tr><td align='center|justify' style='width:60%' ><?=$pregunta->texto?><br></td>
					<?php	for ($i = 0; $i < 11; $i++):?>
						<td style='text-align:center'><input type='radio' name='<?='#'.$pregunta->encuesta.'-'.$id_ciclo.'-'.$pregunta->id?>' id='<?=$pregunta->id?>' value='<?=$i?>'></td>
					<?php endfor;
					if($pregunta->id>=$id_encuesta_optativa):?>
					<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$pregunta->encuesta."-$id_ciclo-".$pregunta->id?>')">Deseleccionar</a></td>
				<?php endif;?>
			</tr>
		<?php elseif($pregunta->tipo=="pregunta_fin"):?>
		</table></div></br>
	<?php endif;
endforeach;
?>
<br><br><center><input type='submit' value='Siguiente >>' class='boton' accesskey='s'></center>
<input type="hidden" name="id_genero" id="id_genero" value="<?php echo "$encuesta-$id_ciclo-1-".$_POST['genero']; ?>">
<input type="hidden" name="id_centro" id="id_centro" value="<?php echo "$encuesta-$id_ciclo-2-$id_centro"; ?>">
<input type="hidden" name="id_ciclo" id="id_ciclo" value="<?php echo "$encuesta-$id_ciclo-3-$id_ciclo"; ?>">
<input type="hidden" name="id_participado" id="id_participado" value="<?php echo "$encuesta-$id_ciclo-1-".$_POST['participado']; ?>">
<input type="hidden" name="id_encuesta" id="id_encuesta" value="<?php echo $_POST['id_encuesta']; ?>">
</form>
</div>
<?php else: ?>
	<META HTTP-EQUIV="Refresh" CONTENT="3;URL=https://www.adistanciafparagon.es">
		<center><h1 style="font-size: 16pt;margin-top:5px;font-weight: bold;">NO EXISTE CICLO en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>
	<?php endif;?>
</div>
<?=$OUTPUT->footer()?>
