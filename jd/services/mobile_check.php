<?php

//Sample URL : http://shitalpatil.jdsoftware.com/jdbox/services/mobile_check.php?mobile=9970561087&data_city=Mumbai&module=CS&rquest=mobile_employee_check&company_name=OurIndia


require_once('../config.php');
require_once('includes/mobile_check_class.php');

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$mobile_check_class_obj  	= new mobile_check_class($params);
$mobile_check_info_arr 	= $mobile_check_class_obj->fetch_mobile();


// Handling to prevent json_encode twice , if any errors thrown from inside function (not from constructor)

if(is_array($mobile_check_info_arr)){
	$mobile_check_info_str 	= json_encode($mobile_check_info_arr);
}else{
	$mobile_check_info_str = $mobile_check_info_arr;
}

print($mobile_check_info_str);

?>



