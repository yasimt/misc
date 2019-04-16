<?php
ini_set("memory_limit",-1);
set_time_limit(0);
//ini_set("ERROR",E_ALL);
ini_set("display_errors", 1);
//error_reporting(E_ALL );
require_once('../config.php');
require_once('includes/budgetDetailsClass.php');
require_once('includes/regfeeclass.php');
require_once('includes/nationallistingclass.php');
require_once('includes/budgetDetailsWrapperClass.php');
require_once('includes/budgetsubmitclass.php');


#http://prameshjha.jdsoftware.com/jdbox/services/budgetDetailsWrapper.php?data_city=Mumbai&parentid=P1008739&ver=22&module=tme&action=getbudgetinitialmanagecampaign&trace=0
#http://prameshjha.jdsoftware.com/jdbox/services/budgetDetailsWrapper.php?data_city=Mumbai&tenure=12&parentid=P1008739&ver=22&module=tme&action=getpindata&catlist=12779&trace=0

if($_REQUEST["trace"] ==1 && !defined("DEBUG_MODE"))
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

#define("DEBUG_MODE",1);

if(isset($_REQUEST['data_city']))
{
	$params['data_city']	= $_REQUEST['data_city'];
	
	if(isset($_REQUEST['ver']))
	$params['version']		= $_REQUEST['ver'];
	
	if(isset($_REQUEST['parentid']))
	$params['parentid']		= $_REQUEST['parentid'];
	
	if(isset($_REQUEST['tenure']))
	$params['tenure']		= $_REQUEST['tenure'];
	
	if(isset($_REQUEST['mode']))
	$params['mode']		    = $_REQUEST['mode']; // 1-best positon 2-fixed position 3-package	
	
	if(isset($_REQUEST['option']))
	$params['option']	    = $_REQUEST['option']; // default 1, max 7
	
	if(isset($_REQUEST['action']))
	$params['action']	    = strtolower($_REQUEST['action']); 
	
	if(isset($_REQUEST['catlist']))
	$params['catlist']	    = strtolower($_REQUEST['catlist']); 
	
	
	if(isset($_REQUEST['onlyExclusive']))
	$params['onlyExclusive']= $_REQUEST['onlyExclusive']; // default 1, max 7
	
	if(isset($_REQUEST['module']))
	$params['module']	    = $_REQUEST['module']; // module - tme , me 
	
	if(isset($_REQUEST['cpf']))
	$params['custompackage'] = $_REQUEST['cpf']; // custom package flag
	
	if(isset($_REQUEST['pbgtyrly']))
	$params['packagebgt_yrly'] = $_REQUEST['pbgtyrly']; // custom package flag
	
	if(isset($_REQUEST['pinbgt']))
	$params['pinbgt'] 	= $_REQUEST['pinbgt']; // 1 - custom pincode wise budget flag
	
	if(isset($_REQUEST['pinview']))
	$params['pinview'] 	= $_REQUEST['pinview']; // 1-pincode view 0-category view
	
	
	if(isset($_REQUEST['catBudget']))
	$params['catBudget'] = $_REQUEST['catBudget'];
	
	if(isset($_REQUEST['packageBudget']))
	$params['packageBudget'] = $_REQUEST['packageBudget'];
	
	if(isset($_REQUEST['pdgBudget']))
	$params['pdgBudget'] 	 = $_REQUEST['pdgBudget'];
	
	if(isset($_REQUEST['actual_bgt']))
	$params['actual_bgt'] 	 = $_REQUEST['actual_bgt'];
	
	if(isset($_REQUEST['totBudget']))
	$params['totBudget'] 	 = $_REQUEST['totBudget'];
	
	if(isset($_REQUEST['city_bgt']))
	$params['city_bgt'] 	= $_REQUEST['city_bgt'];
	
	if(isset($_REQUEST['customBudget']))	
	$params['customBudget'] 	= $_REQUEST['customBudget'];
	
	if(isset($_REQUEST['reg_bgt']))
	$params['reg_bgt'] 	= $_REQUEST['reg_bgt'];
	
	if(isset($_REQUEST['removeCatStr']))
	$params['removeCatStr'] 	= $_REQUEST['removeCatStr'];
	
	if(isset($_REQUEST['nonpaidStr']))
	$params['nonpaidStr'] 	= $_REQUEST['nonpaidStr'];
	
	if(isset($_REQUEST['usercode']))
	$params['usercode'] 	= $_REQUEST['usercode'];
		
	 if(isset($_REQUEST['skippackage']))
	 $params['skippackage'] 	= $_REQUEST['skippackage'];
	 
	 if(isset($_REQUEST['oldpackageBudget']))
	 $params['oldpackageBudget'] 	= $_REQUEST['oldpackageBudget'];
	 
	
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



if(empty($params['data_city']) || empty($params['parentid']) || empty($params['action']) || ( empty($params['version']) && !in_array(strtolower($params['action']),array('getfinancemaindata','getfinancetempdata')) ) )
{
	$result['results'] = array();
	$result['error']['code'] = 1;	
	$result['error']['msg'] = "parameters missing";
}
else
{
	$params['data_city']= strtolower($params['data_city']);
	
	if( in_array(strtolower($params['action']),array('getactualapiresponse','getbudgetinitial')) && ( empty($params['tenure']) || empty($params['mode']) || empty($params['option'])))
	{
		$result['results'] = array();
		$result['error']['code'] = 1;	
		$result['error']['msg'] = "parameter missing";
		$resultstr= json_encode($result);
		print($resultstr);
		exit;
	}
	
	
	$budgetDetailsWrapperClassobj = new budgetDetailsWrapperClass($params);
	$params['action']=strtolower(trim($params['action']));
	
	if($params['action']=='getactualapiresponse')
	{
		$result= $budgetDetailsWrapperClassobj->getactualapiresponse();
	
	}elseif($params['action']=='getbudgetinitial')
	{
		$result= $budgetDetailsWrapperClassobj->getbudgetinitial();
		
	}elseif($params['action']=='getpindata')
	{
		$result= $budgetDetailsWrapperClassobj->getpindata();
		
	}elseif($params['action']=='submitbudget')
	{		
		$result= $budgetDetailsWrapperClassobj->submitbudget();
	
	}elseif($params['action']=='submitbudgetphonesearch')
	{		
		$result= $budgetDetailsWrapperClassobj->submitbudgetphonesearch();
	
	}elseif($params['action']=='getbudgetinitialmanagecampaign')
	{
		$result= $budgetDetailsWrapperClassobj->getbudgetinitialmanagecampaign();
	
	}elseif($params['action']=='getbudgetinitialphonesearch')
	{
		$result= $budgetDetailsWrapperClassobj->getbudgetinitialphonesearch();
	
	}elseif($params['action']=='getfinancetempdata')
	{
		$result= $budgetDetailsWrapperClassobj->getfinancetempdata();
	
	}elseif($params['action']=='paymenttypedealclosed')
	{
		$result= $budgetDetailsWrapperClassobj->paymenttypedealclosed();
	
	}elseif($params['action']=='getfinancemaindata')
	{
		$result= $budgetDetailsWrapperClassobj->getfinancemaindata();
	
	}elseif($params['action']=='tbflexibgttbws')
	{
		$result= $budgetDetailsWrapperClassobj->tbflexibgttbws();
	
	}else
	{
		$result['results'] = array();
		$result['error']['code'] = 1;	
		$result['error']['msg'] = "invalid action";
	}
	
	
$resultstr= json_encode($result);
print($resultstr);

if( in_array(strtolower($params['action']),array('getbudgetinitial','submitbudget','getbudgetinitialmanagecampaign','getbudgetinitialphonesearch','submitbudgetphonesearch') ) )
{
	$budgetDetailsWrapperClassobj->centraliselogging($params,$params['action'],null,$result);
}
	
	
}

