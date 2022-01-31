<?PHP

error_reporting(E_ALL);

require_once("../config.php");
//require("./libreria/class.phpmailer.php");
require("./libreria/PHPMailer_5.2/PHPMailerAutoload.php");
//require_once("./libreria/PHPExcel.php");
require_once("./PHPExcel.php");
$PAGE->set_pagelayout('admin');
$title = 'Excel a profesor del acceso de los alumnos';
$urlHeadTemplate = "Fragmentos_Mail/excel_profesor_acceso_alumnos/head.html";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();
$NUM_ERRORES = 0;

$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
if(has_capability('moodle/site:config', $coursecontext)) {
	require_login();
	//Si ha llegado aquí, es admin
	//Inicialmente esta a 20 lo modificamos
	set_time_limit(400000);
	ini_set('max_execution_time',400000);


	$quecurso = "SELECT id
	FROM mdl_course
	WHERE category not in (0,18,26,27,38,52,53)
	AND shortname not like '%\_TU'
	AND id not in(164,548,549,614)
	order by mdl_course.id";

	$cuantosCursos="SELECT count(id) as num
	FROM mdl_course
	WHERE category not in (0,18,26,27,38,52,53)
	AND shortname not like '%\_TU'
	AND id not in(164,548,549,614)
	order by mdl_course.id";

	$totalc = current($DB->get_records_sql($cuantosCursos))->num;
	$body = file_get_contents($urlHeadTemplate);
	if(!isset($_GET['curso'])|| !isset($_GET['step']) || !isset($_GET ['auto']) && !isset($_GET['idcurso'])){

		?>
		<script>
			function onClickAuto(){
				var select = document.getElementById("step");
				select.selectedIndex = 0;
			}
			function onChangeSelect(){
				var select = document.getElementById("step");
				if(select.options[select.selectedIndex].value!=1)
					document.getElementById("auto").checked = false;
			}
		</script>
		<div style="width:90%">
			<h2>Ajustes:</h2>
			<form id="form1" style="width:90%;margin-left:5%" method="get" action="excel_profesor_acceso_alumnos.php" onsubmit="javascript:process()">
				<div>
					<p>Cantidad de cursos por iteraci&oacute;n</p>
					<select id="step" name="step" onchange="onChangeSelect()" >
						<option value="1">1</option>
					<?php
					for($i=5;$i<=160;$i*=2)
 					echo '<option value="'.$i.'">'.$i.'</option>'; ?>
					</select>
				</div>
				<br>
				<div>
					 <p>Curso de inicio:</p>
					 <input type="number" name="curso" max="<?=$totalc?>" min="0" value="0">
				</div>

				<div>
					 <p>&Uacute;nico curso:</p>
					 <input type="number" name="idcurso">
				</div>
				<br>
				<div class="checkbox">
					<label><input id="auto" name="auto" type="checkbox" value="1" onclick="onClickAuto()" checked>Auto</label><i>(Solo con iteraci&oacute;n = 1)</i>
				</div>
				<br>
				<p>Plantilla por defecto: <a href="<?php echo $urlHeadTemplate;?>"><?php echo $urlHeadTemplate;?></a></p>
				 <iframe style="width:100%;height:300px;border: 1px solid #000000" src="<?=$urlHeadTemplate?>"></iframe>
				<br>
				<button style="width:100%" type="submit" class="btn btn-default btn-lg">Empezar
					<i class="fa fa-envelope-o" aria-hidden="true"></i>
				</button><br>
			</form>
		</div>
		<?php
	}else{

	//require("./libreria/BD_debug.php");
	//$conexion = get_db_conn();

	//buscamos todos los cursos
	/*$quecurso = "SELECT *
	FROM mdl_course
	WHERE category not in (0,18,26,27,38,52,53)
	AND shortname not like '%\_TU'
	AND id not in(164,548,549,614)
	order by mdl_course.id";*/

	//$quecurso = "SELECT *
	//FROM mdl_course
	//WHERE mdl_course.id=547";
	if(!isset($_GET['idcurso'])){
	$step=$_GET['step'];
	$hechos=$_GET['curso'];
	$auto=0;


	//LIMIT 340,15";
	/*0	15	30	45	60	75	90	105	120	135	150	165	180	195	210	225	240	255	270	285	300	315	330	*/
	$quecurso=$quecurso." LIMIT $hechos,$step";
	$porcentaje = (($hechos+$step)/$totalc)*100;


	//$quecurso = "SELECT * FROM mdl_course where mdl_course.id=165";
	//$rescurso = mysql_query($quecurso, $conexion) or die(mysql_error());
	$rescurso = $DB->get_records_sql($quecurso);
	//Solo si step = 1, auto puede valer 1
	if($step==1 && count($rescurso)==1)
		$auto = $_GET['auto'];

	$nextURL = substr($PAGE->url,0,strpos($PAGE->url,'?'))."?step=$step&curso=".($hechos+$step)."&auto=$auto";
}else{
	$idcurso = $_GET['idcurso'];
	$step = 0;
	$hechos = 0;
	$auto = 0;

	$quecurso = "SELECT *
	FROM mdl_course
	WHERE id = $idcurso
	order by mdl_course.id";
	$nextURL="Terminado";

	$rescurso = $DB->get_records_sql($quecurso);
}
	?>
	<h2>Siguiente URL:</h2>
	<p><?=$nextURL?></p>
	<h2>Progreso:</h2>
	<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="<?=$totalc?>" style="width: <?=$porcentaje?>%;background:#5cb85c">
    	<center><?=intval($porcentaje)?>%<center>
  	</div>
	</div>
	<center><a class="btn btn-info btn-lg active"  role="button" href="<?=$nextURL?>">Continuar <i class="fa fa-arrow-right" aria-hidden="true"></i></a></center>
	<br><textarea rows="10" style="width:90%" readonly>
	<?php



	$mail = new PHPMailer();

	$mail->IsSMTP();
	$mail->Host = "smtp.aragon.es"; //smtp.ita.es //localhost
	$mail->SMTPSecure = "tls";
	//$mail->SMTPKeepAlive = true;

	$mail->SMTPAuth = true;

	//credenciales usuario
	$mail->Username = "admin.moodlefpdistancia@aragon.es";
	$mail->Password = "xxx";
	$mail->From = "admin.moodlefpdistancia@aragon.es";
	$mail->FromName = "Administrador de Plataforma FP";
	$mail->IsHTML(true);

	//while($rowcurso = mysql_fetch_assoc($rescurso)){
	foreach ($rescurso as $rowcurso) {

		$curso = $rowcurso->id;

		//Obtenemos los datos de los profesores a los que vamos a enviar el correo
		/*$queprof = "SELECT c.id, c.fullname,u.id, u.firstname,u.lastname, u.email
					FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id=ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid=ctx.id JOIN mdl_user AS u ON u.id=ra.userid
					WHERE ra.roleid= 3 AND ctx.instanceid=c.id AND c.id=".$rowcurso['id']." order by c.id";*/
		$queprof = "SELECT c.id, c.fullname,u.id, u.firstname,u.lastname, u.email
					FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id=ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid=ctx.id JOIN mdl_user AS u ON u.id=ra.userid
					WHERE ra.roleid= 3 AND ctx.instanceid=c.id AND c.id=$curso order by c.id";
		//mysql_query("SET NAMES 'utf8'");
		//mysql_query("SET CHARACTER SET utf8");
		//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
		//$resprof = mysql_query($queprof, $conexion) or die(mysql_error());
		$resprof = $DB->get_records_sql($queprof);

		//Obtenemos los datos de los alumnos del curso//
		//$queEmp = "SELECT c.id, c.fullname,u.id, u.firstname,u.lastname, u.email FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id=ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid=ctx.id JOIN mdl_user AS u ON u.id=ra.userid
		//		WHERE ra.roleid= 5 AND ctx.instanceid=c.id AND c.id =".$rowcurso['id']." order by c.id";
		$queEmp = "SELECT c.id, c.fullname,u.id, u.firstname,u.lastname, u.email FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id=ctx.instanceid JOIN mdl_role_assignments AS ra ON ra.contextid=ctx.id JOIN mdl_user AS u ON u.id=ra.userid
				WHERE ra.roleid= 5 AND ctx.instanceid=c.id AND c.id =$curso order by c.id";
		//mysql_query("SET NAMES 'utf8'");
		//mysql_query("SET CHARACTER SET utf8");
		//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
		//$resEmp = mysql_query($queEmp, $conexion) or die(mysql_error());
		//$totEmp = mysql_num_rows($resEmp);
		$resEmp = $DB->get_records_sql($queEmp);

		$totEmp = count($resEmp);

		if ($totEmp !=0)
		{

			//Inicializamos los datos de que fila y columna comenzar a escribir
			$fila=3;

			//Creamos el objeto excel
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,2,"USER ID");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,2,"NOMBRE");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,2,"APELLIDOS");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,2,"EMAIL");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,2,"NOMBRE DEL CURSO");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,2,"NUMERO DIAS CONECTADO");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,2,"VECES VISTO CURSO");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,2,"NUMERO DE REGISTROS");

			//while($rowsql = mysql_fetch_row($resEmp))
			foreach ($resEmp as $rowsql)
			{
				//$curso=$rowsql[0];
				//$nombrecurso= $rowsql[1];
				$nombrecurso=$rowsql->fullname;

				//$usuario=$rowsql[2];
				$usuario=$rowsql->id;
				$fila=$fila + 1;
				$columna=9;

				//Numero de dias que se ha conectado
				//$queEmp5 = "SELECT DISTINCT(DATE(FROM_UNIXTIME(timecreated))) FROM mdl_logstore_standard_log WHERE userid =$usuario AND courseid =$curso GROUP BY DATE(FROM_UNIXTIME(timecreated))";
				$queEmp5 = "SELECT count(DISTINCT(DATE(FROM_UNIXTIME(timecreated)))) as num FROM mdl_logstore_standard_log WHERE userid =$usuario AND courseid =$curso";
				//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
				//$dias= mysql_num_rows($resEmp5);
				$dias = current($DB->get_records_sql($queEmp5))->num;


				// numero de veces visto el curso
				//$queEmp5 = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE target='course' AND action='viewed' AND userid=$usuario AND courseid = $curso";
				$queEmp5 = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE target='course' AND action='viewed' AND userid=$usuario AND courseid = $curso";
				//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
				//$total= mysql_fetch_row($resEmp5);
				$total = current($DB->get_records_sql($queEmp5))->num;

				//numero de registros
				//$qu = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso";
				$qu = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso";
				//$re = mysql_query($qu, $conexion) or die(mysql_error());
				//$total_reg = mysql_fetch_row($re);
				$total_reg = current($DB->get_records_sql($queEmp5))->num;

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$fila,$usuario);
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$fila,$rowsql[3]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$fila,$rowsql->firstname);
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$fila,$rowsql[4]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$fila,$rowsql->lastname);
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$fila,$rowsql[5]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$fila,$rowsql->email);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$fila,$nombrecurso);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$fila,$dias);
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$fila,$total[0]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7,$fila,$total);
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$fila,$total_reg[0]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$fila,$total_reg);

				//saber los recursos de cada seccion
				$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
				//mysql_query("SET NAMES 'utf8'");
				//mysql_query("SET CHARACTER SET utf8");
				//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
				//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
				$resEmp5 = $DB->get_records_sql($queEmp5);

				//while($rowsql5 = mysql_fetch_row($resEmp5))
				foreach ($resEmp5 as $rowsql5)
				{
					//$section=$rowsql5[1];
					$section=$rowsql5->section;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,1,"TEMA ".$section);

					//$seccion=$rowsql5[0];
					$seccion=$rowsql5->id;
					$queEmp6 = "SELECT id, module, instance From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
					//mysql_query("SET NAMES 'utf8'");
					//mysql_query("SET CHARACTER SET utf8");
					//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
					//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
					$resEmp6 = $DB->get_records_sql($queEmp6);

					//while($rowsql6 = mysql_fetch_row($resEmp6))
					foreach ($resEmp6 as $rowsql6)
					{
						//$id_modulo=$rowsql6[0];
						$id_modulo=$rowsql6->id;
						//$tipom=$rowsql6[1];
						$tipom=$rowsql6->module;
						//$instance=$rowsql6[2];
						$instance=$rowsql6->instance;

						switch ($tipom)
						{
						case 10:
							//Es un foro
							//numero de visitas
							//$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_foro=  mysql_fetch_row($resEmp7);
							$visto_foro = $DB->get_records_sql($queEmp7);

							//numero de post
							//$queEmp8 = "SELECT COUNT(mdl_forum_posts.id) From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_forum_posts.id) as num From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_foro=  mysql_fetch_row($resEmp8);
							$escrito_foro = $DB->get_records_sql($queEmp8);

							//nombre del foro
							//$qu1 = "SELECT *  FROM mdl_forum WHERE id = $instance";
							$qu1 = "SELECT name  FROM mdl_forum WHERE id = $instance";
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_foro[0]." Post: ".$escrito_foro[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_foro)->num." Post: ".current($escrito_foro)->num);
							$columna=$columna +1;

							break;
						case 7:
							//Las bases de datos son msl_data data_records es el numero de veces que ha escrito registros
							//numero de visitas
							//$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE component='mod_data' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_data' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_base=  mysql_fetch_row($resEmp7);
							$visto_base = $DB->get_records_sql($queEmp7);

							//numero de registros
							//$queEmp8 = "SELECT COUNT(mdl_data_records.id) From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_data_records.id) as num From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_base= mysql_fetch_row($resEmp8);
							$escrito_base = $DB->get_records_sql($queEmp8);

							//nombre del foro
							$qu1 = "SELECT name FROM mdl_data WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_base[0]." Aportaciones: ".$escrito_base[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_base)->num." Post: ".current($escrito_base)->num);
							$columna=$columna +1;
							break;
						case 6:
							//las consultas son mdl_choice
							//$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE component='mod_choice' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_choice' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_consulta=  mysql_fetch_row($resEmp7);
							$visto_consulta = $DB->get_records_sql($queEmp7);

							//numero de registros
							//$queEmp8 = "SELECT COUNT(mdl_choice_answers.id) From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario";
							$queEmp8 = "SELECT COUNT(mdl_choice_answers.id) as num From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_consulta=  mysql_fetch_row($resEmp8);
							$escrito_consulta = $DB->get_records_sql($queEmp8);

							//nombre de la consulta
							$qu1 = "SELECT name  FROM mdl_choice WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_consulta[0]." Aportaciones: ".$escrito_consulta[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_consulta)->num." Aportaciones: ".current($escrito_consulta)->num);
							$columna=$columna +1;

							break;

						case 19:
							//hay q poner mdl_quiz con veces entrado, resultado, intentos
							//$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) FROM mdl_logstore_standard_log WHERE component='mod_quiz' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_quiz' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_cuestionario=  mysql_fetch_row($resEmp7);
							$visto_cuestionario = $DB->get_records_sql($queEmp7);

							//numero de registros
							//$queEmp8 = "SELECT COUNT(mdl_quiz_grades.id),mdl_quiz_grades.grade From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_quiz_grades.id) as num,mdl_quiz_grades.grade as grade From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$rowsql8=mysql_fetch_row($resEmp8);
							$rowsql8 = $DB->get_records_sql($queEmp8);

							$nota_cuestionario=0;
							if(current($rowsql8)->num){
								//$rowsql8 = mysql_fetch_assoc($resEmp8);
								//$nota_cuestionario=$rowsql8[1];
								$nota_cuestionario=current($rowsql8)->grade;
							}

							//numero de intentos
							//$queEmp8 = "SELECT COUNT(mdl_quiz_attempts.id) From mdl_quiz_attempts  inner join mdl_quiz on mdl_quiz_attempts.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_attempts.userid=$usuario and mdl_quiz_attempts.quiz=$instance ";
							$queEmp8 = "SELECT COUNT(mdl_quiz_attempts.id) as num From mdl_quiz_attempts  inner join mdl_quiz on mdl_quiz_attempts.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_attempts.userid=$usuario and mdl_quiz_attempts.quiz=$instance ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$intentos = mysql_fetch_row($resEmp8);
							$intentos = $DB->get_records_sql($queEmp8);

							//nombre del cuestionario
							$qu1 = "SELECT name  FROM mdl_quiz WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_cuestionario[0]." Nota: ".$nota_cuestionario." Intentos:".$intentos[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_cuestionario)->num." Nota: ".number_format($nota_cuestionario,2)." Intentos:".current($intentos)->num);
							$columna=$columna +1;

							break;

						case 23:
							//m�dulo survey
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_survey' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_survey' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_encuesta= mysql_fetch_row($resEmp7);
							$visto_encuesta = $DB->get_records_sql($queEmp7);

							//numero de registros
							//$queEmp8 = "SELECT COUNT(mdl_survey_answers.id) From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_survey_answers.id) as num From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_encuesta = mysql_num_rows($resEmp8);
							$escrito_encuesta = $DB->get_records_sql($queEmp8);

							//nombre
							$qu1 = "SELECT name  FROM mdl_survey WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_encuesta[0]." Aportaciones: ".$escrito_encuesta[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_encuesta)->num." Aportaciones: ".current($escrito_encuesta)->num);
							$columna=$columna +1;

							break;
						case 16:
							//m�dulo herramienta externa mdl_lti_submission
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_lti' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_lti' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_herramienta = mysql_fetch_row($resEmp7);
							$visto_herramienta = $DB->get_records_sql($queEmp7);

							//numero de registros
							//$queEmp8 = "SELECT COUNT(mdl_lti_submission.id) From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_lti_submission.id) as num From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_herramienta = mysql_fetch_row($resEmp8);
							$escrito_herramienta = $DB->get_records_sql($queEmp8);

							//nombre del foro
							$qu1 = "SELECT name  FROM mdl_lti WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_herramienta[0]." Aportaciones: ".$escrito_herramienta[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_herramienta)->num." Aportaciones: ".current($escrito_herramienta)->num);
							$columna=$columna +1;

							break;
						case 11:
							//Glosario
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_glosario = mysql_fetch_row($resEmp7);
							$visto_glosario = $DB->get_records_sql($queEmp7);

							//nombre
							//$qu1 = "SELECT *  FROM mdl_glossary WHERE id = $instance";
							$qu1 = "SELECT name  FROM mdl_glossary WHERE id = $instance";
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_glosario[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_glosario)->num);
							$columna=$columna +1;

							break;
						case 14:
							//m�dulo mdl_lesson
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_lesson' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_lesson' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_leccion = mysql_fetch_row($resEmp7);
							$visto_leccion = $DB->get_records_sql($queEmp7);

							//numero de envios
							//$queEmp8 = "SELECT COUNT(mdl_lesson_attempts.id) From mdl_lesson_attempts inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
							$queEmp8 = "SELECT COUNT(mdl_lesson_attempts.id) as num From mdl_lesson_attempts inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_leccion = mysql_fetch_row($resEmp8);
							$escrito_leccion = $DB->get_records_sql($queEmp8);

							//numero de envios
							//$queEmp8 = "SELECT COUNT(mdl_lesson_grades.id), mdl_lesson_grades.grade From mdl_lesson_grades inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_lesson_grades.id) as num, mdl_lesson_grades.grade as grade From mdl_lesson_grades inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$rowsql8 = mysql_fetch_row($resEmp8);
							$rowsql8 = $DB->get_records_sql($queEmp8);

							$nota_leccion=0;
							//if( $resEmp8[0])
							if( current($rowsql8)->num)
							{
								//$nota_leccion=$rowsql8[1];
								$nota_leccion=current($rowsql8)->grade;
							}

							//nombre lecci�n
							$qu1 = "SELECT name  FROM mdl_lesson WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_leccion[0]." Intentos: ".$escrito_leccion[0]." Nota: ".$nota_leccion);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_leccion)->num." Intentos: ".current($escrito_leccion)->num." Nota: ".$nota_leccion);
							$columna=$columna +1;

							break;
						case 26:
							//taller workshop
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_workshop' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_workshop' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_taller = mysql_fetch_row($resEmp7);
							$visto_taller = $DB->get_records_sql($queEmp7);

							//numero de envios
							//$queEmp8 = "SELECT COUNT(mdl_workshop_submissions.id), mdl_workshop_submissions.grade From mdl_workshop_submissions inner join mdl_workshop on mdl_workshop_submissions.workshopid=mdl_workshop.id WHERE mdl_workshop.id = $instance and  mdl_workshop_submissions.authorid=$usuario ";
							$queEmp8 = "SELECT COUNT(mdl_workshop_submissions.id) as num, mdl_workshop_submissions.grade as grade From mdl_workshop_submissions inner join mdl_workshop on mdl_workshop_submissions.workshopid=mdl_workshop.id WHERE mdl_workshop.id = $instance and  mdl_workshop_submissions.authorid=$usuario ";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_taller = mysql_fetch_row($resEmp8);
							$escrito_taller = $DB->get_records_sql($queEmp8);

							$nota_taller=0;
							//if ($escrito_taller[0]){
							if (current($escrito_taller)->num){
								//$nota_taller=$escrito_taller[1];
								$nota_taller=current($escrito_taller)->grade;
							}

							//nombre del taller
							$qu1 = "SELECT name  FROM mdl_workshop WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_taller[0]." Nota: ".$nota_taller);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_taller)->num." Nota: ".$nota_taller);
							$columna=$columna +1;

							break;
						case 1:
							//Tareas
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_assign' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_assign' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_tarea = mysql_fetch_row($resEmp7);
							$visto_tarea = $DB->get_records_sql($queEmp7);

							//numero de envios
							$queEmp8 = "SELECT mdl_assign.name as name From mdl_assign_grades  inner join mdl_assign on mdl_assign_grades.assignment=mdl_assign.id WHERE mdl_assign.id = $instance and  mdl_assign_grades.userid=$usuario ";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_tarea = mysql_num_rows($resEmp8);
							$escrito_tarea = $DB->get_records_sql($queEmp8);

							$nota_tarea=0;
							//if ($escrito_tarea){
							if (current($escrito_tarea)){
								$//rowsql8 = mysql_fetch_assoc($resEmp8);
								//$nota_tarea=round($rowsql8['grade'],2);
								$nota_tarea=number_format(current($rowsql8)->grade,2);
								//$nombre_tarea=$rowsql8['name'];
								$nombre_tarea=current($rowsql8)->name;
							}else{
								//nombre de tarea mdl assign
								$qu1 = "SELECT name FROM mdl_assign WHERE id = $instance";
								//mysql_query("SET NAMES 'utf8'");
								//mysql_query("SET CHARACTER SET utf8");
								//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
								//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
								//$rowsqlr = mysql_fetch_assoc($re1);
								$rowsqlr = $DB->get_records_sql($qu1);
								//$nombre_tarea=$rowsqlr['name'];
								$nombre_tarea=current($rowsqlr)->name;
							}
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$nombre_tarea);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_tarea[0]." Nota: ".$nota_tarea);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_tarea)->num." Nota: ".$nota_tarea);
							$columna=$columna +1;

							break;
						// case 2: //NO SE USA

							// $queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'assignment' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario  ";
							// $resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							// $visto_tarea = mysql_num_rows($resEmp7);

							// numero de envios
							// $queEmp8 = "SELECT * From mdl_assignment_submissions  inner join mdl_assignment on mdl_assignment_submissions.assignment=mdl_assignment.id WHERE mdl_assignment.id = $instance and  mdl_assignment_submissions.userid=$usuario and mdl_assignment_submissions.assignment=$instance ";
							// $resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							// $escrito_tarea = mysql_num_rows($resEmp8);

							// $nota_tarea=0;
							// if ($escrito_tarea){
								// $rowsql8 = mysql_fetch_assoc($resEmp8);
								// $nota_tarea=$rowsql8['grade'];
							// }
							// tarea mdl assign
							// nombre del foro
							// $qu1 = "SELECT *  FROM `mdl_assignment` WHERE `id` = $instance";
							// $re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							// $rowsqlr = mysql_fetch_assoc($re1);

							// $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,utf8_decode($rowsqlr['name']));
							// $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_tarea." Nota: ".$nota_tarea);
							// $columna=$columna +1;

							// break;
						case 25:
							//wiki
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_wiki' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_wiki' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_wiki = mysql_fetch_row($resEmp7);
							$visto_wiki = $DB->get_records_sql($queEmp7);

							//numero de envios
							//$queEmp8 = "SELECT COUNT(mdl_wiki_versions.id)  FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
							$queEmp8 = "SELECT COUNT(mdl_wiki_versions.id) as num  FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_wiki = mysql_fetch_row($resEmp8);
							$escrito_wiki = $DB->get_records_sql($queEmp8);

							//nombre de wiki
							$qu1 = "SELECT name  FROM mdl_wiki WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_wiki[0]." Aportaciones: ".$escrito_wiki[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_wiki)->num." Aportaciones: ".current($escrito_wiki)->num);
							$columna=$columna +1;

							break;
						case 20:
							//archivo externo
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE (component='mod_resource' OR component='core') AND (action='viewed' OR action='updated') AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE (component='mod_resource' OR component='core') AND (action='viewed' OR action='updated') AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_recurso = mysql_fetch_row($resEmp7);
							$visto_recurso = $DB->get_records_sql($queEmp7);

							//nombre
							$qu1 = "SELECT name  FROM mdl_resource WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_recurso[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_recurso)->num);
							$columna=$columna +1;

							break;
						case 21:
							//SCORM
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_scorm' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_scorm' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_scorm = mysql_fetch_row($resEmp7);
							$visto_scorm = $DB->get_records_sql($queEmp7);

							//nombre del SCROM
							//$queEmp8 = "SELECT title,mdl_scorm_scoes.id  FROM mdl_scorm_scoes left join mdl_scorm_scoes_track  on mdl_scorm_scoes_track.scoid=mdl_scorm_scoes.id WHERE  mdl_scorm_scoes_track.userid=$usuario and mdl_scorm_scoes.scorm=$instance and element='cmi.core.lesson_status' and value='completed' order by mdl_scorm_scoes.id DESC";
							$queEmp8 = "SELECT title,mdl_scorm_scoes.id as id  FROM mdl_scorm_scoes left join mdl_scorm_scoes_track  on mdl_scorm_scoes_track.scoid=mdl_scorm_scoes.id WHERE  mdl_scorm_scoes_track.userid=$usuario and mdl_scorm_scoes.scorm=$instance and element='cmi.core.lesson_status' and value='completed' order by mdl_scorm_scoes.id DESC";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
							//$escrito_SCORM = mysql_num_rows($resEmp8);
							$resEmp8 = $DB->get_records_sql($queEmp8);
							$escrito_SCORM = count($resEmp8);



							if ($escrito_SCORM){
								//$rowsql8 = mysql_fetch_assoc($resEmp8);
								//$pag_SCORM=$rowsql8['title'];
								$pag_SCORM=current($resEmp8)->title;
								//$numero=$rowsql8['id'];
								$numero=current($resEmp8)->id;
							}else{
								$numero=0;
								$pag_SCORM="Sin iniciar";
							}

							$queEmp9 = "SELECT MAX(id) as id FROM mdl_scorm_scoes  WHERE  mdl_scorm_scoes.scorm=$instance ";
							//$resEmp9 = mysql_query($queEmp9, $conexion) or die(mysql_error());
							$resEmp9 = $DB->get_records_sql($queEmp9);
							//if (mysql_num_rows($resEmp9)>0){
							if (count($resEmp9)>0){
								//$rowsql9 = mysql_fetch_assoc($resEmp9);
								//$nmax=$rowsql9['id'];
								$nmax=current($resEmp9)->id;

							}else{
								$nmax=0;
							}

							If ($numero==$nmax){
								$pag_SCORM2="Completado";
							}
							else{
								$pag_SCORM2="Incompleto";
							}

							$qu1 = "SELECT name  FROM mdl_scorm WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_scorm[0]." \Estado: ".utf8_encode($pag_SCORM2)." \Ultima pagina visitada: ".$pag_SCORM);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_scorm)->num." \Estado: ".utf8_encode($pag_SCORM2)." \Ultima pagina visitada: ".$pag_SCORM);
							$columna=$columna +1;

							break;
						case 3:
							//libro
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_libro = mysql_fetch_row($resEmp7);
							$visto_libro = $DB->get_records_sql($queEmp7);

							//nombre del libro
							$qu1 = "SELECT name  FROM mdl_book WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_libro[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_libro)->num);
							$columna=$columna +1;

							break;
						case 18:
							//pagina
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_pagina = mysql_fetch_row($resEmp7);
							$visto_pagina = $DB->get_records_sql($queEmp7);

							//nombre
							$qu1 = "SELECT name  FROM mdl_page WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_pagina[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_pagina)->num);
							$columna=$columna +1;

							break;
						case 24:
							//url
							//$queEmp7 = "SELECT COUNT(id) From mdl_log WHERE mdl_log.module = 'url' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
							//$queEmp7 = "SELECT COUNT(id) FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							$queEmp7 = "SELECT COUNT(id) as num FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
							//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
							//$visto_url = mysql_fetch_row($resEmp7);
							$visto_url = $DB->get_records_sql($queEmp7);

							//nombre del foro
							$qu1 = "SELECT name  FROM mdl_url WHERE id = $instance";
							//mysql_query("SET NAMES 'utf8'");
							//mysql_query("SET CHARACTER SET utf8");
							//mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
							//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
							//$rowsqlr = mysql_fetch_assoc($re1);
							$rowsqlr = $DB->get_records_sql($qu1);

							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,$rowsqlr['name']);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,2,current($rowsqlr)->name);
							//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".$visto_url[0]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna,$fila,"Visitas: ".current($visto_url)->num);
							$columna=$columna +1;

							break;
						}
					}
				}

				for ($i = 0; $i <= $columna; $i++) {
					$objPHPExcel->getActiveSheet()->getColumnDimension(chr(65+$i))->setAutoSize(true);
				}

			}

			//Almacenamos el excel
			$varname = "/tmp/excel_profesor_acceso_alumnos2.xlsx";
			//$varname = "ACCESO_".filter_var($nombrecurso,FILTER_SANITIZE_STRING).".xlsx";
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save($varname);

			//Enviar email
			//while($rowprof = mysql_fetch_assoc($resprof)){
			foreach ($resprof as $rowprof) {

				//$add=$rowprof['email'];
				//$add=$rowprof->email;
				$add="gcebollero@itainnova.es";

				$mail->AddAttachment($varname, $varname);
				$mail->AddAddress($add);
				$mail->AddAddress("gcebollero@aragon.es");
				//$mail->AddAddress('fpdistancia@itainnova.es');
				//$mail->AddBCC('fpdistancia@itainnova.es');
				//$mail->AddAddress('cpina@itainnova.es');

				//$body = "";
				//$body.= "<p>Estimad@ :</p>
				//<p>Te enviamos el <strong>resumen de entradas y tareas/actividades</strong> planteadas desde el comienzo del curso de <strong>los alumn@s matriculados en el curso que tutorizas</strong> (n&uacute;mero de accesos a la plataforma, aportaciones en los foros, tareas que ha enviado, tareas que faltan por enviar....)</p>
				//<p>Con esta informaci&oacute;n puedes comprobar la participaci&oacute;n y las conexiones que han realizado los alumnos a lo largo del curso y contrastar si los alumnos han alcanzado determinados aprendizajes y por tanto si se han alcanzado los objetivos del curso.</p>
				//<p>Adem&aacute;s nos aporta informaci&oacute;n para corregir o reorientar sobre la marcha el proceso de ense&ntilde;anza (permite ofrecer ayuda y asistencia en los momentos en que se puedan producir dudas o abandonos).</p>
				//<p>Quedando a tu disposici&oacute;n ante cualquier duda, comentario o sugerencia, te saluda atentamente:</p>
				//<p>Administradores Moodle de la Plataforma</p>
				//<p>Carlos y Reyes</p>
				//<p>&nbsp;</p>
				//<p>NOTA: Desde la portada de la Plataforma o desde el apartado mis cursos&gt; <a href='http://moodle.teleformacionfp.aragon.es/my/' target='_blank'>Mis cursos</a></p>
				//<p>Puedes acceder a la <strong><a href='http://moodle.teleformacionfp.aragon.es/course/view.php?id=278.' target='_blank'>Sala de Pofesores</a>. </strong>Un espacio reservado para tod@s los profesores que permitir&aacute; el intercambio de experiencias, resoluci&oacute;n de dudas y formulaci&oacute;n de consultas con otros docentes en el que a lo largo del a&ntilde;o, iremos colgando, recursos e informaci&oacute;n que os puede resultar de utilidad.</p>
				//<ul>
				//<li>
				//<div><a href='https://www.youtube.com/watch?v=BEBpIdaYVIU&amp;list=PL4Qs5FwWJg3Gal_l1nh9Z_3KmIlTVExdG' data-sessionlink='ei=8gUGV6mDG5ffcI3zr_gN&amp;feature=c4-overview&amp;ved=CEsQwBsiEwipxPrB-vvLAhWXLxwKHY35C98omxw'><img src='https://i.ytimg.com/vi/BEBpIdaYVIU/mqdefault.jpg' alt='' width='196' /><strong>24</strong> v&iacute;deos </a></div>
				//<div><h3><a dir='ltr' title='Tutoriales para Profesores' href='https://www.youtube.com/playlist?list=PL4Qs5FwWJg3Gal_l1nh9Z_3KmIlTVExdG' target='_blank' data-sessionlink='ei=8gUGV6mDG5ffcI3zr_gN&amp;ved=CEwQvxsiEwipxPrB-vvLAhWXLxwKHY35C98omxw'>Tutoriales para Profesores</a></h3></div>
				//</li>
				//</ul>";
				/*$body.= "<p>Estimad@ Profesor:</p>
				<p>En muchas ocasiones el estudiante abandona el curso, sin que el tutor y/o compa&ntilde;eros se percaten, es decir, de forma silenciosa los participantes se desmotivan y no contin&uacute;an con sus estudios.</p>
				<p><strong>Es importante contar con las herramientas necesarias para evitar que los periodos de desconexi&oacute;n del alumno no sean muy prolongados en el tiempo.</strong> Un alumno que est&eacute; dos semanas sin conectarse a la Plataforma, tendr&aacute; muchas probabilidades de ser baja ya que despu&eacute;s de ese tiempo es complicado volver a entrar en la din&aacute;mica de estudio. Por esta raz&oacute;n, os enviamos a alumnos y profesores estos informes de actividad del curso.</p>
				<p>Sin registros e informes y sin personas que se encarguen de acompa&ntilde;ar al alumno en su proceso de aprendizaje, las tasas de abandono siguen siendo un problema en el eLearning. La combinaci&oacute;n de informes de seguimiento potentes y de tutorizaci&oacute;n personalizada ayudan a que los alumnos aprendan y finalicen con &eacute;xito los cursos.</p>
				<p><strong>&iexcl;&iexcl;Recta final del curso!!.</strong> Te enviamos el <strong>resumen de entradas y tareas/actividades</strong> planteadas desde el comienzo del curso de <strong>los alumn@s matriculados en el curso que tutorizas</strong> (n&uacute;mero de accesos a la plataforma, aportaciones en los foros, tareas que ha enviado, tareas que faltan por enviar....).</p>
				<p>Con esta informaci&oacute;n puedes comprobar la participaci&oacute;n y las conexiones que han realizado los alumnos a lo largo del curso y contrastar si los alumnos han alcanzado determinados aprendizajes y por tanto si se han alcanzado los objetivos del curso.</p>
				<p>Adem&aacute;s nos aporta informaci&oacute;n para corregir o reorientar sobre la marcha el proceso de ense&ntilde;anza (permite ofrecer ayuda y asistencia en los momentos en que se puedan producir dudas o abandonos).</p>
				<p>Quedando a tu disposici&oacute;n ante cualquier duda, comentario o sugerencia, te saluda atentamente:</p>
				<p>Administradores Moodle de la Plataforma</p>
				<p>Carlos y Reyes</p>
				<p>&nbsp;</p>
				<p>NOTA: Desde la portada de la Plataforma o desde el apartado mis cursos&gt; <a href='http://moodle.teleformacionfp.aragon.es/my/' target='_blank'>Mis cursos</a></p>
				<p>Puedes acceder a la <strong><a href='http://moodle.teleformacionfp.aragon.es/course/view.php?id=278.' target='_blank'>Sala de Pofesores</a>. </strong>Un espacio reservado para tod@s los profesores que permitir&aacute; el intercambio de experiencias, resoluci&oacute;n de dudas y formulaci&oacute;n de consultas con otros docentes en el que a lo largo del a&ntilde;o, iremos colgando, recursos e informaci&oacute;n que os puede resultar de utilidad.</p>
				<ul>
				<li>
				<div><a href='https://www.youtube.com/watch?v=BEBpIdaYVIU&amp;list=PL4Qs5FwWJg3Gal_l1nh9Z_3KmIlTVExdG' data-sessionlink='ei=8gUGV6mDG5ffcI3zr_gN&amp;feature=c4-overview&amp;ved=CEsQwBsiEwipxPrB-vvLAhWXLxwKHY35C98omxw'><img src='https://i.ytimg.com/vi/BEBpIdaYVIU/mqdefault.jpg' alt='' width='196' /><strong>24</strong> v&iacute;deos </a></div>
				<div><h3><a dir='ltr' title='Tutoriales para Profesores' href='https://www.youtube.com/playlist?list=PL4Qs5FwWJg3Gal_l1nh9Z_3KmIlTVExdG' target='_blank' data-sessionlink='ei=8gUGV6mDG5ffcI3zr_gN&amp;ved=CEwQvxsiEwipxPrB-vvLAhWXLxwKHY35C98omxw'>Tutoriales para Profesores</a></h3></div>
				</li>
				</ul>";
				*/
				$mail->Body = $body;
				$mail->Subject = "Actividad de la asignatura: ".UTF8_decode($nombrecurso);
				$enviado = false;
				$i = 0;
				for(; $i < 100 && !$enviado; $i++){
					$enviado = $mail->Send();
				}
				if(!$enviado){
					echo "Error al enviar correo de ";
					$NUM_ERRORES++;
				}else{
					echo "Enviado tras $i intentos \t";
				}
				$mail->clearAddresses();
				$mail->clearAttachments();
			}
			//exit();
			unset($objPHPExcel);
			unlink($varname);
		}

		echo $curso." - ".$nombrecurso."\n";

		$sql="INSERT INTO itainnova_inf_envio_masivo(curso) VALUES (".$curso.")";
		//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
		$DB->execute($sql);
	}
	echo "</textarea><center><h2>Emails enviados correctamente.</h2></center>";
	if($auto && $NUM_ERRORES==0)
		echo '<META HTTP-EQUIV="Refresh" CONTENT="1;URL='.$nextURL.'">';
	//mysql_close($conexion); //cerramos la conexion con la base de datos
}
}
else
{
	//Si llega aquí, no es admin
	echo "<center><h1 style='color:red'>Usuario no autorizado</h1></br>";
	echo "<p>Contacte con su administrador del sitio para mas informaci&oacute;n.</p></center>";
}
echo $OUTPUT->footer();
?>
