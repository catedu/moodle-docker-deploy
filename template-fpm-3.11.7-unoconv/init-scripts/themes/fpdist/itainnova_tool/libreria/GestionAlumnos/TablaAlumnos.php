<?php

require "$CFG->libdir/tablelib.php";
require "$CFG->itainnovatool_libdir/PhpSpreadsheet/vendor/autoload.php";

/**
 * Clase que realiza las operaciones de consulta y renderización de datos de los distintas
 * tablas creadas por la importación de alumnos
 */

class TablaAlumnos
{

	/**
	 * Select para obtener los datos actuales de la BBDD
	 * IMPORTANTE - Dato <<uniqueid>>. Es necesario crear un campo único ya que MOODLE agrupa los datos según
	 * un id único de la primera columna. Si el primer dato no es único, es decir que se repite en varias filas
	 * , como es nuestro caso con username, la función de get_records_sql lo agrupará automáticamente.
	 * Se puede comprobar que si eliminamos el uniqueid, ejecutando la SQL en phpmyadmin
	 * funciona y devuelve un número de registros, pero si luego ejecutamos la misma SQL, elimina
	 * los repetidos.
	 *
	 * @var string
	 */
	public static $selectAlumnosFields = "
		CONCAT({user}.`username`, {cohort}.`idnumber`) AS `uniqueid`,
		{user}.`username` AS `username`,
		ppropio.`ppropio` AS `password`,
		{user}.`firstname` AS `firstname`,
		{user}.`lastname` AS `lastname`,
		{user}.`email` AS `email`,
		{user}.`city` AS `city`,
		{user_info_data}.`data` AS `dni_nif`,
		GROUP_CONCAT(
			distinct {course}.`shortname` separator ','
		) AS `courses`,
		{cohort}.`idnumber` AS `cohort1`
	";

	public static $selectAlumnosFrom = "
			{role_assignments}
			LEFT JOIN {user} ON {user}.`id` = {role_assignments}.`userid`
			LEFT JOIN {context} ON {role_assignments}.`contextid` = {context}.`id`
			JOIN ppropio ON {user}.`id` = ppropio.`idd`
			JOIN {user_info_data} ON {user}.`id` = {user_info_data}.`userid`
			JOIN {user_enrolments} ON {user_enrolments}.`userid` = {user}.`id`
			JOIN {enrol} ON {enrol}.`id` = {user_enrolments}.`enrolid`
			JOIN {course} ON {course}.`id` = {enrol}.`courseid`
			JOIN {cohort_members} ON {cohort_members}.`userid` = {user}.`id`
			JOIN {cohort} ON {cohort}.`id` = {cohort_members}.`cohortid`
	";

	public static $selectAlumnosWhere = "
			NOT {user}.`firstname` LIKE '%\\_%'
			AND {user}.`deleted` = 0
			AND {user_info_data}.`fieldid` = 1
			AND {role_assignments}.`roleid` = 5
			AND {user_enrolments}.`status` = 0
			AND {course}.`shortname` <> 'AYUDA'
			AND NOT(
				{context}.`instanceid` IN (
					SELECT
						{course}.`id`
					FROM
						{course}
					WHERE
						{course}.`category` IN (0, 18, 26, 27, 38, 52, 53)
						OR {course}.`shortname` LIKE '%\\_TU'
				)
			)
			AND NOT {course}.`shortname` LIKE '%\\_TU'
			AND NOT {course}.`shortname` LIKE '%\\_TU\\_OP'
			AND (
				{course}.`shortname` LIKE CONCAT(
					RIGHT(
						{cohort}.`idnumber`,(
							length(
								{cohort}.`idnumber`
							) - 2
						)
					),
					'\\_%'
				)
				OR
				{course}.`shortname` LIKE CONCAT(
					substr(
						{cohort}.`idnumber`,
						3,(
							length(
								{cohort}.`idnumber`
							) - 5
						)
					),
					'\\_%\\_OP'
				)
			)
	";

	public static $selectAlumnosGroupBy = "
			uniqueid,
			{user}.`username`,
			ppropio.`ppropio`,
			{user}.`firstname`,
			{user}.`lastname`,
			{user}.`email`,
			{user}.`city`,
			{user_info_data}.`data`,
			cohort1
	";

