<?php

if(!defined('APP_USER'))
{
  require_once('../../config.php');
}

class email_sms_send  extends DB
{
	private $conn_messaging;
		
	function __construct( $db,$data_city)
	{
		$dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		$data_city 		= ((in_array(strtolower($data_city), $dataservers)) ? strtolower($data_city) : 'remote');
		$this->conn_messaging= 	 $db[$data_city]['messaging']['master'];
		$this->conn_iro = $db[$data_city]['iro']['master'];
		if(strtolower($data_city) == 'remote'){
			$this->remoteflag = 1;
		}else{
			$this->remoteflag = 0;
		}
	}
	function sendSMS($mobileNo, $smstext, $source)
	{
		if($mobileNo!='' && $smstext!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['mobile'] 	= $mobileNo;
				$params['sms_text'] = $smstext;
				$params['source'] 	= $source;
				$params['mod'] 		= 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$smstext= addslashes(stripcslashes($smstext));
				$sql_sms = "INSERT INTO tbl_common_intimations SET 
							mobile 		= '".$mobileNo."', 
							sms_text 	= '".$smstext."',
							source		= '".$source."'";
				$result = parent::execQuery($sql_sms, $this->conn_messaging);
				return $result;
			}
		}
		else
		{
			return 0;
		}
	}
	function sendSMSInvoice($mobileNo, $smstext, $source,$parent_id)
	{
		if($mobileNo!='' && $smstext!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['mobile'] 	 = $mobileNo;
				$params['sms_text']  = $smstext;
				$params['source'] 	 = $source;
				$params['parent_id'] = $parent_id;
				$params['mod'] 		 = 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$sql_sms = "INSERT INTO tbl_common_intimations SET 
							mobile 		= '".$mobileNo."', 
							parent_id 	= '".$parent_id."', 
							sms_text 	= '".$this->mysql_real_escape_custom($smstext)."',
							source		= '".$source."'";
				$result = parent::execQuery($sql_sms, $this->conn_messaging);
				return $result;
			}
		}
		else
		{
			return 0;
		}
	}
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_messaging[0], $this->conn_messaging[1], $this->conn_messaging[2]);
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}
	function sendSMSAdv($mobileNo, $smstext, $source,$parent_id)
	{
		if($mobileNo!='' && $smstext!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['mobile'] 	 = $mobileNo;
				$params['sms_text']  = $smstext;
				$params['source'] 	 = $source;
				$params['parent_id'] = $parent_id;
				$params['mod'] 		 = 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$smstext= addslashes(stripcslashes($smstext));
				$sql_sms = "INSERT INTO tbl_common_intimations SET 
							mobile 		= '".$mobileNo."', 
							sms_text 	='".addslashes(stripslashes($smstext))."',
							parent_id   = '".$parent_id."',
							source		= '".$source."'";
				$result = parent::execQuery($sql_sms, $this->conn_messaging);
				return $result;
			}
		}
		else
		{
			return 0;
		}
	}
	function sendEmail($email_id, $sender_email, $email_subject, $email_text, $source, $parent_id='',$email_id_cc)
	{
		
		if($this->remoteflag == 1)
		$sender_email_remote = $sender_email;
		
		if($sender_email)
		$sender_email = "sender_email  = '".$sender_email."', ";
		
		if($email_id_cc)
		{
			$email_cc =" email_id_cc ='".$email_id_cc."',";
		}
		
		
		if($email_id!='' && $email_text!='' && $email_subject!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['email_id'] 	 	= $email_id;
				$params['email_subject']  	= $email_subject;
				$params['email_text']  		= file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php");
				$params['email_id_cc'] 		= $email_id_cc;
				$params['sender_email'] 	= $sender_email_remote;
				$params['parent_id'] 		= $parent_id;
				$params['source'] 	 		= $source;
				$params['mod'] 		 		= 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$email_text = addslashes(stripcslashes($email_text));
				$sql_email="INSERT INTO tbl_common_intimations SET 
							".$sender_email."
							email_id      = '".$email_id."',
							email_subject = '".$email_subject."',
							email_text    = '".addslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php"))."',
							parent_id     = '".$parent_id."',
							".$email_cc."
							source        = '".$source."'";
				$res_email = parent::execQuery($sql_email, $this->conn_messaging);
				return $res_email;
			}
		}
		else
		{
			return 0;
		}
	}
	function sendInvoiceMails($email_id, $sender_email, $email_subject, $email_text, $source, $parent_id='')
	{
			
		if($email_id!='' && $email_text!='' && $email_subject!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['email_id'] 	 	= $email_id;
				$params['email_subject']  	= $email_subject;
				$params['email_text']  		= $email_text;
				$params['sender_email'] 	= $sender_email;
				$params['parent_id'] 		= $parent_id;
				$params['source'] 	 		= $source;
				$params['mod'] 		 		= 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$sql_email="INSERT INTO tbl_common_intimations SET 
								sender_email  = '".$sender_email."', 
								email_id      = '".$email_id."',
								email_subject = '".$email_subject."',
								email_text    = '".$email_text."',
								parent_id     = '".$parent_id."',
								source        = '".$source."'";
				
				$res_email = parent::execQuery($sql_email, $this->conn_messaging);
				return $res_email; 
			} 
		}
		else
		{
			return 0;
		}
	}
	function sendEmailwithAttachment($email_id, $sender_email, $email_subject, $email_text, $source,$parent_id,$attachment)
	{
		if($email_id!='' && $email_text!='' && $email_subject!='') // This function will be called only on live server.
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['email_id'] 	 	= $email_id;
				$params['email_subject']  	= $email_subject;
				$params['email_text']  		= $email_text;
				$params['sender_email'] 	= $sender_email;
				$params['attachment'] 		= $attachment;
				$params['parent_id'] 		= $parent_id;
				$params['source'] 	 		= $source;
				$params['mod'] 		 		= 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$sql_email="INSERT INTO sms_email_sending.tbl_common_intimations SET 
							parent_id  	  = '".$parent_id."', 
							sender_email  = '".$sender_email."', 
							email_id      = '".$email_id."',
							email_subject = '".$email_subject."',
							email_text    = '".addslashes(stripcslashes($email_text))."',
							attachment    = '".$attachment."',
							source        = '".$source."'";
				$res_email = parent::execQuery($sql_email, $this->conn_messaging);
				return $res_email;
			}
		}
		else
		{
			return 0;
		}
	}
	function sendEmailAdv($email_id, $sender_email, $email_subject, $email_text, $source='CS', $parent_id='',$email_id_cc=null,$email_id_bcc=null,$reply_to=null)
	{
		$reply = $email_cc = $email_bcc="";
		if($email_id_cc)
		{
			$email_cc =" email_id_cc ='".$email_id_cc."',";
		}
		
		if($email_id_bcc)
		{
			$email_bcc =" email_id_bcc ='".$email_id_bcc."',";
		}
		
		if($reply_to)
		{
			$reply =" reply_to ='".$reply_to."',";
		}			
		if($email_id!='' && $email_text!='' && $email_subject!='')
		{
			if($this->remoteflag == 1)
			{
				$params = array();
				$params['email_id'] 	 	= $email_id;
				$params['email_subject']  	= $email_subject;
				$params['email_text']  		= file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php");
				$params['sender_email'] 	= $sender_email;
				$params['email_id_cc'] 		= $email_id_cc;
				$params['email_id_bcc'] 	= $email_id_bcc;
				$params['reply_to'] 		= $reply_to;
				$params['parent_id'] 		= $parent_id;
				$params['source'] 	 		= $source;
				$params['mod'] 		 		= 'common_idc';
				$apires = $this->callSMSEmailAPI($params);
				$apires = trim($apires);
				if(strtolower($apires) == 'success'){
					return 1;
				}else{
					$this->smsEmailAPILog($params);
					return 0;
				}
			}
			else
			{
				$email_text = addslashes(stripcslashes($email_text));
				$sql_email="INSERT INTO tbl_common_intimations SET 
							sender_email  = '".$sender_email."', 
							email_id      = '".$email_id."',
							email_subject = '".$email_subject."',
							email_text    = '".addslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php"))."',
							parent_id     = '".$parent_id."',
							".$email_cc.$email_bcc.$reply."
							source        = '".$source."'";
							
				$res_email = parent::execQuery($sql_email, $this->conn_messaging);       
				return $res_email;
			}
		}
		else
		{
			return 0;
		}
	}
	function callSMSEmailAPI($params)
	{
		$curl_url = SMS_EMAIL_LB_IP."/insert.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	function smsEmailAPILog($params){
		$parentid = '';
		if(isset($params['parent_id'])){
			$parentid = $params['parent_id'];
		}
		$sqlSMSEmailLog =  "INSERT INTO d_jds.tbl_smsemail_api_log SET
							parentid 	= '".$parentid."',
							mobile		= '".$params['mobile']."',
							emailid		= '".addslashes($params['email_id'])."',
							source		= '".addslashes($params['source'])."',
							parameters 	= '".json_encode($params)."',
							updatedOn 	= NOW()";
		$resSMSEmailLog = parent::execQuery($sqlSMSEmailLog, $this->conn_iro);       					
	}
}
?>
