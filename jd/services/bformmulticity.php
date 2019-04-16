<?php

require_once('../config.php');
require_once('includes/bformmulticity_class.php');
require_once('includes/nationallistingclass.php');


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$bformmulticity_obj 	= new bformmulticity_class($params);

if($params['action']=='checkMultiCity'){
	$result_arr    = $bformmulticity_obj->checkMultiCity($params['catidlineage']);
}else if($params['action']=='initialBalance'){
	$result_arr    = $bformmulticity_obj->initialBalance();
}else if($params['action']=='getCountryZones'){
	$result_arr    = $bformmulticity_obj->getCountryZones();
}else if($params['action']=='getZoneListings'){
	$result_arr    = $bformmulticity_obj->getZoneListings();
}else if($params['action']=='getTopCities'){
	$result_arr    = $bformmulticity_obj->getTopCities();
}else if($params['action']=='getStateListings'){
	$result_arr    = $bformmulticity_obj->getStateListings();
}else if($params['action']=='getStateListings2'){
	$result_arr    = $bformmulticity_obj->getStateListings2();
}else if($params['action']=='insertNationalListingval'){
	$result_arr    = $bformmulticity_obj->insertNationalListingval($params['citystr'],$params['latitude'],$params['longitude'],$params['type']);
}else if($params['action']=='insertLocalListingval'){
	$result_arr    = $bformmulticity_obj->insertLocalListingval($params['sphinxid']);
}

echo $result_arr;
?>
