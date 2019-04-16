<?php

// imteyazraja.jdsoftware.com/jdbox/services/budget_additional_info.php?action=fetchcity&srchcity=mum&post_data=1

require_once('../config.php');
require_once('includes/budget_additional_class.php');


if($_REQUEST['trace'] == 1)
{
	header('Content-Type: application/json');
}
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
$bdgt_addnl_class_obj 	= new budget_additional_class();
if($params['action'] == 'fetchcity'){
	$bdgt_addnl_response_arr = $bdgt_addnl_class_obj->getCities($params);
	$bdgt_addnl_response_str = json_encode($bdgt_addnl_response_arr);
	print($bdgt_addnl_response_str);
}elseif($params['action'] == 'bdgtlog'){
	$bdgt_log_response_arr = $bdgt_addnl_class_obj->getBudgetLog($params);
	$bdgt_log_response_str = json_encode($bdgt_log_response_arr);
	print($bdgt_log_response_str);
}elseif($params['action'] == 'bdgtzonewise'){
	$bdgt_zonewise_info_arr = $bdgt_addnl_class_obj->getBudgetZoneWise($params['main_zone']);
	$bdgt_zonewise_info_str = json_encode($bdgt_zonewise_info_arr);
	print($bdgt_zonewise_info_str);
}elseif($params['action'] == 'bdgtstatewise'){
	$bdgt_statewise_info_arr = $bdgt_addnl_class_obj->getBudgetStateWise($params['state_name']);
	$bdgt_statewise_info_str = json_encode($bdgt_statewise_info_arr);
	print($bdgt_statewise_info_str);
}else if($params['action'] == 'bdgttier1'){
	$bdgt_tier1_info_arr = $bdgt_addnl_class_obj->getTier1BudgetDetails();
	$bdgt_tier1_info_str = json_encode($bdgt_tier1_info_arr);
	print($bdgt_tier1_info_str);
}else if($params['action'] == 'bdgttier2'){
	$bdgt_tier2_info_arr = $bdgt_addnl_class_obj->getTier2BudgetDetails();
	$bdgt_tier2_info_str = json_encode($bdgt_tier2_info_arr);
	print($bdgt_tier2_info_str);
}else if($params['action'] == 'bdgttier3'){
	$bdgt_tier3_info_arr = $bdgt_addnl_class_obj->getTier3BudgetDetails($params);
	$bdgt_tier3_info_str = json_encode($bdgt_tier3_info_arr);
	print($bdgt_tier3_info_str);
}else if($params['action'] == 'exporttoexcel'){
	$export_excel_zonewise 	= $bdgt_addnl_class_obj->exportToExcel($params);
}elseif($params['action'] == 'statelist'){
	$state_list_info_arr = $bdgt_addnl_class_obj->getStateNames();
	$state_list_info_str = json_encode($state_list_info_arr);
	print($state_list_info_str);
}
else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



