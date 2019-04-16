<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/selfsignup.php?key=!@@!
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/insSelfSignUpClass.php');

if($_REQUEST)
{
	header('Content-Type: application/json');
	$params = $_REQUEST;
	
}

if(count($params)>0){
	$selfsignup_class_obj  		= new insSelfSignUpClass($params,$db);
	$selfsignup_process_arr 	= $selfsignup_class_obj->processData();
	$selfsignup_process_str 	= json_encode($selfsignup_process_arr);
	print($selfsignup_process_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Access Denied";
	echo json_encode($die_msg_arr);
    die();
}

?>
