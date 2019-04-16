<?php

// imteyazraja.jdsoftware.com/jdbox/services/misc_budget_update.php?data_city=Mumbai&action=bannerbdgt&post_data=1

require_once('../config.php');
require_once('includes/misc_budget_update_class.php');


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

$misc_budget_class_obj 		= new misc_budget_update_class($params);

if($params['action'] == 'bannerbdgt'){
	$banner_bdgt_info_arr 	= $misc_budget_class_obj->getBannerBudgetInfo();
	$banner_bdgt_info_str 	= json_encode($banner_bdgt_info_arr);
	print($banner_bdgt_info_str);
}elseif($params['action'] == 'updtbnrbdgt'){
	$update_bnrbdgt_info_arr 	= $misc_budget_class_obj->updateBannerBudget($params);
	$update_bnrbdgt_info_str 	= json_encode($update_bnrbdgt_info_arr);
	print($update_bnrbdgt_info_str);
}elseif($params['action'] == 'jdrrbdgt'){
	$jdrr_bdgt_info_arr 	= $misc_budget_class_obj->getJdrrBudgetInfo();
	$jdrr_bdgt_info_str 	= json_encode($jdrr_bdgt_info_arr);
	print($jdrr_bdgt_info_str);
}elseif($params['action'] == 'updtjdrrbdgt'){
	$update_jdrrbdgt_info_arr 	= $misc_budget_class_obj->updateJdrrBudget($params);
	$update_jdrrbdgt_info_str 	= json_encode($update_jdrrbdgt_info_arr);
	print($update_jdrrbdgt_info_str);
}elseif($params['action'] == 'nationalbdgt'){
	$national_bdgt_info_arr = $misc_budget_class_obj->getNationalListingBudget($params);
	$national_bdgt_info_str = json_encode($national_bdgt_info_arr);
	print($national_bdgt_info_str);
}elseif($params['action'] == 'updtnatbdgt'){
	$update_natbdgt_info_arr 	= $misc_budget_class_obj->updateNationalBudget($params);
	$update_natbdgt_info_str 	= json_encode($update_natbdgt_info_arr);
	print($update_natbdgt_info_str);
}else if($params['action'] == 'posavail'){
	$bdgt_position_info_arr = $misc_budget_class_obj->getPositionAvailInfo();
	$bdgt_position_info_str = json_encode($bdgt_position_info_arr);
	print($bdgt_position_info_str);
}else if($params['action'] == 'posupdate'){
	$update_position_info_arr = $misc_budget_class_obj->updatePositionAvail($params);
	$update_position_info_str = json_encode($update_position_info_arr);
	print($update_position_info_str);
}
else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



