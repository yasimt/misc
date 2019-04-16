<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');
require_once('../config.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}
require_once('includes/log_generate_complaint_class.php');
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

$log_generate_complaint = new log_generate_complaint_class($params);



if($params['action'] == 1)
{
	$result = $log_generate_complaint -> get_all_complaints();
}

if($params['action'] == 2)
{
	
	$result = $log_generate_complaint -> get_complaint_content();
}

if($params['action'] == 3)
{
		
	$result = $log_generate_complaint -> get_complain_types();
}

if($params['action'] == 4)
{
		
	$result = $log_generate_complaint -> get_complain_sources();
}

if($params['action'] == 5)
{
		
	$result = $log_generate_complaint -> getContractCategories();
}

if($params['action'] == 7)
{
		
	$result = $log_generate_complaint -> getComplaintFormInfo();
}


if($params['action'] == 8)
{
		
	$result = $log_generate_complaint -> Log_Update_ComplaintDetails();
}

if($params['action'] == 10)
{
		
	$result = $log_generate_complaint -> FetchSubComplaintType();
}
if($params['action'] == 11)
{
		
	$result = $log_generate_complaint -> Fetchautoid_details();
}
if($params['action'] == 12)
{
		
	$result = $log_generate_complaint -> cmplain_history($params['parentid']);
}

//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

