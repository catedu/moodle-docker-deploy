<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<title>phpChart - Multiple Y Axes</title>
  <style type="text/css" media="screen">
    .jqplot-axis {
      font-size: 0.85em;
    }
    .jqplot-title {
      font-size: 1.1em;
    }
    
    .jqplot-y6axis-tick {
      padding-right: 0px;
    }

/* Use this to hide an axis */
/*    .jqplot-y3axis {
      display: none;
    }*/
  </style>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    

    $l1 = array(2, 3, 1, 4, 3);
    $l2 = array(1, 4, 3, 2, 2.5);
    $l3 = array(14, 24, 18, 8, 22);
    $l4 = array(102, 104, 153, 122, 138);
    $l5 = array(843, 777, 754, 724, 722);
    

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pc = new C_PhpChartX(array($l1,$l2,$l3,$l4,$l5),'chart1');

    $pc->add_plugins(array('highlighter'));
    $pc->set_title(array('text'=>'Default Multiply y axes'));
    
	$pc->set_animate(true);

    $pc->add_series(array('yaxis'=>'y2axis'));
    $pc->add_series(array('yaxis'=>'y3axis'));
    $pc->add_series(array('yaxis'=>'y4axis'));
    $pc->add_series(array('yaxis'=>'y5axis'));
    $pc->add_series(array('yaxis'=>'y6axis'));
    
    $pc->set_highlighter(array('bringSeriesToFront'=>true));
    
    $pc->set_axes(array(
        'yaxis'=>array('autoscale'=>true),
        'y2axis'=>array('autoscale'=>true),
        'y3axis'=>array('autoscale'=>true),
        'y4axis'=>array('autoscale'=>true),
        'y5axis'=>array('autoscale'=>true),
        'y6axis'=>array('autoscale'=>true),
    ));
    $pc->draw(800,300);   

/*
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pc = new C_PhpChartX(array($l1,$l2,$l3,$l4,$l5),'chart2');

    $pc->add_plugins(array('highlighter'));
    $pc->set_title(array('text'=>'Customized Multiply y axes'));
    
	$pc->set_animate(true);

    $pc->add_series(array());
    $pc->add_series(array('yaxis'=>'y2axis'));
    $pc->add_series(array('yaxis'=>'y3axis'));
    $pc->add_series(array('yaxis'=>'y4axis'));
    $pc->add_series(array('yaxis'=>'y5axis'));
    $pc->add_series(array('yaxis'=>'y6axis'));
    
    $pc->set_axes_default(array('useSeriesColor'=>true));
    
    $pc->set_highlighter(array('bringSeriesToFront'=>true));
    
    $pc->set_axes(array(
        'yaxis'=>array('autoscale'=>true),
        'y2axis'=>array('autoscale'=>true,'padMax'=>2),
        'y3axis'=>array('autoscale'=>true,'padMax'=>2.5),
        'y4axis'=>array('autoscale'=>true,'padMax'=>2),
        'y5axis'=>array('autoscale'=>true,'padMax'=>2.3),
        'y6axis'=>array('autoscale'=>true),
    ));
    
    $pc->set_grid(array('gridLineWidth'=>1.0,'borderWidth'=>2.5,'shadow'=>false));
    
    $pc->draw(800,300);   
 
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 3 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pc = new C_PhpChartX(array($l1,$l3,$l4,$l5),'chart3');

    $pc->add_plugins(array('highlighter'));
    $pc->set_animate(true);

    $pc->set_axes_default(array('show'=>false));
    $pc->set_grid_padding(array('top'=>0,'bottom'=>0,'left'=>0,'right'=>44));
    $pc->set_highlighter(array('bringSeriesToFront'=>true));  
    
    $pc->draw(800,300);   
    
     /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 4 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pc = new C_PhpChartX(array($l1,$l3,$l4,$l5),'chart4');

    $pc->add_plugins(array('highlighter'));
    $pc->set_animate(true);

    $pc->set_axes_default(array('show'=>false));
    $pc->set_axes(array(
        'xaxis'=>array('show'=>false),
        'yaxis'=>array('show'=>false)
    ));    
    $pc->set_highlighter(array('bringSeriesToFront'=>true));     
    $pc->draw(800,300);   
    
*/	
?>

    </body>
</html>