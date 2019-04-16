<?php
require_once 'class_send_sms_email.php';

class send_ods_vendor_applink_class extends DB{
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		$ucode 				= trim($params['ucode']);
		$uname 				= trim($params['uname']);
		$mobile				= trim($params['mobile']);
		
		if(trim($parentid)=='')
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
		if(trim($module)==''){
			$message = "Module is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($ucode)==''){
			$message = "UserCode is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($uname)==''){
			$message = "Username is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if((strtolower($module)=='tme' || strtolower($module)=='me') && $mobile==''){
			$message = "Mobile Number is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		
		
		$this->parentid  	= trim($parentid);
		$this->data_city 	= trim($data_city);
		$this->module  	  	= trim($module);
		$this->ucode  	  	= trim($ucode);
		$this->uname  	  	= trim($uname);
		$this->mobile		= trim($mobile);
		
		$this->setServers();
	
	}
	function setServers()
	{
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->is_remote = 'REMOTE';
		}
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if(strtoupper($this->module) =='CS'){
			$this->conn_temp		= $this->conn_iro;
		}
		elseif(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
		}
		elseif(strtoupper($this->module) =='ME' || strtoupper($this->module) =='JDA') {
			$this->conn_temp		= $this->conn_idc;
		}
		
	}
	
	function sendsmsandemail(){
		
		GLOBAL $db;		
		$emailsms_obj = new email_sms_send($db,$this->data_city);
		
		if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){
			$selDetails  = "select mobile,email, parentid from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$resDetails  = parent::execQuery($selDetails, $this->conn_temp);
			if($resDetails && mysql_num_rows($resDetails) > 0){
				$rowDetails1 = mysql_fetch_assoc($resDetails);
				if($rowDetails1['mobile']!=''){
					$mobile  	 = $rowDetails1['mobile'];
					$email       = $rowDetails1['email'];
				}else{
					$message = "Please add mobile number in contract & try gain";
					echo json_encode($this->send_die_message($message,1));
					die();
				}
				
			}
			else{
				$message = "Please add mobile number in contract & try gain";
				echo json_encode($this->send_die_message($message,1));
				die();
			}
		}else if(strtolower($this->module)=='tme' || strtolower($this->module)=='me'){
			$mobile = $this->mobile;
		}else{
			$message = "Please add mobile number in contract & try gain";
			echo json_encode($this->send_die_message($message,1));
			die();
		}

		if($mobile!=''){  
			$mobilenums = explode(',',$mobile);
			$this->shortUrl = 'http://jsdl.in/odsapp';
			$sms_cont = "The Link to Download your JD On Demand Services Vendor app is  ".$this->shortUrl."";
			
			 foreach($mobilenums as $key => $val){
				$smsflg = '';
				$result       = $emailsms_obj->sendSMSAdv($val,$sms_cont,'ODS_VENDOR_LINK_'.strtoupper($this->module).'', $this->parentid);
				if($result){
					$smsflg =1;
					$qry_log   = "insert into online_regis.tbl_ods_app_log (parentid, mobile, sent_time, sms_send_flag,usercode, username, source, data_city) values ('".$this->parentid."','".$val."','".date('Y-m-d H:i:s')."', '".$smsflg."', '".$this->ucode."','".$this->uname."', '".$this->module."', '".$this->data_city."') ";
					
					$res_log = parent::execQuery($qry_log, $this->conn_idc);
				}
				
			 } 
			 
			 if($result && $res_log){
				$die_msg_arr['data'] = array();
				$die_msg_arr['error']['code'] = 0;
				$die_msg_arr['error']['msg'] = 'Success';
				echo json_encode($die_msg_arr);
			 }
		}else{
			$message = "Please add mobile number & try gain";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		
	}
	
	private function send_die_message($msg,$errorCode)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = $errorCode;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
