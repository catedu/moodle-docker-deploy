<?php
require_once("../conf.php");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>phpChart - Basic Chart Animated</title>
</head>
<body>
    
<?php
$pc = new C_PhpChartX(array(array(11, 9, 5, 12, 14)),'basic_chart');
$pc->set_animate(true);
$pc->set_title(array('text'=>'Basic Chart Animated'));
$pc->draw();
?>

</body>
</html>