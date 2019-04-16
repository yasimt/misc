<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/doc_vertical_check.php?parentid=PXX22.XX22.140911105438.T6Y2&data_city=Mumbai&module=TME
require_once('../config.php');
require_once('../functions.php');
require_once('includes/doc_vertical_class.php');


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



$doc_vertical_class_obj 	= new docVerticalClass($params);
$doc_vertical_info_arr 	= $doc_vertical_class_obj->getDocVerticalInfo();
$doc_vertical_info_res 	= json_encode($doc_vertical_info_arr);
print($doc_vertical_info_res);

?>



