<?php
require_once('../../config.php');
//require("./libreria/BD_debug.php");
$urlTemplate = "./template_faq.html";
$editor1 = file_get_contents($urlTemplate);


$PAGE->set_pagelayout('admin');
$title = "Edici&oacute;n de las preguntas frecuentes" ;
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('ITAINNOVA Tools', new moodle_url('/itainnova_tool'));
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
echo $OUTPUT->header();
$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
if(has_capability('moodle/site:config', $coursecontext)) {
	require_login();
  //Si ha llegado aquí, es admin
  if(!($_POST)){
  ?>
  <center><h1><?=$title?></h1></center>
  <script type="text/javascript" src="../libreria/ckeditor/ckeditor.js"></script>
  <p><br><br> <strong>&#60;h1&#62;</strong> genera una nueva entrada en el FAQ</p><br>
  <form method="post">
    <div style="width:90%;margin-left:5%;margin-bottom:10px">
      <textarea cols="80" id="editor1" name="editor1" rows="10">
        <?php echo $editor1;?>
      </textarea>
      <script type="text/javascript">
      CKEDITOR.replace ("editor1");
      </script>
    </div>
    <center><button class="btn btn-default btn-lg" type="submit">Guardar <i class="fa fa-floppy-o" aria-hidden="true"></i></button></center>
  </form>
    <?php
  }else{
    //Guardamos los cambios
    unlink($urlTemplate);
    $file_handle = fopen($urlTemplate, 'w');
    fwrite($file_handle, $_POST['editor1']);
    fclose($file_handle);
    echo "<h2>FAQ guardado correctamente.</h2>";
  }
}else{
  //Si llega aquí, no es admin
  echo "<center><h1 style='color:red'>Usuario no autorizado</h1></br>";
  echo "<p>Contacte con su administrador del sitio para mas informaci&oacute;n.</p></center>";
}
echo $OUTPUT->footer();
?>
