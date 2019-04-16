<?php
require_once('../config.php');
require_once('includes/jdrr_budget_class.php');

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

if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
print_r($params);
}
//echo json_encode($params); exit;
$jdrr_obj = new jdrr_budget_class($params);

if($params['action'] == 1)
{
	
	$result = $jdrr_obj->populate_JDRR_Budget();
}
if($params['action'] == -1)
{
	$result = $jdrr_obj->removeJDRRCampaign();
}

if($params['action'] == 2)
{
	$result = $jdrr_obj->PopulateMainTable();
}
if($params['action'] == 3)
{
	$result = $jdrr_obj->PopulateTempCampaign();
}
if($params['action'] == 4)
{
	$result = $jdrr_obj->delTempCampaign();
}

if($params['action'] == 5)
{
	$result = $jdrr_obj->getRatingDetails();
}

if($params['action'] == 7)
{
	$result = $jdrr_obj->getJdrrAutoSuggest();
}


if($params['action'] == 8)
{
	
	$result = $jdrr_obj->populate_JDRR_Budget_NEW();
}


if($params['action'] == 9)//populate jdrr for freebies - ask hemavathi
{
	
	$result = $jdrr_obj->populate_JDRR_Budget_FREE();
}

//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

