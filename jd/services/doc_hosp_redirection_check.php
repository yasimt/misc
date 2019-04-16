<?php

//Sample URL : http://172.29.0.237:1010/services/doc_hosp_redirection_check.php?parentid=PXX22.XX22.111209130950.P4F8&data_city=mumbai&module=TME&vertical_name=doctor
require_once('../config.php');
require_once('../functions.php');
require_once('includes/doc_hosp_redirection_class.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);


if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print"<pre>";print_r($params);
}

$doc_hosp_redirection_cls_obj 	= new doc_hosp_redirection_class($params);
$doc_hosp_redirection_res_arr 	= $doc_hosp_redirection_cls_obj->getDocHospRedirectionFlag();
$doc_hosp_redirection_res_str 	= json_encode($doc_hosp_redirection_res_arr);

print($doc_hosp_redirection_res_str);

?>



