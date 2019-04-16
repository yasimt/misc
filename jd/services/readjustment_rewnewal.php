<?php 
//http://vinaydesai.jdsoftware.com/jdbox/services/readjustment_rewnewal.php?parentid=PXX22.XX22.171214132453.U4S5&version=23&data_city=mumbai&module=me&renewal_type=1

require_once('../config.php');
require_once('includes/readjustment_renewal_class.php');

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
	$params['parentid'] 		=	$_REQUEST['parentid'];	
	$params['module'] 			=	$_REQUEST['module'];	
	$params['version'] 			=	$_REQUEST['version'];
	$params['renewal_type']		=	$_REQUEST['renewal_type'];		
	$params['data_city'] 		=	$_REQUEST['data_city'];

}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}
$params['rquest'] = "readjustment"; 

$readjustment_live_class_obj  	=	new readjustment_live_class($params);
$readjustment_live_info_arr 	=	$readjustment_live_class_obj->readjustment_live();
$readjustment_live_info_str 		=	json_encode($readjustment_live_info_arr);
//print_r($readjustment_live_info_str);
?>



