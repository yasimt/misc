<?php

// imteyazraja.jdsoftware.com/jdbox/services/pdg_budget_update.php?data_city=Mumbai&action=tier2catpdg&post_data=1

require_once('../config.php');
require_once('includes/pdg_budget_update_class.php');


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

$pdg_budget_class_obj 		= new pdg_budget_update_class($params);

if($params['action'] == 'tier1pdgbdgt'){
	$teamwise_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier1PDGBudget();
	$teamwise_bdgt_pdg_str 	= json_encode($teamwise_bdgt_pdg_arr);
	print($teamwise_bdgt_pdg_str);
}
elseif($params['action'] == 'tier2teampdg'){
	$tier2_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier2TeamBdgtPdg();
	$tier2_bdgt_pdg_str 	= json_encode($tier2_bdgt_pdg_arr);
	print($tier2_bdgt_pdg_str);
}
elseif($params['action'] == 'tier2catpdg'){
	$tier2_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier2CatBdgtPdg();
	$tier2_bdgt_pdg_str 	= json_encode($tier2_bdgt_pdg_arr);
	print($tier2_bdgt_pdg_str);
}
elseif($params['action'] == 'tier2discpdg'){
	$tier2_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier2DiscountPdg();
	$tier2_bdgt_pdg_str 	= json_encode($tier2_bdgt_pdg_arr);
	print($tier2_bdgt_pdg_str);
}
elseif($params['action'] == 'tier3teampdg'){
	$tier3_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier3TeamBdgtPdg();
	$tier3_bdgt_pdg_str 	= json_encode($tier3_bdgt_pdg_arr);
	print($tier3_bdgt_pdg_str);
}
elseif($params['action'] == 'tier3catpdg'){
	$tier3_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier3CatBdgtPdg();
	$tier3_bdgt_pdg_str 	= json_encode($tier3_bdgt_pdg_arr);
	print($tier3_bdgt_pdg_str);
}
elseif($params['action'] == 'tier3discpdg'){
	$tier3_bdgt_pdg_arr 	= $pdg_budget_class_obj->getTier3DiscountPdg();
	$tier3_bdgt_pdg_str 	= json_encode($tier3_bdgt_pdg_arr);
	print($tier3_bdgt_pdg_str);
}
elseif($params['action'] == 'zoneteampdg'){
	$zone_bdgt_pdg_arr 	= $pdg_budget_class_obj->getZoneTeamBdgtPdg($params['zone_name']);
	$zone_bdgt_pdg_str 	= json_encode($zone_bdgt_pdg_arr);
	print($zone_bdgt_pdg_str);
}
elseif($params['action'] == 'zonecatpdg'){
	$zone_bdgt_pdg_arr 	= $pdg_budget_class_obj->getZoneCatBdgtPdg($params['zone_name']);
	$zone_bdgt_pdg_str 	= json_encode($zone_bdgt_pdg_arr);
	print($zone_bdgt_pdg_str);
}
elseif($params['action'] == 'zonediscpdg'){
	$zone_bdgt_pdg_arr 	= $pdg_budget_class_obj->getZoneDiscountPdg($params['zone_name']);
	$zone_bdgt_pdg_str 	= json_encode($zone_bdgt_pdg_arr);
	print($zone_bdgt_pdg_str);
}
elseif($params['action'] == 'stateteampdg'){
	$state_bdgt_pdg_arr = $pdg_budget_class_obj->getStateTeamBdgtPdg($params['state_name']);
	$state_bdgt_pdg_str = json_encode($state_bdgt_pdg_arr);
	print($state_bdgt_pdg_str);
}
elseif($params['action'] == 'statecatpdg'){
	$state_bdgt_pdg_arr = $pdg_budget_class_obj->getStateCatBdgtPdg($params['state_name']);
	$state_bdgt_pdg_str = json_encode($state_bdgt_pdg_arr);
	print($state_bdgt_pdg_str);
}
elseif($params['action'] == 'statediscpdg'){
	$state_bdgt_pdg_arr = $pdg_budget_class_obj->getStateDiscountPdg($params['state_name']);
	$state_bdgt_pdg_str = json_encode($state_bdgt_pdg_arr);
	print($state_bdgt_pdg_str);
}
elseif($params['action'] == 'remotepdg'){
	$remote_bdgt_pdg_arr = $pdg_budget_class_obj->getRemoteBudgetPdg();
	$remote_bdgt_pdg_str = json_encode($remote_bdgt_pdg_arr);
	print($remote_bdgt_pdg_str);
}
else if($params['action'] == 'updttier1pdg'){
	$update_tier1_pdg_arr 	= $pdg_budget_class_obj->updateTier1PDG($params);
	$update_tier1_pdg_str 	= json_encode($update_tier1_pdg_arr);
	print($update_tier1_pdg_str);
}
else if($params['action'] == 'updttier2pdg'){
	$update_tier2_pdg_arr 	= $pdg_budget_class_obj->updateTier2PDG($params);
	$update_tier2_pdg_str 	= json_encode($update_tier2_pdg_arr);
	print($update_tier2_pdg_str);
}
else if($params['action'] == 'updttier3pdg'){
	$update_tier3_pdg_arr 	= $pdg_budget_class_obj->updateTier3PDG($params);
	$update_tier3_pdg_str 	= json_encode($update_tier3_pdg_arr);
	print($update_tier3_pdg_str);
}
else if($params['action'] == 'updtzonepdg'){
	$update_zone_pdg_arr 	= $pdg_budget_class_obj->updateZonePDG($params);
	$update_zone_pdg_str 	= json_encode($update_zone_pdg_arr);
	print($update_zone_pdg_str);
}
else if($params['action'] == 'updtstatepdg'){
	$update_state_pdg_arr 	= $pdg_budget_class_obj->updateStatePDG($params);
	$update_state_pdg_str 	= json_encode($update_state_pdg_arr);
	print($update_state_pdg_str);
}
else if($params['action'] == 'updtremotepdg'){
	$update_remote_pdg_arr 	= $pdg_budget_class_obj->updateRemoteBudgetPdg($params);
	$update_remote_pdg_str 	= json_encode($update_remote_pdg_arr);
	print($update_remote_pdg_str);
}
else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



