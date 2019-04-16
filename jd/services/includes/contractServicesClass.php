<?php
class contractServicesClass extends DB
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
		$noparentid 	= trim($params['noparentid']);
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
		if((trim($parentid)=='') && ($noparentid !=1)){
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
		$this->conn_national	= $db['db_national'];
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
		$this->cs_url 						=	$this->urldetails['url'];
	}
	
	public function tempContract() {
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_obj 						= new MongoClass();
			$mongo_inputs 					= array();
			$mongo_inputs['module']       	= 'TME';
			$mongo_inputs['parentid']       = $this->params['parentid'];
			$mongo_inputs['data_city']      = $this->params['data_city'];
			$mongo_inputs['table']          = json_encode(array(
				"tbl_business_temp_data"=>"contractid,catIds,categories"
			));
			if(!empty($this->params['parentid'])){
				$resData = $mongo_obj->getTableData($mongo_inputs);		
				$numRows = count($resData);
			}
		}else{
			$checksql 			= "SELECT contractid,catIds,categories FROM tbl_business_temp_data WHERE contractid = '".$this->params['parentid']."'";
			$sqlobj 			= parent::execQuery($checksql, $this->conn_tme);
			$numRows			= parent::numRows($conQuery);
			if($numRows > 0) {
				$resData 			= parent::fetchData($sqlobj);
			}
		}
		$retArr		=	array();
		if($numRows > 0) {
			$retArr['data']		=	$resData;
			$retArr['count']	=	$numRows;
		} else {
			$retArr['count']	=	0;
		}
		return $retArr;
	}
	
	public function actEcsRetention() {
		$retArr		=	array();
		$query		=	"UPDATE tbl_ecs_retention SET retention_stop_flag = '".$this->params['flag']."',retention_stop_flag_date=NOW() WHERE parentid='".$this->params['parentid']."'";
		$con		=	parent::execQuery($query,$this->conn_fin);
		if($con){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Updated';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Updated';
		}
		return $retArr;
	}
	
	public function searchCompanyByNum() {
		if($this->params['phone']	==	'') {
			$srchWhere	=	" parentid	=	'".$this->params['parentid']."'";
		} else {
			$srchWhere	=	" (MATCH(a.contact_details) AGAINST ('".$this->params['phone']."'  IN BOOLEAN MODE))";
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	20*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",20";
		} else if($this->params['fullData']	!=	'') {
			$limitFlag	=	"";
		} else {
			$limitFlag	=	" LIMIT 0,20";
		}
		$queryCompSrch	=	"SELECT a.compname,a.parentid as contractid,a.paidstatus,a.freez,a.mask,a.contact_details,a.tmeCode FROM tbl_tmesearch a WHERE ".$srchWhere." GROUP BY a.parentid ORDER BY a.paidstatus DESC".$limitFlag;
		$conCompSrch	=	parent::execQuery($queryCompSrch,$this->conn_local);
		$numCompSrch	=	parent::numRows($conCompSrch);
		if($numCompSrch > 0) {
			while($resCompSrch	=	parent::fetchData($conCompSrch)) {
				$retArr['data'][]	=	$resCompSrch;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$querySrchTemp 	= 	"SELECT a.companyName as compname,a.parentid as contractid,a.freez,a.mask,a.paid as paidstatus,a.contact_details FROM c2s_nonpaid a WHERE ".$srchWhere." GROUP BY a.parentid".$limitFlag;
			$conSrchTemp	=	parent::execQuery($querySrchTemp,$this->conn_local);
			$numCompSrch	=	parent::numRows($conSrchTemp);
			if($numCompSrch > 0) {
				while($resSrchTemp	=	parent::fetchData($conSrchTemp)) {
					$retArr['data'][]	=	$resSrchTemp;
				}
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			} else {
				$querySrchComTemp	=	"SELECT parentid as contractid,a.companyname as compname,a.userid as mktEmpCode,a.contact_details,c.empName FROM tme_jds.tbl_business_temp_compsrch a LEFT JOIN mktgEmpMaster c ON (a.userid = c.mktEmpCode) WHERE ".$srchWhere.$limitFlag;
				$conSrchComTemp	=	parent::execQuery($querySrchComTemp,$this->conn_local);
				$numCompSrch	=	parent::numRows($conSrchComTemp);
				if($numCompSrch > 0) {
					while($resSrchComTemp	=	parent::fetchData($conSrchComTemp)) {
						$retArr['data'][]	=	$resSrchComTemp;
					}
					$retArr['errorCode']	=	0;
					$retArr['errorStatus']	=	'Data Found';
				} else {
					$retArr['errorCode']	=	1;
					$retArr['errorStatus']	=	'Data Not Found';
				}
			}
		}
		$retArr['count']	=	$numCompSrch;
		return $retArr;
	}
	
	public function fetchEcsRetentionData() {
		$retArr				=	array();
		$parentidStr		=	'';
		$queryEcsRetention	=	"SELECT parentid,companyname FROM tbl_companymaster_search WHERE  MATCH (phone_search) AGAINST ('" . $this->params['ecs_contact'] . "' IN BOOLEAN MODE)"; 
		$conPageEREtention	=	parent::execQuery($queryEcsRetention,$this->conn_iro);
		$numPage			=	parent::numRows($conPageEREtention);
		if($numPage > 0) {
			while($res	=	parent::fetchData($conPageEREtention)) {
				$retArr['data'][$res['parentid']]['parentid']			=	$res['parentid'];
				$retArr['data'][$res['parentid']]['companyname']		=	$res['companyname'];
				$parentidStr										   .=	"'".$res['parentid']."'".',';
			}
			$EcsMandatedecode		=	json_decode($this->fetchEcsMandateFlag($parentidStr),1);
			$EcsSidecode			=	json_decode($this->fetchSiMandateFlag($parentidStr),1);
			$EcsActioncode			=	json_decode($this->fetchEcsActionFlag($parentidStr),1);
			if($EcsMandatedecode['errorCode']==	0) {
				foreach($EcsMandatedecode['data'] As $key=>$value) {
					$retArr['data'][$key]['flag']			=	$value['flag'];
					$retArr['data'][$key]['eflag']			=	$value['eflag'];
					$retArr['data'][$key]['ecs_stop_flag']	=	$value['ecs_stop_flag'];
				}
			}
			if($EcsSidecode['errorCode']	==	0) {
				foreach($EcsSidecode['data'] as $key=>$value) {
					$retArr['data'][$key]['flag']				=	$value['si_flag'];
					$retArr['data'][$key]['Si_flag']			=	$value['si_flag'];
					$retArr['data'][$key]['Si_eflag']			=	$value['si_eflag'];
					$retArr['data'][$key]['Si_ecs_stop_flag']	=	$value['si_ecs_stop_flag'];
				}
			}
			if($EcsActioncode['errorCode']	==	0) {
				foreach($EcsActioncode['data'] as $key=>$value) {
					$retArr['data'][$key]['tme_comments']			=	$value['tme_comments'];
					$retArr['data'][$key]['action_flag']			=	$value['action_flag'];
					$retArr['data'][$key]['state']					=	$value['state'];
					$retArr['data'][$key]['escalated_details']		=   $value['escalated_details'];
					$retArr['data'][$key]['date_str']				=	$value['date_str'];
					$retArr['data'][$key]['tmename']				=	$value['tmename'];
					$retArr['data'][$key]['ecs_reject_approved']	=	$value['ecs_reject_approved'];
					$retArr['data'][$key]['reactivate_flag']		=	$value['reactivate_flag'];
					$retArr['data'][$key]['tmecode']				=	$value['tmecode'];
					$retArr['data'][$key]['EcsUpdate_Flag']			=	$value['EcsUpdate_Flag'];
					$retArr['data'][$key]['website']				=	$value['website'];
				}
			}
			$respArr	=	array();
			$count	=	0;
			foreach($retArr['data'] as $keyPar=>$valPar) {
				if(isset($valPar['flag']) && $valPar['flag']	==	'1') {
					$respArr['data'][]	=	$valPar;
					$count++;
				}
			}
			
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	'Row Id Found';
		} else {
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'No Rowid Found';
		}
		$respArr['count']	=	$count;
		return $respArr;
	}
	
	public function fetchEcsMandateFlag($parentid) {
		$resEcsMandate	=	array();
		$parentid		=	rtrim($parentid, ",");
		$sqlSelect1 	=	"SELECT a.parentid,if (count(1) = sum(a.ecs_stop_flag),'1','0')  as ecs_stop_flag FROM db_ecs.ecs_mandate a WHERE a.parentid IN (".$parentid.") GROUP BY a.parentid";
		$con			=	parent::execQuery($sqlSelect1,$this->conn_fin);
		$num			=	parent::numRows($con);
		if($num>0){
			while($res	=	parent::fetchData($con)) {
				$resEcsMandate['data'][$res['parentid']]['flag']			=	1;
				$resEcsMandate['data'][$res['parentid']]['eflag']			=	1;
				$resEcsMandate['data'][$res['parentid']]['ecs_stop_flag']	=	$res['ecs_stop_flag'];
			}
			$resEcsMandate['errorCode']		=	0;
			$resEcsMandate['errorStatus']	=	'Row Id Found';
		} else {
			$resEcsMandate['errorCode']		=	1;
			$resEcsMandate['errorStatus']	=	'No Rowid Found';
		}
		$resEcsMandate['count']	=	$num;
		return json_encode($resEcsMandate);
	}
	
	public function fetchSiMandateFlag($parentid) {
		$parentid		=	rtrim($parentid, ",");
		$sqlSiMandate  	=   "SELECT a.parentid,if (count(1) = sum(a.ecs_stop_flag),'1','0')  as si_ecs_stop_flag FROM  db_si.si_mandate a WHERE a.parentid in (".$parentid.") GROUP BY a.parentid";
		$resSiMandate   =   parent::execQuery($sqlSiMandate,$this->conn_fin);
		$num			=	parent::numRows($resSiMandate);
		$retSiFlag		=	array();
		if($num>0) {
			while($rowEcs = parent::fetchData($resSiMandate)){
				$retSiFlag['data'][$rowEcs['parentid']]['si_flag'] 				  = 1;
				$retSiFlag['data'][$rowEcs['parentid']]['si_eflag']			 	  = 1;
				$retSiFlag['data'][$rowEcs['parentid']]['si_ecs_stop_flag']		  = $rowEcs['si_ecs_stop_flag'];
			}
			$retSiFlag['errorCode']	=	0;
			$retSiFlag['errorStatus']	=	'Row Id Found';
		} else {
				$retSiFlag['errorCode']	=	1;
				$retSiFlag['errorStatus']	=	'No Rowid Found';
		}
		$retSiFlag['count']	=	$num;
		return json_encode($retSiFlag);
	}
	
	public function fetchEcsActionFlag($parentid) {
		$parentid		=	rtrim($parentid, ",");
		$sqlEcsAction  	= "SELECT b.parentid,b.tme_comment,b.action_flag,b.companyname as compname,b.state,b.escalated_details,b.insert_date as date_str,tmename,ecs_reject_approved,reactivate_flag,tmecode,b.allocated_date,b.update_date FROM tbl_new_retention b WHERE b.parentid in (".$parentid.")";
		$resEcsAction   =   parent::execQuery($sqlEcsAction,$this->conn_local);
		$num			=	parent::numRows($resEcsAction);
		$retSiFlag		=	array();
		if($num>0){
			while($rowEcsAction = parent::fetchData($resEcsAction)){
				$select_finance 		= "SELECT * FROM db_finance.tbl_companymaster_finance WHERE parentid = '".$rowEcsAction['parentid']."' AND campaignid IN ('72','73')";
				$select_finance_Res 	= parent::execQuery($select_finance,$this->conn_fin);
				$select_finance_numRows = parent::numRows($select_finance_Res);
				if($select_finance_numRows > 0){
					$rowEcsAction['website'] = 1;
				}else{
					$rowEcsAction['website'] = 0;
				}
				$retEcsAction['data'][$rowEcsAction['parentid']]['tme_comments']		 	= $rowEcsAction['tme_comment'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['action_flag']		 	 	= $rowEcsAction['action_flag'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['state']		 		 	= $rowEcsAction['state'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['escalated_details']	 	= $rowEcsAction['escalated_details'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['date_str']				= $rowEcsAction['date_str'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['tmename']	 				= $rowEcsAction['tmename'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['ecs_reject_approved']	 	= $rowEcsAction['ecs_reject_approved'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['reactivate_flag']	 		= $rowEcsAction['reactivate_flag'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['tmecode']	 				= $rowEcsAction['tmecode'];
				$retEcsAction['data'][$rowEcsAction['parentid']]['website']	 				= $rowEcsAction['website'];
				if($rowEcsAction['action_flag'] == 5 || $rowEcsAction['action_flag'] == 23){
					$timestring = $rowEcsAction['update_date'];
				}else{
					$timestring = $rowEcsAction['allocated_date'];
				}
				if($timestring != '' && $timestring != null){
					$datetime=new DateTime($timestring);
					$datetime->modify('+60 day');
					$datetime->format("Y-m-d");
					$curr_date_pri = new DateTime('now');
					$datetime->getTimestamp(); 
					$curr_date_pri->getTimestamp();
					if($curr_date_pri->getTimestamp() >= $datetime->getTimestamp()){
						$retEcsAction['data'][$rowEcsAction['parentid']]['EcsUpdate_Flag'] = 1; //update
					}else{
						$retEcsAction['data'][$rowEcsAction['parentid']]['EcsUpdate_Flag'] = 0; //remain
					}
				}else{
					$retEcsAction['data'][$rowEcsAction['parentid']]['EcsUpdate_Flag'] = 1; //update
				}
			}
			$retEcsAction['errorCode']	=	0;
			$retEcsAction['errorStatus']	=	'Row Id Found';
		}else{
			$retEcsAction['errorCode']	=	1;
			$retEcsAction['errorStatus']	=	'No Rowid Found';
		}
		$retEcsAction['count']	=	$num;
		return json_encode($retEcsAction);
	}
	
	public function fetchTmeRetentionComments() {
		$retArr		=	array();
		if($this->params['flag']	==	1) {
			$query	=	"SELECT tme_comment FROM tbl_new_retention WHERE parentid='".$this->params['parentid']."'";
			$con	=	parent::execQuery($query,$this->conn_local);
			$num	=	parent::numRows($con);
			if($num > 0) {
				$retArr['data']	=	parent::fetchData($con);
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		} else if($this->params['flag']	==	2) {
			$date_comnt = date('Y-m-d H:i:s').'--'.$this->params['empcode'].'('.$this->params['empcode'].')';
			$queryIns 	= "INSERT INTO tbl_new_retention SET tme_comment = concat(ifnull(tme_comments,''),',','".addslashes($this->params['tme_comment'].'--'.$date_comnt)."'), parentid = '".$this->params['parid']."' ON DUPLICATE KEY UPDATE  tme_comments = concat(ifnull(tme_comments,''),',','".addslashes($this->params['tme_comment'].'--'.$date_comnt)."')";
			$conIns		= parent::execQuery($queryIns,$this->conn_local);
			if($conIns){
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Inserted';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Inserted';
			}
		}
		return $retArr;
	}
	
	public function StoreCommentretention(){
		$date_comnt = date('Y-m-d H:i:s').'--'.$this->params['empcode'].'('.$this->params['empcode'].')';
		if($this->params['lead'] == '0'){
			$query			=	"UPDATE tbl_new_lead SET tme_comment = concat(ifnull(tme_comment,''),',','".addslashes(addslashes(stripslashes($this->params['Comment'])).'--'.$date_comnt)."')   WHERE parentid='".$this->params['parentid']."'";
		}else{
			$query			=	"UPDATE tbl_new_retention SET tme_comment = concat(ifnull(tme_comment,''),',','".addslashes(addslashes(stripslashes($this->params['Comment'])).'--'.$date_comnt)."')   WHERE parentid='".$this->params['parentid']."'";
		}
		$con  		= 	parent::execQuery($query,$this->conn_local);
		$selQuery	=	"SELECT tme_comment FROM tbl_new_retention WHERE parentid = '".$this->params['parentid']."'";
		$conQuery	=	parent::execQuery($selQuery,$this->conn_local);
		$num		=	parent::numRows($conQuery);
		if($num	>	0)
			$resQuery	=	parent::fetchData($conQuery);
		if($con){
			$retArr['results']['errorCode']	=	0;	
			$retArr['results']['errorStatus']	=	'Data Inserted';	
		}else{
			$retArr['results']['errorCode']	=	1;	
			$retArr['results']['errorStatus']	=	'Data Not Inserted';	
		}
		$retArr['results']['retData']	=	$resQuery['tme_comment'];
		return $retArr;
	}
	
	public function StoreCommentECS(){
		$date_comnt = date('Y-m-d H:i:s').'--'.$this->params['empcode'].'('.$this->params['empcode'].')';
		$query			=	"UPDATE tbl_ecs_retention_action SET tme_comments = concat(ifnull(tme_comments,''),',','".addslashes(addslashes(stripslashes($this->params['Comment'])).'--'.$date_comnt)."')   WHERE parentid='".$this->params['parentid']."'";
		$con  		= 	parent::execQuery($query,$this->conn_local);
		$selQuery	=	"SELECT tme_comment FROM tbl_ecs_retention_action WHERE parentid = '".$this->params['parentid']."'";
		$conQuery	=	parent::execQuery($selQuery,$this->conn_local);
		$num		=	parent::numRows($conQuery);
		if($num	>	0)
			$resQuery	=	parent::fetchData($conQuery);
		if($con){
			$retArr['results']['errorCode']	=	0;	
			$retArr['results']['errorStatus']	=	'Data Inserted';	
		}else{
			$retArr['results']['errorCode']	=	1;	
			$retArr['results']['errorStatus']	=	'Data Not Inserted';	
		}
		$retArr['results']['retData']	=	$resQuery['tme_comment'];
		return $retArr;
	}
	
	public function getAutoWrapupInfo(){
		$retArr 			= 	array();
		$cond				=	'';
		$limitFlag			=	'';
		if(isset($this->params['srchData']) && $this->params['srchData']!=''){
			$limitFlag	 =	" LIMIT 0,50";
			$search	.=	' AND empcode ="'.$this->params['srchData'].'"';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	50*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",50";
		}else {
			$limitFlag	=	" LIMIT 0,50";
		}
		if($this->params['dateFrom']	!=	'' && $this->params['dateTo']!=''){
			$cond	.=" AND  (DATE(a.updated_on)>='".$this->params['dateFrom']."' AND  DATE(a.updated_on)<='".$this->params['dateTo']."') GROUP BY a.empcode";
		}
		if($this->params['dateFrom']	==	'' && $this->params['dateTo']==''){
			$this->params['dateFrom']	= date("Y-m-d");
			$this->params['dateTo']	=	date("Y-m-d");
			$cond	.=" AND  (DATE(a.updated_on)>='".$this->params['dateFrom']	."' AND  DATE(a.updated_on)<='".$this->params['dateTo']."') GROUP BY a.empcode";
		}
		$count_query	=	"SELECT DISTINCT(a.empcode),a.empname,SUM(wrapupTime) AS TotalWrapupTime,g.allocId as team FROM tbl_auto_wrapup_log a RIGHT JOIN d_jds.tbl_disposition_info b ON (b.disposition_value = a.Disposition) RIGHT JOIN d_jds.mktgEmpMaster g ON (g.mktEmpCode = a.empcode) WHERE a.wrapupTime != '' AND a.parentid != '' AND a.Disposition != '' ".$cond." ".$search." ";
		$count_con	=	parent::execQuery($count_query,$this->conn_tme);
		$count_num	=	parent::numRows($count_con);
		$query	=	"SELECT DISTINCT(a.empcode),a.empname,SUM(wrapupTime) AS TotalWrapupTime,g.allocId as team FROM tbl_auto_wrapup_log a RIGHT JOIN d_jds.tbl_disposition_info b ON (b.disposition_value = a.Disposition) RIGHT JOIN d_jds.mktgEmpMaster g ON (g.mktEmpCode = a.empcode) WHERE a.wrapupTime != '' AND a.parentid != '' AND a.Disposition != '' ".$cond." ".$search." ".$limitFlag;
		$con	=	parent::execQuery($query,$this->conn_tme);
		$num	=	parent::numRows($con);
		if($num > 0) {
			$retArr['total'] 		= $count_num;
			$i = 0;
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][$i]	=	array();
				$retArr['data'][$i]	=	$res;	
				$retArr['data'][$i]['TotalWrapupTime']	=	(int)$res['TotalWrapupTime'];	
				$i++;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	
	public function getAutoWrapupInfoDetail(){
		$retArr 			= 	array();
		$cond				=	'';
		$paridSrch			=	'';
		$limitFlag			=	'';
		if(isset($this->params['srchData']) && $this->params['srchData']!=''){
			$limitFlag	 =	" LIMIT 0,50";
			$search	.=	' AND empcode ="'.$this->params['srchData'].'"';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	50*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",50";
		}else {
			$limitFlag	=	" LIMIT 0,50";
		}
		if($this->params['dateFrom']	!=	'' && $this->params['dateTo']!=''){
			$cond	.=" AND  (DATE(a.updated_on)>='".$this->params['dateFrom']."' AND  DATE(a.updated_on)<='".$this->params['dateTo']."') ";
		}
		if($this->params['dateFrom']	==	'' && $this->params['dateTo']==''){
			$this->params['dateFrom']	= date("Y-m-d");
			$this->params['dateTo']	=	date("Y-m-d");
			$cond	.=" AND  (DATE(a.updated_on)>='".$this->params['dateFrom']	."' AND  DATE(a.updated_on)<='".$this->params['dateTo']."') ";
		}
		$count_query	=	"SELECT DISTINCT(a.empcode),a.empname,a.parentid,a.wrapupTime,b.disposition_name as Disposition,a.updated_on FROM tbl_auto_wrapup_log a RIGHT JOIN d_jds.tbl_disposition_info b ON (b.disposition_value = a.Disposition) WHERE a.wrapupTime != '' AND a.parentid != '' AND a.Disposition != '' ".$cond." ".$search." ";
		$count_con	=	parent::execQuery($count_query,$this->conn_tme);
		$count_num	=	parent::numRows($count_con);
		$query	=	"SELECT DISTINCT(a.empcode),a.empname,a.parentid,a.wrapupTime,b.disposition_name as Disposition,a.updated_on FROM tbl_auto_wrapup_log a RIGHT JOIN d_jds.tbl_disposition_info b ON (b.disposition_value = a.Disposition) WHERE a.wrapupTime != '' AND a.parentid != '' AND a.Disposition != '' ".$cond." ".$search." ".$limitFlag;
		$con	=	parent::execQuery($query,$this->conn_tme);
		$num	=	parent::numRows($con);
		if($num > 0) {
			$retArr['total'] 		= $count_num;
			while($res	=	parent::fetchData($con)) {
				$retArr['data'][]	=	$res;	
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	
	public function removeAllCallBack() {
		$retArr	=	array();
		$query	=	"UPDATE tblContractAllocation SET pop_flag	=	'".$this->params['pop_flag']."' WHERE allocationId = '".$this->params['allocId']."'";
		$con	=	parent::execQuery($query,$this->conn_local);
		if($con){
			$retArr['results']['errorCode']		=	0;
			$retArr['results']['errorStatus']	=	'Updated';
		}else{
			$retArr['results']['errorCode']		=	1;
			$retArr['results']['errorStatus']	=	'NOT Updated';
		}
		return $retArr;
	}
	
	public function payment_type() { // Done
		$resArr 		= 	array();
		$bittotal 		=	0;
		$data_city 		= 	trim($this->params['data_city']);
		$parentid 		= 	$this->params['parentid'];
		$type 			= 	$this->params['type'];
		$payment_mode 	= 	$this->params['payment_mode'];
		$version 		= 	$this->params['version'];
		$campaignids 	= 	$this->params['campaignids'];
        $original_flg 	= 	$this->params['original_flg'];
        $disc_flg 		= 	$this->params['disc_flg'];
        $twoyear_flg 	= 	$this->params['twoyear_flg'];
        $camp_arr 		= 	explode(',',$campaignids);
		if(in_array(747,$camp_arr)){
			$sel_campaign = '';
			foreach($camp_arr as $key => $val){
				if($val!= '5' && $val!= '22' && $val!= '741' && $val!= '10'){
					$sel_campaign .= $val.','; 
				}
			}
			$sel_campaign = rtrim($sel_campaign,',');
		}else {
			$sel_campaign = $campaignids;
		}
		if($original_flg == 1){
			$bit_where 	= " and one_year='1'";
		}else if($disc_flg == 1){
			$bit_where 	= " and one_year_discount='1'";
		}else if($twoyear_flg == 1){
			$bit_where 	= " and two_year='1'";
		}
		$sel_sql		=	"select * from tbl_payment_type where parentid='".$parentid."' and version='".$version."' and find_in_set('package_expired',payment_type) <> 0";
		$con_sel_sql	=	parent::execQuery($sel_sql,$this->conn_fin);
		$num_sel_sql	=	parent::numRows($con_sel_sql);
		$exp_flag=0;
		if($num_sel_sql > 0) { 
			$exp_flag=1; 
		}
		$camp_val 		= 	"select bitvalue from tbl_payment_type_master where campaign_id in(".$sel_campaign.") ".$bit_where;
        $camp_res    	=   parent::execQuery($camp_val,$this->conn_fin);
        if(parent::numRows($camp_res) > 0){
			 while($camp_val  =   parent::fetchData($camp_res)) {
				 $bittotal += $camp_val['bitvalue'];
			 }
		}
		if( ($exp_flag==0) || ($exp_flag==1 && trim($type)!='jdrr' && trim($type)!='jdrrplus' && trim($type)!='banner' )){
			$ins_sql =" insert into tbl_payment_type
							set parentid='".$parentid."',
								payment_type='".$type."',
								version='".$version."',
								module = 'tme',
								instrument_type ='".$payment_mode."',
								inserted_time = now(),
								payment_type_flag = '".$bittotal."'
							ON DUPLICATE KEY UPDATE 
								payment_type='".$type."',
								module ='tme',
								instrument_type ='".$payment_mode."',
								inserted_time = now(),
								payment_type_flag = '".$bittotal."'";
			$ins_res = parent::execQuery($ins_sql,$this->conn_fin);	  	
		}else
			$ins_res =true; 
		if(isset($ins_res)){
			$resArr['error_code'] = '0';
			$resArr['error_msg'] = 'success';
		}else {
			$resArr['error_code'] = '1';
			$resArr['error_msg'] = 'failed';
		}
		return $resArr;
	}
	
	public function delete_update(){
		$resArrr			=	array();
		$resetcampaigns		=	"UPDATE campaigns_selected_status set selected=-1 where parentid='".trim($this->params['parentid'])."' and campaignid='".$this->params['campaignid']."'";  
		$con				=	parent::execQuery($resetcampaigns,$this->conn_temp);
		if($con){
			$resArrr['errorCode']	=	0;		
			$resArrr['errorStatus']	=	'Data Updated';		
		}else{
			$resArrr['errorCode']	=	1;		
			$resArrr['errorStatus']	=	'Data Not Updated';		
		}
		return $resArrr;
	}
	
	public function check_one_plus_block(){
		$check_flg 		= "SELECT ecssecyrinc,one_plus,city FROM d_jds.tbl_business_uploadrates WHERE city='".$this->params['data_city']."' ";
		$res_flg   		= parent::execQuery($check_flg,$this->conn_local);
		$count     		= parent::numRows($res_flg);		
		if($count>0){
			$row	  					=	parent::fetchData($res_flg);
			$one_plus 					= $row['one_plus'];
			$resArr['data']['one_plus'] =  $one_plus;
			$resArr['data']['code'] 	= '0';
			$resArr['data']['error_msg']= 'success';
		}else{			
			$resArr['data']['code'] 	= '1';
			$resArr['data']['error_msg']= 'fail';
		}
		return $resArr;		
	}
	
	
	public function sendratinglink(){
		$resArr					= array();
		$sql					= "SELECT concat(url_cityid,shorturl) as shorturl from db_iro.tbl_id_generator where parentid='".$this->params['parentid']."'";
		$query					= parent::execQuery($sql,$this->conn_local);
		if(parent::numRows($query)>0){
			while($row	= parent::fetchData($query)){
				$resArr['shortUrl']	=	$row['shorturl'];
			}
		}else{
			$resArr['shortUrl']		=	'';
		}
		$sms_cont 				= "Dear ".$this->params['compname'].",\nCongratulations! Your company rating page is now live. Send free SMS & Email to your customers to get ratings on Justdial- India's Leading Local Search Engine.\nClick Now: http://jsdl.in/RT-".$resArr['shortUrl']."\n \nRegards,\nTeam Justdial";
		$sms_cont				=  addslashes(stripslashes($sms_cont));
		$email_cont 			= '';
		$url					=	"http://jsdl.in/RT-".$resArr['shortUrl'];
		$email_cont 		   .= "Dear ".$this->params['compname'].",<br><br>";
		$email_cont 		   .= "Congratulations! Your company rating page is now live.Send free SMS & Email to your customers to get ratings on Justdial- India's Leading Local Search Engine.<br><br>";
		$email_cont 		   .= "Click Now: <a href=".$url.">".$url."</a><br><br>";
		$email_cont 		   .= "Regards,<br>";
		$email_cont 		   .= "Team Justdial";		
		$email_cont				= trim(addslashes($email_cont));		
		$source 				= "TME_REVIEW";
		$from					= "feedback@justdial.com";
		$subject				= "Get feedback from your customers"; 
		foreach($this->params['mob_arr'] as $key => $val) {
			if($val !=''){
				 $sql_sms					= "INSERT INTO tbl_common_intimations SET 
													mobile = '".$val."', 
													sms_text ='".$sms_cont."',
													source='".$source."'";
				$result 					= parent::execQuery($sql_sms,$this->conn_message);
			}
		}		
		foreach($this->params['email_arr'] as $key => $val) {
			if($val !=''){
				$sql_email					= "INSERT INTO tbl_common_intimations SET 
												sender_email  = '".$from."', 
												email_id      = '".$val."',
												email_subject = '".$subject."',
												email_text    = '".addslashes(stripslashes(file_get_contents("http://messaging.justdial.com/email_header.php")."<br><br>".stripslashes($email_cont)."<br><br>".file_get_contents("http://messaging.justdial.com/email_footer.php")))."',
												source        = '".$source."'";
				$result 					= parent::execQuery($sql_email,$this->conn_message);
			}
		}
		$resArr['data']['error_code'] = 0;
		$resArr['data']['error_msg'] = "success";
		return $resArr;
	}
	
	public function getTimerStatus(){
		$retArr	=	array();
		$insert	=	"SELECT isConnected FROM tbl_timer_status where empcode = '".$this->params['empcode']."'";
		$result	=	parent::execQuery($insert,$this->conn_tme);
		$data	=	parent::fetchData($result);
		$count	=	parent::numRows($result);
		if($count != 0){
			$retArr['errorCode']	=	0;
			$retArr['data']			=	$data;
		}else{
			$retArr['errorCode']	=	1;
			$retArr['msg']	=	'No Data';
		}
		return $retArr;
	}

	public function update_generalinfo_shadow(){
		$resultArr 				=  array();
		if(!isset($this->params['parentid']) && $this->params['parentid'] == ''){
			$resultArr['errorCode']	=	1;
			$resultArr['errorStatus']	=	'ParIdIssue';
		}elseif(!isset($this->params['contact_person']) && $this->params['contact_person'] == ''){
			$resultArr['errorCode']	=	1;
			$resultArr['errorStatus']	=	'NameNotSent';
		}elseif(!isset($this->params['salute']) && $this->params['salute'] == ''){
			$resultArr['errorCode']	=	1;
			$resultArr['errorStatus']	=	'salutationNotSent';
		}else{
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 				= $this->params['parentid'];
				$mongo_inputs['data_city'] 				= $this->params['data_city'];
				$mongo_inputs['module']					= 'tme';
				$mongo_data 							= array();
				$geninfo_tbl 							= "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt 							= array();
				$this->params['contact_person']			=	$this->params['salute'].' '.$this->params['contact_person'];
				$geninfo_upt['contact_person'] 			= addslashes(stripslashes($this->params['contact_person']));
				$geninfo_upt['contact_person_display'] 	= addslashes(stripslashes($this->params['contact_person']));
				$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateClientInfo 					= $this->mongo_obj->updateData($mongo_inputs);
			}
			$this->params['contact_person']	=	$this->params['salute'].' '.$this->params['contact_person'];
			$sqlUpdateClientInfo 	= "UPDATE tbl_companymaster_generalinfo_shadow SET contact_person = '".addslashes(stripslashes($this->params['contact_person']))."',contact_person_display='".addslashes(stripslashes($this->params['contact_person']))."' WHERE parentid ='".$this->params['parentid']."'";
			//$resUpdateClientInfo 	= parent::execQuery($sqlUpdateClientInfo,$this->conn_tme);
			if($resUpdateClientInfo){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	"Updated";
			}else{
				$resultArr['errorCode']	=	1;
				$resultArr['errorStatus']	=	'Update Not Done';
			}
		}
		return $resultArr;
	}
	
	public function freeWebsiteStatus(){
			$retArr 		= array();
			 $camSelQuery	=	"SELECT * FROM db_finance.tbl_payment_type WHERE parentid='".$this->params['parentid']."' ORDER BY inserted_time";
            $conCamSel		=	parent::execQuery($camSelQuery,$this->conn_fin);
            $numCamSel		=	parent::numRows($conCamSel);
            if($numCamSel > 0){
				$retArr['camSelData']['errorCode']	=	0;
	            while($row = parent::fetchData($conCamSel)){
					$retArr['camSelData']['data'][]	=	$row;
				}
			}else{
				$retArr['camSelData']['errorCode']	=	1;
			}
			$userSelQuery	=	"SELECT * FROM tme_jds.tbl_omni_cat_log where parentid='".$this->params['parentid']."'";
            $conUserSel		=	parent::execQuery($userSelQuery,$this->conn_local);
            $numUserSel		=	parent::numRows($conUserSel);
            $userSelQuery1		=	"SELECT * FROM tbl_omni_cat_log where parentid='".$this->params['parentid']."'";
            $conUserSel1		=	parent::execQuery($userSelQuery1,$this->conn_idc);
            $numUserSel1		=	parent::numRows($conUserSel1);
            if($numUserSel > 0){
				while($row = parent::fetchData($conUserSel)){
					$retArr['userSelData']['data1'][]	=	$row;
				}
			}
			if($numUserSel1 > 0){
				while($row = parent::fetchData($conUserSel1)){
					$retArr['userSelData']['data2'][]	=	$row;
				}
			}
            if($numUserSel > 0 || $numUserSel1 > 0){
				$retArr['userSelData']['errorCode']	=	0;
				if($numUserSel > 0){
					$retArr['userSelData']['data']	=	$retArr['userSelData']['data1'];
				}else if($numUserSel1 > 0){
					$retArr['userSelData']['data']	=	$retArr['userSelData']['data2'];
				}else if($numUserSel > 0 && $numUserSel1 > 0){
					$retArr['userSelData']['data']	=	array_unique(array_merge($retArr['userSelData']['data1'],$retArr['userSelData']['data2']));
				}
			}else{
				$retArr['userSelData']['errorCode']	=	1;
			}
			return $retArr;
	}
	
	public function getforgetLink(){
			$resarr 			= 	array();
            $sql    			=   "SELECT forget_link FROM online_regis.tbl_domainReg_forget_link where provider='".$this->params["registername"]."'";
            $res    			=  	parent::execQuery($sql,$this->conn_idc);
            if(parent::numRows($res) > 0){
                $rowdata 				= 	parent::fetchData($res);
                $resarr['data'] 		=   $rowdata['forget_link']; 
                $resarr['errormsg'] 	= 	"data found";
                $resarr['errorcode'] 	= 	"0";
            }else {
                $resarr['errormsg'] 	= 	"data not found";
                $resarr['errorcode'] 	= 	"1";
            }
            return $resarr;
     }
     
     public function domainregisterauto() {
        $retArr     =   array();
        $quCities   =   "SELECT provider FROM online_regis.tbl_domainReg_forget_link WHERE provider LIKE '".$this->params['srchData']."%' LIMIT 10";
        $conNum     =   parent::execQuery($quCities,$this->conn_idc);
        $numRowsNum =   parent::numRows($conNum);
        if($numRowsNum > 0) {
            while($row  =   parent::fetchData($conNum)){
                $retArr['data'][]           =   $row;
            }
            $retArr['errorCode']    =   0;
            $retArr['errorStatus']  =   'Data Found';
        } else {
            $retArr['errorCode']    =   1;
            $retArr['errorStatus']  =   'Data Not Found';
        }
        return $retArr;
    }
    
    public function set_pack_emi(){
		$resarr 	= 		array();
		$ins_val 	=		"insert into tbl_lifetime_emi_option set parentid='".$this->params['parentid']."',companyname='".addslashes(stripslashes($this->params['companyname']))."',version='".$this->params['version']."',selected_emi_months='".$this->params['selected_emi']."',budget_multiplier='".$this->params['budget_multiplier']."',campaign='".$this->params['campaign']."',inserted_time=now() on duplicate key update selected_emi_months='".$this->params['selected_emi']."',budget_multiplier='".$this->params['budget_multiplier']."',campaign='".$this->params['campaign']."',inserted_time=now()";
		$res    	=  	 	parent::execQuery($ins_val,$this->conn_tme);
		if($res){
			$resarr['error_code'] = 0;
			$resarr['error_msg'] = 'Data Inserted';
		}else{
			$resarr['error_code'] = 1;
			$resarr['error_msg'] = 'Insertion Failed';
		}
		return $resarr;
	}
	
	public function get_pack_emi(){
		$resarr 			= 	array();
		$sel 				=	"select budget_multiplier from tbl_lifetime_emi_option where parentid='".$this->params['parentid']."' and version='".$this->params['version']."'";
		$res    			=  	parent::execQuery($sel,$this->conn_tme);
		if($res && parent::numRows($res) > 0){
			$rowdata 				= parent::fetchData($res);
			$resarr['data'] 		= $rowdata['budget_multiplier'];
			$resarr['error_code'] 	= 0;
			$resarr['error_msg'] 	= 'Data found';
		}else{
			$resarr['error_code'] 	= 1;
			$resarr['error_msg'] 	= 'Data Not Found';
		}
		return $resarr;
	}
       
    public function fetchCorIncorAccuracy(){
		$retArr = array();
		$search = '';
		$cond = '';
		$cond0 = '';
		if(isset($this->params['srchData']) && $this->params['srchData']!=''){
			$limitFlag	 =	" LIMIT 0,15";
			$search	.=	' AND empcode ="'.$this->params['srchData'].'"';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	15*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",15";
		}else {
			$limitFlag	=	" LIMIT 0,15";
		}
		if($this->params['dateFrom']	!=	'' && $this->params['dateTo']!=''){
			$cond	.=" AND  (DATE(updated_on)>='".$this->params['dateFrom']."' AND  DATE(updated_on)<='".$this->params['dateTo']."') GROUP BY empcode";
			$cond0	.="   (DATE(entry_date)>='".$this->params['dateFrom']."' AND  DATE(entry_date)<='".$this->params['dateTo']."') GROUP BY parentid";
		}
		if($this->params['dateFrom']	==	'' && $this->params['dateTo']==''){
			$this->params['dateFrom']	= 	date("Y-m-d");
			$dateFrom					= 	date("Y-m-d",strtotime("-1 days"));
			$this->params['dateTo']		=	date("Y-m-d");
			$cond						.=" AND  (DATE(updated_on)>='".$this->params['dateFrom']	."' AND  DATE(updated_on)<='".$this->params['dateTo']."') GROUP BY empcode";
			$cond0						.=" (DATE(entry_date)>='".$dateFrom."' AND  DATE(entry_date)<='".$this->params['dateTo']."') GROUP BY parentid";
		}
		$live_count				=	0;
		$get_user_data			=	"SELECT * FROM d_jds.tbl_companydetails_edit WHERE  ".$cond0." ORDER BY entry_date";
		$con_user_data			=	parent::execQuery($get_user_data,$this->conn_tme);	
		$num_user				=	parent::numRows($con_user_data);
		$get_audit					=	"SELECT * FROM tme_jds.tbl_correct_incorrect_logs_np WHERE percentage != 0  ".$cond." ".$search." ORDER BY updated_on";
		$con_audit_data				=	parent::execQuery($get_audit,$this->conn_tme);	
		$num_audit					=	parent::numRows($con_audit_data);
		if($num_audit > 0) {
			while($row = parent::fetchData($con_audit_data)){
				if($row['proceed'] == "1"){
					$live_count++;
				}
			}
		}
		$retArr= array();
		$get_data					=	"SELECT * FROM tme_jds.tbl_correct_incorrect_logs_np WHERE percentage != 0  ".$cond." ".$search." ORDER BY updated_on DESC  ".$limitFlag."";
		$con_get_data				=	parent::execQuery($get_data,$this->conn_tme);	
		$num						=	parent::numRows($con_get_data);
		if($num > 0) {
			while($row = parent::fetchData($con_get_data)){
				if(($row['bformData'] != '' && $row['bformData'] != '[]' ) || ($row['bformIncorrect'] != '' && $row['bformIncorrect'] != '[]')){
					$retArr['data'][] = $row;
				}
			}
			$retArr['data']['total_audit']	=	$num_audit;
			$retArr['data']['total_user']	=	$num_user;
			$retArr['data']['live_count']	=	$live_count;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return $retArr;
	}
	
	public function fetchCorIncorAccuracyDetail(){
		$resArr 			= 	array();
		$cond				=	'';
		$paridSrch			=	'';
		$limitFlag			=	'';
		if(isset($this->params['srchData']) && $this->params['srchData']!=''){
			$limitFlag	 =	" LIMIT 0,50";
			$search	.=	' AND empcode ="'.$this->params['srchData'].'"';
		}
		if(isset($this->params['parentid']) && $this->params['parentid']!=''){
			$limitFlag	 =	" LIMIT 0,50";
			$search	.=	' AND parentid ="'.$this->params['parentid'].'"';
		}
		if(isset($this->params['pageShow'])) {
			$pageVal	=	50*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",50";
		}else {
			$limitFlag	=	" LIMIT 0,50";
		}
		if($this->params['dateFrom']	!=	'' && $this->params['dateTo']!=''){
			$cond	.=" AND  (DATE(updated_on)>='".$this->params['dateFrom']."' AND  DATE(updated_on)<='".$this->params['dateTo']."') ";
		}
		if($this->params['dateFrom']	==	'' && $this->params['dateTo']==''){
			$this->params['dateFrom']	= date("Y-m-d");
			$this->params['dateTo']		=	date("Y-m-d");
			$cond						.=" AND  (DATE(updated_on)>='".$this->params['dateFrom']	."' AND  DATE(updated_on)<='".$this->params['dateTo']."') ";
		}
		$count_query	=	"SELECT * FROM tme_jds.tbl_correct_incorrect_logs_np WHERE  percentage != 0 ".$cond." ".$search."  ORDER BY updated_on DESC";
		$count_con		=	parent::execQuery($count_query,$this->conn_tme);
		$count_num		=	parent::numRows($count_con);
		$query			=	"SELECT * FROM tme_jds.tbl_correct_incorrect_logs_np WHERE  percentage != 0 ".$cond." ".$search."  ORDER BY updated_on DESC ".$limitFlag."";
		$con			=	parent::execQuery($query,$this->conn_tme);
		$num			=	parent::numRows($con);
		if($num > 0) {
			while($res	=	parent::fetchData($con)) {
				$res['bformIncorrect']	=	json_decode($res['bformIncorrect'],true);
				$res['bformData']		=	json_decode($res['bformData'],true);
				$retArr['data'][]		=	$res;	
			}
			$retArr['data']['total']	=	$num;
			$retArr['errorCode']		=	0;
			$retArr['errorStatus']		=	'Data Found';
		} else {
			$retArr['errorCode']		=	1;
			$retArr['errorStatus']		=	'Data Not Found';
		}
		return $retArr;
	}
	
	public function getAppointLogInfo(){
		$resArr 			= 	array();
		$cond				=	'';
		$paridSrch			=	'';
		$limitFlag			=	'';
		if(isset($this->params['pageShow'])) {
			$pageVal	=	50*$this->params['pageShow'];
			$limitFlag	=	" LIMIT ".$pageVal.",50";
		}else {
			$limitFlag	=	" LIMIT 0,50";
		}
		if(isset($this->params['followup']) && $this->params['followup'] == 1){
			$cond	.=" AND d.followUp = 1 ";
		}else if(isset($this->params['followup']) && $this->params['followup'] == 2){
			$cond	.=" AND d.followUp = 2 ";
		}else if(isset($this->params['followup']) && $this->params['followup'] == 3){
			$cond	.=" AND d.followUp = 3 ";
		}		 
		if($this->params['appointDatefrom']	!=	'' && $this->params['appointDateto']!=''){
			$cond	.=" AND  (DATE(a.allocationtime)>='".$this->params['appointDatefrom']	."' AND  DATE(a.allocationtime)<='".$this->params['appointDateto']."') ";
		}if($this->params['actionFor']	!=	'' && $this->params['actionto']!=''){
			$cond	.=" AND  (DATE(a.actiontime)>='".$this->params['actionFor']	."' AND  DATE(a.actiontime)<='".$this->params['actionto']."') ";
		}if($this->params['appointDatefrom']	==	'' && $this->params['appointDateto']=='' && $this->params['actionFor']	==	'' && $this->params['actionto']==''){
			$this->params['appointDatefrom']	= date("Y-m-d");
			$this->params['appointDateto']	=	date("Y-m-d");
			$cond	.=" AND  (DATE(a.allocationtime)>='".$this->params['appointDatefrom']	."' AND  DATE(a.allocationtime)<='".$this->params['appointDateto']."') ";
		}
		if(isset($this->params['srchData']) && $this->params['srchData']!=''){
			$limitFlag	 =	" LIMIT 0,50";
			switch($this->params['srchParam']) {
				case 'paridSrch' :
					$paridSrch	.=	' AND a.contractcode="'.$this->params['srchData'].'"'; //  LIKE "'.$paramsGET['srchData'].'%" 
				break;
				case 'compSrch' :
					$paridSrch	.=	' AND a.compname LIKE "'.$this->params['srchData'].'%"';
				break;
				case 'areaSrch' :
					$paridSrch	.=	' AND a.area LIKE "'.$this->params['srchData'].'%"';
				break;
				case 'pinSrch' :
					$paridSrch	.=	' AND a.pincode="'.$this->params['srchData'].'"';
				break;
				case 'tmeSrch' :
					$paridSrch	.=	' AND (a.parentcode="'.$this->params['srchData'].'" OR a.tmename LIKE "'.$this->params['srchData'].'%")';
				break;
				case 'meSrch' :
					$paridSrch	.=	' AND (a.empcode="'.$this->params['srchData'].'" OR a.mename LIKE "'.$this->params['srchData'].'%")';
				break;
			}
		}else{
			$paridSrch	='';
		}
		$cond			.=' '.$paridSrch;
		$cnt_sql 		=	"SELECT a.contractcode,a.compname,a.area,a.pincode,a.allocationtime,a.actiontime,a.tmename,d.tme_rank AS TMERank,a.parentcode AS tmecode,a.mename,a.empcode,d.me_rank AS MERank,d.json_resp,d.followup,a.data_city,d.appt_alloc,d.cont_allocf FROM d_jds.tblContractAllocation AS a LEFT JOIN d_jds.tbl_ranking_me_final AS c ON a.empcode=c.empcode LEFT JOIN d_jds.tbl_apptLogs AS d ON a.contractcode=d.parentid  WHERE  a.allocationtype='25' AND d.appt_alloc = 1  ".$cond;
		$cnt_res 		= 	parent::execQuery($cnt_sql,$this->conn_local_slave);
		$cnt_num 		= 	parent::numRows($cnt_res);
		$ins_sql 		=	"SELECT a.contractcode,a.compname,a.area,a.pincode,a.allocationtime,a.actiontime,a.tmename,d.tme_rank AS TMERank,a.parentcode AS tmecode,a.mename,a.empcode,d.me_rank AS MERank,d.json_resp,d.followup,a.data_city,d.appt_alloc,d.cont_allocf FROM d_jds.tblContractAllocation AS a LEFT JOIN d_jds.tbl_ranking_me_final AS c ON a.empcode=c.empcode LEFT JOIN d_jds.tbl_apptLogs AS d ON a.contractcode=d.parentid  WHERE  a.allocationtype='25' AND d.appt_alloc = 1 ".$cond." ".$limitFlag;
		$ins_res 		= 	parent::execQuery($ins_sql,$this->conn_local_slave);
		if(parent::numRows($ins_res) > 0){
			$i=0;
			while($res	=	parent::fetchData($ins_res)) {
				$res['json_resp']	=	json_decode($res['json_resp'],1);
				if($res['json_resp']['list_of_absent_me']['errorCode']	==	0){
					foreach($res['json_resp']['list_of_absent_me']['data']  as $k=>$val){
						$res['absent_me'][]	=	$val['empcode']; 
					}
				}
				if($res['json_resp']['final_me_elig']){
					foreach($res['json_resp']['final_me_elig'] as $t=>$r){
						if($t	==	'mktEmpCode')
							$res['final_me'][]	=	$r; 
					}
				}
				if($res['json_resp']['listofBusyEmpCode']){
					foreach($res['json_resp']['listofBusyEmpCode'] as $y=>$m){
							$res['busy_me'][]	=	$m; 
					}
				}
				$final_list_show	=	array();
				foreach($res['json_resp']['before_reset_me_array'] as $fin_key=>$fin_val){
					$final_list_show['mktEmpcode']				=	$fin_val['mktEmpCode'];
					$final_list_show['empName']					=	$fin_val['empName'];
					$final_list_show['mobile']					=	$fin_val['mobile'];
					$final_list_show['allocID']					=	$fin_val['allocID'];
					$final_list_show['cumulative_rank_city']	=	$fin_val['cumulative_rank_city'];
					if(isset($res['json_resp']['follow_up']))
						$final_list_show['follow_up_data']		=	$res['json_resp']['follow_up'];
					if(isset($res['json_resp']['follow_up']) && in_array($fin_val['mktEmpCode'],$res['json_resp']['follow_up'])){
						$final_list_show['follow_up']			=	0;
					}else{
						$final_list_show['follow_up']			=	1;
					}
					$final_list_show['color']	=	'black';
					if(in_array($fin_val['mktEmpCode'],$res['absent_me'])){
						$final_list_show['color']	=	'red';
					}if(in_array($fin_val['mktEmpCode'],$res['final_me'])){
						$final_list_show['color']	=	'green';
					}if(in_array($fin_val['mktEmpCode'],$res['busy_me'])){
						$final_list_show['color']	=	'blue';
					}if($final_list_show['follow_up']	==	0){
						$final_list_show['color']	=	'Indigo';
					}
					$res['final_list_show'][]	=	$final_list_show;
				}
				$resArr['data'][$i]['parentid'] 			= $res['contractcode'];
				$resArr['data'][$i]['compname'] 			= $res['compname'];
				$resArr['data'][$i]['area'] 				= $res['area'];
				$resArr['data'][$i]['pincode'] 				= $res['pincode'];
				$resArr['data'][$i]['allocationtime'] 		= $res['allocationtime'];
				$resArr['data'][$i]['actiontime'] 			= $res['actiontime'];
				$resArr['data'][$i]['tmename'] 				= $res['tmename'];
				$resArr['data'][$i]['TMERank'] 				= $res['TMERank'];
				$resArr['data'][$i]['tmecode'] 				= $res['tmecode'];
				$resArr['data'][$i]['team_type'] 			= $res['team_type'];
				$resArr['data'][$i]['mename'] 				= $res['mename'];
				$resArr['data'][$i]['MERank'] 				= $res['MERank'];
				$resArr['data'][$i]['json_resp'] 			= $res['json_resp'];
				$resArr['data'][$i]['followup'] 			= $res['followup'];
				$resArr['data'][$i]['appt_alloc'] 			= $res['appt_alloc'];
				$resArr['data'][$i]['data_city'] 			= $res['data_city'];
				$resArr['data'][$i]['cont_allocf'] 			= $res['cont_allocf'];
				$resArr['data'][$i]['final_list_show'] 		= $res['final_list_show'];
				$i++;
			}
			$resArr['total'] 		= $cnt_num;
			$resArr['errorCode'] 	= '0';
			$resArr['errorMsg'] 	= 'success';
		}else {
			$resArr['errorCode'] 	= '1';
			$resArr['errorMsg'] 	= 'failed';
		}
		return $resArr;	
	}
	
	private function getSphinxIdGen() {
		$retArr	=	array();
		$query	=	"SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$this->params['parentid']."'";
		$con	=	parent::execQuery($query,$this->conn_iro);
		if(parent::numRows($con)) {
			$retArr['data']			=	parent::fetchData($con);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Returned Successfully';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Returned';
		}
		return $retArr;
	}
	
	public function showContractBalance() {
		if($this->params['empId'] != '013084' && $this->params['empId'] != '007727'){ 
			$query	=	"SELECT parentid, surplus_amount FROM payment_general_surplus WHERE parentid	=	'".$this->params['parentid']."'";
			$con	=	parent::execQuery($query,$this->conn_fin);
			$num	=	parent::numRows($con);
			$res	=	array();
			if($num > 0) {
				$res['firstRow']['data']					=	parent::fetchData($con);
				$res['firstRow']['data']['campaignname']	=	"General Account";
				$res['firstRow']['data']['campaignid']		=	"0";
				$res['firstRow']['data']['endDate']			=	"n/a";
				$res['firstRow']['errorCode']		=	'0';
				$res['firstRow']['errorStatus']		=	'Data Successfully Returned';
			} else {
				$res['firstRow']['errorCode']		=	'1';
				$res['firstRow']['errorStatus']		=	'Data not available';
			}
		}else{
			$res['firstRow']['errorCode']		=	'1';
			$res['firstRow']['errorStatus']		=	'Data not available';
		}
		$sphinxId		=	$this->getSphinxIdGen();
		$res['partSec']	=	array();
		$queryAllDat	=	"SELECT campaignid, campaignname, RefDatabaseServer, RefDatabase, RefQuery, RefQueryDur FROM payment_campaign_master WHERE fundsTransferDisplayFlag=1";
		$conQuerAllDat	=	parent::execQuery($queryAllDat,$this->conn_fin);
		$numQuerAllDat	=	parent::numRows($conQuerAllDat);
		if($numQuerAllDat > 0) {
			$res['partSec']['errorCode']	=	0;
			$res['partSec']['errorStatus']	=	'Result Found';
			$i = 0;
			while($resultSet		=	parent::fetchData($conQuerAllDat)) {
				$res['partSec']['data'][$i]['campaignid'] 	= 	$resultSet['campaignname'];
				$RefQuery 									= 	$resultSet['RefQuery'];
				$RefQuery 									= 	str_replace('$sphinxid', $sphinxId['data'], $RefQuery);
				$RefDatabaseServer 							= 	$resultSet['RefDatabaseServer'];
				$RefDatabase 								= 	$resultSet['RefDatabase'];
				$RefQueryDur 								= 	$resultSet['RefQueryDur'];
				$RefQueryDur 								= 	str_replace('$sphinxid', $sphinxId['data'], $RefQueryDur);
				$res['partSec']['data'][$i]['endDate']		=	array();
				if($RefQueryDur!='select 0') {
					if($RefDatabase=='db_nationallisting') {
						$RefQueryDur 	.= 	" AND parentid = '" . $this->params['parentid'] . "'";
						$resEndDate 	= 	parent::execQuery($RefQueryDur,$this->conn_national);
						$numRetEndDate	=	parent::numRows($resEndDate);
					} else {
						$resEndDate 	= 	parent::execQuery($RefQueryDur,$this->conn_fin);
						$numRetEndDate	=	parent::numRows($resEndDate);
					}
					if($numRetEndDate	>	0) {
						if($RefDatabase=='db_nationallisting') {
							$rowEndDate 	= 	parent::fetchData($resEndDate);
						} else {
							$rowEndDate 	= 	parent::fetchData($resEndDate);
						}
					}
				}
				$res['partSec']['data'][$i]['balance']	=	array();
				if($RefDatabase	==	'db_nationallisting') {
					$RefQuery 		.= " AND parentid = '" . $this->params['parentid'] . "'";
					$resbalance 	= 	parent::execQuery($RefQuery,$this->conn_national);
					$numbalance 	= 	parent::numRows($resbalance);
					if($numbalance > 0) {
						$balanceRow = 	parent::fetchData($resbalance);
						$res['partSec']['data'][$i]['balance']	=	$balanceRow;
					}
					$res['partSec']['data'][$i]['balance']['numRows']	=	$numbalance;
				} else {
					$resbalance 	= 	parent::execQuery($RefQuery,$this->conn_national);
					$numbalance 	= 	parent::numRows($resbalance);
					if($numbalance > 0) {
						$balanceRow = 	parent::fetchData($resbalance);
						$res['partSec']['data'][$i]['balance']	=	$balanceRow;
					}
					$res['partSec']['data'][$i]['balance']['numRows']	=	$numbalance;
				}
				if($resultSet['campaignid']	==	22){
					$RefQuery 		= 	"SELECT IFNULL(balance,0) AS JDRRBalance FROM tbl_companymaster_finance WHERE sphinx_id='".$sphinxId['data']."' AND campaignid=22";
					$resbalance 	= 	parent::execQuery($RefQuery,$this->conn_fin);
					$numbalance 	= 	parent::numRows($resbalance);
					if($numbalance > 0) {
						$balanceRow = 	parent::fetchData($resbalance);
						$res['partSec']['data'][$i]['balance']	=	$balanceRow;
					}
					$res['partSec']['data'][$i]['balance']['numRows']	=	$numbalance;
				}
				if($resultSet['campaignid']	==	10){
					$RefQueryDur 	= 	"SELECT (CURRENT_DATE() + INTERVAL IF(balance > 0, balance, 0)/bid_perday DAY) AS end_date FROM db_national_listing.tbl_companymaster_finance_national WHERE sphinx_id='".$sphinxId['data']."' AND campaignid='".$resultSet['campaignid']."'";
					$resEndDate 	= 	parent::execQuery($RefQueryDur,$this->conn_national);	
					$numEndDate		=	parent::numRows($resEndDate);
				} else {
					$RefQueryDur 	= 	"SELECT (CURRENT_DATE() + INTERVAL IF(balance > 0, balance, 0)/bid_perday DAY) AS end_date FROM tbl_companymaster_finance WHERE sphinx_id='".$sphinxId['data']."' AND campaignid='".$resultSet['campaignid']."'";
					$resEndDate 	= 	parent::execQuery($RefQueryDur,$this->conn_fin);
					$numEndDate		=	parent::numRows($resEndDate);
				}
				if($numEndDate	>	0) {
					if($resultSet['campaignid']	==	10) {
						$rowEndDate 	= 	parent::fetchData($resEndDate);
						$res['partSec']['data'][$i]['endDate']	=	$rowEndDate;
					} else {
						$rowEndDate 	= 	parent::fetchData($resEndDate);
						$res['partSec']['data'][$i]['endDate']	=	$rowEndDate;
					}
				}
				$res['partSec']['data'][$i]['endDate']['numRows']	=	$numEndDate;
				$i++;
			}
		} else {
			$res['partSec']['errorCode']	=	1;
			$res['partSec']['errorStatus']	=	'Result Not Found';
		}
		return $res;
	}
	
	////////////////////////////////////////////Freebees/////////////////////////////////////////////
	
	function showfreebees(){
		$resArr			=	array();
		if($this->conn_city	==	'remote')
			$bal	=	'10000';
		else
			$bal	=	'15000';
		$chkBudget		=	"SELECT total FROM(SELECT SUM(budget) AS total FROM tbl_companymaster_finance WHERE parentid ='".$this->parentid."' AND campaignid IN (1,2,10) AND budget>0)t WHERE t.total>=".$bal;
		$conBudget		=	parent::execQuery($chkBudget, $this->conn_fin_slave);
		$numbudget		=	parent::numRows($conBudget);
		if($numbudget>0){
			$ecs_active = false;
			$get_ecs_status 	= "SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."' AND deactiveflag = 0 AND ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1  UNION SELECT outlet_parentid,master_billdeskid from db_ecs.ecs_mandate_outlet WHERE outlet_parentid='".$this->parentid."' AND outlet_status IN (0,1) AND vertical_flag=0 LIMIT 1";
			$res_ecs_status = parent::execQuery($get_ecs_status, $this->conn_fin_slave);
			if($res_ecs_status && mysql_num_rows($res_ecs_status)){
				$row_ecs_status = mysql_fetch_assoc($res_ecs_status);
				$ecs_active = true;
			}else{
				$get_si_status = "SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='".$this->parentid."' and deactiveflag = 0 and ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1 ";
				$res_si_status = parent::execQuery($get_si_status, $this->conn_fin_slave);
				if($res_si_status && mysql_num_rows($res_si_status)){
					$row_si_status = mysql_fetch_assoc($res_si_status);
					$ecs_active = true;
				}
			}
			if($ecs_active == true){
				$checkBanner	=	"SELECT count(1) as banner FROM tbl_companymaster_finance WHERE parentid ='".$this->parentid."' AND campaignid in (5,13) AND balance>0";
				$conBanner		=	parent::execQuery($checkBanner, $this->conn_fin_slave);
				$countbanner	=	parent::fetchData($conBanner);
				$gettempver		=	"SELECT version from tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid=22";
				$contemp 		= 	parent::execQuery($gettempver, $this->conn_fin_slave);
				$tempNum		=	parent::numRows($contemp);
				if($tempNum > 0){
					$tempNumdata	=	parent::fetchData($contemp);
					$checkJdrr		=	"SELECT count(1) as tot,SUM(campaign_net_amount+campaign_premium_amount+campaign_delta_amount) AS Total FROM payment_snapshot_entity AS d WHERE parentid ='".$this->parentid."' AND VERSION='".$tempNumdata['version']."' AND campaignid=22 AND credit_flag=1 AND (campaign_net_amount+campaign_premium_amount+campaign_delta_amount)>0 ";
					$conJdrr		=	parent::execQuery($checkJdrr, $this->conn_fin);
					$countJdrr		=	parent::fetchData($conJdrr);
				}
				if($countJdrr['tot'] > 0 || $countbanner['banner'] > 0){
					$resArr['errorCode']	=	1;
					$resArr['errorStatus']	=	'Active Jdrr/Banner campaign Found';
				}else{
					$resArr['errorCode']	=	0;
					$resArr['errorStatus']	=	'No Active Jdrr/Banner campaign Found';
				}
			}else{
				$resArr['errorCode']	=	1;
				$resArr['errorStatus']	=	'Non ECS Contract.Freebees Not Allowed!';
			}
		}else{
			$resArr['errorCode']	=	1;
			$resArr['errorStatus']	=	'Contract Budget is not satisfied the condition of 10k/15k';
		}
		return $resArr;
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
	
	public function getallcategories(){
			$tempdata = array();
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['module']       	= $this->module;
				$mongo_inputs['parentid']       = $this->parentid;
				$mongo_inputs['data_city']      = $this->data_city;
				$mongo_inputs['table']          = json_encode(array(
					"tbl_companymaster_extradetails"=>"catidlineage_nonpaid",
					"tbl_business_temp_data"=>"catIds"
				));
				$tempdata = $this->mongo_obj->getShadowData($mongo_inputs);
			}else{
				$sqlExtradetShadow  = "SELECT catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
				$resExtradetShadow 	= parent::execQuery($sqlExtradetShadow, $this->conn_iro);
				if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
					$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
					$tempdata['tbl_companymaster_extradetails'] = $row_extrdet_shadow;
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
			$this->all_temp_catids	=	array();
			if(($tempdata['tbl_business_temp_data']['catIds']!='' ) || ($tempdata['tbl_companymaster_extradetails']['catidlineage_nonpaid'] !='')){
				$this->temp_catlin_arr   =   explode('|P|',$tempdata['tbl_business_temp_data']['catIds']);
				$this->temp_catlin_arr 	= $this->getValidCategories($this->temp_catlin_arr);
				
				$this->temp_catlin_np_arr = explode("/,/",trim($tempdata['tbl_companymaster_extradetails']['catidlineage_nonpaid'],"/"));
				$this->temp_catlin_np_arr = $this->getValidCategories($this->temp_catlin_np_arr);
			}
			$all_temp_catids_arr 	= array();
			$all_temp_catids_arr 	= array_unique(array_merge($this->temp_catlin_arr,$this->temp_catlin_np_arr));
			$this->all_temp_catids 	= $all_temp_catids_arr;
	}
	
	public function getBannerBlockedCategory(){
		$trimCatid	=	'';
		$this->getallcategories();
		$retArr		=	array();
		$categories ='';
		$categoriesun ='';
		$catidun ='';
		if(count($this->all_temp_catids) > 0){
			foreach($this->all_temp_catids as $k=>$v){
				$trimCatid	.="'".$v."',";
			}
			$trimCatid			=	rtrim($trimCatid,',');
			$retArr['allowJDRR']	=	0;
			$retArr['catid']	=	$trimCatid;
			$finalArr 			= 	array();
			$querySelCatInfo 	=   "SELECT distinct(category_name) as category_name,catid,misc_cat_flag&64 as misc FROM tbl_categorymaster_generalinfo WHERE catid IN (".$trimCatid.")";
			$conCatinfo			=  	parent::execQuery($querySelCatInfo,$this->conn_local);
			$numCatInfo			=	parent::numRows($conCatinfo);
			if($numCatInfo > 0) {
				
				$catNum 		= 	0;
				while($resCatInfo	=	parent::fetchData($conCatinfo)) {
					if($resCatInfo['misc'] == 64){
						$categories 	.="'".$resCatInfo['category_name']."',";
						$finalArr['block'][$catNum] = $resCatInfo['catid'];
						$catNum++;
					}else{
						$categoriesun 	.="'".$resCatInfo['category_name']."',";
						$catidun		.="'".$resCatInfo['catid']."',";
						$finalArr['unblock'][$catNum] = $resCatInfo['catid'];
					}
				}
				$retArr['allcats']		=	$finalArr;
				if(count($this->all_temp_catids) == $catNum){
					$categories 			= 	rtrim($categories,",");
					$retArr['catnames']		=	$categories;
					$retArr['errorCode']	=	0;
					$retArr['allowJDRR']	=	1; 
					$retArr['errorStatus']	=	'Contract is having Blocked Categories ('.$categories.') . Freebees Not Allowed for Banner campaign!';	
				}else{
					$categories 			= 	rtrim($categoriesun,",");
					$catids 				= 	rtrim($catidun,",");
					$retArr['catnames']		=	$categories;
					$retArr['catid']		=	$catids;
					$retArr['errorCode']	=	0;
					$retArr['errorStatus']	=	'Contract is having UnBlocked Categories ('.$categories.') . Freebees Allowed!';	
				}
			} else {
				foreach($this->all_temp_catids as $k=>$v){
					$finalArr['unblock'][$k] = $v;
				}
				$retArr['allcats'] 		=	$finalArr; 
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'No Block Categories Found';
			}
		}else{
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'No Categories present in main table';
		}
		return $retArr;
	}

	public function explode_bidder($bidder_strng,$actual=0){
		if(trim($bidder_strng)!=''){
			$temparr	= explode(",",$bidder_strng);
			foreach($temparr as $temp){
				$bidder_arr	= explode("-",$temp);
				if($actual == 1){
					$return_arr[$bidder_arr['0']]	= $bidder_arr['1']."~".$bidder_arr['2'];
				}else{
					$return_arr[$bidder_arr['0']]	= $bidder_arr['1'];
				}
			}
		}
		return $return_arr;
	}

	public function getcatInventoryChk($catidarr){
		$available			= array();
		$res				= array();
		$allow 				=	0;
		$sql	= "SELECT catid,cat_sponbanner_bidder,cat_sponbanner_inventory FROM tbl_cat_banner_bid WHERE catid IN (".$catidarr.") AND data_city='".$this->data_city."'";
		$qry	= parent::execQuery($sql,$this->conn_fin_slave);
		if($qry && parent::numRows($qry)){
			while($row = parent::fetchData($qry)){
				if(trim($row['cat_sponbanner_bidder'])!=''){
					$bidder_arr	= $this->explode_bidder($row['cat_sponbanner_bidder']);
					$get_bidder	= array_keys($bidder_arr);
					if(in_array($this->parentid,$get_bidder)){
						$available[$row['catid']] = (1 - ($row['cat_sponbanner_inventory'] - $bidder_arr[$this->parentid]));
					}else{
						$available[$row['catid']] = (1 - $row['cat_sponbanner_inventory']);
					}
				}else{
					$available[$row['catid']] = 1;
				}
			}
			$catidArr 		 = explode(",",str_replace("'",'',$catidarr));
			$catid_frm_avail = array_keys($available);
			$diffarr		 = array_diff($catidArr,$catid_frm_avail);
			
			// echo '<pre>';print_r($catid_frm_avail);
			// echo '<pre>';print_r($catidArr); 
			// echo '<pre>';print_r($diffarr);

			$finalCat 		 =	array();
			$jk = 0;
			if(count($diffarr) > 0){
				foreach($diffarr as $cat){
					$available[$cat] = 1;
					if($available[$cat] > 0){
						$finalCat["avail"][$jk] = str_replace("'",'',$cat);
					}else{
						$finalCat["Not avail"][$jk] = str_replace("'",'',$cat);
					}
					$jk++;
				}
			}
			if(count($catidArr)>0){
				foreach($catidArr as $cat1){
					if($available[$cat1] > 0){
						$finalCat["avail"][$jk] = str_replace("'",'',$cat1);
					}else{
						$finalCat["Not avail"][$jk] = str_replace("'",'',$cat1);
					}
					$jk++;
				}	
			}
			$finalCat["Not avail"] 	= array_unique($finalCat["Not avail"]);
			$finalCat["avail"] 		= array_unique($finalCat["avail"]);
			$res['data']		=	$finalCat;
			$res['errorCode']	=	0;
			$res['errorStatus']	=	'Data Found';	
		}else{
			$finalCat 	=	array();
			$kl 		=	0;
			$catidArr 	= explode(",",str_replace("'",'',$catidarr));
			if(count($catidArr)>0){
				foreach($catidArr as $cat2){
					$available[$cat2] = 1;
					if($available[$cat2] > 0){
						$finalCat["avail"][$kl] = str_replace("'",'',$cat2);
					}else{
						$finalCat["Not avail"][$kl] = str_replace("'",'',$cat2);
					}
					$kl++;
				}	
			}
			$finalCat["Not avail"] 	= array_unique($finalCat["Not avail"]);
			$finalCat["avail"] 		= array_unique($finalCat["avail"]);
			$res['data']		=	$finalCat;
			$res['errorCode']	=	0;
			$res['errorStatus']	=	'No data';
		}
		return $res;
	}

	public function checkfreebees(){
		// if the same employee then update the entry only if its pending
		// if other employee do not allow to apply
		// Approved. show the content with approved. remove the submit button
		// check active Jdrr Banner / Website . Kindly do not show the option
		// check the contract is signed up for 10 k and above - if so allow 
		$resarr 			=	array();
		$showfreebees		=	$this->showfreebees();
		if($showfreebees['errorCode'] > 0){
			$resarr['errorCode']	=	$showfreebees['errorCode'];
			$resarr['errorStatus']	=	$showfreebees['errorStatus'];
			return $resarr;
		}
		$getBlockCheck 	=	$this->getBannerBlockedCategory();
		// print_r($getBlockCheck);
		if($getBlockCheck['errorCode'] > 0){
			$resarr['errorCode']	=	$getBlockCheck['errorCode'];
			$resarr['errorStatus']	=	$getBlockCheck['errorStatus'];
			$resarr['allowJDRR']	=	$getBlockCheck['allowJDRR'];
			return $resarr;
		}else{
			$resarr['allowJDRR']		=	$getBlockCheck['allowJDRR'];
			$resarr['allowJDRRStatus']	=	$getBlockCheck['errorStatus'];
		}
		$getInventoryChk 	=	$this->getcatInventoryChk($getBlockCheck['catid']);// passed unblocked categories
		if($getInventoryChk['errorCode'] > 0){
			$resarr['errorCode']	=	$getInventoryChk['errorCode'];
			$resarr['errorStatus']	=	$getInventoryChk['errorStatus'];
			return $resarr;
		}
		$insertFinArr		=	array();
		if(isset($getInventoryChk['data']) && isset($getBlockCheck['allcats']))
			$insertFinArr 		=	array_merge($getBlockCheck['allcats'],$getInventoryChk['data']);
		else if(isset($getInventoryChk['data']) && !isset($getBlockCheck['allcats']))
			$insertFinArr 		=	$getInventoryChk['data'];
		else
			$insertFinArr 		=	$getBlockCheck['allcats'];
		$resarr['catids'] 			= 	$insertFinArr;
		$resarr['getBlockCheck'] 	= 	$getBlockCheck;
		$resarr['getInventoryChk'] 	= 	$getInventoryChk;
		$getver_main		=	"SELECT version from tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND balance>0 AND campaignid IN (1,2,10) LIMIT 1";
		$conver_main		=	parent::execQuery($getver_main,$this->conn_fin_slave);
		$tempNum			=	parent::numRows($conver_main);
		if(count($tempNum) > 0){
			$fetVrsn				=	parent::fetchData($conver_main);
			$resarr['version']		=	$fetVrsn['version'];
			$chkEntry	=	"SELECT * FROM tbl_freebees_info WHERE parentid	='".$this->params['parentid']."' AND version ='".$fetVrsn['version']."'";
			$conChk		=	parent::execQuery($chkEntry,$this->conn_local);
			$fetchNum	=	parent::numRows($conChk);
			if($fetchNum > 0){
				$dataRow	=	parent::fetchData($conChk);
				$resarr['data']			=	$dataRow;
				if(strtolower($dataRow['status']) == "pending"){
					if($this->params['empcode'] == $dataRow['empcode']){
						$resarr['errorCode']	=	0;
						$resarr['errorStatus']	=	'Allow to Edit Request';
					}else{
						$resarr['errorCode']	=	1;
						$resarr['errorStatus']	=	'Already request has been raised for this Contract by '.$dataRow['empname'].' and its still pending';
					}
				}else if(strtolower($dataRow['status']) == "approved" || strtolower($dataRow['status']) == "rejected"){
					$resarr['errorCode']	=	0;
					$resarr['errorStatus']	=	'This request is '.$dataRow['status'];
				}else if(strtolower($dataRow['status']) == ""){
					$resarr['errorCode']	=	1;
					$resarr['errorStatus']	=	'Status is Empty!';
				}
			}else{
				$resarr['errorCode']	=	0;
				$resarr['errorStatus']	=	'New';
			}
		}else{
			$resarr['errorCode']	=	1;
			$resarr['errorStatus']	=	'Version not found.';
		}
		return $resarr;
	}
	
	public function insertfreebees(){
		$resarr 	= 	array();
		if(strtoupper($this->params['campaign']) == "JDRR"){
			$budget	=	4;
		}else{
			$budget	=	8;
		}
		$ins_val 	=	"INSERT INTO tbl_freebees_info set 
																parentid			='".$this->params['parentid']."',
																companyname			='".addslashes(stripslashes(ucwords($this->params['companyname'])))."',
																empcode				='".$this->params['empcode']."',
																empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['empname']))))."',
																campaign			='".strtoupper($this->params['campaign'])."',
																reason				='".addslashes(stripslashes($this->params['reason']))."',
																status				='Pending',
																version				='".$this->params['version']."',
																budget				='".$budget."',
																catids 				='".json_encode($this->params['catids'])."',
																insertedon			=now()
										ON DUPLICATE KEY UPDATE
																companyname			='".addslashes(stripslashes(ucwords($this->params['companyname'])))."',
																empcode				='".$this->params['empcode']."',
																empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['empname']))))."',
																campaign			='".strtoupper($this->params['campaign'])."',
																reason				='".addslashes(stripslashes($this->params['reason']))."',
																budget				='".$budget."',
																catids 				='".json_encode($this->params['catids'])."',
																updatedon			=now()";
		$res    	=  	parent::execQuery($ins_val,$this->conn_local);
		$ins_log 	=	"INSERT INTO tbl_freebees_log set 
																parentid			='".$this->params['parentid']."',
																companyname			='".addslashes(stripslashes(ucwords($this->params['companyname'])))."',
																empcode				='".$this->params['empcode']."',
																empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['empname']))))."',
																campaign			='".strtoupper($this->params['campaign'])."',
																reason				='".addslashes(stripslashes($this->params['reason']))."',
																status				='Pending',
																version				='".$this->params['version']."',
																budget				='".$budget."',
																catids 				='".json_encode($this->params['catids'])."',
																insertedon			=now()";
		$reslog    	=  	parent::execQuery($ins_log,$this->conn_local);
		if($res){
			$resarr['errorCode'] 	= 0;
			$resarr['errorStatus'] 	= 'Data Inserted';
		}else{
			$resarr['errorCode'] 	= 1;
			$resarr['errorStatus'] 	= 'Insertion Failed';
		}
		return $resarr;
	}
	
	function find_top_parents($catids){
		$parent_arr	= array();
		if(trim($catids)!='' && trim($catids,',')!=''){
			$sql_get = "SELECT DISTINCT a.catid,a.category_name,b.parentlineage FROM tbl_categorymaster_generalinfo a join tbl_categorymaster_parentinfo b on a.catid=b.catid WHERE a.catid IN (".$catids.") ORDER BY a.category_name";
			$res_get =parent::execQuery($sql_get,$this->conn_local);
			if($res_get && mysql_num_rows($res_get)){
				$i = 1;
				while($row = parent::fetchData($res_get)){
					$parentlineage_arr = explode("/",$row['parentlineage']);
					if($parentlineage_arr[1] && count($parentlineage_arr)>2 && strtoupper($parentlineage_arr[1])!='UNALLOTED CATEGORY' && strtoupper($parentlineage_arr[1])!='CATEGORY WITHOUT PARENT FROM OTHER CITY'){
						$top_parent = $parentlineage_arr[1];
					}else{
						$top_parent = "Parentless";
					}
					$par_name = trim(ucwords(strtolower($top_parent)));
					$parent_arr[$par_name][$i]['catid'] = $row['catid'];
					$parent_arr[$par_name][$i]['catname'] = $row['category_name'];
					$i++;
				}
				if(count($parent_arr['Parentless'])){
					foreach($parent_arr as $key=>$value){
						if($key!= 'Parentless'){
							foreach($parent_arr[$key] as $key2=>$value2){
									foreach($parent_arr['Parentless'] as $pkey=> $pvalue){
										if($value2['catid'] == $pvalue['catid']) {
											unset($parent_arr['Parentless'][$pkey]);
										}
									}
							}
						}
					}
				}        
			}	
						
			if(count($parent_arr['Parentless'])){
				$v = $parent_arr['Parentless'];
				unset($parent_arr['Parentless']);
				$parent_arr['Parentless'] = $v;
			}else{
				unset($parent_arr['Parentless']);
			}
		}
		return $parent_arr;
	}

	function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
  
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }


	public function updateDetails(){
		$resarr 	= 	array();
		$this->parentid  = $this->params['data']['parentid'];
		if(strtoupper($this->params['data']['campaign']) == "JDRR"){
			$budget		=	4;
			$campaignid	=	22;
		}else{
			$budget		=	8;
			$campaignid	=	5;
		}
		if(strtolower($this->params['updStat']) == "rejected"){
					$ins_val 	=	"UPDATE tbl_freebees_info SET	status				='".addslashes(stripslashes($this->params['updStat']))."',
																	hod_reason			='".addslashes(stripslashes($this->params['hod_reason']))."',
																	hod_code			='".$this->params['empcode']."',
																	hod_name			='".addslashes(stripslashes($this->params['empname']))."',
																	updatedon			=now(),
																	API_URL				='',
																	API_params			='',
																	API_response		='' 
															WHERE 
																	parentid='".$this->params['data']['parentid']."' AND version='".$this->params['data']['version']."'";
					$res    	=  	parent::execQuery($ins_val,$this->conn_local);
					$ins_log 	=	"INSERT INTO tbl_freebees_log set 
																			parentid			='".$this->params['data']['parentid']."',
																			companyname			='".addslashes(stripslashes(ucwords($this->params['data']['companyname'])))."',
																			empcode				='".$this->params['data']['empcode']."',
																			empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['data']['empname']))))."',
																			campaign			='".strtoupper($this->params['data']['campaign'])."',
																			reason				='".addslashes(stripslashes($this->params['data']['reason']))."',
																			status				='".addslashes(stripslashes($this->params['updStat']))."',
																			version				='".$this->params['data']['version']."',
																			hod_reason			='".addslashes(stripslashes($this->params['hod_reason']))."',
																			hod_code			='".$this->params['empcode']."',
																			hod_name			='".addslashes(stripslashes($this->params['empname']))."',
																			budget				='".$budget."',
																			updatedon			=now(),
																			API_URL				='',
																			API_params			='',
																			API_response		=''";
					$reslog    	=  	parent::execQuery($ins_log,$this->conn_local);
					if($res){
						$resarr['errorCode'] 	= 0;
						$resarr['errorStatus'] 	= 'Request is Rejected!!';
					}else{
						$resarr['errorCode'] 	= 1;
						$resarr['errorStatus'] 	= 'Rejection Failed!.';
					}
		}else{
				// call Finance APi to update the tabels for the freebees, on success update the table with the status.
				$paramsArr							=	array();
				$retValemp							=	array();			
				$postArrayempinfo					=	array();
				$fin_arr							=	array();
				$campaignIdArr  					=  array();
				$getUnblockedCat 					= array();
				$notAvailArr 						= array();
				$AvailArr 							= array();
				$catIDS 							= array();
				$out = 0;
				$catIDS 		=	$this->params['data']['catids'];
				$getcats 		=	json_decode($catIDS,1);
				foreach($getcats as $catk=>$catv){
					if($catk == 'unblock'){
						$getUnblockedCat[$out] = $catv;
					}
					$out++;
				}
				$not = 0;
				foreach($getcats as $v1=>$k1){
					if($v1 == 'Not avail'){
						$notAvailArr[$not] = $k1;
					}
					if($v1 == 'avail'){
						$AvailArr[$not] = $k1;
					}
					$not++;
				}
				// echo '--$notAvailArr====><pre>';print_r($notAvailArr);
				// echo '--$AvailArr====><pre>';print_r($AvailArr);
				// echo '--getcats====><pre>';print_r($getUnblockedCat);
				$key = 0;
				if($campaignid == 5){
					// Handle the inventory check here
					$catIDS1 		=	$this->params['data']['catids'];
					$getcats1 		=	json_decode($catIDS1,1);
					$j=0;
					foreach($getcats1 as $catk=>$catv){
						if($catk == 'avail' && count($catv) >0){
							array_push($campaignIdArr,'5','13'); 
						}else if($catk == 'Not avail' && count($catv) >0){
							array_push($campaignIdArr,'5'); 
						}
						$j++;
					}

					$campaignIdArr 	=	array_unique($campaignIdArr);
					if(in_array("5", $campaignIdArr) && in_array("13", $campaignIdArr)){
						$checkBudget = 4;	
					}else{
						$checkBudget = 8;	
					}
					foreach($campaignIdArr as $k=>$v){
						$postArrayempinfo['camp_array'][$k]['campaignid']	=	$v;
						$postArrayempinfo['camp_array'][$k]['budget']		=	$checkBudget;
						$postArrayempinfo['camp_array'][$k]['duration']		=	'365';
					}
				}else{
					$postArrayempinfo['camp_array'][$key]['campaignid']	=	$campaignid;
					$postArrayempinfo['camp_array'][$key]['budget']		=	$budget;
					$postArrayempinfo['camp_array'][$key]['duration']	=	'365';
				}
				$Apiparams					=	'parentid='.$this->params['data']['parentid'].'&data_city='.$this->params['data_city'].'&version='.$this->params['data']['version'].'&camp_array='. json_encode($postArrayempinfo['camp_array']).'&trace=0';
				$url						=	$this->cs_url.'00_Payment_Rework/accounts/Processes/freebees_api.php';
				$paramsArr['url'] 			= 	$this->cs_url.'00_Payment_Rework/accounts/Processes/freebees_api.php?'.$Apiparams;
				$paramsArr['formate'] 		= 	'basic';
				$retArrMain 				= 	json_decode($this->curl($paramsArr),1);
				// echo '---here---<pre>';print_r($retArr); die;
				 // $retArrMain['errorCode'] 		= 	0;
				if(count($retArrMain) >0 && $retArrMain['errorCode'] == 0){
					//update the tbl_catspon,catspon_banner_rotation,tbl_comp_banner,jd_reviewrating tables 

					// get Fin Arr 

					$getver_main		=	"SELECT * from tbl_companymaster_finance WHERE parentid = '".$this->params['data']['parentid']."' AND balance>0 AND version='".$this->params['data']['version']."'";
					$conver_main		=	parent::execQuery($getver_main,$this->conn_fin_slave);
					$tempNum			=	parent::numRows($conver_main);
					
					if(count($tempNum) > 0){
						while($row_fin_det = parent::fetchData($conver_main)){
							$fin_arr[$row_fin_det['campaignid']] = $row_fin_det;
						}
					}

					//// fin arr ends

					/// JDRR updation 

					if(isset($fin_arr['22']['balance']) && $fin_arr['22']['balance']>0){
								//$this->jdbox_ip_url			=	'http://vishalvinodrana.jdsoftware.com/jdbox/'; // remove in future.
								$paramsArr 					= 	array();
								$Apiparams12				=	'data_city='.$this->params['data_city'].'&parentid='.$this->params['data']['parentid'].'&action=9&module=cs&trace=0';
								$url11						=	$this->jdbox_ip_url.'services/populate_jdrr_budget.php';
								$paramsArr['url'] 			= 	$this->jdbox_ip_url.'services/populate_jdrr_budget.php?'.$Apiparams12;
								$paramsArr['formate'] 		= 	'basic';
								$retArr12 					= 	json_decode($this->curl($paramsArr),1);
					}

					/// JDRR ends

					/// Banner starts

					$trimCatid	=	'';
					// $this->getallcategories();
					$retArr		=	array();
					$categories ='';
					if(count($notAvailArr) > 0){
						foreach($notAvailArr as $k=>$v){
							foreach($v as $k1=>$v1){
								$trimCatid	.="'".$v1."',";
							}
						}
						$trimCatid	=	rtrim($trimCatid,',');
						$sql_cat	= "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN(".$trimCatid.")";
						$qry_cat	= parent::execQuery($sql_cat,$this->conn_local);
						if($qry_cat && parent::numRows($qry_cat) > 0){
							while($row_cat	= parent::fetchData($qry_cat)){
								$catdetails_notarr[$row_cat['catid']]['catname']	= $row_cat['category_name'];
								$catdetails_notarr[$row_cat['catid']]['nat_cat']	= $row_cat['national_catid'];
							}
						}
					}
					if(count($AvailArr) > 0){
						foreach($AvailArr as $k=>$v){
							foreach($v as $k1=>$v1){
								$trimCatid1	.="'".$v1."',";
							}
						}
						$trimCatid1	=	rtrim($trimCatid1,',');
						$sql_cat1	= "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN(".$trimCatid1.")";
						$qry_cat1	= parent::execQuery($sql_cat1,$this->conn_local);
						if($qry_cat1 && parent::numRows($qry_cat1) > 0){
							while($row_cat1	= parent::fetchData($qry_cat1)){
								$catdetails_arr[$row_cat1['catid']]['catname']	= $row_cat1['category_name'];
								$catdetails_arr[$row_cat1['catid']]['nat_cat']	= $row_cat1['national_catid'];
							}
						}
					}
					// echo '--arr---<pre>';print_r($catdetails_arr);
					// echo '---not---<pre>';print_r($catdetails_notarr);
										
					//Available 
					if(count($catdetails_arr)>0){
						$count_cat	= count($catdetails_arr);
						$catids		= array_keys($catdetails_arr);	
						$parents	= array_keys($this->find_top_parents(implode(",",$catids)));
						if(count($parents) == 2 && (in_array("Parentless",$parents) || in_array("parentless",$parents))){
							foreach($parents as $parnm){
								if($parnm!='Parentless'){
									$final_parent	= $parnm;
								}
							}
						}else if(count($parents) == 1 || count($parents) > 2 || (count($parents)  ==  2 && !in_array("Parentless",$parents) && !in_array("parentless",$parents))){
							$final_parent	= $parents['0'];
						}

						foreach($catdetails_arr as $catid => $catname){								
							if(isset($fin_arr['5']['balance']) && $fin_arr['5']['balance']>0){							
								$variable_budget =  ($fin_arr['5']['budget'] /$count_cat);
								$tbl_comp_bannersql = "INSERT INTO tbl_comp_banner set
																parentid='".$this->params['data']['parentid']."',
																catid='".$catid."',
																campaign_type=4,						
																cat_name='".addslashes($catname['catname'])."',						
																banner_camp=2,
																national_catid='".$catname['nat_cat']."',
																update_date= now(),
																tenure = 365,						
																budget=".$fin_arr['5']['budget'].",
																start_date= '".$fin_arr['5']['start_date']."',
																end_date= '".$fin_arr['5']['end_date']."',
																variable_budget	= ".$variable_budget.",
																campaign_name='cat_banner',
																iscalculated=1,
																inventory=0,				
																parentname='".addslashes($final_parent)."'
									ON DUPLICATE KEY UPDATE
																cat_name='".addslashes($catname['catname'])."',						
																banner_camp=2,
																national_catid='".$catname['nat_cat']."',
																update_date= now(),
																tenure = 365,						
																budget=".$fin_arr['5']['budget'].",
																start_date= '".$fin_arr['5']['start_date']."',
																end_date= '".$fin_arr['5']['end_date']."',
																variable_budget	= ".$variable_budget.",		
																campaign_name='cat_banner',
																iscalculated=1,
																inventory=0,				
																parentname='".addslashes($final_parent)."'";
								parent::execQuery($tbl_comp_bannersql,$this->conn_local); // echo '--result ---1==='.
								parent::execQuery($tbl_comp_bannersql,$this->conn_idc); //echo '--result ---2==='.
							}
							if(isset($fin_arr['13']['balance']) && $fin_arr['13']['balance']>0){
									$variable_budget =  ($fin_arr['13']['budget'] /$count_cat);
									$tbl_catsponsql = "INSERT INTO tbl_catspon set
															parentid='".$this->params['data']['parentid']."',
															catid = '".$catid."',
															campaign_type  	= 1,
															cat_name = '".addslashes($catname['catname'])."',
															national_catid	= ".$catname['nat_cat'].",
															iscalculated 	= 1,
															banner_camp  	= 2,
															tenure 		 	= 365,
															budget=".$fin_arr['13']['budget'].",
															variable_budget	= ".$variable_budget.",
															update_date= now(),
															start_date      = '".$fin_arr['13']['start_date']."',
															end_date		= '".$fin_arr['13']['end_date']."',						
															campaign_name  	= 'catspon',
															parentname 		= '".addslashes($final_parent)."'
										ON DUPLICATE KEY UPDATE
															cat_name 		= '".addslashes($catname['catname'])."',
															national_catid	= ".$catname['nat_cat'].",
															iscalculated 	= 1,
															banner_camp  	= 2,
															tenure 		 	= 365,
															budget=".$fin_arr['13']['budget'].",
															variable_budget	= ".$variable_budget.",
															update_date= now(),
															start_date      = '".$fin_arr['13']['start_date']."',
															end_date		= '".$fin_arr['13']['end_date']."',						
															campaign_name  	= 'catspon',
															parentname 		= '".addslashes($final_parent)."'";
								parent::execQuery($tbl_catsponsql,$this->conn_local);//echo '--result ---3==='.
								parent::execQuery($tbl_catsponsql,$this->conn_idc);//echo '--result ---4==='.
							}
						}
					}
					//Available ends

					//NOT Available 
					if(count($catdetails_notarr)>0){
						$count_cat	= count($catdetails_notarr);
						$catids		= array_keys($catdetails_notarr);	
						$parents	= array_keys($this->find_top_parents(implode(",",$catids)));
						if(count($parents) == 2 && (in_array("Parentless",$parents) || in_array("parentless",$parents))){
							foreach($parents as $parnm){
								if($parnm!='Parentless'){
									$final_parent	= $parnm;
								}
							}
						}else if(count($parents) == 1 || count($parents) > 2 || (count($parents)  ==  2 && !in_array("Parentless",$parents) && !in_array("parentless",$parents))){
							$final_parent	= $parents['0'];
						}

						foreach($catdetails_notarr as $catid => $catname){								
							if(isset($fin_arr['5']['balance']) && $fin_arr['5']['balance']>0){							
								$variable_budget =  ($fin_arr['5']['budget'] /$count_cat);
								$tbl_comp_bannersql = "INSERT INTO tbl_comp_banner set
																parentid='".$this->params['data']['parentid']."',
																catid='".$catid."',
																campaign_type=4,						
																cat_name='".addslashes($catname['catname'])."',						
																banner_camp=2,
																national_catid='".$catname['nat_cat']."',
																update_date= now(),
																tenure = 365,						
																budget=".$fin_arr['5']['budget'].",
																start_date= '".$fin_arr['5']['start_date']."',
																end_date= '".$fin_arr['5']['end_date']."',
																variable_budget	= ".$variable_budget.",
																campaign_name='cat_banner',
																iscalculated=1,
																inventory=0,				
																parentname='".addslashes($final_parent)."'
									ON DUPLICATE KEY UPDATE
																cat_name='".addslashes($catname['catname'])."',						
																banner_camp=2,
																national_catid='".$catname['nat_cat']."',
																update_date= now(),
																tenure = 365,						
																budget=".$fin_arr['5']['budget'].",
																start_date= '".$fin_arr['5']['start_date']."',
																end_date= '".$fin_arr['5']['end_date']."',
																variable_budget	= ".$variable_budget.",		
																campaign_name='cat_banner',
																iscalculated=1,
																inventory=0,				
																parentname='".addslashes($final_parent)."'";
								parent::execQuery($tbl_comp_bannersql,$this->conn_local); // echo '--result ---1==='.
								parent::execQuery($tbl_comp_bannersql,$this->conn_idc); //echo '--result ---2==='.
							}
					}
				}
					//NOT Available ends


					////////////////// Banner ends 

					//banner Approval

						if((isset($fin_arr['13']['balance']) && $fin_arr['13']['balance']>0) || (isset($fin_arr['5']['balance']) && $fin_arr['5']['balance']>0)){
							$camp_arr = array();
							if($fin_arr['5']['balance'] > 0 && $fin_arr['13']['balance'] > 0){
								array_push($camp_arr,'5','13');
								$campaignid =   " AND campaignid IN('5','13')";
							}else if($fin_arr['5']['balance'] > 0){
								$campaignid =   " AND campaignid IN('5')";
								array_push($camp_arr,'5');
							}else{
								array_push($camp_arr,'13');
								$campaignid =   " AND campaignid IN('13')";
							}
							$checksql	=	"select group_concat(version) as versions from tbl_banner_approval where parentid='".$this->params['data']['parentid']."' ".$campaignid;
							$checksql	=	parent::execQuery($checksql,$this->conn_fin_slave);
							$renewflag	=	0;
							while($row	=	parent::fetchData($checksql)){
								$versionsql	= $row['versions'];
								if($row['versions'] != null && $row['versions'] != ''){
									$versionarr=explode(',', $versionsql);
									if (in_array($version, $versionarr)) {
										$renewflag=0;
									} else {
										$renewflag=1;
									}
								}
							}
							// echo '--renew==='.$renewflag;
							if($renewflag==0){
								$checksql	=	"select parentid from tbl_banner_image_olddata 	where parentid='".$this->params['data']['parentid']."'";
								$checksql	=	parent::execQuery($checksql,$this->conn_fin_slave);
								if(parent::numRows($checksql)>0)
									$renewflag=1;
							}
							$checksql_campaigntype = "SELECT payment_type_flag,payment_type FROM tbl_payment_type_dealclosed 
													WHERE 
													parentid = '".$this->params['data']['parentid']."'
													AND 
													VERSION  = '".$this->params['data']['version']."'";	
							$checkres_campaigntype = parent::execQuery($checksql_campaigntype,$this->conn_fin_slave); 
							if(parent::numRows($checkres_campaigntype)>0){
								$checkrow_campaigntype = parent::fetchData($checkres_campaigntype);
							}else{
								$checkrow_campaigntype = "Banner-Standard";
							}
							
							foreach($camp_arr as $k=>$v){
								if($renewflag==1){
									$sql = "Insert into tbl_banner_approval SET
												parentid	     = '".$this->params['data']['parentid']."',
												version 	     = '".$this->params['data']['version']."',
												campaignid 	     = '".$v."',
												companyname      = '".addslashes(stripcslashes($this->params['data']['companyname']))."',
												campaigntype_bit = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type_flag']))."',
												campaign_name    = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type']))."',
												entry_date 	     = '".date('Y-m-d H:i:s')."',
												data_city 	     = '".addslashes(stripcslashes($this->params['data_city']))."',
												fin_approveddate = '".date('Y-m-d H:i:s')."',
												approval_status  = 0
									
									ON DUPLICATE KEY UPDATE
									
												companyname      = '".addslashes(stripcslashes($this->params['data']['companyname']))."',	
												campaigntype_bit = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type_flag']))."',
												campaign_name    = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type']))."',
												data_city 	     = '".addslashes(stripcslashes($this->params['data_city']))."'";
									parent::execQuery($sql,$this->conn_fin_slave); //echo '---result--10---->'.
								}else{
									 $sql = "Insert into tbl_banner_approval SET
												parentid	     = '".$this->params['data']['parentid']."',
												version 	     = '".$this->params['data']['version']."',
												campaignid 	     = '".$v."',
												companyname      = '".addslashes(stripcslashes($this->params['data']['companyname']))."',
												campaigntype_bit = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type_flag']))."',
												campaign_name    = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type']))."',
												entry_date 	     = '".date('Y-m-d H:i:s')."',
												data_city 	     = '".addslashes(stripcslashes($this->params['data_city']))."',
												fin_approveddate = '".date('Y-m-d H:i:s')."',
												approval_status  = 0
											
											ON DUPLICATE KEY UPDATE
											
												companyname      = '".addslashes(stripcslashes($this->params['data']['companyname']))."',	
												campaigntype_bit = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type_flag']))."',
												campaign_name    = '".addslashes(stripcslashes($checkrow_campaigntype['payment_type']))."',
												data_city 	     = '".addslashes(stripcslashes($this->params['data_city']))."'";
									parent::execQuery($sql,$this->conn_fin_slave);//echo '---result--12---->'.
								}
							}
						}

						//banner Approval ends


					//~ die('=======over=======');
					$ins_val 	=	"UPDATE tbl_freebees_info SET	status				='".addslashes(stripslashes($this->params['updStat']))."',
																	hod_reason			='".addslashes(stripslashes($this->params['hod_reason']))."',
																	hod_code			='".$this->params['empcode']."',
																	hod_name			='".addslashes(stripslashes($this->params['empname']))."',
																	updatedon			=now(),
																	API_URL				='".$url."',
																	API_params			='".$Apiparams."',
																	API_response		='".addslashes(stripslashes(json_encode($retArrMain)))."' 
															WHERE 
																	parentid='".$this->params['data']['parentid']."' AND version='".$this->params['data']['version']."'";
					$res    	=  	parent::execQuery($ins_val,$this->conn_local);
					$ins_log 	=	"INSERT INTO tbl_freebees_log set 
																			parentid			='".$this->params['data']['parentid']."',
																			companyname			='".addslashes(stripslashes(ucwords($this->params['data']['companyname'])))."',
																			empcode				='".$this->params['data']['empcode']."',
																			empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['data']['empname']))))."',
																			campaign			='".strtoupper($this->params['data']['campaign'])."',
																			reason				='".addslashes(stripslashes($this->params['data']['reason']))."',
																			status				='".addslashes(stripslashes($this->params['updStat']))."',
																			version				='".$this->params['data']['version']."',
																			hod_reason			='".addslashes(stripslashes($this->params['hod_reason']))."',
																			hod_code			='".$this->params['empcode']."',
																			hod_name			='".addslashes(stripslashes($this->params['empname']))."',
																			budget				='".$budget."',
																			updatedon			=now(),
																			API_URL				='".$url."',
																			API_params			='".$Apiparams."',
																			API_response		='".addslashes(stripslashes(json_encode($retArrMain)))."'";
					$reslog    	=  	parent::execQuery($ins_log,$this->conn_local);
					if($res){
						$resarr['errorCode'] 	= 0;
						$resarr['errorStatus'] 	= 'Request is Approved!.';
					}else{
						$resarr['errorCode'] 	= 1;
						$resarr['errorStatus'] 	= 'Approval failed!.';
					}
				}else{
					$ins_log 	=	"INSERT INTO tbl_freebees_log set 
																			parentid			='".$this->params['data']['parentid']."',
																			companyname			='".addslashes(stripslashes(ucwords($this->params['data']['companyname'])))."',
																			empcode				='".$this->params['data']['empcode']."',
																			empname				='".addslashes(stripslashes(ucwords(strtolower($this->params['data']['empname']))))."',
																			campaign			='".strtoupper($this->params['data']['campaign'])."',
																			reason				='".addslashes(stripslashes($this->params['data']['reason']))."',
																			status				='".addslashes(stripslashes($this->params['updStat']))."',
																			version				='".$this->params['data']['version']."',
																			hod_reason			='".addslashes(stripslashes($this->params['hod_reason']))."',
																			hod_code			='".$this->params['empcode']."',
																			hod_name			='".addslashes(stripslashes($this->params['empname']))."',
																			budget				='".$budget."',
																			updatedon			=now(),
																			API_URL				='".$url."',
																			API_params			='".$Apiparams."',
																			API_response		='".addslashes(stripslashes(json_encode($retArrMain)))."'";
					$reslog    	=  	parent::execQuery($ins_log,$this->conn_local);
					$resarr['errorCode'] 	= 1;
					$resarr['errorStatus'] 	= $retArr['errorStatus'];
				}
		}
		return $resarr;
	}
	
	public function getFreebeesInfo(){
		$response       = 	array();
		$srch 			=	"";
		$date 			=	"";
		if(strtolower($this->params['status']) == 'all')
			$condition  =  " status IN('Pending','Approved','Rejected')";
		else if(strtolower($this->params['status'])!='' || strtolower($this->params['status'])!= null)
			$condition  = " status='".strtolower($this->params['status'])."'";
		else
			$condition 		=	" status='Pending'";	
		if($this->params['from']!='' && $this->params['to']!='')
			$date 		.= 	"AND insertedon >='".date("Y-m-d",strtotime($this->params['from']))." 00:00:00' AND insertedon <='".date("Y-m-d",strtotime($this->params['to']))." 23:59:59'";
		if($this->params['term']!='')
			$srch		.=	"AND (parentid='".$this->params['term']."' OR companyname='".$this->params['term']."')";
		$query			=	"SELECT * FROM tbl_freebees_info WHERE ".$condition." ".$date." ".$srch." ORDER BY insertedon DESC LIMIT 1000";
		$con 			= 	parent::execQuery($query, $this->conn_local);
		$num_Rows		=	parent::numRows($con);
		if($num_Rows > 0){
			while ($res = parent::fetchData($con)) {
				$response['data'][]	=	$res;
			}
			$response['errorCode']		=	0;
			$response['errorStatus']	=	'Data Found';	
		}else{
			$response['errorCode']		=	1;
			$response['errorStatus']	=	'Data Not Found';	
		}
		return $response;
	}
	
	public function resetfreebeesInfo(){
		$response       = 	array();
		$query			=	"SELECT * FROM tbl_freebees_info WHERE parentid='".$this->params['data']['parentid']."' AND version='".$this->params['data']['version']."'";
		$con 			= 	parent::execQuery($query, $this->conn_local);
		$num_Rows		=	parent::numRows($con);
		if($num_Rows > 0){
			while ($res = parent::fetchData($con)) {
				$response['data'][]	=	$res;
			}
			$response['errorCode']		=	0;
			$response['errorStatus']	=	'Data Found';	
		}else{
			$response['errorCode']		=	1;
			$response['errorStatus']	=	'Data Not Found';	
		}
		return $response;
	}
	
	public function curl($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";
        # Init Curl Call #
        $ch = curl_init();
        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
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
	////////////////////////////////////////////Freebees/////////////////////////////////////////////

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

