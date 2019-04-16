<?php
require_once('../config.php');
require_once('includes/movie_timing_class.php');

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

$movie_timing_obj 	= new movie_timing_class($params);

if($params['action']=='get_today_timings'){	
	$result_arr    = $movie_timing_obj->get_today_timings();
}

echo json_encode($result_arr);
?>
