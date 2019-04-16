<?php 
ini_set("memory_limit", "-1");
set_time_limit(0);
class generate_invoice_class extends DB {
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	const CURRENT_SERVICE_TAX = '0.15';
    const CURRENT_GST = '0.18';
	function __construct($params)
	{
		$this->params = $params;
		$errorarray = array();	
		if(!$this->params['action'])
		{
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['parentid']) || $this->params['parentid'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['module']) || $this->params['module'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['data_city']) || $this->params['data_city'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='data city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['version']) || $this->params['version'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='version missing';
			echo json_encode($errorarray); exit;
		}
		if(isset($this->params['genInvoice'])){
				$this->generate_inv = $this->params['genInvoice'];
		}else{
			$this->generate_inv	=	'';
		}
			
		if(isset($this->params['invDate'])){
			$this->invDate = $this->params['invDate'];
		}else{
			$this->invDate	=	'';
		}
		
		if(isset($this->params['app_version'])){
			$this->app_version = $this->params['app_version'];
		}else{
			$this->app_version	=	'';
		}
		if(isset($this->params['chk_gstn'])){
			$this->chk_gstn = $this->params['chk_gstn'];
		}else{
			$this->chk_gstn	=	'';
		}
		if(isset($this->params['usrcd'])){
			$this->usrcd = $this->params['usrcd'];
		}else{
			$this->usrcd	=	'';
		}
		if(isset($this->params['instrumentid'])){
			$this->instrumentid = $this->params['instrumentid'];
		}else{
			$this->instrumentid	=	'';
		}
		if(isset($this->params['trans_id'])){
			$this->trans_id = $this->params['trans_id'];
		}else{
			$this->trans_id	=	'';
		}
		if(isset($this->params['download_mod'])){
			$this->download_mod = $this->params['download_mod'];
		}else{
			$this->download_mod	=	'';
		}
		$this->usercode = null;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		$this->data_city =	$this->params['data_city'];
	}
	
	function setServers()
	{	
		global $db;
					
		if(DEBUG_MODE)
		{
			echo '<pre>db array :: ';
			print_r($db);
		}
		
		$data_city 				= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->idc_con			= $db[strtolower($data_city)]['idc']['master'];
        $this->db_payment       = $db['db_payment'];
		$this->local_tme_conn	= $db[strtolower($data_city)]['tme_jds']['master'];
		$this->local_d_jds		= $db[strtolower($data_city)]['d_jds']['master'];
		$this->local_iro_conn	= $db[strtolower($data_city)]['iro']['master'];
		$this->fin_con			= $db[strtolower($data_city)]['fin']['master'];
		$this->db_budgeting		= $db[strtolower($data_city)]['db_budgeting']['master'];
		
		switch(strtolower($this->params['module']))
		{
			case 'cs':
				$this->temp_con = $this->local_iro_conn;
			break;
			case 'tme':
				$this->temp_con = $this->local_tme_conn;
				$this->mongo_tme = 1;
				//~ if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
					//~ $this->mongo_tme = 1;
				//~ }
			break;
			case 'me':
				$this->temp_con = $this->idc_con;
				$this->mongo_flag = 1;
				//~ if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
					//~ $this->mongo_flag = 1;
				//~ }
			break;
			default:
			die('Invalid Module');
			break;
		}
		
	}
	
	function storeDealclosedData(){
		
		
		
		
	}
	
