<?php
class category_sendinfo_class extends DB
{
	var $conn_iro     = null;
	var $conn_fnc     = null;
	var $params  	  = null;
	var $dataservers  = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var $parentid	  = null;
	var $data_city	  = null;
	var $oldcatid_str = null;

	function __construct($params)
	{
		$parentid 	    = trim($params['parentid']);
		$data_city      = trim($params['data_city']); 
		$oldcatid_str   = trim($params['national_catidlineage_search']); 
		$oldhotcategory = trim($params['hotcategory']);
		$error_found = 0;
		if(trim($parentid)=='')
		{
		    $message = "Parentid is blank.";
		    $error_found = 1;
		    echo json_encode($this->sendResponse($message));
		}
		if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			$error_found = 1;
			echo json_encode($this->sendResponse($message));
		}       	
		if(trim($oldcatid_str)=='')
		{
			$message = "national_catidlineage_search is blank.";
			$error_found = 1;
			echo json_encode($this->sendResponse($message));
		}
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj 	= new companyClass();
		
		
		$this->parentid  	  = $parentid;
		$this->data_city 	  = $data_city;
		$this->oldcatid_str   = $oldcatid_str;
		$this->oldhotcategory = $oldhotcategory;
		$this->error_found  	  = $error_found;
		$this->setServers();
	}

	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');	
		$this->conn_iro = $db[$conn_city]['iro']['master'];    // 67.213 - db_iro
		$this->conn_fnc = $db[$conn_city]['fin']['master'];   // 67.215 db_finance	 		
	}	

	function sendCatInfo(){
		
		if($this->error_found == 1){
			return;
		}

		$imgParams = array();
		$extradetails_info = $this->getExtraDetailsInfo();

		$oldcatid_arr = array();
		$newcatid_arr = array();

		if(count($extradetails_info)>0){
			
			$oldcatid_arr = explode("/,/",trim($this->oldcatid_str,"/"));
			$oldcatid_arr = array_filter($oldcatid_arr);
			$oldcatid_arr = $this->getValidCategories($oldcatid_arr);
			
			$newcatid_str = trim($extradetails_info['newcatid']);
			$newcatid_arr = explode("/,/",trim($newcatid_str,"/"));
			$newcatid_arr = array_filter($newcatid_arr);
			$newcatid_arr = $this->getValidCategories($newcatid_arr);

			$first_diff = array();
			$first_diff = array_diff($oldcatid_arr,$newcatid_arr);
			
			$second_diff = array();
			$second_diff = array_diff($newcatid_arr,$oldcatid_arr);
			
			$diff = 0;
			if(count($first_diff)>0 || count($second_diff)>0){
				$diff =  1;
			}
			
			$old_hotcat = trim($this->oldhotcategory,"/");
			$old_hotcat	= intval($old_hotcat);
						
			$new_hotcat  = trim($extradetails_info['newhotcategory'],"/");
			$new_hotcat  = intval($new_hotcat);
			$hotcat_name = $this->getCatInfo($new_hotcat);
			
			if($old_hotcat != $new_hotcat){
				$diff =  1;
			}
			
			if($diff == 1){
				$imgParams['rating_review_count'] = 0;

				$docid_str 					                = $this->findDocidVal();
				$gendata                                  	= $this->getGeneralInfoDetails();
				$balVal                                     = $this->getCompanyMaster();
				$imgParams['parentid']		                = $this->parentid;
				$imgParams['docid']			                = $docid_str;
				$imgParams['data_city'] 	                = $this->data_city;
				$imgParams['national_catidlineage_search'] 	= implode(",",$newcatid_arr);
				$imgParams['companyname'] 	                = $gendata['companyname'];
				$imgParams['city'] 			                = $gendata['city'];
				$checksum 					                = md5($docid_str.'AKSTEYU946375');
				$imgParams['checksum'] 		                = $checksum;
				$imgParams['paid_status']                   = $balVal;
				$imgParams['company_call_count']            = $gendata['company_callcnt'];
				$imgParams['area']                          = $gendata['area'];
				$imgParams['modified_at']                   = date("Y-m-d H:i:s");
				$imgParams['rating_review_count']           = $extradetails_info['averagerating'];
				$imgParams['hotcategory']                   = $hotcat_name;
				if($imgParams['rating_review_count'] == '')
				{	
					$imgParams['rating_review_count'] = 0;
				}

				$imgurl = "http://192.168.17.152/api/update_default_img_dealclose.php";
				$imgres = $this->makeCurlCallPost($imgurl,$imgParams);
			}			
		}
	}

	public function getExtraDetailsInfo(){
	   $extra_details_arr = array();
	   $sql = "SELECT national_catidlineage_search as newcatid ,averagerating,hotcategory as newhotcategory FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
	   //$sql_rs = parent::execQuery($sql, $this->conn_iro);
	   $comp_params = array();
	   $comp_params['data_city'] 	= $this->data_city;
	   $comp_params['table'] 		= 'extra_det_id';		
	   $comp_params['parentid'] 	= $this->parentid;
	   $comp_params['fields']		= 'national_catidlineage_search,averagerating,hotcategory';
	   $comp_params['action']		= 'fetchdata';
	   $comp_params['page']			= 'category_sendinfo_class';

	   $comp_api_arr	= array();
	   $comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
	   if($comp_api_res!=''){
		   $comp_api_arr 	= json_decode($comp_api_res,TRUE);
	   }
	   
	   if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
	   {
		   	$row_averagerating 	= $comp_api_arr['results']['data'][$this->parentid];
		   	$extra_details_arr['newcatid']        = $row_averagerating['national_catidlineage_search'];
			$extra_details_arr['averagerating']   = $row_averagerating['averagerating'];
			$extra_details_arr['newhotcategory']  = $row_averagerating['hotcategory'];			
	   }

		/* if($sql_rs && parent::numRows($sql_rs)>0){
			$row_averagerating = parent::fetchData($sql_rs);
			
			$extra_details_arr['newcatid']        = $row_averagerating['newcatid'];
			$extra_details_arr['averagerating']   = $row_averagerating['averagerating'];
			$extra_details_arr['newhotcategory']  = $row_averagerating['newhotcategory'];
		} */
		return $extra_details_arr;	
	}
	  
	public function getValidCategories($new_value)
	{
		$final_catids_arr = array();
		if((!empty($new_value)) && (count($new_value) >0))
		{
		  	foreach($new_value as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
		     		$final_catids_arr[]	= $final_catid;
				}
		 	}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
			return $final_catids_arr;	
	}

	public function findDocidVal(){
		$sqlDocid  = "SELECT docid FROM tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$resDocid  = parent::execQuery($sqlDocid, $this->conn_iro);
		$row_docid = parent::fetchData($resDocid);
		return trim($row_docid['docid']);
	}


	public function getCompanyMaster(){
		$balflag = 0;
		$sqlcomp = "SELECT balance FROM tbl_companymaster_finance WHERE parentid = '".$parentid."' AND balance > 0 LIMIT 1";	
		$rescomp = parent::execQuery($sqlcomp,$this->conn_fnc);
		if($rescomp && parent::numRows($rescomp)){
			$balflag = 1;
		}  
		return $balflag;

	}

	public function getGeneralInfoDetails(){
		$sqlcallcnt = "SELECT companyname,company_callcnt,city,area FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		/* $rescallcnt  = parent::execQuery($sqlcallcnt, $this->conn_iro);
		$row_callcnt = parent::fetchData($rescallcnt); */

		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'companyname,company_callcnt,city,area';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'category_sendinfo_class';

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
		{
			$row_callcnt 	= $comp_api_arr['results']['data'][$this->parentid];			
		}
		$returnData  = array();
		$returnData['companyname'] 	    = $row_callcnt['companyname'];
		$returnData['city'] 			= $row_callcnt['city'];
		$returnData['company_callcnt'] 	= $row_callcnt['company_callcnt'];
		$returnData['area'] 	        = $row_callcnt['area'];
		
		return $returnData;
	}

	public function getCatInfo($catid){
		GLOBAL $db;
		$category_name_str = '';
		//$sqlcategory = "SELECT category_name FROM d_jds.tbl_categorymaster_generalinfo WHERE catid = '".$catid."'";
		//$rescategory = parent::execQuery($sqlcategory, $this->conn_iro);
		$cat_params = array();
		$cat_params['page'] 	='category_sendinfo_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'category_name';

		$where_arr  	=	array();
		$where_arr['catid']		= $catid;		
		$cat_params['where']	= json_encode($where_arr);
		if($catid!='' && $catid!='0'){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if(count($cat_res_arr['results'])>0){
			$row_category = $cat_res_arr['results']['0'];
			$category_name_str = trim($row_category['category_name']);
		}
		return $category_name_str;
	}

	public function makeCurlCallPost($curlurl,$input_arr){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $input_arr);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}

	
	private function sendResponse($msg)
	{
	   $resp_msg_arr['error']['code'] = 1;
	   $resp_msg_arr['error']['msg']  = $msg;
	   return $resp_msg_arr;
	}	
}

?>
