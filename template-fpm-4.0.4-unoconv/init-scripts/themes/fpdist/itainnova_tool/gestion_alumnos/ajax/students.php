<?php
/**
 * Archivo de recuperación de datos de alumnos
 * Utiliza el parámetro $_GET['searchType'] para realizar las distintas consultas de datos
 */

// Configuración del Moodle
require_once('../../../config.php');

// Gestión de Alumnos
require_once('../../libreria/GestionAlumnos/autoloader.php');

header('Content-type:application/json;charset=utf-8');

try {

    if(!empty($_GET['searchType'])){
    
        if ($_GET['searchType'] == 'all_info') {

            if (empty($_GET['username']) && empty($_GET['email'])) {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Falta usuario y correo para realizar la consulta.'
                ]);
                die();
            }

            $params = [
                'username' =>  $_GET['username'],
                'email' =>  $_GET['email'],
                'cohort' =>  $_GET['cohort']
            ];

            $params['errores'] = true;

            // Recuperación de datos del alumno actualmente matriculado
            $resultAlumnoActual = TablaAlumnos::getAlumnosMatriculadosActual($params);

            if (!empty($resultAlumnoActual['result'] == 'OK')) {

                // Recuperación de datos del excel importado para comparar
                $resultAlumnoComparar = TablaAlumnos::getAlumnosMatriculadosComparar($params);

                $alumnoActual = [];
                $alumnoComparar = [];
                $campos = [
                    'firstname' => 'Nombre',
                    'lastname' => 'Apellidos',
                    'username' => 'Usuario',
                    'email' => 'Email',
                    'dni_nif' => 'DNI/NIF',
                    'city' => 'Ciudad',
                    'cohort1' => 'Cohorte'
                ];

                $courseColumns =  TablaAlumnos::getCoursesColumns();

                // Número de campos de curso
                for ($x = 1; $x < $courseColumns; $x++) {
                    $campos['course' . $x] = 'Curso ' . $x;
                }

                // Convertimos los Objetos en Array

                // "Alumno Actual" (Acutalmente en la bbdd)
                $resultAlumnoActual = (array) $resultAlumnoActual['resultAlumno'];

                // "Alumno a comparar" (Importado desde el excel)
                $resultAlumnoComparar = (array) $resultAlumnoComparar['resultAlumno'];

                // Añadimos las variables de errores
                if (!empty($resultAlumnoComparar['errores_curso'])) $errores = $resultAlumnoComparar['errores_curso'];
                elseif (!empty($resultAlumnoComparar['errores_datos'])) $errores = $resultAlumnoComparar['errores_datos'];

                if (!empty($resultAlumnoActual['errores'])) $erroresBajas = $resultAlumnoActual['errores'];

                // Buscamos cada uno de los campos para verificar los datos del "Alumno a comparar"
                // Aprovechamos para verificar los del "Alumno Actual"
                $errores = explode(',', $errores);
                foreach ($campos as $ind => $campo) {
                    if (!empty($resultAlumnoActual[$ind])) {
                        $alumnoActual[$campo]['valor'] = $resultAlumnoActual[$ind];
                    }
                    if (!empty($resultAlumnoComparar[$ind])) {
                        $alumnoComparar[$campo]['valor'] = $resultAlumnoComparar[$ind];
                        if (in_array($ind, $errores)) {
                            $alumnoComparar[$campo]['error'] = true;
                        }
                    }
                }

                // El "Alumno Actual" tiene los campos de cursos en un sólo campo de la tabla dividido con comas, por lo que 
                // necesitamos transformarlo en un array y tratarlo. Esto no lo tiene el "Alumno Actual", ya que cada curso,
                // se divide en una columna diferente en la tabla 
                $erroresBajas = explode(',', $erroresBajas);
                if (!empty($resultAlumnoActual['courses'])) {
                    $courses = explode(',', $resultAlumnoActual['courses']);
                    $courseNum = 1;
                    foreach ($courses as $course) {
                        $alumnoActual['Curso ' . $courseNum]['valor'] = $course;
                        if (in_array('course' . $courseNum, $erroresBajas)) {
                            $alumnoActual['Curso ' . $courseNum]['error'] = true;
                        }
                        $courseNum++;
                    }
                }

                // All good, send the response
                echo json_encode([
                    'status' => 'ok',
                    'alumnoActual' => $alumnoActual,
                    'alumnoComparar' => $alumnoComparar,
                    'estado_exportar' => $resultAlumnoComparar['export_altas_bajas']
                ]);
            } else {
                echo json_encode([
                    'status' => 'ko',
                    'message' => '<p>No ha sido posible encontrar los datos del alumno.</p><p>Compruebe manualmente que el alumno "'.$_GET['email'].'" esté dado de alta y pertenece a la cohorte "'.$_GET['cohort'].'".</p>'
                ]);
            }
        } elseif ($_GET['searchType'] == 'cohort') {

            /****************************/
            /* Recuperación de COHORTE */
            /**************************/

            $sql = "SELECT *
                    FROM {cohort}
                    WHERE idnumber LIKE '" . $_GET['cohort'] . "'";

            $resultCohort = $DB->get_record_sql($sql);

            $username = !empty($_GET['username']) ? $_GET['username'] : '';

            if(!empty($resultCohort)){
                // All good, send the response
                echo json_encode([
                    'status' => 'ok',
                    'cohortUrl' => new moodle_url('/cohort/assign.php').'?id='.$resultCohort->id.'&removeselect_searchtext='.$username
                ]);
            }else{
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'La cohorte <b>'.$_GET['cohort'].'</b> no se encuentra dado de alta.'
                ]);
            }
            
        } elseif ($_GET['searchType'] == 'user') {

            /****************************/
            /* Recuperación de USUARIO */
            /**************************/

            $sql = "SELECT {user}.id
                    FROM {user} JOIN {user_info_data} ON {user}.`id` = {user_info_data}.`userid`
                    WHERE 1=1";
            
            if($_GET['searchField'] == 'username'){
                $sql .= ' AND {user}.`username` LIKE "' . $_GET['searchValue'] . '"';
            }

            if($_GET['searchField'] == 'dni_nif'){
                $sql .= ' AND {user_info_data}.`data` LIKE "' . $_GET['searchValue'] . '"';
            }

            $sql .= ' GROUP BY {user}.id ';

            $resultUser = $DB->get_record_sql($sql);

            if(!empty($resultUser)){
                // All good, send the response
                echo json_encode([
                    'status' => 'ok',
                    'userUrl' => new moodle_url('/user/profile.php').'?id='.$resultUser->id
                ]);
            }else{
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'El usuario <b>'.$_GET['username'].'</b> no se encuentra dado de alta.'
                ]);
            }
        } else {
            
            echo json_encode([
                'status' => 'ko',
                'message' => 'Error en el tipo de búsqueda'
            ]);
        }
    } elseif(!empty($_POST['addStudentTo'])) {
        
        $_POST['export_altas_bajas'] = '0';
        $alumnosMatriculadosComparar = TablaAlumnos::getAlumnosMatriculadosComparar($_POST, false);

        $alumnoCompara = new stdClass();
        if($alumnosMatriculadosComparar['result'] == 'OK') {
            $alumnoCompara = $alumnosMatriculadosComparar['resultAlumno'];
        } else {
            echo json_encode([
                'status' => 'ko',
                'message' => 'No existen datos en la tabla de Alumnos Importados con los datos proporcionados',
                'data' => $_POST
            ]);
            exit();
        }

        $alumnosMatriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($_POST);

        if($alumnosMatriculadosActual['result'] == 'OK') {
            $alumnoActual = $alumnosMatriculadosActual['resultAlumno'];
        } else {
            echo json_encode([
                'status' => 'ko',
                'message' => 'No existen datos en la tabla de Alumnos Actualmente Matriculados con los datos proporcionados',
                'data' => $_POST
            ]);
            exit();
        }

        if (!empty($alumnoActual->courses)) {

            $alumnosBajaCursos = clone $alumnoCompara;
            $alumnosAltaCursos = clone $alumnoCompara;

            unset($alumnosBajaCursos->export_altas_bajas);
            unset($alumnosAltaCursos->export_altas_bajas);

            $cursosActuales = explode(',', $alumnoActual->courses);
            $cursosNuevos = [];
            for ($courseNum = 1; $courseNum <= TablaAlumnos::getCoursesColumns(); $courseNum++) {
                $alumnosAltaCursos->{'course' . $courseNum} = '';
                $alumnosBajaCursos->{'course' . $courseNum} = '';

                $curso = $alumnoCompara->{'course' . $courseNum};
                if (!empty($curso)) {
                    // Añadimos todos los cursos importados para la comparación
                    $cursosNuevos[] = $curso;

                    // if(!in_array($curso, $cursosActuales)) $cambios[$alumnoActual->dni_nif][$alumnoActual->cohort1]['course'.$courseNum] = $curso;
                    if (!in_array($curso, $cursosActuales)) {
                        $cambios['cursos_alta'][] = 'course' . $courseNum;
                    }
                }
            }

            $contCurso = 1;
            foreach ($cursosActuales as $ind => $curso) {
                if (!in_array($curso, $cursosNuevos)) {
                    $alumnosBajaCursos->{'course' . $contCurso} = $curso;
                    $cambios['cursos_baja'][] = 'course' . ($ind + 1);
                    $contCurso++;
                }
            }

            if (!empty($cursosNuevos)) {
                $contCurso = 1;
                foreach ($cursosNuevos as $curso) {
                    if (!in_array($curso, $cursosActuales)) {
                        $alumnosAltaCursos->{'course' . $contCurso} = $curso;
                        $contCurso++;
                    }
                }
            }

            // Controlamos si existen modificaciones con esta variable
            $createdRecord = false;

            if (!empty($cambios['cursos_alta'])) {
                // ALTA CURSO ALUMNOS
                $DB->insert_record('itainnova_alumnos_altas_cursos', $alumnosAltaCursos);

                $createdRecord = true;
            }

            if (!empty($cambios['cursos_baja'])) {
                // BAJA CURSO ALUMNOS
                $DB->insert_record('itainnova_alumnos_bajas_cursos', $alumnosBajaCursos);

                $createdRecord = true;
            }

            if($createdRecord === true){
                $alumnoUpdate = new stdClass();
                $alumnoUpdate->id = $alumnoCompara->id; 
                $alumnoUpdate->export_altas_bajas = 1;

                $DB->update_record('itainnova_alumnos_comparar',$alumnoUpdate);

                echo json_encode([
                    'updated_record' => 'OK',
                    'alumnoCompara' => $alumnoCompara,
                    'alumnoActual' => $alumnoActual
                ]);
            }else{
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'No se ha realizado ningún cambio en las tablas',
                    'alumnoCompara' => $alumnoCompara,
                    'alumnoActual' => $alumnoActual
                ]);
            }
        }else{
            echo json_encode([
                'status' => 'KO',
                'message' => 'El alumno actual no tiene cursos',
                'alumnoCompara' => $alumnoCompara,
                'alumnoActual' => $alumnoActual
            ]);
        }

    } else {
        echo json_encode([
            'status' => 'ko',
            'message' => 'Acción no correspondiente'
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