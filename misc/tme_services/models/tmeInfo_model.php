<?php

	
//namespace etc\tmemodel;

include_once("../library/class.logs.php");

class TmeInfo_Model extends Model {
	private $limitVal	=	50;
	public $call_type_arr = Array("I" => "Inbound Call", "M" => "Manual Call", "O" => "Dialer Call", "C" => "Callback Call");
	public function __construct() {
		parent::__construct();
		$this->logsObj = new Logs();
		$this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
	}
	
	public function call_cticlicktocall(){
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		
		$station_id = $paramsGET['station_id'];
		$login_city = $paramsGET['login_city'];

		$resArr 				= 	array();
		$params 				= 	array();
		$dbObjLocal				=	new DB($this->db['db_local']);
		$city 					= 	strtolower($login_city);
		if($parseConf['servicefinder']['remotecity'] == 1){
			$filename = $_SERVER['DOCUMENT_ROOT'].'common/ip'.$city.'.txt';
		}else{
			$filename = $_SERVER['DOCUMENT_ROOT'].'common/ip.txt';
		}
		if(file_exists($filename)){
			$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line_num => $line) {
				if (!empty($line));
				{
					$working_server = $line;
					$WHICH_VENDOR = "TECHINFO";
					break;
				}
			}
			if(empty($working_server))
				echo "<h1>Sorry could not connect to techinfo<h2>";	
		}
		$resArr['techinfoDisp'] = 'http://'.$working_server.':9090/IDG?cmd=AGENTCLOSECALL&%PARAM1%&&&&%PARAM2%&';
		$resArr['techinfoUrl'] = 'http://'.$working_server.':9090/IDG?cmd=AGENTMAKECALL&1000&%PARAM1%&'.$paramsGET['parentid'].'&&%PARAM2%&';
		if($working_server == '172.29.26.145' && ($station_id > '6601' && $station_id < '6647'))
			$resArr['techinfoUrl'] = 'http://'.$working_server.':9090/IDG?cmd=AGENTMAKECALL&1001&%PARAM1%&'.$paramsGET['parentid'].'&&%PARAM2%&';
		if($working_server == '172.29.32.143' && ($station_id > '6001' && $station_id < '6035'))
			$resArr['techinfoUrl'] = 'http://'.$working_server.':9090/IDG?cmd=AGENTMAKECALL&1001&%PARAM1%&'.$paramsGET['parentid'].'&&%PARAM2%&';
		$params['url'] 			= 	HRMODULE . '/employee/fetch_employee_info/' . $paramsGET['empcode'];
		$params['formate'] 		= 	'basic';
		$content_emp 			= 	Utility::curlCall($params);
		$retArr['hrInfo']   	=   json_decode($content_emp,true);
		$resArr['number']		=	$retArr['hrInfo']['data']['mobile_num'];
		if($parseConf['servicefinder']['remotecity'] == 1){
			$resArr['number'] 	= '0'.$resArr['number'];
		}else{
			$resArr['number'] 	= $resArr['number'];
		}
		
		if((isset($paramsGET['tme_central'])) && ($paramsGET['tme_central'] == 1)){
			$resArr['ctinum']		=	$paramsGET['remote_add'];
		}else{
			$resArr['ctinum']		=	$_SERVER['REMOTE_ADDR'];
		}
		
