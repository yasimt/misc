<?php

//Sample URL : http://172.29.0.217:1010/services/loginDetails.php?empcode=10000760&data_city=delhi&action=ssoinfo&post_data=1
 //ini_set('display_errors', '1');
require_once('../config.php');
require_once('../tme_constants.php');
require_once('includes/loginClass.php');




if($_REQUEST['post_data'])
{
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$login_class_obj  	= new loginClass($params);

if($params['action'] == 'ssoinfo'){
	$login_details_arr 	= $login_class_obj->getSSOInfo();
	$login_details_str 	= json_encode($login_details_arr);
	print($login_details_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



