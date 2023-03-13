<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
		<title>phpChart - Point Labels</title>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    
    

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $line1 = array(array(-12,7, null),array(-3,14, null),array(2,-1, '<'),array(7,-1, '<'),array(11,11, null), array(13, -1, '<'));
  
    $pc = new C_PhpChartX(array($line1),'chart1');
    $pc->add_plugins(array('cursor','pointLabels','barRenderer','categoryAxisRenderer'),true);
	$pc->set_animate(true);
    $pc->set_title(array('text'=>'Plot with Zooming with Point Labels'));
    $pc->set_axes_default(array('useSeriesColor' => true));
    $pc->set_series_default(array('showMarker' =>false, 'pointLabels'=>array('location'=>'s', 'ypadding' =>2)));
    $pc->set_cursor(array('tooltipLocation'=>'sw', 'zoom'=>true));
    $pc->set_axes(array('yaxis'=>array('pad'=> 1.3)));
    $pc->draw(600,400);       

/*
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $pop1980 = array(7071, 2968, 3005, 1595, 789);
    $pop1990 = array(7322, 3485, 2783, 1630, 983);
    $pop2000 = array(8008, 3694, 2896, 1974, 1322);
    $ticks = array("New York", "Los Angeles", "Chicago", "Houston", "Phoenix");
    
    $pc = new C_PhpChartX(array($pop1980,$pop1990,$pop2000),'chart2');
    $pc->add_plugins(array('cursor','pointLabels','barRenderer','categoryAxisRenderer'),true);
	$pc->set_animate(true);
    $pc->set_title(array('text'=>'City Population (thousands)'));
    $pc->set_series_default(          array( 'renderer'=> 'plugin::BarRenderer',
           'pointLabels'=> array(
                'location'=> 's',
				'ypadding'=> 15
           ))
);
    $pc->set_axes(array(
           'xaxis'=> array(
               'label'=> 'City',
               'renderer'=> 'plugin::CategoryAxisRenderer',
               'ticks'=> $ticks
           ),
           'yaxis'=> array(
               'max'=> 9000,
               'min'=> 500,
               'tickOptions'=> array(
                   'formatString'=> '%d'
                )
           )
       ));
    $pc->add_series(array('label'=>'1980'));
    $pc->add_series(array('label'=>'1990'));
    $pc->add_series(array('label'=>'2000'));
    $pc->add_series(array('label'=>'2008 (est)'));
    $pc->set_legend(array('show'=>true));
    $pc->draw(600,400);   

*/
?>

    </body>
</html>