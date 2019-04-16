<?php
class locationinfoApiClass extends DB
{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	var  $data_city		= null;
	var  $module		= null;
	function __construct($params){
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$action 		= trim($params['action']);
		$empcode 		= trim($params['empcode']);
		$parentid 		= trim($params['parentid']);
		$reporting_head_code 	= trim($params['reporting_head_code']);
		$reporting_head_name 	= trim($params['reporting_head_name']);
		$empname 				= trim($params['empname']);
		$city 					= trim($params['city']);
		$off_sales 				= trim($params['off_sales']);
		$mobile_num 			= trim($params['mobile_num']);
		$otp 					= trim($params['otp']);
		$confirmed 				= trim($params['confirmed']);
		$status 				= trim($params['status']);
		$city_type 				= trim($params['city_type']);
		$teamname 				= trim($params['teamname']);
		$empcode 				= trim($params['empcode']);
		$managercode 			= trim($params['managercode']);
		$this->mongo_flag 		= 0;
		$this->mongo_tme 		= 0;
		$this->limitVal			= 50;
		$this->mongo_obj 		= new MongoClass();
		$this->data_city 		= $data_city;
		$this->params 			= $params;
		$this->module  	  		= $module;
		$this->ucode			= $empcode;
		if(isset($this->params['srchparam']) || isset($this->params['srchwhich']) || (isset($this->params['srchData']))){
			$this->params['srchparam'] = urldecode($this->params['srchparam']);
			$this->params['srchwhich'] = urldecode($this->params['srchwhich']);
			$this->params['srchData']  = urldecode($this->params['srchData']);
		}
		if(($this->params['parentid']=='' || $this->params['parentid']==null) && ($this->params['action']=='RetentionData_info' || $this->params['action']=='EditListingData')){
			$message = "Please pass parentid.";
			echo json_encode($this->send_die_message($message));
			die();
		}else{
			$this->parentid = $parentid;
		}		
		if($this->params['extraVals']=='' && $this->params['action']=='ReportData'){
			$message = "Please pass extraVals.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->setServers();
		if(trim($data_city)==''){
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)==''){
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($empcode)==''){  //&& $this->params['action']	!=	'pincodemaster' && $this->params['action']	!=	'pincodemasterdialer'
			$message = "Empcode is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($action)==''){
			$message = "Please pass valid action!!.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if($this->params['action']	==	"accetRejectRequest"){
			if($this->params['reporting_head_code']	==	""){
				$message = "Reporting Head Code is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
			if($this->params['reportee']	==	""){
				$message = "Reportee Code is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		if($this->params['reporting_head_code']	==	"" && $this->params['action']	==	"insertlineage"){
			$message = "Reporting Head Code is null!!.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if($this->params['action']	==	"sendOTP"){
			if($this->params['managercode']	==	""){
				$message = "Manager Code is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
			if($this->params['otp']	==	""){
				$message = "OTP is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		if($this->params['action']	==	"checkOTP"){
			if($this->params['managercode']	==	""){
				$message = "Manager Code is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		if($this->params['action']	==	'getcitylist'){
			if($this->params['srchData']	==	""){
				$message = "Search text is Blank!!.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		
		function insert_return($con) {
			if ($con) {
			    $errorMsg = "data inserted successfully";
			    $errorCode = "0";
			    $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
			    $response = array("results" => $resArr);
			} else {
			    $errorMsg = "data insertion fail";
			    $errorCode = "1";
			    $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
			    $response = array("results" => $resArr);
			}
			return $response;
		    }
	}
	
	
	
	function setServers(){
		global $db;
		if(DEBUG_MODE){
			echo '<pre>db array :: ';
			print_r($db);
		}
		$data_city 				= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote'); 
		$this->conn_iro    		= $db[$data_city]['iro']['master'];
		$this->conn_local  		= $db[$data_city]['d_jds']['master'];
		$this->conn_local_slave = $db[$data_city]['d_jds']['slave'];
		$this->conn_tme 		= $db[$data_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$data_city]['idc']['master'];
		$this->conn_fin   		= $db[$data_city]['fin']['master'];
		$this->conn_fin_slave   = $db[$data_city]['fin']['slave'];
		$this->conn_message		= $db[$data_city]['messaging']['master'];
		$this->remote			= $data_city;
		if(strtoupper($this->module) == 'ME'){
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		if(strtoupper($this->module) == 'TME'){
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($this->data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
		}
		switch(strtolower($this->module)){
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance 	= $db[$data_city]['fin']['slave'];
			$this->conn_LOCAL   	= $db[$data_city]['iro']['master'];
			break;
			case 'tme':
			$this->conn_temp 			= $db[$data_city]['tme_jds']['master'];
			$this->conn_main 			= $db[$data_city]['idc']['master'];
			$this->conn_finance_temp 	= $this->conn_temp;
			$this->conn_finance 	= $db[$data_city]['fin']['slave'];
			$this->conn_LOCAL   		= $db[$data_city]['d_jds']['master'];
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_LOCAL   	= $db[$data_city]['idc']['master'];
			$this->conn_finance 	= $db[$data_city]['fin']['slave'];
			break;
			case 'jda':			
			break;
			default:
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
			break;
		}
		$configclassobj						= new configclass();
		$this->urldetails					=	$configclassobj->get_url(urldecode($this->data_city));
		$this->jdbox_ip_url					=	$this->urldetails['jdbox_url'];
		$this->city_indicator				=	$this->urldetails['city_indicator'];
		$this->hrmodule						=	$this->urldetails['HRMODULE'];
		$this->KNOWLEDGE_APICALL			=	$this->urldetails['KNOWLEDGE_APICALL'];
	}
	
	public function EcsRequestStatusCheck(){
		$result						=	array();
		$status 					= 	'';
		$mngr_data 					= 	'';
		$fetchEcsStatus 			= 	"SELECT * FROM tbl_ecs_dealclose_pending WHERE parentid = '".$this->params['parentid']."' AND EmpCode = '".$this->params['empcode']."'";
		$fetchEcsStatusRes			=	parent::execQuery($fetchEcsStatus, $this->conn_tme);
		$fetchEcsStatus_NumRows		=	parent::numRows($fetchEcsStatusRes);
		$fetchEcsStatus_FetchData	=	parent::fetchData($fetchEcsStatusRes);
		$entry_start 				= 	$fetchEcsStatus_FetchData['updated_on'];
		if($fetchEcsStatus_NumRows > 0){
			if($entry_start != '' && $entry_start != null && $entry_start != '0000-00-00 00:00:00' ){
					 $seconds  		= 	strtotime(date('Y-m-d H:i:s')) - strtotime($entry_start);
					 $hours 		= 	floor($seconds / 3600);
					 $datetime2 	= 	new DateTime($entry_start);
						if($hours > 72){
							$result['errorCode']	=	5;
							$result['errorStatus']	=	'Data Not Found';
						}else{
							$result['Acc_Reg_Flag'] =   $fetchEcsStatus_FetchData['Acc_Reg_Flag'];
							$result['mngr_data']    =   $fetchEcsStatus_FetchData['upd_deg_mngr'];
							$result['errorCode']	=	$fetchEcsStatus_FetchData['Mngr_Flag'];
							$result['errorStatus']	=	'Data Found';
						}
			}else{
					$result['Acc_Reg_Flag'] =   $fetchEcsStatus_FetchData['Acc_Reg_Flag'];
					$result['mngr_data']    =   $fetchEcsStatus_FetchData['upd_deg_mngr'];
					$result['errorCode']	=	$fetchEcsStatus_FetchData['Mngr_Flag'];
					$result['errorStatus']	=	'Data Found';
			}
		}else{
			$result['errorCode']	=	5;
			$result['errorStatus']	=	'Data Not Found';
		}
		return $result;
	}
	
	public function pincodemaster(){
		$retArr				=	array();
		if($this->params['pin_auto']	==''){
			$condition		=	'';
			$limitFlag		=	'';
		}else{
			$condition 		=	"pincode LIKE '".addslashes($this->params['pin_auto'])."%' AND";
			$limitFlag		=	'LIMIT 10';
		}
		if ($this->params['area'] == ''){
			$area_code 		=	"";
			if($this->params['city'] != ''){
				$city_code 	= 	$this->params['city'];
			}else{
				$city_code	=	"";
			}
		}else{
			$area_code 	= 	$this->params['area'];
			$city_code 	= 	$this->params['city'];
		}
		if($city_code=="" || empty($city_code)){
			$retArr['errorCode'] 		=	"1";
			$retArr['errorStatus'] 		=	"City not entered";
			return json_encode($retArr);
		}
		if($this->remote == 'remote'){
			$city_columns_name 	= "city";
			$city_code_new		=	str_replace(",","','",stripslashes($city_code));
		}else{
			$city_columns_name 	= "data_city";
			$city_code_new		= $city_code;
		}
		$pin_option = '';
		if($area_code	== 	null || $area_code 	== 	''){
			$sel_pin_list 	= "SELECT DISTINCT pincode FROM  tbl_areamaster_consolidated_v3 WHERE ".$condition." " . $city_columns_name . " IN ('" . (stripslashes(strtoupper($city_code_new))) . "') and display_flag =1 AND type_flag=1 AND pincode IS NOT NULL ORDER BY pincode ".$limitFlag." ";
		}
		else{
			$sel_pin_list 	= "SELECT DISTINCT pincode FROM  tbl_areamaster_consolidated_v3 WHERE ".$condition." " . $city_columns_name . " IN ('" . (stripslashes(strtoupper($city_code_new))) . "') and areaname='".$area_code."' and display_flag =1 AND type_flag=1 AND pincode IS NOT NULL ORDER BY pincode ".$limitFlag." ";
		}
		$con	=	parent::execQuery($sel_pin_list, $this->conn_local);
		$num	=	parent::numRows($con);		
		if($num>1){
			while ($res_pin_list = parent::fetchData($con)){
				$pin_option 		= 	$retArr['data'][]	=	$res_pin_list['pincode'];
			}
			$retArr['pincode_count']	=	$num;
			$retArr['errorCode'] 		=	"0";
			$retArr['errorStatus'] 		=	"Data found";
		}else if($num==1){
			$res_pin_list 				= 	parent::fetchData($con);
			$pin_option 				= 	$retArr['data'][]	=	$res_pin_list['pincode'];
			$retArr['pincode_count']	=	$num;
			$retArr['errorCode'] 		=	"0";
			$retArr['errorStatus'] 		=	"Data found";
		}else{
			$retArr['errorCode'] 		=	"1";
			$retArr['errorStatus'] 		=	"Data not found";
		}
		return $retArr;
	}
	
	public function pincode_master_dialer(){ // in use
		$retArr 		=	array();
		if($this->params['pin_auto']==''){
			$condition	=	'';
			$limitFlag	=	'';
		}else{
			$condition 	=	"pincode LIKE '".addslashes($this->params['pin_auto'])."%' AND";
			$limitFlag	=	'LIMIT 10';
		}
		$pin_option = '';
		$sel_pin_list 	= "SELECT DISTINCT pincode FROM  tbl_areamaster_consolidated_v3 WHERE ".$condition." display_flag =1 AND type_flag=1 AND pincode IS NOT NULL ORDER BY pincode ".$limitFlag." ";
		$con	=	parent::execQuery($sel_pin_list, $this->conn_local);
		$num	=	parent::numRows($con);		
		if($num>1){
			while ($res_pin_list = parent::fetchData($con)){
				$pin_option 		= 	$retArr['data'][]	=	$res_pin_list['pincode'];
			}
			$retArr['pincode_count']	=	$num;
			$retArr['errorCode'] 		=	"0";
			$retArr['errorStatus'] 		=	"Data found";
		}else if($num==1){
			$res_pin_list 	= 	$dbObjLocal->fetchData($con);
			$pin_option 	= 	$retArr['data'][]	=	$res_pin_list['pincode'];
			$retArr['pincode_count']	=	$num;
			$retArr['errorCode'] 	=	"0";
			$retArr['errorStatus'] 	=	"Data found";
		}else{
			$retArr['errorCode'] 	=	"1";
			$retArr['errorStatus'] 	=	"Data not found";
		}
		return $retArr;
	}
	
	public function get_area(){ // in use
		$retArr_area		=	array();
		if(empty($this->params['srch_string'])){
			$sel_state_list	=	"SELECT areaname as mainarea,area_id FROM `tbl_areamaster_consolidated_v3` WHERE  data_city='".$this->params['city_name']."'and type_flag=1 and display_flag=1 GROUP BY areaname ORDER BY areaname";
		}else{
			$sel_state_list	=	"SELECT areaname as mainarea,area_id FROM `tbl_areamaster_consolidated_v3` WHERE data_city='".$this->params['city_name']."'  and areaname like '%".$this->params['srch_string']."%' and type_flag=1 and display_flag=1 GROUP BY areaname ORDER BY areaname";		
		}
		$con		=	parent::execQuery($sel_state_list, $this->conn_local);
		$num		=	parent::numRows($con);
		if($num>0){
			while($data	=	parent::fetchData($con)) {
				$retArr_area['data']['area_list'][]		=	$data;
			}
			$retArr_area['errorCode'] 		=	0;
			$retArr_area['errorStatus'] 		=	"Data found";
		}else{
			$retArr_area['errorCode'] 		=	1;
			$retArr_area['errorStatus'] 		=	"Data Not found";
		}
		return $retArr_area;
	}
	
	//Curl Call func Starts
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
	
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}

?>
