<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Formulario de Incidencias</title>
</head>
<script>
var validate = function() {
   var form = document.getElementById("formulario");
   
   if (form.nombre.value.length < 4) {
      alert("Debe rellenar Nombre");
	  return false;
   }
   if (form.apellidos.value.length < 4) {
      alert("Debe rellenar Apellido");
	  return false;
   }
   if (form.email.value.length < 4) {
      alert("Debe rellenar Email");
	  return false;
   }   
   if (form.aConsulta.value=="..."){
      alert("Debe rellenar ciclo");
	  return false;
   }
   if (form.bConsulta.value=="..."){
      alert("Debe rellenar Tipo de consulta");
	  return false;
   }
   if (form.comentario.value.length < 4) {
      alert("Debe rellenar Comentario");
	  return false;
   }  
   return true;
}
</script>
<body   bgcolor="#ffffff" text="#003300" link="#006060" vlink="#006060">
<center>
<div style="width:800px;">
<img src="./incidencia/incidencia_header.jpg" width="800" alt="Tama�o original" border="0">
<br><br>
<?
require("./libreria/class.phpmailer.php");
if (!$_POST){
?>
<div style="padding-top:3px;">
<form action="incidencias.php" method="post" name="formulario" id="formulario" onsubmit="return validate()" >
<!-- <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p> -->
<center>
<div style=" color:#000000;text-align:justify;font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">
	<center><b>FORMULARIO DE ATENCI&Oacute;N DE INCIDENCIAS</b><br><br></center>
	Desde el Equipo de la Plataforma de Formaci�n Profesional a distancia del Gobierno de Arag�n queremos darte el mejor servicio posible ante cualquier incidencia que te surja.<br><br>
	En el Bloque de "AYUDA" de este Portal , encontrar�s varios enlaces con informaci�n �til que te puede ayudar a resolver las principales dudas o incidencias t�cnicas que te surjan. Te recomendamos el apartado de <a href="http://moodle.teleformacionfp.aragon.es/ayuda/preguntas.htm"><b>"Preguntas frecuentes"</b></a> . donde encontrar�s resueltas las dudas e incidencias m�s comunes.
	<br><br><br><center>�No has podido resolver tu duda o incidencia t�cnica?</center><br>
	Si no has encontrado la respuesta que buscabas, contacta con nosotros a trav�s de este formulario. Resolveremos tu consulta en la mayor brevedad posible.<br>
	<br><center>Los campos con asterisco (*) son obligatorios.</center>
</div>
</center>
<br><br>

<table style="">
<tr>
	<td><div style=" color:#001e71; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Nombre:</div> </td>
	<td><input "type="text" name="nombre" id="nombre" size="50"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>	
<tr>
	<td><div style=" color:#001e71; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Apellidos:</div> </td>
	<td><input "type="text" name="apellidos" id="apellidos" size="50"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div style=" color:#001e71; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Correo electr&oacute;nico:</div> </td>
	<td><input "type="text" name="email" id="email" size="100"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div style=" color:#001e71; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Ciclo en el que est�s matriculado:</div> </td>
	<td>
		<SELECT NAME="aConsulta" id="aConsulta">
		   <OPTION VALUE="...">...</OPTION>
		   <OPTION VALUE="CPIFP Bajo Arag�n Desarrollo de Aplicaciones Multiplataforma" >CPIFP Bajo Arag�n Desarrollo de Aplicaciones Multiplataforma</OPTION>
		   <OPTION VALUE="CPIFP Corona de Arag�n Administraci�n y Finanzas">CPIFP Corona de Arag�n	Administraci�n y Finanzas (ADFI)</OPTION>
		   <OPTION VALUE="CPIFP Corona de Arag�n Laboratorio de An�lisis y de Control de Calidad (LACC)">CPIFP Corona de Arag�n	Laboratorio de An�lisis y de Control de Calidad (LACC)</OPTION>
		   <OPTION VALUE="CPIFP Corona de Arag�n Asistencia a la direcci�n">CPIFP Corona de Arag�n	Asistencia a la direcci�n</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Comercio Internacional">CPIFP Los Enlaces	Comercio Internacional</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Desarrollo de Aplicaciones Web (DAW)">CPIFP Los Enlaces	Desarrollo de Aplicaciones Web (DAW)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Sistemas Microinform�ticos (SMR)">CPIFP Los Enlaces	Sistemas Microinform�ticos (SMR)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Transporte y Log�stica (TL)">CPIFP Los Enlaces	Transporte y Log�stica (TL)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Gesti�n de ventas y espacios comerciales<">CPIFP Los Enlaces	Gesti�n de ventas y espacios comerciales</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Producci�n de audiovisuales y espect�culos">CPIFP Los Enlaces	Producci�n de audiovisuales y espect�culos</OPTION>
		   <OPTION VALUE="CPIFP Pir�mide	Instalaciones El�ctricas y Autom�ticas">CPIFP Pir�mide	Instalaciones El�ctricas y Autom�ticas</OPTION>
		   <OPTION VALUE="IES Avempace	Educaci�n Infantil (IES Avempace)">IES Avempace	Educaci�n Infantil (IES Avempace)</OPTION>
		   <OPTION VALUE="IES Mart�nez Vargas 	Educaci�n Infantil (IES Mart�nez Vargas)">IES Mart�nez Vargas 	Educaci�n Infantil</OPTION>
		   <OPTION VALUE="IES Miralbueno	Agencias de viajes y gesti�n de eventos(AVGE)">IES Miralbueno	Agencias de viajes y gesti�n de eventos(AVGE)</OPTION>
		   <OPTION VALUE="IES Montearag�n	Atenci�n sociosanitaria a personas en dependencia (APSD)">IES Montearag�n	Atenci�n sociosanitaria a personas en dependencia (APSD)</OPTION>
		   <OPTION VALUE="IES Pablo Serrano	Administraci�n de Sistemas Inform�ticos en Red (ASIR)">IES Pablo Serrano	Administraci�n de Sistemas Inform�ticos en Red (ASIR)</OPTION>
		   <OPTION VALUE="IES R�o G�llego	Farmacia y Parafarmacia">IES R�o G�llego	Farmacia y Parafarmacia</OPTION>
		   <OPTION VALUE="IES R�o G�llego	Emergencias Sanitarias">IES R�o G�llego	Emergencias Sanitarias</OPTION>
		   <OPTION VALUE="IES Santa Emerenciana	Gesti�n Administrativa">IES Santa Emerenciana	Gesti�n Administrativa</OPTION>
		   <OPTION VALUE="IES Sierra de Guara	Gesti�n Administrativa">IES Sierra de Guara	Gesti�n Administrativa</OPTION>
		   <OPTION VALUE="IES Tiempos Modernos	Gesti�n Administrativa">IES Tiempos Modernos	Gesti�n Administrativa</OPTION>
		   <OPTION VALUE="IES Vega del Turia 	Emergencias sanitarias"">IES Vega del Turia 	Emergencias sanitarias </OPTION>
		   <OPTION VALUE="IES Mar�a Moliner	Integraci�n social">IES Mar�a Moliner	Integraci�n social</OPTION>
		   <OPTION VALUE="I.E.S. Luis Bu�uel	Atenci�n sociosanitaria a personas en dependencia (APSD)">I.E.S. Luis Bu�uel	Atenci�n sociosanitaria a personas en dependencia (APSD)</OPTION>
		</SELECT> <br>
	</td>	
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div style=" color:#001e71;  font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Tipo de consulta:</div></td>
		<td>
		<SELECT NAME="bConsulta" id="bConsulta">
		   <OPTION VALUE="..." Selected>...</OPTION>
		   <OPTION VALUE="Al intentar acceder a la Plataforma aparece un mensaje diciendo La p&aacute;gina no pudo ser mostrada"> Al intentar acceder a la Plataforma aparece un mensaje diciendo "La p&aacute;gina no pudo ser mostrada".</OPTION>
		   <OPTION VALUE="La velocidad de conexi&oacute;n es muy lenta.El tiempo de espera para cargar la p&aacute;gina es excesivo.">La velocidad de conexi&oacute;n es muy lenta.El tiempo de espera para cargar la p&aacute;gina es excesivo.</OPTION>
		   <OPTION VALUE="Problema de acceso a la Plataforma">Problema de acceso a la Plataforma</OPTION>
		   <OPTION VALUE="Incidencias de apoyo a la docencia(contenidos, actividades pr�cticas, criterios en la correcci�n, orientaci�n...)<">Incidencias de apoyo a la docencia(contenidos, actividades pr�cticas, criterios en la correcci�n, orientaci�n...)</OPTION>
		   <OPTION VALUE="Incidencia con los Materiales Did�cticos">Incidencia con los Materiales Did�cticos</OPTION>
		   <OPTION VALUE="Otros">Otros</OPTION>
		</SELECT> <br>
		</td>
	</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div style="color:#001e71;  font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">*Comentario:</div></td>
	<td><textarea cols=80 rows=10 name="comentario" id="comentario"></textarea> <br></td>
</tr>	
</table>
<br>
<center>
	<div style=" color:#000000; font-size: 10pt;font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;">
	Gracias por tu colaboraci�n. Es de gran utilidad para mejorar la organizaci�n de nuestros cursos </div>
</center>	
<br>
<center><div style="padding-top:10px;"class="boton"><input type="submit"  value="Enviar Consulta" class="boton" accesskey="s"></div></center>
</form>
<br><br>
<img src="./incidencia/incidencia_pie.png" width="800" border="0">
</div></div></center>
<?
}else{
	$mail = new PHPMailer();

	$mail->IsSMTP(); 
	$mail->Host = "smtp.aragon.es";
	$mail->SMTPSecure = "tls";

	$mail->SMTPAuth = true;

	// credenciales usuario
	$mail->Username = "admin.moodlefpdistancia@aragon.es";
	$mail->Password = "xxx"; 
	$mail->From = "admin.moodlefpdistancia@aragon.es";
	$mail->FromName = "Administrador de Plataforma FP";
	$mail->Subject = "Consulta de la plataforma";

	//if (($_POST["consulta"]==1)or($_POST["consulta"]==2)){
	//	$add="teleformacionfp@aragon.es";
	//	$mail->AddAddress($add);			
	//}

	//$add="fpdistancia@ita.es";
	$add="cpina@itainnova.es";						
	$mail->AddAddress($add);

	$body = "";
	$body.= "Nombre y apellidos: ".$_POST["nombre"]." ".$_POST["apellidos"]."<br>Email: ".$_POST["email"]."<br>Ciclo en el que est� matriculado: ".$_POST["aConsulta"]."<br><br>".$_POST["comentario"]; 
	$mail->Body = $body;
	$mail->IsHTML(true);
	$mail->Send();

	echo "<br><center><h2>Consulta enviada<br>";
	echo "Muchas Gracias, resolveremos tu consulta en el menor tiempo posible</h2></center>\n";
	}
?>
</div>
</div>
</body>
</html> 