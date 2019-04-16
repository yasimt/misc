<?php
ini_set("memory_limit",-1);
set_time_limit(0);
require_once('../config.php');
require_once('includes/inventoryblockingserviceclass.php');
require_once('includes/invMgmtClass.php');
require_once('includes/sendMail.php');
require_once('includes/budgetDetailsClass.php');
require_once('includes/regfeeclass.php');

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


$ibsclass_obj = new inventoryblockingserviceclass($params);



if($params['action']=='blockinventory')
{
//echo json_encode($params);
//exit;
$result = $ibsclass_obj->blockinventory();	
}


//print_r($result);
$resultstr= json_encode($result);

print($resultstr);

?>
