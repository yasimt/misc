<?php
ini_set("memory_limit",-1);
set_time_limit(0);
require_once('../config.php');
require_once('includes/budgetDetailsClass.php');
require_once('includes/regfeeclass.php');
require_once('includes/nationallistingclass.php');
//http://sunnyshende.jdsoftware.com/JDBOXNEW/services/budgetDetails.php?data_city=mumbai&tenure=12&parentid=PXX22.XX22.090811191331.T3M2&mode=1&option=1&ver=12
if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['data_city']))
{
	$params['data_city']	= $_REQUEST['data_city'];
	$params['version']		= $_REQUEST['ver'];
	$params['parentid']		= $_REQUEST['parentid'];
	//$params['catid_list']	= $_REQUEST['cat_list'];
	//$params['pincode_list']	= $_REQUEST['pin_list'];
	$params['tenure']		= $_REQUEST['tenure'];
	$params['mode']		    = $_REQUEST['mode']; // 1-best positon 2-fixed position 3-package
	$params['option']	    = $_REQUEST['option']; // default 1, max 7
	$params['onlyExclusive']= $_REQUEST['onlyExclusive']; // default 1, max 7
	$params['module']	    = $_REQUEST['module']; // module - tme , me 
	$params['custompackage'] = $_REQUEST['cpf']; // custom package flag
	$params['packagebgt_yrly'] = $_REQUEST['pbgtyrly']; // custom package flag
	$params['pinbgt'] 	= $_REQUEST['pinbgt']; // 1 - custom pincode wise budget flag
	$params['pinview'] 	= $_REQUEST['pinview']; // 1-pincode view 0-category view
	$params['bypass'] 	= $_REQUEST['bypass']; // 1-pincode view 0-category view
	$params['glcpb'] 	= $_REQUEST['glcpb']; // get llive cat pincode budget
	
	$params['filterData'] = $_REQUEST['filterData']; // get llive cat pincode budget
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

if(empty($params['data_city']) || empty($params['version']) || empty($params['parentid']) || empty($params['tenure']) || empty($params['mode']) || empty($params['option']))
{
	$result['results'] = array();
	$result['error']['code'] = 1;	
	$result['error']['msg'] = "Incorrect I/P parameters passed";	
}
else
{
	$areadetailsclassobj = new budgetDetailsClass($params);
	$result = $areadetailsclassobj->getBudget();
	//echo "<pre>"; print_r($result);
}
$resultstr= json_encode($result);

print($resultstr);

