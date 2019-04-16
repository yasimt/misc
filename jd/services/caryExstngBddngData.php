<?php
//ini_set('display_errors', '1');
require_once('../config.php');
require_once('includes/budgetinitecsclass.php');
require_once('includes/caryExstngBddngData_class.php');

//http://vishalvinodrana.jdsoftware.com/jdbox/services/caryExstngBddngData.php?data_city=mumbai&parentid=PXX22.XX22.180918180042.K9K5&data_city=mumbai&usercode=009882&username=raj&action=carryExstngBdngDataToNewVersion&version=23&module=me&trace=


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

$caryExstngBddngData_obj = new caryExstngBddngData_class($params);

if($params['action']=='carryExstngBdngDataToNewVersion')
{
	$result = $caryExstngBddngData_obj-> caryExstngBddngData();
}

$resultstr= json_encode($result);

print($resultstr);

?>
