<?php 
 
require_once('../config.php');
require_once('includes/profile_strength_class.php');

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

if(isset($_REQUEST['parentid']))
{
	$params 					=	$_REQUEST;	
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}
foreach($_REQUEST as $key=>$value)
{
	$params[$key] = $value;
}	
$profile_strength_class_obj  	=	new profile_strength_class($params);
$profile_strength_info_arr 		=	$profile_strength_class_obj->profile_strength();
$profile_strength_info_str 		=	json_encode($profile_strength_info_arr);
print_r($profile_strength_info_str); 
?>



