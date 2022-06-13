<?php

/**
 * Clase que realiza las acciones de comparación y  tratamiento de datos
 */
class ComparaAlumno
{

	private $archivoExcel = '';
	private $baseUrl = '';
	private $coursesColumns = 12;

	/**
	 * Variable de tipo array con Emails Validos
	 *
	 * @var array
	 */
	private $emailsValidos = [];

	/**
	 * Construye la clase con el nombre del archivo que se ha subido a la base de datos
	 *
	 * @param string $archivoExcel
	 */
	public function __construct($archivoExcel = '', $baseUrl = '')
	{
		$this->archivoExcel = $archivoExcel;
		$this->baseUrl = $baseUrl;
		$this->emailsValidos = self::setEmailsValidos();
		$this->coursesColumns = TablaAlumnos::getCoursesColumns();
	}

	/**
	 * Devuelve los dominios de emails válidos {itainnova_valida_emails}
	 *
	 * @return void
	 */
	public static function setEmailsValidos()
	{
		global $DB;

		$validaEmails = $DB->get_records_sql('SELECT * FROM {itainnova_valida_emails}');
		$emailsValidos = array();
		foreach ($validaEmails as $email) {
			$emailsValidos[] = $email->domain_email;
		}

		return $emailsValidos;
	}

	/**
	 * Recupera los alumnos utilizando el nombre del archivo EXCEL cargado
	 * Si recuperamos la variable $sinErrores = true, devolverá todos los alumnos importados
	 * que no tengan errores
	 *
	 * @return void
	 */
	private function getAlumnosImportados($sinErrores = false) : array
	{
		global $DB;

		$sql = 'SELECT *
				FROM {itainnova_alumnos_comparar}
				WHERE archivo_excel="' . $this->archivoExcel . '"';

		if ($sinErrores == true) {
			$sql  .= ' AND id NOT IN (
						SELECT import_id
						FROM {itainnova_alumnos_comparar_errores}
						WHERE archivo_excel="' . $this->archivoExcel . '"
					)
					AND id NOT IN (
						SELECT import_id
						FROM {itainnova_alumnos_comparar_errores_cursos}
						WHERE archivo_excel="' . $this->archivoExcel . '"
					)';
		}

		$alumnos = $DB->get_records_sql($sql);

