<?php

require_once('../config.php');
require_once('includes/compaignPromoclass.php');
//http://prameshjha.jdsoftware.com/jdbox/services/compaignPromo.php?data_city=mumbai&action=getemployeedata&trace=0

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);	
	header('Content-Type: application/json');
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

$params['data_city']=strtolower($params['data_city']);

//~ echo json_encode($params);
//~ die;

if(DEBUG_MODE)
{
	echo '<pre><br><b>params:</b>'; print_r($params);	
}
					
$compcatobj = new compaignPromoclass($params);

if($params['action']=='getemployeecontractdata')
{
	$result = $compcatobj->getemployeecontractdata();
	
}elseif($params['action']=='sendmessage')
{
	$result = $compcatobj->sendmessage();
	
}elseif($params['action']=='getsentmsgdetails')
{
	$result = $compcatobj->getsentmsgdetails();
	
}else if($params['action']=='autoSuggest'){
	
	$result = $compcatobj->autoSuggestDetails();
}


$resultstr= json_encode($result);

print($resultstr);