	/**
	 * Columnas visibles Tabla / Excel
	 *
	 * @var array
	 */
	private $columns = [
		'id', 'firstname', 'lastname', 'username', 'email', 'dni_nif'
	];

	/**
	 * Cabeceras Tabla / Excel
	 *
	 * @var array
	 */
	private $headers = [
		'ID', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE'
	];

	/**
	 * Establece el número de columnas de cursos
	 * En algunos años ha llegado a ampliarse hasta 16
	 * Recordar cambiar (añadir / eliminar) el número de columnas en la bbdd
	 *
	 * @var integer
	 */
	private $coursesColumns = 12;

	/**
	 * URL Base
	 * Moodle utiliza esta url para realizar los filtros y paginación
	 *
	 * @var [type]
	 */
	private $baseUrl;

	/**
	 * Constructor
	 * $baseUrl es necesario si vamos a mostrar un listado utilizando el método getListAlumnosHTML()
	 *
	 * @param [type] $baseUrl
	 */
	public function __construct($baseUrl = null)
	{
		$this->baseUrl = $baseUrl;
		$this->coursesColumns = self::getCoursesColumns();
	}

	/**
	 * Método auxiliar para imprimir el tiempo con milisegundos
	 * Utilizado para medir el tiempo de ejecución de algunos métodos
	 *
	 * @return void
	 */
	public function printTime()
	{
		$micro_date = microtime();
		$date_array = explode(" ", $micro_date);
		$date = date("Y-m-d H:i:s", $date_array[1]);
		echo "Date: $date:" . $date_array[0] . "<br>";
	}

	/**
	 * Método para recuperar los datos de los alumnos actualmente matriculados
	 * Podemos enviar un array con parametros para realizar la búsqueda en la tabla temporal mdl_itainnova_alumnos_actual (Consulta rápida y se puede filtrar con $params)
	 * Si $params está vacío realizará una "consulta real" utilizando las tablas de moodle (Tarda mas y devuelve todos los datos)
	 * $single es un boolean con el que especificaremos si queremos que el método nos devuelva un sólo registro y asignarlo a una variable o varios para realizar una iteración.
	 *
	 * @param array $params
	 * @param boolean $single
	 * @return void
	 */
	public static function getAlumnosMatriculadosActual($params = array(), $single = true)
	{
		global $DB;

		if (empty($params)) {

			$select = self::$selectAlumnosFields;
			$from = self::$selectAlumnosFrom;
			$where = self::$selectAlumnosWhere;
			$groupBy = self::$selectAlumnosGroupBy;

			$sql = "SELECT
					$select
					FROM $from
					WHERE $where
					GROUP BY $groupBy";

			return $DB->get_records_sql($sql);
			
		} else {
			
			$email = explode('@', $params['email']);

			$dni_nif = null;
			if (!empty($params['dni_nif'])) $dni_nif = $params['dni_nif'];

			if (!empty($params['username'])) $username = $params['username'];
			else $username = $email[0];

			if (!empty($params['cohort'])) $cohort = $params['cohort'];

			// No se puede buscar datos para comparar si todos estos datos están vacíos
			if (empty($dni_nif) && empty($email[0]) && empty($username)) {
				return array('result' => 'KO', 'emptyData' => true, 'resultAlumno' => null);
			}

			$select = "*";

			$from = "{itainnova_alumnos_actual} alumno";

			if (!empty($params['errores'])) {
				$from .= ' LEFT JOIN {itainnova_alumnos_comparar_errores_cursos_bajas} error_bajas ON alumno.id = error_bajas.id_alumno';
			}

			$where = " 1=1 AND ( ";
			if(!empty($username)){
				$where .= " alumno.username LIKE '$username' ";
			}

			if(!empty($params['email'])) {
				if(!empty($username)) $where .= ' OR ';
				$where .= " alumno.email LIKE '".$params['email']."' ";
			}

			if (!empty($dni_nif)) {
				if(!empty($username) || !empty($email[0])) $where .= ' OR ';
				$where .= " alumno.dni_nif LIKE '%$dni_nif%' ";
			}

			$where .= " ) ";

			if (!empty($cohort)) {
				$where .= " AND alumno.cohort1 LIKE '$cohort' ";
			}

			$groupBy = "
				alumno.username,
				alumno.firstname,
				alumno.lastname,
				alumno.email,
				alumno.dni_nif
			";

			$sql = "SELECT
					$select
					FROM $from
					WHERE $where
					GROUP BY $groupBy";

			if ($single == false) {
				$alumnos = $DB->get_records_sql($sql);
			} else {
				$alumnos = $DB->get_record_sql($sql);
			}

			if (empty($alumnos)) {
				return array('result' => 'KO', 'resultAlumno' => null);
			} else {
				return array('result' => 'OK', 'resultAlumno' => $alumnos);
			}
		}
	}

