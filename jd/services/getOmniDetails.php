<?php

require_once('../config.php');
require_once('includes/omniDetailsClass.php');

//ganeshrj.jdsoftware.com/jdbox_cat/services/getOmniDetails.php?data_city=mumbai&module=me&action=1&usercode=10024775


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

$omniDetailsClassobj = new omniDetailsClass($params);
if($params['action']=='1')
	$result = $omniDetailsClassobj->getBformWebsiteDetails();
if($params['action']=='2')
	$result = $omniDetailsClassobj->saveWebsiteDetails();
if($params['action']=='3')
	$result = $omniDetailsClassobj->getWebsiteDetails();
if($params['action']=='4')
	$result = $omniDetailsClassobj->tempTomain();
if($params['action']=='5')
	$result = $omniDetailsClassobj->deleteWebsiteDetails();
if($params['action']=='6')
	$result = $omniDetailsClassobj->transferToOmni();
if($params['action']=='7')
	$result = $omniDetailsClassobj->setOmniDomain();
if($params['action']=='8')
	$result = $omniDetailsClassobj->saveOmniExtraDetailsTemp();
if($params['action']=='9')
	$result = $omniDetailsClassobj->tempTomainOmniExtraDetails(); 
if($params['action']=='10')
	$result = $omniDetailsClassobj->transferToOmniDemo(); 
if($params['action']=='11')
	$result = $omniDetailsClassobj->checkCategoryType(); 
if($params['action']=='12')
	$result = $omniDetailsClassobj->insertDemoLinkDetails(); 
if($params['action']=='13')
	$result = $omniDetailsClassobj->fetchDemoLinkDetails(); 
if($params['action']=='14')
	$result = $omniDetailsClassobj->domainMappingService();
if($params['action']=='15')
	$result = $omniDetailsClassobj->setFromFinance();
if($params['action']=='16')
	$result = $omniDetailsClassobj->setDomainCs();
if($params['action']=='17')
	$result = $omniDetailsClassobj->forDisplayingDemoProduct();
if($params['action']=='18')
	$result = $omniDetailsClassobj->setOmniDomainCustom(); 
if($params['action']=='19')
	$result = $omniDetailsClassobj->transferToOmniCustom(); 
if($params['action']=='20')
	$result = $omniDetailsClassobj->getOmniTemplateDetails(); 
if($params['action']=='21')
	$result = $omniDetailsClassobj->saveOmniAppTemplateDetails(); 
if($params['action']=='22')
	$result = $omniDetailsClassobj->domainMappingFix(); 
if($params['action']=='23')
	$result = $omniDetailsClassobj->setIosFeesToOmni();
if($params['action']=='24')
	$result = $omniDetailsClassobj->setEmailIdForOmni(); 
if($params['action']=='25')
	$result = $omniDetailsClassobj->setSmsFeesToOmni(); 
if($params['action']=='26')
	$result = $omniDetailsClassobj->omniDealCloseDemoApi(); 
///ssl
if($params['action']=='27'){
	$result = $omniDetailsClassobj->setSSLFeesToOmni();
}
///ssl
print($result);

