<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/campaign_budget_info.php?action=minbudget&data_city=Mumbai&parentid=PXX22.XX22.140911105438.T6Y2&key=a707142b6fc4e0b89b84316e659cbd556a74efc745634b4b77001893c9e221cb&post_data=1
require_once('../config.php');
require_once('../functions.php');
require_once('includes/campaign_budget_class.php');

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

$campaign_class_obj 	= new campaignDetailsClass($params);
if($params['action'] == 'minbudget'){
	$campaign_info_arr 	= $campaign_class_obj->getCampaignMinBudget($params);
	$campaign_info_str 	= json_encode($campaign_info_arr);
	print($campaign_info_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>
