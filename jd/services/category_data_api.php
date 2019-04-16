<?php
ini_set("memory_limit",-1);
//ini_set("ERROR",E_ALL);
ini_set("display_errors", 1);
ini_set('display_startup_errors', 1); 

set_time_limit(0);

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

//error_reporting(E_ALL );
require_once('../config.php');
require_once('includes/category_data_api_class.php');


//http://sunnyshende.jdsoftware.com/JDBOXNEW/services/budgetDetails.php?data_city=mumbai&tenure=12&parentid=PXX22.XX22.090811191331.T3M2&mode=1&option=1&ver=12

$_REQUEST['city'] = (strtolower(trim($_REQUEST['city'])) == 'vizag') ? 'Visakhapatnam' : $_REQUEST['city'];

$params	= $_REQUEST;
if(isset($_REQUEST['city']))
{
	$city 			= trim($_REQUEST['city']);
	$params['city']	= $_REQUEST['city'];
}
 $service_flag = 0;

if(isset($_REQUEST['city']) && $_REQUEST['city'] !="" && isset($_REQUEST['module']) && ISSET($_REQUEST['where']) && $_REQUEST['where'] != "" && empty($_REQUEST['scase']) )
{ // Category details generic api call for genio
	$return 			= addslashes(stripslashes($_REQUEST['return']));
	$orderby 			= addslashes(stripslashes($_REQUEST['orderby']));
	$module 			= addslashes(stripslashes($_REQUEST['module']));
	//$city 				= addslashes(stripslashes($_REQUEST['city']));
	$where 				= $_REQUEST['where'];
	$limit 				= isset($_REQUEST['limit'])?(int)addslashes(stripslashes($_REQUEST['limit'])):10000;
	$q_type 			= $_REQUEST['q_type'];
    $service_flag		= 1;
}
elseif(isset($_REQUEST['city']) && $_REQUEST['city'] !="" && isset($_REQUEST['module']) && isset($_REQUEST['scase']) && $_REQUEST['scase'] != "")
{  // Category details special case api call for genio
	$return 			= addslashes(stripslashes($_REQUEST['return']));
	$orderby 			= addslashes(stripslashes($_REQUEST['orderby']));
	$module 			= addslashes(stripslashes($_REQUEST['module']));
	$where 				= $_REQUEST['where'];
	$limit 				= isset($_REQUEST['limit'])?(int)addslashes(stripslashes($_REQUEST['limit'])):10000;
	$q_type 			= $_REQUEST['q_type'];
	$scase 				= $_REQUEST['scase'];
    $service_flag		= 2;
}
elseif (isset($_REQUEST['city']) && $_REQUEST['city'] !="" && isset($_REQUEST['scase']) && $_REQUEST['scase'] == 4 && isset($_REQUEST['catid']) && $_REQUEST['catid'] !="" ) 
{
	$city 				= $_REQUEST['city'];
	$catid 				= $_REQUEST['catid'];
    $service_flag		= 3;	
}

$categoryDetailsClassobj = new categoryDetailsClass($params);
//echo $service_flag;die;
switch($service_flag)
{
	case 1:	$result = $categoryDetailsClassobj->get_catgory_details($return, $city, $where, $orderby , $limit, $q_type);
			break;
	case 2:	$result = $categoryDetailsClassobj->get_catgory_details_scase($return, $city, $where, $orderby , $limit, $q_type, $scase);
			break;
	case 3:	$result = $categoryDetailsClassobj->get_similar_business_category($city, $catid);
			break;		
	default :
			$results_array['results'] = array();
			$results_array['errorcode'] = '1';
			$results_array['msg'] = 'Wrong Service parameter';

			$result = json_encode($results_array);							
}



//$resultstr= json_encode($result);

print($result);

