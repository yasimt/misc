<?php
class categoryInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getCategoryInfo() {
		echo $this->view->categoryInfo	=	$this->model->getCategoryInfo();
	}
	
	function getCatAutoSuggest() {
		echo $this->view->categoryInfo	=	$this->model->getCatAutoSuggest();
	}
	
	function getCatData() {
		echo $this->view->categoryInfo	=	$this->model->getCatData();
	}
	
	function getExistingCatsContract() {
		echo $this->view->categoryInfo	=	$this->model->getExistingCatsContract();
	}
	
	function submitCategories() {
		echo $this->view->categoryInfo	=	$this->model->submitCategories();
	}
	
	function catPreviewData() {
		echo $this->view->categoryInfo	=	$this->model->catPreviewData();
	}
	
	function findMultiParentage() {
		echo $this->view->categoryInfo	=	$this->model->findMultiParentage();
	}
	
	function sendCatsForModeration() {
		echo $this->view->categoryInfo	=	$this->model->sendCatsForModeration();
	}
	
	function checkCatRestriction() {
		echo $this->view->categoryInfo	=	$this->model->checkCatRestriction();
	}
	
	function submitCatPreview() {
		echo $this->view->categoryInfo	=	$this->model->submitCatPreview();
	}
	
	function searchPlusCampFinder() {
		echo $this->view->categoryInfo	=	$this->model->searchPlusCampFinder();
	}
	
	function docHospRedirectCheck() {
		echo $this->view->categoryInfo	=	$this->model->docHospRedirectCheck();
	}
	
	function othersVerticalRedirect() {
		echo $this->view->categoryInfo	=	$this->model->othersVerticalRedirect();
	}
	
	function categoryResetAPI() {
		echo $this->view->categoryInfo	=	$this->model->categoryResetAPI();
	}
	function check_attribute_present(){
		echo $this->view->categoryInfo	=	$this->model->check_attribute_present();
	}
	function attributesPage() {
		echo $this->view->categoryInfo	=	$this->model->attributesPage();
	}
	
	function updateAttributes() {
		echo $this->view->categoryInfo	=	$this->model->updateAttributes();
	}
	function getnationalflag() {
		echo $this->view->categoryInfo	=	$this->model->getnationalflag();
	}
	function fetchtempdatanational() {
		echo $this->view->categoryInfo	=	$this->model->fetchtempdatanational();
	}
	function calcupdatedatanational() {
		echo $this->view->categoryInfo	=	$this->model->calcupdatedatanational();
	}
	function removeLocalforNational() {
		echo $this->view->categoryInfo	=	$this->model->removeLocalforNational();
	}
	function submitRelevantCat() {
		echo $this->view->categoryInfo	=	$this->model->submitRelevantCat();
	}
	
	function getPopularCat() {
		echo $this->view->categoryInfo	=	$this->model->getPopularCat();
	}
	
	function fetchEditListingData() {
		echo $this->view->categoryInfo	=	$this->model->fetchEditListingData();
	}
	function fetchEditListingEntry() {
		echo $this->view->categoryInfo	=	$this->model->fetchEditListingEntry();
	}
	function isPhoneSearchCampaign() {
		echo $this->view->categoryInfo	=	$this->model->isPhoneSearchCampaign();
	}
	function categoryInstantLive() {
		echo $this->view->categoryInfo	=	$this->model->categoryInstantLive();
	}
	function save_dc_cat() {
		echo $this->view->categoryInfo	=	$this->model->save_dc_cat();
	}
	function docVerticalCheck() {
		echo $this->view->categoryInfo	=	$this->model->docVerticalCheck();
	}
}