	/**
	 * Recupera el número de columnas de cursos para gestionar las iteraciones de cursos
	 *
	 * @return void
	 */
	public static function getCoursesColumns(){
		global $DB;

		$sql = 'SELECT * FROM {itainnova_import_config} where 1=1';
		$itaInnovaConfig = $DB->get_record_sql($sql);

		return $itaInnovaConfig->courses_column_number;
	}

	/**
	 * Método para recuperar los datos de los alumnos importados con errores
	 * para realizar la comparación. Recibe como parámetro necesario un array
	 * en el que se añaden los datos del alumno para buscarlo en las tablas de
	 * itainnova_alumnos_comparar, itainnova_alumnos_comparar_errores,
	 * itainnova_alumnos_comparar_errores_cursos
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getAlumnosMatriculadosComparar($params = array(), $columnasErrores = true)
	{
		global $DB;

		$coursesColumns = self::getCoursesColumns();

		// No se puede buscar datos para comparar si el parámetro está
		if (empty($params)) {
			return array('result' => 'KO', 'emptyData' => true);
		}

		$dni_nif = null;
		if (!empty($params['dni_nif'])) $dni_nif = $params['dni_nif'];
		$email = explode('@', $params['email']);
		$username = $params['username'];
		$cohort = $params['cohort'];

		if($columnasErrores){
			$select = 'comparar.id
				,comparar.export_altas_bajas
				,comparar.firstname AS firstname
				,comparar.lastname AS lastname
				,comparar.username AS username
				,comparar.email AS email
				,comparar.dni_nif AS dni_nif
				,comparar.city AS city
				,comparar.cohort1 AS cohort1
				,errores_curso.errores AS errores_curso
				,error.errores AS errores_datos';

			// Añadimos al select los cursos según el número configurado
			for ($x = 1; $x <= $coursesColumns; $x++) {
				$select .= ',comparar.course' . $x . ' AS course' . $x;
			}

			$from = '{itainnova_alumnos_comparar} comparar
				LEFT JOIN {itainnova_alumnos_comparar_errores_cursos} errores_curso ON comparar.id = errores_curso.import_id
				LEFT JOIN {itainnova_alumnos_comparar_errores} error ON comparar.id = error.import_id
			';
		}else{
			$select = '*';

			// Añadimos al select los cursos según el número configurado
			for ($x = 1; $x <= $coursesColumns; $x++) {
				$select .= ',comparar.course' . $x . ' AS course' . $x;
			}

			$from = '{itainnova_alumnos_comparar} comparar';
		}

		// No se puede buscar datos para comparar si todos estos datos están vacíos
		if (empty($dni_nif) && empty($email[0]) && empty($username)) {
			return array('result' => 'KO', 'emptyData' => true);
		}

		$where = " (
			comparar.username LIKE '%$username%'
			OR comparar.email LIKE '%$email[0]%'
		";

		if (!empty($dni_nif)) {
			$where .= " OR comparar.dni_nif LIKE '%$dni_nif%' ";
		}

		$where .= " ) ";

		if (!empty($cohort)) {
			$where .= " AND comparar.cohort1 LIKE '$cohort' ";
		}

		if(isset($params['export_altas_bajas'])){
			$where .= " AND comparar.export_altas_bajas=".$params['export_altas_bajas']." ";
		}

		$sql = "SELECT
				$select
                FROM $from
				WHERE $where";

		/* echo $sql;
		die(); */

		$alumnos = $DB->get_record_sql($sql);