		return $alumnos;
	}

	/**
	 * Elimina todos los alumnos de {itainnova_alumnos_comparar} que no tengan errores
	 * Su utilidad es la de limpiar datos innecesarios o irrelevantes
	 *
	 * @return void
	 */
	private function deleteAlumnosSinErrores()
	{
		global $DB;

		$conditions = 'archivo_excel="' . $this->archivoExcel . '"
					AND id NOT IN (
						SELECT import_id
						FROM {itainnova_alumnos_comparar_errores}
						WHERE archivo_excel="' . $this->archivoExcel . '"
					)
					AND id NOT IN (
						SELECT import_id
						FROM {itainnova_alumnos_comparar_errores_cursos}
						WHERE archivo_excel="' . $this->archivoExcel . '"
					)';

		$params = null;

		$alumnos = $DB->delete_records_select('itainnova_alumnos_comparar', $conditions, $params);

		return $alumnos;
	}

	/**
	 * Trata los datos importados para realizar las operaciones de
	 * comparación con datos ya en base de datos.
	 *
	 * @return void
	 */
	public function tratarDatosImport()
	{
		global $DB;

		$DB->delete_records('itainnova_alumnos_comparar_errores');
		$DB->delete_records('itainnova_alumnos_comparar_errores_cursos');
		$DB->delete_records('itainnova_alumnos_comparar_errores_cursos_bajas');

		// Al estar vacías las tablas de errores recuperamos todos los alumnos de la tabla
		// itainnova_alumnos_comparar
		$alumnosComparacion = $this->getAlumnosImportados();
		
		/* echo '$alumnosComparacion <br>';
		var_dump($alumnosComparacion); */

		$camposCompara = array(
			'username',
			'firstname',
			'lastname',
			'email',
			'city',
			'dni_nif',
			'cohort1'
		);

		if (!empty($alumnosComparacion)) {
			$cont = 0;

			$coursesColumns = $this->coursesColumns;

			foreach ($alumnosComparacion as $alumnoCompara) {

				$params = [
					'username' =>  $alumnoCompara->username,
					'email' =>  $alumnoCompara->email,
					'dni_nif' =>  $alumnoCompara->dni_nif,
					'cohort' =>  $alumnoCompara->cohort1
				];

				$matriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($params, true);

				/* echo '$matriculadosActual <br>';
				var_dump($matriculadosActual); */

				// Array de cambios para campos, cursos de alta y baja
				// Este array añadirá según el tipo de cambios de cada alumno en la tabla correspondientre
				$cambios = [
					'campos' => [],
					'cursos_alta' => [],
					'cursos_baja' => [],
				];

				$alumnoActual = null;

				if ($matriculadosActual['result'] == 'OK') {

					$alumnoActual = $matriculadosActual['resultAlumno'];

					if(empty($alumnoCompara->email) && !empty($alumnoActual->email)){
						$alumnoCompara->email = $alumnoActual->email;
					}

					// Comparamos los campos de nombre, apellidos, email...
					// Se añaden al array de cambios
					foreach ($camposCompara as $campo) {
						if (empty($alumnoCompara->{$campo}) ||
							trim($alumnoCompara->{$campo}) != trim($alumnoActual->{$campo})){
								$cambios['campos'][] = $campo;
							}
					}

					if (!empty($alumnoActual->courses)) {

						$alumnosBajaCursos = clone $alumnoCompara;
						$alumnosAltaCursos = clone $alumnoCompara;

						// Cursos que actualmente están en la ficha del alumno
						$cursosActuales = explode(',', $alumnoActual->courses);

						// Añadiremos los cursos que nos lleguen del excel como "Nuevos"
						// En el caso de que los cursos actuales con los nuevos no sean iguales
						// se crearán la diferencia como altas o bajas
						$cursosNuevos = [];

						// Comparamos los cursos actuales y comparamos
						// con el alumno importado. Si no existe en el listado,
						// el curso es nuevo
						for ($ind = 1; $ind <= 20; $ind++) {
							$alumnosAltaCursos->{'course' . $ind} = '';
							$alumnosBajaCursos->{'course' . $ind} = '';

							$cursoCompara = $alumnoCompara->{'course' . $ind};
							if (!empty($cursoCompara)) {
								// Añadimos todos los cursos importados para la comparación
								$cursosNuevos[] = $cursoCompara;

								// Si el curso del registro en el excel no existe entre los del alumno actual
								// añadiremos ese curso como ALTA
								if (!in_array($cursoCompara, $cursosActuales)) {
									$cambios['cursos_alta'][] = 'course' . $ind;

									// if(($ind + 1) > $coursesColumns) $coursesColumns = $ind + 1;
								}
							}
						}

						// Buscaremos si este alumno a comparar tiene mas de un registro en la tabla de comparación
						// así evitaremos duplicar registros con los cursos de alta y baja
						$sql = 'SELECT *
								FROM {itainnova_alumnos_comparar}
								WHERE username ="' . $alumnoCompara->username . '"
								AND id != ' . $alumnoCompara->id .'
								AND cohort1 = "'.$alumnoCompara->cohort1.'"';

						$alumnosCompara2 = $DB->get_records_sql($sql);

						/* echo '$alumnosCompara2 <br>';
						var_dump($alumnosCompara2); */

						if (!empty($alumnosCompara2) && count($alumnosCompara2) >= 1){
							foreach($alumnosCompara2 as $alumno){
								// Comparamos los cursos actuales y comparamos
								// con el alumno importado. Si no existe en el listado,
								// el curso es nuevo
								for ($ind = 1; $ind <= 20; $ind++) {

									$cursoCompara = $alumno->{'course' . $ind};
									if (!empty($cursoCompara)) {

										// Añadimos todos los cursos importados para la comparación
										if (!in_array($cursoCompara, $cursosNuevos)) {
											$cursosNuevos[] = $cursoCompara;
										}

										// Si el curso del registro en el excel no existe entre los del alumno actual
										// añadiremos ese curso como ALTA
										if (!in_array($cursoCompara, $cursosActuales)) {
											$cambios['cursos_alta'][] = 'course' . $ind;

											// if(($ind + 1) > $coursesColumns) $coursesColumns = $ind + 1;
										}
									}
								}
							}
						}

						$sql = "SELECT *
								FROM {itainnova_alumnos_bajas_cursos}
								WHERE email LIKE '$alumnoCompara->email'
								AND cohort1 = '$alumnoCompara->cohort1'";

						$alumnoBajaCursosUpdate = $DB->get_record_sql($sql);
						
						if(count($cursosNuevos) > $coursesColumns) $coursesColumns = count($cursosNuevos);

						if(!empty($alumnoBajaCursosUpdate)){
							
							$cursosBajaTemp = [];

							$contCursos = 1;
							// Añadimos los cursos de baja creados anteriormente en $alumnoBajaCursosUpdate
							for($ind=0;$ind<20;$ind++) {
								if(!empty($alumnoBajaCursosUpdate->{'course' . $contCursos})) {
									$cursosBajaTemp[] = $alumnoBajaCursosUpdate->{'course' . $contCursos};
									if($contCursos > $coursesColumns) $coursesColumns = $contCursos;
									$contCursos++;
								}
							}

							// Añadimos los cursos que faltan
							for($ind=0;$ind<20;$ind++) {
								$curso = $cursosActuales[$ind];
								if (!empty($curso) && !in_array($curso, $cursosBajaTemp) && !in_array($curso, $cursosNuevos)) {
									$alumnoBajaCursosUpdate->{'course' . $contCursos} = $curso;
									$cursosBajaTemp[] = $curso;
									if($contCursos > $coursesColumns) $coursesColumns = $contCursos;
									$contCursos++;
								}
							}

							// Comparamos los cursos actuales y comparamos
							// con los cursos nuevos. Si no existe en el listado,
							// el curso hay que darlo de baja
							for($ind=0;$ind<20;$ind++) {
								$curso = $cursosActuales[$ind];
								if(!empty($curso) && in_array($curso, $cursosBajaTemp)){
									$cambios['cursos_baja'][] = 'course' . ($ind+1);
								}
							}

						}else{
							// Comparamos los cursos actuales y comparamos
							// con los cursos nuevos. Si no existe en el listado,
							// el curso hay que darlo de baja
							$contCursos = 1;
							
							// var_dump($cursosActuales);
							// var_dump($cursosNuevos);

							foreach ($cursosActuales as $ind => $curso) {
								if (!in_array($curso, $cursosNuevos)) {
									$alumnosBajaCursos->{'course' . $contCursos} = $curso;
									$cambios['cursos_baja'][] = 'course' . ($ind + 1);
									if($contCursos > $coursesColumns) $coursesColumns = $contCursos;
									$contCursos++;
								}
							}
						}

						if (!empty($cambios['campos']) && in_array('email', $cambios['campos'])){
							// SI CONTIENE UN CAMBIO DE EMAIL, LO DEJAMOS EN EL LISTADO DE COMPARACIÓN PARA VERIFICAR MANUALMENTE EL CAMBIO
						} else {

							if (!empty($cursosNuevos)) {
								$contCursos = 1;
								foreach ($cursosNuevos as $ind => $curso) {
									if (!in_array($curso, $cursosActuales)) {
										$alumnosAltaCursos->{'course' . $contCursos} = $curso;
										if($contCursos > $coursesColumns) $coursesColumns = $contCursos;
										$contCursos++;
									}
								}
							}

							// ALTA CURSO ALUMNOS
							// Si existen cambios de alta a cursos los añadimos a la tabla
							if (!empty($cambios['cursos_alta'])) {
								$DB->insert_record('itainnova_alumnos_altas_cursos', $alumnosAltaCursos);

								$alumnoCompara->export_altas_bajas = 1;
								$DB->update_record('itainnova_alumnos_comparar', $alumnoCompara);
							}

							// var_dump($alumnosBajaCursos);
							// die();

							// BAJA CURSO ALUMNOS
							// Si existen cambios de baja a cursos los añadimos a la tabla
							if (!empty($cambios['cursos_baja'])) {
								if(empty($alumnoBajaCursosUpdate)){
									$DB->insert_record('itainnova_alumnos_bajas_cursos', $alumnosBajaCursos);
								}else{
									$DB->update_record('itainnova_alumnos_bajas_cursos', $alumnoBajaCursosUpdate);
								}

								$alumnoCompara->export_altas_bajas = 1;
								$DB->update_record('itainnova_alumnos_comparar', $alumnoCompara);
							}

							// Eliminamos los cambios
							// $cambios = array();
						}
					}
				} else {

					$cambios['campos'][] = 'NO_EXISTE';

					foreach ($camposCompara as $campo) {
						// if($alumnoCompara->{$campo} != $alumnoActual->{$campo}) $cambios[$alumnoActual->dni_nif][$alumnoActual->cohort1][$campo] = 1;
						if (empty($alumnoCompara->{$campo})) $cambios['campos'][] = $campo;
						elseif ($campo == 'email') {
							// Verificación de dominio de email
							list($user, $domain) = explode('@', $alumnoCompara->{$campo});
							if (!in_array($domain, $this->emailsValidos)) {
								$cambios['campos'][] = $campo;
							}
						}
					}
				}

				if (!empty($cambios['campos']) || !empty($cambios['cursos_alta'])) {

					$totalCambios = array_merge($cambios['campos'], $cambios['cursos_alta']);
					$totalCambios = implode(',', $totalCambios);

					// Si existen errores de curso, lo añadimos a una tabla específica
					// Los demás errores de tipo nombre, apellidos... Lo separamos para gestionar mejor estas modificaciones
					// Los que tengan error de email, van si o si a la tabla de errores, aún teniendo cursos dentro de los errores
					if (stripos($totalCambios, 'email') !== false) $tablaErrores = 'itainnova_alumnos_comparar_errores';
					else $tablaErrores = 'itainnova_alumnos_comparar_errores_cursos';
					/* elseif (stripos($totalCambios, 'course') !== false) $tablaErrores = 'itainnova_alumnos_comparar_errores_cursos';
					else $tablaErrores = 'itainnova_alumnos_comparar_errores';*/

					$errorAlumno = [
						'import_id' => $alumnoCompara->id,
						'errores' => $totalCambios,
						'estado' => !empty($cambios['cursos_alta']) ? 2 : 1
					];

					$insertData = json_decode(json_encode($errorAlumno), FALSE);

					$DB->insert_record($tablaErrores, $insertData);
				}

				if (!empty($alumnoActual) && !empty($cambios['cursos_baja'])) {

					$tablaErrores = 'itainnova_alumnos_comparar_errores_cursos_bajas';
					$errorAlumno = [
						'id_alumno' => $alumnoActual->id,
						'errores' => implode(',', $cambios['cursos_baja'])
					];

					$insertData = json_decode(json_encode($errorAlumno), FALSE);

					$DB->insert_record($tablaErrores, $insertData);
				}

				$cont++;
			}

			$importExcel = new ImportExcelAlumnos();
			
			// var_dump($coursesColumns);

			$importExcel->setCoursesColumns($coursesColumns);

			// Todos los que no hayan pasado por las tablas de errores los eliminamos
			// Ya que no se han podido comparar con la COHORTE ni CURSOS actuales
			// $alumnosSinErrores = $this->getAlumnosImportados(true);
			// $DB->insert_records('itainnova_alumnos_altas_cursos', $alumnosSinErrores);
			// $this->deleteAlumnosSinErrores();
		} else {
			// echo 'No existen datos en la tabla de comparación';
			// die();
		}
	}
}