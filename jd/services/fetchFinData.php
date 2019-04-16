<?php
/*
 * fetchFinData.php
 * 
 * Copyright   2018 Raj Yadav <rajyadav@localhost.localdomain>
 */

//ini_set('display_errors', '1');

if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('includes/fetchFinDataClass.php');

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

$fetchFinDataObj = new fetchFinDataClass($params);

if($params['action']=='fetchBalGaAmt')
{
	$result = $fetchFinDataObj -> fetchBalGaAmt();	
}

if($params['action']=='fetchCampaignWiseBal')
{
	$result = $fetchFinDataObj -> fetchCampaignWiseBal();	
}


if($params['action']=='fetchSinglPaymtParentData')
{
	$result = $fetchFinDataObj -> fetchSinglPaymtParentData();	
}

if($params['action']=='checkInterMediateEntry')
{
	$result = $fetchFinDataObj -> checkInterMediateEntry();	
}


if($params['action']=='fetchPaymtAppnData')
{
	$result = $fetchFinDataObj -> fetchPaymtAppnData();	
}

$resultstr= json_encode($result);

print($resultstr);

?>
