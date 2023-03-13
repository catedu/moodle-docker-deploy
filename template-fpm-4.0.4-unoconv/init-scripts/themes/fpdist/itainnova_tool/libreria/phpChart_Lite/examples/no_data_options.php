<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<title>phpChart - No Data Points</title>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    
    $line1 = array(1, 3, 5, 7, 9);
    

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $pc = new C_PhpChartX(array(),'chart1');

    $pc->jqplot_show_plugins(true);
    $pc->set_title(array('text'=>'Chart'));
    $pc->set_series_default(array('yaxis'=>'y2axis'));
    $pc->set_no_data_indicator(array(
        'show'=>true,
        'indicator'=>'<img src="ajax-loader.gif" />',
        'axes' => array(
            'xaxis'=>array('min'=>0,'max'=>5,'tickInterval'=>1,'showTicks'=>false),
            'yaxis'=>array('min'=>0,'max'=>8,'tickInterval'=>2,'showTicks'=>false),
        )
    ));
    
    $pc->draw(800,300);   

    ?>

    </body>
</html>