<?php

ini_set("memory_limit", "-1");
class selfSignUpClass extends DB
{
	var  $conn_default  = null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $configobj		= null;
	
	function __construct($params,$db)
	{
		
		$this->parentid     = trim($params['parentid']);
		$this->data_city    = trim($params['data_city']);
		$this->trace	    = trim($params['trace']);
		$this->dup_transid  = trim($params['dup_transid']);
		$this->pass_transid = trim($params['trans_id']);
		$this->module = trim($params['module']);
		$this->bypass = trim($params['bypass']);
		$this->db 			= $db;
		$this->conn_default = $this->db['remote']['idc']['master'];
		
		$this->conn_log 	= $db['db_log'];//reference to 17.103 server
		
		$data_city 		    = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_idc 	= $this->db[strtolower($data_city)]['idc']['master'];
		$this->conn_local   = $this->db[strtolower($data_city)]['d_jds']['master'];
		$this->fin          = $this->db[strtolower($data_city)]['fin']['master'];
		$this->setCommonInfo();
		
		$this->sms_email_Obj = new email_sms_send($db,$params['data_city']);

		$this->categoryClass_obj = new categoryClass();		
		//echo '<pre>';print_r($this->db);die;
		
	}
	// Function to set Common Information
	function setCommonInfo()
	{
		$this->configobj = new configclass();		
		
	}
	function processData()
	{
		
			$sqlFetchRecords = "SELECT parentid,companyname,data_city,campaigninfo,payment_type,trans_id, amount_paid, paymode, payment_id, bank_ref_no, payment_info, source,version, dealCloseType, user_code, user_name, module FROM online_regis1.tbl_selfsignup_contracts WHERE parentid='".$this->parentid."' AND data_city='".$this->data_city."' AND done_flag = 0  AND trans_id = '".$this->pass_transid."'";
			$resFetchRecords = parent::execQuery($sqlFetchRecords, $this->conn_default);
			if($this->trace)
			{
				echo '<br> sql :: '.$sqlFetchRecords;
				echo '<br> res :: '.$resFetchRecords;
				echo '<br> row :: '.parent::numRows($resFetchRecords);
			}
			if($resFetchRecords && parent::numRows($resFetchRecords)>0)
			{
				while($row_data = parent::fetchData($resFetchRecords))
				{
					if($this->trace)
					{
						echo '<pre> row :: ';
						print_r($row_data);
					}
					unset($parentid, $data_city, $campaigninfo, $trans_id, $valid_json,$total_budget,$amount_paid,$max_duration,$payment_type,$data_source,$payment_info,$passed_version,$emi,$user_code,$user_name,$campaign_type,$amount_paid_without_tax);
					$parentid 		= trim($row_data['parentid']);
					$data_city 		= trim($row_data['data_city']);
					$campaigninfo 	= trim($row_data['campaigninfo']);
					$passed_version = trim($row_data['version']);
					$trans_id 		= trim($row_data['trans_id']);
					$amount_paid	= trim($row_data['amount_paid']);
					$mode_of_payment= trim($row_data['paymode']);
					$mihpayid		= trim($row_data['payment_id']);
					$bank_ref_num	= trim($row_data['bank_ref_no']);
					$payment_info	= trim($row_data['payment_info']);
					$data_source 	= trim($row_data['source']);
					$payment_type 	= trim($row_data['payment_type']);
					$dealCloseType 	= trim($row_data['dealCloseType']);
					$user_code 		= trim($row_data['user_code']);
					$user_name 	    = trim($row_data['user_name']);
					
					$module 	    = trim($row_data['module']);
					
					$this->contract_source = trim($row_data['source']);
					
					if($dealCloseType == 3 && $passed_version && $data_city)
					{
						$sqlIgnore = "SELECT campaigninfo,payment_type,trans_id, amount_paid, paymode, payment_id, bank_ref_no, payment_info, source, dealCloseType FROM online_regis1.tbl_selfsignup_contracts WHERE parentid='".$parentid."' AND data_city='".$data_city."' AND version = '".$passed_version."'  AND trans_id != '".$this->pass_transid."' AND done_flag = 0 ";
						$resIgnore = parent::execQuery($sqlIgnore, $this->conn_default);
						if($this->trace)
						{
							echo '<br> sql :: '.$sqlIgnore;
							echo '<br> res :: '.$resIgnore;
							echo '<br> row :: '.parent::numRows($resIgnore);
						}
						if($resIgnore && parent::numRows($resIgnore)>0)
						{
							$rowIgnore = mysql_fetch_assoc($resIgnore);
							$updt_params = array();
							$updt_params['parentid'] 	= $parentid;
							$updt_params['data_city'] 	= $data_city;
							$updt_params['done_flag'] 	= 2;
							$updt_params['remarks'] 	= 'Duplicate Version - '.json_encode($rowIgnore);
							$this->updateDoneFlag($updt_params);
							
							
							$log_data_arr = array();
							$log_data_arr['info']	= 'Duplicate version';
							$log_data_arr['status'] = 'request received';
							$this->insertLog($log_data_arr);
							return $log_data_arr;
							exit;
						}
					}
					
					$urlinfo = $this->getURLInfo($data_city);
					
					if( strtolower($this->contract_source) == 'selfsignup' || strtolower($this->contract_source) == 'web_edit' || ( strtolower($this->contract_source) == 'clientselfsignup' && trim($user_code) ) )
					{
					   $campaign_data_old_frmat  = json_decode($campaigninfo,true);	
					   
					   if($this->trace)
						{
							echo '<pre>campaign info data arr:: ';
							print_r($campaign_data_old_frmat);
							//die;
						}
						
					   if(count($campaign_data_old_frmat)>0)
					   {
						   $campaign_data_new_frmat = array();
						   
						   foreach($campaign_data_old_frmat as $campaignid_key => $campaignid_data)
						   {
							   
							   if(trim($campaignid_data['keyword']) !='')
							   $combo_taken[] =  $campaignid_data['keyword'];
							   
							   if(trim($campaignid_data['bitval']) !='')
							   $combo_taken_bit[] =  $campaignid_data['bitval'];
							   
							   if( $campaignid_data['actcamp'] == '1' && strtolower(trim($campaignid_data['keyword'])) !='flexi_selected_user' )
								  $campaign_type = 'pure_pack';
							   
							   if( $campaignid_data['flexi_pincode_budget'] )
							   {
								  $campaign_type = 'pure_pack';
								  $combo_taken[] =  'flexi_pincode_budget';
								  $combo_taken_bit[] =  4294967296;
							   }
							   
							   if(!stristr($campaignid_data['actcamp'],',') && $campaignid_data['actcamp'] && !is_null($campaignid_data['actcamp']))
							   {
								    
						
						
								   $campaign_data_new_frmat[$campaignid_data['actcamp']] = $campaign_data_old_frmat[$campaignid_key];
								   
								   if($this->trace)
									{
										/*echo '<pre>campaign info old data arr key wise :: '.$campaignid_key;
											   print_r($campaign_data_old_frmat[$campaignid_key]);
									    echo '<pre>campaign info old data arr key wise :: '.$campaignid_data['actcamp'];
											   print_r($campaign_data_new_frmat[$campaignid_data['actcamp']]);*/
										//die;
									}
									
								   if($campaignid_data['actcamp'] == 4)
								   {
									   $sms_promo_details = $this->getSMSPromoDetails($parentid,$data_city,$urlinfo);
										if(count($sms_promo_details)>0)
										{
											$data_arr = json_decode($sms_promo_details['data'], 1);
											if($this->trace)
											{
												echo '<pre>sms promo row 145:: ';
												//print_r($sms_promo_details);
												print_r($data_arr);
											}
										}
										$campaign_data_new_frmat[$campaignid_data['actcamp']]['dailythreshold'] = $data_arr[$parentid]['daily_threshold'];
								    }
									
								   //$campaign_data_old_frmat[$campaignid_data['actcamp']]['passed_campaignid']  = $campaignid_key;
								   //unset($campaign_data_old_frmat[$campaignid_data['actcamp']]['actcamp']);
								   
								   //if($campaignid_data['actcamp'] != $campaignid_key)
								   //unset($campaign_data_old_frmat[$campaignid_key]);
								   
							   }
							   else if(stristr($campaignid_data['actcamp'],','))
							   {
								     $campaign_mul_camp_Arr = explode(',',$campaignid_data['actcamp']);
								     
								     if(in_array('13',$campaign_mul_camp_Arr) && in_array('5',$campaign_mul_camp_Arr) && in_array('22',$campaign_mul_camp_Arr))
								     {
										 $banner_campaign_ratio =  (4/$campaignid_data['budget']);
										 $jdrr_campaign_ratio =  1 - (8/$campaignid_data['budget']);
										 $campaign_data_old_frmat[22]['budget']   = $jdrr_campaign_ratio*$campaignid_data['budget'];
										 $campaign_data_old_frmat[22]['duration'] = $campaignid_data['duration'];
										 
										 $campaign_data_old_frmat[13]['budget']   = $banner_campaign_ratio*$campaignid_data['budget'];
										 $campaign_data_old_frmat[13]['duration'] = $campaignid_data['duration'];
										 
										 $campaign_data_old_frmat[5]['budget']    = $banner_campaign_ratio*$campaignid_data['budget'];
										 $campaign_data_old_frmat[5]['duration']  = $campaignid_data['duration'];
									 }
									 else  if(in_array('13',$campaign_mul_camp_Arr) && in_array('5',$campaign_mul_camp_Arr))
								     {
										 $banner_campaign_ratio =  0.5;
										 
										 $campaign_data_old_frmat[13]['budget']   = $banner_campaign_ratio*$campaignid_data['budget'];
										 $campaign_data_old_frmat[13]['duration'] = $campaignid_data['duration'];
										 
										 $campaign_data_old_frmat[5]['budget']    = $banner_campaign_ratio*$campaignid_data['budget'];
										 $campaign_data_old_frmat[5]['duration']  = $campaignid_data['duration'];
									 }
									 
									
										 unset($campaign_data_old_frmat[$campaignid_key]['actcamp']);
									 
									 if($campaignid_data['actcamp'] != $campaignid_key)
										 unset($campaign_data_old_frmat[$campaignid_key]);
							   }
						   }
						   
						   
						   $campaigninfo  = json_encode($campaign_data_new_frmat);	
						   
					   }else
					   {
						    $updt_params = array();
							$updt_params['parentid'] 	= $parentid;
							$updt_params['data_city'] 	= $data_city;
							$updt_params['done_flag'] 	= 2;
							$updt_params['remarks'] 	= 'campaigninfo data not found column';
							$this->updateDoneFlag($updt_params);
							
							
							$log_data_arr = array();
							$log_data_arr['info']	= 'Invalid JSON';
							$log_data_arr['status'] = 'request received';
							$this->insertLog($log_data_arr);
					   }
					   //echo 'frmt data <pre>';
					   //print_r($campaigninfo);
					   //die;
					}
					
					if($this->trace)
					{
						echo '<pre> final campaign arr:: ';
						//print_r($sms_promo_details);
						print_r($campaign_data_new_frmat);
					}
					
					$valid_json  	=	$this->isValidJSON($campaigninfo);
					
					$campaign_data  = json_decode($campaigninfo,true);
					$campids_arr	= array_keys($campaign_data);
					$campids_arr    = array_unique(array_filter($campids_arr));
					
					if( in_array(2,$campids_arr) )
						$campaign_type = '';
					
						
					
					
					
					$payment_arr   = json_decode($payment_info,true);
					$payment_arr    = array_unique(array_filter($payment_arr));
					
					
					if(count($payment_arr)>0)
					{
						$selPGMode		= trim($payment_arr['selPGMode']);
						$tax			= trim($payment_arr['tax']);
						$emi			= trim($payment_arr['emi']);
					}
					
					if( $tax > 0  && $amount_paid>0 )
					{
						
						$amount_paid_without_tax = $amount_paid / (1 + $tax );// formula changed 14-11-18
					}	
					
					$server_city 	= $this->getServerCity($data_city);
					
					if(count($campaign_data)>0){
							$log_data_arr = array();
							$log_data_arr['info']	= 'final budget data :: '.json_encode($campaign_data);
							$log_data_arr['status'] = 'final budget json';
						$this->insertLog($log_data_arr);
						
						$this -> upsell_manipulate_type = 1;//default manipulate type for upsell 
						
						foreach ($campaign_data as $campaign_id => $campaign_details)
						{
							if($campaign_details['is_dependent'] != '1')
							{
								$total_budget += $campaign_details['budget'];
								$max_duration  = max($campaign_details['duration'],$max_duration);
							}
						}
						
						$dealclose_details_arr_chk = $this->getContractDealClosedDetails();
						
						if($amount_paid_without_tax > $total_budget && $total_budget>0 && $dealclose_details_arr_chk['dealclose_details']['is_single_payment'] != 1)
						{
						  
						  $this -> upsell_manipulate_type = 3;//override default manipulate type for upsell to autoupsell
						  foreach ($campaign_data as $campaign_id => $campaign_details)
						  {
								if($campaign_details['budget']>0 && $campaign_details['is_dependent'] != '1')
								{
									/*if( $campaign_type == 'pure_pack' )
										$campaign_data[$campaign_id]['duration'] += round((($amount_paid_without_tax-$total_budget)*($campaign_details['budget']/$total_budget))/($campaign_details['budget']/$campaign_details['duration']));
									else
									{*/
										$campaign_data[$campaign_id]['budget'] = round($campaign_details['budget'] * ( $amount_paid_without_tax/$total_budget ));
										
										$campaign_data[$campaign_id]['upsl']   = ( $amount_paid_without_tax/$total_budget );
								}
						  }
						  
							$campaigninfo  = json_encode($campaign_data);	
						  
							$log_data_arr = array();
							$log_data_arr['info']	= 'final budget data-excess  :: '.json_encode($campaign_data);
							$log_data_arr['status'] = 'final budget json-excess';
							$this->insertLog($log_data_arr);
								
						}
						
						$campaign_index = 0;
						foreach ($campaign_data as $campaign_id => $campaign_details)
						{
							$finance_temp[$campaign_index]['campaignid'] 		 = $campaign_id;
							$finance_temp[$campaign_index]['budget']    		 = $campaign_details['budget'];
							$finance_temp[$campaign_index]['duration']  		 = $campaign_details['duration'];
							$finance_temp[$campaign_index]['recalculate_flag']   = 1;
							$campaign_index++;      
						}
						$dashboard_parametres['tbl_companymaster_finance_temp'] = $finance_temp;//for dashboard
						
						$tbl_payment_type = array();
						$combo_taken = array_unique($combo_taken);
						$tbl_payment_type['payment_type']      = implode(",",$combo_taken);
						$tbl_payment_type['payment_type_flag'] = (is_array($combo_taken_bit) && count($combo_taken_bit) >0 ) ? array_sum(array_unique($combo_taken_bit)) : 0;
						
					}
					if($this->trace)
					{
						echo '<pre>campaign info data:: ';
						print_r($campaign_data);
						//die;
					}
						
								
					if($valid_json == 1)
					{
						
						
						$urlinfo = $this->getURLInfo($data_city);
						
						
						
						$this-> ValidateCampaignData($data_city, $urlinfo, $parentid, $user_code, $campaign_type, $campids_arr, $user_name);
						
						if(trim($data_source) == 'clientselfsignup' && in_array(1,$campids_arr) )
						{
									
							if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
								$fetch_url  = "http://imteyazraja.jdsoftware.com/jdbox/services/fetchLiveData.php";
							}
							else{
								//http://192.168.22.103:800/services/mongoWrapper.php
								//action=getdata&post_data=1&parentid=PX755.X755.170630180732.C7A1&table=tbl_companymaster_extradetails_shadow&data_city=Bhopal&module=ME 
								$fetch_url  = $urlinfo['jdbox_url']."services/fetchLiveData.php";
							}
							$fetch_data			    	     = array();
							$fetch_data['parentid'] 	 	 = $parentid;
							$fetch_data['data_city'] 		 = $data_city;
							$fetch_data['ucode'] 			 = $user_code;
							$fetch_data['uname'] 			 = $user_name;
							$fetch_data['module']			 = ( strtolower(trim($module)) == 'tme' ) ? 'TME' : 'ME'; 
							$fetch_data['post_data']    	 = '1';
							
							$fetch_res_arr  = json_decode($this->curlCall($fetch_url,$fetch_data),true);
							
							if($this->trace)
							print_r($fetch_res_arr);
							
							
							if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
								$mongo_url  = "http://imteyazraja.jdsoftware.com/jdbox/services/mongoWrapper.php";
							}
							else{
								//http://192.168.22.103:800/services/mongoWrapper.php
								//action=getdata&post_data=1&parentid=PX755.X755.170630180732.C7A1&table=tbl_companymaster_extradetails_shadow&data_city=Bhopal&module=ME 
								$mongo_url  = $urlinfo['jdbox_url']."services/mongoWrapper.php";
							}
							$mongo_data			    	     = array();
							$mongo_data['action']	    	 = 'getdata';
							$mongo_data['post_data']    	 = '1';
							$mongo_data['parentid'] 	 	 = $parentid;
							$mongo_data['table']	         = 'tbl_companymaster_generalinfo_shadow';
							$mongo_data['data_city'] 		 = $data_city;
							$mongo_data['module']			 = ( strtolower(trim($module)) == 'tme' ) ? 'TME' : 'ME'; 
							
							$generalinfo_arr   = json_decode($this->curlCall($mongo_url,$mongo_data),true);
							if($this->trace)
							{
								echo '<hr> generalinfo data<pre>';
								echo json_encode(array( 'a_a_p' => $generalinfo_arr['pincode'], 
																					 'g_p_s' => '',
																					 'n_a_a_p' => $generalinfo_arr['pincode'],
																			  ));
								print_r($generalinfo_arr);
								//die('check mongo data');
							}
							
							//http://apoorva.jdsoftware.com/MEGENIO/me_services/areaPincodeInfo/setAreaPincodeInfo_new?urlFlag=1&parentid=PXX22.XX22.181031123924.F3Q5&data_city=Mumbai&pincodejson=\%22a_a_p\%22:\%22400064\%22,\%22n_a_a_p\%22:\%22400064\%22,\%22g_p_s\%22:\%22\%22}%22} 
							
							if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
								$prepare_data_url       = "http://imteyazraja.jdsoftware.com/megenio/me_services/areaPincodeInfo/setAreaPincodeInfo_new";
							}
							else{
								$prepare_data_url      =  GNO_URL."/me_services/areaPincodeInfo/setAreaPincodeInfo_new";
							}
							$prepare_data			    	 	= array();
							$prepare_data['parentid']    	 	= $parentid;
							$prepare_data['data_city']       	= $data_city;
							//$prepare_data['trace']		       	= 1;
							$prepare_data['whichCampaign']     	= 1;
							$prepare_data['selfsignup']     	= 1;
							$prepare_data['empcode']     		= $user_code;
							$prepare_data['server_city']     	= $server_city;
							$prepare_data['pincodeStr']			= $generalinfo_arr['pincode'];
							$prepare_data['pincodejson'] 	 	= json_encode(array( 'a_a_p' => $generalinfo_arr['pincode'], 
																					 'g_p_s' => '',
																					 'n_a_a_p' => $generalinfo_arr['pincode'],
																			  ));
							$prepare_data['urlFlag']  		 	= 1;
							$prepare_data_res 			   = json_decode($this->curlCall($prepare_data_url,$prepare_data),true);
							if($this->trace)
							{
								echo '<hr> prepare_data_url data<pre>';
								echo  '<br>'.$prepare_data_url;
								print_r($prepare_data);
								print_r($prepare_data_res);
								//die('check payment data');
							}
							
							//die;
							
						}
						
					/*populate companymaster data  - start*/
						/*api to get data from generalinfo,extradetails,business_temp_data */
					  if( !in_array(strtolower(trim($data_source)),array('clientselfsignup','web_edit')) )
					  {
						if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
								$mongo_url  = "http://imteyazraja.jdsoftware.com/jdbox/services/mongoWrapper.php";
							}
							else{
								//http://192.168.22.103:800/services/mongoWrapper.php
								//action=getdata&post_data=1&parentid=PX755.X755.170630180732.C7A1&table=tbl_companymaster_extradetails_shadow&data_city=Bhopal&module=ME 
								$mongo_url  = $urlinfo['jdbox_url']."services/mongoWrapper.php";
							}
							$mongo_data			    	     = array();
							$mongo_data['action']	    	 = 'getdata';
							$mongo_data['post_data']    	 = '1';
							$mongo_data['parentid'] 	 	 = $parentid;
							$mongo_data['table']	         = 'tbl_companymaster_generalinfo_shadow';
							$mongo_data['data_city'] 		 = $data_city;
							$mongo_data['module']			 = 'ME';//( strtolower(trim($module)) == 'tme' ) ? 'TME' : 'ME'; 
							
							$generalinfo_arr   = json_decode($this->curlCall($mongo_url,$mongo_data),true);
							$dashboard_parametres['tbl_companymaster_generalinfo_shadow'] = $generalinfo_arr;//for dashboard
							
							$mongo_data['table']	         = 'tbl_companymaster_extradetails_shadow';							
							$extradetails_arr  = json_decode($this->curlCall($mongo_url,$mongo_data),true);
							$dashboard_parametres['tbl_companymaster_extradetails_shadow'] = $extradetails_arr;//for dashboard
							
							$mongo_data['table']	         = 'tbl_business_temp_data';							
							$business_data_arr = json_decode($this->curlCall($mongo_url,$mongo_data),true);
							$dashboard_parametres['tbl_business_temp_data'] = $business_data_arr;//for dashboard
							
							$business_data_arr['catIds'] = '|P|'.trim($business_data_arr['catIds'], '|P|');//handling done to 
							
							if($this->trace)
							{
								echo '<pre>contract shadow data row :: ';
								print_r($generalinfo_arr);
								print_r($extradetails_arr);
								print_r($business_data_arr);
							}
							
							$catids_list=substr(str_replace('|P|',',',$business_data_arr['catIds']),2);
							
							
							$catids_arr = $this -> getCatidlineageArr($business_data_arr['catIds'],$catids_list);
							
						$log_data_arr = array();
						$log_data_arr['info']	= 'curl url :: '.$mongo_url.' geninfo data count :: '.count($generalinfo_arr).'  extra data count :: '.count($extradetails_arr).'  category count :: '.count($catids_arr).' category data :: '.implode(",",$catids_arr);
						$log_data_arr['status'] = 'check shadow data';
						$this->insertLog($log_data_arr);
							
						$attributes_arr = array("attributes" => "mainattr","attributes_edit" => "facility");
						
						
						if(count($generalinfo_arr)>0 && count($extradetails_arr)>0 && count($catids_arr)>0)
						{
							$geninf=array('sphinx_id','regionid','companyname','parentid','docid','country','state','city','display_city','area','area_display','subarea','office_no','building_name','street','street_direction','street_suffix','landmark','landmark_custom','pincode','pincode_addinfoo','latitude','longitude','latitude_actual','longitude_actual','geocode_accuracy_level','full_address','stdcode','landline','dialable_landline','landline_display','dialable_landline_display','landline_feedback','mobile','mobile_admin','dialable_mobile','mobile_display','dialable_mobile_display','mobile_feedback','mobile_feedback_nft','fax','tollfree','tollfree_display','email','email_display','email_feedback','sms_scode','website','contact_person','contact_person_display','callconnect','othercity_number','displayType','data_city','helpline','helpline_display') ;
							$ext_det=array('nationalid','sphinx_id','regionid','companyname','parentid','landline_addinfo','mobile_addinfo','tollfree_addinfo','contact_person_addinfo','working_time_start','working_time_end','payment_type','year_establishment','accreditations','certificates','no_employee','freeze','mask','data_city','catidlineage','catidlineage_search','national_catidlineage','national_catidlineage_search','original_date','createdby','createdtime','original_creator','attribute_search','attributes_edit','attributes','flags','map_pointer_flags','tag_line');
							$general_info_arr=array(); 
							$extra_info_arr=array();
							
							if(count($generalinfo_arr)>0)
							{
								foreach ($generalinfo_arr as $key => $value) {
										if(in_array(trim($key), $geninf)){
												$general_info_arr[$key]=$value;
										}
								}
							}
							
							if(count($extradetails_arr)>0)
							{
								foreach ($extradetails_arr as $key => $value) {
										if(in_array(trim($key), $ext_det)){
												$extra_info_arr[$key]=$value;
										}

								}
							}

							if(count($catids_arr)>0)
							{
								foreach ($catids_arr as $key => $value) {
										if(in_array(trim($key), $ext_det)){
												$extra_info_arr[$key]=$value;
										}

								}
							}
							
							if(count($attributes_arr)>0)
							{
								foreach ($attributes_arr as $key => $value) {
										if(in_array(trim($key), $ext_det)){
												$extra_info_arr[$key]=$business_data_arr[$value];
										}

								}
							}
							
							//check if building level geocode present
							
							/*passing user code to createdby key */
								$extra_info_arr['createdby'] = $user_code;
								$extra_info_arr['original_creator'] = $user_code;
								
							 

							$geoCheck = $this->checkGeoAcuuracy($parentid,$data_city);
							if($geoCheck['code'] == 0){
								if($geoCheck['data']['geocode_accuracy_level'] == 1){
									//to get map pointer flags
									$flagsRes = $this->getPointerFlags($parentid,$geoCheck['data']['latitude'],$geoCheck['data']['longitude'],$geoCheck['data']['geocode_accuracy_level'],$data_city,$urlinfo['jdbox_url']);
									$general_info_arr['geocode_accuracy_level'] = $geoCheck['data']['geocode_accuracy_level'];
									$general_info_arr['latitude'] 				= $geoCheck['data']['latitude'];
									$general_info_arr['longitude'] 				= $geoCheck['data']['longitude'];
									$extra_info_arr['flags'] 					= $flagsRes['result']['flags'];
									$extra_info_arr['map_pointer_flags'] 		= $flagsRes['result']['map_pointer_flags'];
								}else{
									// Geocode API
									$geodata_arr    				=    array();
									$geodata_arr['building_name']   =    $general_info_arr['building_name'];
									$geodata_arr['landmark']        =    $general_info_arr['landmark'];
									$geodata_arr['street']        	=    $general_info_arr['street'];
									$geodata_arr['latitude']        =    $general_info_arr['latitude'];
									$geodata_arr['longitude']       =    $general_info_arr['longitude'];
									$geodata_arr['module']        	=    'ME';
									$geodata_arr['rquest']        	=    'getGeocodeAccuracy';
									$geodata_arr['parentid']        =    $parentid;
									$geodata_arr['data_city']       =    $data_city;
									$geodata_arr['city']            =    $general_info_arr['city'];
									$geodata_arr['area']            =    $general_info_arr['area'];
									$geodata_arr['pincode']        	=    $general_info_arr['pincode'];
									$geodata_arr['state']           =    $general_info_arr['state'];
									
									$geocode_api_url       			=  $urlinfo['url']."api_services/api_geocode_accuracy.php";
									$geocode_api_res 				= json_decode($this->curlCall($geocode_api_url,http_build_query($geodata_arr)),true);
									
									if(strtolower($geocode_api_res['status']) == 'pass'){
										$geores_data 								= $geocode_api_res['data'];
										$general_info_arr['geocode_accuracy_level'] = $geores_data['geocode_accuracy_level'];
										$general_info_arr['latitude'] 				= $geores_data['latitude'];
										$general_info_arr['longitude'] 				= $geores_data['longitude'];
										$extra_info_arr['flags'] 					= $geores_data['flags'];
										$extra_info_arr['map_pointer_flags'] 		= $geores_data['map_pointer_flags'];
									}
								}
							}else{
								// Geocode API
									$geodata_arr    				=    array();
									$geodata_arr['building_name']   =    $general_info_arr['building_name'];
									$geodata_arr['landmark']        =    $general_info_arr['landmark'];
									$geodata_arr['street']        	=    $general_info_arr['street'];
									$geodata_arr['latitude']        =    $general_info_arr['latitude'];
									$geodata_arr['longitude']       =    $general_info_arr['longitude'];
									$geodata_arr['module']        	=    'ME';
									$geodata_arr['rquest']        	=    'getGeocodeAccuracy';
									$geodata_arr['parentid']        =    $parentid;
									$geodata_arr['data_city']       =    $data_city;
									$geodata_arr['city']            =    $general_info_arr['city'];
									$geodata_arr['area']            =    $general_info_arr['area'];
									$geodata_arr['pincode']        	=    $general_info_arr['pincode'];
									$geodata_arr['state']           =    $general_info_arr['state'];
									
									$geocode_api_url       			=  $urlinfo['url']."api_services/api_geocode_accuracy.php";
									$geocode_api_res 				= json_decode($this->curlCall($geocode_api_url,http_build_query($geodata_arr)),true);
									
									if(strtolower($geocode_api_res['status']) == 'pass'){
										$geores_data 								= $geocode_api_res['data'];
										$general_info_arr['geocode_accuracy_level'] = $geores_data['geocode_accuracy_level'];
										$general_info_arr['latitude'] 				= $geores_data['latitude'];
										$general_info_arr['longitude'] 				= $geores_data['longitude'];
										$extra_info_arr['flags'] 					= $geores_data['flags'];
										$extra_info_arr['map_pointer_flags'] 		= $geores_data['map_pointer_flags'];
									}
							}
							
							/*handling for vno*/
							//$general_info_arr['virtualNumber']         = 0;
							//$general_info_arr['virtual_mapped_number'] = 0;
							//unset($general_info_arr['blockforvirtual']);
							/*handling for vno*/
							
							$upt_fields['tbl_companymaster_generalinfo'] = $general_info_arr;
							$upt_fields['tbl_companymaster_extradetails']= $extra_info_arr;
							$tbl[]='tbl_companymaster_generalinfo';
							$tbl[]='tbl_companymaster_extradetails';
							$params_to_send['tbl']=json_encode($tbl);
							$params_to_send['fields']=json_encode($upt_fields);
							$params_to_send['action']=1;
							$params_to_send['module']='ME';
							$params_to_send['genio_lite_daemon'] = 1;
							$params_to_send['ucode']='selfsignup';
							$params_to_send['uname']='selfsignup';
							$params_to_send['source_ucode']=$user_code;
							$params_to_send['source_uname']=$user_name;
							$params_to_send['data_city']=$this->data_city;
							
							if( $data_source == 'selfsignup' && stristr(strtolower($user_name),'jduser') )
							{
								$params_to_send['manage_campaign'] = 1;
							}
							
							
							if($this->trace)
							{
								echo '<pre>all main data :: ';
								print_r($params_to_send);
							}
							

							if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
							{
									$curl_url       =  $urlinfo['jdbox_url']."services/saveCompanyData.php";
							}
							else
							{
									$curl_url       = "http://imteyazraja.jdsoftware.com/jdbox/services/saveCompanyData.php";
							}
							
							$resmsg = json_decode($this->curlCall($curl_url,http_build_query($params_to_send)),true);
							
							$log_data_arr = array();
							$log_data_arr['info']	= 'curl ulr :: '.$curl_url.' resp data :: '.json_encode($resmsg);
							$log_data_arr['status'] = 'save main data';
							$this->insertLog($log_data_arr);
							
							if($this->trace)
							{
								echo '<pre>url  :: '.$curl_url;
								echo '<pre>res data :: ';
								print_r($resmsg);
							}
						}else{
								$updt_params = array();
								$updt_params['parentid'] 	= $parentid;
								$updt_params['data_city'] 	= $data_city;
								$updt_params['done_flag'] 	= 2;
								$updt_params['remarks'] 	= 'Entry Not Found In Mongo g :: '.count($generalinfo_arr).' ex ::'.count($extradetails_arr).' c :: '.count($catids_arr);
								$this->updateDoneFlag($updt_params);
						}
						/*api to get data from generalinfo,extradetails,business_temp_data */	
					  }
					/*populate companymaster data  - end*/
							
						
						$contract_data = array();
						$contract_data = $this->findContractDetails($parentid,$data_city);
						
