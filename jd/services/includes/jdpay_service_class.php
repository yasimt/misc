<?php
require_once 'class_send_sms_email.php';
require_once('ecsMandateClass.php');
class jdpay_service_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $ucode		= null;
	
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message,1));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($module)==''){
			$message = "Module is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		
		$this->parentid  	= trim($parentid);
		$this->data_city 	= trim($data_city);
		$this->module  	  	= trim($module);
		
			//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->companyClass_obj = new companyClass();
		
		$this->setServers();
		$this->setDocid();
		$urls = $this->get_curl_url($this->data_city);
		$this->jdbox_url		  = $urls['jdbox_url'];	
	}
	
	function setServers()
	{
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->is_remote = 'REMOTE';
		}
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif(strtoupper($this->module) =='ME' || strtoupper($this->module) =='JDA') {
			$this->conn_temp		= $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		
	}
	function get_curl_url(){
		$urlArr = array();
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			$jdbox_url 				= "http://saritapc.jdsoftware.com/jdbox/";
		}
		else
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI' :
					$jdbox_url 				= "http://".MUMBAI_JDBOX_API."/";
					break;

				case 'AHMEDABAD' :
					$jdbox_url 				= "http://".AHMEDABAD_JDBOX_API."/";
					break;

				case 'BANGALORE' :
					$jdbox_url 				= "http://".BANGALORE_JDBOX_API."/";
					break;

				case 'CHENNAI' :
					$jdbox_url 				= "http://".CHENNAI_JDBOX_API."/";
					break;

				case 'DELHI' :
					$jdbox_url 				= "http://".DELHI_JDBOX_API."/";
					break;

				case 'HYDERABAD' :
					$jdbox_url 				= "http://".HYDERABAD_JDBOX_API."/";
					break;

				case 'KOLKATA' :
					$jdbox_url 				= "http://".KOLKATA_JDBOX_API."/";
					break;

				case 'PUNE' :
					$jdbox_url 				= "http://".PUNE_JDBOX_API."/";
					break;

				default:
					$jdbox_url 				= "http://".REMOTE_CITIES_JDBOX_API."/";
					break;
			}	
			
		}
		$urlArr['jdbox_url'] 			= $jdbox_url;
		return $urlArr;
	}
	
	function setDocid(){
		$sql    = "select sphinx_id,docid,concat(url_cityid,shorturl) as shorturl from db_iro.tbl_id_generator where parentid='".trim($this->parentid)."'";
		$res    = parent::execQuery($sql, $this->conn_iro);
		if($res && mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$this->sphinx_id = $row['sphinx_id'];
			$this->docid     = $row['docid'];
			$this->shortUrl  = $row['shorturl'];
			
		}
	}
	/* Function Added as the requiremnt got changed by Rohit Sir
	 * Function Created By Apoorv Agrawal 
	*/
	function accountDetailsToLive($data_city){
		GLOBAL $db;
		$query  = "select parentid, email, mobile from jdpay_message_request where parentid='".$this->parentid."'";
		$result = parent::execQuery($query, $this->conn_temp);
		if($result && mysql_num_rows($result) > 0){
			$row        = mysql_fetch_assoc($result);
			$mobilenum  = $row['mobile'];
			$emailids   = $row['email'];
		}
	
		if($mobilenum!=''){
			$mobilenums = explode(',',$mobilenum);
		}
		if($emailids!=''){
			$emailid = explode(',',$emailids);
		}
		$smsflg = '';
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "companyname,area,city,parentid";
			$rowDetails1 = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$selDetails  = "select companyname, area, city, parentid from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$resDetails  = parent::execQuery($selDetails, $this->conn_temp);
			if($resDetails && mysql_num_rows($resDetails) > 0){
				$rowDetails1 = mysql_fetch_assoc($resDetails);	
			}
			$compname   = trim($rowDetails1['companyname']);
			$area  		= $rowDetails1['area'];
			$city       = $rowDetails1['city'];
		}
		$emailflg	=	2;
		$smsflg		=	2;
			$query  = "update jdpay_message_request set send_flag=2, email_send_flag='".$emailflg."', sms_send_flag='".$smsflg."', actual_sent_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' ";
			$resqry = parent::execQuery($query, $this->conn_temp);
			
			$selDetails = "select * from jdpay_message_request where parentid='".$this->parentid."'";
			$resDetails = parent::execQuery($selDetails, $this->conn_temp);
			if($resDetails && mysql_num_rows($resDetails) > 0){
				
				$rowDetails = mysql_fetch_assoc($resDetails);
			}
			$params_arr = array();
			$params_arr['parentid']  = $this->parentid;
			$params_arr['action']    = '2';
			$params_arr['data_city'] = $this->data_city;
			if(strtolower($this->module)=='jda' || strtolower($this->module)=='me'){
				$params_arr['module']    = 'me';
			}else{
				$params_arr['module']    = $this->module;
			}
			
			$params_arr['is_remote'] = $this->is_remote;
			 	
			$ecsMandateClassobj = new ecsMandateClass($params_arr);
			$resEcs             = $ecsMandateClassobj->getMandateDetails();
			$ecs_result         = array();
			if($resEcs['error']['code']==0 && $resEcs['error']['result']){
				if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
					
					$ecs_url = "http://karnishmaster.jdsoftware.com/web_services/biopay_api/biopay/bankdetails?identifier=1&docid=".$this->docid."&compname=".urlencode($compname)."&area=".urlencode($area)."&city=".urlencode($city)."&datacity=".urlencode($this->data_city)."&beneficiaryname=".urlencode($resEcs['error']['result']['account_name'])."&accno=".$resEcs['error']['result']['account_number']."&acctype=".urlencode(addslashes(stripslashes($resEcs['error']['result']['account_type'])))."&ifsc=".addslashes(stripslashes($resEcs['error']['result']['ifsc_code']))."&bankname=".urlencode(addslashes(stripslashes($resEcs['error']['result']['bank_name'])))."&bstate=&bcity=".addslashes(stripslashes($resEcs['error']['result']['branch_location']))."&bbranch=".urlencode(addslashes(stripslashes($resEcs['error']['result']['bank_branch'])))."&source=genio&case=prod";					
				}else{
					$ecs_url = "https://transactions.justdial.com/biopay_api/biopay/bankdetails?identifier=1&docid=".$this->docid."&compname=".urlencode($compname)."&area=".urlencode($area)."&city=".urlencode($city)."&datacity=".urlencode($this->data_city)."&beneficiaryname=".urlencode($resEcs['error']['result']['account_name'])."&accno=".$resEcs['error']['result']['account_number']."&acctype=".urlencode($resEcs['error']['result']['account_type'])."&ifsc=".$resEcs['error']['result']['ifsc_code']."&bankname=".urlencode($resEcs['error']['result']['bank_name'])."&bstate=&bcity=".$resEcs['error']['result']['branch_location']."&bbranch=".urlencode($resEcs['error']['result']['bank_branch'])."&source=genio&case=prod";
				}
				$res_ecs_api   = array();
				$res_ecs_api   = $this->curl_result($ecs_url);
				$ecs_result    = json_decode($res_ecs_api,true);
				$qry_log   = "insert into jdpay_message_request_log (parentid, email, mobile, request_time, usercode,username,send_flag,email_send_flag,sms_send_flag,actual_sent_time, api_url,api_result, source) values ('".$this->parentid."','".$rowDetails['email']."','".$rowDetails['mobile']."','".date('Y-m-d H:i:s')."','".$rowDetails['usercode']."','".$rowDetails['username']."', '2','".$emailflg."', '".$smsflg."', '".date('Y-m-d H:i:s')."', '".addslashes(stripslashes($ecs_url))."', '".trim(stripslashes($res_ecs_api))."', '".$this->module."') ";
				$res_log = parent::execQuery($qry_log, $this->conn_temp);
				
			}
			echo json_encode($this->send_die_message($message,0));
			die();
	}
	function sendsmsandemail(){
		GLOBAL $db;
		$emailsms_obj = new email_sms_send($db,$this->data_city);
		
		$query  = "select parentid, email, mobile from jdpay_message_request where parentid='".$this->parentid."'";
		$result = parent::execQuery($query, $this->conn_temp);
		if($result && mysql_num_rows($result) > 0){
			$row        = mysql_fetch_assoc($result);
			$mobilenum  = $row['mobile'];
			$emailids   = $row['email'];
		}
	
		if($mobilenum!=''){
			$mobilenums = explode(',',$mobilenum);
		}
		if($emailids!=''){
			$emailid = explode(',',$emailids);
		}
		$smsflg = '';
		if($mobilenum!=''){  
			
			//~ $sms_cont = "You can now ask your customers to Pay ONLINE from the below link & the amount will be deposited to your account almost instantly.\nClick here the link below :\n http://jsdl.in/JP-".$this->shortUrl."";
			$sms_cont = "Receive Online Payments from your customers using Jd Pay. Amount will be credited instantly.\nClick here : http://jsdl.in/JP-".$this->shortUrl."\nTeam Justdial";
			 foreach($mobilenums as $key => $val){
				$emailsms_obj->sendSMS($val,$sms_cont,'JD_PAY_'.strtoupper($this->module).'');
			 } 
			 if($emailsms_obj){
				$smsflg =1;
			 }
			 
		}
		
		$rowDetails1 = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'companyname,area,city,parentid';
		$cat_params['page']			= 'jdpay_service_class';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){
			$rowDetails1 = $resTempCategory['results']['data'][$this->parentid];
			$compname   = trim($rowDetails1['companyname']);
			$area  		= $rowDetails1['area'];
			$city       = $rowDetails1['city'];
		}

		/*$selDetails  = "select companyname, area, city, parentid from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		$resDetails  = parent::execQuery($selDetails, $this->conn_iro);
		if($resDetails && mysql_num_rows($resDetails) > 0){
			$rowDetails1 = mysql_fetch_assoc($resDetails);
			$compname   = trim($rowDetails1['companyname']);
			$area  		= $rowDetails1['area'];
			$city       = $rowDetails1['city'];
		}*/
		
		$emailflg = '';$email_cont='';$headers='';
		if($emailids!=''){
			 $url	=	"http://jsdl.in/JP-".$this->shortUrl;
			
			 $email_cont .= file_get_contents(trim($this->jdbox_url).'/services/jd_pay2.php?url='.urlencode($url).'&companyname='.urlencode($compname));
			
			 $headers  = "From: noreply@justdial.com" . "\r\n" .
			 $headers .= "MIME-Version: 1.0\r\n";
			 $headers .= "Content-Type: text/html; charset=UTF-8\r\n";			 
			 
			 $subject	=	"Just Dial Pay Link";
			 $from  	= "noreply@justdial.com";
			foreach($emailid as $key => $val)
			{
				$email_cont	=	trim($email_cont);
				//if(mail($val, $subject, $email_cont, $headers)){
				if($emailsms_obj->sendEmail($val,$from,$subject, $email_cont, 'JD_PAY_'.strtoupper($this->module).'',$this->parentid)){
					$emailflg = 1;					
				}else{
					$emailflg = 0;
				}
				
			}
		}
		
		if($smsflg==1 || $emailflg==1){
			
			$query  = "update jdpay_message_request set send_flag=1, email_send_flag='".$emailflg."', sms_send_flag='".$smsflg."', actual_sent_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' ";
			$resqry = parent::execQuery($query, $this->conn_temp);
			
			$selDetails = "select * from jdpay_message_request where parentid='".$this->parentid."'";
			$resDetails = parent::execQuery($selDetails, $this->conn_temp);
			if($resDetails && mysql_num_rows($resDetails) > 0){
				
				$rowDetails = mysql_fetch_assoc($resDetails);
			}
			
			if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
				$url ="http://karnishmaster.jdsoftware.com/web_services/biopay_api/biopay/insertdata?docid=".$this->docid."&mobileno=".$rowDetails['mobile']."&case=mobile&source=genio&email=".$rowDetails['email']."";
			}else{
				$url = "https://transactions.justdial.com/biopay_api/biopay/insertdata?docid=".$this->docid."&mobileno=".$rowDetails['mobile']."&case=mobile&source=genio&email=".$rowDetails['email']."";
			}
			$resApi   = array();
			$resApi   = $this->curl_result($url);
			$resulArr = json_decode($resApi,true);
			if($rowDetails){
				
				$qry_log   = "insert into jdpay_message_request_log (parentid, email, mobile, request_time, usercode,username,send_flag,email_send_flag,sms_send_flag,actual_sent_time, api_url,api_result, source) values ('".$this->parentid."','".$rowDetails['email']."','".$rowDetails['mobile']."','".date('Y-m-d H:i:s')."','".$rowDetails['usercode']."','".$rowDetails['username']."', '1','".$emailflg."', '".$smsflg."', '".date('Y-m-d H:i:s')."', '".$url."', '".trim(stripslashes($resApi))."', '".$this->module."') ";
			
				$res_log = parent::execQuery($qry_log, $this->conn_temp);
			}
			
			$params_arr = array();
			$params_arr['parentid']  = $this->parentid;
			$params_arr['action']    = '2';
			$params_arr['data_city'] = $this->data_city;
			if(strtolower($this->module)=='jda' || strtolower($this->module)=='me'){
				$params_arr['module']    = 'me';
			}else{
				$params_arr['module']    = $this->module;
			}
			
			
			$params_arr['is_remote'] = $this->is_remote;
			 	
			$ecsMandateClassobj = new ecsMandateClass($params_arr);
			$resEcs             = $ecsMandateClassobj->getMandateDetails();
			$ecs_result         = array();
			
			if($resEcs['error']['code']==0 && $resEcs['error']['result']){
				
				if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
					
					$ecs_url = "http://karnishmaster.jdsoftware.com/web_services/biopay_api/biopay/bankdetails?identifier=1&docid=".$this->docid."&compname=".urlencode($compname)."&area=".urlencode($area)."&city=".urlencode($city)."&datacity=".urlencode($this->data_city)."&beneficiaryname=".urlencode($resEcs['error']['result']['account_name'])."&accno=".$resEcs['error']['result']['account_number']."&acctype=".urlencode($resEcs['error']['result']['account_type'])."&ifsc=".$resEcs['error']['result']['ifsc_code']."&bankname=".urlencode($resEcs['error']['result']['bank_name'])."&bstate=&bcity=".$resEcs['error']['result']['branch_location']."&bbranch=".urlencode($resEcs['error']['result']['bank_branch'])."&source=genio&case=prod";					
				}else{
					$ecs_url = "https://transactions.justdial.com/biopay_api/biopay/bankdetails?identifier=1&docid=".$this->docid."&compname=".urlencode($compname)."&area=".urlencode($area)."&city=".urlencode($city)."&datacity=".urlencode($this->data_city)."&beneficiaryname=".urlencode($resEcs['error']['result']['account_name'])."&accno=".$resEcs['error']['result']['account_number']."&acctype=".urlencode($resEcs['error']['result']['account_type'])."&ifsc=".$resEcs['error']['result']['ifsc_code']."&bankname=".urlencode($resEcs['error']['result']['bank_name'])."&bstate=&bcity=".$resEcs['error']['result']['branch_location']."&bbranch=".urlencode($resEcs['error']['result']['bank_branch'])."&source=genio&case=prod";
					
				}
				
				$res_ecs_api   = array();
				$res_ecs_api   = $this->curl_result($ecs_url);
				$ecs_result    = json_decode($res_ecs_api,true);				
				
				$qry_log   = "insert into jdpay_message_request_log (parentid, email, mobile, request_time, usercode,username,send_flag,email_send_flag,sms_send_flag,actual_sent_time, api_url,api_result, source) values ('".$this->parentid."','".$rowDetails['email']."','".$rowDetails['mobile']."','".date('Y-m-d H:i:s')."','".$rowDetails['usercode']."','".$rowDetails['username']."', '1','".$emailflg."', '".$smsflg."', '".date('Y-m-d H:i:s')."', '".$ecs_url."', '".trim(stripslashes($res_ecs_api))."', '".$this->module."') ";
	
				$res_log = parent::execQuery($qry_log, $this->conn_temp);
				
			}
			
			if($resulArr['error']['code']==0){
				$message = "Success";
				$die_msg_arr1['error']['code'] = 0;
				echo json_encode($this->send_die_message($message,0));
				die();
			}else{
				$message = "biopay api failed!!";
				echo json_encode($this->send_die_message($message,1));
				die();
			}
		
		}
	
	}
	function getJdPayAccountDetails(){
		
		$result = array();
		//check response from api for the given parentid.
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			
			$url = "http://karnishmaster.jdsoftware.com/web_services/biopay_api/biopay/bankdetails?identifier=2&docid=".trim($this->docid)."";			
			
		}else{
			
			$url = "https://transactions.justdial.com/biopay_api/biopay/bankdetails?identifier=2&docid=".trim($this->docid)."&case=prod";
			
		}
		
		$result   = json_decode($this->curl_result($url),1);
		//echo "<br>result:-";print_r($result);
		if($result['error']['code']==0 || $result['error']['code']=='0'){
			return 1;
		}else{
			return 0;
		}

	}
	
	
	private function send_die_message($msg,$errorCode)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = $errorCode;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	private function curl_result($url){
		$ch = curl_init();        
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 190);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		//echo "===>>";print_r($output);echo"<===";
		curl_close($ch); 
		return $output;
	}
	
}
?>
