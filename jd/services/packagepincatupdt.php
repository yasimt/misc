<?php

ini_set("memory_limit",-1);
set_time_limit(0);

#error_reporting(1);
#error_reporting(E_ALL ~ E_NOTICE);
#ini_set("display_errors", 1);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

require_once('../config.php');
require_once('../library/configclass.php');
require_once('../library/class.Curl.php');

require_once('includes/packagepincatupdtclass.php');
require_once('includes/nationallistingclass.php');
require_once('includes/regfeeclass.php');
require_once('includes/budgetDetailsClass.php');


if(isset($_REQUEST["trace"]))
{
	if($_REQUEST["trace"] ==1)
	{
		define("TRACE_MODE",1);
		define("DEBUG_MODE",0);		
	}
	elseif($_REQUEST["trace"] ==11)
	{
		define("TRACE_MODE",1);	
		define("DEBUG_MODE",1);
	}
		echo "<pre>";
}
else
{
	define("TRACE_MODE",0);
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}
 //echo '<br>-DEBUG_MODE-='.DEBUG_MODE;
 //echo '<br>-TRACE_MODE-='.TRACE_MODE.'<br>';
//http://prameshjha.jdsoftware.com/jdbox/services/inventoryblockingservice.php?action=blockinventory&data_city=mumbai&parentid=P1103602&liveversion=23&newversion=33


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

//echo json_encode($params);
//exit;


$ibsclass_obj = new packagepincatupdtclass($params);



if($params['action']=='apportion')
{
	$result = $ibsclass_obj->apportion();	
}elseif($params['action']=='packbudgetcalbypin')
{
	$result = $ibsclass_obj->packbudgetcalbypin();	
}


//print_r($result);
$resultstr= json_encode($result);

print($resultstr);
$ibsclass_obj->centraliselogging($result,'api response',$params['source']);

?>
