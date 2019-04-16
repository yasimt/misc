<?php

require_once('../config.php');
require_once('includes/ecsMandateClass.php');
//ganeshrj.jdsoftware.com/jdbox_cat/services/updateOmniBudget.php?data_city=mumbai&module=tme&action=1&user_price=&parentid=&version= add
//ganeshrj.jdsoftware.com/jdbox_cat/services/updateOmniBudget.php?data_city=mumbai&module=tme&action=-1&parentid=&version= delete

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

if(isset($_REQUEST))
{
	$params=$_REQUEST;
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

$ecsMandateClassobj = new ecsMandateClass($params);
if($params['action']=='1')
	$result = $ecsMandateClassobj->getBankDetails();
else if($params['action']=='2')
	$result = $ecsMandateClassobj->getMandateDetails();
else if($params['action']=='3')
	$result = $ecsMandateClassobj->saveMandateDetails();
else if($params['action']=='4')
	$result = $ecsMandateClassobj->TempToMainIdc();
else if($params['action']=='5')
	$result = $ecsMandateClassobj->MainToTemp();
else if($params['action']=='6')
	$result = $ecsMandateClassobj->TempToMain();
else if($params['action']=='7')
	$result = $ecsMandateClassobj->getMandateDetailsCS();
else if($params['action']=='8')
	$result = $ecsMandateClassobj->bankNameAutoSuggest();
else if($params['action']=='9')
	$result = $ecsMandateClassobj->bankCityAutoSuggest();
else if($params['action']=='10')
	$result = $ecsMandateClassobj->bankBranchAutoSuggest();
else if($params['action']=='11')
	$result = $ecsMandateClassobj->bankIfsc();
else if($params['action']=='12')
	$result = $ecsMandateClassobj->bankMICR();
else if($params['action']=='13')
	$result = $ecsMandateClassobj->getBankDetailsmicr();
else{
	echo 'invalid req';exit;
}

$resultstr= json_encode($result);
print($resultstr);

