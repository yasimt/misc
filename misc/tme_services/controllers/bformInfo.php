<?php
class bformInfo extends Controller {
	function __construct() {
        parent::__construct();
    }
    
    function getTempData() {
		echo $this->view->bformInfo	=	$this->model->getTempData();
	}
	function getDNDInfo() {
		echo $this->view->bformInfo	=	$this->model->getDNDInfo();
	}
	function submitLocationForm() {
		echo $this->view->bformInfo	=	$this->model->submitLocationForm();
	}
	function globalCompanyApi($params = array()) {
		echo $this->view->bformInfo	=	$this->model->globalCompanyApi($params);
	}
	function getStateInfo(){
		echo $this->view->bformInfo	=	$this->model->getStateInfo();
	}
	function getCityInfo(){
		echo $this->view->bformInfo	=	$this->model->getCityInfo();
	}
	function cityAutosuggest(){
		echo $this->view->bformInfo	=	$this->model->cityAutosuggest();
	}
	function getStreetInfo(){
		echo $this->view->bformInfo	=	$this->model->getStreetInfo();
	}
	function getAreaInfo(){
		echo $this->view->bformInfo	=	$this->model->getAreaInfo();
	}
	function areaAutosuggest(){
		echo $this->view->bformInfo	=	$this->model->areaAutosuggest();
	}
	function areaPincodeRequest(){
		echo $this->view->bformInfo	=	$this->model->areaPincodeRequest();
	}
	function getLandmarkInfo(){
		echo $this->view->bformInfo	=	$this->model->getLandmarkInfo();
	}
	function getStdCodeInfo(){
		echo $this->view->bformInfo	=	$this->model->getStdCodeInfo();
	}
	function getPincodeInfo(){
		echo $this->view->bformInfo	=	$this->model->getPincodeInfo();
	}
	function pincodeLookup(){
		echo $this->view->bformInfo	=	$this->model->pincodeLookup();
	}
	function sourceWiseDupCheck(){
		echo $this->view->bformInfo	=	$this->model->sourceWiseDupCheck();
	}
	function correctIncorrectInfo(){
		echo $this->view->bformInfo	=	$this->model->correctIncorrectInfo();
	}
	function getPaymentNarrationInfo(){
		echo $this->view->bformInfo	=	$this->model->getPaymentNarrationInfo();
	}
	function getMandateinfo(){
		echo $this->view->bformInfo	=	$this->model->getMandateinfo();
	}
	function sendAppLink(){
		echo $this->view->bformInfo	=	$this->model->sendAppLink();
	}
	function sendTvAdLink(){
		echo $this->view->bformInfo	=	$this->model->sendTvAdLink();
	}
	function sendTvAdNAppLink(){
		echo $this->view->bformInfo	=	$this->model->sendTvAdNAppLink();	
	}
	function checkLeadContract(){		
		echo $this->view->bformInfo	=	$this->model->checkLeadContract();	
	}
	function checkEntryEcslead(){
		echo $this->view->bformInfo	=	$this->model->checkEntryEcslead();	
	}
	function updateClientInfo(){
		echo $this->view->bformInfo	=	$this->model->updateClientInfo();
	}
	function insertLog(){
		echo $this->view->bformInfo	=	$this->model->insertLog();
	}
	function instantLiveApi(){
		echo $this->view->bformInfo	=	$this->model->instantLiveApi();
	}
	function insertgenralinfoshadow() {
		echo $this->view->bformInfo	=	$this->model->insertgenralinfoshadow();
	}
	function insertextradetailsshadow() {
		echo $this->view->bformInfo	=	$this->model->insertextradetailsshadow();
	}
	function insertirodetails() {
		echo $this->view->bformInfo	=	$this->model->insertirodetails();
	}
	function inserttempinter() {
		echo $this->view->bformInfo	=	$this->model->inserttempinter();
	}
	function iroAppTransfer(){
		echo $this->view->bformInfo	=	$this->model->iroAppTransfer();
	}
	function iroAppSaveExit(){
		echo $this->view->bformInfo	=	$this->model->iroAppSaveExit();
	}
	function iroAppProceed(){
		echo $this->view->bformInfo	=	$this->model->iroAppProceed();
	}
	function getJdrrDetails(){
		echo $this->view->bformInfo	=	$this->model->getJdrrDetails();
	}
	
	function getFreeListingData(){
		echo $this->view->bformInfo	=	$this->model->getFreeListingData();
	}
	function getMatchedActiveData(){
		echo $this->view->bformInfo	=	$this->model->getMatchedActiveData();
	}
	function estimatedSearchInfo(){
		echo $this->view->bformInfo	=	$this->model->estimatedSearchInfo();
	}
	function ecsTransferInfo(){
		echo $this->view->bformInfo	=	$this->model->ecsTransferInfo();
	}
	function webDialerAllocation(){
		echo $this->view->bformInfo	=	$this->model->webDialerAllocation();
	}
	function ecsEscalationDetails(){
		echo $this->view->bformInfo	=	$this->model->ecsEscalationDetails();
	}
	function buildingAutoComplete(){
		echo $this->view->bformInfo	=	$this->model->buildingAutoComplete();
	}
	function iroCardInfo(){
		echo $this->view->bformInfo	=	$this->model->iroCardInfo();
	}
	function fetchRestInfo(){
		echo $this->view->bformInfo	=	$this->model->fetchRestInfo();
	}
	function jdpayEcsPopup(){
		echo $this->view->bformInfo	=	$this->model->jdpayEcsPopup();
	}
	function ecsTransDetailsUpdate(){
		echo $this->view->bformInfo	=	$this->model->ecsTransDetailsUpdate();
	}
	function phoneSearchAllocation(){
		echo $this->view->bformInfo	=	$this->model->phoneSearchAllocation();
	}
	function ecsSendUpgradeRequest(){
		echo $this->view->bformInfo	=	$this->model->ecsSendUpgradeRequest();
	}
	function fetchEcsDetails(){
		echo $this->view->bformInfo	=	$this->model->fetchEcsDetails();
	}
	function getAllTme(){
		echo $this->view->bformInfo	=	$this->model->getAllTme();
	}
	function updateRetentionTmeInfo(){
		echo $this->view->bformInfo	=	$this->model->updateRetentionTmeInfo();
	}
	function updateRepeatCount(){
		echo $this->view->bformInfo	=	$this->model->updateRepeatCount();
	}
	function getbypassdet(){
		echo $this->view->bformInfo	=	$this->model->getbypassdet();
	}
	
}

?>
