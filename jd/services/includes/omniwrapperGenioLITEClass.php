<?php
class omniwrapperGenioLITEClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	var $dealcloseflow= null;
	function __construct($params, $omniclassObj = null, $omniBudgetClassobj = null ,$finance_display_obj = null,$domainClassobj = null)
	{		
		$this->params = $params;		
		
		
		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		if(trim($this->params['parentid']) == "")
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Parentid Missing";
				echo json_encode($result_msg_arr);exit;
		}
		else
			$this->parentid  = $this->params['parentid']; 
			
			
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 
			
			
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 
		
		
		if(trim($this->params['version']) == "" && $this->action!='10' && $this->action!='1')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "version Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->version  = $this->params['version']; 
			
			
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 
			

		if(trim($this->params['usercode']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Usercode Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->usercode  = $this->params['usercode']; 
			
		
		if(trim($this->params['campaign_details']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Campaign Budget Details Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->campaign_details  = json_decode($this->params['campaign_details'],true); 
			
			
		if( $omniclassObj == null)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "omni class reference is missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->omniclassObj  = $omniclassObj; 
			
		if( $omniBudgetClassobj == null)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "omni budget class reference is missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->omniBudgetClassobj  = $omniBudgetClassobj; 
		
		if( $omniBudgetClassobj == null)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "finance class reference is missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->finance_display_obj  = $finance_display_obj; 
			
		if( $domainClassobj == null)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "finance class reference is missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->domainClassobj  = $domainClassobj; 	
		
		
			
		if(trim($this->params['username']) != "")
		{
			$this->username  = urldecode($this->params['username']); 
		}
		//,domain_regiter_emailId,domainReg_forget_link,action_flag_forget,action_flag_forgetstatus
		
		
		if(trim($this->params['trace']) != "")
		{
			$this-> trace = $this->params['trace'];
		}
		
		
		if($this-> trace){
			//echo '<br> coming in nereeeee';print_r($this->params);
		}
		
		
		$status=$this->setServers();
		
		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    	= $db[$data_city]['iro']['master'];
		
		$this->conn_log		= $db['db_log']; // pointing to 17.103
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['iro']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc  = $db[$data_city]['idc']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc  = $db[$data_city]['idc']['master'];
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_temp_new = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc = $this->conn_temp;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
			default:
			return -1;
			break;
		}
	}
	
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_idc[0], $this->conn_idc[1], $this->conn_idc[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}
	
	function PopulateOmniEntry()
	{
		if($this-> trace)
		{
			echo '<pre> campaign details :: ';
			print_r($this->campaign_details);
			echo '<br>';
			
		}
		
		$result_temp_to_main			   = $this->omniclassObj -> tempTomain($this->campaign_details);// updating tbl_omni_website_details, tbl_omni_details_consolidated
		
		
		if($this-> trace)
		{
			echo '<br> temp to main :: ';
			print_r($result_temp_to_main);
			//echo '<br> temp to main :: ';
			echo '<br>';
			
		}
		
		$result_tempTomainOmniExtraDetails = $this->omniclassObj -> tempTomainOmniExtraDetails($this->campaign_details); 	//updating tbl_omni_extradetails	
		if($this-> trace)
		{
			echo '<br> temp to main extra:: ';
			print_r($result_tempTomainOmniExtraDetails);
			//echo '<br> temp to main extra:: ';
			echo '<br>';
		}
		
		 //print_r($this->omniclassObj -> omniDealCloseDemoApi($this->campaign_details)); 
		$this->omniclassObj -> genio_lite_campaign_info = $this->campaign_details;
		
		$result_omniDealCloseDemoApi	   = $this->omniclassObj -> omniDealCloseDemoApi($this->campaign_details,1); 
		
		if($this-> trace)
		{
			echo '<br> deal close demo api:: ';
			print_r($result_omniDealCloseDemoApi);
			echo '<br> deal close demo api:: ';
			echo '<br>';
		}
		//die;
		
		
		if(array_key_exists("86",$this->campaign_details))
		{
			$omniBudgetClass  = $this->omniBudgetClassobj->tempToMainSSL($this->campaign_details);	
		}
		else
		{
			$omniBudgetClass['error']['msg'] = 'success';
			$omniBudgetClass['error']['code'] = '0';
		}	
		if($this-> trace)
		{
			echo '<br> Omni Budget :: ';
			print_r($omniBudgetClass);
			echo '<br>';
		}
		
		
		$allowed_dependant = 0;
		$dependant_arr = array();
		foreach($this->campaign_details as $key => $val)
		{
			
			foreach($val as $keyn => $valn)
			{
				
				
				if($keyn == "is_dependent" && $valn == 1)
				{
					$allowed_dependant = 1;
					$dependant_arr[$key]['parentid'] = $this->parentid;
					$dependant_arr[$key]['pri_campaignid'] = $val['parent_campaign'];
					$dependant_arr[$key]['version'] = $this->version;
					$dependant_arr[$key]['combo_type'] = $val['combo'];
					$dependant_arr[$key]['dep_campaignid'] = $key;
					$dependant_arr[$key]['dep_budget'] = $val['budget'];
					$dependant_arr[$key]['dep_duration'] = $val['duration'];
					//break;
				}	
			}	
		}
		
		
		if($allowed_dependant == 1 && count($dependant_arr) > 0)
		{
			$finance_res = $this->finance_display_obj->tempToMainDependent($this->campaign_details,$dependant_arr,$this->version);
		}
		else
		{
			$finance_res['error']['msg'] = 'success';
			$finance_res['error']['code'] = '0';
		}	
		//print_r($dependant_arr);die;
		if($this-> trace)
		{
			echo '<br> finance Budget :: ';
			print_r($finance_res);
			echo '<br>';
		}
		
		
		if(array_key_exists("82",$this->campaign_details))
		{
			
			$domainClassobjres  = $this->domainClassobj->tempTomain($this->campaign_details);
		}
		else
		{
			$domainClassobjres['error']['msg'] = 'success';
			$domainClassobjres['error']['code'] = '0';
		}	
		
		
		if($this-> trace)
		{
			echo '<br> domain service :: ';
			print_r($domainClassobjres);
			echo '<br>';
		}
		
		if(array_key_exists("83",$this->campaign_details))
		{
			
			$smsbudget  = $this->omniBudgetClassobj->tempToMainSms($this->campaign_details);
		}
		else
		{
			$smsbudget['error']['msg'] = 'success';
			$smsbudget['error']['code'] = '0';
		}		 
		 if($this-> trace)
		{
			echo '<br> sms service :: ';
			print_r($smsbudget);
			echo '<br>';
		}
		 
		
		//echo '<br>'.$result_temp_to_main['error']['msg'];
		//echo '<br>'.$result_tempTomainOmniExtraDetails['error']['msg'];
		//echo '<br>'.$result_omniDealCloseDemoApi['error']['msg'];
		//echo '<br>'.$result_domainMappingFix['error']['msg'];
		if( ( count($result_temp_to_main)>0 && strtolower($result_temp_to_main['error']['code']) == '0' ) && ( count($result_tempTomainOmniExtraDetails)>0 && strtolower($result_tempTomainOmniExtraDetails['error']['code']) == '0' ) && ( count($result_omniDealCloseDemoApi)>0 && strtolower($result_omniDealCloseDemoApi['error']['code']) == '0' ) && ( count($omniBudgetClass)>0 && strtolower($omniBudgetClass['error']['code']) == '0' ) && ( count($finance_res)>0 && strtolower($finance_res['error']['code']) == '0' ) && ( count($domainClassobjres)>0 && strtolower($domainClassobjres['error']['code']) == '0' ) && ( count($smsbudget)>0 && strtolower($smsbudget['error']['code']) == '0' ))
		{
			$return_message['error']['code'] = 0;
			$return_message['error']['msg']  = 'success';
		}else
		{
			$return_message['error']['code'] = 0;
			$return_message['error']['msg']  = 'failed';
		}
		return $return_message;
	}


}	
?>
