<?php
class live_data_class extends DB
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
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']); 
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($ucode)=='')
		{
			$message = "Ucode is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($uname)=='')
		{
			$message = "Uname is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->uname  	  	= $uname;
		/*mongo*/
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj  = new companyClass();
		$this->setServers();
		
		if( strtoupper($this->module) != 'DE' )
		{
			$this->version		= $this->getversion();
			//downsell status check start
			$downsel	=	json_decode($this->checkDownselstatus(),1);
			$statDown		=	'';
			if($downsel['error']	==	4 || $downsel['error']	==	0 || $downsel['error']	==	'0'){
				if($downsel['status']	==	1){
					$statDown	=	'Approved';
				}else if($downsel['status']	==	0){
					$statDown	=	'Pending';
				}
				$message = 'This Contract Requested For DownSell and it is '.$statDown.'. You Cannot proceed to edit the Contract!!.';
				echo json_encode($this->sendDownDieMessage($message));
				die();
			}
		}
		//downsell status check end
		
		
		
		
		$valid_modules_arr = array("TME","ME","JDA","DE");
		if(!in_array($this->module,$valid_modules_arr)){
			$message = "This service is only applicable for DE/TME/ME/JDA module.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$configcls_obj		= new configclass();
		$urldetails			= $configcls_obj->get_url($this->data_city);
		$this->cs_url		= $urldetails['url'];
		$this->jdbox_url	= $urldetails['jdbox_url'];
		$this->debug_mode		= 	0;
		if($params['trace']==1){
			$this->debug_mode	= 	1;
			print"<pre>";print_r($params);
		}
	}
	
	//get version
	function getversion()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['table']          = "tbl_temp_intermediate";
			$mongo_inputs['fields']         = "version";
			$summary_version_arr	 			= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$summary_version_sql 	="select version from tbl_temp_intermediate where parentid='".$this->parentid."'";
			$summary_version_rs = parent::execQuery($summary_version_sql, $this->conn_temp);			
			$summary_version_arr = mysql_fetch_assoc($summary_version_rs);
		}
		$result = $summary_version_arr['version'];
		return $result;
	}
	
	// check downsel status
	function checkDownselstatus()
	{
		$downArr 		= array();
		if($this->version!=''){
			$sqlDownsel 	= "SELECT status,delete_flag,request_type,dealclose_flag,module FROM online_regis.downsell_trn WHERE parentid='$this->parentid' AND version='$this->version' AND delete_flag!=1 ORDER BY updated_at DESC LIMIT 1"; 
			$resDownsel		=	parent::execQuery($sqlDownsel, $this->conn_idc);
			$num			=	parent::numRows($resDownsel);
			if($num > 0){
				while($row_down	  =	parent::fetchData($resDownsel)){
					 $status = $row_down['status'];
					 $flag   = $row_down['delete_flag'];
					 $rqsttyp= $row_down['request_type'];
					 $dealclose_flag	= $row_down['dealclose_flag'];
					 $module			= $row_down['module'];
				}
				if($flag==1){
					 $downArr = json_encode(array('error' => 3, 'msg' => 'It is a deleted request', 'type'=>$rqsttyp, 'status'=>$status));
				}else if($dealclose_flag	==	2 && $status == 1){
					 $downArr = json_encode(array('error' => 3, 'msg' => 'It is a Dealclosed Request', 'type'=>$rqsttyp, 'status'=>$status));
				}else if(strtolower($module)	==	'geniolite' && $status == 1){
					 $downArr = json_encode(array('error' => 5, 'msg' => 'Allow as it is from geniolite', 'type'=>$rqsttyp, 'status'=>$status));
				}else if($status==2){
					 $downArr = json_encode(array('error' => 3, 'msg' => 'It is a rejected request', 'type'=>$rqsttyp, 'status'=>$status));
				}else if($status==0){
					 $downArr = json_encode(array('error' => 4, 'msg' => 'Pending request', 'type'=>$rqsttyp, 'status'=>$status));
				}else if($dealclose_flag	!=	2 && $status==1  && strtolower($module)	!=	'geniolite'){
					$downArr = json_encode(array('error' => 0, 'msg' => 'It is an approved request', 'type'=>$rqsttyp, 'status'=>$status));
				}
			}else{
				 $downArr = json_encode(array('error' => 3, 'msg' => 'No record found'));
			}
		}else{
			$downArr = json_encode(array('error' => 5, 'msg' => 'No Version'));
		}
		return $downArr;
	}
	
	
	// Function to set DB connection objects
	function setServers()
	{
		global $db;
		$this->conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$this->conn_city]['iro']['master'];
		$this->conn_local  		= $db[$this->conn_city]['d_jds']['master'];
		$this->conn_idc   		= $db[$this->conn_city]['idc']['master'];
		$this->conn_national   	= $db['db_national'];
		$this->conn_tme  		= $db[$this->conn_city]['tme_jds']['master'];
		$this->conn_fin  		= $db[$this->conn_city]['fin']['master'];
		if(strtoupper($this->module) == 'ME')
		{
			$this->conn_temp = $this->conn_idc;
			$this->mongo_flag = 1;
		}
		
		if(strtoupper($this->module) == 'TME')
		{
			$this->conn_temp     = $this->conn_tme;
			$this->mongo_tme = 1;
		}
		
	}
	function populateTempTables()
	{
		/* Mongo data insertion - start */
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
		}
		/*mongo*/
		
		if(strtoupper($this->module) == 'DE')/*check if contract has been already edited by some other user*/
		{
			$sql_ext_shadow = "SELECT updatedBy,updatedOn,ROUND(TIME_TO_SEC((TIMEDIFF(NOW(), updatedon))) / 60) AS diff 
							   FROM db_iro.tbl_companymaster_extradetails_shadow 
							   WHERE parentid='".$this->parentid."'
							   AND updatedBy !=''
							   AND updatedBy !='".$this->ucode."'
							   AND ROUND(TIME_TO_SEC((TIMEDIFF(NOW(), updatedon))) / 60) < 5
								";
			$res_ext_shadow = parent::execQuery($sql_ext_shadow, $this->conn_iro);
			
			if($res_ext_shadow && mysql_num_rows($res_ext_shadow))
			{
				$row_ext_shadow = mysql_fetch_assoc($res_ext_shadow);
				
				if(trim($row_ext_shadow['updatedBy']))
				{
					
					$sso_curl_url	    =	SSO_IP.":8080/api/getEmployee_xhr.php?auth_token=".urlencode("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
					$sso_data['isJson'] 	= 	1;
					$sso_data['textSearch'] = 	4;	
					$sso_data['empcode']  	= 	strtolower(trim($row_ext_shadow['updatedBy']));	
					//$sso_resp 		= 	json_decode(curlCall($sso_curl_url,$sso_data),true);
					$sso_resp 		= 	json_decode($this->curlCall($sso_curl_url,$sso_data),true);
					/*check for active ecs/si - start*/
					
				}
	
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg']  =  $sso_resp['data'][0]['empname']." ( ".$row_ext_shadow['updatedBy']." ) is already editing the contract. Please check it after ".( 5 - $row_ext_shadow['diff'] )." mins";
				
				return $result_msg_arr;
			}
			
		}
		
		/*check if its an active contract - TME*/
		 if(strtoupper($this->module) == 'TME')
		 {
			$sso_curl_url	    =	SSO_IP.":8080/api/getEmployee_xhr.php?auth_token=".urlencode("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
			$sso_data['isJson'] 	= 	1;
			$sso_data['textSearch'] = 	4;	
			$sso_data['empcode']  	= 	strtolower($this->ucode);	
						
			//$sso_resp 		= 	json_decode(curlCall($sso_curl_url,$sso_data),true);
			$sso_resp 		= 	json_decode($this->curlCall($sso_curl_url,$sso_data),true);
			/*check for active ecs/si - start*/

			//in_array(strtolower($data_city_ses['data_city']),array("mumbai","bangalore","chennai","ahmedabad","pune","coimbatore"))

			if((strtolower(trim($this->data_city)) != 'delhi') && (count($sso_resp)>0 && !in_array(strtolower($sso_resp['data'][0]['team_type']),array("bd","rd"))))
			{
				
				$sql_fin = "SELECT balance, manual_override, expired  FROM tbl_companymaster_finance WHERE parentid='".$this->parentid."' AND campaignid IN (1,2) AND (balance>0 OR (manual_override =1 AND expired = 0))";
				//$res_fin  = $conn_finance ->query_sql($sql_fin);
				$res_fin 	= parent::execQuery($sql_fin, $this->conn_fin);
				 
				if( ($res_fin && mysql_num_rows($res_fin)) )
				{
					
						$ecs_qry="SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."' AND activeflag = 1 AND deactiveflag = 0 AND ecs_stop_flag = 0 AND ( mandate_type IS NULL  OR mandate_type='' OR mandate_type='JDA' ) LIMIT 1 ";
						//$resecs  = $conn_finance ->query_sql($ecs_qry);
						$resecs = parent::execQuery($ecs_qry, $this->conn_fin);
			   
						$si_qry="SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='".$this->parentid."' AND activeflag = 1 and deactiveflag = 0 and ecs_stop_flag = 0 AND ( mandate_type IS NULL  OR mandate_type='' OR mandate_type='JDA' ) LIMIT 1";
						//$ressi  = $conn_finance ->query_sql($si_qry);
						$ressi = parent::execQuery($si_qry, $this->conn_fin);
						
						$sql_is_allowed = "SELECT * FROM db_jda.tbl_ecs_contract_edit  WHERE parentid = '".$this->parentid."'";
						$res_is_allowed = parent::execQuery($sql_is_allowed, $this->conn_idc);
						if(mysql_num_rows($res_is_allowed) <= 0 && (mysql_num_rows($resecs) > 0 || mysql_num_rows($ressi) > 0) )
						{
							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = "Active ECS contract is blocked !";
							//print_r($result_msg_arr);
							return $result_msg_arr;
						}
						
					
				}
			}
		}
		/*check if its an active contract - TME*/
		
		#Step 1 : Populating 17.233 Temp Tables
		
		$debug_resp = array();
		if($this->debug_mode==1){
			$start_time_1 = date('H:i:s');
			$debug_resp['Process Start Time'] = date('H:i:s');
		}
		$edit_flag = 0;
												/*:::::::: tbl_companymaster_generalinfo ::::::::*/
		//~ $sqlFetchGenInfo = " SELECT nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,
		//~ landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,landline_display,landline_feedback,mobile,	mobile_display,	mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,
		//~ blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,hide_address,data_city,mobile_admin FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		//~ $resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,	landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,landline_display,landline_feedback,mobile,	mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,hide_address,data_city,mobile_admin';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'live_data_class';

		$comp_api_res  	='';
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
				
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1'){
			$edit_flag = 1;
			//$row_gen_info	=	parent::fetchData($resFetchGenInfo);
			$row_gen_info=	$comp_api_arr['results']['data'][$this->parentid];			
			
			/*mongo*/
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt = array();
				$geninfo_upt['companyname'] 				= stripslashes($row_gen_info['companyname']);
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
				$geninfo_ins['regionid'] 	= $row_gen_info['regionid'];
				$mongo_data[$geninfo_tbl]['insertdata'] = $geninfo_ins;
			}
			else
			{	
				$row_gen_info 	= 	$this->addslashesArray($row_gen_info);			
				$sqlGenInfoShadow 	 = "INSERT INTO tbl_companymaster_generalinfo_shadow
									SET
									nationalid				=	'".$row_gen_info['nationalid']."',
									sphinx_id				=	'".$row_gen_info['sphinx_id']."',
									regionid				=	'".$row_gen_info['regionid']."',
									companyname				=	'".$row_gen_info['companyname']."',
									parentid				=	'".$row_gen_info['parentid']."',
									country					=	'".$row_gen_info['country']."',
									state					=	'".$row_gen_info['state']."',
									city					=	'".$row_gen_info['city']."',
									display_city			=	'".$row_gen_info['display_city']."',
									area					=	'".$row_gen_info['area']."',
									subarea					=	'".$row_gen_info['subarea']."',
									office_no				=	'".$row_gen_info['office_no']."',
									building_name			=	'".$row_gen_info['building_name']."',
									street					=	'".$row_gen_info['street']."',
									street_direction		=	'".$row_gen_info['street_direction']."',
									street_suffix			=	'".$row_gen_info['street_suffix']."',
									landmark				=	'".$row_gen_info['landmark']."',
									landmark_custom			=	'".$row_gen_info['landmark_custom']."',
									pincode					=	'".$row_gen_info['pincode']."',
									pincode_addinfo			=	'".$row_gen_info['pincode_addinfo']."',
									latitude				=	'".$row_gen_info['latitude']."',
									longitude				=	'".$row_gen_info['longitude']."',
									geocode_accuracy_level	=	'".$row_gen_info['geocode_accuracy_level']."',
									full_address			=	'".$row_gen_info['full_address']."',
									stdcode					=	'".$row_gen_info['stdcode']."',
									landline				=	'".$row_gen_info['landline']."',
									landline_display		=	'".$row_gen_info['landline_display']."',
									landline_feedback		=	'".$row_gen_info['landline_feedback']."',
									mobile					=	'".$row_gen_info['mobile']."',
									mobile_display			=	'".$row_gen_info['mobile_display']."',
									mobile_feedback			=	'".$row_gen_info['mobile_feedback']."',
									fax						=	'".$row_gen_info['fax']."',
									tollfree				=	'".$row_gen_info['tollfree']."',
									tollfree_display		=	'".$row_gen_info['tollfree_display']."',
									email					=	'".$row_gen_info['email']."',
									email_display			=	'".$row_gen_info['email_display']."',
									email_feedback			=	'".$row_gen_info['email_feedback']."',
									sms_scode				=	'".$row_gen_info['sms_scode']."',
									website					=	'".$row_gen_info['website']."',
									contact_person			=	'".$row_gen_info['contact_person']."',
									contact_person_display	=	'".$row_gen_info['contact_person_display']."',
									callconnect				=	'".$row_gen_info['callconnect']."',
									virtualNumber			=	'".$row_gen_info['virtualNumber']."',
									virtual_mapped_number	=	'".$row_gen_info['virtual_mapped_number']."',
									blockforvirtual			=	'".$row_gen_info['blockforvirtual']."',
									othercity_number		=	'".$row_gen_info['othercity_number']."',
									paid					=	'".$row_gen_info['paid']."',
									displayType				=	'".$row_gen_info['displayType']."',
									company_callcnt			=	'".$row_gen_info['company_callcnt']."',
									company_callcnt_rolling	=	'".$row_gen_info['company_callcnt_rolling']."',
									hide_address			=	'".$row_gen_info['hide_address']."',
									data_city				=	'".$row_gen_info['data_city']."',
									mobile_admin			=	'".$row_gen_info['mobile_admin']."'
									
									ON DUPLICATE KEY UPDATE
									
									companyname				=	'".$row_gen_info['companyname']."',
									country					=	'".$row_gen_info['country']."',
									state					=	'".$row_gen_info['state']."',
									city					=	'".$row_gen_info['city']."',
									display_city			=	'".$row_gen_info['display_city']."',
									area					=	'".$row_gen_info['area']."',
									subarea					=	'".$row_gen_info['subarea']."',
									office_no				=	'".$row_gen_info['office_no']."',
									building_name			=	'".$row_gen_info['building_name']."',
									street					=	'".$row_gen_info['street']."',
									street_direction		=	'".$row_gen_info['street_direction']."',
									street_suffix			=	'".$row_gen_info['street_suffix']."',
									landmark				=	'".$row_gen_info['landmark']."',
									landmark_custom			=	'".$row_gen_info['landmark_custom']."',
									pincode					=	'".$row_gen_info['pincode']."',
									pincode_addinfo			=	'".$row_gen_info['pincode_addinfo']."',
									latitude				=	'".$row_gen_info['latitude']."',
									longitude				=	'".$row_gen_info['longitude']."',
									geocode_accuracy_level	=	'".$row_gen_info['geocode_accuracy_level']."',
									full_address			=	'".$row_gen_info['full_address']."',
									stdcode					=	'".$row_gen_info['stdcode']."',
									landline				=	'".$row_gen_info['landline']."',
									landline_display		=	'".$row_gen_info['landline_display']."',
									landline_feedback		=	'".$row_gen_info['landline_feedback']."',
									mobile					=	'".$row_gen_info['mobile']."',
									mobile_display			=	'".$row_gen_info['mobile_display']."',
									mobile_feedback			=	'".$row_gen_info['mobile_feedback']."',
									fax						=	'".$row_gen_info['fax']."',
									tollfree				=	'".$row_gen_info['tollfree']."',
									tollfree_display		=	'".$row_gen_info['tollfree_display']."',
									email					=	'".$row_gen_info['email']."',
									email_display			=	'".$row_gen_info['email_display']."',
									email_feedback			=	'".$row_gen_info['email_feedback']."',
									sms_scode				=	'".$row_gen_info['sms_scode']."',
									website					=	'".$row_gen_info['website']."',
									contact_person			=	'".$row_gen_info['contact_person']."',
									contact_person_display	=	'".$row_gen_info['contact_person_display']."',
									callconnect				=	'".$row_gen_info['callconnect']."',
									virtualNumber			=	'".$row_gen_info['virtualNumber']."',
									virtual_mapped_number	=	'".$row_gen_info['virtual_mapped_number']."',
									blockforvirtual			=	'".$row_gen_info['blockforvirtual']."',
									othercity_number		=	'".$row_gen_info['othercity_number']."',
									paid					=	'".$row_gen_info['paid']."',
									displayType				=	'".$row_gen_info['displayType']."',
									company_callcnt			=	'".$row_gen_info['company_callcnt']."',
									company_callcnt_rolling	=	'".$row_gen_info['company_callcnt_rolling']."',
									hide_address			=	'".$row_gen_info['hide_address']."',
									data_city				=	'".$row_gen_info['data_city']."',
									mobile_admin			=	'".$row_gen_info['mobile_admin']."'";
			
				if(strtoupper($this->module) == 'TME')
				{
					$sqlGenInfoShadow	= $sqlGenInfoShadow."/* TMEMONGOQRY */";
				   $resGenInfoShadow 	= parent::execQuery($sqlGenInfoShadow, $this->conn_tme);
				}
				else if(strtoupper($this->module) == 'DE')
				{
				   $resGenInfoShadow 	= parent::execQuery($sqlGenInfoShadow, $this->conn_iro);
				}
				else 
				{
					$resGenInfoShadow 	= parent::execQuery($sqlGenInfoShadow, $this->conn_idc);
				}
			}
		}
												/*:::::::: tbl_companymaster_extradetails ::::::::*/
												
		//~ $sqlFetchExtraDetails = "SELECT nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,attribute_search,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,	web_ratings,number_of_reviews,group_id,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,createdby,createdtime,original_creator,original_date,updatedBy,updatedOn,map_pointer_flags,flags,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,award,testimonial,proof_establishment,data_city, closedown_flag,tag_catid,tag_catname FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		//$resFetchExtraDetails 	= parent::execQuery($sqlFetchExtraDetails, $this->conn_iro);
		
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,attribute_search,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,createdby,createdtime,original_creator,original_date,updatedBy,updatedOn,map_pointer_flags,flags,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,award,testimonial,proof_establishment,data_city,closedown_flag,tag_catid,tag_catname,social_media_url,misc_flag';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'live_data_class';

		$comp_api_res  	='';
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1'){
			//$row_ext_details =	parent::fetchData($resFetchExtraDetails);
			$row_ext_details =	$comp_api_arr['results']['data'][$this->parentid];
			
			$live_catlin_arr = array();
			$live_catlin_arr = explode("/,/",trim($row_ext_details['catidlineage'],"/"));
			$live_catlin_arr = array_filter($live_catlin_arr);
			$live_catlin_arr = 	$this->getValidCategories($live_catlin_arr);
			
			$paid_catids_arr = $this->getCategoryDetails($live_catlin_arr);
			
			if(count($paid_catids_arr)>0){
				foreach($paid_catids_arr as $catid => $catinfo_arr){
					$catname 		= trim($catinfo_arr['catname']);
					$national_catid = trim($catinfo_arr['national_catid']);
					
					$catids_arr[] 			= $catid;
					$catnames_arr[] 		= $catname;
					$national_catids_arr[] 	= $national_catid;
				}
				$catnames_str = "|P|";
				$catnames_str .= implode("|P|",$catnames_arr);
				
				$catids_str = "|P|";
				$catids_str .= implode("|P|",$catids_arr);
				
				$national_catids_str = "|P|";
				$national_catids_str .= implode("|P|",$national_catids_arr);
				
				$catSelected = str_ireplace("|P|","|~|",$catnames_str);
				
			}else{
				$catnames_str = "";
				$catids_str = "";
				$national_catids_str = "";
				$catSelected = "";
			}
			
			/*mongo*/
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$extrdet_tbl = "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['companyname'] 					= stripslashes($row_ext_details['companyname']);
				$extrdet_upt['landline_addinfo'] 				= $row_ext_details['landline_addinfo'];
				$extrdet_upt['mobile_addinfo'] 					= $row_ext_details['mobile_addinfo'];
				$extrdet_upt['tollfree_addinfo'] 				= $row_ext_details['tollfree_addinfo'];					
				$extrdet_upt['contact_person_addinfo'] 			= $row_ext_details['contact_person_addinfo'];
				$extrdet_upt['attributes'] 						= $row_ext_details['attributes'];
				$extrdet_upt['attributes_edit'] 				= $row_ext_details['attributes_edit'];
				$extrdet_upt['attribute_search'] 				= $row_ext_details['attribute_search'];
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
				$extrdet_upt['data_city']			 			= $row_ext_details['data_city'];
				$extrdet_upt['social_media_url']			 	= $row_ext_details['social_media_url'];
				
				
				
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
				
				/*:::::::: tbl_business_temp_data ::::::::*/
				
				$bustemp_tbl = "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['companyName'] 			= $row_gen_info['companyname'];
				$bustemp_upt['mainattr'] 				= $row_ext_details['attributes'];
				$bustemp_upt['facility'] 				= $row_ext_details['attributes_edit'];
				$bustemp_upt['categories'] 				= $catnames_str;
				$bustemp_upt['catIds'] 					= $catids_str;
				$bustemp_upt['nationalcatIds'] 			= $national_catids_str;
				$bustemp_upt['catSelected'] 			= $catSelected;
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
			}
			else
			{
				$row_ext_details = $this->addslashesArray($row_ext_details);
				$sqlExtraDetailsShadow = "INSERT INTO tbl_companymaster_extradetails_shadow
									SET
									nationalid 						= 	'".$row_ext_details['nationalid']."',
									sphinx_id 						= 	'".$row_ext_details['sphinx_id']."',
									regionid 						=	'".$row_ext_details['regionid']."',
									companyname 					= 	'".$row_ext_details['companyname']."',
									parentid 						= 	'".$row_ext_details['parentid']."',
									landline_addinfo 				= 	'".$row_ext_details['landline_addinfo']."',
									mobile_addinfo 					= 	'".$row_ext_details['mobile_addinfo']."',
									tollfree_addinfo 				=	'".$row_ext_details['tollfree_addinfo']."',
									contact_person_addinfo 			= 	'".$row_ext_details['contact_person_addinfo']."',
									attributes 						= 	'".$row_ext_details['attributes']."',
									attributes_edit 				= 	'".$row_ext_details['attributes_edit']."',
									attribute_search 				= 	'".$row_ext_details['attribute_search']."',
									turnover 						= 	'".$row_ext_details['turnover']."',
									working_time_start 				= 	'".$row_ext_details['working_time_start']."',
									working_time_end 				= 	'".$row_ext_details['working_time_end']."',
									payment_type 					= 	'".$row_ext_details['payment_type']."',
									year_establishment 				= 	'".$row_ext_details['year_establishment']."',
									/*accreditations 				= 	'".$row_ext_details['accreditations']."',*/
									certificates 					= 	'".$row_ext_details['certificates']."',
									no_employee 					= 	'".$row_ext_details['no_employee']."',
									tag_catid 						= 	'".$row_ext_details['tag_catid']."',
									tag_catname 					= 	'".$row_ext_details['tag_catname']."',
									business_group 					= 	'".$row_ext_details['business_group']."',
									email_feedback_freq 			= 	'".$row_ext_details['email_feedback_freq']."',
									statement_flag 					= 	'".$row_ext_details['statement_flag']."',
									alsoServeFlag 					= 	'".$row_ext_details['alsoServeFlag']."',
									averageRating 					=	'".$row_ext_details['averageRating']."',
									ratings 						= 	'".$row_ext_details['ratings']."',
									web_ratings 					= 	'".$row_ext_details['web_ratings']."',
									number_of_reviews 				= 	'".$row_ext_details['number_of_reviews']."',
									group_id 						= 	'".$row_ext_details['group_id']."',
									guarantee						= 	'".$row_ext_details['guarantee']."',
									Jdright 						= 	'".$row_ext_details['Jdright']."',
									LifestyleTag 					= 	'".$row_ext_details['LifestyleTag']."',
									contract_calltype 				= 	'".$row_ext_details['contract_calltype']."',
									batch_group 					= 	'".$row_ext_details['batch_group']."',
									audit_status 					= 	'".$row_ext_details['audit_status']."',
									customerID 						= 	'".$row_ext_details['customerID']."',
									datavalidity_flag 				= 	'".$row_ext_details['datavalidity_flag']."',
									deactflg 						= 	'".$row_ext_details['deactflg']."',
									display_flag 					= 	'".$row_ext_details['display_flag']."',
									fmobile 						= 	'".$row_ext_details['fmobile']."',
									femail 							= 	'".$row_ext_details['femail']."',
									flgActive 						= 	'".$row_ext_details['flgActive']."',
									freeze 							= 	'".$row_ext_details['freeze']."',
									mask 							= 	'".$row_ext_details['mask']."',
									future_contract_flag 			= 	'".$row_ext_details['future_contract_flag']."',
									hidden_flag 					= 	'".$row_ext_details['hidden_flag']."',
									lockDateTime 					= 	'".$row_ext_details['lockDateTime']."',
									lockedBy 						= 	'".$row_ext_details['lockedBy']."',
									temp_deactive_start 			= 	'".$row_ext_details['temp_deactive_start']."',
									temp_deactive_end 				= 	'".$row_ext_details['temp_deactive_end']."',
									micrcode 						= 	'".$row_ext_details['micrcode']."',
									prompt_cat_temp 				= 	'".$row_ext_details['prompt_cat_temp']."',
									promptype 						= 	'".$row_ext_details['promptype']."',
									referto 						= 	'".$row_ext_details['referto']."',
									serviceName 					= 	'".$row_ext_details['serviceName']."',
									srcEmp 							= 	'".$row_ext_details['srcEmp']."',
									telComm 						= 	'".$row_ext_details['telComm']."',
									createdby		 				= 	'".$row_ext_details['createdby']."',
									createdtime 					= 	'".$row_ext_details['createdtime']."',
									original_creator 				= 	'".$row_ext_details['original_creator']."',
									original_date 					= 	'".$row_ext_details['original_date']."',
									updatedBy 						= 	'".$this->ucode."',
									updatedOn 						= 	NOW(),
									map_pointer_flags				= 	'".$row_ext_details['map_pointer_flags']."',
									flags							= 	'".$row_ext_details['flags']."',
									catidlineage_nonpaid			= 	'".$row_ext_details['catidlineage_nonpaid']."',
									national_catidlineage_nonpaid	= 	'".$row_ext_details['national_catidlineage_nonpaid']."',
									data_city						= 	'".$row_ext_details['data_city']."',
									award							=	'".addslashes(stripslashes($row_ext_details['award']))."',
									testimonial						=	'".addslashes(stripslashes($row_ext_details['testimonial']))."',
									proof_establishment				=	'".addslashes(stripslashes($row_ext_details['proof_establishment']))."',
									social_media_url				=	'".$row_ext_details['social_media_url']."',
									misc_flag						=	'".$row_ext_details['misc_flag']."',
									closedown_flag					=   '".$row_ext_details['closedown_flag']."'
									ON DUPLICATE KEY UPDATE
									
									companyname 					= 	'".$row_ext_details['companyname']."',
									landline_addinfo 				= 	'".$row_ext_details['landline_addinfo']."',
									mobile_addinfo					= 	'".$row_ext_details['mobile_addinfo']."',
									tollfree_addinfo 				= 	'".$row_ext_details['tollfree_addinfo']."',
									contact_person_addinfo 			= 	'".$row_ext_details['contact_person_addinfo']."',
									attributes 						= 	'".$row_ext_details['attributes']."',
									attributes_edit 				= 	'".$row_ext_details['attributes_edit']."',
									attribute_search 				= 	'".$row_ext_details['attribute_search']."',
									turnover 						= 	'".$row_ext_details['turnover']."',
									working_time_start 				= 	'".$row_ext_details['working_time_start']."',
									working_time_end 				= 	'".$row_ext_details['working_time_end']."',
									payment_type 					= 	'".$row_ext_details['payment_type']."',
									year_establishment 				= 	'".$row_ext_details['year_establishment']."',
									/*accreditations 				= 	'".$row_ext_details['accreditations']."',*/
									certificates 					= 	'".$row_ext_details['certificates']."',
									no_employee 					= 	'".$row_ext_details['no_employee']."',
									tag_catid 						= 	'".$row_ext_details['tag_catid']."',
									tag_catname 					= 	'".$row_ext_details['tag_catname']."',
									business_group 					= 	'".$row_ext_details['business_group']."',
									email_feedback_freq 			= 	'".$row_ext_details['email_feedback_freq']."',
									statement_flag 					= 	'".$row_ext_details['statement_flag']."',
									alsoServeFlag 					= 	'".$row_ext_details['alsoServeFlag']."',
									averageRating 					= 	'".$row_ext_details['averageRating']."',
									ratings 						= 	'".$row_ext_details['ratings']."',
									web_ratings 					= 	'".$row_ext_details['web_ratings']."',
									number_of_reviews 				= 	'".$row_ext_details['number_of_reviews']."',
									group_id 						= 	'".$row_ext_details['group_id']."',
									guarantee 						= 	'".$row_ext_details['guarantee']."',
									Jdright 						= 	'".$row_ext_details['Jdright']."',
									LifestyleTag 					= 	'".$row_ext_details['LifestyleTag']."',
									contract_calltype 				= 	'".$row_ext_details['contract_calltype']."',
									batch_group 					= 	'".$row_ext_details['batch_group']."',
									audit_status 					= 	'".$row_ext_details['audit_status']."',
									createdby 						= 	'".$row_ext_details['createdby']."',
									createdtime 					= 	'".$row_ext_details['createdtime']."',
									customerID 						= 	'".$row_ext_details['customerID']."',
									datavalidity_flag 				= 	'".$row_ext_details['datavalidity_flag']."',
									deactflg 						= 	'".$row_ext_details['deactflg']."',
									display_flag 					= 	'".$row_ext_details['display_flag']."',
									fmobile 						= 	'".$row_ext_details['fmobile']."',
									femail 							= 	'".$row_ext_details['femail']."',
									flgActive 						= 	'".$row_ext_details['flgActive']."',
									freeze 							= 	'".$row_ext_details['freeze']."',
									mask 							= 	'".$row_ext_details['mask']."',
									future_contract_flag 			= 	'".$row_ext_details['future_contract_flag']."',
									hidden_flag 					= 	'".$row_ext_details['hidden_flag']."',
									lockDateTime 					= 	'".$row_ext_details['lockDateTime']."',
									lockedBy 						= 	'".$row_ext_details['lockedBy']."',
									temp_deactive_start 			= 	'".$row_ext_details['temp_deactive_start']."',
									temp_deactive_end 				= 	'".$row_ext_details['temp_deactive_end']."',
									micrcode 						= 	'".$row_ext_details['micrcode']."',
									prompt_cat_temp 				= 	'".$row_ext_details['prompt_cat_temp']."',
									promptype 						= 	'".$row_ext_details['promptype']."',
									referto 						= 	'".$row_ext_details['referto']."',
									serviceName 					= 	'".$row_ext_details['serviceName']."',
									srcEmp 							= 	'".$row_ext_details['srcEmp']."',
									telComm 						= 	'".$row_ext_details['telComm']."',
									updatedBy 						= 	'".$this->ucode."',
									updatedOn 						= 	NOW(),
									map_pointer_flags				= 	'".$row_ext_details['map_pointer_flags']."',
									flags							= 	'".$row_ext_details['flags']."',
									catidlineage_nonpaid			= 	'".$row_ext_details['catidlineage_nonpaid']."',
									national_catidlineage_nonpaid	= 	'".$row_ext_details['national_catidlineage_nonpaid']."',
									data_city						= 	'".$row_ext_details['data_city']."',
									award							=	'".addslashes(stripslashes($row_ext_details['award']))."',
									testimonial						=	'".addslashes(stripslashes($row_ext_details['testimonial']))."',
									proof_establishment				=	'".addslashes(stripslashes($row_ext_details['proof_establishment']))."',
									social_media_url				=	'".$row_ext_details['social_media_url']."',
									misc_flag						=	'".$row_ext_details['misc_flag']."',
									closedown_flag					=   '".$row_ext_details['closedown_flag']."'";
				if(strtoupper($this->module) == 'TME')
				{
				   $sqlExtraDetailsShadow	= $sqlExtraDetailsShadow."/* TMEMONGOQRY */";
				   $resExtraDetailsShadow 	= parent::execQuery($sqlExtraDetailsShadow, $this->conn_tme);
				}
				else if(strtoupper($this->module) == 'DE')
				{
				   $resExtraDetailsShadow 	= parent::execQuery($sqlExtraDetailsShadow, $this->conn_iro);
				}
				else 
				{
					$resExtraDetailsShadow 	= parent::execQuery($sqlExtraDetailsShadow, $this->conn_idc);
				}
				
				
				$sqlBusinessTempData = "INSERT INTO tbl_business_temp_data 
									SET
									contractid		= '".$this->parentid."',
									companyName		= '".$this->stringProcess($row_gen_info['companyname'])."',
									mainattr		= '".$this->stringProcess($row_ext_details['attributes'])."',
									facility		= '".$this->stringProcess($row_ext_details['attributes_edit'])."',
									categories 		= '".$this->stringProcess($catnames_str)."',
									catIds 			= '".$catids_str."',
									nationalcatIds 	= '".$national_catids_str."',
									catSelected 	= '".$this->stringProcess($catSelected)."',
									categories_list	= ''
									
									ON DUPLICATE KEY UPDATE
									
									companyName		= '".$this->stringProcess($row_gen_info['companyname'])."',
									mainattr		= '".$this->stringProcess($row_ext_details['attributes'])."',
									facility		= '".$this->stringProcess($row_ext_details['attributes_edit'])."',
									categories 		= '".$this->stringProcess($catnames_str)."',
									catIds 			= '".$catids_str."',
									nationalcatIds 	= '".$national_catids_str."',
									catSelected 	= '".$this->stringProcess($catSelected)."',
									categories_list	= ''";
				if(strtoupper($this->module) == 'TME')
				{
				   $sqlBusinessTempData		= $sqlBusinessTempData."/* TMEMONGOQRY */";
				   $resBusinessTempData 	= parent::execQuery($sqlBusinessTempData, $this->conn_tme);
				}
				else if(strtoupper($this->module) == 'DE')
				{
					$resBusinessTempData 	= parent::execQuery($sqlBusinessTempData, $this->conn_local);
				}
				else 
				{
					$resBusinessTempData 	= parent::execQuery($sqlBusinessTempData, $this->conn_idc);
				}
			}
		}
		$reasonInfo 	= $this->reasonInfo();
		$addInfo 		= $this->addInfoTxt();
		$sourceInfo 	= $this->sourceInfo();
		$tmeInfo 		= $this->tmeInfo();
		
		$temp_insert_arr = array();
		$temp_update_arr = array();
		$nonpaid = 0;
		if($this->nonpaid == 1){
			$nonpaid = 1;
		}
		
		/*mongo*/
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			$intermd_upt['contract_calltype'] 		= $row_ext_details['contract_calltype'];
			$intermd_upt['displayType'] 			= $row_ext_details['displayType'];
			$intermd_upt['deactivate'] 				= $row_ext_details['deactflg'];
			$intermd_upt['temp_deactive_start'] 	= $row_ext_details['temp_deactive_start'];
			$intermd_upt['temp_deactive_end'] 		= $row_ext_details['temp_deactive_end'];
			$intermd_upt['deactflg'] 				= $row_ext_details['deactflg'];
			$intermd_upt['freez'] 					= $row_ext_details['freeze'];
			$intermd_upt['mask'] 					= $row_ext_details['mask'];
			$intermd_upt['reason_id'] 				= $reasonInfo['reason_id'];
			$intermd_upt['add_infotxt'] 			= $addInfo['add_infotxt'];
			$intermd_upt['mainsource'] 				= $sourceInfo['mainsource'];
			$intermd_upt['subsource'] 				= $sourceInfo['subsource'];
			$intermd_upt['datesource'] 				= $sourceInfo['datesource'];
			$intermd_upt['callconnect'] 			= $row_gen_info['callconnect'];
			$intermd_upt['callconnectid'] 			= $row_gen_info['callconnectid'];
			$intermd_upt['virtualNumber'] 			= $row_gen_info['virtualNumber'];
			$intermd_upt['virtual_mapped_number'] 	= $row_gen_info['virtual_mapped_number'];
			$intermd_upt['actMode'] 				= "1";
			$intermd_upt['facility_flag'] 			= "0";
			$intermd_upt['nonpaid'] 				= $nonpaid;
			$intermd_upt['empcode']					= $tmeInfo['employeeCode'];
			$intermd_upt['name_code'] 				= $tmeInfo['iroCode'];
			$intermd_upt['txtTE'] 					= $tmeInfo['meCode'];
			$intermd_upt['txtM'] 					= $tmeInfo['mCode'];
			$intermd_upt['txtME'] 					= $tmeInfo['tmeCode'];
			$intermd_upt['reason_text'] 			= $reasonInfo['reason_text'];
			$intermd_upt['assignTmeCode'] 			= $tmeInfo['tmeCode'];
			$intermd_upt['blockforvirtual'] 		= $row_gen_info['blockforvirtual'];
			$intermd_upt['generatexml'] 			= "1";
			$intermd_upt['cat_reset_flag'] 			= "0";
			$intermd_ins = array();
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			
			$intermd_ins = array();
			$intermd_ins['tme_code'] 				= $tmeInfo['tmeCode'];
			$mongo_data[$intermd_tbl]['insertdata'] = $intermd_ins;
			
			$mongo_inputs['table_data'] = $mongo_data;
			$res = $this->mongo_obj->updateData($mongo_inputs);
			$this->insertTextLog($mongo_data);
		}
		else
		{
			
			if(strtolower($row_ext_details['deactflg'])=="y" || strtolower($row_ext_details['deactflg'])=="f")
			{
				$flgDeactive = 1;
				$deactivate = "FREEZ";
			}
			else
			{ 
				$flgDeactive = 0; 
				$deactivate = $row_ext_details['deactflg'];
			}
			
		
			$flagCheck = $row_ext_details['flags']&512;
			
			if($flagCheck == 512)
			{
				$flags = 1;
			}
			else
			{
				$flags = 0;
			}		
			
			
			
			
			$temp_insert_arr[] = "parentid = '".$this->parentid."'";
			$temp_insert_arr[] = "contract_calltype = '".$this->stringProcess($row_ext_details['contract_calltype'])."'";
			$temp_insert_arr[] = "displayType = '".$this->stringProcess($row_ext_details['displayType'])."'";
			$temp_insert_arr[] = "deactivate = '".$deactivate."'";
			$temp_insert_arr[] = "temp_deactive_start = '".$row_ext_details['temp_deactive_start']."'";
			$temp_insert_arr[] = "temp_deactive_end = '".$row_ext_details['temp_deactive_end']."'";
			$temp_insert_arr[] = "deactflg = '".$flgDeactive."'";
			$temp_insert_arr[] = "freez = '".$row_ext_details['freeze']."'";
			$temp_insert_arr[] = "mask = '".$row_ext_details['mask']."'";
			$temp_insert_arr[] = "reason_id = '".$reasonInfo['reason_id']."'";
			$temp_insert_arr[] = "add_infotxt = '".$this->stringProcess($addInfo['add_infotxt'])."'";
			//$temp_insert_arr[] = "mainsource = '".$this->stringProcess($sourceInfo['mainsource'])."'";
			//$temp_insert_arr[] = "subsource = '".$this->stringProcess($sourceInfo['subsource'])."'";
			//$temp_insert_arr[] = "datesource = '".$sourceInfo['datesource']."'";
			$temp_insert_arr[] = "callconnect = '".$row_gen_info['callconnect']."'";
			$temp_insert_arr[] = "callconnectid = '".$row_gen_info['callconnectid']."'";
			$temp_insert_arr[] = "virtualNumber = '".$row_gen_info['virtualNumber']."'";
			$temp_insert_arr[] = "virtual_mapped_number = '".$row_gen_info['virtual_mapped_number']."'";
			$temp_insert_arr[] = "actMode = '1'";
			$temp_insert_arr[] = "facility_flag = '0'";
			$temp_insert_arr[] = "nonpaid = '".$nonpaid."'";
			$temp_insert_arr[] = "empcode = '".$tmeInfo['employeeCode']."'";
			$temp_insert_arr[] = "name_code = '".$tmeInfo['iroCode']."'";
			$temp_insert_arr[] = "txtTE = '".$tmeInfo['meCode']."'";
			$temp_insert_arr[] = "txtM = '".$tmeInfo['mCode']."'";
			$temp_insert_arr[] = "txtME = '".$tmeInfo['tmeCode']."'";
			$temp_insert_arr[] = "reason_text = '".$this->stringProcess($reasonInfo['reason_text'])."'";
			$temp_insert_arr[] = "assignTmeCode = '".$tmeInfo['tmeCode']."'";
			$temp_insert_arr[] = "tme_code = '".$tmeInfo['tmeCode']."'";
			$temp_insert_arr[] = "blockforvirtual = '".$row_gen_info['blockforvirtual']."'";
			$temp_insert_arr[] = "generatexml = '1'";
			$temp_insert_arr[] = "cat_reset_flag = '0'";
			$temp_insert_arr[] = "dotcom = '".$flags."'";
			
			
			
			$temp_update_arr[] = "contract_calltype = '".$this->stringProcess($row_ext_details['contract_calltype'])."'";
			$temp_update_arr[] = "displayType = '".$this->stringProcess($row_ext_details['displayType'])."'";
			$temp_update_arr[] = "deactivate = '".$deactivate."'";
			$temp_update_arr[] = "temp_deactive_start = '".$row_ext_details['temp_deactive_start']."'";
			$temp_update_arr[] = "temp_deactive_end = '".$row_ext_details['temp_deactive_end']."'";
			$temp_update_arr[] = "deactflg = '".$flgDeactive."'";
			$temp_update_arr[] = "freez = '".$row_ext_details['freeze']."'";
			$temp_update_arr[] = "mask = '".$row_ext_details['mask']."'";
			$temp_update_arr[] = "reason_id = '".$reasonInfo['reason_id']."'";
			$temp_update_arr[] = "add_infotxt = '".$this->stringProcess($addInfo['add_infotxt'])."'";
			$temp_update_arr[] = "mainsource = ''";
			$temp_update_arr[] = "subsource = ''";
			$temp_update_arr[] = "datesource = ''";
			$temp_update_arr[] = "callconnect = '".$row_gen_info['callconnect']."'";
			$temp_update_arr[] = "callconnectid = '".$row_gen_info['callconnectid']."'";
			$temp_update_arr[] = "virtualNumber = '".$row_gen_info['virtualNumber']."'";
			$temp_update_arr[] = "virtual_mapped_number = '".$row_gen_info['virtual_mapped_number']."'";
			$temp_update_arr[] = "actMode = '1'";
			$temp_update_arr[] = "facility_flag = '0'";
			$temp_update_arr[] = "nonpaid = '".$nonpaid."'";
			$temp_update_arr[] = "empcode = '".$tmeInfo['employeeCode']."'";
			$temp_update_arr[] = "name_code = '".$tmeInfo['iroCode']."'";
			$temp_update_arr[] = "txtTE = '".$tmeInfo['meCode']."'";
			$temp_update_arr[] = "txtM = '".$tmeInfo['mCode']."'";
			$temp_update_arr[] = "txtME = '".$tmeInfo['tmeCode']."'";
			$temp_update_arr[] = "reason_text = '".$this->stringProcess($reasonInfo['reason_text'])."'";
			$temp_update_arr[] = "assignTmeCode = '".$tmeInfo['tmeCode']."'";
			$temp_update_arr[] = "blockforvirtual = '".$row_gen_info['blockforvirtual']."'";
			$temp_update_arr[] = "generatexml = '1'";
			$temp_update_arr[] = "cat_reset_flag = '0'";
			$temp_update_arr[] = "narration = ''";
			$temp_update_arr[] = "dotcom = '".$flags."'";
			
			
			$sql_str_ins_inter		=	implode(",",$temp_insert_arr);
			$sql_str_updt_inter		=	implode(",",$temp_update_arr);
			$query_insert_inter		=	"INSERT INTO tbl_temp_intermediate SET ";
			$query_on_dup_gen		=	" ON DUPLICATE KEY UPDATE ";
			$query_insert_inter    .= 	$sql_str_ins_inter.$query_on_dup_gen.$sql_str_updt_inter;
			
			if(strtoupper($this->module) == 'TME')
			{
			   $query_insert_inter		= $query_insert_inter."/* TMEMONGOQRY */";
			   $result_insert_inter 	= parent::execQuery($query_insert_inter, $this->conn_tme);
			}
			else if(strtoupper($this->module) == 'DE')
			{
			   $query_insert_inter = $query_insert_inter."/* DE Mongo - update mysql query */";
			   $result_insert_inter 	= parent::execQuery($query_insert_inter, $this->conn_local);
			}
			else 
			{
				$result_insert_inter 	= parent::execQuery($query_insert_inter, $this->conn_idc);
			}
		}
		
		
		$this->csFetchInfo($edit_flag); // Updating CS Fetch Info Table
		
		/* -------------here for populating attributes temp tables starts ---- */
		
		/* deleting entry for DE only - raj*/
		if(strtoupper($this->module) == 'DE')
		{
			$deleteExistingData = "DELETE FROM tbl_rest_veg_nonveg_selection_shadow WHERE parentid = '".$this->parentid."'";
			$resultExistingData      = parent::execQuery($deleteExistingData, $this->conn_local);
		}
		
		
		$get_main_enrty = "SELECT * FROM tbl_companymaster_attributes WHERE parentid='".$this->parentid."' ";
		$res_main_entry = parent::execQuery($get_main_enrty, $this->conn_iro);
		if($res_main_entry && parent::numRows($res_main_entry)){
			$del_entry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'" ;
			if(strtoupper($this->module) == 'DE')
			{
				$res_entry      = parent::execQuery($del_entry, $this->conn_local);
			}
			else
			{
				$res_entry      = parent::execQuery($del_entry, $this->conn_temp);
			}
		
			$insert = "INSERT INTO tbl_companymaster_attributes_temp (docid,parentid, city, attribute_name,attribute_dname, attribute_value, attribute_type, attribute_sub_group,sub_group_name,display_flag,sub_group_position,attribute_position,attribute_id,attribute_prefix, main_attribute_flag,main_attribute_position) VALUES ";
			while($row_main_data = parent::fetchData($res_main_entry)){
				if($row_main_data['parentid']!='' && $row_main_data['attribute_name']!='' && $row_main_data['attribute_id']!=''){
					$row_main_data 	= 	$this->addslashesArray($row_main_data);
					$insert1 .= " ('".$row_main_data['docid']."', '".$this->parentid."', '".$row_main_data['city']."', '".addslashes($row_main_data['attribute_name'])."', '".addslashes($row_main_data['attribute_dname'])."', '".$row_main_data['attribute_value']."', '".$row_main_data['attribute_type']."' ,'".$row_main_data['attribute_sub_group']."', '".addslashes($row_main_data['sub_group_name'])."',  '".$row_main_data['display_flag']."',  '".$row_main_data['sub_group_position']."','".$row_main_data['attribute_position']."', '".$row_main_data['attribute_id']."', '".$row_main_data['attribute_prefix']."',  '".$row_main_data['main_attribute_flag']."' , '".$row_main_data['main_attribute_position']."' ) ".",";	
				}
			}
			if($insert1!=''){
				$insert1 = rtrim($insert1,",");				
				$fin_temp = $insert.$insert1;					
				
				if(strtoupper($this->module) == 'DE')
				{
					$res_insert = parent::execQuery($fin_temp, $this->conn_local);
				}
				else
				{
					$res_insert = parent::execQuery($fin_temp, $this->conn_temp);
				}
			}
		}
		/* -------------here for populating attributes temp tables ends ---- */
		
	  if(strtoupper($this->module) == 'DE')
	  {
		/*---------------populating shadow tables for movie timings - start----------------*/
		$selectMainTabData = "SELECT DISTINCT(catid) as catid FROM tbl_movie_timings WHERE parentid= '".$this->parentid."' AND movie_date >= DATE(NOW())";
		$resMainTabData = parent::execQuery($selectMainTabData, $this->conn_iro);
		
		$mCount 		   = mysql_num_rows($resMainTabData);
	    if($this->debug_mode==1){
			echo '<br>';
			echo 'sql :: '.$selectMainTabData;
			echo '<br>';
		   echo '<br>'.$mCount;
		   echo '<br>';
		}
		if($mCount > 0)
		{
			while($rowMainTabData = mysql_fetch_assoc($resMainTabData))
			{
				$catid			  = $rowMainTabData['catid'];				
				
				if($catid!='' && $catid!=0){					
					$sel_catid_index = "SELECT MIN(movie_date) AS sel_date_min, MAX(movie_date) AS sel_date_max, category_name,CONCAT_WS(', ',GROUP_CONCAT(movie_date, ' ' , movie_timings)) AS all_timing_str FROM tbl_movie_timings WHERE parentid='".$this->parentid."' AND DATE(movie_date)>=DATE(NOW()) AND catid='".$catid."'";
					//$res_catid_index  = $this->conn_iro->query_sql($sel_catid_index);
					$res_catid_index = parent::execQuery($sel_catid_index, $this->conn_iro);
					if($this->debug_mode==1){
						echo '<br>';
						echo 'sql :: '.$sel_catid_index;
						echo '<br>';
					   echo '<br>'.mysql_num_rows($res_catid_index);
					   echo '<br>';
					}
					if($res_catid_index && mysql_num_rows($res_catid_index) > 0){
						while($row_catid_index = mysql_fetch_assoc($res_catid_index)){
							$dateMin 		= $row_catid_index['sel_date_min'];
							$dateMax 		= $row_catid_index['sel_date_max'];
							$all_timing_str = $row_catid_index['all_timing_str'];
							$category_name  = $row_catid_index['category_name']; 
							$getDay = new DateTime($dateMin);
							$day = $getDay->format('l');
							
							//getting last friday of min date
							$MinFri_date = strtotime($dateMin);
							$arrDateMinLastFri = strtotime("last friday", $MinFri_date);
							$start_date = date("Y-m-d", $arrDateMinLastFri);
							if ($day == "Friday") {
								$start_date = $dateMin;
							}
							
							$dtNextFri = new DateTime($dateMax);
							$NextFri = $dtNextFri->format('l');
							$maxDateFri = strtotime($dateMax);
							$arrDateMaxNextFri = strtotime("friday", $maxDateFri);
							$end_date = date("Y-m-d", $arrDateMaxNextFri);							
							
							$all_timing_arr = array();
							$all_timing_arr = explode(",", $all_timing_str);

							$all_timing_arr = array_filter($all_timing_arr);
							$all_timing_arr = array_merge(array_unique($all_timing_arr));
					
							if (count($all_timing_arr) > 0) {
								$only_date_arr = array();
								$only_time_arr = array();
								foreach ($all_timing_arr as $timing_val) {
									$timing_val_arr = explode(" ", $timing_val);
									$only_date_val = $timing_val_arr[0];
									$only_time_val = $timing_val_arr[1];
									$only_date_arr[] = $only_date_val;
									$only_time_arr[] = $only_time_val;
								}
				
								$start_date_new = date_create($start_date);
								$end_date_new = date_create($end_date);								
								$date_difference = date_diff($start_date_new, $end_date_new);
								
								
								$date_diff_new = $date_difference->format("%a");
								if($date_diff_new==0){
									$total_loop_count =1;
								}else{
									$total_loop_count = $date_diff_new / 7;
								}
								$today = date('Y-m-d');
								for ($index = 1; $index <= $total_loop_count; $index++) {
									$index_new = $index - 1;
									$index_counter = $index_new * 7;
									$current_week_date = date('Y-m-d', strtotime($start_date . "+" . $index_counter . " days"));
									
									for ($day = 0; $day <= 6; $day++) {
										$final_date = date('Y-m-d', strtotime($current_week_date . "+" . $day . " days"));
										$index;
										$all_matched_keys = array_keys($only_date_arr, $final_date);
										if (count($all_matched_keys) > 0) {
											foreach ($all_matched_keys as $key_val) {
												$date_val = $only_date_arr[$key_val];
												$time_val = $only_time_arr[$key_val];
																									
												if ($date_val != '' && $time_val != '') {
													
													//delete old entry of data from shadow. from shadow main table wil be populated properly
													$del_sql_qry = "DELETE FROM tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' AND catid='".$catid."' AND movie_date < DATE(NOW()) ";
													//$res_sql_qry = $this->conn_iro->query_sql($del_sql_qry);
													
													$res_sql_qry = parent::execQuery($del_sql_qry, $this->conn_iro);
													
													
												   $queryMovieTime = "INSERT INTO tbl_movie_timings_shadow
																			SET
																			parentid  = '" . $this->parentid . "',
																			catid     = '" . $catid . "',
																			category_name = '" . addslashes(stripslashes($category_name)) . "' ,
																			movie_timings = '" . $time_val . "',
																			movie_date    = '" . $date_val . "',
																			index_mv      = '" . $index . "'
																			ON DUPLICATE KEY UPDATE
																			category_name = '" . addslashes(stripslashes($category_name)) . "',
																			movie_timings = '" . $time_val . "',
																			movie_date    = '" . $date_val . "',
																			index_mv      = '" . $index . "'
																			";

													//$respqueryMovieTime = $this->conn_iro->query_sql($queryMovieTime);
													
													$respqueryMovieTime = parent::execQuery($queryMovieTime, $this->conn_iro);
													
												
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
		}
		
		/*---------------populating shadow tables for movie timings - end----------------*/
		
		
			/* from tbl_geocodes to tbl_compgeocodes_shadow - starts */
				$select_geocodes = "SELECT parentid,
									latitude_area,
									longitude_area,
									latitude_pincode,
									longitude_pincode,
									latitude_street,
									longitude_street,
									latitude_bldg,
									longitude_bldg,
									latitude_final,
									longitude_final,
									logdatetime,
									mappedby,
									latitude_landmark,
									longitude_landmark
									FROM tbl_compgeocodes
									where parentid = '".$this->parentid."' ";

				//$result_geocodes = $this->conn_local->query_sql($select_geocodes);
				
				$result_geocodes 	= parent::execQuery($select_geocodes, $this->conn_local);
				
				if($result_geocodes && mysql_num_rows($result_geocodes))
					{
						$row_geocodes	 =  mysql_fetch_assoc($result_geocodes);
							$insert_geocode_shadow = "INSERT INTO
											tbl_compgeocodes_shadow
									SET
									parentid				= '".$this->parentid."',
									latitude_area			= '".$row_geocodes[latitude_area]."',
									longitude_area			= '".$row_geocodes[longitude_area]."',
									latitude_pincode		= '".$row_geocodes[latitude_pincode]."',
									longitude_pincode		= '".$row_geocodes[longitude_pincode]."',
									latitude_street			= '".$row_geocodes[latitude_street]."',
									longitude_street		= '".$row_geocodes[longitude_street]."',
									latitude_bldg			= '".$row_geocodes[latitude_bldg]."',
									longitude_bldg			= '".$row_geocodes[longitude_bldg]."',
									latitude_final			= '".$row_geocodes[latitude_final]."',
									longitude_final			= '".$row_geocodes[longitude_final]."',
									logdatetime				= '".$row_geocodes[logdatetime]."',
									mappedby				= '".$row_geocodes[mappedby]."',
									latitude_landmark		= '".$row_geocodes[latitude_landmark]."',
									longitude_landmark		= '".$row_geocodes[longitude_landmark]."'

									ON DUPLICATE KEY UPDATE

									latitude_area			= '".$row_geocodes[latitude_area]."',
									longitude_area			= '".$row_geocodes[longitude_area]."',
									latitude_pincode		= '".$row_geocodes[latitude_pincode]."',
									longitude_pincode		= '".$row_geocodes[longitude_pincode]."',
									latitude_street			= '".$row_geocodes[latitude_street]."',
									longitude_street		= '".$row_geocodes[longitude_street]."',
									latitude_bldg			= '".$row_geocodes[latitude_bldg]."',
									longitude_bldg			= '".$row_geocodes[longitude_bldg]."',
									latitude_final			= '".$row_geocodes[latitude_final]."',
									longitude_final			= '".$row_geocodes[longitude_final]."',
									logdatetime				= '".$row_geocodes[logdatetime]."',
									mappedby				= '".$row_geocodes[mappedby]."',
									latitude_landmark		= '".$row_geocodes[latitude_landmark]."',
									longitude_landmark		= '".$row_geocodes[longitude_landmark]."' ";
									
							//$insert_geocode_shadow_res = $this->conn_local->query_sql($insert_geocode_shadow);
							
							$insert_geocode_shadow_res 	= parent::execQuery($insert_geocode_shadow, $this->conn_local);
							
							
							
					}
					else //  there is no entry in tbl_compgeocodes so removing from temp table
					{
							$del_query = "DELETE FROM tbl_compgeocodes_shadow WHERE parentid = '".$this->parentid."'";
							//$this->conn_local->query_sql($del_query);
							$res_del_query 	= parent::execQuery($del_query, $this->conn_local);
							
					}
					
						$bldg_query = "SELECT * FROM unapproved_building_geocodes_main WHERE parentid = '".$this->parentid."' AND approval_flag=0 ORDER BY date DESC LIMIT 1";
						//$bldg_result = $this ->conn_local->query_sql($bldg_query);
						$bldg_result= parent::execQuery($bldg_query, $this->conn_local);
						
						if($bldg_result && mysql_num_rows($bldg_result))
						{
							$bldg_row = mysql_fetch_assoc($bldg_result);
							$bldg_row = $this->addslashesArray($bldg_row); /*--stripping and adding slashes in the array----*/
							$qry_main = "INSERT INTO unapproved_building_geocodes
									SET 
									parentid			=	'".$bldg_row[parentid]."',
									username			=	'".$bldg_row[username]."',
									userid				=	'".$bldg_row[userid]."',
									temp_latitude		=	'".$bldg_row[temp_latitude]."',
									temp_longitude		=	'".$bldg_row[temp_longitude]."',
									approved_latitude	=	'".$bldg_row[approved_latitude]."',
									approved_longitude	=	'".$bldg_row[approved_longitude]."',
									temp_tagging		=	'".$bldg_row[temp_tagging]."',
									original_tagging	=	'".$bldg_row[original_tagging]."',
									approval_flag		=	'".$bldg_row[approval_flag]."',
									date				=	'".$bldg_row[date]."',
									approve_reject_date =	'".$bldg_row[approve_reject_date]."',
									approve_reject_by	=	'".$bldg_row[approve_reject_by]."',
									old_address			=	'".$bldg_row[old_address]."',
									new_address			=	'".$bldg_row[new_address]."'
									
									ON DUPLICATE KEY UPDATE
									
									username			=	'".$bldg_row[username]."',
									userid				=	'".$bldg_row[userid]."',
									temp_latitude		=	'".$bldg_row[temp_latitude]."',
									temp_longitude		=	'".$bldg_row[temp_longitude]."',
									approved_latitude	=	'".$bldg_row[approved_latitude]."',
									approved_longitude	=	'".$bldg_row[approved_longitude]."',
									temp_tagging		=	'".$bldg_row[temp_tagging]."',
									original_tagging	=	'".$bldg_row[original_tagging]."',
									approval_flag		=	'".$bldg_row[approval_flag]."',
									date				=	'".$bldg_row[date]."',
									approve_reject_date =	'".$row[approve_reject_date]."',
									approve_reject_by	=	'".$row[approve_reject_by]."',
									old_address			=	'".$bldg_row[old_address]."',
									new_address			=	'".$bldg_row[new_address]."'	";
									
									//$res = $this->conn_local->query_sql($qry_main);
									
									$res= parent::execQuery($qry_main, $this->conn_local);
						}
						else //  there is no entry in unapproved_building_geocodes_main so removing from temp table
						{
								$del_query = "DELETE FROM unapproved_building_geocodes WHERE parentid = '".$this->parentid."'";
								//$this->conn_local->query_sql($del_query);
								$res_del_query= parent::execQuery($del_query, $this->conn_local);
						}

		/* from tbl_geocodes to tbl_compgeocodes_shadow - ends   4a */
		
		
		
	 }
		
		if($this->debug_mode==1){
			$taken_time_1 =  strtotime(date('H:i:s')) - strtotime($start_time_1);
			$taken_time_1 =  gmdate("H:i:s", $taken_time_1);
			$debug_resp['1']['action'] 		= "Populating Temp Tables on 17.233";
			$debug_resp['1']['takentime'] 	= $taken_time_1;
		}
		#Step 2 : Populating finance (xx.161) Temp Tables + national_listing temp table
		
		if($this->debug_mode==1){
			$start_time_2 = date('H:i:s');
		}
		
	if(strtoupper($this->module) != 'DE')
	{
		
		$curl_url	=	$this->jdbox_url.'finance/contract_data_api.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['data_city'] 	= 	$this->data_city; #For remote_city passing data_city as remote
		$data['module']		=	strtolower(trim($this->module));
		$data['ucode']		=	$this->ucode;
		$data['uname']		=	$this->uname;
		$fin_temp_resp 		= 	json_decode($this->curlCall($curl_url,$data),true);
		if($fin_temp_resp['ERRCODE'] !=1){
			$error_found = 1;
		}
		if($this->debug_mode==1){
			$taken_time_2 =  strtotime(date('H:i:s')) - strtotime($start_time_2);
			$taken_time_2 =  gmdate("H:i:s", $taken_time_2);
			$debug_resp['2']['action'] 		= "Populating Finance Temp Tables on xx.161";
			$debug_resp['2']['API'] 			= $curl_url;
			$debug_resp['2']['params'] 		= json_encode($data);
			$debug_resp['2']['response'] 	= json_encode($fin_temp_resp);
			$debug_resp['2']['takentime'] 	= $taken_time_2;
		}
		#Step 3 : Populating tbl_omni_ecs_details (17.233)
		
		if($this->debug_mode==1){
			$start_time_3 = date('H:i:s');
		}
		
		$curl_url	=	$this->jdbox_url.'services/ecs_mandate_form.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['data_city'] 	= 	$this->data_city;
		$data['action'] 	= 	5;
		$data['module']  	= 	strtolower(trim($this->module));
		if($this->conn_city == 'remote'){
		 	$data['remote']  	= 	1;
		}
		$omni_temp_resp 		= 	$this->curlCall($curl_url,$data);
		if($this->debug_mode==1){
			$taken_time_3 =  strtotime(date('H:i:s')) - strtotime($start_time_3);
			$taken_time_3 =  gmdate("H:i:s", $taken_time_3);
			$debug_resp['3']['action'] 		= "Populating OMNI Temp Tables";
			$debug_resp['3']['API'] 		= $curl_url;
			$debug_resp['3']['params'] 		= json_encode($data);
			$debug_resp['3']['response'] 	= $omni_temp_resp;
			$debug_resp['3']['takentime'] 	= $taken_time_3;
		}
		#Step 4 : Populating category sponsorship temp tables
		
		if($this->debug_mode==1){
			$start_time_4 = date('H:i:s');
		}
		
		$curl_url	=	$this->cs_url.'business/textbannersponservice.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['s_deptCity'] = 	$this->data_city;
		$data['module']  	=	strtolower(trim($this->module));
		$data['action']  	= 	'getContractData';
		$catspon_temp_resp 	= 	$this->curlCall($curl_url,$data);
		if($this->debug_mode==1){
			$taken_time_4 =  strtotime(date('H:i:s')) - strtotime($start_time_4);
			$taken_time_4 =  gmdate("H:i:s", $taken_time_4);
			$debug_resp['4']['action'] 		= "Populating Category Sponsorship Temp Tables";
			$debug_resp['4']['API'] 		= $curl_url;
			$debug_resp['4']['params'] 		= json_encode($data);
			$debug_resp['4']['response'] 	= $catspon_temp_resp;
			$debug_resp['4']['takentime'] 	= $taken_time_4;
		}
		#Step 5 : Populating Banner temp tables
		
		if($this->debug_mode==1){
			$start_time_5 = date('H:i:s');
		}
		
		$curl_url	=	$this->cs_url.'business/bannerservice.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['s_deptCity'] = 	$this->data_city;
		$data['module']  	=	strtolower(trim($this->module));
		$data['type']  		=	'5';
		$data['state']  	=	1;
		$data['action']  	= 	'mainToTemp';
		$banner_temp_resp 	= 	$this->curlCall($curl_url,$data);
		if($this->debug_mode==1){
			$taken_time_5 =  strtotime(date('H:i:s')) - strtotime($start_time_5);
			$taken_time_5 =  gmdate("H:i:s", $taken_time_5);
			$debug_resp['5']['action'] 		= "Populating Banner Temp Tables";
			$debug_resp['5']['API'] 			= $curl_url;
			$debug_resp['5']['params'] 		= json_encode($data);
			$debug_resp['5']['response'] 	= $banner_temp_resp;
			$debug_resp['5']['takentime'] 	= $taken_time_5;
		}
		#Step 6 : Populating Bidding Details Table (xx.124)
		
		if($this->debug_mode==1){
			$start_time_6 = date('H:i:s');
		}
		
		$curl_url	=	$this->jdbox_url.'services/getcontractapi.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['data_city'] 	= 	$this->data_city;
		$data['action'] 	= 	'updatetemptable';
		$data['module']		=	strtolower(trim($this->module));
		$data['usercode']	=	$this->ucode;
		$data['username']	=	$this->uname;
		$bidding_temp_res 	= 	$this->curlCall($curl_url,$data);		
		
		if(json_decode($bidding_temp_res,true))
		{
			$bidding_temp_resp 	= 	json_decode($bidding_temp_res,true);
		}else {
			$bidding_temp_resp['error']['code'] = 1;
			$bidding_temp_resp['error']['msg']  = $bidding_temp_res;
		}
		
		if($bidding_temp_resp['error']['code'] !=0){
			$error_found = 1;
		}
		
		if($bidding_temp_resp['error']['code'] !=0){
			$error_found = 1;
		}
		if($this->debug_mode==1){
			$taken_time_6 =  strtotime(date('H:i:s')) - strtotime($start_time_6);
			$taken_time_6 =  gmdate("H:i:s", $taken_time_6);
			$debug_resp['6']['action'] 		= "Populating Bidding Temp Tables";
			$debug_resp['6']['API'] 		= $curl_url;
			$debug_resp['6']['params'] 		= json_encode($data);
			$debug_resp['6']['response'] 	= json_encode($bidding_temp_resp);
			$debug_resp['6']['takentime'] 	= $taken_time_6;
		}
	
	
		#Step 7 : Populating sms bid temp
		
		if($this->debug_mode==1){
			$start_time_7 = date('H:i:s');
		}
		
		$curl_url	=	$this->jdbox_url.'services/fetch_update_sms_promo.php';
		//$curl_url	=	'http://172.29.0.217:1010/services/fetch_update_sms_promo.php';
		$data		=	array();
		$data['parentid'] 	= 	$this->parentid;
		$data['data_city'] 	= 	$this->data_city;
		$data['action'] 	= 	'updatetemptable';
		$data['module']		=	strtolower(trim($this->module));
		$data['usercode']	=	$this->ucode;
		$data['username']	=	$this->uname;
		$sms_bid_res 		= 	json_decode($this->curlCall($curl_url,$data), true);	
		//echo "<br>".$curl_url."?".http_build_query($data);
		//echo "<hr>";
		//print_r($bidding_temp_res);exit;
		
		if(count($sms_bid_res) <= 0)
		{
			$sms_bid_res['error']['code'] = 1;
			$sms_bid_res['error']['msg']  = 'failed';
		}
		
		
		if($sms_bid_res['error']['code'] !=0){
			$error_found = 1;
		}
		
		if($this->debug_mode==1){
			$taken_time_7 =  strtotime(date('H:i:s')) - strtotime($start_time_7);
			$taken_time_7 =  gmdate("H:i:s", $taken_time_7);
			$debug_resp['7']['action'] 		= "Populating sms bid Temp Tables";
			$debug_resp['7']['API'] 		= $curl_url;
			$debug_resp['7']['params'] 		= json_encode($data);
			$debug_resp['7']['response'] 	= json_encode($sms_bid_res);
			$debug_resp['7']['takentime'] 	= $taken_time_7;
		}
	}
		#Step 8 : Changing updateFlag in tbl_lock_company
		if($this->module == 'TME' || $this->module == 'ME'){
			if($this->module == 'TME'){
				$extra_where = " tme_updateflag = 0,updateflag	= 0";
			}
			else{
				$extra_where = " me_updateflag  = 0,updateflag  = 0	";
			}
			$sqlUpdtLockCompanyFlag = "UPDATE tbl_lock_company SET ".$extra_where.",updatedDate = '".date('Y-m-d H:i:s')."' WHERE parentid ='".$this->parentid."'";
			$resUpdtLockCompanyFlag   = parent::execQuery($sqlUpdtLockCompanyFlag, $this->conn_local);
		}
		/* All steps completed retuning response */
		if($error_found == 1){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Fail";
		}else{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
		}
		if($this->debug_mode==1){
			$total_time =  strtotime(date('H:i:s')) - strtotime($start_time_1);
			$total_time =  gmdate("H:i:s", $total_time);
			$debug_resp['Process End Time'] 	= date('H:i:s');
			$debug_resp['Total Time Taken'] 	= $total_time;
			$result_msg_arr['debug'] 			= $debug_resp;
		}
		return $result_msg_arr;
	}
	function getCategoryDetails($catids_arr)
	{
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'live_data_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,national_catid,auth_gen_ncatid';

		$where_arr  	=	array();
		if(count($catids_arr)>0){			
			$where_arr['catid']			= implode(",",$catids_arr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key => $row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$national_catid	= trim($row_catdetails['national_catid']);
				
				$CatinfoArr[$catid]['catname'] = $category_name;
				$CatinfoArr[$catid]['national_catid'] = $national_catid;
			}
		}
		return $CatinfoArr;
	}
	function reasonInfo()
	{
		$reasonArr = array();
		$sqlSelReason = "SELECT reason_id,reasons,reason_text FROM tbl_contract_reasons WHERE contractid = '".$this->parentid."'";
		$resSelReason = parent::execQuery($sqlSelReason, $this->conn_local);
		if($resSelReason && parent::numRows($resSelReason)){
			$row_reason	= parent::fetchData($resSelReason);
			$reason_id 	= $row_reason['reason_id'];
			$reason_text= $row_reason['reason_text'];
			$reasonArr['reason_id']  	= intval($reason_id);
			$reasonArr['reason_text'] 	= trim($reason_text);
		}
		return $reasonArr;
	}
	function addInfoTxt(){
		$addInfoArr = array();
		$sqlAddInfoTxt = "SELECT add_infotxt FROM tbl_comp_addInfo where contractId ='".$this->parentid."' ORDER BY lockdateTime DESC LIMIT 1";
		$resAddInfoTxt = parent::execQuery($sqlAddInfoTxt, $this->conn_local);
		if($resAddInfoTxt && parent::numRows($resAddInfoTxt)){
			$row_addinfo = parent::fetchData($resAddInfoTxt);
			$add_infotxt = trim($row_addinfo['add_infotxt']);
			$addInfoArr['add_infotxt'] = $add_infotxt;
		}
		return $addInfoArr;
	}
	function sourceInfo(){
		$sourceInfoArr = array();
		$sqlSourceInfo    = "SELECT mainsource,subsource,datesource FROM tbl_company_source WHERE parentid='".$this->parentid."' ORDER BY datesource DESC LIMIT 1";
		$resSourceInfo = parent::execQuery($sqlSourceInfo, $this->conn_local);
		if($resSourceInfo && parent::numRows($resSourceInfo)){
			$row_source = parent::fetchData($resSourceInfo);
			$mainsource   = trim($row_source['mainsource']);
			$subsource    = trim($row_source['subsource']);
			$datesource   = trim($row_source['datesource']);
			$sourceInfoArr['mainsource'] 	= $mainsource;
			$sourceInfoArr['subsource'] 	= $subsource;
			$sourceInfoArr['datesource'] 	= $datesource;
		}
		return $sourceInfoArr;
	}
	function tmeInfo(){
		$tmeInfoArr = array();
		$sqlTMEInfo = "SELECT employeeCode,iroCode,meCode,mCode,tmeCode,tmeName FROM tbl_contract_tmeDetails WHERE contractid='".$this->parentid."'";
		$resTMEInfo = parent::execQuery($sqlTMEInfo, $this->conn_local);
		if($resTMEInfo && parent::numRows($resTMEInfo)){
			$row_tme = parent::fetchData($resTMEInfo);
			$employeeCode 	= trim($row_tme['employeeCode']);
			$iroCode 		= trim($row_tme['iroCode']);
			$meCode 		= trim($row_tme['meCode']);
			$mCode 			= trim($row_tme['mCode']);
			$tmeCode 		= trim($row_tme['tmeCode']);
			$tmeInfoArr['employeeCode'] = $employeeCode;
			$tmeInfoArr['iroCode'] 		= $iroCode;
			$tmeInfoArr['meCode'] 		= $meCode;
			$tmeInfoArr['mCode'] 		= $mCode;
			$tmeInfoArr['tmeCode'] 		= $tmeCode;
			$tmeInfoArr['tmeName'] 		= $tmeName;
		}
		return $tmeInfoArr;
	}
	function updateVideoLogo($serviceName)
	{
		if(stristr($serviceName,"video_shooting")) {  
			$video_up = 2; 
		}elseif(stristr($serviceName,"video")){
			$video_up = 1;
		}else { 
			$video_up=0;
		}
		if(stristr($serviceName,"logo")){  
			$logo_up = 1; 
		}else{ 
			$logo_up=0;
		}
		if(stristr($serviceName,"catalog")){
			$catalog_up = 1;
		}else{
			$catalog_up = 0;
		}
		$sqlVideoLogo = "INSERT INTO tbl_business_temp_enhancements SET
						video_facility 		= '".$video_up."',
						logo_facility 		= '".$logo_up."',
						catalog_facility 	= '".$catalog_up."',
						contractid 			= '".$this->parentid."'
						ON DUPLICATE KEY UPDATE
						video_facility 		= '".$video_up."',
						logo_facility 		= '".$logo_up."',
						catalog_facility 	= '".$catalog_up."'";
		$resVideoLogo 	= parent::execQuery($sqlVideoLogo, $this->conn_idc);
	}
	function csFetchInfo($edit_flag)
	{
		$sqlInsertCSFetch =  "INSERT INTO tbl_cs_fetch_info 
								SET 
								parentid 	= '".$this->parentid."',
								edit_flag 	= '".$edit_flag."',
								updatedate 	= '".date('Y-m-d H:i:s')."'
								ON DUPLICATE KEY UPDATE
								edit_flag 	= '".$edit_flag."',
								updatedate 	= '".date('Y-m-d H:i:s')."'";
		$resInsertCSFetch 	= parent::execQuery($sqlInsertCSFetch, $this->conn_idc);
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
	function stringProcess($string){
		$string = trim($string);
		$string = addslashes(stripslashes($string));
		return $string;
	}
	private function insertTextLog($data_arr)
	{	//need to change 
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $this->parentid;
		$post_data['PUBLISH']           = $this->module;
		$post_data['ROUTE']             = 'LIVE_DATA_CLASS';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= 'update data from live data class';		
		$post_data['DATA_JSON']['paramssubmited'] = $data_arr;
		$post_data = http_build_query($post_data);
		/*echo "<pre>";print_r($data_arr);
		echo "<pre>";print_r($post_data);*/
		$log_res = $this->curlCall($log_url,$post_data);
	}
	function curlCall($curl_url,$data)
	{	
		#echo $curlurl.'?'.$data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($curl_url));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	private function sendDownDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 2;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
