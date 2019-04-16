<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/campaign_payment_info.php?action=minpayment&data_city=Mumbai&parentid=PXX22.XX22.140911105438.T6Y2&key=94dd0f215b39c6d475a13d8461f6934c39d35aaa30ba5d32e161b27c5ac9680e&post_data=1&json={"campdata":{"2":{"budget":1000,"duration":365,"discount":2},"18":{"budget":2000}},"contval":20000}
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/campaign_payment_class.php');

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

$campaign_class_obj 	= new campaignPaymentClass($params);
if($params['action'] == 'minpayment'){
	$campaign_info_arr 	= $campaign_class_obj->getCampaignMinPayment($params);
	$campaign_info_str 	= json_encode($campaign_info_arr);
	print($campaign_info_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>
