<?php
error_reporting(E_ALL);
global $DB;
require_once("../../config.php");
$PAGE->set_pagelayout('base');
$title = "Encuesta ".(date('Y')-1)." - ".date('Y')." Ciclo Modulo";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
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

require("../libreria/class.utilidades.php");
if(($_POST)){

	$id_encuesta=filter_var($_POST['id_encuesta'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	foreach($_POST as $nombre_campo => $valor){
		if (substr($nombre_campo,0,1)=='#'){
			$arraycampo=explode('-',$nombre_campo,5);
			$encuesta=filter_var(substr($arraycampo[0],1,FILTER_SANITIZE_FULL_SPECIAL_CHARS));
			$fasecampo=filter_var($arraycampo[1],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$ciclocampo=filter_var($arraycampo[2],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$modulocampo=filter_var($arraycampo[3],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$preguntacampo=filter_var($arraycampo[4],FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			//Evitamos que se inserten los datos si es la misma id_encuesta
			if (is_numeric($valor)){
				$valor=filter_var($valor,FILTER_SANITIZE_NUMBER_INT);
				$valor=intval($valor);
				if(!$DB->record_exists_sql($sql_check_exists,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>$fasecampo,'codigo1'=> $ciclocampo,'codigo2'=>$modulocampo,'codigo3' => $preguntacampo)))
				$DB->execute($sql_insert_values,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>$fasecampo,'codigo1'=>$ciclocampo,'codigo2'=>$modulocampo,'codigo3'=>$preguntacampo,'respuesta1'=>$valor,'respuesta2'=>"-1"));

			}else{
				$valor=filter_var($valor,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				$valor=substr($valor,0,1000);
				if(!$DB->record_exists_sql($sql_check_exists,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>$fasecampo,'codigo1'=> $ciclocampo,'codigo2'=>$modulocampo,'codigo3' => $preguntacampo)))
				$DB->execute($sql_insert_values,array('id_encuesta'=>$id_encuesta,'encuesta'=>$encuesta,'fase'=>$fasecampo,'codigo1'=>$ciclocampo,'codigo2'=>$modulocampo,'codigo3'=>$preguntacampo,'respuesta1'=>-2,'respuesta2'=>$valor));
			}
		}
	}
	//Guardamos la ip en todo momento
	$sql="INSERT INTO encuesta_datos (`id`,`id_encuesta`, `encuesta`,`fase`, `codigo 1`, `codigo 2`, `codigo 3`, `respuesta 1`, `respuesta 2`)
	VALUES (NULL,'".$id_encuesta."', '".$encuesta."','0','IP', '', '4', -1, '".getRealIP()."')";
	//$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());
	$DB->execute($sql);


	?>
	<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
	<center><h1 style="font-weight: bold;" >Encuesta enviada.</h1></center>
	<center>
		<h2>Muchas gracias por tu colaboraci&oacute;n.  Tus comentarios ser&aacute;n de gran  utilidad para mejorar la calidad de la formaci&oacute;n.</h2>
	</center>
	<hr SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;"/><br>
<?php
 }
  else {
	 ?>
	<meta HTTP-EQUIV="Refresh" CONTENT="3;URL=https://www.adistanciafparagon.es">
		<center><h1 style="font-size: 16pt;margin-top:5px;font-weight: bold;">NO EXISTE M&Oacute;DULO en breve ser&aacute; redireccionado a la p&aacute;gina principal</h1></center>
		<?php
	}
		echo $OUTPUT->footer();?>
