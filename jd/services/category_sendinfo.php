<?php

require_once('../config.php');
require_once('includes/category_sendinfo_class.php');

if(count($_REQUEST) > 0)
{
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}

$obj 		= new category_sendinfo_class($params);
$data_arr 	= $obj->sendCatInfo();
$data_str 	= json_encode($data_arr);
print($data_str);


?>
