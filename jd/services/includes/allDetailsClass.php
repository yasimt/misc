<?php
class allDetailsClass extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 	= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $tbl_id_generator = array();
	var  $docid			=null;
	
	function __construct($params)
	{
		$this->params 	= $params;
		$parentid 		= trim($params['parentid']);
		$noparentid 	= trim($params['noparentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		$noname 		= trim($params['noname']);
		$isconnected 	= trim($params['isconnected']);
		
		if((trim($parentid)=='') && ($noparentid !=1))
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponse($message,1));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendResponse($message,1));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendResponse($message,1));
			die();
		}
		if(trim($ucode)=='')
		{
			$message = "Ucode is blank.";
			echo json_encode($this->sendResponse($message,1));
			die();
		}
		if(trim($uname)=='' && ($noname !=1))
		{
			$message = "Uname is blank.";
			echo json_encode($this->sendResponse($message,1));
			die();
		}
		$this->isconnected 	= $isconnected;
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->uname  	  	= $uname;
		/*mongo*/
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->companyClass_obj  = new companyClass();
		$this->setServers();
		
		
		
		
		$valid_modules_arr = array("TME","ME","JDA");
		if(!in_array($this->module,$valid_modules_arr)){
			$message = "This service is only applicable for ME/JDA module.";
			echo json_encode($this->sendResponse($message));
			die();
		}
		$configcls_obj		= new configclass();
		$urldetails			= $configcls_obj->get_url($this->data_city);
		$this->cs_url		= $urldetails['url'];
		$this->tme_url		= $urldetails['tme_url'];
		$this->jdbox_url	= $urldetails['jdbox_url'];
		$this->iro_url		= $urldetails['iro_url'];
		$this->rest_url 	= $urldetails['rest_url'];
		if($params['trace']==1){
			print"<pre>";print_r($params);
		}
		$this->current_date = date("Y-m-d H:i:s"); 
	}
	// Function to set DB connection objects
	function setServers()
	{
		global $db;
		$this->conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$this->conn_city]['iro']['master'];
		$this->conn_local  		= $db[$this->conn_city]['d_jds']['master'];
		$this->conn_tme  	= $db[$this->conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$this->conn_city]['idc']['master'];
		$this->conn_fin   		= $db[$this->conn_city]['fin']['master'];
		$this->conn_fin_slave   = $db[$this->conn_city]['fin']['slave'];
		$this->conn_dnc   		= $db['dnc'];
		
		
		if($this->module == 'ME')
		{
			$this->conn_temp = $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		if($this->module == 'TME')
		{
			$this->conn_temp     = $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($this->conn_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}
		}
		
	}
	
	function getPaymentCampaignMaster()
	{
		$payment_campaign_master= array(1=>'Phone Search - Package',2=>'Phone Search - Platinum/Diamond',3=>'Web Search',4=>'SMS Leads',5=>'Competitors Banner',6=>'Power Listing',7=>'Registration Fee',8=>'Enhancements (Video/Logo/Catalog)',9=>'Print',10=>'National Registration - Phone',11=>'National Registration - Web',12=>'Phone Search - Tenure',13=>'Category Banner',14=>'Category Filter Banner',15=>'Category Text Banner',16=>'Smart Registration Fee',17=>'Hidden Contract',18=>'C2C Contract',19=>'Quick Quote',20=>'Phone Search - Lead',22=>'JDRR',23=>'Restaurants',24=>'Video Shoot',25=>'Table Reservation',26=>'Doctor Reservation',27=>'Liquor Service',28=>'Gas Booking Service',29=>'Shop Front',30=>'Laundry Service',31=>'Grocery Delivery',32=>'SPA',33=>'Vehicle Service',34=>'Bus Booking',35=>'Cab Booking',36=>'Water Purifier Service',37=>'Courier Service',38=>'AC service',39=>'Pharmacy Delivery',40=>'Diagnostic Labs',41=>'Test Drive',42=>'Flower Service',43=>'Cake Service',44=>'Sweet Service',45=>'Flight Booking',46=>'Hotel Booking',47=>'Book Service',48=>'Movies',49=>'Mineral Water',50=>'Pathology Service',51=>'Insurance',52=>'FinMis TR',53=>'FinMis DR',54=>'Loan',55=>'Banquet Halls',56=>'Activation Fee',57=>'Dairy Service',58=>'Recharge',59=>'Plumber',60=>'GenieTag',61=>'5 Star Hotels',62=>'b2bmarketplace',63=>'nonbrandedmarketplace',64=>'Deals and offers',65=>'Events',66=>'Train Booking',67=>'Repair',68=>'service',69=>'LME',70=>'Advance Activation Fee',71=>'Banner Creation Fee',72=>'Website Creation Fee',73=>'Website Maintenance Fee',74=>'Domain Registration fee',75=>'iOS app creation fee',76=>'FinMis E-Com Reserve 1',77=>'FinMis E-Com Reserve 2',78=>'FinMis E-Com Reserve 3',79=>'FinMis E-Com Reserve 4',80=>'FinMis E-Com Reserve 5',81=>'Prompt Campaign',82=>'Email',83=>'SMS',84=>'Android App Fee',85=>'Delivery Boy',86=>'SSL certificate',99=>'GA CORPKILL');		
		return $payment_campaign_master;
	}
	
	
	function getTempInfo(){
		
		$tempdata = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['module']       	= $this->module;
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_generalinfo_shadow"=>"sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,mobile_admin,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city",
				"tbl_companymaster_extradetails_shadow"=>"parentid,working_time_start,working_time_end,social_media_url,award,testimonial,proof_establishment,payment_type,turnover,year_establishment,updatedBy,updatedOn,landline_addinfo,mobile_addinfo,statement_flag,fb_prefered_language,catidlineage_nonpaid,tag_line,certificates,no_employee,accreditations",
				"tbl_business_temp_data"=>"catIds"
			));
				
			$tempdata = $this->mongo_obj->getShadowData($mongo_inputs);
		}
		else
		{
			$sqlGeninfoShadow  = "SELECT sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,mobile_admin,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
			$resGeninfoShadow = parent::execQuery($sqlGeninfoShadow, $this->conn_temp);
			if($resGeninfoShadow && parent::numRows($resGeninfoShadow)>0){
				$row_geninfo_shadow = parent::fetchData($resGeninfoShadow);
				$tempdata['tbl_companymaster_generalinfo_shadow'] = $row_geninfo_shadow;
			}
			$sqlExtradetShadow  = "SELECT parentid,working_time_start,working_time_end,social_media_url,award,testimonial,proof_establishment,payment_type,turnover,year_establishment,updatedBy,updatedOn,landline_addinfo,mobile_addinfo,statement_flag,fb_prefered_language,catidlineage_nonpaid,tag_line,certificates,no_employee,accreditations FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$this->parentid."'";
			$resExtradetShadow = parent::execQuery($sqlExtradetShadow, $this->conn_temp);
			if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
				$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
				$tempdata['tbl_companymaster_extradetails_shadow'] = $row_extrdet_shadow;
			}
			$sqlBusTempData  = "SELECT catIds FROM tbl_business_temp_data WHERE contractid = '".$this->parentid."'";
			$resBusTempData = parent::execQuery($sqlBusTempData, $this->conn_temp);
			if($resBusTempData && parent::numRows($resBusTempData)>0){
				$row_bus_temp_data = parent::fetchData($resBusTempData);
				$tempdata['tbl_business_temp_data'] = $row_bus_temp_data;
			}
			
		}
		$this->temp_catlin_arr 	= 	array();
		$this->temp_catlin_np_arr = array();
		if(($tempdata['tbl_business_temp_data']['catIds']!='' ) || ($tempdata['tbl_companymaster_extradetails_shadow']['catidlineage_nonpaid'] !='')){
			$this->temp_catlin_arr   =   explode('|P|',$tempdata['tbl_business_temp_data']['catIds']);
			$this->temp_catlin_arr 	= $this->getValidCategories($this->temp_catlin_arr);
			
			$this->temp_catlin_np_arr = explode("/,/",trim($tempdata['tbl_companymaster_extradetails_shadow']['catidlineage_nonpaid'],"/"));
			$this->temp_catlin_np_arr = $this->getValidCategories($this->temp_catlin_np_arr);
		}
		$all_temp_catids_arr = array();
		$all_temp_catids_arr = array_unique(array_merge($this->temp_catlin_arr,$this->temp_catlin_np_arr));
		$tempdata['all_temp_catids'] 	= $all_temp_catids_arr;
		$all_catinfo_arr 	 = $this->getCategoryDetails($all_temp_catids_arr);
		$tempdata['catinfo'] = $all_catinfo_arr;
		
		
		$tempdata['stdcode'] = $this->getstdcodeofpincode($tempdata['tbl_companymaster_generalinfo_shadow']['data_city'],$tempdata['tbl_companymaster_generalinfo_shadow']['pincode']);
		$tempdata['mktgEmpMaster'] = $this->getmktgEmpMasterdata($this->ucode);
		$tempdata['tbl_tmesearch'] = $this->getTmeSearchData();
		$tempdata['ownershipData'] = $this->getownershipData();
		if($tempdata['tbl_tmesearch']['data_source']	==	'Joinfree-Websit'){
			$tempdata['Extension']	=	$this->get_extension();
		}else{
			$tempdata['Extension']	=	'';
		}
		$tempdata['deliveredCases'] = $this->deliveredCases();
		$tempdata['PreferedLanguage'] = $this->getPreferedLanguage();
		$tempdata['deactive_reason'] = $this->get_tbl_sms_feedback_deactive_log_data();
		$tbl_companymaster_finance_data = $this->getFinanceDataLive();
		$tempdata['tbl_companymaster_finance'] = $tbl_companymaster_finance_data;
		
		$tempdata['paidExpiredStatus'] =  $this->getpaidexpiredStatus($tbl_companymaster_finance_data);
		$tempdata['finpaidtatus'] =  $this->getFinPaidStatus($tbl_companymaster_finance_data);
		$tempdata['company_source'] =  $this->get_tbl_company_source_data();
		$tempdata['client_waiting_flag'] =  $this->clientWaitingInfo();
		$tempdata['client_visiting_data'] =  $this->clientVisitingInfo();
		$tempdata['instruction'] = $this->getInstructionInfo();
		$tempdata['tbl_companymaster_generalinfo']	= $this->getGeninfoMainData();
		$tempdata['tbl_companymaster_extradetails']	= $this->getExtradetMainData();
		$tempdata['tbl_id_generator']		= $this->idGeneratorData();
		$tempdata['paymenttype'] 			=	array("Cash","Master Card","Visa Card","Debit Cards","Cheques","American Express Card","Credit Card");
		$tempdata['notification_status']	=	$this->getNotificationStat($tempdata["tbl_companymaster_generalinfo_shadow"]["mobile"]);
		//condition for RD team
		if(strtolower($tempdata['mktgEmpMaster']['allocId']) == 'rd' && isset($tempdata['tbl_companymaster_generalinfo']['parentid'])){
			$general_array		=	$tempdata['tbl_companymaster_generalinfo'];	//For RD Team fetching data from live table if data found - only company details - ITU
			$tempdata['tbl_companymaster_generalinfo_shadow'] = $general_array;
		}
		$tempdata['bypassgeniolite']		=	$this->getbypassdet();
		return $tempdata;
	}
	
	public function getbypassdet(){
		$empInfo = 1;
		$sqlPaidContractsInfo = "SELECT * FROM online_regis.tbl_bypassgeniolite_access WHERE empcode='".$this->params['ucode']."'";
		$resPaidContractsInfo = parent::execQuery($sqlPaidContractsInfo, $this->conn_idc);
		if($resPaidContractsInfo && parent::numRows($resPaidContractsInfo)>0){
			$empInfo = 0;
		}
		return $empInfo;            
	}
	function getNotificationStat($numbers){
		if($numbers!=''){
			$url 			= 	'http://notifications.justdial.com/newnotify/UserStatus.php?udids='.$numbers.'&mobtyp=2&isdcode=0091';
			$content 		= 	$this->curlCallNot($url);
			$retArr			=	json_decode($content,true);
			$status			=	0;
			if(count($retArr) > 0){
				foreach($retArr['results'] as $k=>$v){
					if((isset($v['isLiteUser']) && $v['isLiteUser'] == true) || (isset($v['app_version'])))
						$status++;	
				}
				if($status > 0)
					return 'YES';
				else
					return 'NO';
			}else{
				return 'N/A';
			}
		}else{
			return 'NO';
		}
	}
	function get_extension(){
		$parentid 	= trim($this->params['parentid']);
		$city 		= trim($this->params['data_city']);
		if(empty($parentid)){
			$message = "City is mandatory to get extension info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		if(empty($city)){
			$message = "City is mandatory to get extension info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}		
		$paramsSend					= array();
		$paramsSend['rquest'] 		= "get_extention";
		$paramsSend['parentid'] 	= $parentid;
		$paramsSend['data_city'] 	= $city;
		$paramsSend['limit'] 		= "10";
		$paramsSend['module'] 		= $this->module;
		$area_info_url 				= $this->jdbox_url."services/location_api.php";
		$area_resp 					= json_decode($this->curlCall($area_info_url,$paramsSend),true);
		//~ echo '<pre>';print_r($this->curlCall($area_info_url,$paramsSend)); die;
		//~ echo '<pre>';print_r($area_resp); die;
		//data['result']['areaname']['areaname'] is area
		if($area_resp['numRows'] >0 ){
			$message = "Extesion Data Found";
			$resultArr['data']				=	array_unique($area_resp, SORT_REGULAR);
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;			
		}else{
			$message = "Extension Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		return $resultArr;
	}
	
	function deliveredCases(){
		$deliveredCases = 0;
		$sqlGeninfoMain   = "SELECT parentid FROM tbl_jdrr_delivered WHERE parentid = '".$this->parentid."'";
		$resGeninfoMain  = parent::execQuery($sqlGeninfoMain, $this->conn_local);
		if($resGeninfoMain && parent::numRows($resGeninfoMain)>0){
			$deliveredCases = 1;
		}
		return $deliveredCases;
	}
	
	function getGeninfoMainData(){
		$row_geninfo_main = array();
		//$sqlGeninfoMain  = "SELECT sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,subarea,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		//$resGeninfoMain = parent::execQuery($sqlGeninfoMain, $this->conn_iro);
				
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'sphinx_id,parentid,companyname,building_name,street,landmark,country,state,city,area,subarea,pincode,latitude,longitude,geocode_accuracy_level,landline,email,email_feedback,email_display,mobile,mobile_display,mobile_feedback,contact_person,fax,tollfree,email,sms_scode,website,paid,othercity_number,data_city';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'allDetailClass';

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		
		//~ if($resGeninfoMain && parent::numRows($resGeninfoMain)>0){
			//~ $row_geninfo_main = parent::fetchData($resGeninfoMain);
		//~ }
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
		{
			$row_geninfo_main 	= $comp_api_arr['results']['data'][$this->parentid];			
		}
		return $row_geninfo_main;
	}
	function getExtradetMainData(){
		$row_extrdet_main = array();
		//$sqlExtradetMain  = "SELECT parentid,working_time_start,working_time_end,catidlineage,updatedBy,updatedOn,mask FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		//$resExtradetMain = parent::execQuery($sqlExtradetMain, $this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'parentid,working_time_start,working_time_end,catidlineage,updatedBy,updatedOn,mask';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'allDetailsClass';
		
		$comp_api_res 	= '';
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
		{
			$row_extrdet_main 	= $comp_api_arr['results']['data'][$this->parentid]; 			
		}
		//~ if($resExtradetMain && parent::numRows($resExtradetMain)>0){
			//~ $row_extrdet_main = parent::fetchData($resExtradetMain);
		//~ }
		return $row_extrdet_main;
	}
	function getCategoryDetails($catids_arr)
	{
		$catInfoArr = array();
		$paidCatArr = array();
		$nonpaidCatArr = array();
		
		$paidCatStr 	= '';
		$nonpaidCatStr 	= '';
		$fos = 0;
		$catids_str = implode("','",$catids_arr);
		$sqlCategoryDetails = "SELECT catid,category_name,display_product_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_local);
		if($resCategoryDetails && parent::numRows($resCategoryDetails)>0)
		{
			while($row_catdetails = parent::fetchData($resCategoryDetails))
			{
				$catid 						= trim($row_catdetails['catid']);
				$category_name				= trim($row_catdetails['category_name']);
				$display_product_flag 		= intval($row_catdetails['display_product_flag']);
				if(in_array($catid,$this->temp_catlin_arr)){
					$paidCatArr[] = $category_name;
				}else{
					$nonpaidCatArr[] = $category_name;
				}
				
				if(((int)$display_product_flag & 8796093022208) == 8796093022208){
					$fos = 1;
				}
				
			}
		}
		if(count($paidCatArr)>0){
			$paidCatStr = implode("|~|",$paidCatArr);
		}
		if(count($nonpaidCatArr)>0){
			$nonpaidCatStr = implode("|~|",$nonpaidCatArr);
		}
		$catInfoArr['paid'] = $paidCatStr;
		$catInfoArr['nonpaid'] = $nonpaidCatStr;
		$catInfoArr['fos'] 		= $fos;
		return $catInfoArr;
	}
	
	function getmktgEmpMasterdata($mktEmpCode)
	{
		$resultarr = array();
		$sql = "SELECT * FROM mktgEmpMaster WHERE mktEmpCode = '".$mktEmpCode."'";
		$res = parent::execQuery($sql, $this->conn_local);
		if($res && parent::numRows($res)>0){
			$row = parent::fetchData($res);
			$resultarr = $row;
		}
		return $resultarr;
	}
	 
	function getInstructionInfo(){
		$sqlInstructionInfo = "SELECT instruction FROM tblContractAllocation WHERE contractcode='" . $this->parentid . "' and allocationtype ='22' order by allocationtime desc LIMIT 1";
        $resInstructionInfo = parent::execQuery($sqlInstructionInfo, $this->conn_local);
        if($resInstructionInfo && parent::numRows($resInstructionInfo)>0){
            $row_instruction = parent::fetchData($resInstructionInfo);
            if(!empty($row_instruction['instruction'])) {
                $ret_instruct = $row_instruction['instruction'];
            } else {
                $ret_instruct = 'No Instructions for this contract';
            }
        } else {
            $ret_instruct = 'No Instructions for this contract';
        }
        return $ret_instruct;
	}
	

	
	function curl_call($param) {
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
    
    function getSSOInfo($empcode){
			$paramsArr			=	array();
			$retValemp			=	array();				
			$postArrayempinfo	=	array();
			$paramsArr['url'] 					= 	'http://192.168.20.237:8080/api/getEmployee_xhr.php';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$postArrayempinfo['empcode']		=	 trim($empcode); //trim($this->params['empcode']);
			$postArrayempinfo['textSearch']		=	4;
			$postArrayempinfo['reseller_flag']	=	1;
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
			$paramsArr['postData'] 				= 	json_encode($postArrayempinfo);
			$retValemp 							= 	json_decode($this->curl_call($paramsArr),true);
			return $retValemp;
	}
	
	public function getTmeSearchData()
	{
		$resultarr = array();
		$sql = "SELECT * FROM tbl_tmesearch WHERE parentid = '".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_local);
		if($res && parent::numRows($res)>0){
			$row = parent::fetchData($res);
			$resultarr = $row;
		}
		return $resultarr;
	}
	
	function getownershipData(){
		//~ $resultarr 	= 	array();
		//~ $sql 		= 	"SELECT * FROM tbl_tme_data_search WHERE parentid = '".$this->parentid."'";
		//~ $res 		= 	parent::execQuery($sql, $this->conn_local);
		//~ if($res && parent::numRows($res)>0){
			//~ while($row = parent::fetchData($res)){
				//~ $resultarr['data'][] 	= 	$row;
				//~ $GetSSOInfo 			=	$this->getSSOInfo($this->ucode);
				//~ $parentid				=	$row['parentid'];
				//~ $companyname			=	$row['companyname'];
				//~ if($GetSSOInfo['errorcode'] ==	0){
					//~ $resTme			=	$GetSSOInfo['data'][0];
				//~ }
				//~ $GetSSOInfo1 			=	$this->getSSOInfo($row['empcode']);
				//~ if($GetSSOInfo1['errorcode'] ==	0){
					//~ $resTme1		=	$GetSSOInfo1['data'][0];
					//~ $empName		=	$resTme1['empname'];
				//~ }
				//~ if(strtolower($resTme['team_type']) == 'rd' || strtolower($resTme['team_type']) == 'bd'){
						//~ $resultarr['errorCode']		=	2;
						//~ $resultarr['errorStatus']	=	'Retention Team / Bounce Team Employee So Do not Block!.';	
				//~ }else{
					//~ if($this->ucode == $row['empcode']){
						//~ $resultarr['data']		=	$resTme1;
						//~ $resultarr['errorCode']		=	0;
						//~ $resultarr['errorStatus']	=	'Data Allocated to you';
					//~ }else{
						//~ $resultarr['data']			=	$resTme1;
						//~ $resultarr['errorCode']		=	1;
						//~ $resultarr['errorStatus']	=	$companyname.'('.$parentid.') Allocated to '.$empName;
					//~ }
				//~ }
			//~ }
		//~ }else{
			//~ $resultarr['errorCode']		=	2;
			//~ $resultarr['errorStatus']	=	'No Data';
		//~ }
		$resultarr['errorCode']		=	2;
		$resultarr['errorStatus']	=	'No Data';
		return $resultarr;
	}
	
	public function idGeneratorData()
	{
		$resultarr = array();
		$sql = "SELECT * FROM tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_iro);
		if($res && parent::numRows($res)>0){
			$row = parent::fetchData($res);
			$resultarr = $row;
		}
		return $resultarr;
	}
	

	function ecsTransferInfo()
	{
		$retArr 	= array();
		$extnID 	= $this->params['extn'];
		$main_zone 	= $this->params['login_city'];
		
		if($this->conn_city=='remote')
		{
			$tbl_f12_transfer_data_sql 	= "SELECT id,parentid,f12_id,scity FROM tbl_f12_transfer_data WHERE (ext='".$extnID."' OR cli='".$extnID."') AND status=0 ORDER BY DATETIME DESC LIMIT 1;";
			$tbl_f12_transfer_data_res	= parent::execQuery($tbl_f12_transfer_data_sql, $this->conn_iro);
			$tbl_f12_transfer_data_num	= parent::numRows($tbl_f12_transfer_data_res);
			
			if($tbl_f12_transfer_data_num > 0){
				$tbl_f12_transfer_data_row	= parent::fetchData($tbl_f12_transfer_data_res);
				
				$tbl_zone_cities_sql				=	"SELECT * FROM tbl_zone_cities WHERE cities='".$tbl_f12_transfer_data_row['scity']."' AND main_zone = '".$main_zone."'";
				$tbl_zone_cities_res  				=	parent::execQuery($tbl_zone_cities_sql, $this->conn_local);
				$tbl_zone_cities_num				=	parent::numRows($tbl_zone_cities_res);
				if($tbl_zone_cities_num > 0){
					$f12_id_qry = "SELECT opt_name FROM tbl_f12_option WHERE opt_code = '".$tbl_f12_transfer_data_row['f12_id']."'";
					$f12_id_res =  parent::execQuery($f12_id_qry, $this->conn_iro);
					$f12_id_row = parent::fetchData($f12_id_res);
					$retArr['error']['code']		=	0;
					$retArr['data'] 	= $tbl_f12_transfer_data_row;
					$retArr['opt_name'] = $f12_id_row['opt_name'];
				}
			}
			
		}else
		{
			$tbl_f12_transfer_data_sql 	= "SELECT id,parentid,f12_id FROM db_iro.tbl_f12_transfer_data WHERE (ext='".$extnID."' OR cli='".$extnID."') AND status=0 ORDER BY DATETIME DESC LIMIT 1;";
			$tbl_f12_transfer_data_res	= parent::execQuery($tbl_f12_transfer_data_sql, $this->conn_iro);
			$tbl_f12_transfer_data_num	= parent::numRows($tbl_f12_transfer_data_res);
			
			if($tbl_f12_transfer_data_num>0){
				$tbl_f12_transfer_data_row				= parent::fetchData($tbl_f12_transfer_data_res);
				
				$f12_id_qry = "SELECT opt_name FROM tbl_f12_option WHERE opt_code = '".$tbl_f12_transfer_data_row['f12_id']."'";
				$f12_id_res =  parent::execQuery($f12_id_qry,$this->conn_iro);	
				$f12_id_row = parent::fetchData($f12_id_res);
				
				
				$upd_lead_api 		= "INSERT INTO d_jds.tbl_transfer_api_log
											SET
											parentid = '".$tbl_f12_transfer_data_row['parentid']."',
											extn = '".$extnID."',
											insert_date = NOW(),
											optName   = '".$tbl_f12_transfer_data_row['f12_id']."',
											flag = 1";
				parent::execQuery($upd_lead_api, $this->conn_local);
				$retArr['error']['code']		=	0;
				$retArr['data'] 	= $tbl_f12_transfer_data_row;
				$retArr['opt_name'] = $f12_id_row['opt_name'];
			}
		}		
		return $retArr;		
	}	
	
	
	function getFinanceDataLive()
	{
		$resultarr = array();
		$sql = "select campaignid,budget,balance,bid_perday,expired, expired_on,duration from tbl_companymaster_finance WHERE parentid = '".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_fin);
		if($res && parent::numRows($res)>0){
			
			while($row = parent::fetchData($res))
			{				
				$resultarr[$row['campaignid']] = $row;
			}
			
		}
		
		if(DEBUG_MODE)
		{			
			echo '<br><b>DB Query:</b>'.$sql;			
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($res);
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		return $resultarr;
	}
	
	function getpaidStat(){
		$resultarr 		= 0;
		$sql 			= "select balance from tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND balance > 0";
		$res 			= parent::execQuery($sql, $this->conn_fin);
		if($res && parent::numRows($res)>0){
			$resultarr 	= 1;
		}
		return $resultarr;
	}
	
	public function getPreferedLanguage()
	{
		$languageQry = "SELECT * FROM tbl_language_master WHERE active_flag = '1'";
		$conLanguage = parent::execQuery($languageQry,$this->conn_iro);		
        $responseArr	=	array();
        
        if ($conLanguage && mysql_num_rows($conLanguage)) {
			while ($resLanguage = mysql_fetch_assoc($conLanguage)) {
				$responseArr[]	=	$resLanguage;
			}	
        }
        return $responseArr;
    }
		
    

	function getpaidexpiredStatus($financearray)
	{		
		$minexpval = 0;
		
		if(count($financearray))
		{
			foreach($financearray as $campid=>$camparr)
			{
				$minexpval = min($minexpval,$camparr['expired']);
			}
		}
		
		return $minexpval;
	}
	
	function getFinPaidStatus($financearray)
	{		
		$paidstatus = 0;
		
		if(count($financearray))
		{
			foreach($financearray as $campid=>$camparr)
			{
				if($camparr['balance']>0)
				{
					$paidstatus=1;
					break;
				}
			}
		}
		
		return $paidstatus;
	}
	
	function get_tbl_company_source_data()
    {
        $currDate = date('Y-m-d');
        $resultarr = array();
        $sql = "select * from tbl_company_source WHERE parentid = '".$this->parentid."' AND datesource>='".$currDate." 00:00:00' AND datesource<='".$currDate." 23:59:59' order by  csid desc limit 1";
        $res = parent::execQuery($sql, $this->conn_local);
        //~ parent::numRows($res) = 0;
        if($res && parent::numRows($res)>0){
            $row = parent::fetchData($res);
            $resultarr = $row;
        }else
        {
            $sql_tmesearch = "select data_source as subsource from tbl_tmesearch WHERE parentid = '".$this->parentid."' order by datasource_date desc limit 1";
            $res_tmesearch = parent::execQuery($sql_tmesearch, $this->conn_local);
            if($res_tmesearch && parent::numRows($res_tmesearch)>0){
                $row_tmesearch = parent::fetchData($res_tmesearch);
                $resultarr = $row_tmesearch;
            }else
            {
                $sql_hotData = "select source as subsource from tbl_hotData WHERE parentid = '".$this->parentid."' order by create_date desc limit 1";
                $res_hotData = parent::execQuery($sql_hotData, $this->conn_local);
                if($res_hotData && parent::numRows($res_hotData)>0){
                    $row_hotData = parent::fetchData($res_hotData);
                    $resultarr = $row_hotData;
                }
            }
        }
        return $resultarr;
    }

	function get_tbl_sms_feedback_deactive_log_data()
	{
		$resultarr = array();
		$sql = "select reason from tbl_sms_feedback_deactive_log WHERE parentid = '".$this->parentid."' order by  deactive_date desc limit 1";
		$res = parent::execQuery($sql, $this->conn_temp);
		if($res && parent::numRows($res)>0){
			$row = parent::fetchData($res);
			$resultarr = $row;
		}
		return $resultarr;
	}
	
	function getstdcodeofpincode($data_city,$pincode = '')
	 {
		 $stdcode=null;
		if(trim($pincode) != '')
		{
            $condition = "pincode	=	'" . $pincode . "'";
            
        }else
        {
            $condition = "data_city	=	'" . $data_city . "'";
        }

		$sql 	= 	"SELECT stdcode FROM tbl_areamaster_consolidated_v3 WHERE display_flag=1 AND ".$condition." LIMIT 1";
		
		$res = parent::execQuery($sql, $this->conn_local);
		#$res_stdcode 	= 	$this->conn_local->query_sql($sql_stdcode);
		if($res && parent::numRows($res)>0)
		{
			$row = parent::fetchData($res);
			$stdcode 		= 	$row['stdcode'];	
			if($stdcode[0]=='0')
			{
				$stdcode = substr($stdcode,1);
			}
			else
			{
                $stdcode = $stdcode;
            }
        }
        return $stdcode;
    }
	
	function clientWaitingInfo()
	{
		$waiting_flag = 0;
		$sqlClientWaitingInfo = "SELECT parentid FROM d_jds.tbl_walkin_client_details WHERE tmecode = '".$this->ucode."' AND (final_status = '' OR final_status IS NULL) LIMIT 1";
		$resClientWaitingInfo = parent::execQuery($sqlClientWaitingInfo, $this->conn_local);
		if($resClientWaitingInfo && parent::numRows($resClientWaitingInfo)>0){
			$waiting_flag = 1;
		}
		return $waiting_flag;
	}
	
	function clientVisitingInfo(){
		$visiting_info = array();
		$sqlClientVisitingInfo = "SELECT parentid as contractid,companyname,start_time FROM d_jds.tbl_walkin_client_details WHERE tmecode = '".$this->ucode."' AND (final_status = '' OR final_status IS NULL) AND parentid = '".$this->parentid."' ORDER BY allocated_date DESC LIMIT 1";
		$resClientVisitingInfo = parent::execQuery($sqlClientVisitingInfo, $this->conn_local);
		if($resClientVisitingInfo && parent::numRows($resClientVisitingInfo)>0){
			$row_visting_data = parent::fetchData($resClientVisitingInfo);
			if($row_visting_data['start_time'] == '' || $row_visting_data['start_time'] == null){
				$visiting_info['disable'] = 0;
			}else{
				$visiting_info['disable'] = 1;
			}
			$visiting_info['errorCode'] 	=	0;
		}else{
			$visiting_info['errorCode'] 	=	1;
		}
		return $visiting_info;
	}
	function dndInfo(){
		$contact_details_arr = array();
		if($this->params['landline']){
			$landline_arr = explode(",",$this->params['landline']);
			$landline_arr = array_unique(array_filter($landline_arr));
			if(count($landline_arr)>0){
				if(intval($this->params['stdcode'])<=0){
					$message = "stdcode is blank.";
					echo json_encode($this->sendResponse($message,1));
					die();
				}
				$DND_stdcode = ltrim($this->params['stdcode'], '0');
				$l	=	0;
				$contact_details_arr['phone']	=	$landline_arr[0];
				foreach($landline_arr	as	$key_landline=>$value_landl){
					if($l	!=	0){
						$contact_details_arr['phone'.($l+1)]	=	$value_landl;
					}
					$l++;
				}
			}
		}
		if($this->params['mobile']){
			$mobile_arr = explode(",",$this->params['mobile']);
			$mobile_arr = array_unique(array_filter($mobile_arr));
			if(count($mobile_arr)>0){
				$k	=	0;
				$contact_details_arr['mobile']	=	$mobile_arr[0];
				foreach($mobile_arr	as	$key_mobile=>$value_mob){
					if($k	!=	0){
						$contact_details_arr['mobile'.($k+1)]	=	$value_mob;
					}
					$k++;
				}
			}
		}
		$number_array   = array("phone","phone2","phone3","phone4","mobile","mobile2","mobile3","mobile4");
		foreach($number_array AS $key=>$value)
		{
			if($contact_details_arr[$value]!='' && is_numeric($contact_details_arr[$value]))
			{
				if(strstr($value ,"phone"))
				{
					$contact_details_tele .= $DND_stdcode.$contact_details_arr[$value]." ";
				}
				else if(strstr($value ,"mobile"))
				{
					$contact_details_mobile .= $contact_details_arr[$value]." ";
				}	
			}
		}
		$contact_details = $contact_details_tele.$contact_details_mobile;
		$contact_details = preg_replace('/\s+/', ' ', trim($contact_details));
		
		$final_contact_arr = array();
		$final_contact_arr = explode(' ', $contact_details);
		$final_contact_arr = array_unique(array_filter($final_contact_arr));
		
		
		$red_list = array();
        $green_list = array();
        
		if(count($final_contact_arr)>0){
			$post_data = array();	
			$post_data['phonenum'] = implode(",",$final_contact_arr);
			$dncurl = $this->tme_url."api/dncsearch_new.php";
			$dnresp = $this->curlCall($dncurl,$post_data);
			
			
			$dnd_numbers_arr = array();
			$nondnd_numbers_arr = array();
			if($dnresp){
				$dnc_results = json_decode($dnresp,true);
				foreach($dnc_results as $contactval => $dnc_info){
					if($dnc_info['found'] == 1 && $dnc_info['status'] == 'DND'){
						$dnd_numbers_arr[] = $contactval;
					}else if($dnc_info['found'] == 1 && $dnc_info['status'] == 'NonDND'){
						$nondnd_numbers_arr[] = $contactval;
					}
				}
			}
			#print"<pre>";print_r($dnc_results);
			#print"<pre>";print_r($dnd_numbers_arr);
			#print"<pre>";print_r($nondnd_numbers_arr);
			
			if($final_contact_arr[0]    !=    '') {
                foreach($final_contact_arr as $numberVal) {
					if(in_array($numberVal,$dnd_numbers_arr)){
						
						if(intval($DND_stdcode)>0){
							$len_std        =	strlen($DND_stdcode);                  		// Fetching stdcode length to compare with Landline numbers//
							$std_number		=	substr($numberVal,0,$len_std);        	// Removing numbers equal to std code length//
							if(intval($std_number)	==	intval($DND_stdcode)){        		// Checking wether std code and removed numbers are equal //
								$final_number 	=  substr($numberVal,$len_std);         // Removing stdcode from the number  //
							}else{
								$final_number	=  $numberVal;                        	// No check if number's digit doesnot match with stdcode i.e mobile number//
							}
						}else{
							$final_number	=  $numberVal;
						}
						if($this->isLinkedWithPaidContracts($final_number)) {
                            $green_list[] = $final_number;
                        }else{
							$red_list[]     =     $final_number;
						}
						
					}else if(in_array($numberVal,$nondnd_numbers_arr)){
						
						if(intval($DND_stdcode)>0){
							$len_std        =	strlen($DND_stdcode);                  		// Fetching stdcode length to compare with Landline numbers//
							$std_number		=	substr($numberVal,0,$len_std);        	// Removing numbers equal to std code length//
							if(intval($std_number)	==	intval($DND_stdcode)){        		// Checking wether std code and removed numbers are equal //
								$final_number 	=  substr($numberVal,$len_std);         // Removing stdcode from the number  //
							}else{
								$final_number	=  $numberVal;                        	// No check if number's digit doesnot match with stdcode i.e mobile number//
							}
						}else{
							$final_number	=  $numberVal;
						}
						$green_list[] = $final_number;
					}
				}
            }
		}
		return array("red_list"=>$red_list, "green_list"=>$green_list);
		
		
	}
	function isLinkedWithPaidContracts($DNDNumber){
		$paid_info = false;
		$sqlMatchedContracts = "SELECT group_concat(DISTINCT parentid SEPARATOR '\", \"') as parentids FROM tbl_companymaster_search WHERE MATCH(phone_search) AGAINST('".$DNDNumber."')";
		$resMatchedContracts = parent::execQuery($sqlMatchedContracts, $this->conn_iro);
		if($resMatchedContracts && parent::numRows($resMatchedContracts)>0){
			$row_matched_contracts	=	parent::fetchData($resMatchedContracts);
			if(trim($row_matched_contracts['parentids'])!=''){
				$sqlPaidContractsInfo = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid IN (\"" . trim($row_matched_contracts['parentids']) . "\") AND balance > 0 LIMIT 1";
				$resPaidContractsInfo = parent::execQuery($sqlPaidContractsInfo, $this->conn_fin_slave);
				if($resPaidContractsInfo && parent::numRows($resPaidContractsInfo)>0){
					$paid_info = true;
				}                       
			} 
		}
		return $paid_info;
	}
	function getStateInfo(){
		$resultArr = array();
		$stateInfoArr = array();
		$sqlStateInfo = "SELECT DISTINCT st_name, state_id FROM state_master WHERE country_id = '98' ORDER by st_name";
		$resStateInfo = parent::execQuery($sqlStateInfo, $this->conn_local);
		if($resStateInfo && parent::numRows($resStateInfo)>0){
			while($row_state_info	=	parent::fetchData($resStateInfo)){
				$stateInfoArr[] = $row_state_info; 
			}
		}
		if(count($stateInfoArr)>0){
			$message = "State Data Found";
			$resultArr['data']				=	$stateInfoArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "State Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	
	function getCityInfo(){
		
		$state_id = $this->params['state_id'];
		
		if(intval($state_id)<=0){
			$state_id = $this->getStateId();
		}
		
		
		$resultArr   = array();
		$cityInfoArr = array();
		if(intval($state_id)>0){
			$sqlCityInfo = "SELECT ct_name, city_id,state_name FROM city_master WHERE state_id ='".$state_id."' AND DE_display=1 AND display_flag=1 AND ct_name!='' ORDER BY ct_name";
			$resCityInfo = parent::execQuery($sqlCityInfo, $this->conn_local);
			if($resCityInfo && parent::numRows($resCityInfo)>0){
				while($row_city_info	=	parent::fetchData($resCityInfo)){
					$cityInfoArr[] = $row_city_info; 
				}
			}
		}
		if(count($cityInfoArr)>0){
			$message = "City Data Found";
			$resultArr['data']				=	$cityInfoArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "City Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	function getStateId(){
		$state_id = '';
		$sqlStateId = "SELECT state_id FROM city_master WHERE ct_name = '".$this->data_city."'";
		$resStateId = parent::execQuery($sqlStateId, $this->conn_local);
		if($resStateId && parent::numRows($resStateId)>0){
			$row_state_id = parent::fetchData($resStateId);
			$state_id = trim($row_state_id['state_id']);
		}
		return $state_id;
	}
	function cityAutoSuggest(){
		$term = trim($this->params['term']);
		$remote = trim($this->params['remote']);
		
		$cond = '';
		if($remote == 1){
			$cond = " city_id not in ('8','11','29','34','19','25','5','1') AND ";
		}
		$cityListArr = array();
		if(!empty($term)){
			$sqlCityList = "SELECT ct_name,stdcode FROM city_master WHERE ".$cond." ct_name LIKE '" . $term . "%' AND country_id ='98' AND allow_data = '1' AND DE_display=1 AND display_flag=1  LIMIT 10";
			$resCityList = parent::execQuery($sqlCityList, $this->conn_local);
			if($resCityList && parent::numRows($resCityList)>0){
				while($row_citylist = parent::fetchData($resCityList)) {
					$cityname 	= trim(ucwords(strtolower($row_citylist['ct_name'])));
					$stdcode 	= trim($row_citylist['stdcode']);
					$cityListArr[$cityname]['std'] = $stdcode;
				}
			}
		}
		if(count($cityListArr)>0){
			$message = "City Data Found";
			$resultArr['data']				=	$cityListArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "City Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	function getPincodeInfo(){
		
		$area 		= trim($this->params['area']);
		$city 		= trim($this->params['city']);
		$pincode 	= trim($this->params['pincode']);
		$auto		= trim($this->params['autosuggest']);
		
		$resultArr = array();
		$pincodeInfoArr = array();
		$condition = "";
		$limit_cond = "";
		if(!empty($city)){
			$condition .= " (data_city = '".addslashes($city)."' OR city = '".addslashes($city)."') AND ";	
		}
		
		if(!empty($area)){
			$condition .= " areaname like '%".$area."%' AND ";	
		}
		if(intval($pincode)>0){
			$condition 	.=	" pincode LIKE '".addslashes($pincode)."%' AND ";
			$limit_cond	=	" LIMIT 10 ";
		}
		$groupby = '';
		if($auto == 1){
			$groupby = " GROUP BY pincode ";
		}
		if(empty($condition)){
			$condition .= " data_city = '".addslashes($this->data_city)."' AND ";	
		}
		$sqlPincodeInfo = "SELECT DISTINCT pincode,stdcode,latitude_final,longitude_final FROM  tbl_areamaster_consolidated_v3 WHERE ".$condition." display_flag = 1 AND type_flag = 1 AND pincode IS NOT NULL ".$groupby." ORDER BY pincode ".$limit_cond." ";
		$resPincodeInfo = parent::execQuery($sqlPincodeInfo, $this->conn_local);
		if($resPincodeInfo && parent::numRows($resPincodeInfo)>0){
			while($row_pincode_info	=	parent::fetchData($resPincodeInfo)){
				$pincodeval 		= trim($row_pincode_info['pincode']);
				$latitude_final 	= trim($row_pincode_info['latitude_final']);
				$longitude_final 	= trim($row_pincode_info['longitude_final']);
				$stdcodevl = trim($row_pincode_info['stdcode']); 
				if($stdcodevl[0]=='0'){
					$stdcode = substr($stdcodevl,1);
				}
				else{
					$stdcode = $stdcodevl;
				}
				$pincodeInfoArr[$pincodeval]['std'] = $stdcode;
				$pincodeInfoArr[$pincodeval]['lat'] = $latitude_final;
				$pincodeInfoArr[$pincodeval]['lon'] = $longitude_final;
				
			}
		}
		if(count($pincodeInfoArr)>0){
			$message = "Pincode Data Found";
			$resultArr['data']				=	$pincodeInfoArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "Pincode Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
	}
	function getPincodeLookup(){
		
		$pincode 	= trim($this->params['pincode']);
		$resultArr = array();
		$pincodeInfoArr = array();
		if($pincode){
			$sqlPincodeInfo = "SELECT DISTINCT pincode,areaname as area,city,state,data_city FROM tbl_areamaster_consolidated_v3  WHERE pincode='".$pincode."' and type_flag=1 AND display_flag=1 AND pincode IS NOT NULL";
			$resPincodeInfo = parent::execQuery($sqlPincodeInfo, $this->conn_local);
			if($resPincodeInfo && parent::numRows($resPincodeInfo)>0){
				while($row_pincode_info	=	parent::fetchData($resPincodeInfo)){
					$pincodeInfoArr[] 	=	 $row_pincode_info;
				}
			}
		}
		if(count($pincodeInfoArr)>0){
			$message = "Pincode Data Found";
			$resultArr['data']				=	$pincodeInfoArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "Pincode Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
	}
	function getAreaInfo(){
		
		$search 	= trim($this->params['search']);
		$city 		= trim($this->params['city']);
		
		if(empty($search)){
			$message = "search params is mandatory to get area info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
		if(empty($city)){
			$message = "City is mandatory to get area info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
				
		$paramsSend					= array();
		$paramsSend['rquest'] 		= "get_area";
		$paramsSend['type'] 		= "1";
		$paramsSend['search'] 		= $search;
		$paramsSend['city'] 		= $city;
		$paramsSend['limit'] 		= "10";
		$paramsSend['module'] 		= $this->module;
		
		$area_info_url 			= $this->jdbox_url."services/location_api.php";
		$area_resp 				= json_decode($this->curlCall($area_info_url,$paramsSend),true);
		//data['result']['areaname']['areaname'] is area
		if($area_resp['numRows'] >0 ){
			$message = "Area Data Found";
			$resultArr['data']				=	array_unique($area_resp, SORT_REGULAR);
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;			
		}else{
			$message = "Area Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		return $resultArr;
	}
	
	function areaAutoSuggest(){
		$term = trim($this->params['term']);
		$areaListArr = array();
		if(!empty($term)){
			$sqlAreaList = "SELECT DISTINCT areaname,stdcode FROM tbl_areamaster_consolidated_v3 WHERE data_city = '".$this->data_city."' AND display_flag = 1 AND type_flag=1 AND areaname LIKE '".$term."%' ORDER BY areaname";
			$resAreaList = parent::execQuery($sqlAreaList, $this->conn_local);
			if($resAreaList && parent::numRows($resAreaList)>0){
				while($row_arealist = parent::fetchData($resAreaList)) {
					$areaname 	= trim(ucwords(strtolower($row_arealist['areaname'])));
					$stdcode 	= trim($row_arealist['stdcode']);
					$areaListArr[$areaname]['std'] = $stdcode;
				}
			}
		}
		if(count($areaListArr)>0){
			$message = "Area Data Found";
			$resultArr['data']				=	$areaListArr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "Area Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	
	function getStreetInfo(){
		
		$search 	= trim($this->params['search']);
		$city 		= trim($this->params['city']);
		$area 		= trim($this->params['area']);
		$pincode 	= trim($this->params['pincode']);
		
		if(empty($search)){
			$message = "search params is mandatory to get street info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
		if(empty($city)){
			$message = "City is mandatory to get street info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
				
		$paramsSend					= array();
		$paramsSend['rquest'] 		= "get_area";
		$paramsSend['type'] 		= "3";
		$paramsSend['search'] 		= $search;
		$paramsSend['city'] 		= $city;
		if(!empty($area)){
			$paramsSend['parent_area'] 	= $area;
		}
		if(intval($pincode)){
			$paramsSend['pincode'] 	= $pincode;
		}
		$paramsSend['limit'] 		= "10";
		$paramsSend['module'] 		= $this->module;
		
		$street_info_url 			= $this->jdbox_url."services/location_api.php";
		$street_resp 				= json_decode($this->curlCall($street_info_url,$paramsSend),true);
		//data['result']['street']['areaname'] is street
		if($street_resp['numRows'] >0 ){
			$message = "Street Data Found";
			$resultArr['data']				=	$street_resp;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;			
		}else{
			$message = "Street Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		return $resultArr;
	}
	
	function getLandmarkInfo(){
		
		$search 	= trim($this->params['search']);
		$city 		= trim($this->params['city']);
		$area 		= trim($this->params['area']);
		$pincode 	= trim($this->params['pincode']);
		
		if(empty($search)){
			$message = "search params is mandatory to get landmark info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
		if(empty($city)){
			$message = "City is mandatory to get landmark info.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
				
		$paramsSend					= array();
		$paramsSend['rquest'] 		= "get_area";
		$paramsSend['type'] 		= "2";
		$paramsSend['search'] 		= $search;
		$paramsSend['city'] 		= $city;
		if(!empty($area)){
			$paramsSend['parent_area'] 	= $area;
		}
		if(intval($pincode)){
			$paramsSend['pincode'] 	= $pincode;
		}
		$paramsSend['limit'] 		= "10";
		$paramsSend['module'] 		= $this->module;
		
		
		$landmark_info_url 			= $this->jdbox_url."services/location_api.php";
		$landmark_resp 				= json_decode($this->curlCall($landmark_info_url,$paramsSend),true);
		//data['result']['landmark']['areaname'] is landmark
		if($landmark_resp['numRows'] >0 ){
			$message = "Landmark Data Found";
			$resultArr['data']				=	$landmark_resp;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}else{
			$message = "Landmark Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		return $resultArr;
	}
	function getStdCodeInfo(){
		$pincode 	= trim($this->params['pincode']);
		
		if(empty($pincode)){
			$message = "pincode is mandatory to get stdcode";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		$stdcodeval = $this->getstdcodeofpincode($this->data_city,$pincode);
		if(intval($stdcodeval)>0){
			$message = "Stdcode Found.";
			$resultArr['stdcode']			=	$stdcodeval;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}else{
			$message = "Stdcode Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	
	function sourceWiseDuplicacyChk(){
		
		$response_arr = array();
		
		$datasource_arr = array('joinfree','adprogrograms','iro deferred sa','iro deferred ap','transferred','iro transferred'); 
		
		$paid 				= trim($this->params['paid']);
		$data_source 		= trim($this->params['data_source']);
		$contact_details 	= trim($this->params['contact_details']);
		$datasource_date 	= trim($this->params['datasource_date']);
		$updatedOn			= trim($this->params['updatedOn']);
		$companyname		= trim($this->params['companyname']);
		if(($updatedOn == $datasource_date) && ($paid == 0) && in_array(strtolower($data_source),$datasource_arr) && !empty($contact_details)){
			$this->duplicacyLogPaid();
			$this->duplicacyLogNonpaid();
			$sqlDuplicacyCheck = "SELECT a.parentid,a.address,a.contact_person,a.companyname,a.paid FROM tbl_companymaster_search as a JOIN tbl_companymaster_extradetails as b ON a.parentid=b.parentid WHERE MATCH(phone_search) AGAINST('".$contact_details."' IN BOOLEAN MODE) AND freeze = 0 AND mask = 0 AND a.parentid != '".$this->parentid."' ORDER BY b.updatedOn DESC";
			$resDuplicacyCheck = parent::execQuery($sqlDuplicacyCheck, $this->conn_iro);
			if($resDuplicacyCheck && parent::numRows($resDuplicacyCheck)>0){
				while($row_duplicacy	=	parent::fetchData($resDuplicacyCheck)){
					$pid = trim($row_duplicacy['parentid']);
					$response_arr[$pid]['address']				= $row_duplicacy['address'];
					$response_arr[$pid]['contact_person']		= $row_duplicacy['contact_person'];
					$response_arr[$pid]['companyname']			= $row_duplicacy['companyname'];
					$response_arr[$pid]['frcSrc']				= 2;
					$response_arr[$pid]['flgAfterCallStatus']	= 1;
					$response_arr[$pid]['paid'] 				= $row_duplicacy['paid'];
					if($row_duplicacy['paid'] == '1'){
						$response_arr[$pid]['nonpaid']			= 0;
					}else{
						$response_arr[$pid]['nonpaid']			= 1;
					}
				}
			}
		}
		if(count($response_arr)>0){
			$message = "Duplicate Data Found";
			$resultArr['data']				=	$response_arr;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}else{
			$message = "Duplicate Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		
	}
	
	function getPaymentNarrationInfo()
	{
		$resultArr = array();
		$narration_data 		= $this->getNarrationInfo();
		if(count($narration_data['data'])>0){
			$resultArr['narration']	= $narration_data['data'];
		}else{
			$resultArr['narration'] = array();
		}
		$finArr					 	= $this->getFinanceDataLive();
		$PaymentCampaignMasterarr 	= $this->getPaymentCampaignMaster();
		
		$banalcearr= array();
		if(count($finArr)>0){
			foreach($finArr as $campaingid=>$campaignarr){
				$banalcearr[$campaingid]= array('campaingid'=>$campaingid,'campaingnname'=>$PaymentCampaignMasterarr[$campaingid],'balance'=>$campaignarr['balance']);
			}
		}
		$resultArr['balanceinfo']		= $banalcearr;		
		$resultArr['instrumentinfo']	= $this->getinstrumentdetails();
		$resultArr['comsourcedata']		= $this->companySourceDetails();
		
		return $resultArr;
	}
	
	function getinstrumentdetails()
	{
		$resultarr = array();
		
		$sql = "SELECT a.instrumentId, a.parentid, a.instrumentType, a.instrumentAmount, a.tdsAmount, a.version, a.entry_date, a.paymentType ,
		b.accountsRecievedFlag, b.bankSentFlag, b.bankClearanceFlag, b.accountsClearanceFlag, b.finalApprovalFlag,
		c.chequeNo, c.chequeDate,  c.bankBranch, c.bankName, c.location, c.depositDate,
		e.approvalCode,
		f.companyname
		FROM payment_instrument_summary a JOIN
		payment_clearance_details b on a.instrumentid=b.instrumentid LEFT JOIN
		payment_cheque_details c on a.instrumentid=c.instrumentid LEFT JOIN
		payment_cash_details d on a.instrumentid=d.instrumentid LEFT JOIN
		payment_cc_details e on a.instrumentid=e.instrumentid JOIN
		payment_otherdetails f on a.parentid=f.parentid and a.version=f.version
		WHERE a.parentid='".$this->parentid."' ORDER BY a.entry_date DESC";
		
		$res = parent::execQuery($sql, $this->conn_fin);
		if($res && parent::numRows($res)>0)
		{			
			while($row = parent::fetchData($res))
			{				
				$resultarr[$row['instrumentId']] = $row;
			}			
		}		
		return $resultarr;		
	}
	
	
	function companySourceDetails()
	{
		$resultarr= array();
		$sql = "SELECT DISTINCT a.mainsource,b.sName,a.subsource,a.datesource from tbl_company_source a LEFT JOIN source b on a.mainsource=b.sCode WHERE a.contactID ='".$this->parentid."' ORDER BY a.datesource";

        $res = parent::execQuery($sql, $this->conn_local);
        
		if($res && parent::numRows($res)>0)
		{			
			while($row = parent::fetchData($res))
			{
				$resultarr[] = $row;
			}			
		}
		
		return $resultarr;
		
	}
	function getNarrationInfo(){
		$resultArr = array();
		$narrationData = array();
		$sqlNarrationInfo = "SELECT narration FROM tbl_paid_narration WHERE contractid = '".$this->parentid."' ORDER BY creationDt DESC";
		$resNarrationInfo = parent::execQuery($sqlNarrationInfo, $this->conn_local);
		if($resNarrationInfo && parent::numRows($resNarrationInfo)>0){
			$i = 1;
			while($row_narration = parent::fetchData($resNarrationInfo)){
				$narrationData[]	= $row_narration['narration'];
			}
		}
		if(count($narrationData)>0){
			$message = "Narration Data Found";
			$resultArr['data']				=	$narrationData;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
			
		}else{
			$message = "Narration Data Not Found.";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	private function duplicacyLogPaid(){ 
		$sqlDupLogPaid	=  "INSERT INTO tbl_duplicacy_records_shadow 
							SET 
							parentid 		= '".$this->parentid."',
							companyname		= '".addslashes($this->params['companyname'])."',
							updatedby		= '".$this->ucode."',
							insertdate  	= '".$this->current_date."',
							data_source 	= '".$this->params['data_source']."',
							deleted_flag 	= '0'
							ON DUPLICATE KEY UPDATE
							companyname		= '".addslashes($this->params['companyname'])."',
							updatedby		= '".$this->ucode."',
							insertdate  	= '".$this->current_date."',
							data_source 	= '".$this->params['data_source']."',
							deleted_flag 	= '0'";
		$resDupLogPaid = parent::execQuery($sqlDupLogPaid, $this->conn_local);
	}	
	private function duplicacyLogNonpaid(){ 
		$sqlDupLogNonpaid = "INSERT INTO tbl_tme_np
							 SET 
							 parentid 		= '".$this->parentid."',
							 tmeid 			= '".$this->ucode."',
							 datetime 		= '".$this->current_date."',
							 entryby_flag 	= '1',
							 deleted_flag 	= '0'
							 ON DUPLICATE KEY UPDATE 
							 tmeid 			= '".$this->ucode."',
							 datetime 		= '".$this->current_date."',
							 entryby_flag 	= '1',
							 deleted_flag 	= '0'";
		$resDupLogNonpaid = parent::execQuery($sqlDupLogNonpaid, $this->conn_local);
	}
	function addslashesArray($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
	function getValidCategories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_merge(array_unique(array_filter($final_catids_arr)));
		}
		return $final_catids_arr;	
	}
	function imagedetails()
	{
		$resultarr['propicimage'] = $this->getImageProPic();
		$resultarr['docimage'] = $this->getImageDoc();
		$resultarr['advimage'] = $this->getImageAdv();
		
		return $resultarr;
	}
	
	function getImageProPic()
	{
		$docid = $this->params['docid'];
		$params_sess = array();		
        $url = WEB_SERVICES_API . '/web_services/vlc.php?docid='.$docid.'&city='.$this->data_city['data_city'].'&mode=tme&media=c&data=l&group=1';
        $content = $this->curlCall($url);
        $retArr	=	 json_decode($content,true);
        $respArr	=	array();
        if (count($retArr) > 0) {
			$i=0;
			foreach($retArr as $key1=>$value1) { 
				foreach($value1 as $key2=>$value2) {
					foreach($value2 as $value) {
						$respArr[$i][$value['product_id']]['product_url']		=	$value['product_url'];
						$respArr[$i][$value['product_id']]['product_thumb_url']	=	$value['product_thumb_url'];
						$respArr[$i][$value['product_id']]['upload_by']			=	$value['upload_by'];
						$respArr[$i][$value['product_id']]['docid']				=	$value['docid'];
						$respArr[$i][$value['product_id']]['catalogue_id']		=	$value['catalogue_id'];
						$respArr[$i][$value['product_id']]['catalogue_name']	=	stripslashes($value['catalogue_name']);
						$respArr[$i][$value['product_id']]['product_id']		=	$value['product_id'];
						$respArr[$i][$value['product_id']]['create_date']		=	$value['create_date'];
						$respArr[$i][$value['product_id']]['approved']			=	$value['approved'];
						$respArr[$i][$value['product_id']]['approved_by']		=	$value['approved_by'];
						$respArr[$i][$value['product_id']]['approved_datetime']	=	$value['approved_datetime'];$i++;
					}
				}
			}
		}
		
        return $respArr;
	}
	
	function getImageDoc()
	{
		$docid = $this->params['docid'];
		$params_sess = array();		
        $url = WEB_SERVICES_API . '/web_services/vlc.php?docid='.$docid.'&city='.$this->data_city.'&mode=tme&media=d&data=';
        
        $content = $this->curlCall($url);
        $retArr	=	 json_decode($content,true);
        $respArr	=	array();
        $i=0;
        if (count($retArr) > 0) {
			foreach($retArr[$docid]['d'] as $value) {
				if($value['file_url'] || $value['file_thumb_url'])
				{
					$respArr[$i]['file_url']	=	$value['file_url'];
					$respArr[$i]['file_thumb_url']	=	$value['file_thumb_url'];
					$i++;
				}
				
				
			}
		}
        return $respArr;
	}
	
	function getImageAdv() 
	{
		$docid = $this->params['docid'];
		$params_sess = array();
		$url = WEB_SERVICES_API . '/web_services/vlc.php?docid='.$docid.'&city='.$this->data_city.'&mode=tme&media=a&data=';
        
        $content = $this->curlCall($url);
        $retArr	=	 json_decode($content,true);
        $respArr	=	array();
        $i=0;
        if (count($retArr) > 0) {
			foreach($retArr[$this->docid]['a'] as $value) {
				
				if($value['file_url'] || $value['file_thumb_url'])
				{
					$respArr[$i]['file_url']	=	$value['file_url'];
					$respArr[$i]['file_thumb_url']	=	$value['file_thumb_url'];
					$i++;
				}
				
				
				
			}
		}
        return $respArr;
	}
	
	public function correctIncorrectInfo()
	{
		$return_data = array();
		$sqlCorrectIncorrect = "SELECT parentid FROM tbl_correct_incorrect WHERE parentid='".$this->parentid."' ORDER by entry_date DESC LIMIT 1";
		$resCorrectIncorrect = parent::execQuery($sqlCorrectIncorrect,$this->conn_local);
		if($resCorrectIncorrect && parent::numRows($resCorrectIncorrect)>0){
			$return_data['results']['data']= 1;
		}else
		{	
			$return_data['results']['data']= 0;		
		}
		
		$sqlEditDetails	= "SELECT edited_data FROM tbl_companydetails_edit WHERE parentid='".$this->parentid."' ORDER BY entry_date DESC LIMIT 1";
		$resEditDetails	= parent::execQuery($sqlEditDetails,$this->conn_local);
		
		if($resEditDetails && parent::numRows($resEditDetails)>0){
			$row_edited_data = parent::fetchData($resEditDetails);
			$return_data['results']['edited_data']	= $row_edited_data['edited_data'];
		}else{
			$return_data['results']['edited_data']	= array();
		}
		$return_data['error']['code']		=	0;
		return $return_data;
	}
	public function checkLeadContract(){
		$resultArr = array();
		$lead      = 0;
		$getContractType = "SELECT parentid FROM d_jds.tbl_new_retention WHERE parentid='".$this->parentid."' limit 1";
		$resContractType =  parent::execQuery($getContractType,$this->conn_local);
		$leadCount       = parent::numRows($resContractType);
		if($leadCount>0){
			$lead = 1;			
		}else{ //check entry in lead tbl
			$getContractType1 = "SELECT parentid FROM d_jds.tbl_new_lead WHERE parentid='".$this->parentid."' limit 1";
			$resContractType1 =  parent::execQuery($getContractType1,$this->conn_local);
			$leadCount1       = parent::numRows($resContractType1);
			if($leadCount1>0){
				$lead = 1;
				
			}else{
				$lead = 0;				
			}
		}		
		return $lead;
	}
	public function getMandateinfo(){
		$resultArr = array();
		$sql_mandate_info    = "SELECT parentid, billAmount, billing_cycle from db_ecs_billing.ecs_bill_details where parentid='".$this->parentid."' order by duedate  desc limit 1";
		$res_mandate_info    = parent::execQuery($sql_mandate_info,$this->conn_fin);
		if(parent::numRows($res_mandate_info)>0){
			$row_mandate_info =	parent::fetchData($res_mandate_info);
			
			$message = "Mandate Info Found";
			$resultArr['data']				=	$row_mandate_info;
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		else{
			$message = "Mandate Info Not Found";
			$resultArr['error']['code']		=	0;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
	}
	public function sendTvAdNAppLink(){
		// this function needs all params needed for both sendTvAdLink & sendAppLink function i.e mobile,email,empNum & mandatroty params
		$res_tv_ad_link = $this->sendTvAdLink();

		$result = array();
		if($res_tv_ad_link['error']['code']==0){
			$result['ad_link']['code'] = 0;
			$result['ad_link']['msg'] = $res_tv_ad_link['error']['msg'];
		}
		else{
			$result['ad_link']['code'] = 1;
			$result['ad_link']['msg']   = $res_tv_ad_link['error']['msg'];
		}
		$send_app_link             = $this->sendAppLink();
		if($send_app_link['error']['code']==0){
			$result['app_link']['code'] = 0;
			$result['app_link']['msg'] = 'App Link Success';
		}
		else{
			$result['app_link']['code'] = 1;
			$result['app_link']['msg'] = 'failed';
		}
		return $result;
	}
	public function sendTvAdLink(){
		$postarr 	= array();
		$curl_url   = $this->jdbox_url."/services/newTvcs_service.php";
		$postarr['mobile']	   =  $this->params['mobile'];
		$postarr['email']	   =  $this->params['email'];
		$postarr['parentid']   =  $this->parentid;
		$postarr['ucode'] 	   =  $this->ucode;
		$postarr['data_city']  =  $this->data_city;
		$postarr['uname']	   =  $this->uname;
		$postarr['ad_id']	   =  $this->params['ad_id'];
		$postarr['module']	   =  $this->module;
		$res_tv_ad_link = array();
		$res_tv_ad_link	=	json_decode($this->curlCall($curl_url,$postarr),true);
		return $res_tv_ad_link;
	}

	public function sendAppLink(){
		$return_data = array();
		$empNum 				= $this->params['empNum'];
		$mobile 				= $this->params['mobile'];
		$paidstats	 			= $this->getpaidStat();
		if(!empty($empNum)){
			$curlParams_temp =	array();
			$url			 =	"http://win.justdial.com/26june2015/shareReferrals.php?mobile=".$empNum."&source=2";
			$curlParams_temp['formate'] 	= 	'basic';
			$curlParams_temp['method'] 		=	'get';
			$getReferral_data = json_decode($this->curlCall($url,$curlParams_temp),true);
			if(!is_array($getReferral_data) || isset($getReferral_data['error'])) {
				$return_data['error']['code']  	= 1;
				$return_data['error']['msg']  	= 'SMS has not been sent..';
				return $return_data;
			}
		}else{
			$return_data['error']['code']  	= 1;
			$return_data['error']['msg']  	= 'Employee Mobile Number is missing.';
			return $return_data;
		}
		$sms_text 		= 	'';
		$inboxDemo 		= 	"";		
		$hindiArr 	= array("mumbai","delhi","pune","ahmedabad","jaipur","chandigarh"); //hindi
		$englishArr = array("bangalore","chennai","hyderabad","kolkata","coimbatore"); //english
		
		$getMainZone    = "select main_zone from d_jds.tbl_zone_cities where Cities='".trim($this->params['city'])."'"; 
		$resMainZone	= parent::execQuery($getMainZone,$this->conn_local);
		$count 			= parent::numRows($resMainZone);
		if($count > 0){
			$rowMainZone = parent::fetchData($resMainZone,$this->conn_local);
			$main_zone   = $rowMainZone['main_zone'];
			if(in_array(strtolower($main_zone), $hindiArr)){
				$inboxDemolink = 'https://youtu.be/b0b49xPKSIo';
			}else if(in_array(strtolower($main_zone), $englishArr)){
				$inboxDemolink = "https://youtu.be/BKYfSZjMts8"; //english
			}else{
				$inboxDemolink = 'https://youtu.be/b0b49xPKSIo';
			}
		}else{
			$inboxDemolink = 'https://youtu.be/b0b49xPKSIo';
		}		
		$value_str		   =	'';
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['module']       	= 'tme';
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_generalinfo_shadow"=>"companyname",
			));
			$tempdata = $this->mongo_obj->getShadowData($mongo_inputs);
		}else{
			$sqlGeninfoShadow  = "SELECT companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
			$resGeninfoShadow = parent::execQuery($sqlGeninfoShadow, $this->conn_temp);
			if($resGeninfoShadow && parent::numRows($resGeninfoShadow)>0){
				$row_geninfo_shadow = parent::fetchData($resGeninfoShadow);
				$tempdata['tbl_companymaster_generalinfo_shadow'] = $row_geninfo_shadow;
			}
		}
		$companyname  = addslashes(stripslashes($tempdata['tbl_companymaster_generalinfo_shadow']['companyname']));
		if($paidstats == 1){
			$sms_text  = "$companyname,\n\n";
			$sms_text .= "IMPORTANT NOTICE\n";
			$sms_text .= "Receive & Manage your Business Enquiries on Justdial App. It\'s fast with instant notifications & great analytical tools!\n";
			$sms_text .= "SMS Feedback will be discontinued on 31st August\n";
			$sms_text .= "Click and Download NOW.\n";
			$sms_text .= "https://jsdl.in/appqdl\n";
			$sms_text .= "Click to Watch this 2 min video to understand how to download & check Feedback\n";
			$sms_text .= $inboxDemolink."\n";
		}else{
			$sms_text  = "Download all new, Super-fast JD App\n\n";
			$sms_text .= "https://jsdl.in/appqdl\n";
			$sms_text .= "What\'s new?\n";
			$sms_text .= "- News, trending stories & Live TV on JD Social\n";
			$sms_text .= "- Chat messenger to connect with businesses\n";
		}
		if(!empty($mobile)){
			$mobileNumArr = explode(',',$mobile);
			$mobileNumArr = array_unique(array_filter($mobileNumArr));
			if(count($mobileNumArr)>0){
				foreach($mobileNumArr as $mobilenum){
					if(strlen($mobilenum) == 10){
						$sqlDNCInfo= "SELECT * FROM dnc.dndlist  WHERE dndnumber=".$mobilenum." AND (safe_till <= NOW()  OR is_safe=0) and is_deleted=0 ";
						$resDNCInfo	= parent::execQuery($sqlDNCInfo,$this->conn_dnc);
						$num_dnc 	= parent::numRows($resDNCInfo);
						if($num_dnc>0){
							$value_str = '';
							break;
						}else{
							$value_str .= "('CB_INTIMATION','".$mobilenum."','".$sms_text."','Y'),";
						}
					}
				}
			}
			$resSendAppLink = false;
			if($value_str!=''){
				$value_str	= substr($value_str,0,-1);
				$sqlSendAppLink	= "INSERT ignore INTO db_jd_emailsms.tmeappentry(TMEName,EmpMobile,SmsText,SmsReady) VALUES ".$value_str;
				$resSendAppLink = parent::execQuery($sqlSendAppLink, $this->conn_fin);
			}
			if($resSendAppLink) {
				$return_data['error']['code']  	= 0;
				$return_data['error']['msg']  	= 'SMS has been sent to the client.';
				return $return_data;
			}else { // not inserted
				$return_data['error']['code']  	= 1;
				$return_data['error']['msg']  	= 'SMS has not been sent as we found number in DNC.';
				return $return_data;
			}
		}else{
			$return_data['error']['code']  	= 1;
			$return_data['error']['msg']  	= 'Mobile Number is missing.';
			return $return_data;
		}
	}

	public function getMatchedActiveData(){
		$id = $this->params['id'];

		$res_arr = array();
		$select_mobile = "SELECT clinum FROM db_iro.tbl_apptransfer WHERE (clinum='".$id."' OR extno='".$id."' )  ORDER BY entrydate DESC LIMIT 1"; 
		$fetch_result  = parent::execQuery($select_mobile,$this->conn_iro);
		if(parent::numRows($fetch_result,$this->conn_iro) > 0) {
			$row_mobile = parent::fetchData($fetch_result,$this->conn_iro);
			$mobileNum	= $row_mobile['clinum'];
		}else{
			$mobileNum	=	$id;
		}
		
		$subSelect = "SELECT parentid,phone_search FROM tbl_companymaster_search WHERE MATCH (phone_search) AGAINST ('" . ltrim($mobileNum,'0') . "' IN BOOLEAN MODE) AND expired = 0";
		$subResult = parent::execQuery($subSelect,$this->conn_iro);
		$i	=	0;
		if (parent::numRows($subResult) > 0) {
			$res_arr['error']['code']  	= 0;
			while ($subRow = parent::fetchData($subResult)) {
				$findextra	=	$this->fetchGeneralMain($subRow['parentid']);
				
				$res_arr['subdata'][$i]['parentid']	 	= 	$subRow['parentid'];
				$res_arr['subdata'][$i]['phone_search']	= 	$subRow['phone_search'];
				$res_arr['subdata'][$i]['contact_person']	= 	$findextra['contact_person'];
				$res_arr['subdata'][$i]['companyname']	 	= 	$findextra['companyname'];
				$res_arr['subdata'][$i]['paid']			= 	0;
			}
		}else{
			$res_arr['error']['code']  	= 1;
			$res_arr['error']['msg']  	= 'No record found';
		}
        return $res_arr;
	}
	private function fetchGeneralMain($contractCode=''){		
		if($contractCode != '') {
			$parentid	=	$contractCode;
		} else {
			$parentid	=	$this->parentid;
		}
		$gen_arr_main   = array();
		//$sql_geninfo 	= "SELECT parentid,sphinx_id,companyname,building_name,street,landmark,pincode,area,subarea,contact_person,mobile,landline,email,website,state,city,paid FROM tbl_companymaster_generalinfo WHERE parentid = '".$parentid."'";
		//$res_geninfo 	= parent::execQuery($sql_geninfo,$this->conn_iro);
		
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $parentid;
		$comp_params['fields']		= 'parentid,sphinx_id,companyname,building_name,street,landmark,pincode,area,subarea,contact_person,mobile,landline,email,website,state,city,paid';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'allDetailClass';

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1'){
			//$row_geninfo = parent::fetchData($res_geninfo);
			$row_geninfo  	= $comp_api_arr['results']['data'][$parentid];	
			$gen_arr_main  	= $row_geninfo;	
				
		}
		$gen_arr_main['count']	= count($comp_api_arr['results']['data']);
		
        return $gen_arr_main;
    }
	public function updateClientInfo(){
		
		$disp_flag 	= trim($this->params['disp_flag']);
		$disp_value = trim($this->params['disp_value']);
		
		if($disp_flag == 2){
			$sqlUpdateClientInfo = "UPDATE tbl_walkin_client_details SET final_status = '".$disp_value."',end_time = NOW() WHERE parentid = '".$this->parentid."' AND tmecode = '".$this->ucode."' AND (final_status = '' OR final_status IS NULL) ";
			$resUpdateClientInfo = parent::execQuery($sqlUpdateClientInfo,$this->conn_local);
		}else{
			$sqlUpdateClientInfo = "UPDATE tbl_walkin_client_details SET start_time = NOW() WHERE parentid = '".$this->parentid."' AND tmecode = '".$this->ucode."' AND (final_status = '' OR final_status IS NULL) ";
			$resUpdateClientInfo = parent::execQuery($sqlUpdateClientInfo,$this->conn_local);
		}
		$resultArr['error']['code']		=	0;
		$resultArr['error']['msg']		=	"no error";
		
		return $resultArr;
	}
	public function iroAppTransfer(){
		
		$extn = $this->params['extn'];
		if(strlen($extn)<4){
			$message = "Invalid Extension";
			$resultArr['error']['code']		=	1;
			$resultArr['error']['msg']		=	$message;
			return $resultArr;
		}
		$currdate    = date('Y-m-d H:i:s',mktime(date("H"), date("i")-20, date("s"), date("m")  , date("d"), date("Y"))); 
		$currdate_log    = date("Y-m-d H:i:s");
		
		
		if(strlen($extn) == 4){
			$cond =	'(ExtNo ='.$extn.' OR Clinum ='.$extn.' )';
		}else if(strlen($extn) > 4){
			$cond =	'(CallerMobile ='.substr($extn,-10).'  OR CallerPhone ='.$extn.' OR Clinum ='.$extn.')';
		}
		$sql_query_Transfer = "SELECT Clinum,
							IroCode,
							IroName,
							CallerName,
							CallerMobile,
							CallerPhone,
							companyname,
							ExtNo,
							Parentid,
							Category,
							City,
							EntryDate,
							Uniquefield,
							type,
							f12_id
							from db_iro.tbl_apptransfer where  ".$cond." and 
							EntryDate >='".$currdate."' and tmetransferflag = 0 order by EntryDate desc limit 1";
		$res_query_transfer = parent::execQuery($sql_query_Transfer,$this->conn_local);

		if(parent::numRows($res_query_transfer)>0){
			$res_fetch_Data =	parent::fetchData($res_query_transfer);
			/************************For Piad/Expired/Save as Nonpaid Check***************************/
				
			$sql_query_paidcont 	= "SELECT count(parentid) FROM db_finance.tbl_companymaster_finance WHERE parentid ='".$res_fetch_Data['Parentid']."' AND balance>0 AND expired=0 AND freeze =0 AND mask =0 GROUP BY parentid "; 
			
			$execqry_paidcont 		= parent::execQuery($sql_query_paidcont,$this->conn_fin);
			$numrowstme_paidcont 	= parent::numRows($execqry_paidcont,$this->conn_fin);
			if($numrowstme_paidcont >0){
				$transferDetails['paid']		=	1;				
			}
			else{
				$transferDetails['paid']		=	0;
			}

			$qry_saveasnonpaid = "SELECT count(*) as cnt FROM db_iro.tbl_apptransfer WHERE Parentid='".$res_fetch_Data['Parentid']."' AND Uniquefield = '".$res_fetch_Data['Uniquefield']."'";
			$res_saveasnonpaid = parent::execQuery($qry_saveasnonpaid,$this->conn_local);
			$numrowstme_saveas_nonpaid = parent::fetchData($res_saveasnonpaid);
			if($numrowstme_saveas_nonpaid['cnt'] == 0){
				$transferDetails['saveas_nonpaid']		=	1;
			}
			else{
				$transferDetails['saveas_nonpaid']		=	0;
			}
			
			/*$qry_area_pincode 					= "SELECT area,pincode FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$res_fetch_Data['Parentid']."'";
				$res_area_pincode 					= parent::execQuery($qry_area_pincode,$this->conn_local);
				$res_num_area_pincode 				= parent::numRows($res_area_pincode,$this->conn_local);
				$numrowstme_area_pincode 			= parent::fetchData($res_area_pincode,$this->conn_local);
				if($res_num_area_pincode>0)
				{
					
					$transferDetails['pincode']		=	$numrowstme_area_pincode['pincode'];
					$transferDetails['area']		=	$numrowstme_area_pincode['area'];
				}
				
			*/
			
				$mongo_url  = $this->jdbox_url."services/mongoWrapper.php";
				
				$mongo_data			    	     = array();
				$mongo_data['action']	    	 = 'getdata';
				$mongo_data['post_data']    	 = '1';
				$mongo_data['parentid'] 	 	 = $res_fetch_Data['Parentid'];
				$mongo_data['table']	         = 'tbl_companymaster_generalinfo_shadow';
				$mongo_data['data_city'] 		 = $res_fetch_Data['City'];
				$mongo_data['module']			 = 'TME';
				
				$getLastDealClose_data   = json_decode($this->curlCall_extra($mongo_url,$mongo_data),true);
				//echo "<br>1829:--";print_r($generalinfo_arr);
				//echo "<br>:----";die;
				
				if($getLastDealClose_data['pincode'] == '' || $getLastDealClose_data['area'] == '')
				{
					//$qry_area_pincode 					= "SELECT area,pincode FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$res_fetch_Data['Parentid']."'";
					//$res_area_pincode 					= parent::execQuery($qry_area_pincode,$this->conn_local);
					$comp_params = array();
					$comp_params['data_city'] 	= $this->data_city;
					$comp_params['table'] 		= 'gen_info_id';		
					$comp_params['parentid'] 	= $res_fetch_Data['Parentid'];
					$comp_params['fields']		= 'area,pincode';
					$comp_params['action']		= 'fetchdata';
					$comp_params['page']		= 'allDetailClass';
					
					$comp_api_res 	= '';
					$comp_api_arr	= array();
					$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
					if($comp_api_res!=''){
						$comp_api_arr 	= json_decode($comp_api_res,TRUE);
					}
					if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
					{
						$res_num_area_pincode 		= count($comp_api_arr['results']['data']);
						$numrowstme_area_pincode 	= $comp_api_arr['results']['data'][$res_fetch_Data['Parentid']];			
					}
					//$res_num_area_pincode 				= parent::numRows($res_area_pincode);
					 //$numrowstme_area_pincode 			= parent::fetchData($res_area_pincode);
					
					$transferDetails['pincode']		=	$numrowstme_area_pincode['pincode'];
					$transferDetails['area']		=	$numrowstme_area_pincode['area'];

					if($res_num_area_pincode>0)
						$transferDetails['source']		=	'noshadow';
					else
						$transferDetails['source']		=	'noshadowmain';	
				}else
				{
					$transferDetails['pincode']		=	$getLastDealClose_data['pincode'];
					$transferDetails['area']		=	$getLastDealClose_data['area'];
					$transferDetails['source']		=	'noupdate';	
				}
			
			
			$transferDetails[] = $res_fetch_Data;
			
			if(isset($res_fetch_Data['Parentid']))
			{
				 $insertqry_appIro = "INSERT INTO db_iro.tbl_appointment_iro SET 
										parentid='".$res_fetch_Data['Parentid']."',
										ironame='".$res_fetch_Data['IroName']."',
										irocode='".$res_fetch_Data['IroCode']."'
										ON DUPLICATE KEY UPDATE
										ironame='".$res_fetch_Data['IroName']."',
										irocode='".$res_fetch_Data['IroCode']."'";	
				$execInsertappIro =  parent::execQuery($insertqry_appIro,$this->conn_local);
			}

			$retArr['error']['code'] 	=	0;
			$retArr['error']['msg']  	=	"Data Inserted";
			$retArr['data'] = $transferDetails;
		}
		else{
			$retArr['error']['code'] 	=	1;
			$retArr['error']['msg'] 	=	"No Data Found In tbl_apptransfer Table";
		}
		return $retArr;
	}
	public function iroAppSaveExit(){
		
		$IROData = json_decode($this->params['irodata'],1);
		$tmeData = $this->mktgEmpMap();
		$tmesearch_data = $this->getTmeSearchData();
		$tmecode = $tmeData['rowid'];
		$exist_tmecode = $tmesearch_data['tmeCode'];
		$exist_empcode = $tmesearch_data['empCode'];
		
		
		$retArr = array();
		if(count($IROData)>0){
			
			$sqlSaveNExitInfo ="INSERT INTO tbl_saveexit SET 
								parentid	=	'".$IROData['parentid']."',
								tmecode		=	'".$tmecode."',
								city		=	'".$IROData['city']."',
								EntryDate	=	NOW()
								ON DUPLICATE KEY UPDATE
								tmecode		=	'".$tmecode."',
								city		=	'".$IROData['city']."',
								EntryDate	=	NOW()";
			$resSaveNExitInfo = parent::execQuery($sqlSaveNExitInfo,$this->conn_local);
			
			
		if($exist_tmecode!= '' || $exist_tmecode!= null)
		{
			$existTmecode = $exist_tmecode;
			$existEmpcode = $exist_empcode;
		}else
		{
			$existTmecode = $tmecode;
			$existEmpcode = $this->ucode;
		} 
			
			if($IROData['paid'] == 0){
				$sqlUpdateTmeSearch ="UPDATE tbl_tmesearch SET tmecode = '".$existTmecode."', empCode='".$existEmpcode."', data_source = 'TRANSFERRED', datasource_date=NOW() WHERE parentid ='".$IROData['parentid']."'";
				$resUpdateTmeSearch = parent::execQuery($sqlUpdateTmeSearch,$this->conn_local);
			}
		}
		if($resSaveNExitInfo)
		{
			$retArr['error']['code'] 	=	0;
			$retArr['error']['msg'] 	=	"Data Inserted";
		}else
		{
			$retArr['error']['code'] 	=	1;
			$retArr['error']['msg'] 	=	"Data Not Inserted";
		}
		return $retArr;
	}
	
	public function iroAppProceed(){
		
		$IROData = json_decode($this->params['irodata'],1);
		$tmeData = $this->mktgEmpMap();
		$tmesearch_data = $this->getTmeSearchData();
		$tmecode = $tmeData['rowid'];
		$exist_tmecode = $tmesearch_data['tmeCode'];
		$exist_empcode = $tmesearch_data['empCode'];
		
		
				if($IROData['pincode'] != '' && $IROData['area']!= '')
				{
					
					
					if($this->mongo_flag == 1 || $this->mongo_tme == 1)
					{
						
						$mongo_inputs = array();
						$mongo_inputs['module'] 	= 'TME';
						$mongo_inputs['parentid'] 	= $IROData['parentid'];
						$mongo_inputs['data_city'] 	= $IROData['city'];
						
						$mongo_data = array();
					
						$geninfo_tbl 		= "tbl_companymaster_generalinfo_shadow";
						$geninfo_upt = array();
						$geninfo_upt['companyname'] 			= addslashes(stripslashes($companyname));
						$geninfo_upt['area'] 					= addslashes(stripslashes($IROData['area']));
						$geninfo_upt['pincode'] 				= $IROData['pincode'];
						$geninfo_upt['data_city'] 				= addslashes($IROData['city']);
						
						
						$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
						
						$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
						$extrdet_upt = array();
						
						$extrdet_upt['companyname'] 		= addslashes(stripslashes($companyname));
						$extrdet_upt['updatedOn'] = date('Y-m-d H:i:s');
						$extrdet_upt['data_city'] = $IROData['city'];			
						$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
						
						
						$extrdet_ins = array();
						$extrdet_ins['original_date'] 		= date('Y-m-d H:i:s');
						$mongo_data[$extrdet_tbl]['insertdata'] = $extrdet_ins;
						
						$mongo_inputs['table_data'] = $mongo_data;
						$resGeninfoShadow = $this->mongo_obj->updateData($mongo_inputs);
						
						
						
						if($resGeninfoShadow){
						$message = "inserted successfully";
						return $res_arr =  json_encode($this->sendResponse($message,0));
						}else{
							$message = "insertion failed";
							return $res_arr =  json_encode($this->sendResponse($message,1));
						}	
					}
					
				}
				
		$retArr		=	array();
		
		if($IROData['calldis'] == '1')
		{
			$sqlUpdateAppTransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '0' WHERE   Uniquefield = '".$IROData['Uniquefield']."' ";
					$resUpdateAppTransfer 	= parent::execQuery($sqlUpdateAppTransfer,$this->conn_local);
		}else
		{
				$sqlUpdateAppTransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '1' WHERE  Uniquefield = '".$IROData['Uniquefield']."' ";
				$resUpdateAppTransfer 	= parent::execQuery($sqlUpdateAppTransfer,$this->conn_local);
		}
		
		//$sqlUpdateAppTransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '1' WHERE Parentid = '".$IROData['parentid']."' AND Uniquefield = '".$IROData['Uniquefield']."' ";
		//$resUpdateAppTransfer 	= parent::execQuery($sqlUpdateAppTransfer,$this->conn_local);
		
		$sqlUpdateExtraShadow 	= "UPDATE db_iro.tbl_companymaster_extradetails SET updatedby = '".$this->ucode."' WHERE Parentid = '".$IROData['parentid']."'";
		$resUpdateExtraShadow 	= parent::execQuery($sqlUpdateExtraShadow,$this->conn_local);
		
		$sqlUpdateAppointment 	= "UPDATE db_iro.appointment SET AllocatedTo = '".$this->ucode." - ' WHERE Parentid = '".$IROData['parentid']."' AND Uniquefield = '".$IROData['Uniquefield']."' ";
		$resUpdateAppointment	= parent::execQuery($sqlUpdateAppointment,$this->conn_local);
	
		$log_records = date('Y-m-d H:i:s').'IP===>'.$IROData['remote_addr'].'ParentId===>'.$IROData['parentid'].'AllocatedTo===>'.$this->ucode.'QueryAppoint_IRO===>'.$sqlUpdateAppointment.'Appoint_IRO======>'.$sqlUpdateAppTransfer;
		
		$sqlUpdateLogTable 	= "UPDATE db_iro.irotmeapplogs SET transferflag = 1,inserttime_tme = NOW(),tmecode='".$this->ucode."',tmename = '".$this->uname."',tme_logs = '".$this->stringProcess($log_records)."', allocated_tme = '".$this->ucode."' WHERE Parentid = '".$IROData['parentid']."' AND uniquefield = '".$IROData['Uniquefield']."' ";
		$resUpdateLogTable 	= parent::execQuery($sqlUpdateLogTable,$this->conn_local);

		if($exist_tmecode!= '' || $exist_tmecode!= null)
		{
			$existTmecode = $exist_tmecode;
			$existEmpcode = $exist_empcode;
		}else
		{
			$existTmecode = $tmecode;
			$existEmpcode = $this->ucode;
		} 


		if($existTmecode){
			$sqlUpdateTmeSearch ="UPDATE d_jds.tbl_tmesearch SET tmecode ='".$existTmecode."',empCode='".$existEmpcode."',data_source = 'TRANSFERRED', datasource_date=NOW() WHERE parentid ='".$IROData['parentid']."'";
			$resUpdateTmeSearch = parent::execQuery($sqlUpdateTmeSearch,$this->conn_local);
		}		 
		if($resUpdateAppTransfer)
		{
			$retArr['error']['code'] 	=	0;
			$retArr['error']['msg'] 	=	"Data Inserted";
		}else
		{
			$retArr['error']['code'] 	=	1;
			$retArr['error']['msg'] 	=	"Data Not Inserted";
		}
		return $retArr;
	}
	
	public function mktgEmpMap(){
		$sqlTmeCodeInfo ="SELECT rowid FROM d_jds.mktgEmpMap WHERE mktEmpCode ='".$this->ucode."'";
		$resTmeCodeInfo = parent::execQuery($sqlTmeCodeInfo,$this->conn_local);
		if($resTmeCodeInfo && parent::numRows($resTmeCodeInfo)>0){
			$row_tmecode = parent::fetchData($resTmeCodeInfo);
		}
		return $row_tmecode;
	}
	
	public function pincodeChangeLog(){
		
		$resultArr = array();
		
		$prev_pin 	= trim($this->params['prev_pin']);
		$new_pin 	= trim($this->params['new_pin']);
		$prev_area 	= trim($this->params['prev_area']);
		$new_area 	= trim($this->params['new_area']);
		
		$prev_pin = trim(strstr($prev_pin,'('));
		$prev_pin = str_replace(array( '(', ')' ), '', $prev_pin);
		
		$new_pin = trim(strstr($new_pin,'('));
		$new_pin = str_replace(array( '(', ')' ), '', $new_pin);
		
		
		$sqlPincodeChangeLog = "INSERT INTO tbl_pincode_change_logs SET
								parentid 		= 	'".$this->parentid."',
								changed_by		=	'".$this->ucode."',
								date_of_change	=	'".$this->current_date."',
								prev_pincode	=	'".$prev_pin."',
								curr_pincode	=	'".$new_pin."',
								prev_area		=	'".$this->stringProcess($prev_area)."',
								curr_area		=	'".$this->stringProcess($new_area)."'";
		$resPincodeChangeLog = parent::execQuery($sqlPincodeChangeLog,$this->conn_local);
		if($resPincodeChangeLog){
			$resultArr['error']['code'] 	=	0;
			$resultArr['error']['msg']  	=	"Data Inserted";
		}else{
			$resultArr['error']['code'] 	=	1;
			$resultArr['error']['msg']  	=	"Data Not Inserted";
		}
		return $resultArr;
	}
	
	public function areaPincodeRequest(){
		$resultArr = array();
		$params = array();
		$url = $this->jdbox_url."services/location_api.php";
		$params['rquest'] 	= 'new_area_pincode_request';
		$params['parentid'] = $this->parentid;
		$params['area'] 	= $this->params['area'];
		$params['pincode'] 	= $this->params['pincode'];
		$params['city'] 	= $this->data_city;
		$params['data_city']= $this->data_city;
		$params['stdcode'] 	= $this->params['stdcode'];
		$params['type'] 	= 'area';
		$params['ucode'] 	= $this->ucode;
		$params['uname'] 	= $this->uname;
		$params['module'] 	= $this->module;
		
		$content 			= json_decode($this->curlCall($url,$params),true);
		if($content['error']['message']	==	'success'){
			$insertSql = "INSERT INTO area_pincode_request_log
						  SET
						  area 		= '".$this->stringProcess($params['area'])."',
						  pincode 	= '".$params['pincode']."',
						  empcode 	= '".$this->ucode."',
						  insertdate = now()";
					  
			$resSql = parent::execQuery($insertSql,$this->conn_local);
				if($resSql){
					$resultArr['error']['code'] 	=	0;
					$resultArr['error']['msg']  	=	"Data Inserted";
				}else{
					$resultArr['error']['code'] 	=	1;
					$resultArr['error']['msg']  	=	"Data Not Inserted";
				}
		}else{
			$resultArr['error']['code'] 	=	1;
			$resultArr['error']['msg']  	=	"Data Not Inserted";
		}
		return $resultArr;
	}
	
	public function insertMobileFeedback(){
		$resultArr 		= array();
		$feedback_flag 	= 1;
		$user_id 		= $this->ucode;
		$date			= date('Y-m-d H:i:s');
		$parentid		= $this->parentid;
		$reason			= $this->params['reason'];
		$mobilenumber 	= $this->params['mobile'];
				
		$insertSql = "INSERT into tbl_sms_feedback_deactive_log(parentid,feedback_flag,reason,user_id,deactive_date,mobilenumber) values('".$parentid."','".$feedback_flag."','".$this->stringProcess($reason)."','".$user_id."','".$date."','".$mobilenumber."')";
		$resSql = parent::execQuery($insertSql,$this->conn_tme);
		if($resSql){
			$resultArr['error']['code'] = 0;
			$resultArr['error']['msg']  = "Data Inserted";
		}else{
			$resultArr['error']['code'] = 1;
			$resultArr['error']['msg']  = "Data Not Inserted";
		}
		return $resultArr;
	}
	
	public function addSuggestedCity(){
		$resultArr = array();
		$city 	= $this->data_city;
		$ucode 	= $this->ucode;
		$insertSql = "INSERT INTO tbl_suggested_city SET city = '".$city."', empcode = '".$ucode."', insertdate = now()";
		$resSql = parent::execQuery($insertSql,$this->conn_local);
		if($resSql){
			$resultArr['error']['code'] 	=	0;
			$resultArr['error']['msg']  	=	"Data Inserted";
		}else{
			$resultArr['error']['code'] 	=	1;
			$resultArr['error']['msg']  	=	"Data Not Inserted";
		}
		return $resultArr;
	}
	
	public function getPidData(){
		$resultArr 	= array();
		$id 		= $this->params['id'];
		$selectSql 	= "SELECT fname,company,merge_phone as phone FROM freelisting_othercity_data WHERE ad_id = '".$id."' LIMIT 1";
		$resSql 	= parent::execQuery($selectSql,$this->conn_local);
		if($resSql && parent::numRows($resSql)>0){
			$row = parent::fetchData($resSql);
			$data['maindata'] 	= $row;
			$contactDetails 	= $row['phone'];
			
			$subSelect = "SELECT a.parentid as ad_id,b.contact_person as fname,a.compname as company,a.contact_details as phone,a.paidstatus FROM tbl_tmesearch a LEFT JOIN tme_jds.tbl_companymaster_generalinfo_shadow b ON a.parentid=b.parentid WHERE MATCH (a.contact_details) AGAINST ('".$contactDetails."' IN BOOLEAN MODE)";
			$subResult = parent::execQuery($subSelect,$this->conn_local);
			if($subResult && parent::numRows($subResult)>0){
				while($subRow = parent::fetchData($subResult)){
					$data['subdata'][$subRow['ad_id']] = $subRow;
					$data['subdata'][$subRow['ad_id']]['paid']	= $subRow['paidstatus'];
					if($subRow['paidstatus'] == '1'){
						$data['subdata'][$subRow['ad_id']]['nonpaid']	= 0;
					}else{
						$data['subdata'][$subRow['ad_id']]['nonpaid']	= 1;
					}
				}
				$resultArr['error']['code'] 	=	0;
				$resultArr['error']['msg']  	=	"Data Found";
				$resultArr['data']  			=	$data;
			}
		}
		else
		{
			$resultArr['error']['code'] 	=	1;
			$resultArr['error']['msg']  	=	"Data Not Found";
		}
		return $resultArr;
	}
	
	public function getJdrrDetails(){
		$resultArr = array();
		$jdrr_details = array();
		$contact_person = $this->params['contact_person'];
		$selectSql =  "select * from db_iro.tbl_jdrr_contracts_details where parentid='".$this->parentid."'";
		$resSql = parent::execQuery($selectSql,$this->conn_local);
		if($resSql && parent::numRows($resSql)>0){
			while($row = parent::fetchData($resSql)){
				$jdrr_details[]	= $row;
				$jdrr_details['contact_person']	= $contact_person;
			}
			$resultArr['error']['code'] 	=	0;
			$resultArr['error']['msg']  	=	"Data Found";
			$resultArr['data']  			=	$jdrr_details;
		}else{
			$resultArr['error']['code'] 	=	1;
			$resultArr['error']['msg']  	=	"Data Not Found";
		}
		return $resultArr;
	}
	
	public function checkEntryEcsLead(){
		$resultArr 	= array();
		$final_val 	= $this->params['final_val'];
		$cname 		= $this->params['compname'];
		$ip 		= $this->params['ip'];
		if($final_val == 'lead')
		{
			$lead_entry 	= "SELECT parentid FROM tbl_new_lead WHERE parentid = '".$this->parentid."'";
			$con_lead_entry = parent::execQuery($lead_entry,$this->conn_local);
			$lead_numRows 	= parent::numRows($con_lead_entry);
			if($lead_numRows > 0){
				$resultArr['error']['code'] 	=	0;
				$resultArr['error']['msg']  	=	"Data Inserted";
				$resultArr['data']  			=	0;
				return $resultArr;
			}else{
				$Insert_into_lead = "INSERT INTO tbl_new_lead SET 
									parentid			=	'".$this->parentid."',
									companyname 		=	'".$this->stringProcess($cname)."',
									tmecode				=	'".$this->ucode."',
									tmename				=	'".$this->uname."',
									allocated_date      =   NOW(),
									insert_date         =   NOW(),
									update_date			=	NOW(),
									data_city			=	'".$this->data_city."',
									request_source		=	'Phone Search',
									state 				=	'2',
									ip					= '".$ip."'";
				$conn_Insert_into_lead = parent::execQuery($Insert_into_lead,$this->conn_local);
				
				$Insert_into_Lead_Log 		= "INSERT INTO tbl_new_lead_log SET 
												parentid			=	'".$this->parentid."',
												companyname 		=	'".$this->stringProcess($cname)."',
												tmecode				=	'".$this->ucode."',
												tmename				=	'".$this->uname."',
												state				=	'2',
												insert_date			=	NOW(),
												update_date			=	NOW(),
												data_city			= 	'".$this->data_city."',
												request_source		=	'Phone Search'";
				$Insert_into_Lead_Log_Res = parent::execQuery($Insert_into_Lead_Log,$this->conn_local);
				$resultArr['error']['code'] 	=	0;
				$resultArr['error']['msg']  	=	"Data Inserted";
				$resultArr['data']  			=	1;
				return $resultArr; 
			}
		}
		else if($final_val == 'ecs')
		{
			$ecs_entry 		= "SELECT parentid FROM tbl_new_retention WHERE parentid = '".$this->parentid."'";
			$con_ecs_entry 	= parent::execQuery($ecs_entry,$this->conn_local);
			$ecs_numRows 	= parent::numRows($con_ecs_entry);
	
			if($ecs_numRows > 0){
				$resultArr['error']['code'] 	=	0;
				$resultArr['error']['msg']  	=	"Data Inserted";
				$resultArr['data']  			=	0;
				return $resultArr;
			}else{
				$Insert_into_Retention =   "INSERT INTO tbl_new_retention SET 
											parentid				=	'".$this->parentid."',
											companyname 			=	'".$this->stringProcess($cname)."',
											tmecode					=	'".$this->ucode."',
											tmename					=	'".$this->uname."',
											allocated_date      	=   NOW(),
											data_city				=	'".$this->data_city."',
											request_source			=	'Phone Search',
											insert_date             =   NOW(),
											update_date				=	NOW(),
											state 					=	'2',
											ip						= 	'".$ip."'";
				$conn_Insert_into_Retention = parent::execQuery($Insert_into_Retention,$this->conn_local);
				
				$Insert_into_Retention_Log 	= 	"INSERT INTO tbl_new_retention_log SET 
												parentid			=	'".$this->parentid."',
												companyname 		=	'".$this->stringProcess($cname)."',
												tmecode				=	'".$this->ucode."',
												tmename				=	'".$this->uname."',
												insert_date			=	NOW(),
												state				=	'2',
												data_city			= 	'".$this->data_city."',
												request_source		=	'Phone Search'";
				$Insert_into_Retention_Log_Res = parent::execQuery($Insert_into_Retention_Log,$this->conn_local);
				$resultArr['error']['code'] 	=	0;
				$resultArr['error']['msg']  	=	"Data Inserted";
				$resultArr['data']  			=	1;
				return $resultArr; 
			}
		}
	}
	
	public function estimatedSearchInfo(){
		$resultArr 	= array();
        $docid 		= $this->params['docid'];
        $currtime   = date("Y-m-d");
        $fiveDaysOld= date("Y-m-d",strtotime("-5 days"));
        $userName   = "sales_team";
        $secret_key = 'FG_XW-BO._AXG';
        $checkSum 	= md5($userName . $currtime . $secret_key);
        $url        = "http://searchmis.justdial.com/custom_search?mis_src=iframe&un=".$userName."&cs=".$checkSum."&date_range=".$fiveDaysOld."+-+".$currtime."&id=".$docid."&type=2";
		$resultArr['error']['code'] 	=	0;
		$resultArr['error']['msg']  	=	"Data Found";
		$resultArr['data']  			=	$url;
		return $resultArr;
	}
	
	public function iroCardInfo(){
		$resultArr 	= array();
        $url 		= $this->iro_url."/mvc/services/company/getcards?parentid=".$this->parentid."&city=".$this->data_city;
        $content 	= json_decode($this->curlCall($url),true);
        
        if($content['errors']['code'] === 0){
			$resultArr['data']			= $content['results']['data'];
			$resultArr['error']['code']	= 0;
			$resultArr['error']['msg']	= 'Data Found';
		}else{
			$resultArr['error']['code']	= 1;
			$resultArr['error']['msg']	= 'Data Not Found';
		}
		return $resultArr;
	}
	public function buildingAutoComplete(){
		$res_arr = array();
		$string = strtolower($this->params['term']);
		if($string!=''){
			$strArr	=	explode(' ',$string);
			if(count($strArr)>0){
				$src_str =	implode("','",$strArr);
			}
		}
		$building_data = array();
		if($src_str!=''){     
			$sel_query = "SELECT Abbrevation, Full_Form FROM tbl_auto_suggest where Abbrevation IN('".$src_str."')";
		    $con_sel_query  = parent::execQuery($sel_query,$this->conn_local); 
			$num_empcode    = parent::numRows($con_sel_query);
			$paid_json_file =	array();
			if($num_empcode	> 0) {
				while($row_check = parent::fetchData($con_sel_query,$this->conn_local)){                           
					$building_data[] = $row_check;
				}
			}
        }
        if(count($building_data)>0){
			$res_arr['data']			= $building_data;
			$res_arr['error']['code']	= 0;
			$res_arr['error']['msg']	= 'Data Found';
		}else{
        	$res_arr['error']['code'] 	= 1;
 			$res_arr['error']['msg'] 	= 'No Record found';
        }
        return $res_arr;
    }
	public function ecsEscalationDetails(){
		$limitVal = 50;
		if($this->params['tme_comm']==1){
			$resultArr 	= array();
			$retArr		= array();
			$tmecode 	= $this->ucode;
			$whereCond 	= '';
			$ecsCond 	= '';
			$andCond 	= "";
			$stateCond 	= "";
			$orderCond	= 'order by a.update_date,a.allocated_date desc';
			$groupCond	= '';
			
			$pending 	= $open = $close = $follow_up = $call_back = $repeat_call = $per_closed = $reallocated = 0;
			$disp_actions = explode(":",$this->params['srchparam']);
			$action_name = trim(strtolower($disp_actions[0]));
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND a.companyname LIKE "'.$this->params['srchData'].'%" ';
						break;
						case 'parentidLike' :
							$whereCond	=	' AND a.parentid = "'.$this->params['srchData'].'" ';
						break;
						case 'followup' :
							$whereCond	=	' AND a.action_flag = 4';
						break;
						case 'retain' :
							$whereCond	=	' AND a.action_flag = 5';
						break;
						case 'stoptme' :
							$whereCond	=	' AND a.action_flag = 9';
						case 'repeatCall' :
							$whereCond	=	' AND (a.repeat_call = 1 OR a.repeat_call = 2)';
						break;
					}
				} else if($this->params['srchwhich'] == 'ecs_actions') {
					$orderCond	=	' ORDER BY a.allocated_date DESC';
					if($action_name != "closed percentage")
					{
						$andCond = ' AND a.update_date > a.repeatcall_taggedon';
						if($action_name == "pending"){
								//~ $whereCond	=	" AND a.action_flag = 0 AND a.reallocate_flag != 1";
								$whereCond	=	" AND a.action_flag = 0 AND a.state != 3";
							}else if($action_name == "open"){
								$whereCond	=	" AND a.action_flag IN ('1','31')";
							}else if($action_name == "close"){
								$whereCond	=	" AND a.action_flag IN ('2','32')";
							}else if($action_name == "follow up"){
								$whereCond	=	" AND a.action_flag IN ('3','33')";
							}else if($action_name == "call back"){
								$whereCond	=	" AND a.action_flag IN ('4','34')";
							}
							//~ else if($action_name == "reallocated"){
								//~ $whereCond	=	" AND a.reallocate_flag = 1 AND a.action_flag = 0 AND a.allocated_date > a.repeatcall_taggedon";
							//~ }
							else if($action_name == "repeat call"){
								$andCond = " AND a.repeatcall_taggedon > a.update_date ";
								$whereCond	=	" AND (a.repeat_call = 1 OR a.repeat_call = 2 OR a.repeat_call = 4)".$andCond;
								$orderCond	=  "order by a.repeatcall_taggedon desc"; 
							}
					}
						
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$groupCond="group by a.parentId";
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$limitVal;
			}
			
			if($this->params['srchparam'] != 'compnameLike'){
				$queryNum 	=	"SELECT COUNT(1) AS COUNT FROM tbl_new_lead  WHERE tmecode ='".$tmecode."'";
				$conNum		= parent::execQuery($queryNum,$this->conn_local);
				$numRowsNum = parent::fetchData($conNum);
			}
			
			$qutmeAllocData	="SELECT a.companyname,a.parentid as contractid,a.action_flag,a.tmecode,a.data_city,a.tmename,a.state,a.repeatCount,a.allocated_date FROM d_jds.tbl_new_lead a LEFT JOIN d_jds.mktgEmpMaster b ON a.tmecode = b.mktEmpCode LEFT JOIN d_jds.mktgEmpMaster c ON b.empParent = c.mktEmpCode LEFT JOIN d_jds.tbl_new_retention d ON a.parentid = d.parentid WHERE (b.Approval_flag = '1' AND b.allocId = 'RD') AND (IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.allocated_date OR (d.allocated_date = '' OR d.allocated_date IS NULL)) AND (IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.update_date OR (d.update_date = '' OR d.update_date IS NULL)) AND a.tmecode = '".$tmecode."' AND (a.allocated_date >= '2017-11-01 00:00:00') AND b.block_emp=0 AND empType=5";
			
			$qutmeAllocData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
			//echo $qutmeAllocData;die();
			$contmeAlloclData= parent::execQuery($qutmeAllocData,$this->conn_local);
			$numPage = parent::numRows($contmeAlloclData);
			if($numPage > 0)
			{
				while($res = parent::fetchData($contmeAlloclData))
				{
					$retArr['data'][$res['contractid']]['action_flag']			=	$res['action_flag'];
					$retArr['data'][$res['contractid']]['tmecode']				=	$res['tmecode'];
					$retArr['data'][$res['contractid']]['companyname']			=	$res['companyname'];
					$retArr['data'][$res['contractid']]['data_city']			=	$res['data_city'];
					$retArr['data'][$res['contractid']]['contractid']			=	trim($res['contractid']);
					$retArr['data'][$res['contractid']]['tmename']		        =	$res['tmename'];
					$retArr['data'][$res['contractid']]['state']	           	=	$res['state'];
					$retArr['data'][$res['contractid']]['repeatCount'] 			=	$res['repeatCount'];
					$retArr['data'][$res['contractid']]['allocated_date']  		=	$res['allocated_date'];
				}
				
				foreach($retArr['data'] as $keyPar=>$valPar)
				{
					if($valPar['companyname'] == '' || $valPar['companyname'] == 'null' || $valPar['companyname'] == 'undefined')
					{
					   //$selectLead_Cname = "SELECT companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$keyPar."'";
					  // $selectLead_Cname_Res = parent::execQuery($selectLead_Cname,$this->conn_local);
						$comp_params = array();
						$comp_params['data_city'] 	= $this->data_city;
						$comp_params['table'] 		= 'gen_info_id';		
						$comp_params['parentid'] 	= $keyPar;
						$comp_params['fields']		= 'companyname';
						$comp_params['action']		= 'fetchdata';
						$comp_params['page']		= 'allDetailClass';
						
						$comp_api_res 	= '';
						$comp_api_arr	= array();
						$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
						if($comp_api_res!=''){
							$comp_api_arr 	= json_decode($comp_api_res,TRUE);
						}
					  
					   //$selectLead_Cname_NumRows = parent::numRows($selectLead_Cname_Res);
					   if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
					   {
						   //$selectLead_Cname_Data = parent::fetchData($selectLead_Cname_Res);
						   $selectLead_Cname_Data 	= $comp_api_arr['results']['data'][$keyPar];
						   $valPar['compname'] = $selectLead_Cname_Data['companyname'];
					   }
					}
					   
					if($valPar['contractid'] != '' || $valPar['contractid'] != 'null' || $valPar['contractid'] != 'undefined')
					{
					   $select_TmeStatus = "SELECT * FROM d_jds.tbl_new_lead_log WHERE parentid = '".$keyPar."' AND action_flag IN ('0','1','2','3','31','32','33','34') ORDER BY update_date DESC LIMIT 1";
					   //~ $select_TmeStatus = "SELECT * FROM d_jds.log_complain_main WHERE parentid = '".$keyPar."' AND complain_type IN ('110','139','138','70','24','25','58','56','60','57','59') ORDER BY complain_registration_date DESC LIMIT 1";
					   $select_TmeStatus_Res = parent::execQuery($select_TmeStatus,$this->conn_local);
					   $select_TmeStatus_NumRows = parent::numRows($select_TmeStatus_Res);
					   if($select_TmeStatus_NumRows > 0)
					   {
						   $select_TmeStatus_Data = parent::fetchData($select_TmeStatus_Res);
						   $valPar['tme_status'] = $select_TmeStatus_Data['action_flag'];
						   $valPar['tme_status_date'] = $select_TmeStatus_Data['update_date'];
						   $valPar['tme_allocated_date'] = $select_TmeStatus_Data['insert_date'];
					   }
					   
					   $select_uploaded_file = "SELECT * FROM d_jds.tbl_retention_lead_transfer_uploads where parentid = '".$valPar['contractid']."' ORDER BY update_date DESC";
					   
					   $select_uploaded_file_Res = parent::execQuery($select_uploaded_file,$this->conn_local);
					   $select_uploaded_file_NumRows = parent::numRows($select_uploaded_file_Res);
					   if($select_uploaded_file_NumRows > 0){
						   while($row = parent::fetchData($select_uploaded_file_Res)){
							   $valPar['uploaded_files'][] = $row;
						   }
					   }
					}
					$resultArr['data'][]	=	$valPar;
				}
				
				
				
				$ecsReportCountData = "SELECT a.companyname,a.parentid,a.update_date, a.allocated_date,a.data_city,a.tmename,a.action_flag,a.tmecode,a.state,a.repeat_call,a.repeatcall_taggedon,a.request_source,a.reallocate_flag 
				FROM d_jds.tbl_new_lead a LEFT JOIN d_jds.mktgEmpMaster b ON a.tmecode = b.mktEmpCode LEFT JOIN d_jds.mktgEmpMaster c ON b.empParent = c.mktEmpCode 
				LEFT JOIN d_jds.tbl_new_retention d ON a.parentid = d.parentid 
				WHERE  (b.Approval_flag = '1' AND b.allocId = 'RD') AND 
				(IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.allocated_date OR (d.allocated_date = '' OR d.allocated_date IS NULL))
				AND (IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.update_date OR (d.update_date = '' OR d.update_date IS NULL))  AND  a.tmecode = '".$tmecode."' 
				AND (a.allocated_date >= '2017-11-01 00:00:00')  AND b.block_emp=0 AND empType=5 
				ORDER BY a.allocated_date,a.update_date DESC";
				$sel_retention_report_Res = parent::execQuery($ecsReportCountData,$this->conn_local);
				$total_report_contracts	=	parent::numRows($sel_retention_report_Res);
				while($res	=	parent::fetchData($sel_retention_report_Res)){
						$datetime=new DateTime($res['repeatcall_taggedon']);
						$curr_date_pri = new DateTime($res['update_date']);
						$allocated_date = new DateTime($res['allocated_date']);
						//~ $datetime->getTimestamp(); 
						//~ $curr_date_pri->getTimestamp(); 
						//~ $allocated_date->getTimestamp(); 
						$monthyear = date("Y-m"); 
						$date = date("Y-m",strtotime($res['update_date']));
						
						if($date >= $monthyear){
							$total_report_contracts = $total_report_contracts + 1;
						}
						
						if(($res['repeatcall_taggedon'] != null && $res['repeatcall_taggedon'] != '0000-00-00 00:00:00') && ($datetime->getTimestamp() >= $curr_date_pri->getTimestamp())){
							if(($res['repeat_call'] == 1 || $res['repeat_call'] == 2 || $res['repeat_call'] == 4)){
								$repeat_call = $repeat_call + 1;
							}
					}else{
						//~ if($res['action_flag'] == 0 && $res['state'] != 3){
						if($res['action_flag'] == 0 && $res['state'] != 3){
							$pending = $pending + 1;
						}else if(($res['action_flag'] == 1 || $res['action_flag'] == 31)){
							$open = $open + 1;
						}else if(($res['action_flag'] == 2 || $res['action_flag'] == 32)){
							$close = $close + 1;
							if ($date >= $monthyear) $per_closed = $per_closed + 1;
						}else if(($res['action_flag'] == 3 || $res['action_flag'] == 33)){
							$follow_up = $follow_up + 1;
						}else if(($res['action_flag'] == 4 || $res['action_flag'] == 34)){
							$call_back = $call_back + 1;
						}
						//~ else if($res['reallocate_flag'] == 1 && $res['action_flag'] == 0 && $allocated_date->getTimestamp() >= $datetime->getTimestamp()){
							//~ $reallocated = $reallocated + 1;
						//~ }
					}
				}
				//~ echo '-----'.$reallocated;die();
				$closed_percentage = round((($close / $total_report_contracts) * 100),1);
				
				$resultArr['act_count'][0]['key']  =	"Pending";					$resultArr['act_count'][0]['val']  =	"Pending : ".$pending;
				$resultArr['act_count'][1]['key']  =	"Open";						$resultArr['act_count'][1]['val']  =	"Open : ".$open;
				$resultArr['act_count'][2]['key']  =	"Close";					$resultArr['act_count'][2]['val']  =	"Close : ".$close;
				$resultArr['act_count'][3]['key']  =	"Follow Up";				$resultArr['act_count'][3]['val']  =	"Follow Up : ".$follow_up;
				$resultArr['act_count'][4]['key']  =	"Repeat Call";				$resultArr['act_count'][4]['val']  =	"Repeat Call : ".$repeat_call;
				//~ $resultArr['act_count'][5]['key']  =	"Reallocated";				$resultArr['act_count'][5]['val']  =	"Reallocated : ".$reallocated;
				$resultArr['act_count'][5]['key']  =	"Closed Percentage";		$resultArr['act_count'][5]['val']  =	"Closed Percentage : ".$closed_percentage."%";
				$resultArr['act_count'][6]['key']  =	"Call Back";				$resultArr['act_count'][6]['val']  =	"Call Back : ".$call_back;
				
				$resultArr['error']['code']	= 0;
				$resultArr['error']['msg']	= 'Data Found';
			}
			else 
			{
				$resultArr['error']['code']	= 1;
				$resultArr['error']['msg']	= 'Data Not Found';
			}
			$resultArr['count']	=	$numPage;
			if($this->params['srchparam'] != 'compnameLike'){
				$resultArr['counttot']	=	$numRowsNum['COUNT'];
			}
			return $resultArr;
		}
		else
		{
			$resultArr 	= array();
			$parentid 	= $this->params['srchData'];
			$lead 		= $this->params['lead'];
			$cname 		= $this->params['companyname'];
			$tmename 	= $this->uname;
			$tmecode 	= $this->ucode;
			$ecs_arr 	= array();
			$lead_arr 	= array();
			
			$queryNum 	= "SELECT COUNT(1) AS COUNT FROM tbl_new_retention WHERE tmecode ='".$tmecode."' or escalated_details like '%".$tmecode."%'";
			$conNum		= parent::execQuery($queryNum,$this->conn_local);
			$numRowsNum = parent::fetchData($conNum);
			
			$qutmeAllocData	= "SELECT companyname as compname,parentId as contractid,update_date as entry_date,allocated_date,update_date,data_city,ecs_stop_flag,tmename,action_flag,tmecode,escalated_details,state,reactivate_flag,reactivated_on,reactivated_by,tme_comment as tme_comments,repeat_call,repeatcall_taggedon,ecs_reject_approved,reactivate_reject_comment,insert_date,allocate_by_cs,repeatCount from d_jds.tbl_new_retention WHERE parentid= '".$parentid."'";
			$contmeAlloclData = parent::execQuery($qutmeAllocData,$this->conn_local);
			$numPage = parent::numRows($contmeAlloclData);
			if($numPage > 0)
			{
				while($res = parent::fetchData($contmeAlloclData))
				{
					$ecs_arr[] = $res;
					$action_flag=$res['action_flag'];
					if($action_flag == 5 || $action_flag == 23)
					{
						$timestring_ecs = $res['update_date'];
					}
					else
					{
						$timestring_ecs = $res['allocated_date'];
					}
					
					$lead_entry 	= "SELECT *,parentid as contractid FROM d_jds.tbl_new_lead WHERE parentid = '".$parentid."'";
					$conn_lead_entry= parent::execQuery($lead_entry,$this->conn_local);
					$num_lead_entry = parent::numRows($conn_lead_entry);
					
					if($num_lead_entry > 0)
					{
						$row = parent::fetchData($conn_lead_entry);
						$lead_arr[] = $row;
						$action = $row['action_flag'];
						
						if($action == 5 || $action == 23)
						{
							$timestring_lead = $row['update_date'];
						}
						else
						{
							$timestring_lead = $row['allocated_date'];
						}
					}
					
					// for checking 2 months condition //
						
					if($timestring_ecs != '' && $timestring_ecs != null && $timestring_lead != '' && $timestring_lead != null)
					{
						$ecs_date=new DateTime($timestring_ecs);
						$ecs_date->getTimestamp(); 
						
						$lead_date = new DateTime($timestring_lead);
						$lead_date->getTimestamp(); 
						
						if($ecs_date > $lead_date)
						{
							$timestring = $timestring_ecs;
							$lead_flag = 1;
						}
						else
						{
							$timestring = $timestring_lead;
							$lead_flag = 0;
						}
					}
					else if($timestring_ecs != '' && $timestring_ecs != null && ($timestring_lead == '' || $timestring_lead == null))
					{
						$timestring = $timestring_ecs;
						$lead_flag = 1;
					}
					else
					{
						$timestring = $timestring_lead;
						$lead_flag = 0;
					}
						
					if($timestring != '' && $timestring != null)
					{		
						$datetime=new DateTime($timestring);
						$datetime->modify('+30 day');
						$datetime->format("Y-m-d");
						$curr_date_pri = new DateTime('now');
						$datetime->getTimestamp(); 
						$curr_date_pri->getTimestamp(); 

				
						if($curr_date_pri->getTimestamp() >= $datetime->getTimestamp())
						{
							$ecs_arr[0]['EcsUpdate_Flag'] = 1; //update
							$lead_arr[0]['EcsUpdate_Flag'] = 1; //update
						}
						else
						{
							$ecs_arr[0]['EcsUpdate_Flag'] = 0; //remain
							$lead_arr[0]['EcsUpdate_Flag'] = 0; //remain
						}
					}
					else
					{
						$ecs_arr[0]['EcsUpdate_Flag'] = 1; //update
						$lead_arr[0]['EcsUpdate_Flag'] = 1; //update
					}
				
					if($lead_flag == 0 || $lead_flag == 1)
					{
						if($lead_flag == 1)
						{
							$resultArr['data'] = $ecs_arr;
						}
						else
						{
							$resultArr['data'] = $lead_arr;
						}
					}
				}
				$resultArr['error']['code']	= 0;
				$resultArr['error']['msg']	= 'Data Found';
			}
			else
			{
				$lead_entry 	= "SELECT *,parentid as contractid FROM d_jds.tbl_new_lead WHERE parentid = '".$parentid."'";
				$conn_lead_entry= parent::execQuery($lead_entry,$this->conn_local);
				$num_lead_entry = parent::numRows($conn_lead_entry);
				if($num_lead_entry > 0)
				{
					$row = parent::fetchData($conn_lead_entry);
					$action_flag = $row['action_flag'];
					$lead_arr = $row; 
					
					if($action_flag == 5 || $action_flag == 23)
					{
						$timestring = $row['update_date'];
					}
					else
					{
						$timestring = $row['allocated_date'];
					}
					
					if($timestring != '' && $timestring != null)
					{		
						$datetime=new DateTime($timestring);
						$datetime->modify('+30 day');
						$datetime->format("Y-m-d");
						$curr_date_pri = new DateTime('now');
						$datetime->getTimestamp(); 
						$curr_date_pri->getTimestamp(); 

				
						if($curr_date_pri->getTimestamp() >= $datetime->getTimestamp())
						{
							$lead_arr['EcsUpdate_Flag'] = 1; //update
						}
						else
						{
							$lead_arr['EcsUpdate_Flag'] = 0; //remain
						}
					}
					else
					{
						$lead_arr['EcsUpdate_Flag'] = 1; //update
					}
					
					$resultArr['data'][]		= $lead_arr;
					$resultArr['error']['code']	= 0;
					$resultArr['error']['msg']	= 'Data Found';
				}
			}
			$empCode = $resultArr['data'][0]['tmecode'];
			if($empCode != '' && $empCode != null)
			{
				$ActiveEmployee 	= "SELECT * FROM d_jds.mktgEmpMaster WHERE mktEmpCode = '".$empCode."' AND block_emp=0 AND empType=5";
				$ActiveEmployee_Res = parent::execQuery($ActiveEmployee,$this->conn_local);
				$ActiveEmployee_Data= parent::fetchData($ActiveEmployee_Res);
				$resultArr['isActive'] =  $ActiveEmployee_Data['Approval_flag'];
				$resultArr['allocID'] 	=  $ActiveEmployee_Data['allocId'];
			}
			
			$resultArr['count']		=	$numPage;
			$resultArr['counttot']	=	$numRowsNum['COUNT'];
			return $resultArr;
		}
	}
	
	public function webDialerAllocation()
	{
		$retArr 		= array();
		$parentid 		= $this->parentid;
		$companyname 	= $this->params['companyname'];
		$tmecode 		= $this->ucode;
		$tmename 		= $this->uname;
		$data_city 		= $this->data_city;
		$source 		= $this->params['source'];
		$now 			= date("Y-m-d H:i:s");
		
		$insertSql =   "INSERT INTO tbl_new_retention SET
						parentid 		= '".$parentid."',
						tmecode 		= '".$tmecode."',
						tmename 		= '".$this->stringProcess($tmename)."',
						insert_date 	= '".$tmecode."~".$now."',
						update_date 	= NOW(),
						companyname 	= '".$this->stringProcess($companyname)."',
						state 			= '2',
						data_city 		= '".$data_city."',
						ecs_stop_flag 	= '0',
						action_flag 	= '0',
						complain_type 	= '".$source."',
						allocated_date 	= NOW(),
						request_source 	= '".$source."'
			  
						ON DUPLICATE KEY UPDATE 
			  
						escalated_details 	= '',
						repeat_call 		= '',
						reactivate_flag 	='',
						complain_type 		='".$source."',
						stop_request_datetime ='',
						stop_remark 		='',
						reactivated_on 		= '',
						reactivated_by 		='',
						ecs_reject_approved ='',
						stop_reason 		= '',
						stop_remark 		='',
						approve_datetime 	='',
						approved_by 		='',
						ip					='',
						ecs_stop_flag 		='0',
						action_flag 		= '0',
						state 				= '2',
						tmecode 			= '".$tmecode."',
						companyname 		= '".$this->stringProcess($companyname)."',
						tmename 			= '".$this->stringProcess($tmename)."',
						update_date 		= now(),
						insert_date 		='".$tmecode."~".$now."',
						data_city 			= '".$data_city."',
						allocated_date 		= now(),
						request_source 		='".$source."'";
		$insertRes = parent::execQuery($insertSql,$this->conn_local);
		
		if(isset($insertRes)){
			$insertLogSql = "INSERT INTO tbl_new_retention_log
						SET
						parentid = '".$parentid."',
						tmecode = '".$tmecode."',
						tmename = '".$this->stringProcess($tmename)."',
						insert_date = now(),
						complain_type = '".$source."',
						companyname = '".$this->stringProcess($companyname)."',
						data_city = '".$data_city."',
						request_source = '".$source."',
						state = '2'";
			$insertLogRes = parent::execQuery($insertLogSql,$this->conn_local);
			$retArr['error']['code']= 0;
			$retArr['error']['msg']	= 'Allocated Successfully';
			
		}else{	
			$retArr['error']['code']= 1;
			$retArr['error']['msg']	= 'Allocation Failed';
		}
		return $retArr;
	}
	
	public function phoneSearchAllocation()
	{
		$retArr = array();
		$cityArr = array('ahmedabad','bangalore','chennai','delhi','hyderabad','kolkata','mumbai','pune');
		$contract_city = $this->params['contract_city'];
		$employee_city = $this->params['employee_city'];
		if(in_array($contract_city,$cityArr)){
			$final_contract_city =  $contract_city;
		}else{
			$select_zone 				= "SELECT * FROM d_jds.tbl_zone_cities WHERE Cities = '".$contract_city."'";
			$select_zone_Res 			= parent::execQuery($select_zone,$this->conn_local);
			$select_zone_fetchData		= parent::fetchData($select_zone_Res);
			$final_contract_city		= strtolower($select_zone_fetchData['main_zone']); // navi mumbai
		}
		
		if(in_array($employee_city,$cityArr)){
			$final_employee_city =  $employee_city;
		}else{
			$select_zone 				= "SELECT * FROM d_jds.tbl_zone_cities WHERE Cities = '".$employee_city."'";
			$select_zone_Res 			= parent::execQuery($select_zone,$this->conn_local);
			$select_zone_fetchData		= parent::fetchData($select_zone_Res);
			$final_employee_city		= strtolower($select_zone_fetchData['main_zone']); // navi mumbai
		}

		if($final_contract_city == $final_employee_city){
			if($this->params['lead'] != '' && $this->params['lead'] == 1 ){
					//ecs allocate
					$Insert_into_Retention = "INSERT INTO d_jds.tbl_new_retention SET parentid	=	'".$this->parentid."',
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															allocated_date		=		NOW(),
															insert_date			=		NOW(),
															update_date			=		NOW(),
															state				=		'2',
															companyname			=		'".$this->stringProcess($this->params['companyname'])."',
															data_city			=		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					= 		'".$this->params['ip']."'
															
															ON DUPLICATE KEY UPDATE	
																						
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															allocated_date		=		NOW(),
															insert_date			=		NOW(),
															update_date			=		NOW(),
															state				=		'2',
															companyname			=		'".$this->stringProcess($this->params['companyname'])."',
															data_city			=		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					= 		'".$this->params['ip']."'"; 
					$Insert_into_Retention_Res = parent::execQuery($Insert_into_Retention,$this->conn_local);
					
					$Insert_into_Retention_Log 	= "INSERT INTO d_jds.tbl_new_retention_log SET parentid		=	'".$this->parentid."',
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															companyname			=	    '".$this->stringProcess($this->params['companyname'])."',
															insert_date			=		NOW(),
															state				=		'2',
															data_city			= 		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					= 		'".$this->params['ip']."'";
					$Insert_into_Retention_Log_Res = parent::execQuery($Insert_into_Retention_Log,$this->conn_local);
					
					if($Insert_into_Retention_Res){
						$retArr['error']['code']= 0;
						$retArr['error']['msg']	= 'Data Updated';
					}else{
						$retArr['error']['code']= 1;
						$retArr['error']['msg']	= 'Data Not Updated';
					}
				}else{
					$Insert_into_Lead = "INSERT INTO d_jds.tbl_new_lead SET parentid	=	'".$this->parentid."',
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															allocated_date		=		NOW(),
															insert_date			=		NOW(),
															update_date			=		NOW(),
															state				=		'2',
															companyname			=		'".$this->stringProcess($this->params['companyname'])."',
															data_city			=		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					=       '".$this->params['ip']."'
															
															ON DUPLICATE KEY UPDATE	
																						
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															allocated_date		=		NOW(),
															insert_date			=		NOW(),
															update_date			=		NOW(),
															state				=		'2',
															companyname			=		'".$this->stringProcess($this->params['companyname'])."',
															data_city			=		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					=       '".$this->params['ip']."'";
					$Insert_into_Lead_Res = parent::execQuery($Insert_into_Lead,$this->conn_local);
					
					$Insert_into_Lead_Log 			= "INSERT INTO d_jds.tbl_new_lead_log SET parentid		=	'".$this->parentid."',
															tmecode				=		'".$this->ucode."',
															tmename		 		=		'".$this->stringProcess($this->uname)."',
															companyname			=		'".$this->stringProcess($this->params['companyname'])."',
															state				=		'2',
															insert_date			=		NOW(),
															update_date			=		NOW(),
															data_city			= 		'".$this->data_city."',
															request_source		=		'Phone Search',
															ip					= 		'".$this->params['ip']."'";
					$Insert_into_Lead_Log_Res = parent::execQuery($Insert_into_Lead_Log,$this->conn_local);
					
					if($Insert_into_Lead_Res){
						$retArr['error']['code']= 0;
						$retArr['error']['msg']	= 'Data Updated';
					}else{						
						$retArr['error']['code']= 1;
						$retArr['error']['msg']	= 'Data Not Updated';
					}
			   }
		}else{			
			$retArr['error']['code']= 2;
			$retArr['error']['msg']	= 'Contract belongs to different city';
		}
		
		return $retArr;
	}
	
	public function fetchEcsDetails()
	{
		$retArr = array();
		$versionArr = array();
		$finalArr = array();
		
		//$select_Name_Id = "SELECT parentid,companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '" . $this->parentid . "'";
		//$select_Name_Id_Res = parent::execQuery($select_Name_Id,$this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'parentid,companyname';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'allDetailClass';

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		
		//$select_Name_Id_numRows = parent::numRows($select_Name_Id_Res);
		//~ if($select_Name_Id_numRows > 0)
		//~ {
			//~ while($row = parent::fetchData($select_Name_Id_Res))
			//~ {
				//~ $retArr['companyname'] = $row['companyname'];
				//~ $retArr['parentid'] = $row['parentid'];
			//~ }
		//~ }
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
		{
			$row 	= $comp_api_arr['results']['data'][$this->parentid];
			$retArr['companyname'] 	= $row['companyname'];
			$retArr['parentid'] 	= $row['parentid'];			
		}
		
		$select_version = "SELECT version FROM payment_apportioning WHERE parentid = '" . $this->parentid . "' AND (budget != balance) ORDER BY entry_date DESC LIMIT 1";
		$select_version_res = parent::execQuery($select_version,$this->conn_fin);
		$select_version_numrows = parent::numRows($select_version_res);
		$select_version_fetchData = parent::fetchData($select_version_res);
		$curr_version = $select_version_fetchData['version'];
	
		
		$select_Curr_Cont_Value = "SELECT a.parentid,a.version,a.budget,a.entry_date,c.tmeName,c.meName
		FROM payment_apportioning a JOIN payment_campaign_master b ON a.campaignid=b.campaignid  
		JOIN payment_otherdetails  c ON a.parentid=c.parentid
		WHERE a.parentid='" . $this->parentid . "'  AND (a.budget>0 OR a.balance>0)  AND a.version=c.version  AND (a.budget!=a.balance) 
		ORDER BY a.entry_date DESC,a.version,a.campaignid";
		$select_Curr_Cont_Value_Res = parent::execQuery($select_Curr_Cont_Value,$this->conn_fin);
		$select_Curr_Cont_Value_numRows = parent::numRows($select_Curr_Cont_Value_Res);
	
		if($select_Curr_Cont_Value_numRows > 0)
		{
			while($row = parent::fetchData($select_Curr_Cont_Value_Res))
			{
				$versionArr[] = $row;
			}
			foreach($versionArr as $key=>$val)
			{
				if($curr_version == $val['version'])
				{
					$finalArr['data'][] = $val;
					$finalArr['budget'][] = $val['budget'];
				}
			}
			
			$retArr['curr_contract_value'] = array_sum($finalArr['budget']);
			$retArr['tmeName'] = $finalArr['data'][0]['tmeName'];
			$retArr['mecode'] = $finalArr['data'][0]['meName'];
			
		}
		else
		{
			$retArr['curr_contract_value'] = 'Not Found';
			$retArr['tmeName'] = 'Not Found';
			$retArr['mecode'] = 'Not Found';
		}
		
		$url_st	= $this->jdbox_url."services/contract_type.php?parentid=".$this->parentid."&data_city=".$this->data_city."&rquest=get_contract_type";
		$content= json_decode($this->curlCall($url_st),true);
		
		$contracttype = $content['result']['contract_type'];
		if($contracttype != '')
		{
			$retArr['curr_contract_type'] = $contracttype;
		}
		else
		{
			$retArr['curr_contract_type'] = 'Not Found';
		}
		$retArr['error']['code']= 0;
		$retArr['error']['msg']	= 'Data Found';
		return $retArr;
	}
	
	public function getAllTme(){
		$value 	= $this->params['value'];
		$tme 	= $this->params['term'];
		if($value == 1)
		{
			$emptype = '5';
		}
		else
		{
			$emptype = '3';
		}
		
		$retArr = array();
		$resultArr = array();
		$select_all_tme = "SELECT empName FROM d_jds.mktgEmpMaster WHERE empName LIKE '" . $tme . "%' AND empType = '".$emptype."' LIMIT 10";
		$select_all_tme_res = parent::execQuery($select_all_tme,$this->conn_local);
		$numRows_tme = parent::numRows($select_all_tme_res);
		
		if($numRows_tme > 0)
		{
			while ($row = parent::fetchData($select_all_tme_res))
			{
			   $resultArr[] = $row['empName'];
			}
			$retArr['data']			= $resultArr;
			$retArr['error']['code']= 0;
			$retArr['error']['msg']	= 'Data Found';
		}
		else
		{
			$retArr['error']['code']= 1;
			$retArr['error']['msg']	= 'Data Not Found';
		}
		return $retArr;
	}
	
	public function ecsSendUpgradeRequest(){
		$ecs_flag 		= $this->params['ecs_flag'];
		$name 			= $this->params['name'];
		$curr_value 	= $this->params['curr_value'];
		$new_value 		= $this->params['new_value'];
		$curr_type 		= $this->params['curr_type'];
		$new_type 		= $this->params['new_type'];
		$curr_tme 		= $this->params['curr_tme'];
		$new_tme 		= $this->params['new_tme'];
		$curr_me 		= $this->params['curr_me'];
		$new_me 		= $this->params['new_me'];
		$new_payment 	= $this->params['new_payment'];
		$inst_det 		= $this->params['inst_det'];
		$payment_mode 	= $this->params['payment_mode'];
		$id 			= $this->params['id'];
		$payment_date 	= $this->params['payment_date'];
		$payment_number = $this->params['payment_number'];
		$payment_amount = $this->params['payment_amount'];
		
		$retArr = array();
		$city 	= array();
		$final_city = '';
		
		$zone_city_list = array('ahmedabad','bangalore','chandigarh','chennai','coimbatore','delhi','hyderabad','jaipur','kolkata','mumbai','pune');
		$city_value = strtolower($this->data_city);
		if(in_array($city_value,$zone_city_list))
		{
			$final_city = $city_value;
		}
		else
		{
			$selectZone = "SELECT main_zone FROM d_jds.tbl_zone_cities WHERE Cities = '".$city_value."'";
			$selectZone_res = parent::execQuery($selectZone,$this->conn_tme);
			$selectZone_num = parent::numRows($selectZone_res);
			
			if($selectZone_num > 0)
			{
				$ZoneFetchData = parent::fetchData($selectZone_res);
				$final_city = $ZoneFetchData['main_zone'];
			}
		}
		
		$select_city = "SELECT mngr_code FROM tme_jds.ecs_req_mngr_list WHERE data_city = '".$final_city."'";
		$select_city_res = parent::execQuery($select_city,$this->conn_tme);
		$num_res = parent::numRows($select_city_res);
		
		if($num_res > 0)
		{
			while($row = parent::fetchData($select_city_res))
			{
				$city['mngr_code'][] = $row['mngr_code'];
			}
		}
		$EmpName  = $this->uname;
		$gen_info = $this->fetchGeneralMain();
		
		$insert = "INSERT INTO tme_jds.tbl_ecs_dealclose_pending SET 
				parentid 				= '".trim($this->parentid)."',
				EmpCode 				= '".$this->ucode."',
				EmpName 				= '".$EmpName."',
				MngrCode 				= '".$city['mngr_code'][0]."',
				MngrCode1 				= '".$city['mngr_code'][1]."',
				MngrCode2 				= '".$city['mngr_code'][2]."',
				Acc_Reg_Flag 			= '".$ecs_flag."',
				companyname				= '".$gen_info['companyname']."',
				city					= '".$gen_info['city']."',
				pincode					= '".$gen_info['pincode']."',
				mobile					= '".$gen_info['mobile']."',
				email					= '".$gen_info['email']."',
				Mngr_Flag				= '0',
				requested_on 			= NOW(),
				updated_on 				= '',
				detail_cname 			= '".$name."',
				detail_cid 				= '".$id."',
				current_contract_value 	= '".$curr_value."',
				new_contract_value 		= '".$new_value."',
				current_contract_type 	= '".$curr_type."',
				new_contract_type 		= '".$new_type."',
				collected_new_payment 	= '".$new_payment."',
				instrument_details 		= '".$inst_det."',
				new_contract_payment_mode= '".$payment_mode."',
				detail_current_tme 		= '".$curr_tme."',
				detail_new_tme 			= '".$new_tme."',
				detail_current_me 		= '".$curr_me."',
				detail_new_me 			= '".$new_me."',
				contact_person			= '".$gen_info['contact_person']."',
				payment_date            = '".$payment_date."',
				payment_number			= '".$payment_number."',
				payment_amount 			= '".$payment_amount."'
				
				ON DUPLICATE KEY UPDATE
				
				EmpName 				= '".$EmpName."',
				MngrCode 				= '".$city['mngr_code'][0]."',
				MngrCode1 				= '".$city['mngr_code'][1]."',
				MngrCode2 				= '".$city['mngr_code'][2]."',
				Acc_Reg_Flag			= '".$ecs_flag."',
				companyname				= '".$gen_info['companyname']."',
				city					= '".$gen_info['city']."',
				pincode					= '".$gen_info['pincode']."',
				mobile					= '".$gen_info['mobile']."',
				email					= '".$gen_info['email']."',
				Mngr_Flag				= '0',
				requested_on 			= NOW(),
				updated_on 				= '',
				detail_cname 			= '".$name."',
				detail_cid 				= '".$id."',
				current_contract_value 	= '".$curr_value."',
				new_contract_value 		= '".$new_value."',
				current_contract_type 	= '".$curr_type."',
				new_contract_type 		= '".$new_type."',
				collected_new_payment 	= '".$new_payment."',
				instrument_details 		= '".$inst_det."',
				new_contract_payment_mode= '".$payment_mode."',
				detail_current_tme 		= '".$curr_tme."',
				detail_new_tme 			= '".$new_tme."',
				detail_current_me 		= '".$curr_me."',
				detail_new_me 			= '".$new_me."',
				contact_person			= '".$gen_info['contact_person']."',
				payment_date            = '".$payment_date."',
				payment_number			= '".$payment_number."',
				payment_amount 			= '".$payment_amount."'";																	
			$conInsert = parent::execQuery($insert,$this->conn_tme);
			if($conInsert){
				$retArr['data']			= 1;
				$retArr['error']['code']= 0;
				$retArr['error']['msg']	= 'Data Inserted';
			}else{
				$retArr['data']			= 0;
				$retArr['error']['code']= 1;
				$retArr['error']['msg']	= 'Data Not Inserted';
			}
			return $retArr;
	}

	function mobilefeedback(){
		
		$res_arr = array();
		if($this->params['mob_feedback'] == ''){
			$message = "mobile feedback missing";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}
		if($this->params['mobilenumber'] == ''){
			$message = "mobile number missing";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}
		
		$date=date('Y-m-d H:i:s');
		$parentid=$this->parentid;
		$insertFeedback="INSERT into tbl_sms_feedback_deactive_log(parentid,feedback_flag,reason,user_id,deactive_date,mobilenumber) values('".$this->parentid."','1','".$this->params['mob_feedback']."','".$this->ucode."','".$date."','".$this->params['mobilenumber']."')";
		$res_cat = parent::execQuery($insertFeedback, $this->conn_tme);
		if($res_cat){
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
		}
		
	}
	
	function updateCorrectIncorrectInfo(){
		$res_arr = array();
		$docid = $this->params['docid'];
		$insert_cor_incorrect		=	"INSERT INTO d_jds.tbl_correct_incorrect SET parentid	=	'".$this->parentid."',
												entry_date	=	now(),
												empcode		=	'".$this->ucode."',
												data_city	=	'".$this->data_city."',
												flag		=	1,
												docid		=	'".$docid."'
												ON DUPLICATE KEY UPDATE
												entry_date	=	now(),
												empcode		=	'".$this->ucode."',
												flag		=	1,
												data_city	=	'".$this->data_city."'";
		$con_cor_incor = parent::execQuery($insert_cor_incorrect, $this->conn_tme);
		if($con_cor_incor){
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
		}else {
			$message = "insertion failed";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}
		
	}
	
	public function getLatLongInfo($pincode){
		
		$pincode = intval($pincode);
		if($pincode >0 ){
			$sqlLatLongInfo = "SELECT latitude_final ,longitude_final FROM tbl_areamaster_consolidated_v3 WHERE pincode ='".$pincode."' AND display_flag=1 LIMIT 1";
			$resLatLongInfo = parent::execQuery($sqlLatLongInfo, $this->conn_local);
			if($resLatLongInfo && parent::numRows($res)>0){
				return $row_latlong = parent::fetchData($resLatLongInfo);
			}
		}
		
	}
	public function insertirodetails(){
		$date		=	date("Y-m-d H:i:s");
		$tmecode	=	$this->params['ucode'];
		//----IRO code and IRO name
		$iro_arr	=	explode("-",$this->params['iro_name']);
		$ironame	=	trim($iro_arr[0]);
		$irocode	=	trim($iro_arr[1]," ()");
		
		//----Fetching previous IRO codes
		$sel_iro  	= 	"SELECT irocode,irocode1,irocode2 FROM tbl_appointment_iro WHERE parentid = '".$this->parentid."'";
		$res  		= 	parent::execQuery($sel_iro, $this->conn_iro);
		if($res && parent::numRows($res)>0){
			$oldFlag	=	1;
			$row_iro    = parent::fetchData($res);
			$irocode1 	=	$row_iro[irocode];
			$irocode2 	=	$row_iro[irocode1];
			$irocode3 	=	$row_iro[irocode2];
			if($irocode	==	$irocode1){
				$irocode1 	=	$row_iro[irocode1];
				$irocode2 	=	$row_iro[irocode2];
				$swap		=	"No swap";
			}
		}	 

		if(trim($this->params['iro_name'])!=''){
			$success	=	"case one(NON EMPTY IRO)"; 
			if(stristr($this->params['iro_name'],"-(") && stristr($this->params['iro_name'],")")){   //---checking whether "-(" and ")" is present in string
				$success	=	"case one unsuccessful(NON EMPTY IRO)";
				
				$select_IRO_only	=	"SELECT empcode FROM emplogin WHERE concat_ws(' ',empfname,emplname)='".$ironame."' and empcode='".$irocode."' ";	
				$res_IRO_only  		= 	parent::execQuery($select_IRO_only,$this->conn_iro);
				if($res_IRO_only  && parent::numRows($res_IRO_only)>0){
					$ins_iro ="INSERT INTO tbl_appointment_iro SET
											parentid ='".$this->parentid."',
											ironame  ='".$ironame."',
											irocode  ='".$irocode."',
											irocode1 ='".$irocode1."',
											irocode2 ='".$irocode2."',
											tmecode  ='".$tmecode."',
											appointment_date ='".$date."'
											ON DUPLICATE KEY UPDATE 
											ironame  ='".$ironame."',
											irocode  ='".$irocode."',
											irocode1 ='".$irocode1."',
											irocode2 ='".$irocode2."',
											tmecode  ='".$tmecode."',
											appointment_date ='".$date."'";
					
					$res_iro1  		= 	parent::execQuery($ins_iro,$this->conn_iro);					
					$success="case one successful(NON EMPTY IRO)";
				}	
			}
		}else if(trim($this->params['iro_name'])=='' && $oldFlag){
			$updt_iro = "UPDATE tbl_appointment_iro SET
							ironame  ='".$ironame."',
							irocode  ='".$irocode."',
							irocode1 ='".$irocode1."',
							irocode2 ='".$irocode2."',
							tmecode  ='".$tmecode."',
							appointment_date ='".$date."'
							WHERE parentid='".$this->parentid."' ";
			$res_iro2  		= 	parent::execQuery($updt_iro,$this->conn_iro);							
			if($res_iro2)
			$success="case two successful(empty IRO update)";  
		}
		if($res_iro1 || $res_iro2){
			//~ $fp = fopen('../logs/log_tme/iro_appointmentlog'.date('Y-M').'.html','a')or exit('Unable to open file!');
			//~ $string="<br>#parentid=".$parentid."#Tme code=".$tmecode."##new=".$irocode."##old1=".$irocode1."##old2=".$irocode2."##old3=".$irocode3."##".$success."####".$date."#".$swap;
			//~ fwrite($fp,$string );
			//~ fclose($fp);
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
			
		}else {
			$message = "insertion failed";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}
	}
	public function jdpayEcsPopup(){
		
		$extn 	=	trim($this->params['extn']);
		$login_city = trim($this->params['login_city']);
		
		$datafound = 0;
		if($this->conn_city=='remote')
		{
			$sqlJDPayECSData = "SELECT f12_id,compname,parentid,calluid,scity FROM tbl_f12_transfer_data WHERE (ext='".$extn."' OR cli='".$extn."') AND status=0 ORDER BY DATETIME DESC LIMIT 1;";
			$resJDPayECSData = parent::execQuery($sqlJDPayECSData,$this->conn_iro);
			if($resJDPayECSData && parent::numRows($resJDPayECSData)>0){
				$row_ecsdata 	= 	parent::fetchData($resJDPayECSData);
				$sqlCityInfo	=	"SELECT cities FROM tbl_zone_cities WHERE cities='".$row_ecsdata['scity']."' AND main_zone='".$login_city."'";
				$resCityInfo  	=	parent::execQuery($sqlCityInfo,$this->conn_local);
				if($resCityInfo && parent::numRows($resCityInfo)>0){
					$datafound = 1;
				}
			}
			
		}else
		{
			$sqlJDPayECSData = "SELECT f12_id,compname,parentid,calluid,scity FROM tbl_f12_transfer_data WHERE (ext='".$extn."' OR cli='".$extn."') AND status=0 ORDER BY DATETIME DESC LIMIT 1;";
			$resJDPayECSData = parent::execQuery($sqlJDPayECSData,$this->conn_iro);
			if($resJDPayECSData && parent::numRows($resJDPayECSData)>0){
				$row_ecsdata 	= 	parent::fetchData($resJDPayECSData);
				$datafound = 1;
			}
		}
		if($datafound == 1){
			$sqlOptName = "SELECT opt_name FROM db_iro.tbl_f12_option WHERE opt_code = '".$row_ecsdata['f12_id']."'";
			$resOptName = parent::execQuery($sqlOptName,$this->conn_iro);
			if($resOptName && parent::numRows($resOptName)>0){
				$row_optinfo 	= 	parent::fetchData($resOptName);
				$row_ecsdata['opt_name'] = $row_optinfo['opt_name'];
			}
			
		}
		$res_arr = array();
		if($datafound == 1){
			$res_arr['data']			= $row_ecsdata;
			$res_arr['error']['code']	= 0;
			$res_arr['error']['msg']	="Data Found";
		}else{
			$res_arr['error']['code']	= 1;
			$res_arr['error']['msg']	="Data Not Found";
		}
		return $res_arr;
	}

	public function bformIncorrectLoginfo(){
		$return_arr = array();

		if($this->params['incorrectdata']!='[]'){
			$sent_for_dc=1;
		}else{
			$sent_for_dc=0;
			$this->params['incorrectdata']=' ';
		}
		$Decodeparams		=	json_decode($this->params['correctdata'],true);
		$IncorrectDecode	=	json_decode($this->params['incorrectdata'],true);
		$fetch_val			=	"SELECT show_flag from tbl_incorrect_log where parentid='".$this->parentid."'"; 
		$fetch_val_con		=	parent::execQuery($fetch_val,$this->conn_tme);
		$fect_show_flag		=	parent::fetchData($fetch_val_con);

		if(!$IncorrectDecode){ 
				$IncorrectDecode	=	' ';				
		}
		else{
			$resultInCorrectArray	=	array();
			if($IncorrectDecode){
				$key	=	array();
				foreach($IncorrectDecode as $k=>$value){
					if(!in_array($value['key'],$key))
						array_push($key,$value['key']);
					if($value['key']){
						if($resultInCorrectArray[$value['key']]['oldVal']!='')
							$resultInCorrectArray[$value['key']]['oldVal']	=	$resultInCorrectArray[$value['key']]['oldVal'].",".$value['oldVal'];
						else
						{
							$resultInCorrectArray[$value['key']]['oldVal']=$value['oldVal'];
						}
						if($resultInCorrectArray[$value['key']]['newVal']!='')
							$resultInCorrectArray[$value['key']]['newVal']	=	$resultInCorrectArray[$value['key']]['newVal'].",".$value['newVal'];
						else
						{
							$resultInCorrectArray[$value['key']]['newVal']=$value['newVal'];
						}
					}
				}
			}
			$resultInCorrectArray	=	json_encode($resultInCorrectArray);
			if($fect_show_flag['show_flag']	==	0 || $fect_show_flag['show_flag']	==	2){ 
				$show_flag	=	$fect_show_flag['show_flag']	+	1;
			}
			$insert_incorrect_log	=	"INSERT into tbl_incorrect_log SET `parentid`='".$this->parentid."',
																   `show_flag`='".$show_flag."'
										 ON DUPLICATE KEY UPDATE   `parentid`='".$this->parentid."',
																   `show_flag`='".$show_flag."'";
			$con_insert_flag		=	parent::execQuery($insert_incorrect_log,$this->conn_tme);		
		}
		$resultCorrectArray	=	array();
			if($Decodeparams){
				$keyCorrect	=array();
				foreach($Decodeparams as $key=>$val){
					if(!in_array($val['key'],$keyCorrect))
							array_push($keyCorrect,$val['key']);
					if($val['key']){
							if($resultCorrectArray[$val['key']]['oldVal']!='')
								$resultCorrectArray[$val['key']]['oldVal']	=	$resultCorrectArray[$val['key']]['oldVal'].",".$val['oldVal'];
							else
							{
								$resultCorrectArray[$val['key']]['oldVal']=$val['oldVal'];
							}
							if($resultCorrectArray[$val['key']]['newVal']!='')
								$resultCorrectArray[$val['key']]['newVal']	=	$resultCorrectArray[$val['key']]['newVal'].",".$val['newVal'];
							else
							{
								$resultCorrectArray[$val['key']]['newVal']=$val['newVal'];
							}
					}
				}
				if($fect_show_flag['show_flag']	==	0 || $fect_show_flag['show_flag']	==	2){ 
					$show_flag	=	$fect_show_flag['show_flag']	+	1;
				}
				$insert_incorrect_log	=	"INSERT into tbl_incorrect_log SET `parentid`='".$this->parentid."',
																	   `show_flag`='".$show_flag."'
											 ON DUPLICATE KEY UPDATE   `parentid`='".$this->parentid."',
																	   `show_flag`='".$show_flag."'";
				$con_insert_flag		=	parent::execQuery($insert_incorrect_log,$this->conn_tme);	
			}

			$resultCorrectArray	=	json_encode($resultCorrectArray); 
			$user_id	=$this->params['ucode'];			
			$date 		=date('Y-m-d H:i:s');
			$parentid 	=$this->parentid;

			$insertBformChanges="INSERT into tbl_bform_dc_logs_np(parentid,bformData,bformIncorrect,empcode,updated_on,sent_for_dc,percentage,proceed) values('".$parentid."','".$resultCorrectArray."','".$resultInCorrectArray."','".$user_id."','".$date."','".$sent_for_dc."','".$this->params['percentage']."','".$this->params['proceed']."')";
			$coninsertBformChanges=parent::execQuery($insertBformChanges,$this->conn_tme);
			
			$insertBformChanges2="INSERT into tbl_correct_incorrect_logs_np(parentid,bformData,bformIncorrect,empcode,updated_on,sent_for_dc,percentage,proceed,compname,city) values('".$parentid."','".$resultCorrectArray."','".$resultInCorrectArray."','".$user_id."','".$date."','".$sent_for_dc."','".$this->params['percentage']."','".$this->params['proceed']."','".$this->params['compname']."','".$this->data_city."')";
			$coninsertBformChanges2=parent::execQuery($insertBformChanges2,$this->conn_tme);
			
			if($coninsertBformChanges2){
				$return_arr['error']['code'] 	= 0;
				$return_arr['error']['msg'] 	= 'Success';
			}
			else{
				$return_arr['error']['code'] 	= 1;
				$return_arr['error']['msg'] 	= 'Failed';	
			}
		return $return_arr;
	}
	
	public function fetchRestaurantInfo(){		
		$curl_url = $this->rest_url."/restDet/" . $this->params['doc_id'];
		$rest_info_arr 	= json_decode($this->curlCall($curl_url),true);       
        return $rest_info_arr;
	}

	public function ecsTransDetailsUpdate(){
		$res_arr = array();
		$ip_address = 	$this->params['ip'];
		$ucode 		=	$this->params['ucode'];
		$uname 		=	$this->params['uname'];
		$calluid 	=	$this->params['calluid'];
		$identifier = 	$this->params['identifier'];
		$extnID 	=	$this->params['extn'];
			
		if($ucode!='' && $calluid!='')
		{
			$upd_ecs = "UPDATE db_iro.tbl_f12_transfer_data SET status=1,tme_code ='".$ucode."',tme_name ='".$uname."' WHERE calluid ='".$calluid."'";
			$con_upd_ecs = parent::execQuery($upd_ecs,$this->conn_local);
			//$identifier = $this->params['identifier'];
		
			if($identifier == 'ECS001')
			{
				$contract_flag = 0;
			}else if($identifier == 'LR0001')
			{
				$contract_flag = 1;
			}

			$upd_lead_api 		= "INSERT INTO d_jds.tbl_transfer_api_log
								SET
								parentid = '".$this->parentid."',
								insert_date = NOW(),
								extn = '".$extnID."',
								tmecode   = '".$ucode."',
								flag = 2";
			$con_upd_lead_retlog = parent::execQuery($upd_lead_api,$this->conn_local);	
		
			 /*****2months Conditions*******/
			
			$select_irated_flag = "SELECT * FROM db_iro.tbl_f12_transfer_data WHERE calluid ='".$calluid."' ";
			$select_irated_flag_Res	= parent::execQuery($select_irated_flag,$this->conn_local);
			$select_irated_flag_Data = parent::fetchData($select_irated_flag_Res);
			
			$flag 		= $select_irated_flag_Data['flag'];
			$parentid 	= $select_irated_flag_Data['parentid'];
			$dcity 		= $select_irated_flag_Data['dcity'];
			$compname 	= $select_irated_flag_Data['compname'];
			
			if($flag & 2 == 2){ // flag = 2 means irate cases //
				$source = "IRO Irated Transfer";
			}else{
				$source = "IRO Transferred";
			}
			if($identifier == 'ECS001')
			{
				$now = date('Y-m-d H:i:s');
				
				$upd_ecs_ret = "INSERT INTO d_jds.tbl_new_retention SET parentid   =	'".$this->parentid."',
												tmecode					=	'".$ucode."',
												tmename					=	'".$uname."',
												allocated_date      	=   NOW(),
												insert_date             =   '".$ucode."~".$now."',
												update_date				=	NOW(),
												companyname				=	'".$compname."',
												data_city				= '".$dcity."',
												escalated_details 		= '',
												reactivate_flag 		= '',
												stop_request_datetime 	= '',
												stop_remark 			= '',
												reactivated_on 			= '',
												reactivated_by 			= '',
												ecs_reject_approved 	= '',
												stop_reason 			= '',
												approve_datetime 		= '',
												approved_by 			= '',
												reallocated_flag		= '0',
												ecs_stop_flag 			= '0',
												action_flag 			= '0',
												state					= '2',
												transfer_by_iro			= '1',
												request_source 			= '".$source."',
												contract_flag 			= '".$contract_flag."',
												ip						= '".$ip_address."'
												ON DUPLICATE KEY UPDATE
												tmecode					=	'".$ucode."',
												tmename					=	'".$uname."',
												companyname				=	'".$compname."',
												data_city				= '".$dcity."',
												state					= 2,
												update_date				=	NOW(),
												allocated_date      	=   NOW(),
												escalated_details 		= '',
												reactivate_flag 		= '',
												stop_request_datetime 	= '',
												stop_remark 			= '',
												reactivated_on 			= '',
												reactivated_by 			= '',
												ecs_reject_approved 	= '',
												stop_reason 			= '',
												approve_datetime 		= '',
												approved_by 			= '',
												ip						= '',
												reallocated_flag		= '0',
												ecs_stop_flag 			= '0',
												action_flag 			= '0',
												transfer_by_iro			= '1',
												state					= '2',
												request_source 			= '".$source."',
												contract_flag 			= '".$contract_flag."',
												ip						= '".$ip_address."'";
				$con_upd_ecs_ret =	parent::execQuery($upd_ecs_ret,$this->conn_local);

				$upd_ecs_log 		= "INSERT INTO d_jds.tbl_new_retention_log
									SET
									parentid = '".$this->parentid."',
									tmecode = '".$ucode."',
									tmename = '".$uname."',
									insert_date = now(),
									companyname = '".$compname."',
									data_city = '".$dcity."',
									transfer_by_iro			= '1',
									request_source = '".$source."',
									contract_flag = '".$contract_flag."',
									state = 2";
				$con_upd_ecs_retlog	= parent::execQuery($upd_ecs_log,$this->conn_local);
			}
			elseif ($identifier == 'LR0001'){
				$now = date('Y-m-d H:i:s');
				 
				$upd_lead_ret = "INSERT INTO d_jds.tbl_new_lead SET parentid	=	'".$this->parentid."',
													tmecode					=	'".$ucode."',
													tmename					=	'".$uname."',
													allocated_date      	=   NOW(),
													insert_date             =   '".$ucode."~".$now."',
													update_date				=	NOW(),
													companyname				=	'".$compname."',
													data_city				= '".$dcity."',
													state					= '2',
													action_flag 			= 0,
													request_source 			= '".$source."',
													transfer_by_iro			= '1',
													contract_flag 			= '".$contract_flag."',
													ip						= '".$ip_address."'
													ON DUPLICATE KEY UPDATE
													tmecode					=	'".$ucode."',
													tmename					=	'".$uname."',
													companyname				=	'".$compname."',
													data_city				= '".$dcity."',
													state					= 2,
													state					= '2',
													update_date				=	NOW(),
													request_source 			= '".$source."',
													transfer_by_iro			= '1',
													contract_flag 			= '".$contract_flag."',
													ip						= '".$ip_address."'";
													
				$con_upd_lead_ret	= parent::execQuery($upd_lead_ret,$this->conn_local);
			}			
		}
		else{
			
			$message = "Invalid Params.";
			echo json_encode($this->sendResponse($message,1));
			die();
			
		}	
		$res_arr['error']['code']	= 0;
		$res_arr['con_upd_ecs']		= $con_upd_ecs;
		$res_arr['con_upd_ecs_ret']	= $con_upd_ecs_ret;
		$res_arr['con_upd_lead_ret']= $con_upd_lead_ret;

		return $res_arr;
	}
	function updateRetentionTmeInfo(){
		
		$ecs_flag 		=	$this->params['ecs_flag'];
		$companyname 	= 	$this->params['companyname'];
		$ip 			=	$this->params['ip'];
		
		$return_arr = array();
		if($ecs_flag == 1)
		{
			$sqlUpdateLeadDetails	= "UPDATE tbl_new_lead SET  tmename = '".$this->uname."', tmecode = '".$this->ucode."', update_date = NOW(), allocated_date = NOW() WHERE parentid ='".$this->parentid."'";
			$resUpdateLeadDetails	= parent::execQuery($sqlUpdateLeadDetails,$this->conn_local);
			
			$sqlLeadInfoLog =  "INSERT INTO tbl_new_lead_log SET 
								parentid		= '".$this->parentid."',
								tmecode			= '".$this->ucode."',
								tmename			= '".$this->uname."',
								insert_date     = NOW(),
								update_date		= NOW(),
								companyname		= '".$companyname."',
								data_city		= '".$this->data_city."',
								ip				= '".$ip."'";
			$resLeadInfoLog	= parent::execQuery($sqlLeadInfoLog,$this->conn_local);												
			
			if($resUpdateLeadDetails){
				$return_arr['error']['code'] 	= 0;
				$return_arr['error']['msg'] 	= 'Success';
			}
			else{
				$return_arr['error']['code'] 	= 1;
				$return_arr['error']['msg'] 	= 'Failed';	
			}
		}
		else
		{
			$sqlUpdateRetentionDetails	= "UPDATE tbl_new_retention SET  tmename = '".$this->uname."',tmecode = '".$this->ucode."',update_date = NOW(),allocated_date = NOW() WHERE parentid ='".$this->parentid."'";
			$resUpdateRetentionDetails	= parent::execQuery($sqlUpdateRetentionDetails,$this->conn_local);
			
			 $sqlRetentionInfoLog 		=  "INSERT INTO tbl_new_retention_log SET 
											parentid		= '".$this->parentid."',
											tmecode			= '".$this->ucode."',
											tmename			= '".$this->uname."',
											insert_date     = NOW(),
											companyname		= '".$companyname."',
											data_city		= '".$this->data_city."',
											ip				= '".$ip."'";
			$resRetentionInfoLog	= parent::execQuery($sqlRetentionInfoLog,$this->conn_local);												
			
			if($resUpdateRetentionDetails){
				$return_arr['error']['code'] 	= 0;
				$return_arr['error']['msg'] 	= 'Success';
			}
			else{
				$return_arr['error']['code'] 	= 1;
				$return_arr['error']['msg'] 	= 'Failed';	
			}
		}
		return $return_arr;
		
	}
	function updateRepeatCount(){
		
		$ecs_flag 		=	$this->params['ecs_flag'];
		$companyname 	= 	$this->params['companyname'];
		$ip 			=	$this->params['ip'];
		
		$return_arr = array();
		if($ecs_flag == 1)
		{
			$sqlUpdtLeadRepeatCount = "UPDATE tbl_new_lead SET  repeat_call = '4', repeatcall_taggedon=NOW() WHERE parentid ='".$this->parentid."'";
			$resUpdtLeadRepeatCount	= parent::execQuery($sqlUpdtLeadRepeatCount,$this->conn_local);
			
			$sqlLeadRepeatCntLog = "INSERT INTO tbl_new_lead_log SET 
									parentid		= '".$this->parentid."',
									tmecode			= '".$this->ucode."',
									tmename			= '".$this->uname."',
									insert_date     = NOW(),
									update_date		= NOW(),
									companyname		= '".$companyname."',
									data_city		= '".$this->data_city."',
									repeat_call		= '4',
									ip				= '".$ip."'";
			$resLeadRepeatCntLog = parent::execQuery($sqlLeadRepeatCntLog,$this->conn_local);
			if($resUpdtLeadRepeatCount){
				$return_arr['error']['code'] 	= 0;
				$return_arr['error']['msg'] 	= 'Success';
			}
			else{
				$return_arr['error']['code'] 	= 1;
				$return_arr['error']['msg'] 	= 'Failed';	
			}
		}
		else // discussed with sandeep
		{
			$sqlUpdtLeadRetentionCount 	= "UPDATE tbl_new_retention SET  repeat_call = '3', repeatcall_taggedon=NOW() WHERE parentid ='".$this->parentid."'";
			$resUpdtLeadRetentionCount	= parent::execQuery($sqlUpdtLeadRetentionCount,$this->conn_local);
				
			$sqlRetentionRepeatCntLog 	=  "INSERT INTO tbl_new_retention_log SET
											parentid		= '".$this->parentid."',
											tmecode			= '".$this->ucode."',
											tmename			= '".$this->uname."',
											insert_date     = NOW(),
											companyname		= '".$companyname."',
											data_city		= '".$this->data_city."',
											repeat_call		= '3',
											ip				= '".$ip."'";
			$resRetentionRepeatCntLog = parent::execQuery($sqlRetentionRepeatCntLog,$this->conn_local);
				
			$sqlLeadData = "SELECT parentid FROM tbl_new_lead WHERE parentid = '".$this->parentid."'";
			$resLeadData = parent::execQuery($sqlLeadData,$this->conn_local);
			
			if($resLeadData && parent::numRows($resLeadData)>0){
				$upd_lead 	= "UPDATE tbl_new_lead SET  tmename = '".$this->uname."',tmecode = '".$this->ucode."', allocated_date = NOW(), update_date = NOW() WHERE parentid = '".$this->parentid."' ";
				$res_lead 	= parent::execQuery($upd_lead,$this->conn_local);
				
				$lead_info_log = "INSERT INTO tbl_new_lead_log SET
										parentid		= '".$this->parentid."',
										tmecode			= '".$this->ucode."',
										tmename			= '".$this->uname."',
										insert_date     = NOW(),
										update_date		= NOW(),
										companyname		= '".$companyname."',
										data_city		= '".$this->data_city."',
										ip				= '".$ip."'";	
				$lead_res_log 	= parent::execQuery($lead_info_log,$this->conn_local);
			}
			if($resUpdtLeadRetentionCount){
				$return_arr['error']['code'] 	= 0;
				$return_arr['error']['msg'] 	= 'Success';
			}
			else{
				$return_arr['error']['code'] 	= 1;
				$return_arr['error']['msg'] 	= 'Failed';	
			}
		}
		return $return_arr;
	}
	function insertgenralinfoshadow(){
		$res_arr = array();
		
		$latlongdata = $this->getLatLongInfo($this->params['pincode']);
		if(count($latlongdata)>0){
			$this->params['latitude'] 	= $latlongdata['latitude_final'];
			$this->params['longitude'] 	= $latlongdata['longitude_final'];
		}
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			
			$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
			$geninfo_upt = array();
			
			$geninfo_upt['companyname'] 			= $this->params['companyname'];
			$geninfo_upt['country'] 				= '98';
			$geninfo_upt['state'] 					= $this->params['state'];
			$geninfo_upt['city'] 					= $this->params['city'];
			$geninfo_upt['display_city'] 			= $this->params['display_city'];
			$geninfo_upt['area'] 					= $this->params['area'];
			$geninfo_upt['building_name'] 			= $this->params['building_name'];
			$geninfo_upt['street'] 					= $this->params['street'];
			$geninfo_upt['landmark'] 				= $this->params['landmark'];
			$geninfo_upt['pincode'] 				= $this->params['pincode'];
			$geninfo_upt['latitude'] 				= $this->params['latitude'];
			$geninfo_upt['longitude'] 				= $this->params['longitude'];
			$geninfo_upt['geocode_accuracy_level'] 	= $this->params['geocode_accuracy_level'];
			$geninfo_upt['full_address'] 			= $this->params['full_address'];
			$geninfo_upt['stdcode'] 				= $this->params['stdcode'];
			$geninfo_upt['landline'] 				= $this->params['landline'];
			$geninfo_upt['landline_display'] 		= $this->params['landline_display'];
			$geninfo_upt['mobile'] 					= $this->params['mobile'];
			$geninfo_upt['mobile_display'] 			= $this->params['mobile_display'];
			$geninfo_upt['mobile_feedback'] 		= $this->params['mobile_feedback'];
			$geninfo_upt['fax'] 					= $this->params['fax'];
			$geninfo_upt['tollfree'] 				= $this->params['tollfree'];
			$geninfo_upt['tollfree_display'] 		= $this->params['tollfree_display'];
			$geninfo_upt['email'] 					= $this->params['email'];
			$geninfo_upt['email_display'] 			= $this->params['email_display'];
			$geninfo_upt['email_feedback'] 			= $this->params['email_feedback'];
			$geninfo_upt['sms_scode'] 				= $this->params['sms_scode'];
			$geninfo_upt['website'] 				= $this->params['website'];
			$geninfo_upt['contact_person'] 			= $this->params['contact_person'];
			$geninfo_upt['contact_person_display'] 	= $this->params['contact_person_display'];
			$geninfo_upt['othercity_number'] 		= $this->params['othercity_number'];
			$geninfo_upt['mobile_admin'] 			= $this->params['mobile_admin'];
			$geninfo_upt['data_city'] 				= $this->data_city;
					
			$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
			$mongo_inputs['table_data'] = $mongo_data;
			$resGeninfoShadow = $this->mongo_obj->updateData($mongo_inputs);
		}
			
		$ins_gen_info_entries	=	"INSERT INTO tbl_companymaster_generalinfo_shadow SET 	parentid		=	'".$this->parentid."',
																companyname		=	'".addslashes(stripslashes($this->params['companyname']))."',
																country			=	'98',
																state			=	'".$this->params['state']."',
																city			=	'".$this->params['city']."',
																display_city	=	'".$this->params['display_city']."',
																area			=	'".$this->params['area']."',
																building_name	=	'".addslashes(stripslashes($this->params['building_name']))."',
																street			=	'".addslashes(stripslashes($this->params['street']))."',
																landmark		=	'".addslashes(stripslashes($this->params['landmark']))."',
																pincode			=	'".$this->params['pincode']."',
																latitude		=	'".$this->params['latitude']."',
																longitude		=	'".$this->params['longitude']."',
																geocode_accuracy_level = '".$this->params['geocode_accuracy_level']."',
																full_address	=	'".addslashes(stripslashes($this->params['full_address']))."',
																stdcode			=	'".$this->params['stdcode']."',
																landline		=	'".$this->params['landline']."',
																landline_display		=	'".$this->params['landline_display']."',
																mobile			=	'".$this->params['mobile']."',
																mobile_display	=	'".$this->params['mobile_display']."',
																mobile_feedback	=	'".$this->params['mobile_feedback']."',
																fax				=	'".$this->params['fax']."',
																tollfree				=	'".$this->params['tollfree']."',
																tollfree_display		=	'".$this->params['tollfree_display']."',
																email			=	'".$this->params['email']."',
																email_display	=	'".$this->params['email_display']."',
																email_feedback	=	'".$this->params['email_feedback']."',
																sms_scode		=	'".$this->params['sms_scode']."',
																website			=	'".addslashes(stripslashes($this->params['website']))."',
																contact_person	=	'".addslashes(stripslashes($this->params['contact_person']))."',
																contact_person_display	=	'".addslashes(stripslashes($this->params['contact_person_display']))."',
																othercity_number=	'".$this->params['othercity_number']."',
																mobile_admin	=	'".$this->params['mobile_admin']."',	
																data_city		='".$this->data_city."'
							ON DUPLICATE KEY UPDATE								
																companyname		=	'".addslashes(stripslashes($this->params['companyname']))."',
																country			=	'98',
																state			=	'".$this->params['state']."',
																city			=	'".$this->params['city']."',
																display_city	=	'".$this->params['display_city']."',
																area			=	'".$this->params['area']."',
																building_name	=	'".addslashes(stripslashes($this->params['building_name']))."',
																street			=	'".addslashes(stripslashes($this->params['street']))."',
																landmark		=	'".addslashes(stripslashes($this->params['landmark']))."',
																pincode			=	'".$this->params['pincode']."',
																latitude		=	'".$this->params['latitude']."',
																longitude		=	'".$this->params['longitude']."',
																geocode_accuracy_level = '".$this->params['geocode_accuracy_level']."',
																full_address	=	'".addslashes(stripslashes($this->params['full_address']))."',
																stdcode			=	'".$this->params['stdcode']."',
																landline		=	'".$this->params['landline']."',
																landline_display		=	'".$this->params['landline_display']."',
																mobile			=	'".$this->params['mobile']."',
																mobile_display	=	'".$this->params['mobile_display']."',
																mobile_feedback	=	'".$this->params['mobile_feedback']."',
																fax				=	'".$this->params['fax']."',
																tollfree		=	'".$this->params['tollfree']."',
																tollfree_display			=	'".$this->params['tollfree_display']."',
																email			=	'".$this->params['email']."',
																email_display	=	'".$this->params['email_display']."',
																email_feedback	=	'".$this->params['email_feedback']."',
																sms_scode		=	'".$this->params['sms_scode']."',
																website			=	'".addslashes(stripslashes($this->params['website']))."',
																contact_person	=	'".addslashes(stripslashes($this->params['contact_person']))."',
																contact_person_display	=	'".addslashes(stripslashes($this->params['contact_person_display']))."',
																othercity_number=	'".$this->params['othercity_number']."',
																mobile_admin	=	'".$this->params['mobile_admin']."',
																data_city		='".$this->data_city."'";
			$ins_gen_info_entries = $ins_gen_info_entries."/* TMEMONGOQRY */";														
			//$resGeninfoShadow = parent::execQuery($ins_gen_info_entries, $this->conn_temp);	
		
		if($resGeninfoShadow){
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
		}else{
			$message = "insertion failed";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}	
	}
	
	function insertextradetailsshadow(){
		$res_arr = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			
			$extrdet_tbl = "tbl_companymaster_extradetails_shadow";
			$extrdet_upt = array();
			
			$extrdet_upt['companyname'] 			= $this->params['companyname'];
			$extrdet_upt['landline_addinfo'] 		= $this->params['landline_addinfo'];
			$extrdet_upt['mobile_addinfo'] 			= $this->params['mobile_addinfo'];
			$extrdet_upt['tollfree_addinfo'] 		= $this->params['tollfree_addinfo'];
			$extrdet_upt['turnover'] 				= $this->params['turnover'];
			$extrdet_upt['working_time_start'] 		= $this->params['working_time_start'];
			$extrdet_upt['working_time_end'] 		= $this->params['working_time_end'];
			$extrdet_upt['payment_type'] 			= $this->params['payment_type'];
			$extrdet_upt['year_establishment'] 		= $this->params['year_establishment'];
			$extrdet_upt['statement_flag'] 			= $this->params['statement_flag'];
			$extrdet_upt['updatedBy'] 				= $this->params['updatedBy'];
			$extrdet_upt['updatedOn'] 				= date('Y-m-d H:i:s');
			$extrdet_upt['data_city'] 				= $this->data_city;
			$extrdet_upt['social_media_url'] 		= $this->params['social_media_url'];
			$extrdet_upt['catidlineage'] 			= $this->params['catidlineage'];
			$extrdet_upt['award'] 					= $this->params['award'];
			$extrdet_upt['testimonial'] 			= $this->params['testimonial'];
			$extrdet_upt['proof_establishment'] 	= $this->params['proof_establishment'];
			$extrdet_upt['fb_prefered_language'] 	= $this->params['fb_prefered_language'];
			$extrdet_upt['tag_line'] 				= $this->params['tag_line'];
			$extrdet_upt['certificates'] 			= $this->params['certificates'];
			$extrdet_upt['accreditations'] 			= $this->params['accreditations'];
			$extrdet_upt['no_employee'] 			= $this->params['no_employee'];
			$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
			
			$extrdet_ins = array();
			$extrdet_ins['createdby'] 				= $this->params['createdby'];
			$extrdet_ins['createdtime']				= $this->params['createdtime'];
			$extrdet_ins['original_creator'] 		= $this->params['original_creator'];
			$extrdet_ins['original_date'] 			= date('Y-m-d H:i:s');
			
			$mongo_data[$extrdet_tbl]['insertdata'] = $extrdet_ins;
			$mongo_inputs['table_data'] = $mongo_data;
			$resextrainfoShadow = $this->mongo_obj->updateData($mongo_inputs);
			
		}
		$ins_extra_det			=	"INSERT INTO tbl_companymaster_extradetails_shadow SET	`companyname` 			='".addslashes(stripslashes($this->params['companyname']))."',
																	`parentid`				='".$this->parentid."',
																	`landline_addinfo`	='".addslashes(stripslashes($this->params['landline_addinfo']))."',
																	`mobile_addinfo`		='".addslashes(stripslashes($this->params['mobile_addinfo']))."',
																	`tollfree_addinfo`	='".addslashes(stripslashes($this->params['tollfree_addinfo']))."',
																	`turnover`			='".$this->params['turnover']."',
																	`working_time_start`  = '".$this->params['working_time_start']."',
																	`working_time_end` 	= '".$this->params['working_time_end']."',
																	`payment_type`		='".$this->params['payment_type']."',
																	`year_establishment`	='".$this->params['year_establishment']."',
																	`statement_flag`		=	'".$this->params['statement_flag']."',
																	`createdby`			='".$this->params['createdby']."',
																	`createdtime`			='".$this->params['createdtime']."', 
																	`original_creator`	='".$this->params['original_creator']."',
																	`original_date`		='".date('Y-m-d H:i:s')."',
																	`updatedBy`			='".$this->params['updatedBy']."',
																	`updatedOn`			='".date('Y-m-d H:i:s')."',
																	`data_city`			='".$this->data_city."',
																	`social_media_url`	='".addslashes(stripslashes($this->params['social_media_url']))."',
																	`catidlineage`		='".$this->params['catidlineage']."', 
																	`fb_prefered_language` 		='".addslashes(stripslashes($this->params['fb_prefered_language']))."',
																	`tag_line`			='".addslashes(stripslashes($this->params['tag_line']))."',
																	`certificates` 		='".addslashes(stripslashes($this->params['certificates']))."',
																	`accreditations` 	='".addslashes(stripslashes($this->params['accreditations']))."',
																	`no_employee` 		='".addslashes(stripslashes($this->params['no_employee']))."',
																	`award` 			='".addslashes(stripslashes($this->params['award']))."',
																	`testimonial` 			='".addslashes(stripslashes($this->params['testimonial']))."',
																	`proof_establishment` 			='".addslashes(stripslashes($this->params['proof_establishment']))."'
								ON DUPLICATE KEY UPDATE
																	`fb_prefered_language` 		='".addslashes(stripslashes($this->params['fb_prefered_language']))."',
																	`companyname` 		='".addslashes(stripslashes($this->params['companyname']))."',
																	`landline_addinfo`	='".addslashes(stripslashes($this->params['landline_addinfo']))."',
																	`mobile_addinfo`		='".addslashes(stripslashes($this->params['mobile_addinfo']))."',
																	`tollfree_addinfo`	='".addslashes(stripslashes($this->params['tollfree_addinfo']))."',
																	`turnover`			='".addslashes(stripslashes($this->params['turnover']))."',
																	`working_time_start` 	= '".$this->params['working_time_start']."',
																	`working_time_end` 	= '".$this->params['working_time_end']."',
																	`payment_type`		='".$this->params['payment_type']."',
																	`year_establishment`	='".$this->params['year_establishment']."',
																	`statement_flag`		=	'".$this->params['statement_flag']."',
																	`updatedBy`			='".$this->params['updatedBy']."',
																	`updatedOn`			='".date('Y-m-d H:i:s')."',
																	`data_city`			='".$this->data_city."',
																	`social_media_url`	='".addslashes(stripslashes($this->params['social_media_url']))."',
																	`catidlineage`		='".$this->params['catidlineage']."',
																	`tag_line`			='".addslashes(stripslashes($this->params['tag_line']))."',
																	`certificates` 		='".addslashes(stripslashes($this->params['certificates']))."',
																	`accreditations` 	='".addslashes(stripslashes($this->params['accreditations']))."',
																	`no_employee` 		='".addslashes(stripslashes($this->params['no_employee']))."',
																	`award` 			='".addslashes(stripslashes($this->params['award']))."',
																	`testimonial` 			='".addslashes(stripslashes($this->params['testimonial']))."',
																	`proof_establishment` 			='".addslashes(stripslashes($this->params['proof_establishment']))."'";
		$ins_extra_det = $ins_extra_det."/* TMEMONGOQRY */";															
		//$resextrainfoShadow = parent::execQuery($ins_extra_det, $this->conn_temp);	
		
		if($resextrainfoShadow){
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
		}else{
			$message = "insertion failed";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}	
	}
	
	public function inserttempinter(){
		
		require_once('versioninitclass.php');
		$versionInitClass = new versionInitClass($this->params);
		$version = $versionInitClass->setversion();
		
		$paid_match_flag 	= 	0;
		$parent_ids_list	=	'';
		$actMode_flag = " actmode = '0',";
		$actMode_mongo = 0;
		if((isset($this->params['actmode']) && $this->params['actmode']))
		{
			$actMode_flag = " actmode = '1',";
			$actMode_mongo = 1;
		}
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_data = array();
			$intermd_upt = array();
			$mongo_inputs['parentid'] 		= $this->parentid;
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			
			$intermd_tbl 						= "tbl_temp_intermediate";			
			$intermd_upt['contracts'] 			= $parent_ids_list;
			$intermd_upt['deactivate'] 			= 'N';
			$intermd_upt['displayType'] 		= 'IRO~WEB~WIRELESS';			
			$intermd_upt['datesource'] 			= date('Y-m-d H:i:s');
			$intermd_upt['nonpaid'] 			= $this->params['nonpaid'];
			$intermd_upt['c2c'] 				= $this->params['c2c'];
			$intermd_upt['hiddenCon'] 			= $this->params['hiddenCon'];
			$intermd_upt['web'] 				= $this->params['web'];
			$intermd_upt['version'] 			= $version;
			$intermd_upt['facility_flag'] 		= '0';
			$intermd_upt['mask'] 				= '0';
			$intermd_upt['freez'] 				= '0';
			$intermd_upt['contract_calltype'] 	= 'B';
			$intermd_upt['actMode'] 			= $actMode_mongo;
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$intermd_ins = array();
			$intermd_ins['tme_code'] 		= $this->params['ucode'];
			$intermd_ins['tme_email'] 		= $this->params['emp_emailId'];
			$intermd_ins['tme_mobile'] 		= $this->params['emp_mobile'];
			$mongo_data[$intermd_tbl]['insertdata'] = $intermd_ins;
			
			$mongo_inputs['table_data'] = $mongo_data;
			$res = $this->mongo_obj->updateData($mongo_inputs);
		}
		
		$sql = "INSERT INTO tbl_temp_intermediate SET
				contracts 	= '".$parent_ids_list."',
				parentid 	= '".$this->parentid."',
				deactivate 	= 'N',	
				displayType = 'IRO~WEB~WIRELESS',
				tme_code   	= '".$this->params['ucode']."',
				tme_email  	= '".$this->params['emp_emailId']."',
				tme_mobile 	= '".$this->params['emp_mobile']."',
				".$actMode_flag."
				datesource 	= '".date("Y-m-d H:i:s")."',
				nonpaid = '".$this->params['nonpaid']."',
				c2c = '".$this->params['c2c']."',
				hiddenCon = '" .$this->params['hiddenCon']."',
				web = '".$this->params['web']."',
				version = '".$version."',
				facility_flag = 0
				ON DUPLICATE KEY UPDATE
				contracts 	= '".$parent_ids_list."',
				contract_calltype = 'B',
				deactivate 	= 'N',							
				displayType = 'IRO~WEB~WIRELESS',
				freez 		= '0',
				mask 		='0',
				".$actMode_flag."
				datesource 	= '".date("Y-m-d H:i:s")."',
				nonpaid = '".$this->params['nonpaid']."',
				c2c = '".$this->params['c2c']."',
				hiddenCon = '" .$this->params['hiddenCon']."',
				web = '".$this->params['web']."',
				version = '".$version."',
				facility_flag = 0";
		$sql = $sql."/* TMEMONGOQRY */";
		//$res  		= 	parent::execQuery($sql,$this->conn_temp);	
		if($res){
			$message = "inserted successfully";
			return $res_arr =  json_encode($this->sendResponse($message,0));
		}else {
			$message = "insertion falied";
			return $res_arr =  json_encode($this->sendResponse($message,1));
		}
			
	}
	
	function topVerifiedData(){
		$resArr 						= array();
		$select_topcompany_data			=	"Select parentid from tbl_verifyTopcompanyData where (parentid='".$this->params['parentid']."' or parentid='p".$this->params['parentid']."')";
		$fetch_topcompany_data 			= 	parent::execQuery($select_topcompany_data, $this->conn_local);
		if(parent::numRows($fetch_topcompany_data) > 0){
			$resArr['errorCode']		=	0;
			$resArr['errorMsg']			=	'Data Found';
		}else{
			$resArr['errorCode']		=	1;
			$resArr['errorMsg']			=	'Data Not Found';
		}
		return $resArr;
	}
	
	function businessTempdataIdc(){
		$tempdata = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['module']       	= $this->module;
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_business_temp_data"=>"contractid,categories,catIds,htmldump,slogan,categories_list,pages,bid_day_sel,bid_timing,threshold,autobid,catSelected,uId,mainattr,facility,companyName,avgAmt,percentage,comp_deduction_amt,thresholdform,thresholdType,original_catids,authorised_categories,bid_lead_num,bid_type,nationalcatIds,parentname,bid_led_num_year,thresholdPercnt,TotThresh,thresWeekSup,thresDailySup,thresMonthSup,bid_lead_num_sys,significance,htmldump_np,slogan_np,category_flow_info"
			));
				
			$tempdata = $this->mongo_obj->getShadowData($mongo_inputs);
			$mongo_inputs = array();
			$mongo_inputs['module']       	= 'ME';
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$intermd_tbl 					= "tbl_business_temp_data";			
			$intermd_upt['contractid'] 		= $tempdata['tbl_business_temp_data']['contractid'];
			$intermd_upt['categories'] 		= $tempdata['tbl_business_temp_data']['categories'];
			$intermd_upt['catIds'] 			= $tempdata['tbl_business_temp_data']['catIds'];
			$intermd_upt['nationalcatIds'] 	= $tempdata['tbl_business_temp_data']['nationalcatIds'];
			$intermd_upt['catSelected'] 	= $tempdata['tbl_business_temp_data']['catSelected'];
			$intermd_upt['original_catids'] = $tempdata['tbl_business_temp_data']['original_catids'];
			$intermd_upt['htmldump'] 		= $tempdata['tbl_business_temp_data']['htmldump'];
			$intermd_upt['slogan'] 			= $tempdata['tbl_business_temp_data']['slogan'];
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			$mongo_inputs['table_data'] 	= $mongo_data;
			$res = $this->mongo_obj->updateData($mongo_inputs);
			//~ echo '---result---<pre>';print_r($res);
		}
		else
		{
			$sqlBusTempData  = "SELECT contractid,categories,catIds,htmldump,slogan,categories_list,pages,bid_day_sel,bid_timing,threshold,autobid,catSelected,uId,mainattr,facility,companyName,avgAmt,percentage,comp_deduction_amt,thresholdform,thresholdType,original_catids,authorised_categories,bid_lead_num,bid_type,nationalcatIds,parentname,bid_led_num_year,thresholdPercnt,TotThresh,thresWeekSup,thresDailySup,thresMonthSup,bid_lead_num_sys,significance,htmldump_np,slogan_np,category_flow_info FROM tbl_business_temp_data WHERE contractid = '".$this->parentid."'";
			$resBusTempData = parent::execQuery($sqlBusTempData, $this->conn_temp);
			if($resBusTempData && parent::numRows($resBusTempData)>0){
				$row_bus_temp_data = parent::fetchData($resBusTempData);
				$tempdata['tbl_business_temp_data'] = $row_bus_temp_data;
			}
			$updateCats = "INSERT INTO tbl_business_temp_data SET contractid ='".$tempdata['tbl_business_temp_data']['contractid']."',
			categories = '".$tempdata['tbl_business_temp_data']['categories']."',
			catIds		= '".$tempdata['tbl_business_temp_data']['catIds']."',
			nationalcatIds = '".$tempdata['tbl_business_temp_data']['nationalcatIds']."',
			catSelected =    '".$tempdata['tbl_business_temp_data']['catSelected']."',
			original_catids = '".$tempdata['tbl_business_temp_data']['original_catids']."',
			htmldump = '".$tempdata['tbl_business_temp_data']['htmldump']."',
	        slogan = '".$tempdata['tbl_business_temp_data']['slogan']."'
	        ON DUPLICATE KEY UPDATE 
	        categories = '".$tempdata['tbl_business_temp_data']['categories']."',
			catIds		= '".$tempdata['tbl_business_temp_data']['catIds']."',
			nationalcatIds = '".$tempdata['tbl_business_temp_data']['nationalcatIds']."',
			catSelected =    '".$tempdata['tbl_business_temp_data']['catSelected']."',
			original_catids = '".$tempdata['tbl_business_temp_data']['original_catids']."',
			htmldump = '".$tempdata['tbl_business_temp_data']['htmldump']."',
	        slogan = '".$tempdata['tbl_business_temp_data']['slogan']."'";
	        $res = parent::execQuery($updateCats, $this->conn_idc);
		}
		return $res;
	}

	function dispositionInfo(){
			$resArr		=	array();
			$sel_alloc = "SELECT allocID,secondary_allocId  FROM mktgEmpMaster WHERE mktempcode='".$this->params['ucode']."'";
			$alloc_obj 	= parent::execQuery($sel_alloc, $this->conn_local);
			$alloc_arr = array();
			while($alloc_res = parent::fetchData($alloc_obj)){
				if($alloc_res['secondary_allocId'] == ''){
					$alloc_arr = array();
					array_push($alloc_arr,$alloc_res['allocID']);
				}else {
					$alloc_arr = explode(',',$alloc_res['secondary_allocId']);
					array_push($alloc_arr,$alloc_res['allocID']);
				}
			}
			$alloc_id ='';
			foreach($alloc_arr as $alloc_key => $alloc_val){
				$alloc_id .= "'".$alloc_val."',";
			}
			$alloc_str = rtrim($alloc_id,',');
			$disp_val = '';
			if($alloc_str!=''){
				$get_mappinfo = "select disposition_val from tbl_disposition_mapping where allocid in(".$alloc_str.")";
				$disp_obj 	= parent::execQuery($get_mappinfo, $this->conn_local);
				while($disp_res = parent::fetchData($disp_obj)){
					$disp_val .= "'".$disp_res['disposition_val']."',";
				}
				$disp_str = rtrim($disp_val,',');
			}
			$disp_arr = array();
			if($disp_val == '') {
				$get_disp="SELECT disposition_name,disposition_value,optgroup FROM tbl_disposition_info where display_flag='1' ORDER BY optgroup_priority_flag ";
			}else{
				$get_disp="SELECT disposition_name,disposition_value,optgroup FROM tbl_disposition_info where disposition_value in (".$disp_str.") AND display_flag='1' ORDER BY optgroup_priority_flag ";
			}
			$obj_disp=	parent::execQuery($get_disp, $this->conn_local);
			$i = 0;
			while($res_disp = parent::fetchData($obj_disp)) {
				$resArr[$res_disp['optgroup']][$i]['optgroup'] 			= $res_disp['optgroup'];
				$resArr[$res_disp['optgroup']][$i]['disposition_name'] 	= $res_disp['disposition_name'];
				$resArr[$res_disp['optgroup']][$i]['disposition_value'] = $res_disp['disposition_value'];
				$i++;
			}
		return $resArr;
	}
	
	public function mktgBarLoad(){
		$resArr 							= 	array();
		$resArr['topVerified']				=	$this->topVerifiedData();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_obj 						= new MongoClass();
			$mongo_inputs 					= array();
			$mongo_inputs['module']       	= 'TME';
			$mongo_inputs['parentid']       = $this->params['parentid'];
			$mongo_inputs['data_city']      = $this->params['data_city'];
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_generalinfo_shadow"=>"landline,mobile,companyname,pincode",
				"tbl_business_temp_data"=>"categories"
			));
			if(!empty($this->params['parentid'])){
				$val = $mongo_obj->getTableData($mongo_inputs);		
			}
		}else{
			$checksql 			= "SELECT a.categories,b.landline,b.mobile,b.companyname,b.pincode FROM tbl_business_temp_data AS a JOIN  tbl_companymaster_generalinfo_shadow AS b ON a.contractid = b.parentid WHERE contractid = '".$this->params['parentid']."'";
			$sqlobj 			= parent::execQuery($checksql, $this->conn_tme);
			$val 				= parent::fetchData($sqlobj);
		}
		$lan_arr 				= array();
		$mob_arr 				= array();
		$cat_arr 				= array();
		if(count($val) > 0){
			if($val['categories'] != null && $val['categories'] != '' ) {
				$cat_arr = explode('|P|',$val['categories']);
			}
			if(stristr($val['landline'],',')){
				$lan_arr = explode(',',$val['landline']);
			}else {
				$lan_arr['0'] = $val['landline'];
			}
			if(stristr($val['mobile'],',')){
				$mob_arr = explode(',',$val['mobile']);
			}else {
				$mob_arr['0'] = $val['mobile'];
			}
			$resArr['vc_pincode'] 	= $val['pincode'];
			$resArr['vc_compname'] 	= $val['companyname'];
		}
		$resArr['tbl_companymaster_generalinfo'] = $this->getGeninfoMainData(); // check count
		$resArr['dispositionInfo'] 				 = $this->dispositionInfo();
		$resArr['tbl_tmesearch'] 				 = $this->getTmeSearchData(); // check count also take data_source as ' rowCompsrch '
		return $resArr;
	}
	
	public function chkRatingCat(){
		$catsAll 	= 	$this->getTempInfo();
		$catstrVal	=	$catsAll['all_temp_catids'];
		$catstr 	= 	'';
		if(count($catstrVal) == 0){
			$result['errorCode']	=	1;
			$result['errorStatus']	=	'No Categories';
		}else{ 
			foreach($catstrVal as $k=>$v){
				$catstr .= "'".$v."',";
			}
			$catstr = rtrim($catstr,',');
			$chkCatRating	=	"SELECT * FROM d_jds.tbl_categorymaster_generalinfo WHERE promt_ratings_flag&16=16 AND category_name NOT LIKE '%(p)' AND catid IN(".$catstr.")";
			$con_chkCatRat	=	parent::execQuery($chkCatRating, $this->conn_local);
			$numRowsCat		=	parent::numRows($con_chkCatRat);
			if($numRowsCat ==	0){ 
				$result['errorCode']	=	0;
				$result['errorStatus']	=	'Rating Categories';
			}else{
				$result['errorCode']	=	1;
				$result['errorStatus']	=	'Contract contains non Rateable Categories';
			}
		}
        return $result;
	}

	public function updateTimerStatus(){
		$res_arr 	= array();
		//echo "<pre>";print_r($this->conn_tme);
		if($this->isconnected!=''){
			$insert1	=	"INSERT INTO tbl_timer_status SET
							 	empcode		=	'".$this->ucode."',
								isConnected	=	'".$this->isconnected."'
							ON DUPLICATE KEY UPDATE 
								isConnected	=	'".$this->isconnected."'";
			$res_upd_time 	=	parent::execQuery($insert1,$this->conn_tme);
		}
		else{
			$sql_timer 	=	"SELECT isConnected FROM tbl_timer_status WHERE empcode ='".$this->ucode."' ";
			$res_timer 	=	 parent::execQuery($sql_timer,$this->conn_tme);
			if(parent::numRows($res_timer)>0){
				$row_timer =	parent::fetchData($res_timer);
				if($row_timer['isConnected']==0){
					$upd_time		 =	"UPDATE tbl_timer_status SET isConnected=1 WHERE empcode = '".$this->ucode."'";
					$res_upd_time	 = parent::execQuery($upd_time,$this>conn_tme);
				}
			}
		}
		if($res_upd_time){
			$res_arr['success'] = 1;
		}
		else{
			$res_arr['success'] = 0;
		}
		return $res_arr;
	}
	
	function arrayProcess($resultArray)
	{
		if(count($resultArray)>0){
			foreach($resultArray AS $key=>$value){
				$resultArray[$key] = addslashes(stripslashes(trim($value)));
			}
		}
		return $resultArray;
	}
	function stringProcess($string){
		$string = trim($string);
		$string = addslashes(stripslashes($string));
		return $string;
	}
	function curlCall($curl_url,$data=array())
	{	
		#echo $curlurl.'?'.$data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($curl_url));
		
		if(count($data)>0)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		
		if(DEBUG_MODE)
		{			
			echo '<br><b>curl url --</b>'.$curl_url;
			echo '<br><b>response </b>'.$content;
			
		}
		
		curl_close($ch);
		return $content;
	}
	
	function curlCallNot($curl_url,$data=array()){	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($curl_url));
		if(count($data)>0){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		if(DEBUG_MODE){			
			echo '<br><b>curl url --</b>'.$curl_url;
			echo '<br><b>response </b>'.$content;
		}
		curl_close($ch);
		return $content;
	}
	
	function curlCall_extra($curl_url,$data)
	{	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		//echo "<pre>content:--4353:--";print_r($content);
		curl_close($ch);
		return $content;
	}
	
	private function sendResponse($msg,$code)
	{
		$die_msg_arr['error']['code'] = $code;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
