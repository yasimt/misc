<?php

//Sample URL : http://vishalvinodrana.jdsoftware.com/jdbox/services/legal_cat_search.php?parentid=P1216215596K2J9B5&data_city=Mumbai&module=CS&catid=1215,32525&post_data=1
require_once('../config.php');
require_once('includes/class_legal_cat.php');
require_once('includes/class_send_sms_email.php');

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


$check_legal_cat  	= new check_legal_cat($params);

$smsObj	 			= new email_sms_send($db,$params['data_city']);

//echo '<pre>';print_r($check_legal_cat);
$category_page_details_arr 	= $check_legal_cat->Check_legal_category($smsObj);
$category_page_details_str 	= json_encode($category_page_details_arr);

print($category_page_details_str);

?>



