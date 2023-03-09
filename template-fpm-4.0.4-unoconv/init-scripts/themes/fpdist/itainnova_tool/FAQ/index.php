<?php
error_reporting(E_ALL);
require_once("../../config.php");
global $DB;
$PAGE->set_pagelayout('base');
$title = "Preguntas frecuentes";
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title, new moodle_url(substr($PAGE->url,0,strpos($PAGE->url,'?'))));
$PAGE->set_context(context_system::instance());
echo $OUTPUT->header();
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div style="width:90%;margin-left:5%">
  <center><h1><?=$title?></h1></center><br>
  <?php
  $handle = fopen("template_faq.html", "r");
if ($handle) {
  ?>
  <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <?php
    $tags_for_delete = array("<h1>","</h1>");
    $id = 0;
    while (($line = fgets($handle)) !== false) {
        if(substr( $line, 0, 4 ) === "<h1>"){
          if($id>0){
            echo "</div></div></div>";
          }
          $id+=1;
          $line = str_replace($tags_for_delete,"",$line);
          ?>
          <div class="panel panel-default">
            <div class="panel-heading" id="heading<?=$id?>" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$id?>" aria-expanded="<?php echo (boolval($id)) ? 'false' : 'true';?>" aria-controls="collapse<?=$id?>">
              <h4 class="panel-title">
                <strong><?=$line?></strong>
              </h4>
            </div>
            <div id="collapse<?=$id?>" class="panel-collapse collapse <?php if($id==1) echo 'in'?>" role="tabpanel" aria-labelledby="heading<?=$id?>">
              <div class="panel-body">
          <?php
        }else{
        echo $line;
        }
    }
    echo '</div></div></div></div>';
    fclose($handle);
} else {
    echo "<h2>FAQ no disponible, por favor rellene una incidencia. Muchas gracias por su colaboraci&oacute;n</h2>";
}
echo $OUTPUT->footer();
  ?>
