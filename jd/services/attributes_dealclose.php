<?php
/*this file is used to add/remove attributes by default based on selected categories. executed on dealclose in every module*/
//case 1 - remove attributes of categories present in this table online_regis1.tbl_removed_categories 
//case 2 - add attributes based on categories present in business_temp_data & catidlineage_nonpaid
require_once('../config.php');
require_once('includes/attributes_dealclose_class.php');

if($_REQUEST)
{
	$params = array();
	if($_REQUEST['json'] == 1){
		$data_array = json_decode($_REQUEST['data'],true);
		foreach($data_array as $key=>$value){
			$params[$key] = $value;
		}
		
	}else{	
		foreach($_REQUEST as $key=>$value){
			$params[$key] = $value;
		}
	}
	
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}
//~ echo "<pre>params:29--";print_r($params);
$attribute_obj 	= new attributes_dealclose_class($params);
if($params['action']=='add_remove_attr'){
	//$res_arr 	= $attribute_obj->add_remove_attributes();
	$res_arr 	= $attribute_obj->add_remove_attributes_new();
}
$str  = json_encode($res_arr);
echo $str;
?>
