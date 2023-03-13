<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>phpChart - Basic Chart</title>
</head>
  
<body>
<style type="text/css">
#basic_chart_3 .jqplot-point-label {
  border: 1.5px solid #aaaaaa;
  padding: 1px 3px;
  background-color: #eeccdd;
}
</style>
<?php
$data1 = array(array(11, 123, 1236, "Acura"), array(45, 92, 1067, "Alfa Romeo"));
$pc = new C_PhpChartX(array($data1),'basic_chart');
$pc->set_title(array('text'=>'Basic Chart'));
$pc->add_plugins(array('cursor'));
/*$pc->set_series_default(array(
	'renderer'=>'plugin::BubbleRenderer',
	'rendererOptions'=>array('bubbleGradients'=>true)));
*/
$pc->add_series(array('label'=>'Sales'));
$pc->set_legend(array('show'=>true,'placement'=>'outsideGrid'));
$pc->set_cursor(array("show"=>true,'zoom'=>true));
$pc->draw();

/*

$data1 = array(14, 32, 41, 44, 40, 47, 53, 67);
$pc = new C_PhpChartX(array($data1),'basic_chart_2');
$pc->set_title(array('text'=>'Basic Chart'));
$pc->set_animate(true);
$pc->add_plugins(array('pointLabels', 'cursor'));
$pc->set_series_default(array('showMarker'=>true));
$pc->set_cursor(array("show"=>true,'zoom'=>true));
$pc->draw();

$data1 = array(14, 32, 41, 44, 40);
$pc = new C_PhpChartX(array($data1),'basic_chart_3');
$pc->set_title(array('text'=>'Basic Chart'));
$pc->set_animate(true);
$pc->add_plugins(array('pointLabels'));
$pc->set_series_default(array(
	'renderer'=>'plugin::BarRenderer',
	'showMarker'=>true));
$pc->add_series(array(
	'pointLabels'=>array(
		'show'=>true,
		'labels'=>array('fourteen', 'thirty two', 'fourty one', 'fourty four', 'fourty'))));
$pc->set_axes(array(
	'xaxis'=>array('rendnerer'=>'plugin::CategoryAxisRenderer'),
	'yaxis'=>array('padMax'=>1.3)));
$pc->draw();


$data1 = array();
$pc = new C_PhpChartX(array($data1),'basic_chart_4');
$pc->set_title(array('text'=>'Basic Chart 4'));
$pc->set_data_renderer("js::sineRenderer");
$pc->add_plugins(array('pointLabels'));
$pc->set_animate(true);
$pc->draw();

*/
?>
<script>
sineRenderer = function() {
	var data = [[]];
	for (var i=0; i<13; i+=0.5) {
	  data[0].push([i, Math.sin(i)]);
	}
	return data;
  };
</script>

<?php
$data1 = array();
$pc = new C_PhpChartX('./jsondata.txt','basic_chart_ajax');
$pc->set_title(array('text'=>'Basic Chart Ajax'));
$pc->set_data_renderer("js::ajaxDataRenderer");
$pc->draw();
?>
<script>
var ajaxDataRenderer = function(url, plot)
		{
			var ret = null;
			$.ajax({
				// have to use synchronous here, else returns before data is fetched
				async: false,
				url: url,
				dataType:'json',
				success: function(data) {
					ret = data;
				}
			});
			return ret;
		};
</script>



</body>
</html>

