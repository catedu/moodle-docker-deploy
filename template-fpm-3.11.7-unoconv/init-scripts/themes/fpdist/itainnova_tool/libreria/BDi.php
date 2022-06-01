<?
//
// Database queries
//
function get_db_connITA() {	
    $datos['DB_IP'] = 'localhost';
	$datos['DB_USER'] = 'teleform';
	$datos['DB_PASS'] = 'xxx';
	$datos['DB_NAME'] = 'teleform_moodle';
	$conn = mysqli_connect($datos['DB_IP'], $datos['DB_USER'], $datos['DB_PASS'],$datos['DB_NAME']);
	if (!$conn) {
		echo "No pudo conectarse a la BD: " . mysqli_error();
		exit;
	}	 
	return $conn;
}
 
//PARA PROTEGER SQL INJECT
//function cleanQuery($string){
//	if(get_magic_quotes_gpc()) {  // prevents duplicate backslashes
//		$string = stripslashes($string);
//	}
//	if (phpversion() >= '4.3.0'){
//		$string = mysqli_real_escape_string($string);
//	}else{
//		$string = mysqli_escape_string($string);
//	}
//	return $string;
//}
?>