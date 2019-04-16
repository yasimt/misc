<?php	
//namespace etc\tmemodel;
class CancelApptInfo_Model extends Model {
	public function __construct() {
		parent::__construct();
	}
	
	public function cancel_appt() {
		header('Content-Type: application/json');
		$paramsArr	=	array();
		$retArr		=	array();
		if(isset($_REQUEST['urlFlag']) && $_REQUEST['urlFlag'] == 1){
			$paramsArr	=	$_REQUEST;
		}else{
			$paramsArr	=	json_decode(file_get_contents('php://input'),true);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Parent-Id Not Found";
			return json_encode($retArr);
		}elseif(!isset($paramsArr['empCode']) || (isset($paramsArr['empCode']) && $paramsArr['empCode'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"MECode Not Found";
			return json_encode($retArr);
		}elseif(!isset($paramsArr['parentCode']) || (isset($paramsArr['parentCode']) && $paramsArr['parentCode'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"TMECode Not Found";
			return json_encode($retArr);
		}elseif(!isset($paramsArr['apptTime']) || (isset($paramsArr['apptTime']) && $paramsArr['apptTime'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Appt Time Not Found";
			return json_encode($retArr);
		}elseif(!isset($paramsArr['companyname']) || (isset($paramsArr['companyname']) && $paramsArr['companyname'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Company Name Not Found";
			return json_encode($retArr);
		}elseif(!isset($paramsArr['logged_user_id']) || (isset($paramsArr['logged_user_id']) && $paramsArr['logged_user_id'] == '')){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"user not logged-in";
			return json_encode($retArr);
		}else{
			// update the table tblContractAllocation Table
			$con_local		=	new DB($this->db['db_local']);
			$db_idc_ar		=	new DB($this->db['db_idc']);
			$sqlUpdateCancelAlloc 	=	"UPDATE tblContractAllocation SET cancel_flag = '1' WHERE contractCode='".$paramsArr['parentid']."' AND empcode='".$paramsArr['empCode']."' AND allocationType IN ('25','99') AND parentcode='".$paramsArr['parentCode']."'";
			$conUpdateCancelAlloc 					=	$con_local->query($sqlUpdateCancelAlloc);
			
			$sqlUpdateCancelAlloc_idc 	=	"UPDATE tblContractAllocation SET cancel_flag = '1' WHERE contractCode='".$paramsArr['parentid']."' AND empcode='".$paramsArr['empCode']."' AND allocationType IN ('25','99') AND parentcode='".$paramsArr['parentCode']."'";
			$con_idcUpdateCancelAlloc				=	$db_idc_ar->query($sqlUpdateCancelAlloc_idc);
			
			$sqlUpdateCancelAlloc_consolidated 	=	"UPDATE db_justdial_products.tblContractAllocation_consolidated SET cancel_flag = '1' WHERE contractCode='".$paramsArr['parentid']."' AND empcode='".$paramsArr['empCode']."' AND allocationType IN ('25','99') AND parentcode='".$paramsArr['parentCode']."' AND actionTime = '".$paramsArr['apptTime']."'";
			$con_idcUpdateCancelAlloc_consolidated	=	$db_idc_ar->query($sqlUpdateCancelAlloc_consolidated);
			
			if($conUpdateCancelAlloc && $con_idcUpdateCancelAlloc && $con_idcUpdateCancelAlloc_consolidated) {
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'UPDATE tblContractAllocation DONE';
				$sendMessageToME	=	json_decode($this->sendMessageToME($paramsArr['empCode'],$paramsArr['companyname'],$paramsArr['parentid'],$paramsArr['apptTime']),true);
				$sendMailsToME		=	json_decode($this->send_mail_to_me($paramsArr['empCode'],$paramsArr['companyname'],$paramsArr['parentid'],$paramsArr['apptTime'],$paramsArr['parentCode']),true);
				if($sendMessageToME['errorCode'] == 0){
					$retArr['errorCode_msg_sent']	=	0;
					$retArr['errorStatus_msg_sent']	=	"Message Sent";
				}else{
					$retArr['errorCode_msg_sent']	=	1;
					$retArr['errorStatus_msg_sent']	=	"Message Not Sent";
				}
				/*
				 * Start Insertion in Logs Table for the Cancel Appt.
				 * Date : March 20 2017
				 * Created by Apoorv Agrawl
				*/
				$SERVER_CITY_FLG	=	'';
				$server_addr_arr	= explode(".",$_SERVER['SERVER_ADDR']);
					if($server_addr_arr[2] == 0){
						$SERVER_CITY_FLG	=	"Mumbai";
					}elseif($server_addr_arr[2] == 8){
						$SERVER_CITY_FLG	=	"Delhi";
					}elseif($server_addr_arr[2] == 16){
						$SERVER_CITY_FLG	=	"Kolkata";
					}elseif($server_addr_arr[2] == 26){
						$SERVER_CITY_FLG	=	"Bangalore";
					}elseif($server_addr_arr[2] == 32){
						$SERVER_CITY_FLG	=	"Chennai";
					}elseif($server_addr_arr[2] == 40){
						$SERVER_CITY_FLG	=	"Pune";
					}elseif($server_addr_arr[2] == 50){
						$SERVER_CITY_FLG	=	"Hyderabad";
					}elseif($server_addr_arr[2] == 56 || $server_addr_arr[2] == 35){
						$SERVER_CITY_FLG	=	"Ahmedabad";
					}elseif($server_addr_arr[2] == 17){
						$SERVER_CITY_FLG	=	"remote_cities";
					}elseif($server_addr_arr[2] == 64){
						$SERVER_CITY_FLG	=	"Mumbai";
					}
					$SERVER_CITY_FLG	=	strtolower($SERVER_CITY_FLG);
					$sel_qur_appt		=	"SELECT allocationId,contractCode,empcode,parentcode,actionTime,allocationType,tmename,mename,data_city,allocationTime FROM tblContractAllocation WHERE contractCode='".$paramsArr['parentid']."' AND empCode= '".$paramsArr['empCode']."' AND parentcode='".$paramsArr['parentCode']."' AND allocationType IN (25,99)";
					$con_qur_appt		=	$con_local->query($sel_qur_appt);
					$con_qur_appt_numrows 	= $con_local->numRows($con_qur_appt);
					if($con_qur_appt_numrows > 0){
						$sel_qur_appt_data	=	$con_local->fetchData($con_qur_appt);
						$currentTime	=	date("Y-m-d H:i:s");
						$src	=	'';
						$cancel_new_arr_params = array();
						$cancel_new_arr_params['parentid'] = $sel_qur_appt_data['contractCode'];
						$cancel_new_arr_params['me_code'] = $sel_qur_appt_data['empcode'];
						$cancel_new_arr_params['allocation_id'] = $sel_qur_appt_data['allocationId'];
						$cancel_new_arr_params['allocation_time'] = $sel_qur_appt_data['allocationTime'];
						$cancel_new_arr_params['last_disposition_time'] = $currentTime;
						$cancel_new_arr_params['cancel_flag'] = 1;
						$cancel_new_arr_params['disposition'] = $sel_qur_appt_data['allocationType'];						
						$cancel_new_arr_params['action_time'] = $sel_qur_appt_data['actionTime'];						
						$this->updateCancelAppt_new($cancel_new_arr_params);
						if($SERVER_CITY_FLG == 'remote_cities'){
							$src	=	'remote-'.strtolower($sel_qur_appt_data['data_city']);
						}else{$src	=	strtolower($sel_qur_appt_data['data_city']);}
						
						$insert_cancel_flags	=	"INSERT INTO online_regis.tbl_cancel_logs (contractCode,empcode,parentcode,apptDate,meName,tmeName,cancelby_code,insertedOn,allocationType,city,src,cancelby_name,cancel_src) VALUES ('".$paramsArr['parentid']."','".$sel_qur_appt_data['empcode']."','".$sel_qur_appt_data['parentcode']."','".$sel_qur_appt_data['actionTime']."','".addslashes(stripslashes($sel_qur_appt_data['mename']))."','".addslashes(stripslashes($sel_qur_appt_data['tmename']))."','".$paramsArr['empCode']."','".$currentTime."','".$sel_qur_appt_data['allocationType']."','".$sel_qur_appt_data['data_city']."','".addslashes(stripslashes($src))."','".$paramsArr['logged_user_id']."','TME')";
						
						$con_insert_cancel_flags		=	$db_idc_ar->query($insert_cancel_flags);
						if($con_insert_cancel_flags){
							$retArr['errorCode_insert_log'] =   0;
							$retArr['errorStatus_insert_log']   =   'Update Not Done';
						}else{
							$retArr['errorCode_insert_log'] =   1;
							$retArr['errorStatus_insert_log']   =   'Update Not Done';
						}
					}
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'UPDATE tblContractAllocation FAIL';
			}
		}
		return json_encode($retArr);
	}
	// Get Details Of ME for Sending Cancellation Message to the ME
	public function sendMessageToME($empCode,$compName,$parentid,$ApptTime) {
		$get_TME_ME_data	=	json_decode($this->get_TME_ME_data($empCode),true);
		if($get_TME_ME_data['errorCode'] == 0){
			if($get_TME_ME_data['errorCode'] == 0){
				$sms_text		=	"Your Appoitnment for Contract '".addslashes(stripslashes($compName))."' '".addslashes(stripslashes($parentid))."' for Appointment Time ".addslashes(stripslashes($ApptTime)).", has been Canceled by TME.";
				//~ "Id,CallNGO,TMEName,TMEMobile,MKTCode,MKTName,EntryDate,EntryTime,ApptDate,ApptTime,OpenReg,	Company,Contact,Building,Street,Location,AreaName,PinCode,Telephone1,Telephone2,Mobile,Info1,CityName,EmpEmail,EmpMobile,TMEManager,Class,parentid,T20_flag,empclass,tmecode"
				$con_fin	=	new DB($this->db['db_finance']);
				$query		=	"INSERT ignore INTO db_jd_emailsms.tmeappentry(MKTCode,MKTName,SmsText,SmsReady,EmpMobile) VALUES ('".$empCode."','".$get_TME_ME_data['data']['empName']."','".addslashes(stripslashes($sms_text))."','Y','".$get_TME_ME_data['data']['mobile']."')";
				$con_tmeappentry 	= 	$con_fin->query($query); 
				if($con_tmeappentry) { // data Inserted
					$ret_Arr['errorCode']	=	0;
					$ret_Arr['errorStatus']	=	"SMS Sent";
				} else { // not inserted
					$ret_Arr['errorCode']	=	1;
					$ret_Arr['errorStatus']	=	"SMS Not Sent";
				}
			}
		}else{
			$ret_Arr	=	$get_TME_ME_data;
		}
		return json_encode($ret_Arr);
	}
	/*
	 * Creating a function for sending the email to the ME executive in case of Appointment Cancel
	 * Created by Apoorv Agrawal
	*/
	public function send_mail_to_me($empCode,$compName,$parentid,$ApptTime,$parentCode){
		$get_ME_data	=	array();
		$get_TME_data	=	array();
		$get_ME_data	=	json_decode($this->get_TME_ME_data($empCode),true);
		$get_TME_data	=	json_decode($this->get_TME_ME_data($parentCode),true);
		$me_email	=	'';
		$retArr['me_details']	=	$get_ME_data;
		if($get_ME_data['errorCode'] == 0){
			if($get_ME_data['errorCode'] == 0){
				$me_email	=	$get_ME_data['data']['emailId'];
				$paras_curlCall		=	array();
				$sms_sent_appt		=	array();
				$paras_curlCall['action']		=	'cancelapptem';
				$paras_curlCall['me_email']		=	addslashes(stripslashes($me_email));
				$paras_curlCall['me_name']		=	addslashes(stripslashes($get_ME_data['data']['empName']));
				$paras_curlCall['comp_name']	=	addslashes(stripslashes($compName));
				$paras_curlCall['parentid']		=	$parentid;
				$paras_curlCall['ApptTime']		=	$ApptTime;
				$paras_curlCall['tme_name']		=	addslashes(stripslashes($get_TME_data['data']['empName']));
				//~ $curlParams_temp['url']			=	"http://" . $_SERVER['HTTP_HOST'] . "/tmegenio/library/RatingEmailSms.php?".http_build_query($paras_curlCall)."";
				$curlParams_temp['url']			=	"http://" . $_SERVER['HTTP_HOST'] . "/library/RatingEmailSms.php?".http_build_query($paras_curlCall)."";
				$curlParams_temp['formate'] 	= 	'basic';
				$curlParams_temp['method'] 		=	 'get';
				$sms_sent_appt		=	Utility::curlCall($curlParams_temp);
				$sms_sent_appt		=	json_decode($sms_sent_appt,true);
				if($sms_sent_appt['errorCode'] == 0){
					$retArr['errorCode_instant']	=	0;
					$retArr['errorStatus_instant']	=	'Mail SENT';
				}else{
					$retArr['errorCode_instant']	=	1;
					$retArr['errorStatus_instant']	=	'Mail NOT SENT';
				}
			}
		}else{
			$retArr['errorCode_instant']	=	1;
			$retArr['errorStatus_instant']	=	'Mail NOT SENT';
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
	private function updateCancelAppt_new($cancel_new_arr_params){
		$curlParams = array();
		$curlParams['url'] = "http://".GNO_URL."/presentation/dashboard_services/dashboard/updateApptDisposition";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['postData'] = $cancel_new_arr_params;
		$dc_response = Utility::curlCall($curlParams);		
	}
}
?>
