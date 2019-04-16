<?php

//Sample URL : http://172.29.0.237:1010/services/category_restriction_check.php?parentid=PXX22.XX22.150720190836.T3Z2&data_city=Mumbai&module=TME&all_catidlist=314594,310544,310545&remove_catidlist=
require_once('../config.php');
require_once('../sha256.inc.php');
require_once('includes/category_restriction_class.php');


if($_REQUEST['post_data'])
{
	$params = array();
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

$category_rest_class_obj  	= new category_restriction_class($params);
$category_rest_info_arr 	= $category_rest_class_obj->getCategoryRestrictedInfo();
$category_rest_info_str 	= json_encode($category_rest_info_arr);

print($category_rest_info_str);

?>



