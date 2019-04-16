<?
//error_reporting(E_ALL); ini_set('display_errors', 1); 
include_once('category_api_class.php');	


$categoryClass_obj = new categoryClass();

$cat_params = array();
$cat_params['data_city'] 	= $_REQUEST['data_city'];
$cat_params['module'] 		= $_REQUEST['module'];
$cat_params['return']		= $_REQUEST['return'];
$cat_params['limit']		= $_REQUEST['limit'];
$cat_params['orderby']		= $_REQUEST['orderby'];


$where_arr  	=	array();
$where_arr['catid']			= "305,4417";
$where_arr['mask_status']	= "0";
$where_arr['bfc_bifurcation_flag']	= "!4,5,6,7";
$where_arr['category_type']	= "!64";	
$cat_params['where']	= json_encode($where_arr);

//~ if($_REQUEST['where']==''){
	//~ $cat_params['where']		= json_encode($where_arr);
//~ }
//~ else{
	$cat_params['where']		= $_REQUEST['where'];	
//}

//$cat_params['trace'] =1;
//echo json_encode(value)
//echo "<pre>";print_r($where_arr);
//echo "<pre>";print_r($cat_params);
	$cat_api_res		= array();	
	echo $cat_api_res_str	=	$categoryClass_obj->getCatRelatedInfo($cat_params);
	$cat_res_arr = json_decode($cat_api_res_str,TRUE);
	
?>
