<?php

//Sample URL : http://172.29.0.237:1010/services/reset_categories.php?parentid=PXX22.XX22.120407204752.W2Q4&data_city=Mumbai&module=TME
require_once('../config.php');
require_once('includes/reset_categories_class.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

if($_REQUEST['print_flag'])
{
	print"<pre>";print_r($params);
}

$reset_cat_class_obj  	= new reset_categories_class($params);
$reset_cat_class_arr 	= $reset_cat_class_obj->reset_categories();
$reset_cat_class_str 	= json_encode($reset_cat_class_arr);

print($reset_cat_class_str);

?>



