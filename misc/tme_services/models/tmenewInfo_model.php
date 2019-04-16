<?php
class TmenewInfo_Model extends Model {
	private $limitVal	=	50;
	public function __construct() {
		parent::__construct();
		$this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
	}
	
	
		public function getSSOInfo() {
				if(isset($_REQUEST['urlFlag'])){
                        $params =       array_merge($_POST,$_GET);
                }else{
                        header('Content-Type: application/json');
                        $params =       json_decode(file_get_contents('php://input'),true);
                }
                $paramsArr			=	array();
				$retValemp			=	array();				
				$postArrayempinfo	=	array();
				$data_city						=  ((in_array(strtolower($params['server_city']), $this->main_cities)) ? strtolower($params['server_city']) : 'remote');	
				$url 							= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
				if($data_city	==	'remote'){
					$postArrayempinfo['remote_zone'] =   REMOTEZONEFLAG;
				}
				$postArrayempinfo['post_data']  = 	"1";           
				$postArrayempinfo['module']     = 	"TME";
				$postArrayempinfo['data_city']  = 	$data_city; 
				$postArrayempinfo['empcode']    =	$params['empcode']; 
				$postArrayempinfo['action']		=  "getSSOInfo";
				$paramsArr                      =   array('url' => $url, 'method' => 'post','postData' => json_encode($postArrayempinfo),'formate' => 'basic','headerJson' => 'json');
				$retValemp 						= 	json_decode(Utility::curlCall($paramsArr),true);
				if((count($retValemp)>0) && ($retValemp['errorcode'] == 0)){
					if(strtolower($retValemp['data'][0]['type_of_employee']) == 'tme' || strtolower($retValemp['data'][0]['type_of_employee']) == ''){
						$retValemp['empType']                 		= 5;
					}else if(strtolower($retValemp['data'][0]['type_of_employee']) == 'me'){
						$retValemp['empType']                 		= 3;
					}else if(strtolower($retValemp['data'][0]['type_of_employee']) == 'jda'){
						$retValemp['empType']                 		= 13;
					}
					$retValemp['allocid']			=	$retValemp['data'][0]['team_type'];
					$retValemp['S_EmpParentid']		=	$retValemp['data'][0]['reporting_head_code'];
					$retValemp['S_UserName']  		= 	$retValemp['data'][0]['empname'];
					$retValemp["uname"]       		= 	$retValemp['data'][0]['empname'];
					$retValemp["ucode"]      		= 	$retValemp['data'][0]['empcode'];
					$retValemp["empcode"]      		= 	$retValemp['data'][0]['empcode'];
					$retValemp['mktgEmpCode'] 		= 	$retValemp['data'][0]['empcode'];
					
					$retValemp['tme_mobile'] 		= 	$retValemp['data'][0]['mobile_num'];
					$retValemp['status']     		= 	$retValemp['data'][0]['status'];
					$retValemp['ipaddress']         = 	$_SERVER['REMOTE_ADDR'];
					$result                      	= 	array();
					$curlParams                     = 	array();
					$url                            = $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';
					if($data_city	==	'remote'){
						$postArray['remote_zone'] =   REMOTEZONEFLAG;
					}
					$postArray['empcode']           = trim($params['empcode']);           
					$postArray['empName']           = $retValemp['data'][0]['empname'];   
					$postArray['empParent']         = $retValemp['data'][0]['reporting_head_code'];        
					$postArray['remoteAddr']        = $_SERVER['REMOTE_ADDR'];
					$postArray['post_data']         = "1";           
					$postArray['module']            = "TME";
					$postArray['remoteAddr']        = $_SERVER['REMOTE_ADDR'];
					$postArray['data_city']         = $data_city;
					$postArray['user_city']         = ((in_array(strtolower($params['server_city']), $this->main_cities)) ? strtolower($params['server_city']) : $retValemp['data'][0]['city']);
					$postArray['action']            = "cityInfo";   
					$dataParam                      = array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
					$result                         = json_decode(Utility::curlCall($dataParam),true);
					$pageno  						= "1";
					if($params['StationId']!=''){
						if($data_city == 'remote'){
							$remote_city	=	'remote_city';
							$header = "ctiMainPage.php?Pageno=".$pageno."&city=".$params['loginCity'];
						}else{
							$header = "ctiMainPage.php?Pageno=".$pageno;
						}
					}else{
						$header = "MainPage.php?Pageno=".$pageno;
					}
					$retValemp['mktgEmpRowId'] 			= $result['empRowId'];
					if(($result['mktgEmp']['tmeClass'] == 4) && (!$retValemp['mktgEmpRowId']))
						$retValemp['mktgEmpRowId'] = "O_".$retValemp["ucode"];
					$retValemp['s_deptCountry_id'] 		= $result['CityInfo']['country_id'];
					$retValemp['s_deptCountry'] 		= $result['CityInfo']['country_name'];
					$retValemp['s_deptState_id'] 		= $result['CityInfo']['state_id'];
					$retValemp['s_deptState'] 			= $result['CityInfo']['state_name'];
					$retValemp['s_deptCity_id'] 		= $result['CityInfo']['city_id'];
					$retValemp['s_deptCity'] 			= $result['CityInfo']['ct_name'];
					$retValemp['extn'] 					= $result['mktgEmp']['extn'];
					$retValemp['secondary_allocID'] 	= $result['mktgEmp']['secondary_allocID'];
					$retValemp['tmeClass'] 				= $result['mktgEmp']['tmeClass'];
					$retValemp['mktgEmpCls']  			= $result['mktgEmp']['tmeClass'];
					$retValemp['level'] 				= $result['mktgEmp']['level'];
					$retValemp['state'] 				= $result['mktgEmp']['state'];
					$retValemp['time_slot'] 			= $result['time_slot'];
					$retValemp['header']				= $header;
					$retValemp['REMOTE_CITY_MODULE']	= $remote_city;	
				}else{
					$retValemp['errorcode']		= $retValemp['errorcode'];	
					$retValemp['errorstatus']	= $retValemp['reportStatus'];	
				}
				return  json_encode($retValemp);
        }
        
        
         public function miniBformload(){
                $retArr             =       array();
                if(isset($_REQUEST['urlFlag'])){
                      $params =       array_merge($_POST,$_GET);
                }else{
                      header('Content-Type: application/json');
                      $params =       json_decode(file_get_contents('php://input'),true);
                }
                $resultArr                      	= array();
                $curlParams                     	= array();
                $data_city            				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
                $url                            	=   $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/miniBform.php';
                if($data_city	==	'remote'){
					$postArray['remote_zone'] =   REMOTEZONEFLAG;
				}
                $postArray['empcode'] 				= 	$params['empcode'];		 
				$postArray['bname'] 				= 	$params['bname'];
				$postArray['flagNew'] 				= 	$params['flgNew'];
				$postArray['city_db_dropdown'] 		= 	$params['city_db_dropdown'];
				$postArray['mongo_data_city'] 		= 	$params['mongo_data_city'];
				$postArray['parentid'] 				= 	$params['parentid'];
				$postArray['City_onload'] 			= 	$params['City_onload'];
				$postArray['noparentid'] 			= 	1;
				$postArray['mongo_user'] 			= 	$params['mongo_user'];
                $postArray['data_city']        	 	= 	$params['data_city'];    
				$postArray['navibar'] 				= 	$params['navibar'];
				$postArray['post_data']				= 	"1";		 
				$postArray['module']				= 	"TME";		 
				$postArray['action']				= 	"miniBformload";       
                $params['formate']              	= 	'basic';
                $dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
                $result                         	= 	Utility::curlCall($dataParam);
                //~ echo '---pp---res----<pre>';print_r($result);
                return $result;
        }
        
