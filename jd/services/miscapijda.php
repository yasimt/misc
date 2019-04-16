<?php

require_once('../config.php');
require_once('includes/miscapijdaclass.php');
require_once('includes/versioninitclass.php');

//prameshjha.jdsoftware.com/jdbox/services/miscapijda.php?data_city=mumbai&parentid=PXX22.XX22.150919123119.V4U2&action=updatefinancetemptable&module=me&usercode=013084


if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}

if($_REQUEST)
{
	$params=$_REQUEST;
	$params['data_city']= urldecode($params['data_city']);
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
$params['data_city']= urldecode($params['data_city']);

}

//echo"\n <br>params". json_encode($params);
//die('inside jdbox');

$miscapijdaclass_obj = new miscapijdaclass($params);

if($params['action']=='updatefinancetemptable')
{
	$campaignlist_condtn=null;
	
	if(trim($params['campaignlist'])!=null)
	{
		$campaignlist= $params['campaignlist'];
	}
	
	$result = $miscapijdaclass_obj->updatefinancetempTable($campaignlist);
}

$resultstr= json_encode($result);
print($resultstr);

?>
