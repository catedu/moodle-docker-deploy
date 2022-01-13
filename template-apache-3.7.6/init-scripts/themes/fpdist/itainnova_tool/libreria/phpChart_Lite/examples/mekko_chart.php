<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
  <style type="text/css" media="screen">
    body {
        margin: 15px;
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    }
    
    p {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .jqplot-target {
        margin: 60px;
    }
    
    pre {
        padding: 10px;
        background-color: #efead9;
        margin: 10px;
    }
    .jqplot-axis {
      font-size: 0.8em;
    }
    
    .jqplot-mekko-barLabel {
        font-size: 1em;
    }
    
    #chart2 .jqplot-axis {
        font-size: 0.7em;
    }
    
    #chart3 .jqplot-title {
        padding-bottom: 40px;
    }
  </style>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $bar1 = array(array('shirts', 8),array('hats', 14),array('shoes', 6),array('gloves', 16),array('dolls', 12));
    $bar2 = array(15,6,9,13,6);
    $bar3 = array(array('grumpy',4),array('sneezy',2),array('happy',7),array('sleepy',9),array('doc',7));
    $barLabels = array('Mickey Mouse', 'Donald Duck', 'Goofy');
    
    $pc = new C_PhpChartX(array($bar1,$bar2,$bar3),'chart1');
    $pc->add_plugins(array('canvasTextRenderer'));
	
	$pc->set_title(array('text'=>'Revenue Breakdown per Character'));
    $pc->set_series_default(array('renderer'=>'plugin::MekkoRenderer'));
    $pc->set_legend(array('show'=>true));
    $pc->set_axes_default(array('renderer'=>'plugin::MekkoAxisRenderer'));
    $pc->set_axes(array(
         'xaxis'=>array(
			'barLabels'=>$barLabels,
			'tickOptions'=>array('formatString'=>'$%dM')
		)
    ));
    $pc->draw(600,300);
    
    
/*
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    
    $pc = new C_PhpChartX(array($bar1,$bar2,$bar3),'chart2');
    $pc->add_plugins(array('mekkoRenderer','mekkoAxisRenderer','canvasTextRenderer','canvasAxisLabelRenderer'));
    
    $pc->set_title(array('text'=>'Revenue Breakdown per Character'));
    $pc->set_series_default(array(
			'renderer'=>'plugin::MekkoRenderer',
			'rendererOptions'=>array('borderColor'=>'#dddddd')));
    $pc->set_legend(
			array('show'=>true,
			'rendererOptions'=>array('placement'=>'insideGrid'),
			'location'=>'e'));
    $pc->set_axes_default(array(
			'renderer'=>'plugin::MekkoAxisRenderer',
			'tickOptions'=>array()));
    $pc->set_axes(array(
         'xaxis'=>array(
			'barLabels'=>$barLabels,
			'tickOptions'=>array('formatString'=>'$%dM'),
			'max'=>175,
			'rendererOptions'=>array(
				'barLabelOptions'=>array('angle'=>-35),
				'barLabelRenderer'=>'plugin::CanvasAxisLabelRenderer')),
		'x2axis'=>array(
			'show'=>true,
			'tickMode'=>'even',
			'max'=>175,
			'tickOptions'=>array('formatString'=>'$%dM')
			)
    ));
    $pc->draw(600,300);

     /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 3 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $legendLabels = array('hotels', 'rides', 'buses', 'instruments', 'totes');

    
    $pc = new C_PhpChartX(array($bar1,$bar2,$bar3),'chart3');
    $pc->add_plugins(array('mekkoRenderer','mekkoAxisRenderer','canvasTextRenderer','canvasAxisLabelRenderer'));
    
    $pc->set_title(array('text'=>'Revenue Breakdown per Character'));
    $pc->set_series_default(array(
			'renderer'=>'plugin::MekkoRenderer',
			'rendererOptions'=>array('showBorders'=>false)));
    $pc->set_legend(array(
			'show'=>true,
			'rendererOptions'=>array(
				'placement'=>'outside',
				'numberRows'=>1),
			'location'=>'n',
			'labels'=>$legendLabels));
    $pc->set_axes_default(array(
			'renderer'=>'plugin::MekkoAxisRenderer',
			'tickOptions'=>array('showGridline'=>false)));
    $pc->set_axes(array(
			'xaxis'=>array('tickMode'=>'bar',
			'tickOptions'=>array('formatString'=>'$%dM')
				)
    ));
    $pc->draw(600,300);
*/     
    ?>

    </body>
</html>