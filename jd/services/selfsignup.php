<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/selfsignup.php?key=!@@!
set_time_limit(0);
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/selfsignup_class.php');
require_once('includes/class_send_sms_email.php');


if($_REQUEST['key'] && $_REQUEST['parentid'] && $_REQUEST['data_city'])
{
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}

if(count($params)>0){
	$selfsignup_class_obj  		= new selfSignUpClass($params,$db);
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
