<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');

require_once('../config.php');
require_once('includes/geocode_accuracy_class.php');
require_once('../library/configclass.php');

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}
if($params['trace'] == 1)
	echo "<prE>"; 
 
$geocode_class_obj  	= new geocode_class($params);
$geocode_info_arr 		= $geocode_class_obj->fetch_details();
$geocode_info_str 		= json_encode($geocode_info_arr	);

print($geocode_info_str);

?>



