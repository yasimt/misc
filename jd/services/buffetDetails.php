<?php
//buffetDetails.php

require_once('../config.php');
require_once('includes/buffetDetailsClass.php');

if($_REQUEST)
{
	$params = array();
	if($_REQUEST['json'] == 1){
		$data_array = json_decode($_REQUEST['data'],true);
		foreach($data_array as $key=>$value){
			$params[$key] = $value;
		}
		
	}else{	
		foreach($_REQUEST as $key=>$value){
			$params[$key] = $value;
		}
	}
	
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$buffetObj 	= new buffetDetailsClass($params);
if($params['action']=='check_buffet'){	
	$buffet_info_arr 	= $buffetObj->checkBuffetDetails();
}elseif($params['action']=='update_buffet'){
	$buffet_info_arr 	= $buffetObj->updateBuffetDetails();
}

$buffet_info_res_str 	= json_encode($buffet_info_arr);
echo $buffet_info_res_str;
?>
