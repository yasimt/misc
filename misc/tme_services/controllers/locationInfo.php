<?php

class locationInfo extends Controller{
	
	function __construct(){
		 parent::__construct();
	}
	function get_lat_long(){
		echo $this->view->locationInfo		=	$this->model->get_lat_long();
	}
	function stdcode_master(){
		echo $this->view->locationInfo		=	$this->model->stdcode_master();
	}
	function get_area(){
		echo $this->view->locationInfo		=	$this->model->get_area();
	}
	function street_master_auto(){
		echo $this->view->locationInfo		=	$this->model->street_master_auto();
	}
	function pincode_master(){
		echo $this->view->locationInfo		=	$this->model->pincode_master();
	}
	function unsold_inventoryData(){
		echo $this->view->locationInfo		=	$this->model->unsold_inventoryData();
	}
	function EcsRequestStatusCheck(){
		echo $this->view->locationInfo		=	$this->model->EcsRequestStatusCheck();
	}
	function fetchBudgetInfo($parentid){
		echo $this->view->locationInfo		=	$this->model->fetchBudgetInfo($parentid);
	}
	function getAssocCatSeries() {
		echo $this->view->locationInfo		=	$this->model->getAssocCatSeries();
	}
	function pincode_master_dialer() {
		echo $this->view->locationInfo		=	$this->model->pincode_master_dialer();
	}
}
?>
