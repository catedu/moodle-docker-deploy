<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

ini_set('max_execution_time', 2147483647);
ini_set("log_errors", 1);
require_once('../libreria/checkAdmin.php');
require('../libreria/PhpSpreadsheet/vendor/autoload.php');
define('ENCUESTA','20200115a');
//define('ENCUESTA','20300000%');
//ECHO '<h1> PRUEBA </h1>';
set_time_limit(0);
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//Obtenemos los datos
$sql_get_data = "SELECT * FROM encuesta_datos WHERE encuesta like :encuesta ORDER BY encuesta";
$data = $DB->get_records_sql($sql_get_data,array('encuesta'=>ENCUESTA));
//Si existen datos
if(count($data)){
  //Obtenemos los modulos
  $sql_get_curso = "SELECT cursos.id as curso_id, cursos.name as curso
  FROM {course_categories} cursos
  WHERE cursos.coursecount > 0
  ORDER BY cursos.id ASC;";
  $cursos = $DB->get_records_sql_menu($sql_get_curso);

  //Creamos la estructura de almacenamiento
  $agregado_obj = array();
  foreach ($data as $row) {
    //Obtenemos el identificador
    $key = $row->id_encuesta;
    //Si no esta creado el objeto en la estructura, lo creamos
    if(!isset($agregado_obj[$key])){
      $agregado_obj[$key] = new StdClass();
      $agregado_obj[$key]->_0_id_encuesta = $key;
    }
    //Separamos las preguntas por fases
    switch ($row->fase) {
      case 0: #Primera parte de la encuesta
      $codigo3 = $row->{'codigo 3'};
      if($codigo3==1){#Genero
        $agregado_obj[$key]->_genero = $row->{'respuesta 2'};
      }elseif($codigo3==2){#Centro
        $agregado_obj[$key]->centro = $row->{'respuesta 2'};
      }elseif($codigo3==3){#ciclo
        $agregado_obj[$key]->ciclo = $row->{'respuesta 2'};
      }
      //Ajustamos el rol
      if(substr($row->encuesta,8,9)=='a'){
        $agregado_obj[$key]->_rol = 'Alumno';
      }else{
        $agregado_obj[$key]->_rol = 'Profesor';
      }
      break;
      case 1:#Segunda parte de la encuesta
      //Mapeamos los valores
      if(!isset($agregado[$key][$seccion]))
      $pregunta = 'p_'.$row->{'codigo 3'};
      if(strlen($row->{'respuesta 1'})){
        $agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
      }else{
        $agregado_obj[$key]->$pregunta = $row->{'respuesta 2'};
      }
      break;
      case 2:#Tercera parte de la encuesta
      if(!isset($agregado[$key][$seccion]))
      if($row->{'respuesta 1'}<0){
        //Es tipo texto
        $pregunta = 't_'.$row->{'codigo 3'};
        $agregado_obj[$key]->$pregunta = html_entity_decode($row->{'respuesta 2'});
      }elseif($row->{'codigo 2'}==0){
        //Pregunta normal
        $pregunta = 'p_'.$row->{'codigo 3'};
        $pregunta.=' '.$cursos[intval($row->{'codigo 1'})];
        $pos = strpos($pregunta,'(');
        if($pos>0)
        $pregunta = substr($pregunta,0,strpos($pregunta,'('));
        $agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
      }else{
        //Practicas (opcional)
        $pregunta = 'pr_'.$row->{'codigo 3'};
        $agregado_obj[$key]->$pregunta = $row->{'respuesta 1'};
      }
      break;
    }
  }
  //Obtenemos los centros
  $sql_get_centros = "SELECT centros.id as centro_id, centros.name
  FROM {course_categories} centros
  INNER JOIN {course_categories} cursos on centros.id = cursos.parent
  WHERE centros.coursecount = 0
  ORDER BY centros.name ASC;";
  $centros =(array) $DB->get_records_sql_menu($sql_get_centros);

  //Creamos la hoja
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setTitle('Alumnos');
  $row = 2;
  $profesor = false;
  //var_export($agregado_obj);
  foreach ($agregado_obj as $persona) {
    if($persona->_rol=='Profesor' && !$profesor){
      //quickSort($sheet);
      //bubbleSort($sheet);
      //minimunSort($sheet);
      $profesor = true;
      $row = 2;
      $sheet = $spreadsheet->createSheet();
      $sheet->setTitle('Profesores');
    }
    foreach ($persona as $key => $value) {

      if($key=="centro")
      $value = $centros[$value];
      if($key=="ciclo")
      $value = $cursos[$value];
      //insertValueToColumnOrAppendSorted($sheet,$value,$key,$row);
      insertValueToColumnOrAppend($sheet,$value,$key,$row);
    }
    $row++;
  }
  //ordenamos la hoja de los Profesores
  //quickSort($sheet);
  //bubbleSort($sheet);
  //minimunSort($sheet);

  //obtenemos las preguntas
  $sql_get_preguntas = "SELECT id,texto,tipo,encuesta,fase FROM encuesta WHERE encuesta like :encuesta ORDER BY id ASC";
  $preguntas = $DB->get_records_sql($sql_get_preguntas,array('encuesta'=>ENCUESTA));
  $sheet = $spreadsheet->createSheet();
  $sheet->setTitle('Preguntas');
  $fila = 1;
  foreach ($preguntas as $pregunta) {
    $key ="";
    switch ($pregunta->tipo) {
      case 'pregunta_texto':
      $key='t_'.$pregunta->id;
      $tipo = strtoupper(substr($pregunta->encuesta,8,9));
      break;
      case 'respuesta':
      $key='p_'.$pregunta->id;
      $tipo = strtoupper(substr($pregunta->encuesta,8,9));
      break;
      case 'pregunta_modulo':
      $key='pr_'.$pregunta->id;
      $tipo = strtoupper(substr($pregunta->encuesta,8,9));
      break;
      case 'titulo':
      $tipo="";
      $fila++;
      break;
      case 'pregunta':
      if($pregunta->fase==0)
      continue 2;
      $tipo="";
      $fila++;
      break;
      default:
      continue 2;
    }
    $sheet->setCellValue('A'.$fila,$key);
    $sheet->setCellValue('B'.$fila,$tipo);
    $sheet->setCellValue('C'.$fila,$pregunta->texto);
    $fila++;
  }

  $nombre_fic = './encuesta/encuesta'.date("Y-m-d").'.xlsx';
  if(file_exists($nombre_fic))
  unlink($nombre_fic);
  $writer = new Xlsx($spreadsheet);
  $writer->save($nombre_fic);

  echo '<a href="'.$nombre_fic.'">Encuesta</a>';
}
/*
* Busca iterativamente si existe una columna con el mismo valor $column
* Si no existe, la inserta ordenador
*/
function insertValueToColumnOrAppendSorted($sheet,$value,$column,$row){
  $iterative_column = 1;
  $inserted = false;

  while(!$inserted){
    $current = $sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue();
    $next = $sheet->getCell(getColumnFromNumber($iterative_column+1).'1')->getValue();
    if(empty($current)){
      $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
      $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);
      $inserted=true;
    }else if(strcmp($current,$column)==0){//Ya esta, insertamos
      $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
      $inserted=true;
    }else if(strcmp($current,$column)<0 && strcmp($next,$column)<0){
      $iterative_column++;
    }else if(strcmp($current,$column)<0 && strcmp($next,$column)>0){
      //$sheet->insertNewColumnBefore(getColumnFromNumber($iterative_column+1), 1);
      desplazarDerecha($sheet,$iterative_column);
      $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
      $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);
      $inserted=true;
      break;
    }else{
      $iterative_column++;
  }
}
}
function desplazarDerecha($sheet,$ColumnFrom){
  $numFilas = 1;
  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
  $numFilas++;

  for($i = $numFilas; $i>$ColumnFrom;$i--){
    $row = 1;
    echo "$i>$ColumnFrom";
    if($row==2) die();
    while(!is_null($sheet->getCell(getColumnFromNumber($row).'1')->getValue())){
      $sheet->setCellValue(getColumnFromNumber($row).($i+1),$sheet->getCell(getColumnFromNumber($row).$i)->getValue());
      $sheet->setCellValue(getColumnFromNumber($row).($i+1),'');
      $row++;
    }
  }
}


