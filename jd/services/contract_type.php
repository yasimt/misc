<?php

//Sample URL : http://shitalpatil.jdsoftware.com/jdbox/services/mobile_check.php?mobile=9970561087&data_city=Mumbai&module=CS&rquest=mobile_employee_check&company_name=OurIndia


require_once('../config.php');
require_once('includes/contract_type_class.php');

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$contract_type_class_obj  	= new contract_type_class($params);
$contract_type_info_arr 	= $contract_type_class_obj->fetch_contract();
$contract_type_info_str 	= json_encode($contract_type_info_arr);

print($contract_type_info_str);

?>



