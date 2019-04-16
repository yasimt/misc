<?php

/* This  file is for more than omni agreement for sending auto mails on dealclose. pls ignore the file name*/
ini_set('display_errors',1); 
ini_set('display_startup_errors',1);
error_reporting(-1) ;
error_reporting(0);
require_once('../config.php');
require_once('includes/jdappnotificationclass.php');



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


$jdappnotification = new jdappnotificationclass($params);
if($params['action']=='1')
	$result = $jdappnotification->appNotification();

$resultstr= json_encode($result);

//print($resultstr);

