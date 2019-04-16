<?php
require_once('../config.php');
require_once('includes/contractdetailsclass.php');
//http://prameshjha.jdsoftware.com/jdbox/services/contractdetails.php?parentid=PXX22.XX22.150727114451.M3C1&action=GetPlatDiamCategories&data_city=mumbai&module=tme&version=22&campaignid=2&trace=1

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
	echo '<pre><br><b>params:</b>'; print_r($params);	
}
					
$contractdetailsclassobj = new contractdetailsclass($params);

if($params['action']=='GetPlatDiamCategories')
{
$result = $contractdetailsclassobj->GetPlatDiamCategories();	
}

if($params['action']=='budgetvalidation')
{
$result = $contractdetailsclassobj->budgetvalidation();	
}

if($params['action']=='isflexipackagecontract')
{
$result = $contractdetailsclassobj->isflexipackagecontract();	
}

$resultstr= json_encode($result);

print($resultstr);

