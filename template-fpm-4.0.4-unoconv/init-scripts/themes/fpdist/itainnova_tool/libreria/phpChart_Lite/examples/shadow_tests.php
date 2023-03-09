<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
		<title>phpChart - Shadow</title>
    </head>
    <body>
        <div><span> </span><span id="info1b"></span></div>


<?php
    
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 1 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $cosPoints1 = array();
    $cosPoints2 = array();
    $cosPoints3 = array();
    $cosPoints4 = array();

    for ($i = 0; $i < 2 * pi(); $i += 0.1) {
        array_push($cosPoints1,array($i, cos($i)));
        array_push($cosPoints2,array($i, cos($i)+1));
        array_push($cosPoints3,array($i, cos($i)+2));
        array_push($cosPoints4,array($i, cos($i)+3));
    }

    $pc = new C_PhpChartX(array($cosPoints1, $cosPoints2, $cosPoints3, $cosPoints4),'plot1');
    $pc->add_plugins(array('barRenderer','categoryAxisRenderer'),true);
    $pc->set_title(array('text'=>'New Shadow Algorithm'));
    $pc->set_series_default(array('showMarker'=>false));
    
    $pc->add_series(array('lineWidth'=>1.5));
    $pc->add_series(array('lineWidth'=>2.5));
    $pc->add_series(array('lineWidth'=>5));
    $pc->add_series(array('lineWidth'=>8));
    
    $pc->draw(600,400);       

/*
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 2 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $line1 = array(1, 4, 9, 16);
    $line2 = array(25, 12.5, 6.25, 3.125);
    $line3 = array(2, 7, 15, 30);

    $pc = new C_PhpChartX(array($line1, $line2, $line3),'plot2');
    $pc->add_plugins(array('barRenderer','categoryAxisRenderer'),true);
    $pc->set_title(array('text'=>'Bar Chart with Shadows'));
    $pc->set_stack_series(true);
    $pc->set_legend(array(
        'show'=> true,
        'location'=> 'nw',
        'xoffset'=> 55
    ));

    $pc->set_series_default(array(
        'renderer'=> 'plugin::BarRenderer',
        'rendererOptions'=> array(
            'barPadding'=> 2,
            'barMargin'=> 40
        )
    ));
    
    $pc->add_series(array('label'=>'Profits'));
    $pc->add_series(array('label'=>'Expenses'));
    $pc->add_series(array('label'=>'Sales'));

    $pc->set_axes(array(
        'xaxis'=> array(
            'renderer'=> 'plugin::CategoryAxisRenderer',
            'ticks'=> array('1st Qtr', '2nd Qtr', '3rd Qtr', '4th Qtr') 
        )
    ));
    
    $pc->draw(400,600);       
    
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Chart 3 Example
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    $line1 = array(1, 4, 3, 2);
    $line2 = array(7, 1, 2, 1);
    $line3 = array(2, 7, 1, 3);

    $pc = new C_PhpChartX(array($line1, $line2, $line3),'chart3');
    
    $pc->add_plugins(array('barRenderer','categoryAxisRenderer'),true);
    $pc->set_title(array('text'=>'Bar Chart with Shadows'));
    //$pc->set_stack_series(true);
    $pc->set_legend(array(
        'show'=> true,
        'location'=> 'nw',
        'xoffset'=> 55
    ));

    $pc->set_series_default(array(
        'renderer'=> 'plugin::BarRenderer',
        'rendererOptions'=> array(
            'barPadding'=> 10,
            'barMargin'=> 10
        )
    ));
    
    $pc->add_series(array('label'=>'Profits'));
    $pc->add_series(array('label'=>'Expenses'));
    $pc->add_series(array('label'=>'Sales'));

    $pc->set_axes(array(
        'xaxis'=> array(
            'renderer'=> 'plugin::CategoryAxisRenderer',
            'ticks'=> array('1st Qtr', '2nd Qtr', '3rd Qtr', '4th Qtr') 
        ),
        'yaxis'=>array('min'=>0)
    ));
    
    $pc->draw(400,600);       
*/
    ?>

    </body>
</html>