<?php
class original_data_class extends DB
{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $ucode		= null;
	
	
	function __construct($params)
	{
		$this->params = $params;
        if(trim($this->params['data_city'])=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendResponse($message));
			die();
		}
		
		
		
		$this->data_city 	= $this->params['data_city'];
		
		
		$this->setServers();
		
	}
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_iro    	= $db[$conn_city]['iro']['master'];
		
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		$this->conn_log   		= $db[$conn_city]['data_correction']['master'];
		
		
		
		$this->conn_city  = $conn_city;
		
	}
	function processData()
	{
		echo $sqlFetchData = "SELECT parentid,original_date FROM test.tbl_orgdate_fixed_contracts WHERE done_flag = 0 LIMIT 5000"; 
		
		$resFetchData = parent::execQuery($sqlFetchData, $this->conn_iro);
		if($resFetchData && parent::numRows($resFetchData)>0){
			while($row_data = parent::fetchData($resFetchData)){
				$parentid = trim($row_data['parentid']);
				$original_date = $this->getOriginalDate($parentid);
				
				
				if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$original_date)){
					
					echo "\n\n\n".$sqlUpdate = "UPDATE tbl_companymaster_extradetails SET original_date = '".$original_date."' WHERE parentid = '".$parentid."'";
					$resUpdate = parent::execQuery($sqlUpdate, $this->conn_iro);
					
					echo "\n\n\nAPI RESPONSE : ".$response = $this->webAPICall($parentid);
					
					
					$param_array = array();
					$curl_url                    =  "http://192.168.17.217:811/services/instant_live.php";
					$param_array['parentid']    =      $parentid;
					$param_array['data_city']   =      $this->conn_city;
					$param_array['module']      =      'ORIGDT';
					$param_array['ucode']          =      'ORIGDT';
					$instant_url =  $curl_url."?".http_build_query($param_array);
					
					echo "\n\n\n".$insertInstant  = "INSERT INTO online_regis1.tbl_instant_live
									SET
									parentid        =    '".$param_array['parentid']."',
									data_city    =    '".$param_array['data_city']."',
									url          =    '".$instant_url."',
									source       =    '".$param_array['module']."',
									ucode        =    '".$param_array['ucode']."',
									entry_date   =    NOW()";
					$resInstant	  = parent::execQuery($insertInstant, $this->conn_idc);
					
					$sqlUpdtDoneFlag = "UPDATE test.tbl_orgdate_fixed_contracts SET done_flag = 1 WHERE parentid = '".$parentid."'";
					$resUpdtDoneFlag = parent::execQuery($sqlUpdtDoneFlag, $this->conn_iro);
				}else{
					$sqlUpdtDoneFlag = "UPDATE test.tbl_orgdate_fixed_contracts SET done_flag = 2 WHERE parentid = '".$parentid."'";
					$resUpdtDoneFlag = parent::execQuery($sqlUpdtDoneFlag, $this->conn_iro);
				}
			}
		}
		
		
	}
	function getOriginalDate($parentid){
		$org_date = '';
		echo "\n\n\n".$sqlGetDate = "SELECT date(updatedOn) as updatedOn,request_module FROM db_iro.web_api_log WHERE parentid = '".$parentid."' ORDER BY updatedOn ASC LIMIT 1";
		$resGetDate = parent::execQuery($sqlGetDate, $this->conn_log);
		if($resGetDate && parent::numRows($resGetDate)>0){
			$row_data = parent::fetchData($resGetDate);
			$request_module = trim($row_data['request_module']);
			$updatedOn 		= trim($row_data['updatedOn']);
			if($request_module == 'DEALCLOSE_LIVE'){
				$org_date = $updatedOn;
			}else{
				$dataArr = explode(".",$parentid);
				if((count($dataArr) == 4) && (strlen($dataArr['2']) == 12)){
					echo "\n\n\n".$dateevar = substr($dataArr['2'],0,6);
					echo "\n\n\n".$org_date = "20".substr($dataArr['2'],0,2)."-".substr($dataArr['2'],2,2)."-".substr($dataArr['2'],4,2);
				}
			}
		}else{
			$dataArr = explode(".",$parentid);
			if((count($dataArr) == 4) && (strlen($dataArr['2']) == 12)){
				echo "\n\n\n".$dateevar = substr($dataArr['2'],0,6);
				echo "\n\n\n".$org_date = "20".substr($dataArr['2'],0,2)."-".substr($dataArr['2'],2,2)."-".substr($dataArr['2'],4,2);
			}
			
		}
		return $org_date;
	}
	function webAPICall($parentid)
	{
		if(!empty($parentid))
		{
			$ucode			=	'ORIGDT';
			$uname			=	'ORIGDT';
			$validationcode	=	'DBBKND';
			$data_city 		= 	strtoupper($this->data_city);

			switch($data_city)
			{
				case 'MUMBAI' :
						$url = "http://".MUMBAI_CS_API."/";
						$city_indicator = "main_city";
				break;

				case 'AHMEDABAD' :
					$url = "http://".AHMEDABAD_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'BANGALORE' :
					$url = "http://".BANGALORE_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'CHENNAI' :
					$url = "http://".CHENNAI_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'DELHI' :
					$url = "http://".DELHI_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'HYDERABAD' :
					$url = "http://".HYDERABAD_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'KOLKATA' :
					$url = "http://".KOLKATA_CS_API."/";
					$city_indicator = "main_city";
				break;

				case 'PUNE' :
					$url = "http://".PUNE_CS_API."/";
					$city_indicator = "main_city";
				break;

				default: 
					$url = "http://".REMOTE_CITIES_CS_API."/";
					$city_indicator = "remote_city";
					
			}

			if(preg_match("/\bjdsoftware.com\b/i", $_SERVER['HTTP_HOST']))
			{
				if($city_indicator == "remote_city")
				{
					$curl_url	= "http://". $_SERVER['HTTP_HOST']."/csgenio/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
				}
				else
				{
					$curl_url	= "http://". $_SERVER['HTTP_HOST']."/csgenio/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
				}
			}
			else
			{
				$curl_url	= $url."/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=".urlencode($ucode)."&validationcode=".$validationcode."&uname=".urlencode($uname)."&insta_activate=2";
			}
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resjson = curl_exec($ch);
			curl_close($ch);
			return $resjson;
		}
	}
	
	private function sendResponse($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	
}
?>
