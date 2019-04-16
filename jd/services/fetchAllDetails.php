<?php

//Sample URL : http://imteyazraja.jdsoftware.com/jdbox/services/fetchAllDetails.php?parentid=PXX22.XX22.170705191446.B9T2&data_city=Mumbai&module=ME&post_data=1&ucode=10000760&uname=Imteyaz&action=tempdetails
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/allDetailsClass.php');


if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	echo "<pre>";
}
else
{
	define("DEBUG_MODE",0);	
	header('Content-Type: application/json');
}



if($_REQUEST['post_data'])
{
	
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	
	$params	= json_decode(file_get_contents('php://input'),true);
}
$all_details_class_obj  	= new allDetailsClass($params);

if($params['action'] == 'tempdetails'){
	$all_details_resp_arr = $all_details_class_obj->getTempInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'dndinfo'){
	$all_details_resp_arr = $all_details_class_obj->dndInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'stateinfo'){
	$all_details_resp_arr = $all_details_class_obj->getStateInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'cityinfo'){
	$all_details_resp_arr = $all_details_class_obj->getCityInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'cityautosuggest'){
	$all_details_resp_arr = $all_details_class_obj->cityAutoSuggest();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'streetinfo'){
	$all_details_resp_arr = $all_details_class_obj->getStreetInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'areainfo'){
	$all_details_resp_arr = $all_details_class_obj->getAreaInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'areaautosuggest'){
	$all_details_resp_arr = $all_details_class_obj->areaAutoSuggest();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'landmarkinfo'){
	$all_details_resp_arr = $all_details_class_obj->getLandmarkInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'stdcodeinfo'){
	$all_details_resp_arr = $all_details_class_obj->getStdCodeInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'pincodeinfo'){
	$all_details_resp_arr = $all_details_class_obj->getPincodeInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'pincodelookup'){
	$all_details_resp_arr = $all_details_class_obj->getPincodeLookup();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'imagedetails'){
	$all_details_resp_arr = $all_details_class_obj->imagedetails();
	$all_details_resp_str = json_encode($all_details_resp_arr,JSON_FORCE_OBJECT);
	print($all_details_resp_str);
}else if($params['action'] == 'sourcewisedupchk'){
	$all_details_resp_arr = $all_details_class_obj->sourceWiseDuplicacyChk();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'correctincorrectinfo'){
	$all_details_resp_arr = $all_details_class_obj->correctIncorrectInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'narrationinfo'){
	$all_details_resp_arr = $all_details_class_obj->getNarrationInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'paymentnarrationInfo'){
	$all_details_resp_arr = $all_details_class_obj->getPaymentNarrationInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'mandateinfo'){
	$all_details_resp_arr = $all_details_class_obj->getMandateinfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'sendapplink'){
	$all_details_resp_arr = $all_details_class_obj->sendAppLink();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'sendtvadlink'){
	$all_details_resp_arr = $all_details_class_obj->sendTvAdLink();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'sendtvadnapplink'){
	$all_details_resp_arr = $all_details_class_obj->sendTvAdNAppLink();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'getContractType'){
	$all_details_resp_arr = $all_details_class_obj->checkLeadContract();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'updateclientinfo'){
	$all_details_resp_arr = $all_details_class_obj->updateClientInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else if($params['action'] == 'iroapptransfer'){
	$all_details_resp_arr = $all_details_class_obj->iroAppTransfer();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'iroappsavenexit'){
	$all_details_resp_arr = $all_details_class_obj->iroAppSaveExit();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'iroappproceed'){
	$all_details_resp_arr = $all_details_class_obj->iroAppProceed();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'pincodechangelog'){
	$all_details_resp_arr = $all_details_class_obj->pincodeChangeLog();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'areapincoderequest'){
	$all_details_resp_arr = $all_details_class_obj->areaPincodeRequest();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'mobilefeedback'){
	$all_details_resp_arr = $all_details_class_obj->insertMobileFeedback();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'addsuggestedcity'){
	$all_details_resp_arr = $all_details_class_obj->addSuggestedCity();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'getpiddata'){
	$all_details_resp_arr = $all_details_class_obj->getPidData();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'getjdrrdetails'){
	$all_details_resp_arr = $all_details_class_obj->getJdrrDetails();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'mobilefeedback'){
	$all_details_resp_arr = $all_details_class_obj->mobilefeedback();
	print($all_details_resp_arr);
}else if($params['action'] == 'correct_incorrect_update'){ 
	$all_details_resp_arr = $all_details_class_obj->updateCorrectIncorrectInfo();
	print($all_details_resp_arr);
}else if($params['action'] == 'insertgenralinfoshadow'){ 
	$all_details_resp_arr = $all_details_class_obj->insertgenralinfoshadow();
	print($all_details_resp_arr);
}else if($params['action'] == 'insertextradetailsshadow'){ 
	$all_details_resp_arr = $all_details_class_obj->insertextradetailsshadow();
	print($all_details_resp_arr);
}else if($params['action'] == 'insertondemandinfo'){ 
	$all_details_resp_arr = $all_details_class_obj->insertondemandinfo();
	print($all_details_resp_arr);
}else if($params['action'] == 'insertirodetails'){ 
	$all_details_resp_arr = $all_details_class_obj->insertirodetails();
	print($all_details_resp_arr);
}else if($params['action'] == 'inserttempinter'){ 
	$all_details_resp_arr = $all_details_class_obj->inserttempinter();
	print($all_details_resp_arr);
}else if($params['action'] == 'checkentryecslead'){
	$all_details_resp_arr = $all_details_class_obj->checkEntryEcsLead();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'estimatedsearchlink'){
	$all_details_resp_arr = $all_details_class_obj->estimatedSearchInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'matchedactivedata'){
	$all_details_resp_arr = $all_details_class_obj->getMatchedActiveData();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'irocardinfo'){
	$all_details_resp_arr = $all_details_class_obj->iroCardInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'ecsescalationdetails'){
	$all_details_resp_arr = $all_details_class_obj->ecsEscalationDetails();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'webdialerallocation'){
	$all_details_resp_arr = $all_details_class_obj->webDialerAllocation();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'phonesearchallocation'){
	$all_details_resp_arr = $all_details_class_obj->phoneSearchAllocation();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'ecstransferinfo'){
	$all_details_resp_arr = $all_details_class_obj->ecsTransferInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'bform_incorrect_loginfo'){
	$all_details_resp_arr = $all_details_class_obj->bformIncorrectLoginfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'building_autocomplete'){
	$all_details_resp_arr = $all_details_class_obj->buildingAutoComplete();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'fetch_restaurant_info'){
	$all_details_resp_arr = $all_details_class_obj->fetchRestaurantInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'jdpay_ecs_popup'){
	$all_details_resp_arr = $all_details_class_obj->jdpayEcsPopup();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'ecs_trans_details_update'){
	$all_details_resp_arr = $all_details_class_obj->ecsTransDetailsUpdate();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'fetchecsdetails'){
	$all_details_resp_arr = $all_details_class_obj->fetchEcsDetails();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'getalltme'){
	$all_details_resp_arr = $all_details_class_obj->getAllTme();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'ecssendupgraderequest'){
	$all_details_resp_arr = $all_details_class_obj->ecsSendUpgradeRequest();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'updateretentiontmeinfo'){
	$all_details_resp_arr = $all_details_class_obj->updateRetentionTmeInfo();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'updaterepeatcount'){
	$all_details_resp_arr = $all_details_class_obj->updateRepeatCount();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'mktgBarLoad'){
	$all_details_resp_arr = $all_details_class_obj->mktgBarLoad();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'idgenerator'){
	$all_details_resp_arr = $all_details_class_obj->idGeneratorData();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action'] == 'tmesearchdata'){
	$all_details_resp_arr = $all_details_class_obj->getTmeSearchData();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action']== "chkRatingCat"){
	$all_details_resp_arr = $all_details_class_obj->chkRatingCat();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action']== "getbypassdet"){
	$all_details_resp_arr = $all_details_class_obj->getbypassdet();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str); die;
}else if($params['action']== "businessTempdataIdc"){
	$all_details_resp_arr = $all_details_class_obj->businessTempdataIdc();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}else if($params['action']== "updateTimerStatus"){
	$all_details_resp_arr = $all_details_class_obj->updateTimerStatus();
	$all_details_resp_str = json_encode($all_details_resp_arr);
	print($all_details_resp_str);
}
else{
    $die_msg_arr['error']['code'] = 1;
	$die_msg_arr['error']['msg'] = "Invalid Action";
	echo json_encode($die_msg_arr);
    die();
}
// ecslead 
// generalinfo main table  

//if($params['action'] == 'tempdetails'){ 
// action  duplicatecheck -- shital 18 


?>
