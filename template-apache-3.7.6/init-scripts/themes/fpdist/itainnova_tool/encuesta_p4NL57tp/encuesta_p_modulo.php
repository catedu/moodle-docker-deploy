<?php
error_reporting(E_ALL);
global $DB;
require_once("../../config.php");
$PAGE->set_pagelayout('base');
$title                 = "Encuesta ".(date('Y')-1)." - ".date('Y')."Ciclo Modulo";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();


	require_once('../../config.php');

		//require("../libreria/BD_debug.php");
		//$conexion = get_db_conn();
		if(($_POST)){
			$id_encuesta=filter_var($_POST['id_encuesta'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			foreach($_POST as $nombre_campo => $valor){
				//Saneamos todos los valores recibidos por el post

				if (substr($nombre_campo,0,1)=='#'){

					$arraycampo=explode('-',$nombre_campo,3);
					$encuesta=filter_var(substr($arraycampo[0],1,FILTER_SANITIZE_FULL_SPECIAL_CHARS));
					$codigo1=filter_var($arraycampo[1],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					$codigo2=filter_var($arraycampo[2],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					$valor=filter_var($valor,FILTER_SANITIZE_NUMBER_INT);

					//Insertamos únicamente si no hay datos introducidos ya según la id_encuesta, encuesta, fase... etc
					$sql="INSERT INTO encuesta_datos (`id`,`id_encuesta`, `encuesta`,`fase`, `codigo 1`, `codigo 2`, `codigo 3`, `respuesta 1`, `respuesta 2`)
					SELECT NULL,'$id_encuesta', '$encuesta','1','$codigo1', '','$codigo2',$valor,''
					WHERE NOT EXISTS (SELECT 1 FROM encuesta_datos WHERE id_encuesta = '$id_encuesta'
						AND encuesta = '$encuesta' AND fase = 1 AND `codigo 1` = '$codigo1' AND `codigo 2` = '' AND `codigo 3` = '$codigo2' ) LIMIT 1";
						//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
						$DB->execute($sql);
					}else{
						if(($nombre_campo=='id_genero') ||($nombre_campo=='id_centro')||($nombre_campo=='id_ciclo')||($nombre_campo=='id_participado')) {
							$arraycampo=explode('-',$valor,4);
							$encuesta=filter_var($arraycampo[0],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
							$codigo1=filter_var($arraycampo[1],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
							$codigo2=filter_var($arraycampo[2],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
							$valorcampo=$arraycampo[3];
							$valorcampo=filter_var($valorcampo,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
							if($nombre_campo=='id_ciclo'){
								$ciclo=$valorcampo;
							}
							$sql="INSERT INTO encuesta_datos (`id`,`id_encuesta`, `encuesta`,`fase`, `codigo 1`, `codigo 2`, `codigo 3`, `respuesta 1`, `respuesta 2`)
							SELECT NULL,'$id_encuesta', '$encuesta','0','$codigo1', '', '$codigo2', -1, '$valorcampo'
							WHERE NOT EXISTS (SELECT 1 FROM encuesta_datos WHERE id_encuesta = '$id_encuesta'
								AND encuesta = '$encuesta' AND fase = 0 AND `codigo 1` = '$codigo1' AND `codigo 2` = '' AND `codigo 3` = '$codigo2') LIMIT 1";
								//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
								$DB->execute($sql);
							}
						}
					}

					$sql="SELECT name
					FROM mdl_course_categories
					WHERE id ='$ciclo'";
					//$sqlinsert= mysql_query($sql, $conexion);
					$sqlinsert = $DB->get_records_sql($sql);

					//if(!$sqlinsert) {
					//	die('Could not get data: ' . mysql_error());
					//}

					//while($row = mysql_fetch_array($sqlinsert)) {
					foreach($sqlinsert as $row){
						//$nombreciclo=$row['name'];
						$nombreciclo      = $row->name;
					}
					?>

					<script type='text/javascript'>
					function clearChecks(radioName) {
						var radio = document.getElementsByName(radioName);
						for(var x=0;x<radio.length;x++) {
							radio[x].checked = false;
						}
					}

					function validar(form){
						var rellenadas = 0;
						var submit = true;
						<?php
						//Función que comprueba que se ha rellenado al menos un campo excepto en el de prácticas
						$sql="SELECT id,encuesta,fase,orden,texto,tipo,ciclo,modulo,pregunta
						FROM encuesta
						WHERE encuesta ='$encuesta' and fase='2'
						ORDER BY orden ASC";
						//$sqlinsert= mysql_query($sql, $conexion);
						$sqlinsert        = $DB->get_records_sql($sql);

						//while($rowsql = mysql_fetch_assoc($sqlinsert)){
						foreach($sqlinsert as $rowsql){
							//if($rowsql['tipo']=="pregunta_modulo"){
							if($rowsql->tipo=="pregunta_modulo"){

								for($modulo = 1; $modulo <= 3;$modulo++){
									/*curso = document.getElementsByName('#<?=$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$modulo."-".$rowsql['id']?>');*/
									?>
									curso = document.getElementsByName('#<?=$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$modulo."-".$rowsql->id?>');
									for(var i=0; i < curso.length && submit;i++) if(curso[i].checked) rellenadas++;
									<?php } //end for modulo?>
									if(rellenadas==0 && submit){
										alert('Rellena al menos una pregunta por bloque por favor.');
										submit = false;
									}
									rellenadas=0;
									<?php
								} //end if
							} //end while ?>
							return submit;
						}
						</script>

						<HR SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;" ><br>
							<center><h1 style="font-weight: bold;" >Ciclo: <?php echo $nombreciclo; ?></h1></center>
							<HR SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;" ><br>

								<div style='padding:30px;'>
									<h3>PLANIFICACI&Oacute;N Y ORGANIZACI&Oacute;N DE LOS M&Oacute;DULOS</h3>
									<center><h4 style="color:red">VALORA AL MENOS UNO DE LOS M&Oacute;DULOS QUE TUTORIZAS</h4></center>
								</div>

									<div class="well" style='text-align:left;padding:10px;background:#FEFEFE'>
										<form action="encuesta_p_modulo_fin.php" method="post" name="form_encuesta" onsubmit="return validar(this);">
											<?php // Creamos el formulario leyendolo de la BD.

											$sql="SELECT id,encuesta,fase,orden,texto,tipo,ciclo,modulo,pregunta
											FROM encuesta
											WHERE encuesta ='$encuesta' and fase='2'
											ORDER BY orden ASC";
											//$sqlinsert= mysql_query($sql, $conexion);
											$sqlinsert = $DB->get_records_sql($sql);

											//while($rowsql = mysql_fetch_assoc($sqlinsert)){
											foreach ($sqlinsert as $rowsql) {
												//if($rowsql['tipo']=="titulo"){
												if($rowsql->tipo=="titulo"){
													//echo "<div style=' color:#000000; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
													echo '<h2>'.$rowsql->texto.'</h2><br>';
												}
												//Si es inicio de la pregunta
												//if($rowsql['tipo']=="pregunta_modulo"){
												if($rowsql->tipo=="pregunta_modulo"){
													//echo "<div style=' color:#000000; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
													//echo "<table border='0'>";
													//echo "<tr><th style='color:#ffffff;background-color:#6491b9; width:500px;'>  </th>";
													echo "<p><b>".$rowsql->texto."</b></p><br>";
													?>
													<div class="table-responsive">
														<table class="table table-hover table-bordered table-condensed">
														<tr style="background:#f5f5f5">
														<th></th>
													<?php
													for($i=0;$i<=10;$i++)
													echo "<th style='text-align:center'><b>$i</b></th>";
													echo "<th style='text-align:center'><b>--</b></th> </tr>";
													//Imprimimos los tres módulos
													for($modulo = 1; $modulo<=3;$modulo++){
														echo "<tr><td style='text-align:center'> M&oacute;dulo ".$modulo."<br></td>"; //
														for ($i = 0; $i < 11; $i++) {
															//echo "<td style='color:#000000;border:1px solid #999'> <input  type='radio' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$modulo."-".$rowsql['id']."' id='".$rowsql['id']."' value='".$i."' > </td>";
															echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$modulo."-".$rowsql->id."' id='".$rowsql->id."' value='".$i."' > </td>";
														}
														/*<td style='color:#000000;border:1px solid #999'> <a href="javascript:clearChecks('<?="#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$modulo."-".$rowsql['id']?>')">Deseleccionar</a></td>*/
														?>
														<td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$modulo."-".$rowsql->id?>')">Deseleccionar</a></td>
													</tr>
													<?php
												}
												echo "</table></div></br>";
											}
											//if($rowsql['tipo']=="pregunta"){
											if($rowsql->tipo=="pregunta"){
													//Si el texto es 'SÓLO EN MÓDULOS CON PRÁCTICAS' lo imprime en rojo
													//if($rowsql['id']==29){
													if($rowsql->id==29){
															//echo "<div style='color:#FF0000;margin-left:40px; font-size: 12pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
													echo '<h2 style="color:red">'.$rowsql->texto.'</h2>';
												}else{
													//echo "<div style='color:#000000;margin-left:40px; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
													echo '<h2>'.$rowsql->texto.'</h2>';
												};
												?>
												<div class="table-responsive">
													<table class="table table-hover table-bordered table-condensed">
													<tr style="background:#f5f5f5">
													<th></th>
												<?php
												for($i=0;$i<=10;$i++)
												echo "<th style='text-align:center'><b>$i</b></th>";
												echo "</tr>";
											}
											//Checkboxes
											//if($rowsql['tipo']=="respuesta"){
											if($rowsql->tipo=="respuesta"){
												//echo"<tr><td style='color:#000000; width:500px;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;padding-left:10px;border:1px solid #999'> <br>".$rowsql['texto']."</td>";
												echo"<tr><td style='text-align:center'> <br>".$rowsql->texto."</td>";
												for ($i = 0; $i < 11; $i++)
												echo "<td style='text-align:center'> <input  type='radio' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-0-".$rowsql->id."' id='".$rowsql->id."' value='".$i."' >  </td>";
												//echo "<td style='color:#000000;border:1px solid #999'> <input  type='radio' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-0-".$rowsql['id']."' id='".$rowsql['id']."' value='".$i."' >  </td>";
												/*<td style='color:#000000;border:1px solid #999'> <a href="javascript:clearChecks('<?="#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-".$rowcurso['id']."-".$rowsql['id']?>')">Deseleccionar</a></td>*/
												?>
												<td <td style="text-align:center"> <a href="javascript:clearChecks('<?="#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-".$rowcurso->id."-".$rowsql->id?>')">Deseleccionar</a></td>
											</tr>
											<?php
										}
										//Imprimimos el final de la tabla
										//if($rowsql['tipo']=="pregunta_fin"){
										if($rowsql->tipo=="pregunta_fin"){
												echo "</table></div></br>";
										}
										//Imprimimos los textarea para los comentarios
										//if($rowsql['tipo']=="pregunta_texto"){
										if($rowsql->tipo=="pregunta_texto"){
											//echo "<div style='color:#000000; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
											echo "<p><b>".$rowsql->texto."</b></p><br>";
											//echo "<textarea placeholder='Escribe tus comentarios ... ' name='#".$rowsql['encuesta']."-".$rowsql['fase']."-".$ciclo."-0-".$rowsql['id']."' rows='8' cols='95' id='".$rowsql['id']."'></textarea>";
											echo "<textarea style='width:90%;margin-left:5%' placeholder='Escribe tus comentarios ... ' name='#".$rowsql->encuesta."-".$rowsql->fase."-".$ciclo."-0-".$rowsql->id."' rows='8' cols='95' id='".$rowsql->id."'></textarea>";
											echo "</br></br>";
										}
									} //end while($rowsql = mysql_fetch_assoc($sqlinsert))

								?>
								<br><br><center><input type='submit' value='Finalizar' class='boton' accesskey='s'></center><br><br>
								<input type="hidden" name="id_encuesta" id="id_encuesta" value="<?php echo $_POST['id_encuesta']; ?>">
							</form>
						</div>
					<?php
				}else{
					//else if(($_POST))
					echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"3;URL=https://www.adistanciafparagon.es\">";
					echo "<center><h1 style=\"font-size: 16pt;margin-top:5px;font-weight: bold;\">NO EXISTE M&Oacute;DULO en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>";
				}
			echo $OUTPUT->footer();
				?>
