<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
		<title>phpChart - Point Label with Custom Graphics</title>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    $s1 = array(
		array(0, 300, '<img height="30px" width="30px" src="images/new.png"/>'), 
		array(1, 150, '<img height="30px" width="30px" src="images/new.png"/>'), 
		array(2, 35, '<img height="30px" width="30px" src="images/new.png"/>'));
  
    $pc = new C_PhpChartX(array($s1),'chart1');
    $pc->add_plugins(array('cursor','pointLabels','barRenderer','categoryAxisRenderer'),true);
	$pc->set_animate(true);
    $pc->set_title(array('text'=>'Point Graphic Label Test'));
    $pc->set_legend(array('show'=>true));
    $pc->set_axes_default(array('useSeriesColor'=> true));
    $pc->set_series_default(array(
           'pointLabels'=> array(
              'show'=> true,
              'escapeHTML'=> false,
              'ypadding'=> -15 
           )
       ));
    $pc->draw(600,400);       

    ?>

    </body>
</html>