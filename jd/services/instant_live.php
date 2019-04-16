<?php 
//http://shitalpatil.jdsoftware.com/jdbox/services/instant_live.php?parentid=PXX22.XX22.150529085534.I6N6&data_city=Mumbai&module=CS&ucode=013080
// instant live class
require_once('../config.php');
require_once('includes/instant_live_class.php');

header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
 
if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	print_r($params);
}

if(isset($_REQUEST['parentid']))
{
	$params['parentid'] 		=	$_REQUEST['parentid'];
	$params['data_city'] 		=	$_REQUEST['data_city'];
	$params['module'] 			=	$_REQUEST['module'];	
	$params['ucode'] 			=	$_REQUEST['ucode'];
	$params['cron'] 			=	$_REQUEST['cron'];
	
	if(isset($_REQUEST['instrumentid'])){
		$params['instrumentid'] =	$_REQUEST['instrumentid'];
	}
	if(isset($_REQUEST['version'])){
		$params['version'] =	$_REQUEST['version'];
	}
	if(isset($_REQUEST['movie_insta'])){
		$params['movie_insta'] =	$_REQUEST['movie_insta'];
	}
	if(isset($_REQUEST['skiponline'])){
		$params['skiponline'] =	$_REQUEST['skiponline'];
	}
	$params['trace'] 			=	$_REQUEST['trace'];	
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}
if(isset($params['cron']) && $params['cron'] == 1)
	$params['rquest'] = "SendToInstantUpdation"; 
else
	$params['rquest'] = "InstantUpdation"; 
	
$instant_live_class_obj  	=	new instant_live_class($params);
$instant_live_info_arr 		=	$instant_live_class_obj->instant_live();
$instant_live_info_str 		=	json_encode($instant_live_info_arr);
print_r($instant_live_info_str);
?>



