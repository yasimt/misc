<?php

//Sample URL : http://172.29.0.217:811/services/autotag_vertical_list.php?action=vlist&post_data=1
require_once('../config.php');
require_once('../functions.php');
require_once('includes/autotag_vertical_list_class.php');


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

$vertical_list_obj 	= new autotag_vertical_list_class($params);
if($params['action'] == 'vlist'){
	$vertical_list_arr	= $vertical_list_obj->getVerticalList();
	$vertical_list_str 	= json_encode($vertical_list_arr);
	print($vertical_list_str);
}
if($params['action'] == 'vrules'){
	$vertical_list_arr	= $vertical_list_obj->getVerticalRules($params['vabbr']);
	$vertical_list_str 	= json_encode($vertical_list_arr);
	print($vertical_list_str);
}
if($params['action'] == 'updtdrrules'){
	$dr_update_resp_arr	= $vertical_list_obj->updateDrRules($params);
	$dr_update_resp_str 	= json_encode($dr_update_resp_arr);
	print($dr_update_resp_str);
}

?>



