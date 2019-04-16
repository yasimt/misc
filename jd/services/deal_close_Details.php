<?php
 
if($_REQUEST['module'] == 'tme')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
}

require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/deal_close_details_class.php');

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

$deal_close_details_obj = new deal_close_details_class($params);

if($params['action']=='fetchDealClosedetails')
{
	$result = $deal_close_details_obj -> fetchDealClosedetails();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
