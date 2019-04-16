<?php
require_once('../config.php');
require_once('includes/nationallistingclass.php');
//http://prameshjha.jdsoftware.com/jdbox/services/nationallisting.php?action=isnationallisting&parentid=PXX22.XX22.151020072620.U9B3&module=cs&data_city=mumbai

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
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

$nationallistingclass_obj = new nationallistingclass($params);

if($params['action']=='isnationallisting')
{
	$result = $nationallistingclass_obj->isnationallisting();
}

if($params['action']=='fetchtempdata')
{
	$result = $nationallistingclass_obj->getNationalListingTempData();
}

if($params['action']=='calcupdatedata')
{
	$result = $nationallistingclass_obj->Calculate_Update_Budget();
}

if($params['action']=='removeLocalforNational')
{
	$result = $nationallistingclass_obj->removeLocalforNational();
}

if($params['action']=='checkCalcStatus')
{
	$result = $nationallistingclass_obj->NationalListingSanityCheck();
}

if($params['action']=='GetNationalMinBudget')
{
	$result = $nationallistingclass_obj->GetNationalMinBudget();
}



if($params['action']=='CheckNationListing2')
{
	$result = $nationallistingclass_obj->CheckNationListing2();
}


if($params['action']=='InsertGenioLite')
{
	$result = $nationallistingclass_obj->InsertGenioLite();
}

if($params['action']=='InsertSelectedCites')
{
	$result = $nationallistingclass_obj->InsertSelectedCites();
}

if($params['action']=='FetchSelectedCites')
{
	$result = $nationallistingclass_obj->FetchSelectedCites();
}

if($params['action']=='resetNationalBannerData')
{
	$result = $nationallistingclass_obj->resetNationalBannerData();
}



$resultstr= json_encode($result);

print($resultstr);

?>
