<?php

//Sample URL : http://172.29.0.237:1010/services/category_page.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME
require_once('../config.php');
require_once('includes/category_page_class.php');


if($_REQUEST['post_data'])
{
	header('Content-Type: application/json');
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

$category_page_class_obj  	= new category_page_class($params);
$category_page_details_arr 	= $category_page_class_obj->getContractCategoryDetails();
$category_page_details_str 	= json_encode($category_page_details_arr);

print($category_page_details_str);

?>



