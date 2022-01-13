<?php
error_reporting(E_ALL);
require_once("../../config.php");
global $DB;
$PAGE->set_pagelayout('base');
$title = "ERROR AVAST";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div style="width:90%;margin-left:5%">
<center><h1><?=$title?></h1></center><br>
<p>Buenos d&iacute;as.</p>
<p>Se ha comprobado en las ultimas horas que una actualizaci&oacute;n del antivirus AVAST, bloquea el acceso a la Plataforma de Formaci&oacute;n a Distancia.</p>
<p>Si os aparece el siguiente mensaje, <strong>"hemos anulado de forma segura la conexión de fp.distancia.aragon.es porque estaba infectada por URL:Blacklist"</strong>, no es un error de la plataforma,
 sino del antivirus, por lo que la &ucate;nica soluci&oacute;n que podemos dar, es que durante la realizaci&oacute;n del examan se desactive el antivirus y se vuelva a activar a su finalizacion, 
 os env&iacute;amos imagen, es muy sencillo, simplemente clickando el bot&oacaute;n derecho en la parte intferior derecha de vuestros ordenadores, en el icono de AVAST:
 <img src="error_avast.png" alt="Error AVAST"></a></p>

<p>Un saludo.</p>

<p><strong>P.D.</strong>Acordaros de reactivarlo al terminar el ex&acute;men.

<?php
echo $OUTPUT->footer();
?>

