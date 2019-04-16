<?php

class log_generate_invoice_content_class extends DB
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
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	
	const CURRENT_SERVICE_TAX = '0.15';
    const CURRENT_GST = '0.18';
	
	function __construct($params)
	{		
		$this->params = $params;	
		
		if(!$this->params['action'])
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		if($this->params['action'] == 1)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['version']) {
				$this->version = $this->params['version'];
			}else{
				$errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['module']) {
				$this->module = $this->params['module'];
			}else{
				$errorarray['errormsg']='module missing';
				echo json_encode($errorarray); exit;
			}
		
			if($this->params['data_city']) {
				$this->data_city = $this->params['data_city'];
			}else{
				$errorarray['errormsg']='data_city missing';
				echo json_encode($errorarray); exit;
			}
			
		}		
		
		if($this->params['action'] == 2)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			if($this->params['version']) {
				$this->version = $this->params['version'];
			}
			/*else{
				$errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}*/
            if($this->params['module']) {
                $this->module = $this->params['module'];
            }else{
                $errorarray['errormsg']='module missing';
                echo json_encode($errorarray); exit;
            }
             if(isset($this->params['invDate'])){
				$this->invDate = $this->params['invDate'];
			}else{
				$this->invDate	=	'';
			}
			 if(isset($this->params['instrumentid'])){
				$this->instrumentid = $this->params['instrumentid'];
			}else{
				$this->instrumentid	=	'';
			}
						
		}
		
		if($this->params['action'] == 3)
		{
			if($this->params['parentid']) {
				$this->parentid = $this->params['parentid'];
			}else{
			    $errorarray['errormsg']='parentid missing';
				echo json_encode($errorarray); exit;
			}
			
			/*else{
				$errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}*/
						
		}
		
        if($this->params['action'] == 4)
        {
            if($this->params['parentid']) {
                $this->parentid = $this->params['parentid'];
            }else{
                $errorarray['errormsg']='parentid missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['version']) {
                $this->version = $this->params['version'];
            }else{
                $errorarray['errormsg']='version missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['module']) {
                $this->module = $this->params['module'];
            }else{
                $errorarray['errormsg']='module missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['data_city']) {
                $this->data_city = $this->params['data_city'];
            }else{
                $errorarray['errormsg']='data_city missing';
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

        }
        
        if($this->params['action'] == 5)
        {
            if($this->params['parentid']) {
                $this->parentid = $this->params['parentid'];
            }else{
                $errorarray['errormsg']='parentid missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['version']) {
                $this->version = $this->params['version'];
            }else{
                $errorarray['errormsg']='version missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['module']) {
                $this->module = $this->params['module'];
            }else{
                $errorarray['errormsg']='module missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['data_city']) {
                $this->data_city = $this->params['data_city'];
            }else{
                $errorarray['errormsg']='data_city missing';
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
			 if(isset($this->params['ecs_month'])){
				$this->ecsMonth = $this->params['ecs_month'];
			}else{
				$this->ecsMonth	=	'';
			}
			if(isset($this->params['completeMonth'])){
				$this->completeMonth = $this->params['completeMonth'];
			}else{
				$this->completeMonth	=	'';
			}

        }
		
		
		 if($this->params['action'] == 6)
        {
			
            if($this->params['parentid']) {
                $this->parentid = $this->params['parentid'];
            }else{
                $errorarray['errormsg']='parentid missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['version']) {
                $this->version = $this->params['version'];
            }else{
				$this->version	=	'';
			}

            if($this->params['module']) {
                $this->module = $this->params['module'];
            }else{
                $errorarray['errormsg']='module missing';
                echo json_encode($errorarray); exit;
            }

            if($this->params['data_city']) {
                $this->data_city = $this->params['data_city'];
            }else{
                $errorarray['errormsg']='data_city missing';
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
			
            if(isset($this->params['ecs_month'])){
				$this->ecsMonth = $this->params['ecs_month'];
			}else{
				$this->ecsMonth	=	'';
			}

        }
        $this->mongo_flag = 0;
        $this->mongo_tme = 0;
        $this->ucode = null;
        $this->mongo_obj = new MongoClass();
        $this->categoryClass_obj = new categoryClass();
		$this->setServers();
		//echo json_encode('const'); exit;
		
	}
		
	// Function to set DB connection objects
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
		$this->fin_con_slave			= $db[strtolower($data_city)]['fin']['slave'];
		$this->db_budgeting		= $db[strtolower($data_city)]['db_budgeting']['master'];
		if(ALLUSER == 1){
			$this->mongo_flag = 1;
		}
		
		if(strtoupper($this->params['module']) == 'TME')
		{
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
		}		
	}
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->fin_con[0], $this->fin_con[1], $this->fin_con[2]) ; 
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}

	function log_invoice_content($contractdetailsclassobj)
	{
		$qryChecknewInvoiceName = "select invoice_businessname,invoice_cpersonname,invoice_cpersonnum from payment_otherdetails where parentid='".$this->parentid."' and version ='".$this->version."'";
		$resChecknewInvoiceName = parent::execQuery($qryChecknewInvoiceName, $this->fin_con);
		if($resChecknewInvoiceName && mysql_num_rows($resChecknewInvoiceName)>0){
			$rowChecknewInvoiceName = mysql_fetch_assoc($resChecknewInvoiceName);
			$newComapanyName = $rowChecknewInvoiceName['invoice_businessname'];
			$newContactPerson = $rowChecknewInvoiceName['invoice_cpersonname'];
			$newContactNo = $rowChecknewInvoiceName['invoice_cpersonnum'];
		}
		
		$sqlSel = "SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."' ";
		
		if($this->params['action']==4)
			$sqlSel = "SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."' ";
		
		
		$omni_combo_sql="SELECT * 
						 FROM  dependant_campaign_details_temp 
						 WHERE parentid='".$this->parentid."' AND version='".$this->version."'";


		$payment_type_sql="SELECT * 
						 FROM  tbl_payment_type 
						 WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
						 
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$resSel = parent::execQuery($sqlSel, $this->local_iro_conn);
			if($resSel && mysql_num_rows($resSel)>0)
			{
				$rowSel = mysql_fetch_assoc($resSel);
			}
			break;
			case 'tme':
			if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name";
				$rowSel 					= $this->mongo_obj->getData($mongo_inputs);	
			}
			else
			{
				$resSel 		= parent::execQuery($sqlSel, $this->local_tme_conn);
				if($resSel && mysql_num_rows($resSel)>0)
				{
					$rowSel = mysql_fetch_assoc($resSel);
				}
			}
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->local_tme_conn);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			break;
			case 'me':
			case 'jda':
			if(stripos($sqlSel, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'me';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name";
				$rowSel 					= $this->mongo_obj->getData($mongo_inputs);	
			}
			else
			{
				$resSel			= parent::execQuery($sqlSel, $this->idc_con);
				if($resSel && mysql_num_rows($resSel)>0)
				{
					$rowSel = mysql_fetch_assoc($resSel);
				}
			}
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			break;
			default:
			die('Invalid Module');
			break;
		}
		
		
		//if($resSel && mysql_num_rows($resSel)>0)
		//if(count($rowSel)>0)
		//{
		//	$rowSel = mysql_fetch_assoc($resSel);
		//}
	//	echo 'data <pre>';print_r($rowSel);
		if($newComapanyName!=''){
				$companyName	= trim($newComapanyName);
			}else{
				$companyName	= trim($rowSel['companyname']);
			} 
			
			if($newContactPerson!=''){
				$contact_person		= $newContactPerson;
			}else{
				$contact_person		= $rowSel['contact_person'];
			}
			
			if($newContactNo!=''){
				$number		= $newContactNo;
			}else{
				$number		= $rowSel['mobile'];
			}
			
			
			
			$land_str_area = $rowSel['building_name'].",".$rowSel['landmark'].",".$rowSel['street'].",".$rowSel['area'];
			$land_str_area = rtrim(ltrim($land_str_area,","),",");
			
			
			$payment_receipt_content['comp_gen_info']['Company Name'] = trim($companyName);
			$payment_receipt_content['comp_gen_info']['Customer Name']   = $contact_person;
			$payment_receipt_content['comp_gen_info']['Billing Parentid']= $this->parentid;
			$payment_receipt_content['comp_gen_info']['Billing Address']    = $land_str_area;
			$payment_receipt_content['comp_gen_info']['City']	     = $rowSel['city'].'-'.$rowSel['pincode'];
			$payment_receipt_content['comp_gen_info']['Email']	     = $rowSel['email'];
			$payment_receipt_content['comp_gen_info']['Contact No']	     = $number;
			$payment_receipt_content['comp_gen_info']['Pincode']	     = $rowSel['pincode'];
			
			
			
			
		
		$delete_invoice_receipt = "DELETE FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_delete_invoice_receipt = parent::execQuery($delete_invoice_receipt, $this->fin_con);			
			
		$delete_annexure = "DELETE FROM tbl_invoice_annexure_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_delete_annexure = parent::execQuery($delete_annexure, $this->fin_con);			
			
			
			
		/*------------------------------------------------------------------------------------------------*/
		
		$annexure_details['annexure_header']['companyname'] = $companyName.'  ('.$this->parentid.')';/*putting_company_name*/
		
		/*------------------------------------------------------------------------------------------------*/
		
			
		$sql_get_contract_campaigns = "SELECT a.campaignid,campaignname,a.version,budget,balance,ecsflag,duration,a.entry_date,tmeName,meName
										FROM payment_apportioning a JOIN payment_campaign_master b JOIN payment_otherdetails c
										ON a.campaignid=b.campaignid  AND a.parentid=c.parentid AND a.version=c.version
										WHERE a.parentid='".$this->parentid."' AND a.version='".$this->version."' and (budget>0 and budget<>balance) 
										ORDER BY a.entry_date,a.campaignid";
										
		$res_get_contract_campaigns = parent::execQuery($sql_get_contract_campaigns, $this->fin_con);
		
		if($res_get_contract_campaigns && mysql_num_rows($res_get_contract_campaigns))
		{
			while($row_get_contract_campaigns = mysql_fetch_assoc($res_get_contract_campaigns))
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
			}
		}
		
		$original_campaign_arr = $campaign_arr;
		//echo 'before<pre>';print_r($campaign_arr);
		//echo '<br> ecs flag ::'.$ecs_flag;
		//echo '<br>';
		
			
		$dp2yrs=0;
		if($omni_combo_res && mysql_num_rows($omni_combo_res)>0){
			
			$omni_combo_row=mysql_fetch_assoc($omni_combo_res);
			$omni_details_combo['omni_combo_name']=$omni_combo_row['combo_type']; 

		}

		if($payment_type_res && mysql_num_rows($payment_type_res)>0){
			
			$payment_type_row=mysql_fetch_assoc($payment_type_res);
			
			if(strpos($payment_type_row['payment_type'], 'package_10dp_2yr') !== false ){
				$dp2yrs=1;
			}
			$lfl_checking = explode(',',$payment_type_row['payment_type']);
		}
		
		//if(in_array("flexi_pincode_budget", $lfl_checking))
		if(in_array('flexi_pincode_budget',$lfl_checking) || in_array('fixed position',$lfl_checking) || in_array('package_10dp_2yr',$lfl_checking))
			$lfl_package = 1;
			else
			$lfl_package = 0;
		if($campaign_arr[1] && $campaign_arr[1]['duration']==3650 && $lfl_package == 1){
			
			$campaign_arr[1]['campaignname'] = 'VFL Package';
		}
		else if($campaign_arr[1] && mysql_num_rows($omni_combo_res)>0){

		$campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
		$original_campaign_arr[1]['campaignname'] = $omni_details_combo['omni_combo_name'];
		}
		else if($campaign_arr[1] && !mysql_num_rows($omni_combo_res)>0 && $campaign_arr[73]['budget'] == 1){
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
		
		if(count($campaign_arr)>0){
			foreach($campaign_arr as $campaign_details){
				$campaign_name_arr[]=$campaign_details['campaignname'];
				$campaign_id_arr[]=$campaign_details['campaignid'];
			}
			$campaign_name_arr = array_unique($campaign_name_arr);
			$campaign_id_arr = array_unique($campaign_id_arr);
		}
		

	/*--------------------------------------------------------------------------------------------------*/
		if(count($campaign_name_arr)>0){
			$annexure_details['annexure_header']['campaign_name_arr'] = $campaign_name_arr;/*list of campaign name */
			$annexure_details['annexure_header']['campaign_id_arr'] = $campaign_id_arr;/*list of campaign name */
		}
		
		if($deal_close_date) {
		$annexure_details['annexure_header']['Date'] = $deal_close_date;/*date of deal close */
		$payment_receipt_content['comp_gen_info']['Date'] = $deal_close_date;/*date of deal close */
		}
	/*------------------------------------------------------------------------------------------------*/
		if(count($campaign_name_arr)>0)
		$annexure_details['annexure_header']['payment_mode'] = ($ecs_flag)?'ECS':'NON-ECS';/*type of payment mode */
		
	/*------------------------------------------------------------------------------------------------*/
	
	    if($ecs_flag)
	    {
			$sql_ecs_mandate = "SELECT cycleselected,capamt FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
			$res_ecs_mandate = parent::execQuery($sql_ecs_mandate, $this->fin_con);
			if($res_ecs_mandate && mysql_num_rows($res_ecs_mandate))
			{
					$row_ecs_mandate = mysql_fetch_assoc($res_ecs_mandate);
					$cycle_selected  = $row_ecs_mandate['cycleselected'];
					$cap_amount 	 = $row_ecs_mandate['capamt'];
			}else{
				$sql_si_mandate = "SELECT cycleselected,capamt FROM db_si.si_mandate WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
				$res_si_mandate = parent::execQuery($sql_si_mandate, $this->fin_con);
				if($res_si_mandate && mysql_num_rows($res_si_mandate))
				{
					$row_si_mandate = mysql_fetch_assoc($res_si_mandate);
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
				if($this->module!='cs' && $this->module!='CS')
					die('Invalid cycle selected');
				break;
			}
			$annexure_details['annexure_header']['ECS']['Billing Cycle'] = $cyle;
			$annexure_details['annexure_header']['ECS'][$cyle.' Amount(inc Serv. Tax)'] = 'Rs.'.number_format($cap_amount);
			
		}
		
		/*--------------------------------Payment receipt content-----------------------------------------*/
		
		if(( count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && !count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for JD Omni';
		else if(( count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for Advertising Listing and JD Omni';
		else if(( !count(array_intersect(array('72','73','74'),array_keys($original_campaign_arr)))>0 ) && count(array_diff(array_keys($original_campaign_arr),array('72','73','74')))>0)
		$payment_receipt_content['receipt_title']	     = 'Being Amount paid for Advertising Listings on Just Dial';
		
		$payment_receipt_content['Relationship Manager']	     = $me_name;
		$payment_receipt_content['Telemarketing']			     = $tme_name;
		
        if($this->params['action'] == 4){
            $instrument_details_arr = $this->getInstrumentDetailsArrApproval($this->parentid,$this->version,$this->generate_inv,$this->invDate);
		
        }else{
		$instrument_details_arr = $this->getInstrumentDetailsArr($this->parentid,$this->version);
        }
		
		$payment_receipt_content['instrument_details'] = $instrument_details_arr;
		
//		ECHO 'RECEIPT CONTENT<pre>';
	//	print_r($payment_receipt_content);
				
		$sql_insert_annexure_header_log = "INSERT INTO tbl_invoice_payment_receipt_content(parentid,version,receipt_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','".$this->mysql_real_escape_custom(json_encode($payment_receipt_content))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['annexure_header']))."','".date('Y-m-d H:i:s')."','".$this->data_city."')";
		$res_insert_annexure_header_log = parent::execQuery($sql_insert_annexure_header_log, $this->fin_con);
			    
	    /*--------------------------------Payment receipt content-------------------------------------------*/
		
		//$omni_details_combo['omni_combo_name']=='Combo 2';
		
		
		$category_bidding_arr = $contractdetailsclassobj->GetPlatDiamCategories();
		
		//echo 'category bidding details arr <pre>';print_r($category_bidding_arr);
		//if($campaign_arr[2]['budget']>0 && count($category_bidding_arr))
		if(($campaign_arr[1]['budget']>0 || $campaign_arr[2]['budget']>0) && count($category_bidding_arr))
		{
			$catid_arr  = array_keys($category_bidding_arr);
			//echo 'category details arr <pre>';print_r($catid_arr);
			
			//$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catid_arr)."')";
			//$res_category = parent::execQuery($sql_category, $this->local_d_jds);
			$cat_params = array();
			$cat_params['page'] 		= 'log_generate_invoice_content_class';
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
				 $sql_areaname = "select pincode,group_concat(areaname ORDER BY callcnt_perday DESC) as areaname from tbl_areamaster_consolidated_v3  where data_city='".$this->data_city."' and type_flag=1 and  pincode in ('".implode("','",$pincode_areaname_arr)."') AND display_flag=1 group by pincode ORDER BY callcnt_perday DESC";
				 $res_areaname = parent::execQuery($sql_areaname, $this->local_d_jds);
				 
				 if($res_areaname && mysql_num_rows($res_areaname)>0)
				 {
					while($row_areaname=mysql_fetch_assoc($res_areaname)) {
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
			
			 $sql_insert_ddg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','2','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[2]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['ddg_details']))."','".$this->mysql_real_escape_custom(json_encode($category_name_arr))."','".date('Y-m-d H:i:s')."','".$this->data_city."')";
			$res_insert_ddg_log = parent::execQuery($sql_insert_ddg_log, $this->fin_con);			
			
			if(count($original_campaign_arr[1]['budget'])>0) {
				$sql_insert_pkg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','1','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[1]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['pkg_details']))."','','".date('Y-m-d H:i:s')."','".$this->data_city."')";
				$res_insert_pkg_log = parent::execQuery($sql_insert_pkg_log, $this->fin_con);
			}
				
		}
		if($campaign_arr[72]['budget']>0  || $campaign_arr[73]['budget']>0)
		{
			//$sql_pkg_cats ="select trim(',' FROM replace(catids,'|P|',',')) as catids from tbl_business_temp_data where contractid ='".$this -> parentid."'";
			/*switch(strtolower($this->module))
			{
				case 'cs':
				$sql_pkg_cats 	= "select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->local_d_jds);
				$row_pkg_cats	= mysql_fetch_assoc($res_pkg_cats);
				break;
				case 'tme':
				if($this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}else{
					$sql_pkg_cats 	= "select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
					$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->local_tme_conn);
					$row_pkg_cats 	= mysql_fetch_assoc($res_pkg_cats);
				}
				break;
				case 'me':
				case 'jda':
				//$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}else{
					$sql_pkg_cats 	="select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
					$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
					$row_pkg_cats 	= mysql_fetch_assoc($res_pkg_cats);
				}
				
				break;
				default:
				die('Invalid Module');
				break;
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
			
			if(count($row_pkg_cats)>0)
			{
				$catLst = $row_pkg_cats['catIds'];
				$catids_arr = explode(',',$catLst);
				if(count($catids_arr)>0)
				{
					//$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
					//$res_category = parent::execQuery($sql_category, $this->local_d_jds);
					
					$cat_params = array();
					$cat_params['page'] 	= 'log_generate_invoice_content_class';
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

					if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
					{
						foreach($cat_res_arr['results'] as $key=>$row_category)
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
					 $sql_insert_omni_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','73','".$this->mysql_real_escape_custom(json_encode($omnibgt_array))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['omni_other_details']['cat_details']))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['omni_other_details']['pincode']))."','".date('Y-m-d H:i:s')."','".$this->data_city."')";
					$res_insert_pkg_log = parent::execQuery($sql_insert_omni_log, $this->fin_con);
				}
			}
		}
		if(($campaign_arr[1]['budget']>0 && ($campaign_arr[2]['budget'] <= 0 )) || $campaign_arr[10]['budget']>0)
		{
			//$sql_pkg_cats ="select trim(',' FROM replace(catids,'|P|',',')) as catids from tbl_business_temp_data where contractid ='".$this -> parentid."'";
			/*switch(strtolower($this->module))
			{
				case 'cs':
				$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->local_d_jds);
				$row_pkg_cats 	= mysql_fetch_assoc($res_pkg_cats);
				break;
				case 'tme':
				if($this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}else{
					$sql_pkg_cats ="select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
					$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->local_tme_conn);
					$row_pkg_cats 	= mysql_fetch_assoc($res_pkg_cats);
				}
				break;
				case 'me':
				case 'jda':
				//$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->params['data_city'];
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$row_pkg_cats 				= $this->mongo_obj->getData($mongo_inputs);
				}else{
					$sql_pkg_cats 	="select catIds from tbl_business_temp_data where contractid ='".$this -> parentid."'";
					$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
					$row_pkg_cats 	= mysql_fetch_assoc($res_pkg_cats);
				}
				
				break;
				default:
				die('Invalid Module');
				break;
			}*/
			//if($res_pkg_cats && mysql_num_rows($res_pkg_cats)>0)
			
			
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
				//$row_pkg_cats = mysql_fetch_assoc($res_pkg_cats);
				$catLst = $row_pkg_cats['catIds'];
				$catids_arr = explode(',',$catLst);
				if(count($catids_arr)>0)
				{
					//$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
					//$res_category = parent::execQuery($sql_category, $this->local_d_jds);
					//here searchtype is not being used 
					$cat_params = array();
					$cat_params['page'] 		= 'log_generate_invoice_content_class';
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
						foreach($cat_res_arr['results'] as $key=>$row_category)
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

				 $sql_insert_pkg_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','1','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[1]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['bidding_details']['pkg_details']))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['package_other_details']))."','".date('Y-m-d H:i:s')."','".$this->data_city."')";
				$res_insert_pkg_log = parent::execQuery($sql_insert_pkg_log, $this->fin_con);
			}
			
		}
		
		if($campaign_arr[4]['budget']>0 || $campaign_arr[5]['budget']>0 || $campaign_arr[13]['budget']>0)
		{
			
			$qrysms = "select tcontractid,bid_value from tbl_smsbid_temp where bcontractid='".$this->parentid."'";
			
			$sql_get_comp_banner = "SELECT cat_name FROM tbl_comp_banner_temp WHERE parentid='".$this->parentid."'";
			
			$qrycatspon 		 = "SELECT cat_name FROM tbl_catspon_temp where parentid ='".$this->parentid."' AND campaign_type='1'";
			
			switch(strtolower($this->module))
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
			if($res_get_comp_banner && mysql_num_rows($res_get_comp_banner) && $campaign_arr[5]['budget']>0)
			{
			   while($row_get_comp_banner = mysql_fetch_assoc($res_get_comp_banner))
			   {
				   $comp_banner_cat_list[] = trim($row_get_comp_banner['cat_name']);
			   }
			   
			   $comp_banner_cat_list = array_unique($comp_banner_cat_list);
			}
			
			if($resqrycatspon && mysql_num_rows($resqrycatspon) && $campaign_arr[13]['budget']>0)
			{
				while($rowcatspon = mysql_fetch_assoc($resqrycatspon))
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
				
				$sql_insert_banner_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','13','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[5]).json_encode($original_campaign_arr[13]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['banner_campaign']))."','','".date('Y-m-d H:i:s')."','".$this->data_city."')";
			    $res_insert_banner_log = parent::execQuery($sql_insert_banner_log, $this->fin_con);
				
			}
			
			if($res_sms && mysql_num_rows($res_sms) && $campaign_arr[4]['budget']>0)
			{				
				while($row_sms = mysql_fetch_assoc($res_sms))
				{
					$sms_map_arr[$row_sms['tcontractid']]['bidvalue'] = $row_sms['bid_value'];
				}
				
				
				if(count($sms_map_arr))
				$parentid_list = array_keys($sms_map_arr);
				
				if(count($parentid_list)>0)
				{
					$c2s_comp     ="select parentid,companyname from c2s_nonpaid where parentid IN ('".implode("','",$parentid_list)."') group by parentid";
					$res_c2s_comp = parent::execQuery($c2s_comp, $this->local_d_jds);
					while($row_c2s_comp = mysql_fetch_assoc($res_c2s_comp))
					{
						$sms_map_arr[$row_c2s_comp['parentid']]['compname'] = $row_c2s_comp['companyname'];
					}
				}
				/*--------------------------------------------------------------------------------------------------*/	
				    $annexure_details['smspromo_campaign']['sms_promo_details'] = $sms_map_arr;	/*sms promo campaign details - campaignid : 4 */
				/*--------------------------------------------------------------------------------------------------*/
				
				
				$sql_insert_smspromo_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','4','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[4]))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['smspromo_campaign']))."','','".date('Y-m-d H:i:s')."','".$this->data_city."')";
			    $res_insert_smspromo_log = parent::execQuery($sql_insert_smspromo_log, $this->fin_con);
			    
			}
			
		}
		
		
			if($campaign_arr[10]['budget']>0)
			{
				
				$sql_national_temp      = "select Category_city, ContractTenure from tbl_national_listing_temp where parentid = '".$this->parentid."'";
				
				switch(strtolower($this->module))
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
				
				if($res_national_temp && mysql_num_rows($res_national_temp))
				{
					$row_national_temp	= mysql_fetch_assoc($res_national_temp);
					$nationalcityarr = explode('|#|',trim($row_national_temp['Category_city'],'|#|'));
					$annexure_details['national_listing_details'] = array('cityarray'=>$nationalcityarr, 'tenure'=>$row_national_temp['ContractTenure'],'categories'=>$national_listing_categories);
					$annexure_details['national_listing_other_details'] = array('cityarray'=>$nationalcityarr, 'tenure'=>$row_national_temp['ContractTenure']);
				}
				
				$sql_insert_smspromo_log = "INSERT INTO tbl_invoice_annexure_content(parentid,version,campaignid,campaign_budget_details,category_details,other_details,entry_date,data_city) VALUES('".$this->parentid."','".$this->version."','10','".$this->mysql_real_escape_custom(json_encode($original_campaign_arr[10]))."','".$this->mysql_real_escape_custom(json_encode($national_listing_categories))."','".$this->mysql_real_escape_custom(json_encode($annexure_details['national_listing_other_details']))."','".date('Y-m-d H:i:s')."','".$this->data_city."')";
			    $res_insert_smspromo_log = parent::execQuery($sql_insert_smspromo_log, $this->fin_con);
			    
				
			}
						
			//echo 'DDG category details arr <pre>';print_r($invoice_bidding_details_arr);
			//echo '<br>';
			//echo json_encode($category_name_arr);
			//echo '<br>';die;
			//echo 'package category details arr <pre>';print_r($package_category_array);
			
			//echo '<pre>annexure content';print_r($annexure_details);die;
			
			if($res_insert_annexure_header_log){
				$error['return_message']='receipt data logged';
				$error['code']='1';
				return $error; 
			}
			
			//echo '<br>'; echo json_encode($annexure_details['bidding_details']);die;
			
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
					while($row_cheque = mysql_fetch_assoc($res_cheque)){
						array_push($receiptDetailsarray,$row_cheque);
					}
					
				//payment details - cash
				$query_cash = "SELECT c.instrumentType,a.instrumentId, 
				c.approvalCode as paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_cc_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_cash = parent::execQuery($query_cash, $this->fin_con);
				while($row_cash = mysql_fetch_assoc($res_cash)){
					array_push($receiptDetailsarray,$row_cash);
				}

				//payment details - credit card
				$query_credit = "SELECT c.instrumentType,a.instrumentId,		
				c.receiptNo as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_cash_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_credit = parent::execQuery($query_credit, $this->fin_con);
				while($row_credit = mysql_fetch_assoc($res_credit)){
					array_push($receiptDetailsarray,$row_credit);
				}

				//payment details -NEFT Payments
				$query_neft = "SELECT c.instrumentType,a.instrumentId,		
				c.approvalCode as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
				FROM payment_instrument_summary AS a		
				INNER JOIN payment_neft_details AS c ON a.instrumentId = c.instrumentId 
				WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."'  AND a.paymentType ='FRESH'";
				$res_neft = parent::execQuery($query_neft, $this->fin_con);
				while($row_neft = mysql_fetch_assoc($res_neft)){
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
					WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND c.version='".$version."'  AND a.paymentType ='FRESH' AND c.fin_entry_flag=0";
					$res_payu = parent::execQuery($query_payu, $this->idc_con);
					while($row_payu = mysql_fetch_assoc($res_payu)){
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
				$totalTds 		  	+= round($value['tdsAmount'] + $totalTds);	
				
				$grossAmount 	  	= round($value['instrumentAmount'] + $value['tdsAmount']);
				$totalgrossAmount 	= round($grossAmount + $totalgrossAmount);


				
                //Check Service Tax starts
                $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->parentid."' AND version='".$this->version."'";
                $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
                while($rows = mysql_fetch_assoc($chkSrvcTaxRes)){
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
                if($resCampaignBudget && mysql_num_rows($resCampaignBudget)>0){
                    $rsCampaign = mysql_fetch_assoc($resCampaignBudget);
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
           }


        $instrument_arr['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
        $instrument_arr['all_instruments']   = $instrument_details_arr;
        $instrument_arr['instrument_total']  = $instrument_total_amt;

        return $instrument_arr;

    }

    function getInstrumentDetailsArrApproval($Pparentid,$version,$genInvoice,$invDate){
				if($genInvoice == 1){
					$whereDate		=	" and date(d.finalapprovaldate)>='".$invDate."'";
					$whereEnDate	=	" and date(a.entry_date)>='".$invDate."'";
				}else{
  	              $today=date('Y-m-d');
  	              $whereDate	=	 " and date(d.finalapprovaldate)>='".$today."'";
  	              $whereEnDate	=	 "";
				}
  	             $sqlgetmax="select max(end_time)  as today tbl_invoice_process_det";
                 $res_max = parent::execQuery($sqlgetmax, $this->fin_con);
                     while($row_max = mysql_fetch_assoc($res_max)){
                          $today=$row_max['today'];
 	                   }
 	                   

                $receiptDetailsarray = array();
                //payment details - cheque
                 $query_cheque = "SELECT  c.instrumentType,a.instrumentId,
                c.chequeNo AS paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,c.chequeNo, a.entry_date, a.paymentType,a.pan_no,a.tan_no,d.finalapprovaldate
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cheque_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND (a.app_version ='".$version."' or a.version='".$version."')  AND d.finalapprovalflag=1".$whereDate;

                $res_cheque = parent::execQuery($query_cheque, $this->fin_con);
                    while($row_cheque = mysql_fetch_assoc($res_cheque)){
                        array_push($receiptDetailsarray,$row_cheque);
	                   }
	                   

                //payment details - cash
                $query_cash = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails, a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no,d.finalapprovaldate
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cc_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND(a.app_version ='".$version."' or a.version='".$version."')   AND d.finalapprovalflag=1".$whereDate;

                $res_cash = parent::execQuery($query_cash, $this->fin_con);
                while($row_cash = mysql_fetch_assoc($res_cash)){
                    array_push($receiptDetailsarray,$row_cash);
                }

                //payment details - credit card
                 $query_credit = "SELECT c.instrumentType,a.instrumentId,
                c.receiptNo as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no,d.finalapprovaldate
                FROM payment_instrument_summary AS a
                INNER JOIN payment_cash_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND (a.app_version ='".$version."' or a.version='".$version."')  AND d.finalapprovalflag=1".$whereDate;

                $res_credit = parent::execQuery($query_credit, $this->fin_con);
                while($row_credit = mysql_fetch_assoc($res_credit)){
                    array_push($receiptDetailsarray,$row_credit);
                      
                }

                //payment details -NEFT Payments
                 $query_neft = "SELECT c.instrumentType,a.instrumentId,
                c.approvalCode as paymentdetails , a.instrumentAmount, IF(a.service_tax < 1,a.service_tax*100,a.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no,d.finalapprovaldate
                FROM payment_instrument_summary AS a
                INNER JOIN payment_neft_details AS c ON a.instrumentId = c.instrumentId
                INNER JOIN payment_clearance_details AS d ON a.instrumentId = d.instrumentId
                WHERE a.parentid = '".$Pparentid."' AND (a.app_version ='".$version."' or a.version='".$version."')   AND d.finalapprovalflag=1".$whereDate; 

                $res_neft = parent::execQuery($query_neft, $this->fin_con);
                while($row_neft = mysql_fetch_assoc($res_neft)){
                    array_push($receiptDetailsarray,$row_neft);
                }
                

                //payment details -Payu Payments
               if($version % 10 != 3){ 
				   
				 $query_payu	=	"SELECT a.instrumentType,a.instrumentId,a.instrumentAmount,IF(c.service_tax < 1,c.service_tax * 100,c.service_tax) AS service_tax,a.tdsAmount,a.parentid,IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount,a.entry_date,a.paymentType,a.pan_no,a.tan_no,d.finalapprovaldate FROM db_payment.payu_instrument_summary AS a INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId WHERE a.parentid = '".$Pparentid."' AND a.version = '12' AND c.version = '12' AND c.proc_flag = 1 AND inst_delete_flag = 0".$whereEnDate;
				  
               //~ $query_payu = "SELECT a.instrumentType,a.instrumentId,a.instrumentAmount, IF(c.service_tax < 1,c.service_tax*100,c.service_tax) as service_tax, a.tdsAmount, a.parentid, IFNULL(a.instrumentAmount, 0) + IFNULL(a.tdsAmount, 0) AS totalAmount, a.entry_date, a.paymentType,a.pan_no,a.tan_no
					//~ FROM db_payment.payu_instrument_summary AS a
					//~ INNER JOIN db_payment.genio_online_transactions AS c ON a.instrumentId = c.instrumentId
					
					//~ WHERE a.parentid = '".$Pparentid."' AND a.version ='".$version."' AND c.version='".$version."' ";

					$res_payu = parent::execQuery($query_payu, $this->idc_con);
					
					while($row_payu = mysql_fetch_assoc($res_payu)){
						array_push($receiptDetailsarray,$row_payu);
					}
				
				}
               
              
	if(count($receiptDetailsarray)>0)
              {
                $i=0;
                foreach($receiptDetailsarray as $key=>$value){
                $instrument_details_arr[$i]['date']     = $value['entry_date'];
                $instrument_details_arr[$i]['app_date'] = $value['finalapprovaldate'];
                $instrument_details_arr[$i]['id']     = $value['instrumentId'];
                //$instrument_details_arr[$i]['amount'] = $value['instrumentAmount'];

                $instrumentId       = $value['instrumentId'];
                $instrumentId_org   = $value['instrumentId'];

                //$instrumentAmount     = round($value['instrumentAmount']);
                $totalInstrumentAmount = round($value['instrumentAmount'] + $totalInstrumentAmount);

                //$tdsAmount            = round($value['tdsAmount']);
                $totalTds           = round($value['tdsAmount'] + $totalTds);
                $grossAmount        = round($value['instrumentAmount'] + $value['tdsAmount']);
                $totalgrossAmount   = round($grossAmount + $totalgrossAmount);


                //Check Service Tax starts
                 //~ $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->parentid."' AND version='".$this->version."'";
                 //~ $srviceTaxPerc=0;
                //~ $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
               	//~ if($chkSrvcTaxRes && mysql_num_rows($chkSrvcTaxRes)>0){			
	                //~ while($rows = mysql_fetch_assoc($chkSrvcTaxRes)){
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
				
				$totalAmount 	  	= round($value['totalAmount'] - $totalTds);	
				//$totalInstrumentAmt = round($totalAmount + $totalInstrumentAmt);
				$totalInstrumentAmt = $totalInstrumentAmt+round($value['instrumentAmount']);
				
				if(strtolower($value['instrumentType']) == 'cheque'){
					$chequeNo = $value['chequeNo']; 
				}else{
					$chequeNo = "NA"; 
				}	
				
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
				if($resCampaignBudget && mysql_num_rows($resCampaignBudget)>0){			
					$rsCampaign = mysql_fetch_assoc($resCampaignBudget);			
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
		      $instrument_total_amt['service_tax']  = $srviceTaxPerc; 
		   } 
		
		
		$instrument_arr['instrument_header'] = array('Date','Invoice No.','Chq.No.','Net Amt.','Service Tax','Swachh Bharat Cess','Krishi Kalyan Cess','Gross Amt.','TDS Amt.','Instrument Amt.');
		$instrument_arr['all_instruments']   = $instrument_details_arr;
		$instrument_arr['instrument_total']  = $instrument_total_amt;
		
		return $instrument_arr;
		
	}
	
	/*function get_all_instrument(){
		
		$resArr	=	array();
		$InstrumentDetailsarray	=	array();
		$query_instrument	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->parentid."' AND a.approvalStatus=1 GROUP BY a.entry_date DESC";
		$res_instrument 	= parent::execQuery($query_instrument, $this->fin_con);
		if(mysql_num_rows($res_instrument) > 0){
			$resArr['errorCode']	=	0;
			$resArr['data']['parentid']	=	$this->parentid;
			while($row_ins = mysql_fetch_assoc($res_instrument)){
				array_push($InstrumentDetailsarray,$row_ins);
				$resArr['data']['invoice/Annexure'][$row_ins['instrumentId']]['approved_date'][]		=	$row_ins['approvalDate'];
				//~ $resArr['data']['invoice'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
				$resArr['data']['receipt'][$row_ins['instrumentId']]['dealClosed_date'][]		=	$row_ins['entryDate'];
				//~ $resArr['data']['receipt'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
			}
			//~ $resArr['data']	=	$InstrumentDetailsarray;
		}else{
			$resArr['errorCode']	=	1;
			$resArr['msg']			=	'Instrument not found';
			
		}
		
		return $resArr;
	}*/
	
	
	function get_all_instrument(){
		
		$resArr	=	array();
		$InstrumentDetailsarray	=	array();
		 $query_instrument	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' AND a.approvalStatus=1 GROUP BY b.finalapprovaldate DESC";
		$res_instrument 	= parent::execQuery($query_instrument, $this->fin_con);
		
		 $query_instrument1	=	"SELECT *,b.finalapprovaldate as approvalDate,a.entry_date as entryDate FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='".$this->params['parentid']."' GROUP BY b.finalapprovaldate DESC";
		$res_instrument1 	= parent::execQuery($query_instrument1, $this->fin_con);
		
		$query_disruption	=	"SELECT * FROM payment_apportioning WHERE parentid='".$this->params['parentid']."' AND disruption_flag=4 GROUP BY VERSION ORDER BY entry_date DESC";
		$res_disruption 	= parent::execQuery($query_disruption, $this->fin_con);
		
		$query_instrument_companyname	=	"SELECT companyname FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$this->params['parentid']."'";
		$res_instrument_companyname 	= parent::execQuery($query_instrument_companyname, $this->local_iro_conn);	
		$row_instrument_companyname = mysql_fetch_assoc($res_instrument_companyname);
		if(mysql_num_rows($res_instrument) > 0 || mysql_num_rows($res_instrument1) > 0){
			$resArr['errorCode']	=	0;
			$resArr['data']['parentid']	=	$this->params['parentid'];
			$resArr['data']['companyname']	=	$row_instrument_companyname['companyname'];
			while($row_ins = mysql_fetch_assoc($res_instrument)){
				array_push($InstrumentDetailsarray,$row_ins);
				$resArr['data']['invoiceAnnexure'][$row_ins['instrumentId']]['approved_date'][]		=	date('Y-m-d',strtotime($row_ins['approvalDate']));
				$resArr['data']['invoiceAnnexure'][$row_ins['instrumentId']]['version'][]		=	$row_ins['version'];
				//~ $resArr['data']['invoice'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
				
				//~ $resArr['data']['receipt'][$row_ins['instrumentId']]['campaign'][]			=	$row_ins['campaign'];
			}
			while($row_ins1 = mysql_fetch_assoc($res_instrument1)){
					$resArr['data']['invoiceAnnexure'][$row_ins1['instrumentId']]['dealClosed_date'][]		=	date('Y-m-d',strtotime($row_ins1['entryDate']));
					$resArr['data']['invoiceAnnexure'][$row_ins1['instrumentId']]['version'][]				=	$row_ins1['version'];
			}
			
			while($row_ins2 = mysql_fetch_assoc($res_disruption)){
					$resArr['data']['invoiceAnnexure']['dispurtion']['dealClosed_date'][]		=	date('Y-m-d',strtotime($row_ins2['entry_date']));
					$resArr['data']['invoiceAnnexure']['dispurtion']['version'][]				=	$row_ins2['version'];
			}
			
			//~ $resArr['data']	=	$InstrumentDetailsarray;
		}else{
			$resArr['errorCode']	=	1;
			$resArr['msg']			=	'Instrument not found';
			
		}
		
		return json_encode($resArr);
	}
	
	
	function get_invoice_rules(){
		
		$state_details = array();
		/**********************1st Rule Start******************/
		
		$query_gst_state		="SELECT * FROM db_payment.tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->params['parentid']."' ORDER BY doneon DESC LIMIT 1";
		$resgstState	 		= parent::execQuery($query_gst_state, $this->idc_con);
		$rowgstState 			= parent::fetchData($resgstState);
			
		if($rowgstState['gstn_state_name']!='' && $rowgstState['gstn_state_name']!= null)
		{
			$stateName 				= $rowgstState['gstn_state_name'];
		}
			
		/***********************End************************/
		
		/**********************2nd Rule Start******************/
		
		 $sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
		if($this->params['action']==4)
			$sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
		switch(strtolower($this->module)){
			case 'cs': 
			$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_iro_conn);
			$rowDet = mysql_fetch_assoc($resSelUserDet);
			break;
			case 'tme':
			if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "city,companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_tme_conn); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			break;
			case 'me': 
			case 'jda':
			
			if(stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'me';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "city,companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			
			
			break;
			default: die('Invalid Module'); break;
		}
		
        if($stateName == ''){
			if(count($rowDet)>0){
				$stateName   = $rowDet['state'];
			}
        }
		
		/***********************End************************/
		
		/*****************3rd rule*************************/
		
		$getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->parentid."' AND version='".$this->version."';";
		$resUcode = parent::execQuery($getUcode, $this->idc_con);
		if(mysql_num_rows($resUcode)>0)
		{
			while($rowUcode = mysql_fetch_assoc($resUcode)){
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
			
		
		
		if($rowDet['state'] == '' && $stateName == '')
		{
			$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$ssourl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$transferstring = curl_exec($ch);
			curl_close($ch);
			$sso_det=json_decode($transferstring,TRUE);
			$employee_city=$sso_det['data']['city'];
			if($employee_city=='Delhi'){
				$employee_city='Noida';
			}

			$getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
			$stateData = parent::execQuery($getState, $this->local_d_jds);
			while($row = mysql_fetch_assoc($stateData)){
				$stateName  =$row['state_name'];
				$stateId    =$row['state_id'];
			}
		}
		
		$state_details['state_name'] =$stateName;
		$state_details['usercode'] =$usrCode;
		return $state_details;
		/***************End*******************/	
	}
	function get_invoice_content(){
		
		/**********Function calling for State and Usercode for Invoice******************/
		
		$stateDtetais_inv = $this->get_invoice_rules();
		$stateName = $stateDtetais_inv['state_name'];
		$usrCode   = $stateDtetais_inv['usercode'];
		
		
				
		/***********************End******************************************************/
			
			 $sql_receipt = "SELECT receipt_details,other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
			$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
			if($res_receipt && mysql_num_rows($res_receipt)>0)
			{
				$row_receipt = mysql_fetch_assoc($res_receipt);
			}
			
			$sql_annexure = "SELECT campaignid,category_details,other_details AS campaign_other_details FROM tbl_invoice_annexure_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
			$res_annexure = parent::execQuery($sql_annexure, $this->fin_con);
			if($res_annexure && mysql_num_rows($res_annexure)>0)
			{
				while($row_annexure = mysql_fetch_assoc($res_annexure)){
					$annexure_content['annexure_content'][$row_annexure['campaignid']]['category_details'] = $row_annexure['category_details'];
					$annexure_content['annexure_content'][$row_annexure['campaignid']]['campaign_other_details'] = $row_annexure['campaign_other_details'];
				}
				$row_receipt['annexure_content']= $annexure_content['annexure_content'];
			}
		
        /***************************End************************/
       
       
        $receipt_details_arr=array();

		$receipt_details_arr=json_decode($row_receipt['receipt_details'],TRUE);

	

       /* $getQry ="SELECT receipt_details FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
        $resQry = parent::execQuery($getQry, $this->fin_con);
        while($rowData = mysql_fetch_assoc($resQry)){
            $receipt_details_arr=json_decode($rowData['receipt_details'],TRUE);
        }
		*/
	
		
         $chk=$receipt_details_arr['gst_info']['receipt_no'];

        if($receipt_details_arr['gst_info']['receipt_no']==''){
            $RcptInvArray=$this->makeInvoiceRecieptId($stateName,'SN','');
            $invoiceNumber=$RcptInvArray['invoice'];
            $receiptNumber=$RcptInvArray['receipt'];
            $row_receipt['gst_info']['receipt_no']  = $receiptNumber;
            $row_receipt['gst_info']['invoice_no']  = $invoiceNumber;
        }else{
            $row_receipt['gst_info']['receipt_no']  = $receipt_details_arr['gst_info']['receipt_no'];
            $row_receipt['gst_info']['invoice_no']  = $receipt_details_arr['gst_info']['invoice_no'];
        }

		

        $row_receipt['gst_info']['deal_closed_user']    = $usrCode;

        //JD GST variables ends here
        
        /******************Jd Details Start From Here**********************/
        
        $ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$ssourl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$transferstring = curl_exec($ch);
		curl_close($ch);
		$sso_det=json_decode($transferstring,TRUE);
		$employee_city_jd=$sso_det['data']['city'];
		if($employee_city_jd=='Delhi'){
			$employee_city_jd='Noida';
		}

		$getState_jd="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city_jd."'";
		$stateData_jd = parent::execQuery($getState_jd, $this->local_d_jds);
		while($row_jd = mysql_fetch_assoc($stateData_jd)){
			$stateName_jd  	=$row_jd['state_name'];
			$stateId    	=$row_jd['state_id'];
		}
        
        
        
        $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName_jd."' AND jd_city='".$employee_city_jd."'";
        $resJdData = parent::execQuery($getJdData, $this->db_payment);
        if(mysql_num_rows($resJdData)==0){
            $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName_jd."'";
            $resJdData = parent::execQuery($getJdData, $this->db_payment);
        }

        while($row_resJdData = mysql_fetch_assoc($resJdData)){
            $row_receipt['gst_info']['jd_state_code']           = $row_resJdData['state_code'];
            $row_receipt['gst_info']['jd_state_name']           = $row_resJdData['state_name'];
            $row_receipt['gst_info']['jd_jd_city']              = $row_resJdData['jd_city'];
            $row_receipt['gst_info']['jd_cin_no']               = $row_resJdData['cin_no'];
            $row_receipt['gst_info']['jd_pan_no']               = $row_resJdData['pan_no'];
            $row_receipt['gst_info']['jd_jd_gst']               = $row_resJdData['jd_gst'];
            $row_receipt['gst_info']['jd_billing_address']      = $row_resJdData['jd_billing_address'];
            $row_receipt['gst_info']['jd_vat_tin_no']           = $row_resJdData['vat_tin_no'];
            $row_receipt['gst_info']['jd_sac_code']             = $row_resJdData['sac_code'];
            $row_receipt['gst_info']['jd_hsn_code']             = $row_resJdData['hsn_code'];
        }
        //JD GST variables ends here


        //GST variables starts here
        $sqlSelUserDet = "SELECT companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
        if($this->params['action']==4)
        	$sqlSelUserDet = "SELECT companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
        switch(strtolower($this->module)){
            case 'cs': 
            $resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_iro_conn); 
            $rowDet = mysql_fetch_assoc($resSelUserDet);
            break;
            case 'tme': 
            if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_tme_conn); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
            break;
            case 'me': 
            case 'jda':
            if(stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'me';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
            break;
            //case 'jda': $resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); break;
            default: die('Invalid Module'); break;
        }
        if(count($rowDet)>0){
                $contractName   =$rowDet['companyname'];
                $contactPerson  =$rowDet['contact_person'];
                $stateName      =$rowDet['state'];
                $areaName       =$rowDet['area'];
                $pincode        =$rowDet['pincode'];
                $fullAddress    =$rowDet['full_address'];
        }
        //write..
        
        $query_gst_details="SELECT * FROM tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->parentid."' ORDER BY doneon DESC LIMIT 1";
        $res_gst_details = parent::execQuery($query_gst_details, $this->db_payment);
        while($row_gst_details = mysql_fetch_assoc($res_gst_details)){
            $row_receipt['gst_info']['gstn_contract_name']   = $row_gst_details['gstn_contract_name'];
            $row_receipt['gst_info']['gstn_state_code']      = $row_gst_details['gstn_state_code'];
            $row_receipt['gst_info']['gstn_state_name']      = $row_gst_details['gstn_state_name'];
            $row_receipt['gst_info']['area']                 = $row_gst_details['area'];
            $row_receipt['gst_info']['gstn_address']         = $row_gst_details['gstn_address'];
            $row_receipt['gst_info']['gstn_pincode']         = $row_gst_details['gstn_pincode'];
            $row_receipt['gst_info']['gstn_no']              = $row_gst_details['gstn_no'];
        }
        $qryChecknewInvoiceName = "select invoice_businessname,invoice_cpersonname from payment_otherdetails where parentid='".$this->parentid."' and version ='".$this->version."'";
		$resChecknewInvoiceName = parent::execQuery($qryChecknewInvoiceName, $this->fin_con);
		if($resChecknewInvoiceName && mysql_num_rows($resChecknewInvoiceName)>0){
			$resChecknewInvoiceName = mysql_fetch_assoc($resChecknewInvoiceName);
			$odComapanyName = $rowChecknewInvoiceName['invoice_businessname'];
			$odContactPerson = $rowChecknewInvoiceName['invoice_cpersonname'];
		}else {
			$odComapanyName = '';
			$odContactPerson = '';
		}
		
		
		$checked_gstn_new 		= "select invoiceName from addInvoiceCompany where parentid='".$this->params['parentid']."'";
		$checkod_res_gst_new 	= parent::execQuery($checked_gstn_new, $this->fin_con);
		if($checkod_res_gst_new && parent::numRows($checkod_res_gst_new)>0){
			$checkod_arr_new = parent::fetchData($checkod_res_gst_new);
			$companyname_gst_new = $checkod_arr_new['invoiceName'];
		}
		
		if($companyname_gst_new != '' || $companyname_gst_new != null)
		{
			$row_receipt['gst_info']['gstn_contract_name'] = $companyname_gst_new;
		}
		if($companyname_gst_new == '' || $companyname_gst_new == null)
		{
			$row_receipt['gst_info']['gstn_contract_name'] = $row_receipt['gst_info']['gstn_contract_name'];
		}
		if(($companyname_gst_new == '' && $row_receipt['gst_info']['gstn_contract_name'] == '') || ($companyname_gst_new == null && $row_receipt['gst_info']['gstn_contract_name'] == null))
		{
			$row_receipt['gst_info']['gstn_contract_name'] = $odComapanyName;
		}
		if($companyname_gst_new == '' && $row_receipt['gst_info']['gstn_contract_name'] == '' && $odComapanyName=='')
		{
			$row_receipt['gst_info']['gstn_contract_name'] = $contractName;
		}
		
		
		
        if($row_receipt['gst_info']['gstn_contract_name']==''){
			if($odComapanyName != ''){
				$row_receipt['gst_info']['gstn_contract_name']=$odComapanyName;
			}else{
				$row_receipt['gst_info']['gstn_contract_name']=$contractName;
			}
        }
        if($row_receipt['gst_info']['gstn_state_name']==''){
            $row_receipt['gst_info']['gstn_state_name']=$stateName;
        }
        if($row_receipt['gst_info']['area']==''){
            $row_receipt['gst_info']['area']=$areaName;
        }
        if($row_receipt['gst_info']['gstn_pincode']==''){
            $row_receipt['gst_info']['gstn_pincode']=$pincode;
        }
        if($row_receipt['gst_info']['gstn_address']==''){
            $row_receipt['gst_info']['gstn_address']=$fullAddress;
        }
        //GST variables ends here

        //Check Service Tax starts
         $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->parentid."' AND version='".$this->version."'";
        $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
         $srviceTaxPerc=0;
        if($chkSrvcTaxRes && mysql_num_rows($chkSrvcTaxRes)>0){
	        while($rows = mysql_fetch_assoc($chkSrvcTaxRes)){
	            $srviceTaxPerc=$rows['service_tax_percent'];
	            //$srviceTaxPerc='0.15';
	        }
	      }
        
		if($this->params['action']==4)
        	$srviceTaxPerc=$receipt_details_arr['instrument_details']['instrument_total']['service_tax'];
        	
        if($srviceTaxPerc == 0 || $srviceTaxPerc =='')
        {
			$srviceTaxPerc=$receipt_details_arr['instrument_details']['instrument_total']['service_tax_percent'];
		}	
       
        
        $row_receipt['gst_info']['service_tax_percent']=$srviceTaxPerc;
        //Check Service Tax ends

        $totAmt=$receipt_details_arr['instrument_details']['instrument_total']['totnetamt'];
        $totlForgst=str_replace(",", '', $totAmt);
        $totlForgst=(float)$totlForgst;
        $buyerState=strtolower($stateName);
        $sellerState=strtolower($row_receipt['gst_info']['jd_state_name']);
        $tottdsamt=$receipt_details_arr['instrumnet_details']['instrument_total']['tottdsamt'];
        $tottdsamt_int=str_replace(",", '', $tottdsamt);
         $totinsamt=round(str_replace(",",'',$receipt_details_arr['instrument_details']['instrument_total']['totinsamt']));
        
        
        if($srviceTaxPerc=='0.18'){
            if($buyerState==$sellerState){
                $cgstPer=9;
                $sgstPer=9;
                $igstPer=0;
            }else if($buyerState!=$sellerState){
                $cgstPer=0;
                $sgstPer=0;
                $igstPer=18;
            }
            $cgst=($cgstPer/100)*$totlForgst;
            $sgst=($sgstPer/100)*$totlForgst;
            $igst=($igstPer/100)*$totlForgst;
            $tot_gst=$cgst+$sgst+$igst;
            //$gross_total=round($totlForgst + $tot_gst);
            $gross_total=round($totlForgst + $tot_gst -(int) $tottdsamt_int);
        }else if ($srviceTaxPerc=='0.15'){
            $cgstPer='0';
            $sgstPer='0';
            $igstPer='0';
            $cgst='0';
            $sgst='0';
            $igst='0';
            $tot_gst='0';
        }

        //UPDATE table with GST variables starts
        $receipt_details_arr['gst_info']['receipt_no']          = $row_receipt['gst_info']['receipt_no'];
        $receipt_details_arr['gst_info']['invoice_no']          = $row_receipt['gst_info']['invoice_no'];
        $receipt_details_arr['gst_info']['jd_state_code']       = $row_receipt['gst_info']['jd_state_code'];
        $receipt_details_arr['gst_info']['jd_state_name']       = $row_receipt['gst_info']['jd_state_name'];
        $receipt_details_arr['gst_info']['jd_jd_city']          = $row_receipt['gst_info']['jd_jd_city'];
        $receipt_details_arr['gst_info']['jd_cin_no']           = $row_receipt['gst_info']['jd_cin_no'];
        $receipt_details_arr['gst_info']['jd_pan_no']           = $row_receipt['gst_info']['jd_pan_no'];
        $receipt_details_arr['gst_info']['jd_jd_gst']           = $row_receipt['gst_info']['jd_jd_gst'];
        $receipt_details_arr['gst_info']['jd_billing_address']  = $row_receipt['gst_info']['jd_billing_address'];
        $receipt_details_arr['gst_info']['jd_vat_tin_no']       = $row_receipt['gst_info']['jd_vat_tin_no'];
        $receipt_details_arr['gst_info']['jd_sac_code']         = $row_receipt['gst_info']['jd_sac_code'];
        $receipt_details_arr['gst_info']['jd_hsn_code']         = $row_receipt['gst_info']['jd_hsn_code'];

        $receipt_details_arr['gst_info']['gstn_contract_name']  = $row_receipt['gst_info']['gstn_contract_name'];
        $receipt_details_arr['gst_info']['gstn_state_code']     = $row_receipt['gst_info']['gstn_state_code'];
        $receipt_details_arr['gst_info']['gstn_state_name']     = $stateName;
        $receipt_details_arr['gst_info']['area']                = $row_receipt['gst_info']['area'];
        $receipt_details_arr['gst_info']['gstn_address']        = $row_receipt['gst_info']['gstn_address'];
        $receipt_details_arr['gst_info']['gstn_pincode']        = $row_receipt['gst_info']['gstn_pincode'];
        $receipt_details_arr['gst_info']['gstn_no']             = $row_receipt['gst_info']['gstn_no'];
        $receipt_details_arr['gst_info']['initial_amt']         = $totlForgst;
        $receipt_details_arr['gst_info']['instrument_amt']         = $totinsamt;
        $receipt_details_arr['gst_info']['cgst_perc']           = $cgstPer;
        $receipt_details_arr['gst_info']['cgst']                = $cgst;
        $receipt_details_arr['gst_info']['sgst_perc']           = $sgstPer;
        $receipt_details_arr['gst_info']['sgst']                = $sgst;
        $receipt_details_arr['gst_info']['igst_perc']           = $igstPer;
        $receipt_details_arr['gst_info']['igst']                = $igst;
        $receipt_details_arr['gst_info']['tot_gst_perc']        = $cgstPer+$sgstPer+$igstPer;
        $receipt_details_arr['gst_info']['tot_gst']             = $tot_gst;
        
        
         
        
        if($srviceTaxPerc=='0.18'){
            $receipt_details_arr['gst_info']['gross_total']         = $gross_total;
        }else if($srviceTaxPerc=='0.15'){
            $tot_sev_tax=(float)$srviceTaxPerc*$totlForgst;
            $receipt_details_arr['gst_info']['tot_service_tax']     = $tot_sev_tax;
            $receipt_details_arr['gst_info']['gross_total']         = $totlForgst+$tot_sev_tax-(int)$tottdsamt_int;
        }

			$update_alldetails = "UPDATE db_payment.tbl_reciept_invoice_record SET 					
																									companyname = '".addslashes($receipt_details_arr['gst_info']['gstn_contract_name'])."',
																									client_name = '".$contactPerson."',
																									city = '".$city."',
																									state = '".$stateName."',
																									total_amount = '".$receipt_details_arr['gst_info']['instrument_amt']."',
																									gross_amount = '".round($receipt_details_arr['gst_info']['gross_total'])."',
																									net_amount = '".round($receipt_details_arr['gst_info']['initial_amt'])."',
																									CGST = '".round($receipt_details_arr['gst_info']['cgst'])."',
																									SGST = '".round($receipt_details_arr['gst_info']['sgst'])."',
																									IGST = '".round($receipt_details_arr['gst_info']['igst'])."',
																									Total_GST = '".round($receipt_details_arr['gst_info']['tot_gst'])."',
																									cgst_perc = '".$cgstPer."',
																									sgst_perc = '".$sgstPer."',
																									igst_perc = '".$igstPer."',
																									tot_gst_perc = '".$receipt_details_arr['gst_info']['tot_gst_perc']."',
																									TDS = '".round($tot_sev_tax)."',
																									instrumentAmount = '".$receipt_details_arr['gst_info']['instrument_amt']."',
																									insert_date=NOW(),
																									clearance_date = '".$this->invDate."',
																									jd_cin_no = '".$receipt_details_arr['gst_info']['jd_cin_no']."',
																									jd_pan_no = '".$receipt_details_arr['gst_info']['jd_pan_no']."',
																									jd_gst = '".$receipt_details_arr['gst_info']['jd_jd_gst']."',
																									jd_city_name = '".$receipt_details_arr['gst_info']['jd_jd_city']."',
																									jd_building_address = '".addslashes($receipt_details_arr['gst_info']['jd_billing_address'])."',
																									jd_sac_code = '".$receipt_details_arr['gst_info']['jd_sac_code']."',
																									client_building_address = '".addslashes($receipt_details_arr['gst_info']['gstn_address'])."',
																									client_state = '".$receipt_details_arr['gst_info']['gstn_state_name']."',
																									client_email = '".$email."',
																									client_contactno = '".$mobile."',
																									client_pan = '".addslashes($receipt_details_arr['gst_info']['gstn_address'])."',
																									client_gst = '".$receipt_details_arr['gst_info']['gstn_no']."',
																									client_gstn_state = '".$receipt_details_arr['gst_info']['gstn_state_name']."',
																									client_tan = '',
																									cron_flag = 1
																									WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
																									 $resJdData_upd = parent::execQuery($update_alldetails, $this->db_payment);
        $encReceipt_details_arr=json_encode($receipt_details_arr);

         $updateQry ="UPDATE tbl_invoice_payment_receipt_content SET receipt_details='".$this->mysql_real_escape_custom($encReceipt_details_arr)."' WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
        if($chk==''){
            $resultQry = parent::execQuery($updateQry, $this->fin_con);
        }
        //UPDATE table with GST variables ends
		return $row_receipt;
    }
	
	/*function get_invoice_content(){
        $getUcode="SELECT userid FROM deal_closed_users WHERE parentid='".$this->parentid."' AND version='".$this->version."';";
        $resUcode = parent::execQuery($getUcode, $this->idc_con);
        if(mysql_num_rows($resUcode)>0)
        {
            while($rowUcode = mysql_fetch_assoc($resUcode)){
                $usrCode=$rowUcode['userid'];
            }
        }
		$sql_receipt = "SELECT receipt_details,other_details as annexure_header_content FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_receipt = parent::execQuery($sql_receipt, $this->fin_con);
		if($res_receipt && mysql_num_rows($res_receipt)>0)
		{
			$row_receipt = mysql_fetch_assoc($res_receipt);
		}
		
		$sql_annexure = "SELECT campaignid,category_details,other_details AS campaign_other_details FROM tbl_invoice_annexure_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_annexure = parent::execQuery($sql_annexure, $this->fin_con);
		if($res_annexure && mysql_num_rows($res_annexure)>0)
		{
			while($row_annexure = mysql_fetch_assoc($res_annexure)){
				$annexure_content['annexure_content'][$row_annexure['campaignid']]['category_details'] = $row_annexure['category_details'];
				$annexure_content['annexure_content'][$row_annexure['campaignid']]['campaign_other_details'] = $row_annexure['campaign_other_details'];
			}
			$row_receipt['annexure_content']= $annexure_content['annexure_content'];
		}
        $ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$ssourl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $transferstring = curl_exec($ch);
        curl_close($ch);
        $sso_det=json_decode($transferstring,TRUE);
        $employee_city=$sso_det['data']['city'];
        if($employee_city=='Delhi'){
            $employee_city='Noida';
        }

        $getState="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
        $stateData = parent::execQuery($getState, $this->local_d_jds);
        while($row = mysql_fetch_assoc($stateData)){
            $stateName  =$row['state_name'];
            $stateId    =$row['state_id'];
        }
        
        
       
        $sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
		if($this->params['action']==4)
			$sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
		switch(strtolower($this->module)){
			case 'cs': 
			$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_iro_conn);
			$rowDet = mysql_fetch_assoc($resSelUserDet);
			break;
			case 'tme':
			if(($this->mongo_tme == 1) && (stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false))
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "city,companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_tme_conn); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			break;
			case 'me': 
			case 'jda':
			
			if(stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "city,companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			
			
			break;
			//case 'jda': $resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); break;
			default: die('Invalid Module'); break;
		}
		
        if($stateName == ''){
			//if($resSelUserDet && mysql_num_rows($resSelUserDet)>0){
			if(count($rowDet)>0){
				//$rowDet = mysql_fetch_assoc($resSelUserDet);
				$employee_city	=	$rowDet['city'];
				$stateName   = $rowDet['state'];
				if(strtolower($employee_city)	==	'delhi' || strtolower($employee_city) == 'gurgaon'){
					$employee_city	=	'Noida';
				}
				if(strtolower($stateName)	==	'delhi' || strtolower($stateName)	==	'haryana'){
					$stateName	=	'Uttar Pradesh';
				}
			}
        }
        $receipt_details_arr=array();

        $getQry ="SELECT receipt_details FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
        $resQry = parent::execQuery($getQry, $this->fin_con);
        while($rowData = mysql_fetch_assoc($resQry)){
            $receipt_details_arr=json_decode($rowData['receipt_details'],TRUE);
        }

        $chk=$receipt_details_arr['gst_info']['receipt_no'];

        if($receipt_details_arr['gst_info']['receipt_no']==''){
            $RcptInvArray=$this->makeInvoiceRecieptId($stateName,'SN','');
            $invoiceNumber=$RcptInvArray['invoice'];
            $receiptNumber=$RcptInvArray['receipt'];
            $row_receipt['gst_info']['receipt_no']  = $receiptNumber;
            $row_receipt['gst_info']['invoice_no']  = $invoiceNumber;
        }else{
            $row_receipt['gst_info']['receipt_no']  = $receipt_details_arr['gst_info']['receipt_no'];
            $row_receipt['gst_info']['invoice_no']  = $receipt_details_arr['gst_info']['invoice_no'];
        }

        $row_receipt['gst_info']['deal_closed_user']    = $usrCode;

        //JD GST variables ends here
        $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."' AND jd_city='".$employee_city."'";
        $resJdData = parent::execQuery($getJdData, $this->db_payment);
        if(mysql_num_rows($resJdData)==0){
            $getJdData="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName."'";
            $resJdData = parent::execQuery($getJdData, $this->db_payment);
        }

        while($row_resJdData = mysql_fetch_assoc($resJdData)){
            $row_receipt['gst_info']['jd_state_code']           = $row_resJdData['state_code'];
            $row_receipt['gst_info']['jd_state_name']           = $row_resJdData['state_name'];
            $row_receipt['gst_info']['jd_jd_city']              = $row_resJdData['jd_city'];
            $row_receipt['gst_info']['jd_cin_no']               = $row_resJdData['cin_no'];
            $row_receipt['gst_info']['jd_pan_no']               = $row_resJdData['pan_no'];
            $row_receipt['gst_info']['jd_jd_gst']               = $row_resJdData['jd_gst'];
            $row_receipt['gst_info']['jd_billing_address']      = $row_resJdData['jd_billing_address'];
            $row_receipt['gst_info']['jd_vat_tin_no']           = $row_resJdData['vat_tin_no'];
            $row_receipt['gst_info']['jd_sac_code']             = $row_resJdData['sac_code'];
            $row_receipt['gst_info']['jd_hsn_code']             = $row_resJdData['hsn_code'];
        }
        //JD GST variables ends here


        //GST variables starts here
        $sqlSelUserDet = "SELECT companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
        if($this->params['action']==4)
        	$sqlSelUserDet = "SELECT companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
        switch(strtolower($this->module)){
            case 'cs': 
            $resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_iro_conn); 
            $rowDet = mysql_fetch_assoc($resSelUserDet);
            break;
            case 'tme': 
            if(($this->mongo_tme == 1) && (stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false))
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "city,companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_tme_conn); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
            break;
            case 'me': 
            case 'jda':
            if(stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,state,area,pincode,full_address";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
            break;
            //case 'jda': $resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); break;
            default: die('Invalid Module'); break;
        }
        if(count($rowDet)>0){
                $contractName   =$rowDet['companyname'];
                $contactPerson  =$rowDet['contact_person'];
                $stateName      =$rowDet['state'];
                $areaName       =$rowDet['area'];
                $pincode        =$rowDet['pincode'];
                $fullAddress    =$rowDet['full_address'];
        }
        //write..
        
        $query_gst_details="SELECT * FROM tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->parentid."' ORDER BY doneon DESC LIMIT 1";
        $res_gst_details = parent::execQuery($query_gst_details, $this->db_payment);
        while($row_gst_details = mysql_fetch_assoc($res_gst_details)){
            $row_receipt['gst_info']['gstn_contract_name']   = $row_gst_details['gstn_contract_name'];
            $row_receipt['gst_info']['gstn_state_code']      = $row_gst_details['gstn_state_code'];
            $row_receipt['gst_info']['gstn_state_name']      = $row_gst_details['gstn_state_name'];
            $row_receipt['gst_info']['area']                 = $row_gst_details['area'];
            $row_receipt['gst_info']['gstn_address']         = $row_gst_details['gstn_address'];
            $row_receipt['gst_info']['gstn_pincode']         = $row_gst_details['gstn_pincode'];
            $row_receipt['gst_info']['gstn_no']              = $row_gst_details['gstn_no'];
        }
        $qryChecknewInvoiceName = "select invoice_businessname,invoice_cpersonname from payment_otherdetails where parentid='".$this->parentid."' and version ='".$this->version."'";
		$resChecknewInvoiceName = parent::execQuery($qryChecknewInvoiceName, $this->fin_con);
		if($resChecknewInvoiceName && mysql_num_rows($resChecknewInvoiceName)>0){
			$resChecknewInvoiceName = mysql_fetch_assoc($resChecknewInvoiceName);
			$odComapanyName = $rowChecknewInvoiceName['invoice_businessname'];
			$odContactPerson = $rowChecknewInvoiceName['invoice_cpersonname'];
		}else {
			$odComapanyName = '';
			$odContactPerson = '';
		}
		
        if($row_receipt['gst_info']['gstn_contract_name']==''){
			if($odComapanyName != ''){
				$row_receipt['gst_info']['gstn_contract_name']=$odComapanyName;
			}else{
				$row_receipt['gst_info']['gstn_contract_name']=$contractName;
			}
        }
        if($row_receipt['gst_info']['gstn_state_name']==''){
            $row_receipt['gst_info']['gstn_state_name']=$stateName;
        }
        if($row_receipt['gst_info']['area']==''){
            $row_receipt['gst_info']['area']=$areaName;
        }
        if($row_receipt['gst_info']['gstn_pincode']==''){
            $row_receipt['gst_info']['gstn_pincode']=$pincode;
        }
        if($row_receipt['gst_info']['gstn_address']==''){
            $row_receipt['gst_info']['gstn_address']=$fullAddress;
        }
        //GST variables ends here

        //Check Service Tax starts
        $chkSrvcTaxQry="SELECT * FROM db_finance.payment_discount_factor where parentid='".$this->parentid."' AND version='".$this->version."'";
        $chkSrvcTaxRes = parent::execQuery($chkSrvcTaxQry, $this->fin_con);
         $srviceTaxPerc=0;
        if($chkSrvcTaxRes && mysql_num_rows($chkSrvcTaxRes)>0){
	        while($rows = mysql_fetch_assoc($chkSrvcTaxRes)){
	            $srviceTaxPerc=$rows['service_tax_percent'];
	            //$srviceTaxPerc='0.15';
	        }
	      }
        
		if($this->params['action']==4)
        	$srviceTaxPerc=$receipt_details_arr['instrument_details']['instrument_total']['service_tax'];
        
        $row_receipt['gst_info']['service_tax_percent']=$srviceTaxPerc;
        //Check Service Tax ends

        $totAmt=$receipt_details_arr['instrument_details']['instrument_total']['totnetamt'];
        $totlForgst=str_replace(",", '', $totAmt);
        $totlForgst=(float)$totlForgst;
        $buyerState=strtolower($row_receipt['gst_info']['gstn_state_name']);
        $sellerState=strtolower($row_receipt['gst_info']['jd_state_name']);
        
        if($srviceTaxPerc=='0.18'){
            if($buyerState==$sellerState){
                $cgstPer=9;
                $sgstPer=9;
                $igstPer=0;
            }else if($buyerState!=$sellerState){
                $cgstPer=0;
                $sgstPer=0;
                $igstPer=18;
            }
            $cgst=($cgstPer/100)*$totlForgst;
            $sgst=($sgstPer/100)*$totlForgst;
            $igst=($igstPer/100)*$totlForgst;
            $tot_gst=$cgst+$sgst+$igst;
            $gross_total=round($totlForgst + $tot_gst);
        }else if ($srviceTaxPerc=='0.15'){
            $cgstPer='0';
            $sgstPer='0';
            $igstPer='0';
            $cgst='0';
            $sgst='0';
            $igst='0';
            $tot_gst='0';
        }

        //UPDATE table with GST variables starts
        $receipt_details_arr['gst_info']['receipt_no']          = $receiptNumber;
        $receipt_details_arr['gst_info']['invoice_no']          = $invoiceNumber;
        $receipt_details_arr['gst_info']['jd_state_code']       = $row_receipt['gst_info']['jd_state_code'];
        $receipt_details_arr['gst_info']['jd_state_name']       = $row_receipt['gst_info']['jd_state_name'];
        $receipt_details_arr['gst_info']['jd_jd_city']          = $row_receipt['gst_info']['jd_jd_city'];
        $receipt_details_arr['gst_info']['jd_cin_no']           = $row_receipt['gst_info']['jd_cin_no'];
        $receipt_details_arr['gst_info']['jd_pan_no']           = $row_receipt['gst_info']['jd_pan_no'];
        $receipt_details_arr['gst_info']['jd_jd_gst']           = $row_receipt['gst_info']['jd_jd_gst'];
        $receipt_details_arr['gst_info']['jd_billing_address']  = $row_receipt['gst_info']['jd_billing_address'];
        $receipt_details_arr['gst_info']['jd_vat_tin_no']       = $row_receipt['gst_info']['jd_vat_tin_no'];
        $receipt_details_arr['gst_info']['jd_sac_code']         = $row_receipt['gst_info']['jd_sac_code'];
        $receipt_details_arr['gst_info']['jd_hsn_code']         = $row_receipt['gst_info']['jd_hsn_code'];

        $receipt_details_arr['gst_info']['gstn_contract_name']  = $row_receipt['gst_info']['gstn_contract_name'];
        $receipt_details_arr['gst_info']['gstn_state_code']     = $row_receipt['gst_info']['gstn_state_code'];
        $receipt_details_arr['gst_info']['gstn_state_name']     = $row_receipt['gst_info']['gstn_state_name'];
        $receipt_details_arr['gst_info']['area']                = $row_receipt['gst_info']['area'];
        $receipt_details_arr['gst_info']['gstn_address']        = $row_receipt['gst_info']['gstn_address'];
        $receipt_details_arr['gst_info']['gstn_pincode']        = $row_receipt['gst_info']['gstn_pincode'];
        $receipt_details_arr['gst_info']['gstn_no']             = $row_receipt['gst_info']['gstn_no'];
        $receipt_details_arr['gst_info']['initial_amt']         = $totlForgst;
        $receipt_details_arr['gst_info']['cgst_perc']           = $cgstPer;
        $receipt_details_arr['gst_info']['cgst']                = $cgst;
        $receipt_details_arr['gst_info']['sgst_perc']           = $sgstPer;
        $receipt_details_arr['gst_info']['sgst']                = $sgst;
        $receipt_details_arr['gst_info']['igst_perc']           = $igstPer;
        $receipt_details_arr['gst_info']['igst']                = $igst;
        $receipt_details_arr['gst_info']['tot_gst_perc']        = $cgstPer+$sgstPer+$igstPer;
        $receipt_details_arr['gst_info']['tot_gst']             = $tot_gst;
        if($srviceTaxPerc=='0.18'){
            $receipt_details_arr['gst_info']['gross_total']         = $gross_total;
        }else if($srviceTaxPerc=='0.15'){
            $tot_sev_tax=(float)$srviceTaxPerc*$totlForgst;
            $receipt_details_arr['gst_info']['tot_service_tax']     = $tot_sev_tax;
            $receipt_details_arr['gst_info']['gross_total']         = $totlForgst+$tot_sev_tax;
        }

        $encReceipt_details_arr=json_encode($receipt_details_arr);

         $updateQry ="UPDATE tbl_invoice_payment_receipt_content SET receipt_details='".$this->mysql_real_escape_custom($encReceipt_details_arr)."' WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
        if($chk==''){
            $resultQry = parent::execQuery($updateQry, $this->fin_con);
        }
        //UPDATE table with GST variables ends
		return $row_receipt;
    }
*/
    function makeInvoiceRecieptId($stateName,$mod){
		
		
		
		$today				=	date('Y-m-d');
		$new_day			=	date("d",strtotime($today));
		//~ $toLowerState		=	strtolower($stateName);
		//~ $state				=	ucfirst($toLowerState);
		
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
            case 'andaman and aicobar': $stCode='AN'; break;

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
		
		
		$instrument_details_arr = $this->getInstrumentDetailsArrApproval($this->parentid,$this->version,$this->generate_inv,$this->invDate);
			
		$get_all_instrument_arr = json_decode($this->get_all_instrument($this->parentid,$this->version),true);
		
		/*foreach($get_all_instrument_arr['data']['invoiceAnnexure'] as $key_approve=>$val_approve)
		{
			
			if($val_approve['approved_date'][0] != '' )
			{
				$app_date = date('Y-m-d',strtotime($val_approve['approved_date'][0]));
				$chk_dt = 1;
			}else if($val_approve['dealClosed_date'][0] != '' )
			{
				$app_date = date('Y-m-d',strtotime($val_approve['dealClosed_date'][0]));
				$chk_dt = 2;
			}
			
		}*/
		
		foreach($get_all_instrument_arr['data']['invoiceAnnexure'] as $key_approve=>$val_approve)
		{
			//if($val_approve['approved_date'][0] != '' )
			//$app_date = date('Y-m-d',strtotime($val_approve['approved_date'][0]));
			//if($val_approve['dealClosed_date'][0] != '' )
			//$app_date = date('Y-m-d',strtotime($val_approve['dealClosed_date'][0]));
			
			if($val_approve['approved_date'][0] != '' )
			{
				
				if($this->version == $val_approve['version'][0])
				{
					$app_date = date('Y-m-d',strtotime($val_approve['approved_date'][0]));
					$deal_closed_date = date('Y-m-d',strtotime($val_approve['dealClosed_date'][0]));
					$instrumentid = $key_approve;
					$chk_dt = 1;
				}
			}else if($val_approve['dealClosed_date'][0] != '' )
			{
				
				if($this->version == $val_approve['version'][0])
				{
					$app_date = date('Y-m-d',strtotime($val_approve['dealClosed_date'][0]));
					$deal_closed_date = date('Y-m-d',strtotime($val_approve['dealClosed_date'][0]));
					$chk_dt = 2;
					$instrumentid = $key_approve;
				}
			}
			
		}
			
		$this->params['invDate'] = $app_date;
		
		
        
        
        if($mod === 'SN'){
			
			/*$checkExistQry_inv="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."'";
			$checkExistRes_inv = parent::execQuery($checkExistQry_inv, $this->db_payment);*/
			
			$checkExistQry_inv_dealclosed ="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$deal_closed_date."'";
			$checkExistRes_inv_dl = parent::execQuery($checkExistQry_inv_dealclosed, $this->db_payment);
			$chk_flag = 0;
			if(mysql_num_rows($checkExistRes_inv_dl) == 0){
				 $checkExistQry_inv_main="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."'";
				 //$checkExistRes_inv_main = parent::execQuery($checkExistQry_inv_main, $this->db_payment);
				 $chk_flag = 1;
			}
			if($chk_flag == 0)
			{
				$checkExistRes_inv = parent::execQuery($checkExistQry_inv_dealclosed, $this->db_payment);
			}
			else
			{
				$checkExistRes_inv = parent::execQuery($checkExistQry_inv_main, $this->db_payment);
			}
			
			
	        if(mysql_num_rows($checkExistRes_inv)>0){
				$chk_invoice_arr_inv = array();
	            $i=0;
					while($rowRcpt = parent::fetchData($checkExistRes_inv)){
							
						if($rowRcpt['inv_rcpt_code'] == '')
						{			
								$getRecieptDet	=	"SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='reciept' ORDER BY id DESC LIMIT 1"; //check not a new month
								$resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
								if(parent::numRows($resRecieptDet)>0){
									while($rowRcpt = parent::fetchData($resRecieptDet)){
										$recieptCountStart=$rowRcpt['increment_id'];
									}
								}else{
									$recieptCountStart=0;
								}
						
								$getInvoiceDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='invoice' ORDER BY id DESC LIMIT 1";
								$resInvoiceDet = parent::execQuery($getInvoiceDet, $this->db_payment);
								if(parent::numRows($resInvoiceDet)>0){
									while($rowInvc = parent::fetchData($resInvoiceDet)){
										$invoiceCountStart=$rowInvc['increment_id'];
									}
								}else{
									$invoiceCountStart=0;
								}
						}else
						{
							$chk_invoice_arr_inv[$i] = $rowRcpt['inv_rcpt_code'];
							$chk_invoice_arr_incrmntid[$i] = $rowRcpt['increment_id'];
							
							$chk_invoice_nonecs=substr($chk_invoice_arr_inv[0], 0, 2);
							$chk_rcpt_nonecs=substr($chk_invoice_arr_inv[1], 0, 2);
							
							
							if($stCode == $chk_invoice_nonecs)
							{
								$idArray['invoice']=$chk_invoice_arr_inv[0];
								$idArray['increment_id_inv']=$chk_invoice_arr_incrmntid[0];
								$invoiceCountStart=$chk_invoice_arr_incrmntid[0];
								
							}else
							{
								$getInvoiceDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='invoice' ORDER BY id DESC LIMIT 1";
								$resInvoiceDet = parent::execQuery($getInvoiceDet, $this->db_payment);
								if(parent::numRows($resInvoiceDet)>0){
									while($rowInvc = parent::fetchData($resInvoiceDet)){
										$invoiceCountStart=$rowInvc['increment_id'];
									}
								}else{
									$invoiceCountStart=0;
								}
							}
							if($stCode == $chk_rcpt_nonecs)
							{
								$idArray['receipt']=$chk_invoice_arr_inv[1];
								$idArray['increment_id_rcpt']=$chk_invoice_arr_incrmntid[1];
								$recieptCountStart=$chk_invoice_arr_incrmntid[1];
								
							}else
							{
								$getRecieptDet	=	"SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='reciept' ORDER BY id DESC LIMIT 1"; //check not a new month
								$resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
								if(parent::numRows($resRecieptDet)>0){
									while($rowRcpt = parent::fetchData($resRecieptDet)){
										$recieptCountStart=$rowRcpt['increment_id'];
									}
								}else{
									$recieptCountStart=0;
								}
							}
						}
						$i++;
					}
				}else
				{
					$getRecieptDet	=	"SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='reciept' ORDER BY id DESC LIMIT 1"; //check not a new month
					$resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
					if(parent::numRows($resRecieptDet)>0){
						while($rowRcpt = parent::fetchData($resRecieptDet)){
							$recieptCountStart=$rowRcpt['increment_id'];
						}
					}else{
						$recieptCountStart=0;
					}
			
					$getInvoiceDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record WHERE type='invoice' ORDER BY id DESC LIMIT 1";
					$resInvoiceDet = parent::execQuery($getInvoiceDet, $this->db_payment);
					if(parent::numRows($resInvoiceDet)>0){
						while($rowInvc = parent::fetchData($resInvoiceDet)){
							$invoiceCountStart=$rowInvc['increment_id'];
						}
					}else{
						$invoiceCountStart=0;
					}
				}
			
				/*if($new_day == 01){
					$recieptCountStart=0;
					$invoiceCountStart=0;
				}*/
        }
        
     	if($stCode != 'NAS'){
			if($mod == 'SN'){
				$month		       =	date("m",strtotime($app_date));
				$year		       =	date("y",strtotime($app_date));
				$incrInvNum=$invoiceCountStart + 1;
				$incrRcpNum=$recieptCountStart + 1;
				$invcNo = str_pad($incrInvNum, 8, 0, STR_PAD_LEFT);
				$rcptNo = str_pad($incrRcpNum, 8, 0, STR_PAD_LEFT);
				$invCode=$stCode.$month.$year.$mod.$invcNo;
				$rcptCode=$stCode.$month.$year.'RC'.$rcptNo;
				$idArray['invoice']=$invCode;
				$idArray['receipt']=$rcptCode;
				$now        = date('Y-m-d H:i:s');
			}
			
		}else{
			$idArray	=	array();
		}
	
		//~ $instrument_id			=  'CHQ22APR12A9B0Z4';
		$today					=	date('Y-m-d');
		
		
		if($mod === 'SN'){
			
			/*$checkExistQry="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."'";
			$checkExistRes = parent::execQuery($checkExistQry, $this->db_payment);*/
			
			$checkExistQry_main="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$deal_closed_date."'";
			$checkExistRes_main = parent::execQuery($checkExistQry_main, $this->db_payment);
			
			$chk_flag_main = 0;
			if(mysql_num_rows($checkExistRes_main) == 0){
				$checkExistQry_main_1="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."'";
				 $chk_flag_main = 1;
			}
			
		
			if($chk_flag_main == 0)
			{
				$checkExistRes = parent::execQuery($checkExistQry_main, $this->db_payment);
			}
			else
			{
				$checkExistRes = parent::execQuery($checkExistQry_main_1, $this->db_payment);
			}
			
			
	        if(mysql_num_rows($checkExistRes)>0){
	            $codeArray=array();
	            $idArray_auto = array();
				$chk_invoice_arr = array();
	            $i=0;
	            while($rowRes = mysql_fetch_assoc($checkExistRes)){
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
					
					
						if(($chk_invoice !== $chk_orig) && ($chk_invoice!='' && $chk_orig!=''))
						{
							
							$upd_invoice ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$invCode."',insert_date=NOW(),cron_flag=1,increment_id='".$incrInvNum."',instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='invoice' AND id='".$idArray_auto[0]."'";
							$inv_res = parent::execQuery($upd_invoice, $this->db_payment);
							$idArray['invoice']=$invCode;
							$idArray['receipt']=$codeArray[1];
						}
						
						if(($chk_rcpt !== $chk_orig_rcpt) && ($chk_orig_rcpt!='' && $chk_rcpt !=''))
						{
							
							$upd_rcpt ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$rcptCode."',insert_date=NOW(),cron_flag=1,increment_id='".$incrRcpNum."',instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='reciept' AND id='".$idArray_auto[1]."'";
							$inv_res = parent::execQuery($upd_rcpt, $this->db_payment);
							
							$idArray['invoice']=$codeArray[0];
							$idArray['receipt']=$rcptCode;
						}
						
						if(($chk_invoice === $chk_orig) && ($chk_rcpt === $chk_orig_rcpt))
						{
							
							 $upd_invoice_both ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$codeArray[0]."',insert_date=NOW(),cron_flag=1,instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='invoice' AND id='".$idArray_auto[0]."'";
							$inv_res = parent::execQuery($upd_invoice_both, $this->db_payment);
							
							 $upd_rcpt_both ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$codeArray[1]."',insert_date=NOW(),cron_flag=1,instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='reciept' AND id='".$idArray_auto[1]."'";
							$inv_res = parent::execQuery($upd_rcpt_both, $this->db_payment);
							
							$idArray['invoice']=$codeArray[0];
							$idArray['receipt']=$codeArray[1];
						}
						
				}else
				{
					
					$upd_invoice_blank ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$invCode."',insert_date=NOW(),cron_flag=1,increment_id='".$incrInvNum."',instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='invoice' AND id='".$idArray_auto[0]."'";
						$inv_res = parent::execQuery($upd_invoice_blank, $this->db_payment);
						
					 $upd_rcpt_blank ="UPDATE tbl_reciept_invoice_record SET inv_rcpt_code= '".$rcptCode."',insert_date=NOW(),cron_flag=1,increment_id='".$incrRcpNum."',instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."' WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND date(approval_date) = '".$app_date."' AND type='reciept' AND id='".$idArray_auto[1]."'";
						$inv_res = parent::execQuery($upd_rcpt_blank, $this->db_payment);
						
					$idArray['invoice']=$invCode;
					$idArray['receipt']=$rcptCode;	
							
				}
	        }else{
				$checkExistQry="SELECT * FROM tbl_reciept_invoice_record WHERE parentid='".$this->params['parentid']."' AND version='".$this->params['version']."' AND (approval_date IS NULL OR DATE(approval_date) = '0000-00-00' OR approval_date ='')";
				$checkExistRes = parent::execQuery($checkExistQry, $this->db_payment);
				if(mysql_num_rows($checkExistRes)>0){
		            $codeArray=array();
		            $codeArray_id =array();
		            $i=0;
		            while($rowRes = mysql_fetch_assoc($checkExistRes)){
		                $codeArray[$i]=$rowRes['inv_rcpt_code'];
		                $codeArray_id[$i]=$rowRes['id'];
		                $i++;
		            }
		            
		            $insInvoiceQry_inv="UPDATE tbl_reciept_invoice_record SET approval_date='".$app_date."',insert_date=NOW(),cron_flag=1 WHERE type='invoice' AND parentid='".$this->params['parentid']."' AND id='".	$codeArray_id[0]."'";
					$insInvoiceDet = parent::execQuery($insInvoiceQry_inv, $this->db_payment);
	            
	            
					$insInvoiceQry_rcpt="UPDATE tbl_reciept_invoice_record SET approval_date='".$app_date."',insert_date=NOW(),cron_flag=1 WHERE type='reciept' AND parentid='".$this->params['parentid']."' AND id='".$codeArray_id[1]."'";
					$insInvoiceDet = parent::execQuery($insInvoiceQry_rcpt, $this->db_payment);
		            
		            
		            $idArray['invoice']=$codeArray[0];
		            $idArray['receipt']=$codeArray[1];
				}else{
					//ecs_flag change #################
						$insInvoiceQry="INSERT INTO tbl_reciept_invoice_record SET inv_rcpt_code='".$invCode."', type='invoice', increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$now."',approval_date='".$app_date."',insert_date=NOW(),cron_flag=1,instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."'";
						$insInvoiceDet = parent::execQuery($insInvoiceQry, $this->db_payment);
			
			            $insReceiptQry="INSERT INTO tbl_reciept_invoice_record SET inv_rcpt_code='".$rcptCode."', type='reciept', increment_id='".$incrRcpNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$now."',approval_date='".$app_date."',insert_date=NOW(),cron_flag=1,instrumentId='".$instrumentid."',dealclosed_date = '".$deal_closed_date."'";
			            $rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->db_payment);
					//~ }
				}
			}
        }
        
        return $idArray;
	}
	
	
	function makeEcsRecieptId($stateName,$mod,$month,$Compmonth,$empCity,$billingcity,$contract_city,$usrCode,$jd_state_name,$ecs_date,$ecs_date_upd,$billnumber,$completeMonth,$ecs_date_datetime){
		
		
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
            case 'andaman and aicobar': $stCode='AN'; break;

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
		 
        if($mod === 'SE'){
		if($completeMonth >= '2017-07-01' && $completeMonth <= '2018-01-31')
		{
			
			$getRecieptDet	=	"SELECT invoiceno,increment_id,date_time FROM db_payment.tbl_reciept_invoice_record_new_temp WHERE parentid='".$this->params['parentid']."' $ecs_date  ORDER BY id DESC LIMIT 1"; 
			$resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
			if(parent::numRows($resRecieptDet)==0)
			{
				$getRecieptDet	=	"SELECT invoiceno,increment_id,date_time FROM db_payment.tbl_reciept_invoice_record_new_temp WHERE parentid='".$this->params['parentid']."' $ecs_date_datetime  ORDER BY id DESC LIMIT 1"; 
				$resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
			}
		}else
		{	
			if($completeMonth>= '2018-05-21')
			{	
			  $getRecieptDet	=	"SELECT invoiceno,increment_id,date_time FROM db_invoice.tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date AND billnumber ='".$billnumber."' ORDER BY id DESC LIMIT 1"; //check not a new month
			  $resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->fin_con_slave);
			  }else
			  {
				  $getRecieptDet	=	"SELECT invoiceno,increment_id,date_time FROM tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date AND billnumber ='".$billnumber."' ORDER BY id DESC LIMIT 1"; //check not a new month
				  $resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
				  if(parent::numRows($resRecieptDet)==0)
				  {
					  $getRecieptDet	=	"SELECT invoiceno,increment_id,date_time FROM tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date_datetime ORDER BY id DESC LIMIT 1"; //check not a new month
					 $resRecieptDet 	= 	parent::execQuery($getRecieptDet, $this->db_payment);
				  }
			  }
		}
	        if(parent::numRows($resRecieptDet)>0){
	            while($rowRcpt = parent::fetchData($resRecieptDet)){
					if($rowRcpt['invoiceno'] == '')
					{
						if($completeMonth>= '2018-05-21')
						{
							$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
							$resRecieptDet = parent::execQuery($getRecieptDet, $this->fin_con_slave);
						}else
						{
							$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
							$resRecieptDet = parent::execQuery($getRecieptDet, $this->db_payment);
						}
						$recipt_num_rows = mysql_num_rows($resRecieptDet);
						if($recipt_num_rows>0){
							$rowRcpt = mysql_fetch_assoc($resRecieptDet);
								$ecsCountStart=$rowRcpt['increment_id'];
								$checkedmonth=$month;                              
						}else{
								$ecsCountStart=0;
						}
					}else
					{
						 $chk_invoice=substr($rowRcpt['invoiceno'], 0, 2);
						if($completeMonth >= '2017-07-01' && $completeMonth <= '2017-11-31')
						{
								$idArray['invoice']=$rowRcpt['invoiceno'];
								$idArray['increment_id']=$rowRcpt['increment_id'];
								$checkedmonth=date("m", strtotime($rowRcpt['date_time']));
						}else
						{ 
							if($stCode == $chk_invoice)
							{
								$idArray['invoice']=$rowRcpt['invoiceno'];
								$idArray['increment_id']=$rowRcpt['increment_id'];
								$checkedmonth=date("m", strtotime($rowRcpt['date_time']));           
							}else
							{
								if($completeMonth>= '2018-05-21')
								{
									$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
									$resRecieptDet = parent::execQuery($getRecieptDet, $this->fin_con_slave);
								}else
								{
									$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
									$resRecieptDet = parent::execQuery($getRecieptDet, $this->db_payment);
								}
								$recipt_num_rows = mysql_num_rows($resRecieptDet);
								if($recipt_num_rows>0){
									$rowRcpt = mysql_fetch_assoc($resRecieptDet);
										$ecsCountStart=$rowRcpt['increment_id'];
										$checkedmonth=$month;                              
								}else{
										$ecsCountStart=0;
								}
							}
						}
					}
	            }
	        }else{
	          
				if($completeMonth>= '2018-05-21')
				{
					$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM db_invoice.tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
					$resRecieptDet = parent::execQuery($getRecieptDet, $this->fin_con_slave);
				}else
				{
					$getRecieptDet="SELECT MAX(increment_id) AS increment_id FROM tbl_reciept_invoice_record_new ORDER BY id DESC LIMIT 1"; // For Both
					$resRecieptDet = parent::execQuery($getRecieptDet, $this->db_payment);
				}
				 $recipt_num_rows = mysql_num_rows($resRecieptDet);
				if($recipt_num_rows>0){
						$rowRcpt = mysql_fetch_assoc($resRecieptDet);
						$ecsCountStart=$rowRcpt['increment_id'];
						$checkedmonth=$month;                              
					
				}else{
						$ecsCountStart=0;
				}
	        }
		}
		
		
		if($stCode != 'NAS'){
			if($mod == 'SE'){
				
				$month		       =	$checkedmonth;
				$year		       =	date("y");
				$incrInvNum	       =	$ecsCountStart + 1;
				$invcNo 		   = 	str_pad($incrInvNum, 8, 0, STR_PAD_LEFT);
				$invCode		   =	$stCode.$month.$year.$mod.$invcNo;
				if($idArray['invoice']!='')
				{
					$invCode = $idArray['invoice'];
					$incrInvNum=	$idArray['increment_id'];
				}
				else
				{
					$invCode = $invCode;
					$incrInvNum=	$incrInvNum;
				}
					
			}
	        $now        = date('Y-m-d H:i:s');
		}else{
			$idArray	=	array();
		}
		$today					=	date('Y-m-d');
		
		if($mod == 'SE'){
			$instrument_details_arr = $this->getInstrumentDetailsArr($this->parentid,$this->version);
			$deal_closed_date				=	$instrument_details_arr['all_instruments'][0]['date'];
			if($completeMonth >= '2017-07-01' && $completeMonth <= '2018-01-31')
			{
				$selectEcsMOnth	=	"SELECT date_time,id FROM db_payment.tbl_reciept_invoice_record_new_temp WHERE parentid='".$this->params['parentid']."' $ecs_date ";
				$resEcsDet 		= 	parent::execQuery($selectEcsMOnth, $this->db_payment);
				if(parent::numRows($resEcsDet)==0)
				{
					 $selectEcsMOnth	=	"SELECT date_time,id FROM db_payment.tbl_reciept_invoice_record_new_temp WHERE parentid='".$this->params['parentid']."' $ecs_date_datetime ";
					$resEcsDet 		= 	parent::execQuery($selectEcsMOnth, $this->db_payment);
				}
			}else
			{
				if($completeMonth>= '2018-05-21')
				{
					$selectEcsMOnth	=	"SELECT date_time,id FROM db_invoice.tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date AND billnumber ='".$billnumber."'";
					$resEcsDet 		= 	parent::execQuery($selectEcsMOnth, $this->fin_con_slave);
				}else
				{
					$selectEcsMOnth	=	"SELECT date_time,id FROM tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date AND billnumber ='".$billnumber."'";
					$resEcsDet 		= 	parent::execQuery($selectEcsMOnth, $this->db_payment);
					if(parent::numRows($resEcsDet)==0)
					{
						$selectEcsMOnth	=	"SELECT date_time,id FROM tbl_reciept_invoice_record_new WHERE parentid='".$this->params['parentid']."' $ecs_date_datetime"; //check not a new month
						$resEcsDet 	= 	parent::execQuery($selectEcsMOnth, $this->db_payment);
					}
				}
			}
	        $rowRcpt 		= 	mysql_fetch_assoc($resEcsDet);
	        $rownumRcpt 	= 	mysql_num_rows($resEcsDet);
	        $ecsDateTime	=	$rowRcpt['date_time'];
	        $id	=	$rowRcpt['id'];
	        $ecsCount		=	$rownumRcpt;
	        //~ if($stCode != 'NAS'){
		        if($ecsCount > 0){
					if($stCode != 'NAS')
					{
						if($completeMonth>= '2018-05-21')
						{
							 $updateEcsQry	=	"UPDATE db_invoice.tbl_reciept_invoice_record_new SET inv_rcpt_code='".$invCode."',invoiceno='".$invCode."', type='ecsinvoice', increment_id='".$incrInvNum."',version='".$this->params['version']."', date_time='".$ecsDateTime."',ecs_flag=1,approval_date='".$Compmonth."',state='".$stateName."',city='".$contract_city."',insert_date=NOW(),billing_city='".$billingcity."',contract_city='".$contract_city."',jd_city_name='".$empCity."', invoice_create='1',clearance_date='".$Compmonth."',billnumber='".$billnumber."' WHERE parentid='".$this->params['parentid']."' AND id='".$id."'  $ecs_date";
							$upEcsDet 	= 	parent::execQuery($updateEcsQry, $this->fin_con_slave);
						}else
						{
							$updateEcsQry	=	"UPDATE tbl_reciept_invoice_record_new SET inv_rcpt_code='".$invCode."',invoiceno='".$invCode."', type='ecsinvoice', increment_id='".$incrInvNum."',version='".$this->params['version']."', date_time='".$ecsDateTime."',ecs_flag=1,approval_date='".$Compmonth."',state='".$stateName."',city='".$contract_city."',insert_date=NOW(),billing_city='".$billingcity."',contract_city='".$contract_city."',jd_city_name='".$empCity."', invoice_create='1',clearance_date='".$Compmonth."',billnumber='".$billnumber."' WHERE parentid='".$this->params['parentid']."' AND id='".$id."'";
							$upEcsDet 	= 	parent::execQuery($updateEcsQry, $this->db_payment);
						}
						
						$upd_bills		=	"UPDATE db_finance.invoice_ecs_bills_fin SET pickup_flag = 1 WHERE parentid='".$this->params['parentid']."' $ecs_date";
						$upEcsbills 	= 	parent::execQuery($upd_bills, $this->fin_con);
						
						
						$idArray['invoice']=$invCode;
						
						
						
					}
					
				}else{
					if($stCode != 'NAS')
					{
						if($completeMonth>= '2018-05-21')
						{
							$insReceiptQry="INSERT INTO db_invoice.tbl_reciept_invoice_record_new SET inv_rcpt_code='".$invCode."', type='ecsinvoice', increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$Compmonth."',invoiceno='".$invCode."',ecs_flag=1,approval_date='".$Compmonth."',clearance_date='".$Compmonth."',state='".$stateName."',city='".$contract_city."',invoice_create='1',billing_city='".$billingcity."',contract_city='".$contract_city."',jd_city_name='".$empCity."',insert_date=NOW(),dc_by='".$usrCode."',jd_state_name='".$jd_state_name."',billnumber='".$billnumber."'";
							$rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->fin_con_slave);
						}else
						{
							$insReceiptQry="INSERT INTO tbl_reciept_invoice_record_new SET inv_rcpt_code='".$invCode."', type='ecsinvoice', increment_id='".$incrInvNum."', parentid='".$this->params['parentid']."', version='".$this->params['version']."', date_time='".$Compmonth."',invoiceno='".$invCode."',ecs_flag=1,approval_date='".$Compmonth."',clearance_date='".$Compmonth."',state='".$stateName."',city='".$contract_city."',invoice_create='1',billing_city='".$billingcity."',contract_city='".$contract_city."',jd_city_name='".$empCity."',insert_date=NOW(),dc_by='".$usrCode."',jd_state_name='".$jd_state_name."',billnumber='".$billnumber."'";
							$rcptInvoiceDet = parent::execQuery($insReceiptQry, $this->db_payment);
						}
						
						$upd_billsns	=	"UPDATE db_finance.invoice_ecs_bills_fin SET pickup_flag = 1 WHERE parentid='".$this->params['parentid']."' $ecs_date";
						$upEcsbillsns 	= 	parent::execQuery($upd_billsns, $this->fin_con);
						
						
						
						$idArray['invoice']=$invCode;
					}
				}
			
		}
		
        
        return $idArray;
	}
	
	function get_invoice_versions()
	{
		$sql_versions = "SELECT version , entry_date  FROM tbl_invoice_payment_receipt_content WHERE parentid='".$this->parentid."'";
		$res_versions = parent::execQuery($sql_versions, $this->fin_con);
		
		if($res_versions && mysql_num_rows($res_versions)>0)
		{
			
			while($row_versions = mysql_fetch_assoc($res_versions)){
				$version_array[$row_versions['entry_date']] = $row_versions['version'];
			}
			return $version_array;
		}else
		{
			
			return $error;
		}
	}
	
	function get_ecs_invoice_content(){
		
		
		
		$sqlSel = "SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name,state,full_address FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."' ";
		
		$omni_combo_sql="SELECT * 
						 FROM  dependant_campaign_details_temp 
						 WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		

		$payment_type_sql="SELECT * 
						 FROM  tbl_payment_type 
						 WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
						 
		$receipt_details_arr=array();
		switch(strtolower($this->module))
		{
			case 'cs':
			$resSel = parent::execQuery($sqlSel, $this->local_iro_conn);
			break;
			case 'tme':
			$resSel 		= parent::execQuery($sqlSel, $this->local_tme_conn);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->local_tme_conn);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			break;
			case 'me':
			$resSel			= parent::execQuery($sqlSel, $this->idc_con);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$payment_type_res = parent::execQuery($payment_type_sql, $this->fin_con);
			break;
			case 'jda':
			$resSel			= parent::execQuery($sqlSel, $this->idc_con);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			break;
			default:
			die('Invalid Module88');
			break;
		}
		
		$land_str_area = $rowSel['building_name'].",".$rowSel['landmark'].",".$rowSel['street'].",".$rowSel['area'];
		$land_str_area = rtrim(ltrim($land_str_area,","),",");
			
		if($resSel && mysql_num_rows($resSel)>0)
		{
			$rowSel = mysql_fetch_assoc($resSel);
			
			$ecs_gen_details['comp_gen_info']['Company Name']	 	=	trim($rowSel['companyname']);
			$ecs_gen_details['comp_gen_info']['Customer Name']  	= 	$rowSel['contact_person'];
		    $ecs_gen_details['comp_gen_info']['Billing Parentid']	=	$this->parentid;
		    $ecs_gen_details['comp_gen_info']['Billing Address'] 	=	$land_str_area;
		    $ecs_gen_details['comp_gen_info']['City']	     		=	$rowSel['city'].'-'.$rowSel['pincode'];
		    $ecs_gen_details['comp_gen_info']['Email']	     		=	$rowSel['email'];
		    $ecs_gen_details['comp_gen_info']['Contact No']	    	=	$rowSel['mobile'];
		    $ecs_gen_details['comp_gen_info']['Pincode']	     	=	$rowSel['pincode'];
		    $ecs_gen_details['comp_gen_info']['full_address']	    =	$rowSel['full_address'];
			$ecs_gen_details['comp_gen_info']['area']	    		=	$rowSel['area'];
			$ecs_gen_details['comp_gen_info']['contract_city']	    =	$rowSel['city'];
			$ecs_gen_details['comp_gen_info']['contract_state']	    =	$rowSel['state'];
		}
		
		$row_receipt['receipt_details']	=	$ecs_gen_details;
		
		 $query_gst_details="SELECT * FROM tbl_gstn_emailer_contract_data_latest WHERE parentid='".$this->parentid."' ORDER BY doneon DESC LIMIT 1";
        $res_gst_details = parent::execQuery($query_gst_details, $this->db_payment);
        while($row_gst_details = mysql_fetch_assoc($res_gst_details)){
            $receipt_details_arr['gst_info']['gstn_contract_name']   = $row_gst_details['gstn_contract_name'];
            $receipt_details_arr['gst_info']['gstn_state_code']      = $row_gst_details['gstn_state_code'];
            $receipt_details_arr['gst_info']['gstn_state_name']      = $row_gst_details['gstn_state_name'];
            $receipt_details_arr['gst_info']['area']                 = $row_gst_details['area'];
            $receipt_details_arr['gst_info']['gstn_address']         = $row_gst_details['gstn_address'];
            $receipt_details_arr['gst_info']['gstn_pincode']         = $row_gst_details['gstn_pincode'];
            $receipt_details_arr['gst_info']['gstn_no']              = $row_gst_details['gstn_no'];
            $receipt_details_arr['gst_info']['server_city']          = $row_gst_details['server_city'];
        }
	
		/*$completeYr = date('Y',strtotime($this->completeMonth));
		
		if($this->ecsMonth == '07' && $completeYr== '2017'){
			$ecs_date	=	"and clearance_date>='2017-07-01 00:00:00' and clearance_date<='2017-07-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-07-01 00:00:00' and date_time<='2017-07-31 23:59:59'";
		}else if($this->ecsMonth == '08' && $completeYr== '2017'){
			$ecs_date	=	"and clearance_date>='2017-08-01 00:00:00' and clearance_date<='2017-08-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-08-01 00:00:00' and date_time<='2017-08-31 23:59:59'";
		}else if($this->ecsMonth == '09' && $completeYr== '2017'){
			$ecs_date	=	"and clearance_date>='2017-09-01 00:00:00' and clearance_date<='2017-09-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-09-01 00:00:00' and date_time<='2017-09-31 23:59:59'";
		}else if($this->ecsMonth == '10' && $completeYr== '2017'){
			$ecs_date	=	"and clearance_date>='2017-10-01 00:00:00' and clearance_date<='2017-10-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-10-01 00:00:00' and date_time<='2017-10-31 23:59:59'";
		}else if($this->ecsMonth == '11' && $completeYr== '2017'){
			$ecs_date	=	"and clearance_date>='2017-11-01 00:00:00' and clearance_date<='2017-11-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-11-01 00:00:00' and date_time<='2017-11-31 23:59:59'";
		}else if($this->completeMonth == '2017-12-01'){
			$ecs_date	=	"and clearance_date>='2017-12-01 00:00:00' and clearance_date<='2017-12-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-12-01 00:00:00' and date_time<='2017-12-20 23:59:59'";
		}else if($this->completeMonth == '2017-12-21'){
			$ecs_date	=	"and clearance_date>='2017-12-21 00:00:00' and clearance_date<='2017-12-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2017-12-21 00:00:00' and date_time<='2017-12-31 23:59:59'";
		}else if($this->completeMonth == '2018-01-01'){
			$ecs_date	=	"and clearance_date>='2018-01-01 00:00:00' and clearance_date<='2018-01-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-01-01 00:00:00' and date_time<='2018-01-10 23:59:59'";
		}else if($this->completeMonth == '2018-01-11'){
			$ecs_date	=	"and clearance_date>='2018-01-11 00:00:00' and clearance_date<='2018-01-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-01-11 00:00:00' and date_time<='2018-01-31 23:59:59'";
		}else if($this->completeMonth == '2018-02-01'){
			$ecs_date	=	"and clearance_date>='2018-02-01 00:00:00' and clearance_date<='2018-02-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-02-01 00:00:00' and date_time<='2018-02-10 23:59:59'";
		}else if($this->completeMonth == '2018-02-11'){
			$ecs_date	=	"and clearance_date>='2018-02-11 00:00:00' and clearance_date<='2018-02-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-02-11 00:00:00' and date_time<='2018-02-20 23:59:59'";
		}else if($this->completeMonth == '2018-02-21'){
			$ecs_date	=	"and clearance_date>='2018-02-21 00:00:00' and clearance_date<='2018-02-28 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-02-21 00:00:00' and date_time<='2018-02-28 23:59:59'";
		}else if($this->completeMonth == '2018-03-01'){
			$ecs_date	=	"and clearance_date>='2018-03-01 00:00:00' and clearance_date<='2018-03-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-03-01 00:00:00' and date_time<='2018-03-10 23:59:59'";
		}else if($this->completeMonth == '2018-03-11'){
			$ecs_date	=	"and clearance_date>='2018-03-11 00:00:00' and clearance_date<='2018-03-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-03-11 00:00:00' and date_time<='2018-03-20 23:59:59'";
		}else if($this->completeMonth == '2018-03-21'){
			$ecs_date	=	"and clearance_date>='2018-03-21 00:00:00' and clearance_date<='2018-03-29 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-03-21 00:00:00' and date_time<='2018-03-29 23:59:59'";
		}else if($this->completeMonth == '2018-03-30'){
			$ecs_date	=	"and clearance_date>='2018-03-30 00:00:00' and clearance_date<='2018-03-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-03-30 00:00:00' and date_time<='2018-03-31 23:59:59'";
		}else if($this->completeMonth == '2018-04-01'){
			$ecs_date	=	"and clearance_date>='2018-04-01 00:00:00' and clearance_date<='2018-04-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-04-01 00:00:00' and date_time<='2018-04-10 23:59:59'";
		}else if($this->completeMonth == '2018-04-11'){
			$ecs_date	=	"and clearance_date>='2018-04-11 00:00:00' and clearance_date<='2018-04-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-04-11 00:00:00' and date_time<='2018-04-20 23:59:59'";
		}else if($this->completeMonth == '2018-04-21'){
			$ecs_date	=	"and clearance_date>='2018-04-21 00:00:00' and clearance_date<='2018-04-30 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-04-21 00:00:00' and date_time<='2018-04-30 23:59:59'";
		}else if($this->completeMonth == '2018-05-01'){
			$ecs_date	=	"and clearance_date>='2018-05-01 00:00:00' and clearance_date<='2018-05-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-05-01 00:00:00' and date_time<='2018-05-10 23:59:59'";
		}else if($this->completeMonth == '2018-05-11'){
			$ecs_date	=	"and clearance_date>='2018-05-11 00:00:00' and clearance_date<='2018-05-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-05-11 00:00:00' and date_time<='2018-05-20 23:59:59'";
		}else if($this->completeMonth == '2018-05-21'){
			$ecs_date	=	"and clearance_date>='2018-05-21 00:00:00' and clearance_date<='2018-05-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-05-21 00:00:00' and date_time<='2018-05-31 23:59:59'";
		}else if($this->completeMonth == '2018-06-01'){
			$ecs_date	=	"and clearance_date>='2018-06-01 00:00:00' and clearance_date<='2018-06-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-06-01 00:00:00' and date_time<='2018-06-10 23:59:59'";
		}else if($this->completeMonth == '2018-06-11'){
			$ecs_date	=	"and clearance_date>='2018-06-11 00:00:00' and clearance_date<='2018-06-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-06-11 00:00:00' and date_time<='2018-06-20 23:59:59'";
		}else if($this->completeMonth == '2018-06-21'){
			$ecs_date	=	"and clearance_date>='2018-06-21 00:00:00' and clearance_date<='2018-06-30 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-06-21 00:00:00' and date_time<='2018-06-30 23:59:59'";
		}else if($this->completeMonth == '2018-07-01'){
			$ecs_date	=	"and clearance_date>='2018-07-01 00:00:00' and clearance_date<='2018-07-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-07-01 00:00:00' and date_time<='2018-07-10 23:59:59'";
		}else if($this->completeMonth == '2018-07-11'){
			$ecs_date	=	"and clearance_date>='2018-07-11 00:00:00' and clearance_date<='2018-07-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-07-11 00:00:00' and date_time<='2018-07-20 23:59:59'";
		}else if($this->completeMonth == '2018-07-21'){
			$ecs_date	=	"and clearance_date>='2018-07-21 00:00:00' and clearance_date<='2018-07-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-07-21 00:00:00' and date_time<='2018-07-31 23:59:59'";
		}else if($this->completeMonth == '2018-08-01'){
			$ecs_date	=	"and clearance_date>='2018-08-01 00:00:00' and clearance_date<='2018-08-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-08-01 00:00:00' and date_time<='2018-08-10 23:59:59'";
		}else if($this->completeMonth == '2018-08-11'){
			$ecs_date	=	"and clearance_date>='2018-08-11 00:00:00' and clearance_date<='2018-08-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-08-11 00:00:00' and date_time<='2018-08-20 23:59:59'";
		}else if($this->completeMonth == '2018-08-21'){
			$ecs_date	=	"and clearance_date>='2018-08-21 00:00:00' and clearance_date<='2018-08-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-08-21 00:00:00' and date_time<='2018-08-31 23:59:59'";
		}else if($this->completeMonth == '2018-09-01'){
			$ecs_date	=	"and clearance_date>='2018-09-01 00:00:00' and clearance_date<='2018-09-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-09-01 00:00:00' and date_time<='2018-09-10 23:59:59'";
		}else if($this->completeMonth == '2018-09-11'){
			$ecs_date	=	"and clearance_date>='2018-09-11 00:00:00' and clearance_date<='2018-09-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-09-11 00:00:00' and date_time<='2018-09-20 23:59:59'";
		}else if($this->completeMonth == '2018-09-21'){
			$ecs_date	=	"and clearance_date>='2018-09-21 00:00:00' and clearance_date<='2018-09-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-09-21 00:00:00' and date_time<='2018-09-31 23:59:59'";
		}else if($this->completeMonth == '2018-10-01'){
			$ecs_date	=	"and clearance_date>='2018-10-01 00:00:00' and clearance_date<='2018-10-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-10-01 00:00:00' and date_time<='2018-10-10 23:59:59'";
		}else if($this->completeMonth == '2018-10-11'){
			$ecs_date	=	"and clearance_date>='2018-10-11 00:00:00' and clearance_date<='2018-10-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-10-11 00:00:00' and date_time<='2018-10-20 23:59:59'";
		}else if($this->completeMonth == '2018-10-21'){
			$ecs_date	=	"and clearance_date>='2018-10-21 00:00:00' and clearance_date<='2018-10-31 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-10-21 00:00:00' and date_time<='2018-10-31 23:59:59'";
		}else if($this->completeMonth == '2018-11-01'){
			$ecs_date	=	"and clearance_date>='2018-11-01 00:00:00' and clearance_date<='2018-11-10 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-11-01 00:00:00' and date_time<='2018-11-10 23:59:59'";
		}else if($this->completeMonth == '2018-11-11'){
			$ecs_date	=	"and clearance_date>='2018-11-10 00:00:00' and clearance_date<='2018-11-20 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-11-10 00:00:00' and date_time<='2018-11-20 23:59:59'";
		}else if($this->completeMonth == '2018-11-21'){
			$ecs_date	=	"and clearance_date>='2018-11-21 00:00:00' and clearance_date<='2018-11-30 23:59:59'";
			$ecs_date_upd	=	"and date_time>='2018-11-21 00:00:00' and date_time<='2018-11-30 23:59:59'";
		} 
		*/
			$completeYr = date('Y',strtotime($this->completeMonth));
			$completemnt = date('m',strtotime($this->completeMonth));
			$completeday = explode('-',$this->completeMonth);
		
		
		switch (strtolower($this->data_city)) {
		case 'ahmedabad':
			$citycode = '56';
			$version_ct = 'ahmedabad';
			break;
		case 'bangalore':
			$citycode = '26';
			$version_ct = 'bangalore';
			break;
		case 'chennai':
			$citycode = '32';
			$version_ct = 'chennai';
			break;
		case 'delhi':
			$citycode = '8';
			$version_ct = 'delhi';
			break;
		case 'hyderabad':
			$citycode = '50';
			$version_ct = 'hyderabad';
			break;
		case 'kolkata':
			$citycode = '16';
			$version_ct = 'kolkata';
			break;
		case 'mumbai':
			$citycode = '0';
			$version_ct = 'mumbai';
			break;
		case 'pune':
			$citycode = '40';
			$version_ct = 'pune';
			break;
		default :
			$citycode = '17';
			$version_ct = 'Remote_city';
			break;	
	}
	
	if($this->completeMonth >= '2017-07-01' && $this->completeMonth <= '2017-11-31')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-01';
		$ToDt = $completeYr.'-'.$completemnt.'-31';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($this->completeMonth == '2017-12-01' && $this->completeMonth <= '2017-12-20')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-01';
		$ToDt = $completeYr.'-'.$completemnt.'-20';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($this->completeMonth >= '2017-12-21' && $this->completeMonth <= '2017-12-31')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-21';
		$ToDt = $completeYr.'-'.$completemnt.'-31';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($this->completeMonth == '2018-01-01' && $this->completeMonth <= '2018-12-10')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-01';
		$ToDt = $completeYr.'-'.$completemnt.'-10';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($this->completeMonth >= '2018-01-11' && $this->completeMonth <= '2018-01-31')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-11';
		$ToDt = $completeYr.'-'.$completemnt.'-31';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($completeday[2] >= '01' && $completeday[2] <= '10')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-01';
		$ToDt = $completeYr.'-'.$completemnt.'-10';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($completeday[2] >= '11' && $completeday[2] <= '20')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-11';
		$ToDt = $completeYr.'-'.$completemnt.'-20';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	if($completeday[2] >= '21' && $completeday[2] <= '31')
	{
		$FromDt = $completeYr.'-'.$completemnt.'-21';
		$ToDt = $completeYr.'-'.$completemnt.'-31';
		$ecs_date	=	"and clearance_date>='".$FromDt." 00:00:00' and clearance_date<='".$ToDt." 23:59:59'";
		$ecs_date_datetime	=	"and date_time>='".$FromDt." 00:00:00' and date_time<='".$ToDt." 23:59:59'";
	}
	
	if($this->completeMonth >= '2017-07-01' && $this->completeMonth <= '2017-11-31')
	{
		$count = 3;
	}else
	{
	
		$query_ecs_details="SELECT * FROM online_regis.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date ORDER BY clearance_date DESC";
		$res_ecs_details = parent::execQuery($query_ecs_details, $this->idc_con);
		if(mysql_num_rows($res_ecs_details)	> 0)
		{
			
			$count = 1;
				$drop_main_details		="DROP TABLE IF EXISTS test.invoice_ecs_bills";
				$con_main_drop			=	parent::execQuery($drop_main_details, $this->idc_con);

				$create_main_details		="CREATE table test.invoice_ecs_bills LIKE online_regis.invoice_ecs_bills";
				$con_main					=	parent::execQuery($create_main_details, $this->idc_con);


				 $query_ecs_details="SELECT parentid,billing_city,VERSION AS version,clearance_date,billnumber,credit_note,dc_by FROM online_regis.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date GROUP BY parentid";
				$con_ecs		=	parent::execQuery($query_ecs_details, $this->idc_con);
				$numRows 		= 	mysql_num_rows($con_ecs); 
				if($numRows > 0){
					while($num_row = mysql_fetch_assoc($con_ecs)){
						
						$num_row_new[]		=	$num_row['parentid'];
					}
					
				}

			
				foreach($num_row_new as $k=>$v)
				{
					 $query_ecs_details_city_chk="SELECT * FROM online_regis.invoice_ecs_bills WHERE parentid='".$v."' $ecs_date ORDER BY clearance_date DESC limit 1";
					$res_ecs_details_chk = parent::execQuery($query_ecs_details_city_chk, $this->idc_con);
					$row_ecs_details_chk = mysql_fetch_assoc($res_ecs_details_chk);
					
					
					
					if(strtolower($row_ecs_details_chk['billing_city']) == strtolower($version_ct))
					{
						 $query_ecs_details="INSERT INTO test.invoice_ecs_bills select * from online_regis.invoice_ecs_bills where parentid='".$v."' and billing_city='".$row_ecs_details_chk['billing_city']."'  $ecs_date";
						$res_ecs_details = parent::execQuery($query_ecs_details, $this->idc_con);
								
					}
				}
				if($this->completeMonth >= '2018-05-21')
				{
					 $query_upd_bill 			= "SELECT * FROM db_invoice.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."'  $ecs_date_upd GROUP BY parentid";
					 $res_upd_bill 				= parent::execQuery($query_upd_bill, $this->fin_con_slave);
				}else
				{
					$query_upd_bill 			= "SELECT * FROM db_payment.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."'  $ecs_date_upd GROUP BY parentid";
					$res_upd_bill 				= parent::execQuery($query_upd_bill, $this->idc_con);
				}
					if($res_upd_bill && mysql_num_rows($res_upd_bill) > 0){
						$row_locked_details = mysql_fetch_assoc($res_upd_bill);
					
						
						$query_ecs_details="SELECT * FROM test.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date AND credit_note=0 ORDER BY clearance_date DESC";
						$res_gst_details 	= parent::execQuery($query_ecs_details, $this->idc_con);
						$res_gst_res 		= mysql_fetch_assoc($res_gst_details);
						 
						if($row_locked_details['billnumber'] == '' || $row_locked_details['billnumber'] == '-')
						{
							if($this->completeMonth >= '2018-05-21')
							{	
								$upd_gstn		="UPDATE db_invoice.tbl_reciept_invoice_record_new SET billnumber='".$res_gst_res['billnumber']."' WHERE parentid='".$this->parentid."' $ecs_date_upd";
								$res_upd_gstn 	= parent::execQuery($upd_gstn, $this->fin_con_slave); 
							}else
							{
								$upd_gstn		="UPDATE db_payment.tbl_reciept_invoice_record_new SET billnumber='".$res_gst_res['billnumber']."' WHERE parentid='".$this->parentid."' $ecs_date_upd";
								$res_upd_gstn 	= parent::execQuery($upd_gstn, $this->idc_con); 
							}
						}
					}
				
		}else
		{
			
			$count = 2;
				$drop_main_details		="DROP TABLE IF EXISTS test.invoice_ecs_bills";
				$con_main_drop			=	parent::execQuery($drop_main_details, $this->fin_con);

				$create_main_details		="CREATE table test.invoice_ecs_bills LIKE db_finance.invoice_ecs_bills_fin";
				$con_main					=	parent::execQuery($create_main_details, $this->fin_con);


				 $query_ecs_details="SELECT parentid,billing_city,VERSION AS version,clearance_date,billnumber,credit_note,dc_by FROM db_finance.invoice_ecs_bills_fin WHERE parentid='".$this->parentid."' $ecs_date GROUP BY parentid";
				$con_ecs		=	parent::execQuery($query_ecs_details, $this->fin_con);
				$numRows 		= 	mysql_num_rows($con_ecs); 
				if($numRows > 0){
					while($num_row = mysql_fetch_assoc($con_ecs)){
						
						$num_row_new[]		=	$num_row['parentid'];
					}
					
				}

			
				foreach($num_row_new as $k=>$v)
				{
					 $query_ecs_details_city_chk="SELECT * FROM db_finance.invoice_ecs_bills_fin WHERE parentid='".$v."' $ecs_date ORDER BY clearance_date DESC limit 1";
					$res_ecs_details_chk = parent::execQuery($query_ecs_details_city_chk, $this->fin_con);
					$row_ecs_details_chk = mysql_fetch_assoc($res_ecs_details_chk);
					
					
					
					if(strtolower($row_ecs_details_chk['billing_city']) == strtolower($version_ct))
					{
						 $query_ecs_details="INSERT INTO test.invoice_ecs_bills select * from db_finance.invoice_ecs_bills_fin where parentid='".$v."' and billing_city='".$row_ecs_details_chk['billing_city']."'  $ecs_date";
						$res_ecs_details = parent::execQuery($query_ecs_details, $this->fin_con);
								
					}
				}
				
				 $query_upd_bill 			= "SELECT * FROM db_payment.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."'  $ecs_date_upd GROUP BY parentid";
				 $res_upd_bill 				= parent::execQuery($query_upd_bill, $this->idc_con);
					if($res_already_there && mysql_num_rows($res_upd_bill) > 0){
						$res_upd_bill = mysql_fetch_assoc($res_upd_bill);
						
						$query_ecs_details="SELECT * FROM test.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date AND credit_note=0 ORDER BY clearance_date DESC";
						$res_gst_details 	= parent::execQuery($query_ecs_details, $this->fin_con);
						$res_gst_res 		= mysql_fetch_assoc($res_gst_details);
						 
						if($row_locked_details['billnumber'] == '' || $row_locked_details['billnumber'] == '-')
						{
							if($this->completeMonth >= '2018-05-21')
							{
								$upd_gstn		="UPDATE db_invoice.tbl_reciept_invoice_record_new SET billnumber='".$res_gst_res['billnumber']."' WHERE parentid='".$this->parentid."' $ecs_date_upd";
								$res_upd_gstn 	= parent::execQuery($upd_gstn, $this->fin_con_slave); 
							}else
							{
								$upd_gstn		="UPDATE db_payment.tbl_reciept_invoice_record_new SET billnumber='".$res_gst_res['billnumber']."' WHERE parentid='".$this->parentid."' $ecs_date_upd";
								$res_upd_gstn 	= parent::execQuery($upd_gstn, $this->idc_con); 
							}
						}
					}
		}
	
	}
			
		if($count == 1)
		{
			$query_ecs_details="SELECT * FROM test.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date AND credit_note=0 ORDER BY clearance_date DESC";
			$res_ecs_details = parent::execQuery($query_ecs_details, $this->idc_con);
		}
		if($count == 2)
		{
			$query_ecs_details="SELECT * FROM test.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date AND credit_note=0 ORDER BY clearance_date DESC";
			$res_ecs_details = parent::execQuery($query_ecs_details, $this->fin_con);
		}
        if($count == 3)
		{
			 $query_ecs_details="SELECT * FROM online_regis.invoice_ecs_bills WHERE parentid='".$this->parentid."' $ecs_date AND credit_note=0 ORDER BY clearance_date DESC";
			$res_ecs_details = parent::execQuery($query_ecs_details, $this->idc_con);
		}
        
        if($res_ecs_details && mysql_num_rows($res_ecs_details)	>	0){
	        while($row_ecs_details = mysql_fetch_assoc($res_ecs_details)){
				
	           if(strtolower($version_ct) === strtolower($row_ecs_details['billing_city']))
	           { 
				   $usrCode											=	 $row_ecs_details['dc_by'];
				   
				   $srviceTaxPerc	=	$row_ecs_details['gst']/100;
				   $row_ecs_details['parentid'] = trim($row_ecs_details['parentid']);
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['deal_closed_user'][]         = 	$row_ecs_details['dc_by'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['tme_code'][]         = 	$row_ecs_details['tme_code'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['me_code'][]         = 	$row_ecs_details['me_code'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['dc_by_name'][]         		= 	$row_ecs_details['dc_by_name'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['pan_no'][]        			= 	$row_ecs_details['pan_no'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['tan_no'][]        			= 	$row_ecs_details['tan_no'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['gst_no'][]        			= 	$receipt_details_arr['gst_info']['gstn_no'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['clearance_date'][]        	= 	$row_ecs_details['clearance_date'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['billAmount'][]        		= 	$row_ecs_details['billAmount'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['bill_tds_amount'][]        	= 	$row_ecs_details['bill_tds_amount'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['gst'][]        				 = 	$row_ecs_details['gst'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['billing_city'][]        				 = 	$row_ecs_details['billing_city'];
				   $receipt_details_arr['gst_info'][$row_ecs_details['parentid']]['billnumber'][]        				 = 	$row_ecs_details['billnumber'];
				}
	        }
		}
		
		
		if($receipt_details_arr['gst_info']['gstn_state_name']!='' && $receipt_details_arr['gst_info']['gstn_state_name']!= null)
		{
			$stateName = $receipt_details_arr['gst_info']['gstn_state_name'];
			$ctName = $ecs_gen_details['comp_gen_info']['contract_city'];
			
		}
		if($receipt_details_arr['gst_info']['gstn_state_name']=='' && $receipt_details_arr['gst_info']['gstn_state_name']== null){
			
			$state_name_2ndrule = "SELECT state_name,ct_name FROM d_jds.tbl_city_master WHERE ct_name ='".$ecs_gen_details['comp_gen_info']['contract_city']."'";
			$res_state_2nd = parent::execQuery($state_name_2ndrule, $this->local_d_jds);
			$row_state_2nd = mysql_fetch_assoc($res_state_2nd);
			$stateName = $row_state_2nd['state_name'];	
			$ctName = $row_state_2nd['ct_name'];	
		}
			
		if($row_state_2nd['state_name'] =='' && ($receipt_details_arr['gst_info']['gstn_state_name']=='' && $receipt_details_arr['gst_info']['gstn_state_name']== null))
		{
			$ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$ssourl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$transferstring = curl_exec($ch);
			curl_close($ch);
			$sso_det=json_decode($transferstring,TRUE);
			$employee_city=$sso_det['data']['city'];
			if($employee_city=='Delhi'){
				$employee_city='Noida';
			}
			
			if($employee_city == '')
			{
				$employee_city = 'Mumbai';
			}else
			{
				$employee_city = $employee_city;
			}
			
			$getState_main="SELECT state_id,state_name,ct_name FROM d_jds.city_master WHERE ct_name='".$employee_city."'";
			$stateData_main = parent::execQuery($getState_main, $this->local_d_jds);
			$row = mysql_fetch_assoc($stateData_main);
			$stateName  =$row['state_name'];
			$ctName  =$row['ct_name'];
			$stateId    =$row['state_id'];
		}	
		

		
        
        $contractName   =$row_receipt['receipt_details']['comp_gen_info']['Company Name'];
		$contactPerson  =$row_receipt['receipt_details']['comp_gen_info']['Customer Name'];
		$stateName      =$stateName;
		$areaName       =$row_receipt['receipt_details']['comp_gen_info']['area'];
		$pincode        =$row_receipt['receipt_details']['comp_gen_info']['Pincode'];
		$fullAddress    =$row_receipt['receipt_details']['comp_gen_info']['full_address'];
        
       
        $sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
		if($this->params['action']==5)
			$sqlSelUserDet = "SELECT city,companyname,contact_person,state,area,pincode,full_address FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
		switch(strtolower($this->module)){
			case 'cs': 
			$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_iro_conn); 
			$rowDet = mysql_fetch_assoc($resSelUserDet);
			break;
			case 'tme':
			if($this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'tme';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->local_tme_conn); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			break;
			case 'me': 
			case 'jda':
			if(stripos($sqlSelUserDet, 'tbl_companymaster_generalinfo_shadow') !== false)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->params['data_city'];
				$mongo_inputs['module']		= 'me';
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "companyname,contact_person,mobile,landmark,street,area,city,pincode,email,building_name";
				$rowDet 					= $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$resSelUserDet = parent::execQuery($sqlSelUserDet, $this->idc_con); 
				$rowDet = mysql_fetch_assoc($resSelUserDet);
			}
			break;
			default: die('Invalid Module99'); break;
		}
		
        
			if($usrCode=='ME1')
			{
				$usrCode = 	$receipt_details_arr['gst_info'][$this->parentid]['tme_code'][0];
				
			}else if($usrCode=='TME1')
			{
				$usrCode = 	$receipt_details_arr['gst_info'][$this->parentid]['me_code'][0];
			}else
			{
				$usrCode = $usrCode;
			}
        
        
        
        $ssourl= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$usrCode;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$ssourl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $transferstring = curl_exec($ch);
        curl_close($ch);
        $sso_det=json_decode($transferstring,TRUE);
        $dc_employee_city=$sso_det['data']['city'];
        if($dc_employee_city=='Delhi'){
            $dc_employee_city='Noida';
        }
		if($dc_employee_city=='Ahemdabad'){
            $dc_employee_city='Ahmedabad';
        }
        
        if($dc_employee_city == '')
		{
			$dc_employee_city = 'Mumbai';
		}else
		{
			$dc_employee_city = $dc_employee_city;
		}
        
        $getState_dc="SELECT state_id,state_name FROM d_jds.city_master WHERE ct_name='".$dc_employee_city."'";
        $stateData_dc = parent::execQuery($getState_dc, $this->local_d_jds);
        while($row = mysql_fetch_assoc($stateData_dc)){
            $stateName_dc  =$row['state_name'];
            $stateId_dc    =$row['state_id'];
        }
        
        
         $getJdData_dc="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName_dc."' AND jd_city='".$dc_employee_city."'";
        $resJdData_dc = parent::execQuery($getJdData_dc, $this->db_payment);
        if(mysql_num_rows($resJdData_dc)==0){
			 $getJdData_dc="select * from tbl_invoice_user_jd_details WHERE state_name='".$stateName_dc."'";
            $resJdData_dc = parent::execQuery($getJdData_dc, $this->db_payment);
        }
        
      
        

        while($row_resJdData = mysql_fetch_assoc($resJdData_dc)){
		
			$receipt_details_arr['gst_info']['jd_state_code']           = $row_resJdData['state_code'];
            $receipt_details_arr['gst_info']['jd_state_name']           = $row_resJdData['state_name'];
            $receipt_details_arr['gst_info']['jd_jd_city']              = $row_resJdData['jd_city'];
            $receipt_details_arr['gst_info']['jd_cin_no']               = $row_resJdData['cin_no'];
            $receipt_details_arr['gst_info']['jd_pan_no']               = $row_resJdData['pan_no'];
            $receipt_details_arr['gst_info']['jd_jd_gst']               = $row_resJdData['jd_gst'];
            $receipt_details_arr['gst_info']['jd_billing_address']      = $row_resJdData['jd_billing_address'];
            $receipt_details_arr['gst_info']['jd_vat_tin_no']           = $row_resJdData['vat_tin_no'];
            $receipt_details_arr['gst_info']['jd_sac_code']             = $row_resJdData['sac_code'];
            $receipt_details_arr['gst_info']['jd_hsn_code']             = $row_resJdData['hsn_code'];
        }
    
        
        
        if($receipt_details_arr['gst_info']['gstn_contract_name']==''){
            $receipt_details_arr['gst_info']['gstn_contract_name']=$contractName;
        }
        if($receipt_details_arr['gst_info']['gstn_state_name']==''){
            $receipt_details_arr['gst_info']['gstn_state_name']=$stateName;
        }
        if($receipt_details_arr['gst_info']['area']==''){
            $receipt_details_arr['gst_info']['area']=$areaName;
        }
        if($receipt_details_arr['gst_info']['gstn_pincode']==''){
            $receipt_details_arr['gst_info']['gstn_pincode']=$pincode;
        }
        if($receipt_details_arr['gst_info']['gstn_address']==''){
            $receipt_details_arr['gst_info']['gstn_address']=$fullAddress;
        }
        
        if($row_receipt['gst_info']['gstn_no'] == ''){
			$row_receipt['gst_info']['gstn_no']	=	$receipt_details_arr['gst_info']['gstn_no'];
		}
        
        $receipt_details_arr['gst_info']['service_tax_percent']= $srviceTaxPerc; //check the connection
        
      
        
        $totAmt			=	$row_ecs_details['billAmount'];
        $totlForgst		=	str_replace(",", '', $totAmt);
        $totlForgst		=	(float)$totlForgst;
        
        $buyerState		=	strtolower($stateName);
        $sellerState	=	strtolower($row_receipt['gst_info']['jd_state_name']);
         
         if($srviceTaxPerc=='0.18'){
            if($buyerState==$sellerState){
                $cgstPer=9;
                $sgstPer=9;
                $igstPer=0;
            }else if($buyerState!=$sellerState){
                $cgstPer=0;
                $sgstPer=0;
                $igstPer=18;
            }
            $cgst=($cgstPer/100)*$totlForgst;
            $sgst=($sgstPer/100)*$totlForgst;
            $igst=($igstPer/100)*$totlForgst;
            $tot_gst=$cgst+$sgst+$igst;
            $gross_total=round($totlForgst + $tot_gst);
        }else if ($srviceTaxPerc=='0.15'){
            $cgstPer='0';
            $sgstPer='0';
            $igstPer='0';
            $cgst='0';
            $sgst='0';
            $igst='0';
            $tot_gst='0';
        }
        
     
		$RcptInvArray							=	$this->makeEcsRecieptId($stateName,'SE',$this->ecsMonth,$receipt_details_arr['gst_info'][$this->parentid]['clearance_date'][0],$receipt_details_arr['gst_info']['jd_jd_city'],$receipt_details_arr['gst_info'][$this->parentid]['billing_city'][0],$ecs_gen_details['comp_gen_info']['contract_city'],$usrCode,$receipt_details_arr['gst_info']['jd_state_name'],$ecs_date,$ecs_date_upd,$receipt_details_arr['gst_info'][$this->parentid]['billnumber'][0],$this->completeMonth,$ecs_date_datetime);
		$invoiceNumber							=	$RcptInvArray['invoice'];
		
			$receipt_details_arr['gst_info']['gstn_pincode']        = $row_receipt['gst_info']['gstn_pincode'];
			$receipt_details_arr['gst_info']['gstn_no']             = $row_receipt['gst_info']['gstn_no'];
			$jd_state_name      = $receipt_details_arr['gst_info']['jd_state_name'];
			$gstn_state_name    =  $stateName;
			$gst				= $receipt_details_arr['gst_info'][$this->parentid]['gst'][0]==''?"-":$receipt_details_arr['gst_info'][$this->parentid]['gst'][0];
			
			$total_amount=0;
			$bill_tds_amount_tds=0;
			foreach($receipt_details_arr['gst_info'][$this->parentid]['clearance_date'] as $key=>$val)
			{
				$total_amount 	= $total_amount+(int)$receipt_details_arr['gst_info'][$this->parentid]['billAmount'][$key];
				$bill_tds_amount_tds = $bill_tds_amount_tds + $receipt_details_arr['gst_info'][$this->parentid]['bill_tds_amount'][$key];
			}
			
			
			$gross_amount = round($total_amount+$bill_tds_amount_tds);	
			$net_amount = round(($total_amount+$bill_tds_amount_tds)/(1+$gst/100));	
					 
					
					 
		
			if(strtolower($jd_state_name) === strtolower($gstn_state_name))
			{
				$gst_split = $gst/2;
				$percAmnt = round($net_amount*$gst_split/100);
				
			}else
			{
				$gst_split = 0;
				$percAmnt = 0;
			}
			
			if(strtolower($jd_state_name) !== strtolower($gstn_state_name))
			{
				$gst_all = $gst;
				$percAmntall = round($net_amount*$gst/100);
			}else
			{
				$gst_all = 0;
				$percAmntall = 0;
			}
			$percAmntallTotal = round($net_amount*$gst/100);
			$instrumentAmount = ($net_amount + $percAmntallTotal) - ($bill_tds_amount_tds);
			
			
			if($row_receipt['receipt_details']['comp_gen_info']['contract_state']!='')
			{
				$client_state_final = $row_receipt['receipt_details']['comp_gen_info']['contract_state'];
			}
			if($client_state_final == '' && $row_gst_details['gstn_state_name']!='')
			{
				$client_state_final = $row_gst_details['gstn_state_name'];
			}
			
			if($this->completeMonth >= '2018-05-21')
			{
			   $update_alldetails = "UPDATE db_invoice.tbl_reciept_invoice_record_new SET total_amount = '".$total_amount."',
																									gross_amount = '".$gross_amount."',
																									net_amount = '".$net_amount."',
																									CGST = '".$percAmnt."',
																									SGST = '".$percAmnt."',
																									IGST = '".$percAmntall."',
																									Total_GST = '".$percAmntallTotal."',
																									TDS = '".$bill_tds_amount_tds."',
																									instrumentAmount = '".$instrumentAmount."',
																									insert_date=NOW(),
																									clearance_date='".$receipt_details_arr['gst_info'][$this->parentid]['clearance_date'][0]."',
																									approval_date='".$receipt_details_arr['gst_info'][$this->parentid]['clearance_date'][0]."',
																									companyname='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Company Name'])."',
																									pincode='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Pincode'])."',
																									billnumber ='".$receipt_details_arr['gst_info'][$this->parentid]['billnumber'][0] ."',
																									city='".addslashes($receipt_details_arr['gst_info']['jd_jd_city'])."',
																									billing_city='".addslashes($receipt_details_arr['gst_info'][$this->parentid]['billing_city'][0])."',
																									contact_person='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Customer Name'])."',
																									version = '".$this->params['version']."',
																									invoice_create = 1,
																									client_name='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Customer Name'])."',
																									contract_city='".addslashes($row_receipt['receipt_details']['comp_gen_info']['contract_city'])."',
																									client_state='".addslashes($row_receipt['receipt_details']['comp_gen_info']['contract_state'])."',
																									client_gstn_state='".addslashes($row_gst_details['gstn_state_name'])."',
																									client_gstn_server_city='".addslashes($row_gst_details['server_city'])."',
																									client_building_address='".addslashes($receipt_details_arr['gst_info']['gstn_address'])."',
																									client_email='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Email'])."',
																									client_contactno='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Contact No'])."',
																									client_pan='".$receipt_details_arr['gst_info'][$this->parentid]['pan_no'][0]."',
																									client_tan='".$receipt_details_arr['gst_info'][$this->parentid]['tan_no'][0]."',
																									client_gst='".$receipt_details_arr['gst_info'][$this->parentid]['gst_no'][0]."',
																									
																									jd_cin_no='".$receipt_details_arr['gst_info']['jd_cin_no']."',
																									jd_pan_no='".$receipt_details_arr['gst_info']['jd_pan_no']."',
																									jd_gst='".$receipt_details_arr['gst_info']['jd_jd_gst']."',
																									jd_building_address='".addslashes($receipt_details_arr['gst_info']['jd_billing_address'])."',
																									jd_sac_code='".$receipt_details_arr['gst_info']['jd_sac_code']."',
																									jd_state_name ='".$receipt_details_arr['gst_info']['jd_state_name'] ."',
																									jd_city_name='".addslashes($receipt_details_arr['gst_info']['jd_jd_city'])."',
																									
																									dc_by ='".$receipt_details_arr['gst_info'][$this->parentid]['deal_closed_user'][0] ."',
																									dc_by_name ='".addslashes($receipt_details_arr['gst_info'][$this->parentid]['dc_by_name'][0])."',
																									client_state_final = '".addslashes($client_state_final)."',
																									state_invoice_final = '".addslashes($stateName)."'
																									WHERE parentid='".$this->parentid."' $ecs_date";
			$con_ecs		=	parent::execQuery($update_alldetails, $this->fin_con_slave);
		}else
		{
			$update_alldetails = "UPDATE db_payment.tbl_reciept_invoice_record_new SET total_amount = '".$total_amount."',
																									gross_amount = '".$gross_amount."',
																									net_amount = '".$net_amount."',
																									CGST = '".$percAmnt."',
																									SGST = '".$percAmnt."',
																									IGST = '".$percAmntall."',
																									Total_GST = '".$percAmntallTotal."',
																									TDS = '".$bill_tds_amount_tds."',
																									instrumentAmount = '".$instrumentAmount."',
																									insert_date=NOW(),
																									companyname='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Company Name'])."',
																									pincode='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Pincode'])."',
																									billnumber ='".$receipt_details_arr['gst_info'][$this->parentid]['billnumber'][0] ."',
																									city='".addslashes($receipt_details_arr['gst_info']['jd_jd_city'])."',
																									billing_city='".addslashes($receipt_details_arr['gst_info'][$this->parentid]['billing_city'][0])."',
																									contact_person='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Customer Name'])."',
																									version = '".$this->params['version']."',
																									invoice_create = 1,
																									client_name='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Customer Name'])."',
																									contract_city='".addslashes($row_receipt['receipt_details']['comp_gen_info']['contract_city'])."',
																									client_state='".addslashes($row_receipt['receipt_details']['comp_gen_info']['contract_state'])."',
																									client_gstn_state='".addslashes($row_gst_details['gstn_state_name'])."',
																									client_gstn_server_city='".addslashes($row_gst_details['server_city'])."',
																									client_building_address='".addslashes($receipt_details_arr['gst_info']['gstn_address'])."',
																									client_email='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Email'])."',
																									client_contactno='".addslashes($row_receipt['receipt_details']['comp_gen_info']['Contact No'])."',
																									client_pan='".$receipt_details_arr['gst_info'][$this->parentid]['pan_no'][0]."',
																									client_tan='".$receipt_details_arr['gst_info'][$this->parentid]['tan_no'][0]."',
																									client_gst='".$receipt_details_arr['gst_info'][$this->parentid]['gst_no'][0]."',
																									
																									jd_cin_no='".$receipt_details_arr['gst_info']['jd_cin_no']."',
																									jd_pan_no='".$receipt_details_arr['gst_info']['jd_pan_no']."',
																									jd_gst='".$receipt_details_arr['gst_info']['jd_jd_gst']."',
																									jd_building_address='".addslashes($receipt_details_arr['gst_info']['jd_billing_address'])."',
																									jd_sac_code='".$receipt_details_arr['gst_info']['jd_sac_code']."',
																									jd_state_name ='".$receipt_details_arr['gst_info']['jd_state_name'] ."',
																									jd_city_name='".addslashes($receipt_details_arr['gst_info']['jd_jd_city'])."',
																									
																									dc_by ='".$receipt_details_arr['gst_info'][$this->parentid]['deal_closed_user'][0] ."',
																									dc_by_name ='".addslashes($receipt_details_arr['gst_info'][$this->parentid]['dc_by_name'][0])."',
																									client_state_final = '".addslashes($client_state_final)."',
																									state_invoice_final = '".addslashes($stateName)."'
																									WHERE parentid='".$this->parentid."'  AND billnumber='".$receipt_details_arr['gst_info'][$this->parentid]['billnumber'][0]."' $ecs_date";
			$con_ecs		=	parent::execQuery($update_alldetails, $this->db_payment);
		}
        
			switch(strtolower($receipt_details_arr['gst_info'][$this->parentid]['billing_city'][0])){
            case 'mumbai': $citycd='mum'; break;
            case 'delhi': $citycd='del'; break;
            case 'kolkata': $citycd='kol'; break;
            case 'bangalore': $citycd='blr'; break;
            case 'chennai': $citycd='chn'; break;
            case 'pune': $citycd='pun'; break;
            case 'hyderabad': $citycd='hyd'; break;
            case 'ahmedabad': $citycd='ahm'; break;
            default: $citycd='rem'; break;
		}
		//$table_upd = 'test.invoice_ecs_bills_'.$citycd.'_'.$this->ecsMonth.'_02';
        
        
			 $upd_bills_temp		=	"UPDATE test.invoice_ecs_bills_gst SET exist_flag_new = 1 WHERE parentid='".$this->parentid."' $ecs_date";
				$upEcsbills_temp 	= 	parent::execQuery($upd_bills_temp, $this->fin_con_slave);
        
        
         if($this->completeMonth >= '2018-05-21')
		{
			$query_already_there 	= "SELECT * FROM db_invoice.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."' AND invoice_create=1 $ecs_date GROUP BY parentid";
			$res_already_there 		= parent::execQuery($query_already_there, $this->fin_con_slave);
		}else
		{
			$query_already_there 	= "SELECT * FROM db_payment.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."' AND invoice_create=1 $ecs_date GROUP BY parentid";
			$res_already_there 		= parent::execQuery($query_already_there, $this->db_payment);
		}
			if($res_already_there && mysql_num_rows($res_already_there) > 0){
				$row_locked_details = mysql_fetch_assoc($res_already_there);
			}
        
        //~ $query_already_there 	= "SELECT * FROM db_payment.tbl_reciept_invoice_record_new WHERE parentid='".$this->parentid."' AND invoice_create=1 $ecs_date_upd GROUP BY parentid";
		//~ $res_already_there 		= parent::execQuery($query_already_there, $this->db_payment);
        //~ if($res_already_there && mysql_num_rows($res_already_there) > 0){
			//~ $row_locked_details = mysql_fetch_assoc($res_already_there);
		//~ }
     
		return $row_locked_details;
	}
	
	
	

}


?>
