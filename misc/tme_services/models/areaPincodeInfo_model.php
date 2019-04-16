<?php
class AreaPincodeInfo_Model extends Model {
	function __construct() {
        parent::__construct(); 
        $this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
    }
    
    public function getAllArea() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$retArr						=	array();
		$paramsSend					=	array();
		$paramsSend['opt']			=	$params['opt']['sendAttr'];	
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['rds']			=	$params['opt']['rds'];	
		$paramsSend['pincode']		=	$params['opt']['pincode'];	
		$paramsSend['module']		=	MODULE;
		$curlParams 				= 	array();
		$data_city					=   ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url'] 			= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/areaDetails.php';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'post';
		$curlParams['headerJson'] 	= 	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend); 
		$singleCheck				=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function setAreaPincodeInfo() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$retArr						=	array();
		$paramsSend					=	array();
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['pincodelist']	=	$params['pincodeStr'];	
		$paramsSend['action']		=	'set';	
		$paramsSend['module']		=	MODULE;	
		$pincodejson 				= 	'';
		if(isset($params['pincodejson'])){
			$pincodejson 			= 	json_encode($params['pincodejson']);
		}
		$paramsSend['pincodejson'] 	= 	$pincodejson;
		$curlParams 				= 	array();	
		$data_city					=   ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url'] 			= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/pincodeSelection.php';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'post';
		$curlParams['headerJson'] 	= 	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend); 
		$singleCheck				=	Utility::curlCall($curlParams);
		return $singleCheck;
	}

	public function getAllPincodes($parentid,$data_city) {
		$paramsSend					=	array();
		$paramsSend['parentid']		=	$parentid;
		$paramsSend['data_city']	=	$data_city;
		$paramsSend['module']		=	MODULE;
		$paramsSend['action']		=	'get';
		$curlParams 				= 	array();
		$datacity					=   ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams['url'] 			= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/pincodeSelection.php';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'post';
		$curlParams['headerJson'] 	= 	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend); 
		$singleCheck				=	Utility::curlCall($curlParams);
		return $singleCheck;
	}	
}
?>
