<?php

require_once('../config.php');
require_once('includes/send_ods_vendor_applink_class.php');



if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print"<pre>";print_r($params);
}

if($_REQUEST){	
	$params = $_REQUEST;
}else{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
	$params = $params;
}

$ods_class_obj	 	= new send_ods_vendor_applink_class($params);
if($params['action']=='sendsms'){
	$result_arr   	    = $ods_class_obj->sendsmsandemail();
}
echo  $result_arr;
	
?>
