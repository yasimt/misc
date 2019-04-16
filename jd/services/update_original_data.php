<?php

//Sample URL : http://192.168.22.103:1010/services/update_original_data.php?data_city=Mumbai
require_once('../config.php');
require_once('includes/original_data_class.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);


if($_REQUEST['post_data'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}

$original_data_class_obj 	= new original_data_class($params);
$process_data_response_str 	= $original_data_class_obj->processData();

print($process_data_response_str);

?>



