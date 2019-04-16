<?php
require_once('../config.php');
require_once('includes/regfeeclass.php');

//http://prameshjha.jdsoftware.com/jdbox/services/regfee.php?campaignid=1&contactno=8800900009&data_city=mumbai

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

//echo json_encode($params); exit;
$regfeeclassobj = new regfeeclass($params);
$contactno=$params['contactno'];
$result = $regfeeclassobj->getRegfee($contactno);
//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

