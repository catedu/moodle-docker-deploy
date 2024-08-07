<?php
error_reporting(E_ALL);
require_once("../../config.php");
$PAGE->set_pagelayout('base');
$title = "Aviso";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();
global $USER;

//print_r($_POST);
require("../libreria/class.utilidades.php");
if (isloggedin()) {
  if(isset($_POST['logout'])){
    require_logout();
    redirect($PAGE->url."/encuesta_p.php");

  }else{
    ?><center>
      <div style="width:70%;padding-top:10px">
        <h2><?=$title?></h2>
        <div class="alert alert-danger" role="alert">Has iniciado sesi&oacute;n como <?=$USER->firstname?>. La encuesta es <b>an&oacute;nima</b> pero quiz&aacute;s quieras cerrar sesi&oacute;n primero.</div>
      </div>
      <form method="post">
        <input type="hidden" name="logout" value="true"></input>
        <div>
          <button type="submit" class="btn btn-lg">Cerrar sesi&oacute;n y continuar</button>
          <a class="btn btn-lg" href="encuesta_p.php">Continuar</a>
        </div>
      </form>
    </center>
    <?php
  }
}else{
  echo '<META HTTP-EQUIV="Refresh" CONTENT="0;URL=encuesta_p.php">';
}
echo $OUTPUT->footer();
?>
