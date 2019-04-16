<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');
require_once('../config.php');
require_once('../functions.php');
require_once('includes/company_details_class.php');
//require_once('includes/class_send_sms_email.php');

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

if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
{
	$params['cs_url'] = get_cs_application_url($params['data_city']);
}
else
{
	$params['cs_url'] = "http://vinaydesai.jdsoftware.com/csgenio_test/";
}


if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
print_r($params);
}
//echo json_encode($params); exit;
$company_details_obj = new company_details_class($params);


if($params['action'] == 1)
{
	$result = $company_details_obj->getCompanyDetails();
}

if($params['action'] == 2)
{
	$result = $company_details_obj->getFixedPositionDetails();
}

if($params['action'] == 3)
{
	$result = $company_details_obj->ECS_SI_Mandate_Details();
}

if($params['action'] == 4)
{
	$result = $company_details_obj->GetECS_SI_Billing_Report();
}

if($params['action'] == 5)//block_unblock VN
{
	$result = $company_details_obj->Block_Unblock_VN();
}

if($params['action'] == 6)//feedback report
{
	$result = $company_details_obj->getFeedBackReport();
}

if($params['action'] == 7)//complaint type list
{
	$result = $company_details_obj->getComplainTypes();
}


if($params['action'] == 8)//log complaint 
{
	require_once('includes/class_send_sms_email.php');
	
	$sms_email_Obj	 = new email_sms_send($db,$params['data_city']);
	
	$result = $company_details_obj->LogClientComplaint($sms_email_Obj);
}

if($params['action'] == 9)//fetch logged complaints 
{
	require_once('includes/log_generate_complaint_class.php');
	$new_params = $params;
	$new_params['action'] = 1;
	$log_generate_complaints = new log_generate_complaint_class($new_params);
	
	$result = $log_generate_complaints -> get_all_complaints();
}

if($params['action'] == 10)//jdrr status
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');
	$result = $company_details_obj->getJDRRStatus();
}


if($params['action'] == 34)//get both ECS and SI details
{
	$result['ecs_si_mandate_details']  = $company_details_obj->ECS_SI_Mandate_Details();
	
	$company_details_obj->params['report_type'] = 'ecs';
	$result['ecs_si_billing_report']['ecs'] = $company_details_obj->GetECS_SI_Billing_Report();
	
	$company_details_obj->params['report_type'] = 'si';
	$result['ecs_si_billing_report']['si'] = $company_details_obj->GetECS_SI_Billing_Report();
}

if($params['action'] == 36) // Update edit listing audit data
{
	
	$result = $company_details_obj-> UpdateAuditedCompanyGeneralData();
}

if($params['action'] == 45) // Update edit listing audit data
{
		
	$result = $company_details_obj-> SetRadiusOfSign();
}


if($params['action'] == 6)//feedback report
$resultstr= $result;
else
$resultstr= json_encode($result);

print($resultstr);

