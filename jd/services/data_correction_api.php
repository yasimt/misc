<?php 
require_once('../config.php');
require_once('includes/data_correction_class.php');

header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
 
if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print_r($params);
}

if(isset($_REQUEST))
{	 
	$params			=	$_REQUEST;
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}

$data_correction_class_obj  	=	new data_correction_class($params);
$data_correction_info_arr 		=	$data_correction_class_obj->data_correction();
$data_correction_info_str 		=	json_encode($data_correction_info_arr);
$return_str 					= 	json_encode($data_correction_info_arr);
print_r($return_str);
?>



