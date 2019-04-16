<?php
session_start();
require_once('config.php');
require_once('insertTempClass.php');

//echo "<pre>"; 
//print_r($_REQUEST);
//echo "<pre>";

//die();

if (is_array($_REQUEST) && count($_REQUEST) && isset($_REQUEST['process']) && $_REQUEST['process'] == 'tempTablePopulation')
{	
	$messageArr= array();
	
	if(isset($_REQUEST['parentid']) && isset($_REQUEST['data_city']) && isset($_REQUEST['module']))
	{
		$obj  		= new insertToTemp($_REQUEST);
		$ucode		=  $_REQUEST['ucode'];	
		$obj->InsertIntoTempTables();
	
		$messageArr['status']='pass';
		$messageArr['statuscode'] ='200';
		$messageArr['message']='ok';
	}
	else
	{
		
		$messageArr['status']='fail';
		$messageArr['statuscode'] ='400';
		$messageArr['message']='Parameter Missing';
	}
	
	echo json_encode($messageArr);
}
