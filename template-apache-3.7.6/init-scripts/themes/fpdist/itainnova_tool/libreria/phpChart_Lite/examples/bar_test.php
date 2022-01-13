<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>phpChart - Bar Test</title>
</head>
    <body>
        <div><span> </span><span id="info1b"></span></div>

<?php
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s1 = array(2, 6, 7, 10);
    $ticks = array('a', 'b', 'c', 'd');
    
    $pc = new C_PhpChartX(array($s1),'chart1');
    $pc->add_plugins(array('highlighter','pointLabels'));
	$pc->set_animate(true);
	$pc->set_series_default(array(
		'renderer'=>'plugin::BarRenderer',
		'pointLabels'=> array('show'=>true)));
    $pc->set_axes(array(
         'xaxis'=>array(
			'renderer'=>'plugin::CategoryAxisRenderer',
			'ticks'=>$ticks)
    ));
    $pc->set_highlighter(array('show'=>false));
    $pc->bind_js('jqplotDataClick',array(
		'series'=>'seriesIndex',
		'point'=>'pointIndex',
		'data'=>'data'));
    $pc->draw(400,300);

/*
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s1 = array(2, 6, 7, 10);
    $s2 = array(7, 5, 3, 2);
    $ticks = array('a', 'b', 'c', 'd');

    $pc = new C_PhpChartX(array($s1,$s2),'chart2');
    $pc->add_plugins(array('highlighter','pointLabels'));
	$pc->set_animate(true);
	$pc->set_series_default(array(
		'renderer'=>'plugin::BarRenderer',
		'pointLabels'=> array('show'=>true)));
	$pc->set_axes(array(
         'xaxis'=>array(
			'renderer'=>'plugin::CategoryAxisRenderer',
			'ticks'=>$ticks)
    ));
    $pc->bind_js('jqplotDataHighlight',array(
		'series'=>'seriesIndex',
		'point'=>'pointIndex',
		'data'=>'data'));
    $pc->bind_js('jqplotDataUnhighlight',array('Nothing'));
    $pc->draw(600,400);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 2b Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    echo '<div><span id="info2c"></span></div>';

    $s1 = array(2, 6, 7, 10);
    $s2 = array(7, 5, 3, 2);
    $ticks = array('a', 'b', 'c', 'd');

    $pc = new C_PhpChartX(array(array(array(2,1), array(4,2), array(6,3), array(3,4)), array(array(5,1), array(1,2), array(3,3), array(4,4)), array(array(4,1), array(7,2), array(1,3), array(2,4))),'chart2b');
    $pc->add_plugins(array('highlighter','pointLabels'));   
	$pc->set_animate(true);
    $pc->set_series_default(array('renderer'=>'plugin::BarRenderer',
                                  'pointLabels'=> array('show'=>true,'location'=>'e','edgeTolerance'=>-15),
                                  'shadowAngle'=>135,
                                  'rendererOptions'=>array('barDirection'=>'horizontal')));
    $pc->set_axes(array(
         'yaxis'=>array('renderer'=>'plugin::CategoryAxisRenderer')
    ));
    //$pc->set_highlighter(array('show'=>false));
    $pc->bind_js('jqplotDataHighlight',array(
		'series'=>'seriesIndex',
		'point'=>'pointIndex',
		'data'=>'data',
		'pageX'=>'ev.pageX',
		'pageY'=>'ev.pageY'));
    $pc->bind_js('jqplotDataClick',array(
		'series'=>'seriesIndex',
		'point'=>'pointIndex',
		'data'=>'data',
		'pageX'=>'ev.pageX',
		'pageY'=>'ev.pageY'),'','info2c');
    $pc->bind_js('jqplotDataUnhighlight',array('Nothing'));
    $pc->draw(600,400);


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 3 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s1 = array(2, 6, 7, 10);
    $s2 = array(7, 5, 3, 2);
    $s3 = array(14, 9, 3, 8);

    $pc = new C_PhpChartX(array($s1,$s2,$s3),'chart3');
    $pc->add_plugins(array('highlighter','pointLabels'));
    $pc->set_stack_series(true);
	$pc->set_animate(true);
    $pc->set_capture_right_click(true);
    $pc->set_series_default(array(
		'renderer'=>'plugin::BarRenderer',
		'rendererOptions'=>array('highlightMouseDown'=>true),
		'pointLabels'=> array('show'=>true)));
    $pc->set_legend(array('show'=>true,'location'=>'e','placement'=>'outside'));
    $pc->bind_js('jqplotDataRightClick',array('series'=>'seriesIndex','point'=>'pointIndex','data'=>'data'));
    $pc->draw(600,400);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 4 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $pc = new C_PhpChartX(array(array(array(2,1), array(6,2), array(7,3), array(10,4)), array(array(7,1), array(5,2),array(3,3),array(2,4)), array(array(14,1), array(9,2), array(9,3), array(8,4))),'chart4');
    $pc->add_plugins(array('highlighter','pointLabels'));
	$pc->set_animate(true);
    $pc->set_stack_series(true);
    $pc->set_capture_right_click(true);
    $pc->set_series_default(array('renderer'=>'plugin::BarRenderer',
                                  'shadowAngle'=>'135',
                                  'rendererOptions'=>array('highlightMouseDown'=>true,
                                                          'barDirection'=>'horizontal'),
                                  'pointLabels'=>array('show'=>true,'formatString'=>'%d')));
    $pc->set_legend(array('show'=>true,
                          'location'=>'e',
                          'placement'=>'outside'));
    $pc->set_axes(array(
         'yaxis'=>array('renderer'=>'plugin::CategoryAxisRenderer')
    ));

    $pc->draw(600,400);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 5 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $pc = new C_PhpChartX(array(array(array(2,1), array(null,2), array(7,3), array(10,4))),'chart5');
    $pc->add_plugins(array('highlighter'));
	$pc->set_animate(true);
    $pc->set_series_default(array('renderer'=>'plugin::BarRenderer',
                                  'shadowAngle'=>135,
                                  'rendererOptions'=>array('highlightMouseDown'=>true,
                                                          'barDirection'=>'horizontal'),
                                  'pointLabels'=>array('show'=>true,'formatString'=>'%d')));
    $pc->add_series(array('rendererOptions'=>array('highlightMouseDown'=>true, 'barDirection'=>'horizontal')));
    $pc->set_capture_right_click(true);

    $pc->set_legend(array('show'=>true,
                          'location'=>'e',
                          'placement'=>'outside'));
    $pc->set_yaxes(array(
         'yaxis'=>array('renderer'=>'plugin::CategoryAxisRenderer')
    ));

    $pc->draw(600,400);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Pie 6 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $pc = new C_PhpChartX(array(array(1,2,3,4)),'chart6');
    $pc->add_plugins(array('highlighter','pointLabels'));
	$pc->set_animate(true);
    $pc->set_series_default(array('renderer'=>'plugin::PieRenderer'));
    $pc->draw(600,400);

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Bar 7 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $s1 = array(2, -6, 7, -5);
    $ticks = array('a', 'b', 'c', 'd');
    $pc = new C_PhpChartX(array($s1),'chart7');
	$pc->set_animate(true, true);
    $pc->add_plugins(array('highlighter','pointLabels'));
    $pc->set_series_default(array('renderer'=>'plugin::BarRenderer',
                                  'rendererOptions'=>array('fillToZero'=>true),
                                  'pointLabels'=>array('show'=>true)));
    $pc->set_axes(array(
         //'yaxis'=>array('autoscale'=>true),
         'xaxis'=>array('renderer'=>'plugin::CategoryAxisRenderer','ticks'=>$ticks)
    ));

    $pc->draw(300,300);
*/
?>

    </body>
</html>