        public function insertshadowdetails(){
			$retArr             =       array();
			if(isset($_REQUEST['urlFlag'])){
				  $params =       array_merge($_POST,$_GET);
			}else{
				  header('Content-Type: application/json');
				  $params =       json_decode(file_get_contents('php://input'),true);
			}
			$resultArr                      	= array();
			$curlParams                     	= array();
			$data_city            				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/miniBform.php';
			if($data_city	==	'remote'){
				$postArray['remote_zone'] =   REMOTEZONEFLAG;
			}
			$postArray['sphinx_id'] 			= 	$params['sphinx_id'];		 
			$postArray['companyname'] 			= 	$params['companyname'];
			$postArray['parentid'] 				= 	$params['parentid'];
			$postArray['pincode'] 				= 	$params['pincode'];
			$postArray['stdcode'] 				= 	$params['stdcode'];
			$postArray['landline'] 				= 	$params['landline'];
			$postArray['mobile'] 				= 	$params['mobile'];
			$postArray['empcode'] 				= 	$params['empcode'];
			$postArray['empname'] 				= 	$params['empname'];
			$postArray['data_city'] 			= 	$params['data_city'];
			$postArray['businesshid'] 			= 	$params['businesshid'];
			$postArray['Stationid'] 			= 	$params['Stationid'];
			$postArray['edat'] 					= 	$params['edat'];
			$postArray['post_data']				= 	"1";		 
			$postArray['module']				= 	"TME";		 
			$postArray['action']				= 	"insertshadowdetails";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
			$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
			return json_encode($result);
        }
        
