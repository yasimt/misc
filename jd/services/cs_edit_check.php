<?php

//Sample URL : http://172.29.0.237:1010/services/cs_edit_check.php?parentid=PXX22.XX22.111209130950.P4F8&data_city=mumbai&module=TME
require_once('../config.php');
require_once('includes/cs_edit_check_class.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);


if($_REQUEST['post_data'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}

$cs_edit_check_class_obj 	= new cs_edit_check_class($params);
$cs_edit_chk_response_arr 	= $cs_edit_check_class_obj->getCSRedirectionUrl($params);
$cs_edit_chk_response_str 	= json_encode($cs_edit_chk_response_arr);

print($cs_edit_chk_response_str);

?>