		if (empty($alumnos)) {
			return array('result' => 'KO', 'resultAlumno' => null);
		} else {
			return array('result' => 'OK', 'resultAlumno' => $alumnos);
		}
	}

	/**
	 * Crea un listado de los alumnos utilizando los parámetros para crear el nombre de la tabla
	 * y los datos recuperados por SQL
	 * $tableName y $exportTableName deben ser únicos, en el caso de utilicemos este método más de una vez
	 * para renderizar datos en la misma página
	 * Para crear la tabla se utiliza la clase table_sql_alumnos que extiende de la clase table_sql
	 * original de Moodle para añadir los métodos de renderizado y exportación
	 *
	 * @return void
	 */
	public function getListAlumnosHTML($tableName = 'listado-alumnos', $exportTableName = 'Listado de Alumnos')
	{

		$table = new table_sql_alumnos($tableName);

		$download = optional_param('download', '', PARAM_ALPHA);
		$table->is_downloading($download, $exportTableName, 'Listado');

		// El sistema no reconoce que se está realizando una descarga hasta que se
		// instancia la tabla (new table_sql_alumnos) y se llama a la función is_downloading,
		// por lo que en el caso de los listados al realizar una descarga, las consultas de
		// tipos de altas y bajas de laumnos los volvemos a asignar desde este apartado


		if($tableName == 'listado-alumnos-bajas') $tableAlumno = 'alumno.';
		else $tableAlumno = '';

		if ($table->is_downloading()) {

			$this->columns = [
				'username', 'password', 'firstname', 'lastname', 'email', 'city', 'dni_nif', 'cohort1'
			];

			$this->headers = [
				'username', 'password', 'firstname', 'lastname', 'email', 'city', 'profile_field_NIF', 'cohort1'
			];

			self::$selectAlumnosFields = $tableAlumno.'id,'. 
				$tableAlumno.'username,'. 
				$tableAlumno.'password,'. 
				$tableAlumno.'firstname,'. 
				$tableAlumno.'lastname,'. 
				$tableAlumno.'email,'.
				$tableAlumno.'city,'. 
				$tableAlumno.'dni_nif,'. 
				$tableAlumno.'cohort1';


			if ($_GET['listadotipo'] == 'bajas' || $_GET['listadotipo'] == 'bajasCursos') {

				for ($x = 1; $x <= $this->coursesColumns; $x++) {
					$this->headers[] = 'course' . $x;
					$this->headers[] = 'enrolstatus'.$x;

					$this->columns[] = 'course' . $x;
					$this->columns[] = 'enrolstatus' . $x;

					self::$selectAlumnosFields .= ', course' . $x . ', CASE WHEN course' . $x . ' != "" THEN 1 ELSE "" END AS enrolstatus' . $x;
				}
			} elseif ($_GET['listadotipo'] == 'altasCursos' || $_GET['listadotipo'] == 'altas') {

				for ($x = 1; $x <= $this->coursesColumns; $x++) {					
					$this->headers[] = 'course' . $x;
					$this->headers[] = 'enrolstatus'.$x;

					$this->columns[] = 'course' . $x;
					$this->columns[] = 'enrolstatus' . $x;

					self::$selectAlumnosFields .= ', course' . $x . ', CASE WHEN course' . $x . ' != "" THEN 0 ELSE "" END AS enrolstatus' . $x;
				}
			}
		}

		// Definimos las columnas a mostrar
		$table->define_columns($this->columns);

		// Definimos el nombre de cada columna
		$table->define_headers($this->headers);

		/******************* FILTROS ***************************
		 * Filtros varios para mostrar en los distintos listados
		 * Método de envío GET
		 *****************************************************/
		$camposFiltro = ['username', 'password', 'firstname', 'lastname', 'email', 'city', 'dni_nif', 'cohort1'];

		foreach($camposFiltro as $campo){
			if(!empty($_GET[$campo])){
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.$campo.' LIKE "%'.$_GET[$campo].'%" ';
			}
		}

		if(!empty($_GET['cambios_emails'])) {
			if($_GET['cambios_emails'] == 'S'){
				self::$selectAlumnosWhere .= ' AND id IN (select import_id FROM {itainnova_alumnos_comparar_errores} WHERE errores LIKE "%email%") ';
			}else{
				self::$selectAlumnosWhere .= ' AND id NOT IN (select import_id FROM {itainnova_alumnos_comparar_errores} WHERE errores LIKE "%email%") ';
			}
		}

		if(!empty($_GET['no_emails'])) {
			if($_GET['no_emails'] == 'S'){
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'email = "" ';
			}else{
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'email != "" ';
			}
		}

		if(!empty($_GET['error_emails_dominio'])) {
			if($_GET['error_emails_dominio'] == 'S'){
				self::$selectAlumnosWhere .= ' AND right('.$tableAlumno.'email, length('.$tableAlumno.'email)-INSTR('.$tableAlumno.'email, "@")) NOT IN (select domain_email FROM mdl_itainnova_valida_emails) ';
			}else{
				self::$selectAlumnosWhere .= ' AND right('.$tableAlumno.'email, length('.$tableAlumno.'email)-INSTR('.$tableAlumno.'email, "@")) IN (select domain_email FROM mdl_itainnova_valida_emails) ';
			}
		}

		if(!empty($_GET['usuarios_duplicados'])){
			// IMPORTANTE - La variable se modifica en el método getListAlumnosImportHTML()
			$tabla = self::$selectAlumnosFrom;
			if($_GET['usuarios_duplicados'] == 'S'){
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'email IN (select email FROM '.$tabla.' WHERE 1=1 GROUP BY email HAVING count(*) > 1) ';
			}else{
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'email NOT IN (select email FROM '.$tabla.' WHERE 1=1 GROUP BY email HAVING count(*) > 1) ';
			}
		}

		if(!empty($_GET['usuarios_registrados'])){

			if($_GET['usuarios_registrados'] == 'S'){
				self::$selectAlumnosWhere .= ' AND ( '.$tableAlumno.'email IN (select email FROM {itainnova_alumnos_actual} WHERE 1=1 GROUP BY email) ';
				self::$selectAlumnosWhere .= ' OR '.$tableAlumno.'username IN (select username FROM {itainnova_alumnos_actual} WHERE 1=1 GROUP BY username) )';
			}else{
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'email NOT IN (select email FROM {itainnova_alumnos_actual} WHERE 1=1 GROUP BY email) ';
				self::$selectAlumnosWhere .= ' AND '.$tableAlumno.'username NOT IN (select username FROM {itainnova_alumnos_actual} WHERE 1=1 GROUP BY username) ';
			}
		}

		if(!empty($_GET['usuarios_suspendidos'])){

			if($_GET['usuarios_suspendidos'] == 'S'){
				self::$selectAlumnosWhere .= ' AND usuario.suspended = 1 ';
			} elseif($_GET['usuarios_suspendidos'] == 'NULL') {
				self::$selectAlumnosWhere .= ' AND usuario.suspended IS NULL ';
			}else{
				self::$selectAlumnosWhere .= ' AND usuario.suspended = 0 ';
			}
		}

		if(!empty($_GET['export_altas_bajas'])){
			self::$selectAlumnosWhere .= ' AND export_altas_bajas = '.$_GET['export_altas_bajas'].' ';
		}

		/*************************************/
		
		// Creamos la consulta
		$table->set_sql(
			self::$selectAlumnosFields,
			self::$selectAlumnosFrom,
			self::$selectAlumnosWhere,
			array(),
			self::$selectAlumnosGroupBy
		);

		// Definimos la URL base
		$table->define_baseurl($this->baseUrl);

		return $table;
	}

	/**
	 * Renderiza el listado de alumnos utilizando el método getListAlumnosHTML()
	 * Utiliza el parámetro $params para verificar que tipo de datos debe renderizar
	 *
	 * @return void
	 */
	public function getListAlumnosImportHTML($params = array())
	{

		/**
		 * Renderiza la tabla de comparación {itainnova_alumnos_comparar} mostrando los distintos tipos de errores
		 * Los errores pueden ser de "cursos" {itainnova_alumnos_comparar_errores_cursos} o "datos" {itainnova_alumnos_comparar_errores}
		 */
		if ($params['tipo'] == 'comparacion') {
			$tableName = 'listado-alumnos-comparar';
			$exportTableName = 'Listado Comparación de Alumnos';

			$this->columns = [
				'firstname', 'lastname', 'username', 'email', 'dni_nif', 'city', 'cohort1'
			];

			$this->headers = [
				'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'Ciudad', 'Cohorte'
			];

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				$this->columns[] = 'course' . $x;
				$this->headers[] = 'Curso ' . $x;
			}

			self::$selectAlumnosFields = 'comparar.id
			,comparar.export_altas_bajas
			,comparar.firstname AS firstname
			,comparar.lastname AS lastname
			,comparar.username AS username
			,comparar.email AS email
			,comparar.dni_nif AS dni_nif
			,comparar.city AS city
			,comparar.cohort1 AS cohort1';

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				self::$selectAlumnosFields .= ',course' . $x;
			}

			self::$selectAlumnosFrom = "{itainnova_alumnos_comparar} comparar";

			// ERRORES CURSOS
			if ($params['comparar'] == 'cursos') {
				self::$selectAlumnosFields .= ',error.errores';
				self::$selectAlumnosFrom .= " INNER JOIN {itainnova_alumnos_comparar_errores_cursos} error ON comparar.id = error.import_id ";

			// ERRORES DATOS
			} elseif ($params['comparar'] == 'datos') {
				self::$selectAlumnosFields .= ',error.errores';
				self::$selectAlumnosFrom .= " INNER JOIN {itainnova_alumnos_comparar_errores} error ON comparar.id = error.import_id ";

			// MOSTRAMOS TODOS CON ERRORES
			} else {
				self::$selectAlumnosFields .= ',CASE WHEN error.errores != "" THEN error.errores ELSE errores_curso.errores END as errores';
				self::$selectAlumnosFrom .= "
					LEFT JOIN {itainnova_alumnos_comparar_errores_cursos} errores_curso ON comparar.id = errores_curso.import_id
					LEFT JOIN {itainnova_alumnos_comparar_errores} error ON comparar.id = error.import_id
				";
			}

			// self::$selectAlumnosWhere = 'comparar.archivo_excel="' . $this->archivoExcel . '"';
			// self::$selectAlumnosWhere = 'estado=1';
			self::$selectAlumnosWhere = '1=1';

		/**
		 * Renderiza la tabla de ALTAS DE ALUMNOS {itainnova_alumnos_altas}
		 */
		} elseif ($params['tipo'] == 'altas') {

			$tableName = 'listado-alumnos-alta';
			$exportTableName = 'Listado Altas Nuevas de Alumnos';

			$this->columns = [
				'id', 'firstname', 'lastname', 'username', 'email', 'dni_nif', 'cohort1'
			];

			$this->headers = [
				'Orden', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'Cohorte'
			];

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				$this->columns[] = 'course' . $x;
				$this->headers[] = 'Curso ' . $x;
			}

			self::$selectAlumnosFields = 'id, firstname, lastname, username, email, dni_nif, cohort1';

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				self::$selectAlumnosFields .= ',course' . $x;
			}

			self::$selectAlumnosFrom = '{itainnova_alumnos_altas}';

			self::$selectAlumnosWhere = '1=1';

		/**
		 * Renderiza la tabla de ERRORES DE MODULOS {itainnova_cod_modulos_error}
		 */
		} elseif ($params['tipo'] == 'modulos') {

			$tableName = 'listado-alumnos-alta';
			$exportTableName = 'Listado Altas Nuevas de Alumnos';

			$this->columns = [
				'alumno_dni_nif', 'row_id', 'cod_ciclo', 'cod_mod_dga', 'cod_mod_moodle'
			];

			$this->headers = [
				'DNI/NIE', 'Línea Excel Importación', 'Cod. Ciclo', 'Cod. Mod. DGA', 'Cod. Mod. Moodle'
			];

			self::$selectAlumnosFields = '*';

			self::$selectAlumnosFrom = '{itainnova_cod_modulos_error}';

			self::$selectAlumnosWhere = '1=1';

		/**
		 * Renderiza la tabla de ALTAS DE CURSOS {itainnova_alumnos_altas_cursos}
		 */
		} elseif ($params['tipo'] == 'altasCursos') {

			$tableName = 'listado-alumnos-altas-cursos';
			$exportTableName = 'Listado Altas Nuevas de Cursos';

			$this->columns = [
				'id', 'firstname', 'lastname', 'username', 'email', 'dni_nif', 'cohort1'
			];

			$this->headers = [
				'Orden', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'Cohorte'
			];

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				$this->columns[] = 'course' . $x;
				$this->headers[] = 'Curso ' . $x;
			}

			self::$selectAlumnosFields = 'id, firstname, lastname, username, email, dni_nif, cohort1';

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				self::$selectAlumnosFields .= ',course' . $x;
			}

			self::$selectAlumnosFrom = '{itainnova_alumnos_altas_cursos}';

			self::$selectAlumnosWhere = '1=1';

		/**
		 * Renderiza la tabla de BAJAS DE ALUMNOS {itainnova_alumnos_bajas}
		 */
		} elseif ($params['tipo'] == 'bajas') {

			$tableName = 'listado-alumnos-bajas';
			$exportTableName = 'Listado Bajas de Alumnos';

			$this->columns = [
				'id', 'firstname', 'lastname', 'username', 'email', 'dni_nif', 'estado', 'cohort1', 'suspended'
			];

			$this->headers = [
				'Orden', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'Tipo de Baja', 'Cohorte', 'Estado Cuenta'
			];

			self::$selectAlumnosFields = 'alumno.id, alumno.firstname, alumno.lastname, alumno.username, alumno.email, alumno.dni_nif,
				CASE WHEN alumno.estado = 2 THEN "Matricula de baja" ELSE "Anulación de Matrícula" END AS estado,
				alumno.cohort1,
				CASE
					WHEN usuario.suspended = 1 THEN "Cuenta Suspendida"
					WHEN usuario.suspended = 0 THEN "Cuenta Habilitada"
					ELSE "No Existe"
				END AS suspended';

			self::$selectAlumnosFrom = '{itainnova_alumnos_bajas} AS alumno LEFT JOIN {user} AS usuario 
										ON (usuario.username = alumno.username OR usuario.email = alumno.email)';

			self::$selectAlumnosWhere = '1=1';

		/**
		 * Renderiza la tabla de BAJAS DE CURSOS {itainnova_alumnos_bajas_cursos}
		 */
		} elseif ($params['tipo'] == 'bajasCursos') {

			$tableName = 'listado-alumnos-bajas-cursos';
			$exportTableName = 'Listado Bajas de Cursos';

			$this->columns = [
				'id', 'firstname', 'lastname', 'username', 'email', 'dni_nif', 'cohort1'
			];

			$this->headers = [
				'Orden', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'Cohorte'
			];

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				$this->columns[] = 'course' . $x;
				$this->headers[] = 'Curso ' . $x;
			}

			self::$selectAlumnosFields = 'id, firstname, lastname, username, email, dni_nif, cohort1';

			for ($x = 1; $x <= $this->coursesColumns; $x++) {
				self::$selectAlumnosFields .= ',course' . $x;
			}

			self::$selectAlumnosFrom = '{itainnova_alumnos_bajas_cursos}';

			self::$selectAlumnosWhere = '1=1';

		} else {

			$tableName = 'listado-alumnos-importados';
			$exportTableName = 'Listado alumnos importados SIGAD';

			$this->columns = array('idficha', 'firstname', 'lastname', 'username', 'email', 'dni_nif', 'date_insert');
			$this->headers = array('ID Ficha', 'Nombre', 'Apellidos', 'Usuario', 'Email', 'DNI/NIE', 'F. Inserción');

			self::$selectAlumnosFields = '*';
			self::$selectAlumnosFrom = '{itainnova_import_alumnos}';
			self::$selectAlumnosWhere = '1=1';

		}

		self::$selectAlumnosGroupBy = '';

		$table = $this->getListAlumnosHTML($tableName, $exportTableName);

		return $table;
	}
}

