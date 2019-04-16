<?php
require_once('../config.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}
require_once('includes/proposal_content_class.php');
require_once('includes/contractdetailsclass.php');

//http://prameshjha.jdsoftware.com/jdbox/services/regfee.php?campaignid=1&contactno=8800900009&data_city=mumbai


if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

}

if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
print_r($params);
}
//echo json_encode($params); exit;

$proposal_obj = new proposal_content_class($params);

$params_contract_details = $params;
$params_contract_details['action'] ='GetPlatDiamCategories';


if($params['action'] == 1)
{
	$contractdetailsclassobj = new contractdetailsclass($params_contract_details);
	
	$result = $proposal_obj -> get_proposal_content($contractdetailsclassobj);
}


//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