						$urlinfo = $this->getURLInfo($data_city);
						
						if($this->trace)
						{
							echo '<pre> row :: ';
							print_r($contract_data);
							print_r($urlinfo);
						}
					
						$log_data_arr = array();
						$log_data_arr['info']	= 'contract data count :: '.count($contract_data);
						$log_data_arr['status'] = 'check main data';
						$this->insertLog($log_data_arr);
						
						if(count($contract_data)>0)
						{
							
							$instrument_details = $this->getInstrumentDetails($parentid,$data_city,$passed_version);
							
							
							$dealclose_details_arr = $this->getContractDealClosedDetails();
							
							
							if(trim($data_source) == 'clientselfsignup')
							{
									$instrument_details['online'][] =  array('trans_id'	  		  => $trans_id,
																				 'instr_amt'		  => round($amount_paid), 
																				 'pay_card_type'      => $mode_of_payment, 
																				 'payu_trans_id'      => $mihpayid, 
																				 /*'card_bank'          => $pg_response_arr['bankcode'], 
																				 'udf4'		          => $pg_response_arr['trans_details'][$row_online_instrument['transaction_id']]['udf4'], */
																				 'pg_name'  		  => $selPGMode, 
																				 'remote_addr'        => '192.168.1.141' );
																				 
									$dealclose_details_arr['dealclose_details']['amount_paybale'] = $amount_paid;
									
							}
							
									
							if($this->trace)
							print_r($dealclose_details_arr);
							
							
							if($this->trace)
							{
								echo '<pre> payment rows :: ';
								print_r($instrument_details);
							}
							
							$this-> validateInstrumentData($data_city, $urlinfo, $parentid, $instrument_details, $dealclose_details_arr, $user_code, $user_name);
							
							$catlin_str	 = $contract_data['extradet']['catidlineage'];
							$catlin_arr	 = array();
							$catlin_arr  = explode("/,/",trim($catlin_str,"/"));
							$catlin_arr  = array_filter($catlin_arr);
							$catlin_arr	 = $this->getValidCategories($catlin_arr);
							$catinfo_arr = array();
							$catinfo_arr = $this->getCategoryDetails($catlin_arr,$data_city);
							$pincode	 = $contract_data['geninfo']['pincode'];
							

							if( $user_code && !stristr(strtolower($user_name),'jduser') && (count($instrument_details) >0  || ($dealclose_details_arr['dealclose_details']['is_single_payment'] == 2  && $dealclose_details_arr['dealclose_details']['apportioned_amount']>0)) )
							{
								
								
									/********** SAVE DATA FOR RONAK's Dashboard*****************/

										$jdaParams = array();
										$jdaParams['parentid']           = $parentid;
										$jdaParams['data_city']          = $data_city;
										$jdaParams['uname']              = $user_name;
										$jdaParams['ucode']              = $user_code;
										$jdaParams['package_type']       = 'paid';
										$jdaParams['skipReason']         = '';
										$jdaParams['data_tag']           = 'hot';
										$jdaParams['verification_status']= 1;
										$jdaParams['requestParam']       = 1;
										$jdaParams['mobileNoVerified']   = $this->verifiedMobileNum($parentid);

										
										if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
											$jdaurl	  = "http://imteyazraja.jdsoftware.com/megenio/jda_services/jdadealcloseservice.php";
										}
										else{
											//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
											$jdaurl      =  GNO_URL.'/jda_services/jdadealcloseservice.php';
										}
										$jdaResponse =  json_decode($this->curlCall($jdaurl,$jdaParams),true);
										
										if($this->trace)
										{
											echo '<br> url :: '.$jdaurl;
											print_r($jdaParams);
											print_r($jdaResponse);
										}
										
										$jdalogparams = array();
										$jdalogparams['parentid']    = $parentid;
										$jdalogparams['ucode']       = $user_code;
										$jdaParams['path']           = $jdaurl;
										$jdalogparams['url']         = json_encode($jdaParams);
										$jdalogparams['response']    = json_encode($jdaResponse);
										$jdalogparams['jda_flag']    = 3;
										
										$this-> jdaResponseLog($jdalogparams);

									/************************************/ 
									
									
									
									$mp_data			    	 = array();
									$mp_data['parentid']    	 = $parentid;
									$mp_data['order_id']  		 = $trans_id;
									$mp_data['data_city']        = $data_city;
									$mp_data['total_budget']     = $total_budget;
									$mp_data['duration']  		 = $max_duration;
									
									if(trim($data_source) != 'clientselfsignup')
									$mp_data['version']  		 = $passed_version;
									
									switch($payment_type)
									{
										case '1':
										 $mp_data['pay_type']= 'upfront';
										break;
										case '2':
										 $mp_data['pay_type']= 'ecs';
										break;
										case '3':
										 $mp_data['pay_type']= 'si';
										break;
										default:
										 $mp_data['pay_type']= 'upfront';
										break;
									}
									
									$mp_data['module'] 	 	= 'me';
									$mp_data['source'] 	 	= 'GL_SIGNUP'; 
									$mp_data['request_from']= 'gl'; 
									
									
									$mp_data['upsell_manipulate_type'] = $this -> upsell_manipulate_type;//passing manipulate type for upsell or autoupsell
									
									$genio_lite = 1; 
									
									$this-> genio_lite = 1;
									
									$mp_data['instruments']        = $instrument_details;
									
									if($campaigninfo)
									{
										$campaigninfo_with_dep = json_decode($campaigninfo,true);
										foreach($campaigninfo_with_dep as $campaignid_with_dep => $campaigndata_with_dep)
										{
											if($campaigndata_with_dep['is_dependent'] == '1')
											{
												unset($campaigninfo_with_dep[$campaignid_with_dep]);
											}
										}
										
										if(count($campaigninfo_with_dep)>0)
										$campaigninfo_without_dep  = json_encode($campaigninfo_with_dep);	
										
									}
									
									$mp_data['campaign_details']   = json_decode($campaigninfo_without_dep,true);
									
									if(in_array($dealCloseType,array(2,3)))
										$mp_data['dealCloseType']  	   = $dealCloseType;									
									else
										$mp_data['dealCloseType']  	   = 0;			
										
									
									$mp_data['renewal_type'] 	 = 0;//flag for case of  balance  djustment -  temporary for geniolite						
									
									if(strtolower(trim($module)) == 'tme')
									{
										$mp_data['emp_dtls']['tmecode']   = $user_code;
										$mp_data['emp_dtls']['mecode']    = $dealclose_details_arr['dealclose_details']['associated_tme_code'];	
									}
									else
									{
										$mp_data['emp_dtls']['mecode']    = $user_code;
										$mp_data['emp_dtls']['tmecode']   = $dealclose_details_arr['dealclose_details']['associated_tme_code'];
									}
									
									$mp_data['emp_dtls']['authority_details'] = urlencode(stripslashes(preg_replace('/[^(\x20-\x7F)]*/','', $generalinfo_arr['contact_person'])));
									
									$mp_data['emp_dtls']['gl_tme'] =  ( strtolower(trim($module)) == 'tme' ) ? 1 : 0 ;
									
									$mp_data['extra_details']['combo_type']	  	 = $tbl_payment_type['payment_type'];
									$mp_data['extra_details']['combo_type_flag'] = $tbl_payment_type['payment_type_flag'];
									
									if($dealclose_details_arr['dealclose_details']['tds_percent'] > 0)
									{
										$mp_data['extra_details']['tds_percentage']	 = $dealclose_details_arr['dealclose_details']['tds_percent'];
										
										$mp_data['emp_dtls']['pan_no']	  	 	 = $dealclose_details_arr['dealclose_details']['pan_number'];
										$mp_data['emp_dtls']['tan_no']	  	 	 = $dealclose_details_arr['dealclose_details']['tan_number'];
									}
																		
									$mp_data['extra_details']['bid_day_sel'] = 'MON,TUE,WED,THU,FRI,SAT,SUN,NHL';
									$mp_data['extra_details']['bid_timing']  = ($data_arr[$parentid]['bid_timming']) ? $data_arr[$parentid]['bid_timming'] : '00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59';
									
									if(trim($dealclose_details_arr['dealclose_details']['invoice_name']))
									$mp_data['extra_details']['invoice_business_name'] = $dealclose_details_arr['dealclose_details']['invoice_name'];
									
									if($this -> is_restricted_category_present)
										$mp_data['extra_details']['is_restricted'] = 1;
									
									
									if($dealCloseType == 2)
									{
										$mp_data['addnl_act']['bal_adj'] 		 = 1;
									}
									else
									{
										$mp_data['addnl_act']['bal_adj'] 		 = 0;
									}
										
									if($dealclose_details_arr['dealclose_details']['is_single_payment'] == 1)
									{
										if($this->trace)
										{
											echo '<br> app amt :: '.$dealclose_details_arr['dealclose_details']['apportioned_amount'];
											echo '<br> paid amt :: '.$amount_paid_without_tax;
										}
										
										$mp_data['addnl_act']['parent_per']    = round( (($dealclose_details_arr['dealclose_details']['apportioned_amount']/$amount_paid_without_tax) * 100), 4);										
										$mp_data['extra_details']['multicity'] = 1;
									} 
									else if($dealclose_details_arr['dealclose_details']['is_single_payment'] == 2) 
									{
										$mp_data['single_cheque_pay'] = array('source_city' => $dealclose_details_arr['dealclose_details']['parent_contract_city'],'remote_addr' => "192.168.1.141", 'instrument_amount' => $dealclose_details_arr['dealclose_details']['apportioned_amount'], 'source_parentid' =>$dealclose_details_arr['dealclose_details']['parent_contract']);
									}
									
									$mp_data['amount_payable'] 		   = $dealclose_details_arr['dealclose_details']['amount_paybale'];
									
									if($dealclose_details_arr['dealclose_details']['balance_adjusted']> 0 && $dealclose_details_arr['dealclose_details']['available_balance']>0 && $mp_data['campaign_details'][2]['duration'] > 3600 && $dealCloseType == 2)
									{
										$mp_data['addnl_act']['bal_adjust_flag'] = 1;
										$mp_data['total_adjusted_balance'] = $dealclose_details_arr['dealclose_details']['balance_adjusted'];
										$mp_data['bal_adjust_perc'] 	   = round(($dealclose_details_arr['dealclose_details']['balance_adjusted']/$dealclose_details_arr['dealclose_details']['available_balance'])*100); //putting round as we need to pass only 50 or 100
									}
									
									$curl_BalAdjust_data =  $mp_data;//putting data for renewal
									
									
									
								    if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
										//$mp_url	  = "http://ravindradaroge.jdsoftware.com/csgenio/services/gl_payment_service_api.php";
										$mp_url	  = "http://172.29.64.51:7799/DealClose";
									}
									else{
										//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
										
										$temp_block = 0;//temp flag
										if( $temp_block /*in_array(strtolower(trim($data_city)),array('ahmedabad'))*/ )
										{
											$mp_url	   = $urlinfo['node_fin_api_url']."DealClose";
											$post_data = array(); 
											$post_data  = json_encode($mp_data);
											$a = stripslashes($this->curlCallNew($mp_url,$post_data));
											$a = ltrim($a,'"');
											$a = rtrim($a,'"');
											$mp_resp = json_decode($a,true);
											
											
										}
										else
										{
											$mp_url	   = $urlinfo['url']."services/gl_payment_service_api.php";
											$post_data = array();
											$post_data['pay_req']  = json_encode($mp_data);
											$mp_resp               = json_decode($this->curlCall($mp_url,$post_data),true);

										}	
									}
									
									//print_r($b);				
									//$mp_resp 			   = json_decode($this->curlCallNew($mp_url,$post_data),true);				
									//$post_data['trace']    = 1;									
									//$mp_resp 			   = json_decode($this->curlCall($mp_url,$post_data),true);					
									//$mp_resp 			   = json_decode($this->curlCallNew($mp_url,$post_data),true);				
									//$post_data['trace']    = 1;									
									//$mp_resp 			   = json_decode($this->curlCall($mp_url,$post_data),true);
									if($this->trace)
									{
										echo '<hr> payment instrument data<pre>';
										echo  '<br>'.$mp_url;
										print_r($mp_data);
										print_r($mp_resp);
									}
									
									$log_data_arr = array();
									$log_data_arr['info']	= 'curl ulr :: '.$mp_url.' params '.json_encode($post_data).' resp data :: '.json_encode($mp_resp);
									$log_data_arr['status'] = 'save payment data';
									$this->insertLog($log_data_arr);
									

									
									if(count($mp_resp)>0 && strtolower(trim($mp_resp['STATUS'])) == 'success'  && in_array($mp_data['pay_type'], array('ecs','si')) )
									{
										if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
											$mandate_url       = "http://vivek.jdsoftware.com/megenio/me_services/campaignInfo/submit_mandate_shadow";
											//$mandate_url      =  GNO_URL."/me_services/campaignInfo/submit_mandate_shadow";
										}
										else{
											$mandate_url      =  GNO_URL."/me_services/campaignInfo/submit_mandate_shadow";
										}
										$mandate_data			    	 = array();
										$mandate_data['parentid']    	 = $parentid;
										$mandate_data['order_id']  		 = $trans_id;
										$mandate_data['data_city']       = $data_city;
										$mandate_data['mandate_type']    = $mp_data['pay_type'];
										$mandate_data['urlFlag']  		 = 1;
										$mandate_data['master_transaction_id'] = $this->pass_transid;
										$mandate_resp 			   = json_decode($this->curlCall($mandate_url,$mandate_data),true);
										if($this->trace)
										{
											echo '<hr> mandate data<pre>';
											echo  '<br>'.$mandate_url;
											print_r($mandate_data);
											print_r($mandate_resp);
											//die('check payment data');
										}
										
										$log_data_arr = array();
										$log_data_arr['info']	= 'curl ulr :: '.$mandate_url.' params '.json_encode($mandate_data).' resp data :: '.json_encode($mandate_resp);
										$log_data_arr['status'] = 'save mandate data';
										$this->insertLog($log_data_arr);
										