/**
 * Clase que extiende de la clase original de Moodle table_sql
 */
class table_sql_alumnos extends table_sql
{

	/**
	 * Variable de tipo array con Emails Validos
	 *
	 * @var array
	 */
	private $emailsValidos = [];

	function __construct($uniqueid)
	{
		parent::__construct($uniqueid);
		// some sensible defaults
		$this->set_attribute('cellspacing', '0');
		$this->set_attribute('class', 'generaltable generalbox');

		$this->emailsValidos = ComparaAlumno::setEmailsValidos();
	}

	/**
	 * Modificado del query_db original para añadir el Group By a los parámetros
	 */
	function set_sql($fields, $from, $where, array $params = array(), $groupBy)
	{
		$this->sql = new stdClass();
		$this->sql->fields = $fields;
		$this->sql->from = $from;
		$this->sql->where = $where;
		$this->sql->params = $params;
		$this->sql->groupBy = $groupBy;
	}

	/**
	 * Modificado del query_db original para añadir el Group By a los parámetros
	 *
	 * @param int $pagesize size of page for paginated displayed table.
	 * @param bool $useinitialsbar do you want to use the initials bar. Bar
	 * @return void
	 */
	function query_db($pagesize, $useinitialsbar = true)
	{
		global $DB;
		if (!$this->is_downloading()) {
			if ($this->countsql === NULL) {
				$this->countsql = 'SELECT COUNT(1) FROM ' . $this->sql->from . ' WHERE ' . $this->sql->where;
				$this->countparams = $this->sql->params;
			}
			$grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
			if ($useinitialsbar && !$this->is_downloading()) {
				$this->initialbars($grandtotal > $pagesize);
			}

			list($wsql, $wparams) = $this->get_sql_where();
			if ($wsql) {
				$this->countsql .= ' AND ' . $wsql;
				$this->countparams = array_merge($this->countparams, $wparams);

				$this->sql->where .= ' AND ' . $wsql;
				$this->sql->params = array_merge($this->sql->params, $wparams);

				$total  = $DB->count_records_sql($this->countsql, $this->countparams);
			} else {
				$total = $grandtotal;
			}

			$this->pagesize($pagesize, $total);
		}

		// Fetch the attempts
		$sort = $this->get_sql_sort();
		if ($sort) {
			$sort = "ORDER BY $sort";
		} else {
			$sort = "ORDER BY id ASC";
		}

		$sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
				WHERE {$this->sql->where}";

		/* echo $sql;
		die(); */

		if (!empty($this->sql->groupBy)) $sql .= " GROUP BY {$this->sql->groupBy} ";
		// if(!empty($this->sql->limit)) $sql .= " LIMIT {$this->sql->limit} ";

		$sql .= " {$sort}";

		if (!$this->is_downloading()) {
			$this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
		} else {
			$this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
		}
	}

