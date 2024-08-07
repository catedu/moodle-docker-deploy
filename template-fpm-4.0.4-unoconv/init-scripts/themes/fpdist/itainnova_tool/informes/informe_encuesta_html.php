<?php
//echo "informe_encuesta_chart.php<br/>";
$showinsert=0;

ini_set('display_errors',1);
error_reporting(E_ALL);

ini_set('max_execution_time', 2147483647);
ini_set("log_errors", 1);
require_once('../libreria/checkAdmin.php');
require('../libreria/PhpSpreadsheet/vendor/autoload.php');

set_time_limit(0);
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$PAGE->set_pagelayout('admin');
$title = 'ITAINNOVA Tools';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
echo $OUTPUT->header();
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);


if (empty($_POST)){
	
	$encuestas = $DB->get_records_sql("SELECT DISTINCT(encuesta) as encuesta FROM encuesta order by encuesta DESC");
?>
	<div style="width:90%;">
		<h2>Elige una Encuesta: (Pablo probando) </h2>
		<p>Las finalizadas en a son de Alumnos</p>
		<p>Las finalizadas en p Profesores</p>
		<form action="informe_encuesta_html.php" style="width:50%;margin-left:5%" method="post">
			<select  id='id_encuesta' style="width:100%" name='id_encuesta' required >
				<option value="">Por favor selecciona la encuesta ...</option>
<?php
	foreach ($encuestas as $encuesta) {
		echo "<option value='".$encuesta->encuesta."'>".$encuesta->encuesta."</option>";
	}
?>
			</select>
			<br><br><br>
			<center>
				<button type="submit" class="btn btn-info btn-lg active">Siguiente</button>
			</center>
		</form>
	</div>
<?php

}else{
	$idEncuesta = $_POST['id_encuesta'];
	//
	$estiloH4 = "style=\"color:white;background-color:#181023;font-weight: bold;font-size: 24px;border-top-style: solid;border-top-color: black;border-top-width: 1px;padding-top:25px;padding-left:10px;\"";
	$estiloH5 = "style=\"color:white;background-color:#261a38;font-weight: bold;font-size: 20px; padding-top:20px;padding-left:20px;\"";
	$estiloCentro = "style=\"color:white;background-color:#312147;font-weight: bold;font-size: 18px;\"";
	$estiloCiclo = "style=\"color:white;background-color:#5f5370;font-weight: bold;font-size: 16px;\"";
	$estiloModulo = "style=\"color:white;background-color:#b2a2cd;font-weight: bold;font-size: 14px;\"";
	
	//

	echo "<h2>Informe de la encuesta ".$idEncuesta."</h2>";
	/************************************
	Poner un índice
	************************************/
	echo "<h3>Índice</h3>\n";
	echo "<p><a href=\"#cantidadEstudiantesMatriculados\">Cantidad de estudiantes matriculados</a></p>\n";
	echo "<p><a href=\"#cantidadDeEncuestasRespondidas\">Cantidad de encuestas respondidas</a></p>\n";
	echo "<p><a href=\"#cantidadEncuestasGenero\">Cantidad de encuestas por género</a></p>\n";
	echo "<p><a href=\"#cantidadEncuestasContestadasPorCiclo\">Cantidad de encuestas contestadas por ciclo</a></p>\n";
	echo "<p><a href=\"#respuestasAPregDeEncuestas\">Respuestas a las preguntas de la encuesta</a></p>\n";
	/************************************
	Cantidad de estudiantes matriculados
	************************************/
	echo "<h3 id=\"cantidadEstudiantesMatriculados\">Cantidad de estudiantes matriculados</h3>\n";

	$SQL = 
		"SELECT c.id, (select cc2.name from mdl_course_categories cc2 where cc2.id = cc.parent) centro, cc.name ciclo,  c.fullname modulo, COUNT(ue.id) AS total
		FROM mdl_course AS c 
		    JOIN mdl_enrol AS en ON en.courseid = c.id
		    JOIN mdl_user_enrolments AS ue ON ue.enrolid = en.id
		    JOIN mdl_course_categories AS cc ON cc.id = c.category
		WHERE ue.status = 0 -- usuarios activos
		GROUP BY c.id
		ORDER BY centro, ciclo, modulo";

	$data = $DB->get_records_sql($SQL);

	if(count($data) > 0){
		//echo "count($data): " . (count($data)) . "<br/>\n";
		echo "<table class='generaltable' >\n";
		echo "<thead>\n";
		echo "  <tr>\n";
		echo "    <th>#</th>\n";
		echo "    <th>Centro</th>\n";
		echo "    <th>Ciclo</th>\n";
		echo "    <th>Módulo</th>\n";
		echo "    <th style=\"text-align:right;\">Nº Matrículas</th>\n";
		echo "  </tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
		$i = 0;
		$centroAnterior = "";
		$cicloAnterior = "";
		foreach ($data as $row) {
			if( $centroAnterior != $row->centro ){
				$centroAnterior = $row->centro;
				echo "  <tr $estiloCentro>\n";
				echo "    <td></td>\n";
				echo "    <td colspan='4' >".$row->centro."</td>\n";
				echo "  </tr>\n";
			}
			if( $cicloAnterior != $row->ciclo ){
				$cicloAnterior = $row->ciclo;
				echo "  <tr $estiloCiclo>\n";
				echo "    <td></td>\n";
				echo "    <td></td>\n";
				echo "    <td colspan='3' >".$row->ciclo."</td>\n";
				echo "  </tr>\n";
			}
			echo "  <tr>\n";
			echo "    <td>".$i++."</td>\n";
			echo "    <td></td>\n";
			echo "    <td></td>\n";
			echo "    <td>".$row->modulo."</td>\n";
			echo "    <td align=\"right\">".$row->total."</td>\n";
			echo "  </tr>\n";
		}
		echo "</tbody>\n";
		echo "</table>\n";
	}else{
		echo "<p>No hay datos para esta consulta: " . $SQL . "</p>\n";
	}
	echo "<hr/>\n";
	/************************************
	Cantidad de encuestas respondidas
	************************************/
	echo "<h3 id=\"cantidadDeEncuestasRespondidas\" >Cantidad de encuestas respondidas</h3>\n";
	$SQL = 
		"SELECT count(distinct(id_encuesta)) as total
		FROM encuesta_datos
		Where encuesta = '".$idEncuesta."'
		order by encuesta DESC";
		
	$data = $DB->get_records_sql($SQL);
	if(count($data) > 0){
		foreach ($data as $row) {
			echo "<p>Total de encuestas: " . $row->total . "</p>\n";
		}
	}else{
		echo "<p>No hay datos para esta consulta: " . $SQL . "</p>\n";
	}
	echo "<hr/>\n";
	
	/************************************
	Cantidad de encuestas respondidas por género
	************************************/

	echo "<h3 id=\"cantidadEncuestasGenero\">Cantidad de encuestas respondidas por género</h3>\n";
	$SQL = 
		"SELECT `respuesta 2` genero, count(*) total
		FROM encuesta_datos
		Where encuesta = '" . $idEncuesta . "' and `codigo 2` = 'id_genero'
		group by `respuesta 2`
		order by fase";
		
	$data = $DB->get_records_sql($SQL);
	if(count($data) > 0){
		//echo "count($data): " . (count($data)) . "<br/>\n";
		echo "<table class='generaltable' >\n";
		echo "  <thead>\n";
		echo "  <tr>\n";
		echo "    <th>Género</th>\n";
		echo "    <th style=\"text-align:right;\">Cantidad</th>\n";
		echo "  </tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		$subtotal = 0;
		foreach ($data as $row) {
			echo "  <tr>\n";
			echo "    <td>\n";
			echo $row->genero;
			echo "    </td>\n";
			echo "    <td align=\"right\">\n";
			echo $row->total;
			echo "    </td>\n";
			echo "  </tr>\n";
			$subtotal += $row->total;
		}
		echo "  <tbody>\n";
		echo "  <tfoot>\n";
		echo "  <tr>\n";
		echo "    <td>Total</td>\n";
		echo "    <td align=\"right\">$subtotal</td>\n";
		echo "  </tr>\n";
		echo "  <tfoot>\n";
		echo "</table>\n";
	}else{
		echo "<p>No hay datos para esta consulta: " . $SQL . "</p>\n";
	}
	echo "<hr/>\n";

	/************************************
	Cantidad de encuestas por ciclo
	************************************/

	echo "<h3 id=\"cantidadEncuestasContestadasPorCiclo\" >Cantidad de encuestas contestadas por ciclo</h3>\n";

	$SQL = 
		"SELECT `respuesta 2` id, cc.name ciclo, cc2.name centro, count(*) total
		FROM encuesta_datos ed
		JOIN mdl_course_categories AS cc ON cc.id = ed.`respuesta 2`
		JOIN mdl_course_categories AS cc2 ON cc2.id = cc.parent
		Where encuesta = '".$idEncuesta."' and `codigo 2` = 'id_ciclo'
		group by `respuesta 2`
		order by centro, ciclo DESC";

	$data = $DB->get_records_sql($SQL);

	if(count($data) > 0){
		//echo "count($data): " . (count($data)) . "<br/>\n";
		echo "<table class='generaltable' >\n";
		echo "  <thead>\n";
		echo "  <tr>\n";
		echo "    <th>#</th>\n";
		echo "    <th>Centro</th>\n";
		echo "    <th>Ciclo</th>\n";
		echo "    <th style=\"text-align:right;\">Nº Encuestas</th>\n";
		echo "  </tr>\n";
		echo "  </thead>\n";
		echo "  <tbody>\n";
		$i = 1;
		$subtotal = 0;
		$centroAnterior = "";
		foreach ($data as $row) {
			if( $centroAnterior != $row->centro ){
				$centroAnterior = $row->centro;
				echo "  <tr $estiloCentro>\n";
				echo "    <td></td>\n";
				echo "    <td colspan='4' >".$row->centro."</td>\n";
				echo "  </tr>\n";
			}
			echo "  <tr>\n";
			echo "    <td>". $i++."</td>\n";
			echo "    <td></td>\n";
			echo "    <td>".$row->ciclo."</td>\n";
			echo "    <td align=\"right\">".$row->total."</td>\n";
			echo "  </tr>\n";
			$subtotal += $row->total;
		}
		echo "  </tbody>\n";
		echo "  <tfoot>\n";
		echo "  <tr>\n";
		echo "    <td  colspan='3' align=\"right\">Total:</td>\n";
		echo "    <td align=\"right\">$subtotal</td>\n";
		echo "  </tr>\n";
		echo "  </tfoot>\n";
		echo "</table>\n";
	}else{
		echo "<p>No hay datos para esta consulta: " . $SQL . "</p>\n";
	}
	echo "<hr/>\n";

	/************************************
	Respuestas a las preguntas de la encuesta
	************************************/

	echo "<h3 id=\"respuestasAPregDeEncuestas\">Respuestas a las preguntas de la encuesta</h3>\n";

	$SQL = 
		"SELECT id, fase, orden, texto, tipo
		FROM encuesta e
		Where e.encuesta = '" . $idEncuesta . "' and fase > 0
		order by fase, orden";
	$SQL2 = "";

	$data = $DB->get_records_sql($SQL);

	if(count($data) > 0){
		foreach ($data as $row) {
			switch ($row->tipo) {
				case "titulo":
					echo "<h4 $estiloH4 >".$row->texto."</h4>\n";
					break;

				case "respuesta":
					echo "<h5 $estiloH5 >- ".$row->texto."</h5>\n";
					$SQL2 = 
						"SELECT ed.`respuesta 1` respuesta, count(*) total
						FROM encuesta_datos ed
						WHERE ed.encuesta = '" . $idEncuesta . "' and ed.`codigo 3` = '" . $row->id . "'
						GROUP BY ed.`respuesta 1`
						ORDER BY ed.`respuesta 1`";
					$data2 = $DB->get_records_sql($SQL2);
					if(count($data2) > 0){
						$suma = 0;
						$votos = 0;
						$ultimaColPuesta = 0;
						echo "<table class='generaltable' >\n";
						echo "  <tr>\n";
						for ($i = 0; $i <= 10; $i++) {
							echo "    <th>".$i."</th>\n";
						}
						echo "  </tr>\n";
						echo "  <tr>\n";
						foreach ($data2 as $row) {
							$respuesta = $row->respuesta;
							while($respuesta > $ultimaColPuesta ){
								echo "    <td>0</td>\n";
								$ultimaColPuesta++;
							}
							echo "    <td>".$row->total."</td>\n";
							$ultimaColPuesta = $respuesta + 1;
							
							$suma += ($row->total * $row->respuesta);
							$votos += $row-> total;
						}
						while($ultimaColPuesta <= 10){
							echo "    <td>0</td>\n";
							$ultimaColPuesta++;
						}
						echo "  </tr>\n";
						echo "  <tr>\n";
						$media = $suma / $votos;
						echo "    <td colspan=\"11\" align=\"right\" >Media: ".round($media,2)."</td>\n";
						echo "  </tr>\n";
						echo "</table>\n";
						
					}else{
						echo "<p>No hay datos para esta consulta: " . $SQL2 . "</p>\n";
					}
					break;
				case "pregunta_fin":
					break;

				case "pregunta":
					echo "<h4 $estiloH4 >".$row->texto."</h4>\n";
					break;

				case "pregunta_modulo":
					echo "<h5 $estiloH5 >- ".$row->texto."</h5>\n";
					$SQL2 = 
						"SELECT concat(cc2.id, ' ', cc.id, ' ', c.id, ' ',`respuesta 1`) id , cc2.name centro, cc.name ciclo, c.fullname modulo, `respuesta 1` respuesta , count(*) total
						FROM encuesta_datos  ed
							JOIN mdl_course_categories AS cc ON cc.id = ed.`codigo 1`
							JOIN mdl_course_categories AS cc2 ON cc2.id = cc.parent
							JOIN mdl_course AS c ON c.id = ed.`codigo 2`
						Where ed.encuesta = '" . $idEncuesta . "' and `codigo 3`= " . $row->id . "
						group by centro, ciclo, modulo, `respuesta 1`
						order by centro, ciclo, modulo";
						$data2 = $DB->get_records_sql($SQL2);
						if(count($data2) > 0){
							$suma = 0;
							$votos = 0;
							$sumaModulo = 0;
							$votosModulo = 0;
							$sumaCiclo = 0;
							$votosCiclo = 0;
							$sumaCentro = 0;
							$votosCentro = 0;

							$ultimaColPuesta = 0;

							$primerCentro = true;
							$primerCiclo = true;
							$primerModulo = true;

							$cambioCentro = false;
							$cambioCiclo = false;
							$cambioModulo = false;
							
							$centroAnterior = "";
							$cicloAnterior = "";
							$moduloAnterior = "";

							
							echo "<table class='generaltable' >\n";
							foreach ($data2 as $row) {
								//centro
								if( $centroAnterior != $row->centro ){
									$cambioCentro = true;
								}else{
									$cambioCentro = false;
								}
								//ciclo
								if( $cicloAnterior != $row->ciclo ){
									$cambioCiclo = true;
								}else{
									$cambioCiclo = false;
								}
								//modulo
								if( $moduloAnterior != $row->modulo ){
									$cambioModulo = true;
								}else{
									$cambioModulo = false;
								}
								//
								if( $cambioModulo){
									// Pongo las columnas que faltan
									if( !$primerModulo ){
										while($ultimaColPuesta <= 10){
											echo "    <td>0</td>\n";
											$ultimaColPuesta++;
										}
									}
									$ultimaColPuesta = 0;
									// Pongo la media del módulo
									if( !$primerModulo ){
										echo "  <tr $estiloModulo>\n";
										$mediaModulo = $sumaModulo / $votosModulo;
										echo "    <td></td>\n";
										echo "    <td></td>\n";
										echo "    <td colspan=\"9\" align=\"right\" >Media módulo ".$moduloAnterior.": ".round($mediaModulo,2)."</td>\n";
										echo "  </tr>\n";
									}
									$primerModulo = false;
									// Resteo estadísticas a nivel de módulo
									$sumaModulo = 0;
									$votosModulo = 0;
									//
									$moduloAnterior = $row->modulo;
								}
								if($cambioCiclo){
									// Pongo la media del ciclo
									if( !$primerCiclo ){
										echo "  <tr $estiloCiclo>\n";
										$mediaCiclo = $sumaCiclo / $votosCiclo;
										echo "    <td></td>\n";
										echo "    <td colspan=\"10\" align=\"right\" >Media ciclo ".$cicloAnterior.": ".round($mediaCiclo,2)."</td>\n";
										echo "  </tr>\n";
									}
									$primerCiclo = false;
									// Reseteo estadísticas a nivel de ciclo
									$sumaCiclo = 0;
									$votosCiclo = 0;
									//
									$cicloAnterior = $row->ciclo;
								}

								if($cambioCentro){
									// Pongo la media del centro
									if( !$primerCentro ){
										echo "  <tr $estiloCentro>\n";
										$mediaCentro = $sumaCentro / $votosCentro;
										echo "    <td colspan=\"11\" align=\"right\" >Media centro ".$centroAnterior.": ".round($mediaCentro,2)."</td>\n";
										echo "  </tr>\n";
									}
									$primerCentro = false;
									// Reseteo estadísticas a nivel de centro
									$sumaCentro = 0;
									$votosCentro = 0;
									//
									$centroAnterior = $row->centro;
									// Escribo el nombre del Centro
									echo "  <tr $estiloCentro>\n";
									echo "    <th colspan=\"11\" >".$row->centro."</th>\n";
									echo "  </tr>\n";

								}
								if($cambioCiclo){
									// Escribo el nombre del ciclo
									echo "  <tr $estiloCiclo>\n";
									echo "    <th></th>\n";
									echo "    <th colspan=\"10\" >".$row->ciclo."</th>\n";
									echo "  </tr>\n";
								}
								if( $cambioModulo){
									// Escribo nombre del módulo
									echo "  <tr $estiloModulo>\n";
									echo "    <th></th>\n";
									echo "    <th></th>\n";
									echo "    <th colspan=\"9\" >".$row->modulo."</th>\n";
									echo "  </tr>\n";
									// Pongo columnas de 0 a 10
									echo "  <tr>\n";
									for ($i = 0; $i <= 10; $i++) {
										echo "    <th>".$i."</th>\n";
									}
									echo "  </tr>\n";
									// Abro la fila para la respuesta
									echo "  <tr>\n";
								}
								//
								$respuesta = $row->respuesta;
								while($respuesta > $ultimaColPuesta ){
									echo "    <td>0</td>\n";
									$ultimaColPuesta++;
								}
								echo "    <td>".$row->total."</td>\n";
								$ultimaColPuesta = $respuesta + 1;
								// Actualizo variables
								$suma += ($row->total * $row->respuesta);
								$votos += $row-> total;
								$sumaModulo += ($row->total * $row->respuesta);
								$votosModulo += $row-> total;
								$sumaCiclo += ($row->total * $row->respuesta);
								$votosCiclo += $row-> total;
								$sumaCentro += ($row->total * $row->respuesta);
								$votosCentro += $row-> total;
							}
							echo "</table>\n";
						}else{
							echo "<p>No hay datos para esta consulta: " . $SQL2 . "</p>\n";
						}
					break;

				case "pregunta_texto":
					echo "<h5 $estiloH5 >- ".$row->texto."</h5>\n";
					$SQL2 = 
						"SELECT ed.id, cc.name ciclo, cc2.name centro, `respuesta 2` respuesta
						FROM encuesta_datos  ed
							JOIN mdl_course_categories AS cc ON cc.id = ed.`codigo 1`
							JOIN mdl_course_categories AS cc2 ON cc2.id = cc.parent
						Where ed.encuesta = '".$idEncuesta."' and `codigo 3` = ".$row->id." and `respuesta 2` <> ''
						order by centro, ciclo";
					$data2 = $DB->get_records_sql($SQL2);
					if(count($data2) > 0){
						//echo "count($data): " . (count($data)) . "<br/>\n";
						echo "<table class='generaltable' >\n";
						echo "  <tr>\n";
						echo "    <th>Centro</th>\n";
						echo "    <th>Ciclo</th>\n";
						echo "  </tr>\n";
						$i = 0;
						$centroAnterior = "";
						$cicloAnterior = "";
						foreach ($data2 as $row) {
							if( $centroAnterior != $row->centro ){
								$centroAnterior = $row->centro;
								echo "  <tr $estiloCentro >\n";
								echo "    <td colspan='2' >".$row->centro."</td>\n";
								echo "  </tr>\n";
							}
							if( $cicloAnterior != $row->ciclo ){
								$cicloAnterior = $row->ciclo;
								echo "  <tr $estiloCiclo >\n";
								echo "    <td></td>\n";
								echo "    <td>".$row->ciclo."</td>\n";
								echo "  </tr>\n";
							}
							echo "  <tr>\n";
							echo "    <td colspan=\"2\" >\n";
							echo "- ". $row->respuesta;
							echo "    </td>\n";
							echo "  </tr>\n";
						}
						echo "</table>\n";
					}else{
						echo "<p>No hay datos para esta consulta: " . $SQL2 . "</p>\n";
					}

					break;
				default:
					echo "<p>Tipo de pregunta no soportado: " . $row->tipo . "</p>\n";
					break;
			}
			echo "<br/><br/>\n";
		}
	}else{
		echo "<p>No hay datos para esta consulta: " . $SQL . "</p>\n";
	}
	echo "<hr/>\n";



	/*
	//Obtenemos los datos
	$data = $DB->get_records_sql("SELECT * FROM encuesta_datos WHERE encuesta = '".$idEncuesta."' ORDER BY encuesta");
	//Si existen datos
	if(count($data)){
	  //Obtenemos los modulos
	  $sql_get_curso = "SELECT cursos.id as curso_id, cursos.name as curso
	  FROM {course_categories} cursos
	  WHERE cursos.coursecount > 0
	  ORDER BY cursos.id ASC;";
	  $cursos = $DB->get_records_sql_menu($sql_get_curso);

	  //Creamos la estructura de almacenamiento
	  $agregado_obj = array();
	  foreach ($data as $row) {
		//Obtenemos el identificador
		$key = $row->id_encuesta;
		//Si no esta creado el objeto en la estructura, lo creamos
		if(!isset($agregado_obj[$key])){
		  $agregado_obj[$key] = new StdClass();
		  $agregado_obj[$key]->_0_id_encuesta = $key;
		}
		//Separamos las preguntas por fases
		switch ($row->fase) {
		  case 0: #Primera parte de la encuesta
			$codigo3 = $row->{'codigo 3'};
			if($codigo3==1){#Genero
				$agregado_obj[$key]->_genero = $row->{'respuesta 2'};
			}elseif($codigo3==2){#Centro
				$agregado_obj[$key]->centro = $row->{'respuesta 2'};
			}elseif($codigo3==3){#ciclo
				$agregado_obj[$key]->ciclo = $row->{'respuesta 2'};
			}
			//Ajustamos el rol
			if(substr($row->encuesta,8,9)=='a'){
				$agregado_obj[$key]->_rol = 'Alumno';
			}else{
				$agregado_obj[$key]->_rol = 'Profesor';
			}
			break;
		  case 1:#Segunda parte de la encuesta
			//Mapeamos los valores
			if(!isset($agregado[$key][$seccion]))
			$pregunta = 'p_'.$row->{'codigo 3'};
			if(strlen($row->{'respuesta 1'})){
				$agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
			}else{
				$agregado_obj[$key]->$pregunta = $row->{'respuesta 2'};
			}
			break;
		  case 2:#Tercera parte de la encuesta
			if(!isset($agregado[$key][$seccion]))
			if($row->{'respuesta 1'}<0){
				//Es tipo texto
				$pregunta = 't_'.$row->{'codigo 3'};
				$agregado_obj[$key]->$pregunta = html_entity_decode($row->{'respuesta 2'});
			}elseif($row->{'codigo 2'}==0){
				//Pregunta normal
				$pregunta = 'p_'.$row->{'codigo 3'};
				$pregunta.=' '.$cursos[intval($row->{'codigo 1'})];
				$pos = strpos($pregunta,'(');
				if($pos>0)
				$pregunta = substr($pregunta,0,strpos($pregunta,'('));
				$agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
			}else{
				//Practicas (opcional)
				$pregunta = 'pr_'.$row->{'codigo 3'};
				$agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
			}
			break;
		}
	  }
	  //Obtenemos los centros
	  $sql_get_centros = "SELECT centros.id as centro_id, centros.name
	  FROM {course_categories} centros
	  INNER JOIN {course_categories} cursos on centros.id = cursos.parent
	  WHERE centros.coursecount = 0
	  ORDER BY centros.name ASC;";
	  $centros =(array) $DB->get_records_sql_menu($sql_get_centros);

	  //Creamos la hoja
	  $spreadsheet = new Spreadsheet();
	  $sheet = $spreadsheet->getActiveSheet();
	  $sheet->setTitle('Alumnos');
	  $row = 2;
	  $profesor = false;
	  //var_export($agregado_obj);
	  foreach ($agregado_obj as $persona) {
		if($persona->_rol=='Profesor' && !$profesor){
		  //quickSort($sheet);
		  //bubbleSort($sheet);
		  //minimunSort($sheet);
		  $profesor = true;
		  $row = 2;
		  $sheet = $spreadsheet->createSheet();
		  $sheet->setTitle('Profesores');
		}
		foreach ($persona as $key => $value) {

		  if($key=="centro")
		  	$value = $centros[$value];
		  if($key=="ciclo")
		  	$value = $cursos[$value];
		  //insertValueToColumnOrAppendSorted($sheet,$value,$key,$row);
		  insertValueToColumnOrAppend($sheet,$value,$key,$row);
		}
		$row++;
	  }
	  //ordenamos la hoja de los Profesores
	  //quickSort($sheet);
	  //bubbleSort($sheet);
	  //minimunSort($sheet);

	  //obtenemos las preguntas
	  $sql_get_preguntas = "SELECT id,texto,tipo,encuesta,fase FROM encuesta WHERE encuesta = '".$_POST['id_encuesta']."' ORDER BY fase,orden ASC";
	  $preguntas = $DB->get_records_sql($sql_get_preguntas);
	  $sheet = $spreadsheet->createSheet();
	  $sheet->setTitle('Preguntas');
	  $fila = 1;
	  foreach ($preguntas as $pregunta) {
		$key ="";
		switch ($pregunta->tipo) {
		  case 'pregunta_texto':
			$key='t_'.$pregunta->id;
			$tipo = strtoupper(substr($pregunta->encuesta,8,9));
			break;
		  case 'respuesta':
			$key='p_'.$pregunta->id;
			$tipo = strtoupper(substr($pregunta->encuesta,8,9));
			break;
		  case 'pregunta_modulo':
			$key='pr_'.$pregunta->id;
			$tipo = strtoupper(substr($pregunta->encuesta,8,9));
			break;
		  case 'titulo':
			$tipo="";
			$fila++;
			break;
		  case 'pregunta':
			if($pregunta->fase==0)
			continue 2;
			$tipo="";
			$fila++;
			break;
		  default:
		  	continue 2;
		}
		$sheet->setCellValue('A'.$fila,$key);
		$sheet->setCellValue('B'.$fila,$tipo);
		$sheet->setCellValue('C'.$fila,$pregunta->texto);
		$fila++;
	  }

	  $nombre_fic = './encuesta/'.$_POST['id_encuesta'].'_'.date("Y-m-d").'.xlsx';
	  if(file_exists($nombre_fic))
	  	unlink($nombre_fic);
	  $writer = new Xlsx($spreadsheet);
	  $writer->save($nombre_fic);

	  echo "<p><strong>------------------</strong><br>";
	  echo '<a href="'.$nombre_fic.'"><strong>DESCARGAR ENCUESTA</strong></a></p>';
	}
	*/
	
} //FIN POST



