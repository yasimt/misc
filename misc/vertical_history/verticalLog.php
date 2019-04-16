<?php 
class verticalLog extends DB
{	
	function __construct($parentid,$ucode,$type_flag,$dbarr) 
	{	
		$this->dbarr 			= $dbarr;
		$this->conn_iro   		= new DB($this->dbarr['DB_IRO']);
		$this->conn_local 		= new DB($this->dbarr['LOCAL']);
		
		$this->parentid	 		= $parentid;
		$this->ucode	 		= str_replace("[Auto]","",$ucode);
		$this->uname	 		= $this->get_updated_user_name();
		$this->type_flag 		= $type_flag;
		$this->module	 		= 'CS';
		$this->vertical_name  	= $this->getVerticalName();
		
		$this->remote_city_flag = 0;
		if(defined("REMOTE_CITY_MODULE")){
			$this->remote_city_flag = 1;
		}
		
		$this->city_ip = '';
		if($_SERVER['SERVER_ADDR']!='')
		{
			$serverip = explode(".",$_SERVER['SERVER_ADDR']);
			$this->city_ip = $serverip[2];
		}
		$this->extra_details	= $this->get_extra_details_main();
		$this->data_city  		= $this->getDataCity();
		$this->docid 			= $this->docid_creator();
		
		
		$this->proceed_flag = 1;
		
		if(($this->parentid == '') || ($this->docid == '') || ($this->data_city == '') || ($this->vertical_name == '') || ($this->ucode == '') || ($this->city_ip == '') || intval($this->type_flag <=0) || ($this->extra_details['valid_flag'] ==0))
		{
			$this->proceed_flag =0;
		}
		$this->logflag 	  = 0;
		if($this->proceed_flag == 1)
		{
			$this->LogCurrentInfo()	;
		}
	}
	function __destruct()
	{
		if(($this->logflag != 2) && ($this->proceed_flag ==1))
		{
			$this->updateLog();
		}
		unset($this->parentid);
		unset($this->ucode);
		unset($this->uname);
		unset($this->type_flag);
		unset($this->vertical_name);
		unset($this->city_ip);
		unset($this->data_city);
		unset($this->proceed_flag);
		unset($this->logflag);
	}
	function updateLog()
	{
		if($this->logflag == 1)
		{
			$this->LogCurrentInfo();		
		}	
		
		if($this->logflag == 2)
		{
			$this->removematchlog();
			
			if(count($this->oldVerticalDetails) || count($this->newVerticalDetails))
			{
				$sqlInsertVerticalHistory = "INSERT INTO tbl_vertical_bform_details SET
												 parentid				= '".$this->parentid."',
												 docid					= '".$this->docid."',
												 companyname			= '".addslashes(stripslashes($this->extra_details['companyname']))."',
												 data_city				= '".$this->data_city."',
												 vertical_name			= '".$this->vertical_name."',
												 ucode		 			= '".$this->ucode."',
												 uname		 			= '".$this->uname."',
												 module		 			= '".$this->module."',
												 parent_pid				= '".$this->extra_details['ref_parentid']."',
												 insertdate				= '".date("Y-m-d H:i:s")."',
												 business_details_old 	= '".http_build_query($this->oldVerticalDetails)."',
												 business_details_new 	= '".http_build_query($this->newVerticalDetails)."'";
				
				$resInsertVerticalHistory = $this->conn_iro->query_sql($sqlInsertVerticalHistory);
			}
			$this->oldVerticalDetails = array();
			$this->newVerticalDetails = array();					
			$this->logflag =0;
		}
	}
	function removematchlog()
	{
		$this->oldVerticalDetails = array_map('trim',$this->oldVerticalDetails);
		$this->newVerticalDetails = array_map('trim',$this->newVerticalDetails);
		if($this->logflag == 2)
		{
			if(count($this->oldVerticalDetails) > 0)
			{
				foreach($this->newVerticalDetails as $key => $value)
				{					
					if(strtolower($value) == strtolower($this->oldVerticalDetails[$key]))
					{
						unset($this->oldVerticalDetails[$key]);
						unset($this->newVerticalDetails[$key]);
					}	
				}
			}					
		}
		return false;
	}
	function LogCurrentInfo()
	{
		$vertical_info_arr = $this->LogVerticalDetails();
		
		switch($this->logflag)
		{
			case '0':
				$this->oldVerticalDetails = $vertical_info_arr;
				$this->logflag = 1;					
				break;
			case '1':
				$this->newVerticalDetails = $vertical_info_arr;				
				$this->logflag = 2;			

				break;
		}
	}
	function LogVerticalDetails()
	{
		$vertical_info_arr	  =	array();
		$sqlFetchVerticalInfo =	"SELECT type_flag,iro_type_flag,website_type_flag,sub_type_flag,type_flag_actions,ref_parentid,companyname FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";

		$resFetchVerticalInfo = $this->conn_iro->query_sql($sqlFetchVerticalInfo);
		
		if($resFetchVerticalInfo && mysql_num_rows($resFetchVerticalInfo)>0)
		{
			$row_vertical_info						= mysql_fetch_assoc($resFetchVerticalInfo);
			$vertical_info_arr['type_flag'] 		= $row_vertical_info['type_flag'];
			$vertical_info_arr['iro_type_flag'] 	= $row_vertical_info['iro_type_flag'];
			$vertical_info_arr['website_type_flag'] = $row_vertical_info['website_type_flag'];
			$vertical_info_arr['sub_type_flag'] 	= $row_vertical_info['sub_type_flag'];
			$vertical_info_arr['type_flag_actions'] = $row_vertical_info['type_flag_actions'];
			$vertical_info_arr['ref_parentid'] 		= $row_vertical_info['ref_parentid'];
			$vertical_info_arr['companyname'] 		= $row_vertical_info['companyname'];
		}
		return $vertical_info_arr;
	}
	function get_updated_user_name()
	{
		$final_empname 	= $this->ucode;
		$sqlEmplogin = "SELECT CONCAT(empFName,' ',empLName) AS empname FROM emplogin WHERE empCode='".$this->ucode."'";
		$resEmplogin = $this->conn_iro->query_sql($sqlEmplogin);
		if($resEmplogin && mysql_num_rows($resEmplogin)>0)
		{
			$row_emplogin 	= mysql_fetch_assoc($resEmplogin);
			$empname 		= trim(ucwords(strtolower($row_emplogin['empname'])));
			if(!empty($empname))
			{
				$final_empname = $empname;
			}
		}
		else
		{
			$sqlMktgUser = "SELECT empName FROM mktgEmpMaster WHERE mktEmpCode='".$this->ucode."'";
			$resMktgUser = $this->conn_local->query_sql($sqlMktgUser);
			if($resMktgUser && mysql_num_rows($resMktgUser)>0)
			{
				$row_mktguser = mysql_fetch_assoc($resMktgUser);
				$empname 	  = trim(ucwords(strtolower($row_mktguser['empName'])));
				if(!empty($empname))
				{
					$final_empname = $empname;
				}
			}
		}
		return $final_empname;
	}
	function getDataCity()
	{
		$data_city = 'DATA_CITY';	
		switch($this->city_ip)
		{
			case 0:
				$data_city = "Mumbai";
				break;
			case 1:
			case 17:
				$data_city = $this->extra_details['data_city'];
				break;
			case 8:
				$data_city = "Delhi";
				break;
			case 16:
				$data_city = "Kolkata";
				break;
			case 26:
				$data_city = "Bangalore";
				break;
			case 32:
				$data_city = "Chennai";
				break;
			case 40:
				$data_city = "Pune";
				break;
			case 50:
				$data_city = "Hyderabad";
				break;
			case 35:	
			case 56:
				$data_city = "Ahmedabad";
				break;
			case 64:
				if($this->remote_city_flag == 1){
					$data_city = $this->extra_details['data_city'];
				}else{
					$data_city = "Mumbai";
				}
				break;
		}
		return $data_city;
	}
	function get_extra_details_main()
	{
		$extra_details_arr = array();
		$extra_details_arr['valid_flag'] = 0;
		$sqlExtradetails	= "SELECT data_city,companyname,ref_parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resExtradetails	= $this->conn_iro->query_sql($sqlExtradetails);
		if($resExtradetails && mysql_num_rows($resExtradetails)>0)
		{
			$row_extra_details					= mysql_fetch_assoc($resExtradetails);
			$extra_details_arr['data_city'] 	= $row_extra_details['data_city'];
			$extra_details_arr['companyname'] 	= $row_extra_details['companyname'];
			$extra_details_arr['ref_parentid'] 	= $row_extra_details['ref_parentid'];
			$extra_details_arr['valid_flag'] 	= 1;
		}
		return $extra_details_arr;
	}
	function getVerticalName()
	{
		$vertical_name = '';
		if(intval($this->type_flag)>0)
		{
			$sqlFetchVerticalName = "SELECT vertical_name FROM tbl_vertical_master WHERE type_flag_value = '".$this->type_flag."'";
			$resFetchVerticalName = $this->conn_iro->query_sql($sqlFetchVerticalName);
			if($resFetchVerticalName && mysql_num_rows($resFetchVerticalName)>0)
			{
				$vertical_name = $this->exactVerticalName();
				if(empty($vertical_name))
				{
					$row_vertical_name = mysql_fetch_assoc($resFetchVerticalName);
					$vertical_name = trim($row_vertical_name['vertical_name']);
					$vertical_name = ucwords(strtolower($vertical_name));
				}
			}
		}
		return $vertical_name;
	}
	function exactVerticalName()
	{
		$exact_vertical_name = '';
		$vertical_name_arr = array(
									"1" 			=> "Table Reservation",
									"2" 			=> "Doctor Reservation",
									"4" 			=> "Gas Booking",
									"8" 			=> "Restaurant",
									"16" 			=> "Wine",
									"32" 			=> "Shopfront",
									"64" 			=> "Laundry",
									"128" 			=> "Grocery",
									"256" 			=> "Spa & Salon",
									"512" 			=> "Vehicle",
									"1024"		 	=> "Bus Booking",
									"2048" 			=> "Cab Booking",
									"4096" 			=> "Water Purifier",
									"8192" 			=> "Courier",
									"16384" 		=> "AC Service",
									"32768" 		=> "Pharmacy",
									"65536" 		=> "Diagnostic Lab",
									"131072" 		=> "Test Drive",
									"262144" 		=> "Flower",
									"524288" 		=> "Cake",
									"1048576" 		=> "Sweet",
									"2097152" 		=> "Flight Booking",
									"4194304" 		=> "Hotel Booking",
									"8388608" 		=> "Book Service",
									"16777216" 		=> "Movies",
									"33554432" 		=> "Mineral Water",
									"67108864" 		=> "Pathology",
									"134217728" 	=> "Insurance",
									"268435456" 	=> "Loan",
									"536870912" 	=> "Banquet Halls",
									"1073741824" 	=> "Dairy Service"
									);
		if(key_exists($this->type_flag,$vertical_name_arr))
		{
			$exact_vertical_name = $vertical_name_arr[$this->type_flag];
		}
		return $exact_vertical_name;
	}
	function docid_creator()
	{
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
			case 35:	
			case 56:
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
				$message = "Sever Address : ".$_SERVER['SERVER_ADDR']."<br>City IP : ".$this->city_ip."<br>Parentid : ".$this->parentid."<br>Docid : ".$this->docid."<br>Data City : ".$this->data_city."<br>Module : ".$this->module."<br>Flow : Vertical History";
				$from = "noreply@justdial.com";
				include_once("../library/class_email_sms_send.php");
				$emailsms_obj = new email_sms_send($this->dbarr);
				if($emailsms_obj)
				{
					$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'cs');
				}
				break;			
		}
		
		return $docid;
	}
	function stdcode_master()
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
}
?>
