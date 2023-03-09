<?php
require_once('../config.php');
error_reporting(E_ALL);
/* pChart library inclusions */
require("./libreria/pChart/class/pData.class.php");
require("./libreria/pChart/class/pDraw.class.php");
require("./libreria/pChart/class/pImage.class.php");

require('./libreria/class.PDF/class.ezpdf.php');
require('./libreria/pclzip/pclzip.lib.php');
require('./libreria/class.cadena.php');

$PAGE->set_pagelayout('admin');
$title = 'INFORME PROFESORES';
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();

$categoria=$_GET["categoria"];
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
if(has_capability('moodle/site:config', $coursecontext)) {
	require_login();
	//Si ha llegado aquí, es admin
	set_time_limit(4000000);
	
	if($categoria==NULL || $categoria == 0){
		$sql = "SELECT id,name
		FROM mdl_course_categories
		WHERE parent <> 0
		AND parent <> 18
		ORDER BY id"; //Evitamos los centros, sala de profesores y ayuda (parent=0) y los laboratios moodle y cursos para docentes (parent = 18)
		$centros = $DB->get_records_sql($sql);
		?>
		<div style="width:90%;">
			<h2>Elige un centro:</h2>
			<form action="informe_profesores_pdf.php" style="width:70%;margin-left:5%" method="get">
				<select  id='categoria' style="width:100%" name='categoria' >
					<option value=0>Por favor selecciona el curso ...</option>
					<?php
					foreach ($centros as $centro) {
						echo "<option value='".$centro->id."'>".$centro->name."</option>";
					}
					?>
				</select><br>
				<center><button type="submit" class="btn btn-info btn-lg active">Siguiente</button></center>
			</form>
		</div>
		<?php
	}else{
		$table = new html_table();
		$table->head = array('Curso','Profesor','Abrir','Borrar');
		$informes=array();

		$ficherozip=$categoria.'_informe_profesores_pdf'.'_'.date("Y.n.j.G.i.s").'.zip';

		$archive = new PclZip($ficherozip);
		
		//Cursos
		//$quecurso = "SELECT * FROM mdl_course where mdl_course.id !=1";
		//$quecurso = "SELECT * FROM mdl_course WHERE category not in (0,18,26,27,38,52) AND category = $categoria AND shortname not like '%\_TU' order by mdl_course.id";
		$quecurso = "SELECT id,fullname,category FROM mdl_course WHERE category not in (0,18,26,27,38,52) AND category = $categoria AND shortname not like '%\_TU' order by mdl_course.id";
		//$quecurso = "SELECT * FROM mdl_course where mdl_course.id=547 order by mdl_course.id";

		//$rescurso = mysql_query($quecurso, $conexion) or die(mysql_error());
		$rescurso = $DB->get_records_sql($quecurso);
		//while($rowcurso = mysql_fetch_assoc($rescurso))
		foreach ($rescurso as $rowcurso)
		{
			//$curso=$rowcurso['id'];
			$curso = $rowcurso->id;
			//$nombre_curso= $rowcurso['fullname'];
			$nombre_curso = $rowcurso->fullname;
			//$categoria = $rowcurso['category'];
			$categoria = $rowcurso->category;
			//$queprof = "SELECT mdl_user.id, firstname, lastname
			//	FROM mdl_user right join mdl_role_assignments  on mdl_user.id=mdl_role_assignments.userid left join mdl_context on mdl_role_assignments.contextid=mdl_context.id
			//	WHERE mdl_context.contextlevel=50 and mdl_context.instanceid in ($curso,$category)  and mdl_role_assignments.roleid in (3,18) "; //Profesor del curso coordinador de la categoria

			//Profesor del curso UNION Coordinador de la categoria para las tutorias y las cohortes

			$queprof = "SELECT mdl_user.id, firstname, lastname
			FROM mdl_user RIGHT JOIN mdl_role_assignments ON mdl_user.id = mdl_role_assignments.userid LEFT JOIN mdl_context ON mdl_role_assignments.contextid = mdl_context.id
			WHERE mdl_context.contextlevel =50 AND mdl_context.instanceid =$curso AND mdl_role_assignments.roleid =3";
			//$resprof = mysql_query($queprof, $conexion) or die(mysql_error());
			$resprof = $DB->get_records_sql($queprof);
			//if (!mysql_num_rows($resprof))
			if(count($resprof)==0)
			{
				$queprof = "SELECT mdl_user.id, firstname, lastname
				FROM mdl_user RIGHT JOIN mdl_role_assignments ON mdl_user.id = mdl_role_assignments.userid LEFT JOIN mdl_context ON mdl_role_assignments.contextid = mdl_context.id
				WHERE mdl_context.contextlevel =40 AND mdl_context.instanceid =$categoria AND mdl_role_assignments.roleid =18";
				//$resprof = mysql_query($queprof, $conexion) or die(mysql_error());
				$resprof = $DB->get_records_sql($queprof);
			}

			//while($rowprof = mysql_fetch_assoc($resprof))
			foreach ($resprof as $rowprof)
			{
				//$usuario=$rowprof['id'];
				$usuario = $rowprof->id;
				//$nombre_prof=$rowprof['firstname']." ".$rowprof['lastname'];
				$nombre_prof=$rowprof->firstname." ".$rowprof->lastname;
				$nombre_prof=preg_replace('([^A-Za-z0-9])', '', $nombre_prof);
				//Comienzo desde septiembre
				if(date("m")<9){ //Si aun no hemos llegado a septiembre, restamos uno al año
					$tiempoi = gmmktime(0,0,0,9,1,date("y")-1);
				}else{
					$tiempoi = gmmktime(0,0,0,9,1,date("y"));
				}
				$tiempoaux = $tiempoi;

				$hoy= time();
				$dia=1;

				unset($array);
				unset($arraydatos);
				unset($data);

				while  ($tiempoaux < $hoy)
				{

					$array[]=date('d-m',$tiempoaux);
					//calculamos el valor de los clics hechos ese d�a
					//clics entre tiempoaux y tiempoaux + 1 d�a

					$tiempoaux1=$tiempoaux + 86400;

					//$sqlregistros = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND `course` = $curso  and time > ".$tiempoaux." and time < ".$tiempoaux1." ORDER BY `mdl_log`.`module` ASC";
					$sqlregistros = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso AND timecreated > ".$tiempoaux." AND timecreated < ".$tiempoaux1." ";

					//$sqlinsertregistros= mysql_query($sqlregistros, $conexion) or die(mysql_error());
					//$totalregistros=mysql_num_rows($sqlinsertregistros);
					$sqlinsertregistros = $DB->get_records_sql($sqlregistros);
					$totalregistros = count($sqlinsertregistros);
					//Guardamos en un array los datos
					$arraydatos[]=$totalregistros;
					$tiempoaux=$tiempoaux1;

				}


				/* Create and populate the pData object */
				$MyData = new pData();
				
				$MyData->loadPalette("./libreria/pChart/palettes/navy.color",TRUE);
				//$MyData->addPoints(array(150,220,300,250,420,200,300,200,100),"Server A");
				$MyData->addPoints($arraydatos,"Clicks");
				$MyData->setAxisName(0," ");
				$MyData->addPoints($array,"Months");
				$MyData->setSerieDescription("Months","Month");
				$MyData->setAbscissa("Months");

				/* Create the pChart object */
				$myPicture = new pImage(1400,460,$MyData);
				$myPicture->drawGradientArea(0,0,1400,460,DIRECTION_VERTICAL,array("StartR"=>480,"StartG"=>480,"StartB"=>480,"EndR"=>360,"EndG"=>360,"EndB"=>360,"Alpha"=>200));
				$myPicture->drawGradientArea(0,0,1400,460,DIRECTION_HORIZONTAL,array("StartR"=>480,"StartG"=>480,"StartB"=>480,"EndR"=>360,"EndG"=>360,"EndB"=>360,"Alpha"=>40));
				$myPicture->setFontProperties(array("FontName"=>"./libreria/pChart/fonts/calibri.ttf","FontSize"=>12));

				/* Draw the scale  */
				$myPicture->setGraphArea(100,60,1380,420);
				$myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10, "LabelSkip"=>15,"Mode"=>SCALE_MODE_START0));

				/* Turn on shadow computing */
				$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

				/* Draw the chart */
				$settings = array("Floating0Serie"=>"Floating 0","Draw0Line"=>TRUE,"Gradient"=>TRUE,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>FALSE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);
				$myPicture->drawBarChart($settings);

				/* Write the chart legend */
				$myPicture->drawLegend(1160,24,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

				/* Turn on Antialiasing */
				$myPicture->Antialias = TRUE;
				/* Render the picture (choose the best way) */
				$myPicture->Render("imagen.png");


				//Nombre del profesor y Curso
				//$sqlprof="SELECT * FROM mdl_user WHERE id=".$usuario;
				//$sqlinsertprof= mysql_query($sqlprof, $conexion) or die(mysql_error());
				//$rowsqlprof = mysql_fetch_assoc($sqlinsertprof);
				//$nombre_prof=$rowsqlprof['firstname']." ".$rowsqlprof['lastname'];


				//$sqlprof="SELECT * FROM mdl_course WHERE id=".$curso;
				//$sqlinsertprof= mysql_query($sqlprof, $conexion) or die(mysql_error());
				//$rowsqlprof = mysql_fetch_assoc($sqlinsertprof);
				//$nombre_curso=$rowsqlprof['fullname'];


				//Creamos el pdf con el contenido
				$pdf= new Cezpdf('A4');
				
				$pdf->selectFont('./libreria/Fonts/Helvetica.afm');
				$pdf->ezSetMargins(30, 30, 50, 30);
				$pdf->ezSetY(840);
				$img_graph = ImageCreatefrompng('imagen.png'); // aqui llamamos a la imagen generada por pChart
				$pdf->addImage($img_graph,75,500,450,300);
				$pdf->ezText(utf8_decode ($nombre_prof)." ".utf8_decode ($nombre_curso),16,array('justification'=>'center'));
				$pdf->ezText("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n",16);

				//aqui vamos a incluir el resto de datos del profesor en una tabla
				$titles = array('descripcion'=>utf8_decode('<b>Descripción</b>'), 'numero'=>utf8_decode('<b>Resultado</b>'));

				//####################################################################################################################

				//N�mero de conexiones de este profesor.
				//$sqldias="SELECT * from mdl_log where userid=".$usuario." and module='user' and action='login' and time > 1412121600 order by time desc";
				//$sqldias = "SELECT * FROM mdl_logstore_standard_log WHERE action='loggedin' AND userid=$usuario AND timecreated > ".$tiempoi." ORDER BY timecreated DESC";
				$sqldias = "SELECT count(id) as num FROM mdl_logstore_standard_log WHERE action='loggedin' AND userid=$usuario AND timecreated > ".$tiempoi." ORDER BY timecreated DESC";
				//$sqlinsertdias= mysql_query($sqldias, $conexion) or die(mysql_error());
				//$totaldias=mysql_num_rows($sqlinsertdias);
				$sqlinsertdias = $DB->get_records_sql($sqldias);
				//$sqlinsertdias = array_values($sqlinsertdias);
				$totaldias = current($sqlinsertdias)->num;


				$data[] = array('descripcion'=>utf8_decode("Número de conexiones"), 'numero'=>$totaldias);
				//Numero de d�as que se ha conectado el profesor
				//$queEmp5 = "SELECT DISTINCT(DATE(FROM_UNIXTIME(timecreated))) FROM mdl_logstore_standard_log WHERE userid =$usuario AND courseid =$curso GROUP BY DATE(FROM_UNIXTIME(timecreated))";
				$queEmp5 = "SELECT count(DISTINCT(DATE(FROM_UNIXTIME(timecreated)))) as num FROM mdl_logstore_standard_log WHERE userid =$usuario AND courseid =$curso GROUP BY DATE(FROM_UNIXTIME(timecreated))";
				//$resEmp5 = mysql_query($queEmp5, $conexion) or die(mysql_error());
				//$dias= mysql_num_rows($resEmp5);
				$resEmp5 = $DB->get_records_sql($queEmp5);
				$dias = current($resEmp5)->num;

				$data[] = array('descripcion'=>utf8_decode("Número de días que se ha conectado"), 'numero'=>$dias);

				//Numero de registros
				//$sqlregistros = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND `course` = $curso  and time > 1412121600 ORDER BY `mdl_log`.`module` ASC";
				//$sqlregistros = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso AND timecreated > ".$tiempoi." ";
				$sqlregistros = "SELECT count(id) as num FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso AND timecreated > ".$tiempoi." ";
				//$sqlinsertregistros= mysql_query($sqlregistros, $conexion) or die(mysql_error());
				//$totalregistros=mysql_num_rows($sqlinsertregistros);
				$sqlinsertregistros = $DB->get_records_sql($sqlregistros);
				$totalregistros  = current($sqlinsertregistros)->num;

				$data[] = array('descripcion'=>utf8_decode('Número de registros del curso:'), 'numero'=>$totalregistros);
				//Numero de mensajes enviados desde la mensajer�a de la plataforma

				//$sqlmensajes = "SELECT *  FROM mdl_message WHERE useridfrom = $usuario AND timecreated > ".$tiempoi." ";
				$sqlmensajes = "SELECT count(id) as num FROM mdl_message WHERE useridfrom = $usuario AND timecreated > ".$tiempoi." ";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);
				$sqlinsertmensajes = $DB->get_records_sql($sqlmensajes);
				$totalmensajes = current($sqlinsertmensajes)->num;

				$data[] = array('descripcion'=>utf8_decode('Número de mensajes enviados del curso:'), 'numero'=>$totalmensajes);

				//Numero de mensajes para un curso con jmail

				//$sqlmensajes = "SELECT *  FROM mdl_block_jmail WHERE sender = $usuario AND courseid=$curso AND timesent > ".$tiempoi." ";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);

				//$data[] = array('descripcion'=>'N�mero de mensajes enviados desde jmail::', 'numero'=>$totalmensajes);


				//Numero de correos recibidos
				//$sqlmensajes = "SELECT *  FROM mdl_message WHERE useridto = $usuario AND timecreated > ".$tiempoi." ";
				$sqlmensajes = "SELECT count(id) as num FROM mdl_message WHERE useridto = $usuario AND timecreated > ".$tiempoi." ";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);
				$totalmensajes = current($DB->get_records_sql($sqlmensajes))->num;

				$data[] = array('descripcion'=>utf8_decode('Número de mensajes recibidos:'), 'numero'=>$totalmensajes);
				//$sqlmensajes = "SELECT *  FROM `mdl_block_jmail_sent` WHERE `userid` = $usuario  ";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);

				//$data[] = array('descripcion'=>'N�mero de mensajes recibidos desde jmail:', 'numero'=>$totalmensajes);

				//Correo local_mail


				$sqllocalmail = "SELECT count(m.id) as num FROM mdl_local_mail_messages m INNER JOIN mdl_local_mail_message_users mu ON m.id = mu.messageid WHERE role = 'from' AND mu.userid= $usuario and m.courseid= $curso AND time > ".$tiempoi." ";
				//$sqllocalmail= mysql_query($sqllocalmail, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqllocalmail);
				$totalmensajes = current($DB->get_records_sql($sqllocalmail))->num;

				$data[] = array('descripcion'=>utf8_decode('Número de correos enviados atraves de la plataforma:'), 'numero'=>$totalmensajes);

				//$sqllocalmail = "SELECT * FROM mdl_local_mail_messages m INNER JOIN mdl_local_mail_message_users mu ON m.id = mu.messageid WHERE role = 'to' AND mu.userid= $usuario and m.courseid= $curso AND time > ".$tiempoi." ";
				$sqllocalmail = "SELECT count(m.id) as num FROM mdl_local_mail_messages m INNER JOIN mdl_local_mail_message_users mu ON m.id = mu.messageid WHERE role = 'to' AND mu.userid= $usuario and m.courseid= $curso AND time > ".$tiempoi." ";
				//$sqllocalmail= mysql_query($sqllocalmail, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqllocalmail);
				$totalmensajes = current($DB->get_records_sql($sqllocalmail))->num;

				$data[] = array('descripcion'=>utf8_decode('Número de correos recibidos atraves de la plataforma:'), 'numero'=>$totalmensajes);


				//Numero de mensajes en los foros

				//$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND course=$curso and module='forum' and (action='add discussion' or action='add post' ) AND time > 1412121600 ";
				//$sqlmensajes = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso AND component = 'mod_forum' AND action ='created' AND (target='post' OR target='discussion') AND timecreated > ".$tiempoi." ";
				$sqlmensajes = "SELECT count(id) as num FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid = $curso AND component = 'mod_forum' AND action ='created' AND (target='post' OR target='discussion') AND timecreated > ".$tiempoi." ";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);
				$totalmensajes = current($DB->get_records_sql($sqllocalmail))->num;

				$data[] = array('descripcion'=>utf8_decode('Número de post escrito en los foros del curso:'), 'numero'=>$totalmensajes);

				//Tiempos de conexi�n

				$actividad1=0;
				$actividad2=0;
				$tiempoc=0;

				//$sql2 = "SELECT *  FROM mdl_log WHERE userid = $usuario AND time > 1412121600  order by time DESC";
				//Esto no va, comento todo y empiezo de cero
				/*
				$sql2 = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND timecreated > ".$tiempoi." order by timecreated DESC";
				$sqlinsert2= mysql_query($sql2, $conexion) or die(mysql_error());

				while($rowsql2 = mysql_fetch_assoc($sqlinsert2))
				{

					if(($actividad1==0)and($actividad2==0))
					{

						$rowsql2 = mysql_fetch_assoc($sqlinsert2);
						$actividad1=$rowsql2['time'];
						$rowsql2 = mysql_fetch_assoc($sqlinsert2);
						$actividad2=$rowsql2['time'];
					}

					$segundos_diferencia=$actividad1-$actividad2;
					$horas_diferencia = $segundos_diferencia / (60*60);
					$horas_diferencia = abs($horas_diferencia);

					if( $horas_diferencia>1)
					{
						//calculamos el tiempo que ha pasado entre el ultimo login y el estado actual
						$tiempoc = $tiempoc / (60);
						$tiempoc=round($tiempoc, 2);

						$data[] = array('descripcion'=>'Tiempo de conexión en minutos :', 'numero'=>$tiempoc);
						$tiempoc=0;
					}
					else
					{
						$tiempoc=$tiempoc + $segundos_diferencia;
					}

					//consideremos si la siguiente actividad pertenece a esta conexi�n o a otra
					$actividad1=$actividad2;
					$actividad2=$rowsql2['time'];

				}
				*/
				$sqlTiempoConexion = "SELECT timecreated,action
				FROM `mdl_logstore_standard_log`
				WHERE (action = 'loggedin' OR action = 'loggedout') AND userid = $usuario AND timecreated >= $tiempoi
				ORDER BY timecreated ASC";
				$accesos = $DB->get_records_sql($sqlTiempoConexion);
				$loggedin = 0;
				$loggedout = 0;
				$tiempoc = 0;
				foreach ($accesos as $accion) {
					if($accion->action == 'loggedin')
						$loggedin =  $accion->timecreated;
					if($accion->action == 'loggedout' )
						$loggedout = $accion->timecreated;
					if($loggedout>$loggedin)
						$tiempoc+=($loggedout-$loggedin);
				}
				$data[] = array('descripcion'=>utf8_decode('Tiempo de conexión:'), 'numero'=>date('H:i:s', mktime(0, 0, $tiempoc)));

				//$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario   AND time > 1412121600  order by time DESC";
				//$sqlmensajes = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid=$curso AND action like '%update%'  AND timecreated > ".$tiempoi." order by timecreated DESC";
				$sqlmensajes = "SELECT count(id) as num FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid=$curso AND action='updated' AND component='core' AND target ='course_module'  AND timecreated > $tiempoi";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);
				$totalmensajes = current($DB->get_records_sql($sqlmensajes))->num;

				$data[] = array('descripcion'=>utf8_decode('Número actualizaciones de recursos que ha realizado :'), 'numero'=>$totalmensajes);

				//Subida de archivos
				//$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario  and action='add' and module='resource' AND course=$curso and time > 1412121600  order by time DESC";
				//$sqlmensajes = "SELECT * FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid=$curso AND component='core' AND action='created' AND target ='course_module' AND timecreated > ".$tiempoi." order by timecreated DESC";
				$sqlmensajes = "SELECT count(id) as num FROM mdl_logstore_standard_log WHERE userid = $usuario AND courseid=$curso AND component='core' AND action='created' AND target ='course_module' AND timecreated > $tiempoi";
				//$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
				//$totalmensajes=mysql_num_rows($sqlinsertmensajes);
				$totalmensajes = current($DB->get_records_sql($sqlmensajes))->num;

				$data[] = array('descripcion'=>utf8_decode('Número de subida de recursos que ha realizado :'), 'numero'=>$totalmensajes);

				//GEneramos las opciones de la tabla para plasmarla en el pdf
				$options = array(

					'shadeCol'=>array(0.9,0.9,0.9),
					'xOrientation'=>'center',
					'width'=>400,

				);
				$pdf->ezTable($data,$titles,'',$options );

				//#######################################

				$pdfcode = $pdf->ezOutput();
				
				$dir="./informe_profesores_pdf";
				$dir1 = "./informe_profesores_pdf/avempace";
				$dir2 = "./informe_profesores_pdf/bajo_aragon";
				$dir3 = "./informe_profesores_pdf/corona_aragon";
				$dir4 = "./informe_profesores_pdf/enlaces";
				$dir5 = "./informe_profesores_pdf/luis_bunnuel";
				$dir6 = "./informe_profesores_pdf/maria_moliner";
				$dir7 = "./informe_profesores_pdf/martinez_vargas";
				$dir8 = "./informe_profesores_pdf/miralbueno";
				$dir9 = "./informe_profesores_pdf/montearagon";
				$dir10 = "./informe_profesores_pdf/pablo_serrano";
				$dir11 = "./informe_profesores_pdf/piramide";
				$dir12 = "./informe_profesores_pdf/rio_gallego";
				$dir13 = "./informe_profesores_pdf/santa_emerenciana";
				$dir14 = "./informe_profesores_pdf/sierra_de_guara";
				$dir15 = "./informe_profesores_pdf/tiempos_modernos";
				$dir16 = "./informe_profesores_pdf/vega_del_turia";
				//Comprueba nombre y le coloca la direcci�n que le corresponda
				if(substr(strtolower($nombre_curso), 0, 5)== "iesav")
				{
					$fname = normaliza($dir1.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');


				}
				else if(substr(strtolower($nombre_curso), 0, 4)== "fpba")
				{
					$fname = normaliza($dir2.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 4)== "fpca")
				{
					$fname = normaliza($dir3.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 4)== "fpen")
				{
					$fname = normaliza($dir4.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "ieslb")
				{
					$fname = normaliza($dir5.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesmm")
				{
					$fname = normaliza($dir6.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesmv")
				{
					$fname = normaliza($dir7.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesmi")
				{
					$fname = normaliza($dir8.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesmo")
				{
					$fname = normaliza($dir9.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesps")
				{
					$fname = normaliza($dir10.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 4)== "fppi")
				{
					$fname = normaliza($dir11.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesrg")
				{
					$fname = normaliza($dir12.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesse")
				{
					$fname = normaliza($dir13.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iessg")
				{
					$fname = normaliza($dir14.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iestm")
				{
					$fname = normaliza($dir15.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else if(substr(strtolower($nombre_curso), 0, 5)== "iesvt")
				{
					$fname = normaliza($dir16.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
				else
				{
					$fname = normaliza($dir.'/'.$nombre_curso.'_'.$nombre_prof.'.pdf');
				}
			
				file_put_contents($fname, $pdfcode); //guardar en el server
		
				//$pdf->Output($fname,'F');
				
				//#####CPINA
				$fp = fopen($fname, 'a');
				if ($fp) {
					 fwrite($fp, $pdfcode);
					 fclose($fp);
					 echo "Creado<br>";
				} else {
					 echo "No Creado<br>";
				}
				//#####
				
				
				
				

				$fp = fopen($fname,'wb');
				fwrite($fp,$pdfcode);
				fclose($fp);
				
				echo $fname."<br>";

				$v_list = $archive->add($fname);
				if ($v_list == 0)
				{
					die("Error : ".$archive->errorInfo(true));
				}
				array_push($informes,
				array("$nombre_curso","$nombre_prof",
				"<a href='$fname' target='_blank'><i class='fa fa-external-link' aria-hidden='true'></i></a>",
				"<a href='borrar.php?path=$fname' target='_blank'><i class='fa fa-trash-o' aria-hidden='true'></i></a>"));
			}

		}
		?>
		<div style="width90%">
			<h2>Informe completado:</h2>
			<div style="width:90%;margin-left:5%">
				<?php
		$table->data = $informes;
		echo html_writer::table($table);
		?>
			</div>
		</div>
		<br/>
		<center><a class="btn btn-info btn-lg active"  role="button" href="<?=$ficherozip?>">Descargar como zip <i class="fa fa-download" aria-hidden="true"></i></a></center>
		<?php
	}
}else{
	//Si llega aquí, no es admin
	echo "<center><h1 style='color:red'>Usuario no autorizado</h1></br>";
	echo "<p>Contacte con su administrador del sitio para mas informaci&oacute;n.</p></center>";
}
echo $OUTPUT->footer();
?>
