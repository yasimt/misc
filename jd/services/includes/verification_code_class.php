<?php

class verification_code_class extends DB
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
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = trim($this->params['parentid']); //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = trim($this->params['data_city']); //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['action']) != "" && $this->params['action'] != null)
		{
			$this->action  = trim($this->params['action']); //initialize datacity
		}else
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
		if($this->params['action'] == 2)
		{

			if((trim($this->params['mobile']) != "" && $this->params['mobile'] != null) || (trim($this->params['email']) != "" && $this->params['email'] != null))
			{
				$this->mobile_number = trim($this->params['mobile']); //initialize mobile
				$this->emailid  = trim($this->params['email']); //initialize email
			}else
			{
				$errorarray['errormsg']='mobile or email id missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['source']) == "funds_transfer" )
			{
			
				if(trim($this->params['companyname']) != "" && $this->params['companyname'] != null && trim($this->params['amount']) != "" && $this->params['amount'] != null )
				{
					$this->companyname  = trim($this->params['companyname']); //initialize companyname
					$this->amount		= trim($this->params['amount']); //initialize datacity
				}else
				{
					$errorarray['errormsg']='amount or companyname is missing';
					echo json_encode($errorarray); exit;
				}
			}
			
			
		}
		
		if($this->params['action'] == 4)
		{
			if(trim($this->params['passed_Code']) != "" && $this->params['passed_Code'] != null)
			{
				$this->passed_Code  = trim($this->params['passed_Code']); //initialize datacity
			}else
			{
				$errorarray['errormsg']='passed Code missing';
				echo json_encode($errorarray); exit;
			}
		}
		
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
			{
				$this->module  = strtolower($this->params['module']); //initialize datacity
			}else
			{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
			
	   if(!in_array(trim($this->params['action']),array(3,1)))
	   {
		   
			
			if(trim($this->params['source']) != "" && $this->params['source'] != null)
			{
				$this->source  = strtolower($this->params['source']); //initialize datacity
			}else
			{
				$errorarray['errormsg']='source missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
			{
				$this->usercode  = strtolower($this->params['usercode']); //initialize datacity
			}else
			{
				$errorarray['errormsg']='usercode missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['username']) != "" && $this->params['username'] != null)
			{
				$this->username  = strtolower($this->params['username']); //initialize datacity
			}else
			{
				$errorarray['errormsg']='username missing';
				echo json_encode($errorarray); exit;
			}
	    }
	    
	    
		
		if(trim($this->params['remote']) != "" && $this->params['remote'] != null)
		{
			$this->remote  = $this->params['remote']; //initialize remote
		}

		$this->setServers();
		//echo json_encode('const'); exit;
		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
					
		if(DEBUG_MODE)
		{
			echo '<pre>db array :: ';
		print_r($db);
		}
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConTmeJds 		= $db[$data_city]['tme_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		//$this->db_budgeting		= $db[$data_city]['db_budgeting']['master'];
		if(DEBUG_MODE)
		{
			echo '<pre> IDc db array :: ';
			print_r($this->dbConIdc);
		}
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
			break;
		}
	}
	
	function setsphinxid()
	{
			$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			$res = parent::execQuery($sql, $this->dbConIro);

			if($res && mysql_num_rows($res) )
			{
					$row= mysql_fetch_assoc($res);
					$this->sphinx_id = $row['sphinx_id'];
					$this->docid = $row['docid'];
			}else
			{
					echo "sphinx_id not found in tbl_id_generator";
					exit;
			}
	}
	
	
		
	function GenerateRandomValidationCode()
	{
		return rand(100000,999999);
	}
	
	function sendEmailSms($smsObj,$verificationCode)
	{
		if(trim($this->params['source']) == "funds_transfer"){
			$smstext	 =  "Hello,your NETSECURE code is ".$verificationCode." for Funds Transfer of Rs.".$this->params['amount']." from contract ".$this->params['companyname'].". to contract ".$this->params['dest_companyname'].".";
		}elseif(strtolower(trim($this->params['source'])) == "cs_edit"){
			$smstext	 =  "Dear Customer, The Verification code for making changes in your contract is ".$verificationCode."~pls read out the code to our executive to complete the changes.~Justdial";
		}else{
			$smstext	 = "Your Justdial verification code is ".$verificationCode;		
		}
		if(trim($this->params['mobile'])!=''){
			$res_sms 	 = $smsObj->sendSMS($this->params['mobile'], $smstext,$this->params['source']);
		}
		
		if(trim($this->params['email'])!=''){
		$from	 = "noreply@justdial.com";
		$subject = "Justdial Verification Code";
		if(trim($this->params['source']) == "funds_transfer"){
			$subject .= " for Fund Transfer";
			$emailtext = "Dear Sir/Madam,<br>This is regarding your request of Fund Transfer of<b>Rs.".$this->params['amount']."<b> from contract <b>".$this->params['companyname']." - ".$this->params['src_parentid']."</b> to contract <b>".$this->params['dest_companyname']." - ".$this->params['dest_parentid']."</b><br>Pls read this Verification code <b>".$verificationCode."</b> to our executive on call to process the request.<br><br>Thanks,<br>Team Justdial";
		}
		else if(strtolower(trim($this->params['source'])) == "cs_edit"){
			$emailtext = str_replace('~','<br>',$smstext);
		}else{
			$emailtext= "Dear Sir/Madam,<br>Your Justdial verification code is ".trim($verificationCode)."<br><br>Thanks,<br>Team Justdial";
		}
		   $res_email 	 = $smsObj->sendEmail($this->params['email'], $from, $subject, $emailtext, $this->params['source']);
		}
	
		if($res_sms && $res_email)
		{
			return true;
		}
	}
	

	function writeValidationCodeInTable($validationcode)
	{
		
		$sql= "Insert into mobilemail_verification_code set
		parentid='".$this->parentid."',
		validationcode='".$validationcode."'
		ON Duplicate KEY UPDATE
		validationcode='".$validationcode."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
		}
		
		$sql_insert_log = "INSERT INTO contracts_verified_details(parentid,companyname,mobile_number,email_id,verification_code,verified_flag,module,source,data_city,usercode,username,date_time) VALUES 
		 ('".$this->parentid."','".addslashes(urldecode($this->params['companyname']))."','".$this->params['mobile']."','".$this->params['email']."','".$validationcode."','0','".$this->params['module']."','".$this->params['source']."','".$this->params['data_city']."','".$this->params['usercode']."','".addslashes(urldecode($this->params['username']))."',NOW()) ";
		$res_insert_log = parent::execQuery($sql_insert_log, $this->dbConDjds);
		
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql_insert_log;
			echo '<br>res  :: '.$res_insert_log;
		}
		
		return $res;
	}

	function readValidationCodeFromTable()
	{
		$validationcode=null;
		$sql= "select validationcode from mobilemail_verification_code where parentid='".$this->parentid."'";	
		$res = parent::execQuery($sql, $this->conn_temp);
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
			echo '<br>rows  :: '.mysql_num_rows($res);
		}
		if($res && mysql_num_rows($res) )
		{
			$row= mysql_fetch_assoc($res);
			$validationcode= $row['validationcode'];
		}
		
		return $validationcode;	
	}
	
	function ValidateVerificationCode()
	{
		$sql= "select validationcode from mobilemail_verification_code where parentid='".$this->parentid."' AND validationcode='".$this->params['passed_Code']."'";	
		$res = parent::execQuery($sql, $this->conn_temp);
		if(DEBUG_MODE)
		{
			echo '<br>sql  :: '.$sql;
			echo '<br>res  :: '.$res;
			echo '<br>rows  :: '.mysql_num_rows($res);
		}
		if($res && mysql_num_rows($res) )
		{
			$row= mysql_fetch_assoc($res);
			$validationcode= $row['validationcode'];
			
			$sql_update_log = "UPDATE contracts_verified_details set verified_flag='1' WHERE parentid='".$this->parentid."' AND verification_code='".$this->params['passed_Code']."' AND data_city='".$this->params['data_city']."' AND usercode='".$this->params['usercode']."' AND module='".$this->params['module']."' AND source='".$this->params['source']."'";
			$res_update_log = parent::execQuery($sql_update_log, $this->dbConDjds);
			if(DEBUG_MODE)
			{
			echo '<br>sql  :: '.$sql_update_log;
			echo '<br>res  :: '.$res_update_log;
			}
		}
		
		if($validationcode)
		return true;
		else 
		return false;	
	}
	

}



?>
