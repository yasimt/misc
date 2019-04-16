<?php
class mktgGetContDataClass extends DB
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
	
	
	function __construct($params,$live_data_class_obj)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
				
		$bouncedata 	= trim($params['BD']);
		$extnID 		= trim($params['extn']);
		$ecs_flag 		= trim($params['ecs_flag']);
		$web_dialer 	= trim($params['web_dialer']);
		$hotdata	 	= trim($params['hotdata']);
		
		$force		 	= trim($params['force']);
		
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
		if(trim($ucode)=='')
		{
			$message = "Ucode is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($uname)=='')
		{
			$message = "Uname is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->uname  	  	= $uname;
		
		$this->bouncedata  	= $bouncedata;
		$this->extnID  	  	= $extnID;
		$this->ecs_flag  	= $ecs_flag;
		$this->web_dialer  	= $web_dialer;
		$this->hotdata      = $hotdata;
		$this->force   	    = $force;
		
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->companyClass_obj  = new companyClass();
		$this->setServers();
		
		$this->live_data_class_obj = new live_data_class($params);
		
		
		$valid_modules_arr = array("TME","ME","JDA");
		if(!in_array(strtoupper($this->module),$valid_modules_arr)){
			$message = "This service is only applicable for TME/ME/JDA module.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		$this->debug_mode		= 	0;
		if($params['trace']==1){
			$this->debug_mode	= 	1;
			print"<pre>";print_r($params);
		}
	}
	
	function PopulateTempTables()
	{
		$contractid = array();
		if($this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "parentid";			
			$contractid 				= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sql_bform = "Select parentid from tbl_companymaster_generalinfo_shadow where parentid ='".$this->parentid."'";
			$res = parent::execQuery($sql_bform, $this->conn_tme);
			if($res && mysql_num_rows($res)){
				$contractid = mysql_fetch_assoc($res);
			}
		}
		
		if(count($contractid) > 0 && !$this->force){
			$live_data_details_arr['error']['code'] = 0;
			$live_data_details_arr['error']['msg'] = "Entry Already Present In Shadow";
		}
		else
		{
			//$selectMain 	= "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid ='".$this->parentid."'";
		    //$res = parent::execQuery($selectMain, $this->conn_iro);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'extra_det_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'parentid';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'mktgGetContDataClass';

			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
			{
				$row_geninfo_main 	= $comp_api_arr['results']['data'][$this->parentid];			
			}
			
			if($row_geninfo_main['parentid']!=''){
				$live_data_details_arr 	= $this->live_data_class_obj->populateTempTables();
			}else{
				 $live_data_details_arr['error']['code'] = 2;
				 $live_data_details_arr['error']['msg'] = "Redirect to bform";
			}
		}
		return $live_data_details_arr;
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
		$this->conn_national   	= $db['db_national'];
		if(strtoupper($this->module) == 'ME')
		{
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		
		if(strtoupper($this->module) == 'TME')
		{
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($this->conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
		}
	}
	
	function addslashesArray($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
	function curlCall($curl_url,$data)
	{	
		//echo $curl_url.'?'.print_r($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($curl_url));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
