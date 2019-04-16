<?php

//Sample URL : http://172.29.0.237:1010/services/multiparentage_check.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME&removed_catid=&catid_list=18844,337420,305,67771,295590,310545,314594,302&rquest=check_multiparentange&ucode=10000760&uname=ImteyazRaja&company_name=OurIndia

//Moderation Sample Url : 
//http:  172.29.0.237:1010/services/multiparentage_check.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME&removed_catid=&rquest=insertIntoCCRMultiParent
//&ucode=10000760&uname=ImteyazRaja&company_name=OurIndia&catid_selected=18844,337420&cat_for_moderation=67771,295590

require_once('../config.php');
require_once('includes/multiparentage_class.php');

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
  
$multiparentage_class_obj  	= new multiparentage_class($params);
$multiparentage_info_arr 	= $multiparentage_class_obj->multiparentage();
$multiparentage_info_str 	= json_encode($multiparentage_info_arr);

print($multiparentage_info_str);

?>



