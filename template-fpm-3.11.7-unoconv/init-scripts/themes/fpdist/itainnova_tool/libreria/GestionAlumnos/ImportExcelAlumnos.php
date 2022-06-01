<?php

/**
 * Clase que se realiza la importación del archivo de alumnos del SIGAD
 * y la ejecución del primer paso de tratamiento de datos
 */
class ImportExcelAlumnos
{

	private $target_dir = null;
	private $file = null;
	private $fileSrc = null;
	private $fileType = null;
	private $coursesColumns = 12;

	/**
	 * Constructor que necesita el nombre del campo de importación y el nombre de la carpeta
	 * donde se realizará la importación
	 *
	 * @param string $fileName
	 * @param string $importDir
	 */
	public function __construct($fileName = 'file', $importDir = 'importaciones_excel')
	{
		global $CFG;

		$this->target_dir = $CFG->itainnovatool_dir . '/' . $importDir . '/';
		$this->file = $_FILES[$fileName];
		$this->fileSrc = $this->target_dir . basename($this->file["name"]);
		$this->fileType = strtolower(pathinfo($this->fileSrc, PATHINFO_EXTENSION));
		$this->createTableAlumnosActual();
	}

	public function setCoursesColumns($coursesColumns){
		global $DB;

		$insertData = new stdClass;
		$insertData->courses_column_number = $coursesColumns;

		$DB->delete_records('itainnova_import_config');
		$DB->insert_record('itainnova_import_config', $insertData);

		$this->coursesColumns = TablaAlumnos::getCoursesColumns();
	}

	/**
	 * Crea la tabla temporal de mdl_itainnova_alumnos_actual para el fácil acceso y manejo de datos de los alumnos actuales
	 *
	 * @return void
	 */
	private function createTableAlumnosActual()
	{

		global $DB;

		$DB->delete_records('itainnova_alumnos_actual');

		$alumnosActualQuery = TablaAlumnos::getAlumnosMatriculadosActual();

		// Quería controlar el crear o no esta tabla temporal comparando el número de registros
		// Pero como podemos comprobar que efectivamente esos registros no se han cambiado?
		// Dejo la idea aquí para un futuro

		// $countAlumnosTable = $DB->count_records_sql('SELECT count(id) FROM {itainnova_alumnos_actual} WHERE 1=1');
		// if(count((array) $alumnosActualQuery) != $countAlumnosTable){
		$DB->insert_records('itainnova_alumnos_actual', $alumnosActualQuery);
		// }
	}

	/**
	 * Recuperación de módulos ITA
	 * Realiza una consulta utilizando el código del ciclo y el código del módulo
	 *
	 * @return void
	 */
	private function getModulosIta($codCiclo, $codModulo): object
	{
		global $DB;

		$sql = "SELECT *
			FROM {itainnova_cod_modulos}
			WHERE cod_ciclo='$codCiclo' AND cod_mod_dga='$codModulo'";

		$modulo = $DB->get_record_sql($sql);

		if(!empty($modulo)) return $modulo;
		else return new stdClass();
	}

