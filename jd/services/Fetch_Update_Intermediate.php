<?php
/*
 * fetchFinData.php
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
require_once('includes/class_fetch_update_intermediate.php');

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

$fetch_update_intermediate = new class_fetch_update_intermediate($params);

if($params['action']=='fetchIntermediateData')
{
	$result = $fetch_update_intermediate -> fetchIntermediateData();	
}

if($params['action']=='fetchExistingNarration')
{
	$result = $fetch_update_intermediate -> fetchExistingNarration();	
}

if($params['action']=='updateIntermediateData')
{
	$result = $fetch_update_intermediate -> updateIntermediateData();	
}

if($params['action']=='fetchSubSource')
{
	$result = $fetch_update_intermediate -> fetchSubSource();	
}


if($params['action']=='fetchSummaryData')
{
	$result = $fetch_update_intermediate -> fetchSummaryData();	
}

if($params['action']=='fetchSummaryDatMain')
{
	$result = $fetch_update_intermediate -> fetchSummaryMainData();	
}
 

$resultstr= json_encode($result);

print($resultstr);

?>
