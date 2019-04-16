<?php
class doc_hosp_redirection_class extends DB
{
	var  $conn_local   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$vertical_name 	= trim($params['vertical_name']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		if(trim($vertical_name)=='')
		{
			$message = "Vertical Name is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->parentid  		= $parentid;
		$this->data_city 		= $data_city;
		$this->module  	  		= strtoupper($module);
		$this->vertical_name  	= strtoupper($vertical_name);
		
		$valid_vertical_arr = array("DOCTOR","HOSPITAL");
		
		if(!in_array($this->vertical_name,$valid_vertical_arr))
		{
			$message = "Invalid Vertical Name.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->setServers();
		$this->docid		=   $this->docid_creator();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_local  	= $db[$conn_city]['d_jds']['master'];
		
	}
	function getDocHospRedirectionFlag()
	{
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$rsvn_type_url 	 = "http://".WEB_SERVICES_API."/web_services/rsvnType.php";
		}
		else
		{
			$rsvn_type_url = "http://sunnyshende.jdsoftware.com/web_services/web_services/rsvnType.php";	
		}
		
		if($this->vertical_name == 'DOCTOR')
		{
			$doc_flag = 0;
			$rsvn_type_data  = "docid=".$this->docid."&type_flag=2&sub_type_flag=1&backend_flow=1";
			$rsvn_type_resp  = $this->curl_call_post($rsvn_type_url,$rsvn_type_data);
			$doc_data_result = json_decode($rsvn_type_resp,true); 
			
			if(isset($doc_data_result['results']['multilocation']) && !empty($doc_data_result['results']['multilocation']))
			{
				$arry_count = count($doc_data_result['results']['multilocation']);
				if($arry_count >1)
				{
					$doc_flag =1;
				}
			}
			$result_msg_arr['data'] = $doc_flag;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else if($this->vertical_name == 'HOSPITAL')
		{
			$hosp_flag = 0;
			$rsvn_type_data   = "docid=".$this->docid."&type_flag=2&sub_type_flag=2&backend_flow=1";
			$rsvn_type_resp   = $this->curl_call_post($rsvn_type_url,$rsvn_type_data);
			$hosp_data_result = json_decode($rsvn_type_resp,true); 
			
			if(isset($hosp_data_result['results']['hospital']) && !empty($hosp_data_result['results']['hospital']))
			{
				$arry_count = count($hosp_data_result['results']['hospital']);
				if($arry_count >=1)
				{
					$hosp_flag = 1;
				}
			}
			$result_msg_arr['data'] = $hosp_flag;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else
		{
			$message = "Invalid Vertical Name.";
			echo json_encode($this->send_die_message($message));
			die();
		}
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	public function docid_creator()
	{	
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI':
					$docid = "022".$this->parentid;
					break;
					
				case 'DELHI':
					$docid = "011".$this->parentid;
					break;
					
				case 'KOLKATA':
					$docid = "033".$this->parentid;
					break;
					
				case 'BANGALORE':
					$docid = "080".$this->parentid;
					break;
					
				case 'CHENNAI':
					$docid = "044".$this->parentid;
					break;
					
				case 'PUNE':
					$docid = "020".$this->parentid;
					break;
					
				case 'HYDERABAD':
					$docid = "040".$this->parentid;
					break;
					
				case 'AHMEDABAD':
					$docid = "079".$this->parentid;
					break;	
						
				default :
					$docid_stdcode 	= $this->stdcode_master();
					if($docid_stdcode){
						$temp_stdcode = ltrim($docid_stdcode,0);
					}
					$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
					if(in_array(strtoupper($this->data_city),$ArrCity)){
						$sqlStdCode	= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
						$resStdCode = parent::execQuery($sqlStdCode, $this->conn_local);
						$rowStdCode =  mysql_fetch_array($resStdCode);
						$cityStdCode	=  $rowStdCode['stdcode'];
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
		}
		else
		{
			$docid = "022".$this->parentid;
		}
		return $docid;
	}
	public function stdcode_master()
	{
		$sql_stdcode = "SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		$res_stdcode = parent::execQuery($sql_stdcode, $this->conn_local);
		if($res_stdcode)
		{
			$row_stdcode	=	mysql_fetch_assoc($res_stdcode);
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
	function curl_call_post($curlurl,$data)
	{	
		#echo $curlurl.'?'.$data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch); 
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
}
?>
