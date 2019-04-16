<?php
require_once('../config.php');
require_once('includes/save_nonapid_category_class.php');

header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}

}

$save_nonpaid_category_Obj  = new save_nonapid_category_class($params);
$nonpaid_result_arr 	    = $save_nonpaid_category_Obj->save_category_temp_data();
$nonpaid_result_str	        = json_encode($nonpaid_result_arr);

print($nonpaid_result_str);

?>
