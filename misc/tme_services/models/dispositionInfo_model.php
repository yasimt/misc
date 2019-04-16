
<?php
########################################################################
# This file is not a Service  file
# This is a Class File For deciding the flow for disposition_type
# Created: Apoorv Agrawal
# Date : 'When It will go live' 
# Start Date: 28-03-2016
# End Date = Date
########################################################################
class DispositionInfo_Model extends Model {
	public function __construct(){
		parent::__construct();
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->companyClass_obj  = new companyClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
	}
	 /* Main Function Which Decides the Disposition Flow */
	public function disposeFlow(){
		header('Content-Type: application/json');
		$retArr					=	array();
		$this->city				=	''; // changed here in LVE Apoorv Agrawal Date 21-11-2016
		$this->allocTime		=	'';
		$this->actionTime 		=	'';
		$this->dat_city			=	'';
		$this->area 			=	'';
		$this->pincode 			=	'';
		$this->instrct			=	'';
		// TME DETAILS
		$this->TmeCode 			=	'';
		$this->empName			=	'';// TME Name
		$this->tme_mobile		=	'';
		$this->tme_email		=	'';
		$this->tme_allocid		=	'';
		$this->tme_emp_parent	=	'';
		$this->TMEName			=	'';
		// ME DETAILS
		$this->MECode			=	'';
		$this->MEName			=	'';
		$this->ME_mobile		=	'';
		$this->ME_email			=	'';
		$this->contracts		=	'';
		$this->rowId			=	'';
		$this->actionDate		=	'';
		$this->shadow_failFlag	=	'';// tbl_companymaster_generalinfo_shadow status flag
		$this->action_DateStr	=	'';// action Date Creating String
		$this->genshadowArr 	=	array();
		$this->get_tmeArr 		=	array();
		$this->mainTableData	=	array();
		$this->get_mktgEmpMap	=	array();
		$this->get_ME_Arr		=	array();
		$this->grabCheckArr		=	array();
		$this->altaddResp		=	array(); // alternate adress array
		$this->action_timeArr	=	array();// action time Array
		$this->getshadow_IDC	=	array();
		$respArray				=	array();
		$Landlinearr			=	array();
		$arguemntArr			=	array();
		$this->allocToME 		= 	0; // For Allocate To ME
		$this->alloc_to_ME_TME	=	0; // For Allocate To ME fro TME's
		$alt_add_pincode_flg	=	0; // Grab Flow on change in address From Alternate Address
		$this->Grb_Normal_alt_add		=	'';// Alernate Address Grab or Non-Grab Flag
		
		$this->eligibilty				=	''; // latest declared variable
		$this->list_of_me_pincodewise	=	array(); // latest declared variable
		$this->TMERANK					=	'';  // latest declared variable
		
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		$this->logInsert	=	''; // changes made here by Apoorv Agrawal - today (27-01-2017)
		$this->TOTALTME		=	''; // changes made here by Apoorv Agrawal - today (03-02-2017)
		
		$this->moveToIDCFlag_resp	=	''; // 1 means don't move the data to IDC by Apoorv Agrawal - today (27-02-2017)
		$this->moveToIDCFlag		=	0; // 0 means move data to IDC by Apoorv Agrawal - today (27-02-2017)
		$this->fn_call_cnt	=	0;
		$this->contact_person	=	'';
		if(isset($_REQUEST['parentid']) && ($_REQUEST['parentid']!='')){
			$arguemntArr 	=	$_REQUEST;
		}else{
			$arguemntArr 	=	$params;
		}
		
		/* Declaring Reference Variables so that it can be accessable in this Class File */
		if(isset($arguemntArr['parentid']) && ($arguemntArr['parentid']!='' || !empty($arguemntArr['parentid'])) && isset($arguemntArr['disposeVal']) && isset($arguemntArr['ucode'])){
			$this->parentid		=	$arguemntArr['parentid'];
			$this->dispose_type	=	$arguemntArr['disposeVal'];
			$this->empCode 		=	$arguemntArr['ucode'];
			$this->compName		=	$arguemntArr['compName']; // he is sending
			$this->MECode 		=	$arguemntArr['meCode'];
			$this->dat_city		=	$arguemntArr['data_city'];// For remote and Pand India
			$this->Ext_Id		=	$arguemntArr['Extn_id'];
			$this->actionTime 	=	$arguemntArr['actTime']; // action time (08:00)
			$this->actionDate	=	$arguemntArr['actDate']; // action date (06-04-2016)
			$this->instrct		=	$arguemntArr['instrct'];
			$this->allocToME	=	$arguemntArr['allocToME'];// for super CAD TME's
			$this->alloc_to_ME_TME		=	$arguemntArr['alloc_to_ME_TME']; // for Allocate TO ME for TME's of delhi
			$this->Grb_Normal_alt_add	=	$arguemntArr['Grb_Normal_alt_add']; // Alernate Address Grab or Non-Grab Flag
			$this->ignore_followUp	=	$arguemntArr['ignore_followUp']; // Alernate Address Grab or Non-Grab Flag
			$this->logInsert	=	$arguemntArr['logInsert']; // changes made here by Apoorv Agrawal - today (27-01-2017)
			$this->TOTALTME	=	$arguemntArr['TOTALTME']; // changes made here by Apoorv Agrawal - today (03-02-2017)
			
			$this->moveToIDCFlag_resp	=	$arguemntArr['moveToIDCFlag']; // Flag responsible for tranferring Data to IDC - today(27-02-2017)
			if(isset($arguemntArr['elgFlag'])){
				$this->elgFlag	=	$arguemntArr['elgFlag'];//latest declared variable
			}
			if(isset($arguemntArr['eligibilty'])){
					$this->eligibilty	=	$arguemntArr['eligibilty'];// latest declared variable
			}
			if(isset($arguemntArr['list_of_me_pincodewise'])){
				$this->list_of_me_pincodewise	=	$arguemntArr['list_of_me_pincodewise']; // latest declared variable
			}
			
			// if pincode is sent in case of APF/CBF then use the sent pincode for processing
			if(isset($arguemntArr['pincode']) && intval($arguemntArr['pincode']) > 0 ){
				$this->pincode		=	$arguemntArr['pincode'];
			}
			if(isset($arguemntArr['TMERANK'])){
				$this->TMERANK	=	$arguemntArr['TMERANK'];
			}
		}else{
			$retArr['errorCode']			=	1;
			$retArr['errorStatus_Request']	=	'parameters not Set properly';
			echo json_encode($retArr);die();
		}
		$parentid				=	$this->parentid;
		$dispose_type			=	$this->dispose_type;
		$empCode 				=	$this->empCode;
		$compName				=	$this->compName;
		$this->actionDate		=	date("Y-m-d", strtotime($this->actionDate));
		// Following variables are required for Call Back and FollowUp
	
		$this->genshadowArr 	=	json_decode($this->get_generalinfo_shadow(),true); // Setting Data from companymaster_generalInfo_shadow
		$this->mainTableData	=	json_decode($this->getMainTabGenData(),true); // Setting Data from copmany_Master_generalInfo
		$this->get_tmeArr 		=	json_decode($this->get_TME_ME_data($this->empCode),true); // Setting Data from mktgEmpMaster
		$this->get_mktgEmpMap 	=	json_decode($this->get_mktgEmpMap_data(),true); // Setting Data from mktgEmpMap
		// For WaRNING
		$Landlinearr[0]	=	'';
		$Landlinearr[1]	=	'';
		if($this->genshadowArr['errorCode'] == 0){
			$this->genshadowArr =	$this->genshadowArr['data'];
			// If pincode is not sent then take pincode from generalinfo_shadow Table for processing
			if($this->pincode == '' || empty($this->pincode)){
				$this->pincode 		=	$this->genshadowArr['pincode'];
			}
			$this->area 		=	$this->genshadowArr['area'];
			$this->data_city	=	$this->genshadowArr['data_city'];
			$this->city			=	$this->genshadowArr['city']; // changed here in LVE Apoorv Agrawal Date 21-11-2016
			$this->shadow_failFlag	=	0;
			if($this->genshadowArr['mobile'] != ''){
				$this->contracts	= $this->genshadowArr['mobile'];	
			}
			if($this->genshadowArr['landline'] != ''){
				$this->contracts.= ','.$this->genshadowArr['landline'];
				if(trim($this->genshadowArr['landline'])){
					$Landlinearr = explode(',',$this->genshadowArr['landline']);
				}
			}
			$this->contracts	=	trim($this->contracts,","); // Setting Contacts here 
			if($this->genshadowArr['contact_person'] != '' || !empty($this->genshadowArr['contact_person']) || $this->genshadowArr['contact_person'] != null || $this->genshadowArr['contact_person'] != 'null'){
				$this->contact_person	=	$this->genshadowArr['contact_person'];
			}
		}else{
			$this->shadow_failFlag	=	1;
		}
		$this->phone 	=	$Landlinearr[0];
		$this->phone2 	=	$Landlinearr[1];
		if($this->get_tmeArr['errorCode'] == 0){
			$failFlag 				=	0;
			$this->get_tmeArr		=	$this->get_tmeArr['data'];
			$this->empName 			=	$this->get_tmeArr['empName'];
			$this->tme_mobile		=	$this->get_tmeArr['mobile'];
			$this->tme_email		=	$this->get_tmeArr['emailId'];
			$this->tme_allocid		=	$this->get_tmeArr['allocID'];
			$this->tme_emp_parent	=	$this->get_tmeArr['empParent'];
		}else{
			$failFlag 			=	1;
		}
		if($this->mainTableData['errorCode'] == 0){
			$this->mainTableData	=	$this->mainTableData['data'];
		}
		
		if($this->get_mktgEmpMap['errorCode'] == 0){
			$this->get_mktgEmpMap	=	$this->get_mktgEmpMap['data'];
			$failFlag 				=	0;
		}else{
			$failFlag 				=	1;
		}
		
		//~ if($this->getshadow_IDC['errorCode'] == 0){
			//~ $this->getshadow_IDC	=	$this->getshadow_IDC['data'];
			//~ $failFlag 				=	0;
		//~ }else{
			//~ $failFlag 				=	1;
		//~ }
		// Testing All Fucntions for parameters
		if($dispose_type != '25' && $dispose_type != '99' && $dispose_type != '22' && $dispose_type != '24' && $dispose_type != '317'){
			$flag_redirect	=	'OCD';//setting flag for redirection of flow
			// For Rest of the Disposition Value
		}else{
			if( ($this->actionDate ==	'' || empty($this->actionDate)) && ($this->actionTime == '' || empty($this->actionTime)) ){
				$respArray['errorCode'] 	=	1;
				$respArray['errorStatus_paramsIssue'] 	=	'errorStatus_paramsIssue';
				echo json_encode($respArray);die();
			}else{
				$respArray['errorCode'] 	=	0;
				$respArray['errorStatus_paramsIssue'] 	=	'No paramsIssue';
			}
			if($dispose_type == '22' || $dispose_type == '24' || $dispose_type == '317'){
				$flag_redirect 	= 	'CBF';
				/*
				 * Set the all the required data and Variables here.               
				 * actionTime is appointmentTime from UI                          
				 * allocationTime is at what time appointment is booked system time
				 * t20 flag should be default 
				 * parentcode is TME CODE 
				 * empCode is ME CODE is jrcode
				 * contractCode is the parentid in tblContractAlloc Table
				 * when CallBack OR FollowUp mename Should be empty
				*/
				$this->TmeCode 		=	$this->empCode;
				$this->MECode		=	$this->empCode;
				$this->TMEName		=	$this->empName; // TME NAME
				$this->MEName		=	'';
			}elseif($dispose_type == '25' || $dispose_type == '99'){
				$flag_redirect 	= 	'APF';
				/* In Case of Appointment Fix and Re-fix 
					check for Grab or Non Grab Flow   
				
					$allMeFlag SENT from UI only for grab Flow
					If in Grab Flow then check for $allMeFlag = 1 then Non Grab Will Work For ALL ME
					QUICK patch for ALL ME FOR GRAB FLOW
					Created by Apoorv Agrawal
					Date: 2016/04/20
				*/
				if($this->elgFlag == 'chck'){
					$fn_show_elig_me	=	json_decode($this->fn_show_elig_me(),true); // latest func declared here
					return json_encode($fn_show_elig_me);
				}
				$allMeFlag		=	0;
				if(isset($arguemntArr['AllMeFlag'])){
					$allMeFlag		=	$arguemntArr['AllMeFlag'];
				}
				$this->grabCheckArr 	=	json_decode($this->decideGrab_NonGrab(),true); 
				if($this->grabCheckArr['errorCode'] == 0){
					$this->grabCheckArr 	=	$this->grabCheckArr['data'];
				}
				/*
				 * condition Added && ($this->allocToME == 0) for Super CAD TME's
				*/
				if((($this->grabCheckArr['isGrabFlow'] == 1 && $allMeFlag==0) && ($this->allocToME == 0) && ($this->alloc_to_ME_TME==0)) || ($this->Grb_Normal_alt_add == 2)){
					/* 
					 * Setting up parameters necessary for Grab Flow
					*/
					$this->MECode 		=	'';
					$this->MEName		=	'';
					$this->ME_mobile	=	'';
					$this->ME_email		=	'';
				}elseif($this->grabCheckArr['isGrabFlow'] == 0){	
					/* Setting up parameters necessary for Non Grab Flow */
					if(empty($this->MECode) || $this->MECode==''){
						$respArray['errorCode'] 		=	1;
						$respArray['errorSatus_MECODE']	=	'Parameter Issue MECODE not sent';
						echo json_encode($respArray);die();
					}
					$this->get_ME_Arr 	=	json_decode($this->get_TME_ME_data($this->MECode),true);
					if($this->get_ME_Arr['erroCode'] == 0){
						$this->get_ME_Arr 	=	$this->get_ME_Arr['data'];
						$this->MEName		=	$this->get_ME_Arr['empName'];
						$this->ME_mobile	=	$this->get_ME_Arr['mobile'];
						$this->ME_email		=	$this->get_ME_Arr['emailId'];
					}else{
						$respArray['errorCode'] 			=	1;
						$respArray['errorSatus_MEdetails']	=	'Some Error Occured';
						$respArray['SomeError']				=	1;
					}
				}
			}
		}
		//~ if($dispose_type!='' || !empty($dispose_type)){
			//~ if($this->mainTableData['paid'] == 0){
				//~ // dumpintoDataCorrection
				//~ $dataCorrectionAPI	=	$this->dumpIntoDataCorrection();
			//~ }
		//~ }else{
			//~ $respArray['errorCode'] 				=	1;
			//~ $respArray['errorStatus_parameters'] 	=	'parameters not Set properly';
			//~ echo json_encode($respArray);
			//~ die();
		//~ }
		// Make a general function for insertion into tbl_me_tme_sink
		$insert_into_sink_Resp 	=	json_decode($this->insert_into_sink($this->empCode),true);
		if($insert_into_sink_Resp['errorCode']==0){
			$respArray['errorCode'] 	=	0;
			$respArray['errorStatus_tbl_me_tme_sink'] 	=	'Data inserted in tbl_me_tme_sink';
		}else{
			$respArray['errorCode'] 	=	1;
			$respArray['errorStatus_tbl_me_tme_sink'] 	=	'Data insertion Failed in tbl_me_tme_sink';
		}
		$fn_redirect_resp 	=	json_decode($this->fn_redirect($flag_redirect,$this->MECode),true);
		if($fn_redirect_resp['errorCode']==0){
			$respArray['errorCode'] 	=	0;
			$respArray['errorStatus_tblContractAllocation_MoveToIDC'] 	=	$fn_redirect_resp;
		}else{
			$respArray['errorCode'] 	=	1;
			$respArray['errorStatus_tblContractAllocation_MoveToIDC'] 	=	$fn_redirect_resp;
		}
		//~ if($dispose_type != 114){
			//~ $VistingCard_status 	=	json_decode($this->updateVistingCard(),true);
			//~ //return json_encode($VistingCard_status);die();
			//~ if($VistingCard_status['errorCode']==0){
				//~ $respArray['live_flg'] 	= 	1;
				//~ $curlParams_intermediate 				=	array();
				//~ $curlParams_intermediate['url']			=	JDBOX_API."/services/contract_type.php?parentid=".$this->parentid."&data_city=".$this->data_city."&rquest=get_contract_type";
				//~ $curlParams_intermediate['formate'] 	= 	'basic';
				//~ $curlParams_intermediate['method'] 		=	 'get';
				//~ $paid_status	=	Utility::curlCall($curlParams_intermediate);
				//~ $paid_status	=	json_decode($paid_status,true);
				//~ if($paid_status[result][paid] != 1){
					//~ $respArray['live_url'] 	= 	DECS_TME."/business/setContractData.php?tme=".$this->get_mktgEmpMap['rowId']."&source=tme_live&diposeFlag=1";
					//~ if($VistingCard_status['errorCode'] == 0){
						//~ $respArray['errorCode'] 	=	0;
						//~ $respArray['errorStatus_VistingCard']	=	$VistingCard_status;
					//~ }else{
						//~ $respArray['errorCode'] 	=	1;
						//~ $respArray['errorStatus_VistingCard']	=	$VistingCard_status;
					//~ }
				//~ }else{
					//~ $respArray['live_url'] 	= 	"../newTme/welcome";
				//~ }
				//~ if($VistingCard_status['errorCode'] == 0){
					//~ $respArray['errorCode'] 	=	0;
					//~ $respArray['errorStatus_VistingCard']	=	$VistingCard_status;
				//~ }else{
					//~ $respArray['errorCode'] 	=	1;
					//~ $respArray['errorStatus_VistingCard']	=	$VistingCard_status;
				//~ }
			//~ }else{
				//~ $respArray['live_flg'] 	= 	0;	
			//~ }
		//~ }
		if($this->dispose_type!='' || !empty($this->dispose_type)){
			if($this->mainTableData['paid'] == 0){
				// dumpintoDataCorrection
				$tme_feedback_disposition_arr	=	Array('7','12','98','114');
				$dc_logs_disposition_arr	=	Array('25','99','22','24','6','21','9','322','207');
				if(in_array($this->dispose_type,$dc_logs_disposition_arr)  || in_array($this->dispose_type,$tme_feedback_disposition_arr)){
					$dataCorrectionAPI	=	$this->insertLogBformDC($this->empCode,$this->dat_city);
					$respArray['insertLogBformDC']	=	$dataCorrectionAPI;
				}else{
					$dataCorrectionAPI	=	$this->dumpIntoDataCorrection($this->empCode);
					$respArray['dumpIntoDataCorrection']	=	$dataCorrectionAPI;
				}
				
			}
		}else{
			$respArray['errorCode'] 				=	1;
			$respArray['errorStatus_parameters'] 	=	'parameters not Set properly';
			echo json_encode($respArray);
			die();
		}
		$this->action_timeArr 				= 	explode("-", $this->actionDate);
		$callbackdate						= 	$this->action_timeArr[0]."-".$this->action_timeArr[1]."-".$this->action_timeArr[2]."%20".$this->actionTime;
		$respArray['callbackdate'] 			=	$callbackdate;
		if($respArray){
			return json_encode($respArray);
		}else{
			$respArray['errorCode'] 			=	1;
			$respArray['errorStatus_Dispose'] 	=	'Some Problem Please Try Again Latter';
			return json_encode($respArray);
		}
	}
	
