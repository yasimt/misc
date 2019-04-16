<?php
//Sample URL : http://172.29.0.217:811/services/attribute_page.php?parentid=PXX22.XX22.151012085942.M1S8&data_city=mumbai&module=cs&action=fetchattr
require_once('../config.php');
require_once('includes/attributes_temp_to_main_class.php'); //attributes_temp_to_main.php

if($_REQUEST){
	$params = array();
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$attribute_page_cls_obj 	= new attributes_temp_to_main_class($params);
if($params['action']=='temp_to_main'){
	$attribute_info_res_arr 	= $attribute_page_cls_obj->tempToMain();
}
$attribute_info_res_str 	= json_encode($attribute_info_res_arr);
echo $attribute_info_res_str;

?>
