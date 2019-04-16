<?php

class tmenewInfo extends Controller {
	
    function __construct() {
        parent::__construct();
    }
    function get_tmeInfo() {
		echo $this->view->tmeinfo	=	$this->model->tmeInfo();
	}
	function getMenuLinks() {
		echo $this->view->tmeinfo	=	$this->model->fetchMenuLinks();
	}
	function getLineage(){
		echo $this->view->tmeinfo	=	$this->model->getLineage();
	}
	function checkUpdatedOn(){
		echo $this->view->tmeinfo	=	$this->model->checkUpdatedOn();
	}
	function insertlineageDetails(){
		echo $this->view->tmeinfo	=	$this->model->insertlineage();
	}
	function fetchreportees(){
		echo $this->view->tmeinfo	=	$this->model->fetchreportees();
	}
	function accetRejectRequest(){
		echo $this->view->tmeinfo	=	$this->model->accetRejectRequest();
	}
	function insertReportDetails(){
		echo $this->view->tmeinfo	=	$this->model->insertReportDetails();
	}
	function sendOTP(){
		echo $this->view->tmeinfo	=	$this->model->sendOTP();
	}
	function checkOTP(){
		echo $this->view->tmeinfo	=	$this->model->checkOTP();
	}
	function countRequest(){
		echo $this->view->tmeinfo	=	$this->model->countRequest();
	}
	function insertPenaltyUpdatedOn(){
		echo $this->view->tmeinfo	=	$this->model->insertPenaltyUpdatedOn();
	}
	function getcitylist(){
		echo $this->view->tmeinfo	=	$this->model->getcitylist();
	}
	function getHotData() {
		echo $this->view->tmeinfo	=	$this->model->getHotData();
	}
	function fetchNewBusiness() {
		echo $this->view->tmeinfo	=	$this->model->fetchNewBusiness();
	}
	function fetchRestaurantData() {
		echo $this->view->tmeinfo	=	$this->model->fetchRestaurantData();
	}
	function fetchEcsData() {
		echo $this->view->tmeinfo	=	$this->model->fetchEcsData();
	}
	function fetchExpiredData() {
		echo $this->view->tmeinfo	=	$this->model->fetchExpiredData();
	}
	function fetchProspectData() {
		echo $this->view->tmeinfo	=	$this->model->fetchProspectData();
	}
	function fetchSpecialData(){
		echo $this->view->tmeinfo	=	$this->model->fetchSpecialData();		
	}
	function accountDetRest() {
		echo $this->view->tmeinfo	=	$this->model->accountDetRest();
	}
	function gettmeAllocData() {
		echo $this->view->tmeinfo	=	$this->model->fetchtmeAllocData();
	}
	function fetchReversedRetentionData() {
		echo $this->view->tmeinfo	=	$this->model->fetchReversedRetentionData();
	}
	function fetchNonecsData(){
		echo $this->view->tmeinfo	=	$this->model->fetchNonecsData();		
	}
	function fetchunsoldData(){
		echo $this->view->tmeinfo	=	$this->model->fetchunsoldData();		
	}
	function fetchExpiredDataEcs() {
		echo $this->view->tmeinfo  = $this->model->fetchExpiredDataEcs();
	}
	function fetchExpiredDataNonEcs() {
		echo $this->view->tmeinfo  = $this->model->fetchExpiredDataNonEcs();
	}
	function fetchEcsRequestData(){
		echo $this->view->tmeinfo	=	$this->model->fetchEcsRequestData();		
	}
	function fetchdeliverySystem() {
		echo $this->view->tmeinfo  = $this->model->fetchdeliverySystem();
	}
	function fetchleadComplaints() {
		echo $this->view->tmeinfo	=	$this->model->fetchleadComplaints();
	}
	function fetchjdrrPropectData(){
		echo $this->view->tmeinfo   =   $this->model->fetchjdrrPropectData();
	}
	function fetchReportData() {
		echo $this->view->tmeinfo	=	$this->model->fetchReports();
	}
	function getAllocatedContracts() {
		echo $this->view->tmeinfo	=	$this->model->fetchAllocContracts();
	}
	function fetchPackgaeData() {
		echo $this->view->tmeinfo	=	$this->model->fetchPackgaeData();
	}
	function fetchRetentionData_information() {
		echo $this->view->tmeinfo	=	$this->model->fetchRetentionData_information();
	}
	function fetchRetentionData() {
		echo $this->view->tmeinfo	=	$this->model->fetchRetentionData();
	}
	function fetchJDRatingData() {
		echo $this->view->tmeinfo	=	$this->model->fetchJDRatingData();
	}
	function getCallBackData() {
		echo $this->view->tmeinfo	=	$this->model->getCallBackData();
	}
	function fetchMagazineData() {
		echo $this->view->tmeinfo  = $this->model->fetchMagazineData();
	}
	function checkemployeedeclaration() {
		echo $this->view->response	=	$this->model->checkemployeedeclaration();
	}
	function storeemp() {
		echo $this->view->response	=	$this->model->storeemp();
	}
	function getlineagealldata(){
		echo $this->view->response	=	$this->model->getlineagealldata();
	}
	function getSSOInfo() {
		echo $this->view->tmeinfo	=	$this->model->getSSOInfo();
	}	
	function companyAutoSuggest() {
		echo $this->view->tmeinfo	=	$this->model->companyAutoSuggest();
	}
	function fetchNumberData($empCode='') {
		echo $this->view->tmeinfo	=	$this->model->fetchNumberData($empCode);
	}	
	function fetchDeactivateRestaurantData($empCode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->fetchDeactivateRestaurantData($empCode,$fullPage);
	}
	function fetchChainRestuarantData($empCode,$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->fetchChainRestuarantData($empCode,$fullPage);
	}
	function fetchBounceData(){
		echo $this->view->tmeinfo	=	$this->model->fetchBounceData();		
	}
	function JdrIro(){
		echo $this->view->tmeinfo	=	$this->model->JdrIro();		
	}
	function WebIro(){
		echo $this->view->tmeinfo	=	$this->model->WebIro();		
	}
	function whatsappcalled(){
		echo $this->view->tmeinfo	=	$this->model->whatsappcalled();		
	}
	function fetchBounceECSData(){
		echo $this->view->tmeinfo	=	$this->model->fetchBounceECSData();		
	}
	function fetchInstantECSData(){
		echo $this->view->tmeinfo	=	$this->model->fetchInstantECSData();		
	}
	function fetchDealClosedReport($empCode='',$fullPage='') {
		echo $this->view->tmeinfo	=	$this->model->fetchDealCloseDataReport($empCode,$fullPage);
	}
	function fetchjdrrCourierData(){
		echo $this->view->tmeinfo   =   $this->model->fetchjdrrCourierData();
	}
	function miniBformload() {
		echo $this->view->response	=	$this->model->miniBformload();
	}
	function insertshadowdetails() {
		echo $this->view->response	=	$this->model->insertshadowdetails();
	}
	function ownershipdata() {
		echo $this->view->response	=	$this->model->ownershipdata();
	}
	function fetchAllocEmpDetails() {
		echo $this->view->response	=	$this->model->fetchAllocEmpDetails();
	}
	function docidcreator() {
		echo $this->view->response	=	$this->model->docidcreator();
	}
	function fetchrestaurantdealsoffer() {
		echo $this->view->response	=	$this->model->fetchrestaurantdealsoffer();
	}
	function fetchsuperhotdata() {
		echo $this->view->response	=	$this->model->fetchsuperhotdata();
	}
	function updatesuperhotdata() {
		echo $this->view->response	=	$this->model->updatesuperhotdata();
	}
	function cityautosuggest() {
		echo $this->view->tmeinfo	=	$this->model->cityautosuggest();
	}

}
?>
