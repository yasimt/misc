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
require_once('includes/jd_omni_report_class.php');

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
$jd_omni_report = new jd_omni_report_class($params);


if($params['action'] == 1)
{
	$result = $jd_omni_report -> GenerateReport();
}


if($params['action'] == 2)
{
	$result = $jd_omni_report -> UpdateVersion();
}


if($params['action'] == 3)
{
	$result = $jd_omni_report -> getOmniDespositions();
}


if($params['action'] == 4)
{
	$result = $jd_omni_report -> updateOmniDesposition();
}



//echo "<pre>"; print_r($result);
$resultstr= json_encode($result);

print($resultstr);