        public function fetchAllocEmpDetails(){
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
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/miniBform.php';
			if($data_city	==	'remote'){
				$postArray['remote_zone'] =   REMOTEZONEFLAG;
			}
			$postArray['parentid'] 				= 	$params['parentid'];
			$postArray['empcode'] 				= 	$params['empcode'];
			$postArray['empname'] 				= 	$params['empname'];
			$postArray['data_city'] 			= 	$params['data_city'];
			$postArray['post_data']				= 	"1";		 
			$postArray['module']				= 	"TME";		 
			$postArray['action']				= 	"fetchAllocEmpDetails";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
			$result                         	= 	json_decode(Utility::curlCall($dataParam),1);
			return json_encode($result);
        }

	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/get_tmeInfo?urlFlag=1&empcode=10018317&data_city=mumbai&action=EmpInfo&module=TME
	public function tmeInfo() {
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
        $paramsSend['action']		= 'EmpInfo';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=EmpInfo&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        $singleCheck['remoteAddr']  = $_SERVER['REMOTE_ADDR'];
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getMenuLinks/?urlFlag=1&empcode=10018317&data_city=mumbai&action=MenuLinks&module=TME
	public function fetchMenuLinks() {
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
        $paramsSend['action']		= 'MenuLinks';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=MenuLinks&module=TME&post_data=1&allocid='.$params['allocid'].'&secondaryid='.$params['secondaryid'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getLineage/?urlFlag=1&empcode=10018317&data_city=mumbai&action=getLineage&module=TME
	public function getLineage() {
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
        $paramsSend['action']		= 'getLineage';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=getLineage&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/checkUpdatedOn/?urlFlag=1&empcode=10018317&data_city=mumbai&action=checkUpdatedOn&module=TME
	public function checkUpdatedOn() {
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
        $paramsSend['action']		= 'checkUpdatedOn';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=checkUpdatedOn&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/insertlineage/?urlFlag=1&empname=hema&reporting_head_code=018916&reporting_head_name=sumesh&empcode=10018308&city=mumbai&city_type=1&mobile_num=7899660889&off_sales=1&teamname=BD&otp=098789&data_city=mumbai&action=insertlineage&module=TME
	public function insertlineage() {
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
        $paramsSend['action']		= 'insertlineage';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.urlencode($params['data_city']).'&action=insertlineage&module=TME&post_data=1&empname='.urlencode($params['empname']).'&reporting_head_code='.$params['reporting_head_code'].'&city='.urlencode($params['city']).'&city_type='.urlencode($params['city_type']).'&mobile_num='.$params['mobile_num'].'&off_sales='.$params['off_sales'].'&teamname='.urlencode($params['teamname']).'&otp='.$params['otp'].'&reporting_head_name='.urlencode($params['reporting_head_name']).'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $curlParams['method']   	= 'post';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchreportees/?urlFlag=1&empcode=10018318&data_city=mumbai&action=fetchreportees&module=TME
	public function fetchreportees() {
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
        $paramsSend['action']		= 'fetchreportees';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=fetchreportees&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/accetRejectRequest/?urlFlag=1&empcode=10018318&data_city=mumbai&action=accetRejectRequest&module=TME&status=1&confirmed=confirmed&reporting_head_code=018916&reportee=10018318
	public function accetRejectRequest() {
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
        $paramsSend['action']		= 'accetRejectRequest';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=accetRejectRequest&module=TME&post_data=1&status='.$params['status'].'&confirmed='.$params['confirmed'].'&reporting_head_code='.$params['reporting_head_code'].'&reportee='.$params['reportee'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/insertReportDetails/?urlFlag=1&empcode=10018318&data_city=mumbai&action=insertReportDetails&module=TME&empname=hema
	public function insertReportDetails() {
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
        $paramsSend['action']		= 'insertReportDetails';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=insertReportDetails&module=TME&post_data=1&empname='.$params['empname'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/sendOTP/?urlFlag=1&empcode=10018318&data_city=mumbai&action=sendOTP&module=TME&otp=889977&managercode=018916&mobno=7899660880
	public function sendOTP() {
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
        $paramsSend['action']		= 'sendOTP';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=sendOTP&module=TME&post_data=1&otp='.$params['otp'].'&managercode='.$params['managercode'].'&mobno='.$params['mobno'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/checkOTP/?urlFlag=1&empcode=10018318&data_city=mumbai&action=checkOTP&module=TME&managercode=018916
	public function checkOTP() {
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
        $paramsSend['action']		= 'checkOTP';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=checkOTP&module=TME&post_data=1&managercode='.$params['managercode'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}	
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/countRequest/?urlFlag=1&empcode=10018318&data_city=mumbai&action=countRequest&module=TME
	public function countRequest() {
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
        $paramsSend['action']		= 'countRequest';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=countRequest&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/insertPenaltyUpdatedOn/?urlFlag=1&empcode=10018318&data_city=mumbai&action=insertPenaltyUpdatedOn&module=TME
	public function insertPenaltyUpdatedOn() {
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
        $paramsSend['action']		= 'insertPenaltyUpdatedOn';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=insertPenaltyUpdatedOn&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getcitylist/?urlFlag=1&data_city=mumbai&action=getcitylist&module=TME&srchData=a
	public function getcitylist() {
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
        $paramsSend['action']		= 'getcitylist';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?data_city='.$params['data_city'].'&action=getcitylist&module=TME&post_data=1&srchData='.$params['srchData'].'&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getHotData?urlFlag=1&empcode=10026425&data_city=mumbai&action=HotData&post_data=1&module=tme
	public function getHotData() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}		                 
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=HotData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);  
		return json_encode($singleCheck); 		 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchNewBusiness?urlFlag=1&empcode=10026425&data_city=mumbai&action=newBusiness&post_data=1&module=tme
	public function fetchNewBusiness() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=newBusiness&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		if($paramsGET['trace'] == 1){
			$debug_arr = array();
			$debug_arr['url'] = $curlParams['url'];
			return json_encode($debug_arr);
		}
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 		
	}
	
	public function fetchrestaurantdealsoffer() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=fetchrestaurantdealsoffer&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 		
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchRestaurantData?urlFlag=1&empcode=10026425&data_city=mumbai&action=RestaurantData&post_data=1&module=tme
	public function fetchRestaurantData() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}		
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=RestaurantData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 		
		return json_encode($singleCheck); 	
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchEcsData?urlFlag=1&empcode=10026425&data_city=mumbai&action=EcsData&post_data=1&module=tme
	public function fetchEcsData() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}		     
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=EcsData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 	
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchExpiredData?urlFlag=1&empcode=10026425&data_city=mumbai&action=ExpiredData&post_data=1&module=tme
	public function fetchExpiredData(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}        
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ExpiredData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchProspectData?urlFlag=1&empcode=10026425&data_city=mumbai&action=ProspectData&post_data=1&module=tme
	public function fetchProspectData(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}     
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ProspectData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchSpecialData?urlFlag=1&empcode=10026425&data_city=mumbai&action=SpecialData&post_data=1&module=tme
	public function fetchSpecialData(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}       
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=SpecialData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 
	}	
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/accountDetRest?urlFlag=1&action=accountDetRest&empcode=10026425&module=TME&data_city=mumbai&post_data=1
	public function accountDetRest() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}          
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=accountDetRest&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);		
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/gettmeAllocData?urlFlag=1&action=tmeAllocData&empcode=10026425&module=TME&data_city=mumbai&post_data=1
	public function fetchtmeAllocData(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}         
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=tmeAllocData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.$paramsGET['srchparam'].'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchReversedRetentionData?urlFlag=1&action=reversedRetentionData&empcode=10026425&module=TME&data_city=mumbai&post_data=1
	public function fetchReversedRetentionData(){	
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}  
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=reversedRetentionData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.$paramsGET['srchwhich'].'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);		
		return json_encode($singleCheck); 	
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchNonecsData?urlFlag=1&action=NonEcsData&empcode=10026425&module=TME&data_city=mumbai&post_data=1
	public function fetchNonecsData(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}     
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=NonEcsData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);		
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchunsoldData?urlFlag=1&empcode=10026425&data_city=mumbai&action=unsoldData&post_data=1&module=tme
	public function fetchunsoldData(){ 		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');	       
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=unsoldData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);  
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchExpiredDataEcs?urlFlag=1&empcode=10026425&data_city=mumbai&action=ExpiredEcsData&post_data=1&module=tme
	public function fetchExpiredDataEcs(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}  
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ExpiredEcsData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchExpiredDataNonEcs?urlFlag=1&empcode=10026425&data_city=mumbai&action=ExpiredNonEcsData&post_data=1&module=tme
	public function fetchExpiredDataNonEcs(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}     
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ExpiredNonEcsData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchEcsRequestData?urlFlag=1&empcode=10026425&data_city=mumbai&action=ecsRequestData&post_data=1&module=tme
	public function fetchEcsRequestData(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);;
		}      
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ecsRequestData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck);
	}	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchdeliverySystem?urlFlag=1&empcode=10026425&data_city=mumbai&action=deliverySystem&post_data=1&module=tme
	public function fetchdeliverySystem(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=deliverySystem&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']   = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchleadComplaints?urlFlag=1&empcode=10026425&data_city=mumbai&action=leadComplaints&post_data=1&module=tme
	public function fetchleadComplaints(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}       
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');   
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=leadComplaints&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&srchData='.urlencode($paramsGET['srchData']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['headerJson']   = 'json';
		$curlParams['method']   = 'post';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchjdrrPropectData?urlFlag=1&empcode=10026425&data_city=mumbai&action=jdrrPropectData&post_data=1&module=tme
	public function fetchjdrrPropectData(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=jdrrPropectData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 		
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchReportData?urlFlag=1&empcode=10026425&data_city=mumbai&action=ReportData&post_data=1&module=tme&extraVals=167&pageShow=&srchwhich=&srchparam=
	public function fetchReports() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ReportData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&extraVals='.$paramsGET['extraVals'].'&post_data=1&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 		
		return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getAllocatedContracts?urlFlag=1&empcode=10026425&data_city=mumbai&action=AllocContracts&post_data=1&module=tme&pageShow=&srchwhich=&srchparam=
	public function fetchAllocContracts() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		
		if($paramsGET['campaign_sr']==1)
		{
			$search_param_campaign = explode(',',$paramsGET['srchparam']);
			foreach($search_param_campaign as $key=>$val)
			{
						switch($val) {
							case 'ALL' :
								$srchprm_cmpgn	=	'0';
							break;
							case 'PDG' :
								$srchprm_cmpgn	=	'2';
							break;
							case 'Package' :
								$srchprm_cmpgn	=	'1';
							break;
							case 'H2L' :
								$srchprm_cmpgn	=	'1';
							break;
							case 'LPPPackage' :
								$srchprm_cmpgn	=	'1';
							break;
							case 'JDRR2019' :
								$srchprm_cmpgn	=	'22';
							break;
						}
				$srchprm_cmpgn_ar[] =$srchprm_cmpgn;
				
			}
			$paramsGET['srchparam'] = implode('|',$srchprm_cmpgn_ar);
		}else
		{
			$paramsGET['srchparam'] = $paramsGET['srchparam'];
		}
		
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=AllocContracts&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&srchData='.urlencode($paramsGET['srchData']).'&pageShow='.$paramsGET['pageShow'].'&city='.urlencode($paramsGET['city']).'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone.'&campaign_srch='.$paramsGET['campaign_sr'];
		$curlParams['formate']      = 'basic';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);  
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchPackgaeData?urlFlag=1&empcode=10026425&data_city=mumbai&action=PackgaeData&post_data=1&module=tme&pageShow=&srchwhich=&srchparam=
	public function fetchPackgaeData() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}		 
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');         
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=PackgaeData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&srchData='.urlencode($paramsGET['srchData']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['method']   = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);  
		return json_encode($singleCheck); 
	}
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchRetentionData_information?urlFlag=1&empcode=10026425&data_city=mumbai&action=RetentionData_info&post_data=1&module=tme&pageShow=&srchwhich=&srchparam=&srchData=parentid=
	public function fetchRetentionData_information(){ 
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}          
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=RetentionData_info&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&lead='.$paramsGET['lead'].'&cname='.urlencode($paramsGET['companyname']).'&parentid='.$paramsGET['srchData'].'&srchwhich='.$paramsGET['srchwhich'].'&srchparam='.urlencode($paramsGET['srchparam']).'&tmename='.$paramsGET['tmename'].'&post_data=1&remote_zone='.$remote_zone;	
		$curlParams['formate']      = 'basic';
		$curlParams['method']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck);
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchRetentionData?urlFlag=1&empcode=10026425&data_city=mumbai&action=retentionData&post_data=1&module=tme&pageShow=&srchwhich=&srchparam=
	public function fetchRetentionData(){		
		header('Content-Type: application/json');
		
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}       
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');   
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=retentionData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&lead='.$paramsGET['lead'].'&cname='.urlencode($paramsGET['companyname']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.$paramsGET['srchwhich'].'&srchparam='.urlencode($paramsGET['srchparam']).'&parentid='.$paramsGET['parid'].'&tmename='.$paramsGET['tmename'].'&post_data=1&remote_zone='.$remote_zone;	
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}	
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/fetchJDRatingData?urlFlag=1&empcode=10026425&data_city=mumbai&action=JDRatingData&post_data=1&module=tme&pageShow=&srchwhich=&srchparam=&parentid=
	public function fetchJDRatingData() {
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}     
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');     
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=JDRatingData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&lead='.$paramsGET['lead'].'&cname='.urlencode($paramsGET['companyname']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.$paramsGET['srchwhich'].'&srchparam='.urlencode($paramsGET['srchparam']).'&parentid='.$paramsGET['parid'].'&tmename='.$paramsGET['tmename'].'&post_data=1&remote_zone='.$remote_zone;	
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 
	}
	
	//http://tmegeniocategory.jdsoftware.com/tmegenio/tme_services/tmenewInfo/getCallBackData?urlFlag=1&action=2&empcode=10026425&module=TME&data_city=mumbai&post_data=1
	function getCallBackData() {		
		$resultArr  =   array();
        $curlParams =   array();
		header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);;
		}
		$paramsGET['data_city'] 	= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=2&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&post_data=1&remote_zone='.$remote_zone;	
		$curlParams['formate']      = 'basic';
		$curlParams['method']       = 'post';
		$curlParams['headerJson']   = 'json';
		$curlParams['postData']     = json_encode($paramsSend);
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);        
		return json_encode($singleCheck); 
		 		
	}
	//http://172.29.0.217:1010/services/tmenewServices.php?action=magazineData&empcode=10047534&module=TME&data_city=remote&srchparam=companyname&srchData=&srchwhich=order-desc&pageShow=0&parentid=&post_data=1
    public function fetchMagazineData(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=magazineData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	//http://172.29.0.217:1010/services/tmenewServices.php?action=empDeclaration&empcode=10047534&module=TME&data_city=remote&srchparam=companyname&srchData=&srchwhich=order-desc&pageShow=0&parentid=&post_data=1
	 public function checkemployeedeclaration(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=empDeclaration&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&post_data=1&remote_zone='.$remote_zone;        
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	public function storeemp() {
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=storemp&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.$paramsGET['data_city'].'&post_data=1&remote_zone='.$remote_zone;        
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        //$singleCheck                = Utility::curlCall($curlParams);
        //echo "<pre>singleCheck:==";print_r($singleCheck);
        return json_encode($singleCheck);
	}
	//http://172.29.0.217:1010/services/tmenewServices.php?action=getlineagealldata&empcode=10047534&module=TME&data_city=pune&enddate=&startdate=&pageShow=&post_data=1&urlFlag=1
	 public function getlineagealldata(){
		 // startdate, enddate , employee , city , pageShow ,
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);
        }
        $data_city     = ((in_array(strtolower($paramsGET['city']), $this->main_cities)) ? strtolower($paramsGET['city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']          = 'http://172.29.0.217:1010/services/tmenewServices.php?action=getlineagealldata&empcode='.$paramsGET['employee'].'&module=TME&data_city='.$paramsGET['city'].'&post_data=1&enddate='.$paramsGET['enddate'].'&startdate='.$paramsGET['startdate'].'&pageShow='.$paramsGET['pageShow'].'&remote_zone='.$remote_zone;        
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	 
	public function fetchPaidExpiredVNData($empcode){ //not in use
		$result_arr = array();		
		$result_arr['errorCode'] = 1;
		$result_arr['errorMsg']  = 'Not In Use';
		
		return json_encode($result_arr);
	}
	
	public function fetchDeactivateRestaurantData(){ //not in use
		$result_arr = array();	
		$result_arr['errorCode'] = 1;
		$result_arr['errorMsg']  = 'Not In Use';		
		return json_encode($result_arr);
	}
	
	public function fetchChainRestuarantData(){ //not in use
		$result_arr = array();
		header('Content-Type: application/json');
		$params		=	array_merge($_GET,$_POST);
		$paramsGET	=	json_decode(file_get_contents('php://input'),true);				
		$result_arr['errorCode'] = 1;
		$result_arr['errorMsg']  = 'Not In Use';		
		return json_encode($result_arr);
	}
	//http://saritapc.jdsoftware.com/jdbox/services/tmenewServices.php?action=bounceData&empcode=10026425&module=TME&data_city=pune&enddate=&startdate=&pageShow=&post_data=1&urlFlag=1
	public function fetchBounceData(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=bounceData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	public function JdrIro(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		 $curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=JdrIro&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
      
        return json_encode($singleCheck);
	}
	
	public function WebIro(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		 $curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'/services/tmenewServices.php?action=WebIro&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
      
        return json_encode($singleCheck);
	}
	
	
	public function whatsappcalled(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		 $curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'/services/tmenewServices.php?action=whatsappcalled&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
      
        return json_encode($singleCheck);
	}
	
	//http://saritapc.jdsoftware.com/jdbox/services/tmenewServices.php?action=bounceECSData&empcode=10026425&module=TME&data_city=pune&enddate=&startdate=&pageShow=&post_data=1&urlFlag=1
	public function fetchBounceECSData(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=bounceECSData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	//http://saritapc.jdsoftware.com/jdbox/services/tmenewServices.php?action=instantEcsData&empcode=10026425&module=TME&data_city=pune&enddate=&startdate=&pageShow=&post_data=1&urlFlag=1
	public function fetchInstantECSData(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=instantEcsData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);        
        return json_encode($singleCheck);
	}
	
	public function fetchDealCloseDataReport(){ //Not In Use
		$result_arr = array();			
		$result_arr['errorCode'] = 1;
		$result_arr['errorMsg']  = 'Not In Use';		
		return json_encode($result_arr);
	}
	//http://saritapc.jdsoftware.com/jdbox/services/tmenewServices.php?action=CourierData&empcode=10026425&module=TME&data_city=pune&enddate=&startdate=&pageShow=&post_data=1&urlFlag=1
	public function fetchjdrrCourierData(){
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }
              
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=CourierData&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchData='.urlencode($paramsGET['srchData']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);                        
        
        return json_encode($singleCheck);
	}
	
	public function companyAutoSuggest() {
		header('Content-Type: application/json');
		$params		=	array_merge($_GET,$_POST);
		$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		$paramsGET['urlFlag']  = 1;
		
		$retArr	=	array();
		$retValAlloc	=	json_decode($this->fetchAllocContracts($paramsGET),true);
		if($retValAlloc['errorCode']	==	'0') {
			$i =	0;
			foreach($retValAlloc['data'] as $key=>$value) {
				$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
				$i++;
			}
			$retArr['fromSet']	=	'allocation';
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data returned Successfully';
		} else {
			$retValHotData	=	json_decode($this->getHotData($paramsGET),true);
			if($retValHotData['errorCode']	==	'0') {
				$i=	0;
				foreach($retValHotData['data'] as $key=>$value) {
					$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
					$i++;
				}
				$retArr['fromSet']	=	'hotData';
				$retArr['errorCode']	=	'0';
				$retArr['errorStatus']	=	'Data returned Successfully';
			} else {
				$retValNewBusi	=	json_decode($this->fetchNewBusiness($paramsGET),true);
				if($retValNewBusi['errorCode']	==	'0') {
					$i=	0;
					foreach($retValNewBusi['data'] as $key=>$value) {
						$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
						$i++;
					}
					$retArr['fromSet']	=	'newBusiness';
					$retArr['errorCode']	=	'0';
					$retArr['errorStatus']	=	'Data returned Successfully';
				} else {
					$retRetData	=	json_decode($this->fetchRetentionData($paramsGET),true);
					if($retRetData['errorCode']	==	'0') {
						$i=	0;
						foreach($retRetData['data'] as $key=>$value) {
							$retArr['data'][ucwords(strtolower($value['companyName']))]['contractid'][]	=	$value['contractid'];
							$i++;
						}
						$retArr['fromSet']	=	'retentionData';
						$retArr['errorCode']	=	'0';
						$retArr['errorStatus']	=	'Data returned Successfully';
					} else {
						$prospectData	=	json_decode($this->fetchProspectData($paramsGET),true);
						if($prospectData['errorCode']	==	'0') {
							$i=	0;
							foreach($prospectData['data'] as $key=>$value) {
								$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
								$i++;
							}
							$retArr['fromSet']	=	'prospectData';
							$retArr['errorCode']	=	'0';
							$retArr['errorStatus']	=	'Data returned Successfully';
						} else {
							$jdratingData	=	json_decode($this->fetchJDRatingData($paramsGET),true);
							if($jdratingData['errorCode']	==	'0') {
								$i=	0;
								foreach($jdratingData['data'] as $key=>$value) {
									$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
									$i++;
								}
								$retArr['fromSet']	=	'jdRatingData';
								$retArr['errorCode']	=	'0';
								$retArr['errorStatus']	=	'Data returned Successfully';
							} else {
								//$jdratingData	=	json_decode($this->fetchPaidExpiredVNData($paramsGET['empcode']),true); //not in use
								$jdratingData['errorCode']     = 1;
								if($jdratingData['errorCode']	==	'0') {
									$i=	0;
									foreach($jdratingData['data'] as $key=>$value) {
										$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
										$i++;
									}
									$retArr['fromSet']	=	'paidExpVN';
									$retArr['errorCode']	=	'0';
									$retArr['errorStatus']	=	'Data returned Successfully';
								} else {
									$ecsData	=	json_decode($this->fetchEcsData($paramsGET),true);
									if($ecsData['errorCode']	==	'0') {
										$i=	0;
										foreach($ecsData['data'] as $key=>$value) {
											$retArr['data'][ucwords(strtolower($value['compname']))]['contractid'][]	=	$value['contractid'];
											$i++;
										}
										$retArr['fromSet']	=	'workedECSData';
										$retArr['errorCode']	=	'0';
										$retArr['errorStatus']	=	'Data returned Successfully';
									} else {
										$ecsData	=	json_decode($this->fetchRestaurantData($paramsGET),true);
										if($ecsData['errorCode']	==	'0') {
											$i=	0;
											foreach($ecsData['data'] as $key=>$value) {
												$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
												$i++;
											}
											$retArr['fromSet']	=	'restaurantData';
											$retArr['errorCode']	=	'0';
											$retArr['errorStatus']	=	'Data returned Successfully';
										} else {
											//$ecsData	=	json_decode($this->fetchDeactivateRestaurantData($paramsGET['empcode']),true);  //not in use
											$ecsData['errorCode']	=	'1';
											if($ecsData['errorCode']	==	'0') {
												$i=	0;
												foreach($ecsData['data'] as $key=>$value) {
													$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
													$i++;
												}
												$retArr['fromSet']	=	'deactivatedRest';
												$retArr['errorCode']	=	'0';
												$retArr['errorStatus']	=	'Data returned Successfully';
											} else {
												//~ $ecsData	=	json_decode($this->fetchExpiredData($paramsGET['empcode']),true);
												//~ if($ecsData['errorCode']	==	'0') {
													//~ $i=	0;
													//~ foreach($ecsData['data'] as $key=>$value) {
														//~ $retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
														//~ $i++;
													//~ }
													//~ $retArr['fromSet']	=	'expiredData';
													//~ $retArr['errorCode']	=	'0';
													//~ $retArr['errorStatus']	=	'Data returned Successfully';
												//~ } else {
													//$ecsData	=	json_decode($this->fetchChainRestuarantData($paramsGET['empcode']),true); //not in use
													$ecsData['errorCode']      = 1;
													if($ecsData['errorCode']	==	'0') {
														$i=	0;
														foreach($ecsData['data'] as $key=>$value) {
															$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
															$i++;
														}
														$retArr['fromSet']	=	'chainRest';
														$retArr['errorCode']	=	'0';
														$retArr['errorStatus']	=	'Data returned Successfully';
													} else {
														$retValfetchBounceData	=	json_decode($this->fetchBounceData($paramsGET),true);
														if($retValfetchBounceData['errorCode']	==	'0') {
															$i=	0;
															foreach($retValfetchBounceData['data'] as $key=>$value) {
																$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
																$i++;
															}
															$retArr['fromSet']		=	'bouncedData';
															$retArr['errorCode']	=	'0';
															$retArr['errorStatus']	=	'Data returned Successfully';
														} else{
															$retValfetchBounceECSData	=	json_decode($this->fetchBounceECSData($paramsGET['empcode']),true);
															if($retValfetchBounceECSData['errorCode']	==	'0') {
																$i=	0;
																foreach($retValfetchBounceECSData['data'] as $key=>$value) {
																	$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
																	$i++;
																}
																$retArr['fromSet']		=	'bouncedDataECS';
																$retArr['errorCode']	=	'0';
																$retArr['errorStatus']	=	'Data returned Successfully';
															} else {
																$retValInstantECSData	=	json_decode($this->fetchInstantECSData($paramsGET),true);
																if($retValInstantECSData['errorCode']	==	'0') {
																	$i=	0;
																	foreach($retValInstantECSData['data'] as $key=>$value) {
																		$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
																		$i++;
																	}
																	$retArr['fromSet']		=	'instantECS';
																	$retArr['errorCode']	=	'0';
																	$retArr['errorStatus']	=	'Data returned Successfully';
																} else {
																	/*$retValfetchSpecialData	=	json_decode($this->fetchSpecialData($paramsGET['empcode']),true);*/
																	$retValfetchSpecialData['errorCode'] = 1;
																	if($retValfetchSpecialData['errorCode']	==	'0') {
																		$i=	0;
																		foreach($retValfetchSpecialData['data'] as $key=>$value) {
																			$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
																			$i++;
																		}
																		$retArr['fromSet']		=	'specialData';
																		$retArr['errorCode']	=	'0';
																		$retArr['errorStatus']	=	'Data returned Successfully';
																	} else {
																		//$retValfetchDealClosedData	=	json_decode($this->fetchDealCloseDataReport($paramsGET['empcode']),true); //Not In Use
																		$retValfetchDealClosedData['errorCode']     = 1;
																		if($retValfetchDealClosedData['errorCode']	==	'0') {
																			$i=	0;
																			foreach($retValfetchDealClosedData['data'] as $key=>$value) {
																				$retArr['data'][ucwords(strtolower($value['companyname']))]['contractid'][]	=	$value['contractid'];
																				$i++;
																			}
																			$retArr['fromSet']		=	'dealClosedRep';
																			$retArr['errorCode']	=	'0';
																			$retArr['errorStatus']	=	'Data returned Successfully';
																		} else {
																			if(is_numeric($paramsGET['srchData'])) {
																				$retArr['errorCode']	=	'2';
																				$retArr['fromSet']		=	'phoneSrch';
																				$retArr['errorStatus']	=	'Data Not Found, search for number';
																			} else {
																				$retArr['errorCode']	=	'1';
																				$retArr['errorStatus']	=	'Data Not Found';
																			}
																		}
																	}
																} 
															}	
														}	
													}
											    //~ }
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return json_encode($retArr);
	}
	public function getRowId($empCode) {
		
		$curlParams =   array();
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }      
        $paramsGET['data_city']     = ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
        if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']          = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=getRowId&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['method']       = 'post';
        $curlParams['headerJson']   = 'json';
        $curlParams['postData']     = json_encode($paramsSend);
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);                        
        //~ $singleCheck                = Utility::curlCall($curlParams);                        
        //~ echo "<pre>singleCheck:==";print_r($singleCheck);        
        return json_encode($singleCheck);		
	}
	
	public function ownershipdata(){
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		if($_SERVER['REMOTE_ADDR'] == '172.29.87.77') {
			$paramsGET['empcode'] = '005178';
		}	
		$data_city 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=ownership&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&data_flag='.$paramsGET['data_flag'].'&remote_zone='.$remote_zone;		
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true); 
		return json_encode($singleCheck); 		
	}
	
	public function fetchNumberData($empCode='') {
		header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
            $paramsGET    =    array_merge($_POST,$_GET);
        }else{
            $paramsGET    =  json_decode(file_get_contents('php://input'),true);;
        }
		$postArray                      	= 	array();
		$retPhoneSrch                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php'; // 
		$postArray['noparentid'] 			= 	1;
		$postArray['phone'] 				= 	$paramsGET['parid'];
		$postArray['pageShow'] 				= 	$paramsGET['pageShow'];
		$postArray['empcode'] 				= 	$paramsGET['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['flag']					= 	$paramsGET['flag'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"searchCompanyByNum";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retPhoneSrch                       = 	json_decode(Utility::curlCall($dataParam),1);
		$rowId	=	'';
		if($empCode	!=	'') {
			$rowId	=	json_decode($this->getRowId($paramsGET),true);
		}
		$empInfo	=	json_decode($this->tmeInfo($paramsGET),true);
		if($empInfo['results']['allocId']	==	'RD') {
			$postArray1                      	= 	array();
			$retPhoneSrchECS                    = 	array();
			$curlParams                     	= 	array();
			$data_city            				= 	((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
			$postArray1['noparentid'] 			= 	1;
			$postArray1['pageShow'] 			= 	$paramsGET['pageShow'];
			$postArray1['empcode'] 				= 	$paramsGET['empcode'];
			$postArray1['data_city'] 			= 	$data_city;
			$postArray1['ecs_contact']			= 	$paramsGET['parid'];
			$postArray1['post_data']			= 	"1";		 
			$postArray1['module']				= 	"TME";		 
			$postArray1['action']				= 	"fetchEcsRetentionData";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray1),'formate' => 'basic','headerJson' => 'json');
			$retPhoneSrchECS                    = 	json_decode(Utility::curlCall($dataParam),1);
		}
		$errorCode	=	'1';
		
		$retArr	=	array();	
		$retArr['count']	=	0;
		if(isset($retPhoneSrch) && $retPhoneSrch['errorCode']	==	'0') {
			$i=	0;
			$k=0;
			foreach($retPhoneSrch['data'] as $key=>$value) {
				if($rowId != '') {
					if($rowId['data']['rowId']	==	$value['tmeCode']) {
						$retArr['data']['own'][$i]['companyname']	=	ucwords(strtolower($value['compname']));
						$retArr['data']['own'][$i]['contractid']	=	$value['contractid'];
						$retArr['data']['own'][$i]['paidstatus']	=	$value['paidstatus'];
						$retArr['data']['own'][$i]['tmeCode']	=	$value['tmeCode'];
						$i++;
					} else {
						$retArr['data']['other'][$k]['companyname']	=	ucwords(strtolower($value['compname']));
						$retArr['data']['other'][$k]['contractid']	=	$value['contractid'];
						$retArr['data']['other'][$k]['paidstatus']	=	$value['paidstatus'];
						$retArr['data']['other'][$k]['tmeCode']	=	$value['tmeCode'];
						$k++;
					}
				} else {
					$retArr['data']['other'][$k]['companyname']	=	ucwords(strtolower($value['compname']));
					$retArr['data']['other'][$k]['contractid']	=	$value['contractid'];
					$retArr['data']['other'][$k]['paidstatus']	=	$value['paidstatus'];
					$retArr['data']['other'][$k]['tmeCode']	=	$value['tmeCode'];
					$k++;
				}
			}
			$errorCode	=	'0';
			$retArr['count']	=	$retPhoneSrch['count'];
		}
		
		$errorCodeECS	=	'1';
		if(isset($retPhoneSrchECS) && $retPhoneSrchECS['count']	>	0) {
			$i=	0;
			$k=0;
			foreach($retPhoneSrchECS['data'] as $key=>$value) {
				$retArr['data']['ecs'][$i]['companyname']	=	ucwords(strtolower($value['companyname']));
				$retArr['data']['ecs'][$i]['contractid']	=	$value['parentid'];
				$retArr['data']['ecs'][$i]['ecs_stop_flag']	=	$value['ecs_stop_flag'];
				$retArr['data']['ecs'][$i]['state']	=	$value['state'];
				$retArr['data']['ecs'][$i]['escalated_details']	=	$value['escalated_details'];
				$retArr['data']['ecs'][$i]['date_str']	=	$value['date_str'];
				$retArr['data']['ecs'][$i]['tmename']	=	$value['tmename'];
				$retArr['data']['ecs'][$i]['ecs_reject_approved']	=	$value['ecs_reject_approved'];
				$retArr['data']['ecs'][$i]['reactivate_flag']	=	$value['reactivate_flag'];
				$retArr['data']['ecs'][$i]['tmecode']	=	$value['tmecode'];
				$retArr['data']['ecs'][$i]['action_flag']	=	$value['action_flag'];
				$retArr['data']['ecs'][$i]['EcsUpdate_Flag']	=	$value['EcsUpdate_Flag'];
				$retArr['data']['ecs'][$i]['website']	=	$value['website'];
				$i++;				
			}
			$errorCodeECS	=	'0';
			$retArr['count']	=	$retArr['count']+$retPhoneSrchECS['count'];
		}
		
		if($errorCode	==	'0' || $errorCodeECS	==	'0'){
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	//http://prameshjha.jdsoftware.com/jdbox/services/compaignPromo.php?data_city=mumbai&action=sendmessage&parentid=PXXX1&empcode=000000&campaignname=package
	
	public function docidcreator() {
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
        $paramsSend['action']		= 'docidcreator';
        $params['data_city'] 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        if($params['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?parentid='.$params['parentid'].'&empcode='.$params['empcode'].'&data_city='.$params['data_city'].'&action=docidcreator&module=TME&post_data=1&remote_zone='.$remote_zone;
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}	
	
	public function fetchsuperhotdata() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=fetchsuperhotdata&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&srchparam='.urlencode($paramsGET['srchparam']).'&srchData='.urlencode($paramsGET['srchData']).'&srchwhich='.urlencode($paramsGET['srchwhich']).'&pageShow='.$paramsGET['pageShow'].'&parentid='.$paramsGET['parid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 		
	}
	
	
	public function updatesuperhotdata() {		
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$paramsGET	=	array_merge($_POST,$_GET);
		}else{
			$paramsGET	=	json_decode(file_get_contents('php://input'),true);
		}
		$paramsGET['data_city'] 		= ((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
		if($paramsGET['data_city']	==	'remote'){
			$remote_zone =   REMOTEZONEFLAG;
		}
		$curlParams['url']  = $this->genioconfig['jdbox_url'][strtolower($paramsGET['data_city'])].'services/tmenewServices.php?action=updatesuperhotdata&empcode='.$paramsGET['empcode'].'&module=TME&data_city='.urlencode($paramsGET['data_city']).'&parentid='.$paramsGET['parentid'].'&post_data=1&remote_zone='.$remote_zone;
		$curlParams['formate']      = 'basic';
		$curlParams['formate']      = 'post';
		$curlParams['headerJson']   = 'json';
		$singleCheck                = json_decode(Utility::curlCall($curlParams),true);
		return json_encode($singleCheck); 		
	}
	
	public function cityautosuggest(){
		$retValemp             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$data_city            			= ((in_array(strtolower($params['server_city']), $this->main_cities)) ? strtolower($params['server_city']) : 'remote');
		$url                            = $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/fetchAllDetails.php';
		$postArrayempinfo['post_data']  = 	"1";           
		$postArrayempinfo['module']     = 	"TME";
		$postArrayempinfo['data_city']  = 	$params['data_city']; 
		$postArrayempinfo['ucode']    	=	$params['ucode']; 
		$postArrayempinfo['uname']    	=	$params['uname']; 
		$postArrayempinfo['term']    	=	$params['term']; 
		if($data_city == 'remote')
			$postArrayempinfo['remote']    	=	1; 
		else
			$postArrayempinfo['remote']    	=	0; 
		$postArrayempinfo['action']		=  "cityautosuggest";
		$postArrayempinfo['noparentid']	=  1;
		$paramsArr                      =   array('url' => $url, 'method' => 'post','postData' => json_encode($postArrayempinfo),'formate' => 'basic','headerJson' => 'json');
		$retValemp 						= 	json_decode(Utility::curlCall($paramsArr),true);
		return json_encode($retValemp);
	}
	
}
?>
