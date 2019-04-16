<?php
class campaignInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getBestCampaignInfo() {
		echo $this->view->campaignInfo	=	$this->model->getBestCampaignInfo();
	}
	
	function setBudgetData() {
		echo $this->view->campaignInfo	=	$this->model->setBudgetData();
	}
	
	function getDataBudgetFinal() {
		echo $this->view->campaignInfo	=	$this->model->getDataBudgetFinal();
	}
	
	function getExistingInventory() {
		echo $this->view->campaignInfo	=	$this->model->getExistingInventory();
	}
	
	function getCampaignMaster() {
		echo $this->view->campaignInfo	=	$this->model->getCampaignMaster();
	}
	
	function releaseInventory() {
		echo $this->view->campaignInfo	=	$this->model->releaseInventory();
	}

	function getVersion($data_city ='', $parentid = '', $usercode ='') {
		echo $this->view->campaignInfo	=	$this->model->getVersion($data_city,$parentid,$usercode);
	}

	function getSetBudgetData() {
		echo $this->view->campaignInfo	=	$this->model->getSetBudgetData();
	}
	
	function resetCampaign() {
		echo $this->view->campaignInfo	=	$this->model->resetCampaign();
	}
	function checkFlexiCat() {
		echo $this->view->campaignInfo	=	$this->model->checkFlexiCat();
	}
	function getMinimumBudgetFlexi() {
		echo $this->view->campaignInfo	=	$this->model->getMinimumBudgetFlexi();
	}
	function submit_flexi_value() {
		echo $this->view->campaignInfo	=	$this->model->submit_flexi_value();
	}
}
