<?php
error_reporting(E_ALL);
global $DB;
require_once("../../config.php");
$PAGE->set_pagelayout('base');
$title              = "Encuesta ".(date('Y')-1)." - ".date('Y')."Ciclo Modulo";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
require_once('../../config.php');
echo $OUTPUT->header();

$sql_check_exists   =
"	SELECT * FROM encuesta_datos
WHERE id_encuesta = :id_encuesta
AND encuesta = :encuesta
AND fase = :fase
AND `codigo 1` = :codigo1
AND `codigo 2` = :codigo2
AND `codigo 3` = :codigo3
";

$sql_insert_values  =
"	INSERT INTO encuesta_datos(`id`,`id_encuesta`, `encuesta`,`fase`, `codigo 1`, `codigo 2`, `codigo 3`, `respuesta 1`, `respuesta 2`)
VALUES (NULL,:id_encuesta,:encuesta,:fase,:codigo1,:codigo2,:codigo3,:respuesta1,:respuesta2)
";

$sql_nombre_ciclo =
"	SELECT name
FROM {course_categories}
WHERE id = :id_ciclo";

$sql_get_preguntas =
"	SELECT id,encuesta,fase,orden,texto,tipo
FROM encuesta
WHERE encuesta = :encuesta and fase = :fase
ORDER BY orden";

$sql_get_curso = "SELECT id,fullname, shortname
FROM {course}
WHERE category = :course
AND shortname NOT LIKE '%t'
ORDER BY shortname";

