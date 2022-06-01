<?php

date_default_timezone_set('Europe/Madrid');

// Configuración del Moodle
require_once('../../../config.php');

// Librería PDF
require_once('../../libreria/mpdf7/autoload.php');

header('Content-type:application/json;charset=utf-8');

$coordinadoresFolder = '../coordinadores/';

$centro = $_POST['centers']['center'];

// Datos del Ciclo
$ciclo = (object) $centro['courseData'];

// Datos de los profesores
$teachersData = (object) $centro['teachersData'];

$calendarios = $_POST['centers']['calendars'];
$imagenes = $_POST['centers']['images'];



$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);

//Lo ciframos con una clave de 128 bits, para evitar ediciones
//Permitimos copiar e imprimir
//https://mpdf.github.io/reference/mpdf-functions/setprotection.html
$mpdf->SetProtection(array('copy', 'print', 'print-highres'), '', '1nf0rm3.2107', 128);

$mpdf->AddPage('P');
$stylesheet = file_get_contents('../css/alumnos.css');

$mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
$mpdf->WriteHTML("<h1 align='center'>Plataforma de Formaci&oacute;n Profesional a Distancia del Gobierno de Arag&oacute;n</h1>");
$mpdf->WriteHTML("<br><h2 align='center'>Informe de seguimiento del profesorado</h2>");

//$ciclo = current($DB->get_records('course_categories',array('id'=>$ciclo)));

