<?php
//ini_set('display_errors', '1');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Access-Control-Allow-Methods: *');
	
set_time_limit(0);
ini_set("memory_limit", "-1");
require_once('../config.php');
require_once('includes/fixed_position_national_class.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

//echo"\n <br>params". json_encode($params);
//die('inside jdbox');

$fixed_position_national = new fixed_position_national_class($params);

if($params['action']=='updateinventory')
{
	$result = $fixed_position_national -> updateinventory();	
}


$resultstr= json_encode($result);

print($resultstr);

?>
