<?php

require_once('../config.php');
require_once('includes/domainClass.php');

//ganeshrj.jdsoftware.com/jdbox_cat/services/domain_service.php?data_city=mumbai&module=me&action=1&usercode=10024775&domain_name=www.ganeshrj.com


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

$domainClassobj = new domainClass($params);
if($params['action']=='1')
	$result = $domainClassobj->checkAvailibity();
if($params['action']=='2')
	$result = $domainClassobj->getPrice();
#if($params['action']=='3')
	//$result = $domainClassobj->registerWebsite();
if($params['action']=='4')
	$result = $domainClassobj->getAllPrice();
if($params['action']=='5')
	$result = $domainClassobj->addNoOfEmails();
if($params['action']=='6')
	$result = $domainClassobj->addEmailTemp(); 
if($params['action']=='7')
	$result = $domainClassobj->getEmailPricingDirecti(); 
if($params['action']=='8')
	$result = $domainClassobj->tempTomain(); 
if($params['action']=='9')
	$result = $domainClassobj->deleteEmailCampaign(); 
#if($params['action']=='10')
	//$result = $domainClassobj->switchBasedSelection();  

/*if($params['action']=='5')
	$result = $domainClassobj->registerProcessOnetime();*/
print($result);

