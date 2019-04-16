<?php
class budget_additional_class extends DB
{
	var  $srchcity		= null;
	var  $conn_local   	= null;
	var  $params  		= null;
	
	function __construct()
	{
		global $db;
		$this->conn_local_slave = $db['remote']['d_jds']['slave'];
		$this->conn_local 		= $db['remote']['d_jds']['master'];
		$this->conn_idc   		= $db['remote']['idc']['master'];
	}
	function getCities($params)
	{
		$srchcity  	= $params['srchcity'];
		$campaign	= $params['campaign'];
		if($campaign == 'package'){
			$condition = " AND city not in ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') ";
		}
		$sqlFetchCity 	= "SELECT DISTINCT city FROM tbl_business_uploadrates WHERE city LIKE '".$srchcity."%' ".$condition." LIMIT 10";
		$resFetchCity   = parent::execQuery($sqlFetchCity, $this->conn_local_slave);
		if($resFetchCity && parent::numRows($resFetchCity)>0)
		{
			while($row_city = parent::fetchData($resFetchCity))
			{
				$responseArr['data'][] = strtolower($row_city['city']);
				$responseArr['errorcode']  	= 0;
				$responseArr['errormsg']  	= "data found";
			}
		}else {
			$responseArr['errorcode']  	= 1;
			$responseArr['errormsg']  	= "No data found";
		}
		return $responseArr;
	}
	function getBudgetLog($params)
	{
		$pgno = trim($params['pgno']);
		$limit = 200;
		if(empty($pgno)) $pgno = 1;
		$ps = (($pgno-1)*$limit);
		
		$campaign = trim($params['campaign']);
		$sqlBudgetLog = "SELECT campaign_name,city_name,ucode,uname,insertdate,log_str FROM online_regis1.tbl_budgetchange_log_idc WHERE campaign_name = '".$campaign."' AND log_str IS NOT NULL ORDER BY insertdate DESC LIMIT $ps, $limit";
		$resBudgetLog = parent::execQuery($sqlBudgetLog, $this->conn_idc);
		if($resBudgetLog && parent::numRows($resBudgetLog)>0)
		{
			$i = 0;
			while($row_budget_log = parent::fetchData($resBudgetLog))
			{
				$log_str 	= trim($row_budget_log['log_str']);
				$log_arr	= json_decode($log_str,true);
				if($log_arr['C'] == 1) // changes done check
				{
					$log_action_arr = array();
					$log_action_arr = $this->getLogActionInfo($campaign,$log_arr['A']);	
									
					switch(strtolower($campaign))
					{
						case 'package' 	:
						case 'pdg'		:
							foreach($log_arr['O'] as $log_key => $log_val)
							{
								$log_action_name = $log_action_arr[$log_key];
								if($log_action_name && isset($log_arr['N'][$log_key]))
								{
									if($log_key == 'TW' || $log_key == 'ADPKG')
									{
										if($log_key == 'TW')
										{
											foreach($log_val as $teamabbr => $teambudget)
											{
												$old_value = intval($teambudget);
												$new_value = intval($log_arr['N']['TW'][$teamabbr]);
												if($old_value !=$new_value)
												{
													$teamname = $this->teamInfo($teamabbr);
													$responseArr['data'][$i]['L'][$log_action_name][$teamname]['O'] = $old_value;
													$responseArr['data'][$i]['L'][$log_action_name][$teamname]['N'] = $new_value;
												}
											}
										}
										else if($log_key == 'ADPKG')
										{
											foreach($log_val as $campid => $campbudget)
											{
												$old_value = intval($campbudget);
												$new_value = intval($log_arr['N']['ADPKG'][$campid]);
												if($old_value !=$new_value)
												{
													$campname = $this->campIdInfo($campid);
													$responseArr['data'][$i]['L'][$log_action_name][$campname]['O'] = $old_value;
													$responseArr['data'][$i]['L'][$log_action_name][$campname]['N'] = $new_value;
												}
											}
										}
									}
									else
									{
										if($log_key == 'DISCOUNT'){
											$old_value = $log_val;
											$new_value = $log_arr['N'][$log_key];
										}else{
											$old_value = intval($log_val);
											$new_value = intval($log_arr['N'][$log_key]);
										}
										if($old_value !=$new_value)
										{
											$responseArr['data'][$i]['L'][$log_action_name]['O'] = $old_value;
											$responseArr['data'][$i]['L'][$log_action_name]['N'] = $new_value;
										}
									}
								}
							}
							$responseArr['data'][$i]['ac'] 	= trim($log_arr['A']); // action
							$responseArr['data'][$i]['ct'] 	= $this->formatString($row_budget_log['city_name']);
							$responseArr['data'][$i]['un'] 	= $this->formatString($row_budget_log['uname'])."(".trim($row_budget_log['ucode']).")";
							$responseArr['data'][$i]['dt'] 	= trim($row_budget_log['insertdate']);
							$i++;
						break;
						case 'jdrr' 	:
						case 'banner' 	:
						case 'national listing' :
						case 'omni 1'	:
							foreach($log_arr['O'] as $log_key => $log_val)
							{
								$log_action_name = $log_action_arr[$log_key];
								if($log_action_name && isset($log_arr['N'][$log_key]))
								{
									$old_value = intval($log_val);
									$new_value = intval($log_arr['N'][$log_key]);
									if($old_value !=$new_value)
									{
										$responseArr['data'][$i]['L'][$log_action_name]['O'] = $old_value;
										$responseArr['data'][$i]['L'][$log_action_name]['N'] = $new_value;
									}
								}
							}
							$responseArr['data'][$i]['ac'] 	= trim($log_arr['A']); // action
							$responseArr['data'][$i]['ct'] 	= $this->formatString($row_budget_log['city_name']);
							$responseArr['data'][$i]['un'] 	= $this->formatString($row_budget_log['uname'])."(".trim($row_budget_log['ucode']).")";
							$responseArr['data'][$i]['dt'] 	= trim($row_budget_log['insertdate']);
							$i++;
						break;
						case 'position availability' :
							foreach($log_arr['O'] as $log_key => $log_val)
							{
								$log_action_name = $log_action_arr[$log_key];
								if($log_action_name)
								{
									foreach($log_val as $position_flag => $active_flag)
									{
										$old_value = intval($active_flag);
										$new_value = intval($log_arr['N'][$log_key][$position_flag]);
										if($old_value !=$new_value)
										{
											$responseArr['data'][$i]['L'][$position_flag]['O'] = $old_value;
											$responseArr['data'][$i]['L'][$position_flag]['N'] = $new_value;
										}
									}
								}
							}
							$responseArr['data'][$i]['ac'] 	= trim($log_arr['A']); // action
							$responseArr['data'][$i]['ct'] 	= $this->formatString($row_budget_log['city_name']);
							$responseArr['data'][$i]['un'] 	= $this->formatString($row_budget_log['uname'])."(".trim($row_budget_log['ucode']).")";
							$responseArr['data'][$i]['dt'] 	= trim($row_budget_log['insertdate']);
							$i++;
						break;
					}
					
				}
			}
			$responseArr['errorcode']  	= 0;
			$responseArr['errormsg']  	= "data found";
		}else {
			$responseArr['errorcode']  	= 1;
			$responseArr['errormsg']  	= "No data found";
		}
		return $responseArr;
	}
	function getLogActionInfo($campaign,$action)
	{
		$action_arr = array();
		$campaign 	= strtolower($campaign);
		switch(strtolower($campaign))
		{
			case 'package' 	:
				$action_arr['TW'] 		= 'Team Wise';
				$action_arr['TOP'] 		= 'Top 200 Category';
				$action_arr['NORM'] 	= 'Normal Category';
				$action_arr['MINI'] 	= 'Flexi Upfront';
				$action_arr['MINIECS'] 	= 'Flexi ECS Monthly';
				$action_arr['PREMIUM'] 	= 'Package Premium ECS';
				$action_arr['PREMUPFRNT'] = 'Package Premium Upfront';
				$action_arr['EXPD'] 	= 'Expire Eligibility Days';
				$action_arr['EXP1'] 	= 'Package Expiry One Year';
				$action_arr['EXP2'] 	= 'Package Expiry Two Year';
				$action_arr['CUSTOM'] 	= 'Package Custom Minimum';
				$action_arr['DISCOUNT'] = 'Package Discount';
				$action_arr['ELIGIB'] 	= 'Discount Eligibility';
				$action_arr['FLXCUSTOM'] = 'Flexi Custom Minimum';
				$action_arr['ADPKG'] 	= 'Premium Ad';
			break;
			case 'pdg' 		:
				$action_arr['TW'] 		= 'Team Wise';
				$action_arr['TOP'] 		= 'Top 200 Category';
				$action_arr['NORM'] 	= 'Normal Category';
				$action_arr['DISCOUNT'] = 'PDG Discount';
				$action_arr['ELIGIB'] 	= 'Discount Eligibility';
			break;
			case 'jdrr' 	: 
				$action_arr['UPFRNT'] 	= 'Upfront';
			break;
			case 'banner' 	: 
				$action_arr['UPFRNT'] 	= 'Upfront';
				$action_arr['ECS'] 		= 'ECS';
			break;
			case 'national listing' : 
				$action_arr['natbdgt'] 	= 'National Listing';
			break;
			case 'omni 1' :
				$action_arr['upfrontstatus'] 	= 'Upfront Status';
				$action_arr['ecsstatus'] 		= 'ECS Status';
			break;
			case 'position availability' :
				$action_arr['POS'] 	= 'Position';
			break;
		}
		
		return $action_arr;
	}
	function getBudgetZoneWise($main_zone){
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$sqlBudgetZoneWise = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."')";
			$resBudgetZoneWise = parent::execQuery($sqlBudgetZoneWise, $this->conn_local);
			if($resBudgetZoneWise && parent::numRows($resBudgetZoneWise)>0){
				$i = 0;
				while($row_budget_zonewise 		= parent::fetchData($resBudgetZoneWise))
				{
					$city 					= trim($row_budget_zonewise['city']);
					$city					= ucwords(strtolower($city));
					$top_minbudget_package 	= trim($row_budget_zonewise['top_minbudget_package']);
					$minbudget_package 		= trim($row_budget_zonewise['minbudget_package']);
					$package_mini 			= trim($row_budget_zonewise['package_mini']);
					$extra_package_details 		= trim($row_budget_zonewise['extra_package_details']);
					$top_minbudget_fp 		= trim($row_budget_zonewise['top_minbudget_fp']);
					$minbudget_fp 			= trim($row_budget_zonewise['minbudget_fp']);
					$minbudget_national 	= trim($row_budget_zonewise['minbudget_national']);
					$extra_package_details_arr 	= json_decode($extra_package_details,true);
					if(count($extra_package_details_arr)>0){
						$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
					}
					$resultArr['data'][$i]['city']		= $city;
					$resultArr['data'][$i]['toppkg']  	= $top_minbudget_package;
					$resultArr['data'][$i]['normpkg']  	= $minbudget_package;
					$resultArr['data'][$i]['pkgmini']  	= $package_mini;
					$resultArr['data'][$i]['pkgprem']  	= $package_premium;
					$resultArr['data'][$i]['toppdg']  	= $top_minbudget_fp;
					$resultArr['data'][$i]['normpdg']  	= $minbudget_fp;
					$resultArr['data'][$i]['national']  = $minbudget_national;
					$i++;
				}
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getBudgetStateWise($state_name){
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$sqlBudgetStateWise = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."')";
			$resBudgetStateWise = parent::execQuery($sqlBudgetStateWise, $this->conn_local);
			if($resBudgetStateWise && parent::numRows($resBudgetStateWise)>0){
				$i = 0;
				while($row_budget_statewise 		= parent::fetchData($resBudgetStateWise))
				{
					$city 					= trim($row_budget_statewise['city']);
					$city					= ucwords(strtolower($city));
					$top_minbudget_package 	= trim($row_budget_statewise['top_minbudget_package']);
					$minbudget_package 		= trim($row_budget_statewise['minbudget_package']);
					$package_mini 			= trim($row_budget_statewise['package_mini']);
					$extra_package_details 	= trim($row_budget_statewise['extra_package_details']);
					$top_minbudget_fp 		= trim($row_budget_statewise['top_minbudget_fp']);
					$minbudget_fp 			= trim($row_budget_statewise['minbudget_fp']);
					$minbudget_national 	= trim($row_budget_statewise['minbudget_national']);
					$extra_package_details_arr 	= json_decode($extra_package_details,true);
					if(count($extra_package_details_arr)>0){
						$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
					}
					
					$resultArr['data'][$i]['city']		= $city;
					$resultArr['data'][$i]['toppkg']  	= $top_minbudget_package;
					$resultArr['data'][$i]['normpkg']  	= $minbudget_package;
					$resultArr['data'][$i]['pkgmini']  	= $package_mini;
					$resultArr['data'][$i]['pkgprem']  	= $package_premium;
					$resultArr['data'][$i]['toppdg']  	= $top_minbudget_fp;
					$resultArr['data'][$i]['normpdg']  	= $minbudget_fp;
					$resultArr['data'][$i]['national']  = $minbudget_national;
					$i++;
				}
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier1BudgetDetails(){
		$resultArr = array();
		$sqlTier1BudgetDetails = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
		$resTier1BudgetDetails = parent::execQuery($sqlTier1BudgetDetails, $this->conn_local);
		if($resTier1BudgetDetails && parent::numRows($resTier1BudgetDetails)>0){
			$i = 0;
			while($row_budget_tier1 		= parent::fetchData($resTier1BudgetDetails))
			{
				$city 					= trim($row_budget_tier1['city']);
				$city					= ucwords(strtolower($city));
				$top_minbudget_package 	= trim($row_budget_tier1['top_minbudget_package']);
				$minbudget_package 		= trim($row_budget_tier1['minbudget_package']);
				$package_mini 			= trim($row_budget_tier1['package_mini']);
				$extra_package_details 	= trim($row_budget_tier1['extra_package_details']);
				$top_minbudget_fp 		= trim($row_budget_tier1['top_minbudget_fp']);
				$minbudget_fp 			= trim($row_budget_tier1['minbudget_fp']);
				$minbudget_national 	= trim($row_budget_tier1['minbudget_national']);
				
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
				}
				
				$resultArr['data'][$i]['city']		= $city;
				$resultArr['data'][$i]['toppkg']  	= $top_minbudget_package;
				$resultArr['data'][$i]['normpkg']  	= $minbudget_package;
				$resultArr['data'][$i]['pkgmini']  	= $package_mini;
				$resultArr['data'][$i]['pkgprem']  	= $package_premium;
				$resultArr['data'][$i]['toppdg']  	= $top_minbudget_fp;
				$resultArr['data'][$i]['normpdg']  	= $minbudget_fp;
				$resultArr['data'][$i]['national']  = $minbudget_national;
				$i++;
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier2BudgetDetails(){
		$resultArr = array();
		$sqlTier2BudgetDetails = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE  tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
		$resTier2BudgetDetails = parent::execQuery($sqlTier2BudgetDetails, $this->conn_local);
		if($resTier2BudgetDetails && parent::numRows($resTier2BudgetDetails)>0){
			$i = 0;
			while($row_budget_tier2 		= parent::fetchData($resTier2BudgetDetails))
			{
				$city 					= trim($row_budget_tier2['city']);
				$city					= ucwords(strtolower($city));
				$top_minbudget_package 	= trim($row_budget_tier2['top_minbudget_package']);
				$minbudget_package 		= trim($row_budget_tier2['minbudget_package']);
				$package_mini 			= trim($row_budget_tier2['package_mini']);
				$extra_package_details 	= trim($row_budget_tier2['extra_package_details']);
				$top_minbudget_fp 		= trim($row_budget_tier2['top_minbudget_fp']);
				$minbudget_fp 			= trim($row_budget_tier2['minbudget_fp']);
				$minbudget_national 	= trim($row_budget_tier2['minbudget_national']);
				
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
				}
				
				$resultArr['data'][$i]['city']		= $city;
				$resultArr['data'][$i]['toppkg']  	= $top_minbudget_package;
				$resultArr['data'][$i]['normpkg']  	= $minbudget_package;
				$resultArr['data'][$i]['pkgmini']  	= $package_mini;
				$resultArr['data'][$i]['pkgprem']  	= $package_premium;
				$resultArr['data'][$i]['toppdg']  	= $top_minbudget_fp;
				$resultArr['data'][$i]['normpdg']  	= $minbudget_fp;
				$resultArr['data'][$i]['national']  = $minbudget_national;
				$i++;
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3BudgetDetails($params){
		
		$pgno = trim($params['pgno']);
		$limit = 25;
		if(empty($pgno)) $pgno = 1;
		$ps = (($pgno-1)*$limit);
		
		$resultArr = array();
		$sqlTier3BudgetDetails = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') ORDER BY city LIMIT $ps, $limit";
		$resTier3BudgetDetails = parent::execQuery($sqlTier3BudgetDetails, $this->conn_local);
		if($resTier3BudgetDetails && parent::numRows($resTier3BudgetDetails)>0)
		{
			$i = 0;
			while($row_budget_tier3 		= parent::fetchData($resTier3BudgetDetails))
			{
				$city 					= trim($row_budget_tier3['city']);
				$city					= ucwords(strtolower($city));
				$top_minbudget_package 	= trim($row_budget_tier3['top_minbudget_package']);
				$minbudget_package 		= trim($row_budget_tier3['minbudget_package']);
				$package_mini 			= trim($row_budget_tier3['package_mini']);
				$extra_package_details 		= trim($row_budget_tier3['extra_package_details']);
				$top_minbudget_fp 		= trim($row_budget_tier3['top_minbudget_fp']);
				$minbudget_fp 			= trim($row_budget_tier3['minbudget_fp']);
				$minbudget_national 	= trim($row_budget_tier3['minbudget_national']);
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
				}
				
				$resultArr['data'][$i]['city']		= $city;
				$resultArr['data'][$i]['toppkg']  	= $top_minbudget_package;
				$resultArr['data'][$i]['normpkg']  	= $minbudget_package;
				$resultArr['data'][$i]['pkgmini']  	= $package_mini;
				$resultArr['data'][$i]['pkgprem']  	= $package_premium;
				$resultArr['data'][$i]['toppdg']  	= $top_minbudget_fp;
				$resultArr['data'][$i]['normpdg']  	= $minbudget_fp;
				$resultArr['data'][$i]['national']  = $minbudget_national;
				$i++;
			}
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function exportToExcel($params)
	{
		$table = '';
		$request = trim($params['request']);
		if($request == 'zonewise')
		{
			$main_zone = trim($params['main_zone']);
			$filename="zonewise";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$table  =",,,     ".$main_zone." Zone Budget   ,,,\r\n";
			$table .= "City Name, Package Mini Budget, Premium Ad Pkg Yrly, Package Top 200 Budget, Package Normal Budget, PDG Top 200 Budget, PDG Normal Budget, National Listing Budget\r\n";
			$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
			if(count($zonewise_cityarr)>0){
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$sqlBudgetZoneWise = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."')";
				$resBudgetZoneWise = parent::execQuery($sqlBudgetZoneWise, $this->conn_local);
				if($resBudgetZoneWise && parent::numRows($resBudgetZoneWise)>0)
				{
					while($row_budget_zonewise 	= parent::fetchData($resBudgetZoneWise))
					{
						$city 					= trim($row_budget_zonewise['city']);
						$city					= ucwords(strtolower($city));
						$package_mini 			= trim($row_budget_zonewise['package_mini']);
						$extra_package_details 	= trim($row_budget_zonewise['extra_package_details']);
						$top_minbudget_package 	= trim($row_budget_zonewise['top_minbudget_package']);
						$minbudget_package 		= trim($row_budget_zonewise['minbudget_package']);
						$top_minbudget_fp 		= trim($row_budget_zonewise['top_minbudget_fp']);
						$minbudget_fp 			= trim($row_budget_zonewise['minbudget_fp']);
						$minbudget_national 	= trim($row_budget_zonewise['minbudget_national']);
						
						$extra_package_details_arr 	= json_decode($extra_package_details,true);
						if(count($extra_package_details_arr)>0){
							$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
						}
						
						$table.=str_replace(","," ",$city).",".str_replace(","," ",$package_mini).",".str_replace(","," ",$package_premium).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$top_minbudget_fp).",".str_replace(","," ",$minbudget_fp).",".str_replace(","," ",$minbudget_national).","."\r\n";
					}
					$table = str_replace(",","\t",$table);
					echo $table;
				}
			}
		}
		else if($request == 'statewise')
		{
			$state_name = trim($params['state_name']);
			$filename="statewise";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$table  =",,,     ".$state_name." State Budget   ,,,\r\n";
			$table .= "City Name, Package Mini Budget, Premium Ad Pkg Yrly, Package Top 200 Budget, Package Normal Budget, PDG Top 200 Budget, PDG Normal Budget, National Listing Budget\r\n";
			$statewise_cityarr = $this->stateWiseCityList($state_name);
			if(count($statewise_cityarr)>0){
				$statewise_citystr = implode("','",$statewise_cityarr);
				$sqlBudgetStateWise = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."')";
				$resBudgetStateWise = parent::execQuery($sqlBudgetStateWise, $this->conn_local);
				if($resBudgetStateWise && parent::numRows($resBudgetStateWise)>0)
				{
					while($row_budget_statewise = parent::fetchData($resBudgetStateWise))
					{
						$city 					= trim($row_budget_statewise['city']);
						$city					= ucwords(strtolower($city));
						$package_mini 			= trim($row_budget_statewise['package_mini']);
						$extra_package_details 	= trim($row_budget_statewise['extra_package_details']);
						$top_minbudget_package 	= trim($row_budget_statewise['top_minbudget_package']);
						$minbudget_package 		= trim($row_budget_statewise['minbudget_package']);
						$top_minbudget_fp 		= trim($row_budget_statewise['top_minbudget_fp']);
						$minbudget_fp 			= trim($row_budget_statewise['minbudget_fp']);
						$minbudget_national 	= trim($row_budget_statewise['minbudget_national']);
						$extra_package_details_arr 	= json_decode($extra_package_details,true);
						if(count($extra_package_details_arr)>0){
							$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
						}
						
						$table.=str_replace(","," ",$city).",".str_replace(","," ",$package_mini).",".str_replace(","," ",$package_premium).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$top_minbudget_fp).",".str_replace(","," ",$minbudget_fp).",".str_replace(","," ",$minbudget_national).","."\r\n";
					}
					$table = str_replace(",","\t",$table);
					echo $table;
				}
			}
		}
		else if($request == 'tier1')
		{
			$filename="top11_cities";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$table  =",,,     Top 11 Cities Budget   ,,,\r\n";
			$table .= "City Name, Package Mini Budget, Premium Ad Pkg Yrly, Package Top 200 Budget, Package Normal Budget, PDG Top 200 Budget, PDG Normal Budget, National Listing Budget\r\n";
			
			$sqlBudgetTier1 = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE city IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
			$resBudgetTier1 = parent::execQuery($sqlBudgetTier1, $this->conn_local);
			if($resBudgetTier1 && parent::numRows($resBudgetTier1)>0)
			{
				while($row_budget_tier1 	= parent::fetchData($resBudgetZoneWise))
				{
					$city 					= trim($row_budget_tier1['city']);
					$city					= ucwords(strtolower($city));
					$package_mini 			= trim($row_budget_tier1['package_mini']);
					$extra_package_details 	= trim($row_budget_tier1['extra_package_details']);
					$top_minbudget_package 	= trim($row_budget_tier1['top_minbudget_package']);
					$minbudget_package 		= trim($row_budget_tier1['minbudget_package']);
					$top_minbudget_fp 		= trim($row_budget_tier1['top_minbudget_fp']);
					$minbudget_fp 			= trim($row_budget_tier1['minbudget_fp']);
					$minbudget_national 	= trim($row_budget_tier1['minbudget_national']);
					$extra_package_details_arr 	= json_decode($extra_package_details,true);
					if(count($extra_package_details_arr)>0){
						$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
					}
					
					$table.=str_replace(","," ",$city).",".str_replace(","," ",$package_mini).",".str_replace(","," ",$package_premium).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$top_minbudget_fp).",".str_replace(","," ",$minbudget_fp).",".str_replace(","," ",$minbudget_national).","."\r\n";
				}
				$table = str_replace(",","\t",$table);
				echo $table;
			}
		}
		else if($request == 'tier2')
		{
			$filename="tier2";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$table  =",,,     Tier 2 Cities Budget   ,,,\r\n";
			$table .= "City Name, Package Mini Budget, Premium Ad Pkg Yrly, Package Top 200 Budget, Package Normal Budget, PDG Top 200 Budget, PDG Normal Budget, National Listing Budget\r\n";
			$sqlBudgetTier2 = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
			$resBudgetTier2 = parent::execQuery($sqlBudgetTier2, $this->conn_local);
			if($resBudgetTier2 && parent::numRows($resBudgetTier2)>0)
			{
				while($row_budget_tier2 	= parent::fetchData($resBudgetZoneWise))
				{
					$city 					= trim($row_budget_tier2['city']);
					$city					= ucwords(strtolower($city));
					$package_mini 			= trim($row_budget_tier2['package_mini']);
					$extra_package_details 	= trim($row_budget_tier2['extra_package_details']);
					$top_minbudget_package 	= trim($row_budget_tier2['top_minbudget_package']);
					$minbudget_package 		= trim($row_budget_tier2['minbudget_package']);
					$top_minbudget_fp 		= trim($row_budget_tier2['top_minbudget_fp']);
					$minbudget_fp 			= trim($row_budget_tier2['minbudget_fp']);
					$minbudget_national 	= trim($row_budget_tier2['minbudget_national']);
					$extra_package_details_arr 	= json_decode($extra_package_details,true);
					if(count($extra_package_details_arr)>0){
						$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
					}
					
					$table.=str_replace(","," ",$city).",".str_replace(","," ",$package_mini).",".str_replace(","," ",$package_premium).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$top_minbudget_fp).",".str_replace(","," ",$minbudget_fp).",".str_replace(","," ",$minbudget_national).","."\r\n";
				}
				$table = str_replace(",","\t",$table);
				echo $table;
			}
		}
		else if($request == 'tier3')
		{
			$filename="package_remote_tier3";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$table  =",,,     Tier 3 Cities Budget   ,,,\r\n";
			$table .= "City Name, Package Mini Budget, Premium Ad Pkg Yrly, Package Top 200 Budget, Package Normal Budget, PDG Top 200 Budget, PDG Normal Budget, National Listing Budget\r\n";
			$sqlBudgetTier3 = "SELECT city,top_minbudget_package,minbudget_package,package_mini,extra_package_details,top_minbudget_fp,minbudget_fp,minbudget_national FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') ORDER BY city";
			$resBudgetTier3 = parent::execQuery($sqlBudgetTier3, $this->conn_local);
			if($resBudgetTier3 && parent::numRows($resBudgetTier3)>0)
			{
				while($row_budget_tier3 	= parent::fetchData($resBudgetZoneWise))
				{
					$city 					= trim($row_budget_tier3['city']);
					$city					= ucwords(strtolower($city));
					$package_mini 			= trim($row_budget_tier3['package_mini']);
					$extra_package_details 	= trim($row_budget_tier3['extra_package_details']);
					$top_minbudget_package 	= trim($row_budget_tier3['top_minbudget_package']);
					$minbudget_package 		= trim($row_budget_tier3['minbudget_package']);
					$top_minbudget_fp 		= trim($row_budget_tier3['top_minbudget_fp']);
					$minbudget_fp 			= trim($row_budget_tier3['minbudget_fp']);
					$minbudget_national 	= trim($row_budget_tier3['minbudget_national']);
					$extra_package_details_arr 	= json_decode($extra_package_details,true);
					if(count($extra_package_details_arr)>0){
						$package_premium = $extra_package_details_arr['116']['package_value'] * 12;
					}
					
					$table.=str_replace(","," ",$city).",".str_replace(","," ",$package_mini).",".str_replace(","," ",$package_premium).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$top_minbudget_fp).",".str_replace(","," ",$minbudget_fp).",".str_replace(","," ",$minbudget_national).","."\r\n";
				}
				$table = str_replace(",","\t",$table);
				echo $table;
			}
		}
	}
	function getStateNames(){
		$resultArr = array();
		$state_list_arr = array();
		$sqlFindStateList = "SELECT GROUP_CONCAT(DISTINCT state_name ORDER by state_name SEPARATOR '|') as statelist FROM city_master WHERE state_name !='' AND state_name NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resFindStateList = parent::execQuery($sqlFindStateList, $this->conn_local);
		if($resFindStateList && parent::numRows($resFindStateList)>0){
			$row_state_list = parent::fetchData($resFindStateList);
			$state_list	= trim($row_state_list['statelist']);
			$state_list_arr = explode("|",$state_list);
		}
		if(count($state_list_arr)>0){
			$resultArr['statelist'] = $state_list_arr;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;

	}
	function zoneWiseCityList($main_zone)
	{
		$zone_cities_arr = array();
		$sqlZoneCityList = "SELECT GROUP_CONCAT(Cities SEPARATOR '|') as zonecity FROM tbl_zone_cities WHERE main_zone = '".$main_zone."' AND Cities NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resZoneCityList = parent::execQuery($sqlZoneCityList, $this->conn_local);
		if($resZoneCityList && parent::numRows($resZoneCityList)>0)
		{
			$row_zone_citylist = parent::fetchData($resZoneCityList);
			$zonecity 			= trim($row_zone_citylist['zonecity']);
			$zone_cities_arr = explode("|",$zonecity);
		}
		return $zone_cities_arr;
	}
	function stateWiseCityList($state_name)
	{
		$state_cities_arr = array();
		$sqlStateCityList = "SELECT GROUP_CONCAT(ct_name SEPARATOR '|') as statecity FROM city_master WHERE state_name = '".$state_name."' AND ct_name NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resStateCityList = parent::execQuery($sqlStateCityList, $this->conn_local);
		if($resStateCityList && parent::numRows($resStateCityList)>0)
		{
			$row_state_citylist = parent::fetchData($resStateCityList);
			$statecity 			= trim($row_state_citylist['statecity']);
			$state_cities_arr = explode("|",$statecity);
		}
		return $state_cities_arr;
	}
	function teamInfo($team_abbr)
	{
		$team_name = '';
		$sqlTeamInfo = "SELECT team_name,team_abbr FROM online_regis1.tbl_team_info WHERE team_abbr = '".$team_abbr."'";
		$resTeamInfo = parent::execQuery($sqlTeamInfo, $this->conn_idc);
		if($resTeamInfo && parent::numRows($resTeamInfo)>0)
		{
			$row_teaminfo = parent::fetchData($resTeamInfo);
			$team_name 	  = trim($row_teaminfo['team_name']);
		}
		return $team_name;
	}
	function campIdInfo($campaignid)
	{
		$campname = '';
		$sqlExtraPkgDetails = "SELECT name FROM online_regis1.tbl_campaignid_mapping WHERE campaignid = '".$campaignid."'";
		$resExtraPkgDetails = parent::execQuery($sqlExtraPkgDetails, $this->conn_idc);
		if($resExtraPkgDetails && parent::numRows($resExtraPkgDetails)>0){
			$row_extrapkg = parent::fetchData($resExtraPkgDetails);
			$campname	= trim($row_extrapkg['name']);
			$campname	= str_ireplace("Package","",$campname);
			$campname	= trim($campname);
		}
		return $campname;
	}
	function formatString($string)
	{
		$string = trim($string);
		if(($string !=null) && (!empty($string))){
			return ucwords(strtolower($string));
		}else{
			return $string;
		}
	}
}
?>
