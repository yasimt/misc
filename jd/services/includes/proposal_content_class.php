<?php

class proposal_content_class extends DB
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
			
			if($this->params['ecs_flag']) {
				$this->ecs_flag = $this->params['ecs_flag'];
				
				if($this->params['bill_cycle']) {
				$this->bill_cycle = $this->params['bill_cycle'];
				}else{
					$errorarray['errormsg']='bill_cycle missing';
					echo json_encode($errorarray); exit;
				}
				
				if($this->params['bill_cycle_amt']) {
				$this->bill_cycle_amt = $this->params['bill_cycle_amt'];
				}else{
					$errorarray['errormsg']='bill_cycle_amt missing';
					echo json_encode($errorarray); exit;
				}
				
				if($this->params['advance_amt']) {
				$this->advance_amt = $this->params['advance_amt'];
				}
				
				if($this->params['advance_duration']) {
				$this->advance_duration = $this->params['advance_duration'];
				}
				
				
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
		$this->local_tme_conn	= $db[strtolower($data_city)]['tme_jds']['master'];
		$this->local_d_jds		= $db[strtolower($data_city)]['d_jds']['master'];
		$this->local_iro_conn	= $db[strtolower($data_city)]['db_iro']['master'];
		$this->fin_con			= $db[strtolower($data_city)]['fin']['master'];
		
	}
	
	function get_proposal_content($contractdetailsclassobj)
	{
		$qryChecknewInvoiceName = "select invoice_businessname,invoice_cpersonname,invoice_cpersonnum from payment_otherdetails where parentid='".$this->parentid."' and version ='".$this->version."'";
		$resChecknewInvoiceName = parent::execQuery($qryChecknewInvoiceName, $this->fin_con);
		if($resChecknewInvoiceName && mysql_num_rows($resChecknewInvoiceName)>0){
			$rowChecknewInvoiceName = mysql_fetch_assoc($resChecknewInvoiceName);
			$newComapanyName = $rowChecknewInvoiceName['invoice_businessname'];
			$newContactPerson = $rowChecknewInvoiceName['invoice_cpersonname'];
			$newContactNo = $rowChecknewInvoiceName['invoice_cpersonnum'];
		}
		
		$sqlSel = "SELECT companyname,contact_person,mobile,landmark,street,area,city,pincode,email FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."' ";
		$omni_combo_sql= "SELECT * 
						 FROM  dependant_campaign_details_temp 
						 WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$temp_sql	   = "SELECT * 
						 FROM  tbl_companymaster_finance_temp  
						 WHERE parentid='".$this->parentid."' /*AND version='".$this->version."'*/  AND recalculate_flag=1";				 
		$sql_fnc       = "SELECT campaignid, campaignname  
						  FROM payment_campaign_master";
		
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$resSel	  = parent::execQuery($sqlSel, $this->local_iro_conn);
			$temp_res = parent::execQuery($temp_sql, $this->fin_con);
			$res_fnc  = parent::execQuery($sql_fnc, $this->fin_con);
			break;
			case 'tme':
			$resSel 		= parent::execQuery($sqlSel, $this->local_tme_conn);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->local_tme_conn);
			$temp_res 		= parent::execQuery($temp_sql, $this->local_tme_conn);
			$res_fnc 		= parent::execQuery($sql_fnc, $this->fin_con);
			break;
			case 'me':
			$resSel			= parent::execQuery($sqlSel, $this->idc_con);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$temp_res 		= parent::execQuery($temp_sql, $this->idc_con);
			$res_fnc 		= parent::execQuery($sql_fnc, $this->fin_con);
			break;
			case 'jda':
			$resSel			= parent::execQuery($sqlSel, $this->idc_con);
			$omni_combo_res = parent::execQuery($omni_combo_sql, $this->idc_con);
			$res_fnc 		= parent::execQuery($sql_fnc, $this->fin_con);
			break;
			default:
			die('Invalid Module');
			break;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br>sql:: '.$temp_sql;
			echo '<br>res:: '.$temp_res;
			echo '<br>rows:: '.mysql_num_rows($temp_res);
		}
		
		if($res_fnc && mysql_num_rows($res_fnc)>0)
		{
			while($row_fnc = mysql_fetch_assoc($res_fnc))
			{
				$campaign_name_arr[$row_fnc['campaignid']] = $row_fnc['campaignname'];
			}
			
			$campaign_name_arr[1] = 'Supreme Package';
		}
		
		if($omni_combo_res && mysql_num_rows($omni_combo_res)>0){
			
			$omni_combo_row=mysql_fetch_assoc($omni_combo_res);
			$omni_details_combo['omni_combo_name']=$omni_combo_row['combo_type']; 

		}
		
		if($temp_res && mysql_num_rows($temp_res))
		{
			while($temp_row = mysql_fetch_assoc($temp_res))
			{
				$campaign_arr[$temp_row['campaignid']]['campaignname'] 	  = $campaign_name_arr[$temp_row['campaignid']];
				$campaign_arr[$temp_row['campaignid']]['version'] 	   	  = $temp_row['version'];
				$campaign_arr[$temp_row['campaignid']]['budget']	 	  = $temp_row['budget'];
				$campaign_arr[$temp_row['campaignid']]['monthly_budget']  = number_format(round(((($temp_row['budget']/$temp_row['duration'])*365)/12)));
				$campaign_arr[$temp_row['campaignid']]['duration']	   	  = $temp_row['duration'];
				/*$deal_close_date														 = $row_get_contract_campaigns['entry_date'];
				$campaign_arr[$temp_row['campaignid']]['balance']		 = $row_get_contract_campaigns['balance'];
				$ecs_flag 																 = $row_get_contract_campaigns['ecsflag'];
				$tme_name 																 = $row_get_contract_campaigns['tmeName'];
				$me_name 																 = $row_get_contract_campaigns['meName'];*/
				
				$total_budget	 += $temp_row['budget'];
				$total_mn_budget += (($temp_row['budget']/$temp_row['duration'])*365)/12;
			}
			
			$total_budget_service_tax = $total_budget* self :: CURRENT_SERVICE_TAX;
			$tot_budget_plus_serv_tax = $total_budget + $total_budget_service_tax;
			
			$total_mn_service_tax = $total_mn_budget * self :: CURRENT_SERVICE_TAX;
			$tot_mn_plus_serv_tax = $total_mn_budget + $total_mn_service_tax;
		}
		
		$original_campaign_arr = $campaign_arr;
		
		if($original_campaign_arr[2]['budget']>0 && $original_campaign_arr[1]['budget']>0)
			$campaign_type = 3;
		else if($original_campaign_arr[2]['budget']>0 && !$original_campaign_arr[1]['budget'])
			$campaign_type = 2;
		else if(!$original_campaign_arr[2]['budget'] && $original_campaign_arr[1]['budget']>0)
			$campaign_type = 1;
		else if (count($original_campaign_arr)>0)
			$campaign_type = 4;
		
		if(DEBUG_MODE)
		{
			echo 'before<pre>';
			print_r($campaign_arr);
			print_r($omni_details_combo);
		}
		
		/*fix for registration fee - start*/			
			if($campaign_arr[2]['budget']>0 && $campaign_arr[7]['budget']>0) {
				$campaign_arr[2]['budget'] = $campaign_arr[2]['budget'] + $campaign_arr[7]['budget'];
				unset($campaign_arr[7]);
			}
			else if($campaign_arr[1]['budget']>0 && $campaign_arr[7]['budget']>0) {
				$campaign_arr[1]['budget'] = $campaign_arr[1]['budget'] + $campaign_arr[7]['budget'];
				unset($campaign_arr[7]);
			}
		/*fix for registration fee - end*/
		
		/*fix for banner and jdrr plus - start*/			
			if(($campaign_arr[5]['budget'] + $campaign_arr[13]['budget']) == 8 && $campaign_arr[22]['budget']) {
				
				$campaign_arr[51322]['budget'] 		 = ($campaign_arr[5]['budget'] + $campaign_arr[13]['budget'] + $campaign_arr[22]['budget']);
				$campaign_arr[51322]['campaignname'] = "Jdrr Plus";
				$campaign_arr[51322]['duration']    	 = min($campaign_arr[5]['duration'], $campaign_arr[13]['duration'], $campaign_arr[22]['duration']);
				unset($campaign_arr[5]);
				unset($campaign_arr[13]);
				unset($campaign_arr[22]);
			}
			else if(($campaign_arr[5]['budget'] + $campaign_arr[13]['budget']) == 8) {
				$campaign_arr[513]['budget'] 		= ($campaign_arr[5]['budget'] + $campaign_arr[13]['budget']);
				$campaign_arr[513]['campaignname']  = "Banner";
				$campaign_arr[513]['duration']      = min($campaign_arr[5]['duration'], $campaign_arr[13]['duration']);
				unset($campaign_arr[5]);
				unset($campaign_arr[13]);
			}
		/*fix for banner and jdrr plus - end*/
		if(DEBUG_MODE)
		{
			echo 'before11<pre>';
			print_r($campaign_arr);
		}
		
			
		
		foreach($campaign_arr as $key => $val)
		{	
			
			if(($key == '1' || $key == '73') && $omni_details_combo['omni_combo_name'] != '')
			$val['campaignname'] = $omni_details_combo['omni_combo_name'];
			
			$budget[$val['campaignname']]['budget']   = number_format($val['budget']);;
			$budget[$val['campaignname']]['duration'] = $val['duration'];
		}
		
		if(DEBUG_MODE)
		{
			echo 'Proposal campaigns<pre>';
			print_r($budget);
		}
		//die;
		
		//$omni_details_combo['omni_combo_name']=='Combo 2';
		
		
		$category_bidding_arr = $contractdetailsclassobj->GetPlatDiamCategories();
		
		//echo 'category bidding details arr <pre>';print_r($category_bidding_arr);
		
		if($original_campaign_arr[2]['budget']>0 && count($category_bidding_arr))
		{
			$catid_arr  = array_keys($category_bidding_arr);
			//echo 'category details arr <pre>';print_r($catid_arr);
			
			$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catid_arr)."')";
			$res_category = parent::execQuery($sql_category, $this->local_d_jds);
			if($res_category && mysql_num_rows($res_category)>0)
			{
				while($row_category =  mysql_fetch_assoc($res_category))
				{
					$category_name_arr[$row_category['catid']]['catname'] = $row_category['category_name'].'-'.$row_category['searchtype'];
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
				if($original_campaign_arr[1]['budget']>0)
				$package_category_array = array_unique($package_category_array);
				
				$annexure_details['bidding_details']['ddg_category_details']		 = $category_name_arr;
				$annexure_details['bidding_details']['ddg_category_pin_details']	 = $invoice_bidding_details_arr;
				$annexure_details['bidding_details']['pkg_details'] 		   		 = $package_category_array;
			/*--------------------------------------------------------------------------------------------------*/
			
				
		}
		
		if(($original_campaign_arr[1]['budget']>0 && ($original_campaign_arr[2]['budget'] <= 0 )) || $original_campaign_arr[10]['budget']>0)
		{
			$sql_pkg_cats ="select trim(',' FROM replace(catids,'|P|',',')) as catids from tbl_business_temp_data where contractid ='".$this -> parentid."'";
			switch(strtolower($this->module))
			{
				case 'cs':
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->local_d_jds);
				break;
				case 'tme':
				$res_pkg_cats 	= parent::execQuery($sql_pkg_cats, $this->local_tme_conn);
				break;
				case 'me':
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
				break;
				case 'jda':
				$res_pkg_cats	= parent::execQuery($sql_pkg_cats, $this->idc_con);
				break;
				default:
				die('Invalid Module');
				break;
			}
			if($res_pkg_cats && mysql_num_rows($res_pkg_cats)>0)
			{
				$row_pkg_cats = mysql_fetch_assoc($res_pkg_cats);
				$catids_arr = explode(',',$row_pkg_cats['catids']);
				if(count($catids_arr))
				{
					$sql_category = "SELECT catid,category_name,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid in ('".implode("','",$catids_arr)."')";
					$res_category = parent::execQuery($sql_category, $this->local_d_jds);
					if($res_category && mysql_num_rows($res_category)>0)
					{
						while($row_category =  mysql_fetch_assoc($res_category))
						{
							$category_pkg_name_arr[$row_category['catid']] = $row_category['category_name'];
						}
					}
					
					if($original_campaign_arr[1]['budget']>0) {
						$annexure_details['bidding_details']['pkg_details'] = $category_pkg_name_arr;
						$annexure_details['package_other_details']['package_consist_of'] = array('-Phone Priority Listing','-Web Blue Listing','-Video','-Catalog','-Corporate Logo','-WAP Priority Listing','-SMS Short Code Advt');
					}
					if($original_campaign_arr[10]['budget']>0) {
						$national_listing_categories = $category_pkg_name_arr;
					}
			
				}
			}
			
		}
		
		if($original_campaign_arr[4]['budget']>0 || $original_campaign_arr[5]['budget']>0 || $original_campaign_arr[13]['budget']>0)
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
				
			}
			
		}
		
		if($original_campaign_arr[10]['budget']>0)
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
 			}
 			
		}
		
		if($this->ecs_flag) {
			
			switch($this->bill_cycle)
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
				die('Invalid cycle selected');
				break;
			}
			$annexure_details['annexure_header']['ECS']['Billing Cycle'] = $cyle;
			$annexure_details['annexure_header']['ECS']['Advance Duration'] = $this->advance_duration .' days';
			$annexure_details['annexure_header']['ECS'][$cyle.' Amount(inc Serv. Tax @15%)'] = 'Rs.'.number_format($this->bill_cycle_amt);
			$annexure_details['annexure_header']['ECS']['Advance Amount(inc Serv. Tax @15%)'] = 'Rs.'.number_format($this->advance_amt);
			
		}
			//echo 'DDG category details arr <pre>';print_r($invoice_bidding_details_arr);
			//echo '<br>';
			//echo json_encode($category_name_arr);
			//echo '<br>';die;
			//echo 'package category details arr <pre>';print_r($package_category_array);
			
			if(DEBUG_MODE)
			{
				echo 'annexure content<pre>';
				print_r($annexure_details);
			}
			
			//echo '<pre>annexure content';print_r($annexure_details);die;
			
			if(count($budget)>0 ){
				$proposal_content['code']      			  = 1;
				$proposal_content['campaign_type']		  = $campaign_type;
				$proposal_content['original_budget']	  = $original_campaign_arr;
				$proposal_content['budget']    			  = $budget;
				$proposal_content['service_tax']		  = 15;
				
				$proposal_content['total_annual_budget']  = number_format(round($total_budget));
				$proposal_content['total_annual_tax']	  = number_format(round($total_budget_service_tax));
				$proposal_content['total_annual_gross']	  = number_format(round($tot_budget_plus_serv_tax));
				$proposal_content['total_monthly_budget'] = number_format(round($total_mn_budget));
				$proposal_content['total_monthly_tax']	  = number_format(round($total_mn_service_tax));
				$proposal_content['total_monthly_gross']  = number_format(round($tot_mn_plus_serv_tax));
				
				$proposal_content['annexure']  		= $annexure_details;
				////$error['return_message']='receipt data logged';
				//$error['code']='1';
				//return $error; 
				return $proposal_content;
			}else
			{
				$error['return_message']='No campaigns found';
				$error['code']='-1';
				return $error; 
			}
			
			//echo '<br>'; echo json_encode($annexure_details['bidding_details']);die;
			
	}
	
}


?>
