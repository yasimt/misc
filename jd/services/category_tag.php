<?php

require_once('../config.php');
require_once('includes/category_tag_class.php');

//http://prameshjha.jdsoftware.com/jdbox/services/dealclosedashboard.php?action=campaignwisecount


if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	echo "<pre>";
}
else
{
	define("DEBUG_MODE",0);
	
	if(! in_array($_REQUEST["action"], array('test')))
	{
		header('Content-type: application/json');
	}
	
	
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
	
$obj = new category_tag_class($params);


if($params['action']=='getMergeCategory')
$result = $obj-> getMergeCategory();



/*if($params['action']=='5')
	$result = $domainClassobj->registerProcessOnetime();*/
if(is_array($result))
{
	$result= json_encode($result, JSON_FORCE_OBJECT);
}
else
{
	$result= $result;
}	
print($result);

