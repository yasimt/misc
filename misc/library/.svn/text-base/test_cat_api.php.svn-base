<?
include_once('category_api_class.php');	
$categoryClass_obj = new categoryClass();

/*$category_type = 128;

if(($category_type&64) == 64){
	$b2b = 1;
}
else{
	$b2b = 0;
}
if((category_type&64)=64,1,0) 
echo $b2b;
die;*/

$cat_params = array();
$cat_params['data_city'] 	= $_REQUEST['data_city'];
$cat_params['module'] 		= $_REQUEST['module'];
$cat_params['return']		= $_REQUEST['return'];
$cat_params['limit']		= $_REQUEST['limit'];
$cat_params['orderby']		= $_REQUEST['orderby'];


$where_arr  	=	array();
/*$where_arr['priority_flag']	= "0";
$where_arr['premium_flag']	= "0";*/
//$where_arr['parent_national_catid']			= "11054242";
		$where_arr['catid']			= "305,4417";
		$where_arr['mask_status']	= "0";
		$where_arr['bfc_bifurcation_flag']	= "!4,5,6,7";
		$where_arr['category_type']	= "!64";	
		$cat_params['where']	= json_encode($where_arr);

if($_REQUEST['where']==''){
	$cat_params['where']		= json_encode($where_arr);
}
else{
	$cat_params['where']		= $_REQUEST['where'];	
}

//$cat_params['trace'] =1;
if($_GET['trace']==1){
	echo "<pre>";print_r($cat_params);
}

	$cat_api_res		= array();
	echo $cat_api_res_str	=	$categoryClass_obj->getCatRelatedInfo($cat_params);
	$cat_res_arr = json_decode($cat_api_res_str,TRUE);
		

	/*if($cat_res_arr['errorcode']=='0' &&count($cat_res_arr['results'])>0)
	$this->categoryClass_obj = new categoryClass();
	if(((int)$category_type & 4096) == 4096*/


//http://ratand.jdsoftware.com/jdbox/services/categoryDetails.php?city=mumbai&module=TME&return=catid,national_catid,category_name&where={catid:305,385543, category_name : car hire,Dentists , priority_flag : 0,1 , premium_flag : 0 , miscellaneous_flag : !16 }&orderby=callcount desc&limit=2&trace=0


/*
http://ratand.jdsoftware.com/jdbox/services/categoryDetails.php?city=mumbai&module=TME&return=catid,national_catid,category_name&
where={catid:305,385543, category_name : car hire,Dentists , priority_flag : 0,1 , premium_flag : 0 , miscellaneous_flag : !16 }&orderby=callcount desc&limit=2&trace=0


http://pratikjain.jdsoftware.com/jdbox/test_cat_api.php?data_city=mumbai&module=TME*/
?>
