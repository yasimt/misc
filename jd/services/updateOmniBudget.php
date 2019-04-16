<?php

require_once('../config.php');
require_once('includes/omniBudgetClass.php');
//ganeshrj.jdsoftware.com/jdbox_cat/services/updateOmniBudget.php?data_city=mumbai&module=tme&action=1&user_price=&parentid=&version= add
//ganeshrj.jdsoftware.com/jdbox_cat/services/updateOmniBudget.php?data_city=mumbai&module=tme&action=-1&parentid=&version= delete

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

$omniBudgetClassobj = new omniBudgetClass($params);
if($params['action']=='1')
	$result = $omniBudgetClassobj->addOmni();
if($params['action']=='3')
	$result = $omniBudgetClassobj->PopulateTempCampaign();
if($params['action']=='4')
	$result = $omniBudgetClassobj->delTempCampaign();
if($params['action']=='5')
	$result = $omniBudgetClassobj->emailIdCheckForWebsite();
if($params['action']=='6')	
	$result = $omniBudgetClassobj->checkUserPrv();
if($params['action']=='7')	
	$result = $omniBudgetClassobj->addIosCampaignTemp();
if($params['action']=='8')	
	$result = $omniBudgetClassobj->deleteIosCampaignTemp();
if($params['action']=='9')	
	$result = $omniBudgetClassobj->addIosCampaign();
if($params['action']=='10')	
	$result = $omniBudgetClassobj->deleteIosCampaign();
if($params['action']=='11')	
	$result = $omniBudgetClassobj->getDependentPackageDetails();
if($params['action']=='12')	
	$result = $omniBudgetClassobj->addSmsCampaign(); 
if($params['action']=='13')	
	$result = $omniBudgetClassobj->deleteSmsCampaign(); 
if($params['action']=='14')	
	$result = $omniBudgetClassobj->tempToMainSms(); 
if($params['action']=='15')	
	$result = $omniBudgetClassobj->getSmsPrice();  
if($params['action']=='16')	
	$result = $omniBudgetClassobj->addAndroidCampaignTemp();  
if($params['action']=='17')	
	$result = $omniBudgetClassobj->addandroidtemplate();
if($params['action']=='18')	
	$result = $omniBudgetClassobj->deleteAndroidCampaign();
if($params['action']=='19')	
	$result = $omniBudgetClassobj->deleteAndroidTemp();
/////////////////////SSL/////////////////////
if($params['action']=='20')	
	$result = $omniBudgetClassobj->addSSLCampaign();
if($params['action']=='21')	
	$result = $omniBudgetClassobj->deleteSSLCampaign();
if($params['action']=='22')	
	$result = $omniBudgetClassobj->tempToMainSSL();
if($params['action']=='23')	
	$result = $omniBudgetClassobj->getSSLPrice(); 
///////////////////SSL///////////////////////
else if($params['action']=='-1')
	$result = $omniBudgetClassobj->deleteOmni();

$resultstr= json_encode($result);

print($resultstr);