/*
* Busca iterativamente si existe una columna con el mismo valor $row
* Si no existe, la añade al final
*/
function insertValueToColumnOrAppend($sheet,$value,$column,$row){
  $iterative_column = 1;
  while(strcmp($sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue(),$column)<>0 && !is_null($sheet->getCell(getColumnFromNumber($iterative_column).'1')->getValue())){
    $iterative_column++;
  }
  $sheet->setCellValue(getColumnFromNumber($iterative_column).$row,$value);
  $sheet->setCellValue(getColumnFromNumber($iterative_column).'1',$column);
  echo "<p>inserted $value on ".getColumnFromNumber($iterative_column)."$row  $column</p>";
}


/*
* Ordena la hoja por el titulo de la columna utilizando el algoritmo de bubblesort
* Coste n^2
*/
function bubbleSort($sheet){
  $numFilas = 1;
  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
  $numFilas++;
  $numColumnas = 1;
  while(!is_null($sheet->getCell(getColumnFromNumber($numColumnas+1).'1')->getValue()))
  $numColumnas++;

  $swap = true;
  while($swap){
    $swap = false;
    for($column = 1; $column < $numColumnas; $column++){
      echo getColumnFromNumber($column).'<br>';
      $left = $sheet->getCell(getColumnFromNumber($column).'1')->getValue();
      $right = $sheet->getCell(getColumnFromNumber($column+1).'1')->getValue();
      if(is_null($left)) $left='';
      if(is_null($right)) $right='';
      if(strcmp($right,$left)<0){
        $swap = true;

        //swap columns
        for($fila = 1; $fila <= $numFilas; $fila++){
          $left =  $sheet->getCell(getColumnFromNumber($column).$fila)->getValue();
          $right =  $sheet->getCell(getColumnFromNumber($column+1).$fila)->getValue();
          if(is_null($left)) $left='';
          if(is_null($right)) $right='';
          $sheet->setCellValue(getColumnFromNumber($column+1).$fila,$left);
          $sheet->setCellValue(getColumnFromNumber($column).$fila,$right);

        } // end for fila
      }//end if
    }//end for i
    $numColumnas--;
  }//end while
}//end function
function minimunSort($sheet){
  echo 'minimunSort<br>';
  set_time_limit(0);
  $numFilas = 1;
  while(!is_null($sheet->getCell('A'.($numFilas+1))->getValue()))
  $numFilas++;
  $numColumnas = 1;
  while(!is_null($sheet->getCell(getColumnFromNumber($numColumnas+1).'1')->getValue()))
  $numColumnas++;


  for($columnasOrdenadas=1;$columnasOrdenadas<$numColumnas;$columnasOrdenadas++){
    $col = findMinimunAlpha($sheet,$columnasOrdenadas,$numColumnas);
    //swap columns
    for($fila = 1; $fila <= $numFilas; $fila++){
      $index =  $sheet->getCell(getColumnFromNumber($columnasOrdenadas).$fila)->getValue();
      $index = str_replace(';','',$index);
      $found =  $sheet->getCell(getColumnFromNumber($col).$fila)->getValue();
      $found = str_replace(';','',$found);

      $sheet->setCellValue(getColumnFromNumber($col).$fila,$index);
      $sheet->setCellValue(getColumnFromNumber($columnasOrdenadas).$fila,$found);


    } // end for fila
  }
}

