<?php
ini_set("memory_limit",-1);

require_once('../config.php');
require_once('includes/budgetDetailsHiddenclass.php');
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
	$params['action']		= $_REQUEST['action'];
	//$params['catid_list']	= $_REQUEST['cat_list'];
	//$params['pincode_list']	= $_REQUEST['pin_list'];
	$params['tenure']		= $_REQUEST['tenure'];
	$params['mode']		    = $_REQUEST['mode']; // 1-best positon 2-fixed position 3-package
	$params['option']	    = $_REQUEST['option']; // default 1, max 7
	$params['module']	    = $_REQUEST['module']; // module - tme , me 
	
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


if( (!$params['action']) && (empty($params['data_city']) || empty($params['version']) || empty($params['parentid']) || empty($params['tenure'])))
{
	$result['results'] = array();
	$result['error']['code'] = 1;	
	$result['error']['msg'] = "Incorrect I/P parameters passed";	
}
else
{
	
	$areadetailsclassobj = new budgetDetailshiddenClass($params);
	
	if($params['action'] == '1')
	{
	  $result = $areadetailsclassobj->HiddenInventoryChecking();
	}
	else
	{
	  $result = $areadetailsclassobj->getBudget();
	}
	
}

//echo "<pre>"; print_r($result);
//array('MUMBAI'=>'22'
//$result = array("result" => array("c_data" =>array("315068" => array("cid" => "315068","ncid" => "10332503","cnm" => "Multiplex Cinema Halls","pin_data" => array("400068" => array("inv_booked"=>"100%","budget"=>"300","bidvalue"=>"80"))))));
//echo "<pre>"; print_r($result);die;
$resultstr= json_encode($result);

print($resultstr);

