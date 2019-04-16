<?php
if(!defined('APP_PATH'))
{
    require_once("../library/config.php");
}
include_once(APP_PATH."library/path.php");
include_once(APP_PATH."library/historyLog.php");
class vertical_auto_tagging extends DB
{
	public function __construct($parentid,$usercode,$username,$data_city,$module,$manual_ip_address=''){
		
		GLOBAL $dbarr;
		$this->conn_iro  	= 	new DB($dbarr['DB_IRO']);	
		$this->conn_local   = 	new DB($dbarr['LOCAL']);
		$this->conn_fnc 	= 	new DB($dbarr['DB_FNC']);
		$this->conn_idc		=	new DB($dbarr['IDC']);
		
		$this->parentid		=	trim($parentid);
		$this->ucode		=	trim($usercode);
		$this->uname		=	trim($username);
		$this->data_city 	=	trim($data_city);
		$this->module		=	trim($module);
		
		$this->city_ip = '';
		if(!empty($manual_ip_address))
		{
			$serverip = explode(".",$manual_ip_address);
			$arr_count = count($serverip);
			if($arr_count == 4)
			{
				$this->city_ip = $serverip[2];
			}
			else
			{
				if($_SERVER['SERVER_ADDR']!='')
				{
					$serverip = explode(".",$_SERVER['SERVER_ADDR']);
					$this->city_ip = $serverip[2];
				}
				else
				{
					if($_SERVER['argv'][1]!='')
					{
						$this->city_ip = $_SERVER['argv'][1];
					}
				}
			}
		}
		else
		{
			if($_SERVER['SERVER_ADDR']!='')
			{
				$serverip = explode(".",$_SERVER['SERVER_ADDR']);
				$this->city_ip = $serverip[2];
			}
			else
			{
				if($_SERVER['argv'][1]!='')
				{
					$this->city_ip = $_SERVER['argv'][1];
				}
			}
		}
		if($this->city_ip == '')
		{
			$mailto = "imteyaz.raja@justdial.com";
			$subject = "City IP is blank";
			$message = "Manual IP Address : ".$manual_ip_address."<br>SERVER ADDRESS : ".$_SERVER['SERVER_ADDR']."<br>Parentid : ".$parentid."<br>SERVER ARGUMENT : ".$_SERVER['argv'][1]."<br>Data City : ".$this->data_city."<br>Module : ".$this->module."<br>Flow : Vertical Auto Tagging";
			$from = "noreply@justdial.com";
			include_once("../library/class_email_sms_send.php");
			$emailsms_obj = new email_sms_send($dbarr);
			if($emailsms_obj)
			{
				$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'cs');
			}
		}
		$this->remote_city_flag = 0;
		if(defined("REMOTE_CITY_MODULE")){
			$this->remote_city_flag = 1;
		}
		