function findMinimunAlpha($sheet,$ColumnFrom,$ColumnTo){
  $minimunIndex = $ColumnFrom;
  $minimunValue = $sheet->getCell(getColumnFromNumber($ColumnFrom).'1')->getValue();
  for($i = $ColumnFrom+1;$i<$ColumnTo;$i++){
    if(strcmp($minimunValue,$sheet->getCell(getColumnFromNumber($i).'1')->getvalue())>0){
      $minimunIndex=$i;
      $minimunValue=$sheet->getCell(getColumnFromNumber($i).'1')->getvalue();
    }
  }

  return $minimunIndex;
}

/*
* Ordena la hoja por el titulo de la columna utilizando el metodo de quickSort
* Coste n*log n
*/
function quickSort($sheet,$inicial = '1',$final = '0'){
  $i = $inicial;
  if($final==0){
    while(!is_null($sheet->getCell(getColumnFromNumber($final+1).'1')->getValue()))
    $final++;
  }
  $f = $final;

  $pivote = ($final + $inicial)/2;
  $pivote = $sheet->getCell(getColumnFromNumber($pivote).'1')->getValue();

  while($i <= $f){
    while (strcmp($sheet->getCell(getColumnFromNumber($i).'1')->getValue(),$pivote)<0)  $i++;
    while (strcmp($sheet->getCell(getColumnFromNumber($f).'1')->getValue(),$pivote)>0)  $f--;
    if ($i <= $f) {
      swapColumns($i,$f,$sheet);
      $i++;
      $f--;
    }
    if ($inicial < $f)
    quickSort($sheet,$inicial, $f);

    if ($i < $final)
    quickSort($sheet,$i, $final);
  }

}
/*
* funcion auxiliar para quickSort, intercambia toda la columna1 por la columan2
*/
function swapColumns($column1,$column2,$sheet){
  $row = 1;
  $column1 = getColumnFromNumber($column1);
  $column2 = getColumnFromNumber($column2);
  while(!is_null($sheet->getCell('A'.$row)->getValue())){
    $val1 = $sheet->getCell($column1.$row)->getValue();
    $val2 = $sheet->getCell($column2.$row)->getValue();
    $sheet->setCellValue($column1.$row,$val2);
    $sheet->setCellValue($column2.$row,$val1);
    $row++;
  }
}

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
?>
