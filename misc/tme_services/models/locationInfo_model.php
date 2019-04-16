<?php 
class LocationInfo_Model extends Model{
	function __construct(){
		 parent::__construct();
		 $this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
	}
	
	public function street_master_auto(){ // in use - Pass module datacity and empcode
		
		$moduleval	 	=	strtoupper(MODULE);
		$retArr 		=	array();
		$urlFlag 		=	$_REQUEST['urlFlag'];
		header('Content-Type:application/json');
		$params 		=	json_decode(file_get_contents('php://input'),true);
		$data_city		=	$params['data_city'];
		if(!$urlFlag){
			$search_str =	$params['search'];
			$city_code 	=	$params['city'];
			$pin_code 	=	$params['pincode'];
			$area_code 	=	$params['area']	;
			$data_city		=	$params['data_city'];
			$empcode		=	$params['empcode'];
			$empname		=	$params['empname'];
			
			$moduleval		=	$params['module'];
			$parentid		=	$params['parentid'];
			
		}else{
			$area_code 		=	$params['area'] 	=	$_REQUEST['area'] ;
			$search_str 	=	$params['search']	=	$_REQUEST['search'];
			$city_code 		=	$params['city'] 	=	$_REQUEST['city'];
			$pin_code 		=	$params['pincode']	=	$_REQUEST['pincode'];
			$search_str		=	urlencode(urldecode($search_str));
			$city_code		=	urlencode(urldecode($city_code));
			$main_area		=	urlencode(urldecode($main_area));
			$area_code		= 	urlencode(urldecode($area_code));
			$data_city		=	$_REQUEST['data_city'];
			$empcode		=	$_REQUEST['empcode'];
			$empname		=	$_REQUEST['empname'];
			$moduleval		=	$_REQUEST['module'];
			$parentid		=	$_REQUEST['parentid'];
		}
		//~ $request_type 			=	"street";
		//~ $paramsSend 			= 	array("search"=>$search_str,"limit"=>10,"request_type"=>$request_type,"citynm"=>$city_code,"module"=>$moduleval,"pincode"=>$pin_code,"area"=>$area_code);
		//~ $url 					=	DECS_CITY."/api_services/area_autosuggestApi.php?rquest=location_autosuggest&";	
		
		$curlParams = array();
		$dataArr	=	array();
		
		$dataArr['parentid']	=	$parentid;
		$dataArr['data_city']	=	$data_city;
		$dataArr['ucode']		=	$empcode;
		$dataArr['uname']		=	$empname;
		$dataArr['search']		=	$search_str;
		$dataArr['city']		=	$city_code;
		$dataArr['area']		=	$area_code;
		$dataArr['module']		=	$moduleval;
		$dataArr['action']		=	'streetinfo';
		$curlParams['postData']	=	json_encode($dataArr);		
		
		$curlParams['url'] = $this->genioconfig['jdbox_url'][strtolower($data_city)]."/services/fetchAllDetails.php";				
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlcall	=	Utility::curlCall($curlParams);
		//return $curlcall;		
		$singleCheck_arr  = array();
		if($curlcall){
			$singleCheck_arr	=	json_decode($curlcall,true);
		}
		
		$new_foramtted_arr = array();
		if($singleCheck_arr['data']['numRows']>0){
			$street_data_arr = array();
			$street_data_arr = $singleCheck_arr['data']['result']['street'];
			foreach($street_data_arr as $index=>$area_list_arr){
				$new_foramtted_arr['data']['area_list'][$index]['area']			=	$area_list_arr['entity_area'];
				$new_foramtted_arr['data']['area_list'][$index]['pincode']		=	$area_list_arr['pincode'];
				$new_foramtted_arr['data']['area_list'][$index]['mainarea']		=	$area_list_arr['parent_area'];
				$new_foramtted_arr['data']['area_list'][$index]['display_area']	=	$area_list_arr['areaname_display'];
				$new_foramtted_arr['data']['area_list'][$index]['city']			=	$area_list_arr['city'];
				$new_foramtted_arr['data']['area_list'][$index]['data_city']	=	$area_list_arr['data_city'];
				$new_foramtted_arr['data']['area_list'][$index]['state']		=	$area_list_arr['state'];
				$new_foramtted_arr['data']['area_list'][$index]['zoneid']		=	$area_list_arr['zoneid'];
				$new_foramtted_arr['data']['area_list'][$index]['type_flag']	=	$area_list_arr['type_flag'];
				$new_foramtted_arr['data']['area_list'][$index]['latitude_area']		=	$area_list_arr['latitude_area'];
				$new_foramtted_arr['data']['area_list'][$index]['longitude_area']		=	$area_list_arr['longitude_area'];
				$new_foramtted_arr['data']['area_list'][$index]['longitude_pincode']	=	$area_list_arr['longitude_pincode'];
				$new_foramtted_arr['data']['area_list'][$index]['latitude_final']		=	$area_list_arr['latitude_final'];
				$new_foramtted_arr['data']['area_list'][$index]['longitude_final']		=	$area_list_arr['longitude_final'];
				$new_foramtted_arr['data']['area_list'][$index]['area_len']				=	$area_list_arr['area_len'];				
			}			
			$new_foramtted_arr['errorCode'] 		=	"0";
			$new_foramtted_arr['errorStatus'] 		=	"Data found";	
		}
		else{
			$new_foramtted_arr['errorCode']			=	1;			
			$new_foramtted_arr['errorStatus'] 		=	"Data Not found";
		}		
		return json_encode($new_foramtted_arr);
	}
	
