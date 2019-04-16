<?php

require_once('../config.php');
require_once('includes/dealclosedashboardclass.php');

//http://prameshjha.jdsoftware.com/jdbox/services/dealclosedashboard.php?action=campaignwisecount


if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	echo "<pre>";
}
else
{
	define("DEBUG_MODE",0);
	
	if(! in_array($_REQUEST["action"], array('test')))
	{
		header('Content-type: application/json');
	}
	
	
}

if(isset($_REQUEST))
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

$obj = new dealclosedashboardclass($params);

if($params['action']=='CampaignWiseDetails')
	$result = $obj->CampaignWiseDetails();
	
	
if($params['action']=='campaignwisecount')
	$result = $obj->campaignwisecount();

if($params['action']=='citywisecount')
	$result = $obj->citywisecount();

if($params['action']=='employeewisecount')
	$result = $obj->employeewisecount();

if($params['action']=='citywisecountwithgeocode')
	$result = $obj->citywisecountwithgeocode();

if($params['action']=='recentdealclose')
	$result = $obj->recentdealclose();

if($params['action']=='dealclosegraph')
	$result = $obj->dealclosegraph();
	
	
if($params['action']=='cityWiseDetails')
	$result = $obj->cityWiseDetails();

	
if($params['action']=='GetHourlyCount')
	$result = $obj->GetHourlyCount();
	

if($params['action']=='updatecontactinfo')
    $result = $obj->updatecontactinfo();
    

if($params['action']=='getContractDealClosedDetails')
    $result = $obj->getContractDealClosedDetails();

if($params['action']=='getActualCampaigNames')
    $result = $obj->getActualCampaigNames();    

if($params['action']=='getCityWiseInstrumentAmount')
    $result = $obj->getCityWiseInstrumentAmount();    

if($params['action']=='getAllLiteData')
    $result = $obj->getAllLiteData();    
    
if($params['action']=='getLiteInstrData')
    $result = $obj->getLiteInstrData();   

if($params['action']=='test')
$result = $obj->test();



/*if($params['action']=='5')
	$result = $domainClassobj->registerProcessOnetime();*/
	
	
if(is_array($result))
{
	$result= json_encode($result, JSON_FORCE_OBJECT);
}
else
{
	$result= $result;
}	
print($result);

