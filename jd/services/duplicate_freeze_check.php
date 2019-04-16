<?php

require_once('../config.php');
require_once('../historyLog.php');
require_once('includes/duplicate_freeze_check_class.php');

//http://vishalvinodrana.jdsoftware.com/jdbox/services/duplicate_freeze_check.php?src_parentid=&dist_parentid&data_city=&module=

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$duplicate_class_obj  	= new duplicate_freeze_check_class	($params);

if($params['action'] == 1)
	$duplicate_info_arr 	= $duplicate_class_obj->duplicate_freeze_check();


if($params['action'] == 2)
	$submite_arr 	= $duplicate_class_obj->duplicate_freeze_submit();

//print_r($submite_arr);

if($params['action'] == 1)
$resultstr= json_encode($duplicate_info_arr);
else if($params['action'] == 2)
$resultstr= json_encode($submite_arr);

print($resultstr);

?>
