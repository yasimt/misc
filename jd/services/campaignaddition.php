<?php
set_time_limit(0);
require_once('../config.php');
require_once('includes/campaignadditionclass.php');
require_once('../library/configclass.php');
require_once('../library/class.Curl.php');

//http://prameshjha.jdsoftware.com/jdbox/services/campaignaddition.php?action=viewdata&data_city=remote&version=1&trace=1


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
	if(! in_array($_REQUEST["action"], array('todaysprocessedddata','process')))
	{
		header('Content-type: application/json');
	}
}

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
//header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

//echo json_encode($params);
//exit;


$campaignadditionclassobj = new campaignadditionclass($params);
$result= null;

if($params['action']=='process')
{
	$result = $campaignadditionclassobj->process();	
}

if($params['action']=='viewdata')
{
	$result = $campaignadditionclassobj->viewdata();	
}

if($params['action']=='addcampaigns')
{
	$result = $campaignadditionclassobj->addcampaigns();	
}
if($params['action']=='addbanners')
{
	$result = $campaignadditionclassobj->addbanners();	
}

if($params['action']=='omnistorecreation')
{
	$result = $campaignadditionclassobj->omnistorecreation();	
}

if($params['action']=='todaysprocessedddata')
{
	$result = $campaignadditionclassobj->todaysprocessedddata();	
}

if($result)
{
	$resultstr= json_encode($result);
	print($resultstr);
}


?>
