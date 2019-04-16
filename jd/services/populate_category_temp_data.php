<?php

//Sample URL : http://172.29.0.237:1010/services/populate_category_temp_data.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME&catlist=123|P|1234
require_once('../config.php');
require_once('includes/category_temp_data_class.php');


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

$categort_temp_class_obj  	= new category_temp_data_class($params);
$category_temp_details_arr 	= $categort_temp_class_obj->populate_category_temp_data();
$category_temp_details_str 	= json_encode($category_temp_details_arr);

print($category_temp_details_str);

?>



