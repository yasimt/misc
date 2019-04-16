<?php

//Sample URL : http://172.29.0.217:811/services/attribute_page.php?parentid=PXX22.XX22.151012085942.M1S8&data_city=mumbai&module=cs&action=fetchattr
require_once('../config.php');
require_once('includes/attribute_page_class_new_2.php');

//~ header('Content-Type: application/json');
//~ $params	= json_decode(file_get_contents('php://input'),true);

if($_REQUEST)
{
	$params = array();
	if($_REQUEST['json'] == 1){
		$data_array = json_decode($_REQUEST['data'],true);
		foreach($data_array as $key=>$value){
			$params[$key] = $value;
		}
		
	}else{	
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

$attribute_page_cls_obj 	= new attribute_page_class($params);
if($params['action']=='check_attr'){
	$attribute_info_res_arr 	= $attribute_page_cls_obj->check_att_pre();
}
else if($params['action']=='fetchattr')
{
	$attribute_info_res_arr 	= $attribute_page_cls_obj->getAttributesInfo();
}
elseif($params['action']=='updateattr')
{
	$attribute_info_res_arr 	= $attribute_page_cls_obj->updateAttributesInfo();
}
$attribute_info_res_str 	= json_encode($attribute_info_res_arr);

echo $attribute_info_res_str;

?>



