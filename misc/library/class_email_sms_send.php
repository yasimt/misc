<?php
// changes needed
if(!defined('APP_PATH'))
{
   require_once("config.php");
}
include_once(APP_PATH."library/path.php");
global $dbarr;

	class email_sms_send
	{
        private $conn_messaging;
            
        function __construct( $dbarr)
        {
            $this->conn_messaging= 	new DB($dbarr['DB_MESSAGING']);
			$this->conn_iro = new DB($dbarr['DB_IRO']);
			if(defined("REMOTE_CITY_MODULE")){
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
					$result = $this->conn_messaging->query_sql($sql_sms);
					return $result;
				}
            }
			else
			{
                return 0;
            }
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
								sms_text 	= '".$smstext."',
								parent_id   = '".$parent_id."',
								source		= '".$source."'";
					$result = $this->conn_messaging->query_sql($sql_sms);
					return $result;
				}
            }
			else
			{
                return 0;
            }
		}
		function sendEmail($email_id, $sender_email, $email_subject, $email_text, $source, $parent_id='',$email_id_cc='')
		{
            if($email_id!='' && $email_text!='' && $email_subject!='')
			{
				if($this->remoteflag == 1)
				{
					$params = array();
					$params['email_id'] 	 	= $email_id;
					$params['email_subject']  	= $email_subject;
					$params['email_text']  		= file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php");
					$params['email_id_cc'] 		= $email_id_cc;
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
					$email_text = addslashes(stripcslashes($email_text));
					$sql_email	=  "INSERT INTO tbl_common_intimations SET 
									sender_email  = '".$sender_email."', 
									email_id      = '".$email_id."',
									email_subject = '".$email_subject."',
									email_text    = '".addslashes(stripcslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php")))."',
									parent_id     = '".$parent_id."',
									email_id_cc   = '".$email_id_cc."',
									source        = '".$source."'";
					$res_email = $this->conn_messaging->query_sql($sql_email);
					return $res_email;
				}
            }
			else
			{
                return 0;
            }
		}
		function sendEmailwithAttachment($email_id, $sender_email, $email_subject, $email_text, $source,$parent_id,$attachment,$email_id_cc='')
		{
            if($email_id!='' && $email_text!='' && $email_subject!='' && APP_LIVE == 1) // This function will be called only on live server.
			{
				if($this->remoteflag == 1)
				{
					$params = array();
					$params['email_id'] 	 	= $email_id;
					$params['email_id_cc'] 	 	= $email_id_cc;
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
								email_id_cc   = '".$email_id_cc."',
								email_subject = '".$email_subject."',
								email_text    = '".addslashes(stripcslashes($email_text))."',
								attachment    = '".$attachment."',
								source        = '".$source."'";
					$res_email = $this->conn_messaging->query_sql($sql_email);
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
									email_text    = '".addslashes(stripcslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php")))."',
									parent_id     = '".$parent_id."',
									".$email_cc.$email_bcc.$reply."
									source        = '".$source."'";
									
					$res_email = $this->conn_messaging->query_sql($sql_email);                
					return $res_email;
				}
            }
			else
			{
                return 0;
            }
		}
		function sendEmailInvoiceMail($email_id, $sender_email, $email_subject, $email_text, $source,$attachment,$parent_id) 
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
								sender_email  = '".$sender_email."', 
								email_id      = '".$email_id."',
								parent_id     = '".$parent_id."', 
								email_subject = '".$email_subject."',
								email_text    = '".mysql_real_escape_string($email_text)."',
								attachment    = '".$attachment."',
								source        = '".$source."'";
                
					$res_email = $this->conn_messaging->query_sql($sql_email);
					return $res_email;
				}
            } 
			else
			{
                return 0;
            }
		}
		
		function sendScheduleSMS($parent_id, $mobileNo, $smstext, $source)
		{
            if($mobileNo!='' && $smstext!='')
			{
				$next_day_1  = date('Y-m-d ', strtotime(' +1 day'));
				$next_day_2  = date('Y-m-d ', strtotime(' +2 day'));
				$current_date = date('Y-m-d');
				
				if($this->remoteflag == 1)
				{
					$params = array();
					$params['mobile'] 		= $mobileNo;
					$params['sms_text'] 	= $smstext;
					$params['source'] 		= $source;
					$params['parent_id'] 	= $parent_id;
					$params['mod'] 			= 'common_idc';
					$apires = $this->callSMSEmailAPI($params);
					$apires = trim($apires);
					if(strtolower($apires) != 'success'){
						$this->smsEmailAPILog($params);
					}
					$smstext= addslashes(stripcslashes($smstext));
				}
				else
				{
					$smstext= addslashes(stripcslashes($smstext));
					$sqlSMSInfo	= "INSERT INTO tbl_common_intimations SET
									parent_id   = '".$parent_id."',
									mobile      = '".$mobileNo."', 
									sms_text    = '".$smstext."',
									source      = '".$source."'";
					$resSMSInfo = $this->conn_messaging->query_sql($sqlSMSInfo);
				}
				$sqlSMSInfo_0 = "SELECT parent_id, mobile FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) <= '".$current_date."' AND source = '".$source."'";
				$resSMSInfo_0 = $this->conn_iro->query_sql($sqlSMSInfo_0);
				if($resSMSInfo_0 && mysql_num_rows($resSMSInfo_0)>0)
				{
					$updtSMSInfo_0 = "UPDATE tbl_schedule_sms_email SET mobile = '".$mobileNo."', sms_text  = '".$smstext."' WHERE parent_id = '".$parent_id."' AND date(insert_date) <= '".$current_date."' AND source = '".$source."'";
					$resUpdtInfo_0 = $this->conn_iro->query_sql($updtSMSInfo_0);
				}
				
				$sqlSMSInfo_1 = "SELECT parent_id, mobile FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_1."' AND source = '".$source."'";
				$resSMSInfo_1 = $this->conn_iro->query_sql($sqlSMSInfo_1);
				if($resSMSInfo_1 && mysql_num_rows($resSMSInfo_1)>0)
				{
					$updtSMSInfo_1 = "UPDATE tbl_schedule_sms_email SET mobile = '".$mobileNo."', sms_text  = '".$smstext."' WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_1."' AND source = '".$source."'";
					$resUpdtInfo_1 = $this->conn_iro->query_sql($updtSMSInfo_1);
				}
				else
				{
					$sqlSMSMsg_1 = "INSERT INTO tbl_schedule_sms_email SET
									parent_id   = '".$parent_id."',
									mobile      = '".$mobileNo."', 
									sms_text    = '".$smstext."',
									source      = '".$source."',
									insert_date = '".$next_day_1."'";
					$resSMSMsg_1 = $this->conn_iro->query_sql($sqlSMSMsg_1);
				}
				$sqlSMSInfo_2 = "SELECT parent_id, mobile FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_2."' AND source = '".$source."'";
				$resSMSInfo_2 = $this->conn_iro->query_sql($sqlSMSInfo_2);
				if($resSMSInfo_2 && mysql_num_rows($resSMSInfo_2)>0)
				{
					$updtSMSInfo_2 = "UPDATE tbl_schedule_sms_email SET mobile = '".$mobileNo."', sms_text  = '".$smstext."' WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_2."' AND source = '".$source."'";
					$resUpdtInfo_2 = $this->conn_iro->query_sql($updtSMSInfo_2);
				}
				else
				{
					$sqlSMSMsg_2	= "INSERT INTO tbl_schedule_sms_email SET
									parent_id   = '".$parent_id."',
									mobile		= '".$mobileNo."', 
									sms_text	= '".$smstext."',
									source		= '".$source."',
									insert_date = '".$next_day_2."'";
					$resSMSMsg_2 = $this->conn_iro->query_sql($sqlSMSMsg_2);
				}
                return 1;
            }
			else
			{
                return 0;
			}
		}
		function sendScheduleEmail($parent_id, $email_id, $sender_email, $email_subject, $email_text, $source)
		{
            if($email_id!='' && $email_text!='' && $email_subject!='')
			{
				$next_day_1  = date('Y-m-d ', strtotime(' +1 day'));
				$next_day_2  = date('Y-m-d ', strtotime(' +2 day'));
				$current_date = date('Y-m-d');
				
				if($this->remoteflag == 1)
				{
					$params = array();
					$params['email_id'] 	 	= $email_id;
					$params['email_subject']  	= $email_subject;
					$params['email_text']  		= file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php");
					$params['email_id_cc'] 		= $email_id_cc;
					$params['sender_email'] 	= $sender_email;
					$params['parent_id'] 		= $parent_id;
					$params['source'] 	 		= $source;
					$params['mod'] 		 		= 'common_idc';
					$apires = $this->callSMSEmailAPI($params);
					$apires = trim($apires);
					if(strtolower($apires) != 'success'){
						$this->smsEmailAPILog($params);
					}
					$email_text = addslashes(stripcslashes($email_text));
				}
				else
				{
					$email_text = addslashes(stripcslashes($email_text));
					$sqlEmailInfo = "INSERT INTO tbl_common_intimations SET 
									parent_id     = '".$parent_id."',
									sender_email  = '".$sender_email."', 
									email_id      = '".$email_id."',
									email_subject = '".$email_subject."',
									email_text    = '".addslashes(stripcslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$email_text."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php")))."',
									source        = '".$source."'";
					$resEmailInfo = $this->conn_messaging->query_sql($sqlEmailInfo);
				}
				
				$sqlEmailInfo_0 = "SELECT parent_id, email_id FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) <= '".$current_date."' AND source = '".$source."'";
				$resEmailInfo_0 = $this->conn_iro->query_sql($sqlEmailInfo_0);
				if($resEmailInfo_0 && mysql_num_rows($resEmailInfo_0)>0)
				{
					$updtEmailInfo_0 = "UPDATE tbl_schedule_sms_email SET sender_email = '".$sender_email."', email_id = '".$email_id."',
					email_subject = '".$email_subject."',	email_text = '".$email_text."' WHERE parent_id = '".$parent_id."' and date(insert_date) <= '".$current_date."' AND source = '".$source."'";
					$resUpdtInfo_0 = $this->conn_iro->query_sql($updtEmailInfo_0);
				}
				
				$sqlEmailInfo_1 = "SELECT parent_id, email_id FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_1."' AND source = '".$source."'";
				$resEmailInfo_1 = $this->conn_iro->query_sql($sqlEmailInfo_1);
				if($resEmailInfo_1 && mysql_num_rows($resEmailInfo_1)>0)
				{
					$updtEmailInfo_1 = "UPDATE tbl_schedule_sms_email SET sender_email = '".$sender_email."', email_id = '".$email_id."',
					email_subject = '".$email_subject."',	email_text = '".$email_text."' WHERE parent_id = '".$parent_id."' and date(insert_date) = '".$next_day_1."' AND source = '".$source."'";
					$resUpdtInfo_1 = $this->conn_iro->query_sql($updtEmailInfo_1);
				}
				else
				{	
					$sqlEmailMsg_1	= "INSERT INTO tbl_schedule_sms_email SET 
									parent_id     = '".$parent_id."',
									sender_email  = '".$sender_email."', 
									email_id      = '".$email_id."',
									email_subject = '".$email_subject."',
									email_text    = '".$email_text."',
									source        = '".$source."',
									insert_date   = '".$next_day_1."'";
					$resEmailMsg_1 = $this->conn_iro->query_sql($sqlEmailMsg_1);
				}
				$sqlEmailInfo_2 = "SELECT parent_id, email_id FROM tbl_schedule_sms_email WHERE parent_id = '".$parent_id."' AND date(insert_date) = '".$next_day_2."' AND source = '".$source."'";
				$resEmailInfo_2 = $this->conn_iro->query_sql($sqlEmailInfo_2);
				if($resEmailInfo_2 && mysql_num_rows($resEmailInfo_2)>0)
				{
					$updtSMSInfo_2 = "UPDATE tbl_schedule_sms_email SET sender_email = '".$sender_email."', email_id = '".$email_id."', email_subject = '".$email_subject."', email_text  = '".$email_text."' WHERE parent_id = '".$parent_id."' and date(insert_date) = '".$next_day_2."' AND source = '".$source."'";
					$resUpdtInfo_2 = $this->conn_iro->query_sql($updtSMSInfo_2);
				}
				else
				{
					$sqlEmailMsg_2	= "INSERT INTO tbl_schedule_sms_email SET 
								parent_id     = '".$parent_id."',
								sender_email  = '".$sender_email."', 
								email_id      = '".$email_id."',
								email_subject = '".$email_subject."',
								email_text    = '".$email_text."',
								source        = '".$source."',
								insert_date   = '".$next_day_2."'";
					$resEmailMsg_2 = $this->conn_iro->query_sql($sqlEmailMsg_2);
				}
                return 1;
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
			}else if(isset($_SESSION['parentid'])){
				$parentid = $_SESSION['parentid'];
			}
			$sqlSMSEmailLog =  "INSERT INTO d_jds.tbl_smsemail_api_log SET
								parentid 	= '".$parentid."',
								mobile		= '".$params['mobile']."',
								emailid		= '".addslashes($params['email_id'])."',
								source		= '".addslashes($params['source'])."',
								parameters 	= '".json_encode($params)."',
								updatedOn 	= NOW()";
			$resSMSEmailLog = $this->conn_iro->query_sql($sqlSMSEmailLog);
		}
    }
?>
