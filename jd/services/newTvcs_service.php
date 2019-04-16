<?php
require_once('../config.php');
require_once('includes/Tvcs_service_class.php');

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

if($_REQUEST){	
	$params = $_REQUEST;
}else{
	$params = $params;
}

$Tvc_class_obj 	= new Tvcs_service_class($params);
if(trim($params['action'])=='view_tv_ad'){	
	$result_arr   	 = $Tvc_class_obj->returnTvAdLink();
	echo $result_str   = json_encode($result_arr);
}else if(trim($params['action'])=='fetch_tv_links'){
	$result_arr   	 = $Tvc_class_obj->fetchAdlink();
	echo $result_str = json_encode($result_arr);
}else{
	$result_arr   	 = $Tvc_class_obj->sendTvcAdLink();
	echo $result_str   = json_encode($result_arr);
}



?>
