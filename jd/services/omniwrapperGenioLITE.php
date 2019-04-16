<?php
/*
 * omniwrapperGenioLITE.php
 * 
 * Copyright 2018 Raj Yadav <rajyadav@localhost.localdomain>
 * JDBOX_API."/services/getOmniDetails.php?data_city=".urlencode($params['data_city'])."&module=me&action=2&usercode=".$params['user_code']."&parentid=".$params['parentid']."&version=".$params['version']."&website1=".urlencode($params['website1'])."&website2=".urlencode($params['website2'])."&website3=".urlencode($params['website3'])."&payment_type=".urlencode($params['payment_type'])."&own_website=".$params['own_website']."&combo=".$params['combo'].'&domain_registername='.$params['domain_registername'].'&domain_userid='.$params['domain_userid'].'&domain_pass='.$params['domain_pass'];
 
 */
//echo '<br> check this one :: ';
//ini_set('display_errors', '1');
require_once('../config.php');
require_once('includes/omniDetailsClass.php');
require_once('includes/omniwrapperGenioLITEClass.php');
require_once('includes/omniBudgetClass.php');
require_once('includes/financeDisplayClass.php');
require_once('includes/nationallistingclass.php');
require_once('includes/domainClass.php');
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
$omniBudgetClassobj = new omniBudgetClass($params);
$finance_display_obj = new financeDisplayClass($params);
$domainClassobj = new domainClass($params);

$omniwrapperGenioLITEObj = new omniwrapperGenioLITEClass($params, $omniDetailsClassobj, $omniBudgetClassobj,$finance_display_obj,$domainClassobj);


//print_r($omniwrapperGenioLITEObj);

$result = $omniwrapperGenioLITEObj->PopulateOmniEntry();

//echo 'hereeeeee';
echo json_encode($result);


?>
