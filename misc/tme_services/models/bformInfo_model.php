<?php
class BformInfo_Model extends Model {
	function __construct() {
        parent::__construct();
        $this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
    }
	
	private function validateParams($params){
		
		if(!isset($params) || count($params) == 0 ){
			$resarr['error']['msg'] = 'No Parameters passed';
			$resarr['error']['code'] = '1';
			echo json_encode($resarr);die;
		}if(($params['parentid'] == '') && ($params['noparentid'] !=1)){
			$resarr['error']['msg'] = 'Parentid Missing';
			$resarr['error']['code'] = '1';
			echo json_encode($resarr);die;
		}else if($params['data_city']  == ''){
			$resarr['error']['msg'] = 'Data City Missing';
			$resarr['error']['code'] = '1';
			echo json_encode($resarr);die;
		}else if($params['ucode']  == ''){
			$resarr['error']['msg'] = 'User Code Missing';
			$resarr['error']['code'] = '1';
			echo json_encode($resarr);die;
		}else if($params['uname']  == ''){
			$resarr['error']['msg'] = 'User Name Missing';
			$resarr['error']['code'] = '1';
			echo json_encode($resarr);die;
		}
	}
	 public function getTempData() { 
		 //~ http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/bformInfo/getTempData?parentid=PXX22.XX22.171203102328.T3N2&data_city=mumbai&ucode=10023531&uname=sand&server_city=mumbai
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$this->validateParams($params); // common params validation
		
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams = array();
		
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'tempdetails';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		
		
		return $curlcall;
		 
	 }
	public function getDNDInfo() {
		 //~ http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/bformInfo/getDNDInfo?parentid=PXX22.XX22.171203102328.T3N2&data_city=mumbai&ucode=10023531&uname=sand&server_city=mumbai
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'dndinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		
		
		return $curlcall;
		 
	 }
	 
	 public function getStateInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'stateinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function correctIncorrectInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'correctincorrectinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getCityInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'cityinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function cityAutosuggest() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'cityautosuggest';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getStreetInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'streetinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 
	 public function getAreaInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'areainfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	  public function areaAutosuggest() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'areaautosuggest';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function areaPincodeRequest() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'areapincoderequest';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getLandmarkInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'landmarkinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getStdCodeInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'stdcodeinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getPincodeInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'pincodeinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function pincodeLookup() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'pincodelookup';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	  public function sourceWiseDupCheck() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'sourcewisedupchk';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 
	 
	 public function submitLocationForm() {
		 //~ http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/bformInfo/getDNDInfo?parentid=PXX22.XX22.171203102328.T3N2&data_city=mumbai&ucode=10023531&uname=sand&server_city=sasa
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'utfcheck';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	json_decode(Utility::curlCall($curlParams),1);
		$resarr['utf_check'] = $curlcall;
		$global_api_res = json_decode($this->globalCompanyApi($params),1);
		$resarr['globalCompanyApi'] = $global_api_res;
		return json_encode($resarr);
	}
	 