	function getCustomerGenDetails(){
		
		switch(strtolower($this->params['data_city']))
			{
				case 'mumbai':
				$pdf_city = 'mumbai';
				break;
				case 'delhi':
				$pdf_city = 'delhi';
				break;
				case 'kolkata':
				$pdf_city = 'kolkata';
				break;
				case 'bangalore':
				$pdf_city = 'bangalore';
				break;
				case 'chennai':
				$pdf_city = 'chennai';
				break;
				case 'hyderabad':
				$pdf_city = 'hyderabad';
				break;
				case 'pune':
				$pdf_city = 'pune';
				break;
				case 'ahmedabad':
				$pdf_city = 'ahmedabad';
				break;
				default:
				$pdf_city = 'remote';
				break;
			}
		
		
		if($this->params['data_city'] == 'Remote City' || $this->params['data_city'] =='remote' || $pdf_city=='remote')
		{
			$dtCty = 'remote_cities';
		}else
		{
			$dtCty = $this->params['data_city'];
		}
		$custome_details	=	array();
		$gen_array			=	array();
		
		$completeYr_from = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'01';
		 $completeYr_to = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'31';
		
		
		$checkod_qry_fresh = "select invoiceName from addInvoiceCompany where parentid='".$this->params['parentid']."' ORDER BY entryDate DESC LIMIT 1";
		$checkod_res_fresh = parent::execQuery($checkod_qry_fresh, $this->fin_con);
		if($checkod_res_fresh && parent::numRows($checkod_res_fresh)>0){
			$checkod_arr_fresh = parent::fetchData($checkod_res_fresh);
			$companyname_fresh = $checkod_arr_fresh['invoiceName'];
		}
		if($this->params['parentid'] == 'PXX22.XX22.090612215830.X7B9' || $this->chk_gstn == 1)
		{
			$client_gst_compname="select gstn_contract_name,gstn_address,gstn_state_name,gstn_no,doneon FROM db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."' ORDER BY doneon DESC LIMIT 1";
			$upd_recent_chk			="UPDATE db_payment.tbl_gstn_emailer_contract_data_latest SET chkflag=1 where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'";
			$con_upd_recent_chk = parent::execQuery($upd_recent_chk, $this->db_payment);
		}else
		{
			$client_gst_compname="select gstn_contract_name,gstn_address,gstn_state_name,gstn_no,doneon,chkflag FROM db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'  AND doneon<='".$completeYr_to." 23:59:59' ORDER BY doneon DESC LIMIT 1";
		}
		$client_gst_res_compname = parent::execQuery($client_gst_compname, $this->db_payment);
		if($client_gst_res_compname && parent::numRows($client_gst_res_compname)>0){
			$client_gst_arr_compname = parent::fetchData($client_gst_res_compname);
			
			if($client_gst_arr_compname['chkflag'] == 1)
				{
					 $client_gst_compname="select gstn_contract_name,gstn_address,gstn_state_name,gstn_no,doneon FROM db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."' ORDER BY doneon DESC LIMIT 1";
					$client_gst_res_compname = parent::execQuery($client_gst_compname, $this->db_payment);
					if($client_gst_res_compname && parent::numRows($client_gst_res_compname)>0){
						
						$client_gst_arr_compname = parent::fetchData($client_gst_res_compname);
						
						$client_gst_comp   = $client_gst_arr_compname['gstn_contract_name'];
						$gstn_address   = $client_gst_arr_compname['gstn_address'];
						$gstn_state_name   = $client_gst_arr_compname['gstn_state_name'];
					}
				}
			
			$client_gst_comp   = $client_gst_arr_compname['gstn_contract_name'];
			$gstn_address   = $client_gst_arr_compname['gstn_address'];
			$gstn_state_name   = $client_gst_arr_compname['gstn_state_name'];
        }
		
		
		$checkod_qry = "select invoice_businessname,invoice_cpersonname,invoice_cpersonnum from payment_otherdetails where parentid='".$this->params['parentid']."' and version ='".$this->params['version']."'";
		$checkod_res = parent::execQuery($checkod_qry, $this->fin_con);
		if($checkod_res && parent::numRows($checkod_res)>0){
			$checkod_arr = parent::fetchData($checkod_res);
			$od_companyname = $checkod_arr['invoice_businessname'];
			$od_contactperson = $checkod_arr['invoice_cpersonname'];
			$od_number = $checkod_arr['invoice_cpersonnum'];
		}
		
		if(strtolower($this->params['module']) == 'me' || strtolower($this->params['module']) == 'tme')
		{
			$general_sql="SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name,city,pincode,state FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->params['parentid']."'";
			$general_res = parent::execQuery($general_sql, $this->temp_con);
			if(parent::numRows($general_res)==0){
				$general_sql="SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name,city,pincode,state FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$this->params['parentid']."'";
				$general_res = parent::execQuery($general_sql, $this->local_iro_conn); 
				  
			}
		}else
		{
			$general_sql="SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name,city,pincode,state FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$this->params['parentid']."'";
			$general_res = parent::execQuery($general_sql, $this->temp_con);
		}
		if($general_res && parent::numRows($general_res)>0){
			$general_arr = parent::fetchData($general_res);
			$gen_companyname = $general_arr['companyname'];
			$gen_contact_person = $general_arr['contact_person'];
			$gen_mobile = $general_arr['mobile'];
			$gen_email = $general_arr['email'];
			$gen_landmark = $general_arr['landmark'];
			$gen_street = $general_arr['street'];
			$gen_area = $general_arr['area'];
			$gen_pincode = $general_arr['pincode'];
			$gen_building_name = $general_arr['building_name'];
			$gen_city = $general_arr['city'];
			$gen_pincode = $general_arr['pincode'];
			$gen_state = $general_arr['state'];
		}
		
		$custome_details['Billing Parentid']	=	$this->params['parentid'];
		
		
		if($companyname_fresh != '' || $companyname_fresh != null)
		{
			$custome_details['Company Name'] = $companyname_fresh;
		}
		if($companyname_fresh == '' || $companyname_fresh == null)
		{
			$custome_details['Company Name'] = $client_gst_comp;
		}
		if($companyname_fresh == '' && $client_gst_comp == '')
		{
			$custome_details['Company Name'] = $od_companyname;
		}
		if($companyname_fresh == '' && $client_gst_comp == '' && $od_companyname=='')
		{
			$custome_details['Company Name'] = $gen_companyname;
		}
		
		/*if($od_companyname == '' || $od_companyname == null){
			$custome_details['Company Name'] = $gen_companyname;
		}else{
			$custome_details['Company Name'] = $od_companyname;
		}*/
		
		if($od_contactperson == '' || $od_contactperson == null){  
			$custome_details['Customer Name'] =  $gen_contact_person;
		}else{
			$custome_details['Customer Name'] = $od_contactperson;
		}
		if($gstn_state_name == '' || $gstn_state_name == null )
		{
			$custome_details['state'] = $gen_state;
		}else
		{
			$custome_details['state'] = $gstn_state_name;
		}
		
		
		//$custome_details['state'] = $gen_state;
		if($gstn_address == '' || $gstn_address == null)
		{
			$custome_details['Billing Address'] = $gen_building_name.",".$gen_landmark.",".$gen_street.",".$gen_area.",".$gen_city.",".$gen_pincode;
			$custome_details['Billing Address'] = trim($custome_details['Billing Address'],',');
		}else
		{
			
			$custome_details['Billing Address'] = trim($gstn_address,',');
		}
		
		$custome_details['Email']  	     = $gen_email;
		
		if($od_number == '' || $od_number == null){
			$custome_details['Contact No'] 	 = $gen_mobile;
		}else{
			$custome_details['Contact No']	=	$od_number;
			
		}
		//~ $custome_details['gst_no']  	 = $gstn_no;
		
		if($this->params['download_mod'] == 1)
		{
			$new_date_qry ="SELECT new_date FROM db_invoice.tbl_gst_nonecs_date_data WHERE parentid='".$this->params['parentid']."' and version ='".$this->params['version']."' AND instrumentid='".$this->params['instrumentid']."'";
			$con_new_date_qry = parent::execQuery($new_date_qry, $this->fin_con);
			if($con_new_date_qry && parent::numRows($con_new_date_qry)>0){
				$fetch_new_date = parent::fetchData($con_new_date_qry);
				$custome_details['Date']    	 = $fetch_new_date['new_date'];
			}else
			{
				$custome_details['Date']    	 = $this->invDate;
			}
		}else
		{
			$custome_details['Date']    	 = $this->invDate;
		}
		
		
		
		//$custome_details['Date']    	 = $this->invDate;
		$custome_details['contract_city']    	 = $gen_city;
		//~ print_r($custome_details); die;
		$instrument_sql ="SELECT pan_no,tan_no,entry_doneby FROM payment_instrument_summary WHERE parentid='".$this->params['parentid']."' and version ='".$this->params['version']."'";
		$ins_res = parent::execQuery($instrument_sql, $this->fin_con);
		if($ins_res && parent::numRows($ins_res)>0){
			$ins_arr = parent::fetchData($ins_res);
			$custome_details['pan_no']	= $ins_arr['pan_no'];
			$custome_details['tan_no']	= $ins_arr['tan_no'];
			$custome_details['entry_doneby']	= $ins_arr['entry_doneby'];
		}else {
			$custome_details['pan_no']	= '';
			$custome_details['tan_no']	= '';
		}
		
			$sql_get_contract_campaigns = "SELECT a.campaignid,campaignname,a.version,budget,balance,ecsflag,duration,a.entry_date,tmeName,meName
										FROM payment_apportioning a JOIN payment_campaign_master b JOIN payment_otherdetails c
										ON a.campaignid=b.campaignid  AND a.parentid=c.parentid AND a.version=c.version
										WHERE a.parentid='".$this->params['parentid']."' AND a.version='".$this->params['version']."' AND a.cfwd_version='' and (budget>0 and budget<>balance) 
										ORDER BY a.entry_date,a.campaignid";
										
		$res_get_contract_campaigns = parent::execQuery($sql_get_contract_campaigns, $this->fin_con);
		
		if($res_get_contract_campaigns && parent::numRows($res_get_contract_campaigns))
		{
			while($row_get_contract_campaigns = parent::fetchData($res_get_contract_campaigns))
			{
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['campaignname'] = $row_get_contract_campaigns['campaignname'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['campaignid']	 = $row_get_contract_campaigns['campaignid'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['version'] 	 = $row_get_contract_campaigns['version'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['budget']		 = $row_get_contract_campaigns['budget'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['duration']	 = $row_get_contract_campaigns['duration'];
				$deal_close_date														 = $row_get_contract_campaigns['entry_date'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['balance']		 = $row_get_contract_campaigns['balance'];
				$ecs_flag 																 = $row_get_contract_campaigns['ecsflag'];
				$tme_name 																 = $row_get_contract_campaigns['tmeName'];
				$me_name 																 = $row_get_contract_campaigns['meName'];
				//~ $me_name																=	'Y S Anusha';
			}
		}
		
		$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$custome_details['entry_doneby'];
		$transferstring = $this->curlcall($ssourl,'','');
		$sso_det=json_decode($transferstring,TRUE);
		$relationshipMgr=$sso_det['data']['empname'];
		if($me_name!= '')
		{
			$custome_details['Relationship Manager']	=	$me_name;
		}else
		{
			$custome_details['Relationship Manager']	=	$relationshipMgr;
		}
		
		if($custome_details != ''){
			$gen_array['errorCode']	=	0;
			$gen_array['data']		=	$custome_details;
		}else{
			$gen_array['errorCode']	=	1;
		}
		return json_encode($gen_array);
		
	}
	
	function get_all_instrument(){
		
		$resArr	=	array();
		$InstrumentDetailsarray	=	array();
		$query_instrument	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' AND a.approvalStatus=1 AND (a.instrumentAmount>0 OR a.tdsAmount>0) AND campaign_type!=61 GROUP BY b.finalapprovaldate DESC";
		$res_instrument 	= parent::execQuery($query_instrument, $this->fin_con);
		
		$query_instrument1	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' GROUP BY a.entry_date DESC";
		$res_instrument1 	= parent::execQuery($query_instrument1, $this->fin_con);
		
		$query_disruption	=	"SELECT * FROM payment_apportioning WHERE parentid='".$this->params['parentid']."' AND disruption_flag=4 GROUP BY VERSION ORDER BY entry_date DESC";
		$res_disruption 	= parent::execQuery($query_disruption, $this->fin_con);
		
		$query_creditnote	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' AND a.approvalStatus=1 AND a.instrumentAmount<0 GROUP BY b.finalapprovaldate DESC";
		$res_creditnote 	= parent::execQuery($query_creditnote, $this->fin_con);
		
		 $query_right_to_use	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' AND a.approvalStatus=1 AND (a.instrumentAmount>0 OR a.tdsAmount>0) AND campaign_type=61 GROUP BY b.finalapprovaldate DESC";
		$res_right_to_use 	= parent::execQuery($query_right_to_use, $this->fin_con);
		
		
		$query_ccemi_chk =	"SELECT * FROM online_regis1.tbl_selfsignup_contracts WHERE  parentid = '".$this->params['parentid']."' ";
		$res_ccemi_chk 	 = parent::execQuery($query_ccemi_chk, $this->idc_con);
		
		
		$query_companyname	=	"SELECT companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$this->params['parentid']."'";
		$res_companyname 	= parent::execQuery($query_companyname, $this->local_iro_conn);
		$row_comp = mysql_fetch_assoc($res_companyname);
		
		if(mysql_num_rows($res_instrument) > 0 || mysql_num_rows($res_instrument1) > 0){
			$resArr['errorCode']	=	0;
			$resArr['data']['parentid']	=	$this->params['parentid'];
			$resArr['data']['companyname']	=	$row_comp['companyname'];
			while($row_ins = mysql_fetch_assoc($res_instrument)){
				array_push($InstrumentDetailsarray,$row_ins);
				$resArr['data']['invoiceAnnexure'][$row_ins['instrumentId']]['approved_date'][]		=	date('Y-m-d',strtotime($row_ins['approvalDate']));
				$resArr['data']['invoiceAnnexure'][$row_ins['instrumentId']]['version'][]		=	$row_ins['version'];
				//~ $resArr['data']['invoice'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
				
				//~ $resArr['data']['receipt'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
			}
			while($row_ins1 = mysql_fetch_assoc($res_instrument1)){
					$resArr['data']['receipt'][$row_ins1['instrumentId']]['dealClosed_date'][]		=	date('Y-m-d',strtotime($row_ins1['entryDate']));
					$resArr['data']['receipt'][$row_ins1['instrumentId']]['version'][]				=	$row_ins1['version'];
			}
			while($row_ins2 = mysql_fetch_assoc($res_disruption)){
					$resArr['data']['receipt']['dispurtion']['dealClosed_date'][]		=	date('Y-m-d',strtotime($row_ins2['entry_date']));
					$resArr['data']['receipt']['dispurtion']['version'][]				=	$row_ins2['version'];
			}
			while($row_ins3 = mysql_fetch_assoc($res_creditnote)){
				$resArr['data']['credit'][$row_ins3['instrumentId']]['approved_date'][]		=	date('Y-m-d',strtotime($row_ins3['approvalDate']));
				$resArr['data']['credit'][$row_ins3['instrumentId']]['version'][]		=	$row_ins3['version'];
				$resArr['data']['credit'][$row_ins3['instrumentId']]['app_version'][]		=	$row_ins3['app_version'];
			}
			while($row_ins4 = mysql_fetch_assoc($res_right_to_use)){
				$resArr['data']['res_right_to_use'][$row_ins4['instrumentId']]['approved_date'][]		=	date('Y-m-d',strtotime($row_ins4['approvalDate']));
				$resArr['data']['res_right_to_use'][$row_ins4['instrumentId']]['version'][]		=	$row_ins4['version'];
			}
			while($row_ccemi_chk 	 = mysql_fetch_assoc($res_ccemi_chk))
			{
				
				$query_ccemi =	"SELECT * FROM tbl_contract_transaction_info WHERE  master_transaction_id = '".$row_ccemi_chk['trans_id']."' AND is_processed IN (1,2) AND pg_pay_mode='ccemi'";
				$res_ccemi 	 = parent::execQuery($query_ccemi, $this->idc_con);
				$row_ccemi_main 	 = parent::numRows($res_ccemi);
				if($row_ccemi_main>0)
				{
					  $row_ins5 = mysql_fetch_assoc($res_ccemi);
				
					$resArr['data']['invoice_ccemi'][$row_ccemi_chk['trans_id']]['approved_date'][]			=	date('Y-m-d',strtotime($row_ccemi_chk['requested_date']));
					$resArr['data']['invoice_ccemi'][$row_ccemi_chk['trans_id']]['version'][]				=	$row_ccemi_chk['version'];
					$resArr['data']['invoice_ccemi'][$row_ccemi_chk['trans_id']]['trans_id'][]				=	$row_ccemi_chk['trans_id'];
				}
			}
			//~ $resArr['data']	=	$InstrumentDetailsarray;
		}else{
			$resArr['errorCode']	=	1;
			$resArr['msg']			=	'Instrument not found';
			
		}
		
		return json_encode($resArr);
	}
	
	function get_all_instrument_approved($generate_invoice_fn){
		
		 $resArr	=	array();
		$InstrumentDetailsarray	=	array();
		if($this->params['fromDt']!= '')
		{
			$frmDt = $this->params['fromDt'];
		}else
		{
			$frmDt ='';	
		}
		if($this->params['toDt']!='')
		{
			$toDt = $this->params['toDt'];
		}else
		{
			$toDt = '';
		}
		$toDt = $this->params['toDt'];
		 $query_instrument	=	"SELECT *,b.finalapprovaldate AS approvalDate,a.entry_date AS entryDate,IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' AND a.approvalStatus=1 AND (a.instrumentAmount>0 OR a.tdsAmount>0) AND b.finalapprovaldate>='$frmDt 00:00:00' AND b.finalapprovaldate<='$toDt 23:59:59' GROUP BY b.finalapprovaldate DESC";
		
		$res_instrument 	= parent::execQuery($query_instrument, $this->fin_con);
		
		
		if(mysql_num_rows($res_instrument) > 0){
			$resArr['errorCode']	=	0;
			$resArr['data']['parentid']	=	$this->params['parentid'];
			while($row_ins = mysql_fetch_assoc($res_instrument)){
				array_push($InstrumentDetailsarray,$row_ins);
				$resArr['data']['instrumentid'][$row_ins['instrumentId']]['approved_date'][]		=	date('Y-m-d',strtotime($row_ins['approvalDate']));
				$resArr['data']['instrumentid'][$row_ins['instrumentId']]['version'][]		=	$row_ins['version'];
				$resArr['data']['instrumentid'][$row_ins['instrumentId']]['total_amount'][]		=	$row_ins['totalAmount'];
				//~ $resArr['data']['invoice'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
				
				//~ $resArr['data']['receipt'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
			}
			
			//~ $resArr['data']	=	$InstrumentDetailsarray;
		}else{
			$resArr['errorCode']	=	1;
			$resArr['msg']			=	'Instrument not found';
			
		}
		
		return json_encode($resArr);
	}
	
	function getCustomerGstDetails(){
		
		
		switch(strtolower($this->params['data_city']))
			{
				case 'mumbai':
				$pdf_city = 'mumbai';
				break;
				case 'delhi':
				$pdf_city = 'delhi';
				break;
				case 'kolkata':
				$pdf_city = 'kolkata';
				break;
				case 'bangalore':
				$pdf_city = 'bangalore';
				break;
				case 'chennai':
				$pdf_city = 'chennai';
				break;
				case 'hyderabad':
				$pdf_city = 'hyderabad';
				break;
				case 'pune':
				$pdf_city = 'pune';
				break;
				case 'ahmedabad':
				$pdf_city = 'ahmedabad';
				break;
				default:
				$pdf_city = 'remote';
				break;
			}
		
		if($this->params['data_city'] == 'Remote City' || $this->params['data_city'] =='remote' || $pdf_city=='remote')
		{
			$dtCty = 'remote_cities';
		}else
		{
			$dtCty = $this->params['data_city'];
		}
		 
		$completeYr_from = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'01';
		 $completeYr_to = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'31';
		
		$row_receipt = array();
		if($this->params['parentid'] == 'PXX22.XX22.090612215830.X7B9' || $this->chk_gstn == 1)
		{
			$client_gst_sql="select gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no,server_city from db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."' ORDER BY doneon DESC LIMIT 1";
			
			$upd_recent_chk			="UPDATE db_payment.tbl_gstn_emailer_contract_data_latest SET chkflag=1 where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'";
			$con_upd_recent_chk = parent::execQuery($upd_recent_chk, $this->db_payment);
		}else
		{
			
			if($this->params['genInvoice'] == 4)
			{
				
				$RcptInvArray=$this->makeCreditNoteID($stateName,$row_receipt,$this->params['app_version']);
				
				$invoiceNumber_orig_date=$RcptInvArray['orig_invoice_date'];
				
				$client_gst_sql="select gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no,server_city,chkflag from db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'  AND doneon<='".$invoiceNumber_orig_date." 23:59:59' ORDER BY doneon DESC LIMIT 1";
			}else
			{
				$client_gst_sql="select gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no,server_city,chkflag from db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'  AND doneon<='".$completeYr_to." 23:59:59' ORDER BY doneon DESC LIMIT 1";
			}
			
			
			//$client_gst_sql="select gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no,server_city,chkflag from db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."'  AND doneon<='".$completeYr_to." 23:59:59' ORDER BY doneon DESC LIMIT 1";
		} 
		$client_gst_res = parent::execQuery($client_gst_sql, $this->db_payment);
		if($client_gst_res && parent::numRows($client_gst_res)>0){
			$client_gst_arr = parent::fetchData($client_gst_res);
			
				if($client_gst_arr['chkflag'] == 1)
				{
					$client_gst_sql="select gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no,server_city from db_payment.tbl_gstn_emailer_contract_data_latest where parentid='".$this->params['parentid']."' AND server_city='".$dtCty."' ORDER BY doneon DESC LIMIT 1";
					
					$client_gst_res = parent::execQuery($client_gst_sql, $this->db_payment);
						if($client_gst_res && parent::numRows($client_gst_res)>0){
							$client_gst_arr = parent::fetchData($client_gst_res);
							
							$row_receipt['errorCode']	=	0;
							$row_receipt['data']['gstn_contract_name']   = $client_gst_arr['gstn_contract_name'];
							$row_receipt['data']['gstn_state_code']      = $client_gst_arr['gstn_state_code'];
							$row_receipt['data']['gstn_state_name']      = $client_gst_arr['gstn_state_name'];
							$row_receipt['data']['area']                 = $client_gst_arr['area'];
							$row_receipt['data']['gstn_address']         = $client_gst_arr['gstn_address'];
							$row_receipt['data']['gstn_pincode']         = $client_gst_arr['gstn_pincode'];
							if($this->invDate>='2017-05-22')
							{
								$row_receipt['data']['gstn_no']              = $client_gst_arr['gstn_no'];
							}else
							{
								$row_receipt['data']['gstn_no']              = '';
							}
							$row_receipt['data']['server_city']              = $client_gst_arr['server_city'];
							
						}
				}
			
					$row_receipt['errorCode']	=	0;
					$row_receipt['data']['gstn_contract_name']   = $client_gst_arr['gstn_contract_name'];
					$row_receipt['data']['gstn_state_code']      = $client_gst_arr['gstn_state_code'];
					$row_receipt['data']['gstn_state_name']      = $client_gst_arr['gstn_state_name'];
					$row_receipt['data']['area']                 = $client_gst_arr['area'];
					$row_receipt['data']['gstn_address']         = $client_gst_arr['gstn_address'];
					$row_receipt['data']['gstn_pincode']         = $client_gst_arr['gstn_pincode'];
					if($this->invDate>='2017-05-22')
					{
						$row_receipt['data']['gstn_no']              = $client_gst_arr['gstn_no'];
					}else
					{
						$row_receipt['data']['gstn_no']              = '';
					}
					$row_receipt['data']['server_city']              = $client_gst_arr['server_city'];
					
					
		}else{
			$row_receipt['errorCode']	=	1;
		}
		return json_encode($row_receipt);
	}
	
	/*function getjustdialdetails(){
		$jd_details = array();
		$getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."';";
        $resUcode = parent::execQuery($getUcode, $this->idc_con);
        if(parent::numRows($resUcode)>0)
        {
            while($rowUcode = parent::fetchData($resUcode)){
                $usrCode=$rowUcode['userid'];
            }
        }
        if($usrCode== ''){
			$getUcode = "SELECT mecode,mename,tmecode,tmename FROM payment_otherdetails  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
			$resUcode = parent::execQuery($getUcode, $this->fin_con);
			if(parent::numRows($resUcode)>0)
			{
				$rowUcode = parent::fetchData($resUcode);
				if($this->params['version'] % 10 == 3)
					$usrCode=$rowUcode['mecode'];
				else if	($this->params['version'] % 10 == 2)
					$usrCode=$rowUcode['tmecode'];
			}
		}
		
		$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
        $transferstring = $this->curlcall($ssourl,'','');
        $sso_det=json_decode($transferstring,TRUE);
        $employee_city=$sso_det['data']['city'];
        if($employee_city=='Delhi'){
            $employee_city='Noida';
        }
        
        $getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
        $stateData = parent::execQuery($getState, $this->local_d_jds);
        
        while($row = parent::fetchData($stateData)){
            $stateName  =$row['state_name'];
            $stateId    =$row['state_id'];
        }
        
        if($stateName == ''){
			$generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->params['parentid']."'";
			$generalres = parent::execQuery($generalsql, $this->temp_con);
			if($generalres && parent::numRows($generalres)>0){
				$generalrow = parent::fetchData($generalres);
				$employee_city 	= $generalrow['city'];
				$stateName	= $generalrow['state'];
				
				if(strtolower($employee_city)	==	'delhi' || strtolower($employee_city) == 'gurgaon'){
					$employee_city	=	'Noida';
				}
				if(strtolower($stateName)	==	'delhi' || strtolower($stateName)	==	'haryana'){
					$stateName	=	'Uttar Pradesh';
				}
			}
		}
		
		$getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."' AND jd_city='".$employee_city."'";
        $resJdData = parent::execQuery($getJdData, $this->db_payment);
        if(parent::numRows($resJdData)==0){
            $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."'";
            $resJdData = parent::execQuery($getJdData, $this->db_payment);
        }
        
        
        if(parent::numRows($resJdData) > 0){
			$row_resJdData = parent::fetchData($resJdData);
			$jd_details['errorCode']			   = 0;
			$jd_details['data']['jd_state_code']           = $row_resJdData['state_code'];
			$jd_details['data']['jd_state_name']           = $row_resJdData['state_name'];
			$jd_details['data']['jd_jd_city']              = $row_resJdData['jd_city'];
			$jd_details['data']['jd_cin_no']               = $row_resJdData['cin_no'];
			$jd_details['data']['jd_pan_no']               = $row_resJdData['pan_no'];
			$jd_details['data']['jd_jd_gst']               = $row_resJdData['jd_gst'];
			$jd_details['data']['jd_billing_address']      = $row_resJdData['jd_billing_address'];
			$jd_details['data']['jd_vat_tin_no']           = $row_resJdData['vat_tin_no'];
			$jd_details['data']['jd_sac_code']             = $row_resJdData['sac_code'];
			$jd_details['data']['jd_hsn_code']             = $row_resJdData['hsn_code'];
			$jd_details['data']['dealclosed_by']           = $usrCode;
		}else{
			$jd_details['errorCode']	=	1;
		}
		return json_encode($jd_details);
    }*/
   
   
   function getjustdialdetails(){
		
		$jd_details = array();
		
		$getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."';";
		$resUcode = parent::execQuery($getUcode, $this->idc_con);
		if(parent::numRows($resUcode)>0)
		{
			while($rowUcode = parent::fetchData($resUcode)){
				$usrCode=$rowUcode['userid'];
			}
		}
		if($usrCode== ''){
			$getUcode = "SELECT mecode,mename,tmecode,tmename FROM payment_otherdetails  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
			$resUcode = parent::execQuery($getUcode, $this->fin_con);
			if(parent::numRows($resUcode)>0)
			{
				$rowUcode = parent::fetchData($resUcode);
				if($this->params['version'] % 10 == 3)
					$usrCode=$rowUcode['mecode'];
				else if	($this->params['version'] % 10 == 2)
					$usrCode=$rowUcode['tmecode'];
			}
			if(($usrCode == '') && ($rowUcode['mecode']!='' && $rowUcode['mecode']!='ME1'))
			{
				$usrCode = 	$rowUcode['mecode'];
			}else if(($usrCode == '') && ($rowUcode['tmecode']!='' && $rowUcode['tmecode']!='TME1'))
			{
				$usrCode = 	$rowUcode['tmecode'];
			}
			if($this->params['parentid'] == 'PXX22.XX22.180809114831.Q6A7')
			{
				$usrCode = '';
			}
			if($usrCode == '' || $usrCode == 0)
			{
				$getUcode_paymentInstrmnt = "SELECT entry_doneby FROM payment_instrument_summary  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
				$resUcodepaymentInstrmnt = parent::execQuery($getUcode_paymentInstrmnt, $this->fin_con);
				$rowUcodepaymentInstrmnt = parent::fetchData($resUcodepaymentInstrmnt);
				$usrCode = $rowUcodepaymentInstrmnt['entry_doneby'];
			}	
			
		}
			
			if($usrCode == 'userselfwebsignup')
			{
				
				$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='Mumbai'";
				$stateData = parent::execQuery($getState, $this->local_d_jds);
				while($row = mysql_fetch_assoc($stateData)){
					$stateName  =$row['state_name'];
					$stateId    =$row['state_id'];
				}
			}
			else
			{
				$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
				$transferstring = $this->curlcall($ssourl,'','');
				$sso_det=json_decode($transferstring,TRUE);
				$employee_city=$sso_det['data']['city'];
				if($employee_city=='Delhi'){
					$employee_city='Noida';
				}
				
				$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
				$stateData = parent::execQuery($getState, $this->local_d_jds);
				
				while($row = parent::fetchData($stateData)){
					$stateName  =$row['state_name'];
					$stateId    =$row['state_id'];
				}
			}
		
		
			/*$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
			$transferstring = $this->curlcall($ssourl,'','');
			$sso_det=json_decode($transferstring,TRUE);
			$employee_city=$sso_det['data']['city'];
			if($employee_city=='Delhi'){
				$employee_city='Noida';
			}
			
			$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
			$stateData = parent::execQuery($getState, $this->local_d_jds);
			
			while($row = parent::fetchData($stateData)){
				$stateName  =$row['state_name'];
				$stateId    =$row['state_id'];
			}*/
        
		
		$getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."' AND jd_city='".$employee_city."'";
        $resJdData = parent::execQuery($getJdData, $this->db_payment);
        if(parent::numRows($resJdData)==0){
            $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."'";
            $resJdData = parent::execQuery($getJdData, $this->db_payment);
        }
       
        if(parent::numRows($resJdData) > 0){
			$row_resJdData = parent::fetchData($resJdData);
			$jd_details['errorCode']			   = 0;
			$jd_details['data']['jd_state_code']           = $row_resJdData['state_code'];
			$jd_details['data']['jd_state_name']           = $row_resJdData['state_name'];
			$jd_details['data']['jd_jd_city']              = $row_resJdData['jd_city'];
			$jd_details['data']['jd_cin_no']               = $row_resJdData['cin_no'];
			$jd_details['data']['jd_pan_no']               = $row_resJdData['pan_no'];
			$jd_details['data']['jd_jd_gst']               = $row_resJdData['jd_gst'];
			$jd_details['data']['jd_billing_address']      = $row_resJdData['jd_billing_address'];
			$jd_details['data']['jd_vat_tin_no']           = $row_resJdData['vat_tin_no'];
			$jd_details['data']['jd_sac_code']             = $row_resJdData['sac_code'];
			$jd_details['data']['jd_hsn_code']             = $row_resJdData['hsn_code'];
			$jd_details['data']['dealclosed_by']           = $usrCode;
		}else{
			$jd_details['errorCode']	=	1;
		}
		return json_encode($jd_details);
    }
   
    
    function getInstrumentDetailsArr($Pparentid,$version){
		
				$receiptDetailsarray = array();
				//payment details - cheque
				$query_cheque = "SELECT  c.instrumentType,a.instrumentId,
				c.chequeNo AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,c.chequeNo, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a			
				INNER JOIN payment_cheque_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND a.paymentType ='FRESH'";

				
				$res_cheque = parent::execQuery($query_cheque, $this->fin_con);
					while($row_cheque = parent::fetchData($res_cheque)){
						array_push($receiptDetailsarray,$row_cheque);
					}
					
				//payment details - cash
				$query_cash = "SELECT c.instrumentType,a.instrumentId, 
				c.approvalCode as paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_cc_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_cash = parent::execQuery($query_cash, $this->fin_con);
				while($row_cash = parent::fetchData($res_cash)){
					array_push($receiptDetailsarray,$row_cash);
				}

				//payment details - credit card
				$query_credit = "SELECT c.instrumentType,a.instrumentId,		
				c.receiptNo as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_cash_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_credit = parent::execQuery($query_credit, $this->fin_con);
				while($row_credit = parent::fetchData($res_credit)){
					array_push($receiptDetailsarray,$row_credit);
				}

				//payment details -NEFT Payments
				$query_neft = "SELECT c.instrumentType,a.instrumentId,		
				c.approvalCode as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_neft_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_neft = parent::execQuery($query_neft, $this->fin_con);
				while($row_neft = parent::fetchData($res_neft)){
					array_push($receiptDetailsarray,$row_neft);
				}
				//echo '</br>SERVICE_TAX=='.SERVICE_TAX;
				//echo "<pre>";
				//print_r($receiptDetailsarray);exit;
				
				//payment details -Payu Payments
				if($version % 10 != 3){ 
					$query_payu = "SELECT a.instrumentType,a.instrumentId,a.instrumentAmount, IF(c.service_tax < 1,c.service_tax*100,c.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
					FROM db_payment.payu_instrument_summary AS a		
					INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId 
					WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND c.version='".$version."' AND dealclose_flag=1 AND inst_delete_flag=0 AND a.paymentType ='FRESH' AND c.fin_entry_flag=0";
					$res_payu = parent::execQuery($query_payu, $this->idc_con);
					while($row_payu = parent::fetchData($res_payu)){
						array_push($receiptDetailsarray,$row_payu);
					}
				}
			  if(count($receiptDetailsarray)>0)
			  {
				  $i=0;
				foreach($receiptDetailsarray as $key=>$value){
				$instrument_details_arr[$i]['date']     = $value['entry_date'];
				$instrument_details_arr[$i]['id']     = $value['instrumentId'];
				//$instrument_details_arr[$i]['amount'] = $value['instrumentAmount'];
				
				$instrumentId 		= $value['instrumentId'];		
				$instrumentId_org 	= $value['instrumentId'];
				
				//$instrumentAmount 	= round($value['instrumentAmount']);
				$totalInstrumentAmount = round($value['instrumentAmount'] + $totalInstrumentAmount);		
				
				//$tdsAmount 	 	  	= round($value['tdsAmount']);
				//$totalTds 		  	+= round($value['tdsAmount'] + $totalTds);	
				$totalTds 		  	+= round($value['tdsAmount']);	
				
				$grossAmount 	  	= round($value['instrumentAmount'] + $value['tdsAmount']);
				$totalgrossAmount 	= round($grossAmount + $totalgrossAmount);


				
                //Check Service Tax starts
                $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
                $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
                while($rows = parent::fetchData($chkSrvcTaxRes)){
                    $srviceTaxPerc=$rows['service_tax_percent'];
                }
                
                //Check Service Tax ends
                if($srviceTaxPerc=='0.15'){
                    $cur_servicetax      = self :: CURRENT_SERVICE_TAX * 100;
                    $servicetaxAmt       = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
                    $servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_SERVICE_TAX);
                }else if($srviceTaxPerc=='0.18'){
                    $cur_servicetax      = self :: CURRENT_GST * 100;
                    $servicetaxAmt       = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
                    $servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_GST);
                }
				
                //IMP net amount
                $netAmount           = ($grossAmount - $servicetaxAmt);



                $servicetaxAmtSb14   = ($netAmount * (14 / 100));
                $servicetaxAmtSb05   = ($netAmount * (0.5 / 100));
                $servicetaxAmtkkc05  = ($netAmount * (0.5 / 100));
                //exit;
                $totalServicetaxAmt14   = (number_format($servicetaxAmtSb14, 2, '.', '') + $totalServicetaxAmt14);
                $totalServicetaxAmt05   = (number_format($servicetaxAmtSb05, 2, '.', '') + $totalServicetaxAmt05);
                $totalServicetaxAmtkkc05= (number_format($servicetaxAmtkkc05, 2, '.', '') + $totalServicetaxAmtkkc05);

                //$netAmount            = round($grossAmount - $servicetaxAmt);
                $totalNetAmount     = (number_format($netAmount, 2, '.', '') + $totalNetAmount);

                $totalAmount        = round($value['totalAmount'] - $totalTds);
                //$totalInstrumentAmt = round($totalAmount + $totalInstrumentAmt);
                $totalInstrumentAmt = $totalInstrumentAmt+round($value['instrumentAmount']);

                if(strtolower($value['instrumentType']) == 'cheque'){
                    $chequeNo = $value['chequeNo'];
                }else{
                    $chequeNo = "NA";
                }

                $instrument_details_arr[$i]['chqno']   =    ucfirst(strtolower($value['instrumentType']));
                $instrument_details_arr[$i]['chqno_actual']   = $chequeNo;
                $instrument_details_arr[$i]['netamt']  = number_format($netAmount,2);
                $instrument_details_arr[$i]['srvctax'] = number_format($servicetaxAmtSb14,2);
                $instrument_details_arr[$i]['sbctax']  = number_format($servicetaxAmtSb05,2);
                $instrument_details_arr[$i]['kkctax']  = number_format($servicetaxAmtkkc05,2);
                $instrument_details_arr[$i]['grossamt']= number_format($grossAmount,2);
                $instrument_details_arr[$i]['tdsamt']  = number_format($value['tdsAmount'],2);
                $instrument_details_arr[$i]['totamt']  = number_format($totalAmount,2);
                $instrument_details_arr[$i]['tan_no']  = $value['tan_no'];
                $instrument_details_arr[$i]['pan_no']  = $value['pan_no'];

                $activation_fee = '';
                $getCampaignQry = "SELECT budget FROM payment_snapshot WHERE parentid = '".$Pparentid."' AND instrumentId = '".$value['instrumentId']."' AND campaignId = '56' and (budget>0)";
                $resCampaignBudget = parent::execQuery($getCampaignQry, $this->fin_con);
                if($resCampaignBudget && parent::numRows($resCampaignBudget)>0){
                    $rsCampaign = parent::fetchData($resCampaignBudget);
                    $activation_fee = ' (inclusive Activation fee Rs '.$rsCampaign['budget'].'/-)';
                }
                $i ++;
              }

            //  $instrument_total_amt['totnetamt']  = number_format($totalNetAmount,2);
              $instrument_total_amt['totnetamt']  = number_format(round($totalNetAmount),2);
              $instrument_total_amt['totsrvctax'] = number_format($totalServicetaxAmt14,2);
              $instrument_total_amt['totsbctax']  = number_format($totalServicetaxAmt05,2);
              $instrument_total_amt['totkkctax']  = number_format($totalServicetaxAmtkkc05,2);
              $instrument_total_amt['totgrsamt']  = number_format($totalgrossAmount,2);
              $instrument_total_amt['tottdsamt']  = number_format($totalTds,2);
              $instrument_total_amt['totinsamt']  = number_format($totalInstrumentAmt,2);
              $instrument_total_amt['service_tax_percent']  = $srviceTaxPerc; 
              
              
              $instrument_arr['errorCode']		=	0;	
              $instrument_arr['data']['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
			  $instrument_arr['data']['all_instruments']   = $instrument_details_arr;
			  $instrument_arr['data']['instrument_total']  = $instrument_total_amt;
           }else{
			   $instrument_arr['errorCode']		=	1;	
		   }
			
			
			//~ echo '<pre>';print_r($instrument_arr);
			return json_encode($instrument_arr);

    }
    
    function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->fin_con[0], $this->fin_con[1], $this->fin_con[2]) ; 
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}

	 function fn_ccemi($Pparentid,$version,$genInvoice,$invDate){
				
				$instrument_arr	=	array();
				$receiptDetailsarray = array();
                //payment details - cheque
               
				$query_ccemi = "SELECT * FROM tbl_contract_transaction_info WHERE parentid   ='".$Pparentid."' AND master_transaction_id = '".$this->trans_id."' AND is_processed IN (1,2)";
				$res_ccemi = parent::execQuery($query_ccemi, $this->idc_con);
                $row_num = parent::numRows($res_ccemi);
				if($row_num>0)
				{
				  $row_ccemi = parent::fetchData($res_ccemi);
				  //$instrument_total_amt['totnetamt']  = number_format($totalNetAmount,2);
				  $instrument_total_amt['totamount']  = number_format(round($row_ccemi['amount_paybale']),2);
				  $instrument_total_amt['emiamount']  = number_format(round($row_ccemi['emi_waiver_amount']),2);
				  $instrument_total_amt['paidamount'] = number_format(round($row_ccemi['amount_paid']),2);
				  
				  
				  $instrument_arr['errorCode']		   = 0;	
				  $instrument_arr['data']['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
				  $instrument_arr['data']['all_instruments']   = $instrument_details_arr;
				  $instrument_arr['data']['instrument_total']  = $instrument_total_amt;
			  
		   }else{
			   $instrument_arr['errorCode']		   = 1;	
		   } 
		
		return json_encode($instrument_arr);
		
	}

	function getInstrumentDetailsArrApproval_mod($Pparentid,$version,$genInvoice,$invDate){
				 
				
				$instrument_arr	=	array();
				if($genInvoice == 1 || $genInvoice == 4){
					$whereDate		=	" and date(d.finalapprovaldate)='".$invDate."'";
					$whereEnDate	=	" and date(a.entry_date)='".$invDate."'";
				}else{
  	              $today=date('Y-m-d');
  	              $whereDate	=	 " and date(d.finalapprovaldate)='".$today."'";
  	              $whereEnDate	=	 "";
				}
  	             //~ $sqlgetmax="select max(end_time)  as today tbl_invoice_process_det";
                 //~ $res_max = parent::execQuery($sqlgetmax, $this->fin_con);
                     //~ while($row_max = parent::fetchData($res_max)){
                          //~ $today=$row_max['today'];
 	                   //~ }
 	                   //~ 

                $receiptDetailsarray = array();
                //payment details - cheque
                 $query_cheque = "SELECT  c.instrumentType,a.instrumentId,
                c.chequeNo AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,c.chequeNo, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cheque_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND a.version='".$version."' AND a.instrumentId='".$this->instrumentid."' AND d.finalapprovalflag=1 and a.instrumentAmount>0".$whereDate;

                $res_cheque = parent::execQuery($query_cheque, $this->fin_con);
                    while($row_cheque = parent::fetchData($res_cheque)){
                        array_push($receiptDetailsarray,$row_cheque);
	                   }
	                   
				//~ echo '---1----<pre>';print_r($receiptDetailsarray);die;
                //payment details - cash
                $query_cash = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cc_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND a.version='".$version."' AND a.instrumentId='".$this->instrumentid."'  AND d.finalapprovalflag=1 ".$whereDate;

                $res_cash = parent::execQuery($query_cash, $this->fin_con);
                while($row_cash = parent::fetchData($res_cash)){
                    array_push($receiptDetailsarray,$row_cash);
                }
				
				
                //payment details - credit card
               $query_credit = "SELECT c.instrumentType,a.instrumentId,
                c.receiptNo as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cash_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."'  AND a.instrumentId='".$this->instrumentid."' AND d.finalapprovalflag=1 ".$whereDate;

                $res_credit = parent::execQuery($query_credit, $this->fin_con);
                while($row_credit = parent::fetchData($res_credit)){
                    array_push($receiptDetailsarray,$row_credit);
                      
                }
		
                //payment details -NEFT Payments
                
                 $query_neft = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_neft_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."'  AND a.instrumentId='".$this->instrumentid."' AND d.finalapprovalflag=1 ".$whereDate; 

                $res_neft = parent::execQuery($query_neft, $this->fin_con);
                while($row_neft = parent::fetchData($res_neft)){
                    array_push($receiptDetailsarray,$row_neft);
                }
				//payment details -preinvoice
				 $query_preinvoice = "SELECT c.instrumentType,a.instrumentId,
                c.receiptNo  AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) AS service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_preinvoice_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."' AND a.instrumentId='".$this->instrumentid."' AND d.finalapprovalflag=1 ".$whereDate; 

                $res_preinvoice = parent::execQuery($query_preinvoice, $this->fin_con);
                while($row_preinvoice = parent::fetchData($res_preinvoice)){
                    array_push($receiptDetailsarray,$row_preinvoice);
                }
	
                

                //payment details -Payu Payments
               if($version % 10 != 3){ 
				   
				 $query_payu	=	"SELECT a.instrumentType,a.instrumentId,a.instrumentAmount,IF(c.service_tax < 1,c.service_tax * 100,c.service_tax) AS service_tax,a.tdsAmount,a.parentid,IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,a.entry_date,a.paymentType,a.pan_no,a.tan_no FROM db_payment.payu_instrument_summary AS a INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId WHERE a.parentid = '".$Pparentid."' AND a.version = '".$version."' AND c.version = '".$version."' AND c.proc_flag = 0 AND inst_delete_flag = 0 AND dealclose_flag=1 AND fin_entry_flag=1".$whereEnDate;
				  
               //~ $query_payu = "SELECT a.instrumentType,a.instrumentId,a.instrumentAmount, IF(c.service_tax < 1,c.service_tax*100,c.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
					//~ FROM db_payment.payu_instrument_summary AS a
					//~ INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId
					
					//~ WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND c.version='".$version."' ";

					$res_payu = parent::execQuery($query_payu, $this->idc_con);
					
					while($row_payu = parent::fetchData($res_payu)){
						array_push($receiptDetailsarray,$row_payu);
					}
				
				}
               
               //~ echo '<pre>';
               //~ print_r($receiptDetailsarray);die;
              
			if(count($receiptDetailsarray)>0)
              {
				  
				
                $i=0;
                foreach($receiptDetailsarray as $key=>$value){
                $instrument_details_arr[$i]['date']     = $value['entry_date'];
                $instrument_details_arr[$i]['id']     = $value['instrumentId'];
                //$instrument_details_arr[$i]['amount'] = $value['instrumentAmount'];

                $instrumentId       = $value['instrumentId'];
                $instrumentId_org   = $value['instrumentId'];

				//echo $value['instrumentAmount'];

                //$instrumentAmount     = round($value['instrumentAmount']);
                $totalInstrumentAmount = round($value['instrumentAmount'] + $totalInstrumentAmount);
				
                //$tdsAmount            = round($value['tdsAmount']);
                $totalTds           = round($value['tdsAmount'] + $totalTds);
                //$totalTds           = round($value['tdsAmount']);
                $grossAmount        = round($value['instrumentAmount'] + $value['tdsAmount']);
                $totalgrossAmount   = round($grossAmount + $totalgrossAmount);


                //Check Service Tax starts
                 //~ $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
                 //~ $srviceTaxPerc=0;
                //~ $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
               	//~ if($chkSrvcTaxRes && parent::numRows($chkSrvcTaxRes)>0){			
	                //~ while($rows = parent::fetchData($chkSrvcTaxRes)){
	                    //~ $srviceTaxPerc=$rows['service_tax_percent'];
	                //~ }
            	//~ }
            	
            	
            	//~ if($srviceTaxPerc==0)
            	$srviceTaxPerc=($value['service_tax']/100);

                //Check Service Tax ends
                if($srviceTaxPerc=='0.15'){
				$cur_servicetax 	 = self :: CURRENT_SERVICE_TAX * 100;
				$servicetaxAmt    	 = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
				$servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_SERVICE_TAX);
                }else if($srviceTaxPerc=='0.18'){
                    $cur_servicetax      = self :: CURRENT_GST * 100;
                    $servicetaxAmt       = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
                    $servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_GST);
                }

				//IMP net amount
				$netAmount			 = ($grossAmount - $servicetaxAmt);
				
				
				
				$servicetaxAmtSb14   = ($netAmount * (14 / 100));
				$servicetaxAmtSb05   = ($netAmount * (0.5 / 100));
				$servicetaxAmtkkc05	 = ($netAmount * (0.5 / 100));
				//exit;
				$totalServicetaxAmt14   = (number_format($servicetaxAmtSb14, 2, '.', '') + $totalServicetaxAmt14);
				$totalServicetaxAmt05   = (number_format($servicetaxAmtSb05, 2, '.', '') + $totalServicetaxAmt05);
				$totalServicetaxAmtkkc05= (number_format($servicetaxAmtkkc05, 2, '.', '') + $totalServicetaxAmtkkc05);
				
				//$netAmount        	= round($grossAmount - $servicetaxAmt);
				$totalNetAmount		= (number_format($netAmount, 2, '.', '') + $totalNetAmount);
				
				$totalAmount 	  	= round($value['totalAmount'] - $value['tdsAmount']);	
				//$totalInstrumentAmt = round($totalAmount + $totalInstrumentAmt);
				$totalInstrumentAmt = $totalInstrumentAmt+round($value['instrumentAmount']);
				
				if(strtolower($value['instrumentType']) == 'cheque'){
					$chequeNo = $value['chequeNo']; 
				}else{
					$chequeNo = "NA"; 
				}	
				
				
				//~ $instrument_details_arr	=	array();
				$instrument_details_arr[$i]['chqno']   =	ucfirst(strtolower($value['instrumentType']));  
				$instrument_details_arr[$i]['chqno_actual']   = $chequeNo;
				$instrument_details_arr[$i]['netamt']  = number_format($netAmount,2);
				$instrument_details_arr[$i]['srvctax'] = number_format($servicetaxAmtSb14,2);
				$instrument_details_arr[$i]['sbctax']  = number_format($servicetaxAmtSb05,2);
				$instrument_details_arr[$i]['kkctax']  = number_format($servicetaxAmtkkc05,2);
				$instrument_details_arr[$i]['grossamt']= number_format($grossAmount,2);
				$instrument_details_arr[$i]['tdsamt']  = number_format($value['tdsAmount'],2);
				$instrument_details_arr[$i]['totamt']  = number_format($totalAmount,2);
				$instrument_details_arr[$i]['tan_no']  = $value['tan_no'];
				$instrument_details_arr[$i]['pan_no']  = $value['pan_no'];

				$activation_fee = '';
				$getCampaignQry = "SELECT budget FROM payment_snapshot WHERE parentid = '".$Pparentid."' AND instrumentId = '".$value['instrumentId']."' AND campaignId = '56' and (budget>0)";
				$resCampaignBudget = parent::execQuery($getCampaignQry, $this->fin_con);
				if($resCampaignBudget && parent::numRows($resCampaignBudget)>0){			
					$rsCampaign = parent::fetchData($resCampaignBudget);			
					$activation_fee = ' (inclusive Activation fee Rs '.$rsCampaign['budget'].'/-)';			
				}
				$i ++;
		      }
		      
		      //$instrument_total_amt['totnetamt']  = number_format($totalNetAmount,2);
		      $instrument_total_amt['totnetamt']  = number_format(round($totalNetAmount),2);
		      $instrument_total_amt['totsrvctax'] = number_format($totalServicetaxAmt14,2);
		      $instrument_total_amt['totsbctax']  = number_format($totalServicetaxAmt05,2);
		      $instrument_total_amt['totkkctax']  = number_format($totalServicetaxAmtkkc05,2);
		      $instrument_total_amt['totgrsamt']  = number_format($totalgrossAmount,2);
		      $instrument_total_amt['tottdsamt']  = number_format($totalTds,2);
		      $instrument_total_amt['totinsamt']  = number_format($totalInstrumentAmt,2);
		      $instrument_total_amt['service_tax_percent']  = $srviceTaxPerc; 
		    
		      
		      $instrument_arr['errorCode']		   = 0;	
		      $instrument_arr['data']['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
			  $instrument_arr['data']['all_instruments']   = $instrument_details_arr;
			  $instrument_arr['data']['instrument_total']  = $instrument_total_amt;
			  
		   }else{
			   $instrument_arr['errorCode']		   = 1;	
		   } 
		
		//~ echo '<pre>';
		//~ print_r($instrument_arr);die;
		return json_encode($instrument_arr);
		
	}
    function getInstrumentDetailsArrApproval($Pparentid,$version,$genInvoice,$invDate){
				
				
				$instrument_arr	=	array();
				if($genInvoice == 1 || $genInvoice == 4){
					$whereDate		=	" and date(d.finalapprovaldate)='".$invDate."'";
					$whereEnDate	=	" and date(a.entry_date)='".$invDate."'";
				}else{
  	              $today=date('Y-m-d');
  	              $whereDate	=	 " and date(d.finalapprovaldate)='".$today."'";
  	              $whereEnDate	=	 "";
				}
				if($genInvoice == 4)
				$andCond = "";
				else
				$andCond = "and a.instrumentAmount>0";
  	             //~ $sqlgetmax="select max(end_time)  as today tbl_invoice_process_det";
                 //~ $res_max = parent::execQuery($sqlgetmax, $this->fin_con);
                     //~ while($row_max = parent::fetchData($res_max)){
                          //~ $today=$row_max['today'];
 	                   //~ }
 	                   //~ 

                $receiptDetailsarray = array();
                //payment details - cheque
                 $query_cheque = "SELECT  c.instrumentType,a.instrumentId,
                c.chequeNo AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,c.chequeNo, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cheque_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND a.version='".$version."'  AND d.finalapprovalflag=1 ".$andCond.$whereDate;

                $res_cheque = parent::execQuery($query_cheque, $this->fin_con);
                    while($row_cheque = parent::fetchData($res_cheque)){
                        array_push($receiptDetailsarray,$row_cheque);
	                   }
	                   
				//~ echo '---1----<pre>';print_r($receiptDetailsarray);die;
                //payment details - cash
                $query_cash = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cc_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND a.version='".$version."'   AND d.finalapprovalflag=1 ".$andCond.$whereDate;

                $res_cash = parent::execQuery($query_cash, $this->fin_con);
                while($row_cash = parent::fetchData($res_cash)){
                    array_push($receiptDetailsarray,$row_cash);
                }
				
				
                //payment details - credit card
                $query_credit = "SELECT c.instrumentType,a.instrumentId,
                c.receiptNo as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cash_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."'  AND d.finalapprovalflag=1 ".$andCond.$whereDate;

                $res_credit = parent::execQuery($query_credit, $this->fin_con);
                while($row_credit = parent::fetchData($res_credit)){
                    array_push($receiptDetailsarray,$row_credit);
                      
                }
		
                //payment details -NEFT Payments
                 $query_neft = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_neft_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."'   AND d.finalapprovalflag=1 ".$andCond.$whereDate; 

                $res_neft = parent::execQuery($query_neft, $this->fin_con);
                while($row_neft = parent::fetchData($res_neft)){
                    array_push($receiptDetailsarray,$row_neft);
                }
				//payment details -preinvoice
				 $query_preinvoice = "SELECT c.instrumentType,a.instrumentId,
                c.receiptNo  AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) AS service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
                FROM payment_instrument_summary AS a
                INNER JOIN payment_preinvoice_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND  a.version='".$version."' AND d.finalapprovalflag=1 ".$andCond.$whereDate;

                $res_preinvoice = parent::execQuery($query_preinvoice, $this->fin_con);
                while($row_preinvoice = parent::fetchData($res_preinvoice)){
                    array_push($receiptDetailsarray,$row_preinvoice);
                }
	
                
                //payment details -Payu Payments
               if($version % 10 != 3){ 
				   
				 $query_payu	=	"SELECT a.instrumentType,a.instrumentId,a.instrumentAmount,IF(c.service_tax < 1,c.service_tax * 100,c.service_tax) AS service_tax,a.tdsAmount,a.parentid,IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,a.entry_date,a.paymentType,a.pan_no,a.tan_no FROM db_payment.payu_instrument_summary AS a INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId WHERE a.parentid = '".$Pparentid."' AND a.version = '".$version."' AND c.version = '".$version."' AND c.proc_flag = 0 AND inst_delete_flag = 0 AND dealclose_flag=1 AND fin_entry_flag=1".$whereEnDate;
				  
               //~ $query_payu = "SELECT a.instrumentType,a.instrumentId,a.instrumentAmount, IF(c.service_tax < 1,c.service_tax*100,c.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
					//~ FROM db_payment.payu_instrument_summary AS a
					//~ INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId
					
					//~ WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND c.version='".$version."' ";

					$res_payu = parent::execQuery($query_payu, $this->idc_con);
					
					while($row_payu = parent::fetchData($res_payu)){
						array_push($receiptDetailsarray,$row_payu);
					}
				
				}
               
              
			if(count($receiptDetailsarray)>0)
              {
				  
				
                $i=0;
                foreach($receiptDetailsarray as $key=>$value){
                $instrument_details_arr[$i]['date']     = $value['entry_date'];
                $instrument_details_arr[$i]['id']     = $value['instrumentId'];
                //$instrument_details_arr[$i]['amount'] = $value['instrumentAmount'];

                $instrumentId       = $value['instrumentId'];
                $instrumentId_org   = $value['instrumentId'];

                //$instrumentAmount     = round($value['instrumentAmount']);
                $totalInstrumentAmount = round($value['instrumentAmount'] + $totalInstrumentAmount);

                //$tdsAmount            = round($value['tdsAmount']);
                $totalTds           = round($value['tdsAmount'] + $totalTds);
                //$totalTds           = round($value['tdsAmount']);
                $grossAmount        = round($value['instrumentAmount'] + $value['tdsAmount']);
                $totalgrossAmount   = round($grossAmount + $totalgrossAmount);


                //Check Service Tax starts
                 //~ $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
                 //~ $srviceTaxPerc=0;
                //~ $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
               	//~ if($chkSrvcTaxRes && parent::numRows($chkSrvcTaxRes)>0){			
	                //~ while($rows = parent::fetchData($chkSrvcTaxRes)){
	                    //~ $srviceTaxPerc=$rows['service_tax_percent'];
	                //~ }
            	//~ }
            	
            	
            	//~ if($srviceTaxPerc==0)
            	$srviceTaxPerc=($value['service_tax']/100);

                //Check Service Tax ends
                if($srviceTaxPerc=='0.15'){
				$cur_servicetax 	 = self :: CURRENT_SERVICE_TAX * 100;
				$servicetaxAmt    	 = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
				$servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_SERVICE_TAX);
                }else if($srviceTaxPerc=='0.18'){
                    $cur_servicetax      = self :: CURRENT_GST * 100;
                    $servicetaxAmt       = ($grossAmount - ($grossAmount / ( 1 + ($cur_servicetax / 100)) ) );
                    $servicetaxAmtSb100  = ($servicetaxAmt / self :: CURRENT_GST);
                }

				//IMP net amount
				$netAmount			 = ($grossAmount - $servicetaxAmt);
				
				
				
				$servicetaxAmtSb14   = ($netAmount * (14 / 100));
				$servicetaxAmtSb05   = ($netAmount * (0.5 / 100));
				$servicetaxAmtkkc05	 = ($netAmount * (0.5 / 100));
				//exit;
				$totalServicetaxAmt14   = (number_format($servicetaxAmtSb14, 2, '.', '') + $totalServicetaxAmt14);
				$totalServicetaxAmt05   = (number_format($servicetaxAmtSb05, 2, '.', '') + $totalServicetaxAmt05);
				$totalServicetaxAmtkkc05= (number_format($servicetaxAmtkkc05, 2, '.', '') + $totalServicetaxAmtkkc05);
				
				//$netAmount        	= round($grossAmount - $servicetaxAmt);
				$totalNetAmount		= (number_format($netAmount, 2, '.', '') + $totalNetAmount);
				
				$totalAmount 	  	= round($value['totalAmount'] - $value['tdsAmount']);	
				//$totalInstrumentAmt = round($totalAmount + $totalInstrumentAmt);
				$totalInstrumentAmt = $totalInstrumentAmt+round($value['instrumentAmount']);
				
				if(strtolower($value['instrumentType']) == 'cheque'){
					$chequeNo = $value['chequeNo']; 
				}else{
					$chequeNo = "NA"; 
				}	
				
				
				//~ $instrument_details_arr	=	array();
				$instrument_details_arr[$i]['chqno']   =	ucfirst(strtolower($value['instrumentType']));  
				$instrument_details_arr[$i]['chqno_actual']   = $chequeNo;
				$instrument_details_arr[$i]['netamt']  = number_format($netAmount,2);
				$instrument_details_arr[$i]['srvctax'] = number_format($servicetaxAmtSb14,2);
				$instrument_details_arr[$i]['sbctax']  = number_format($servicetaxAmtSb05,2);
				$instrument_details_arr[$i]['kkctax']  = number_format($servicetaxAmtkkc05,2);
				$instrument_details_arr[$i]['grossamt']= number_format($grossAmount,2);
				$instrument_details_arr[$i]['tdsamt']  = number_format($value['tdsAmount'],2);
				$instrument_details_arr[$i]['totamt']  = number_format($totalAmount,2);
				$instrument_details_arr[$i]['tan_no']  = $value['tan_no'];
				$instrument_details_arr[$i]['pan_no']  = $value['pan_no'];

				$activation_fee = '';
				$getCampaignQry = "SELECT budget FROM payment_snapshot WHERE parentid = '".$Pparentid."' AND instrumentId = '".$value['instrumentId']."' AND campaignId = '56' and (budget>0)";
				$resCampaignBudget = parent::execQuery($getCampaignQry, $this->fin_con);
				if($resCampaignBudget && parent::numRows($resCampaignBudget)>0){			
					$rsCampaign = parent::fetchData($resCampaignBudget);			
					$activation_fee = ' (inclusive Activation fee Rs '.$rsCampaign['budget'].'/-)';			
				}
				$i ++;
		      }
		      
		      //$instrument_total_amt['totnetamt']  = number_format($totalNetAmount,2);
		      $instrument_total_amt['totnetamt']  = number_format(round($totalNetAmount),2);
		      $instrument_total_amt['totsrvctax'] = number_format($totalServicetaxAmt14,2);
		      $instrument_total_amt['totsbctax']  = number_format($totalServicetaxAmt05,2);
		      $instrument_total_amt['totkkctax']  = number_format($totalServicetaxAmtkkc05,2);
		      $instrument_total_amt['totgrsamt']  = number_format($totalgrossAmount,2);
		      $instrument_total_amt['tottdsamt']  = number_format($totalTds,2);
		      $instrument_total_amt['totinsamt']  = number_format($totalInstrumentAmt,2);
		      $instrument_total_amt['service_tax_percent']  = $srviceTaxPerc; 
		    
		      
		      $instrument_arr['errorCode']		   = 0;	
		      $instrument_arr['data']['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
			  $instrument_arr['data']['all_instruments']   = $instrument_details_arr;
			  $instrument_arr['data']['instrument_total']  = $instrument_total_amt;
			  
		   }else{
			   $instrument_arr['errorCode']		   = 1;	
		   } 
		
		return json_encode($instrument_arr);
		
	}
	
	
	function annexContent(){
		
		$row_receipt	=	array();
		$retAnnexure_details		=	array();
		
		 $sql_receipt = "SELECT other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
		if($res_receipt && parent::numRows($res_receipt)>0)
		{
			$row_receipt = parent::fetchData($res_receipt);
		}
		
		$sql_annexure = "SELECT campaignid,category_details,other_details AS campaign_other_details FROM tbl_invoice_annexure_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_annexure = parent::execQuery($sql_annexure, $this->fin_con);
		if($res_annexure && parent::numRows($res_annexure)>0)
		{
			while($row_annexure = parent::fetchData($res_annexure)){
				$annexure_content['annexure_content'][$row_annexure['campaignid']]['category_details'] = $row_annexure['category_details'];
				$annexure_content['annexure_content'][$row_annexure['campaignid']]['campaign_other_details'] = $row_annexure['campaign_other_details'];
			}
			$row_receipt['annexure_content']= $annexure_content['annexure_content'];
		}
		
		
		
		if($row_receipt != '')
		{
			$retAnnexure_details['errorCode']	=	0;
			$retAnnexure_details['data']		=	$row_receipt;
		}else{
			$retAnnexure_details['errorCode']	=	1;
		}
		
		
		return json_encode($retAnnexure_details);	
		
	}
	function annexureContent($generate_invoice_fn){
		
		$annexure_details		=	array();
		$retAnnexure_details	=	array();
		
		$sql_receipt = "SELECT other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
		if($res_receipt && parent::numRows($res_receipt)>0)
		{
			$row_receipt = parent::fetchData($res_receipt);
		}
		
		$annexure_details	=	$row_receipt;
		
		$delete_invoice_receipt = "DELETE FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_delete_invoice_receipt = parent::execQuery($delete_invoice_receipt, $this->fin_con);		
		
		if($this->params['module'] == 'me')
		{
			$general_sql="SELECT data_city FROM tbl_companymaster_generalinfo WHERE parentid='".$this->params['parentid']."'";
			$general_res = parent::execQuery($general_sql, $this->temp_con);
		}else
		{
			$sql_orgin_city 		= "SELECT data_city FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$this->params['parentid']."'";
			$res_origin_city 		=	 parent::execQuery($sql_orgin_city, $this->local_d_jds);	
			$row_origin_city 		= parent::fetchData($res_origin_city);
		}
		
			
			
		$delete_annexure = "DELETE FROM tbl_invoice_annexure_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_delete_annexure = parent::execQuery($delete_annexure, $this->fin_con);	
		
		$sql_get_contract_campaigns = "SELECT a.campaignid,campaignname,a.version,budget,balance,ecsflag,duration,a.entry_date,tmeName,meName,c.cdata_city
										FROM payment_apportioning a JOIN payment_campaign_master b JOIN payment_otherdetails c
										ON a.campaignid=b.campaignid  AND a.parentid=c.parentid AND a.version=c.version
										WHERE a.parentid='".$this->params['parentid']."' AND a.version='".$this->params['version']."' AND a.cfwd_version='' and (budget>0 and budget<>balance) 
										ORDER BY a.entry_date,a.campaignid";
										
		$res_get_contract_campaigns = parent::execQuery($sql_get_contract_campaigns, $this->fin_con);
		
		if($res_get_contract_campaigns && parent::numRows($res_get_contract_campaigns))
		{
			while($row_get_contract_campaigns = parent::fetchData($res_get_contract_campaigns))
			{
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['campaignname'] = $row_get_contract_campaigns['campaignname'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['campaignid']	 = $row_get_contract_campaigns['campaignid'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['version'] 	 = $row_get_contract_campaigns['version'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['budget']		 = $row_get_contract_campaigns['budget'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['duration']	 = $row_get_contract_campaigns['duration'];
				$deal_close_date														 = $row_get_contract_campaigns['entry_date'];
				$campaign_arr[$row_get_contract_campaigns['campaignid']]['balance']		 = $row_get_contract_campaigns['balance'];
				$ecs_flag 																 = $row_get_contract_campaigns['ecsflag'];
				$tme_name 																 = $row_get_contract_campaigns['tmeName'];
				$me_name 																 = $row_get_contract_campaigns['meName'];
				$orgin_city 																 = $row_origin_city['data_city'];
			}
		}
		
		$original_campaign_arr = $campaign_arr;
		//~ echo 'before<pre>';print_r($original_campaign_arr);
		//echo '<br> ecs flag ::'.$ecs_flag;
		//echo '<br>';
		$omni_combo_sql="SELECT * 
						 FROM  dependant_campaign_details_temp 
						 WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";


		$payment_type_sql="SELECT * 
						 FROM  tbl_payment_type 
						 WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
						 
		$payment_type_sql_new="SELECT * 
						 FROM  tbl_payment_type_dealclosed 
						 WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		//~ if($this->params['module'] == 'cs'){
			//~ $this->params['module']	=	'me';
		//~ }
		
		switch(strtolower($this->params['module']))
		{
			
			case 'tme':
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->local_tme_conn);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			$payment_type_res_new = parent::execQuery($payment_type_sql_new, $this->fin_con);
			break;
			case 'me':
			
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			$payment_type_res_new = parent::execQuery($payment_type_sql_new, $this->fin_con);
			break;
			case 'cs':
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			$payment_type_res_new = parent::execQuery($payment_type_sql_new, $this->fin_con);
			break;
			case 'jda':
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			break;
			default:
			//~ die('Invalid Module OMNI');
			//~ break;
		}
			
		$dp2yrs=0;
		if($omni_combo_res && parent::numRows($omni_combo_res)>0){
			
			$omni_combo_row=parent::fetchData($omni_combo_res);
			$omni_details_combo['omni_combo_name']=$omni_combo_row['combo_type']; 

		}

		if($payment_type_res && parent::numRows($payment_type_res)>0){
			
			$payment_type_row=parent::fetchData($payment_type_res);
			
			if(strpos($payment_type_row['payment_type'], 'package_10dp_2yr') !== false ){
				$dp2yrs=1;
			}
			if(strpos($payment_type_row['payment_type'], 'package_10dp_2yr') !== false ){
				$dp2yrs=1;
			}
			$lfl_checking = explode(',',$payment_type_row['payment_type']);

		}else
		{
			if($payment_type_res_new && parent::numRows($payment_type_res_new)>0){
			
				$payment_type_row_new=parent::fetchData($payment_type_res_new);
				
				if(strpos($payment_type_row_new['payment_type'], 'package_10dp_2yr') !== false ){
					$dp2yrs=1;
				}
				if(strpos($payment_type_row_new['payment_type'], 'package_10dp_2yr') !== false ){
					$dp2yrs=1;
				}
				$lfl_checking = explode(',',$payment_type_row_new['payment_type']);

			}
		}
		
		  //if(in_array("flexi_pincode_budget", $lfl_checking))
		if(in_array('flexi_pincode_budget',$lfl_checking) || in_array('fixed position',$lfl_checking) || in_array('package_10dp_2yr',$lfl_checking))
			$lfl_package = 1;
			else
			$lfl_package = 0;
		
		if($campaign_arr[1] && $campaign_arr[1]['duration']>=3650 && $lfl_package == 1){
			
			$campaign_arr[1]['campaignname'] = 'VFL Package';
		}
		else if($campaign_arr[1] && parent::numRows($omni_combo_res)>0){

		$campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
		$original_campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
		}
		else if($campaign_arr[1] && !parent::numRows($omni_combo_res)>0 && $campaign_arr[73]['budget'] == 1){
			$campaign_arr[1]['campaignname'] = 'Combo';	
			//unset($campaign_arr[73]);	
		}
		else if($campaign_arr[73] && $omni_details_combo['omni_combo_name']=='Omni Ultima'){
		$campaign_arr[73]['campaignname'] = $omni_details_combo['omni_combo_name'];
			
			//$campaign_arr[73]['campaignname'] ='';
		}
		else if($campaign_arr[73] && $omni_details_combo['omni_combo_name']=='Omni Supreme'){
			
			$campaign_arr[1]['campaignname'] = 'Package';	 
			
			//$campaign_arr[73]['campaignname'] ='';
		}
		else if($campaign_arr[1] && $omni_details_combo['omni_combo_name']=='Combo'){
		$campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
			
			//$campaign_arr[73]['campaignname'] ='';
		}
		else if($dp2yrs==1)
			$campaign_arr[1]['campaignname'] ='Supreme Pack (Tenure : 2yrs)';
		elseif($campaign_arr[1])
		$campaign_arr[1]['campaignname'] ='Supreme Pack';
		
		
		
		if(($campaign_arr[5] || $campaign_arr[13] || $campaign_arr[22]) && $omni_details_combo['omni_combo_name'] == 'Combo 2')
		{
			unset($campaign_arr[5]);
			unset($campaign_arr[13]);
			unset($campaign_arr[22]);
			//unset($campaign_arr[73]);
			//unset($campaign_arr[1]);
			$campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
			$campaign_arr[73]['campaignname'] ='';
		}
		elseif(($campaign_arr[5] || $campaign_arr[13]))
		{
				$campaign_arr[5]['campaignname'] = 'Banner';
				$campaign_arr[13]['campaignname'] = 'Banner';
		}
		
		
		
		$campaign_name_arr = array();
		//~ echo "<pre>campaign_arr--";print_r($campaign_arr);
		if(count($campaign_arr)>0){
			foreach($campaign_arr as $key=>$campaign_details){
				//~ echo "<pre>campaign_details:--";print_r($campaign_details);
				$campaign_name_arr[]=$campaign_details['campaignname'];
				$campaign_id_arr[]=$campaign_details['campaignid'];
			}
			$campaign_name_arr = array_unique($campaign_name_arr);
			$campaign_id_arr = array_unique($campaign_id_arr);
		}
		
	
	/*--------------------------------------------------------------------------------------------------*/
		if(count($campaign_name_arr)>0){
			$annexure_details['annexure_content']['campaign_name_arr'] = $campaign_name_arr;/*list of campaign name */
			$annexure_details['annexure_content']['campaign_id_arr'] = $campaign_id_arr;/*list of campaign name */
		}

		
		
		
		if($deal_close_date) {
		$annexure_details['annexure_content']['Date'] = $deal_close_date;/*date of deal close */
		$payment_receipt_content['comp_gen_info']['Date'] = $deal_close_date;/*date of deal close */
		}
	/*------------------------------------------------------------------------------------------------*/
		if(count($campaign_name_arr)>0)
		$annexure_details['annexure_content']['payment_mode'] = ($ecs_flag)?'ECS':'NON-ECS';/*type of payment mode */
		
	/*------------------------------------------------------------------------------------------------*/
	
	    if($ecs_flag)
	    {
			
			$sql_ecs_mandate = "SELECT cycleselected,capamt FROM db_ecs.ecs_mandate WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
			$res_ecs_mandate = parent::execQuery($sql_ecs_mandate, $this->fin_con);
			if($res_ecs_mandate && parent::numRows($res_ecs_mandate))
			{
					$row_ecs_mandate = parent::fetchData($res_ecs_mandate);
					$cycle_selected  = $row_ecs_mandate['cycleselected'];
					$cap_amount 	 = $row_ecs_mandate['capamt'];
			}else{
				$sql_si_mandate = "SELECT cycleselected,capamt FROM db_si.si_mandate WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
				$res_si_mandate = parent::execQuery($sql_si_mandate, $this->fin_con);
				if($res_si_mandate && parent::numRows($res_si_mandate))
				{
					$row_si_mandate = parent::fetchData($res_si_mandate);
					$cycle_selected  = $row_si_mandate['cycleselected'];
					$cap_amount 	 = $row_si_mandate['capamt'];
				}
			}
			switch($cycle_selected)
			{
				case '30':
				$cyle = 'Monthly';
				break;
				case '15':
				$cyle = 'Fortnightly';
				break;
				case '7':
				$cyle = 'Weekly';
				break;
				default:
				if($this->params['module']!='cs' && $this->params['module']!='CS')
					$annexure_details['annexure_content']['invalidCycle'] = 'Invalid cycle selected';
				break;
			}
			$annexure_details['annexure_content']['ECS']['Billing Cycle'] = $cyle;
			$annexure_details['annexure_content']['ECS'][$cyle.' Amount(inc Serv. Tax)'] = 'Rs.'.number_format($cap_amount);
			
		}
		
		
		
		/*--------------------------------Payment receipt content-----------------------------------------*/
		
		if(( count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && !count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for JD Omni';
		else if(( count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for Advertising Listing and JD Omni';
		else if(( !count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for Advertising Listings on Just Dial';
		
		
		$relationshipMgrannx ="SELECT entry_doneby FROM payment_instrument_summary WHERE parentid='".$this->params['parentid']."' and version ='".$this->params['version']."'";
		$ins_res = parent::execQuery($relationshipMgrannx, $this->fin_con);
		if($ins_res && parent::numRows($ins_res)>0){
			$ins_arr = parent::fetchData($ins_res);
			$custome_details['entry_doneby']	= $ins_arr['entry_doneby'];
		}
		
		$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$custome_details['entry_doneby'];
		$transferstring = $this->curlcall($ssourl,'','');
		$sso_det=json_decode($transferstring,TRUE);
		$relationshipMgr=$sso_det['data']['empname'];
		if($me_name!= '' || $tme_name!='')
		{
			$payment_receipt_content['Relationship Manager']	     = $me_name;
			$payment_receipt_content['Telemarketing']			     = $tme_name;
		}else
		{
			$custome_details['Relationship Manager']	=$relationshipMgr;
			$payment_receipt_content['Telemarketing']	= $relationshipMgr;
		}
		
		
		
		
		
        if($this->params['action'] == 1){
            $instrument_details_arr = json_decode($this->getInstrumentDetailsArrApproval($this->params['parentid'],$this->params['version'],$this->generate_inv,$this->invDate),true);
		
        }else{
		$instrument_details_arr = json_decode($this->getInstrumentDetailsArr($this->params['parentid'],$this->params['version']),true);
        }
        
        if($instrument_details_arr['errorCode']	==	0){
			$payment_receipt_content['instrument_details'] = $instrument_details_arr['data'];
		}
        
		$instrument_id			=  $payment_receipt_content['instrument_details']['all_instruments'][0]['id'];
		//~ $instrument_id				=	'CHQ22APR12A9B0Z4';
		
       
		
//		ECHO 'RECEIPT CONTENT<pre>';
	//	print_r($payment_receipt_content);
				
		$sql_insert_annexure_content_log = "INSERT INTO tbl_invoice_payment_receipt_content(parentid,version,receipt_details,other_details,entry_date,data_city,instrumentId) VALUES('".$this->params['parentid']."','".$this->params['version']."','".$this->mysql_real_escape_custom(json_encode($payment_receipt_content))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['annexure_content']))."','".date('Y-m-d H:i:s')."','".$this->params['data_city']."','".$instrument_id."')";
		$res_insert_annexure_content_log = parent::execQuery($sql_insert_annexure_content_log, $this->fin_con);
			    
	    /*--------------------------------Payment receipt content-------------------------------------------*/
		
		
	
		//$omni_details_combo['omni_combo_name']=='Combo 2';
		
		
		$category_bidding_arr = $generate_invoice_fn->GetPlatDiamCategories(); //object
		
			
		//echo 'category bidding details arr <pre>';print_r($category_bidding_arr);
		
		if(($campaign_arr[1]['budget']>0 || $campaign_arr[2]['budget']>0) && count($category_bidding_arr))
		{
				
			$catid_arr  = array_keys($category_bidding_arr);
			//echo 'category details arr <pre>';print_r($catid_arr);
			
			//$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catid_arr)."')";
			//$res_category = parent::execQuery($sql_category, $this->local_d_jds);

			$cat_params = array();
			$cat_params['page'] ='generateinvoicecontentclass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,category_name,search_type';

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$catid_arr);
			$cat_params['where']		= json_encode($where_arr);
			if(count($catid_arr)>0){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_category)
				{
					$search_type =	$row_category['search_type'];
					switch ($search_type) {
						case '0':
							$searchtype = 'L';
							break;
						case '1':
							$searchtype = 'A';
							break;
						case '2':
							$searchtype = 'Z';
							break;
						case '3':
							$searchtype = 'SZ';
							break;						
						case '4':
							$searchtype = 'NM';
							break;
						case '5':
							$searchtype = 'VNM';
							break;	
					}
					$category_name_arr[$row_category['catid']]['catname'] = $row_category['category_name'].'-'.$searchtype;
				}
			}
			
		
			$pincode_areaname_arr = array();
			foreach ($category_bidding_arr as $catid=>$zone_pincode_arr)
			{
				foreach ($zone_pincode_arr as $zoneid => $pincode_arr)
				{
					$pincode_areaname_arr = array_merge($pincode_areaname_arr,array_keys($pincode_arr));
				}
			}
			
			
			$pincode_areaname_arr = array_unique($pincode_areaname_arr);
			
			if(count($pincode_areaname_arr))
			{
				
				if($this->params['data_city'] == 'remote')
				$data_city = $orgin_city;
				else
				$data_city = $this->params['data_city'];
				
				 $sql_areaname = "select pincode,group_concat(areaname ORDER BY callcnt_perday DESC) as areaname from tbl_areamaster_consolidated_v3  where data_city='".$data_city."' and type_flag=1 and  pincode in ('".implode("','",$pincode_areaname_arr)."') AND display_flag=1 group by pincode ORDER BY callcnt_perday DESC";
				 $res_areaname = parent::execQuery($sql_areaname, $this->local_d_jds);
				 
				 if($res_areaname && parent::numRows($res_areaname)>0)
				 {
					while($row_areaname=parent::fetchData($res_areaname)) {
						$areaname=explode(',', $row_areaname['areaname']);
						$areaname=$areaname[0];
						$pincodenames[$row_areaname['pincode']]=$row_areaname['pincode'].' - '.$areaname;
					}
				 }
			}
			
			
						
			foreach ($category_bidding_arr as $catid=>$zone_pincode_arr)
			{
				foreach ($zone_pincode_arr as $zoneid => $pincode_arr)
				{
					
					foreach($pincode_arr as $pincode => $pincode_details)
					{
						
						foreach($pincode_details as $position => $position_details){
							//echo '<pre>'.$position;
							//print_r($position_details);die;
							
							
							
							if($position<100)
							{
								
								$invoice_bidding_details_arr[$pincodenames[$pincode]][$catid] = $position_details['position'].' - '.($position_details['inventory']*100);
								
								//$invoice_bidding_details_arr[$catid][$pincodenames[$pincode]] = $position_details['position'].' - '.($position_details['inventory']*100);
							}else
							{
								$invoice_bidding_details_arr[$pincodenames[$pincode]][$catid] = ' - ';
								
								//$invoice_bidding_details_arr[$catid][$pincodenames[$pincode]] = ' - ';
								
								if($position == 100)
								$package_category_array[$catid] = $category_name_arr[$catid]['catname'];
								
							}
						}
					}
				}
			}
			
			/*--------------------------------------------------------------------------------------------------*/
				if($campaign_arr[1]['budget']>0)
				$package_category_array = array_unique($package_category_array);
				
				$annexure_details['bidding_details']['ddg_details'] = $invoice_bidding_details_arr;
				$annexure_details['bidding_details']['pkg_details'] = $package_category_array;
			/*--------------------------------------------------------------------------------------------------*/
			
			 $sql_insert_ddg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','2','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[2]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['ddg_details']))."','".$this->mysql_real_escape_custom(json_encode($category_name_arr))."','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
			$res_insert_ddg_log = parent::execQuery($sql_insert_ddg_log, $this->fin_con);			
			
			if(count($original_campaign_arr[1]['budget'])>0) {
				$sql_insert_pkg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','1','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[1]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['pkg_details']))."','','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
				$res_insert_pkg_log = parent::execQuery($sql_insert_pkg_log, $this->fin_con);
			}
				
		}
		if($campaign_arr[72]['budget']>0  || $campaign_arr[73]['budget']>0)
		{
			
			/*switch(strtolower($this->params['module']))
			{
				case 'cs':
				$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->local_d_jds);
				$row_pkg_cats = parent::fetchData($res_pkg_cats);
				break;
				case 'tme':
				if($this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->params['parentid'];
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->params['module'];
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}
				else{
					$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
					$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->local_tme_conn);
					$row_pkg_cats = parent::fetchData($res_pkg_cats);
				}
				break;
				case 'me':
				case 'jda':
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->params['parentid'];
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->params['module'];
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}
				else{
					$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
					$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->idc_con);
					$row_pkg_cats = parent::fetchData($res_pkg_cats);
				}
				break;
				default:
				die('Invalid Module');
				break;
			}*/
			//if($res_pkg_cats && parent::numRows($res_pkg_cats)>0)
			
			$sql_temp = "select GROUP_CONCAT(catid) AS catIds from tbl_bidding_details_intermediate where parentid='".$this -> params['parentid']."' and version='".$this -> params['version']."' ";
			$res_temp = parent::execQuery($sql_temp, $this->db_budgeting);
			$row_pkg_cats= mysql_fetch_assoc($res_temp);
			
			if($row_pkg_cats['catIds']=='' || $row_pkg_cats['catIds']==NULL)
			{
				$sql_main = "select GROUP_CONCAT(catid) AS catIds from tbl_bidding_details_intermediate_archive where parentid='".$this -> params['parentid']."' and version='".$this -> params['version']."' ";
				$res_main = parent::execQuery($sql_main, $this->db_budgeting);
				$row_pkg_cats= mysql_fetch_assoc($res_main);
			}
			
			
			if(count($row_pkg_cats)>0)
			{
				$catLst = $row_pkg_cats['catIds'];
				$catids_arr = explode(',',$catLst);
				if(count($catids_arr)>0)
				{
					//$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
					//$res_category = parent::execQuery($sql_category, $this->local_d_jds);

					$cat_params = array();
					$cat_params['page'] ='generateinvoicecontentclass';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,category_name,search_type';

					$where_arr  	=	array();			
					$where_arr['catid']			= implode(",",$catids_arr);
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
					{
						foreach($cat_res_arr['results'] as $key =>$row_category)
						{
							$category_pkg_name_arr[$row_category['catid']] = $row_category['category_name'];
						}
					}
					
					$annexure_details['omni_other_details']['cat_details']['cat_details'] = $category_pkg_name_arr;
					$pincode_areaname_arr = array();

					$annexure_details['omni_other_details']['pincode']['pincode'] = $payment_receipt_content['comp_gen_info']['Pincode'];
					$omnibgt_array[72]=$original_campaign_arr[72];
					$omnibgt_array[73]=$original_campaign_arr[73];
					$omnibgt_array[74]=$original_campaign_arr[74];
					$omnibgt_array[75]=$original_campaign_arr[75];
					$omnibgt_array[82]=$original_campaign_arr[82];
					$omnibgt_array[83]=$original_campaign_arr[83];
					 $sql_insert_omni_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','73','".$this->mysql_real_escape_custom(json_encode($omnibgt_array))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['omni_other_details']['cat_details']))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['omni_other_details']['pincode']))."','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
					$res_insert_pkg_log = parent::execQuery($sql_insert_omni_log, $this->fin_con);
				}
			}
		}
		if(($campaign_arr[1]['budget']>0 && ($campaign_arr[2]['budget'] <= 0 )) || $campaign_arr[10]['budget']>0)
		{
			/*switch(strtolower($this->params['module']))
			{
				case 'cs':
				$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->local_d_jds);
				$row_pkg_cats = parent::fetchData($res_pkg_cats);
				break;
				case 'tme':
				if($this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->params['parentid'];
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->params['module'];
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}
				else{
					$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
					$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->local_tme_conn);
					$row_pkg_cats = parent::fetchData($res_pkg_cats);
				}
				break;
				case 'me':
				case 'jda':
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->params['parentid'];
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->params['module'];
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}
				else{
					$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> params['parentid']."'";
					$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
					$row_pkg_cats = parent::fetchData($res_pkg_cats);
				}
				break;
				default:
				die('Invalid Module');
				break;
			}			
			//if($res_pkg_cats && parent::numRows($res_pkg_cats)>0)
			if(count($row_pkg_cats)>0)
			{
				$catLst = $row_pkg_cats['catIds'];
				$catLst = str_replace('|P|',',',$catLst);
				$catids_arr = explode(',',$catLst);
				$catids_arr = array_filter($catids_arr);
				if(count($catids_arr))
				{
					$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
					$res_category = parent::execQuery($sql_category, $this->local_d_jds);
					if($res_category && parent::numRows($res_category)>0)
					{
						while($row_category =  parent::fetchData($res_category))
						{
							$category_pkg_name_arr[$row_category['catid']] = $row_category['category_name'];
						}
					}
					
					if($campaign_arr[1]['budget']>0)
					{
						$annexure_details['bidding_details']['pkg_details'] = $category_pkg_name_arr;
						$annexure_details['package_other_details']['package_consist_of'] = array('-App Priority Listing','-Web Priority Listing','-Wap Priority Listing','-Phone Priority Listing');
					}
					
					if($campaign_arr[10]['budget']>0) {
						$national_listing_categories = $category_pkg_name_arr;
					}
					
				}
			}*/
			
			
			$sql_temp = "select GROUP_CONCAT(catid) AS catIds from tbl_bidding_details_intermediate where parentid='".$this -> params['parentid']."' and version='".$this -> params['version']."' ";
			$res_temp = parent::execQuery($sql_temp, $this->db_budgeting);
			$row_pkg_cats= mysql_fetch_assoc($res_temp);
			
			if($row_pkg_cats['catIds']=='' || $row_pkg_cats['catIds']==NULL)
			{
				$sql_main = "select GROUP_CONCAT(catid) AS catIds from tbl_bidding_details_intermediate_archive where parentid='".$this -> params['parentid']."' and version='".$this -> params['version']."' ";
				$res_main = parent::execQuery($sql_main, $this->db_budgeting);
				$row_pkg_cats= mysql_fetch_assoc($res_main);
			}
			
			
			
			//~ echo $row_pkg_catsss;
			//~ //print_r($row_pkg_cats);
			//~ echo $ayy_c = implode("','",$row_pkg_cats);die;
			
			if(count($row_pkg_cats)>0)
			{
					$catLst = $row_pkg_cats['catIds'];
					$catids_arr = explode(',',$catLst);
					if(count($catids_arr)>0)
					{
						 //$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
						//$res_category = parent::execQuery($sql_category, $this->local_d_jds);
						$cat_params = array();
						$cat_params['page'] ='generateinvoicecontentclass';
						$cat_params['data_city'] 	= $this->data_city;
						$cat_params['return']		= 'catid,category_name,search_type';

						$where_arr  	=	array();			
						$where_arr['catid']			= implode(",",$catids_arr);
						$cat_params['where']		= json_encode($where_arr);
						$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

						$cat_res_arr = array();
						if($cat_res!=''){
							$cat_res_arr =	json_decode($cat_res,TRUE);
						}

						if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
						{
							foreach($cat_res_arr['results'] as $key =>$row_category)
							{
								$category_pkg_name_arr[$row_category['catid']] = $row_category['category_name'];
							}
						}
						
						if($campaign_arr[1]['budget']>0)
						{
							$annexure_details['bidding_details']['pkg_details'] = $category_pkg_name_arr;
							$annexure_details['package_other_details']['package_consist_of'] = array('-App Priority Listing','-Web Priority Listing','-Wap Priority Listing','-Phone Priority Listing');
						}
						
						if($campaign_arr[10]['budget']>0) {
							$national_listing_categories = $category_pkg_name_arr;
						}
						
					}
				
			}
			
			
			if($campaign_arr[1]['budget']>0)
			{
				$pincode_areaname_arr = array();
				foreach ($category_bidding_arr as $catid=>$zone_pincode_arr)
				{
					foreach ($zone_pincode_arr as $zoneid => $pincode_arr)
					{
						$pincode_areaname_arr = array_merge($pincode_areaname_arr,array_keys($pincode_arr));
					}
				}
				
				
				$pincode_areaname_arr = array_unique($pincode_areaname_arr);
				$annexure_details['package_other_details']['pincode'] = $pincode_areaname_arr;

				 $sql_insert_pkg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','1','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[1]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['pkg_details']))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['package_other_details']))."','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
				$res_insert_pkg_log = parent::execQuery($sql_insert_pkg_log, $this->fin_con);
			}
			
		}
		
		if($campaign_arr[4]['budget']>0 || $campaign_arr[5]['budget']>0 || $campaign_arr[13]['budget']>0)
		{
			
			$qrysms = "select tcontractid,bid_value from tbl_smsbid_temp where bcontractid='".$this->params['parentid']."'";
			
			$sql_get_comp_banner = "SELECT cat_name FROM tbl_comp_banner_temp WHERE parentid='".$this->params['parentid']."'";
			
			$qrycatspon 		 = "SELECT cat_name FROM tbl_catspon_temp where parentid ='".$this->params['parentid']."' AND campaign_type='1'";
			
			switch(strtolower($this->params['module']))
			{
				case 'cs':
				$sql_get_comp_banner = parent::execQuery($sql_get_comp_banner, $this->local_d_jds);
				$resqrycatspon 		 = parent::execQuery($qrycatspon, $this->local_d_jds);
				$res_sms 		     = parent::execQuery($qrysms, $this->local_d_jds);
				break;
				case 'tme':
				$res_get_comp_banner = parent::execQuery($sql_get_comp_banner, $this->local_tme_conn);
				$resqrycatspon 		 = parent::execQuery($qrycatspon, $this->local_tme_conn);
				$res_sms 		     = parent::execQuery($qrysms, $this->local_tme_conn);
				break;
				case 'me':
				$res_get_comp_banner = parent::execQuery($sql_get_comp_banner, $this->idc_con);
				$resqrycatspon 		 = parent::execQuery($qrycatspon, $this->idc_con);
				$res_sms 		     = parent::execQuery($qrysms, $this->idc_con);
				break;
				case 'jda':
				$res_get_comp_banner = parent::execQuery($sql_get_comp_banner, $this->idc_con);
				$resqrycatspon 		 = parent::execQuery($qrycatspon, $this->idc_con);
				$res_sms 		     = parent::execQuery($qrysms, $this->idc_con);
				break;
				default:
				die('Invalid Module');
				break;
			}
			
			$comp_banner_cat_list = array();$catspon_banner_cat_list = array();
			if($res_get_comp_banner && parent::numRows($res_get_comp_banner) && $campaign_arr[5]['budget']>0)
			{
			   while($row_get_comp_banner = parent::fetchData($res_get_comp_banner))
			   {
				   $comp_banner_cat_list[] = trim($row_get_comp_banner['cat_name']);
			   }
			   
			   $comp_banner_cat_list = array_unique($comp_banner_cat_list);
			}
			
			if($resqrycatspon && parent::numRows($resqrycatspon) && $campaign_arr[13]['budget']>0)
			{
				while($rowcatspon = parent::fetchData($resqrycatspon))
			   {
				   $catspon_banner_cat_list[] = trim($rowcatspon['cat_name']);
			   }
			   
			   $catspon_banner_cat_list = array_unique($catspon_banner_cat_list);
			}
			
			if(count($comp_banner_cat_list) || count($catspon_banner_cat_list))
			{
				$total_banner_cat_list = array_merge($comp_banner_cat_list,$catspon_banner_cat_list);
				
				foreach($total_banner_cat_list as $catname)
				{
				  if(in_array($catname,$catspon_banner_cat_list))
				  $banner_arr[$catname]['spon_banner'] = 'Available';
				  else
				  $banner_arr[$catname]['spon_banner'] = 'Not Available';
				  
				  if(in_array($catname,$comp_banner_cat_list))
				  $banner_arr[$catname]['comp_banner'] = 'Available';
				  else
				  $banner_arr[$catname]['comp_banner'] = 'Not Available';
				  
				  
				}
				/*--------------------------------------------------------------------------------------------------*/
				   $annexure_details['banner_campaign']['banner_details'] = $banner_arr;/*banner campaign category details - 5 and 12 campaign*/
				/*--------------------------------------------------------------------------------------------------*/
				
				$sql_insert_banner_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','13','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[5]).json_encode($original_campaign_arr[13]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['banner_campaign']))."','','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
			    $res_insert_banner_log = parent::execQuery($sql_insert_banner_log, $this->fin_con);
				
			}
			
			if($res_sms && parent::numRows($res_sms) && $campaign_arr[4]['budget']>0)
			{				
				while($row_sms = parent::fetchData($res_sms))
				{
					$sms_map_arr[$row_sms['tcontractid']]['bidvalue'] = $row_sms['bid_value'];
				}
				
				
				if(count($sms_map_arr))
				$parentid_list = array_keys($sms_map_arr);
				
				if(count($parentid_list)>0)
				{
					$c2s_comp     ="select parentid,companyname from c2s_nonpaid where parentid IN ('".implode("','",$parentid_list)."') group by parentid";
					$res_c2s_comp = parent::execQuery($c2s_comp, $this->local_d_jds);
					while($row_c2s_comp = parent::fetchData($res_c2s_comp))
					{
						$sms_map_arr[$row_c2s_comp['parentid']]['compname'] = $row_c2s_comp['companyname'];
					}
				}
				/*--------------------------------------------------------------------------------------------------*/	
				    $annexure_details['smspromo_campaign']['sms_promo_details'] = $sms_map_arr;	/*sms promo campaign details - campaignid : 4 */
				/*--------------------------------------------------------------------------------------------------*/
				
				
				$sql_insert_smspromo_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','4','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[4]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['smspromo_campaign']))."','','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
			    $res_insert_smspromo_log = parent::execQuery($sql_insert_smspromo_log, $this->fin_con);
			    
			}
			
		}
			$sql_annexure = "SELECT campaignid,category_details,other_details AS campaign_other_details FROM tbl_invoice_annexure_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
			$res_annexure = parent::execQuery($sql_annexure, $this->fin_con);
			if($res_annexure && parent::numRows($res_annexure)>0)
			{
				while($row_annexure = parent::fetchData($res_annexure)){
					$annexure_content['annexure_content'][$row_annexure['campaignid']]['category_details'] = $row_annexure['category_details'];
					$annexure_content['annexure_content'][$row_annexure['campaignid']]['campaign_other_details'] = $row_annexure['campaign_other_details'];
				}
				$annexure_details['annexure_content']= $annexure_content['annexure_content'];
			}
		
			if($campaign_arr[10]['budget']>0)
			{
				
				$sql_national_temp      = "select Category_city, ContractTenure from tbl_national_listing_temp where parentid = '".$this->params['parentid']."'";
				
				switch(strtolower($this->params['module']))
				{
					case 'cs':
					$res_national_temp	= parent::execQuery($sql_national_temp, $this->local_d_jds);
					break;
					case 'tme':
					$res_national_temp 	= parent::execQuery($sql_national_temp, $this->local_tme_conn);
					break;
					case 'me':
					$res_national_temp	= parent::execQuery($sql_national_temp, $this->idc_con);
					break;
					case 'jda':
					$res_national_temp	= parent::execQuery($sql_national_temp, $this->idc_con);
					break;
					default:
					die('Invalid Module');
					break;
				}
				
				if($res_national_temp && parent::numRows($res_national_temp))
				{
					$row_national_temp	= parent::fetchData($res_national_temp);
					$nationalcityarr = explode('|#|',trim($row_national_temp['Category_city'],'|#|'));
					$annexure_details['national_listing_details'] = array('cityarray'=>$nationalcityarr, 'tenure'=>$row_national_temp['ContractTenure'],'categories'=>$national_listing_categories);
					$annexure_details['national_listing_other_details'] = array('cityarray'=>$nationalcityarr, 'tenure'=>$row_national_temp['ContractTenure']);
				}
				
				$sql_insert_smspromo_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->params['parentid']."','".$this->params['version']."','10','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[10]))."','".$this->mysql_real_escape_custom(json_encode($national_listing_categories))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['national_listing_other_details']))."','".date('Y-m-d H:i:s')."','".$this->params['data_city']."')";
			    $res_insert_smspromo_log = parent::execQuery($sql_insert_smspromo_log, $this->fin_con);
			    
				
			}
						
			
			//echo 'DDG category details arr <pre>';print_r($invoice_bidding_details_arr);
			//echo '<br>';
			//echo json_encode($category_name_arr);
			//echo '<br>';die;
			//echo 'package category details arr <pre>';print_r($package_category_array);
			
			//echo '<pre>annexure content';print_r($annexure_details);die;
			if($annexure_details != '')
			{
				$retAnnexure_details['errorCode']	=	0;
				$retAnnexure_details['data']		=	$annexure_details;
			}else{
				$retAnnexure_details['errorCode']	=	1;
			}
			
			//~ print_r($annexure_details);
			
			//~ if($res_insert_annexure_content_log){
				//~ $error['return_message']='receipt data logged';
				//~ $error['code']='1';
				//~ return $error; 
			//~ }
			
			return json_encode($retAnnexure_details);
	}
	
	/*function makeInvoiceRecieptId($stateName,$row_receipt){
		
		$today				=	date('Y-m-d');
		$new_day			=	date("d",strtotime($today));
		
        $getRecieptDet="SELECT  MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='reciept' ORDER BY id DESC LIMIT 1";
        $resRecieptDet = parent::execQuery($getRecieptDet, $this->db_payment);
        if(parent::numRows($resRecieptDet)>0){
            while($rowRcpt = parent::fetchData($resRecieptDet)){
                $recieptCountStart=$rowRcpt['increment_id'];
            }
        }else{
            $recieptCountStart=0;
        }

        $getInvoiceDet="SELECT  MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='invoice' ORDER BY id DESC LIMIT 1";
        $resInvoiceDet = parent::execQuery($getInvoiceDet, $this->db_payment);
        if(parent::numRows($resInvoiceDet)>0){
            while($rowInvc = parent::fetchData($resInvoiceDet)){
                $invoiceCountStart=$rowInvc['increment_id'];
            }
        }else{
            $invoiceCountStart=0;
        }
        $idArray=array();
        switch($stateName){
            case 'Gujarat': $stCode='GJ'; break;
            case 'Karnataka': $stCode='KA'; break;
            case 'Tamil Nadu': $stCode='TN'; break;
            case 'Delhi': $stCode='DL'; break;

            case 'Telangana': $stCode='TS'; break;
            case 'West Bengal': $stCode='WB'; break;
            case 'Maharashtra': $stCode='MH'; break;
            case 'Andhra Pradesh': $stCode='AP'; break;

            case 'Assam': $stCode='AS'; break;
            case 'Jharkhand': $stCode='JH'; break;
            case 'Chhattisgarh': $stCode='CT'; break;
            case 'Orissa': $stCode='OR'; break;

            case 'Punjab': $stCode='PB'; break;
            case 'Kerala': $stCode='KL'; break;
            case 'Haryana': $stCode='HR'; break;
            case 'Sikkim': $stCode='SK'; break;

            case 'Bihar': $stCode='BH'; break;
            case 'Rajasthan': $stCode='RJ'; break;
            case 'Uttar Pradesh': $stCode='UP'; break;
            case 'Manipur': $stCode='MN'; break;

            case 'Uttarakhand': $stCode='UT'; break;
            case 'Pondicherry': $stCode='PY'; break;
            case 'Meghalaya': $stCode='ME'; break;
            case 'Himachal Pradesh': $stCode='HP'; break;

            case 'Mizoram': $stCode='MI'; break;
            case 'Tripura': $stCode='TR'; break;
            case 'Nagaland': $stCode='NL'; break;
            case 'Andaman And Nicobar': $stCode='AN'; break;

            case 'Jammu And Kashmir': $stCode='JK'; break;
            case 'Madhya Pradesh': $stCode='MP'; break;
            case 'Chandigarh': $stCode='CH'; break;
            case 'Arunachal Pradesh': $stCode='AR'; break;

            case 'Goa': $stCode='GA'; break;
            case 'Dadra Nager Haveli': $stCode='DN'; break;
            case 'Daman And Diu': $stCode='DD'; break;
            case 'Lakshadweep': $stCode='LD'; break;
            default: $stCode='MH'; break;
        }
        
      
			
        $anxHdrArr=json_decode($row_receipt['annexure_header_content'],TRUE);
        $payMode=$anxHdrArr['payment_mode'];
        if($payMode=='NON-ECS'){
            $mod='SN';
        }else{
            $mod='SE';
        }

        $month		       =	date("m",strtotime($this->params['invDate']));
		$year		       =	date("y",strtotime($this->params['invDate']));
        $incrInvNum=$invoiceCountStart + 1;
        $incrRcpNum=$recieptCountStart + 1;
        $invcNo = str_pad($incrInvNum, 8, 0, STR_PAD_LEFT);
        $rcptNo = str_pad($incrRcpNum, 8, 0, STR_PAD_LEFT);
        $invCode=$stCode.$month.$year.$mod.$invcNo;
        $rcptCode=$stCode.$month.$year.'RC'.$rcptNo;
        $idArray['invoice']=$invCode;
        $idArray['receipt']=$rcptCode;
        $now        = date('Y-m-d H:i:s');
		
		if($this->params['action'] == 1){
            $instrument_details_arr = json_decode($this->getInstrumentDetailsArrApproval($this->params['parentid'],$this->params['version'],$this->generate_inv,$this->invDate),true);
		
        }else{
		$instrument_details_arr = json_decode($this->getInstrumentDetailsArr($this->params['parentid'],$this->params['version']),true);
        }
        
        if($instrument_details_arr['errorCode']	==	0){
			$payment_receipt_content['instrument_details'] = $instrument_details_arr['data'];
		}
        
		$instrument_id			=  $payment_receipt_content['instrument_details']['all_instruments'][0]['id'];
		
	
		//~ $instrument_id			=  'CHQ22APR12A9B0Z4';
		$today					=	date('Y-m-d H:i:s');
        $checkExistQry="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."'";
        $checkExistRes = parent::execQuery($checkExistQry, $this->db_payment);
        if(parent::numRows($checkExistRes)>0){
                    $codeArray=array();
            $idArray_auto = array();
            $chk_invoice_arr = array();
            $i=0;
            while($rowRes = parent::fetchData($checkExistRes)){
				
				$chk_invoice_arr[$i] = $rowRes['inv_rcpt_code'];
				$idArray_auto[$i]=$rowRes['id'];
				$codeArray[$i]=$rowRes['inv_rcpt_code'];
                $i++;
            }
         
				if($chk_invoice_arr[0]!='' && $chk_invoice_arr[1]!='')
				{
					$chk_invoice=substr($chk_invoice_arr[0], 0, 5);
					$chk_rcpt=substr($chk_invoice_arr[1], 0, 5);
					$chk_orig=substr($idArray['invoice'], 0, 5);
					$chk_orig_rcpt=substr($idArray['receipt'], 0, 5);
					
					if($this->params['parentid'] == 'PXX11.XX11.130227112917.W7V9' && $this->params['invDate']=='2017-10-25')
					{
						$invCode ='UP1017SN00573547';
						$codeArray[0]='UP1017SN00573547';
					}
					
						if(($chk_invoice !== $chk_orig) && ($chk_invoice!='' && $chk_orig!=''))
						{
							$upd_invoice ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$invCode."',insert_date=NOW(),increment_id='".$incrInvNum."',instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='invoice' AND id='".$idArray_auto[0]."'";
							$inv_res = parent::execQuery($upd_invoice, $this->db_payment);
							$idArray['invoice']=$invCode;
							$idArray['receipt']=$codeArray[1];
						}
						
						if(($chk_rcpt !== $chk_orig_rcpt) && ($chk_orig_rcpt!='' && $chk_rcpt !=''))
						{
							$upd_rcpt ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$rcptCode."',insert_date=NOW(),increment_id='".$incrRcpNum."',instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='reciept' AND id='".$idArray_auto[1]."'";
							$inv_res = parent::execQuery($upd_rcpt, $this->db_payment);
							
							$idArray['invoice']=$codeArray[0];
							$idArray['receipt']=$rcptCode;
						}
						
						if(($chk_invoice === $chk_orig) && ($chk_rcpt === $chk_orig_rcpt))
						{
							$upd_invoice_both ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$codeArray[0]."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='invoice' AND id='".$idArray_auto[0]."'";
							$inv_res = parent::execQuery($upd_invoice_both, $this->db_payment);
							
							$upd_rcpt_both ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$codeArray[1]."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='reciept' AND id='".$idArray_auto[1]."'";
							$inv_res = parent::execQuery($upd_rcpt_both, $this->db_payment);
							
							$idArray['invoice']=$codeArray[0];
							$idArray['receipt']=$codeArray[1];
						}
						
				}else
				{
					
					$upd_invoice_blank ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$invCode."',insert_date=NOW(),increment_id='".$incrInvNum."',instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='invoice' AND id='".$idArray_auto[0]."'";
						$inv_res = parent::execQuery($upd_invoice_blank, $this->db_payment);
						
					 $upd_rcpt_blank ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$rcptCode."',insert_date=NOW(),increment_id='".$incrRcpNum."',instrumentId='".$this->params['instrumentid']."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$this->params['invDate']."' AND type='reciept' AND id='".$idArray_auto[1]."'";
						$inv_res = parent::execQuery($upd_rcpt_blank, $this->db_payment);
						
					$idArray['invoice']=$invCode;
					$idArray['receipt']=$rcptCode;	
							
				}

        }else{
			
			$checkExistQry="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND (approval_date IS NULL OR DATE(approval_date) = '0000-00-00' OR approval_date ='')";
			$checkExistRes = parent::execQuery($checkExistQry, $this->db_payment);
			if(parent::numRows($checkExistRes)>0){
	            $codeArray=array();
	            $codeArray_id =array();
	            $i=0;
	            while($rowRes = parent::fetchData($checkExistRes)){
	                $codeArray[$i]=$rowRes['inv_rcpt_code'];
	                 $codeArray_id[$i]=$rowRes['id'];
	                $i++;
	            }
	            
	            $insInvoiceQry_inv="UPDATE tbl_reciept_invoice_record SET approval_date='".$this->params['invDate']."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."' WHERE type='invoice' AND parentid='".$this->params['parentid']."' AND id='".	$codeArray_id[0]."'";
				$insInvoiceDet = parent::execQuery($insInvoiceQry_inv, $this->db_payment);
	            
	            
	            $insInvoiceQry_rcpt="UPDATE tbl_reciept_invoice_record SET approval_date='".$this->params['invDate']."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."' WHERE type='reciept' AND parentid='".$this->params['parentid']."' AND id='".$codeArray_id[1]."'";
	            $insInvoiceDet = parent::execQuery($insInvoiceQry_rcpt, $this->db_payment);
	            
	            $idArray['invoice']=$codeArray[0];
	            $idArray['receipt']=$codeArray[1];
			}else{
				$insInvoiceQry="INSERT INTO tbl_reciept_invoice_record SET inv_rcpt_code='".$invCode."', type='invoice', increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$now."',approval_date='".$this->params['invDate']."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."'";
	            $insInvoiceDet = parent::execQuery($insInvoiceQry, $this->db_payment);
	
	            $insReceiptQry="INSERT INTO tbl_reciept_invoice_record SET inv_rcpt_code='".$rcptCode."', type='reciept', increment_id='".$incrRcpNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$now."',approval_date='".$this->params['invDate']."',insert_date=NOW(),instrumentId='".$this->params['instrumentid']."'";
	            $rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->db_payment);
	         }
        }
        
        
        return $idArray;
	}
	*/
	
	function makeInvoiceRecieptId($stateName,$row_receipt,$invNum,$rcpNum){
		
		$today				=	date('Y-m-d');
		$new_day			=	date("d",strtotime($today));
		
		$idArray=array();
		
        switch(strtolower($stateName)){
            case 'gujarat': $stCode='GJ'; break;
            case 'karnataka': $stCode='KA'; break;
            case 'tamil nadu': $stCode='TN'; break;
            case 'delhi': $stCode='DL'; break;

            case 'telangana': $stCode='TS'; break;
            case 'west bengal': $stCode='WB'; break;
            case 'maharashtra': $stCode='MH'; break;
            case 'andhra pradesh': $stCode='AP'; break;
            case 'andhra pradesh (new)': $stCode='AP'; break;

            case 'assam': $stCode='AS'; break;
            case 'jharkhand': $stCode='JH'; break;
            case 'chhattisgarh': $stCode='CT'; break;
            case 'orissa': $stCode='OR'; break;

            case 'punjab': $stCode='PB'; break;
            case 'kerala': $stCode='KL'; break;
            case 'haryana': $stCode='HR'; break;
            case 'sikkim': $stCode='SK'; break;

            case 'bihar': $stCode='BH'; break;
            case 'rajasthan': $stCode='RJ'; break;
            case 'uttar pradesh': $stCode='UP'; break;
            case 'manipur': $stCode='MN'; break;

            case 'uttarakhand': $stCode='UT'; break;
            case 'pondicherry': $stCode='PY'; break;
            case 'meghalaya': $stCode='ME'; break;
            case 'himachal pradesh': $stCode='HP'; break;

            case 'mizoram': $stCode='MI'; break;
            case 'tripura': $stCode='TR'; break;
            case 'nagaland': $stCode='NL'; break;
            case 'andaman and nicobar': $stCode='AN'; break;

            case 'jammu and kashmir': $stCode='JK'; break;
            case 'madhya pradesh': $stCode='MP'; break;
            case 'chandigarh': $stCode='CH'; break;
            case 'arunachal pradesh': $stCode='AR'; break;

            case 'goa': $stCode='GA'; break;
            case 'dadra nager haveli': $stCode='DN'; break;
            case 'daman and diu': $stCode='DD'; break;
            case 'lakshadweep': $stCode='LD'; break;
            default: $stCode='NAS'; break;
        }
        
		
	//	 echo $this->instrumentid;die;
		//$start_month = date('Y-m-02',strtotime('this month'));
		//$start_month = '2018-05-11';
		//$end_month  = date('Y-m-01',strtotime('this month'));
		//$end_month  = '2018-05-20';
        
			$dealclosedInfo  = $this->dealclosedinf();
            if($this->instrumentid == '')
			$this->instrumentid = $dealclosedInfo['instrumentId'];
			else
			$this->instrumentid = $this->instrumentid;
			
			if($this->invDate == '')
			$this->invDate = date('Y-m-d',strtotime($dealclosedInfo['entryDate']));
			else
			$this->invDate = $this->invDate;
			
        
        $mod = 'SN';
        $month = date('m',strtotime($this->invDate));
		
	       $getReciept_annex_inv_Det	=	"SELECT invoiceno,increment_id,date_time,inv_rcpt_code,type FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE parentid='".$this->params['parentid']."' AND date_time>='".$this->invDate."' AND date_time<='".$this->invDate."' AND instrumentid ='".$this->instrumentid."' AND version='".$this->params['version']."' ORDER BY id DESC";//check not a new month
	       $resReciept_annex_inv_Det = parent::execQuery($getReciept_annex_inv_Det, $this->fin_con);
	        if(mysql_num_rows($resReciept_annex_inv_Det)>0){
	            while($rowRcpt_inv_annex = mysql_fetch_assoc($resReciept_annex_inv_Det)){
					
					if($rowRcpt_inv_annex['inv_rcpt_code'] == '' && $rowRcpt_inv_annex['type'] == 'invoice')
					{
						
						$getInv_annex_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='invoice' ORDER BY id DESC LIMIT 1"; // For Both
						$resInv_annex_old = parent::execQuery($getInv_annex_old, $this->fin_con);
						$inv_num_rows = mysql_num_rows($resInv_annex_old);
						if($inv_num_rows>0){
							$rowinv_annex_old = mysql_fetch_assoc($resInv_annex_old);
								$nonecsCountStartINV=$rowinv_annex_old['increment_id'];
								$checkedmonth=$month;                              
						}else{
								$nonecsCountStartINV=0;
						}
					}else
					{
					
						$type_invno[$rowRcpt_inv_annex['type']] = $rowRcpt_inv_annex['inv_rcpt_code'];
						$type_incre[$rowRcpt_inv_annex['type']] = $rowRcpt_inv_annex['increment_id'];	
				
						
						
						$chk_invoice=substr($rowRcpt_inv_annex['inv_rcpt_code'], 0, 2);
						if($invNum!='')
						{
							if(($stCode == $chk_invoice && $rowRcpt_inv_annex['type'] == 'invoice') &&  ($invNum==$type_invno['invoice']))
							{
								
								$idArray['invoice']=$type_invno['invoice'];
								$idArray['increment_id']=$type_incre['invoice'];
								$checkedmonth=date("m", strtotime($rowRcpt_inv_annex['date_time']));           
							}else
							{
								
								$getInv_annex_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='invoice' ORDER BY id DESC LIMIT 1"; // For Both
								$resInv_annex_old = parent::execQuery($getInv_annex_old, $this->fin_con);
								$inv_num_rows = mysql_num_rows($resInv_annex_old);
								if($inv_num_rows>0){
									$rowinv_annex_old = mysql_fetch_assoc($resInv_annex_old);
										$nonecsCountStartINV=$rowinv_annex_old['increment_id'];
										$checkedmonth=$month;                              
								}else{
										$nonecsCountStartINV=0;
								}
							}
						}else
						{
							if(($stCode == $chk_invoice && $rowRcpt_inv_annex['type'] == 'invoice'))
							{
								
								$idArray['invoice']=$type_invno['invoice'];
								$idArray['increment_id']=$type_incre['invoice'];
								$checkedmonth=date("m", strtotime($rowRcpt_inv_annex['date_time']));           
							}else
							{
								
								$getInv_annex_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='invoice' ORDER BY id DESC LIMIT 1"; // For Both
								$resInv_annex_old = parent::execQuery($getInv_annex_old, $this->fin_con);
								$inv_num_rows = mysql_num_rows($resInv_annex_old);
								if($inv_num_rows>0){
									$rowinv_annex_old = mysql_fetch_assoc($resInv_annex_old);
										$nonecsCountStartINV=$rowinv_annex_old['increment_id'];
										$checkedmonth=$month;                              
								}else{
										$nonecsCountStartINV=0;
								}
							}
						}
					}
					
					if($rowRcpt_inv_annex['inv_rcpt_code'] == '' && $rowRcpt_inv_annex['type'] == 'reciept')
					{
						
						$getRecieptDet_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='reciept' ORDER BY id DESC LIMIT 1"; // For Both
						$resRecieptDet_old = parent::execQuery($getRecieptDet_old, $this->fin_con);
						$recipt_num_rows = mysql_num_rows($resRecieptDet_old);
						if($recipt_num_rows>0){
							$rowRcpt_old = mysql_fetch_assoc($resRecieptDet_old);
								$nonecsCountStartRC=$rowRcpt_old['increment_id'];
								$checkedmonth=$month;                              
						}else{
								$nonecsCountStartRC=0;
						}
					}else
					{
						$type_invno[$rowRcpt_inv_annex['type']] = $rowRcpt_inv_annex['inv_rcpt_code'];
						$type_incre[$rowRcpt_inv_annex['type']] = $rowRcpt_inv_annex['increment_id'];
						$chk_reciept=substr($rowRcpt_inv_annex['inv_rcpt_code'], 0, 2);
						if($rcpNum!='')
						{
							if(($stCode == $chk_reciept  && $rowRcpt_inv_annex['type'] == 'reciept') && ($rcpNum==$type_invno['reciept']))
							{
								$idArray['reciept']=$type_invno['reciept'];
								$idArray['increment_id']=$type_incre['reciept'];
								$checkedmonth=date("m", strtotime($rowRcpt_inv_annex['date_time']));   
								
										
							}else
							{
								$getRecieptDet_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='reciept' ORDER BY id DESC LIMIT 1"; // For Both
								$resRecieptDet_old = parent::execQuery($getRecieptDet_old, $this->fin_con);
								$recipt_num_rows = mysql_num_rows($resRecieptDet_old);
								if($recipt_num_rows>0){
									$rowRcpt_old = mysql_fetch_assoc($resRecieptDet_old);
										$nonecsCountStartRC=$rowRcpt_old['increment_id'];
										$checkedmonth=$month;                              
								}else{
										$nonecsCountStartRC=0;
								}
							}
						}else
						{
							if(($stCode == $chk_reciept  && $rowRcpt_inv_annex['type'] == 'reciept'))
							{
								$idArray['reciept']=$type_invno['reciept'];
								$idArray['increment_id']=$type_incre['reciept'];
								$checkedmonth=date("m", strtotime($rowRcpt_inv_annex['date_time']));   
								
										
							}else
							{
								$getRecieptDet_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='reciept' ORDER BY id DESC LIMIT 1"; // For Both
								$resRecieptDet_old = parent::execQuery($getRecieptDet_old, $this->fin_con);
								$recipt_num_rows = mysql_num_rows($resRecieptDet_old);
								if($recipt_num_rows>0){
									$rowRcpt_old = mysql_fetch_assoc($resRecieptDet_old);
										$nonecsCountStartRC=$rowRcpt_old['increment_id'];
										$checkedmonth=$month;                              
								}else{
										$nonecsCountStartRC=0;
								}
							}
						}
					}
					
					
					
	            }
	        }else{
	         
	           $getinv_annex_new="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='invoice' ORDER BY id DESC LIMIT 1"; // For Both
				   $resinv_annex_new = parent::execQuery($getinv_annex_new, $this->fin_con);
				  $inv_num_rows = mysql_num_rows($resinv_annex_new);
				if($inv_num_rows>0){
					
						$rowinv_new = mysql_fetch_assoc($resinv_annex_new);
						if($rowinv_new['increment_id']!='')
						{
							$nonecsCountStartINV=$rowinv_new['increment_id'];
							$checkedmonth=$month;                              
						}else
						{
							$nonecsCountStartINV=0;
						}
					
				}else{
						$nonecsCountStartINV=0;
				}
			
				$getRecieptDet_new="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='reciept' ORDER BY id DESC LIMIT 1"; // For Both
					$resRecieptDet_new = parent::execQuery($getRecieptDet_new, $this->fin_con);
					$recipt_num_rows = mysql_num_rows($resRecieptDet_new);
				if($recipt_num_rows>0){
					
						$rowRcpt_new = mysql_fetch_assoc($resRecieptDet_new);
						if($rowRcpt_new['increment_id']!='')
						{
							$nonecsCountStartRC=$rowRcpt_new['increment_id'];
							$checkedmonth=$month;
						}else
						{
							$nonecsCountStartRC=0;
						}                              
					
				}else{
						$nonecsCountStartRC=0;
				}
	        }
	      
		
		if($stCode != 'NAS'){
			
			 switch(strtolower($this->params['data_city'])){
				case 'mumbai': $invcode=0; break;
				case 'delhi': $invcode=1; break;
				case 'kolkata': $invcode=2; break;
				case 'bangalore': $invcode=3; break;
				case 'chennai': $invcode=4; break;
				case 'pune': $invcode=5; break;
				case 'hyderabad': $invcode=6; break;
				case 'ahmedabad': $invcode=7; break;
				case 'remote_city': $invcode=8; break;
				default:$invcode=8; break;
			}
			
			
			if($mod == 'SN'){
				
				$month		       =	$month = date('m',strtotime($this->invDate));;
				//$year		       =	date("y");
				$year		       =	date("y",strtotime($this->invDate));
				/*$incrInvNum	       =	$nonecsCountStartINV + 1;
				$RcipNum	   	   =	$nonecsCountStartRC + 1;
				$invcNo 		   = 	str_pad($incrInvNum, 7, 0, STR_PAD_LEFT);
				$invCode		   =	$stCode.$month.$year.$mod.$invcode.$invcNo;
				$rcpNo 		   	   = 	str_pad($RcipNum, 7, 0, STR_PAD_LEFT);
				$rcptCode		   =	$stCode.$month.$year.'RC'.$invcode.$invcNo;*/
				
				
				if($idArray['invoice'] == '')
				{
					$incrInvNum	       		=	$nonecsCountStartINV + 1;
					$invcNo 		   		= 	str_pad($incrInvNum, 7, 0, STR_PAD_LEFT);
					$invCode		   		=	$stCode.$month.$year.$mod.$invcode.$invcNo;
					$idArray['invoice'] 	= $invCode;
					$idArray['annexure'] 	= $invCode;
					$idArray['increment_id'] 	= $incrInvNum;
				}else
				{
					$idArray['invoice'] 		= $idArray['invoice'];
					$idArray['increment_id'] 	= $idArray['increment_id'];
				}
				if($idArray['reciept'] == '')
				{
					$RcipNum	   	   =	$nonecsCountStartRC + 1;
					$rcpNo 		   	   = 	str_pad($RcipNum, 7, 0, STR_PAD_LEFT);
					$rcptCode		   =	$stCode.$month.$year.'RC'.$invcode.$rcpNo;
					$idArray['reciept'] = $rcptCode;
					$idArray['increment_id'] = $RcipNum;	
				}else
				{
					$idArray['reciept'] = $idArray['reciept'];
					$idArray['increment_id'] = $idArray['increment_id'];
				}
				
				
				
				
				/*$idArray['invoice'] = $invCode;
				$idArray['annexure'] = $invCode;
				$idArray['reciept'] = $rcptCode;*/
					
			}
	        $now        = date('Y-m-d H:i:s');
		}else{
			$idArray	=	array();
		}
	
		
	
		$today					=	date('Y-m-d');
		
		if($mod == 'SN'){
			
			$selectnonEcsMOnth	= "SELECT invoiceno,increment_id,date_time,inv_rcpt_code,type FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE parentid='".$this->params['parentid']."' AND date_time>='".$this->invDate."' AND date_time<='".$this->invDate."' AND instrumentid ='".$this->instrumentid."' AND version='".$this->params['version']."' ORDER BY id DESC";
			$resnonEcsDet = parent::execQuery($selectnonEcsMOnth, $this->fin_con);
			while($rowRcpt_chk 		= 	mysql_fetch_assoc($resnonEcsDet)){
				$type_cat[$rowRcpt_chk['type']] = $rowRcpt_chk['inv_rcpt_code'];
				 $type_inc[$rowRcpt_chk['type']] = $rowRcpt_chk['increment_id'];
				 $type_type['type'][$rowRcpt_chk['type']] = $rowRcpt_chk['type'];
			}
	        //$rowRcpt_chk 		= 	mysql_fetch_assoc($resnonEcsDet);
	        $rownumRcpt 	= 	mysql_num_rows($resnonEcsDet);
	        $ecsDateTime	=	$rowRcpt_chk['date_time'];
	        $nonecsCount		=	$rownumRcpt;
	        if($stCode != 'NAS'){
		        if($nonecsCount > 0){
					
					if($stCode != 'NAS')
					{
						
						while($rowRcpt_chk 		= 	mysql_fetch_assoc($resnonEcsDet)){
							$type_cat[$rowRcpt_chk['type']] = $rowRcpt_chk['inv_rcpt_code'];
							$type_inc[$rowRcpt_chk['type']] = $rowRcpt_chk['increment_id'];
							$type_type['type'][$rowRcpt_chk['type']] = $rowRcpt_chk['type'];
						}
						
						
						if($type_cat['reciept'] == '')
						{
							$type_cat['reciept'] = $idArray['reciept'];
							$type_inc['reciept'] = $idArray['increment_id'];
						}
						else
						{
							if($rcpNum==$type_cat['reciept'])
							{
								
								$type_cat['reciept'] = $type_cat['reciept'];
								$type_inc['reciept'] = $type_inc['reciept'];
								
							}else
							{
								$type_cat['reciept'] = $idArray['reciept'];
								$type_inc['reciept'] = $idArray['increment_id'];
							}
						}
						
						
						
						if($type_cat['invoice'] == '' && $type_inc['invoice']=='')
						{
							$insInvQry="INSERT ignore INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$idArray['invoice']."', type='invoice',increment_id='".$idArray['increment_id']."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."', invoiceno='".$idArray['invoice']."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW(),state_invoice_final='inserted_module'";
								$rcptInvoiceDet = parent::execQuery($insInvQry, $this->fin_con);
							
							$type_cat['invoice'] = $idArray['invoice'];
							$type_inc['invoice'] = $idArray['increment_id'];
						}
						else if($type_cat['invoice'] == '' && $type_inc['invoice']!='')
						{
							 $updatenonEcsQryinv_ex	=	"UPDATE  db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$idArray['invoice']."',insert_date=NOW(),invoice_create='1',state_invoice_final='updated_exist',increment_id='".$idArray['increment_id']."' WHERE parentid='".$this->params['parentid']."' AND instrumentid ='".$this->instrumentid."' AND type='invoice' AND date_time='".$this->invDate."'";
							$upnonEcsDet = parent::execQuery($updatenonEcsQryinv_ex, $this->fin_con);
							$type_cat['invoice'] = $idArray['invoice'];
							$type_inc['invoice'] = $idArray['increment_id'];
						}
						else
						{
							if($invNum==$type_cat['invoice'])
							{
								$type_cat['invoice'] = $type_cat['invoice'];
								$type_inc['invoice'] = $type_inc['invoice'];
							}else
							{
								$type_cat['invoice'] = $idArray['invoice'];
								$type_inc['invoice'] = $idArray['increment_id'];
							}
						}
				
						
						if($type_cat['reciept'] !='')
						{
						
							$slect_invoice_present         = "SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE inv_rcpt_code='".$type_cat['reciept']."' AND type='reciept'";
                            $conQuery_inv_pres            = parent::execQuery($slect_invoice_present, $this->fin_con);
                            $numRows_in_pres            = mysql_num_rows($conQuery_inv_pres);
                            if($numRows_in_pres ==0)
                            { 
						
								$updatenonEcsQryrcpt	=	"UPDATE ignore db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$type_cat['reciept']."', insert_date=NOW(),invoice_create='1',state_invoice_final='updated',increment_id='".$type_inc['reciept']."' WHERE parentid='".$this->params['parentid']."' AND instrumentid ='".$this->instrumentid."' AND type='reciept' AND date_time='".$this->invDate."'";
								$upnonEcsDet = parent::execQuery($updatenonEcsQryrcpt, $this->fin_con);
							}
						}
						
						if($type_cat['invoice']!='')
						{
								
								$slect_invoice_present_inv         = "SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE inv_rcpt_code='".$type_cat['invoice']."' AND type='invoice'";
                                $conQuery_inv_pres_inv            = parent::execQuery($slect_invoice_present_inv, $this->fin_con);
                                $numRows_in_pres_inv            = mysql_num_rows($conQuery_inv_pres_inv);
                                if($numRows_in_pres_inv ==0) 
                                {
									$updatenonEcsQryinv	=	"UPDATE ignore db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$type_cat['invoice']."',insert_date=NOW(),invoice_create='1',state_invoice_final='updated',increment_id='".$type_inc['invoice']."' WHERE parentid='".$this->params['parentid']."' AND instrumentid ='".$this->instrumentid."' AND type='invoice' AND date_time='".$this->invDate."'";
									$upnonEcsDet = parent::execQuery($updatenonEcsQryinv, $this->fin_con);
								}
						}
							$idArray['invoice'] = $type_cat['invoice'];
							$idArray['annexure'] = $type_cat['invoice'];
							$idArray['reciept'] = $type_cat['reciept'];
						
						
					}
					
				}else{
					
					if($stCode != 'NAS')
					{
						
						//$idArray['invoice'] = 'MP0119SN80049207';
						$slect_invoice_present 		= "SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE inv_rcpt_code='".$idArray['invoice']."' AND type='invoice'";
						$conQuery_inv_pres			=	parent::execQuery($slect_invoice_present, $this->fin_con);
						$numRows_in_pres			= mysql_num_rows($conQuery_inv_pres);
						if($numRows_in_pres ==0)
						{
							$log_al = "INSERT INTO db_invoice.tbl_invoice_log SET parentid ='".$this->params['parentid']."',
							instrumentid  ='".$this->instrumentid."',
							data_city = '".addslashes($this->params['data_city'])."',
							version = '".$this->params['version']."',
							source = '".$this->params['module']."',
							path = 'duplicatecheck',
							html = '".$idArray['invoice']."'";
							$log_res =parent::execQuery($log_al, $this->fin_con); 
							
							if($idArray['invoice']!='')
							{
								$insReceiptQry="INSERT ignore INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$idArray['reciept']."', type='reciept', increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."', invoiceno='".$idArray['invoice']."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW(),state_invoice_final='inserted_module'";
									$rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->fin_con);
								$insInvQry="INSERT ignore INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$idArray['invoice']."', type='invoice',increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."', invoiceno='".$idArray['invoice']."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW(),state_invoice_final='inserted_module'";
								$rcptInvoiceDet = parent::execQuery($insInvQry, $this->fin_con);
							}
							
						
							$idArray['invoice'] = $idArray['invoice'];
							$idArray['annexure'] = $idArray['invoice'];
							$idArray['reciept'] = $idArray['reciept'];
						}else
						{
							$new_invoice = $this->duplicate_invoice($stateName,$row_receipt);
							$slect_invoice_present 		= "SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE inv_rcpt_code='".$new_invoice['invoice']."' AND type='invoice'";
							$conQuery_inv_pres			=	parent::execQuery($slect_invoice_present, $this->fin_con);
							$numRows_in_pres			= mysql_num_rows($conQuery_inv_pres);
							
							if($numRows_in_pres == 0)
							{
								
								$log_al = "INSERT INTO db_invoice.tbl_invoice_log SET parentid ='".$this->params['parentid']."',
								instrumentid  ='".$this->instrumentid."',
								data_city = '".addslashes($this->params['data_city'])."',
								version = '".$this->params['version']."',
								source = '".$this->params['module']."',
								path = 'duplicatecheck_new',
								html = '".$new_invoice['invoice']."'";
								$log_res =parent::execQuery($log_al, $this->fin_con); 
								
								if($new_invoice['invoice']!='')
								{
									$insReceiptQry="INSERT ignore INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$new_invoice['reciept']."', type='reciept', increment_id='".$new_invoice['increment_id']."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."', invoiceno='".$new_invoice['invoice']."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW(),state_invoice_final='inserted_dupli_module'";
									$rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->fin_con);
							
								   $insInvQry="INSERT ignore INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".$new_invoice['invoice']."', type='invoice',increment_id='".$new_invoice['increment_id']."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."', invoiceno='".$new_invoice['invoice']."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW(),state_invoice_final='inserted_dupli_module'";
									$rcptInvoiceDet = parent::execQuery($insInvQry, $this->fin_con);
								}
							
								$idArray['invoice'] = $new_invoice['invoice'];
								$idArray['annexure'] = $new_invoice['invoice'];
								$idArray['reciept'] = $new_invoice['reciept'];
							}
							
						}
					}
				}
			 }
			
		}
		
		
		
        return $idArray;
	}
	
	
	function duplicate_invoice($stateName,$row_receipt)
	{
		
		switch(strtolower($stateName)){
            case 'gujarat': $stCode='GJ'; break;
            case 'karnataka': $stCode='KA'; break;
            case 'tamil nadu': $stCode='TN'; break;
            case 'delhi': $stCode='DL'; break;

            case 'telangana': $stCode='TS'; break;
            case 'west bengal': $stCode='WB'; break;
            case 'maharashtra': $stCode='MH'; break;
            case 'andhra pradesh': $stCode='AP'; break;
            case 'andhra pradesh (new)': $stCode='AP'; break;

            case 'assam': $stCode='AS'; break;
            case 'jharkhand': $stCode='JH'; break;
            case 'chhattisgarh': $stCode='CT'; break;
            case 'orissa': $stCode='OR'; break;

            case 'punjab': $stCode='PB'; break;
            case 'kerala': $stCode='KL'; break;
            case 'haryana': $stCode='HR'; break;
            case 'sikkim': $stCode='SK'; break;

            case 'bihar': $stCode='BH'; break;
            case 'rajasthan': $stCode='RJ'; break;
            case 'uttar pradesh': $stCode='UP'; break;
            case 'manipur': $stCode='MN'; break;

            case 'uttarakhand': $stCode='UT'; break;
            case 'pondicherry': $stCode='PY'; break;
            case 'meghalaya': $stCode='ME'; break;
            case 'himachal pradesh': $stCode='HP'; break;

            case 'mizoram': $stCode='MI'; break;
            case 'tripura': $stCode='TR'; break;
            case 'nagaland': $stCode='NL'; break;
            case 'andaman and nicobar': $stCode='AN'; break;

            case 'jammu and kashmir': $stCode='JK'; break;
            case 'madhya pradesh': $stCode='MP'; break;
            case 'chandigarh': $stCode='CH'; break;
            case 'arunachal pradesh': $stCode='AR'; break;

            case 'goa': $stCode='GA'; break;
            case 'dadra nager haveli': $stCode='DN'; break;
            case 'daman and diu': $stCode='DD'; break;
            case 'lakshadweep': $stCode='LD'; break;
            default: $stCode='NAS'; break;
        }
         $mod = 'SN';
        $month = date('m',strtotime($this->invDate));
			$getInv_annex_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='invoice' ORDER BY id DESC LIMIT 1"; // For Both
			$resInv_annex_old = parent::execQuery($getInv_annex_old, $this->fin_con);
			$inv_num_rows = mysql_num_rows($resInv_annex_old);
			if($inv_num_rows>0){
				$rowinv_annex_old = mysql_fetch_assoc($resInv_annex_old);
					$nonecsCountStartINV=$rowinv_annex_old['increment_id'];
					$checkedmonth=$month;                              
			}else{
					$nonecsCountStartINV=0;
			}
			
			$getRecieptDet_old="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE type='reciept' ORDER BY id DESC LIMIT 1"; // For Both
			$resRecieptDet_old = parent::execQuery($getRecieptDet_old, $this->fin_con);
			$recipt_num_rows = mysql_num_rows($resRecieptDet_old);
			if($recipt_num_rows>0){
				$rowRcpt_old = mysql_fetch_assoc($resRecieptDet_old);
					$nonecsCountStartRC=$rowRcpt_old['increment_id'];
					$checkedmonth=$month;                              
			}else{
					$nonecsCountStartRC=0;
			}
			
			
				if($stCode != 'NAS'){
				
				 switch(strtolower($this->params['data_city'])){
					case 'mumbai': $invcode=0; break;
					case 'delhi': $invcode=1; break;
					case 'kolkata': $invcode=2; break;
					case 'bangalore': $invcode=3; break;
					case 'chennai': $invcode=4; break;
					case 'pune': $invcode=5; break;
					case 'hyderabad': $invcode=6; break;
					case 'ahmedabad': $invcode=7; break;
					case 'remote_city': $invcode=8; break;
					default:$invcode=8; break;
				}
			}
			
			
			
			if($mod == 'SN'){
				
				$month		       =	$month = date('m',strtotime($this->invDate));;
				//$year		       =	date("y");
				$year		       =	date("y",strtotime($this->invDate));
				$incrInvNum	       =	$nonecsCountStartINV + 1;
				$RcipNum	   	   =	$nonecsCountStartRC + 1;
				$invcNo 		   = 	str_pad($incrInvNum, 7, 0, STR_PAD_LEFT);
				$invCode		   =	$stCode.$month.$year.$mod.$invcode.$invcNo;
				$rcpNo 		   	   = 	str_pad($RcipNum, 7, 0, STR_PAD_LEFT);
				$rcptCode		   =	$stCode.$month.$year.'RC'.$invcode.$invcNo;
				
				
				$idArray['invoice'] = $invCode;
				$idArray['annexure'] = $invCode;
				$idArray['reciept'] = $rcptCode;
				$idArray['increment_id'] = $incrInvNum;
					
			}
			return $idArray;
			
	}
	
	
	
	function makeCreditNoteID($stateName,$row_receipt,$app_version){
		
		$idArray=array();
    
	$against_inst=explode('.',$this->instrumentid);
	if($against_inst[1] == 'L1' && $against_inst[2] != '')
	{
		$against_inst_new= $against_inst[0].'.'.$against_inst[2]; 
	}else
	{
		$against_inst_new= $against_inst[0]; 
	}
	
	$sel_curr_invoice 					= "SELECT invoiceno,clearance_date,instrumentid FROM db_invoice.tbl_gst_nonecs_data WHERE instrumentid IN('".$this->instrumentid."','".$against_inst_new."')";
	$con_curr_invoice					=	parent::execQuery($sel_curr_invoice, $this->fin_con);
	$res_cur_rows 							= mysql_num_rows($con_curr_invoice);
	if($res_cur_rows>0)
	{
		while($res_curr_invoice 						= mysql_fetch_assoc($con_curr_invoice))
		{
			if($against_inst_new == $res_curr_invoice['instrumentid'])
			{
				$idArray['orig_invoice'] = $res_curr_invoice['invoiceno'];
				$idArray['orig_invoice_date'] = $res_curr_invoice['clearance_date'];
			}
			if($this->instrumentid == $res_curr_invoice['instrumentid'])
			{
				$idArray['curr_invoice'] = $res_curr_invoice['invoiceno'];
				$idArray['curr_invoice_date'] = $res_curr_invoice['clearance_date'];
			}
		}
	}else
	{
			$sel_db_paymnt 						= "SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE instrumentid IN('".$this->instrumentid."','".$against_inst_new."') and type='invoice'";
			$con_db_paymnt						=	parent::execQuery($sel_db_paymnt, $this->idc_con);
			$res_db_paymnt_rows 				= mysql_num_rows($con_db_paymnt);
			if($res_db_paymnt_rows>0)
			{
				while($res_curr_invoice 						= mysql_fetch_assoc($con_db_paymnt))
				{
					if($against_inst_new == $res_curr_invoice['instrumentid'])
					{
						$idArray['orig_invoice'] = $res_curr_invoice['invoiceno'];
						$idArray['orig_invoice_date'] = $res_curr_invoice['clearance_date'];
					}
					if($this->instrumentid == $res_curr_invoice['instrumentid'])
					{
						$idArray['curr_invoice'] = $res_curr_invoice['invoiceno'];
						$idArray['curr_invoice_date'] = $res_curr_invoice['clearance_date'];
					}
				}
			}else
			{
				$sel_instrument 					= "SELECT a.parentid,b.finalApprovalDate FROM db_finance.payment_instrument_summary a JOIN db_finance.payment_clearance_details b ON a.instrumentId=b.instrumentId WHERE b.finalApprovalFlag=1 AND a.instrumentId IN('".$this->instrumentid."','".$against_inst_new."') GROUP BY parentid";
				$con_instrument						=	parent::execQuery($sel_instrument, $this->fin_con);
				while($res_curr_invoice 						= mysql_fetch_assoc($con_instrument))
				{
				
					$sel_credit_exist 					= "SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$res_curr_invoice['parentid']."' and type='invoice' and approval_date ='".date('Y-m-d',strtotime($res_instrument['finalApprovalDate']))."'";
					$con_sel_n_exist						=	parent::execQuery($sel_credit_exist, $this->idc_con);
					$res_sel_exist 							= mysql_fetch_assoc($con_sel_n_exist);
					
					$idArray['curr_invoice'] = $res_sel_exist['invoiceno'];
					$idArray['curr_invoice_date'] = $res_sel_exist['clearance_date'];
				}
				
				
			}
			
	}
	return $idArray;
}
	/*function invoiceAnnexureNumber(){
		
		$row_receipt	=	array();
		$jd_details = array();
		$getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."';";
        $resUcode = parent::execQuery($getUcode, $this->idc_con);
        if(parent::numRows($resUcode)>0)
        {
            while($rowUcode = parent::fetchData($resUcode)){
                $usrCode=$rowUcode['userid'];
            }
        }
        if($usrCode== ''){
			$getUcode = "SELECT mecode,mename,tmecode,tmename FROM payment_otherdetails  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
			$resUcode = parent::execQuery($getUcode, $this->fin_con);
			if(parent::numRows($resUcode)>0)
			{
				$rowUcode = parent::fetchData($resUcode);
				if($this->params['version'] % 10 == 3)
					$usrCode=$rowUcode['mecode'];
				else if	($this->params['version'] % 10 == 2)
					$usrCode=$rowUcode['tmecode'];
			}
		}
		
		$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
        $transferstring = $this->curlcall($ssourl,'','');
        $sso_det=json_decode($transferstring,TRUE);
        $employee_city=$sso_det['data']['city'];
        if($employee_city=='Delhi'){
            $employee_city='Noida';
        }
        
        $getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
        $stateData = parent::execQuery($getState, $this->local_d_jds);
        
        while($row = parent::fetchData($stateData)){
            $stateName  =$row['state_name'];
            $stateId    =$row['state_id'];
        }
        
        
        
        if($stateName == ''){
			$generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->params['parentid']."'";
			$generalres = parent::execQuery($generalsql, $this->temp_con);
			if($generalres && parent::numRows($generalres)>0){
				$generalrow = parent::fetchData($generalres);
				$employee_city 	= $generalrow['city'];
				$stateName	= $generalrow['state'];
				
				if(strtolower($employee_city)	==	'delhi' || strtolower($employee_city) == 'gurgaon'){
					$employee_city	=	'Noida';
				}
				if(strtolower($stateName)	==	'delhi' || strtolower($stateName)	==	'haryana'){
					$stateName	=	'Uttar Pradesh';
				}
			}
		}
		
		$sql_receipt = "SELECT receipt_details,other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
		if($res_receipt && parent::numRows($res_receipt)>0)
		{
			$row_receipt = parent::fetchData($res_receipt);
		}
		
		 $getQry ="SELECT receipt_details FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
        $resQry = parent::execQuery($getQry, $this->fin_con);
        while($rowData = parent::fetchData($resQry)){
            $receipt_details_arr=json_decode($rowData['receipt_details'],TRUE);
        }

        $chk=$receipt_details_arr['gst_info']['receipt_no'];

        if($receipt_details_arr['gst_info']['receipt_no']==''){
            $RcptInvArray=$this->makeInvoiceRecieptId($stateName,$row_receipt);
            $invoiceNumber=$RcptInvArray['invoice'];
            $receiptNumber=$RcptInvArray['receipt'];
            $row_receipt['errorCode']	=	0;
            $row_receipt['data']['annexure_no']  = $receiptNumber;
            $row_receipt['data']['invoice_no']  = $invoiceNumber;
        }else{
			$row_receipt['errorCode']	=	0;
            $row_receipt['data']['annexure_no']  = $receipt_details_arr['gst_info']['receipt_no'];
            $row_receipt['data']['invoice_no']  = $receipt_details_arr['gst_info']['invoice_no'];
        }
		
		return json_encode($row_receipt);
        
	}
	*/
	
	function invoiceAnnexureNumber(){
		
		$row_receipt	=	array();
		$jd_details = array();
		
		$completeYr_from = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'01';
		 $completeYr_to = date('Y',strtotime($this->invDate)).'-'.date('m',strtotime($this->invDate)).'-'.'31';
		/**********************1st Rule Start******************/
		if($this->params['parentid'] == 'PXX22.XX22.090612215830.X7B9' || $this->chk_gstn == 1)
		{
			 $query_gst_state		="SELECT * FROM db_payment.tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->params['parentid']."'  ORDER BY doneon DESC LIMIT 1";
		}else
		{
			$query_gst_state		="SELECT * FROM db_payment.tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->params['parentid']."' AND doneon<='".$completeYr_to." 23:59:59' ORDER BY doneon DESC LIMIT 1";
		}
		 
		$resgstState	 		= parent::execQuery($query_gst_state, $this->idc_con);
		$rowgstState 			= parent::fetchData($resgstState);
		
		if($rowgstState['gstn_state_name']!='' && $rowgstState['gstn_state_name']!= null)
		{
			$stateName 				= $rowgstState['gstn_state_name'];
		}
		
		
		/***********************End************************/
		
		/**********************2nd Rule Start******************/
		
		/*if($stateName == ''){
			
			//$generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tme_jds.tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->params['parentid']."'";
			//$generalres = parent::execQuery($generalsql, $this->temp_con);
			
			$generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tme_jds.tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->params['parentid']."'";
			$generalres = parent::execQuery($generalsql, $this->temp_con);
			if(parent::numRows($generalres)==0){
				$generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->params['parentid']."'";
				$generalres = parent::execQuery($generalsql, $this->temp_con);
			}
			
			if($generalres && parent::numRows($generalres)>0){
				$generalrow = parent::fetchData($generalres);
				//$employee_city 	= $generalrow['city'];
				
				
				$state_name_2ndrule = "SELECT state_name,ct_name FROM d_jds.tbl_city_master WHERE ct_name ='".$generalrow['city']."'";
				$res_state_2nd = parent::execQuery($state_name_2ndrule, $this->temp_con);
				$row_state_2nd =  parent::fetchData($res_state_2nd);
				$stateName = $row_state_2nd['state_name'];	
				$ctName = $row_state_2nd['ct_name'];	
					
			}
		}*/
		
		if($stateName == ''){
			
			 $generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM db_iro.tbl_companymaster_generalinfo WHERE parentid ='".$this->params['parentid']."'";
            $generalres = parent::execQuery($generalsql, $this->local_iro_conn);
            if($generalres && mysql_num_rows($generalres)>0)
            {

                $generalrow = mysql_fetch_assoc($generalres);
            }else
            {

                    switch(strtolower($this->params['module']))
                    {
                        case 'cs':
                        $generalsql = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM db_iro.tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->params['parentid']."'";
                        $generalres = parent::execQuery($generalsql, $this->temp_con);
                        if($generalres && mysql_num_rows($generalres)>0)
                        {
                            $generalrow = mysql_fetch_assoc($generalres);
                        }

                        break;
                        case 'tme':

                            $mongo_inputs = array();
                            $mongo_inputs['parentid']     = $this->params['parentid'];
                            $mongo_inputs['data_city']     = $this->params['data_city'];
                            $mongo_inputs['module']        = 'tme';
                            $mongo_inputs['table']         = "tbl_companymaster_generalinfo_shadow";
                            $mongo_inputs['fields']     = "city,companyname,contact_person,state,area,pincode,full_address";
                            $generalrow                     = $this->mongo_obj->getData($mongo_inputs);

                        break;
                        case 'me':
                        case 'jda':

                            $mongo_inputs = array();
                            $mongo_inputs['parentid']     = $this->params['parentid'];
                            $mongo_inputs['data_city']     = $this->params['data_city'];
                            $mongo_inputs['module']        = 'me';
                            $mongo_inputs['table']         = "tbl_companymaster_generalinfo_shadow";
                            $mongo_inputs['fields']     = "city,companyname,contact_person,state,area,pincode,full_address";
                            $generalrow                     = $this->mongo_obj->getData($mongo_inputs);
                        break;

                    }
            }


            $state_name_2ndrule = "SELECT state_name,ct_name FROM d_jds.tbl_city_master WHERE ct_name ='".$generalrow['city']."'";
            $res_state_2nd = parent::execQuery($state_name_2ndrule, $this->local_iro_conn);
            $row_state_2nd =  parent::fetchData($res_state_2nd);
            $stateName = $row_state_2nd['state_name'];
            $ctName = $row_state_2nd['ct_name']; 
		}
		
		/***********************End************************/
		if($row_state_2nd['state_name'] == '' && $stateName == '')
		{
			$getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."';";
			$resUcode = parent::execQuery($getUcode, $this->idc_con);
			if(parent::numRows($resUcode)>0)
			{
				while($rowUcode = parent::fetchData($resUcode)){
					$usrCode=$rowUcode['userid'];
				}
			}
			if($usrCode== ''){
				$getUcode = "SELECT mecode,mename,tmecode,tmename FROM payment_otherdetails  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
				$resUcode = parent::execQuery($getUcode, $this->fin_con);
				if(parent::numRows($resUcode)>0)
				{
					$rowUcode = parent::fetchData($resUcode);
					if($this->params['version'] % 10 == 3)
						$usrCode=$rowUcode['mecode'];
					else if	($this->params['version'] % 10 == 2)
						$usrCode=$rowUcode['tmecode'];
				}
				if($usrCode == '' || $usrCode == 0)
				{
					$getUcode_paymentInstrmnt = "SELECT entry_doneby FROM payment_instrument_summary  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."'";
					$resUcodepaymentInstrmnt = parent::execQuery($getUcode_paymentInstrmnt, $this->fin_con);
					$rowUcodepaymentInstrmnt = parent::fetchData($resUcodepaymentInstrmnt);
					$usrCode = $rowUcodepaymentInstrmnt['entry_doneby'];
				}
			}
			
			
			if($usrCode == 'userselfwebsignup')
			{
				
				$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='Mumbai'";
				$stateData = parent::execQuery($getState, $this->local_d_jds);
				while($row = mysql_fetch_assoc($stateData)){
					$stateName  =$row['state_name'];
					$stateId    =$row['state_id'];
				}
			}
			else
			{
				 $ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
				$transferstring = $this->curlcall($ssourl,'','');
				$sso_det=json_decode($transferstring,TRUE);
				$employee_city=$sso_det['data']['city'];
				if(strtolower($employee_city)=='delhi' || strtolower($employee_city)=='gurgaon'){
					$employee_city='Noida';
				}
				
				$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
				$stateData = parent::execQuery($getState, $this->local_d_jds);
				
				while($row = parent::fetchData($stateData)){
					$stateName  =$row['state_name'];
					$stateId    =$row['state_id'];
				}
			}
			
			/*$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
			$transferstring = $this->curlcall($ssourl,'','');
			$sso_det=json_decode($transferstring,TRUE);
			$employee_city=$sso_det['data']['city'];
			if(strtolower($employee_city)=='delhi' || strtolower($employee_city)=='gurgaon'){
				$employee_city='Noida';
			}
			
			$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
			$stateData = parent::execQuery($getState, $this->local_d_jds);
			
			while($row = parent::fetchData($stateData)){
				$stateName  =$row['state_name'];
				$stateId    =$row['state_id'];
			}*/
        }
       
		$sql_receipt = "SELECT receipt_details,other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
		$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
		if($res_receipt && parent::numRows($res_receipt)>0)
		{
			$row_receipt = parent::fetchData($res_receipt);
		}
		
		 $getQry ="SELECT receipt_details FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
        $resQry = parent::execQuery($getQry, $this->fin_con);
        while($rowData = parent::fetchData($resQry)){
            $receipt_details_arr=json_decode($rowData['receipt_details'],TRUE);
        }

		$getQry_main ="SELECT * FROM db_invoice.tbl_gst_nonecs_data WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND date(approval_date)='".$this->params['invDate']."'";
        $resQry_main = parent::execQuery($getQry_main, $this->fin_con);
        while($rowData_main = parent::fetchData($resQry_main)){
            $receipt_details_arr['invoiceno_exist']=$rowData_main['invoiceno'];
        }
        
       /* $getQry_main_reciept ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND type='reciept' AND date(approval_date)='".$this->params['invDate']."'";
        $resQry_main_rcpt = parent::execQuery($getQry_main_reciept, $this->idc_con);
        while($rowData_main_rcpt = parent::fetchData($resQry_main_rcpt)){
            $receipt_details_arr['receipt_no']=$rowData_main_rcpt['inv_rcpt_code'];
        }*/
        
        if($this->params['invDate']>='2018-12-01')
        {
			
			$getQry_main_entry ="SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."'  AND date_time='".$this->params['invDate']."' AND type='invoice'";
			$resQry_main_entry = parent::execQuery($getQry_main_entry, $this->fin_con);
			if($resQry_main_entry && parent::numRows($resQry_main_entry)==0 && $this->params['action'] != 1)
			{
				$getQry_main_reciept ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND type='reciept' AND date(approval_date)='".$this->params['invDate']."'";
				$resQry_main_rcpt = parent::execQuery($getQry_main_reciept, $this->idc_con);
				$rowData_main_rcpt = parent::fetchData($resQry_main_rcpt);
				$receipt_details_arr['receipt_no']=$rowData_main_rcpt['inv_rcpt_code'];
				
				 $getQry_main_inv ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND type='invoice' AND date(approval_date)='".$this->params['invDate']."'";
				$resQry_main_inv = parent::execQuery($getQry_main_inv, $this->idc_con);
				$rowData_main_inv = parent::fetchData($resQry_main_inv);
				$receipt_details_arr['invoice_no']=$rowData_main_inv['inv_rcpt_code'];
			}else
			{
				$getQry_main_reciept ="SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."'  AND date_time='".$this->params['invDate']."' AND type='invoice'";
				$resQry_main_new = parent::execQuery($getQry_main_reciept, $this->fin_con);
				$rowData_main_rcpt_new = parent::fetchData($resQry_main_new);
				$receipt_details_arr['invoice_no']=$rowData_main_rcpt_new['inv_rcpt_code'];
				
				$getQry_main_reciept ="SELECT * FROM db_invoice.tbl_reciept_invoice_record_nonecs WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."'  AND date_time='".$this->params['invDate']."' AND type='reciept'";
				$resQry_main_new = parent::execQuery($getQry_main_reciept, $this->fin_con);
				$rowData_main_rcpt_new = parent::fetchData($resQry_main_new);
				$chk_invoice_arr1[] = $rowData_main_rcpt_new['inv_rcpt_code'];
				$receipt_details_arr['receipt_no']=$rowData_main_rcpt_new['inv_rcpt_code'];
			}
			
			
		}else
		{
			
			$getQry_main_reciept ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND type='reciept' AND date(approval_date)='".$this->params['invDate']."'";
			$resQry_main_rcpt = parent::execQuery($getQry_main_reciept, $this->idc_con);
			$resQry_main_rows = parent::numRows($getQry_main_reciept, $this->idc_con);
			if($resQry_main_rows>0)
			{
				$rowData_main_rcpt = parent::fetchData($resQry_main_rcpt);
				$receipt_details_arr['receipt_no']=$rowData_main_rcpt['inv_rcpt_code'];
			}else
			{
				$getQry_main_reciept ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'AND type='reciept' AND date(approval_date)='".$this->params['invDate']."'";
				$resQry_main_rcpt = parent::execQuery($getQry_main_reciept, $this->idc_con);
				$rowData_main_rcpt = parent::fetchData($resQry_main_rcpt);
				$receipt_details_arr['receipt_no']=$rowData_main_rcpt['inv_rcpt_code'];
			}
			
			$getQry_main_inv ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND type='invoice' AND date(approval_date)='".$this->params['invDate']."'";
			$resQry_main_inv = parent::execQuery($getQry_main_inv, $this->idc_con);
			$resQry_main_rows_inv = parent::numRows($resQry_main_inv, $this->idc_con);
			if($resQry_main_rows_inv>0)
			{
				$rowData_main_inv = parent::fetchData($resQry_main_inv);
				$receipt_details_arr['invoice_no']=$rowData_main_inv['inv_rcpt_code'];
			}else
			{
				$getQry_main_inv ="SELECT * FROM db_payment.tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."'AND type='invoice' AND date(approval_date)='".$this->params['invDate']."'";
				$resQry_main_inv = parent::execQuery($getQry_main_inv, $this->idc_con);
				$rowData_main_inv = parent::fetchData($resQry_main_inv);
				$receipt_details_arr['invoice_no']=$rowData_main_inv['inv_rcpt_code'];
			}
			if($receipt_details_arr['invoice_no'] !='')
			{
				$old_invno = 1;
			}
		}
       //echo '<pre>';
       //print_r($receipt_details_arr);die;
     
		$getQry_inv_dupli ="SELECT * FROM db_invoice.tbl_whatsapp_approval_data WHERE  invoiceno='".$receipt_details_arr['invoice_no']."' AND parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$this->params['instrumentid']."' AND date(approval_date)='".$this->params['invDate']."'";
        $resQry_inv_dupli = parent::execQuery($getQry_inv_dupli, $this->fin_con);
        $rowData_dupli = parent::numRows($resQry_inv_dupli);
        $rowData_dupli_res = parent::fetchData($resQry_inv_dupli);
         $receipt_details_arr['invoiceno_dupli']=$rowData_dupli_res['invoiceno'];
       
		$getQry_inv_exist ="SELECT * FROM db_invoice.tbl_whatsapp_approval_data WHERE  invoiceno='".$receipt_details_arr['invoice_no']."'";
        $resQry_inv_exist = parent::execQuery($getQry_inv_exist, $this->fin_con);
        $rowData_exist = parent::numRows($resQry_inv_exist);
        $rowData_exist_res = parent::fetchData($resQry_inv_exist);
        $receipt_details_arr['invoiceno_exist_whts']=$rowData_exist_res['invoiceno'];
       
     
        $chk=$receipt_details_arr['gst_info']['receipt_no'];
		
		
		if($this->params['genInvoice'] == 4)
		{
			$RcptInvArray=$this->makeCreditNoteID($stateName,$row_receipt,$this->params['app_version']);
			
			$invoiceNumber_orig		=$RcptInvArray['orig_invoice'];
			$invoiceNumber_orig_date=$RcptInvArray['orig_invoice_date'];
			$curr_inv=$RcptInvArray['curr_invoice'];
			$curr_inv_date=$RcptInvArray['curr_invoice_date'];
			$row_receipt['errorCode']	=	0;
			$row_receipt['data']['invoice_no']  = $invoiceNumber_orig;
			$row_receipt['data']['invoice_date']  = $invoiceNumber_orig_date;
			$row_receipt['data']['buyer_state']  = $stateName;
			$row_receipt['data']['credit_against_inv']  = $curr_inv;
			$row_receipt['data']['credit_against_date']  = $curr_inv_date;
		}else
		{
			
			if($receipt_details_arr['invoiceno_exist'] != '')
			{
				
				$row_receipt['errorCode']	=	0;
				$row_receipt['data']['annexure_no']  = $receipt_details_arr['receipt_no'];
				$row_receipt['data']['invoice_no']   = $receipt_details_arr['invoiceno_exist'];
				$row_receipt['data']['buyer_state']  = $stateName;
			}else
			{
				
				if(($receipt_details_arr['invoice_no'] == '' && $receipt_details_arr['receipt_no'] == '')){
					
					$RcptInvArray=$this->makeInvoiceRecieptId($stateName,$row_receipt);
					
					$invoiceNumber=$RcptInvArray['invoice'];
					$receiptNumber=$RcptInvArray['reciept'];
					$row_receipt['errorCode']	=	0;
					$row_receipt['data']['annexure_no']  = $receiptNumber;
					$row_receipt['data']['invoice_no']  = $invoiceNumber;
					$row_receipt['data']['buyer_state']  = $stateName;
				}else{
					
					$row_receipt['errorCode']	=	0;
					if($receipt_details_arr['invoice_no'] != '' && $receipt_details_arr['receipt_no'] != '')
					{
						if($rowData_dupli>0 && $receipt_details_arr['invoice_no'] ==  $receipt_details_arr['invoiceno_dupli'])
						{
							
							$row_receipt['data']['annexure_no']  = $receipt_details_arr['receipt_no'];
							$row_receipt['data']['invoice_no']  = $receipt_details_arr['invoice_no'];
							$row_receipt['data']['buyer_state']  = $stateName;
							
						}
						else if($rowData_dupli==0 && $old_invno==1)
						{
							$row_receipt['data']['annexure_no']  = $receipt_details_arr['receipt_no'];
							$row_receipt['data']['invoice_no']  = $receipt_details_arr['invoice_no'];
							$row_receipt['data']['buyer_state']  = $stateName;	
						}
						else if($rowData_exist>0 && $receipt_details_arr['invoice_no'] ==  $receipt_details_arr['invoiceno_exist_whts'])
						{
							
							
							$upd_exist_inv="UPDATE db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='' WHERE parentid='".$this->params['parentid']."' AND instrumentid ='".$this->instrumentid."' AND type='invoice' AND date_time='".$this->invDate."' AND version='".$this->params['version']."'";
							$InvoiceDet = parent::execQuery($upd_exist_inv, $this->fin_con);
							
							$RcptInvArray=$this->makeInvoiceRecieptId($stateName,$row_receipt);
							$invoiceNumber=$RcptInvArray['invoice'];
							$receiptNumber=$RcptInvArray['reciept'];
							$row_receipt['errorCode']	=	0;
							$row_receipt['data']['annexure_no']  = $receiptNumber;
							$row_receipt['data']['invoice_no']  = $invoiceNumber;
							$row_receipt['data']['buyer_state']  = $stateName;
						}
						else
						{
							$RcptInvArray=$this->makeInvoiceRecieptId($stateName,$row_receipt,$receipt_details_arr['invoice_no'],$receipt_details_arr['receipt_no']);
					
							$invoiceNumber=$RcptInvArray['invoice'];
							$receiptNumber=$RcptInvArray['reciept'];
							$row_receipt['errorCode']	=	0;
							$row_receipt['data']['annexure_no']  = $receiptNumber;
							$row_receipt['data']['invoice_no']  = $invoiceNumber;
							$row_receipt['data']['buyer_state']  = $stateName;
							
						}
					}else
					{
						if($receipt_details_arr['receipt_no']!='')
						{
							$row_receipt['data']['annexure_no']  = $receipt_details_arr['receipt_no'];
							$row_receipt['data']['invoice_no']  = $receipt_details_arr['invoice_no'];
							$row_receipt['data']['buyer_state']  = $stateName;
						}else
						{
							/*$row_receipt['data']['annexure_no']  = $receipt_details_arr['gst_info']['receipt_no'];
							$row_receipt['data']['invoice_no']  = $receipt_details_arr['gst_info']['invoice_no'];
							$row_receipt['data']['buyer_state']  = $stateName;*/
							
							$row_receipt['data']['annexure_no']  = str_replace('SN','RC',$receipt_details_arr['invoice_no']);
							$row_receipt['data']['invoice_no']  = $receipt_details_arr['invoice_no'];
							$row_receipt['data']['buyer_state']  = $stateName;
							
							$insReceiptQry="INSERT INTO db_invoice.tbl_reciept_invoice_record_nonecs SET inv_rcpt_code='".str_replace('SN','RC',$receipt_details_arr['invoice_no'])."', type='reciept', increment_id='".$receipt_details_arr['increment_id']."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$this->invDate."',approval_date='".$this->invDate."',state='".$stateName."',billnumber='".$this->instrumentid."',instrumentid='".$this->instrumentid."',insert_date=NOW()";
							$rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->fin_con);
						}
						if($receipt_details_arr['invoice_no']=='')
						{
							$RcptInvArray=$this->makeInvoiceRecieptId($stateName,$row_receipt);
					
							$invoiceNumber=$RcptInvArray['invoice'];
							$receiptNumber=$RcptInvArray['reciept'];
							$row_receipt['errorCode']	=	0;
							$row_receipt['data']['annexure_no']  = $receiptNumber;
							$row_receipt['data']['invoice_no']  = $invoiceNumber;
							$row_receipt['data']['buyer_state']  = $stateName;
						}
					}
				}
			}
		}
		
		return json_encode($row_receipt);
        
	}
	
	function getInvoiceContent($generate_invoice_fn){
		$invoice_content				=	array();
		$invoice_cust_gst				=	array();
		$invoice_JD_gst					=	array();
		$invoice_gen_details			=	array();
		$invoice_instrument_details		=	array();
		$invoice_annexure_number		=	array();
		$annexure_details				=	array();
		
		//~ echo $annexure_details			=		$this->annexureContent($generate_invoice_fn);die;
		
		
		$dealclosedInfo  = $this->dealclosedinf();
            
            $rec_array = array();	
			if($this->invDate == '')
			$this->invDate = date('Y-m-d',strtotime($dealclosedInfo['entryDate']));
			else
			$this->invDate = $this->invDate;
			
			if($this->instrumentid == '')
			{
				$this->instrumentid = $dealclosedInfo['instrumentId'];
				$this->params['instrumentid'] = $dealclosedInfo['instrumentId'];
			}
			else
			{
				$this->instrumentid = $this->instrumentid;
				$this->params['instrumentid'] = $this->instrumentid;
			}
			
			if($dealclosedInfo['single_ck'] == 1)
				$this->params['parentid'] = $dealclosedInfo['parentid'];
			else	
				$this->params['parentid'] = $this->params['parentid'];
		
		
		
		$invoice_gen_details						=	json_decode($this->getCustomerGenDetails(),true);
		if($invoice_gen_details['errorCode'] == 0){
			$invoice_content['invoice_details']['errorCode']		=	0;
			$invoice_content['invoice_details']['data']				=	$invoice_gen_details['data'];
		}else{
			$invoice_content['invoice_details']['errorCode']		=	1;
			$invoice_content['msg']				=	'Invoice Details Not Found';
		}
		
		$invoice_JD_gst 		=	json_decode($this->getjustdialdetails(),true);
		if($invoice_JD_gst['errorCode'] == 0){
			$invoice_content['jd_gst']['errorCode']	=	0;
			$invoice_content['jd_gst']['data']	=	$invoice_JD_gst['data'];
		}else{
			$invoice_content['jd_gst']['errorCode']		=	1;
			$invoice_content['msg']				=	'Justdial Gst Details Not Found';
		}
		
		$invoice_cust_gst 		=	json_decode($this->getCustomerGstDetails(),true);
		if($invoice_cust_gst['errorCode'] == 0){
			$invoice_content['customer_gst']['errorCode']	=	0;
			$invoice_content['customer_gst']['data']		=	$invoice_cust_gst['data'];
		}else{
			$invoice_content['customer_gst']['errorCode']		=	1;
			$invoice_content['msg']				=	'Customer Gst Details Not Found';
		}
		
		//~ $invoice_cust_gst 		=	json_decode($this->getCustomerGstDetails(),true);
		//~ if($invoice_cust_gst['errorCode'] == 0){
			//~ $invoice_content['customer_gst']['errorCode']	=	0;
			//~ $invoice_content['customer_gst']['data']		=	$invoice_cust_gst['data'];
		//~ }else{
			//~ $invoice_content['customer_gst']['errorCode']		=	1;
			//~ $invoice_content['msg']				=	'Customer Gst Details Not Found';
		//~ }
		
		
		if($this->params['action'] == 1){
			//$instrument_details_arr = json_decode($this->getInstrumentDetailsArrApproval($this->params['parentid'],$this->params['version'],$this->params['genInvoice'],$this->params['invDate']),true);
			if($this->download_mod == 1)
			{
				$instrument_details_arr = json_decode($this->getInstrumentDetailsArrApproval_mod($this->params['parentid'],$this->params['version'],$this->params['genInvoice'],$this->params['invDate']),true);
			}else
			{
				$instrument_details_arr = json_decode($this->getInstrumentDetailsArrApproval($this->params['parentid'],$this->params['version'],$this->params['genInvoice'],$this->params['invDate']),true);
			}
			if($instrument_details_arr['errorCode']	==	0){
				$invoice_content['instrumnet_details']['errorCode']	=	0;
				$invoice_content['instrumnet_details']['data']		=	$instrument_details_arr['data'];
			}else{
				$invoice_content['instrumnet_details']['errorCode']	=	1;
				$invoice_content['msg']		=	'Instrument Not Found';
			}
		}else if($this->params['action'] == 6){
			$instrument_details_arr = json_decode($this->fn_ccemi($this->params['parentid'],$this->params['version'],$this->params['invDate']),true);
			if($instrument_details_arr['errorCode']	==	0){
				$invoice_content['instrumnet_details']['errorCode']	=	0;
				$invoice_content['instrumnet_details']['data']		=	$instrument_details_arr['data'];
			}else{
				$invoice_content['instrumnet_details']['errorCode']	=	1;
				$invoice_content['msg']		=	'Instrument Not Found';
			}
		}
		else{
			$instrument_details_arr = json_decode($this->getInstrumentDetailsArr($this->params['parentid'],$this->params['version'],$this->params['genInvoice'],$this->params['invDate']),true);
			if($instrument_details_arr['errorCode']	==	0){
				$invoice_content['instrumnet_details']['errorCode']	=	0;
				$invoice_content['instrumnet_details']['data']		=	$instrument_details_arr['data'];
			}else{
				$invoice_content['instrumnet_details']['errorCode']	=	1;
				$invoice_content['msg']		=	'Instrument Not Found';
			}
		}
		
		$invoice_annexure_number = json_decode($this->invoiceAnnexureNumber($this->params['parentid'],$this->params['version'],$this->params['genInvoice'],$this->params['invDate']),true);
		if($invoice_annexure_number['errorCode']	==	0){
			$invoice_content['invoice_annexure_number']['errorCode']	=	0;
			$invoice_content['invoice_annexure_number']['data']		=	$invoice_annexure_number['data'];
		}else{
			$invoice_content['invoice_annexure_number']['errorCode']	=	1;
			$invoice_content['msg']		=	'Instrument number Not Found';
		}
		
		
		//~ echo $annexure_details = $this->annexContent();
		$annexure_details = json_decode($this->annexureContent($generate_invoice_fn),true);
		if($annexure_details['errorCode']	==	0){
			$invoice_content['annexure_details']['errorCode']	=	0;
			$invoice_content['annexure_details']['data']		=	$annexure_details['data'];
		}else{
			$invoice_content['annexure_details']['errorCode']	=	1;
			$invoice_content['msg']		=	'Annexure Details Not Found';
		}
		
		return json_encode($invoice_content);
		
	}
	
	function getdealclosedinfo(){
		
		
		
		
		
		
	}
	
	function curlcall($url,$params,$type =''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($type = 'post'){
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	 function updateAll_details($invoice_output,$taxMode){
			
			
			
			$dealclosedInfo  = $this->dealclosedinf();
            $rec_array = array();	
			if($this->invDate == '')
			$this->invDate = date('Y-m-d',strtotime($dealclosedInfo['entry_date']));
			else
			$this->invDate = $this->invDate;
			
			if($this->instrumentid == '')
			$instrumentid_n = $dealclosedInfo['instrumentId'];
			else
			$instrumentid_n = $this->instrumentid;
			
			$tottdsamt=0;
			$totnetamt	=	array();
            $totnetamt=explode(".",$invoice_output['instrumnet_details']['data']['instrument_total']['totnetamt']);
			$totsrvctax=$invoice_output['instrumnet_details']['data']['instrument_total']['totsrvctax'];
            $totsbctax=$invoice_output['instrumnet_details']['data']['instrument_total']['totsbctax'];
            $totkkctax=$invoice_output['instrumnet_details']['data']['instrument_total']['totkkctax'];
            $totgrsamt=explode(".",$invoice_output['instrumnet_details']['data']['instrument_total']['totgrsamt']);
            $tottdsamt=$invoice_output['instrumnet_details']['data']['instrument_total']['tottdsamt'];
           $totinsamt=$invoice_output['instrumnet_details']['data']['instrument_total']['totinsamt'];
			
            $totinsamt=str_replace(',','', $totinsamt);
			$totgrsamt=str_replace(',','', $totgrsamt);
			$totnetamt=str_replace(',','', $totnetamt);
         
           $totlForgst=str_replace(",", '', $totnetamt[0]);
             $tottdsamt_int=str_replace(",", '', $tottdsamt);
             if($invoice_output['instrumnet_details']['data']['instrument_total']['service_tax_percent']=='0.18'){
                if($taxMode==0){
                    $cgstPer=9;
                    $sgstPer=9;
                    $igstPer=0;
                }else if($taxMode==1){
                    $cgstPer=0;
                    $sgstPer=0;
                    $igstPer=18;
                }

                $totlForgst=(float)$totlForgst;
                $cgst=($cgstPer/100)*$totlForgst;

                $sgst=($sgstPer/100)*$totlForgst;

                $igst=($igstPer/100)*$totlForgst;

                $tot_gst=$cgst+$sgst+$igst;

               
                $gross=$totlForgst + $tot_gst -(int) $tottdsamt_int;


            }else if($invoice_output['instrumnet_details']['data']['instrument_total']['service_tax_percent']=='0.15'){
                $tot_sevice_tax=(15/100)*$totlForgst;
                $gross=$totlForgst + $tot_sevice_tax - (int)$tottdsamt_int;
            }else{
				$tot_sevice_tax=($invoice_output['instrumnet_details']['data']['instrument_total']['service_tax_percent'])*$totlForgst;
                $gross=$totlForgst + $tot_sevice_tax - (int)$tottdsamt_int;
			}
          
           
           /*************************************Update Table for all details************************************/
           $update_alldetails = "UPDATE db_payment.tbl_reciept_invoice_record SET 					total_amount = '".$gross."',
																									gross_amount = '".round($totgrsamt[0])."',
																									net_amount = '".round($totnetamt[0])."',
																									CGST = '".round($cgst)."',
																									SGST = '".round($sgst)."',
																									IGST = '".round($igst)."',
																									Total_GST = '".round($tot_gst)."',
																									cgst_perc = '".$cgstPer."',
																									sgst_perc = '".$sgstPer."',
																									igst_perc = '".$igstPer."',
																									tot_gst_perc = '18%',
																									TDS = '".round($tot_sevice_tax)."',
																									instrumentAmount = '".$gross."',
																									insert_date=NOW(),
																									clearance_date='".$this->invDate."',
																									companyname='".addslashes($invoice_output['invoice_details']['data']['Company Name'])."',
																									city='".addslashes($invoice_output['jd_gst']['data']['jd_jd_city'])."',
																									client_name='".addslashes($invoice_output['invoice_details']['data']['Customer Name'])."',
																									contract_city='".addslashes($invoice_output['invoice_details']['data']['contract_city'])."',
																									client_state='".addslashes($invoice_output['invoice_details']['data']['state'])."',
																									client_gstn_state='".addslashes($invoice_output['customer_gst']['data']['gstn_state_name'])."',
																									client_gstn_server_city='".addslashes($invoice_output['customer_gst']['data']['server_city'])."',
																									client_building_address='".addslashes($invoice_output['customer_gst']['data']['gstn_address'])."',
																									client_email='".addslashes($invoice_output['invoice_details']['data']['Email'])."',
																									client_contactno='".addslashes($invoice_output['invoice_details']['data']['Contact No'])."',
																									client_pan='".$invoice_output['invoice_details']['data']['pan_no']."',
																									client_tan='".$invoice_output['invoice_details']['data']['tan_no']."',
																									client_gst='".$invoice_output['customer_gst']['data']['gstn_no']."',
																									
																									jd_cin_no='".$invoice_output['jd_gst']['data']['jd_cin_no']."',
																									jd_pan_no='".$invoice_output['jd_gst']['data']['jd_pan_no']."',
																									jd_gst='".$invoice_output['jd_gst']['data']['jd_jd_gst']."',
																									jd_building_address='".addslashes($invoice_output['jd_gst']['data']['jd_billing_address'])."',
																									jd_sac_code='".$invoice_output['jd_gst']['data']['jd_sac_code']."',
																									jd_state_name ='".$invoice_output['jd_gst']['data']['jd_state_name'] ."',
																									jd_city_name='".addslashes($invoice_output['jd_gst']['data']['jd_jd_city'])."',
																									dc_by ='".$invoice_output['jd_gst']['data']['dealclosed_by'] ."'
																									WHERE parentid='".$this->params['parentid']."' AND DATE(approval_date)='".$this->invDate."'";
			
           
			// $inv_res =$this->idc->query_sql($update_alldetails);
			 $inv_res			= parent::execQuery($update_alldetails, $this->idc_con);  
			 
			
			 $update_alldetails_main1 = "UPDATE db_invoice.tbl_reciept_invoice_record_nonecs SET 			
																									companyname		='".addslashes($invoice_output['invoice_details']['data']['Company Name'])."',
																									client_name		='".addslashes($invoice_output['invoice_details']['data']['Customer Name'])."',
																									approval_date	='".$this->invDate."',
																									city 			= '".addslashes($invoice_output['invoice_details']['data']['contract_city'])."',
																									billing_city    = '".addslashes($invoice_output['invoice_details']['data']['contract_city'])."',
																									state = '".addslashes($invoice_output['invoice_details']['data']['state'])."',
																									total_amount = '".$gross."',
																									gross_amount = '".round($totgrsamt[0])."',
																									net_amount = '".round($totnetamt[0])."',
																									CGST = '".round($cgst)."',
																									SGST = '".round($sgst)."',
																									IGST = '".round($igst)."',
																									Total_GST = '".round($tot_gst)."',
																									TDS = '".round($tot_sevice_tax)."',
																									instrumentAmount = '".$totinsamt."',
																									insert_date=NOW(),
																									clearance_date='".$this->invDate."',
																									jd_cin_no='".$invoice_output['jd_gst']['data']['jd_cin_no']."',
																									jd_pan_no='".$invoice_output['jd_gst']['data']['jd_pan_no']."',
																									jd_gst='".$invoice_output['jd_gst']['data']['jd_jd_gst']."',
																									jd_building_address='".addslashes($invoice_output['jd_gst']['data']['jd_billing_address'])."',
																									jd_sac_code='".$invoice_output['jd_gst']['data']['jd_sac_code']."',
																									client_building_address='".addslashes($invoice_output['customer_gst']['data']['gstn_address'])."',
																									client_state='".addslashes($invoice_output['invoice_details']['data']['state'])."',
																									client_email='".addslashes($invoice_output['invoice_details']['data']['Email'])."',
																									client_contactno='".addslashes($invoice_output['invoice_details']['data']['Contact No'])."',
																									client_pan='".$invoice_output['invoice_details']['data']['pan_no']."',
																									client_tan='".$invoice_output['invoice_details']['data']['tan_no']."',
																									client_gst='".$invoice_output['customer_gst']['data']['gstn_no']."',
																									contract_city='".addslashes($invoice_output['invoice_details']['data']['contract_city'])."',
																									dc_by ='".$invoice_output['jd_gst']['data']['dealclosed_by'] ."',
																									jd_state_name ='".$invoice_output['jd_gst']['data']['jd_state_name'] ."',
																									jd_city_name='".addslashes($invoice_output['jd_gst']['data']['jd_jd_city'])."',
																									contact_person	='".addslashes($invoice_output['invoice_details']['data']['Customer Name'])."' 
																									WHERE parentid='".$this->params['parentid']."' AND instrumentid='".$this->instrumentid."' AND version ='".$this->params['version']."'";
			
           
			// $con_billing_city1			=	$this->fnc->query_sql($update_alldetails_main1);
			  $con_billing_city1 = parent::execQuery($update_alldetails_main1, $this->fin_con); 
			 
			 
		}
	
	
	function htmlpdfgen_new($generate_invoice_fn)
        {
            
            $dealclosedInfo  = $this->dealclosedinf();
            $rec_array = array();	
			if($this->invDate == '')
			$this->invDate = date('Y-m-d',strtotime($dealclosedInfo['entryDate']));
			else
			$this->invDate = $this->invDate;
			
			if($this->instrumentid == '')
			$this->instrumentid = $dealclosedInfo['instrumentId'];
			else
			$this->instrumentid = $this->instrumentid;
            
            
            
			$insert_log_api = "INSERT INTO db_payment.tbl_invoice_api_log SET parentid ='".$this->params['parentid']."',
			instrumentid  ='".$instrumentid_n."',
			data_city = '".$this->params['data_city']."',
			version = '".addslashes($this->params['version'])."',
			api_call_date = NOW(),
			suc_flag = 1,
			entry_doneby ='".$this->params['usrcd']."',
			source ='".$this->params['module']."'";
			$insert_log			= parent::execQuery($insert_log_api, $this->idc_con); 
            
            
            
            
            
            $rec_array = array();	
            if($this->invDate == '')
			$invDate_n = date('Y-m-d',strtotime($dealclosedInfo['entryDate']));
			else
			$invDate_n = $this->invDate;
			
			if($instrumentid == '')
			$instrumentid_n = $dealclosedInfo['instrumentId'];
			else
			$instrumentid_n = $this->instrumentid;
			
			$recipt_output= json_decode($this->getInvoiceContent($generate_invoice_fn),true);
			$invalid_cycle = json_decode($recipt_output['annexure_details']['data']['annexure_header_content'],true);
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
			$email 						 = $invoice_details['Email'];
          
			 $buyerState    =	strtolower($recipt_output['invoice_annexure_number']['data']['buyer_state']);
			 $sellerState	=	strtolower($recipt_output['jd_gst']['data']['jd_state_name']);
           
            if($buyerState==$sellerState){
                $taxMode=0;
            }else if($buyerState!=$sellerState){
                $taxMode=1;
            }
			$dealclosedInfo_upd  = $this->updateAll_details($recipt_output,$taxMode);
            $this->gross=0;
			if($invalid_cycle['invalidCycle'] != 'Invalid cycle selected' && $recipt_output['invoice_annexure_number']['data']['invoice_no']!='')
			{
				$customerRec.= $this->getMainHeader_new('Customer Receipt');                       //For Tax Invoice
				
				$customerRec.= $this->htmlreciptgen_new('Customer Receipt',$recipt_output);        //For Tax Invoice
				$customerRec.= $this->htmlCustCatRcptdetails_new($recipt_output,$taxMode);
				$customerRec.=      "</table>
							<!--table2-->

				<!--table1-->";
			   $customerRec.=$this->getFooterAnx_new($recipt_output,$this->invDate);
			   $customerRec.="</div>";
			   $customerRec.="<div>&nbsp;</div><div style='width:100%;padding-top:50px;page-break-before: always;'>";
			   $customerRec.=$this->getFooterAnx_new_content($recipt_output,$this->invDate);
			   $customerRec.="</div></body>
				</html>";
				
			   

				// annexure starts
				

				 $Annexure	.=$this->getMainHeader_new('Annexure');
				 $Annexure 	.= $this->htmlreciptgen_new('Annexure',$recipt_output);
			   // if($this->gross>0)
				 $Annexure .= $this->htmlinstrumentdetails_new($recipt_output);
				 $asfasf=      "</table>
							<!--table2-->

				<!--table1-->";
				 $Annexure .= $this->htmlpincodeannexure_new($recipt_output);
				 $Annexure .= $this->htmlnationalannexure_new($recipt_output);
				 $Annexure .= $this->htmlcatannexure_new($recipt_output);
				 $Annexure .= $this->htmlbannerannexure_new($recipt_output);

				 $this->htmlgetnoinventory_new($recipt_output);

				 $Annexure .= $this->pdgmappingannexure_new($recipt_output);
				 $Annexure.=$this->getFooterAnx_new($recipt_output,$dealclosedInfo['entryDate']);
				 $Annexure.="</div></body>

				</html>";
				
				switch(strtolower($this->params['data_city']))
				{
					case 'mumbai':
					$pdf_city = 'mumbai';
					break;
					case 'delhi':
					$pdf_city = 'delhi';
					break;
					case 'kolkata':
					$pdf_city = 'kolkata';
					break;
					case 'bangalore':
					$pdf_city = 'bangalore';
					break;
					case 'chennai':
					$pdf_city = 'chennai';
					break;
					case 'hyderabad':
					$pdf_city = 'hyderabad';
					break;
					case 'pune':
					$pdf_city = 'pune';
					break;
					case 'ahmedabad':
					$pdf_city = 'ahmedabad';
					break;
					default:
					$pdf_city = 'remote';
					break;
				}
				
				$date_time = date('Y-m-d H:i:s');
				 $timestamp = strtotime($date_time);
				 $parentid = $this->params['parentid'];

					$htmlstr[0]=$customerRec;
					$htmlstr[1]=$Annexure;
					$name[0]="reciept".$timestamp;
					$name[1]="annexture".$timestamp;
					$width=3000;
					$height=3000;
					$city=$this->params['data_city'];
					if($invDate_n == '')
					{
						$month = date('M-Y');
					}else
					{
						$month=date('Y-m-d', strtotime($invDate_n));
					}
					
					$parentid=$this->params['parentid'];
					$ch = curl_init();
					$curlConfig = array(
					CURLOPT_URL            => "http://192.168.51.202/generatePDF1processnew.php",
					CURLOPT_POST           => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POSTFIELDS     => array(
						'htmlstr' => json_encode($htmlstr),
						'uniquename' => json_encode($name),
						'city' => $pdf_city,
						'month' => $month,
						'parentid'=>$instrumentid_n
			
						)
					);
					curl_setopt_array($ch, $curlConfig);
					$result = curl_exec($ch); 
					curl_close($ch);
					$res_path = json_decode($result,true);
				
					$total_amount_chk = str_replace(',','',$recipt_output['instrumnet_details']['data']['instrument_total']['totinsamt']); 
				
				// Customer Receipt starts
				 
				if($res_path['urltax']!='' && $res_path['urlanexture']!='' && $res_path['errorCode'] == 0 && $total_amount_chk>1)
				{
					//~ if($dealclosedInfo['single_ck']==1)
					//~ {
						//~ $process_status = 'single chk'; 
					//~ }
					
					$getUcode = "SELECT entry_doneby FROM payment_instrument_summary  WHERE parentid = '".$this->params['parentid']."' AND version='".$this->params['version']."' AND instrumentId='".$instrumentid_n."'";
					$resUcode = parent::execQuery($getUcode, $this->fin_con);
					$rowUcode = mysql_fetch_assoc($resUcode);
					
					if($this->params['usrcd'] == '')
					$ucode_new = $rowUcode['entry_doneby'];
					else
					$ucode_new = $this->params['usrcd'];
					
						  $insert_annexure_data = "INSERT INTO tbl_invoice_proposal_details SET parentid ='".$this->params['parentid']."',
							insert_date  ='".date('Y-m-d H:i:s')."',
							path = '".addslashes($res_path['urlanexture'])."',
							download_path = '".addslashes($res_path['urlanexture'])."',
							html_file_name = '".addslashes($res_path['urlanexture'])."',
							pdf_file_name = '".addslashes($res_path['urlanexture'])."',
							doc_type = 'annexure',
							userid ='".$ucode_new."',
							instrumentid ='".$instrumentid_n."',
							module ='".$this->params['module']."',
							email_id = '".$email."',
							filetype = 'pdf',
							version = '".$this->params['version']."',
							server_ip = '".$_SERVER['SERVER_ADDR']."',
							updatetime=now(),
							flag_send=1,
							pdf_generated = 1

							ON DUPLICATE KEY UPDATE

							insert_date  ='".date('Y-m-d H:i:s')."',
							path = '".addslashes($res_path['urlanexture'])."',
							download_path = '".addslashes($res_path['urlanexture'])."',
							html_file_name = '".addslashes($res_path['urlanexture'])."',
							pdf_file_name = '".addslashes($res_path['urlanexture'])."',
							doc_type = 'annexure',
							userid ='".$ucode_new."',
							instrumentid ='".$instrumentid_n."',
							module ='".$this->params['module']."',
							email_id = '".$email."',
							filetype = 'pdf',
							server_ip = '".$_SERVER['SERVER_ADDR']."',
							updatetime=now(),
							flag_send=1,
							pdf_generated = 1";
						 $annex_res = parent::execQuery($insert_annexure_data, $this->fin_con);   
						//$annex_res =$this->fnc->query_sql($insert_annexure_data);
					
					//if($dealclosedInfo['single_ck'] != 1)
					//{
					  $insert_inv_data = "INSERT INTO tbl_invoice_proposal_details SET parentid ='".$this->params['parentid']."',
							insert_date  ='".date('Y-m-d H:i:s')."',
							path = '".addslashes($res_path['urltax'])."',
							download_path = '".addslashes($res_path['urltax'])."',
							html_file_name = '".addslashes($res_path['urltax'])."',
							pdf_file_name = '".addslashes($res_path['urltax'])."',
							doc_type = 'custreceipt',
							userid ='".$ucode_new."',
							instrumentid ='".$instrumentid_n."',
							module ='".$this->params['module']."',
							email_id = '".$email."',
							filetype = 'pdf',
							version = '".$this->params['version']."',
							server_ip = '".$_SERVER['SERVER_ADDR']."',
							updatetime=now(),
							flag_send=1,
							pdf_generated = 1

							ON DUPLICATE KEY UPDATE

							insert_date  ='".date('Y-m-d H:i:s')."',
							path = '".addslashes($res_path['urltax'])."',
							download_path = '".addslashes($res_path['urltax'])."',
							html_file_name = '".addslashes($res_path['urltax'])."',
							pdf_file_name = '".addslashes($res_path['urltax'])."',
							doc_type = 'custreceipt',
							userid ='".$ucode_new."',
							instrumentid ='".$instrumentid_n."',
							module ='".$this->params['module']."',
							email_id = '".$email."',
							filetype = 'pdf',
							server_ip = '".$_SERVER['SERVER_ADDR']."',
							updatetime=now(),
							flag_send=1,
							pdf_generated = 1";
					//$invo_res =$this->fnc->query_sql($insert_inv_data);
					$invo_res = parent::execQuery($insert_inv_data, $this->fin_con); 
			//	}  
			
			$dealclosed_data = "INSERT INTO db_invoice.tbl_whatsapp_dealclosed_data SET parentid ='".$this->params['parentid']."',
                        instrumentid  ='".$instrumentid_n."',
                        dealclosed_date = '".$this->invDate."',
                        billnumber  ='".$instrumentid_n."',
                        billAmount  ='".$total_amount_chk."',
                        version = '".$this->params['version']."',
                        recieptno = '".$recipt_output['invoice_annexure_number']['data']['annexure_no']."',
                        billing_city = '".addslashes($this->params['data_city'])."',
                        dc_by = '".$ucode_new."',
                        entry_date = NOW(),
                        source = '".$this->params['module']."-geniolite',
                        path = '".addslashes($res_path['urltax'])."',
                        sent_to='".$email."',
                        exist_flag=1";
                  $log_dealclosed = parent::execQuery($dealclosed_data, $this->fin_con); 
			
			
			$log_al = "INSERT INTO db_invoice.tbl_invoice_log SET parentid ='".$this->params['parentid']."',
                        instrumentid  ='".$instrumentid_n."',
                        data_city = '".addslashes($this->params['data_city'])."',
                        version = '".$this->params['version']."',
                        source = '".$this->params['module']."',
                        path = '".addslashes($res_path['urltax'])."',
                        html = '".addslashes($customerRec)."'";
            $log_res =parent::execQuery($log_al, $this->fin_con); 
			
					if($this->params['module'] == 'me')
					{	 
						if($invo_res==1)
						{
							$rec_array['errorCode']	=	0;
							$rec_array['data']		=	$res_path;
						}else
						{
							$rec_array['errorCode']	=	1;
						}
						
						$insert_log_api = "INSERT INTO db_payment.tbl_invoice_api_log SET parentid ='".$this->params['parentid']."',
						instrumentid  ='".$instrumentid_n."',
						data_city = '".$this->params['data_city']."',
						version = '".addslashes($this->params['version'])."',
						api_call_date = NOW(),
						suc_flag = 2,
						entry_doneby ='".$this->params['usrcd']."',
						source ='".$this->params['module']."'";
						$insert_log			= parent::execQuery($insert_log_api, $this->idc_con); 
						
						
						return json_encode($rec_array);
					}
				}else
				{
					$rec_array['errorCode']	=	0;
					//$rec_array['message']		=	'success';
					$rec_array['data']['message']		=	'success';
					$rec_array['data']['contract']		=	'Single Check Instrument';
					return json_encode($rec_array);
				}
			}else
			{
				$rec_array['errorCode']	=	0;
				$rec_array['data']['message']		=	'success';
				$rec_array['data']['contract']		=	'invalid cycle and invoice number not generated';
				return json_encode($rec_array);
			}
			
			
        }
	  function dealclosedinf()
        {
			$sel_Inf = "SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' and a.version='".$this->params['version']."' GROUP BY a.entry_date DESC";
			//$inv_res =$this->fnc->query_sql($sel_Inf);
			$inv_res = parent::execQuery($sel_Inf, $this->fin_con); 
			$all_details = mysql_fetch_assoc($inv_res);
			$all_num_rows = mysql_num_rows($inv_res);
			if($all_num_rows == 0)
			{
				$sel_Inf = "SELECT parentid,entry_date as entryDate,campaignId,version FROM payment_apportioning WHERE parentid='".$this->params['parentid']."' AND VERSION='".$this->params['version']."' AND cfwd_version='' GROUP BY entry_date DESC";
				$inv_res = parent::execQuery($sel_Inf, $this->fin_con); 
				$all_details = mysql_fetch_assoc($inv_res);
			}
			return $all_details;
			
			/*$all_details = array();
			$sel_Inf = "SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' and a.version='".$this->params['version']."'GROUP BY a.entry_date DESC";
			//$inv_res =$this->fnc->query_sql($sel_Inf);
			$inv_res = parent::execQuery($sel_Inf, $this->fin_con);
			$all_num_rows = mysql_num_rows($inv_res);
			$all_details = mysql_fetch_assoc($inv_res);
			$single_chk_val=0;
			$all_details['single_ck'] = $single_chk_val;
			if($all_num_rows==0)
			{
				$single_chk = "SELECT * FROM payment_contract_multicity WHERE destparentid='".$this->params['parentid']."' AND version='".$this->params['version']."'";
				$inv_single = parent::execQuery($single_chk, $this->fin_con);
				$single_res = mysql_fetch_assoc($inv_single);
				
				$sel_Inf = "SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$single_res['sourceparentid']."' and a.version='".$single_res['version']."'GROUP BY a.entry_date DESC";
				$inv_res = parent::execQuery($sel_Inf, $this->fin_con);
				$all_num_rows = mysql_num_rows($inv_res);
				$all_details = mysql_fetch_assoc($inv_res);
				$single_chk_val=1;
				$all_details['single_ck'] = $single_chk_val;
				
			}*/
			
		}
	 function getMainHeader_new($type='Customer Receipt'){
			$main='
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head><meta charset="UTF-8" /><title>'.$type.'</title></head>
			<body><div style="border:1px solid black;border-spacing: 0px;width:100%">';
			return $main; 
		}
		 function htmlreciptgen_new($type,$recipt_output){
           
           
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
            
			$gstn_state_name    		 = $invoice_details['state'];
			$email 						 = str_replace(',','<br>',$invoice_details['Email']);
			
            $strhtmlRecipt  = "<html><head><title>Recipt</title></head><body>";
            $strhtmlRecipt .= "<table width='100%' border='0' cellspacing='0' cellpadding='10' style='border:1px solid #000;'>";
           

				$invoice_no='';
                $invoice_det='';
                if(isset($instrumnet_details['all_instruments'][0])){
                    $invoice_det =$instrumnet_details['all_instruments'][0];

                }
                else{
                    foreach ($instrumnet_details['all_instruments'] as $key => $inv_details) {
                        $invoice_det =$inv_details;
                    }
                }
			
                //$invoice_no=$gst_info['invoice_annexure_number']['invoice_no'];
            switch($type)
			{
				case 'Customer Receipt' : $invoice_no=$invoice_annexure_number['annexure_no'];
							 break;
				case 'Annexure' :$invoice_no=$invoice_annexure_number['invoice_no'];
							break;	
										
			}
			  //GST Details starts here
                //$gstn_contract_name =((count($gst_info['customer_gst']) == 0) || $gst_info['customer_gst']['gstn_contract_name']=='')?$company_info['comp_gen_info']['Company Name']:$gst_info['customer_gst']['gstn_contract_name'];
                
                $gstn_contract_name 	=$invoice_details['Company Name'];
                $gstn_costomer_name 	=$invoice_details['Customer Name'];
                $gstn_address 			=$invoice_details['Billing Address'];
                $ContactNo 				=$invoice_details['Contact No'];
                $pan_no				 	=$invoice_details['pan_no'];
                $tan_no 				=$invoice_details['tan_no'];
                $gstn_no 				=$customer_gst['gstn_no'];
                
                $invoiceDate = $invoice_details['Date'];
                //$invoice_no = $invoice_annexure_number['annexure_no'];
                $BillingParentid = $invoice_details['Billing Parentid'];
                $jd_cin_no               = $jd_gst['jd_cin_no'];
                $jd_pan_no               = $jd_gst['jd_pan_no'];
                $jd_jd_gst               = $jd_gst['jd_jd_gst']; 
                $jd_billing_address      = $jd_gst['jd_billing_address'];
                $jd_sac_code             = $jd_gst['jd_sac_code']; 
                
               
                //GST Details ends here
				
				if($this->module == 'cs')
					$img_lo = 'images/jdbig_logo.gif'; 
				else
				$img_lo = 'images/jdbig_logo.gif';
				
                $strhtmlRecipt  .= "</table></div></body></html>";
                $strhtmlRecipt  ='';
                $strhtmlRecipt  .= "<tr><td>";
                $strhtmlRecipt  .= '<table width="100%" style="width:100%;font-size:13px;font-family:Verdana, sans-serif;padding:10px 12px;"  border="0" align="center" cellspacing="0" cellpadding="2" bgcolor="#ffffff">';
                $strhtmlRecipt  .='<tr><td  align="center" style="padding:10px 0" ><img src="http://192.168.1.141:100/resturant_dashboard/images/jd_logo.png"></td></tr>';
                $strhtmlRecipt  .='<tr><td  align="center" style="font-size:16px;font-family:Verdana, sans-serif;border-top:1px solid #000000;padding:15px 0;color:#000000"><b>'.$type.'</b></td></tr>';

                $strhtmlRecipt  .='<tr>';
                $strhtmlRecipt  .='<td style="padding:0 0 15px 0;">';
                $strhtmlRecipt  .='<table width="100%" style="padding:0 0 10px 0;" cellspacing="0"><!--table3-->';
                $strhtmlRecipt  .='<tr>';
                $strhtmlRecipt  .='<td width="50%" style="vertical-align:top;padding:0;font-family:Verdana, sans-serif;border-top:1px solid #000000;border-bottom:1px solid #000000;">';

                    $strhtmlRecipt  .='<table width="100%" cellpadding="0" cellspacing="0" style="vertical-align:top;">
                    <tr><td colspan="2" style="font-size:13px;padding:5px 0 5px 10px;font-family:Verdana, sans-serif;background:#e4e4e4;">Customer Details</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">Billing Name</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$gstn_contract_name.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">Contact Person</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$gstn_costomer_name.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">Billing Address</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$gstn_address.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">State</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;">'.$gstn_state_name.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">Email</td><td style="font-size:15px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$email.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">Contact No</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$ContactNo.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">PAN No</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$pan_no.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">TAN No</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$tan_no.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;padding:0 0 0 10px;">GST No</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$gstn_no.'</td></tr>
                    </table><!--inner50-->';

                $strhtmlRecipt  .='</td><!--td50%-->';

                $strhtmlRecipt  .='<td width="50%"  style="vertical-align:top;border-left:1px solid #000000;padding:0;font-family:Verdana, sans-serif;border-top:1px solid #000000;border-bottom:1px solid #000000;">';
                    $strhtmlRecipt  .='<table width="100%" cellpadding="0" cellspacing="0" style="vertical-align:top;">
                    <tr><td colspan="2" style="font-size:13px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;background:#e4e4e4;">Just Dial Details</td></tr>
                    <tr><td width="30%" style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">Date</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;">'.date('d-m-Y',strtotime($invoiceDate)).'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">Invoice No</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$invoice_no.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">Billing Parent ID</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$BillingParentid.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">CIN No</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$jd_cin_no.'</td></tr>
                    <tr><td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">PAN No</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$jd_pan_no.'</td></tr>
                    <tr>
                    <td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">GST of JD</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$jd_jd_gst.'</td></tr>
                    <tr>
                    <td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif; vertical-align:top;">JD Billing Address</td><td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$jd_billing_address.'</td></tr>
                    <tr>
                    <td style="font-size:12px;padding:5px 0 5px 20px;font-family:Verdana, sans-serif;">SAC Code</td>
                    <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;margin-left:20px;">'.$jd_sac_code.'</td></tr>
                    <tr>

                    </tr>
                    </table><!--inner50-->';

                $strhtmlRecipt  .='</td><!--td50%-->';

                $strhtmlRecipt  .='</tr>
                </table><!--table3-->
                </td>
                </tr>';

                return $strhtmlRecipt;

        }
        
        function htmlCustCatRcptdetails_new($recipt_output,$taxMode){
             $takenstring='';
             
            
            $invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
             

             $camps=json_decode($annexure_details['annexure_header_content'],1);

             $camps=$camps['campaign_name_arr'];
	
		

             $package=0;
             $omni=0;
             $jdrr=0;
             $banner=0;
             foreach ($camps as $key => $value) {

                if($value=='Phone Search - Package' || $value=='Supreme Pack'){
                    $package=1;
                }
                if($value=='Phone Search - Platinum/Diamond'){
                    $package=1;
                }
                if($value=='Competitors Banner' || strtolower($value)=='banner'){
                    $banner=1;
                }
                if($value=='National Registration - Phone'){
                    $package=1;
                }

                if($value=='Jdrr Plus'){

                }
                if($value=='JDRR'){
                    $jdrr=1;
                }

                if($value=='Jd Omni Set up Fee' || strtolower($value)=='Jd Omni Service' || strpos(strtolower($value), 'omni') !== false ||  strpos($value, 'combo') !==false || $value=='Omni Supreme' || $value=='Combo 2' ){
                    $omni=1;
                    if(strpos($value, 'combo') !==false || $value=='Omni Supreme' || $value=='Combo 2'){
                        $package=1;
                    }
                }

             }
             if($package==1 && $omni==1){
                $takenstring='Being Amount paid for Advertising Listings & JD Omni';
             }
             else if($omni==1){
                $takenstring='Being Amount paid for JD Omni';
             }
             else if($package==1){
                $takenstring='Being Amount paid for Advertising Listings';
             }
             else if($jdrr==1 && $banner==1){
                $takenstring='Being Amount paid for JDRR & Banner';
             }
             else if($banner==1){
                $takenstring='Being Amount paid for Banner';
             }
             else if($jdrr==1){
                $takenstring='Being Amount paid for JDRR';
             }
             else{
                if($jdrr!=1 && $banner!=1 && $package!=1 && $omni!=1)
                $takenstring='Being Amount paid for '.$value;
             }
             $strhtmlRecipt='';
             $strhtmlRecipt.='<tr>
                 <td align="center" style="font-size:15px;font-family:Verdana, sans-serif;padding:5px 0 15px;color:#000000"><b>'.$takenstring.' on Just Dial</b>
                 </td>
             </tr>';

             // category details start;
             //Company details
             $comp_data_output = json_decode($annexure_details['annexure_header_content'],true);



             //Packge Details
             if(count($annexure_details['annexure_content'][1]) > 0){
                $pkg_details= json_decode($annexure_details['annexure_content'][1]['category_details'],true);
                $pkg_cnst_output= json_decode($annexure_details['annexure_content'][1]['campaign_other_details'],true);
             }

             // SMS LEADS
             if(count($annexure_details['annexure_content'][4]) > 0){
                $sms_data_output= json_decode($annexure_details['annexure_content'][4]['category_details'],true);
             }

             //Banner Campaign
             if(count($annexure_details['annexure_content'][13]) > 0){
                $banner_data_output= json_decode($annexure_details['annexure_content'][13]['category_details'],true);
             }

             //Position and Inventory Details - category Name
             if(count($invoice_details['annexure_content'][2]['campaign_other_details']) > 0){
                $cat_output= json_decode($invoice_details['annexure_content'][2]['campaign_other_details'],true);
             }

             //Position and Inventory Details - position
             if(count($invoice_details['annexure_content'][2]) > 0){
                $catdata_output['ddg_details']= json_decode($invoice_details['annexure_content'][2]['category_details'],true);
             }
             //
            $strhtmlRecipt.='<tr><td style="padding:0 0 15px 0;">
            <table width="100%" style="padding:0 0 10px 0;" cellspacing="0" cellpadding="0">';

            $strhtmlRecipt.='<tr><td width="50%" style="vertical-align:top;padding:0;font-family:Verdana, sans-serif;">
            <table width="100%" style="vertical-align:top;padding:0 0 0 0;" cellspacing="0" cellpadding="0">';

            if(count($annexure_details['annexure_content'][2]) > 0 || count($annexure_details['annexure_content'][1]) >0 || count($annexure_details['annexure_content'][10]) >0 || count($annexure_details['annexure_content'][73]) >0 ){
                $cats_pkg=json_decode($annexure_details['annexure_content'][1]['category_details'],true);
                $n_cats_pkg=json_decode($annexure_details['annexure_content'][10]['category_details'],true);

                $cats_pdg=json_decode($annexure_details['annexure_content'][2]['campaign_other_details'],true);
                $cat_array=array();
              if(count($annexure_details['annexure_content'][73]) > 0){
                    $omni_details= json_decode($annexure_details['annexure_content'][73]['category_details'],true);

                    foreach ($omni_details['cat_details'] as $ckey => $categories) {
                        array_push($cat_array,$categories);
                    }

                }
                $cats_pdg_key=array();
                $cats_pkg_key=array();
                $n_cats_pkg_key=array();
                $cat_array_key=array();

                $total_key=array();
                $cats_pkg_key=array_keys($cats_pkg);
                $cats_pdg_key=array_keys($cats_pdg);
                $n_cats_pkg_key=array_keys($n_cats_pkg);
                $cat_array_key=$cat_array;

                if(count($cats_pkg_key)>0){
                    $total_key=array_merge($total_key,$cats_pkg_key);
                }
                if(count($cats_pdg_key)>0){
                    $total_key=array_merge($total_key,$cats_pdg_key);
                }
                if(count($n_cats_pkg_key)>0){
                    $total_key=array_merge($total_key,$n_cats_pkg_key);
                }
                if(count($cat_array_key)>0){
                    $total_key=array_merge($total_key,$cat_array_key);
                }
                $totalcat=count(array_unique($total_key));
                $words=str_replace('Only', '', $this->convertNumberToWord($totalcat));
                $words=str_replace('<br>', '', $words);
                $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Categories Count</td></tr>';
                $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">'.$words.' ('.$totalcat.')</td></tr>';
            }


            $pincode_pkg= json_decode($annexure_details['annexure_content'][1]['campaign_other_details'],true);
            $pincode_pdg= json_decode($annexure_details['annexure_content'][2]['category_details'],true);


            if(count($annexure_details['annexure_content'][2]) > 0){
            $pincode=array_keys($pincode_pdg);
            $pincodecount=count($pincode);
            }
            else if(count($annexure_details['annexure_content'][1]) > 0){

                $pincode=array_keys($pincode_pkg['pincode']);
                $pincodecount=count($pincode);
            }
            else if(count($annexure_details['annexure_content'][73]) > 0){
                $pincode= json_decode($annexure_details['annexure_content'][73]['campaign_other_details'],true);
                $pincodecount=count($pincode['pincode']);
            }
            $words=str_replace('Only', '', $this->convertNumberToWord($pincodecount));
            $words=str_replace('<br>', '', $words);
            if(count($annexure_details['annexure_content'][2]) > 0 || count($annexure_details['annexure_content'][1]) >0 || count($annexure_details['annexure_content'][73]) >0){

            $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Pincode Count</td></tr>';
            $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">'.$words.' ('.$pincodecount.')</td></tr>';
            }
            $banner_data_output= json_decode($annexure_details['annexure_content'][13]['category_details'],true);
            $catsponcount=0;
            $bannercount=0;
            if(count($banner_data_output['banner_details']) > 0){
                    foreach($banner_data_output['banner_details'] as $bannername=>$bannervalue){
                        if($bannervalue['spon_banner']=='Available'){
                            $catsponcount++;
                        }
                        if($bannervalue['comp_banner']=='Available'){
                            $bannercount++;
                        }

                    }
                    if($catsponcount>0){

                        $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Category Sponsership Categories Count</td></tr>';
                        $bwords=str_replace('Only', '', $this->convertNumberToWord($catsponcount));
						$bwords=str_replace('<br>', '', $bwords);
                        $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">'.$bwords.' ('.$catsponcount.')</td></tr>';
                    }
                    if($bannercount>0){

                        $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Banner Categories Count</td></tr>';
                        $bwords=str_replace('Only', '', $this->convertNumberToWord($bannercount));
						$bwords=str_replace('<br>', '', $bwords);
                        $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">'.$bwords.' ('.$bannercount.')</td></tr>';
                    }

            }

            $strhtmlRecipt.='<tr><td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Product Name</td> </tr>';

            $products='';

            $strhtmlRecipt.='<tr>';
            $strhtmlRecipt.='<td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">';
            $strhtmlRecipt.='<table width="100%">';

            foreach ($comp_data_output['campaign_name_arr'] as $productskey => $products) { 
                if(trim($products)!=''){
                $strhtmlRecipt.='<tr><td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/tax_in_sq_bullet.png"></td><td style="font-size:15px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">'.$products.'</td></tr>';
                }
            }
            $strhtmlRecipt.='</table></td></tr>';

            $otherdetails=json_decode($annexure_details['annexure_content'][1]['campaign_other_details'],true);

            if(count($otherdetails) > 0 ){
            $strhtmlRecipt.='<tr> <td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold">Package Consists Of*</td></tr>';
            $strhtmlRecipt.='<tr> <td style="font-size:13px;padding:10px;font-family:Verdana, sans-serif;vertical-align:top;">';

            $strhtmlRecipt.='<table width="100%">';
            $strhtmlRecipt.='<tr><td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/tax_in_sq_bullet.png">
            </td> <td style="font-size:13px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">App Priority Listing</td> </tr>';
			$strhtmlRecipt.='<tr>
			<td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/tax_in_sq_bullet.png">
			</td>
            <td style="font-size:13px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">Web Priority Listing</td>
			</tr>';
			$strhtmlRecipt.='<tr>
			<td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/tax_in_sq_bullet.png">
			</td>
            <td style="font-size:13px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">Wap Priority Listing</td>
			</tr>';
			$strhtmlRecipt.='<tr>
			<td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/tax_in_sq_bullet.png">
			</td>
            <td style="font-size:13px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">Phone Priority Listing</td>
			</tr>';
			$strhtmlRecipt.='</table>'; 
			}

			$strhtmlRecipt.='</td></tr></table></td>';
			
			$tottdsamt=0;

			$totnetamt=$instrumnet_details['instrument_total']['totnetamt'];
			$totsrvctax=$instrumnet_details['instrument_total']['totsrvctax'];
			$totsbctax=$instrumnet_details['instrument_total']['totsbctax'];
			$totkkctax=$instrumnet_details['instrument_total']['totkkctax'];
			$totgrsamt=$instrumnet_details['instrument_total']['totgrsamt'];
			$tottdsamt=$instrumnet_details['instrument_total']['tottdsamt'];
			$totinsamt=$instrumnet_details['instrument_total']['totinsamt'];
			
			$totinsamt=str_replace(',','', $totinsamt);
			$totinsamt+=0; 
			$strhtmlRecipt.='<td width="50%" style="vertical-align:top;padding:0;font-family:Verdana, sans-serif;">';
			$strhtmlRecipt.='<table width="100%" style="vertical-align:top;padding:0 0 0 20px;" cellpadding="0" cellspacing="0">';
			$strhtmlRecipt.='<tr><td style="font-size:13px;font-family:Verdana, sans-serif;">';
            $strhtmlRecipt.='<table width="100%" cellspacing="0" cellpadding="0"><tr>';
            $strhtmlRecipt.='<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold" width="70%"> Payment Details </td>';
			$strhtmlRecipt.='<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold"> &nbsp;</td>';
			$strhtmlRecipt.='<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;background-color:#e4e4e4;font-weight:bold;text-align:right"> Amount (Rs)</td></tr>';

			$strhtmlRecipt.='<tr>
            <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">Gross Product Amount</td>
			<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">&nbsp;</td>
			<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right"> '.$totnetamt.'</td>
			</tr>';

			$strhtmlRecipt.='<tr>
            <td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;border-top:1px solid #000000;border-bottom:1px solid #000000;" colspan="2"><b>(A)</b> Net Amount</td>
            <td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right;border-top:1px solid #000000;border-bottom:1px solid #000000;font-weight:bold">'.$totnetamt.'</td>
			</tr>';
            $totlForgst=str_replace(",", '', $totnetamt);
            if($instrumnet_details['instrument_total']['service_tax_percent']=='0.18'){
                if($taxMode==0){
                    $cgstPer=9;
                    $sgstPer=9;
                    $igstPer=0;
                }else if($taxMode==1){
                    $cgstPer=0;
                    $sgstPer=0;
                    $igstPer=18;
                }

                $totlForgst=(float)$totlForgst;
                $cgst=($cgstPer/100)*$totlForgst;

			$strhtmlRecipt.='<tr>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">CGST ('.$cgstPer.'%)</td>
			<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">(+)</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.round($cgst,2).'</td>
			</tr>';
                $sgst=($sgstPer/100)*$totlForgst;

			$strhtmlRecipt.='<tr>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">SGST/UTGST ('.$sgstPer.'%)</td>
			<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">(+)</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.round($sgst,2).'</td>
			</tr>';

                $igst=($igstPer/100)*$totlForgst;

                $strhtmlRecipt.='<tr>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">IGST ('.$igstPer.'%)</td>
			<td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">(+)</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.round($igst,2).'</td>
                </tr>';
                $tot_gst=$cgst+$sgst+$igst;

			$strhtmlRecipt.='<tr>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;"><b>(B)</b> Total GST (18%)</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">&nbsp;</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.round($tot_gst,2).'</td>
			</tr>';
                $gross=$totlForgst + $tot_gst - $tottdsamt;
            }else if($instrumnet_details['instrument_total']['service_tax_percent']=='0.15'){
                $tot_sevice_tax=(15/100)*$totlForgst;
                $strhtmlRecipt.='<tr>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;"><b>(B)</b> Total Service tax (15%)</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">&nbsp;</td>
                <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.round($tot_sevice_tax,2).'</td>
                </tr>';
                $gross=$totlForgst + $tot_sevice_tax - $tottdsamt;
            }

			$strhtmlRecipt.='<tr>
            <td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;border-top:1px solid #000000;border-bottom:1px solid #000000;font-weight:bold" colspan="2">(A + B) Grand Amount</td>
            <td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right;border-top:1px solid #000000;border-bottom:1px solid #000000;font-weight:bold">'.round(((float)$gross+(float)$tottdsamt),2).'</td>
			</tr>';
			 
            $strhtmlRecipt.='<tr>
            <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">TDS</td>
            <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;">(-)</td>
            <td style="font-size:13px;padding:10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right">'.$tottdsamt.'</td>
            </tr>';
			$strhtmlRecipt.='<tr>
			<td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;border-top:1px solid #000000;border-bottom:1px solid #000000;font-weight:bold" colspan="2">Instrument Amount </td>
            <td style="font-size:13px;padding:10px 10px;font-family:Verdana,sans-serif;vertical-align:top;text-align:right;border-top:1px solid #000000;border-bottom:1px solid #000000;font-weight:bold">'.round($gross).'</td>
			</tr>';
            $gross=round($gross);
			$this->gross=$gross;
			$strhtmlRecipt.='<tr>
			<td style="font-size:13px;padding:15px 10px;font-family:Verdana,sans-serif;vertical-align:top;" colspan="3">'.$this->convertNumberToWord($gross).'</td>';

			$strhtmlRecipt.='</tr></table></td></tr></table></td></tr></table></td></tr>';
			 //

			 return  $strhtmlRecipt;
		}
		
		 function getFooterAnx_new($recipt_output){
			  
			  
			  
            $otherdetails=json_decode($recipt_output['annexure_content'][1]['campaign_other_details'],true);
           
            $invoice_details 			 = $recipt_output['invoice_details']['data'];
           $Relationship_Manager = $recipt_output['invoice_details']['data']['Relationship Manager'];

            $footer.='

                            <table width="100%" style="padding:0 0 10px 0;font-size:13px;font-family:Verdana, sans-serif;"  cellspacing="0">
                                <!--table4-->
                                <tr>
                                    <td width="50%" style="vertical-align:top;padding:0;font-family:Verdana, sans-serif;">
                                        <table width="100%" style="vertical-align:top;padding:0 0 0 10px;">
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">For all TDS deductions, form 16A should be sent at tds@Justdial.com</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">For Terms of Service with Advertiser visit https://www.justdial.com/Terms-of-Use/Service-for-Advertiser.</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">Justdial will share information related to your account by SMS / Whatsapp / Email or App notifications</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">We fall in category of Advertisement Services under section 194C of Income Tax Act, wherein maximum TDS rate is mentioned as 2% on Net Amount.</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">Pursuant to Income Tax circular No 1/2014 dated 13-01-2014 Tds should not be deducted on tax.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25px;vertical-align:top;padding:12px 0 0;"><img src="images/small_sq_bullet.png">
                                                </td>
                                                <td style="font-size:12px;padding:5px 0;font-family:Verdana, sans-serif;vertical-align:top;line-height:20px">For any queries related to gst email us at gst@justdial.com
                                                </td>

                                            </tr>
                                        </table>
                                        <!--inner50-->
                                    </td>
                                    <!--td50%-->

                                    <td width="50%" style="vertical-align:top;padding:0;font-family:Verdana, sans-serif;">
                                        <table width="100%" style="vertical-align:top;padding:0 0 0 50px;">
                                            <tr>
                                                <td style="font-size:13px;padding:5px 0 60px 0;font-family:Verdana, sans-serif;font-weight:bold;">Relationship Manager : '.$Relationship_Manager.'</td>
                                            </tr>';
                                             
                                            if($this->invDate>='2018-07-19')
                                            {
												
													$img = 'http://192.168.1.141:100/resturant_dashboard/images/invoicesign.png';
												
													$footer.='<tr>';
													$footer.='<td class="tabtdwdth65" align="right" style="padding-right:210px">Sign :</td>';
													$footer.='</tr>';
													$footer.='<tr><td align="right" style="padding-right:40px"><img src="'.$img.'" height="100" width="235" class="tabwdhgt"></img></td>';
													$footer.='</tr>';
													$footer.='<tr><td align="right" class="tabfont13" style="font-size:14px;padding:5px 0;font-family:Verdana, sans-serif;padding-right:40px;">Authorised Signatory</td></tr>
													</tr>';
											}else
											{
												$img = 'http://192.168.1.141:100/resturant_dashboard/images/invoicesign1.png';
												
													$footer.='<tr>';
													$footer.='<td class="tabtdwdth65" align="right" style="padding-right:210px">Sign :</td>';
													$footer.='</tr>';
													$footer.='<tr><td align="right" style="padding-right:40px"><img src="'.$img.'" height="100" width="235" class="tabwdhgt"></img></td>';
													$footer.='</tr>';
													$footer.='<tr><td class="tabfont13" align="right" style="font-size:14px;padding:5px 0;font-family:Verdana, sans-serif;padding-right:40px;">Authorised Signatory</td></tr>
													</tr>';
											}
                                            

                                           // }
                                        $footer.='</table>
                                        </table>
                                        <!--inner50-->
                                    </td>
                                    <!--td50%-->

                                </tr>
                            </table>
                            <!--table4-->
                        </td>
                    </tr>';  
			//~ $footer.='	<table width="100%" style="padding:0 0 10px 0;font-size:13px;font-family:Verdana, sans-serif;" cellspacing="0"><tr>
                                    //~ <td align="center" style="font-size:12px;font-family:Verdana, sans-serif;border-top:1px solid #000000;padding:10px 0;color:#000000;border-bottom:1px solid #000000;">This is a system generated receipt</td>
			                    //~ </tr>
//~ 
//~ 
			                    //~ <tr>
			                        //~ <td align="center" style="font-size:15px;font-family:Verdana, sans-serif;font-weight:bold;padding:10px 0">Just Dial Limited</td>
			                    //~ </tr>
			                    //~ <tr>
                                    //~ <td align="center" style="font-size:12px;font-family:Verdana, sans-serif;padding:0 0 10px 0">CIN: L74140MH1993PLC150054</td>
			                    //~ </tr>
			                    //~ <tr>
			                        //~ <td align="center" style="font-size:12px;font-family:Verdana, sans-serif;line-height:20px">Registered & Corporate Office: Palm Court Building M, 501/B, 5th Floor, New Link Road, Besides Goregaon Sports Complex,
			                            //~ <br/> Malad West, Mumbai - 400064. Ph: 022-28884060 / 66976666, Fax: 022-28823789. Email: mumbai@justdial.com</td>
			                    //~ </tr></table>
//~ 
			               //~ 
			//~ ';
			return $footer;
		} 
		function getFooterAnx_new_content($recipt_output){
			  
			  
			  
            $otherdetails=json_decode($recipt_output['annexure_content'][1]['campaign_other_details'],true);
           
            $invoice_details 			 = $recipt_output['invoice_details']['data'];
           

              
			$footer.='<table width="100%"  border=1 align="center" cellspacing="0" cellpadding="2" bgcolor="#ffffff"><!--table1-->
<tr><td>
<table width="100%" style="width:100%;font-size:13px;font-family: Roboto, sans-serif;padding:10px 12px;"  border=0 align="center" cellspacing="0" cellpadding="2" bgcolor="#ffffff"><!--table2-->
<tr><td  align="center" style="padding:10px 0 15px" ><img src="http://192.168.1.141:100/resturant_dashboard/images/jd_logo.png"></td></tr>


<tr><td style="font-size:18px;font-family: Roboto, sans-serif;padding:25px 25px 10px;color:#000000;border-top:2px solid #000000;" class="tabfont13"><b>BASIC TERMS &amp; CONDITIONS (LISTING SERVICE)</b></td></tr>
<tr>
<td style="padding:0 0 15px 0;">
<table width="100%" style="padding:0 0 10px 0;" cellspacing="0" cellpadding="0"><!--table5-->
<tr>
<td  style="vertical-align:top;padding:0;font-family: Roboto, sans-serif;">
<table width="100%"  style="vertical-align:top;padding:0 0 0 20px;" cellpadding="0" cellspacing="0">
<tr>
<td style="font-size:15px;font-family: Roboto, sans-serif;">
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">
1. Contract’s duration is one year or more, unless otherwise determined by the parties under his agreement/contract. </td>
</tr>


<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">2. Upon the execution of ECS / CCSI / NACH / Direct Debit MANDATE Just Dial is authorized to DEDUCT the installment amount until Just Dial received advance notice as specified in clause 4 of the terms and service.</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">3. In case payment mode opted by the ADVERTISER/S IN ECS, CCSI & NACH, then the contract would be AUTOMATICALLY RENEWED on the same terms and conditions unless determined by parties. The automatic renewal is at the absolute discretion of Just Dial.</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">4. If Advertiser wishes to terminate ECS / CCSI / NACH / Direct Debit facility, then Advertiser has to provide prior written NOTICE OF 3 MONTHS to Just Dial, only upon the completion of minimum tenure of 9 (Nine) months from the effective date.</td>
</tr>


<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">5. Just Dial reserves the right to terminate the contract or its services at its discretion with or without cause or by serving 30 (Thirty) days written notice to the Advertiser/s.</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">6. Just Dial DOES NOT GUARANTEE and do not intend to guarantee any business to its vendor, it is merely a medium which connects general public with vendors of goods and services listed with Just Dial.</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">7. In case of any disputes, differences and/or claims arising out of the contract shall be settled by Arbitration in accordance with the provisions of Arbitration & Conciliation Act, 1996 or any statutory amendment thereof. The Arbitrator shall be appointed by the authorized representative/Director of Just Dial. The proceeding shall be conducted in English and held at Mumbai. The Award shall be final and binding. The Court of Mumbai shall have the exclusive jurisdiction.</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">8. The Advertiser has given his consent to contact him for any business promotion of Just Dial during the tenure of this agreement or even after the expiry of its tenure, whether the Advertiser has registered or not registered their entity/firm’s contact numbers in the “Do Not Call” registry of Telecom Regulatory Authority of India (TRAI).</td>
</tr>

<tr>
<td style="font-size:13px;padding:10px 4px;font-family: Roboto,sans-serif;vertical-align:top;" class="tabfont12">9. For more details  <a href="http://genio.in/tc/terms-of-service.html">Click here</a></td>
</tr>

</table>
</td>

</tr>
</table><!--inner50--></td><!--td50%-->

</tr>
</table><!--table5-->
</td>
</tr>


<tr><td  align="center" style="font-size:13px;font-family: Roboto, sans-serif;border-top:1px solid #000000;padding:10px 0;color:#000000;border-bottom:1px solid #000000;">This is a system generated invoice</td></tr>


<tr><td  align="center" style="font-size:25px;font-family: Roboto, sans-serif;font-weight:bold;padding:10px 0" class="tabfont13">Just Dial Limited</td></tr>
<tr><td  align="center" style="font-size:13px;font-family: Roboto, sans-serif;padding:0 0 10px 0">CIN: L74140MH1993PLC150054</td></tr>
<tr><td  align="center" style="font-size:13px;font-family: Roboto, sans-serif;line-height:20px">Registered & Corporate Office: Palm Court Building M, 501/B, 5th Floor, New Link Road, Besides Goregaon Sports Complex,<br/>
Malad West, Mumbai - 400064. Ph: 022-28884060 / 66976666, Fax: 022-28823789. Email: mumbai@justdial.com</td></tr>

</table><!--table2-->
</td>
</tr>
</table><!--table1-->
</body>
                          </html>';
            return $footer; 
		}
		 function htmlinstrumentdetails_new($recipt_output){
			
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
            $totnetamt=$instrumnet_details['instrument_total']['totnetamt'];
            $totsrvctax=$instrumnet_details['instrument_total']['totsrvctax'];
            $totnetamt=str_replace(',','', $totnetamt);
            $totsrvctax=str_replace(',','', $totsrvctax);
            //echo '<pre>';
            //print_r($company_info['gst_info']);
            //die();
			$strReciptPdf.='<tr>
			    <td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;">Instrument Details</td>
			</tr>';
			$strReciptPdf.= '<tr>';
			$strReciptPdf.= '<td style="vertical-align:top;">';
			$strReciptPdf.= '<table width="100%" style="padding:0 10px 30px 10px" cellspacing="0">';
	        $strReciptPdf.= '<tr>';
	            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;font-weight:bold;text-align:left;padding:7px 0;" width="33%">Date</td>';
	            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;font-weight:bold;text-align:left;padding:7px 0;" width="33%">Instrument Type</td>';
	            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;font-weight:bold;text-align:left;padding:7px 0;" width="33%">Instrument No.</td>';
	            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;font-weight:bold;text-align:right;padding:7px 0;" width="33%">Amount (Rs)</td>';
	        $strReciptPdf.= '</tr>';

			foreach($instrumnet_details['all_instruments'] as $instDetails=>$instDetailsVal){
				
				
				$instDetailsVal['grossamt']=str_replace(',', '',$instDetailsVal['grossamt']);
				$instDetailsVal['grossamt']=str_replace('.00', '',$instDetailsVal['grossamt']);
				$instDetailsVal['tdsamt']=str_replace(',', '',$instDetailsVal['tdsamt']);
				$instDetailsVal['tdsamt']=str_replace('.00', '',$instDetailsVal['tdsamt']);
				 $instDetailsVal['grossamt']= $instDetailsVal['grossamt']+0;

				
				$gross=$instDetailsVal['grossamt']-$instDetailsVal['tdsamt'];

				          $strReciptPdf.= '<tr>';
				            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;color:#747474;padding:7px 0" width="33%">'.date('d-M-Y',strtotime($instDetailsVal['date'])).'</td>';
				            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;color:#747474;padding:7px 0" width="33%">'.$instDetailsVal['chqno'].'</td>';
				            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;color:#747474;padding:7px 0" width="33%">'.$instDetailsVal['chqno_actual'].'</td>';
				            $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:right;color:#747474;padding:7px 0" width="33%">'.round($gross).'</td>';
				          $strReciptPdf.= '</tr>';

			 }	
			$totinsamt=$instrumnet_details['instrument_total']['totinsamt'];//need to check
			$tottdsamt=$instrumnet_details['instrument_total']['tottdsamt'];//need to check
			$totgrsamt=$instrumnet_details['instrument_total']['totgrsamt'];
			$totgrsamt=str_replace(",", '', $totgrsamt);
			$totgrsamt=str_replace(".00", '', $totgrsamt);
			$tottdsamt=str_replace(",", '', $tottdsamt);
			$tottdsamt=str_replace(".00", '', $tottdsamt);
			$gross=$totgrsamt - $tottdsamt;
			$gross+=0;
			$totinsamt=str_replace(',','', $totinsamt);
			$totinsamt+=0; 


				 $strReciptPdf.= '<tr>';
				     $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;padding:5px 0;border-top:1px solid #000000;font-weight:bold;" width="25%">&nbsp;</td>';
				     $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;padding:7px 0;border-top:1px solid #000000;font-weight:bold;" width="25%">Total Amount</td>';
				      $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:left;padding:5px 0;border-top:1px solid #000000;font-weight:bold;" width="25%">&nbsp;</td>';
				     $strReciptPdf.= '<td style="font-family:Verdana, sans-serif;font-size:15px;vertical-align:top;text-align:right;padding:7px 0;border-top:1px solid #000000;font-weight:bold;" width="25%">'. ($gross).'</td>';
				 $strReciptPdf.= '</tr>';
				$strReciptPdf.= '</table>';
				$strReciptPdf.= '</td>';

			 $strReciptPdf.= '</tr>';

			 return $strReciptPdf;

		}
		 function htmlpincodeannexure_new($recipt_output){
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
			 $strReciptPdf='';
			 $pdg=0;
			 if(count($annexure_details['annexure_content'][2]) > 0){ 
			 	$strReciptPdf .='<table width="100%" cellspacing="2" cellpadding="10" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
			 $strReciptPdf.= '<tr>';
			     $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="6">Pincode(s)</td>';

			 $strReciptPdf.= '</tr>';

			//	$strReciptPdf.= '<tr>';
				//$strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';

			 	//$strReciptPdf.= '<div style="padding:0 10px 0 10px;"><table cellpadding="4">';
			 	
			 	$pincode= json_decode($annexure_details['annexure_content'][2]['category_details'],true);
			 	$pincode=array_keys($pincode);
			 	$counter=0;


 	            foreach ($pincode as $pkey => $pincodev) {

 	            	if($counter==0)
 	            	$strReciptPdf.= '<tr>';	
 	            	 $strReciptPdf.= '<td style="text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;float:left;">'.trim(strstr($pincodev,'-',true)).'</td>';
 	            	 $counter++; 
 	            	if($counter%6==0)
 	            	 $strReciptPdf.= '</tr><tr>';	

 	             }


 	                // $strReciptPdf.= '</tr></table></div>';
			 	    //$strReciptPdf.= '</td>';
			 	$strReciptPdf.= '</tr></table>';
			 	
			 	$pdg=1;
			 }

			 if(count($annexure_details['annexure_content'][1]) > 0 && $pdg==0){ 
			 	
			 	$pincode= json_decode($annexure_details['annexure_content'][1]['campaign_other_details'],true);
			 	//var_dump($recipt_output['annexure_content']);
			 	$pincode=$pincode['pincode'];
			 	 	$strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
			 	 $strReciptPdf.= '<tr>';
			 	     $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="6">Pincode(s)</td>';
			 	 $strReciptPdf.= '</tr>';

			 		//$strReciptPdf.= '<tr>';
			 		//$strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
			 		//$strReciptPdf.= '<div style="padding:0 10px 0 10px;">';
			 		$counter=0; 
			 		  foreach ($pincode as $pkey => $pincodev) {
 	            	 if($counter==0)
 	            	 $strReciptPdf.= '<tr>';	
 	            	  $strReciptPdf.= '<td style="text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;float:left;">'.trim($pincodev).'</td>';
 	            	  $counter++; 
 	            	 if($counter%6==0)
 	            	  $strReciptPdf.= '</tr><tr>';	
 	             }
 	                // $strReciptPdf.= '</div>';
			 	    $strReciptPdf.= '</tr></table>';
			 	
			 	$pdg=1; 
			 }
			 if(count($annexure_details['annexure_content'][73]) > 0 && $pdg==0){ 
			 	$pincode= json_decode($annexure_details['annexure_content'][73]['campaign_other_details'],true);
			 		$strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
			 	 $strReciptPdf.= '<tr>';
			 	     $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;">Pincode(s)</td>';
			 	 $strReciptPdf.= '</tr>';

			 		/*$strReciptPdf.= '<tr>';
			 		$strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
			 		$strReciptPdf.= '<ul style="padding:0 10px 0 10px;">';
 	            	 $strReciptPdf.= '<li style="list-style-type:none;text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:150px;float:left;padding:5px 0;">'.trim($pincode['pincode']).'</li>'; 
 	            	 			 		$counter=0; */
 			 		 
  	            	 $strReciptPdf.= '<tr>';	
  	            	  $strReciptPdf.= '<td style="text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;float:left;">'.trim($pincode['pincode']).'</td>';
  	            	  
 	            /*     $strReciptPdf.= '</ul>';
			 	    $strReciptPdf.= '</td>';
			 	$strReciptPdf.= '</tr>';*/
			 	   $strReciptPdf.= '</tr></table>';

			 }
			 
			 return  $strReciptPdf; 

		}
		
		 function htmlnationalannexure_new($recipt_output){
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
			
			$strReciptPdf='';

			 if(count($annexure_details['annexure_content'][10]) > 0){ 
			 $strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
			 $strReciptPdf.= '<tr>
                        <td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="5">City Names</td>
                    </tr>'; 
                $natinalcities= json_decode($annexure_details['annexure_content'][10]['campaign_other_details'],true);
			 	$natinalcities=$natinalcities['cityarray'];
			 	/*$strReciptPdf.= '<tr>';
				$strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
				$strReciptPdf.= '<div style="padding:0 10px 0 10px;"><table cellpadding="3">';*/
			 	$counter=0; 
			 	$pincode= json_decode($annexure_details['annexure_content'][2]['category_details'],true);
			 	$pincode=array_keys($pincode);

 	            foreach ($natinalcities as $ncities => $cities) {
 	            	/* $strReciptPdf.= '<li style="list-style-type:none;text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:150px;float:left;padding:5px 0;">'.trim($cities).'</li>'; */
 	            	 if($counter==0)
			 			$strReciptPdf.= '<tr>';	
			 		            $strReciptPdf.= '<td style="text-align:left;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:350px;float:left;margin:5px;display:block">'.$cities."&nbsp;&nbsp;".'</td>';
			 		             $counter++; 
 	            	if($counter%5==0)
 	            	 $strReciptPdf.= '</tr><tr>';	        


 	             }
 	            /*     $strReciptPdf.= '</ul>';
			 	     $strReciptPdf.= '</tr></table></div>';
			 	$strReciptPdf.= '</tr>';*/
			 	$strReciptPdf.= '</tr></table>';
			 	
			 	
			 }
			 return  $strReciptPdf; 

		}
		 function htmlcatannexure_new($recipt_output){
			 
			 $invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
			 
			 
			 $cat_array=array();
			 // for package categories
			 if(count($annexure_details['annexure_content'][1]) > 0){ 
			 	$pkg_details= json_decode($annexure_details['annexure_content'][1]['category_details'],true);

			 	foreach ($pkg_details as $ckey => $categories) {
			 		array_push($cat_array,$categories); 
			 		
			 	}
			 		       
			 }
			 if(count($annexure_details['annexure_content'][10]) > 0){ 
			 	$pkg_details= json_decode($annexure_details['annexure_content'][10]['category_details'],true);

			 	foreach ($pkg_details as $ckey => $categories) {
			 		array_push($cat_array,$categories); 
			 		
			 	}
			 		       
			 }
			  if(count($recipt_output['annexure_content'][73]) > 0){
				$omni_details= json_decode($recipt_output['annexure_content'][73]['category_details'],true);
				
				foreach ($omni_details['cat_details'] as $ckey => $categories) {
					array_push($cat_array,$categories); 
					
				}

			}

			 if(count($annexure_details['annexure_content'][2]) > 0){ 
				
				$pdg_details= json_decode($annexure_details['annexure_content'][2]['campaign_other_details'],true);
			 	foreach ($pdg_details as $ckey => $categories) {
			 		array_push($cat_array,$categories['catname']); 
			 	}
			 }
			 $cat_array=array_unique($cat_array);
			 
			 if(count($cat_array) > 0){ 
			 	$strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
				$strReciptPdf.= '<tr>';
			     $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="3">Categories Taken</td>';
			 $strReciptPdf.= '</tr>';
			
			 	/* $strReciptPdf.= '<tr>';
	 		    $strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
	 		        $strReciptPdf.= '<div style="padding:0 10px 0 10px;"><table cellpadding="3">';*/
	 		        $counter=0; 
			 	foreach ($cat_array as $ckey => $categories) {
			 		if($counter==0)
			 			$strReciptPdf.= '<tr>';	
			 		            $strReciptPdf.= '<td style="text-align:left;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:350px;float:left;margin:5px;display:block">'.$categories."&nbsp;&nbsp;".'</td>';
			 		             $counter++; 
 	            	if($counter%3==0)
 	            	 $strReciptPdf.= '</tr><tr>';	        
			 	}
	 		    /*  $strReciptPdf.= '</tr></table></div>';
	 		    $strReciptPdf.= '</td>';

			 	$strReciptPdf.= '</tr>';*/
			 	 $strReciptPdf.= '</tr></table>';
			 }

			 return  $strReciptPdf; 
		}
		
		 function htmlbannerannexure_new($company_info,$recipt_output){
			
			 $invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
			 $strReciptPdf='';
			 $cat_array=array();
			
			$banner_data_output= json_decode($annexure_details['annexure_content'][13]['category_details'],true);
			
			
			if(count($banner_data_output['banner_details']) > 0){
				
					foreach($banner_data_output['banner_details'] as $bannername=>$bannervalue){
						if($bannervalue['spon_banner']=='Available'){
							$catsponcount++;
						}
					}
					 $strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
			 $strReciptPdf.= '<tr>';
			 $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="4">Banner Categories Taken</td>';
			 $strReciptPdf.= '</tr>';
			
			 	// $strReciptPdf.= '<tr>';
	 		  //  $strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
	 		     //   $strReciptPdf.= '<div style="padding:0 10px 0 10px;">';
	 		        $counter=0;
			 	foreach($banner_data_output['banner_details'] as $bannername=>$bannervalue){
			 		if($counter==0)
 	            	$strReciptPdf.= '<tr>';	
 		            $strReciptPdf.= '<td style="text-align:left;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:350px;float:left;margin:5px;display:block">'.$bannername."&nbsp;&nbsp;".'</td>';
 		             $counter++; 
 	            	if($counter%4==0)
 	            	 $strReciptPdf.= '</tr><tr>';	 
			 	}
	 		    /*  $strReciptPdf.= '</tr></table></div>';
				
				$strReciptPdf.= '</td>';

			 	$strReciptPdf.= '</tr>';*/
				$strReciptPdf.= '</tr></table>';
			}
			 if($catsponcount>0){ 	
			 $strReciptPdf .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 		
			 $strReciptPdf.= '<tr>';
			 $strReciptPdf.= '<td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="8">Category Sponsership Categories Taken</td>';
			 $strReciptPdf.= '</tr>';
			
			 	 /*$strReciptPdf.= '<tr>';
	 		    $strReciptPdf.= '<td style="vertical-align:top;padding:0 0 30px 0">';
	 		        $strReciptPdf.= '<div style="padding:0 10px 0 10px;"><table cellpadding="3">';*/
	 		        $counter=0;
			 	foreach($banner_data_output['banner_details'] as $bannername=>$bannervalue){
			 		if($bannervalue['spon_banner']=='Available'){
			 		if($counter==0)
 	            	$strReciptPdf.= '<tr>';	

 		            $strReciptPdf.= '<td style="text-align:left;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:350px;float:left;margin:5px;display:block">'.$bannername."&nbsp;&nbsp;".'</td>';
 		             $counter++; 
 	            	if($counter%4==0)
 	            	 $strReciptPdf.= '</tr><tr>';
 	            	}
			 	}
	 		     /* $strReciptPdf.= '</tr></table></div>';
				
				$strReciptPdf.= '</td>';

			 	$strReciptPdf.= '</tr>';*/
				 $strReciptPdf.= '</tr></table>';
			 }
			 	
			 return  $strReciptPdf; 
		}
		
		 function htmlgetnoinventory_new($recipt_output){
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];
			
			$catdata_output['ddg_details']=json_decode($annexure_details['annexure_content'][2]['category_details'],true);
			$package_cat=array();
			if(count($annexure_details['annexure_content'][2]['campaign_other_details']) > 0){ 
				$cat_output= json_decode($recipt_output['annexure_content'][2]['campaign_other_details'],true);
			}
			 
			$cnt=1;
			$page = ceil(count($cat_output)/6);
			$categories_count = 6;

			$cat_exists = array();
			$cat_output_main = $cat_output;
			$catid_main_arr = array();

			for($i=0;$i<$page;$i++) {
				$cat_cnt=0;	
				$cat_output = array_diff_assoc($cat_output_main,$catid_main_arr);	
				$catid_arr = array();
				$cat_cnt=0;
				$l=1;
				
				if(count($catdata_output['ddg_details'])> 0){
					if($i != 0){
						
					 }		
					  
				}
					
				foreach($cat_output as $catkey=>$catvalue) {		
				  $cat_cnt++;	
				 
				 if($cat_cnt<=$categories_count && ($cat_cnt<=count($cat_output))){
					$catid_arr[$catkey] = $catvalue;
					if(count($catdata_output['ddg_details'])> 0){
						
				 }
				$pagebreakcounter=0;
				 if($cat_cnt == $categories_count || ($cat_cnt<$categories_count && $cat_cnt == count($cat_output)))
				 {	
					$catid_main_arr = $catid_main_arr+$catid_arr;
					
					//$str .= "</tr>";
					foreach($catdata_output['ddg_details'] as $pincodename=>$pincode_value)
					{
						$pagebreakcounter++;

						  foreach($catid_main_arr as $catid=>$catid_val)
						  {
						  		 
							  if($pincode_value[$catid]){
						  		
							  	if(trim($pincode_value[$catid])=='-' || $pincode_value[$catid]== ' - ')
								{

									array_push($package_cat, $catid_val['catname']);

								}
								
							}

						  }	
						  
					}		
					
				 }
				 $l++;
			  }  
				
			}
			$str1='';

		 	if(count($package_cat)>0){ 
	 		$str1.= '<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">';
	 		$str1.= '<tr><td align="center" style="font-size:16px;font-family:Verdana, sans-serif;padding:10px 0;color:#000000;font-weight:normal;background-color:#e4e4e4;font-weight:bold;" colspan="6">Categories Which Got Package Due To Lack Of Inventory</td></tr>'; 
	 		/*$str1.= '<tr>'; 
	 		$str1.= '<td style="vertical-align:top;padding:0 0 30px 0">';*/
		 	//$str1.= '<div style="padding:0 10px 0 10px;">';
		 	$counter=0;
		 	$package_cat=array_unique($package_cat) ;
            foreach ($package_cat as $catkey => $catname) {

            	if($counter==0)
            	$str1.= '<tr>';	
            	 $str1.= '<td style="text-align:center;font-family:Verdana, sans-serif;font-size:15px;color:#747474;width:150px;float:left;">'.trim($catname).'</td>';
            	 $counter++; 
            	if($counter%6==0)
            	 $str1.= '</tr><tr>';	

             }
				$str1.= '</tr></table>'; 
	 		    //$str1.= '</td>';
			 	//$str1.= '</tr>';
			}
			 /* $str1=str_replace("<", "&ls", $str1);
			 	$str1=str_replace(">", "&gt", $str1);*/
			 return  $str1; 

		}
	}
	
	 function pdgmappingannexure_new($recipt_output){
			
			$invoice_details 			 = $recipt_output['invoice_details']['data'];
            $jd_gst 					 = $recipt_output['jd_gst']['data'];
            $customer_gst 				 = $recipt_output['customer_gst']['data'];
            $instrumnet_details 		 = $recipt_output['instrumnet_details']['data'];
            $invoice_annexure_number 	 = $recipt_output['invoice_annexure_number']['data'];
            $annexure_details 	 		 = $recipt_output['annexure_details']['data'];

			$strReciptPdf='';
			$catdata_output['ddg_details']=json_decode($annexure_details['annexure_content'][2]['category_details'],true);
			$package_cat=array();

			if(count($annexure_details['annexure_content'][2]['campaign_other_details']) > 0){ 
				$cat_output= json_decode($annexure_details['annexure_content'][2]['campaign_other_details'],true);
			}
			 
			$cnt=1;
			$page = ceil(count($cat_output)/4);

			$categories_count = 4;

			$cat_exists = array();
			$cat_output_main = $cat_output;
			$catid_main_arr = array();
/*			$str .='<tr>
                        <td style="font-size:13px;font-family:Verdana, sans-serif;padding:10px 0;font-weight:normal;">';*/
		   $str .='<table width="100%" cellspacing="0" style="border-bottom:1px solid #000000;padding: 0 0 20px;">'; 
		                      
			for($i=0;$i<$page;$i++) {
				

				$cat_cnt=0;	
				
				$cat_output = array_diff_assoc($cat_output_main,$catid_main_arr);
				
				$catid_arr = array();
				$cat_cnt=0;
				$l=1;
				
				if(count($catdata_output['ddg_details'])> 0){
					if($i != 0){
						$str .= "</tr><tr><td Height='20' colspan='7'></td></tr>";
					 }		
					  $str .= '<tr>
                                    <td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;background-color:#e4e4e4;font-weight:bold;text-align:center;padding:5px;">Pincode(s)</td>';
				}
				foreach($cat_output as $catkey=>$catvalue) {		
				  $cat_cnt++;	
				
				 
				 if($cat_cnt<=$categories_count && ($cat_cnt<=count($cat_output))){
					$catid_arr[$catkey] = $catvalue;
					if(count($catdata_output['ddg_details'])> 0){
						$str .= '<td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;background-color:#e4e4e4;font-weight:bold;text-align:center;padding:5px;">'.$catvalue['catname'].'</td>';
					 }
				 }
				$pagebreakcounter=0;
				
				 if($cat_cnt == $categories_count || ($cat_cnt<$categories_count && $cat_cnt == count($cat_output)))
				 {	
					$catid_main_arr = $catid_main_arr+$catid_arr;
					$str .= "</tr>";
					foreach($catdata_output['ddg_details'] as $pincodename=>$pincode_value)
					{
						
						$pagebreakcounter++;
						if($pagebreakcounter%50)
							$str .='<tr style="page-break-inside: auto"><td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;text-align:left;padding:5px;">'.$pincodename.'  </td>'; 
						else
					    $str .='<tr><td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;text-align:left;padding:5px;">'.$pincodename.'  </td>'; 
					 

						  foreach($catid_arr as $catid=>$catid_val)
						  {
						  	
							  if($pincode_value[$catid]){
								
							  	
								$str .= '<td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;text-align:center;padding:5px;">'.$pincode_value[$catid].'</td>'; 
								if(trim($pincode_value[$catid])=='-')
								{
									array_push($package_cat, $catid_val['catname']);
								}
								
							}
							else{
								$str .= '<td style="color:#747474;font-family:Verdana, sans-serif;font-size:13px;text-align:center;padding:5px;">X</td>'; 
							}
						  }	
						  $str .= "</tr>";				
					}		
					
					 $cat_output = '';
					
				 }
				 $l++;
			  }  
				
			}
			$str  .= "</table>";
			
		 	 
			 return  $str; 

		}
		
function convertNumberToWord($num = false)
{
    $num = str_replace(array(',', ' '), '' , trim($num));
    if(! $num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ( $hundreds == 1 ? '' : 's' ) . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}
	
	
	
	
	
	
}














?>
