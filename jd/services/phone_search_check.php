<?php

//Sample URL : http://172.29.0.237:1010/services/cs_edit_check.php?parentid=PXX22.XX22.111209130950.P4F8&data_city=mumbai&module=TME
require_once('../config.php');
require_once('includes/phone_search_check_class.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);


if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print"<pre>";print_r($params);
}

$phone_search_class_obj 	= new phone_search_check_class($params);
$phone_search_response_arr 	= $phone_search_class_obj->getPhoneSearchCampaignInfo();
$phone_search_response_str 	= json_encode($phone_search_response_arr);

print($phone_search_response_str);

?>



