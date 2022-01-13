<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Encuesta 2016 Ciclo Modulo</title>
</head>
<body  bgcolor="#ffffff" text="#003300" link="#006060" vlink="#006060">
<center><div style="width:800px;color:#6491b9;">
<table>
<tr>
<td>
<img src="http://moodle.teleformacionfp.aragon.es/theme/aardvark_postitnote/pix/Gobierno.png" id="logo">
</td>
<td>
<center><img src="http://moodle.teleformacionfp.aragon.es/theme/aardvark_postitnote/pix/graphics/v2.jpg"  alt="Tamaño original" border="0"></center>
</td>
</tr>
</table>
<?php
 
print_r($_POST);

echo "UNO".$_POST["id_encuesta01"]."</br>";
echo "DOS".$_POST["id_encuesta02"]."</br>";

require("./libreria/BD.php");
$conexion = get_db_conn();

$sql="INSERT INTO `teleform_moodle`.`encuesta_itainnova_datos` (`id_encuesta01`,`id_encuesta02`,`valor01`, `valor02`, `valor03`, `valor04`, `valor05`, `valor06`, `valor07`, `valor08`, `valor09`, `valor10`, `valor11`, `valor12`) 
VALUES ('".$_POST["id_encuesta01"]."','".$_POST["id_encuesta01"]."', '".$_POST["valor01"]."', '".$_POST["valor02"]."', '".$_POST["valor03"]."', '".$_POST["valor04"]."', '".$_POST["valor05"]."', '".$_POST["valor06"]."', '".$_POST["valor07"]."', '".$_POST["valor08"]."', '".$_POST["valor09"]."', '".$_POST["valor10"]."', '".$_POST["valor11"]."', '".$_POST["valor12"]."')";

$sqlinsert= mysql_query($sql, $conexion) or die(mysql_error());



$sql="SELECT name FROM mdl_course_categories WHERE id ='".$_POST["id_encuesta02"]."'";
	
$sqlinsert= mysql_query($sql, $conexion);
	
if(!$sqlinsert) {
	die('Could not get data: ' . mysql_error());
}

while($row = mysql_fetch_array($sqlinsert)) {
	$nombreciclo=$row['name'];
}

?>
<HR SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;" ><br>
<center><h1 style="font-weight: bold;" >Ciclo: <?php echo $nombreciclo; ?></h1></center>
<HR SIZE=1 WIDTH="100%" COLOR="#c3d7f0" style="float:right; margin-left:30px;" ><br>

<form action="encuesta_2016_modulo_fin.php" method="post" name="form_encuesta">  
	<div style='margin-left:50px;'>
 
	<? // Creamos el formulario leyendolo de la BD.
	
	$sql="SELECT codigo,id,texto,tipo,nombre_columna FROM encuesta_itainnova WHERE codigo ='ciclo_modulo' ORDER BY id ASC";
	$sqlinsert= mysql_query($sql, $conexion);
	
	?>
	<div style="width:800px;">
	<?
	while($rowsql = mysql_fetch_assoc($sqlinsert)){
		if($rowsql['tipo']=="titulo"){
			echo "<div style=' color:#000000;margin-left:40px; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
		}
		
		if($rowsql['tipo']=="pregunta"){
			echo "<div style=' color:#000000;margin-left:40px; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
			echo "<center><table border='1'>";
			echo "<tr>
				<td style='color:#ffffff;background-color:#6491b9; width:500px;'>  </td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>0</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>1</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>2</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>3</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>4</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>5</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>6</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>7</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>8</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>9</b></td>
				<td style='color:#ffffff;background-color:#6491b9;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 'ALIGN='CENTER'><b>10</b></td>
			</tr>";
		}
		
		if($rowsql['tipo']=="respuesta_modulo"){
			$sql="SELECT fullname, shortname FROM mdl_course WHERE category='".$_POST["id_encuesta02"]."'";
			$sqlcurso= mysql_query($sql, $conexion);
			while($rowcurso = mysql_fetch_assoc($sqlcurso)){
				echo "<tr><td style='color:#000000; width:500px;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;padding-left:10px;'> <br>".$rowcurso['fullname']."</td>";
				for ($i = 0; $i < 11; $i++) {
					echo "<td style='color:#000000;ALIGN='center' '> <input  type='radio' name='".$rowsql['nombre_columna']."' id='".$rowsql['nombre_columna']."' value='".$rowsql['nombre_columna']."-"".$i."' >  </td>";
				}
				echo "</tr>";
			}
			echo "</table></center>";					
		}
		
		if($rowsql['tipo']=="respuesta"){
			echo"<tr><td style='color:#000000; width:500px;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;padding-left:10px;'> <br>".$rowsql['texto']."</td>";
			for ($i = 0; $i < 11; $i++) {
				echo "<td style='color:#000000;ALIGN='center' '> <input  type='radio' name='".$rowsql['nombre_columna']."' id='".$rowsql['nombre_columna']."' value='".$i."' >  </td>";
			}
			echo "</tr>";
		}
		
		if($rowsql['tipo']=="pregunta_fin"){
			echo "</table></center>";
		}
		
		if($rowsql['tipo']=="pregunta_texto"){
			echo "<div style='color:#000000;margin-left:40px; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;'><b>".$rowsql['texto']."</b></div><br>";
			echo "<textarea name='".$rowsql['nombre_columna']."' rows='10' cols='75'>Escribe tus comentarios ... </textarea>";		
		}
		
	}
	?>
	</div>
	<?
	echo"<br><br><center><input type='submit' value='Seleccionar' class='boton' accesskey='s'></center><br><br>";
	?>
	<input type="hidden" name="id_encuesta01" id="id_encuesta01" value="<?php echo "2016"; ?>">
	<input type="hidden" name="id_encuesta02" id="id_encuesta02" value="<?php echo $_POST["ciclo"]; ?>">
	</div>
</form>

</body>
</html> 