										if( !is_array($mandate_resp) || ( is_array($mandate_resp) && count($mandate_resp) <=0 ) || ( is_array($mandate_resp) && count($mandate_resp) > 0 && $mandate_resp['errorCode'] ) )
										{
											
											
											$message = $mandate_resp['errorMsg'].' - '.$parentid.'('.$data_city.')';;
											$this -> sendNotification($data_city, $parentid, $company_name, $user_codes = array('009882','10013675','10033558','10033590'), $message, $notification_alert = 1 , $mail_alert = 1);
											
										}
			
										
									}
									
									
									//$mp_data['instruments']['cash']
									
									/*
									 * array("parentid"=>"PXX22.XX22.180305104323.J3N8","order_id"=>"GE","data_city"=>"mumbai","server_city"=>"mumbai","total_budget"=>"27500","duration"=>"365","pay_type"=>"upfront","module"=>"me","source"=>"GL_SIGNUP",
"instruments"=>array(
"cash"=>array("0"=>array("instr_amt"=>"162487","deposit_location"=>"mumbai"),"1"=>array("instr_amt"=>"48024","deposit_location"=>"kolkata")),
"cheque"=>array("0"=>array("cheque_number"=>"021211","cheque_date"=>"2018-12-21","cheque_dep_loc"=>"MUMBAI","cheque_micr"=>"400109032","cheque_bank_city"=>"MUMBAI","cheque_bank_name"=>"THANE JANATA SAHAKARI BANK LTD","cheque_ifsc"=>"TJSB0000071","cheque_bank_branch"=>"AJAY MITTAL INDUSTRIAL PREMISES COOP SOCIETY LTD. CTS NO. 1637","cheque_acc_number"=>"071140300000011","cheque_acc_type"=>"1","instr_amt"=>"61145","cheque_image"=>"cheque/chequeImages/PXX22.XX22.140406195702.N7B7_1513842521.jpg"),"1"=>array("cheque_number"=>"567779","cheque_date"=>"2018-12-30","cheque_dep_loc"=>"MUMBAI","cheque_micr"=>"400048005","cheque_bank_city"=>"MUMBAI","cheque_bank_name"=>"DHANALAKSHMI BANK LTD","cheque_ifsc"=>"DLXB0000169","cheque_bank_branch"=>"HILL FORT","cheque_acc_number"=>"016905300007190","cheque_acc_type"=>"1","instr_amt"=>"47908","cheque_image"=>"cheque/chequeImages/PNE03343_1517420597.jpg"))),

"campaign_details"=>array("13"=>array("budget"=>"3800","duration"=>"365"),"5"=>array("duration"=>"185","budget"=>"6200"),"22"=>array("duration"=>"365","budget"=>"85200"),"1"=>array("duration"=>"180","budget"=>"2500")),"emp_dtls"=>array("mecode"=>"10012025","tmecode"=>"10013119","iro_code"=>"10022015","iro_name"=>"sanddep","pan_no"=>"asdsa3456t","tan_no"=>"qwer12345y"),"extra_details"=>array("bid_day_sel"=>"mon-fri","bid_timing"=>"8010","contract_form_no"=>"cf123456","offline_reason"=>"tme unavaialable","invoice_business_name"=>"Shah Core Cutting","invoice_person_name"=>"Ravindra d","invoice_mobile"=>"8524560000","tds_percentage"=>"2","multicity"=>"1","is_restricted"=>"1","exclusivelisting_tag"=>"0","smartlisting_flag"=>"0","regionid"=>"","gstn_no"=>"","bid_type"=>"1"),"addnl_act"=>array()))
									 * 
									 * */
									$mp_data['version']	         = $passed_version;
									$mp_data['campaign_details'] = $campaigninfo;
									
									
							}
							else if(stristr(strtolower($user_name),'jduser')  ||  $amount_paid>0  || strtolower(trim($data_source)) == 'web_edit' )
							{
									if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
										$mp_url	  = "http://ravindradaroge.jdsoftware.com/csgenio/services/payment_service_api.php";
									}
									else{
										//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
										$mp_url	   = $urlinfo['url']."services/payment_service_api.php";
									}
									
									
									$mp_data			    	 = array();
									$mp_data['parentid']    	 = $parentid;
									$mp_data['ucode'] 	    	 = 'selfsignup';
									
									if($data_source == 'selfsignup')
									{
										$mp_data['source'] 	 	     = 'self_signup'; 
										$mp_data['module'] 	 	     = 'me';
										
										$this -> manage_campaign     = 1;
									}
									else if($data_source == 'clientselfsignup')
									{
										$mp_data['source'] 	 	     = 'JDRR_SIGNUP';
										$mp_data['module'] 	 	     = 'cs';
									}
									else if($data_source == 'web_edit')
									{
										$mp_data['source'] 	 	     = 'self_signup';
										$mp_data['module'] 	 	     = 'cs';
									}		
									//$mp_data['source'] 	 	     = $data_source;
									
									$mp_data['data_city']        = $data_city;
									$mp_data['version']	         = $passed_version;
									$mp_data['campaign_details'] = $campaigninfo;
									
									$mapped_cityname_qry = "select dialer_mapped_cityname from tbl_city_master where data_city='".$data_city."'";
									$mapped_cityname_res = parent::execQuery($mapped_cityname_qry, $this->conn_default);
									if($mapped_cityname_res && mysql_num_rows($mapped_cityname_res)>0){
										$mapped_cityname_arr = mysql_fetch_assoc($mapped_cityname_res);
										$mp_data['origin_city'] = $mapped_cityname_arr['dialer_mapped_cityname'];
									}
									
									if($dealCloseType == 3)
									{
										$mp_data['instrument_amount']= 1;
										$mp_data['no_payment']= 1;
									}
									else
										$mp_data['instrument_amount']= $amount_paid;
										
									
									if(strtolower(trim($mode_of_payment)) == 'cash')
									{
										$mp_data['no_payment']= 1;//dirty fix till daroge gives new api
										$mp_data['instrument_amount']= ($amount_paid>0) ? $amount_paid :1 ;
									}
									
									
									$mp_data['total_budget']     = $total_budget;
									$mp_data['payuid']  		 = $trans_id.$this->dup_transid;
									$mp_data['order_id']  		 = $trans_id.$this->dup_transid;
									$mp_data['duration']  		 = $max_duration;
									$mp_data['mode']	  		 = $mode_of_payment;
									$mp_data['selPGMode']	  	 = $selPGMode;
									$mp_data['mihpayid']	  	 = $mihpayid.$this->dup_transid;
									$mp_data['bank_ref_num']  	 = $bank_ref_num;
									$mp_data['dealCloseType']  	 = $dealCloseType;
									$mp_data['tax']			  	 = $tax;
									$mp_data['emi']			  	 = $emi;
									$mp_data['combo_type']	  	 = $tbl_payment_type['payment_type'];
									$mp_data['combo_type_flag']	 = $tbl_payment_type['payment_type_flag'];
									
									
									$mp_data['renewal_type'] 	 = 0;//flag for case of balance adjustment
									
									switch($payment_type)
									{
										case '1':
										 $mp_data['payment_type']= 'upfront';
										break;
										case '2':
										 $mp_data['payment_type']= 'ecs';
										break;
										case '3':
										 $mp_data['payment_type']= 'si';
										break;
										default:
										 $mp_data['payment_type']= 'upfront';
										break;
									}
									
									if($this->trace)
									{
										echo '<pre> url :: '.$mp_url;
										print_r($mp_data);
									}
									
									if(in_array($dealCloseType,array(2,3)))
									{
										$curl_BalAdjust_data =  $mp_data;
									}
									
									$mp_resp 				= json_decode($this->curlCall($mp_url,$mp_data),true);
									if($this->trace)
									{
										print_r($mp_resp);
									}
									
									$log_data_arr = array();
									$log_data_arr['info']	= 'curl ulr :: '.$mp_url.' params '.json_encode($mp_data).' resp data :: '.json_encode($mp_resp);
									$log_data_arr['status'] = 'save payment data';
									$this->insertLog($log_data_arr);
							}	
						
							
							$conn_budget = $this->db[$server_city]['db_budgeting']['master'];
							
									
							if($this->trace)
							{
								echo '<pre> category data :: '.$pincode;
								print_r($catinfo_arr);
							}
							
							$log_data_arr = array();
							$log_data_arr['info']	= 'main categories :: '.json_encode($catinfo_arr).' pincode '.$pincode;
							$log_data_arr['status'] = 'check category data';
							$this->insertLog($log_data_arr);
							
							if((count($catinfo_arr)>0) && (intval($pincode)>0) && (count($mp_resp)>0 && strtolower(trim($mp_resp['STATUS'])) == 'success' ))
							{
								if(($this -> total_cash_amount)>0 && ($this -> total_cash_amount/1.18) > 199999 )
								{
									$from		  = "geniolitedaemon@justdial.com";
									$emailid 	  = "harish.bhatt@justdial.com,subhashrauthan@justdial.com";
									$email_id_cc = "rohitkaul@justdial.com, rajkumaryadav@justdial.com";
									$source      = "GENIO_LITE";
									$subject     = "ALERT : More than Rs.1,99,999 CASH INSTRUMENT is received !   ";
									$emailtext   = $parentid.'('.$data_city.')';
									$res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
									
								}
								
								if(count($campids_arr)>0){
									$version 	 = $mp_resp['VERSION'];
									$proceed_flag = 1;
									
									/*changes for deleting existing approved request for allowing multiple instruments - start*/
									
									if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
										$mp_url_multi_inst		     = "http://vishalvinodrana.jdsoftware.com/jdbox/services/chkMultiPaymtsEligibility.php";
									}
									else{
										//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
										$mp_url_multi_inst					     = $urlinfo['jdbox_url']."services/chkMultiPaymtsEligibility.php";
									}
									$mp_multi_inst_data	    	 		 = array();
									$mp_multi_inst_data['parentid']      = trim($parentid);
									$mp_multi_inst_data['data_city']     = strtolower(trim($data_city));
									$mp_multi_inst_data['version']       = $version;
									$mp_multi_inst_data['module']        = 'me';
									$mp_multi_inst_data['action']        = 'resetExistingRequest';
									
									$mp_multi_inst_resp			    	 = '';
									$mp_multi_inst_resp 				= json_decode($this->curlCall($mp_url_multi_inst,$mp_multi_inst_data),true);
									
									if($this->trace)
									{
										echo '<pre> reset ExistingMultiPayRequest url :: '.$mp_url_multi_inst;
										print_r($mp_multi_inst_resp);
										print_r($mp_multi_inst_data);
									}
									
									$log_data_arr = array();
									$log_data_arr['info']	= 'curl ulr :: '.$mp_url_multi_inst.' params '.json_encode($mp_multi_inst_data).' resp data :: '.json_encode($mp_multi_inst_resp);
									$log_data_arr['status'] = 'resetExistingMultiPayRequest';
									$this->insertLog($log_data_arr);
									
									/*changes for deleting existing approved request for allowing multiple instruments - end */
									
									
									/*if((in_array(5,$campids_arr)) && (in_array(3,$campids_arr) || in_array(4,$campids_arr))){ // With JDRR Plus , JDRR or Banner is not allowed as separate campaign. 
										$proceed_flag = 0;
									}*/
									
									//For dashboard
								    $json=array();
									$finance_tbl="select * from tbl_companymaster_finance where parentid='".$parentid."'";
									$finance_res = parent::execQuery($finance_tbl, $this->fin);
									if($finance_res && mysql_num_rows($finance_res)>0){

										while($financedata=mysql_fetch_assoc($finance_res)){
											 $json[] = $financedata;
										}
										$dashboard_parametres['tbl_companymaster_finance']=($json);
									}
									$json=array();
									$finance_tbl="select * from tbl_companymaster_finance_shadow where parentid='".$parentid."'";
									$finance_res = parent::execQuery($finance_tbl, $this->fin);
									if($finance_res && mysql_num_rows($finance_res)>0){
										while($financedata=mysql_fetch_assoc($finance_res)){
											 $json[] = $financedata;
										}
										$dashboard_parametres['tbl_companymaster_finance_shadow']=($json);
									}
									//For dashboard
									
									if($proceed_flag == 1){
										
										if( in_array(1,$campids_arr) )
										{
											if($passed_version)
											{
											
												if($campaign_type == 'pure_pack' && in_array(1,$campids_arr) && !in_array(2,$campids_arr) && !stristr(strtolower($user_name),'jduser') && strtolower($this->contract_source) != 'web_edit' )
												{
													$this->setPackBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$campaign_data,$user_code,$user_name,$curl_BalAdjust_data);
												}
												else if($campaign_type == 'flexi_pin_pack' && in_array(1,$campids_arr) && !in_array(2,$campids_arr) && !stristr(strtolower($user_name),'jduser') && strtolower($this->contract_source) != 'web_edit')
												{
														//echo '763'.$campaign_type;
													//die;
													$this->setFlexiPinBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$campaign_data,$user_code,$user_name);
												}
												else if( stristr(strtolower($user_name),'jduser')  ||  (!stristr(strtolower($user_name),'jduser') && !in_array(2,$campids_arr)) )
													$this->setBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$campaign_data,$curl_BalAdjust_data,$user_name);
											}
											else if ( strtolower($this->contract_source) == 'clientselfsignup' && trim($user_code) && $campaign_type == 'pure_pack' && in_array(1,$campids_arr) && !in_array(2,$campids_arr) )
											{
													$this->setPackBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$campaign_data,$user_code,$user_name,$curl_BalAdjust_data);
											}
											else
											{
												$this->setBiddingDetails($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$campaign_data,$contract_data,$server_city);
												$this->setIntoIntermediate($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city);
											}
										}
										else if( in_array(2,$campids_arr) && stristr(strtolower($user_name),'jduser') )
										{
											$this->setBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$campaign_data,$curl_BalAdjust_data,$user_name);
										}
										
										/*else if ( !in_array(1,$campids_arr) && !in_array(2,$campids_arr) )
										{
										       $this -> caryExstngBddngData($parentid, $version, $data_city, $user_code, $user_name);
										}*/
										
										if(in_array(5,$campids_arr) || in_array(13,$campids_arr)){
											
											$banner_data = array();
											$banner_data['parentid'] 	= $parentid;
											$banner_data['data_city'] 	= $data_city;
											$banner_data = $this->saveBannerBudget($catinfo_arr,$banner_data,$campaign_data);
											
											$dashboard_parametres['tbl_catspon_temp']     = $banner_data['catspon'];
											$dashboard_parametres['tbl_comp_banner_temp'] = $banner_data['comp'];
											
											$this->saveBannerSpecification($parentid,$data_city,$catinfo_arr);
										}
										
										if(in_array(22,$campids_arr))
										{
											if(trim($data_source) == 'clientselfsignup')
											{
												if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
													$mp_url_jdrr   = "http://arnoldmachado.jdsoftware.com/megenio/jdrr/api_orderid.php?orderid=".$trans_id."&parentid=".$parentid."&version=".$version."&data_city=".urlencode($data_city)."";
												}
												else{
													//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
													$mp_url_jdrr    = "http://genio.in/jdrr/api_orderid.php?orderid=".$trans_id."&parentid=".$parentid."&version=".$version."&data_city=".urlencode($data_city)."";
										
												}
												$mp_data			    	 = array();
												$mp_data['orderid']   		 = trim($trans_id);
												$mp_data['source']           = strtolower(trim($data_source));
												$mp_data['data_city']        = strtolower(trim($data_city));
												$mp_resp_jdrr			    	 = '';
												
												$mp_resp_jdrr 				= json_decode($this->curlCall($mp_url_jdrr,$mp_data),true);
												
												$log_data_arr = array();
												$log_data_arr['info']	= 'curl ulr :: '.$mp_url_jdrr.' params '.json_encode($mp_data).' resp data :: '.json_encode($mp_resp_jdrr);
												$log_data_arr['status'] = 'call jdrr api';
												$this->insertLog($log_data_arr);
												
												if($this->trace)
												{
													echo '<pre> jdrr api :: '.$mp_url_jdrr;
													print_r($mp_resp_jdrr);
													print_r($mp_data);
												}
											}
											else
											{
												$jdrr_data = array();
												$jdrr_data['parentid']	= $parentid; 
												$jdrr_data['data_city']	= $data_city; 
												$jdrr_data['budget'] 	= trim($campaign_data[22]['budget']);
												$jdrr_data['tenure'] 	= '365';
												$jdrr_data['version'] 	= $version;
												$this->saveJDRRBudget($contract_data,$jdrr_data);
											}
										}
										
										if(in_array(72,$campids_arr) || in_array(73,$campids_arr))
										{
											$this->updateOmniData($parentid, $version, $conn_iro, $conn_fin, $conn_idc, $conn_budget, $data_city, $campaigninfo);
										}
										
										if( ( in_array(10,$campids_arr) || ( $this -> manage_campaign && $this -> isActiveNationalListing($parentid,$data_city,$urlinfo)) ) && !in_array(strtolower(trim($data_source)),array('clientselfsignup','web_edit')) )
										{
											$this -> populateNationalListingData($generalinfo_arr, $extradetails_arr, $business_data_arr, $campaign_data);
										}
										
										if( in_array(4,$campids_arr) && !in_array(strtolower(trim($data_source)),array('clientselfsignup','web_edit')) )
										{
											$this -> populateSMSPROMODetails($parentid,$data_city,$urlinfo);
										}
										
										if( in_array(98,$campids_arr) && !in_array(strtolower(trim($data_source)),array('clientselfsignup','web_edit')) )
										{
											$this -> populateCrisilDetails($parentid,$data_city,$trans_id,$user_name,$version);
											
										}
										
										
									   if( ( ( in_array(1,$campids_arr) && $campaign_data[1]['duration'] < 1825 ) || ( in_array(2,$campids_arr) && $campaign_data[2]['duration'] < 1825 ) ||  stristr(strtolower($user_name),'jduser') ) && ($this -> isActivePhoneSearch($parentid,$data_city,$urlinfo,$version) || $this-> auto_renewal_adjustment) )
									   {	

											if(strtoupper(trim($curl_BalAdjust_data['source']))	== 'GL_SIGNUP' && is_array($curl_BalAdjust_data['campaign_details']) )
											{
												$curl_BalAdjust_data['campaign_details']   = json_encode($curl_BalAdjust_data['campaign_details']);
											}
									
											if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
												$mp_url_existing_balance_adjustment  = "http://vinaydesai.jdsoftware.com/csgenio_test/api_services/api_readjustment_process.php";
											}
											else{
												//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
												$mp_url_existing_balance_adjustment  = $urlinfo['url']."api_services/api_readjustment_process.php";
											}
											
											/*if($this-> auto_renewal_adjustment)
												$curl_BalAdjust_data['aprove_flag'] = 1;//called after entry in shadow 
											else 
											{
												$mp_url_existing_balance_adjustment_res = json_decode($this->curlCall($mp_url_existing_balance_adjustment,$curl_BalAdjust_data),true);
												if($this->trace)
												{
													echo '<pre> est bal adj 1:: '.$mp_url_existing_balance_adjustment;
													print_r($mp_url_existing_balance_adjustment_res);
												}
												
												$log_data_arr = array();
												$log_data_arr['info']	= 'curl url:: '.$mp_url_existing_balance_adjustment.' params '.json_encode($curl_BalAdjust_data).' resp data :: '.json_encode($mp_url_existing_balance_adjustment_res);
												$log_data_arr['status'] = 'auto readustment api 1';
												$this->insertLog($log_data_arr);
												
												$curl_BalAdjust_data['aprove_flag'] = 1;//called after entry in shadow 
												
											}*/
											
											$mp_url_existing_balance_adjustment_res = json_decode($this->curlCall($mp_url_existing_balance_adjustment,$curl_BalAdjust_data),true);
											
											if($this->trace)
											{
												echo '<pre> est bal adj 2:: '.$mp_url_existing_balance_adjustment;
												print_r($mp_url_existing_balance_adjustment_res);
											}
											
											$log_data_arr = array();
											$log_data_arr['info']	= 'curl url :: '.$mp_url_existing_balance_adjustment.' params '.json_encode($curl_BalAdjust_data).' resp data :: '.json_encode($mp_url_existing_balance_adjustment_res);
											$log_data_arr['status'] = 'auto readustment api 2';
											$this->insertLog($log_data_arr);
											
											if($mp_url_existing_balance_adjustment_res['results']['ERRCODE'] == 0 && strtolower(trim($mp_url_existing_balance_adjustment_res['results']['STATUS'])) == 'success')
											{
												
											}else{
													$existing_payment_error_log_params = array();
													$existing_payment_error_log_params['parentid'] 		= $parentid;
													$existing_payment_error_log_params['data_city']	    = $data_city;
													$existing_payment_error_log_params['done_flag'] 	= 2;
													$existing_payment_error_log_params['remarks'] 		= 'Err in exst bal adjst';
													$this->updateDoneFlag($existing_payment_error_log_params);
											}
											
												
										}
										
										if( ( $mihpayid!= '' && !in_array(strtolower(trim($mode_of_payment)),array('cash','cheque','offlinecreditcard','neft')) && !$genio_lite ) || ( $genio_lite && strtolower($mp_resp['STATUS']) == 'success' &&  count($mp_resp['INSTRUMENTID']['online']) > 0 ) )
										{
											$instrument_ids = array();
											if(count($mp_resp['INSTRUMENTID']['online']) > 0 && $genio_lite)
											{
												$instrument_ids = array_values( $mp_resp['INSTRUMENTID']['online'] );
											}
											else
											{
												$instrument_ids[] = trim($mp_resp['INSTRUMENTID']);
											}
											
											foreach($instrument_ids as $online_instrument_id)
											{
												if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
													$mp_url					     = "http://ravindradaroge.jdsoftware.com/jdbox/finance/self_signup_auto_approval.php";
												}
												else{
													//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
													$mp_url					     = $urlinfo['jdbox_url']."finance/self_signup_auto_approval.php";
												}
												$mp_data			    	 = array();
												$mp_data['instrument_id']    = trim($online_instrument_id);
												$mp_data['data_city']        = strtolower(trim($data_city));
												$mp_resp			    	 = '';
												
												$mp_resp 				= json_decode($this->curlCall($mp_url,$mp_data),true);
												
												if($this->trace)
												{
													echo '<pre> category data :: '.$mp_url;
													print_r($mp_resp);
													print_r($mp_data);
												}
												
												$log_data_arr = array();
												$log_data_arr['info']	= 'curl ulr :: '.$mp_url.' params '.json_encode($mp_data).' resp data :: '.json_encode($mp_resp);
												$log_data_arr['status'] = 'auto approval api';
												$this->insertLog($log_data_arr);
											}
										}
										
										
										// data updation for dashboard start 

											$paramsdashboard['parentid']    = $parentid;
											$paramsdashboard['version']     = $version;
											$paramsdashboard['data_city']   = $data_city;
											$paramsdashboard['action']      = 'updatecontactinfo';
											$paramsdashboard['module']      = (stristr($user_name,'jduser')) ? 'manage_campaign' : 'me_lite';
											//$paramsdashboard['trace']       = 1;
											$paramsdashboard['ucode']       = ($user_code) ? $user_code: 'me_lite';
											$paramsdashboard['uname']       = ($user_name) ? $user_name: 'me_lite';

											$tablearray = array('tbl_companymaster_generalinfo_shadow','tbl_companymaster_extradetails_shadow','tbl_business_temp_data','tbl_temp_intermediate','tbl_catspon_temp','tbl_comp_banner_temp','tbl_companymaster_finance','tbl_companymaster_finance_shadow','tbl_companymaster_finance_temp','tbl_companymaster_finance_national','tbl_companymaster_finance_national_shadow','tbl_smsbid_temp','tbl_national_listing_temp');

											$contract_info_details_array= array();
											foreach($tablearray as $tablename)
											{
													$contract_info_details_array[$tablename] = $dashboard_parametres[$tablename];
											}

											$contract_info_details_array['tbl_payment_type']        =       $tbl_payment_type;
											$contract_info_details_array['session'] = $dashboard_parametres['session'];
											$contract_info_details_array['SERVER'] = $_SERVER;
											$contract_info_details_json = json_encode($contract_info_details_array, JSON_FORCE_OBJECT);
											$paramsdashboard['contract_info_details'] = $contract_info_details_json;

											$mapped_cityname_qry = "select mapped_cityname from tbl_city_master where data_city='".$data_city."'";
											$mapped_cityname_res = parent::execQuery($mapped_cityname_qry, $this->conn_default);
											if($mapped_cityname_res && mysql_num_rows($mapped_cityname_res)>0){
												$mapped_cityname_arr = mysql_fetch_assoc($mapped_cityname_res);
												$paramsdashboard['mapped_cityname'] = $mapped_cityname_arr['mapped_cityname'];
											}

											if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) )
												$dashboardapiurl = 'http://imteyazraja.jdsoftware.com/jdbox/services/dealclosedashboard.php';
											else
												$dashboardapiurl = $urlinfo['jdbox_url'].'services/dealclosedashboard.php';
												
												$dashboardrespstring 				= json_decode($this->curlCall($dashboardapiurl,$paramsdashboard),true);
												
												$log_data_arr = array();
												$log_data_arr['info']	= 'curl ulr :: '.$dashboardapiurl.' resp data :: '.json_encode($dashboardrespstring);
												$log_data_arr['status'] = 'call dashboard api api';
												$this->insertLog($log_data_arr);
												
									// data updation for dashboard end 
										
										/*	logging data for notification module - start */
									
											if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
												$mp_url_notfn					     = "http://ravindradaroge.jdsoftware.com/jdbox/finance/dc_notification_api.php";
											}
											else{
												//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
												$mp_url_notfn					     = $urlinfo['jdbox_url']."finance/dc_notification_api.php";
											}
											$mp_notfn_data			   = array();
											$mp_notfn_data['parentid'] = $parentid; 
											$mp_notfn_data['version']  = $version;
											$mp_notfn_data['data_city']= strtolower(trim($data_city));
											
											$mp_main_data['data']  		= json_encode($mp_notfn_data);
											$mp_resp_notfn 				= json_decode($this->curlCall($mp_url_notfn,$mp_main_data),true);
											
											if($this->trace)
											{
												echo '<pre> category data :: '.$mp_url_notfn;
												print_r($mp_resp_notfn);
												print_r($mp_notfn_data);
											}
											
											$log_data_arr = array();
											$log_data_arr['info']	= 'curl ulr :: '.$mp_url_notfn.' params '.json_encode($mp_notfn_data).' resp data :: '.json_encode($mp_resp_notfn);
											$log_data_arr['status'] = 'notification api';
											$this->insertLog($log_data_arr);
											
									/*	logging data for notification module -  end */
												
													
										if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
											$send_invoice = "http://saritapc.jdsoftware.com/jdbox/services/generateinvoicecontent.php";
										}
										else{
											$send_invoice =  $urlinfo['jdbox_url']."services/generateinvoicecontent.php";
										}
										$send_invoice_data = array();
										$send_invoice_data['action']   = '5';
										$send_invoice_data['parentid'] = $parentid; 
										$send_invoice_data['version']  = $version;
										$send_invoice_data['module']   = 'me';
										$send_invoice_data['data_city']= $data_city;
										$send_invoice_data['usrcd']	   = $user_code;
										$send_invoice_data['invDate']  = date('Y-m-d H:i:s');
										$send_invoice_data['source']   = 'GENIOLITE';
										
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL,$send_invoice);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch,CURLOPT_POST, TRUE);
										curl_setopt($ch,CURLOPT_POSTFIELDS,$send_invoice_data);
										$transferstring = curl_exec($ch);
										curl_close($ch);
												
										/*if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
											$url= "http://imteyazraja.jdsoftware.com/jdbox/services/log_generate_invoice_content.php";
										}
										else{
											$url=  $urlinfo['jdbox_url']."services/log_generate_invoice_content.php";
										}
										$post_data = array();
										$post_data['action']   =1;
										$post_data['parentid'] =$parentid; 
										$post_data['version']  =$version;
										$post_data['module']   ='cs';
										$post_data['data_city']=$data_city;
										$post_data['user_web']=1;

										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL,$url);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch,CURLOPT_POST, TRUE);
										curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
										$transferstring = curl_exec($ch);
										curl_close($ch);
										
										
										
										$log_data_arr = array();
										$log_data_arr['info']	= 'curl ulr :: '.$url.' params '.json_encode($post_data).' resp data :: '.json_encode($transferstring);
										$log_data_arr['status'] = 'insert invoice api';
										$this->insertLog($log_data_arr);*/
										
										
										$transferstringArr = json_decode($transferstring,1);
										
										if($this->trace)
										{
											echo '<pre> invoice data :: '.$send_invoice;
											print_r($send_invoice_data);
											print_r($transferstringArr);
										}
										
										
										//calling saritha's api 
										if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
											$omni_agrm_url= "http://saritapc.jdsoftware.com/jdbox/services/omni_agreement.php";
										}
										else{
											$omni_agrm_url=  $urlinfo['jdbox_url']."services/omni_agreement.php";
										}
										$omni_post_data = array();
										$omni_post_data['action']   = 4;
										$omni_post_data['parentid'] = $parentid; 
										$omni_post_data['version']  = $version;
										$omni_post_data['module']   = 'me';
										$omni_post_data['data_city']= $data_city;
										$omni_post_data['source']	= 'dealclose_geniolite_tc';
										$omni_post_data['usercode']	= $user_code;
										if( $server_city == 'remote' )
											$omni_post_data['remote'] = 1;
										else
											$omni_post_data['remote'] = 0;
											
											
										$res_omni_agrm_url 				= json_decode($this->curlCall($omni_agrm_url,$omni_post_data),true);
										
										
										
										$log_data_arr = array();
										$log_data_arr['info']	= 'curl ulr :: '.$omni_agrm_url.' params '.json_encode($omni_post_data).' resp data :: '.json_encode($res_omni_agrm_url['error']);
										$log_data_arr['status'] = 'omni agrmt api';
										$this->insertLog($log_data_arr);
										
										
										
											
											
											
										$log_data_arr = array();
										$log_data_arr['info']	= 'curl ulr :: '.$send_invoice.' params '.json_encode($send_invoice_data).' resp data :: '.json_encode($transferstringArr);
										$log_data_arr['status'] = 'send invoice api';
										$this->insertLog($log_data_arr);
										
										if($transferstringArr['errorCode'] == 0 && strtolower($transferstringArr['data']['message']) == 'success' )
										{
											
												
											
											if($user_code && $genio_lite)
											{
												
												if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
													$notification_url      =  GNO_URL."/geniolite_dash/sendNoti.php?userid=".$user_code."&pid=".$parentid."";//?topic=009882&msg=hello"
												}
												else{
													//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
													$notification_url      =  GNO_URL."/geniolite_dash/sendNoti.php?userid=".$user_code."&pid=".$parentid."";//?topic=009882&msg=hello"
												}
												
												$notification_data['topic']  = $user_code;
												$notification_data['msg']    = 'Your deal for '.ucwords(stripslashes($row_data['companyname']))."(".$data_city.")".' is successfully processed';
												$mp_resp_notification		 = $this->curlCall($notification_url,$notification_data);
												
												
												$notif_log_data_arr = array();
												$notif_log_data_arr['info']	  = $mp_resp_notification;
												$notif_log_data_arr['status'] = 'Notfication Sent';
												$this->insertLog($notif_log_data_arr);
												
												if($this->trace)
												{
													echo '<pre> notification data url :: '.$notification_url;
													print_r($notification_data);
													echo $mp_resp_notification;
												}
														
											}
												
											$updt_params = array();
											$updt_params['parentid'] 	= $parentid;
											$updt_params['data_city'] 	= $data_city;
											$updt_params['done_flag'] 	= 1;
											$updt_params['remarks'] 	= 'receipt data logged';
											$this->updateDoneFlag($updt_params);
										}
										else
										{
											 $from		  = "geniolitedaemon@justdial.com";
											 $emailid 	  = "ronak.joshi@justdial.com,itu.behera@justdial.com";
											 $email_id_cc = "rohitkaul@justdial.com, rajkumaryadav@justdial.com";//
											 $source      = "GENIO_LITE";
											 $subject     = "GENIO LITE Daemon Error - Invoice Not Generated";
											 $emailtext   = " Contract Id :       ".$this->parentid."
															  <br> Data City :      ".$this->data_city."
															  <br><br> API URL :      ".$send_invoice."
															  <br><br> Passed Params:      ".json_encode($post_data)."
															  <br><br> API Resp :      ".$transferstring."";
											 $res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
											 
											 if($this->trace)
											{
												echo '<br> Email send resp :: '.$res_email;
												//echo json_encode($budget_curl_res);
											}
											
											$updt_params = array();
											$updt_params['parentid'] 	= $parentid;
											$updt_params['data_city'] 	= $data_city;
											$updt_params['done_flag'] 	= 2;
											$updt_params['remarks'] 	= 'Invoice data not logged';
											$this->updateDoneFlag($updt_params);
										}
										
										$result_msg_arr['error']['code'] = 0;
										$result_msg_arr['error']['msg'] = 'Success'; 
										$result_msg_arr['data']['version'] = $version; 
										echo json_encode($result_msg_arr);
																
										
										
										
									}else{
										//updatedoneflag
										//insertlog
									}
								}
							}
							else
							{
								$updt_params = array();
								$updt_params['parentid'] 	= $parentid;
								$updt_params['data_city'] 	= $data_city;
								$updt_params['done_flag'] 	= 2;
								if(count($mp_resp) <= 0 || ( count($mp_resp)>0 && strtolower(trim($mp_resp['STATUS'])) == 'error' ) )
								{
									$updt_params['remarks'] 	= addslashes((is_array($mp_resp['MESSAGE']) ? json_encode($mp_resp['MESSAGE']) :$mp_resp['MESSAGE']));
									
									$subject      = "GENIO LITE Daemon Error - Payment API Failed";
									$emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com,ravindra.daroge@justdial.com";
									$source_curl  = $mp_url;
									$postarr      = $mp_data;
									$source_msg   = $updt_params['remarks'];
									
								}
								else
								{
									$updt_params['remarks'] 	= 'Issue Found in contract category or pincode';
									$subject      = "GENIO LITE Daemon Error - Invalid Mongo Data";
									$emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com,imteyaz.raja@justdial.com";
									$source_curl  = $mongo_url;
									$postarr      = '';
									$source_msg   = 'Category count :: '.count($catinfo_arr)." Pincode :: ".$pincode;
								}
									
									if($this -> manage_campaign)
									{
										$emailid 	  .= ",sumesh.dubey@justdial.com";
									}
								 
								 
								 $from		  = "geniolitedaemon@justdial.com";
								 $email_id_cc = "rohitkaul@justdial.com, rajkumaryadav@justdial.com";//
								 $source      = "GENIO_LITE";
								 $emailtext   = " Contract Id :       ".$this->parentid."
												  <br> Data City :      ".$this->data_city."
												  <br><br> API URL :      ".$source_curl."
												  <br><br> Passed Params:      ".json_encode($postarr)."
												  <br><br> API Error :      ".$source_msg."";
								 
								if($this -> manage_campaign || $this-> genio_lite)
									$res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
									
								$this->updateDoneFlag($updt_params);
							}
						}
						else
						{
							
							$updt_params = array();
							$updt_params['parentid'] 	= $parentid;
							$updt_params['data_city'] 	= $data_city;
							$updt_params['done_flag'] 	= 2;
							$updt_params['remarks'] 	= 'Contract Details Not Found';
							$this->updateDoneFlag($updt_params);
						}
						
						
					}
					else
					{
						$updt_params = array();
						$updt_params['parentid'] 	= $parentid;
						$updt_params['data_city'] 	= $data_city;
						$updt_params['done_flag'] 	= 2;
						$updt_params['remarks'] 	= 'Invalid JSON in campaigninfo column';
						$this->updateDoneFlag($updt_params);
						
						
						$log_data_arr = array();
						$log_data_arr['info']	= 'Invalid JSON';
						$log_data_arr['status'] = 'request received';
						$this->insertLog($log_data_arr);
						
					}
					exit;
					
				}
			}
			else
			{
				
						$log_data_arr = array();
						$log_data_arr['info']	= 'Invalid JSON';
						$log_data_arr['status'] = 'request received';
						$this->insertLog($log_data_arr);
				/*$updt_params = array();
				$updt_params['parentid'] 	= $parentid;
				$updt_params['data_city'] 	= $data_city;
				$updt_params['done_flag'] 	= 2;
				$updt_params['remarks'] 	= 'Entry Not Found In Self Sign UP';
				$this->updateDoneFlag($updt_params);*/
			}
		return "Success";
		
	}
	
	private function saveBannerBudget($catinfo_arr,$banner_data,$campaign_data){
		
		 
		if($this->trace)
		{
			echo "<br>inside saveBannerBudget";
			echo "<br>catinfo_arr";print_r($catinfo_arr);
			echo "<br>banner_data";print_r($banner_data);
			echo "<br>campaign_data";print_r($campaign_data);
			echo "<br>trace--".$this->trace	;
		}
		
		$parentid	= $banner_data['parentid'];
		$data_city	= $banner_data['data_city'];
		
		
		$server_city = $this->getServerCity($data_city);
		$conn_local  = $this->db[$server_city]['d_jds']['master'];
		$conn_finance  	= $this->db[$server_city]['fin']['master'];
		
		$banner_data= array();
		
		$catids_arr = array();
		
		
		$sql_banner_details = "SELECT  bannerType, selectedCities,catid_parentage,catId_mp_list FROM banner_payment_rotation_temp WHERE  parentid = '".$parentid."' AND active_flag = 1 /*AND bannerType = 1*/";
		$res_banner_details = parent::execQuery($sql_banner_details, $this->conn_idc);
		if($res_banner_details && parent::numRows($res_banner_details)>0){
		  $row_banner_details = parent::fetchData($res_banner_details);
		}
		
		if($this->trace)
		{
			echo '<br> sql_banner_details -- '.$sql_banner_details;
			echo "<br>row_banner_details--"; print_r($row_banner_details);		
		}
		
		# we will give preference to catId_mp_list of banner_payment_rotation_temp , if data is not present then only we will consider catidlineage category
		if($row_banner_details['catId_mp_list']!=null && strlen($row_banner_details['catId_mp_list'])>2)
		{		
			$catlin_str	 = $row_banner_details['catId_mp_list'];
			$catlin_arr	 = array();
			$catId_mp_list_arr  = explode(",",$row_banner_details['catId_mp_list']);
			$catId_mp_list_arr  = array_filter($catId_mp_list_arr);
			$catlin_arr	 = $this->getValidCategories($catId_mp_list_arr);
			$catinfo_arr = array();
			$catinfo_arr = $this->getCategoryDetails($catlin_arr,$data_city);			
			
			if($this->trace)
			{				
				echo "<br>catId_mp_list".$row_banner_details['catId_mp_list'];
				echo "<br>catId_mp_list_arr";print_r($catId_mp_list_arr);
				echo "<br>catlin_arr";print_r($catlin_arr);
				echo "<br>catinfo_arr";print_r($catinfo_arr);
			}
		}
		
		
		$catids_arr	= array_keys($catinfo_arr);
		
		$urlinfo = $this->getURLInfo($data_city);
		
		$mp_url 				= $urlinfo['url']."api/multiparentage_check.php";
		$mp_data				= array();
		$mp_data['parentid'] 	= $parentid;
		$mp_data['ucode'] 	 	= 'selfsignup';
		$mp_data['module'] 	 	= 'genio';
		$mp_data['action'] 	 	= 'check_multiparentage';
		$mp_data['catid_list'] 	= implode(",",$catids_arr);
		$mp_resp 				= json_decode($this->curlCall($mp_url,$mp_data),true);
		$parentage 				= $mp_resp['parentage'];
		
		
		
		$available_cat = $this->bannerInventoryCheck($catids_arr,$data_city);
		
		if($this->trace)
		{
			print"<pre>catids_arr";print_r($catids_arr);
			print"<pre>available_cat";print_r($available_cat);
		}
		
		if(count($available_cat)>0){
			//print_r($available_cat);
				//$catspon_mul=0.5;
			$sql_insert_values = "";
			foreach($available_cat as $catid => $avail_inv){
				
				if($avail_inv >= 1 && $catinfo_arr[$catid]['is_allow'] && ($catinfo_arr[$catid]['p_client']>0 || $catinfo_arr[$catid]['np_client']>0))
				{
					if( ($row_banner_details['bannerType'] == 1 && $catinfo_arr[$catid]['is_national'] == 1) || $row_banner_details['bannerType'] != 1)
					{
					
						$this->catspon_categories[] = $catid;
						$avail_catspon_category[$catid]['catname'] = $catinfo_arr[$catid]['catname'];
						$avail_catspon_category[$catid]['natcat']  = $catinfo_arr[$catid]['natcat'];
						
						$total_cat_count++;
						$sql_insert_values .= "('".$parentid."', 'BUDGET','VARBDGET', '".date('Y-m-d H:i:s')."', '".$catinfo_arr[$catid]['catname']."', '".$catid."', '".$campaign_data[13]['duration']."','BIDPERDAY','catspon', '1', '1', '1', '".$catinfo_arr[$catid]['natcat']."', '".addslashes($row_banner_details['selectedCities'])."', '".addslashes($row_banner_details['catid_parentage'])."'),";
					}
				}				
				
				//if()
				// tbl_comp_banner
				//tbl_catspon
			}
			
			if($sql_insert_values)
			{
				$sql_insert_values = str_replace('BUDGET',($campaign_data[13]['budget']/$total_cat_count),$sql_insert_values);
				$sql_insert_values = str_replace('VARBDGET',($campaign_data[13]['budget']/$total_cat_count),$sql_insert_values);					
				
				$sql_insert_values = str_replace('BIDPERDAY',(($campaign_data[13]['budget']/$total_cat_count)/$campaign_data[13]['duration']),$sql_insert_values);
				
				$banner_data['catspon']['category_details'] = $avail_catspon_category;
				$banner_data['catspon']['category_counts']  = $total_cat_count;
				$banner_data['catspon']['budget']           = $campaign_data[13]['budget'];
				$banner_data['catspon']['tenure']           = $campaign_data[13]['duration'];
				
				$sql_del = "DELETE FROM tbl_catspon WHERE parentid='".$parentid."'";
				//$res_del = parent::execQuery($sql_del, $conn_local);
				$res_del = parent::execQuery($sql_del, $this->conn_idc);
				
				$sql_ins = "INSERT INTO tbl_catspon(parentid, budget,variable_budget, update_date, cat_name, catid, tenure, bid_per_day, campaign_name, campaign_type, iscalculated, banner_camp, national_catid, selectedCities,parentname) VALUES ".trim($sql_insert_values,",");
				//$res_ins = parent::execQuery($sql_ins, $conn_local);
				$res_ins = parent::execQuery($sql_ins, $this->conn_idc);
				
				if($this->trace)
				{
					echo '<br> sql_del -- '.$sql_del;
					echo '<br> sql_ins -- '.$sql_ins;					
				}
				
				$log_data_arr = array();
				$log_data_arr['info']	= 'tbl_catspon query res :: '.$res_ins;
				$log_data_arr['status'] = 'cat spon data';
				$this->insertLog($log_data_arr);
				
				
				
			}
		}
		
		if(count($catids_arr)>0)
		{
			 $total_cat_count =0;
			 foreach($catids_arr as $catid){
					if( $catinfo_arr[$catid]['is_allow'] && ($catinfo_arr[$catid]['p_client']>0 || $catinfo_arr[$catid]['np_client']>0) )
					{
						if( ($row_banner_details['bannerType'] == 1 && $catinfo_arr[$catid]['is_national'] == 1) || $row_banner_details['bannerType'] != 1)
						{
							$total_cat_count++;
							$sql_insert_values_comp .= "('".$parentid."', 'BUDGET','VARBDGET', '".date('Y-m-d H:i:s')."', '".$catinfo_arr[$catid]['catname']."', '".$catid."', '".$campaign_data[5]['duration']."','BIDPERDAY','cat_banner', '4', '1', '1', '".$catinfo_arr[$catid]['natcat']."', '".addslashes($row_banner_details['selectedCities'])."', '".addslashes($row_banner_details['catid_parentage'])."'),";
						}
					}
				}
			
				if($sql_insert_values_comp)
				{
					$sql_insert_values_comp = str_replace('BUDGET',($campaign_data[5]['budget']/$total_cat_count),$sql_insert_values_comp);
					$sql_insert_values_comp = str_replace('VARBDGET',($campaign_data[5]['budget']/$total_cat_count),$sql_insert_values_comp);
										
					$sql_insert_values_comp = str_replace('BIDPERDAY',(($campaign_data[5]['budget']/$total_cat_count)/$campaign_data[5]['duration']),$sql_insert_values_comp);
					
					
					$banner_data['comp']['category_details'] = $catinfo_arr;
					$banner_data['comp']['category_counts']  = $total_cat_count;
					$banner_data['comp']['budget']           = $campaign_data[5]['budget'];
					$banner_data['comp']['tenure']           = $campaign_data[5]['duration'];
					
					$sql_del = "DELETE FROM tbl_comp_banner WHERE parentid='".$parentid."'";
					//$res_del = parent::execQuery($sql_del, $conn_local);
					$res_del = parent::execQuery($sql_del, $this->conn_idc);
					
					$sql_ins = "INSERT INTO tbl_comp_banner(parentid, budget,variable_budget, update_date, cat_name, catid, tenure, bid_per_day, campaign_name, campaign_type, iscalculated, banner_camp, national_catid, selectedCities,parentname) VALUES ".trim($sql_insert_values_comp,",");
					//$res_ins = parent::execQuery($sql_ins, $conn_local);
					$res_ins = parent::execQuery($sql_ins, $this->conn_idc);
					
					
					if($this->trace)
					{
						echo '<br> sql_del -- '.$sql_del;
						echo '<br> sql_ins -- '.$sql_ins;
					}

					$log_data_arr = array();
					$log_data_arr['info']	= 'tbl_comp_banner query res :: '.$res_ins;
					$log_data_arr['status'] = 'comp banner data';
					$this->insertLog($log_data_arr);
					
				}
				
				//if()
				// tbl_comp_banner
				//tbl_catspon
			}
		
		
			return $banner_data;
		
		
	}
	
	function setBiddingDetails($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$budget_details,$contract_data,$server_city){
			
			//print_r($budget_details);di e;
			
			
			$data['total_budget'] = $budget_details[1]['budget'];
			if(count($contract_data['geninfo']))
			{
				$gi_arr= $contract_data['geninfo'];			
			}	
			if(count($contract_data['extradet']))
			{
				$cat_arr_ext= $contract_data['extradet'];			
			}

			if(count($contract_data['idgen']))
			{
				$id_gen_arr= $contract_data['idgen'];
			}	

			$contact_details = $gi_arr['landline'].",".$gi_arr['mobile'];
			$contact_details_array = explode(',',$contact_details);
			$contact_details_array = array_filter($contact_details_array);

			$contact_details_str='';
			if(count($contact_details_array))
			{
				$contact_details_str = implode(',',$contact_details_array);
			}
			$categories=$cat_arr_ext['catidlineage'].",".$cat_arr_ext['catidlineage_nonpaid'];
			$categories=str_replace("/",'', $categories);
			$categories=rtrim($categories,",");
			$categories=ltrim($categories,",");
			$categories=explode(',', $categories);
			$categories=array_unique($categories);
			$categories=implode(',',$categories);
			
			$category_list_str=$categories;
			
			$this->conn_idc = $this->db[strtolower($server_city)]['idc']['master'];
			$sql_idc = "SELECT GROUP_CONCAT(pincode) AS pincode_lists FROM tbl_camp_pincode_info_lite  WHERE parentid='".$id_gen_arr['parentid']."'";
			$res_idc = parent::execQuery($sql_idc, $this->conn_idc);
			if($res_idc && mysql_num_rows($res_idc))
			{
				$row_idc = mysql_fetch_assoc($res_idc);
				
				if($row_idc['pincode_lists'])
					$pincode_list_str=$row_idc['pincode_lists'];
				else
					$pincode_list_str=$gi_arr['pincode'];
			}
			else
			{
				$pincode_list_str=$gi_arr['pincode'];
			}
			

			$update_sql= "INSERT INTO tbl_bidding_details_summary set
							sphinx_id		='".$id_gen_arr['sphinx_id']."',
							parentid		='".$id_gen_arr['parentid']."',
							docid			='".$id_gen_arr['docid']."',
							companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
							data_city		='".$id_gen_arr['data_city']."',
							pincode			='".$gi_arr['pincode']."',
							latitude		='".$gi_arr['latitude']."',
							longitude		='".$gi_arr['longitude']."',
							version			='".$version."',
							module			='self_signup',
							sys_package_budget='".$data['total_budget']."',
							sys_total_budget='".$data['total_budget']."',
							actual_package_budget='".$data['total_budget']."',
							actual_total_budget='".$data['total_budget']."',
							contact_details	='".$contact_details_str."',
							category_list	='".$category_list_str."',
							dealclosed_flag	=0,
							pincode_list	='".$pincode_list_str."',
							updatedon			='".date('Y-m-d H:i:s')."',
							duration			='365',
							username			='selfsignup',
							updatedby			='selfsignup' 
							ON DUPLICATE KEY UPDATE
							sphinx_id		='".$id_gen_arr['sphinx_id']."',						
							docid			='".$id_gen_arr['docid']."',
							companyname		='".addslashes(stripcslashes($gi_arr['companyname']))."',
							data_city		='".$id_gen_arr['data_city']."',
							pincode			='".$gi_arr['pincode']."',
							latitude		='".$gi_arr['latitude']."',
							longitude		='".$gi_arr['longitude']."',
							module			='self_signup',
							sys_package_budget='".$data['total_budget']."',
							sys_total_budget='".$data['total_budget']."',
							actual_package_budget='".$data['total_budget']."',
							actual_total_budget='".$data['total_budget']."',
							contact_details	='".$contact_details_str."',
							category_list	='".$category_list_str."',
							dealclosed_flag	=0,
							pincode_list	='".$pincode_list_str."',
							updatedon			='".date('Y-m-d H:i:s')."',
							duration			='365',
							username			='selfsignup',
							updatedby			='selfsignup'
							";

						//$res=$conn_budget->query_sql($update_sql); 
						//die;
						$res_ins = parent::execQuery($update_sql, $conn_budget);
						
						if($this->trace)
								{
									echo '<pre> update_sql data :: '.$update_sql;
									print_r($res_ins);
								}
								
			$log_data_arr = array();
			$log_data_arr['info']	= 'tbl_bidding_details_summary query res :: '.$res_ins;
			$log_data_arr['status'] = 'bidding summary data';
			$this->insertLog($log_data_arr);

					
		}

		function setIntoIntermediate($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city){ 
			
			$urlinfo = $this->getURLInfo($data_city);
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){

			$url="http://imteyazraja.jdsoftware.com/jdbox/services/budgetDetails.php?data_city=".$data_city."&tenure=12&parentid=".$parentid."&mode=3&option=1&ver=".$version;
			$urldealclose="http://imteyazraja.jdsoftware.com/jdbox/services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			else{
				$url  = $urlinfo['jdbox_url']."services/budgetDetails.php?data_city=".$data_city."&tenure=12&parentid=".$parentid."&mode=3&option=1&ver=".$version;
				$urldealclose  =  $urlinfo['jdbox_url']."services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			
			$mp_data1['data_city']  = $data_city;
			$mp_data1['tenure'] 	= 12;
			$mp_data1['parentid'] 	= $parentid;
			$mp_data1['mode']  		= 3;
			$mp_data1['option'] 	= 1;
			$mp_data1['ver']  		= $version;
			
			$res_best_camp_for_pack = json_decode($this->curlCall($url,$mp_data1),true);
			 //print_r($res_best_camp_for_pack);
			 
			$log_data_arr = array();
			$log_data_arr['info']	= 'curl ulr :: '.$url;
			$log_data_arr['status'] = 'get budget data';
			$this->insertLog($log_data_arr);
			 
			if($this->trace)
			{
				echo '<pre> url :: '.$url;
				print_r($mp_data1);
				print_r($res_best_camp_for_pack);
			}
			 
			//$res_best_camp_for_pack=curlCallNew($url); 
			
			$budgetjson= $this->dobudgetjson($res_best_camp_for_pack,$data,$version,$conn_budget,$parentid); 
			
			if($this->trace)
			{
				echo '<pre> budget  :: '.$url;
				print_r($res_best_camp_for_pack);
			}
			
			
			/*if(!$budgetjson)
				apiresult(0,'Intermediate Failure',$conn_iro,$logid); */
			
			//$deal_close=curlCallNew($urldealclose);  
			$mp_data2['data_city']  = $data_city;
			$mp_data2['parentid'] 	= $parentid;
			$mp_data2['astatus'] 	= 1;
			$mp_data2['astate'] 	= 1;
			$mp_data2['version']    = $version;
			$deal_close = json_decode($this->curlCall($urldealclose,$mp_data2),true);
			
			$deal_close_arr=json_decode($deal_close,1);
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'curl ulr :: '.$urldealclose.' params :: '.json_encode($mp_data2).' res :: '.json_encode($deal_close);
			$log_data_arr['status'] = 'get budget data';
			$this->insertLog($log_data_arr);
			
			if($this->trace)
			{
				echo '<pre> url :: '.$urldealclose;
				print_r($mp_data2);
				print_r($deal_close_arr);
			}
			
			
			if($deal_close_arr['error']['code']==0){
				return true;
			}
			else
				return false;

		}
		
		function setPackBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$budget_arr,$user_code,$user_name,$curl_BalAdjust_data)
		{ 
			
			$urlinfo	  = $this->getURLInfo($data_city);
			$server_city  = $this->getServerCity($data_city);
			
			$postarr['parentid']              = $parentid;
			$postarr['data_city']             = $data_city;
			$postarr['server_city']           = $server_city;
			$postarr['version']               = $version;
			$postarr['module']                = 'me';
			$postarr['empcode']               = $user_code;
			$postarr['username']              = $user_name;
			$postarr['tabNo']                 = 3;
			$postarr['optNo']                 = 1;
			$postarr['non_flexi']             = 1;
			$postarr['flexiVal']              = $budget_arr[1]['budget'];
			$postarr['tenure']	              = (max($budget_arr[1]['duration'],$budget_arr[2]['duration'])/365)*12;;
			$postarr['urlFlag']	              = 1;
			$postarr['genio_lite_daemon']    = 1;
			
			// echo '<pre>';print_r($postarr);exit;echo JDBOX_SERVICES_API;exit;
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$budget_curl  = "http://apoorva.jdsoftware.com/megenio/me_services/campaignInfo/getBestCampaignInfo";
			}
			else{
				$budget_curl  = "192.168.20.17/me_services/campaignInfo/getBestCampaignInfo";
			}
			
			$budget_curl_res = json_decode($this->curlCall($budget_curl,$postarr),true);
			
			
			if($this->trace)
			{
				echo '<pre> Before budget change package url :: '.$budget_curl;
				print_r($postarr);
				print_r($budget_curl_res);
				//echo json_encode($budget_curl_res);
			}
			
			if( count($budget_curl_res) <= 0 || ( count($budget_curl_res) > 0 && $budget_curl_res['error']['code'] == '1' ) ) 
			{
				 $from		  = "geniolitedaemon@justdial.com";
				 $emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com";
				 $email_id_cc = "rohitkaul@justdial.com, rajkumaryadav@justdial.com";//
				 $source      = "GENIO_LITE - ".strtoupper($postarr['module'])."";
				 $subject     = "GENIO LITE Daemon Error - GetBestCampaignInfo API Failed";
				 $emailtext   = " Contract Id :       ".$this->parentid."
								  <br> Data City :      ".$this->data_city."
								  <br><br> API URL :      ".$budget_curl."
								  <br><br> Passed Params:      ".json_encode($postarr)."
								  <br><br> API Error :      ".json_encode($budget_curl_res['error']['msg'])."
								  <br><br> API Resp :      ".json_encode($budget_curl_res)."";
				 $res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
				 
				 if($this->trace)
				{
					echo '<br> Email send resp :: '.$res_email;
					//echo json_encode($budget_curl_res);
				}

			}
			
			$best_arr=$budget_curl_res;
			$sub_arr=array();
			$inner_arr=array();
			$totalpackagebudget=0;
			$ins_arr=array();
			$nonpaid_catid_arr=array();
			$ins_str='';
			
			$best_arr['result']['packageBudget'] = $budget_arr[1]['budget'];
			$best_arr['result']['pdgBudget'] 	 = $budget_arr[2]['budget'];
			$best_arr['result']['tenure'] 	     = (max($budget_arr[1]['duration'],$budget_arr[2]['duration'])/365)*12;
			$best_arr['result']['totBudget'] 	 = ($budget_arr[1]['budget'] + $budget_arr[2]['budget']);
			
			foreach ($best_arr['result']['c_data'] as $key => $value) { 
				
				//if($value['flexi_bgt']>0)
					$best_arr['result']['c_data'][$key]['c_bgt'] = $value['flexi_bgt'];
				/*else
					$nonpaid_catid_arr[] = $key;*/
					
				foreach ($value['pin_data'] as $pinkey => $pinvalue) {
					
					foreach($pinvalue['pos'] as $positionval =>$positionarr)
					{
						if($positionval != 100)
						unset($best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['pos'][$positionval]);
					}
					
					$best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['pos'][100]['budget']   = $best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['flexi_bgt'];
					//$best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['pos'][100]['bidvalue'] = 
					$best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['pos'][100]['inventory']= $best_arr['result']['c_data'][$key]['pin_data'][$pinkey]['pos'][100]['inv_avail'];
				
				}
				
			}
			
			$best_arr['result']['removeCatStr']  = implode(',',$nonpaid_catid_arr);
			$best_arr['result']['nonpaidStr'] 	 = implode(',',$nonpaid_catid_arr);
			
			if($this->trace)
			{
				echo '<pre> after budget change';
				print_r($best_arr['result']);
			}
			
			
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$budget_submit_url = "http://imteyazraja.jdsoftware.com/jdbox/services/budgetsubmit.php"; 
			}
			else{
				$budget_submit_url  =  $urlinfo['jdbox_url']."services/budgetsubmit.php?parentid=".$parentid.""; 
			}
			$budget_submit['action'] 	= 'submitbudget';
			$budget_submit['parentid'] 	= $parentid;
			$budget_submit['module'] 	= 'me';
			$budget_submit['version']   = $version;
			$budget_submit['usercode'] 	= $user_code;
			$budget_submit['data_city'] = $data_city;
			$budget_submit['genio_lite_daemon']    = 1;
			$budget_submit['budgetjson']= json_encode($best_arr['result']);// Above data array
			$budget_submit['duration']  = round((max($budget_arr[1]['duration'],$budget_arr[2]['duration'])/365)*12);
			$budget_submit['package_10dp_2yr']    = $budget_arr[1]['budget'];//send 0 
			
			$budget_submit_res = json_decode($this->curlCall($budget_submit_url,$budget_submit),true);
			
			if($this->trace)
			{
				echo '<pre> budget submit url :: '.$budget_submit_url;
				print_r($budget_submit);
				print_r($budget_submit_res);
				//echo json_encode($budget_curl_res);
			}
			
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$budget_submit_url.' params :: '.json_encode($budget_submit).' res :: '.json_encode($budget_submit_res);
			$log_data_arr['status'] = 'update budget';
			$this->insertLog($log_data_arr);
			
			
			//$budget_arr = $this -> preApprovalAdjustment($parentid, $data_city, $urlinfo, $budget_arr, $curl_BalAdjust_data, $user_name);//existing readjustment
			
			
			$budget_submit_actual['parentid']              = $parentid;
			$budget_submit_actual['data_city']             = $data_city;
			$budget_submit_actual['version']               = $version;
			$budget_submit_actual['module']                = 'me';
			$budget_submit_actual['action']                = 'updateActualBudget';
			$budget_submit_actual['genio_lite_daemon']     = 1;
			$budget_submit_actual['actual_fp_budget']      = $budget_arr[2]['budget'];
			$budget_submit_actual['actual_package_budget'] = $budget_arr[1]['budget'];
			$budget_submit_actual['actual_regfee_budget']  = $budget_arr[7]['budget'];
			$budget_submit_actual['actual_total_budget']   = ($budget_arr[1]['budget']+$budget_arr[2]['budget']);
			$budget_submit_actual['duration']              = max($budget_arr[1]['duration'],$budget_arr[2]['duration']);
			$budget_submit_actual['usercode']              = $user_code;
			
			// echo '<pre>';print_r($postarr);exit;echo JDBOX_SERVICES_API;exit;
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$budget_submit_actual_curl  = "http://imteyazraja.jdsoftware.com/jdbox/services/budgetsubmit.php";
			}
			else{
				$budget_submit_actual_curl  = $urlinfo['jdbox_url']."services/budgetsubmit.php";
			}
			$budget_submit_actual_curl_res = json_decode($this->curlCall($budget_submit_actual_curl,$budget_submit_actual),true);
			
			if($this->trace)
			{
				echo '<pre> budget submit actual url :: '.$budget_submit_actual_curl;
				print_r($budget_submit_actual);
				print_r($budget_submit_actual_curl_res);
				//echo json_encode($budget_curl_res);
			}
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$budget_submit_actual_curl.' params :: '.json_encode($budget_submit_actual).' res :: '.json_encode($budget_submit_actual_curl_res);
			$log_data_arr['status'] = 'update act budget';
			$this->insertLog($log_data_arr);
			
		
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$urldealclose="http://imteyazraja.jdsoftware.com/jdbox/services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			else{
				$urldealclose  =  $urlinfo['jdbox_url']."services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			
			$mp_data2['data_city']  = $data_city;
			$mp_data2['parentid'] 	= $parentid;
			$mp_data2['astatus'] 	= 1;
			$mp_data2['astate'] 	= 1;
			$mp_data2['version']    = $version;
			$deal_close = json_decode($this->curlCall($urldealclose,$mp_data2),true);
			
			if( ( is_array($deal_close['results']['fail']) && count($deal_close['results']['fail'])>0 ) || ( isset($deal_close['error']['code']) && ($deal_close['error']['code'])!=0 ) )
			 {
					 $from		  = "geniolitedaemon@justdial.com";
					 $emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com";
					 $email_id_cc = " rajkumaryadav@justdial.com ";//
					 $source      = "GENIO_LITE - online";
					 $subject     = "GENIO LITE Daemon Error - invMgmt API Failed";
					 $emailtext   = " Contract Id :       ".$this->parentid."
									  <br> Data City :      ".$this->data_city."
									  <br><br> API URL :      ".$urldealclose."
									  <br><br> Passed Params:      ".json_encode($mp_data2)."
									  <br><br> API Error :      ".json_encode($deal_close['error']['msg'])."";
									  
					 if( !preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) )				  
						$res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
					 
					 
					$updt_params = array();
					$updt_params['parentid'] 	= $parentid;
					$updt_params['data_city'] 	= $data_city;
					$updt_params['done_flag'] 	= 2;
					$updt_params['remarks'] 	= $deal_close['error']['msg'];
					$this->updateDoneFlag($updt_params);
					
					$message = 'Your deal for - '.$parentid.'('.$data_city.') is failed !';
					$user_codes = array('009882','10013675','10033558');
					array_push($user_codes, $user_code);
					$this -> sendNotification($data_city, $parentid, $company_name, $user_codes, $message, $notification_alert = 1 , $mail_alert = 0);
					exit;
					
					 
					 if($this->trace)
					{
						echo '<br> Email send resp :: '.$res_email;
						//echo json_encode($budget_curl_res);
					}
			 }
			 
			
			$deal_close_arr=json_decode($deal_close,1);
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$urldealclose.' params :: '.json_encode($mp_data2).' res :: '.json_encode($deal_close);
			$log_data_arr['status'] = 'get budget data';
			$this->insertLog($log_data_arr);
			
			if($this->trace)
			{
				echo '<pre> url :: '.$urldealclose;
				print_r($mp_data2);
				print_r($deal_close);
			}
			
			if($deal_close_arr['error']['code']==0){
				return true;
			}
			else{
				$updt_params = array();
				$updt_params['parentid'] 	= $parentid;
				$updt_params['data_city'] 	= $data_city;
				$updt_params['done_flag'] 	= 2;
				$updt_params['remarks'] 	= 'bidding data not updated';
				$this->updateDoneFlag($updt_params);
				return false;
			}
			
		}
		
		function setFlexiPinBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$budget_arr,$user_code,$user_name)
		{ 
			$urlinfo = $this->getURLInfo($data_city);
			
			$postarr['data_city']             = $data_city;
			$postarr['parentid']              = $parentid;
			$postarr['action']                = 'packbudgetcalbypin';
			$postarr['version']               = $version;
			$postarr['module']                = 'me';
			$postarr['source']                = 'me';
			$postarr['usercode']              = $user_code;
			$postarr['username']              = $user_name;
			
			// echo '<pre>';print_r($postarr);exit;echo JDBOX_SERVICES_API;exit;
			
			//http://prameshjha.jdsoftware.com/jdbox/services/packagepincatupdt.php?data_city=mumbai&parentid=p1000691&action=packbudgetcalbypin&username=admin&usercode=10000&source=me&version=53&module=me
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$budget_curl  = "http://imteyazraja.jdsoftware.com/jdbox/services/packagepincatupdt.php";
			}
			else{
				$budget_curl  = $urlinfo['jdbox_url']."services/packagepincatupdt.php";
			}
			$budget_curl_res = json_decode($this->curlCall($budget_curl,$postarr),true);
			
			if($this->trace)
			{
				echo '<pre> url :: '.$budget_curl;
				print_r($postarr);
				print_r($budget_curl_res);
			}
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$budget_curl.' params :: '.json_encode($postarr).' res :: '.json_encode($budget_curl_res);
			$log_data_arr['status'] = 'update act budget';
			$this->insertLog($log_data_arr);
			
			if( count($budget_curl_res)>0 && $budget_curl_res['error']['code'] == 0 && strtolower($budget_curl_res['error']['msg']) == 'successful' )
			{
				
				if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
					$urldealclose="http://imteyazraja.jdsoftware.com/jdbox/services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
				}
				else{
					$urldealclose  =  $urlinfo['jdbox_url']."services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
				}
				
				$mp_data2['data_city']  = $data_city;
				$mp_data2['parentid'] 	= $parentid;
				$mp_data2['astatus'] 	= 1;
				$mp_data2['astate'] 	= 1;
				$mp_data2['version']    = $version;
				$deal_close = json_decode($this->curlCall($urldealclose,$mp_data2),true);
				
				$deal_close_arr=json_decode($deal_close,1);
				
				$log_data_arr = array();
				$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$urldealclose.' params :: '.json_encode($mp_data2).' res :: '.json_encode($deal_close);
				$log_data_arr['status'] = 'get budget data';
				$this->insertLog($log_data_arr);
				
				if($this->trace)
				{
					echo '<pre> url :: '.$urldealclose;
					print_r($mp_data2);
					print_r($deal_close);
				}
				
				if($deal_close_arr['error']['code']==0){
					return true;
				}
				else{
					$updt_params = array();
					$updt_params['parentid'] 	= $parentid;
					$updt_params['data_city'] 	= $data_city;
					$updt_params['done_flag'] 	= 2;
					$updt_params['remarks'] 	= 'bidding data not updated';
					$this->updateDoneFlag($updt_params);
					return false;
				}
			}
			else
			{
					$updt_params = array();
					$updt_params['parentid'] 	= $parentid;
					$updt_params['data_city'] 	= $data_city;
					$updt_params['done_flag'] 	= 2;
					$updt_params['remarks'] 	= 'Flexi Budget Not Calculated';
					$this->updateDoneFlag($updt_params);
					return false;
			}
			
			
			
		}
		
		
		function setBiddingData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$logid,$data_city,$budget_arr,$curl_BalAdjust_data,$user_name){ 
			
			$urlinfo = $this->getURLInfo($data_city);
			
			//$budget_arr = $this -> preApprovalAdjustment($parentid, $data_city, $urlinfo, $budget_arr, $curl_BalAdjust_data, $user_name);//existing readjustment
			
			$postarr['parentid']              = $parentid;
			$postarr['data_city']             = $data_city;
			$postarr['version']               = $version;
			$postarr['module']                = (strtolower($this->contract_source) == 'web_edit') ? 'cs': 'me';
			$postarr['action']                = 'updateActualBudget';
			$postarr['actual_fp_budget']      = $budget_arr[2]['budget'];
			$postarr['actual_package_budget'] = $budget_arr[1]['budget'];
			$postarr['actual_regfee_budget']  = $budget_arr[7]['budget'];
			$postarr['actual_total_budget']   = ($budget_arr[1]['budget']+$budget_arr[2]['budget']);
			$postarr['duration']              = max($budget_arr[1]['duration'],$budget_arr[2]['duration']);
			$postarr['usercode']              = 'selfsignup';
			
			// echo '<pre>';print_r($postarr);exit;echo JDBOX_SERVICES_API;exit;
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$budget_curl  = "http://imteyazraja.jdsoftware.com/jdbox/services/budgetsubmit.php";
			}
			else{
				$budget_curl  = $urlinfo['jdbox_url']."services/budgetsubmit.php";
			}
			$budget_curl_res = json_decode($this->curlCall($budget_curl,$postarr),true);
			
			if($this->trace)
			{
				echo '<pre> url :: '.$budget_curl;
				print_r($postarr);
				print_r($budget_curl_res);
			}
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$budget_curl.' params :: '.json_encode($postarr).' res :: '.json_encode($budget_curl_res);
			$log_data_arr['status'] = 'update act budget';
			$this->insertLog($log_data_arr);
			
			
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$urldealclose="http://imteyazraja.jdsoftware.com/jdbox/services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			else{
				$urldealclose  =  $urlinfo['jdbox_url']."services/invMgmt.php?data_city=".$data_city."&astatus=1&astate=1&parentid=".$parentid."&version=".$version; 
			}
			
			$mp_data2['data_city']  = $data_city;
			$mp_data2['parentid'] 	= $parentid;
			$mp_data2['astatus'] 	= 1;
			$mp_data2['astate'] 	= 1;
			$mp_data2['version']    = $version;
			$deal_close = json_decode($this->curlCall($urldealclose,$mp_data2),true);
			
			//$deal_close_arr=json_decode($deal_close,1);
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$urldealclose.' params :: '.json_encode($mp_data2).' res :: '.json_encode($deal_close);
			$log_data_arr['status'] = 'get budget data';
			$this->insertLog($log_data_arr);
			
			if($this->trace)
			{
				echo '<pre> url :: '.$urldealclose;
				print_r($mp_data2);
				print_r($deal_close);
			}
			
			 if($deal_close['error']['code']==0)
			 {
				return true;
			 }
			 else 
			 {
					 $from		  = "geniolitedaemon@justdial.com";
					 $emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com,sumesh.dubey@justdial.com";
					 $email_id_cc = " rajkumaryadav@justdial.com ";//
					 $source      = "GENIO_LITE - online";
					 $subject     = "GENIO LITE Daemon Error - invMgmt API Failed";
					 $emailtext   = " Contract Id :       ".$this->parentid."
									  <br> Data City :      ".$this->data_city."
									  <br><br> API URL :      ".$urldealclose."
									  <br><br> Passed Params:      ".json_encode($mp_data2)."
									  <br><br> API Error :      ".json_encode($deal_close['error']['msg'])."";
									  
					 if( !preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) )				  
						$res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
					 
					 
					$updt_params = array();
					$updt_params['parentid'] 	= $parentid;
					$updt_params['data_city'] 	= $data_city;
					$updt_params['done_flag'] 	= 2;
					$updt_params['remarks'] 	= $deal_close['error']['msg'];
					$this->updateDoneFlag($updt_params);
					
					$message = 'Your deal for - '.$parentid.'('.$data_city.') is failed !';
					$user_codes = array('009882','10013675','10033558');
					array_push($user_codes, $user_code);
					$this -> sendNotification($data_city, $parentid, $company_name, $user_codes, $message, $notification_alert = 1 , $mail_alert = 0);
					exit;
					
			 }
			 
			
		}
		
		function preApprovalAdjustment($parentid, $data_city, $urlinfo, $budget_arr, $curl_BalAdjust_data, $user_name)
		{
			   if( ( ( $budget_arr[1]['budget'] > 0 && $budget_arr[1]['duration'] < 1825 ) || ( $budget_arr[2]['budget'] > 0 && $budget_arr[2]['duration'] < 1825 ) ||  stristr(strtolower($user_name),'jduser') ) && ($this -> isActivePhoneSearch($parentid,$data_city,$urlinfo)) )
			   {	

					if(strtoupper(trim($curl_BalAdjust_data['source']))	== 'GL_SIGNUP' && is_array($curl_BalAdjust_data['campaign_details']) )
					{
						$curl_BalAdjust_data['campaign_details']   = json_encode($curl_BalAdjust_data['campaign_details']);
					}
			
					if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
						$mp_url_existing_balance_adjustment  = "http://vinaydesai.jdsoftware.com/csgenio_test/api_services/api_readjustment_process.php";
					}
					else{
						//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
						$mp_url_existing_balance_adjustment  = $urlinfo['url']."api_services/api_readjustment_process.php";
					}
					
					$mp_url_existing_balance_adjustment_res = json_decode($this->curlCall($mp_url_existing_balance_adjustment,$curl_BalAdjust_data),true);
					
					if($this->trace)
					{
						echo '<pre> est bal adj :: '.$mp_url_existing_balance_adjustment;
						print_r($mp_url_existing_balance_adjustment_res);
					}
					
					$log_data_arr = array();
					$log_data_arr['info']	= 'curl ulr :: '.$mp_url_existing_balance_adjustment.' params '.json_encode($curl_BalAdjust_data).' resp data :: '.json_encode($mp_url_existing_balance_adjustment_res);
					$log_data_arr['status'] = 'auto approval api';
					$this->insertLog($log_data_arr);
					
					if($mp_url_existing_balance_adjustment_res['results']['ERRCODE'] == 0 && strtolower(trim($mp_url_existing_balance_adjustment_res['results']['STATUS'])) == 'success' && count($mp_url_existing_balance_adjustment_res['results']['DATA'])>0)
					{
						
						$this-> auto_renewal_adjustment = 1;
						
						if($mp_url_existing_balance_adjustment_res['results']['DATA'][1]['new_budget'] > 0 && $budget_arr[1]['budget'] > 0)
							$budget_arr[1]['budget'] =  $mp_url_existing_balance_adjustment_res['results']['DATA'][1]['new_budget'];
						
						if($mp_url_existing_balance_adjustment_res['results']['DATA'][1]['newduration'] > 0 && $budget_arr[1]['duration'] > 0)
							$budget_arr[1]['duration'] =  $mp_url_existing_balance_adjustment_res['results']['DATA'][1]['newduration'];
						
						if($mp_url_existing_balance_adjustment_res['results']['DATA'][2]['new_budget'] > 0 && $budget_arr[2]['budget'] > 0)
							$budget_arr[2]['budget'] =  $mp_url_existing_balance_adjustment_res['results']['DATA'][1]['new_budget'];
						
						if($mp_url_existing_balance_adjustment_res['results']['DATA'][2]['newduration'] > 0 && $budget_arr[2]['duration'] > 0)
							$budget_arr[2]['duration'] =  $mp_url_existing_balance_adjustment_res['results']['DATA'][1]['newduration'];
							
						
					}
						
				}
				
				return $budget_arr;
							
		}
		function caryExstngBddngData($parentid, $version, $data_city, $user_code, $user_name)
		{ 
			$urlinfo = $this->getURLInfo($data_city);
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$caryExstngBddngData_url = "http://vishalvinodrana.jdsoftware.com/jdbox/services/caryExstngBddngData.php"; 
			}
			else{
				$caryExstngBddngData_url =  $urlinfo['jdbox_url']."services/caryExstngBddngData.php"; 
			}
			
			$caryExstngBddngData_param['parentid'] 	= $parentid;
			$caryExstngBddngData_param['data_city'] = $data_city;
			$caryExstngBddngData_param['module'] 	= 'me';
			$caryExstngBddngData_param['version']   = $version;
			$caryExstngBddngData_param['usercode'] 	= $user_code;
			$caryExstngBddngData_param['username'] 	= $user_name;
			$caryExstngBddngData_param['action'] 	= 'carryExstngBdngDataToNewVersion';
			$caryExstngBddngData_param['trace']     = 0;
			
			$caryExstngBddngData_res = json_decode($this->curlCall($caryExstngBddngData_url,$caryExstngBddngData_param),true);
			
			if($this->trace)
			{
				echo '<pre> budget submit url :: '.$caryExstngBddngData_url;
				print_r($caryExstngBddngData_param);
				echo '<br> res :: '.json_encode($caryExstngBddngData_res);
			}
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'passed version :: '.$version.'curl ulr :: '.$caryExstngBddngData_url.' params :: '.json_encode($caryExstngBddngData_param).' res :: '.json_encode($caryExstngBddngData_res);
			$log_data_arr['status'] = 'carry exst bding';
			$this->insertLog($log_data_arr);
			
		}
		
		function populateNationalListingData($genralInfoArr,$extraDetailsArr,$interMediateTable,$campaign_data)
		{
			
			$sql_generalinfo = "SELECT * FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
			$res_generalinfo = parent::execQuery($sql_generalinfo, $this->conn_idc);
			if($res_generalinfo && parent::numRows($res_generalinfo)>0)
			{
				$genralInfoArr = parent::fetchData($res_generalinfo);
			}
			
			if($this->trace)
			{
				echo '<br> sql :: '.$sql_generalinfo;
				echo '<br> res :: '.$res_generalinfo;
				echo '<br> row :: '.parent::numRows($res_generalinfo);
			}
			
			$sql_extradetails = "SELECT * FROM tbl_companymaster_extradetails WHERE parentid = '".$this->parentid."'";
			$res_extradetails  = parent::execQuery($sql_extradetails, $this->conn_idc);
			if($res_extradetails && parent::numRows($res_extradetails)>0)
			{
				$extraDetailsArr = parent::fetchData($res_extradetails);
			}
			
			if($this->trace)
			{
				echo '<br> sql :: '.$sql_extradetails;
				echo '<br> res :: '.$res_extradetails;
				echo '<br> row :: '.parent::numRows($res_extradetails);
			}
			
			if($this->trace)
			{
				echo '<pre> company data:: ';
				print_r($genralInfoArr);
				print_r($extraDetailsArr);
				//die;
			}
			
			$insert_into_genifo=" INSERT INTO db_national_listing.tbl_companymaster_generalinfo_national SET
									nationalid				= '".$genralInfoArr[nationalid]."',
									sphinx_id				= '".$genralInfoArr[sphinx_id]."',
									regionid				= '".$genralInfoArr[stdcode]."',
									companyname				= '".addslashes(stripslashes($genralInfoArr[companyname]))."',
									parentid				= '".$genralInfoArr[parentid]."',
									country					= '".addslashes(stripslashes($genralInfoArr[country]))."',
									state					= '".addslashes(stripslashes($genralInfoArr[state]))."',
									city					= '".addslashes(stripslashes($genralInfoArr[city]))."',
									display_city			= '".addslashes(stripslashes($genralInfoArr[display_city]))."',
									area					= '".addslashes(stripslashes($genralInfoArr[area]))."',
									subarea					= '".addslashes(stripslashes($genralInfoArr[subarea]))."',
									office_no				= '".$genralInfoArr[office_no]."',
									building_name			= '".addslashes(stripslashes($genralInfoArr[building_name]))."',
									street					= '".addslashes(stripslashes($genralInfoArr[street]))."',
									street_direction		= '".addslashes(stripslashes($genralInfoArr[street_direction]))."',
									street_suffix			= '".addslashes(stripslashes($genralInfoArr[street_suffix]))."',
									landmark				= '".addslashes(stripslashes($genralInfoArr[landmark]))."',
									landmark_custom			= '".addslashes(stripslashes($genralInfoArr[landmark_custom]))."',
									pincode					= '".$genralInfoArr[pincode]."',
									pincode_addinfo			= '".$genralInfoArr[pincode_addinfo]."',
									latitude				= '".$genralInfoArr[latitude]."',
									longitude				= '".$genralInfoArr[longitude]."',
									geocode_accuracy_level	= '".$genralInfoArr[geocode_accuracy_level]."',
									full_address			= '".addslashes(stripslashes($genralInfoArr[full_address]))."',
									stdcode					= '".$genralInfoArr[stdcode]."',
									landline				= '".$genralInfoArr[landline]."',
									landline_display		= '".$genralInfoArr[landline_display]."',
									landline_feedback		= '".addslashes(stripslashes($genralInfoArr[landline_feedback]))."',
									mobile					= '".$genralInfoArr[mobile]."',
									mobile_display			= '".$genralInfoArr[mobile_display]."',
									mobile_feedback			= '".addslashes(stripslashes($genralInfoArr[mobile_feedback]))."',
									fax						= '".$genralInfoArr[fax]."',
									tollfree				= '".$genralInfoArr[tollfree]."',
									tollfree_display		= '".$genralInfoArr[tollfree_display]."',
									email					= '".$genralInfoArr[email]."',
									email_display			= '".$genralInfoArr[email_display]."',
									email_feedback			= '".addslashes(stripslashes($genralInfoArr[email_feedback]))."',
									sms_scode				= '".addslashes(stripslashes($genralInfoArr[sms_scode]))."',
									website					= '".$genralInfoArr[website]."',
									contact_person			= '".addslashes(stripslashes($genralInfoArr[contact_person]))."',
									contact_person_display	= '".addslashes(stripslashes($genralInfoArr[contact_person_display]))."',
									othercity_number		= '".$genralInfoArr[othercity_number]."',
									mobile_admin			= '".$genralInfoArr[mobile_admin]."',
									paid					= '".$genralInfoArr[paid]."',
									displayType				= '".$genralInfoArr[displayType]."',
									data_city				= '".$genralInfoArr[data_city]."'
									
								ON DUPLICATE KEY UPDATE
									
									companyname				= '".addslashes(stripslashes($genralInfoArr[companyname]))."',
									country					= '".addslashes(stripslashes($genralInfoArr[country]))."',
									state					= '".addslashes(stripslashes($genralInfoArr[state]))."',
									city					= '".addslashes(stripslashes($genralInfoArr[city]))."',
									display_city			= '".addslashes(stripslashes($genralInfoArr[display_city]))."',
									area					= '".addslashes(stripslashes($genralInfoArr[area]))."',
									subarea					= '".addslashes(stripslashes($genralInfoArr[subarea]))."',
									office_no				= '".$genralInfoArr[office_no]."',
									building_name			= '".addslashes(stripslashes($genralInfoArr[building_name]))."',
									street					= '".addslashes(stripslashes($genralInfoArr[street]))."',
									street_direction		= '".addslashes(stripslashes($genralInfoArr[street_direction]))."',
									street_suffix			= '".addslashes(stripslashes($genralInfoArr[street_suffix]))."',
									landmark				= '".addslashes(stripslashes($genralInfoArr[landmark]))."',
									landmark_custom			= '".addslashes(stripslashes($genralInfoArr[landmark_custom]))."',
									pincode					= '".$genralInfoArr[pincode]."',
									pincode_addinfo			= '".addslashes(stripslashes($genralInfoArr[pincode_addinfo]))."',
									latitude				= '".$genralInfoArr[latitude]."',
									longitude				= '".$genralInfoArr[longitude]."',
									geocode_accuracy_level	= '".$genralInfoArr[geocode_accuracy_level]."',
									full_address			= '".addslashes(stripslashes($genralInfoArr[full_address]))."',
									stdcode					= '".$genralInfoArr[stdcode]."',
									landline				= '".$genralInfoArr[landline]."',
									landline_display		= '".$genralInfoArr[landline_display]."',
									landline_feedback		= '".addslashes(stripslashes($genralInfoArr[landline_feedback]))."',
									mobile					= '".$genralInfoArr[mobile]."',
									mobile_display			= '".$genralInfoArr[mobile_display]."',
									mobile_feedback			= '".addslashes(stripslashes($genralInfoArr[mobile_feedback]))."',
									fax						= '".$genralInfoArr[fax]."',
									tollfree				= '".$genralInfoArr[tollfree]."',
									tollfree_display		= '".$genralInfoArr[tollfree_display]."',
									email					= '".$genralInfoArr[email]."',
									email_display			= '".$genralInfoArr[email_display]."',
									email_feedback			= '".addslashes(stripslashes($genralInfoArr[email_feedback]))."',
									sms_scode				= '".addslashes(stripslashes($genralInfoArr[sms_scode]))."',
									website					= '".$genralInfoArr[website]."',
									contact_person			= '".addslashes(stripslashes($genralInfoArr[contact_person]))."',
									contact_person_display	= '".addslashes(stripslashes($genralInfoArr[contact_person_display]))."',
									othercity_number		= '".$genralInfoArr[othercity_number]."',
									mobile_admin			= '".$genralInfoArr[mobile_admin]."',
									paid					= '".$genralInfoArr[paid]."',
									displayType				= '".$genralInfoArr[displayType]."',
									data_city				= '".$genralInfoArr[data_city]."'" ;
			
			$res_into_genifo = parent::execQuery($insert_into_genifo, $this->conn_idc);
			
			if($this->trace)
			{
				echo '<br> sql :: '.$insert_into_genifo;
				echo '<br> res :: '.$res_into_genifo;
			}

			$insert_into_extraDetails="INSERT INTO db_national_listing.tbl_companymaster_extradetails_national SET
											nationalid					= '".$extraDetailsArr[nationalid]."',
											sphinx_id					= '".$extraDetailsArr[sphinx_id]."',
											regionid					= '".$extraDetailsArr[regionid]."',
											companyname					= '".addslashes(stripslashes($extraDetailsArr[companyname]))."',
											parentid					= '".$extraDetailsArr[parentid]."',
											landline_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[landline_addinfo]))."',
											mobile_addinfo 				= '".addslashes(stripslashes($extraDetailsArr[mobile_addinfo]))."',
											tollfree_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[tollfree_addinfo]))."',
											contact_person_addinfo 		= '".addslashes(stripslashes($extraDetailsArr[contact_person_addinfo]))."',
											attributes 					= '".addslashes(stripslashes($extraDetailsArr[attributes]))."',
											attributes_edit 			= '".addslashes(stripslashes($extraDetailsArr[attributes_edit])) ."',
											turnover 					= '".addslashes(stripslashes($extraDetailsArr[turnover]))."',
											working_time_start 			= '".$extraDetailsArr[working_time_start]."',
											working_time_end 			= '".$extraDetailsArr[working_time_end]."',
											payment_type 				= '".$extraDetailsArr[payment_type]."',
											year_establishment 			= '".$extraDetailsArr[year_establishment]."',
											accreditations 				= '".addslashes(stripslashes($extraDetailsArr[accreditations]))."',
											certificates 				= '".addslashes(stripslashes($extraDetailsArr[certificates]))."',
											no_employee 				= '".$extraDetailsArr[no_employee]."',
											business_group 				= '".$extraDetailsArr[business_group]."',
											email_feedback_freq 		= '".$extraDetailsArr[email_feedback_freq]."',/*Email frequency feedback*/
											statement_flag 				= '".$extraDetailsArr[statement_flag]."',
											alsoServeFlag 				= '".$extraDetailsArr[alsoServeFlag]."',
											guarantee 					= '".$extraDetailsArr[guarantee]."',
											contract_calltype 			= '".$extraDetailsArr[contract_calltype] ."',
											deactflg 					= '".$extraDetailsArr[deactflg]."',
											display_flag 				= '".$extraDetailsArr[display_flag] ."',
											fmobile 					= '".$extraDetailsArr[fmobile]."',
											femail 						= '".$extraDetailsArr[femail]."',
											flgActive 					= '".$extraDetailsArr[flgActive]."',
											freeze 						= '".$extraDetailsArr[freeze]."',
											mask 						= '".$extraDetailsArr[mask]."',
											hidden_flag 				= '".$extraDetailsArr[hidden_flag]."',
											lockDateTime 				= '".$extraDetailsArr[lockDateTime]."',
											lockedBy 					= '".addslashes(stripslashes($extraDetailsArr[lockedBy]))."',
											temp_deactive_start 		= '".$extraDetailsArr[temp_deactive_start]."',
											temp_deactive_end 			= '".$extraDetailsArr[temp_deactive_end]."',
											promptype 					= '".$extraDetailsArr[promptype]."',
											serviceName 				= '".addslashes(stripslashes($extraDetailsArr[serviceName]))."',
											createdby					= '".$extraDetailsArr[createdby]."',
											createdtime					= now(),
											original_creator			= '".$extraDetailsArr[original_creator]."',
											original_date				= now(),
											updatedBy 					= '".$extraDetailsArr[updatedBy]."',
											updatedOn 					= '".$extraDetailsArr[updatedOn]."',
											catidlineage				= '".$extraDetailsArr[catidlineage]."',
											catidlineage_search			= '".addslashes(stripslashes($extraDetailsArr[catidlineage_search]))."',
											fb_prefered_language		= '".$extraDetailsArr[fb_prefered_language]."',
											data_city					= '".$extraDetailsArr[data_city]."'
											
										ON DUPLICATE KEY UPDATE
											
											companyname					='".addslashes(stripslashes($extraDetailsArr[companyname]))."',
											landline_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[landline_addinfo]))."',
											mobile_addinfo 				= '".addslashes(stripslashes($extraDetailsArr[mobile_addinfo]))."',
											tollfree_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[tollfree_addinfo]))."',
											contact_person_addinfo		= '".addslashes(stripslashes($extraDetailsArr[contact_person_addinfo]))."',
											attributes 					= '".addslashes(stripslashes($extraDetailsArr[attributes]))."',
											attributes_edit 			= '".addslashes(stripslashes($extraDetailsArr[attributes_edit]))."',
											turnover 					= '".$extraDetailsArr[turnover]."',
											working_time_start 			= '".$extraDetailsArr[working_time_start]."',
											working_time_end 			= '".$extraDetailsArr[working_time_end]."',
											payment_type 				= '".$extraDetailsArr[payment_type]."',
											year_establishment 			= '".$extraDetailsArr[year_establishment]."',
											accreditations 				= '".addslashes(stripslashes($extraDetailsArr[accreditations]))."',
											certificates 				= '".addslashes(stripslashes($extraDetailsArr[certificates]))."',
											no_employee 				= '".$extraDetailsArr[no_employee]."',
											business_group 				= '".addslashes(stripslashes($extraDetailsArr[business_group]))."',
											email_feedback_freq 		= '".addslashes(stripslashes($extraDetailsArr[email_feedback_freq]))."',
											statement_flag 				= '".$extraDetailsArr[statement_flag]."',
											alsoServeFlag 				= '".$extraDetailsArr[alsoserve]."',
											guarantee 					= '".$extraDetailsArr[guarantee]."',
											contract_calltype 			= '".$extraDetailsArr[contract_calltype] ."',
											createdby 					= '".addslashes(stripslashes($extraDetailsArr[createdby]))."',
											createdtime 				= '".$extraDetailsArr[createdtime]."',
											deactflg 					= '".$extraDetailsArr[deactflg]."',
											display_flag 				= '".$extraDetailsArr[display_flag] ."',
											fmobile 					= '".$extraDetailsArr[fmobile]."',
											femail 						= '".$extraDetailsArr[femail]."',
											flgActive 					= '".$extraDetailsArr[flgActive]."',
											freeze 						= '".$extraDetailsArr[freeze]."',
											mask 						= '".$extraDetailsArr[mask]."',
											hidden_flag 				= '".$extraDetailsArr[hidden_flag]."',
											lockDateTime 				= '".$extraDetailsArr[lockDateTime]."',
											lockedBy 					= '".addslashes(stripslashes($extraDetailsArr[lockedBy]))."',
											temp_deactive_start 		= '".$extraDetailsArr[temp_deactive_start]."',
											temp_deactive_end 			= '".$extraDetailsArr[temp_deactive_end]."',
											promptype 					= '".$extraDetailsArr[promptype]."',
											serviceName 				= '".addslashes(stripslashes($extraDetailsArr[serviceName]))."',
											updatedBy 					= '".addslashes(stripslashes($extraDetailsArr[updatedBy]))."',
											updatedOn 					= '".$extraDetailsArr[updatedOn]."',
											catidlineage				= '".$extraDetailsArr[catidlineage]."',
											catidlineage_search			= '".addslashes(stripslashes($extraDetailsArr[catidlineage_search]))."',
											fb_prefered_language		= '".$extraDetailsArr[fb_prefered_language]."',
											data_city					= '".$extraDetailsArr[data_city]."'";
		
					$result_ext_details = parent::execQuery($insert_into_extraDetails, $this->conn_idc);
		
					if($this->trace)
					{
						echo '<br> sql :: '.$insert_into_extraDetails;
						echo '<br> res :: '.$result_ext_details;
					}
			/*-------------- creating Array for Phone search display---------------------------*/
			if(trim($genralInfoArr[mobile_display]))
				$phone_searchArr[]=trim($genralInfoArr[mobile_display]);
			if(trim($genralInfoArr[landline_display]))
				$phone_searchArr[]=trim($genralInfoArr[landline_display]);
			if(trim($genralInfoArr[tollfree_display]))
				$phone_searchArr[]=trim($genralInfoArr[tollfree_display]);
			if(trim($genralInfoArr[fax]))
				$phone_searchArr[]=trim($genralInfoArr[fax]);
			if(trim($genralInfoArr[virtualNumber]))
				$phone_searchArr[]=trim($genralInfoArr[virtualNumber]);


			$phone_search	=	implode(',',$phone_searchArr);
			$address 		= 	$genralInfoArr[fulladdress].",".$genralInfoArr[city].",".$genralInfoArr[state];
			
			$insert_comp_srch="INSERT INTO db_national_listing.tbl_companymaster_search_national SET
									nationalid						= '".$extraDetailsArr[nationalid]."',
									sphinx_id						= '".$extraDetailsArr[sphinx_id]."',
									regionid						= '".$genralInfoArr[stdcode]."',
									companyname						= '".addslashes($extraDetailsArr[companyname])."',
									parentid						= '".$extraDetailsArr[parentid]."',
									companyname_search				= '".addslashes($extraDetailsArr[companyname_search])."',
									companyname_search_area			= '".addslashes($extraDetailsArr[companyname_search_area])."',
									/*companyname_search_stem		= '".addslashes($interMediateTable['compname_stemed'])."',
									companyname_search_WS			= '".addslashes($interMediateTable['compname_WS'])."',
									companyname_search_stem_WS	    = '".addslashes($interMediateTable['compname_stemed_WS'])."',*/
									latitude						= '".$genralInfoArr[latitude]."',
									longitude						= '".$genralInfoArr[longitude]."',
									state							= '".$genralInfoArr[state]."',
									city							= '".addslashes($genralInfoArr[city])."',
									pincode							= '".$genralInfoArr[pincode]."',
									phone_search					= '".$phone_search."',
									address							= '".addslashes($address)."',
									contact_person					= '".addslashes($extraDetailsArr[contact_person_display])."',
									email							= '".$genralInfoArr[email_display]."',
									website							= '".$genralInfoArr[website]."',
									catidlineage_search				=  '".addslashes(stripslashes($extraDetailsArr[catidlineage_search]))."',
									length							= '".strlen($genralInfoArr[companyname])."',
									national_catidlineage_search	= '".addslashes(stripslashes($extraDetailsArr[national_catidlineage_search]))."',
									display_flag					= '".$extraDetailsArr[display_flag]."',
									prompt_flag						= '".$interMediateTable[prompt_flag]."',
									paid							= '".$interMediateTable[paid]."',
									updatedBy						= '".$extraDetailsArr[updatedBy]."',
									updatedOn						= '".$extraDetailsArr[updatedOn]."'
							
							ON DUPLICATE KEY UPDATE
							
									companyname						= '".addslashes($genralInfoArr[companyname])."',
									companyname_search				= '".addslashes($extraDetailsArr[companyname_search])."',
									companyname_search_area			= '".addslashes($extraDetailsArr[companyname_search_area])."',
									/*companyname_search_stem		= '".addslashes($interMediateTable['compname_stemed'])."',
									companyname_search_WS			= '".addslashes($interMediateTable['compname_WS'])."',
									companyname_search_stem_WS	    = '".addslashes($interMediateTable['compname_stemed_WS'])."',*/
									latitude						= '".$genralInfoArr[latitude]."',
									longitude						= '".$genralInfoArr[longitude]."',
									state							= '".$genralInfoArr[state]."',
									city							= '".$genralInfoArr[city]."',
									pincode							= '".$genralInfoArr[pincode]."',
									phone_search					= '".$phone_search."',
									address							= '".addslashes($address)."',
									contact_person					= '".$extraDetailsArr[contact_person_display]."',
									email							= '".$genralInfoArr[email_display]."',
									website							= '".$genralInfoArr[website]."',
									catidlineage_search				=  '".addslashes(stripslashes($extraDetailsArr[catidlineage_search]))."',
									national_catidlineage_search	= '".addslashes(stripslashes($extraDetailsArr[national_catidlineage_search]))."',
									length							= '".strlen($extraDetailsArr[companyname])."',
									display_flag					= '".$extraDetailsArr[display_flag]."',
									prompt_flag						= '".$interMediateTable[prompt_flag]."',
									paid							= '".$interMediateTable[paid]."',
									updatedBy						= '".$extraDetailsArr[updatedBy]."',
									updatedOn						= '".$extraDetailsArr[updatedOn]."'";
				
			$result_comp_srch = parent::execQuery($insert_comp_srch, $this->conn_idc);	
			if($this->trace)
			{
				echo '<br> sql :: '.$insert_comp_srch;
				echo '<br> res :: '.$result_comp_srch;
			}
			
			$this->insertCatDetails($genralInfoArr[data_city], $campaign_data);
		}
		
		function showFromTemp()
		{
			//$fieldStr	= implode(',',$fieldArr);
			$sql = "SELECT * FROM tbl_national_listing_temp WHERE parentid = '".$this->parentid."'";
			$qry = parent::execQuery($sql, $this->conn_idc);
			if($qry && mysql_num_rows($qry))
			{
				$row	= mysql_fetch_assoc($qry);
 			}
 			if($this->trace)
			{
				echo '<br> sql :: '.$sql;
				echo '<br> res :: '.$qry;
			}
			
			return $row;
 		}
 		
		function insertCatDetails($data_city, $campaign_data)
		{
			$row_sel	= $this -> showFromTemp();
			
			if($this -> manage_campaign)
			{
				
				$row_sel['Category_nationalid'] = $this -> national_eligible_catids;
				$row_sel['TotalCategoryWeight'] = $this -> national_eligible_catids_count;
				
			}
			
			$dail_contr	    = ($campaign_data[10]['budget']>0) ? ($campaign_data[10]['budget']/$campaign_data[10]['duration']) : $row_sel['dailyContribution'];
			$dail_web_contr = ($campaign_data[10]['budget']>0) ? ($campaign_data[10]['budget']/$campaign_data[10]['duration']) : $row_sel['WebdailyContribution'];
			
			$sql="INSERT INTO db_national_listing.tbl_national_listing 
						SET
							parentid 				='".$row_sel['parentid']."',
							Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
							Category_nationalid		='".$row_sel['Category_nationalid']."',
							TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
							totalcityweight			='".$row_sel['totalcityweight']."',
							contractCity			='".addslashes(stripslashes($row_sel['contractCity']))."',
							ContractStartDate		='".$row_sel['ContractStartDate']."',
							ContractTenure			='".$row_sel['ContractTenure']."',
							dailyContribution		='".addslashes(stripslashes($dail_contr))."',
							WebdailyContribution	='".addslashes(stripslashes($dail_web_contr))."',
							latitude				='".$row_sel['latitude']."',
							longitude				='".$row_sel['longitude']."',
							iroCard					='".$row_sel['iroCard']."',
							lastupdate				='".$row_sel['lastupdate']."',
							update_flag				='0',
							data_city				='".$data_city."',
							state_zone				='".$row_sel['state_zone']."',
							paid					= 1,
							short_url				='".$row_sel['short_url']."'
							
						ON DUPLICATE KEY UPDATE
							
							Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
							Category_nationalid		='".$row_sel['Category_nationalid']."',
							TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
							totalcityweight			='".$row_sel['totalcityweight']."',
							contractCity			='".addslashes(stripslashes($row_sel['contractCity']))."',
							ContractStartDate		='".$row_sel['ContractStartDate']."',
							ContractTenure			='".$row_sel['ContractTenure']."',
							dailyContribution		='".addslashes(stripslashes($dail_contr))."',
							WebdailyContribution	='".addslashes(stripslashes($dail_web_contr))."',
							latitude				='".$row_sel['latitude']."',
							longitude				='".$row_sel['longitude']."',
							iroCard					='".$row_sel['iroCard']."',
							lastupdate				='".$row_sel['lastupdate']."',
							update_flag				='0',
							data_city				='".$data_city."',
							state_zone				='".$row_sel['state_zone']."',
							paid					= 1,
							short_url				='".$row_sel['short_url']."'";
				
			$res = parent::execQuery($sql, $this->conn_idc);
			if($this->trace)
			{
				echo '<br> sql :: '.$sql;
				echo '<br> res :: '.$res;
			}
			$insert_debug_log = "INSERT INTO tbl_national_listing_temp_debug SET parentid='".$row_sel['parentid']."',page='services/selfsignup.php',line_no= '2156',query= '".addslashes($sql)."',date_time= '".date('Y-m-d H:i:s')."',ucode= '".$_SESSION['ucode']."',uname='".$_SESSION['uname']."'";
			$res_insert_debug_log = parent::execQuery($insert_debug_log, $this->conn_idc);
			
		}
		
		
		function dobudgetjson($res_best_camp_for_pack,$data,$version,$conn_budget,$parentid){
			
			$best_arr=$res_best_camp_for_pack;
			$sub_arr=array();
			$inner_arr=array();
			  $best_arr=$best_arr['result']['c_data'];
			  $totalpackagebudget=0;
			  $ins_arr=array();
			  $ins_str='';
			foreach ($best_arr as $key => $value) { 
				 $ins_str='(';
					$totalpackagebudget=0;
					foreach ($value['pin_data'] as $pinkey => $pinvalue) {
					$totalpackagebudget+=$pinvalue['pos'][100]['budget'];
					$inv_array[$pinkey]['budget']=$pinvalue['pos'][100]['budget'];
					$inv_array[$pinkey]['bidvalue']=$pinvalue['pos'][100]['bidvalue'];
					$inv_array[$pinkey]['inv']=$pinvalue['pos'][100]['inv_avail'];
					$inv_array[$pinkey]['pos']=100;
					$inv_array[$pinkey]['cnt']=$pinvalue['cnt'];
					$inv_array[$pinkey]['cnt_f']=$pinvalue['cnt_f']; 
					
				}
				$ins_str.="'".$parentid."',";
				$ins_str.="'".$version."',";
				$ins_str.="'".$key."',";
				$ins_str.="'".$value['ncid']."',";
				$ins_str.="'".addslashes(stripslashes((json_encode($inv_array))))."',";
				$ins_str.="'".$totalpackagebudget."',"; 
				$ins_str.="'self_signup',"; 
				$ins_str.="'".date("Y-m-d H:i:s")."')"; 
				array_push($sub_arr, $ins_str);
			}
			$sub_ins_new=implode(",",$sub_arr);  
			

			if($sub_ins_new!=''){
				$ins_inter_sql="INSERT INTO db_budgeting.tbl_bidding_details_intermediate(parentid,VERSION,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon)
					VALUES $sub_ins_new";
				$res_ins = parent::execQuery($ins_inter_sql, $conn_budget);
				
				if($this->trace)
				{
					echo '<pre> sql  :: '.$ins_inter_sql;
					echo '<pre> res  :: '.$res_ins;
				}
				
				$log_data_arr = array();
			$log_data_arr['info']	= 'sql query res :: '.$res_ins;
			$log_data_arr['status'] = 'save bid intermediate data';
			$this->insertLog($log_data_arr);
			
				
				return true; 
			}
			else
				return false;

		}

	
	function bannerInventoryCheck($catids_arr,$data_city){
		
		$server_city 	= $this->getServerCity($data_city);
		$conn_finance  	= $this->db[$server_city]['fin']['master'];
		
		
			
		$available	= array();
		if(count($catids_arr)){
			$catids_str = implode("','",$catids_arr);
			$sqlBannerInventory	= "SELECT catid,cat_sponbanner_inventory FROM tbl_cat_banner_bid WHERE catid IN ('".$catids_str."') AND data_city = '".$data_city."'";
			$resBannerInventory	= parent::execQuery($sqlBannerInventory, $conn_finance);
			
			if($resBannerInventory && parent::numRows($resBannerInventory)>0){
				
				while($row_bnrinven = parent::fetchData($resBannerInventory)){
					$available[$row_bnrinven['catid']] = (1 - $row_bnrinven['cat_sponbanner_inventory']);
				}
			}
			
			$catid_frm_avail = array_keys($available);
			$diffarr		 = array_diff($catids_arr,$catid_frm_avail);
			
			foreach($diffarr as $cat){
				$available[$cat]	= 1;
			}
		}
		return $available;
	}
	
	private function saveBannerSpecification($parentid,$data_city,$catinfo_arr){
		
		//$server_city = $this->getServerCity($data_city);
		//$conn_iro    = $this->db[$server_city]['iro']['master'];
		
		$catids_arr	= $this->catspon_categories;
		
		if(count($catids_arr)>0)
			$banner_catids = implode(",",$catids_arr);
			
		
		$sql_banner_details = "SELECT * FROM banner_payment_rotation_temp WHERE  parentid = '".$parentid."' AND active_flag = 1 ";
		$res_banner_details = parent::execQuery($sql_banner_details, $this->conn_idc);
		if($res_banner_details && parent::numRows($res_banner_details)>0){
		  $row_banner_details = parent::fetchData($res_banner_details);
		}
		
		if($this->trace)
		{
			echo '<br>banner rotation select query  :: '.$sql_banner_details;
			echo '<br>banner rotation select res  :: '.$res_banner_details;
			echo '<br>banner rotation select rows  :: '.parent::numRows($res_banner_details);
			print_r($row_banner_details);
		}
		
		$instructions   = ( $row_banner_details['instruction'] != '') ? $row_banner_details['instruction'] : 'No Specification';
		$budget	        = ( $row_banner_details['total_cost']>0 ) 	  ? $row_banner_details['total_cost']       : 4;
		$no_of_rotation = ( $row_banner_details['no_of_rotation']>0 ) ? $row_banner_details['no_of_rotation'] : 1;
		$payment_type   = ( $row_banner_details['paymentType'] == 2 ) ? 'ecs' : 'upfront';
		
		
		$sql_banner_rotation = "INSERT INTO catspon_banner_rotation SET
								 parentid 			 = '".$parentid."',
								 budget		 		 = '".$budget."',
								 no_of_rotation 	 = '".$no_of_rotation."',
								 payment_type	  	 = '".$payment_type."',
							     categories_for_spon = '".$banner_catids."'
								 ON DUPLICATE KEY UPDATE
								 budget		 		 = '".$budget."',
								 no_of_rotation 	 = '".$no_of_rotation."',
								 payment_type	  	 = '".$payment_type."',
								 categories_for_spon = '".$banner_catids."'";								 
		$res_banner_rotation = parent::execQuery($sql_banner_rotation, $this->conn_idc);
		
		if($this->trace)
		{
			echo '<br>banner rotation select query  :: '.$sql_banner_rotation;
			echo '<br>banner rotation select res  :: '.$res_banner_rotation;
		}
		
		$sqlCompSpecification = "INSERT INTO tbl_client_banner_specification SET
								 parentid 		= '".$parentid."',
								 campaignid 	= '5',
								 specification 	= '".$instructions."'
								 ON DUPLICATE KEY UPDATE
								 specification 	= '".$instructions."'";
		
		$resCompSpecification = parent::execQuery($sqlCompSpecification, $this->conn_idc);
		//$resCompSpecification = parent::execQuery($sqlCompSpecification, $conn_iro); // Competitors Banner
		
		if($this->trace)
		{
			echo '<br>banner rotation select query  :: '.$sqlCompSpecification;
			echo '<br>banner rotation select res  :: '.$resCompSpecification;
		}
		
		$sqlCatSpecification = "INSERT INTO tbl_client_banner_specification SET
								 parentid 		= '".$parentid."',
								 campaignid 	= '13',
								 specification 	= '".$instructions."'
								 ON DUPLICATE KEY UPDATE
								 specification 	= '".$instructions."'";
		$resCatSpecification = parent::execQuery($sqlCatSpecification, $this->conn_idc);
		if($this->trace)
		{
			echo '<br>banner rotation select query  :: '.$sqlCatSpecification;
			echo '<br>banner rotation select res  :: '.$resCatSpecification;
		}
		//$resCatSpecification = parent::execQuery($sqlCatSpecification, $conn_iro);	// Category Banner
		
	}
	private function saveJDRRBudget($contract_data,$jdrr_data){
		
		
		$server_city = $this->getServerCity($jdrr_data['data_city']);
		$conn_local  = $this->db[$server_city]['d_jds']['master'];
		
		$geninfo_data 	= $contract_data['geninfo'];
		$address 		= $geninfo_data['full_address'].",".$geninfo_data['city'].",".$geninfo_data['state']." Pincode - ".$geninfo_data['pincode'];
		
		$sqlRemoveJdrrInfo = "DELETE FROM tbl_jd_reviewrating_budget WHERE parentid = '".$jdrr_data['parentid']."'";
		//$resRemoveJdrrInfo = parent::execQuery($sqlRemoveJdrrInfo, $conn_local);
		$resRemoveJdrrInfo = parent::execQuery($sqlRemoveJdrrInfo, $this->conn_idc);
		
		if($this->trace)
		{
			echo '<br>jdrr delete query  :: '.$sqlRemoveJdrrInfo;
			echo '<br>jdrr delete res  :: '.$resRemoveJdrrInfo;
		}
		
		$sqlGetJdrr = "SELECT * FROM tbl_jdrr_temp WHERE parentid = '".$jdrr_data['parentid']."' AND version = '".$jdrr_data['version']."'";
		//$resRemoveJdrrInfo = parent::execQuery($sqlRemoveJdrrInfo, $conn_local);
		$resGetJdrr = parent::execQuery($sqlGetJdrr, $this->conn_idc);
		
		if($this->trace)
		{
			echo '<br>jdrr selct query  :: '.$sqlGetJdrr;
			echo '<br>jdrr selct res  :: '.$resGetJdrr;
			echo '<br>jdrr num rows  :: '.mysql_num_rows($resGetJdrr);
		}
		
		if($resGetJdrr && mysql_num_rows($resGetJdrr))
		{
			$rowGetJdrr=mysql_fetch_assoc($resGetJdrr);
			$sqlInsertJdrrInfo = "INSERT INTO tbl_jd_reviewrating_budget SET
								  parentid            		= '" . $rowGetJdrr['parentid'] . "',
								  budget              		= '" . $rowGetJdrr['budget'] . "',
								  tenure              		= '" . $rowGetJdrr['tenure'] . "',
								  avg_rating         		= '" . $rowGetJdrr['avg_rating'] . "',
								  no_of_rating        		= '" . $rowGetJdrr['no_of_rating'] . "',
								  no_of_certificate 		= '" . $rowGetJdrr['no_of_certificate'] . "',
								  monthlyPayment    		= '" . $rowGetJdrr['monthlyPayment'] . "',
								  downPayment        		= '" . $rowGetJdrr['downPayment'] . "',
								  certificate_size  		= '" . addslashes($rowGetJdrr['certificate_size']) . "',
								  multiple_branch_flag  	= '" . $rowGetJdrr['multiple_branch_flag'] . "',
								  Linked_branch_Contractid  = '" . addslashes($rowGetJdrr['Linked_branch_Contractid']). "',
								  uptDate             		= '".date("Y-m-d H:i:s")."',
								  data_city          		= '".addslashes($rowGetJdrr['data_city'])."',
								  address            		= '".addslashes($rowGetJdrr['address'])."',
								  email              		= '".addslashes($rowGetJdrr['email'])."'";
			$resInsertJdrrInfo = parent::execQuery($sqlInsertJdrrInfo, $this->conn_idc);
			if($this->trace)
			{
				echo '<br>jdrr insert query  :: '.$sqlInsertJdrrInfo;
				echo '<br>jdrr inser res  :: '.$resInsertJdrrInfo;
			}
			
		} else if($resRemoveJdrrInfo){
			$sqlInsertJdrrInfo = "INSERT INTO tbl_jd_reviewrating_budget SET
								  parentid            		= '" . $jdrr_data['parentid'] . "',
								  budget              		= '" . $jdrr_data['budget'] . "',
								  tenure              		= '" . $jdrr_data['tenure'] . "',
								  avg_rating         		= '',
								  no_of_rating        		= '',
								  no_of_certificate 		= '1',
								  monthlyPayment    		= '0',
								  downPayment        		= '0',
								  certificate_size  		= '16X12',
								  multiple_branch_flag  	= '',
								  Linked_branch_Contractid  = '',
								  uptDate             		= '".date("Y-m-d H:i:s")."',
								  data_city          		= '".$jdrr_data['data_city']."',
								  address            		= '".$this->slasher($address)."',
								  email              		= '".$this->slasher($geninfo_data['email'])."'";
			$resInsertJdrrInfo = parent::execQuery($sqlInsertJdrrInfo, $conn_local);					  
			if($this->trace)
			{
				echo '<br>jdrr insert query  :: '.$sqlInsertJdrrInfo;
				echo '<br>jdrr inser res  :: '.$resInsertJdrrInfo;
			}
        }
        
        $log_data_arr = array();
		$log_data_arr['info']	= 'jdrr query res '.$resInsertJdrrInfo;
		$log_data_arr['status'] = 'insert jdrr data';
		$this->insertLog($log_data_arr);                   
		
	}
	
	function verifiedMobileNum($parentid)
	{
		$verified_mobile = '';
		$sqlVerifiedMobile = "SELECT mobile FROM mobilemail_verification_code WHERE parentid = '".$parentid."'";
		$resVerifiedMobile = parent::execQuery($sqlVerifiedMobile, $this->conn_idc);
		if($resVerifiedMobile)
		{
			$row_verified = parent::fetchData($resVerifiedMobile);
			$verified_mobile= $row_verified['mobile'];
		}
            return $verified_mobile;
            
	}
	
	
	function jdaResponseLog($logparams)
	{
		$sqlInsertData = "INSERT INTO online_regis1.tbl_jda_api_response_gl_log
																  SET
																  parentid              = '".$logparams['parentid']."',
																  ucode                 = '".$logparams['ucode']."',
																  url                   = '".addslashes($logparams['url'])."',
																  response              = '".addslashes($logparams['response'])."',
																  jda_flag              = '".$logparams['jda_flag']."',
																  insertdate    = NOW()";
		$resInsertData = parent::execQuery($sqlInsertData, $this->conn_idc);
		if($this->trace)
		{
			echo '<br> sql :: '.$sqlInsertData;
			echo '<br> res :: '.$resInsertData;
		}
	}
	
	function getContractDealClosedDetails()
	{
		$sql_get_assoc_details = "SELECT associated_tme_code, associated_tme_name, tds_percent, invoice_name, amount_paybale, balance_adjusted, is_single_payment  from tbl_contract_transaction_info where parentid = '".$this->parentid."' and master_transaction_id ='".$this->pass_transid."' ";
		$res_get_assoc_details = parent::execQuery($sql_get_assoc_details, $this->conn_idc);
		if($res_get_assoc_details && parent::numRows($res_get_assoc_details)>0)
		{
			$dealclose_details_arr = array();
			$row_get_assoc_details = parent::fetchData($res_get_assoc_details);
			
			$row_get_assoc_details['invoice_name'] = urlencode(stripslashes(preg_replace('/[^(\x20-\x7F)]*/','', $row_get_assoc_details['invoice_name'])));
			
			
			$sql_get_assoc_ext_details = "SELECT pan_number, tan_number, available_balance, apportioned_amount, parent_contract, parent_contract_city  FROM tbl_contract_transaction_extra_details where parentid = '".$this->parentid."' and master_transaction_id ='".$this->pass_transid."' ";
			
			$res_get_assoc_ext_details = parent::execQuery($sql_get_assoc_ext_details, $this->conn_idc);
			if($res_get_assoc_ext_details && parent::numRows($res_get_assoc_ext_details)>0)
			{
				$row_get_assoc_ext_details = parent::fetchData($res_get_assoc_ext_details);
				if($row_get_assoc_details['tds_percent'] > 0)
				{
					$row_get_assoc_details['pan_number']	  = $row_get_assoc_ext_details['pan_number'];
					$row_get_assoc_details['tan_number']	  = $row_get_assoc_ext_details['tan_number'];
				}
				$row_get_assoc_details['available_balance']   = $row_get_assoc_ext_details['available_balance'];
				
				$row_get_assoc_details['apportioned_amount']  = $row_get_assoc_ext_details['apportioned_amount'];
				$row_get_assoc_details['parent_contract']  	  = $row_get_assoc_ext_details['parent_contract'];
				$row_get_assoc_details['parent_contract_city']= $row_get_assoc_ext_details['parent_contract_city'];
			}
			
			$dealclose_details_arr['dealclose_details'] 	 = $row_get_assoc_details;
			
			return $dealclose_details_arr;
		}
	}
	
	function getInstrumentDetails($parentid,$data_city,$version)
	{
		$sql_instrument = "SELECT instrument_type, instrument_number AS cheque_number, instrument_date AS cheque_date, instrument_deposit_location AS cheque_dep_loc,
								  bank_micr AS cheque_micr, bank_city AS cheque_bank_city, bank_name AS cheque_bank_name, bank_ifsc AS cheque_ifsc, 
								  bank_branch AS cheque_bank_branch, bank_acc_number AS cheque_acc_number, IF(LOWER(bank_acc_type) = 'savings',1,2) AS cheque_acc_type,  round(instrument_amount) AS instr_amt, instrument_image_path AS cheque_image, instrument_deposit_location AS deposit_location 
								  FROM tbl_geniolite_instrument_data
								  WHERE parentid ='".$parentid."' 
								  AND   master_transaction_id='".$this->pass_transid."' 
								  AND VERSION = '".$version."'
								  AND isactive = 1";
								  
		$res_instrument = parent::execQuery($sql_instrument, $this->conn_idc);
		if($this->trace)
		{
			echo '<br> sql :: '.$sql_instrument;
			echo '<br> res :: '.$res_instrument;
			echo '<br> row :: '.parent::numRows($res_instrument);
		}
		if($res_instrument && parent::numRows($res_instrument)>0)
		{
			$instrument_details_arr = array();
			$this -> total_cash_amount = 0;
			while($row_instrument = parent::fetchData($res_instrument))
			{
				if(strtolower($row_instrument['instrument_type']) == 'cash')
				{
					$instrument_details_arr['cash'][] = array('instr_amt'=> $row_instrument['instr_amt'], 'deposit_location'=> urlencode($row_instrument['deposit_location']));
					
					//storing total cash instrument amount to send alert to paresh for amount greater than 199999
					
					$this -> total_cash_amount += $row_instrument['instr_amt'];
				}
				
				if(strtolower($row_instrument['instrument_type']) == 'cheque')
				{
					if($row_instrument['cheque_bank_branch'])
					$row_instrument['cheque_bank_branch'] = urlencode(preg_replace('/[^(\x20-\x7F)]*/','', $row_instrument['cheque_bank_branch']));
					
					if($row_instrument['cheque_bank_name'])
					$row_instrument['cheque_bank_name'] = urlencode(preg_replace('/[^(\x20-\x7F)]*/','', $row_instrument['cheque_bank_name']));
					
					if($row_instrument['deposit_location'])
					$row_instrument['deposit_location'] = urlencode(preg_replace('/[^(\x20-\x7F)]*/','', $row_instrument['deposit_location']));
					
					$instrument_details_arr['cheque'][] = $row_instrument;
				}
				
			}
		}
		
		$sql_online_instrument = "SELECT a.parentid, a.master_transaction_id, a.transaction_id, a.amount, b.pg_response, a.instrument_type, b.remote_address, 
								  a.instrument_deposit_location, a.auth_code, date(a.instrument_date) as instrument_date  
								  FROM tbl_payment_summary_genio_lite a JOIN tbl_transaction_summary_genio_lite b
								  ON a.parentid = b.parentid
								  AND a.master_transaction_id=b.master_transac_id
								  AND a.transaction_id = b.transaction_id
								  WHERE a.parentid ='".$parentid."' 
								  AND   a.master_transaction_id='".$this->pass_transid."' 
								  AND   a.VERSION = '".$version."'
								  AND   a.instrument_type NOT IN ('cash','cheque')
								  AND   a.isActive = 1
								  AND   b.payment_processed=1 ";
		$res_online_instrument = parent::execQuery($sql_online_instrument, $this->conn_idc);
		
		if($this->trace)
		{
			echo '<br> sql :: '.$sql_online_instrument;
			echo '<br> res :: '.$res_online_instrument;
			echo '<br> row :: '.parent::numRows($res_online_instrument);
		}
		if($res_online_instrument && parent::numRows($res_online_instrument)>0)
		{
			
			while($row_online_instrument = parent::fetchData($res_online_instrument))
			{
				if(trim($row_online_instrument['instrument_type']))
				{
				   $pg_response_arr 					       =  json_decode($row_online_instrument['pg_response'],true);
				   if(is_array($pg_response_arr) && count($pg_response_arr)>0 && !in_array(strtolower(trim($row_online_instrument['instrument_type'])), array('neft','oflc')))
				   {
					$instrument_details_arr['online'][]        =  array( 'trans_id'		      => $row_online_instrument['transaction_id'],
																		 'instr_amt'		  => round($pg_response_arr['trans_details'][$row_online_instrument['transaction_id']]['amt']), 
																		 'pay_card_type'      => strtolower(trim($row_online_instrument['instrument_type'])), 
																		 'payu_trans_id'      => $pg_response_arr['trans_details'][$row_online_instrument['transaction_id']]['mihpayid'], 
																		 'card_bank'          => $pg_response_arr['bankcode'], 
																		 'udf4'		          => $pg_response_arr['trans_details'][$row_online_instrument['transaction_id']]['udf4'], 
																		 'pg_name'  		  => $pg_response_arr['selPGMode'], 
																		 'remote_addr'        => $row_online_instrument['remote_address'] );
				   }				   
				   else if(strtolower(trim($row_online_instrument['instrument_type'])) == 'neft')
					{
						$instrument_details_arr['neft'][]       =  array( 'neft_payment_date'=> $row_online_instrument['instrument_date'],
																		  'neft_approvalcode'=> $row_online_instrument['auth_code'], 
																		  'instr_amt'    	 => round($row_online_instrument['amount']) );
					}
				   else if(strtolower(trim($row_online_instrument['instrument_type'])) == 'oflc')
					{
						$instrument_details_arr['offlinecreditcard'][] = array( 'oflc_card_date'  => $row_online_instrument['instrument_date'],
																			 'oflc_approvalcode'  => $row_online_instrument['auth_code'], 
																			 'instr_amt'		  => round($row_online_instrument['amount']), 
																			 'card_dep_loc'       => urlencode($row_online_instrument['instrument_deposit_location']));
					}
				}
			}
			
		}
		
		
		return $instrument_details_arr;
		
	}
	
	function updateOmniData($parentid,$version,$conn_iro,$conn_fin,$conn_idc,$conn_budget,$data_city, $campaigninfo){
		    
		    $urlinfo = $this->getURLInfo($data_city);
			
			$get_postarr['parentid']              = $parentid;
			$get_postarr['data_city']             = $data_city;
			$get_postarr['version']               = $version;
			$get_postarr['module']                = 'me';
			$get_postarr['action']                = 'populateomnidata';
			$get_postarr['campaign_details']	  = $campaigninfo;
			$get_postarr['usercode']              = 'selfsignup';
			
			// echo '<pre>';print_r($postarr);exit;echo JDBOX_SERVICES_API;exit;
			
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$omni_curl  = "http://vishalvinodrana.jdsoftware.com/jdbox/services/omniwrapperGenioLITE.php";
			}
			else{
				$omni_curl  = $urlinfo['jdbox_url']."services/omniwrapperGenioLITE.php";
			}
			
			$get_omni_curl_res = json_decode($this->curlCall($omni_curl,$get_postarr),true);
			
			if($this->trace)
			{
				echo '<pre> category data :: '.$omni_curl;
				print_r($get_postarr);
				print_r($get_omni_curl_res);
			}			
			
			
			
			
			$log_data_arr = array();
			$log_data_arr['info']	= 'curl ulr :: '.$omni_curl.' params '.json_encode($get_postarr).' resp data :: '.json_encode($get_omni_curl_res);
			$log_data_arr['status'] = 'omn api-res: '.$get_omni_curl_res['error']['code'];
			$this->insertLog($log_data_arr);
			
			
				
			
			
	}
	
	private function findContractDetails($parentid,$data_city){
		
		$server_city = $this->getServerCity($data_city);
		$conn_iro    = $this->db[$server_city]['iro']['master'];
		
		$contractinfo_arr = array();
		$sqlGeneralInfo = "SELECT parentid,companyname,contact_person,pincode,latitude,longitude,landline,mobile,mobile_feedback,email,city,state,full_address FROM tbl_companymaster_generalinfo WHERE parentid = '".$parentid."'";
		$resGeneralInfo = parent::execQuery($sqlGeneralInfo, $conn_iro);
		if($resGeneralInfo && parent::numRows($resGeneralInfo)>0){
			$gendata = 1;
			$row_geninfo = parent::fetchData($resGeneralInfo);
			$contractinfo_arr['geninfo'] = $row_geninfo;
		}
		$sqlExtraDetails = "SELECT parentid,catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
		$resExtraDetails = parent::execQuery($sqlExtraDetails, $conn_iro);
		if($resExtraDetails && parent::numRows($resExtraDetails)>0){
			$extradata = 1;
			$row_extradetails = parent::fetchData($resExtraDetails);
			$contractinfo_arr['extradet'] = $row_extradetails;
		}
		
		$sqlIDGenerator = "SELECT parentid,sphinx_id,docid,data_city FROM tbl_id_generator WHERE parentid = '".$parentid."'";
		$resIDGenerator = parent::execQuery($sqlIDGenerator, $conn_iro);
		if($resIDGenerator && parent::numRows($resIDGenerator)>0){
			$idgendata = 1;
			$row_idgen = parent::fetchData($resIDGenerator);
			$contractinfo_arr['idgen'] = $row_idgen;
		}
		if(($gendata == 1) && ($extradata == 1) && ($idgendata == 1)){
			return $contractinfo_arr;
		}else{
			return array();
		}
	}
	
	private function getServerCity($data_city){
		$data_city 	= trim($data_city);
		return $server_city = ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
	}
	
	private function isValidJSON($string){
	   return is_array(json_decode($string, true)) ? 1 : 0;
	}
	private function updateDoneFlag($updt_params){
		$parentid 	= $updt_params['parentid'];
		$data_city 	= $updt_params['data_city'];
		$done_flag 	= $updt_params['done_flag'];
		$remarks 	= $updt_params['remarks'];
		
		$sqlUpdtDoneFlag = "UPDATE online_regis1.tbl_selfsignup_contracts SET done_flag = '".$done_flag."', remarks = '".addslashes($remarks)."', processed_date = '".date('Y-m-d H:i:s')."' WHERE parentid = '".$parentid."' AND data_city = '".$data_city."' AND trans_id = '".$this->pass_transid."' ";
		$resUpdtDoneFlag = parent::execQuery($sqlUpdtDoneFlag, $this->conn_default);
	}
	
	private function insertLog($log_params){
		//print"<pre>";print_r($log_params);
		$qry="INSERT INTO tbl_onlineSignUp_log(parentid, source, status_flag, signup_info, data_city, updateddate) VALUES
				  ('".$this->parentid."','".$this->contract_source."','".addslashes($log_params['status'])."','".addslashes($log_params['info'])."','".addslashes($this->data_city)."','".date('Y-m-d-H:i:s')."')";
        $res = parent::execQuery($qry, $this->conn_log);
		
	}
	
	
	function getSMSPromoDetails($parentid,$data_city,$urlinfo)
	{
		if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
			$smstemp="http://vishalvinodrana.jdsoftware.com/jdbox/services/fetch_update_sms_promo.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchTempData"; 
		}
		else{
			$smstemp  =  $urlinfo['jdbox_url']."services/fetch_update_sms_promo.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchTempData"; 
		}
		
		$mp_data2['data_city']  = $data_city;
		$mp_data2['parentid'] 	= $parentid;
		$mp_data2['module'] 	= 'me';
		$mp_data2['action'] 	= 'fetchTempData';
		$smstemp_res = json_decode($this->curlCall($smstemp,$mp_data2),true);
		if($this->trace)
		{
			echo '<pre> category data :: '.$smstemp;
			print_r($smstemp_res);
		}			
		return $smstemp_res;
			
	}
	
	function populateSMSPROMODetails($parentid,$data_city,$urlinfo)
	{
		if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
			$smsmain="http://vishalvinodrana.jdsoftware.com/jdbox/services/fetch_update_sms_promo.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=PopulateMainTable"; 
		}
		else{
			$smsmain  =  $urlinfo['jdbox_url']."services/fetch_update_sms_promo.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=PopulateMainTable"; 
		}
		
		$mp_data2['data_city']  = $data_city;
		$mp_data2['parentid'] 	= $parentid;
		$mp_data2['module'] 	= 'me';
		$mp_data2['action'] 	= 'PopulateMainTable';
		$smsmain_res = json_decode($this->curlCall($smsmain,$mp_data2),true);
		if($this->trace)
		{
			echo '<pre> category data :: '.$smsmain;
			print_r($smsmain_res);
		}			
		
		$log_data_arr = array();
		$log_data_arr['info']	= 'curl ulr :: '.$smsmain.' params '.json_encode($mp_data2).' resp data :: '.json_encode($smsmain_res);
		$log_data_arr['status'] = 'auto approval api';
		$this->insertLog($log_data_arr);
		
		return $smstemp_res;
			
	}
	
	function populateCrisilDetails($parentid,$data_city,$trans_id,$user_name,$version)
	{
		if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
			$crisilmain="http://apoorva.jdsoftware.com/megenio/me_services/contractInfo/processCrisilDealCloseInfo"; 
		}
		else{			
			$crisilmain  =  GNO_URL."/me_services/contractInfo/processCrisilDealCloseInfo";
		}
		
		$mp_data2['empname'] 	= $user_name;
		$mp_data2['data_city']  = $data_city;
		$mp_data2['master_transaction_id'] 	= $trans_id;
		$mp_data2['parentid'] 	= $parentid;		
		$mp_data2['version'] 	= $version;
		$mp_data2['urlFlag'] 	= 1;
		$crisilmain_res = json_decode($this->curlCall($crisilmain,$mp_data2),true);
		if($this->trace)
		{
			echo '<pre> category data :: '.$crisilmain;
			print_r($crisilmain_res);
		}			
		
		$log_data_arr = array();
		$log_data_arr['info']	= 'curl ulr :: '.$crisilmain.' params '.json_encode($mp_data2).' resp data :: '.json_encode($crisilmain_res);
		$log_data_arr['status'] = 'crisil_campaign';
		$this->insertLog($log_data_arr);
		
		return $smstemp_res;
			
	}
	
	
	function isActivePhoneSearch($parentid,$data_city,$urlinfo,$version)
	{
		if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
			$fetchCampaignWiseBal="http://vishalvinodrana.jdsoftware.com/jdbox/services/fetchFinData.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchCampaignWiseBal&version=".$version.""; 
		}
		else{
			$fetchCampaignWiseBal  =  $urlinfo['jdbox_url']."services/fetchFinData.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchCampaignWiseBal&version=".$version.""; 
		}
		
		$mp_data['data_city']   = $data_city;
		$mp_data['parentid'] 	= $parentid;
		$mp_data['version'] 	= $version;
		$mp_data['module']   	= 'me';
		$mp_data['action'] 		= 'fetchCampaignWiseBal';
		$fetchCampaignWiseBal_res = json_decode($this->curlCall($fetchCampaignWiseBal,$mp_data),true);
		if($this->trace)
		{
			echo '<pre> fetchCampaignWiseBal url :: '.$fetchCampaignWiseBal;
			print_r($mp_data);
			print_r($fetchCampaignWiseBal_res);
		}			
		
		$log_data_arr = array();
		$log_data_arr['info']	= 'curl ulr  :: '.$fetchCampaignWiseBal.' params '.json_encode($mp_data).' resp data :: '.json_encode($fetchCampaignWiseBal_res);
		$log_data_arr['status'] = 'is active phone search api';
		$this->insertLog($log_data_arr);
		
		if( count($fetchCampaignWiseBal_res) > 0 && ( $fetchCampaignWiseBal_res[1]['bal'] > 0 || $fetchCampaignWiseBal_res[2]['bal'] > 0 ) && !in_array($version, array($fetchCampaignWiseBal_res[1]['version'], $fetchCampaignWiseBal_res[2]['version'])) &&  $fetchCampaignWiseBal_res[1]['readjust_started'] <= 0 &&  $fetchCampaignWiseBal_res[2]['readjust_started'] <= 0 )
		{
			return true;
		}else
		{
			return false;
		}
			
	}
	
	
	function isActiveNationalListing($parentid,$data_city,$urlinfo)
	{
		if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
			$fetchCampaignWiseBal="http://vishalvinodrana.jdsoftware.com/jdbox/services/fetchFinData.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchCampaignWiseBal"; 
		}
		else{
			$fetchCampaignWiseBal  =  $urlinfo['jdbox_url']."services/fetchFinData.php?data_city=".$data_city."&module=me&parentid=".$parentid."&action=fetchCampaignWiseBal"; 
		}
		
		$mp_data['data_city']  = $data_city;
		$mp_data['parentid'] 	= $parentid;
		$mp_data['module'] 	= 'me';
		$mp_data['action'] 	= 'fetchCampaignWiseBal';
		$fetchCampaignWiseBal_res = json_decode($this->curlCall($fetchCampaignWiseBal,$mp_data),true);
		if($this->trace)
		{
			echo '<pre> fetchCampaignWiseBal url :: '.$fetchCampaignWiseBal;
			print_r($mp_data);
		}			
		
		$log_data_arr = array();
		$log_data_arr['info']	= 'curl ulr :: '.$fetchCampaignWiseBal.' params '.json_encode($mp_data).' resp data :: '.json_encode($fetchCampaignWiseBal_res);
		$log_data_arr['status'] = 'is active national';
		$this->insertLog($log_data_arr);
		
		if( count($fetchCampaignWiseBal_res)>0 && $fetchCampaignWiseBal_res[10]['bal'] > 0 )
		{
			return true;
		}else
		{
			return false;
		}
			
	}
	
	
	
	function getParentCategories($catidlist,$deactive=0)
	{

			$catidarray = null;
			$parent_categories_arr= null;
			$catidlistarr = explode(",",$catidlist);

			$catidlistarr = array_unique($catidlistarr);
			$catidlistarr = array_filter($catidlistarr);
			$catidliststr = implode(",",$catidlistarr);

			if($deactive)
			{
					$display_cond="";
			}
			else
			{
					$display_cond.= " AND isdeleted=0 AND mask_status=0 ";
			}


			//$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid>0 AND category_name !='' ".$display_cond;
			$final_parent_category_arr = array();
			//$res 	= parent::execQuery($sql, $this->conn_local);
			$cat_params = array();
			$cat_params['page']= 'selfsignup_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'associate_national_catid';

			$where_arr  	=	array();
			if($catidliststr!=''){
				$where_arr['catid']			= $catidliststr;
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

							//$associate_national_catid_arr = explode(',',$row['associate_national_catid']);

							$associate_national_catid_arr = array_unique($associate_national_catid_arr);
							$associate_national_catid_arr = array_filter($associate_national_catid_arr);
							$associate_national_catid_str = implode(",",$associate_national_catid_arr);

							// fetching the catid from national_catid and removing original catid
							//$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid in (".$associate_national_catid_str.") and catid not in (".$catidliststr.") AND catid>0 AND category_name !='' ".$display_cond;
							$final_parent_category_arr = array();
							//$res 	= parent::execQuery($sql, $this->conn_local);
							$cat_params = array();
							$cat_params['page']= 'selfsignup_class';
							$cat_params['data_city'] 	= $this->data_city;
							$cat_params['return']		= 'catid';

							$where_arr  	=	array();
							if($associate_national_catid_str!=''){
								$where_arr['national_catid']	= $associate_national_catid_str;
								$where_arr['catid']				= "!".$catidliststr;
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
									//$row = mysql_fetch_assoc($res);
									foreach ($cat_res_arr['results'] as $key => $cat_arr) {
										$parent_categories =	$cat_arr['catid'];
										if($parent_categories!=''){
											$parent_categories_arr[]= $parent_categories;
										}									
									}

									if(count($parent_categories_arr)>0)
									{
	 //$parent_categories_arr = explode(',',$row['parent_categories']);

											$parent_categories_arr = array_unique($parent_categories_arr);
											$parent_categories_arr = array_filter($parent_categories_arr);


											return $parent_categories_arr;
									}
							}
					}
			}
	}

	
	function getCatidLineageArr($cat_ids_list,$catids_list)
	{
		$catid_id_listOther   = trim($cat_ids_list,'|P|');

		$catidArrExisting 	  = explode('|P|',$catid_id_listOther);
		$catidLineage 		  = str_replace('|P|','/,/',$cat_ids_list);
		$catidLineage 		  = '/'.substr($catidLineage,3).'/';
		$catidList 	  		  = substr(str_replace('|P|',',',$cat_ids_list),1);
		
		if($catids_list!='')
		{
			$row_catid_parent = $this->getParentCategories($catids_list);
			if(count($row_catid_parent)>0)
			{
				$arrayCatLinSrch_inter  = array_merge($row_catid_parent,$catidArrExisting);
				$arrayCatLinSrch 		= array_unique ($arrayCatLinSrch_inter);
			}
			else
			{
				$arrayCatLinSrch 		= $catidArrExisting;
			}
			$arrayCatLinSrch = $this->getValidCategories($arrayCatLinSrch);

			$catLinSrch  = implode('/,/',$arrayCatLinSrch);
			$catLinSrch = '/'.$catLinSrch.'/';
			
			$national_catids = '';					
			//$sql_national_catids = "select catid, national_catid, category_scope from d_jds.tbl_categorymaster_generalinfo where catid in('".str_replace(",","','",$catidList)."') and isdeleted=0 and mask_status=0 and biddable_type=1 group by catid,category_name order by callcount desc";
			//$res_national_catids 	= parent::execQuery($sql_national_catids, $this->conn_local);
			$cat_params = array();
			$cat_params['page']= 'selfsignup_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,national_catid,category_scope';
			$cat_params['orderby']		= 'callcount desc';

			$where_arr  	=	array();
			if($catidList!=''){
				$where_arr['catid']			= $catidList;
				$where_arr['isdeleted']		= '0';
				$where_arr['mask_status']	= '0';
				$where_arr['biddable_type']	= '1';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_national_catids)
				{
					if($national_catids)
						$national_catids .= "/,/".$row_national_catids['national_catid']; 
					else
						$national_catids = $row_national_catids['national_catid']; 
						
					if( $row_national_catids['category_scope'] == 1 ||  $row_national_catids['category_scope'] == 2 )
						$national_eligible_catids_arr[] = $row_national_catids['national_catid']; 
				}
			}
			
			if(is_array($national_eligible_catids_arr) && count($national_eligible_catids_arr)>0)
			{
				$national_eligible_catids_arr = array_unique($national_eligible_catids_arr);
				$this -> national_eligible_catids_count =  count($national_eligible_catids_arr);
				$this -> national_eligible_catids       = "|P|".implode("|P|", $national_eligible_catids_arr)."|P|";
				
			}
			
			$national_catids = '/'.$national_catids.'/';
			
			$national_catids_srch ='';
			
			//$sql_national_catids_srch = "select catid,national_catid from d_jds.tbl_categorymaster_generalinfo where catid in('".str_replace(",","','",$arrayCatLinSrch)."') and isdeleted=0 and mask_status=0 and active_flag=1 and biddable_type=1 group by catid,category_name order by callcount desc";
			//$res_national_catids_srch 	= parent::execQuery($sql_national_catids_srch, $this->conn_local);
			$cat_params = array();
			$cat_params['page']= 'selfsignup_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid,national_catid';
			$cat_params['orderby']		= 'callcount desc';

			$where_arr  	=	array();
			if(count($arrayCatLinSrch)>0){
				$where_arr['catid']			= implode(",",$arrayCatLinSrch);
				$where_arr['isdeleted']		= '0';
				$where_arr['mask_status']	= '0';
				$where_arr['biddable_type']	= '1';
				$where_arr['active_flag']	= '1';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_national_catids_srch)
				{
					if($national_catids_srch)
						$national_catids_srch .= "/,/".$row_national_catids_srch['national_catid']; 
					else
						$national_catids_srch = $row_national_catids_srch['national_catid']; 
				}
			}
			$national_catids_srch = '/'.$national_catids_srch.'/';

		}
		
		$catid_arr['catidlineage']		           = $catidLineage;
		$catid_arr['catidlineage_search']   	   = $catLinSrch;
		$catid_arr['national_catidlineage'] 	   = $national_catids;
		$catid_arr['national_catidlineage_search'] = $national_catids_srch;
		
		return $catid_arr;
		
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
	function getCategoryDetails($catids_arr,$data_city)
	{
		
		$server_city = $this->getServerCity($data_city);
		$conn_local  = $this->db[$server_city]['d_jds']['master'];
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid, category_name, national_catid, IF(misc_cat_flag&64 = 64,0,1) AS is_allow, paid_clients, nonpaid_clients, category_scope, is_restricted FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $conn_local);
		$cat_params = array();
		$cat_params['page']= 'selfsignup_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,national_catid,misc_cat_flag,paid_clients,nonpaid_clients,category_scope,is_restricted';

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
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_catdetails)
			{
				$misc_cat_flag =	$row_catdetails['misc_cat_flag'];
				$is_allow 	  = 1;
				if(((int)$misc_cat_flag & 64)==64){
					$is_allow = 0;
				}

				$catid 			= intval($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$national_catid	= intval($row_catdetails['national_catid']);
				$CatinfoArr[$catid]['catname']    	= $category_name;
				$CatinfoArr[$catid]['natcat']     	= $national_catid;
				$CatinfoArr[$catid]['is_allow']   	= $is_allow;
				$CatinfoArr[$catid]['p_client']   	= trim($row_catdetails['paid_clients']);
				$CatinfoArr[$catid]['np_client']  	= trim($row_catdetails['nonpaid_clients']);
				$CatinfoArr[$catid]['is_national']	= in_array(trim($row_catdetails['category_scope']), array(1,2)) ? 1 : 0;
				$CatinfoArr[$catid]['is_restricted']= trim($row_catdetails['is_restricted']);
				
				if(trim($row_catdetails['is_restricted']) == 1)
					$this -> is_restricted_category_present = 1;
			}
		}
		return $CatinfoArr;
	}
	
	function ValidateCampaignData($data_city, $urlinfo, $parentid, $user_code, $campaign_type, $campids_arr, $user_name)
	{
		if( $campaign_type == 'pure_pack' && in_array(1,$campids_arr) && !in_array(2,$campids_arr) && !stristr(strtolower($user_name),'jduser') && !in_array(strtolower($this->contract_source), array('web_edit', 'clientselfsignup'))  )
		{
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$mp_url					     = "http://vishalvinodrana.jdsoftware.com/jdbox/services/validatePhoneSearchCampaign.php";
			}
			else{
				//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
				$mp_url					     = $urlinfo['jdbox_url']."services/validatePhoneSearchCampaign.php";
			}
			$mp_data			    	 = array();
			$mp_data['parentid']	     = trim($parentid);
			$mp_data['data_city']        = strtolower(trim($data_city));
			$mp_data['usercode']         = strtolower(trim($user_code));
			$mp_data['module']	         = 'me';
			$mp_data['action']	         = 'validatePincode';
			
			$mp_resp 				= json_decode($this->curlCall($mp_url,$mp_data),true);
			
			
			
			if($this->trace)
			{
				echo '<pre> validate url :: '.$mp_url;
				print_r($mp_data);
				print_r($mp_resp);
			}	
			
			if( !is_array($mp_resp) || ( is_array($mp_resp) && count($mp_resp) <=0 ) || ( is_array($mp_resp) && count($mp_resp) > 0 && $mp_resp['errorCode'] ) )
			{
				
				$updt_params = array();
				$updt_params['parentid'] 	= $parentid;
				$updt_params['data_city'] 	= $data_city;
				$updt_params['done_flag'] 	= 2;
				$updt_params['remarks'] 	= $mp_resp['errormsg'];
				$this->updateDoneFlag($updt_params);
				
				
				$message = 'Package Pincodes Not Found - '.$parentid.'('.$data_city.')';
				$this -> sendNotification($data_city, $parentid, $company_name, $user_codes = array('009882','10013675','10033558',$user_code), $message, $notification_alert = 1 , $mail_alert = 1);
			
				exit;
			}
		}
		
	}
	
	function validateInstrumentData($data_city, $urlinfo, $parentid, $instrument_data, $dealclose_details_data, $user_code, $user_name)
	{
		if( $user_code && !stristr(strtolower($user_name),'jduser') && ( !is_array($instrument_data) || ( is_array($instrument_data) && count($instrument_data) <=0 ) ) && ( !is_array($dealclose_details_data) || ( is_array($dealclose_details_data) && ( $dealclose_details_data['dealclose_details']['is_single_payment'] != 2 || ( $dealclose_details_data['dealclose_details']['is_single_payment'] == 2  && $dealclose_details_data['dealclose_details']['apportioned_amount'] <= 0 ) ) ) )  )
		{
			$updt_params = array();
			$updt_params['parentid'] 	= $parentid;
			$updt_params['data_city'] 	= $data_city;
			$updt_params['done_flag'] 	= 2;
			$updt_params['remarks'] 	= 'Payment Not Found';
			$this->updateDoneFlag($updt_params);
			
			$message = 'Payment Not Found - '.$parentid.'('.$data_city.')';
			$this -> sendNotification($data_city, $parentid, $company_name, $user_codes = array('009882','10013675'), $message, $notification_alert = 1 , $mail_alert = 1);
			exit;
		}
	}
	
	function sendNotification($data_city, $parentid, $company_name='', $user_codes, $message, $notification_alert, $mail_alert)
	{
		
		$user_codes  = is_array($user_codes) ? $user_codes : array($user_codes);
		foreach($user_codes as $user_code)
		{
			if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
				$notification_url      =  GNO_URL."/geniolite_dash/sendNoti.php?userid=".$user_code."&pid=".$parentid."";//?topic=009882&msg=hello"
			}
			else{
				//$url= "http://".JDBOX_SERVICES_API."/log_generate_invoice_content.php";
				$notification_url      =  GNO_URL."/geniolite_dash/sendNoti.php?userid=".$user_code."&pid=".$parentid."";//?topic=009882&msg=hello"
			}
			
			$notification_data['topic']  = $user_code;
			$notification_data['msg']    = $message;
			$mp_resp_notification		 = $this->curlCall($notification_url,$notification_data);
		}
		
		if($mail_alert)
		{
			$from		  = "geniolitedaemon@justdial.com";
			$emailid 	  = "ronak.joshi@justdial.com,apoorv.agrawal@justdial.com,vivek1@justdial.com";
			$email_id_cc = "rohitkaul@justdial.com, rajkumaryadav@justdial.com";
			$source      = "GENIO_LITE";
			$subject     = "GENIO LITE Daemon Error ";
			$emailtext   = $message;
			$res_email = $this->sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
		}
		
	}
	
	private function getURLInfo($data_city){
		return $urldetails	= $this->configobj->get_url($data_city);
		/*
		$urlArr['url'] 					= $url;
		$urlArr['jdbox_url'] 			= $jdbox_url;
		$urlArr['jdbox_service_url'] 	= $jdbox_url.'services/';	
		$urlArr['city_indicator'] 		= $city_indicator;
		*/
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	function arrayProcess($requestedArr)
	{
		$processedArr = array();
		if(count($requestedArr)>0){
			$processedArr = array_merge(array_unique(array_filter($requestedArr)));
		}
		return $processedArr;
	}
	function slasher($data)
	{
		if(is_array($data)){
			foreach($data as $key=>$value){
				$data[$key] = addslashes(stripslashes($value));
			}
		}else{
			$data = addslashes(stripslashes($data));
		}
		return $data;		
	}
	function curlCall($curl_url,$data)
	{	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	function curlCallNew($curl_url,$data)
	{	
		$curl = curl_init();

		  curl_setopt_array($curl, array(
		  CURLOPT_PORT => "3005",
		  CURLOPT_URL => $curl_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST", 
		  CURLOPT_POSTFIELDS => "pay_req=".$data,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded",
			"postman-token: e8db0b91-a87f-df1b-a96b-3c6c0524c87a"
		  ),
		));
		$response = curl_exec($curl);
		curl_close($ch);
		return $response;
	}

	function checkGeoAcuuracy($parentid,$city){
		$retArr = array();
		$checkforentry = "SELECT latitude,longitude,geocode_accuracy_level  FROM online_regis.tbl_checkGeocodes WHERE parentid = '".$parentid."' AND city =  '".$city."'";
		$rescheckforentry = parent::execQuery($checkforentry, $this->conn_default);
		if($rescheckforentry && parent::numRows($rescheckforentry)>0)
		{
			$geoCheck = mysql_fetch_assoc($rescheckforentry);
			$retArr['data'] = $geoCheck;
			$retArr['code'] = 0;
		}else{
			$retArr['code'] = 1;
		}	
		return $retArr;					
	}

	function getPointerFlags($parentid,$lat,$long,$geocodeAcc,$datacity,$url){
		 $curlParamsurl =  $url."services/location_api.php?rquest=map_pointer_flag&parentid=".$parentid."&latitude=".$lat."&longitude=".$long."&data_city=".$datacity."&geocode_accuracy_level=".$geocodeAcc;
		$flagsRes   =  json_decode($this->curlCall($curlParamsurl),true);
		return $flagsRes;
	}
}
?>
