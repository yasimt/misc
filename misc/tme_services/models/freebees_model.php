<?php
class Freebees_Model extends Model {
	public function __construct() {
		parent::__construct();
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
		$this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		$this->authToken = "ZedA76A%'>j0~'z]&w7bR64{s";
	}
	
	public function getEmpInfo() {
		header('Content-Type: application/json');
		$params 					=   json_decode(file_get_contents('php://input'),true);
		$retArr						=	array();
		$curlParams 				= array();
		$curlParams['url'] 			= 	SSOINFO.'/api/fetch_employee_info_lite.php?auth_token='.urlencode($this->authToken).'&empcode='.$params['empcode'];
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST';
		$curlParams['postData'] 	= 	json_encode($params);
		$curlParams['headerJson'] 	= 	'json';
		$singleCheck				=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function checkfreebees(){
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
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"checkfreebees";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		 // echo '<pre>';print_r(Utility::curlCall($dataParam)); die;
		return json_encode($retArr);
	}
	
	public function insertfreebees(){
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
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['companyname'] 			= 	$params['companyname'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['campaign'] 				= 	$params['campaign'];
		$postArray['reason'] 				= 	$params['reason'];
		$postArray['version'] 				= 	$params['version'];
		$postArray['catids'] 				= 	$params['catids'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"insertfreebees";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		 // echo '<pre>';print_r(Utility::curlCall($dataParam)); die;
		return json_encode($retArr);
	}
	
	public function getFreebeesInfo() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				= array();
		$params['noparentid']	=	1;
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'getFreebeesInfo';
		$paramsSend['module'] 	= MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/contractServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		//~ print_r($curlcall);
		return $curlcall;
	}
	
	public function updateDetails() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				= array();
		$params['noparentid']	=	1;
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'updateDetails';
		$paramsSend['module'] 	= MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/contractServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	}
	
	public function resetfreebeesInfo() {
		$resarr 		= array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams 			= array();
		$paramsSend				= array();
		$params['noparentid']	=	1;
		$paramsSend 			= $params;
		$paramsSend['action'] 	= 'resetfreebeesInfo';
		$paramsSend['module'] 	= MODULE;
		$curlParams['url'] 		= $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/contractServices.php";
		$curlParams['formate'] 	= 'basic';
		$curlParams['method'] 	= 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall				=	Utility::curlCall($curlParams);
		return $curlcall;
	}
}
?>
