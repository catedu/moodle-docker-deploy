<?php
require_once('../libreria/checkAdmin.php');
$urlBodyTemplate = "../Fragmentos_Mail/informe_actividad_excel/body.html";
$urlBodyTemplateAlumnos = "../Fragmentos_Mail/informe_actividad_alumnos/body.html";
$editor1 = file_get_contents($urlBodyTemplate);
$editor2 = file_get_contents($urlBodyTemplateAlumnos);
$title = "Informes";

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url, 0, strpos($PAGE->url, '?'))));
$PAGE->set_url(new moodle_url('/itainnova_tool/informes/index.php', array('id'=> $courseid, 'batch'=> $batch)));
$PAGE->set_pagelayout('frametop');
echo $OUTPUT->header();
echo '<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';

if (isset($_POST['editor1'])) {
  $myfile = fopen($urlBodyTemplate, "w") or die("Unable to open file!");
  $txt = htmlspecialchars($_POST['editor1']);
  fwrite($myfile, $txt);
  fclose($myfile);
} elseif (isset($_POST['editor2'])) {
  $myfile = fopen($urlBodyTemplateAlumnos, "w") or die("Unable to open file!");
  $txt = htmlspecialchars($_POST['editor2']);
  fwrite($myfile, $txt);
  fclose($myfile);
}
?>
<div id="tabs" style="height:500px;margin-top:10px">
  <ul>
    <li style="width:14ch;margin-top:1ch;margin-left:1ch;">Coordinadores</span></li>
    <li><a href="#coordinadores">Generar informes</a></li>
    <li style="width:11ch;margin-top:1ch;margin-left:1ch;">Profesores</span></li>
    <li><a href="#profesores-editar">Editar mensaje</a></li>
    <li><a href="#profesores-enviar">Enviar</a></li>
    <li style="width:9ch;margin-top:1ch;margin-left:1ch;">Alumnos</span></li>
    <li><a href="#alumnos-editar">Editar mensaje</a></li>
    <li><a href="#alumnos-enviar">Enviar</a></li>
    <li style="width:6ch;margin-top:1ch;margin-left:1ch;">Otros</span></li>
    <li><a href="#ver-informes" onclick="openFilemanager();">Ver informes</a></li>
  </ul>
  <div id="coordinadores">
    <div style="width:90%">
      <center>
    <form method="get" action="coordinadores.php">
      <select name="centro" style="width:auto" required>
        <option>Selecciona el centro</option>
        <?php
        $sqlcentro = "SELECT id, name
        FROM {course_categories}
        WHERE parent NOT IN
          (SELECT id
            FROM {course_categories})
          AND id NOT IN (0,18,27,52,53,55,58)
            ORDER BY name";
            $centros = $DB->get_records_sql($sqlcentro);
            foreach ($centros as $centro) {
              echo "<option value='$centro->id'>$centro->name</option>";
            }
            ?>
          </select>
          <input type="submit" value="Generar">
        </form>
      </center>
      </div>
  </div>
  <div id="profesores-editar">
    <form method="post">
      <p>Plantilla por defecto: <a href="<?php echo $urlBodyTemplate;?>"><?php echo $urlBodyTemplate;?></a></p>
      <textarea style="width:100%;" id="editor1" name="editor1" cols="50" rows="5">
        <?=$editor1?>
      </textarea>
      <input type="submit" value="Guardar" style="margin-top:25px">
    </form>
  </div>
  <div id="profesores-enviar">
    <center>
      <form method="get" action="profesores.php">
        <?php
        $sql_cursos_no_enviados_hoy = "SELECT id,fullname
        FROM mdl_course
        WHERE id NOT IN
        (SELECT courseid FROM mdl_itainnova_log WHERE mdl_itainnova_log.logdate = CURRENT_DATE)
        AND category NOT IN (0,18,27,52,55,58)
		AND shortname NOT LIKE '%ERROR%'
        AND shortname NOT LIKE '%OLD%'
        AND shortname NOT LIKE '%_TU'
        ORDER BY id ASC";
				
		//AND category=27 Bambalinas

        $result = $DB->get_records_sql_menu($sql_cursos_no_enviados_hoy);
        echo "<p><strong>Num. Cursos pendientes hoy:</strong>".count($result)."</p>";
        echo '<select name="course" required><option value="">Elige curso de inicio</option>';
        foreach ($result as $id => $nombre_curso) {
          echo "<option value='$id'>$id- $nombre_curso</option>";
        }
        ?>
      </select>
      <br>
      <input type='checkbox' name='batch' value=1 checked>Avanzar al siguiente curso autom&aacute;ticamente<br>
      <input type="submit" value="Enviar informes" style="margin-top:50px">
	  <p><strong><em>NOTA:<em></strong> Para que se vean los encabezados de las actividades en el informe, el curso debe tener un alumno Prueba.</p>
    </form>
  </center>
</div>
<div id="alumnos-editar">
  <form method="post">
    <p>Plantilla por defecto: <a href="<?php echo $urlBodyTemplateAlumnos;?>"><?php echo $urlBodyTemplateAlumnos;?></a></p>
    <textarea style="width:100%;" id="editor2" name="editor2" cols="50" rows="5">
      <?=$editor2?>
    </textarea>
    <input type="submit" value="Guardar" style="margin-top:25px">
  </form>
</div>
<div id="alumnos-enviar">
  <center>
    <form action="alumnos.php" method="get">
      <p><strong>Env&iacute;os hechos hoy:</strong><?=$DB->count_records('itainnova_log', array('logdate'=>date("Y-m-d"),'source'=>'alumnos.php'))?></p>
      <label>N&uacute;mero de informes por iteraci&oacute;n
        <input style="width:6ch;" name="step" type="number" placeholder="Num. informes por iteraci&oacute;n" step="5" min="5" max="100" value="25"></label>
        <input type="submit" value="Enviar informes">
      </form>
    </center>
  </div>
  <div id="ver-informes">
    <p>El gestor de ficheros se ha abierto en un PopUp, <strong><a onclick="openFilemanager();">haz click aqu&iacute; <i class="fa fa-external-link-square" aria-hidden="true"></i></a></strong> para volver a abrirlo</p>
  </div>
</div>
<script src="../libreria/ckfinder/ckfinder.js"></script>
<script>
function openFilemanager(){
  CKFinder.popup( 'ckfinder1', {
    height: 600
  } );
}
</script>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
  $( "#tabs" ).tabs();
} );
</script>

<script src="//cdn.ckeditor.com/4.7.3/full/ckeditor.js"></script>
<script type="text/javascript">
CKEDITOR.replace("editor1");
CKEDITOR.replace("editor2");
</script>

<?php
echo $OUTPUT->footer();
?>
