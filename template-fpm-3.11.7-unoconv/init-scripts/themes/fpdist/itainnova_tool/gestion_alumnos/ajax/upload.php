<?php

/**
 * Archivo de importación de archivo EXCEL
 * Al realizar la subida se realiza un primer procesamiento de datos
 */

// Configuración del Moodle
require_once('../../../config.php');

// Gestión de Alumnos
require_once('../../libreria/GestionAlumnos/autoloader.php');

header('Content-type:application/json;charset=utf-8');

try {
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // Llamamos a la clase de improtación 
    $importAlumnos = new ImportExcelAlumnos('file', 'importaciones_excel');

    /**
     * Relaiza la importación y nos devuelve al path del archivo
     */
    $filepath = $importAlumnos->upload();

    // All good, send the response
    echo json_encode([
        'status' => 'ok',
        'path' => $filepath
    ]);

} catch (RuntimeException $e) {
	// Something went wrong, send the err message as JSON
	http_response_code(400);

	echo json_encode([
		'status' => 'error',
		'message' => $e->getMessage()
	]);
}