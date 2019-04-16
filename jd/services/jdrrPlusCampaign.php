<?php

require_once('../config.php');
require_once('includes/jdrrPlusCampaignClass.php');
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

$jdrrPlusCampaignClassobj = new jdrrPlusCampaignClass($params);
if($params['action']=='1')
	$result = $jdrrPlusCampaignClassobj->checkJdrrPlusCampaignEligibility();

if($params['action']=='2')
	$result = $jdrrPlusCampaignClassobj->customJdrrPlus();
if($params['action']=='3')
	$result = $jdrrPlusCampaignClassobj->deleteJdrrPlusCustom();


$resultstr= json_encode($result);
print($resultstr);

