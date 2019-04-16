<?php
require_once('../config.php');
require_once('includes/packagebackendentrclass.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	//header('Content-type: application/json');
}


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}


echo " <br> START-- Time:".date('Y-m-d H:i:s');	
$testclass = new packagebackendentrclass($params);
$testclass->process();

echo " <br> END-- Time:".date('Y-m-d H:i:s');	


?>
