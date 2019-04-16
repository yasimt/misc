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
require_once('includes/verification_code_class.php');
require_once('includes/class_send_sms_email.php');

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
$verificationCode_obj = new verification_code_class($params);


if($params['action'] == 1)
{
	$result = $verificationCode_obj->GenerateRandomValidationCode();
}
if($params['action'] == 2)
{
	$verificationCode = $verificationCode_obj->GenerateRandomValidationCode();
	
	$smsObj	 = new email_sms_send($db,$params['data_city']);
	
	$res = $verificationCode_obj->sendEmailSms($smsObj,$verificationCode);
	
	if(DEBUG_MODE)
	{
		echo '<br>verificaton code sent in sms res  :: '.$res;
	}
	
	$result = $verificationCode_obj->writeValidationCodeInTable($verificationCode);
	
	if(DEBUG_MODE)
	{
		echo '<br>verificaton code insert res  :: '.$result;
	}
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

