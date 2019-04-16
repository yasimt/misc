<?php
class cs_edit_check_class extends DB
{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $ucode		= null;
	
	
	function __construct($params)
	{
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$userid 		= trim($params['userid']);
		$ucode 			= trim($params['ucode']);
		$downselFlag 			= trim($params['downselFlag']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->userid		= $userid;
		$this->ucode		= $ucode;
		$this->downselFlag		= $downselFlag;
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();
		$this->companyname  = $this->getCompanyName();
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
		//downsell status check end
	}
	
		//get version
	function getversion()
	{
		$db	=	'';
		if(strtoupper($this->module) == 'TME'){
			$db	.=	'tme_jds.';
		}
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "version";
			$summary_version_arr = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$summary_version_sql 	="select version from ".$db."tbl_temp_intermediate where parentid='".$this->parentid."'";
			if(strtoupper($this->module) == 'TME'){
				$summary_version_rs = parent::execQuery($summary_version_sql, $this->conn_local);			
			}else{
				$summary_version_rs = parent::execQuery($summary_version_sql, $this->conn_idc);			
			}
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
			$sqlDownsel 	=  "SELECT status,delete_flag,request_type,dealclose_flag,module FROM online_regis.downsell_trn WHERE parentid='$this->parentid' AND version='$this->version' AND delete_flag!=1 ORDER BY updated_at DESC LIMIT 1"; 
			$resDownsel		=	parent::execQuery($sqlDownsel, $this->conn_idc);
			$num			=	parent::numRows($resDownsel);
			if($num > 0){
				while($row_down	  =	parent::fetchData($resDownsel)){
					 $status 			= $row_down['status'];
					 $flag   			= $row_down['delete_flag'];
					 $rqsttyp			= $row_down['request_type'];
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
				}else if($dealclose_flag	!=	2 && $status==1 && strtolower($module)	!=	'geniolite'){
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
			
		$conn_city 			= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_local  	= $db[$conn_city]['d_jds']['master'];
		$this->conn_iro    	= $db[$conn_city]['iro']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if(strtoupper($this->module) == 'ME' || strtoupper($this->module) == 'JDA')
		{
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		if(strtoupper($this->module) == 'TME')
		{
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
		}
	}
	function getCSRedirectionUrl($params)
	{
		if(isset($params['downselFlag']) && ($params['downselFlag'] == 1)){
			$result_msg_arr['redirecturl'] = '';
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "CS Redirection Url Not Needed";
			return $result_msg_arr;
		}else{
		$result_found = 0;
		$cs_redirection_url = '';
		if(strtoupper($this->module) == 'TME' || strtoupper($this->module) == 'ME')
		{
			if(strtoupper($this->module) == 'TME'){
				$extra_where = " AND (tme_updateflag= 1 OR updateflag=1) ";
			}
			else{
				$extra_where = " AND (me_updateflag= 1 OR updateflag=1)";
			}
			$sqlFetchCSData = "SELECT updateby,source,updateddate,tme_updateflag,me_updateflag FROM tbl_lock_company WHERE parentid='".$this->parentid."' ".$extra_where." LIMIT 1";
			$resFetchCSData   = parent::execQuery($sqlFetchCSData, $this->conn_local);
			if($resFetchCSData && mysql_num_rows($resFetchCSData)>0)
			{
				$row_cs_data = mysql_fetch_assoc($resFetchCSData);
				$editedBy    = trim($row_cs_data['updateby']);
				$updatedBy   = trim($row_cs_data['updateby']);
				$source		 = trim($row_cs_data['source']);
				$updateddate = trim($row_cs_data['updateddate']);
				$tme_updateflag	= intval($row_cs_data['tme_updateflag']);
				$me_updateflag 	= intval($row_cs_data['me_updateflag']);
				
				$current_date = date("Y-m-d H:i:s");
				
				$sql_lock_log =	"INSERT INTO tbl_lock_company_log
								 SET 
								 	parentid 		= '".$this->parentid."',
								 	updatedBy_old 	= '".$updatedBy."',
								 	updatedBy_new 	= '".$this->ucode."',
								 	updateddate_old	= '".$updateddate."',
								 	updateddate_new = NOW(),
								 	source_old 		= '".$source."',
								 	source_new 		= '".strtoupper($this->module)."' ";
				$res_lock_log = parent::execQuery($sql_lock_log, $this->conn_local);

				if($this->module == 'TME'){					
						$sqlUpdtLockCompanyFlag = "UPDATE tbl_lock_company SET UpdateFlag = 0, tme_updateflag = 0, updatedDate = '".$current_date."' WHERE parentid ='".$this->parentid."' ";					
				}else if($this->module == 'ME'){											
						$sqlUpdtLockCompanyFlag = "UPDATE tbl_lock_company SET UpdateFlag = 0, me_updateflag = 0, updatedDate = '".$current_date."' WHERE parentid ='".$this->parentid."' ";					
				}
				
				//due to trigger on tbl_lock_table unable to set only module flag hence updating UpdateFlag to 0				
				$resUpdtLockCompanyFlag   = parent::execQuery($sqlUpdtLockCompanyFlag, $this->conn_local);
				if($this->module == 'TME')
				{
					$_SESSION['cs_fetch']	= '1'; // for verification code i.e. selecting where to get the contact numbers
				}
				$cs_redirection_url = "business/redirectPage.php?companyName=".urlencode($this->companyname)."&editBy=".urlencode($updateby)."&updateBy=".$updatedBy."&parentid=".$this->parentid."&data_city=".urlencode($this->data_city)."&userid=".$this->userid."&source=".urlencode($source);
				$result_found = 1;
			}
		}
		if($result_found == 1)
		{
			$result_msg_arr['redirecturl'] = $cs_redirection_url;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
		}
		else
		{
			$result_msg_arr['redirecturl'] = $cs_redirection_url;
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "CS Redirection Url Not Needed";
		}
		return $result_msg_arr;
		}
	}
	function getCompanyName()
	{
		$companyname = '';
		$sqlCompanyName = "SELECT companyname FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
		$resCompanyName = parent::execQuery($sqlCompanyName, $this->conn_iro);
		if($resCompanyName && mysql_num_rows($resCompanyName)>0)
		{
			$row_company = mysql_fetch_assoc($resCompanyName);
			$companyname = $row_company['companyname'];
		}
		return $companyname;
	}
	private function send_die_message($msg)
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
