<?php
//Comprobar error al envio, que los cursos tengan profesor.

require_once('../libreria/checkAdmin.php');
require('../libreria/PhpSpreadsheet/vendor/autoload.php');
require("../libreria/PHPMailer_5.2/PHPMailerAutoload.php");
require_once($CFG->dirroot.'/report/outline/locallib.php');
require_once($CFG->dirroot.'/report/outline/lib.php');
$urlBodyTemplate = "../Fragmentos_Mail/informe_actividad_excel/body.html";
$editor1 = file_get_contents($urlBodyTemplate);
$title = "Informe de actividad de los alumnos al profesorado";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add('Informes', new moodle_url('/itainnova_tool/informes'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
$PAGE->set_url(new moodle_url('/itainnova_tool/informes/profesores.php', array('id'=> $courseid, 'batch'=> $batch)));
$PAGE->set_pagelayout('frametop');
echo $OUTPUT->header();
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
/*
* Calcula la columna en funcion del entero que se le pase
* @param integer, posicion en la columna empezando A=1
*
* @return string
*/
function getColumnFromNumber($column) {
  $numeric = ($column - 1) % 26;
  $letter = chr(65 + $numeric);
  $num2 = intval(($column - 1) / 26);
  if ($num2 > 0) {
    return getColumnFromNumber($num2) . $letter;
  } else {
    return $letter;
  }
}
#### DATOS

//Creamos el Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

//GET PARAMETERS
$courseid = required_param('course', PARAM_NUMBER);
$batch = optional_param('batch',false, PARAM_BOOL);

//Obtenermos el curso y su contexto
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);

//Obtenermos los usuarios
$users = get_enrolled_users($coursecontext,'',0,'u.*','', 0, 0);

#### HEADER
$sheet->setCellValue('C1', 'CURSO:');
$sheet->setCellValue('D1', $course->fullname);
$sheet->setCellValue('A3', 'USER ID');
$sheet->setCellValue('B3', 'NOMBRE');
$sheet->setCellValue('C3', 'APELLIDOS');
$sheet->setCellValue('D3', 'EMAIL');
$sheet->setCellValue('E1', "Acceso al curso");
$sheet->setCellValue('E3', 'Nº Dias Conectado');
$sheet->setCellValue('F3', 'Nº veces visto curso');
$sheet->setCellValue('G3', 'Nº Registros');
#### VARs
$visitas = "Visitas"; //Texto columna conteo de visitas
$mensajes = "Mensajes"; //Texto columna conteo numero de mensajes en el foro
$entradas = "Entradas"; //Texto columna conteo de entradas en el glosario
$accesos = "Accesos"; //Texto columna conteo de Accesos
$nota = "Calificación"; //Texto de columna calificacion obtenida
$no_realizado = "--";
$realizado = "Realizado";
$choice = "Eleccion";
$row = 4; //Empezamos en la col 4 ya que 1-3 -> header
$prof_email = array();
$header_printed = false;
#### Estilos
$estiloHeader =[
  'font' =>['bold' => true],
  'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
  'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
];
$estiloFila =[
  'font' =>['bold' => false],
  'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
];
$estiloColumna = [
  'alignment' =>  ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
  'borders'   =>  [ 'left' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
];

##### BUCLE DE ALUMNOS
foreach ($users as $user){
  $column=8;
  //Si no es ALUMNO (roleid=5)
  if(!$DB->record_exists("role_assignments", array('userid'=>"$user->id" , 'roleid'=>'5', 'contextid'=>"$coursecontext->id"))){
    //Si es profesor y no le hemos enviado aún este informe hoy
    if($DB->record_exists("role_assignments", array('userid'=>"$user->id" , 'roleid'=>'3', 'contextid'=>"$coursecontext->id")) &&
    !$DB->record_exists("itainnova_log", array('source'=>basename(__FILE__), 'courseid'=> $courseid, 'log'=>"$user->email", 'logdate' => date("Y-m-d"))))
	  array_push($prof_email,$user->email);//guardamos todos los profesores
    continue; //Si no es alumno, continuamos
  }
  //Es alumno
  if($DB->count_records("role_assignments", array('userid'=>"$user->id", 'contextid'=>"$coursecontext->id"))>1)
  continue; //Es alumno pero puede tener también rol de prof, coord o jefeestudios -> ignoramos
  //Utilizamos el usuario demo para fijar el HEADER
  if($user->lastname=="_ALU" || $user->lastname=="_PROF"){
    if($header_printed) continue;
    $modinfo = get_fast_modinfo($courseid, $user->id);
    $sections = $modinfo->get_section_info_all();
    foreach ($sections as $i => $section) {
      if ($section->uservisible) { // prevent hidden sections in user activity. Thanks to Geoff Wilbert!
        // Check the section has modules/resources, if not there is nothing to display.
        if (!empty($modinfo->sections[$i])) {
          $sheet->setCellValue(getColumnFromNumber($column).'1',get_section_name($courseid, $section));
        }
        foreach ($modinfo->sections[$i] as $cmid) {
          $mod = $modinfo->cms[$cmid];

          if (empty($mod->uservisible)) {
            continue;
          }
          $instance = $DB->get_record("$mod->modname", array("id"=>$mod->instance));
          $libfile = "$CFG->dirroot/mod/$mod->modname/lib.php";

          if (file_exists($libfile)) {
            require_once($libfile);
          }

          $user_outline = $mod->modname."_user_outline";
          if (function_exists($user_outline)) {
            $output = $user_outline($course, $user, $mod, $instance);
          } else {
            $output = report_outline_user_outline($user->id, $cmid, $mod->modname, $instance->id);
          }
          switch ($mod->modname) {
            case 'label':
            case 'openmeetings':
            //No la incluimos
            break;
            case 'forum':
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.'));
              $sheet->setCellValue(getColumnFromNumber($column).'3',$visitas);
              $column++;
              $sheet->setCellValue(getColumnFromNumber($column).'2',"[$mod->modname]");
              $sheet->setCellValue(getColumnFromNumber($column).'3',$mensajes);
              $column++;
              break;
              case 'chat':
              case 'resource':
              case 'url':
              case 'page':
              case 'folder':
              case 'wiki':
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.'));
              $sheet->setCellValue(getColumnFromNumber($column).'3',$accesos." [$mod->modname]");
              $column++;
              break;
              case 'scorm':
              case 'quiz':
              case 'assign':
              case 'data':
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.'));
              $sheet->setCellValue(getColumnFromNumber($column).'3',$nota." [$mod->modname]");
              $column++;
              break;
              case 'glossary':
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.'));
              $sheet->setCellValue(getColumnFromNumber($column).'3',$entradas);
              $column++;
              $sheet->setCellValue(getColumnFromNumber($column).'2',"[$mod->modname]");
              $sheet->setCellValue(getColumnFromNumber($column).'3',$nota);
              $column++;
              break;
              case 'choice':
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.'));
              $sheet->setCellValue(getColumnFromNumber($column).'3',$choice." [$mod->modname]");
              $column++;
              break;
              default:
              //Por si acaso nos encontramos con otro tipo de recurso
              $sheet->setCellValue(getColumnFromNumber($column).'2',rtrim($instance->name,'.')."($mod->modname)");
              $column++;
              break;
            }
          }
        }
      }
      $header_printed = true;
      continue; //Es Alumno demo -> pasamos al siguiente
    }
    $sheet->setCellValue("A$row", $user->id);
    $sheet->setCellValue("B$row", $user->firstname);
    $sheet->setCellValue("C$row", $user->lastname);
    $sheet->setCellValue("D$row", $user->email);
    $num_dias = $DB->count_records_select("logstore_standard_log", "courseid = ? AND userid = ? ", array($courseid,$user->id), $countitem="COUNT(DISTINCT(DATE(FROM_UNIXTIME(timecreated))))");
    $sheet->setCellValue("E$row", $num_dias);
    $num_vistas = $DB->count_records_select("logstore_standard_log", "courseid = ? AND userid = ? AND action = ? AND target = ?", array($courseid,$user->id,'viewed','course'), $countitem="COUNT(id)");
    $sheet->setCellValue("F$row", $num_vistas);
    $total_acciones = $DB->count_records_select("logstore_standard_log", "courseid = ? AND userid = ?", array($courseid,$user->id), $countitem="COUNT(id)");
    $sheet->setCellValue("G$row", $total_acciones);
    $sheet->getStyle("E$row:G$row")->applyFromArray($estiloColumna);
    /*
    * @source report/outline/user.php
    */
    $modinfo = get_fast_modinfo($courseid, $user->id);
    $sections = $modinfo->get_section_info_all();
    foreach ($sections as $i => $section) {
      if ($section->uservisible) { // prevent hidden sections in user activity. Thanks to Geoff Wilbert!
        // Check the section has modules/resources, if not there is nothing to display.
        if (!empty($modinfo->sections[$i])) {
          //$sheet->setCellValue($column.'2',get_section_name($courseid, $section));
        }
        foreach ($modinfo->sections[$i] as $cmid) {
          $mod = $modinfo->cms[$cmid];

          if (empty($mod->uservisible)) {
            continue;
          }
          $instance = $DB->get_record("$mod->modname", array("id"=>$mod->instance));
          $libfile = "$CFG->dirroot/mod/$mod->modname/lib.php";

          if (file_exists($libfile)) {
            require_once($libfile);
          }

          $user_outline = $mod->modname."_user_outline";
          if (function_exists($user_outline)) {
            $output = $user_outline($course, $user, $mod, $instance);
          } else {
            $output = report_outline_user_outline($user->id, $cmid, $mod->modname, $instance->id);
          }

          $cells = getColumnFromNumber($column)."$row:";
          switch ($mod->modname) {
            case 'label':
            case 'openmeetings':
            break;
            case 'scorm':
            //obtenemos la calificacion en fraccion
            $cal = explode('/',substr($output->info,strpos($output->info,':')+1));
            $cal = (strpos($cal[0], '-')!==false) ? $no_realizado : $realizado;
            $sheet->setCellValue(getColumnFromNumber($column).$row,$cal);
            $column++;
            break;
            case 'quiz':
            case 'assign':
            case 'data':
            //obtenemos la calificacion en fraccion y las casteamos a double
            $cal = explode('/',substr($output->info,strpos($output->info,':')+1));
            $cal[0]=doubleval($cal[0]);
            $cal[1]=doubleval($cal[1]);
            $sheet->setCellValue(getColumnFromNumber($column).$row,"$cal[0]/$cal[1]");
            $column++;
            break;
            case 'forum':
              //quitamos todo menos el numero de mensajes
              $num_visitas = $DB->count_records("logstore_standard_log", array ('component'=>'mod_forum', 'action' => 'viewed','userid'=> $user->id, 'contextinstanceid' => $cmid));
              $sheet->setCellValue(getColumnFromNumber($column).$row,$num_visitas);
              $column++;
              $num_mensajes = preg_replace("/(,*?)(\s?)Calificación\:(\s?)(.*?)(-?)(\/?)(.*?)/" , "" , $output->info); //Quitamos la Calificacion si la hay
              $num_mensajes = preg_replace("/(\s*?)(\d+)(\s+)mensajes/","$2",$num_mensajes);
              if($num_mensajes == "")
              $num_mensajes = 0;
              $sheet->setCellValue(getColumnFromNumber($column).$row,$num_mensajes);
              //$sheet->setCellValue($column.$row,preg_replace("/(,*?)(\s?)Calificación\:(\s?)(.*?)(-?)(\/?)(.*?)/" , "" , $output->info));
              $column++;
              break;
              case 'glossary':
              if($output->time==NULL){ //Time == null -> no realizado
                $sheet->setCellValue(getColumnFromNumber($column).$row,$no_realizado);
                $column++;
                $sheet->setCellValue(getColumnFromNumber($column).$row,"0/0");
                $column++;
              }else{
                $num_entradas = preg_replace("/\s*(\d+)\s+Entradas,\s*Calificación\:\s+.*/","$1",$output->info);
                $sheet->setCellValue(getColumnFromNumber($column).$row,$num_entradas);
                $column++;
                $cal = preg_replace("/\s*\d+\s+Entradas,\s*Calificación\:\s+(.*)/","$1",$output->info);
                if($cal=="-") $cal = "0/0";
                $sheet->setCellValue(getColumnFromNumber($column).$row,$cal);
                $column++;
              }
              break;
              case 'choice':
              if($output->info==NULL){
                $sheet->setCellValue(getColumnFromNumber($column).$row,$no_realizado);
              }else{
                $sheet->setCellValue(getColumnFromNumber($column).$row,substr($output->info,1,strlen($output->info)-2));
              }
              $column++;
              break;
              default:
              $num = preg_replace("/^(\s*?)(\d+)(\s)vistas/","$2",$output->info);
              if($num=="")$num=0;
              $sheet->setCellValue(getColumnFromNumber($column).$row,$num);
              $column++;
              break;
            }
            $cells.=getColumnFromNumber($column)."$row";
            $sheet->getStyle($cells)->applyFromArray($estiloColumna);
          }
        }
      }
      $row++;
    }
    //Autoajustamos ancho de columnas
    $column=1;
    while(!is_null($sheet->getCell(getColumnFromNumber($column).'3')->getValue())){
      $sheet->getColumnDimension(getColumnFromNumber($column))->setAutoSize(true);
      $sheet->getStyle(getColumnFromNumber($column)."1:".getColumnFromNumber($column)."3")->applyFromArray($estiloHeader);
      $column++;
    }
    #Colores y centrado en las filas
    $row = 4;
    $column--;
    while(!is_null($sheet->getCell('A'.$row)->getValue())) {
      $sheet->getStyle('A'.$row.':'.getColumnFromNumber($column).$row)->applyFromArray($estiloFila);
      $sheet->getStyle(getColumnFromNumber($column+1).$row.':'.getColumnFromNumber($column+2).$row)->applyFromArray($estiloColumna);
      if($row%2==0)
      $sheet->getStyle('A'.$row.':'.getColumnFromNumber($column).$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      ->getStartColor()->setARGB('0F3F3F3');
      $row++;
    }
    #bordes en el header
    $column=5;
    while(!is_null($sheet->getCell(getColumnFromNumber($column).'3')->getValue())){
      if(!is_null($sheet->getCell(getColumnFromNumber($column).'1')->getValue()))
      $sheet->getStyle(getColumnFromNumber($column).'1:'.getColumnFromNumber($column).'3')->applyFromArray($estiloColumna);
      $column++;
    }
    $writer = new Xlsx($spreadsheet);
    $nombre_fic = 'profesores/Informe-'.$courseid.'-'.$course->shortname.'-'.date("Y-m-d").'.xlsx';
    # Guardamos el fichero
    if(file_exists($nombre_fic))
    unlink($nombre_fic);
    $writer->save($nombre_fic);
    # Enviamos el informe a los profesores
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = "smtp.aragon.es";
    $mail->SMTPSecure = "tls";
    $mail->SMTPAuth = true;
    // credenciales usuario
				include "./secret/crenciales.php";
    $mail->Subject = 'Informe de seguimiento '.utf8_decode($course->fullname).' '.date("d-m-Y");
    $mail->IsHTML(true);
    $contenido_email = $editor1;
	
	foreach ($prof_email as $profesor) {

	  //COMENTAR PARA PRUEBAS
	  $mail->AddAddress($profesor);
      //$mail->AddAddress('admin.moodlefpdistancia@aragon.es');
	  //$mail->AddAddress("cpina@itainnova.es");
      
	  $mail->addAttachment('./'.$nombre_fic, '','base64', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'attachment');
      $mail->Body = $contenido_email;
      $enviado = false;
      for($i = 0; $i < 2 && !$enviado; $i++){
        $enviado = $mail->Send();
      }
      if(!$enviado){
        echo "<p><strong>Curso:".$courseid." Error</strong> al enviar correo de ".$profesor."</p>";
        echo '<p>'.$mail->ErrorInfo.'</p>';
        echo "<br>";
        $batch=0;
      }else{
        $logentry = new stdClass();
        $logentry->source   = basename(__FILE__);
        $logentry->courseid = $courseid;
        $logentry->log      = $profesor;
        $logentry->logdate  = date('Y-m-d');
        $logentry->logtime  = date('H:i:s');
        $lastinsertid = $DB->insert_record('itainnova_log', $logentry, false);
        echo '<p>Enviado correctamente a '.$profesor.'</p>';
      }
      $mail->ClearAddresses();
      $mail->ClearAllRecipients();
      $mail->clearAttachments();
    }
    if($batch){
      $next = $DB->get_records_sql("SELECT id FROM mdl_course where id > $courseid AND category not in (0,18,27,52,55,58) AND shortname not like '%ERROR%' AND shortname not like '%OLD%' AND shortname not like '%_TU' LIMIT 1");
      echo "<meta http-equiv='refresh' content='2;url=http://adistanciafparagon.es/itainnova_tool/informes/profesores.php?course=".current($next)->id."&batch=$batch'>";
    }
