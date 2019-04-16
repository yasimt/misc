<?php
	class searchDet extends Controller {
	
			function __construct() {
				parent::__construct();
			}
			
			function getcompanydetails(){
				echo $this->view->searchDet	=	$this->model->getcompanydetails();
			}
			
			function getallinfo(){
				echo $this->view->searchDet	=	$this->model->getallinfo();
			}
			
			function getCampaignNames(){
				echo $this->view->searchDet	=	$this->model->getCampaignNames();
			}
			
			function getDispositionNames(){
				echo $this->view->searchDet	=	$this->model->getDispositionNames();
			}
			function getEmpAssignments(){
				echo $this->view->searchDet	=	$this->model->getEmpAssignments();
			}
			function getInstrumentDet(){
				echo $this->view->searchDet	=	$this->model->getInstrumentDet();
			}
			function getManagerDetails(){
				echo $this->view->searchDet	=	$this->model->getManagerDetails();
			}
			function getAllEmpAssignments(){
				echo $this->view->searchDet	=	$this->model->getAllEmpAssignments();
			}

	}
?>
