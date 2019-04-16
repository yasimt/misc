<?php

require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/tmeNewServicesClass.php');
//SAMPLE URL : http://tmegeniocategory.jdsoftware.com/jdbox/services/tmeWrapperPage.php?empcode=10026425&data_city=mumbai&module=TME&action=1&trace=1
//http://tmegeniocategory.jdsoftware.com/jdbox/services/tmenewServices.php?post_data=1&empcode=10018317&data_city=mumbai&action=EmpInfo&module=TME
if($_REQUEST["trace"] ==1){
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}else{
	define("DEBUG_MODE",0);	
}
if($_REQUEST['post_data']){
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
}else{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

$wrapperClassobj = new tmeNewServicesClass($params);
if($params['action']=='1'){
	$result = $wrapperClassobj->getEmpMessageDetails();
}else if($params['action']=='2'){
	$result = $wrapperClassobj->getCallBackData();
}else if($params['action']	==	'EmpInfo'){
	$result = $wrapperClassobj->EmpInfo();
}else if($params['action']	==	'MenuLinks'){
	$result = $wrapperClassobj->fetchMenuLinks();
}else if($params['action']	==	'HotData'){ 
	$result = $wrapperClassobj->getHotData();
}else if($params['action']	==	'newBusiness'){
	$result = $wrapperClassobj->fetchNewBusiness();
}else if($params['action']	==	'RestaurantData'){
	$result = $wrapperClassobj->fetchRestaurantData();
}else if($params['action']	==	'EcsData'){
	$result = $wrapperClassobj->fetchEcsData();
}else if($params['action']	==	'ExpiredData'){
	$result = $wrapperClassobj->fetchExpiredData();
}else if($params['action']	==	'ProspectData'){
	$result = $wrapperClassobj->fetchProspectData();
}else if($params['action']	==	'SpecialData'){
	$result = $wrapperClassobj->fetchSpecialData();
}else if($params['action']	==	'accountDetRest'){
	$result = $wrapperClassobj->accountDetRest();
}else if($params['action']	==	'tmeAllocData'){
	$result = $wrapperClassobj->fetchtmeAllocData();
}else if($params['action']	==	'reversedRetentionData'){
	$result = $wrapperClassobj->fetchReversedRetentionData(); //continue
}else if($params['action']	==	'NonEcsData'){
	$result = $wrapperClassobj->fetchNonecsData(); 
}else if($params['action']	==	'unsoldData'){
	$result = $wrapperClassobj->fetchunsoldData(); 
}else if($params['action']	==	'ExpiredEcsData'){
	$result = $wrapperClassobj->fetchExpiredDataEcs(); 
}else if($params['action']	==	'ExpiredNonEcsData'){
	$result = $wrapperClassobj->fetchExpiredDataNonEcs(); 
}else if($params['action']	==	'ecsRequestData'){
	$result = $wrapperClassobj->fetchEcsRequestData(); 
}else if($params['action']	==	'deliverySystem'){
	$result = $wrapperClassobj->fetchdeliverySystem(); 
}else if($params['action']	==	'leadComplaints'){
	$result = $wrapperClassobj->fetchleadComplaints(); 
}else if($params['action']	==	'jdrrPropectData'){
	$result = $wrapperClassobj->fetchjdrrPropectData(); 
}else if($params['action']	==	'ReportData'){
	$result = $wrapperClassobj->fetchReports(); 
}else if($params['action']	==	'AllocContracts'){
	$result = $wrapperClassobj->fetchAllocContracts(); 
}else if($params['action']	==	'PackgaeData'){
	$result = $wrapperClassobj->fetchPackgaeData(); 
}else if($params['action']	==	'RetentionData_info'){
	$result = $wrapperClassobj->fetchRetentionData_information(); 
}else if($params['action']	==	'retentionData'){
	$result = $wrapperClassobj->fetchRetentionData();
}else if($params['action']	==	'JDRatingData'){
	$result = $wrapperClassobj->fetchJDRatingData();
}else if($params['action']	==	'getLineage'){
	$result = $wrapperClassobj->getLineageInfo();
}else if($params['action']	==	'checkUpdatedOn'){
	$result = $wrapperClassobj->checkUpdatedOn();
}else if($params['action']	==	'insertlineage'){
	$result = $wrapperClassobj->insertlineageDetails();
}else if($params['action']	==	'fetchreportees'){
	$result = $wrapperClassobj->fetchreportees();
}else if($params['action']	==	'accetRejectRequest'){
	$result = $wrapperClassobj->accetRejectRequest();
}else if($params['action']	==	'insertReportDetails'){
	$result = $wrapperClassobj->insertReportDetails();
}else if($params['action']	==	'sendOTP'){
	$result = $wrapperClassobj->sendOTP();
}else if($params['action']	==	'checkOTP'){
	$result = $wrapperClassobj->checkOTP();
}else if($params['action']	==	'countRequest'){
	$result = $wrapperClassobj->countRequest();
}else if($params['action']	==	'insertPenaltyUpdatedOn'){
	$result = $wrapperClassobj->insertPenaltyUpdatedOn();
}else if($params['action']	==	'getcitylist'){
	$result = $wrapperClassobj->getcitylist();
}else if($params['action']	==	'magazineData'){
	$result = $wrapperClassobj->fetchMagazineData();
}else if($params['action']	==	'empDeclaration'){
	$result = $wrapperClassobj->checkemployeedeclaration();
}else if($params['action']	==	'getSpeedLinks'){
	$result = $wrapperClassobj->getSpeedLinks();
}else if($params['action']	==	'setSpeedLinks'){
	$result = $wrapperClassobj->setSpeedLinks();
}else if($params['action']	==	'setSortOrder'){
	$result = $wrapperClassobj->setSortOrder();
}else if($params['action']=='getContractCatLive'){
	$result = $wrapperClassobj->getContractCatLive($params['contractid']);
}else if($params['action']	==	'getEcsEmpcode'){
	$result = $wrapperClassobj->getEcsEmpcode();
}else if($params['action']	==	'getDoDontDetails'){
	$result = $wrapperClassobj->getDoDontDetails();
}else if($params['action']	==	'getDispositionList'){
	$result = $wrapperClassobj->getDispositionList();
}else if($params['action']	==	'getSSOInfo'){
	$result = $wrapperClassobj->getSSOInfo();
}else if($params['action']	==	'employTimeLog'){
	$result = $wrapperClassobj->employTimeLog();
}else if($params['action']	==	'cityInfo'){
	$result = $wrapperClassobj->cityInfo();
}else if($params['action']	==	'mediaspeaks'){
	$result = $wrapperClassobj->mediaspeaks();
}else if($params['action']	==	'getlineagealldata'){
	$result = $wrapperClassobj->getlineagealldata();
}else if($params['action']	==	'getmktgEmpMaster'){
	$result = $wrapperClassobj->getmktgEmpMaster();
}else if($params['action']	==	'ReportsInfoTimeline'){
	$result = $wrapperClassobj->fetchReportsInfoTimeline();
}else if($params['action']	==	'DealCloseDataReport'){
	$result = $wrapperClassobj->fetchDealCloseDataReport();
}else if($params['action']	==	'bounceData'){
	$result = $wrapperClassobj->fetchBounceData();
}else if($params['action']	==	'bounceECSData'){
	$result = $wrapperClassobj->fetchBounceECSData();
}else if($params['action']	==	'instantEcsData'){
	$result = $wrapperClassobj->fetchInstantECSData();
}else if($params['action']	==	'PaidExpiredVN'){
	$result = $wrapperClassobj->fetchPaidExpiredVNData();
}else if($params['action']	==	'getlineagealldata'){
	$result = $wrapperClassobj->getlineagealldata();
}else if($params['action']	==	'citymasterInfo'){
	$result = $wrapperClassobj->citymasterInfo();
}else if($params['action']	==	'CourierData'){
	$result = $wrapperClassobj->fetchjdrrCourierData();
}else if($params['action']	==	'getRowId'){
	$result = $wrapperClassobj->getEmpRowId();
}else if($params['action']	== 'storemp'){
	$result = $wrapperClassobj->storeemp();
}else if($params['action']	== 'EditListingData'){
	$result = $wrapperClassobj->fetchEditListingData();
}else if($params['action']	== 'fetchEditListingEntry'){
	$result = $wrapperClassobj->fetchEditListingEntry();
}else if($params['action']	== 'mobAllocDetails'){
	$result = $wrapperClassobj->mobAllocDetails();
}else if($params['action']	== 'companyAuto'){
	$result = $wrapperClassobj->companyAutoSuggestAllocated();
}else if($params['action']	== 'getPenaltyInfo'){
	$result = $wrapperClassobj->getPenaltyDetails();
}else if($params['action']	== 'updateRdFlg'){
	$result = $wrapperClassobj->updateRdFlg();
}else if($params['action']	== 'checkvccontract'){
	$result = $wrapperClassobj->checkvccontract();
}else if($params['action']	== 'checkvccondition'){
	$result = $wrapperClassobj->checkvccondition();
}else if($params['action']	== 'getDataCountTME'){
	$result = $wrapperClassobj->getDataCountTME();
}else if($params['action']	== 'checkupdate'){
	$result = $wrapperClassobj->checkupdate();
}else if($params['action']	== 'getHistory'){
	$result = $wrapperClassobj->getHistory();
}else if($params['action']	== 'insertmename'){
	$result = $wrapperClassobj->insertmename();
}else if($params['action']	== 'fetchmelist'){
	$result = $wrapperClassobj->fetchmelist();
}else if($params['action']	== 'fetchContractinventoryMorethanFifty'){
	$result = $wrapperClassobj->fetchContractinventoryMorethanFifty();
}else if($params['action']	== 'fetchContractInventory'){
	$result = $wrapperClassobj->fetchContractInventory();
}else if($params['action']	== 'SendRemainderEcsLead'){
	$result = $wrapperClassobj->SendRemainderEcsLead();
}else if($params['action']	== 'FetchEcsDetailsForm'){
	$result = $wrapperClassobj->FetchEcsDetailsForm();
}else if($params['action']	== 'fetchCategoryData'){
	$result = $wrapperClassobj->fetchCategoryData();
}else if($params['action']	== 'updatestopflag'){
	$result = $wrapperClassobj->updatestopflag();
}else if($params['action']	==	'insertDeliveredCaseInfo'){
	$result = $wrapperClassobj->insertDeliveredCaseInfo();
}else if($params['action']	== 'reactivaterequest'){
	$result = $wrapperClassobj->reactivaterequest();
}else if($params['action']	== 'sendmngrrequest'){
	$result = $wrapperClassobj->send_mngr_request();
}else if($params['action']	== 'deleteProspectData'){
	$result = $wrapperClassobj->deleteProspectData();
}else if($params['action']	== 'ownership'){
	$result = $wrapperClassobj->ownership();
}else if($params['action']	==	'delProspectData'){
	$result = $wrapperClassobj->delProspectData();
}else if($params['action']	==	'saveasnonpaidlog'){
	$result = $wrapperClassobj->saveasnonpaidlog();
}else if($params['action']	==	'insertWhatsapp'){
	$result = $wrapperClassobj->insertWhatsapp();
}else if($params['action']	==	'checkTrackerRep'){
	echo $result = $wrapperClassobj->checkTrackerRep($param); die();
}else if($params['action']	==	"fetchMulticityTagging"){
	echo $result = $wrapperClassobj->fetchMulticityTagging(); die();
}else if($params['action']	==	'insertWhatsappData'){
	$result = $wrapperClassobj->insertWhatsappData();
}else if($params['action']	==	'JdrIro'){
	$result = $wrapperClassobj->JdrIro();
}else if($params['action']	==	'WebIro'){
	$result = $wrapperClassobj->WebIro();
}else if($params['action']	==	'docidcreator'){
	$result = $wrapperClassobj->docid_creator();
}else if($params['action']	==	 "checkifpropicselected"){
	echo $result = $wrapperClassobj->checkifpropicselected(); die();
}else if($params['action'] == "setImageProPic"){
	echo $result = $wrapperClassobj->setImageProPic(); die();
}else if($params['action']	==	'fetchrestaurantdealsoffer'){
	$result = $wrapperClassobj->fetchrestaurantdealsoffer();
}else if($params['action']	==	'whatsappcalled'){
	$result = $wrapperClassobj->whatsappcalled();
}else if($params['action']	==	'fetchsuperhotdata'){
	$result = $wrapperClassobj->fetchsuperhotdataNew();
}else if($params['action']	==	'updatesuperhotdata'){
	$result = $wrapperClassobj->updatesuperhotdata();
}else if($params['action']	==	'fetchFreebeesEmp'){
	$result = $wrapperClassobj->fetchFreebeesEmp();
}

if($result){
	$resultstr= json_encode($result);
	print($resultstr);	
}else{
    $die_msg_arr['errorcode'] = 1;
	$die_msg_arr['errormsg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}
?>
