<?php 
/**
* 
*/

class MkgJrInfo_Model extends Model{
	function __construct(){
		# code...
		parent::__construct();
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
	}
	public function mktgEmpMap(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$ucode		=	$params['ucode'];
		}else{
			$ucode		=	$_REQUEST['ucode'];
		}
		if($ucode=="" || empty($ucode)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"ucode  Not found";
			echo json_encode($retArr);
			exit;
		}
		$conn_local		=	new DB($this->db['db_local']);//d_jds
		$seletrowid 	= 	"SELECT rowId FROM d_jds.mktgEmpMap WHERE mktEmpCode='".$ucode."'";
		$rowidobj 		=  	$conn_local->query($seletrowid);
		$num			=	$conn_local->numRows($rowidobj);
		if($num >0){
			$rowid_res 		= 	$conn_local->fetchData($rowidobj);
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"Data Found";
		}else{
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Data Not Found";
		}
		echo json_encode($retArr); die();
	}

	/*
	* function to get Data City Based on s_deptCity
	* Created by Apoorv Agrawal 
	* Date: 9-02-2016
	*/
	public function get_datacity($city){	
		$dbObjLocal	=	new DB($this->db['db_local']);
		$city		= 	strtolower($city);
		$datacity	= 	'';
		switch($city){
			case 'ahmedabad':
			case 'sanand':	$datacity = "ahmedabad";
				break;
			case 'delhi':
			case 'gurgaon':
			case 'ghaziabad':
			case 'faridabad':
			case 'greater noida':
			case 'noida':			$datacity = "delhi";
				break;		
			case 'hyderabad':
			case 'secunderabad':	$datacity = "hyderabad";
				break;		
			case 'howrah':
			case 'hooghly':
			case 'north 24 parganas':
			case 'south 24 parganas':
			case 'kolkata': 		$datacity = "kolkata";
				break;	
			case 'mumbai':
			case 'thane':
			case 'navi mumbai':
			case 'panvel':
			case 'vashi':
			case 'new panvel':
			case 'raigad-maharashtra':
			case 'raigarh':
			case 'raigad':			$datacity = "mumbai";
				break;
			case 'chandigarh':
			case 'mohali':
			case 'zirakpur':
			case 'panchkula':		$datacity = "chandigarh";
				break;
			default: 
				$sql 	= 	"SELECT data_city FROM city_master WHERE ct_name = '".$city."'" ; 	
				$con	=	$dbObjLocal->query($sql);
				$num	=	$dbObjLocal->numRows($con);
				if($con && $num > 0){
					$row 		= 	$dbObjLocal->fetchData($con);
					$datacity 	=	$row['data_city'];
				}
		}	
		return $datacity;	
	}
	protected function addslashesArray($resultArray){
		foreach($resultArray AS $key=>$value){
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		return $resultArray;
	}
	
	/* Function to get timings for Grab & Non Grab 
	 * Appointment Fix/ Appointment Refix/ CALL BACK/ FOLLOW UP
	 * Created By Sumesh Dubey and Apoorv Agrawal
		Date Format dd-mm-YY
		Date: 05-02-2016
		Parameters: Date.GrabFlag: 1/2
		purpose: Get TimeSlots with Abeld or Dis-abled flag
		1->Disabled,0->Enabled
		* Chabges Made for alternate Address for grab flow also 
		* Grb_Normal_alt_add flag added 2 means GRAB else Non Grab
		* Changes Done by Apoorv Agrawal
		* @06-058-2016(DOL) 
	*/
	public function get_me(){
		GLOBAL $parseConf;
		$remote_flg	=	0;//0 means pan India: 1 means remoteCity
		if($parseConf['remotecity'] ==0){
			$remote_flg	=	0;
		}else{
			$remote_flg	=	1;
		}
		//die();
		$urlFlag 	=	$_REQUEST['urlFlag'];
		$stree 		=	"";
		$retArr 	=	array();
		$tmSt 		=    "";
		$tmEnd		=	"";			
		$stVal		=	"";									

		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		
		$time_array 			=	array("08:00","08:30","09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30","20:00","20:30","21:00","21:30","22:00","22:30","23:00","23:30");
		
		$followUp_Callback_arr 	=	array("08:00","08:30","09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30","20:00","20:30","21:00","21:30","22:00","22:30","23:00","23:30");

		/*
		 * @Checking Grab appointment flag 
		*/
		$allocateToJdaClick	= 0; // new variable declared here for Allocate to JDA
		if(!$urlFlag){
			$graBFlag 	=	$params['graBFlag'];
			$pincode	=	$params['pincode'];
			$actiondate = 	$params['actiondate'];
			$stVal		=	$params['stVal'];
			$parentid	=	$params['parentid'];
			$AllMeFlag	=	$params['AllMeFlag'];
			$data_city	=	$params['data_city'];
			$tme_code	=	$params['tme_code'];
			$allocToME	=	$params['allocToME'];
			$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
			$Grb_Normal_alt_add	=	$params['Grb_Normal_alt_add'];
			$Jd_Omini_flg		=	$params['Jd_Omini_flg'];
			$bypass_autoAlloc	=	$params['bypass_autoAlloc'];
			if(isset($params['allocateToJdaClick'])){
				$allocateToJdaClick		=	$params['allocateToJdaClick']; // new variable declared here for Allocate to JDA
			}else{
				$allocateToJdaClick 	=	0;
			}
			$allocID = $params['team_type'];
			$login_city = $params['login_city'];
		}else{
			$graBFlag 	=	$_REQUEST['graBFlag'];
			$pincode 	=	$_REQUEST['pincode'];  
			$actiondate = 	$_REQUEST['actiondate'];
			$stVal		=	$_REQUEST['stVal'];
			$parentid	=	$_REQUEST['parentid'];
			$AllMeFlag	=	$_REQUEST['AllMeFlag'];
			$data_city	=	$_REQUEST['data_city'];
			$parentid	=	$_REQUEST['parentid'];
			$tme_code	=	$_REQUEST['tme_code'];
			$allocToME	=	$_REQUEST['allocToME'];
			$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
			$Grb_Normal_alt_add	=	$_REQUEST['Grb_Normal_alt_add'];
			$Jd_Omini_flg		=	$_REQUEST['Jd_Omini_flg'];
			$bypass_autoAlloc	=	$params['bypass_autoAlloc'];
			if(isset($params['allocateToJdaClick'])){
				$allocateToJdaClick		=	$params['allocateToJdaClick']; // new variable declared here for Allocate to JDA
			}else{
				$allocateToJdaClick 	=	0;
			}
			$allocID = $_REQUEST['team_type'];
			$login_city = $_REQUEST['login_city'];
		}
	/*
	 * code added here for handling date issue
	 * CHANGES DONE HERE AS FOR APPOINTMENT -FIX AND RE-FIX SHOULD +2 WEEKS ONLY
	 * Apoorv Agrawal
	*/	
	$timestring	=	$params['date'];
	$datetime	=	new DateTime($timestring);
	$datetime->format("Y-m-d");
	$datetime->getTimestamp();
	
	$curr_date_pri = new DateTime('now');
	
	$curr_date_pri->modify('+2 weeks');
	$curr_date_pri->modify('-1 day');
	$curr_date_pri->format("Y-m-d");
	$curr_date_pri->getTimestamp();
	
	$date_from_calender	=	strtotime($datetime->format("Y-m-d"));
	$date_from_server	=	strtotime($curr_date_pri->format("Y-m-d"));
	//~  code added here for handling date issue
	if(($date_from_calender > $date_from_server) && ($params['stVal'] == '25' || $params['stVal'] == '99')){
		$retArr['errorStatus']	=	"DIS";
		$retArr['errorCode']	=	1;
		return json_encode($retArr);die;
	}
	if($parentid == "" || empty($parentid)){
		$retArr['errorStatus']	=	"ParentId not Found";
		$retArr['errorCode']	=	0;
		echo json_encode($retArr);die();
	}
	if($actiondate == "") {
		$actiondate	=	date('Y-m-d',strtotime('+1 day'));
	}
	foreach ($time_array as $key => $value) {
		$retArr['data']['timeArr'][$value]	=	array();
	}
	$future_Date 	=	 date('Y/m/d',strtotime('+1 day'));
    if($pincode=='' && ($stVal == "25" || $stVal	==	"99")){
		$retArr['errorCode'] 	=	1;
		$retArr['errorStatus'] 	=	"Pincode not present";
		echo json_encode($retArr);
		exit;
	}
	if($stVal == "" || empty($stVal)){
		$retArr['errorCode'] 	=	1;
		$retArr['errorStatus'] 	=	"Disposition value not present";
		echo json_encode($retArr);
		exit;
	}
	/*Check For Categories*/
	if($stVal == "25" || $stVal	==	"99"){
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64'){
			$paramsarr['url']		=	SERVICE_IP."/transferInfo/fun_tbl_business_temp_data/";
		}else{
			$paramsarr['url']		=	SERVICE_IP."/transferInfo/fun_tbl_business_temp_data/";
		}
		$paramsarr['parentId']		=	$parentid;
		$paramsarr['formate'] 		= 	'basic';
		$paramsarr['method'] 		=	 'post';
		$paramsarr['headerJson'] 	=	 'json';
		$paramsarr['postData'] 		= 	json_encode($paramsarr);
		$tbl_temp_intermediate		=	Utility::curlCall($paramsarr);
		//echo $tbl_temp_intermediate; die();
		$tbl_temp_intermediate 		=	json_decode($tbl_temp_intermediate,true);	
		$retArr['tbl_temp_intermediate']['resp'] = $tbl_temp_intermediate;
		$retArr['tbl_temp_intermediate']['url'] = $paramsarr['url'];
		if($tbl_temp_intermediate['errorCode']	==1 || $tbl_temp_intermediate['data']['categories'] == ''){
			$retArr['errorCode'] 		=	1;
			$retArr['errorStatus'] 		=	"no_cat";
			$retArr['ME_NA']			=	0;
			$retArr['server_var']		=	$_SERVER;
			echo json_encode($retArr);
			exit;
		}
		$deal_close_jda = 0;
		$get_deal_close_arr = array();
		$get_deal_close_arr = json_decode($this->getLastDealClose($parentid,$data_city),true);
		if($get_deal_close_arr['error_code'] == 0){
			//~ $get_deal_close_arr[$parentid]['me_details']['Title'] = "JDA Customer Facing";
			$section = $get_deal_close_arr[$parentid]['me_details']['Title'];
			$section = trim(strtolower($section));
			//~ $get_deal_close_arr[$parentid]['me_details']['status'] = "InActive";
			if(isset($get_deal_close_arr[$parentid]['me_details']['empcode']) && (strtolower(trim($get_deal_close_arr[$parentid]['me_details']['status'])) == 'active' && strpos($section,'jda') !== false)){
				$server_addr_arr_new	= explode(".",$_SERVER['SERVER_ADDR']);
				// Hyderabad -- Main and Remote, Jaipur- Remote ,Chandigarh- Remote
				// Hyderabad -- Main Done
				
				if( ( isset($get_deal_close_arr[$parentid]['finance_info']['BALANCE_AMT']) && $get_deal_close_arr[$parentid]['finance_info']['BALANCE_AMT'] > 0 ) || 
					( isset($get_deal_close_arr[$parentid]['finance_info']['STATUS']) && strtolower($get_deal_close_arr[$parentid]['finance_info']['STATUS']) == 'active' )){
					if($server_addr_arr_new[2] == 8 || $server_addr_arr_new[2] == "50"){
						$allocateToJdaClick = 1;				
						$deal_close_me_code = $get_deal_close_arr[$parentid]['me_details']['empcode'];
						$deal_close_jda = 1;
					}
					if($server_addr_arr_new[2] == 17){
						if(strtolower($login_city) == 'chandigarh' || strtolower($login_city) == 'jaipur' || strtolower($login_city) == 'hyderabad' || strtolower($login_city) == 'delhi' || strtolower($login_city) == 'noida'){
							$allocateToJdaClick = 1;				
							$deal_close_me_code = $get_deal_close_arr[$parentid]['me_details']['empcode'];
							$deal_close_jda = 1;
						}
						
					}
				}
			}
		}
		$retArr['get_deal_close_arr'] = $get_deal_close_arr;
	}
	/*	Flag = 1; Disabled
	 *	Flag = 0; Enabled	  
	*/
	/*
	 * For JD OMINI Invite Fixed
	 */
	if($stVal	==	"317"){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$graBFlag 	=	$params['graBFlag'];
			$dateSend	=	$params['date'];
			$stVal		=	$params['stVal'];
			$tme_code	=	$params['tme_code'];
			$allocToME	=	$params['allocToME'];
			$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
		}else{
			$graBFlag 	=	$_REQUEST['graBFlag'];
			$dateSend 	=	$_REQUEST['date'];  
			$stVal		=	$_REQUEST['stVal'];
			$tme_code	=	$_REQUEST['tme_code'];
			$allocToME	=	$_REQUEST['allocToME'];
			$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
		}
		if($stVal == "" || empty($stVal)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Disposition value not present";
			echo json_encode($retArr);
			exit;
		}
		
		if($dateSend == "" || empty($dateSend)) {
			$dateSend	=	$actiondate;
		}
		$givenDate     		= 	date("Y-m-d", strtotime($dateSend));
		$currentTime 		=	date('H:i');
		$Currentdate 		= 	date('Y-m-d');
		$dbObjLocal			=	new DB($this->db['db_local']);//d_jds
		$timeSlots_qr		=	"SELECT timeSlots FROM d_jds.jdomini_timeslots;";
		$timeSlots_qr_con 	=	$dbObjLocal->query($timeSlots_qr);
		$rowgrabapptNum		= 	$dbObjLocal->numRows($timeSlots_qr_con);
		$time_slots_data 	=	$dbObjLocal->fetchData($timeSlots_qr_con);
		$jdomini_arr		=	array();
		$jdomini_arr		=	explode(',',$time_slots_data['timeSlots']);
		if($givenDate>$Currentdate){
			//enabled
			for ($i=0; $i <count($jdomini_arr) ; $i++) { 
				$retArrGrab[$jdomini_arr[$i]]	=	array("flag"=>'0');
			}
		}elseif($givenDate<$Currentdate){
			//disabled
			for ($i=0; $i <count($jdomini_arr) ; $i++) { 
				$retArrGrab[$jdomini_arr[$i]]	=	array("flag"=>'1');
				
			}
		}else{
			for ($i=0; $i <count($jdomini_arr) ; $i++) { 
				if( $jdomini_arr[$i] <	$currentTime){
					//disabled time
					$retArrGrab[$jdomini_arr[$i]]	=	array("flag"=>'1');
				}else{
					//enabled time
					$retArrGrab[$jdomini_arr[$i]]	=	array("flag"=>'0');
				}
			}
		}
			$retArr['data'] 		=	$retArrGrab;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"Data Found";
			$retArr['whichFlow'] 	=	"grab";
			$retArr['future_Date'] 	=	$future_Date;
			$retArr['givenDate']	=	$givenDate;
			$retArr['ME_NA']		=	0;
			echo json_encode($retArr);
			exit;	
		echo json_encode($jdomini_arr);die;
		
	}
	
	if($stVal == "24" || $stVal	==	"22"){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$graBFlag 	=	$params['graBFlag'];
			$dateSend	=	$params['date'];
			$stVal		=	$params['stVal'];
			$tme_code	=	$params['tme_code'];
			$allocToME	=	$params['allocToME'];
			$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
			$Jd_Omini_flg		=	$params['Jd_Omini_flg'];
		}else{
			$graBFlag 	=	$_REQUEST['graBFlag'];
			$dateSend 	=	$_REQUEST['date'];  
			$stVal		=	$_REQUEST['stVal'];
			$tme_code	=	$_REQUEST['tme_code'];
			$allocToME	=	$_REQUEST['allocToME'];
			$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
			$Jd_Omini_flg		=	$_REQUEST['Jd_Omini_flg'];
		}
		if($stVal == "" || empty($stVal)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Disposition value not present";
			echo json_encode($retArr);
			exit;
		}
		
		if($dateSend == "" || empty($dateSend)) {
			$dateSend	=	$actiondate;
		}
		$givenDate     		= 	date("Y-m-d", strtotime($dateSend));
		$currentTime 		=	date('H:i');
		$Currentdate 		= 	date('Y-m-d');
		if($givenDate>$Currentdate){
			//enabled
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'0');
			}
		}elseif($givenDate<$Currentdate){
			//disabled
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'1');
				
			}
		}else{
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				if( $followUp_Callback_arr[$i] <	$currentTime){
					//disabled time
					$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'1');
				}else{
					//enabled time
					$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'0');
				}
			}
		}
			$retArr['data'] 		=	$retArrGrab;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"Data Found";
			$retArr['whichFlow'] 	=	"grab";
			$retArr['future_Date'] 	=	$future_Date;
			$retArr['givenDate']	=	$givenDate;
			$retArr['ME_NA']		=	0;
			echo json_encode($retArr);
			exit;		
	}
	$db_tme		=	new DB($this->db['db_tme']);//tme_jds
	
	if(MONGOUSER == 1){
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $parentid;
		$mongo_inputs['data_city'] 	= SERVER_CITY;
		$mongo_inputs['module']		= 'tme';
		$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
		$mongo_inputs['fields'] 	= "contact_person";
		$contact_data = $this->mongo_obj->getData($mongo_inputs);
		$num_generalinfo_shadow = count($contact_data);
	}
	else
	{	
		$query_generalinfo_shadow 	= 	"SELECT contact_person FROM tbl_companymaster_generalinfo_shadow WHERE parentid='".$parentid."';";
		$conn_generalinfo_shadow 	=	$db_tme->query($query_generalinfo_shadow);
		$num_generalinfo_shadow		=	$db_tme->numRows($conn_generalinfo_shadow);
		$contact_data 				=	$db_tme->fetchData($conn_generalinfo_shadow);
	}
	if($num_generalinfo_shadow > 0) {
		//$contact_data 		=	$db_tme->fetchData($conn_generalinfo_shadow);
		if($contact_data['contact_person'] == '' || $contact_data['contact_person'] == 'null' || $contact_data['contact_person'] == null){
			$retArr['show_contact_pop_up']	=	1; // show the pop-up
		}else{
			$retArr['show_contact_pop_up']	=	0; // don't show the pop-up
		}
	} else {
		$retArr['show_contact_pop_up']	=	1; // show the pop-up
	}
	$dbObjLocal				=	new DB($this->db['db_local']);//d_jds
	$grabCkeck				=	"SELECT  COUNT(1) as count FROM tbl_grabapptPincode WHERE pincode='".$pincode."'";
	$grabchck_con 			=	$dbObjLocal->query($grabCkeck);
	$rowgrabapptNum			= 	$dbObjLocal->numRows($grabchck_con);
	$rowgrabapptPincode 	=	$dbObjLocal->fetchData($grabchck_con);
	if($rowgrabapptPincode['count']>0 || $Grb_Normal_alt_add == 2){
		$graBFlag 	= 	'1';
	}else{
		$graBFlag 	= 	'0';
	}
	/*
	 * condition added for Super Cad TME's
	 * Done By Apoorv Agrawal
	 * Requirment By Rohit Kaul Sir 
	 * Only for DELHI
	*/
	$retArr['superCad_flag']	=	0;
	$retArr['allocTOME']		=	0;// Allocate To ME options for eveery TME if Recored is present in tblContractAllocation
	$retArr['allME_DELHI']		=	1;
	$server_addr_arr	= explode(".",$_SERVER['SERVER_ADDR']);
	$automated_flg	=	0;
	$dbObjlocal		=	new DB($this->db['db_local']);
	$retArr['directiveFlag']	=	0;
	if(($stVal == "99" || $stVal	==	"25") && $AllMeFlag != 1){
		// explode and take 0/64
		$server_addr_arr	= explode(".",$_SERVER['SERVER_ADDR']);
		if($server_addr_arr[2] == "0" || $server_addr_arr[2] == "64"){ // Mumbai
			// Taking Appt live for all TME team In Mumbai
			$deal_close_jda = 0;
			if($bypass_autoAlloc == 0){
				$retArr['directiveFlag']	=	1;
				$automated_flg	=	1;
			}else{
				$retArr['directiveFlag']	=	0;
				$automated_flg	=	0;
			}
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
			//~ $automated_flg	=	1;
			//~ $retArr['directiveFlag']	=	1;
		}elseif($server_addr_arr[2] == "8"){ // Delhi
			/*New Conditon For Delhi Added 07-02-2017*/
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				/*New Changes Done Here for Allocate to JDA
				 * Removing Allocate To JDA option for Retention Team On 2017-11-20 Req. by Rohit Sir
				 * Providing Allocate To JDA option for Retention Team - RD ,Revival Expiry (BE) / On 2017-11-23 Req. by Rohit Sir
				*/
				if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					$retArr['superCad_flag']	=	1;
					$retArr['allME_DELHI']		=	1;
					//~ $retArr['allocateToJda']	=	1; // show the button
				}else{
					$retArr['superCad_flag']	=	0;
					$retArr['allME_DELHI']		=	1;
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				}
				$retArr['allocateToJda']	=	1; // show the button
				if($deal_close_jda == 1){
					$retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				}
				$alloc_to_ME_qr 	= 	"SELECT count(1) as alloc_rec FROM tblContractAllocation WHERE contractCode = '".$parentid."' AND parentCode='".$tme_code."' AND (allocationType = '25');";
				$con_alloc_to_ME_qr	=	$dbObjlocal->query($alloc_to_ME_qr);
				$res_alloc_to_ME_qr =	$dbObjlocal->fetchData($con_alloc_to_ME_qr);
				if($res_alloc_to_ME_qr['alloc_rec']>0){
					$retArr['allocTOME']	=	1;
				}else{
					$retArr['allocTOME']	=	0;
				}				
			}
		}elseif($server_addr_arr[2] == "26"){ // Bangalore
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			$deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				$automated_flg	=	0;
				$retArr['directiveFlag']	=	0;
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
		}elseif($server_addr_arr[2] == "32"){ // Chennai
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			$deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);				
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
		}elseif($server_addr_arr[2] == "40"){ // Pune
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			$deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
		}elseif($server_addr_arr[2] == "50"){ // Hyderabad
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			//~ $deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) == "BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				if($deal_close_jda == 1){
					$retArr['allocateToJda'] = 1;
				}
			}
		}elseif( $server_addr_arr[2] == "56" || $server_addr_arr[2] == "35" ){ // Ahemdabad
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			$deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
		}elseif($server_addr_arr[2] == "16"){ // Kolkata
			//$list_of_tme_auto_app
			//$dbObjlocal			=	new DB($this->db['db_local']);
			$deal_close_jda = 0;
			$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
			$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
			$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
			if($num_supr_cd_tme>0){
				$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
				if($bypass_autoAlloc == 0){
					$retArr['directiveFlag']	=	1;
					$automated_flg	=	1;
				}else{
					$retArr['directiveFlag']	=	0;
					$automated_flg	=	0;
				}
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
		}elseif($server_addr_arr[2] == "17"){ // Remote City
			$retArr['allocateToJda']	=	1;
			if(strtolower($login_city) == 'chandigarh'){
				$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				if($num_supr_cd_tme>0){
					$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
					if($bypass_autoAlloc == 0){
						$retArr['directiveFlag']	=	1;
						$automated_flg	=	1;
					}else{
						$retArr['directiveFlag']	=	0;
						$automated_flg	=	0;
					}
					//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
						//~ $retArr['allocateToJda']	=	1; // show the button
					//~ }else{
						//~ $retArr['allocateToJda']	=	0; // Don't show the button
					//~ }
					$retArr['allocateToJda']	=	1; // show the button
					//~ if($deal_close_jda == 1){
						//~ $retArr['allocateToJda'] = 1;
					//~ }
				}
			}elseif(strtolower($login_city) == 'jaipur'){
				$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				if($num_supr_cd_tme>0){
					$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
					if($bypass_autoAlloc == 0){
						$retArr['directiveFlag']	=	1;
						$automated_flg	=	1;
					}else{
						$retArr['directiveFlag']	=	0;
						$automated_flg	=	0;
					}
					//$retArr['allocateToJda']	=	1; // show the button
					// Uncommented as per the Requiremnt 2018-06-18
					//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) == "BD" || strtoupper($allocID) == "HD" || strtoupper($allocID) == "O" || strtoupper($allocID) == "S"){
						//~ $retArr['allocateToJda']	=	1; // show the button
					//~ }else{
						//~ $retArr['allocateToJda']	=	0; // Don't show the button
					//~ }
					$retArr['allocateToJda']	=	1; // show the button
					if($deal_close_jda == 1){
						$retArr['allocateToJda'] = 1;
					}
				}
			}elseif(strtolower($login_city) == 'delhi' || strtolower($login_city) == 'noida'){
				$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				if($num_supr_cd_tme>0){
					$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);					
					/*New Changes Done Here for Allocate to JDA*/
					if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
						$retArr['superCad_flag']	=	1;
						$retArr['allME_DELHI']		=	1;
						//~ $retArr['allocateToJda']	=	1; // show the button
					}else{
						$retArr['superCad_flag']	=	0;
						$retArr['allME_DELHI']		=	1;
						//~ $retArr['allocateToJda']	=	0; // Don't show the button
					}
					$alloc_to_ME_qr 	= 	"SELECT count(1) as alloc_rec FROM tblContractAllocation WHERE contractCode = '".$parentid."' AND parentCode='".$tme_code."' AND (allocationType = '25');";
					$con_alloc_to_ME_qr	=	$dbObjlocal->query($alloc_to_ME_qr);
					$res_alloc_to_ME_qr =	$dbObjlocal->fetchData($con_alloc_to_ME_qr);
					if($res_alloc_to_ME_qr['alloc_rec']>0){
						$retArr['allocTOME']	=	1;
					}else{
						$retArr['allocTOME']	=	0;
					}
					$retArr['allocateToJda']	=	1; // show the button
					if($deal_close_jda == 1){
						$retArr['allocateToJda'] = 1;
					}
				}
			}elseif(strtolower($login_city) == 'kolkata'){
				$deal_close_jda = 0;
				$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				if($num_supr_cd_tme>0){
					$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
					//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
						//~ $retArr['allocateToJda']	=	1; // show the button
					//~ }else{
						//~ $retArr['allocateToJda']	=	0; // Don't show the button
					//~ }				
				}
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}elseif(strtolower($login_city) == 'hyderabad'){
				$supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				$con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				$num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				if($num_supr_cd_tme>0){
					$res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
					//Providing Allocate To JDA option for Retention Team - RD ,Revival Expiry (BE) / On 2017-11-23 Req. by Rohit Sir BD Bounce Data , Hot Data, Online, Super as per new Rq. by Atul on 2018-06-05
					//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) == "BD" || strtoupper($allocID) == "HD" || strtoupper($allocID) == "O" || strtoupper($allocID) == "S"){
						//~ $retArr['allocateToJda'] = 1; // show the button
					//~ }else{
						//~ $retArr['allocateToJda'] = 0; // Don't show the button
					//~ }
					$retArr['allocateToJda']	=	1;
					if($deal_close_jda == 1){
						$retArr['allocateToJda'] = 1;
					}
				}
			}elseif(strtolower($login_city) == 'mumbai'){
				$deal_close_jda = 0;
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}elseif(strtolower($login_city) == 'pune'){
				$deal_close_jda = 0;
				//~ if(strtoupper($allocID) == "SJ" || strtoupper($allocID) == "RD" || strtoupper($allocID) == "BE" || strtoupper($allocID) =="BD"){
					//~ $retArr['allocateToJda']	=	1; // show the button
				//~ }else{
					//~ $retArr['allocateToJda']	=	0; // Don't show the button
				//~ }
				$retArr['allocateToJda']	=	1; // show the button
				//~ if($deal_close_jda == 1){
					//~ $retArr['allocateToJda'] = 1; // Condtion for Allowing allocate to JDA option for Renewal contracts
				//~ }
			}
			//~ elseif( strtolower($login_city) == 'coimbatore' ){
				//~ $supr_cd_tme_quer 	= 	"SELECT allocID FROM mktgEmpMaster WHERE mktEmpCode = '".$tme_code."'";
				//~ $con_supr_cd_tme	=	$dbObjlocal->query($supr_cd_tme_quer); 
				//~ $num_supr_cd_tme	=	$dbObjlocal->numRows($con_supr_cd_tme);
				//~ if($num_supr_cd_tme>0){
					//~ $res_supr_cd_tme 	=	$dbObjlocal->fetchData($con_supr_cd_tme);
					//~ if(strtoupper($res_supr_cd_tme['allocID']) == "SJ" || strtoupper($res_supr_cd_tme['allocID']) == "RD" || strtoupper($res_supr_cd_tme['allocID']) == "BE"){
						//~ $retArr['allocateToJda']	=	1; // show the button
					//~ }else{
						//~ $retArr['allocateToJda']	=	0; // Don't show the button
					//~ }
				//~ }
				//~ if($bypass_autoAlloc == 0){
					//~ $retArr['directiveFlag']	=	1;
					//~ $automated_flg	=	1;
				//~ }else{
					//~ $retArr['directiveFlag']	=	0;
					//~ $automated_flg	=	0;
				//~ }
			//~ }
		}
	}
	$retArr['deal_close_jda'] = $deal_close_jda;
	/*Very Very Imp Condition*/
	if($allocateToJdaClick == 1){
		$retArr[automated_flg]	=	0;
	}else{
		$retArr[automated_flg]	=	$automated_flg;
	}
	/*This will Solve All The prob*/
	
	if($automated_flg	==	1 && $allocateToJdaClick == 0){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		if(!$urlFlag){
			$graBFlag 	=	$params['graBFlag'];
			$dateSend	=	$params['date'];
			$stVal		=	$params['stVal'];
			$pincode	=	$params['pincode'];
			$AllMeFlag	=	$params['AllMeFlag'];
			$data_city	=	$params['data_city'];
			$tme_code	=	$params['tme_code'];
			$allocToME	=	$params['allocToME'];
			$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
			$Grb_Normal_alt_add	=	$params['Grb_Normal_alt_add'];
			$Jd_Omini_flg		=	$params['Jd_Omini_flg'];
			
		}else{
			$graBFlag 	=	$_REQUEST['graBFlag'];
			$dateSend 	=	$_REQUEST['date'];  
			$stVal		=	$_REQUEST['stVal'];
			$pincode	=	$_REQUEST['pincode'];
			$AllMeFlag	=	$_REQUEST['AllMeFlag'];
			$data_city	=	$_REQUEST['data_city'];
			$tme_code	=	$_REQUEST['tme_code'];
			$allocToME	=	$_REQUEST['allocToME'];
			$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
			$Grb_Normal_alt_add	=	$_REQUEST['Grb_Normal_alt_add'];
			$Jd_Omini_flg		=	$_REQUEST['Jd_Omini_flg'];
		}
		if($dateSend == "" || empty($dateSend)) {
			$actiondate	=	$actiondate;
		}else{
			$actiondate			= 	date("Y-m-d", strtotime($dateSend));
			$currentTime 		=	date('H:i');
			$Currentdate 		= 	date('Y-m-d');
		}
		if($pincode=='' || empty($pincode) && ($stVal == "25" || $stVal	==	"99")){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Pincode not present";
			echo json_encode($retArr);
			exit;
		}
		if(!isset($params['date']) || $params['date'] == '') {
			$actiondate			=	$actiondate;
			$currentTime 		=	date('H:i');
			$Currentdate 		= 	date('Y-m-d');
			$givenDate     		= 	date('Y-m-d',strtotime('+1 day'));
		}else{
			$actiondate			= 	date("Y-m-d", strtotime($dateSend));
			$givenDate     		= 	date('Y-m-d',strtotime($dateSend));
			$currentTime 		=	date('H:i');
			$Currentdate 		= 	date('Y-m-d');
		}
		$tme_data	=	array();
		$me_data	=	array();
		$tme_data	=	json_decode($this->get_TME_rank($tme_code,$login_city),true);		
		if($tme_data['cumulative_rank_city_tme'] == null || $tme_data['cumulative_rank_city_tme'] == ''){
			$retArr['TMERANK']	=	10000000;
		}else{
			$retArr['TMERANK']	=	$tme_data['cumulative_rank_city_tme'];
		}
		$retArr['tme_data']	=	$tme_data;
		$retArr['TOTALTME']	=	$tme_data['TOTALTME'];
		$me_data	=	json_decode($this->get_ME_Total($pincode),true);
		if($me_data['errorCode']	==	0){
			$retArr['me_data']			=	$me_data;
			$retArr['errorCode_me']		=	0;
			$retArr['errorStatus_me']	=	"DF";
		}else{
			$retArr['errorCode_me']		=	1;
			$retArr['errorStatus_me']	=	"DNF";
		}
		if($retArr['TMERANK']	==	10000000){
			$elgbility	=	10000000;
		}else{
			if(intval($retArr['tme_data']['TOTALTME']) > 0){
				$retArr['tot_tme_check'] = 'in if';
				$elgbility	=	($retArr['tme_data']['cumulative_rank_city_tme']/$retArr['tme_data']['TOTALTME'])*$retArr['me_data']['count_pincode_wise_me'];
			}else{
				$retArr['tot_tme_check'] = 'in else';
				$elgbility	=	10000000;
			}
			//~ $elgbility	=	($retArr['tme_data']['cumulative_rank_city_tme']/$retArr['tme_data']['TOTALTME'])*$retArr['me_data']['count_pincode_wise_me'];
		}
		if(is_numeric($elgbility)){
			$elgbility	=	round($elgbility);
		}else{
			$elgbility	=	10000000;
		}
		if($elgbility == 0){
			$retArr['elgbility']	=	1;
		}else{
			$retArr['elgbility']	=	$elgbility;
		}
		$listOfME		=	array();
		$listofbusyME	=	array();
		foreach($retArr['me_data']['LISTOFME'] as $key=>$val){
			$list_of_me_code_pinwise	.=	$val['mktEmpCode']."','";
			$listOfME[]	=	$val['mktEmpCode'];
		}
		/*New Code Added Here*/
		$listOfME_str	=	'';
		$listOfME_str	= 	implode("','",$listOfME);	
		$listOf_absent_time_slot	=	array();
		$dbObjIDC		=	new DB($this->db['db_idc']);
		if($listOfME_str != ''){
			$absent_me_query	=	"SELECT empcode,absent_on,time_slot_tme FROM tbl_meabsentdetails WHERE empcode IN ('".$listOfME_str."') AND absent_on = '".$givenDate."'";
			$con_absent_me		=	$dbObjIDC->query($absent_me_query); 
			$num_absent_me		=	$dbObjIDC->numRows($con_absent_me);
			if($num_absent_me > 0){
				while($data_absent_me	=	$dbObjIDC->fetchData($con_absent_me)) {
					if($data_absent_me['time_slot_tme'] != ''){
						//~ echo "here";
						$time_slot				=	explode("-",$data_absent_me['time_slot_tme']);
						$time_slot_start		=	$time_slot[0];
						$time_slot_end			=	$time_slot[1];
						$time_slot_start_block	=	$time_slot[0];
						$time_slot_end_block	=	$time_slot[1];
						$hourdiff 				= 	round((strtotime($time_slot_end) - strtotime($time_slot_start))/3600);
						$hourdiff				=	$hourdiff*2;
						for($i=0;$i<$hourdiff;$i++){
							if(strtotime($time_slot_start_block) <= strtotime($time_slot_end)){
								$listOf_absent_time_slot[$data_absent_me['empcode']][]	=	$time_slot_start_block;
								$time_slot_start_block = date("H:i", strtotime('+30 minutes', strtotime($time_slot_start_block)));
							}
						}
						$listOf_absent_time_slot[$data_absent_me['empcode']][]	=	$time_slot_end;
						$listOf_absent_time_slot[$data_absent_me['empcode']]	=	array_unique($listOf_absent_time_slot[$data_absent_me['empcode']]);
					}elseif($data_absent_me['time_slot_tme'] == '' || $data_absent_me['time_slot_tme'] == null || $data_absent_me['time_slot_tme'] == 'null'){
						// consider ME absent for whole day i.e remove this ME from the list itself
						$listOf_absent_time_slot[$data_absent_me['empcode']] = '';
					}
				}
			}
		}
		if(!empty($listOf_absent_time_slot)){
			$retArr['listOf_absent_time_slot']	=	$listOf_absent_time_slot;
			foreach($listOf_absent_time_slot as $key=>$data){
				if($data == ''){
					// absent for full day
					if (in_array($key, $listOfME)){
						$pos = array_search($key, $listOfME);
						unset($listOfME[$pos]);
					}
				}else{
					if(count($listOf_absent_time_slot[$key]) == count($followUp_Callback_arr)){
						// absent for full day
						if (in_array($key, $listOfME)){
							$pos = array_search($key, $listOfME);
							unset($listOfME[$pos]);
						}
					}else{
						// absent for time slot
						foreach($data as $k=>$d){
							$listOfbusyMe[$d][]	=	$key;
						}
					}
				}
			}
		}else{
			$retArr['listOf_absent_time_slot']	=	'';
		}
		/*New Code Added Here*/
		$list_of_me_code_pinwise	=	"empcode IN	('" . $list_of_me_code_pinwise . "')";
		$con_local	=	new DB($this->db['db_local']);
		if($givenDate>$Currentdate){
			//enabled
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) {
				$timestamp_plus = strtotime(''.$followUp_Callback_arr[$i].'') + 60*60;
				$time30Min_plus = date('H:i:s', $timestamp_plus);
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
					$time30Min_plus	=	'23:59:59';
				}
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				/*Calculating half hour before*/
				$timestamp_back = strtotime(''.$followUp_Callback_arr[$i].'') - 60*60;
				$time30Min_back = date('H:i', $timestamp_back);
			}
			$listOfME_str = '';
			if(!empty($listOfME)){
				$listOfME_str = implode("','",$listOfME);
			}
			if($listOfME_str != ''){
				$sel_me_code = "SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empcode IN ('".$listOfME_str."') AND actionTime >= '".$givenDate." 00:00:00' AND actionTime <= '".$givenDate." 23:59:59' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime DESC";					
				$data_me_tim_con = $con_local->query($sel_me_code);
				$get_absentME_qur_num = $con_local->numRows($data_me_tim_con);
				if($get_absentME_qur_num > 0){
					while($data = $con_local->fetchData($data_me_tim_con)){						
						$time_slot_arr_n = explode(' ',$data['actionTime']);
						$time_slot_str = '';
						$time_slot_str = $time_slot_arr_n[1];
						$time_slot_str = substr($time_slot_str, 0,-3);
						
						$time_slot_str_bck_thrty = strtotime($time_slot_str) - 30*60;
						$time_slot_str_bck_thrty = date('H:i', $time_slot_str_bck_thrty);						
						$time_slot_str_bck_onehr = strtotime($time_slot_str_bck_thrty) - 30*60;
						$time_slot_str_bck_onehr = date('H:i', $time_slot_str_bck_onehr);
						
						
						$time_slot_str_fwd_thrty = strtotime($time_slot_str) + 30*60;
						$time_slot_str_fwd_thrty = date('H:i', $time_slot_str_fwd_thrty);						
						$time_slot_str_fwd_onehr = strtotime($time_slot_str_fwd_thrty) + 30*60;
						$time_slot_str_fwd_onehr = date('H:i', $time_slot_str_fwd_onehr);
						
						$listOfbusyMe[$time_slot_str][] = $data['empCode'];						
						
						$listOfbusyMe[$time_slot_str_fwd_thrty][] = $data['empCode'];
						$listOfbusyMe[$time_slot_str_fwd_onehr][] = $data['empCode'];
						
						$listOfbusyMe[$time_slot_str_bck_thrty][] = $data['empCode'];
						$listOfbusyMe[$time_slot_str_bck_onehr][] = $data['empCode'];
					}
					foreach($listOfbusyMe as $key=>$data){
						$listOfbusyMe[$key] = array_unique($data);
					}
				}else{
					//$retArrGrab[$followUp_Callback_arr[$i]]	= array("flag"=>'0');
					$retArr['follow_up']['busy'] = 2; // 2 - FREE means that follow-up ME is FREE
					$retArr['follow_up']['busy_free_status'] = 'Free'; // 2 - FREE means that follow-up ME is FREE
				}
				//enabled time
				/* Changes Made Here by Apoorv as per the new Requirment block timing if ME marked as followUp or Appt Refix Start */
				//~ $sel_me_code_idc = "SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empcode IN ('".$listOfME_str."') AND actionTime >= '".$givenDate." 00:00:00' AND actionTime <= '".$givenDate." 23:59:59' AND allocationType IN ('35','36') AND cancel_flag=0 ORDER BY actionTime DESC";
				//~ $data_me_tim_con_idc = $dbObjIDC->query($sel_me_code_idc);
				//~ $get_absentME_qur_num_idc = $dbObjIDC->numRows($data_me_tim_con_idc);
				//~ if($get_absentME_qur_num_idc > 0){
					//~ while($data = $dbObjIDC->fetchData($data_me_tim_con_idc)){
						//~ $time_slot_arr_n = explode(' ',$data['actionTime']);
						//~ $time_slot_str = '';
						//~ $time_slot_str = $time_slot_arr_n[1];
						//~ $time_slot_str = substr($time_slot_str, 0,-3);
						
						//~ $time_slot_str_bck_thrty = strtotime($time_slot_str) - 30*60;
						//~ $time_slot_str_bck_thrty = date('H:i', $time_slot_str_bck_thrty);						
						//~ $time_slot_str_bck_onehr = strtotime($time_slot_str_bck_thrty) - 30*60;
						//~ $time_slot_str_bck_onehr = date('H:i', $time_slot_str_bck_onehr);
						
						
						//~ $time_slot_str_fwd_thrty = strtotime($time_slot_str) + 30*60;
						//~ $time_slot_str_fwd_thrty = date('H:i', $time_slot_str_fwd_thrty);						
						//~ $time_slot_str_fwd_onehr = strtotime($time_slot_str_fwd_thrty) + 30*60;
						//~ $time_slot_str_fwd_onehr = date('H:i', $time_slot_str_fwd_onehr);
						
						//~ $listOfbusyMe[$time_slot_str][] = $data['empCode'];						
						
						//~ $listOfbusyMe[$time_slot_str_fwd_thrty][] = $data['empCode'];
						//~ $listOfbusyMe[$time_slot_str_fwd_onehr][] = $data['empCode'];
						
						//~ $listOfbusyMe[$time_slot_str_bck_thrty][] = $data['empCode'];
						//~ $listOfbusyMe[$time_slot_str_bck_onehr][] = $data['empCode'];
						$listOfbusyMe[$data['actionTime']][] = $data['empCode'];
					//~ }
					//~ foreach($listOfbusyMe as $key=>$data){
						//~ $listOfbusyMe[$key] = array_unique($data);
					//~ }						
				//~ }else{					
					//~ $retArr['follow_up']['busy'] = 2; 		// 2 - FREE means that follow-up ME is FREE
					//~ $retArr['follow_up']['busy_free_status'] = 'Free'; // 2 - FREE means that follow-up ME is FREE					
				//~ }
				/* Changes Made Here by Apoorv as per the new Requirment block timing if ME marked as followUp or Appt Refix End */					
			}
			foreach($followUp_Callback_arr as $key=>$data_time){
				if(isset($listOfbusyMe[$data_time]) && count($listOfbusyMe[$data_time]) == count($listOfME)){
					$retArrGrab[$data_time]	=	array("flag"=>'3');
					$retArr['follow_up']['busy']	=	1; 		// 1 - BUSY means that follow-up ME is BUSY
					$retArr['follow_up']['busy_free_status']	=	'Busy'; // 1 - BUSY means that follow-up ME is BUSY
				}else{
					$retArrGrab[$data_time]	=	array("flag"=>'0');
				}
			}
		}elseif($givenDate<$Currentdate){
			//disabled
			//echo "2nd if";
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'1');				
			}
		}else{
			//echo "3rd if";
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) {
				$timestamp_plus = strtotime(''.$followUp_Callback_arr[$i].'') + 60*60;
				$time30Min_plus = date('H:i:s', $timestamp_plus);
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				if($time30Min_plus == '00:00:00' || $time30Min_plus == '00:30:00'){
					$time30Min_plus	=	'23:59:59';
				}
				// changes made here by Apoorv Agrawal - today (27-01-2017)
				/*Calculating half hour before*/
				$timestamp_back = strtotime(''.$followUp_Callback_arr[$i].'') - 60*60;
				$time30Min_back = date('H:i', $timestamp_back);
			}
			$listOfME_str = '';
			if(!empty($listOfME)){
				$listOfME_str = implode("','",$listOfME);
			}
			if($listOfME_str != ''){
				$sel_me_code = "SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empcode IN ('".$listOfME_str."') AND actionTime >= '".$givenDate." 00:00:00' AND actionTime <= '".$givenDate." 23:59:59' AND allocationType IN ('25','99') AND cancel_flag=0 ORDER BY actionTime DESC";				
				$data_me_tim_con = $con_local->query($sel_me_code);
				$get_absentME_qur_num = $con_local->numRows($data_me_tim_con);
				if($get_absentME_qur_num > 0){
					while($data = $con_local->fetchData($data_me_tim_con)){						
						$time_slot_arr_n = explode(' ',$data['actionTime']);
						$time_slot_str = '';
						$time_slot_str = $time_slot_arr_n[1];
						$time_slot_str = substr($time_slot_str, 0,-3);
						
						$time_slot_str_bck_thrty = strtotime($time_slot_str) - 30*60;
						$time_slot_str_bck_thrty = date('H:i', $time_slot_str_bck_thrty);						
						$time_slot_str_bck_onehr = strtotime($time_slot_str_bck_thrty) - 30*60;
						$time_slot_str_bck_onehr = date('H:i', $time_slot_str_bck_onehr);
						
						
						$time_slot_str_fwd_thrty = strtotime($time_slot_str) + 30*60;
						$time_slot_str_fwd_thrty = date('H:i', $time_slot_str_fwd_thrty);						
						$time_slot_str_fwd_onehr = strtotime($time_slot_str_fwd_thrty) + 30*60;
						$time_slot_str_fwd_onehr = date('H:i', $time_slot_str_fwd_onehr);
						
						$listOfbusyMe[$time_slot_str][] = $data['empCode'];						
						
						$listOfbusyMe[$time_slot_str_fwd_thrty][] = $data['empCode'];
						$listOfbusyMe[$time_slot_str_fwd_onehr][] = $data['empCode'];
						
						$listOfbusyMe[$time_slot_str_bck_thrty][] = $data['empCode'];
						$listOfbusyMe[$time_slot_str_bck_onehr][] = $data['empCode'];
					}
					foreach($listOfbusyMe as $key=>$data){
						$listOfbusyMe[$key] = array_unique($data);
					}
				}else{
					//$retArrGrab[$followUp_Callback_arr[$i]]	= array("flag"=>'0');
					$retArr['follow_up']['busy'] = 2; // 2 - FREE means that follow-up ME is FREE
					$retArr['follow_up']['busy_free_status'] = 'Free'; // 2 - FREE means that follow-up ME is FREE
				}
				//enabled time
				/* Changes Made Here by Apoorv as per the new Requirment block timing if ME marked as followUp or Appt Refix Start */
				//~ $sel_me_code_idc = "SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empcode IN ('".$listOfME_str."') AND actionTime >= '".$givenDate." 00:00:00' AND actionTime <= '".$givenDate." 23:59:59' AND allocationType IN ('35','36') AND cancel_flag=0 ORDER BY actionTime DESC";
				//~ $data_me_tim_con_idc = $dbObjIDC->query($sel_me_code_idc);
				//~ $get_absentME_qur_num_idc = $dbObjIDC->numRows($data_me_tim_con_idc);
				//~ if($get_absentME_qur_num_idc > 0){
					//~ while($data = $dbObjIDC->fetchData($data_me_tim_con_idc)){
						//~ $time_slot_arr_n = explode(' ',$data['actionTime']);
						//~ $time_slot_str = '';
						//~ $time_slot_str = $time_slot_arr_n[1];
						//~ $time_slot_str = substr($time_slot_str, 0,-3);
						
						//~ $time_slot_str_bck_thrty = strtotime($time_slot_str) - 30*60;
						//~ $time_slot_str_bck_thrty = date('H:i', $time_slot_str_bck_thrty);						
						//~ $time_slot_str_bck_onehr = strtotime($time_slot_str_bck_thrty) - 30*60;
						//~ $time_slot_str_bck_onehr = date('H:i', $time_slot_str_bck_onehr);
						
						
						//~ $time_slot_str_fwd_thrty = strtotime($time_slot_str) + 30*60;
						//~ $time_slot_str_fwd_thrty = date('H:i', $time_slot_str_fwd_thrty);						
						//~ $time_slot_str_fwd_onehr = strtotime($time_slot_str_fwd_thrty) + 30*60;
						//~ $time_slot_str_fwd_onehr = date('H:i', $time_slot_str_fwd_onehr);
						
						//~ $listOfbusyMe[$time_slot_str][] = $data['empCode'];						
						
						//~ $listOfbusyMe[$time_slot_str_fwd_thrty][] = $data['empCode'];
						//~ $listOfbusyMe[$time_slot_str_fwd_onehr][] = $data['empCode'];
						
						//~ $listOfbusyMe[$time_slot_str_bck_thrty][] = $data['empCode'];
						//~ $listOfbusyMe[$time_slot_str_bck_onehr][] = $data['empCode'];
						$listOfbusyMe[$data['actionTime']][] = $data['empCode'];
					//~ }
					//~ foreach($listOfbusyMe as $key=>$data){
						//~ $listOfbusyMe[$key] = array_unique($data);
					//~ }
				//~ }else{
					
						//~ $retArr['follow_up']['busy'] = 2; 		// 2 - FREE means that follow-up ME is FREE
						//~ $retArr['follow_up']['busy_free_status'] = 'Free'; // 2 - FREE means that follow-up ME is FREE
					
				//~ }
				/* Changes Made Here by Apoorv as per the new Requirment block timing if ME marked as followUp or Appt Refix End */					
			}
			foreach($followUp_Callback_arr as $key=>$data_time){
				if(isset($listOfbusyMe[$data_time]) && count($listOfbusyMe[$data_time]) == count($listOfME)){
					$retArrGrab[$data_time]	= array("flag"=>'3');
					$retArr['follow_up']['busy'] = 1; 		// 1 - BUSY means that follow-up ME is BUSY
					$retArr['follow_up']['busy_free_status'] = 'Busy'; // 1 - BUSY means that follow-up ME is BUSY
				}else{
					if($data_time < $currentTime){
						//disabled time
						$retArrGrab[$data_time]	= array("flag"=>'1');
					}else{
						$retArrGrab[$data_time]	=	array("flag"=>'0');
					}
				}
			}
		}
		$retArr['data'] 			=	$retArrGrab;
		//~ $retArr['data']				=	$time_array;
		$retArr['errorCode'] 		=	0;
		$retArr['errorStatus'] 		=	"Data found";
		$retArr['whichFlow'] 		=	"normal";
		$retArr['future_Date'] 		=	$future_Date;
		$retArr['dateSend'] 		=	$dateSend;
		$retArr['givenDate'] 		=	$givenDate;
		$retArr['allME']			=	1;
		$retArr['ME_NA']			=	0;
		//echo json_encode($retArr);		
		return json_encode($retArr);
	}else{
		if(($graBFlag=='0'|| $AllMeFlag == 1) || ($allocToME == 1) || ($alloc_to_ME_TME	== 1) || $allocateToJdaClick == 1){
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
			if(!$urlFlag){
				$graBFlag 	=	$params['graBFlag'];
				$dateSend	=	$params['date'];
				$stVal		=	$params['stVal'];
				$pincode	=	$params['pincode'];
				$AllMeFlag	=	$params['AllMeFlag'];
				$data_city	=	$params['data_city'];
				$tme_code	=	$params['tme_code'];
				$allocToME	=	$params['allocToME'];
				$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
				$Grb_Normal_alt_add	=	$params['Grb_Normal_alt_add'];
				$Jd_Omini_flg		=	$params['Jd_Omini_flg'];
				
			}else{
				$graBFlag 	=	$_REQUEST['graBFlag'];
				$dateSend 	=	$_REQUEST['date'];  
				$stVal		=	$_REQUEST['stVal'];
				$pincode	=	$_REQUEST['pincode'];
				$AllMeFlag	=	$_REQUEST['AllMeFlag'];
				$data_city	=	$_REQUEST['data_city'];
				$tme_code	=	$_REQUEST['tme_code'];
				$allocToME	=	$_REQUEST['allocToME'];
				$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
				$Grb_Normal_alt_add	=	$_REQUEST['Grb_Normal_alt_add'];
				$Jd_Omini_flg		=	$_REQUEST['Jd_Omini_flg'];
			}
			if($dateSend == "" || empty($dateSend)) {
				$actiondate	=	$actiondate;
			}else{
				$actiondate			= 	date("Y-m-d", strtotime($dateSend));
				$currentTime 		=	date('H:i');
				$Currentdate 		= 	date('Y-m-d');
			}
			if($pincode=='' || empty($pincode) && ($stVal == "25" || $stVal	==	"99")){
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Pincode not present";
				echo json_encode($retArr);
				exit;
			}
			if($pincode!=''){
				$me_final_arr 	= 	array();
				$retArr_me		=	array();
				$dbObjlocal		=	new DB($this->db['db_local']);
				/*Condition Added to get All me Data based on $AllMeFlag*/
				if(($AllMeFlag==1)){
					$sql 		=	"SELECT pincode FROM tbl_areamaster_consolidated_v3 WHERE data_city ='".$data_city."' and type_flag=1 AND display_flag=1";
					$res 		= 	$dbObjlocal->query($sql);	
					$rowData 	= 	$dbObjlocal->fetchData($res);
					$pncde_arr	=	array();
					if($dbObjlocal->numRows($res)	>	0 	&& 	$rowData!=null){
						while($row 	= 	$dbObjlocal->fetchData($res)){
							$pincode_implode.= 	$row['pincode']."','";
						}	
					}
					$condition	=	"pincode IN	('" . $pincode_implode . "')";
				}else{
					if(($allocToME == 0) || ($alloc_to_ME_TME == 1) || ($AllMeFlag == 0)){
						$condition 	= "pincode	=	'" . $pincode . "'";
					}
				}
				
				
				/*************************Add assigned ME to TME*************************/
				if($allocateToJdaClick	== 0){
					$dbObjlocal		=	new DB($this->db['db_local']);
					$sel_assigned_me 	= "SELECT COUNT(DISTINCT empcode) as cnt FROM tbl_me_allocation_final WHERE empType=5 AND (empcode !='' AND empcode IS NOT NULL) AND empcode = '".$tme_code."' AND assigned_me!='' AND assigned_flag=1 AND  match(assigned_pincode) against('".$pincode."' in boolean mode);";
					$con_assignedme		=	$dbObjlocal->query($sel_assigned_me); 
					$num_assigned		=	$dbObjlocal->numRows($con_assignedme);
					$row_assigned		=	$dbObjlocal->fetchData($con_assignedme);
					if($row_assigned['cnt'] == 1 && $AllMeFlag != 1)
					{
						//~ $sel_me_final = "SELECT assigned_me as ME FROM tbl_me_allocation_final WHERE empcode ='".$tme_code."' AND approval_flag = 1 AND (empcode !='' AND empcode IS NOT NULL) AND  match(assigned_pincode) against('".$pincode."' in boolean mode);";
						$sel_me_final = "SELECT empcode as ME FROM tbl_me_allocation_final where ".$condition." AND approval_flag = 1 AND (empcode !='' AND empcode IS NOT NULL);";
					}
					else
					{
						$sel_me_final = "SELECT empcode as ME FROM tbl_me_allocation_final where ".$condition." AND approval_flag = 1 AND (empcode !='' AND empcode IS NOT NULL);";
					}
					$con_me		=	$dbObjlocal->query($sel_me_final); 
					$num_me		=	$dbObjlocal->numRows($con_me);
				}elseif($allocateToJdaClick	== 1){
					// Call Ronaks API for getting JDA data
					$jda_details_arr = array();
					$jda_details_arr['pincode'] = $pincode;
					$JDAData_resp = $this->getJDAAPI($jda_details_arr);
					if( empty($JDAData_resp) || count($JDAData_resp) == 0 ){
						$num_me = 0;
					}else{
						$num_me = count($JDAData_resp);
					}
					//~ $dbObjlocal		=	new DB($this->db['db_idc']);
					//~ $get_jda_qur	=	"SELECT mktEmpCode as ME FROM login_details.tbl_loginDetails WHERE MATCH(jda_areas) AGAINST('".$pincode."' IN BOOLEAN MODE) AND approval_flag=1 AND empType=13";
					//~ $con_me		=	$dbObjlocal->query($get_jda_qur); 
					//~ $num_me		=	$dbObjlocal->numRows($con_me);
				}
				if($num_me>0){
					$i		=	0;
					$strEmp	=	"";
					if( $allocateToJdaClick	== 1 ){
						
						foreach( $JDAData_resp['ME'] as $key=>$data ){
							$retArr_me['data'][] = $data;
							$strEmp .=	$data."','";
							$i++;
						}
					}else{
						while($data	=	$dbObjlocal->fetchData($con_me)) {
							$retArr_me['data'][]	=	$data['ME'];
							$strEmp .=	$data['ME']."','";
							$i++;
						}
					}
					/*
					 * Changes Made to display JDOMIMI ME list
					 * and it's empType is 14
					*/
					if($Jd_Omini_flg == 1 && isset($Jd_Omini_flg)){
						$Jd_Omini_empType	=	14;
					}else{
						$Jd_Omini_empType	=	3;
					}
					$strEmp		    =   rtrim($strEmp, ',');
					$listOfAbsentME	=	'';
					$dbObjIDC			=	new DB($this->db['db_idc']);
					$absent_me_query	=	"SELECT empcode,absent_on,time_slot_tme FROM tbl_meabsentdetails WHERE empcode IN ('".$strEmp."') AND absent_on = '".$actiondate."'";
					$con_absent_me		=	$dbObjIDC->query($absent_me_query); 
					$num_absent_me		=	$dbObjIDC->numRows($con_absent_me);
					if($num_absent_me > 0){
						while($data_absent_me	=	$dbObjIDC->fetchData($con_absent_me)) {
							if($data_absent_me['time_slot_tme'] == '' || $data_absent_me['time_slot_tme'] == null || $data_absent_me['time_slot_tme'] == 'null'){
								if(in_array($data_absent_me['empcode'],$retArr_me['data'])){
									$posOfElement = array_search($data_absent_me['empcode'], $retArr_me['data']);
									unset($retArr_me['data'][$posOfElement]);
								}
								$listOfAbsentME		.=	$data_absent_me['empcode']."','";
							}
						}
						$listOfAbsentME		    =   rtrim($listOfAbsentME, ',');
					}
					if($listOfAbsentME !=''){
						$strEmp	=	'';
						foreach($retArr_me['data'] as $key=>$data_me){
							$strEmp	.=	$data_me."','";
						}
						$strEmp		    =   rtrim($strEmp, ',');
					}
					if($allocateToJdaClick	== 1){
						$dbObjlocal		=	new DB($this->db['db_idc']);
						$get_jda_qur	=	"SELECT employee_id as mktEmpCode,employee_name as empName, employee_mobile as mobile FROM db_dialer.tbl_employee_lineage WHERE employee_id IN('".$strEmp."')";
						$con_me_chk		=	$dbObjlocal->query($get_jda_qur); 
						$num_me_chk		=	$dbObjlocal->numRows($con_me_chk);
					}else{
						$dbObjlocal		=	new DB($this->db['db_local']);					
						$sqlSelect 		= 	"SELECT a.mktEmpCode, a.empName, a.mobile,a.allocID,if(b.cumulative_rank_city is null,'NA',b.cumulative_rank_city) as cumulative_rank_city FROM mktgEmpMaster As a LEFT JOIN d_jds.tbl_ranking_me_final AS b ON a.mktEmpCode = b.empcode WHERE a.mktEmpCode IN('".$strEmp."') AND a.mktEmpCode NOT IN('018916','000108') AND a.emptype = '3' AND a.Approval_flag = '1' ORDER BY b.cumulative_rank_city;";
						$con_me_chk		=	$dbObjlocal->query($sqlSelect); 
						$num_me_chk		=	$dbObjlocal->numRows($con_me_chk);
					}
						if($num_me_chk>0) {
							$i=0;
							while($data_chk	=	$dbObjlocal->fetchData($con_me_chk)) {
								if(empty($data_chk['mobile']) || $data_chk['mobile']==''){
									$data_chk['mobile']	=	'Number NotAvl';
								}
								if($remote_flg	==	1){
									if(empty($data_chk['mobile']) || $data_chk['mobile']==''){
										$data_chk['mobile']	=	'Number NA';
									}else{
										$data_chk['mobile']	=	'0'.$data_chk['mobile'];
									}
								}
								//
								$retArr['data']['meInfo'][]	=	$data_chk;
								$checkStr	.=	$data_chk['mktEmpCode']."','";
								
							}							
				
							
							if($allocateToJdaClick	== 1 && ($server_addr_arr[2] == "8" || $server_addr_arr[2] == "50")){
								$f_str_me_list = "'$checkStr'";						
								if (strpos($f_str_me_list, $deal_close_me_code) == false && $deal_close_me_code != "") {
									$checkStr.= $deal_close_me_code."','";
									$arra_to_ins = array('mktEmpCode'=>$get_deal_close_arr[$parentid]['me_details']['empcode'],"empName"=>$get_deal_close_arr[$parentid]['me_details']['empname'],"mobile"=>$get_deal_close_arr[$parentid]['me_details']['mobile']);
									array_push($retArr['data']['meInfo'],$arra_to_ins);
								}
								$pos_of_deal_close = 0;
								foreach($retArr['data']['meInfo'] as $key=>$data){
									if($data['mktEmpCode'] == $get_deal_close_arr[$parentid]['me_details']['empcode']){
										$pos_of_deal_close = $key;
										break;
									}
								}
								$top_position_arr = $retArr['data']['meInfo'][0];
								if($pos_of_deal_close > 0){
									$retArr['data']['meInfo'][0] = $retArr['data']['meInfo'][$pos_of_deal_close];
									$retArr['data']['meInfo'][$pos_of_deal_close] = $top_position_arr;
								}
							}
							if($allocateToJdaClick	== 1 && ($server_addr_arr[2] == "17" && (strtolower($login_city) == 'chandigarh' || strtolower($login_city) == 'jaipur' || strtolower($login_city) == 'hyderabad' || strtolower($login_city) == 'delhi' || strtolower($login_city) == 'noida') )){
								$f_str_me_list = "'$checkStr'";
								if (strpos($f_str_me_list, $deal_close_me_code) == false && $deal_close_me_code != "") {
									$checkStr.= $deal_close_me_code."','";
									$arra_to_ins = array('mktEmpCode'=>$get_deal_close_arr[$parentid]['me_details']['empcode'],"empName"=>$get_deal_close_arr[$parentid]['me_details']['empname'],"mobile"=>$get_deal_close_arr[$parentid]['me_details']['mobile']);
									array_push($retArr['data']['meInfo'],$arra_to_ins);
								}
								$pos_of_deal_close = 0;
								foreach($retArr['data']['meInfo'] as $key=>$data){
									if($data['mktEmpCode'] == $get_deal_close_arr[$parentid]['me_details']['empcode']){
										$pos_of_deal_close = $key;
										break;
									}
								}
								$top_position_arr = $retArr['data']['meInfo'][0];
								if($pos_of_deal_close > 0){
									$retArr['data']['meInfo'][0] = $retArr['data']['meInfo'][$pos_of_deal_close];
									$retArr['data']['meInfo'][$pos_of_deal_close] = $top_position_arr;
								}
							}
							$checkStr		=		substr($checkStr, 0,-3);
							$me_tim_dtls	=		array();
							$appData		=		array();
							$dbObjlocal		=	new DB($this->db['db_local']);
							$sel_me_code	= 		"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$checkStr."') AND actionTime >= '".$actiondate." 00:00:00' AND actionTime <= '".$actiondate." 23:59:59' AND flgAllocStatus > 0 AND cancel_flag = 0 AND allocationType IN (25,99) ORDER BY actionTime";
								$data_me_tim_con		=	$dbObjlocal->query($sel_me_code);
								while($data_me_tim		=	$dbObjlocal->fetchData($data_me_tim_con)) {
									$data_me_tim_exp	=	explode(" ",$data_me_tim['actionTime']);	
									$appData[substr($data_me_tim_exp[1],0,-3)][$data_me_tim['empCode']]	=	$data_me_tim;
								}
							$dbObjIDC		=	new DB($this->db['db_idc']);
							//~ $sel_me_code_idc	= 		"SELECT empCode, parentCode, contractCode, allocationType, actionTime, instruction, flgAllocStatus FROM tblContractAllocation WHERE empCode IN ('".$checkStr."') AND actionTime >= '".$actiondate." 00:00:00' AND actionTime <= '".$actiondate." 23:59:59' AND flgAllocStatus > 0 AND cancel_flag = 0 AND allocationType IN (35,36) ORDER BY actionTime";
								//~ $data_me_tim_con_idc		=	$dbObjIDC->query($sel_me_code_idc);
								//~ while($data_me_tim_idc		=	$dbObjIDC->fetchData($data_me_tim_con_idc)) {
									//~ $data_me_tim_exp_idc	=	explode(" ",$data_me_tim_idc['actionTime']);	
									//~ $appData[substr($data_me_tim_exp_idc[1],0,-3)][$data_me_tim_idc['empCode']]	=	$data_me_tim_idc;
								//~ }	
								//print_r($appData);
								$i = 0;
								foreach($retArr['data']['timeArr'] as $key=>$value) {
									if((strtotime($actiondate." ".$key.":00") < strtotime(date('Y-m-d H:i:s'))) && array_key_exists($key,$appData)) {
										$retArr['data']['timeArr'][$key]['flag']	=	1;
										if(array_key_exists('allocData',$retArr['data']['timeArr'][$key])) {
											$i++;
										} else {
											$i = 0;
										} 
										foreach($appData[$key] as $keyEmp=>$valEmp) {
											$retArr['data']['timeArr'][$key]['allocData'][$keyEmp]		=	$appData[$key][$keyEmp];
										}
										$keyStrToTime	=	strtotime($key);
										$time30Min		=	date("H:i", strtotime('+30 minutes', $keyStrToTime));
										$time60Min		=	date("H:i", strtotime('+60 minutes', $keyStrToTime));
										$date30min		=	date("Y-m-d H:i", strtotime('+30 minutes', $keyStrToTime));
										$date60min		=	date("Y-m-d H:i", strtotime('+60 minutes', $keyStrToTime));
										if(strtotime($date30min) < strtotime(date("Y-m-d 23:59"))) {
											$retArr['data']['timeArr'][$time30Min]['flag']		=	2;
											$retArr['data']['timeArr'][$time60Min]['flag']		=	2;
											foreach($appData[$key] as $keyEmp=>$valEmp) {
												$retArr['data']['timeArr'][$time30Min]['allocData'][$keyEmp]	=	$appData[$key][$keyEmp];
												$retArr['data']['timeArr'][$time60Min]['allocData'][$keyEmp]	=	$appData[$key][$keyEmp];
											}
										}
									} else if(array_key_exists($key,$appData) && $retArr['data']['timeArr'][$key]['flag'] != 1) {
										$retArr['data']['timeArr'][$key]['flag']			=	2;
										if(array_key_exists('allocData',$retArr['data']['timeArr'][$key])) {
											$i++;
										} else {
											$i = 0;
										}
										foreach($appData[$key] as $keyEmp=>$valEmp) {
											$retArr['data']['timeArr'][$key]['allocData'][$keyEmp]		=	$appData[$key][$keyEmp];
										}
										$keyStrToTime	=	strtotime($key);
										$time30Min		=	date("H:i", strtotime('+30 minutes', $keyStrToTime));
										$time60Min		=	date("H:i", strtotime('+60 minutes', $keyStrToTime));
										$date30min		=	date("Y-m-d H:i", strtotime('+30 minutes', $keyStrToTime));
										$date60min		=	date("Y-m-d H:i", strtotime('+60 minutes', $keyStrToTime));
										if(strtotime($date30min) < strtotime(date("Y-m-d 23:59"))) {
											$retArr['data']['timeArr'][$time30Min]['flag']		=	2;
											$retArr['data']['timeArr'][$time60Min]['flag']		=	2;
											foreach($appData[$key] as $keyEmp=>$valEmp) {
												$retArr['data']['timeArr'][$time30Min]['allocData'][$keyEmp]	=	$appData[$key][$keyEmp];
												$retArr['data']['timeArr'][$time60Min]['allocData'][$keyEmp]	=	$appData[$key][$keyEmp];
											}
										}
									} else if(strtotime($actiondate." ".$key.":00") < strtotime(date('Y-m-d H:i:s'))) {
										$retArr['data']['timeArr'][$key]['flag']	=	1;
									} else if($retArr['data']['timeArr'][$key]['flag'] != 2 && (strtotime($actiondate." ".$key.":00") >= strtotime(date('Y-m-d H:i:s')))){
										$retArr['data']['timeArr'][$key]['flag']	=	0;
									}
								}
								if(array_key_exists('00:00',$retArr['data']['timeArr'])){
									unset($retArr['data']['timeArr']['00:00']);
								}
								$retArr['errorCode'] 		=	0;
								$retArr['errorStatus'] 		=	"Data found";
								$retArr['whichFlow'] 		=	"normal";
								$retArr['future_Date'] 		=	$future_Date;
								$retArr['allME']			=	1;
								$retArr['ME_NA']			=	0;
								$retArr['meInfoLength']		=	count($retArr['data']['meInfo']);
								echo json_encode($retArr);
								exit;
						}

				else{
					$retArr['errorCode'] 		=	1;
					$retArr['errorStatus'] 		=	"Data Not found";
					$retArr['allME']			=	1;
					$retArr['ME_NA']			=	1;
					echo json_encode($retArr);
					exit;
				}			
			}
			$retArr['errorCode'] 		=	1;
			$retArr['allME']			=	1;
			$retArr['errorStatus'] 		=	"ME Not found";
			$retArr['ME_NA']			=	1;
			echo json_encode($retArr);
			exit;

		}		
					
	}	
		if($graBFlag=='1')
		{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
			if(!$urlFlag){
				$graBFlag 	=	$params['graBFlag'];
				$dateSend	=	$params['date'];
				$tme_code	=	$params['tme_code'];
				$allocToME	=	$params['allocToME'];
				$alloc_to_ME_TME	=	$params['alloc_to_ME_TME'];
				$Grb_Normal_alt_add	=	$params['Grb_Normal_alt_add'];
				$Jd_Omini_flg		=	$params['Jd_Omini_flg'];
			}else{
				$graBFlag 	=	$_REQUEST['graBFlag'];
				$dateSend 	=	$_REQUEST['date']; 
				$tme_code	=	$_REQUEST['tme_code'];
				$allocToME	=	$_REQUEST['allocToME']; 
				$alloc_to_ME_TME	=	$_REQUEST['alloc_to_ME_TME'];
				$Grb_Normal_alt_add	=	$_REQUEST['Grb_Normal_alt_add'];
				$Grb_Normal_alt_add	=	$_REQUEST['Grb_Normal_alt_add'];
				$Jd_Omini_flg		=	$_REQUEST['Jd_Omini_flg'];
			}
			if($dateSend == "") {
				$dateSend	=	$actiondate;
			}	
			if($graBFlag=="" || $graBFlag==null || empty($graBFlag)){
				$retArrGrab['errorCode'] 	=	1;
				$retArrGrab['errorStatus'] 	=	"Parameters Not upto the Mark";
				echo json_encode($retArrGrab);
				exit;
			}
			$givenDate     = date("Y-m-d", strtotime($dateSend));
			
			if(($givenDate=="" || $givenDate==null || empty($givenDate))){
				$retArrGrab['errorCode'] 	=	1;
				$retArrGrab['errorStatus'] 	=	"Date Format not correct";
				echo json_encode($retArrGrab);
				exit;
			}
			$currentTime 		=	date('H:i');
			$Currentdate 		= 	date('Y-m-d');
			if($givenDate>$Currentdate){
				for ($i=0; $i <count($time_array) ; $i++) { 
					$retArrGrab[$time_array[$i]]	=	array("flag"=>'0');
				}
			}elseif($givenDate<$Currentdate){
				for ($i=0; $i <count($time_array) ; $i++) { 
					$retArrGrab[$time_array[$i]]	=	array("flag"=>'1');
					
				}
			}else{
				for ($i=0; $i <count($time_array) ; $i++) { 
					if( $time_array[$i] <	$currentTime){
						//disabled time
						$retArrGrab[$time_array[$i]]	=	array("flag"=>'1');
					}else{
						//enabled time
						$retArrGrab[$time_array[$i]]	=	array("flag"=>'0');
					}
				}
			}
				$retArr['data'] 		=	$retArrGrab;
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Found";
				$retArr['whichFlow'] 	=	"grab";
				$retArr['future_Date'] 	=	$future_Date;
				echo json_encode($retArr);
				exit;
		}		
			//echo 'came out ';
			//	echo json_encode($retArr);
	}
	
	}
	
	/* Function to get TME RANK, Total TME*/
	public function get_TME_rank($empCode,$login_city){
		$this->meTmeCode		=	'';
		$this->meTmeCode		= 	$empCode;
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$server_addr_arr	= explode(".",$_SERVER['SERVER_ADDR']);
		if($server_addr_arr[2] == 0){
			$city	=	"branch='Mumbai' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 8){
			$city	=	"branch='Delhi' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 16){
			$city	=	"branch='Kolkata' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 26){
			$city	=	"branch='Bangalore' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 32){
			$city	=	"branch='Chennai' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 40){
			$city	=	"branch='Pune' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 50){
			$city	=	"branch='Hyderabad' AND city_flag = 1";
		}elseif( $server_addr_arr[2] == 56 || $server_addr_arr[2] == "35" ) {
			$city	=	"branch='Ahmedabad' AND city_flag = 1";
		}elseif($server_addr_arr[2] == 17){
			if(strtolower($login_city) == 'delhi' || strtolower($login_city) == 'noida'){
				$city 	= 	"branch IN ('Noida','Delhi') AND city_flag = 2"; //  AND city_flag = 2
			}else{
				$city	=	"branch='".$login_city."' AND city_flag = 2"; // AND city_flag = 2
			}
		}elseif($server_addr_arr[2] == 64){
			$city	=	"branch='Mumbai'";
		}
		$query_mktgEmpMaster 	=	"SELECT cumulative_rank_city FROM tbl_ranking_tme_final WHERE empcode='".$this->meTmeCode."' AND active_flag = '1'";
		$conn_mktgEmpMaster 	=	$con_local->query($query_mktgEmpMaster);
		$num_mktgEmpMaster 		=	$con_local->numRows($conn_mktgEmpMaster);
		$respArr 				=	$con_local->fetchData($conn_mktgEmpMaster);
		if($num_mktgEmpMaster > 0){
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
			$retArr['cumulative_rank_city_tme']		=	$respArr['cumulative_rank_city'];
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'TMERankNotFound ';
		}
		/*Cal total TME*/
		$query_tot_TME 					=	"SELECT empcode FROM tbl_ranking_tme_final WHERE ".$city."";
		$conn_query_tot_TME 			=	$con_local->query($query_tot_TME);
		$num_conn_query_tot_TME 		=	$con_local->numRows($conn_query_tot_TME);
		$retArr['TOTALTME']				=	$num_conn_query_tot_TME;
		return json_encode($retArr);
	}
	/*get list of ME's based on pincode and also their rank*/
	public function get_ME_Total($pincode){
		$checkStr				=	'';
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$query_mktgEmpMaster 	=	"SELECT empcode as ME FROM tbl_me_allocation_final where pincode='".$pincode."' AND approval_flag = 1 AND (empcode !='' AND empcode IS NOT NULL)";
		$conn_mktgEmpMaster 	=	$con_local->query($query_mktgEmpMaster);
		$num_mktgEmpMaster 		=	$con_local->numRows($conn_mktgEmpMaster);		
		if($num_mktgEmpMaster > 0){
			$strEmp	=	"";
			while($data	=	$con_local->fetchData($conn_mktgEmpMaster)) {
				$strEmp.=	$data['ME']."','";
			}
			$strEmp		    =   rtrim($strEmp, ",");
			$strEmp		    =   rtrim($strEmp, ",''");
			$sqlSelect 		= 	"SELECT a.mktEmpCode, a.empName, a.mobile,a.allocID,if(b.cumulative_rank_city is null,10000000,b.cumulative_rank_city) as cumulative_rank_city FROM mktgEmpMaster As a LEFT JOIN d_jds.tbl_ranking_me_final AS b ON a.mktEmpCode = b.empcode WHERE a.mktEmpCode IN('".$strEmp."') AND a.mktEmpCode NOT IN('018916','000108') AND a.emptype = '3' AND a.Approval_flag = '1' ORDER BY cumulative_rank_city ASC";
			$con_me_chk		=	$con_local->query($sqlSelect); 
			$num_me_chk		=	$con_local->numRows($con_me_chk);
			if($num_me_chk > 0){
				$retArr['count_pincode_wise_me']	=	$num_me_chk;
				while($data_chk	=	$con_local->fetchData($con_me_chk)) {				
					$retArr['LISTOFME'][]	=	$data_chk;
				}
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'DataFound';
			}else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'DNF';
			}
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'DNF';
		}
		return json_encode($retArr);
	}
	
	/* API for login me For AllMe Data for manager
	 * Created By Apoorv Agrawal
	 */
	public function allMelogin(){
		$retArr 	=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		//echo "<pre>";print_r($params);die();
		if(!$urlFlag){
			$loginid 			=	$params['loginid'];
			$logInPassword		=	$params['logInPassword'];
		}else{
			$loginid 			=	$_REQUEST['loginid'];
			$logInPassword		=	$_REQUEST['logInPassword'];
		}
		$id       	= 	htmlspecialchars(trim($loginid));  
        $pwd   		= 	htmlspecialchars(trim($logInPassword)); 
		$dbObjlocal	=	new DB($this->db['db_local']);
		$user_chk 	= "SELECT * FROM mktgEmpMaster WHERE mktEmpCode = '".$id."' and empPassword = '".md5($pwd)."' and empType = '9'";
		$user_fetch	= $dbObjlocal->query($user_chk);
		$user_row	= $dbObjlocal->numRows($user_fetch);
		if ($user_row > 0){
			$ret = 1;
		} 
		else{
			$ret = 2;
		}
		$retArr['ls']			=	$ret;
		$retArr['errorCode']	=	0;
		echo json_encode($retArr);
	}
	
	/*
	 * API Function To Insert Alertnate Address Data From
	 * Created bu Apoorv Agrawal
	 * Date: 21-03-2016
	*/
	public function addAlternateAddress(){
		$retArr		=	array();
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		
		if(!$urlFlag){
			$companyname	=	$params['companyname'];
			$parentid		=	$params['parentid'];	
			$country		=	'India';
			$city			=	$params['city'];
			$state			=	$params['state'];
			$area			=	$params['area'];
			$building		=	$params['building'];
			$street			=	$params['street'];
			$landmark		=	$params['landmark'];
			$pincode		=	$params['pincode'];
			$state_id		=	$this->get_state_id($state);
			$city_id		=	$this->get_city_id($city);
			$ucode			=	$params['ucode'];
			$country_id		=	$params['country'];// 98 For INDIA
		}else{
			$companyname	=	$_REQUEST['companyname'];
			$parentid		=	$_REQUEST['parentid'];	
			$country		=	'India';
			$city			=	$_REQUEST['city'];
			$state			=	$_REQUEST['state'];
			$area			=	$_REQUEST['area'];
			$building		=	$_REQUEST['building'];
			$street			=	$_REQUEST['street'];
			$landmark		=	$_REQUEST['landmark'];
			$pincode		=	$_REQUEST['pincode'];
			$state_id		=	$this->get_state_id($state);
			$city_id		=	$this->get_city_id($city);
			$ucode			=	$_REQUEST['ucode'];
			$country_id		=	$_REQUEST['country'];
		}
		if($companyname=='' || empty($companyname)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Company Not Found";
			return false;
		}
		if($parentid=='' || empty($parentid)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Parentid Not Found";
		}
		if($country=='' || empty($country)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"country Not Found";
			return false;
		}
		if($city=='' || empty($city)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"city Not Found";
			return false;
		}
		if($state=='' || empty($state)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"state Not Found";
			return false;
		}
		if($pincode=='' || empty($pincode)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"pincode Not Found";
			return false;
		}
		if($ucode=='' || empty($ucode)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"EmployeeCode Not Found";
			return false;
		}
		if($country_id=='' || empty($country_id)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"country Id Not Found";
			return false;
		}
		if($state_id=='' || empty($state_id)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"State Id Not Found";
			return false;
		}
		if($city_id=='' || empty($city_id)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"City Id Not Found";
			return false;
		}
		$db_tme		=	new DB($this->db['db_tme']);//tme_jds
		$sql 		= 	"INSERT INTO tbl_companymaster_extradetails_altaddress_shadow 
						SET
						companyname = '".addslashes($companyname)."',
						parentid = '".addslashes($parentid)."',
						country = '".addslashes($country)."',
						state = '".addslashes($state)."',
						city = '".addslashes($city)."',
						area = '".addslashes($area)."',
						building_name = '".addslashes($building)."',
						street = '".addslashes($street)."', 
						landmark = '".addslashes($landmark)."',
						pincode = '".addslashes($pincode)."',
						country_id = '".addslashes($country_id)."',
						state_id = '".addslashes($state_id)."',
						city_id = '".addslashes($city_id)."',
						insertdate = now(),
						tmeCode = '".$ucode."'
						ON DUPLICATE KEY UPDATE 
						country = '".addslashes($country)."',
						state = '".addslashes($state)."',
						city = '".addslashes($city)."',
						area = '".addslashes($area)."',
						building_name = '".addslashes($building)."',
						street = '".addslashes($street)."', 
						landmark = '".addslashes($landmark)."',
						pincode = '".addslashes($pincode)."',
						country_id = '".addslashes($country_id)."',
						state_id = '".addslashes($state_id)."',
						city_id = '".addslashes($city_id)."',
						insertdate = now(),
						tmeCode = '".$ucode."'";
		$res 		= 	$db_tme->query($sql);
		if($res){
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"Data Inserted";
		}else{
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Data not Inserted";
		}
		echo json_encode($retArr);
	}
	/*
	 * Function Written for internal use to get Sate_id based on State name
	 * Created By Apoorv Agrawal
	 * Added B'Cs Needed to update Alternatre Address
	 */
	public function get_state_id($state_name){
		$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
		$st_name		=	$state_name;
		$query			=	"Select state_id FROM d_jds.state_master WHERE st_name = '".$st_name."';";
		$result  		=	$dbObjLocal->query($query);
		$num			=	$dbObjLocal->numRows($result);
		$data			=	$dbObjLocal->fetchData($result);
		if($num>0){
			return $data['state_id'];die();
		}
	}
	/*
	 * Function Written for internal use to get city_id based on City name
	 * Created By Apoorv Agrawal
	 * Added B'Cs Needed to update Alternatre Address
	 */
	public function get_city_id($ct_name){
		$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
		$ct_name		=	$ct_name;
		$query			=	"SELECT city_id FROM d_jds.tbl_city_master WHERE ct_name =	'".$ct_name."';";
		$result  		=	$dbObjLocal->query($query);
		$num			=	$dbObjLocal->numRows($result);
		$data			=	$dbObjLocal->fetchData($result);
		if($num>0){
			return $data['city_id'];die();
		}
	}
	
	public function ecsTransfer(){
		GLOBAL $parseConf;
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
		
		$ecs_flag		=	$params['ecs_flag'];
		$extnID			=	$params['extn'];
		
			
		if($parseConf['servicefinder']['remotecity'] == 1)
		{
			
			$upd_lead_api 		= "INSERT INTO d_jds.tbl_transfer_api_log
									SET
									extn = '".$extnID."',
									insert_date = NOW(),
									transferflag = '".$ecs_flag."'";
			$con_upd_lead_retlog				= $dbObjLocal->query($upd_lead_api);
			
			$query			=	"SELECT id,parentid,f12_id,scity FROM db_iro.tbl_f12_transfer_data WHERE (ext='".$extnID."' OR cli='".$extnID."') AND status =0 ORDER BY DATETIME DESC LIMIT 1;";
			$result  		=	$dbObjLocal->query($query);
			$data			=	$dbObjLocal->fetchData($result);
			
			$query_city		=	"SELECT * FROM d_jds.tbl_zone_cities WHERE cities='".$data['scity']."' AND main_zone='".$_SESSION['loginCity']."'";
			$result_ct  			=	$dbObjLocal->query($query_city);
			$num_ct					=	$dbObjLocal->numRows($result_ct);
			if($num_ct > 0)
			{
					$f12_id_qry = "SELECT opt_name FROM db_iro.tbl_f12_option WHERE opt_code = '".$data['f12_id']."'";
					$f12_id_res =  $dbObjLocal->query($f12_id_qry);	
					$f12_id_row = $dbObjLocal->fetchData($f12_id_res);
					
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Data Inserted";
					$retArr['data'] = $data;
					$retArr['opt_name'] = $f12_id_row;
			}else
			{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data not Inserted";
			}
			
			
		}else
		{
			
			$upd_lead_api 		= "INSERT INTO d_jds.tbl_transfer_api_log
									SET
									extn = '".$extnID."',
									insert_date = NOW(),
									transferflag = '".$ecs_flag."'";
			$con_upd_lead_retlog				= $dbObjLocal->query($upd_lead_api);
			
			$query			=	"SELECT id,parentid,f12_id FROM db_iro.tbl_f12_transfer_data WHERE (ext='".$extnID."' OR cli='".$extnID."') ORDER BY DATETIME DESC LIMIT 1;";
			$result  		=	$dbObjLocal->query($query);
			$num			=	$dbObjLocal->numRows($result);
			$data			=	$dbObjLocal->fetchData($result);
			
			$f12_id_qry = "SELECT opt_name FROM db_iro.tbl_f12_option WHERE opt_code = '".$data['f12_id']."'";
			$f12_id_res =  $dbObjLocal->query($f12_id_qry);	
			$f12_id_row = $dbObjLocal->fetchData($f12_id_res);
			
			if($num >0){
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Inserted";
				$retArr['data'] = $data;
				$retArr['opt_name'] = $f12_id_row;
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data not Inserted";
			}
		}
		echo json_encode($retArr);
	}
	public function iroAppTransfer() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		
		$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
		$dbObjFIN	=	new DB($this->db['db_finance']);
		$currdate    = date('Y-m-d H:i:s',mktime(date("H"), date("i")-10, date("s"), date("m")  , date("d"), date("Y"))); 
		//$currdate    = '2017-01-20 15:27:20';
		$currdate_log    = date("Y-m-d H:i:s");
		$IP   = $_SERVER['REMOTE_ADDR'];
		$transferDetails = array();
		$retArr		=	array();
	
		if(strlen($params['extn']) == 4)
		{
			$cond =	'(ExtNo ='.$params['extn'].' OR Clinum ='.$params['extn'].' )';
		}else if(strlen($params['extn']) > 4)
		{
			$cond =	'(CallerMobile ='.substr($params['extn'],-10).'  OR CallerPhone ='.$params['extn'].' OR Clinum ='.$params['extn'].')';
		}
		
			$iro_api_log 		= "INSERT INTO d_jds.tbl_iro_transfer_api_log
									SET
									extn = '".$params['extn']."',
									insert_date = NOW(),
									step_flow  = 1,
									transferflag = 'IRO-Recent'";
			$iro_api_log_con	= $dbObjLocal->query($iro_api_log);
		
	
		      $sql_query_Transfer = "SELECT Clinum,
							IroCode,
							IroName,
							CallerName,
							CallerMobile,
							CallerPhone,
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
							
				
				$ExtnRec = "../logs/log_tme/irototme-".date('Y-m-d').".txt";
				$pathToLog = dirname($ExtnRec);
				if (!file_exists($pathToLog)) {
					mkdir($pathToLog, 0777, true);
				}
				$fp = fopen($ExtnRec, 'a') or die("can't open file");
				$strData = date("Y-m-d H:i:s")."\t mkgJr \tclinum===>".$params['extn']."\tDefauld_City===>".$city."\t TME ExtNo==>".$_SESSION['Extn_id']."\tREMOTE===>".$_SERVER['REMOTE_ADDR']."\tSERVER===>".$_SERVER['HTTP_HOST']."\tsqlqry===>".$sql_query_Transfer."\n";
				
				
				
					
			$execqryTransfer 	= $dbObjLocal->query($sql_query_Transfer);
			 $numrowstme 		= $dbObjLocal->numRows($execqryTransfer);
			 
			 $strData .= " \t numrowstme ".$numrowstme." \n";
			@FWRITE($fp,$strData."\r\n");
			@FCLOSE($fp);
			 
			 
			if($numrowstme > 0)
			{
				 $res_fetch_Data 	= $dbObjLocal->fetchData($execqryTransfer);
				
				/************************For Piad/Expired/Save as Nonpaid Check***************************/
				
				 $sql_query_paidcont 	= "SELECT * FROM db_finance.tbl_companymaster_finance WHERE parentid ='".$res_fetch_Data['Parentid']."' AND balance>0 AND expired=0 AND freeze =0 AND mask =0 GROUP BY parentid "; 
				$execqry_paidcont 		= $dbObjFIN->query($sql_query_paidcont);
				$numrowstme_paidcont 	= $dbObjFIN->numRows($execqry_paidcont);
				if($numrowstme_paidcont >0)
				{
					$transferDetails['paid']		=	1;
					
				}else {
					$transferDetails['paid']		=	0;
				}
				
				
				$qry_saveasnonpaid = "SELECT count(*) as cnt FROM db_iro.tbl_apptransfer WHERE Parentid='".$res_fetch_Data['Parentid']."' AND Uniquefield = '".$res_fetch_Data['Uniquefield']."'";
				$res_saveasnonpaid = $dbObjLocal->query($qry_saveasnonpaid);
				$numrowstme_saveas_nonpaid = $dbObjLocal->fetchData($res_saveasnonpaid);
				if($numrowstme_saveas_nonpaid['cnt'] == 0)
				{
					$transferDetails['saveas_nonpaid']		=	1;
				}else {
					$transferDetails['saveas_nonpaid']		=	0;
				}
				
				/*
				$qry_area_pincode 					= "SELECT area,pincode FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$res_fetch_Data['Parentid']."'";
				$res_area_pincode 					= $dbObjLocal->query($qry_area_pincode);
				$res_num_area_pincode 				= $dbObjLocal->numRows($res_area_pincode);
				$numrowstme_area_pincode 			= $dbObjLocal->fetchData($res_area_pincode);
				if($res_num_area_pincode>0)
				{
					
					$transferDetails['pincode']		=	$numrowstme_area_pincode['pincode'];
					$transferDetails['area']		=	$numrowstme_area_pincode['area'];
				}
				*/
				
				$paramsarr['url'] = "http://192.168.22.103:800/services/mongoWrapper.php?action=getdata&post_data=1&parentid=".$res_fetch_Data['Parentid']."&data_city=".$res_fetch_Data['City']."&module=tme&table=tbl_companymaster_generalinfo_shadow";
				$paramsarr['formate'] = 'basic';
				$paramsarr['method'] = 'get';
				$paramsarr['postData'] = $paramsarr;
				$getLastDealClose_data = json_decode(Utility::curlCall($paramsarr),true);
				
				
				if($getLastDealClose_data['pincode'] == '' || $getLastDealClose_data['area'] == '')
				{
					$qry_area_pincode 					= "SELECT area,pincode FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$res_fetch_Data['Parentid']."'";
					$res_area_pincode 					= $dbObjLocal->query($qry_area_pincode);
					$res_num_area_pincode 				= $dbObjLocal->numRows($res_area_pincode);
					$numrowstme_area_pincode 			= $dbObjLocal->fetchData($res_area_pincode);
					
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
				
				/*****************************End**********************************************************/
				
				/*******************************************Insert db_iro.tbl_appointment_iro**************/
				
				if(isset($res_fetch_Data['Parentid']))
					{
						 $insertqry_appIro = "INSERT INTO db_iro.tbl_appointment_iro SET 
												parentid='".$res_fetch_Data['Parentid']."',
												ironame='".$res_fetch_Data['IroName']."',
												irocode='".$res_fetch_Data['IroCode']."'
												ON DUPLICATE KEY UPDATE
												ironame='".$res_fetch_Data['IroName']."',
												irocode='".$res_fetch_Data['IroCode']."'";	
						$execInsertappIro = $dbObjLocal->query($insertqry_appIro);
					}
				
				
				$iro_api_log1 		= "INSERT INTO d_jds.tbl_iro_transfer_api_log
									SET
									extn = '".$params['extn']."',
									insert_date = NOW(),
									parentid = '".$res_fetch_Data['Parentid']."',
									step_flow  = 2,
									transferflag = 'IRO-Parentid'";
					$iro_api_log_con1	= $dbObjLocal->query($iro_api_log1);
				/************************************************End****************************************/
				
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Inserted";
				$retArr['data'] = $transferDetails;
				
				
			}else
			{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data not Inserted";
				
				
			}
			
			echo json_encode($retArr);
    }
    
	public function iroAppSaveExit()
    {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
	
		
		
			$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
			//$dbObjLocal			=	new DB($this->db['db_local_slave']);
			$retArr		=	array();
			if($params['parentid'] !='')
			{
				
				
				 $saveExit ="INSERT INTO d_jds.tbl_saveexit SET 
													parentid='".$params['parentid']."',
													tmecode='".$params['usercode']."',
													city='".$params['city']."',
													EntryDate=NOW()
													ON DUPLICATE KEY UPDATE
													tmecode='".$params['usercode']."',
													city='".$params['city']."',
													EntryDate=NOW()";
				$saveExit_con = $dbObjLocal->query($saveExit);
				
				
					$allocateTimecode 	="SELECT rowid FROM d_jds.mktgEmpMap WHERE mktEmpCode ='".$params['usercode']."'";
					$con_tmecode 		= $dbObjLocal->query($allocateTimecode);
					$res_tmecode		= $dbObjLocal->fetchData($con_tmecode);
					
					$allocateExistTimecode 		="SELECT tmecode,empCode FROM d_jds.tbl_tmesearch WHERE parentid ='".$params['parentid']."' AND (tmecode IS NOT NULL AND tmecode !='')";
					$con_Existtmecode 			= $dbObjLocal->query($allocateExistTimecode);
					$res_Existtmecode			= $dbObjLocal->fetchData($con_Existtmecode);
				
				
					if($res_Existtmecode['tmecode'] != '')
					{
						$existTmecode = $res_Existtmecode['tmecode'];
						$existEmpcode = $res_Existtmecode['empCode'];
					}else
					{
						$existTmecode = $rowstme_rowid['rowid'];
						$existEmpcode = $params['usercode'];
					} 
				 
					
					
					$allocate_tme_saveExit ="UPDATE d_jds.tbl_tmesearch SET tmecode ='".$existTmecode."',empCode ='".$existEmpcode."', data_source = 'TRANSFERRED',datasource_date=NOW() WHERE parentid ='".$params['parentid']."'";
					$delexecqry_Emp_name_paid = $dbObjLocal->query($allocate_tme_saveExit);
				
				
			
				$iro_api_log2 		= "INSERT INTO d_jds.tbl_iro_transfer_api_log
									SET
									extn = '".$params['extn']."',
									insert_date = NOW(),
									parentid = '".$params['parentid']."',
									step_flow  = 3,
									transferflag = 'IRO-SaveExit'";
					$iro_api_log_con2	= $dbObjLocal->query($iro_api_log2);	
			
				if(isset($saveExit_con)) 
				{
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Data Inserted";
				}else
				{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus'] 	=	"Data Not Inserted";
				}
			}else
			{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Inserted";
			}	
			
			echo json_encode($retArr);
	}
	
	public function proceedCompany()
    {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
			$dbObjLocal		=	new DB($this->db['db_local']);//d_jds
			//$dbObjLocal			=	new DB($this->db['db_local_slave']);
			$dbObjFIN	=	new DB($this->db['db_finance']);
			$retArr		=	array();
			if($params['parentid'] !='')
			{
				$IP   = $_SERVER['REMOTE_ADDR'];
				
				if($params['pincode'] != '' && $params['area']!= '')
				{
					
					$mongo_inputs = array();
					$mongo_inputs['action'] 	= 'updatedata';
					$mongo_inputs['post_data'] 	= '1';
					$mongo_inputs['module'] 	= 'TME';
					$mongo_inputs['parentid'] 	= $params['parentid'];
					$mongo_inputs['data_city'] 	= $params['city'];
					
					$mongo_data = array();
					
					$geninfo_tbl 		= "tbl_companymaster_generalinfo_shadow";
					$geninfo_upt = array();
					$geninfo_upt['companyname'] 			= addslashes(stripslashes($params['businessname']));
					$geninfo_upt['area'] 					= addslashes(stripslashes($params['area']));
					$geninfo_upt['pincode'] 				= $params['pincode'];
					$geninfo_upt['data_city'] 				= addslashes($params['city']);
					$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
						
					
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					
					$extrdet_upt['companyname'] 		= addslashes(stripslashes($params['businessname']));
					$extrdet_upt['updatedOn'] = date('Y-m-d H:i:s');
					$extrdet_upt['data_city'] = $params['city'];			
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
					
					
					$extrdet_ins = array();
					$extrdet_ins['original_date'] 		= date('Y-m-d H:i:s');
					$mongo_data[$extrdet_tbl]['insertdata'] = $extrdet_ins;
							
							
							
						 $mongo_inputs['table_data'] 			= http_build_query($mongo_data);
							
						
							 //$url 	= 	'http://hemavathiv.jdsoftware.com/jdbox/services/mongoWrapper.php';
							 $url 	= JDBOX_API."/services/mongoWrapper.php";
							//$url =	'172.29.'.$citycode.'.217:1010/services/mongoWrapper.php';
						
						$curlParams_temp	=	array();
						$curlParams_temp['url']		=	$url;
						$curlParams_temp['method']	=	'post';
						$curlParams_temp['formate'] = 	'basic';
						$curlParams_temp['postData'] = $mongo_inputs;
						
						
						
						$insert_resp	=	Utility::curlCall($curlParams_temp);
						//var_dump($insert_resp);	
				}
				
				
				//if($params['data_city'] == 'Pune')
				//{
					if($params['calldis'] == '1')
					{
						$update_query_apptransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '0' WHERE Uniquefield = '".$params['Uniquefield']."' ";
						$delexecqry_apptransfer 				= $dbObjLocal->query($update_query_apptransfer);
					}else
					{
						$update_query_apptransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '1' WHERE Uniquefield = '".$params['Uniquefield']."' ";
						$delexecqry_apptransfer 				= $dbObjLocal->query($update_query_apptransfer);
					}
				/*}else
				{
					$update_query_apptransfer 	= "UPDATE db_iro.tbl_apptransfer SET tmetransferflag = '1' WHERE Parentid = '".$params['parentid']."' AND Uniquefield = '".$params['Uniquefield']."' ";
					$delexecqry_apptransfer 				= $dbObjLocal->query($update_query_apptransfer);
				}*/
				
				$update_query_extra 			= "UPDATE db_iro.tbl_companymaster_extradetails SET updatedby = '".$params['usercode']."' WHERE Parentid = '".$params['parentid']."'";
					$delexecqry_query_extra 			= $dbObjLocal->query($update_query_extra);
				
				  $update_query_extra 			= "UPDATE db_iro.tbl_companymaster_extradetails SET updatedby = '".$params['usercode']."' WHERE Parentid = '".$params['parentid']."'";
				$delexecqry_query_extra 			= $dbObjLocal->query($update_query_extra);
				
				  $update_query_appointment 	= "UPDATE db_iro.appointment SET AllocatedTo = '".$params['usercode']." - ' WHERE Parentid = '".$params['parentid']."' AND Uniquefield = '".$params['Uniquefield']."' ";
				$delexecqry_appointment = $dbObjLocal->query($update_query_appointment);
				
				$log_records = date('Y-m-d H:i:s').'IP===>'.$IP.'ParentId===>'.$params['parentid'].'AllocatedTo===>'.$params['usercode'].'QueryAppoint_IRO===>'.$update_query_appointment.'Appoint_IRO mkg======>'.$update_query_apptransfer;
				
				  $update_query_App_log = 'UPDATE db_iro.irotmeapplogs SET transferflag = 1,inserttime_tme = NOW(),tmecode="'.$params['usercode'].'",tmename = "'.$params['uname'].'",tme_logs="'.$log_records.'", allocated_tme ="'.$params['usercode'].'" WHERE Parentid = "'.$params['parentid'].'" AND uniquefield = "'.$params['Uniquefield'].'" ';
				 $delexecqry_App_log = $dbObjLocal->query($update_query_App_log);
				 
				$allocate_rowid 		="SELECT rowid FROM d_jds.mktgEmpMap WHERE mktEmpCode ='".$params['usercode']."'";
				$delexecqry_rowid 		= $dbObjLocal->query($allocate_rowid);
				$rowstme_rowid	 	= $dbObjLocal->fetchData($delexecqry_rowid);
				
				$allocateExistTimecode 		="SELECT tmecode,empCode FROM d_jds.tbl_tmesearch WHERE parentid ='".$params['parentid']."' AND (tmecode IS NOT NULL AND tmecode !='')";
				$con_Existtmecode 			= $dbObjLocal->query($allocateExistTimecode);
				$res_Existtmecode			= $dbObjLocal->fetchData($con_Existtmecode);
				
				
				
				/***ECS PAID CHECK***/
				$sqECS 		= "SELECT  * FROM db_finance.invoice_ecs_bills_fin WHERE parentid='".$IROData['parentid']."' ORDER BY clearance_date DESC LIMIT 1";
				$conEcs 	= $dbObjFIN->query($sqECS);
				if($conEcs && $dbObjFIN->numRows($conEcs)>0){
					$res_Owner = $dbObjFIN->fetchData($conEcs);
					$ECSempcode = $res_Owner['dc_by'];
					$sqlECSTmecode ="SELECT rowid FROM d_jds.mktgEmpMap WHERE mktEmpCode ='".$ECSempcode."'";
					$conEcsTmecode = $dbObjLocal->query($sqlECSTmecode);	
					$existres = $dbObjLocal->fetchData($conEcsTmecode);
					$exist_owner_ecs_tme = $existres['rowid'];
					$exist_owner_ecs_emp = $ECSempcode;
					if($exist_owner_ecs_tme!='' || $exist_owner_ecs_tme!= null) 
					{
						$existTmecode = $exist_owner_ecs_tme;
						$existEmpcode = $exist_owner_ecs_emp;
					}else
					{
						if($res_Existtmecode['tmecode']!= '')
						{
							$existTmecode = $res_Existtmecode['tmecode'];
							$existEmpcode = $res_Existtmecode['empCode'];
						}else
						{
							$existTmecode = $rowstme_rowid['rowid'];
							$existEmpcode = $params['usercode'];
						}	
					}
				}else
				{
				
					if($res_Existtmecode['tmecode']!= '')
					{
						$existTmecode = $res_Existtmecode['tmecode'];
						$existEmpcode = $res_Existtmecode['empCode'];
					}else
					{
						$existTmecode = $rowstme_rowid['rowid'];
						$existEmpcode = $params['usercode'];
					} 
				}
				
				
				
				/*if($res_Existtmecode['tmecode']!= '')
				{
					$existTmecode = $res_Existtmecode['tmecode'];
					$existEmpcode = $res_Existtmecode['empCode'];
				}else
				{
					$existTmecode = $rowstme_rowid['rowid'];
					$existEmpcode = $params['usercode'];
				}*/
				 
				 
				$update 	= "UPDATE d_jds.tbl_tmesearch set tmecode ='' where parentid ='".$params['parentid']."'";
				$delexecqry_apptransfers 				= $dbObjLocal->query($update);
				 
				 $allocate_tme_tmesearch 		="UPDATE d_jds.tbl_tmesearch SET tmecode ='".$existTmecode."',empCode ='".$existEmpcode."',data_source = 'TRANSFERRED',datasource_date=NOW() WHERE parentid ='".$params['parentid']."'";
				$delexecqry_tme_tmesearch 		= $dbObjLocal->query($allocate_tme_tmesearch);
				
				
				$iro_api_log2 		= "INSERT INTO d_jds.tbl_iro_transfer_api_log
									SET
									extn = '".$params['extn']."',
									insert_date = NOW(),
									parentid = '".$params['parentid']."',
									step_flow  = 4,
									transferflag = 'IRO-Allocated'";
					$iro_api_log_con2	= $dbObjLocal->query($iro_api_log2);	
			
				$retArr['errorCode'] 	=	0;
				$retArr['errorStatus'] 	=	"Data Inserted";
			}else
			{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Data Not Inserted";
			}	
			
			echo json_encode($retArr);
	}
	/*
	 * API CREATED TO HANDLE ME ABSENT SLOT WISE
	 * CREATED BY APOORV AGRAWAL
	 * DATE : 12-04-2017
	*/
	public function meisabsent(){
		/*Query to get the Absent Details of MEs*/
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$argsArr	=	array();
		if(isset($_REQUEST['urlFlag']) && $_REQUEST['urlFlag'] == 1){
			$argsArr	=	$_REQUEST;
		}else{
			$argsArr	=	$params;
		}
		if($argsArr['meCode'] == '' || $argsArr['meCode'] == null){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'MeCode Not Sent';
			return json_encode($retArr);
		}
		if($argsArr['dateOfAppt'] == '' || $argsArr['dateOfAppt'] == null){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Date Not Sent';
			return json_encode($retArr);
		}
		if($argsArr['slotOfAppt'] == '' || $argsArr['slotOfAppt'] == null){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Appt slot Not Sent';
			return json_encode($retArr);
		}
		$argsArr['dateOfAppt'] 	= 	date("Y-m-d", strtotime($argsArr['dateOfAppt']));
		//~ echo "<pre>";print_r($argsArr);
		//~ die;
		$listOf_absent_time_slot	=	array();
		$abesnt_me_data 	=	array();
		$dbObjIDC			=	new DB($this->db['db_idc']);
		$absent_me_query	=	"SELECT empcode,absent_on,time_slot_tme FROM tbl_meabsentdetails WHERE empcode ='".$argsArr['meCode']."' AND absent_on = '".$argsArr['dateOfAppt']."'";
		$con_absent_me		=	$dbObjIDC->query($absent_me_query); 
		$num_absent_me		=	$dbObjIDC->numRows($con_absent_me);
		if($num_absent_me > 0){
			while($data_absent_me	=	$dbObjIDC->fetchData($con_absent_me)) {
				if($data_absent_me['time_slot_tme'] != ''){
					//~ echo "here";
					$time_slot				=	explode("-",$data_absent_me['time_slot_tme']);
					$time_slot_start		=	$time_slot[0];
					$time_slot_end			=	$time_slot[1];
					$time_slot_start_block	=	$time_slot[0];
					$time_slot_end_block	=	$time_slot[1];
					$hourdiff 				= 	round((strtotime($time_slot_end) - strtotime($time_slot_start))/3600);
					$hourdiff				=	$hourdiff*2;
					for($i=0;$i<$hourdiff;$i++){
						if(strtotime($time_slot_start_block) <= strtotime($time_slot_end)){
							$listOf_absent_time_slot[]	=	$time_slot_start_block;
							$time_slot_start_block		=	date("H:i", strtotime('+30 minutes', strtotime($time_slot_start_block)));
						}
					}
					$listOf_absent_time_slot[]	=	$time_slot_end;
				}else{
					// consider ME absent for whole day i.e remove this ME from the list itself
				}
			}
			if(in_array($argsArr['slotOfAppt'],$listOf_absent_time_slot)){
				$retArr['data']['showPopUp']	=	0; // 0 - show popup
			}else{
				$retArr['data']['showPopUp']	=	1; // 1 - don't show popup
			}
			$retArr['data']['listOf_absent_time_slot']	=	$listOf_absent_time_slot;
		}else{
			$retArr['data']['showPopUp']	=	1;
		}
		return json_encode($retArr);
	}
	private function getLastDealClose($parentid,$data_city){
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64'){
			$paramsarr['url'] = "http://vishalvinodrana.jdsoftware.com/jdbox/services/deal_close_Details.php?data_city=".$data_city."&parentid=".$parentid."&module=me&action=fetchDealClosedetails";
		}else{
			$paramsarr['url'] = JDBOX_API."/services/deal_close_Details.php?data_city=".$data_city."&parentid=".$parentid."&module=me&action=fetchDealClosedetails";
		}
		$paramsarr['formate'] = 'basic';
		$paramsarr['method'] = 'get';
		$paramsarr['postData'] = $paramsarr;
		$getLastDealClose_data = Utility::curlCall($paramsarr);
		return $getLastDealClose_data;
	}
	
	public function getAreaXHR() {
        header('Content-Type: application/json');
        if(isset($_REQUEST['urlFlag'])){
			$params = $_REQUEST;
		}else{
			$params =   json_decode(file_get_contents('php://input'),1);
		}
        
       
       
        $postDataArr    =   array();
        $postDataArr['city']    =   $params['city'];
        if(in_array(strtolower($params['server_city']),$this->cityArr)) {
            $postDataArr['server_city'] = $params['server_city'];
        } else {
            $postDataArr['server_city'] = "remote";
        }
        $postDataArr['s_deptCity']  	=   $params['server_city'];
        $postDataArr['search']  		=   $params['search'];
        $postDataArr['pincode'] 		=   $params['pincode'];
        $retArr =   array();
        $retArr['data'] =   array();
        $curlParams = array();
        $curlParams['url']              =   'http://192.168.22.103/me_services/bformInfo/area_master_auto';
        $curlParams['formate']          =   'basic';
        $curlParams['method']           =   'POST';
        $curlParams['postData']         =   json_encode($postDataArr);
        $curlParams['headerJson']       =   'json';
        $singleCheck                    =   Utility::curlCall($curlParams);
        return $singleCheck;
    }
    
    // New API Call to the List of JDAs form Ronak's API
    // Parameters Passed :- pincode
    // URL :- http://192.168.22.103/presentation/dashboard_services/dashboard/fetchPincodeWiseEmployee?pincode=410208
    // Method used is GET
    // Created by Apoorv Agrawal
    // Reason :- Refernce Table Changed
    private function getJDAAPI( $paramsArr = array() ){
		$curlParams = array();
		$jda_empcode_list = '';
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])) {
			$curlParams['url'] = 'http://apoorva.jdsoftware.com/MEGENIO/presentation/dashboard_services/dashboard/fetchPincodeWiseEmployee?pincode='.$paramsArr['pincode'].''; // Development URL
		}else{
			$curlParams['url'] = 'http://192.168.20.17/presentation/dashboard_services/dashboard/fetchPincodeWiseEmployee?pincode='.$paramsArr['pincode'].'';
			// Live URl
		}
        $curlParams['formate'] = 'basic';
        $curlParams['method'] = 'GET';
        $apiResp = Utility::curlCall($curlParams);
        $JDA_details_arr = array();
        $JDA_details_arr = json_decode($apiResp, true);
        $jda_empcode_list_arr = array();
        if($JDA_details_arr['error']['code'] == 0){
			// Do the processing here
			foreach($JDA_details_arr['result']['data'] as $key=>$data){
				$jda_empcode_list_arr['ME'][] = $data['employee_id'];
			}
		}
		return $jda_empcode_list_arr;        
	}
	
}
?>
