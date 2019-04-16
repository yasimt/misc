<?php

/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
require_once('../config.php');
require_once('../historyLog.php');
require_once('includes/saveCompanyDataClass.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST))
{
	$params=$_REQUEST;
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}

if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}

$saveCompanyDataObj = new saveCompanyDataClass($params);
if($params['action']=='1'){
	$result = $saveCompanyDataObj->saveCompanyDetails(); 
	createLog($result,$params);
}
else{
	$result['error']['code']=1;
	$result['error']['msg']='Invalid Request';
	createLog($result);
}

function createLog($result,$params=null){

	$resultstr= json_encode($result);
	print($resultstr);
}


