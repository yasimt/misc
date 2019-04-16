<?php
class omniDetailsClass extends DB
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
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	var $dealcloseflow= null;
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
		else
			$this->parentid  = $this->params['parentid']; 
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 
		
		
		if(trim($this->params['version']) == "" && $this->action!='10' && $this->action!='1')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "version Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->version  = $this->params['version']; 
			
			
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 

		if(trim($this->params['usercode']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Usercode Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->usercode  = $this->params['usercode']; 
		if(trim($this->params['username']) != "")
		{
			$this->username  = urldecode($this->params['username']); 
		}
		if(trim($this->params['set_domain']) != "")
		{
			$this->set_domain  = urldecode($this->params['set_domain']); 
		}
		else
			$this->set_domain=0;
			
		if(trim($this->params['website_demo']) != "")
		{
			$this->website_demo  = urldecode($this->params['website_demo']); 
		}
		if(trim($this->params['demo_temp_type']) != "")
		{
			$this->demo_temp_type  = urldecode($this->params['demo_temp_type']); 
		}
		else
			$this->demo_temp_type  ='';
		if($this->params['callFrom'] != "")
		{
			$this->callFrom  = urldecode($this->params['callFrom']); 
		}
		else
			$this->callFrom  ='';
		



		if(trim($this->params['action']) == 2)
		{
			if(trim($this->params['website1'])==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Primary Website Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
			$this->website1  = $this->params['website1']; 
			if(trim($this->params['own_website'])==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Website Ownership Details Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
			$this->own_website  = $this->params['own_website']; 
			if(trim($this->params['payment_type'])==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Payment Details Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
			$this->payment_type  = $this->params['payment_type']; 


		}
		
		if(trim($this->params['website2']) != "")
		{
			$this->website2  = $this->params['website2']; 
		}
		if(trim($this->params['website3']) != "")
		{
			$this->website3  = $this->params['website3']; 
		}
		if(trim($this->params['website4']) != "")
		{
			$this->website4  = $this->params['website4']; 
		}
		if(trim($this->params['template_id']) != "")
		{
			$this->template_id  = $this->params['template_id']; 
		}
		
		if(trim($this->params['mobile']) != "")
		{
			$this->mobile  = $this->params['mobile']; 
		}
		if(trim($this->params['docid']) != "")
		{
			$this->docid  = $this->params['docid']; 
		} 
		if(trim($this->params['email']) != "")
		{
			$this->email  = urldecode($this->params['email']); 
		}
		if(trim($this->params['cs_website']) != "")
		{
			$this->cs_website  = $this->params['cs_website']; 
		}
		if(trim($this->params['national_catid']) != "")
		{
			$this->national_catid  = $this->params['national_catid']; 
		}
		else{
			$this->national_catid  ='';
		}
		if(trim($this->params['custom_website']) != "")
		{
			$this->custom_website  = $this->params['custom_website']; 
		}
		else{
			$this->custom_website  ='';
		}
		if(trim($this->params['supplierid']) != "")
		{
			$this->supplierid  = $this->params['supplierid']; 
		}
		else{
			$this->supplierid  ='';
		}

		if(trim($this->params['app_template_name']) != "")
		{
			$this->app_template_name  = $this->params['app_template_name']; 
		}
		else{
			$this->app_template_name  ='';
		}

		if(trim($this->params['app_template_id']) != "")
		{
			$this->app_template_id  = $this->params['app_template_id']; 
		}
		else{
			$this->app_template_id  =''; 
		}

		if(trim($this->params['domainmapping_website']) != "")
		{
			$this->domainmapping_website  = $this->params['domainmapping_website']; 
		}
		else{
			$this->domainmapping_website  =''; 
		}

		
		if(trim($this->params['domain_registername']) != ""){
			$this->domain_registername  = $this->params['domain_registername']; 
		}else{
			$this->domain_registername  =''; 
		}
		if(trim($this->params['year1']) != ""){
			$this->year1  = $this->params['year1']; 
		}else{
			$this->year1  =''; 
		}
		if(trim($this->params['year2']) != ""){
			$this->year2  = $this->params['year2']; 
		}else{
			$this->year2  =''; 
		}
		if(trim($this->params['price1']) != ""){
			$this->price1  = $this->params['price1']; 
		}else{
			$this->price1  =''; 
		}
		if(trim($this->params['price2']) != ""){
			$this->price2  = $this->params['price2']; 
		}else{
			$this->price2  =''; 
		}
		if(trim($this->params['domain_userid']) != ""){
			$this->domain_userid  = $this->params['domain_userid']; 
		}else{
			$this->domain_userid  =''; 
		}
		if(trim($this->params['domain_pass']) != ""){
			$this->domain_pass  = $this->params['domain_pass']; 
		}else{
			$this->domain_pass  =''; 
		}
		if(trim($this->params['domain_regiter_emailId']) != ""){
			$this->domain_regiter_emailId  = $this->params['domain_regiter_emailId']; 
		}else{
			$this->domain_regiter_emailId  =''; 
		}
		if(trim($this->params['domainReg_forget_link']) != ""){
			$this->domainReg_forget_link  = $this->params['domainReg_forget_link']; 
		}else{
			$this->domainReg_forget_link  =''; 
		}
		if(trim($this->params['action_flag_forget']) != ""){
			$this->action_flag_forget  = $this->params['action_flag_forget']; 
		}else{
			$this->action_flag_forget  =''; 
		}
		if(trim($this->params['action_flag_forgetstatus']) != ""){
			$this->action_flag_forgetstatus  = $this->params['action_flag_forgetstatus']; 
		}else{
			$this->action_flag_forgetstatus  =''; 
		}
		if(trim($this->params['omni_domain_option']) != ""){
			$this->omni_domain_option  = $this->params['omni_domain_option']; 
		}else{
			$this->omni_domain_option  =''; 
		}
		//,domain_regiter_emailId,domainReg_forget_link,action_flag_forget,action_flag_forgetstatus
		
		if($params['action']=='19' || $params['action']==19 ){ 
		if(trim($params['template_name'])=='' || $params['template_name']==null  || !isset($params['template_name'])) 
		{
			$result['results'] = array();
			$result['error']['code'] = 1;	
			$result['error']['msg'] = "Template Name  is Not Present";
			echo json_encode($result);	exit;
		}
		else
			$this->template_name = $params['template_name'];
		
		if(trim($params['template_type'])=='' || $params['template_type']==null || !isset($params['template_type']))
		{
			$result['results'] = array();  
			$result['error']['code'] = 1;	

			$result['error']['msg'] = "Template Type is  Not Present";
			echo json_encode($result);	exit;
		}
		else
			$this->template_type = $params['template_type'];

		if(trim($params['vertical_id'])=='' || $params['vertical_id']==null || !isset($params['vertical_id']))
		{
			$result['results'] = array();  
			$result['error']['code'] = 1;	

			$result['error']['msg'] = "Vertical Id is  Not Present";
			echo json_encode($result);	exit;
		}
		else
			$this->vertical_id = $params['vertical_id'];

		if(trim($params['vertical_name'])=='' || $params['vertical_name']==null || !isset($params['vertical_name']))
		{
			$result['results'] = array();  
			$result['error']['code'] = 1;	

			$result['error']['msg'] = "Vertical Name is  Not Present";
			echo json_encode($result);	exit;
		}
		else
			$this->vertical_name = $params['vertical_name'];

		if(trim($params['demo_url'])=='' || $params['demo_url']==null || !isset($params['demo_url']))
		{
			$result['results'] = array();  
			$result['error']['code'] = 1;	

			$result['error']['msg'] = "Demo Url is  Not Present";
			echo json_encode($result);	exit;
		}
		else
			$this->demo_url = $params['demo_url'];



		


		if(trim($params['omni_type'])=='' || $params['omni_type']==null || !isset($params['omni_type']))
		{
			$result['results'] = array();  
			$result['error']['code'] = 1;	

			$result['error']['msg'] = "Omni Type is  Not Present"; 
			echo json_encode($result);	exit;
		}
		else
			$this->omni_type = $params['omni_type']; 
		  
	}

		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$status=$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		
		$this -> genio_lite_campaign_info = null;

		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			//$this->tempmapomniUrl="http://sunnyshende.jdsoftware.com/web_services/omni_services/templateMapping.php";
			$this->tempmapomniUrl="http://gammatesting.jdseller.com/marketplace/static/php/web/service_api.php";
			$this->domainomniUrl="http://sunnyshende.jdsoftware.com/web_services/omni_services/domainMapping.php";
			$this->omniUrl="http://betatesting.jdseller.com/marketplace/static/php/web/common_api.php"; 
			$this->omniHrUrl="http://tejasnikam.jdsoftware.com/HROMNI/employee/createInstance";
			$this->childDoctorDetails="http://sunnyshende.jdsoftware.com/web_services/web_services/childDocidsInfo.php";
		}
		else{ 
			//$this->tempmapomniUrl="http://192.168.20.102:9001/omni_services/templateMapping.php";
			$this->tempmapomniUrl="http://192.168.20.48/marketplace/static/php/web/service_api.php";
			$this->domainomniUrl="http://192.168.20.102:9001/omni_services/domainMapping.php";	
			$this->omniUrl="http://192.168.20.48/marketplace/static/php/web/common_api.php";
			$this->omniHrUrl="http://192.168.20.82/employee/createInstance"; 
			$this->childDoctorDetails="http://192.168.20.102:9001/web_services/childDocidsInfo.php";
		}
		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->meurl="http://".GNO_URL;
		 $this->from_price=0;
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    	= $db[$data_city]['iro']['master'];
		
		$this->conn_log		= $db['db_log']; // pointing to 17.103
		
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
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
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
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
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
	function getBformWebsiteDetails(){
		if($this->mongo_flag==1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "website,mobile,email";
			$row_website = $this->mongo_obj->getData($mongo_inputs);	
		}
		else
		{
			$sql="select website,mobile,email from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$res = parent::execQuery($sql, $this->conn_temp_new);
			if($res && mysql_num_rows($res))
			{
				$row_website = mysql_fetch_assoc($res);
			}
		}
		
		if(count($row_website)>0)
		{
			$arr=array();
			$arr['website']=urldecode($row_website['website']);
			$arr['mobile']=urldecode($row_website['mobile']);
			$arr['email']=urldecode($row_website['email']);
			
			$arr['invoice_email']='';
			$arr['invoice_mobile']='';
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Website Details Found';
			$result_msg_arr['error']['result'] = $arr;
			return json_encode($result_msg_arr);
		}
		else
		{	
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Website Not Found";
			echo json_encode($result_msg_arr);exit;
		}		
	}

	function saveWebsiteDetails(){

			$website_str='';
			
			if($this->website1!=''){
				$website_str.=$this->website1;
			}
			if($this->website2!=''){
				$website_str.=",".$this->website2;
			}
			if($this->website3!=''){
				$website_str.=",".$this->website3;
			}
			if($this->website4!=''){
				$website_str.=",".$this->website4;
			}
			$domain_registername  =	$this->domain_registername;
			$domain_userid		  = $this->domain_userid;
			if($this->domain_pass!= '')
				$domain_pass		  = $this->domain_pass;
			else
				$domain_pass		  = '';

			$webDomain_yearprice		=	array();
			$webDomain_yearprice['year'][0]		=	$this->year1;
			$webDomain_yearprice['year'][1]		=	$this->year2;
			$webDomain_yearprice['price'][0]	=	$this->price1;
			$webDomain_yearprice['price'][1]	=	$this->price2;
			$webDomain_yearpricefinal	=	json_encode($webDomain_yearprice);
		$res_ins_website=false;

		if($website_str!=''){
				//,domain_regiter_emailId,domainReg_forget_link,action_flag_forget,action_flag_forgetstatus
$sql_ins_website = "INSERT INTO tbl_omni_website_details_temp set
									parentid='".$this->parentid."',
									website_requests='".$website_str."',
									added_by  	= '".$this->usercode."',
									added_time  	= '".date('Y-m-d H:i:s')."',
									website_own  	= '".$this->own_website."',
									payment_type  	= '".$this->payment_type."',
									domain_regiter_name	=	'".$domain_registername."',
									domain_userid	=	'".$domain_userid."',
									domain_pass	=	'".$domain_pass."',
									domain_regiter_emailId = '".$this->domain_regiter_emailId."',
									domainReg_forget_link = '".$this->domainReg_forget_link."',
									omni_domain_option = '".$this->omni_domain_option."'
			 					ON DUPLICATE KEY UPDATE
									website_requests='".$website_str."',
									added_by  	= '".$this->usercode."',
									added_time  	= '".date('Y-m-d H:i:s')."',
									payment_type  	= '".$this->payment_type."',
									website_own  	= '".$this->own_website."',
									domain_regiter_name	=	'".$domain_registername."',
									domain_userid	=	'".$domain_userid."',
									domain_pass	=	'".$domain_pass."',
									domain_regiter_emailId = '".$this->domain_regiter_emailId."',
									domainReg_forget_link = '".$this->domainReg_forget_link."',
									omni_domain_option = '".$this->omni_domain_option."'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_temp);

$sql_ins_websitelog = "INSERT INTO online_regis.tbl_omni_website_details_Log set
									parentid='".$this->parentid."',
									website_requests='".$website_str."',
									added_by  	= '".$this->usercode."',
									added_time  	= '".date('Y-m-d H:i:s')."',
									website_own  	= '".$this->own_website."',
									payment_type  	= '".$this->payment_type."',
									domain_regiter_name	=	'".$domain_registername."',
									domain_userid	=	'".$domain_userid."',
									domain_pass	=	'".$domain_pass."',
									domain_regiter_emailId = '".$this->domain_regiter_emailId."',
									domainReg_forget_link = '".$this->domainReg_forget_link."',
									status = '".$this->action_flag_forgetstatus."',
									action_flag = '".$this->action_flag_forget."',
									omni_domain_option = '".$this->omni_domain_option."'";
$res_ins_websitelog = parent::execQuery($sql_ins_websitelog, $this->conn_idc);
		}
		if($res_ins_website){
			if(trim($this->own_website=='0')){
				require_once('includes/domainClass.php');
				$websitearr=explode(',', $website_str);
				$price_arr=array();
				foreach ($websitearr as $key => $value) {
					$this->params['domainname']=$value;
					$domainClassobj = new domainClass($this->params);
					$result = $domainClassobj->getPrice();
					if($result!=0){
						array_push($price_arr, $result);
					}
				}
				$price=max($price_arr);
				$this->setsphinxid();
				$this->PopulateTempCampaign();

				$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=74,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));

			}
			else{
				$this->delTempCampaign();
			}
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Successful';
			return json_encode($result_msg_arr);
			
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error occurred!";
			echo json_encode($result_msg_arr);exit;
		}
	}
	function deleteWebsiteDetails(){
		 	$sql_ins_website = "delete from tbl_omni_website_details_temp where parentid='".$this->parentid."'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_temp);
			
			$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='74'";
			$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp);

			$this->delTempCampaign();

			if($res_ins_website){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = 'Successful';
				return json_encode($result_msg_arr);
			}
			else{
					$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error occurred!";
			echo json_encode($result_msg_arr);exit;
			}
	}
	function tempTomain($genio_lite_campaign = null){
		$dependend=false;
		$checkdept=$this->checkOmniDependent(0,2);
		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){
			$dependend=true;

		}

		$sqlcheck="select * from tbl_omni_website_details where parentid='".$this->parentid."' and version='".$this->version."'";
		$checkmain_res = parent::execQuery($sqlcheck, $this->conn_idc);
		if($checkmain_res && mysql_num_rows($checkmain_res)>0){
			$result_msg_arr['error']['code'] = 0;// as richie is going forward in jda
			$result_msg_arr['error']['msg'] = "Success";  
			$result_msg_arr['error']['msg_err'] = "Data Already Present for this version";   //as richie needs this.
			if(count($genio_lite_campaign)>0)
				return $result_msg_arr;
			else
			{
				echo json_encode($result_msg_arr);exit;
			}
		} 
	 	$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid='74' and recalculate_flag=1";
		$checktempres = parent::execQuery($checktemp, $this->conn_temp);
		if($checktempres && mysql_num_rows($checktempres)>0 || $dependend || (count($genio_lite_campaign)>0 && array_key_exists("74",$genio_lite_campaign)) ){
			$websiteDetails="select * from tbl_omni_website_details_temp where parentid='".$this->parentid."'";
			$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_temp);
			if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
	 		{
		 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
					{
				 		 $sql_ins_website = "INSERT INTO tbl_omni_website_details set
					 					parentid='".$websiteDetailsrow['parentid']."',
					 					website_requests='".$websiteDetailsrow['website_requests']."',
					 					added_by  	= '".$websiteDetailsrow['added_by']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					website_own  	= '".$websiteDetailsrow['website_own']."',
					 					version  	= '".$this->version."',
					 					payment_type  	= '".$websiteDetailsrow['payment_type']."',
					 					domain_regiter_name	=	'".$websiteDetailsrow['domain_regiter_name']."',
										domain_userid	=	'".$websiteDetailsrow['domain_userid']."',
										domain_pass	=	'".$websiteDetailsrow['domain_pass']."',
										domain_regiter_emailId = '".$websiteDetailsrow['domain_regiter_emailId']."',
										domainReg_forget_link = '".$websiteDetailsrow['domainReg_forget_link']."',
										omni_domain_option = '".$websiteDetailsrow['omni_domain_option']."'
					 					ON DUPLICATE KEY UPDATE
					 					website_requests='".$websiteDetailsrow['website_requests']."',
					 					added_by  	= '".$websiteDetailsrow['added_by']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					payment_type  	= '".$websiteDetailsrow['payment_type']."',
					 					version  	= '".$this->version."',
					 					website_own  	= '".$websiteDetailsrow['website_own']."',
					 					domain_regiter_name	=	'".$websiteDetailsrow['domain_regiter_name']."',
										domain_userid	=	'".$websiteDetailsrow['domain_userid']."',
										domain_pass	=	'".$websiteDetailsrow['domain_pass']."',
										domain_regiter_emailId = '".$websiteDetailsrow['domain_regiter_emailId']."',
										domainReg_forget_link = '".$websiteDetailsrow['domainReg_forget_link']."',
										omni_domain_option = '".$websiteDetailsrow['omni_domain_option']."'";
						$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
					
					$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					website_requests  	= '".$websiteDetailsrow['website_requests']."',
					 					website_request_date	  	= '".date('Y-m-d H:i:s')."',
					 					website_request_by  	= '".$websiteDetailsrow['added_by']."',
					 					dealclosed_date  	= '".date('Y-m-d H:i:s')."'
					 					ON DUPLICATE KEY UPDATE
					 					website_requests  	= '".$websiteDetailsrow['website_requests']."',
					 					website_request_date	  	= '".date('Y-m-d H:i:s')."',
					 					website_request_by  	= '".$websiteDetailsrow['added_by']."',
					 					dealclosed_date  	= '".date('Y-m-d H:i:s')."'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);

				}
			}
			if($res_ins_website){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else {
					echo json_encode($result_msg_arr);exit;
				}
					
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else {
					echo json_encode($result_msg_arr);exit;
				}
			}
		}
		else{
			$websiteDetails="select * from tbl_omni_website_details_temp where parentid='".$this->parentid."' and website_own=1";
				$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_temp);
				if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
		 		{
			 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
						{
				 		$sql_ins_website = "INSERT INTO tbl_omni_website_details set
					 					parentid='".$websiteDetailsrow['parentid']."',
					 					website_requests='".$websiteDetailsrow['website_requests']."',
					 					added_by  	= '".$websiteDetailsrow['added_by']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					website_own  	= '".$websiteDetailsrow['website_own']."',
					 					version  	= '".$this->version."',
					 					payment_type  	= '".$websiteDetailsrow['payment_type']."',
					 					domain_regiter_name	=	'".$websiteDetailsrow['domain_regiter_name']."',
										domain_userid	=	'".$websiteDetailsrow['domain_userid']."',
										domain_pass	=	'".$websiteDetailsrow['domain_pass']."',
										domain_regiter_emailId = '".$websiteDetailsrow['domain_regiter_emailId']."',
										domainReg_forget_link = '".$websiteDetailsrow['domainReg_forget_link']."',
										omni_domain_option = '".$websiteDetailsrow['omni_domain_option']."'
					 					ON DUPLICATE KEY UPDATE
					 					website_requests='".$websiteDetailsrow['website_requests']."',
					 					added_by  	= '".$websiteDetailsrow['added_by']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					payment_type  	= '".$websiteDetailsrow['payment_type']."',
					 					version  	= '".$this->version."',
					 					website_own  	= '".$websiteDetailsrow['website_own']."',
					 					domain_regiter_name	=	'".$websiteDetailsrow['domain_regiter_name']."',
										domain_userid	=	'".$websiteDetailsrow['domain_userid']."',
										domain_pass	=	'".$websiteDetailsrow['domain_pass']."',
										domain_regiter_emailId = '".$websiteDetailsrow['domain_regiter_emailId']."',
										domainReg_forget_link = '".$websiteDetailsrow['domainReg_forget_link']."',
										omni_domain_option = '".$websiteDetailsrow['omni_domain_option']."'";
						$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
						
						$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					website_requests  	= '".$websiteDetailsrow['website_requests']."',
					 					website_request_date	  	= '".date('Y-m-d H:i:s')."',
					 					website_request_by  	= '".$websiteDetailsrow['added_by']."',
					 					dealclosed_date  	= '".date('Y-m-d H:i:s')."'
					 					ON DUPLICATE KEY UPDATE
					 					website_requests  	= '".$websiteDetailsrow['website_requests']."',
					 					website_request_date	  	= '".date('Y-m-d H:i:s')."',
					 					website_request_by  	= '".$websiteDetailsrow['added_by']."',
					 					dealclosed_date  	= '".date('Y-m-d H:i:s')."'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc); 
					}
					if($res_ins_website){
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = "Success";
						//echo json_encode($result_msg_arr);exit;
					}
					else{
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg'] = "Error";
						//echo json_encode($result_msg_arr);exit;
					}
					
					if(count($genio_lite_campaign)>0)
						return $result_msg_arr;
					else
					{
						echo json_encode($result_msg_arr);exit;
					}
					
					
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Finance Details";
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else
				{
					echo json_encode($result_msg_arr);exit;
				}
			}
		}

	}
	function getWebsiteDetails(){

		$website_str='';
		
		$websiteDetails="select * from tbl_omni_website_details where parentid='".$this->parentid."'";
		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_temp);
			if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
	 		{
	 			while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
				{

					$website_str=$websiteDetailsrow['website_requests'];
				}
			}
		if($website_str!=''){
			$website_arr=explode(',', $website_str);
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Website Details Found";
			$result_msg_arr['result']['website'] = $website_arr;
			return json_encode($result_msg_arr);
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Website Details Found";
			echo json_encode($result_msg_arr);exit;
		}


	}

	function setsphinxid()
	{
			$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			$res = parent::execQuery($sql, $this->dbConIro);

			if($res && mysql_num_rows($res) )
			{
					$row= mysql_fetch_assoc($res);
					$this->sphinx_id = $row['sphinx_id'];
					$this->docid = $row['docid'];
			}else
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "sphinx_id not found in tbl_id_generator";
					echo json_encode($result_msg_arr);exit;

			}
	}
	function financeInsertUpdateTemp($campaignid,$camp_data) {

        if ($campaignid>0 && is_array($camp_data)) {

            $insert_str = '';
            foreach($camp_data as $column_key => $column_value) {

                $temp_str    = $column_key ."='".$column_value . "'";
                $insert_str .= (($insert_str=='') ? $temp_str : ','.$temp_str) ;
            }

                        $compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                            ". $insert_str.",
                                            sphinx_id   = '".$this->sphinx_id."',
                                            campaignid  = '".$campaignid."',
                                            parentid    = '" . $this->parentid . "'
                                            ON DUPLICATE KEY UPDATE
                                            " . $insert_str . "";//exit;
                        
            $res_compmaster_fin_temp_insert = parent::execQuery($compmaster_fin_temp_insert, $this->conn_finance_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_jdrr_budget :: '.$compmaster_fin_temp_insert;
				echo '<br>res :: '.$res_compmaster_fin_temp_insert;
			}
			
			return $res_compmaster_fin_temp_insert;

        }
	 
    }
    function PopulateTempCampaign(){
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='74',
							selected  	= 1
							ON DUPLICATE KEY UPDATE
							campaignid='74',
							selected  	= 1";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		/*if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
		$querydel="delete from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
		$ressql = parent::execQuery($querydel, $this->conn_temp);
		}*/
 		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
	 }
	 function delTempCampaign(){
	 	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='74',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='74',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "delete from tbl_companymaster_finance_temp where
								parentid='".$this->parentid."'and campaignid='74'";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_finance_temp);
		
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success"; 
		return $result_msg_arr;
	 }
	 function setTemplateInCons($templatedetails){
	 	$sql_ins_website = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
	 		 					parentid='".$this->parentid."',
	 		 					data_city='".$this->data_city_cm."',
	 		 					vertical_id='".$templatedetails['ts']['vid']."',
	 		 					template_type='".$templatedetails['ts']['ttyp']."',
	 		 					template_tag='".$templatedetails['ts']['tnm']."'
	 		 					ON DUPLICATE KEY UPDATE
	 		 					vertical_id='".$templatedetails['ts']['vid']."',
	 		 					template_type='".$templatedetails['ts']['ttyp']."',
	 		 					template_tag='".$templatedetails['ts']['tnm']."'";
	 	$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc); 
	 }

	 function checkOmniDependent($live=0,$type=2){
		 $conn=$this->conn_finance;
		if($live==1){
			$tbl="dependant_campaign_details";
			 $conn=$this->conn_finance;
		}
		else{
		$tbl="dependant_campaign_details_temp";
		 	$conn=$this->conn_temp;	
		}
		$sqlcheck="select * from $tbl where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sqlcheck, $conn);
		$cnt=0;
		if($res && mysql_num_rows($res)){
			$returnarr['msg']['dependent_present']=1;
			while($row = mysql_fetch_assoc($res)){
				$returnarr['data'][$cnt]['combo_type']=$row['combo_type'];
				$returnarr['data'][$cnt]['pri_campaignid']=$row['pri_campaignid'];
				$returnarr['data'][$cnt]['dep_campaignid']=$row['dep_campaignid'];
				$cnt++;
				
			}
			return $returnarr;
		}
		else{
			$returnarr['msg']['dependent_present']=0;
			return $returnarr;
		}
		

		
	}
	function insertIntoFailureLog($step,$response){
	 	$ins_log_sql = "INSERT INTO online_regis1.omni_store_creation_failure set
		 					parentid='".$this->parentid."',
		 					data_city   ='".$this->data_city_cm."',
		 					version  	= '".$this->version."',
		 					step  		= '".$step."',
		 					status_flag  	= '0',
		 					count_flag  		= '0',
		 					api_response  	= '".$this->mysql_real_escape_custom($response)."'
		 					ON DUPLICATE KEY UPDATE
		 					step  		= '".$step."',
		 					status_flag  	= '0',
		 					count_flag  		= '0',
		 					api_response  	= '".$this->mysql_real_escape_custom($response)."'";
		$res_log_sql = parent::execQuery($ins_log_sql, $this->conn_idc);
		}
	 function transferToOmni(){
	 	/*$sanitycheck="select * from tbl_omni_mapping where parentid='".$this->parentid."' and (omni_supplier_id <> '' and omni_supplier_id is not null)";
 		$sanitycheckres = parent::execQuery($sanitycheck, $this->dbConIro);
 		if($sanitycheckres && mysql_num_rows($sanitycheckres)>0)
 		{
 			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "already omni store is there";
			echo json_encode($result_msg_arr);exit; 
 		}*/  
 		$dependend=false;
 		$checkdept=$this->checkOmniDependent(1,2);
 		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){
 			$dependend=true;

 		}
	 	$sqlfinance="select app_amount,campaignid from payment_apportioning where parentid='".$this->parentid."' and (campaignid='73' or campaignid='72' or campaignid='74' or campaignid='84' or campaignid='85' or campaignid='75' or campaignid='86' ) and version='".$this->version."'";
			$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
			$app_amount=0;

			if(mysql_num_rows($sqlfinanceres)>0 || $dependend)
			{
	 		$omni_temp = 0;
	 		$ios_app =0;
	 		$android_app =0;
	 		while($campaignsel=mysql_fetch_assoc($sqlfinanceres)){
				if($campaignsel['campaignid'] == '84'){
					$android_app = 1;
				}else if($campaignsel['campaignid'] == '75'){
					$ios_app = 1;
				}else if($campaignsel['campaignid'] == '86'){
					$ssl_cert = 1;
				}
				
			}
				
		 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
		 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
		 		{
		 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
		 				foreach ($sqlgeneralinforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
			$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
		 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
		 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
		 		{
		 			
		 			
					while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
		 				$companydetailsarray[]=$sqlextrainforow;
		 				foreach ($sqlextrainforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
					$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;
				
				$ecsinfo="select acNo,ifs from tbl_omni_ecs_details where parentid='".$this->parentid."'";
			 	$ecsinfores = parent::execQuery($ecsinfo, $this->conn_finance);
			 		if($ecsinfores && mysql_num_rows($ecsinfores)>0)
			 		{
			 			while($ecsinforesrow=mysql_fetch_assoc($ecsinfores)){
			 					$companydetailsarray['acNo']=$ecsinforesrow['acNo'];
								$companydetailsarray['ifsc_code']=$ecsinforesrow['ifs'];
							
			 			}
						
					}
				$companydetailsarray['shop_type']='';
				$ui_app_template='';
				$templateinfo="select * from tbl_omni_extradetails where parentid='".$this->parentid."'";
			 	$templateinfores = parent::execQuery($templateinfo, $this->conn_idc);
		 		if($templateinfores && mysql_num_rows($templateinfores)>0)
		 		{
		 			while($templateinforow=mysql_fetch_assoc($templateinfores)){
		 					$companydetailsarray['shop_type']=str_replace(",","|", $templateinforow['template_id']);
		 					$ui_app_template=$templateinforow['app_template_id']; 
		 					$app_template_name=$templateinforow['app_template_name']; 
		 					$omni_temp = $templateinforow['service_type'];
						
		 			}
					
				}
				
				$sqlgetempdetails="SELECT * FROM  online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
				$getempdetailsres = parent::execQuery($sqlgetempdetails, $this->conn_idc);
				if($getempdetailsres && mysql_num_rows($getempdetailsres)>0)
		 		{
		 			while($getempdetailsrow=mysql_fetch_assoc($getempdetailsres)){
		 					$companydetailsarray['employee_code']=$getempdetailsrow['website_request_by'];
		 					if($this->version%10=='1'){

		 						$companydetailsarray['employee_type']='CS';
		 					}
		 					else if($this->version%10=='2'){
		 						$companydetailsarray['employee_type']='TME';
		 					}
		 					else if($this->version%10=='3'){
		 						$companydetailsarray['employee_type']='ME';
		 						$checkjda="select * from payment_apportioning where parentid='".$this->parentid."' and version='".$this->version."' and source=4";
		 						$checkjdares = parent::execQuery($checkjda, $this->conn_finance);
		 						if($checkjdares && mysql_num_rows($checkjdares)>0)
		 						{
		 							$companydetailsarray['employee_type']='JDA';
		 						}
		 					}
						
		 			}
				}
				$getempname="SELECT * FROM login_details.tbl_loginDetails where mktempcode='".$companydetailsarray['employee_code']."'";
				$getempnameres = parent::execQuery($getempname, $this->conn_idc);
				if($getempnameres && mysql_num_rows($getempnameres)>0)
		 		{
		 			while($getempnamerow=mysql_fetch_assoc($getempnameres)){
		 				$companydetailsarray['employee_name']=$getempnamerow['empName'];
		 			}
		 		}

				$tme_detailsql="SELECT tmecode,mecode,tmeName,meName FROM payment_otherdetails WHERE parentid='".$this->parentid."' and version='".$this->version."'";
				$tme_detailres = parent::execQuery($tme_detailsql, $this->conn_finance);
				if($tme_detailres && mysql_num_rows($tme_detailres)>0)
		 		{
		 			while($tme_detailrow=mysql_fetch_assoc($tme_detailres)){
		 				$companydetailsarray['tmeCode']=$tme_detailrow['tmecode'];
		 				$companydetailsarray['meCode']=$tme_detailrow['mecode'];
		 				$companydetailsarray['tmeName']=$tme_detailrow['tmeName'];
		 				$companydetailsarray['meName']=$tme_detailrow['meName'];
		 			}
		 		} 
		 		
				$categories=$companydetailsarray['catidlineage'];
				$categories=str_replace('/', '', $categories);
				$categories=rtrim($categories,",");
				$categories=ltrim($categories,",");
				$categories=explode(',', $categories);
				$categories=array_unique($categories);
				$categories=implode(',',$categories);

				if($companydetailsarray['shop_type']==''){
					//$gettempsql="SELECT GROUP_CONCAT(DISTINCT(template_id) ) AS template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($gettempsql, $this->dbConIro);
					$cat_params = array();
					$cat_params['page'] 		= 'omniDetailsClass';
					$cat_params['parentid'] 	= $this->parentid;
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'template_id';

					$where_arr  	=	array();
					if($categories!=''){
						$where_arr['catid']			= $categories;
						$cat_params['where']		= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
			 		
			 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			 		{
			 			$template_id_arr = array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){
								$templateid=$cat_arr['template_id'];
								if($templateid!=''){
									$template_id_arr[]= $templateid;
								}
			 			}
			 			$template_id =	implode(",", $template_id_arr);
						$companydetailsarray['shop_type']=str_replace(",","|", $template_id);
					}
				}
				if(trim($companydetailsarray['national_catidlineage'])==''){
					//$sqlnatinfo=" SELECT GROUP_CONCAT('/',national_catid,'/') as national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($sqlnatinfo, $this->dbConIro);
					$cat_params = array();
					$cat_params['page']= 'omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();
					if($categories!=''){
						$where_arr['catid']			= $categories;
						$cat_params['where']		= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

			 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			 		{
			 			$national_catid_arr =array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){
			 					$national_catid=$cat_arr['national_catid'];
			 					if($national_catid!=''){
			 						$national_catid_arr[]= $national_catid;
			 					}
			 			}
			 			$national_catid_str	=	implode("/,/", $national_catid_arr);
						$companydetailsarray['national_catidlineage']="/".$national_catid_str."/";
					}

				}
				
				/*$sanitycheck="select * from tbl_omni_extradetails where parentid='".$this->parentid."' and FIND_IN_SET('13',template_id)>0";
		 		$sanitycheckres = parent::execQuery($sanitycheck, $this->conn_idc);
		 		if($sanitycheckres && mysql_num_rows($sanitycheckres)>0)
		 		{

					$omni_type='PRODUCT';
				}
				else{
					$omni_type='SERVICE';
				}*/
				$templatedetails=$this->getTemplateMapping($companydetailsarray);
				
				$companydetailsarray['template_name']=$templatedetails['ts']['tnm'];
				$companydetailsarray['template_type']=$templatedetails['ts']['ttyp'];
				$companydetailsarray['vertical_id']=$templatedetails['ts']['vid'];
				$companydetailsarray['vertical_name']=$templatedetails['ts']['vnm'];
				$companydetailsarray['omni_type']=$templatedetails['ts']['omnityp'];
				$companydetailsarray['demo_url']=$templatedetails['ts']['demo_url'];
				$companydetailsarray['theme_id']=$templatedetails['ts']['opvid'];
				$companydetailsarray['theme_name']=$templatedetails['ts']['opvnm'];
				$omni_type=$templatedetails['ts']['omnityp'];
				if(strtolower($omni_type)=='services'){
					$omni_type='service';
				}
				else{
					$omni_type='product';
				}
				$companydetailsarray['status']="ACTUAL";
				if(trim($ui_app_template)!=null ||trim($ui_app_template)!=''  )
				$companydetailsarray['ui_template_mobile']=$ui_app_template; //for mobile 
				//$companydetailsarray['ui_template_webs']=""; for website
				$companydetailsarray['paid_status']="1"; 
				$companydetailsarray['source']="JUSTDIAL";

				$companydetailsarray['action']="registerJdCustomer";
				
				if(isset($companydetailsarray['fb_prefered_language'])){
					if($companydetailsarray['fb_prefered_language'] == 0){
						$companydetailsarray['fb_prefered_language'] = 'English';
					}else if($companydetailsarray['fb_prefered_language'] == 1){
						$companydetailsarray['fb_prefered_language'] = 'Hindi';
					}else if($companydetailsarray['fb_prefered_language'] == 2){
						$companydetailsarray['fb_prefered_language'] = 'Bengali';
					}else if($companydetailsarray['fb_prefered_language'] == 3){
						$companydetailsarray['fb_prefered_language'] = 'Gujarati';
					}else if($companydetailsarray['fb_prefered_language'] == 4){
						$companydetailsarray['fb_prefered_language'] = 'Kannada';
					}else if($companydetailsarray['fb_prefered_language'] == 5){
						$companydetailsarray['fb_prefered_language'] = 'Malayalam';
					}else if($companydetailsarray['fb_prefered_language'] == 6){
						$companydetailsarray['fb_prefered_language'] = 'Marathi';
					}else if($companydetailsarray['fb_prefered_language'] == 8){
						$companydetailsarray['fb_prefered_language'] = 'Punjabi';
					}else if($companydetailsarray['fb_prefered_language'] == 9){
						$companydetailsarray['fb_prefered_language'] = 'Tamil';
					}else if($companydetailsarray['fb_prefered_language'] == 10){
						$companydetailsarray['fb_prefered_language'] = 'Telugu';
					}
					
				}
					
				if($omni_temp != 3 || $android_app == 1 || $ios_app == 1 ){
					$omni_json = array();
					if($omni_temp == 5){
						$omni_temp = "7','13";
						$combo =1;
					}else if($omni_temp == 11){
						$omni_temp = "7','12";
						$combo =2;
					}else if($omni_temp == 12){
						$omni_temp ="7";
						$combo =3;
					}else if($omni_temp == 2){
						$android_app = 1;
						$omni_temp ="7','2";
						$combo=4;
					}else if($omni_temp == 1){
						$android_app = 1;
						$omni_temp ="7','1";
						$combo=5;
					}else if($omni_temp == 741){
						$omni_temp ="7";
					}else if($omni_temp == 748){
						$omni_temp ="14";
					}else{
						$combo =0;
					}
					
							
					if($android_app == 1 && $ios_app == 1){
						$omni_type_str = "'".$omni_temp."','8','9'";
					}else if($android_app == 1){
						$omni_type_str = "'".$omni_temp."','8'";
					}else if($ios_app == 1){
						$omni_type_str = "'".$omni_temp."','9'";
					}else{
						$omni_type_str = "'".$omni_temp."'";
					}
					
					$omni_name_sql ="select * from tbl_finance_omni_flow_display_new_new where omni_type in ($omni_type_str)";
					$omninameres = parent::execQuery($omni_name_sql, $this->conn_idc);
					$i =0;
					if($omninameres && mysql_num_rows($omninameres)>0)
			 		{	
						while($omniname=mysql_fetch_assoc($omninameres)){
							
							if($omniname['omni_type'] == '13' || $omniname['omni_type'] == '12' || $omniname['omni_type'] == '2'  || $omniname['omni_type'] == '1'){
								$omni_json[$i]['offer_name'] = $omniname['campaign_name'];
							}else{
							$omni_json[$i]['package_name'] = $omniname['campaign_name'];
							}
									
							$omni_json[$i]['campaign_id'] = $omniname['campaignid'];
							$omni_json[$i]['version'] =  $this->version;
							if($omni_temp == 2 || $omniname['omni_type'] == 13 ){
								$omni_json[$i]['price'] = $omniname['price_upfront_display'] +  $omniname['setup_upfront'] ;
							}
							if($android_app == 1 &&  $omniname['campaignid'] == '742'){
								$omni_json[$i]['finance_campaign'] = '84';
							}else if($ios_app == 1 &&  $omniname['campaignid'] == '743'){
								$omni_json[$i]['finance_campaign'] = '75';
							}else if($omniname['omni_type'] == '2' || $omniname['omni_type'] == '1'){
								$omni_json[$i]['finance_campaign'] = '72,73,84';
							}else{
								$omni_json[$i]['finance_campaign'] = '72,73';
							}
							
							if($combo == 1){
								$omni_json[$i]['Combo'] = "pack_combo";
							}else if($combo == 2){
								$omni_json[$i]['Combo'] = "pdg_combo";
							}else if($combo == 3){
								$omni_json[$i]['Combo'] = "nationallisting_combo";
							}else if($combo == 4){
								$omni_json[$i]['Combo'] = "Complete Suite";
							}else if($combo == 5){
								$omni_json[$i]['Combo'] = "Complete suite for 5 years";
							}else{
								$omni_json[$i]['Combo'] = "";
							}
							$i++;
						}
					}
					
					if($combo == 3){
						$omni_json[$i]['offer_name'] = "Natioanl listing festive combo";
						$omni_json[$i]['campaign_id'] = 10;
						$omni_json[$i]['version'] =  $this->version;
						$omni_json[$i]['finance_campaign'] = '72,73';
						$omni_json[$i]['Combo'] = "nationallisting_combo";
					}
					
					
					$rate_sql = "SELECT omniextradetails FROM d_jds.tbl_business_uploadrates where city='".$this->data_city_cm."'";
					$sqlrateres = parent::execQuery($rate_sql, $this->dbConIro);
					if($sqlrateres && mysql_num_rows($sqlrateres)>0)
			 		{
						while($jsonrates=mysql_fetch_assoc($sqlrateres)){
							$omni_rates =json_decode($jsonrates['omniextradetails'],1);
							foreach($omni_json as $key => $val){
								if(isset($omni_rates[$val['campaign_id']])){
									$omni_json[$key]['price'] = $omni_rates[$val['campaign_id']]['upfront']+$omni_rates[$val['campaign_id']]['down_payment'];
								}
							}
						}
						
					}
					
					$companydetailsarray['package']=json_encode($omni_json);
				}
				///SSL CERTIFICATE
				if($ssl_cert	==	1){
					$ratessl_sql = "SELECT ecs_upfront FROM d_jds.tbl_business_uploadrates where city='".$this->data_city_cm."'";
					$sqlrateresssl = parent::execQuery($ratessl_sql, $this->dbConIro);
					if($sqlrateresssl && mysql_num_rows($sqlrateresssl)>0){
						while($jsonrates=mysql_fetch_assoc($sqlrateresssl)){
							$omni_ratesssl =json_decode($jsonrates['ecs_upfront'],1);
							$ssl_json['campaignId'] 	= '86';
							$ssl_json['campaignName'] 	= 'SSL Certificate';
						}
					}
					$companydetailsarray['ssl_cert']=json_encode($ssl_json);
				}
				///SSL CERTIFICATE
				$this->omniUrl.="?action=registerJdCustomer";
				$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
				$formysql  = $db['mumbai']['idc']['master'];

				$sql_ins_website = "INSERT INTO omni_api_calls_log set
				 					parentid        = '".$this->parentid."',
				 					version         = '".$this->version."',
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= ''
				 					ON DUPLICATE KEY UPDATE
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= ''";
				$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
				$res_arr=json_decode($res,1);
				$res_ins_website = false;
				if(isset($res_arr['supplierId'])){
				$sql_omni_mapping = "INSERT INTO tbl_omni_mapping set
				 					parentid        = '".$this->parentid."',
				 					version         = '".$this->version."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					added_by  	= '".$this->usercode."',
				 					added_time  	= '".date('Y-m-d H:i:s')."',
				 					updated_by  	= '".$this->usercode."',
				 					updated_time  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					added_by  		= '".$this->usercode."',
				 					added_time  	= '".date('Y-m-d H:i:s')."',
				 					updated_by  	= '".$this->usercode."',
				 					updated_time  	= '".date('Y-m-d H:i:s')."'";
				$res_ins_website = parent::execQuery($sql_omni_mapping, $this->dbConIro);
				
			  
				$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid        = '".$this->parentid."',
				 					data_city       = '".$this->data_city_cm."',
				 					docid  			= '".$companydetailsarray['docid']."',
				 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."', 
				 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
				 					template_id  	= '".$companydetailsarray['shop_type']."',
				 					omni_type  	= '".$omni_type."',
				 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
				 					omni_creation_status  	= 'pass',
				 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
				 					demo_link  	= '0',
				 					ui_template_mobile_id  	= '".$ui_app_template."',
				 					ui_template_mobile_name  	= '".$ui_app_t."',
				 					approved_date  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
				 					docid  			= '".$companydetailsarray['docid']."',
				 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
				 					template_id  	= '".$companydetailsarray['shop_type']."',
				 					omni_type  	= '".$omni_type."',
				 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
				 					omni_creation_status  	= 'pass',
				 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
				 					demo_link  	= '0',
				 					approved_date  	= '".date('Y-m-d H:i:s')."'";
				$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
					$this->setTemplateInCons($templatedetails);
				}
				if($res_ins_website){
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Success";
					$result_msg_arr['data']['redirectUrl']  = $res_arr['redirectUrl'];
					$result_msg_arr['data']['supplierId']   = $res_arr['supplierId'];
					$result_msg_arr['data']['storeid']      = $res_arr['storeid'];
					echo json_encode($result_msg_arr);exit;
				}
				else{
					$this->insertIntoFailureLog(1,$res);
				 $sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					approved_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'fail'
					 					ON DUPLICATE KEY UPDATE
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					approved_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'fail'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);

					$result_msg_arr['error']['code'] = 1;
					//$result_msg_arr['error']['msg'] = "Error";
					$err_msg=json_decode($res,1);
					$result_msg_arr['error']['msg'] =$err_msg['msg'];  
					echo json_encode($result_msg_arr);exit; 
				}
		}
		else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Finance Details Missing";
					echo json_encode($result_msg_arr);exit;
		}

			
	 }
	 function setOmniDomain(){
	 	 
	 	$getdomain_name="select * from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";  
		$website='';
		$supplierid='';
		$res = parent::execQuery($getdomain_name, $this->conn_idc); 
	 	if($res && mysql_num_rows($res) )
		{
			while($row_website = mysql_fetch_assoc($res)){
				 $website=$row_website['website'];
				 $supplierid=$row_website['supplier_id'];
			}
		}
		if($website!='' && $supplierid!=''){
		 	$companydetailsarray['action']="setDomainName";
		 	$this->omniUrl.="?action=setDomainName";
		 	$companydetailsarray['domain']=$website;
		 	$companydetailsarray['supplierId']=$supplierid;
	 		$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
	 		$sql_ins_website = "INSERT INTO omni_api_calls_log set
			 					parentid        = '".$this->parentid."',
			 					version         = '".$this->version."',
			 					api_called  	= '".$this->omniUrl."',
			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= ''
			 					ON DUPLICATE KEY UPDATE
			 					api_called  	= '".$this->omniUrl."',
			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= ''";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
			
			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid        = '".$this->parentid."',
				 					data_city       = '".$this->data_city_cm."',
				 					omni_website_mapped_date  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					omni_website_mapped_date  	= '".date('Y-m-d H:i:s')."'";
			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
			if($res_ins_website){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			}


	 	}
	 	else{
	 		$this->insertIntoFailureLog(2,'no supplier or website'); 
	 		$result_msg_arr['error']['code'] = 1;
	 		$result_msg_arr['error']['msg'] = "Error Website or Supplier Id Not Found";
	 		echo json_encode($result_msg_arr);exit; 
	 	}
	 }

	 function curlCall($url,$data=null,$method='get'){
		global $genio_variables;
		global $dbarr;

			$ch = curl_init();        
	        curl_setopt($ch, CURLOPT_URL, $url);
	        //curl_setopt($ch, CURLOPT_URL, $param['url']);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	        if($method=='post'){
	        	
	        	curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	        }
	        else if($method=='json'){
	        	$body = json_encode($data);
	        	curl_setopt($ch, CURLOPT_POST, true);
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			/*	$fp = fopen('php://temp/maxmemory:256000', 'w');
				if (!$fp) 
				{
				    die('could not open temp memory data');
				}
				fwrite($fp, $body);
				fseek($fp, 0); 
				curl_setopt($ch, CURLOPT_PUT, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($ch, CURLOPT_INFILE, $fp); // file pointer
				curl_setopt($ch, CURLOPT_INFILESIZE, strlen($body)); */
				
				/*	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($body))                                                                       
				); */

	        }
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resultString = curl_exec($ch);
	        curl_close($ch); 
			return $resultString;
	}
	function tempTomainOmniExtraDetails($genio_lite_campaign = null){
		$dependend=false;
		$checkdept=$this->checkOmniDependent(0,2);
		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){
			$dependend=true;

		}

	 	$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid in ('72','73','74') and recalculate_flag=1";
		$checktempres = parent::execQuery($checktemp, $this->conn_temp);
		if( mysql_num_rows($checktempres)>0 ||$dependend || (count($genio_lite_campaign)> 0 && (array_key_exists("72",$genio_lite_campaign) || array_key_exists("73",$genio_lite_campaign) || array_key_exists("74",$genio_lite_campaign)))){
			$websiteDetails="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
			$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_temp);
			if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
	 		{
		 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
					{
				 		 $sql_ins_website = "INSERT INTO tbl_omni_extradetails set
					 					parentid='".$websiteDetailsrow['parentid']."',
					 					template_id = '".$websiteDetailsrow['template_id']."',
					 					app_template_id = '".$websiteDetailsrow['app_template_id']."',
					 					app_template_name = '".$websiteDetailsrow['app_template_name']."',
					 					service_type = '".$websiteDetailsrow['omni_type']."'
					 					ON DUPLICATE KEY UPDATE
					 					template_id = '".$websiteDetailsrow['template_id']."',
					 					app_template_id = '".$websiteDetailsrow['app_template_id']."',
					 					app_template_name = '".$websiteDetailsrow['app_template_name']."',
					 					service_type = '".$websiteDetailsrow['omni_type']."'";
						$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
				}
			}
			if($res_ins_website){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				//echo json_encode($result_msg_arr);exit;
			}
			else if(count($genio_lite_campaign) > 0)
			{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
			}	
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				//echo json_encode($result_msg_arr);exit;
			}
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Omni Finance Details";
			//echo json_encode($result_msg_arr);exit;
		}
		
		if(count($genio_lite_campaign)>0)
			return $result_msg_arr;
		else {
			echo json_encode($result_msg_arr);exit;
		}

	}
	
	function transferToOmniDemo($genio_lite_campaign = null){
		//echo "fdbdn";
			if(trim($this->mobile)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Mobile Number Missing";
				
				if(count($genio_lite_campaign)>0)
				{
					return $result_msg_arr;
				}
				else
				{
					echo json_encode($result_msg_arr);exit;
				}	
			}
			/*if(trim($this->email)=='' && $this->demo_temp_type==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Email Is Missing";
				echo json_encode($result_msg_arr);exit;
			}
		 	$sanitycheck="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."' and FIND_IN_SET('13',template_id)>0";
	 		$sanitycheckres = parent::execQuery($sanitycheck, $this->conn_temp);
	 		if($sanitycheckres && mysql_num_rows($sanitycheckres)>0)
	 		{*/
	 			
	 			$this->conn_demo=$this->conn_temp;
	 			$general='tbl_companymaster_generalinfo_shadow';
	 			$extra='tbl_companymaster_extradetails_shadow';
	 			if($this->website_demo==1 ||$this->website_demo=='1'){
	 				if($this->demo_temp_type=='')
	 				{
	 					$result_msg_arr['error']['code'] = 1;
	 					$result_msg_arr['error']['msg'] = "demo_temp_type Missing";
	 					if(count($genio_lite_campaign)>0)
						{
							return $result_msg_arr;
						}
						else
						{
							echo json_encode($result_msg_arr);exit;
						}
	 					
	 				}
	 				$this->conn_demo=$this->dbConIro;
	 				$general='tbl_companymaster_generalinfo';
	 				$extra='tbl_companymaster_extradetails';
					
					$sqlgeneralinfo="select * from ".$general." where parentid='".$this->parentid."'";
					$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_demo);
			 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
			 		{
			 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
			 				foreach ($sqlgeneralinforow as $key => $value) {
								$companydetailsarray[$key]=$value;
							}
			 			}
						
					}

					if (ctype_space($companydetailsarray['contact_person'])) {
						$companydetailsarray['contact_person']='';
					}
					
					$sqlextrainfo="select * from ".$extra." where parentid='".$this->parentid."'";
					$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->conn_demo);
			 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
			 		{
			 			
			 			
						while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
			 				$companydetailsarray[]=$sqlextrainforow;
			 				foreach ($sqlextrainforow as $key => $value) {
								$companydetailsarray[$key]=$value;
							}
			 			}
						
					}
	 			}
	 			else
	 			{
					if($this->mongo_flag==1 || $this->mongo_tme == 1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= $general;
						$mongo_inputs['fields'] 	= "";
						$sqlgeneralinforow = $this->mongo_obj->getData($mongo_inputs);
						foreach ($sqlgeneralinforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
						
						if(ctype_space($companydetailsarray['contact_person'])) {
							$companydetailsarray['contact_person']='';
						}
						
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= $extra;
						$mongo_inputs['fields'] 	= "";
						$sqlextrainforow = $this->mongo_obj->getData($mongo_inputs);
						foreach ($sqlextrainforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
					}
					else
					{
						$sqlgeneralinfo="select * from ".$general." where parentid='".$this->parentid."'";
						$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_demo);
						if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
						{
							while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
								foreach ($sqlgeneralinforow as $key => $value) {
									$companydetailsarray[$key]=$value;
								}
							}
							
						}

						if (ctype_space($companydetailsarray['contact_person'])) {
							$companydetailsarray['contact_person']='';
						}
						
						$sqlextrainfo="select * from ".$extra." where parentid='".$this->parentid."'";
						$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->conn_demo);
						if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
						{
							
							
							while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
								$companydetailsarray[]=$sqlextrainforow;
								foreach ($sqlextrainforow as $key => $value) {
									$companydetailsarray[$key]=$value;
								}
							}
						}
					}
				}

				

		 	 	
				
				if($this->mongo_flag == 1 || $this->mongo_tme == 1)
				{
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds,mainattr,facility";
					$sqltempinforow = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$sqltempinfo="select catIds,mainattr,facility from tbl_business_temp_data where contractid='".$this->parentid."'";
					$sqltempinfores = parent::execQuery($sqltempinfo, $this->conn_demo);
					$num_rows = mysql_num_rows($sqltempinfores);
					if($num_rows>0)
					{
						$sqltempinforow=mysql_fetch_assoc($sqltempinfores);
					}
				}
				$categories=$sqltempinforow['catIds'];
				$companydetailsarray['attributes']=$sqltempinforow['mainattr'];
				$companydetailsarray['attributes_edit']=$sqltempinforow['facility'];
			 		
					if($this->mobile!=''){
						$companydetailsarray['mobile']=$this->mobile;
					}
					if($this->email!=''){
						$companydetailsarray['email']=$this->email;
					}
					$companydetailsarray['catidlineage']=str_replace('/', '',$companydetailsarray['catidlineage']);
					
					$categories=str_replace('|P|', ',', $categories);
					
					if($this->website_demo!=1 ||$this->website_demo!='1'){
						$companydetailsarray['catidlineage']=$categories;
					}
					else{
						$categories=$companydetailsarray['catidlineage'];
					}

					$categories=rtrim($categories,",");
					$categories=ltrim($categories,",");
					$national_catid='';
					$categories=explode(',', $categories);
					$categories=array_unique($categories);
					$categories=implode(',',$categories);
					//$sqlnatinfo=" SELECT GROUP_CONCAT('/',national_catid,'/') as national_catid FROM tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($sqlnatinfo, $this->conn_idc);
					$cat_params = array();
					$cat_params['page'] 		='omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;			
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();
					$where_arr['catid']			= $categories;			
					$cat_params['where']		= json_encode($where_arr);
					if($categories!=''){
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
					$national_catid_arr = array();
			 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			 		{			 			
						foreach($cat_res_arr['results'] as $key =>$cat_arr){		 				
							$national_catid =	$cat_arr['national_catid'];
							if($national_catid!=''){
								$national_catid_arr[] = $national_catid;
							}						
			 			}						
					}
					$national_catid = implode("/,/", $national_catid_arr);
					if(trim($this->national_catid)!='')
						$companydetailsarray['national_catidlineage']=$this->national_catid;
					else
					$companydetailsarray['national_catidlineage']= "/".$national_catid."/";
					
					$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;

					
					$ecsinfo="select acNo,ifs from tbl_omni_ecs_details_temp where parentid='".$this->parentid."'";
				 	$ecsinfores = parent::execQuery($ecsinfo, $this->conn_temp);
				 		if($ecsinfores && mysql_num_rows($ecsinfores)>0)
				 		{
				 			while($ecsinforesrow=mysql_fetch_assoc($ecsinfores)){
				 					$companydetailsarray['acNo']=$ecsinforesrow['acNo'];
									$companydetailsarray['ifsc_code']=$ecsinforesrow['ifs'];
								
				 			}
							
						}
						
					
					$templateinfo="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
				 	$templateinfores = parent::execQuery($templateinfo, $this->conn_temp);
			 		if($templateinfores && mysql_num_rows($templateinfores)>0)
			 		{
			 			while($templateinforow=mysql_fetch_assoc($templateinfores)){
			 					$companydetailsarray['shop_type']=str_replace(",","|", $templateinforow['template_id']);
			 					$omni_temp = $templateinforow['omni_type'];
							
			 			}
					
					}
					else if(count($genio_lite_campaign)>0 && (array_key_exists("72",$genio_lite_campaign) || array_key_exists("73",$genio_lite_campaign)))
					{
						
						if($genio_lite_campaign["72"]["itemid"] == "6" || $genio_lite_campaign["73"]["itemid"] == "6")
						{
							$omni_temp = 2;
						}
						if($genio_lite_campaign["72"]["itemid"] == "16" || $genio_lite_campaign["73"]["itemid"] == "16")
						{
							$omni_temp = 13;
						}
						if($genio_lite_campaign["72"]["itemid"] == "19" || $genio_lite_campaign["73"]["itemid"] == "19")
						{
							$omni_temp = 12;
						}
						else
						{
							$omni_temp = 20;
						}	
					}
					
					$companydetailsarray['employee_code']=$this->usercode; 
					if($this->version%10=='1'){

						$companydetailsarray['employee_type']='CS';
					}
					else if($this->version%10=='2'){
						$companydetailsarray['employee_type']='TME';
					}
					else if($this->version%10=='3'){
						$getempname="SELECT * FROM login_details.tbl_loginDetails where mktempcode='".$this->usercode."' and emptype='13'";
						$getempnameres = parent::execQuery($getempname, $this->conn_idc);
						if($getempnameres && mysql_num_rows($getempnameres)>0)
				 		{
				 			$companydetailsarray['employee_type']='JDA';
				 		}
				 		else{
				 			$companydetailsarray['employee_type']='ME';
				 		}
						
					}
					$companydetailsarray['employee_name']=$this->username;

					$templatedetails=$this->getTemplateMapping($companydetailsarray);
					
					$companydetailsarray['template_name']=$templatedetails['ts']['tnm'];
					$companydetailsarray['template_type']=$templatedetails['ts']['ttyp'];
					$companydetailsarray['vertical_id']=$templatedetails['ts']['vid'];
					$companydetailsarray['vertical_name']=$templatedetails['ts']['vnm'];
					$companydetailsarray['omni_type']=$templatedetails['ts']['omnityp'];
					$companydetailsarray['demo_url']=$templatedetails['ts']['demo_url'];
					$companydetailsarray['theme_id']=$templatedetails['ts']['opvid'];
					$companydetailsarray['theme_name']=$templatedetails['ts']['opvnm'];

					$omni_type=$templatedetails['ts']['omnityp'];
					if(strtolower($omni_type)=='services'){
						if($templatedetails['ts']['active_flag']=='0' || $templatedetails['ts']['active_flag']==0)
						{
							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = "Demo store for your category is not available at this time.";
							echo json_encode($result_msg_arr);exit;
						}
					}
					
					if(isset($companydetailsarray['fb_prefered_language'])){
						if($companydetailsarray['fb_prefered_language'] == 0){
							$companydetailsarray['fb_prefered_language'] = 'English';
						}else if($companydetailsarray['fb_prefered_language'] == 1){
							$companydetailsarray['fb_prefered_language'] = 'Hindi';
						}else if($companydetailsarray['fb_prefered_language'] == 2){
							$companydetailsarray['fb_prefered_language'] = 'Bengali';
						}else if($companydetailsarray['fb_prefered_language'] == 3){
							$companydetailsarray['fb_prefered_language'] = 'Gujarati';
						}else if($companydetailsarray['fb_prefered_language'] == 4){
							$companydetailsarray['fb_prefered_language'] = 'Kannada';
						}else if($companydetailsarray['fb_prefered_language'] == 5){
							$companydetailsarray['fb_prefered_language'] = 'Malayalam';
						}else if($companydetailsarray['fb_prefered_language'] == 6){
							$companydetailsarray['fb_prefered_language'] = 'Marathi';
						}else if($companydetailsarray['fb_prefered_language'] == 8){
							$companydetailsarray['fb_prefered_language'] = 'Punjabi';
						}else if($companydetailsarray['fb_prefered_language'] == 9){
							$companydetailsarray['fb_prefered_language'] = 'Tamil';
						}else if($companydetailsarray['fb_prefered_language'] == 10){
							$companydetailsarray['fb_prefered_language'] = 'Telugu';
						}
						
					}
					
					if(intval($this->params['mass_updt_flag'])==1){
						$companydetailsarray['mass_updt_flag']=1;
					}
					$demo_link_value=1;
					if($this->dealcloseflow==1)
					{
						$dependendChk=	false;
						$checkdept=$this->checkOmniDependent(0,2);
						if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){
							$dependendChk=true;
						}
						if($dependendChk)
							$companydetailsarray['toBePaid']="5";
						else
							$companydetailsarray['toBePaid']="1";
						$demo_link_value=2;
						$finance_sql ="SELECT * FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND recalculate_flag=1";
						$financeinfores = parent::execQuery($finance_sql, $this->conn_temp);
						$android_app = 0;
						$ios_app = 0;
						if($financeinfores && mysql_num_rows($financeinfores)>0){
							while($financeinfo=mysql_fetch_assoc($financeinfores)){
								if($financeinfo['campaignid'] == '75')
									$ios_app = 1;
								if($financeinfo['campaignid'] == '84')
									$android_app = 1;
								if($financeinfo['campaignid'] == '86')
									$ssl_cert = 1;
							}
						}
						else if(count($genio_lite_campaign)>0)
						{
								if(array_key_exists("75",$genio_lite_campaign))
									$ios_app = 1;
								if(array_key_exists("84",$genio_lite_campaign))
									$android_app = 1;
								if(array_key_exists("86",$genio_lite_campaign))
									$ssl_cert = 1;
						}
							
						if($omni_temp != 3 || $android_app == 1 || $ios_app == 1 ){
							$omni_json = array();
							if($omni_temp == 5){
								$omni_temp = "7','13";
								$combo =1;
							}else if($omni_temp == 11){
								$omni_temp = "7','12";
								$combo =2;
							}else if($omni_temp == 12){
								$omni_temp ="7";
								$combo =3;
							}else if($omni_temp == 2){
								$android_app = 1;
								$omni_temp ="7','2";
								$combo=4;
							}else if($omni_temp == 1){
								$android_app = 1;
								$omni_temp ="7','1";
								$combo=5;
							}else if($omni_temp == 741){
								$omni_temp ="7";
							}else if($omni_temp == 748){
								$omni_temp ="14";
							}else{
								$combo =0;
							}
					
							if($android_app == 1 && $ios_app == 1){
								$omni_type_str = "'".$omni_temp."','8','9'";
							}else if($android_app == 1){
								$omni_type_str = "'".$omni_temp."','8'";
							}else if($ios_app == 1){
								$omni_type_str = "'".$omni_temp."','9'";
							}else{
								$omni_type_str = "'".$omni_temp."'";
							}
							
							$omni_name_sql ="select * from tbl_finance_omni_flow_display_new_new where omni_type in ($omni_type_str)";
							$omninameres = parent::execQuery($omni_name_sql, $this->conn_idc);
							$i =0;
							if($omninameres && mysql_num_rows($omninameres)>0)
							{	
								while($omniname=mysql_fetch_assoc($omninameres)){
									if($omniname['omni_type'] == '13' || $omniname['omni_type'] == '12' || $omniname['omni_type'] == '2'  || $omniname['omni_type'] == '1'){
										$omni_json[$i]['offer_name'] = $omniname['campaign_name'];
									}else{
									$omni_json[$i]['package_name'] = $omniname['campaign_name'];
									}
									
									$omni_json[$i]['campaign_id'] = $omniname['campaignid'];
									$omni_json[$i]['version'] =  $this->version;
									
									
								
									if($omni_temp == 2 || $omniname['omni_type'] == 13){
										$omni_json[$i]['price'] = $omniname['price_upfront_display'] +  $omniname['setup_upfront'] ;
									}
									
									
									if($android_app == 1 &&  $omniname['campaignid'] == '742'){
										$omni_json[$i]['finance_campaign'] = '84';
									}else if($ios_app == 1 &&  $omniname['campaignid'] == '743'){
										$omni_json[$i]['finance_campaign'] = '75';
									}else if($omniname['omni_type'] == '2' || $omniname['omni_type'] == '1'){
										$omni_json[$i]['finance_campaign'] = '72,73,84';
									}else{
										$omni_json[$i]['finance_campaign'] = '72,73';
									}
									 
									if($combo == 1){
										$omni_json[$i]['Combo'] = "pack_combo";
									}else if($combo == 2){
										$omni_json[$i]['Combo'] = "pdg_combo";
									}else if($combo == 3){
										$omni_json[$i]['Combo'] = "nationallisting_combo";
									}else if($combo == 4){
										$omni_json[$i]['Combo'] = "Complete Suite";
									}else if($combo == 5){
										$omni_json[$i]['Combo'] = "Complete suite for 5 years";
									}else{
										$omni_json[$i]['Combo'] = "";
									}
									
									$i++;
								}
							}
							else
							{
								$omni_json['0']['offer_name'] = "Omni Campaign";
								$omni_json['0']['package_name'] = "Omni Campaign";
								$omni_json['0']['campaign_id'] = "72,73"; 
								$omni_json['0']['version'] =  $this->version;
								$omni_json['0']['finance_campaign'] = '72,73';
								$omni_json['0']['Combo'] = "";
							}	
							
							if($combo == 3){
								$omni_json[$i]['offer_name'] = "Natioanl listing festive combo";
								$omni_json[$i]['campaign_id'] = 10;
								$omni_json[$i]['version'] =  $this->version;
								$omni_json[$i]['finance_campaign'] = '72,73';
								$omni_json[$i]['Combo'] = "nationallisting_combo";
							}
							
							
							$rate_sql = "SELECT omniextradetails FROM d_jds.tbl_business_uploadrates where city='".$this->data_city_cm."'";
							$sqlrateres = parent::execQuery($rate_sql, $this->dbConIro);
							if($sqlrateres && mysql_num_rows($sqlrateres)>0)
							{
								while($jsonrates=mysql_fetch_assoc($sqlrateres)){
									$omni_rates =json_decode($jsonrates['omniextradetails'],1);
									foreach($omni_json as $key => $val){
										if(isset($omni_rates[$val['campaign_id']])){
											$omni_json[$key]['price'] = $omni_rates[$val['campaign_id']]['upfront']+$omni_rates[$val['campaign_id']]['down_payment'];
										}
									}
								}
								
							}
							$companydetailsarray['package']=json_encode($omni_json);
						}
							///SSL CERTIFICATE
						if($ssl_cert	==	1){
							$ratessl_sql = "SELECT ecs_upfront FROM d_jds.tbl_business_uploadrates where city='".$this->data_city_cm."'";
							$sqlrateresssl = parent::execQuery($ratessl_sql, $this->dbConIro);
							if($sqlrateresssl && mysql_num_rows($sqlrateresssl)>0){
								while($jsonrates=mysql_fetch_assoc($sqlrateresssl)){
									$omni_ratesssl =json_decode($jsonrates['ecs_upfront'],1);
									//~ $ssl_json['price'] = $omni_ratesssl[$val['campaign_id']]['upfront']+$omni_ratesssl[$val['campaign_id']]['down_payment'];
									$ssl_json['campaignId'] 	= '86';
									$ssl_json['campaignName'] 	= 'SSL Certificate';
								}
							}
							$companydetailsarray['ssl_cert']=json_encode($ssl_json);
						}
						///SSL CERTIFICATE
				
				}
					
					$companydetailsarray['status']="GENIODEMO";
					$companydetailsarray['paid_status']="1"; 
					$companydetailsarray['source']="GENIODEMO";
					$companydetailsarray['callFrom']=$this->callFrom;
					$companydetailsarray['action']="registerJdCustomer";
					
					$this->omniUrl.="?action=registerJdCustomer";
					$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
					$sql_ins_website = "INSERT INTO omni_api_calls_log set
					 					parentid        = '".$this->parentid."',
					 					version         = '".$this->version."',
					 					api_called  	= '".$this->omniUrl."',
					 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
					 					api_result  	= '".$this->mysql_real_escape_custom(json_encode($res))."',
					 					called_time  	= '".date('Y-m-d H:i:s')."',
					 					error_text  	= ''
					 					ON DUPLICATE KEY UPDATE
					 					api_called  	= '".$this->omniUrl."',
					 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
					 					api_result  	= '".$this->mysql_real_escape_custom(json_encode($res))."',
					 					called_time  	= '".date('Y-m-d H:i:s')."',
					 					error_text  	= ''";
					$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
					$res_arr=json_decode($res,1);
					$res_ins_website = false;
					if(isset($res_arr['supplierId'])){
					if(strtolower($omni_type)=='services'){
						 
						$this->domainMappingService($companydetailsarray,$res_arr['redirectUrl']);
						$omni_type='service';
					}
					else{
						$omni_type='product';
					}
					$sql_omni_mapping = "INSERT INTO tbl_omni_mapping_demo set
					 					parentid        = '".$this->parentid."',
					 					version         = '".$this->version."',
					 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
					 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
					 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
					 					added_by  	= '".$this->usercode."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					updated_by  	= '".$this->usercode."',
					 					updated_time  	= '".date('Y-m-d H:i:s')."'
					 					ON DUPLICATE KEY UPDATE
					 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
					 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
					 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
					 					added_by  	= '".date('Y-m-d H:i:s')."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					updated_by  	= '".$this->usercode."',
					 					updated_time  	= '".date('Y-m-d H:i:s')."'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_temp);
					if($this->national_catid!=''){
						$updte="update tbl_omni_mapping_demo set diff_catid_choosed=1,diff_national_catid='".$this->national_catid."' where parentid='".$this->parentid."' and version='".$this->version."'";
						$upt = parent::execQuery($updte, $this->conn_temp);

					}
					else{
						$updte="update tbl_omni_mapping_demo set diff_catid_choosed=0,diff_national_catid='' where parentid='".$this->parentid."' and version='".$this->version."'";
						$upt = parent::execQuery($updte, $this->conn_temp);
					}
							$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."', 
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					demo_userid  	= '".$companydetailsarray['employee_code']."',
					 					demo_username  	= '".$companydetailsarray['employee_name']."',
					 					demo_usertype  	= '".$companydetailsarray['employee_type']."',
					 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
					 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
					 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'pass',
					 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
					 					demo_link  	= '".$demo_link_value."'
					 					ON DUPLICATE KEY UPDATE
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					demo_userid  	= '".$companydetailsarray['employee_code']."',
					 					demo_username  	= '".$companydetailsarray['employee_name']."',
					 					demo_usertype  	= '".$companydetailsarray['employee_type']."',
					 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
					 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
					 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'pass',
					 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
					 					demo_link  	= '".$demo_link_value."'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
						$this->setTemplateInCons($templatedetails);
					}
					else{
						if(strtolower($omni_type)=='products'){
							if(isset($res_arr['noProductFlag'])){
								if($res_arr['noProductFlag']==1 || $res_arr['noProductFlag']=='1'){
									$result_msg_arr['error']['code'] = 3;
									$result_msg_arr['error']['msg'] = "Product Store Cant Be Created"; 
									//echo json_encode($result_msg_arr);exit;
								}
							}
						}
					}
					
					if($res_ins_website){
						
					
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = "Success";
						$result_msg_arr['data'] = $res_arr;
						
					
						//echo json_encode($result_msg_arr);exit;
					}
					else{
						if(isset($res_arr['msg'])){
							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = $res_arr['msg'];
							//echo json_encode($result_msg_arr);exit;
						}
						else{
							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = 'Please Resend Omni Demo Link Error Occurred!'; 
							//echo json_encode($result_msg_arr);exit; 
						}
					}
					
					
					if(count($genio_lite_campaign)>0)
					{
						//echo '<br> supplier details <pre> :: 2235 :: '.$res_ins_website;
						//print_r($result_msg_arr);
						//print_r($genio_lite_campaign);
						return json_encode($result_msg_arr);
					}
					else {
						echo json_encode($result_msg_arr);exit;
					}
				
			/*}
			else{
		 			$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Demo store for your category is not available at this time.";
					echo json_encode($result_msg_arr);exit; 
			}*/

			
	 }
	 function checkForChildDoctors($docid){
	 	
	 	 $childurl=$this->childDoctorDetails."?parent_docid=".$docid."&type_flag=2&vertical_name=Doctor";
	 	 $res_doc=$this->curlCall($childurl,'','get');
	 	
	 	$res_doc=json_decode($res_doc,1);
	 	
	 	$doc_id_arr=array();
	 	if($res_doc['results']['child_docid_cnt']> 0 &&  isset($res_doc['results']['child_docid_cnt'])){
			$doc_id_arr=explode(',', $res_doc['results']['child_docids']);
	 		return $doc_id_arr;
	 	}

	 }
	 function saveOmniExtraDetailsTemp(){
	 	
	 	if($this->template_id!=''){
		 	$sql_ins_website = "INSERT INTO tbl_omni_extradetails_temp set
		 		 					parentid='".$this->parentid."',
		 		 					template_id='".$this->template_id."'
		 		 					ON DUPLICATE KEY UPDATE
		 		 					template_id='".$this->template_id."'";
		 	$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_temp);
		 	if($res_ins_website){
		 		$result_msg_arr['error']['code'] = 0;
		 		$result_msg_arr['error']['msg'] = "Success";
		 		echo json_encode($result_msg_arr);exit;
		 	}
		 	else{
		 		$result_msg_arr['error']['code'] = 1;
		 		$result_msg_arr['error']['msg'] = "Error";
		 		echo json_encode($result_msg_arr);exit;
		 	}
	 	}
	 	else{
	 		$result_msg_arr['error']['code'] = 1;
	 		$result_msg_arr['error']['msg'] = "No Template Details Found!";
	 		echo json_encode($result_msg_arr);exit;
	 	}
	 }
	function checkCategoryType() {
		if($this->parentid != '') {
			$sel="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."' and FIND_IN_SET('13',template_id)>0";
			$sqlres = parent::execQuery($sel, $this->conn_temp);
			if($sqlres && mysql_num_rows($sqlres)>0)
			{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Product Category";
				echo json_encode($result_msg_arr);exit;
				
			}else {
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Service Category";
				echo json_encode($result_msg_arr);exit;
			}
		}else {
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "parentId Missing";
			echo json_encode($result_msg_arr);exit;
		}
	}
	function insertDemoLinkDetails() {
		if($this->parentid != '' && $this->version != '') {
			
			$insert_sql = "insert into tbl_omnidemolink_details set 
							parentid = '".$this->parentid."',
							version = '".$this->version."',
							date = now(),
							empcode = '".$this->usercode."'
							on duplicate key update 
							date = now(),
							empcode = '".$this->usercode."'";
			$res_insert_sql = parent::execQuery($insert_sql, $this->conn_temp);
			if($res_insert_sql){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "success";
				echo json_encode($result_msg_arr);exit;
			}else {
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "failed";
				echo json_encode($result_msg_arr);exit;
			}
		}else {
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "parentid or version missing";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function fetchDemoLinkDetails() {
		if($this->parentid != '' && $this->version != '') {
			
			$sel_details = "select * from tbl_omnidemolink_details where parentid='".$this->parentid."' and version ='".$this->version."'";
			$res_sel_details = parent::execQuery($sel_details, $this->conn_temp);
			if($res_sel_details && mysql_num_rows($res_sel_details)>0)
			{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "data found";
				echo json_encode($result_msg_arr);exit;
				
			}else {
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "data not found";
				echo json_encode($result_msg_arr);exit;
			}
		}else {
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "parentid or version missing";
				echo json_encode($result_msg_arr);exit;
		}
		
		
	}
	function getTemplateMapping($companydata=null){
		if(!is_array($companydata)){
		 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
		 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
		 		{
		 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
		 				foreach ($sqlgeneralinforow as $key => $value) {
							$companydata[$key]=$value;
						}
		 			}
					
				}
				
			$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
		 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
		 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
		 		{
		 			
		 			
					while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
		 				$companydetailsarray[]=$sqlextrainforow;
		 				foreach ($sqlextrainforow as $key => $value) {
							$companydata[$key]=$value;
						}
		 			}
					
				}
				
			$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
			$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
				if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
				{
					
					
				while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
						$docid=$sqldocidinforow['docid'];
						
					}
				
			}
			$companydata['docid']=$docid;
		}

		$catdata=array();
		$categories=$companydata['catidlineage'];
		$categories=str_replace('/', '', $categories);
		$categories=rtrim($categories,",");
		$categories=ltrim($categories,",");
		$categories=explode(',', $categories);
		$categories=array_unique($categories);
		$categories=implode(',',$categories);

		//$sqlcatinfo=" SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ($categories);";
 		//$sqlcatinfores = parent::execQuery($sqlcatinfo, $this->dbConIro);
		$cat_params = array();
		$cat_params['page'] 		='omniDetailsClass';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,national_catid,template_id';

		$where_arr  	=	array();
		$where_arr['catid']			= $categories;			
		$cat_params['where']		= json_encode($where_arr);
		if($categories!=''){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
 		{	
 			foreach($cat_res_arr['results'] as $key=>$sqlcatinforow){
 					$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
 					$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
 					$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
 					$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];

 			}
			
		}
		
		$postdata=array();
		$postdatasend=array();

		//$postdata['cd']= $catdata;
		$postdatasend['data']= $catdata; 
		$postdatasend['docid']=$companydata['docid'];
		//$postdatasend['docid']='022PXX22.XX22.120127125208.V9S3';
		//$postdatasend['trace']='1';
		$postdata=json_encode($catdata);

		//$url=$this->tempmapomniUrl."?docid=".$postdatasend['docid']."&data=".urlencode($postdata);
		//$url=$this->tempmapomniUrl."?docid=".$postdatasend['docid'];
		$url=$this->tempmapomniUrl."?action=templateThemeInfo"."&docid=".$postdatasend['docid'];
		//$url=$this->tempmapomniUrl;
		$postdataarr['docid']=$companydata['docid'];
		$postdataarr['data']=($postdata);
		$res=$this->curlCall($url,$postdataarr,'post');
		
		$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
		 					parentid        = '".$this->parentid."',
		 					data_city  	= '".$this->data_city_cm."',
		 					api_params  	= '".($url)."', 
		 					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
		 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
		 					api_called_time  	= '".date('Y-m-d H:i:s')."',
		 					step=1;";
		
		$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);

		$res_arr=json_decode($res,1);
		//echo '<pre>';
		//print_r($res_arr);
		if($res_arr['error']['code']=='0'){
		
			$templatedetails=$res_arr['results'];
			$sql_ins_website = "INSERT INTO tbl_omni_extradetails set
				 					parentid='".$this->parentid."',
				 					template_name='".$templatedetails['ts']['tnm']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					vertical_name='".$templatedetails['ts']['vnm']."',
				 					arecord='".$templatedetails['ts']['pip']."',
				 					demo_url='".$templatedetails['ts']['demo_url']."',
				 					omni_type='".$templatedetails['ts']['omnityp']."'
				 					ON DUPLICATE KEY UPDATE
				 					template_name='".$templatedetails['ts']['tnm']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					vertical_name='".$templatedetails['ts']['vnm']."',
				 					arecord='".$templatedetails['ts']['pip']."',
				 					demo_url='".$templatedetails['ts']['demo_url']."',
				 					omni_type='".$templatedetails['ts']['omnityp']."'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);

			/*$sql_ins_website = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid='".$this->parentid."',
				 					data_city='".$this->data_city_cm."',
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					template_tag='".$templatedetails['ts']['tnm']."'
				 					ON DUPLICATE KEY UPDATE
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					template_tag='".$templatedetails['ts']['tnm']."'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);*/ 
			return $templatedetails; 
		}
		else{
			$sql_ins_website = "INSERT INTO omni_api_calls_log set
			 					parentid        = '".$this->parentid."',
			 					version         = '".$this->version."',
			 					api_called  	= '".$url."',
			 					api_parameter  	= '".$this->mysql_real_escape_custom($url)."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= 'service api error'
			 					ON DUPLICATE KEY UPDATE
			 					api_called  	= '".$this->omniUrl."',
			 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= 'service api error'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Service Api Error";
			if($this->from_price==0){
				
				if(count($this->genio_lite_campaign_info)>0)
					return $result_msg_arr;
				else{
				echo json_encode($result_msg_arr);
				mail ('rajakkal.ganesh@justdial.com' , 'Fetching Data No Data - Service Template Api - omni page'.$this->parentid,'Fetching Data No Data - Service Template Api - omni page'.$this->parentid);
				exit;
				}
			}
			
		}



	}
	function domainMappingService($companydata=null,$website=null,$docids_for_mapping=null){
		if(!is_array($companydata)){
			 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
			 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
			 		{
			 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
			 				foreach ($sqlgeneralinforow as $key => $value) {
								$companydata[$key]=$value;
							}
			 			}
						
					}
					
				$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
			 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
			 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
			 		{
			 			
			 			
						while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
			 				$companydetailsarray[]=$sqlextrainforow;
			 				foreach ($sqlextrainforow as $key => $value) {
								$companydata[$key]=$value;
							}
			 			}
						
					}
					
				$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
				$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
					if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
					{
						
						
					while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
							$docid=$sqldocidinforow['docid'];
							
						}
					
				}
				 $sqlomnimapping="select * from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."' "; 
				$sqlomnimappingres = parent::execQuery($sqlomnimapping, $this->conn_idc);
					if($sqlomnimappingres && mysql_num_rows($sqlomnimappingres)>0)
					{
						
						
					while($sqlomnimappingrow=mysql_fetch_assoc($sqlomnimappingres)){
							$omni_redirecturl=$sqlomnimappingrow['omni_redirecturl'];
							
						}
					
				}
				$companydata['docid']=$docid;
				if($this->action=='19' || $this->action==19){
					$templatedetails['ts']['ttyp']=$this->template_type;
					$templatedetails['ts']['vid']=$this->vertical_id;
					$templatedetails['ts']['tnm']=$this->template_name; 
					$templatedetails['ts']['active_flag']=$this->getActiveFlag($templatedetails['ts']['ttyp']); 
				}
				else if($this->action=='18' || $this->action==18){
					$getfromconsol="select template_type,vertical_id,template_tag from online_regis1.tbl_omni_details_consolidated where docid='".$docid."'";
					$getfromconsolres = parent::execQuery($getfromconsol, $this->conn_idc);
					if($getfromconsolres && mysql_num_rows($getfromconsolres)>0)
					{
					while($getfromconsolrow=mysql_fetch_assoc($getfromconsolres)){
							$templatedetails['ts']['ttyp']=$getfromconsolrow['template_type'];
							$templatedetails['ts']['vid']=$getfromconsolrow['vertical_id'];  
							$templatedetails['ts']['tnm']=$getfromconsolrow['template_tag'];  
							
						} 
						$templatedetails['ts']['active_flag']=$this->getActiveFlag($templatedetails['ts']['ttyp']);
					}
					else
						$templatedetails=$this->getTemplateMapping($companydata);



				}
				else
				$templatedetails=$this->getTemplateMapping($companydata);
				$omni_redirecturl=$this->clean_http($omni_redirecturl);
				$data['cname']				= $companydata['companyname'];
				$data['email_d']			= $companydata['email'];
				$data['mobile_d']			= $companydata['dialable_mobile'];
				$data['landline_d']			= $companydata['dialable_landline'];
				$data['tollfree_d']			= $companydata['tollfree'];
				$data['fax']				= $companydata['fax'];
				$data['address']			= $companydata['full_address'];
				$data['cat_list']			= $this->getIroLikeString(2); 
				$data['cp']					= $companydata['contact_person'];
				$data['web']				= $companydata['website'];
				$data['lat']				= $companydata['latitude'];
				$data['long']				= $companydata['longitude'];
				$data['geo_a']				= $companydata['geocode_accuracy_level'];
				$data['area']				= $companydata['area'];
				$data['city']				= $companydata['city'];
				$data['data_city']			= $companydata['data_city']; 
				$data['t_type']				= $templatedetails['ts']['ttyp'];
				$data['vertical'] 			= $templatedetails['ts']['vid'];
				$data['m_flg']				= "1";
				$data['updatedby']			= $this->usercode;
				$data['omni_d_name']		= $omni_redirecturl;// always omni_redirecturl
		}
		else{
			$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
			$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
				if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
				{
					
					
				while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
						$docid=$sqldocidinforow['docid'];
						
					}
				}
			$companydata['docid']=$docid;
			
			$templatedetails=$this->getTemplateMapping($companydata);
			$omni_redirecturl=$this->clean_http($website);// as here redirect url comes
			$data['cname']				= $companydata['companyname'];
			$data['email_d']			= $companydata['email'];
			$data['mobile_d']			= $companydata['dialable_mobile'];
			$data['landline_d']			= $companydata['dialable_landline'];
			$data['tollfree_d']			= $companydata['tollfree'];
			$data['fax']				= $companydata['fax'];
			$data['address']			= $companydata['full_address'];
			$data['cat_list']			= $this->getIroLikeString(1); 
			$data['cp']					= $companydata['contact_person'];
			$data['web']				= $companydata['website'];
			$data['lat']				= $companydata['latitude'];
			$data['long']				= $companydata['longitude'];
			$data['geo_a']				= $companydata['geocode_accuracy_level'];
			$data['area']				= $companydata['area'];
			$data['city']				= $companydata['city'];
			$data['data_city']			= $companydata['data_city']; 
			$data['t_type']				= $templatedetails['ts']['ttyp'];
			$data['vertical'] 			= $templatedetails['ts']['vid'];
			$data['m_flg']				= "1";
			$data['updatedby']			= $this->usercode;
			$data['omni_d_name']		= $omni_redirecturl; // always omni_redirecturl
		}
		
		
		
		
		//$url=$this->domainomniUrl."?domain=".$website."&did=".$companydata['docid']."&data=".rawurlencode((json_encode($data)))."";
		$url=$this->domainomniUrl;
		
		$postdataarr['domain']=$website;
		$postdataarr['did']=$companydata['docid'];
		$postdataarr['data']=((json_encode($data)));
		if($data['t_type']==12 || $data['t_type']=='12'){
						$docids_for_mapping=$this->checkForChildDoctors($companydata['docid']); 
		}
		if(is_array($docids_for_mapping)){
			array_push($docids_for_mapping, $companydata['docid']);
			
			foreach ($docids_for_mapping as $dckey => $dcvalue) {
				$postdataarr['domain']=$website;
				$postdataarr['did']=$dcvalue;
				$data_for_child=$data;
				$data_for_child=$this->getChildDoctorInfo($dcvalue);
				$data_for_child['omni_d_name']		= $omni_redirecturl;
				$data_for_child['did']		=$dcvalue;
				$data_for_child['t_type']				= $templatedetails['ts']['ttyp'];
				$data_for_child['vertical'] 			= $templatedetails['ts']['vid'];
				$data_for_child['trace'] 			= 1;
				$postdataarr['data']=((json_encode($data_for_child)));
				$res=$this->curlCall($url,$postdataarr,'post');  
				 $sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
				  					parentid        = '".$this->parentid."',
				  					data_city  	= '".$this->data_city_cm."',
				  					api_params  	= '".($url)."', 
				  					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
				  					api_result  	= '".$this->mysql_real_escape_custom($res)."',
				  					api_called_time  	= '".date('Y-m-d H:i:s')."',
				  					step=2";
				 $res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
			}

		}
		else{
				$res=$this->curlCall($url,$postdataarr,'post');
			$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
		 					parentid        = '".$this->parentid."',
		 					data_city  	= '".$this->data_city_cm."',
		 					api_params  	= '".($url)."', 
		 					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
		 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
		 					api_called_time  	= '".date('Y-m-d H:i:s')."',
		 					step=2";
		$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
		}
	

		
		$res_arr=json_decode($res,1);
		if($res_arr['error']['code']=='0'){

			
			if(is_array($docids_for_mapping)){
				array_push($docids_for_mapping, $companydata['docid']);
				foreach ($docids_for_mapping as $dckey => $dcvalue) {
					$url=$this->domainomniUrl."?domain=".$website."&did=".$dcvalue."&a_flag=".$templatedetails['ts']['active_flag']; 
					$res=$this->curlCall($url,$postdata,'get');
					$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
					 					parentid        = '".$this->parentid."',
					 					data_city  	= '".$this->data_city_cm."',
					 					api_params  	= '".($url)."', 
					 					params  	= '".($this->mysql_real_escape_custom($url))."', 
					 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
					 					api_called_time  	= '".date('Y-m-d H:i:s')."',
					 					step=3";
					$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
				}

			}
			else{
				$url=$this->domainomniUrl."?domain=".$website."&did=".$companydata['docid']."&a_flag=".$templatedetails['ts']['active_flag']; 
				$res=$this->curlCall($url,$postdata,'get');
				$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
				 					parentid        = '".$this->parentid."',
				 					data_city  	= '".$this->data_city_cm."',
				 					api_params  	= '".($url)."', 
				 					params  	= '".($this->mysql_real_escape_custom($url))."', 
				 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
				 					api_called_time  	= '".date('Y-m-d H:i:s')."',
				 					step=3";
				$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
			}

			
			$sql_ins_website = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid='".$this->parentid."',
				 					data_city='".$this->data_city_cm."',
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					template_tag='".$templatedetails['ts']['tnm']."'
				 					ON DUPLICATE KEY UPDATE
				 					vertical_id='".$templatedetails['ts']['vid']."',
				 					template_type='".$templatedetails['ts']['ttyp']."',
				 					template_tag='".$templatedetails['ts']['tnm']."'";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
			$this->setTemplateInCons($templatedetails);
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			if($templatedetails['ts']['active_flag']=='1'){
				$uptwebsite="update online_regis1.tbl_omni_details_consolidated  set website_date='".date('Y-m-d H:i:s')."',updated_by='omni service team' where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."' and demo_link=0" ; 
				$ressupweb=parent::execQuery($uptwebsite, $this->conn_idc);  

				$contact_person=$companydata['contact_person'];
				$mobile=$companydata['mobile'];
				$email=$companydata['email'];
				$mobile=explode(',',$mobile);
				$email=explode(',',$email); 
				$mobile=empty($mobile[0])?$mobile[1]:$mobile[0];
				$email=empty($email[0])?$email[1]:$email[0];

					$compdet['parent_id']= $companydata['docid']; // parentid is just a token . not actual parent id as confirmed by tejas nikam
					$compdet['doc_id'] = $companydata['docid'];
					$compdet['contact_person'] = $companydata['contact_person'];
					$compdet['company_name'] = $companydata['companyname'];
					$compdet['company_number'] = $mobile;
					$compdet['city']= $companydata['city'];
					$compdet['email_id'] = $email;
					$compdet['type_flag'] = 'hromni_service'; 
			
			$url=$this->omniHrUrl;
			
			$reshr=$this->curlCall($url,$compdet,'json');
			$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
			 					parentid        = '".$this->parentid."',
			 					data_city  	= '".$this->data_city_cm."',
			 					api_params  	= '".($url)."', 
			 					params  	= '".($this->mysql_real_escape_custom(json_encode($compdet)))."', 
			 					api_result  	= '".$this->mysql_real_escape_custom($reshr)."',
			 					api_called_time  	= '".date('Y-m-d H:i:s')."',
			 					step=4"; 
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
			}
			return json_encode($result_msg_arr);
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
			 					parentid        = '".$this->parentid."',
			 					data_city  	= '".$this->data_city_cm."',
			 					api_params  	= '".$this->mysql_real_escape_custom($url)."',
			 					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
			 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
			 					api_called_time  	= '".date('Y-m-d H:i:s')."',
			 					step=2";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
			mail ('rajakkal.ganesh@justdial.com' , 'Service Domain Mapping Failure'.$this->parentid,'Service Domain Mapping Failure'.$this->parentid);

			$sql_omni_mapping = "INSERT INTO online_regis1.domain_mapping_failure_rerun set
				 					docid        = '".$companydata['docid']."',
				 					failure_reason = '".$this->mysql_real_escape_custom($res)."',
				 					count_flag      = '1',
				 					status_flag  		= '0'
				 					ON DUPLICATE KEY UPDATE
				 					failure_reason = '".$this->mysql_real_escape_custom($res)."',
				 					count_flag      = '1',
				 					status_flag  		= '0'";
			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
			return json_encode($result_msg_arr);  
		}

	}
	function getChildDoctorInfo($docid){
				$parentid=strstr($docid, 'P');
			 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$parentid."'";
			 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
			 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
			 		{
			 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
			 				foreach ($sqlgeneralinforow as $key => $value) {
								$companydata[$key]=$value;
							}
			 			}
						
					}
					
				$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$parentid."'";
			 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
			 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
			 		{
			 			
			 			
						while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
			 				$companydetailsarray[]=$sqlextrainforow;
			 				foreach ($sqlextrainforow as $key => $value) {
								$companydata[$key]=$value;
							}
			 			}
						
					}
			
				//$templatedetails=$this->getTemplateMapping($companydata);
				$omni_redirecturl=$this->clean_http($omni_redirecturl);
				$data['cname']				= $companydata['companyname'];
				$data['email_d']			= $companydata['email'];
				$data['mobile_d']			= $companydata['dialable_mobile'];
				$data['landline_d']			= $companydata['dialable_landline'];
				$data['tollfree_d']			= $companydata['tollfree'];
				$data['fax']				= $companydata['fax'];
				$data['address']			= $companydata['full_address'];
				$data['cat_list']			= $this->getIroLikeString(2); 
				$data['cp']					= $companydata['contact_person'];
				$data['web']				= $companydata['website'];
				$data['lat']				= $companydata['latitude']==null?0:$companydata['latitude'];
				$data['long']				= $companydata['longitude']==null?0:$companydata['longitude'];
				$data['geo_a']				= $companydata['geocode_accuracy_level'];
				$data['area']				= $companydata['area'];
				$data['city']				= $companydata['city'];
				$data['data_city']			= $companydata['data_city']; 
				$data['m_flg']				= "1";
				$data['updatedby']			= $this->usercode;
				
				return $data; 
	}
	function getActiveFlag($templateid){
		$url=$this->tempmapomniUrl."?action=templateThemeInfo&t_typ=".$templateid;
		$res=$this->curlCall($url,$postdataarr,'post');
		$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
		 					parentid        = '".$this->parentid."',
		 					data_city  	= '".$this->data_city_cm."',
		 					api_params  	= '".($url)."', 
		 					params  	= '".($templateid)."', 
		 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
		 					api_called_time  	= '".date('Y-m-d H:i:s')."',
		 					step=5;";
		
		$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
		$res_arr=json_decode($res,1);
		$action_flag=$res_arr['active_flag'];
		return $action_flag;  
	}
	function getIroLikeString($type=2){
	if($type=='2'){
 	
 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
 		{
 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
 				foreach ($sqlgeneralinforow as $key => $value) {
					$companydetailsarray[$key]=$value;
				}
 			}
			
		}
		
	$sqlextrainfo="select *,catidlineage AS catidlineage_origin from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
 		{
 			
 			
			while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
 				$rowContrctExtraDet[]=$sqlextrainforow;
 				
 				foreach ($sqlextrainforow as $key => $value) {

					$rowContrctExtraDet[$key]=$value;
				}
 			}
			
		}

		$catidlineage_originArr = array();			
		$catidlineage_nonpaidArr = array();		
		$consolidatedCatid = array();
		$tempCatidlineage = $rowContrctExtraDet['catidlineage_origin'];
		$catidlineage_originArr = explode(",", $rowContrctExtraDet['catidlineage_origin']);
		$catidlineage_originArr = str_replace("/","",$catidlineage_originArr);
		$catidlineage_nonpaidArr = explode(",", $rowContrctExtraDet['catidlineage_nonpaid']);
		$catidlineage_nonpaidArr = str_replace("/","",$catidlineage_nonpaidArr);

		$consolidatedCatid = array_merge((array)$catidlineage_originArr, (array)$catidlineage_nonpaidArr);

		foreach($consolidatedCatid as $key=>$value){
			if($value==''){
				unset($consolidatedCatid[$key]);
			}
		}
		$consolidatedCatidStr = implode("','",$consolidatedCatid);
		$consolidatedCatidStr = "'".$consolidatedCatidStr."'";

		$filterArr = array();
		$Arr_logs["consolidatedCatid"] 	= $consolidatedCatidStr;
	}
	elseif($type==1){

	if($this->mongo_flag == 1 || $this->mongo_tme == 1)
	{
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $this->parentid;
		$mongo_inputs['data_city'] 	= $this->data_city;
		$mongo_inputs['module']		= $this->module;
		$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
		$mongo_inputs['fields'] 	= "";
		$sqlgeneralinforow = $this->mongo_obj->getData($mongo_inputs);
	}
	else
	{
		$sqlgeneralinfo="select * from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
		$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_temp);
		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
		{
			$sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores);
		}
	}
	foreach ($sqlgeneralinforow as $key => $value) {
		$companydetailsarray[$key]=$value;
	}
	
	
		
	$sqlextrainfo="select *,catidlineage AS catidlineage_origin from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
			if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
			{
				
				
			while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
					$rowContrctExtraDet[]=$sqlextrainforow;
					
					foreach ($sqlextrainforow as $key => $value) {

					$rowContrctExtraDet[$key]=$value;
				}
				}
			
		}
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$sqltempinforow = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqltempinfo="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
			$sqltempinfores = parent::execQuery($sqltempinfo, $this->conn_temp);
			$num_rows = mysql_num_rows($sqltempinfores);
			if($num_rows>0)
			{
				$sqltempinforow=mysql_fetch_assoc($sqltempinfores);
			}
		}
		$categories_bk=$sqltempinforow['catIds'];
		
		$cat_arr=explode('|P|', $categories_bk);
		foreach ($cat_arr as $ckey => $cvalue) {
			
			if(trim($cvalue)!=''){
				
				$categories.="/".$cvalue."/,";
			}
		}
		$categories=rtrim($categories,",");
		
		$rowContrctExtraDet['catidlineage_origin']=$categories;
		$rowContrctExtraDet['catidlineage_nonpaid']=$categories;

		$catidlineage_originArr = array();			
		$catidlineage_nonpaidArr = array();		
		$consolidatedCatid = array();
		$tempCatidlineage = $rowContrctExtraDet['catidlineage_origin'];
		$catidlineage_originArr = explode(",", $rowContrctExtraDet['catidlineage_origin']);
		$catidlineage_originArr = str_replace("/","",$catidlineage_originArr);
		$catidlineage_nonpaidArr = explode(",", $rowContrctExtraDet['catidlineage_nonpaid']);
		$catidlineage_nonpaidArr = str_replace("/","",$catidlineage_nonpaidArr);

		$consolidatedCatid = array_merge((array)$catidlineage_originArr, (array)$catidlineage_nonpaidArr);

		foreach($consolidatedCatid as $key=>$value){
			if($value==''){
				unset($consolidatedCatid[$key]);
			}
		}
		$consolidatedCatidStr = implode("','",$consolidatedCatid);
		$consolidatedCatidStr = "'".$consolidatedCatidStr."'";

		$filterArr = array();
		$Arr_logs["consolidatedCatid"] 	= $consolidatedCatidStr;

	}	
		
		if($consolidatedCatidStr!="''") {
			$sqlHfilter = "SELECT distinct a.catid FROM d_jds.tbl_categorymaster_generalinfo a join d_jds.tbl_categorymaster_generalinfo b on a.national_catid=b.associate_national_catid 
			WHERE b.catid IN (".$consolidatedCatidStr.") AND b.national_catid!=b.associate_national_catid ;";
			
			//$resHfilter = parent::execQuery($sqlHfilter, $this->dbConIro);

			$cat_params = array();
			$cat_params['page']= 'omniDetailsClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'associate_national_catid';

			$where_arr  	=	array();
			if($consolidatedCatidStr!=''){
				$where_arr['catid']			= $consolidatedCatidStr;
				$where_arr['isdeleted']		= '0';
				$where_arr['mask_status']	= '0';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				//$row = parent::fetchData($res);
				foreach ($cat_res_arr['results'] as $key => $cat_arr) {
					$associate_national_catid = $cat_arr['associate_national_catid'];
					if($associate_national_catid!=''){
						$associate_national_catid_arr[] = $associate_national_catid;
					}
				}
				if(count($associate_national_catid_arr)>0)
				{
					$associate_national_catid_arr = array_unique($associate_national_catid_arr);
					$associate_national_catid_arr = array_filter($associate_national_catid_arr);
					$associate_national_catid_str = implode(",",$associate_national_catid_arr);
					
					$final_parent_category_arr = array();					
					$cat_params = array();
					$cat_params['page']= 'omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid';

					$where_arr  	=	array();
					if($associate_national_catid_str!=''){
						$where_arr['national_catid']	= $associate_national_catid_str;
						$where_arr['catid']				= "!".$consolidatedCatidStr;
						$where_arr['isdeleted']			= '0';
						$where_arr['mask_status']	= '0';
						$cat_params['where']		= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
					{	
						foreach ($cat_res_arr['results'] as $key => $cat_arr) {
							$parent_categories =	$cat_arr['catid'];
							if($parent_categories!=''){
								$parent_categories_arr[]= $parent_categories;
							}									
						}

						if(count($parent_categories_arr)>0)
						{
							$parent_categories_arr = array_unique($parent_categories_arr);
							$parent_categories_arr = array_filter($parent_categories_arr);
							$filterArr = $parent_categories_arr;
						}
					}
				}
			}

			// final list here 
			/*while($rowHfilter = mysql_fetch_assoc($resHfilter)) {
				$filterArr[] = $rowHfilter['catid'];
			}*/

			unset($resHfilter, $sqlHfilter, $rowHfilter);
		}
		$Arr_logs["cat_filter"] = $filterArr;
		$diffArr = array();
		$diffArr = array_diff($filterArr, $consolidatedCatid);
		
		$finalCatlist = array_merge((array)$consolidatedCatid, (array)$diffArr);
		if(count($finalCatlist)>0){
			$Strcatidlineage_search = "/".implode("/,/", $finalCatlist)."/";
		}
		
		$finalCatlistStr = implode("','",$finalCatlist);
		if($finalCatlistStr!=''){
			$catids = "'".$finalCatlistStr."'";
		}
		$tempCatArr=explode(",",$catids);
		foreach ($tempCatArr as $key => $value) {
			$value=trim($value,"'");
			$value="'".$value."'";
			$tempCatArr[$key]=$value;
			if (is_null($value) || $value=="" || $value=="''") {
			unset($tempCatArr[$key]);
		  }
		} 
		$strTempCat=implode(",",$tempCatArr);
		$catids = $strTempCat;
		if($catids) {

			//$get_mcat_list = "select category_name as catname,catid,national_catid,if(category_verticals&8192=8192,1,0) as LifestyleTag,category_verticals as classtype, if(business_flag='1' or category_scope=1,1,0) as b2b_tag ,if(auto_suggest_flag=1 or national_catid=associate_national_catid,1,0) AS dflag from d_jds.tbl_categorymaster_generalinfo where catid in(".$catids.") and isdeleted=0 and (mask_status=0 or category_name like 'C2C%') and active_flag > 0 and biddable_type = 1 and (category_name not like '%(rs %' or  category_name not like '% rs %') group by catid,category_name order by filter_callcount_rolling desc";
			//$res_mget_cat_list = parent::execQuery($get_mcat_list, $this->dbConIro);
			$cat_params = array();
			$cat_params['page'] 		='omniDetailsClass';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'category_name,catid,national_catid,category_verticals,category_scope,business_flag,auto_suggest_flag,associate_national_catid';
			$cat_params['orderby'] 		= 'filter_callcount_rolling desc';

			$where_arr  	=	array();
			$where_arr['catid']			= $catids;
			$where_arr['isdeleted']		= '0';
			$where_arr['mask_status']	= '0';
			$where_arr['biddable_type']	= '1';			
			$where_arr['catid']			= $catids;
			$cat_params['where']		= json_encode($where_arr);
			if($catids!=''){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				foreach($cat_res_arr['results'] as $key =>$row_get_cat_list){
					
					$classtype = $this->getCat_Type(trim($row_get_cat_list['category_verticals']));

					/*if($row_get_cat_list['b2b_tag']==1){
						$b2btag=1;
					}*/
					$category_verticals = $row_get_cat_list['category_verticals'];
					$LifestyleTag	=0;
					if(((int)$category_verticals & 8192) == 8192){
						$LifestyleTag =1;
					}
					
					if(trim($LifestyleTag)==1)
					{	
						$flagLifeStylem="LFSTL";
					}
					$auto_suggest_flag 			= $row_get_cat_list['auto_suggest_flag'];
					$national_catid 			= $row_get_cat_list['national_catid'];
					$associate_national_catid 	= $row_get_cat_list['associate_national_catid'];
					//if(auto_suggest_flag=1 or national_catid=associate_national_catid,1,0) AS dflag
					$dflag = 0;
					if($auto_suggest_flag==1 || ($national_catid ==$associate_national_catid)){
						$dflag =1;
					}

					if(in_array($row_get_cat_list['catid'], $catidlineage_originArr)){
						$catTag = 1;
					} else if(in_array($row_get_cat_list['catid'], $catidlineage_nonpaidArr)){
						$catTag = 2;
					} else if (in_array($row_get_cat_list['catid'], $diffArr)){
						$catTag = 0;
					}
					$k++;
					$category_name_list_medium .= "/".$row_get_cat_list['catid']."/|".addslashes(str_replace('','',$row_get_cat_list['category_name']))."/|/|/|/|".$row_get_cat_list['national_catid']."/|".$flagLifeStylem."/|".$classtype."/|".$catTag."/|".$dflag."|&|";
				}
				return $category_name_list_medium=rtrim($category_name_list_medium,"|&|");
			}
		}

	}
	
	function getCat_Type($bit_val){
		$Arr_res = array(1=>'EVENT',2=>'REST',4=>'HOTL',8=>'MVIE',16=>'Hotel',32=>'EDU',64=>'RESORT',128=>'HOSTEL',256=>'HOSPITAL',512=>'BOOK',1024=>'GYM',2048=>'WATER',4096=>'TEMP EVENTS',8192=>'LIFESTYLE',16384 =>'OTHER');
		foreach($Arr_res as $key=>$val){
			if(($bit_val & $key) == $key){
				return $val;
			}				
		}
		return '';			
	}
	function setFromFinance(){


	}
	function setDomainCs(){
		if(trim($this->cs_website)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Website Missing";
			echo json_encode($result_msg_arr);exit;
		}
		$domainname=$this->cs_website;
		$apiDetails="update tbl_omni_mapping set omni_website='".addslashes(stripslashes($domainname))."' where parentid='".$this->parentid."' and version='".$this->version."'"; 
		$apiDetailsres = parent::execQuery($apiDetails, $this->dbConIro);


	 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
	 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
	 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
	 		{
	 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
	 				foreach ($sqlgeneralinforow as $key => $value) {
						$companydetailsarray[$key]=$value;
					}
	 			}
				
			}
				
		$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
	 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
	 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
	 		{
	 			
	 			
				while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
	 				$companydetailsarray[]=$sqlextrainforow;
	 				foreach ($sqlextrainforow as $key => $value) {
						$companydetailsarray[$key]=$value;
					}
	 			}
				
			}
				
		 $sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
		$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
			if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
			{
				
				
			while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
					$docid=$sqldocidinforow['docid'];
					
				}
			
		}
		$companydetailsarray['docid']=$docid;
		$templatedetails=$this->getTemplateMapping($companydetailsarray);
		$this->setOmniDomain();
		$companydetailsarray['template_name']=$templatedetails['ts']['tnm'];
		$companydetailsarray['template_type']=$templatedetails['ts']['ttyp'];
		$companydetailsarray['vertical_id']=$templatedetails['ts']['vid'];
		$companydetailsarray['vertical_name']=$templatedetails['ts']['vnm'];
		$companydetailsarray['omni_type']=$templatedetails['ts']['omnityp'];
		$companydetailsarray['demo_url']=$templatedetails['ts']['demo_url'];
		$arecord=$templatedetails['ts']['pip'];
		$omni_type=$templatedetails['ts']['omnityp'];
		if(strtolower($omni_type)=='services'){
			$this->domainMappingService('',$this->cs_website);
			$omni_type='service';
		}
		if($this->set_domain==1 || $this->set_domain=='1'){

			$consolidated = "update online_regis1.tbl_omni_details_consolidated set  website='".$this->cs_website."',website_arecord='".$arecord."' where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
			$consolidated_res = parent::execQuery($consolidated, $this->conn_idc);

		} 
		$checkasql="select own_cust_website from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
		$checkarecord = parent::execQuery($checkasql, $this->conn_idc);
		if($checkarecord && mysql_num_rows($checkarecord)>0)
		{
			while($checkarecordrow=mysql_fetch_assoc($checkarecord)){
					$own_cust_website=$checkarecordrow['own_cust_website'];
					
			}
			
		} 
		if($own_cust_website=='no'){ 
			
			$parameter = "domainname=".urlencode($this->cs_website)."&action=createcustomarecord&ip=".$arecord;
			$url =$this->meurl.'/business/domainServices.php?action=createcustomarecord&'; 
			$arecordcreation=$this->curlCall($url,$parameter,'post');
			$arecordcreation_arr=$this->curlCall($url,$parameter,'post');
			if(strtolower($zoneres_arr['status'])!='error'){
				$this->createWebsiteLog($url,$parameter,$zoneres,'6','Pass');
			}
			else{ 
				$this->createWebsiteLog($url,$parameter,$zoneres,'6','Fail');
			}
		}
		

		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		$result_msg_arr['data']['arecord'] = $arecord;
		echo json_encode($result_msg_arr);exit;
	}
	function clean_http($url)
    {
        $purl= parse_url($url);
        //echo "<pre>";print_r($purl);echo "</pre>";

        if(stristr($url, "jdomni.com/marketplace/"))
        {
            if(!(empty($purl['host'])))
                $final_domain = $purl['host'].$purl['path'].'?'.$purl['query'];
            else
                $final_domain = trim($purl['path'],"/");
            return $final_domain;
        }

        if(!(empty($purl['host'])))
        {
            //echo '<br>got host';
            $purl['host'] = str_replace("www.","",$purl['host']);
            //print_r($purl);
            if(stristr($purl['host'],".jdomni.com") && !empty($purl['path']))
            {
                //echo '<br>Internla IP:';
                $purl['path'] = trim($purl['path'],"/");
                $pos          = stripos($purl['path'], "/");
                //echo '<br>Pos:'.$pos;
                if($pos===false)
                {
                    $final_domain = $purl['host']."/".$purl['path'];
                }
                else
                {
                    $final_domain = $purl['host']."/".substr($purl['path'],0,$pos);
                }
                //print_r($purl);
            }
            else
            {
                $final_domain = $purl['host'];
            }
        }
        else
        {
            $purl['path'] = trim($purl['path'],"/");
            $purl_array   = explode("/",$purl['path']);
            //print_r($purl_array);
            $purl['host']  = str_replace("www.","",($purl_array[0]));
            $purl['path']  = $purl_array[1];

            if(stristr($purl['host'],".jdomni.com") && !empty($purl['path']))
            {
                $purl['path'] = trim($purl['path'],"/");
                $pos          = stripos($purl['path'], "/");
                //echo '<br>Pos:'.$pos;
                if($pos===false)
                {
                    $final_domain = $purl['host']."/".$purl['path'];
                }
                else
                {
                    $final_domain = $purl['host']."/".substr($purl['path'],0,$pos);
                }
                //print_r($purl);
            }
            else
            {
                $final_domain = $purl['host'];
            }
        }
        return $final_domain;
    } 

	function createWebsiteLog($url,$parameter,$response,$step,$result){
		//tbl_omni_website_details_log revert after prcoess
		$sql_ins_website_log = "INSERT INTO tbl_omni_website_details_log set
				 					parentid='".$this->parentid."',
				 					api_call='".stripslashes(addslashes($url.$parameter))."',
				 					api_result = '".stripslashes(addslashes($response))."',
				 					step ='".$step."',
				 					result = '".$result."',
				 					call_time  	= '".date('Y-m-d H:i:s')."'";
				 					//print_r($this->conn_idc);
		$res_ins_website_log = parent::execQuery($sql_ins_website_log, $this->conn_idc);

	}
	
    function forDisplayingDemoProduct(){
    	$dis_array=array();
    	$sql="select * from online_regis1.tbl_omni_demo_product_list where display_flag=1";
    	$sql_dem = parent::execQuery($sql, $this->conn_idc);
    		if($sql_dem && mysql_num_rows($sql_dem)>0)
    		{
				while($sql_dem_row=mysql_fetch_assoc($sql_dem)){
					$dis_array[$sql_dem_row['national_catid']]=$sql_dem_row['category_name'];
				}
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				$result_msg_arr['data']['catlist'] =$dis_array;
				echo json_encode($result_msg_arr);exit;
    		}
    		else{
    			$result_msg_arr['error']['code'] = 1;
    			$result_msg_arr['error']['msg'] = "Error";
    			
    			echo json_encode($result_msg_arr);exit;
    		}

    }
    function setOmniDomainCustom(){
	 	 
	 	$website=$this->custom_website;
	 	$supplierid=$this->supplierid; 
		if($website!='' && $supplierid!=''){
			$companydetailsarray='';

			$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
		 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
		 		{
		 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
		 				foreach ($sqlgeneralinforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
				
			$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
		 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
		 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
		 		{
		 			
		 			
					while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
		 				$companydetailsarray[]=$sqlextrainforow;
		 				foreach ($sqlextrainforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
					 $sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;
				
				$ecsinfo="select acNo,ifs from tbl_omni_ecs_details where parentid='".$this->parentid."'";
			 	$ecsinfores = parent::execQuery($ecsinfo, $this->conn_finance);
			 		if($ecsinfores && mysql_num_rows($ecsinfores)>0)
			 		{
			 			while($ecsinforesrow=mysql_fetch_assoc($ecsinfores)){
			 					$companydetailsarray['acNo']=$ecsinforesrow['acNo'];
								$companydetailsarray['ifsc_code']=$ecsinforesrow['ifs'];
							
			 			}
						
					}

			$this->domainMappingService('',$this->custom_website); 
			$companydetailsarray='';
		 	$companydetailsarray['action']="setDomainName";
		 	$this->omniUrl.="?action=setDomainName";
		 	$companydetailsarray['domain']=$website;
		 	$companydetailsarray['supplierId']=$supplierid;
	 		$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
	 		$sql_ins_website = "INSERT INTO omni_api_calls_log set
			 					parentid        = '".$this->parentid."',
			 					version         = '".$this->version."',
			 					api_called  	= '".$this->omniUrl."',
			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= ''
			 					ON DUPLICATE KEY UPDATE
			 					api_called  	= '".$this->omniUrl."',
			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
			 					called_time  	= '".date('Y-m-d H:i:s')."',
			 					error_text  	= ''";
			$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);
			
			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid        = '".$this->parentid."',
				 					data_city       = '".$this->data_city_cm."',
				 					website       = '".$this->custom_website."',
				 					omni_website_mapped_date  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					omni_website_mapped_date  	= '".date('Y-m-d H:i:s')."',
				 					website       = '".$this->custom_website."'		";
			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
			if($res_ins_website){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
	 			echo json_encode($result_msg_arr);exit;  		
			}
			else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error"; 
		 			echo json_encode($result_msg_arr);exit; 
			}

	 	}
	 	else{

	 		$result_msg_arr['error']['code'] = 1;
	 		$result_msg_arr['error']['msg'] = "Error Website or Supplier Id Not Found";
	 		echo json_encode($result_msg_arr);exit; 
	 	}
	 }

	 function transferToOmniCustom(){  
	 	/*$sqlfinance="select app_amount from payment_apportioning where parentid='".$this->parentid."' and (campaignid='73' or campaignid='72' or campaignid='74') and version='".$this->version."' group by parentid";
			$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
			$app_amount=0;

			if(mysql_num_rows($sqlfinanceres)>0)
			{
	 		*/	
		 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
		 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
		 		{
		 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
		 				foreach ($sqlgeneralinforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
			$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
		 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
		 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
		 		{
		 			
		 			
					while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
		 				$companydetailsarray[]=$sqlextrainforow;
		 				foreach ($sqlextrainforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
		 			}
					
				}
				
					 $sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;
				
				$ecsinfo="select acNo,ifs from tbl_omni_ecs_details where parentid='".$this->parentid."'";
			 	$ecsinfores = parent::execQuery($ecsinfo, $this->conn_finance);
			 		if($ecsinfores && mysql_num_rows($ecsinfores)>0)
			 		{
			 			while($ecsinforesrow=mysql_fetch_assoc($ecsinfores)){
			 					$companydetailsarray['acNo']=$ecsinforesrow['acNo'];
								$companydetailsarray['ifsc_code']=$ecsinforesrow['ifs'];
							
			 			}
						
					}
				$companydetailsarray['shop_type']='';
				$templateinfo="select * from tbl_omni_extradetails where parentid='".$this->parentid."'";
			 	$templateinfores = parent::execQuery($templateinfo, $this->conn_idc);
		 		if($templateinfores && mysql_num_rows($templateinfores)>0)
		 		{
		 			while($templateinforow=mysql_fetch_assoc($templateinfores)){
		 					$companydetailsarray['shop_type']=str_replace(",","|", $templateinforow['template_id']);
						
		 			}
					
				}
				$sqlgetempdetails="SELECT * FROM  online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
				$getempdetailsres = parent::execQuery($sqlgetempdetails, $this->conn_idc);
				if($getempdetailsres && mysql_num_rows($getempdetailsres)>0)
		 		{
		 			while($getempdetailsrow=mysql_fetch_assoc($getempdetailsres)){
		 					$companydetailsarray['employee_code']=$getempdetailsrow['website_request_by'];
		 					if($this->version%10=='1'){

		 						$companydetailsarray['employee_type']='CS';
		 					}
		 					else if($this->version%10=='2'){
		 						$companydetailsarray['employee_type']='TME';
		 					}
		 					else if($this->version%10=='3'){
		 						$companydetailsarray['employee_type']='ME';
		 						$checkjda="select * from payment_apportioning where parentid='".$this->parentid."' and version='".$this->version."' and source=4";
		 						$checkjdares = parent::execQuery($checkjda, $this->conn_finance);
		 						if($checkjdares && mysql_num_rows($checkjdares)>0)
		 						{
		 							$companydetailsarray['employee_type']='JDA';
		 						}
		 					}
						
		 			}
				}
				// 3 me 13 jda
				$getempname="SELECT * FROM login_details.tbl_loginDetails where mktempcode='".$companydetailsarray['employee_code']."'";
				$getempnameres = parent::execQuery($getempname, $this->conn_idc);
				if($getempnameres && mysql_num_rows($getempnameres)>0)
		 		{
		 			while($getempnamerow=mysql_fetch_assoc($getempnameres)){
		 				$companydetailsarray['employee_name']=$getempnamerow['empName'];
		 			}
		 		}

				$tme_detailsql="SELECT tmecode,mecode,tmeName,meName FROM payment_otherdetails WHERE parentid='".$this->parentid."' and version='".$this->version."'";
				$tme_detailres = parent::execQuery($tme_detailsql, $this->conn_finance);
				if($tme_detailres && mysql_num_rows($tme_detailres)>0)
		 		{
		 			while($tme_detailrow=mysql_fetch_assoc($tme_detailres)){
		 				$companydetailsarray['tmeCode']=$tme_detailrow['tmecode'];
		 				$companydetailsarray['meCode']=$tme_detailrow['mecode'];
		 				$companydetailsarray['tmeName']=$tme_detailrow['tmeName'];
		 				$companydetailsarray['meName']=$tme_detailrow['meName'];
		 			}
		 		} 
		 		
				$categories=$companydetailsarray['catidlineage'];
				$categories=str_replace('/', '', $categories);
				$categories=rtrim($categories,",");
				$categories=ltrim($categories,",");
				$categories=explode(',', $categories);
				$categories=array_unique($categories);
				$categories=implode(',',$categories);

				if($companydetailsarray['shop_type']==''){
					//$gettempsql="SELECT GROUP_CONCAT(DISTINCT(template_id) ) AS template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($gettempsql, $this->dbConIro);
					$cat_params = array();
					$cat_params['page'] 		='omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;			
					$cat_params['return']		= 'template_id';

					$where_arr  	=	array();
					$where_arr['catid']			= $categories;			
					$cat_params['where']		= json_encode($where_arr);
					
					if($categories!=''){
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

			 		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr)>0)
			 		{
			 			$templateid_arr = array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){
								$template_id=$cat_arr['template_id'];
								if($template_id!=''){
									$templateid_arr[] = $template_id;
								}
			 			}
			 			$template_id_str = implode(",", $templateid_arr);
						$companydetailsarray['shop_type']=str_replace(",","|", $template_id_str);
					}
				}
				if(trim($companydetailsarray['national_catidlineage'])==''){
					//$sqlnatinfo=" SELECT GROUP_CONCAT('/',national_catid,'/') as national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($sqlnatinfo, $this->dbConIro);
			 		$cat_params = array();
					$cat_params['page'] 		='omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;			
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();
					$where_arr['catid']			= $categories;			
					$cat_params['where']		= json_encode($where_arr);
					if($categories!=''){
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
			 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			 		{
			 			$national_catid_arr = array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){
			 					$national_catid=$cat_arr['national_catid'];
			 					if($national_catid!=''){
			 						$national_catid_arr[] = $national_catid;
			 					}
			 			}
			 			$national_catid_str	=	implode("/,/",$national_catid_arr);
						$companydetailsarray['national_catidlineage']="/".$national_catid_str."/";
					}

				}
				//$templatedetails=$this->getTemplateMapping($companydetailsarray); 
				
				$companydetailsarray['template_name']=$this->template_name;
				$companydetailsarray['template_type']=$this->template_type;
				$companydetailsarray['vertical_id']=$this->vertical_id;
				$companydetailsarray['vertical_name']=$this->vertical_name;
				$companydetailsarray['omni_type']=$this->omni_type;
				$companydetailsarray['demo_url']=$this->demo_url; 
				$omni_type=$this->omni_type; 
				if(strtolower($omni_type)=='services' || strtolower($omni_type)=='service'){
					$omni_type='service';
				}
				else{
					$omni_type='product';
				}
				$companydetailsarray['status']="ACTUAL";
				$companydetailsarray['paid_status']="1"; 
				$companydetailsarray['source']="JUSTDIAL";

				$companydetailsarray['action']="registerJdCustomer";
				$this->omniUrl.="?action=registerJdCustomer";
				$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
				$formysql  = $db['mumbai']['idc']['master'];

				$sql_ins_website = "INSERT INTO omni_api_calls_log set
				 					parentid        = '".$this->parentid."',
				 					version         = '".$this->version."',
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= ''
				 					ON DUPLICATE KEY UPDATE
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= ''";
				$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_idc);

				$res_arr=json_decode($res,1);
				$res_ins_website = false;
				
				if(isset($res_arr['supplierId'])){

				$sql_omni_mapping = "INSERT INTO tbl_omni_mapping set
				 					parentid        = '".$this->parentid."',
				 					version         = '".$this->version."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					added_by  	= '".$this->usercode."',
				 					added_time  	= '".date('Y-m-d H:i:s')."',
				 					updated_by  	= '".$this->usercode."',
				 					updated_time  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_store_id  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					added_by  		= '".$this->usercode."',
				 					added_time  	= '".date('Y-m-d H:i:s')."',
				 					updated_by  	= '".$this->usercode."',
				 					updated_time  	= '".date('Y-m-d H:i:s')."'";
				$res_ins_website = parent::execQuery($sql_omni_mapping, $this->dbConIro);
				 
			  	if(strtolower($omni_type)=='services' || strtolower($omni_type)=='service'){ 
						
						//$this->domainMappingService('',$res_arr['redirectUrl']); 
						$omni_type='service';
					}
					else{
						$omni_type='product';
					} 
					//$this->setOmniDomain();
				 $sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
				 					parentid        = '".$this->parentid."',
				 					data_city       = '".$this->data_city_cm."',
				 					docid  			= '".$companydetailsarray['docid']."',
				 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."', 
				 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
				 					template_id  	= '".$companydetailsarray['shop_type']."',
				 					omni_type  	= '".$omni_type."',
				 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
				 					omni_creation_status  	= 'pass',
				 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
				 					demo_link  	= '0',
				 					vertical_id  	= '".$this->vertical_id."',
				 					template_type  	= '".$this->template_type."', 
				 					template_tag  	= '".$this->mysql_real_escape_custom($this->template_name)."',
				 					approved_date  	= '".date('Y-m-d H:i:s')."'
				 					ON DUPLICATE KEY UPDATE
				 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
				 					docid  			= '".$companydetailsarray['docid']."',
				 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
				 					template_id  	= '".$companydetailsarray['shop_type']."',
				 					omni_type  	= '".$omni_type."',
				 					storeid  	= '".stripslashes(addslashes(($res_arr['storeid'])))."',
				 					supplier_id  	= '".stripslashes(addslashes(($res_arr['supplierId'])))."',
				 					omni_redirecturl  	= '".stripslashes(addslashes(($res_arr['redirectUrl'])))."',
				 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
				 					omni_creation_status  	= 'pass',
				 					omni_created_date  	= '".date('Y-m-d H:i:s')."',
				 					demo_link  	= '0',
				 					vertical_id  	= '".$this->vertical_id."',
				 					template_type  	= '".$this->template_type."',
				 					template_tag  	= '".$this->mysql_real_escape_custom($this->template_name)."',  
				 					approved_date  	= '".date('Y-m-d H:i:s')."'";
				$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
					if(strtolower($omni_type)=='services' || strtolower($omni_type)=='service'){ 
						
						$this->domainMappingService('',$res_arr['redirectUrl']); 
						$omni_type='service';
					}
					else{
						$omni_type='product';
					} 
					$this->setOmniDomain(); 
				}
				if($omni_type=='product'){
					$url=$this->domainomniUrl."?domain=".$website."&did=".$companydetailsarray['docid']."&a_flag=-1";
					$res=$this->curlCall($url,$postdata,'get');
					$sql_ins_websitee = "INSERT INTO online_regis1.tbl_template_mapping_api set
					 					parentid        = '".$this->parentid."',
					 					data_city  	= '".$this->data_city_cm."',
					 					api_params  	= '".($url)."', 
					 					params  	= '".($this->mysql_real_escape_custom($url))."', 
					 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
					 					api_called_time  	= '".date('Y-m-d H:i:s')."',
					 					step=3";
					$res_ins_websitee = parent::execQuery($sql_ins_websitee, $this->conn_log);  
				}
				if($res_ins_website){
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Success";
					$result_msg_arr['data']['redirectUrl']  = $res_arr['redirectUrl'];
					$result_msg_arr['data']['supplierId']   = $res_arr['supplierId'];
					$result_msg_arr['data']['storeid']      = $res_arr['storeid'];
					echo json_encode($result_msg_arr);exit;
				}
				else{
				 $sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					approved_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'fail'
					 					ON DUPLICATE KEY UPDATE
					 					national_catids  	= '".stripslashes(addslashes(($companydetailsarray['national_catidlineage'])))."',
					 					docid  			= '".$companydetailsarray['docid']."',
					 					catids  	= '".stripslashes(addslashes(($companydetailsarray['catidlineage'])))."',
					 					template_id  	= '".$companydetailsarray['shop_type']."',
					 					omni_type  	= '".$omni_type."',
					 					omni_api_called_date  	= '".date('Y-m-d H:i:s')."',
					 					approved_date  	= '".date('Y-m-d H:i:s')."',
					 					omni_creation_status  	= 'fail'";
					$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);

					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error";
					$result_msg_arr['error']['actual_msg'] = $res_arr['msg']; 
					echo json_encode($result_msg_arr);exit; 
				}
	/*	}
		else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Finance Details Missing";
					echo json_encode($result_msg_arr);exit;
		}*/

			
	 }

	 function getOmniTemplateDetails(){
		 
				if($this->mongo_flag == 1 || $this->mongo_tme == 1)
				{
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] 	= "";
					$sqlgeneralinforow = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$sqlgeneralinfo="select * from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
					$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_temp);
					if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
					{
						$sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores);
					}
				}
				foreach ($sqlgeneralinforow as $key => $value) {
					$companydetailsarray[$key]=$value;
				}
		 
	 	
				
				if($this->mongo_flag == 1 || $this->mongo_tme == 1)
				{
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
					$mongo_inputs['fields'] 	= "";
					$sqlextrainforow = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$sqlextrainfo="select * from tbl_companymaster_extradetails_shadow where parentid='".$this->parentid."'";
					$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->conn_temp);
					if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
					{
						$sqlextrainforow=mysql_fetch_assoc($sqlextrainfores);
					}
				}
				$companydetailsarray[]=$sqlextrainforow;
				foreach ($sqlextrainforow as $key => $value) {
					$companydetailsarray[$key]=$value;
				}
					
				
				
					
					if($this->mongo_flag == 1 || $this->mongo_tme == 1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= "tbl_business_temp_data";
						$mongo_inputs['fields'] 	= "catIds";
						$sqltempinforow = $this->mongo_obj->getData($mongo_inputs);
					}
					else
					{
						$sqltempinfo="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
						$sqltempinfores = parent::execQuery($sqltempinfo, $this->conn_temp);
						$num_rows = mysql_num_rows($sqltempinfores);
						if($num_rows>0)
						{
							$sqltempinforow=mysql_fetch_assoc($sqltempinfores);
						}
					}
					$categories=$sqltempinforow['catIds'];
					
				
					
					if($this->mobile!=''){
						$companydetailsarray['mobile']=$this->mobile;
					}
					if($this->email!=''){
						$companydetailsarray['email']=$this->email;
					}
					$companydetailsarray['catidlineage']=str_replace('/', '',$companydetailsarray['catidlineage']);
					
					$categories=str_replace('|P|', ',', $categories);
					$companydetailsarray['catidlineage']=$categories;
					$categories=rtrim($categories,",");
					$categories=ltrim($categories,",");
					$national_catid='';
					$categories=explode(',', $categories);
					$categories=array_unique($categories);
					$categories=implode(',',$categories);
					//$sqlnatinfo=" SELECT GROUP_CONCAT('/',national_catid,'/') as national_catid FROM tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($sqlnatinfo, $this->conn_idc);
					$cat_params = array();
					$cat_params['page']= 'omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();
					if($categories!=''){
						$where_arr['catid']			= $categories;
						$cat_params['where']		= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

			 		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			 		{
			 			$natcat_arr = array();
						foreach($cat_res_arr['results'] as $key =>$cat_arr){			 				
							if($cat_arr['national_catid']!=''){
								$natcat_arr[]= $cat_arr['national_catid'];
							}							
			 			}
						$national_catid	=	implode("/,/", $natcat_arr);
					}
					if(trim($this->national_catid)!='')
						$companydetailsarray['national_catidlineage']=$this->national_catid;
					else
					$companydetailsarray['national_catidlineage']="/".$national_catid."/";
					
					$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;

					
					$ecsinfo="select acNo,ifs from tbl_omni_ecs_details_temp where parentid='".$this->parentid."'";
				 	$ecsinfores = parent::execQuery($ecsinfo, $this->conn_temp);
				 		if($ecsinfores && mysql_num_rows($ecsinfores)>0)
				 		{
				 			while($ecsinforesrow=mysql_fetch_assoc($ecsinfores)){
				 					$companydetailsarray['acNo']=$ecsinforesrow['acNo'];
									$companydetailsarray['ifsc_code']=$ecsinforesrow['ifs'];
								
				 			}
							
						}
						
					
					$templateinfo="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
				 	$templateinfores = parent::execQuery($templateinfo, $this->conn_temp);
			 		if($templateinfores && mysql_num_rows($templateinfores)>0)
			 		{
			 			while($templateinforow=mysql_fetch_assoc($templateinfores)){
			 					$companydetailsarray['shop_type']=str_replace(",","|", $templateinforow['template_id']);
							
			 			}
						
					}
					$companydetailsarray['employee_code']=$this->usercode; 
					if($this->version%10=='1'){ 

						$companydetailsarray['employee_type']='CS';
					}
					else if($this->version%10=='2'){
						$companydetailsarray['employee_type']='TME';
					}
					else if($this->version%10=='3'){
						$getempname="SELECT * FROM login_details.tbl_loginDetails where mktempcode='".$this->usercode."' and emptype='13'";
						$getempnameres = parent::execQuery($getempname, $this->conn_idc);
						if($getempnameres && mysql_num_rows($getempnameres)>0)
				 		{
				 			$companydetailsarray['employee_type']='JDA';
				 		}
				 		else{
				 			$companydetailsarray['employee_type']='ME';
				 		}
						
					}
					$companydetailsarray['employee_name']=$this->username;

					$templatedetails=$this->getTemplateMapping($companydetailsarray);
					
					$companydetailsarray['template_name']=$templatedetails['ts']['tnm'];
					$companydetailsarray['template_type']=$templatedetails['ts']['ttyp'];
					$companydetailsarray['vertical_id']=$templatedetails['ts']['vid'];
					$companydetailsarray['vertical_name']=$templatedetails['ts']['vnm'];
					$companydetailsarray['omni_type']=$templatedetails['ts']['omnityp'];
					$companydetailsarray['demo_url']=$templatedetails['ts']['demo_url'];
					$omni_type=$templatedetails['ts']['omnityp'];
					if(strtolower($omni_type)=='services'){
						if($templatedetails['ts']['active_flag']=='0' || $templatedetails['ts']['active_flag']==0)
						{
							/*$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = "Demo store for your category is not available at this time.";
							echo json_encode($result_msg_arr);exit;*/
						}
					}
					$companydetailsarray['status']="GENIODEMO";
	 	$templatedetails=$this->getTemplateMapping($companydetailsarray);
	 	$template_name=$templatedetails['ts']['tnm'];
	 	$template_type=$templatedetails['ts']['ttyp'];
	 	$vertical_id=$templatedetails['ts']['vid'];
	 	$vertical_name=$templatedetails['ts']['vnm'];
	 	$omni_type=$templatedetails['ts']['omnityp'];
	 	$shop_type='';
	 	if(strtolower($template_name)=='grocery' ){
	 		$shop_type='grocery';

	 	}
	 	else{
	 		$shop_type='other';
	 	}
	 	$companydetailsarray['shop_type']=$shop_type;
	 	$companydetailsarray['action']='getUIDetails';
	 	$companydetailsarray['from']='genio';
	 	$companydetailsarray['type']='mobile';
	 	
	 	$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
	 	$sql_log = "INSERT INTO omni_api_calls_log set
				 					parentid        = '".$this->parentid."',
				 					version         = '".$this->version."',
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= 'templateapi'
				 					ON DUPLICATE KEY UPDATE
				 					api_called  	= '".$this->omniUrl."',
				 					api_parameter  	= '".$this->mysql_real_escape_custom(json_encode($companydetailsarray))."',
				 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
				 					called_time  	= '".date('Y-m-d H:i:s')."',
				 					error_text  	= 'templateapi'";
		$res_log = parent::execQuery($sql_log, $this->conn_idc);
	 	$res_arr=json_decode($res,1);
	 	if(is_array($res_arr)){

	 		$result_msg_arr['error']['code'] = 0;
	 		$result_msg_arr['error']['msg'] = "Result Found!";
	 		$result_msg_arr['results']['data'] = ($res_arr); 
	 		$result_msg_arr['results']['omni_type'] = $omni_type;  
	 		echo json_encode($result_msg_arr);
	 		exit;
	 		
	 	}
	 	else{
	 		$result_msg_arr['error']['code'] =1;
	 		$result_msg_arr['error']['msg'] = "No Details Found";
	 		echo json_encode($result_msg_arr);
	 		exit;
	 	}
	 	


	 }

	 function saveOmniAppTemplateDetails(){

	 	 if(trim($this->app_template_id)==''){
	 	 	$result_msg_arr['error']['code'] = 1;
	 	 	$result_msg_arr['error']['msg'] = "App template Type missing";
	 	 	echo json_encode($result_msg_arr);exit;
	 	 }
	 	 if(trim($this->app_template_name)==''){
	 	 	$result_msg_arr['error']['code'] = 1;
	 	 	$result_msg_arr['error']['msg'] = "App template name missing";
	 	 	echo json_encode($result_msg_arr);exit;
	 	 }
 		 $savetempsql = "INSERT INTO tbl_omni_extradetails_temp set
	 					parentid='".$this->parentid."',
	 					app_template_id = '".$this->app_template_id."',
	 					app_template_name = '".$this->app_template_name."'
	 					ON DUPLICATE KEY UPDATE
	 					app_template_id = '".$this->app_template_id."',
	 					app_template_name = '".$this->app_template_name."'";
		$restempsql = parent::execQuery($savetempsql, $this->conn_temp);  
		if($restempsql){
			$result_msg_arr['error']['code'] =0;
	 		$result_msg_arr['error']['msg'] = "Success";
	 		echo json_encode($result_msg_arr); 
		}
		else{
			$result_msg_arr['error']['code'] =1;
			$result_msg_arr['error']['msg'] = "Error";
			echo json_encode($result_msg_arr);
		}
	 }

	 function domainMappingFix($companydata=null,$website=null){

	 	$active_flag=1;
	 	$website=$this->domainmapping_website; 
	 	if(!is_array($companydata)){
	 		 	$sqlgeneralinfo="select * from tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
	 		 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->dbConIro);
	 		 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
	 		 		{
	 		 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
	 		 				foreach ($sqlgeneralinforow as $key => $value) {
	 							$companydata[$key]=$value;
	 						}
	 		 			}
	 					
	 				}
	 				
	 			$sqlextrainfo="select * from tbl_companymaster_extradetails where parentid='".$this->parentid."'";
	 		 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->dbConIro);
	 		 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
	 		 		{
	 		 			
	 		 			
	 					while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
	 		 				$companydetailsarray[]=$sqlextrainforow;
	 		 				foreach ($sqlextrainforow as $key => $value) {
	 							$companydata[$key]=$value;
	 						}
	 		 			}
	 					
	 				}
	 				
	 			$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
	 			$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
	 				if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
	 				{
	 					
	 					
	 				while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
	 						$docid=$sqldocidinforow['docid'];
	 						
	 					}
	 				
	 			}
	 			 $sqlomnimapping="select * from tbl_omni_mapping where parentid='".$this->parentid."' and omni_store_id<>'' ";
	 			$sqlomnimappingres = parent::execQuery($sqlomnimapping, $this->dbConIro);
	 				if($sqlomnimappingres && mysql_num_rows($sqlomnimappingres)>0)
	 				{
	 					
	 					
	 				while($sqlomnimappingrow=mysql_fetch_assoc($sqlomnimappingres)){
	 						$omni_redirecturl=$sqlomnimappingrow['omni_redirecturl'];
	 						
	 					}
	 				
	 			}
	 			$companydata['docid']=$docid;
	 			if($this->action=='19' || $this->action==19 ||$this->action=='18' || $this->action==18){
	 				$templatedetails['ts']['ttyp']=$this->template_type;
	 				$templatedetails['ts']['vid']=$this->vertical_id; 
	 			}
	 			else
	 			$templatedetails=$this->getTemplateMapping($companydata);
	 			$omni_redirecturl=$this->clean_http($omni_redirecturl);
	 			$data['cname']				= $companydata['companyname'];
	 			$data['email_d']			= $companydata['email'];
	 			$data['mobile_d']			= $companydata['dialable_mobile'];
	 			$data['landline_d']			= $companydata['dialable_landline'];
	 			$data['tollfree_d']			= $companydata['tollfree'];
	 			$data['fax']				= $companydata['fax'];
	 			$data['address']			= $companydata['full_address'];
	 			$data['cat_list']			= $this->getIroLikeString(2); 
	 			$data['cp']					= $companydata['contact_person'];
	 			$data['web']				= $companydata['website'];
	 			$data['lat']				= $companydata['latitude'];
	 			$data['long']				= $companydata['longitude'];
	 			$data['geo_a']				= $companydata['geocode_accuracy_level'];
	 			$data['area']				= $companydata['area'];
	 			$data['city']				= $companydata['city'];
	 			$data['data_city']			= $companydata['data_city']; 
	 			$data['t_type']				= $templatedetails['ts']['ttyp'];
	 			$data['vertical'] 			= $templatedetails['ts']['vid'];
	 			$data['m_flg']				= "1";
	 			$data['updatedby']			= $this->usercode;
	 			$data['omni_d_name']		= $omni_redirecturl;// always omni_redirecturl
	 	}
	 	else{
	 		$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
	 		$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
	 			if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
	 			{
	 				
	 				
	 			while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
	 					$docid=$sqldocidinforow['docid'];
	 					
	 				}
	 			}
	 		$companydata['docid']=$docid;
	 		
	 		$templatedetails=$this->getTemplateMapping($companydata);
	 		$omni_redirecturl=$this->clean_http($website);// as here redirect url comes
	 		$data['cname']				= $companydata['companyname'];
	 		$data['email_d']			= $companydata['email'];
	 		$data['mobile_d']			= $companydata['dialable_mobile'];
	 		$data['landline_d']			= $companydata['dialable_landline'];
	 		$data['tollfree_d']			= $companydata['tollfree'];
	 		$data['fax']				= $companydata['fax'];
	 		$data['address']			= $companydata['full_address'];
	 		$data['cat_list']			= $this->getIroLikeString(1); 
	 		$data['cp']					= $companydata['contact_person'];
	 		$data['web']				= $companydata['website'];
	 		$data['lat']				= $companydata['latitude'];
	 		$data['long']				= $companydata['longitude'];
	 		$data['geo_a']				= $companydata['geocode_accuracy_level'];
	 		$data['area']				= $companydata['area'];
	 		$data['city']				= $companydata['city'];
	 		$data['data_city']			= $companydata['data_city']; 
	 		$data['t_type']				= $templatedetails['ts']['ttyp'];
	 		$data['vertical'] 			= $templatedetails['ts']['vid'];
	 		$data['m_flg']				= "1";
	 		$data['updatedby']			= $this->usercode;
	 		$data['omni_d_name']		= $omni_redirecturl; // always omni_redirecturl
	 	}
	 	
	 	

	 	//$url=$this->domainomniUrl."?domain=".$website."&did=".$companydata['docid']."&data=".rawurlencode((json_encode($data)))."";
	 	$url=$this->domainomniUrl;
	 	$website=$this->clean_http($website); 
	 	$postdataarr['domain']=$website;
	 	
	 	$postdataarr['did']=$companydata['docid'];
	 	$postdataarr['data']=((json_encode($data)));

	 	$res=$this->curlCall($url,$postdataarr,'post');
	 	$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
	 	 					parentid        = '".$this->parentid."',
	 	 					data_city  	= '".$this->data_city_cm."',
	 	 					api_params  	= '".($url)."', 
	 	 					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
	 	 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
	 	 					api_called_time  	= '".date('Y-m-d H:i:s')."',
	 	 					step=2";
	 	$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
	 	$res_arr=json_decode($res,1);
	 	if($res_arr['error']['code']=='0'){
	 		$url=$this->domainomniUrl."?domain=".$website."&did=".$companydata['docid']."&a_flag=".$templatedetails['ts']['active_flag']; 
	 		$res=$this->curlCall($url,$postdata,'get');
	 		$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
	 		 					parentid        = '".$this->parentid."',
	 		 					data_city  	= '".$this->data_city_cm."',
	 		 					api_params  	= '".($url)."', 
	 		 					params  	= '".($this->mysql_real_escape_custom($url))."', 
	 		 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
	 		 					api_called_time  	= '".date('Y-m-d H:i:s')."',
	 		 					step=3";
	 		$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
	 		$result_msg_arr['error']['code'] = 0;
	 		$result_msg_arr['error']['msg'] = 'Success';
	 		//return json_encode($result_msg_arr);
	 		
			if(count($this->genio_lite_campaign_info)>0)
				return $result_msg_arr;
			else
				return json_encode($result_msg_arr);
				
	 	}
	 	else{ 
	 		$result_msg_arr['error']['code'] = 1;
	 		$result_msg_arr['error']['msg'] = 'Error';
	 		$sql_ins_website = "INSERT INTO online_regis1.tbl_template_mapping_api set
	 		 					parentid        = '".$this->parentid."',
	 		 					data_city  	= '".$this->data_city_cm."',
	 		 					api_params  	= '".$this->mysql_real_escape_custom($url)."',
	 		 					params  	= '".($this->mysql_real_escape_custom(json_encode($postdataarr)))."', 
	 		 					api_result  	= '".$this->mysql_real_escape_custom($res)."',
	 		 					api_called_time  	= '".date('Y-m-d H:i:s')."',
	 		 					step=2";
	 		$res_ins_website = parent::execQuery($sql_ins_website, $this->conn_log);
	 		
 			//return json_encode($result_msg_arr);
 			
 			//echo '<br> 4238 <pre>';print_r($result_msg_arr);
 			
 			if(count($this->genio_lite_campaign_info)>0)
				return $result_msg_arr;
			else
				return json_encode($result_msg_arr);
				
	 	}

	 	
	 }

	 function setIosFeesToOmni(){

	 		$sqlfinance="select sum(app_amount) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='75' and version='".$this->version."' group by parentid";
			$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
			$app_amount=0;
			if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0)
	 		{
		 		while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres))
					{
						$app_amount=$sqlfinancerow['app_amount'];
					}
				
				$selgetiosfees="select * from online_regis1.omni_add_ons_pricing where campaignid='75' and camp_type='1'";
			      $selgetiosfeesres = parent::execQuery($selgetiosfees, $this->conn_idc);
			      if($priceres && mysql_num_rows($priceres)>0){ 
				      	while($rowprice=mysql_fetch_assoc($priceres)){
				      		$price=$rowprice['price_upfront'];				      		
				      	}	
			      }
			       $sqldocid= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			      $resdocid = parent::execQuery($sqldocid, $this->dbConIro);

			      if($resdocid && mysql_num_rows($resdocid) )
			      {
			      		$rowdocid= mysql_fetch_assoc($resdocid);
			      		$docid = $rowdocid['docid'];
			      } 

			      if($price<=$app_amount){
		      		 	$companydetailsarray['action']="IOSAppStatus";
		      		 	$companydetailsarray['flag']=1;
		      		 	$companydetailsarray['docid']=$docid;
		      		 	$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
		      	 		$sql_ins_website = "INSERT INTO omni_api_calls_log set
		      			 					parentid        = '".$this->parentid."',
		      			 					version         = '".$this->version."',
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'iosapp'
		      			 					ON DUPLICATE KEY UPDATE
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'iosapp'";
		      			$res_ins_websitel = parent::execQuery($sql_ins_website, $this->conn_idc);
		      			
		      			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
		      				 					parentid        = '".$this->parentid."',
		      				 					data_city       = '".$this->data_city_cm."',
		      				 					ios_app_applied  	= '1'
		      				 					ON DUPLICATE KEY UPDATE
		      				 					ios_app_applied  	= '1'";
		      			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
		      		if($res_ins_websitel){
		      			$result_msg_arr['error']['code'] = 0;
		      			$result_msg_arr['error']['msg'] = 'Success';
		      			return json_encode($result_msg_arr);
		      		}
		      		else{
		      			$result_msg_arr['error']['code'] = 1;
		      			$result_msg_arr['error']['msg'] = 'Error';
		      			return json_encode($result_msg_arr);
		      		}
			     }
			     else{
			     	mail ('rajakkal.ganesh@justdial.com' , 'Ios App Creation Fee Mismatch '.$this->parentid,$price.'< price--->actual money got'.$app_amount);
			     	$result_msg_arr['error']['code'] = 1;
			     	$result_msg_arr['error']['msg'] = 'Error';
			     	return json_encode($result_msg_arr); 

			     }

			}
			else{
				
				/* no action need currently*/
			}

	 }
	 function setEmailIdForOmni(){
	 	$emailids=$this->email; 
	 	
	 	$email_shadow='';
	 	$email_upt='';
 		$email_arr=explode(',', $emailids);
 	 	foreach ($email_arr as $key => $email) {
 		 	if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
 		 	  	$email_upt=",".$email;

 		 	} else {
 		 	  
 		 	  	$result_msg_arr['error']['code'] = 1;
 		 	  	$result_msg_arr['error']['msg'] = 'Please Enter Valid Email Id';
 		 	  	echo json_encode($result_msg_arr); exit;
 		 	}
 	 	}
 	 	
 	 	if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "";
			$sqlgeninforow = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqlselemail="select email from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$sqlgeninfores = parent::execQuery($sqlselemail, $this->conn_temp);
			if($sqlgeninfores && mysql_num_rows($sqlgeninfores)>0)
			{
				$sqlgeninforow=mysql_fetch_assoc($sqlgeninfores);
			}
		}
		$email_shadow=$sqlgeninforow['email'];
		
		$email_shadow_arr=explode(',', $email_shadow);
		$updtarr=array_unique(array_merge($email_arr, $email_shadow_arr)); 
		$updtemail=implode(',', $updtarr);
		$updtemail=trim($updtemail,",");
		
		$updtres=false;
		if(trim($updtemail)!=''){
			//mongo query
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt = array();
				$geninfo_upt['email'] = $updtemail;
				$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
				
				$mongo_inputs['table_data'] = $mongo_data;
				$updtres = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$updt="update tbl_companymaster_generalinfo_shadow set email='".$updtemail."' where parentid='".$this->parentid."'";
				$updt = $updt."/* TMEMONGOQRY */";
				$updtres = parent::execQuery($updt, $this->conn_temp);
			}
		}
		if($updtres){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			echo json_encode($result_msg_arr); exit;
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Error';
			echo json_encode($result_msg_arr); exit;
		}
				
	 }
	 function setSmsFeesToOmni(){

	 		 $sqlfinance="select sum(app_amount) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='83' and version='".$this->version."' group by parentid";
			$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
			$app_amount=0;
			if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0)
	 		{
		 		while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres))
					{
						$app_amount=$sqlfinancerow['app_amount'];
					}

				$selgetiosfees="select * from online_regis1.omni_add_ons_pricing where campaignid='83' and camp_type='1'";
			      $selgetiosfeesres = parent::execQuery($selgetiosfees, $this->conn_idc);
			      if($priceres && mysql_num_rows($priceres)>0){ 
				      	while($rowprice=mysql_fetch_assoc($priceres)){
				      		$price=$rowprice['price_upfront'];				      		
				      	}	
			      }

				
				$selnosms="select * from tbl_omni_sms_details where parentid='".$this->parentid."' and version='".$this->version."' and approved=0";
			      $selnosmsres = parent::execQuery($selnosms, $this->conn_idc);
			      if($selnosmsres && mysql_num_rows($selnosmsres)>0){ 
				      	while($rownosmsres=mysql_fetch_assoc($selnosmsres)){
				      		$num_of_sms=$rownosmsres['num_of_sms'];				      		
				      	}	
			      }
			      else
			      {
			      	$result_msg_arr['error']['code'] = 1;
			      	$result_msg_arr['error']['msg'] = 'Already Approved/ No Data Present!'; 
			      	return json_encode($result_msg_arr);
			      }


			      $price=($price * $num_of_sms);
			       
			      $sqldocid= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			      $resdocid = parent::execQuery($sqldocid, $this->dbConIro);

			      if($resdocid && mysql_num_rows($resdocid) )
			      {
			      		$rowdocid= mysql_fetch_assoc($resdocid);
			      		$docid = $rowdocid['docid'];
			      }
			      
			      if($price<=$app_amount){
		      		 	$companydetailsarray['action']="addSMSCredits";
		      		 	$companydetailsarray['smscredits']=$num_of_sms; 
		      		 	$companydetailsarray['docid']=$docid;
		      		 	$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
		      	 		$sql_ins_website = "INSERT INTO omni_api_calls_log set
		      			 					parentid        = '".$this->parentid."',
		      			 					version         = '".$this->version."',
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'sms'
		      			 					ON DUPLICATE KEY UPDATE
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'sms'";
		      			$res_ins_websitel = parent::execQuery($sql_ins_website, $this->conn_idc);
		      			
		      			$res_arr=json_decode($res,1);
		      			
		      			if((int)$res_arr['isSuccess']==1){
		      				$updt="update tbl_omni_sms_details set approved=1 where parentid='".$this->parentid."' and version='".$this->version."'"; 
		      				$res_ins_website = parent::execQuery($updt, $this->conn_idc);
		      			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
		      				 					parentid        = '".$this->parentid."',
		      				 					data_city       = '".$this->data_city_cm."',
		      				 					sms_taken='yes',
		      				 					sms_type='jd',
		      				 					sms_no_taken='".$num_of_sms."',
		      				 					sms_approveddate='".date('Y-m-d H:i:s')."',
		      				 					sms_creation_status='pass'
		      				 					ON DUPLICATE KEY UPDATE
		      				 					sms_taken='yes',
		      				 					sms_type='jd',
		      				 					sms_no_taken='".$num_of_sms."',
		      				 					sms_approveddate='".date('Y-m-d H:i:s')."',
		      				 					sms_creation_status='pass'";
		      			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
		      			if($res_ins_website){
		      			$result_msg_arr['error']['code'] = 0;
		      			$result_msg_arr['error']['msg'] = 'Success';
		      			return json_encode($result_msg_arr);
		      			}

		      		}
		      		else{
			      			
		      				
			      			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
			      				 					parentid        = '".$this->parentid."',
			      				 					data_city       = '".$this->data_city_cm."',
			      				 					sms_taken='yes',
			      				 					sms_type='jd',
			      				 					sms_no_taken='".$num_of_sms."',
			      				 					sms_creation_status='fail'
			      				 					ON DUPLICATE KEY UPDATE 
			      				 					sms_taken='yes',
			      				 					sms_type='jd',
			      				 					sms_no_taken='".$num_of_sms."',
			      				 					sms_creation_status='fail'";
			      			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
			      			if($res_ins_website){
			      			$result_msg_arr['error']['code'] = 1;
			      			$result_msg_arr['error']['msg'] = 'Some Error Occured';
			      			return json_encode($result_msg_arr);
		      			}
		      		}

		      		
		      		
			     }
			     else{
			     	mail ('rajakkal.ganesh@justdial.com' , 'Sms Creation Fee Mismatch '.$this->parentid,$price.'< price--->actual money got'.$app_amount);
			     	$result_msg_arr['error']['code'] = 1;
			     	$result_msg_arr['error']['msg'] = 'Error';
			     	return json_encode($result_msg_arr); 

			     }

			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = 'No Finance Data';
				return json_encode($result_msg_arr); 

				/* no action need currently*/

			}

	 }
	 
	 function setSSLFeesToOmni(){
	 		//~ $sqlfinance="select sum(app_amount) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='86' and version='".$this->version."' group by parentid";
			//~ $sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
			$app_amount=0;
			//~ if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0){ echo '====ssl 12====';
		 		//~ while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres)){
						//~ $app_amount=$sqlfinancerow['app_amount'];
				//~ }
				$selnosms="select * from tbl_omni_ssl_details where parentid='".$this->parentid."' and version='".$this->version."' and approved=0";
			    $selnosmsres = parent::execQuery($selnosms, $this->conn_idc);
			    if($selnosmsres && mysql_num_rows($selnosmsres)>0){ 
					while($rownosmsres=mysql_fetch_assoc($selnosmsres)){
						$payment_amount_price=$rownosmsres['payment_amount'];				      		
					}
			    }else{
			      	$result_msg_arr['error']['code'] = 1;
			      	$result_msg_arr['error']['msg'] = 'Already Approved/ No Data Present!'; 
			      	return json_encode($result_msg_arr);
			    }
			      $sqldocid= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			      $resdocid = parent::execQuery($sqldocid, $this->dbConIro);
			      if($resdocid && mysql_num_rows($resdocid) )
			      {
			      		$rowdocid= mysql_fetch_assoc($resdocid);
			      		$docid = $rowdocid['docid'];
			      }
			      //~ if($payment_amount_price<=$app_amount){ echo '====ssl 9898989====';
		      		 	$companydetailsarray['action']			=	"updateStoreStatus";
		      		 	$companydetailsarray['active_status']	=	1; 
		      		 	$companydetailsarray['set_docid']			=	$docid;
		      		 	$companydetailsarray['campaignId']			=	'86';
		      		 	//http://www.jdomni.com/marketplace/static/php/web/common_api.php?action=updateStoreStatus&active_status=1&set_docid=022PXX22.XX22.160307144850.Y6N8&campaignId=86 
		      		 	$res=$this->curlCall($this->omniUrl,$companydetailsarray,'json');
		      	 		$sql_ins_website = "INSERT INTO omni_api_calls_log set
		      			 					parentid        = '".$this->parentid."',
		      			 					version         = '".$this->version."',
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'ssl'
		      			 					ON DUPLICATE KEY UPDATE
		      			 					api_called  	= '".$this->omniUrl."',
		      			 					api_parameter  	= '".stripslashes(addslashes(json_encode($companydetailsarray)))."',
		      			 					api_result  	= '".stripslashes(addslashes(json_encode($res)))."',
		      			 					called_time  	= '".date('Y-m-d H:i:s')."',
		      			 					error_text  	= 'ssl'";
		      			$res_ins_websitel = parent::execQuery($sql_ins_website, $this->conn_idc);
		      			$res_arr=json_decode($res,1);
					if((int)$res_arr['isSuccess']==1){
						 
		      				$updt_temp ="update tbl_omni_ssl_details_temp set approved=1,approved_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'"; 
		      				$res_ins_website_temp = parent::execQuery($updt_temp, $this->conn_temp);
		      				
		      				$updt="update tbl_omni_ssl_details set approved=1,approved_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'"; 
		      				$res_ins_website = parent::execQuery($updt, $this->conn_idc);
		      				
		      			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
		      				 					parentid        = '".$this->parentid."',
		      				 					data_city       = '".$this->data_city_cm."',
		      				 					ssl_taken='yes',
		      				 					ssl_val='".$payment_amount_price."',
		      				 					ssl_approved_date='".date('Y-m-d H:i:s')."',
		      				 					ssl_creation_status='pass'
		      				 					ON DUPLICATE KEY UPDATE
		      				 					ssl_taken='yes',
		      				 					ssl_val='".$payment_amount_price."',
		      				 					ssl_approved_date='".date('Y-m-d H:i:s')."',
		      				 					ssl_creation_status='pass'";
		      			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
		      			if($res_ins_website){
		      			$result_msg_arr['error']['code'] = 0;
		      			$result_msg_arr['error']['msg'] = 'Success';
		      			return json_encode($result_msg_arr);
		      			}
		      		}else{
			      			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
			      				 					parentid        = '".$this->parentid."',
			      				 					data_city       = '".$this->data_city_cm."',
			      				 					ssl_taken='yes',
			      				 					ssl_val='".$payment_amount_price."',
			      				 					ssl_creation_status='fail'
			      				 					ON DUPLICATE KEY UPDATE 
			      				 					ssl_taken='yes',
			      				 					ssl_val='".$payment_amount_price."',
			      				 					ssl_creation_status='fail'";
			      			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);
			      			if($res_ins_website){
			      			$result_msg_arr['error']['code'] = 1;
			      			$result_msg_arr['error']['msg'] = 'Some Error Occured';
			      			return json_encode($result_msg_arr);
		      			}
		      		}
			     //~ }else{ echo '====ssl 88888====';
			     	//~ mail ('rajakkal.ganesh@justdial.com' , 'Sms Creation Fee Mismatch '.$this->parentid,$price.'< price--->actual money got'.$app_amount);
			     	//~ $result_msg_arr['error']['code'] = 1;
			     	//~ $result_msg_arr['error']['msg'] = 'Error';
			     	//~ return json_encode($result_msg_arr); 
			     //~ }
			//~ }
			//~ else{ echo '====ssl 00====';
				//~ $result_msg_arr['error']['code'] = 1;
				//~ $result_msg_arr['error']['msg'] = 'No Finance Data';
				//~ return json_encode($result_msg_arr); 
			//~ }
	 }
	 
	 function priceChartTemplate(){
	 	 		$this->conn_demo=$this->conn_temp;
	 			$general='tbl_companymaster_generalinfo_shadow';
	 			$extra='tbl_companymaster_extradetails_shadow';
	 			if($this->website_demo==1 ||$this->website_demo=='1'){
	 				if($this->demo_temp_type=='')
	 				{
	 					$result_msg_arr['error']['code'] = 1;
	 					$result_msg_arr['error']['msg'] = "demo_temp_type Missing";
	 					echo json_encode($result_msg_arr);exit;
	 				}
	 				$this->conn_demo=$this->dbConIro;
	 				$general='tbl_companymaster_generalinfo';
	 				$extra='tbl_companymaster_extradetails';
	 				
	 				$sqlgeneralinfo="select * from ".$general." where parentid='".$this->parentid."'";
			 	$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_demo);
			 		if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
			 		{
			 			while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
			 				foreach ($sqlgeneralinforow as $key => $value) {
								$companydetailsarray[$key]=$value;
							}
			 			}
						
					}

				if (ctype_space($companydetailsarray['contact_person'])) {
					$companydetailsarray['contact_person']='';
					}
				$sqlextrainfo="select * from ".$extra." where parentid='".$this->parentid."'";
			 	$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->conn_demo);
			 		if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
			 		{
			 			
			 			
						while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
			 				$companydetailsarray[]=$sqlextrainforow;
			 				foreach ($sqlextrainforow as $key => $value) {
								$companydetailsarray[$key]=$value;
							}
			 			}
						
					}

	 			}
	 			else
	 			{
					if($this->mongo_flag==1 || $this->mongo_tme == 1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= $general;
						$mongo_inputs['fields'] 	= "";
						$sqlgeneralinforow = $this->mongo_obj->getData($mongo_inputs);
						foreach ($sqlgeneralinforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
						
						if(ctype_space($companydetailsarray['contact_person'])) {
							$companydetailsarray['contact_person']='';
						}
						
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= $extra;
						$mongo_inputs['fields'] 	= "";
						$sqlextrainforow = $this->mongo_obj->getData($mongo_inputs);
						foreach ($sqlextrainforow as $key => $value) {
							$companydetailsarray[$key]=$value;
						}
					}
					else
					{
						$sqlgeneralinfo="select * from ".$general." where parentid='".$this->parentid."'";
						$sqlgeneralinfores = parent::execQuery($sqlgeneralinfo, $this->conn_demo);
						if($sqlgeneralinfores && mysql_num_rows($sqlgeneralinfores)>0)
						{
							while($sqlgeneralinforow=mysql_fetch_assoc($sqlgeneralinfores)){
								foreach ($sqlgeneralinforow as $key => $value) {
									$companydetailsarray[$key]=$value;
								}
							}
							
						}

						if (ctype_space($companydetailsarray['contact_person'])) {
							$companydetailsarray['contact_person']='';
						}
						
						$sqlextrainfo="select * from ".$extra." where parentid='".$this->parentid."'";
						$sqlextrainfores = parent::execQuery($sqlextrainfo, $this->conn_demo);
						if($sqlextrainfores && mysql_num_rows($sqlextrainfores)>0)
						{
							
							
							while($sqlextrainforow=mysql_fetch_assoc($sqlextrainfores)){
								$companydetailsarray[]=$sqlextrainforow;
								foreach ($sqlextrainforow as $key => $value) {
									$companydetailsarray[$key]=$value;
								}
							}
							
						}
					}
				}

		 	 	
					
								
					if($this->mongo_flag == 1 || $this->mongo_tme == 1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_inputs['table'] 		= "tbl_business_temp_data";
						$mongo_inputs['fields'] 	= "catIds";
						$sqltempinforow = $this->mongo_obj->getData($mongo_inputs);
					}
					else
					{
						$sqltempinfo="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
						$sqltempinfores = parent::execQuery($sqltempinfo, $this->conn_demo);
						$num_rows = mysql_num_rows($sqltempinfores);
						if($num_rows>0)
						{
							$sqltempinforow=mysql_fetch_assoc($sqltempinfores);
						}
					}
					$categories=$sqltempinforow['catIds'];
					
					
			
					if($this->mobile!=''){
						$companydetailsarray['mobile']=$this->mobile;
					}
					if($this->email!=''){
						$companydetailsarray['email']=$this->email;
					}
					$companydetailsarray['catidlineage']=str_replace('/', '',$companydetailsarray['catidlineage']);
					
					$categories=str_replace('|P|', ',', $categories);
					
					if($this->website_demo!=1 ||$this->website_demo!='1'){
						$companydetailsarray['catidlineage']=$categories;
					}
					else{
						$categories=$companydetailsarray['catidlineage'];
					}

					$categories=rtrim($categories,",");
					$categories=ltrim($categories,",");
					$national_catid='';
					$categories=explode(',', $categories);
					$categories=array_unique($categories);
					$categories=implode(',',$categories);
					//$sqlnatinfo=" SELECT GROUP_CONCAT('/',national_catid,'/') as national_catid FROM tbl_categorymaster_generalinfo WHERE catid in ($categories);";
			 		//$sqlnatinfores = parent::execQuery($sqlnatinfo, $this->conn_idc);
					$cat_params = array();
					$cat_params['page'] 		='omniDetailsClass';
					$cat_params['data_city'] 	= $this->data_city;			
					$cat_params['return']		= 'national_catid';

					$where_arr  	=	array();
					$where_arr['catid']			= $categories;			
					$cat_params['where']		= json_encode($where_arr);
					if($categories!=''){						
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					}
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					$national_catid_arr = array();
			 		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			 		{
						foreach($cat_res_arr['results'] as $key =>$cat_arr){		 				
								$national_catid=$cat_arr['national_catid'];
								if($national_catid!=''){
									$national_catid_arr[] = $national_catid;
								}
			 			}						
					}
					$national_catid_str =	implode("/,/",$national_catid_arr);;
					if(trim($this->national_catid)!='')
						$companydetailsarray['national_catidlineage']=$this->national_catid;
					else
					$companydetailsarray['national_catidlineage']="/".$national_catid_str."/";
					
					$sqldocidinfo="select * from tbl_id_generator where parentid='".$this->parentid."'";
					$sqldocidinfores = parent::execQuery($sqldocidinfo, $this->dbConIro);
						if($sqldocidinfores && mysql_num_rows($sqldocidinfores)>0)
						{
							
							
						while($sqldocidinforow=mysql_fetch_assoc($sqldocidinfores)){
								$docid=$sqldocidinforow['docid'];
								
							}
						
					}
					$companydetailsarray['docid']=$docid;
									
					$templateinfo="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
				 	$templateinfores = parent::execQuery($templateinfo, $this->conn_temp);
			 		if($templateinfores && mysql_num_rows($templateinfores)>0)
			 		{
			 			while($templateinforow=mysql_fetch_assoc($templateinfores)){
			 					$companydetailsarray['shop_type']=str_replace(",","|", $templateinforow['template_id']);
							
			 			}
						
					}

					$this->from_price=1;
					$templatedetails=$this->getTemplateMapping($companydetailsarray);

					return $templatedetails;

	 }
	 
	function omniDealCloseDemoApi($genio_lite_campaign = null,$newOmni = null){
		$this->dealcloseflow =1;
		$dependend=false;
		$checkdept=$this->checkOmniDependent(0,2);
		$getBFormDetails	=	json_decode($this->getBformWebsiteDetails(),1);
		if($getBFormDetails['error']['code']	==	0){
			$this->mobile		=	$getBFormDetails['error']['result']['mobile'];	
			$this->email	=	$getBFormDetails['error']['result']['email'];	
		}
		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){
			$dependend=true;
		}
	 	$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid in ('72','73','74') and recalculate_flag=1";
		$checktempres = parent::execQuery($checktemp, $this->conn_temp);
		if( mysql_num_rows($checktempres)>0 ||$dependend || (count($genio_lite_campaign)> 0 && (array_key_exists("72",$genio_lite_campaign) || array_key_exists("73",$genio_lite_campaign) || array_key_exists("74",$genio_lite_campaign)))){
			$websiteDetails="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
			$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_temp);
			if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0){
				$res	=	json_decode($this->transferToOmniDemo($genio_lite_campaign),1);
				if(count($genio_lite_campaign)>0)
				{
					return $res;
				}
				//$insertomni	=	json_decode($this->insertDemoLinkDetails(),1);	
			}
			else if($newOmni == 1)
			{
				$res	=	json_decode($this->transferToOmniDemo($genio_lite_campaign),1);
				if(count($genio_lite_campaign)>0)
				{
					return $res;
				}
			}
		}else{
			
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Omni Finance Details";
			if(count($genio_lite_campaign)>0)
			{
				return $result_msg_arr;
			}
			else
			{
			echo json_encode($result_msg_arr);exit;
			}
		}
	}

}	
?>
