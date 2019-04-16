<?php
class tmeInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
	function FetchEcsDetailsForm() {
		echo $this->view->tmeinfo	=	$this->model->FetchEcsDetailsForm();
	}

	function SendRemainderEcsLead() {
		echo $this->view->tmeinfo	=	$this->model->SendRemainderEcsLead();
	}
	
	function fetchDealClosedReport($empCode='',$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->fetchDealCloseDataReport($empCode,$fullPage);
	}
	
	function setSortOrder() {
		echo $this->view->tmeinfo       =       $this->model->setSortOrder();
	}
	
	function setSpeedLinks() {
		echo $this->view->tmeinfo	=	$this->model->setSpeedLinks();
	}
	
	function getSpeedLinks($empCode='') {
		echo $this->view->tmeinfo       =       $this->model->getSpeedLinks($empCode);
	}
	
	function fetchCategoryData($tmeCode='') {
		echo $this->view->tmeinfo	=	$this->model->fetchCategoryData($tmeCode);
	}
	
	function delProspectData() {
		echo $this->view->tmeinfo	=	$this->model->delProspectData();
	}
	
	function fetchChildInfo() {
		echo $this->view->tmeinfo	=	$this->model->fetchChildInfo();
	}
	
	function showTimelineData($tmecode) {
		echo $this->view->tmeinfo	=	$this->model->showTimelineData($tmecode);
	}
	
	function updatestopflag($tmecode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->updatestopflag($tmecode,$fullPage);
	} 
	
	function getHistory($tmecode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->getHistory($tmecode,$fullPage);
	}
	
	function reactivaterequest($tmecode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->reactivaterequest($tmecode,$fullPage);
	}
	
	 function checkupdate($tmecode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->checkupdate($tmecode,$fullPage);
	}
	
	function fetchmelist() {
		echo $this->view->melist	=	$this->model->fetchmelist();
	}
	
	function insertmename() {
		echo $this->view->response	=	$this->model->insertmename();
	}

	function getDataCountTME() {
		echo $this->view->tmeinfo	=	$this->model->getDataCountTME();
	}
	
	function send_mngr_request($parentid,$eventParam,$empcode) {
		echo $this->view->tmeinfo  = $this->model->send_mngr_request($parentid,$parentid,$empcode);
	}

	function checkvccontract($parentid) {
		echo $this->view->tmeinfo  = $this->model->checkvccontract($parentid);
	}

	function checkvccondition($parentid) {
		echo $this->view->tmeinfo  = $this->model->checkvccondition($parentid);
	}
	
	function allocateappt(){ // hold
		echo $this->view->tmeinfo	=	$this->model->allocateappt();
	}
	
	function getSSOEmp(){
		echo $this->view->tmeinfo	=	$this->model->getSSOEmp();
	}
	
	function call_cticlicktocall() { // at last
		echo $this->view->tmeinfo  = $this->model->call_cticlicktocall();
	}
	
	function getPenaltyDetails() {
		echo $this->view->tmeinfo  = $this->model->getPenaltyDetails();
	}
	
	function getDoDontDetails() {
		echo $this->view->tmeinfo  = $this->model->getDoDontDetails();
	}
	function fetchEditListingData() {
		echo $this->view->tmeinfo  = $this->model->fetchEditListingData();
	}
	
	function fetchEditListingEntry() {
		echo $this->view->tmeinfo  = $this->model->fetchEditListingEntry();
	}
	function getTMECallLogs() {
		echo $this->view->tmeinfo  = $this->model->getTMECallLogs();
	}
	function fetchMagazineData($empCode,$fullPage) {
		echo $this->view->tmeinfo  = $this->model->fetchMagazineData($empCode,$fullPage);
	}
	function getBudgetService() {
		echo $this->view->tmeinfo   =   $this->model->getBudgetService();
	}

	function updateBudgetService() {
		echo $this->view->tmeinfo   =   $this->model->updateBudgetService();
	}
    function getCollectData(){
		echo $this->view->tmeinfo   =   $this->model->getCollectData();
	}
    function updtLogoutTime(){
		echo $this->view->tmeinfo   =   $this->model->updtLogoutTime();
	}
	function updateRdFlg(){
		echo $this->view->tmeinfo   =   $this->model->updateRdFlg();
	}
    function insertDeliveredCaseInfo() {
        echo $this->view->tmeinfo  =   $this->model->insertDeliveredCaseInfo();
    }
    //save as free listing
    function updMnTabSaveAsNonPaid() {
        echo $this->view->tmeinfo  =   $this->model->updMnTabSaveAsNonPaid();
    }
    function insertSaveLogs() {
        echo $this->view->tmeinfo  =   $this->model->insertSaveLogs();
    }
    function insertWhatsapp() {
        echo $this->view->tmeinfo  =   $this->model->insertWhatsapp();
    }
    function insertWhatsappData() {
        echo $this->view->tmeinfo  =   $this->model->insertWhatsappData();
    }
    function getallcampaigns() {
        echo $this->view->tmeinfo  =   $this->model->getallcampaigns();
    }
    function check_if_propic_selected() {
        echo $this->view->tmeinfo  =   $this->model->check_if_propic_selected();
    }
    function setImageProPic(){
    	echo $this->view->tmeinfo  =   $this->model->setImageProPic();
    }
    
	function insertWhatsappSendMsg() {
		echo $this->view->tmeinfo	=	$this->model->insertWhatsappSendMsg();
	}
function editeddata() {
		echo $this->view->tmeinfo	=	$this->model->editeddata();
	}

	function manageediteddata() {
		echo $this->view->tmeinfo	=	$this->model->manageediteddata();
	}
    function getAhdLineage() {
        echo $this->view->tmeinfo  =   $this->model->getAhdLineage();
    }
	function fetchFreebeesEmp() {
        echo $this->view->tmeinfo  =   $this->model->fetchFreebeesEmp();
    }
}
