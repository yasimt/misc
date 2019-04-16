<?php

require_once 'utility.php';

$prod = ($_SERVER['SERVER_ADDR'] == '172.29.64.64') ? 0 : 1;

if($prod == 0) {

	define('SSO_API','http://192.168.22.103:810/SSO/uimage/knowledge/');
}
else {
	
	define("SSO_API","http://accounts.justdial.com/uimage/knowledge/");
}

if($_GET['action'] == "insert") {
	
	$emp_id 		= $_GET['employee_id'];
    $emp_name 		= $_GET['employee_name'];
	$start_time 	= $_GET['start_time'];
	$total_duration	= $_GET['total_duration'];
	$file_path		= $_GET['media_path'];
	$media_id		= $_GET['media_id'];
	$media_type		= $_GET['media_type'];
	$title			= $_GET['title'];

	 $url 		= SSO_API."logClickCount?employee_id=".$emp_id."&start_time=".urlencode($start_time)."&total_duration=".$total_duration."&media_path=".urlencode($file_path)."&media_id=".$media_id."&source=TME&employee_name=".urlencode($emp_name)."&media_type=".urlencode($media_type)."&title=".urlencode($title);
	$dataParam	= array("url" => $url,"method" => "get");
	$res 		= utility::curlCall($dataParam);
	 
	unset($url,$dataParam);

	if(isset ($_GET['callback']))
	{
		header("Content-Type: application/json");

		echo $_GET['callback']."(".json_encode($res).")";
		exit;
	}
}

if($_GET['action'] == "update") {
	
	$emp_id 			= $_GET['employee_id'];
	$emp_name 		= $_GET['employee_name'];
	$start_time 		= $_GET['start_time'];
	$end_time 			= $_GET['end_time'];
	$media_id 			= $_GET['media_id'];
	$flag 				= $_GET['flag'];
	$total_play_time 	= $_GET['total_play_time'];
	$total_play_duration 	= $_GET['total_play_duration'];

	$url 		= SSO_API."updateLogClickEndTime?employee_id=".$emp_id."&start_time=".urlencode($start_time)."&end_time=".urlencode($end_time)."&media_id=".$media_id."&flag=".$flag."&total_play_time=".$total_play_time."&total_play_duration=".$total_play_duration."&source=TME&employee_name=".urlencode($emp_name);
	$dataParam	= array("url" => $url,"method" => "get");
	$res 		= utility::curlCall($dataParam);
	 
	unset($url,$dataParam);

	if(isset ($_GET['callback']))
	{
		header("Content-Type: application/json");

		echo $_GET['callback']."(".json_encode($res).")";
		exit;
	}
}



?>
