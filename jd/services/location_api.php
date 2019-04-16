<?php
// 
//Sample URL : 
//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=get_state&daat_city=mumbai&trace=1
//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=get_pincode&data_city=mumbai&trace=1&city=mumbai
//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=get_area&pincode=400053&data_city=mumbai&trace=1
//http://shitalpatil.jdsoftware.com/jdbox/services/location_api.php?rquest=get_building&pincode=400053&trace=1


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');

require_once('../config.php');
require_once('includes/location_class.php');


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

$location_class_obj  	= new location_class($params);
$location_info_arr 		= $location_class_obj->fetch_details();
$location_info_str 		= json_encode($location_info_arr);

print($location_info_str);

?>



