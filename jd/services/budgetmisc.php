<?php
require_once('../config.php');
require_once('includes/budgetmiscclass.php');

//header('Content-Type: application/json');
//$params	= json_decode(file_get_contents('php://input'),true);

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

//$params['data_city']='mumbai';//parentid
//echo json_encode($params); exit;
$budgetmiscclass_obj = new budgetmiscclass($params);

if($params['action']=='resetcampaign')
{
	$result = $budgetmiscclass_obj->resetCampaign();
}

$resultstr= json_encode($result);

print($resultstr);

