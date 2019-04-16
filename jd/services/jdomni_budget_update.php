<?php

// imteyazraja.jdsoftware.com/jdbox/services/jdomni_budget_update.php?data_city=Mumbai&action=getomni1status&post_data=1

require_once('../config.php');
require_once('includes/jdomni_budget_update_class.php');


if($_REQUEST['post_data'])
{
	if(intval($_REQUEST['post_data']) == 2){
		$encode_str = $_REQUEST['encode_str'];
		$decode_str	= urldecode($encode_str);
		 parse_str($decode_str, $params);
	}
	else{
		foreach($_REQUEST as $key=>$value){
			$params[$key] = $value;
		}
	}
	
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);

}

$jdomni_budget_class_obj 		= new jdomni_budget_update_class($params);

if($params['action'] == 'getomni1status'){
	$getomni1_bdgt_status_arr 	= $jdomni_budget_class_obj->getJdomni1Status();
	$getomni1_bdgt_status_str 	= json_encode($getomni1_bdgt_status_arr);
	print($getomni1_bdgt_status_str);
}else if($params['action'] == 'setomni1status'){
	$setomni1_bdgt_status_arr 	= $jdomni_budget_class_obj->updateJdomni1Status($params);
	$setomni1_bdgt_status_str 	= json_encode($setomni1_bdgt_status_arr);
	print($setomni1_bdgt_status_str);
}else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



