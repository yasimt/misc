<?php

/* This  file is for more than omni agreement for sending auto mails on dealclose. pls ignore the file name*/
ini_set('display_errors',1); 
ini_set('display_startup_errors',1);
error_reporting(-1) ;
error_reporting(0);
require_once('../config.php');
require_once('includes/omniAgreementClass.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['data_city']))
{
	$params=$_REQUEST;
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}

if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}

$omniAgreementClassobj = new omniAgreementClass($params);
if($params['action']=='1')
	$result = $omniAgreementClassobj->forDealClose();
if($params['action']=='2')
	$result = $omniAgreementClassobj->forApproval(); 
if($params['action']=='3')
	$result = $omniAgreementClassobj->sendApprovalMails();
if($params['action']=='4')
	$result = $omniAgreementClassobj->sendDealCloseMails();
if($params['action']=='5')
	$result = $omniAgreementClassobj->cronForSendingMails();
if($params['action']=='6')
	$result = $omniAgreementClassobj->dumpDataOnApproval();
if($params['action']=='7')
	$result = $omniAgreementClassobj->onBoardingApprovalMails();
if($params['action']=='8')
	$result = $omniAgreementClassobj->onBoardingPostApprovalMail();
if($params['action']=='9')
	$result = $omniAgreementClassobj->sendDealCloseMailsThruProcess();
if($params['action']=='10')
	$result = $omniAgreementClassobj->onBoardingSendMails();
if($params['action']=='11')
	$result = $omniAgreementClassobj->dealCloseSendMails();
if($params['action']=='12'){

	$result = $omniAgreementClassobj->insertLog($mobile,$sms_text,$sms_sent, $params['email_id'],$params['email_subject'], $params['email_text'],$params['email_sent'],$params['comments'],$params['pdf_links']);
}	
if($params['action']>12) 
{
	die('No Such Request');
}

$resultstr= json_encode($result);

//print($resultstr);