echo $OUTPUT->footer();

/*
	* Busca iterativamente si existe una columna con el mismo valor $column
	* Si no existe, la inserta ordenador
	*/
	function insertValueToColumnOrAppendSorted($sheet,$value,$column,$row){
	  $iterative_column = 1;
	  $inserted = false;

	  while(!$inserted){
		$current = $sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue();
		$next = $sheet->getCell(getColumnFromNumber($iterative_column+1).'1')->getValue();
		if(empty($current)){
		  $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
		  $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);
		  $inserted=true;
		}else if(strcmp($current,$column)==0){//Ya esta, insertamos
		  $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
		  $inserted=true;
		}else if(strcmp($current,$column)<0 && strcmp($next,$column)<0){
		  $iterative_column++;
		}else if(strcmp($current,$column)<0 && strcmp($next,$column)>0){
		  //$sheet->insertNewColumnBefore(getColumnFromNumber($iterative_column+1), 1);
		  desplazarDerecha($sheet,$iterative_column);
		  $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
		  $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);
		  $inserted=true;
		  break;
		}else{
		  $iterative_column++;
	  }
	}
	}
	function desplazarDerecha($sheet,$ColumnFrom){
	  $numFilas = 1;
	  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
	  $numFilas++;

	  for($i = $numFilas; $i>$ColumnFrom;$i--){
		$row = 1;
		echo "$i>$ColumnFrom";
		if($row==2) die();
		while(!is_null($sheet->getCell(getColumnFromNumber($row).'1')->getValue())){
		  $sheet->setCellValue(getColumnFromNumber($row).($i+1),$sheet->getCell(getColumnFromNumber($row).$i)->getValue());
		  $sheet->setCellValue(getColumnFromNumber($row).($i+1),'');
		  $row++;
		}
	  }
	}


	/*
	* Busca iterativamente si existe una columna con el mismo valor $row
	* Si no existe, la añade al final
	*/
	function insertValueToColumnOrAppend($sheet,$value,$column,$row){
	  $iterative_column = 1;
	  while(strcmp($sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue(),$column)<>0 && !is_null($sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue())){
		$iterative_column++;
	  }
	  $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
	  $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);

	  if($showinsert==1){echo "<p>inserted $value on ".getColumnFromNumber($iterative_column)."$row  $column</p>";}
	}


	/*
	* Ordena la hoja por el titulo de la columna utilizando el algoritmo de bubblesort
	* Coste n^2
	*/
	function bubbleSort($sheet){
	  $numFilas = 1;
	  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
	  $numFilas++;
	  $numColumnas = 1;
	  while(!is_null($sheet->getCell(getColumnFromNumber($numColumnas+1).'1')->getValue()))
	  $numColumnas++;

	  $swap = true;
	  while($swap){
		$swap = false;
		for($column = 1; $column < $numColumnas; $column++){
		  echo getColumnFromNumber($column).'<br>';
		  $left = $sheet->getCell(getColumnFromNumber($column).'1')->getValue();
		  $right = $sheet->getCell(getColumnFromNumber($column+1).'1')->getValue();
		  if(is_null($left)) $left='';
		  if(is_null($right)) $right='';
		  if(strcmp($right,$left)<0){
			$swap = true;

			//swap columns
			for($fila = 1; $fila <= $numFilas; $fila++){
			  $left =  $sheet->getCell(getColumnFromNumber($column).$fila)->getValue();
			  $right =  $sheet->getCell(getColumnFromNumber($column+1).$fila)->getValue();
			  if(is_null($left)) $left='';
			  if(is_null($right)) $right='';
			  $sheet->setCellValue(getColumnFromNumber($column+1).$fila,$left);
			  $sheet->setCellValue(getColumnFromNumber($column).$fila,$right);

			} // end for fila
		  }//end if
		}//end for i
		$numColumnas--;
	  }//end while
	}//end function
	function minimunSort($sheet){
	  echo 'minimunSort<br>';
	  set_time_limit(0);
	  $numFilas = 1;
	  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
	  $numFilas++;
	  $numColumnas = 1;
	  while(!is_null($sheet->getCell(getColumnFromNumber($numColumnas+1).'1')->getValue()))
	  $numColumnas++;


	  for($columnasOrdenadas=1;$columnasOrdenadas<$numColumnas;$columnasOrdenadas++){
		$col = findMinimunAlpha($sheet,$columnasOrdenadas,$numColumnas);
		//swap columns
		for($fila = 1; $fila <= $numFilas; $fila++){
		  $index =  $sheet->getCell(getColumnFromNumber($columnasOrdenadas).$fila)->getValue();
		  $index = str_replace(';','',$index);
		  $found =  $sheet->getCell(getColumnFromNumber($col).$fila)->getValue();
		  $found = str_replace(';','',$found);

		  $sheet->setCellValue(getColumnFromNumber($col).$fila,$index);
		  $sheet->setCellValue(getColumnFromNumber($columnasOrdenadas).$fila,$found);


		} // end for fila
	  }
	}

	function findMinimunAlpha($sheet,$ColumnFrom,$ColumnTo){
	  $minimunIndex = $ColumnFrom;
	  $minimunValue = $sheet->getCell(getColumnFromNumber($ColumnFrom).'1')->getValue();
	  for($i = $ColumnFrom+1;$i<$ColumnTo;$i++){
		if(strcmp($minimunValue,$sheet->getCell(getColumnFromNumber($i).'1')->getvalue())>0){
		  $minimunIndex=$i;
		  $minimunValue=$sheet->getCell(getColumnFromNumber($i).'1')->getvalue();
		}
	  }

	  return $minimunIndex;
	}

	/*
	* Ordena la hoja por el titulo de la columna utilizando el metodo de quickSort
	* Coste n*log n
	*/
	function quickSort($sheet,$inicial = '1',$final = '0'){
	  $i = $inicial;
	  if($final==0){
		while(!is_null($sheet->getCell(getColumnFromNumber($final+1).'1')->getValue()))
		$final++;
	  }
	  $f = $final;

	  $pivote = ($final + $inicial)/2;
	  $pivote = $sheet->getCell(getColumnFromNumber($pivote).'1')->getValue();

	  while($i <= $f){
		while (strcmp($sheet->getCell(getColumnFromNumber($i).'1')->getValue(),$pivote)<0)  $i++;
		while (strcmp($sheet->getCell(getColumnFromNumber($f).'1')->getValue(),$pivote)>0)  $f--;
		if ($i <= $f) {
		  swapColumns($i,$f,$sheet);
		  $i++;
		  $f--;
		}
		if ($inicial < $f)
		quickSort($sheet,$inicial, $f);

		if ($i < $final)
		quickSort($sheet,$i, $final);
	  }

	}
	/*
	* funcion auxiliar para quickSort, intercambia toda la columna1 por la columan2
	*/
	function swapColumns($column1,$column2,$sheet){
	  $row = 1;
	  $column1 = getColumnFromNumber($column1);
	  $column2 = getColumnFromNumber($column2);
	  while(!is_null($sheet->getCell('A'.$row)->getValue())){
		$val1 = $sheet->getCell($column1.$row)->getValue();
		$val2 = $sheet->getCell($column2.$row)->getValue();
		$sheet->setCellValue($column1.$row,$val2);
		$sheet->setCellValue($column2.$row,$val1);
		$row++;
	  }
	}

	/*
	* Calcula la columna en funcion del entero que se le pase
	* @param integer, posicion en la columna empezando A=1
	*
	* @return string
	*/
	function getColumnFromNumber($column) {
	  $numeric = ($column - 1) % 26;
	  $letter = chr(65 + $numeric);
	  $num2 = intval(($column - 1) / 26);
	  if ($num2 > 0) {
		return getColumnFromNumber($num2) . $letter;
	  } else {
		return $letter;
	  }
	}
?>