	/**
	 * Función que utiliza las siglas del ciclo, con el código de módulo de la DGA
	 * para recuperar el nombre del curso de MOODLE.
	 * Verifica el parámetro $matriculaParcial para añadir el curso como OFERTA PARCIAL
	 *
	 * @param [type] $siglasCiclo
	 * @param [type] $modulosDga
	 * @param [type] $matriculaParcial
	 * @return void
	 */
	private function recuperaCursosMoodle($siglasCiclo, $modulosDga, $matriculaParcial)
	{
		$cursos = [];
		$cursosError = 1;

		foreach ($modulosDga as $moduloDga) {

			// Recuperamos los módulos de ITA para comparación
			if (!empty($moduloDga['cod_modulo']) && !empty($siglasCiclo)) {
				$moduloIta = $this->getModulosIta($siglasCiclo, $moduloDga['cod_modulo']);

				// Si existe el módulo
				if (!empty($moduloIta)) {

					// var_dump($moduloIta);

					// Eliminamos cursos de FCT
					if(stripos($moduloIta->cod_mod_moodle,'FCT') === false){

						// Verificamos el estado del ciclo
						// Si es MATRICULA es un alta o modificación de curso
						if ($moduloDga['estado_ciclo'] == 'MATRICULA') {
							if ($matriculaParcial == 1) $cursos['OK'][] = $moduloIta->cod_mod_moodle . '_OP';
							else $cursos['OK'][] = $moduloIta->cod_mod_moodle;
						} else {
							// De lo contrario es una baja de curso
							$cursos['BAJA'][] = $moduloIta->cod_mod_moodle;
						}
					}elseif(stripos($moduloIta->cod_mod_moodle,'FCT') !== false){
						$cursos['FCT'][] = $moduloIta->cod_mod_moodle;
					}
				} else {
					// Si no existe el módulo, lo guardamos como un error
					$cursos['ERROR'][$cursosError]['cod_mod_dga'] = $moduloDga['cod_modulo'];
					$cursos['ERROR'][$cursosError]['cod_ciclo'] = $siglasCiclo;
					$cursosError++;
				}
			} else {
				// Si  viene vacío alguno de los parametros, lo guardamos como un error
				$cursos['ERROR'][$cursosError]['cod_mod_dga'] = $moduloDga['cod_modulo'];
				$cursos['ERROR'][$cursosError]['cod_ciclo'] = $siglasCiclo;
				$cursosError++;
			}
		}

		// Ordenamos los cursos OK en orden alfabetico
		asort($cursos['OK']);

		return $cursos;
	}

	/**
	 * Función que realiza la importación del archivo y ejecuta la función tratarDatosSigad()
	 * para tratar los datos.
	 *
	 * @return void
	 */
	public function upload()
	{
		$check = $this->file["size"];

		if ($check !== false) {

			if (move_uploaded_file($this->file["tmp_name"], $this->fileSrc)) {
				$this->tratarDatosSigad();
				return basename($this->file["name"]);
			} else {
				echo $this->file["tmp_name"] . '<br>';
				echo $this->fileSrc . '<br>';
				die('Error al subir archivo');
			}
		} else {
			die('Error al subir archivo');
		}
	}

	/**
	 * Realiza la comprobación y limpieza de datos a partir del Excel original.
	 * Una vez Importado añadimos todos los datos a sus correspondientes tablas utilizando
	 * el método insertAlumnos()
	 *
	 * @return void
	 */
	public function tratarDatosSigad()
	{

		global $DB;

		// PASO 1
		// Eliminamos todos los registros relacionados con el mismo nombre de archivo
		// para evitar duplicidades
		// $conditions['archivo_excel'] = basename($this->file["name"]);
		$conditions = null;
		$DB->delete_records('itainnova_import_config');
		$DB->delete_records('itainnova_cod_modulos_error', $conditions);
		$DB->delete_records('itainnova_alumnos_comparar', $conditions);
		$DB->delete_records('itainnova_alumnos_altas', $conditions);
		$DB->delete_records('itainnova_alumnos_altas_cursos', $conditions);
		$DB->delete_records('itainnova_alumnos_bajas', $conditions);
		$DB->delete_records('itainnova_alumnos_bajas_cursos', $conditions);
		$DB->delete_records('itainnova_alumnos_comparar_errores', $conditions);
		$DB->delete_records('itainnova_alumnos_comparar_errores_cursos', $conditions);

		// Leemos el archivo EXCEL para recuperar los datos
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->fileSrc);
		$worksheet = $spreadsheet->getActiveSheet();

		// Get the highest row number and column letter referenced in the worksheet
		$highestRow = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
		// Increment the highest column letter
		$highestColumn++;

		// Variable tipo array para guardar los alumnos
		$alumnos = [];

		// Número máximo de cursos
		$coursesColumnsAlta = 1;
		$coursesColumnsBaja = 1;

