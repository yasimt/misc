
<?php

require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/contractServicesClass.php');
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
$wrapperClassobj = new contractServicesClass($params);
if($params['action']=='checkTrackerRep'){
	$result = $wrapperClassobj->checkTrackerRep();
}else if($params['action']=='tempContract'){
	$result = $wrapperClassobj->tempContract();
}else if($params['action']== "actEcsRetention"){
	$result = $wrapperClassobj->actEcsRetention();
}else if($params['action']== "searchCompanyByNum"){
	$result = $wrapperClassobj->searchCompanyByNum();
}else if($params['action']== "fetchEcsRetentionData"){
	$result = $wrapperClassobj->fetchEcsRetentionData();
}else if($params['action']== "TmeRetentionComments"){
	$result = $wrapperClassobj->fetchTmeRetentionComments();
}else if($params['action']== "StoreComment"){
	$result = $wrapperClassobj->StoreCommentretention();
}else if($params['action']== "StoreCommentECS"){
	$result = $wrapperClassobj->StoreCommentECS();
}else if($params['action']== "getAutoWrapupInfo"){
	$result = $wrapperClassobj->getAutoWrapupInfo();
}else if($params['action']== "getAutoWrapupInfoDetail"){
	$result = $wrapperClassobj->getAutoWrapupInfoDetail();
}else if($params['action']== "removeCallBack"){
	$result = $wrapperClassobj->removeAllCallBack();
}else if($params['action']== "paymenttype"){
	$result = $wrapperClassobj->payment_type();
}else if($params['action']== "deleteUpdate"){
	$result = $wrapperClassobj->delete_update();
}else if($params['action']== "checkoneplusblock"){
	$result = $wrapperClassobj->check_one_plus_block();
}else if($params['action']== "sendratinglink"){
	$result = $wrapperClassobj->sendratinglink();
}else if($params['action']== "getTimerStatus"){
	$result = $wrapperClassobj->getTimerStatus();
}else if($params['action']== "updategeneralinfoshadow"){
	$result = $wrapperClassobj->update_generalinfo_shadow();
}else if($params['action']== "freeWebsiteStatus"){
	$result = $wrapperClassobj->freeWebsiteStatus();
}else if($params['action']== "getforgetLink"){
	$result = $wrapperClassobj->getforgetLink();
}else if($params['action']== "domainregisterauto"){
	$result = $wrapperClassobj->domainregisterauto();
}else if($params['action']== "setpackemi"){
	$result = $wrapperClassobj->set_pack_emi();
}else if($params['action']== "getpackemi"){
	$result = $wrapperClassobj->get_pack_emi();
}else if($params['action']== "fetchCorIncorAccuracy"){
	$result = $wrapperClassobj->fetchCorIncorAccuracy();
}else if($params['action']== "fetchCorIncorAccuracyDetail"){
	$result = $wrapperClassobj->fetchCorIncorAccuracyDetail();
}else if($params['action']== "getAppointLogInfo"){
	$result = $wrapperClassobj->getAppointLogInfo();
}else if($params['action'] == "showContractBalance"){
	$result = $wrapperClassobj->showContractBalance();
}else if($params['action'] == "insertfreebees"){
	$result = $wrapperClassobj->insertfreebees();
}else if($params['action'] == "checkfreebees"){
	$result = $wrapperClassobj->checkfreebees();
}else if($params['action']	==	"getFreebeesInfo"){
	$result = $wrapperClassobj->getFreebeesInfo();
}else if($params['action']	==	"updateDetails"){
	$result = $wrapperClassobj->updateDetails();
}else if($params['action']	==	"resetfreebeesInfo"){
	$result = $wrapperClassobj->resetfreebeesInfo();
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

