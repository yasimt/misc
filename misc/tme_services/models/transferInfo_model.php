<?php 
/**
* 
			$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds
			$dbObjme		=	new DB($this->db['db_idc']);//online_regis_mumbai
*/

class TransferInfo_Model extends Model{
	function __construct(){
		 parent::__construct();
		 GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
	}

	####  Creating function for transferdataconClass.php #### 
	####  Following functions can be used as an API's for further use ####
	#### Created by Apoorv Agrawal ####
	#### Date: 10/02/2016 ####

	/* 	Function to get tbl_business_temp_data based on parentId as parameter
		Call this API on load of Page load of mktgJrPage.php
	* 	Table tbl_business_temp_data is used to select category / categories
	*/

	public function fun_tbl_business_temp_data(){
		//echo "HERE";die();
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds
			
			if(MONGOUSER == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid']   = $parentId;
				$mongo_inputs['data_city']  = SERVER_CITY;
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table']      = "tbl_business_temp_data";
				$mongo_inputs['fields']     = "contractid,categories,catIds,htmldump,slogan,categories_list,pages,bid_day_sel,bid_timing,threshold,autobid,catSelected,uId,mainattr,facility,companyName,avgAmt,percentage, comp_deduction_amt,thresholdform,thresholdType,original_catids,authorised_categories,bid_lead_num,bid_type,nationalcatIds,parentname,bid_led_num_year,thresholdPercnt,TotThresh,thresWeekSup,thresDailySup,thresMonthSup,bid_lead_num_sys,significance";
				$row = $this->mongo_obj->getData($mongo_inputs);
				$num = count($row);
			}	
			else
			{
			
			
				$select_temp_data	= 	"SELECT contractid, categories, catIds, htmldump, slogan, categories_list, pages, bid_day_sel, bid_timing, threshold, autobid, catSelected, uId, mainattr, facility, companyName, avgAmt, percentage, comp_deduction_amt, thresholdform, thresholdType, original_catids, authorised_categories, bid_lead_num, bid_type, nationalcatIds, parentname, bid_led_num_year, thresholdPercnt,TotThresh, thresWeekSup, thresDailySup, thresMonthSup, bid_lead_num_sys, significance FROM tbl_business_temp_data  WHERE contractid  = '".$parentId."'";
				$temp_data_con 	=	$db_tme->query($select_temp_data);
				$num			=	$db_tme->numRows($temp_data_con);
				$row 			= $db_tme->fetchData($temp_data_con);
			}
			if($num >0){
				$retArr['data']			=	$row;
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		return json_encode($retArr);exit;
	}
	/* get All Data from Table tbl_temp_intermediate*/
	public function fun_tbl_temp_intermediate(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme	=	new DB($this->db['db_tme']);//tme_jds
			if(MONGOUSER == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid']   = $parentId;
				$mongo_inputs['data_city']  = SERVER_CITY;
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table']      = "tbl_temp_intermediate";
				$mongo_inputs['fields']     = "parentid,contractid,contract_calltype,displayType,deactivate,temp_deactive_start,temp_deactive_end,deactflg,freez,mask,reason_id,add_infotxt,narration,mainsource,subsource,datesource,name_code,txtTE,txtM,txtME,callconnect,callconnectid,virtualNumber,virtual_mapped_number,actMode,nonpaid,c2c,c2s,hiddenCon,cpc,web,tme_mobile,tme_email,tme_code,facility_flag,empcode,employeeCode,txtEmp,reason_text,assignTmeCode,blockforvirtual,guarantee,guarantee_reason,generatexml,source_parentid,source_id,paid_match,contracts,significance,bronze,exclusive,iscalculated";
				$row = $this->mongo_obj->getData($mongo_inputs);
				$num = count($row);
			}
			else
			{
				$select_temp_intermediate	= 	"SELECT parentid,contractid,contract_calltype,displayType,deactivate,temp_deactive_start,temp_deactive_end,deactflg,freez,mask,reason_id,add_infotxt,narration,mainsource,subsource,datesource,name_code,txtTE,txtM,txtME,callconnect,callconnectid,virtualNumber,virtual_mapped_number,actMode,nonpaid,c2c,c2s,hiddenCon,cpc,web,tme_mobile,tme_email,tme_code,facility_flag,empcode,employeeCode,txtEmp,reason_text,assignTmeCode,blockforvirtual,guarantee,guarantee_reason,generatexml,source_parentid,source_id,paid_match,contracts,significance,bronze,exclusive,iscalculated FROM tbl_temp_intermediate  WHERE parentid  = '".$parentId."'";
				
				$temp_intermediate_con	= $db_tme->query($select_temp_intermediate);
				$num					= $db_tme->numRows($temp_intermediate_con);
				$row 					= $db_tme->fetchData($temp_intermediate_con);
			}
			if($num >0){
				$retArr['data']			=	$row;
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;	
	}
	// Function for  get All Data from Table tbl_companymaster_finance_temp
	public function fun_tbl_bid_companymaster_finance(){	
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds	
			$select_temp_bid 	= 	"SELECT nationalid,sphinx_id,regionid,bid_day_sel,parentid,campaignid,budget,duration,version,balance,start_date,end_date,smartlisting_flag,exclusivelisting_tag,daily_threshold, recalculate_flag, searchcriteria FROM tbl_companymaster_finance_temp WHERE parentid = '".$parentId."'";
			$temp_bid_con	= 	$db_tme->query($select_temp_bid);
			$num			=	$db_tme->numRows($temp_bid_con);
			if($num >0){
				while($row 	=	$db_tme->fetchData($temp_bid_con)){
					$retArr['data'][]		=	$row;
				}
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;	
	}

	// Function for  get All Data from tbl_smsbid_temp
	public function fun_tbl_smsbid_temp(){									
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds	
			$select_temp_sms	= 	"SELECT BContractId, TContractId, bid_value,promo_txt, rflag,autoid, Bid, Tid, daily_sms, active_time, new_promo, activeMap FROM tbl_smsbid_temp WHERE Bcontractid  = '".$parentId."'";
			$temp_sms_con	= 	$db_tme->query($select_temp_sms);
			$num			=	$db_tme->numRows($temp_sms_con);
			if($num >0){
				while($row 	=	$db_tme->fetchData($temp_sms_con)){
					$retArr['data'][]		=	$row;
				}
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;								
	}
	// Function for getting all data from tbl_business_temp_enhancements
	public function fun_tbl_business_temp_enhancements(){										
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme				=	new DB($this->db['db_tme']);//tme_jds	
			$temp_enhancements 		= 	"SELECT contractid, video_facility,logo_facility,catalog_facility FROM tbl_business_temp_enhancements  WHERE contractid  = '".$parentId."'";
			$temp_enhancements_con	= 	$db_tme->query($temp_enhancements);
			$num					=	$db_tme->numRows($temp_enhancements_con);
			if($num >0){
				$retArr['data']		=	$db_tme->fetchData($temp_enhancements_con);
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;							
	}
	/* FUNCTION TO GET ALL DATA FROM tbl_compgeocodes_shadow*/
	public function fun_geocode(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme			=	new DB($this->db['db_tme']);//tme_jds	
			$select_geocodes 		=	 "SELECT parentid,latitude_area,longitude_area, latitude_pincode, longitude_pincode,latitude_street,longitude_street,					latitude_bldg,longitude_bldg, latitude_final, longitude_final,logdatetime,mappedby, latitude_landmark, 					longitude_landmark FROM tbl_compgeocodes_shadow WHERE parentid  = '".$parentId."'";
			$select_geocodes_con		= 	$db_tme->query($select_geocodes);
			$num				=	$db_tme->numRows($select_geocodes_con);
			if($num >0){
				$retArr['data']		=	$db_tme->fetchData($select_geocodes_con);
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}
	/* function to get all data from unapproved_building_geocodes*/	
	public function fun_unapproved_geocode(){	
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme				=	new DB($this->db['db_tme']);//tme_jds	
			$select_geocodes_unapproved	= 	"SELECT parentid,username,userid,temp_latitude,temp_longitude, approved_latitude, approved_longitude,temp_tagging, approval_flag, date FROM unapproved_building_geocodes WHERE parentid  = '".$parentId."'";
			$geocodes_unapproved_con		= 	$db_tme->query($select_geocodes_unapproved);
			$num					=	$db_tme->numRows($geocodes_unapproved_con);
			if($num >0){
				$retArr['data']		=	$db_tme->fetchData($geocodes_unapproved_con);
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}					
		echo json_encode($retArr);exit;
	}
	/*Function to get all data from tbl_catspon_temp*/
	public function fun_tbl_catspon_temp (){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds	
			$select_catspon 	= 	"SELECT parentid, budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,					campaign_type,campaign_name FROM tbl_catspon_temp WHERE parentid = '".$parentId."'";
		                $select_catspon_con 	= 	$db_tme->query($select_catspon);
		                $num			=	$db_tme->numRows($select_catspon_con);
		                if($num >0){
			               while($temp_catspon 	=	$db_tme->fetchData($select_catspon_con)) {
			               	$retArr['data'][]    	=    	$temp_catspon;
			               }
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}			
		}
		echo json_encode($retArr);exit;	
	}
	/*function to get all data from tbl_jd_reviewrating_contracts and tbl_jdratings_sales*/
    public function fun_tbl_jd_rev_rat(){
        $retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_local']);//tme_jds	
			$sel_rev_rat_cont	=	"SELECT * FROM d_jds.tbl_jd_reviewrating_contracts WHERE parentid = '".$parentId."'";
			$rev_rat_cont_con	=	$db_tme->query($sel_rev_rat_cont);
			$num_rev_rat		=	$db_tme->numRows($rev_rat_cont_con);
			if($num_rev_rat	> 0){
				$sel_jdratings_sales	=	"SELECT compname,docid,city,contact_person,contact_number,paid,rating,no_of_rating,company_callcount,tmecode,				done_flag,block_flag,parentid FROM d_jds.tbl_jdratings_sales WHERE parentid = '".$parentId."'";
				$sel_jdratings_con	=	$db_tme->query($sel_jdratings_sales);
				$num_rev_rat		=	$db_tme->numRows($sel_jdratings_con);
				if($num_rev_rat>0){
					$retArr['data']		=	$db_tme->fetchData($sel_jdratings_con);
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Data Found";
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus'] 	=	"Data Not Found";
				}			
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}  

	function fun_get_iro_appointment(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_iro		=	new DB($this->db['db_iro']);
			$query		=	"SELECT parentid,ironame,irocode,irocode1,irocode2,tmecode,appointment_date FROM tbl_appointment_iro WHERE parentid =			'".$parentId."'";
			$query_con	=	$db_iro->query($query);
			$num_iro	=	$db_iro->numRows($query_con);
			if($num_iro> 0)	{
				$retArr['data']		=	$db_iro->fetchData($query_con);
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}            
	
	/*
		function to update tbl_companymaster_extradetails_altaddress_shadow table
	*/
	public function fun_tbl_alt_address_update(){
		$retArr 	=	array();
		//header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
			$actDate	=	$params['actDate'];
			$meCode		=	$params['meCode'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
			$actDate	=	$_REQUEST['actDate'];
			$meCode		=	$_REQUEST['meCode'];
		}
		if(($parentId=="" || empty($parentId)) || ($actDate=="" || empty($actDate)) ){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Check the parameters";
			
		}else{			
			$db_tme			=	new DB($this->db['db_tme']);//tme_jds	
			$updt_extra_Alt_shadow_q 	=	"UPDATE tbl_companymaster_extradetails_altaddress_shadow set actionDate = '".$actDate."',meCode = '".$meCode."' 				      WHERE parentid = '".$parentId."'";
			$updateAlt_con 		= 	$db_tme->query($updt_extra_Alt_shadow_q);
			if($updateAlt_con){
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"updation Done";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"updation Failed";
			}
		}
		echo json_encode($retArr);exit;
	}
	/*
		function  getting values from tbl_companymaster_extradetails_altaddress_shadow
	*/
	public function fun_tbl_alt_address(){
		$retArr 	=	array();
		//header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if(($parentId=="" || empty($parentId))){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId not found";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds		
			$select_temp_bid 	= 	"SELECT companyname,parentid,country,state,city,area,building_name,street,landmark,pincode,full_address,stdcode,			  		country_id,state_id,city_id,insertdate,tmeCode,actionDate,meCode FROM 									tbl_companymaster_extradetails_altaddress_shadow WHERE parentid = '".$parentId."'"; 
			$temp_bid_con 	= 	$db_tme->query($select_temp_bid);
			$num			=	$db_tme->numRows($temp_bid_con);
			if($num>0){
				while($row_temp_bid_nu=$db_tme->fetchData($temp_bid_con)){
					$retArr['data'][]			=	$row_temp_bid_nu;
				}
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}


	public function nationallistingAllocation(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if(($parentId=="" || empty($parentId))){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Check the parameters";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds	
            $sql_national_listing 	= 	"SELECT parentid,Category_city,Category_nationalid,TotalCategoryWeight,totalcityweight,contractCity,ContractStartDate,				ContractTenure,dailyContribution,WebdailyContribution,latitude,longitude,iroCard,state_zone,lastupdate,short_url FROM tbl_national_listing_temp WHERE parentid='".$parentId."'";	
			$national_listing_con 	= 	$db_tme->query($sql_national_listing);//tme_jds
			$national_listing_num	=	$db_tme->numRows($national_listing_con);
            if($national_listing_con && $national_listing_num){
				$row_sel 	=	 $db_tme->fetchData($national_listing_con);
				$dbObjme	=	new DB($this->db['db_idc']);//online_regis_mumbai
				$insert_sql 	=	"INSERT INTO tbl_national_listing_temp 
								SET
								parentid 			='".$row_sel['parentid']."',
								Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
								Category_nationalid		='".$row_sel['Category_nationalid']."',
								TotalCategoryWeight	    	='".$row_sel['TotalCategoryWeight']."',
								totalcityweight			='".$row_sel['totalcityweight']."',
								contractCity			='".addslashes(stripslashes($row_sel['contractCity']))."',
								ContractStartDate		='".$row_sel['ContractStartDate']."',
								ContractTenure		='".$row_sel['ContractTenure']."',
								dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
								WebdailyContribution		='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
								latitude			='".$row_sel['latitude']."',
								longitude			='".$row_sel['longitude']."',
								iroCard				='".$row_sel['iroCard']."',
								state_zone			='".$row_sel['state_zone']."',
								lastupdate			='".$row_sel['lastupdate']."',
								short_url			='".$row_sel['short_url']."'
								
							ON DUPLICATE KEY UPDATE
								
								Category_city			=	'".addslashes(stripslashes($row_sel['Category_city']))."',
								Category_nationalid		=	'".$row_sel['Category_nationalid']."',
								TotalCategoryWeight		=	'".$row_sel['TotalCategoryWeight']."',
								totalcityweight			=	'".$row_sel['totalcityweight']."',
								contractCity			=	'".addslashes(stripslashes($row_sel['contractCity']))."',
								ContractStartDate		=	'".$row_sel['ContractStartDate']."',
								ContractTenure		=	'".$row_sel['ContractTenure']."',
								dailyContribution		=	'".addslashes(stripslashes($row_sel['dailyContribution']))."',
								WebdailyContribution		=	'".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
								latitude				=	'".$row_sel['latitude']."',
								longitude			=	'".$row_sel['longitude']."',
								iroCard				=	'".$row_sel['iroCard']."',
								state_zone			=	'".$row_sel['state_zone']."',
								lastupdate			=	'".$row_sel['lastupdate']."',
								short_url			=	'".$row_sel['short_url']."'";
				$res = $dbObjme->query($insert_sql);	
				if($res)	{
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Insertion done";
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus'] 	=	"Insertion aborted";
				}
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"DATA NOT FOUND";
			}
		}
		echo json_encode($retArr);
       	}
       	
       	
  	public function fun_tbl_ecs_dealclose_pending(){
		$retArr 	=	array();
		//header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params 	=	$_REQUEST;
		}else{
			$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		//~ return json_encode($params);die;
		if(($params['parentId']=="" || empty($params['parentId']))){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId not found";
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds		
			$select_ecs_details 	= 	"SELECT * FROM tme_jds.tbl_ecs_dealclose_pending WHERE parentid = '".$params['parentId']."' AND EmpCode='".$params['ucode']."'";
			$select_ecs_details_res 	= 	$db_tme->query($select_ecs_details);
			$select_ecs_details_num			=	$db_tme->numRows($select_ecs_details_res);
			if($select_ecs_details_num>0){
				while($select_ecs_details_data=$db_tme->fetchData($select_ecs_details_res)){
					$retArr['data']			=	$select_ecs_details_data;
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Data Found";
				}
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);
	}
	
	

       	#####  Following Tables in the functions are not in use   ##### 

       	/* function to get data from tbl_business_temp_category*/
	#####  Currently This table is Not in use tbl_business_temp_category  ##### 
	/*public function fun_tbl_business_temp_category(){		
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme	=	new DB($this->db['db_tme']);//tme_jds	
			$select_temp_category 	= 	"SELECT contractid,sequence,categorythread,category_selected,product_selected,brand_selected,postdata, url, product, parent, brand FROM tbl_business_temp_category  WHERE contractid  = '".$parentId."'";
			$temp_category_con 		= 	$db_tme->query($select_temp_category);
			$num				=	$db_tme->numRows($temp_category_con);
			if($num >0){
				$retArr['data']		=	$db_tme->fetchData($temp_category_con);
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}*/
	/*public function fun_tbl_ICICI_TaggedData($jrcode){
		
		$select_ICICI_data= "SELECT * from tme_jds.ICICITaggedData where parentid = '".$this->parentid."' ORDER BY updatedOn DESC LIMIT 1";
			
		$fetch_ICICI_data = $this->conn_tme->query_sql($select_ICICI_data);
		$row_ICICI_data=mysql_fetch_assoc($fetch_ICICI_data);
		$row_ICICI_data['jrcode']=$jrcode;
		return $row_ICICI_data;
	}*/
	// Function for tbl_product_quotes_shadow

	// NOT IN USE
	/*public function tbl_product_quotes_shadow(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$parentId 	=	$params['parentId'];
		}else{
			$parentId	=	$_REQUEST['parentId'];
		}
		if($parentId=="" || empty($parentId)){

			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"parentId Not sent";
			
		}else{
			$db_tme		=	new DB($this->db['db_tme']);//tme_jds	
			$select_product_qu	= 	 "SELECT 	docid,
									regionid,
									parentid,
									product_id,
									product_name,
									product_displayname,
									catid,
									catname,
									brand_id,
									brand_name,
									quotes,
									quotes_remarks,
									product_price,
									discount_percent,
									expiry_date,
									updatedBy,
									updatedOn,
									backenduptdate,
									active_flg
							FROM tbl_product_quotes_shadow WHERE parentid = '".$parentId."'";
			$product_qu_con 	= 	$db_tme->query($select_product_qu);
			$num			=	$db_tme->numRows($product_qu_con);
			if($num>0){
				while($row_temp_bid_nu=$db_tme->fetchData($product_qu_con)){
					$retArr['data'][]		=	$row_temp_bid_nu;
				}
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Found";
			}
		}
		echo json_encode($retArr);exit;
	}       */   

}
