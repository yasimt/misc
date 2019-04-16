<?php

// imteyazraja.jdsoftware.com/jdbox/services/package_budget_update.php?data_city=Mumbai&action=tier2catpkg&post_data=1&

require_once('../config.php');
require_once('includes/package_budget_update_class.php');


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

$package_budget_class_obj 		= new package_budget_update_class($params);

if($params['action'] == 'tier1pkgbdgt'){
	$teamwise_bdgt_package_arr 	= $package_budget_class_obj->getTier1PackageBudget();
	$teamwise_bdgt_package_str 	= json_encode($teamwise_bdgt_package_arr);
	print($teamwise_bdgt_package_str);
}
elseif($params['action'] == 'tier2teampkg'){
	$tier2_bdgt_package_arr 	= $package_budget_class_obj->getTier2TeamBdgtPkg();
	$tier2_bdgt_package_str 	= json_encode($tier2_bdgt_package_arr);
	print($tier2_bdgt_package_str);
}
elseif($params['action'] == 'tier2catpkg'){
	$tier2_bdgt_package_arr 	= $package_budget_class_obj->getTier2CatBdgtPkg();
	$tier2_bdgt_package_str 	= json_encode($tier2_bdgt_package_arr);
	print($tier2_bdgt_package_str);
}
elseif($params['action'] == 'tier2exppkg'){
	$tier2_bdgt_package_arr 	= $package_budget_class_obj->getTier2ExpBdgtPkg();
	$tier2_bdgt_package_str 	= json_encode($tier2_bdgt_package_arr);
	print($tier2_bdgt_package_str);
}
elseif($params['action'] == 'tier2discpkg'){
	$tier2_bdgt_package_arr 	= $package_budget_class_obj->getTier2DiscountPkg();
	$tier2_bdgt_package_str 	= json_encode($tier2_bdgt_package_arr);
	print($tier2_bdgt_package_str);
}
elseif($params['action'] == 'tier2premadpkg'){
	$tier2_bdgt_package_arr 	= $package_budget_class_obj->getTier2PremAdBdgtPkg();
	$tier2_bdgt_package_str 	= json_encode($tier2_bdgt_package_arr);
	print($tier2_bdgt_package_str);
}
elseif($params['action'] == 'tier3teampkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getTier3TeamBdgtPkg();
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'tier3catpkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getTier3CatBdgtPkg();
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'tier3exppkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getTier3ExpBdgtPkg();
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'tier3discpkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getTier3DiscountPkg();
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'tier3premadpkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getTier3PremAdBdgtPkg();
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'zoneteampkg'){
	$zone_bdgt_package_arr 	= $package_budget_class_obj->getZoneTeamBdgtPkg($params['zone_name']);
	$zone_bdgt_package_str = json_encode($zone_bdgt_package_arr);
	print($zone_bdgt_package_str);
}
elseif($params['action'] == 't3accordpkg'){
	$tier3_bdgt_package_arr 	= $package_budget_class_obj->getT3AccordDataPkg($params);
	$tier3_bdgt_package_str 	= json_encode($tier3_bdgt_package_arr);
	print($tier3_bdgt_package_str);
}
elseif($params['action'] == 'zonecatpkg'){
	$zone_bdgt_package_arr 	= $package_budget_class_obj->getZoneCatBdgtPkg($params['zone_name']);
	$zone_bdgt_package_str = json_encode($zone_bdgt_package_arr);
	print($zone_bdgt_package_str);
}
elseif($params['action'] == 'zoneexppkg'){
	$zone_bdgt_package_arr 	= $package_budget_class_obj->getZoneExpBdgtPkg($params['zone_name']);
	$zone_bdgt_package_str = json_encode($zone_bdgt_package_arr);
	print($zone_bdgt_package_str);
}
elseif($params['action'] == 'zonediscpkg'){
	$zone_bdgt_package_arr 	= $package_budget_class_obj->getZoneDiscountPkg($params['zone_name']);
	$zone_bdgt_package_str = json_encode($zone_bdgt_package_arr);
	print($zone_bdgt_package_str);
}
elseif($params['action'] == 'zoneprempkg'){
	$zone_bdgt_package_arr 	= $package_budget_class_obj->getZonePremAdBdgtPkg($params['zone_name']);
	$zone_bdgt_package_str = json_encode($zone_bdgt_package_arr);
	print($zone_bdgt_package_str);
}
elseif($params['action'] == 'stateteampkg'){
	$state_bdgt_package_arr = $package_budget_class_obj->getStateTeamBdgtPkg($params['state_name']);
	$state_bdgt_package_str = json_encode($state_bdgt_package_arr);
	print($state_bdgt_package_str);
}
elseif($params['action'] == 'statecatpkg'){
	$state_bdgt_package_arr = $package_budget_class_obj->getStateCatBdgtPkg($params['state_name']);
	$state_bdgt_package_str = json_encode($state_bdgt_package_arr);
	print($state_bdgt_package_str);
}
elseif($params['action'] == 'stateexppkg'){
	$state_bdgt_package_arr = $package_budget_class_obj->getStateExpBdgtPkg($params['state_name']);
	$state_bdgt_package_str = json_encode($state_bdgt_package_arr);
	print($state_bdgt_package_str);
}
elseif($params['action'] == 'statediscpkg'){
	$state_bdgt_package_arr = $package_budget_class_obj->getStateDiscountPkg($params['state_name']);
	$state_bdgt_package_str = json_encode($state_bdgt_package_arr);
	print($state_bdgt_package_str);
}
elseif($params['action'] == 'stateprempkg'){
	$state_bdgt_package_arr = $package_budget_class_obj->getStatePremAdBdgtPkg($params['state_name']);
	$state_bdgt_package_str = json_encode($state_bdgt_package_arr);
	print($state_bdgt_package_str);
}
elseif($params['action'] == 'remotepkg'){
	$remote_bdgt_package_arr 	= $package_budget_class_obj->getRemoteBudgetPkg();
	$remote_bdgt_package_str = json_encode($remote_bdgt_package_arr);
	print($remote_bdgt_package_str);
}
else if($params['action'] == 'updttier1pkg'){
	$update_tier1_package_arr 	= $package_budget_class_obj->updateTier1Package($params);
	$update_tier1_package_str 	= json_encode($update_tier1_package_arr);
	print($update_tier1_package_str);
}
else if($params['action'] == 'updttier2pkg'){
	$update_tier2_package_arr 	= $package_budget_class_obj->updateTier2Package($params);
	$update_tier2_package_str 	= json_encode($update_tier2_package_arr);
	print($update_tier2_package_str);
}
else if($params['action'] == 'updttier3pkg'){
	$update_tier3_package_arr 	= $package_budget_class_obj->updateTier3Package($params);
	$update_tier3_package_str 	= json_encode($update_tier3_package_arr);
	print($update_tier3_package_str);
}
else if($params['action'] == 'updtzonepkg'){
	$update_zone_package_arr 	= $package_budget_class_obj->updateZonePackage($params);
	$update_zone_package_str 	= json_encode($update_zone_package_arr);
	print($update_zone_package_str);
}
else if($params['action'] == 'updtstatepkg'){
	$update_state_package_arr 	= $package_budget_class_obj->updateStatePackage($params);
	$update_state_package_str 	= json_encode($update_state_package_arr);
	print($update_state_package_str);
}
else if($params['action'] == 'updtremotepkg'){
	$update_remote_package_arr 	= $package_budget_class_obj->updateRemoteBudgetPkg($params);
	$update_remote_package_str 	= json_encode($update_remote_package_arr);
	print($update_remote_package_str);
}
else if($params['action'] == 'exportexcelpkg'){
	$export_excel_package_resp 	= $package_budget_class_obj->exportToExcelPkg($params);
}
else if($params['action'] == 'fetchtmepricing'){
	$fetch_tme_pricing 	= $package_budget_class_obj->fetchtmepricing();
	$fetch_tme_pricing 	=  json_encode($fetch_tme_pricing);
	print($fetch_tme_pricing);
}
else if($params['action'] == 'updatetmepricing'){
	$fetch_tme_pricing 	= $package_budget_class_obj->updatetmepricing($params);
	$fetch_tme_pricing 	=  json_encode($fetch_tme_pricing);
	print($fetch_tme_pricing);
}
else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}
//comment
?>
