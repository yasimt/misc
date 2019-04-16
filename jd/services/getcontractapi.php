<?php

if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}
require_once('../config.php');
require_once('includes/getcontractapiclass.php');
require_once('includes/versioninitclass.php');
require_once('includes/pincodeselectionclass.php');
require_once('includes/budgetinitclass.php');

//prameshjha.jdsoftware.com/jdbox/services/getcontractapi.php?data_city=mumbai&parentid=PXX22.XX22.150919123119.V4U2&action=updatetemptable&module=tme&usercode=013084


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

$getcontractapiclassobj = new getcontractapiclass($params);

if($params['action']=='updatetemptable')
{
	$result = $getcontractapiclassobj->updatetemptable();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
