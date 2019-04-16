<?php
class miniBformClass extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $tbl_id_generator = array();
	var  $docid			=null;
	
	function __construct($params){
		
		$this->params 	= $params;
		$parentid 		= trim($params['parentid']);
		$noparentid 	= trim($params['noparentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['empcode']);
		$flagNew 		= trim($params['flagNew']);
		
		if((trim($parentid)=='') && ($noparentid !=1) && ($flagNew != 1))
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message,1));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($ucode)=='')
		{
			$message = "Ucode is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->uname  	  	= $uname;
		/*mongo*/
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		$valid_modules_arr = array("TME","ME","JDA");
		if(!in_array($this->module,$valid_modules_arr)){
			$message = "This service is only applicable for ME/JDA module.";
			echo json_encode($this->sendResponse($message));
			die();
		}
		$configcls_obj		= new configclass();
		$urldetails			= $configcls_obj->get_url($this->data_city);
		$this->cs_url		= $urldetails['url'];
		$this->jdbox_url	= $urldetails['jdbox_url'];
		$this->iro_url		= $urldetails['iro_url'];
		$this->rest_url 	= $urldetails['rest_url'];
		if($params['trace']==1){
			print"<pre>";print_r($params);
		}
		$this->current_date = date("Y-m-d H:i:s");  
		
	}
	
	private function sendResponse($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	
	// Function to set DB connection objects
	function setServers()
	{
		global $db;
		$this->conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$this->conn_city]['iro']['master'];
		$this->conn_local  		= $db[$this->conn_city]['d_jds']['master'];
		$this->conn_tme  	= $db[$this->conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$this->conn_city]['idc']['master'];
		$this->conn_fin   		= $db[$this->conn_city]['fin']['master'];
		$this->conn_fin_slave   = $db[$this->conn_city]['fin']['slave'];
		$this->conn_dnc   		= $db['dnc'];
		if($this->module == 'ME')
		{
			$this->conn_temp = $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		if($this->module == 'TME')
		{
			$this->conn_temp     = $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($this->conn_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}
		}
		
	}
	
	/* Single APi for Onload Functionality */
	public function miniBformload(){ 
		$resultArr					=	array();
		$resultArr['cityInfo']		=	$this->getCityInfo();
		if($this->params['flagNew'] == 1){
			$resultArr['parentid']		=	$this->parentid_generator();
		}else if($this->params['navibar'] != ''){
			$resultArr				=	$this->getTempInfo();
		}
		return $resultArr;
	}
	
	/* get sphinx id from id_generator */
	public function idGeneratorData(){
		$resultarr = array();
		$sql = "SELECT * FROM tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_iro);
		if($res && parent::numRows($res)>0){
			$row = parent::fetchData($res);
			$resultarr['data'][] 		= $row;
			$resultarr['errorCode'] 	= 0;
			$resultarr['errorStatus'] 	= 'Success';
		}else{
			$resultarr['errorCode'] 	= 1;
			$resultarr['errorStatus'] 	= 'Failure';
		}
		return $resultarr;
	}
	
	/* get city autosuggest */
	public function getCityInfo(){
		$resultArr   = array();
		$cityInfoArr = array();
		if($this->params['City_onload'] != 1){
			$sqlCityInfo = "SELECT ct_name, city_id, state_id, state_name, country_id, country_name, stdcode FROM city_master where city_id='" . trim($this->params['city_db_dropdown']) . "' AND DE_display=1 AND display_flag=1 AND allow_data = '1'";
		}else{
			$sqlCityInfo = "SELECT DISTINCT ct_name as city, city_id, state_id, state_name, country_id, country_name, stdcode FROM city_master where city_id not in ('8','11','29','34','19','25','5','1') and DE_display = 1 AND display_flag=1 AND allow_data = '1' order by ct_name asc";
		}
		$resCityInfo = parent::execQuery($sqlCityInfo, $this->conn_local);
		if($resCityInfo && parent::numRows($resCityInfo)>0){
			while($row_city_info	=	parent::fetchData($resCityInfo)){
				$cityInfoArr[] = $row_city_info; 
			}
		}
		if(count($cityInfoArr)>0){
			$message = "City Data Found";
			$resultArr['data']				=	$cityInfoArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "City Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	
	/* get state id */
	public  function getStateId(){
		$state_id = '';
		$datacity	=	$this->params['data_city'];
		$sqlStateId = "SELECT state_id FROM city_master WHERE ct_name = '".$datacity."'";
		$resStateId = parent::execQuery($sqlStateId, $this->conn_local);
		if($resStateId && parent::numRows($resStateId)>0){
			$row_state_id = parent::fetchData($resStateId);
			$state_id = trim($row_state_id['state_id']);
			$this->params['state_id'] = trim($row_state_id['state_id']);
		}
		return $state_id;
	}
	
	/* generate parentid */
	public function parentid_generator(){
		header('Content-Type: application/json');
		$res	=	array();
		$stdcode 			= 	"XXXX";
		$sCode				=	'';
		$resultArr			=	array();
		for($i = 0; $i < 3; $i++){	//Random String Generator
			 $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
			 $iTotal = count($aChars) - 1;
			 $iIndex = rand(0, $iTotal);
			 $sCode .= $aChars[$iIndex];
			 $sCode .= chr(rand(49, 57));
		}
		if($this->params['data_city']!=''){
			$sql = "SELECT stdcode FROM city_master WHERE ct_name = '".$this->params['data_city']."' and stdcode!='' AND display_flag=1 LIMIT 1";
			$res =  parent::execQuery($sql, $this->conn_local);
			if(parent::numRows($res)){
				$row = parent::fetchData($res);
				$stdcode = $row['stdcode'];
			}
		}else{
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'City name is Null';
		}
		$stdcode = substr($stdcode,1);
		$stdcode = str_pad($stdcode,4,"X",STR_PAD_LEFT);
		if($stdcode=="XXXX"){
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'STD code for city '.$this->params['data_city'].' found to be blank.<h2>Please contact to software team immediately</h2>';
		}
		$stdcode_destination_component 	= 	$stdcode; // 4 digit
		$time_component 				= 	substr(date("YmdHis",time()),2); // 12 digit
		$random_number_component 		= 	substr($sCode,2); // 4 digit
		$cCode 							= 	$stdcode_destination_component.".".$stdcode_destination_component.".".$time_component.".".$random_number_component; //24 + 3 = 27 digits
		if($cCode){
			if($this->conn_city	==	'remote'){
				$module="tme";
				$rflag=1;
			}else{
				$module="tme";
				$rflag=0;
			}	
			$PCode="P".$cCode;
			$curlParams_temp 					=	 array();
			$curlParams_temp['url']				=	 $this->cs_url."/api_services/api_idgeneration.php?source=tme&rquest=idgenerator&module=".$module."&datacity=".
													 urlencode($this->params['data_city'])."&parentid=".$PCode."&rflag=".$rflag."&source=tme";
			$curlParams_temp['formate'] 		= 	'basic';
			$strNewsphinxId						=	 json_decode($this->curlCall($curlParams_temp),true);
			if(count($strNewsphinxId) > 0){
				$resultArr['parentid']				=	'P'.$cCode;
				$resultArr['sphinxID']				=	$strNewsphinxId;
				$resultArr['errorCode']				=	0;
				$resultArr['errorStatus']			=	'Parentid generated';
			}else{
				$resultArr['errorCode']				=	1;
				$resultArr['errorStatus']			=	'SphinxId Not Found';	
			}
		}else{
			$resultArr['errorCode']				=	0;
			$resultArr['errorStatus']			=	'Parentid NOT generated';
		}
		return $resultArr;
	}
		 
	
	public  function getTempInfo(){
		$tempdata = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['module']       	= $this->module;
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_generalinfo_shadow"=>"sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,mobile_admin,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city,stdcode",
				"tbl_companymaster_extradetails_shadow"=>"parentid,working_time_start,working_time_end,social_media_url,award,testimonial,proof_establishment,payment_type,turnover,year_establishment,updatedBy,updatedOn,landline_addinfo,mobile_addinfo,statement_flag,fb_prefered_language,catidlineage_nonpaid,tag_line,certificates,no_employee,accreditations",
				"tbl_business_temp_data"=>"catIds"
			));
			$tempdata['fetchshadow'] = $this->mongo_obj->getShadowData($mongo_inputs);
		}else{
			$sqlGeninfoShadow  = "SELECT sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,mobile_admin,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city,stdcode FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
			$resGeninfoShadow = parent::execQuery($sqlGeninfoShadow, $this->conn_temp);
			if($resGeninfoShadow && parent::numRows($resGeninfoShadow)>0){
				$row_geninfo_shadow = parent::fetchData($resGeninfoShadow);
				$tempdata['fetchshadow']['tbl_companymaster_generalinfo_shadow'] = $row_geninfo_shadow;
			}
			$sqlExtradetShadow  = "SELECT parentid,working_time_start,working_time_end,social_media_url,award,testimonial,proof_establishment,payment_type,turnover,year_establishment,updatedBy,updatedOn,landline_addinfo,mobile_addinfo,statement_flag,fb_prefered_language,catidlineage_nonpaid,tag_line,certificates,no_employee,accreditations FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$this->parentid."'";
			$resExtradetShadow = parent::execQuery($sqlExtradetShadow, $this->conn_temp);
			if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
				$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
				$tempdata['fetchshadow']['tbl_companymaster_extradetails_shadow'] = $row_extrdet_shadow;
			}
		}
		if($tempdata['fetchshadow']['tbl_companymaster_generalinfo_shadow']['pincode']!=''){
			$sql = "SELECT stdcode FROM tbl_stdcode_master WHERE pincode = '".$tempdata['fetchshadow']['tbl_companymaster_generalinfo_shadow']['pincode']."'";
			$res = parent::execQuery($sql, $this->conn_local);
			if($res && parent::numRows($res)>0){
				$row = parent::fetchData($res);
				$tempdata['stdcode'] = (substr($row['stdcode'],0,1) == '0') ? substr($row['stdcode'],1) : $row['stdcode'];
			}else{
				$tempdata['stdcode'] = '';
			}
		}else{
				$tempdata['stdcode'] = '';
		}
		$tempdata['idgenerator']	=	$this->idGeneratorData();
		return $tempdata;
	}
	
	public function insertshadowdetails(){
		$res_arr = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->params['parentid'];
			$mongo_inputs['data_city'] 	= $this->params['data_city'];
			$mongo_inputs['module']		= $this->params['module'];
			$mongo_data = array();
			$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
			$geninfo_upt = array();
			$geninfo_upt['companyname'] 			= $this->params['companyname'];
			$geninfo_upt['pincode'] 				= $this->params['pincode'];
			$geninfo_upt['stdcode'] 				= $this->params['stdcode'];
			$geninfo_upt['landline'] 				= $this->params['landline'];
			$geninfo_upt['landline_display'] 		= $this->params['landline_display'];
			$geninfo_upt['mobile'] 					= $this->params['mobile'];
			$geninfo_upt['mobile_display'] 			= $this->params['mobile_display'];
			$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
			$mongo_inputs['table_data'] 			= $mongo_data;
			$extrdet_tbl 							= "tbl_companymaster_extradetails_shadow";
			$extrdet_upt 							= array();
			$extrdet_upt['companyname'] 			= $this->params['companyname'];
			$extrdet_upt['updatedBy'] 				= $this->params['empcode'];
			$extrdet_upt['updatedOn'] 				= date('Y-m-d H:i:s');
			$extrdet_upt['newbusinessflag'] 		= 1;
			$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
			$extrdet_ins 							= array();
			$extrdet_ins['nationalid'] 				= '';
			$extrdet_ins['sphinx_id'] 				= $this->params['sphinx_id'];
			$extrdet_ins['createdby'] 				= $this->params['empcode'];
			$extrdet_ins['regionid'] 				= '';
			$extrdet_ins['createdtime']				= date('Y-m-d H:i:s');
			$extrdet_ins['original_creator'] 		= $this->params['empcode'];
			$extrdet_ins['original_date'] 			= date('Y-m-d H:i:s');			
			$mongo_data[$extrdet_tbl]['insertdata'] = $extrdet_ins;
			$mongo_inputs['table_data'] 			= $mongo_data;
			$resGeninfoShadow 						= $this->mongo_obj->updateData($mongo_inputs);
		}
		else
		{
			$ins_gen_info_entries 	= 	"INSERT INTO tbl_companymaster_generalinfo_shadow SET
										  nationalid		='',
										  sphinx_id			='".$this->params['sphinx_id']."',
										  companyname		='".$this->params['companyname']."',
										  parentid			='".$this->params['parentid']."',	
										  pincode 			='".$this->params['pincode']."',		 
										  stdcode			='".$this->params['stdcode']."',
										  landline 			='".$this->params['landline']."',
										  landline_display 	='".$this->params['landline']."',
										  mobile			='".$this->params['mobile']."',
										  mobile_display	='".$this->params['mobile']."',
										  geocode_accuracy_level	=	'4'
									ON DUPLICATE KEY UPDATE		  
										  companyname		='".$this->params['companyname']."',
										  pincode 			='".$this->params['pincode']."',		 
										  stdcode			='".$this->params['stdcode']."',
										  landline 			='".$this->params['landline']."',
										  landline_display 	='".$this->params['landline']."',
										  mobile			='".$this->params['mobile']."',
										  mobile_display	='".$this->params['mobile']."'";
			$ins_gen_info_entries = $ins_gen_info_entries."/* TMEMONGOQRY */";														
			//~ $resGeninfoShadow = parent::execQuery($ins_gen_info_entries, $this->conn_temp);	
			
			$sql_extradetails_shadow = "INSERT INTO tbl_companymaster_extradetails_shadow SET
										  nationalid		='',
										  sphinx_id			='".$this->params['sphinx_id']."',
										  regionid			='',
										  companyname 		='".$this->params['companyname']."',
										  parentid			='".$this->params['parentid']."',
										  updatedBy			='".$this->params['empcode']."',
										  updatedOn			=now(),
										  createdby			='".$this->params['empcode']."',
										  createdtime		=now(),
										  original_creator	='".$this->params['empcode']."',
										  original_date		=now(),
										  newbusinessflag	='1' 
										ON DUPLICATE KEY UPDATE
										  companyname 		='".$this->params['companyname']."',
										  parentid			='".$this->params['parentid']."',
										  updatedBy			='".$this->params['empcode']."',
										  updatedOn			=now()";
			$sql_extradetails_shadow = $sql_extradetails_shadow."/* TMEMONGOQRY */";															
			//~ $resextrainfoShadow = parent::execQuery($sql_extradetails_shadow, $this->conn_temp);	
			
		}
			
			$edat = date("Y-m-d H:i:s");
			$sql_save_bv = "INSERT INTO tbl_business_validation SET
								parentid 	= '".$this->params['parentid']."',
								company_name = '".addslashes(stripslashes($this->params['companyname']))."',
								tmecode 	= '".$this->params['empcode']."',
								tmename 	= '".$this->params['empname']."',
								datet_time 	= '".$edat."',
								source_id 	= '".$this->params['businesshid']."',
								Stationid 	= '".$this->params['Stationid']."'
							ON DUPLICATE KEY UPDATE
								parentid 	= '".$this->params['parentid']."',
								company_name = '".addslashes(stripslashes($this->params['companyname']))."',
								tmecode 	= '".$this->params['empcode']."',
								tmename 	= '".$this->params['empname']."',
								datet_time 	= '".$edat."',
								source_id 	= '".$this->params['businesshid']."',
								Stationid 	= '".$this->params['Stationid']."'";
			$res_save = parent::execQuery($sql_save_bv, $this->conn_local);
			if($resGeninfoShadow){
				$res_arr['errorCode']	=	0;
				$res_arr['errorStatus']	=	"Inserted successfully";
				return $res_arr;
			}else{
				$res_arr['errorCode']	=	1;
				$res_arr['errorStatus']	=	"Inserted failed";
				return $res_arr;
			}	
	}
	
	public function fetchAllocEmpDetails(){
		$allocation_array 	= 	array();
		$paridArr			=	$this->params['parentidArr'];
		foreach($paridArr as $key=>$val){
			$select_tme 		= "SELECT parentid,tmeCode FROM tbl_tmesearch WHERE parentid='".$val."'";
			$res_tme 			= parent::execQuery($select_tme, $this->conn_local);
			if($res_tme && parent::numRows($res_tme)>0){
				$row_tme = parent::fetchData($res_tme);
				if(!empty($row_tme['tmeCode'])){
					$select  = "SELECT DISTINCT b.mktEmpCode as empCode,c.empName FROM mktgEmpMap b JOIN mktgEmpMaster c ON (b.mktEmpCode = c.mktEmpCode) WHERE  b.mktEmpCode = c.mktEmpCode and b.rowId = '".$row_tme['tmeCode']."'";
					$res_select 	= parent::execQuery($select, $this->conn_local);
					if($res_select && parent::numRows($res_select)>0){
						$row_alloc 							 = parent::fetchData($res_select);
						$allocation_array[$val]['empCode'] = $row_alloc['empCode'];
						$allocation_array[$val]['empName'] = $row_alloc['empName'];							
					}else{
						$allocation_array[$val]['empCode'] = '';
						$allocation_array[$val]['empName'] = '';							
					}
				}else{
					$allocation_array[$val]['empCode'] = '';
					$allocation_array[$val]['empName'] = '';							
				}				 
			}else{
				$allocation_array[$val]['empCode'] = '';
				$allocation_array[$val]['empName'] = '';
			}	
		}
		return $allocation_array;
	}
	
	public function insertTMELOG(){
		$resArr 	= array();
		$sql 		= "INSERT INTO tbl_contract_tme_log SET
											contractid 	= '".$this->params['parentid']."',
											ucode 		= '".$this->params['empcode']."',
											tme_alloc 	= '".$this->params['tme_alloc']."'";
		$res = parent::execQuery($sql, $this->conn_local);
		if($res){
			$resArr['errorCode']	=	0;
			$resArr['errorMsg']		=	'Data Inserted';
		}else{
			$resArr['errorCode']	=	0;
			$resArr['errorMsg']		=	'Not inserted';
		}
		return $resArr;
	}
	
	//Curl Call func Starts
	public static function curlCall($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";
        # Init Curl Call #
        $ch = curl_init();
        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				if(isset($param['auth_token']) && $param['auth_token']!= ''){
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']),
						'HR-API-AUTH-TOKEN:'.$param['auth_token'])); 
				}else{
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']))); 
				}
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			}
		}
        $retVal = curl_exec($ch);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }
	//Curl Call func Ends
	
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
