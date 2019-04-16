<?php 
/**
* 
*/
class transferInfo extends Controller{
	
	function __construct(){
		# code...
		 parent::__construct();
	}

	function fun_tbl_business_temp_data(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_business_temp_data();
	}

	function fun_tbl_temp_intermediate(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_temp_intermediate();
	}

	function fun_tbl_bid_companymaster_finance(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_bid_companymaster_finance();
	}

	function fun_tbl_smsbid_temp(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_smsbid_temp();
	}

	function fun_tbl_business_temp_enhancements(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_business_temp_enhancements();
	}

	function fun_geocode(){
		echo $this->view->transferInfo		=	$this->model->fun_geocode();
	}
	
	function fun_unapproved_geocode(){
		echo $this->view->transferInfo		=	$this->model->fun_unapproved_geocode();
	}

	function fun_tbl_catspon_temp(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_catspon_temp();
	}
	function fun_tbl_jd_rev_rat(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_jd_rev_rat();
	}
	function fun_get_iro_appointment(){
		echo $this->view->transferInfo		=	$this->model->fun_get_iro_appointment();
	}
	function fun_tbl_alt_address(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_alt_address();
	}
	function nationallistingAllocation(){
		echo $this->view->transferInfo		=	$this->model->nationallistingAllocation();
	}
	// function to update tbl_companymaster_extradetails_shadow
	function fun_tbl_alt_address_update(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_alt_address_update();
	}
	function fun_tbl_ecs_dealclose_pending(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_ecs_dealclose_pending();
	}
	#####  Currently This table is Not in use tbl_business_temp_category  ##### 
	/*function fun_tbl_business_temp_category(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_business_temp_category();
	}*/
	/*function fun_tbl_ICICI_TaggedData(){
		echo $this->view->transferInfo		=	$this->model->fun_tbl_ICICI_TaggedData();
	}*/
	/*function tbl_product_quotes_shadow(){
		echo $this->view->transferInfo		=	$this->model->tbl_product_quotes_shadow();
	}*/
}
?>