if(($_POST)):
	$id_encuesta       = filter_var($_POST['id_encuesta'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	foreach($_POST as $nombre_campo => $valor):
		//echo $nombre_campo.": ".$valor."<br>";
		//Saneamos todos los valores recibidos por el post
		if (substr($nombre_campo,0,1)=='#'){
			$arraycampo      =  explode('-',$nombre_campo,3);
			$encuesta        = filter_var(substr($arraycampo[0],1,FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			$codigo1         = filter_var($arraycampo[1],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$codigo2         = filter_var($arraycampo[2],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$valor           = filter_var($valor,FILTER_SANITIZE_NUMBER_INT);
			//Insertamos únicamente si no hay datos introducidos ya según la id_encuesta, encuesta, fase... etc
			if(!$DB->record_exists_sql($sql_check_exists,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>1,'codigo1'=> $codigo1,'codigo2'=>'','codigo3' => $codigo2)))
				$DB->execute($sql_insert_values,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>1,'codigo1'=>$codigo1,'codigo2'=>'','codigo3'=>$codigo2,'respuesta1'=>$valor,'respuesta2'=>''));
		}elseif(($nombre_campo=='id_genero') ||($nombre_campo=='id_centro')||($nombre_campo=='id_ciclo')){
			$arraycampo     = explode('-',$valor,4);
			$encuesta       = filter_var($arraycampo[0],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$codigo1        = filter_var($arraycampo[1],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$codigo2        = filter_var($arraycampo[2],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$valorcampo     = $arraycampo[3];
			$valorcampo     = filter_var($valorcampo,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if($nombre_campo=='id_ciclo')
			$ciclo          = $valorcampo;
			//echo "ciclo: ".$ciclo."<br>";

			//Si no está guardado, lo insertamos
			if(!$DB->record_exists_sql($sql_check_exists,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>0,'codigo1'=>$codigo1,'codigo2'=>'','codigo3'=>$codigo2)))
				$DB->execute($sql_insert_values,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>0,'codigo1'=>$codigo1,'codigo2'=>$nombre_campo,'codigo3'=>$codigo2,'respuesta1'=>-1,'respuesta2'=>$valorcampo));
		}//nombre_campo == id_genero || id_centro || id_ciclo
	endforeach;
	$nombreciclo = current($DB->get_records_sql($sql_nombre_ciclo,array('id_ciclo'=>$ciclo)))->name;?>
	<script type='text/javascript'>
	function clearChecks(radioName) {
		var radio         = document.getElementsByName(radioName);
		for(var x         = 0;x<radio.length;x++) {
			radio[x].checked = false;
		}
	}
	//Función que comprueba que se ha rellenado al menos un campo excepto en el de prácticas
	function validar(form){
		var rellenadas    = 0;
		var submit        = true;
		<?php
		$preguntas        = $DB->get_records_sql($sql_get_preguntas,array('encuesta'=>$encuesta,'fase'=>2));
		foreach($preguntas as $pregunta):
			$cursos = $DB->get_records_sql($sql_get_curso,array('course'=>$ciclo));
			if($pregunta->tipo=="pregunta_modulo"):
				foreach($cursos as $rowcurso):?>
				curso = document.getElementsByName('<?="#".$pregunta->encuesta."-".$pregunta->fase."-".$ciclo."-".$rowcurso->id."-".$pregunta->id?>');
				for(var i = 0; i < curso.length && submit;i++) if(curso[i].checked) rellenadas++;
				<?php endforeach; ?>
				if(rellenadas==0 && submit){
					alert('Rellena al menos una pregunta por bloque por favor.');
					submit = false;
				}
				rellenadas = 0;
				<?php endif;?>
			<?php endforeach ?>
			return submit;
		}
	</script>

	<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
			<center><h1 style="font-weight: bold;" >Ciclo: <?php echo $nombreciclo; ?></h1></center>
			<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
			<div style='padding:30px;'>
				<h3>PLANIFICACI&Oacute;N Y ORGANIZACI&Oacute;N DE LOS M&Oacute;DULOS</h3>
				<center><h4 style="color:red">VALORA AL MENOS UNO DE LOS M&Oacute;DULOS EN LOS QUE EST&Aacute;S MATRICULADO</h4></center>
			</div>
			<div class="well" style='text-align:left;padding:10px;background:#FEFEFE'>
				<form action="encuesta_modulo_fin.php" method="post" name="form_encuesta" onSubmit="return validar(this)">
					<?php // Creamos el formulario leyendolo de la BD.

					$sql           = "SELECT id,encuesta,fase,orden,texto,tipo,ciclo,modulo,pregunta
					FROM encuesta
					WHERE encuesta ='$encuesta' and fase='2'
					ORDER BY orden";
					//$sqlinsert= mysql_query($sql, $conexion);
					$sqlinsert     = $DB->get_records_sql($sql);

					//while($rowsql = mysql_fetch_assoc($sqlinsert)){
					foreach($sqlinsert as $rowsql){
						//if($rowsql['tipo']=="titulo"){
						if($rowsql->tipo=="titulo"){
							//echo '<h2>'.$rowsql['texto'].'</h2><br>';
							echo '<h2>'.$rowsql->texto.'</h2><br>';
						}

						//if($rowsql['tipo']=="pregunta_modulo"){
						if($rowsql->tipo=="pregunta_modulo"){
							//echo "<p><b>".$rowsql['texto']."</b></p><br>";
							echo "<p><b>".$rowsql->texto."</b></p><br>";
							?>
							<div class="table-responsive">
								<table class="table table-hover table-bordered table-condensed">
									<tr style="background:#f5f5f5">
										<th></th>
										<?php
										for($i    = 0;$i<=10;$i++)
										echo "<th style='text-align:center'><b>$i</b></th>"."\n";
										?>
										<th style="text-align:center"><b>--</b></th>
									</tr>
									<?php
									$sql       = "SELECT id,fullname, shortname
									FROM mdl_course
									WHERE category='$ciclo' and shortname  NOT LIKE '%t'";
									//$sqlcurso= mysql_query($sql, $conexion);
									$sqlcurso  = $DB->get_records_sql($sql);
									//while($rowcurso = mysql_fetch_assoc($sqlcurso)){
									foreach($sqlcurso as $rowcurso){
										//echo "<tr><td>".substr(strrchr($rowcurso['fullname'], "_"), 1)."<br></td>";
										echo "<tr><td>".$rowcurso->fullname."<br></td>";
										for ($i   = 0; $i < 11; $i++) {
											//echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$rowcurso['id']."-".$rowsql['id']."' id='".$rowsql['id']."' value='".$i."' > </td>";
											echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$rowcurso->id."-".$rowsql->id."' id='".$rowsql->id."' value='".$i."' > </td>";
										}
										/*<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$rowcurso['id']."-".$rowsql['id']?>')">Deseleccionar</a></td>*/
										?>
										<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$rowcurso->id."-".$rowsql->id?>')">Deseleccionar</a></td>
									</tr>
									<?php
								}
								echo "</table></div></br>";
							}


							//if($rowsql['tipo']=="pregunta"){
							if($rowsql->tipo=="pregunta"){
								//if($rowsql['id']==29){
								if($rowsql->id==29){
									//echo "<div><b>".$rowsql['texto']."</b></div><br>";
									echo "<div><b>".$rowsql->texto."</b></div><br>";
								}else{
									//echo "<div><b>".$rowsql['texto']."</b></div><br>";
									echo "<div><b>".$rowsql->texto."</b></div><br>";
								};
								?>
								<div class="table-responsive">
									<table class="table table-hover table-bordered table-condensed" >
										<tr style="background:#f5f5f5">
											<th></th>
											<?php
											for($i   = 0;$i<=10;$i++)
											echo "<td style='text-align:center'><b>$i</b></td>";
											echo "<td style='text-align:center'><b>--</b></td>";
											echo "</tr>";
										}

										//if($rowsql['tipo']=="respuesta"){
										if($rowsql->tipo=="respuesta"){
											//echo"<tr><td style='text-align:center'>".$rowsql['texto']."<br></td>";
											echo"<tr><td>".$rowsql->texto."<br></td>";
											for ($i  = 0; $i < 11; $i++) {
												//echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-0-".$rowsql['id']."' id='".$rowsql['id']."' value='".$i."' >  </td>";
												echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-0-".$rowsql->id."' id='".$rowsql->id."' value='".$i."' >  </td>";
											}
											/*<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-0-".$rowsql['id']?>')">Deseleccionar</a></td>
											*/
											?>
											<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-0-".$rowsql->id?>')">Deseleccionar</a></td>
											<?php
											echo "</tr>";
										}

										//if($rowsql['tipo']=="pregunta_fin"){
										if($rowsql->tipo=="pregunta_fin"){
											echo "</table></br>";
										}

										//if($rowsql['tipo']=="pregunta_texto"){
										if($rowsql->tipo=="pregunta_texto"){
											//echo "<p><b>".$rowsql['texto']."</b></p><br>";
											echo "<p><b>".$rowsql->texto."</b></p><br>";
											//echo "<textarea style='width:90%;margin-left:5%' placeholder='Escribe tus comentarios ... ' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-0-".$rowsql['id']."' rows='8' cols='95' id='".$rowsql['id']."'></textarea>";
											echo "<textarea style='width:90%;margin-left:5%' placeholder='Escribe tus comentarios ... ' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-0-".$rowsql->id."' rows='8' cols='95' id='".$rowsql->id."'></textarea>";
											echo "</br></br>";
										}

									}
									echo"<br><br><center><input type='submit' value='Finalizar' class='boton' accesskey='s'></center><br><br>";
								else:?>
								<meta HTTP-EQUIV="Refresh" CONTENT="3;URL=https://www.adistanciafparagon.es">
								<center><h1 style="font-size: 16pt;margin-top:5px;font-weight: bold;">NO EXISTE M&Oacute;DULO en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>
							<?php endif;?>
							<input type="hidden" name="id_encuesta" id="id_encuesta" value="<?php echo $_POST['id_encuesta']; ?>">
						</form>
					</div>

					<?php
					echo $OUTPUT->footer();
					?>
