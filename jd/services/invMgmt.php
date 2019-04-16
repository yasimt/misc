<?php
ini_set("memory_limit",-1);
set_time_limit(0);
require_once('../config.php');
require_once('includes/invMgmtClass.php');
require_once('includes/sendMail.php');
//http://sunnyshende.jdsoftware.com/jdbox/services/invMgmt.php?parentid=P1&version=12&astatus=1&astate=1&trace=1
if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['parentid']) && isset($_REQUEST['version']) && isset($_REQUEST['astatus']) && isset($_REQUEST['astate']) && isset($_REQUEST['data_city']))
{
	$params['data_city']	= $_REQUEST['data_city'];
	$params['version']	= $_REQUEST['version'];
	$params['parentid']	= $_REQUEST['parentid'];
	$params['astatus']	= $_REQUEST['astatus'];  // 1-blocking 2-booking(LIVE) 3-checking
	$params['astate']	= $_REQUEST['astate'];  // 1-dealclose 2-balance readjustment 3-financial approval 4-expiry 5-release 6-part payment 7-ecs 10-category/pin deletion LIve 11-category/pin deletion Shadow ,17 dependednt 
	$params['i_data']	= $_REQUEST['i_data'];
	$params['i_reason']	= $_REQUEST['i_reason'];
	$params['i_updatedby']	= $_REQUEST['i_updatedby'];
	$params['bidperday']	= $_REQUEST['bidperday'];
	$params['catlist']	= $_REQUEST['catlist'];
	$params['module']	= $_REQUEST['module'];
	$params['primarycampaignid']	= $_REQUEST['primarycampaignid'];
	$params['dependentcampaignid']	= $_REQUEST['dependentcampaignid'];
	$params['ecs_flag']	 = (int)$_REQUEST['ecs_flag'];
	$params['instrument_type']	= trim($_REQUEST['instrument_type']);
	$params['source']			     = trim($_REQUEST['source']);
	$params['next_dealclose_version']= trim($_REQUEST['next_dealclose_version']);
	$params['fin_approval']= (int)$_REQUEST['fin_approval'];
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

if(empty($params['data_city']) || empty($params['version']) || empty($params['parentid']) || empty($params['astatus']) || empty($params['astate']))
{
	$result['results'] = array();
	$result['error']['code'] = 1;	
	$result['error']['msg'] = "Incorrect I/P parameters passed";	
}
else
{
	$invmgmtclassobj = new invMgmtClass($params);

	$result = $invmgmtclassobj->manageInventory();
}

//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

