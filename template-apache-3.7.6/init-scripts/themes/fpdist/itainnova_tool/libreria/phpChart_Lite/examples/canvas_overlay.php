<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>

    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>

<?php

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s2 = array(array(9, 3.5), array(15, 4.4), array(22, 6.0), array(38, 9.1), array(51, 12.0), array(62, 14.4));

    $pc = new C_PhpChartX(array($s2),'plot2');
    $pc->add_plugins(array('canvasTextRenderer','canvasOverlay'));

    $grid = array('gridLineWidth'=>1.5, 'gridLineColor'=>'rgb(235,235,235)','drawGridlines'=>true);
    $pc->set_grid($grid);

    $pc->set_canvas_overlay(array('show'=>true,
                                 'objects'=>array(array('verticalLine'=>array('name'=>'barney','x'=>10,'lineWidth'=>6,'color'=>'rgb(100, 55, 124)','shadow'=>false)),
                                                  array('verticalLine'=>array('name'=>'fred','x'=>15,'lineWidth'=>12,'yminOffset'=>'8px','ymaxOffset'=>'29px','color'=>'rgb(50, 55, 30)','shadow'=>false)),
                                                  array('dashedVerticalLine'=>array('name'=>'wilma','x'=>10,'lineWidth'=>2,'yOffset'=>14,'color'=>'rgb(133, 120, 24)','shadow'=>false)),
                                                  array('verticalLine'=>array('name'=>'pebbles','x'=>35,'lineWidth'=>3,'yOffset'=>0,'lineCap'=>'butt','color'=>'rgb(89, 198, 154)','shadow'=>false)),
                                                  array('dashedVerticalLine'=>array('name'=>'bam-bam','x'=>45,'lineWidth'=>5,'dashPattern'=>array(16,12),'lineCap'=>'round','yOffset'=>'20px','color'=>'rgb(66, 98, 144)','shadow'=>false)),
                                            )
                                 )
                           );
    $pc->draw(400,300);


?>

    </body>
</html>