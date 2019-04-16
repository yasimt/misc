<?php
if($_REQUEST["err"]==1){
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
}

require_once('../config.php');
require_once('includes/geoCodeClass.php');





if($_REQUEST["trace"] ==1 )
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
	$params=$_REQUEST;
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

$geoCodeClassObj = new geoCodeClass($params);
if($params['action']=='1')
	$result = $geoCodeClassObj->checkDistance(); 
if($params['action']=='2')
	$result = $geoCodeClassObj->checkDistanceWithPincode(); 
if($params['action']=='3')
	$result = $geoCodeClassObj->getBestLatLong(); 
if($params['action']=='4')
	$result = $geoCodeClassObj->distanceWithTable(); 
if($params['action']=='5'){
	$result = $geoCodeClassObj->pincodeCheck(); 
}
print_new($result);

function print_new($res){
	echo json_encode($res);

}

