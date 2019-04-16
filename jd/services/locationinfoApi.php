<?php

require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/locationinfoApiClass.php');
if($_REQUEST["trace"] ==1){
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}else{
	define("DEBUG_MODE",0);	
}
if($_REQUEST['post_data']){
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
}else{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}
$wrapperClassobj = new locationinfoApiClass($params);
if($params['action']=='EcsRequestStatusCheck'){
	$result = $wrapperClassobj->EcsRequestStatusCheck();
}else if($params['action']	==	'pincodemaster'){
	$result = $wrapperClassobj->pincodemaster();
}else if($params['action']	==	'pincodemasterdialer'){
	$result = $wrapperClassobj->pincode_master_dialer();
}else if($params['action']	==	'getarea'){
	$result = $wrapperClassobj->get_area();
}
if($result){
	$resultstr= json_encode($result);
	print($resultstr);	
}else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}
?>

