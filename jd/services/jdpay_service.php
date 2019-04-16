<?php

require_once('../config.php');
require_once('includes/jdpay_service_class.php');

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

if($_REQUEST){	
	$params = $_REQUEST;
}else{
	$params = $params;
}

$jdpay_class_obj 	= new jdpay_service_class($params);
		

if($params['action']=='getJdPayDetails'){
	$result_arr    = $jdpay_class_obj->getJdPayAccountDetails();
}
elseif($params['action']=='accountDetailsToLive'){
	$result_arr    = $jdpay_class_obj->accountDetailsToLive($params[data_city]);
}
else{
	$result_arr    = $jdpay_class_obj->sendsmsandemail();
}

echo $result_str   = json_encode($result_arr);

//~ print($result_str);
?>