	public function pincode_master(){ // in use
		$retArr 		=	array();
		$urlFlag 		=	$_REQUEST['urlFlag'];
		header('Content-Type: application/json');
		$params			=	json_decode(file_get_contents('php://input'),true);
		$paramsSend		=	array();
		if(!$urlFlag){
			$city 		=	$params['city'];
			$area		=	$params['area'];			
			$pin_auto 	=	$params['pin_auto'];			
			$datacity 	=	$params['data_city'];
			$empcode 	=	$params['empcode'];
		}else{
			$city 		=	$_REQUEST['city'];
			$area		=	$_REQUEST['area'];
			$pin_auto 	=	$_REQUEST['pin_auto'];			
			$datacity 	=	$_REQUEST['data_city'];
			$empcode 	=	$_REQUEST['empcode'];
		}
		$curlParams					= 	array();
		$result						= 	array();
		$paramsSend					= 	array();
		$data_city					=   ((in_array(strtolower($datacity), $this->main_cities)) ? strtolower($datacity) : 'remote');	
		$curlParams['url']			= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/locationinfoApi.php';
		$paramsSend['city']			=	$city;
		$paramsSend['area']			=	$area;
		$paramsSend['pin_auto']		=	$pin_auto;
		$paramsSend['data_city']	=	$datacity;
		$paramsSend['empcode']		=	$empcode;
		$paramsSend['post_data']	= 	"1";		 
		$paramsSend['module']		= 	"TME";		 
		$paramsSend['action']		= 	"pincodemaster"; 
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		=	'post';
		$curlParams['headerJson'] 	=	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend); 
		$result						=	json_decode(Utility::curlCall($curlParams),1);
		return json_encode($result);
	}
	
	public function pincode_master_dialer(){ // in use
		
		$retArr 		=	array();
		$urlFlag 		=	$_REQUEST['urlFlag'];
		header('Content-Type: application/json');
		$params			=	json_decode(file_get_contents('php://input'),true);
		$paramsSend		=	array();
		if(!$urlFlag){
			$pin_auto 	=	$params['pin_auto'];
			$datacity 	=	$params['data_city'];
			$empcode 	=	$params['empcode'];			
		}else{
			$pin_auto 	=	$_REQUEST['pin_auto'];
			$datacity 	=	$_REQUEST['data_city'];			
			$empcode 	=	$_REQUEST['empcode'];			
		}
		$curlParams					= 	array();
		$result						= 	array();
		$paramsSend					= 	array();
		$data_city					=   ((in_array(strtolower($datacity), $this->main_cities)) ? strtolower($datacity) : 'remote');	
		$curlParams['url']			= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/locationinfoApi.php';
		$paramsSend['pin_auto']		=	$pin_auto;
		$paramsSend['data_city']	=	$datacity;
		$paramsSend['empcode']		=	$empcode;
		$paramsSend['post_data']	= 	"1";		 
		$paramsSend['module']		= 	"TME";		 
		$paramsSend['action']		= 	"pincodemasterdialer";
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		=	'post';
		$curlParams['headerJson'] 	=	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend); 
		$result						=	Utility::curlCall($curlParams);
		return json_encode($result);
	}
	
	public function get_area($city_name='',$srch_string=''){ // in use
		header('Content-Type: application/json');
		$urlFlag 		=	$_REQUEST['urlFlag'];
		$params			=	array();
		$retArr_area 	=	array();
		$params			=	json_decode(file_get_contents('php://input'),true);
		if(!$urlFlag){
			if(empty($params['city_name'])){
				$retArr_area['errorCode'] 		=	1;
				$retArr_area['errorStatus'] 	=	"City name Not found";
				$retArr_area 	= 	json_encode($retArr_area)	;
				return ($retArr_area);
			}else{
				$city_name		= $params['city_name'];
				$srch_string    = $params['srch_string'];
				$datacity      = $params['data_city'];
				$empcode    	= $params['empcode'];
			}
		}else{
			if(empty($_REQUEST['city_name'])){
				$retArr_area['errorCode'] 		=	1;
				$retArr_area['errorStatus'] 	=	"City name Not found";
				$retArr_area 	= 	json_encode($retArr_area)	;
				return ($retArr_area);
			}else{
				$city_name 		=	$_REQUEST['city_name'];
				$srch_string 	= 	$_REQUEST['srch_string'];
				$datacity      = 	$_REQUEST['data_city'];
				$empcode    	= 	$_REQUEST['empcode'];
			}
		}
		$curlParams					= 	array();
		$result						= 	array();
		$paramsSend					= 	array();
		$data_city					=   ((in_array(strtolower($datacity), $this->main_cities)) ? strtolower($datacity) : 'remote');	
		$curlParams['url']			= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/locationinfoApi.php';
		$paramsSend['pin_auto']		=	$pin_auto;
		$paramsSend['data_city']	=	$datacity;
		$paramsSend['city_name']	=	$city_name;
		$paramsSend['srch_string']	=	$srch_string;
		$paramsSend['empcode']		=	$empcode;
		$paramsSend['post_data']	= 	"1";		 
		$paramsSend['module']		= 	"TME";		 
		$paramsSend['action']		= 	"getarea";
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		=	'post';
		$curlParams['headerJson'] 	=	'json';
		$curlParams['postData'] 	= 	json_encode($paramsSend);
		$result						=	json_decode(Utility::curlCall($curlParams),1);
		return json_encode($result);
	}
	
	public function EcsRequestStatusCheck(){ // in use
		$curlParams = array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/locationinfoApi.php';
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"EcsRequestStatusCheck";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($result);
	}
	
	public function unsold_inventoryData(){ // not in use
		$result['errorCode']	=	1;
		$result['errorStatus']	=	'NOT IN USE';
		return json_encode($result);
	}
	
	public function fetchBudgetInfo($parentid){ // not in use
		$returnArr['errorCode']		=	1;
		$returnArr['errorStatus']	=	'NOT IN USE';
		return json_encode($returnArr);
	}

	public function getAssocCatSeries() { // not in use
		$respArr['errorCode']	=	1;
		$respArr['errorMsg']	=	"NOT IN USE";
		return json_encode($respArr);
	}
	
	public function get_lat_long($pincode){ // not in use
		$retArr['errorCode'] 		=	1;
		$retArr['errorStatus'] 		=	"NOT IN USE";
		$retArr 					=	json_encode($retArr);
		echo $retArr;
	}

	public function stdcode_master(){ // not in use 
		$retArr['errorCode'] 		=	1;
		$retArr['errorStatus'] 		=	"NOT IN USE";
		$retArr 	=	json_encode($retArr);
		echo $retArr;
	}
}
?>