	/**
	 * Método que verifica el elemento row en cada iteración y en el cual podremos
	 * modificar el dato según nuestras necesidades
	 *
	 * @param [type] $colname
	 * @param [type] $row
	 * @return void
	 */
	function other_cols($colname, $row)
	{
		// Si no se está descargando, modificamos los datos para mostrarlos por pantalla (HTML)
		if (!$this->is_downloading()) {
			$columnasErrores = explode(',', $row->errores);
			$username = '';
			if ($colname == 'username' || $colname == 'dni_nif') {

				if (in_array($colname, $columnasErrores)) $classError = 'error';
				else $classError = '';

				return '<a href="javascript:void(0)" class="searchUser '.$classError.'" data-search-field="'.$colname.'" data-search-value="' . $row->{$colname} . '">' .
					$row->{$colname} . '</a>';

			}elseif ($colname == 'cohort1') {
				return '<a href="javascript:void(0)" class="searchCohort" data-search="' . $row->{$colname} . '" data-username="' . $row->username . '">' . $row->{$colname} . '</a>';
			} elseif ($colname == 'email') {

				list($user, $domain) = explode('@', $row->email);
				if (in_array($colname, $columnasErrores) || !in_array($domain, $this->emailsValidos)) $classError = 'error';
				else $classError = '';

				$errorTitle = '';
				if (!in_array($domain, $this->emailsValidos)) {
					$errorTitle = 'El dominio del email no es válido';
				}

				$column = '';

				if($_GET['tab'] == 'listadosigad' && $_GET['listadotipo'] == 'altas'){
					$column .= '<a href="https://www.adistanciafparagon.es/itainnova_tool/bienvenida_cursos_alumnos.php?email='. $row->email.'" target="_blank">';
				}

				$column .= '<span class="searchStudent ' . $classError . '" title="' . $errorTitle . '" 
					data-type="all_info" data-search="' . $row->username . '|' . $row->email . '|' . $row->cohort1 . '">' . 
					$row->{$colname} . '</span>';

				if($_GET['tab'] == 'listadosigad' && $_GET['listadotipo'] == 'altas'){
					$column .= '</a>';
				}

				return $column;
			} elseif (in_array($colname, $columnasErrores)) {
				return '<span class="error">' . $row->{$colname} . '</span>';
			} else {
				return NULL;
			}
		}
	}
}