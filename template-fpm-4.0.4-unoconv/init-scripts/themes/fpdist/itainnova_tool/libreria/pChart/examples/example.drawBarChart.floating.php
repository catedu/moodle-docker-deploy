<?php   
 /* CAT:Bar Chart */

 /* pChart library inclusions */
 include("../class/pData.class.php");
 include("../class/pDraw.class.php");
 include("../class/pImage.class.php");
 require_once('../../../config.php');
  include('../../class.ezpdf.php');
 
 $curso=$_GET["course"];
$usuario=$_GET["userid"];


$dbhost="localhost"; //host de la base de datos 
$dbusuario="teleform"; //usuario de la base de datos
$dbpassword="4Octubre25Junio"; //password de la base de datos
$db="teleform_moodle"; //nombre de la base de datos
$conexion = mysql_connect($dbhost, $dbusuario, $dbpassword); 
mysql_select_db($db, $conexion); 

$tiempoi = 1412121600;
$tiempoaux = 1412121600;
$hoy= time();
$dia=1;
unset($array);
while  ($tiempoaux < $hoy)
{




$array[]=date('d-m',$tiempoaux);



//calculamos el valor de los clics hecho ese día
//clics entre tiempoaux y tiempoaux + 1 día

	$tiempoaux1=$tiempoaux + 86400;
	$sqlregistros = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND `course` = $curso  and time > ".$tiempoaux." and time < ".$tiempoaux1." ORDER BY `mdl_log`.`module` ASC";
	$sqlinsertregistros= mysql_query($sqlregistros, $conexion) or die(mysql_error());
	//$rowsqlregistros = mysql_fetch_assoc($sqlinsertregistros);
	$totalregistros=mysql_num_rows($sqlinsertregistros);
	$arraydatos[]=$totalregistros;
	$tiempoaux=$tiempoaux1;




}


 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->loadPalette("../palettes/blind.color",TRUE);
 //$MyData->addPoints(array(150,220,300,250,420,200,300,200,100),"Server A");
 $MyData->addPoints($arraydatos,"Cliccs");
 $MyData->setAxisName(0,"Clics");
 $MyData->addPoints($array,"Months");
 $MyData->setSerieDescription("Months","Month");
 $MyData->setAbscissa("Months");

 /* Create the floating 0 data serie */
 //$MyData->addPoints(array(60,80,20,40,0,50,90,30,100),"Floating 0");
 //$MyData->setSerieDrawable("Floating 0",FALSE);

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Draw the scale  */
 $myPicture->setGraphArea(50,30,680,200);
 $myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10, "LabelSkip"=>8,"Mode"=>SCALE_MODE_START0));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the chart */
 $settings = array("Floating0Serie"=>"Floating 0","Draw0Line"=>TRUE,"Gradient"=>TRUE,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>FALSE,"DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255,"DisplayShadow"=>TRUE,"Surrounding"=>10);
 $myPicture->drawBarChart($settings);

 /* Write the chart legend */
 $myPicture->drawLegend(580,12,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->Render("imagen.png"); 
 
 
 $pdf= new Cezpdf('A4');
//$pdf->selectFont('fonts/Helvetica.afm');
$pdf->ezSetMargins(30, 30, 50, 30);
$pdf->ezSetY(810);
$img_graph = ImageCreatefrompng('imagen.png'); // aqui llamamos a la imagen generada por pChart
//$pdf->addImage($img_graph,75,500,450,300);


//aqui vamos a incluir el resto de datos del profesor en una tabla

		
		 
		$titles = array('descripcion'=>'<b>Descripción</b>', 'numero'=>'<b>Resultado</b>');
		 
		//$pdf->ezText("<b>Meses en PHP</b>\n",16);
		//$pdf->ezText("Listado de Meses\n",12);
		


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
//Número de conexiones de este profesor.

	$sqldias="SELECT * from mdl_log where userid=".$usuario." and module='user' and action='login' and time > 1412121600 order by time desc";
	$sqlinsertdias= mysql_query($sqldias, $conexion) or die(mysql_error());
	$totaldias=mysql_num_rows($sqlinsertdias);
	
	//$data[] = array('descripcion'=>'Número de conexiones', 'numero'=>$totaldias);
	 $pdf->ezText("<b>Número de conexiones:</b> ".$totaldias, 10); 
	
	
	
//Numero de días que se ha conectado el profesor

	$ahora = time(); 
	$dias=0;
	while($rowsqldias = mysql_fetch_assoc($sqlinsertdias)){
		
		//resto a una fecha la otra
			$segundos_diferencia = $ahora - $rowsqldias['time'];
			

		//convierto segundos en días
			$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
			
		//obtengo el valor absoulto de los días (quito el posible signo negativo)
			$dias_diferencia = abs($dias_diferencia);

		//quito los decimales a los días de diferencia
			$dias_diferencia = floor($dias_diferencia); 
			
			
		IF ($dias_diferencia > 0) 
		{$dias=$dias +1;
		
		}
		
		$ahora=$rowsqldias['time'];
	
	
	}
	

	//$data[] = array('descripcion'=>'Número de días que se ha conectado', 'numero'=>$dias);
	$pdf->ezText("<b>Número de días que se ha conectado:</b> ".$dias, 10); 

//Numero de registros

	$sqlregistros = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND `course` = $curso  and time > 1412121600 ORDER BY `mdl_log`.`module` ASC";
	$sqlinsertregistros= mysql_query($sqlregistros, $conexion) or die(mysql_error());
	//$rowsqlregistros = mysql_fetch_assoc($sqlinsertregistros);
	$totalregistros=mysql_num_rows($sqlinsertregistros);
	
	
	//$data[] = array('descripcion'=>'Número de registros del curso:', 'numero'=>$totalregistros);
	$pdf->ezText("<b>Número de registros del curso:</b> ".$totalregistros, 10); 
	
	
//Numero de mensajes enviados desde la mensajería de la plataforma

	$sqlmensajes = "SELECT *  FROM `mdl_message` WHERE `useridfrom` = $usuario AND timecreated > 1412121600 ";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	
	
	//$data[] = array('descripcion'=>'Número de mensajes enviados del curso:', 'numero'=>$totalmensajes);
	$pdf->ezText("<b>Número de mensajes enviados del curso:</b> ".$totalmensajes, 10); 
	
//Numero de mensajes para un curso con jmail

	$sqlmensajes = "SELECT *  FROM `mdl_block_jmail` WHERE `sender` = $usuario AND courseid=$curso AND timesent > 1412121600 ";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	

	//$data[] = array('descripcion'=>'Número de mensajes enviados desde jmail::', 'numero'=>$totalmensajes);
	$pdf->ezText("<b>Número de mensajes enviados desde jmail:</b> ".$totalmensajes, 10); 
	
//Numero de correos recibidos

	$sqlmensajes = "SELECT *  FROM `mdl_message` WHERE `useridto` = $usuario AND timecreated > 1412121600 ";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	
	
//$data[] = array('descripcion'=>'Número de mensajes recibidos:', 'numero'=>$totalmensajes);	
$pdf->ezText("<b>Número de mensajes recibidos:</b> ".$totalmensajes, 10); 
	
		$sqlmensajes = "SELECT *  FROM `mdl_block_jmail_sent` WHERE `userid` = $usuario  ";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
		
		
		//$data[] = array('descripcion'=>'Número de mensajes recibidos desde jmail:', 'numero'=>$totalmensajes);
		$pdf->ezText("<b>Número de mensajes recibidos desde jmail:</b> ".$totalmensajes, 10); 					

	
	
	
//Numero de mensajes en los foros

	$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND course=$curso and module='forum' and (action='add discussion' or action='add post' ) AND time > 1412121600 ";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	
	
	
	//$data[] = array('descripcion'=>'Número de post escrito en los foros del curso:', 'numero'=>$totalmensajes);	
	$pdf->ezText("<b>Número de post escrito en los foros del curso:</b> ".$totalmensajes, 10); 

//Tiempos de conexión


	
	$actividad1=0;
	$actividad2=0;
	$tiempoc=0;
	
		
		$sql2 = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario AND time > 1412121600  order by time DESC";
		$sqlinsert2= mysql_query($sql2, $conexion) or die(mysql_error());
		
	
	
		while($rowsql2 = mysql_fetch_assoc($sqlinsert2)){
		
			if(($actividad1==0)and($actividad2==0)){
			
				$rowsql2 = mysql_fetch_assoc($sqlinsert2);
				$actividad1=$rowsql2['time'];
				$rowsql2 = mysql_fetch_assoc($sqlinsert2);
				$actividad2=$rowsql2['time'];
			
			}
			
			$segundos_diferencia=$actividad1-$actividad2;
			$horas_diferencia = $segundos_diferencia / (60*60);
			$horas_diferencia = abs($horas_diferencia);
			
			
			if( $horas_diferencia>1) {
			
			//calculamos el tiempo que ha pasado entre el ultimo login y el estado actual
					$tiempoc = $tiempoc / (60);
					$tiempoc=round($tiempoc, 2);
						
						
							//$data[] = array('descripcion'=>'Tiempo de conexión en minutos :', 'numero'=>$tiempoc);	
							$pdf->ezText("<b>Tiempo de conexión en minutos :</b> ".$tiempoc, 10); 
					$tiempoc=0;
					

			
			}else {
			
			
			
			$tiempoc=$tiempoc + $segundos_diferencia;
			}
			
			//consideremos si la siguiente actividad pertenece a esta conexión o a otra
			
			$actividad1=$actividad2;
			$actividad2=$rowsql2['time'];
			
			
			
			
		
			
	
	
	
	
		}
	
	
//veces que el profesor ha realizado modificaciones

	$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario  and action like '%update%' AND time > 1412121600  order by time DESC";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	
	

								//$data[] = array('descripcion'=>'Número actualizaciones de recursos que ha realizado :', 'numero'=>$totalmensajes);
								$pdf->ezText("<b>Número actualizaciones de recursos que ha realizado :</b> ".$totalmensajes, 10); 								

	
//Subida de archivos

	$sqlmensajes = "SELECT *  FROM `mdl_log` WHERE `userid` = $usuario  and action='add' and module='resource' AND time > 1412121600  order by time DESC";
	$sqlinsertmensajes= mysql_query($sqlmensajes, $conexion) or die(mysql_error());
	//$rowsqlmensajes = mysql_fetch_assoc($sqlinsertmensajes);
	$totalmensajes=mysql_num_rows($sqlinsertmensajes);
	
	
	

	//$data[] = array('descripcion'=>'Número de subida de recursos que ha realizado :', 'numero'=>$totalmensajes);	
	$pdf->ezText("<b>Número de subida de recursos que ha realizado :</b> ".$totalmensajes, 10); 	


	//print_r ($data);	
	$options = array(

'shadeCol'=>array(0.9,0.9,0.9),
'xOrientation'=>'center',
'width'=>800,

); 
	//$pdf->ezTable($data,$titles,'',$options );	
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$pdf->ezStream();
?>