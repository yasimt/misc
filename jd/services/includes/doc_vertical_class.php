<?php
class docVerticalClass extends DB
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
	var  $ucode			= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		
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
		$valid_modules_arr	= array("TME","ME","JDA");
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		
		$this->temp_catids_arr = $this->getContractTempCategories();
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
		
		
		
		if($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
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
	function getDocVerticalInfo()
	{
		$eligible_flag = $this->isEligibleContract();
		if(intval($eligible_flag) !=1)
		{
			$message = "Not Eligible For Doctor Vertical.";
			echo json_encode($this->sendDieMessage($message));
			die;
		}
		if(count($this->temp_catids_arr)>0)
		{
			$temp_categories_str = implode("','",$this->temp_catids_arr);
			//$sqlCategoryInfo = "SELECT catid,category_name,display_product_flag,brand_name,rest_price_range,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$temp_categories_str."') AND display_product_flag&4=4 LIMIT 1";
			//$resCategoryInfo = parent::execQuery($sqlCategoryInfo, $this->conn_local);

			$cat_params = array();
			$cat_params['page'] ='doc_vertical_class';
			$cat_params['skip_log']		= '1';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name,display_product_flag,brand_name,rest_price_range,national_catid';

			$where_arr  	=	array();
			$where_arr['catid']					= implode(",",$this->temp_catids_arr);
			$where_arr['display_product_flag']	= '4';	
			$cat_params['where']				= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				global $db;
				
				$docid = $this->docidCreator();
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
					if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
						$rsvn_type_url 	 = "http://".WEB_SERVICES_API."/web_services/rsvnType.php";
					}else{
						$rsvn_type_url = "http://sunnyshende.jdsoftware.com/web_services/web_services/rsvnType.php";	
					}
					
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
	function isEligibleContract()
	{
		$eligible_flag = 0;
		$sqlContractInfo = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND closedown_flag !=1 AND ((ref_parentid  IS NULL) OR (ref_parentid = '') OR (ref_parentid = '0') OR (parentid = ref_parentid))";
		$resContractInfo = parent::execQuery($sqlContractInfo, $this->conn_iro);
		if($resContractInfo && parent::numRows($resContractInfo)>0)
		{
			$eligible_flag = 1;
		}
		return $eligible_flag;
	}
	public function docidCreator()
	{	
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI':
					$docid = "022".$this->parentid;
					break;
					
				case 'DELHI':
					$docid = "011".$this->parentid;
					break;
					
				case 'KOLKATA':
					$docid = "033".$this->parentid;
					break;
					
				case 'BANGALORE':
					$docid = "080".$this->parentid;
					break;
					
				case 'CHENNAI':
					$docid = "044".$this->parentid;
					break;
					
				case 'PUNE':
					$docid = "020".$this->parentid;
					break;
					
				case 'HYDERABAD':
					$docid = "040".$this->parentid;
					break;
					
				case 'AHMEDABAD':
					$docid = "079".$this->parentid;
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
					$docid = $stdcode.$this->parentid;
			}
		}
		else
		{
			$docid = "022".$this->parentid;
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
	function getContractTempCategories()
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
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}else{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			$row_temp_category	=	parent::fetchData($resTempCategory);
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
	
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	function curlCallPost($curlurl,$data)
	{	
		#echo $curlurl.'?'.$data;
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