		// Por cada fila
		for ($row = 2; $row <= $highestRow; ++$row) {

			$tipoMatricula = '';
			$siglasCiclo = '';
			$matriculaParcial = '';
			$buscaAlumno = null;
			$cursos = [];

			// Por cada columna
			$estadoAlumno = 0;
			for ($col = 'A'; $col != $highestColumn; ++$col) {
				$cellVal = $worksheet->getCell($col . $row)->getValue();

				// echo $col.': '.$cellVal.'<br>';

				if ($col == 'A') {
					$tipoMatricula = trim($cellVal);
					if (empty($tipoMatricula)) break;

					switch ($tipoMatricula) {
						case 'Matricula Formalizada':
							$estadoAlumno = 1;
							break;
						case 'Matricula de baja':
							$estadoAlumno = 2;
							break;
						case 'Anulación de Matrícula':
							$estadoAlumno = 3;
							break;
					}

					$alumnos[$estadoAlumno][$row] = [];
					$alumnos[$estadoAlumno][$row]['estado'] = $estadoAlumno;
					$alumnos[$estadoAlumno][$row]['archivo_excel'] = basename($this->file["name"]);
					$alumnos[$estadoAlumno][$row]['date_insert'] = date('Y-m-d H:i:s');
				} elseif ($col == 'D') {
					if (stripos($cellVal, 'BLECUA') !== false || stripos($cellVal, 'MALLADA')  !== false) {
						unset($alumnos[$estadoAlumno][$row]);
						break;
					} elseif (stripos($cellVal, 'SIERRA DE GUARA') !== false) {
						$siglasCiclo = 'ADG205';
					} elseif (stripos($cellVal, 'TIEMPOS MODERNOS')  !== false) {
						$siglasCiclo = 'ADG204';
					} elseif (stripos($cellVal, 'VEGA DEL TURIA')  !== false) {
						$siglasCiclo = 'SAN204';
					} elseif (stripos($cellVal, 'MONTEARAGÓN')  !== false) {
						$siglasCiclo = 'SSC204';
					} elseif (stripos($cellVal, 'MARTÍNEZ VARGAS')  !== false) {
						$siglasCiclo = 'SSC304';
					}
				} elseif ($col == 'E') {
					$alumnos[$estadoAlumno][$row]['dni_nif'] = trim(mb_strtoupper($cellVal));
				} elseif ($col == 'F') {
					$alumnos[$estadoAlumno][$row]['firstname'] = trim(mb_strtoupper($cellVal));
				} elseif ($col == 'G') {
					$alumnos[$estadoAlumno][$row]['lastname'] = trim(mb_strtoupper($cellVal));
				} elseif ($col == 'H') {
					if (!empty(trim($cellVal))){
						if(!empty($alumnos[$estadoAlumno][$row]['lastname'])){
							$alumnos[$estadoAlumno][$row]['lastname'] .= ' ' . trim(mb_strtoupper($cellVal));
						}else{
							$alumnos[$estadoAlumno][$row]['lastname'] = trim(mb_strtoupper($cellVal));
						}
					}

					$alumnos[$estadoAlumno][$row]['lastname'] = trim($alumnos[$estadoAlumno][$row]['lastname']);

				} elseif ($col == 'I') {
					/* if (empty($cellVal)) {
						unset($alumnos[$estadoAlumno][$row]);
						break;
					} */
					$alumnos[$estadoAlumno][$row]['email'] = mb_strtolower(trim($cellVal));
				} elseif ($col == 'J') {
					$alumnos[$estadoAlumno][$row]['city'] = mb_strtoupper(trim($cellVal));
				} elseif ($col == 'L') {
					if (stripos($cellVal, 'Bachillerato') !== false) {
						break;
					}
				} elseif ($col == 'N') {
					if (empty($siglasCiclo)) $siglasCiclo = $cellVal;
				} elseif ($col == 'O') {
					$modulos = explode(';', $cellVal);
					$modulosDga = [];
					$x = 0;
					$i = 0;
					foreach ($modulos as $ind => $moduloVal) {
						if ($x == 0) {
							$modulosDga[$i]['cod_modulo'] = $moduloVal;
						} elseif ($x == 1) {
							$modulosDga[$i]['nom_ciclo'] = $moduloVal;
						} elseif ($x == 2) {
							$modulosDga[$i]['estado_ciclo'] = $moduloVal;
						}
						$x++;

						if (($ind + 1) % 3 == 0) {
							$x = 0;
							$i++;
						}
					}

				} elseif ($col == 'Q') {
					$alumnos[$estadoAlumno][$row]['idficha'] = $cellVal;
				} elseif ($col == 'P') {
					// $matriculaParcial = $cellVal;
					// Hasta nuevo aviso, las ofertas parciales no existen
					$matriculaParcial = 0;
					$alumnos[$estadoAlumno][$row]['matricula_parcial'] = $matriculaParcial;
				}
			}

			// var_dump($alumnos[$estadoAlumno][$row]);

			if (!empty($alumnos[$estadoAlumno][$row])) {

				// BUSCA A PATIR DE SIGLAS, MODULO Y MATRICULA PARCIAL EL Curso DE MOODLE
				$cursos = $this->recuperaCursosMoodle($siglasCiclo, $modulosDga, $matriculaParcial);

				// var_dump($cursos);

				// ERROR DE Curso (NO SE ENCUENTRA EN BBDD)
				if (!empty($cursos['ERROR'])) {
					$this->insertErrorCursos($row, $alumnos[$estadoAlumno][$row]['dni_nif'], $cursos['ERROR']);
				}

				$i = 1;
				$curso1 = '';
				$cursosOk = $cursos['OK'];

				for ($i = 0; $i <= 20; $i++) {
					if ($i == 0) $curso1 = $cursosOk[$i];
					if (!empty($cursosOk[$i])){
						$alumnos[$estadoAlumno][$row]['course' . ($i + 1)] = $cursosOk[$i];
						if(($i + 1) > $coursesColumnsAlta) $coursesColumnsAlta++;
					}
					else $alumnos[$estadoAlumno][$row]['course' . ($i + 1)] = '';
				}

				// COHORTE
				// Si no existe el curso1 es que no tiene cursos
				if (!empty($siglasCiclo) && !empty($curso1)) {
					$curso1 = explode('_', $curso1);
					$alumnos[$estadoAlumno][$row]['cohort1'] = 'C_' . $curso1[0] . '_' . $curso1[1];
					if ($matriculaParcial) $alumnos[$estadoAlumno][$row]['cohort1'] .= '_OP';
				} elseif (!empty($cursos['BAJA'])) {
					// NO EXISTEN CURSOS DE ALTA SOLO DE BAJA
					// Cabiamos el estado del alumno a 3 BAJA MATRICULA
					// En estos casos los alumnos convalidan todo el curso
					$alumnoBaja = $alumnos[$estadoAlumno][$row];
					// Eliminamos el alumno del array original
					unset($alumnos[$estadoAlumno][$row]);

					$estadoAlumno = 3;
					$alumnos[$estadoAlumno][$row] = $alumnoBaja;
					$alumnos[$estadoAlumno][$row]['estado'] = $estadoAlumno;

					$cursosKo = $cursos['BAJA'];
					for ($i = 0; $i < 20; $i++) {
						if ($i == 0) {
							$curso1 = $cursosKo[$i];
							break;
						}
					}

					$curso1 = explode('_', $curso1);
					$alumnos[$estadoAlumno][$row]['cohort1'] = 'C_' . $curso1[0] . '_' . $curso1[1];
					if ($matriculaParcial) $alumnos[$estadoAlumno][$row]['cohort1'] .= '_OP';

				} else {
					// Eliminamos el alumno del array original
					unset($alumnos[$estadoAlumno][$row]);
					continue;
				}

				$alumnos[$estadoAlumno][$row]['username'] = '';
				$alumnos[$estadoAlumno][$row]['password'] = '';

				if (!empty($alumnos[$estadoAlumno][$row])
					&& $buscaAlumno == null) {

					$buscaAlumno = $alumnos[$estadoAlumno][$row];
					
					// echo '$buscaAlumno';
					// var_dump($buscaAlumno);

					$params = [
						'email' =>  $buscaAlumno['email'],
						'dni_nif' =>  $buscaAlumno['dni_nif'],
						'cohort' => $buscaAlumno['cohort1']
					];

					// Intentamos recuperar el alumno buscando entre los actualmente matriculados
					$matriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($params, false);
					$alumnosActual = $matriculadosActual['resultAlumno'];

					// echo '$alumnosActual';
					// var_dump($alumnosActual);

					// Si no existe el alumno, ES NUEVO
					if ($matriculadosActual['result'] == 'KO') {
						if(!empty($alumnos[$estadoAlumno][$row]['email'])
							&& !empty($alumnos[$estadoAlumno][$row]['dni_nif'])){

							// ¿Existen casos en el que puede no estar dado de alta?
							if ($estadoAlumno == 1 && !empty($alumnos[$estadoAlumno][$row]['cohort1'])) {
								$email = $alumnos[$estadoAlumno][$row]['email'];
								list($user, $pass) = $this->createUserPass($email);

								$alumnos[$estadoAlumno][$row]['username'] = $user;
								$alumnos[$estadoAlumno][$row]['password'] = $pass;

								$alumnoNuevo = $alumnos[$estadoAlumno][$row];

								// Eliminamos el alumno del array original
								unset($alumnos[$estadoAlumno][$row]);

								// Creamos el nuevo estado 4 para añadirlo al array de ALTAS DE ALUMNO
								$estadoAlumno = 4;
								$alumnos[$estadoAlumno][$row] = $alumnoNuevo;
								$alumnos[$estadoAlumno][$row]['estado'] = $estadoAlumno;

							} elseif($estadoAlumno == 2 || $estadoAlumno == 3){

								$params = [
									'dni_nif' => $buscaAlumno['dni_nif'],
									'email' => $buscaAlumno['email'],
								];

								$usuario = TablaAlumnos::getAlumnosMatriculadosActual($params, true);
								
								if(!empty($usuario['result'] == 'OK')){
									$usuario = $usuario['resultAlumno'];
									$alumnos[$estadoAlumno][$row]['username'] = $usuario->username;
									$alumnos[$estadoAlumno][$row]['password'] = $usuario->password;
								}
							}
						} else {

							$params = [
								'email' =>  $buscaAlumno['email'],
								'dni_nif' =>  $buscaAlumno['dni_nif']
							];

							$matriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($params);
							$alumnoActual = $matriculadosActual['resultAlumno'];

							if(!empty($alumnoActual->email)){
								$alumnos[$estadoAlumno][$row]['email'] = $alumnoActual->email;
							}

							if(!empty($alumnoActual->username)){
								$alumnos[$estadoAlumno][$row]['username'] = $alumnoActual->username;
							}

							$DB->insert_record('itainnova_alumnos_comparar', $alumnos[$estadoAlumno][$row]);

							// Eliminamos el alumno
							unset($alumnos[$estadoAlumno][$row]);
						}

					} elseif ($matriculadosActual['result'] == 'OK') {

						// Pueden haber varios registros del mismo alumno
						foreach ($alumnosActual as $alumnoActual) {
							if(!empty($alumnoActual->password)){
								$alumnos[$estadoAlumno][$row]['username'] = $alumnoActual->username;
								$alumnos[$estadoAlumno][$row]['password'] = $alumnoActual->password;
							}
							break;
						}
					}
				}

				// Si los alumnos no son nuevos pueden tener cursos de baja
				if($estadoAlumno != 4){

					$buscaAlumno = $alumnos[$estadoAlumno][$row];
					/* echo '$buscaAlumno';
					var_dump($buscaAlumno); */

					$params = [
						'username' =>  $buscaAlumno['username'],
						'email' =>  $buscaAlumno['email'],
						'dni_nif' =>  $buscaAlumno['dni_nif']
					];

					$matriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($params);
					$alumnoActual = $matriculadosActual['resultAlumno'];

					if(!empty($alumnoActual->email) && empty($buscaAlumno['email'])){
						$alumnos[$estadoAlumno][$row]['email'] = $alumnoActual->email;
					}

					if(!empty($alumnoActual->username) && empty($buscaAlumno['username'])){
						$alumnos[$estadoAlumno][$row]['username'] = $alumnoActual->username;
					}					

					if(!empty($cursos['BAJA'])){
						$alumnoBajasCurso = $this->insertBajaCursos($alumnos[$estadoAlumno][$row], $cursos['BAJA'], $siglasCiclo, $matriculaParcial);

						if(empty($cursos['OK']) && !empty($alumnoBajasCurso)) {
							// var_dump($alumnoBajasCurso);
							// Creamos un alumno para insertar en la comparación
							$alumnoCompararBaja = $alumnos[$estadoAlumno][$row];

							$alumnoCompararBaja['cohort1'] = $alumnoBajasCurso['cohort1'];
							$alumnoCompararBaja['username'] = $alumnoBajasCurso['username'];
							$alumnoCompararBaja['password'] = $alumnoBajasCurso['password'];
							
							// Los alumnos sin cursos de alta primero añadimos los datos de comparación
							$DB->insert_record('itainnova_alumnos_comparar', $alumnoCompararBaja);

							// Actualizamos con todos los cursos de baja del alumno
							$alumnos[$estadoAlumno][$row] = $alumnoBajasCurso;
						}
					}
					
				} else {
					if(empty($alumnos[$estadoAlumno][$row]['cohort1'])){
						unset($alumnos[$estadoAlumno][$row]);
					}

					if(empty($alumnos[$estadoAlumno][$row]['course1'])){
						unset($alumnos[$estadoAlumno][$row]);
					}
				}
			}
		}

