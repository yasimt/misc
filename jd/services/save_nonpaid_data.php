<?php
require_once('../config.php');
require_once('includes/save_nonpaid_data_class.php'); 
 
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
//echo '<pre>';print_r($params);

$save_non_paid_data_obj  = new saveNonPaidData($params);
$nonpaid_result_arr 	    = $save_non_paid_data_obj->finalInsert();

$nonpaid_result_str	        = json_encode($nonpaid_result_arr);
print($nonpaid_result_str);

?>
