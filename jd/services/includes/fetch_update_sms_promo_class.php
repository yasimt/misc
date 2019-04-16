<?php

class fetch_update_sms_promo_class extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		
		$errorarray['error_code'] = 1;
			
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		if( trim($this->params['action']) == "fetchBudgetDetails" )
		{
			if( trim($this->params['companyname']) )
			{
				$this -> companyname = $this->params['companyname'];
			}
			
			if( trim($this->params['catid']) )
			{
				$this -> catid = $this->params['catid'];
			}
			
			if( trim($this->params['area_name']) )
			{
				$this -> area_name = $this->params['area_name'];
			}
			
		}
		
		if( trim($this->params['action']) == "PopulateTempTable" )
		{
			if(trim($this->params['calculated_data']) != "" && $this->params['calculated_data'] != null)
			{
				$this->calculated_data  = $this->params['calculated_data']; //initialize datacity
			}else
			{
				$errorarray['errormsg']='calculated_data missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		$this->companyClass_obj = new companyClass();
		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		
		if(strtoupper($this->module) == 'ME')
		{
			$this->conn_idc   	= $db[$data_city]['idc']['master'];
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp 	= $this->conn_idc;
		}
		else if(strtoupper($this->module) == 'TME')
		{
			$this->conn_tme  	= $db[$data_city]['tme_jds']['master'];
			$this->conn_temp     = $this->conn_tme;
		}
		else
		{
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp     = $this->conn_local;
		}
			
	}

	function updatetemptable()
	{
		
			
			$sql_promo = "select * from tbl_smsbid where bcontractid ='".$this->parentid."'";
			$res_promo 	= parent::execQuery($sql_promo, $this->fin);
			
			if($res_promo)
			{
				$del_sms_promp = "DELETE FROM tbl_smsbid_temp WHERE  bcontractid ='".$this->parentid."' ";
				$res_del_sms_promp 	= parent::execQuery($del_sms_promp, $this->conn_temp);
			   
				$insert="INSERT INTO tbl_smsbid_temp (bcontractid,tcontractid,bid_value,promo_txt,autoid,Tid,daily_sms,active_time , new_promo)
						VALUES";
				while($scC2c = mysql_fetch_array($res_promo))
				{
						$txt = str_replace("'","`",$scC2c[3]);
						$insert_values[] = "('".$scC2c[0]."','".$scC2c[1]."','".$scC2c[2]."','".addslashes($txt)."',NULL,'".$scC2c['Tid']."','".addslashes($scC2c['daily_sms'])."','".$scC2c['active_time']."','".$scC2c['new_promo']."')";
						
						
				}
				
				if(count($insert_values)>0)
					$insert_res = parent::execQuery($insert.implode(",",$insert_values), $this->conn_temp);
				
			}

					$returnarr['error']['code'] = 0;
					$returnarr['count'] 		= count($insert_values);
					$returnarr['res']	 		= $insert_res;
					$returnarr['msg']   		= 'success';
	
			return $returnarr;
	}
	
	function fetchBudgetdetails()
	{
		
		$constant_url =  ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtoupper($this->data_city)._IRO_API_IP : 'REMOTE_CITIES_IRO_API_IP') ;
		
		if($this->companyname)
		{ 
		    $curl_url ="http://".constant($constant_url)."/mvc/autosuggest/Adv_search?dcity=".urlencode($this->data_city)."&scity=".urlencode($this->data_city)."&compname=".urlencode($this->companyname)."&area=".urlencode(strtolower(trim($this -> area_name)))."&catid=&mod=cs&stpos=0&limit=250&paid=2&act=1&t=".rand(0,1000)."";
		}else if ($this->catid)
		{
			$curl_url ="http://".constant($constant_url)."/mvc/autosuggest/Adv_search?dcity=".urlencode($this->data_city)."&scity=".urlencode($this->data_city)."&compname=&area=".urlencode(strtolower(trim($this -> area_name)))."&catid=".urlencode($this->catid)."&mod=cs&stpos=0&limit=250&paid=2&act=1&t=".rand(0,1000)."";
		}
		//contpname=&addr=&street=&area=&phone=&state=&eid=&pid=&web=
		
		$post_data['dcity']    = $this->data_city;
		$post_data['scity']    = $this->data_city; 
		$post_data['compname'] = $this->companyname;
		$post_data['catid']    = $this->catid;
		$post_data['mod']      = 'cs';
		$post_data['stpos']    = 0;
		$post_data['limit']    = 1000;
		$post_data['paid']     = 2;
		$post_data['act']      = 1;
		$post_data['t']        = rand(0,1000);
		
		if($this->trace)
		{
			echo '<br> url :: '.$curl_url;
		}
		//echo '<br> url :: '.$curl_url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch,CURLOPT_POST, TRUE);
		//curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
		$company_data = json_decode(curl_exec($ch) , true) ;
		curl_error($ch);
		curl_close($ch);
		//echo '<pre>';
		if( count($company_data['results']['data']))
		{
			foreach( $company_data['results']['data'] as $parentid_data)
			{
				if($parentid_data['full_address'])
				{
					$full_address = explode(",",$parentid_data['full_address']);
				}
				$parentids[$parentid_data['parentid']]['compname'] = ($parentid_data['compname']) ?$parentid_data['compname'] : $parentid_data['companyname'];
				$parentids[$parentid_data['parentid']]['area']     = $parentid_data['areaname'] ? $parentid_data['areaname'] : substr($full_address[count($full_address)-1],0, strpos($full_address[count($full_address)-1],"-")) ;
			}
			
			if($this->trace)
			{
				echo '<pre>';
				print_r($parentids);
			}
			
			
			if( count($parentids) > 0)
			{
				$sql_c2s_data = "SELECT  * FROM c2s_nonpaid WHERE parentid IN ( '".implode("','",array_keys($parentids))."' ) ";
				$res_c2s_data = parent::execQuery($sql_c2s_data, $this->conn_local);
				
				$sql_sms_bid = "SELECT tcontractid, MIN(bid_value) AS max_bid_value FROM tbl_smsbid WHERE tcontractid IN ( '".implode("','",array_keys($parentids))."' ) GROUP BY tcontractid";
				$res_sms_bid = parent::execQuery($sql_sms_bid, $this->fin);
				if($res_sms_bid && mysql_num_rows($res_sms_bid)>0)
				{
					while($row_sms_bid = mysql_fetch_assoc($res_sms_bid))
					{
						$existing_sms_bid[$row_sms_bid['tcontractid']]['bidvalue'] = $row_sms_bid['max_bid_value'];
					}
				}
				
				if($res_c2s_data && mysql_num_rows($res_c2s_data)>0)
				{
					$sql_min_budget = "SELECT cityminbudget, c2s_min_budget FROM tbl_business_uploadrates  WHERE city='".$this->data_city."'";
					$res_min_budget = parent::execQuery($sql_min_budget, $this->conn_local);
					if($res_min_budget && mysql_num_rows($res_min_budget))
					{
					   $row_min_budget = mysql_fetch_assoc($res_min_budget);
					}
					
					$adjustment = 1; $minbid = 10; $smsAdjustment = 1.5; $newMinBidSms = 6.6667;
					while($row_c2s_data = mysql_fetch_assoc($res_c2s_data))
					{
						 $response_data[$row_c2s_data['parentid']]['compname'] = $parentids[$row_c2s_data['parentid']]['compname'];
						 $response_data[$row_c2s_data['parentid']]['area']     = $parentids[$row_c2s_data['parentid']]['area'];
						 
						
						 $conv 					= max(0.15, $row_c2s_data['final_conv']);
						 $final_bid				= $row_c2s_data['final_bid'] * $conv;
						 $clcnt 				= max($row_c2s_data['company_callcnt'],1);

						 $totcatbid_sms 		= max($clcnt,1) * max($newMinBidSms,$final_bid);
						 $totcatbid_sms 		= ($totcatbid_sms / ($clcnt)) * $smsAdjustment;
						 $bpd_sms 				= max($totcatbid_sms, $minbid, (($existing_sms_bid[$row_c2s_data['parentid']]['bidvalue'] * $smsAdjustment) +1));	 
						 
						 $response_data[$row_c2s_data['parentid']]['ccnt']      = $clcnt;
						 $response_data[$row_c2s_data['parentid']]['bid']       = $bpd_sms;
						 
						 $response_data[$row_c2s_data['parentid']]['daily_bid'] = ceil((max($clcnt,1)*1/365) * $bpd_sms);
						 
						 $c2s_nonpaid_data[] = $row_c2s_data['parentid'];
					}
					
					if( count(array_unique(array_keys($parentids))) > count(array_unique($c2s_nonpaid_data)) )
					{
						$row_c2s_data = '';//setting to blank to consider default value for each key
						$c2s_nonpaid_not_found_data =  array_filter(array_diff(array_unique(array_keys($parentids)), array_unique($c2s_nonpaid_data)));
						//print_r($c2s_nonpaid_not_found_data);
						//print_r($response_data);
						foreach($c2s_nonpaid_not_found_data as $not_found_parentid)
						{
							 $response_data[$not_found_parentid]['compname'] 	= $parentids[$not_found_parentid]['compname'];
							 $response_data[$not_found_parentid]['area']  		= $parentids[$not_found_parentid]['area'];
							 
							
							 $conv 					= max(0.15, $row_c2s_data['final_conv']);
							 $final_bid				= $row_c2s_data['final_bid'] * $conv;
							 $clcnt 				= max($row_c2s_data['company_callcnt'],1);

							 $totcatbid_sms 		= max($clcnt,1) * max($newMinBidSms,$final_bid);
							 $totcatbid_sms 		= ($totcatbid_sms / ($clcnt))*$smsAdjustment;
							 $bpd_sms 				= max($totcatbid_sms,$newMinBidSms,(($existing_sms_bid[$not_found_parentid]['bidvalue'] * $smsAdjustment) +1 ));	 
							 
							 $response_data[$not_found_parentid]['ccnt']      = $clcnt;
							 $response_data[$not_found_parentid]['bid']       = $bpd_sms;
							 
							 $response_data[$not_found_parentid]['daily_bid'] = ceil((max($clcnt,1)*1/365) * $bpd_sms);
						}
						
						
					}
					
					$return_data['error_code'] = 0;
					$return_data['data'] 	  = $response_data;
					$return_data['cityminbudget'] = $row_min_budget['c2s_min_budget'];
				}
				else
				{
					$return_data['error_code'] = 1;
					$return_data['msg'] = 'No Data Found - C2S';
				}
			}
			 else 
			 {
				$return_data['error_code'] = 1;
				$return_data['msg'] = 'No Data Found - SPHINXIDS';
			 }
		}
		else
			{
				$return_data['error_code'] = 1;
				$return_data['msg'] = 'No Data Found - SPHINX';
			}
			
			return $return_data;
	}
	
	function PopulateTempTable()
	{	
			
			if($this -> calculated_data)
			{
				$del_sms_promp = "DELETE FROM tbl_smsbid_temp WHERE  bcontractid ='".$this->parentid."' ";
				$res_del_sms_promp 	= parent::execQuery($del_sms_promp, $this->conn_temp);
			   
				$insert="INSERT INTO tbl_smsbid_temp (bcontractid,tcontractid,bid_value,promo_txt,autoid,Tid,daily_sms,active_time , new_promo)
						VALUES";
				if($this->trace)
				{
					echo '<br>';
					echo '<pre>'.$this -> calculated_data;
					echo '<br>';
				   echo '<br>'.$this -> calculated_data_arr;
				   echo '<br>';
				}
				
				$this -> calculated_data_arr = json_decode($this->calculated_data, true);
				
				if($this->trace)
				{
				   echo '<pre>';
				   print_r($this -> calculated_data_arr);
				}
				
				if(count($this -> calculated_data_arr)>0)
				{
					foreach($this -> calculated_data_arr[$this->parentid]['opted_contracts'] as $tid => $value_details)
					{
						
							$insert_values[] = "('".$this->parentid."','".$tid."','".$value_details['bidvalue']."','".addslashes($this -> calculated_data_arr[$this->parentid]['promo_text'])."',NULL,'".substr($tid,1)."','".$value_details['dailysms']."','".addslashes($this -> calculated_data_arr[$this->parentid]['promo_time'])."','1')";
						
					}
					
					if(count($insert_values)>0)
						$insert_res = parent::execQuery($insert.implode(",",$insert_values), $this->conn_temp);
					
					$returnarr['error']['code'] = 0;
					$returnarr['count'] 		= 'insert rows :: '.count($insert_values);
					$returnarr['res']	 		= $insert_res;
					$returnarr['msg']   		= 'success';
					
					
				}else
				{
					$returnarr['error']['code'] = 1;
					$returnarr['error']['msg'] = 'no data found in json';
				}
				
			}else
			{
				$returnarr['error']['code'] = 1;
				$returnarr['error']['msg'] = 'no data passed';
			}

					
	
			return $returnarr;
	}

	
	function PopulateMainTable()
	{	
			
			$sel_sms_promp = "SELECT BContractId,TContractId,Bid_Value,promo_txt,autoid,Tid,daily_sms,active_time,activeMap,new_promo,rflag FROM tbl_smsbid_temp WHERE bcontractid='".$this->parentid."' ";
			$res_sms_promp 	= parent::execQuery($sel_sms_promp, $this->conn_temp);
			if($this->trace)
			{
				echo '<br>';
				echo '<pre>'.$sel_sms_promp;
				echo '<br>';
				echo '<br>'.mysql_num_rows($res_sms_promp);
				echo '<br>';
			}	
			if($res_sms_promp && mysql_num_rows($res_sms_promp)>0)
			{
				$del_sms_main   =  "delete from tbl_smsbid where bcontractid = '".$this->parentid."' ";
				$res_sms_main 	= parent::execQuery($del_sms_main, $this->conn_idc);
			    if($this->trace)
				{
					echo '<br>';
					echo '<pre>'.$del_sms_main;
					echo '<br>';
					echo '<br>'.$res_sms_main;
					echo '<br>';
				}
				$insert="INSERT INTO tbl_smsbid (BContractId,TContractId,Bid_Value,promo_txt,autoid,Tid,daily_sms,active_time,activeMap,new_promo,rflag,data_city)
						VALUES";
				if($res_sms_main)
				{
					while($row_sms_promp = mysql_fetch_assoc($res_sms_promp))
					{
						
							$insert_values[] = "('".$row_sms_promp['BContractId']."','".$row_sms_promp['TContractId']."','".$row_sms_promp['Bid_Value']."','".addslashes(str_replace("'","`",$row_sms_promp['promo_txt']))."',NULL,'".$row_sms_promp['Tid']."','".$row_sms_promp['daily_sms']."','".str_replace('#', ',', $row_sms_promp['active_time'])."','".$row_sms_promp['activeMap']."','".$row_sms_promp['new_promo']."','1','".addslashes($this->data_city)."')";
						
					}
					
					if(count($insert_values)>0)
						$insert_res = parent::execQuery($insert.implode(",",$insert_values), $this->conn_idc);
					
					if($this->trace)
					{
						echo '<br>';
						echo '<pre>'.$insert;
						echo '<br>';
						echo '<br>'.$insert_res;
						echo '<br>';
					}
					$returnarr['error']['code'] = 0;
					$returnarr['count'] 		= 'insert rows :: '.count($insert_values);
					$returnarr['res']	 		= $insert_res;
					$returnarr['msg']   		= 'success';
					
					
				}else
				{
					$returnarr['error']['code'] = 1;
					$returnarr['error']['msg'] = 'error in deleting existing data';
				}
				
			}else
			{
				$returnarr['error']['code'] = 1;
				$returnarr['error']['msg'] = 'no data found';
			}

					
	
			return $returnarr;
	}
	
	function fetchTempData()
	{
		$sel_sms_promp = "SELECT BContractId,TContractId,Bid_Value,promo_txt,autoid,Tid,daily_sms,active_time,activeMap,new_promo,rflag FROM tbl_smsbid_temp WHERE bcontractid='".$this->parentid."' ";
		$res_sms_promp 	= parent::execQuery($sel_sms_promp, $this->conn_temp);
		if($this->trace)
		{
			echo '<br>';
			echo '<pre>'.$sel_sms_promp;
			echo '<br>';
		    echo '<br>'.mysql_num_rows($res_sms_promp);
		    echo '<br>';
		}
		
		$sql_min_budget = "SELECT cityminbudget, c2s_min_budget FROM tbl_business_uploadrates  WHERE city='".$this->data_city."'";
		$res_min_budget = parent::execQuery($sql_min_budget, $this->conn_local);
		if($res_min_budget && mysql_num_rows($res_min_budget))
		{
		   $row_min_budget = mysql_fetch_assoc($res_min_budget);
		 
		}
		
		if($res_sms_promp && mysql_num_rows($res_sms_promp)>0)
		{
			
			
			$sms_promo_data = array();
			while($row_sms_promp = mysql_fetch_assoc($res_sms_promp))
			{
				$selected_ids[] = $row_sms_promp['TContractId'];
				
				$sms_promo_data[$this->parentid]['cityminbudget'] = $row_min_budget['c2s_min_budget'];
				
				$sms_promo_data[$this->parentid]['bidding_data'][$row_sms_promp['TContractId']] = $row_sms_promp;
				$sms_promo_data[$this->parentid]['bid_timming'] = str_replace('#', ',', $row_sms_promp['active_time']);
			}	
			
			$row_get = array();
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['table'] 		= 'gen_info_id';
			$cat_params['module'] 		= $this->module;
			$cat_params['parentid'] 	= implode(",",$selected_ids);
			$cat_params['action'] 		= 'fetchdata';
			$cat_params['fields']		= 'parentid,companyname,area';
			$cat_params['page']			= 'fetch_update_sms_promo_class';

			$res_gen_info1		= 	array();
			$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

			if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){

				$row_get 		=	$res_gen_info1['results']['data'];

				foreach ($row_get as $key => $value) {
					$selected_ids_data[$key]['compname'] = $value['companyname'];
					$selected_ids_data[$key]['areaname'] = $value['area'];
				}
			}
			if($this->trace)
			{
				echo '<br>comp params';
				echo '<pre>';print_r($cat_params);
				echo '<br>';
				echo 'API res';
				echo '<pre>';print_r($res_gen_info1);
			}

			/*$sql_get = "SELECT parentid, companyname, area FROM db_iro.tbl_companymaster_generalinfo WHERE parentid in ('".implode("','",$selected_ids)."')";
			$res_get = parent::execQuery($sql_get, $this->conn_local);
			if($res_get && mysql_num_rows($res_get))
			{
				while($row_get = mysql_fetch_assoc($res_get))
				{
					$selected_ids_data[$row_get['parentid']]['compname'] = $row_get['companyname'];
					$selected_ids_data[$row_get['parentid']]['areaname'] = $row_get['area'];
				}
			}*/
			
			if(count($sms_promo_data[$this->parentid]['bidding_data'])>0)
			{
				foreach($sms_promo_data[$this->parentid]['bidding_data'] as $key => $bidding_data)
				{
					$bidding_data['compname']  = $selected_ids_data[$bidding_data['TContractId']]['compname'];
					$bidding_data['area'] 	   = $selected_ids_data[$bidding_data['TContractId']]['areaname'];
					$bidding_data['bid']       = $bidding_data['Bid_Value'];
				    $bidding_data['ccnt']      = $bidding_data['daily_sms'];
					$sms_promo_data[$this->parentid]['bidding_data'][$key] = $bidding_data;
					$sms_promo_data[$this->parentid]['promo_text'] = $bidding_data['promo_txt'];
				}
			}
			//echo '<pre>';print_r($sms_promo_data);
			$sql_temp = "SELECT * FROM tbl_sms_promo_lite_tmp WHERE parentid='".$this->parentid."'";
			$res_temp = parent::execQuery($sql_temp, $this->conn_idc);
			if($this->trace)
			{
				echo '<br>';
				echo '<pre>'.$sql_temp;
				echo '<br>';
				echo '<br>'.mysql_num_rows($res_temp);
				echo '<br>';
			}
			
			if($res_temp && parent::numRows($res_temp)>0)
			{
				$row_temp 			 = parent::fetchData($res_temp);
				$calculated_data_arr = json_decode($row_temp['calculated_data'], true);
				$sms_promo_data[$this->parentid]['daily_threshold'] = $calculated_data_arr[$this->parentid]['daily_spend'];
				
			}
			
			$returnarr['error']['code'] = 0;
			$returnarr['data'] 		    = json_encode($sms_promo_data);
			$returnarr['msg']   		= 'success';
			
			
		}
		else
			{
				$returnarr['cityminbudget'] = $row_min_budget['c2s_min_budget'];
				$returnarr['error']['code'] = 1;
				$returnarr['error']['msg'] = 'no data found';
			}
			
			return $returnarr;
	}
	

}



?>
