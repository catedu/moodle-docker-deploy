<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>phpChart - Bubble Chart</title>
</head>
    <body>
        <div><span> </span><span id="info1b"></span></div>

<?php
    

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s1 = array(array(0.6, 2.6, 12, 'Ford'), array(0.5, 3, 16, 'GM'), array(1.3, 2, 17, 'VW'), array(1.2, 1.2, 13, 'Mini'), array(2.7, 1.5, 5), array(1.7, 1.2, 4), array(1.6, 2.9, 3), array(0.3, 0.6, 2), array(1.3, 2.2, 10, 'Franklin'), array(1.1, 1.3, 13, 'Nissan'), array(1, 1, 12, 'Chrysler'), array(2, 2.5, 11, 'Audi'));
    
    $pc = new C_PhpChartX(array($s1),'chart1');
   // $pc->add_plugins(array('bubbleRenderer'));

    $pc->sort_data(true);
    $pc->set_title(array('text'=>'Bubble Test'));
    $pc->set_series_default(array('renderer'=>'plugin::BubbleRenderer','rendererOptions'=>array('autoscalePointsFactor'=>-.15,'bubbleAlpha'=>0.6,'highlightAlpha'=>0.8),'highlightMouseDown'=>true,'shadow'=>true,'shadowAlpha'=>0.05));
    $pc->add_series(array('breakOnNull'=>true));
//    $pc->set_axes(array(
//         'xaxis'=>array('min'=>0,'max'=>18,'tickInterval'=>2),
//    ));

    $pc->draw(600,400);
?>

    </body>
</html>