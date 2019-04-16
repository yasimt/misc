<?php

//http://imteyazraja.jdsoftware.com/jdbox/services/relatedCategory.php?str=Homeopathic%20Doctors&cid=1108&data_city=Mumbai
require_once('../config.php');
require_once('includes/relatedCategoryClass.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST['str']))
{
	$params['data_city']= $_REQUEST['data_city'];
	$params['cid']= $_REQUEST['cid'];
	$params['nid']= $_REQUEST['nid'];
	$params['str']= $_REQUEST['str'];
	$params['grp']= $_REQUEST['grp'];
	$params['off']= $_REQUEST['off'];
	$params['num']= $_REQUEST['num'];
	$params['odr']= $_REQUEST['odr'];
	$params['stp']= $_REQUEST['stp'];
	$params['ntp']= $_REQUEST['ntp'];
	$params['bfcignore']= $_REQUEST['bfcignore'];
	$params['mrkonly']	= $_REQUEST['mrkonly'];
}
else
{
	$params	= json_decode(file_get_contents('php://input'),true);
}


//$params['data_city']='mumbai';
//$params['str']='Restaurants'; // always padd main category - no synonymn
//$params['cid']=''; // catid
//$params['nid']=''; // national catid
//$params['grp']=''; 	//[0-default ALL][1-MRK|2-RK|3-PK][4-filter] 
//$params['off']='0'; 	//offset- starting of record default 0
//$params['num']='10'; 	//number of record - default 10
//$params['odr']='0'; 	//[0-default REL|1-ALPABETIC] 
if(DEBUG_MODE)
{
	echo '<pre>';
	print_r($params);
}


$catdetailsclassobj = new relatedCategoryClass($params);

$result = $catdetailsclassobj->getCategory();
$resultstr= json_encode($result);

print($resultstr);

?>
