<?php
class searchplus_eligibility_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 	= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $usercode		= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->tr_request	= 0;
		if((isset($params['tr_request'])) && ($params['tr_request'] == 1)){
			$this->tr_request	= 1;
		}
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}
		$this->all_searchplus_campaigns_list = $this->getAllSearchplusCampaigns();
		$this->contract_existing_temp_cat_arr = $this->getContractTempCategories();
		
		$this->onDemandFlag 	= 0;
		$this->onDemandVName 	= '';
		$this->onDemandTypeFlag = '';
		$this->onDemandDisplayProduct = '';
		$this->onDemandNatCatidsArr = array();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];	
		
		if(($this->module =='DE') || ($this->module =='CS'))
		{
			$this->conn_temp	 	= $this->conn_local;
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif($this->module =='ME')
		{
			$this->conn_temp		= $this->conn_idc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		else
		{
			$message = "Invalid Module.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
			
	}
	function getContractTempCategories()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$temp_category_arr = array();
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}
		else
		{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0 ){
				$row_temp_category = parent::fetchData($resTempCategory);
			}
		}
		
		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->get_valid_categories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->get_valid_categories($total_catlin_arr);
			}
		}
		return $temp_category_arr; 
	}
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
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
		}
		return $final_catids_arr;	
	}
	function getEligibleSearchplusCampaign()
	{
		$eligible_flag = $this->isEligibleContract();
		if(intval($eligible_flag) !=1)
		{
			$message = "Not Eligible For Search Plus Campaign.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		$eligible_searchplus_campaigns_arr = array();
		if(count($this->all_searchplus_campaigns_list) >0)
		{
			if(count($this->contract_existing_temp_cat_arr)>0)
			{
				$temp_categories_str = implode("','",$this->contract_existing_temp_cat_arr);
				//$sqlCategoryInfo = "SELECT catid,category_name,display_product_flag,brand_name,rest_price_range,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$temp_categories_str."')";
				//$resCategoryInfo = parent::execQuery($sqlCategoryInfo, $this->conn_local);

				$cat_params = array();
				$cat_params['page'] ='searchplus_eligibility_class';
				$cat_params['data_city'] 	= $this->data_city;			
				$cat_params['return']		= 'catid,category_name,display_product_flag,brand_name,rest_price_range,national_catid';

				$where_arr  	=	array();
				$where_arr['catid']		= implode(",",$this->contract_existing_temp_cat_arr);
				$cat_params['where']	= json_encode($where_arr);

				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
				{
					foreach($cat_res_arr['results'] as $key=> $row_category)
					{
						$national_catid 		= trim($row_category['national_catid']);
						$display_product_flag 	= trim($row_category['display_product_flag']);
						foreach($this->all_searchplus_campaigns_list as $vertical_abbr => $vertical_details_arr)
						{
							if(((int)$display_product_flag & $vertical_details_arr['display_product_flag']) == $vertical_details_arr['display_product_flag'])
							{
								$eligible_searchplus_campaigns_arr[] = $vertical_abbr;
								if($vertical_abbr == 'RPR' || $vertical_abbr == 'SRS')
								{
									$this->onDemandNatCatidsArr[] = $national_catid;
								}
							}
						}
					}
					if(count($eligible_searchplus_campaigns_arr)>0){
						$eligible_searchplus_campaigns_arr = array_unique($eligible_searchplus_campaigns_arr);
						
						// Even If Only Doctor Category Exists Providing Option For Hospital As Well And Vice Versa
						
						if(in_array('DR',$eligible_searchplus_campaigns_arr) && !in_array('HS',$eligible_searchplus_campaigns_arr)){
							$eligible_searchplus_campaigns_arr[] = 'HS';
						}
						if(in_array('HS',$eligible_searchplus_campaigns_arr) && !in_array('DR',$eligible_searchplus_campaigns_arr)){
							$eligible_searchplus_campaigns_arr[] = 'DR';
						}
						if((in_array('TR',$eligible_searchplus_campaigns_arr)) && ($this->tr_request !=1)){
							$tableResevKey = array_search('TR',$eligible_searchplus_campaigns_arr);
							unset($eligible_searchplus_campaigns_arr[$tableResevKey]);
						}
						
						if(in_array('RPR',$eligible_searchplus_campaigns_arr) || in_array('SRS',$eligible_searchplus_campaigns_arr))
						{
							if(in_array('RPR',$eligible_searchplus_campaigns_arr) && in_array('SRS',$eligible_searchplus_campaigns_arr)){
								$this->onDemandFlag = 3;
								$this->onDemandVName .= 'Repairs,Service';
								$this->onDemandTypeFlag	.=	'1099511627776,2199023255552';
								$this->onDemandDisplayProduct = '17592186044416,35184372088832';
							}else if(in_array('RPR',$eligible_searchplus_campaigns_arr)){
								$this->onDemandFlag = 2;
								$this->onDemandVName .= 'Repairs';
								$this->onDemandTypeFlag	.=	'1099511627776';
								$this->onDemandDisplayProduct = '17592186044416';
							}else if(in_array('SRS',$eligible_searchplus_campaigns_arr)){
								$this->onDemandFlag = 1;
								$this->onDemandVName .= 'Service';
								$this->onDemandTypeFlag	.=	'2199023255552';
								$this->onDemandDisplayProduct = '35184372088832';
							}
						}
						if($this->onDemandFlag == 3){
							$repairKey = array_search('RPR',$eligible_searchplus_campaigns_arr);
							unset($eligible_searchplus_campaigns_arr[$repairKey]);
							$serviceKey = array_search('SRS',$eligible_searchplus_campaigns_arr);
							unset($eligible_searchplus_campaigns_arr[$serviceKey]);
						}else if($this->onDemandFlag == 2){
							$repairKey = array_search('RPR',$eligible_searchplus_campaigns_arr);
							unset($eligible_searchplus_campaigns_arr[$repairKey]);
						}else if($this->onDemandFlag == 1){
							$serviceKey = array_search('SRS',$eligible_searchplus_campaigns_arr);
							unset($eligible_searchplus_campaigns_arr[$serviceKey]);
						}
					}
				}
			}
			else
			{
				$message = "Category Not Found.";
				echo json_encode($this->sendDieMessage($message));
				die;
			}
		}
		else
		{
			$message = "No record found in db_iro.table tbl_searchplus_campaign_info Module : ".$this->module;
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		$restaurant_bform_searchplus_arr = array("FOS","LOS","SS","CKS");  // On Restaurant B-Form we only show one vertical at a time out of four vertical ie Restaurant,Wine,Sweets & Cake respectively.
		if((count($eligible_searchplus_campaigns_arr)>0) || (intval($this->onDemandFlag)>0))
		{
			$diff_unwanted_restbform_searchplus = 0;
			foreach($restaurant_bform_searchplus_arr as $restaurant_bform_searchplus)
			{
				if(in_array($restaurant_bform_searchplus,$eligible_searchplus_campaigns_arr))
				{
					$diff_unwanted_restbform_searchplus = 1;
					$key = array_search($restaurant_bform_searchplus,$restaurant_bform_searchplus_arr);
					unset($restaurant_bform_searchplus_arr[$key]);
					break;
				}
			}
			if($diff_unwanted_restbform_searchplus ==1){
				$eligible_searchplus_campaigns_arr = array_diff($eligible_searchplus_campaigns_arr,$restaurant_bform_searchplus_arr);
			}
			if((count($eligible_searchplus_campaigns_arr)>0) || (intval($this->onDemandFlag)>0))
			{
				if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
				{
					$cs_app_url = get_cs_application_url($this->data_city);
				}
				else
				{
					$cs_app_url = "http://imteyazraja.jdsoftware.com/csgenio/";
				}
				$eligible_searchplus_campaigns_data = array();
				foreach($eligible_searchplus_campaigns_arr as $eligible_searchplus_abbr)
				{
					$type_flag 			= $this->all_searchplus_campaigns_list[$eligible_searchplus_abbr]['type_flag'];
					$vertical_id 		= $this->all_searchplus_campaigns_list[$eligible_searchplus_abbr]['vertical_id'];
					$vertical_name 		= $this->all_searchplus_campaigns_list[$eligible_searchplus_abbr]['vertical_name'];
					
					
					
					$vertical_status_url = $cs_app_url."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=1&vertical_type_flag=".$type_flag;
					$vertical_status_res = json_decode($this->curlCallGet($vertical_status_url),true);
					$sub_type_flag 		 = $vertical_status_res['sub_type_flag'];
					
					$doc_hosp_vertical_flag = 0;
					if(($type_flag == 2) && ($eligible_searchplus_abbr == 'DR') && ($sub_type_flag == 0 || $sub_type_flag == 1))
					{
						$doc_hosp_vertical_flag = 1;
					}
					else if(($type_flag == 2) && ($eligible_searchplus_abbr == 'HS') && ($sub_type_flag==2))
					{
						$doc_hosp_vertical_flag = 1;
					}
					
					if($type_flag == 2 ){
						if($doc_hosp_vertical_flag == 1){
							$additional_param = 1;
						}
						else{
							$additional_param=0;
						}
					}
					else{
						$additional_param=1;
					}
					$web_status = 'Inactive';
					if(($vertical_status_res['web_active_flag'] == 1) && ($additional_param == 1))
					{
						$web_status = 'Active';
					}
					
					$iro_status = 'Inactive';
					if(($vertical_status_res['iro_active_flag'] == 1) && ($additional_param == 1))
					{
						$iro_status = 'Active';
					}
					
					$eligible_searchplus_campaigns_data[$vertical_name]['vertical_id'] 	= $vertical_id;
					$eligible_searchplus_campaigns_data[$vertical_name]['type_flag'] 	= $type_flag;
					$eligible_searchplus_campaigns_data[$vertical_name]['web_status'] 	= $web_status;
					$eligible_searchplus_campaigns_data[$vertical_name]['iro_status'] 	= $iro_status;
					
				}
				if(intval($this->onDemandFlag) > 0)
				{	
					$eligible_searchplus_campaigns_data['OnDemand Serice']['vertical_id'] 	= "21";
					$eligible_searchplus_campaigns_data['OnDemand Serice']['type_flag'] 	= $this->onDemandTypeFlag;
					$eligible_searchplus_campaigns_data['OnDemand Serice']['vname'] 		= $this->onDemandVName;
					$eligible_searchplus_campaigns_data['OnDemand Serice']['dpflag'] 	 	= $this->onDemandDisplayProduct;
					$eligible_searchplus_campaigns_data['OnDemand Serice']['natcatid'] 	 	= implode(",",$this->onDemandNatCatidsArr);
				}
				$result_msg_arr['data'] = $eligible_searchplus_campaigns_data;
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			}
			else
			{
				$message = "Not Eligible For Search Plus Campaign.";
				echo json_encode($this->sendDieMessage($message));
				die;
			}
			
		}
		else
		{
			$message = "Not Eligible For Search Plus Campaign.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		return $category_matched_verticals_arr;
		
		
	}
	function getAllSearchplusCampaigns()
	{
		$all_searchplus_campaigns_arr = array();
		$sqlSearchplusCampaigns = "SELECT vertical_id,vertical_name,vertical_abbr,display_product_flag,type_flag_value FROM tbl_searchplus_campaign_info WHERE active_flag = 1 AND module = '".$this->module."'";
		$resSearchplusCampaigns = parent::execQuery($sqlSearchplusCampaigns, $this->conn_iro);
		if($resSearchplusCampaigns && mysql_num_rows($resSearchplusCampaigns)>0)
		{
			while($row_searchplus_campaigns = mysql_fetch_assoc($resSearchplusCampaigns))
			{
				$vertical_name 			= trim($row_searchplus_campaigns['vertical_name']);
				$vertical_abbr 			= trim($row_searchplus_campaigns['vertical_abbr']);
				$display_product_flag 	= trim($row_searchplus_campaigns['display_product_flag']);
				$type_flag 				= trim($row_searchplus_campaigns['type_flag_value']);
				$vertical_id 			= trim($row_searchplus_campaigns['vertical_id']);
				$all_searchplus_campaigns_arr[$vertical_abbr]['vertical_name'] 			= $vertical_name;
				$all_searchplus_campaigns_arr[$vertical_abbr]['display_product_flag'] 	= $display_product_flag;
				$all_searchplus_campaigns_arr[$vertical_abbr]['type_flag'] 				= $type_flag;
				$all_searchplus_campaigns_arr[$vertical_abbr]['vertical_id'] 			= $vertical_id;
			}
		}
		return $all_searchplus_campaigns_arr;
	}
	function isEligibleContract()
	{
		$eligible_flag = 0;
		$sqlContractInfo = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND closedown_flag !=1 AND ((ref_parentid  IS NULL) OR (ref_parentid = '') OR (ref_parentid = '0') OR (parentid = ref_parentid))";
		$resContractInfo = parent::execQuery($sqlContractInfo, $this->conn_iro);
		if($resContractInfo && mysql_num_rows($resContractInfo)>0)
		{
			$eligible_flag = 1;
		}
		return $eligible_flag;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	function curlCallGet($curl_url)
	{	
		$ch = curl_init($curl_url);
		$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
	}
	
}



?>
