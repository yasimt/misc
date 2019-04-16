<?php
require_once('../config.php');
require_once('includes/areaDetailsClass.php');
//sunnyshende.jdsoftware.com/jdbox/services/areaDetails.php?data_city=mumbai&latitude=&longitude=&rds=5&opt=DIST

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['data_city']))
{
	$params['data_city']= $_REQUEST['data_city'];
	$params['opt']= $_REQUEST['opt'];
	$params['rds']= $_REQUEST['rds'];
	$params['latitude']= $_REQUEST['latitude'];
	$params['longitude']= $_REQUEST['longitude'];
	$params['pincode']= $_REQUEST['pincode'];
	$params['parentid']= $_REQUEST['parentid'];
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}

if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}

$areadetailsclassobj = new areaDetailsClass($params);

$result = $areadetailsclassobj->getArea();
$resultstr= json_encode($result);

print($resultstr);

