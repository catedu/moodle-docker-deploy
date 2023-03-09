<?php
/*
* Comprobamos que el usuario es el administrador
* Lanza error si no tiene permisos
*/
if(file_exists('../../config.php')){
  require_once('../../config.php');
}else{
  require_once('../config.php');
}
require_login();
$PAGE->set_context(context_system::instance());
try {
  require_capability('moodle/site:config', context_system::instance());
} catch (Exception $e) {
  throw new moodle_exception('requireloginerror');
}
//No cerramos la etiqueta de php al no tener código html
