<?php
//get_attribute_subgroup.php 
//params required are - parentid, data_city.(module is not required as we re always referring to db_iro)

require_once('../config.php');
require_once('includes/get_attribute_subgroup_class.php');

if(count($_POST)>0){
	$params = array();
	foreach($_POST as $key=>$value){
		$params[$key] = $value;
	}
}else{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$subgroup_obj 				 = new get_attribute_subgroup_class($params);

$attribute_subgroup_info_arr = $subgroup_obj->returnSubgroup();
 
$attribute_subgroup_info_str = json_encode($attribute_subgroup_info_arr);

echo $attribute_info_res_str;
?>
