<?php
/*
 * fetch_update_gstnData.php
 * 
 * Copyright 2018 Raj Yadav <rajyadav@localhost.localdomain>
 */

//ini_set('display_errors', '1');
ini_set("memory_limit",-1);
set_time_limit(0);
if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('includes/check_downsellEligibility_class.php');

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

//ho"\n <br>params". json_encode($params);
//die('inside jdbox');

$check_downsellEligibility_Obj = new check_downsellEligibility_class($params);

if($params['action']=='iseligible')
{
	$result = $check_downsellEligibility_Obj -> iseligible();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
