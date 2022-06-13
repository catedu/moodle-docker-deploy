<?php
/**
 * Archivo de procesamiento de datos
 */

// Configuración del Moodle
require_once('../../../config.php');

// Gestión de Alumnos
require_once('../../libreria/GestionAlumnos/autoloader.php');

header('Content-type:application/json;charset=utf-8');

try {

    if ($_GET['process'] == 'tratarDatosImport') {
        $comparaAlumnos = new ComparaAlumno($_GET['excelFile']);
        $comparaAlumnos->tratarDatosImport();

        // All good, send the response
        echo json_encode([
            'status' => 'ok'
        ]);
    }

} catch (RuntimeException $e) {
    // Something went wrong, send the err message as JSON
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}