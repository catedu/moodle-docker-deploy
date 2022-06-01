<?php
require_once('../config.php');
//require("./libreria/BD_debug.php");
require("./libreria/PHPMailer_5.2/PHPMailerAutoload.php");
$urlHeadTemplate = "Fragmentos_Mail/envio_masivo_nota/head.html";
$urlTailTemplate = "Fragmentos_Mail/envio_masivo_nota/tail.html";
$editor1 = file_get_contents($urlHeadTemplate);
$editor2 = file_get_contents($urlTailTemplate);


$PAGE->set_pagelayout('admin');
$title = 'Envio masivo de notas';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();
?>
<script language="JavaScript">
function seleccionar_todo(){
	for (i=0;i<document.form1.elements.length;i++)
	if(document.form1.elements[i].type == "checkbox") document.form1.elements[i].checked=1
}

function deseleccionar_todo(){
	for (i=0;i<document.form1.elements.length;i++)
	if(document.form1.elements[i].type == "checkbox") document.form1.elements[i].checked=0
}
</script>
<script type="text/javascript" src="./libreria/ckeditor/ckeditor.js">
</script>
<div style="width:80%;">
	<!--cabecera-->
	<table>
		<tr>
			<td><img src="<?php echo $CFG->wwwroot.'/theme/'.$CFG->theme.'/background_bandera.png';?>"></td>
			<td><center><img src="<?php echo $CFG->wwwroot.'/theme/'.$CFG->theme.'/pix/LogoGobierno_Aragón.png';?>" border="0"></center></td>
		</tr>
	</table>
	<!-- fin cabecera-->

	<HR SIZE=1 WIDTH="100%"  style="float:right; margin-left:30px;" >

		<?php
		error_reporting(E_ALL);
		$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		if(has_capability('moodle/site:config', $coursecontext)) {
			require_login();
			//Si ha llegado aquí, es admin
			//$conexion = get_db_conn();
			if (!$_POST){
				?>
				<center><h1><u><b>Mensajes de seguimiento</b></u></h1></center> <HR SIZE=1 WIDTH="100%" style="float:right; margin-left:30px;" ><br>
					<form name=form1 method=post action=envio_masivo_nota.php>
						<div  style="background-color:#ffffff; border:solid #c3d7f0 1px;">
							<h2 style="padding-left:10px;">Seleccione el curso (si es necesario)</h2>
							<select style="width:90%;margin-left:5%" id='curso' name='curso' >
								<option value=0>Por favor selecciona el curso ...</option>
								<?php
								$sql="SELECT id, fullname FROM mdl_course WHERE category not in (0,18,26,27,38,52) AND shortname not like '%\_TU' order by mdl_course.id" ;
								//$sql="SELECT id, fullname FROM mdl_course WHERE id =547" ;
								//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
								//$sqlresp = mysql_num_rows($sqlinsert);
								$sqlresp = $DB->get_records_sql($sql);
								//while($rowsql = mysql_fetch_assoc($sqlinsert)){
								foreach ($sqlresp as $rowsql) {
									//echo "<option value='".$rowsql['id']."'>".$rowsql['id']."-".UTF8_decode($rowsql['fullname'])."</option>";
									echo "<option value='".$rowsql->id."'>".$rowsql->id."-".$rowsql->fullname."</option>";
								}
								?>
							</select>
							<br>
							<h2 style="padding-left:10px;">Escriba aquí el mensaje que desea enviar</h2>
							<div style="width:90%;margin-left:5%;">
								<p>Plantilla por defecto: <a href="<?php echo $urlHeadTemplate;?>"><?php echo $urlHeadTemplate;?></a></p>
								<textarea cols="80" id="editor1" name="editor1" rows="10">
									<?php echo $editor1;?>
								</textarea>
								<script type="text/javascript">
								CKEDITOR.replace ("editor1");
								</script>
							</div>
							<br>
							<h2 style="padding-left:10px;">Seleccione los campos que desea enviar</h2>
							<div style='width:90%;margin-left:5%;'>
								<table style="width:100%">
									<tr>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="cc" checked="true" id="cc" value="1"> N&uacute;mero de conexiones</label>
										</td>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="cf" checked="true" id="cf"value="1"> N&uacute;mero de veces que ha escrito en el foro</label>
										</td>
									</tr>
									<tr>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="cv" checked="true" id="cv"value="1" > N&uacute;mero de visitas al curso</label>
										</td>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="cm" checked="true" id="cm"value="1" > N&uacute;mero de visitas por m&oacute;dulo</label>
										</td>
									</tr>
									<tr>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="ct" checked="true" id="ct" value="1"> Test que ha realizado junto a la nota</label>
										</td>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="ctf" checked="true" id="ctf" value="1"> Test que faltan por realizar</label>
										</td>
									</tr>
									<tr>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="ca" checked="true" id="ca"value="1"> Actividades o recursos que has revisado</label>
										</td>
										<td style="width:45%">
											<label class="checkbox-inline"><input type="checkbox" name="caf" checked="true" id="caf"value="1"> Actividades o recursos que te faltan por revisar</label>
										</td>
									</tr>
								</table>
								<br>
								<center>
									<div class="btn-group" role="group" aria-label="...">
										<button type="button" class="btn btn-default" onclick="javascript:seleccionar_todo();">Seleccionar todos</button>
										<button type="button" class="btn btn-default" onclick="javascript:deseleccionar_todo();">Deseleccionar todos</button>
									</div>
								</center>
							</div>
							<br>
							<h2 style="padding-left:10px;">Escriba aquí la despedida del email</h2>
							<div style="width:90%;margin-left:5%;">
								<p>Plantilla por defecto: <a href="<?php echo $urlTailTemplate;?>"><?php echo $urlTailTemplate;?></a></p>
								<textarea cols="80" id="editor2" name="editor2" rows="10">
									<?php echo $editor2;?>
								</textarea>
								<script type="text/javascript">
								CKEDITOR.replace ("editor2");
								</script>
							</div>
							<br>
							<center><input type="submit" name="submit" value="Enviar"></center>
							<br>
						</div>
					</form>
				</div>
				<?php
			}else{
				ini_set('max_execution_time',4000000);

				$mail = new PHPMailer();

				$mail->IsSMTP();
				$mail->Host = "smtp.aragon.es";
				$mail->SMTPSecure = "tls";
				$mail->SMTPAuth = true;
				// credenciales usuario
				include "./secret/crenciales.php";
				$mail->IsHTML(true);
				//$mail->SMTPKeepAlive = true;

				$cursopost="";
				if ($_POST["curso"]!=0){
					$cursopost=" AND mdl_course.id =".$_POST["curso"];
				}
				$quecurso = "SELECT * FROM mdl_course WHERE category not in (0,18,26,27,38,52,53,55) ".$cursopost."  AND shortname not like '%\_TU' AND shortname not like '%OLD%' AND shortname not like '%ERROR%' order by mdl_course.id ASC ";

				//$quecurso = "SELECT * FROM mdl_course WHERE category not in (0,18,26,27,38,52) AND mdl_course.id =165 AND shortname not like '%\_TU' order by mdl_course.id";
				//CARLOS 2016 $quecurso = "SELECT * FROM mdl_course where mdl_course.id=547 order by mdl_course.id";
				//BAMBALINAS $quecurso = "SELECT * FROM mdl_course where mdl_course.id=555 order by mdl_course.id";

				//$rescurso = mysql_query($quecurso, $conexion) or die(mysql_error());
				$rescurso = $DB->get_records_sql($quecurso);
				//while($rowcurso = mysql_fetch_assoc($rescurso)){
				foreach ($rescurso as $rowcurso) {
					//$curso=$rowcurso['id'];
					$curso=$rowcurso->id;
					//$nombrecurso= $rowcurso['fullname'];
					$nombrecurso = $rowcurso->fullname;

					echo "<br>$curso - $nombrecurso<br>";
					//$time_start = microtime(true);

					$mail->Subject = "Mensaje con los datos de seguimiento del curso: ".utf8_decode($nombrecurso);

					$sql="SELECT DISTINCT mdl_role_assignments.userid, mdl_user.firstname, mdl_user.lastname,mdl_user.email FROM mdl_user right join mdl_role_assignments  on mdl_user.id=mdl_role_assignments.userid
					left join mdl_context on mdl_role_assignments.contextid=mdl_context.id where mdl_context.contextlevel=50
					and mdl_context.instanceid=$curso and mdl_role_assignments.roleid = 5"; //Estudiante
					//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
					$sqlinsert = $DB->get_records_sql($sql);
					//$sqlresp = mysql_num_rows($sqlinsert);
					$sqlresp = count($sqlinsert);
					$ii=$sqlresp;

					// Consultamos los usuarios
					//while($rowsql = mysql_fetch_assoc($sqlinsert)){
					foreach ($sqlinsert as $rowsql) {


						$cuerpo="";
						$cuerpo1="";

						//$cuerpo1.="<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head> <strong>Estimad@ ".utf8_decode($rowsql['firstname']).": </strong>";
						$cuerpo1.="<head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head>Estimad@ ".utf8_decode($rowsql->firstname).":";
						$cuerpo.=" <div style='background-color:#6491ba; border:solid #6491ba 1px;color:#ffffff; width:90%;'><div style='padding-left:10px;padding-top:10px;padding-bottom:10px;'><u><b>Estos son sus datos de seguimiento:</b></u></div></div><div style='background-color:#ffffff; border:solid #c3d7f0 1px;color:#1e3f71; width:90%;'><div style='padding-left:10px;padding-top:10px;padding-bottom:10px;'>";

						//$usuario=$rowsql['userid']; //borrar $aux
						$usuario=$rowsql->userid;


						// N� numero de conexiones
						if($_POST['cc']=="1"){
							//20151222$queEmp5 = "SELECT * From mdl_log WHERE mdl_log.module = 'user' and mdl_log.action='login' AND mdl_log.userid=$aux ";
							//$queEmp5 = "SELECT * FROM mdl_logstore_standard_log WHERE action='loggedin' AND userid=$usuario";
							$queEmp5 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE action='loggedin' AND userid=$usuario";

							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							//$total= mysql_num_rows($resEmp5);
							$total = current($DB->get_records_sql($queEmp5))->num;
							$cuerpo.="<strong> N&uacute;mero de conexiones realizadas: </strong>".$total."<br>";
						}
						//aqui claculamos el numero de veces que ha escrito en el foro se llama total
						if($_POST['cf']=="1"){
							$cuerpo.="<strong>N&uacute;mero de veces que ha escrito en el foro: </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//saber los recursos de cada seccion
							//$queEmp5 = "SELECT * From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);
							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach ($resEmp5 as $rowsql5) {
								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;

								//$queEmp6 = "SELECT * From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								$queEmp6 = "SELECT id,module,instance From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								$resEmp6 = $DB->get_records_sql($queEmp6);

								//while($rowsql6 = mysql_fetch_assoc($resEmp6)){
								foreach ($resEmp6 as $rowsql6) {

									//$id_modulo=$rowsql6['id'];
									$id_modulo=$rowsql6->id;
									//$tipom=$rowsql6['module'];
									$tipom=$rowsql6->module;
									//$instance=$rowsql6['instance'];
									$instance=$rowsql6->instance;

									switch ($tipom) {
										case 10:
										//20151222
										//Es un foro //numero de visitas
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_foro=  mysql_num_rows($resEmp7);
										$visto_foro = current($DB->get_records_sql($queEmp7))->num;

										//numero de post
										//$queEmp8 = "SELECT * From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
										$queEmp8 = "SELECT count(message) as num From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_foro=  mysql_num_rows($resEmp8);
										$escrito_foro = current($DB->get_records_sql($queEmp8))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_forum WHERE id=$instance";
										$qu1 = "SELECT name  FROM mdl_forum WHERE id=$instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$cuerpo.=utf8_decode($rowsqlr['name'])."-> ".$visto_foro." visitas y ".$escrito_foro." post escritos.<br>";
										$cuerpo.=utf8_decode(current($rowsqlr)->name)."-> ".$visto_foro." visitas y ".$escrito_foro." post escritos.<br>";

										break;
									}
								}
							}

							$cuerpo.="</div><br>";
						}

						//numero de visitas al curso
						if($_POST['cv']=="1"){
							//20151222
							//$queEmp5 = "SELECT * FROM mdl_logstore_standard_log WHERE target='course' AND action='viewed' AND userid=$usuario AND courseid = $curso ";
							$queEmp5 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE target='course' AND action='viewed' AND userid=$usuario AND courseid = $curso ";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							//$totEmp5 = mysql_num_rows($resEmp5);
							$totEmp5 = current($DB->get_records_sql($queEmp5))->num;

							$cuerpo.="<strong> N&uacute;mero de visitas al curso: </strong>".$totEmp5."<br>";
						}

						//numero de conexiones por modulo
						if($_POST['cm']=="1"){
							$cuerpo.="<strong>N&uacute;mero de veces que ha visto cada m&oacute;dulo: </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//$queEmp5 = "SELECT * From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1 and mdl_course_sections.sequence!=''";
							$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1 and mdl_course_sections.sequence!=''";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);

							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach ($resEmp5 as $rowsql5) {
								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;
								//$queEmp6 = "Select * from mdl_log where mdl_log.action='view' and mdl_log.userid=$usuario and mdl_log.cmid in(SELECT id From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1) ";
								//$queEmp6 = "SELECT * FROM mdl_logstore_standard_log WHERE action='viewed' AND userid=$usuario AND contextinstanceid in (SELECT id From mdl_course_modules WHERE course=$curso and section=$seccion and mdl_course_modules.visible=1)";
								$queEmp6 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE action='viewed' AND userid=$usuario AND contextinstanceid in (SELECT id From mdl_course_modules WHERE course=$curso and section=$seccion and mdl_course_modules.visible=1)";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								//$totEmp6 = mysql_num_rows($resEmp6);
								$totEmp6 = current($DB->get_records_sql($queEmp6))->num;

								$cuerpo.= "N&uacute;mero de visitas al tema ".$section.": ".$totEmp6."<br>";
							}

							$cuerpo.="</div><br>";

						}


						//Ponemos las evaluaciones que ha hecho
						if($_POST['ct']=="1"){
							$cuerpo.=" <strong>Test realizados : </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//$queEmp5 = "SELECT * FROM mdl_course_sections WHERE course=$curso and visible=1";
							$queEmp5 = "SELECT id,section FROM mdl_course_sections WHERE course=$curso and visible=1";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);

							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach ($resEmp5 as $rowsql5) {
								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;
								//$queEmp6 = "SELECT * FROM mdl_course_modules WHERE course=$curso and section=$seccion and mdl_course_modules.visible=1 ";
								$queEmp6 = "SELECT id,module,instance FROM mdl_course_modules WHERE course=$curso and section=$seccion and mdl_course_modules.visible=1 ";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								$resEmp6 = $DB->get_records_sql($queEmp6);

								//while($rowsql6 = mysql_fetch_assoc($resEmp6)){
								foreach ($resEmp6 as $rowsql6) {
									//$id_modulo=$rowsql6['id'];
									$id_modulo=$rowsql6->id;
									//$tipom=$rowsql6['module'];
									$tipom=$rowsql6->module;
									//$instance=$rowsql6['instance'];
									$instance=$rowsql6->instance;
									switch ($tipom) {
										case 6:
										//20160107
										//las consultas son mdl_choice
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'choice' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_choice' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_consulta=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario";
										$queEmp8 = "SELECT count(mdl_choice_answers.id) as num From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_consulta=  mysql_num_rows($resEmp8);
										$escrito_consulta = current($DB->get_records_sql($queEmp8))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_choice WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_choice WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($escrito_consulta>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										break;

										case 19:
										//20160107
										//hay q poner mdl_quiz con veces entrado, resultado, intentos
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'quiz' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_quiz' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_cuestionario=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_quiz_grades.grade as grade From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$quiz=mysql_num_rows($resEmp8);
										$nota_cuestionario=0;

										//if(mysql_num_rows($resEmp8)){
										if(count($resEmp8)){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_cuestionario=$rowsql8['grade'];
											$nota_cuestionario = current($resEmp8)->grade;
											$nota_cuestionario = number_format($nota_cuestionario,2);
										}

										//numero de intentos
										//$queEmp8 = "SELECT * From mdl_quiz_attempts inner join mdl_quiz on mdl_quiz_attempts.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_attempts.userid=$usuario and mdl_quiz_attempts.quiz=$instance ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$intentos = mysql_num_rows($resEmp8);

										//nombre del foro
										//$qu1 = "SELECT * FROM mdl_quiz WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_quiz WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($quiz>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])." con nota:".$nota_cuestionario."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)." con nota:".$nota_cuestionario."<br>";
										}

										break;

										case 23:
										//m�dulo survey
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'survey' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_survey' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_encuesta= mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario  ";
										$queEmp8 = "SELECT count(mdl_survery_answers.id) as num From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario  ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_encuesta = mysql_num_rows($resEmp8);
										$escrito_encuesta = current($DB->get_records_sql($queEmp8))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_survey WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_survey WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($escrito_encuesta>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										break;
									}
								}
							}
							$cuerpo.="</div>";
						}

						//Evaluaciones que le faltan
						if($_POST['ctf']=="1"){
							$cuerpo.=" <strong>Test que faltan por realizar : </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//$queEmp5 = "SELECT * From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);

							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach ($resEmp5 as $rowsql5) {

								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;

								//$queEmp6 = "SELECT * From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								$queEmp6 = "SELECT id,module,instance From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								$resEmp6 = $DB->get_records_sql($queEmp6);

								//while($rowsql6 = mysql_fetch_assoc($resEmp6)){
								foreach ($resEmp6 as $rowsql6) {

									//$id_modulo=$rowsql6['id'];
									$id_modulo=$rowsql6->id;
									//$tipom=$rowsql6['module'];
									$tipom=$rowsql6->module;
									//$instance=$rowsql6['instance'];
									$instance=$rowsql6->instance;

									switch ($tipom) {
										case 6:
										//20160107
										//las consultas son mdl_choice
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'choice' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_choice' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_consulta=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_choice_answers.id) as num From mdl_choice_answers inner join mdl_choice on mdl_choice_answers.choiceid=mdl_choice.id WHERE mdl_choice.id = $instance and  mdl_choice_answers.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_consulta=  mysql_num_rows($resEmp8);
										$escrito_consulta = current($DB->get_records_sql($queEmp8))->num;

										//nombre de la consulta
										//$qu1 = "SELECT *  FROM mdl_choice WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_choice WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($escrito_consulta==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										break;

										case 19:
										//20160107
										//hay q poner mdl_quiz con veces entrado, resultado, intentos
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'quiz' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_quiz' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_cuestionario=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_quiz_grades.grade as grade From mdl_quiz_grades inner join mdl_quiz on mdl_quiz_grades.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$quiz=mysql_num_rows($resEmp8);
										$quiz=count($resEmp8);


										//if(mysql_num_rows($resEmp8)){
										if($quiz){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_cuestionario=$rowsql8['grade'];
											$nota_cuestionario=$rowsql8->grade;
										}
										//numero de intentos
										//$queEmp8 = "SELECT * From mdl_quiz_attempts  inner join mdl_quiz on mdl_quiz_attempts.quiz=mdl_quiz.id WHERE mdl_quiz.id = $instance and  mdl_quiz_attempts.userid=$usuario and mdl_quiz_attempts.quiz=$instance ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$intentos = mysql_num_rows($resEmp8);

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_quiz WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_quiz WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($quiz==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										break;

										case 23:
										//20160107
										//m�dulo survey
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'survey' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_survey' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_encuesta= mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario  ";
										$queEmp8 = "SELECT count(mdl_surver_answers.id) as num From mdl_survey_answers  inner join mdl_survey on mdl_survey_answers.survey=mdl_survey.id WHERE mdl_survey.id = $instance and  mdl_survey_answers.userid=$usuario  ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_encuesta = mysql_num_rows($resEmp8);
										$escrito_encuesta = current($DB->get_records_sql($queEmp8));

										//nombre
										//$qu1 = "SELECT *  FROM mdl_survey WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_survey WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if ($escrito_encuesta==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										break;
									}
								}
							}
							$cuerpo.="</div>";
						}



						//Actividades que han realizado
						if($_POST['ca']=="1"){
							$cuerpo.=" <strong>Actividades o recursos que has revisado: </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//$queEmp5 = "SELECT * From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);

							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach ($resEmp5 as $rowsql5) {

								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;

								//$queEmp6 = "SELECT * From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								$queEmp6 = "SELECT id,module,instance From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								$resEmp6 = $DB->get_records_sql($queEmp6);

								//while($rowsql6 = mysql_fetch_assoc($resEmp6)){
								foreach ($resEmp6 as $rowsql6) {

									//$id_modulo=$rowsql6['id'];
									$id_modulo=$rowsql6->id;
									//$tipom=$rowsql6['module'];
									$tipom=$rowsql6->module;
									//$instance=$rowsql6['instance'];
									$instance=$rowsql6->instance;
									switch ($tipom) {

										case 10:
										//Es un foro //numero de visitas
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_foro=  mysql_num_rows($resEmp7);

										//numero de post
										//$queEmp8 = "SELECT * From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_forum_posts.id) as num From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and mdl_forum_posts.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_foro=  mysql_num_rows($resEmp8);
										$escrito_foro = current($DB->get_records_sql($queEmp8))->num;
										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_forum WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_forum WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$string=utf8_decode($rowsqlr['name']);
										$string=utf8_decode(current($rowsqlr)->name);
										if($escrito_foro){

											if(stristr($string, 'preguntas') == FALSE) {
												//no contiene esa palabra probamos con las dem�s
												if(stristr($string, 'noticias') == FALSE) {
													if(stristr($string, 'Novedades') == FALSE) {
														//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
														$cuerpo.= utf8_decode(current($rowsqlr)->name)."---1<br>";
													}
												}
											}

										}
										//$columna=$columna +1;

										break;
										case 7:
										//20160107
										//Las bases de datos son msl_data data_records es el numero de veces que ha escrito registros
										//numero de visitas
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'data' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_data' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_base=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_data_records.id) as num From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_base=  mysql_num_rows($resEmp8);
										$escrito_base = current($DB->get_records_sql($queEmp8))->num;
										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_data WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_data WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_base){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---2<br>";
										}
										//$columna=$columna +1;

										break;

										case 16:
										//20160107
										//m�dulo herramienta externa mdl_lti_submission
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'lti' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_lti' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_herramienta = mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_lti_submission.id) as num From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_herramienta = mysql_num_rows($resEmp8);
										$escrito_herramienta = current($DB->get_records_sql($queEmp8))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_lti WHERE id = $instance";
										$qu1 = "SELECT name  FROM mdl_lti WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_herramienta){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---3<br>";
										}
										//$columna=$columna +1;
										break;

										case 11:
										//20160107
										//glosario
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'glossary' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_glosario = mysql_num_rows($resEmp7);
										$visto_glosario = current($DB->get_records_sql($queEmp7))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_glossary WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_glossary WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$columna=$columna +1;

										if($visto_glosario){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---4<br>";
										}

										break;
										case 14:
										//20160108
										//m�dulo mdl_lesson
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'lesson' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_lesson' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_leccion = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT * From mdl_lesson_attempts inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
										$queEmp8 = "SELECT count(mdl_lesson.id) as num From mdl_lesson_attempts inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_leccion = mysql_num_rows($resEmp8);
										$escrito_leccion = current($DB->get_records_sql($queEmp8))->num;

										//numero de envios
										//$queEmp8 = "SELECT mdl_lesson_grades.grade From mdl_lesson_grades inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_lesson_grades.grade as grade From mdl_lesson_grades inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										$nota_leccion=0;

										//if( mysql_num_rows($resEmp8)){
										if( count($resEmp8)){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_leccion=$rowsql8['grade'];
											$nota_leccion=current($resEmp8)->grade;
											$nota_leccion = number_format($nota_leccion,2);
										}

										//lecci�n
										//$qu1 = "SELECT * FROM mdl_lesson WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_lesson WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$columna=$columna +1;
										if($nota_leccion>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])." con nota:".$nota_leccion."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)." con nota:".$nota_leccion."---5<br>";
										}
										break;

										case 26:
										//20160108
										//taller workshop
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'workshop' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_workshop' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_taller = mysql_num_rows($resEmp7);

										//TODO revisar, tablas vacías
										//numero de envios
										$queEmp8 = "SELECT * From mdl_workshop_submissions inner join mdl_workshop on mdl_workshop_submissions.workshopid=mdl_workshop.id WHERE mdl_workshop.id = $instance and  mdl_workshop_submissions.authorid=$usuario ";
										echo $queEmp8;
										$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$escrito_taller = mysql_num_rows($resEmp8);
										$nota_taller=0;

										if ($escrito_taller){
											$rowsql8 = mysql_fetch_assoc($resEmp8);
											$nota_taller=$rowsql8['grade'];
										}

										//nombre
										$qu1 = "SELECT * FROM mdl_workshop WHERE id = $instance";
										$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										$rowsqlr = mysql_fetch_assoc($re1);

										//$columna=$columna +1;

										if($escrito_taller>0){
											$cuerpo.= utf8_decode($rowsqlr['name'])." con nota:".$nota_taller."---6<br>";
										}
										break;
										case 2:
										//20160108
										//Esta opcion ya no se usa
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'assignment' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_tarea = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT * From mdl_assignment_submissions  inner join mdl_assignment on mdl_assignment_submissions.assignment=mdl_assignment.id WHERE mdl_assignment.id = $instance and  mdl_assignment_submissions.userid=$usuario and mdl_assignment_submissions.assignment=$instance ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_tarea = mysql_num_rows($resEmp8);
										//$nota_tarea=0;

										//if ($escrito_tarea){
										//	$rowsql8 = mysql_fetch_assoc($resEmp8);
										//	$nota_tarea=$rowsql8['grade'];
										//
										//}
										//tarea mdl assign
										//nombre del foro
										//$qu1 = "SELECT *  FROM `mdl_assignment` WHERE `id` = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);

										//if($escrito_tarea>0){
										//	$cuerpo.= utf8_decode($rowsqlr['name']). " con nota:".$nota_tarea."<br>";
										//
										//}
										//$columna=$columna +1;
										break;
										case 1:
										//20160108
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'assign' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario  ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_assign' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_tarea = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT mdl_assign_grades.grade From mdl_assign_grades  inner join mdl_assign on mdl_assign_grades.assignment=mdl_assign.id WHERE mdl_assign_grades.grade > 0 AND mdl_assign.id = $instance and  mdl_assign_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_assign_grades.grade as grade From mdl_assign_grades  inner join mdl_assign on mdl_assign_grades.assignment=mdl_assign.id WHERE mdl_assign_grades.grade > 0 AND mdl_assign.id = $instance and  mdl_assign_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$escrito_tarea = mysql_num_rows($resEmp8);
										$escrito_tarea = count($resEmp8);
										$nota_tarea=0;

										if ($escrito_tarea){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_tarea=$rowsql8['grade'];
											$nota_tarea=current($resEmp8)->grade;
										}
										//tarea mdl assign
										//nombre
										//$qu1 = "SELECT * FROM mdl_assign WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_assign WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_tarea>0){
											//$cuerpo.= utf8_decode($rowsqlr['name']). " con nota:".$nota_tarea."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name). " con nota:".$nota_tarea."<br>";
										}
										//$columna=$columna +1;

										break;
										case 25:
										//wiki
										//20160108
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'wiki' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_wiki' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_wiki = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT * FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
										$queEmp8 = "SELECT count(mdl_wiki_pages.id) as num FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_wiki = mysql_num_rows($resEmp8);
										$escrito_wiki = current($DB->get_records_sql($queEmp8))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_wiki WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_wiki WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_wiki>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---7<br>";
										}
										//$columna=$columna +1;

										break;
										case 20:
										//20160108
										//archivo externo
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'resource' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_resource' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_resource' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_recurso = mysql_num_rows($resEmp7);
										$visto_recurso = current($DB->get_records_sql($queEmp7))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_resource WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_resource WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_recurso>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---8<br>";
										}
										//$columna=$columna +1;
										break;

										case 21:
										//20160108
										//SCORM
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'scorm' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_scorm' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_scorm = mysql_num_rows($resEmp7);

										//nombre del paquete
										//$queEmp8 = "SELECT title,mdl_scorm_scoes.id  FROM mdl_scorm_scoes left join mdl_scorm_scoes_track  on mdl_scorm_scoes_track.scoid=mdl_scorm_scoes.id WHERE  mdl_scorm_scoes_track.userid=$usuario and mdl_scorm_scoes.scorm=$instance and element='cmi.core.lesson_status' and value='completed' order by mdl_scorm_scoes.id DESC";
										$queEmp8 = "SELECT title,mdl_scorm_scoes.id as id  FROM mdl_scorm_scoes left join mdl_scorm_scoes_track  on mdl_scorm_scoes_track.scoid=mdl_scorm_scoes.id WHERE  mdl_scorm_scoes_track.userid=$usuario and mdl_scorm_scoes.scorm=$instance and element='cmi.core.lesson_status' and value='completed' order by mdl_scorm_scoes.id DESC";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$escrito_SCORM = mysql_num_rows($resEmp8);
										$escrito_SCORM = count($resEmp9);

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

										$queEmp9 = "SELECT MAX(id) as id FROM mdl_scorm_scoes WHERE mdl_scorm_scoes.scorm=$instance ";
										//$resEmp9 = mysql_query($queEmp9, $conexion) or die(mysql_error());
										$resEmp9 = $DB->get_records_sql($queEmp9);

										//if (mysql_num_rows($resEmp9)>0){
										if (count($resEmp9)>0){
											//$rowsql9 = mysql_fetch_assoc($resEmp9);
											//$nmax=$rowsql9['id'];
											$nmax = current($resEmp9)->id;
										}else{
											$nmax=0;
										}

										if ($numero==$nmax){
											$pag_SCORM2="Completado";
										}
										else{
											$pag_SCORM2="Incompleto";
										}

										//$qu1 = "SELECT * FROM mdl_scorm WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_scorm WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if(stristr($pag_SCORM2, 'Incompleto') == FALSE){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										//$columna=$columna +1;
										break;
										case 3:
										//20160108
										//libro
										//glosario
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'book' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_libro = mysql_num_rows($resEmp7);
										$visto_libro = current($DB->get_records_sql($queEmp7))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_book WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_book WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_libro>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---9<br>";
										}

										//$columna=$columna +1;

										break;
										case 18:
										//20160108
										//pagina
										//glosario
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'page' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_pagina = mysql_num_rows($resEmp7);
										$visto_pagina = current($DB->get_records_sql($queEmp7))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_page WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_page WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_pagina>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---10<br>";
										}
										//$columna=$columna +1;

										break;
										case 24:
										////20160108
										//url
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'url' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_url = mysql_num_rows($resEmp7);
										$visto_url = current($DB->get_records_sql($queEmp7))->num;

										//nombre
										//$qu1 = "SELECT * FROM mdl_url WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_url WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_url>0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."---11<br>";
										}
										//$columna=$columna +1;
										//glosario
										break;
									}

								}

							}
							$cuerpo.="</div>";
						}

						//Actividades que faltan por realizar

						if($_POST['caf']=="1"){
							$cuerpo.=" <strong>Actividades o recursos que te faltan por revisar: </strong><br>";
							$cuerpo.="<div style='margin-left:50px;'>";

							//$queEmp5 = "SELECT * From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							$queEmp5 = "SELECT id,section From mdl_course_sections WHERE mdl_course_sections.course=$curso and mdl_course_sections.visible=1";
							//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
							$resEmp5 = $DB->get_records_sql($queEmp5);

							//while($rowsql5 = mysql_fetch_assoc($resEmp5)){
							foreach($resEmp5 as $rowsql5){

								//$section=$rowsql5['section'];
								$section=$rowsql5->section;
								//$seccion=$rowsql5['id'];
								$seccion=$rowsql5->id;

								//$queEmp6 = "SELECT * From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								$queEmp6 = "SELECT id,module,instance From mdl_course_modules WHERE mdl_course_modules.course=$curso and mdl_course_modules.section=$seccion and mdl_course_modules.visible=1 ";
								//$resEmp6 = mysql_query($queEmp6, $conexion) or die(mysql_error());
								$resEmp6 = $DB->get_records_sql($queEmp6);

								//while($rowsql6 = mysql_fetch_assoc($resEmp6)){
								foreach($resEmp6 as $rowsql6){

									//$id_modulo=$rowsql6['id'];
									$id_modulo=$rowsql6->id;
									//$tipom=$rowsql6['module'];
									$tipom=$rowsql6->module;
									//$instance=$rowsql6['instance'];
									$instance=$rowsql6->instance;

									switch ($tipom) {
										case 10:
										//Es un foro
										//numero de visitas
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'forum' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_forum' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_foro=  mysql_num_rows($resEmp7);

										//numero de post
										//$queEmp8 = "SELECT * From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and  mdl_forum_posts.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_forum_posts.discussion) as num From mdl_forum_posts inner join mdl_forum_discussions on mdl_forum_posts.discussion=mdl_forum_discussions.id WHERE mdl_forum_discussions.forum = $instance and  mdl_forum_posts.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_foro=  mysql_num_rows($resEmp8);
										$escrito_foro = current($DB->get_records_sql($queEmp8))->num;
										//nombre del foro
										//$qu1 = "SELECT * FROM mdl_forum WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_forum WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$string=utf8_decode($rowsqlr['name']);
										$string=utf8_decode(current($rowsqlr)->name);
										if($escrito_foro==0){
											if(stristr($string, 'preguntas') == FALSE) {
												//no contiene esa palabra probamos con las dem�s
												if(stristr($string, 'noticias') == FALSE) {
													if(stristr($string, 'Novedades') == FALSE) {
														$cuerpo.= utf8_decode(current($rowsqlr)->name)."(Aportaciones)<br>";
													}
												}
											}
										}
										//$columna=$columna +1;

										break;
										case 7:
										//Las bases de datos son msl_data data_records es el numero de veces que ha escrito registros
										//numero de visitas
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'data' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_data' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_base=  mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_data_records.dataid) as num From mdl_data_records inner join mdl_data on mdl_data_records.dataid=mdl_data.id WHERE mdl_data.id = $instance and  mdl_data_records.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_base=  mysql_num_rows($resEmp8);
										$escrito_base = current($DB->get_records_sql($queEmp8))->num;

										//nombre del foro
										//$qu1 = "SELECT * FROM mdl_data WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_data WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_base==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;

										break;

										case 16:
										//m�dulo herramienta externa mdl_lti_submission
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'lti' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_lti' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_herramienta = mysql_num_rows($resEmp7);

										//numero de registros
										//$queEmp8 = "SELECT * From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
										$queEmp8 = "SELECT count(mdl_lti.id) as num From mdl_lti_submission  inner join mdl_lti on mdl_lti_submission.ltiid=mdl_lti.id WHERE mdl_lti.id = $instance and  mdl_lti_submission.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_herramienta = mysql_num_rows($resEmp8);
										$escrito_herramienta = current($DB->get_records_sql($queEmp8))->num;

										//nombre
										//$qu1 = "SELECT *  FROM `mdl_lti` WHERE `id` = $instance";
										$qu1 = "SELECT name FROM `mdl_lti` WHERE `id` = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_herramienta==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;
										break;

										case 11:
										//glosario
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'glossary' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_glossary' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_glosario = mysql_num_rows($resEmp7);
										$visto_glosario = current($DB->get_records_sql($queEmp7))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_glossary WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_glossary WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$columna=$columna +1;
										if($visto_glosario==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										break;

										case 14:
										//m�dulo mdl_lesson
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'lesson' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_lesson' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_leccion = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT * From mdl_lesson_attempts   inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
										$queEmp8 = "SELECT count(mdl_lesson.id) as id From mdl_lesson_attempts   inner join mdl_lesson on mdl_lesson_attempts.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_attempts.userid=$usuario";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_leccion = mysql_num_rows($resEmp8);
										$escrito_leccion = current($DB->get_records_sql($queEmp8))->num;

										//numero de envios
										//$queEmp8 = "SELECT mdl_lesson_grades.grade From mdl_lesson_grades  inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_lesson_grades.grade as grade From mdl_lesson_grades  inner join mdl_lesson on mdl_lesson_grades.lessonid=mdl_lesson.id WHERE mdl_lesson.id = $instance and  mdl_lesson_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);

										$nota_leccion = 0;
										//if( mysql_num_rows($resEmp8)){
										if( count($resEmp8)){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_leccion=$rowsql8['grade'];
											$nota_leccion=current($resEmp8)->grade;
										}


										//lecci�n
										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_lesson WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_lesson WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$columna=$columna +1;
										if($nota_leccion==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										break;

										case 26:
										//workshop
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'workshop' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_workshop' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_taller = mysql_num_rows($resEmp7);
										//TODO optimizar esta SQL si se sigue utilizando
										//numero de envios
										//$queEmp8 = "SELECT * From mdl_workshop_submissions inner join mdl_workshop on mdl_workshop_submissions.workshopid=mdl_workshop.id WHERE mdl_workshop.id = $instance and  mdl_workshop_submissions.authorid=$usuario ";
										$queEmp8 = "SELECT * From mdl_workshop_submissions inner join mdl_workshop on mdl_workshop_submissions.workshopid=mdl_workshop.id WHERE mdl_workshop.id = $instance and  mdl_workshop_submissions.authorid=$usuario ";

										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$escrito_taller = mysql_num_rows($resEmp8);
										$escrito_taller = count($resEmp8);

										if ($escrito_taller){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_taller=$rowsql8['grade'];
											$nota_taller=current($resEmp8)->grade;
										}
										//taller
										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_workshop WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_workshop WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										//$columna=$columna +1;

										if($escrito_taller==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										break;

										case 2:
										//NO SE USA
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'assignment' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario  ";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_tarea = mysql_num_rows($resEmp7);

										//numero de envios
										//$queEmp8 = "SELECT * From mdl_assignment_submissions  inner join mdl_assignment on mdl_assignment_submissions.assignment=mdl_assignment.id WHERE mdl_assignment.id = $instance and  mdl_assignment_submissions.userid=$usuario and mdl_assignment_submissions.assignment=$instance ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_tarea = mysql_num_rows($resEmp8);
										//$nota_tarea=0;
										//if ($escrito_tarea){
										//		$rowsql8 = mysql_fetch_assoc($resEmp8);
										//		$nota_tarea=$rowsql8['grade'];
										//}
										//tarea mdl assign
										//nombre del foro
										//$qu1 = "SELECT *  FROM `mdl_assignment` WHERE `id` = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										//if($escrito_tarea==0){
										//	$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
										//}
										//$columna=$columna +1;

										break;

										case 1:
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'assign' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario  ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_assign' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_tarea = mysql_num_rows($resEmp7);

										//SOLO SI ESCRIBE
										//numero de envios
										//$queEmp8 = "SELECT * From mdl_assign_grades  inner join mdl_assign on mdl_assign_grades.assignment=mdl_assign.id WHERE mdl_assign.id = $instance and  mdl_assign_grades.userid=$usuario ";
										$queEmp8 = "SELECT mdl_assign_grades.grade From mdl_assign_grades  inner join mdl_assign on mdl_assign_grades.assignment=mdl_assign.id WHERE mdl_assign.id = $instance and  mdl_assign_grades.userid=$usuario ";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$escrito_tarea = mysql_num_rows($resEmp8);
										$escrito_tarea = count($resEmp8);

										$nota_tarea=0;
										if ($escrito_tarea){
											//$rowsql8 = mysql_fetch_assoc($resEmp8);
											//$nota_tarea=$rowsql8['grade'];
											$nota_tarea=current($resEmp8)->grade;
										}

										//tarea mdl assign
										//nombre del foro
										//$qu1 = "SELECT * FROM mdl_assign WHERE id = $instance";
										$qu1 = "SELECT * FROM mdl_assign WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_tarea==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;

										break;

										case 25:
										//wiki
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'wiki' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_wiki' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_wiki = mysql_num_rows($resEmp7);

										//SI HA REALIZADO TRABAJO EN LA WIKI
										//$queEmp8 = "SELECT * FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
										$queEmp8 = "SELECT count(mdl_wiki_pages.id) as num FROM mdl_wiki_versions right join mdl_wiki_pages on mdl_wiki_versions.pageid=mdl_wiki_pages.id inner join mdl_wiki_subwikis on mdl_wiki_subwikis.id=mdl_wiki_pages.subwikiid inner join mdl_wiki on mdl_wiki_subwikis.wikiid=mdl_wiki.id   WHERE  mdl_wiki_versions.userid=$usuario and mdl_wiki.id= $instance";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										//$escrito_wiki = mysql_num_rows($resEmp8);
										$escrito_wiki = current($DB->get_records_sql($queEmp8))->num;

										//nombre del foro
										//$qu1 = "SELECT * FROM mdl_wiki WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_wiki WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($escrito_wiki==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;

										break;

										case 20:
										//archivo externo
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_resource' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_resource' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_recurso = mysql_num_rows($resEmp7);
										$visto_recurso = current($DB->get_records_sql($queEmp7))->num;

										//nombre
										//$qu1 = "SELECT *  FROM mdl_resource WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_resource WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_recurso==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;
										break;

										case 21:
										//SCORM
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'scorm' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_scorm' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_scorm = mysql_num_rows($resEmp7);

										//nombre
										$queEmp8 = "SELECT title,mdl_scorm_scoes.id  FROM `mdl_scorm_scoes`  left join mdl_scorm_scoes_track  on mdl_scorm_scoes_track.scoid=mdl_scorm_scoes.id WHERE  mdl_scorm_scoes_track.userid=$usuario and mdl_scorm_scoes.scorm=$instance and element='cmi.core.lesson_status' and value='completed' order by mdl_scorm_scoes.id DESC";
										//$resEmp8 = mysql_query($queEmp8, $conexion) or die(mysql_error());
										$resEmp8 = $DB->get_records_sql($queEmp8);
										//$escrito_SCORM = mysql_num_rows($resEmp8);
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

										//$qu1 = "SELECT * FROM mdl_scorm WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_scorm WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if(stristr($pag_SCORM2, 'Completado') == FALSE){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										//$columna=$columna +1;
										break;

										case 3:
										//libro
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'book' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_book' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_libro = mysql_num_rows($resEmp7);
										$visto_libro = current($DB->get_records_sql($queEmp7))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_book WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_book WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);


										if($visto_libro==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}

										//$columna=$columna +1;
										//glosario
										break;

										case 18:
										//pagina
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'page' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND objectid= $id_modulo";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_page' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";

										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_pagina = mysql_num_rows($resEmp7);
										$visto_pagina = current($DB->get_records_sql($queEmp7))->num;

										//nombre del foro
										//$qu1 = "SELECT *  FROM mdl_page WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_page WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);

										if($visto_pagina==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;
										//glosario
										break;

										case 24:
										//url
										//$queEmp7 = "SELECT * From mdl_log WHERE mdl_log.module = 'url' and mdl_log.cmid=$id_modulo AND mdl_log.userid=$usuario ";
										//$queEmp7 = "SELECT * FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										$queEmp7 = "SELECT count(mdl_logstore_standard_log.id) as num FROM mdl_logstore_standard_log WHERE component='mod_url' AND action='viewed' AND userid=$usuario AND contextinstanceid= $id_modulo";
										//$resEmp7 = mysql_query($queEmp7, $conexion) or die(mysql_error());
										//$visto_url = mysql_num_rows($resEmp7);
										$visto_url = current($DB->get_records_sql($queEmp7))->num;

										//nombre del url
										//$qu1 = "SELECT *  FROM mdl_url WHERE id = $instance";
										$qu1 = "SELECT name FROM mdl_url WHERE id = $instance";
										//$re1 = mysql_query($qu1, $conexion) or die(mysql_error());
										//$rowsqlr = mysql_fetch_assoc($re1);
										$rowsqlr = $DB->get_records_sql($qu1);


										if($visto_url==0){
											//$cuerpo.= utf8_decode($rowsqlr['name'])."<br>";
											$cuerpo.= utf8_decode(current($rowsqlr)->name)."<br>";
										}
										//$columna=$columna +1;
										//glosario
										break;
									}
								}
							}
							$cuerpo.="</div>";

						}

						$cuerpo.="</div></div>";
						//Añadimos sus datos de acceso
						$cuerpo.=" <br><br><div style='background-color:#6491ba; border:solid #6491ba 1px;color:#ffffff; width:90%;'><div style='padding-left:10px;padding-top:10px;padding-bottom:10px;'><u><b>Estos son sus datos de acceso:</b></u></div></div><div style='background-color:#ffffff; border:solid #c3d7f0 1px;color:#1e3f71; width:90%;'><div style='padding-left:10px;padding-top:10px;padding-bottom:10px;'>";

						//$queUserPass ="SELECT * FROM mdl_user, ppropio WHERE mdl_user.id = ppropio.idd AND mdl_user.id=$usuario";
						$queUserPass ="SELECT username,ppropio,email FROM mdl_user, ppropio WHERE mdl_user.id = ppropio.idd AND mdl_user.id=$usuario";
						//$resUserPass = mysql_query($queUserPass, $conexion) or die(mysql_error());
						$resUserPass = $DB->get_records_sql($queUserPass);
						//$totUserPass = mysql_num_rows($resUserPass);
						$totUserPass = count($resUserPass);
						//$rowUserPass = mysql_fetch_assoc($resUserPass);

						if ($totUserPass>0){
							//$cuerpo.="<strong>Usuario: </strong>".$rowUserPass['username']."<br>";
							$cuerpo.="<strong>Usuario: </strong>".current($resUserPass)->username."<br>";
							//$cuerpo.="<strong>Contrase&ntilde;a: </strong>".$rowUserPass['ppropio']."<br>";
							$cuerpo.="<strong>Contrase&ntilde;a: </strong>".current($resUserPass)->ppropio."<br>";
							//$cuerpo.="<strong>Email: </strong>".$rowUserPass['email']."<br><br>";
							$cuerpo.="<strong>Email: </strong>".current($resUserPass)->email."<br><br>";
						};


						$cuerpo.="</div></div>";

						//ENVIAMOS EL EMAIL

						$allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
						$allowedTags.='<li><ol><ul><span><div><br><ins><del><a><table><td><tr>';

						if (isset($_POST['editor1'])){
							if (!empty($_POST['editor1'])){
								$editor1=$_POST['editor1'];
							}
						}
						$cuerpo2 = strip_tags(stripslashes($editor1),$allowedTags);

						if (isset($_POST['editor2'])){
							if (!empty($_POST['editor2'])){
								$editor2=$_POST['editor2'];
							}
						}

						$cuerpo3 = strip_tags(stripslashes($editor2),$allowedTags);
						$cuerpo4 =$cuerpo1."<br><br>".$cuerpo2."<br><br>".$cuerpo."<br><br>".$cuerpo3;

						//Una vez que tenemos todo, limpiamos el objeto mail anterior
						$mail->clearAddresses(); //Limpiamos destinatarios
						$mail->clearAttachments(); //Limpiamos adjuntos



						//$add=$rowsql['email'];
						$add=$rowsql->email;
						$mail->AddAddress($add);
						//$mail->AddAddress('gcebollero@aragon.es');
						//$mail->AddBCC('gcebollero@itainnova.es');
						$body = "";
						$body.= $cuerpo4;
						$mail->Body = $body;
						$enviado = false;
						for($i = 0; $i < 2 && !$enviado; $i++){
							$enviado = $mail->Send();
						}
						$logentry = new stdClass();
						$logentry->source   = basename(__FILE__);
						$logentry->courseid = $curso;
						$logentry->logdate  = date('Y-m-d');
						$logentry->logtime  = date('H:i:s');
						if(!$enviado){
						 echo "<p>Error al enviar correo de ".$add."</p>";
						 echo '<p>'.$mail->ErrorInfo.'</p>';
						 echo "<br>";
						 $logentry->log      = 'ERROR '.$add;
					 }else{
						 $logentry->log      = $add;
					 }
					 	$lastinsertid = $DB->insert_record('itainnova_log', $logentry, false);
						set_time_limit(400000);

					}

					$sql="INSERT INTO itainnova_inf_envio_masivo(curso) VALUES (".$curso.")";
					//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
					$DB->execute($sql);
					//$time_end = microtime(true);
					//echo " >> ".round(($time_end - $time_start),2);
				}
				echo "<center><h2>Envio terminado.</h2></center>";
				//mysql_close($conexion); //cerramos la conexion con la base de datos
			}


		}else{
			//Si llega aquí, no es admin
			echo "<center><h1 style='color:red'>Usuario no autorizado</h1></br>";
			echo "<p>Contacte con su administrador del sitio para mas informaci&oacute;n.</p></center>";
		}
		echo $OUTPUT->footer();
		?>
