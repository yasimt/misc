<?php
	class lineageInfo extends Controller {
	
			function __construct() {
				parent::__construct();
			}
			
			function getLineage(){
				echo $this->view->lineageinfo	=	$this->model->getLineage();
			}
			
			function getcitylist(){
				echo $this->view->lineageinfo	=	$this->model->getcitylist();
			}
			
			function insertlineageDetails(){
				echo $this->view->lineageinfo	=	$this->model->insertlineageDetails();
			}
			function fetchreportees(){
				echo $this->view->lineageinfo	=	$this->model->fetchreportees();
			}
			function accetRejectRequest(){
				echo $this->view->lineageinfo	=	$this->model->accetRejectRequest();
			}
			function insertReportDetails(){
				echo $this->view->lineageinfo	=	$this->model->insertReportDetails();
			}
			function sendOTP(){
				echo $this->view->lineageinfo	=	$this->model->sendOTP();
			}
			function checkOTP(){
				echo $this->view->lineageinfo	=	$this->model->checkOTP();
			}
			function countRequest(){
				echo $this->view->lineageinfo	=	$this->model->countRequest();
			}
			function checkUpdatedOn(){
				echo $this->view->lineageinfo	=	$this->model->checkUpdatedOn();
			}
			
			function insertPenaltyUpdatedOn(){
				echo $this->view->lineageinfo	=	$this->model->insertPenaltyUpdatedOn();
			}
			function getlineagealldata(){
				echo $this->view->lineageinfo	=	$this->model->getlineagealldata();
			}
			
	}
?>
