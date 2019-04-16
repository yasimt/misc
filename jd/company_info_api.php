<?php

require_once('company_details_class.php');

$params_arr = array();
if(count($_REQUEST)>0){
	foreach($_REQUEST as $key=>$value)
	{
		$params_arr[$key] = $value;
	}	
}
else{	
	header('Content-Type: application/json');
	$params_arr	= json_decode(file_get_contents('php://input'),true);

}

if(count($params_arr) >0){
	$company_class_obj	= new companyClass();
	$comp_api_res_data	=	$company_class_obj->getCompanyInfo($params_arr);
	
	echo $comp_api_res_data;
}


?>


