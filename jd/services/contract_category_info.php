<?php

//Sample : http://172.29.0.237:1010/services/contract_category_info.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME
require_once('../config.php');
require_once('includes/contract_category_class.php');


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
$contract_cat_class_obj = new contract_category_class($params);
$category_details_arr 	= $contract_cat_class_obj->contractCategoryInfo();
$category_details_str 	= json_encode($category_details_arr);

print($category_details_str);

?>



