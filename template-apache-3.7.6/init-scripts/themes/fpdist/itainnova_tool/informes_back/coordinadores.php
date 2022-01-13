<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../libreria/checkAdmin.php');
require_once('../libreria/mpdf7/autoload.php');

$centro = required_param('centro', PARAM_INT);
$offset = optional_param('offset',0,PARAM_INT);
$sqlciclos = "SELECT MIN(id) as id,name, count(id) as pendientes
              FROM {course_categories}
              WHERE parent = :centro
              AND id > :ciclo";
$ciclo = array_values($DB->get_records_sql($sqlciclos,array('centro'=>$centro,'ciclo'=>$offset)))[0];
$last_september = (date("m")>8) ? gmmktime(0,0,0,9,1,date("y")) : gmmktime(0,0,0,9,1,date("y")-1);
$sql_profesores_por_centro = "SELECT role_assignments.id,course.id as courseid, user.id as userid, CONCAT(user.firstname,' ',user.lastname) as name, course.fullname, course.shortname, course.id as curso
FROM {user} user
JOIN {role_assignments} role_assignments ON user.id = role_assignments.userid
JOIN {context} context ON role_assignments.contextid = context.id
JOIN {course} course ON course.id = context.instanceid
WHERE context.contextlevel = 50
AND role_assignments.roleid = 3
AND course.shortname not like 'OLD%'
AND course.category = :coursecategory";
$sqlconexiones = "SELECT DISTINCT(from_unixtime(timecreated,'%Y-%m-%d')) as dia
FROM {logstore_standard_log}
WHERE userid = :userid
AND action = :action
AND timecreated >= :time";
$profesores = array_values($DB->get_records_sql($sql_profesores_por_centro,array('coursecategory'=>$ciclo->id)));
if(isset($_POST['num_prof'])){
  $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
  //Lo ciframos con una clave de 128 bits, para evitar ediciones
  //Permitimos copiar e imprimir
  //https://mpdf.github.io/reference/mpdf-functions/setprotection.html
  $mpdf->SetProtection(array('copy','print','print-highres'), '', '1nf0rm3.2107', 128);
  $mpdf->AddPage('P');
  $stylesheet = file_get_contents('alumnos.css');
  $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
  $mpdf->WriteHTML("<h1 align='center'>Plataforma de Formaci&oacute;n Profesional a Distancia del Gobierno de Arag&oacute;n</h1>");
  $mpdf->WriteHTML("<br><h2 align='center'>Informe de seguimiento del profesorado</h2>");
  //$ciclo = current($DB->get_records('course_categories',array('id'=>$ciclo)));
  $mpdf->WriteHTML('<div style="position: absolute; margin-top: auto; margin-left: auto; margin-right: auto; margin-bottom: auto;"><img src="portada_informe_profesores.png" width="90%" /></div>');
  $mpdf->WriteHTML("
  <div style='position: fixed; left: 0mm; bottom: 0mm;'>
  <table class='tablaDatos' align='left' style='width:100%'>
  <tbody><tr><td><strong>Ciclo:</strong></td><td>$ciclo->name</td></tr>
  <tr><td><strong>Fecha informe:</strong></td><td>".date('d-m-Y')."</td></tr>
  </tbody></table></div>");
  $num = $_POST['num_prof'];
  foreach ($profesores as $profesor) {
    $header = array (
      'odd' => array (
        'L' => array (
          'content' => 'Informe de seguimiento del profesorado',
          'font-size' => 10,
          'font-style' => 'B',
          'font-family' => 'serif',
          'color'=>'#000000'
        ),
        'C' => array (
          'content' => (strrpos($profesor->fullname,'_') === False ? $profesor->fullname : substr($profesor->fullname,strrpos($profesor->fullname,'_')+1)) ,
          'font-size' => 10,
          'font-style' => 'B',
          'font-family' => 'serif',
          'color'=>'#000000'
        ),
        'R' => array (
          'content' => date('d-m-Y'),
          'font-size' => 10,
          'font-style' => 'B',
          'font-family' => 'serif',
          'color'=>'#000000'
        ),
        'line' => 1,
      ),
    );
    $mpdf->SetHeader($header);
    $mpdf->AddPage('L');
    if(isset($dom))
      unset($dom);
    $dom = new DOMDocument();
    $dom->loadHTML($_POST[$profesor->userid.'-calendar-'.$profesor->curso]);
      $dom->getElementsByTagName('svg')->item(0)->setAttribute('height','350px');
      $dom->getElementsByTagName('svg')->item(0)->setAttribute('width','910px');
      $dom->replaceChild($dom->getElementsByTagName('svg')->item(0), $dom->documentElement);

    $nodes = $dom->getElementsByTagName('text');
    foreach ($nodes as $node) {
      switch ($node->nodeValue) {
        case '0':
        case '10':
        $node->nodeValue = '';
        break;
        case 'Jan':
        $node->nodeValue = 'Enero';
        break;
        case 'Feb':
        $node->nodeValue = 'Febrero';
        break;
        case 'Mar':
        $node->nodeValue = 'Marzo';
        break;
        case 'Apr':
        $node->nodeValue = 'Abril';
        break;
        case 'May':
        $node->nodeValue = 'Mayo';
        break;
        case 'Jun':
        $node->nodeValue = 'Junio';
        break;
        case 'Jul':
        $node->nodeValue = 'Julio';
        break;
        case 'Aug':
        $node->nodeValue = 'Agosto';
        break;
        case 'Sep':
        $node->nodeValue = 'Septiembre';
        break;
        case 'Oct':
        $node->nodeValue = 'Octubre';
        break;
        case 'Nov':
        $node->nodeValue = 'Noviembre';
        break;
        case 'Dec':
        $node->nodeValue = 'Diciembre';
        break;
      }
    }
    //Quitamos la escala de colores (arriba derecha)
    $nodes = $dom->getElementsByTagName('path');
    for($i=0;$i<$nodes->length;$i++){
      if (strpos($nodes[$i]->getAttribute("fill"), 'ABSTRACT_RENDERER_ID_1') !== false ){
        $nodes[$i]->parentNode->removeChild($nodes[$i]);
        $i--;
      }
      if($nodes[$i]<> NULL && $nodes[$i]->getAttribute('stroke') == '#eeeeee'){
        $nodes[$i]->parentNode->removeChild($nodes[$i]);
        $i--;
      }
    }
    $mpdf->WriteHTML($dom->saveHTML());
    $img = $_POST[$profesor->userid.'-clicks-'.$profesor->curso];
    //Numero de conexiones de este profesor.
    $sqltotalconexiones = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE action = :action
    AND userid = :userid
    AND timecreated >= :time ";
    $totalconexiones = $DB->count_records_sql($sqltotalconexiones,array('action'=>'loggedin','userid'=>$profesor->userid,'time'=>$last_september));
    //Numero de dias que se ha conectado el profesor
    $sqldias = "SELECT count(DISTINCT(DATE(FROM_UNIXTIME(timecreated))))
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    GROUP BY DATE(FROM_UNIXTIME(timecreated))";
    $dias = count($DB->get_records_sql($sqlconexiones,array('userid'=>$profesor->userid,'action'=>'loggedin','time'=>$last_september)));
    $sqlregistros = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND timecreated >= :time ";
    $registros = $DB->count_records_sql($sqlregistros,array('userid'=>$profesor->userid,'courseid'=>$profesor->courseid,'time'=>$last_september));
    //Numero de mensajes enviados desde la mensajeria de la plataforma
    $sqlmensajes = "SELECT count(id)
    FROM {message}
    WHERE useridfrom = :userid
    AND timecreated >= :time";
    $num_mensajes = $DB->count_records_sql($sqlmensajes,array('userid'=>$profesor->userid,'time'=>$last_september));
    //Numero de correos recibidos
    $sqlmensajesrecibidos = "SELECT count(id)
    FROM {message}
    WHERE useridto = :userid
    AND timecreated >= :time ";
    $num_mensajes_recibidos = $DB->count_records_sql($sqlmensajesrecibidos,array('userid'=>$profesor->userid,'time'=>$last_september));
    //Correo local_mail
    $sqllocalmail = "SELECT count(local_mail_messages.id)
    FROM {local_mail_messages} local_mail_messages
    JOIN {local_mail_message_users} local_mail_message_users ON local_mail_messages.id = local_mail_message_users.messageid
    WHERE role = 'from'
    AND local_mail_message_users.userid = :userid
    AND local_mail_messages.courseid = :courseid
    AND time >= :time ";
    $localmail = $DB->count_records_sql($sqllocalmail,array('userid'=>$profesor->userid,'courseid'=>$profesor->courseid,'time'=>$last_september));
    //Numero de mensajes en los foros
    $sqlposts = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND component = 'mod_forum'
    AND action = 'created'
    AND ( target = 'post' OR target = 'discussion' )
    AND timecreated >= :time ";
    $posts = $DB->count_records_sql($sqlposts,array('userid'=>$profesor->userid,'courseid'=>$profesor->courseid,'time'=>$last_september));
    $sqlactualizados = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND action = 'updated'
    AND component = 'core'
    AND target = 'course_module'
    AND timecreated >= :time";
    $actualizados = $DB->count_records_sql($sqlactualizados,array('userid'=>$profesor->userid,'courseid'=>$profesor->courseid,'time'=>$last_september));
    //Subida de archivos
    $sqlnuevos = "SELECT count(id)
    FROM {logstore_standard_log}
    WHERE userid = :userid
    AND courseid = :courseid
    AND component = 'core'
    AND action = 'created'
    AND target = 'course_module'
    AND timecreated >= :time";
    $nuevos = $DB->count_records_sql($sqlnuevos,array('userid'=>$profesor->userid,'courseid'=>$profesor->courseid,'time'=>$last_september));
    $mpdf->WriteHTML('
    <div style="width:100%;max-height:200px;"><img style="max-height:200px;" src='.$img.'></div>
    <table style="width:100%;padding-top:50px" class="tablaDatos">
    <tr>
    <th>Descripci&oacute;n</th>
    <th style="text-align:center">Resultado</th>
    <th>Descripci&oacute;n</th>
    <th>Resultado</th>
    </tr>
    <tr class="tr-0">
    <td>N&uacute;mero de conexiones</td>
    <td style="text-align:center">'.$totalconexiones.'</td>
    <td>N&uacute;mero de d&iacute;as que se ha conectado</td>
    <td style="text-align:center">'.$dias.'</td>
    </tr>
    <tr class="tr-1">
    <td>N&uacute;mero de registros del curso</td>
    <td style="text-align:center">'.$registros.'</td>
    <td>N&uacute;mero de mensajes enviados del curso</td>
    <td style="text-align:center">'.$num_mensajes.'</td>
    </tr>
    <tr class="tr-0">
    <td>N&uacute;mero de mensajes recibidos</td>
    <td style="text-align:center">'.$num_mensajes_recibidos.'</td>
    <td>N&uacute;mero de correos enviados a trav&eacute;s de la plataforma</td>
    <td style="text-align:center">'.$localmail.'</td>
    </tr>
    <tr class="tr-1">
    <td>N&uacute;mero de post\'s escritos en los foros del curso</td>
    <td style="text-align:center">'.$posts.'</td>
    <td>N&uacute;mero de actualizaciones de recursos que ha realizado</td>
    <td style="text-align:center">'.$actualizados.'</td>
    </tr>
    <tr class="tr-0">
    <td>N&uacute;mero de subida de recursos que ha realizado</td>
    <td style="text-align:center">'.$nuevos.'</td>
    </tr>
    </table>');


  }
  $nombre_fic = 'coordinadores/'.substr($profesor->shortname,0,strrpos($profesor->shortname,'_')).'_'.date("d-m-Y").'.pdf';
  if(is_siteadmin($USER->id)){
    $mpdf->Output($nombre_fic, \Mpdf\Output\Destination::FILE);
    $mpdf->WriteHTML($nombre_fic);
  }
  if($ciclo->pendientes>1){
  echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=http://".$_SERVER[HTTP_HOST].strstr($_SERVER[REQUEST_URI],'?',true).'?centro='.$centro.'&amp;offset='.$ciclo->id."'/>";
  $_POST = array();
  unset($dom);
}else{
  $title = "Informe de actividad de los profesores";
  $PAGE->set_title($title);
  $PAGE->set_heading($title);
  $PAGE->set_cacheable(false);
  $PAGE->navbar->ignore_active();
  $PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
  $PAGE->navbar->add('Informes', new moodle_url('/itainnova_tool/informes'));
  $PAGE->set_pagelayout('frametop');
    echo $OUTPUT->header();
    $zipname = './coordinadores/'.substr($profesor->shortname,0,strpos($profesor->shortname,'_')).'_'.date("d-m-Y").'.zip';

    $zip = new ZipArchive();
    if(file_exists($zipname))
        unlink ($zipname);
    if ($zip->open($zipname, ZIPARCHIVE::CREATE) != TRUE) {
        echo '<h2>Error al crear el fichero zip</h2>';
    }else{
      $options = array('remove_all_path' => TRUE);
      $zip->addGlob('coordinadores/'.substr($profesor->shortname,0,strpos($profesor->shortname,'_')).'*'.date("d-m-Y").'.pdf',GLOB_BRACE,$options);
      unlink('coordinadores/'.substr($profesor->shortname,0,strpos($profesor->shortname,'_')).'*'.date("d-m-Y").'.pdf');
      // close and save archive
      $zip->close();
      echo "<a href='$zipname'>Descargar</a>";
      echo "<iframe width='1' height='1' frameborder='0' src='$zipname'></iframe>";
    }
    echo $OUTPUT->footer();

}
  //$mpdf->Output();
}else{
  $title = "Informe de actividad de los profesores";
  $PAGE->set_title($title);
  $PAGE->set_heading($title);
  $PAGE->set_cacheable(false);
  $PAGE->navbar->ignore_active();
  $PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
  $PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
  $PAGE->set_pagelayout('frametop');
  echo $OUTPUT->header();

  //Aquí llamamos a la API de google para generar los gráficos
  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script> var callbacks = 0; </script>
  <?php
  $sql_clicks_dia = "SELECT week(from_unixtime(timecreated),1) AS date,count(action) AS clicks
  FROM {logstore_standard_log} log
  WHERE userid = :userid
  AND courseid = :courseid
  AND timecreated >= :time
  GROUP BY date";
  $sqlconexionescurso = "SELECT DISTINCT(from_unixtime(timecreated,'%Y-%m-%d')) as dia
  FROM {logstore_standard_log}
  WHERE userid = :userid
  AND courseid = :courseid
  AND timecreated >= :time";
  for($i=0;$i<count($profesores);$i++){
    $calendario = $profesores[$i]->userid.'-calendar-'.$profesores[$i]->curso;
    $clicks = $profesores[$i]->userid.'-clicks-'.$profesores[$i]->curso;
    $conexiones = $DB->get_records_sql($sqlconexionescurso,array('userid'=>$profesores[$i]->userid,'courseid'=>$profesores[$i]->courseid,'time'=>$last_september));
    $actividad = $DB->get_records_sql($sql_clicks_dia,array('userid'=>$profesores[$i]->userid,'courseid'=>$profesores[$i]->courseid,'time'=>$last_september));
    ?>

    <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ['Fecha', 'Clicks', { role: 'style' },{ role: 'annotation' }],
        <?php
        date_default_timezone_set('Europe/Madrid');
        if(count($actividad)==0)
        echo "['Sin actividad',0,'#e74c3c',0],";
        foreach ($actividad as $click){
          $fecha = new DateTime();
          $fecha->setISODate(date('Y'), $click->date);
          echo "['".strftime("%d-%b",$fecha->getTimestamp())."',$click->clicks,'#e74c3c',$click->clicks],";
          unset($fecha);
        }
        ?>
      ]);


      var options = {
        title: "Actividad por semana",
        height: 300,
        titleTextStyle: {fontSize: 36},
        legend: 'none',
        chartArea:{left:0,top:50,width:'100%',height:'70%'}
      };

      var chart_div = document.getElementById('<?=$clicks?>');
      var chart = new google.visualization.ColumnChart(chart_div);

      // Wait for the chart to finish drawing before calling the getImageURI() method.
      google.visualization.events.addListener(chart, 'ready', function () {
        chart_div.innerHTML = '<input style="visibility:hidden" name="<?=$clicks?>" value="' + chart.getImageURI() + '">';
        callbacks = callbacks +1;
        if(callbacks == <?=count($profesores)?>*2){
          document.getElementById("image").submit();
          document.getElementById("title").innerHTML="Generando PDFs...";
        }
      });

      chart.draw(data, options);

    }
  </script>

  <script type="text/javascript">
  google.charts.load("current", {packages:["calendar"]});
  //45 -> Sept-17 Cambiar a current para nueva version
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var dataTable = new google.visualization.DataTable();
    dataTable.addColumn({ type: 'date', id: 'Date' });
    dataTable.addColumn({ type: 'number', id: 'count' });
    dataTable.addRows([
      <?php
      $comas = 0;
      foreach ($conexiones as $dia) {
        $dia = explode("-",$dia->dia);
        $dia[2]=intval($dia[2]);
        $dia[1]-=1;
        if($comas>0) echo ",";
        echo "\n[ new Date($dia[0], $dia[1], $dia[2]), 10 ]";
        $comas++;
      }
      ?>
      /*  [ new Date(2017, 9, 4), 10 ],
      [ new Date(2017, 9, 5), 10 ],
      [ new Date(2017, 9, 12), 10 ],
      [ new Date(2017, 9, 13), 10 ],
      [ new Date(2017, 9, 19), 10 ],
      [ new Date(2017, 9, 23), 10 ],
      [ new Date(2017, 9, 24), 10 ],
      [ new Date(2017, 9, 30), 10 ]*/
    ]);

    var chart = new google.visualization.Calendar(document.getElementById('<?=$calendario?>'));
    var chart_div = document.getElementById('<?=$calendario?>');
    google.visualization.events.addListener(chart, 'ready', function () {
      var contenedor = document.getElementById('<?=$calendario?>');
      chart_div.innerHTML = "<input style='visibility:hidden' name='<?=$calendario?>' value='" + contenedor.innerHTML + "'>";
      //chart_div.innerHTML = contenedor.innerHTML;
      callbacks = callbacks +1;
      if(callbacks == <?=count($profesores)?>*2){
        document.getElementById("image").submit();
        document.getElementById("title").innerHTML="Generando PDFs...";
      }
    });
    var options = {
      title: "<?=htmlentities($profesores[$i]->name)?>",
      height: 500,
      tooltip: {isHtml: false},
      calendar: { daysOfWeek: 'DLMXJVS',
      cellColor: {
        stroke: '#F5F5F5',      // Color the border of the squares.
        strokeOpacity: 0.5, // Make the borders half transparent.
        strokeWidth: 2      // ...and two pixels thick.
      }},
      colorAxis : {
        minValue: 0,
        maxValue: 10,
        colors: ['#000000', '#01bc07']},

      };

      chart.draw(dataTable, options);
    }
    </script>
    <?php
  } //END FOR profesores
  ?>
</head>
<body>
  <center>
    <h1 id="title">Generando gr&aacute;ficos... (<?=count($profesores)?>)</h1>
    <h5>Por favor, espere</h5>
  </center>
  <form id='image' method='post'>
    <input style="visibility:hidden" name="num_prof" value="<?=count($profesores)?>">
    <?php
    foreach ($profesores as $profesor) {
      ?>
      <div id='<?=$profesor->userid?>-calendar-<?=$profesor->curso?>'></div>
      <div id='<?=$profesor->userid?>-clicks-<?=$profesor->curso?>'></div>
      <?php
    }
    ?>
  </form>
  <?php
  echo $OUTPUT->footer();
}
?>