	/*
	 * Function to decide the redirection for mktgJrpage(Appointment Fix And Appointment Re-fix)
	 * FollowUp And Call Back 
	*/
	public function fn_redirect($rd_flg,$MECode){
		$retArr 			=	array();
		if($rd_flg == 'OCD'){
			if(($this->dispose_type==7) || ($this->dispose_type==12) || ($this->dispose_type==98) || ($this->dispose_type==114)){
				
				$tmeFeedback_status 	=	json_decode($this->tmeFeedback(),true);
				
				if($tmeFeedback_status['errorCode']==0){
					$retArr['errorCode'] 	=	0;
					$retArr['errorSatus_tmeFeedback']	=	$tmeFeedback_status;
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorSatus_tmeFeedback']	=	$tmeFeedback_status;
				}
			}
			$insertDispose_status 		=	json_decode($this->insertDisposeData(),true);
			$retArr['allocated_resp_type'] = 1;
			if($insertDispose_status['errorCode'] == 0){
				$retArr['errorCode'] 	=	0;
				$retArr['errorSatus_insertDisposeData']	=	$insertDispose_status;
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorSatus_insertDisposeData']	=	$insertDispose_status;
			}
		}else{
			$this->action_timeArr 	= 	explode("-", $this->actionDate);	
			$this->action_DateStr	= 	$this->action_timeArr[0]."-".$this->action_timeArr[1]."-".$this->action_timeArr[2]." ".$this->actionTime.":00";
			$this->altaddResp 		=	json_decode($this->get_altaddress(),true);
			if($this->altaddResp['errorCode'] ==0){
				$this->altaddResp		=	$this->altaddResp['data'];
				$this->altAddres_flg	=	1; // DATA FOUND for altaddress_shadow table
			}else{
				$this->altAddres_flg	=	0;// DATA not Found for altaddress_shadow table
			}
			if($rd_flg == 'CBF'){
				// Call FollowUp And Call Back Fucntion Here
				$retArr['rd_flg']	=	$rd_flg;
				$retArr['status']	=	'inside CBF IF';
				$insertDispose_status 		=	json_decode($this->insertDisposeData(),true);
				$retArr['allocated_resp_type'] = 1;
				if($insertDispose_status['errorCode'] == 0){
					$retArr['errorCode'] 	=	0;
					$retArr['errorSatus_insertDisposeData']	=	$insertDispose_status;
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorSatus_insertDisposeData']	=	$insertDispose_status;
				}
				//~ $updateAlloc 		=	json_decode ($this->updateContractAlloc(),true);
				//~ if($updateAlloc['errorCode'] == 0){
					//~ $retArr['errorCode']	=	0;
					//~ $retArr['errorStatus_updateContractAlloc']	=	$updateAlloc;
				//~ }else{
					//~ $retArr['errorCode']	=	1;
					//~ $retArr['errorStatus_updateContractAlloc']	=	$updateAlloc;
				//~ }
			}elseif($rd_flg == 'APF'){
				// Call Function related To AppointMent Fix
				$retArr['rd_flg']	=	$rd_flg;
				$retArr['status']	=	'inside APF IF';
				$fn_call_cnt		=	$this->fn_call_cnt;
				$fn_call_cnt		=	$fn_call_cnt+1;
				/*Entry in consolidated table added by Apoorv Agrawal*/				
				$this->insert_apptLogs	=	json_decode($this->insert_apptLogs($fn_call_cnt),true);
				if($this->insert_apptLogs['errorCode'] == 0){
					$retArr['errorStatus_insert_apptLogs'] 	=	$this->insert_apptLogs;
				}else{
					$retArr['errorStatus_insert_apptLogs'] 	=	$this->insert_apptLogs;
				}
				
				/*Create a function to check if the Appt is getting allocated to the previous ME*/
				$move_to_idc_genric	=	0; // new variable declared if 0 move the data to IDC
				$check_appt_me	=	json_decode($this->check_appt_me(),true);
				if($check_appt_me['errorCode'] == 0 && $check_appt_me['errorStatus'] == 'S'){
					$move_to_idc_genric	=	1; // Don't do the IDC data push
				}
				// delete the previous entries before insertion
				$this->deleteResp 	=	json_decode($this->deleteContractAlloction($this->empCode,$MECode),true);
				if($this->deleteResp['errorCode'] == 0){
					$retArr['errorStatus_delete_ContractAlloction'] 	=	$this->deleteResp;
				}else{
					$retArr['errorStatus_delete_ContractAlloction'] 	=	$this->deleteResp;
				}
				$insertDispose_status = json_decode($this->insertDisposeData(),true);
				if($rd_flg == 'APF'){
					if($insertDispose_status['errorStatus_checkSlotForMe'] == "DPF"){
						$respArr_new = array();
						$respArr_new['data_concur'] = $insertDispose_status['data_concur'];//Don't Proceed Further						
						if(is_array($insertDispose_status['data_concur']) && count($insertDispose_status['data_concur']) > 1){
							$concurrentcheck_func_resp 	=	json_decode($this->concurrentcheck_func($respArr_new['data_concur']),true);
							$respArr_new['allocated_resp'] = $concurrentcheck_func_resp['appt_given_to']['parentcode'];
							$respArr_new['allocated_resp_type'] = 2;
							$respArr_new['concurrentcheck_func_resp'] = $concurrentcheck_func_resp;							
							return json_encode($respArr_new);
						}else{
							$Insert_tmeappentry_stat 	=	json_decode($this->insert_tmeappentry($this->empName),true);
							$Insert_in_appt_map =	json_decode($this->insert_tbl_appt_map($this->parentid,$this->MECode,$this->empCode,$this->actionDate,$this->actionTime),true);
							$respArr_new = array();							
							$respArr_new['errorCode_checkSlotForMe'] = 0;
							$respArr_new['errorStatus_checkSlotForMe'] = "PF";//Don't Proceed Further
							$retArr['respArr_new']	 = $respArr_new;
							$retArr['allocated_resp'] = $insertDispose_status['data_concur'][0]['parentcode'];
							$retArr['allocated_resp_type'] = 1;
						}
					}
				}
				$this->insert_tblContratcAllocConsolidate	=	json_decode($this->insert_tblContratcAllocConsolidate($this->empCode,$MECode),true);
				$ins_Call_Recrd 	=	json_decode($this->insertCallRecording(),true);
				if($ins_Call_Recrd['errorCode'] == 0){
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus_CallRecording'] 	=	$ins_Call_Recrd;
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus_CallRecording'] 	=	$ins_Call_Recrd;
				}
				$ins_AppCall_Rec 	=	json_decode($this->insert_AppointmentCall_Rec(),true);
				if($ins_AppCall_Rec['errorCode'] == 0){
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus_ins_AppCall_Rec'] 	=	$ins_AppCall_Rec;
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus_ins_AppCall_Rec'] 	=	$ins_AppCall_Rec;
				}
				// update TABLE grocery_pharmacy 
				$update_pharmacy_Resp 	=	json_decode($this->update_pharmacy(),true);
				if($update_pharmacy_Resp['errorCode'] == 0){
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus_tbl_grocery_pharmacy_preffered_vendors'] 	=	$update_pharmacy_Resp;
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus_tbl_grocery_pharmacy_preffered_vendors'] 	=	$update_pharmacy_Resp;
				}
				if($this->MECode == ''){
					$this->action_timeArr 				= 	explode("-", $this->actionDate);	
					$this->action_DateStr				= 	$this->action_timeArr[0]."-".$this->action_timeArr[1]."-".$this->action_timeArr[2]." ".$this->actionTime.":00";
					$curlParams_temp 					=	array();
					$curlParams_temp['url']				=	SERVICE_IP."/tmeInfo/allocateappt";
					$curlParams_temp['formate'] 		= 	'basic';
					$curlParams_temp['method'] 			=	 'post';
					//$curlParams_temp['headerJson'] 	=	 'json';
					$temp_params['parentid']			=	$this->parentid;
					$temp_params['actiontime']			=	$this->action_DateStr;
					$temp_params['pincode']				=	$this->pincode;
					$temp_params['city']				=	$this->data_city;
					$curlParams_temp['postData'] 		= 	$temp_params; 
					$grab_data							=	Utility::curlCall($curlParams_temp);
					if($grab_data){
						$retArr['grab_status'] 	=	json_encode($grab_data);
					}
				}else{
				}
				$delete_edit_log_stat 	=	json_decode($this->delete_edit_log(),true);
				if($delete_edit_log_stat['errorCode'] == 0){
					//~ $retArr['errorCode'] 	=	0;
					$retArr['errorStatus_delete_edit_log'] 	=	$delete_edit_log_stat;
				}else{
					//~ $retArr['errorCode'] 	=	1;
					$retArr['errorStatus_delete_edit_log'] 	=	$delete_edit_log_stat;
				}
				/*
				 * CALLING FUNCTIONS TO MOVE DATA UP TO IDC SERVER START HERE
				*/
				if($move_to_idc_genric != 1){ // Line addded here Date(02-03-2017)
					$moveto_IDC_extradetails_shadow 	=	json_decode($this->MoveToIDC_extradetails_shadow(),true);
					if($moveto_IDC_extradetails_shadow['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_extradetails_shadow'] 	=	$moveto_IDC_extradetails_shadow;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_extradetails_shadow'] 	=	$moveto_IDC_extradetails_shadow;
					}
					//~ 
					$moveto_IDC_bid_companymaster 	=	json_decode($this->MoveToIDC_bid_companymaster(),true);
					if($moveto_IDC_bid_companymaster['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_bid_companymaster'] 	=	$moveto_IDC_bid_companymaster;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_bid_companymaster'] 	=	$moveto_IDC_bid_companymaster;
					}
					$moveto_IDC_smsbid_temp 	=	json_decode($this->MoveToIDC_smsbid_temp(),true);
					if($moveto_IDC_smsbid_temp['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_smsbid_temp'] 	=	$moveto_IDC_smsbid_temp;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_smsbid_temp'] 	=	$moveto_IDC_smsbid_temp;
					}
					$moveto_IDC_temp_enhancements 	=	json_decode($this->MoveToIDC_temp_enhancements(),true);
					if($moveto_IDC_temp_enhancements['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_temp_enhancements'] 	=	$moveto_IDC_temp_enhancements;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_temp_enhancements'] 	=	$moveto_IDC_temp_enhancements;
					}
					
					$moveto_IDC_alt_address 	=	json_decode($this->MoveToIDC_alt_address_update(),true);
					if($moveto_IDC_alt_address['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_alt_address_update'] 	=	$moveto_IDC_alt_address;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_alt_address_update'] 	=	$moveto_IDC_alt_address;
					}
					$moveto_IDC_alt_address_insert 	=	json_decode($this->MoveToIDC_alt_address_insert(),true);
					if($moveto_IDC_alt_address_insert['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_alt_address_insert'] 	=	$moveto_IDC_alt_address_insert;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_alt_address_insert'] 	=	$moveto_IDC_alt_address_insert;
					}
					$moveto_IDC_geoCodes_insert 	=	json_decode($this->MoveToIDC_geoCodes_insert(),true);
					if($moveto_IDC_geoCodes_insert['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						//~ $retArr['errorStatus_MoveToIDC_geoCodes_insert'] 	=	$moveto_IDC_geoCodes_insert;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						//~ $retArr['errorStatus_MoveToIDC_geoCodes_insert'] 	=	$moveto_IDC_geoCodes_insert;
					}
					$moveto_IDC_catspon_temp_insert 	=	json_decode($this->MoveToIDC_catspon_temp_insert(),true);
					if($moveto_IDC_catspon_temp_insert['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						//~ $retArr['errorStatus_MoveToIDC_catspon_temp_insert'] 	=	$moveto_IDC_catspon_temp_insert;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						//~ $retArr['errorStatus_MoveToIDC_catspon_temp_insert'] 	=	$moveto_IDC_catspon_temp_insert;
					}
					$moveto_IDC_jd_rev_rat_insert 	=	json_decode($this->MoveToIDC_jd_rev_rat_insert(),true);
					if($moveto_IDC_jd_rev_rat_insert['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						//~ $retArr['errorStatus_MoveToIDC_jd_rev_rat_insert'] 	=	$moveto_IDC_jd_rev_rat_insert;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						//~ $retArr['errorStatus_MoveToIDC_jd_rev_rat_insert'] 	=	$moveto_IDC_jd_rev_rat_insert;
					}
					$moveto_IDC_fun_unapproved_geocode 	=	json_decode($this->MoveToIDC_fun_unapproved_geocode(),true);
					if($moveto_IDC_fun_unapproved_geocode['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_fun_unapproved_geocode'] 	=	$moveto_IDC_fun_unapproved_geocode;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_fun_unapproved_geocode'] 	=	$moveto_IDC_fun_unapproved_geocode;
					}
					$moveto_IDC_companymaster_generalInfo_shadow 	=	json_decode($this->MoveToIDC_companymaster_generalInfo_shadow(),true);
					if($moveto_IDC_companymaster_generalInfo_shadow['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_companymaster_generalInfo_shadow'] 	=	$moveto_IDC_companymaster_generalInfo_shadow;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_companymaster_generalInfo_shadow'] 	=	$moveto_IDC_companymaster_generalInfo_shadow;
					}
					$moveto_IDC_temp_intermediate 	=	json_decode($this->moveTo_IDC_temp_intermediate(),true);
					if($moveto_IDC_temp_intermediate['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_temp_intermediate'] 	=	$moveto_IDC_temp_intermediate;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_temp_intermediate'] 	=	$moveto_IDC_temp_intermediate;
					}
					
					$moveto_IDC_business_temp 	=	json_decode($this->moveTo_IDC_business_temp_data(),true);
					if($moveto_IDC_business_temp['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_business_temp_data'] 	=	$moveto_IDC_business_temp;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_business_temp_data'] 	=	$moveto_IDC_business_temp;
					}
					
					$moveto_IDC_appointment_iro 	=	json_decode($this->moveToIdc_tbl_appointment_iro(),true);
					if($moveto_IDC_appointment_iro['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_MoveToIDC_tbl_appointment_iro'] 	=	$moveto_IDC_appointment_iro;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_MoveToIDC_tbl_appointment_iro'] 	=	$moveto_IDC_appointment_iro;
					}
					
					$moveto_IDC_EcsRequest_Info 	=	json_decode($this->moveto_IDC_EcsRequest_Info(),true);
					if($moveto_IDC_EcsRequest_Info['errorCode'] == 0){
						//~ $retArr['errorCode'] 	=	0;
						$retArr['errorStatus_moveto_IDC_EcsRequest_Info'] 	=	$moveto_IDC_EcsRequest_Info;
					}else{
						//~ $retArr['errorCode'] 	=	1;
						$retArr['errorStatus_moveto_IDC_EcsRequest_Info'] 	=	$moveto_IDC_EcsRequest_Info;
					}
				}else{// Line addded here Date(27-02-2017)
					$retArr['errorStatus_data_move_to_idc']	=	'NO'; // added today
				}
			}
		}
		//data correction  api call
		$paramSend['data']['parentid']    = $this->parentid;
		$paramSend['disposition'] = $this->dispose_type;
		$paramSend['empcode'] 	  = $this->empCode;
		$paramSend['city']		  = $this->data_city;
		$paramSend['request']		  = 'setDispositon';
		$result = $this->jsonInsertDisp($paramSend); 
		
		
		if($rd_flg == 'APF'){
			$update_appLogs_stat	=	json_decode($this->update_appLogs($this->parentid),true);
			//~ echo "<pre>";print_r($update_appLogs_stat);die;
			$retArr['update_appLogs']	=	$update_appLogs_stat;
		}
		$hotDataUpdate 		=	json_decode($this->hotDataSms(),true);
		if($hotDataUpdate['errorCode'] == 0){
			$retArr['errorSatus_hotData']	=	$hotDataUpdate;
		}else{
			$retArr['errorSatus_hotData']	=	$hotDataUpdate;
		}
		return json_encode($retArr);
	}	
	/*
	 * Fucntion To Decide Grab Or Non Grab Flow
	*/
	public function decideGrab_NonGrab(){
		$retArr 	=	array();
		$con_local 	=	new DB($this->db['db_local']);
		$qur_grab_chk 	=	"SELECT  COUNT(1) as count FROM tbl_grabapptPincode WHERE  pincode='".$this->pincode."'";
		$con_grab_chk 	=	$con_local->query($qur_grab_chk);
		$pin_chk_obj_cn = 	$con_local->fetchData($con_grab_chk);
		if($con_grab_chk){
			if($pin_chk_obj_cn['count']==0){
				$retArr['data']['isGrabFlow'] 	=	0;// True Grab Flow
			}else{
				$retArr['data']['isGrabFlow'] 	=	1;// False Non Grab Flow
			}
			$retArr['errorCode'] 		=	0;
			$retArr['errorStatus']		=	'query run';
		}else{
			$retArr['errorCode'] 		=	1;
			$retArr['errorStatus']		=	'query failed';
		}
		return json_encode($retArr);
	}
	
	/*
	 * Funcion to get data from Table: mktgEmpMap
	*/
	public function get_mktgEmpMap_data(){
		$con_local 	=	new DB($this->db['db_local']);
		$retArr 	=	array();
		$qu_mktgEmpMap 		=	"Select mktEmpCode,autoId,rowId,mktEmpType FROM mktgEmpMap where mktEmpCode = '".$this->empCode."'";
		$con_mktgEmpMap 	=	$con_local->query($qu_mktgEmpMap);
		$num_mktgEmpMap		=	$con_local->numRows($con_mktgEmpMap);
		if($num_mktgEmpMap > 0) {
			$retArr['data'] 		=	$con_local->fetchData($con_mktgEmpMap);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
		//mktEmpCode,autoId,rowId,mktEmpType
	}
	/*
	 * Function to get data from tbl_companymaster_generalinfo_shadow
	 * changed as per Rohit Kaul Sir
	*/	
	public function get_generalinfo_shadow(){
		$db_tme		=	new DB($this->db['db_tme']);//tme_jds
		$retArr 	=	array();
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "nationalid,sphinx_id,regionid,companyname,parentid,docid,country,state,city,display_city,area,area_display,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,paid,othercity_number,blockforvirtual,callconnect,virtualNumber,virtual_mapped_number,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,mobile_admin,cc_status";
			$row_generalinfo_shadow = $this->mongo_obj->getData($mongo_inputs);
			$num_generalinfo_shadow = count($row_generalinfo_shadow);
		}
		else
		{		
			$query_generalinfo_shadow 	= 	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,docid,country,state,city,display_city,area,area_display,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,paid,othercity_number,blockforvirtual,callconnect,virtualNumber,virtual_mapped_number,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,mobile_admin,cc_status FROM tbl_companymaster_generalinfo_shadow WHERE parentid='".$this->parentid."';";
			$conn_generalinfo_shadow 	=	$db_tme->query($query_generalinfo_shadow);
			$num_generalinfo_shadow		=	$db_tme->numRows($conn_generalinfo_shadow);
			$row_generalinfo_shadow 	= 	$db_tme->fetchData($conn_generalinfo_shadow);
			$current_paid_status  = $this->getCurrentPaidStatus($row_generalinfo_shadow['parentid']);
			$row_generalinfo_shadow['paid'] = $current_paid_status['result']['paid'];  	
		}
		if($num_generalinfo_shadow > 0) {
			$retArr['data'] 		=	$row_generalinfo_shadow;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	/*
	 * Function To get TME or ME INFO from mktgEmpMaster
	*/
	public function get_TME_ME_data($empCode){
		$this->meTmeCode		= 	$empCode;
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$query_mktgEmpMaster 	=	"SELECT autoId,mktEmpCode,oldTmeCode,empType,empName,tmeClass,empParent,phoneNo,extn,mobile,emailId,state,city,nation,nat_code,
									state_code,city_code,datetime, Approval_flag,allocID,secondary_allocid,level,irodata,data_city,dnc_type,allocation_flag,dummy_flag 
									FROM mktgEmpMaster WHERE mktEmpCode='".$this->meTmeCode."';";
		$conn_mktgEmpMaster 	=	$con_local->query($query_mktgEmpMaster);
		$num_mktgEmpMaster 		=	$con_local->numRows($conn_mktgEmpMaster);
		if($num_mktgEmpMaster > 0){
			$retArr['data'] 		=	$con_local->fetchData($conn_mktgEmpMaster);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	/*
	 * Delete from tblContractAllocation for Appointment Fix & Appointment Re-Fix
	*/
	public function deleteContractAlloction($empCode,$mecode){
		$con_local		=	new DB($this->db['db_local']);
		$con_idc_ar		=	new DB($this->db['db_idc']);
		$retArr			=	array();
		//~ $delete_query 			= 	"DELETE FROM tblContractAllocation WHERE 
									//~ contractCode='".$this->parentid."' AND (parentCode='".$empCode."') AND (allocationType='25' OR allocationType='99')";
									
		$delete_query 			= 	"UPDATE tblContractAllocation SET cancel_flag=1 WHERE contractCode='".$this->parentid."' AND (allocationType='25' OR allocationType='99')";
		$con_delete	 			=	$con_local->query($delete_query);
		$con_delete_from_IDC	= 	$con_idc_ar->query($delete_query);
		if($con_delete && $con_delete_from_IDC){
			$retArr['errorCode'] 	=	'0';
			$retArr['errorStatus'] 	=	'DELETION DONE from both database servers';
		}else{
			$retArr['errorCode'] 	=	'1';
			$retArr['errorStatus'] 	=	'DELETION Failed';
		}
		$fp = fopen('../../logs/log_tme/reapp'.date('M').'.txt', 'a');
		fwrite($fp, '#'.$delete_query.'#');
		fclose($fp);
		return json_encode($retArr);
	}
	public function concurrentcheck_func($concurrent_arr){
		$con_local = new DB($this->db['db_local']);
		$con_idc_ar = new DB($this->db['db_idc']);
		$retArr =	array();
		$retArr['appt_given_to'] = $concurrent_arr[0];
		if(is_array($concurrent_arr)){		
			unset($concurrent_arr[0]);
			if(count($concurrent_arr) > 0){
				foreach($concurrent_arr as $key=>$value){
					$delete_query = "DELETE FROM tblContractAllocation WHERE contractCode='".$value['contractCode']."' AND (parentcode ='".$value['parentcode']."') AND empcode = '".$value['empcode']."' AND (allocationType='25' OR allocationType='99') AND actionTime = '".$value['actionTime']."'";
					$con_delete	 			=	$con_local->query($delete_query);
					$con_delete_from_IDC	= 	$con_idc_ar->query($delete_query);
					if($con_delete && $con_delete_from_IDC){
						$retArr['errorCode'][$value] 	=	'0';
						$retArr['errorStatus'][$value] 	=	'DELETION DONE from both database servers';
					}else{
						$retArr['errorCode'][$value] 	=	'1';
						$retArr['errorStatus'][$value] 	=	'DELETION Failed';
					}
					//~ $delete_query = "UPDATE db_justdial_products.tblContractAllocation_consolidated SET cancel_flag = 1 WHERE contractCode='".$value['contractCode']."' AND (parentcode ='".$value['parentcode']."') AND empcode = '".$value['empcode']."' AND (allocationType='25' OR allocationType='99') AND actionTime = '".$value['actionTime']."'";
					$delete_query = "DELETE FROM db_justdial_products.tblContractAllocation_consolidated WHERE contractCode='".$value['contractCode']."' AND (parentcode ='".$value['parentcode']."') AND empcode = '".$value['empcode']."' AND (allocationType='25' OR allocationType='99') AND actionTime = '".$value['actionTime']."'";
					$con_delete_from_IDC = $con_idc_ar->query($delete_query);
				}
			}
		}
		$retArr['appt_deleted_arr'] = $concurrent_arr;
		return json_encode($retArr);
	}
	/*
	 * update TABLE tbl_grocery_pharmacy_preffered_vendors
	*/
	public function update_pharmacy(){
		$con_local		=	new DB($this->db['db_local']);
		$retArr			=	array();
		$updt_groc_pharmacy 	=	"UPDATE tbl_grocery_pharmacy_preffered_vendors SET 
									alloc_type = '".$this->dispose_type."', alloc_time = '".date('Y-m-d H:i:s')."', parent_code = '".$this->empCode."', emp_code = '".$this->MECode."' WHERE parentid = '".$this->parentid."'";
		$con_groc_pharmacy 		=	$con_local->query($updt_groc_pharmacy);
		if($con_groc_pharmacy){
			$retArr['errorCode'] 	=	'0';
			$retArr['errorStatus'] 	=	'UPDATE DONE tbl_grocery_pharmacy_preffered_vendors';
		}else{
			$retArr['errorCode'] 	=	'1';
			$retArr['errorStatus'] 	=	'UPDATE Failed tbl_grocery_pharmacy_preffered_vendors';
		}
		return json_encode($retArr);
	}
	
	/*
	 * dumpIntoDataCorrection() to Call this File dataCorrectionIntermediate.php
	*/
	public function dumpIntoDataCorrection($empcode){
		$curlParams2 				= 	array();
		$paramsGET					= 	array();
		$curlParams2['url'] 		= 	DECS_TME.'/business/dataCorrectionIntermediate.php';
		$curlParams2['formate'] 	=	'basic';
		$curlParams2['method'] 		=	'post';
		$curlParams2['headerJson']	=	'json';
		$paramsGET['parentid'] 		=	$this->parentid;
		$paramsGET['empcode'] 		=	$empcode;
		$paramsGET['allocType'] 	=	$this->dispose_type;
		$curlParams2['postData'] 	= 	json_encode($paramsGET);
		$tmeInfo					=	Utility::curlCall($curlParams2);
		return json_encode($tmeInfo);
	}
	
	/*
	 * function to getMainTabGenData()
	*/
	public function getMainTabGenData(){
		$con_iro						=	new DB($this->db['db_iro']);//db_iro
		/* $qu_companymaster_generalinfo	=	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		$con_companymaster_generalinfo	=	$con_iro->query($qu_companymaster_generalinfo);
		$num_companymaster_generalinfo	=	$con_iro->numRows($con_companymaster_generalinfo); */

		$comp_params = array();
		$comp_params['data_city'] 	= SERVER_CITY;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'disposition_model';		

		$comp_api_arr 	= 	array();
		$comp_api_arr	=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),TRUE);
		
		if($comp_api_arr['errors']['code']=='0' && count($comp_api_arr['results']['data'])>0) {
			$retArr['data']			=	$comp_api_arr['results']['data'][$this->parentid];
			$current_paid_status  = $this->getCurrentPaidStatus($retArr['data']['parentid']);
			$retArr['data']['paid'] = $current_paid_status['result']['paid'];  	
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	/*
	 * 
	*/
	public function get_altaddress(){
		$retArr			=	array();
		$currentTime	=	date("Y-m-d H:i:s");
		$db_tme			=	new DB($this->db['db_tme']);
		$quAltAddress 	= 	"SELECT companyname,country,state,city,area,building_name,street,landmark,pincode FROM tbl_companymaster_extradetails_altaddress_shadow WHERE parentid = '".$this->parentid."' AND insertdate > DATE_SUB('".addslashes($currentTime)."', INTERVAL 1 HOUR);";
		$conAltAddress	=	$db_tme->query($quAltAddress);
		$numAltAddress	=	$db_tme->numRows($conAltAddress);
		if($numAltAddress>0){
			$retArr['data'] 		=	$db_tme->fetchData($conAltAddress);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
		//
	}
	
	/*
	 * update function only in the case of Call back/22 and Follow-Up/24
	*/
	public function updateContractAlloc(){
		$con_local 	=	new DB($this->db['db_local']);
		$retArr 	=	array();
		$sqlUpdatePrevAlloc 	= 	"UPDATE tblContractAllocation SET flgAllocStatus='0' WHERE contractCode='".$this->parentid."' AND parentCode='".$this->empCode."' AND allocationType=".$this->dispose_type."";
		$conUpdatePrevAlloc 		=	$con_local->query($sqlUpdatePrevAlloc);
		if($conUpdatePrevAlloc) {
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'UPDATE tblContractAllocation DONE';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'UPDATE tblContractAllocation FAIL';
		}
		return json_encode($retArr);
	}
	
	/*
	 * Common Insertion Function For All Disposition APART From 25/99(App Fix/App Refix) And 22/24(CallBack and FollowUp)
	 * Modifying function so that it can do insertion for all the allocation  or disposition type
	 * 	empCode is MECode
	 *  it is empty in case of grab flow
	 * it same as TME CODE For followUp ANd Call BACK
	*/
	public function insertDisposeData(){
		$retA			=	array();
		$con_local		=	new DB($this->db['db_local']); 
		$db_idc_ar		=	new DB($this->db['db_idc']);
		$retArr			=	array();
		$currentTime	=	date("Y-m-d H:i:s");// is server time time 
		/*
		 * Insertion is happening for all disposition_type
		*/
		//MRK
			//~ $this->UpdtContractAllocation_CmpName();
		//MRK
		$insert_intbl	=	'';
		$get_teamName_tme_str = '';
		if($this->dispose_type !='25' && $this->dispose_type !='99' && $this->dispose_type !='24' && $this->dispose_type !='22' && $this->dispose_type !='317'){
			$sqlCondition  	=	"";
			$tblAllocValue	=	'';
			$parentCode		=	'';	
			$empCode		=	$this->empCode;
			$actionTime		=	addslashes($currentTime);
			$tme_search_qr	=	'';
			$insertFlag		=	0;
			$insert_intbl	=	0;
		}else{
			$get_allocId_tme	=	json_decode($this->get_TME_ME_data($this->empCode),true);
			if($get_allocId_tme['errorCode'] == 0){
				$get_teamName_tme	=	json_decode($this->get_team_type($get_allocId_tme['data']['allocID']),true);
				
				if($get_teamName_tme['errorCode'] == 0){
					$get_teamName_tme_str	=	$get_teamName_tme['data']['team_name'];
				}
			}
			$tme_search_qr	=	",parentcode='".$this->empCode."'";
			$parentCode		=	$this->empCode;
			$empCode		=	$this->MECode;
			$action_timeArr = 	explode("-",$this->actionDate);	
			
			$condition_dt	=	explode(":",$this->actionTime);
			
			$actionTime		= 	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$this->actionTime.":00";
			$tblAllocQuery 	=	',parentCode, tmename, mename, instruction,t20flag,area,pincode,alt_address_flag,data_city,contact_person,categories_data,competitors,me_email_id,me_mobile_number,team_type,paid';
			
			$con_fin = new DB($this->db['db_finance']);
			$paid_flg = 0;
			$paid_check_qr = "SELECT parentid FROM db_finance.tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND balance > 0 LIMIT 1";
			$paid_check_qr_con = $con_fin->query($paid_check_qr);
			$lack_handl_qry_numRows = $con_fin->numRows($paid_check_qr_con);
			if($lack_handl_qry_numRows > 0){
				$paid_flg = 1;
			}
			
			$cat_data	=	array();
			$cat_data	=	json_decode($this->get_business_temp_data(),true);
			$categories_data	=	'';
			$competitors_data	=	'';
			if($cat_data['errorCode'] == 0){
				$cat_name_arr	=	array();
				$cat_id_arr		=	array();
				if(($cat_data['data']['categories'] !='' && !empty($cat_data['data']['categories']) && $cat_data['data']['categories'] !=null && $cat_data['data']['categories'] != 'null') && ($cat_data['data']['catIds'] !='' && !empty($cat_data['data']['catIds']) && $cat_data['data']['catIds'] !=null && $cat_data['data']['catIds'] != 'null')){
					$catidarray		=	array();
					$cat_name_arr	=	explode("|P|",trim($cat_data['data']['categories'],"|P|"));
					$cat_id_arr		=	explode("|P|",trim($cat_data['data']['catIds'],"|P|"));
					
					$catidarray 	= 	explode('|P|',$cat_data['data']['catIds']);
					$catidarray 	= 	array_unique($catidarray);
					$catidarray 	= 	array_filter($catidarray);
					$categorymaster_data	=	json_decode($this->get_compt_cat($catidarray),true);
					
					$area_shadow_gen 	= 	$this->genshadowArr['area'];
					$pincode_shadow 	= 	$this->genshadowArr['pincode'];
					$street_shadow 		= 	$this->genshadowArr['street'];
					$AREA_shadow 		= 	$area_shadow_gen.'|&|'.$street_shadow;
					$competitors_data	=	'';
					if($categorymaster_data['errorCode'] == 0){
						$catid			=	$categorymaster_data['data']['catid'];
						$category_name	=	$categorymaster_data['data']['category_name'];
						$categories_data	=	'';
						$categories_data	.=	$category_name.'|~|'.$catid.',';
						$categories_data	=	trim($categories_data,",");
					}					
				}
			}
			if(trim($this->area) == ""){
				$this->area = $this->getPopularArea($this->pincode);
			}
			$tblAllocValue 	=	',"'.$parentCode.'", "'.addslashes(stripslashes($this->empName)).'", "'.addslashes(stripslashes($this->MEName)).'", "'.addslashes(stripslashes($this->instrct)).'",0,"'.addslashes(stripslashes($this->area)).'","'.$this->pincode.'","'.$this->altAddres_flg.'","'.$this->data_city.'","'.addslashes(stripslashes($this->contact_person)).'","'.addslashes(stripslashes($categories_data)).'","'.$competitors_data.'","'.addslashes(stripslashes($this->ME_email)).'","'.$this->ME_mobile.'","'.addslashes(stripslashes($get_teamName_tme_str)).'","'.$paid_flg.'"';
			//~ $tblAllocValue 	=	',"'.$parentCode.'", "'.addslashes(stripslashes($this->empName)).'", "'.addslashes(stripslashes($this->MEName)).'", "'.addslashes(stripslashes($this->instrct)).'","","'.addslashes(stripslashes($this->area)).'","'.$this->pincode.'","'.$this->altAddres_flg.'","'.$this->data_city.'","'.addslashes(stripslashes($this->contact_person)).'","'.addslashes(stripslashes($categories_data)).'","'.$competitors_data.'","'.addslashes(stripslashes($this->ME_email)).'","'.$this->ME_mobile.'"';
			
			$con_date		=	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$condition_dt[0];
			$lack_handl_qry 	=	"SELECT IF(DATE_FORMAT(actionTime, '%Y-%m-%d %H') = '".$con_date."','1','0') AS conditn_date FROM tblContractAllocation WHERE contractCode = '".$this->parentid."'  AND (allocationType='25' || allocationType='99') ORDER BY actionTime DESC";
			$lack_handl_qry_Con		=	$con_local->query($lack_handl_qry);
			$lack_handl_qry_data 	= 	$con_local->fetchData($lack_handl_qry_Con);
			if($lack_handl_qry_data['conditn_date'] == 1 && !empty($lack_handl_qry_data['conditn_date'])){
				$insertFlag	= 1;
			}else{
				$insertFlag	= 0;
			}
			$one_Check_qur	=	"SELECT contractCode FROM d_jds.tblContractAllocation WHERE actionTime BETWEEN  ('".$actionTime."' - INTERVAL 60 MINUTE)  AND ('".$actionTime."' + INTERVAL 60 MINUTE)  AND allocationtype IN (25,99) AND cancel_flag = 0 AND empCode='".$empCode."'";
			$lack_handl_qry_Con		=	$con_local->query($one_Check_qur);
			$lack_handl_qry_numRows 	= 	$con_local->numRows($lack_handl_qry_Con);
			if($lack_handl_qry_numRows == 0){
				$insert_intbl	=	0;
			}else{
				$insert_intbl	=	1;
			}
		}
		//~ $this->update_tbl_tme_data_search($this->parentid,$this->dispose_type,$currentTime); // Commenting on 2018-07-06 as tbl_tme_data_search Does not have any data
		/*$select_qr_concurrent = "Select contractCode FROM tblContractAllocation WHERE empCode = '".$empCode."' AND actionTime = '".$actionTime."'";
		$select_qr_concurrent_Con	=	$con_local->query($select_qr_concurrent);
		$select_qr_concurrent_numRows 	= 	$con_local->numRows($select_qr_concurrent_Con);
		if($select_qr_concurrent_numRows > 0){
			$retArr['errorCode'] =	0;
			$retArr['errorStatus_tblContractAllocation_IDC_lack'] = 'Data inserted in tblContractAllocation at IDC';
			$retArr['errorStatus_tblContractAllocation_lack'] = 'Data inserted in tblContractAllocation';			
			$retArr['errorStatus_tbl_tmesearch_lack'] = 'Data inserted in tbl_tmesearch';
		}else{*/		
			$disposeInsertQuery		=	"INSERT INTO tblContractAllocation (empCode,contractCode,allocationType,allocationTime,actionTime,compname ".$tblAllocQuery.") 
									VALUES 
									('".$empCode."', '".$this->parentid."','".$this->dispose_type."','".addslashes($currentTime)."','".addslashes($actionTime)."','".addslashes($this->genshadowArr['companyname'])."'".$tblAllocValue.")";
			$insertdisposeQueryCon	=	$con_local->query($disposeInsertQuery);
			if($this->dispose_type =='25' || $this->dispose_type =='99'){
				// Push to IDC server data of tblContractAllocation
				$Con_insert_dispose_AF_AR  = $db_idc_ar->query($disposeInsertQuery);
				$lastInsertId = $con_local->lastInsertedId();
				if($Con_insert_dispose_AF_AR){
					$retArr['errorCode']	=	0;
					$retArr['errorStatus_tblContractAllocation_IDC']	=	'Data inserted in tblContractAllocation at IDC';
				}else{
					$retArr['errorCode']	=	1;
					$retArr['errorStatus_tblContractAllocation_IDC']	=	'Data inserted Failed in tblContractAllocation at IDC';
				}
				sleep(1);
				$one_Check_qur	=	"SELECT COUNT(1) as totCount FROM d_jds.tblContractAllocation WHERE actionTime BETWEEN  ('".$actionTime."' - INTERVAL 60 MINUTE)  AND ('".$actionTime."' + INTERVAL 60 MINUTE)  AND allocationtype IN (25,99) AND cancel_flag=0 AND empCode='".$empCode."'";
				$lack_handl_qry_Con		=	$con_local->query($one_Check_qur);
				$lack_handl_qry_data 	= 	$con_local->fetchData($lack_handl_qry_Con);			
				if($lack_handl_qry_data['totCount'] > 0){
					$retArr['errorCode_checkSlotForMe']	=	1;
					$retArr['errorStatus_checkSlotForMe']	=	"DPF";//Proceed Further
					$one_Check_qur_new	=	"SELECT empcode,parentcode,contractCode,actionTime FROM d_jds.tblContractAllocation WHERE actionTime BETWEEN  ('".$actionTime."' - INTERVAL 60 MINUTE)  AND ('".$actionTime."' + INTERVAL 60 MINUTE)  AND allocationtype IN (25,99) AND cancel_flag=0 AND empcode='".$empCode."' ORDER BY parentcode DESC";
					$lack_handl_qry_Con_new = $con_local->query($one_Check_qur_new);
					while($lack_handl_qry_data_new = $con_local->fetchData($lack_handl_qry_Con_new)){
						$retArr['data_concur'][] = $lack_handl_qry_data_new;
					}
					
				}else{
					$one_Check_qur_new	=	"SELECT empcode,parentcode,contractCode,actionTime FROM d_jds.tblContractAllocation WHERE actionTime BETWEEN  ('".$actionTime."' - INTERVAL 60 MINUTE)  AND ('".$actionTime."' + INTERVAL 60 MINUTE)  AND allocationtype IN (25,99) AND cancel_flag=0 AND empcode='".$empCode."' ORDER BY parentcode DESC";
					$lack_handl_qry_Con_new = $con_local->query($one_Check_qur_new);
					while($lack_handl_qry_data_new = $con_local->fetchData($lack_handl_qry_Con_new)){
						$retArr['data_concur'][] = $lack_handl_qry_data_new;
					}					
					$retArr['errorCode_checkSlotForMe']	=	1;
					$retArr['errorStatus_checkSlotForMe']	=	"DPF";//Don't Proceed Further
				}
				$appt_arr_params = array();
				$appt_arr_params['allocation_type'] = $this->dispose_type;
				$appt_arr_params['appointment_time'] = $actionTime;
				$appt_arr_params['allocation_time'] = $currentTime;
				$appt_arr_params['parentid'] = $this->parentid;
				$appt_arr_params['city'] = $this->data_city;
				$appt_arr_params['comp_name'] = addslashes(stripslashes($this->genshadowArr['companyname']));
				$appt_arr_params['tme_code'] = $parentCode;
				$appt_arr_params['tme_name'] = addslashes(stripslashes($this->empName));
				$appt_arr_params['me_code'] = $empCode;
				$appt_arr_params['me_name'] = addslashes(stripslashes($this->MEName));
				$appt_arr_params['allocation_id'] = $lastInsertId;
				$appt_arr_params['area'] = addslashes(stripslashes($this->area));
				$appt_arr_params['pincode'] = $this->pincode;
				$appt_arr_params['instruction'] = addslashes(stripslashes($this->instrct));
				$appt_arr_params['category'] = addslashes(stripslashes($categories_data));
				$appt_arr_params['cancel_flag'] = 0;
				$appt_arr_params['action_time'] = $actionTime;
				$this->call_insertNewApptApi($appt_arr_params);
			}
			if($insertdisposeQueryCon){
				$retArr['errorCode']	=	0;
				$retArr['errorStatus_tblContractAllocation']	=	'Data inserted in tblContractAllocation';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus_tblContractAllocation']	=	'Data inserted Failed in tblContractAllocation';
			}
			
			$updtbl_tmesearch		=	"UPDATE tbl_tmesearch 
										SET 
										empCode='".$this->empCode."',allocationType='".$this->dispose_type."',allocationTime='".addslashes($currentTime)."',actionTime='".$actionTime."' WHERE parentid='".$this->parentid."'";
			$updtbl_tmesearchCon	=	$con_local->query($updtbl_tmesearch);
			if($updtbl_tmesearchCon){
				$retArr['errorCode']					=	0;
				$retArr['errorStatus_tbl_tmesearch']	=	'Data inserted in tbl_tmesearch';
			}else{
				$retArr['errorCode']					=	1;
				$retArr['errorStatus_tbl_tmesearch']	=	'Data inserted Failed in tbl_tmesearch';
			}
		//}
		return json_encode($retArr);
	}
	

// MRK
	public function UpdtContractAllocation_CmpName(){
		$retArr 	=	array();
		$con_local 	=	new DB($this->db['db_local']);
		$Bcompany_name_length =  strlen($this->compName);		
		$bName 				  = 	$this->compName;
		if($Bcompany_name_length > 6)
			{	
				$bname_arr = explode(" ",$bName);						
				foreach ($bname_arr as $key => $value) 
				{				
					if(strlen($bname_arr[$key])<'6')				
					{
					  if(ctype_upper($bname_arr[$key]))
						{ $company_word_array[$key] = $bname_arr[$key];	}
						else
						{ $company_word_array[$key] = ucwords(strtolower($bname_arr[$key]));		}
					}
				else
					{ $company_word_array[$key] = ucwords(strtolower($bname_arr[$key]));}
				}
			  	$bName = implode(" ",$company_word_array);	
				$this->compName = $bName;					  	
        }
        if($this->compName != '' || !empty($this->compName)){
			$this->genshadowArr['companyname']	=	addslashes($this->compName);	// update now as the insertion of tblcontractAllocation Table company fetch from the table
		}else{
			$this->genshadowArr['companyname']	=	addslashes(stripslashes($this->genshadowArr['companyname']));	// update now as the insertion of tblcontractAllocation Table company fetch from the table
		}
		$Query_check_company = "select * from tblContractAllocation where contractCode='".$this->parentid."'";
		$res_conay_chk = $con_local->query($Query_check_company);
		$res_num_rows = $con_local->numRows($res_conay_chk);
		if($res_num_rows>0)
		{
			$update_CntrctAllocation = "update tblContractAllocation set compname='".$this->genshadowArr['companyname']."' where contractCode='".$this->parentid."'";
			$con_update_compny_name = $con_local->query($update_CntrctAllocation);
		}	
		if($con_update_compny_name){			
			$retArr['errorCode'] 		=	0;
			$retArr['errorStatus']		=	'Company name Updated in tblcontractAllocation';
		}else{
			$retArr['errorCode'] 		=	1;
			$retArr['errorStatus']		=	'Company name Not Updated in tblcontractAllocation Tab';
		}
		return json_encode($retArr);
	}

	/*
	 * A generic function to insert data into tbl_me_tme_sink
	*/
	public function insert_into_sink($empCode){
		$con_local		=	new DB($this->db['db_local']); 
		$retArr			=	array();
		$querInsert_tbl_me_tme_sink 	= 	"INSERT INTO tbl_me_tme_sink SET parentid = '".$this->parentid."',
											empId = '".$empCode."',mod_flag = 0,approval_flag = 0,allocationType='".$this->dispose_type."'  
											ON DUPLICATE KEY UPDATE
											empId = '".$empCode."',mod_flag = 0,approval_flag = 0,allocationType='".$this->dispose_type."'";
		$Insert_tbl_me_tme_sink_con 	=	$con_local->query($querInsert_tbl_me_tme_sink);
		if($Insert_tbl_me_tme_sink_con){
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus']	=	'Insertion Done in tbl_me_tme_sink';
		}else{
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus']	=	'Insertion Failed in tbl_me_tme_sink';
		}
		return json_encode($retArr);
	}
	
	
	/*
	 * this Function Is Called For Following Dispositions ONLY:
	 * dispose_type=1(Does Not Exists in TABLE tbl_disposition_info), 
	 * dispose_type=7(Wrong Number), 
	 * dispose_type=8(Does Not Exists in TABLE tbl_disposition_info),
	 * dispose_type=11(Does Not Exists in TABLE tbl_disposition_info),
	 * dispose_type=12(Company Closed),
	 * dispose_type=98(Not in Business),
	 * dispose_type=114(Discard Call)
	 */
	public function tmeFeedback(){
		$retArr 					=	array();
		/*
		$con_local 					=	new DB($this->db['db_local']);
		$currentTime_tmeFeedback	=	date("Y-m-d H:i:s");
		$query_tmeFeedback 			=	"INSERT INTO tbl_tmeFeedback_dataCorrection(contractid, companyname, empcode, empname, allocationType, allocationTime,contact_details) 
										VALUES 
										('".addslashes(stripslashes(trim($this->parentid)))."', '".addslashes(stripslashes(trim($this->compName)))."', '".$this->empCode."', '".addslashes(stripslashes($this->empName))."','".$this->dispose_type."','".addslashes($currentTime_tmeFeedback)."','".addslashes(stripslashes($this->contracts))."')";
		$insert_tmeFeedback 		= 	$con_local->query($query_tmeFeedback);
		if($insert_tmeFeedback){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus_tmeFeedback']	=	'Insertion Success in tmeFeedback';
		}else{
			$retArr['errorCode']	=	0;
			$retArr['errorStatus_tmeFeedback']	=	'Insertion Failed in tmeFeedback';
		}
		*/
		$retArr['errorCode']	=	0;
		$retArr['errorStatus_tmeFeedback']	=	'Insertion Success in tmeFeedback';
		return json_encode($retArr);
	}
	/*
	 * Function to update Visiting Card 
	*/
	public function updateVistingCard(){
		$retA 		=	array();
		$retArr 	=	array();
		$con_local 	=	new DB($this->db['db_local']);	
		$query_visitingcard_contracts 	=	"select parentid,createdon from tbl_visitingcard_contracts where parentid='".$this->parentid."' and transfer_flag =0 ";
		$con_visitingcard_contracts 	=	$con_local->query($query_visitingcard_contracts);
		$currentTime 					=	date("Y-m-d H:i:s");
		$count							=	$con_local->numRows($con_visitingcard_contracts);
		if($count>0){
			while($vistingCardval = $con_local->fetchData($con_visitingcard_contracts)){
				if(strtotime($vistingCardval['createdon']."+ 30 min") >=  strtotime($currentTime)){
					$this->insertIntoLogs();
					$update_visitingcard_contracts 		= "update tbl_visitingcard_contracts set transfer_flag =1 where parentid='".$this->parentid."'";
					$con_up_visitingcard_contracts 		=  $con_local->query($update_visitingcard_contracts);
					
					if($con_up_visitingcard_contracts){
						$retArr['Update_errorCode']							=	0;
						$retArr['errorSatus_visitingcard_contracts']	=	"Table visitingcard_contracts updated";
					}else{
						$retArr['Update_errorCode']							=	0;
						$retArr['errorSatus_visitingcard_contracts']	=	"Table visitingcard_contracts update Fail";
					}
				}
			}
			$retArr['errorCode']							=	0;
			$retArr['errorStatus_visitingcard_contracts']	=	'Entry Found in Table visitingcard_contracts';
		}else{
			$retArr['errorCode']							=	1;
			$retArr['errorStatus_visitingcard_contracts']	=	'No entry Found in Table visitingcard_contracts';
		}
		return json_encode($retArr);
	}
	
	/*
	 * Function to insert into the logs file and server
	*/
	public function insertIntoLogs(){
		$db_tme		=	new DB($this->db['db_tme']); // tme_jds
		$log_data 	=	array();
		$post_data 	=	array();
		$log_data['url'] 		= 	'http://192.168.17.144/logs.php';
		$post_data['ID']        =	$this->parentid;                
		$post_data['PUBLISH'] 	=	'TME';         	
		$post_data['ROUTE']     =	'Visiting Card';   		
		$post_data['USER_ID']	=	"".$this->empCode."";
		$post_data['MESSAGE']   =	'Mandatory Fileds of Visiting card contract';	
		$log_data['method'] 	=	'post';
		$post_data['CRITICAL_FLAG']  = 1 ;
		$logJoinquery 			=	"SELECT b.companyname,b.landline,b.mobile,b.pincode,a.categories FROM tme_jds.tbl_business_temp_data AS a JOIN  tme_jds.tbl_companymaster_generalinfo_shadow AS b ON a.contractid = b.parentid WHERE contractid = '".$this->parentid."'";
		$con_join_log 			=	$db_tme->query($logJoinquery);
		while($logdata = $db_tme->fetchData($con_join_log)) {
			$post_data['DATA']['QUERY'] 		=   $logJoinquery;					
			$post_data['DATA']['companyname'] 	= 	$logdata['companyname'];
			$post_data['DATA']['landline']	 	= 	$logdata['landline'];
			$post_data['DATA']['mobile']	    = 	$logdata['mobile'];
			$post_data['DATA']['pincode']     	= 	$logdata['pincode'];	
			$post_data['DATA']['categories']	= 	$logdata['categories'];
		}
		$log_data['formate'] 	= 	'basic';
		$log_data['postData'] 	= 	http_build_query($post_data);
		$tmeInfo				=	Utility::curlCall($log_data);
		return json_encode($tmeInfo);
	}
	/*function to update Table tmeappentry
	*/
	public function insert_tmeappentry($TMEName){
		$con_fin		 	=	new DB($this->db['db_finance']);
		$currentTime		=	date("Y-m-d H:i:s");
		$bus_temp_data_arr	=	array();
		$action_date_time 	= 	explode("-",$this->actionDate);	
		$actionTime			= 	$action_date_time[0]."-".$action_date_time[1]."-".$action_date_time[2]." ".$this->actionTime.":00";
		$date_t 			= 	explode(' ',$actionTime);
		$bus_temp_data_arr 	=	json_decode($this->get_business_temp_data(),true);
		if($bus_temp_data_arr['errorCode'] ==0){
			$bus_temp_data_arr 			=	$bus_temp_data_arr['data'];
			$bus_temp_data_cat_withP 	=	$bus_temp_data_arr['categories'];
			$bus_temp_data_cat_withoutP = str_replace( "|P|", ",",$bus_temp_data_cat_withP);
		}
		if($this->shadow_failFlag == 0){
			$enter_callngo 	=	'ASSIGN JOB JDRR';
		}else{
			$enter_callngo 	=	'ASSIGN JOB';
		}
		$params_new = array();
		$params_new['url'] = HRMODULE . '/employee/fetch_employee_info/' . $this->MECode;
		$params_new['formate'] = 'basic';
		$content_emp = json_decode(Utility::curlCall($params_new),true);
		$me_reporting_head 		= '';
		$me_reporting_head_mail = '';
		$me_reporting_head_code = '';
		$me_reporting_head = $content_emp['data']['reporting_head'];
        $me_reporting_head_mail = $content_emp['data']['reporting_head_mail'];
        $me_reporting_head_code = $content_emp['data']['reporting_head_code'];
        
        $me_reporting_head_2_empcode = $content_emp['data']['reporting_head_code_2'];
        $me_reporting_head_2_name = $content_emp['data']['reporting_head_name_2'];
        $me_reporting_head_2_email = strtolower($content_emp['data']['reporting_head_mail_2']);
        
        
        $me_jda_flg = '';
		$content_emp['data']['section'] = strtolower($content_emp['data']['section']);
		//~ if($content_emp['data']['section'] == "bde operations" || $content_emp['data']['section'] == "bde support" || $content_emp['data']['section'] == "bde client facing" || $content_emp['data']['section'] == "bde managers"){
			//~ $me_jda_flg = 'ME';
		//~ }elseif($content_emp['data']['section'] == "jda operations" || $content_emp['data']['section'] == "jda support" || $content_emp['data']['section'] == "jda sales managers" || $content_emp['data']['section'] == "jda customer facing"){
			//~ $me_jda_flg = 'JDA';			
		//~ }
		if(strpos($content_emp['data']['section'],'jda') !== false){
			$me_jda_flg = 'JDA';
		}else{
			$me_jda_flg = 'ME';
		}
		$tme_manager		=	'';
		$tme_manager_mob	=	'';
		$tme_manager_str	=	'';
		$tme_manager_email	=	'';
		$tme_manager_empcode =	'';
		$tme_manager_name =	'';
		$params_new = array();
		$params_new_tme['url'] = HRMODULE . '/employee/fetch_employee_info/' . $this->empCode;
		$params_new_tme['formate'] = 'basic';
		$tme_manager_data = json_decode(Utility::curlCall($params_new_tme),true);
		$tme_manager_code	=	$tme_manager_data['data']['reporting_head_code'];
		$this->tme_emp_parent = $tme_manager_code;
		//~ $tme_manager_data	=	json_decode($this->get_TME_ME_data($this->tme_emp_parent),true);
		
		$tme_manager = $tme_manager_data['data']['reporting_head'];
		$tme_manager_mob = $tme_manager_data['data']['mobile'];
		$tme_manager_email = $tme_manager_data['data']['reporting_head_mail'];
		$tme_manager_empcode = $tme_manager_data['data']['reporting_head_code'];
		$tme_manager_name = $tme_manager_data['data']['reporting_head'];
		$tme_manager_allocId = '';
		if($tme_manager_data['data']['mobile'] != ''){
			$tme_manager_str	=	$tme_manager."-".$tme_manager_data['data']['mobile'];
		}else{
			$tme_manager_str	=	$tme_manager;
		}
		
		// Making a Curl Call Here;
		$paras_curlCall	=	array();
		$paras_curlCall['action']	=	'apptMsg';
		$paras_curlCall['Company']		=	addslashes(stripslashes($this->genshadowArr['companyname']));
		$paras_curlCall['parentid']		=	$this->parentid;
		if($this->altAddres_flg==1){
			$paras_curlCall['CityName']	=	addslashes(stripslashes($this->altaddResp['city']));
		}else{
			$paras_curlCall['CityName']	=	addslashes(stripslashes($this->genshadowArr['city']));
		}
		$paras_curlCall['meCode'] = addslashes(stripslashes($this->MECode));
		$paras_curlCall['meName'] = addslashes(stripslashes($this->MEName));
		$paras_curlCall['EmpEmail'] = addslashes(stripslashes($this->ME_email));
		$paras_curlCall['EmpMobile'] = $this->ME_mobile;
		$paras_curlCall['tmecode'] = $this->empCode;
		$paras_curlCall['TMEName'] = $this->empName;
		$paras_curlCall['tme_mobile'] = $this->tme_mobile;
		$paras_curlCall['tme_email'] = $this->tme_email;
		
		
		$paras_curlCall['tme_manager'] = addslashes(stripslashes($tme_manager_str));
		$paras_curlCall['tme_manager_email'] = addslashes(stripslashes($tme_manager_email));
		$paras_curlCall['tme_manager_empcode'] = addslashes(stripslashes($tme_manager_empcode));
		$paras_curlCall['tme_manager_mob']	= addslashes(stripslashes($tme_manager_mob));
		$paras_curlCall['tme_manager_name']	= addslashes(stripslashes($tme_manager_name));
		$paras_curlCall['tme_manager_allocId'] = addslashes(stripslashes($tme_manager_allocId));
		$paras_curlCall['me_reporting_head'] = addslashes(stripslashes($me_reporting_head));
		$paras_curlCall['me_reporting_head_mail'] =	addslashes(stripslashes($me_reporting_head_mail));
		$paras_curlCall['me_reporting_head_code'] =	addslashes(stripslashes($me_reporting_head_code));
		$paras_curlCall['me_jda_flg'] =	$me_jda_flg;
		$paras_curlCall['me_reporting_head_2_empcode'] = $me_reporting_head_2_empcode;
		$paras_curlCall['me_reporting_head_2_name'] = $me_reporting_head_2_name;
		$paras_curlCall['me_reporting_head_2_email'] = $me_reporting_head_2_email;
		
		$paras_curlCall['ApptDate']	=	addslashes($date_t[0]);
		$paras_curlCall['ApptTime']	=	addslashes($date_t[1]);
		$paras_curlCall['Contact']	=	addslashes(stripslashes($this->genshadowArr['contact_person']));
		if($this->altAddres_flg==1){
			$paras_curlCall['area']		=	addslashes(stripslashes($this->altaddResp['area']));
			$paras_curlCall['Building']	=	addslashes(stripslashes($this->altaddResp['building_name']));
			$paras_curlCall['Street']	=	addslashes(stripslashes($this->altaddResp['street']));
			$paras_curlCall['Location']	=	addslashes(stripslashes($this->altaddResp['landmark']));
			$paras_curlCall['pincode']	=	$this->altaddResp['pincode'];
		}else{
			$paras_curlCall['area']		=	addslashes(stripslashes($this->genshadowArr['area']));
			$paras_curlCall['Building']	=	addslashes(stripslashes($this->genshadowArr['building_name']));
			$paras_curlCall['Street']	=	addslashes(stripslashes($this->genshadowArr['street']));
			$paras_curlCall['Location']	=	addslashes(stripslashes($this->genshadowArr['landmark']));
			$paras_curlCall['pincode']	=	$this->genshadowArr['pincode'];
		}
		$short_url	=	'';
		if($this->genshadowArr['building_name'] != '' || $this->genshadowArr['area'] != ''){
			/*Writing A Query to get the AllocationId of the contract*/
			$con_local 	=	new DB($this->db['db_local']);
			$get_alloc_id	=	"SELECT allocationId FROM tblContractAllocation WHERE allocationType IN (25,99) AND contractCode = '".$this->parentid."' AND empcode ='".$this->MECode."' AND parentcode = '".$this->empCode."' AND cancel_flag=0 ORDER BY actionTime DESC";
			$con_get_alloc_id 	= 	$con_local->query($get_alloc_id);
			$data_get_alloc_id 	=	$con_local->fetchData($con_get_alloc_id);
			// Call Ronak Joshi API for getting the shortUrl
			$lisofParams['type']	=	1;
			$lisofParams['appointment_id']	=	$data_get_alloc_id['allocationId'];
			$lisofParams['data_city']	=	$this->dat_city;
			$lisofParams['parent_id']	=	$this->parentid;
			$lisofParams['contract_id']	=	$this->genshadowArr['docid'];
			$lisofParams['city']		=	$this->genshadowArr['city'];
			$lisofParams['employee_id']		=	$this->MECode;	
			$lisofParams['employee_name']	=	addslashes(stripslashes($this->MEName));
			$lisofParams['vlat']	=	$this->genshadowArr['latitude'];
			$lisofParams['vlng']	=	$this->genshadowArr['longitude'];
			$lisofParams['vendor_name']	=	addslashes(stripslashes($this->genshadowArr['companyname']));
			$lisofParams['vendor_address']	=	addslashes(stripslashes($this->genshadowArr['full_address']));
			$curlParams['url']	=	"http://192.168.22.103/dialerDashboard/dialerDashboardServices/trackURL?".http_build_query($lisofParams)."";			
			$curlParams['formate'] 	= 	'basic';
			$curlParams['method'] 	=	 'get';
			$get_shortUrl_geoCode	=	array();
			$get_shortUrl_geoCode	=	Utility::curlCall($curlParams);
			$get_shortUrl_geoCode	=	json_decode($get_shortUrl_geoCode,true);
			$short_url	=	$get_shortUrl_geoCode['result']['short_url'];
		}
		if($short_url != ''){
			$paras_curlCall['short_url']	=	$short_url;
		}else{
			$paras_curlCall['short_url']	=	'';
		}
		$con_local 	=	new DB($this->db['db_local']);
		$paras_curlCall['reject_appt_url']	=	'';
		$exp_server_add	=	array();
		$exp_server_add = 	explode(".", $_SERVER['SERVER_ADDR']);
		if(($exp_server_add[2] == 64 || $exp_server_add[2] == 0)) {
			$get_auto_id	=	"SELECT allocationId,contractCode,parentcode,empcode,mename,tmename,compname,actionTime FROM tblContractAllocation WHERE allocationType IN (25,99) AND contractCode = '".$this->parentid."' AND actionTime = '".$actionTime."' AND parentcode = '".$this->empCode."' AND empcode = '".$this->MECode."' AND cancel_flag=0";
			$con_get_auto_id	=	$con_local->query($get_auto_id);
			$num_con_get_auto_id 	=	$con_local->numRows($con_get_auto_id);
			if($num_con_get_auto_id > 0){
				$resp_arr['data'] 	=	$con_local->fetchData($con_get_auto_id);
				$paras_curlCall['reject_appt_url']	=	"http://genio.in/track/manageAppt/".$resp_arr['data']['allocationId']."";
			}
		}		
		$paras_curlCall['Telephone1']	=	$this->phone;
		$paras_curlCall['Telephone2']	=	$this->phone2;
		$paras_curlCall['Mobile']		=	$this->genshadowArr['mobile'];
		$paras_curlCall['instrct']		=	addslashes(stripslashes($this->instrct));
		$paras_curlCall['empclass']		=	$this->tme_allocid;
		$paras_curlCall['TMEMobile']	=	$this->tme_mobile;
		$paras_curlCall['bus_temp_data_cat_withoutP']	=	addslashes(stripslashes($bus_temp_data_cat_withoutP));
		
		/*New Code Added for Ad Link*/
		$ad_link	=	array();
		$ad_link 	=	json_decode($this->returnTvAdLink(),true);
		$paras_curlCall['ad_link']			=	$ad_link['link'];
		/*New Code Added for Ad Link*/
		$curlParams_temp['url']				=	"http://" . $_SERVER['HTTP_HOST'] . "/library/RatingEmailSms.php";
		$curlParams_temp['formate'] 		= 	'basic';
		$curlParams_temp['method'] 			=	 'post';
		//$curlParams_temp['headerJson'] 	=	 'json';
		$curlParams_temp['postData'] 		= 	$paras_curlCall; 
		$sms_sent_appt						=	Utility::curlCall($curlParams_temp);
		$retArr['sms_sent_appt']	=	$sms_sent_appt;
		if($sms_sent_appt){
			$retArr['errorCode_instant']	=	0;
			$retArr['errorStatus_instant']	=	'SMS SENT';
		}else{
			$retArr['errorCode_instant']	=	1;
			$retArr['errorStatus_instant']	=	'SMS NOT SENT';
		}
		$appt_date_time	=	$actionTime;
		$con_local 	=	new DB($this->db['db_local']);
		if($this->altAddres_flg==1){
			$insert_sms_email_table		=	"INSERT INTO d_jds.tbl_appt_sms_email SET
												parentid = '".$this->parentid."',
												compname = '".addslashes(stripslashes($this->genshadowArr['companyname']))."',
												contact_person = '".addslashes(stripslashes($this->genshadowArr['contact_person']))."',
												tme_name = '".addslashes(stripslashes($this->empName))."',
												tme_mobile = '".$this->tme_mobile."',
												tme_team = '".$this->tme_allocid."',
												tme_code = '".$this->empCode."',
												me_name  = '".addslashes(stripslashes($this->MEName))."',
												me_mobile = '".$this->ME_mobile."',
												me_code	  = '".$this->MECode."',
												tme_manager = '".addslashes(stripslashes($tme_manager_str))."',
												tme_manager_code = '".$tme_manager_code."',
												appt_time = '".addslashes(stripslashes($date_t[0]))."',
												insertedOn = '".$currentTime."',
												pincode = '".$this->altaddResp['pincode']."',
												appt_slot = '".addslashes(stripslashes($date_t[1]))."',
												me_email = '".addslashes(stripslashes($this->ME_email))."',
												city = '".addslashes(stripslashes($this->altaddResp['city']))."',
												category = '".addslashes(stripslashes($bus_temp_data_cat_withoutP))."',
												landline_one = '".$this->phone."',
												landline_two = '".$this->phone2."',
												contract_mobile = '".addslashes(stripslashes($this->genshadowArr['mobile']))."',
												building = '".addslashes(stripslashes($this->altaddResp['building_name']))."',
												street = '".addslashes(stripslashes($this->altaddResp['street']))."',
												landmark = '".addslashes(stripslashes($this->altaddResp['landmark']))."',
												area_name = '".addslashes(stripslashes($this->altaddResp['area']))."',
												appt_date_time = '".$appt_date_time."',
												ad_link = '".$ad_link['link']."',
												src='TME',
												updatedOn='".$currentTime."',
												vendor_location	=	'".addslashes(stripslashes($short_url))."',
												sms_sent_flag = 0
											ON DUPLICATE KEY UPDATE
												compname = '".addslashes(stripslashes($this->genshadowArr['companyname']))."',
												contact_person = '".addslashes(stripslashes($this->genshadowArr['contact_person']))."',
												tme_name = '".addslashes(stripslashes($this->empName))."',
												tme_mobile = '".$this->tme_mobile."',
												tme_team = '".$this->tme_allocid."',
												tme_code = '".$this->empCode."',
												me_name  = '".addslashes(stripslashes($this->MEName))."',
												me_mobile = '".$this->ME_mobile."',
												me_code	  = '".$this->MECode."',
												tme_manager = '".addslashes(stripslashes($tme_manager_str))."',
												tme_manager_code = '".$tme_manager_code."',
												appt_time = '".addslashes(stripslashes($date_t[0]))."',
												insertedOn = '".$currentTime."',
												pincode = '".$this->altaddResp['pincode']."',
												appt_slot = '".addslashes(stripslashes($date_t[1]))."',
												me_email = '".addslashes(stripslashes($this->ME_email))."',
												city = '".addslashes(stripslashes($this->altaddResp['city']))."',
												category = '".addslashes(stripslashes($bus_temp_data_cat_withoutP))."',
												landline_one = '".$this->phone."',
												landline_two = '".$this->phone2."',
												contract_mobile = '".addslashes(stripslashes($this->genshadowArr['mobile']))."',
												building = '".addslashes(stripslashes($this->altaddResp['building_name']))."',
												street = '".addslashes(stripslashes($this->altaddResp['street']))."',
												landmark = '".addslashes(stripslashes($this->altaddResp['landmark']))."',
												area_name = '".addslashes(stripslashes($this->altaddResp['area']))."',
												appt_date_time = '".$appt_date_time."',
												ad_link = '".$ad_link['link']."',
												src='TME',
												updatedOn='".$currentTime."',
												vendor_location	=	'".addslashes(stripslashes($short_url))."',
												sms_sent_flag = 0";
		}else{
			$insert_sms_email_table		=	"INSERT INTO d_jds.tbl_appt_sms_email SET
												parentid = '".$this->parentid."',
												compname = '".addslashes(stripslashes($this->genshadowArr['companyname']))."',
												contact_person = '".addslashes(stripslashes($this->genshadowArr['contact_person']))."',
												tme_name = '".addslashes(stripslashes($this->empName))."',
												tme_mobile = '".$this->tme_mobile."',
												tme_team = '".$this->tme_allocid."',
												tme_code = '".$this->empCode."',
												me_name  = '".addslashes(stripslashes($this->MEName))."',
												me_mobile = '".$this->ME_mobile."',
												me_code	  = '".$this->MECode."',
												tme_manager = '".addslashes(stripslashes($tme_manager_str))."',
												tme_manager_code = '".$tme_manager_code."',
												appt_time = '".addslashes(stripslashes($date_t[0]))."',
												insertedOn = '".$currentTime."',
												pincode = '".$this->genshadowArr['pincode']."',
												appt_slot = '".addslashes(stripslashes($date_t[1]))."',
												me_email = '".addslashes(stripslashes($this->ME_email))."',
												city = '".addslashes(stripslashes($this->genshadowArr['city']))."',
												category = '".addslashes(stripslashes($bus_temp_data_cat_withoutP))."',
												landline_one = '".$this->phone."',
												landline_two = '".$this->phone2."',
												contract_mobile = '".addslashes(stripslashes($this->genshadowArr['mobile']))."',
												building = '".addslashes(stripslashes($this->genshadowArr['building_name']))."',
												street = '".addslashes(stripslashes($this->genshadowArr['street']))."',
												landmark = '".addslashes(stripslashes($this->genshadowArr['landmark']))."',
												area_name = '".addslashes(stripslashes($this->genshadowArr['area']))."',
												appt_date_time = '".$appt_date_time."',
												ad_link = '".$ad_link['link']."',
												src='TME',
												updatedOn='".$currentTime."',
												vendor_location	=	'".addslashes(stripslashes($short_url))."',
												sms_sent_flag = 0
											ON DUPLICATE KEY UPDATE
												compname = '".addslashes(stripslashes($this->genshadowArr['companyname']))."',
												contact_person = '".addslashes(stripslashes($this->genshadowArr['contact_person']))."',
												tme_name = '".addslashes(stripslashes($this->empName))."',
												tme_mobile = '".$this->tme_mobile."',
												tme_team = '".$this->tme_allocid."',
												tme_code = '".$this->empCode."',
												me_name  = '".addslashes(stripslashes($this->MEName))."',
												me_mobile = '".$this->ME_mobile."',
												me_code	  = '".$this->MECode."',
												tme_manager = '".addslashes(stripslashes($tme_manager_str))."',
												tme_manager_code = '".$tme_manager_code."',
												appt_time = '".addslashes(stripslashes($date_t[0]))."',
												insertedOn = '".$currentTime."',
												pincode = '".$this->genshadowArr['pincode']."',
												appt_slot = '".addslashes(stripslashes($date_t[1]))."',
												me_email = '".addslashes(stripslashes($this->ME_email))."',
												city = '".addslashes(stripslashes($this->genshadowArr['city']))."',
												category = '".addslashes(stripslashes($bus_temp_data_cat_withoutP))."',
												landline_one = '".$this->phone."',
												landline_two = '".$this->phone2."',
												contract_mobile = '".addslashes(stripslashes($this->genshadowArr['mobile']))."',
												building = '".addslashes(stripslashes($this->genshadowArr['building_name']))."',
												street = '".addslashes(stripslashes($this->genshadowArr['street']))."',
												landmark = '".addslashes(stripslashes($this->genshadowArr['landmark']))."',
												area_name = '".addslashes(stripslashes($this->genshadowArr['area']))."',
												appt_date_time = '".$appt_date_time."',
												ad_link = '".$ad_link['link']."',
												src='TME',
												updatedOn='".$currentTime."',
												vendor_location	=	'".addslashes(stripslashes($short_url))."',
												sms_sent_flag = 0";
		}
		$con_sms_email_table 	= 	$con_local->query($insert_sms_email_table); 
		if($con_sms_email_table){
			$retArr['errorCode_sms_email_table']	=	0;
			$retArr['errorStatus_sms_email_table']	=	'Table sms_email_table updated';
		}else{
			$retArr['errorCode_sms_email_table']	=	1;
			$retArr['errorStatus_sms_email_table']	=	'Table sms_email_table NOT updated';
		}
		return json_encode($retArr);
	}
	
	public function insertCallRecording(){
		$retArr 		=	array();
		$con_local 		=	new DB($this->db['db_local']);
		$landline 		=	explode($this->genshadowArr['landline'],',');
		$mobile 		=	explode($this->genshadowArr['mobile'],',');
		if(count($landline)>0){
			$landLineFlag	=	1;
			for($i=0;$i<count($landline);$i++){
				if(trim($landline[$i])!=''){
					$insert_rec1="INSERT IGNORE INTO tbl_CallRecording_Company set parentid='".$this->parentid."' ,tele_1='".$landline[$i]."'";
					$dummy1     = $con_local->query($insert_rec1);
					if($dummy1){
						$landLineFlag	=	0;
					}
				}
			}
			if($landLineFlag==0){
				$retArr['errorCode']	=	0;
				$retArr['errorSatus_landline_CallRecording_Company'] 	=	'Insertion of landline DONE in CallRecording_Company';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorSatus_landline_CallRecording_Company'] 	=	'Insertion of landline FAIL in CallRecording_Company';
			}
		}else{$retArr['errorCode']	=	1;}
		if(count($mobile)>0){
			$mobFlag	=	1;
			for($i=0;$i<count($mobile);$i++){
				if(trim($mobile[$i])!=''){
					$insert_rec1="INSERT IGNORE INTO tbl_CallRecording_Company set parentid='".$this->parentid."' ,tele_1='".$mobile[$i]."'";
					$dummy1     = $con_local->query($insert_rec1);
					if($dummy1){
						$mobFlag	=	0;
					}
				}
			}
			if($mobFlag==0){
				$retArr['errorCode']	=	0;
				$retArr['errorStatus_mobile_CallRecording_Company'] 	=	'Insertion of mobile DONE in CallRecording_Company';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus_mobile_CallRecording_Company'] 	=	'Insertion of mobile in CallRecording_Company';
			}

		}else{$retArr['errorCode']	=	1;}
		return json_encode($retArr);
	}
	/*
	 * This insertion will Happen only when extensionId is entered at start 
	*/
	public function insert_AppointmentCall_Rec(){
		$retArr			=	array();
		$con_local 		=	new DB($this->db['db_local']);
		$con_idc_ar		=	new DB($this->db['db_idc']);
		$currentTime	=	date("Y-m-d H:i:s");
		$seltaudio 		= 	"SELECT filepath as RecFilePath,date(called_date) as date, called_date as starttime from tbl_CallRecording_Company 
							where parentid='".$this->parentid."' AND extn='".$this->Ext_Id."' and filepath is not NULL order by called_date desc limit 1";
		$restaudio 		= 	$con_local->query($seltaudio);
		$row 			= 	$con_local->fetchData($restaudio);
		if($restaudio && !empty($row)){
			$retArr['errorCode']	=	0;
			$retArr['error_status']	=	'Query Run Data Present in tbl_CallRecording_Company';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['error_status']	=	'Query Run but Data not Present in tbl_CallRecording_Company';
		}
			if($row[date]){
				$date=explode('-',$row['date']);
				$year=$date[0];
				$mnth=$date[1];
				$dated=$date[2];

				$hour=explode(' ',$row['starttime']);
				$hr=explode(':',$hour[1]);
				$yearhr=$hr[0];
				
				$file = $year.'/'.$mnth.'/'.$dated.'/'.$yearhr.'/'.$row['RecFilePath'];
				
				$insert_call 	=	"INSERT INTO  tbl_AppointmentCall_Rec 
									SET
									filepath='".$file."' ,
									updated_date='".addslashes($currentTime)."',
									tmecode='".$this->empCode."' ,
									parentid='".$this->parentId."'
									ON DUPLICATE KEY UPDATE 
									filepath='".$file."' ,
									updated_date='".addslashes($currentTime)."',
									tmecode='".$this->empCode."'";
				$fetch_call 	= 	$con_idc_ar->query($insert_call);
				if($fetch_call){
					$retArr['errorCode']	=	0;
					$retArr['errorStatus_AppointmentCall_Rec'] 	=	'Insertion DONE in AppointmentCall_Rec';
				}else{
					$retArr['errorCode']	=	1;
					$retArr['errorStatus_AppointmentCall_Rec'] 	=	'Insertion DONE in AppointmentCall_Rec';
				}
			}
			return json_encode($retArr);
	}
		
	/*
	 * function to get Data from tbl_business_temp_data
	*/
	public function get_business_temp_data(){
		$db_tme			=	new DB($this->db['db_tme']);
		$retArr			=	array();
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "contractid,categories,catIds,htmldump,slogan,categories_list,pages,bid_day_sel,bid_day_sel,bid_timing,threshold,autobid,catSelected,uId,mainattr,facility,companyName,avgAmt,percentage,comp_deduction_amt,thresholdform,authorised_categories,thresholdType,original_catids,bid_lead_num,bid_type,nationalcatIds,parentname,bid_led_num_year,thresholdPercnt,TotThresh,thresWeekSup,thresDailySup,thresMonthSup,bid_lead_num_sys,significance,htmldump_np,slogan_np";
			$temp_data = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{		
			$qr_bus_temp_data 	=	"SELECT contractid,categories,catIds,htmldump,slogan,categories_list,pages,bid_day_sel,bid_day_sel,bid_timing,threshold,autobid,catSelected,uId,mainattr,facility,companyName,avgAmt,percentage,comp_deduction_amt,thresholdform,authorised_categories,thresholdType,original_catids,bid_lead_num,bid_type,nationalcatIds,parentname,bid_led_num_year,thresholdPercnt,TotThresh,thresWeekSup,thresDailySup,thresMonthSup,bid_lead_num_sys,significance,htmldump_np,slogan_np FROM tbl_business_temp_data WHERE contractid='".$this->parentid."'";
			$con_bus_temp_data 	=	$db_tme->query($qr_bus_temp_data);
			if($db_tme->numRows($con_bus_temp_data)){
				$temp_data 		=	$db_tme->fetchData($con_bus_temp_data);
			}
		}
		if(count($temp_data)>0){
			$retArr['data'] 		=	$temp_data;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus_business_temp_data']	=	'Data Found in tbl_business_temp_data';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus_business_temp_data']	=	'Data Not Found in tbl_business_temp_data';
		}
		return json_encode($retArr);
	}
	
	/*
	 * function to delete from Table tbl_cs_tme_edit_log
	*/
	public function delete_edit_log(){
		$db_idc_ar	=	new DB($this->db['db_idc']);
		$retArr 	=	array();
		
		$delete_qry_tme_edit_log 		=	"DELETE FROM tbl_cs_tme_edit_log WHERE parentid='".$this->parentid."'";
        $con_delete_qry_tme_edit_log 	=	$db_idc_ar->query($delete_qry_tme_edit_log);
        if($con_delete_qry_tme_edit_log){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'DELETE tme_edit_log DONE';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'DELETE tme_edit_log FAIL';
		}
		return json_encode($retArr);
	}
	private function addslashesArray($resultArray){
		foreach($resultArray AS $key=>$value){
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		return $resultArray;
	}
	/*
	 * START of Functions to Move Data from tme_jds server to IDC server 
	*/
	public function MoveToIDC_extradetails_shadow(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$paramsarr_extradetails_shadow 	=	array();
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64'){
			$paramsarr_extradetails_shadow['url']		=	SERVICE_IP."/contractInfo/getShadowTabExtraData/".$this->parentid;
		}else{
			$paramsarr_extradetails_shadow['url']		=	SERVICE_IP."/contractInfo/getShadowTabExtraData/".$this->parentid;
		}
		$paramsarr_extradetails_shadow['formate'] 		=	'basic';
		$paramsarr_extradetails_shadow['method'] 		=	'post';
		$paramsarr_extradetails_shadow['headerJson'] 	=	'json';
		$row_companymaster_extradetails_shadow			=	Utility::curlCall($paramsarr_extradetails_shadow);
		$row_companymaster_extradetails_shadow			=	json_decode($row_companymaster_extradetails_shadow,true);	
		if($row_companymaster_extradetails_shadow['errorCode'] ==0){
			$row_companymaster_extradetails_shadow_arr		= 	$this->addslashesArray($row_companymaster_extradetails_shadow['data']);	
			//~ $tbl_del4 				=	"DELETE FROM tbl_companymaster_extradetails_shadow WHERE parentid  = '".$this->parentid."'";							
			//~ $restbl_temp_company	=	 $dbObjme->query($tbl_del4);
			//~ if($restbl_temp_company){
				//~ $error_arr['errorCode'] 		=	0;
				//~ $error_arr['errorStatus_DLETE_companymaster_extradetails'] 	=	"delete companymaster_extradetails_shadow Done";
			//~ }else{
			 	//~ $error_arr['errorCode'] 		=	1;
				//~ $error_arr['errorStatus_DLETE_companymaster_extradetails'] 	=	"delete companymaster_extradetails_shadow Fail";
			//~ }
			//return json_encode($error_arr);die();
			
			
			$row_ext_details = $row_companymaster_extradetails_shadow_arr;
			
			
			
			$mongo_data = array();
			$extrdet_tbl = "tbl_companymaster_extradetails_shadow";
			$extrdet_upt = array();
			$extrdet_upt['companyname'] 					= $row_ext_details['companyname'];
			$extrdet_upt['landline_addinfo'] 				= $row_ext_details['landline_addinfo'];
			$extrdet_upt['mobile_addinfo'] 					= $row_ext_details['mobile_addinfo'];
			$extrdet_upt['tollfree_addinfo'] 				= $row_ext_details['tollfree_addinfo'];					
			$extrdet_upt['contact_person_addinfo'] 			= $row_ext_details['contact_person_addinfo'];
			$extrdet_upt['attributes'] 						= $row_ext_details['attributes'];
			$extrdet_upt['attributes_edit'] 				= $row_ext_details['attributes_edit'];
			$extrdet_upt['turnover'] 						= $row_ext_details['turnover'];
			$extrdet_upt['working_time_start'] 				= $row_ext_details['working_time_start'];
			$extrdet_upt['working_time_end'] 				= $row_ext_details['working_time_end'];
			$extrdet_upt['payment_type'] 					= $row_ext_details['payment_type'];
			$extrdet_upt['year_establishment'] 				= $row_ext_details['year_establishment'];
			$extrdet_upt['certificates'] 					= $row_ext_details['certificates'];
			$extrdet_upt['no_employee'] 					= $row_ext_details['no_employee'];
			$extrdet_upt['business_group'] 					= $row_ext_details['business_group'];
			$extrdet_upt['email_feedback_freq'] 			= $row_ext_details['email_feedback_freq'];
			$extrdet_upt['statement_flag'] 					= $row_ext_details['statement_flag'];
			$extrdet_upt['alsoServeFlag'] 					= $row_ext_details['alsoServeFlag'];
			$extrdet_upt['averageRating'] 					= $row_ext_details['averageRating'];
			$extrdet_upt['ratings'] 						= $row_ext_details['ratings'];
			$extrdet_upt['web_ratings'] 					= $row_ext_details['web_ratings'];
			$extrdet_upt['number_of_reviews'] 				= $row_ext_details['number_of_reviews'];
			$extrdet_upt['group_id'] 						= $row_ext_details['group_id'];
			$extrdet_upt['guarantee'] 						= $row_ext_details['guarantee'];
			$extrdet_upt['Jdright'] 						= $row_ext_details['Jdright'];
			$extrdet_upt['LifestyleTag'] 					= $row_ext_details['LifestyleTag'];
			$extrdet_upt['contract_calltype'] 				= $row_ext_details['contract_calltype'];
			$extrdet_upt['batch_group'] 					= $row_ext_details['batch_group'];
			$extrdet_upt['audit_status'] 					= $row_ext_details['audit_status'];
			$extrdet_upt['customerID'] 						= $row_ext_details['customerID'];
			$extrdet_upt['datavalidity_flag'] 				= $row_ext_details['datavalidity_flag'];
			$extrdet_upt['deactflg'] 						= $row_ext_details['deactflg'];
			$extrdet_upt['display_flag'] 					= $row_ext_details['display_flag'];
			$extrdet_upt['fmobile'] 						= $row_ext_details['fmobile'];
			$extrdet_upt['femail'] 							= $row_ext_details['femail'];
			$extrdet_upt['flgActive'] 						= $row_ext_details['flgActive'];
			$extrdet_upt['freeze'] 							= $row_ext_details['freeze'];
			$extrdet_upt['mask'] 							= $row_ext_details['mask'];
			$extrdet_upt['future_contract_flag'] 			= $row_ext_details['future_contract_flag'];
			$extrdet_upt['hidden_flag'] 					= $row_ext_details['hidden_flag'];
			$extrdet_upt['lockDateTime'] 					= $row_ext_details['lockDateTime'];
			$extrdet_upt['lockedBy'] 						= $row_ext_details['lockedBy'];
			$extrdet_upt['temp_deactive_start'] 			= $row_ext_details['temp_deactive_start'];
			$extrdet_upt['temp_deactive_end'] 				= $row_ext_details['temp_deactive_end'];
			$extrdet_upt['micrcode'] 						= $row_ext_details['micrcode'];
			$extrdet_upt['prompt_cat_temp'] 				= $row_ext_details['prompt_cat_temp'];
			$extrdet_upt['promptype'] 						= $row_ext_details['promptype'];
			$extrdet_upt['referto']	 						= $row_ext_details['referto'];
			$extrdet_upt['serviceName'] 					= $row_ext_details['serviceName'];
			$extrdet_upt['srcEmp'] 							= $row_ext_details['srcEmp'];
			$extrdet_upt['telComm'] 						= $row_ext_details['telComm'];			
			$extrdet_upt['updatedBy'] 						= $row_ext_details['updatedBy'];
			$extrdet_upt['updatedOn'] 						= $row_ext_details['updatedOn'];
			$extrdet_upt['map_pointer_flags'] 				= $row_ext_details['map_pointer_flags'];
			$extrdet_upt['flags'] 							= $row_ext_details['flags'];
			$extrdet_upt['catidlineage_nonpaid'] 			= $row_ext_details['catidlineage_nonpaid'];
			$extrdet_upt['national_catidlineage_nonpaid'] 	= $row_ext_details['national_catidlineage_nonpaid'];
			$extrdet_upt['award'] 							= $row_ext_details['award'];
			$extrdet_upt['testimonial'] 					= $row_ext_details['testimonial'];
			$extrdet_upt['proof_establishment'] 			= $row_ext_details['proof_establishment'];
			$mongo_data[$extrdet_tbl]['updatedata'] 		= $extrdet_upt;
			
			$extrdet_ins = array();
			$extrdet_ins['nationalid'] 			= $row_ext_details['nationalid'];
			$extrdet_ins['sphinx_id'] 			= $row_ext_details['sphinx_id'];
			$extrdet_ins['regionid'] 			= $row_ext_details['regionid'];
			$extrdet_ins['createdby'] 			= $row_ext_details['createdby'];
			$extrdet_ins['createdtime'] 		= $row_ext_details['createdtime'];
			$extrdet_ins['original_creator'] 	= $row_ext_details['original_creator'];
			$extrdet_ins['original_date'] 		= $row_ext_details['original_date'];
			$mongo_data[$extrdet_tbl]['insertdata'] = $extrdet_ins;
			
			$extra_params = array(
				"action"			=>	"updatedata",
				"post_data"			=>	"1",
				"parentid"			=>	$this->parentid,
				"data_city"			=>	$this->dat_city,
				"module"			=>	"ME",
				"table_data" 		=>	http_build_query($mongo_data)
			);
			$url =	JDBOX_API.'/services/mongoWrapper.php';
			$curlParams	=	array();
			$curlParams['url']		=	$url;
			$curlParams['method']	=	'post';
			$curlParams['formate'] = 	'basic';
			$curlParams['postData'] = $extra_params;
			$extra_resp	=	json_decode(Utility::curlCall($curlParams),true);
			
			//~ $tbl_insert4 			=	"INSERT INTO tbl_companymaster_extradetails_shadow SET 
										 //~ nationalid      				= '".$row_companymaster_extradetails_shadow_arr[nationalid]."',
										 //~ sphinx_id      				= '".$row_companymaster_extradetails_shadow_arr[sphinx_id]."',
										 //~ regionid      					= '".$row_companymaster_extradetails_shadow_arr[regionid]."',
										 //~ companyname					= '".addslashes(stripslashes($row_companymaster_extradetails_shadow_arr[companyname]))."',
										 //~ parentid        				= '".$this->parentid."',
										 //~ landline_addinfo		 		= '".$row_companymaster_extradetails_shadow_arr[landline_addinfo]."',
										 //~ mobile_addinfo        			= '".$row_companymaster_extradetails_shadow_arr[mobile_addinfo]."',
										 //~ tollfree_addinfo		 		= '".$row_companymaster_extradetails_shadow_arr[tollfree_addinfo]."',
										 //~ contact_person_addinfo 		= '".$row_companymaster_extradetails_shadow_arr[contact_person_addinfo]."',
										 //~ attributes           			= '".$row_companymaster_extradetails_shadow_arr[attributes]."',
										 //~ attributes_edit     			= '".$row_companymaster_extradetails_shadow_arr[attributes_edit]."',
										 //~ turnover      					= '".$row_companymaster_extradetails_shadow_arr[turnover]."',
										 //~ working_time_start     		= '".$row_companymaster_extradetails_shadow_arr[working_time_start]."',
										 //~ working_time_end     			= '".$row_companymaster_extradetails_shadow_arr[working_time_end]."',
										 //~ payment_type           		= '".$row_companymaster_extradetails_shadow_arr[payment_type]."',
										 //~ year_establishment     		= '".$row_companymaster_extradetails_shadow_arr[year_establishment]."',
										 //~ accreditations        			= '".$row_companymaster_extradetails_shadow_arr[accreditations]."',
										 //~ certificates     				= '".$row_companymaster_extradetails_shadow_arr[certificates]."',
										 //~ no_employee          			= '".$row_companymaster_extradetails_shadow_arr[no_employee]."',
										 //~ business_group     			= '".$row_companymaster_extradetails_shadow_arr[business_group]."',
										 //~ email_feedback_freq 			= '".$row_companymaster_extradetails_shadow_arr[email_feedback_freq]."',
										 //~ statement_flag   				= '".$row_companymaster_extradetails_shadow_arr[statement_flag]."',
										 //~ alsoServeFlag			   		= '".$row_companymaster_extradetails_shadow_arr[alsoServeFlag]."',
										 //~ averageRating 					= '".$row_companymaster_extradetails_shadow_arr[averageRating]."',
										 //~ ratings 						= '".$row_companymaster_extradetails_shadow_arr[ratings]."',
										 //~ web_ratings		    		= '".$row_companymaster_extradetails_shadow_arr[web_ratings]."',
										 //~ number_of_reviews  			=  '".$row_companymaster_extradetails_shadow_arr[number_of_reviews]."',
										 //~ group_id      					= '".$row_companymaster_extradetails_shadow_arr[group_id]."',
										 //~ catidlineage					= '".$row_companymaster_extradetails_shadow_arr[catidlineage]."',
										 //~ catidlineage_search		 	= '".$row_companymaster_extradetails_shadow_arr[catidlineage_search]."',
										 //~ national_catidlineage  		= '".$row_companymaster_extradetails_shadow_arr[national_catidlineage]."',
										 //~ national_catidlineage_search	= '".$row_companymaster_extradetails_shadow_arr[national_catidlineage_search]."',
										 //~ category_count   				= '".$row_companymaster_extradetails_shadow_arr[category_count]."',
										 //~ hotcategory   					= '".$row_companymaster_extradetails_shadow_arr[hotcategory]."',
										 //~ flags							= '".$row_companymaster_extradetails_shadow_arr[flags]."',
										 //~ vertical_flags    				= '".$row_companymaster_extradetails_shadow_arr[vertical_flags]."',
										 //~ business_assoc_flags 			= '".$row_companymaster_extradetails_shadow_arr[business_assoc_flags]."',
										 //~ map_pointer_flags   			= '".$row_companymaster_extradetails_shadow_arr[map_pointer_flags]."',
										 //~ guarantee  					= '".$row_companymaster_extradetails_shadow_arr[guarantee]."',
										 //~ Jdright  						=  '".$row_companymaster_extradetails_shadow_arr[Jdright]."',
										 //~ LifestyleTag  					= '".$row_companymaster_extradetails_shadow_arr[LifestyleTag]."',
										 //~ contract_calltype 				= '".$row_companymaster_extradetails_shadow_arr[contract_calltype]."',
										 //~ batch_group 					=  '".$row_companymaster_extradetails_shadow_arr[batch_group]."',
										 //~ audit_status 					= '".$row_companymaster_extradetails_shadow_arr[audit_status]."',
										 //~ createdby 						= '".$row_companymaster_extradetails_shadow_arr[createdby]."',
										 //~ createdtime 					= '".$row_companymaster_extradetails_shadow_arr[createdtime]."',
										 //~ customerID 					= '".$row_companymaster_extradetails_shadow_arr[customerID]."',
										 //~ datavalidity_flag 				= '".$row_companymaster_extradetails_shadow_arr[datavalidity_flag]."',
										 //~ deactflg 						= '".$row_companymaster_extradetails_shadow_arr[deactflg]."',
										 //~ display_flag 					= '".$row_companymaster_extradetails_shadow_arr[display_flag]."',
										 //~ fmobile 						= '".$row_companymaster_extradetails_shadow_arr[fmobile]."',
										 //~ femail 						= '".$row_companymaster_extradetails_shadow_arr[femail]."',
										 //~ flgActive 						= '".$row_companymaster_extradetails_shadow_arr[flgActive]."',
										 //~ flgApproval 					= '".$row_companymaster_extradetails_shadow_arr[flgApproval]."',
										 //~ freeze 						= '".$row_companymaster_extradetails_shadow_arr[freeze]."',
										 //~ mask 							= '".$row_companymaster_extradetails_shadow_arr[mask]."',
										 //~ future_contract_flag 			= '".$row_companymaster_extradetails_shadow_arr[future_contract_flag]."',
										 //~ hidden_flag 					= '".$row_companymaster_extradetails_shadow_arr[hidden_flag]."',
										 //~ lockDateTime 					= '".$row_companymaster_extradetails_shadow_arr[lockDateTime]."',
										 //~ lockedBy 						= '".$row_companymaster_extradetails_shadow_arr[lockedBy]."',
										 //~ temp_deactive_start 			= '".$row_companymaster_extradetails_shadow_arr[temp_deactive_start]."',
										 //~ temp_deactive_end 				= '".$row_companymaster_extradetails_shadow_arr[temp_deactive_end]."',
										 //~ micrcode 						= '".$row_companymaster_extradetails_shadow_arr[micrcode]."',
										 //~ prompt_cat_temp 				= '".$row_companymaster_extradetails_shadow_arr[prompt_cat_temp]."',
										 //~ promptype 						= '".$row_companymaster_extradetails_shadow_arr[promptype]."',
										 //~ referto 						= '".$row_companymaster_extradetails_shadow_arr[referto]."',
										 //~ serviceName 					= '".$row_companymaster_extradetails_shadow_arr[serviceName]."',
										 //~ srcEmp 						= '".$row_companymaster_extradetails_shadow_arr[srcEmp]."',
										 //~ telComm 						= '".$row_companymaster_extradetails_shadow_arr[telComm]."',
										 //~ newbusinessflag 				= '".$row_companymaster_extradetails_shadow_arr[newbusinessflag]."',
										 //~ tme_code 						= '".$row_companymaster_extradetails_shadow_arr[tme_code]."',
										 //~ original_creator 				= '".$row_companymaster_extradetails_shadow_arr[original_creator]."',
										 //~ original_date 					= '".$row_companymaster_extradetails_shadow_arr[original_date]."',
										 //~ updatedBy 						= '".$row_companymaster_extradetails_shadow_arr[updatedBy]."',
										 //~ updatedOn 						= '".$row_companymaster_extradetails_shadow_arr[updatedOn]."',
										 //~ quick_quote_flag 				= '".$row_companymaster_extradetails_shadow_arr[quick_quote_flag]."'
										  //~ ON DUPLICATE KEY UPDATE
										 //~ nationalid      				= '".$row_companymaster_extradetails_shadow_arr[nationalid]."',
										 //~ sphinx_id      				= '".$row_companymaster_extradetails_shadow_arr[sphinx_id]."',
										 //~ regionid      					= '".$row_companymaster_extradetails_shadow_arr[regionid]."',
										 //~ companyname					= '".addslashes(stripslashes($row_companymaster_extradetails_shadow_arr[companyname]))."',										 
										 //~ landline_addinfo		 		= '".$row_companymaster_extradetails_shadow_arr[landline_addinfo]."',
										 //~ mobile_addinfo        			= '".$row_companymaster_extradetails_shadow_arr[mobile_addinfo]."',
										 //~ tollfree_addinfo		 		= '".$row_companymaster_extradetails_shadow_arr[tollfree_addinfo]."',
										 //~ contact_person_addinfo 		= '".$row_companymaster_extradetails_shadow_arr[contact_person_addinfo]."',
										 //~ attributes           			= '".$row_companymaster_extradetails_shadow_arr[attributes]."',
										 //~ attributes_edit     			= '".$row_companymaster_extradetails_shadow_arr[attributes_edit]."',
										 //~ turnover      					= '".$row_companymaster_extradetails_shadow_arr[turnover]."',
										 //~ working_time_start     		= '".$row_companymaster_extradetails_shadow_arr[working_time_start]."',
										 //~ working_time_end     			= '".$row_companymaster_extradetails_shadow_arr[working_time_end]."',
										 //~ payment_type           		= '".$row_companymaster_extradetails_shadow_arr[payment_type]."',
										 //~ year_establishment     		= '".$row_companymaster_extradetails_shadow_arr[year_establishment]."',
										 //~ accreditations        			= '".$row_companymaster_extradetails_shadow_arr[accreditations]."',
										 //~ certificates     				= '".$row_companymaster_extradetails_shadow_arr[certificates]."',
										 //~ no_employee          			= '".$row_companymaster_extradetails_shadow_arr[no_employee]."',
										 //~ business_group     			= '".$row_companymaster_extradetails_shadow_arr[business_group]."',
										 //~ email_feedback_freq 			= '".$row_companymaster_extradetails_shadow_arr[email_feedback_freq]."',
										 //~ statement_flag   				= '".$row_companymaster_extradetails_shadow_arr[statement_flag]."',
										 //~ alsoServeFlag			   		= '".$row_companymaster_extradetails_shadow_arr[alsoServeFlag]."',
										 //~ averageRating 					= '".$row_companymaster_extradetails_shadow_arr[averageRating]."',
										 //~ ratings 						= '".$row_companymaster_extradetails_shadow_arr[ratings]."',
										 //~ web_ratings		    		= '".$row_companymaster_extradetails_shadow_arr[web_ratings]."',
										 //~ number_of_reviews  			=  '".$row_companymaster_extradetails_shadow_arr[number_of_reviews]."',
										 //~ group_id      					= '".$row_companymaster_extradetails_shadow_arr[group_id]."',
										 //~ catidlineage					= '".$row_companymaster_extradetails_shadow_arr[catidlineage]."',
										 //~ catidlineage_search		 	= '".$row_companymaster_extradetails_shadow_arr[catidlineage_search]."',
										 //~ national_catidlineage  		= '".$row_companymaster_extradetails_shadow_arr[national_catidlineage]."',
										 //~ national_catidlineage_search	= '".$row_companymaster_extradetails_shadow_arr[national_catidlineage_search]."',
										 //~ category_count   				= '".$row_companymaster_extradetails_shadow_arr[category_count]."',
										 //~ hotcategory   					= '".$row_companymaster_extradetails_shadow_arr[hotcategory]."',
										 //~ flags							= '".$row_companymaster_extradetails_shadow_arr[flags]."',
										 //~ vertical_flags    				= '".$row_companymaster_extradetails_shadow_arr[vertical_flags]."',
										 //~ business_assoc_flags 			= '".$row_companymaster_extradetails_shadow_arr[business_assoc_flags]."',
										 //~ map_pointer_flags   			= '".$row_companymaster_extradetails_shadow_arr[map_pointer_flags]."',
										 //~ guarantee  					= '".$row_companymaster_extradetails_shadow_arr[guarantee]."',
										 //~ Jdright  						=  '".$row_companymaster_extradetails_shadow_arr[Jdright]."',
										 //~ LifestyleTag  					= '".$row_companymaster_extradetails_shadow_arr[LifestyleTag]."',
										 //~ contract_calltype 				= '".$row_companymaster_extradetails_shadow_arr[contract_calltype]."',
										 //~ batch_group 					=  '".$row_companymaster_extradetails_shadow_arr[batch_group]."',
										 //~ audit_status 					= '".$row_companymaster_extradetails_shadow_arr[audit_status]."',
										 //~ createdby 						= '".$row_companymaster_extradetails_shadow_arr[createdby]."',
										 //~ createdtime 					= '".$row_companymaster_extradetails_shadow_arr[createdtime]."',
										 //~ customerID 					= '".$row_companymaster_extradetails_shadow_arr[customerID]."',
										 //~ datavalidity_flag 				= '".$row_companymaster_extradetails_shadow_arr[datavalidity_flag]."',
										 //~ deactflg 						= '".$row_companymaster_extradetails_shadow_arr[deactflg]."',
										 //~ display_flag 					= '".$row_companymaster_extradetails_shadow_arr[display_flag]."',
										 //~ fmobile 						= '".$row_companymaster_extradetails_shadow_arr[fmobile]."',
										 //~ femail 						= '".$row_companymaster_extradetails_shadow_arr[femail]."',
										 //~ flgActive 						= '".$row_companymaster_extradetails_shadow_arr[flgActive]."',
										 //~ flgApproval 					= '".$row_companymaster_extradetails_shadow_arr[flgApproval]."',
										 //~ freeze 						= '".$row_companymaster_extradetails_shadow_arr[freeze]."',
										 //~ mask 							= '".$row_companymaster_extradetails_shadow_arr[mask]."',
										 //~ future_contract_flag 			= '".$row_companymaster_extradetails_shadow_arr[future_contract_flag]."',
										 //~ hidden_flag 					= '".$row_companymaster_extradetails_shadow_arr[hidden_flag]."',
										 //~ lockDateTime 					= '".$row_companymaster_extradetails_shadow_arr[lockDateTime]."',
										 //~ lockedBy 						= '".$row_companymaster_extradetails_shadow_arr[lockedBy]."',
										 //~ temp_deactive_start 			= '".$row_companymaster_extradetails_shadow_arr[temp_deactive_start]."',
										 //~ temp_deactive_end 				= '".$row_companymaster_extradetails_shadow_arr[temp_deactive_end]."',
										 //~ micrcode 						= '".$row_companymaster_extradetails_shadow_arr[micrcode]."',
										 //~ prompt_cat_temp 				= '".$row_companymaster_extradetails_shadow_arr[prompt_cat_temp]."',
										 //~ promptype 						= '".$row_companymaster_extradetails_shadow_arr[promptype]."',
										 //~ referto 						= '".$row_companymaster_extradetails_shadow_arr[referto]."',
										 //~ serviceName 					= '".$row_companymaster_extradetails_shadow_arr[serviceName]."',
										 //~ srcEmp 						= '".$row_companymaster_extradetails_shadow_arr[srcEmp]."',
										 //~ telComm 						= '".$row_companymaster_extradetails_shadow_arr[telComm]."',
										 //~ newbusinessflag 				= '".$row_companymaster_extradetails_shadow_arr[newbusinessflag]."',
										 //~ tme_code 						= '".$row_companymaster_extradetails_shadow_arr[tme_code]."',
										 //~ original_creator 				= '".$row_companymaster_extradetails_shadow_arr[original_creator]."',
										 //~ original_date 					= '".$row_companymaster_extradetails_shadow_arr[original_date]."',
										 //~ updatedBy 						= '".$row_companymaster_extradetails_shadow_arr[updatedBy]."',
										 //~ updatedOn 						= '".$row_companymaster_extradetails_shadow_arr[updatedOn]."',
										 //~ quick_quote_flag 				= '".$row_companymaster_extradetails_shadow_arr[quick_quote_flag]."'";
			 //~ $restbl_temp_company = $dbObjme->query($tbl_insert4); 
			 if(true){
			 	$error_arr['errorCode'] 		=	0;
				$error_arr['errorStatus_companymaster_extradetails'] 	=	"Insert companymaster_extradetails_shadow Done";
			 }else{
			 	$error_arr['errorCode'] 		=	1;
				$error_arr['errorStatus_companymaster_extradetails'] 	=	"Insert companymaster_extradetails_shadow Fail";
			 }
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_select_companymaster_extradetails'] 	=	"companymaster_extradetails Data Not Found";
		}
		return json_encode($error_arr);
	}
	
	public function MoveToIDC_bid_companymaster(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$companymaster_finance 					=	array();
		$companymaster_finance['url']			=	SERVICE_IP."/transferInfo/fun_tbl_bid_companymaster_finance";
		$companymaster_finance['formate'] 		= 	'basic';
		$companymaster_finance['method'] 		=	 'post';
		$companymaster_finance['headerJson'] 	=	 'json';
		$finance_params['parentId']				=	$this->parentid;
		$companymaster_finance['postData'] 		= 	json_encode($finance_params); // json encode the parameters sent in the API's
		$row_companymaster_finance				=	Utility::curlCall($companymaster_finance);
		$row_companymaster_finance 				=	json_decode($row_companymaster_finance,true);	
		if($row_companymaster_finance['errorCode'] ==0){
			$row_companymaster_finance_arr		= 	$row_companymaster_finance['data'];	
			$tbl_del6 				=	"DELETE FROM tbl_companymaster_finance_temp WHERE parentid  = '".$this->parentid."'";							
			$restbl_temp_bid 		= 	$dbObjme->query($tbl_del6); 
				
			foreach($row_companymaster_finance_arr as $row_temp_bid_arr){
				$tbl_insert6 			= 	"INSERT INTO tbl_companymaster_finance_temp SET 
											nationalid				=	'".$row_temp_bid_arr['nationalid']."',
											sphinx_id				=	'".$row_temp_bid_arr['sphinx_id']."',
											regionid				=	'".$row_temp_bid_arr['regionid']."',
											bid_day_sel				=	'".$row_temp_bid_arr['bid_day_sel']."',
											parentid				=	'".$this->parentid."',
											campaignid				=	'".$row_temp_bid_arr['campaignid']."',
											budget					=	'".$row_temp_bid_arr['budget']."',
											duration				=	'".$row_temp_bid_arr['duration']."',
											version					=	'".$row_temp_bid_arr['version']."',
											balance					=	'".$row_temp_bid_arr['balance']."',
											start_date				=	'".$row_temp_bid_arr['start_date']."',
											end_date				=	'".$row_temp_bid_arr['end_date']."',
											smartlisting_flag		=	'".$row_temp_bid_arr['smartlisting_flag']."',
											exclusivelisting_tag	=	'".$row_temp_bid_arr['exclusivelisting_tag']."',
											daily_threshold			=	'".$row_temp_bid_arr['daily_threshold']."',
											recalculate_flag		=	'".$row_temp_bid_arr['recalculate_flag']."',
											searchcriteria			=	'".$row_temp_bid_arr['searchcriteria']."'";
				$restbl_temp_bid 		=	$dbObjme->query($tbl_insert6); 
				if($restbl_temp_bid){
					$error_arr['errorCode'] 	=	0;
					$error_arr['errorStatus_companymaster_finance_temp'] 	=	"Insert companymaster_finance_temp Done";
				}else{
					$error_arr['errorCode'] 	=	1;
					$error_arr['errorStatus_companymaster_finance_temp'] 	=	"Insert companymaster_finance_temp  Fail";
				}
			}
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_Select_companymaster_finance'] 	=	"companymaster_finance Data Not Found";
		}
		return json_encode($error_arr);
	}
	
	public function MoveToIDC_smsbid_temp(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$tbl_smsbid_temp 				=	array();
		$tbl_smsbid_temp['url']			=	SERVICE_IP."/transferInfo/fun_tbl_smsbid_temp";
		$tbl_smsbid_temp['formate'] 	= 	'basic';
		$tbl_smsbid_temp['method'] 		=	'post';
		$tbl_smsbid_temp['headerJson'] 	=	'json';
		$smsbid_params['parentId']		=	$this->parentid;
		$tbl_smsbid_temp['postData'] 	= 	json_encode($smsbid_params); // json encode the parameters sent in the API's
		$row_tbl_smsbid_temp			=	Utility::curlCall($tbl_smsbid_temp);
		$row_tbl_smsbid_temp 			=	json_decode($row_tbl_smsbid_temp,true);	
		if($row_tbl_smsbid_temp['errorCode'] ==0){
			$row_tbl_smsbid_temp_arr		= 	$row_tbl_smsbid_temp['data'];	
			$tbl_del7 			=	"DELETE FROM tbl_smsbid_temp WHERE Bcontractid  = '".$this->parentid."'";							
			$restbl_temp_sms 	= 	$dbObjme->query($tbl_del7);
			foreach($row_tbl_smsbid_temp_arr as $row_temp_general){
				$tbl_insert7 		= 	"INSERT INTO tbl_smsbid_temp SET 
										 BContractId    = '".$this->parentid."',
										 TContractId 	= '".$row_temp_general[TContractId]."',
										 bid_value		= '".$row_temp_general[bid_value]."',
										 promo_txt      = '".$row_temp_general[promo_txt]."',
										 rflag        	= '".$row_temp_general[rflag]."',
										 autoid 		= '".$row_temp_general[autoid]."',
										 Bid          	= '".$row_temp_general[Bid]."',
										 Tid 			= '".$row_temp_general[Tid]."',
										 daily_sms      = '".$row_temp_general[daily_sms]."',
										 active_time    = '".$row_temp_general[active_time]."',
										 new_promo      = '".$row_temp_general[new_promo]."',
										 activeMap      = '".$row_temp_general[activeMap]."'";
				 $restbl_temp_sms 	= 	$dbObjme->query($tbl_insert7); 
				 if($restbl_temp_sms){
			 		$error_arr['errorCode'] 	=	0;
					$error_arr['errorStatus_smsbid_temp'] 	=	"Insert smsbid_temp Done";
				}else{
			 		$error_arr['errorCode'] 	=	1;
					$error_arr['errorStatus_smsbid_temp'] 	=	"Insert smsbid_temp Fail";
				}
			}
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_Select_smsbid_temp'] 	=	"smsbid_temp Data Not Found";
		}
		return json_encode($error_arr);
	}
	
	public function MoveToIDC_temp_enhancements(){
		header('Content-Type: application/json');
		$dbObjme							=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 							=	$this->parentid;
		$mecode 							=	$this->MECode;
		$error_arr 							=	array();
		$temp_enhancements 					=	array();
		$temp_enhancements['url']			=	SERVICE_IP."/transferInfo/fun_tbl_business_temp_enhancements";
		$temp_enhancements['formate'] 		= 	'basic';
		$temp_enhancements['method'] 		=	 'post';
		$temp_enhancements['headerJson'] 	=	 'json';
		$enhancements_params['parentId']	=	$this->parentid;
		$temp_enhancements['postData'] 		= 	json_encode($enhancements_params); // json encode the parameters sent in the API's
		$row_temp_enhancements				=	Utility::curlCall($temp_enhancements);
		$row_temp_enhancements 				=	json_decode($row_temp_enhancements,true);	
		if($row_temp_enhancements['errorCode'] ==0){
			$row_temp_enhancements_arr		= 	$this->addslashesArray($row_temp_enhancements['data']);
			$tbl_del8 						=	"DELETE FROM tbl_business_temp_enhancements WHERE contractid  = '".$this->parentid."'";
			$restbl_temp_enhancements 		=	 $dbObjme->query($tbl_del8);
			$tbl_insert8 					= 	"INSERT INTO tbl_business_temp_enhancements SET 
												 contractid      	= 	'".$this->parentid."',
												 video_facility     = 	'".$row_temp_enhancements_arr[video_facility]."',
												 logo_facility		= 	'".$row_temp_enhancements_arr[logo_facility]."',
												 catalog_facility   = 	'".$row_temp_enhancements_arr['catalog_facility']."'";
			 $restbl_temp_enhancements 		=	$dbObjme->query($tbl_insert8); 
			 if($restbl_temp_enhancements){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_business_temp_enhancements'] 	=	"Insert business_temp_enhancements Done";
			 }else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_business_temp_enhancements'] 	=	"Insert business_temp_enhancements Fail";
			 }
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_Select_business_temp_enhancements'] 		=	"business_temp_enhancements Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_alt_address_update(){
		header('Content-Type: application/json');
		//$dbObjme										=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 										=	$this->parentid;
		$mecode 										=	$this->MECode;
		$error_arr 										=	array();
		$altaddress_shadow_update_params				=	array();
		$altaddress_shadow_update_params['url']			=	SERVICE_IP."/transferInfo/fun_tbl_alt_address_update";
		$altaddress_shadow_update_params['formate'] 	= 	'basic';
		$altaddress_shadow_update_params['method'] 		=	'post';
		$altaddress_shadow_update_params['headerJson'] 	=	'json';
		$altaddress_shadow_update_params['parentId']	=	$this->parentid;
		$altaddress_shadow_update_params['actDate'] 	=	$this->action_DateStr;
		$altaddress_shadow_update_params['meCode']		=	$this->MECode;
		$altaddress_shadow_update['postData'] 			= 	json_encode($altaddress_shadow_update_params); // json encode the parameters sent in the API's
		$row_altaddress_shadow_update					=	Utility::curlCall($altaddress_shadow_update);
		$row_altaddress_shadow_update					=	json_decode($row_altaddress_shadow_update,true);
		if($row_altaddress_shadow_update['errorCode'] ==0){
			$error_arr['errorCode'] 		=	0;
			$error_arr['errorStatus_alt_address_update'] 		=	"alt_address_update DONE";
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_alt_address_update'] 		=	"alt_address_update FAIL";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_alt_address_insert(){
		header('Content-Type: application/json');
		$dbObjme								=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 								=	$this->parentid;
		$mecode 								=	$this->MECode;
		$error_arr 								=	array();
		$altaddress_shadow						=	array();
		$altaddress_shadow['url']				=	SERVICE_IP."/transferInfo/fun_tbl_alt_address";
		$altaddress_shadow['formate'] 			= 	'basic';
		$altaddress_shadow['method'] 			=	 'post';
		$altaddress_shadow['headerJson'] 		=	 'json';
		$altaddress_shadow_params['parentId']	=	$this->parentid;
		$altaddress_shadow['postData'] 			= 	json_encode($altaddress_shadow_params); // json encode the parameters sent in the API's
		$row_alt_address						=	Utility::curlCall($altaddress_shadow);
		$row_alt_address 						=	json_decode($row_alt_address,true);	
		if($row_alt_address['errorCode'] ==0){
			$row_alt_address_arr		= 	$row_alt_address['data'];
			$tbl_del9 				=	"DELETE FROM tbl_companymaster_extradetails_altaddress_shadow WHERE parentid  = '".$this->parentid."'";							
			$restbl_temp_bid_del 	= 	$dbObjme->query($tbl_del9); 
			foreach($row_alt_address_arr as $row_temp_bid_arr){
			 	$tbl_insert9 		=	"INSERT INTO tbl_companymaster_extradetails_altaddress_shadow 
										SET 
										companyname 	= '".addslashes(stripslashes($row_temp_bid_arr['companyname']))."',
										parentid 		= '".addslashes($this->parentid)."',
										country 		= '".addslashes($row_temp_bid_arr['country'])."',
										state 			= '".addslashes($row_temp_bid_arr['state'])."',
										city			= '".addslashes($row_temp_bid_arr['city'])."',
										area 			= '".addslashes($row_temp_bid_arr['area'])."',
										building_name 	= '".addslashes(stripslashes($row_temp_bid_arr['building_name']))."',
										street 			= '".addslashes($row_temp_bid_arr['street'])."', 
										landmark 		= '".addslashes($row_temp_bid_arr['landmark'])."',
										pincode 		= '".addslashes($row_temp_bid_arr['pincode'])."',
										country_id 		= '".addslashes($row_temp_bid_arr['country_id'])."',
										state_id 		= '".addslashes($row_temp_bid_arr['state_id'])."',
										city_id 		= '".addslashes($row_temp_bid_arr['city_id'])."',
										insertdate 		= '".$row_temp_bid_arr['insertdate']."',
										tmeCode 		=  '".$row_temp_bid_arr['tmeCode']."',
										actionDate 		=  '".$row_temp_bid_arr['actionDate']."',
										meCode 			=  '".$row_temp_bid_arr['meCode']."'
										";
				$restbl_temp_bid	= 	$dbObjme->query($tbl_insert9); 
				if($restbl_temp_bid){
			 		$errFlag 	=	0;
				}else{
				 	$errFlag 	=	1;
				}
			}
			if($errFlag==0){
				$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_companymaster_extradetails_altaddress_shadow'] 	=	"Insertion companymaster_extradetails_altaddress_shadow Done";
			}else{
				$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_companymaster_extradetails_altaddress_shadow'] 	=	"Insertion companymaster_extradetails_altaddress_shadow Fail";
			}
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_alt_address'] 	=	"alt_address Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_geoCodes_insert(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$compgeocodes_shadow						=	array();
		$compgeocodes_shadow['url']					=	SERVICE_IP."/transferInfo/fun_geocode";
		$compgeocodes_shadow['formate'] 			= 	'basic';
		$compgeocodes_shadow['method'] 				=	'post';
		$compgeocodes_shadow['headerJson'] 			=	'json';
		$compgeocodes_shadow_params['parentId']		=	$this->parentid;
		$compgeocodes_shadow['postData'] 			= 	json_encode($compgeocodes_shadow_params); // json encode the parameters sent in the API's
		$row_compgeocodes_shadow					=	Utility::curlCall($compgeocodes_shadow);
		$row_compgeocodes_shadow 					=	json_decode($row_compgeocodes_shadow,true);	
		if($row_compgeocodes_shadow['errorCode'] ==0){
			$row_compgeocodes_shadow_arr		= 	$this->addslashesArray($row_compgeocodes_shadow['data']);
			$qry 	=	"DELETE FROM tbl_compgeocodes_shadow WHERE parentid  = '".$this->parentid."'";
			$res 	=	$dbObjme->query($qry);
			$qry_ins 	= 	"INSERT INTO tbl_compgeocodes_shadow SET 
							parentid				=	'".$this->parentid."',
							latitude_area			=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[latitude_area]))."',
							longitude_area			=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[longitude_area]))."',
							latitude_pincode		=	'".$row_compgeocodes_shadow_arr[latitude_pincode]."',
							longitude_pincode		=	'".$row_compgeocodes_shadow_arr[longitude_pincode]."',
							latitude_street			=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[latitude_street]))."',
							longitude_street		=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[longitude_street]))."',
							latitude_bldg			=	'".$row_compgeocodes_shadow_arr[latitude_bldg]."',
							longitude_bldg			=	'".$row_compgeocodes_shadow_arr[longitude_bldg]."',
							latitude_final			=	'".$row_compgeocodes_shadow_arr[latitude_final]."',
							longitude_final			=	'".$row_compgeocodes_shadow_arr[longitude_final]."',
							logdatetime				=	'".$row_compgeocodes_shadow_arr[logdatetime]."',
							mappedby				=	'".$row_compgeocodes_shadow_arr[mappedby]."',
							latitude_landmark		=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[latitude_landmark]))."',
							longitude_landmark		=	'".addslashes(stripslashes($row_compgeocodes_shadow_arr[longitude_landmark]))."' ";
			$res_ins 	=	$dbObjme->query($qry_ins);
			if($res_ins){
				$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatustbl_compgeocodes_shadow'] 	=	"INSERTION compgeocodes_shadow Done";
			}else{
				$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatustbl_compgeocodes_shadow'] 	=	"INSERTION compgeocodes_shadow Fail";
			}
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_compgeocodes_shadow'] 	=	"compgeocodes_shadow Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_catspon_temp_insert(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$tbl_catspon_temp							=	array();
		$tbl_catspon_temp['url']					=	SERVICE_IP."/transferInfo/fun_tbl_catspon_temp";
		$tbl_catspon_temp['formate'] 				= 	'basic';
		$tbl_catspon_temp['method'] 				=	'post';
		$tbl_catspon_temp['headerJson'] 			=	'json';
		$tbl_catspon_temp_params['parentId']		=	$this->parentid;
		$tbl_catspon_temp['postData'] 				= 	json_encode($tbl_catspon_temp_params); // json encode the parameters sent in the API's
		$row_tbl_catspon_temp						=	Utility::curlCall($tbl_catspon_temp);
		$row_tbl_catspon_temp 						=	json_decode($row_tbl_catspon_temp,true);	
		if($row_tbl_catspon_temp['errorCode'] ==0){
			$row_tbl_catspon_temp_arr		= 	$row_tbl_catspon_temp['data'];
			$tbl_del9 						=	"DELETE FROM tbl_catspon_temp WHERE parentid  = '".$this->parentid."'";
			$restbl_catspon_temp 			=	$dbObjme->query($tbl_del9);

			foreach($row_tbl_catspon_temp_arr as $catspon_arr){
					 $tbl_insert9       = 	"INSERT INTO tbl_catspon_temp SET
											 parentid           = '".$this->parentid."',
											 budget             = '".$catspon_arr[budget]."',
											 update_date        = '".$catspon_arr[update_date]."',
											 cat_name           = '".addslashes(stripslashes($catspon_arr[cat_name]))."',
											 catid              = '".$catspon_arr[catid]."',
											 tenure             = '".$catspon_arr[tenure]."',
											 start_date         = '".$catspon_arr[start_date]."',
											 end_date           = '".$catspon_arr[end_date]."',
											 bid_per_day        = '".$catspon_arr[bid_per_day]."',
											 campaign_type      = '".addslashes(stripslashes($catspon_arr[campaign_type]))."',
											 campaign_name      = '".addslashes(stripslashes($catspon_arr[campaign_name]))."',
											 variable_budget 	= '".$catspon_arr[variable_budget]."'";
				 
				$restbl_catspon_temp 	= 	$dbObjme->query($tbl_insert9);
				if($restbl_catspon_temp){
			 		$error_arr['errorCode'] 	=	0;
					$error_arr['errorStatus_tbl_catspon_temp'] 	=	"insert errorStatus_tbl_catspon_temp Done";
				}else{
			 		$error_arr['errorCode'] 	=	1;
					$error_arr['errorStatus_tbl_catspon_temp'] 	=	"insert errorStatus_tbl_catspon_temp Fail";
				}
			}	
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_catspon_temp'] 	=	"catspon_temp Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_jd_rev_rat_insert(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$tbl_jdratings_sales						=	array();
		$tbl_jdratings_sales['url']					=	SERVICE_IP."/transferInfo/fun_tbl_jd_rev_rat";
		$tbl_jdratings_sales['formate'] 			= 	'basic';
		$tbl_jdratings_sales['method'] 				=	'post';
		$tbl_jdratings_sales['headerJson'] 			=	'json';
		$tbl_jdratings_sales_params['parentId']		=	$this->parentid;
		$tbl_jdratings_sales['postData'] 			= 	json_encode($tbl_jdratings_sales_params); // json encode the parameters sent in the API's
		$row_tbl_jdratings_sales					=	Utility::curlCall($tbl_jdratings_sales);
		$row_tbl_jdratings_sales 					=	json_decode($row_tbl_jdratings_sales,true);	
		if($row_tbl_jdratings_sales['errorCode'] == 	0){
			$row_tbl_jdratings_sales_arr		= 	$row_tbl_jdratings_sales['data'];
			$tbl_del_rev_rat 	= 	"DELETE FROM tbl_jdratings_sales WHERE parentid  = '".$this->parentid."'";
			$res_del_rev_rat 	= 	$dbObjme->query($tbl_del_rev_rat);

			$qry_ins_rev_rat 	= 	"INSERT INTO tbl_jdratings_sales SET 
									compname			=	'".addslashes(stripslashes($row_tbl_jdratings_sales_arr[compname]))."',
									docid				=	'".$row_tbl_jdratings_sales_arr[docid]."',
									contact_person		=	'".addslashes(stripslashes($row_tbl_jdratings_sales_arr[contact_person]))."',
									contact_number		=	'".$row_tbl_jdratings_sales_arr[contact_number]."',
									paid				=	'".$row_tbl_jdratings_sales_arr[paid]."',
									rating				=	'".$row_tbl_jdratings_sales_arr[rating]."',
									no_of_rating		=	'".$row_tbl_jdratings_sales_arr[no_of_rating]."',
									company_callcount	=	'".$row_tbl_jdratings_sales_arr[company_callcount]."',
									tmecode				=	'".$mecode."',
									done_flag			=	'".$row_tbl_jdratings_sales_arr[done_flag]."',
									block_flag			=	'".$row_tbl_jdratings_sales_arr[block_flag]."',
									parentid			=	'".$this->parentid."'";
			$res_ins_rev_rat 	= 	$dbObjme->query($qry_ins_rev_rat); 
			if($res_ins_rev_rat){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_tbl_jdratings_sales'] 	=	"INSERT tbl_jdratings_sales Done";
			 }else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_tbl_jdratings_sales'] 	=	"INSERT tbl_jdratings_sales Fail";
			 }
			$tbl_del_rev_rat_cont 	= 	"DELETE FROM tbl_jd_reviewrating_contracts WHERE parentid  = '".$this->parentid."'";
			$res_del_rev_rat_cont 	= 	$dbObjme->query($tbl_del_rev_rat_cont);
			if($res_del_rev_rat_cont){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_tbl_jd_reviewrating_contracts'] 	=	"delete tbl_jd_reviewrating_contracts Done";
			}else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_tbl_jd_reviewrating_contracts'] 	=	"delete tbl_jd_reviewrating_contracts Fail";
			}
			$qry_ins_rev_rat_cont	= 	"INSERT INTO tbl_jd_reviewrating_contracts SET 
										parentid	=	'".$this->parentid."',
										tmecode		=	'".$row_tbl_jdratings_sales_arr[tmecode]."',
										mecode		=	'".$mecode."',
										uptDate		=	'".date('Y-m-d H:i:s')."'";
			$res_ins_rev_rat_cont 	= 	$dbObjme->query($qry_ins_rev_rat_cont); 
			if($res_ins_rev_rat_cont){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_tbl_jd_reviewrating_contracts'] 	=	"INSERT tbl_jd_reviewrating_contracts Done";
			}else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_tbl_jd_reviewrating_contracts'] 	=	"INSERT tbl_jd_reviewrating_contracts Fail";
			}
		}else{
			$error_arr['errorCode'] 	=	1;
			$error_arr['errorStatus_jd_rev_rat'] 	=	"jd_rev_rat Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_fun_unapproved_geocode(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$tbl_jdratings_sales						=	array();
		$tbl_jdratings_sales['url']					=	SERVICE_IP."/transferInfo/fun_tbl_jd_rev_rat";
		$tbl_jdratings_sales['formate'] 			= 	'basic';
		$tbl_jdratings_sales['method'] 				=	'post';
		$tbl_jdratings_sales['headerJson'] 			=	'json';
		$tbl_jdratings_sales_params['parentId']		=	$parentId;
		$tbl_jdratings_sales['postData'] 			= 	json_encode($tbl_jdratings_sales_params); // json encode the parameters sent in the API's
		$row_unapproved_building_geocodes_arr		=	Utility::curlCall($tbl_jdratings_sales);
		$row_unapproved_building_geocodes_arr		=	json_decode($row_tbl_jdratings_sales,true);	
		if($row_unapproved_building_geocodes_arr['errorCode'] == 	0){
			$row_unapproved_building_geocodes_arr	=	$row_unapproved_building_geocodes_arr['data'];
			$sql 									= 	"DELETE FROM unapproved_building_geocodes WHERE parentid  = '".$this->parentid."'";
			$result 								= 	$dbObjme->query($sql);
			if($result){
				$error_arr['errorCode'] 		=	0;
				$error_arr['errorStatus_DLETE_unapproved_building_geocodes'] 	=	"errorStatus_DLETE_unapproved_building_geocodes";
			}else{
			 	$error_arr['errorCode'] 		=	1;
				$error_arr['errorStatus_DLETE_unapproved_building_geocodes'] 	=	"errorStatus_DLETE_unapproved_building_geocodes Fail";
			}
			//return json_encode($error_arr);die();
			$sql_ins 								= 	"INSERT INTO unapproved_building_geocodes SET 
														parentid			=	'".$this->parentid."',
														username			=	'".addslashes(stripslashes($row_unapproved_building_geocodes_arr[username]))."',
														userid				=	'".$row_unapproved_building_geocodes_arr[userid]."',
														temp_latitude		=	'".$row_unapproved_building_geocodes_arr[temp_latitude]."',
														temp_longitude		=	'".$row_unapproved_building_geocodes_arr[temp_longitude]."',
														approved_latitude	=	'".$row_unapproved_building_geocodes_arr[approved_latitude]."',
														approved_longitude	=	'".$row_unapproved_building_geocodes_arr[approved_longitude]."',
														temp_tagging		=	'".$row_unapproved_building_geocodes_arr[temp_tagging]."',
														approval_flag		=	'".$row_unapproved_building_geocodes_arr[approval_flag]."',
														date				=	'".$row_unapproved_building_geocodes_arr[date]."' ";
			$result_ins 							= 	$dbObjme->query($sql_ins);
			if($result_ins){
				$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_unapproved_building_geocodes'] 	=	"insert unapproved_building_geocodes Done";
			 }else{
				$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_unapproved_building_geocodes'] 	=	"insert unapproved_building_geocodes Fail";
			 }
		}else{
			$error_arr['errorCode'] 	=	1;
			$error_arr['errorStatus_Select_building_geocodes'] 	=	"building_geocodes Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function MoveToIDC_companymaster_generalInfo_shadow(){
		header('Content-Type: application/json');
		$dbObjme					=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 					=	$this->parentid;
		$mecode 					=	$this->MECode;
		$error_arr 					=	array();
		$row_temp_general 			=	json_decode($this->get_generalinfo_shadow(),true);	
		if($row_temp_general['errorCode'] ==0){
			$row_temp_general_arr		= 	$this->addslashesArray($row_temp_general['data']);	
			//~ $tbl_del5 					=	"DELETE FROM tbl_companymaster_generalinfo_shadow WHERE parentid  = '".$this->parentid."'";							
			//~ $restbl_temp_general 		=	$dbObjme->query($tbl_del5);
			
			
			$row_gen_info = $row_temp_general_arr;
			
			$mongo_data = array();
			$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
			$geninfo_upt = array();
			$geninfo_upt['companyname'] 				= $row_gen_info['companyname'];
			$geninfo_upt['country'] 					= $row_gen_info['country'];
			$geninfo_upt['state'] 						= $row_gen_info['state'];
			$geninfo_upt['city'] 						= $row_gen_info['city'];
			$geninfo_upt['display_city'] 				= $row_gen_info['display_city'];
			$geninfo_upt['area'] 						= $row_gen_info['area'];
			$geninfo_upt['subarea'] 					= $row_gen_info['subarea'];
			$geninfo_upt['office_no'] 					= $row_gen_info['office_no'];
			$geninfo_upt['building_name']				= $row_gen_info['building_name'];
			$geninfo_upt['street'] 						= $row_gen_info['street'];
			$geninfo_upt['street_direction'] 			= $row_gen_info['street_direction'];
			$geninfo_upt['street_suffix'] 				= $row_gen_info['street_suffix'];
			$geninfo_upt['landmark'] 					= $row_gen_info['landmark'];
			$geninfo_upt['landmark_custom'] 			= $row_gen_info['landmark_custom'];
			$geninfo_upt['pincode'] 					= $row_gen_info['pincode'];
			$geninfo_upt['pincode_addinfo'] 			= $row_gen_info['pincode_addinfo'];
			$geninfo_upt['latitude'] 					= $row_gen_info['latitude'];
			$geninfo_upt['longitude'] 					= $row_gen_info['longitude'];
			$geninfo_upt['geocode_accuracy_level'] 		= $row_gen_info['geocode_accuracy_level'];
			$geninfo_upt['full_address'] 				= $row_gen_info['full_address'];
			$geninfo_upt['stdcode'] 					= $row_gen_info['stdcode'];
			$geninfo_upt['landline'] 					= $row_gen_info['landline'];
			$geninfo_upt['landline_display'] 			= $row_gen_info['landline_display'];
			$geninfo_upt['landline_feedback'] 			= $row_gen_info['landline_feedback'];
			$geninfo_upt['mobile'] 						= $row_gen_info['mobile'];
			$geninfo_upt['mobile_display'] 				= $row_gen_info['mobile_display'];
			$geninfo_upt['mobile_feedback'] 			= $row_gen_info['mobile_feedback'];
			$geninfo_upt['fax'] 						= $row_gen_info['fax'];
			$geninfo_upt['tollfree'] 					= $row_gen_info['tollfree'];
			$geninfo_upt['tollfree_display'] 			= $row_gen_info['tollfree_display'];
			$geninfo_upt['email'] 						= $row_gen_info['email'];
			$geninfo_upt['email_display'] 				= $row_gen_info['email_display'];
			$geninfo_upt['email_feedback'] 				= $row_gen_info['email_feedback'];
			$geninfo_upt['sms_scode'] 					= $row_gen_info['sms_scode'];
			$geninfo_upt['website'] 					= $row_gen_info['website'];
			$geninfo_upt['contact_person'] 				= $row_gen_info['contact_person'];
			$geninfo_upt['contact_person_display'] 		= $row_gen_info['contact_person_display'];
			$geninfo_upt['callconnect'] 				= $row_gen_info['callconnect'];
			$geninfo_upt['virtualNumber'] 				= $row_gen_info['virtualNumber'];
			$geninfo_upt['virtual_mapped_number'] 		= $row_gen_info['virtual_mapped_number'];
			$geninfo_upt['blockforvirtual'] 			= $row_gen_info['blockforvirtual'];
			$geninfo_upt['othercity_number'] 			= $row_gen_info['othercity_number'];
			$geninfo_upt['paid'] 						= $row_gen_info['paid'];
			$geninfo_upt['displayType'] 				= $row_gen_info['displayType'];
			$geninfo_upt['company_callcnt'] 			= $row_gen_info['company_callcnt'];
			$geninfo_upt['company_callcnt_rolling'] 	= $row_gen_info['company_callcnt_rolling'];
			$geninfo_upt['hide_address'] 				= $row_gen_info['hide_address'];
			$geninfo_upt['data_city'] 					= $row_gen_info['data_city'];
			$geninfo_upt['mobile_admin'] 				= $row_gen_info['mobile_admin'];								
			$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
			
			$geninfo_ins = array();
			$geninfo_ins['nationalid'] 	= $row_gen_info['nationalid'];
			$geninfo_ins['sphinx_id'] 	= $row_gen_info['sphinx_id'];
			$geninfo_ins['docid'] 		= $row_gen_info['docid'];
			$geninfo_ins['regionid'] 	= $row_gen_info['regionid'];
			$mongo_data[$geninfo_tbl]['insertdata'] = $geninfo_ins;
			
			
			$geninfo_params = array(
				"action"			=>	"updatedata",
				"post_data"			=>	"1",
				"parentid"			=>	$this->parentid,
				"data_city"			=>	$this->dat_city,
				"module"			=>	"ME",
				"table_data" 		=>	http_build_query($mongo_data)
			);
			$url =	JDBOX_API.'/services/mongoWrapper.php';
			$curlParams	=	array();
			$curlParams['url']		=	$url;
			$curlParams['method']	=	'post';
			$curlParams['formate'] = 	'basic';
			$curlParams['postData'] = $geninfo_params;
			$geninfo_resp	=	json_decode(Utility::curlCall($curlParams),true);
			
			//~ $tbl_insert5 				= 	"INSERT INTO tbl_companymaster_generalinfo_shadow SET 
											 //~ nationalid      			= '".$row_temp_general_arr[nationalid]."',
											 //~ docid      				= '".$row_temp_general_arr[docid]."',
											 //~ sphinx_id      			= '".$row_temp_general_arr[sphinx_id]."',
											 //~ regionid					= '".$row_temp_general_arr[regionid]."',
											 //~ companyname        		= '".addslashes(stripslashes($row_temp_general_arr[companyname]))."',
											 //~ parentid 					= '".$this->parentid."',
											 //~ country          			= '".$row_temp_general_arr[country]."',
											 //~ state 						= '".$row_temp_general_arr[state]."',
											 //~ city           			= '".$row_temp_general_arr[city]."',
											 //~ display_city     			= '".$row_temp_general_arr[display_city]."',
											 //~ area      					= '".addslashes(stripslashes($row_temp_general_arr[area]))."',
											 //~ subarea         			= '".addslashes(stripslashes($row_temp_general_arr[subarea]))."',
											 //~ office_no     				= '".$row_temp_general_arr[office_no]."',
											 //~ building_name     		    = '".addslashes(stripslashes($row_temp_general_arr[building_name]))."',
											 //~ street        				= '".addslashes(stripslashes($row_temp_general_arr['street']))."',
											 //~ street_direction      		= '".addslashes(stripslashes($row_temp_general_arr[street_direction]))."',
											 //~ street_suffix     			= '".addslashes(stripslashes($row_temp_general_arr[street_suffix]))."',
											 //~ landmark          			= '".addslashes(stripslashes($row_temp_general_arr[landmark]))."',
											 //~ landmark_custom     	 	= '".addslashes(stripslashes($row_temp_general_arr[landmark_custom]))."',
											 //~ pincode 					= '".$row_temp_general_arr[pincode]."',
											 //~ pincode_addinfo   			= '".$row_temp_general_arr[pincode_addinfo]."',
											 //~ latitude   				= '".$row_temp_general_arr[latitude]."',
											 //~ longitude 					= '".$row_temp_general_arr[longitude]."',
											 //~ geocode_accuracy_level 	= '".$row_temp_general_arr[geocode_accuracy_level]."',
											 //~ full_address    			= '".$row_temp_general_arr[full_address]."',
											 //~ stdcode  					=  '".$row_temp_general_arr[stdcode]."',
											 //~ landline      				= '".addslashes(stripslashes($row_temp_general_arr[landline]))."',
											 //~ landline_display			= '".addslashes(stripslashes($row_temp_general_arr[landline_display]))."',
											 //~ landline_feedback 			= '".addslashes(stripslashes($row_temp_general_arr[landline_feedback]))."',
											 //~ mobile       				= '".addslashes(stripslashes($row_temp_general_arr[mobile]))."',
											 //~ mobile_display    			= '".addslashes(stripslashes($row_temp_general_arr[mobile_display]))."',
											 //~ mobile_feedback   			= '".addslashes(stripslashes($row_temp_general_arr[mobile_feedback]))."',
											 //~ fax   						= '".$row_temp_general_arr[fax]."',
											 //~ tollfree					= '".$row_temp_general_arr[tollfree]."',
											 //~ tollfree_display    		= '".$row_temp_general_arr[tollfree_display]."',
											 //~ email 						= '".$row_temp_general_arr[email]."',
											 //~ email_display   			= '".$row_temp_general_arr[email_display]."',
											 //~ email_feedback  			= '".$row_temp_general_arr[email_feedback]."',
											 //~ sms_scode  				=  '".$row_temp_general_arr[sms_scode]."',
											 //~ website  					= '".$row_temp_general_arr[website]."',
											 //~ contact_person 			= '".$row_temp_general_arr[contact_person]."',
											 //~ contact_person_display 	=  '".$row_temp_general_arr[contact_person_display]."',
											 //~ callconnect 				= '".$row_temp_general_arr[callconnect]."',
											 //~ virtualNumber 				= '".$row_temp_general_arr[virtualNumber]."',
											 //~ virtual_mapped_number 		= '".$row_temp_general_arr[virtual_mapped_number]."',
											 //~ blockforvirtual 			= '".$row_temp_general_arr[blockforvirtual]."',
											 //~ othercity_number 			= '".$row_temp_general_arr[othercity_number]."',
											 //~ paid 						= '".$row_temp_general_arr[paid]."',
											 //~ displayType 				= '".$row_temp_general_arr[displayType]."',
											 //~ company_callcnt 			= '".$row_temp_general_arr[company_callcnt]."',
											 //~ company_callcnt_rolling 	= '".$row_temp_general_arr[company_callcnt_rolling]."',
											 //~ data_city 					= '".$row_temp_general_arr[data_city]."',
											 //~ mobile_admin				= '".$row_temp_general_arr['mobile_admin']."'
											 //~ ON DUPLICATE KEY UPDATE
											 //~ nationalid      			= '".$row_temp_general_arr[nationalid]."',
											 //~ regionid					= '".$row_temp_general_arr[regionid]."',
											 //~ companyname        		= '".addslashes(stripslashes($row_temp_general_arr[companyname]))."',
											 //~ country          			= '".$row_temp_general_arr[country]."',
											 //~ state 						= '".$row_temp_general_arr[state]."',
											 //~ city           			= '".$row_temp_general_arr[city]."',
											 //~ display_city     			= '".$row_temp_general_arr[display_city]."',
											 //~ area      					= '".addslashes(stripslashes($row_temp_general_arr[area]))."',
											 //~ subarea         			= '".addslashes(stripslashes($row_temp_general_arr[subarea]))."',
											 //~ office_no     				= '".$row_temp_general_arr[office_no]."',
											 //~ building_name     		    = '".addslashes(stripslashes($row_temp_general_arr[building_name]))."',
											 //~ street        				= '".addslashes(stripslashes($row_temp_general_arr[street]))."',
											 //~ street_direction      		= '".addslashes(stripslashes($row_temp_general_arr[street_direction]))."',
											 //~ street_suffix     			= '".addslashes(stripslashes($row_temp_general_arr[street_suffix]))."',
											 //~ landmark          			= '".addslashes(stripslashes($row_temp_general_arr[landmark]))."',
											 //~ landmark_custom     	 	= '".addslashes(stripslashes($row_temp_general_arr[landmark_custom]))."',
											 //~ pincode 					= '".$row_temp_general_arr[pincode]."',
											 //~ pincode_addinfo   			= '".$row_temp_general_arr[pincode_addinfo]."',
											 //~ latitude   				= '".$row_temp_general_arr[latitude]."',
											 //~ longitude 					= '".$row_temp_general_arr[longitude]."',
											 //~ geocode_accuracy_level 	= '".$row_temp_general_arr[geocode_accuracy_level]."',
											 //~ full_address    			= '".addslashes(stripslashes($row_temp_general_arr[full_address]))."',
											 //~ stdcode  					=  '".addslashes(stripslashes($row_temp_general_arr[stdcode]))."',
											 //~ landline      				= '".addslashes(stripslashes($row_temp_general_arr[landline]))."',
											 //~ landline_display			= '".addslashes(stripslashes($row_temp_general_arr[landline_display]))."',
											 //~ landline_feedback 			= '".addslashes(stripslashes($row_temp_general_arr[landline_feedback]))."',
											 //~ mobile       				= '".addslashes(stripslashes($row_temp_general_arr[mobile]))."',
											 //~ mobile_display    			= '".addslashes(stripslashes($row_temp_general_arr[mobile_display]))."',
											 //~ mobile_feedback   			= '".addslashes(stripslashes($row_temp_general_arr[mobile_feedback]))."',
											 //~ fax   						= '".$row_temp_general_arr[fax]."',
											 //~ tollfree					= '".$row_temp_general_arr[tollfree]."',
											 //~ tollfree_display    		= '".addslashes(stripslashes($row_temp_general_arr[tollfree_display]))."',
											 //~ email 						= '".addslashes(stripslashes($row_temp_general_arr[email]))."',
											 //~ email_display   			= '".addslashes(stripslashes($row_temp_general_arr[email_display]))."',
											 //~ email_feedback  			= '".addslashes(stripslashes($row_temp_general_arr[email_feedback]))."',
											 //~ sms_scode  				=  '".$row_temp_general_arr[sms_scode]."',
											 //~ website  					= '".$row_temp_general_arr[website]."',
											 //~ contact_person 			= '".addslashes(stripslashes($row_temp_general_arr[contact_person]))."',
											 //~ contact_person_display 	=  '".$row_temp_general_arr[contact_person_display]."',
											 //~ callconnect 				= '".$row_temp_general_arr[callconnect]."',
											 //~ virtualNumber 				= '".$row_temp_general_arr[virtualNumber]."',
											 //~ virtual_mapped_number 		= '".$row_temp_general_arr[virtual_mapped_number]."',
											 //~ blockforvirtual 			= '".$row_temp_general_arr[blockforvirtual]."',
											 //~ othercity_number 			= '".$row_temp_general_arr[othercity_number]."',
											 //~ paid 						= '".$row_temp_general_arr[paid]."',
											 //~ displayType 				= '".$row_temp_general_arr[displayType]."',
											 //~ company_callcnt 			= '".$row_temp_general_arr[company_callcnt]."',
											 //~ company_callcnt_rolling 	= '".$row_temp_general_arr[company_callcnt_rolling]."',
											 //~ mobile_admin				= '".$row_temp_general_arr['mobile_admin']."',
											 //~ data_city 					= '".$row_temp_general_arr[data_city]."'";
//~ 
			 //~ $restbl_temp_general 		= 	$dbObjme->query($tbl_insert5);
			 
			 if(true){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_companymaster_generalinfo_shadow_IDC'] 	=	"INSERT companymaster_generalinfo_shadow_IDC Done";
			 }else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_companymaster_generalinfo_shadow_IDC'] 	=	"INSERT companymaster_generalinfo_shadow_IDC Fail";
			 }
		}else{
			$error_arr['errorCode'] 	=	1;
			$error_arr['errorStatus_generalinfo_shadow'] 	=	"generalinfo_shadow Data Not Found";
		}
		return json_encode($error_arr);
	}
	public function moveTo_IDC_temp_intermediate(){
		header('Content-Type: application/json');
		$dbObjme								=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 								=	$this->parentid;
		$mecode 								=	$this->MECode;
		$error_arr 								=	array();
		$curlParams_intermediate 				=	array();
		$curlParams_intermediate['url']			=	SERVICE_IP."/transferInfo/fun_tbl_temp_intermediate";
		$curlParams_intermediate['formate'] 	= 	'basic';
		$curlParams_intermediate['method'] 		=	 'post';
		$curlParams_intermediate['headerJson'] 	=	 'json';
		$intermediate_params['parentId']		=	$this->parentid;
		$curlParams_intermediate['postData'] 	= 	json_encode($intermediate_params); // json encode the parameters sent in the API's
		$row_temp_intermediate					=	Utility::curlCall($curlParams_intermediate);
		$row_temp_intermediate 					=	json_decode($row_temp_intermediate,true);	
		if($row_temp_intermediate['errorCode'] ==0){
			$row_temp_intermediate_arr		= 	$this->addslashesArray($row_temp_intermediate['data']);
			//~ $tbl_del3 					=	"DELETE FROM tbl_temp_intermediate WHERE parentid  = '".$this->parentid."'";							
			//~ $restbl_temp_intermediate 	= 	$dbObjme->query($tbl_del3);
			
			
			
			$mongo_data = array();
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			$intermd_upt['contract_calltype'] 		= $row_temp_intermediate_arr['contract_calltype'];
			$intermd_upt['displayType'] 			= $row_temp_intermediate_arr['displayType'];
			$intermd_upt['deactivate'] 				= $row_temp_intermediate_arr['deactivate'];
			$intermd_upt['temp_deactive_start'] 	= $row_temp_intermediate_arr['temp_deactive_start'];
			$intermd_upt['temp_deactive_end'] 		= $row_temp_intermediate_arr['temp_deactive_end'];
			$intermd_upt['deactflg'] 				= $row_temp_intermediate_arr['deactflg'];
			$intermd_upt['freez'] 					= $row_temp_intermediate_arr['freez'];
			$intermd_upt['mask'] 					= $row_temp_intermediate_arr['mask'];
			$intermd_upt['reason_id'] 				= $row_temp_intermediate_arr['reason_id'];
			$intermd_upt['add_infotxt'] 			= $row_temp_intermediate_arr['add_infotxt'];
			$intermd_upt['mainsource'] 				= $row_temp_intermediate_arr['mainsource'];
			$intermd_upt['subsource'] 				= $row_temp_intermediate_arr['subsource'];
			$intermd_upt['datesource'] 				= $row_temp_intermediate_arr['datesource'];
			$intermd_upt['callconnect'] 			= $row_temp_intermediate_arr['callconnect'];
			$intermd_upt['callconnectid'] 			= $row_temp_intermediate_arr['callconnectid'];
			$intermd_upt['virtualNumber'] 			= $row_temp_intermediate_arr['virtualNumber'];
			$intermd_upt['virtual_mapped_number'] 	= $row_temp_intermediate_arr['virtual_mapped_number'];
			$intermd_upt['actMode'] 				= "1";
			$intermd_upt['facility_flag'] 			= "0";
			$intermd_upt['nonpaid'] 				= $row_temp_intermediate_arr['nonpaid'];
			$intermd_upt['empcode']					= $row_temp_intermediate_arr['empcode'];
			$intermd_upt['name_code'] 				= $row_temp_intermediate_arr['name_code'];
			$intermd_upt['txtTE'] 					= $row_temp_intermediate_arr['txtTE'];
			$intermd_upt['txtM'] 					= $row_temp_intermediate_arr['txtM'];
			$intermd_upt['txtME'] 					= $row_temp_intermediate_arr['txtME'];
			$intermd_upt['reason_text'] 			= $row_temp_intermediate_arr['reason_text'];
			$intermd_upt['assignTmeCode'] 			= $row_temp_intermediate_arr['assignTmeCode'];
			$intermd_upt['blockforvirtual'] 		= $row_temp_intermediate_arr['blockforvirtual'];
			$intermd_upt['generatexml'] 			= "1";
			$intermd_upt['cat_reset_flag'] 			= "0";
			$intermd_ins = array();
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$intermd_ins = array();
			$intermd_ins['tme_code'] 				= $row_temp_intermediate_arr['tme_code'];
			$mongo_data[$intermd_tbl]['insertdata'] = $intermd_ins;
			
			$inter_params = array(
				"action"			=>	"updatedata",
				"post_data"			=>	"1",
				"parentid"			=>	$this->parentid,
				"data_city"			=>	$this->dat_city,
				"module"			=>	"ME",
				"table_data" 		=>	http_build_query($mongo_data)
			);
			$url =	JDBOX_API.'/services/mongoWrapper.php';
			$curlParams	=	array();
			$curlParams['url']		=	$url;
			$curlParams['method']	=	'post';
			$curlParams['formate'] = 	'basic';
			$curlParams['postData'] = $inter_params;
			$inter_resp	=	json_decode(Utility::curlCall($curlParams),true);
			
			
			
			//~ $tbl_insert3 				=	"INSERT INTO tbl_temp_intermediate SET 
											 //~ parentid      				= '".$this->parentid."',
											 //~ contractid     			= '".$row_temp_intermediate_arr[contractid]."',
											 //~ contract_calltype			= '".addslashes(stripslashes($row_temp_intermediate_arr[contract_calltype]))."',
											 //~ displayType       			= '".addslashes(stripslashes($row_temp_intermediate_arr[displayType]))."',
											 //~ deactivate 				= '".addslashes(stripslashes($row_temp_intermediate_arr[deactivate]))."',
											 //~ temp_deactive_start    	= '".addslashes(stripslashes($row_temp_intermediate_arr[temp_deactive_start]))."',
											 //~ temp_deactive_end 			= '".addslashes(stripslashes($row_temp_intermediate_arr[temp_deactive_end]))."',
											 //~ deactflg           		= '".addslashes(stripslashes($row_temp_intermediate_arr[deactflg]))."',
											 //~ freez     					= '".addslashes(stripslashes($row_temp_intermediate_arr[freez]))."',
											 //~ mask      					= '".addslashes(stripslashes($row_temp_intermediate_arr[mask]))."',
											 //~ reason_id         			= '".addslashes(stripslashes($row_temp_intermediate_arr[reason_id]))."',
											 //~ add_infotxt     			= '".addslashes(stripcslashes($row_temp_intermediate_arr[add_infotxt]))."',
											 //~ narration          		= '".addslashes(stripcslashes($row_temp_intermediate_arr[narration]))."',
											 //~ mainsource        			= '".addslashes(stripslashes($row_temp_intermediate_arr[mainsource]))."',
											 //~ subsource        			= '".addslashes(stripcslashes($row_temp_intermediate_arr[subsource]))."',
											 //~ datesource     			= '".addslashes(stripslashes($row_temp_intermediate_arr[datesource]))."',
											 //~ name_code          		= '".addslashes(stripslashes($row_temp_intermediate_arr[name_code]))."',
											 //~ txtTE      				= '".addslashes(stripslashes($row_temp_intermediate_arr[txtTE]))."',
											 //~ txtM 						= '".addslashes(stripslashes($row_temp_intermediate_arr[txtM]))."',
											 //~ txtME   					= '".addslashes(stripslashes($row_temp_intermediate_arr[txtME]))."',
											 //~ callconnect   				= '".addslashes(stripslashes($row_temp_intermediate_arr[callconnect]))."',
											 //~ callconnectid 				= '".addslashes(stripslashes($row_temp_intermediate_arr[callconnectid]))."',
											 //~ /*virtualNumber 			= '".addslashes(stripslashes($row_temp_intermediate_arr[virtualNumber]))."',
											 //~ virtual_mapped_number  	= '".addslashes(stripslashes($row_temp_intermediate_arr[virtual_mapped_number]))."',*/
											 //~ actMode  					=  '".addslashes(stripslashes($row_temp_intermediate_arr[actMode]))."',
											 //~ nonpaid      				= '".addslashes(stripslashes($row_temp_intermediate_arr[nonpaid]))."',
											 //~ c2c						= '".addslashes(stripslashes($row_temp_intermediate_arr[c2c]))."',
											 //~ c2s 						= '".addslashes(stripslashes($row_temp_intermediate_arr[c2s]))."',
											 //~ hiddenCon       			= '".addslashes(stripslashes($row_temp_intermediate_arr[hiddenCon]))."',
											 //~ cpc    					= '".addslashes(stripslashes($row_temp_intermediate_arr[cpc]))."',
											 //~ web   						= '".addslashes(stripslashes($row_temp_intermediate_arr[web]))."',
											 //~ tme_mobile   				= '".addslashes(stripslashes($row_temp_intermediate_arr[tme_mobile]))."',
											 //~ tme_email					= '".addslashes(stripslashes($row_temp_intermediate_arr[tme_email]))."',
											 //~ tme_code    				= '".addslashes(stripslashes($row_temp_intermediate_arr[tme_code]))."',
											 //~ facility_flag 				= '".addslashes(stripslashes($row_temp_intermediate_arr[facility_flag]))."',
											 //~ empcode   					= '".addslashes(stripslashes($row_temp_intermediate_arr[empcode]))."',
											 //~ employeeCode  				= '".addslashes(stripslashes($row_temp_intermediate_arr[employeeCode]))."',
											 //~ txtEmp  					=  '".addslashes(stripslashes($row_temp_intermediate_arr[txtEmp]))."',
											 //~ reason_text  				= '".addslashes(stripslashes($row_temp_intermediate_arr[reason_text]))."',
											 //~ assignTmeCode 				= '".addslashes(stripslashes($row_temp_intermediate_arr[assignTmeCode]))."',
											 //~ /*blockforvirtual 			=  '".addslashes(stripslashes($row_temp_intermediate_arr[blockforvirtual]))."',*/
											 //~ guarantee 					= '".addslashes(stripslashes($row_temp_intermediate_arr[guarantee]))."',
											 //~ guarantee_reason 			= '".addslashes(stripslashes($row_temp_intermediate_arr[guarantee_reason]))."',
											 //~ generatexml 				= '1',
											 //~ source_parentid 			= '".addslashes(stripslashes($row_temp_intermediate_arr[source_parentid]))."',
											 //~ source_id 					= '".addslashes(stripslashes($row_temp_intermediate_arr[source_id]))."',
											 //~ paid_match 				= '".addslashes(stripslashes($row_temp_intermediate_arr[paid_match]))."',
											 //~ contracts 					= '".addslashes(stripslashes($row_temp_intermediate_arr[contracts]))."',
											 //~ significance 				= '".addslashes(stripslashes($row_temp_intermediate_arr[significance]))."',
											 //~ bronze 					= '".addslashes(stripslashes($row_temp_intermediate_arr[bronze]))."',
											 //~ exclusive 					= '".addslashes(stripslashes($row_temp_intermediate_arr[exclusive]))."',
											 //~ iscalculated 				= '".addslashes(stripslashes($row_temp_intermediate_arr[iscalculated]))."'";
											 //~ 
			 //~ $restbl_temp_intermediate = 	$dbObjme->query($tbl_insert3); 
			  if(true){
			 	$error_arr['errorCode'] 	=	0;
				$error_arr['errorStatus_temp_intermediate'] 	=	"Insert errorStatus_temp_intermediate Done";
			 }else{
			 	$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_temp_intermediate'] 	=	"Insert errorStatus_temp_intermediate Fail";
			 }	
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_select_temp_intermediate'] 	=	"temp_intermediate Data Not Found";
		}
		return json_encode($error_arr);	
	}
	/*
	*/
	public function moveTo_IDC_business_temp_data(){
		header('Content-Type: application/json');
		$dbObjme						=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 						=	$this->parentid;
		$mecode 						=	$this->MECode;
		$error_arr 						=	array();
		$curlParams_temp 				=	array();
		$curlParams_temp['url']			=	SERVICE_IP."/transferInfo/fun_tbl_business_temp_data";
		$curlParams_temp['formate'] 	= 	'basic';
		$curlParams_temp['method'] 		=	 'post';
		$curlParams_temp['headerJson'] 	=	 'json';
		$curlParams_temp['parentId']	=	$this->parentid;
		$curlParams_temp['postData'] 	= 	json_encode($curlParams_temp); // json encode the parameters sent in the API's
		$row_temp_data					=	Utility::curlCall($curlParams_temp);
		$row_temp_data 					=	json_decode($row_temp_data,true);	
		
		if($row_temp_data['errorCode'] ==0){
			$row_temp_data_arr			= 	$row_temp_data['data'];
			//~ $tbl_del 					=	"DELETE FROM tbl_business_temp_data WHERE contractid  = '".$this->parentid."'";							
			//~ $restbl_business_temp_data	= 	$dbObjme->query($tbl_del);//object of idc server
			if(true){
				
				$error_arr['errorCode'] 		=	0;
				$error_arr['errorStatus_delete_business_temp_data'] 	=	"errorStatus_delete_business_temp_data DONE";
				
				
				$mongo_data = array();
				$bustemp_tbl = "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['companyName'] 			= $row_temp_data_arr['companyName'];
				$bustemp_upt['mainattr'] 				= $row_temp_data_arr['mainattr'];
				$bustemp_upt['facility'] 				= $row_temp_data_arr['facility'];
				$bustemp_upt['categories'] 				= $row_temp_data_arr['categories'];
				$bustemp_upt['catIds'] 					= $row_temp_data_arr['catIds'];
				$bustemp_upt['nationalcatIds'] 			= $row_temp_data_arr['nationalcatIds'];
				$bustemp_upt['catSelected'] 			= $row_temp_data_arr['catSelected'];
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
				$bustmp_params = array(
					"action"			=>	"updatedata",
					"post_data"			=>	"1",
					"parentid"			=>	$this->parentid,
					"data_city"			=>	$this->dat_city,
					"module"			=>	"ME",
					"table_data" 		=>	http_build_query($mongo_data)
				);
				$url =	JDBOX_API.'/services/mongoWrapper.php';
				$curlParams	=	array();
				$curlParams['url']		=	$url;
				$curlParams['method']	=	'post';
				$curlParams['formate'] = 	'basic';
				$curlParams['postData'] = $bustmp_params;
				$bustmp_resp	=	json_decode(Utility::curlCall($curlParams),true);
				
				
				//~ $tbl_insert 	= 	"INSERT INTO tbl_business_temp_data SET 
												 //~ contractid      		= '".$this->parentid."',
												 //~ categories      		= '".$row_temp_data_arr[categories]."',
												 //~ catIds          		= '".addslashes(stripslashes($row_temp_data_arr[catIds]))."',
												 //~ htmldump        		= '".addslashes(stripslashes(htmlentities($row_temp_data_arr[htmldump], ENT_QUOTES)))."',
												 //~ slogan        			= '".addslashes(stripslashes($row_temp_data_arr[slogan]))."',
												 //~ categories_list 		= '".addslashes(stripslashes($row_temp_data_arr[categories_list]))."',
												 //~ pages           		= '".addslashes(stripslashes($row_temp_data_arr[pages]))."',
												 //~ bid_day_sel     		= '".addslashes(stripslashes($row_temp_data_arr[bid_day_sel]))."',
												 //~ bid_timing      		= '".addslashes(stripslashes($row_temp_data_arr[bid_timing]))."',
												 //~ autobid         		= '".addslashes(stripslashes($row_temp_data_arr[autobid]))."',
												 //~ catSelected     		= '".addslashes(stripslashes($row_temp_data_arr[catSelected]))."',
												 //~ uId             		= '".addslashes(stripslashes($row_temp_data_arr[uId]))."',
												 //~ mainattr        		= '".addslashes(stripslashes($row_temp_data_arr[mainattr]))."',
												 //~ facility        		= '".addslashes(stripcslashes($row_temp_data_arr[facility]))."',
												 //~ companyName     		= '".addslashes(stripcslashes($row_temp_data_arr[companyName]))."',
												 //~ avgAmt          		= '".addslashes(stripslashes($row_temp_data_arr[avgAmt]))."',
												 //~ percentage      		= '".addslashes(stripslashes($row_temp_data_arr[percentage]))."',
												 //~ comp_deduction_amt 	= '".addslashes(stripslashes($row_temp_data_arr[comp_deduction_amt]))."',
												 //~ thresholdform   		= '".addslashes(stripslashes($row_temp_data_arr[thresholdform]))."',
												 //~ thresholdType   		= '".addslashes(stripslashes($row_temp_data_arr[thresholdType]))."',
												 //~ original_catids 		= '".addslashes(stripslashes($row_temp_data_arr[original_catids]))."',
												 //~ authorised_categories 	= '".addslashes(stripslashes($row_temp_data_arr[authorised_categories]))."',
												 //~ bid_lead_num    		= '".addslashes(stripslashes($row_temp_data_arr[bid_lead_num]))."',
												 //~ bid_type        		= '".addslashes(stripslashes($row_temp_data_arr[bid_type]))."',
												 //~ nationalcatIds  		= '".addslashes(stripslashes($row_temp_data_arr[nationalcatIds]))."',
												 //~ parentname      		= '".addslashes(stripslashes($row_temp_data_arr[parentname]))."',
												 //~ bid_led_num_year		= '".addslashes(stripslashes($row_temp_data_arr[bid_led_num_year]))."',
												 //~ thresholdPercnt 		= '".addslashes(stripslashes($row_temp_data_arr[thresholdPercnt]))."',
												 //~ TotThresh       		= '".addslashes(stripslashes($row_temp_data_arr[TotThresh]))."',
												 //~ thresWeekSup    		= '".addslashes(stripslashes($row_temp_data_arr[thresWeekSup]))."',
												 //~ thresDailySup   		= '".addslashes(stripslashes($row_temp_data_arr[thresDailySup]))."',
												 //~ thresMonthSup   		= '".addslashes(stripslashes($row_temp_data_arr[thresMonthSup]))."',
												 //~ bid_lead_num_sys		= '".addslashes(stripslashes($row_temp_data_arr[bid_lead_num_sys]))."'
												 	//~ ON DUPLICATE KEY UPDATE
												 //~ categories      		= '".$row_temp_data_arr[categories]."',
												 //~ catIds          		= '".addslashes(stripslashes($row_temp_data_arr[catIds]))."',
												 //~ htmldump        		= '".addslashes(stripslashes(htmlentities($row_temp_data_arr[htmldump], ENT_QUOTES)))."',
												 //~ slogan        			= '".addslashes(stripslashes($row_temp_data_arr[slogan]))."',
												 //~ categories_list 		= '".addslashes(stripslashes($row_temp_data_arr[categories_list]))."',
												 //~ pages           		= '".addslashes(stripslashes($row_temp_data_arr[pages]))."',
												 //~ bid_day_sel     		= '".addslashes(stripslashes($row_temp_data_arr[bid_day_sel]))."',
												 //~ bid_timing      		= '".addslashes(stripslashes($row_temp_data_arr[bid_timing]))."',
												 //~ autobid         		= '".addslashes(stripslashes($row_temp_data_arr[autobid]))."',
												 //~ catSelected     		= '".addslashes(stripslashes($row_temp_data_arr[catSelected]))."',
												 //~ uId             		= '".addslashes(stripslashes($row_temp_data_arr[uId]))."',
												 //~ mainattr        		= '".addslashes(stripslashes($row_temp_data_arr[mainattr]))."',
												 //~ facility        		= '".addslashes(stripcslashes($row_temp_data_arr[facility]))."',
												 //~ companyName     		= '".addslashes(stripcslashes($row_temp_data_arr[companyName]))."',
												 //~ avgAmt          		= '".addslashes(stripslashes($row_temp_data_arr[avgAmt]))."',
												 //~ percentage      		= '".addslashes(stripslashes($row_temp_data_arr[percentage]))."',
												 //~ comp_deduction_amt 	= '".addslashes(stripslashes($row_temp_data_arr[comp_deduction_amt]))."',
												 //~ thresholdform   		= '".addslashes(stripslashes($row_temp_data_arr[thresholdform]))."',
												 //~ thresholdType   		= '".addslashes(stripslashes($row_temp_data_arr[thresholdType]))."',
												 //~ original_catids 		= '".addslashes(stripslashes($row_temp_data_arr[original_catids]))."',
												 //~ authorised_categories 	= '".addslashes(stripslashes($row_temp_data_arr[authorised_categories]))."',
												 //~ bid_lead_num    		= '".addslashes(stripslashes($row_temp_data_arr[bid_lead_num]))."',
												 //~ bid_type        		= '".addslashes(stripslashes($row_temp_data_arr[bid_type]))."',
												 //~ nationalcatIds  		= '".addslashes(stripslashes($row_temp_data_arr[nationalcatIds]))."',
												 //~ parentname      		= '".addslashes(stripslashes($row_temp_data_arr[parentname]))."',
												 //~ bid_led_num_year		= '".addslashes(stripslashes($row_temp_data_arr[bid_led_num_year]))."',
												 //~ thresholdPercnt 		= '".addslashes(stripslashes($row_temp_data_arr[thresholdPercnt]))."',
												 //~ TotThresh       		= '".addslashes(stripslashes($row_temp_data_arr[TotThresh]))."',
												 //~ thresWeekSup    		= '".addslashes(stripslashes($row_temp_data_arr[thresWeekSup]))."',
												 //~ thresDailySup   		= '".addslashes(stripslashes($row_temp_data_arr[thresDailySup]))."',
												 //~ thresMonthSup   		= '".addslashes(stripslashes($row_temp_data_arr[thresMonthSup]))."',
												 //~ bid_lead_num_sys		= '".addslashes(stripslashes($row_temp_data_arr[bid_lead_num_sys]))."'";
				//~ 
				 //~ $restbl_business_temp_data = 	$dbObjme->query($tbl_insert);
				
				 if(true){
					$error_arr['errorCode'] 	=	0;
					$error_arr['errorStatus_business_temp_data'] 	=	"Insert business_temp_data Done";
					// echo json_encode($error_arr);die();
				 }else{
					$error_arr['errorCode'] 	=	1;
					$error_arr['errorStatus_business_temp_data'] 	=	"Insert business_temp_data Fail";
				 }	
			}else{
				$error_arr['errorCode'] 		=	1;
				$error_arr['errorStatus_delete_business_temp_data'] 	=	"errorStatus_delete_business_temp_data FAIL";
			}
		}else{
			$error_arr['errorCode'] 		=	1;
			$error_arr['errorStatus_business_temp_data'] 	=	"business_temp_data Data Not Found";
		}	
		return json_encode($error_arr);
	} 
	
	/*
	 * function to move tbl_appointment_iro to IDC server
	*/
	public function moveToIdc_tbl_appointment_iro(){
		header('Content-Type: application/json');
		$dbObjme									=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 									=	$this->parentid;
		$mecode 									=	$this->MECode;
		$error_arr 									=	array();
		$tbl_appointment_iro						=	array();
		$tbl_appointment_iro['url']					=	SERVICE_IP."/transferInfo/fun_get_iro_appointment";
		$tbl_appointment_iro['formate'] 			= 	'basic';
		$tbl_appointment_iro['method'] 				=	'post';
		$tbl_appointment_iro['headerJson'] 			=	'json';
		$tbl_appointment_iro_params['parentId']		=	$this->parentid;
		$tbl_appointment_iro['postData'] 			= 	json_encode($tbl_appointment_iro_params); // json encode the parameters sent in the API's
		$row_tbl_appointment_iro					=	Utility::curlCall($tbl_appointment_iro);
		$row_tbl_appointment_iro 					=	json_decode($row_tbl_appointment_iro,true);	
		if($row_tbl_appointment_iro['errorCode'] ==0){
			$row_tbl_appointment_iro_arr		= 	$this->addslashesArray($row_tbl_appointment_iro['data']);
			$tbl_del_iro_app	=	"DELETE FROM tbl_appointment_iro WHERE parentid = '".$this->parentid."'";
			$con_del_iro_app	=	$dbObjme->query($tbl_del_iro_app);
			$ins_iro_app		=	"INSERT INTO tbl_appointment_iro SET 
									parentid			=	'".$this->parentid."',
									ironame				=	'".addslashes(stripslashes($row_tbl_appointment_iro_arr['ironame']))."',
									irocode				=	'".$row_tbl_appointment_iro_arr['irocode']."',
									irocode1			=	'".$row_tbl_appointment_iro_arr['irocode1']."',
									irocode2			=	'".$row_tbl_appointment_iro_arr['irocode2']."',
									tmecode				=	'".$row_tbl_appointment_iro_arr['tmecode']."',
									appointment_date	=	'".$row_tbl_appointment_iro_arr['appointment_date']."'";
			$con_ins_iro_app	=	$dbObjme->query($ins_iro_app);
			if($con_ins_iro_app){
				$error_arr['errorCode'] 		=	0;
				$error_arr['errorStatus_tbl_appointment_iro'] 	=	"INSERT tbl_appointment_iro Done";
			}else{
				$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_tbl_appointment_iro'] 	=	"INSERT tbl_appointment_iro Fail";
			}						

		}else{
			$error_arr['errorCode'] 	=	1;
			$error_arr['errorStatus_appointment_iro'] 	=	"appointment_iro Data Not Found";
		}
		return json_encode($error_arr);exit();
	}
	/*
	 * function to Insert In datacorrection 
	*/
	public function insertLogBformDC($empCode,$data_city){
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64'){
			$paramArray['url']	=	SERVICE_IP.'/compareInfo/insertLogBformDC/';
		}else{
			$paramArray['url']	=	SERVICE_IP.'/compareInfo/insertLogBformDC/';
		}
		$paramArray['formate']		=	'basic';
		$paramArray['method']		=	'post';
		$postArr['parentid']		=	addslashes(stripcslashes(trim($this->parentid)));
		$postArr['empcode']			=	trim($empCode);
		$postArr['data_city']		=	trim($data_city);
		$postArr['paid']			=	0;
		$postArr['disposeVal']		=	$this->dispose_type;
		$paramArray['postData']		=	http_build_query($postArr); 	
		$CorrectionInsertAPICall	= 	Utility::curlCall($paramArray); 
		return json_encode($CorrectionInsertAPICall);
	}
	public function hotDataSms(){
		$retArr			=	array();
		$con_local 		=	new DB($this->db['db_local']);
		$SelExitCont  					= "SELECT source_date,flag_source FROM d_jds.tbl_hotData WHERE parentid ='".$this->parentid."'";
		$conExitCont					=	$con_local->query($SelExitCont);
		$count							=	$con_local->numRows($conExitCont);
		$fetchExitData 					=   $con_local->fetchData($conExitCont);
		$sourceDate = date('Y-m-d', strtotime($fetchExitData['source_date']));
		
		$SelContractAll 				= "SELECT allocationTime,contractCode FROM d_jds.tblContractAllocation WHERE contractCode = '".$this->parentid."' ORDER BY allocationTime DESC LIMIT 1";
		$conExitContAlloc				=	$con_local->query($SelContractAll);
		$countAlloc						=	$con_local->numRows($conExitContAlloc);
		$fetchExitDataAlloc 			=   $con_local->fetchData($conExitContAlloc);
		$AllocationeDate 				= date('Y-m-d', strtotime($fetchExitDataAlloc['allocationTime']));
		if($AllocationeDate !== $sourceDate)
		{
			$UpdExit 				= "UPDATE d_jds.tbl_hotData SET source_date ='".$fetchExitDataAlloc['allocationTime']."',flag_source =0 WHERE parentid ='".$this->parentid."'";
			$conExitUpdSource				=	$con_local->query($UpdExit);
			if($conExitUpdSource == 1 && isset($conExitUpdSource)){
				$retArr['errorCode_tbl_hotData'] 	=	0;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Done";
			}else{
				$retArr['errorCodetbl_hotData'] 	=	1;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Fail";
			}			
		}
		else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus_tbl_hotData'] 	=	"Data Not Found";
			}
		
		return json_encode($retArr);
	}
	
	public function moveto_IDC_EcsRequest_Info(){
		header('Content-Type: application/json');
		$dbObjme				=	new DB($this->db['db_idc']);//online_regis_mumbai
		$parentId 				=	$this->parentid;
		$mecode 				=	$this->MECode;
		$ucode	 				=	$this->empCode;
		$error_arr 				=	array();
		$tbl_appointment_iro	=	array();
		$curlParams_temp['url']			=	SERVICE_IP."/transferInfo/fun_tbl_ecs_dealclose_pending";
		$curlParams_temp['formate'] 	= 	'basic';
		$curlParams_temp['method'] 		=	 'post';
		$curlParams_temp['headerJson'] 	=	 'json';
		$curlParams_temp['parentId']	=	$this->parentid;
		$curlParams_temp['mecode']		=	$mecode;
		$curlParams_temp['ucode']		=	$ucode;
		$curlParams_temp['postData'] 	= 	json_encode($curlParams_temp); // json encode the parameters sent in the API's
		$row_tbl_appointment_iro			=	Utility::curlCall($curlParams_temp);
		$EcsREquestData 					=	json_decode($row_tbl_appointment_iro,true);	

		if($EcsREquestData['errorCode'] ==0){
			$EcsREquestData_array		= 	$this->addslashesArray($EcsREquestData['data']);
			
			$del_ecs_details	=	"DELETE FROM tbl_ecs_dealclose_pending WHERE parentid = '".$this->parentid."' AND mecode = '".$mecode."'";
			$del_ecs_details_res	=	$dbObjme->query($del_ecs_details);
			$ins_newEcs_details		=	"INSERT INTO tbl_ecs_dealclose_pending SET 
									parentid			=	'".$this->parentid."',
									EmpCode				=	'".$EcsREquestData_array['EmpCode']."',
									EmpName				=	'".addslashes(stripslashes($EcsREquestData_array['EmpName']))."',
									MngrCode			=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode']))."',
									Acc_Reg_Flag			=	'".addslashes(stripslashes($EcsREquestData_array['Acc_Reg_Flag']))."',
									companyname				=	'".addslashes(stripslashes($EcsREquestData_array['companyname']))."',
									pincode				=	'".addslashes(stripslashes($EcsREquestData_array['pincode']))."',
									mobile				=	'".addslashes(stripslashes($EcsREquestData_array['mobile']))."',
									email				=	'".addslashes(stripslashes($EcsREquestData_array['email']))."',
									contact_person				=	'".addslashes(stripslashes($EcsREquestData_array['contact_person']))."',
									Mngr_Flag				=	'".addslashes(stripslashes($EcsREquestData_array['Mngr_Flag']))."',
									city				=	'".addslashes(stripslashes($EcsREquestData_array['city']))."',
									updated_on				=	'".addslashes(stripslashes($EcsREquestData_array['updated_on']))."',
									requested_on				=	'".addslashes(stripslashes($EcsREquestData_array['requested_on']))."',
									MngrCode1				=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode1']))."',
									MngrCode2				=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode2']))."',
									upd_deg_mngr				=	'".addslashes(stripslashes($EcsREquestData_array['upd_deg_mngr']))."',
									upd_deg_mngrCode				=	'".addslashes(stripslashes($EcsREquestData_array['upd_deg_mngrCode']))."',
									detail_cname				=	'".addslashes(stripslashes($EcsREquestData_array['detail_cname']))."',
									detail_cid				=	'".addslashes(stripslashes($EcsREquestData_array['detail_cid']))."',
									current_contract_value				=	'".addslashes(stripslashes($EcsREquestData_array['current_contract_value']))."',
									new_contract_value				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_value']))."',
									current_contract_type				=	'".addslashes(stripslashes($EcsREquestData_array['current_contract_type']))."',
									new_contract_type				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_type']))."',
									collected_new_payment				=	'".addslashes(stripslashes($EcsREquestData_array['collected_new_payment']))."',
									instrument_details				=	'".addslashes(stripslashes($EcsREquestData_array['instrument_details']))."',
									new_contract_payment_mode				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_payment_mode']))."',
									detail_current_tme				=	'".addslashes(stripslashes($EcsREquestData_array['detail_current_tme']))."',
									detail_new_tme				=	'".addslashes(stripslashes($EcsREquestData_array['detail_new_tme']))."',
									detail_current_me				=	'".addslashes(stripslashes($EcsREquestData_array['detail_current_me']))."',
									detail_new_me				=	'".addslashes(stripslashes($EcsREquestData_array['detail_new_me']))."',
									mecode 						= 	'".$mecode."'
									ON DUPLICATE KEY UPDATE
									EmpCode				=	'".$EcsREquestData_array['EmpCode']."',
									EmpName				=	'".addslashes(stripslashes($EcsREquestData_array['EmpName']))."',
									MngrCode			=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode']))."',
									Acc_Reg_Flag			=	'".addslashes(stripslashes($EcsREquestData_array['Acc_Reg_Flag']))."',
									companyname				=	'".addslashes(stripslashes($EcsREquestData_array['companyname']))."',
									pincode				=	'".addslashes(stripslashes($EcsREquestData_array['pincode']))."',
									mobile				=	'".addslashes(stripslashes($EcsREquestData_array['mobile']))."',
									email				=	'".addslashes(stripslashes($EcsREquestData_array['email']))."',
									contact_person				=	'".addslashes(stripslashes($EcsREquestData_array['contact_person']))."',
									Mngr_Flag				=	'".addslashes(stripslashes($EcsREquestData_array['Mngr_Flag']))."',
									city				=	'".addslashes(stripslashes($EcsREquestData_array['city']))."',
									updated_on				=	'".addslashes(stripslashes($EcsREquestData_array['updated_on']))."',
									requested_on				=	'".addslashes(stripslashes($EcsREquestData_array['requested_on']))."',
									MngrCode1				=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode1']))."',
									MngrCode2				=	'".addslashes(stripslashes($EcsREquestData_array['MngrCode2']))."',
									upd_deg_mngr				=	'".addslashes(stripslashes($EcsREquestData_array['upd_deg_mngr']))."',
									upd_deg_mngrCode				=	'".addslashes(stripslashes($EcsREquestData_array['upd_deg_mngrCode']))."',
									detail_cname				=	'".addslashes(stripslashes($EcsREquestData_array['detail_cname']))."',
									detail_cid				=	'".addslashes(stripslashes($EcsREquestData_array['detail_cid']))."',
									current_contract_value				=	'".addslashes(stripslashes($EcsREquestData_array['current_contract_value']))."',
									new_contract_value				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_value']))."',
									current_contract_type				=	'".addslashes(stripslashes($EcsREquestData_array['current_contract_type']))."',
									new_contract_type				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_type']))."',
									collected_new_payment				=	'".addslashes(stripslashes($EcsREquestData_array['collected_new_payment']))."',
									instrument_details				=	'".addslashes(stripslashes($EcsREquestData_array['instrument_details']))."',
									new_contract_payment_mode				=	'".addslashes(stripslashes($EcsREquestData_array['new_contract_payment_mode']))."',
									detail_current_tme				=	'".addslashes(stripslashes($EcsREquestData_array['detail_current_tme']))."',
									detail_new_tme				=	'".addslashes(stripslashes($EcsREquestData_array['detail_new_tme']))."',
									detail_current_me				=	'".addslashes(stripslashes($EcsREquestData_array['detail_current_me']))."',
									detail_new_me				=	'".addslashes(stripslashes($EcsREquestData_array['detail_new_me']))."',
									mecode 						= 	'".$mecode."'";
			$ins_newEcs_details_res	=	$dbObjme->query($ins_newEcs_details);
			if($ins_newEcs_details_res){
				$error_arr['errorCode'] 		=	0;
				$error_arr['errorStatus_ecs_details_insert'] 	=	"INSERT tbl_ecs_dealclose_pending Done";
			}else{
				$error_arr['errorCode'] 	=	1;
				$error_arr['errorStatus_ecs_details_insert'] 	=	"INSERT tbl_ecs_dealclose_pending Fail";
			}						

		}else{
			$error_arr['errorCode'] 	=	1;
			$error_arr['errorStatus_EcsResult'] 	=	"EcsRequest Data Data Not Found";
		}
		return json_encode($error_arr);
	}
	/*
	 * This Function will check if a particular ME's time Slot has already been booked by any TME or not
	 * Including the time interval
	 * Created by Apoorv Agrawal
	 * Start Date: 17-10-2016
	*/
	public function checkSlotForMe(){
		header('Content-Type: application/json');
		$con_local		=	new DB($this->db['db_local']);
		$retcheckArr	=	array();
		$argsArr		=	array();
		if(!isset($_REQUEST['urlFlag']) && $_REQUEST['urlFlag'] == 1){
			$argsArr	=	$_REQUEST;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		if(!isset($argsArr['parentid']) || empty($argsArr['parentid'])){
			$retcheckArr['errorCode_checkSlotForMe']	=	1;
			$retcheckArr['errorStatus_checkSlotForMe']	=	"Parentid Issue";
		}else{
			if($argsArr['disposeVal'] == '25' || $argsArr['disposeVal'] == '99'){
				$action_timeArr = 	explode("/",$argsArr['actDate']);
				$actionTime		= 	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$argsArr['actTime'].":00";
				$empCode		=	$argsArr['meCode'];
				$one_Check_qur	=	"SELECT COUNT(1) as totCount FROM d_jds.tblContractAllocation WHERE actionTime BETWEEN  ('".$actionTime."' - INTERVAL 60 MINUTE)  AND ('".$actionTime."' + INTERVAL 60 MINUTE)  AND allocationtype IN (25,99) AND cancel_flag=0 AND empCode='".$empCode."'";
				$lack_handl_qry_Con		=	$con_local->query($one_Check_qur);
				$lack_handl_qry_data 	= 	$con_local->fetchData($lack_handl_qry_Con);			
				if($lack_handl_qry_data['totCount'] == 0){
					$retcheckArr['errorCode_checkSlotForMe']	=	0;
					$retcheckArr['errorStatus_checkSlotForMe']	=	"PF";//Proceed Further
				}else{
					$retcheckArr['errorCode_checkSlotForMe']	=	1;
					$retcheckArr['errorStatus_checkSlotForMe']	=	"DPF";//Don't Proceed Further
				}
			}else{
				$retcheckArr['errorCode_checkSlotForMe']	=	0;
				$retcheckArr['errorStatus_checkSlotForMe']	=	"NOTAPF"; // NOT Appointment Fix/Refix
			}
		}
		return json_encode($retcheckArr);die;
	}
	/*
	 * function created to make insertion happen in consolidated table for appointment  Allocation
	 * Requirment Given by Rohit Sir
	 * Created by Apoorv Agrawal
	 * DATEOFLIVE:21-11-2016
	*/
	public function insert_tblContratcAllocConsolidate(){
		$respArr	=		array();
		$dbIDC_justdial_products	=	new DB($this->db['db_idc']);
		$currentTime	=	date("Y-m-d H:i:s");// is server time time 
		$server_add		=	$_SERVER['SERVER_ADDR'];
		$exp_server_add = 	explode(".", $server_add);		
		/*New variable Declared Here Start 2017-06-16*/
		$get_allocId_tme	=	array();
		$get_teamName_tme	=	array();
		$get_teamName_tme_str	=	'';
		/*New variable Declared Here End 2017-06-16*/
		switch ($exp_server_add[2]) {
			case '56':
				$citycode = '56';
				$city = 'Ahmedabad';
				$source = 'Ahmedabad';
				break;
			case '26':
				$citycode = '26';
				$city = 'Bangalore';
				$source = 'Bangalore';
				break;
			case '32':
				$citycode = '32';
				$city = 'Chennai';
				$source = 'Chennai';
				break;
			case '8':
				$citycode = '8';
				$city = 'Delhi';
				$source = 'Delhi';
				break;
			case '50':
				$citycode = '50';
				$city = 'Hyderabad';
				$source = 'Hyderabad';
				break;
			case '16':
				$citycode = '16';
				$city = 'Kolkata';
				$source = 'Kolkata';
				break;
			case '0':
				$citycode = '0';
				$city = 'Mumbai';
				$source = 'Mumbai';
				break;
			case '40':
				$citycode = '40';
				$city = 'Pune';
				$source = 'Pune';
				break;
			case '17':
				$citycode = '17';
				$city = 'Remote';
				$source	=	'Remote';
				break;
			case '35':
				$citycode = '35';
				$city = 'Ahmedabad';
				$source = 'Ahmedabad';
				break;
			default:
				$citycode = '67';
				$city = 'Mumbai';
				$source	=	'Mumbai';
		}
		$source	=	strtoupper($source);
		if($this->dispose_type !='25' && $this->dispose_type !='99' && $this->dispose_type !='24' && $this->dispose_type !='22' && $this->dispose_type !='317'){
			$sqlCondition  	=	"";
			$tblAllocValue	=	'';
			$parentCode		=	'';	
			$empCode		=	$this->empCode;
			$actionTime		=	addslashes($currentTime);
			$tme_search_qr	=	'';
			$insertFlag		=	0;
		}else{
			/*New variable Declared Here Start 2017-06-16*/
			$get_allocId_tme	=	json_decode($this->get_TME_ME_data($this->empCode),true);
			if($get_allocId_tme['errorCode'] == 0){
				$get_teamName_tme	=	json_decode($this->get_team_type($get_allocId_tme['data']['allocID']),true);
				
				if($get_teamName_tme['errorCode'] == 0){
					$get_teamName_tme_str	=	$get_teamName_tme['data']['team_name'];
				}
			}
			/*New variable Declared Here End 2017-06-16*/
			$tme_search_qr	=	",parentcode='".$this->empCode."'";
			$parentCode		=	$this->empCode;
			$empCode		=	$this->MECode;
			$action_timeArr = 	explode("-",$this->actionDate);	
			
			$condition_dt	=	explode(":",$this->actionTime);
			
			$actionTime		= 	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$this->actionTime.":00";
			$tblAllocQuery 	=	',parentCode, tmename, mename, instruction,t20flag,area,pincode,alt_address_flag,data_city,city,zone,entry_date,source,team_type';
			
			$tblAllocValue 	=	',"'.$parentCode.'", "'.addslashes(stripslashes($this->empName)).'", "'.addslashes(stripslashes($this->MEName)).'", "'.addslashes(stripslashes($this->instrct)).'","","'.addslashes(stripslashes($this->area)).'","'.$this->pincode.'","'.$this->altAddres_flg.'","'.$this->data_city.'","'.$this->city.'","","'.addslashes($currentTime).'","'.$source.'","'.addslashes(stripslashes($get_teamName_tme_str)).'"';
			
			//~ $con_date		=	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$condition_dt[0];
		}
		$insert_flag_consolidated = 0;
		if($insert_flag_consolidated != 1){
			$disposeInsertQuery_logs		=	"INSERT INTO db_justdial_products.tblContractAllocation_consolidated (empCode,contractCode,allocationType,allocationTime,actionTime,compname ".$tblAllocQuery.") 
									VALUES 
									('".$empCode."', '".$this->parentid."','".$this->dispose_type."','".addslashes($currentTime)."','".addslashes($actionTime)."','".addslashes($this->genshadowArr['companyname'])."'".$tblAllocValue.")";
			$insertdisposeQueryCon	=	$dbIDC_justdial_products->query($disposeInsertQuery_logs);
			if($insertdisposeQueryCon){
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	"Data inserted in tblContractAllocation_consolidated";
			}else{
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	"Data inserted failed tblContractAllocation_consolidated";
			}
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Data inserted failed tblContractAllocation_consolidated";
		}
		return json_encode($respArr);
	}
	public function fn_show_elig_me(){
		$con_local 				=	new DB($this->db['db_local']);
		$retArr['actionTime']	=	$this->actionTime;
		$retArr['actionDate']	=	$this->actionDate;
		if($this->TMERANK == 10000000){
			$retArr['TMERANK']		=	0;
		}else{
			$retArr['TMERANK']		=	$this->TMERANK;
		}
		//~ $retArr['TMERANK']		=	$this->TMERANK;
		// new Code Added Here 03-02-2017
		$final_me_rank	=	'';
		$retArr['pincode']		=	$this->pincode;
		$busy_free_me			=	array();
		$final_me_elig			=	array();
		$listof_me_str	=	'';
		if($this->eligibilty == 10000000){
			$retArr[eligibilty]	=	'';
		}else{
			$retArr[eligibilty]	=	$this->eligibilty;
		}		
		$list_of_me_pincodewise		=	array();
		$list_of_me_pincodewise		=	$this->list_of_me_pincodewise; // list of ME picode and rank wise sorted
		$retArr[before_reset_me_array]	=	$list_of_me_pincodewise;
		//$retArr[list_of_me_pincodewise]	=	$this->list_of_me_pincodewise;
		$follow_up_arr		=	array();
		$follow_up_arr		=	json_decode($this->getFollowUp($this->actionDate,$this->parentid,$this->list_of_me_pincodewise),true);
		$cont_allocf = 0;
		if($follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2){
			$cont_allocf	=	3;
			$this->moveToIDCFlag	=	0;
		}
		if($this->ignore_followUp =='ignore_followUp'){
			$cont_allocf	=	2;
		}
		if($this->eligibilty == 10000000){
			$retArr[eligibilty]	=	'';
		}else{
			$retArr[eligibilty]	=	$this->eligibilty;
		}
		if($follow_up_arr['errorCode'] == 1 || $follow_up_arr['follow_up']['absent']==1){
			$followUp	=	0;
			$this->moveToIDCFlag	=	0;
		}else{
			$followUp	=	1;
			$this->moveToIDCFlag	=	1;
		}
		if(array_key_exists('add_to_prosp',$follow_up_arr) && $follow_up_arr['add_to_prosp'] == 0){
			$followUp	=	2;
			$this->moveToIDCFlag	=	0;
		}
		if(array_key_exists('ecs_mandate',$follow_up_arr) && $follow_up_arr['ecs_mandate'] == 0){
			$followUp	=	3;
			$this->moveToIDCFlag	=	0;
		}
		$retArr[follow_up_arr]	=	$follow_up_arr; // very Imp
		//~ if(($this->ignore_followUp =='ignore_followUp') || $follow_up_arr['errorCode'] == 1 || $follow_up_arr['follow_up']['absent']==1 || $follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2){
		
		// changes Done Here 13-02-2017 Monday
		if(($this->ignore_followUp =='ignore_followUp') || ($follow_up_arr['errorCode'] == 1 || $follow_up_arr['follow_up']['absent']==1) || $follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2 || ($follow_up_arr['ecs_mandate'] == 1 && ($follow_up_arr['follow_up']['absent'] == 1 || $follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2)) || ($follow_up_arr['add_to_prosp'] == 1 && ($follow_up_arr['follow_up']['absent'] == 1 || $follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2))){
			for($i=0;$i<count($this->list_of_me_pincodewise);$i++){
				$listof_me_str	.=	$this->list_of_me_pincodewise[$i]['mktEmpCode']."','";
			}
			$listof_me_str	=	trim($listof_me_str,",");
			/*Calculating half hour after*/
			$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
			$time30Min_plus = date('H:i:s', $timestamp_plus);
			if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
				$time30Min_plus	=	'23:59:59';
			}
			/*Calculating half hour before*/
			$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
			$time30Min_back = date('H:i', $timestamp_back);
			$listofEmpCode	=	array();
			$sel_me_code	= 		"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$listof_me_str."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag = 0 ORDER BY actionTime";
			$data_me_tim_con		=	$con_local->query($sel_me_code);
			$rows_me_tim_con		=	$con_local->numRows($data_me_tim_con);
			if($rows_me_tim_con > 0){
				while($data_me_time		=	$con_local->fetchData($data_me_tim_con)) {
					$busy_free_me[]		=	$data_me_time;
					$listofEmpCode[]	=	$data_me_time['empCode'];
				}
				//~ for($i=0;$i<count($this->list_of_me_pincodewise);$i++){
					//~ if(isset($this->list_of_me_pincodewise[$i])){
						//~ if(in_array($this->list_of_me_pincodewise[$i]['mktEmpCode'], $listofEmpCode)){							
							//~ unset($list_of_me_pincodewise[$i]);
						//~ }
						//~ $this->list_of_me_pincodewise	=	$list_of_me_pincodewise;
					//~ }
				//~ }
			}
			$dbObjIDC		=	new DB($this->db['db_idc']);
			//~ $sel_me_code_idc	= 		"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$listof_me_str."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
			//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
			//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
			//~ if($rows_me_tim_con_idc > 0){
				//~ while($data_me_time		=	$dbObjIDC->fetchData($data_me_tim_con_idc)) {
					//~ $busy_free_me[]		=	$data_me_time;
					//~ $listofEmpCode[]	=	$data_me_time['empCode'];
				//~ }
			//~ }
			$list_of_absent_me	=	array();
			/*Calling function to check if the ME is absent*/
			$list_of_absent_me				=	json_decode($this->fetchAbsentDetailsME($this->actionDate),true);
			$list_of_absent_me_code			=	array();
			$retArr['list_of_absent_me']	=	$list_of_absent_me;
			if($list_of_absent_me['errorCode'] == 0){
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				if(count($this->list_of_me_pincodewise) > 1){
					$y=0;
					foreach($this->list_of_me_pincodewise as $key=>$data){
						if(!array_key_exists($key,$follow_up_arr['follow_up']) && ($follow_up_arr['follow_up']['mktEmpCode'] != $data['mktEmpCode'])){
							$new_list_of_me_pincodewise[$key]	=	$this->list_of_me_pincodewise[$key];
							$y++;
						}
					}
					$this->list_of_me_pincodewise	=	$new_list_of_me_pincodewise; // changes Done Here 13-02-2017 Monday
					$list_of_me_pincodewise			=	$this->list_of_me_pincodewise;									
				}
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				$oneCounter	=	1;
				if(!isset($list_of_absent_me['data']['time_slot_tme'])){
					for($y=0;$y<count($list_of_absent_me['data']);$y++){
						$list_of_absent_me_code[]	=	$list_of_absent_me['data'][$y][empcode];
					}
					foreach($this->list_of_me_pincodewise as $key=>$data){
						if(in_array($data['mktEmpCode'], $list_of_absent_me_code)){
							unset($list_of_me_pincodewise[$key]);
						}
					}
					$this->list_of_me_pincodewise	=	$list_of_me_pincodewise;
				}else{
					/*New Code for handling All Absent Cases*/
					if(isset($list_of_absent_me['data'][0])){
						for($y=0;$y<count($list_of_absent_me['data']);$y++){
							if(isset($list_of_absent_me['data'][$y])){
								$list_of_absent_me_code[]	=	$list_of_absent_me['data'][$y]['empcode'];
							}
						}
						foreach($this->list_of_me_pincodewise as $key=>$data){
							if(in_array($data['mktEmpCode'], $list_of_absent_me_code)){
								unset($list_of_me_pincodewise[$key]);
							}
						}
						$this->list_of_me_pincodewise	=	$list_of_me_pincodewise;
					}
					$timeSlot_absent	=	array();
					foreach($list_of_absent_me['data']['time_slot_tme'] as $key=>$data){						
						foreach($this->list_of_me_pincodewise as $k1=>$d1){
							$listOf_absent_time_slot	=	array();
							if(in_array($key,$d1)){
								$timeSlot_absent		=	explode("-",$data);
								$time_slot_start		=	$timeSlot_absent[0];
								$time_slot_end			=	$timeSlot_absent[1];
								$time_slot_start_block	=	$timeSlot_absent[0];
								$time_slot_end_block	=	$timeSlot_absent[1];
								$hourdiff 				= 	round((strtotime($time_slot_end) - strtotime($time_slot_start))/3600);
								$hourdiff				=	$hourdiff*2;
								for($i=0;$i<$hourdiff;$i++){
									if(strtotime($time_slot_start_block) <= strtotime($time_slot_end)){
										$listOf_absent_time_slot[]	=	$time_slot_start_block;
										$time_slot_start_block		=	date("H:i", strtotime('+30 minutes', strtotime($time_slot_start_block)));
									}
								}
								$listOf_absent_time_slot[]	=	$time_slot_end;
								if(in_array($this->actionTime,$listOf_absent_time_slot)){
									unset($list_of_me_pincodewise[$k1]);
								}
							}
						}
					}
					$this->list_of_me_pincodewise	=	$list_of_me_pincodewise;
				}
				if(empty($busy_free_me)){
					for($i=0;$i<count($this->list_of_me_pincodewise);$i++){
						if($i	==	($this->eligibilty-1)){
							$final_me_elig	=	$this->list_of_me_pincodewise[$i];
						}
					}
					if($this->eligibilty == 1){
						$min_index	=	min(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	($this->list_of_me_pincodewise[$min_index]);
					}
					// changes Done here
					if($this->eligibilty == 10000000){
						$max_index	=	max(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	($this->list_of_me_pincodewise[$max_index]);
					}
				}else{
					foreach($this->list_of_me_pincodewise as $key=>$data){
						if(isset($this->list_of_me_pincodewise[$key])){
							if(in_array($data['mktEmpCode'], $listofEmpCode)){							
								unset($list_of_me_pincodewise[$key]);
							}
						}
					}
					if($this->eligibilty!=	1 && $this->eligibilty != 10000000){
						$max_index	=	max(array_keys($list_of_me_pincodewise));
						if(($max_index)	==	($this->eligibilty)){
							$final_me_elig	=	($list_of_me_pincodewise[$max_index-1]);
						}else{
							//$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty-2]);
							if(empty($final_me_elig)){
								for($i=1;$i<=($this->eligibilty);$i++){
									$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty-$i]);
									if(!empty($final_me_elig)){
										break;
									}
								}
								if(empty($final_me_elig)){
									for($i=0;$i<count($list_of_me_pincodewise);$i++){
										$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty+$i]);
										if(!empty($final_me_elig)){
											break;
										}
									}
								}
							}
						}
					}else{
						if(empty($list_of_me_pincodewise)){
							$final_me_elig	=	array();
						}elseif($this->eligibilty == 10000000){
							$max_index	=	max(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
							$final_me_elig	=	($this->list_of_me_pincodewise[$max_index]);
						}else{
							$min_index	=	min(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
							$final_me_elig	=	($this->list_of_me_pincodewise[$min_index]);
						}
						
					}			
				}
			}else{
				if(empty($busy_free_me)){
					// changes made here by Apoorv Agrawal - today (27-01-2017)
					if(count($this->list_of_me_pincodewise) > 1){
						$y=0;
						foreach($this->list_of_me_pincodewise as $key=>$data){
							if(!array_key_exists($key,$follow_up_arr['follow_up']) && ($follow_up_arr['follow_up']['mktEmpCode'] != $data['mktEmpCode'])){
								$new_list_of_me_pincodewise[$key]	=	$this->list_of_me_pincodewise[$key];
								$y++;
							}
						}
						$this->list_of_me_pincodewise	=	$new_list_of_me_pincodewise; // changes Done Here 13-02-2017 Monday
						$list_of_me_pincodewise			=	$this->list_of_me_pincodewise;									
					}
					// changes made here by Apoorv Agrawal - today (27-01-2017)
					foreach($this->list_of_me_pincodewise as $key=>$data){
						if($key	==	($this->eligibilty-1)){
							$final_me_elig	=	$this->list_of_me_pincodewise[$key];
						}
					}
					if($this->eligibilty == 1){
						$min_index	=	min(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	($this->list_of_me_pincodewise[$min_index]);
					}
					if($this->eligibilty == 10000000){
						$max_index	=	max(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	($this->list_of_me_pincodewise[$max_index]);
					}
				}else{
					if(count($this->list_of_me_pincodewise) > 1){
						$y=0;
						foreach($this->list_of_me_pincodewise as $key=>$data){
							if(!array_key_exists($key,$follow_up_arr['follow_up']) && ($follow_up_arr['follow_up']['mktEmpCode'] != $data['mktEmpCode'])){
								$new_list_of_me_pincodewise[$key]	=	$this->list_of_me_pincodewise[$key];
								$y++;
							}
						}
						$this->list_of_me_pincodewise	=	$new_list_of_me_pincodewise; // changes Done Here 13-02-2017 Monday
						$list_of_me_pincodewise			=	$this->list_of_me_pincodewise;
					}
					// changes made here by Apoorv Agrawal - today (27-01-2017)
					foreach($this->list_of_me_pincodewise as $key=>$data){
						if(in_array($data['mktEmpCode'], $listofEmpCode)){
							unset($list_of_me_pincodewise[$key]);
						}
					}
					if($this->eligibilty!=	1 && $this->eligibilty != 10000000){
						$max_index	=	max(array_keys($list_of_me_pincodewise));
						if(($max_index)	==	($this->eligibilty)){
							$final_me_elig	=	($list_of_me_pincodewise[$max_index-1]);
						}else{
							//$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty-2]);
							if(empty($final_me_elig)){
								for($i=1;$i<=($this->eligibilty);$i++){
									$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty-$i]);
									if(!empty($final_me_elig)){
										break;
									}
								}
								if(empty($final_me_elig)){
									for($i=0;$i<count($list_of_me_pincodewise);$i++){
										$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty+$i]);
										if(!empty($final_me_elig)){
											break;
										}
									}
								}
							}
						}
					}else{
						if(empty($list_of_me_pincodewise)){
							$final_me_elig	=	array();
						}elseif($this->eligibilty == 10000000){
							$max_index	=	max(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
							$final_me_elig	=	($this->list_of_me_pincodewise[$max_index]);
						}else{
							$min_index	=	min(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
							$final_me_elig	=	($this->list_of_me_pincodewise[$min_index]);
						}
					}			
				}
			}
			if(empty($final_me_elig)){
				for($i=1;$i<=($this->eligibilty);$i++){
					$final_me_elig	=	($list_of_me_pincodewise[$this->eligibilty-$i]);
					if(!empty($final_me_elig)){
						break;
					}
				}
				if(empty($final_me_elig)){
					if($this->eligibilty == 10000000){
						$max_index	=	max(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	$list_of_me_pincodewise[$max_index];
					}elseif($this->eligibilty == 1){
						$min_index	=	min(array_keys($list_of_me_pincodewise));//array_keys($list_of_me_pincodewise, min($list_of_me_pincodewise));
						$final_me_elig	=	$list_of_me_pincodewise[$min_index];
						if(empty($final_me_elig)){
							foreach($list_of_me_pincodewise as $key=>$data){
								$final_me_elig	=	$list_of_me_pincodewise[$key];
								if(!empty($final_me_elig)){
									break;
								}
							}
						}
					}else{
						foreach($list_of_me_pincodewise as $key=>$data){
							$final_me_elig	=	$list_of_me_pincodewise[$key];
							if(!empty($final_me_elig)){
								break;
							}
						}
					}
					
				}
			}
			if(empty($final_me_elig) || $final_me_elig == null){
				$retArr['eligiblerrorCode']		=	1;
				$retArr['eligiblerrorStatus']	=	"NoFreeME";
			}else{
				$retArr['eligiblerrorCode']		=	0;
				$retArr['eligiblerrorStatus']	=	"FreeME";
				$retArr['final_me_elig']		=	$final_me_elig;
				// new Code Added Here 03-02-2017
				$final_me_rank	=	$final_me_elig['cumulative_rank_city'];
			}
		}else{
			//$retArr[follow_up_arr]	=	$follow_up_arr;
			//echo $follow_up_arr['follow_up']['follow_up_me_found_in_list'];
			if($follow_up_arr['follow_up']['follow_up_me_found_in_list'] == 2){
				$retArr['follow_up']	=	$follow_up_arr['follow_up'];
			}else{
				if($follow_up_arr['follow_up']['absent'] == 0){
					if($follow_up_arr['follow_up']['busy'] == 2){
						$retArr['follow_up_me_busy']	=	false;
						$retArr['follow_up_me_busy_flag']	=	2;	// Means Follow_up_Me is free
						$follow_up_arr['follow_up']['follow_up_me']	=	1;
						$retArr['follow_up']	=	$follow_up_arr['follow_up'];
						// new Code Added Here 03-02-2017
						for($y=0;$y<count($list_of_me_pincodewise);$y++){
							if($list_of_me_pincodewise[$y]['mktEmpCode'] == $follow_up_arr['follow_up']['mktEmpCode']){
								$final_me_rank	=	$list_of_me_pincodewise[$y]['cumulative_rank_city'];
							}
						}
					}else{
						$retArr['follow_up_me_busy']	=	true;
						$retArr['follow_up_me_busy_flag']	=	1;	// Means Follow_up_Me is busy
						$retArr['follow_up']	=	$follow_up_arr['follow_up'];
						// new Code Added Here 03-02-2017
						for($y=0;$y<count($list_of_me_pincodewise);$y++){
							if($list_of_me_pincodewise[$y]['mktEmpCode'] == $follow_up_arr['follow_up']['mktEmpCode']){
								$final_me_rank	=	$list_of_me_pincodewise[$y]['cumulative_rank_city'];
							}
						}
					}
				}
			}
		}		
		// changes made here by Apoorv Agrawal - today (27-01-2017)
		$retArr['logInsert']	=	$this->logInsert; // changes made here by Apoorv Agrawal - today (27-01-2017)
		$retArr['moveToIDCFlag']	=	$this->moveToIDCFlag; // changes made here by Apoorv Agrawal - today (27-02-2017)
		if($this->logInsert == 'logInsert'){
			$retArr['listofBusyEmpCode']	=	$listofEmpCode;
			$retArr['after_reset_me_array']	=	$list_of_me_pincodewise;
			$retArr['busy_free_me']			=	$busy_free_me;
			$retArr['parentid']				=	$this->parentid;
			$retArr['TOTALTME']				=	$this->TOTALTME;
			$currentTime					=	date("Y-m-d H:i:s");// is server time time 
			// new Code Added Here 03-02-2017 tme_rank/me_rank
			$ins_tbl_apptLogs		=	"INSERT INTO tbl_apptLogs SET									
										`parentid`				=	'".$this->parentid."',									
										`json_resp`				=	'".addslashes(stripslashes(json_encode($retArr)))."',
										`appointmentDate`		=	'".$this->actionDate."',
										`actionTime`			=	'".$this->actionTime."',
										`followUp`				=	'".$followUp."',
										`cont_allocf`			=	'".$cont_allocf."',
										`tme_rank`				=	'".$retArr['TMERANK']."',
										`me_rank`				=	'".$final_me_rank."',
										`insertedOn`  			=	'".$currentTime."'";
			$con_tbl_apptLogs	=	$con_local->query($ins_tbl_apptLogs);
		}
		//tbl_apptLogs		
		return json_encode($retArr);
	}
	/*
	 * Update appt_logs
	*/
	public function update_appLogs($parentid){
		//~ $this->parentid	=	$parentid;
		$con_local	=	new DB($this->db['db_local']);
		$retArr		=	array();
		$sel_tbl_apptLogs		=	"SELECT * from tbl_apptLogs where`parentid` = '".$parentid."' ORDER BY insertedOn DESC LIMIT 1;";
		$con_tbl_apptLogs_sel	=	$con_local->query($sel_tbl_apptLogs);
		$tbl_apptLogs_sel_num	=	$con_local->numRows($con_tbl_apptLogs_sel);
		if($tbl_apptLogs_sel_num > 0){			
			$tbl_apptLogs_data	=	$con_local->fetchData($con_tbl_apptLogs_sel);
			$updt_tbl_apptLogs_rest 	=	"UPDATE tbl_apptLogs SET appt_alloc = '0' WHERE parentid = '".$parentid."'";
			$updt_tbl_apptLogs_con 	=	$con_local->query($updt_tbl_apptLogs_rest);
			$updt_tbl_apptLogs 		=	"UPDATE d_jds.tbl_apptLogs a JOIN d_jds.tblContractAllocation b ON a.parentid= b.contractCode SET a.appt_alloc=1 WHERE parentid = '".$parentid."' AND b.allocationType IN(25,99)  AND a.actionTime = TIME(b.actionTime)"; 
			$updt_tbl_apptLogs_con 	=	$con_local->query($updt_tbl_apptLogs);
			if($updt_tbl_apptLogs_con){
				$sel_tbl_apptLogs_new		=	"SELECT parentid,insertedOn,actionTime from tbl_apptLogs where`parentid` = '".$parentid."' AND appt_alloc = 1 ORDER BY insertedOn DESC;";
				$con_tbl_apptLogs_sel_new	=	$con_local->query($sel_tbl_apptLogs_new);
				$tbl_apptLogs_sel_num_new	=	$con_local->numRows($con_tbl_apptLogs_sel_new);
				if($tbl_apptLogs_sel_num_new > 1){
					while($data	=	$con_local->fetchData($con_tbl_apptLogs_sel_new)){
						$tbl_apptLogs_data_new[]	= $data;
					}
					$updt_tbl_apptLogs_new 	=	"UPDATE tbl_apptLogs SET appt_alloc = '0' WHERE parentid = '".$parentid."' AND insertedOn < '".$tbl_apptLogs_data_new[0]['insertedOn']."'";
					$updt_tbl_apptLogs_con_new 	=	$con_local->query($updt_tbl_apptLogs_new);
				}
				
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	"tbl_apptLogs updated done";
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	"tbl_apptLogs not updated";
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"No Data Found tbl_apptLogs";
		}
		return json_encode($retArr);
	}
	
	/*Function To Check if the ME is absent or not*/
	public function fetchAbsentDetailsME($apptDate) {
		header('Content-Type: application/json');
		$con_idc		=	new DB($this->db['db_local']);
		$resultArr		=	array();
		$resultDate		=	array();
		$finalArr		=	array();
		$whereCond		=	'';
		$whereCond		.=	"WHERE absent_on='".$apptDate."'";
		$fetchAppoint 	=	"SELECT empcode,time_slot_tme FROM tbl_meabsentdetails ".$whereCond;
		$conAppoint		=	$con_idc->query($fetchAppoint);
		$num			=	$con_idc->numRows($conAppoint);
		if($num	>	0){
			while($row	=	$con_idc->fetchData($conAppoint)){
				if($row['time_slot_tme'] == ''){
					$resultArr['data'][]	=	$row;
				}else{
					$resultArr['data']['time_slot_tme'][$row['empcode']]	=	$row['time_slot_tme'];
				}
			}
			$resultArr['errorCode']		=	0;
			$resultArr['errorStatus']	=	'Data Found';
		}else{
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Data Not Found';
		}		
		return json_encode($resultArr);
	}
	/*Function To get the Follow-Up ME from IDC server*/
	public function getFollowUp($actionDate,$parentid,$list_of_me_pinWise){
		header('Content-Type: application/json');
		$resultArr	=	array();
		$retArr		=	array();
		$con_idc_ar	=	new DB($this->db['db_idc']); // 36 is follow-Up Code in IDC(ME-GENIO)
		$con_local	=	new DB($this->db['db_local']);
		//~ $parentid	=	'PXX22.XX22.110802163730.H6V2';//  just to implement Functionality
		//~ $actionDate	=	'2016-12-01';
		$prev_month_ts 	= 	strtotime(''.$actionDate.' -1 month');
		$prev_month 	= 	date('Y-m-d', $prev_month_ts);
		$follow_up_me_found_in_list	=	array();
		$follow_up_me_list_flg		=	0;
		$paidData_me_arr	=	array();
		$paidData_me_arr	=	json_decode($this->get_paid_data($parentid),true);
		//~ print_r($paidData_me_arr);
		if($paidData_me_arr['errorCode'] == 1){
			$retArr['follow_up']['mktEmpCode']	=	'';
			$resultArr['ecs_mandate']		=	1;// ecs_mandate - 1 means ecs_mandate ME not found
			$qr_add_to_prosp	=	"SELECT DISTINCT contractid as contractCode,parentid,compname,mecode as mktEmpCode from tbl_prospectlist where contractid='".$parentid."' AND prospectalloc_time >='".$prev_month." 00:00:00' AND prospectalloc_time <='".$actionDate." 23:59:59' ORDER BY prospectalloc_time DESC LIMIT 1";
			$add_to_prosp_con	=	$con_idc_ar->query($qr_add_to_prosp);
			$qr_add_to_prosp_num	=	$con_idc_ar->numRows($add_to_prosp_con);
			if($qr_add_to_prosp_num > 0){
				$retArr['follow_up']	=	$con_idc_ar->fetchData($add_to_prosp_con);
				//~ $pidexists	=	$rowexists['contractid'];  
				//~ $pid_tagged_ucode = $rowexists['mktEmpCode'];
				$add_to_pros_Me_det	=	json_decode($this->get_ME_data($retArr['follow_up']['mktEmpCode']),true);
				$retArr['follow_up']['empName']	=	$add_to_pros_Me_det['data']['empName'];
				//~ echo "<pre>";print_r($add_to_pros_Me_det);
				if(count($list_of_me_pinWise) > 0){
					for($i=0;$i<count($list_of_me_pinWise);$i++){
						if(in_array($list_of_me_pinWise[$i]['mktEmpCode'], $retArr['follow_up'])){
							$follow_up_me_found_in_list[]	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
						}else{
							$follow_up_me_found_in_list[]	=	2;// 2 means follow up ME is not present in the pincode Wise ME List
						}
					}
				}
				if(in_array(1, $follow_up_me_found_in_list)){
					$retArr['follow_up']['follow_up_me_found_in_list']	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
					$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_found_in_list";
					$whereCond		.=	"WHERE absent_on='".$actionDate."' AND empcode = '".$retArr['follow_up']['mktEmpCode']."'";
					$get_absentME_qur 	=	"SELECT empcode FROM tbl_meabsentdetails ".$whereCond;
					$get_absentME_qur_con	=	$con_local->query($get_absentME_qur);
					$get_absentME_qur_num	=	$con_local->numRows($get_absentME_qur_con);
					if($get_absentME_qur_num > 0){
						$retArr['follow_up']['absent']	=	1; // ME is absent means 1
						$retArr['follow_up']['absent_present_status']	=	'Absent'; // ME is absent means 1
					}else{
						$retArr['follow_up']['absent']	=	0; // ME is present is 0					
						$retArr['follow_up']['absent_present_status']	=	'Present'; // ME is present is 0					
						// check for busy/free Status					
						$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
						$time30Min_plus = date('H:i:s', $timestamp_plus);
						// changes made here by Apoorv Agrawal - today (27-01-2017)
						if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
							$time30Min_plus	=	'23:59:59';
						}
						// changes made here by Apoorv Agrawal - today (27-01-2017)
						/*Calculating half hour before*/
						$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
						$time30Min_back = date('H:i', $timestamp_back);
						$listofEmpCode	=	array();
						// changes made here by Apoorv Agrawal - today (27-01-2017)
						$sel_me_code	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime";
						// changes made here by Apoorv Agrawal - today (27-01-2017)
						$data_me_tim_con		=	$con_local->query($sel_me_code);
						$get_absentME_qur_num	=	$con_local->numRows($data_me_tim_con);
						if($get_absentME_qur_num > 0){
							$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
							$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
						}else{
							$dbObjIDC		=	new DB($this->db['db_idc']);
							$retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
							$retArr['follow_up']['busy_free_status']	=	'Free';
							//~ $sel_me_code_idc	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
							//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
							//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
							//~ if($rows_me_tim_con_idc > 0){
								//~ $retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
								//~ $retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
							//~ }else{
								//~ $retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
								//~ $retArr['follow_up']['busy_free_status']	=	'Free'; // 2 - FREE means that follow-up ME is FREE
							//~ }
						}
					}
					$resultArr	=	$retArr;
				}else{
					$retArr['follow_up']['follow_up_me_found_in_list']	=	2; 	// 2 means follow up ME is not present in the pincode Wise ME List
					$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_not_found_in_list";
					$resultArr	=	$retArr;
				}
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Found';
				$resultArr['add_to_prosp']	=	0;// add_to_prosp - 0 means add_to_prospect ME found 
			}else{
				$follow_up_qur	=	"SELECT empCode as mktEmpCode,contractCode,actionTime,mename as empName, tmename FROM tblContractAllocation WHERE allocationType IN (25,99) AND actionTime >='".$prev_month." 00:00:00' AND actionTime <='".$actionDate." 23:59:59' AND contractCode ='".$parentid."' AND cancel_flag=0 ORDER BY actionTime DESC Limit 1";
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				//~ $resultArr['follow_up_qur']	=	$follow_up_qur;
				$follow_up_qur_con	=	$con_idc_ar->query($follow_up_qur);
				$follow_up_qur_num	=	$con_idc_ar->numRows($follow_up_qur_con);
				if($follow_up_qur_num	>	0){
					$retArr['follow_up']	=	$con_idc_ar->fetchData($follow_up_qur_con);
					if(count($list_of_me_pinWise) > 0){
						for($i=0;$i<count($list_of_me_pinWise);$i++){
							if(in_array($list_of_me_pinWise[$i]['mktEmpCode'], $retArr['follow_up'])){
								$follow_up_me_found_in_list[]	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
							}else{
								$follow_up_me_found_in_list[]	=	2;// 2 means follow up ME is not present in the pincode Wise ME List
							}
						}
					}
					if(in_array(1, $follow_up_me_found_in_list)){
						$retArr['follow_up']['follow_up_me_found_in_list']	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
						$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_found_in_list";
						$whereCond		.=	"WHERE absent_on='".$actionDate."' AND empcode = '".$retArr['follow_up']['mktEmpCode']."'";
						$get_absentME_qur 	=	"SELECT empcode FROM tbl_meabsentdetails ".$whereCond;
						$get_absentME_qur_con	=	$con_local->query($get_absentME_qur);
						$get_absentME_qur_num	=	$con_local->numRows($get_absentME_qur_con);
						if($get_absentME_qur_num > 0){
							$retArr['follow_up']['absent']	=	1; // ME is absent means 1
							$retArr['follow_up']['absent_present_status']	=	'Absent'; // ME is absent means 1
						}else{
							$retArr['follow_up']['absent']	=	0; // ME is present is 0					
							$retArr['follow_up']['absent_present_status']	=	'Present'; // ME is present is 0					
							// check for busy/free Status					
							$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
							$time30Min_plus = date('H:i:s', $timestamp_plus);
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
								$time30Min_plus	=	'23:59:59';
							}
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							/*Calculating half hour before*/
							$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
							$time30Min_back = date('H:i', $timestamp_back);
							$listofEmpCode	=	array();
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							$sel_me_code	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime";
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							$data_me_tim_con		=	$con_local->query($sel_me_code);
							$get_absentME_qur_num	=	$con_local->numRows($data_me_tim_con);
							if($get_absentME_qur_num > 0){
								$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
								$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
							}else{
								$retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
								$retArr['follow_up']['busy_free_status']	=	'Free';
								$dbObjIDC		=	new DB($this->db['db_idc']);
								//~ $sel_me_code_idc	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
								//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
								//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
								//~ if($rows_me_tim_con_idc > 0){
									//~ $retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
									//~ $retArr['follow_up']['busy_free_status'] = 'Busy'; // 1 - BUSY means that follow-up ME is BUSY
								//~ }else{
									//~ $retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
									//~ $retArr['follow_up']['busy_free_status']	=	'Free'; // 2 - FREE means that follow-up ME is FREE
								//~ }
							}
						}
						$resultArr	=	$retArr;
					}else{
						$retArr['follow_up']['follow_up_me_found_in_list']	=	2; 	// 2 means follow up ME is not present in the pincode Wise ME List
						$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_not_found_in_list";
						$resultArr	=	$retArr;
					}
					//~ $resultArr['list_of_me_pinWise']	=	$list_of_me_pinWise;
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Data Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'Data Not Found';
				}
				$resultArr['add_to_prosp']	=	1;// add_to_prosp - 1 means add_to_prospect ME not found 
			}
		}else{
			if($paidData_me_arr['mktEmpCode'] != ''){
				$retArr['follow_up']['mktEmpCode']	=	$paidData_me_arr['mktEmpCode'];
				$retArr['follow_up']['empName']		=	$paidData_me_arr['empName'];
				//~ print_r($retArr['follow_up']);
				//~ print_r($list_of_me_pinWise);
				if(count($list_of_me_pinWise) > 0){
					for($i=0;$i<count($list_of_me_pinWise);$i++){
						if(in_array($list_of_me_pinWise[$i]['mktEmpCode'], $retArr['follow_up'])){
							$retArr['follow_up']['mktEmpCode'] = $list_of_me_pinWise[$i]['mktEmpCode'];
							$follow_up_me_found_in_list[]	=	1; // 1 means ecs_mandate ME is present in the pincode Wise ME List
						}else{
							$follow_up_me_found_in_list[]	=	2;// 2 means ecs_mandate ME is not present in the pincode Wise ME List
						}
					}
				}
				if(in_array(1, $follow_up_me_found_in_list)){
					$retArr['follow_up']['follow_up_me_found_in_list']	=	1; // 1 means ecs_mandate ME is present in the pincode Wise ME List
					$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_found_in_list";
					$whereCond		.=	"WHERE absent_on='".$actionDate."' AND empcode = '".$retArr['follow_up']['mktEmpCode']."'";
					$get_absentME_qur 	=	"SELECT empcode FROM tbl_meabsentdetails ".$whereCond;
					$get_absentME_qur_con	=	$con_local->query($get_absentME_qur);
					$get_absentME_qur_num	=	$con_local->numRows($get_absentME_qur_con);
					if($get_absentME_qur_num > 0){
						$retArr['follow_up']['absent']	=	1; // ME is absent means 1
						$retArr['follow_up']['absent_present_status']	=	'Absent'; // ecs_mandate ME is absent means 1
					}else{
						$retArr['follow_up']['absent']	=	0; // ME is present is 0					
						$retArr['follow_up']['absent_present_status']	=	'Present'; // ecs_mandate ME is present is 0					
						// check for busy/free Status					
						$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
						$time30Min_plus = date('H:i:s', $timestamp_plus);
						// changes made here by Apoorv Agrawal - today (09-02-2017)
						if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
							$time30Min_plus	=	'23:59:59';
						}
						// changes made here by Apoorv Agrawal - today (09-02-2017)
						/*Calculating half hour before*/
						$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
						$time30Min_back = date('H:i', $timestamp_back);
						$listofEmpCode	=	array();
						// changes made here by Apoorv Agrawal - today (09-02-2017)
						$sel_me_code	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime";
						// changes made here by Apoorv Agrawal - today (09-02-2017)
						$data_me_tim_con		=	$con_local->query($sel_me_code);
						$get_absentME_qur_num	=	$con_local->numRows($data_me_tim_con);
						if($get_absentME_qur_num > 0){
							$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that ecs_mandate ME is BUSY
							$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that ecs_mandate ME is BUSY
						}else{
							$dbObjIDC		=	new DB($this->db['db_idc']);
							$retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
							$retArr['follow_up']['busy_free_status']	=	'Free';
							//~ $sel_me_code_idc	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
							//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
							//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
							//~ if($rows_me_tim_con_idc > 0){
								//~ $retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
								//~ $retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
							//~ }else{
								//~ $retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
								//~ $retArr['follow_up']['busy_free_status']	=	'Free'; // 2 - FREE means that follow-up ME is FREE
							//~ }
						}
						$resultArr	=	$retArr;
					}
				}else{
					$retArr['follow_up']['follow_up_me_found_in_list']	=	2; 	// 2 means ecs_mandate ME is not present in the pincode Wise ME List
					$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_not_found_in_list";
					$resultArr	=	$retArr;
				}
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'Data Found';
				$resultArr['ecs_mandate']		=	0;// ecs_mandate - 0 means ecs_mandate ME found 
			}else{
				$retArr['follow_up']['mktEmpCode']	=	'';
				$resultArr['ecs_mandate']		=	1;// ecs_mandate - 1 means ecs_mandate ME not found 
				$qr_add_to_prosp	=	"SELECT DISTINCT contractid as contractCode,parentid,compname,mecode as mktEmpCode from tbl_prospectlist where contractid='".$parentid."' AND prospectalloc_time >='".$prev_month." 00:00:00' AND prospectalloc_time <='".$actionDate." 23:59:59' ORDER BY prospectalloc_time DESC LIMIT 1";
				$add_to_prosp_con	=	$con_idc_ar->query($qr_add_to_prosp);
				$qr_add_to_prosp_num	=	$con_idc_ar->numRows($add_to_prosp_con);
				if($qr_add_to_prosp_num > 0){
					$retArr['follow_up']	=	$con_idc_ar->fetchData($add_to_prosp_con);
					//~ $pidexists	=	$rowexists['contractid'];  
					//~ $pid_tagged_ucode = $rowexists['mktEmpCode'];
					$add_to_pros_Me_det	=	json_decode($this->get_ME_data($retArr['follow_up']['mktEmpCode']),true);
					$retArr['follow_up']['empName']	=	$add_to_pros_Me_det['data']['empName'];
					//~ echo "<pre>";print_r($add_to_pros_Me_det);
					if(count($list_of_me_pinWise) > 0){
						for($i=0;$i<count($list_of_me_pinWise);$i++){
							if(in_array($list_of_me_pinWise[$i]['mktEmpCode'], $retArr['follow_up'])){
								$follow_up_me_found_in_list[]	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
							}else{
								$follow_up_me_found_in_list[]	=	2;// 2 means follow up ME is not present in the pincode Wise ME List
							}
						}
					}
					if(in_array(1, $follow_up_me_found_in_list)){
						$retArr['follow_up']['follow_up_me_found_in_list']	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
						$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_found_in_list";
						$whereCond		.=	"WHERE absent_on='".$actionDate."' AND empcode = '".$retArr['follow_up']['mktEmpCode']."'";
						$get_absentME_qur 	=	"SELECT empcode FROM tbl_meabsentdetails ".$whereCond;
						$get_absentME_qur_con	=	$con_local->query($get_absentME_qur);
						$get_absentME_qur_num	=	$con_local->numRows($get_absentME_qur_con);
						if($get_absentME_qur_num > 0){
							$retArr['follow_up']['absent']	=	1; // ME is absent means 1
							$retArr['follow_up']['absent_present_status']	=	'Absent'; // ME is absent means 1
						}else{
							$retArr['follow_up']['absent']	=	0; // ME is present is 0					
							$retArr['follow_up']['absent_present_status']	=	'Present'; // ME is present is 0					
							// check for busy/free Status					
							$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
							$time30Min_plus = date('H:i:s', $timestamp_plus);
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
								$time30Min_plus	=	'23:59:59';
							}
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							/*Calculating half hour before*/
							$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
							$time30Min_back = date('H:i', $timestamp_back);
							$listofEmpCode	=	array();
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							$sel_me_code	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime";
							// changes made here by Apoorv Agrawal - today (27-01-2017)
							$data_me_tim_con		=	$con_local->query($sel_me_code);
							$get_absentME_qur_num	=	$con_local->numRows($data_me_tim_con);
							if($get_absentME_qur_num > 0){
								$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
								$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
							}else{
								$dbObjIDC		=	new DB($this->db['db_idc']);
								$retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
								$retArr['follow_up']['busy_free_status']	=	'Free';
								//~ $sel_me_code_idc	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
								//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
								//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
								//~ if($rows_me_tim_con_idc > 0){
									//~ $retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
									//~ $retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
								//~ }else{
									//~ $retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
									//~ $retArr['follow_up']['busy_free_status']	=	'Free'; // 2 - FREE means that follow-up ME is FREE
								//~ }
							}
						}
						$resultArr	=	$retArr;
					}else{
						$retArr['follow_up']['follow_up_me_found_in_list']	=	2; 	// 2 means follow up ME is not present in the pincode Wise ME List
						$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_not_found_in_list";
						$resultArr	=	$retArr;
					}
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Data Found';
					$resultArr['add_to_prosp']		=	0;// add_to_prosp - 0 means add_to_prospect ME found 
				}else{
					$follow_up_qur	=	"SELECT empCode as mktEmpCode,contractCode,actionTime,mename as empName, tmename FROM tblContractAllocation WHERE allocationType IN (25,99) AND actionTime >='".$prev_month." 00:00:00' AND actionTime <='".$actionDate." 23:59:59' AND contractCode ='".$parentid."' AND cancel_flag=0 ORDER BY actionTime DESC Limit 1";
					// changes made here by Apoorv Agrawal - today (27-01-2017)
					//~ $resultArr['follow_up_qur']	=	$follow_up_qur;
					$follow_up_qur_con	=	$con_idc_ar->query($follow_up_qur);
					$follow_up_qur_num	=	$con_idc_ar->numRows($follow_up_qur_con);
					if($follow_up_qur_num	>	0){
						$retArr['follow_up']	=	$con_idc_ar->fetchData($follow_up_qur_con);
						if(count($list_of_me_pinWise) > 0){
							for($i=0;$i<count($list_of_me_pinWise);$i++){
								if(in_array($list_of_me_pinWise[$i]['mktEmpCode'], $retArr['follow_up'])){
									$follow_up_me_found_in_list[]	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
								}else{
									$follow_up_me_found_in_list[]	=	2;// 2 means follow up ME is not present in the pincode Wise ME List
								}
							}
						}
						if(in_array(1, $follow_up_me_found_in_list)){
							$retArr['follow_up']['follow_up_me_found_in_list']	=	1; // 1 means Follow up ME is present in the pincode Wise ME List
							$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_found_in_list";
							$whereCond		.=	"WHERE absent_on='".$actionDate."' AND empcode = '".$retArr['follow_up']['mktEmpCode']."'";
							$get_absentME_qur 	=	"SELECT empcode FROM tbl_meabsentdetails ".$whereCond;
							$get_absentME_qur_con	=	$con_local->query($get_absentME_qur);
							$get_absentME_qur_num	=	$con_local->numRows($get_absentME_qur_con);
							if($get_absentME_qur_num > 0){
								$retArr['follow_up']['absent']	=	1; // ME is absent means 1
								$retArr['follow_up']['absent_present_status']	=	'Absent'; // ME is absent means 1
							}else{
								$retArr['follow_up']['absent']	=	0; // ME is present is 0					
								$retArr['follow_up']['absent_present_status']	=	'Present'; // ME is present is 0					
								// check for busy/free Status					
								$timestamp_plus = strtotime(''.$this->actionTime.'') + 60*60;
								$time30Min_plus = date('H:i:s', $timestamp_plus);
								// changes made here by Apoorv Agrawal - today (27-01-2017)
								if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
									$time30Min_plus	=	'23:59:59';
								}
								// changes made here by Apoorv Agrawal - today (27-01-2017)
								/*Calculating half hour before*/
								$timestamp_back = strtotime(''.$this->actionTime.'') - 60*60;
								$time30Min_back = date('H:i', $timestamp_back);
								$listofEmpCode	=	array();
								// changes made here by Apoorv Agrawal - today (27-01-2017)
								$sel_me_code	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime";
								// changes made here by Apoorv Agrawal - today (27-01-2017)
								$data_me_tim_con		=	$con_local->query($sel_me_code);
								$get_absentME_qur_num	=	$con_local->numRows($data_me_tim_con);
								if($get_absentME_qur_num > 0){
									$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
									$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
								}else{
									$dbObjIDC		=	new DB($this->db['db_idc']);
									$retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
									$retArr['follow_up']['busy_free_status']	=	'Free';
									//~ $sel_me_code_idc	= 	"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$retArr['follow_up']['mktEmpCode']."') AND actionTime >= '".$this->actionDate." ".$time30Min_back.":00' AND actionTime <= '".$this->actionDate." ".$time30Min_plus."' AND allocationType IN ('35','36') AND cancel_flag = 0 ORDER BY actionTime";
									//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
									//~ $rows_me_tim_con_idc		=	$dbObjIDC->numRows($data_me_tim_con_idc);
									//~ if($rows_me_tim_con_idc > 0){
										//~ $retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
										//~ $retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
									//~ }else{
										//~ $retArr['follow_up']['busy']	=	2; 		// 2 - FREE means that follow-up ME is FREE
										//~ $retArr['follow_up']['busy_free_status']	=	'Free'; // 2 - FREE means that follow-up ME is FREE
									//~ }
								}
							}
							$resultArr	=	$retArr;
						}else{
							$retArr['follow_up']['follow_up_me_found_in_list']	=	2; 	// 2 means follow up ME is not present in the pincode Wise ME List
							$retArr['follow_up']['follow_up_me_found_in_list_status']	=	"Follow_up_me_not_found_in_list";
							$resultArr	=	$retArr;
						}
						//~ $resultArr['list_of_me_pinWise']	=	$list_of_me_pinWise;
						$resultArr['errorCode']		=	0;
						$resultArr['errorStatus']	=	'Data Found';
					}else{
						$resultArr['errorCode']		=	1;
						$resultArr['errorStatus']	=	'Data Not Found';
					}
					$resultArr['add_to_prosp']	=	1;// add_to_prosp - 1 means add_to_prospect ME not found 
				}
			}
		}
		//~ print_r($resultArr);
		return json_encode($resultArr);
	}
	public function get_ME_data($empCode){
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$query_mktgEmpMaster 	=	"SELECT autoId,mktEmpCode,oldTmeCode,empType,empName,tmeClass,empParent,phoneNo,extn,mobile,emailId,state,city,nation,nat_code,
									state_code,city_code,datetime, Approval_flag,allocID,secondary_allocid,level,irodata,data_city,dnc_type,allocation_flag,dummy_flag 
									FROM mktgEmpMaster WHERE mktEmpCode='".$empCode."';";
		$conn_mktgEmpMaster 	=	$con_local->query($query_mktgEmpMaster);
		$num_mktgEmpMaster 		=	$con_local->numRows($conn_mktgEmpMaster);
		if($num_mktgEmpMaster > 0){
			$retArr['data'] 		=	$con_local->fetchData($conn_mktgEmpMaster);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	/*
	 * Checking if the Appt is getting allocated to same previous ME
	 * Date - 01/03/2017
	*/
	public function check_appt_me(){
		$respArr	=	array();
		$this->MECode; //  the me whome the appt is getting allocated to
		$this->parentid; // the contract for which the Appt. is being given
		$con_local	=	new DB($this->db['db_local']);
		$sel_me	= 	"SELECT empCode FROM tblContractAllocation WHERE contractCode ='".$this->parentid."' AND parentCode = '".$this->empCode."' AND allocationType IN ('25','99') AND cancel_flag = 0 ORDER BY actionTime DESC";
		// changes made here by Apoorv Agrawal - today (27-01-2017)
		$sel_me_con		=	$con_local->query($sel_me);		
		$sel_me_num		=	$con_local->numRows($sel_me_con);
		if($sel_me_num > 0){
			$sel_me_data	=	$con_local->fetchData($sel_me_con);
			$respArr['sel_me_data']	=	$sel_me_data;
			if($sel_me_data[empCode] ==	$this->MECode){
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	'S';
			}else{
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	'NS';
			}
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'NS';
		}
		$mongo_inputs = array();
		$mongo_inputs['module'] = 'ME';
		$mongo_inputs['action'] = 'getdata';
		$mongo_inputs['post_data'] = 1;
		
		$mongo_inputs['parentid'] = $this->parentid;
		$mongo_inputs['data_city'] = $this->data_city;
		$mongo_inputs['table'] = "tbl_companymaster_generalinfo_shadow";
		$mongo_inputs['fields'] = "parentid";
		
		/*CURL CALL TO GET DATA FROM MONGO*/
		$url =	JDBOX_API.'/services/mongoWrapper.php';
		$curlParams_temp = array();
		$curlParams_temp['url'] = $url;
		$curlParams_temp['method'] = 'post';
		$curlParams_temp['formate'] = 'basic';
		$curlParams_temp['postData'] = $mongo_inputs;
		$data_res = json_decode(Utility::curlCall($curlParams_temp),true);
		
		if(count($data_res) == 0 || empty($data_res['parentid'])){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	'NS';
		}
		return json_encode($respArr);
	}
	public function insert_apptLogs($fn_call_cnt){
		$respArr	=		array();
		$dbIDC_justdial_products	=	new DB($this->db['db_idc']);
		$currentTime	=	date("Y-m-d H:i:s");// is server time time
		if($this->dispose_type !='25' && $this->dispose_type !='99' && $this->dispose_type !='24' && $this->dispose_type !='22' && $this->dispose_type !='317'){
			$sqlCondition  	=	"";
			$tblAllocValue	=	'';
			$parentCode		=	'';	
			$empCode		=	$this->empCode;
			$actionTime		=	addslashes($currentTime);
			$tme_search_qr	=	'';
			$insertFlag		=	0;
		}else{
			$tme_search_qr	=	",parentcode='".$this->empCode."'";
			$parentCode		=	$this->empCode;
			$empCode		=	$this->MECode;
			$action_timeArr = 	explode("-",$this->actionDate);	
			
			$condition_dt	=	explode(":",$this->actionTime);
			
			$actionTime		= 	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$this->actionTime.":00";
			$tblAllocQuery 	=	',data_city';
			
			$tblAllocValue 	=	',"'.$this->data_city.'"';
			
			//~ $con_date		=	$action_timeArr[0]."-".$action_timeArr[1]."-".$action_timeArr[2]." ".$condition_dt[0];
		}
		$insert_flag = 0;
		$selQuery		=	"SELECT DATE_FORMAT(`allocationTime`, '%Y-%m-%d %H:%i') AS `formatted_date` FROM `online_regis`.appt_logs WHERE contractCode = '".$this->parentid."' ORDER BY allocationTime DESC LIMIT 1";
		$selQuery_Con	=	$dbIDC_justdial_products->query($selQuery);
		$selQuery_num	=	$dbIDC_justdial_products->numRows($selQuery_Con);
		if($selQuery_num > 0){
			$selQuery_data	=	$dbIDC_justdial_products->fetchData($selQuery_Con);
			$curr_time_min	=	date("Y-m-d H:i");
			if(strtotime($selQuery_data['formatted_date']) ==  strtotime($curr_time_min)){
				$insert_flag	=	1;
			}
		}
		if($insert_flag != 1){
			$insert_appt_logs	=	"INSERT INTO `online_regis`.appt_logs (contractCode,allocatioType,allocationTime,actionTime,data_city,fn_call_cnt) 
									VALUES 
									('".$this->parentid."','".$this->dispose_type."','".addslashes($currentTime)."','".addslashes($actionTime)."','".$this->data_city."','".$fn_call_cnt."')";
			$insert_appt_logs_Con	=	$dbIDC_justdial_products->query($insert_appt_logs);
			if($insert_appt_logs_Con){
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	"Data inserted in online_regis.appt_logs";
			}else{
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	"Data inserted failed online_regis.appt_logs";
			}
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Data inserted failed online_regis.appt_logs";
		}
		$respArr[insert_flag]	=	$insert_flag;
		return json_encode($respArr);die;
	}
	/*
	 * List Of functions added to get the Ad Link Based on the category	 
	 * Date of Live : 19-04-2017	
	 * New Function Addded as it is for sending the Add link based on category id
	 * Created on 14-04-2017 by Apoorv Agrawal
	 * This Function is an Internal used function hence don't use it as API
	 * Pramaters needed are parentid
	*/
	public function returnTvAdLink(){
		$retArr		=	array();
		$link 	= 	array();
		$ad_id 	= 	json_decode($this->get_ad_id(),true);
		if($ad_id['errorCode'] == 0){
			$con_idc_ar		=	new DB($this->db['db_idc']); 
			$ad_ids 	= 	str_replace(",","','",$ad_id['ad_id']);
			$ad_link_qur 	= 	"SELECT ad_id, ad_link, sms_text FROM online_regis1.tbl_category_ad_link WHERE ad_id IN ('".$ad_ids."') AND (sms_text IS NOT NULL AND sms_text!='') AND (ad_link IS NOT NULL AND ad_link!='') ";
			$res_ad_link_con 	= 	$con_idc_ar->query($ad_link_qur);
			if($res_ad_link_con && $con_idc_ar->numRows($res_ad_link_con) > 0){
				while($row_ad_link = $con_idc_ar->fetchData($res_ad_link_con)){
					$link_to_send 	= 	$row_ad_link['ad_link'];				
				}
				$retArr['errorCode'] = 0;
				$retArr['link'] 	 = $link_to_send;
			}else{
				$generic_link 	= "https://youtu.be/5gynJaWAGjE";
				$retArr['errorCode'] = 0;
				$retArr['link'] 	 = $generic_link;
			}
		}else{
			$generic_link 	= "https://youtu.be/5gynJaWAGjE";
			$retArr['errorCode'] = 0;
			$retArr['link'] 	 = $generic_link;
		}
		return json_encode($retArr);
	}
	
	public function get_ad_id(){
		$retArr 	=	array();
		$db_tme_jds		=	new DB($this->db['db_tme']);//tme_jds
		
		if(MONGOUSER==1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$rowTempCategory 			= $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqlTempCategory	= 	"SELECT catIds FROM tbl_business_temp_data WHERE contractid = '".$this->parentid."'";
			$resTempCategory_con 	=  $db_tme_jds->query($sqlTempCategory);
			$rowTempCategory 	= 	$db_tme_jds->fetchData($resTempCategory_con);
		}
		if(count($rowTempCategory)>0){
			if((isset($rowTempCategory['catIds']) && $rowTempCategory['catIds'] != '')){
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$rowTempCategory['catIds']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				if(count($temp_catlin_arr) > 0){                     //fetch ad_id based on cat's
					$temp_catlin_arr_imp = implode("','", $temp_catlin_arr);
					$con_local 	=	new DB($this->db['db_local']); // d_jds
					$fetchAdId 	= 	"SELECT GROUP_CONCAT(DISTINCT(ad_id)) AS ad_id FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$temp_catlin_arr_imp."')";
					$resAdId_con	= 	$con_local->query($fetchAdId);
					if($resAdId_con && $con_local->numRows($resAdId_con) > 0){
						$rowAdId 	= 	$con_local->fetchData($resAdId_con);
						$retArr['ad_id']   	= 	$rowAdId['ad_id'];
						$retArr['errorCode']	=	0;
						$retArr['errorStatus']	=	'Ad id Found';
					}
				}else{
					$retArr['errorCode']	=	1;
					$retArr['errorStatus']	=	'Ad id Not Found';// no cats
				}
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Ad id Not Found';// no cats
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Ad id Not Found';// no cats
		}
		return json_encode($retArr);
	}
	public function get_valid_categories($total_catlin_arr){
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
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	public function create_otp(){
		$argsArr	=	array();
		$params 	=	array_merge($_POST,$_GET);
		if(isset($params['urlFlag']) && $params['urlFlag'] != ''){
			$argsArr	=	$params;
		}else{
			$argsArr		=	json_decode(file_get_contents('php://input'),true);
		}
		if(!isset($argsArr['parentid']) || $argsArr['parentid'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"ParentId issue";
			return json_encode($respArr);
		}
		if(!isset($argsArr['tme_code']) || $argsArr['tme_code'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Tme Code Issue";
			return json_encode($respArr);
		}
		if(!isset($argsArr['companyname']) || $argsArr['companyname'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Company Name Issue";
			return json_encode($respArr);
		}
		$str = '';		
		for($i=5;$i>0;$i--){
			$str = $str.rand(1,9); 
		}
		$manager_details	=	array();
		$server_add		=	$_SERVER['SERVER_ADDR'];
		$exp_server_add = 	explode(".", $server_add);
		//~ if($exp_server_add[2] == 0){
			//~ $manager_details	=	array("000108","10045492");
		//~ }elseif($exp_server_add[2] == 32){
			//~ $manager_details	=	array("004779");
		//~ }
		$whereCondtion	=	'';
		if($exp_server_add[2] == 17){
			$whereCondtion	=	"WHERE city = '".$_SESSION['loginCity']."'";
		}
		$con_local		=	new DB($this->db['db_local']);
		$get_hod_data	=	"SELECT empcode FROM d_jds.tbl_hod_list_otp ".$whereCondtion."";
		$con_get_hod_data	=	$con_local->query($get_hod_data);
		$num_get_hod_data	=	$con_local->numRows($con_get_hod_data);
		if($num_get_hod_data > 0){
			while($data = $con_local->fetchData($con_get_hod_data)){
				$manager_details[]	=	$data['empcode'];
			}
		}
		$listofMobs	=	array();
		for($i=0;$i<count($manager_details);$i++){
			$curlParams_intermediate['url']			=	"http://192.168.20.237/hrmodule/employee/fetch_employee_info/".$manager_details[$i];
			$curlParams_intermediate['formate'] 	= 	'basic';
			$curlParams_intermediate['method'] 		=	 'post';
			$manager_details[$i]	=	json_decode(Utility::curlCall($curlParams_intermediate),true);
			$listofMobs[$i]			=	$manager_details[$i]['data']['mobile_num'];
		}
		//~ $paras_curlCall['action']		=	"aotpm";
		//~ $paras_curlCall['listofMobs']	=	json_encode($listofMobs);
		//~ $paras_curlCall['parentid'] 	=	$argsArr['parentid'];
		//~ $paras_curlCall['companyname'] 	=	$argsArr['companyname'];
		//~ $paras_curlCall['tme_name'] 	=	$argsArr['tme_name'];
		//~ $paras_curlCall['otp'] 			=	$str;
		//~ $curlParams_temp['url']			=	"http://".$_SERVER['HTTP_HOST']."/library/RatingEmailSms.php";
		//~ $curlParams_temp['formate'] 	= 	'basic';
		//~ $curlParams_temp['method'] 		=	'post';
		//~ $curlParams_temp['postData'] 	= 	$paras_curlCall; 
		//~ $sms_sent_otp					=	Utility::curlCall($curlParams_temp);
		//~ $respArr['sms_sent_otp']		=	json_decode($sms_sent_otp,true);
		$con_local		=	new DB($this->db['db_local']);
		$currentTime	=	date("Y-m-d H:i:s");
		$query_all_me_otp 		=	"INSERT INTO d_jds.all_me_otp(parentid,tme_code, tme_name, otp, expired, insertedOn) 
										VALUES 
										('".trim($argsArr['parentid'])."','".$argsArr['tme_code']."','".addslashes(stripslashes($argsArr['tme_name']))."','".$str."','0','".$currentTime."')";
		$con_query_all_me_otp	=	$con_local->query($query_all_me_otp);
		if($con_query_all_me_otp){
			$respArr['errorCode']	=	0;
			$respArr['errorStatus']	=	"Data Inserted";
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Data not Inserted";
		}
		return json_encode($respArr);
	}
	public function checkOTP_otp(){
		$argsArr	=	array();
		$params 	=	array_merge($_POST,$_GET);
		if(isset($params['urlFlag']) && $params['urlFlag'] != ''){
			$argsArr	=	$params;
		}else{
			$argsArr		=	json_decode(file_get_contents('php://input'),true);
		}
		if(!isset($argsArr['parentid']) || $argsArr['parentid'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"ParentId issue";
			return json_encode($respArr);
		}
		if(!isset($argsArr['tme_code']) || $argsArr['tme_code'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"Tme Code Issue";
			return json_encode($respArr);
		}
		if(!isset($argsArr['otp']) || $argsArr['otp'] == ''){
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"otp Issue";
			return json_encode($respArr);
		}
		$con_local			=	new DB($this->db['db_local']);
		$currentTime		=	date("Y-m-d H:i:s");
		$endTime 			= 	strtotime($currentTime) - 900;
		$endTime			=	date("Y-m-d H:i:s",$endTime);
		$query_all_me_otp 	=	"SELECT otp FROM d_jds.all_me_otp WHERE parentid='".trim($argsArr['parentid'])."' AND insertedOn>='".$endTime."' AND insertedOn <='".$currentTime."' ORDER BY insertedOn DESC LIMIT 1";		
		$con_query_all_me_otp	=	$con_local->query($query_all_me_otp);
		if($con_query_all_me_otp && ($con_local->numRows($con_query_all_me_otp) > 0)){
			$otp_data 	= 	$con_local->fetchData($con_query_all_me_otp);
			$respArr['otp_data']	=	$otp_data;
			if($otp_data['otp'] == $argsArr['otp']){
				$respArr['errorCode']	=	0;
				$respArr['errorStatus']	=	"OTPM";
			}else{
				$respArr['errorCode']	=	1;
				$respArr['errorStatus']	=	"OTPW";
			}
		}else{
			$respArr['errorCode']	=	1;
			$respArr['errorStatus']	=	"OTPEXP";
		}
		return json_encode($respArr);
	}
	
	/*New Function Created HERE for inserting All ME Allocations*/
	public function insertAllMeDetails(){
		header('Content-Type: application/json');
		$params		=	array_merge($_GET,$_POST);
		$argsArr	=	array();
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);
		}
		$meCode			=	$argsArr['meCode'];
		$parentid		=	$argsArr['parentid'];
		$this->parentid	=	$parentid;
		$actionSlot		=	$argsArr['actTime'];
		$actionDate		=	$argsArr['actDate'];
		$allocationType	=	$argsArr['disposeVal'];
		$tmeCode		=	$argsArr['ucode'];
		$data_city		=	$argsArr['data_city'];
		/*Get ME details*/
		$me_details		=	array();
		$me_details		=	json_decode($this->get_ME_data($meCode),true);
		$respArr['me_details']['code']	=	$meCode;
		if($me_details['errorCode'] == 0){
			$respArr['me_details']['Name']	=	$me_details['data']['empName'];
			$respArr['me_details']['mobile']	=	$me_details['data']['mobile'];
		}
		/*Get ME details*/		
		
		/*Get TME details*/
		$tme_details	=	array();
		$tme_details	=	json_decode($this->get_ME_data($tmeCode),true);
		$respArr['tme_details']['code']	=	$tmeCode;
		if($tme_details['errorCode'] == 0){
			$respArr['tme_details']['Name']	=	$tme_details['data']['empName'];
			$respArr['tme_details']['mobile']	=	$tme_details['data']['mobile'];
		}
		/*Get TME details*/
		
		$genshadowArr	=	array();
		$genshadowArr 	=	json_decode($this->get_generalinfo_shadow(),true);
		$compName	=	'';
		$area		=	'';
		$pincode	=	'';
		if($genshadowArr['errorCode'] == 0){
			$genshadowArr	=	$genshadowArr['data'];
			$compName		=	$genshadowArr['companyname'];
			$area			=	$genshadowArr['area'];
			$pincode		=	$genshadowArr['pincode'];
		}
		$respArr['contract_details']['compName']	=	$compName;
		$respArr['contract_details']['area']	=	$area;
		$respArr['contract_details']['pincode']	=	$pincode;
		$currentTime	=	date("Y-m-d H:i:s");// is server time time
		$actionDate		=	date("Y-m-d", strtotime($actionDate));
		$con_local 		=	new DB($this->db['db_local']);
		$ins_tbl_apptLogs		=	"INSERT INTO tbl_apptLogs SET									
										`parentid`				=	'".$this->parentid."',									
										`json_resp`				=	'".addslashes(stripslashes(json_encode($respArr)))."',
										`appointmentDate`		=	'".$actionDate."',
										`actionTime`			=	'".$actionSlot."',
										`followUp`				=	'0',
										`all_me`				=	'1',
										`appt_alloc`			=	'1',
										`insertedOn`  			=	'".$currentTime."'";
		$con_tbl_apptLogs	=	$con_local->query($ins_tbl_apptLogs);
		$retArr	=	array();
		if($con_tbl_apptLogs){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data inserted";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data not inserted";
		}
		return json_encode($retArr);
	}
	/*New Function Created HERE for inserting Auto Allocations View Exec click on PopUp*/
	public function insertpincodeDetails(){
		header('Content-Type: application/json');
		$params		=	array_merge($_GET,$_POST);
		$argsArr	=	array();
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);
		}
		$meCode			=	$argsArr['meCode'];
		$parentid		=	$argsArr['parentid'];
		$this->parentid	=	$parentid;
		$actionSlot		=	$argsArr['actTime'];
		$actionDate		=	$argsArr['actDate'];
		$allocationType	=	$argsArr['disposeVal'];
		$tmeCode		=	$argsArr['ucode'];
		$data_city		=	$argsArr['data_city'];
		/*Get ME details*/
		$me_details		=	array();
		$me_details		=	json_decode($this->get_ME_data($meCode),true);
		$respArr['me_details']['code']	=	$meCode;
		if($me_details['errorCode'] == 0){
			$respArr['me_details']['Name']	=	$me_details['data']['empName'];
			$respArr['me_details']['mobile']	=	$me_details['data']['mobile'];
		}
		/*Get ME details*/		
		
		/*Get TME details*/
		$tme_details	=	array();
		$tme_details	=	json_decode($this->get_ME_data($tmeCode),true);
		$respArr['tme_details']['code']	=	$tmeCode;
		if($tme_details['errorCode'] == 0){
			$respArr['tme_details']['Name']	=	$tme_details['data']['empName'];
			$respArr['tme_details']['mobile']	=	$tme_details['data']['mobile'];
		}
		/*Get TME details*/
		
		$genshadowArr	=	array();
		$genshadowArr 	=	json_decode($this->get_generalinfo_shadow(),true);
		$compName	=	'';
		$area		=	'';
		$pincode	=	'';
		if($genshadowArr['errorCode'] == 0){
			$genshadowArr	=	$genshadowArr['data'];
			$compName		=	$genshadowArr['companyname'];
			$area			=	$genshadowArr['area'];
			$pincode		=	$genshadowArr['pincode'];
		}
		$respArr['contract_details']['compName']	=	$compName;
		$respArr['contract_details']['area']	=	$area;
		$respArr['contract_details']['pincode']	=	$pincode;
		$currentTime	=	date("Y-m-d H:i:s");// is server time time
		$actionDate		=	date("Y-m-d", strtotime($actionDate));
		$con_local 		=	new DB($this->db['db_local']);		
		
		$ins_tbl_apptLogs		=	"INSERT INTO tbl_apptLogs SET									
										`parentid`				=	'".$this->parentid."',									
										`json_resp`				=	'".addslashes(stripslashes(json_encode($respArr)))."',
										`appointmentDate`		=	'".$actionDate."',
										`actionTime`			=	'".$actionSlot."',
										`followUp`				=	'0',
										`all_me`				=	'2',
										`appt_alloc`			=	'1',
										`insertedOn`  			=	'".$currentTime."'";
		$con_tbl_apptLogs	=	$con_local->query($ins_tbl_apptLogs);
		$retArr	=	array();
		if($con_tbl_apptLogs){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data inserted";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data not inserted";
		}
		return json_encode($retArr);
	}
	/*
	 * This API is for getting categories based on parentid for getting list of competitors
	 * API created by Apoorv
	*/
	public function get_compt_cat($catidarray){
		$con_local	=	new DB($this->db['db_local']);
		$retArr		=	array();
		if(is_array($catidarray) && count($catidarray) > 0) {
			$sel_cat_qur 	=	"SELECT category_name,catid,search_type from tbl_categorymaster_generalinfo where catid in (".implode(',',$catidarray).")  order by callcount desc limit 1";
			$sel_cat_qur_con	=	$con_local->query($sel_cat_qur);
			$sel_cat_qur_num 	=	$con_local->numRows($sel_cat_qur_con);
			if($sel_cat_qur_num > 0){
				$retArr['data']		=	$con_local->fetchData($sel_cat_qur_con);
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'No Data Found';
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Cat Array';
		}
		return json_encode($retArr);
	}
	public function insert_tbl_appt_map($parentid,$mecode,$tmecode,$actionDate,$actionTime){
		$server_add		=	$_SERVER['SERVER_ADDR'];
		$exp_server_add = 	explode(".", $server_add);
		$source	=	'';
		switch ($exp_server_add[2]) {
			case '56':				
				$source = 'Ahmedabad';
				break;
			case '26':			
				$source = 'Bangalore';
				break;
			case '32':				
				$source = 'Chennai';
				break;
			case '8':				
				$source = 'Delhi';
				break;
			case '50':				
				$source = 'Hyderabad';
				break;
			case '16':
				$source = 'Kolkata';
				break;
			case '0':
				$source = 'Mumbai';
				break;
			case '40':
				$source = 'Pune';
				break;
			case '17':
				$source	=	'Remote';
				break;
			case '35':				
				$source = 'Ahmedabad';
				break;
			default:
				$source	=	'Def';
		}
		$retArr	=	array();
		$con_local 		=	new DB($this->db['db_local']);
		$db_idc_ar		=	new DB($this->db['db_idc']);
		$get_auto_id	=	"SELECT allocationId,contractCode,parentcode,empcode,mename,tmename,compname,actionTime FROM tblContractAllocation WHERE allocationType IN (25,99) AND contractCode = '".$parentid."' AND actionTime = '".$actionDate." ".$actionTime."' AND parentcode = '".$tmecode."' AND empcode = '".$mecode."' AND cancel_flag=0";
		$con_get_auto_id	=	$con_local->query($get_auto_id);
		$num_con_get_auto_id 	=	$con_local->numRows($con_get_auto_id);
		if($num_con_get_auto_id > 0){
			$retArr['data'] 	=	$con_local->fetchData($con_get_auto_id);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
			$inser_qur_appt_map	=	"INSERT INTO online_regis.tbl_appt_map(apptid,contractCode,parentcode,empcode,me_name,tme_name,cancel_url,actionTime,main_url,companyname,data_city) VALUES ('".$retArr['data']['allocationId']."','".$retArr['data']['contractCode']."','".$retArr['data']['parentcode']."','".$retArr['data']['empcode']."','".addslashes(stripslashes($retArr['data']['mename']))."','".addslashes(stripslashes($retArr['data']['tmename']))."','','".$retArr['data']['actionTime']."','','".addslashes(stripslashes($retArr['data']['compname']))."','".strtolower($source)."')";
			$inser_qur_appt_map_con	=	$db_idc_ar->query($inser_qur_appt_map);
			if($inser_qur_appt_map_con) {
				$retArr['errorCode_ins_appt_map']	=	0;
				$retArr['errorStatus_ins_appt_map']	=	'Data inserted';
			}else{
				$retArr['errorCode_ins_appt_map']	=	1;
				$retArr['errorStatus_ins_appt_map']	=	'Data not inserted';
			}
		}else{
			$retArr['errorCode']		=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	public function get_team_type($allocId_tme){
		if(isset($allocId_tme) && ($allocId_tme != '' || $allocId_tme != null || $allocId_tme != 'null')){
			$con_local 		=	new DB($this->db['db_local']);
			$get_team_type_qr	=	" SELECT team_id,team_name FROM tbl_team_type where team_id = '".$allocId_tme."'";
			$con_get_team_type_qr	=	$con_local->query($get_team_type_qr);
			$num_get_team_type_qr 	=	$con_local->numRows($con_get_team_type_qr);
			if($num_get_team_type_qr > 0){
				$retArr['data'] 		=	$con_local->fetchData($con_get_team_type_qr);
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'TeamType Found';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'TeamType Not Found';
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'AllocId Not Found';
		}
		return json_encode($retArr);
	}
	/*
	 * This function accepts a parameter parentid
	 * It will check if this data is Paid active of not
	 * if Paid active then it will return the ME Code(if deal Closed by the TME)
	*/
	public function get_paid_data($parentid){
		$retArr 	=	array();
		if($parentid != '' && $parentid != NULL && $parentid != 'null'){
			$con_fin	=	new DB($this->db['db_finance']);
			//~ $get_paid_details	=	"select tmecode,tmename,mecode,mename from db_finance.contract_payment_details where parentid='".$parentid."' and approvalstatus=1 ORDER BY entry_date DESC LIMIT 1";
			$get_paid_details	=	"SELECT tmecode,tmename,mecode,mename  FROM db_finance.payment_version_approval a JOIN db_finance.payment_otherdetails p ON a.parentid=p.parentid AND a.version=p.version WHERE a.parentid='".$parentid."' ORDER BY approval_date DESC LIMIT 1";
			$con_get_paid_details	=	$con_fin->query($get_paid_details);
			$num_get_paid_details 	=	$con_fin->numRows($con_get_paid_details);
			if($num_get_paid_details > 0){ // checking if contract is paid Active
				$paid_contract_det	=	$con_fin->fetchData($con_get_team_type_qr);
				if($paid_contract_det['mecode'] != "" && $paid_contract_det['mecode'] != NULL && $paid_contract_det['mecode'] != 'null'){ // checking if the deal is closed by ME or not
					$retArr['mktEmpCode']	=	$paid_contract_det['mecode'];
					$retArr['empName']		=	$paid_contract_det['mename'];
					$retArr['errorCode']	=	0;
					$retArr['errorStatus']	=	'ME FOUND';
				}else{
					$retArr['mktEmpCode']	=	'';
					$retArr['empName']		=	'';
					$retArr['errorCode']	=	1;
					$retArr['errorStatus']	=	'ME Not FOUND';
				}
			}else{
				$retArr['mktEmpCode']	=	'';
				$retArr['empName']		=	'';
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Not a Paid Data';
			}
		}else{
			$retArr['mktEmpCode']	=	'';
			$retArr['empName']		=	'';
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'ME Not FOUND';
		}
		return json_encode($retArr);
	}
	public function getCurrentPaidStatus($parentid)
	{		
		$curlPaidParams = array();
		$paidParamsArr	 =	array();
		$paidParamsArr['parentid']	    =	$parentid;
		$paidParamsArr['data_city']	    =	DATA_CITY;
		$paidParamsArr['rquest']		=	'get_contract_type';
		
		$curlPaidParams['formate'] 	 	=	'basic';
		$curlPaidParams['method']       =	'post';
		$curlPaidParams['headerJson']   =	'json';
		$curlPaidParams['url']			=	JDBOX_API."/services/contract_type.php";
		$curlPaidParams['postData'] 	=	json_encode($paidParamsArr);
		$paidStatusData                	=	json_decode(Utility::curlCall($curlPaidParams),true);
		
		$current_paid_status = $paidStatusData;
		return $current_paid_status;
	}
	private function jsonInsertDisp($paramsSend_dc){
		GLOBAL $parseConf;
		$curlParams_dc['url'] = "http://".DC_API_NEW."/data_correction/jsonInsert.php";
		$curlParams_dc['formate'] = 'basic';
		$curlParams_dc['method'] = 'post';
		$curlParams_dc['postData'] = "comp_arr=".urlencode(json_encode($paramsSend_dc));  
		if($parseConf['servicefinder']['remotecity'] == 1){
			$dc_response	=	Utility::curlCall($curlParams_dc);
		}
	}
	private function update_tbl_tme_data_search($parentid,$dispose_type,$currentTime){
		$con_local = new DB($this->db['db_local']);
		$updt_tbl_tme_data_search = "UPDATE tbl_tme_data_search SET allocationtype = '".$dispose_type."' , allocationtime = '".$currentTime."' WHERE parentid = '".$parentid."'";
		$con_updt_tbl_tme_data_search	=	$con_local->query($updt_tbl_tme_data_search);
	}
	private function call_insertNewApptApi($appt_arr){
		$curlParams = array();
		$curlParams['url'] = "http://".GNO_URL."/presentation/dashboard_services/dashboard/insertNewApptApi";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['postData'] = $appt_arr;  
		$dc_response = Utility::curlCall($curlParams);
	}
	private function getPopularArea($pincode){
		$pop_area = "";
		$con_local = new DB($this->db['db_local']);
		$qur_get_popul_area = "SELECT areaname FROM tbl_areamaster_consolidated_v3 WHERE pincode='".trim($pincode)."' AND display_flag=1 AND type_flag=1 AND DE_display=1 ORDER BY company_count DESC LIMIT 1";
		$con_qur_get_popul_area	= $con_local->query($qur_get_popul_area);
		if($con_qur_get_popul_area){
			$num_qur_get_popul_area = $con_local->numRows($con_qur_get_popul_area);
			if($num_qur_get_popul_area > 0){
				$get_popul_area_data = $con_local->fetchData($con_qur_get_popul_area);
				$pop_area = trim($get_popul_area_data['areaname']);
			}
		}
		return $pop_area;
	}
}
?>
