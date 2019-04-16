<?php

//prameshjha.jdsoftware.com/jdbox/services/getcontractapi.php?data_city=mumbai&parentid=PXX22.XX22.150919123119.V4U2&action=updatetemptable&module=tme&usercode=013084
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/mktgGetContDataClass.php');
require_once('includes/live_data_class.php');


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

$mktg_data_class_obj  	= new mktgGetContDataClass($params,$live_data_class_obj);


$live_data_details_arr 	= $mktg_data_class_obj->PopulateTempTables();

if($params['trace'] == 1){
	print"<pre>";print_r($live_data_details_arr);
	
}else{
	$live_data_details_str 	= json_encode($live_data_details_arr);
	print($live_data_details_str);
}
?>
