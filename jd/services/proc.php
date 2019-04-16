<?php

require_once('../config.php');
require_once('includes/procclass.php');
require_once('../library/configclass.php');
require_once('../library/class.Curl.php');

//http://prameshjha.jdsoftware.com/jdbox/services/proc.php?action=bidddetexptobidddet&data_city=mumbai&parentid=P1000465&version=23

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
	if(! in_array($_REQUEST["action"], array('finview','omniview','bdgtview','genview')))
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


$procclassobj = new procclass($params);
$result= null;

if($params['action']=='bidddetexptobidddet')
{
	$result = $procclassobj->bidDetExpToBidDet();
	
}elseif($params['action']=='biddetarcvtobiddet')
{
	$result = $procclassobj->bidDetArcvToBidDet();
	
}elseif($params['action']=='tbl_bidding_details_expired_packagecreationfrompdg')
{
	$result = $procclassobj->tbl_bidding_details_expired_packagecreationfrompdg();
	
}elseif($params['action']=='tbl_bidding_details_packagecreationfrompdg')
{
	$result = $procclassobj->tbl_bidding_details_packagecreationfrompdg();
	
}elseif($params['action']=='tbdetotblbded')
{
	$result = $procclassobj->tbdetotblbded();
	
}elseif($params['action']=='updtidcbantolcl')
{
	$result = $procclassobj->updtidcbantolcl();
	
}elseif($params['action']=='dlcl')
{
	$result = $procclassobj->redealclose();
	
}elseif($params['action']=='upcatlingfrmbid')
{
	$result = $procclassobj->upcatlingfrmbid();
	
}elseif($params['action']=='finview')
{
	$result = $procclassobj->finview();
	
}elseif($params['action']=='omniview')
{
	 $procclassobj->omniview();
	 
}elseif($params['action']=='bdgtview')
{
	 $procclassobj->budgetview();
	 
}elseif($params['action']=='genview')
{
	 $procclassobj->genview();
	 
}elseif($params['action']=='getcatdet')
{
	 $procclassobj->getcatdet();
	 
}else{
	
	$result['error']['code'] = 1;	
	$result['error']['msg'] = "invalid action";
}



//print_r($result);
if($result)
{
	$resultstr= json_encode($result);
	print($resultstr);
}


?>
