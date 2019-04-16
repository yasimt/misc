<?php

if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('includes/fetch_update_sms_promo_class.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
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

//echo"\n <br>params". json_encode($params);
//die('inside jdbox');

$sms_promo_obj = new fetch_update_sms_promo_class($params);

if($params['action']=='updatetemptable')
{
	$result = $sms_promo_obj->updatetemptable();	
}

if($params['action']=='fetchBudgetDetails')
{
	$result = $sms_promo_obj->fetchBudgetdetails();	
}

if($params['action']=='fetchTempData')
{
	$result = $sms_promo_obj->fetchTempData();	
}

if($params['action']=='PopulateTempTable')
{
	$result = $sms_promo_obj->PopulateTempTable();	
}



if($params['action']=='PopulateMainTable')
{
	$result = $sms_promo_obj->PopulateMainTable();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
