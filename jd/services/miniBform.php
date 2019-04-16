<?php

require_once('../config.php');
require_once('../library/configclass.php');

require_once('includes/miniBformClass.php');

if($_REQUEST["trace"] ==1){
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	echo "<pre>";
}else{
	define("DEBUG_MODE",0);	
	header('Content-Type: application/json');
}
if($_REQUEST['post_data']){
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
}else{
	$params	= json_decode(file_get_contents('php://input'),true);
}
//~ echo '===in min===<pre>';print_r($params); 
$miniBformClass_obj  	= new miniBformClass($params);	
if($params['action'] == 'miniBformload'){
	$miniBformClass_arr = $miniBformClass_obj->miniBformload();
	echo json_encode($miniBformClass_arr);
}else if($params['action']	==	'insertshadowdetails'){
	$miniBformClass_arr = $miniBformClass_obj->insertshadowdetails();
	echo json_encode($miniBformClass_arr);
}else if($params['action']	==	'fetchAllocEmpDetails'){
	$miniBformClass_arr = $miniBformClass_obj->fetchAllocEmpDetails();
	echo json_encode($miniBformClass_arr);
}else if($params['action']	==	'insertTMELOG'){
	$miniBformClass_arr = $miniBformClass_obj->insertTMELOG();
	echo json_encode($miniBformClass_arr);
}else{
    $die_msg_arr['error']['code'] = 1;
	$die_msg_arr['error']['msg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}
?>
