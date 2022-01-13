<?php

date_default_timezone_set('Europe/Madrid');

// Configuración del Moodle
require_once('../../../config.php');

$coordinadoresFolder = '../coordinadores/';

$zipname = $coordinadoresFolder.$_REQUEST['zipname'];

$zip = new ZipArchive();
if (file_exists($zipname)) unlink($zipname);

if ($zip->open($zipname, ZIPARCHIVE::CREATE) != TRUE) {
	echo '<h2>Error al crear el fichero zip</h2>';
} else {
    $options = array('remove_all_path' => TRUE);
    $archives = $_REQUEST['archives'];

    $files = $coordinadoresFolder . $archives;
    if($zip->addGlob($files, GLOB_BRACE, $options)){
        echo json_encode(array('zipped' => $files, 'zipfile' => './coordinadores/' . $_REQUEST['zipname'], 'zipname' => $_REQUEST['zipname']));
        // close and save archive
        $zip->close();
        
        // Eliminamos archivos
        // Comentado porque quieren los archivos sueltos también
        // array_map( "unlink", glob($files) );
        exit();
    }
}
