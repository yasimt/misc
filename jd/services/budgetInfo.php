<?php
ini_set("memory_limit",-1);
ini_set('max_execution_time', 300);
require_once('../config.php');
require_once('includes/budgetinfoClass.php');
require_once('includes/budgetDetailsClass.php');
require_once('includes/regfeeclass.php');

//http://prameshjha.jdsoftware.com/jdbox/services/budgetInfo.php?parentid=PXX22.XX22.160523111209.V6M9&data_city=mumbai&action=getlivecatpindetails

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
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



if(!isset($params['action']) || $params['action']=='')
{
	$errorarray['errormsg']='action missing';
	echo json_encode($errorarray); exit;
}

if(DEBUG_MODE)
{
	echo '<pre>params';
	print_r($params);
}

$budgetinfoobj = new budgetinfoClass($params);

if($params['action']=='getlivecatpindetails') // for package only 
{	
	$result = $budgetinfoobj->getlivecatpindetails();

}elseif($params['action']=='getlivepdgpackcatpindetails')  // for package and pdg
{
	$result = $budgetinfoobj->getlivepdgpackcatpindetails();

}elseif($params['action']=='paymenttypedealclosed')  // for package and pdg
{
	$result = $budgetinfoobj->paymenttypedealclosed();

}elseif($params['action']=='paymenttypedealclosedwithcampaignname')  // for package and pdg
{
	$result = $budgetinfoobj->paymenttypedealclosedwithcampaignname();

}


if(DEBUG_MODE)
{
	echo "<pre>finalresultoutput"; print_r($result);
}

$resultstr= json_encode($result,JSON_FORCE_OBJECT);
print($resultstr);

?>
