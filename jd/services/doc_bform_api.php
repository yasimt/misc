<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/doc_bform_api.php?parentid=PXX22.XX22.170705191446.B9T2&data_city=Mumbai&module=ME&action=eligib&post_data=1
require_once('../config.php');
require_once('../functions.php');
require_once('includes/doc_bform_class.php');
require_once('../historyLog.php');

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

$doc_bform_class_obj 	= new docBformClass($params);
if($params['action'] == 'eligib'){
	$doc_bform_info_arr 	= $doc_bform_class_obj->checkDocEligibility($params);
	$doc_bform_info_res 	= json_encode($doc_bform_info_arr);
	print($doc_bform_info_res);
}else if($params['action'] == 'dbformsubmit'){
	$doc_bform_info_arr 	= $doc_bform_class_obj->submitDocBFormData($params);
	$doc_bform_info_res 	= json_encode($doc_bform_info_arr);
	print($doc_bform_info_res);
}

?>