	public function globalCompanyApi($params){
		if(count($params)<=0){
			if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
				$params		=$_REQUEST;
			}else{
				header('Content-Type: application/json');
				$params		=	json_decode(file_get_contents('php://input'),true);
			}
		}
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$params_arr = array();
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/global_company_api.php";
		foreach($params as $key => $val) {
			$params_arr[$key] = $val;
		}
		$params_arr['module'] = MODULE;

		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($params_arr); 
		return $curlcall	=	Utility::curlCall($curlParams);
		 
	}
	 	 
	public function getPaymentNarrationInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'paymentnarrationInfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getMandateinfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'mandateinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	  public function sendAppLink() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'sendapplink';
		$paramsSend['module'] =	MODULE;
		$paramsSend['city']   =	$params['data_city'];
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function sendTvAdLink() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'sendtvadlink';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function sendTvAdNAppLink(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'sendtvadnapplink';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	public function checkLeadContract(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'getContractType';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	}
	
	 public function checkEntryEcslead() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'checkentryecslead';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }	 
	 public function updateClientInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'updateclientinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function insertLog() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = $params['action']; // action is mandatory to pass here, based on that logging will happen
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	
	public function instantLiveApi (){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params);
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'correct_incorrect_update';
		$paramsSend['module'] =	MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcallResp	=	Utility::curlCall($curlParams);
		
		$curlParams_snp = array();
		$paramsSend_snp	=	array();
		
		
		$paramsSend_snp['parentid']	   	=	$params['parentid'];
		$paramsSend_snp['data_city']	=   $params['data_city'];
		$paramsSend_snp['module']       =   MODULE;
        $paramsSend_snp['usercode']     =   $params['ucode'];
        $paramsSend_snp['username']     =   $params['uname'];
		
		$curlParams_snp['url'] 		  =  $this->genioconfig['jdbox_url'][strtolower($params['server_city'])].'services/savenonpaid_jda.php'; 
		$curlParams_snp['formate']    = 'basic';
		$curlParams_snp['method'] 	  = 'post';
		$curlParams_snp['headerJson'] = 'json';
		$curlParams_snp['postData']   = json_encode($paramsSend_snp);
		$snp_result					  = json_decode(Utility::curlCall($curlParams_snp),true);
		
		if($snp_result['error']['code']	==	0){
			$curlParams_ins = array();
			$paramsSend_ins	=	array();
			
			$paramsSend_ins['parentid']	   	=	$params['parentid'];
			$paramsSend_ins['data_city']	=   $params['data_city'];
			$paramsSend_ins['module']       =   MODULE;
			$paramsSend_ins['ucode']     	=   $params['ucode'];
			
			$curlParams_ins['url'] 		  =  $this->genioconfig['jdbox_url'][strtolower($params['server_city'])].'services/instant_live.php'; 
			$curlParams_ins['formate']    = 'basic';
			$curlParams_ins['method'] 	  = 'post';
			$curlParams_ins['headerJson'] = 'json';
			$curlParams_ins['postData']   = json_encode($paramsSend_ins);
			$ins_result			  		  = Utility::curlCall($curlParams_ins);
			return $ins_result;
		}else{
			return json_encode($snp_result);
		}
	}
	
	public function insertgenralinfoshadow(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params);
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'insertgenralinfoshadow';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		
		return $curlcall	=	Utility::curlCall($curlParams);
	}
	
	public function insertextradetailsshadow(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params);
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'insertextradetailsshadow';
		$paramsSend['module'] =	MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		return $curlcall	=	Utility::curlCall($curlParams);
	}
	
	public function getbypassdet(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		if((isset($params['tme_central'])) && ($params['tme_central'] == 1)){
			return 1;
		}
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams = array();
		$paramsSend	=	array();
		$params['noparentid'] = 1;
		$paramsSend = $params;
		$paramsSend['action'] = 'getbypassdet';
		$paramsSend['module'] = MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		return $curlcall	=	Utility::curlCall($curlParams);
	}
	
	
	public function insertirodetails(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params);
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'insertirodetails';
		$paramsSend['module'] =	MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		return $curlcall	=	Utility::curlCall($curlParams);
	}
	
	public function inserttempinter(){
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params);
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'inserttempinter';
		$paramsSend['module'] =	MODULE;
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		return $curlcall	=	Utility::curlCall($curlParams);
	}
	public function iroAppTransfer() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'iroapptransfer';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	  public function iroAppSaveExit() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'iroappsavenexit';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	  public function iroAppProceed() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'iroappproceed';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	  public function getJdrrDetails() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'getjdrrdetails';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getFreeListingData() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'getpiddata';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function getMatchedActiveData() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'matchedactivedata';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function estimatedSearchInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'estimatedsearchlink';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function ecsTransferInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'ecstransferinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function webDialerAllocation() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'webdialerallocation';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	 
	 public function ecsEscalationDetails() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'ecsescalationdetails';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }
	  public function buildingAutoComplete(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'building_autocomplete';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 public function iroCardInfo() {
		$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend = $params;
		$paramsSend['action'] = 'irocardinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
		 
	 }

	 public function fetchRestInfo(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'fetch_restaurant_info';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function jdpayEcsPopup(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'jdpay_ecs_popup';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function ecsTransDetailsUpdate(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'ecs_trans_details_update';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }

	public function phoneSearchAllocation(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'phonesearchallocation';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function ecsSendUpgradeRequest(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');	
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'ecssendupgraderequest';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function fetchEcsDetails(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');	
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'fetchecsdetails';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function getAllTme(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');	
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'getalltme';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	 public function updateRetentionTmeInfo(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');	
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'updateretentiontmeinfo';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
	 
	  public function updateRepeatCount(){
	 	$resarr = array();
		if(isset($_REQUEST['get_flg']) && $_REQUEST['get_flg'] = 1){
			$params		=$_REQUEST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		
		$this->validateParams($params); // common params validation
		$params['server_city'] 	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');	
		
		$curlParams = array();
		$paramsSend	= array();
		$paramsSend = $params;
		$paramsSend['action'] = 'updaterepeatcount';
		$paramsSend['module'] =	MODULE;
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($params['server_city'])]."/services/fetchAllDetails.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$curlcall	=	Utility::curlCall($curlParams);
		return $curlcall;
	 }
}


?>
