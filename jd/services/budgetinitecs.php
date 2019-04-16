<?php

require_once('../config.php');
require_once('includes/budgetinitecsclass.php');

//prameshjha.jdsoftware.com/jdbox/services/budgetinitecs.php?data_city=mumbai&parentid=PXX22.XX22.150919123119.V4U2&action=updatesummarytable&module=cs&usercode=013084


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

$budgetinitecsclass_obj = new budgetinitecsclass($params);

if($params['action']=='updatesummarytable')
{
	$result = $budgetinitecsclass_obj->updatesummarytable();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