		$resArr['dnc_flag'] 	= 	5;
		$resArr['aspect_color']	=	'green';
		$resArr['hotdata']		=	'';
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['empcode'] 				= 	$paramsGET['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getmktgEmpMaster";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		if($result['errorCode']	==	0){
			$rowallocid 		= 	$result['data'];
			$resArr['allocid']	=	$rowallocid['allocid'];
			$resArr['dnc_type']	=	$rowallocid['dnc_type'];
		}else{
			$resArr['allocid']	=	'';
			$resArr['dnc_type']	=	'';
		}
		return json_encode($resArr);
	}
	
	public function fetchReportsInfoTimeline($tmecode,$data_city,$empcode,$fullData='',$dataValExp='') {
		$retArr     =       array();
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['empcode'] 				= 	$tmecode;
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"ReportsInfoTimeline";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function fetchDealCloseDataReport($tmecode='',$fullData=''){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$data_city 	= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		$curlParams['url']  		= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php?action=DealCloseDataReport&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$data_city.'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1';		
		$curlParams['formate']      = 'basic';	
		$curlParams['method']       = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 
	}
	
	public function getRowId($empCode) {
		$dbObjLocal	=	new DB($this->db['db_local']);
		$params	=	array_merge($_POST,$_GET);
		$res	=	array();
		
		$query	=	"SELECT rowId,mktEmpCode,mktEmpType FROM d_jds.mktgEmpMap WHERE mktEmpCode = '".$empCode."'";
		$con	=	$dbObjLocal->query($query);
		if(isset($params['trace']) && $params['trace'] == 1) {
			$dbObjLocal->trace($query);
		}
		$num	=	$dbObjLocal->numRows($con);
		if($num > 0) {
			$res['data']	=	$dbObjLocal->fetchData($con);
			$res['errorCode']	=	0;
			$res['errorStatus']	=	'Row Id Found';
		} else {
			$res['errorCode']	=	1;
			$res['errorStatus']	=	'No Rowid Found';
		}
		$res['count']	=	$num;
		return json_encode($res);
	}
	
	public function fetchCategoryData($tmeCode='') {
		header('Content-Type: application/json');
		$dbObjLocal	=	new DB($this->db['db_local_slave']);
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsGET	=	array_merge($_POST,$_GET);
		$paramArr	=	array();
		$paramArr['catFlag']	=	1;
		$paramArr['catParam']	=	$params['parid'];
		$paramArr['data_city']	=	$params['data_city'];
		
		$curlParams = array();
		$curlParams['url'] = SERVICE_IP.'/categoryInfo/getCategoryInfo';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramArr); 
		$categoryInfo	=	Utility::curlCall($curlParams);
		$categoryInfoDec		=	json_decode($categoryInfo,true);  
		$tmeRowId	=	json_decode($this->getRowId($tmeCode),true);
		if($categoryInfoDec['errorCode']	==	0) {
			if(isset($params['pageShow'])) {
				$pageVal	=	$this->limitVal*$params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			$retArr	=	array();
			$query	=	"SELECT a.compname, a.paidstatus, a.freez, a.mask, a.expired, a.parentId as contractid, a.contact_details, a.contractOrig, a.callcnt, a.parent_group , a.flgduplicate as diwaliflag FROM tbl_tmesearch a JOIN db_iro.tbl_companymaster_search b ON a.parentid = b.parentid WHERE MATCH(b.catidlineage_search) AGAINST ('".$categoryInfoDec['data']['catid']."') AND a.tmeCode = '".$tmeRowId['data']['rowId']."'".$limitFlag;
			$con	=	$dbObjLocal->query($query);
			$numRows	=	$dbObjLocal->numRows($con);
			if($numRows > 0) {
				while($resData	=	$dbObjLocal->fetchData($con)) {
					$retArr['data'][]	=	$resData;
				}
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		} else {
			$retArr['errorCode']	=	2;
			$retArr['errorStatus']	=	'Category Not Found';
		}
		return json_encode($retArr);
	}
	
	public function FetchEcsDetailsForm(){ // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"FetchEcsDetailsForm";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	
	
	public function SendRemainderEcsLead(){ // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['companyname'] 			= 	$params['companyname'];
		$postArray['action_flag'] 			= 	$params['action_flag'];
		$postArray['ip'] 					= 	$_SERVER['SERVER_ADDR'];
		$postArray['post_data']				= 	"1";	 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"SendRemainderEcsLead";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function getSSOEmp(){
		header('Content-Type: application/json');
		$params			=	array_merge($_GET,$_POST);
		$paramsGET		=	json_decode(file_get_contents('php://input'),true);
		$conn_local		=	new DB($this->db['db_local']);
		$resArr 		= array();
		$params = array();
		$params['url'] = HRMODULE . '/employee/employee_xhr/10/1?term='.urlencode($paramsGET['srchData']);
		$params['formate'] = 'basic';
		$content_emp = Utility::curlCall($params);
		$data	=	json_decode($content_emp,true); 
		$i	=0;
		if(sizeof($data) > 0) {
			foreach($data as $dat) {

						$resArr['data'][$i]['empname'] = $dat['empname'];
						$resArr['data'][$i]['empcode'] = $dat['empcode'];
						$resArr['data'][$i]['city']    = $dat['city'];
						$i++;
			}
		$resArr['errorCode']	=	'0';
		$resArr['errorStatus']	=	'Children Data Found';
		}else{
			$resArr['errorCode']	=	'1';
		}
		return json_encode($resArr);
	}
	
	public function getHistory($empCode='',$fullData='') {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['lead'] 					= 	$params['lead'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getHistory";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	Utility::curlCall($dataParam);
		return $result;
	}
	
	
	public function checkupdate() {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"checkupdate";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function fetchmelist() {
	   $curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['term'] 					= 	$params['term'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"fetchmelist";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function insertmename() {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['tmecode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['final_mecode'] 			= 	$params['final_mecode'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"insertmename";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function reactivaterequest($empCode='',$fullData='') {
			$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['tmecode'];
		$postArray['empname'] 				= 	$params['tmename'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"reactivaterequest";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		if($result['errorCode'] == 0)
			echo 0;
		else
			echo 1;
	}
	
	public function updatestopflag($tmecode,$fullData) {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['companyname'] 			= 	$params['companyname'];
		$postArray['empcode'] 				= 	$params['userid'];
		$postArray['tmename'] 				= 	$params['tmename'];
		$postArray['stop_flag'] 			= 	$params['stop_flag'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"updatestopflag";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		if($result['errorCode']	==	0)
			echo 0;
		else
			echo 1;
	}
	
	public function delProspectData() {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['parentid'] 				= 	$params['parid'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empId'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"delProspectData";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function fetchChildInfo() { //no change
		header('Content-Type: application/json');
		$paramsGET		=	json_decode(file_get_contents('php://input'),true);
		$data_city		=	$paramsGET['data_city'];
		$retArr			= 	array();
		$params 		= 	array();
		$params['url'] 	= 	HRMODULE . '/lineage/lineage_xhr/?empcode='.$paramsGET['empId'];
		$params['formate'] = 'basic';
		$content_emp = Utility::curlCall($params);
		$data			=	json_decode($content_emp,true);
		if(isset($data['children'])) {
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Children Data Found';
			$retArr['data']		=	$data['children'];
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Children Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function showTimelineData($tmeCode) {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$retArr					= 	array();
		$data_city				=	$params['data_city'];
		$empcode				=	$params['empId'];
		$dispositionDataAlloc	=	json_decode($this->fetchReportsInfoTimeline($params['empcode'],$params['data_city'],$params['empId'],'',''),1);
		$dealClosedData			=	json_decode($this->fetchDealCloseDataReport($tmeCode,''),1);
		if($dispositionDataAlloc['errorCode']	==	1 && $dealClosedData['errorCode']	==	1) {
			$retArr['errorCode']				=	1;
			$retArr['errorStatus']				=	'Data Not Found for Timeline';
		} else {
			$retArr['errorCode']				=	0;
			$retArr['errorStatus']				=	'Data Found for Timeline';
			$retArr['data']['appointmentAlloc']	=	$dispositionDataAlloc;
			$retArr['data']['dealClosed']		=	$dealClosedData;
		}
		return json_encode($retArr);
	}
	
	public function getDataCountTME() {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['tmecode'];
		$postArray['decisionParam'] 		= 	$params['decisionParam'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"getDataCountTME";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function send_mngr_request($empcode,$parentid,$eventParam) {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['eventParam'] 			= 	$params['eventParam'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"sendmngrrequest";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		$sendLogs 							= 	$this->logsObj->sendLogs($result['dataLog']);
		return json_encode($result);
	}
	


	public function checkvccontract($parentid) {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"checkvccontract";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}


	public function checkvccondition($parentid) {
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"checkvccondition";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}

	public function allocateappt() {
		$retArr	=	array();
		$retArr['errorCode']	=	0;
		$retArr['errorStatus']	=	'NOT IN USE';
		return $retArr;
	}

	public function getPenaltyDetails(){ 
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empCode'];
		$postArray['page'] 					= 	$params['page'];
		$postArray['year'] 					= 	$params['year'];
		$postArray['city'] 					= 	$params['city'];
		$postArray['date'] 					= 	$params['date'];
		$postArray['empdata'] 				= 	$params['empdata'];
		$postArray['actionInfo'] 			= 	$params['action'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"getPenaltyInfo";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function getDoDontDetails(){ 
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empCode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"getDoDontDetails";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function checkAudioFileHeader($dataArr) {
        $file = $dataArr['audioPath'];
        $file_headers = @get_headers($file);
        if ($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            //$exists = false;
            $retVal['error']['code'] = 1;
            $retVal['error']['msg'] = 'File not found';                        
            $retVal['result']['audio_file'] = $file;
        } else {
            //$exists = true;
            $retVal['error']['code'] = 0;
            $retVal['error']['msg'] = 'success';
            $retVal['result']['audio_file'] = $file;
        }
        return $retVal;
    }
    
	public function getTMECallLogs($data){
		
		header('Content-Type: application/json');
		
		$dataArr = json_decode(file_get_contents('php://input'), true);
		
		$from_dt = 	$dataArr['from_date'];
		$to_dt = 	$dataArr['to_date'];
        $url = DIALER_DASH_API."dialerDashboard/dialerDashboardServices/getEmployeeWisedata?employee_id=".urlencode($dataArr['emp_id'])."&from_date=".urlencode($dataArr['from_date'])."&to_date=".urlencode($dataArr['to_date']);
        $dataParam = array("url" => $url, "method" => 'get');
        $result = utility::curlCall($dataParam);
        $result['api'] =   $url;
        if(!empty($result['result'])) {
			foreach ($result['result'] as $key => $value) {
				$endtime_Data =   $value['disp_call_end_time'];
				$endtime_Data_res = date("dMY",strtotime($endtime_Data));
				$endtime_Data_dt_jaipur = trim($value['service_id']).date("/Y/M",strtotime($endtime_Data));
        foreach ($this->call_type_arr as $k1 => $v1) {
            if ($value['call_type'] == $k1) {
                 $result['result'][$key]['call_type_name'] = $v1;
             }
           }
               if(!empty($value['voice_file_name'])) {
                    switch (strtolower($value['data_city'])) {
                        case 'mumbai':
                            
                            $fileData = array();
                            $fileData['file'] = $value['voice_file_name'];
                            $fileData['city'] = "mumbai";
                            $fileData['end_file_data'] = $endtime_Data_res;

                            $audio_file_data = $this->checkAudioFileData($fileData);

                            //$serverAddressDialer    = 'http://172.29.66.181/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];
                            $serverAddressDialer = $audio_file_data['result']['file'];
                            unset($fileData,$audio_file_data);
                            break;
							case 'delhi':
                            $serverAddressDialer    = 'http://172.29.8.88/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];

                            break;
                            case 'kolkata':
                            $serverAddressDialer    = 'http://172.29.16.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];

                            break;
							case 'bangalore':
                            $serverAddressDialer    = 'http://172.29.26.148/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];

                            break;
							case 'chennai':
                            $serverAddressDialer    = 'http://172.29.32.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];

                            break;
                            case 'pune':
                            
                            $fileData = array();
                            $fileData['file'] = $value['voice_file_name'];
                            $fileData['city'] = "pune";
                            $fileData['end_file_data'] = $endtime_Data_res;

                            $audio_file_data = $this->checkAudioFileData($fileData);

                            //$serverAddressDialer    = 'http://172.29.88.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];
                            $serverAddressDialer    = $audio_file_data['result']['file'];
                            unset($fileData,$audio_file_data);
                            break;
                            case 'hyderabad':
                            $serverAddressDialer    = 'http://172.29.50.142/Recordings/'.$value['service_id']."/".date("Y",strtotime($value['disp_call_start_time']))."/".date("M",strtotime($value['disp_call_start_time']))."/".$endtime_Data_res."/".$value['voice_file_name'];

                            break;
							case 'ahmedabad':
                            $serverAddressDialer    = 'http://172.29.56.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];

                            break;
							case 'jaipur':
                            $serverAddressDialer    = 'http://172.29.101.142/Recordings/'.$endtime_Data_dt_jaipur."/".$endtime_Data_res."/".$value['voice_file_name'];
                    
							break;
							case 'chandigarh':
                            $serverAddressDialer    = 'http://172.29.74.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];
                    
							break;
							case 'coimbatore':
                            $serverAddressDialer    = 'http://172.29.72.142/Recordings/'.$endtime_Data_res."/".$value['voice_file_name'];
							break;
                    }

                    if(isset($value['app_call']) &&  ($value['app_call']) == 1){
                        $serverAddressDialer = $value['voice_file_name'];
                    }
                    
                    $result['result'][$key]['call_recording_path']  = $serverAddressDialer;
                }
                else {
                    $result['result'][$key]['call_recording_path']  = "";
                }
		}
		
	}

        return json_encode($result);
		
	}
        
        public function checkAudioFileData($dataArr) {
        
            $retVal = array();

            if($dataArr['city'] == "mumbai") {

                $data_city_ip_arr = array("172.29.66.181","172.29.64.140");
            }
            if($dataArr['city'] == "pune") {

                $data_city_ip_arr = array("172.29.88.142","172.29.88.232");
            }


            foreach ($data_city_ip_arr as $ip_value) {

                if (strpos($dataArr['file'], ".wav") !== false) {

                    $audio_path_arr = array();
                    $audio_path_arr['audioPath'] = "http://" . $ip_value . "/Recordings/" . $dataArr['end_file_data'] . "/" . $dataArr['file'];
                    $file_res = $this->checkAudioFileHeader($audio_path_arr);

                    if ($file_res['error']['code'] == 0) {

                        $serverAddressDialer = $audio_path_arr['audioPath'];
                        unset($audio_path_arr);
                        break;
                    } else {

                        $file = str_replace('.wav', '.mp3', $dataArr['file']);

                        $audio_path_arr1 = array();
                        $audio_path_arr1['audioPath'] = "http://" . $ip_value . "/Recordings/" . $dataArr['end_file_data'] . "/" . $file;
                        $file_res1 = $this->checkAudioFileHeader($audio_path_arr1);

                        if ($file_res1['error']['code'] == 0) {

                            $serverAddressDialer = $audio_path_arr1['audioPath'];
                            unset($audio_path_arr1);
                            break;
                        } else {

                            continue;
                        }
                    }
                } 
                else {

                    $audio_path_arr2 = array();
                    $audio_path_arr2['audioPath'] = "http://" . $ip_value . "/Recordings/" . $dataArr['end_file_data'] . "/" . $dataArr['file'];
                    $file_res2 = $this->checkAudioFileHeader($audio_path_arr2);

                    if ($file_res2['error']['code'] == 0) {

                        $serverAddressDialer = $audio_path_arr2['audioPath'];
                        unset($audio_path_arr2);
                        break;
                    } else {

                        continue;
                    }
                }
            }
            if(isset($serverAddressDialer)) {

                $audio_file = $serverAddressDialer;
            }
            else {
                $audio_file = "";
            }

            $retVal['error']['code']    = 0;
            $retVal['error']['msg']     = "success";
            $retVal['result']['file']   = $audio_file;

            return $retVal;
        }


	public function fetchEditListingData(){
		//EditListingData
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"EditListingData";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function fetchEditListingEntry(){
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"fetchEditListingEntry";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}   
	
    public function getCollectData() {

        header('Content-Type: application/json');

        $dataArr = json_decode(file_get_contents('php://input'), true);
        $retVal = array();
        $employee_id  = (isset($dataArr['employee_id']) && trim($dataArr['employee_id']) != '') ? $dataArr['employee_id'] : '';
        if($dataArr['date_type'] == 1){
			
			$current_end_date	=	date("Y-m-d",strtotime("-1 days"));
			$current_start_date	=	date("Y-m-d",strtotime("-29 days",strtotime($current_end_date)));
			
			$prev_end_date	=	 date("Y-m-d",strtotime("-1 days",strtotime($current_start_date)));
			$prev_start_date	=	date("Y-m-d",strtotime("-29 days",strtotime($prev_end_date)));
		}
		else if($dataArr['date_type'] == 2){
			
			$current_end_date	=	date('Y-m-d', strtotime('last day of last month'));
			$current_start_date	=	date('Y-m-d', strtotime('first day of last month'));
			
			$prev_end_date	=	date('Y-m-d', strtotime('last day of this month 2 months ago'));
			$prev_start_date	=	date('Y-m-d', strtotime('first day of this month 2 months ago'));
			
		}
		else if($dataArr['date_type'] == 3){
						
			$current_start_date	=	date("Y-01-01", strtotime("-1 year"));
			$current_end_date	=	date("Y-12-t", strtotime($current_start_date));;
						
			$prev_start_date	=	date("Y-01-01", strtotime("-1 year",strtotime($current_start_date)));
			$prev_end_date		=	date("Y-12-t", strtotime($prev_start_date));;
			
		}
                else if($dataArr['date_type'] == 4){
						
			$current_start_date	=	date("Y-m-d", strtotime("first day of this month"));
                        
                        if(date("d",$current_start_date) == "01"){
                           $current_end_date = $current_start_date;
                            
                        }
                        else{
                            $current_end_date	=	date("Y-m-d", strtotime("-1 days"));
                        }
			
			$prev_start_date	=	date("Y-m-d", strtotime("first day of last month"));
			$prev_end_date		=	date("Y-m-d", strtotime("-1 month", strtotime($current_end_date)));
                        
			
		}
		

        
        //$url        = "http://192.168.22.103:810/KPI/KPI_Services.php?action=getRealizableValue&employee_id=000251&start_date=2012-07-10&end_date=2017-07-10";
        //10022105 employee id to check
        
	//$url        = DIALER_DASH_API."KPI/KPI_Services.php?action=getRealizableValue&employee_id=10029296&start_date=".urlencode($current_start_date)."&end_date=".urlencode($current_end_date);
	$url        = DIALER_DASH_API."KPI/KPI_Services.php?action=getRealizableValue&employee_id=".urlencode($employee_id)."&start_date=".urlencode($current_start_date)."&end_date=".urlencode($current_end_date);
        $dataParam  = array("url" => $url,"method" => 'get');
        $result     = utility::curlCall($dataParam); 
        
        
		$retVal['result']['current']['current_start_date'] = $current_start_date;
        $retVal['result']['current']['current_end_date'] = $current_end_date;
        $retVal['result']['current']['current_url'] = $url;
        $retVal['result']['current']['current_result'] = $result;
        
	//$url        = DIALER_DASH_API."KPI/KPI_Services.php?action=getRealizableValue&employee_id=10029296&start_date=".urlencode($prev_start_date)."&end_date=".urlencode($prev_end_date);
	$url        = DIALER_DASH_API."KPI/KPI_Services.php?action=getRealizableValue&employee_id=".urlencode($employee_id)."&start_date=".urlencode($prev_start_date)."&end_date=".urlencode($prev_end_date);
        $dataParam  = array("url" => $url,"method" => 'get');
        $result     = utility::curlCall($dataParam); 
        
		$retVal['result']['previous']['prev_start_date'] = $prev_start_date;
        $retVal['result']['previous']['prev_end_date'] = $prev_end_date;
        $retVal['result']['previous']['previous_url'] = $url;
        $retVal['result']['previous']['previous_result'] = $result;

		$retVal['error']['code'] = 0;
		$retVal['error']['msg'] = 'success';
        


        return json_encode($retVal);
    }
	
	public function getBudgetService(){
        header('Content-Type: application/json');
        $params     =   json_decode(file_get_contents('php://input'),true);
        $resultArr  =   array();
        $curlParams = 	array();
		$data_city  = 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url        = 	$this->genioconfig['jdbox_url'][strtolower($data_city)];
        $curlParams['url']          = $url.'services/getBudgetService.php?status='.$params['status'].'&custid='.$params['empId'].'&module=TME&act=dsview&data_city='.$params['data_city'];
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        
        if($singleCheck['error']    ==  0) {
            $resultArr['data']      =   $singleCheck['data'];
            $resultArr['totCount']  =   count($singleCheck['data']);
            $resultArr['errorCode']     =   0;
            $resultArr['errorStatus']   =   'Data Found';
        } else {
            $resultArr['errorCode']     =   1;
            $resultArr['errorStatus']   =   'Data Not Found';
        }
        return json_encode($resultArr);
    }

     
    public function updateBudgetService(){
        header('Content-Type: application/json');
        $params     =   json_decode(file_get_contents('php://input'),true);
        $resultArr  =   array();
        $curlParams = array();
        $data_city  				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url        				= $this->genioconfig['jdbox_url'][strtolower($data_city)];
        $curlParams['url']          = $url.'services/getBudgetService.php?upid='.$params['id'].'&act=updsapi&data_city='.$params['data_city'];
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        if($singleCheck['error']    ==  0) {
            $resultArr['errorCode']     =   0;
            $resultArr['errorStatus']   =   'Data Updated';
        } else {
            $resultArr['errorCode']     =   1;
        $resultArr['errorStatus']   =   'Data Not Updated';
        }
        return json_encode($resultArr);
     }
     
     public function updtLogoutTime(){
            
            header('Content-Type: application/json');

            $dataArr = json_decode(file_get_contents('php://input'), true);
            
            
            $employee_id = (isset($dataArr['employee_id']) && trim($dataArr['employee_id']) != '') ? $dataArr['employee_id'] : '';
            //$logout_time = date('Y-m-d h:i:s', time());
            $logout_time = date('Y-m-d H:i:s');
            
            
            //http://192.168.22.103:810/dialerDashboard/dialerDashboardServices/cron/updtTMESessionData.php?action=updtLogoutTime&employee_id=10030240&logout_time=2017-05-10 20:40:30
            //to check put employee id = 10004555 and logout date & time = 2017-05-10 18:50:10(time can b any) 
            $url = DIALER_DASH_API."dialerDashboard/dialerDashboardServices/cron/updtTMESessionData.php?action=updtLogoutTime&employee_id=".urlencode($employee_id)."&logout_time=".urlencode($logout_time);
            
            $dataParam  = array('url' => $url, 'method' => 'get');
            $result     = utility::curlCall($dataParam);
            $result['api'] = $url;

            return json_encode($result);
		
	}
	
	public function updateRdFlg(){
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['city']), $this->main_cities)) ? strtolower($params['city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['ucode'];
		$postArray['ucode'] 				= 	$params['ucode'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['contractEmpCode'] 		= 	$params['contractEmpCode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"updateRdFlg";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}	
	
	function getSpeedLinks() { // Done
        $retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= array();
        $curlParams 				= array();
        $paramsSend					= array();
		$paramsSend['empcode']		= $params['empCode'];
        $paramsSend['data_city']	= $params['data_city'];
        $paramsSend['action']		= 'getContractCatLive';
        $data_city=$params['data_city'];
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $url= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php';
		$postArray['empcode']=$params['empCode'];		 
		$postArray['post_data']="1";		 
		$postArray['module']="TME";		 
		$postArray['data_city']=$params['data_city'];		 
		$postArray['action']="getSpeedLinks";		 
		$dataParam     =  array('url' => $url, 'method' => 'post','postData' => $postArray);
		$result      =  utility::curlCall($dataParam);	
        return json_encode($result);
		 		
	}
	
	function setSpeedLinks() {		// Done
		$retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= array();
        $curlParams 				= array();
        $paramsSend					= array();
		$paramsSend['empcode']		= $params['empcode'];
        $paramsSend['data_city']	= $params['data_city'];
        $paramsSend['action']		= 'getContractCatLive';
        $data_city=$params['data_city'];
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php';
		$postArray['empcode']=$params['empcode'];		 
		$postArray['post_data']="1";		 
		$postArray['module']="TME";		 
		$postArray['data_city']=$params['data_city'];		 
		$postArray['setLink']=$params['setLink'];		 
		$postArray['extraVals']=$params['extraVals'];		 
		$postArray['display']=$params['display'];		 
		$postArray['setLinkName']=$params['setLinkName'];		 
		$postArray['action']="setSpeedLinks";
		$dataParam     =  array('url' => $url, 'method' => 'post','postData' => $postArray);
		$result      	=  json_decode(utility::curlCall($dataParam),true);
		return json_encode($result);
	}
	
	function setSortOrder() {		// Done
		$retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= array();
        $curlParams 				= array();
        $paramsSend					= array();
		$paramsSend['empcode']		= $params['tmecode'];
        $paramsSend['data_city']	= $params['data_city'];
        $paramsSend['action']		= 'getContractCatLive';
        $data_city					= $params['data_city'];
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url						= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php';
		$postArray['empcode']		=$params['tmecode'];		 
		$postArray['tmecode']		=$params['tmecode'];		 
		$postArray['post_data']		="1";		 
		$postArray['module']		="TME";		 
		$postArray['data_city']		=$params['data_city'];		 
		$postArray['sortOrder']		=$params['sortOrder'];
		if(isset($params['srchparam'])){
			$postArray['srchparam']=$params['srchparam'];
		}
		if(isset($params['trace'])){
			$postArray['trace']=$params['trace'];
		}		 		 
		$postArray['linkFlag']=$params['linkFlag'];		 		 
		$postArray['action']="setSortOrder";	
					 
		$dataParam     =  array('url' => $url, 'method' => 'post','postData' => $postArray);
		$result      =  utility::curlCall($dataParam);	
        return $result;
		 		
	}
	
    public function insertDeliveredCaseInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'insertDeliveredCaseInfo';
		$paramsSend['module'] =	MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/tmenewServices.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function updMnTabSaveAsNonPaid() {
        header('Content-Type: application/json');
        $params     =   json_decode(file_get_contents('php://input'),true);
        $retArr =   array();
        $paramsSend =   array();
        if(CS_SERVER_IP == 17){
            $rflag  =   1;
        }else{
            $rflag  =   0;
        }
		$data_city 					=   ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        //push Tme save as non paid data for moderation 
		$PushtoDataCorrection_arr	=	$this->PushtoDataCorrection($params);
        
        $paramsSend['parentid']     =   $params['parentid'];
        $paramsSend['data_city']    =   $params['data_city'];
        $paramsSend['module']       =   $params['module'];
        $paramsSend['usercode']     =   $params['usercode'];
        $paramsSend['username']     =   $params['username'];
        $paramsSend['action']       =   $params['action'];
        $paramsSend['landline']     =   $params['landline'];
        $paramsSend['me_jda_flag']  =   $params['me_jda_flag'];
        $curlParams 				= 	array();
        $curlParams['url']          = 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/savenonpaid_jda.php';
        $curlParams['formate']      = 	'basic';
        $curlParams['method']       = 	'post';
        $curlParams['headerJson']   = 	'json';
        $curlParams['postData']     = 	json_encode($paramsSend);
        $response171_str            =   Utility::curlCall($curlParams);
        $response171_arr            = 	json_decode($response171_str,true);
        $webresmsg                  = 	'';
        if($response171_arr['error']['code']    ==  0){
            $resultApiArr171['errorCode']   =   0;
            $resultApiArr171['errorStatus'] =   'Success';
            $paramsSendArr  				=   array();
            $paramsSendArr['parentid']      =   $params['parentid'];
            $paramsSendArr['data_city']     =   $params['data_city'];
            $paramsSendArr['module']        =   $params['module'];
            $paramsSendArr['ucode']         =   $params['usercode'];
            $curlParamsArr 					= 	array();
            $curlParamsArr['url']   		= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/instant_live.php';
            $curlParamsArr['formate'] 		= 	'basic';
            $curlParamsArr['method'] 		= 	'post';
	            $curlParamsArr['postData'] 		= 	$paramsSendArr;
            $responseWeb_str            	=   Utility::curlCall($curlParamsArr);
            $responseWeb_arr            	= 	json_decode($responseWeb_str,true);
            if($responseWeb_arr['error']['message'] ==  "success"){
                $webresmsg                      =   "Data has been updated on Website.";
                $resultApiArrweb['errorCode']   =   0;
                $resultApiArrweb['errorStatus'] =   'Success';
            }else{
				$webresmsg                      =  "Pushing data on website is under processing. Kindly check after 10-15 minutes.";
                $resultApiArrweb['errorCode']   =   1;
                $resultApiArrweb['errorStatus'] =   'Data Not Inserted';
            }
        }else{
            $resultApiArr171['errorCode']       =   1;
            $resultApiArr171['errorStatus']     =   'Data Not Inserted';
            $resultApiArrweb['repweb']          =   json_encode(array("msg" => "Instant API Not Called"));
        }
        $mainResponseLog['response171'] = $resultApiArr171;
        $mainResponseLog['responseweb'] = $resultApiArrweb;
        $mainResponseLog['resp171']     = $response171_arr;
        if($webresmsg){
            $mainResponseLog['responsemsg'] = $mainResponseLog['resp171']['error']['msg'].". ".$webresmsg;
        }else{
            $mainResponseLog['responsemsg'] = $mainResponseLog['resp171']['error']['msg'];
        }
        return json_encode($mainResponseLog);
    }
    
    private function PushtoDataCorrection($param)
	{		
		$data_jdbox_url =JDBOX_API."/services/mongoWrapper.php";
		$param_data = Array();
		$param_data['action'] 		=	'getalldata';
		$param_data['post_data'] 	=	'1';
		$param_data['parentid'] 	=	$param['parentid'];
		$param_data['data_city'] 	=	$param['data_city'];
		$param_data['module'] 	  	=	'TME';
		
		
		$dataParam_mongo     =  array('url' => $data_jdbox_url, 'method' => 'post','postData' => $param_data);
		$data_res      =  utility::curlCall($dataParam_mongo);
		
		if(!is_array($data_res['tbl_business_temp_data']))
			$data_res['tbl_business_temp_data']	=	array();
		if(!is_array($data_res['tbl_temp_intermediate']))	
			$data_res['tbl_temp_intermediate']	=	array();
		if(!is_array($data_res['tbl_companymaster_generalinfo_shadow']))	
			$data_res['tbl_companymaster_generalinfo_shadow']	=	array();
		if(!is_array($data_res['tbl_companymaster_extradetails_shadow']))	
			$data_res['tbl_companymaster_extradetails_shadow']	=	array();
		
		$ret_data_orig['data'] =  array_merge($data_res['tbl_companymaster_generalinfo_shadow'],$data_res['tbl_companymaster_extradetails_shadow'],$data_res['tbl_temp_intermediate'],$data_res['tbl_business_temp_data']);
		
		if(is_array($ret_data_orig['data']) && count($ret_data_orig['data'])>0)
		{
			$catid_arr			=	explode('|P|',trim($ret_data_orig['data']['catIds'],'|P|'));
			$national_catid_arr	=	explode('|P|',trim($ret_data_orig['data']['nationalcatIds'],'|P|'));
			
			$ret_data_orig['data']['catidlineage']	=	"/".implode('/,/',$catid_arr)."/";
			$ret_data_orig['data']['national_catidlineage']	= "/".implode('/,/',$national_catid_arr)."/";
			$ret_data_orig['data']['ucode']	= $ret_data_orig['data']['updatedBy'];
			$ret_data_orig['data']['uname']	= $ret_data_orig['data']['name_code'];
			
			
			$datacorrection_url					=	DECS_TME."/api_dc/datacorrection_api.php";
			
			$param_arr = Array();
			$param_arr['parentid']			=	$ret_data_orig['data']['parentid'];
			$param_arr['mod_type']			=	'TME_SAVENP';
			$param_arr['userid']			=	$param['usercode'];
			$param_arr['edited_date']		=	date('Y-m-d H:i:s');
			$param_arr['data_city']			=	$ret_data_orig['data']['data_city'];
			$param_arr['user_data']     	=   json_encode($ret_data_orig['data']);
				
			$curl_arr     	=  array('url' => $datacorrection_url, 'method' => 'post','postData' => $param_arr);
			$response     	=  utility::curlCall($curl_arr);
			
			return $response;
		}
	}
	
	
    public function insertSaveLogs(){
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
        header('Content-Type: application/json');
        $params     =   json_decode(file_get_contents('php://input'),true);
        $conn_tme   =   new DB($this->db['db_tme']);
        $dbObjEmp	=	new DB($this->db['db_iro']);
        $resultArr  =   array();
        $jda_resp   =   json_encode($params['jda_resp']);
		$nrewArr						= array();
		$nrewArr['resp171']				= $params['resp171'];
		$nrewArr['web_resp']			= $params['web_resp'];
		$nrewArr['jda_save_response']	= $jda_resp;
		$log_data['url'] 				= LOG_URL.'logs.php';
		$post_data['ID']         		= $params['parentid'];                
		$post_data['PUBLISH']    		= 'TME';         	
		$post_data['ROUTE']      		= 'saveasfreelisting';   		
		$post_data['CRITICAL_FLAG']  	= 1 ;			
		$post_data['MESSAGE']        	= 'Save As Free Listing';
		$post_data['DATA']['response']	= $nrewArr;
		$post_data['DATA']['empcode']	= $params['usercode'];
		$log_data['method'] 			= 'post';
		$log_data['formate'] 			= 'basic';
		$log_data['postData'] 			=  http_build_query($post_data);
		$log_res						=  Utility::curlCall($log_data);
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
		$postArray['data_city'] 			= 	$data_city;
		$postArray['empcode'] 				= 	$params['usercode'];
		$postArray['web_resp'] 				= 	json_encode($params['web_resp']);
		$postArray['resp171'] 				= 	json_encode($params['resp171']);
		$postArray['jda_save_response'] 	= 	addslashes(stripslashes($jda_resp));
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";
		$postArray['action']				= 	"saveasnonpaidlog";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
    }
    
    public function insertWhatsapp() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				=	array();
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'insertWhatsapp';
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/tmenewServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 public function insertWhatsappData() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				=	array();
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'insertWhatsappData';
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/tmenewServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function insertWhatsappSendMsg() {
		$resarr 		= array();
		if(isset($_REQUEST['urlFlag']) && $_REQUEST['urlFlag'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams 			= array();
		$curlParams['url'] 		=	$this->genioconfig['jdbox_url'][strtolower($params['data_city'])]."services/compaignPromo.php?data_city=".$params['data_city']."&action=sendmessage&parentid=".$params['parentid']."&empcode=".$params['empcode']."&campaignname=".$params['campaignname']."&mobile=".$params['mobile'].'&remote_zone='.$remote_zone.'&compname='.urlencode($params['compname']).'&area='.urlencode($params['area']).'&pincode='.$params['pincode'].'&city='.$params['city'];
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function getallcampaigns(){
	 	$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= 	array();
		$paramsSend				=	array();
		$paramsSend 			= 	$params;
		$paramsSend['action'] 	= 	$params['action'];
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= 	$this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/budgetInfo.php";
		$curlParams['formate'] 	= 	'basic';
		$curlParams['method'] 	= 	'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = 	json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	}
	///////////////////////////COVER////////////
	
	public function check_if_propic_selected(){
	 	$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= 	array();
		$paramsSend				=	array();
		$paramsSend 			= 	$params;
		$paramsSend['action'] 	= 	'checkifpropicselected';
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= 	$this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/tmenewServices.php";
		//$curlParams['url'] 		= 	"http://172.29.0.217:1010/services/tmenewServices.php";
		$curlParams['formate'] 	= 	'basic';
		$curlParams['method'] 	= 	'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = 	json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	}

	public function setImageProPic(){
	 	$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= 	array();
		$paramsSend				=	array();
		$paramsSend 			= 	$params;
		$paramsSend['action'] 	= 	'setImageProPic';
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= 	$this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/tmenewServices.php";
		//$curlParams['url'] 		= 	"http://172.29.0.217:1010/services/tmenewServices.php";
		$curlParams['formate'] 	= 	'basic';
		$curlParams['method'] 	= 	'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = 	json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	}

	public function editeddata(){
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$retArr = array();
		$retArr['data'] = array();

		$dbObjLocal				=	new DB($this->db['db_local']);

		$query = "select * from tbl_companydetails_edit where parentid='".$params['parentid']."' order by entry_date desc limit 1";

		$conquery	=	$dbObjLocal->query($query);

		$numRows	=	$dbObjLocal->numRows($conquery);
			if($numRows > 0) {
				while($resData	=	$dbObjLocal->fetchData($conquery)) {
					$retArr['data']	=	$resData;
				}

				$retArr['errCode'] = 0;
				$retArr['errStatus'] = "data found";
			}
			else{
				$retArr['errCode'] = 1;
				$retArr['errStatus'] = "data not  found";
			}
			$retArr['data']['edited_data'] = json_decode($retArr['data']['edited_data'],1);
			
			// print_r($retArr);
		return json_encode($retArr);
	}

	public function manageediteddata(){
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}


		if($params['flag']==1){
			$params['nonvalidated'] = json_decode($params['nonvalidated'],1);
			$params['validated'] = json_decode($params['validated'],1);
			$result_data = array();
			$retArr = array();
			$retArr['data'] = array();
			$data_bck = array();


			$dbObjLocal				=	new DB($this->db['db_local']);

			$query = "select * from tbl_companydetails_edit where parentid='".$params['parentid']."' order by entry_date desc limit 1";

			

			$conquery	=	$dbObjLocal->query($query);

			$numRows	=	$dbObjLocal->numRows($conquery);
				if($numRows > 0) {
					while($resData	=	$dbObjLocal->fetchData($conquery)) {
						$retArr['data']	=	$resData;
						$result_data    =	$resData;   
						$retArr['data']['edited_data'] = json_decode($retArr['data']['edited_data'],1);
					}

					if(sizeof($params['validated'])>0){
						foreach($params['validated'] as $key=>$value){
						if($value == 'landline'){
							$data_bck['edited_data']['Call_1'] = $retArr['data']['edited_data']['Call_1']; 
							$data_bck['edited_data']['Call_1'] = $retArr['data']['edited_data']['oCall'];

							unset($retArr['data']['edited_data']['oCall']);
							unset($retArr['data']['edited_data']['Call_1']);
						}

						if($value == 'contact_person'){
							$data_bck['edited_data']['Contact_Person'] = $retArr['data']['edited_data']['Contact_Person']; 
							$data_bck['edited_data']['oContact_Person'] = $retArr['data']['edited_data']['oContact_Person'];
							unset($retArr['data']['edited_data']['Contact_Person']);
							unset($retArr['data']['edited_data']['oContact_Person']);
						}

						if($value == 'mobile'){
							$data_bck['edited_data']['Mobile'] = $retArr['data']['edited_data']['Mobile']; 
							$data_bck['edited_data']['oMobile'] = $retArr['data']['edited_data']['oMobile'];

							unset($retArr['data']['edited_data']['Mobile']);
							unset($retArr['data']['edited_data']['oMobile']);
						}

						if($value == 'fax'){
							$data_bck['edited_data']['Fax'] = $retArr['data']['edited_data']['Fax']; 
							$data_bck['edited_data']['oFax'] = $retArr['data']['edited_data']['oFax'];

							unset($retArr['data']['edited_data']['Fax']);
							unset($retArr['data']['edited_data']['oFax']);
						}

						if($value == 'emailid'){
							$data_bck['edited_data']['Email_ID'] = $retArr['data']['edited_data']['Email_ID']; 
							$data_bck['edited_data']['oEmail_ID'] = $retArr['data']['edited_data']['oEmail_ID'];

							unset($retArr['data']['edited_data']['Email_ID']);
							unset($retArr['data']['edited_data']['oEmail_ID']);
						}

						if($value == 'website'){
							$data_bck['edited_data']['Website_Address'] = $retArr['data']['edited_data']['Website_Address']; 
							$data_bck['edited_data']['oWebsite_Address'] = $retArr['data']['edited_data']['oWebsite_Address'];

							unset($retArr['data']['edited_data']['Website_Address']);
							unset($retArr['data']['edited_data']['oWebsite_Address']);
						}

						if($value == 'building'){
							$data_bck['edited_data']['Building'] = $retArr['data']['edited_data']['Building']; 
							$data_bck['edited_data']['oBuilding'] = $retArr['data']['edited_data']['oBuilding'];

							unset($retArr['data']['edited_data']['Building']);
							unset($retArr['data']['edited_data']['oBuilding']);
						}

						if($value == 'Building'){
							$data_bck['edited_data']['Building'] = $retArr['data']['edited_data']['Building']; 
							$data_bck['edited_data']['oBuilding'] = $retArr['data']['edited_data']['oBuilding'];

							unset($retArr['data']['edited_data']['Building']);
							unset($retArr['data']['edited_data']['oBuilding']);
						}

						if($value == 'street'){
							$data_bck['edited_data']['Street'] = $retArr['data']['edited_data']['Street']; 
							$data_bck['edited_data']['oStreet'] = $retArr['data']['edited_data']['oStreet'];

							unset($retArr['data']['edited_data']['Street']);
							unset($retArr['data']['edited_data']['oStreet']);
						}

						if($value == 'area'){
							$data_bck['edited_data']['Area'] = $retArr['data']['edited_data']['Area']; 
							$data_bck['edited_data']['oArea'] = $retArr['data']['edited_data']['oArea'];
							$data_bck['edited_data']['Subarea'] = $retArr['data']['edited_data']['Subarea']; 
							$data_bck['edited_data']['oSubarea'] = $retArr['data']['edited_data']['oSubarea'];

							unset($retArr['data']['edited_data']['Area']);
							unset($retArr['data']['edited_data']['oArea']);
							unset($retArr['data']['edited_data']['Subarea']);
							unset($retArr['data']['edited_data']['oSubarea']);
						}

						if($value == 'landmark'){
							$data_bck['edited_data']['Landmark'] = $retArr['data']['edited_data']['Landmark']; 
							$data_bck['edited_data']['oLandmark'] = $retArr['data']['edited_data']['oLandmark'];

							unset($retArr['data']['edited_data']['Landmark']);
							unset($retArr['data']['edited_data']['oLandmark']);
						}

						if($value == 'pincode'){
							$data_bck['edited_data']['PinCode'] = $retArr['data']['edited_data']['PinCode']; 
							$data_bck['edited_data']['oPinCode'] = $retArr['data']['edited_data']['oPinCode'];

							unset($retArr['data']['edited_data']['PinCode']);
							unset($retArr['data']['edited_data']['oPinCode']);
						}

						if($value == 'contact_person'){
							$data_bck['edited_data']['Contact_Person'] = $retArr['data']['edited_data']['Contact_Person']; 
							$data_bck['edited_data']['oContact_Person'] = $retArr['data']['edited_data']['oContact_Person'];

							unset($retArr['data']['edited_data']['Contact_Person']);
							unset($retArr['data']['edited_data']['oContact_Person']);
						}

						if($value == 'Toll_Free'){
							$data_bck['edited_data']['Toll_Free'] = $retArr['data']['edited_data']['Toll_Free']; 
							$data_bck['edited_data']['oToll_Free'] = $retArr['data']['edited_data']['oToll_Free'];

							unset($retArr['data']['edited_data']['Toll_Free']);
							unset($retArr['data']['edited_data']['oToll_Free']);
						}

						if($value == 'working_time'){
							$data_bck['edited_data']['working_time_start'] = $retArr['data']['edited_data']['working_time_start']; 
							$data_bck['edited_data']['oToll_Free'] = $retArr['data']['edited_data']['oworking_time_start'];

							$data_bck['edited_data']['working_time_end'] = $retArr['data']['edited_data']['working_time_end']; 
							$data_bck['edited_data']['oworking_time_end'] = $retArr['data']['edited_data']['oworking_time_end'];

							unset($retArr['data']['edited_data']['working_time_start']);
							unset($retArr['data']['edited_data']['oworking_time_start']);

							unset($retArr['data']['edited_data']['working_time_end']);
							unset($retArr['data']['edited_data']['oworking_time_end']);
						}

						if($value == 'payment'){
							$data_bck['edited_data']['mode_of_payment'] = $retArr['data']['edited_data']['mode_of_payment']; 
							$data_bck['edited_data']['omode_of_payment'] = $retArr['data']['edited_data']['omode_of_payment'];

							unset($retArr['data']['edited_data']['mode_of_payment']);
							unset($retArr['data']['edited_data']['omode_of_payment']);
						}

						if($value == 'year'){
							$data_bck['edited_data']['year_establishment'] = $retArr['data']['edited_data']['year_establishment']; 
							$data_bck['edited_data']['oyear_establishment'] = $retArr['data']['edited_data']['oyear_establishment'];

							unset($retArr['data']['edited_data']['year_establishment']);
							unset($retArr['data']['edited_data']['oyear_establishment']);
						}

					}

					$update  ="UPDATE tbl_companydetails_edit 
					   			SET 
					   				all_edited_data ='".$result_data['edited_data']."',
					   				edited_data ='".json_encode($retArr['data']['edited_data'])."',
					   				delete_edited_data ='".json_encode($data_bck)."',
					   				validatedarr='".json_encode($params['validated'])."',
					   				notvalidatedarr='".json_encode($params['nonvalidated'])."' 	
					   			WHERE 
					   				parentid='".$params['parentid']."'";
					 $conquery	=	$dbObjLocal->query($update);

					$retArr['errCode'] = 0;
					$retArr['errStatus'] = "data found";

					}
					// else{
					// 	$retArr['errCode'] = 1;
					// 	$retArr['errStatus'] = "data not  found";
					// }

					
				}
				else{
					$retArr['errCode'] = 1;
					$retArr['errStatus'] = "data not  found";
				}

				// if(sizeof($params['nonvalidated'])==0){
				 // echo 
					// $conquery	=	$dbObjLocal->query($delete);
					if(sizeof($params['nonvalidated'])==0){
						$delete = "DELETE FROM tbl_correct_incorrect WHERE parentid='".$params['parentid']."'";
						$conquery	=	$dbObjLocal->query($delete);
					}
					
				// }

				$retArr['data'] = array();
				
				// print_r($retArr);
			return json_encode($retArr);
		}
		else{
			$retArr = array();
			$retArr['data'] = array();

			$dbObjLocal				=	new DB($this->db['db_local']);

			if($params['paramval']=='Street'){

				$query = "select * from tbl_areamaster_consolidated_v3 where entity_area='".$params['searchval']."'";
				// $query = "select * from tbl_areamaster_consolidated_v3 where entity_area='Koramangala Road'";

				$resquery = $dbObjLocal->query($query);
				$resnumrows = $dbObjLocal->numRows($resquery);
				if($resnumrows>0){
					$rows 	= $dbObjLocal->fetchData($resquery);
					$retArr['data'][] =$rows;
					$retArr['msg'] = 'Data Found';
					$retArr['errCode'] = 0;
				}
				else{
					$retArr['msg'] = 'Data Found';
					$retArr['errCode'] = 0;
				}

			}

			if($params['paramval']=='Landmark'){

				$query = "select * from tbl_areamaster_consolidated_v3 where entity_area='".$params['searchval']."'";
				// echo $query = "select * from tbl_areamaster_consolidated_v3 where entity_area='Koramangala Road'";

				$resquery = $dbObjLocal->query($query);
				$resnumrows = $dbObjLocal->numRows($resquery);
				if($resnumrows>0){
					$rows 	= $dbObjLocal->fetchData($resquery);
					$retArr['data'][] =$rows;
					$retArr['msg'] = 'Data Found';
					$retArr['errCode'] = 0;
				}
				else{
					$retArr['msg'] = 'Data Found';
					$retArr['errCode'] = 0;
				}

			}

			return json_encode($retArr);
		}
		
	}
	///////////////////////////COVER////////////

	public function getAhdLineage() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 				= array();
		$paramsSend					= array();
		$paramsSend 				= $params;
		$paramsSend['auth_token'] 	= 'TUFEasRsasqhJhyfagjhsasqNlaccuafasnU';
		$paramsSend['empcode'] 		= $params['empcode'];
		$curlParams['url'] 			= "http://192.168.20.237:8080/api/EmpReportingUpdateDetails.php";
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	=  json_encode($paramsSend); 
		$curlcall					=  Utility::curlCall($curlParams);
		return $curlcall;
	 }
	
	public function fetchFreebeesEmp() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['data_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				=	array();
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'fetchFreebeesEmp';
		$paramsSend['module'] 	=	MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])]."services/tmenewServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['timeout']  = 3;
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
}