		$this->doc_id			=   $this->docid_creator($parentid);
		$this->gen_arr_main		=	$this->get_generalinfo_main();
		$this->extra_arr_main	=	$this->get_extradetails_main();
		$this->compmaster_obj	= 	new companyMasterClass($this->conn_iro,$this->data_city,$this->parentid);
		
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64'){
			$this->web_services_api = "http://sunnyshende.jdsoftware.com/web_services/web_services/";	
		}else{
			$this->web_services_api = "http://".WEB_SERVICES_API."/web_services/";
		}
		
		$this->cs_api = "http://".DE_CS_APP_URL;
		
		$this->doctor_catids = '';
		$this->hospital_catids = '';
		
		$this->live_categories_arr = $this->fetch_contract_live_categories();
		
		$this->all_verticals_list = $this->get_auto_tagging_process_verticals();
		$this->get_contract_level_untag_flag();
		$this->vertical_details_log = array();
		$this->contract_balance_flag = $this->getContractBalance(); // Balance Check
	
	}
	public function get_contract_level_untag_flag(){
		
		$contract_level_untag_flg = 0;
		$contract_level_untag_rsn = '';
		$contract_untag_rules_arr = $this->get_contract_level_untag_rules();
		
		if(count($contract_untag_rules_arr)>0)
		{
			foreach($contract_untag_rules_arr as $untag_rule_identifier)
			{
				switch(strtoupper($untag_rule_identifier))
				{
					case 'MASK' :
						if($this->extra_arr_main['mask'] == 1){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Mask";
						}
					break;
					case 'FREEZE' :
						if($this->extra_arr_main['freeze'] == 1){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Freeze";
						}
					break;
					case 'CLOSED DOWN' :
						if($this->extra_arr_main['closedown_flag'] == 1){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Closed Down";
						}
					break;
					case 'SHIFTED' :
						if($this->extra_arr_main['closedown_flag'] == 2){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Shifted";
						}
					break;
					case 'UNDER RENOVATION' :
						if($this->extra_arr_main['closedown_flag'] == 13){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Under Renovation";
						}
					break;
					case 'OPENING SHORTLY' :
						if($this->extra_arr_main['closedown_flag'] == 14){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Opening Shortly";
						}
					break;
					case 'TEMPORARY CLOSED DOWN' :
						if($this->extra_arr_main['closedown_flag'] == 15){
							$contract_level_untag_flg = 1;
							$contract_level_untag_rsn = "Temporary Closed Down";
						}
					break;
						
				}
				if($contract_level_untag_flg == 1){
					break;
				}
			}
		}
		$this->contract_level_untag_flg = $contract_level_untag_flg;
		$this->contract_level_untag_rsn = $contract_level_untag_rsn;
	}
	public function get_contract_level_untag_rules(){
		$untag_rules_arr = array();
		$sqlFetchUntagRules = "SELECT untag_rules FROM tbl_contract_level_untag_rules";
		$resFetchUntagRules = $this->conn_local->query_sql($sqlFetchUntagRules);
		if($resFetchUntagRules && mysql_num_rows($resFetchUntagRules))
		{
			$row_untag_rules = mysql_fetch_assoc($resFetchUntagRules);
			$untag_rules_str = trim($row_untag_rules['untag_rules']);
			$untag_rules_arr = json_decode($untag_rules_str,true);
		}
		return $untag_rules_arr;
	}
	public function get_sphinxid($parentid){
		$selectSql	=	"SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$parentid."'";
		$selectRes	=	$this->conn_iro->query_sql($selectSql);
		$selectRow	=	$this->conn_iro->fetchData($selectRes);
		$sphinx_id	=   $selectRow['sphinx_id'];
		return $sphinx_id;
	}
	
	public function getContractData(){
		$url = $this->web_services_api."rsvnInfo.php";
		$data = "docid=".$this->doc_id."&type_flag=2";
		$curl_response = $this->curl_call_post($url,$data);
		$contractdata = json_decode($curl_response,true);
		return $contractdata;
	}
	
	public function stdcode_master()
	{
		$sql_stdcode 	= 	"SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		
		$res_stdcode 	= 	$this->conn_local->query_sql($sql_stdcode);
		if($res_stdcode)
		{
			$row_stdcode 	= 	$this->conn_local->fetchData($res_stdcode);
			$stdcode 		= 	$row_stdcode['stdcode'];	
			if($stdcode[0]=='0')
			{
				$stdcode = $stdcode;
			}
			else
			{
				$stdcode = '0'.$stdcode;
			}
		}
		return $stdcode;
	}
	
	public function docid_creator($parentid)
	{
		GLOBAL $dbarr;
		$docid_stdcode 	= $this->stdcode_master();
		
		switch($this->city_ip){
			case 0:
				$docid = "022".$parentid;
				break;
			case 1:
			case 17:
				if($docid_stdcode){
					$temp_stdcode = ltrim($docid_stdcode,0);
				}
				$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
				if(in_array(strtoupper($this->data_city),$ArrCity)){
					$sqlStd		= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
					$resStd		= $this->conn_local->query_sql($sqlStd);
					$rowStd			=  mysql_fetch_array($resStd);
					$cityStdCode	=  $rowStd['stdcode'];
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
				break;
			case 8:
				$docid = "011".$parentid;
				break;
			case 16:
				$docid = "033".$parentid;
				break;
			case 26:
				$docid = "080".$parentid;
				break;
			case 32:
				$docid = "044".$parentid;
				break;
			case 40:
				$docid = "020".$parentid;
				break;
			case 50:
				$docid = "040".$parentid;
				break;
			case 56:
				$docid = "079".$parentid;
				break;
			case 64:
				if($this->remote_city_flag == 1)
				{
					if($docid_stdcode){
					$temp_stdcode = ltrim($docid_stdcode,0);
					}
					$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
					if(in_array(strtoupper($this->data_city),$ArrCity)){
						$sqlStd		= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
						$resStd		= $this->conn_local->query_sql($sqlStd);
						$rowStd			=  mysql_fetch_array($resStd);
						$cityStdCode	=  $rowStd['stdcode'];
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
				}
				else
				{
					$docid = "022".$parentid;
				}
				break;
			default:
				$docid = "022".$parentid;
				$mailto = "imteyaz.raja@justdial.com";
				$subject = "Docid Creator Issue";
				$message = "Sever Address : ".$_SERVER['SERVER_ADDR']."<br>City IP : ".$this->city_ip."<br>Parentid : ".$parentid."<br>Docid : ".$docid."<br>Data City : ".$this->data_city."<br>Module : ".$this->module."<br>Flow : Vertical Auto Tagging";
				$from = "noreply@justdial.com";
				include_once("../library/class_email_sms_send.php");
				$emailsms_obj = new email_sms_send($dbarr);
				if($emailsms_obj)
				{
					$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'cs');
				}
				break;			
		}
		
		return $docid;
	}
	public function getHospitalsList(){
		
		$childurl = $this->web_services_api."rsvnType.php";
		$childdata = "docid=".$this->doc_id."&type_flag=2&sub_type_flag=1&backend_flow=1";
		$childcurl_response = $this->curl_call_post	($childurl,$childdata);
		$childcontractdata = json_decode($childcurl_response,true); 
		return $childcontractdata;
	}
	
	public function fetchVerticalData($docid,$type_flag)
	{
		$fetch_vertical_data_res['results'] = array();
		$fetch_vertical_data_url = $this->web_services_api."fetchVerticalData.php?docid=".$docid."&type_flag=".$type_flag;
		$fetch_vertical_data_res = json_decode($this->curl_call_get($fetch_vertical_data_url),true);
		return $fetch_vertical_data_res['results'];
	}
	public function send_history_data($curlurl,$history_data)
	{	
		#echo $curlurl.'?'.$history_data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('history_data' => $history_data));
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
	public function populate_delivery_area($post_param,$doc_id,$new_parentid) {
		
		$data_city_val = DATA_CITY;
		if($post_param['update_delivery_area_flag'] == '1')
		{
			if($post_param['isentirecity'] == '1') 
			{
				$areacode     =	"SELECT fn_category_nearby_pincode_in_range_all(\"".$_SESSION['pincode']."\",'200') as pincode limit 100";
				$qryareacode  =	$this->conn_idc->query_sql($areacode,$connection);
				$resareacode  = mysql_fetch_array($qryareacode);
				$varSend      = $resareacode['pincode'];
			}
			else 
			{
				$varSend	=	 trim(trim($post_param['visiting_areas'],'~'),',');
			}
			
			if($_SERVER['SERVER_ADDR'] == '172.29.64.64') {
				$delivery_area_url =  "http://imteyazraja.jdsoftware.com/megenio/api/updateDeliveryArea.php";
			} else {
				$delivery_area_url =  GNO_URL."/api/updateDeliveryArea.php";
			}
			$delivery_area_data = "parentid=".$new_parentid."&docid=".$doc_id."&strDelivery=".$varSend."&service=doctor&module=CS&city=".$data_city_val;
			$delivery_area_res  = $this->curl_call_post($delivery_area_url,$delivery_area_data);
			$respArray	=	json_decode($delivery_area_res,true);
		}
		else
		{
			if($_SERVER['SERVER_ADDR'] == '172.29.64.64') {
				$delivery_area_url =  "http://imteyazraja.jdsoftware.com/megenio/api/updateDeliveryArea.php";
			} else {
				$delivery_area_url =  GNO_URL."/api/updateDeliveryArea.php";
			}
			$varSend = '';
			$delivery_area_data = "parentid=".$new_parentid."&docid=".$doc_id."&strDelivery=".$varSend."&service=doctor&module=CS&city=".$data_city_val;
			$delivery_area_res  = $this->curl_call_post($delivery_area_url,$delivery_area_data);
			$respArray	=	json_decode($delivery_area_res,true);
		}
		if($respArray['errorCode']	==	'1') {
			$response=	array();
			$response['results']['errorCode'] = "1";
			$response['results']['errorMsg'] = "data insertion fail";
		} else {
			$response=	array();
			$response['results']['errorCode'] = "0";
			$response['results']['errorMsg'] = "data inserted successfully";
		}
        return json_encode($response);												
	}
	public function curl_call_get($curl_url){

		$ch = curl_init($curl_url);
		$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
		
	}
	public function getStdcode()
	{
		$stdcode = trim($this->gen_arr_main['stdcode']);
		$stdcode = ltrim($stdcode,0);
		if(!intval($stdcode)>0)
		{
			$sql_stdcode 	= 	"SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		
			$res_stdcode 	= 	$this->conn_local->query_sql($sql_stdcode);
			if($res_stdcode)
			{
				$row_stdcode 	= 	$this->conn_local->fetchData($res_stdcode);
				$stdcode 		= 	$row_stdcode['stdcode'];	
				$stdcode = ltrim($stdcode,0);
			}
		}
		$stdcode = trim($stdcode);
		return $stdcode;
	}
	
	function slasher($arr)
	{
		if(is_array($arr))
		{
			foreach($arr as $key=>$value)
			{
				$arr[$key] = addslashes(stripslashes($value));
			}
		}
		else
		{
			$arr = addslashes(stripslashes($arr));
		}
		return $arr;
	}
	/*----Functions Related To Doctor Auto Activation----*/
	
	private function isvalidContractforAutomaticTagging()
	{
		$proceed_flag = 1;
		
		$existing_child_flag = $this->isExistingChidContract();
		
		if($existing_child_flag ==1){
			$proceed_flag = 0;
			$comment = "Existing Child Contract Found.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		$valid_balance_flag = $this->getContractBalance(); // Balance Check
		
		$live_category_arr = $this->fetchLiveCategories();
		
		$checkDocHospCat	= $this->auto_fetch_doctor_category($live_category_arr,'1'); // Passing 1 To Check Both Doctor and Hospital Categories
		
		$prev_tagged_flag = $this->isPreviouslyTagged(1); // Previously Tagged Check
		
		$prev_tagged_local_flag = $this->isPreviouslyTagged_Local(); // Previously Tagged Check On Local Server
				
		if(($checkDocHospCat == false) && ($prev_tagged_flag ==  1 || $prev_tagged_local_flag == 1))
		{
			$comment = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found.";
			$this->insert_auto_activation_log($comment);
			
			if($prev_tagged_flag == 1)
			{
				$narration  = "\n"."Process : Doctor - Auto DeActivation"."\n\n This Contract has been untagged for Doctor as Doctor/Hospital category has been removed.\n" ;
				$this->insertNarration($narration);
				$remarks = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found";
				
				$web_deact_url = $this->web_services_api."rsvnActivate.php?docid=".$this->doc_id."&active_flag=1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."";
				$web_deact_res = json_decode($this->curl_call_get($web_deact_url),true);
				
				
				/*--Handling For Deactivating Existing Doctor Child Contract Starts---*/
				$doc_child_contracts_list = $this->getHospitalsList();		
				$existing_doc_child_docid_arr = array();
				if(count($doc_child_contracts_list['results']['multilocation'])>0){
					foreach($doc_child_contracts_list['results']['multilocation'] as $doc_child_docid_val=>$doc_child_docid_data){
						if(trim($doc_child_docid_val) != trim($this->doc_id))
						{
							$existing_doc_child_docid_arr[] = $doc_child_docid_val;
						}
					}
					$existing_doc_child_docid_arr  = array_filter($existing_doc_child_docid_arr);
					$existing_doc_child_docid_arr  = array_unique($existing_doc_child_docid_arr);
				}
				if(count($existing_doc_child_docid_arr)>0){
					foreach($existing_doc_child_docid_arr as $existing_doc_child_docid_val){
						$existing_doc_child_pid_val = strstr(strtoupper($existing_doc_child_docid_val),'P');
						
						$narration  = "\n"."Process : Doctor - Auto DeActivation"."\n\n This Contract has been untagged for Doctor as Doctor/Hospital Category has been removed from Parent Contract [".$this->parentid."].\n" ;
						$this->insertNarration($narration,$existing_doc_child_pid_val);
						$remarks = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found in Parent Contract [".$this->parentid."]";
						
						$web_deact_url = $this->web_services_api."rsvnActivate.php?docid=".$existing_doc_child_docid_val."&active_flag=1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."";
						$web_deact_res = json_decode($this->curl_call_get($web_deact_url),true);
				
						$cs_deact_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$existing_doc_child_pid_val."&action=2&vertical_type_flag=2&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".$this->ucode;
						$cs_deact_res = json_decode($this->curl_call_get($cs_deact_url),true);
					}
				}
				/*--Handling For Deactivating Existing Doctor Child Contract Ends---*/
				
			}
			
			$cs_deact_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=2&vertical_type_flag=2&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".$this->ucode;
			$cs_deact_res = json_decode($this->curl_call_get($cs_deact_url),true);
			
			/*--Handling For Deactivating Existing Hospital Child Contract Starts---*/
			$hosp_child_contracts_list = $this->getDoctorsList();
			
			$existing_hosp_child_docid_arr = array();
			if(count($hosp_child_contracts_list['results']['hospital'])>0){
			foreach($hosp_child_contracts_list['results'] as $hosp_contracts_arr=>$hosp_contract_details){ 
					if(is_array($hosp_contract_details) && !empty($hosp_contract_details)){

						foreach($hosp_contract_details as $hosp_child_contracts_key=>$hosp_child_contracts_val){

							foreach($hosp_child_contracts_val as $hosp_child_docid_val=>$hosp_child_docid_data){
								if(trim($hosp_child_docid_val) != trim($this->doc_id))
								{
									$existing_hosp_child_docid_arr[] = $hosp_child_docid_val;
								}
							}
						}
					}
				}
				$existing_hosp_child_docid_arr = array_filter($existing_hosp_child_docid_arr);
				$existing_hosp_child_docid_arr = array_unique($existing_hosp_child_docid_arr);
			}
			if(count($existing_hosp_child_docid_arr)>0){
				foreach($existing_hosp_child_docid_arr as $existing_hosp_child_docid_val){
					$existing_hosp_child_pid_val = strstr(strtoupper($existing_hosp_child_docid_val),'P');
					
					$narration  = "\n"."Process : Doctor - Auto DeActivation"."\n\n This Contract has been untagged for Hospital as Doctor/Hospital Category has been removed from Parent Contract [".$this->parentid."].\n" ;
					$this->insertNarration($narration,$existing_hosp_child_pid_val);
					$remarks = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found in Parent Contract [".$this->parentid."]";
					
					$web_deact_url = $this->web_services_api."rsvnActivate.php?docid=".$existing_hosp_child_docid_val."&active_flag=1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."";
					$web_deact_res = json_decode($this->curl_call_get($web_deact_url),true);
			
					$cs_deact_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$existing_hosp_child_pid_val."&action=2&vertical_type_flag=2&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".$this->ucode;
					$cs_deact_res = json_decode($this->curl_call_get($cs_deact_url),true);
				}
			}
			/*--Handling For Deactivating Existing Hospital Child Contract Ends---*/
		}
		
		/*--------Handling For Deactivating Doctor Contracts Having Hospital category with Single Calendar ie Single Location Starts---*/
		
		$checkHospCat	= $this->auto_fetch_doctor_category($live_category_arr,'2'); // Passing 2 To Check Hospital Categories
		
		if(($checkHospCat == true) && ($prev_tagged_flag ==  1 || $prev_tagged_local_flag == 1))
		{
			$doc_child_contracts_list = $this->getHospitalsList();		
			$existing_doc_child_docid_arr = array();
			if(count($doc_child_contracts_list['results']['multilocation'])>0){
				foreach($doc_child_contracts_list['results']['multilocation'] as $doc_child_docid_val=>$doc_child_docid_data){
					if(trim($doc_child_docid_val) != trim($this->doc_id))
					{
						$existing_doc_child_docid_arr[] = $doc_child_docid_val;
					}
				}
				$existing_doc_child_docid_arr  = array_filter($existing_doc_child_docid_arr);
				$existing_doc_child_docid_arr  = array_unique($existing_doc_child_docid_arr);
			}
			$hosp_child_contracts_list = $this->getDoctorsList();
			
			$existing_hosp_child_docid_arr = array();
			if(count($hosp_child_contracts_list['results']['hospital'])>0){
			foreach($hosp_child_contracts_list['results'] as $hosp_contracts_arr=>$hosp_contract_details){ 
					if(is_array($hosp_contract_details) && !empty($hosp_contract_details)){

						foreach($hosp_contract_details as $hosp_child_contracts_key=>$hosp_child_contracts_val){

							foreach($hosp_child_contracts_val as $hosp_child_docid_val=>$hosp_child_docid_data){
								if(trim($hosp_child_docid_val) != trim($this->doc_id))
								{
									$existing_hosp_child_docid_arr[] = $hosp_child_docid_val;
								}
							}
						}
					}
				}
				$existing_hosp_child_docid_arr = array_filter($existing_hosp_child_docid_arr);
				$existing_hosp_child_docid_arr = array_unique($existing_hosp_child_docid_arr);
			}
			if(count($existing_doc_child_docid_arr)<=0 && count($existing_hosp_child_docid_arr)<=0)
			{
				if($prev_tagged_flag == 1)
				{
					$narration  = "\n"."Process : Doctor - Auto DeActivation"."\n\n This Contract has been untagged for Doctor as Hospital category Found.\n" ;
					$this->insertNarration($narration);
					$remarks = "Deactivating Doctor/Hospital Vertical As Hospital Category Found";
					
					$web_deact_url = $this->web_services_api."rsvnActivate.php?docid=".$this->doc_id."&active_flag=1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."";
					$web_deact_res = json_decode($this->curl_call_get($web_deact_url),true);
				}
				$cs_deact_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=2&vertical_type_flag=2&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".$this->ucode;
				$cs_deact_res = json_decode($this->curl_call_get($cs_deact_url),true);
			}
			
		}
		/*--------Handling For Deactivating Doctor Contracts Having Hospital category with Single Calendar ie Single Location Ends---*/
		
		if($valid_balance_flag !=1){
			$proceed_flag = 0;
			$comment = "Balance Not Found.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		
		$checkDoctorCat	= $this->auto_fetch_doctor_category($live_category_arr,'0'); // Passing 0 To Check Doctor Categories
		
		if($checkDoctorCat == false){
			$proceed_flag = 0;
			$comment = "Doctor Category Not Found.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		
		
		
		if($checkHospCat == true){
			$proceed_flag = 0;
			$comment = "Hospital Category Found.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		
		$valid_pid_flag = $this->check_valid_contract();
		
		if($valid_pid_flag !=1){
			$proceed_flag = 0;
			$comment = "Contract is either Closed Down OR Refrence Parentid is not null.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		
		$previous_tagged_flag = $this->isPreviouslyTagged(0); // Previously Tagged Check
		
		$hosp_chk_flag = 0;
		$sqlHospitalCheck = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND ((type_flag&2=2) OR (sub_type_flag = 2))";
		$resHospitalCheck = $this->conn_iro->query_sql($sqlHospitalCheck);
		if($resHospitalCheck && mysql_num_rows($resHospitalCheck)>0)
		{
			$hosp_chk_flag = 1;
		}
		
		if(($previous_tagged_flag == 1) || ($hosp_chk_flag == 1))
		{
			$proceed_flag = 0;
			$comment = "Contract is already tagged for Doctor / Hospital.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		
		$geninfo_mobile = $this->gen_arr_main['mobile'] ;
		$geninfo_mobile_arr = array();
		
		$geninfo_mobile_arr = explode(",",$geninfo_mobile);
		$geninfo_mobile_arr = array_filter($geninfo_mobile_arr);
		$geninfo_mobile_arr = array_unique($geninfo_mobile_arr);
		
		if(count($geninfo_mobile_arr) && !empty($geninfo_mobile_arr)){
		}else{
			$proceed_flag = 0;
			$comment = "Mobile Number Is Not Found.";
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		$doctor_clinic_time_arr = $this->auto_hours_of_operation($this->extra_arr_main['working_time_start'],$this->extra_arr_main['working_time_end']);
		if(($doctor_clinic_time_arr['doc_clinic_time'] == '') || ($doctor_clinic_time_arr['valid_timing_flag'] == '0'))
		{
			$proceed_flag = 0;
			$comment = "Invalid Working Time. Working Start Time : ".$this->extra_arr_main['working_time_start']." Working End Time : ".$this->extra_arr_main['working_time_end'];
			$this->insert_auto_activation_log($comment);
			return $proceed_flag;
		}
		else if($doctor_clinic_time_arr['doc_clinic_time'] !='')
		{
			$auto_clinic_time_arr = explode("|",$doctor_clinic_time_arr['doc_clinic_time']);
			$auto_clinic_time_arr = array_map('trim',$auto_clinic_time_arr);
			$auto_clinic_time_arr = array_filter($auto_clinic_time_arr);
			if((empty($auto_clinic_time_arr)) || (count($auto_clinic_time_arr) == 0))
			{
				$proceed_flag = 0;
				$comment = "Invalid Working Time. Working Start Time : ".$this->extra_arr_main['working_time_start']." Working End Time : ".$this->extra_arr_main['working_time_end'];
				$this->insert_auto_activation_log($comment);
				return $proceed_flag;
			}
		}
		
		return $proceed_flag;
	}
	private function getContractBalance()
	{
		$balance_chk_flag = 0;
		$sqlCheckBalance = "SELECT parentid,balance FROM tbl_companymaster_finance WHERE parentid='" . $this->parentid . "' AND balance>0";
		$resCheckBalance = $this->conn_fnc->query_sql($sqlCheckBalance);
		if($resCheckBalance && mysql_num_rows($resCheckBalance)>0){
			$balance_chk_flag = 1;
		}
		return $balance_chk_flag;
	}
	private function insert_auto_activation_log($comment)
	{
		$sqlDoctorAutoLog = "INSERT INTO tbl_doctor_auto_activation_log SET
							parentid 			= '".$this->parentid."',
							docid 				= '".$this->doc_id."',
							data_city 			= '".addslashes($this->data_city)."',
							company_name 		= '".addslashes(stripslashes($this->gen_arr_main['companyname']))."',
							insertdate 			= '".date("Y-m-d H:i:s")."',
							ucode 				= '".$this->ucode."',
							uname 				= '".$this->uname."',
							validation_code 	= '".$this->module."',
							comment 			= '".$comment."'";
		$resDoctorAutoLog = $this->conn_iro->query_sql($sqlDoctorAutoLog);
	}
	function isPreviouslyTagged($type_flag_chk = 0)
	{
		$tagged_flag = 0;
		$getContractData = $this->getContractData();
		
		if(($getContractData['results']['compdetails']['docid']!='') || ($getContractData['results']['compdetails']['parentid']!=''))
		{
			if($type_flag_chk == 0)
			{
				$tagged_flag= 1;
			}
			else if($type_flag_chk == 1)
			{
				$web_tf_value 	= $getContractData['results']['compdetails']['website_type_flag'];
				$iro_tf_value 	= $getContractData['results']['compdetails']['iro_type_flag']; 
				if((((int)$web_tf_value & 2) == 2) || (((int)$iro_tf_value & 2) == 2))
				{
					$tagged_flag= 1;
				}
			}			
		}
		return $tagged_flag;
	}
	function isPreviouslyTagged_Local()
	{
		$local_tagged_flag = 0;
		$vertical_status_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=1&vertical_type_flag=2";
		$vertical_status_res = json_decode($this->curl_call_get($vertical_status_url),true);
		if(($vertical_status_res['active_flag']==1) && (($vertical_status_res['web_active_flag'] == 1) || ($vertical_status_res['iro_active_flag'] == 1)))
		{
			$local_tagged_flag = 1;
		}
		return $local_tagged_flag;
	}
	
	public function get_generalinfo_main(){
	
		$sqlGeneralInfoMain	= "SELECT parentid,companyname,full_address,latitude,longitude,landline,mobile,email,paid,data_city,stdcode,company_callcnt FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		$resGeneralInfoMain	= $this->conn_iro->query_sql($sqlGeneralInfoMain);
		$rowGeneralInfoMain	= $this->conn_iro->fetchData($resGeneralInfoMain);
		
		return $rowGeneralInfoMain;
	}
	
	public function get_extradetails_main(){
	
		$sqlExtradetailsMain = "SELECT parentid,working_time_start,working_time_end,mask,freeze,closedown_flag FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resExtradetailsMain = $this->conn_iro->query_sql($sqlExtradetailsMain);
		$rowExtradetailsMain = $this->conn_iro->fetchData($resExtradetailsMain);
		
		return $rowExtradetailsMain;
	}
	private function auto_hours_of_operation($working_time_start,$working_time_end)
	{
		$timing_info_arr = array();
		$valid_timing_flag = 1;
		$new_timing_val = '';
		$working_time_start = trim($working_time_start);
		$working_time_end = trim($working_time_end);
		if(!empty($working_time_start) && !empty($working_time_end))
		{
			$sqlSelectTiming = "SELECT d_jds.fn_hours_o_operation('".$working_time_start."','".$working_time_end."') as hours_of_operation";
			$resSelectTiming = $this->conn_local->query_sql($sqlSelectTiming);
			if($resSelectTiming && mysql_num_rows($resSelectTiming)>0)
			{
				$row_timing = mysql_fetch_assoc($resSelectTiming);
				$selected_timing_val = $row_timing['hours_of_operation'];
			}
			$selected_timing_arr = array();
			$selected_timing_arr = explode("|",$selected_timing_val);
			$selected_timing_arr = array_map('trim',$selected_timing_arr);
			$new_timing_arr = array();
			foreach($selected_timing_arr as $selected_timing)
			{
				if (stripos($selected_timing,'closed') !== false){
					$new_timing_arr[] = "";
				}else{
					$new_timing_arr[] = $selected_timing;
				}
			}

			foreach($new_timing_arr as $new_timing)
			{
				$timing_arr = array();
				$timing_arr = explode("~",$new_timing);
				$timing_arr = array_filter($timing_arr);
				foreach($timing_arr as $timing_val)
				{
					$timing_val_arr[] = explode("-",$timing_val);
				}
			}
			if(count($timing_val_arr) >0 )
			{
				$timing_val_arr = array_filter($timing_val_arr);
				foreach($timing_val_arr as $final_timing_arr)
				{
					if(count($final_timing_arr) !=2)
					{
						$valid_timing_flag = 0;
						break;
					}
					else
					{
						$star_time = $final_timing_arr[0];
						$end_time  = $final_timing_arr[1];
						if(strtotime($end_time) <= strtotime($star_time))
						{
							$valid_timing_flag = 0;
							break;
						}
					}
				}
				
			}
			if(is_array($new_timing_arr) && count($new_timing_arr)>0)
			{
				$new_timing_val = implode("|",$new_timing_arr);
			}
		}
		else
		{
			$valid_timing_flag = 0;
		}
		$timing_info_arr['valid_timing_flag'] = $valid_timing_flag;
		$timing_info_arr['doc_clinic_time'] = $new_timing_val;
		
		return $timing_info_arr;
	}
	private function insertNarration($narration,$pid_new_val='')
	{
		if(!empty($pid_new_val)){
			$parentid_val = $pid_new_val;
		}else{
			$parentid_val = $this->parentid;
		}
		if(!empty($narration) && !empty($parentid_val))
		{
			$narration	.= "\n".  date("F j, Y, g:i a") ."\n--". $this->uname;
			$sqlInsertNarration = "INSERT INTO tbl_paid_narration SET
								   contractid = '".$parentid_val."',
								   narration = \"".addslashes($narration)."\",
								   creationDt = '".date("Y-m-d H:i:s")."',
								   createdBy = '".addslashes($this->ucode)."',
								   parentid = '".$parentid_val."',
								   data_city = '".addslashes($this->data_city)."'";
			$resInsertNarration = $this->conn_local->query_sql($sqlInsertNarration);
		}
	}
	private function fetchLiveCategories()
	{
		$final_catlin_arr = array();
		$sqlExtradetailsCategory = "SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		$resExtradetailsCategory = $this->conn_iro->query_sql($sqlExtradetailsCategory);
		if($resExtradetailsCategory && mysql_num_rows($resExtradetailsCategory))
		{
			$row_extra_category	=	mysql_fetch_assoc($resExtradetailsCategory);			
			if((isset($row_extra_category['catidlineage']) && $row_extra_category['catidlineage'] != '') || (isset($row_extra_category['catidlineage_nonpaid']) && $row_extra_category['catidlineage_nonpaid'] != ''))
			{
                $extra_catlin_arr 	= 	array();
				$extra_catlin_arr   =   explode("/,/",trim($row_extra_category['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				
				$extra_catlin_np_arr = array();
				$extra_catlin_np_arr = explode("/,/",trim($row_extra_category['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($extra_catlin_arr,$extra_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				if(count($total_catlin_arr)>0)
				{
					foreach($total_catlin_arr as $catidval)
					{
						$catidval = trim($catidval,',');
						$catidval = trim($catidval,'/');
						$final_catlin_arr[] = $catidval;
					}
				}
				$final_catlin_arr = array_filter($final_catlin_arr);
				$final_catlin_arr = array_unique($final_catlin_arr);
			}
		}
		return $final_catlin_arr;
	}
	private function auto_fetch_doctor_category($live_cat_arr,$doc_hosp_cat_chk = 0)
	{
		$response_flag = false;
		if($doc_hosp_cat_chk == 1){ // 0 - Only Doctor 1- Both , 2- Only Hospital 
			$display_product_flag_condn = "((display_product_flag&4=4) OR (display_product_flag&8=8))";
		}elseif($doc_hosp_cat_chk == 2){
			$display_product_flag_condn = "display_product_flag&8=8";
		}else{
			$display_product_flag_condn = "display_product_flag&4=4";
		}
		
		if(!empty($live_cat_arr) && count($live_cat_arr)>0)
		{
			$live_catids = implode("','",$live_cat_arr);
			$selectCatSql	=	"select count(catid) as cnt,GROUP_CONCAT(catid) AS doctor_catids  from d_jds.tbl_categorymaster_generalinfo where active_flag=1 and ".$display_product_flag_condn." AND catid IN ('".$live_catids."')";
			$selectCatRes	=	$this->conn_local->query_sql($selectCatSql);
			$selectCatRow	=	mysql_fetch_assoc($selectCatRes);
			if($selectCatRow['cnt'] > 0){
				$this->doctor_catids = $selectCatRow['doctor_catids'];
				$response_flag = true;
			}
		}
		return $response_flag;
	}
	
	public function curl_call_post($curlurl,$inputdata){	
		#echo $input_url.'?'.$input_data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inputdata);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
	public function call_rsvnArea_API($hospital_docid)
	{
		$rsvn_area_url  = $this->web_services_api."rsvnArea.php";
		$rsvn_area_data = "docid=".$hospital_docid."&type_flag=2";
		$curl_area_res = $this->curl_call_post($rsvn_area_url,$rsvn_area_data);
		$get_area_data = json_decode($curl_area_res,true);
        return $get_area_data;
    }
	public function getDoctorsList(){
		
		$childurl = $this->web_services_api."rsvnType.php";
		$childdata = "docid=".$this->doc_id."&type_flag=2&sub_type_flag=2&backend_flow=1";
		$childcurl_response = $this->curl_call_post	($childurl,$childdata);
		$childcontractdata = json_decode($childcurl_response,true); 
		return $childcontractdata;
	}
	
	/*----New Function Added Here---*/
	public function automaticVerticalActivation()
	{
		if($this->city_ip == ''){
			//Send Mail
			return;
		}
		print"<pre>";print_r($this->all_verticals_list);
		
		$vertical_auto_process_info_arr = array();
		
		if(count($this->all_verticals_list)>0)
		{
			if($this->contract_level_untag_flg == 1)
			{
				$vertical_abbrevation_arr = array_keys($this->all_verticals_list);
				
				foreach($vertical_abbrevation_arr as $vertical_abbrevation)
				{
					$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_abbr'] = $vertical_abbrevation;
					$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_name'] = $this->all_verticals_list[$vertical_abbrevation]['vertical_name'];
					$vertical_auto_process_info_arr[$vertical_abbrevation]['type_flag'] 	= $this->all_verticals_list[$vertical_abbrevation]['type_flag'];
					$vertical_auto_process_info_arr[$vertical_abbrevation]['action'] 		= 'Untag';
					$vertical_auto_process_info_arr[$vertical_abbrevation]['reason'] 		= $this->contract_level_untag_rsn;
				}
			}
			else
			{
				if(count($this->live_categories_arr)>0)
				{
					$live_categories_str = implode("','",$this->live_categories_arr);
					$sqlCategoryInfo = "SELECT catid,category_name,display_product_flag,brand_name,rest_price_range FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$live_categories_str."')";
					$resCategoryInfo = $this->conn_local-> query_sql($sqlCategoryInfo);
					if($resCategoryInfo && mysql_num_rows($resCategoryInfo)>0)
					{
						$category_matched_verticals_arr = array();
						
						while($row_category = mysql_fetch_assoc($resCategoryInfo))
						{
							$catid 					= trim($row_category['catid']);
							$category_name 			= trim($row_category['category_name']);
							$display_product_flag 	= trim($row_category['display_product_flag']);
							$brand_name 			= trim($row_category['brand_name']);
							$categoryname_display 	= trim($row_category['rest_price_range']);
							
							$vertical_catlist_str = $vertical_abbr.'catlist';
							
							foreach($this->all_verticals_list as $vertical_abbr => $vertical_details_arr)
							{
								if(((int)$display_product_flag&$vertical_details_arr['display_product_flag']) == $vertical_details_arr['display_product_flag'])
								{
									$category_matched_verticals_arr[] = $vertical_abbr;
									$this->vertical_details_log[$vertical_abbr]['catlist']	.=	($this->vertical_details_log[$vertical_abbr]['catlist'])	?	",".$category_name	:	$category_name;
								}
							}
						}
						if(count($category_matched_verticals_arr)>0)
						{
							$category_matched_verticals_arr = array_unique($category_matched_verticals_arr);
						}
					}
					$all_verticals_abbr_list_arr = array_keys($this->all_verticals_list);
					
					foreach($all_verticals_abbr_list_arr as $vertical_abbrevation)
					{
						if(in_array($vertical_abbrevation,$category_matched_verticals_arr))
						{
							$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_abbr'] = $vertical_abbrevation;
							$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_name'] = $this->all_verticals_list[$vertical_abbrevation]['vertical_name'];
							$vertical_auto_process_info_arr[$vertical_abbrevation]['type_flag'] 	= $this->all_verticals_list[$vertical_abbrevation]['type_flag'];
							$vertical_auto_process_info_arr[$vertical_abbrevation]['action'] 		= 'Tag';
							$vertical_auto_process_info_arr[$vertical_abbrevation]['reason'] 		= 'Category Found';
						}
						else
						{
							$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_abbr'] = $vertical_abbrevation;
							$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_name'] = $this->all_verticals_list[$vertical_abbrevation]['vertical_name'];
							$vertical_auto_process_info_arr[$vertical_abbrevation]['type_flag'] 	= $this->all_verticals_list[$vertical_abbrevation]['type_flag'];
							$vertical_auto_process_info_arr[$vertical_abbrevation]['action'] 		= 'Untag';
							$vertical_auto_process_info_arr[$vertical_abbrevation]['reason'] 		= 'Category Not Found';
						}
					}
				}
				else
				{
					$vertical_abbrevation_arr = array_keys($this->all_verticals_list);
					foreach($vertical_abbrevation_arr as $vertical_abbrevation)
					{
						$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_abbr'] = $vertical_abbrevation;
						$vertical_auto_process_info_arr[$vertical_abbrevation]['vertical_name'] = $this->all_verticals_list[$vertical_abbrevation]['vertical_name'];
						$vertical_auto_process_info_arr[$vertical_abbrevation]['type_flag'] 	= $this->all_verticals_list[$vertical_abbrevation]['type_flag'];
						$vertical_auto_process_info_arr[$vertical_abbrevation]['action'] 		= 'Untag';
						$vertical_auto_process_info_arr[$vertical_abbrevation]['reason'] 		= 'Category Not Found';
					}
				}
			}
		}
		else
		{
			// Send Mail
		}
		if(count($vertical_auto_process_info_arr))
		{
			//print"<pre>";print_r($vertical_auto_process_info_arr);
			
			// for $this->contract_level_untag_flg == 1 deallocate blindly
			// for $this->contract_level_untag_flg != 1 check exclusion type
			
			$this->performVerticalAutoProcessing($vertical_auto_process_info_arr);
		}
	}
	function performVerticalAutoProcessing($vertical_auto_process_info_arr)
	{
		print"<pre>";print_r($vertical_auto_process_info_arr);
		
		if($this->contract_level_untag_flg == 1)
		{
			foreach($vertical_auto_process_info_arr as $vertical_abbr => $vertical_details)
			{
				switch(strtoupper($vertical_abbr))
				{
					case 'DR' :
						$this->deactivateDoctorReservation($vertical_abbr);
						$this->finalProcessingDoctorReservation($vertical_abbr);
						
					break;
					case 'PS' :
						$this->deactivatePathologyService($vertical_abbr);
					break;
				}
			}
		}
		else
		{
			foreach($vertical_auto_process_info_arr as $vertical_abbr => $vertical_details)
			{
				switch(strtoupper($vertical_abbr))
				{
					case 'DR' :
							if($vertical_auto_process_info_arr[$vertical_abbr]['action'] == 'Tag')
							{
								$this->activateDoctorReservation($vertical_abbr);
							}
							else if($vertical_auto_process_info_arr[$vertical_abbr]['action'] == 'Untag')
							{
								$this->deactivateDoctorReservation($vertical_abbr);
							}
							$this->finalProcessingDoctorReservation($vertical_abbr);
					break;
					case 'PS' :
						if($vertical_auto_process_info_arr[$vertical_abbr]['action'] == 'Tag')
						{
							$this->activatePathologyService($vertical_abbr);
						}
						else if($vertical_auto_process_info_arr[$vertical_abbr]['action'] == 'Untag')
						{
							//$this->deactivatePathologyService($vertical_abbr);
						}
					break;
				}
			}
		}
	}
	function get_auto_tagging_process_verticals()
	{
		$all_verticals_arr = array();
		$sqlAllVerticals = "SELECT vertical_name,vertical_abbr,display_product_flag,type_flag_value,campaign_id FROM tbl_auto_tagging_process_verticals WHERE active_flag =1 ORDER BY priority_flag";
		$resAllVerticals = $this->conn_local->query_sql($sqlAllVerticals);
		if($resAllVerticals && mysql_num_rows($resAllVerticals)>0)
		{
			while($row_all_vertilcals = mysql_fetch_assoc($resAllVerticals))
			{
				$vertical_name 			= trim($row_all_vertilcals['vertical_name']);
				$vertical_abbr 			= trim($row_all_vertilcals['vertical_abbr']);
				$display_product_flag 	= trim($row_all_vertilcals['display_product_flag']);
				$type_flag 				= trim($row_all_vertilcals['type_flag_value']);
				$campaign_id 			= trim($row_all_vertilcals['campaign_id']);
				$all_verticals_arr[$vertical_abbr]['vertical_name'] 		= $vertical_name;
				$all_verticals_arr[$vertical_abbr]['display_product_flag'] 	= $display_product_flag;
				$all_verticals_arr[$vertical_abbr]['type_flag'] 			= $type_flag;
				$all_verticals_arr[$vertical_abbr]['campaign_id'] 			= $campaign_id;
			}
		}
		return $all_verticals_arr;
	}
	function fetch_contract_live_categories()
	{
		$live_catlin_arr = array();
		$sqlExtradetailsCategory = "SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resExtradetailsCategory = $this->conn_iro->query_sql($sqlExtradetailsCategory);
		if($resExtradetailsCategory && mysql_num_rows($resExtradetailsCategory))
		{
			$row_extra_category	=	mysql_fetch_assoc($resExtradetailsCategory);
			if((isset($row_extra_category['catidlineage']) && $row_extra_category['catidlineage'] != '') || (isset($row_extra_category['catidlineage_nonpaid']) && $row_extra_category['catidlineage_nonpaid'] != ''))
			{
				$extra_catlin_arr 	= 	array();
				$extra_catlin_arr   =   explode("/,/",trim($row_extra_category['catidlineage'],"/"));
				$extra_catlin_arr 	= 	array_filter($extra_catlin_arr);
				
				$extra_catlin_np_arr = array();
				$extra_catlin_np_arr = explode("/,/",trim($row_extra_category['catidlineage_nonpaid'],"/"));
				$extra_catlin_np_arr = array_filter($extra_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($extra_catlin_arr,$extra_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$live_catlin_arr = $this->get_valid_categories($total_catlin_arr);			
			}
		}
		return $live_catlin_arr; 
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
	function isUnderExclusionList($vertical_type_flag)
	{
		$exclusion_list_flg = false;
		$sqlExclusionListChk = "SELECT parentid FROM tbl_exclusion_list_verticals WHERE parentid='".$this->parentid."' AND vertical_type_flag = '".$vertical_type_flag."'";
		$resExclusionListChk = $this->conn_iro ->query_sql($sqlExclusionListChk);
		if($resExclusionListChk && mysql_num_rows($resExclusionListChk)>0)
		{
			$exclusion_list_flg = true;
		}
		return $exclusion_list_flg;
	}
	/*--Vertical Activation Function Starts--*/
	function activateDoctorReservation($vertical_abbr)
	{
		$doctor_type_flg = $this->all_verticals_list[$vertical_abbr]['type_flag'];
		if(intval($doctor_type_flg)>0)
		{
			$doc_proceed_flag = 1;
			
			$doctor_ignore_keyword = $this->get_vertical_ignore_keyword($vertical_abbr);
			
			if(($doc_proceed_flag == 1) && (count($doctor_ignore_keyword)>0))
			{
				foreach($doctor_ignore_keyword as $doc_ignore_keyword)
				{
					switch(strtoupper($doc_ignore_keyword))
					{
						case 'EXCLUSION LIST' :
							$doc_exclusion_list_flg = $this->isUnderExclusionList($doctor_type_flg);
							if($doc_exclusion_list_flg)
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Exclusion List';
							}
						break;
						case 'EXISTING HOSPITAL CONTRACT' :
							$hosp_child_contracts = $this->getHospChildContracts();
							if($hosp_child_contracts['error'] == 0)
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Already Tagged For Hospital. Child Contract : '.$hosp_child_contracts['child_docids'];
							}
							else
							{
								$hosp_tagged_chk_local = $this->hospTaggedFlag_Local();
								if($hosp_tagged_chk_local == 1)
								{
									$doc_proceed_flag = 0;
									$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
									$this->vertical_details_log[$vertical_abbr]['reason'] = 'Already Tagged For Hospital';
								}
							}
						break;
						case 'HOSPITAL CATEGORY EXISTS' :
							$hosp_cat_chk_flag = $this->isHospitalCategoryExists();
							if($hosp_cat_chk_flag == 1)
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Hospital Category Found. Hospital Tagged Category/s : '.$this->hospital_catids;
							}
						break;
						
						case 'DOCTOR CHILD CONTRACT' :
							$doc_child_contract_flag = $this->isExistingChidContract(); 
							if($doc_child_contract_flag){								
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Existing Doctor Child Contract. Ref Parentid : '.$this->doc_child_ref_parentid;
								
							}
						break;
						
						
					}
					if($doc_proceed_flag == 0){
						break;
					}
				}
			}
			
			$doctor_tagging_rules = $this->fetch_auto_tagging_vertical_rules($vertical_abbr);
			
			if(($doc_proceed_flag == 1) && ($doctor_tagging_rules['common_mandatory_conditions'] !=null))
			{
				$doc_mandatory_condn_arr = json_decode($doctor_tagging_rules['common_mandatory_conditions'],true);
				
				foreach($doc_mandatory_condn_arr as $condition_identifier)
				{
					switch(strtoupper($condition_identifier))
					{
						case 'MOBILE OR LANDLINE' :
							$mobile_num_arr 	= $this->geninfo_mobile();
							$landline_num_arr 	= $this->geninfo_landline();
							if((count($mobile_num_arr)<=0) && (count($landline_num_arr)<=0))
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Mobile Number or Landline Number Not Found';
							}
						break;
						case 'MOBILE' :
							$mobile_num_arr 	= $this->geninfo_mobile();
							if(count($mobile_num_arr)<=0)
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Mobile Number Not Found';
							}
						break;
						case 'LANDLINE' :
							$landline_num_arr 	= $this->geninfo_landline();
							if(count($landline_num_arr)<=0)
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Landline Number Not Found';
							}
						break;
						case 'MOBILE AND LANDLINE' :
							$mobile_num_arr 	= $this->geninfo_mobile();
							$landline_num_arr 	= $this->geninfo_landline();
							if((count($mobile_num_arr)<=0) || (count($landline_num_arr)<=0))
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Mobile Number and Landline Number Not Found';
							}
						break;
						case 'BFORM_HOP OR DEFAULT_DOCTOR_HOP' :
							$doc_bform_valid_timing_flag = 1;
							$doctor_clinic_time_arr = $this->auto_hours_of_operation($this->extra_arr_main['working_time_start'],$this->extra_arr_main['working_time_end']);
							if(($doctor_clinic_time_arr['doc_clinic_time'] == '') || ($doctor_clinic_time_arr['valid_timing_flag'] == '0'))
							{
								$doc_bform_valid_timing_flag = 0;
							}
							else if($doctor_clinic_time_arr['doc_clinic_time'] !='')
							{
								$auto_clinic_time_arr = explode("|",$doctor_clinic_time_arr['doc_clinic_time']);
								$auto_clinic_time_arr = array_map('trim',$auto_clinic_time_arr);
								$auto_clinic_time_arr = array_filter($auto_clinic_time_arr);
								if((empty($auto_clinic_time_arr)) || (count($auto_clinic_time_arr) == 0))
								{
									$doc_bform_valid_timing_flag = 0;
								}
							}
							if($doc_bform_valid_timing_flag == 0)
							{
								$doc_default_value = $this->fetch_vertical_default_value($vertical_abbr);
								$doc_default_timing_str = $doc_default_value['HOP'];
								if($doc_default_timing_str)
								{
									$doc_default_timing_arr = json_decode($doc_default_timing_str,true);
									$doc_hours_of_operation = $this->format_hours_of_operation($vertical_abbr,$doc_default_timing_arr);
								}
							}
							else
							{
								$doc_hours_of_operation = $doctor_clinic_time_arr['doc_clinic_time'];
							}
							if(empty($doc_hours_of_operation))
							{
								$doc_proceed_flag = 0;
								$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
								$this->vertical_details_log[$vertical_abbr]['reason'] = 'Hours Of Operation Not Found';
							}
							else
							{
								$this->doc_hours_of_operation_val = $doc_hours_of_operation;
							}
						break;
					}
					if($doc_proceed_flag == 0){
						break;
					}
				}
			}
			if(($doc_proceed_flag == 1) && (!empty($doctor_tagging_rules['eligible_contract_type'])))
			{
				$doc_eligible_contract_type = $doctor_tagging_rules['eligible_contract_type'];
				$doc_eligible_contract_type_arr = explode(",",$doc_eligible_contract_type);
				$doc_eligible_contract_type_arr = array_map('strtoupper', $doc_eligible_contract_type_arr);
				
				if(in_array("PAID",$doc_eligible_contract_type_arr))
				{
					if($this->contract_balance_flag ==1)
					{
						$DoctorAutoTaggingFlag = 1;
					}
				}
				if($DoctorAutoTaggingFlag !=1)
				{
					if(in_array("NONPAID",$doc_eligible_contract_type_arr))
					{
						$doc_tagging_rules = $doctor_tagging_rules['tagging_rules'];
						$doc_tagging_rules_arr = json_decode($doc_tagging_rules,true);
						if(count($doc_tagging_rules_arr)>0 && is_array($doc_tagging_rules_arr['NONPAID']))
						{
							foreach($doc_tagging_rules_arr['NONPAID'] as $nonpaid_rule_key => $nonpaid_rule_value)
							{
								switch($nonpaid_rule_key)
								{
									case 'CALL_COUNT' :
										$company_callcnt = $this->geninfo_company_callcnt();
										if(intval($company_callcnt) < intval($nonpaid_rule_value))
										{
											$doc_proceed_flag = 0;
											$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
											$this->vertical_details_log[$vertical_abbr]['reason'] = 'Company Call Count Condition Fail. Company Callcnt : '.$company_callcnt. ' As Per Rule : '.$nonpaid_rule_value;
										}
									break;
									case 'COMBINED' : 
										parse_str($nonpaid_rule_value, $nonpaid_combined_rules);
										if(count($nonpaid_combined_rules)>0)
										{
											$combined_rule_check = 0;
											$edit_done_check = 0;
											$review_rating_check = 0;
											$combined_callcnt_check = 0;
											foreach($nonpaid_combined_rules as $combined_rule_key => $combined_rule_value)
											{
												switch($combined_rule_key)
												{
													case 'EDIT_DONE' : 												
														$edit_done_check = $this->contract_edit_done_flag($combined_rule_value);
														$combined_log_str = "Edit not done in ".$combined_rule_value." years.";
													break;
													case 'REVIEW_RATING' :
														//$review_rating_check = $this->contract_review_rating_flag();
														$combined_log_str .= " No Review/Rating in last ".$combined_rule_value." years.";
													break;
													case 'CALL_COUNT' : 
														$combined_company_callcnt = $this->geninfo_company_callcnt();
														if(intval($combined_company_callcnt) >= intval($combined_rule_value))
														{
															$combined_callcnt_check =1;
														}
														$combined_log_str .= " Company Callcnt : ".$combined_company_callcnt." As Per Rule : ".$combined_rule_value;
													break;
													
												}
												if(($edit_done_check == 1) || ($review_rating_check == 1) || ($combined_callcnt_check == 1))
												{
													$combined_rule_check = 1;
													break;
												}
											}
											if($combined_rule_check !=1)
											{
												$doc_proceed_flag = 0;
												$this->vertical_details_log[$vertical_abbr]['status'] = 'Not Changed';
												$this->vertical_details_log[$vertical_abbr]['reason'] = $combined_log_str;
											}
										}
									break;
								}
								if($doc_proceed_flag == 0){
									break;
								}
							}
						}
						if($doc_proceed_flag == 1)
						{
							$DoctorAutoTaggingFlag = 1;
						}
					}
				}
			}
			if($DoctorAutoTaggingFlag == 1)
			{
				$doc_bform_info = $this->fetchDoctorBFormInfo($vertical_abbr);
				
				print"<pre>";print_r($doc_bform_info);
				
				if($doc_bform_info['tagged_flag'] == 1)
				{
					
					
					
					$prev_tagged_local_flag = $this->isPreviouslyTagged_Local(); // Previously Tagged Check On Local Server
				
					if(($prev_tagged_flag ==  1 || $prev_tagged_local_flag == 1))
					{
						$comment = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found.";
						$this->insert_auto_activation_log($comment);
						
						if($prev_tagged_flag == 1)
						{
							$narration  = "\n"."Process : Doctor - Auto DeActivation"."\n\n This Contract has been untagged for Doctor as Doctor/Hospital category has been removed.\n" ;
							$this->insertNarration($narration);
							$remarks = "Deactivating Doctor/Hospital Vertical As Doctor/Hospital Category Not Found";
							
							$web_deact_url = $this->web_services_api."rsvnActivate.php?docid=".$this->doc_id."&active_flag=1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."";
							$web_deact_res = json_decode($this->curl_call_get($web_deact_url),true);
						}
					}
					
					
					
					
					
				}
				else
				{
					$time_stamp = date_create();
					$uniqueid 	= date_format($time_stamp, 'U');
					$doctorActivationDataArr = $this->doctorActivationData();
			
					//$this->automatic_populate_hospital_data($doctorActivationDataArr['doc_param'],$doctorActivationDataArr['hos_param'],$uniqueid);
					//$this->automatic_populate_doctor_data($doctorActivationDataArr['doc_param'],$doctorActivationDataArr['hos_param'],$uniqueid);
				}
			}
		}
		else
		{
			//Send Mail
		}
		print"<pre>";print_r($this->vertical_details_log);
		
		// Handling to deactivate Pharmacy/Pathology/Books if doctor is tagged already
		
	}
	function fetchDoctorBFormInfo($vertical_abbr)
	{
		$tagInfoArr 	= array();
		$tagged_flag 	= 0;
		$tagged_status	= 0;
		$doctor_type_flg = $this->all_verticals_list[$vertical_abbr]['type_flag'];
		$doc_vertical_data = $this->getVerticalBformData($doctor_type_flg);
		if(($doc_vertical_data['results']['compdetails']['docid']!='') || ($doc_vertical_data['results']['compdetails']['parentid']!=''))
		{
			$tagged_flag= 1;
			$web_tf_value 	= $doc_vertical_data['results']['compdetails']['website_type_flag'];
			$iro_tf_value 	= $doc_vertical_data['results']['compdetails']['iro_type_flag']; 
			if((((int)$web_tf_value & 2) == 2) || (((int)$iro_tf_value & 2) == 2))
			{
				$tagged_status= 1;
			}
		}
		$tagInfoArr['tagged_flag'] = $tagged_flag;
		$tagInfoArr['tagged_status'] = $tagged_status;
		return $tagInfoArr;
	}
	function getVerticalPresentStatus($vertical_abbr)
	{
		$local_tagged_flag = 0;
		$vertical_status_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=1&vertical_type_flag=2";
		$vertical_status_res = json_decode($this->curl_call_get($vertical_status_url),true);
		if(($vertical_status_res['active_flag']==1) && (($vertical_status_res['web_active_flag'] == 1) && ($vertical_status_res['iro_active_flag'] == 1)))
		{
			$local_tagged_flag = 1;
		}
		return $local_tagged_flag;
	}
	
	
	public function getVerticalBformData($doctor_type_flg){
		$url = $this->web_services_api."rsvnInfo.php";
		$data = "docid=".$this->doc_id."&type_flag=".$doctor_type_flg;
		//echo $url."?".$data; 
		$curl_response = $this->curl_call_post($url,$data);
		$contractdata = json_decode($curl_response,true);
		return $contractdata;
	}
	function finalProcessingDoctorReservation($vertical_abbr)
	{
		echo "inside final processing";
	}
	function format_hours_of_operation($vertical_abbr,$hop_arr)
	{
		$hours_of_operation = '';
		if(count($hop_arr)>0)
		{		
			switch($vertical_abbr)
			{
				case 'DR' :
					foreach($hop_arr as $hop_val)
					{
						if($hop_val == '00:00-00:00')
						{
							$doc_hop_arr[] = '';
						}
						else
						{
							$doc_hop_arr[] = $hop_val;
						}
					}
					$hours_of_operation = implode("|",$doc_hop_arr)."|"; //05:00-07:00|05:00-07:00|05:00-07:00|05:00-07:00|05:00-07:00|05:00-07:00||
				break;
			}
		}
		return $hours_of_operation;
	}
	function get_vertical_ignore_keyword($vertical_abbr)
	{
		$ignore_keyword_arr = array();
		$sqlVerticalIgnoreKeyword = "SELECT ignore_keyword FROM tbl_vertical_ignore_keyword WHERE vertical_abbr = '".$vertical_abbr."'";
		$resVerticalIgnoreKeyword = $this->conn_local->query_sql($sqlVerticalIgnoreKeyword);
		if($resVerticalIgnoreKeyword && mysql_num_rows($resVerticalIgnoreKeyword))
		{
			$row_ignore_keyword = mysql_fetch_assoc($resVerticalIgnoreKeyword);
			$ignore_keyword_str = trim($row_ignore_keyword['ignore_keyword']);
			$ignore_keyword_arr = json_decode($ignore_keyword_str,true);
		}
		return $ignore_keyword_arr;
	}
	function fetch_auto_tagging_vertical_rules($vertical_abbr)
	{
		$sqlVerticalRules = "SELECT common_mandatory_conditions,eligible_contract_type,tagging_rules FROM tbl_auto_tagging_vertical_rules WHERE vertical_abbr = '".$vertical_abbr."'";
		$resVerticalRules = $this->conn_local->query_sql($sqlVerticalRules);
		if($resVerticalRules && mysql_num_rows($resVerticalRules)>0)
		{
			$row_vertical_rules = mysql_fetch_assoc($resVerticalRules);
			return $row_vertical_rules;
		}
	}
	function fetch_vertical_default_value($vertical_abbr)
	{
		$vertical_default_val_arr = array();
		$sqlVerticalDefaultValue = "SELECT default_key,default_value FROM tbl_vertical_default_value WHERE vertical_abbr = '".$vertical_abbr."'";
		$resVerticalDefaultValue = $this->conn_local->query_sql($sqlVerticalDefaultValue);
		if($resVerticalDefaultValue && mysql_num_rows($resVerticalDefaultValue)>0)
		{
			while($row_default_value = mysql_fetch_assoc($resVerticalDefaultValue))
			{
				$default_key 	= trim($row_default_value['default_key']);
				$default_value 	= trim($row_default_value['default_value']);
				$vertical_default_val_arr[$default_key] = $default_value;
			}
		}
		return $vertical_default_val_arr;
	}
	function geninfo_mobile()
	{
		$geninfo_mobile_arr = array();
		$geninfo_mobile = $this->gen_arr_main['mobile'] ;
							
		$geninfo_mobile_arr = explode(",",$geninfo_mobile);
		$geninfo_mobile_arr = array_unique(array_filter($geninfo_mobile_arr));
		return $geninfo_mobile_arr;
	}
	function geninfo_landline()
	{
		$geninfo_landline_arr = array();
		$geninfo_landline = $this->gen_arr_main['landline'] ;
							
		$geninfo_landline_arr = explode(",",$geninfo_landline);
		$geninfo_landline_arr = array_unique(array_filter($geninfo_landline_arr));
		return $geninfo_landline_arr;
	}
	function geninfo_company_callcnt()
	{
		$company_callcnt = 0;
		$geninfo_company_callcnt = $this->gen_arr_main['company_callcnt'] ;
		if(intval($geninfo_company_callcnt)>0){
			$company_callcnt = $geninfo_company_callcnt;
		}
		
		return $company_callcnt;
	}
	function contract_edit_done_flag($year)
	{
		$edit_done_flag = 0;
		$required_timestamp = strtotime("-".$year." years");
		$required_edit_date =  date("Y-m-d H:i:s", $required_timestamp);
		$sqlEditDoneCheck = "SELECT parentid FROM tbl_contract_update_trail WHERE parentid = '".$this->parentid."' AND update_time >= '".$required_edit_date."' LIMIT 1";
		$resEditDoneCheck = $this->conn_local->query_sql($sqlEditDoneCheck);
		if($resEditDoneCheck && mysql_num_rows($resEditDoneCheck)>0)
		{
			$edit_done_flag = 1;
		}
		return $edit_done_flag;
	}
	public function isExistingChidContract()
	{
		$chlid_contract_flag = 0;
		$temparr		= array();
		$fieldstr 		= "parentid,ref_parentid";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid = '".$this->parentid."' AND ref_parentid  IS NOT NULL AND ref_parentid != '' AND ref_parentid != '0' AND parentid != ref_parentid";
		$this->compmaster_obj->set_datacity($this->parentid);
		$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		if($temparr['numrows']>0)
		{
			$row_child_contract = $temparr['data']['0'];
			$this->doc_child_ref_parentid = $row_child_contract['ref_parentid'];
			$chlid_contract_flag = 1;
		}
		return $chlid_contract_flag;
	}
	public function getHospChildContracts()
	{
		$hospchildurl 	= $this->web_services_api."getHospChildContracts.php";
		$hospchilddata 	= "parent_docid=".$this->doc_id;
		$hospchild_api = $this->curl_call_post	($hospchildurl,$hospchilddata);
		$hospchild_res = json_decode($hospchild_api,true); 
		return $hospchild_res;
	}
	public function hospTaggedFlag_Local()
	{
		$hosp_chk_flag = 0;
		$sqlHospitalCheck = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND type_flag&2=2 AND sub_type_flag&2=2";
		$resHospitalCheck = $this->conn_iro->query_sql($sqlHospitalCheck);
		if($resHospitalCheck && mysql_num_rows($resHospitalCheck)>0)
		{
			$hosp_chk_flag = 1;
		}
		return $hosp_chk_flag;
	}
	public function isHospitalCategoryExists()
	{
		$hosp_cat_flag = false;
		if($this->live_categories_arr)
		{
			$live_catids = implode("','",$this->live_categories_arr);
			$sqlHospCategory	=	"select count(catid) as cnt,GROUP_CONCAT(catid) AS hospital_catids  from d_jds.tbl_categorymaster_generalinfo where catid IN ('".$live_catids."') AND display_product_flag&8=8";
			$resHospCategory	=	$this->conn_local->query_sql($sqlHospCategory);
			$row_hosp_category	=	mysql_fetch_assoc($resHospCategory);
			if($row_hosp_category['cnt'] > 0)
			{
				$this->hospital_catids = $row_hosp_category['hospital_catids'];
				$hosp_cat_flag = true;
			}
		}
		return $hosp_cat_flag;
	}
	private function doctorActivationData()
	{		
		$resultArray=  array();
		
		$resultArray['doc_param']['doc_name'] 	 = addslashes(stripslashes($this->gen_arr_main['companyname']));
		$resultArray['doc_param']['qualification'] = null;
		$resultArray['doc_param']['speciality'] 	 = null;
		$resultArray['doc_param']['sub_type_flag'] = 0;
		$resultArray['hos_param']['hospitalname']  = addslashes(stripslashes($this->gen_arr_main['companyname']));
		$resultArray['hos_param']['hospitaladdress'] = addslashes(stripslashes($this->gen_arr_main['full_address']));
		
		$geninfo_mobile = $this->gen_arr_main['mobile'] ;
		$geninfo_mobile_arr = array();
		
		$geninfo_mobile_arr = explode(",",$geninfo_mobile);
		$geninfo_mobile_arr = array_filter($geninfo_mobile_arr);
		$geninfo_mobile_arr = array_unique($geninfo_mobile_arr);
		
		if(count($geninfo_mobile_arr) && !empty($geninfo_mobile_arr)){ 
			$resultArray['hos_param']['mobile'] 			= implode('|~|',$geninfo_mobile_arr);
			$resultArray['hos_param']['callback_mobile'] 	= implode('|~|',$geninfo_mobile_arr);
			$resultArray['hos_param']['login_mobile'] 	=$geninfo_mobile_arr[0];
		}
		$geninfo_email = $this->gen_arr_main['email'] ;
		$geninfo_email_arr = array();
		
		$geninfo_email_arr = explode(",",$geninfo_email);
		$geninfo_email_arr = array_filter($geninfo_email_arr);
		$geninfo_email_arr = array_unique($geninfo_email_arr);
		
		if(count($geninfo_email_arr) && !empty($geninfo_email_arr)){ 
			$resultArray['hos_param']['email'] = implode('|~|',$geninfo_email_arr);
		}
		
		
		$stdcode = trim($this->gen_arr_main['stdcode'],'0');
		$stdcode = '0'.$stdcode;
		$stdcodepattern = $stdcode;
		
		$landlinearr = array();
		$landlinearrstr= null;
		if($this->gen_arr_main['landline'])
		{
			$landlinearr = explode(",",$this->gen_arr_main['landline']);
			$landlinearr = array_filter($landlinearr);
			$landlinearr = array_unique($landlinearr);
			$entityphonestr = implode("|~|",$landlinearr);
			foreach($landlinearr as $k=>$v)
			{
				$landlinearr[$k]= $stdcodepattern."-".$v;
			}		
			$landlinearrstr = implode("|~|",$landlinearr);
		}
		
		$resultArray['hos_param']['callback_phone'] = $landlinearrstr;
		$resultArray['hos_param']['entity_phone'] 	= $entityphonestr;    
		$resultArray['hos_param']['call_forward_flag'] = 0;
		$resultArray['hos_param']['confirmation_flag'] = 1;
		$resultArray['hos_param']['call_forward_mobile'] ="";
		$resultArray['hos_param']['call_forward_phone'] ="";
		$resultArray['hos_param']['doc_parentid'] = $this->doc_id;
		$resultArray['hos_param']['timeslot'] = "15";
		$resultArray['hos_param']['fees'] = "0";
		$resultArray['hos_param']['hosp_auto_pid'] = "";
		
		$resultArray['hos_param']['doctor_clicnic_time'] = $this->doc_hours_of_operation_val;
		
		$resultArray['hos_param']['bookingpolicy_with_val'] = "No Restriction|0";
		$resultArray['hos_param']['cancellationpolicy_with_val'] = "No Restriction|0";
		$resultArray['hos_param']['bookingpolicy'] = "No Restriction";
		$resultArray['hos_param']['min_time_rsvn'] = "0";
		$resultArray['hos_param']['cancellationpolicy'] = "No Restriction";
		$resultArray['hos_param']['min_time_rsvn_cancel'] = "0";
		$resultArray['hos_param']['age_restriction'] =""; 
		$resultArray['hos_param']['gender_spec'] =""; 
		
		return $resultArray;
	}
	public function automatic_populate_hospital_data($doc_param,$hos_param,$uniqueid)
	{
		$sphinx_id    	= $this->get_sphinxid($this->parentid);
		$doctorname 	= $doc_param['doc_name'];
		$loc_parentid 	= '';
		$loc_docid 		= '';
		
	 	$sqlInsrtHospShadow = "INSERT INTO tbl_hospital_info_shadow
							  SET
							  panindia_sphinxid    		= '".$sphinx_id."',
							  parentid             		= '".$this->parentid."',
							  docid               		= '".$this->doc_id."',
							  data_city                 = '".$this->data_city."',
							  hours_of_operation  		= '".$hos_param['doctor_clicnic_time']."',
							  companyname         		= '".addslashes(stripslashes($doctorname))."',
							  address      				= '".addslashes(stripslashes($hos_param['hospitaladdress']))."',
							  mobile               		= '".$hos_param['mobile']."',
							  callback_mobile           = '".$hos_param['callback_mobile']."',
							  login_mobile       		= '".$hos_param['login_mobile']."',
							  callback_phone			= '".$hos_param['callback_phone']."',
							  call_forward_flag			= '".$hos_param['call_forward_flag']."',
							  confirmation_flag			= '".$hos_param['confirmation_flag']."',
							  call_forward_mobile		= '".$hos_param['call_forward_mobile']."',
							  call_forward_phone		= '".$hos_param['call_forward_phone']."',
							  fees						= '".$hos_param['fees']."',
							  email              		= '".$hos_param['email']."',
							  qualification       		= '".$doc_param['qualification']."',
							  specialization       		= '".$doc_param['speciality']."',
							  appointment_booking  		= '".$hos_param['bookingpolicy']."',
							  appointment_cancellation  = '".$hos_param['cancellationpolicy']."',
							  min_time_rsvn				= '".$hos_param['min_time_rsvn']."',
							  min_time_rsvn_cancel		= '".$hos_param['min_time_rsvn_cancel']."',
							  department       			= '".$doc_param['department']."',
							  ucode                     = '".$this->ucode."',
							  time_slot					= '".$hos_param['timeslot']."',
							  parent_pid				= '".$this->parentid."',
							  loc_parentid				= '".$loc_parentid."',
							  age_restriction			= '".$hos_param['age_restriction']."',
							  gender_spec				= '".$hos_param['gender_spec']."',
							  multi_practice			= '".$doc_param['sub_type_flag']."',
							  insertdate                = '".date("Y-m-d H:i:s")."',
							  active_flag				= '1'
							  ON DUPLICATE KEY UPDATE
							  hours_of_operation  		= '".$hos_param['doctor_clicnic_time']."',
							  companyname         		= '".addslashes(stripslashes($doctorname))."',
							  address      				= '".addslashes(stripslashes($hos_param['hospitaladdress']))."',
							  mobile               		= '".$hos_param['mobile']."',
							  callback_mobile           = '".$hos_param['callback_mobile']."',
							  login_mobile       		= '".$hos_param['login_mobile']."',
							  callback_phone			= '".$hos_param['callback_phone']."',
							  call_forward_flag			= '".$hos_param['call_forward_flag']."',
							  confirmation_flag			= '".$hos_param['confirmation_flag']."',
							  call_forward_mobile		= '".$hos_param['call_forward_mobile']."',
							  call_forward_phone		= '".$hos_param['call_forward_phone']."',
							  fees						= '".$hos_param['fees']."',
							  email              		= '".$hos_param['email']."',
							  qualification       		= '".$doc_param['qualification']."',
							  specialization       		= '".$doc_param['speciality']."',
							  appointment_booking  		= '".$hos_param['bookingpolicy']."',
							  appointment_cancellation  = '".$hos_param['cancellationpolicy']."',
							  min_time_rsvn				= '".$hos_param['min_time_rsvn']."',
							  min_time_rsvn_cancel		= '".$hos_param['min_time_rsvn_cancel']."',
							  department       			= '".$doc_param['department']."',
							  ucode                     = '".$this->ucode."',
							  time_slot					= '".$hos_param['timeslot']."',
							  parent_pid				= '".$this->parentid."',
							  loc_parentid				= '".$loc_parentid."',
							  age_restriction			= '".$hos_param['age_restriction']."',
							  gender_spec				= '".$hos_param['gender_spec']."',
							  multi_practice			= '".$doc_param['sub_type_flag']."',
							  insertdate                = '".date("Y-m-d H:i:s")."',
							  active_flag				= '1'";
					  
		$resInsrtHospShadow	=	$this->conn_local->query_sql($sqlInsrtHospShadow);
		
		// searchplus_autotag_api called autotag = 1
		
		$sp_tag_url = $this->cs_api."/api/update_searchplus_tagging.php?parentid=".$this->parentid."&module=CS&doc_id=".$this->doc_id."&data_city=".urlencode($this->data_city)."&usercode=".$this->ucode."&vertical=2&auto=1";
		$sp_tag_res = json_decode($this->curl_call_get($sp_tag_url),true);
		
		$vertical_data_arr = array();
		$vertical_data_arr['old'] = $this->fetchVerticalData($this->doc_id,'2');
		
		$rsvn_update_url  = $this->web_services_api.'rsvnUpdate.php';
	 	$rsvn_update_data ='panindia_sphinxid='.$sphinx_id.'&docid='.$this->doc_id.'&parentid='.$this->parentid.'&data_city='.$this->data_city.'&entity_mobile='.$hos_param['mobile'].'&callback_mobile='.$hos_param['callback_mobile'].'&login_mobile='.$hos_param['login_mobile'].'&callback_phone='.$hos_param['callback_phone'].'&entity_phone='.$hos_param['entity_phone'].'&call_forward_flag='.$hos_param['call_forward_flag'].'&call_forward_mobile='.$hos_param['call_forward_mobile'].'&call_forward_phone='.$hos_param['call_forward_phone'].'&entity_email='.$hos_param['email'].'&hours_of_operation='.$hos_param['doctor_clicnic_time'].'&capacity=1&slot='.$hos_param['timeslot'].'&fees='.$hos_param['fees'].'&entity_name='.urlencode(addslashes(stripslashes($doctorname))).'&qualification='.urlencode($doc_param['qualification']).'&entity_workplace='.urlencode($hos_param['hospitalname']).'&specialization='.urlencode($doc_param['speciality']).'&department='.urlencode($doc_param['department']).'&booking_policy='.$hos_param['bookingpolicy'].'&cancel_policy='.$hos_param['cancellationpolicy'].'&percentage_booking=1.00&changeover_slot=0&min_time_rsvn='.$hos_param['min_time_rsvn'].'&min_time_rsvn_cancel='.$hos_param['min_time_rsvn_cancel'].'&max_booking_limit=1&type_flag=2&sub_type_flag='.$doc_param['sub_type_flag'].'&updatedby='.$this->ucode.'&loc_parentid='.$loc_parentid.'&loc_docid='.$loc_docid.'&ref_parentid='.$this->parentid.'&confirmation_flag='.$hos_param['confirmation_flag'].'&age_restriction='.$hos_param['age_restriction'].'&gender_spec='.$hos_param['gender_spec'].'';
		
		$curl_resp = json_decode($this->curl_call_post($rsvn_update_url,$rsvn_update_data),true);
		
		$vertical_data_arr['new'] = $this->fetchVerticalData($this->doc_id,'2');
		$mandatory_fileds_arr = array('parentid'=>$this->parentid, 'docid'=>$this->doc_id, 'companyname'=>$doctorname, 'data_city'=>$this->data_city, 'vertical_name'=>'Doctor', 'ucode'=>$this->ucode, 'uname'=> $this->uname, 'module'=>$this->module, 'parent_pid'=>$this->parentid, 'remote_city_flag'=>$this->remote_city_flag);
		$vertical_data_arr['mandatory'] = $mandatory_fileds_arr;
		
		$history_data_str = json_encode($vertical_data_arr);  
		$history_data_url = $this->cs_api."/vertical_history/populate_vertical_history_api.php";				
		$history_data_res = $this->send_history_data($history_data_url,$history_data_str);

		
		$remarks = 'Activation / De-Activation Through Automatic Taggging';
		$cs_url = $this->cs_api."/api/update_iro_web_listing_flag.php?parentid=".$this->parentid."&action=2&vertical_type_flag=2&active_flag=1&web_active_flag=1&iro_active_flag=1&sub_type_flag=".$doc_param['sub_type_flag']."&updatedby=".$this->ucode;
		$cs_api_res = json_decode($this->curl_call_get($cs_url),true);
		
		
		$rsvn_activate_url = $this->web_services_api."rsvnActivate.php?docid=".$this->doc_id."&active_flag=1&iro_active_flag=1&web_active_flag=1&vertical_type_flag=2&updatedby=".$this->ucode."&remarks=".urlencode($remarks)."&sub_type_flag=".$doc_param['sub_type_flag'];
		
		//echo "<br>".$rsvn_activate_url;
		$web_api_res = json_decode($this->curl_call_get($rsvn_activate_url),true);
		
		
		
		$sqlDoctorLog = "INSERT INTO tbl_doctor_hospital_log SET
						parentid 			= '".$this->parentid."',
						docid 				= '".$this->doc_id."',
						data_city 			= '".addslashes($this->data_city)."',
						entity_name 		= '".addslashes(stripslashes($doctorname))."',
						insertdate 			= '".date("Y-m-d H:i:s")."',
						ucode 				= '".$this->ucode."',
						uname 				= '".$this->uname."',
						module 				= '".$this->module."',
						ip_address 			= '".$_SERVER['REMOTE_ADDR']."',
						rsvn_update_url 	= '".$rsvn_update_url." ? ".$rsvn_update_data."',
						rsvn_activate_url 	= '".$rsvn_activate_url."',
						iro_type_flag 		= '1',
						website_type_flag 	= '1',
						sub_type_flag 		= '0',
						flow 				= 'Doctor-Automatic',
						parent_pid 			= '".$this->parentid."',
						uniqueid 			= '".$uniqueid."'";
		$resDoctorLog = $this->conn_iro->query_sql($sqlDoctorLog);
		
		$narration  = "\n"."Process : Doctor - Auto Activation"."\n\n This Contract has been tagged for Doctor as we found balance And Doctor category in the contract.\n" ;
		
		$this->insertNarration($narration);
		
		$comment = "Tagged For Doctor Automatically.";
		
		$comment_new = $comment."\nDoctor Catids : ".$this->doctor_catids;
		$this->insert_auto_activation_log($comment_new);
		
		$send_mail_flag = 1;
		$user_name_arr =  array();
		$email_id_arr = array();
		if(trim($hos_param['login_mobile'])!='')
		{
			$user_name_arr = explode("|~|",$hos_param['login_mobile']);
			$user_name_arr = array_unique($user_name_arr);
			$user_name_val = implode(",",$user_name_arr);
			
		}
		$skip_client_mobile = '0000';
		if(in_array($skip_client_mobile,$user_name_arr))
		{
			$send_mail_flag = 0;
		}
		if(trim($hos_param['email'])!='')
		{
			$email_id_arr = explode("|~|",$hos_param['email']);
		}
		
		
		if(trim($email_id_arr[0])!='')
		{
			$email_id = $email_id_arr[0];
		}
		else
		{
			$email_id = '';
		}
		if(APP_LIVE == 1 && $send_mail_flag == 1)
		{
			$mail_url = 'http://192.168.13.101/';
			$mail_path = $mail_url."functions/DoctSmsEmail.php?mobile=".$user_name_val."&doctNm=".urlencode($doctorname)."&email=".$email_id;
			$mail_status = json_decode($this->curl_call_get($mail_path),true);
		}
		else
		{
			$mail_url = 'http://rishichandwani.jdsoftware.com/new_web/';
		}
		return $curl_response['error']['msg'];
	}
	public function automatic_populate_doctor_data($doc_param,$hos_param,$uniqueid)
	{
		$sphinx_id = $this->get_sphinxid($this->parentid);
		$loc_parentid = '';
		
		$sqlInsrtDocShadow = "INSERT INTO tbl_doctor_info_shadow
							  SET
							  panindia_sphinxid    		= '".$sphinx_id."',
							  parentid             		= '".$this->parentid."',
							  docid               		= '".$this->doc_id."',
							  data_city                 = '".$this->deptcity."',
							  companyname         		= '".$doc_param['doc_name']."',
							  hours_of_operation  		= '".$hos_param['doctor_clicnic_time']."',
							  mobile               		= '".$hos_param['mobile']."',
							  callback_mobile           = '".$hos_param['callback_mobile']."',
							  login_mobile       		= '".$hos_param['login_mobile']."',
							  callback_phone			= '".$hos_param['callback_phone']."',
							  call_forward_flag			= '".$hos_param['call_forward_flag']."',
							  confirmation_flag			= '".$hosp_param['confirmation_flag']."',
							  call_forward_mobile		= '".$hos_param['call_forward_mobile']."',
							  call_forward_phone		= '".$hos_param['call_forward_phone']."',
							  fees						= '".$hos_param['fees']."',
							  email              		= '".$hos_param['email']."',
							  time_slot					= '".$hos_param['timeslot']."',
							  multi_practice			= '".$doc_param['sub_type_flag']."',
							  parent_pid				= '".$this->parentid."',
							  loc_parentid				= '".$loc_parentid."',
							  age_restriction			= '".$hos_param['age_restriction']."',
							  gender_spec				= '".$hos_param['gender_spec']."',
							  qualification       		= '".$doc_param['qualification']."',
							  specialization       		= '".$doc_param['speciality']."',
							  department       			= '".$doc_param['department']."',
							  appointment_booking  		= '".$hos_param['bookingpolicy']."',
							  appointment_cancellation  = '".$hos_param['cancellationpolicy']."',
							  min_time_rsvn				= '".$hos_param['min_time_rsvn']."',
							  min_time_rsvn_cancel		= '".$hos_param['min_time_rsvn_cancel']."',
							  ucode                     = '".$this->ucode."',
							  insertdate                = '".date("Y-m-d H:i:s")."'
							  ON DUPLICATE KEY UPDATE
							  companyname         		= '".$doc_param['doc_name']."',
							  hours_of_operation  		= '".$hos_param['doctor_clicnic_time']."',
							  mobile               		= '".$hos_param['mobile']."',
							  callback_mobile           = '".$hos_param['callback_mobile']."',
							  login_mobile       		= '".$hos_param['login_mobile']."',
							  callback_phone			= '".$hos_param['callback_phone']."',
							  call_forward_flag			= '".$hos_param['call_forward_flag']."',
							  confirmation_flag		= '".$hosp_param['confirmation_flag']."',
							  call_forward_mobile		= '".$hos_param['call_forward_mobile']."',
							  call_forward_phone		= '".$hos_param['call_forward_phone']."',
							  fees						= '".$hos_param['fees']."',
							  email              		= '".$hos_param['email']."',
							  time_slot					= '".$hos_param['timeslot']."',
							  multi_practice			= '".$doc_param['sub_type_flag']."',
							  parent_pid				= '".$this->parentid."',
							  loc_parentid				= '".$loc_parentid."',
							  age_restriction			= '".$hos_param['age_restriction']."',
							  gender_spec				= '".$hos_param['gender_spec']."',
							  qualification       		= '".$doc_param['qualification']."',
							  specialization       		= '".$doc_param['speciality']."',
							  department       			= '".$doc_param['department']."',
							  appointment_booking  		= '".$hos_param['bookingpolicy']."',
							  appointment_cancellation  = '".$hos_param['cancellationpolicy']."',
							  min_time_rsvn				= '".$hos_param['min_time_rsvn']."',
							  min_time_rsvn_cancel		= '".$hos_param['min_time_rsvn_cancel']."',
							  ucode                     = '".$this->ucode."',
							  insertdate                = '".date("Y-m-d H:i:s")."'";
					  
		$resInsrtDocShadow 	= $this->conn_local->query_sql($sqlInsrtDocShadow);
		
		$selectSql = "SELECT docid FROM tbl_hospital_info_shadow WHERE parent_pid = '".$this->parentid."' AND active_flag = '1'";
		$selectRes	=	$this->conn_local->query_sql($selectSql);
		if($selectRes && mysql_num_rows($selectRes) > 0){
			while($selectRow = mysql_fetch_assoc($selectRes)){
			
				$docids[] = $selectRow['docid'];
			}
		}
		if(is_array($docids) && !empty($docids)){
			$child_docid = implode(',',$docids);
		}
		$this->raiseExtraUpdatedOn();
		
		
		$rsvn_mapping_url = $this->web_services_api.'rsvnMapping.php';
		$rsvn_mapping_data = 'parent_docid='.$this->doc_id.'&base_docid='.$this->doc_id.'&child_docid='.$child_docid.'&type_flag=2&sub_type_flag='.$doc_param['sub_type_flag'].'&action_flag=0';
		
		$rsvn_mapping_content = $this->curl_call_post($rsvn_mapping_url,$rsvn_mapping_data);
		$rsvn_mapping_response = json_decode($rsvn_mapping_content,true);
		
		$sql_updt_doc_hosp_log = "UPDATE tbl_doctor_hospital_log SET rsvn_mapping_url = '".$rsvn_mapping_url." ? ".$rsvn_mapping_data."' WHERE uniqueid = '".$uniqueid."'";
		$res_updt_doc_hosp_log = $this->conn_iro->query_sql($sql_updt_doc_hosp_log);
		
		return $rsvn_mapping_response['error']['msg'];
	}
	function raiseExtraUpdatedOn()
	{
		$insertarr['tbl_companymaster_extradetails']	= array("updatedOn" => date('Y-m-d H:i:s'),"updatedBy" => $this->ucode);
		$wherecond		= "parentid = '".$this->parentid."'";
		$this->compmaster_obj->set_datacity($this->parentid);
		$this->compmaster_obj->UpdateFields($insertarr,$wherecond);
	}
}	

?>
