<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/fetchLiveData.php?parentid=PXX22.XX22.170705191446.B9T2&data_city=Mumbai&module=ME&post_data=1&ucode=10000760&uname=Imteyaz&trace=1
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/live_data_class.php');


	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: origin, content-type, accept');
	header('Access-Control-Allow-Methods: *');

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

$live_data_class_obj  	= new live_data_class($params);
$live_data_details_arr 	= $live_data_class_obj->populateTempTables();
if($params['trace'] == 1){
	print"<pre>";print_r($live_data_details_arr);
	
}else{
	$live_data_details_str 	= json_encode($live_data_details_arr);
	print($live_data_details_str);
}

?>



