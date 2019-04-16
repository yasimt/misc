<?php
class loginClass extends DB
{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $conn_city		= null;
	var	 $city_db		= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	var  $data_city		= null;
	var  $module		= null;
	function __construct($params){
		
		$this->params = $params;
		$empcode 	= trim($this->params['empcode']);
		$data_city 	= trim($this->params['data_city']);
		
		if($empcode == ''){
			$message = "Employee Code is blank.";
			echo json_encode($this->sendResponseMsg($message,1));
			die();
		}
		if($data_city==''){
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message,1));
			die();
		}
		$this->empcode 		= $empcode;
		$this->data_city 	= $data_city;
		$this->setServers();
	}
	
	function setServers(){
		GLOBAL $db;
		
		$this->conn_city 	= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->city_db 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote_cities');	
		$this->conn_local  	= $db[$this->conn_city]['d_jds']['master'];
		
	}
	
	public function getSSOInfo(){
		$responseArr 	= array();
		
		$empData 		= $this->getEmpInfo();
		if((count($empData)>0) && ($empData['errorcode'] === 0)){
			$responseArr['empdata'] = $empData;
		}
		
		$addInfo		= $this->getUserAddInfo();
		if(count($addInfo)>0){
			$responseArr['addinfo'] = $addInfo;
		}
		
		$serverData		= $this->getServerInfo();
		if(count($serverData)>0){
			$responseArr['serverdata'] = $serverData;
		}
		
		$cityInfo 		= $this->cityInfo();
		if(count($cityInfo)>0){
			$responseArr['cityinfo'] = $cityInfo;
		}
		return $responseArr;
	}
	
	private function getEmpInfo(){
		
		$empInfoArr					=	array();
		$paramsArr					=	array();
		$paramsArr['url'] 			= 	SSO_API_URL.'api/getEmployee_xhr.php';
		$paramsArr['formate'] 		= 	'basic';
		$paramsArr['headerJson'] 	= 	'json';
		$paramsArr['method'] 		= 	'post';
		
		
		$postData					=	array();
		$postData['empcode']		=	 $this->empcode;
		$postData['textSearch']		=	4;
		$postData['reseller_flag']	=	1;
		$postData['lin_update']		=	1;
		$paramsArr['auth_token']	= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
		$paramsArr['postData'] 		= 	json_encode($postData);
		$empInfoArr 				= 	json_decode($this->curlCall($paramsArr),true);
		return $empInfoArr;
	}
	
	private function getUserAddInfo(){
			
			$userAddInfo 		= array();
			$sqlTimeSlotInfo 	= "SELECT alloc_time_slot FROM tbl_time_allocation LIMIT 1"; // expecting only one record
			$resTimeSlotInfo   	= parent::execQuery($sqlTimeSlotInfo,$this->conn_local);
			if($resTimeSlotInfo && parent::numRows($resTimeSlotInfo) > 0){
				$row_time_slot 	= parent::fetchData($resTimeSlotInfo);
				$userAddInfo['time_slot'] = trim($row_time_slot['alloc_time_slot']);
			}
			
			$sqlEmpRowId 		= "SELECT rowId FROM mktgEmpMap WHERE mktEmpCode = '".$this->empcode."'";
			$resEmpRowId       	= parent::execQuery($sqlEmpRowId,$this->conn_local);
			if($resEmpRowId && parent::numRows($resEmpRowId) > 0){
				$row_emp_rowid 	= parent::fetchData($resEmpRowId);
				$userAddInfo['empRowId']  = $row_emp_rowid['rowId'];
			}
			
			
			$sqlUserAddInfo	    =  "SELECT extn,secondary_allocID,tmeClass,level,state FROM mktgEmpMaster WHERE mktempcode = '".$this->empcode."'";
			$resUserAddInfo	=   parent::execQuery($sqlUserAddInfo, $this->conn_local);
			if($resUserAddInfo && parent::numRows($resUserAddInfo) > 0){
				$row_add_info									=	parent::fetchData($res_query);
				$userAddInfo['mktgEmp']['extn']					=	$row_add_info['extn'];
				$userAddInfo['mktgEmp']['secondary_allocID']	=	$row_add_info['secondary_allocID'];
				$userAddInfo['mktgEmp']['tmeClass']				=	$row_add_info['tmeClass'];
				$userAddInfo['mktgEmp']['level']				=	$row_add_info['level'];
				$userAddInfo['mktgEmp']['state']				=	$row_add_info['state'];
			}else{          
				$userAddInfo['mktgEmp']['extn']					=	'';
				$userAddInfo['mktgEmp']['secondary_allocID']	=	'';
				$userAddInfo['mktgEmp']['tmeClass']				=	'';
				$userAddInfo['mktgEmp']['level']				=	'';
				$userAddInfo['mktgEmp']['state']				=	'';
			}
			return $userAddInfo;
	}
	
	private function getServerInfo(){
		
		$serverData = array();
		
		$serverData['qq'] 					= false;
		$serverData['IDC'] 					= IDC_SERVER_IP;
		$serverData['internetip'] 			= IDC_SERVER_IP;
		$serverData['dual_flag'] 			= 0;
		$serverData['module'] 				= 'TME';
		$serverData['AlertPopup'] 			= 'NO';
		$serverData['rest_flag'] 			= 1;
		$serverData['city_db'] 				= "online_regis_".$this->city_db;
		$serverData['s_dual']				= DUAL_SERVER_IP;	
		$serverData['s_main_ip']			= constant(strtoupper($this->conn_city).'_S_MAIN_IP');
		$serverData['s_slave_ip']			= constant(strtoupper($this->conn_city).'_S_SLAVE_IP');
		$serverData['finance_ip']			= constant(strtoupper($this->conn_city).'_FINANCE_IP');
		$serverData['finance_slave_ip']		= constant(strtoupper($this->conn_city).'_FINANCE_SLAVE_IP');
		$serverData['budget_ip']			= constant(strtoupper($this->conn_city).'_BUDGET_IP');
		$serverData['datacrr_ip'] 			= constant(strtoupper($this->conn_city).'_DATACRR_IP');
		$serverData['datacrr_slave_ip'] 	= constant(strtoupper($this->conn_city).'_DATACRR_SLAVE_IP');
		$serverData['messaging_ip']	 		= constant(strtoupper($this->conn_city).'_MESSAGING_IP');
		return $serverData;
	}
	
	private function cityInfo (){
		$cityData 				=	array();
		if($this->conn_city != 'remote'){
			$sqlCityInfo 	= "SELECT country_id,country_name,state_id,state_name,city_id,ct_name FROM city_master where ct_name='".$this->data_city."'";
			$resCityInfo    = parent::execQuery($sqlCityInfo,$this->conn_local);
			if($resCityInfo && parent::numRows($resCityInfo) > 0){
				$row_city       = parent::fetchData($resCityInfo);
				
				$cityData['s_deptCountry_id'] 	= trim($row_city['country_id']);
				$cityData['s_deptCountry'] 		= trim($row_city['country_name']);
				$cityData['s_deptState_id'] 	= trim($row_city['state_id']);
				$cityData['s_deptState'] 		= trim($row_city['state_name']);
				$cityData['s_deptCity_id'] 		= trim($row_city['city_id']);
				$cityData['s_deptCity'] 		= trim($row_city['ct_name']);
				
			}else{
				$cityData['s_deptCity'] 			= $this->data_city;
			}
		}
		
		return $cityData;
	}
	public static function curlCall($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";
        # Init Curl Call #
        $ch = curl_init();
        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				if(isset($param['auth_token']) && $param['auth_token']!= ''){
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']),
						'HR-API-AUTH-TOKEN:'.$param['auth_token'])); 
				}else{
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']))); 
				}
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			}
		}
        $retVal = curl_exec($ch);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }
	//Curl Call func Ends
	
	private function sendResponseMsg($message,$errcode){
		$resp_msg_arr['error']['code'] 	= $errcode;
		$resp_msg_arr['error']['msg'] 	= $message;
		return $resp_msg_arr;
	}
}

?>
