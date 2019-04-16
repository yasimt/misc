<?php
require_once('../config.php');
require_once('includes/getrestrictedcatclass.php');


$get_params = array_merge($_GET,$_POST);
if( $get_params["trace"] ==1 ){
	
	define("DEBUG_MODE",1);
}
else{
	
	define("DEBUG_MODE",0);
}



if($get_params){	
	$params = $get_params;
}else{
	
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$getrestrictedCatClass_obj = new getrestrictedCatClass($params);

$result = $getrestrictedCatClass_obj->getRestrictedCats();

$resultstr= json_encode($result);

print($resultstr);

