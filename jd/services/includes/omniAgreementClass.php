<?php

class omniAgreementClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $ucode		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;

	function __construct($params)
	{		
		$this->params = $params;		

		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		if(trim($this->params['parentid']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Parentid Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->parentid  = $this->params['parentid']; 
		}
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->module  = $this->params['module']; 
		}
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->action  = $this->params['action']; 
		}
		if(trim($this->params['version']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "version Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->version  = $this->params['version']; 
		}
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "data city Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->data_city  = $this->params['data_city']; 
		}
		if(trim($this->params['email_appr']) != "")
		{
			$this->email_appr  = $this->params['email_appr']; 
		}

		if(trim($this->params['instrumentid']) != "")
		{
			$this->instrumentid  = $this->params['instrumentid']; 
		}
		if(trim($this->params['mobile']) != "")
		{
			$this->mobile  = $this->params['mobile']; 
		}

		$this->shorturl='';
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj = new companyClass();
		$status=$this->setServers();
		


		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$this->tempmapomniUrl="http://sunnyshende.jdsoftware.com/web_services/omni_services/templateMapping.php";
			$this->domainomniUrl="http://sunnyshende.jdsoftware.com/web_services/omni_services/domainMapping.php";
			$this->omniUrl="http://gammatesting.jdseller.com/marketplace/static/php/web/common_api.php"; 
			$this->omniHrUrl="http://tejasnikam.jdsoftware.com/HROMNI/employee/createInstance";
			$this->editlist="http://ganeshsharma.jdsoftware.com/jd_rwd_all/";
		}
		else{ 
			$this->tempmapomniUrl="http://192.168.20.102:9001/omni_services/templateMapping.php";
			$this->domainomniUrl="http://192.168.20.102:9001/omni_services/domainMapping.php";	
			$this->omniUrl="http://192.168.20.48/marketplace/static/php/web/common_api.php";
			$this->omniHrUrl="http://192.168.20.82/employee/createInstance"; 
			$this->editlist="https://www.justdial.com/"; // shld change for live
		}
		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->meurl="http://".GNO_URL;
		 
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->conn_local  		= $db[$data_city]['d_jds']['master'];
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['iro']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc  = $db[$data_city]['idc']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc  = $db[$data_city]['idc']['master'];
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_temp_new = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_idc = $this->conn_temp;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
			default:
			return -1;
			break;
		}
	}

	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_idc[0], $this->conn_idc[1], $this->conn_idc[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;
	}

	function isEligible($type=0){
		$campavl=0;
		$campaignids='';
		$dept=0;
		$combo_type='';
		if($type==0){
			$sql="select group_concat(distinct(campaignid)) as campaignid from payment_apportioning where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('72','73','1','2','5','22','13','10') AND (budget-balance)>0 AND budget>0";  
			$res = parent::execQuery($sql, $this->conn_finance);
			if($res && mysql_num_rows($res) > 0){
				while($row = mysql_fetch_assoc($res)){
					$campaignids=$row['campaignid'];
				}
				$campavl=1;
			}
			
			 $sqlcheckforcombo="SELECT * FROM db_finance.dependant_campaign_details where parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sqlcheckforcombo, $this->conn_finance);
			if($res && mysql_num_rows($res) > 0){
				while($row = mysql_fetch_assoc($res)){
					$combo_type=$row['combo_type'];
				}
				$dept=1;
			} 
			
			
		}
		elseif($type==1){
			$sql="select group_concat(distinct(campaignid)) as campaignid from tbl_companymaster_finance where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('72','73','1','2','5','22','13','10')"; 
			$res = parent::execQuery($sql, $this->conn_finance);
			if($res && mysql_num_rows($res) > 0){
				while($row = mysql_fetch_assoc($res)){
					$campaignids=$row['campaignid'];
				}
				$campavl=1;
			}
			
			 $sqlcheckforcombo="SELECT * FROM db_finance.dependant_campaign_details_appr where parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sqlcheckforcombo, $this->conn_finance);
			if($res && mysql_num_rows($res) > 0){
				while($row = mysql_fetch_assoc($res)){
					$combo_type=$row['combo_type'];
				}
				$dept=1; 
			} 
		}
		$returnarr['combo_type']=$combo_type;
		$returnarr['campaignids']=$campaignids;
		$returnarr['dept']=$dept;

		return $returnarr; 
		if($campavl==0 && $dept==0){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'No Available Campaigns'; 
			echo json_encode($result_msg_arr); 
			exit;
		}
	}

	function forDealClose(){
		$fin_details=$this->isEligible(0);
		$ecsflag=0;
		$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
		$res = parent::execQuery($checkforecs, $this->conn_finance); 
		if($res && mysql_num_rows($res) > 0)
		{
			$ecsflag=1;
		}
		$filenames=$this->getFiles($fin_details,$ecsflag);
		$this->sendMailToCustomer($filenames,0);
	}

	function forApproval(){
		$fin_details=$this->isEligible(1);
		$ecsflag=0;
		$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
		$res = parent::execQuery($checkforecs, $this->conn_finance); 
		if($res && mysql_num_rows($res) > 0)
		{
			$ecsflag=1;
		}
		$filenames=$this->getFiles($fin_details,$ecsflag);
		$this->sendMailToCustomer($filenames,1); 
	}

	function getFiles($fin_details,$ecsflag=0){
		$filenames='';
		$where='';
		 $campaignids=$fin_details['campaignids']; 
		$campaignids=explode(',', $campaignids);
		$dept=$fin_details['dept']; 
		$combo_type=$fin_details['combo_type']; 
		$wherein.="";
		foreach ($campaignids as $key => $campaignid) {
			if($campaignid=='1' || $campaignid=='2' || $campaignid=='10' ){ 
				
				if($dept==1 || $dept=='1' ){
					
					if($combo_type=='Omni Supreme'){
						$wherein.=",'combo'";
					}
					elseif($combo_type=='Combo 2'){
						$wherein.=",'combo2'";
					}
					elseif($combo_type=='Omni Ultima'){ 
						$wherein.=",'omni_ultima'"; 
					}
					else{
						$wherein.=",'combo'"; 
					}
				}
				else{
					$wherein.=",'package'";
				}
			}
			if($campaignid=='5' || $campaignid=='13' ){
				$wherein.=",'banner'";
			}
			if($campaignid=='22' ){
				$wherein.=",'jdrr'";
			}
			if($campaignid=='73' || $campaignid=='72' ){ 

				if($dept==1 || $dept=='1' ){
					
					if($combo_type=='Omni Supreme'){
						$wherein.=",'combo'";
					}
					elseif($combo_type=='Combo 2'){
						$wherein.=",'combo2'";
					}
					elseif($combo_type=='Omni Ultima'){ 
						$wherein.=",'omni_ultima'"; 
					}
					else{
						$wherein.=",'combo'"; 
					}
				}
				else{
					$wherein.=",'omni_normal'"; 
				}
			}

		}
		if(trim($wherein)==''){
			$wherein.=",'package'";
		}
		$wherein=rtrim($wherein,",");
		$wherein=ltrim($wherein,",");
		$wherein=explode(',', $wherein);
		$wherein=implode(',', array_unique($wherein));
		$where="where omni_type in ($wherein)";
		if($ecsflag==0)
		{
			$where.="and omni_mode='upfront'";
		}
		else
			$where.="and omni_mode='ecs'";  
		

		 $filestosend="SELECT GROUP_CONCAT(DISTINCT(files_to_send)) as files, GROUP_CONCAT(DISTINCT(file_names)) as file_names, GROUP_CONCAT(files_for_sms,'~',campaign_name  SEPARATOR '|~|') as file_sms_names FROM online_regis1.tbl_omni_agreement $where";

			$resfiles = parent::execQuery($filestosend, $this->conn_idc);

			while($rowfiles = mysql_fetch_assoc($resfiles)){
				
				$filenames=$rowfiles['files'];  
				$file_names=$rowfiles['file_names'];  
				$file_sms_names=$rowfiles['file_sms_names'];  
		}
		$filenames=explode(',', $filenames);
		$filenames=implode(',',array_unique($filenames));
		$file_names=explode(',', $file_names);
		$file_names=implode(',',array_unique($file_names));

		$files['filenames']=$filenames;
		$files['filenames_title']=$file_names;
		$files['filenames_title_sms']=$file_sms_names;
		return $files;
	}

	function sendMailToCustomer($filenames,$type){
		$setpath='';
		global $db;
		$filenames=explode(',', $filenames);
		foreach ($filenames as $key => $value) {
			$setpath.=",http://".GNO_URL."/omni_agreement/".$value; 
		}
		$setpath=trim($setpath,",");
		require_once('class_send_sms_email.php');
		
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);
		$here="<a target='_BLANK' href='http://www.justdial.com/Terms-of-Use/JD-Omni'>here</a>"  ;
		$body.="Dear Customer,<br><br>";
		$body.="Thank you for your recent registration with Just Dial Services.<br>";
		$body.="Please check the attachment for Terms and Conditions of Service.<br><br><br>"; 
		$body.="Thanking you,<br>";
		$body.="Just Dial Limited<br>";
		$body.="Customer Support Department<br>"; 
		$email=$this->getEmail($type);
		//$email='ganeshrj2010@gmail.com';
		if($email==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'No Email Id'; 
			echo json_encode($result_msg_arr); 
			exit;
		}
		//$emailsend=$smsObj->sendEmailwithAttachment($email, 'noreply@justdial.com', 'Thank you for your registration with Just Dial Services.', $body,'ME',$this->parentid,$setpath); 
		if($email){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			echo json_encode($result_msg_arr); 
			exit;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); 
			exit;
		}
	}

	function getEmail($type=0){
		$email='';
		if($type==0){
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "email";
				$rowemail = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sqlemail="select email from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$resemail = parent::execQuery($sqlemail, $this->conn_temp_new);
				if($resemail && mysql_num_rows($resemail) > 0){
					$rowemail = mysql_fetch_assoc($resemail);
				}
			}
			$email=$rowemail['email'];
		}
		elseif($type==1){

			$rowemail = array();
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'email';
			$cat_params['page']			= 'omniAgreementClass';

			$res_gen_info1		= 	array();
			$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

			if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
				$rowemail 		=	$res_gen_info1['results']['data'][$this->parentid];
				$email 			= 	$rowemail['email']; 
			}


		 	/*$sqlemail="select email from db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			$resemail = parent::execQuery($sqlemail, $this->dbConIro);

			if($resemail && mysql_num_rows($resemail) > 0){
					while($rowemail = mysql_fetch_assoc($resemail)){
						$email=$rowemail['email']; 
				}
			}*/
		}
		$email=explode(',',$email);
		$email=empty($email[0])?$email[1]:$email[0]; 
		//$email='ganeshrj2010@gmail.com'; 
		if($this->email_appr!='')
			$email=$this->email_appr;
		return $email; 
	}

	function getMobile($type=0){
		$mobile='';
		
		if($type==0){
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "mobile";
				$rowemail = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sqlemail="select mobile from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$resemail = parent::execQuery($sqlemail, $this->conn_temp_new);
				if($resemail && mysql_num_rows($resemail) > 0){ 
					$rowemail = mysql_fetch_assoc($resemail);
				}
				
			}
			$mobile=$rowemail['mobile'];
		}
		elseif($type==1){

			$rowemail = array();
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'mobile';
			$cat_params['page']			= 'omniAgreementClass';

			$res_gen_info1		= 	array();
			$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

			if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
				$rowemail 		=	$res_gen_info1['results']['data'][$this->parentid];
				$mobile=$rowemail['mobile'];
			}
			
			/*$sqlemail="select mobile from db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			$resemail = parent::execQuery($sqlemail, $this->dbConIro);

			if($resemail && mysql_num_rows($resemail) > 0){
					while($rowemail = mysql_fetch_assoc($resemail)){
						$mobile=$rowemail['mobile']; 
				}
			}*/
		}
		$mobile=explode(',',$mobile);
		$mobile=empty($mobile[0])?$mobile[1]:$mobile[0]; 
		//$email='ganeshrj2010@gmail.com'; 
		return $mobile; 
	}

	function getDocid(){
		$sqldocid="select docid from tbl_id_generator where parentid='".$this->parentid."'";
		$resdocid = parent::execQuery($sqldocid, $this->dbConIro);
		if($resdocid && mysql_num_rows($resdocid) > 0){
				while($rowdocid = mysql_fetch_assoc($resdocid)){
					$docid=$rowdocid['docid'];  
			}
		}

		return $docid;
	}

	function checkFor72hrs(){

		$cond='false';
		$ret=array();
		$dealclose='';
		$sql="select * from payment_apportioning where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('72','73','1','2','5','22','13','10') AND (budget-balance)>0 AND budget>0 ";  
		$res = parent::execQuery($sql, $this->conn_finance);

		if($res && mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$dealclose=$row['entry_date'];
				$dealclose_str= new DateTime($dealclose);
				$now=date('Y-m-d H:i:s',strtotime("-3 days")); 
				$date_now_str=new DateTime($now);
			
				if($dealclose_str<=$date_now_str){
				 $cond='false';
				}
				else{
				 $cond='true';  
				}
			}
		}

		$ret['dealclose_date']=$dealclose;
		$ret['cond']=$cond; 
		return $ret;
	}

	function onBoardingPostApprovalMail(){	
		global $db;
		$firstfile=file_get_contents("mailer_files/after_payment_newcs.html");   
		$companydet=$this->getCompanyDetails(1);		
		$companyname = $companydet['companyname'];		
		$add_type=$companydet['add_type'];
		$pay_mode=$companydet['pay_mode'];
		$from_email=$this->getEmailid();
		
		if(!$from_email)
		$from_email='noreply@justdial.com';
		
		require_once('class_send_sms_email.php');
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);
		
		$src=$this->parentid."_".$this->version;  
		$citycode=0;
		switch($this->data_city_cm){
			case 'ahmedabad':$citycode=0;
			break;
						case 'ahmedabad':$citycode=56;
			break;
						case 'bangalore':$citycode=26;
			break;
						case 'chennai':$citycode=32;
			break;
						case 'delhi':$citycode=8;
			break;
						case 'hyderabad':$citycode=50;
			break;
						case 'kolkata':$citycode=16;
			break;
						case 'mumbai':$citycode=0;
			break;
			case 			'pune':$citycode=40;
			break;
		}
		if(trim($companyname)==''){
			$companyname='Customer';
		}
		$email=$this->getEmail(1);
		if($this->data_city_cm!='remote') 
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
		else
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

		
		$mobile    = $this->getMobile(1);		
		//$email  = 'saritha.pc@justdial.com';
		//$mobile = '9739197411';
		$sqlgetup ="select * from online_regis1.tbl_genio_apis_access where access_to='genio'";
		$res_acc =parent::execQuery($sqlgetup, $this->conn_idc); 
		$secretkey='';
		if($res_acc && mysql_num_rows($res_acc) > 0){
					while($row_acc = mysql_fetch_assoc($res_acc)){
					$secretkey=$row_acc['secret_key']; 
				}
		}	
		$api_key = hash_hmac('sha256', 'genio',(date('Y-m-d') . $secretkey ));  
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			 $genio_url="http://saritapc.jdsoftware.com/MEGENIO/api/agreement_api.php?parentid=".$this->parentid."&version=".$this->version."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
		}else{ 
			//~ $genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
			$genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&version=".$this->version."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
		}
		
		$link_bus=$this->editlist."EC-".$this->shorturl."/edit/em";
		$link_cat=$this->editlist."EC-".$this->shorturl."/key/em";
		$link_hr=$this->editlist."EC-".$this->shorturl."/h/em";		
		$website = $companydet['website'];
		$str= '';
		if($website!=''){
			$website_arr = explode(",",$website);
			$website_arr = array_unique(array_filter($website_arr));
			if(count($website_arr)>0){
				foreach($website_arr as $key=>$value){
					if($str==''){
						$str = '<a href="https://'.$value.'" style="text-decoration:none;color: #293139" target="_BLANK">'.$value.'</a>';
					}else{
						$str .= ', <a href="https://'.$value.'" style="text-decoration:none;color: #293139" target="_BLANK">'.$value.'</a>';
					}
				}
			}
		}
		$firstfile=str_replace(array("CUST_NAME","BUSINESS_NAME","ADD_TYPE","PAY_MODE","CONT_PERSON","COMP_ADDRESS","F_MOBILE_FIELDS","MOBILE_FIELDS","LL_LINE","WEBSITE_ADD","CATEGORIES_SELECTED","TIMINGS","GENIO_ACC_URL","EDITLISTING_BUS","EDITLISTING_CAT","EDITLISTING_HR","FORTRACKING"),array($companydet['contact_person'],$companydet['companyname'], $companydet['add_type'], $companydet['pay_mode'],$companydet['contact_person'],$companydet['full_address'],$companydet['mobile_feedback'],$companydet['mobile'],$companydet['landline'],$str,$companydet['category'],$companydet['timings'],$genio_url,$link_bus,$link_cat,$link_hr,$div),$firstfile);       
		
		$email_body=$firstfile ;
	
		if($email!='' ){
			$ecsflag=0;
			$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
			$res = parent::execQuery($checkforecs, $this->conn_finance); 
			if($res && mysql_num_rows($res) > 0){
				$ecsflag=1;
			}
			$ecsflag = 1;
			$fin_details=$this->isEligible(1);			
			$filenarr=$this->getFiles($fin_details,$ecsflag);
			$filenames_title_sms=$filenarr['filenames_title_sms'];
			$filenames_title_sms_new='';
			$filenames_title_sms=explode('|~|', $filenames_title_sms);			
			$newsmsarr=array();
			foreach ($filenames_title_sms as $smskey => $smsvalue) {				
				$smsvalue=explode('~',  $smsvalue);				
				$filenamesforsms=explode(",", $smsvalue[0]);
				foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {					
					if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){							
						$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
						$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
						unset($newsmsarr[$smkey]);
					}else{
						$smsvalue[1];
						$newsmsarr[$smsvalue[1]]=trim($filenamesforsmsvalue);
					}
				}
			}			
			$filenames_title_sms=$newsmsarr; 
			foreach ($filenames_title_sms as $ffkey => $ffvalue) {
				$filenames_title_sms_new.= "\n".$ffkey." http://genio.in/tc/".$ffvalue;
			}
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
			$sms_text="Justdial thanks you for accepting the Terms of Services\n".$filenames_title_sms_new;
			$sms_text=trim($sms_text);	
			if($email!=''){				
				//~ $smsSend   = $smsObj->sendSMS($mobile, $this->mysql_real_escape_custom($sms_text), 'cs_onboarding',$this->parentid);
				$emailsend = $smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', addslashes(stripslashes($email_body)),'cs_onboarding',$this->parentid); 				 
				if($emailsend){					
					$logsql = "update d_jds.tbl_cs_onboarding_mail_details set 
										    emailid = '".$email."',
										    mobile  = '".$mobile."',
										    mail_sent = '".$emailsend."', 
										    sms_sent  = '".$smsSend."', 
										    done_flag = 1,
										    last_mail_sent = '".date('Y-m-d H:i:s')."', 
										    email_content  = '".addslashes(stripslashes($email_body))."' 
											where parentid ='".$this->parentid."' and version='".$this->version."' ";
					$logres = parent::execQuery($logsql, $this->conn_local);
					
					$mailSentLog = "INSERT INTO tbl_cs_onboarding_last_mail_sent_log SET
														 parentid				= '".$this->params['parentid']."',
														 data_city				= '".$this->params['data_city']."',
														 version 				= '".$this->params['version']."',
														 approvedDate			= '".urldecode($this->params['approvedDate'])."',
														 emailid 				= '".$email."',
														 mobile				 	= '".$mobile."',
														 email_sent				= '".$emailsend."', 
														 sms_sent				= '".$smsSend."', 														 
														 updateOn				= '".date('Y-m-d H:i:s')."',
														 last_mail_sent			= '".date('Y-m-d H:i:s')."'";						
					$res_attr_search 	= parent::execQuery($mailSentLog, $this->conn_local);	
				}
				
				if($emailsend){
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['msg'] = 'Email sent successfully  to "'.$email.'"';
				}else{
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['msg'] = 'Email Not to "'.$email.'" ';
				}
			}else if($email!=''){
				$emailsend = $smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', addslashes(stripslashes($email_body)),'cs_onboarding',$this->parentid); 
				
				$logsql = "update d_jds.tbl_cs_onboarding_mail_details set 
										    emailid = '".$email."',
										    mobile  = 'no mobile',										    
										    mail_sent  = '".$emailsend."', 
										    done_flag  = 1,
										    last_mail_sent = '".date('Y-m-d H:i:s')."', 
										    email_content  = '".addslashes(stripslashes($email_body))."' 
											where parentid='".$this->parentid."'  and  version='".$this->version."'";
				$logres = parent::execQuery($logsql, $this->conn_local);
				
				$mailSentLog = "INSERT INTO tbl_cs_onboarding_last_mail_sent_log SET
														 parentid				= '".$this->params['parentid']."',
														 data_city				= '".$this->params['data_city']."',
														 version 				= '".$this->params['version']."',
														 approvedDate			= '".urldecode($this->params['approvedDate'])."',
														 emailid 				= '".$email."',
														 mobile				 	= 'no mobile',
														 email_sent				= '".$emailsend."', 
														 sms_sent				= '0', 														 
														 updateOn				= '".date('Y-m-d H:i:s')."',
														 last_mail_sent			= '".date('Y-m-d H:i:s')."'";						
				$res_attr_search 	= parent::execQuery($mailSentLog, $this->conn_local);	
				
				if($emailsend){
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['msg'] = 'mobile number not found, Email sent successfully to "'.$email.'"';
				}
			}
			echo json_encode($result_msg_arr); 
			return;
		}else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'mobile and Email not found';
			echo json_encode($result_msg_arr); 
			return;
		}
	}

	function onBoardingSendMails(){	
		global $db;
		$firstfile=file_get_contents("mailer_files/after_payment_newcs.html");   
		$companydet=$this->getCompanyDetails(1);		
		$companyname = $companydet['companyname'];		
		$add_type=$companydet['add_type'];
		$pay_mode=$companydet['pay_mode'];
		$from_email=$this->getEmailid();
		
		if(!$from_email)
		$from_email='noreply@justdial.com';
		
		require_once('class_send_sms_email.php');
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);
		
		$src=$this->parentid."_".$this->version; 
		$citycode=0;
		switch($this->data_city_cm){
			case 'ahmedabad':$citycode=0;
			break;
			case 'ahmedabad':$citycode=56;
			break;
			case 'bangalore':$citycode=26;
			break;
			case 'chennai':$citycode=32;
			break;
			case 'delhi':$citycode=8;
			break;
			case 'hyderabad':$citycode=50;
			break;
			case 'kolkata':$citycode=16;
			break;
			case 'mumbai':$citycode=0;
			break;
			case 'pune':$citycode=40;
			break;
		}
		if(trim($companyname)==''){
			$companyname='Customer';
		}
		if(!isset($this->params['email']) || $this->params['email']==''){
			$email=$this->getEmail(1);
		}else{
			$email = $this->params['email'];
		}
		if($this->data_city_cm!='remote') 
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
		else
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

		
		$mobile   = $this->getMobile(1);			
		$sqlgetup ="select * from online_regis1.tbl_genio_apis_access where access_to='genio'";
		$res_acc =parent::execQuery($sqlgetup, $this->conn_idc); 
		$secretkey='';
		if($res_acc && mysql_num_rows($res_acc) > 0){
					while($row_acc = mysql_fetch_assoc($res_acc)){
					$secretkey=$row_acc['secret_key']; 
				}
		}	
		$api_key = hash_hmac('sha256', 'genio',(date('Y-m-d') . $secretkey ));  
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			
			$genio_url="http://saritapc.jdsoftware.com/MEGENIO/api/agreement_api.php?parentid=".$this->parentid."&version=".$this->version."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
			//~ echo "<pre>url:------";print_r($genio_url);die;
		}else{ 
			//~ $genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
			$genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&version=".$this->version."&data_city=".$this->data_city."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1&post_approval=1&email=".urlencode($email)."&mobile=".urlencode($mobile)."";
		}
		
		$link_bus=$this->editlist."EC-".$this->shorturl."/edit/em";
		$link_cat=$this->editlist."EC-".$this->shorturl."/key/em";
		$link_hr=$this->editlist."EC-".$this->shorturl."/h/em";		
		
		$firstfile=str_replace(array("CUST_NAME","BUSINESS_NAME","ADD_TYPE","PAY_MODE","CONT_PERSON","COMP_ADDRESS","F_MOBILE_FIELDS","MOBILE_FIELDS","LL_LINE","WEBSITE_ADD","CATEGORIES_SELECTED","TIMINGS","GENIO_ACC_URL","EDITLISTING_BUS","EDITLISTING_CAT","EDITLISTING_HR","FORTRACKING"),array($companydet['contact_person'],$companydet['companyname'], $companydet['add_type'], $companydet['pay_mode'],$companydet['contact_person'],$companydet['full_address'],$companydet['mobile_feedback'],$companydet['mobile'],$companydet['landline'],$companydet['website'],$companydet['category'],$companydet['timings'],$genio_url,$link_bus,$link_cat,$link_hr,$div),$firstfile);       
		
		$email_body=$firstfile ;
	
		if($email!='' || $mobile!=''){
			$ecsflag=0;
			$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
			$res = parent::execQuery($checkforecs, $this->conn_finance); 
			if($res && mysql_num_rows($res) > 0){
				$ecsflag=1;
			}
			$ecsflag = 1;
			$fin_details=$this->isEligible(1);			
			$filenarr=$this->getFiles($fin_details,$ecsflag);
			$filenames_title_sms=$filenarr['filenames_title_sms'];
			$filenames_title_sms_new='';
			$filenames_title_sms=explode('|~|', $filenames_title_sms);			
			$newsmsarr=array();
			foreach ($filenames_title_sms as $smskey => $smsvalue) {				
				$smsvalue=explode('~',  $smsvalue);				
				$filenamesforsms=explode(",", $smsvalue[0]);
				foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {					
					if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){							
						$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
						$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
						unset($newsmsarr[$smkey]);
					}else{
						$smsvalue[1];
						$newsmsarr[$smsvalue[1]]=trim($filenamesforsmsvalue);
					}
				}
			}			
			$filenames_title_sms=$newsmsarr; 
			foreach ($filenames_title_sms as $ffkey => $ffvalue) {
				$filenames_title_sms_new.= "\n".$ffkey." http://genio.in/tc/".$ffvalue;
			}
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
			$sms_text="Justdial thanks you for accepting the Terms of Services\n".$filenames_title_sms_new;
			$sms_text=trim($sms_text);	
			if($email!=''){				
				//~ $smsSend   = $smsObj->sendSMS($mobile, $this->mysql_real_escape_custom($sms_text), 'agreement_dashboard',$this->parentid);
				$emailsend = $smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', addslashes(stripslashes($email_body)),'agreement_dashboard',$this->parentid); 				 
				if($smsSend || $emailsend){					
					$logsql = "update d_jds.tbl_cs_onboarding_mail_details set 
										    emailid = '".$email."',
										    mobile  = '".$mobile."',
										    mail_sent = '".$emailsend."', 
										    sms_sent  = '".$smsSend."', 
										    done_flag = 9
											where parentid='".$this->parentid."' and version='".$this->version."'";
					$logres = parent::execQuery($logsql, $this->conn_local);
				}				
				if($emailsend){
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['email'] = $email_body;
					$result_msg_arr['error']['msg'] = 'Email sent successfully  to "'.$email.'"';
				}else{
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['email'] = $email_body;
					$result_msg_arr['error']['msg'] = 'Email Not sent To "'.$email.'" ';
				}
			}else if($email!=''){
				$emailsend = $smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', addslashes(stripslashes($email_body)),'agreement_dashboard',$this->parentid); 
				
				$logsql = "update d_jds.tbl_cs_onboarding_mail_details set 
										    emailid = '".$email."',									    
										    mail_sent  = '".$emailsend."', 
										    done_flag = 9
											where parentid='".$this->parentid."' and version='".$this->version."'";
				$logres = parent::execQuery($logsql, $this->conn_local);
				if($emailsend){
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['email'] = $email_body;
					$result_msg_arr['error']['msg'] = 'mobile number not found, Email sent successfully to "'.$email.'"';
				}
			}
			echo json_encode($result_msg_arr); 
			return;
		}else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'mobile and Email not found';
			echo json_encode($result_msg_arr); 
			return;
		}
	}

	function dealCloseSendMails(){
		global $db;
		$firstfile='';
		$firstfile=file_get_contents("mailer_files/1.html");  

		$companyname=$this->getContactDetails(0);
		
		$from_email=$this->getEmailid();
		$ecsflag=0;
		$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
		$res = parent::execQuery($checkforecs, $this->conn_finance); 
		if($res && mysql_num_rows($res) > 0)
		{
			$ecsflag=1;
		}
		$fin_details=$this->isEligible(0);

		$filenarr=$this->getFiles($fin_details,$ecsflag);

		$filenames=$filenarr['filenames'];
		$file_title=$filenarr['filenames_title'];
		$filenames_title_sms=$filenarr['filenames_title_sms'];
		$file_title=explode(',', $file_title);
		$filenames=strtoupper($filenames);
		$filenames=str_replace(".PDF",'.html', $filenames);

		$filenames=explode(',', $filenames);
		$newlink='';
		$tos='';

		$no=1;
		$sms_text='';
		foreach ($filenames as $hkey => $hvalue) {
			$newlink='http://genio.in/omni_agreement/'.$hvalue;
				
			if(trim($file_title[$hkey])=='Terms Of Service' || strtolower(trim($file_title[$hkey]))=='terms of service'){
				$file_title[$hkey]='';
			}
			$link='<a href="'.$newlink.'" style="text-decoration:none;color:#1274c0;">Terms Of Service '.$file_title[$hkey].'</a><br>';
			$tos.="&nbsp;&nbsp;<br>".$link;
			$no++;	
		}
		$filenames_title_sms_new='';
		$filenames_title_sms=explode('|~|', $filenames_title_sms);
		
		$newsmsarr=array();
		foreach ($filenames_title_sms as $smskey => $smsvalue) {	
			$smsvalue=explode('~',  $smsvalue);
			
			$filenamesforsms=explode(",", $smsvalue[0]);
			foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {	
				if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){					
					$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
					$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
					unset($newsmsarr[$smkey]);
				}
				else{
					$smsvalue[1];
					$newsmsarr[$smsvalue[1]].=",".trim($filenamesforsmsvalue);
					$newsmsarr[$smsvalue[1]]=ltrim($newsmsarr[$smsvalue[1]],","); 
				}
			}
		}
			
		$filenames_title_sms=$newsmsarr; 
			
		foreach ($filenames_title_sms as $ffkey => $ffvalue) {
			$ffvalue=explode(',', $ffvalue);
			$som='';
			foreach ($ffvalue as $smkey => $smvalue) {
				$som.="\n"." http://genio.in/tc/".$smvalue;
				$som=ltrim($som,"\n"); 
			}
			$filenames_title_sms_new.= "\n".$ffkey."\n".$som; 
		}
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
			
			//$filenames_title_sms=str_replace(",","\n", $filenames_title_sms_new);
			$sms_text="Thank you for registering your business with Justdial.\nPlease find below Terms of services\n".$filenames_title_sms_new;

			$sms_text=trim($sms_text);
			$newlink=ltrim($newlink,","); 
				
			$docid=$this->getDocid();
			$this->docid=$docid;
			$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$docid;
			if(!$from_email)
			$from_email='noreply@justdial.com';

			require_once('class_send_sms_email.php');
			$smsObj	 = new email_sms_send($db,$this->data_city_cm);
			$email=$this->getEmail(1);
			//$email='ganeshrj2010@gmail.com';
			
			$firstfile=str_replace('NAME_CONSTANT', $companyname['contact_person'], $firstfile) ;   
			$firstfile=str_replace('MAILINGCONT', $from_email, $firstfile) ;
			$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;
			$firstfile=str_replace('SOURCE', $this->module, $firstfile) ;
			$firstfile=str_replace('CITYCODE', 0, $firstfile) ; 
			$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;
			$src=$this->parentid."_".$this->version;

			$citycode=0;
			switch($this->data_city_cm){
				case 'ahmedabad':$citycode=0;
				break;
				case 'ahmedabad':$citycode=56;
				break;
				case 'bangalore':$citycode=26;
				break;
				case 'chennai':$citycode=32;
				break;
				case 'delhi':$citycode=8;
				break;
				case 'hyderabad':$citycode=50;
				break;
				case 'kolkata':$citycode=16;
				break;
				case 'mumbai':$citycode=0;
				break;
				case 'pune':$citycode=40;
				break;
			}
			if(trim($companyname['contact_person'])==''){
				$companyname1='Customer';
			}else{
				$companyname1=$companyname['contact_person'];
			}
			
			if($this->data_city_cm!='remote')// change to != for testing
			$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
			else
			$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

			/* <a href="#" style="text-decoration:none;color:#1274c0;">Terms of Service</a>.*/
			
			$firstfile=str_replace(array("NAME_CONSTANT","MAILINGCONT","CUSTEMAIL","SOURCE","CITYCODE","EDITLISTING","FORTRACKING","TERMSOFSERVICE","EMAIL"),array($companyname1, $from_email, $email, $this->parentid."_approval_mail",0,$link2,$div,$tos,$email),$firstfile);      
			
			$email_body=$this->mysql_real_escape_custom($firstfile) ;
			 //echo "<pre>params:--";print_r($this->params);
			if(isset($this->params['email']) && $this->params['email']!=''){
				$email = $this->params['email'];
			}else{
				$email=$this->getEmail(0);
			}
			
			if($email==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = 'No Email Id'; 
				echo json_encode($result_msg_arr); 
				exit;
			}
			
			$emailsend=$smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', $email_body,'agreement_dashboard',$this->parentid);
			 //~ $mobile=$this->getMobile(0); dont take this live
			//~ if($mobile!=''){			
				//~ $emailsend=$smsObj->sendSMSInvoice($mobile, $sms_text, 'agreement_dashboard',$this->parentid);
			//~ }
			
			if($firstfile!=''){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Success';

				$result_msg_arr['data']['msg'] = $firstfile;
				echo json_encode($result_msg_arr); 
				exit;
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = 'Error';
				echo json_encode($result_msg_arr); 
				exit;
			} 		 
	}

	function onBoardingApprovalMails(){
		global $db;
		$firstfile=file_get_contents("mailer_files/newcs.html");   
		$companydet=$this->getCompanyDetails(1);		
		$companyname = $companydet['companyname'];		
		$add_type=$companydet['add_type'];
		$pay_mode=$companydet['pay_mode'];
		$from_email=$this->getEmailid();
		$docid=$this->getDocid();
		$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$docid;

		if(!$from_email)
		$from_email='noreply@justdial.com';
		
		require_once('class_send_sms_email.php');
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);
		
		$src=$this->parentid."_".$this->version;  
		$citycode=0;
		switch($this->data_city_cm){
			case 'ahmedabad':$citycode=0;
			break;
						case 'ahmedabad':$citycode=56;
			break;
						case 'bangalore':$citycode=26;
			break;
						case 'chennai':$citycode=32;
			break;
						case 'delhi':$citycode=8;
			break;
						case 'hyderabad':$citycode=50;
			break;
						case 'kolkata':$citycode=16;
			break;
						case 'mumbai':$citycode=0;
			break;
			case 			'pune':$citycode=40;
			break;
		}
		if(trim($companyname)==''){
			$companyname='Customer';
		}
		$email=$this->getEmail(1);
		if($this->data_city_cm!='remote') 
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
		else
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

		
				
		$sqlgetup="select * from online_regis1.tbl_genio_apis_access where access_to='genio'";
		$res_acc =parent::execQuery($sqlgetup, $this->conn_idc); 
		$secretkey='';
		if($res_acc && mysql_num_rows($res_acc) > 0){
					while($row_acc = mysql_fetch_assoc($res_acc)){
					$secretkey=$row_acc['secret_key']; 
				}
		}	
		$api_key = hash_hmac('sha256', 'genio',(date('Y-m-d') . $secretkey ));  
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
			 $genio_url="http://saritapc.jdsoftware.com/MEGENIO/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&action=genio&key=".$api_key."&version=".$this->version."&api_called_by=genio&accepted=1";
		}else{ 
			$genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&action=genio&key=".$api_key."&version=".$this->version."&api_called_by=genio&accepted=1";
		}
		
		$link_bus=$this->editlist."EC-".$this->shorturl."/edit/em";
		$link_cat=$this->editlist."EC-".$this->shorturl."/key/em";
		$link_hr=$this->editlist."EC-".$this->shorturl."/h/em";		
		
		$firstfile=str_replace(array("CUST_NAME","BUSINESS_NAME","ADD_TYPE","PAY_MODE","CONT_PERSON","COMP_ADDRESS","F_MOBILE_FIELDS","MOBILE_FIELDS","LL_LINE","WEBSITE_ADD","CATEGORIES_SELECTED","TIMINGS","GENIO_ACC_URL","EDITLISTING_BUS","EDITLISTING_CAT","EDITLISTING_HR","FORTRACKING"),array($companydet['contact_person'],$companydet['companyname'], $companydet['add_type'], $companydet['pay_mode'],$companydet['contact_person'],$companydet['full_address'],$companydet['mobile_feedback'],$companydet['mobile'],$companydet['landline'],$companydet['website'],$companydet['category'],$companydet['timings'],$genio_url,$link_bus,$link_cat,$link_hr,$div),$firstfile);       
		
		$email_body=$firstfile ;
		
		//~ $mobile    = $this->getMobile(1);
		//$mobile    = '9739197411';
		//$email 	   = 'saritha.pc@justdial.com';
		if($email!=''){
			$ecsflag=0;
			$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
			$res = parent::execQuery($checkforecs, $this->conn_finance); 
			if($res && mysql_num_rows($res) > 0){
				$ecsflag=1;
			}
			$ecsflag = 1;
			$fin_details=$this->isEligible(1);			
			$filenarr=$this->getFiles($fin_details,$ecsflag);
			$filenames_title_sms=$filenarr['filenames_title_sms'];
			$filenames_title_sms_new='';
			$filenames_title_sms=explode('|~|', $filenames_title_sms);			
			$newsmsarr=array();
			foreach ($filenames_title_sms as $smskey => $smsvalue) {				
				$smsvalue=explode('~',  $smsvalue);				
				$filenamesforsms=explode(",", $smsvalue[0]);
				foreach ($filenamesforsms as $filenamesforsmskey => $filenamesforsmsvalue) {					
					if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){							
						$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
						$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
						unset($newsmsarr[$smkey]);
					}else{
						$smsvalue[1];
						$newsmsarr[$smsvalue[1]]=trim($filenamesforsmsvalue);
					}
				}
			}			
			$filenames_title_sms=$newsmsarr; 
			foreach ($filenames_title_sms as $ffkey => $ffvalue) {
				$filenames_title_sms_new.= "\n".$ffkey." http://genio.in/tc/".$ffvalue;
			}
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
			$sms_text="Justdial thanks you for accepting the Terms of Services\n".$filenames_title_sms_new;
			$sms_text=trim($sms_text);	
			if($email!=''){				
				//~ $smsSend   = $smsObj->sendSMSInvoice($mobile, $this->mysql_real_escape_custom($sms_text), 'cs_onboarding',$this->parentid);
				$emailsend = $smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', addslashes(stripslashes($email_body)),'cs_onboarding',$this->parentid); 
				$logsql = "update tbl_agreement_approval set 
											sent_date='".date('Y-m-d H:i:s')."',
											cron=1
											where parentid='".$this->parentid."' and version='".$this->version."'";
				$logres = parent::execQuery($logsql, $this->conn_finance);
				if($emailsend){
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['msg'] =  'Email sent successfully  to "'.$email.'"';
				}else{
					$result_msg_arr['error']['code'] = 0;	
					$result_msg_arr['error']['msg'] = 'No Email sent';
				}
			}
			echo json_encode($result_msg_arr); 
			return;
		}else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'mobile and Email not found';
			echo json_encode($result_msg_arr); 
			return;
		}			
		
	}

	function sendApprovalMails(){
		global $db;
		/*$con72hrs=$this->checkFor72hrs();
		$con72hrs='false'; //// no need to chec now will go on approval
		if($con72hrs['cond']=='true'){  
			 if($con72hrs['dealclose_date']!=''){
				  $sql_res = "INSERT INTO tbl_agreement_approval set
				 		 					parentid='".$this->parentid."',
				 		 					version='".$this->version."',
				 		 					dealclose_date='".$con72hrs['dealclose_date']."',
				 		 					cron='0'
				 		 					ON DUPLICATE KEY UPDATE
				 		 					dealclose_date='".$con72hrs['dealclose_date']."',
				 		 					cron='0'";  

				$sql_res_sql = parent::execQuery($sql_res, $this->conn_finance); 
			}
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = '72 hrs condition not satisfied';
			echo json_encode($result_msg_arr); 
			return;
		} */
		
		$firstfile=file_get_contents("mailer_files/newcs.html");   
		$companydet=$this->getCompanyDetails(1);
		$companyname=$companyname['companyname'];
		
		$add_type=$companydet['add_type'];
		$pay_mode=$companydet['pay_mode'];
		$from_email=$this->getEmailid();
		$docid=$this->getDocid();
		$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$docid;

		if(!$from_email)
		$from_email='noreply@justdial.com';

		
		require_once('class_send_sms_email.php');
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);
		
		//$email='ganeshrj2010@gmail.com';
		
		/*	$firstfile=str_replace('NAME_CONSTANT', $companyname, $firstfile) ;   
		$firstfile=str_replace('MAILINGCONT', $from_email, $firstfile) ;
		$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;
		$firstfile=str_replace('SOURCE', $this->module, $firstfile) ;
		$firstfile=str_replace('CITYCODE', 0, $firstfile) ; 
		$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;*/ 
		$src=$this->parentid."_".$this->version;  
		$citycode=0;
		switch($this->data_city_cm){
			case 'ahmedabad':$citycode=0;
			break;
						case 'ahmedabad':$citycode=56;
			break;
						case 'bangalore':$citycode=26;
			break;
						case 'chennai':$citycode=32;
			break;
						case 'delhi':$citycode=8;
			break;
						case 'hyderabad':$citycode=50;
			break;
						case 'kolkata':$citycode=16;
			break;
						case 'mumbai':$citycode=0;
			break;
			case 			'pune':$citycode=40;
			break;
		}
		if(trim($companyname)=='')
		{
			$companyname='Customer';
		}
		$email=$this->getEmail(1);
		//$email='sarithapoojari@gmail.com'; 
		if($this->data_city_cm!='remote') 
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
		else
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=approval_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

		/*$firstfile=str_replace(array("NAME_CONSTANT","MAILINGCONT","CUSTEMAIL","SOURCE","CITYCODE","EDITLISTING","EMAIL","FORTRACKING"),array($companyname, $from_email, $email, $this->parentid."_approval_mail",0,$link2,$email,$div),$firstfile);      */
				
		$sqlgetup="select * from online_regis1.tbl_genio_apis_access where access_to='genio'";
		$res_acc =parent::execQuery($sqlgetup, $this->conn_idc); 
		$secretkey='';
		if($res_acc && mysql_num_rows($res_acc) > 0){
					while($row_acc = mysql_fetch_assoc($res_acc)){
					$secretkey=$row_acc['secret_key']; 
				}
		}
	
		$api_key = hash_hmac('sha256', 'genio',(date('Y-m-d') . $secretkey ));  

		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			 $genio_url="http://saritapc.jdsoftware.com/MEGENIO/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&action=genio&key=".$api_key."&version=".$this->version."&api_called_by=genio&accepted=1";
		}
		else{ 
			$genio_url="http://genio.in/api/agreement_api.php?parentid=".$this->parentid."&data_city=".$this->data_city."&version=".$this->version."&action=genio&key=".$api_key."&api_called_by=genio&accepted=1";
		}
		
		  $link_bus=$this->editlist."EC-".$this->shorturl."/edit/em";

		 $link_cat=$this->editlist."EC-".$this->shorturl."/key/em";

		  $link_hr=$this->editlist."EC-".$this->shorturl."/h/em";
		
		 $firstfile=str_replace(array("CUST_NAME","BUSINESS_NAME","ADD_TYPE","PAY_MODE","CONT_PERSON","COMP_ADDRESS","F_MOBILE_FIELDS","MOBILE_FIELDS","LL_LINE","WEBSITE_ADD","CATEGORIES_SELECTED","TIMINGS","GENIO_ACC_URL","EDITLISTING_BUS","EDITLISTING_CAT","EDITLISTING_HR","FORTRACKING"),array($companydet['contact_person'],$companydet['companyname'], $companydet['add_type'], $companydet['pay_mode'],$companydet['contact_person'],$companydet['full_address'],$companydet['mobile_feedback'],$companydet['mobile'],$companydet['landline'],$companydet['website'],$companydet['category'],$companydet['timings'],$genio_url,$link_bus,$link_cat,$link_hr,$div),$firstfile);       
		 
		//if($_SERVER['REMOTE_ADDR']=='172.29.87.120')
			//$email='ganeshrj2010@gmail.com';
		//$email_body=$this->mysql_real_escape_custom($firstfile) ;
		$email_body=$firstfile ;
		 
		if($email!=''){
			$ecsflag=0;
			$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
			$res = parent::execQuery($checkforecs, $this->conn_finance); 
			if($res && mysql_num_rows($res) > 0)
			{
				$ecsflag=1;
			}
			$fin_details=$this->isEligible(1);
			
			$filenarr=$this->getFiles($fin_details,$ecsflag);

			$filenames_title_sms=$filenarr['filenames_title_sms'];
			$filenames_title_sms_new='';
			$filenames_title_sms=explode('|~|', $filenames_title_sms);
			
			$newsmsarr=array();
			foreach ($filenames_title_sms as $smskey => $smsvalue) {
				
			$smsvalue=explode('~',  $smsvalue);
			
			$filenamesforsms=explode(",", $smsvalue[0]);
			foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {
				
					if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){
						
						$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
						$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
						unset($newsmsarr[$smkey]);

					}
					else{
						$smsvalue[1];
						$newsmsarr[$smsvalue[1]]=trim($filenamesforsmsvalue);
					}

				}
			}
			
			$filenames_title_sms=$newsmsarr; 
			foreach ($filenames_title_sms as $ffkey => $ffvalue) {
				$filenames_title_sms_new.= "".$ffkey." http://genio.in/tc/".$ffvalue;
			}
			$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
			$filenames_title_sms_new=ltrim($filenames_title_sms_new," "); 
			 $sms_text="Justdial thanks you for accepting the Terms of Services ".$filenames_title_sms_new;
			 $sms_text=trim($sms_text);


			$mobile=$this->getMobile(1);
			if($mobile!=''){
					//~ $emailsend=$smsObj->sendSMSInvoice($mobile, $this->mysql_real_escape_custom($sms_text), 'Approval_TC',$this->parentid);
					$logsql = "update tbl_agreement_approval set 
											sent_date='".date('Y-m-d H:i:s')."',
											cron=1
											where parentid='".$this->parentid."' and version='".$this->version."'";
					$logres = parent::execQuery($logsql, $this->conn_finance);
			}

			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'No Email Id but sms sent'; 
			$result_msg_arr['data']['html'] = $email_body;
			echo json_encode($result_msg_arr); 
			return;
		}
		//$email='ganeshrj2010@gmail.com'; 
		/*	$emailsend=$smsObj->sendInvoiceMails($email, $from_email, 'Thank you for your registration with Just Dial Services.', $email_body,'ME',$this->parentid); 
		if($emailsend){
			 		  $logsql = "update tbl_agreement_mails set
				 					mailto='".$email."',
				 					editlisting_mail_status='1',
				 					editlisting_mail_time='".date('Y-m-d H:i:s')."',
				 					editlisting_delivery_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'";
			$logres = parent::execQuery($logsql, $this->conn_finance);
			if($this->email_appr!=''){
				 		  $logsql = "update tbl_agreement_mails set
					 					mail_status='1',
					 					mail_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'";
				$logres = parent::execQuery($logsql, $this->conn_finance);
				$invoiceins="insert into tbl_invoice_send_details (parentid,version,send_to) values ('".$this->parentid."','".$this->version."','".$email."')";
				$insforinv = parent::execQuery($invoiceins, $this->conn_finance);
				$path="/var/www/production/me_live_remotecity/logs/invoice/".date('Y')."/".date('m')."/".date('d')."/";
				$qryInsertInvcoicePdfLog = "update tbl_invoice_proposal_details set pdf_generated=0,path='".$path."' where parentid='".$this->parentid."' and version='".$this->version."'"; 
				$pdfins = parent::execQuery($qryInsertInvcoicePdfLog, $this->conn_finance);

			}
			$logsql = "update tbl_agreement_approval set 
				 					sent_date='".date('Y-m-d H:i:s')."',
				 					cron=1
				 					where parentid='".$this->parentid."' and version='".$this->version."'";
			$logres = parent::execQuery($logsql, $this->conn_finance);
		}
		else{
			 		 
			 		  $logsql = "update tbl_agreement_mails set 
				 					
				 					mailto='".$email."',
				 					editlisting_mail_status='0',
				 					editlisting_mail_time='".date('Y-m-d H:i:s')."',
				 					editlisting_delivery_time='".date('Y-m-d H:i:s')."'
				 					where parentid='".$this->parentid."' and version='".$this->version."'";
			$logres = parent::execQuery($logsql, $this->conn_finance); 
			 


		} */

		if($email){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			$result_msg_arr['data']['html'] = $email_body;
			echo json_encode($result_msg_arr); 
			return;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); 
			return;
		} 
	 
	}
	
	function sendDealCloseMailsThruProcess(){
		global $db;
		$firstfile='';
		$firstfile=file_get_contents("mailer_files/1.html");  
		$companyDet=$this->getContactDetails(0);				
		$companyname = $companyDet['contact_person'];
		$from_email=$this->getEmailid();
		$ecsflag=0;
		$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
		$res = parent::execQuery($checkforecs, $this->conn_finance); 
		if($res && mysql_num_rows($res) > 0)
		{
			$ecsflag=1;
		}
		$fin_details=$this->isEligible(0);

		$filenarr=$this->getFiles($fin_details,$ecsflag);

		$filenames=$filenarr['filenames'];
		$file_title=$filenarr['filenames_title'];
		$filenames_title_sms=$filenarr['filenames_title_sms'];
		$file_title=explode(',', $file_title);
		$filenames=strtoupper($filenames);
		$filenames=str_replace(".PDF",'.html', $filenames);

		$filenames=explode(',', $filenames);
		$newlink='';
		$tos='';
		/* .*/
		$no=1;
		$sms_text='';
		foreach ($filenames as $hkey => $hvalue) {
			$newlink='http://genio.in/omni_agreement/'.$hvalue;
			

			if(trim($file_title[$hkey])=='Terms Of Service' || strtolower(trim($file_title[$hkey]))=='terms of service'){
				$file_title[$hkey]='';
			
			}
			$link='<a href="'.$newlink.'" style="text-decoration:none;color:#1274c0;">Terms Of Service '.$file_title[$hkey].'</a><br>';
			//~ $tos.="&nbsp;&nbsp;<br>".$link;
			
			  
			$no++;
			
		}
		$newlink= '';
		$newlink = "http://genio.in/tc/terms-of-service.html";		
		$tclink = '<a href="'.$newlink.'" style="text-decoration:none;color:#1274c0;">Terms Of Service</a><br>';
		$tos.="&nbsp;&nbsp;<br>".$tclink;		
		
		$filenames_title_sms_new='';
		$filenames_title_sms=explode('|~|', $filenames_title_sms);
		
		$newsmsarr=array();
		foreach ($filenames_title_sms as $smskey => $smsvalue) {
			
		$smsvalue=explode('~',  $smsvalue);
		
		$filenamesforsms=explode(",", $smsvalue[0]);
		foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {
			
				if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){
					
					$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
					$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
					unset($newsmsarr[$smkey]);

				}
				else{
					$smsvalue[1];
					$newsmsarr[$smsvalue[1]].=",".trim($filenamesforsmsvalue);
					$newsmsarr[$smsvalue[1]]=ltrim($newsmsarr[$smsvalue[1]],","); 
				}

			}
		}
		

		$filenames_title_sms=$newsmsarr; 
		

		foreach ($filenames_title_sms as $ffkey => $ffvalue) {
			$ffvalue=explode(',', $ffvalue);
			$som='';
			foreach ($ffvalue as $smkey => $smvalue) {
			
			$som.="\n"." http://genio.in/tc/".$smvalue;
			$som=ltrim($som,"\n"); 
			}

			$filenames_title_sms_new.= "\n".$ffkey."\n".$som; 

		}
		$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
		$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
		$newlink=ltrim($newlink,","); 
		
		$docid=$this->getDocid();
		$this->docid=$docid;
		$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$docid;
		if(!$from_email)
		$from_email='noreply@justdial.com';

		
		require_once('class_send_sms_email.php');
		$smsObj	 = new email_sms_send($db,$this->data_city_cm);		
		$src=$this->parentid."_".$this->version;

		$citycode=0;
		switch($this->data_city_cm){
			case 'ahmedabad':$citycode=0;
			break;
						case 'ahmedabad':$citycode=56;
			break;
						case 'bangalore':$citycode=26;
			break;
						case 'chennai':$citycode=32;
			break;
						case 'delhi':$citycode=8;
			break;
						case 'hyderabad':$citycode=50;
			break;
						case 'kolkata':$citycode=16;
			break;
						case 'mumbai':$citycode=0;
			break;
			case 			'pune':$citycode=40;
			break;
		}
		if(trim($companyname)=='')
		{
			$companyname='Customer';
		}
		$email=$this->getEmail(0);
		if($this->data_city_cm!='remote')// change to != for testing
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
		else
		$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';

		/* <a href="#" style="text-decoration:none;color:#1274c0;">Terms of Service</a>.*/
		
		$firstfile=str_replace(array("NAME_CONSTANT","MAILINGCONT","CUSTEMAIL","SOURCE","CITYCODE","EDITLISTING","FORTRACKING","TERMSOFSERVICE","EMAIL"),array($companyname, $from_email, $email, $this->parentid."_approval_mail",0,$link2,$div,$tos,$email),$firstfile);      
		
		$email_body=$this->mysql_real_escape_custom($firstfile) ;		
		
		if($firstfile!=''){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			$result_msg_arr['error']['mobile'] = $mobile;
			$result_msg_arr['error']['smsSent'] = $emailsend;

			$result_msg_arr['data']['msg'] = $firstfile;
			echo json_encode($result_msg_arr); 
			exit;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); 
			exit;
		} 
		
	}
	
	function sendDealCloseMails(){
		global $db;
		$firstfile='';
		$firstfile=file_get_contents("mailer_files/1.html");  

		$companyname=$this->getContactDetails(0);
		
		$from_email=$this->getEmailid();
		$ecsflag=0;
		$checkforecs="SELECT * FROM db_finance.payment_otherdetails where parentid='".$this->parentid."' and version='".$this->version."' and  ecsflag>0";
		$res = parent::execQuery($checkforecs, $this->conn_finance); 
		if($res && mysql_num_rows($res) > 0)
		{
			$ecsflag=1;
		}
		$fin_details=$this->isEligible(0);

		$filenarr=$this->getFiles($fin_details,$ecsflag);

		$filenames=$filenarr['filenames'];
		$file_title=$filenarr['filenames_title'];
		$filenames_title_sms=$filenarr['filenames_title_sms'];
		$file_title=explode(',', $file_title);
		$filenames=strtoupper($filenames);
		$filenames=str_replace(".PDF",'.html', $filenames);

		$filenames=explode(',', $filenames);
		$newlink='';
		$tos='';
		/* .*/
		$no=1;
		$sms_text='';
		$tclink= '';
		foreach ($filenames as $hkey => $hvalue) {
			$newlink='http://genio.in/omni_agreement/'.$hvalue;
			

			if(trim($file_title[$hkey])=='Terms Of Service' || strtolower(trim($file_title[$hkey]))=='terms of service'){
				$file_title[$hkey]='';
			
			}
			$link='<a href="'.$newlink.'" style="text-decoration:none;color:#1274c0;">Terms Of Service '.$file_title[$hkey].'</a><br>';
			//~ $tos.="&nbsp;&nbsp;<br>".$link;
			$no++;
			
		}
		
		$newlink= '';
		$newlink = "http://genio.in/tc/terms-of-service.html";		
		$tclink = '<a href="'.$newlink.'" style="text-decoration:none;color:#1274c0;">Terms Of Service </a><br>';
		$tos.="&nbsp;&nbsp;<br>".$tclink;
		$filenames_title_sms_new='';
		$filenames_title_sms=explode('|~|', $filenames_title_sms);
		
		$newsmsarr=array();
		foreach ($filenames_title_sms as $smskey => $smsvalue) {	
			$smsvalue=explode('~',  $smsvalue);
			
			$filenamesforsms=explode(",", $smsvalue[0]);
			foreach ($filenamesforsms as $$filenamesforsmskey => $filenamesforsmsvalue) {	
				if(in_array(trim($filenamesforsmsvalue), $newsmsarr)){					
					$smkey = array_search(trim($filenamesforsmsvalue),$newsmsarr);
					$newsmsarr[$smkey.",".$smsvalue[1]]=trim($filenamesforsmsvalue);
					unset($newsmsarr[$smkey]);

				}
				else{
					$smsvalue[1];
					$newsmsarr[$smsvalue[1]].=",".trim($filenamesforsmsvalue);
					$newsmsarr[$smsvalue[1]]=ltrim($newsmsarr[$smsvalue[1]],","); 
				}

			}
		}
		

		$filenames_title_sms=$newsmsarr; 
		

		foreach ($filenames_title_sms as $ffkey => $ffvalue) {
			$ffvalue=explode(',', $ffvalue);
			$som='';
			foreach ($ffvalue as $smkey => $smvalue) {
			
			$som.="\n"." http://genio.in/tc/".$smvalue;
			$som=ltrim($som,"\n"); 
			}

			$filenames_title_sms_new.= "\n".$ffkey."\n".$som; 

		}
		$filenames_title_sms_new=ltrim($filenames_title_sms_new,","); 
		$filenames_title_sms_new=ltrim($filenames_title_sms_new,"\n"); 
		
		$sms_text = '';
		$inboxDemo = "";
		if(isset($this->params['remote']) && $this->params['remote']==0){ //refer mail  Re: Customer Support Miscellaneous Sms. -- 22-may-2018
			if(strtolower($this->data_city) == 'mumbai' || strtolower($this->data_city) == 'delhi' || strtolower($this->data_city) == 'pune' || strtolower($this->data_city) == 'ahmedabad'){
				$inboxDemo = "https://youtu.be/b0b49xPKSIo"; //hindi
			}else{
				$inboxDemo = "https://youtu.be/BKYfSZjMts8"; //english
			}			
		}else{ //remote city
			$inboxDemo = "https://youtu.be/b0b49xPKSIo"; //hindi
		}
		

		$sms_text = "".$companyname['companyname'].",\n\n";
		$sms_text .= "IMPORTANT NOTICE\n";
		$sms_text .= "Receive & Manage Your Business Enquiries On Justdial App. It's fast with instant notifications & great analytical tools!\n\n";
		$sms_text .= "SMS Feedback will be discontinued shortly\n";		
		$sms_text .= "Click below to download the app. Then go to Feedback Inbox section on your app\n";
		$sms_text .= "https://jsdl.in/appqdl\n\n";
		$sms_text .= "click to Watch this 2min video to understand how to download & check Feedback\n";
		$sms_text .= "https://youtu.be/s2IEeq4CVQg\n\n";
		$sms_text .= "Terms of service\n";
		$sms_text .= "http://genio.in/tc/terms-of-service.html";		
		
		$newlink=ltrim($newlink,","); 
		
		$docid=$this->getDocid();
		$this->docid=$docid;
		$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$docid;
		if(!$from_email)
		$from_email='noreply@justdial.com';

			require_once('class_send_sms_email.php');
			$smsObj	 = new email_sms_send($db,$this->data_city_cm);
			$email=$this->getEmail(1);
			//$email='ganeshrj2010@gmail.com';
			
			$firstfile=str_replace('NAME_CONSTANT', $companyname['contact_person'], $firstfile) ;   
			$firstfile=str_replace('MAILINGCONT', $from_email, $firstfile) ;
			$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;
			$firstfile=str_replace('SOURCE', $this->module, $firstfile) ;
			$firstfile=str_replace('CITYCODE', 0, $firstfile) ; 
			$firstfile=str_replace('CUSTEMAIL', $email, $firstfile) ;
			$src=$this->parentid."_".$this->version;

			$citycode=0;
			switch($this->data_city_cm){
				case 'ahmedabad':$citycode=0;
				break;
				case 'ahmedabad':$citycode=56;
				break;
				case 'bangalore':$citycode=26;
				break;
				case 'chennai':$citycode=32;
				break;
				case 'delhi':$citycode=8;
				break;
				case 'hyderabad':$citycode=50;
				break;
				case 'kolkata':$citycode=16;
				break;
				case 'mumbai':$citycode=0;
				break;
				case 'pune':$citycode=40;
				break;
			}
			if(trim($companyname['contact_person'])==''){
				$companyname1='Customer';
			}else{
				$companyname1 = $companyname['contact_person'];
			}
			
			if($this->data_city_cm!='remote')// change to != for testing
			$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$citycode.'" style="width:1px;height:1px;" /></div>';
			else
			$div='<div style="width:1px;height:1px;"><img src="http://messaging.justdial.com/trac_web.php?e='.$email.'&s=dealclose_inv_'.$src.'&c='.$this->data_city.'" style="width:1px;height:1px;" /></div>';
			
			$firstfile=str_replace(array("NAME_CONSTANT","MAILINGCONT","CUSTEMAIL","SOURCE","CITYCODE","EDITLISTING","FORTRACKING","TERMSOFSERVICE","EMAIL"),array($companyname1, $from_email, $email, $this->parentid."_approval_mail",0,$link2,$div,$tos,$email),$firstfile);      
			
 		$email_body=$this->mysql_real_escape_custom($firstfile) ;
		$mobile=$this->getMobile(0);
		if(isset($this->params['source']) && $this->params['source']!=''){
			$source = $this->params['source'];
		}else{
			$source = 'dealClose-sms-tc';
		}		
		$comments = '';
		
		if($mobile == '')
		{
			$mobile = $this->mobile;
		}
		else
		{
			$mobile = $mobile;
		}
		
		if($mobile!=''){			
			$sms_sent=$smsObj->sendSMSInvoice($mobile, $sms_text, $source,$this->parentid);
			if($sms_sent){
				$comments = 'SMS Sent Suucessfully';
			}else{
				$comments = 'SMS Not Sent';
			}
		}else{
			$comments = 'No Mobile Number Found';
		}
		$this->insertLog($mobile,$sms_text,$sms_sent, $email_id,$email_subject, $email_text,$email_sent,$comments);		
		if($sms_sent){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			$result_msg_arr['data']['msg'] = $firstfile;
			echo json_encode($result_msg_arr); 
			exit;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); 
			exit;
		} 		 
	}
	
	function insertLog($mobile,$sms_text,$sms_sent, $email_id=null,$email_subject=null, $email_text=null,$email_sent=null,$comments=null,$pdf_links=null){
		$ucode = '';
		if(isset($this->params['usercode']))
			$ucode = $this->params['usercode'];
		else
			$ucode = $this->params['ucode'];
			
		$logQuery  = "INSERT INTO online_regis1.tbl_sms_email_logs
					 SET 
					 parentid  = '".$this->parentid."',
					 version   = '".$this->version."',
					 module    = '".$this->module."',
					 data_city = '".$this->data_city."',
					 updatedBy = '".$ucode."',
					 entry_date = '".date('Y-m-d H:i:s')."',
					 mobile     = '".$mobile."',					 
					 sms_text 	='".addslashes(stripslashes($sms_text))."',
					 sms_sent 	='".$sms_sent."',
					 email_id 	='".$email_id."',
					 email_subject 	='".$email_subject."',
					 email_text 	= '".addslashes(stripcslashes($email_text))."',
					 email_sent 	= '".$email_sent."',					
					 source			= '".$this->params['source']."',
					 comments			= '".addslashes(stripcslashes($comments))."',
					 pdf_links			= '".addslashes(stripcslashes($pdf_links))."'
					 ON DUPLICATE KEY UPDATE
					 module    = '".$this->module."',
					 data_city = '".$this->data_city."',
					 updatedBy = '".$ucode."',
					 entry_date = '".date('Y-m-d H:i:s')."',
					 mobile     = '".$mobile."',					 
					 sms_text 	='".addslashes(stripslashes($sms_text))."',
					 sms_sent 	='".$sms_sent."',
					 email_id 	='".$email_id."',
					 email_subject 	='".$email_subject."',
					 email_text 	= '".addslashes(stripcslashes($email_text))."',
					 email_sent 	= '".$email_sent."',					 
					 source			= '".$this->params['source']."',
					 comments			= '".addslashes(stripcslashes($comments))."',
					 pdf_links			= '".addslashes(stripcslashes($pdf_links))."'
					   ";								
					   //echo "<pre>logQuery:--".$logQuery;
		$resLog     = parent::execQuery($logQuery,$this->conn_idc);
	}
	
	function getInstrumentAmt($instrumentid){
			 $today=date('Y-m-d');
			
			$instrumentAmount=0;
			$instrumentType='';
			$sqlinsamt="select instrumentAmount,instrumentType from payment_instrument_summary where parentid='".$this->parentid."' and approvalStatus=1 and version='".$this->version."'"; 
			$resins = parent::execQuery($sqlinsamt, $this->conn_finance);
			if($resins && mysql_num_rows($resins) > 0){

					while($rowins = mysql_fetch_assoc($resins)){
							$instrumentAmount += $rowins['instrumentAmount']; 
							$instrumentType   =  $instrumentType.','.strtoupper($rowins['instrumentType']); 
				}
			 //$maincompanyname=$this->getMultiMaincompanyname($sp,$sc);
			//$instrumentAmount="".$transamt." has been adjusted from amount received in Main listing -".$maincompanyname;
			}
			
			$instrumentType = trim($instrumentType,",");
			
			//echo $instrumentType."---".$instrumentAmount;die;
			
			if($instrumentAmount==0){
				$sc='';
				$sp='';
				$sqlmul="SELECT * FROM payment_contract_multicity WHERE destparentid='".$this->parentid."' and version='".$this->version."'"; 
				$resins = parent::execQuery($sqlmul, $this->conn_finance);
				if($resins && mysql_num_rows($resins) > 0){

						while($rowins = mysql_fetch_assoc($resins)){
							$transamt=$rowins['transamount']; 
							$sc=$rowins['sourcecity']; 
							$sp=$rowins['sourceparentid']; 
					}
				 $maincompanyname=$this->getMultiMaincompanyname($sp,$sc);
				$instrumentAmount="".$transamt." has been adjusted from amount received in Main listing -".$maincompanyname;
				}
			}

			

			 $payment_data['instrumentAmount']=$instrumentAmount;
			 $payment_data['instrumentType']  =$instrumentType;
			 return $payment_data;
	}
	function getMultiMaincompanyname($sourceparentid,$sourcecity){
		global $db;
		$source_data_city 		= ((in_array(strtolower($sourcecity), $this->dataservers)) ? strtolower($sourcecity) : 'remote');
		$this->scdbConIro    		= $db[$source_data_city]['iro']['master'];
		
		$companyname = '';
		$rowemail = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $sourceparentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'companyname';
		$cat_params['page']			= 'omniAgreementClass';
		

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
			$rowemail 		=	$res_gen_info1['results']['data'][$sourceparentid];
			$companyname=$rowemail['companyname']; 
		}


		/*$sqlemail="select companyname from tbl_companymaster_generalinfo where parentid='".$sourceparentid."'";
			$resemail = parent::execQuery($sqlemail, $this->scdbConIro);
			if($resemail && mysql_num_rows($resemail) > 0){

					while($rowemail = mysql_fetch_assoc($resemail)){
						$companyname=$rowemail['companyname']; 
				}
		}*/
		
		return $companyname;
	}

	function getCompanyDetails($type=0){
		
		if($type==0){
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname";
				$rowemail = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sqlemail="select companyname from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$resemail = parent::execQuery($sqlemail, $this->conn_temp);
				if($resemail && mysql_num_rows($resemail) > 0){
					$rowemail = mysql_fetch_assoc($resemail);
				}
			}
			$companyname = $rowemail['companyname'];
		}
		elseif($type==1){
			$companyname=array();
			$inv_details=$this->getAdtype(); 
			
			
			$payment_data     = $this->getInstrumentAmt($this->instrumentid);
			$instrumentAmount = $payment_data['instrumentAmount'];
			$instrumentType   = $payment_data['instrumentType'];
			
			$annx=json_decode($inv_details['annexure_header_content'],1);
			$receipt_details=$inv_details['receipt_details'];
			$receipt_details=json_decode($receipt_details,1);
			
			//$totamt=$receipt_details['instrument_details']['instrument_total']['totinsamt'];
			$totamt=$instrumentAmount;
			
			$add_type=implode(',' ,$annx['campaign_name_arr']);

			$pay_mode=$annx['payment_mode'];
			
			$rowemail = array();
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['page'] 		= 'omniAgreementClass';
			$cat_params['fields']		= 'companyname,contact_person,full_address,mobile,mobile_feedback,landline,website,dialable_landline';

			$res_gen_info1		= 	array();
			$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
			
			
			if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
				$rowemail 		=	$res_gen_info1['results']['data'][$this->parentid];

				$sqlurl=" select concat(ifnull(url_cityid,''),ifnull(shorturl,'')) as shorturl from db_iro.tbl_id_generator where parentid='".$this->parentid."' and  shorturl is not null and url_cityid is not null"; 
				$resurl = parent::execQuery($sqlurl, $this->dbConIro);

				if($resurl && mysql_num_rows($resurl) > 0){
					while($rowurl = mysql_fetch_assoc($resurl)){
						$this->shorturl=$rowurl['shorturl'];
					}
				}

				$companyname['companyname']=$rowemail['companyname']; 
				$contact_person=$rowemail['contact_person'];
				$contact_person=preg_replace('/[\[{\(].*[\]}\)]/U' , '', $contact_person);

				if($contact_person!=''){
					 $contact_person=ucwords($contact_person);
					 $companyname['contact_person']=$contact_person;   
				}
				else{
					$companyname['contact_person']="";  
				}
				
				$companyname['full_address']=$rowemail['full_address']; 
				$companyname['mobile']=$rowemail['mobile']; 
				$companyname['mobile_feedback']=$rowemail['mobile_feedback']; 
				$companyname['landline']=$rowemail['dialable_landline']; 
				$companyname['website']=$rowemail['website']; 
			}


			/*$sqlemail="select companyname,contact_person,full_address,mobile,mobile_feedback,landline,website,dialable_landline from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			$resemail = parent::execQuery($sqlemail, $this->dbConIro);
			$this->shorturl='';
			if($resemail && mysql_num_rows($resemail) > 0){
				while($rowemail = mysql_fetch_assoc($resemail)){
						$sqlurl=" select concat(ifnull(url_cityid,''),ifnull(shorturl,'')) as shorturl from db_iro.tbl_id_generator where parentid='".$this->parentid."' and  shorturl is not null and url_cityid is not null"; 
						$resurl = parent::execQuery($sqlurl, $this->dbConIro);
						if($resurl && mysql_num_rows($resurl) > 0){
						while($rowurl = mysql_fetch_assoc($resurl)){
							$this->shorturl=$rowurl['shorturl'];
								}
						}

						$companyname['companyname']=$rowemail['companyname']; 

						//$companyname['contact_person']=$rowemail['contact_person']==''?'Customer':ucwords($companyname['contact_person']);  
						$contact_person=$rowemail['contact_person'];
						$contact_person=preg_replace('/[\[{\(].*[\]}\)]/U' , '', $contact_person);

						if($contact_person!=''){

							//$contact_person=strtolower($contact_person);
							
							 $contact_person=ucwords($contact_person);
							 $companyname['contact_person']=$contact_person;   
							
 						}
						else
							$companyname['contact_person']="";  
						
						$companyname['full_address']=$rowemail['full_address']; 
						$companyname['mobile']=$rowemail['mobile']; 
						$companyname['mobile_feedback']=$rowemail['mobile_feedback']; 
						$companyname['landline']=$rowemail['dialable_landline']; 
						$companyname['website']=$rowemail['website']; 
				}
			}*/
			$rowemail = array();
			$cat_params['table'] 		= 'extra_det_id';
			$cat_params['page'] 		= 'omniAgreementClass';
			$cat_params['fields']		= 'catidlineage,working_time_start,working_time_end';
			$res_comp_extra1		= 	array();
			$res_comp_extra1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
			$companyname['catidlineage']='';

			if(!empty($res_comp_extra1) && $res_comp_extra1['errors']['code']==0){

				$rowemail 		=	$res_comp_extra1['results']['data'][$this->parentid];
				$companyname['catidlineage']=$rowemail['catidlineage']; 
				$companyname['working_time_start']=$rowemail['working_time_start']; 
				$companyname['working_time_end']=$rowemail['working_time_end']; 

				$companyname['catidlineage']=str_replace('/', '', $companyname['catidlineage']);
				$companyname['catidlineage']=explode(",", $companyname['catidlineage']);
				$companyname['catidlineage']=array_filter($companyname['catidlineage']);
				$companyname['catidlineage']=array_unique($companyname['catidlineage']); 
				$companyname['catidlineage']=implode(",", $companyname['catidlineage']);
				$catids=$companyname['catidlineage'];
				//$sqlcat="select group_concat(category_name) as cat_names from d_jds.tbl_categorymaster_generalinfo where catid in ($catids)";
				//$rescat = parent::execQuery($sqlcat, $this->dbConIro);
				$cat_params = array();
				$cat_params['page'] ='omniAgreementClass';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'category_name';

				$where_arr  	=	array();			
				$where_arr['catid']			= $catids;
				$cat_params['where']		= json_encode($where_arr);
				if($catids!=''){
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				$category_names='';
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0){
					foreach($cat_res_arr['results'] as $key=>$rowcat){
						
						if($category_names==''){
							$category_names=$rowcat['category_name'];
						}else{
							$category_names = $category_names.",".$rowcat['category_name'];
						}
						
					}

				}

				$working_time_start=array_filter(explode(',',$companyname['working_time_start']));
				$working_time_end=array_filter(explode(',',$companyname['working_time_end']));
				$tot_time='';
				$working_time_morning='';
				$working_time_eve='';
				$weekdays=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
				foreach ($working_time_start as $keytime => $valuetime) {
					$mortimings='';
					$tot_time.='<tr><td width="50%" style="color: #080401;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px 15px;line-height:20px;background:#EBEBEB">';
					$tot_time.=$weekdays[$keytime]."</td>";
					$valuetime=explode('-', $valuetime);
					$evevaluetime=$working_time_end[$keytime];
					$evevaluetime=explode('-', $evevaluetime);
					
					if( trim($valuetime[0])=='Closed' )
						$mortimings=$valuetime[0]; 
					else
					 $mortimings=$valuetime[0]." To ".$evevaluetime[0];
					

					$evetimings='';
					if($valuetime[1]!='' && $valuetime[1]!='Closed')
						$evetimings=$valuetime[1]." To ".$evevaluetime[1]; 
					else if( trim($valuetime[1])=='Closed')
						$evetimings=$valuetime[1]; 


					$tot_time.='<td width="50%" style="color: #080401;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px 15px;line-height:20px;background:#EBEBEB">';
					if($evetimings!='')
						$tot_time.=$mortimings." and ".$evetimings;
					else
						$tot_time.=$mortimings;
					 $tot_time.="</td></tr>";
				}
			}

			/*$companyname['catidlineage']='';
			$sqlemail="select catidlineage,working_time_start,working_time_end from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
			$resemail = parent::execQuery($sqlemail, $this->dbConIro);

			if($resemail && mysql_num_rows($resemail) > 0){
				while($rowemail = mysql_fetch_assoc($resemail)){
					$companyname['catidlineage']=$rowemail['catidlineage']; 
					$companyname['working_time_start']=$rowemail['working_time_start']; 
					$companyname['working_time_end']=$rowemail['working_time_end']; 
				}
				$companyname['catidlineage']=str_replace('/', '', $companyname['catidlineage']);
				$companyname['catidlineage']=explode(",", $companyname['catidlineage']);
				$companyname['catidlineage']=array_filter($companyname['catidlineage']);
				$companyname['catidlineage']=array_unique($companyname['catidlineage']); 
				$companyname['catidlineage']=implode(",", $companyname['catidlineage']);
				$catids=$companyname['catidlineage'];
				//$sqlcat="select group_concat(category_name) as cat_names from d_jds.tbl_categorymaster_generalinfo where catid in ($catids)";
				//$rescat = parent::execQuery($sqlcat, $this->dbConIro);
				$cat_params = array();
				$cat_params['page'] ='omniAgreementClass';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'category_name';

				$where_arr  	=	array();			
				$where_arr['catid']			= $catids;
				$cat_params['where']		= json_encode($where_arr);
				if($catids!=''){
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				$category_names='';
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0){
					foreach($cat_res_arr['results'] as $key=>$rowcat){
						
						if($category_names==''){
							$category_names=$rowcat['category_name'];
						}else{
							$category_names = $category_names.",".$rowcat['category_name'];
						}
						
					}

				}

				$working_time_start=array_filter(explode(',',$companyname['working_time_start']));
				$working_time_end=array_filter(explode(',',$companyname['working_time_end']));
				$tot_time='';
				$working_time_morning='';
				$working_time_eve='';
				$weekdays=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
				foreach ($working_time_start as $keytime => $valuetime) {
					$mortimings='';
					$tot_time.='<tr><td width="50%" style="color: #080401;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px 15px;line-height:20px;background:#EBEBEB">';
					$tot_time.=$weekdays[$keytime]."</td>";
					$valuetime=explode('-', $valuetime);
					$evevaluetime=$working_time_end[$keytime];
					$evevaluetime=explode('-', $evevaluetime);
					
					if( trim($valuetime[0])=='Closed' )
						$mortimings=$valuetime[0]; 
					else
					 $mortimings=$valuetime[0]." To ".$evevaluetime[0];
					

					$evetimings='';
					if($valuetime[1]!='' && $valuetime[1]!='Closed')
						$evetimings=$valuetime[1]." To ".$evevaluetime[1]; 
					else if( trim($valuetime[1])=='Closed')
						$evetimings=$valuetime[1]; 


					$tot_time.='<td width="50%" style="color: #080401;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px 15px;line-height:20px;background:#EBEBEB">';
					if($evetimings!='')
						$tot_time.=$mortimings." and ".$evetimings;
					else
						$tot_time.=$mortimings;
					 $tot_time.="</td></tr>";
				}
			}*/

			if($pay_mode=='NON-ECS'){
				$pay_mode='Upfront ('.$instrumentType.')';
			}

			$paymode_html='<tr>
				<td width="50%" style="color: #293139;font-size: 13px;font-weight:500;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px;line-height:20px;">
				Payment Mode
				</td>
				<td width="50%" style="color: #626262;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:0 10px;line-height:20px;">
				'.$pay_mode.'
				</td>
				</tr>
				<tr>
					<td width="50%" style="color: #293139;font-size: 13px;font-weight:500;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px;line-height:20px;">
				Amount Paid 
					</td>
					<td width="50%" style="color: #626262;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:0 10px;line-height:20px;">
				Rs.'.$totamt.'/-
				</td> 
				</tr>
				';
			if($pay_mode=='ECS'){ 
				$pay_mode='Ecs'; 
				$ecs_price='';
				$sqlecs="select * from db_ecs.ecs_mandate where parentid='".$this->parentid."' AND version='".$this->version."'";
				$res_ecs = parent::execQuery($sqlecs, $this->conn_finance);
				if($res_ecs && mysql_num_rows($res_ecs) > 0){
					while($row_ecs = mysql_fetch_assoc($res_ecs)){
						$ecs_price=$row_ecs['mandate_worth'];
					}	
				}
				else{

					$sqlsi="select * from db_si.si_mandate where parentid='".$this->parentid."' AND version='".$this->version."'";
					$res_ecs = parent::execQuery($sqlsi, $this->conn_finance);
					if($res_ecs && mysql_num_rows($res_ecs) > 0){
						while($row_ecs = mysql_fetch_assoc($res_ecs)){
							$ecs_price=$row_ecs['mandate_worth'];
						}	
					}

				}
				$pay_mode = $instrumentType;
				$paymode_html='
				 <tr>
				<td width="50%" style="color: #293139;font-size: 13px;font-weight:500;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px;line-height:20px;">
				Payment Mode
				</td>
				<td width="50%" style="color: #626262;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:0 10px;line-height:20px;">
				'.$pay_mode.'
				</td>
				</tr>				 
				 <tr>
					<td width="50%" style="color: #293139;font-size: 13px;font-weight:500;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px;line-height:20px;">
					Monthly ECS Amount
					</td>
					<td width="50%" style="color: #626262;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:0 10px;line-height:20px;">
					Rs.'.number_format($ecs_price,2).'/-
					</td>
				</tr>		
				<tr>
					<td width="50%" style="color: #293139;font-size: 13px;font-weight:500;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px;line-height:20px;">
				Amount Paid 
					</td>
					<td width="50%" style="color: #626262;font-size: 13px;font-weight:300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:0 10px;line-height:20px;">
				Rs.'.$totamt.'/-
				</td> 
				</tr>
				<tr>
					<td width="100%" style="color: #626262;font-size: 14px;font-weight: 300;-webkit-text-size-adjust: none;font-family: Roboto, Helvetica, Arial, sans-serif;text-align:left;padding:5px 20px 15px;line-height:20px;" colspan=2>						
						Please note, the first ECS of '.number_format($ecs_price,2).' will hit as soon as mandated get processed from your Bank account. Subsequently, all other ECS will hit your accounts as per the debit/selective ECS date. The advance amount will be adjusted with your tenure. 
					</td>
				</tr>
				';
			}

			$category_names = strip_tags($category_names);

			if (strlen($category_names) > 500) {

			    $stringCut = substr($category_names, 0, 100);
			    //$link2="http://www.justdial.com/os_index.php?city=".$this->data_city."&docid=".$this->docid;
			    $link_cat=$this->editlist."EC-".$this->shorturl."/key/em";
	    	    $category_names = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'.'<a href="'.$link_cat.'" target="_BLANK">View More</a>';  
			}
			
			$companyname['category']=$category_names;
			$companyname['add_type']=$add_type;
			$companyname['pay_mode']=$paymode_html; 
			$companyname['timings']=$tot_time; 
		}
		
		 
		return $companyname; 
	}

	function getAdtype(){
		
		$params=$this->params;
		$params['action']=2;
		$params['module']='me';
		require_once('includes/log_generate_invoice_content_class.php');
		require_once('includes/contractdetailsclass.php');
		
		$log_generate_invoice = new log_generate_invoice_content_class($params);
		
	  	$invresult = $log_generate_invoice -> get_invoice_content();

	  	if(!is_array($invresult)){
	  		$params=$this->params;
			$params['module']='me';
	  		$params['action']=1;
	  		$log_generate_invoice = new log_generate_invoice_content_class($params);
	  		$params_contract_details = $params;
			$params_contract_details['action'] ='GetPlatDiamCategories';
	  		$contractdetailsclassobj = new contractdetailsclass($params_contract_details);
	  		$result = $log_generate_invoice -> log_invoice_content($contractdetailsclassobj);
	  		$invresult = $log_generate_invoice -> get_invoice_content();
	  	}
		
		return $invresult;
	}
	
	function getContactDetails($type=0){
		
		if($type==0){
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "contact_person,companyname";
				$rowemail = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sqlemail="select contact_person,companyname from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$resemail = parent::execQuery($sqlemail, $this->conn_temp_new);
				if($resemail && mysql_num_rows($resemail) > 0){
					$rowemail = mysql_fetch_assoc($resemail);
				}
			}
			$companyname=$rowemail;
		}
		elseif($type==1){

			$rowemail = array();
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= $this->parentid;
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'contact_person,companyname';
			$cat_params['page']			= 'omniAgreementClass';

			$res_gen_info1		= 	array();
			$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

			if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
				$rowemail 		=	$res_gen_info1['results']['data'][$this->parentid];
				$companyname 	= 	$rowemail;
			}
		

			/*$sqlemail="select contact_person,companyname from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			$resemail = parent::execQuery($sqlemail, $this->dbConIro);
			if($resemail && mysql_num_rows($resemail) > 0){
					while($rowemail = mysql_fetch_assoc($resemail)){
						$companyname=$rowemail; 
				}
			}*/
		}
		 
		return $companyname; 
	}

	function getEmailid(){
		$email='';
		$sql="select op_email from d_jds.tbl_city_mapped_emails where city_name='".$this->data_city."'";
		$query = parent::execQuery($sql, $this->dbConIro);
		if($query && mysql_num_rows($query) > 0){
			while($row = mysql_fetch_assoc($query)){
				$email=$row['op_email'];
			}
			return $email;
		}
		else
			return false;
	}

	function cronForSendingMails(){
		$sqlres="select * from tbl_agreement_approval where dealclose_date  <= now() - INTERVAL 3 DAY and (sent_date is null or sent_date='')";
		$query_res = parent::execQuery($sqlres, $this->conn_finance);
		if($query_res && mysql_num_rows($query_res) > 0){
			while($row = mysql_fetch_assoc($query_res)){
				$this->version=$row['version'];
				$this->parentid=$row['parentid'];
				$this->sendApprovalMails();
			}
		}
	}

	function dumpDataOnApproval(){
	 	$email=$this->getEmail(1);
		
	  	$sql_res = "INSERT INTO tbl_onboarding_invoice_details set
				 		 					parentid='".$this->parentid."',
				 		 					version='".$this->version."',
				 		 					instrument_id='".$this->instrumentid."',
				 		 					sent_to='".$this->mysql_real_escape_custom($email)."',
				 		 					inserted_date='".date('Y-m-d H:i:s')."',
				 		 					cron=0
				 		 					ON DUPLICATE KEY UPDATE
				 		 					inserted_date='".date('Y-m-d H:i:s')."',
				 		 					sent_to='".$this->mysql_real_escape_custom($email)."',
				 		 					cron=0";  
		 $resins = parent::execQuery($sql_res, $this->conn_finance); 
		 if($resins){
		 	$result_msg_arr['error']['code'] = 0;
		 	$result_msg_arr['error']['msg'] = 'Success';
		 	echo json_encode($result_msg_arr); 
		 	exit;
		}	
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); 
			exit;
		}
	}
}	
?>
