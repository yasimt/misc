<?php
require_once('../config.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}
require_once('includes/complaints_against_staff_class.php');

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
$comp_agstaff = new complaints_against_staff_class($params);


if($params['action'] == 1)
{
	$result = $comp_agstaff -> GenerateReport();
}

if($params['action'] == 2)
{
	$result = $comp_agstaff -> updateComplaints();
}

if($params['action'] == 3)
{
	$result = $verificationCode_obj->readValidationCodeFromTable();
}

if($params['action'] == 4)
{
	$result = $verificationCode_obj->ValidateVerificationCode();
}


//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