		// var_dump($alumnos);
		// var_dump($coursesColumnsAlta);
		// var_dump($coursesColumnsBaja);

		// $this->setCoursesColumns($coursesColumns);
		// die();

		// 'Matricula Formalizada'
		if (!empty($alumnos[1])) $this->insertAlumnos($alumnos[1], 1);
		// 'Matricula de baja'
		if (!empty($alumnos[2])) $this->insertAlumnos($alumnos[2], 2);
		// 'Anulación de Matrícula'
		if (!empty($alumnos[3])) $this->insertAlumnos($alumnos[3], 3);
		// Casos en el que puede que el alumno no esté dado de alta
		if (!empty($alumnos[4])) $this->insertAlumnos($alumnos[4], 4);

		// die();
	}

	/**
	 * Método utilizado por getToken para devolver valores random
	 *
	 * @param [type] $min
	 * @param [type] $max
	 * @return void
	 */
	private function crypto_rand_secure($min, $max)
	{
		$range = $max - $min;
		if ($range < 1) return $min; // not so random...
		$log = ceil(log($range, 2));
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd > $range);
		return $min + $rnd;
	}

	/**
	 * Método para crear texto alfanumérico
	 * Sirve para crear la contraseña
	 *
	 * @param [type] $length
	 * @return void
	 */
	private function getToken($length)
	{
		$token = "";
		$codeAlphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ";
		$codeAlphabet .= "0123456789";
		$max = strlen($codeAlphabet); // edited

		for ($i = 0; $i < $length; $i++) {
			$token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
		}

		return $token;
	}

	/**
	 * Método de Creación de Password
	 * Utiliza el email dividiéndolo a partir del @ y quedando solo la primera parte,
	 * añade un punto y agrega 4 caracteres aleatorios.
	 * Ejemplo: hola@prueba.com = hola.1234
	 *
	 * @param string $email
	 * @return void
	 */
	private function createUserPass($email = '')
	{
		$userBase = explode('@', $email);
		$user = strtolower($userBase[0]);
		$pass = $user . '.' . $this->getToken(4);

		return array($user, $pass);
	}

	/**
	 * Inserta en la tabla {itainnova_alumnos_bajas_cursos} los alumnos con bajas de cursos
	 *
	 * @param array $alumno
	 * @param array $cursos
	 * @return void
	 */
	private function insertBajaCursos($alumno = array(), $cursos = array(), $siglasCiclo, $matriculaParcial)
	{
		global $DB;

		try {

			$transaction = $DB->start_delegated_transaction();

			$params = [
				'username' =>  $alumno['username'],
				'email' =>  $alumno['email'],
				'dni_nif' =>  $alumno['dni_nif'],
				'cohort' =>  $alumno['cohort1']
			];

			$matriculadosActual = TablaAlumnos::getAlumnosMatriculadosActual($params, true);

			if ($matriculadosActual['result'] == 'OK') {
				$alumnoActual = $matriculadosActual['resultAlumno'];
				$cursosActuales = explode(',', $alumnoActual->courses);
			}				

			if (!empty($cursos)) {
				asort($cursos);

				$curso1 = '';
				$cursosBaja = [];
				for ($i = 0; $i <= 20; $i++) {
					if (empty($curso1)) $curso1 = $cursos[$i];

					// Si los cursos que vamos a dar de baja existen en los cursos actuales
					// entonces añadimos los cursos al array
					if (!empty($cursosActuales) && in_array($cursos[$i], $cursosActuales)) {
						$cursosBaja[] = $cursos[$i];
					}

					if (!empty($cursos[$i])) $alumno['course' . ($i + 1)] = $cursos[$i];
					else $alumno['course' . ($i + 1)] = '';
				}

				// COHORTE
				if (!empty($siglasCiclo)) {
					$curso1 = explode('_', $curso1);
					if(!empty($curso1[0])){
						$alumno['cohort1'] = 'C_' . $curso1[0] . '_' . $curso1[1];
						if ($matriculaParcial) $alumno['cohort1'] .= '_OP';
					}
				}

				$insertData = json_decode(json_encode($alumno), FALSE);

				// Si existen cursos de baja añadimos el alumno a la tabla
				if(!empty($cursosBaja)){
					$DB->insert_record('itainnova_alumnos_bajas_cursos', $insertData);
				}
			}
			
			$transaction->allow_commit();

			return $alumno;
		} catch (Exception $e) {
			$transaction->rollback($e);
			var_dump($e);
			die();
		}
	}

	/**
	 * Inserta errores de cursos no encontrados
	 * Utiliza el $row (número de fila del excel), el $dni_nif (DNI del alumno) y $cursos (array de cursos no encontrados)
	 * para insertar los datos en la tabla {itainnova_cod_modulos_error}
	 *
	 * @param [type] $row
	 * @param [type] $dni_nif
	 * @param [type] $cursos
	 * @return void
	 */
	private function insertErrorCursos($row, $dni_nif, $cursos)
	{
		global $DB;

		try {

			$transaction = $DB->start_delegated_transaction();

			if (!empty($cursos)) {

				foreach ($cursos as $curso) {
					if (!empty($curso['cod_ciclo'])) {
						$insertData = new stdClass();
						$insertData->row_id = $row;
						$insertData->alumno_dni_nif = $dni_nif;
						$insertData->cod_ciclo = $curso['cod_ciclo'];
						$insertData->cod_mod_moodle = $curso['cod_mod_moodle'];
						$insertData->cod_mod_dga = $curso['cod_mod_dga'];

						$DB->insert_record('itainnova_cod_modulos_error', $insertData);
					}
				}
			}

			// Assuming the both inserts work, we get to the following line.
			$transaction->allow_commit();
		} catch (Exception $e) {
			$transaction->rollback($e);
			var_dump($e);
			die();
		}
	}

	/**
	 * Método para insertar cada serie de alumnos en las distintas tablas
	 * $estadoAlumno = 1: ERRORES / PROCESO COMPARAR
	 * $estadoAlumno = 2 y 3: BAJAS
	 * $estadoAlumno = 4: ALTAS
	 * $insertData = Array con datos de alumnos. Deben tener todos la misma estructura o número de datos, si no devolverá un error.
	 *
	 * @param array $insertData
	 * @param integer $estadoAlumno
	 * @return void
	 */
	private function insertAlumnos($insertData = array(), $estadoAlumno = 0)
	{
		global $DB;

		try {

			$transaction = $DB->start_delegated_transaction();
			switch ($estadoAlumno) {
				case 1:
					$DB->insert_records('itainnova_alumnos_comparar', $insertData);
					break;
				case 2:
					$DB->insert_records('itainnova_alumnos_bajas', $insertData);
					break;
				case 3:
					$DB->insert_records('itainnova_alumnos_bajas', $insertData);
					break;
				case 4:
					$DB->insert_records('itainnova_alumnos_altas', $insertData);
					break;
			}

			// Assuming the both inserts work, we get to the following line.
			$transaction->allow_commit();
		} catch (Exception $e) {
			$transaction->rollback($e);
			var_dump($e);
			die();
		}
	}
}