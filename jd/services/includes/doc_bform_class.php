<?php
class docBformClass extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 	= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $module		= null;
	var  $data_city		= null;
	var  $ucode			= null;
	
	
	function __construct($params)
	{
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		
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
		$valid_modules_arr	= array("TME","ME","JDA");
		
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->live_request = 0;
		
		 if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$this->cs_api      	=   get_cs_application_url($this->data_city);
			$this->web_api 	 	= "http://".WEB_SERVICES_API."/web_services/";
			$this->live_request	= 1;
		}
		else
		{
			$this->cs_api = "http://imteyazraja.jdsoftware.com/csgenio/";
			$this->web_api = "http://sunnyshende.jdsoftware.com/web_services/web_services/";	
		}
		$this->stdcode_final 	= 0;
		
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj =	new categoryClass();
		$this->companyClass_obj = new companyClass();
		$this->setServers();
		$this->iro_app_url = $this->getIROURL();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$this->conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$this->conn_city]['iro']['master'];
		$this->conn_local  		= $db[$this->conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$this->conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$this->conn_city]['idc']['master'];
		$this->conn_log   		= $db['db_log'];
		
		
		
		if($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($this->conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif($this->module =='ME' || $this->module =='JDA')
		{
			$this->conn_temp		= $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
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
	function getIROURL(){
		
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			switch(strtolower($this->data_city))
			{	
				case 'mumbai' 		: $url = "http://".MUMBAI_IRO_IP;break;
				case 'delhi' 		: $url = "http://".DELHI_IRO_IP;break;
				case 'kolkata' 		: $url = "http://".KOLKATA_IRO_IP;break;
				case 'bangalore' 	: $url = "http://".BANGALORE_IRO_IP;break;
				case 'chennai' 		: $url = "http://".CHENNAI_IRO_IP;break;
				case 'pune' 		: $url = "http://".PUNE_IRO_IP;break;
				case 'hyderabad' 	: $url = "http://".HYDERABAD_IRO_IP;break;
				case 'ahmedabad' 	: $url = "http://".AHMEDABAD_IRO_IP;break;
				default 			: $url = "http://".REMOTE_CITIES_IRO_IP;
			}
		}
		else
		{
			$url = "http://pravinkucha.jdsoftware.com/gred";
		}
		return $url;
	}
	function checkDocEligibility($params)
	{
		$parentid 	= trim($params['parentid']);
		$eligible_flag = $this->isEligibleContract($parentid);
		if(intval($eligible_flag) <= 0)
		{
			$message = "Not Eligible For Doctor Vertical.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		$this->temp_catids_arr = $this->getContractTempCategories($parentid);
		if(count($this->temp_catids_arr)>0)
		{
			$temp_categories_str = implode("','",$this->temp_catids_arr);
			//$sqlCategoryInfo = "SELECT catid,category_name,display_product_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$temp_categories_str."') AND ((display_product_flag&4=4) OR (display_product_flag&8=8))";
			//$resCategoryInfo = parent::execQuery($sqlCategoryInfo, $this->conn_local);

			$cat_params = array();
			$cat_params['page'] 		= 'doc_bform_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,category_name,display_product_flag';

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$this->temp_catids_arr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			//$doc_hosp_disp_flag = 0;
			/*if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0'){
				$display_product_flag =	$cat_res_arr['results']['']['display_product_flag'];
				if(((int)$display_product_flag & 4) == 4){
					$doc_hosp_disp_flag = 1;
				}
				elseif(((int)$display_product_flag & 8) == 8){
					$doc_hosp_disp_flag = 1;
				}
			}*/

			if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0'){			
				$doc_cat_flag = 0;
				$hosp_cat_flag = 0;
				foreach($cat_res_arr['results'] as $key =>$row_catdata)
				{
					$display_product_flag = trim($row_catdata['display_product_flag']);
					if(((int)$display_product_flag & 4) == 4){
						$doc_cat_flag = 1;
					}
					
					if(((int)$display_product_flag & 8) == 8){
						$hosp_cat_flag = 1;
					}
				}
				if($doc_cat_flag == 1)
				{
					global $db;
					$docid = $this->docidCreator($parentid);
					$webedit_vertical     = $db['webedit_vertical'];
					
					$sqlHospitalContracts = "SELECT parent_docid FROM tbl_reservation_mapping WHERE parent_docid = '".$docid."' AND sub_type_flag = 2 LIMIT 1";
					$resHospitalContracts = parent::execQuery($sqlHospitalContracts, $webedit_vertical);
					if($resHospitalContracts && parent::numRows($resHospitalContracts)>0)
					{
						$message = "Hospital B-Form Filled for this contract.";
						echo json_encode($this->sendDieMessage($message));
						die;
					}
					else
					{
						$result_msg_arr['eligibflg']		= $eligible_flag;
						$result_msg_arr['hosp_cat'] 		= $hosp_cat_flag;
						$result_msg_arr['error']['code'] 	= 0;
						$result_msg_arr['error']['msg'] 	= "Success";
						return $result_msg_arr;
					}
				}
				else
				{
					$message = "No Doctor Tagged Category Found.";
					echo json_encode($this->sendDieMessage($message));
					die;
				}
			}
			else
			{
				$message = "No Doctor Tagged Category Found.";
				echo json_encode($this->sendDieMessage($message));
				die;
			}
		}
		else
		{
			$message = "Category Not Found.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		return $category_matched_verticals_arr;
	}
	function isEligibleContract($parentid)
	{
		$eligible_flag = 0;

		$row_data = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'parentid,ref_parentid';
		$cat_params['page']			= 'doc_bform_class';
		$cat_params['skip_log']		= 1;

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			$row_data 		=	$resTempCategory['results']['data'][$parentid];
			$ref_parentid 	= 	trim($row_data['ref_parentid']);

			if((strlen($ref_parentid) < 4) || ($ref_parentid == $parentid)){
				$eligible_flag = 1;
			}else{
				$eligible_flag = 0;
			}
		}
		else
		{
			$eligible_flag = 2;
		}

		/*$sqlContractInfo = "SELECT parentid,ref_parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
		$resContractInfo = parent::execQuery($sqlContractInfo, $this->conn_iro);
		if($resContractInfo && parent::numRows($resContractInfo)>0)
		{
			$row_data 		= parent::fetchData($resContractInfo);
			$ref_parentid 	= trim($row_data['ref_parentid']);
			if((strlen($ref_parentid) < 4) || ($ref_parentid == $parentid)){
				$eligible_flag = 1;
			}else{
				$eligible_flag = 0;
			}
		}
		else
		{
			$eligible_flag = 2;
		}*/
		return $eligible_flag;
	}
	public function docidCreator($parentid)
	{	
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI':
					$docid = "022".$parentid;
					$this->stdcode_final = "022";
					break;
					
				case 'DELHI':
					$docid = "011".$parentid;
					$this->stdcode_final = "011";
					break;
					
				case 'KOLKATA':
					$docid = "033".$parentid;
					$this->stdcode_final = "033";
					break;
					
				case 'BANGALORE':
					$docid = "080".$parentid;
					$this->stdcode_final = "080";
					break;
					
				case 'CHENNAI':
					$docid = "044".$parentid;
					$this->stdcode_final = "044";
					break;
					
				case 'PUNE':
					$docid = "020".$parentid;
					$this->stdcode_final = "020";
					break;
					
				case 'HYDERABAD':
					$docid = "040".$parentid;
					$this->stdcode_final = "040";
					break;
					
				case 'AHMEDABAD':
					$docid = "079".$parentid;
					$this->stdcode_final = "079";
					break;	
						
				default :
					$docid_stdcode 	= $this->stdcodeInfo();
					if($docid_stdcode){
						$temp_stdcode = ltrim($docid_stdcode,0);
					}
					$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
					if(in_array(strtoupper($this->data_city),$ArrCity)){
						$sqlStdCode	= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
						$resStdCode = parent::execQuery($sqlStdCode, $this->conn_local);
						$rowStdCode =  parent::fetchData($resStdCode);
						$cityStdCode	=  $rowStdCode['stdcode'];
						if($temp_stdcode == ""){
							$stdcode = ltrim($cityStdCode,0);
							$stdcode = "0".$stdcode;				
						}else{
							$stdcode = "0".$temp_stdcode;				
						}
						
					}else{
						$stdcode = "9999";
					}	
					$docid = $stdcode.$parentid;
					$this->stdcode_final = $stdcode;
			}
		}
		else
		{
			$docid = "022".$parentid;
			$this->stdcode_final = "022";
		}
		return $docid;
	}
	private function stdcodeInfo()
	{
		$sql_stdcode = "SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		$res_stdcode = parent::execQuery($sql_stdcode, $this->conn_local);
		if($res_stdcode){
			$row_stdcode	=	parent::fetchData($res_stdcode);
			$stdcode 		= 	$row_stdcode['stdcode'];	
			if($stdcode[0]=='0'){
				$stdcode = $stdcode;
			}else{
				$stdcode = '0'.$stdcode;
			}
		}
		return $stdcode;
	}
	function getContractTempCategories($parentid)
	{
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
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}else{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			if($resTempCategory && parent::numRows($resTempCategory))
			{
				$row_temp_category	=	parent::fetchData($resTempCategory);
			}
		}
		
		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->getValidCategories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
			}
		}
		return $temp_category_arr; 
	}
	
	function getDocLocationInfo($params){
		$parentid = trim($params['parentid']);
	
		$docid = $this->docidCreator($parentid);
		
		$rsvn_type_url 	 = $this->web_api."rsvnType.php";
		
		$doc_flag = 0;
		$rsvn_type_data  = "docid=".$docid."&type_flag=2&sub_type_flag=1&backend_flow=1";
		$rsvn_type_resp  = $this->curlCallPost($rsvn_type_url,$rsvn_type_data);
		$doc_data_result = json_decode($rsvn_type_resp,true); 
		
		if(isset($doc_data_result['results']['multilocation']) && !empty($doc_data_result['results']['multilocation']))
		{
			$arry_count = count($doc_data_result['results']['multilocation']);
			if($arry_count >1)
			{
				$doc_flag =1;
			}
		}
		$result_msg_arr['data'] = $doc_flag;
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
	}
	function getValidCategories($total_catlin_arr)
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
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	
	function submitDocBFormData($bformdata){
		
		#print"<pre>";print_r($bformdata);
		
		if($bformdata['newconcall'] == 1){
			$bformdata['docdata'] = json_decode($bformdata['docdata'],true);
		}
		if(intval($bformdata['docdata']['eligibflg'] == 2)){
			unset($bformdata['docdata']['eligibflg']);
			$sqlInsertBformData = "INSERT INTO tbl_docnew_bform_data SET 
									root_parentid = '".trim($bformdata['root_parentid'])."',
									data_city = '".trim($bformdata['data_city'])."',
									ucode = '".trim($bformdata['ucode'])."',
									uname = '".trim($bformdata['uname'])."',
									docdata = '".json_encode($bformdata['docdata'])."'
									
									ON DUPLICATE KEY UPDATE
									
									data_city = '".trim($bformdata['data_city'])."',
									ucode = '".trim($bformdata['ucode'])."',
									uname = '".trim($bformdata['uname'])."',
									docdata = '".json_encode($bformdata['docdata'])."'";
			$resInsertBformData = parent::execQuery($sqlInsertBformData, $this->conn_log);
			$result_msg_arr['error']['code'] = 2;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}else{
			$updateDocBformOldData 	= "UPDATE tbl_docnew_bform_data SET active_flag = 0 WHERE root_parentid = '".trim($bformdata['root_parentid'])."'";
			$resUpdtDocBformOldData = parent::execQuery($updateDocBformOldData, $this->conn_log);
		}
		
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$doc_param 		= $bformdata['docdata']['doc'];
		$hosp_param 	= $bformdata['docdata']['hosp'];
		$root_parentid 	= trim($bformdata['root_parentid']);
		$root_docid		= $this->docidCreator($root_parentid);
		
		$this->bform_params = array();
		$this->bform_params['root_parentid'] = $root_parentid;
		$this->bform_params['root_docid'] = $root_docid;
		$this->bform_params['ucode']		= $bformdata['ucode'];
		$this->bform_params['uname']		= $bformdata['uname'];
		$this->bform_params['data_city'] 	= $this->data_city;
		
		$doctordata = array();
		
		if(count($doc_param['spec'])>0){
			$doctordata['speciality'] = implode('|~|',array_unique(array_filter($doc_param['spec'])));
		}
		
		if(count($doc_param['qual'])>0){
			$qualification_arr 	= array_values($doc_param['qual']);
			$qualification_arr 	= array_unique(array_filter($qualification_arr));
			$doctordata['qualification'] = implode('|~|',$qualification_arr);
		}
		
		if(count($doc_param['awrd'])>0){
			$doctordata['awards'] = implode('|~|',array_unique(array_filter($doc_param['awrd'])));
		}
		if(count($doc_param['rcog'])>0){
			$doctordata['recognition'] = implode('|~|',array_unique(array_filter($doc_param['rcog'])));
		}
		if(count($doc_param['accr'])>0){
			$doctordata['accreditation'] = implode('|~|',array_unique(array_filter($doc_param['accr'])));
		}
		if(count($doc_param['memb'])>0){
			$doctordata['membership'] = implode('|~|',array_unique(array_filter($doc_param['memb'])));
		}
		
		if(count($doc_param['felo'])>0){
			$doctordata['fellowship'] = implode('|~|',array_unique(array_filter($doc_param['felo'])));
		}
		if(count($doc_param['reg_no'])>0){
			$doctordata['reg_no'] = implode('|~|',array_unique(array_filter($doc_param['reg_no'])));
		}
		//$doctordata['reg_no'] 	= trim($doc_param['reg_no']);
		$doctordata['exp_val'] 	= trim($doc_param['exp_val']);
		
		
		
		$this->processed_docids_arr = array();
		$this->cardgen_docids_arr = array();
		
		if(count($hosp_param)>0)
		{
			foreach($hosp_param as $hospkey => $hosp_val)
			{
				$activation_alert = 0;
				if($hosp_val['docpid'] == ''){
					$new_parentid = trim($this->generateParentid());
					$doc_id       = $this->docidCreator($new_parentid);
					$doctorname = $doc_param['docnm'].'('.$hosp_val['loc'].')';
					$this->processed_docids_arr[] = $doc_id;
				}else{
					$new_parentid       = trim($hosp_val['docpid']);
					$doc_id = $this->docidCreator($new_parentid);
					
					if($new_parentid == $root_parentid)
					{
						$doctorname = $doc_param['docnm'];
						$activation_alert = 1;
					}
					else
					{
						$doctorname = $doc_param['docnm'].'('.$hosp_val['loc'].')';
					}
				}
				$doctorname	=	preg_replace('/^Dr /i','Dr. ',$doctorname,1);
				$sphinx_id    = $this->getSphinxid($new_parentid);
				$hosp_val['auto'] = strtoupper($hosp_val['auto']);
				$loc_parentid = $hosp_val['auto'];
				if(!empty($loc_parentid)){
					$loc_docid = $this->docidCreator($loc_parentid);
				}
				
				$update_flag = 0;
				if(($new_parentid != $root_parentid) && (strlen($hosp_val['auto']) > 4)){
					$update_flag = 1;
				}
				
				$this->bform_params['parentid'] 	= $new_parentid;
				$this->bform_params['companyname'] 	= $doctorname;
				$this->bform_params['sphinx_id'] 	= $sphinx_id;
				$this->bform_params['doc_id'] 		= $doc_id;

				if($update_flag == 1){
					
					$doc_con_flg = $this->isDocContractExists($new_parentid);
					
					$mainsource = $this->getMainSource($doc_con_flg);
					
					$this->bform_params['mainsource'] 	= $mainsource;
					
					$this->catid_lineage = array();
					
					$this->catid_lineage = $this->getCatidLineage($root_parentid);
					
					
					$this->geninfo_arr = array();
					$this->extradet_arr = array();
					
					$required_pid = $hosp_val['auto'];
					
					if($this->conn_city == 'remote'){
						$city_indicator = 'remote_city';
					}else{
						$city_indicator = 'main_city';
					}
					 
					$this->geninfo_arr 	= $this->generalInfoMain($required_pid);
					$this->extradet_arr = $this->extraDetailsMain($required_pid);
					
					if((count($this->geninfo_arr)>0) && (count($this->extradet_arr)>0)){
						
						$obj_log	= new contractLog($this->bform_params['parentid'], $this->module, $this->bform_params['ucode']."[Created / Edited via Doctor - Bform]" , $this->data_city);						
						
						$this->insertTMESearch();
						$this->insertGenInfo();
						
						$this->insertExtraDetails();
						$this->insertCompanySource();
						//$this->createHistory();
						unset($obj_log);
						
						
						$curl_data = array();
						$curl_data['city_indicator'] 	= $city_indicator;
						$curl_data['data_city'] 		= $this->data_city;
						$curl_data['parentid']	 		= $this->bform_params['parentid'];
						$curl_data['ucode'] 			= $this->bform_params['ucode'];
						$curl_data['uname'] 			= $this->bform_params['uname'];
						$curl_data['validationcode'] 	= 'HOSNDOC';
						
						$csurl = $this->cs_api."/web_services/curl_serverside.php";
						$cur_ci_response = $this->curlCallPost($csurl,$curl_data);
					}
				} // new contract creation end
				
				$this->docSignedUpContracts($hosp_val);
				
				$hospitaldata = array();
				
				if(intval($hosp_val['tfa']) == 1){
					$hospitaldata['type_flag_actions'] = 1;
				}else{
					$hospitaldata['type_flag_actions'] = 3;
				}
				$hospitaldata['entity_workplace'] = $hosp_val['loc'];
				if(count($hosp_val['mob'])>0){
					$hosp_mob_arr = array_unique(array_filter($hosp_val['mob']));
					$hospitaldata['entity_mobile'] = implode('|~|',$hosp_mob_arr);
				}
				
				if(count($hosp_val['phn'])>0){
					$std = $hosp_val['std'];
					if(substr($std,0,1) == 0){
						$stdcode = $std;
					}else{
						$stdcode = '0'.$std;
					}
					$hosp_phn_arr = array_unique(array_filter($hosp_val['phn']));
					$callback_phn_arr = array();
					if(count($hosp_phn_arr)>0){
						foreach($hosp_phn_arr as $hosp_phn){
							$callback_phn_arr[] = $stdcode.'-'.$hosp_phn;
						}
						$hospitaldata['callback_phone'] = implode('|~|',$callback_phn_arr);
						$hospitaldata['entity_phone'] = implode('|~|',$hosp_phn_arr);
					}
				}
				if(count($hosp_val['eml'])>0){
					$hospitaldata['entity_email'] = implode('|~|',array_unique(array_filter($hosp_val['eml'])));
				}
				$cltime_arr  = $hosp_val['cltime'];
				$clinic_timing = '';
				foreach($cltime_arr as $day=>$value){
					$clinic_timing .= $value."|";
				}
				$clinic_timing	=	$this->getFormattedTime($clinic_timing);		
				$clinic_timing =	str_replace("24:00","00:00",$clinic_timing);
				
				
				if($hosp_val['bkpol'] == 'Days' ){
					$hospitaldata['booking_policy'] = trim($hosp_val['bktxt'])." Days";
					$hospitaldata['min_time_rsvn'] = intval(trim($hosp_val['bktxt'])) * 10000;
					if(intval(trim($hosp_val['bktxt'])) ==1 ){
						$hospitaldata['booking_policy'] = 'Next Day';
					}
				}else if($hosp_val['bkpol'] == 'Hours' ){
					$hospitaldata['booking_policy'] = trim($hosp_val['bktxt'])." Hours";
					$hospitaldata['min_time_rsvn'] = intval(trim($hosp_val['bktxt'])) * 60;
					if(intval(trim($hosp_val['bktxt'])) ==1 ){
						$hospitaldata['booking_policy'] = '1 Hour';
					}
				}else{
					$hospitaldata['booking_policy'] = 'No Restriction';
					$hospitaldata['min_time_rsvn'] = 0;
				}
				
				
				if($hosp_val['cnpol'] == 'Days' ){
					$hospitaldata['cancel_policy'] = trim($hosp_val['cntxt'])." Days";
					$hospitaldata['min_time_rsvn_cancel'] = intval(trim($hosp_val['cntxt'])) * 10000;
					if(intval(trim($hosp_val['cntxt'])) ==1 ){
						$hospitaldata['cancel_policy'] = 'Next Day';
					}
				}else if($hosp_val['cnpol'] == 'Hours' ){
					$hospitaldata['cancel_policy'] = trim($hosp_val['cntxt'])." Hours";
					$hospitaldata['min_time_rsvn_cancel'] = intval(trim($hosp_val['cntxt'])) * 60;
					if(intval(trim($hosp_val['cntxt'])) ==1 ){
						$hospitaldata['cancel_policy'] = '1 Hour';
					}
				}else{
					$hospitaldata['cancel_policy'] = 'No Restriction';
					$hospitaldata['min_time_rsvn_cancel'] = 0;
				}
				
				
				if($hosp_val['ageres'] == '1'){
					$hospitaldata['age_restriction'] = $hosp_val['agfrm']."-".$hosp_val['agto'];
				}else{
					$hospitaldata['age_restriction'] = '';
				}
				
				if($hosp_val['genres'] == '1'){
					if(($hosp_val['mlchk'] == 1) && ($hosp_val['fmlchk'] == 1)){
						$hospitaldata['gender_spec'] = "M"."-"."F";
					}else if($hosp_val['mlchk'] == 1){
						$hospitaldata['gender_spec'] = "M";
					}else if($hosp_val['fmlchk'] == 1){
						$hospitaldata['gender_spec'] = "F";
					}else{
						$hospitaldata['gender_spec'] = '';
					}
				}else{
					$hospitaldata['gender_spec'] = '';
				}
				
				$rsvn_update_url 	 					= $this->web_api."rsvnUpdate.php";
				$update_data = array();
				$update_data['panindia_sphinxid'] 		= $this->bform_params['sphinx_id'];
				$update_data['docid'] 					= $this->bform_params['doc_id'];
				$update_data['parentid'] 				= $this->bform_params['parentid'];
				$update_data['data_city'] 				= $this->data_city;
				$update_data['entity_name'] 			= $this->bform_params['companyname'];
				$update_data['specialization'] 			= $doctordata['speciality'];
				$update_data['qualification'] 			= $doctordata['qualification'];
				$update_data['awards'] 					= $doctordata['awards'];
				$update_data['recognition'] 			= $doctordata['recognition'];
				$update_data['accreditation'] 			= $doctordata['accreditation'];
				$update_data['membership'] 				= $doctordata['membership'];
				$update_data['fellowship'] 				= $doctordata['fellowship'];
				$update_data['reg_no'] 					= $doctordata['reg_no'];
				$update_data['Total_experience'] 		= $doctordata['exp_val'];
				$update_data['entity_workplace'] 		= $hospitaldata['entity_workplace'];
				$update_data['entity_mobile'] 			= $hospitaldata['entity_mobile'];
				$update_data['login_mobile'] 			= $hosp_mob_arr[0];
				$update_data['callback_phone'] 			= $hospitaldata['callback_phone'];
				$update_data['entity_phone'] 			= $hospitaldata['entity_phone'];
				$update_data['entity_email'] 			= $hospitaldata['entity_email'];
				$update_data['fees'] 					= $hosp_val['fees'];
				$update_data['slot'] 					= $hosp_val['slot'];
				$update_data['hours_of_operation'] 		= $clinic_timing;
				$update_data['booking_policy'] 			= $hospitaldata['booking_policy'];
				$update_data['min_time_rsvn'] 			= $hospitaldata['min_time_rsvn'];
				$update_data['cancel_policy'] 			= $hospitaldata['cancel_policy'];
				$update_data['min_time_rsvn_cancel'] 	= $hospitaldata['min_time_rsvn_cancel'];
				$update_data['age_restriction'] 		= $hospitaldata['age_restriction'];
				$update_data['gender_spec'] 			= $hospitaldata['gender_spec'];
				$update_data['capacity'] 				= 1;
				$update_data['percentage_booking'] 		= 1.00;
				$update_data['changeover_slot'] 		= 0;
				$update_data['max_booking_limit'] 		= 1;
				$update_data['type_flag'] 				= 2;
				$update_data['updatedby'] 				= $this->bform_params['ucode'];
				$update_data['loc_parentid'] 			= $loc_parentid;
				$update_data['loc_docid'] 				= $loc_docid;
				$update_data['ref_parentid'] 			= $this->bform_params['root_parentid'];
				
				$rsvn_update_resp = $this->curlCallPost($rsvn_update_url,$update_data);
				
				
				$attributes_new_url 					=  $this->cs_api.'/api/update_attributes.php';
				$attributes_new_data 					= array();
				$attributes_new_data['action'] 			= 'attrupdate';
				$attributes_new_data['parentid'] 		= $this->bform_params['parentid'];
				$attributes_new_data['docid'] 			= $this->bform_params['doc_id'];
				$attributes_new_data['data_city'] 		= $this->data_city;
				$attributes_new_data['updatedby'] 		= $this->bform_params['ucode'];
				$attributes_new_data['vertical_name'] 	= 'doctor';
				$attrinputs_new						= array();
				if(intval($hosp_val['fees'])>0){
					$attrinputs_new['Consultation fee'] = $hosp_val['fees'];
				}else{
					$attrinputs_new['Consultation fee'] = '';
				}
				$attrinputs_new['Cancellation Policy'] 	= $hospitaldata['cancel_policy'];
				$attrinputs_new['Booking Policy'] 		= $hospitaldata['booking_policy'];
				$attributes_new_data['attrinfo'] 	= json_encode($attrinputs_new);
				
				$attributes_new_resp 				= $this->curlCallPost($attributes_new_url,$attributes_new_data);
				
				
				$updatedby_emp 		= $this->bform_params['ucode']." [".$this->bform_params['uname']."]";
				
				$attributes_url 				=  $this->cs_api.'/api/update_iro_web_listing_flag.php';
				$attributes_data 				= array();
				$attributes_data['action'] 		= 6;
				$attributes_data['docid_list'] 	= $this->bform_params['doc_id'];
				$attributes_data['data_city'] 	= $this->data_city;
				$attributes_data['updatedby'] 	= $updatedby_emp;
				$attrinputs						= array();
				if(intval($hosp_val['fees'])>0){
					$attrinputs['Consultation fee'] = $hosp_val['fees'];
				}else{
					$attrinputs['Consultation fee'] = '';
				}
				$attributes_data['attrinfo'] 	= json_encode($attrinputs);
				$attributes_resp 				= $this->curlCallPost($attributes_url,$attributes_data);
				$this->cardgen_docids_arr[]		= $this->bform_params['doc_id'];
				
				$tf_update_local_url = $this->cs_api."/api/update_iro_web_listing_flag.php?dept=".$this->module."&instaignore=1&request=SPBform&parentid=".$this->bform_params['parentid']."&action=2&vertical_type_flag=2&active_flag=1&web_active_flag=1&iro_active_flag=1&updatedby=".urlencode($updatedby_emp)."&type_flag_actions=".$hospitaldata['type_flag_actions'];
				$tf_update_local_res = json_decode($this->curlCallGet($tf_update_local_url),true);
				
				$remarks = 'Activation / De-Activation Through Doctor Flow';
				$tf_update_web_url = $this->web_api."rsvnActivate.php?docid=".$this->bform_params['doc_id']."&active_flag=1&iro_active_flag=1&web_active_flag=1&vertical_type_flag=2&updatedby=".$this->bform_params['ucode']."&remarks=".urlencode($remarks);
				$tf_update_web_res = json_decode($this->curlCallGet($tf_update_web_url),true);
				
				$narration  = "\n"."Process : Doctor Activation"."\n\n This Contract has been tagged for Doctor via Doctor B-Form.\n" ;
				$this->insertNarration($narration);
				
				$this->hospInfoShadow($update_data);
				
				$this->insertLog($uniqueid,$update_data,$tf_update_web_url);
				
				
				if($activation_alert == 1){
					$this->sendActivationMail($update_data);
				}
				
			}
			$doctor_add_response = $this->addDoctor($doctordata,$uniqueid);
		}
		if(trim($doctor_add_response) == 'Record updated'){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Fail";
			return $result_msg_arr;
		}
		
	}
	
	private function addDoctor($doctordata,$uniqueid){
	
		$doc_child_docids_url 	= $this->web_api.'childDocidsInfo.php';
		$doc_child_docids_data 	= 'parent_docid='.$this->bform_params['root_docid'].'&type_flag=2&vertical_name=Doctor';
		$doc_child_docids_resp = json_decode($this->curlCallPost($doc_child_docids_url,$doc_child_docids_data),true);
		
		
		/*-- For Sending Mail In case of Docid Mismatch Starts---*/
		$doc_child_docids_arr =  array();
		if($doc_child_docids_resp['results']['child_docids'] != null){
			$doc_child_docids_str = $doc_child_docids_resp['results']['child_docids'];
			$doc_child_docids_arr = explode(",",$doc_child_docids_str);
			if(count($doc_child_docids_arr)>0){
				$childdocid_json = json_encode($doc_child_docids_arr);
				foreach($doc_child_docids_arr as $childdocidval){
					$childdpidval = strstr(strtoupper($childdocidval),'P');
					$stdcode_exists =  str_replace($childdpidval,"",$childdocidval);
					if(intval($stdcode_exists) != intval($this->stdcode_final)){
						$mailto = "imteyaz.raja@justdial.com";
						$subject = "Docid Mismatch In Reservation Master / Reservation Mapping";
						$message = "Manual IP Address : ".$manual_ip_address."<br>SERVER ADDRESS : ".$_SERVER['SERVER_ADDR']."<br>Parentid : ".$this->bform_params['root_parentid']."<br>Data City : ".$this->data_city."<br>Module : ".$this->module."<br>Flow : Doctor - JDBOX"."<br>Child Docid List : ".$childdocid_json."<br> Stdcode Final : ".$this->stdcode_final."<br>Stdcode Exists : ".$stdcode_exists;
						$from = "noreply@justdial.com";
						global $db;
						require_once('class_send_sms_email.php');
						$emailsms_obj	 = new email_sms_send($db,$this->conn_city);
						if($emailsms_obj){
							$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'docbform');
						}
					}
				}
			}
		}
		/*-- For Sending Mail In case of Docid Mismatch Ends---*/
		
		$doc_child_docids_arr = array_merge($doc_child_docids_arr,$this->processed_docids_arr);
		$parent_child_docids_arr = array();
		$parent_child_docids_arr[] = $this->bform_params['root_docid'];
		$parent_child_docids_arr = array_merge($parent_child_docids_arr,$doc_child_docids_arr);
		$parent_child_docids_arr = array_merge(array_unique(array_filter($parent_child_docids_arr)));
		
		if(count($parent_child_docids_arr) > 0){
			$child_docid = implode(',',$parent_child_docids_arr);
			
			$all_docids_cnt = count($parent_child_docids_arr);
			if($all_docids_cnt > 1){
				$subtype_flag = 1;
			}else{
				$subtype_flag = 0;
			}
			
			$parent_child_pids_arr = array();
			foreach($parent_child_docids_arr as $parentchilddocid){
				$parent_child_pids_arr[] = strstr(strtoupper($parentchilddocid),'P');
			}
			$child_pid = implode(',',$parent_child_pids_arr);
			
			$all_pids_str = implode("','",$parent_child_pids_arr);
			
			$rsvn_mapping_url = $this->web_api.'rsvnMapping.php';
			$rsvn_mapping_data = 'parent_docid='.$this->bform_params['root_docid'].'&base_docid='.$this->bform_params['root_docid'].'&child_docid='.$child_docid.'&type_flag=2&sub_type_flag='.$subtype_flag.'&action_flag=0';
			$curlcontent = $this->curlCallPost($rsvn_mapping_url,$rsvn_mapping_data);
			$curl_response = json_decode($curlcontent,true);
			
			$attributes_new_url 					=  $this->cs_api.'/api/update_attributes.php';
			$attributes_new_data 					= array();
			$attributes_new_data['action'] 			= 'attrupdate';
			$attributes_new_data['parentid'] 		= $this->bform_params['root_parentid'];
			$attributes_new_data['docid'] 			= $this->bform_params['root_docid'];
			$attributes_new_data['data_city'] 		= $this->data_city;
			$attributes_new_data['updatedby'] 		= $this->bform_params['ucode'];
			$attributes_new_data['vertical_name'] 	= 'doctor';
			$attrinputs_new						= array();
			if(intval($doctordata['exp_val'])>0){
				$attrinputs_new['Years of Experience'] 	= $doctordata['exp_val'];
			}else{
				$attrinputs_new['Years of Experience'] 	= '';
			}
			$attrinputs_new['Qualification'] 	= str_replace('#','$#$',$doctordata['qualification']);
			$attrinputs_new['Specialization'] 	= $doctordata['speciality'];
			$attrinputs_new['Awards'] 			= $doctordata['awards'];
			$attrinputs_new['Fellowship'] 		= $doctordata['fellowship'];
			$attrinputs_new['Membership'] 		= $doctordata['membership'];
			$attrinputs_new['Accreditation'] 	= $doctordata['accreditation'];
			$attrinputs_new['Recognition'] 		= $doctordata['recognition'];
			$attrinputs_new['Registration No'] 	= $doctordata['reg_no'];
			
			$attributes_new_data['attrinfo'] 	= json_encode($attrinputs_new);
			$attributes_new_resp 				= $this->curlCallPost($attributes_new_url,$attributes_new_data);
			
			
			$attributes_mapping_url 					=  $this->cs_api.'/api/update_attributes.php';
			$attributes_mapping_data 					= array();
			$attributes_mapping_data['action'] 			= 'attrmapping';
			$attributes_mapping_data['data_city'] 		= $this->data_city;
			$attributes_mapping_data['updatedby'] 		= $this->bform_params['ucode'];
			$attributes_mapping_data['vertical_name'] 	= 'doctor';
			$attributes_mapping_data['parent_docid'] 	= $this->bform_params['root_docid'];
			$attributes_mapping_data['child_docid'] 	= $child_docid;
			$attributes_mapping_resp 					= $this->curlCallPost($attributes_mapping_url,$attributes_mapping_data);
			
			
			$iroparams = array();
			$iroparams['root_parentid'] = $this->bform_params['root_parentid']; 
			$iroparams['data_city'] 	= $this->data_city;
			$irocards_old				= $this->iroCardsData($iroparams);
			$attrs_old					= json_decode($irocards_old['medium']['attrstr'],true);
			
			$exp_old					= "";
			if(count($attrs_old['val'])>0){
				foreach($attrs_old['val'] as $attrkey => $attrval){
					if(strtolower($attrval[1]) == 'years of experience'){
						$exp_old = intval($attrval[2]);
					}
				}
			}
			$updatedby_emp 					= $this->bform_params['ucode']." [".$this->bform_params['uname']."]";
			$attributes_url 				=  $this->cs_api.'/api/update_iro_web_listing_flag.php';
			$attributes_data 				= array();
			$attributes_data['action'] 		= 6;
			$attributes_data['data_city'] 	= $this->data_city;
			$attributes_data['updatedby'] 	= $updatedby_emp;
			$attrinputs						= array();
			if(intval($doctordata['exp_val'])>0){
				$attrinputs['Years of Experience'] 	= $doctordata['exp_val'];
			}else{
				$attrinputs['Years of Experience'] 	= '';
			}
			if($exp_old != $doctordata['exp_val']){
				$attributes_data['docid_list'] 	= $child_docid;
			}else{
				$attributes_data['docid_list'] 	= implode(",",$this->cardgen_docids_arr);
			}
			$attributes_data['attrinfo'] = json_encode($attrinputs);
			$attributes_resp 			 = $this->curlCallPost($attributes_url,$attributes_data);
			
			$subtype_web_url 	= $this->web_api.'rsvnActivate.php';
			$subtype_web_data 	= 'docid_list='.$child_docid.'&action=5&sub_type_flag='.$subtype_flag.'&vertical_type_flag=2';
			$subtype_web_res 	= $this->curlCallPost($subtype_web_url,$subtype_web_data);
			$sqlUpdtSubtypeLocal = "UPDATE tbl_companymaster_extradetails SET sub_type_flag = '".$subtype_flag."', db_update = '".date("Y-m-d H:i:s")."', updatedby = '".$this->bform_params['ucode']."',  updatedon ='".date("Y-m-d H:i:s")."' WHERE parentid IN ('".$all_pids_str."')";
			$resUpdtSubtypeLocal = parent::execQuery($sqlUpdtSubtypeLocal,$this->conn_iro);

			
			$sql_updt_mapping_url = "UPDATE tbl_doctor_hospital_log SET rsvn_mapping_url = '".$rsvn_mapping_url."?".$rsvn_mapping_data."', parent_contract_data = '".$subtype_web_data."' WHERE uniqueid = '".$uniqueid."'";
			$res_updt_mapping_url = parent::execQuery($sql_updt_mapping_url,$this->conn_iro);
			
			return $curl_response['error']['msg'];
		}
	}
	
	public function iroCardsData($params_arr){
		$root_parentid 	= trim($params_arr['root_parentid']);
		$data_city 		= trim($params_arr['data_city']);
		$curlurl 		= $this->iro_app_url."/mvc/services/company/getcards?parentid=".$root_parentid."&city=".$data_city;
		$attr_info		= json_decode($this->curlCallGet($curlurl),true);
		$results = array();
		if($attr_info['errors']['code'] === 0){
			$results = $attr_info['results']['data'];
		}
		return $results;
	}
	
	private function hospInfoShadow($update_data){
		
		$insertSql = "INSERT INTO tbl_hospital_info_shadow
					  SET
					  panindia_sphinxid    		= '".$this->bform_params['sphinx_id']."',
   					  parentid             		= '".$this->bform_params['parentid']."',
					  docid               		= '".$this->bform_params['doc_id']."',
					  data_city                 = '".$this->data_city."',
					  hours_of_operation  		= '".$update_data['hours_of_operation']."',
					  companyname         		= '".addslashes(stripslashes($this->bform_params['companyname']))."',
					  mobile               		= '".$update_data['entity_mobile']."',
					  login_mobile       		= '".$update_data['login_mobile']."',
					  callback_phone			= '".$update_data['callback_phone']."',
					  fees						= '".$update_data['fees']."',
					  email              		= '".$update_data['entity_email']."',
					  qualification       		= '".$update_data['qualification']."',
					  specialization       		= '".$update_data['specialization']."',
					  appointment_booking  		= '".$update_data['booking_policy']."',
					  appointment_cancellation  = '".$update_data['cancel_policy']."',
					  min_time_rsvn				= '".$update_data['min_time_rsvn']."',
					  min_time_rsvn_cancel		= '".$update_data['min_time_rsvn_cancel']."',
					  ucode                     = '".$this->bform_params['ucode']."',
					  time_slot					= '".$update_data['slot']."',
					  parent_pid				= '".$this->bform_params['root_parentid']."',
					  loc_parentid				= '".$update_data['loc_parentid']."',
					  age_restriction			= '".$update_data['age_restriction']."',
					  gender_spec				= '".$update_data['gender_spec']."',
					  capacity					= '".$update_data['capacity']."',
					  insertdate                = '".date("Y-m-d H:i:s")."',
					  active_flag				= '1'
					  ON DUPLICATE KEY UPDATE
					  docid               		= '".$this->bform_params['doc_id']."',
					  data_city                 = '".$this->data_city."',
					  hours_of_operation  		= '".$update_data['hours_of_operation']."',
					  companyname         		= '".addslashes(stripslashes($this->bform_params['companyname']))."',
					  mobile               		= '".$update_data['entity_mobile']."',
					  login_mobile       		= '".$update_data['login_mobile']."',
					  callback_phone			= '".$update_data['callback_phone']."',
					  fees						= '".$update_data['fees']."',
					  email              		= '".$update_data['entity_email']."',
					  qualification       		= '".$update_data['qualification']."',
					  specialization       		= '".$update_data['specialization']."',
					  appointment_booking  		= '".$update_data['booking_policy']."',
					  appointment_cancellation  = '".$update_data['cancel_policy']."',
					  min_time_rsvn				= '".$update_data['min_time_rsvn']."',
					  min_time_rsvn_cancel		= '".$update_data['min_time_rsvn_cancel']."',
					  ucode                     = '".$this->bform_params['ucode']."',
					  time_slot					= '".$update_data['slot']."',
					  parent_pid				= '".$this->bform_params['root_parentid']."',
					  loc_parentid				= '".$update_data['loc_parentid']."',
					  age_restriction			= '".$update_data['age_restriction']."',
					  gender_spec				= '".$update_data['gender_spec']."',
					  capacity					= '".$update_data['capacity']."',
					  insertdate                = '".date("Y-m-d H:i:s")."',
					  active_flag				= '1'";
		$insertRes = parent::execQuery($insertSql,$this->conn_local);
	}
	
	private function sendActivationMail($update_data){
		$login_mobile = $update_data['login_mobile'];
		$entity_email = $update_data['entity_email'];
		$send_mail_flag = 1;
		$user_name_arr =  array();
		$email_id_arr = array();
		if(trim($login_mobile)!='')
		{
			$user_name_arr = explode("|~|",$login_mobile);
			$user_name_arr = array_unique($user_name_arr);
			$user_name_val = implode(",",$user_name_arr);
			
		}
		$skip_client_mobile = '0000';
		if(in_array($skip_client_mobile,$user_name_arr))
		{
			$send_mail_flag = 0;
		}
		if(trim($entity_email)!='')
		{
			$email_id_arr = explode("|~|",$entity_email);
		}
		
		
		if(trim($email_id_arr[0])!='')
		{
			$email_id = $email_id_arr[0];
		}
		else
		{
			$email_id = '';
		}
		if($this->live_request == 1 && $send_mail_flag == 1)
		{
			$mail_url = 'http://192.168.20.11/';
			$mail_path = $mail_url."functions/DoctSmsEmail.php?mobile=".$user_name_val."&doctNm=".urlencode($this->bform_params['companyname'])."&email=".$email_id;
			$mail_status = json_decode($this->curlCallGet($mail_path),true);
		}
		else
		{
			$mail_url = 'http://rishichandwani.jdsoftware.com/new_web/';
		}
	}
	
	private function insertLog($uniqueid,$rsvn_update_data,$rsvn_activate_url){
		$sqlDoctorLog = "INSERT INTO tbl_doctor_hospital_log SET
						parentid 			= '".$this->bform_params['parentid']."',
						docid 				= '".$this->bform_params['doc_id']."',
						data_city 			= '".addslashes($this->data_city)."',
						entity_name 		= '".addslashes(stripslashes($this->bform_params['companyname']))."',
						insertdate 			= '".date("Y-m-d H:i:s")."',
						ucode 				= '".$this->bform_params['ucode']."',
						uname 				= '".$this->bform_params['uname']."',
						module 				= '".$this->module."',
						ip_address 			= '".$this->bform_params['ipaddr']."',
						rsvn_update_url 	= '".json_encode($rsvn_update_data)."',
						rsvn_activate_url 	= '".$rsvn_activate_url."',
						iro_type_flag 		= '1',
						website_type_flag 	= '1',
						flow 				= 'Doctor',
						parent_pid 			= '".$this->bform_params['root_parentid']."',
						uniqueid 			= '".$uniqueid."'";
		$resDoctorLog = parent::execQuery($sqlDoctorLog,$this->conn_iro);
	}
	
	private function insertNarration($narration)
	{
		if(!empty($narration))
		{
			$narration	.= "\n".  date("F j, Y, g:i a") ."\n--". $this->uname;
			$sqlInsertNarration = "INSERT INTO tbl_paid_narration SET
								   contractid = '".$this->bform_params['parentid']."',
								   narration = \"".addslashes($narration)."\",
								   creationDt = '".date("Y-m-d H:i:s")."',
								   createdBy = '".addslashes($this->bform_params['ucode'])."',
								   parentid = '".$this->bform_params['parentid']."',
								   data_city = '".addslashes($this->data_city)."'";
			$resInsertNarration = parent::execQuery($sqlInsertNarration,$this->conn_local);
		}
	}
	public function getFormattedTime($timing_str){
		$time_arr 	=	array();
		$time_arr	=	explode("|", $timing_str);

		$this->short_day    	= $this->short_day();
		foreach ($time_arr as $key => $time_in_str) {
			$days_Arr[$this->short_day[$key]]= $time_in_str;
		}
		$daywise_Arr = array();
		
		foreach ($days_Arr as $day => $timing_str) {
			if($timing_str!=''){	
				$testdata = explode("~",$timing_str);
				
				$new_arr = array();
				foreach ($testdata as $key => $value) {
					if($value!=''){
						$value_Arr	=	explode("-", $value);
						$starttime 	=   strtotime($value_Arr["0"]);
						$endtime 	= 	strtotime($value_Arr["1"]);
						$new_arr[$key]	=	$strtotime+$endtime;
					}
				}
				asort($new_arr);

				$final_arr = array();
				foreach ($new_arr as $key1 => $value1) {
					$final_arr[]	=	$testdata[$key1];
				}
				$daywise_Arr[$day]= implode("~",$final_arr);
			}
			else{
					$daywise_Arr[$day]='';
				}
		}

		$timing_for_updation = array();
		foreach ($daywise_Arr as $day => $timing) {
			if($timing!=''){
				$corrected_arr	= $this->mergeTiming($timing);
				$timing_for_updation[$day] = implode("~",$corrected_arr);
			}
			else{
				$timing_for_updation[$day] ='';	
			}
		}
		return implode("|",$timing_for_updation);
	}
	public function short_day(){
		$days =	array(0=>"mon",1=>"tue",2=>"wed",3=>"thu",4=>"fri",5=>"sat",6=>"sun");
		return $days;
	}
	private function mergeTiming($timing){
		$temp_Arr  = array();
		$final_arr = array();
		$final_arr		=	explode("~", $timing);
		foreach ($final_arr as $key => $value) {
			if($value!=''){
				$t_Arr		=	explode("-", $value);
				$starttime1 =	$t_Arr['0'];
				$endtime1 	=	$t_Arr['1'];		
				if($pres_key 	=	array_search($starttime1, $temp_Arr)){
					$temp_Arr[$pres_key] =  $endtime1;
				}
				else{
					$temp_Arr[] 	= $starttime1;
					$temp_Arr[] 	= $endtime1;
				}		
			}
		}
		$index = 0;
		foreach ($temp_Arr as $key => $value) {
			if($key%2==0){
				$final_time[]= $temp_Arr[$index]."-".$temp_Arr[$index+1];
			}
			$index++;
		}
		return $final_time;
	}
	
	private function docSignedUpContracts($hosp_val){
		$sqlDocSigned = "INSERT INTO tbl_doctor_signedup_contracts SET 
						 parentid             	= '".$this->bform_params['parentid']."',
						 docid               	= '".$this->bform_params['doc_id']."',
						 companyname         	= '".addslashes(stripslashes($this->bform_params['companyname']))."',
						 data_city 				= '".$this->data_city."',
						 ucode                  = '".$this->bform_params['ucode']."',
						 uname                  = '".addslashes($this->bform_params['uname'])."',
						 insertdate             = '".date("Y-m-d H:i:s")."',						
						 parent_pid				= '".$this->bform_params['root_parentid']."',
					     loc_parentid			= '".$hosp_val['auto']."',
					     active_flag			= '1'
					     ON DUPLICATE KEY UPDATE
						 docid               	= '".$this->bform_params['doc_id']."',
						 companyname         	= '".addslashes(stripslashes($this->bform_params['companyname']))."',
						 data_city              = '".$this->data_city."',
						 ucode                  = '".$this->bform_params['ucode']."',
						 uname                  = '".addslashes($this->bform_params['uname'])."',
						 insertdate             = '".date("Y-m-d H:i:s")."',						
						 parent_pid				= '".$this->bform_params['root_parentid']."',
					     loc_parentid			= '".$hosp_val['auto']."',
					     active_flag			= '1'";
		$resDocSigned = parent::execQuery($sqlDocSigned,$this->conn_local);
	}
	
	public function isDocContractExists($parentid)
	{
		$exists_doc = 0;
		$cat_params = array();

		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'parentid';
		$cat_params['page']			= 'doc_bform_class';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			if(count($resTempCategory['results']['data'])>0){
				$exists_doc = 1;
			}
		}

		/*$sqlDocContractInfo = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";
		$resDocContractInfo = parent::execQuery($sqlDocContractInfo,$this->conn_iro);
		if($resDocContractInfo && parent::numRows($resDocContractInfo)>0){
			$exists_doc = 1;
		}*/
		return $exists_doc;
	}
	
	private function insertTMESearch(){
		
		$arealineage = '/'.$this->geninfo_arr['country'].'/'.$this->geninfo_arr['state'].'/'.$this->geninfo_arr['city'].'/'.$this->geninfo_arr['area'].'/';
					
					
		$ins_tmesearch_sql      =  "INSERT INTO tbl_tmesearch
						SET
						parentId 		=	'".$this->bform_params['parentid']."',
						contractid		=	'".$this->bform_params['parentid']."',
						compname		=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
						pincode			=	'".$this->geninfo_arr['pincode']."',
						paidstatus		=	'0',
						freez			=   '0',
						mask			=   '0',
						expired			=   '0',
						contact_details =   '".$this->geninfo_arr['mobile']."',
						data_city		=	'".$this->data_city."',
						arealineage		= 	'".$arealineage."',
						data_source		= 'DocFlow',
						datasource_date	= '".date("Y-m-d H:i:s")."',
						mainsource 		= '".$this->bform_params['mainsource']."',
						subsource 		= '',
						datesource 		= '".date("Y-m-d H:i:s")."'
						
						ON DUPLICATE KEY UPDATE
						contractid		=	'".$this->bform_params['parentid']."',
						compname		=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
						pincode			=	'".$this->geninfo_arr['pincode']."',
						paidstatus		=	'0',
						freez			=   '0',
						mask			=   '0',
						expired			=   '0',
						contact_details =   '".$this->geninfo_arr['mobile']."',
						data_city		=	'".$this->data_city."',
						arealineage		= 	'".$arealineage."',
						data_source		= 'DocFlow',
						datasource_date	= '".date("Y-m-d H:i:s")."',
						mainsource 		= '".$this->bform_params['mainsource']."',
						subsource 		= ''";
		$ins_tmesearch_res 			= parent::execQuery($ins_tmesearch_sql,$this->conn_local);
		
	}
	private function insertGenInfo(){
		
		$sqlInsertGenInfo		=  "INSERT INTO tbl_companymaster_generalinfo 
									SET 	
									sphinx_id    		= '".$this->bform_params['sphinx_id']."',
									parentid		=	'".$this->bform_params['parentid']."',
									companyname		=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
									country			=	'".$this->geninfo_arr['country']."',
									state			=	'".$this->geninfo_arr['state']."',
									city			=	'".$this->geninfo_arr['city']."',
									display_city	=	'".$this->geninfo_arr['display_city']."',
									area			=	'".$this->geninfo_arr['area']."',
									building_name	=	'".$this->geninfo_arr['building_name']."',
									street			=	'".$this->geninfo_arr['street']."',
									landmark		=	'".$this->geninfo_arr['landmark']."',
									pincode			=	'".$this->geninfo_arr['pincode']."',
									latitude		=	'".$this->geninfo_arr['latitude']."',
									longitude		=	'".$this->geninfo_arr['longitude']."',
									geocode_accuracy_level = '".$this->geninfo_arr['geocode_accuracy_level']."',
									full_address	=	'".$this->geninfo_arr['full_address']."',
									stdcode			=	'".$this->geninfo_arr['stdcode']."',
									landline		=	'".$this->geninfo_arr['landline']."',
									landline_display=	'".$this->geninfo_arr['landline_display']."',
									mobile			=	'".$this->geninfo_arr['mobile']."',
									mobile_display	=	'".$this->geninfo_arr['mobile_display']."',
									email			=	'".$this->geninfo_arr['email']."',
									email_display	=	'".$this->geninfo_arr['email_display']."',
									contact_person	=	'".addslashes($this->geninfo_arr['contact_person'])."',
									contact_person_display	=	'".addslashes($this->geninfo_arr['contact_person_display'])."',
									fax				=	'".$this->geninfo_arr['fax']."',
									tollfree		=	'".$this->geninfo_arr['tollfree']."',
									website			=	'".$this->geninfo_arr['website']."',
									displayType		=	'".$this->geninfo_arr['displayType']."',
									regionid		= 	'".$this->geninfo_arr['regionid']."',
									paid			=	'0',
									data_city			='".$this->data_city."'
									ON DUPLICATE KEY UPDATE								
									companyname		=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
									country			=	'".$this->geninfo_arr['country']."',
									state			=	'".$this->geninfo_arr['state']."',
									city			=	'".$this->geninfo_arr['city']."',
									display_city	=	'".$this->geninfo_arr['display_city']."',
									area			=	'".$this->geninfo_arr['area']."',
									building_name	=	'".$this->geninfo_arr['building_name']."',
									street			=	'".$this->geninfo_arr['street']."',
									landmark		=	'".$this->geninfo_arr['landmark']."',
									pincode			=	'".$this->geninfo_arr['pincode']."',
									latitude		=	'".$this->geninfo_arr['latitude']."',
									longitude		=	'".$this->geninfo_arr['longitude']."',
									geocode_accuracy_level = '".$this->geninfo_arr['geocode_accuracy_level']."',
									full_address	=	'".$this->geninfo_arr['full_address']."',
									stdcode			=	'".$this->geninfo_arr['stdcode']."',
									landline		=	'".$this->geninfo_arr['landline']."',
									landline_display=	'".$this->geninfo_arr['landline_display']."',
									mobile			=	'".$this->geninfo_arr['mobile']."',
									mobile_display	=	'".$this->geninfo_arr['mobile_display']."',
									email			=	'".$this->geninfo_arr['email']."',
									email_display	=	'".$this->geninfo_arr['email_display']."',
									contact_person	=	'".addslashes($this->geninfo_arr['contact_person'])."',
									contact_person_display	=	'".addslashes($this->geninfo_arr['contact_person_display'])."',
									fax				=	'".$this->geninfo_arr['fax']."',
									tollfree		=	'".$this->geninfo_arr['tollfree']."',
									website			=	'".$this->geninfo_arr['website']."',
									displayType		=	'".$this->geninfo_arr['displayType']."',
									regionid		= 	'".$this->geninfo_arr['regionid']."',
									paid			=	'0',
									data_city		=	'".$this->data_city."'";
		$resInsertGenInfo 			= parent::execQuery($sqlInsertGenInfo,$this->conn_iro);
	}
	
	private function insertExtraDetails(){
		$ins_extra_det_main		 = "INSERT INTO tbl_companymaster_extradetails
									SET	
									sphinx_id    		= 	'".$this->bform_params['sphinx_id']."',
									parentid			=	'".$this->bform_params['parentid']."',
									companyname			=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
									mobile_addinfo		=   '".addslashes(stripslashes($this->extradet_arr['mobile_addinfo']))."',
									landline_addinfo	= 	'".addslashes(stripslashes($this->extradet_arr['landline_addinfo']))."',
									regionid			= 	'".$this->extradet_arr['regionid']."',
									display_flag		=	'".$this->extradet_arr['display_flag']."',
									flags				=	'".$this->extradet_arr['flags']."',
									map_pointer_flags	=	'".$this->extradet_arr['map_pointer_flags']."',
									createdby			=	'".$this->bform_params['ucode']."',
									createdtime			=	'".date("Y-m-d H:i:s")."',
									original_creator	=	'".$this->bform_params['ucode']."',
									original_date		=	'".date("Y-m-d H:i:s")."',
									updatedBy			=	'".$this->bform_params['ucode']."',
									updatedOn			= 	'".date("Y-m-d H:i:s")."',
									data_city			=   '".$this->data_city."',
									year_establishment	= 	'".$this->extradet_arr['year_establishment']."',
									catidlineage		=	'".$this->catid_lineage['catidlineage']."',
									national_catidlineage =	'".$this->catid_lineage['national_catidlineage']."',
									catidlineage_search	=	'".$this->catid_lineage['catidlineage_search']."',
									national_catidlineage_search =	'".$this->catid_lineage['national_catidlineage_search']."',
									ref_parentid		= 	'".$this->bform_params['root_parentid']."',
									sub_type_flag 		=   '1'
									ON DUPLICATE KEY UPDATE
									companyname			=	'".addslashes(stripslashes($this->bform_params['companyname']))."',
									mobile_addinfo		=   '".addslashes(stripslashes($this->extradet_arr['mobile_addinfo']))."',
									landline_addinfo	= 	'".addslashes(stripslashes($this->extradet_arr['landline_addinfo']))."',
									regionid			= 	'".$this->extradet_arr['regionid']."',
									display_flag		=	'".$this->extradet_arr['display_flag']."',
									flags				=	'".$this->extradet_arr['flags']."',
									map_pointer_flags	=	'".$this->extradet_arr['map_pointer_flags']."',
									updatedBy			=	'".$this->bform_params['ucode']."',
									updatedOn			= 	'".date("Y-m-d H:i:s")."',
									data_city			=	'".$this->data_city."',
									year_establishment	= 	'".$this->extradet_arr['year_establishment']."',
									catidlineage		=	'".$this->catid_lineage['catidlineage']."',
									national_catidlineage =	'".$this->catid_lineage['national_catidlineage']."',
									catidlineage_search	=	'".$this->catid_lineage['catidlineage_search']."',
									national_catidlineage_search =	'".$this->catid_lineage['national_catidlineage_search']."',
									ref_parentid		= '".$this->bform_params['root_parentid']."',
									sub_type_flag 		=   '1'";
		$ins_extra_res_main 		= parent::execQuery($ins_extra_det_main,$this->conn_iro);							
	}
	
	private function createHistory(){
		
		/*$business_details_arr = array();
			
		if(count($this->geninfo_arr)>0){
			foreach($this->geninfo_arr as $gen_details_key=>$gen_details_value){
				$business_details_arr[$gen_details_key] = $gen_details_value;
			}
		}
		$business_details_arr['parentid'] = $this->bform_params['parentid'];
		$business_details_arr['sphinx_id'] = $this->bform_params['sphinx_id'];
		$business_details_arr['companyname'] = addslashes(stripslashes($this->bform_params['companyname']));
		$business_details_arr['paid'] = '0';
		$business_details_arr['updatedOn'] = date('Y-m-d H:i:s');
		$business_details_arr['catList'] = $this->catid_lineage['catid_history'];
		$sqlHistoryLog 	= 						"INSERT INTO tbl_contract_update_trail SET
												parentid				= '".$this->bform_params['parentid']."',
												update_time				= '".date("Y-m-d H:i:s")."',
												updated_by				= '".$this->bform_params['ucode']."[Created / Edited via Doctor - Bform]"."',
												paidstatus				= '0',
												compname				= '".addslashes(stripslashes($this->bform_params['companyname']))."',
												business_details_old	= '',
												business_details_new	= '".http_build_query($business_details_arr)."'";
		$resHistoryLog 		= parent::execQuery($sqlHistoryLog,$this->conn_local);*/
		$obj_log	= new contractLog($this->bform_params['parentid'], $this->module, $this->bform_params['ucode']."[Created / Edited via Doctor - Bform]" , $this->data_city);
	}
	private function insertCompanySource(){
		$emp_detail = $this->bform_params['ucode'].",".$this->bform_params['uname'];
		$sql_InsertSource = "INSERT INTO d_jds.tbl_company_source SET
							mainsource 	= '".$this->bform_params['mainsource']."',
							datesource 	= '".date("Y-m-d H:i:s")."',
							emp_detail 	= '".$emp_detail."',
							contactID 	= '".$this->bform_params['parentid']."',
							parentid 	= '".$this->bform_params['parentid']."',
							paidstatus 	= '0',
							data_city 	= '".$this->data_city."'";
		$res_InsertSource 	= parent::execQuery($sql_InsertSource,$this->conn_local);
	}
	public function generalInfoMain($parentid){
		
		$row_geninfo = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['page'] 		= 'doc_bform_class';
		
		$cat_params['fields']		= 'sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,display_city,area,pincode,full_address,latitude,longitude,	geocode_accuracy_level,stdcode,landline,landline_display,mobile,mobile_display,mobile_feedback,contact_person,contact_person_display,fax,tollfree,email,email_display,sms_scode,website,paid,othercity_number,data_city,displayType,regionid,company_callcnt,company_callcnt_rolling,area';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
			$row_geninfo 		=	$res_gen_info1['results']['data'][$parentid];
		}


		/*$sqlGeninfoMain			=	"SELECT sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,display_city,area,pincode,full_address,latitude,longitude,	geocode_accuracy_level,stdcode,landline,landline_display,mobile,mobile_display,mobile_feedback,contact_person,contact_person_display,fax,tollfree,email,email_display,sms_scode,website,paid,othercity_number,data_city,displayType,regionid,company_callcnt,company_callcnt_rolling,area FROM tbl_companymaster_generalinfo WHERE parentid = '".$parentid."'";
		$resGeninfoMain 			= parent::execQuery($sqlGeninfoMain,$this->conn_iro);
		$row_geninfo			=	parent::fetchData($resGeninfoMain);*/
		
		return $row_geninfo;
	}
	public function extraDetailsMain($parentid){
		
		$row_extradet = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['page'] 		= 'doc_bform_class';
		$cat_params['fields']		= 'companyname,freeze,mask,mobile_addinfo,landline_addinfo,regionid,display_flag,tag_catid,tag_catname,year_establishment,deactflg,temp_deactive_start,temp_deactive_end,updatedOn,original_date,price_range,flags,map_pointer_flags';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			$row_extradet 		=	$resTempCategory['results']['data'][$parentid];
		}

		/*$sqlExtradetMain			=	"SELECT companyname,freeze,mask,mobile_addinfo,landline_addinfo,regionid,display_flag,tag_catid,tag_catname,year_establishment,deactflg,
		temp_deactive_start,temp_deactive_end,updatedOn,original_date,price_range,flags,map_pointer_flags  FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
		$resExtradetMain 			= parent::execQuery($sqlExtradetMain,$this->conn_iro);
		$row_extradet			=	parent::fetchData($resExtradetMain);*/

		return $row_extradet;
	}
	private function getCatidLineage($parentid){
		
		 if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid']       = $parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['table']          = "tbl_business_temp_data";
			$mongo_inputs['fields']         = "catIds,categories";
			$row_catlin = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlCatidlineage 	= "SELECT catIds,categories FROM tbl_business_temp_data WHERE contractid = '".$parentid."'";
			$resCatidlineage 	= parent::execQuery($sqlCatidlineage,$this->conn_temp);
			if($resCatidlineage && parent::numRows($resCatidlineage)>0){
				$row_catlin	  	= parent::fetchData($resCatidlineage);
			}
		}
		
		if(count($row_catlin)>0){
			$cat_ids_list 	= $row_catlin['catIds'];
			$cat_name_list = $row_catlin['categories'];
			$catid_arr  	=  explode("|P|",$cat_ids_list); 	
			$catid_arr 		= array_filter($catid_arr);
			$catid_arr   	= array_unique($catid_arr);
			$catname_arr = explode("|P|",$cat_name_list);
			$catname_arr = array_filter($catname_arr);
			$catname_arr = array_unique($catname_arr);
		}
		if(count($catid_arr)>0)
		{
			$catids_str = '';
			$catids_str = implode(",",$catid_arr);
			$catid_history = implode(",",$catname_arr);
			$national_catids_arr = array();
			$national_catids_arr = $this->getNationalCatid($catids_str);
			$final_catlin = '';
			$final_nat_catlin = '';
			foreach($catid_arr as $catidlineage)
			{
				if($final_catlin)
				{
					$final_catlin .= ",/".$catidlineage."/";
				}
				else
				{
					$final_catlin = "/".$catidlineage."/";
				}
			}
			$catdetails_arr['catidlineage'] = $final_catlin;
			$catdetails_arr['catidlineage_search'] = $final_catlin;
			foreach($national_catids_arr as $nat_catidlineage)
			{
				if($final_nat_catlin)
				{
					$final_nat_catlin .= ",/".$nat_catidlineage."/";
				}
				else
				{
					$final_nat_catlin = "/".$nat_catidlineage."/";
				}
			}
			$catdetails_arr['national_catidlineage'] = $final_nat_catlin;
			$catdetails_arr['national_catidlineage_search'] = $final_nat_catlin;
			$catdetails_arr['catid_history'] = $catid_history;
		}
		return $catdetails_arr;
		
	}
	function getNationalCatid($catidList)
	{
		$nationalCatidArr = array();
		//$sqlNationalCatidList =  "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catidList."')";

		$cat_params = array();
		$cat_params['page'] 		= 'doc_bform_class';
		$cat_params['data_city'] 	= $this->data_city;		
		$cat_params['return']		= 'catid,national_catid';	

		$where_arr  	=	array();			
		$where_arr['catid']			= $catidList;		
		$cat_params['where']		= json_encode($where_arr);
		
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		//$resNationalCatidList = parent::execQuery($sqlNationalCatidList,$this->conn_local);
	
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key=>$cat_arr)
			{
				$nationalCatidArr[] = $cat_arr['national_catid'];
			}
		}
		return $nationalCatidArr;
	}
	public function getMainSource($existing_flag)
	{
		$source_code = '';
		switch(strtoupper($this->data_city))
		{
			case 'MUMBAI':
				if($existing_flag == 1){
					$source_code = '1283';
				}else{
					$source_code = '1282';
				}
				break;
			case 'REMOTE':
				if($existing_flag == 1){
					$source_code = '3443';
				}else{
					$source_code = '3442';
				}
				break;
			case 'DELHI':
				if($existing_flag == 1){
					$source_code = '1369';
				}else{
					$source_code = '1368';
				}
				break;
			case 'KOLKATA':
				if($existing_flag == 1){
					$source_code = '1265';
				}else{
					$source_code = '1264';
				}
				break;
			case 'BANGALORE':
				if($existing_flag == 1){
					$source_code = '1224';
				}else{
					$source_code = '1223';
				}
				break;
			case 'CHENNAI':
				if($existing_flag == 1){
					$source_code = '1214';
				}else{
					$source_code = '1213';
				}
				break;
			case 'PUNE':
				if($existing_flag == 1){
					$source_code = '1192';
				}else{
					$source_code = '1191';
				}
				break;
			case 'HYDERABAD':
				if($existing_flag == 1){
					$source_code = '1184';
				}else{
					$source_code = '1183';
				}
				break;
			case 'AHMEDABAD':
				if($existing_flag == 1){
					$source_code = '1229';
				}else{
					$source_code = '1227';
				}
				break;
			default :
				if($existing_flag == 1){
					$source_code = '1283';
				}else{
					$source_code = '1282';
				}
				break;
		}
		return $source_code;
	}
	
	
	
	 private function generateParentid(){
        
        for($i = 0; $i < 3; $i++){  //Random String Generator
             $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
             $iTotal = count($aChars) - 1;
             $iIndex = rand(0, $iTotal);
             $sCode .= $aChars[$iIndex];
             $sCode .= chr(rand(49, 57));
        }
        $stdcode = "XXXX";
        if($this->data_city){
            $sqlFetchStdCode = "SELECT stdcode FROM tbl_stdcode_master WHERE city = '".$this->data_city."' and stdcode!='' LIMIT 1";
            $resFetchStdCode = parent::execQuery($sqlFetchStdCode,$this->conn_local);
            $numberOfRows    = parent::numRows($resFetchStdCode);
            if($resFetchStdCode && $numberOfRows > 0){
                $row_std_code   =   parent::fetchData($resFetchStdCode);
                $stdcode = $row_std_code['stdcode'];
            }
        }
        $stdcode = substr($stdcode,1);
        $stdcode = str_pad($stdcode,4,"X",STR_PAD_LEFT);

        if($stdcode=="XXXX"){
            $message = "STD code for given data city ".$this->data_city." does not exist.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $stdcode_destination_component = $stdcode; // 4 digit
        $time_component = substr(date("YmdHis",time()),2); // 12 digit
        $random_number_component = substr($sCode,2); // 4 digit

        $cCode = $stdcode_destination_component.".".$stdcode_destination_component.".".$time_component.".".$random_number_component; //24 + 3 = 27 digits
        /*Genrating Sphinx id*/
        if($cCode){
			$PCode="P".$cCode;
			$id_generator_url= $this->cs_api."/api_services/api_idgeneration.php?source=docvendor&rquest=idgenerator&module=cs&datacity=".urlencode($this->data_city)."&parentid=".$PCode."&rflag=".$this->remote_flag;
            $strNewsphinxId = json_decode($this->curlCallGet($id_generator_url),true);
        }
        /*--------------------*/
        return ('P'.$cCode);    
    }
	private function getSphinxid($parentid){
        $selectSql  =   "SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$parentid."'";
        $selectRes  =   parent::execQuery($selectSql,$this->conn_iro);
        $selectRow  =   parent::fetchData($selectRes);
        $sphinx_id  =   $selectRow['sphinx_id'];
        return $sphinx_id;
    }
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	public function curlCallGet($curl_url){
        $ch = curl_init($curl_url);
        $ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $resstr = curl_exec($ch);
        curl_close($ch);
        return $resstr;
    }
	function curlCallPost($curlurl,$data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch); 
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
}



?>
