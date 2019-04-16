<?php 

//http://shitalpatil.jdsoftware.com/jdbox/services/duplicate_check.php?parentid=PXX22.XX22.150529085534.I6N6&companyname=DEV+Computers&pincode=&phone=9920444106&area=&landmark=&street=&data_city=Mumbai&module=CS&rflag=0

require_once('../config.php');
require_once('includes/duplicate_check_class.php');
require_once('includes/sphinxapi.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');

if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print_r($params);
}

if(isset($_REQUEST['data_city']))
{
	$params['parentid'] 		=	$_REQUEST['parentid'];
	$params['companyname'] 		=	$_REQUEST['companyname'];
	$params['phone'] 			=	$_REQUEST['phone'];
	$params['pincode'] 			=	$_REQUEST['pincode'];
	$params['area'] 			=	$_REQUEST['area'];
	$params['landmark'] 		=	$_REQUEST['landmark'];
	$params['building'] 		=	$_REQUEST['building'];	
	$params['street'] 			=	$_REQUEST['street'];	
	$params['address']			=	$_REQUEST['address'];
	$params['data_city'] 		=	$_REQUEST['data_city'];
	$params['module'] 			=	$_REQUEST['module'];	
	$params['rflag'] 			=	$_REQUEST['rflag'];
	$params['n'] 				=	$_REQUEST['n'];	
	$params['minibform'] 		=	$_REQUEST['minibform'];	
	$params['trace'] 			=	$_REQUEST['trace'];	
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}  
$duplicate_class_obj  	= new duplicate_check_class	($params);
$duplicate_info_arr 	= $duplicate_class_obj->duplicate_check();
$duplicate_info_str 	= json_encode($duplicate_info_arr);
?>



