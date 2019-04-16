<?php

//Sample URL : http://172.29.0.237:1010/services/catpreview_submit.php?parentid=PXX22.XX22.150727114451.M3C1&data_city=Mumbai&module=TME&remove_catidlist=334983&movie_timing={"123":"10 AM","1234":"10 PM"}
require_once('../config.php');
require_once('includes/catpreview_submit_class.php');

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

$catsubmit_class_obj 	= new catpreview_submit_class($params);
$catsubmit_result_arr 	= $catsubmit_class_obj->saveCategoryTempData();
$catsubmit_result_str	= json_encode($catsubmit_result_arr);

print($catsubmit_result_str);

?>
