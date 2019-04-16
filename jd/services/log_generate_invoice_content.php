<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
require_once('../config.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}
require_once('includes/log_generate_invoice_content_class.php');
require_once('includes/contractdetailsclass.php');

//http://prameshjha.jdsoftware.com/jdbox/services/regfee.php?campaignid=1&contactno=8800900009&data_city=mumbai


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

}

if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
print_r($params);
}
//echo json_encode($params); exit;

$log_generate_invoice = new log_generate_invoice_content_class($params);

$params_contract_details = $params;
$params_contract_details['action'] ='GetPlatDiamCategories';


if($params['action'] == 1)
{
	$contractdetailsclassobj = new contractdetailsclass($params_contract_details);
	$result = $log_generate_invoice -> log_invoice_content($contractdetailsclassobj);
}

if($params['action'] == 2)
{
	
	$result = $log_generate_invoice -> get_invoice_content();
}

if($params['action'] == 3)
{
		
	$result = $log_generate_invoice -> get_invoice_versions();
}


if($params['action'] == 4)
{
    $contractdetailsclassobj = new contractdetailsclass($params_contract_details);
    $logData    = $log_generate_invoice -> log_invoice_content($contractdetailsclassobj);
    $result     = $log_generate_invoice -> get_invoice_content();
}
if($params['action'] == 5)
{
	
    $contractdetailsclassobj = new contractdetailsclass($params_contract_details);
    $result    = $log_generate_invoice -> get_ecs_invoice_content($contractdetailsclassobj);
}
if($params['action'] == 6){
	
    $contractdetailsclassobj = new contractdetailsclass($params_contract_details);
    $result    = $log_generate_invoice -> get_all_instrument($contractdetailsclassobj);
    
	
}

//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

