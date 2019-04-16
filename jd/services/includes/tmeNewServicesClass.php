<?php
class tmeNewServicesClass extends DB
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
		$this->mongo_tme 		= 1;
		$this->limitVal			= 50;
		$this->mongo_obj 		= new MongoClass();
		$this->companyClass_obj  = new companyClass();
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
		$this->categoryClass_obj = new categoryClass();
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
		if(trim($empcode)=='' && $this->params['action']	!=	'getcitylist' && $this->params['action']	!=	'getDispositionList'){
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
	
	//BroadCast Details start
	function getEmpMessageDetails(){				
		$queryCity = "SELECT * FROM mktgEmpMaster WHERE mktEmpCode='".$this->ucode."' AND Approval_flag=1";
		$resCity   =  parent::execQuery($queryCity, $this->conn_local);
		if($resCity && parent::numRows($resCity)>0){
			$fetchCty = parent::fetchData($resCity);
			$teamtype = 'ALL';
			if($fetchCty['allocId']!=null){
				 if($fetchCty['allocId']=='OTH'){
					 $teamtype = 'Others';
				 }
				  if($fetchCty['allocId']=='BD'){
					 $teamtype = 'Bounce';
				 }
				  if($fetchCty['allocId']=='HD'){
					 $teamtype = 'Hot Data';
				 }
				  if($fetchCty['allocId']=='O'){
					 $teamtype = 'Online';
				 }
				  if($fetchCty['allocId']=='RD'){
					 $teamtype = 'Retention';
				 }
				  if($fetchCty['allocId']=='BE'){
					 $teamtype = 'Revival';
				 }
				  if($fetchCty['allocId']=='S'){
					 $teamtype = 'Super';
				 }
				 if($fetchCty['allocId']=='SJ'){
					 $teamtype = 'Super Cats';
				 }
			}	
			$KNOWLEDGE_APICALL	= $this->urldetails['KNOWLEDGE_APICALL']."knowledge/fetchBroadCastData?title=&limit=200&page=1&emp_id=".$this->ucode."&emp_type=TME&team_type=$teamtype&tagged_city=".$fetchCty['city'];		
			$curlParams					= 	array();
			$curlParams['url']			= 	$url;
			$curlParams['formate'] 		=  	'basic';
			$res = json_decode($this->get_curl_data($KNOWLEDGE_APICALL),1);				
			if(empty($res)){
				$retArr['errorCode']    =   1;
				$retArr['errorMsg']     =   "Data Not Found";
				return $retArr;
			}
			$i=0;
			if($res['error']['code']==0) {
				foreach($res['result']['data'] as $key=>$index){
					$retArr['data'][$i]['title']= $index['title'];
					$retArr['data'][$i]['message']= $index['message'];
					if($index['mandatory']==1){
						if($index['read_flag']==0){
							$retArr['data'][$i]['flag']=1;
						}else if($index['read_flag']==1){
							$retArr['data'][$i]['flag']=0;
						}
					}else if($index['mandatory']==0){
						$retArr['data'][$i]['flag']=0;
					}
					$retArr['data'][$i]['mandatory']= $index['mandatory'];
					$retArr['data'][$i]['media_path']= $index['media_path'];
					if($index['media_show']==""){
						$retArr['data'][$i]['media_show']="nomedia";
					}
					else{
						$retArr['data'][$i]['media_show']= $index['media_show'];
					}
					if($index['media_type']==""){
						$retArr['data'][$i]['media_type']="nomedia";
					}
					else{
						$retArr['data'][$i]['media_type']= $index['media_type'];
					}
					if(isset($index['entry_date'])){
						$date=$index['entry_date'];
						$retArr['data'][$i]['msg_time']= date('d-m-Y | h:i A', strtotime($date));
					}
					if(is_array($index['_id'])) {
						$retArr['data'][$i]['media_id']= $index['_id']['$oid'];
					}
					else {
						$retArr['data'][$i]['media_id']= $index['_id'];
					}
					$retArr['data'][$i]['senderId']="justdial";
					$i++;
					
				}
				$retArr['total']		= $i;
				$retArr['errorCode']    =   0;
				$retArr['errorMsg']     =   "Data Found Successfully";
			}else {
				$retArr['errorCode']    =   1;
				$retArr['errorMsg']     =   "Data Not Found";
			}
		}else{
			$retArr['errorCode']    =   1;
			$retArr['errorMsg']     =   "Data Not Found";			
		}
		return $retArr;
	}
	//BroadCast Details END
	
	//CallBack DATA STARTS
	function getCallBackData(){
		$retArr		 = 	array();		
		$TIME_RANGE1 = 5;	//(TIME IN MINUTES)
		$TIME_RANGE2 = 15;		
		$st_time     = date('Y-m-d H:i:s');		
		$end_time1 	 = date('Y-m-d H:i:s', time()+($TIME_RANGE1*60));
		$end_time2   = date('Y-m-d H:i:s', time()+($TIME_RANGE2*60));
		$query		=	"SELECT contractCode,allocationType,actionTime,compname,allocationId,pop_flag FROM tblContractAllocation WHERE pop_flag=1 AND empCode = '".$this->ucode."' AND actionTime >='".$st_time."' AND actionTime <='".$end_time1."' AND allocationType != 317 ORDER BY actionTime";
		$result_query	=	parent::execQuery($query, $this->conn_local);
		$count			=	parent::numRows($result_query);
		if($count > 0) {			
			while($row	=	parent::fetchData($result_query)) {
				$retArr['data'][]	=	$row;
			}
		}		
		$query2		  =	"SELECT contractCode,allocationType,actionTime,compname,allocationId,pop_flag FROM tblContractAllocation WHERE pop_flag=0 AND empCode = '".$this->ucode."' AND actionTime >='".$st_time."' AND actionTime <='".$end_time2."' AND allocationType != 317 ORDER BY actionTime";
		$result_qry2  =	parent::execQuery($query2, $this->conn_local);
		$count2		  = parent::numRows($result_qry2);
		if($count2 > 0) {
			while($row2	=	parent::fetchData($result_qry2)) {
				$retArr['data'][]	=	$row2;
			}
		}		
		if($count > 0 || $count2 > 0) {
			$retArr['errorCode']	=	0;
			$retArr['errorMsg']		=	'Data returned successfully';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorMsg']		=	'Data Not Found!!';
		}
		return $retArr;
	}
	//CallBack DATA ENDS
	
	// Employee Info API Starts
	public function EmpInfo() {
		$retArr				=	array();
		$GetSSOInfo 		=	$this->getSSOInfo();
		if($GetSSOInfo['errorcode'] ==	0){
			$resTme					=	$GetSSOInfo['data'][0];
			$retArr['results']		=	$resTme;
			$retArr['results']['allocId']	=	$resTme['team_type'];
			if($resTme['status'] == 'ACTIVE')
				$retArr['results']['Approval_flag']	=	1;
			$retArr['results']['data_city']		=	$resTme['city'];
			$retArr['results']['city']			=	$resTme['city'];
			$retArr['results']['empParent']		=	$resTme['reporting_head_code'];
			$retArr['results']['mktEmpCode']	=	$resTme['empcode'];
			$retArr['results']['empName']		=	$resTme['empname'];
			$retArr['results']['mobile']		=	$resTme['mobile_num'];
			$retArr['results']['emailId']		=	$resTme['email_id'];
			if(strtolower($resTme['type_of_employee']) == 'tme' || strtolower($resTme['type_of_employee']) == ''){
				$retArr['results']['empType']                 		= 5;
			}else if(strtolower($resTme['type_of_employee']) == 'me'){
				$retArr['results']['empType']                 		= 3;
			}else if(strtolower($resTme['type_of_employee']) == 'jda'){
				$retArr['results']['empType']                 		= 13;
			}
			///////////////////////////////////////////////
			$getmktgmasterData	=	$this->getmktgEmpMaster();
			if($getmktgmasterData['errorCode'] == 0){ // make this 1 in future
				$retArr['results']['extn']					=	$getmktgmasterData['data']['extn'];
				$retArr['results']['secondary_allocID']		=	$getmktgmasterData['data']['secondary_allocID'];
				$retArr['results']['tmeClass']				=	$getmktgmasterData['data']['tmeClass'];
				$retArr['results']['level']					=	$getmktgmasterData['data']['level'];
				$retArr['results']['state']					=	$getmktgmasterData['data']['state'];
			}else{
				$retArr['results']['extn']					=	'';
				$retArr['results']['secondary_allocID']		=	'';
				$retArr['results']['tmeClass']				=	'';
				$retArr['results']['level']					=	'';
				$retArr['results']['state']					=	'';
			}
			//////////////////////////////////////////////
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
			$params 				= 	array();
			$params['url'] 			= 	$this->hrmodule.'/employee/fetch_employee_info/' . $this->ucode;
			$params['formate'] 		= 	'basic';
			$content_emp 			=   $this->curlCall($params);
			$retArr['hrInfo']		=	json_decode($content_emp,true);
			$retArr['rankingData']	=	$this->fetch_rank_details();
		}else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data found';
		}
		$retArr['remoteAddr']		=	$_SERVER['REMOTE_ADDR'];
		$start_date 				= 	date('Y-m-d').' 00:00:00';
		$sel_clientWaiting 			= "SELECT parentid as contractid,companyname FROM d_jds.tbl_walkin_client_details WHERE tmecode = '".$this->ucode."' AND (final_status = '' OR final_status IS NULL) AND allocated_date >= '".$start_date."' GROUP BY parentid";
		$sel_clientWaiting_Res 		= 	parent::execQuery($sel_clientWaiting, $this->conn_LOCAL);
		$sel_clientWaiting_numRows 	= 	parent::numRows($sel_clientWaiting_Res);
		if($sel_clientWaiting_numRows > 0){
			while($row = parent::fetchData($sel_clientWaiting_Res)){
				$retArr['client_waiting']['data'][] 	=	 $row;
			}
			$retArr['client_waiting']['flag'] 			= 	1;
		}else{
			$retArr['client_waiting']['flag'] 			= 	0;
		}
		return $retArr;
	}
	//End
	
		
	// Employee Rank Info API Starts
	public function fetch_rank_details() {
		$res		=	array();
		$query		=	"SELECT dc_today,dc_month,which_month,overall_rank,team_rank FROM tbl_employee_rank WHERE empcode = '".$this->ucode."' AND which_month = '".date('Y-m')."'";
		$con		=	parent::execQuery($query, $this->conn_local);
		$num		=	parent::numRows($con);
		if($num > 0) {
			$res['data']		=	parent::fetchData($con);
			$res['count']		=	$num;
		} else {
			$res['count']		=	0;
		}
		return $res;
	}
	// Employee Rank Info API Ends
	
	// Left Menu Links API Starts
	public function fetchMenuLinks() { 
		//~ $getLinkOrder	=	json_decode($this->fetchEmpLinkOrder($this->ucode),true);
		//~ $linkArr		=	array();
		//~ if($getLinkOrder['errorCode'] == 0) {
			//~ $linkOrder	=	$getLinkOrder['data'];
			//~ $linkArr['data']	=	explode(',',$linkOrder);
			//~ $linkArr['count']	=	count($linkArr['data']);
		//~ } else {
			//~ $linkArr['count']	=	0;
		//~ }
		//~ echo '<pre>';print_r($this->params);
		$sec_all = '';
		if($this->params['secondaryid'] != '' || $this->params['secondaryid'] != null) {
			$sec_arr = explode(',',$this->params['secondaryid']);
			foreach($sec_arr as $key => $val) {
					$sec_all .= "'".$val."',";
			}
			$allocids = "'".$this->params['allocid']."',".rtrim($sec_all,',');
		}else {
			$allocids = "'".$this->params['allocid']."'";
		}
		$sel 			=	"SELECT link_name FROM tbl_team_mapping WHERE allocid in (".$allocids.")";
		$alloc_obj 		=  	parent::execQuery($sel, $this->conn_local);
		$links 			=   "";
		while($alloc_res	=	parent::fetchData($alloc_obj)) {
				$links .= "'".$alloc_res['link_name']."',";
		}
		$res	=	array();
		if($links == '') {
			$query	=	"SELECT menu_id,menu_name,menu_link,display_menu,extraVals from tbl_menu_links WHERE display_menu>0";
		} else {
			$query	=	"SELECT menu_id,menu_name,menu_link,display_menu,extraVals from tbl_menu_links where menu_name in (".rtrim($links,',').") AND display_menu>0";
		}
		$con	=	parent::execQuery($query,$this->conn_local);		
		//~ echo $query;
		$num	=	parent::numRows($con);
		$retArr	=	array();
		$responseArr	=	array();
		$k=1;
		while($resData	=	parent::fetchData($con)) {
			$res['data'][$k]	=	$resData;
			if($resData['menu_name'] == 'DIY JDRR prospect DATA'){
				$JDRR_prospect_DATA_qr = "SELECT x1.*, x2.* FROM db_justdial_products.tbl_online_campaign x1, db_justdial_products.tbl_allocation_panindia_master x2 WHERE x1.docid = x2.docid AND allocated_flag=1 AND read_flag=0 AND empcode='".$this->ucode."'";
				$JDRR_prospect_DATA_qr_con  = parent::execQuery($JDRR_prospect_DATA_qr,$this->conn_idc);
				$JDRR_prospect_DATA_qr_num 	= parent::numRows($JDRR_prospect_DATA_qr_con);
				if($JDRR_prospect_DATA_qr_num > 0){
					$res['data'][$k]['count'] = $JDRR_prospect_DATA_qr_num;
				}else{
					$res['data'][$k]['count'] = '';
				}
			}
			if($resData['menu_id']	==	'47' || $resData['menu_id']	==	'48') {
				$privilegeQry	=	"SELECT * FROM tme_ecs_tracker_privilege WHERE tme_code='".$this->ucode."' AND tme_status = 1";
				$resPrivilege  	= 	parent::execQuery($privilegeQry,$this->conn_local);
				$numPrivilege 	= 	parent::numRows($resPrivilege);
				if($numPrivilege > 0){
					if($resData['menu_id']	==	'47') {
						$ecs_tracker_rpt_name = '../00_Payment_Rework/accounts/ecs_tracker_report.php?mode=2&me_tme=';
					} else {
						$ecs_tracker_rpt_name = '../00_Payment_Rework/accounts/si_tracker_report.php?mode=2&me_tme=';
					}
				}else{
					if($resData['menu_id']	==	'47') {
						$ecs_tracker_rpt_name = '../00_Payment_Rework/accounts/ecs_tracker_report_tme.php?mode=2&me_tme=';
					} else {
						$ecs_tracker_rpt_name = '../00_Payment_Rework/accounts/si_tracker_report_tme.php?mode=2&me_tme=';
					}
				}
				$res['data'][$k]['menu_link']	=	$ecs_tracker_rpt_name;
			}
			$k++;
		}
		//~ if($linkArr['count'] > 0) {
			//~ $l = 0;
			//~ $arrCheck	=	array();
			//~ foreach($linkArr['data'] as $speedVals) {
				//~ $retArr['data'][$l]	=	$res['data'][$speedVals];
				//~ $arrCheck[]	=	$res['data'][$linkArr['data'][$l]]['menu_id'];
				//~ $l++;
			//~ }
			//~ $maxArrKey	=	max(array_keys($retArr['data'])) + 1;
			//~ foreach($res['data'] as $allMenu) {
				//~ if(!in_array($allMenu['menu_id'],$arrCheck)) {
					//~ $retArr['data'][$maxArrKey]	=	$allMenu;
				//~ }
				//~ $maxArrKey++;
			//~ }
			//~ $i = 0;
			//~ foreach($retArr['data'] as $value) {
				//~ $responseArr['data'][$i]	=	$value;
				//~ $i++;
			//~ }
			//~ $responseArr['count']	=	$num;
		//~ } else {
			$i	=	0;
			foreach($res['data'] as $value) {
				$responseArr['data'][$i]	=	$value;
				$i++;
			}
			$responseArr['errorCode']	=	'0';
			$responseArr['errorStatus']	=	'Data Found';
		//~ }
		//~ array_multisort($responseArr['data'], SORT_ASC, $res['data']);
		return $responseArr;
	}

	
	
	public function fetchEmpLinkOrder($empcode) {
		$retArr	=	array();
		$selSortOrder	=	"SELECT sort_order,sort_order_report FROM tbl_speed_links WHERE emp_id = '".$empcode."'";
		$conSortOrder	=	parent::execQuery($selSortOrder,$this->conn_local);
		$numOrder		=	parent::numRows($conSortOrder);
		if($numOrder > 0) {
			$resLinks	=	parent::fetchData($conSortOrder);
			if(($resLinks['sort_order'] == '' || $resLinks['sort_order'] == null) && ($resLinks['sort_order_report'] == '' || $resLinks['sort_order_report'] == null)) {
				$retArr['errorCode']	=	'1';
				$retArr['errorStatus']	=	'Data Not Found';
			} else {
				if($resLinks['sort_order_report']	==	'' || $resLinks['sort_order_report'] == null) {
					$retArr['data']	=	$resLinks['sort_order'];
				} else if($resLinks['sort_order'] == '' || $resLinks['sort_order']	==	null){
					$retArr['data']	=	$resLinks['sort_order_report'];
				} else {
					$retArr['data']	=	$resLinks['sort_order'].','.$resLinks['sort_order_report'];
				}
				$retArr['errorCode']	=	'0';
				$retArr['errorStatus']	=	'Data Found';
			}
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	//End

	public function getEmpRowId() {		//dnt remove it
		$result	 	=	array();				
		$query	    = "SELECT rowId,mktEmpCode,mktEmpType FROM d_jds.mktgEmpMap WHERE mktEmpCode = '".$this->ucode."'";
		$res_query	= parent::execQuery($query, $this->conn_local);
		$count      = parent::numRows($res_query);
		if($res_query &&  $count>0 ){
			$result['data']	    =	parent::fetchData($res_query);
			$result['errorCode']	=	0;
			$result['errorStatus']	=	'Row Id Found';
		}else{
			$result['errorCode']	=	1;
			$result['errorStatus']	=	'No Rowid Found';
		}
		$result['count'] = $count;
		return $result;
	}
	
	public function storeemp(){		
		$name_arr = $this->EmpInfo($this->params['empcode']);		
		$empname  = $name_arr['results']['empname'];
		$sel ="insert into d_jds.tbl_employee_declaration(employee_name,employee_code,source,declared_on) values('".addslashes(stripslashes($empname))."','".$this->ucode."','TME',now()) ";		
		$res_query	= parent::execQuery($sel, $this->conn_local);
		$count      = parent::numRows($res_query);
		if($res_query) {
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data inserted';
			
		}else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not inserted';
		}
		return $retArr;		
	}
	
	//editlistingData start
	public function fetchEditListingData(){		
		$retArr= array();
		$get_data	    = "SELECT * FROM d_jds.tbl_companydetails_edit WHERE parentid='".$this->params['parentid']."' ORDER BY entry_date DESC LIMIT 1";
		$con_get_data	= parent::execQuery($get_data, $this->conn_tme);
		$num            = parent::numRows($con_get_data);
		if($num > 0) {
			$json_edited_data	=	parent::fetchData($con_get_data);
			$retArr['data']		=	json_decode($json_edited_data['edited_data']);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	//END
	
	public function fetchEditListingEntry(){
		$retArr						=	array();
		$get_data					=	"SELECT * FROM d_jds.tbl_correct_incorrect WHERE parentid='".$this->params['parentid']."' ORDER BY entry_date DESC LIMIT 1";
		$con_get_data				=	parent::execQuery($get_data,$this->conn_tme);	
		$num						=	parent::numRows($con_get_data);
		if($num > 0) {
			$retArr['num']			=	$num;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	public function mobAllocDetails(){				
		$res		=	array();
		$resArrDisp = array();		
		$getRowId	=	json_decode($this->getRowId($this->params['empcode']),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';
			$orderCond	=	' ORDER BY parent_group ASC';// group id
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'mask' :
							$whereCond	=	' AND mask=1 ';
						break;
						case 'freez' :
							$whereCond	=	' AND freez=1 ';
						break;
						case 'compnameLike' :
							$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$whereCond	=	'';
					//~ if($params['srchparam'] == 'exp_on') {
						//~ $whereCond	=	' AND paidstatus = 1 AND expired = 0 ';
					//~ }
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			
			$setParId	=	"";
			if(isset($this->params['parentid']) && $this->params['parentid']!='') {
				$setParId	=	str_replace(",","','",$this->params['parentid']);
				$whereCond	=	" AND parentId IN ('".$setParId."')";
				$limitFlag	=	"";
			}
			if($this->params['city']){
				$city	=	" AND data_city='".$this->params['city']."'";
			}else{
				$city	=	"";
			}
			if($this->params['srchparam'] != 'compnameLike'){
				$queryNum	=	"SELECT count(1) as countTot, count(distinct(parent_group)) as countGroup FROM tbl_tmesearch a WHERE tmeCode='".$getRowId['data']['rowId']."' AND parentId != '' ".$whereCond." ".$city."";
				$conNum		   = parent::execQuery($queryNum, $this->conn_local_slave);
				$numRowsNum    = parent::fetchData($conNum);
			}
			$query	=	"SELECT parentId as contractid, compname FROM tbl_tmesearch WHERE tmeCode='".$getRowId['data']['rowId']."' AND parentId != '' ".$whereCond." ".$city." GROUP BY parentId ".$orderCond."".$limitFlag;			
			$con	= parent::execQuery($query, $this->conn_local_slave);
			$num    = parent::numRows($con);
			$parentidStr	=	"";
			if($num > 0) {
				$docIdStr	=	'';
				$j =0;
				while($resData	=	parent::fetchData($con)) {
						//$getMobilenum	=	"SELECT mobile,landline FROM tbl_companymaster_generalinfo WHERE parentid='".$resData['contractid']."'";						
						//$conMob 	= parent::execQuery($getMobilenum, $this->conn_iro);
						$comp_params = array();
						$comp_params['data_city'] 	= $this->data_city;
						$comp_params['table'] 		= 'gen_info_id';
						$comp_params['module'] 		= $this->module;
						$comp_params['parentid'] 	= $resData['contractid'];
						$comp_params['action'] 		= 'fetchdata';
						$comp_params['page'] 		= 'tmeNewServicesClass';
						$comp_params['fields']		= 'mobile,landline';

						$comp_api_res		= 	array();
						$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);

						//$numMob     = parent::numRows($conMob);
						$numMob 	 =	count($comp_api_res['results']['data']);
						if($numMob	>	0 && $comp_api_res['errors']['code']=='0'){
							foreach($comp_api_res['results']['data'] as $pid =>$rowMob){
								if($rowMob['mobile']!= '' && $rowMob['mobile'] != null)
									$res['data'][$j]['mobile']     	=   $rowMob['mobile']; //explode(',',$rowMob['mobile']);	
								else
									$res['data'][$j]['mobile']      =   '';	
								if($rowMob['landline']!= '' && $rowMob['landline'] != null)
									$res['data'][$j]['landline']   	=   $rowMob['landline'];	//explode(',',$rowMob['landline']);	
								else
									$res['data'][$j]['landline']    =   '';	
							}
						}else{
							$res['data'][$j]['mobile']      =   '';	
							$res['data'][$j]['landline']    =   '';	
						}
					$res['data'][$j]['contractid']	=	$resData['contractid'];
					$res['data'][$j]['compname']	=	$resData['compname'];
					$j++;
				}
				$res['errorCode']	=	0;
				$res['errorStatus']	=	'Data found';
			} else {
				$res['errorCode']	=	1;
				$res['errorStatus']	=	'No Data Allocated';
			}
			$res['count']		=	$num;
			if($this->params['srchparam'] != 'compnameLike'){
				$res['counttot']	=	$numRowsNum['countTot'];
				$res['countGroup']	=	$numRowsNum['countGroup'];
			}
		} else {
			$res['errorCode']	=	1;
			$res['errorStatus']	=	'No Data Allocated';
		}
		return $res;
	}
	//END
	public function companyAutoSuggestAllocated(){		
		$getRowId	=	json_decode($this->getRowId($this->params['empcode']),true);
		$retArr		=	array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY actionTime DESC, parentCode DESC';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'company' :
						$whereCond	=	' AND compname LIKE "'.$this->params['srchData'].'%"';
					break;
					case 'today':
						$whereCond	=	" AND (actionTime>='".$cur_time." 00:00:00' AND actionTime<='".$cur_time." 23:59:59')";
					break;
				}
			} else {
				$whereCond	=	'';
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$getCompanies	=	"SELECT Distinct(compname),parentId as contractCode FROM tbl_tmesearch WHERE tmeCode='".$getRowId['data']['rowId']."' AND compname LIKE '".$this->params['srchData']."%' LIMIT 10";		
		$con 	  	    = parent::execQuery($getCompanies, $this->conn_local_slave);
		$numMob   	    = parent::numRows($con);						
		if($numMob >	0){
			$i =0;
			while($row	=	parent::fetchData($con)){
				$retArr['data'][$i]['compname']	=	$row['compname'];
				$retArr['data'][$i]['parid']	=	$row['contractCode']; $i++;
			}
			
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Search Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Search Data Not Found';
		}
		return $retArr;
	}
	//END
	
	public function getPenaltyDetails(){		
		$retArr		= array();
		$cond		=	'';
		$condtop	=	'';
		$limitFlag	=	'';
		$sno=1;
		$retArr['datatop']=array();
		if(isset($this->params['page'])) {
			$pageVal	=	20*($this->params['page']-1);
			$limitFlag	=	" LIMIT ".$pageVal.",20";
		}else {
			$limitFlag	=	" LIMIT 0,20";
		}
		
		if(isset($this->params['actionInfo']) && $this->params['actionInfo']=='report'){
			if(isset($this->params['date']) && $this->params['date']!=''){
				$search_form = date('Y-m-01',strtotime(date($this->params['year'].'-'.$this->params['date'].'-d')));
				$search_to =  date('Y-m-t',strtotime(date($this->params['year'].'-'.$this->params['date'].'-d')));
				$cond	.=" WHERE DATE(updatedOn) BETWEEN '".$search_form."' AND '".$search_to."' ";
				$condtop	.=" WHERE DATE(updatedOn) BETWEEN '".$search_form."' AND '".$search_to."' ";
			}
			else{
				$search_form = date('Y-m-01',strtotime(date('Y-m-d')));
				$search_to =  date('Y-m-t',strtotime(date('Y-m-d')));
				$cond	.=" WHERE DATE(updatedOn) BETWEEN '".$search_form."' AND '".$search_to."' ";
				$condtop	.=" WHERE DATE(updatedOn) BETWEEN '".$search_form."' AND '".$search_to."' ";
			}
			
			if(isset($this->params['city']) && $this->params['city']!='Pan India'){
				$cond.= " And city LIKE ('%".strtolower($this->params['city'])."%') ";
				$condtop.= " And city LIKE ('%".strtolower($this->params['city'])."%') ";
			}
			if(isset($this->params['empdata']) && $this->params['empdata']!=''){
				$cond.= " And (empcode LIKE ('%".$this->params['empdata']."%')  or empname LIKE ('%".$this->params['empdata']."%'))";
			}
			$select_penalty_details_top 	= "SELECT * FROM online_regis.tbl_employee_penalty_info".$condtop." ORDER BY updatedOn DESC,penalty DESC LIMIT 3";			
			$select_penalty_details_res_top 	     = parent::execQuery($select_penalty_details_top, $this->conn_idc);
			$select_penalty_details_res_top_count    = parent::numRows($select_penalty_details_res_top);
		
			if($select_penalty_details_res_top_count>0){
				while($row = parent::fetchData($select_penalty_details_res_top)){
					array_push($retArr['datatop'],$row);
				}
				$retArr['errorCodetop']=0;
				$retArr['errormsgtop']='success';
			}else{
				$retArr['errorCodetop']=1;
				$retArr['errormsgtop']='failed';
			}
			$select_penalty_details 	      = "SELECT * FROM online_regis.tbl_employee_penalty_info".$cond." ORDER BY updatedOn DESC ".$limitFlag;
			$select_penalty_details_count     = "SELECT * FROM online_regis.tbl_employee_penalty_info".$cond." ORDER BY updatedOn DESC ";			
			$select_penalty_details_res_count = parent::execQuery($select_penalty_details_count, $this->conn_idc);			
			$retArr['count']   				  = parent::numRows($select_penalty_details_res_count);
		}else{
			$select_penalty_details 		  = "SELECT * FROM online_regis.tbl_employee_penalty_info_monthly ORDER BY updatedOn DESC LIMIT 3";
		}		
		$select_penalty_details_res 	     = parent::execQuery($select_penalty_details, $this->conn_idc);
		$num_penalty_details   				 = parent::numRows($select_penalty_details_res);
		$i	=	0;
		if($num_penalty_details > 0){			
			while($row = parent::fetchData($select_penalty_details_res)){
				$retArr['data'][$i] = $row;
				$retArr['data'][$i]['SNO']=$sno++;
				$month=date("F",strtotime($row['updatedOn']));
				$year=date("Y",strtotime($row['updatedOn']));
				
				$i++;
			}
			$row = parent::fetchData($select_penalty_details_res);
			$retArr['month'] = $month;
			$retArr['year'] = $year;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	//END
	
	public function updateRdFlg(){
		$retArr	= array();
		if($this->params['parentid']!=''){
			if(!isset($this->params['parentid']) || (isset($this->params['parentid']) && $this->params['parentid'] == '')){
				$retArr['errorCode'] = 1;
				$retArr['errorStatus'] = "Please send contractid as parentid";
				return $retArr;
			}
			if(!isset($this->params['data_city']) || (isset($this->params['data_city']) && $this->params['data_city'] == '')){
				$retArr['errorCode'] = 1;
				$retArr['errorStatus'] = "Please send data_city as data_city";
				return $retArr;
			}
			if(!isset($this->params['contractEmpCode']) || (isset($this->params['contractEmpCode']) && $this->params['contractEmpCode'] == '')){
				$retArr['errorCode'] = 1;
				$retArr['errorStatus'] = "Please send contractEmpCode as contractEmpCode";
				return $retArr;
			}
			if(!isset($this->params['ucode']) || (isset($this->params['ucode']) && $this->params['ucode'] == '')){
				$retArr['errorCode'] = 1;
				$retArr['errorStatus'] = "Please send tmeCode as ucode";
				return $retArr;
			}
			if($this->params['contractEmpCode'] != $this->params['ucode']){
				$update_qur = "UPDATE `db_justdial_products`.tbl_online_campaign SET read_flag = 1 WHERE parentid = '".$this->params['parentid']."'";						
				$update_qur_con	= parent::execQuery($update_qur, $this->conn_idc);			
				if($update_qur_con){
					$retArr['errorCode']  = 0;
					$retArr['errorStatus']  = "Updated Success";
				}else{
					$retArr['errorCode']  = 1;
					$retArr['errorStatus']  = "Updated fail";
				}
			}else{
				$retArr['errorCode']  = 0;
				$retArr['errorStatus']  = "Updated not required";
			}			
		}else{
			$retArr['errorCode']  = 1;
			$retArr['errorStatus']  = "Parentid Is Missing!!";
		}		
		return $retArr;		
	}
	//END
	
	public function checkvccontract(){		
		$retArr		= 	array();		
		if($this->params['parentid']!=''){
			$checkvcquery = "select parentid,createdon from tbl_visitingcard_contracts where parentid='".$this->params['parentid']."' and transfer_flag = 0 ";  			
			$vcres_obj	= parent::execQuery($checkvcquery, $this->conn_local);
			$count      = parent::numRows($vcres_obj);		
			$cur_time 	= date("Y-m-d H:i:s");
			if($count > 0){
				while($vcval = parent::fetchData($vcres_obj)) {
					if($count > 0 && (strtotime($vcval['createdon']."+ 30 min") >=  strtotime($cur_time) )){
						$retArr['vc_flag'] = 1;
					}else {
						$retArr['vc_flag'] = 0;
					}	
				}
			}else {
				$retArr['vc_flag'] = 0;
			}
		}else{
			$retArr['vc_flag'] = 0;
		}
		return $retArr;
	}
	
	public function checkupdate(){		
		$retArr 	 = array();
		$check_query = "select parentid,action_flag from tbl_new_retention where parentid='".$this->params['parentid']."' and tmecode='".$this->ucode."'"; 		
		$sqlobj  	 = parent::execQuery($check_query, $this->conn_local);
		$res	 	 = parent::fetchData($sqlobj);
		$chkres		 =	0;
		if($res['action_flag'] == 16) {
			$update		=	"update tbl_new_retention set action_flag='18' where parentid='".$this->params['parentid']."'";
			$res_obj1	= 	parent::execQuery($update, $this->conn_local);
			if($res_obj1) {
				$insert		=	"insert into tbl_new_retention_log(parentid,tmecode,action_flag,insert_date) values('".$this->params['parentid']."','".$this->ucode."','18',now())";
				$res_obj2	= 	parent::execQuery($insert, $this->conn_local);								
				$retArr['errorCode']	=	1;
			}
		}else if($res['action_flag'] == 17) {
			$update="update tbl_new_retention set action_flag='19' where parentid='".$this->params['parentid']."'";			
			$res_obj3	= parent::execQuery($update, $this->conn_local);	
			if($res_obj3) {
				$insert="insert into tbl_new_retention_log(parentid,tmecode,action_flag,insert_date) values('".$this->params['parentid']."','".$this->ucode."','19',now())";
				$res_obj4	= parent::execQuery($insert, $this->conn_local);
				$retArr['errorCode']	=	1;
			}
		}else{
			$retArr['errorCode']	=	2;
		}
		return $retArr;
	} //END
	
	
	public function getHistory(){				
		$repeatArr  = array();			
		$data		= array();
		if(isset($this->params['parentid']) && $this->params['parentid']!=''){
			$repeatArr	=	json_decode($this->lead_or_retention_check($this->params['parentid']),1);								
			$sql = "(SELECT parentid,tmename,tmecode,insert_date,(CASE WHEN (update_date != '' AND update_date IS NOT NULL) THEN update_date
					WHEN (insert_date != '' AND insert_date IS NOT NULL) THEN insert_date
					ELSE update_date
					END)AS update_date,state,cscode,allocate_by_cs,request_source,action_flag,repeat_call,contract_flag,NULL AS approved_by,NULL AS ecs_reject_comment,
					NULL AS web_stop_reason,transfer_by_iro,ecs_skip,'LEAD' FROM d_jds.tbl_new_lead_log WHERE action_flag NOT IN ('7','8') AND 
					parentid = '".$this->params['parentid']."')
					UNION ALL
					(SELECT parentid,tmename,tmecode,insert_date,insert_date AS update_date,state,cscode,allocate_by_cs,request_source,action_flag,repeat_call,contract_flag,approved_by,
					ecs_reject_comment,web_stop_reason,transfer_by_iro,ecs_skip,'ECS' FROM d_jds.tbl_new_retention_log WHERE action_flag NOT IN ('7','8') AND
					 parentid = '".$this->params['parentid']."') 
					ORDER BY update_date DESC";				
			
			$res	= parent::execQuery($sql, $this->conn_local);	    			
			if(isset($res) && parent::numRows($res) > 0){
				$i=0;
				while($row = parent::fetchData($res)){
					$action_perform = ''; $emptext = ''; $action_perform1 = ''; $state = ''; $hist_str = '';
					
					$approvedtext = ($row['approved_by'] != '')?$row['approved_by']:'No Name Found';					
					if($row['approved_by'] != '' && $row['approved_by'] != null ){
						$sql1 = "SELECT empname FROM d_jds.mktgEmpMaster WHERE mktEmpCode = '".$row['approved_by']."'";						
						$res1 = parent::execQuery($sql1, $this->conn_local);
						if(isset($res1) && parent::numRows($res1) > 0){
							$row1 = parent::fetchData($res1);							
							if($row1['empname'] != ''){
								$approvedtext = 	ucwords(strtolower($row1['empname'])).'('.$row['approved_by'].')';
							}
						}	
					}
					if($row['tmename'] != ''){
						$emptext = 	ucwords(strtolower($row['tmename'])).'('.$row['tmecode'].')';
					}else{
						$emptext = 	$row['tmecode'];	
					}
					
					if($row['firstname'] != ''){
						$csemptext = 	ucwords(strtolower($row['firstname'])).' '.ucwords(strtolower($row['lastname'])).'('.$row['cscode'].')';
					}else{
						$csemptext = 	$row['cscode'];	
					}
					
					if($row['update_date'] != '' && $row['update_date'] != null){
						$row['insert_date'] = $row['update_date'];
					}else{
						$row['insert_date'] = $row['insert_date'];
					}
					if($row['request_source'] == 'web_dialer'){
						$sess_source = $row['request_source'];
						$select_stop_reason 	 = "SELECT web_stop_reason FROM d_jds.tbl_new_retention WHERE parentid = '".$this->params['parentid']."'";
						$select_stop_reason_Res  = parent::execQuery($select_stop_reason, $this->conn_local);						
						$select_stop_reason_Data = parent::fetchData($select_stop_reason_Res);
						$web_stop_reason		 = $select_stop_reason_Data['web_stop_reason'];
						$row['request_source'] = $row['request_source'].' - Reason : '.$web_stop_reason;
					}
					
					if($row['transfer_by_cs'] == '1'){
						$action_perform1	= 'Call allocated by CS-'.$csemptext.' to TME-'.$emptext.' on '.$row['insert_date'].' Source : '.$row['request_source'];
					}else if($row['allocate_by_cs'] == '1'){
						if (strpos($csemptext,'JDA') !== false) {
							$action_perform1='Automated call allocation to TME - '.$emptext.' on '.$row['insert_date'].' because '.$csemptext.' could not retain the contract';
						}else if(strpos($emptext,'JDA') !== false){
								$action_perform1	= 'Call allocated by CS-'.$csemptext.' to '.$emptext.' on '.$row['insert_date'].' Source : '.$row['request_source'];
						}else{
							$action_perform1	= 'Call allocated by CS-'.$csemptext.' to TME-'.$emptext.' on '.$row['insert_date'].' Source : '.$row['request_source'];
						}
					}else if($row['request_source'] == 'web'){
						$action_perform1	= 'Stop request from web,allocated to TME-'.$emptext.' on '.$row['insert_date'];
					}else if($row['request_source'] == 'jda'){
						$action_perform1	= 'Stop request from jda,allocated to TME-'.$emptext.' on '.$row['insert_date'];
					}
					
					if($row['action_flag'] == '4'){
						$action_perform	= 'TME status - Follow Up -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '31'){
						$action_perform	= 'LEAD Action - OPEN Status by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '32'){
						$action_perform	= 'LEAD Action - CLOSED Status by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '33'){
						$action_perform	= 'LEAD Action - FOLLOW-UP Status by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '34'){
						$action_perform	= 'LEAD Action - CALL-BACK Status by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '0'){
						if($sess_source == 'web_dialer'){
							if($row['tmecode'] != '' && $row['tmecode'] != null && $row['tmecode'] != 'null'){
								$action_perform1	= 'Contract was allocated to '.$row['tmename'].'('.$row['tmecode'].'). Source :'.$row['request_source'];
							}else{
								$action_perform1	= 'Stop request from Web-Dialer on '.$row['insert_date'].' Reason : '.$row['web_stop_reason'];
							}
						}else if($row['allocate_by_cs'] == '1'){
							$action_perform	= ''.$row['LEAD'].' Contract Allocated to '.$emptext.' on '.$row['insert_date'].' by '.$row['cscode'].' - CS';
						}else if($row['transfer_by_iro'] == '1'){
							$action_perform	= ''.$row['LEAD'].' Contract Allocated to '.$emptext.' on '.$row['insert_date'].' by '.$row['request_source'].'- IRO';
						}else{
							$action_perform	= ''.$row['LEAD'].' Contract Allocated to '.$emptext.' on '.$row['insert_date'].' by '.$row['request_source'];
						} 
						
						/*if($row['repeat_call'] == '1' || $row['repeat_call'] == '3'){
							$action_perform	= 'ECS Remainder Sent by '.$emptext.' to '.$repeatArr['tmename'].'('.$repeatArr['tmecode'].') on '.$row['insert_date'];
						}else if($row['repeat_call'] == '4'){
							$action_perform	= 'LEAD Remainder Sent by '.$emptext.' to '.$repeatArr['tmename'].'('.$repeatArr['tmecode'].') on '.$row['insert_date'];
						}*/
						
						if($row['repeat_call'] == '1' || $row['repeat_call'] == '2' || $row['repeat_call'] == '3' || $row['repeat_call'] == '4'){
							if($row['LEAD'] == "LEAD"){
								$action_perform	= 'LEAD Remainder Sent by '.$emptext.' to '.$repeatArr['tmename'].'('.$repeatArr['tmecode'].') on '.$row['insert_date'];
							}else if($row['LEAD'] == "ECS"){
								$action_perform	= 'ECS Remainder Sent by '.$emptext.' to '.$repeatArr['tmename'].'('.$repeatArr['tmecode'].') on '.$row['insert_date'];
							}
						}
					}else if($row['action_flag'] == '0' && $row['state'] == '3'){
						$action_perform	= 'Contract has moved to HOD Module on '.$row['insert_date'];
					}else if($row['action_flag'] == '21'){
						$action_perform	= 'TME status - Ringing -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '22'){
						$action_perform	= $row['LEAD'].' Contract was Re-Allocated by '.$row['cscode'].' on '.$row['insert_date'].' , Source : '.$row['request_source'];
					}else if($row['action_flag'] == '23'){
						$action_perform	= 'TME status - Not Contactable-ECS Continued'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '5'){
						if(strpos($emptext,'JDA') !== false){
							$action_perform	= 'JDA Status - Retain -'.' by '.$emptext.' on '.$row['insert_date'];
						}else{
							if($row['mename'] == '' || $row['mename'] == null) {
								$actionflag = '5';
								$action_perform	= 'TME Status - Retain -'.' by '.$emptext.' on '.$row['insert_date'];
							}
						}
					}else if($row['action_flag'] == '99'){
						$action_perform	= 'Repeat Call received by CS-'.$csemptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '100'){
						$action_perform	= 'Repeat call regarding ECS Stop reallocated to tme - '.$emptext.' on '.$row['insert_date']." by ".$row['cscode'];
					}else if($row['action_flag'] == '6'){
						$action_perform	= 'TME Status - Pending-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '9'){
						$action_perform	= 'TME Status - Stop ECS -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '1'){
						$action_perform	= 'TME Status - Open Action in Lead -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '2'){
						$action_perform	= 'TME Status - Close Action in Lead -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '3'){
						$action_perform	= 'TME Status - Follow-Up Action in Lead -'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '10'){						
						if($sess_source == 'web_dialer'){
							$action_perform	= 'ECS Stopped Request thru Website directly stopped by TME';
						}else{
						$action_perform	= 'ECS Stop request is approved'.' by '.$approvedtext.' on '.$row['insert_date'];
						}
					}else if($row['action_flag'] == '12'){
						$action_perform	= 'ECS stop request is rejected'.' by '.$approvedtext.' on '.$row['insert_date'].',Rejected Reason: '.$row['ecs_reject_comment'];
					}else if($row['action_flag'] == '18'){
						$action_perform	= 'TME Status - Upgraded-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '14'){
						$action_perform	= 'ECS Stop request is approved'.' by script on '.$row['insert_date'];
					}else if($row['action_flag'] == '19'){
						$action_perform	= 'TME Status - Degraded-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['reactivate_flag'] == '1'){
						$action_perform	= 'TME Status - Request for Reactivation is approved -'.' by '.$row['reactivated_by'].' on '.$row['reactivated_on'];
					}else if($row['reactivate_flag'] == '2'){
						$action_perform	= 'TME Status - Request for Reactivation is rejected -'.' by '.$row['reactivated_by'].' on '.$row['reactivated_on'];
					}else if($row['action_flag'] == '15'){
						$action_perform	= 'TME Status - Request for Reactivation-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '99'){
						$action_perform	= 'Repeat Call received by CS-'.$csemptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '24'){
						$action_perform	= 'Retained - ecs is skipped for the period '.$row['ecs_skip'].' on '.$row['insert_date'].' by '.$row['tmename'].'-'.$row['tmecode'];
					}else if($row['action_flag'] == '100'){
						$action_perform	= 'Repeat call regarding ECS Stop reallocated to tme - '.$emptext.' on '.$row['insert_date'];
					}else if($row['allocate_by_cs'] == '2'){
						$action_perform ='This Contract was assigned to tme - '.$emptext.' by - '.$csemptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '25'){
						$action_perform	= 'TME Status - Ignore Request-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '26'){
						$action_perform	= 'TME Status - Invalid Data-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '27'){
						$action_perform	= 'TME Status - Ecs Clarification Call-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '35'){
						$action_perform	= 'TME Status - Requested For Business CloseDown Validation-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '36'){
						$action_perform	= 'TME Status - Business Active - Reply From DB Team-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '37'){
						$action_perform	= 'TME Status - Business CloseDown Confirmed - Reply From DB Team-'.' by '.$emptext.' on '.$row['insert_date'];
					}else if($row['action_flag'] == '38'){
						$action_perform	= 'TME Status - Data Sent to Web Suppert Team'.' by '.$emptext.' on '.$row['insert_date'];
					}else{
						$action_perform = '';
					}
					if($row['state'] == '1'){
						$state ="This Contract is with First Level TME";
					}
					
					if($state != ''){
						$data['history'][$i]['display_text'] = $state;	
						$i++;
					}
					
					if($action_perform1 != ''){
						$data['history'][$i]['display_text'] = $action_perform1;
						$i++;
					}else if($action_perform != ''){
						$data['history'][$i]['display_text'] = $action_perform;	
						$i++;
					}
				}	
			}else{
					$data['history'] = 0;	
			}
		}else{
			$data['history'] = 0;	
		}
		return $data;		
	} //END
	
	public function insertmename(){		
		$resArr = array();				
		if(isset($this->params['parentid']) && $this->params['parentid']!=''){
			$inset   = "update tbl_new_retention set mename='".addslashes(stripcslashes($this->params['empname']))."',mecode = '".$this->params['final_mecode']."' where parentid='".$this->params['parentid']."'"; 
			$res_obj	 = parent::execQuery($inset, $this->conn_local);
			if($res_obj) {
				$insert_log ="insert into tbl_new_retention_log(parentid,insert_date,action_flag,mename,tmecode,mecode) values('".$this->params['parentid']."',now(),'5','".$this->params['empname']."','".$this->params['tmecode']."','".$this->params['final_mecode']."')";				
				$log_obj	= parent::execQuery($insert_log, $this->conn_local);
				$resArr['errorCode']	=	0;
				$resArr['errorStatus']	=	'Data inserted';
			}else {
				$resArr['errorCode']	=	1;
				$resArr['errorStatus']	=	'Data Not inserted';
			}
		}else{
			$resArr['errorCode']	=	1;
			$resArr['errorStatus']	=	'Parentid Is Missing!!';
		}
		return $resArr;
	} //END
	
	public function fetchmelist(){
		$resArr = array();				
		$query 		="select mktempcode,empname from mktgEmpMaster where emptype=3 AND approval_flag=1 and empname like '".$this->params['term']."%'ORDER BY empname"; 		
		$res_obj	= parent::execQuery($query, $this->conn_local);
		$count 		= parent::numRows($res_obj);
		if($count > 0) {
			while($res = parent::fetchData($res_obj)) {
				$resArr['data'][] = $res;
			}			
			$resArr['errorCode']	=	0;
			$resArr['errorStatus']	=	'Data found';
		}else{
			$resArr['data'] = '';
			$resArr['errorCode']	=	1;
			$resArr['errorStatus']	=	'Data Not found';
		}
		return $resArr;
	} //END
	
	public function fetchContractinventoryMorethanFifty(){		
		$retArr		= array();
		$whereCond	=	'';
		$orderCond	=	'';
		$groupCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by group_id";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentId IN ('".$setParId."')";
			$limitFlag	=	"";
		}
			
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$orderCond="";
		$groupCond="";
		$whereCond="";
		
		$quinvenData     = "SELECT * FROM  db_finance.tbl_partial_inventory_consolidated WHERE parentid='".$setParId."'";
		$quinvenData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;		
		$coninvenData	 = parent::execQuery($quinvenData, $this->conn_fin);
		$numPage	     =	parent::numRows($coninvenData);
		
		$quinvenData_cnt     =  "SELECT DISTINCT(pincode) as Pincode FROM  db_finance.tbl_partial_inventory_consolidated WHERE parentid='".$setParId."'";		
		$coninvenData_cnt	 = parent::execQuery($quinvenData_cnt, $this->conn_fin);
		$numPage_cnt		 =	parent::numRows($coninvenData_cnt);
		$pin_arr			 =	array();
		if($numPage_cnt	>	0){
			while($row_cnt	= parent::fetchData($coninvenData_cnt)){
				$pin_arr[]	=	$row_cnt['Pincode'];
			}	
		}
		
		$cat_pincode	=	array();
		if($numPage > 0) {
			while($rowParentid = parent::fetchData($coninvenData)){
				$retArr['data'][$rowParentid['Parentid']][companyname] 				= 	$rowParentid['Companyname'];
				$retArr['data'][$rowParentid['Parentid']][Category_Name][] 			= 	$rowParentid['Category_Name'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][cat_name] 		= 	$rowParentid['Category_Name'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][$rowParentid['Pincode']][poistionflag] 	= $rowParentid['Position_Flag'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][$rowParentid['Pincode']][Inventory] 		= $rowParentid['Inventory'];
				array_push($cat_pincode,$rowParentid['Pincode']);
			}			
			$cat_pincode	=	array_unique($cat_pincode);
			$retArr['pincodes_val']	=	$cat_pincode;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum;
		
		return $retArr;
	} //END
	
	public function fetchContractInventory(){				
		$retArr		= array();
		$whereCond	=	'';
		$orderCond	=	'';
		$groupCond	='';
		if(isset($params['srchparam']) && $params['srchparam'] != null && $params['srchparam'] != 'all') {
			if($params['srchwhich'] == 'where') {
				switch($params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$params['srchwhich']);
				$groupCond="group by group_id";
				$orderCond	=	' ORDER BY '.$params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($params['pageShow'])) {
			$pageVal	=	$this->limitVal*$params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentId IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		$orderCond="";
		$groupCond="";
		$whereCond="";
		$quinvenData     = "SELECT * FROM  db_finance.tbl_partial_inventory_consolidated WHERE parentid='".$setParId."'";
		$quinvenData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;		
		$coninvenData	 = parent::execQuery($quinvenData, $this->conn_fin);
		$numPage	     = parent::numRows($coninvenData);
		
		$quinvenData_cnt     =  "SELECT DISTINCT(pincode) as Pincode FROM  db_finance.tbl_partial_inventory_consolidated WHERE parentid='".$setParId."'";
		$coninvenData_cnt 	 =	parent::execQuery($quinvenData_cnt, $this->conn_fin);
		$numPage_cnt		 =	parent::numRows($coninvenData_cnt);
		$pin_arr			 =	array();
		if($numPage_cnt	>	0){
			while($row_cnt	= parent::fetchData($coninvenData_cnt)){
				$pin_arr[]	=	$row_cnt['Pincode'];
			}	
		}
		$cat_pincode	=	array();
		if($numPage > 0) {
			while($rowParentid = parent::fetchData($coninvenData)){
				$retArr['data'][$rowParentid['Parentid']][companyname] 				= 	$rowParentid['Companyname'];
				$retArr['data'][$rowParentid['Parentid']][Category_Name][] 			= 	$rowParentid['Category_Name'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][cat_name] 		= 	$rowParentid['Category_Name'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][$rowParentid['Pincode']][poistionflag] 	= $rowParentid['Position_Flag'];
				$retArr['data'][$rowParentid['Parentid']][Category][$rowParentid['Catid']][$rowParentid['Pincode']][Inventory] 		= $rowParentid['Inventory'];
				array_push($cat_pincode,$rowParentid['Pincode']);
			}			
			$cat_pincode	=	array_unique($cat_pincode);
			$retArr['pincodes_val']	=	$cat_pincode;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum;
		return $retArr;
	} //END
	
	public function SendRemainderEcsLead(){
		$retArr= array();
		$select_ecs_entry    = "SELECT * FROM d_jds.tbl_new_retention WHERE parentid = '".$this->params['parentid']."'";
		$conn_ecs_entry		 = parent::execQuery($select_ecs_entry, $this->conn_local);
		$numRows_ecs_entry	 = parent::numRows($conn_ecs_entry);		
		$Data_ecs_entry		 = parent::fetchData($conn_ecs_entry);
		if($Data_ecs_entry['update_date'] == '' || $Data_ecs_entry['update_date'] == null){
			$ecs_time = $Data_ecs_entry['allocate_date'];
		}else{
			$ecs_time = $Data_ecs_entry['update_date'];
		}
		$select_lead_entry    = "SELECT * FROM d_jds.tbl_new_lead WHERE parentid = '".$this->params['parentid']."'";
		$select_lead_entry	  = parent::execQuery($select_lead_entry, $this->conn_local);
		$conn_lead_entry	  = parent::numRows($conn_lead_entry);		
		$Data_lead_entry	  = parent::fetchData($conn_lead_entry);
		if($Data_lead_entry['update_date'] == '' || $Data_lead_entry['update_date'] == null){
			$lead_time = $Data_lead_entry['allocate_date'];
		}else{
			$lead_time = $Data_lead_entry['update_date'];
		}		
		if($numRows_ecs_entry > 0 && $numRows_lead_entry > 0){
			$ecs_date=new DateTime($ecs_time);
			$ecs_date->getTimestamp();
			$lead_date = new DateTime($lead_time);
			$lead_date->getTimestamp(); 
			if($ecs_date > $lead_date){
				$lead_flag = 1;
			}else{
				$lead_flag = 0;
			}
		}else{
			if($numRows_ecs_entry > 0){
				$lead_flag = 1;
			}else{
				$lead_flag = 0;
			}
		}
		if($lead_flag == 0){ // update lead table			
			$upd_repeatCount 				= "UPDATE d_jds.tbl_new_lead SET  repeat_call = '4', repeatcall_taggedon=NOW() WHERE parentid ='".$this->params['parentid']."'";
			$conn_upd_repeatCount_lead	    = parent::execQuery($upd_repeatCount, $this->conn_local);														
			$upd_repeatCount_log 			= "INSERT INTO d_jds.tbl_new_lead_log SET parentid		=	'".$this->params['parentid']."',
														tmecode					=	'".$this->ucode."',
														tmename					=	'".$this->params['uname']."',
														companyname				=	'".$this->params['companyname']."',
														repeat_call = '4',
														update_date				=	NOW(),
														data_city				= '".$this->params['data_city']."',
														repeatcall_taggedon=NOW(),
														ip						= '".$this->params['ip']."'";			
			$conn_upd_repeatCount_log	  = parent::execQuery($upd_repeatCount_log, $this->conn_local);			
			if($conn_upd_repeatCount_lead){
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Updated Successfully';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Updation Failed';
			}
		}else if($lead_flag == 1){  // update ecs table
			$upd_repeatCount 				= "UPDATE d_jds.tbl_new_retention SET  repeat_call = '3', repeatcall_taggedon=NOW() WHERE parentid ='".$this->params['parentid']."'";
			$conn_upd_repeatCount_ecs	    = parent::execQuery($upd_repeatCount, $this->conn_local);														
			$upd_repeatCount_log 			= "INSERT INTO d_jds.tbl_new_retention_log SET parentid		=	'".$this->params['parentid']."',
														tmecode					=	'".$this->ucode."',
														tmename					=	'".$this->params['empname']."',
														companyname				=	'".$this->params['companyname']."',
														repeat_call 			= '3',
														insert_date				=	NOW(),
														data_city				= '".$this->params['data_city']."',
														ip						= '".$this->params['ip']."'";
			$conn_upd_repeatCount_log	    = parent::execQuery($upd_repeatCount_log, $this->conn_local); 
			
			if($conn_upd_repeatCount_ecs == 1){
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Updated Successfully';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Updation Failed';
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Updation Failed';
		}		
		$retArr['errorCode']	=	0;
		$retArr['errorStatus']	=	'Updated Successfully';	
		return $retArr;
	} //END
	
	public function FetchEcsDetailsForm(){		
		$retArr= array();
		if(isset($this->params['parentid']) && $this->params['parentid']!=''){
			$select_ecs_details = "SELECT * FROM tme_jds.tbl_ecs_dealclose_pending WHERE detail_cname != '' AND parentid = '".$this->params['parentid']."' AND EmpCode = '".$this->params['empcode']."'";			
			$select_ecs_details_res	= parent::execQuery($select_ecs_details, $this->conn_local);
			$num_ecs_details	    = parent::numRows($select_ecs_details_res);			
			if($num_ecs_details > 0){
				while($row = parent::fetchData($select_ecs_details_res)){
					$retArr['data'][] = $row;
				}
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Parentid Is Missing!!';
		}
		return $retArr;
	} //END
	
	public function checkvccondition(){				
		$checksql = "SELECT a.categories,b.landline,b.mobile,b.companyname,b.pincode FROM tbl_business_temp_data AS a JOIN  tbl_companymaster_generalinfo_shadow AS b ON a.contractid = b.parentid WHERE contractid = '".$this->params['parentid']."'";		
		$sqlobj	= parent::execQuery($checksql, $this->conn_tme);
		$lan_arr = array();
		$mob_arr = array();
		$cat_arr = array();
		while($val = parent::fetchData($sqlobj)) {
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
			$vc_pincode = $val['pincode'];
			$vc_compname = $val['companyname'];
		}
		$retArr['category_count'] = count($cat_arr);		
		if($lan_arr['0'] != ''  || $lan_arr['0'] != null) {
			$retArr['land_count'] = count($lan_arr);
		}else {
			$retArr['land_count'] = 0;
		}
		if($mob_arr['0'] != ''  || $mob_arr['0'] != null) {
			$retArr['mob_count'] = count($mob_arr);
		}else {
			$retArr['mob_count'] = 0;
		}
		$retArr['pincode'] = $vc_pincode;
		$retArr['compname'] = $vc_compname;
		return $retArr;
	}
	
	public function updatestopflag(){
		$result	=	array();
		if($this->params['stop_flag'] == 1) {
			$action_flag = 9;
			$query	=	"update tbl_new_retention set action_flag='".$action_flag."',stop_request_datetime=now(),ecs_stop_flag ='1' where parentid = '".$this->params['parentid']."' ";
		}else {
			$action_flag = $this->params['stop_flag'];
			$query			=	"update tbl_new_retention set action_flag='".$action_flag ."' where parentid = '".$this->params['parentid']."' ";
		}		
		$res_query	= parent::execQuery($query, $this->conn_local);		
		if($res_query){
		 	$insert =  "INSERT INTO tbl_new_retention_log
						SET
						parentid = '".$this->params['parentid']."',
						tmecode = '".$this->ucode."',
						tmename = '".$this->params['tmename']."',
						insert_date = now(),
						companyname = '".addslashes(stripslashes($this->params['companyname']))."',
						action_flag = '".$action_flag."'";
			$con  	= 	parent::execQuery($insert, $this->conn_local);		
			$result['errorCode']	=	0;
			$result['errorstatus']	=	'Data Updated';
		}else {
			$result['errorCode']	=	1;
			$result['errorstatus']	=	'Data Not Updated';
		}
		return $result;
	} 	//END
	
	public function getDataCountTME(){	
		$retArr		= 	array();		
		if($this->params['decisionParam'] == '1') {
			$getRowId		=	json_decode($this->getRowId(),true);
			$allocContracts	=	"SELECT count(*) as countVal,a.allocationType as allocationType FROM tblContractAllocation a JOIN tbl_tmesearch b ON a.contractCode = b.parentid WHERE (a.empcode='".$this->ucode."' or a.parentCode='".$this->ucode."') AND b.tmeCode='".$getRowId['data']['rowId']."' AND a.allocationType IN ('22','6','21','25','207','9','21','24') GROUP BY a.allocationType";
		} else {
			$allocContracts	=	"SELECT count(1) as countVal,allocationType FROM tblContractAllocation WHERE (empcode='".$this->ucode."' or parentCode='".$this->ucode."') AND allocationType IN ('22','6','21','25','207','9','21','24') GROUP BY allocationType";
		}
		$conn		  = parent::execQuery($allocContracts, $this->conn_local_slave);	
		$numRows      = parent::numRows($conn);		
		if($numRows	>	0) {
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Returned Successfully';
			while($resData	=	parent::fetchData($conn)) {
				$retArr['data'][]	=	$resData;
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Found';
		}
		return $retArr;
	} //END
	
	public function deleteProspectData(){		
		$retArr = array();
		if(isset($this->params['parentid']) && $this->params['parentid']!=''){
			$parIdStrRep	= str_replace(",","','",$this->params['parentid']);		
			$query			= "DELETE FROM allocation.tbl_prospectlist where parentid IN ('".$parIdStrRep."')";		
			$conIns			=  parent::execQuery($query, $this->conn_local);
			if ($conIns) {
			    $errorMsg = "data Deleted successfully";
			    $errorCode = "0";
			    $retArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
			} else {
			    $errorMsg = "data deletion fail";
			    $errorCode = "1";
			    $retArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
			}
		}
		return $retArr;
	} //END
	
	public function getRowId() {		
		$result	 	=	array();				
		$query	    = "SELECT rowId,mktEmpCode,mktEmpType FROM d_jds.mktgEmpMap WHERE mktEmpCode = '".$this->ucode."'";
		$res_query	= parent::execQuery($query, $this->conn_local);
		$count      = parent::numRows($res_query);
		if($res_query &&  $count>0 ){
			$result['data']	    =	parent::fetchData($res_query);
			$result['errorCode']	=	0;
			$result['errorStatus']	=	'Row Id Found';
		}else{
			$result['errorCode']	=	1;
			$result['errorStatus']	=	'No Rowid Found';
		}
		$result['count'] = $count;
		return json_encode($result);
	}

	
	public function getmktgEmpMaster() {		
		$result	 	=	array();				
		$query	    =  "Select mktEmpCode,empName,empType,tmeClass,empParent,phoneNo,extn,mobile,emailId,state,city,Approval_flag,allocId,secondary_allocID,level,data_city,dnc_type,allocid FROM mktgEmpMaster WHERE mktempcode = '".$this->ucode."'";
		$res_query	=   parent::execQuery($query, $this->conn_local);
		$count      =   parent::numRows($res_query);
		if($res_query &&  $count>0 ){
			$result['data']	    =	parent::fetchData($res_query);
			$result['errorCode']	=	0;
			$result['errorStatus']	=	'Data Found';
		}else{
			$result['errorCode']	=	1;
			$result['errorStatus']	=	'No Data Found';
		}
		$result['count'] = $count;
		return $result;
	}
	
	//GetHotData API START
	
	//	only for remote 
	
	//  online - join and give three option in in the source 
	
	//	for others do not join the table 
	
	function getHotData(){
		$getRowId	=	json_decode($this->getRowId(),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';
			$orderCond	=	' ORDER BY a.datasource_date DESC';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($paramsGET['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND a.compname LIKE "%'.$this->params['srchData'].'%" ';
							$orderCond	=	' ORDER BY a.compname DESC';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);					
					$orderCond  =       ' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					//~ if($this->params['srchparam'] =='compnameLike'){ 
						//~ $orderCond	=	' ORDER BY a.compname '.$expOrder[1];
					//~ }else{
						//~ $orderCond	=	' ORDER BY "'.$this->params['srchparam'].'" '.$expOrder[1];
					//~ }
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			
			$setParId	=	"";
			if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
				$setParId	=	str_replace(",","','",$this->params['parentid']);
				$whereCond	=	" AND a.contractid IN ('".$setParId."')";
				$limitFlag	=	"";
			}
			$GetSSOInfo 			=	$this->getSSOInfo();
			if($GetSSOInfo['errorcode'] ==	0){
				$allocId		=	$GetSSOInfo['data'][0]['team_type'];
			}
			$optimise_queryCond	=	'';
			$optimise_queryComp	=	'';
			$join_col ='';
			
			if($this->params['srchparam'] != 'compnameLike'){
				if($this->city_indicator == 'remote_city'){
					if($allocId	!='' && strtolower($allocId) == 'o'){
						$optimise_queryComp	.=	"left join db_iro.appointment as d on a.parentid=d.parentid  WHERE a.tmecode = '".$getRowId['data']['rowId']."' and a.data_source IN('IRO DEFERRED AP','IRO TRANS AP','IRO ALREADY TAK','IRO DEFERRED SA')  ".$whereCond;
					}else{
						$optimise_queryComp	.=	"WHERE a.tmecode = '".$getRowId['data']['rowId']."' and a.data_source IN('joinfree','adprogrograms','IRO DEFERRED AP','IRO TRANS AP','Self Edit Reque','IRO ALREADY TAK','Self Edit UP','Self Edit NP','Self Edit P','IRO DEFERRED SA','Web Edit Listin','TRANSFERRED_DRO','TRANSFERRED')  ".$whereCond;
					}
				}else{
					$optimise_queryComp	.=	"left join db_iro.appointment as d on a.parentid=d.parentid  WHERE a.tmecode = '".$getRowId['data']['rowId']."' and a.data_source IN('joinfree','adprogrograms','IRO DEFERRED AP','IRO TRANS AP','Self Edit Reque','IRO ALREADY TAK','Self Edit UP','Self Edit NP','Self Edit P','IRO DEFERRED SA','Web Edit Listin','TRANSFERRED_DRO','TRANSFERRED')  ".$whereCond;
				}
				$queryNum	=	"SELECT count(1)  as count FROM tbl_tmesearch a ".$optimise_queryComp;		
				$conNum		=	parent::execQuery($queryNum, $this->conn_local_slave);
				$numRowsNum	=	parent::fetchData($conNum);			
			}
			
			if($this->city_indicator == 'remote_city'){
				if($allocId	!='' && strtolower($allocId) == 'o'){
					$join_col	.=",substring_index(group_concat(d.comments order by d.app_date desc),',',1) AS comments,substring_index(group_concat(d.app_date order by d.app_date desc),',',1) AS app_data,substring_index(group_concat(d.classcategory order by d.app_date desc),',',1) AS class,substring_index(group_concat(d.company order by d.app_date desc),',',1) AS company,substring_index(group_concat(d.iro order by d.app_date desc),',',1) as iro ";
					$optimise_queryCond		.=	" LEFT JOIN db_iro.appointment AS d ON a.parentid=d.parentid 
					WHERE  a.data_source IN('IRO DEFERRED AP','IRO TRANS AP','IRO ALREADY TAK','IRO DEFERRED SA') and a.tmecode = '".$getRowId['data']['rowId']."'
					".$whereCond." group by a.parentid ".$orderCond."".$limitFlag;
				}else{
					$join_col	.='';
					$optimise_queryCond		.=	" WHERE  a.data_source IN('joinfree','adprogrograms','IRO DEFERRED AP','IRO TRANS AP','Self Edit Reque','IRO ALREADY TAK','Self Edit UP','Self Edit NP','Self Edit P','IRO DEFERRED SA','Web Edit Listin','TRANSFERRED_DRO','TRANSFERRED') and a.tmecode = '".$getRowId['data']['rowId']."'
					".$whereCond." group by a.parentid ".$orderCond."".$limitFlag;
				}
			}else{
				$join_col	.=",substring_index(group_concat(d.comments order by d.app_date desc),',',1) AS comments,substring_index(group_concat(d.app_date order by d.app_date desc),',',1) AS app_data,substring_index(group_concat(d.classcategory order by d.app_date desc),',',1) AS class,substring_index(group_concat(d.company order by d.app_date desc),',',1) AS company,substring_index(group_concat(d.iro order by d.app_date desc),',',1) as iro ";
				$optimise_queryCond		.=	"LEFT JOIN db_iro.appointment AS d ON a.parentid=d.parentid 
				WHERE  a.data_source IN('joinfree','adprogrograms','IRO DEFERRED AP','IRO TRANS AP','Self Edit Reque','IRO ALREADY TAK','Self Edit UP','Self Edit NP','Self Edit P','IRO DEFERRED SA','Web Edit Listin','TRANSFERRED_DRO','TRANSFERRED') and a.tmecode = '".$getRowId['data']['rowId']."'
				".$whereCond." group by a.parentid ".$orderCond."".$limitFlag;
			}
			
			$query	=	"SELECT
				a.compname,
				a.allocationtime AS alloctime,
				a.allocationType AS alloctype,
				a.data_source,
				a.parentid AS contractid,
				a.updated_date,
				a.datasource_date ".$join_col." 
				FROM tbl_tmesearch a ".$optimise_queryCond ;
			$queryResult	=	parent::execQuery($query, $this->conn_local_slave);					
			$num			=	parent::numRows($queryResult);
			if($num > 0) {
				while($res	=  parent::fetchData($queryResult)) {
					$retArr['data'][]	=	$res;
				}
				$retArr['count']	=	$num;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Returned Successfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Available';
			}
			$retArr['count']		=	$num;
			if($this->params['srchparam'] != 'compnameLike'){
				$retArr['counttot']		=	$numRowsNum['count'];
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Allocated';
		}
		return $retArr;
	}
	//GetHotData API END
	
	//fetch NewBusiness API START
	function fetchNewBusiness(){
		$retArr	=	array();
		
		$mongo_qry_sort = array();
		$mongo_qry_like = array();
		$mongo_qry_in = array();
		$mongo_qry_limit = '';
		$mongo_qry_skip = '';
		
		$whereCond	=	'';
		$orderCond	=	' ORDER BY updatedon DESC';
		$mongo_qry_sort['updatedOn'] = -1;
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
						$mongo_qry_like['companyname'] = $this->params['srchData'];
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
				$mongo_qry_sort[$this->params['srchparam']] = $expOrder[1];
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			$mongo_qry_limit = $this->limitVal;
			$mongo_qry_skip = $pageVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
			$mongo_qry_limit = $this->limitVal;
			$mongo_qry_skip = 0;
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
			$mongo_qry_in['parentid'] = $setParId;
		}
		$tdate = date("Y-m-d");
		$fdate = date("Y-m-d", strtotime($tdate.' - 15 days'));
		$fdate = $fdate." 00:00:00"; 
		$tdate = $tdate." 23:59:59";
		if($this->params['srchparam'] != 'compnameLike')
		{
			if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
				$mongo_inputs['queryfield'] = json_encode(array("updatedBy"=>$this->ucode,"newbusinessflag"=>'1'));
				$mongo_inputs['fields'] 	= "parentid";
				$mongo_inputs["daterange"]  = json_encode(array("updatedOn"=>array($fdate,$tdate)));
				$mongo_inputs['in'] 		= json_encode($mongo_qry_in);
				$mongo_inputs['like'] 		= json_encode($mongo_qry_like);
				$mongo_res_ex_sh 			= $this->mongo_obj->getDataMatch($mongo_inputs);
				$rowcount 					= count($mongo_res_ex_sh);
			}
			else
			{			
				$queryNum	=	"SELECT count(1)  as count FROM tbl_companymaster_extradetails_shadow WHERE updatedby = '".$this->ucode."' AND newbusinessflag=1 and updatedon >= date_sub(now(), INTERVAL 15 day) ".$whereCond;
				$resQuery  	=	parent::execQuery($queryNum, $this->conn_tme);					
				$numRowsNum	=	parent::fetchData($resQuery);
				$rowcount 	= 	$numRowsNum['count'];
			}
		}
		
		if($this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['queryfield'] = json_encode(array("updatedBy"=>$this->ucode,"newbusinessflag"=>'1'));
			$mongo_inputs['fields'] 	= "companyname";
			$mongo_inputs['aliaskey'] 	= json_encode(array("parentid"=>"contractid","updatedOn"=>"updatedon"));
			$mongo_inputs["daterange"]  = json_encode(array("updatedOn"=>array($fdate,$tdate)));
			$mongo_inputs['orderby']	= json_encode($mongo_qry_sort);
			$mongo_inputs['in'] 		= json_encode($mongo_qry_in);
			$mongo_inputs['like'] 		= json_encode($mongo_qry_like);
			$mongo_inputs['limit'] 		= $mongo_qry_limit;
			$mongo_inputs['skip'] 		= $mongo_qry_skip;
			$res			 			= $this->mongo_obj->getDataMatch($mongo_inputs);
			$num 						= count($res);
		}
		else
		{
			$query	=	"SELECT parentid as contractid,companyname,updatedon FROM tbl_companymaster_extradetails_shadow WHERE updatedby = '".$this->ucode."' AND newbusinessflag=1 and updatedon >= date_sub(now(), INTERVAL 15 day) ".$whereCond." ".$orderCond." ".$limitFlag;
			$con  	=	parent::execQuery($query, $this->conn_tme);
			$num	=	parent::numRows($con);
			$res	=  parent::fetchData($con);
		}
		if($num > 0) {
			foreach($res as $response) {
				$retArr['data'][]	=	$response;
			}
			$retArr['count']		=	$num;
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data Returned Successfully';
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Available';
		}
		$retArr['count']		=	$num;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']		=	$rowcount;
		}
		return $retArr;
	}
	//fetch NewBusiness API END
	//Restaurant Data API
	function ownership(){
		$respArr	=	array();
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		//~ $this->ucode = '005178';
		if($_SERVER['REMOTE_ADDR'] == '172.29.87.77') {
			$this->ucode = '005178';
		}
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM d_jds.tbl_tme_data_search	WHERE empcode='".$this->ucode."' and data_flag=".$this->params['data_flag'];
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);
		}
		$quRestuarant	=	"SELECT parentid as contractid,companyname,allocationtime,allocationtype FROM d_jds.tbl_tme_data_search	WHERE empcode='".$this->ucode."' and data_flag=".$this->params['data_flag']."".$orderCond." ".$limitFlag;
		$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_local_slave);
		$numPage	    =	parent::numRows($conRestuarant);
		if($numPage > 0) {			
			while($res	=	parent::fetchData($conRestuarant)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	//Restaurant Data API
	//Restaurant Data API
	/*function fetchRestaurantData(){
		$respArr	=	array();
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM rest_allocation_data	WHERE empcode='".$this->ucode."' and allocation_flag=1 ".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);
		}
			
		$quRestuarant	=	"SELECT parentid as contractid,companyname,company_callcnt,menu_avail FROM rest_allocation_data	WHERE empcode='".$this->ucode."' and allocation_flag=1 ".$whereCond."".$orderCond." ".$limitFlag;
		$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_local_slave);
		$numPage	    =	parent::numRows($conRestuarant);
		if($numPage > 0) {			
			while($res	=	parent::fetchData($conRestuarant)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}*/
	
	function fetchRestaurantData(){
		$respArr	=	array();
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM allocation.tbl_grocery_data	WHERE tmecode='".$this->ucode."' ".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local);					
			$numRowsNum	=	parent::fetchData($conNum);
		}
		$quRestuarant	=	"SELECT parentid as contractid,companyname FROM allocation.tbl_grocery_data	WHERE tmecode='".$this->ucode."' order by orderby asc ".$whereCond." ".$limitFlag;
		$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_local);
		$numPage	    =	parent::numRows($conRestuarant);
		if($numPage > 0) {			
			while($res	=	parent::fetchData($conRestuarant)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	
	
	//Restaurant Data API
	
	//EcsData API START
	function fetchEcsData(){		
		$retArr		 =	array();
		$parentidStr =	'';
		$whereCond	=	'';
		$orderCond="ORDER BY updatedate DESC";
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND b.companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
					case 'approved' :
						$whereCond	=	' AND a.ecs_reject_approved = "1" ';
					break;
					case 'rejected' :
						$whereCond	=	' AND a.ecs_reject_approved = "2" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND a.parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}	
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(a.parentid) as count FROM 			
					d_jds.tbl_ecs_retention_action a LEFT JOIN db_iro.tbl_companymaster_generalinfo b ON a.parentid=b.parentid WHERE a.tmecode = '".$this->ucode."' AND a.action_flag NOT IN ('7','8')".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);			
		}		
		$queryPage	=	"SELECT a.parentid,a.action_flag,a.tme_comments,a.tmecode,a.updatedate,a.allocate_by_cs,a.transfer_by_cs,a.ecs_reject_approved,b.companyname, b.data_city FROM 			
					d_jds.tbl_ecs_retention_action a LEFT JOIN db_iro.tbl_companymaster_generalinfo b ON a.parentid=b.parentid WHERE a.tmecode = '".$this->ucode."' AND a.action_flag NOT IN ('7','8') ".$whereCond.$orderCond.$limitFlag;		
		$conPage  	=	parent::execQuery($queryPage, $this->conn_local_slave);					
		$numPage	=	parent::numRows($conNum);				
			
		if($numPage > 0) {
			while($res	=	parent::fetchData($conPage)) {
				$retArr['data'][$res['parentid']]['tme_comments']			=	$res['tme_comments'];
				$retArr['data'][$res['parentid']]['action_flag']			=	$res['action_flag'];
				$retArr['data'][$res['parentid']]['tmecode']				=	$res['tmecode'];
				$retArr['data'][$res['parentid']]['updatedate']				=	$res['updatedate'];
				$retArr['data'][$res['parentid']]['allocate_by_cs']			=	$res['allocate_by_cs'];
				$retArr['data'][$res['parentid']]['transfer_by_cs']			=	$res['transfer_by_cs'];
				$retArr['data'][$res['parentid']]['ecs_reject_approved']	=	$res['ecs_reject_approved'];
				$retArr['data'][$res['parentid']]['compname']				=	$res['companyname'];
				$retArr['data'][$res['parentid']]['data_city']				=	$res['data_city'];
				$retArr['data'][$res['parentid']]['contractid']				=	$res['parentid'];
				$ecsTrackRepStat	=	json_decode($this->checkTrackerRep($res['parentid']),1);
				if($ecsTrackRepStat['ECS']	==	'1' && $ecsTrackRepStat['SI'] == '0') {
					$retArr['data'][$res['parentid']]['ecsTrackRep']	=	'ECS';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '0'){
					$retArr['data'][$res['parentid']]['ecsTrackRep']	=	'SI';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '1'){
					$retArr['data'][$res['parentid']]['ecsTrackRep']	=	'ECS/SI';
				} else {
					$retArr['data'][$res['parentid']]['ecsTrackRep']	=	'NECS';
				}
				$parentidStr	.=	"'".$res['parentid']."'".',';
			}
			$valRet =	$this->fetchActionFlag($parentidStr,$this->ucode);  
			$Ecsflag=	$this->fetchEcsFlag($parentidStr); 				
			$EcsSi	=	$this->fetchEcsFlagSI($parentidStr);		
			$valdecode	=	json_decode($valRet,true);
			$flagdecode	=	json_decode($Ecsflag,true);
			$Sidecode	=	json_decode($EcsSi,true);
			
			if($valdecode['errorCode']==	0) {
				foreach($valdecode['data'] as $key=>$value) {
					$retArr['data'][$key]['last_action']	=	$value['last_action'];
				}
			}
			if($flagdecode['errorCode']	==	0) {
				foreach($flagdecode['data'] as $key=>$value) {
					$retArr['data'][$key]['flag']			=	$value['flag'];
					$retArr['data'][$key]['eflag']			=	$value['eflag'];
					$retArr['data'][$key]['ecs_stop_flag']	=	$value['ecs_stop_flag'];
				}
			}
			if($Sidecode['errorCode']	==	0) {
				foreach($Sidecode['data'] as $key=>$value) {
					$retArr['data'][$key]['Si_flag']			=	$value['Si_flag'];
					$retArr['data'][$key]['Si_eflag']			=	$value['Si_eflag'];
					$retArr['data'][$key]['Si_ecs_stop_flag']	=	$value['Si_ecs_stop_flag'];
				}
			}
			$respArr	=	array();
			foreach($retArr['data'] as $keyPar=>$valPar) {
				$respArr['data'][]	=	$valPar;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Row Id Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'No Rowid Found';
		}
		$respArr['count']		=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	//EcsData API END
	
	//ExpiredData API START
	function fetchExpiredData(){
		$respArr	=	array();
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}			
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM d_jds.tbl_expire_data WHERE tmeCode ='".$getRowId['data']['rowId']."'".$whereCond;			
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);		
		}
		
		$quExpired	=	"SELECT parentid as contractid,compname as companyname FROM d_jds.tbl_expire_data WHERE tmeCode='".$getRowId['data']['rowId']."' ".$whereCond." ".$orderCond." ".$limitFlag;
		$conExpired =	parent::execQuery($quExpired, $this->conn_local_slave);					
		$numPage	=	parent::numRows($conExpired);				
		if($numPage > 0) {
			while($res	=	parent::fetchData($conExpired)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	//ExpiredData API END
	
	//ProspectData API START
	function fetchProspectData(){		
		$retArr	=	array();
		$orderCond	=	" ORDER BY compname ";
		$whereCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	'') {
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}			
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count from allocation.tbl_prospectlist where tmecode = '".$this->ucode."'".$whereCond;			
			$conNum	    =	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);				
		}		
		$SelProspectPage = "SELECT parentid as contractid,compname,tmecode from allocation.tbl_prospectlist where tmecode = '".$this->ucode."'".$whereCond."".$orderCond.$limitFlag;
		$conProspectPage =  parent::execQuery($SelProspectPage, $this->conn_local_slave);		
		$numPage         =	parent::numRows($conProspectPage);		
		if($numPage>0){
			while($rowPros	=	parent::fetchData($conProspectPage)){
				$retArr['data'][]	=	$rowPros;
			}			
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	='Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']		=	$numPage;
		$retArr['counttot']		=	$numRowsNum['count'];
		return $retArr;
	}
	//ProspectData API END
	
	//Special DATA API START
	/*function fetchSpecialData(){	
		$dbObjLocal	=	new DB($this->db['db_local']);		
		$retArr	    = array();
		$whereCond	= '';
		$orderCond  = '';
		$groupCond  = '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by group_id";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		//$orderCond="order by autoid DESC";
		//$groupCond="group by group_id";
		if($this->city_indicator == 'remote_city'){ 
			$cityTme	=	json_decode($this->EmpInfo($this->ucode),true);
			$zoneCity	=	json_decode($this->RemoteZoneCities($cityTme['results']['city']),true);
			$zoneCity['errorCode'] = 1; //giving this as 1 bcoz tbl doesnt exist fr many of the cities.
			if($zoneCity['errorCode']	==	0) {
				$tableRem	=	'tbl_special_data_'.strtolower($zoneCity['data']);
			} else {
				$tableRem	=	'tbl_special_data';
			}			
			$queryNum 	=	"SELECT * from ".$tableRem." WHERE tmecode ='".$this->ucode."'".$groupCond;
			$conNum	    =	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::numRows($conNum);	
			
			$quSpecialData	=	"SELECT companyname,parentid as contractid,entry_date from ".$tableRem." WHERE tmecode ='".$this->ucode."'";
		} else {
			if($this->params['srchparam'] != 'compnameLike'){
				$queryNum 	=	"SELECT count(1) from tbl_special_data WHERE tmecode ='".$this->ucode."'".$groupCond;
				$conNum	    =	parent::execQuery($queryNum, $this->conn_local_slave);					
				$numRowsNum	=	parent::numRows($conNum);
			}			
			$quSpecialData	=	"SELECT companyname,parentid as contractid,entry_date from tbl_special_data WHERE tmecode ='".$this->ucode."'";
		}		
		$quSpecialData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
		$conSpecialData  =	parent::execQuery($quSpecialData, $this->conn_local_slave);		
		$numPage	     =	parent::numRows($conSpecialData);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conSpecialData)) {
				$retArr['data'][ ]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum;
		}
		return $retArr;
	}
	*/
	
	function fetchSpecialData(){	
		$dbObjLocal	=	new DB($this->db['db_local']);		
		$retArr	    = array();
		$whereCond	= '';
		$orderCond  = '';
		$groupCond  = '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by parentid";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		//$orderCond="order by autoid DESC";
		//$groupCond="group by group_id";
		
			if($this->params['srchparam'] != 'compnameLike'){
				$queryNum 	=	"SELECT count(1) from allocation.tbl_buy1_get1 WHERE tmecode ='".$this->ucode."'".$groupCond;
				$conNum	    =	parent::execQuery($queryNum, $this->conn_local);					
				$numRowsNum	=	parent::numRows($conNum);
			}			
			$quSpecialData	=	"SELECT companyname,parentid as contractid,entry_date from allocation.tbl_buy1_get1 WHERE tmecode ='".$this->ucode."'";
			
		$quSpecialData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
		$conSpecialData  =	parent::execQuery($quSpecialData, $this->conn_local);		
		$numPage	     =	parent::numRows($conSpecialData);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conSpecialData)) {
				$retArr['data'][ ]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum;
		}
		return $retArr;
	}
	
	
	//Special DATA API END
	
	//Restaurant Account Details API START
	function accountDetRest(){ //online_regis
		$retArr		= array();
		$whereCond	= '';
		$orderCond	= '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		$queryNum 	= "SELECT parentid as contractid,companyname FROM online_regis.tbl_onlinepay_restraunt WHERE empcode ='".$this->ucode."'".$whereCond;		
		$conNum  	=  parent::execQuery($queryNum, $this->conn_idc);					
		$numRowsNum	=  parent::numRows($conNum);	
				
		$sqlSelect 	= "SELECT parentid as contractid,companyname FROM online_regis.tbl_onlinepay_restraunt WHERE empcode ='".$this->ucode."'".$whereCond." ".$orderCond." ".$limitFlag;		
		$con  		=	parent::execQuery($sqlSelect, $this->conn_idc);					
		$numPage	=	parent::numRows($con);
		if($numPage > 0) {
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum;
		return $retArr;
	}
	//Restaurant Account Details API END
	
	//TME allocation API START
	function fetchtmeAllocData(){		
		$retArr     = array();
		$whereCond	= '';
		$orderCond  = '';
		$groupCond  = '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by parentId";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if( !empty($this->params['limitFlag']) ) {
			$this->limitVal = 10;
		} 
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	= "SELECT count(1) as count from allocation.tbl_tmedata_alloc WHERE tmecode IN('".$this->ucode."')".$groupCond;
			$conNum  	=  parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=  parent::numRows($conNum);
		}		
		$qutmeAllocData     = "SELECT compname as companyname,parentId as contractid,datasource_date as entry_date from allocation.tbl_tmedata_alloc WHERE tmecode IN('".$this->ucode."')";
		$qutmeAllocData	   .=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;		
		$contmeAlloclData  	=  parent::execQuery($qutmeAllocData, $this->conn_local_slave);					
		$numPage			=  parent::numRows($contmeAlloclData);
		if($numPage > 0) {
			while($res	=	 parent::fetchData($contmeAlloclData)) {
				$retArr['data'][ ]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum['count'];
		}
		return $retArr;
	}
	//TME allocation API END
	
	//Retention DATA API START
	function fetchReversedRetentionData(){		
			
		$retArr     = array();
		$whereCond	= '';
		$ecsCond    = '';
		$andCond    = "";
		$stateCond  = "";
		$orderCond  = 'order by update_date desc';
		$groupCond  = '';		
		$pending = $ringing = $follow_up = $invalid_data = $ignore_request = $ecs_call = $upgrade = $degrade = $degrade = $retained = $reallocated = $req_to_stop = $full_stopped = $ecs_continued =$ecs_continued = $ecs_reactivate = $ecs_skip = $rejected = $repeat_call = $per_retained = $per_ecs_skip = $per_upgrade = $per_degrade = $per_invalid_data = $per_ecs_call = $per_ignore_request = $total_report_contracts = 0;
			
		$disp_actions = explode(":",$this->params['srchparam']);
		$action_name  = trim(strtolower($disp_actions[0]));		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all'){
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) { //ecs_actions
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
					case 'parentidLike' :
						$whereCond	=	' AND parentid = "'.$this->params['srchData'].'" ';
					break;
					case 'followup' :
						$whereCond	=	' AND action_flag = 4';
					break;
					case 'retain' :
						$whereCond	=	' AND action_flag = 5';
					break;
					case 'stoptme' :
						$whereCond	=	' AND action_flag = 9';
					case 'repeatCall' :
						$whereCond	=	' AND (repeat_call = 1 or repeat_call = 2 or repeat_call = 3 or repeat_call = 4)';
						$orderCond	=  'order by repeatcall_taggedon desc'; 
					break;
					case 'web_dialer' :
					$whereCond	=	' AND request_source = "web_dialer"';
					break;					
					case 'phone search' :
					$whereCond	=	' AND request_source = "phone search"';
					break;
					case 'iro' :
					$whereCond	=	' AND request_source = "iro" AND allocate_by_cs = "0"';
					break;
				}
			}else if($this->params['srchwhich'] == 'ecs_actions') {
				$orderCond	=	' ORDER BY update_date DESC';
				if($action_name != "retained percentage"){
					$andCond = ' AND update_date > repeatcall_taggedon';
					$stateCond = " AND state != '3'";
					if($action_name == "pending"){
						$whereCond	=	' AND action_flag = 0 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "ringing"){
						$whereCond	=	' AND action_flag = 21 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "follow up"){
						$whereCond	=	' AND action_flag = 4 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "invalid data"){
						$whereCond	=	' AND action_flag = 26 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "ignore request"){
						$whereCond	=	' AND action_flag = 25 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "ecs clarification call"){
						$whereCond	=	' AND action_flag = 27 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "upgrade"){
						$whereCond	=	' AND action_flag = 18 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "degrade"){
						$whereCond	=	' AND action_flag = 19 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "retained"){
						$whereCond	=	' AND action_flag = 5 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "reallocated"){

						// ((reallocated_flag = 1 AND action_flag = 0) OR repeat_call = 1 OR repeat_call = 2)
						$whereCond	=	' AND ((reallocated_flag = 1 AND action_flag = 0) OR repeat_call = 1 OR repeat_call = 2)'.$stateCond;
					}else if($action_name == "requested to stop"){
						$whereCond	=	' AND action_flag = 9 AND (repeat_call != 1) '.$andCond;
					}else if($action_name == "completely stopped"){
						$whereCond	=	' AND action_flag IN ("10","14","13")  AND (repeat_call != 1)';
					}else if($action_name == "ecs continued"){
						$whereCond	=	' AND action_flag = 23 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "ecs reactivate"){
						$whereCond	=	' AND action_flag = 15 AND (repeat_call != 1)';
					}else if($action_name == "ecs skip"){
						$whereCond	=	' AND action_flag = 24 AND (repeat_call != 1)'.$stateCond;
					}else if($action_name == "rejected"){
						$whereCond	=	' AND action_flag = 12 AND (repeat_call != 1)';
					}else if($action_name == "repeat call"){
						$andCond = ' AND repeatcall_taggedon > update_date ';
						$whereCond	=	' AND (repeat_call = 1 or repeat_call = 2 or repeat_call = 3 or repeat_call = 4)'.$andCond.$stateCond;
						$orderCond	=  'order by repeatcall_taggedon desc'; 
					}
				}					
			}else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by parentId";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		}else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		}else if($fullData	!=	''){
			$limitFlag	=	"";
		}else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$queryNum 	=	"SELECT COUNT(1) AS COUNT FROM tbl_new_retention  WHERE tmecode ='".$this->ucode."' or escalated_details like '%".$this->ucode."%'";
		$conNum  	=	parent::execQuery($queryNum, $this->conn_local);					
		$numRowsNum	=	parent::fetchData($conNum);		
		
		$qutmeAllocData	=	"SELECT companyname as compname,parentId as contractid,update_date as entry_date,data_city,ecs_stop_flag,tmename,action_flag,tmecode,escalated_details,state,reactivate_flag,reactivated_on,reactivated_by,tme_comment as tme_comments,repeat_call,repeatcall_taggedon,ecs_reject_approved,reactivate_reject_comment,reallocated_flag,insert_date,allocate_by_cs,repeatCount,request_source from d_jds.tbl_new_retention WHERE tmecode = '".$this->ucode."' AND (parentid != '' AND parentid IS NOT NULL) AND contract_flag = 0";	    
		$qutmeAllocData	.=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
		$contmeAlloclData =	parent::execQuery($qutmeAllocData, $this->conn_local);									
		$numPage          =	parent::numRows($contmeAlloclData);
		 //~ && $EmpData['allocId'] == "RD" && $EmpData['Approval_flag'] == 1
		if($numPage > 0) {
			while($res	=	parent::fetchData($contmeAlloclData)) {
				//$retArr['data'][ ]		=	$res;

				//$sql_getcomp = "SELECT companyname FROM db_iro.tbl_companymaster_generalinfo where parentid='".$res['contractid']."'";
				//$contme_nw =	parent::execQuery($sql_getcomp, $this->conn_local);									
				//$contme_ne =	parent::fetchData($contme_nw);									
				$comp_params = array();
				$comp_params['data_city'] 	= $this->data_city;
				$comp_params['table'] 		= 'gen_info_id';
				$comp_params['module'] 		= $this->module;
				$comp_params['parentid'] 	= $res['contractid'];
				$comp_params['action'] 		= 'fetchdata';
				$comp_params['page'] 		= 'tmeNewServicesClass';
				$comp_params['fields']		= 'companyname';

				$comp_api_res		= 	array();
				$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);
				
				$contme_ne 			= 	$comp_api_res['results']['data'][$res['contractid']];
				// print_r($contme_ne['companyname']);
				// die;


				$retArr['data'][$res['contractid']]['tme_comments']			=	$res['tme_comments'];
				$retArr['data'][$res['contractid']]['action_flag']			=	$res['action_flag'];
				$retArr['data'][$res['contractid']]['tmecode']				=	$res['tmecode'];
				$retArr['data'][$res['contractid']]['entry_date']			=	$res['entry_date'];
				$retArr['data'][$res['contractid']]['allocate_by_cs']		=	$res['allocate_by_cs'];
				$retArr['data'][$res['contractid']]['ecs_reject_approved']	=	$res['ecs_reject_approved'];
				// $retArr['data'][$res['contractid']]['compname']				=	$res['compname'];
				$retArr['data'][$res['contractid']]['compname']  			= $contme_ne['companyname'];
				$retArr['data'][$res['contractid']]['data_city']			=	$res['data_city'];
				$retArr['data'][$res['contractid']]['contractid']			=	trim($res['contractid']);
				$retArr['data'][$res['contractid']]['ecs_stop_flag']		=	$res['ecs_stop_flag'];
				$retArr['data'][$res['contractid']]['tmename']		        =	$res['tmename'];
				$retArr['data'][$res['contractid']]['escalated_details']	=	$res['escalated_details'];
				$retArr['data'][$res['contractid']]['state']	            =	$res['state'];
				$retArr['data'][$res['contractid']]['reactivate_flag']	    =	$res['reactivate_flag'];
				$retArr['data'][$res['contractid']]['reactivated_on']	    =	$res['reactivated_on'];
				$retArr['data'][$res['contractid']]['reactivated_by']	    =	$res['reactivated_by'];
				$retArr['data'][$res['contractid']]['repeat_call']	        =	$res['repeat_call'];
				$retArr['data'][$res['contractid']]['repeatcall_taggedon']  =	$res['repeatcall_taggedon'];
				$retArr['data'][$res['contractid']]['ecs_reject_approved']  =	$res['ecs_reject_approved'];
				$retArr['data'][$res['contractid']]['reactivate_reject_comment']  =	$res['reactivate_reject_comment'];
				$retArr['data'][$res['contractid']]['request_source']  =	$res['request_source'];
				$retArr['data'][$res['contractid']]['repeatCount']  =	$res['repeatCount'];
				$retArr['data'][$res['contractid']]['date_str']  =	$res['insert_date'];
				$date_arr = explode(',',$res['insert_date']);
				foreach($date_arr as $date_key => $date_val) {
				if($temp = strchr($date_val,$tmecode)) {
						$temp1 = explode('~',$temp);
						$retArr['data'][$res['contractid']]['date'] = $temp1['1'];
					}
				}
				$ecsTrackRepStat	=	json_decode($this->checkTrackerRep($res['contractid']),1);
				if($ecsTrackRepStat['ECS']	==	'1' && $ecsTrackRepStat['SI'] == '0') {
					$retArr['data'][$res['contractid']]['ecsTrackRep']	=	'ECS';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '0'){
					$retArr['data'][$res['contractid']]['ecsTrackRep']	=	'SI';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '1'){
					$retArr['data'][$res['contractid']]['ecsTrackRep']	=	'ECS/SI';
				} else {
					$retArr['data'][$res['contractid']]['ecsTrackRep']	=	'NECS';
				}				
				$select_finance = "SELECT * FROM db_finance.tbl_companymaster_finance WHERE parentid = '".$res['contractid']."' AND campaignid IN ('72','73')";
				$select_finance_Res  	=	parent::execQuery($select_finance, $this->conn_fin);					
				$select_finance_numRows	=	parent::numRows($select_finance_Res);		
				if($select_finance_numRows > 0){
					$retArr['data'][$res['contractid']]['website'] = "1"; 
				}else{
					$retArr['data'][$res['contractid']]['website'] = "0";
				}
			}			
		    foreach($retArr['data'] as $keyPar=>$valPar) {
			   if($valPar['contractid'] != '' || $valPar['contractid'] != null){
				   $select_uploaded_file = "SELECT * FROM d_jds.tbl_retention_lead_transfer_uploads where parentid = '".$valPar['contractid']."'  ORDER BY update_date DESC";
				   $select_uploaded_file_Res  	    =	parent::execQuery($select_uploaded_file, $this->conn_local);					
				   $select_uploaded_file_NumRows	=	parent::numRows($select_uploaded_file_Res);		
				   
				   if($select_uploaded_file_NumRows > 0){
					   while($row = parent::fetchData($select_uploaded_file_Res)){
						   $valPar['uploaded_files'][] = $row;
					   }
				   }
			   }
				$respArr['data'][]	=	$valPar;
			}
			 $ecsReportCountData = "SELECT companyname as compname,parentId as contractid,update_date as entry_date,data_city,ecs_stop_flag,tmename,action_flag,tmecode,escalated_details,state,reactivate_flag,reactivated_on,reactivated_by,tme_comment as tme_comments,repeat_call,repeatcall_taggedon,ecs_reject_approved,reactivate_reject_comment,insert_date,allocate_by_cs,repeatCount,request_source,reallocated_flag from d_jds.tbl_new_retention WHERE tmecode = '".$this->ucode."' AND contract_flag = 0 AND (parentid != '' AND parentid IS NOT NULL)";
			$sel_retention_report_Res  	    =	parent::execQuery($ecsReportCountData, $this->conn_local);					
			//~ $total_report_contracts	=	$dbObjLocal->numRows($sel_retention_report_Res);

	
			while($res	=	parent::fetchData($sel_retention_report_Res)){

					$datetime=new DateTime($res['repeatcall_taggedon']);
					$curr_date_pri = new DateTime($res['entry_date']);
					$datetime->getTimestamp(); 
					$curr_date_pri->getTimestamp(); 
					$monthyear = date("Y-m"); 
					$date = date("Y-m",strtotime($res['entry_date']));
					
					if($date >= $monthyear){
						$total_report_contracts = $total_report_contracts + 1;
					}
				// if(($res['repeatcall_taggedon'] != null && $res['repeatcall_taggedon'] != '0000-00-00 00:00:00') && ($datetime->getTimestamp() >= $curr_date_pri->getTimestamp()))
				// {
				// 		if(($res['repeat_call'] == 1 || $res['repeat_call'] == 2 || $res['repeat_call'] == 3 || $res['repeat_call'] == 4) && $res['state'] == 2)
				// 		{
				// 			$repeat_call = $repeat_call + 1;
				// 		}
				// }else{
					if($res['action_flag'] == 0 && $res['state'] == 2 && $res['reallocated_flag'] != 1 && $res['repeat_call'] != 1){
						$pending = $pending + 1;
					}else if($res['action_flag'] == 21 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ringing = $ringing + 1;
					}else if($res['action_flag'] == 4 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$follow_up = $follow_up + 1;
					}else if($res['action_flag'] == 26 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$invalid_data = $invalid_data + 1;
						if ($date >= $monthyear) $per_invalid_data = $per_invalid_data + 1;
					}else if($res['action_flag'] == 25 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ignore_request = $ignore_request + 1;
						if ($date >= $monthyear) $per_ignore_request = $per_ignore_request + 1;
					}else if($res['action_flag'] == 27 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ecs_call = $ecs_call + 1;
						if ($date >= $monthyear) $per_ecs_call = $per_ecs_call + 1;
					}else if($res['action_flag'] == 18 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$upgrade = $upgrade + 1;
						if ($date >= $monthyear) $per_upgrade = $per_upgrade + 1;
					}else if($res['action_flag'] == 19 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$degrade = $degrade + 1;
						if ($date >= $monthyear) $per_degrade = $per_degrade + 1;
					}else if($res['action_flag'] == 5 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$retained = $retained + 1;
						if ($date >= $monthyear) $per_retained = $per_retained + 1;
					}				
					else if((($res['reallocated_flag'] == 1 && $res['action_flag'] == 0) || $res['repeat_call'] == 1 || $res['repeat_call'] == 2)  && $res['state'] == 2 && $res['state']!= 3 ) {	
						$reallocated = $reallocated + 1; 

					}else if($res['action_flag'] == 9 && $res['repeat_call'] != 1){
						$req_to_stop = $req_to_stop + 1;
					}else if($res['action_flag'] == 10 || $res['action_flag'] == 14 || $res['action_flag'] == 13 && $res['repeat_call'] != 1){
						$full_stopped = $full_stopped + 1;
					}else if($res['action_flag'] == 23 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ecs_continued = $ecs_continued + 1;
					}else if($res['action_flag'] == 15 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ecs_reactivate = $ecs_reactivate + 1;
					}else if($res['action_flag'] == 24 && $res['state'] == 2 && $res['repeat_call'] != 1){
						$ecs_skip = $ecs_skip + 1;
						if ($date >= $monthyear) $per_ecs_skip = $per_ecs_skip + 1;
					}else if($res['action_flag'] == 12 && $res['repeat_call'] != 1){
						$rejected = $rejected + 1;
					}
				
			}


			$ret_num = $per_retained + $per_ecs_skip + $per_upgrade + $per_degrade;
			$ret_deno = $total_report_contracts - ($per_invalid_data + $per_ecs_call + $per_ignore_request);
			if($ret_deno!=0){
				$retained_percentage = round((($ret_num / $ret_deno) * 100),1);			
			}
			
			$respArr['act_count'][0]['key']  =	"Pending";					$respArr['act_count'][0]['val']  =	"Pending : ".$pending;
			$respArr['act_count'][1]['key']  =	"Ringing";					$respArr['act_count'][1]['val']  =	"Ringing : ".$ringing;
			$respArr['act_count'][2]['key']  =	"Follow Up";				$respArr['act_count'][2]['val']  =	"Follow Up : ".$follow_up;
			$respArr['act_count'][3]['key']  =	"Repeat Call";				$respArr['act_count'][3]['val']  =	"Repeat Call : ".$repeat_call;
			// repeat_call + Reallocated mergedrepeat_call
			$respArr['act_count'][4]['key']  =	"Reallocated";				$respArr['act_count'][4]['val']  =	"Reallocated : ".$reallocated;
			$respArr['act_count'][5]['key']  =	"Rejected";					$respArr['act_count'][5]['val']  =	"Rejected : ".$rejected;
			$respArr['act_count'][6]['key']  =	"Invalid Data";				$respArr['act_count'][6]['val']  =	"Invalid Data : ".$invalid_data;
			$respArr['act_count'][7]['key']  =	"Ignore Request";			$respArr['act_count'][7]['val']  =	"Ignore Request : ".$ignore_request;
			$respArr['act_count'][8]['key']  =	"Ecs Clarification Call";	$respArr['act_count'][8]['val']  =	"Ecs Clarification Call : ".$ecs_call;
			$respArr['act_count'][9]['key']  =	"Upgrade";					$respArr['act_count'][9]['val']  =	"Upgrade : ".$upgrade;
			$respArr['act_count'][10]['key']  =	"Degrade";					$respArr['act_count'][10]['val']  =	"Degrade : ".$degrade;
			$respArr['act_count'][11]['key']  =	"Retained";					$respArr['act_count'][11]['val']  =	"Retained : ".$retained;
			$respArr['act_count'][12]['key']  =	"Requested To Stop";		$respArr['act_count'][12]['val']  =	"Requested To Stop : ".$req_to_stop;
			$respArr['act_count'][13]['key']  =	"Completely Stopped";		$respArr['act_count'][13]['val']  =	"Completely Stopped : ".$full_stopped;
			$respArr['act_count'][14]['key']  =	"Ecs Continued";			$respArr['act_count'][14]['val']  =	"Ecs Continued : ".$ecs_continued;
			$respArr['act_count'][15]['key']  =	"Ecs Reactivate";			$respArr['act_count'][15]['val']  =	"Ecs Reactivate : ".$ecs_reactivate;
			$respArr['act_count'][16]['key']  =	"Ecs Skip";					$respArr['act_count'][16]['val']  =	"Ecs Skip : ".$ecs_skip;
			$respArr['act_count'][17]['key']  =	"Retained Percentage";		$respArr['act_count'][17]['val']  =	"Retained Percentage : ".$retained_percentage."%";
			
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Success';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Fail';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['COUNT'];
		}
		return $respArr;
	}
	//Retention DATA API END
	
	//NonECS data API START
	function fetchNonecsData(){ 		
		$retArr     = array();
		$whereCond	= '';
		$orderCond  = '';
		$groupCond  = '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by parentId";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count from d_jds.tbl_dialer_process_ecs WHERE ecs_flag=0 AND paid=1 and emp_id IN('".$this->ucode."')".$groupCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum         	=	parent::fetchData($conNum);	
		}
		$qutmeAllocData	=	"SELECT compname as companyname,parentId as contractid,ecs_flag,paid from d_jds.tbl_dialer_process_ecs WHERE ecs_flag=0 AND paid=1 and emp_id IN('".$this->ucode."')";		
		$qutmeAllocData	   .=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
		$contmeAlloclData  	=	parent::execQuery($qutmeAllocData, $this->conn_local_slave);					
		$numPage         	=	parent::numRows($contmeAlloclData);	
		if($numPage > 0) {
			while($res	=	parent::fetchData($contmeAlloclData)) {
				$retArr['data'][ ]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum['count'];
		}
		return $retArr;
	}
	//NonECS data API END
	
	//Unsold DATA API START
	function fetchunsoldData(){		
		$retArr     = array();
		$whereCond	= '';
		$orderCond  = '';
		$groupCond  = '';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by group_id";
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$orderCond="";
		$groupCond="";
		$quSpecialData	=	"SELECT Companyname as companyname,Parentid as contractid FROM db_iro.tbl_unsold_inventory_details WHERE Empolyee_Id='".$this->ucode."' GROUP BY Parentid";
		$conSpecialData  	=	parent::execQuery($quSpecialData, $this->conn_iro);					
		$numPage			=	parent::numRows($conSpecialData);	
		if($numPage > 0) {
			while($res	=	parent::fetchData($conSpecialData)) {
				$retArr['data'][ ]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Row Id Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Rowid Found';
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum;
		return $retArr;
	}
	//Unsold DATA API END
	
	//ExpiredDataEcs DATA API START
	function fetchExpiredDataEcs(){
		$respArr	=	array();
		$whereCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM allocation.tbl_expired_main WHERE ecs_flag=1 AND tmeCode ='".$getRowId['data']['rowId']."'".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);	
		}		
		$quExpired	=	"SELECT parentid as contractid,companyname as companyname FROM allocation.tbl_expired_main WHERE ecs_flag=1 AND tmeCode='".$getRowId['data']['rowId']."' ".$whereCond." ".$orderCond." ".$limitFlag;
		$conExpired =	parent::execQuery($quExpired, $this->conn_local_slave);					
		$numPage	=	parent::numRows($conExpired);	
		if($numPage > 0) {
			while($res	=	parent::fetchData($conExpired)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}	
	//ExpiredDataEcs DATA API END
	
	//ExpiredNONECSData DATA API Start
	function fetchExpiredDataNonEcs(){		
		$respArr	=	array();
		$whereCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null){
			$setParId	=	str_replace(",","','",$this->params['parid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		$getRowId	=	json_decode($this->getRowId($tmecode),true);
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM allocation.tbl_expired_main WHERE ecs_flag=0 AND tmeCode ='".$getRowId['data']['rowId']."'".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::fetchData($conNum);				
		}		
		$quExpired	=	"SELECT parentid as contractid,companyname as companyname FROM allocation.tbl_expired_main WHERE ecs_flag=0 AND tmeCode='".$getRowId['data']['rowId']."' ".$whereCond." ".$orderCond." ".$limitFlag;
		$conExpired =	parent::execQuery($quExpired, $this->conn_local_slave);					
		$numPage	=	parent::numRows($conExpired);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conExpired)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	//ExpiredNONECSData DATA API END
	
	//EcsRequest DATA API STARTS
	function fetchEcsRequestData(){		 
		$retArr= array();		
		$whereCond	=	'';
		$orderCond	=	' ORDER BY requested_on DESC';			
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'companynameLike' :
						$whereCond	=	' AND companyname LIKE "'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		$MngrArr = array("000096","000108","000134","000151","000211","10041909","000080","000403","000460","000461","000740","000852","000872","001951","002035","003633","012663","013169","016846","022631","10001918","10007821","10012718","10026705","10029432","000462","000405","000428","10031872","000050","000511","000022","10012414","000279","000082","000017","000109","000084","000103","001408","020195","000192","015255","10046401","10013709","10026425","10018317");		
		if(in_array($this->ucode, $MngrArr)){
			$select_empdata = "SELECT *,parentid AS contractid FROM tme_jds.tbl_ecs_dealclose_pending WHERE Mngr_Flag IN ('0','3') AND Acc_Reg_Flag != '0' ".$whereCond." ".$orderCond;
			$con_empdata    = parent::execQuery($select_empdata, $this->conn_tme);					
			$num_empdata    = parent::numRows($con_empdata);		
			if($num_empdata > 0){
				while($row = parent::fetchData($con_empdata)){
					$retArr['data'][] = $row;
				}
				$retArr['count'] = $num_empdata;
				$retArr['counttot'] = $num_empdata;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			}else{
				$retArr['counttot'] = 0;
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		}else{
			$retArr['counttot'] = 0;
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found'; // empcode doesnot have access //
		}
		return $retArr;
	}
	//EcsRequest DATA API END
	
	//DeliverySystem DATA API START
	function fetchdeliverySystem(){		
		$respArr	=	array();
		$whereCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}			
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM d_jds.tbl_delivery_signup WHERE tmecode ='".$getRowId['data']['rowId']."'".$whereCond;
			$conNum     = parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum = parent::fetchData($conNum);	
		}		
		$quExpired	=	"SELECT parentid as contractid,companyname as companyname FROM d_jds.tbl_delivery_signup WHERE tmecode='".$getRowId['data']['rowId']."' ".$whereCond." ".$orderCond." ".$limitFlag;
		$conExpired     = parent::execQuery($quExpired, $this->conn_local_slave);					
		$numPage        = parent::numRows($conExpired);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conExpired)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['count'];
		}
		return $respArr;
	}
	//DeliverySystem DATA API END
	
	//LeadComplaints DATA API START
	function fetchleadComplaints(){		
		$retArr     = array();
		$whereCond	= '';
		$ecsCond    = '';
		$andCond 	= "";
		$stateCond  = "";
		$orderCond  = 'order by a.update_date,a.allocated_date desc';
		$groupCond  = '';		
		$pending = $open = $close = $follow_up = $call_back = $repeat_call = $per_closed = $reallocated = 0;
		$disp_actions = explode(":",$this->params['srchparam']);
		$action_name  = trim(strtolower($disp_actions[0]));		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND a.companyname LIKE "%'.$this->params['srchData'].'%" ';
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
			}else if($this->params['srchwhich'] == 'ecs_actions'){
				$orderCond	=	' ORDER BY a.allocated_date DESC';
				if($action_name != "closed percentage"){
					$andCond = ' AND a.update_date > a.repeatcall_taggedon';
					if($action_name == "pending"){
						$whereCond	=	" AND a.action_flag = 0 AND a.state != 3";
					}else if($action_name == "open"){
						$whereCond	=	" AND a.action_flag IN ('1','31')";
					}else if($action_name == "close"){
						$whereCond	=	" AND a.action_flag IN ('2','32')";
					}else if($action_name == "follow up"){
						$whereCond	=	" AND a.action_flag IN ('3','33')";
					}else if($action_name == "call back"){
						$whereCond	=	" AND a.action_flag IN ('4','34')";
					}else if($action_name == "repeat call"){
						$andCond = " AND a.repeatcall_taggedon > a.update_date ";
						$whereCond	=	" AND (a.repeat_call = 1 OR a.repeat_call = 2 OR a.repeat_call = 4)".$andCond;
						$orderCond	=  "order by a.repeatcall_taggedon desc"; 
					}
				}					
			}else{
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$groupCond="group by a.parentId";
				if($this->params['srchparam']=='compname'){
					$orderCond	=	' ORDER BY companyname '.$expOrder[1];
				}else{
					$orderCond	=	' ORDER BY '.$params['srchparam'].' '.$expOrder[1];
				}
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		}else{
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT COUNT(1) AS COUNT FROM tbl_new_lead  WHERE tmecode ='".$this->ucode."'";			
			$conNum     = parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum = parent::fetchData($conNum);	
		}
		
		$qutmeAllocData	="SELECT a.companyname,a.parentid as contractid,a.action_flag,a.tmecode,a.data_city,a.tmename,a.state,a.repeatCount,a.allocated_date
		FROM d_jds.tbl_new_lead a LEFT JOIN d_jds.mktgEmpMaster b ON a.tmecode = b.mktEmpCode LEFT JOIN d_jds.mktgEmpMaster c ON b.empParent = c.mktEmpCode 
		LEFT JOIN d_jds.tbl_new_retention d ON a.parentid = d.parentid 
		WHERE  (b.Approval_flag = '1' AND b.allocId = 'RD') AND 
		(IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.allocated_date OR (d.allocated_date = '' OR d.allocated_date IS NULL))
		AND (IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.update_date OR (d.update_date = '' OR d.update_date IS NULL))  AND  a.tmecode = '".$this->ucode."' 
		AND (a.allocated_date >= '2017-11-01 00:00:00')";	    
		$qutmeAllocData	 .=	$whereCond." ".$groupCond." ".$orderCond." ".$limitFlag;
		$contmeAlloclData = parent::execQuery($qutmeAllocData, $this->conn_local_slave);					
		$numPage 		  = parent::numRows($contmeAlloclData);	
		if($numPage > 0) {
			while($res	=	parent::fetchData($contmeAlloclData)) {
				//$retArr['data'][ ]		=	$res;
				$retArr['data'][$res['contractid']]['action_flag']			=	$res['action_flag'];
				$retArr['data'][$res['contractid']]['tmecode']				=	$res['tmecode'];
				$retArr['data'][$res['contractid']]['companyname']			=	$res['companyname'];
				$retArr['data'][$res['contractid']]['data_city']			=	$res['data_city'];
				$retArr['data'][$res['contractid']]['contractid']			=	trim($res['contractid']);
				$retArr['data'][$res['contractid']]['tmename']		        =	$res['tmename'];
				$retArr['data'][$res['contractid']]['state']	            =	$res['state'];
				$retArr['data'][$res['contractid']]['repeatCount'] 			=	$res['repeatCount'];
				$retArr['data'][$res['contractid']]['allocated_date']  		=	$res['allocated_date'];
			}
		
			foreach($retArr['data'] as $keyPar=>$valPar) {
			  
			   if($valPar['companyname'] == '' || $valPar['companyname'] == 'null' || $valPar['companyname'] == 'undefined'){
				   //$selectLead_Cname = "SELECT companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$keyPar."'";
				   //$selectLead_Cname_Res	 = parent::execQuery($selectLead_Cname, $this->conn_local);					
				    $comp_params = array();
					$comp_params['data_city'] 	= $this->data_city;
					$comp_params['table'] 		= 'gen_info_id';
					$comp_params['module'] 		= $this->module;
					$comp_params['parentid'] 	= $keyPar;
					$comp_params['action'] 		= 'fetchdata';
					$comp_params['page'] 		= 'tmeNewServicesClass';
					$comp_params['fields']		= 'companyname';

					$comp_api_res		= 	array();
					$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);

				   //$selectLead_Cname_NumRows = parent::numRows($selectLead_Cname_Res);
				   $selectLead_Cname_NumRows = count($comp_api_res['results']['data'][$keyPar]);
				   if($selectLead_Cname_NumRows > 0) {
					   //$selectLead_Cname_Data =  parent::fetchData($selectLead_Cname_Res);
					   $selectLead_Cname_Data	=	$comp_api_res['results']['data'][$keyPar];
					   $valPar['compname'] = $selectLead_Cname_Data['companyname'];
				   }
			   }
			   
			   if($valPar['contractid'] != '' || $valPar['contractid'] != 'null' || $valPar['contractid'] != 'undefined') {
				   $select_TmeStatus = "SELECT * FROM d_jds.tbl_new_lead_log WHERE parentid = '".$keyPar."' AND action_flag IN ('0','1','2','3','31','32','33','34') ORDER BY update_date DESC LIMIT 1";
				  
				   $select_TmeStatus_Res	 = parent::execQuery($select_TmeStatus, $this->conn_local_slave);					
				   $select_TmeStatus_NumRows = parent::numRows($select_TmeStatus_Res);
				   if($select_TmeStatus_NumRows > 0){
					   $select_TmeStatus_Data = parent::fetchData($select_TmeStatus_Res);
					   $valPar['tme_status'] = $select_TmeStatus_Data['action_flag'];
					   $valPar['tme_status_date'] = $select_TmeStatus_Data['update_date'];
					   $valPar['tme_allocated_date'] = $select_TmeStatus_Data['insert_date'];
				   }				   
					$select_uploaded_file = "SELECT * FROM d_jds.tbl_retention_lead_transfer_uploads where parentid = '".$valPar['contractid']."' ORDER BY update_date DESC";
					$select_uploaded_file_Res	 = parent::execQuery($select_uploaded_file, $this->conn_local_slave);					
					$select_uploaded_file_NumRows = parent::numRows($select_uploaded_file_Res);				   
				    if($select_uploaded_file_NumRows > 0){
					   while($row = parent::fetchData($select_uploaded_file_Res)){
						   $valPar['uploaded_files'][] = $row;
					   }
				    }
			   }			   
				$respArr['data'][]	=	$valPar;
			}
			
			$ecsReportCountData = "SELECT a.companyname,a.parentid,a.update_date, a.allocated_date,a.data_city,a.tmename,a.action_flag,a.tmecode,a.state,a.repeat_call,a.repeatcall_taggedon,a.request_source,a.reallocate_flag 
				FROM d_jds.tbl_new_lead a LEFT JOIN d_jds.mktgEmpMaster b ON a.tmecode = b.mktEmpCode LEFT JOIN d_jds.mktgEmpMaster c ON b.empParent = c.mktEmpCode 
				LEFT JOIN d_jds.tbl_new_retention d ON a.parentid = d.parentid 
				WHERE  (b.Approval_flag = '1' AND b.allocId = 'RD') AND 
				(IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.allocated_date OR (d.allocated_date = '' OR d.allocated_date IS NULL))
				AND (IF((a.insert_date IS NOT NULL AND a.insert_date != ''),a.insert_date,a.update_date) > d.update_date OR (d.update_date = '' OR d.update_date IS NULL))  AND  a.tmecode = '".$this->ucode."' 
				AND (a.allocated_date >= '2017-11-01 00:00:00') 
				ORDER BY a.allocated_date,a.update_date DESC";
				$sel_retention_report_Res = parent::execQuery($ecsReportCountData, $this->conn_local_slave);					
				$total_report_contracts   = parent::numRows($sel_retention_report_Res);
				while($res	=	parent::fetchData($sel_retention_report_Res)){
					$datetime=new DateTime($res['repeatcall_taggedon']);
					$curr_date_pri = new DateTime($res['update_date']);
					$allocated_date = new DateTime($res['allocated_date']);
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
					}
				}
				//~ echo '-----'.$reallocated;die();
				$closed_percentage = round((($close / $total_report_contracts) * 100),1);
				
				$respArr['act_count'][0]['key']  =	"Pending";					$respArr['act_count'][0]['val']  =	"Pending : ".$pending;
				$respArr['act_count'][1]['key']  =	"Open";						$respArr['act_count'][1]['val']  =	"Open : ".$open;
				$respArr['act_count'][2]['key']  =	"Close";					$respArr['act_count'][2]['val']  =	"Close : ".$close;
				$respArr['act_count'][3]['key']  =	"Follow Up";				$respArr['act_count'][3]['val']  =	"Follow Up : ".$follow_up;
				$respArr['act_count'][4]['key']  =	"Repeat Call";				$respArr['act_count'][4]['val']  =	"Repeat Call : ".$repeat_call;
				//~ $respArr['act_count'][5]['key']  =	"Reallocated";				$respArr['act_count'][5]['val']  =	"Reallocated : ".$reallocated;
				$respArr['act_count'][5]['key']  =	"Closed Percentage";		$respArr['act_count'][5]['val']  =	"Closed Percentage : ".$closed_percentage."%";
				$respArr['act_count'][6]['key']  =	"Call Back";				$respArr['act_count'][6]['val']  =	"Call Back : ".$call_back;
				
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	'Success';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Fail';
		}
		$respArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum['COUNT'];
		}		
		return $respArr;
	}
	//LeadComplaints DATA API END
	
	//jdrrPropectData API START
	function fetchjdrrPropectData(){
		//echo "<pre>params:--";print_r($this->params);
		$retArr	   =	array();
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';
			$orderCond	=	' ORDER BY x1.insert_time DESC';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND a.compname LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					if($this->params['srchparam'] =='company_name'){
						$orderCond	=	' ORDER BY x1.'.$this->params['srchparam'].' '.$expOrder[1];
					}else if($this->params['srchparam'] =='alloctime'){
						$orderCond	=	' ORDER BY x2.update_time '.$expOrder[1];						
					}else{
						$orderCond	=	' ORDER BY x1.'.$this->params['srchparam'].' '.$expOrder[1];
					}
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			$get_count_qur = "SELECT x1.*, x2.* FROM db_justdial_products.tbl_online_campaign x1, db_justdial_products.tbl_allocation_panindia_master x2 WHERE x1.docid = x2.docid AND allocated_flag=1 AND empcode='".$this->ucode."'";			
			$get_count_qur_con	   = parent::execQuery($get_count_qur, $this->conn_idc);					
			$get_count_qur_con_num = parent::numRows($get_count_qur_con);				
			$retArr['counttot']	   =	$get_count_qur_con_num;			
			
			$query	=	"SELECT x1.*, x2.* FROM db_justdial_products.tbl_online_campaign x1, db_justdial_products.tbl_allocation_panindia_master x2 WHERE x1.docid = x2.docid AND allocated_flag=1 AND empcode='".$this->ucode."' ".$orderCond."".$limitFlag ;
			$con	= parent::execQuery($query, $this->conn_idc);					
			$num	= parent::numRows($con);
			if($num > 0) {
				while($res	=	parent::fetchData($con)) {
					$retArr['data'][]	=	$res;
				}
				$retArr['count']	=	$num;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Returned Successfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Available';
			}
			$retArr['count']		=	$num;
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Allocated';
		}
		return $retArr;
	}
	//jdrrPropectData API END
	
	//Report DATA START
	function fetchReports(){
		/*AllocationType and their Values 
		Appointment Fixed				25
		Callback						22
		Callback-contact				124
		Not interested					21
		Not the concerned person		10
		Company Closed					12
		Do not disturb					9
		Internal call					208
		Paid Client						207
		Wrong Number					7
		Not in business					98
		Follow up						24
		Interested in JustDial service	115
		interested in MastertApp		116
		Not interested in MasterApp		117
		Refixed Appointments			99
		Doctor Interested				64
		Doctor Not interested			65
		IB-callback						166
		IB-appointment					167
		IB-renewed						168
		IB-not interested				169
		Today's Call					51*/		
		/*
		array(
		"Not Interested"								=>"33"
		"Company Closed"								=>"161"
		"Follow up"										=>"36"	
		"Not in Business"								=>"162"
		"Appointment Refixed"							=>"35"
		"Interested in Master App"						=>"116"
		"Not providing Home Delivery/Table Reservation"	=>"90"
		"Not Interested in Master App"					=>"117"
		"Not Applicable for Master App"					=>"119"
		"CALL back - Contact details verified"			=>"124"
		"Voice Mail OR IVR"								=>"209"
		"Interested for JDFOS AND TR"					=>"203"
		"Interested only for JDTRS"						=>"204"
		"Interested only for JDFOS"						=>"205"
		"No Menu"										=>"127"
		"Bank Account Details Updated"					=>"256"
		"No Bank Account"								=>"257"
		"Not interested for Online Payment"				=>"259"
		"Collect New Menu"								=>"265"
		"Menu Verified"									=>"266"
		"Provides Home Delivery"						=>"273"
		"Not Interested in JD Delivery"					=>"285"
		"Interested in JD Delivery"						=>"284"
		"NOT Available"									=>"32"
		"NOT Visited"									=>"2024"
		"DATA Incorrect"								=>"31"
		"NOT the Concerned Person"						=>"40"
		"Under Renovation"								=>"164"
		"Delivered"										=>"57"
		"Business SHUTDOWN"								=>"58"
		"Address CHANGED"								=>"59"
		"Go Back"										=>"39"
		"Fake Appt"										=>"2025"
		"Bounced Collection"							=>"2026"
		"JDRR Delivered"								=>"321"
		"JD Pay QR Code Tagged"							=>"322")
		*/
	$dispositon_arr_idc = array("Not Interested"=>"33","Company Closed"=>"161","Follow up"=>"36","Not in Business"=>"162","Appointment Refixed"=>"35","Interested in Master App"=>"116","Not providing Home Delivery/Table Reservation"=>"90","Not Interested in Master App"=>"117","Not Applicable for Master App"=>"119","CALL back - Contact details verified"=>"124","Voice Mail OR IVR"=>"209","Interested for JDFOS AND TR"=>"203","Interested only for JDTRS"=>"204","Interested only for JDFOS"=>"205","No Menu"=>"127","Bank Account Details Updated"=>"256","No Bank Account"=>"257","Not interested for Online Payment"=>"259","Collect New Menu"=>"265","Menu Verified"=>"266","Provides Home Delivery"=>"273","Not Interested in JD Delivery"=>"285","Interested in JD Delivery"=>"284","NOT Available"=>"32","NOT Visited"=>"2024","DATA Incorrect"=>"31","NOT the Concerned Person" =>"40","Under Renovation" =>"164","Delivered"=>"57","Business SHUTDOWN"=>"58","Address CHANGED"=>"59","Go Back"=>"39","Fake Appt"=>"2025","Bounced Collection"=>"2026","JDRR Delivered"=>"321","JD Pay QR Code Tagged"=>"322");
		$retAppoint	= array();
		$orderCond  = '';
		$whereCond	= '';
		$date       = '';
		if($this->params['extraVals']	==	25){
			$tmename	=	"mename as tmename";
		}else {
			$tmename	=	"tmename";
		}
		if($this->params['extraVals']	==	99) {
			$whereCond=" WHERE (empcode='".$this->ucode."' or parentCode='".$this->ucode."') AND allocationType=".$this->params['extraVals']."";
		} else if(($this->params['extraVals']  ==  166) || ($this->params['extraVals']  ==  167) || ($this->params['extraVals'] == 168) || ($this->params['extraVals'] ==  169)) {
			$whereCond=" WHERE empcode='".$this->ucode."' AND allocationType=".$this->params['extraVals']."";	
		} else if($this->params['extraVals']	 == 51) {
			$date=date('Y-m-d');
			$whereCond=" WHERE empcode='".$this->ucode."' AND allocationType IN (22, 24, 124) AND (actionTime>='".$date." 00:00:00' AND actionTime<='".$date." 23:59:59')";
		} else if($this->params['extraVals']	==	25) {
			$whereCond=" WHERE parentCode='".$this->ucode."' AND allocationType=".$this->params['extraVals'];
		} else if($this->params['extraVals']	 != 64 && $this->params['extraVals']	 != 65) { 
			$whereCond=" WHERE empcode='".$this->ucode."' AND allocationType=".$this->params['extraVals'];
		} 
		$orderCond	=	" ORDER BY actionTime DESC ";
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				$currDate	=	date('Y-m-d');
				switch($this->params['srchparam']) {
					case '1week' :
						$whereCond	.=	' AND allocationTime >= "'.date('Y-m-d', strtotime($currDate.'-1 week')).' 00:00:00" AND allocationTime <= "'.date('Y-m-d').' 23:59:59"';
					break;
					case '1month' :
						$whereCond	.=	' AND allocationTime >= "'.date('Y-m-d', strtotime($currDate.'-1 month')).' 00:00:00" AND allocationTime <= "'.date('Y-m-d').' 23:59:59"';
					break;
				}
			}else{
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
			}
			$srchStr	=	$this->params['srchparam'];
		}else{
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		}else if($fullData	!=	''){
			$limitFlag	=	"";
		}else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		if($this->params['extraVals']	==	64 || $this->params['extraVals']	==	65) {
			$queryNum 	=	"SELECT count(a.contractCode) as count FROM tblContractAllocation a WHERE (a.empCode='".$this->ucode."' or a.parentCode='".$this->ucode."') AND a.allocationType =".$this->params['extraVals'].$whereCond;
			$conNum	    = parent::execQuery($queryNum, $this->conn_local);					
			$numRowsNum = parent::fetchData($conNum);
			
			$Report	=	"SELECT a.parentCode, a.empcode, a.contractCode, a.allocationType, a.allocationTime, a.actionTime, a.instruction,a.compname,a.tmename,a.pincode,a.grab_flag FROM tblContractAllocation a WHERE (a.empCode='".$this->ucode."' or a.parentCode='".$this->ucode."') AND a.allocationType =".$this->params['extraVals'].$whereCond.$orderCond.$limitFlag;
			
		} else {
			$queryNum 	=	"SELECT count(1) as count FROM tblContractAllocation".$whereCond;
			$conNum	    = parent::execQuery($queryNum, $this->conn_local);					
			$numRowsNum = parent::fetchData($conNum);
			
			$Report	=	"SELECT parentCode, contractCode, allocationType,allocationTime, actionTime, instruction,empcode,pincode,grab_flag,compname,cancel_flag as cancel_flag, diposition_date,dispositon_type, ".$tmename." FROM tblContractAllocation".$whereCond.$orderCond.$limitFlag;
		}
		$conReport = parent::execQuery($Report, $this->conn_local);					
		$numPage   = parent::numRows($conReport);
		if($numPage>0){
			$counter	=	0;
			while($rowAppoint	=	parent::fetchData($conReport)){				
				if($this->params['extraVals']	==	25 || $this->params['extraVals']	==	99){
					if(strtotime(date('Y-m-d H:i:s', time())) < strtotime($rowAppoint['actionTime'])){
						$retAppoint['data'][$counter]['showCancel']	=	1;
					}else{
						$retAppoint['data'][$counter]['showCancel']	=	0;
					}
					if($rowAppoint['dispositon_type'] != '' || $rowAppoint['dispositon_type'] != null){
						foreach($dispositon_arr_idc as $key=>$val){
							if($val == $rowAppoint['dispositon_type']){
								$retAppoint['data'][$counter]['dispositon_type'] = $key;
							}
						}
					}else{
						$retAppoint['data'][$counter]['dispositon_type'] =	'';
					}
					$retAppoint['data'][$counter]['diposition_date'] =	$rowAppoint['diposition_date'];
					$retAppoint['data'][$counter]['parentCode']		=	$rowAppoint['parentCode'];
					$retAppoint['data'][$counter]['contractid']		=	$rowAppoint['contractCode'];
					$retAppoint['data'][$counter]['allocationType']	=	$rowAppoint['allocationType'];
					$retAppoint['data'][$counter]['allocationTime']	=	$rowAppoint['allocationTime'];
					$retAppoint['data'][$counter]['actionTime']		=	$rowAppoint['actionTime'];
					$retAppoint['data'][$counter]['instruction']		=	$rowAppoint['instruction'];
					$retAppoint['data'][$counter]['empcode']			=	$rowAppoint['empcode'];
					$retAppoint['data'][$counter]['companyname']        = preg_replace('/[[:^print:]]/', '',$rowAppoint['compname']);
					$retAppoint['data'][$counter]['tmename']			=	$rowAppoint['tmename'];
					$retAppoint['data'][$counter]['pincode']			=	$rowAppoint['pincode'];
					$retAppoint['data'][$counter]['grab_flag']			=	$rowAppoint['grab_flag'];
					$retAppoint['data'][$counter]['cancel_flag']			=	$rowAppoint['cancel_flag']; // new line Added Here cancel_flag
				}else{
					$retAppoint['data'][$counter]['parentCode']		=	$rowAppoint['parentCode'];
					$retAppoint['data'][$counter]['contractid']	=	$rowAppoint['contractCode'];
					$retAppoint['data'][$counter]['allocationType']	=	$rowAppoint['allocationType'];
					$retAppoint['data'][$counter]['allocationTime']	=	$rowAppoint['allocationTime'];
					$retAppoint['data'][$counter]['actionTime']		=	$rowAppoint['actionTime'];
					$retAppoint['data'][$counter]['instruction']		=	$rowAppoint['instruction'];
					$retAppoint['data'][$counter]['empcode']			=	$rowAppoint['empcode'];
					$retAppoint['data'][$counter]['companyname']        = preg_replace('/[[:^print:]]/', '',$rowAppoint['compname']);
					$retAppoint['data'][$counter]['tmename']			=	$rowAppoint['tmename'];
					$retAppoint['data'][$counter]['pincode']			=	$rowAppoint['pincode'];
					$retAppoint['data'][$counter]['grab_flag']			=	$rowAppoint['grab_flag'];
					$retAppoint['data'][$counter]['cancel_flag']		=	$rowAppoint['cancel_flag']; // new line Added Here cancel_flag
				}				
				$counter++;
			}
			
			$retAppoint['errorCode']	=	0;
			$retAppoint['errorStatus']	='Row Id Found';
		} else {
			$retAppoint['errorCode']	=	1;
			$retAppoint['errorStatus']	=	'No Rowid Found';
		}
		$retAppoint['count']	=	$numPage;
		$retAppoint['counttot']	=	$numRowsNum['count'];
		return $retAppoint;
	}
	//Report DATA END
	
	
	public function fetchReportsInfoTimeline() {
		$retAppoint		=	array();
		$orderCond		=	'';
		$whereCond		=	'';
		$date			=	'';
		$whereCond		=	" WHERE (empcode='".$this->params['empcode']."' or parentCode='".$this->params['empcode']."')";
		$orderCond		=	" ORDER BY allocationTime ASC ";
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				$currDate	=	date('Y-m-d');
				switch($this->params['srchparam']) {
					case '1week' :
						$whereCond	.=	' AND allocationTime >= "'.date('Y-m-d', strtotime($currDate.'-1 week')).' 00:00:00" AND allocationTime <= "'.date('Y-m-d').' 23:59:59"';
					break;
					case '1month' :
						$whereCond	.=	' AND allocationTime >= "'.date('Y-m-d', strtotime($currDate.'-1 month')).' 00:00:00" AND allocationTime <= "'.date('Y-m-d').' 23:59:59"';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}	
		$setParId	=	"";
		if(isset($this->params['parid'])) {
			$setParId	=	str_replace(",","','",$this->params['parid']);
			$whereCond	.=	" AND contractcode IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		$queryNum 	=	"SELECT count(1) as count FROM tblContractAllocation".$whereCond;
		$conNum		=	parent::execQuery($queryNum, $this->conn_local);
		$numRowsNum	=	parent::fetchData($conNum);
		$Report	=	"SELECT parentCode, contractCode, allocationType,allocationTime, actionTime, instruction,empcode,compname,mename,tmename FROM tblContractAllocation".$whereCond.$orderCond.$limitFlag;
		$conReport	=	parent::execQuery($Report, $this->conn_local);
		$numPage	=	parent::numRows($conReport);
		if($numPage>0){
			$counter			=	0;
			while($rowAppoint	=	parent::fetchData($conReport)){
				$retAppoint['data'][$counter]['parentCode']		=	$rowAppoint['parentCode'];
				$retAppoint['data'][$counter]['contractid']		=	$rowAppoint['contractCode'];
				$retAppoint['data'][$counter]['allocationType']	=	$rowAppoint['allocationType'];
				$retAppoint['data'][$counter]['allocationTime']	=	$rowAppoint['allocationTime'];
				$retAppoint['data'][$counter]['actionTime']		=	$rowAppoint['actionTime'];
				$retAppoint['data'][$counter]['instruction']	=	$rowAppoint['instruction'];
				$retAppoint['data'][$counter]['empcode']		=	$rowAppoint['empcode'];
				$retAppoint['data'][$counter]['companyname']	=	$rowAppoint['compname'];
				$retAppoint['data'][$counter]['tmename']		=	$rowAppoint['tmename'];
				$retAppoint['data'][$counter]['mename']			=	$rowAppoint['mename'];
				$counter++;
			}
			$retAppoint['errorCode']	=	0;
			$retAppoint['errorStatus']	='Row Id Found';
		} else {
			$retAppoint['errorCode']	=	1;
			$retAppoint['errorStatus']	=	'No Rowid Found';
		}
		$retAppoint['count']	=	$numPage;
		$retAppoint['counttot']	=	$numRowsNum['count'];
		return $retAppoint;
	}
	//End
	
	
	public function fetchDealCloseDataReport(){
		$whereCond	=	'';
		$orderCond	=	'ORDER BY uptDate DESC';
		$retArr		=	array();
		 
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyName LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	.=	" AND a.parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		// check its from bypass if so check with both tmecode and mecode
		$bypassCondition = '';
		$sqlbypass = "SELECT * FROM online_regis.tbl_bypassgeniolite_access WHERE empcode='".$this->params['empcode']."'";
		$resbypass = parent::execQuery($sqlbypass, $this->conn_idc);
		if($resbypass && parent::numRows($resbypass) == 0){
			$bypassCondition = " a.tmecode  ='".$this->params['empcode']."' OR a.mecode ='".$this->params['empcode']."'";
		}else{
			$bypassCondition = " a.tmecode  ='".$this->params['empcode']."'";
		}
		$queryNum 	=	"SELECT a.parentid AS contractid,  MAX(c.entry_date) AS uptDate FROM payment_executives_details a LEFT JOIN payment_apportioning AS c ON (a.parentid=c.parentid AND a.version=c.version) WHERE ".$bypassCondition." ".$whereCond." GROUP BY a.parentid"; 
		$conNum		=	parent::execQuery($queryNum, $this->conn_fin);
		$numRowsNum	=	parent::numRows($conNum);
		$quDealCloseData="SELECT a.parentid AS contractid,  MAX(c.entry_date) AS uptDate FROM payment_executives_details a LEFT JOIN payment_apportioning AS c ON (a.parentid=c.parentid AND a.version=c.version) WHERE ".$bypassCondition." ".$whereCond." GROUP BY a.parentid ".$orderCond.$limitFlag;
		$conDealCloseData	=	parent::execQuery($quDealCloseData, $this->conn_fin);
		if(isset($this->paramsGET['trace']) && $this->paramsGET['trace'] == 1) {
			$dbObjLocal->trace($quDealCloseData);
		}
		$numPage	=	parent::numRows($conDealCloseData);
		if($numPage > 0) {
			$strParid	=	'';
			while($res	=	parent::fetchData($conDealCloseData)) {
				$retArr['data'][$res['contractid']]['contractid']		=	$res['contractid'];
				$retArr['data'][$res['contractid']]['uptDate']			=	$res['uptDate'];
				$strParid	.=	$res['contractid'].',';
			} 
			$paramsSend					=	array();
			$parid						=	substr($strParid,0,-1);
			$singdecode					=	json_decode($this->fetchMulticityTagging($parid),true);
			$getCompanme				=	json_decode($this->fetchCompanyname($parid),true);
			if($singdecode['errorCode']	== '0' ){ 
				foreach($singdecode['data'] As $key=>$value) {
					$retArr['data'][$key]['flag']	=	$value['flag'];
				}
			} 
			if($getCompanme['errorCode']	== '0' ){ 
				foreach($getCompanme['data'] As $key1=>$value1) {
					$retArr['data'][$key1]['companyname']	=	$value1['companyname'];
				}
			}
			$respArr	=	array();
			foreach($retArr['data'] as $keyPar=>$valPar) {
				$respArr['data'][]	=	$valPar;
			}
			$respArr['errorCode']	=	'0' ;
			$respArr['errorStatus']	=	'Row Id Found';
		}else{
			$respArr['errorCode']	=	'1' ;
			$respArr['errorStatus']	=	'No Row Id Found';
		}
		$respArr['count']			=	$numPage; //print_r($respArr); die;
		$respArr['counttot']		=	$numRowsNum; //print_r($respArr); die;
		return $respArr;
	}
	//End
	
	public function fetchCompanyname($parid) {
		$parid = explode(",",$parid);
		$par_id = '';
		foreach($parid as $k=>$v){
			$par_id .= "'".$v."',";
		}
		$par_id						=	substr($par_id,0,-1);
		//$query 		= 	"SELECT parentid,companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid IN (".$par_id.")";
		//$con     	= 	parent::execQuery($query, $this->conn_local);
		//$numRows	=	parent::numRows($con);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';
		$comp_params['module'] 		= $this->module;
		$comp_params['parentid'] 	= implode(",",$parid);
		$comp_params['action'] 		= 'fetchdata';
		$comp_params['page'] 		= 'tmeNewServicesClass';
		$comp_params['fields']		= 'parentid,companyname';

		$comp_api_res		= 	array();
		$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);
		$numRows 			=	count($comp_api_res['results']['data']);

		if($numRows > 0){
			foreach($comp_api_res['results']['data'] as  $pid =>$row){
				$retArr['data'][$row['parentid']]['companyname']	=	$row['companyname'];
			}
			$retArr['count']		=	$numRows;
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function fetchMulticityTagging($parid= "") {
		$retArr		=	array();
		if($parid	==	"")
			$parentid	=	str_replace(',',"','",$this->params['parentid']);
		else
			$parentid	=	str_replace(',',"','",$parid);
		$query 		= 	"SELECT sourceparentid as contractid FROM payment_multicity_tagging WHERE sourceparentid IN ('".$parentid."')";
		$con     	= 	parent::execQuery($query, $this->conn_fin);
		$numRows	=	parent::numRows($con);
		if($numRows > 0){
			while($row	=	parent::fetchData($con)){
				$retArr['data'][$row['contractid']]['contractid']	=	$row['contractid'];
				$retArr['data'][$row['contractid']]['flag']				=	1;
			}
			$retArr['count']		=	$numRows;
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	function fetchBounceData(){					
		$retArr		= 	array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY insert_date';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM payment_data_allocation WHERE tmecode='".$this->ucode."' AND block_flag = 0 and type=1 ".$whereCond." GROUP BY parentid ";
			$conNum		=	parent::execQuery($queryNum, $this->conn_fin);					
			$numRowsNum	=	parent::numRows($conNum);
		}
		$sqlSelect  	=	"SELECT parentid as contractid,companyname as companyname,bounced_remarks as bouncedreasons,dealclose_tmename as tmename,dealclose_mename as mename FROM payment_data_allocation WHERE tmecode='".$this->ucode."'".$whereCond." AND block_flag = 0 and type=1 GROUP BY parentid ".$orderCond." ".$limitFlag;
		$con     		= 	parent::execQuery($sqlSelect, $this->conn_fin);					
		$numPage		=	parent::numRows($con);
		if($numPage > 0) {
			while($res	=	parent::fetchData($con)) {
				$ecsTrackRepStat	=	json_decode($this->checkTrackerRep($res['contractid']),1);
				if($ecsTrackRepStat['ECS']	==	'1' && $ecsTrackRepStat['SI'] == '0') {
					$res['ecsTrackRep']	=	'ECS';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '0'){
					$res['ecsTrackRep']	=	'SI';
				} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '1'){
					$res['ecsTrackRep']	=	'ECS/SI';
				} else {
					$res['ecsTrackRep']	=	'NECS';
				}
				$res['BD']	=	1;
				$retArr['data'][]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum;
		}
		return $retArr;
	}
	//BounceData End
	
	
	//JDRIRO
	
	function JdrIro(){					
		$retArr		= 	array();
		$retArr_jd  = array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY companyname';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		//$this->ucode ='10045159';
		$params['url'] 			= 	$this->jdbox_ip_url."services/compaignPromo.php?data_city=".$this->data_city."&action=getemployeecontractdata&empcode=".$this->ucode;
		$params['formate'] 		= 	'basic';
		$content_emp 			=   $this->curlCall($params);
		$retArr_jd['jdrData']		=	json_decode($content_emp,true);
		$retArr_jd['rankingData']	=	$this->fetch_rank_details();
	
		
		if(count($retArr_jd['jdrData']['data'])>0)
		{
			foreach($retArr_jd['jdrData']['data'] as $KeyData=>$valData)
			{
				if($valData['allocated_campaign'] == 'JDRR')
				{
					$res_array['contractid'] = $valData['parentid'];
					$res_array['docid'] 	 = $valData['parentid'];
					$res_array['companyname'] = $valData['compname'];
					$retArr['data'][]		=	$res_array;
				}
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else
		{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	count($retArr_jd['jdrData']['data']);
		
		return $retArr;
	}
	
	
	function WebIro(){					
		$retArr		= 	array();
		$retArr_jd  = array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY companyname';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		//$this->ucode ='10045159';
		$params['url'] 			= 	$this->jdbox_ip_url."services/compaignPromo.php?data_city=".$this->data_city."&action=getemployeecontractdata&empcode=".$this->ucode;
		$params['formate'] 		= 	'basic';
		$content_emp 			=   $this->curlCall($params);
		$retArr_jd['jdrData']		=	json_decode($content_emp,true);
		$retArr_jd['rankingData']	=	$this->fetch_rank_details();
	
		
		if(count($retArr_jd['jdrData']['data'])>0)
		{
			foreach($retArr_jd['jdrData']['data'] as $KeyData=>$valData)
			{
				if($valData['allocated_campaign'] == 'Website')
				{
					$res_array['contractid'] = $valData['parentid'];
					$res_array['docid'] 	 = $valData['parentid'];
					$res_array['companyname'] = $valData['compname'];
					$retArr['data'][]		=	$res_array;
				}
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else
		{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	count($retArr_jd['jdrData']['data']);
		
		return $retArr;
	}
	
	function whatsappcalled(){					
		$retArr		= 	array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY companyname desc';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM online_regis.tbl_campaigndeliver WHERE tmecode='".$this->ucode."' ".$whereCond;			
			$conNum		=	parent::execQuery($queryNum, $this->conn_idc);					
			$numRowsNum	=	parent::fetchData($conNum);
		}	
		
		$sqlSelect  	=	"SELECT company_name as companyname,parentid as contractid from online_regis.tbl_campaigndelivery WHERE tme_code ='".$this->ucode."' ".$whereCond." GROUP BY parentid ".$orderCond;				
		$con		=	parent::execQuery($sqlSelect, $this->conn_idc);					
		$numPage	=	parent::numRows($con);		
		if($numPage > 0) {
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum['count'];
		}
		return $retArr;
	}
	
	
	function fetchBounceECSData(){			
		$retArr		= 	array();
		$whereCond	=	'';
		$orderCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	' AND block_flag = 0 and type=2';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		
		$queryNum 	=	"SELECT parentid as contractid,companyname as companyname,bounced_remarks as bouncedreasons,dealclose_tmename as tmename,dealclose_mename as mename FROM payment_data_allocation WHERE tmecode='".$this->ucode."' AND block_flag = 0 and type=2 ".$whereCond." GROUP BY parentid ";		
		$conNum		=	parent::execQuery($queryNum, $this->conn_fin);					
		$numRowsNum	=	parent::numRows($conNum);
		
		$sqlSelect  	=	"SELECT parentid as contractid,companyname as companyname,bounced_remarks as bouncedreasons,dealclose_tmename as tmename,dealclose_mename as mename FROM payment_data_allocation WHERE tmecode='".$this->ucode."'".$whereCond." AND block_flag = 0 and type=2 GROUP BY parentid ".$orderCond." ".$limitFlag;
		$con		=	parent::execQuery($sqlSelect, $this->conn_fin);					
		$numPage	=	parent::numRows($con);
		if($numPage > 0) {
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum;
		return $retArr;		
	}
	//BounceECSData End
	
	function fetchInstantECSData(){				
		$retArr		= 	array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY entry_date desc';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!='') {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM tbl_instant_ecs_bounce WHERE tmecode='".$this->ucode."' ".$whereCond;			
			$conNum		=	parent::execQuery($queryNum, $this->conn_idc);					
			$numRowsNum	=	parent::fetchData($conNum);
		}	
		
		$sqlSelect  	=	"SELECT parentid,SUBSTRING_INDEX(GROUP_CONCAT(parentid ORDER BY entry_date DESC),',',1) AS contractid,SUBSTRING_INDEX(GROUP_CONCAT(companyname ORDER BY entry_date DESC),',',1) AS companyname ,SUBSTRING_INDEX(GROUP_CONCAT(dealclose_tmename ORDER BY entry_date DESC),',',1) AS tmename,SUBSTRING_INDEX(GROUP_CONCAT(dealclose_mename ORDER BY entry_date DESC),',',1) AS mename,SUBSTRING_INDEX(GROUP_CONCAT(bounced_remarks ORDER BY entry_date DESC),',',1) AS bouncedreasons,SUBSTRING_INDEX(GROUP_CONCAT(source_flag ORDER BY entry_date DESC),',',1) AS source_flag,SUBSTRING_INDEX(GROUP_CONCAT(entry_date ORDER BY entry_date DESC),',',1) AS entry_date FROM tbl_instant_ecs_bounce WHERE tmecode='".$this->ucode."' ".$whereCond." GROUP BY parentid ".$orderCond.$limitFlag;				
		$con		=	parent::execQuery($sqlSelect, $this->conn_idc);					
		$numPage	=	parent::numRows($con);		
		if($numPage > 0) {
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][]		=	$res;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		$retArr['count']	=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']	=	$numRowsNum['count'];
		}
		return $retArr;
	}
	//InstantEcsData End
	
	function fetchjdrrCourierData(){							
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';
			$orderCond	=	'';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND company_name LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'company_name';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}			
			$get_count_qur = "SELECT parentid,category,company_name,data_city,pincode,location,responsibility,team_manager,tme_name,tme_code FROM `db_justdial_products`.tbl_campaigndelivery WHERE tme_code='".$this->ucode."'";
			$get_count_qur_con		=	parent::execQuery($get_count_qur, $this->conn_idc);					
			$get_count_qur_con_num	=	parent::numRows($get_count_qur_con);
			$retArr['counttot']		=	$get_count_qur_con_num;			
			
			$query	=	"SELECT parentid,category,company_name,data_city,pincode,location,responsibility,team_manager,tme_name,tme_code FROM `db_justdial_products`.tbl_campaigndelivery WHERE tme_code='".$this->ucode."' ".$orderCond."".$limitFlag ;			
			$con	=	parent::execQuery($query, $this->conn_idc);					
			$num	=	parent::numRows($con);
			
			if($num > 0) {
				while($res	=	parent::fetchData($con)) {
					$retArr['data'][]	=	$res;
				}
				$retArr['count']	=	$num;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Returned Successfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Available';
			}
			$retArr['count']		=	$num;
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Allocated';
		}
		return $retArr;
	}
	//CourierData end
	
	//AllocContract DATA API START
	function fetchAllocContracts(){	
		$res	    =	array();
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';
			$orderCond	=	' ';// group id
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'mask' :
							$whereCond	=	' AND mask=1 ';
						break;
						case 'freez' :
							$whereCond	=	' AND freez=1 ';
						break;
						case 'compnameLike' :
							$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$whereCond	=	'';					
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			//~ if($this->params['srchparam']	==	'campaignid'){
				//~ $whereCond	.=	' AND (FIND_IN_SET("2", campaignid))'; //  OR FIND_IN_SET("1", campaignid))
			//~ }
			
			if($this->params['campaign_srch']	==	1){
				if($this->params['srchparam'] == 0 || $this->params['srchparam']== '')
				{
					$whereCond	.=	' AND CONCAT(",",campaignid, ",") REGEXP ",(0|1|2|22),"'; //  OR FIND_IN_SET("1", campaignid))
					$orderCond	=	' ';
				}else
				{
					$whereCond	.=	' AND CONCAT(",",campaignid, ",") REGEXP ",('.$this->params['srchparam'].'),"'; //  OR FIND_IN_SET("1", campaignid))
					$orderCond	=	' ';
				}
			}else
			{
				if($this->params['srchparam']	==	'campaignid'){
					$whereCond	.=	' AND (FIND_IN_SET("2", campaignid))'; //  OR FIND_IN_SET("1", campaignid))
				}
			}
			
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}			
			$setParId	=	"";
			if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
				$setParId	=	str_replace(",","','",$this->params['parentid']);
				$whereCond	=	" AND parentId IN ('".$setParId."')";
				$limitFlag	=	"";
			}
			if($this->params['city']){
				$city	=	" AND data_city='".urldecode($this->params['city'])."'";
			}else{
				$city	=	"";
			} 
			if($this->params['srchparam'] != 'compnameLike'){
				$queryNum	=	"SELECT count(1) as countTot, count(distinct(parent_group)) as countGroup FROM tbl_tmesearch a WHERE tmeCode='".$getRowId['data']['rowId']."' AND parentId != '' ".$whereCond." ".$city."";
				$conNum	    = parent::execQuery($queryNum, $this->conn_local);					
				$numRowsNum = parent::fetchData($conNum);
			}
			
			//$query	=	"SELECT a.datasource_date,a.updated_date,a.parentId as contractid, a.compname, a.paidstatus, a.freez, a.lead, a.mask, a.expired, a.parentId, a.contact_details, a.contractOrig, a.callcnt, a.parent_group,a.flgduplicate as diwaliflag,a.prevAllocTmecode,a.web,a.lead, a.exp_on, a.data_city as data_city, SUBSTRING_INDEX(GROUP_CONCAT(b.allocationType ORDER by b.allocationtime desc) , ',', 1) AS allocationType,SUBSTRING_INDEX(GROUP_CONCAT(b.allocationtime ORDER by b.allocationtime desc) , ',', 1) AS allocationtime FROM tbl_tmesearch a left join d_jds.tblContractAllocation b on a.parentid = b.contractcode WHERE tmeCode='".$getRowId['data']['rowId']."' AND a.parentId != '' ".$whereCond." GROUP BY a.parentId,b.contractcode ".$orderCond."".$limitFlag;
			
			$query	=	"SELECT datasource_date,updated_date,parentId as contractid, compname, paidstatus, freez, lead, mask, expired, parentId, contact_details, contractOrig, callcnt, parent_group,flgduplicate as diwaliflag,prevAllocTmecode,web,lead,exp_on,data_city as data_city,allocationType,allocationTime as allocationtime,actionTime as actiontime , expired_on as expired_on,business_type,campaignid FROM tbl_tmesearch WHERE tmeCode='".$getRowId['data']['rowId']."' AND parentId != '' ".$whereCond." ".$city." ".$orderCond."".$limitFlag;
			$con = parent::execQuery($query, $this->conn_local);					
			$num = parent::numRows($con);
			$parentidStr	=	"";
			if($num > 0) {
				$docIdStr	=	'';
				while($resData	=	parent::fetchData($con)) {
					if($resData['campaignid']!=''){
						$resData['campaignid']		=	explode(',',$resData['campaignid']);
						$campaignid 	=	'';
						foreach($resData['campaignid'] as $k=>$v){
							$campaignid 	.=	"'".$v."',";
						}
						$campaignid		=	rtrim($campaignid,',');
						$select_finance 		=   "SELECT GROUP_CONCAT(campaignName) as campaignName FROM db_finance.payment_campaign_master where campaignid IN (".$campaignid.")";
						$select_finance_Res  	=	parent::execQuery($select_finance, $this->conn_fin);					
						$select_finance_numRows	=	parent::numRows($select_finance_Res);		
						if($select_finance_numRows > 0){
							$select_finance_data	=	parent::fetchData($select_finance_Res);
							$resData['campaignName']	=	$select_finance_data['campaignName'];
						}else{
							$resData['campaignName']	=	'';
						}
					}
					$ecsTrackRepStat	=	json_decode($this->checkTrackerRep($resData['contractid']),1);
					if($ecsTrackRepStat['ECS']	==	'1' && $ecsTrackRepStat['SI'] == '0') {
						$resData['ecsTrackRep']	=	'ECS';
					} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '0'){
						$resData['ecsTrackRep']	=	'SI';
					} else if($ecsTrackRepStat['SI'] == '1' && $ecsTrackRepStat['ECS'] == '1'){
						$resData['ecsTrackRep']	=	'ECS/SI';
					} else {
						$resData['ecsTrackRep']	=	'NECS';
					}
					if($resData['expired_on']!= null || $resData['expired_on']!=''){
						$expired_date	=	$resData['expired_on'];
						$datetime1 = new DateTime('now');
						$datetime2 = new DateTime($expired_date);
						$interval = $datetime1->diff($datetime2);
						$no_of_days	=	$interval->format('%R%a days');
						if($no_of_days	>=	30 || $no_of_days	<=	-30){
							$months		=	($no_of_days/30);
							$day_month	=	"Months";
						}
						else{
							$months		=	$no_of_days;
							$day_month	=	"days";
						}
						$resData['no_of_days']	=	round($months)." ".$day_month;
						$resData['nodays']		=	round($months);
					}else{
						$resData['no_of_days']	=	'';
						$resData['nodays']		=	-1;
					}
					$parentidStr	.=	$resData['contractid']."','";
					$res['data'][]	=	$resData;
				}
				
				$queryAllocStatus	=	"SELECT SUBSTRING_INDEX(GROUP_CONCAT(contractCode ORDER BY allocationTime DESC) , ',', 1) as contractCode, SUBSTRING_INDEX(GROUP_CONCAT(allocationType ORDER BY allocationTime DESC) , ',', 1) as allocationType, SUBSTRING_INDEX(GROUP_CONCAT(actionTime ORDER BY allocationTime DESC) , ',', 1) as actionTime, SUBSTRING_INDEX(GROUP_CONCAT(allocationTime ORDER BY allocationTime DESC) , ',', 1) as allocationTime FROM tblContractAllocation WHERE contractCode IN ('".substr($parentidStr,0,-2).") AND empCode = '".$this->ucode."'".$city." GROUP BY contractCode";
				$conAllocStatus = parent::execQuery($queryAllocStatus, $this->conn_local);					
				$numAllocStatus = parent::numRows($conAllocStatus);
				if($numAllocStatus > 0) {
					while($resAllocData	=	parent::fetchData($conAllocStatus)) {
						$res['allocData']['data'][$resAllocData['contractCode']]	=	$resAllocData;
						$res['allocData']['errorCode']	=	0;
					}
				} else {
					$res['allocData']['errorCode']	=	1;
				}
				$res['errorCode']	=	0;
				$res['errorStatus']	=	'Data found';
			} else {
				$res['errorCode']	=	1;
				$res['errorStatus']	=	'No Data Allocated';
			}
			$res['count']		=	$num;
			if($this->params['srchparam'] != 'compnameLike'){
				$res['counttot']	=	$numRowsNum['countTot'];
			}
			$res['countGroup']	=	$numRowsNum['countGroup'];
		} else {
			$res['errorCode']	=	1;
			$res['errorStatus']	=	'No Data Allocated';
		}
		return $res;
	}
	//AllocContract DATA API END
	
	//Package DATA API START
	function fetchPackgaeData(){		
		$retArr		=	array();
		$getRowId	=	json_decode($this->getRowId($this->ucode),true);
		if($getRowId['errorCode'] ==	0) {
			$whereCond	=	'';
			$orderCond	=	'';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'company' :
							$whereCond	=	' and Companyname LIKE "'.$this->params['srchData'].'%" ';
						break;
						case 'pure':
							$whereCond	=	' and Package_Flag = 1 ';
						break;
						case 'mix':
							$whereCond	=	' and Package_Flag = 2 ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'Companyname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			
			$setParId	=	"";
			if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
				$setParId	=	str_replace(",","','",$this->params['parentid']);
				$whereCond	=	" AND a.Parentid IN ('".$setParId."')";
				$limitFlag	=	"";
			}
		    $queryNum	=	"SELECT COUNT(1) as count FROM d_jds.tbl_packer_estate_details where empCode='".$this->ucode."'".$whereCond;
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local);					
			$numRowsNum	=	parent::fetchData($conNum);	
		    $query	=	"SELECT Parentid as contractid,Companyname as compname,Data_city,Package_Flag,Category_Remark FROM d_jds.tbl_packer_estate_details where empCode='".$this->ucode."'".$whereCond." ".$orderCond." ".$limitFlag;
			$con  	=	parent::execQuery($query, $this->conn_local);					
			$num	=	parent::numRows($con);	
			if($num > 0) {
				while($res	=	parent::fetchData($con)) {
					$retArr['data'][]	=	$res;
				}
				$retArr['count']	=	$num;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Returned Successfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Available';
			}
			$retArr['count']		=	$num;
			$retArr['counttot']		=	$numRowsNum['count'];
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Allocated';
		}
		return $retArr;
	}
	//Package DATA API END
	
	//RetentionData_info DATA API START
	function fetchRetentionData_information(){		
		$retArr   = array();
		$ecs_arr  = array();
		$lead_arr = array();		
		$queryNum 	=	"SELECT COUNT(1) AS COUNT FROM tbl_new_retention  WHERE tmecode ='".$this->ucode."' or escalated_details like '%".$this->ucode."%'";
		$conNum  	=	parent::execQuery($queryNum, $this->conn_local);					
		$numRowsNum	=	parent::fetchData($conNum);		
		
		$qutmeAllocData	=	"SELECT companyname as compname,parentId as contractid,update_date as entry_date,allocated_date,update_date,data_city,ecs_stop_flag,tmename,action_flag,tmecode,escalated_details,state,reactivate_flag,reactivated_on,reactivated_by,tme_comment as tme_comments,repeat_call,repeatcall_taggedon,ecs_reject_approved,reactivate_reject_comment,insert_date,allocate_by_cs,repeatCount from d_jds.tbl_new_retention WHERE parentid= '".$this->parentid."'";
		$contmeAlloclData	=	parent::execQuery($qutmeAllocData, $this->conn_local);	
		$numPage			=	parent::numRows($contmeAlloclData);	
		if($numPage > 0) {
			while($res	=	parent::fetchData($contmeAlloclData)) {
				$ecs_arr[] = $res;
				$action_flag=$res['action_flag'];
				if($action_flag == 5 || $action_flag == 23){
					$timestring_ecs=$res['update_date'];
				}else{
					$timestring_ecs=$res['allocated_date'];
				}
				
				// checking for entry in lead table //
				
				$lead_entry = "SELECT *,parentid as contractid FROM d_jds.tbl_new_lead WHERE parentid = '".$this->parentid."'";
				$conn_lead_entry =	parent::execQuery($lead_entry, $this->conn_local);	
				$num_lead_entry	 =	parent::numRows($conn_lead_entry);	
				if($num_lead_entry > 0){
					$row	=	parent::fetchData($conn_lead_entry);
					$lead_arr[] = $row;
					$action = $row['action_flag'];
					if($action == 5 || $action == 23){
						$timestring_lead = $row['update_date'];
					}else{
						$timestring_lead = $row['allocated_date'];
					}
				}
				// for checking 2 months condition //					
				if($timestring_ecs != '' && $timestring_ecs != null && $timestring_lead != '' && $timestring_lead != null){
					$ecs_date=new DateTime($timestring_ecs);
					$ecs_date->getTimestamp(); 					
					$lead_date = new DateTime($timestring_lead);
					$lead_date->getTimestamp();
					if($ecs_date > $lead_date){
						$timestring = $timestring_ecs;
						$lead_flag = 1;
					}else{
						$timestring = $timestring_lead;
						$lead_flag = 0;
					}
				}else if($timestring_ecs != '' && $timestring_ecs != null && ($timestring_lead == '' || $timestring_lead == null)){
					$timestring = $timestring_ecs;
					$lead_flag = 1;
				}else{
					$timestring = $timestring_lead;
					$lead_flag = 0;
				}
				
				if($timestring != '' && $timestring != null){		
					$datetime=new DateTime($timestring);
					$datetime->modify('+30 day');
					$datetime->format("Y-m-d");
					$curr_date_pri = new DateTime('now');
					$datetime->getTimestamp(); 
					$curr_date_pri->getTimestamp(); 
					if($curr_date_pri->getTimestamp() >= $datetime->getTimestamp()){
						$ecs_arr[0]['EcsUpdate_Flag'] = 1; //update
						$lead_arr[0]['EcsUpdate_Flag'] = 1; //update
					}else{
						$ecs_arr[0]['EcsUpdate_Flag'] = 0; //remain
						$lead_arr[0]['EcsUpdate_Flag'] = 0; //remain
					}
				}else{
					$ecs_arr[0]['EcsUpdate_Flag'] = 1; //update
					$lead_arr[0]['EcsUpdate_Flag'] = 1; //update
				}
				
				if($lead_flag == 0 || $lead_flag == 1){
					if($lead_flag == 1){
						$retArr['data'] = $ecs_arr;
					}else{
						$retArr['data'] = $lead_arr;
					}
				}	
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$lead_entry        = "SELECT *,parentid as contractid FROM d_jds.tbl_new_lead WHERE parentid = '".$this->parentid."'";
			$conn_lead_entry =	parent::execQuery($lead_entry, $this->conn_local);	
			$num_lead_entry	 =	parent::numRows($conn_lead_entry);	
			if($num_lead_entry > 0){
				$row	=	parent::fetchData($conn_lead_entry);
				$action_flag = $row['action_flag'];
				$lead_arr = $row; 
				if($action_flag == 5 || $action_flag == 23){
					$timestring = $row['update_date'];
				}else{
					$timestring = $row['allocated_date'];
				}				
				if($timestring != '' && $timestring != null){		
					$datetime=new DateTime($timestring);
					$datetime->modify('+30 day');
					$datetime->format("Y-m-d");
					$curr_date_pri = new DateTime('now');
					$datetime->getTimestamp(); 
					$curr_date_pri->getTimestamp();
					if($curr_date_pri->getTimestamp() >= $datetime->getTimestamp()){
						$lead_arr['EcsUpdate_Flag'] = 1; //update
					}else{
						$lead_arr['EcsUpdate_Flag'] = 0; //remain
					}
				}else{
					$lead_arr['EcsUpdate_Flag'] = 1; //update
				}
				$retArr['data'][] = $lead_arr;
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			}
	    } 
		$empCode = $retArr['data'][0]['tmecode'];
		if($empCode != '' && $empCode != null){
			$ActiveEmployee 	= "SELECt * FROM d_jds.mktgEmpMaster WHERE mktEmpCode = '".$this->ucode."'";
			$ActiveEmployee_Res =	parent::execQuery($ActiveEmployee, $this->conn_local);	
			$ActiveEmployee_Data =	parent::fetchData($ActiveEmployee_Res);	
			$retArr['isActive'] =  $ActiveEmployee_Data['Approval_flag'];
			$retArr['allocID'] =  $ActiveEmployee_Data['allocId'];
		}
		$retArr['count']	=	$numPage;
		$retArr['counttot']	=	$numRowsNum['COUNT'];
		return $retArr;
	}
	//RetentionData_info DATA API END
	
	//retention DATA API START
	function fetchRetentionData(){ //db_fin		
		$retArr	    =	array();
		$whereCond	=	'';
		$orderCond	=	' ORDER BY entry_date DESC';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
					break;
					case 'csstatusconfirm' :
						$whereCond	=	' AND cs_status = 1 ';
					break;
					case 'csstatusrejected' :
						$whereCond	=	' AND cs_status = 2 ';
					break;
					case 'csstatuspending' :
						$whereCond	=	' AND (cs_status != 1 AND cs_status != 2) ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM tbl_ecs_retention WHERE tmecode='".$this->ucode."' ".$whereCond." GROUP BY parentid ";
			$conNum     =	parent::execQuery($queryNum, $this->conn_fin);	
			$numRowsNum =	parent::numRows($conNum);	
		}
		
		$query 	=	"SELECT parentid as contractid,compname as companyName,retention_stop_flag,ecs_status,reason_selected,ecsstatus_date,tme_status,tmestatus_date,tme_comment,cs_status,csstatus_date,paid,retention_stop_flag_date,source,entry_date,remarks
		FROM tbl_ecs_retention WHERE tmecode='".$this->ucode."' ".$whereCond." GROUP BY parentid ".$orderCond.$limitFlag; 
		$con  =	parent::execQuery($query, $this->conn_fin);	
		$num  =	parent::numRows($con);
		if($num > 0) {
			$strParId	=	'';
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][$res['contractid']]	=	$res;
				$strParId	.=	$res['contractid'].',';
			}
			$strParId	=	str_replace(",","','",trim($strParId,','));			
			$selAllocStatus	=	"SELECT SUBSTRING_INDEX(GROUP_CONCAT(allocationType ORDER BY allocationtime DESC) , ',', 1) as allocationType,contractcode FROM tblContractAllocation WHERE contractCode IN('".$strParId."') AND empcode='".$this->ucode."' GROUP BY contractcode";			
			$conSelAlloc     =	parent::execQuery($selAllocStatus, $this->conn_local_slave);	
			$numSelAlloc     =	parent::numRows($conSelAlloc);			
			
			if($numSelAlloc > 0) {
				while($resSelAlloc	=	parent::fetchData($conSelAlloc)) {
					$retArr['data'][$resSelAlloc['contractcode']]['allocationType']	=	$resSelAlloc['allocationType'];
				}
			}
			$respArr	=	array();
			foreach($retArr['data'] as $keyPar=>$valPar) {
				$respArr['data'][]	=	$valPar;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Returned Successfully';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']		=	$num;
		if($this->params['srchparam'] != 'compnameLike'){
			$respArr['counttot']	=	$numRowsNum;
		}
		return $respArr;
	}
	//retention DATA API END
	
	//JDRatingData API START
	function fetchJDRatingData(){		
		$retArr		=	array();
		$orderCond	=	" ORDER BY compname ";
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND compname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}	
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		if($this->params['srchparam'] != 'compnameLike'){
			$queryNum 	=	"SELECT count(1) as count FROM tbl_jdratings_sales	WHERE tmecode='".$this->ucode."' ".$whereCond." GROUP BY parentid ";
			$conNum     =	parent::execQuery($queryNum, $this->conn_local_slave);	
			$numRowsNum =	parent::numRows($conNum);	
		}
		
		$SelJDRating	=	"SELECT parentid as contractid,compname,company_callcount,paid,rating,no_of_rating,cc_paid FROM tbl_jdratings_sales	WHERE tmecode='".$this->ucode."' ".$whereCond." GROUP BY parentid ".$orderCond.$limitFlag;
		$conJDRatingPage  =	parent::execQuery($SelJDRating, $this->conn_local_slave);	
		$numPage		  =	parent::numRows($conJDRatingPage);	
		if($numPage>0){
			while($rowJDRating	=	parent::fetchData($conJDRatingPage)){
				$retArr['data'][]	=	$rowJDRating;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	='Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Data Found';
		}
		$retArr['count']		=	$numPage;
		if($this->params['srchparam'] != 'compnameLike'){
			$retArr['counttot']		=	$numRowsNum;
		}
		return $retArr;
	}
	//JDRatingData API END
	
	//magazineData API START
	function fetchMagazineData(){		
		$respArr	=	array();
		$whereCond	=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				if($this->params['srchparam']=='compnameLike'){
					$orderCond	=	' ORDER BY companyname '.$expOrder[1];
				}else{
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				}
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'companyname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}		
		$getRowId	=	json_decode($this->getRowId($tmecode),true);		
		$queryNum 	=	"SELECT count(1) as count FROM allocation.tbl_magazine_data WHERE companyname!='' and empcode ='".$this->ucode."'".$whereCond;		
		$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);	
		$numRowsNum	=	parent::fetchData($conNum);	
		
		$quExpired	=	"SELECT parentid as contractid,companyname as companyname FROM allocation.tbl_magazine_data WHERE companyname!='' and empcode='".$this->ucode."' ".$whereCond." ".$orderCond." ".$limitFlag;
		$conExpired =	parent::execQuery($quExpired, $this->conn_local_slave);	
		$numPage	=	parent::numRows($conExpired);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conExpired)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']		=	$numPage;
		$respArr['counttot']	=	$numRowsNum['count'];
		return $respArr;
	}
	//magazineData API END
	
	//employeeDeclaration API START
	function checkemployeedeclaration(){		
		$retArr		= array();
		$sel 		= "select employee_code,declared_on from tbl_employee_declaration where employee_code='".$this->ucode."' ORDER BY declared_on DESC LIMIT 1";
		$res_obj  	= parent::execQuery($sel, $this->conn_local);					
		$numrow		= parent::numRows($res_obj);	
		$row 		= parent::fetchData($res_obj);
		$declared_time = date("Y-m-d H:i:s",strtotime($row['declared_on']." + 3 months"));  
		$retArr['data']['numrow'] = $numrow;
		if(date("Y-m-d H:i:s") >= $declared_time) {
			$retArr['data']['time_flag'] = 1;
		}else {
			$retArr['data']['time_flag'] = 0;
		}
		return $retArr;
	}
	//employeeDeclaration API END
	
	private function RemoteZoneCities($city){ 
		$retArr= array();
		$specialData_zone		= "SELECT cities,Zone,main_zone FROM tbl_zone_cities where cities ='".$city."'";
		$special_datacon_zone  	=	parent::execQuery($specialData_zone, $this->conn_local_slave);					
		$numRows				=	parent::numRows($special_datacon_zone);	
		if($numRows > 0) {
			$numSpecial_zone	= 	parent::fetchData($special_datacon_zone);
			$retArr['data']		=	$numSpecial_zone['main_zone'];
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not found';
		}
		return json_encode($retArr);
	}
	
	public function checkTrackerRep($contractid='') {
		if($this->params['fromContractInfo'] == "1"){
			$contractid	=	$this->params['parentid'];
			$cond		=	"WHERE parentid='".$contractid."'";
		}else{
			$contractid	=	$contractid;
			$cond		=	"WHERE parentid='".$contractid."' AND activeflag=1 AND deactiveflag=0 AND ecs_stop_flag=0";
		}
		$retArr			=	array();
		$sel_ecs_trac 	= "SELECT parentid FROM db_ecs.ecs_mandate ".$cond ;
		$conPage  	=	parent::execQuery($sel_ecs_trac, $this->conn_fin);					
		$numPage	=	parent::numRows($conPage);			
		if($numPage > 0) {
			$retArr['ECS']	=	'1';
		} else {
			$retArr['ECS']	=	'0';
		}
		$sel_si_trac = "SELECT parentid FROM db_si.si_mandate ".$cond;
		$res_si_trac =	parent::execQuery($sel_si_trac, $this->conn_fin);					
		$count	     =	parent::numRows($res_si_trac);			
		if($count > 0) {
			$retArr['SI']	=	'1';
		} else {
			$retArr['SI']	=	'0';
		}
		return json_encode($retArr);
	}
	
	public function fetchEcsFlagSI($parentid) {		
		$parentid = rtrim($parentid, ",");
		$sqlSi    = "SELECT a.parentid,if (count(1) = sum(a.ecs_stop_flag),'1','0')  as si_ecs_stop_flag FROM  db_si.si_mandate a WHERE a.parentid in ('".$parentid."') GROUP BY a.parentid";
		$resSi    = parent::execQuery($sqlSi, $this->conn_fin);
		$num	  =	parent::numRows($resSi);
		$retFlagSi	=	array();
		if($num>0){
			while($rowEcsSi = parent::fetchData($resSi)){
				$retFlagSi['data'][$rowEcsSi['parentid']]['Si_flag'] 				  = 1;
				$retFlagSi['data'][$rowEcsSi['parentid']]['Si_eflag']			 	  = 1;
				$retFlagSi['data'][$rowEcsSi['parentid']]['Si_ecs_stop_flag']		  = $rowEcsSi['si_ecs_stop_flag'];
			}
			$retFlagSi['errorCode']		=	0;
			$retFlagSi['errorStatus']	=	'Row Id Found';
		}else{
			$retFlagSi['errorCode']		=	1;
			$retFlagSi['errorStatus']	=	'No Rowid Found';
		}
		$retFlagSi['count']	=	$num;
		return json_encode($retFlagSi);		
	}
	public function fetchEcsFlag($parentid) {		
		$retFlag  =	array();
		$parentid = rtrim($parentid, ",");
		$sqlEcs   = "SELECT a.parentid,if (count(1) = sum(a.ecs_stop_flag),'1','0')  as ecs_stop_flag FROM db_ecs.ecs_mandate a WHERE a.parentid in ('".$parentid."') GROUP BY a.parentid";
		$resEcs   =	parent::execQuery($sqlEcs, $this->conn_fin);					
		$num	  =	parent::numRows($resEcs);		
		if($num>0){
			while($rowEcs = parent::fetchData($resEcs)){
				$retFlag['data'][$rowEcs['parentid']]['flag'] 				  = 1;
				$retFlag['data'][$rowEcs['parentid']]['eflag']			 	  = 1;
				$retFlag['data'][$rowEcs['parentid']]['ecs_stop_flag']		  = $rowEcs['ecs_stop_flag'];
			}
			$retFlag['errorCode']	=	0;
			$retFlag['errorStatus']	=	'Row Id Found';
		}else{
			$retFlag['errorCode']	=	1;
			$retFlag['errorStatus']	=	'No Rowid Found';
		}
		$retFlag['count']	=	$num;
		return json_encode($retFlag);
	}
	public function fetchActionFlag($parentid,$tmecode) {		
		$resAction	=	array();
		$parentid	=	rtrim($parentid, ",");
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'followup' :
						$whereCond	=	' AND action_flag = "4" AND action_flag is not null';
					break;
					case 'retain':
						$whereCond	=	' AND action_flag = "5" AND action_flag is not null';
					break;
					case 'stoptme':
						$whereCond	=	' AND action_flag = "9" AND action_flag is not null';
					break;
					default :
						$whereCond	=	' AND action_flag IN ("4","5","9")';
					break;
				}
			}
		}
		$sqlSelect1 =	"SELECT parentid,SUBSTRING_INDEX(GROUP_CONCAT(action_flag ORDER by insertdate desc) , ',', 1) as last_action FROM d_jds.tbl_ecs_retention_action_log WHERE parentid IN('".$parentid."') ".$whereCond." AND tmecode = '".$tmecode."' AND action_flag IN (4,5,9) GROUP BY parentid";
		$con   	=	parent::execQuery($sqlSelect1, $this->conn_local);					
		$num  	=	parent::numRows($con);			
		if($num>0){
			while($res	=	parent::fetchData($con)) {
				$resAction['data'][$res['parentid']]['last_action']	=	$res['last_action'];
			}
			$resAction['errorCode']	=	0;
			$resAction['errorStatus']	=	'Row Id Found';
		} else {
			$resAction['errorCode']	=	1;
			$resAction['errorStatus']	=	'No Rowid Found';
		}
		$resAction['count']	=	$num;
		return json_encode($resAction);
	}
	
	function get_curl_data($url,$param=array()){
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);
		curl_close($ch);
		return $resmsg;	
	}
	
	//Fetch Lineage Info Starts
	public function getLineageInfo(){
			$resultArr	=	array();
			if(strtolower($this->module)	==	'me'){
				$db	=	"db_dialer.";
			}else{
				$db = '';
			}
			$otheremp	=	"SELECT empcode from ".$db."tbl_employee_lineage_softDept WHERE empcode='".$this->ucode."'";
			$conemp		=	parent::execQuery($otheremp,$this->conn_LOCAL);
			$numRowsemp	=	parent::numRows($conemp);
			if($numRowsemp	>	0){//SOFTWARE
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Software employee';
			}else{// TME/ME Executive
				$insertLog	=	"SELECT * from ".$db."tbl_employee_lineage_update WHERE employee_id='".$this->ucode."'";
				$conRes		=	parent::execQuery($insertLog,$this->conn_LOCAL);
				$numRows	=	parent::numRows($conRes);
				if($numRows	>	0){
					$resultArr['data']			=	parent::fetchData($conRes);
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Lineage Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'Lineage Not Found';
				}	
			}			
			return $resultArr;			
		}
	//End
	
	//To Show Lineage Pop Up On Load Starts -- Currently this implementation has been done for TME not for ME
		public function checkUpdatedOn(){
			$resultArr		=	array();
			$getdate 		=   "SELECT DATE(updated_on) AS updated_on,DATE(penalty_submitted_on) AS penalty_submitted_on FROM updatedon_lineage WHERE empcode='".$this->ucode."'";
			$condate		=	parent::execQuery($getdate,$this->conn_LOCAL);
			$numcountRows	=	parent::numRows($condate);
			if($numcountRows	>	0){
				$res			=	parent::fetchData($condate);
				if($res['updated_on'] != null){
					$from				=	strtotime(date('Y-m-d H:i:s'));
					$to					=	strtotime($res['updated_on']);
					$diff 				= 	$from - $to;
					$differencelineage 	= 	floor($diff / (60 * 60 * 24));
					if($differencelineage >= 7){
						$resultArr['showpopup'] 	= 1;
					}else{
						$resultArr['showpopup'] 	= 0;
					}
				}else{
					$resultArr['showpopup'] 	= 1;
				}
				if($res['penalty_submitted_on'] != null){
					$from				=	strtotime(date('Y-m-d H:i:s'));
					$to					=	strtotime($res['penalty_submitted_on']);
					$diff 				= 	$from - $to;
					$differencepenalty 	= 	floor($diff / (60 * 60 * 24));
					if($differencepenalty >= 30){
						$resultArr['showpenalty'] 	= 1;
					}else{
						$resultArr['showpenalty'] 	= 0;
					}
				}else{
					$resultArr['showpenalty'] 	= 1;
				}
			}
			if($numcountRows > 0){
				$resultArr['difflineage'] 	= 	$differencelineage;
				$resultArr['diffpenalty'] 	= 	$differencepenalty;
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Found';
			}else{
				$resultArr['showpopup'] 	= 	1;
				$resultArr['showpenalty'] 	= 	1;
				$resultArr['showpopup']		= 	0;
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Data Not Found';
			}
			return $resultArr;		
		}
	//End
	
	//Lineage Insertion API Starts
	public function insertlineageDetails() {
			$res_arr	= 	array();
			$date		=	date("Y-m-d H:i:s");
			$query 		= 	"SELECT reporting_head_code from tbl_employee_lineage_update WHERE employee_id='".$this->params['empcode']."'";
			$conRes		=	parent::execQuery($query,$this->conn_LOCAL);
			$numRows	=	parent::numRows($conRes);
			if($numRows	>	0){
				$reportinghead	 =	parent::fetchData($conRes);
			}
			$is_processed 		= 	0;
			$getData_city		=	"SELECT main_zone FROM tbl_zone_cities WHERE cities='".urldecode($this->params['city'])."'";
			$getDataCityCon		=	parent::execQuery($getData_city,$this->conn_local);
			$data_city			=	parent::fetchData($getDataCityCon);
			$ins_lin_det		=	"INSERT INTO tbl_employee_lineage_update SET
										employee_id   			= 	'".$this->params['empcode']."',
										employee_name 			=	'".addslashes(stripcslashes(urldecode($this->params['empname'])))."',
										reporting_head_code		=	'".$this->params['reporting_head_code']."',
										reporting_head_name		=	'".addslashes(stripcslashes(urldecode($this->params['reporting_head_name'])))."',
										city					=	'".urldecode($this->params['city'])."',
										data_city				=	'".$data_city['main_zone']."',
										entry_date				=	'".$date."',
										city_type				=	'".urldecode($this->params['city_type'])."',
										employee_mobile			=	'".$this->params['mobile_num']."',
										off_calls				=	'".$this->params['off_sales']."',
										is_processed			=	'".$is_processed."',
										team_name				=	'".urldecode($this->params['teamname'])."',
										verification_code		=   '".$this->params['otp']."'
									ON DUPLICATE KEY UPDATE
										employee_name 			=	'".addslashes(stripcslashes(urldecode($this->params['empname'])))."',
										reporting_head_code		=	'".$this->params['reporting_head_code']."',
										reporting_head_name		=	'".addslashes(stripcslashes(urldecode($this->params['reporting_head_name'])))."',
										city					=	'".urldecode($this->params['city'])."',
										data_city				=	'".$data_city['main_zone']."',
										city_type				=	'".urldecode($this->params['city_type'])."',
										updated_on				=	'".$date."',
										employee_mobile			=	'".$this->params['mobile_num']."',
										off_calls				=	'".$this->params['off_sales']."',
										is_processed			=	'".$is_processed."',
										team_name				=	'".urldecode($this->params['teamname'])."',
										verification_code		=   '".$this->params['otp']."'";
			$con_insert_extra	=	parent::execQuery($ins_lin_det,$this->conn_LOCAL);
			$sms				=	$this->send_sms($this->params['mobile_num'],'The OTP to verify your number is '. $this->params['otp'].'.','TME');
			$ins_log_det		=	"INSERT INTO d_jds.tbl_employee_lineage_log SET
										employee_id   			= 	'".$this->params['empcode']."',
										employee_name 			=	'".addslashes(stripcslashes(urldecode($this->params['empname'])))."',
										reporting_head_code		=	'".$this->params['reporting_head_code']."',
										reporting_head_name		=	'".addslashes(stripcslashes(urldecode($this->params['reporting_head_name'])))."',
										city					=	'".urldecode($this->params['city'])."',
										data_city				=	'".$data_city['main_zone']."',
										log_date				=	'".$date."',
										city_type				=	'".urldecode($this->params['city_type'])."',
										employee_mobile			=	'".$this->params['mobile_num']."',
										off_calls				=	'".$this->params['off_sales']."',
										updated_on				=	'".$date."',
										is_processed			=	'".$is_processed."',
										team_name				=	'".urldecode($this->params['teamname'])."',
										verification_code		=   '".$this->params['otp']."'";
			$con_insert_log	=	parent::execQuery($ins_log_det,$this->conn_LOCAL);
			$this->params['data_city'] 		= 	$data_city['main_zone'];
			$this->params['date']				= 	$date;
			$jsonstringdata 			=	json_encode($this->params);
			if(urldecode($this->params['city_type']) == 'Main City'){
				$mktgcity = 1;
			}else if(urldecode($this->params['city_type']) == 'Remote City'){
				$mktgcity = 2;
			}else if(urldecode($this->params['city_type']) == 'All'){
				$mktgcity =  3;
			}
			$update_mktg 	= "UPDATE mktgEmpMaster SET allocId = '".urldecode($this->params['teamname'])."' ,city_type = '".$mktgcity."',lineage_city = '".urldecode($this->params['city'])."' WHERE mktEmpCode= '".$this->params['empcode']."'";
			$res_mktg 		=  parent::execQuery($update_mktg,$this->conn_local);
			$ins_consolog 	= "insert into online_regis.consolidated_lineage_log set empcode = '".$this->params['empcode']."' ,log_datastring= '".$jsonstringdata."' ,updated_on=  '".$date."',city ='".$this->params['data_city']."'";
			$res_consolog 	=  parent::execQuery($ins_consolog,$this->conn_idc);
			$checkempcode	= 	"SELECT * FROM updatedon_lineage where empcode='".$this->params['empcode']."'";
			$resempcode		=	parent::execQuery($checkempcode,$this->conn_LOCAL);
			if(parent::numRows($resempcode) > 0){
				$queryforupdt		=	"UPDATE updatedon_lineage SET updated_on = '".$date."' WHERE empcode='".$this->params['empcode']."'";
				$runqueryforupdt	=	parent::execQuery($queryforupdt,$this->conn_LOCAL);
			}else if(parent::numRows($resempcode) == 0){
				$queryforins		=	"INSERT INTO updatedon_lineage SET updated_on = '".$date."' , empcode='".$this->params['empcode']."'";
				$runqueryforins		=	parent::execQuery($queryforins,$this->conn_LOCAL);
			}
			//curl call to insert into hr table
			$paramsArr			=	array();				
			$postArray 							= 	array();
			$paramsArr['url'] 					= 	'http://192.168.20.237/hrmodule/hrapi/insert_emp_lineage_update';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$postArray['isJson']				=	1;
			$postArray['empcode']				=	$this->params['empcode'];
			$postArray['empname']				=	urldecode($this->params['empname']);
			$postArray['empType']				=	'TME';
			$postArray['reporting_head_code']	=	$this->params['reporting_head_code'];
			$postArray['reporting_head_name']	=	urldecode($this->params['reporting_head_name']);
			$postArray['city']					=	urldecode($this->params['city']);
			$postArray['mapped_main_city']		=	$data_city['main_zone'];
			$postArray['date']					=	$date;
			$postArray['city_type']				=	urldecode($this->params['city_type']);
			$postArray['is_processed']			=	'0';
			$postArray['off_sales']				=	$this->params['off_sales'];
			$postArray['mobile_num']			=	$this->params['mobile_num'];
			$postArray['otp']					=	$this->params['otp'];
			$postArray['team_type']				=	urldecode($this->params['teamname']);
			$postArray['insert_flag']			=	1;
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s".$postArray['empcode']);
			$paramsArr['postData'] 				= 	json_encode($postArray);
			$retVal 							= 	json_decode($this->curlCall($paramsArr),true);
			$postArrayempinfo 					= 	array();
			$postArrayempinfo['isJson']			=	1;
			$postArrayempinfo['empcode']		=	$this->params['empcode'];
			if(urldecode($this->params['city_type']) == 'Main City'){
				$postArrayempinfo['city_type'] = 1;
			}else if(urldecode($this->params['city_type']) == 'Remote City'){
				$postArrayempinfo['city_type'] = 2;
			}else if(urldecode($this->params['city_type']) == 'All'){
				$postArrayempinfo['city_type'] = 3;
			}
			$paramsArr			=	array();				
			$paramsArr['url'] 					= 	'http://192.168.20.237/hrmodule/hrapi/update_city_type';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$postArrayempinfo['team_type']		=	urldecode($this->params['teamname']);
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s".$postArrayempinfo['empcode']);
			$paramsArr['postData'] 				= 	json_encode($postArrayempinfo);
			$retValemp 							= 	json_decode($this->curlCall($paramsArr),true);
			$paramsHR							=	array();
			$paramsHR['url'] 					= 	$this->hrmodule . '/employee/fetch_employee_info/' . $this->params['reporting_head_code'];
			$paramsHR['formate'] 				= 	'basic';
			$content_emp 						= 	$this->curlCall($paramsHR);
			$resultman							=	json_decode($content_emp,true);
			$subject							=	'Status Of the Request';
			$from 								= 	"noreply@justdial.com";
			$emailtext							=	ucwords(addslashes(stripcslashes(urldecode($this->params['empname'])))).'  has sent u the request . Kindly login to genio App to Accept/reject request.';
			if($reportinghead['reporting_head_code'] != $this->params['reporting_head_code'] ){
				$updateConfirm						=	"UPDATE tbl_employee_lineage_update SET confirmed = '', confirmed_datetime= '', status = '0', is_processed = '0' WHERE employee_id='".$this->params['empcode']."'";
				$conUp								=	parent::execQuery($updateConfirm,$this->conn_LOCAL);
				$postData 							= 	array();
				$paramsArr							=	array();				
				$paramsArr['url'] 					= 	'http://192.168.20.237/hrmodule/hrapi/insert_emp_lineage_update';
				$paramsArr['formate'] 				= 	'basic';
				$paramsArr['headerJson'] 			= 	'json';
				$paramsArr['method'] 				= 	'post';
				$postData['isJson']					=	1;
				$postData['confirmed']				=	'';
				$postData['confirmed_datetime']		=	'';
				$postData['status']					=	'0';
				$postData['is_processed']			=	'0';
				$postData['empcode']				=	$this->params['empcode'];
				$postData['reporting_head_code']	=	$this->params['reporting_head_code'];
				$postData['update_flag']			=	1;
				$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s".$postData['empcode']);
				$paramsArr['postData'] 				= 	json_encode($postData);
				$retVal1 							= 	json_decode($this->curlCall($paramsArr),true);
			}
			if($con_insert_extra && ($retVal['errorCode'] == 0)){
				$res_arr['errorCode']	=	0;
				$res_arr['errorStatus']	=	'Data Inserted';
			}else{
				$res_arr['errorCode']	=	1;
				$res_arr['errorStatus']	=	'Data NOT Inserted';
			}
			return $res_arr;
		}
		
		//Function to send SMS Starts
		public function send_sms($mobileNo, $smstext, $source){
			$smstext= addslashes($smstext);   
			if($mobileNo!='' && $smstext!=''){
				$sql_sms="INSERT INTO sms_email_sending.tbl_common_intimations SET 
					mobile = '".$mobileNo."', 
					sms_text ='".addslashes($smstext)."',
					source='".$source."'";
				$result = parent::execQuery($sql_sms,$this->conn_message);
				return $result;
			}else{
				return 0;
			}
		}
		//End
		
		//Function to send Email Starts
		public function sendEmail($emailid, $from, $subject, $emailtext, $source){
			$emailtext	= addslashes($emailtext);
			if($emailid!='' && $emailtext!='' && $subject!=''){
				 $sql_email="INSERT INTO sms_email_sending.tbl_common_intimations SET 
								sender_email  = '".$from."', 
								email_id      = '".$emailid."',
								email_subject = '".$subject."',
								email_text    = '".addslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".$emailtext."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php"))."',
								source        = '".$source."'";
				$res_email = parent::execQuery($sql_email,$this->conn_message);
				return $res_email;
			}else{
				return 0;
			}
		}
		//End
		
	//End
	
	// Fetch reportees for managers to accept or reject Starts
		public function fetchreportees(){
			$resultArr		=	array();
			$fetchreportees	=	"SELECT * from tbl_employee_lineage_update WHERE reporting_head_code='".$this->params['empcode']."' ORDER BY status";
			$conRep			=	parent::execQuery($fetchreportees,$this->conn_LOCAL);
			$numRows		=	parent::numRows($conRep);
			if($numRows	>	0){
				while($row	=	parent::fetchData($conRep)){
					$resultArr['data'][]	=	$row;
				}
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Lineage Found';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Lineage Not Found';
			}				
			return $resultArr;			
		}
	//End
	
	
	//Update tables on accept or reject Starts
		public function accetRejectRequest(){
			$date			=	date('Y-m-d H:i:s');
			$resultArr		=	array();
			if($params['status'] == 1){
				$is_processed = 0;
			}else{
				$is_processed = 0;
			} // reportee reporting_head_code, status , accetRejectRequest
			$updateReportee		=	"UPDATE tbl_employee_lineage_update SET status='".$this->params['status']."',confirmed='".$this->params['confirmed']."',confirmed_datetime='".$date."',is_processed='".$is_processed."' WHERE reporting_head_code='".$this->params['reporting_head_code']."' AND employee_id='".$this->params['reportee']."'";
			$conUp				=	parent::execQuery($updateReportee,$this->conn_LOCAL);
			if($params['status']	==	1){
				$flag	=	'Accepted';
				$tag	=	'check the manager info';
			}else{
				$flag	=	'Rejected';
				$tag	=	'tag your manager';
			}
			$postData 							= 	array();
			$paramsArr							= 	array();
			$postData['isJson']					= 	1;
			$postData['confirmed']				= 	$this->params['confirmed'];
			$postData['confirmed_datetime']		= 	$date;
			$postData['status']					= 	$this->params['status'];
			$postData['is_processed']			= 	$is_processed;
			$postData['empcode']				= 	$this->params['reportee'];
			$postData['reporting_head_code']	= 	$this->params['reporting_head_code'];
			$postData['update_flag']			= 	1;
			$paramsArr['url']					=	'http://192.168.20.237/hrmodule/hrapi/insert_emp_lineage_update';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$paramsArr['auth_token'] 			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s".$postData['empcode']);
			$paramsArr['postData'] 				= 	json_encode($postData);
			$retVal 							= 	json_decode(json_encode($this->curlCall($paramsArr)),true);
			$ins_log_det						=	"INSERT INTO tbl_employee_lineage_log SET
														employee_id				=	'".$this->params['reportee']."',
														status					=	'".$this->params['status']."',
														reporting_head_code		=	'".$this->params['reporting_head_code']."',
														confirmed				=	'".$this->params['confirmed']."',
														confirmed_datetime		=	'".$date."',
														updated_on				=	'".$date."',
														is_processed			=	'".$is_processed."'";
			$con_insert_log	=	parent::execQuery($ins_log_det,$this->conn_LOCAL);
			if($conUp	>	0 && ($retVal['errorCode'] == 0)){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Updated';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Data Not Updated';
			}				
			return $resultArr;
		}
	//End
	
	
	// Api to insert employee if his/her manager not found in autosuggest Starts
		public function insertReportDetails(){
			$resultArr			=	array();
			$date				=	date('Y-m-d H:i:s');
			$insertLogReport	=	"INSERT INTO tbl_report_manager_list SET 
												empcode =	'".$this->params['empcode']."',
												status	=	'Manager Code Not Found',
												empname	=	'".addslashes(stripcslashes($this->params['empname']))."',
												created_date='".$date."'";
			$conRes				=	parent::execQuery($insertLogReport,$this->conn_LOCAL);
			if($conRes){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Inserted';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Data Not Inserted';
			}				
			return $resultArr;		
		}
	//End
	
	//On resending OTP Starts
		public function sendOTP(){
			$resultArr	=	array();
			$newOTP		=	"UPDATE tbl_employee_lineage_update SET verification_code='".$this->params['otp']."' WHERE reporting_head_code='".$this->params['managercode']."' AND employee_id='".$this->params['empcode']."'";
			$conUp		=	parent::execQuery($newOTP,$this->conn_LOCAL); 
			$postData 							= 	array();
			$paramsArr							=	array();
			$postData['isJson']					=	1;
			$postData['otp']					=	$this->params['otp'];
			$postData['empcode']				=	$this->params['empcode'];
			$postData['reporting_head_code']	=	$this->params['managercode'];
			$postData['update_flag']			=	1;
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s".$postData['empcode']);
			$paramsArr['url']					=	'http://192.168.20.237/hrmodule/hrapi/insert_emp_lineage_update';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$paramsArr['postData'] 				= 	json_encode($postData);
			$retVal 							= 	json_decode($this->curlCall($paramsArr),true);
			if($conUp && ($retVal['errorCode'] == 0)){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'OTP sent again';
				$sms	=	$this->send_sms($this->params['mobno'],'The OTP to verify your number is '.$this->params['otp'].'.','TME');
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'OTP not sent';
			}
			return $resultArr;		
		}
	//End
	
	// Verifying the OTP Starts
		public function checkOTP(){
			$resultArr	=	array();
			$getOTP		=	"SELECT verification_code from tbl_employee_lineage_update WHERE reporting_head_code='".$this->params['managercode']."' AND employee_id='".$this->params['empcode']."'";
			$conUp		=	parent::execQuery($getOTP,$this->conn_LOCAL);
			$numRows	=	parent::numRows($conUp);
			if($numRows	>	0){
				$resultArr['data']			=	parent::fetchData($conUp);
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'OTP Found';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'OTP Not Found';
			}
			return $resultArr;		
		}
	//End
	
	//Count of pending request that manager need to approve Starts
		public function countRequest(){
			$resultArr		=	array();
			$getcount 		=  "SELECT COUNT(status) as count FROM tbl_employee_lineage_update WHERE reporting_head_code='".$this->params['empcode']."' AND status='0'";
			$consel			=	parent::execQuery($getcount,$this->conn_LOCAL);
			$numcountRows	=	parent::numRows($consel);
			if($numcountRows	>	0){
				$resultArr['data']			=	parent::fetchData($consel);
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Found';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Data Not Found';
			}
			return $resultArr;		
		}
	//End
		
	//Penalty pop Up submit updation API Starts
		public function insertPenaltyUpdatedOn(){
			$resultArr	=	array();
			$insertdate = "INSERT INTO updatedon_lineage SET empcode	= '".$this->params['empcode']."',
																	penalty_submitted_on	=	NOW()
										    ON DUPLICATE KEY UPDATE penalty_submitted_on	=	NOW()";
			$condate	=	parent::execQuery($insertdate,$this->conn_LOCAL);
			if($condate	==	1){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Inserted';
			}else{
				$resultArr['errorCode']		=	1;
				$resultArr['errorStatus']	=	'Data Not Inserted';
			}
			return $resultArr;		
		}
	//End
		
	//City Autosuggest API Starts
		public function getcitylist() {
			$res_arr	= 	array();
			$qry 		= 	"SELECT DISTINCT cities FROM  tbl_zone_cities WHERE cities LIKE '".$this->params['srchData']."%' LIMIT 10";
			$res 		= 	parent::execQuery($qry,$this->conn_LOCAL);
			if(parent::numRows($res) > 0) {
				while($row = parent::fetchData($res)){
					$res_arr['data'][] 		= $row;
					$res_arr['errorCode']  	= 0;
					$res_arr['errorStatus'] = "data found";
				}
			}else {
				$res_arr['errorCode']  = 1;
				$res_arr['errorStatus']  = "No data found";
			}
			return $res_arr;
		} 
	//End
	
	
	public function getSSOInfo(){
			$paramsArr			=	array();
			$retValemp			=	array();				
			$postArrayempinfo	=	array();
			$paramsArr['url'] 					= 	'http://192.168.20.237:8080/api/getEmployee_xhr.php';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$postArrayempinfo['empcode']		=	 trim($this->params['empcode']); //trim($this->params['empcode']);
			$postArrayempinfo['textSearch']		=	4;
			$postArrayempinfo['reseller_flag']	=	1;
			$postArrayempinfo['lin_update']		=	1;
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
			$paramsArr['postData'] 				= 	json_encode($postArrayempinfo);
			$retValemp 							= 	json_decode($this->curlCall($paramsArr),true);
			return $retValemp;
	}

	//EMployee Login LOg API Starts
	public function employTimeLog (){
		 $resultArr					=	array();
		 $pwdsel   					=  "SELECT empPassword FROM mktgEmpMaster WHERE mktEmpCode='".$this->params['empcode']."' AND Approval_flag = '1'";
		 $fetchpwd 					=  parent::execQuery($pwdsel,$this->conn_local);
		 $pwddb						=  parent::fetchData($fetchpwd);
		 $resultArr['pwddb']		=  $pwddb['empPassword'];
		 $lock_det					=  "SELECT unlock_date FROM lock_user where mktEmpCode = '".$this->params['empcode']."'";
		 $fetch_undate 				=  parent::execQuery($lock_det,$this->conn_local);
		 $undate_db	  				=  parent::fetchData($fetch_undate);
		 $resultArr['undate'] 		=  $undate_db["unlock_date"];
		 $resultArr['encpassword'] 	=  md5($this->params['EmpPwd']);
		 if ($this->params['StationId'] == ''){ 
			 $resultArr['StationIdact'] = 'Not Entered';
		 } else {
			 $resultArr['StationIdact'] = $this->params['StationId'];
		 }
		 $resultArr['log_compulsion']	=	0;
		 if($this->city_indicator 	!= 'remote_city'){
			$day_number				=	date('w');
			$find_day_comp			=	"SELECT compulsion_days,no_popup FROM tbl_compulsion_data WHERE empcode = '".$this->params['empcode']."'";
			$conn_day_comp			=	parent::execQuery($find_day_comp,$this->conn_local);
			$res_day_comp			=	parent::fetchData($conn_day_comp);
			$exp_day_comp			=	explode(',',$res_day_comp['compulsion_days']);
			if($res_day_comp['no_popup'] > 0){
				if(in_array($day_number,$exp_day_comp)){
					$resultArr['log_compulsion']	=	1;
				}
			}
		}
		$resultArr['data']	=	1;
		return $resultArr;
	}
	//End 
	
	public function cityInfo (){
			$resultArr 				=	array();
			$sqlSelect 				= "SELECT alloc_time_slot FROM tbl_time_allocation ";
			$res2       			= parent::execQuery($sqlSelect,$this->conn_local);
			$payscqle 				= parent::fetchData($res2);
			$resultArr['time_slot'] = $payscqle['alloc_time_slot'];
			$sqlSelect 		  		= "SELECT rowId FROM mktgEmpMap WHERE mktEmpCode='".$this->params['empcode']."'";
			$res2       	  		= parent::execQuery($sqlSelect,$this->conn_local);
			$empRowId         		= parent::fetchData($res2);
			$resultArr['empRowId']  = $empRowId['rowId'];
			$sqlCity 		  		= "SELECT ct_name,city_id,state_id,state_name,country_id,country_name FROM city_master where ct_name='".$this->params['user_city']."'";
			$res3       	  		= parent::execQuery($sqlCity,$this->conn_local);
			$empCity         		= parent::fetchData($res3);
			$resultArr['CityInfo']  = $empCity;
			///////////////////////////////////////////////
			$query	    =  "Select tmeClass,state,secondary_allocID,level,extn FROM mktgEmpMaster WHERE mktempcode = '".$this->params['empcode']."'";
			$res_query	=   parent::execQuery($query, $this->conn_local);
			$count      =   parent::numRows($res_query);
			if($count > 0){ // make this 1 in future
				$getmktgmasterData								=	parent::fetchData($res_query);
				$resultArr['mktgEmp']['extn']					=	$getmktgmasterData['extn'];
				$resultArr['mktgEmp']['secondary_allocID']		=	$getmktgmasterData['secondary_allocID'];
				$resultArr['mktgEmp']['tmeClass']				=	$getmktgmasterData['tmeClass'];
				$resultArr['mktgEmp']['level']					=	$getmktgmasterData['level'];
				$resultArr['mktgEmp']['state']					=	$getmktgmasterData['state'];
			}else{          
				$resultArr['mktgEmp']['extn']					=	'';
				$resultArr['mktgEmp']['secondary_allocID']		=	'';
				$resultArr['mktgEmp']['tmeClass']				=	'';
				$resultArr['mktgEmp']['level']					=	'';
				$resultArr['mktgEmp']['state']					=	'';
			}
			//////////////////////////////////////////////
			return $resultArr;
	}
	//End
	
	public function mediaspeaks(){
			$resultArr 				=	array();
			$ins_rslr_popup			=   "Insert into tbl_reseller_popup set tmecode ='".$this->params['empcode']."',popup_time =now() ON DUPLICATE KEY UPDATE popup_time =now();";
		    $res_rslr_popup			=   parent::execQuery($ins_rslr_popup,$this->conn_local);
		    if($res_rslr_popup){
		    	$resultArr['erorCode']		=	0;
		    	$resultArr['errorStatus']	=	'Data Inserted';
		    }else{
		    	$resultArr['erorCode']		=	1;
		    	$resultArr['errorStatus']	=	'Data Not Inserted';
		    }
		    return $resultArr;
	}
	//End

	//getspeed links
		public function getSpeedLinks() {

		$retArr	=	array();
		$query	=	"SELECT speed_link,display_menu,extraVals FROM tbl_speed_links WHERE emp_id = '".$this->params['empcode']."'";
		$con	=	parent::execQuery($query,$this->conn_local);
		$num	=	parent::numRows($con);
		if($num > 0) {
			$resArr	=	parent::fetchData($con);
			$retArr['data'] 		=   $resArr;
            $retArr['errorCode']    =	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']    =   1;
            $retArr['errorStatus']  =   'Data Not Found';
		}
		return $retArr;
	}
	//END
	
	// set speed links
	public function setSpeedLinks() {
		$query			=	"INSERT INTO tbl_speed_links SET emp_id = '".$this->params['empcode']."' , speed_link = '".$this->params['setLink']."', extraVals = '".$this->params['extraVals']."' ON DUPLICATE KEY UPDATE speed_link = '".$this->params['setLink']."',display_menu = '".$this->params['display']."',extraVals = '".$this->params['extraVals']."'";
		$con  			= 	parent::execQuery($query,$this->conn_local);
		$retArr		 	=	json_encode(insert_return($con));
		return stripslashes($retArr);
	}
	//END
	
	//set order
		public function setSortOrder(){
		$retArr= array();
		$order='';
		$whereCond	=	'';
		$orderCond=	'';
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		
		if($this->params['linkFlag']	==	'4') {
			$sortOrderCol	=	'sort_order';
		} else if($this->params['linkFlag']	==	'5'){
			$sortOrderCol	=	'sort_order_report';
		}
		$quSortOrder="INSERT INTO tbl_speed_links  SET ".$sortOrderCol."='".$this->params['sortOrder']."',
														emp_id	=	'".$this->params['tmecode']."'
													ON DUPLICATE KEY UPDATE
													".$sortOrderCol."='".$this->params['sortOrder']."'";
		$conSortOrder	=	parent::execQuery($quSortOrder,$this->conn_local);
		$retArr['errorCode']	=	0;
		$retArr['errorStatus']	=	'Sorted';
		return json_encode($retArr);
	}
	//END
		
		//getContractCatLive
	public function getContractCatLive($contractid='') {
		$params	=	array_merge($_GET,$_POST);
		//$selCatList	=	"SELECT catidlineage FROM tbl_companymaster_extradetails WHERE parentid = '".$contractid."'";
		//$conCatList	=	parent::execQuery($selCatList, $this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id';
		$comp_params['module'] 		= $this->module;
		$comp_params['parentid'] 	= $contractid;
		$comp_params['action'] 		= 'fetchdata';
		$comp_params['page'] 		= 'tmeNewServicesClass';
		$comp_params['fields']		= 'catidlineage';

		$comp_api_res		= 	array();
		$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);
		
		//$numCont	=	parent::numRows($conCatList);
		$numCont	=	count($comp_api_res['results']['data'][$contractid]);
		if(isset($params['trace']) && $params['trace'] == 1) {
			//$dbObjLocal->trace($selCatList);
			echo "COMP API data";
			echo "<pre>";print_r($comp_params);
			echo "<br>Res :";
			echo "<pre>";print_r($comp_api_res);
		}
		if($numCont > 0 && $comp_api_res['errors']['code']=='0' ) {
			//$resCatidList	=	parent::fetchData($conCatList);
			$resCatidList	=	$comp_api_res['results']['data'][$contractid];
			if($resCatidList['catidlineage'] != '' && $resCatidList['catidlineage'] != null) {
				$catIdList	=	str_replace('/',"'",$resCatidList['catidlineage']);
				$trimCatid	=	trim($catIdList);
				
				//$querySelCatInfo = "SELECT distinct(category_name),catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$trimCatid.") ORDER BY callcount DESC";
				//$conCatinfo	=  	parent::execQuery($querySelCatInfo,$this->conn_local);

				$cat_params = array();
				$cat_params['page'] ='tmeNewServicesClass';
				$cat_params['data_city'] 	= $this->data_city;			
				$cat_params['return']		= 'catid,category_name';
				$cat_params['orderby']		= 'callcount DESC';

				$where_arr  	=	array();
				if($resCatidList['catidlineage']!=''){
					$where_arr['catid']		= str_replace('/',"",$resCatidList['catidlineage']);	
					$cat_params['where']	= json_encode($where_arr);

					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
				//$numCatInfo	=	count($cat_res_arr['results']);
				if($cat_res_arr['errorcode']=='0'  && count($cat_res_arr['results']) > 0) {
					$retArr	=	array();
					foreach($cat_res_arr['results'] as $key =>$resCatInfo) {
						$retArr['data'][]	=	$resCatInfo;
					}
					$retArr['errorCode']	=	0;
					$retArr['errorStatus']	=	'Categories Data Found';
				} else {
					$retArr['errorCode']	=	1;
					$retArr['errorStatus']	=	'Categories Data Not Found';
				}
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Categories Not Found';
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Categories Not Found';
		}
		return $retArr;
	}
	
	//End

	// Fetch Ecs Employee Code API
	public function getEcsEmpcode() {
		$retArr					=	array();
		$select_empcode 		= 	"SELECT * FROM ecs_req_mngr_list WHERE mngr_code = '".$this->params['empcode']."'";
		$select_empcode_res		=	parent::execQuery($select_empcode, $this->conn_tme);
		$num_rows 				= 	parent::numRows($select_empcode_res);
		if($num_rows > 0){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	//End
	
	//Do Dont Rules API
	public function getDoDontDetails(){
		$retArr						=	array();
		$select_penalty_details 	= 	"SELECT * FROM online_regis.tbl_employee_doDont";
		$select_penalty_details_res	=	parent::execQuery($select_penalty_details,$this->conn_idc);
		$num_penalty_details		=	parent::numRows($select_penalty_details_res);
		$i	=	1;
		if($num_penalty_details > 0){
			while($row = parent::fetchData($select_penalty_details_res)){
				$retArr['data'][$i] = $row;
				$i++;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	//End
	
	//Disposition List API Starts
	public function getDispositionList() {
		$prim_allocid 			= trim($this->params['allocid']);
		$sec_allocid 			= trim($this->params['secondaryid']);
		if($sec_allocid == ''){
			$alloc_arr = array();
			array_push($alloc_arr,$prim_allocid);
		}else {
			$alloc_arr = explode(',',$sec_allocid);
			array_push($alloc_arr,$prim_allocid);
		}		$alloc_id ='';
		foreach($alloc_arr as $alloc_key => $alloc_val) {
			$alloc_id .= "'".$alloc_val."',";
		}
		$alloc_str 		= rtrim($alloc_id,','); 
		$get_mappinfo 	= "SELECT disposition_val from tbl_disposition_mapping where allocid in(".$alloc_str.")";
		$disp_obj 		= parent::execQuery($get_mappinfo,$this->conn_local);
		$disp_val 		= '';
		while($disp_res = parent::fetchData($disp_obj)){
			$disp_val .= "'".$disp_res['disposition_val']."',";
		}
		$disp_str 			= rtrim($disp_val,',');
		$disp_arr 			= array();
		$retArr 			= array();
		if($disp_val == '') {
			$get_disp="SELECT disposition_name,disposition_value,optgroup,redirect_url FROM d_jds.tbl_disposition_info where display_flag='1' order by optgroup_priority_flag"; 
		}else {
			$get_disp="SELECT disposition_name,disposition_value,optgroup,redirect_url FROM tbl_disposition_info where disposition_value in (".$disp_str.") ORDER BY optgroup_priority_flag AND display_flag='1'";
		}
		$obj_disp			=	parent::execQuery($get_disp,$this->conn_local);
		$num 				= 	parent::numRows($obj_disp);
		$i = 0;
		if($num > 0) {
			while($res_disp = parent::fetchData($obj_disp)) {
				$disp_arr[$res_disp['optgroup']][$i]['optgroup'] = $res_disp['optgroup'];
				$disp_arr[$res_disp['optgroup']][$i]['disposition_name'] = $res_disp['disposition_name'];
				$disp_arr[$res_disp['optgroup']][$i]['disposition_value'] = $res_disp['disposition_value'];
				$disp_arr[$res_disp['optgroup']][$i]['redirect_url'] = $res_disp['redirect_url'];
				$i++;
			}
			$retArr['data'] = 	$disp_arr;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	
	public function lead_or_retention_check($parentid){		
		$repeatArr 	 = array();	
		$sel_ecs 	 = "SELECT * FROM d_jds.tbl_new_retention WHERE parentid = '".$parentid."'";
		$sel_ecs_res =	parent::execQuery($sel_ecs,$this->conn_local);		
		$ecs_numRows = 	parent::numRows($sel_ecs_res);
		
		$sel_lead = "SELECT * FROM d_jds.tbl_new_lead WHERE parentid = '".$parentid."'";
		$sel_lead_res =	parent::execQuery($sel_lead,$this->conn_local);		
		$lead_numRows = parent::numRows($sel_ecs_res);
		
		if($ecs_numRows > 0 && $lead_numRows > 0){
			$ecs_data = parent::fetchData($sel_ecs_res);
			$lead_data = parent::fetchData($sel_lead_res);
			
			if($ecs_data['update_date'] != '' && $ecs_data['update_date'] != null){
				$ecs_date = $ecs_data['update_date'];
			}else{
				$ecs_date = $ecs_data['allocated_date'];
			}
			
			if($lead_data['update_date'] != '' && $lead_data['update_date'] != null){
				$lead_date = $lead_data['update_date'];
			}else{
				$lead_date = $lead_data['allocated_date'];
			}
			
			$ecs_update_date=new DateTime($ecs_date);
			$ecs_update_date->format("Y-m-d");
			
			$lead_update_date=new DateTime($lead_date);
			$lead_update_date->format("Y-m-d");
			
			if($ecs_update_date->getTimestamp() > $lead_update_date->getTimestamp()){
				$repeatArr['tmename'] = $ecs_data['tmename'];
				$repeatArr['tmecode'] = $ecs_data['tmecode'];
				$repeatArr['lead_flag'] = '1';
			}else{
				$repeatArr['tmename'] = $lead_data['tmename'];
				$repeatArr['tmecode'] = $lead_data['tmecode'];
				$repeatArr['lead_flag'] = '0';
			}
		}else{
			if($ecs_numRows > 0){
				$ecs_data = parent::fetchData($sel_ecs_res);
				$repeatArr['tmename'] = $ecs_data['tmename'];
				$repeatArr['tmecode'] = $ecs_data['tmecode'];
				$repeatArr['lead_flag'] = '1';
			}else{
				$lead_data = parent::fetchData($sel_lead_res);
				$repeatArr['tmename'] = $lead_data['tmename'];
				$repeatArr['tmecode'] = $lead_data['tmecode'];
				$repeatArr['lead_flag'] = '0';
			}
		}
		return json_encode($repeatArr);
	} //END

	public function getlineagealldata(){
			$resArr 			= 	array();
			$cond				=	'';
			if($this->params['startdate']!= '' && $this->params['enddate']!= '' && $this->params['empcode']!= '' && $this->params['empcode']!= '111' && $this->params['data_city']!= ''){
				$cond	.="WHERE DATE(updated_on) BETWEEN '".$this->params['startdate']."' AND '".$this->params['enddate']."' and empcode= '".$this->params['empcode']."' and city= '".$this->params['data_city']."'";	
			}else if($this->params['startdate']!= '' && $this->params['enddate']!= '' &&  $this->params['empcode']!= '' && $this->params['empcode']!= '111'){
				$cond	.="WHERE DATE(updated_on) BETWEEN '".$this->params['startdate']."' AND '".$this->params['enddate']."' and empcode= '".$this->params['empcode']."'";			
			}else if($this->params['startdate']!= '' && $this->params['enddate']!= '' &&  $this->params['data_city']!= ''){
				$cond	.="WHERE DATE(updated_on) BETWEEN '".$this->params['startdate']."' AND '".$this->params['enddate']."' and city= '".$this->params['data_city']."'";						
			}else if($this->params['startdate']!= '' && $this->params['enddate']!= ''){
				$cond	.="WHERE DATE(updated_on) BETWEEN '".$this->params['startdate']."' AND '".$this->params['enddate']."'";	
			}else if($this->params['empcode']!= '' && $this->params['empcode']!= '111' && $this->params['data_city']!= ''){
				$cond	.="WHERE empcode= '".$this->params['empcode']."' and city= '".$this->params['data_city']."'";	
			}else if( $this->params['data_city']!= ''){
				$cond	.="WHERE city= '".$this->params['data_city']."'";	
			}else if($this->params['empcode']!= '' && $this->params['empcode']!= '111'){
				$cond	.="WHERE empcode= '".$this->params['empcode']."'";	
			}else{
				$cond	.="";
			}
			$limitFlag	=	'';
			if(isset($this->params['pageShow'])) {
				$pageVal	=	20*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",20";
			}else {
				$limitFlag	=	" LIMIT 0,20";
			}
			$cnt_sql 		=	"SELECT * from online_regis.consolidated_lineage_log  ".$cond." order by updated_on desc".$limitFlag;
			$cnt_res 		= 	parent::execQuery($cnt_sql,$this->conn_idc);
			$cnt_num 		= 	parent::numRows($cnt_res);
			$cnt_count 		=	"SELECT * from online_regis.consolidated_lineage_log  ".$cond."  order by updated_on desc"; 
			$cnt_count_res 	= 	parent::execQuery($cnt_count,$this->conn_idc);
			$cnt_count_num 	= 	parent::numRows($cnt_count_res);
			if($cnt_num	>	0){
				while($row	=	parent::fetchData($cnt_res)){		
					$row['log_datastring'] = json_decode($row['log_datastring'],1);
					$resArr['data'][] 		= $row;
				}
				$resArr['total'] 		= $cnt_count_num;
				$resArr['errorCode'] 	= '0';
				$resArr['errorMsg'] 	= 'success';
			}else {
				$resArr['errorCode'] 	= '1';
				$resArr['errorMsg'] 	= 'failed';
			}
			return $resArr;	
	}
	//End
	
	public function insertDeliveredCaseInfo(){		
		$resArr		=	array();
		$insert 	=  "INSERT INTO tbl_deliveredcases	SET
															parentid = '".$this->params['parentid']."',
															empcode = '".$this->params['empcode']."',
															empname = '".addslashes(stripslashes($this->params['empname']))."',
															companyname = '".addslashes(stripslashes($this->params['companyname']))."',
															need_helpto_install = '".$this->params['need_helpto_install']."',
															frame_installed = '".$this->params['frame_installed']."',
															jdrr_frame = '".$this->params['jdrr_frame']."',
															logtime=NOW()";
		$con  	= 	parent::execQuery($insert, $this->conn_tme);	
		if($con){
			$resArr['errorCode'] 	= '0';
			$resArr['errorMsg'] 	= 'Data inserted';
		}else{
			$resArr['errorCode'] 	= '1';
			$resArr['errorMsg'] 	= 'Data Not inserted';
		}	
		return $resArr;
	} //END 
	
//Upgrade Degrade Accept/Reject API
	public function send_mngr_request() {
		$retArr		= 	array();
		$gen_info 	= 	array();
		$mngr_code 	= '';
		$parentid 	= 	$this->params['parentid'];
		/**********data for logs*********/
		//$select_gen_info 			= 	"SELECT companyname FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->params['parentid']."'";
		//$conn_gen_info 				= 	parent::execQuery($select_gen_info, $this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';
		$comp_params['module'] 		= $this->module;
		$comp_params['parentid'] 	= $this->params['parentid'];
		$comp_params['action'] 		= 'fetchdata';
		$comp_params['page'] 		= 'tmeNewServicesClass';
		$comp_params['fields']		= 'companyname';

		$comp_api_res		= 	array();
		$comp_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),true);


		//$fetch_gen_info 			= 	parent::fetchData($conn_gen_info);
		$fetch_gen_info		= $comp_api_res['results']['data'][$this->params['parentid']];

		$gen_info['companyname'] 	= 	$fetch_gen_info['companyname'];
		$data['ID']         		=	$this->params['parentid'];                 		    
		$data['PUBLISH']    		=   'TME';         	  		
		$data['ROUTE']      		= 	'UPGRADE_DEGRADE REQUEST';  
		$data['DATA']['UCODE'] 		= 	$this->params['empcode'];
		$data['DATA']['EMP_NAME'] 	= 	$this->params['empname'];
		if($this->params['eventParam'] == 1){
			$data['DATA']['REQUEST'] = 'Upgraded';
		}else{
			$data['DATA']['REQUEST'] = 'Degraded';
		}
		$data['DATA']['COMP_NAME'] 		= $gen_info['companyname'];
		$data['DATA']['MNGR_RESPONSE'] 	= 0;
		$data['DATA']['UPDATED_ON'] 	= date('Y-m-d H:i:s');
		/**********data for logs*********/
		if($this->params['eventParam'] == 1){
			$mngr_code = 1;
		}else if($this->params['eventParam'] == 0){
			$mngr_code = 2;
		}else{
			$mngr_code = 0;
		}
		$selectIDCEntry 		= "SELECT * FROM tbl_ecs_dealclose_pending WHERE parentid = '".$this->params['parentid']."' AND EmpCode = '".$this->params['empcode']."'"; // checking whether TME has Allocated Appointment to ME // 
		$selectIDCEntry_Res 	=  	parent::execQuery($selectIDCEntry, $this->conn_idc);
		$selectIDCEntry_count	=	parent::numRows($selectIDCEntry_Res);
		if($selectIDCEntry_count > 0){
			$MngrReq_IDC = "UPDATE tbl_ecs_dealclose_pending SET Mngr_Flag = '".$mngr_code."',
																 updated_on = NOW(),
																 upd_deg_mngr = '".$this->params['empname']."',
																 upd_deg_mngrCode = '".$this->params['empcode']."'
																 WHERE parentid = '".$this->params['parentid']."' AND EmpCode = '".$this->params['empcode']."'";
			$MngrReq_IDC_Res =  parent::execQuery($MngrReq_IDC, $this->conn_idc);
		}
		$mngr_req_insert = "UPDATE tbl_ecs_dealclose_pending SET Mngr_Flag = '".$mngr_code."',
																 updated_on = NOW(),
																 upd_deg_mngr = '".$this->params['empname']."',
																 upd_deg_mngrCode = '".$this->params['empcode']."'
																 WHERE parentid = '".$this->params['parentid']."' AND EmpCode = '".$this->params['empcode']."'";
		$conn_mngr_req 	=  parent::execQuery($mngr_req_insert, $this->conn_tme);
		if($conn_mngr_req){
			$retArr['errorCode'] = 0;
			$retArr['errorStatus'] = 'Inserted Successfully';
		}else{
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = 'Insertion Failed';
		}
		$retArr['dataLog']		=	$data;
		return $retArr;
	}
	//END
	
	//reactivaterequest  API
	public function reactivaterequest() {
		$retArr = array();
		$update = "update tbl_new_retention set action_flag = 15,update_date=now(),reactivated_on=now() where parentid='".$this->params['parentid']."'";
		$con  	= 	parent::execQuery($update, $this->conn_local);
		if($con){
		 	$insert =  "INSERT INTO tbl_new_retention_log
						SET
						parentid = '".$this->params['parentid']."',
						tmecode = '".$this->params['empcode']."',
						tmename = '".$this->params['empname']."',
						reactivated_on=now(),
						action_flag ='15',
						insert_date = now()";
			$con  	= 	parent::execQuery($insert, $this->conn_local);	
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Updated';
		}
		else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Updated';
		}
		return $retArr;
	}
	
	public function fetchCategoryData() {
		$retArr 				= 	array();
		$paramArr 				= 	array();
		$categoryInfoDec		=	json_decode($this->getCategoryInfo(),true);
		$tmeRowId				=	json_decode($this->getRowId(),true);
		if($tmeRowId['errorCode'] == 0){
			if($categoryInfoDec['errorCode']	==	0) {
				if(isset($params['pageShow'])) {
					$pageVal	=	$this->limitVal*$params['pageShow'];
					$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
				} else {
					$limitFlag	=	" LIMIT 0,".$this->limitVal;
				}
			$query		=	"SELECT a.compname, a.paidstatus, a.freez, a.mask, a.expired, a.parentId as contractid, a.contact_details, a.contractOrig, a.callcnt, a.parent_group , a.flgduplicate as diwaliflag FROM tbl_tmesearch a JOIN db_iro.tbl_companymaster_search b ON a.parentid = b.parentid WHERE MATCH(b.catidlineage_search) AGAINST ('".$categoryInfoDec['data']['catid']."') AND a.tmeCode = '".$tmeRowId['data']['rowId']."'".$limitFlag;
				$con		=	parent::execQuery($query, $this->conn_local_slave);
				$numRows	=	parent::numRows($con);
				if($numRows > 0) {
					while($resData	=	parent::fetchData($con)) {
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
		}else{
			$retArr['errorCode']	=	2;
			$retArr['errorStatus']	=	'ROWID NOT FOUND';
		}
		return $retArr;
	}
	
	public function getCategoryInfo() {
		$retArr		=	array();
		$whereCond	=	'';
		if($this->params['catParam']	!=	'') {

			$where_arr  	=	array();
			
			if($this->params['catFlag']	==	1) {
				//$whereCond	=	" category_name = '".trim($this->params['catParam'])."'";
				$where_arr['category_name']		= implode(",",$this->params['catParam']);
			} else if($this->params['catFlag']	==	0) {
				//$whereCond	=	" catid = '".$this->params['catParam']."'";
				$where_arr['catid']		= implode(",",$this->params['catParam']);
			} else if($this->params['catFlag']	==	2) {
				//$whereCond	=	" catid IN (".$this->params['catParam'].")";
				$where_arr['catid']		= implode(",",$this->params['catParam']);
			}
			//$query		=	"SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE ".$whereCond;
			//$con		=	parent::execQuery($query, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] ='tmeNewServicesClass';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name';

			$cat_params['where']			= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			//$numRows	=	parent::numRows($con);
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0) {
				if($this->params['catFlag']	==	2) {
					foreach($cat_res_arr['results'] as $key =>$res_arr) {
						$retArr['data'][]	=	$res_arr;
					}
				} else {
					//$res	=	parent::fetchData($con);
					$retArr['data']	=	$cat_res_arr['results']['0'];
				}
				
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Sent Successsfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		} else {
			$retArr['errorCode']	=	2;
			$retArr['errorStatus']	=	'Parameters are blank';
		}
		return json_encode($retArr);
	}
	
	public function delProspectData(){
		$retArr			=	array();
		$parIdStrRep	=	str_replace(",","','",$this->params['parentid']);
		$query			=	"DELETE FROM allocation.tbl_prospectlist where parentid IN ('".$parIdStrRep."')";
		$conIns			=	parent::execQuery($query, $this->conn_local);
		if($conIns){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Deleted Successfully!';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Deletion got failed!';
		}
		return $retArr;
	}
	
	public function saveasnonpaidlog(){
		$resultArr	= 	array();
		$date       =   date("Y-m-d H:i:s");
		$insertLog  =   "Insert Into tbl_saveasnonpaid_logs SET parentid		=	'".$this->params['parentid']."',
                                                           empcode				=	'".$this->params['empcode']."',
                                                           main_table_resp		=	'".json_encode($this->params['resp171'])."',
                                                           web_response			=	'".json_encode($this->params['web_resp'])."',
                                                           jda_save_response	=	'".addslashes(stripslashes($this->params['jda_resp']))."',
                                                           insertedon   		=   '".$date."'";
        $conRes     =   parent::execQuery($insertLog,$this->conn_tme);
        if($conRes){
            $resultArr['errorCode'] =   0;
            $resultArr['errorStatus']   =   'Data Inserted';
        }else{
            $resultArr['errorCode'] =   1;
            $resultArr['errorStatus']   =   'Data Not inserted';
        }
        return $resultArr;
	}
	
	public function insertWhatsapp(){		
		$resArr		=	array();
		$insert 	=  "INSERT INTO tbl_whatsapp_log	SET
															parentid 	= '".$this->params['parentid']."',
															empcode 	= '".$this->params['empcode']."',
															empname 	= '".addslashes(stripslashes($this->params['empname']))."',
															companyname = '".addslashes(stripslashes($this->params['companyname']))."',
															TYPE 		= '".$this->params['type']."',
															STATUS 		= '".$this->params['status']."',
															number 		= '".$this->params['number']."',
															source 		= '".$this->params['source']."',
															language	=	'".addslashes(stripslashes($this->params['lang']))."',
															empType 		= '".$this->params['empType']."',
															insertedon	= NOW()";
		$con  	= 	parent::execQuery($insert, $this->conn_tme);	
		if($con){
			$resArr['errorCode'] 	= '0';
			$resArr['errorMsg'] 	= 'Data inserted';
		}else{
			$resArr['errorCode'] 	= '1';
			$resArr['errorMsg'] 	= 'Data Not inserted';
		}	
		return $resArr;
	} //END 
	
	public function insertWhatsappData(){		
		$resArr		=	array();
		$insert 	=  "INSERT INTO online_regis.tbl_whatsapp_content	SET
															parentid 	= '".$this->params['parentid']."',
															message 	= '".addslashes(stripslashes($this->params['message']))."',
															rating 		= '".$this->params['rating']."',
															imgpath 	= '".addslashes(stripslashes($this->params['imgpath']))."',
															insertdate	= NOW(),
															is_active=1";
		$con  	= 	parent::execQuery($insert, $this->conn_idc);	
		if($con){
			$resArr['errorCode'] 	= '0';
			$resArr['errorMsg'] 	= 'Data inserted';
		}else{
			$resArr['errorCode'] 	= '1';
			$resArr['errorMsg'] 	= 'Data Not inserted';
		}	
		return $resArr;
	} //END 
	
	public function fetchFreebeesEmp(){		
		$resArr		 =	array();
		$checkAccess =  "SELECT empcode FROM tbl_freebees_approval_access WHERE access_flag=1";
		$con  		 = 	parent::execQuery($checkAccess, $this->conn_local);	
		$numRows	 =	parent::numRows($con);
		if($numRows > 0){
			while($data = parent::fetchData($con)){
				$resArr['data'][] 		= $data['empcode'];
			}
			$resArr['errorCode'] 	= '0';
			$resArr['errorMsg'] 	= 'Data Found';
		}else{
			$resArr['errorCode'] 	= '1';
			$resArr['errorMsg'] 	= 'Data Not Found';
		}	
		return $resArr;
	} //END
	
	///////////////////////////COVER////////////
	
	public function docid_creator(){	
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			switch(strtoupper($this->data_city)){
				case 'MUMBAI':
					$docid = "022".$this->parentid;
					break;
					
				case 'DELHI':
					$docid = "011".$this->parentid;
					break;
					
				case 'KOLKATA':
					$docid = "033".$this->parentid;
					break;
					
				case 'BANGALORE':
					$docid = "080".$this->parentid;
					break;
					
				case 'CHENNAI':
					$docid = "044".$this->parentid;
					break;
					
				case 'PUNE':
					$docid = "020".$this->parentid;
					break;
					
				case 'HYDERABAD':
					$docid = "040".$this->parentid;
					break;
					
				case 'AHMEDABAD':
					$docid = "079".$this->parentid;
					break;
				default :
					$docid_stdcode 	= $this->stdcode_master();
					if($docid_stdcode){
						$temp_stdcode = ltrim($docid_stdcode,0);
					}
					$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
					if(in_array(strtoupper($this->data_city),$ArrCity)){
						$sqlStdCode	= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
						$resStdCode = parent::execQuery($sqlStdCode, $this->conn_local);
						$rowStdCode =  parent::fetchData($resStdCode);
						$cityStdCode	=  $rowStdCode['stdcode'];
						if($temp_stdcode == ""){
							$stdcode = ltrim($cityStdCode,0);
							$stdcode = "0".$stdcode;				
						}else{
							$stdcode = "0".$temp_stdcode;				
						}
					}else{
						$stdcode = "9999";
					}	
					$docid = $stdcode.$this->parentid;
			}
		}else{
			$docid = "022".$this->parentid;
		}
		return $docid;
	}
	
	public function stdcode_master(){
		$sql_stdcode = "SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		$res_stdcode = parent::execQuery($sql_stdcode, $this->conn_local);
		if($res_stdcode){
			$row_stdcode	=	parent::fetchData($res_stdcode);
			$stdcode 		= 	$row_stdcode['stdcode'];	
			if($stdcode[0]=='0'){
				$stdcode = $stdcode;
			}else{
				$stdcode = '0'.$stdcode;
			}
		}
		return $stdcode;
	}	

	public function checkifpropicselected(){
		$query_check_if_propic_selected     =   "select * from tbl_profile_pic_temp where parentid='".$this->parentid."'"; 
		$result_check_if_propic_selected    =   parent::execQuery($query_check_if_propic_selected,$this->conn_tme); 
		$numofrows  						=   parent::numRows($result_check_if_propic_selected);
		return $numofrows;
	}
	
	///////////////////////////COVER////////////
	
	public function add_selected_profile_pic($params){ // not in use
			$params_sess = array();
			$params_sess['url'] = PROPIC_API . 'web_services/vlc.php?docid='.$params['docid'].'&city='.$params['data_city'].'&product_id='.$params['product_id'].'&mode=pp&user_id='.$_SESSION['ucode'].'&module=TME';
			//echo $params_sess['url']."<br>";
			$params_sess['formate'] = 'basic';
			$content = $this->curlCall($params_sess);
			//echo $content."<br>";
			return $content;
	}
	
	 public function setImageProPic(){
		 	$status = '';
		 	$reason = '';
		 	$resultArr	=	0;
		 	if($this->params['content'] == 1){
		 		$status = 'Successful';
		 		$reason = $this->params['content'];
		 		$resultArr	=	1;
		 	}else{
		 		if($this->params['content'] == "Error"){
			 		$status = 'UnSuccessful';
			 		$reason = 'No Response from Server';
			 	}else{
			 		$status = 'UnSuccessful';
			 		$reason = $this->params['content'];
			 	}
			 	$resultArr	=	0;
		 	} 
           $query_insert_temp_tbl="INSERT INTO tbl_profile_pic_temp 
                                                               SET 
                                                               parentid                        =       '".$this->params['parentid']."', 
                                                               docid                           =       '".$this->params['docid']."', 
                                                               product_id                      =       '".$this->params['product_id']."', 
                                                               city                            =       '".$this->params['data_city']."', 
                                                               tagged_by                       =       '".$this->params['empcode']."', 
                                                               type                            =       '".$this->params['type']."', 
                                                               date_of_entry           		   =       '".date('Y-m-d H:i:s')."' 
                                                                
			    ON DUPLICATE KEY UPDATE 
                                                               docid                           =       '".$this->params['docid']."', 
                                                               product_id                      =       '".$this->params['product_id']."', 
                                                               city                            =       '".$this->params['data_city']."', 
                                                               tagged_by                       =       '".$this->params['empcode']."', 
                                                               date_of_entry           		   =       '".date('Y-m-d H:i:s')."'"; 
                                                            
           $con_insert_temp_tbl            =       parent::execQuery($query_insert_temp_tbl,$this->conn_tme);
            
           $query_insert_log_tbl="INSERT INTO tbl_profile_pic_log 
                                                           SET 
                                                                   parentid                        =       '".$this->params['parentid']."', 
                                                                   docid                           =       '".$this->params['docid']."', 
                                                                   city                            =       '".$this->params['data_city']."', 
                                                                   product_id                      =       '".$this->params['product_id']."', 
                                                                   tagged_by                       =       '".$this->params['empcode']."', 
                                                                   date_of_entry           		   =       '".date('Y-m-d H:i:s')."', 
                                                                   type                            =       '".$this->params['type']."', 
                                                                   status                          =       '".$status."', 
                                                                   reason                          =       '".$reason."'"; 
           $con_insert_log_tbl             =       parent::execQuery($query_insert_log_tbl,$this->conn_tme); 
           return $resultArr;
	}
	
	///////////////////////////COVER////////////
	
	function fetchrestaurantdealsoffer(){
		$respArr	=	array();
		$whereCond	=	'';		
		if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
			if($this->params['srchwhich'] == 'where') {
				switch($this->params['srchparam']) {
					case 'compnameLike' :
						$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
					break;
				}
			} else {
				$expOrder	=	explode('-',$this->params['srchwhich']);
				$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
				$whereCond	=	'';
			}
			$srchStr	=	$this->params['srchparam'];
		} else {
			$srchStr	=	'compname';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	$this->limitVal*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
		} else if($fullData	!=	''){
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,".$this->limitVal;
		}
		
		$setParId	=	"";
		if(isset($this->params['parentid']) && $this->params['parentid']!=null) {
			$setParId	=	str_replace(",","','",$this->params['parentid']);
			$whereCond	=	" AND parentid IN ('".$setParId."')";
			$limitFlag	=	"";
		}
		$data_city 				= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote'); 
		if($data_city == 'remote'){
			$cityStr = '';
		}else{
			$cityStr = " AND city='".$data_city."'";
		}
		$queryNum	=	"SELECT count(1) as count  FROM online_regis.tbl_offer_rest WHERE empcode='".$this->ucode."'"."".$cityStr;
		$conNum  	=	parent::execQuery($queryNum, $this->conn_idc);					
		$numRowsNum	=	parent::fetchData($conNum);
		$quRestuarant	=	"SELECT parentid as contractid,companyname FROM online_regis.tbl_offer_rest WHERE empcode='".$this->ucode."'"."".$cityStr.$orderCond." ".$limitFlag;
		$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_idc);
		$numPage	    =	parent::numRows($conRestuarant);
		if($numPage > 0) {			
			while($res	=	parent::fetchData($conRestuarant)) {
				$respArr['data'][]		=	$res;
			}
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Data Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Found';
		}
		$respArr['count']	=	$numPage;
		$respArr['counttot']	=	$numRowsNum['count'];
		return $respArr;
	}
	
	function fetchsuperhotdata(){
		$respArr	=	array();
		$getRowId	=	json_decode($this->getRowId($this->params['empcode']),true);
		if($getRowId['errorCode']	==	0) {
			$whereCond	=	'';		
			$orderCond 	= 'ORDER BY d.formsubmitdate DESC';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			$queryNum	=	"SELECT count(1) as count
								FROM d_jds.tbl_tmesearch a LEFT JOIN db_iro.appointment AS d ON a.parentid=d.Parentid WHERE (d.jdyp&32=32 OR d.jdyp&16=16) AND a.tmeCode='".$getRowId['data']['rowId']."' AND d.formsubmitdate >= '2018-11-26 00:00:00' ".$whereCond." GROUP BY a.parentid";
			$conNum  	=	parent::execQuery($queryNum, $this->conn_local_slave);					
			$numRowsNum	=	parent::numRows($conNum);
			$quRestuarant	=	"SELECT a.compname as companyname,a.allocationtime AS alloctime,a.allocationType AS alloctype,a.data_source,a.parentid AS contractid,
								a.updated_date,a.datasource_date,d.app_date,d.jdyp,substring_index(group_concat(d.comments order by d.formsubmitdate desc),',',1) AS comments,substring_index(group_concat(d.app_date order by d.formsubmitdate desc),',',1) AS app_data,substring_index(group_concat(d.classcategory order by d.formsubmitdate desc),',',1) AS class,substring_index(group_concat(d.company order by d.formsubmitdate desc),',',1) AS company,substring_index(group_concat(d.iro order by d.formsubmitdate desc),',',1) as iro  FROM d_jds.tbl_tmesearch a LEFT JOIN db_iro.appointment AS d ON a.parentid=d.Parentid WHERE  (d.jdyp&32=32 OR d.jdyp&16=16) AND a.tmeCode='".$getRowId['data']['rowId']."' AND d.formsubmitdate >= '2018-11-26 00:00:00' ".$whereCond." GROUP BY a.parentid ".$orderCond." ".$limitFlag;
			$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_local_slave);
			$numPage	    =	parent::numRows($conRestuarant);
			if($numPage > 0) {			
				while($res	=	parent::fetchData($conRestuarant)) {
					$respArr['data'][]		=	$res;
				}
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	'Data Found';
			} else {
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	'Data Not Found';
			}
			$respArr['count']	=	$numPage;
			$respArr['counttot']	=	$numRowsNum;
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'Data Not Allocated to you';
		}
		return $respArr;
	}
	
	function fetchsuperhotdataNew(){
			$respArr	=	array();
			$whereCond	=	'';		
			$orderCond 	= 'ORDER BY inserted_on DESC';
			if(isset($this->params['srchparam']) && $this->params['srchparam'] != null && $this->params['srchparam'] != 'all') {
				if($this->params['srchwhich'] == 'where') {
					switch($this->params['srchparam']) {
						case 'compnameLike' :
							$whereCond	=	' AND companyname LIKE "%'.$this->params['srchData'].'%" ';
						break;
					}
				} else {
					$expOrder	=	explode('-',$this->params['srchwhich']);
					$orderCond	=	' ORDER BY '.$this->params['srchparam'].' '.$expOrder[1];
					$whereCond	=	'';
				}
				$srchStr	=	$this->params['srchparam'];
			} else {
				$srchStr	=	'compname';
			}
			if(isset($this->params['pageShow'])) {
				$pageVal	=	$this->limitVal*$this->params['pageShow'];
				$limitFlag	=	" LIMIT ".$pageVal.",".$this->limitVal;
			} else if($fullData	!=	''){
				$limitFlag	=	"";
			} else {
				$limitFlag	=	" LIMIT 0,".$this->limitVal;
			}
			//~ $this->params['empcode'] = '000849';
			$queryreadFlag	=	"SELECT count(1) as countRedFlag FROM tme_jds.tbl_superhotdata WHERE empcode='".$this->params['empcode']."' AND red_flag=1";
			$conFlag  		=	parent::execQuery($queryreadFlag, $this->conn_tme);				
			$numconFlag		=	parent::fetchData($conFlag);
			
			$queryNum		=	"SELECT count(1) as count FROM tme_jds.tbl_superhotdata WHERE empcode='".$this->params['empcode']."'";
			$conNum  		=	parent::execQuery($queryNum, $this->conn_tme);				
			$numRowsNum		=	parent::fetchData($conNum);
			$quRestuarant	=	"SELECT parentid as contractid,companyname,inserted_on,empcode,empname,red_flag,irocode,ironame,updatetime FROM tme_jds.tbl_superhotdata WHERE empcode='".$this->params['empcode']."' ".$whereCond." ".$orderCond." ".$limitFlag;
			$conRestuarant 	=	parent::execQuery($quRestuarant, $this->conn_tme);
			$numPage	    =	parent::numRows($conRestuarant);
			if($numPage > 0) {			
				while($res	=	parent::fetchData($conRestuarant)) {
					$respArr['data'][]		=	$res;
				}
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	'Data Found';
			} else {
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	'Data Not Found';
			}
			$respArr['count']		=	$numPage;
			$respArr['counttot']	=	$numRowsNum['count'];
			$respArr['readCount']	=	$numconFlag['countRedFlag'];
			return $respArr;
	}
	
	public function updatesuperhotdata(){ 
		$retArr			=	array();
		//~ $this->params['empcode'] = '000849';
		$update_qur 	= 	"UPDATE tbl_superhotdata SET red_flag = 1,updatetime=NOW() WHERE empcode='".$this->params['empcode']."'";
		$update_qur_con	= 	parent::execQuery($update_qur, $this->conn_tme);			
		if($update_qur_con){
			$retArr['errorCode']  = 0;
			$retArr['errorStatus']  = "Updated Success";
		}else{
			$retArr['errorCode']  = 1;
			$retArr['errorStatus']  = "Updated fail";
		}
		return $retArr;
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
