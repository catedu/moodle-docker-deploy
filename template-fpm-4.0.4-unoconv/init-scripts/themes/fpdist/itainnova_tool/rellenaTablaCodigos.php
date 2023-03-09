<?php

if(file_exists('../../config.php')){
    require_once('../../config.php');
}else{
    require_once('../config.php');
}

try {
    require_capability('moodle/site:config', context_system::instance());
  } catch (Exception $e) {
    throw new moodle_exception('requireloginerror');
  }

/* $myfile = fopen("codigos_modulos.txt", "r") or die("Unable to open file!");
echo fread($myfile, filesize("codigos_modulos.txt"));
fclose($myfile); */

$file = "codigos_modulos.txt";
// $content = file_get_contents($file);
$content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$newData = array();
$i = 0;
$y = 0;
$x = 1;
$fields = array('cod_ciclo','cod_mod_moodle','cod_mod_dga');
foreach($content as $data){
    $data = str_ireplace(array('"',';'),array('',''),$data);
	$newData[$i][$fields[$y]] = $data;
	
	$y++;
	if($x % 3 == 0){
		$i++;
		$y = 0;
	}
    $x++;
}

global $DB;

foreach($newData as $data){
	try {

		
		echo nl2br(print_r($data,true));
		$insertData = json_decode(json_encode($data), FALSE);

		// $insertData = (object) $data;

		/* $insertData = new stdClass();
		foreach($data as $i => $v){
			$insertData->$i = $v;
		} */

		var_dump($insertData);
		// die();

		$transaction = $DB->start_delegated_transaction();
		$DB->insert_record('itainnova_cod_modulos', $insertData);
			
		// Assuming the both inserts work, we get to the following line.
		$transaction->allow_commit();
	
   } catch(Exception $e) {
		$transaction->rollback($e);
		var_dump($e);
		die();
   }
}