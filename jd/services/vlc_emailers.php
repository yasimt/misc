<?php 

//http://shitalpatil.jdsoftware.com/jdbox/services/vlc_emailers.php?parentid=PXX22.XX22.150529085534.I6N6&data_city=Mumbai&module=CS&rflag=0
 
require_once('../config.php');
require_once('includes/vlc_emailers_class.php');
 
if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}
$duplicate_class_obj  	= new vlc_emailers_class($params);
$duplicate_info_arr 	= $duplicate_class_obj->checkImageProPic(); 
$duplicate_info_str 	= json_encode($duplicate_info_arr);
?>



