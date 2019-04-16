<?php
/*
 * fetch_update_gstnData.php
 * 
 * Copyright 2018 Raj Yadav <rajyadav@localhost.localdomain>
 */

//ini_set('display_errors', '1');
if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('includes/fetch_update_gstnData_class.php');

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

$gstnData_obj = new fetch_update_gstnData_class($params);

if($params['action']=='fetchgstnData')
{
	$result = $gstnData_obj -> fetchgstnData();	
}

if($params['action']=='PopulategstnData')
{
	
	$result = $gstnData_obj -> PopulategstnData();
}



$resultstr= json_encode($result);

print($resultstr);

?>
