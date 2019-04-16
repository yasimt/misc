<?php
define("DB_JD_Products","db_justdial_products");

class verticalAction extends DB 
{
	public function __construct($parentid) {
		
		GLOBAL $dbarr;
		$this->dbarr		= 	$dbarr;
		$this->conn_iro   	= 	new DB($dbarr['DB_IRO']);
		$this->conn_local   =  	new DB($dbarr['LOCAL']);
		$this->conn_tme    	= 	new DB($dbarr['DB_TME']);
		$this->conn_idc		=	new DB($dbarr['IDC']);
		$this->conn_fnc		=	new DB($dbarr['DB_FNC']);
		
		$this->parentid 	=	$parentid;
		$this->ucode		=	$_SESSION['ucode'];
		$this->uname        =   $_SESSION['uname'];
		$this->module       =   $_SESSION['module'];
		$this->data_city	=  	DATA_CITY;
		
		$this->gen_arr_shadow = $this->get_generalinfo_shadow();
		
		if($_SERVER['SERVER_ADDR']!='')
		{
			$serverip = explode(".",$_SERVER['SERVER_ADDR']);
			$this->city_ip = $serverip[2];
		}
		if($this->city_ip == '')
		{
			$mailto = "imteyaz.raja@justdial.com";
			$subject = "City IP is blank";
			$message = "SERVER ADDRESS : ".$_SERVER['SERVER_ADDR']."<br>Parentid : ".$parentid."<br>SERVER ARGUMENT : ".$_SERVER['argv'][1]."<br>Module : ".$this->module."<br>Flow : Vertical Action Class";
			$from = "noreply@justdial.com";
			include_once("../library/class_email_sms_send.php");
			$emailsms_obj = new email_sms_send($dbarr);
			if($emailsms_obj)
			{
				$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'cs');
			}
		}
		$this->remote_city_flag = 0;
		if(defined("REMOTE_CITY_MODULE"))
		{
			$this->remote_city_flag = 1;
		}
		$this->docid = $this->docid_creator();
	}
	 
    public function vertical_mandate($type_flag) 
    {
		if($this->city_ip == ''){
			return;
		}
		$service_name = $this->getVerticalName($type_flag);
		
		$mandate_type = $this->getMandateType($type_flag);
		
		if(!empty($service_name) && !empty($mandate_type))
		{
			$sqlInsrtMandate	=  "INSERT INTO ".DB_JD_Products.".tbl_vertical_mandate_console
									SET 
									docid			= '".$this->docid."',
									parentid		= '".$this->parentid."',
									Empcode			= '".$this->ucode."',
									EmpName			= '".$this->uname."',
									data_city		= '".addslashes(stripslashes($this->gen_arr_shadow['data_city']))."',
									type_flag		= '".$type_flag."',
									entry_date		= '".date("Y-m-d H:i:s")."',
									updatedOn		= '".date("Y-m-d H:i:s")."',
									source			= '".$this->module."',
									service_name	= '".$service_name."',
									mandate_type	= '".$mandate_type."'
									
									ON DUPLICATE KEY UPDATE
									Empcode			= '".$this->ucode."',
									EmpName			= '".$this->uname."',
									data_city		= '".addslashes(stripslashes($this->gen_arr_shadow['data_city']))."',
									type_flag		= '".$type_flag."',
									updatedOn		= '".date("Y-m-d H:i:s")."',
									source			= '".$this->module."',
									service_name	= '".$service_name."',
									mandate_type	= '".$mandate_type."'";
			$resInsrtMandate	=	$this->conn_idc->query_sql($sqlInsrtMandate);
		}
			
		$sqlInsrtMandateLog	=	"INSERT INTO ".DB_JD_Products.".tbl_vertical_mandate_console_log
								 SET
								 docid			= '".$this->docid."',
								 parentid		= '".$this->parentid."',
								 Empcode		= '".$this->ucode."',
								 EmpName		= '".$this->uname."',
								 data_city		= '".addslashes(stripslashes($this->gen_arr_shadow['data_city']))."',
								 type_flag		= '".$type_flag."',
								 updatedOn		= '".date("Y-m-d H:i:s")."',
								 source			= '".$this->module."',
								 service_name	= '".$service_name."',
								 mandate_type	= '".$mandate_type."'";
		$resInsrtMandateLog	=	$this->conn_idc->query_sql($sqlInsrtMandateLog);
				
	}
	public function get_generalinfo_shadow(){
		
		$sqlGeneralInfo	= "SELECT data_city,companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
		$resGeneralInfo	= $this->conn_iro->query_sql($sqlGeneralInfo);
		$rowGeneralInfo	= $this->conn_iro->fetchData($resGeneralInfo);
		
		return $rowGeneralInfo;
	}
	public function docid_creator()
	{
		GLOBAL $dbarr;
		$docid_stdcode 	= $this->stdcode_master();
		
		switch($this->city_ip){
			case 0:
				$docid = "022".$this->parentid;
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
				$docid = $stdcode.$this->parentid;
				break;
			case 8:
				$docid = "011".$this->parentid;
				break;
			case 16:
				$docid = "033".$this->parentid;
				break;
			case 26:
				$docid = "080".$this->parentid;
				break;
			case 32:
				$docid = "044".$this->parentid;
				break;
			case 40:
				$docid = "020".$this->parentid;
				break;
			case 50:
				$docid = "040".$this->parentid;
				break;
			case 56:
			case 35:
				$docid = "079".$this->parentid;
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
					$docid = $stdcode.$this->parentid;
				}
				else
				{
					$docid = "022".$this->parentid;
				}
				break;
			default:
				$docid = "022".$this->parentid;
				$mailto = "imteyaz.raja@justdial.com";
				$subject = "Docid Creator Issue";
				$message = "Sever Address : ".$_SERVER['SERVER_ADDR']."<br>City IP : ".$this->city_ip."<br>Parentid : ".$this->parentid."<br>Docid : ".$this->docid."<br>Data City : ".$this->data_city."<br>Module : ".$this->module."<br>Flow : Vertical Action Class";
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
	public function getVerticalName($type_flag)
	{
		$vertical_name = '';
		$sqlFetchVerticalName = "SELECT vertical_name from tbl_vertical_master WHERE type_flag_value = '".$type_flag."'";
		$resFetchVerticalName = $this->conn_iro->query_sql($sqlFetchVerticalName);
		$rowFetchVerticalName = $this->conn_iro->fetchData($resFetchVerticalName);
		$vertical_name = $rowFetchVerticalName['vertical_name'];
		return $vertical_name;
	}
	public function getMandateType($type_flag)
	{
		$mandate_type_val = '';
		if(intval($type_flag)>0)
		{
			if($type_flag == 8)
			{
				$sqlECSMandateType = "SELECT parentid FROM ".DB_ECS.".ecs_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0 AND mandate_type='RESTO'";
			}
			else
			{ 
				$sqlECSMandateType = "SELECT parentid FROM ".DB_ECS.".ecs_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0  AND vertical_flag>0 AND mandate_type='Vertical' AND vertical_flag = '".$type_flag."'";
			}
			$resECSMandateType = $this->conn_fnc->query_sql($sqlECSMandateType);
			if($resECSMandateType && mysql_num_rows($resECSMandateType)>0)
			{
				$mandate_type_val = 'ECS';
			}
			else
			{
				if($type_flag == 8)
				{
					$sqlSIMandateType = "SELECT parentid FROM ".DB_SI.".si_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0 AND mandate_type='RESTO'";
				}
				else
				{
					$sqlSIMandateType = "SELECT parentid FROM ".DB_SI.".si_mandate WHERE parentid = '".$this->parentid."' AND deactiveflag=0 AND ecs_stop_flag=0  AND vertical_flag>0 AND mandate_type='Vertical' AND vertical_flag = '".$type_flag."'";
				}
					$resSIMandateType = $this->conn_fnc->query_sql($sqlSIMandateType);
				if($resSIMandateType && mysql_num_rows($resSIMandateType)>0)
				{
					$mandate_type_val = 'SI';
				}
			}
		}
		return $mandate_type_val;
	}
	public function saveRestVerticalMandate()
	{
		$rest_tagged_flag = 0;
		$sqlRestaurantTypeFlagChk = "SELECT parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."' AND type_flag&8=8";
		$resRestaurantTypeFlagChk = $this->conn_iro->query_sql($sqlRestaurantTypeFlagChk);
		if($resRestaurantTypeFlagChk && mysql_num_rows($resRestaurantTypeFlagChk)>0)
		{
			$this->vertical_mandate(8);
		}
		return $rest_tagged_flag;
	}
	
	public function sendProposal($filename,$emailid,$subject,$message)
	{
		$path = APP_PATH."business/VerticalProposal/";
		$file = $path.$filename;
		if (!file_exists($file)){
			echo '0';
			return;
		}
		$file_size = filesize($file);
		$handle = fopen($file, "r");
		$content2 = fread($handle, $file_size);
		$content=str_replace('&nbsp;','',$content2);
		fclose($handle);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$header = "From: Justdial <noreply>\r\n";
		$header .= "Reply-To: noreply@justdial.com\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$nmessage .= "--".$uid."\r\n";
		$nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$nmessage .= $message."\r\n\r\n";
		$nmessage .= "--".$uid."\r\n";
		$nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use diff. types here
		$nmessage .= "Content-Transfer-Encoding: base64\r\n";
		$nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$nmessage .= $content."\r\n\r\n";
		
		$nmessage .= "--".$uid."--";
		
		if($emailid)
		{
			$mailing = mail($emailid, $subject, $nmessage, $header);
		}
		if($mailing)
		{
			echo '1';
		}
		else
		{
			echo '0';
		}
	}
	function sendEmailwithAttachment($filename,$emailid,$subject,$message)
	{
		GLOBAL $dbarr;
		include_once('../library/class_email_sms_send.php');
		$emailsms_obj = new email_sms_send($dbarr);
		
		$filepath = APP_PATH."/business/VerticalProposal/".$filename;
		if (!file_exists($filepath)){
			echo '0';
			return;
		}
		$attachmentpath = APP_URL."/business/VerticalProposal/".$filename;
		$from ="noreply@justdial.com";
		if(!empty($attachmentpath) && !empty($emailid))
		{
			echo $mailres = $emailsms_obj->sendEmailwithAttachment($emailid, $from, $subject, $message , 'cs',$this->parentid,$attachmentpath);
		}
		else
		{
			echo "0";
		}
	}
}
?>
