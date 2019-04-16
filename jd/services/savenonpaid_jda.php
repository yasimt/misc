<?php
require_once('../config.php');
require_once('includes/save_nonpaid_jda_class.php');

// http://imteyazraja.jdsoftware.com/jdbox/services/savenonpaid_jda.php?post_data=1&parentid=PXX22.XX22.170719124812.M5P4&data_city=Mumbai&module=JDA&usercode=10000760&username=Imteyaz
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

$save_non_paid_contract_obj  = new saveNonPaidContract($params);
$nonpaid_result_arr 	    = $save_non_paid_contract_obj->finalInsert();

$nonpaid_result_str	        = json_encode($nonpaid_result_arr);
print($nonpaid_result_str);

?>
