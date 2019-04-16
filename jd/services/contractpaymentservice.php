<?php
require_once('../config.php');
require_once('includes/contractpaymentserviceclass.php');

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
	header('Content-type: application/json');
}

//http://prameshjha.jdsoftware.com/jdbox/services/contractpaymentservice.php?action=updatepaymentdetailsdealclose&data_city=mumbai&instrumentid=C01B12J8G4

//http://prameshjha.jdsoftware.com/jdbox/services/contractpaymentservice.php?action=updatepaymentdetailsdealclose&data_city=mumbai&origincity=mumbai&depositlocation=mumbai&parentid=PXX22.XX22.121108175438.Z3W7&companyname=test&campaignidlist=1,2,13&version=12&dealclosedate=2012-11-08&dealclosebudget=15000&campaignwisebudget=1-5000,2-9000,13-1000&paymentType=fresh&instrumentid=C01B12J8G4&instrumentType=cash&instrumentamount=10000&service_tax=12&tdsAmount=0&cscode=013084&csname=pramesh%20jha&tmecode&tmename&mecode&mename&jdacode&jdaname&entrymodule=cs&source=cs&data_city=mumbai&origin_city=mumbai&approvalStatus=0&defaultupdatetimestamp=2012-11-08%2018:08:38&module=tme&duration=365


//updatepaymentdetailsapproval

//http://prameshjha.jdsoftware.com/jdbox/services/contractpaymentservice.php?action=updatepaymentdetailsapproval&instrumentid=C01B12J8G4&data_city=mumbai

//&origincity=mumbai&depositlocation=mumbai&parentid=PXX22.XX22.121108175438.Z3W7&companyname=test&campaignidlist=1,2,13&version=12&dealclosedate=2012-11-08&dealclosebudget=15000&campaignwisebudget=1-5000,2-9000,13-1000&paymentType=fresh&&instrumentType=cash&instrumentamount=10000&service_tax=12&tdsAmount=0&cscode=013084&csname=pramesh%20jha&tmecode&tmename&mecode&mename&jdacode&jdaname&entryModule=cs&source=cs&data_city=mumbai&origin_city=mumbai&approvalStatus=0&defaultupdatetimestamp=2012-11-08%2018:08:38&module=tme&duration=365

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

//echo json_encode($params);
//exit;


$cpsclass_obj = new contractpaymentserviceclass($params);


if($params['action']=='updatepaymentdetailsdealclose')
{
//echo json_encode($params);
//exit;
$result = $cpsclass_obj->updatepaymentdetailsdealclose();	
}

if($params['action']=='updatepaymentdetailsapproval')
{
$result = $cpsclass_obj->updatepaymentdetailsapproval();	
}


if($params['action']=='deleteinstrument')
{
//echo json_encode($params);
//exit;
$result = $cpsclass_obj->deleteinstrument();	
}

//print_r($result);
$resultstr= json_encode($result);

print($resultstr);

?>
