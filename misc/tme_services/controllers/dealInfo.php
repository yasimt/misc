<?php
class dealInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getPublishDeals() {
		echo $this->view->dealInfo	=	$this->model->getPublishDeals();
	}
	function updateJdDealDetails() {
		echo $this->view->dealInfo	=	$this->model->updateJdDealDetails();
	}
	function getJdCouponTemplates() {
		echo $this->view->dealInfo	=	$this->model->getJdCouponTemplates();
	}
	function getJdCustomTemplates() {
		echo $this->view->dealInfo	=	$this->model->getJdCustomTemplates();
	}
	function addJdDealInformation() {
		echo $this->view->dealInfo	=	$this->model->addJdDealInformation();
	}
	function addJdCustomTemplate() {
		echo $this->view->dealInfo	=	$this->model->addJdCustomTemplate();
	}
	function editExistingHighlights() {
		echo $this->view->dealInfo	=	$this->model->editExistingHighlights();
	}
	function editExistingTerms() {
		echo $this->view->dealInfo	=	$this->model->editExistingTerms();
	}
	function publishJdDeal() {
		echo $this->view->dealInfo	=	$this->model->publishJdDeal();
	}
	function getJdDeals() {
		echo $this->view->dealInfo	=	$this->model->getJdDeals();
	}
	function updateJdDealInformation() {
		echo $this->view->dealInfo	=	$this->model->updateJdDealInformation();
	}
	function getNatCatIdFrmParentId() {
		echo $this->view->dealInfo	=	$this->model->getNatCatIdFrmParentId();
	}
	function getJdDealTemplates() {
		echo $this->view->dealInfo	=	$this->model->getJdDealTemplates();
	}
	function updateJdCustomTemplate() {
		echo $this->view->dealInfo	=	$this->model->updateJdCustomTemplate();
	}
	function updatecmdealstatus() {
		echo $this->view->dealInfo	=	$this->model->updatecmdealstatus();
	}
	function updateCmDealInformation() {
		echo $this->view->dealInfo	=	$this->model->updateCmDealInformation();
	}
	function fetchDealAutoSuggest() {
		echo $this->view->dealInfo	=	$this->model->fetchDealAutoSuggest();
	}
	function addGenioDeal() {
		echo $this->view->dealInfo	=	$this->model->addGenioDeal();
	}
	function updateGenioDeal() {
		echo $this->view->dealInfo	=	$this->model->updateGenioDeal();
	}
	function updatePublishedDeal() {
		echo $this->view->dealInfo	=	$this->model->updatePublishedDeal();
	}
	function updatePublishDealStatus() {
		echo $this->view->dealInfo	=	$this->model->updatePublishDealStatus();
	}
	function getDealOfferType() {
		echo $this->view->dealInfo	=	$this->model->getDealOfferType();
	}
	function generateAccesscode() {
		echo $this->view->dealInfo	=	$this->model->generateAccesscode();
	}
	function accessCodeGenNew() {
		echo $this->view->dealInfo	=	$this->model->accessCodeGenNew();
	}
	function insertAccesscode() {
		echo $this->view->dealInfo	=	$this->model->insertAccesscode();
	}
	function checkAccesscode() {
		echo $this->view->dealInfo	=	$this->model->checkAccesscode();
	}
	function getAccessCodeDetials() {
		echo $this->view->dealInfo	=	$this->model->getAccessCodeDetials();
	}
}