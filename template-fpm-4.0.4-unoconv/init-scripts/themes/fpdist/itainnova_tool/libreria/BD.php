<?php
//
// Database queries
//
function get_db_conn() {
		
	$datos['DB_IP'] = 'localhost';
	$datos['DB_NAME'] = 'moodlefpdistancia';
	$datos['DB_USER'] = 'moodlefpdistancia';
	$datos['DB_PASS'] = 'xxx';
	
	
	$conn = mysql_connect($datos['DB_IP'], $datos['DB_USER'], $datos['DB_PASS']);
	
	mysql_select_db($datos['DB_NAME'], $conn);
	if (!$conn) {
		echo "No pudo conectarse a la BD: " . mysql_error();
		exit;
	}	 
	return $conn;
}
 
//PARA PROTEGER SQL INJECT
function cleanQuery($string){
	if(get_magic_quotes_gpc()) {  // prevents duplicate backslashes
		$string = stripslashes($string);
	}
	if (phpversion() >= '4.3.0'){
		$string = mysql_real_escape_string($string);
	}else{
		$string = mysql_escape_string($string);
	}
	return $string;
}
?>