$mpdf->WriteHTML('<div style="position: absolute; margin-top: auto; margin-left: auto; margin-right: auto; margin-bottom: auto;">
					<img src="../images/portada_informe_profesores.png" width="90%" />
				</div>');

$nombreCiclo = $ciclo->name;

$mpdf->WriteHTML("<div style='position: fixed; left: 0mm; bottom: 0mm;'>
					<table class='tablaDatos' align='left' style='width:100%'>
						<tbody><tr><td><strong>Ciclo:</strong></td><td>$nombreCiclo</td></tr>
						<tr><td><strong>Fecha informe:</strong></td><td>" . date('d-m-Y') . "</td></tr>
					</tbody></table>
				</div>");


// Nombre CORTO del ciclo para crear nombre de archivo PDF
$cursoNombreCorto = '';

// Por cada profesor del ciclo
foreach ($teachersData AS $teacher) {
	
	$teacher = (object) $teacher;
	$profesor = (object) $teacher->profesor;

	// Nombre COMPLETO del ciclo NO CONFUNDIR CON NOMBRE DEL PROFESOR por el uso de la variable
	// En get_centros.php se recuperan los datos en la SQL $sql_profesores_por_centro
	$cursoNombreCompleto = $profesor->fullname;

	// Asignamos el Nombre CORTO del ciclo
	if(empty($cursoNombreCorto) && !empty($profesor->shortname)) 
		$cursoNombreCorto = $profesor->shortname;
	
	// Título Curso
	$header = array(
		'odd' => array(
			'L' => array(
				'content' => 'Informe de seguimiento del profesorado',
				'font-size' => 10,
				'font-style' => 'B',
				'font-family' => 'serif',
				'color' => '#000000'
			),
			'C' => array(
				'content' => (strrpos($cursoNombreCompleto, '_') === False ? $cursoNombreCompleto : substr($cursoNombreCompleto, strrpos($cursoNombreCompleto, '_') + 1)),
				'font-size' => 10,
				'font-style' => 'B',
				'font-family' => 'serif',
				'color' => '#000000'
			),
			'R' => array(
				'content' => date('d-m-Y'),
				'font-size' => 10,
				'font-style' => 'B',
				'font-family' => 'serif',
				'color' => '#000000'
			),
			'line' => 1,
		),
	);

	$mpdf->SetHeader($header);
	$mpdf->AddPage('L');

	// ID para buscar el div de tipo calendario
	// Es el mismo ID que se crea en get_centros.php para relacionarlo
	$indCalendar = $profesor->userid . '-calendar-' . $profesor->curso;
	$calendarDiv = '';
	foreach($calendarios as $ind => $calendario) {
		if($calendario['index'] == $indCalendar) {
			$calendarDiv = mb_convert_encoding($calendario['calendar'],"ISO-8859-1");
			break;
		}
	}
	
	if(!empty($calendarDiv)){

		$dom = new DOMDocument();

		$dom->loadHTML($calendarDiv);
		$dom->getElementsByTagName('svg')->item(0)->setAttribute('height', '350px');
		$dom->getElementsByTagName('svg')->item(0)->setAttribute('width', '910px');
		$dom->replaceChild($dom->getElementsByTagName('svg')->item(0), $dom->documentElement);
	
		$nodes = $dom->getElementsByTagName('text');
		foreach ($nodes AS $node) {
			switch ($node->nodeValue) {
				case '0':
				case '10':
					$node->nodeValue = '';
					break;
				case 'Jan':
					$node->nodeValue = 'Enero';
					break;
				case 'Feb':
					$node->nodeValue = 'Febrero';
					break;
				case 'Mar':
					$node->nodeValue = 'Marzo';
					break;
				case 'Apr':
					$node->nodeValue = 'Abril';
					break;
				case 'May':
					$node->nodeValue = 'Mayo';
					break;
				case 'Jun':
					$node->nodeValue = 'Junio';
					break;
				case 'Jul':
					$node->nodeValue = 'Julio';
					break;
				case 'Aug':
					$node->nodeValue = 'Agosto';
					break;
				case 'Sep':
					$node->nodeValue = 'Septiembre';
					break;
				case 'Oct':
					$node->nodeValue = 'Octubre';
					break;
				case 'Nov':
					$node->nodeValue = 'Noviembre';
					break;
				case 'Dec':
					$node->nodeValue = 'Diciembre';
					break;
			}
		}

		//Quitamos la escala de colores (arriba derecha)
		$nodes = $dom->getElementsByTagName('path');
		for ($i = 0; $i < $nodes->length; $i++) {
			if (strpos($nodes[$i]->getAttribute("fill"), 'ABSTRACT_RENDERER_ID_1') !== false) {
				$nodes[$i]->parentNode->removeChild($nodes[$i]);
				$i--;
			}
			if ($nodes[$i] <> NULL && $nodes[$i]->getAttribute('stroke') == '#eeeeee') {
				$nodes[$i]->parentNode->removeChild($nodes[$i]);
				$i--;
			}
		}

		$mpdf->WriteHTML($dom->saveHTML());

		unset($dom);
	}

	$img = '';
	$indImage = $profesor->userid . '-clicks-' . $profesor->curso;
	foreach($imagenes as $ind => $image) {
		if($image['index'] == $indImage) {
			$img = $image['image'];
			break;
		}
	}

	if(!empty($img)){
		$mpdf->WriteHTML('<div style="width:100%;max-height:200px;"><img style="max-height:200px;" src=' . $img . '></div>');
	}

	$mpdf->WriteHTML('<table style="width:100%;padding-top:50px" class="tablaDatos">
			<tr>
				<th>Descripci&oacute;n</th>
				<th style="text-align:center">Resultado</th>
				<th>Descripci&oacute;n</th>
				<th>Resultado</th>
			</tr>
			<tr class="tr-0">
				<td>N&uacute;mero de conexiones</td>
				<td style="text-align:center">' . $teacher->totalconexiones . '</td>
				<td>N&uacute;mero de d&iacute;as que se ha conectado</td>
				<td style="text-align:center">' . $teacher->dias . '</td>
			</tr>
			<tr class="tr-1">
				<td>N&uacute;mero de registros del curso</td>
				<td style="text-align:center">' . $teacher->registros . '</td>
				<td>N&uacute;mero de mensajes enviados del curso</td>
				<td style="text-align:center">' . $teacher->num_mensajes . '</td>
			</tr>
			<tr class="tr-0">
				<td>N&uacute;mero de mensajes recibidos</td>
				<td style="text-align:center">' . $teacher->num_mensajes_recibidos . '</td>
				<td>N&uacute;mero de correos enviados a trav&eacute;s de la plataforma</td>
				<td style="text-align:center">' . $teacher->localmail . '</td>
			</tr>
			<tr class="tr-1">
				<td>N&uacute;mero de post\'s escritos en los foros del curso</td>
				<td style="text-align:center">' . $teacher->posts . '</td>
				<td>N&uacute;mero de actualizaciones de recursos que ha realizado</td>
				<td style="text-align:center">' . $teacher->actualizados . '</td>
			</tr>
			<tr class="tr-0">
				<td>N&uacute;mero de subida de recursos que ha realizado</td>
				<td style="text-align:center">' . $teacher->nuevos . '</td>
			</tr>
		</table>');
}


// Nombre del Fichero PDF
$nombre_fic = substr($cursoNombreCorto, 0, strrpos($cursoNombreCorto, '_')) . '_' . date("d-m-Y") . '.pdf';

// Nombre del fichero ZIP (A crear posteriormente en el siguiente proceso)
$zipname = substr($cursoNombreCorto, 0, strpos($cursoNombreCorto, '_')) . '_' . date("d-m-Y") . '.zip';

// Con esta variable buscaremos todos los archivos para añadir al ZIP
// Utiliza el operador comodín para posteriormente realizar la búsqueda de los archivos creados
$ziparchives = substr($cursoNombreCorto, 0, strpos($cursoNombreCorto, '_')) . '*' . date("d-m-Y") . '.pdf';

// Se crea el archivo PDF
if (is_siteadmin($USER->id)) {
	$fichero = $coordinadoresFolder.$nombre_fic;
	$mpdf->Output($fichero, \Mpdf\Output\Destination::FILE);
	$mpdf->WriteHTML($fichero);
}

// Enviamos los datos necesarios para crear el ZIP
echo json_encode(array('zipname' => $zipname, 'archive' => $ziparchives, 'pdf' => $fichero));