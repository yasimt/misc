<?php

// imteyazraja.jdsoftware.com/jdbox/services/empaccess_info.php?empcode=10000760&post_data=1

require_once('../config.php');
require_once('includes/empaccess_class.php');


if($_REQUEST['post_data'])
{
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

$empaccess_class_obj 	= new empaccess_class();
if($params['action'] == 'authchk'){
	$empaccess_response_arr = $empaccess_class_obj->authorisedUsersCheck($params);
	$empaccess_response_str = json_encode($empaccess_response_arr);
	print($empaccess_response_str);
}elseif($params['action'] == 'accessdetails'){
	$empaccess_response_arr = $empaccess_class_obj->getEmpAccessInfo($params);
	$empaccess_response_str = json_encode($empaccess_response_arr);
	print($empaccess_response_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



