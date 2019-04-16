<?php

//Sample URL : http://172.29.0.237:1010/services/others_vertical_redirection_check.php?parentid=PXX22.XX22.111209130950.P4F8&data_city=mumbai&module=TME&vertical_name=doctor
require_once('../config.php');
require_once('../functions.php');
require_once('includes/others_vertical_redirection_class.php');


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


$others_vertical_redirect_cls_obj 	= new others_vertical_redirection_class($params);
$others_vertical_redirect_res_arr 	= $others_vertical_redirect_cls_obj->getOthersVerticalRedirectionUrl();
$others_vertical_redirect_res_str 	= json_encode($others_vertical_redirect_res_arr);

print($others_vertical_redirect_res_str);

?>



