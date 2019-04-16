<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/catAdditionalInfo.php?parentid=PXX22.XX22.161108113042.D6Q2&data_city=Mumbai&module=ME&ucode=10000760&action=ccrhistory&post_data=1
require_once('../config.php');
require_once('includes/category_addinfo_class.php');


if($_REQUEST['post_data'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$category_addinfo_class_obj  = new category_addinfo_class($params);
if($params['action'] == 'ccrhistory'){
	$category_addinfo_class_arr  = $category_addinfo_class_obj->getCCRHistoryInfo($params);
	$category_addinfo_class_str  = json_encode($category_addinfo_class_arr);
	print($category_addinfo_class_str);
}else{
	$die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}

?>



