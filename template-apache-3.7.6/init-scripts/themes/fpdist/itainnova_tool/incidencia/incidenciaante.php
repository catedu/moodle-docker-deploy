<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Formulario de Incidencias</title>
	<style>
    @font-face {
	font-family:'MalayalamSangamMN-Regular';
	src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MalayalamSangamMN_gdi.eot');
	src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MalayalamSangamMN_gdi.eot?#iefix') format('embedded-opentype'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MalayalamSangamMN_gdi.woff') format('woff'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MalayalamSangamMN_gdi.ttf') format('truetype'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MalayalamSangamMN_gdi.svg#MalayalamSangamMN-Regular') format('svg');
	font-weight: 400;
	font-style: normal;
	font-stretch: normal;
	unicode-range: U+0020-201D;
}
        
        @font-face {
	font-family:'MyriadPro-Regular';
	src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MyriadPro-Regular_gdi.eot');
	src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MyriadPro-Regular_gdi.eot?#iefix') format('embedded-opentype'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MyriadPro-Regular_gdi.woff') format('woff'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MyriadPro-Regular_gdi.ttf') format('truetype'),
		url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/MyriadPro-Regular_gdi.svg#MyriadPro-Regular') format('svg');
	font-weight: 400;
	font-style: normal;
	font-stretch: normal;
	unicode-range: U+0020-25CA;
}
   @font-face {
  font-family: 'FontAwesome';
  src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/fontawesome-webfont.eot?v=3.2.1');
  src: url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/fontawesome-webfont.eot?#iefix&v=3.2.1') format('embedded-opentype'), 
      url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/fontawesome-webfont.woff?v=3.2.1') 
      format('woff'), 
      url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/fontawesome-webfont.ttf?v=3.2.1') format('truetype'), 
      url('http://moodle.teleformacionfp.aragon.es/theme/eduhub/font/fontawesome-webfont.svg#fontawesomeregular?v=3.2.1') 
      format('svg');
  font-weight: normal;
  font-style: normal;
}
    </style>
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
<body   bgcolor="#fff" text="#003300" link="#006060" vlink="#006060">
<center>
<div style="width:800px;">
<img src="./incidencia/incidencia_header.png" width="800" alt="Tamaño original" border="0">
<br><br>
<?
require("../libreria/class.phpmailer.php");
if (!$_POST){
?>
<div style="padding-top:3px;">
<form action="incidencia.php" method="post" name="formulario" id="formulario" onsubmit="return validate()" >
<!-- <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p> -->
<center>
<div>
	<center><b>FORMULARIO DE ATENCIÓN DE INCIDENCIAS</b><br><br></center>
	Desde el Equipo de la Plataforma de Formación Profesional a distancia del Gobierno de Aragón queremos darte el mejor servicio posible ante cualquier incidencia que te surja.<br><br>
	En el Bloque de "AYUDA" de este Portal , encontrarás varios enlaces con información útil que te puede ayudar a resolver las principales dudas o incidencias técnicas que te surjan. Te recomendamos el apartado de <a href="http://moodle.teleformacionfp.aragon.es/itainnova_tool/FAQ/preguntas_frecuentes.html"><b>"Preguntas frecuentes"</b></a> . donde encontrarás resueltas las dudas e incidencias más comunes.
	<br><br><br><center>¿No has podido resolver tu duda o incidencia técnica?</center><br>
	Si no has encontrado la respuesta que buscabas, contacta con nosotros a través de este formulario. Resolveremos tu consulta en la mayor brevedad posible.<br>
	<br><center>Los campos con asterisco (*) son obligatorios.</center>
</div>
</center>
<br><br>

<table style="">
<tr>
	<td><div>*Nombre:</div> </td>
	<td><input "type="text" name="nombre" id="nombre" size="50"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>	
<tr>
	<td><div>*Apellidos:</div> </td>
	<td><input "type="text" name="apellidos" id="apellidos" size="50"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div>*Correo electrónico:</div> </td>
	<td><input "type="text" name="email" id="email" size="100"><br></td>
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div>*Ciclo en el que estás matriculado:</div> </td>
	<td>
		<SELECT NAME="aConsulta" id="aConsulta">
		   <OPTION VALUE="...">...</OPTION>
		   <OPTION VALUE="CPIFP Bajo Aragón Desarrollo de Aplicaciones Multiplataforma" >CPIFP Bajo Aragón Desarrollo de Aplicaciones Multiplataforma</OPTION>
		   <OPTION VALUE="CPIFP Corona de Aragón Administración y Finanzas">CPIFP Corona de Aragón	Administración y Finanzas (ADFI)</OPTION>
		   <OPTION VALUE="CPIFP Corona de Aragón Laboratorio de Análisis y de Control de Calidad (LACC)">CPIFP Corona de Aragón	Laboratorio de Análisis y de Control de Calidad (LACC)</OPTION>
		   <OPTION VALUE="CPIFP Corona de Aragón Asistencia a la dirección">CPIFP Corona de Aragón	Asistencia a la dirección</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Comercio Internacional">CPIFP Los Enlaces	Comercio Internacional</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Desarrollo de Aplicaciones Web (DAW)">CPIFP Los Enlaces	Desarrollo de Aplicaciones Web (DAW)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Sistemas Microinformáticos (SMR)">CPIFP Los Enlaces	Sistemas Microinformáticos (SMR)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Transporte y Logística (TL)">CPIFP Los Enlaces	Transporte y Logística (TL)</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Gestión de ventas y espacios comerciales<">CPIFP Los Enlaces	Gestión de ventas y espacios comerciales</OPTION>
		   <OPTION VALUE="CPIFP Los Enlaces	Producción de audiovisuales y espectáculos">CPIFP Los Enlaces	Producción de audiovisuales y espectáculos</OPTION>
		   <OPTION VALUE="CPIFP Pirámide	Instalaciones Eléctricas y Automáticas">CPIFP Pirámide	Instalaciones Eléctricas y Automáticas</OPTION>
		   <OPTION VALUE="IES Avempace	Educación Infantil (IES Avempace)">IES Avempace	Educación Infantil (IES Avempace)</OPTION>
		   <OPTION VALUE="IES Martínez Vargas 	Educación Infantil (IES Martínez Vargas)">IES Martínez Vargas 	Educación Infantil</OPTION>
		   <OPTION VALUE="IES Miralbueno	Agencias de viajes y gestión de eventos(AVGE)">IES Miralbueno	Agencias de viajes y gestión de eventos(AVGE)</OPTION>
		   <OPTION VALUE="IES Montearagón	Atención sociosanitaria a personas en dependencia (APSD)">IES Montearagón	Atención sociosanitaria a personas en dependencia (APSD)</OPTION>
		   <OPTION VALUE="IES Pablo Serrano	Administración de Sistemas Informáticos en Red (ASIR)">IES Pablo Serrano	Administración de Sistemas Informáticos en Red (ASIR)</OPTION>
		   <OPTION VALUE="IES Río Gállego	Farmacia y Parafarmacia">IES Río Gállego	Farmacia y Parafarmacia</OPTION>
		   <OPTION VALUE="IES Río Gállego	Emergencias Sanitarias">IES Río Gállego	Emergencias Sanitarias</OPTION>
		   <OPTION VALUE="IES Santa Emerenciana	Gestión Administrativa">IES Santa Emerenciana	Gestión Administrativa</OPTION>
		   <OPTION VALUE="IES Sierra de Guara	Gestión Administrativa">IES Sierra de Guara	Gestión Administrativa</OPTION>
		   <OPTION VALUE="IES Tiempos Modernos	Gestión Administrativa">IES Tiempos Modernos	Gestión Administrativa</OPTION>
		   <OPTION VALUE="IES Vega del Turia 	Emergencias sanitarias">IES Vega del Turia 	Emergencias sanitarias </OPTION>
		   <OPTION VALUE="IES María Moliner	Integración social">IES María Moliner	Integración social</OPTION>
		   <OPTION VALUE="I.E.S. Luis Buñuel	Atención sociosanitaria a personas en dependencia (APSD)">I.E.S. Luis Buñuel	Atención sociosanitaria a personas en dependencia (APSD)</OPTION>
		</SELECT> <br>
	</td>	
</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div>*Tipo de consulta:</div></td>
		<td>
		<SELECT NAME="bConsulta" id="bConsulta">
		   <OPTION VALUE="..." Selected>...</OPTION>
		   <OPTION VALUE="Al intentar acceder a la Plataforma aparece un mensaje diciendo La p&aacute;gina no pudo ser mostrada"> Al intentar acceder a la Plataforma aparece un mensaje diciendo "La página no pudo ser mostrada".</OPTION>
		   <OPTION VALUE="La velocidad de conexión es muy lenta.El tiempo de espera para cargar la página es excesivo.">La velocidad de conexión es muy lenta. El tiempo de espera para cargar la página es excesivo.</OPTION>
		   <OPTION VALUE="Problema de acceso a la Plataforma">Problema de acceso a la Plataforma</OPTION>
		   <OPTION VALUE="Incidencias de apoyo a la docencia(contenidos, actividades prácticas, criterios en la corrección, orientación...)<">Incidencias de apoyo a la docencia(contenidos, actividades prácticas, criterios en la corrección, orientación...)</OPTION>
		   <OPTION VALUE="Incidencia con los Materiales Didácticos">Incidencia con los Materiales Didácticos</OPTION>
		   <OPTION VALUE="Otros">Otros</OPTION>
		</SELECT> <br>
		</td>
	</tr>
<tr>
	<td><br><br></td>
</tr>
<tr>
	<td><div>*Comentario:</div></td>
	<td><textarea cols=80 rows=10 name="comentario" id="comentario"></textarea> <br></td>
</tr>	
</table>
<br>
<center>
	<div>
	Gracias por tu colaboración. Es de gran utilidad para mejorar la organización de nuestros cursos </div>
</center>	
<br>
<center><div style="padding-top:10px;"class="boton"><input type="submit"  value="Enviar Consulta" class="boton" accesskey="s"></div></center>
</form>
<br><br>
<img src="./incidencia/incidencia_pie1.png" width="800" border="0">
</div></div></center>
<?
}else{
	$mail = new PHPMailer();

	$mail->IsSMTP(); 
	$mail->Host = "smtp.aragon.es";
	$mail->SMTPSecure = "tls";

	$mail->SMTPAuth = true;

	// credenciales usuario
				include "./secret/crenciales.php";
	$mail->Subject = "Consulta de la plataforma";

	//if (($_POST["consulta"]==1)or($_POST["consulta"]==2)){
	//	$add="teleformacionfp@aragon.es";
	//	$mail->AddAddress($add);			
	//}

	//$add="fpdistancia@ita.es";
	$add="abarreras@itainnova.es";						
	$mail->AddAddress($add);

	$body = "";
	$body.= "Nombre y apellidos: ".$_POST["nombre"]." ".$_POST["apellidos"]."<br>Email: ".$_POST["email"]."<br>Ciclo en el que estás matriculado: ".$_POST["aConsulta"]."<br><br>".$_POST["comentario"]; 
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