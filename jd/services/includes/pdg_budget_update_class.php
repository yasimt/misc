<?php
class pdg_budget_update_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'kolkata', 'bangalore', 'chennai', 'pune', 'hyderabad', 'ahmedabad');

	var  $action		= null;
	var  $data_city		= null;
	var  $teaminfo_arr	= array();
	var  $all_cities_arr= array();
	
	
	function __construct($params)
	{
		$action 			= trim($params['action']);
		$data_city 			= trim($params['data_city']);
		if(trim($action)=='')
        {
            $message = "Action is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
        {
            $message = "Data City is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->action  		= $action;
		$this->data_city 	= $data_city;
		$this->setServers();
		$this->teaminfo_arr = $this->teamInfo();
		$this->tier2_citylist = $this->tier2CityList();
		$this->tier3_citylist = $this->tier3CityList();
		$this->all_cities_arr = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		

	}
	function getTier1PDGBudget()
	{
		$resultArr = array();
		$sqlTier1BudgetPdg = "SELECT city,team_minbudget_fp,top_minbudget_fp,minbudget_fp,maxdiscount,discount_eligibility FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$resTier1BudgetPdg = parent::execQuery($sqlTier1BudgetPdg, $this->conn_local);
		if($resTier1BudgetPdg && parent::numRows($resTier1BudgetPdg)>0)
		{
			while($row_tier1_pdg = parent::fetchData($resTier1BudgetPdg))
			{
				$city 					= trim($row_tier1_pdg['city']);
				$city					= ucwords(strtolower($city));
				$minbudget_fp 			= trim($row_tier1_pdg['minbudget_fp']);
				$maxdiscount 			= floatval($row_tier1_pdg['maxdiscount']);
				$maxdiscount			= number_format($maxdiscount, 2);
				$discount_eligibility	= intval($row_tier1_pdg['discount_eligibility']);
				
				$top_minbudget_fp 	= trim($row_tier1_pdg['top_minbudget_fp']);
				$teambdgt_pdg 		= trim($row_tier1_pdg['team_minbudget_fp']);
				$teambdgt_pdg_arr 	= array();
				$teambdgt_pdg_arr 	= json_decode($teambdgt_pdg,true);
				if(count($teambdgt_pdg_arr)>0)
				{
					$resultArr['data'][$city] = $teambdgt_pdg_arr;
				}
				$resultArr['catbdgt'][$city]['top200']  = $top_minbudget_fp; // Top 200 Category Budget
				$resultArr['catbdgt'][$city]['normal']  = $minbudget_fp; // Normal Category Budget
				$resultArr['discount'][$city]['maxval'] = $maxdiscount; // PDG Maximum Discount In Percentage
				$resultArr['discount'][$city]['eligib'] = $discount_eligibility; // PDG Discount Eligibility
			}
		}
		if(count($resultArr)>0){
			$resultArr['teaminfo'] = $this->teaminfo_arr;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
		
	}
	function getTier2TeamBdgtPdg()
	{
		$resultArr = array();
		$sqlTier2TeamBdgtPdg = "SELECT team_minbudget_fp FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier2TeamBdgtPdg = parent::execQuery($sqlTier2TeamBdgtPdg, $this->conn_local);
		if($resTier2TeamBdgtPdg && parent::numRows($resTier2TeamBdgtPdg)>0)
		{
			$row_tier2_bdgt_pdg 	= parent::fetchData($resTier2TeamBdgtPdg);
			$tire2_teambdgt_pdg 	= trim($row_tier2_bdgt_pdg['team_minbudget_fp']);
			$tire2_teambdgt_pdg_arr = array();
			$tire2_teambdgt_pdg_arr = json_decode($tire2_teambdgt_pdg,true);
			if(count($tire2_teambdgt_pdg_arr)>0)
			{
				$resultArr['tier2data'] = $tire2_teambdgt_pdg_arr;
			}
		}
		
		if(count($resultArr)>0){
			$resultArr['teaminfo']  = $this->teaminfo_arr;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier2CatBdgtPdg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2CatBdgtPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,top_minbudget_fp,minbudget_fp,concat(top_minbudget_fp,minbudget_fp) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier2CatBdgtPdg = parent::execQuery($sqlTier2CatBdgtPdg, $this->conn_local);
		if($resTier2CatBdgtPdg && parent::numRows($resTier2CatBdgtPdg)>0)
		{
			$row_tier2_catbdgt_pdg 		= parent::fetchData($resTier2CatBdgtPdg);
			$tier2selcity 				= trim($row_tier2_catbdgt_pdg['tier2selcity']);
			$tire2_cat_top_minbdgt_pdg 	= trim($row_tier2_catbdgt_pdg['top_minbudget_fp']);
			$tire2_cat_norm_minbdgt_pdg = trim($row_tier2_catbdgt_pdg['minbudget_fp']);
			
			$resultArr['tier2top200'] 	= $tire2_cat_top_minbdgt_pdg; // Top 200 Category Budget
			$resultArr['tier2normal'] 	= $tire2_cat_norm_minbdgt_pdg; // Normal Category Budget
			
			$tier2selcity_arr = explode("|",$tier2selcity);
			$tier2mismatch_cityarr = array_diff($this->tier2_citylist,$tier2selcity_arr);
			$tier2mismatch_cityarr = $this->arrayProcess($tier2mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier2mismatch_cityarr)>0){
				$tier2mismatch_cityinfo =  $this->getMismatchCityBdgtInfo($tier2mismatch_cityarr);
				$resultArr['tire2mismatch'] = $tier2mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier2DiscountPdg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2DiscountPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,maxdiscount,discount_eligibility,concat(maxdiscount,discount_eligibility) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier2DiscountPdg = parent::execQuery($sqlTier2DiscountPdg, $this->conn_local);
		if($resTier2DiscountPdg && parent::numRows($resTier2DiscountPdg)>0)
		{
			$row_tier2_discount_pdg = parent::fetchData($resTier2DiscountPdg);
			$tier2selcity 			= trim($row_tier2_discount_pdg['tier2selcity']);
			$tire2_maxdisc_pdg 		= floatval($row_tier2_discount_pdg['maxdiscount']);
			$tire2_maxdisc_pdg		= number_format($tire2_maxdisc_pdg, 2);
			$tire2_disceligib_pdg 	= intval($row_tier2_discount_pdg['discount_eligibility']);			
			
			$resultArr['maxval'] 	= $tire2_maxdisc_pdg; 	 // Maximum Discount For PDG
			$resultArr['eligib'] 	= $tire2_disceligib_pdg; // Discount Eligibility For PDG
			
			$tier2selcity_arr = explode("|",$tier2selcity);
			$tier2mismatch_cityarr = array_diff($this->tier2_citylist,$tier2selcity_arr);
			$tier2mismatch_cityarr = $this->arrayProcess($tier2mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier2mismatch_cityarr)>0){
				$tier2mismatch_cityinfo =  $this->getMismatchDiscountPdg($tier2mismatch_cityarr);
				$resultArr['tire2discmismatch'] = $tier2mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3TeamBdgtPdg()
	{
		$resultArr = array();
		$sqlTier3TeamBdgtPdg = "SELECT team_minbudget_fp FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier3TeamBdgtPdg = parent::execQuery($sqlTier3TeamBdgtPdg, $this->conn_local);
		if($resTier3TeamBdgtPdg && parent::numRows($resTier3TeamBdgtPdg)>0)
		{
			$row_tier3_bdgt_pdg 	= parent::fetchData($resTier3TeamBdgtPdg);
			$tire3_teambdgt_pdg 	= trim($row_tier3_bdgt_pdg['team_minbudget_fp']);
			$tire3_teambdgt_pdg_arr = array();
			$tire3_teambdgt_pdg_arr = json_decode($tire3_teambdgt_pdg,true);
			if(count($tire3_teambdgt_pdg_arr)>0)
			{
				$resultArr['tier3data'] = $tire3_teambdgt_pdg_arr;
			}
		}
		if(count($resultArr)>0){
			$resultArr['teaminfo']  = $this->teaminfo_arr;
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3CatBdgtPdg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3CatBdgtPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,top_minbudget_fp,minbudget_fp,concat(top_minbudget_fp,minbudget_fp) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier3CatBdgtPdg = parent::execQuery($sqlTier3CatBdgtPdg, $this->conn_local);
		if($resTier3CatBdgtPdg && parent::numRows($resTier3CatBdgtPdg)>0)
		{
			$row_tier3_catbdgt_pdg 		= parent::fetchData($resTier3CatBdgtPdg);
			$tier3selcity 				= trim($row_tier3_catbdgt_pdg['tier3selcity']);
			$tire3_cat_top_minbdgt_pdg 	= trim($row_tier3_catbdgt_pdg['top_minbudget_fp']);
			$tire3_cat_norm_minbdgt_pdg = trim($row_tier3_catbdgt_pdg['minbudget_fp']);
			
			$resultArr['tier3top200'] 	= $tire3_cat_top_minbdgt_pdg; // Top 300 Category Budget
			$resultArr['tier3normal'] 	= $tire3_cat_norm_minbdgt_pdg; // Normal Category Budget
			
			$tier3selcity_arr = explode("|",$tier3selcity);
			$tier3mismatch_cityarr = array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr = $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				$tier3mismatch_cityinfo =  $this->getMismatchCityBdgtInfo($tier3mismatch_cityarr);
				$resultArr['tire3mismatch'] = $tier3mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3DiscountPdg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3DiscountPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,maxdiscount,discount_eligibility,concat(maxdiscount,discount_eligibility) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier3DiscountPdg = parent::execQuery($sqlTier3DiscountPdg, $this->conn_local);
		if($resTier3DiscountPdg && parent::numRows($resTier3DiscountPdg)>0)
		{
			$row_tier3_discount_pdg = parent::fetchData($resTier3DiscountPdg);
			$tier3selcity 			= trim($row_tier3_discount_pdg['tier3selcity']);
			$tire3_maxdisc_pdg 		= floatval($row_tier3_discount_pdg['maxdiscount']);
			$tire3_maxdisc_pdg		= number_format($tire3_maxdisc_pdg, 2);
			$tire3_disceligib_pdg 	= intval($row_tier3_discount_pdg['discount_eligibility']);			
			
			$resultArr['maxval'] 	= $tire3_maxdisc_pdg; 	 // Maximum Discount For PDG
			$resultArr['eligib'] 	= $tire3_disceligib_pdg; // Discount Eligibility For PDG
			
			$tier3selcity_arr = explode("|",$tier3selcity);
			$tier3mismatch_cityarr = array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr = $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				$tier3mismatch_cityinfo =  $this->getMismatchDiscountPdg($tier3mismatch_cityarr);
				$resultArr['tire3discmismatch'] = $tier3mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getZoneTeamBdgtPdg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$sqlZoneTeamBdgtPdg = "SELECT team_minbudget_fp FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') LIMIT 1";
			$resZoneTeamBdgtPdg = parent::execQuery($sqlZoneTeamBdgtPdg, $this->conn_local);
			if($resZoneTeamBdgtPdg && parent::numRows($resZoneTeamBdgtPdg)>0){
				$row_zone_bdgt_pdg 	= parent::fetchData($resZoneTeamBdgtPdg);
				$zone_teambdgt_pdg 	= trim($row_zone_bdgt_pdg['team_minbudget_fp']);
				$zone_teambdgt_pdg_arr = array();
				$zone_teambdgt_pdg_arr = json_decode($zone_teambdgt_pdg,true);
				if(count($zone_teambdgt_pdg_arr)>0)
				{
					$resultArr['zonedata'] = $zone_teambdgt_pdg_arr;
				}
			}
			if(count($resultArr)>0){
				$resultArr['teaminfo']  = $this->teaminfo_arr;
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getZoneCatBdgtPdg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();
			$sqlZoneCatBdgtPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,top_minbudget_fp,minbudget_fp,concat(top_minbudget_fp,minbudget_fp) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resZoneCatBdgtPdg = parent::execQuery($sqlZoneCatBdgtPdg, $this->conn_local);
			if($resZoneCatBdgtPdg && parent::numRows($resZoneCatBdgtPdg)>0){
				$row_zone_catbdgt_pdg 		= parent::fetchData($resZoneCatBdgtPdg);
				$zoneselcity 				= trim($row_zone_catbdgt_pdg['zoneselcity']);
				$zone_cat_top_minbdgt_pdg 	= trim($row_zone_catbdgt_pdg['top_minbudget_fp']);
				$zone_cat_norm_minbdgt_pdg  = trim($row_zone_catbdgt_pdg['minbudget_fp']);
				
				$resultArr['zonetop200'] 	= $zone_cat_top_minbdgt_pdg; // Top 300 Category Budget
				$resultArr['zonenormal'] 	= $zone_cat_norm_minbdgt_pdg; // Normal Category Budget
				
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->zoneWiseInUploadRates($zonewise_cityarr);
				$zonemismatch_cityarr = array_diff($zonewise_city_uploadrates,$zoneselcity_arr);
				$zonemismatch_cityarr = $this->arrayProcess($zonemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($zonemismatch_cityarr)>0){
					$zonemismatch_cityinfo =  $this->getMismatchCityBdgtInfo($zonemismatch_cityarr);
					$resultArr['zonemismatch'] = $zonemismatch_cityinfo;
				}
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getZoneDiscountPdg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();
			$sqlZoneDiscountPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,maxdiscount,discount_eligibility,concat(maxdiscount,discount_eligibility) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resZoneDiscountPdg = parent::execQuery($sqlZoneDiscountPdg, $this->conn_local);
			if($resZoneDiscountPdg && parent::numRows($resZoneDiscountPdg)>0){
				$row_zone_discount_pdg 	= parent::fetchData($resZoneDiscountPdg);
				$zoneselcity 			= trim($row_zone_discount_pdg['zoneselcity']);
				$zone_maxdisc_pdg 		= floatval($row_zone_discount_pdg['maxdiscount']);
				$zone_maxdisc_pdg		= number_format($zone_maxdisc_pdg, 2);
				$zone_disceligib_pdg 	= intval($row_zone_discount_pdg['discount_eligibility']);
							
				$resultArr['maxval'] 	= $zone_maxdisc_pdg; 	// Maximum Discount For PDG
				$resultArr['eligib'] 	= $zone_disceligib_pdg; // Discount Eligibility For PDG
				
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->zoneWiseInUploadRates($zonewise_cityarr);
				$zonemismatch_cityarr = array_diff($zonewise_city_uploadrates,$zoneselcity_arr);
				$zonemismatch_cityarr = $this->arrayProcess($zonemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($zonemismatch_cityarr)>0){
					$zonemismatch_cityinfo =  $this->getMismatchDiscountPdg($zonemismatch_cityarr);
					$resultArr['zonediscmismatch'] = $zonemismatch_cityinfo;
				}
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getStateTeamBdgtPdg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$sqlStateTeamBdgtPdg = "SELECT team_minbudget_fp FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') LIMIT 1";
			$resStateTeamBdgtPdg = parent::execQuery($sqlStateTeamBdgtPdg, $this->conn_local);
			if($resStateTeamBdgtPdg && parent::numRows($resStateTeamBdgtPdg)>0){
				$row_state_bdgt_pdg = parent::fetchData($resStateTeamBdgtPdg);
				$state_teambdgt_pdg 	= trim($row_state_bdgt_pdg['team_minbudget_fp']);
				$state_teambdgt_pdg_arr = array();
				$state_teambdgt_pdg_arr = json_decode($state_teambdgt_pdg,true);
				if(count($state_teambdgt_pdg_arr)>0)
				{
					$resultArr['statedata'] = $state_teambdgt_pdg_arr;
				}
			}
			if(count($resultArr)>0){
				$resultArr['teaminfo']  = $this->teaminfo_arr;
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getStateCatBdgtPdg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStateCatBdgtPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,top_minbudget_fp,minbudget_fp, concat(top_minbudget_fp,minbudget_fp) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resStateCatBdgtPdg = parent::execQuery($sqlStateCatBdgtPdg, $this->conn_local);
			if($resStateCatBdgtPdg && parent::numRows($resStateCatBdgtPdg)>0){
				$row_state_catbdgt_pdg 		= parent::fetchData($resStateCatBdgtPdg);
				$stateselcity 				= trim($row_state_catbdgt_pdg['stateselcity']);
				$state_cat_top_minbdgt_pdg 	= trim($row_state_catbdgt_pdg['top_minbudget_fp']);
				$state_cat_norm_minbdgt_pdg  = trim($row_state_catbdgt_pdg['minbudget_fp']);
				
				$resultArr['statetop200'] 	= $state_cat_top_minbdgt_pdg; // Top 300 Category Budget
				$resultArr['statenormal'] 	= $state_cat_norm_minbdgt_pdg; // Normal Category Budget
				
				$stateselcity_arr = explode("|",$stateselcity);
				$statewise_city_uploadrates = $this->cityCheckInUploadRates($statewise_cityarr);
				$statemismatch_cityarr = array_diff($statewise_city_uploadrates,$stateselcity_arr);
				$statemismatch_cityarr = $this->arrayProcess($statemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($statemismatch_cityarr)>0){
					$statemismatch_cityinfo =  $this->getMismatchCityBdgtInfo($statemismatch_cityarr);
					$resultArr['statemismatch'] = $statemismatch_cityinfo;
				}
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getStateDiscountPdg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStateDiscountPdg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,maxdiscount,discount_eligibility,concat(maxdiscount,discount_eligibility) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resStateDiscountPdg = parent::execQuery($sqlStateDiscountPdg, $this->conn_local);
			if($resStateDiscountPdg && parent::numRows($resStateDiscountPdg)>0){
				$row_state_discount_pdg = parent::fetchData($resStateDiscountPdg);
				$stateselcity 			= trim($row_state_discount_pdg['stateselcity']);
				$state_maxdiscount_pdg 	= floatval($row_state_discount_pdg['maxdiscount']);
				$state_maxdiscount_pdg	= number_format($state_maxdiscount_pdg, 2);
				$state_disc_eligib_pdg	= intval($row_state_discount_pdg['discount_eligibility']);
				
				$resultArr['maxval'] 	= $state_maxdiscount_pdg;
				$resultArr['eligib'] 	= $state_disc_eligib_pdg;
				
				$stateselcity_arr = explode("|",$stateselcity);
				$statewise_city_uploadrates = $this->cityCheckInUploadRates($statewise_cityarr);
				$statemismatch_cityarr = array_diff($statewise_city_uploadrates,$stateselcity_arr);
				$statemismatch_cityarr = $this->arrayProcess($statemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($statemismatch_cityarr)>0){
					$statemismatch_cityinfo =  $this->getMismatchDiscountPdg($statemismatch_cityarr);
					$resultArr['statediscmismatch'] = $statemismatch_cityinfo;
				}
				$resultArr['errorcode'] = 0;
			}else{
				$resultArr['errorcode'] = 1;
			}
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getRemoteBudgetPdg()
	{
		$resultArr = array();
		$sqlRemoteBudgetPdg = "SELECT top_minbudget_fp,minbudget_fp,maxdiscount,discount_eligibility FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$resRemoteBudgetPdg = parent::execQuery($sqlRemoteBudgetPdg, $this->conn_local);
		if($resRemoteBudgetPdg && parent::numRows($resRemoteBudgetPdg)>0)
		{
			$row_remote_bdgt_pdg 		= parent::fetchData($resRemoteBudgetPdg);
			$remote_top_minbudget_pdg 	= intval($row_remote_bdgt_pdg['top_minbudget_fp']);
			$remote_minbudget_pdg 		= intval($row_remote_bdgt_pdg['minbudget_fp']);
			$remote_maxdiscount_pdg 	= floatval($row_remote_bdgt_pdg['maxdiscount']);
			$remote_maxdiscount_pdg		= number_format($remote_maxdiscount_pdg, 2);
			$remote_disc_eligib_pdg		= intval($row_remote_bdgt_pdg['discount_eligibility']);
			$resultArr['remotetop']  	= $remote_top_minbudget_pdg;
			$resultArr['remotenorm']  	= $remote_minbudget_pdg;
			$resultArr['remotemaxval'] 	= $remote_maxdiscount_pdg;
			$resultArr['remoteeligib'] 	= $remote_disc_eligib_pdg;
		}
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function updateTier1PDG($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		$type   = $params['type'];
		
		if(count($params['tier1bdgt'])>0)
		{
			if($type == 'catwise'){
				 
				$top_minbudget_fp	= (intval($params['tier1bdgt']['top200'])>0) ? intval($params['tier1bdgt']['top200']) : 0;
				$minbudget_fp		= (intval($params['tier1bdgt']['normal'])>0) ? intval($params['tier1bdgt']['normal']) : 0;
				$t1pdglogstr		= json_encode($params['tier1bdgt']['L']);
							
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."' WHERE city = '".$this->data_city."'";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier1PdgCatRCity = $query_str;
				$resUpdtTier1PdgCatRCity = parent::execQuery($sqlUpdtTier1PdgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier1PdgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Tier 1 PDG Update - Category Wise',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t1pdglogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier1PdgCat 	= $query_str;
							$resupdateTier1PdgCat 	= parent::execQuery($sqlupdateTier1PdgCat, $conn_city_local);
							if($resupdateTier1PdgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Tier 1 PDG Update - Category Wise'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Tier 1 PDG Update - Category Wise',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Tier 1 PDG Update - Category Wise',$query_str);
					}
				}
				
			}else if($type == 'teamwise'){
				
				$t1pdglogstr		= json_encode($params['tier1bdgt']['L']);
				$tier1pdg_teambdgt_arr = array();
				foreach($params['tier1bdgt']['teamwise'] as $t1pdgtmbdgtkey => $t1pdgtmbdgtval){
					$t1pdgtmbdgtval	= (intval($t1pdgtmbdgtval)>0) ? intval($t1pdgtmbdgtval) : 0;
					$tier1pdg_teambdgt_arr[$t1pdgtmbdgtkey] = $t1pdgtmbdgtval;
				}
				if(count($tier1pdg_teambdgt_arr)>0)
				{
					$team_minbudget_fp = json_encode($tier1pdg_teambdgt_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_fp = '".$team_minbudget_fp."' WHERE city = '".$this->data_city."'";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier1PdgCatRCity = $query_str;
					$resUpdtTier1PdgCatRCity = parent::execQuery($sqlUpdtTier1PdgCatRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier1PdgCatRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'PDG',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Tier 1 PDG Update - Team Wise',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t1pdglogstr."'";
						$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
						$city_update_arr = array();
						try{
							$i = 0;
							$j = 0;
							foreach($this->all_cities_arr as $cityvalue)
							{
								$i ++;
								$conn_city_local	= array();
								$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
								$sqlupdateTier1PdgCat 	= $query_str;
								$resupdateTier1PdgCat 	= parent::execQuery($sqlupdateTier1PdgCat, $conn_city_local);
								if($resupdateTier1PdgCat)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'PDG',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Tier 1 PDG Update - Team Wise'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Tier 1 PDG Update - Team Wise',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Tier 1 PDG Update - Team Wise',$query_str);
						}
					}
				}
			}else if($type == 'discount'){
				$maxdiscount			= floatval($params['tier1bdgt']['maxval']);
				$maxdiscount			= number_format($maxdiscount, 2);
				$discount_eligibility	= (intval($params['tier1bdgt']['eligib'])>0) ? intval($params['tier1bdgt']['eligib']) : 0;
				$t1pdglogstr			= json_encode($params['tier1bdgt']['L']);
				
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier1PdgDiscRCity = $query_str;
				$resUpdtTier1PdgDiscRCity = parent::execQuery($sqlUpdtTier1PdgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier1PdgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Tier 1 PDG Update - PDG Discount',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t1pdglogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier1PdgDisc 	= $query_str;
							$resupdateTier1PdgDisc 	= parent::execQuery($sqlupdateTier1PdgDisc, $conn_city_local);
							if($resupdateTier1PdgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Tier 1 PDG Update - PDG Discount'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Tier 1 PDG Update - PDG Discount',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Tier 1 PDG Update - PDG Discount',$query_str);
					}
				}
			}
		}
		return $resultArr;
	}
	function updateTier2PDG($params)
	{
		global $db;
		$query_str = '';
		$t2_exclusion_city_arr = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur');
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$resultArr = array();
		$type = trim($params['type']);
		if(strtolower($type) == 'catwise')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$top_minbudget_fp	= (intval($params['tier2bdgt']['top200'])>0) ? intval($params['tier2bdgt']['top200']) : 0;
				$minbudget_fp		= (intval($params['tier2bdgt']['normal'])>0) ? intval($params['tier2bdgt']['normal']) : 0;
				$t2diffcitypdg 		= trim($params['tier2bdgt']['t2diffcitypdg']);
				$t2pdgcatlogstr		= json_encode($params['tier2bdgt']['L']);
				if(!empty($t2diffcitypdg)){
					$t2diffcitypdg_arr = array();
					$t2diffcitypdg_arr = explode("|",$t2diffcitypdg);
					$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$t2diffcitypdg_arr);
					$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
				}
				$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier2PdgCatRCity = $query_str;
				$resUpdtTier2PdgCatRCity = parent::execQuery($sqlUpdtTier2PdgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier2PdgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 2 Category Wise PDG Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t2pdgcatlogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlUpdateTier2PdgCat 	= $query_str;
							$resUpdateTier2PdgCat 	= parent::execQuery($sqlUpdateTier2PdgCat, $conn_city_local);
							if($resUpdateTier2PdgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 2 Category Wise PDG Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 2 Category Wise PDG Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 2 Category Wise PDG Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$tier2pdg_teambdgt_arr = array();
				foreach($params['tier2bdgt']['t2pdgtmbdgt'] as $t2pdgtmbdgtkey => $t2pdgtmbdgtval){
					$t2pdgtmbdgtval	= (intval($t2pdgtmbdgtval)>0) ? intval($t2pdgtmbdgtval) : 0;
					$tier2pdg_teambdgt_arr[$t2pdgtmbdgtkey] = $t2pdgtmbdgtval;
				}
				$t2pdgteamlogstr = json_encode($params['tier2bdgt']['L']);
				if(count($tier2pdg_teambdgt_arr)>0)
				{
					$team_minbudget_fp = json_encode($tier2pdg_teambdgt_arr);
					
					$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_fp = '".$team_minbudget_fp."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
					
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier2PdgTeamRCity = $query_str;
					$resUpdtTier2PdgTeamRCity = parent::execQuery($sqlUpdtTier2PdgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier2PdgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'PDG',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 2 Team Wise PDG Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t2pdgteamlogstr."'";
						$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
						$city_update_arr = array();
						try{
							$i = 0;
							$j = 0;
							foreach($this->all_cities_arr as $cityvalue)
							{
								$i ++;
								$conn_city_local	= array();
								$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
								$sqlupdateTier2PdgTeam 	= $query_str;
								$resupdateTier2PdgTeam 	= parent::execQuery($sqlupdateTier2PdgTeam, $conn_city_local);
								if($resupdateTier2PdgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'PDG',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 2 Team Wise PDG Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 2 Team Wise PDG Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 2 Team Wise PDG Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$maxdiscount	= floatval($params['tier2bdgt']['maxval']);
				$maxdiscount	= number_format($maxdiscount, 2);
				$discount_eligibility	= (intval($params['tier2bdgt']['eligib'])>0) ? intval($params['tier2bdgt']['eligib']) : 0;

				
				$t2pdgdisclogstr = json_encode($params['tier2bdgt']['L']);
				
				$tire2discmismatchpdg 			= trim($params['tier2bdgt']['t2diffcitydiscpdg']);
				if(!empty($tire2discmismatchpdg)){
					$tire2discmismatchpdg_arr = array();
					$tire2discmismatchpdg_arr = explode("|",$tire2discmismatchpdg);
					$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$tire2discmismatchpdg_arr);
					$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
				}
				$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
				
				$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier2PdgDiscRCity = $query_str;
				$resUpdtTier2PdgDiscRCity = parent::execQuery($sqlUpdtTier2PdgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier2PdgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 2 PDG Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t2pdgdisclogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier2PdgDisc 	= $query_str;
							$resupdateTier2PdgDisc 	= parent::execQuery($sqlupdateTier2PdgDisc, $conn_city_local);
							if($resupdateTier2PdgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 2 PDG Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 2 PDG Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 2 PDG Discount Update',$query_str);
					}
				}
			}
		}
		return $resultArr;
	}
	function updateTier3PDG($params)
	{
		global $db;
		$query_str = '';
		$t3_exclusion_city_arr = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur');
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$resultArr = array();
		$type = trim($params['type']);
		if(strtolower($type) == 'catwise')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$top_minbudget_fp	= (intval($params['tier3bdgt']['top200'])>0) ? intval($params['tier3bdgt']['top200']) : 0;
				$minbudget_fp		= (intval($params['tier3bdgt']['normal'])>0) ? intval($params['tier3bdgt']['normal']) : 0;
				$t3diffcitypdg 			= trim($params['tier3bdgt']['t3diffcitypdg']);
				$t3pdgcatlogstr			= json_encode($params['tier3bdgt']['L']);
				if(!empty($t3diffcitypdg)){
					$t3diffcitypdg_arr = array();
					$t3diffcitypdg_arr = explode("|",$t3diffcitypdg);
					$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$t3diffcitypdg_arr);
					$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
				}
				$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier3PdgCatRCity = $query_str;
				$resUpdtTier3PdgCatRCity = parent::execQuery($sqlUpdtTier3PdgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier3PdgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 3 Category Wise PDG Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t3pdgcatlogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier3PdgCat 	= $query_str;
							$resupdateTier3PdgCat 	= parent::execQuery($sqlupdateTier3PdgCat, $conn_city_local);
							if($resupdateTier3PdgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$params['data_city']."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 3 Category Wise PDG Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 3 Category Wise PDG Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 3 Category Wise PDG Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$tier3pdg_teambdgt_arr = array();
				foreach($params['tier3bdgt']['t3pdgtmbdgt'] as $t3pdgtmbdgtkey => $t3pdgtmbdgtval){
					$t3pdgtmbdgtval	= (intval($t3pdgtmbdgtval)>0) ? intval($t3pdgtmbdgtval) : 0;
					$tier3pdg_teambdgt_arr[$t3pdgtmbdgtkey] = $t3pdgtmbdgtval;
				}
				$t3pdgteamlogstr			= json_encode($params['tier3bdgt']['L']);
				if(count($tier3pdg_teambdgt_arr)>0)
				{
					$team_minbudget_fp = json_encode($tier3pdg_teambdgt_arr);
					
					$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_fp = '".$team_minbudget_fp."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
					
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier3PdgTeamRCity = $query_str;
					$resUpdtTier3PdgTeamRCity = parent::execQuery($sqlUpdtTier3PdgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier3PdgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'PDG',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 3 Team Wise PDG Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t3pdgteamlogstr."'";
						$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
						$city_update_arr = array();
						try{
							$i = 0;
							$j = 0;
							foreach($this->all_cities_arr as $cityvalue)
							{
								$i ++;
								$conn_city_local	= array();
								$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
								$sqlupdateTier3PdgTeam 	= $query_str;
								$resupdateTier3PdgTeam 	= parent::execQuery($sqlupdateTier3PdgTeam, $conn_city_local);
								if($resupdateTier3PdgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'PDG',
														  city_name 		= '".$params['data_city']."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 3 Team Wise PDG Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 3 Team Wise PDG Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 3 Team Wise PDG Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$maxdiscount	= floatval($params['tier3bdgt']['maxval']);
				$maxdiscount	= number_format($maxdiscount, 2);
				$discount_eligibility	= (intval($params['tier3bdgt']['eligib'])>0) ? intval($params['tier3bdgt']['eligib']) : 0;

				
				$t3pdgdisclogstr = json_encode($params['tier3bdgt']['L']);
				
				$tire3discmismatchpdg 			= trim($params['tier3bdgt']['t3diffcitydiscpdg']);
				if(!empty($tire3discmismatchpdg)){
					$tire3discmismatchpdg_arr = array();
					$tire3discmismatchpdg_arr = explode("|",$tire3discmismatchpdg);
					$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$tire3discmismatchpdg_arr);
					$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
				}
				$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
				
				$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier3PdgDiscRCity = $query_str;
				$resUpdtTier3PdgDiscRCity = parent::execQuery($sqlUpdtTier3PdgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier3PdgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 3 PDG Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t3pdgdisclogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier3PdgDisc 	= $query_str;
							$resupdateTier3PdgDisc 	= parent::execQuery($sqlupdateTier3PdgDisc, $conn_city_local);
							if($resupdateTier3PdgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 3 PDG Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 3 PDG Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 3 PDG Discount Update',$query_str);
					}
				}
			}
		}
		return $resultArr;
	}
	function updateZonePDG($params)
	{
		global $db;
		$query_str = '';
		$main_zone 			= $params['zone_name'];
		$zonewise_cityarr 	= $this->zoneWiseCityList($main_zone);
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$zone_city_name = $this->data_city." - ".$main_zone;
		
		$resultArr = array();
		$type = trim($params['type']);
		if(strtolower($type) == 'catwise')
		{
			if(count($params['zonebdgt'])>0)
			{
				$exclude_city_condn = '';
				$top_minbudget_fp	= (intval($params['zonebdgt']['top200'])>0) ? intval($params['zonebdgt']['top200']) : 0;
				$minbudget_fp		= (intval($params['zonebdgt']['normal'])>0) ? intval($params['zonebdgt']['normal']) : 0;
				$zonediffcitypdg	= trim($params['zonebdgt']['zonediffcitypdg']);
				$zonepdgcatlogstr 	= json_encode($params['zonebdgt']['L']);
				if(!empty($zonediffcitypdg)){
					$zonediffcitypdg_arr = array();
					$zonediffcitypdg_arr = explode("|",$zonediffcitypdg);
					$zone_exclusion_city_str = implode("','",$zonediffcitypdg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtZonePdgCatRCity = $query_str;
				$resUpdtZonePdgCatRCity = parent::execQuery($sqlUpdtZonePdgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtZonePdgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$zone_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$main_zone." Zone - Category Wise PDG Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$zonepdgcatlogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateZonePdgCat 	= $query_str;
							$resupdateZonePdgCat 	= parent::execQuery($sqlupdateZonePdgCat, $conn_city_local);
							if($resupdateZonePdgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$zone_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$main_zone." Zone - Category Wise PDG Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($main_zone.' Zone - Category Wise PDG Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($main_zone.' Zone - Category Wise PDG Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['zonebdgt'])>0)
			{
				$zonepdg_teambdgt_arr = array();
				foreach($params['zonebdgt']['zonepdgtmbdgt'] as $zonepdgtmbdgtkey => $zonepdgtmbdgtval){
					$zonepdgtmbdgtval	= (intval($zonepdgtmbdgtval)>0) ? intval($zonepdgtmbdgtval) : 0;
					$zonepdg_teambdgt_arr[$zonepdgtmbdgtkey] = $zonepdgtmbdgtval;
				}
				$zonepdgteamlogstr 		= json_encode($params['zonebdgt']['L']);
				if(count($zonepdg_teambdgt_arr)>0)
				{
					$team_minbudget_fp = json_encode($zonepdg_teambdgt_arr);
					$zonewise_citystr = implode("','",$zonewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_fp = '".$team_minbudget_fp."' WHERE city IN ('".$zonewise_citystr."')";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtZonePdgTeamRCity = $query_str;
					$resUpdtZonePdgTeamRCity = parent::execQuery($sqlUpdtZonePdgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtZonePdgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'PDG',
											city_name 		= '".$zone_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$main_zone." Zone - Team Wise PDG Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$zonepdgteamlogstr."'";
						$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
						$city_update_arr = array();
						try{
							$i = 0;
							$j = 0;
							foreach($this->all_cities_arr as $cityvalue)
							{
								$i ++;
								$conn_city_local	= array();
								$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
								$sqlupdateZonePdgTeam 	= $query_str;
								$resupdateZonePdgTeam 	= parent::execQuery($sqlupdateZonePdgTeam, $conn_city_local);
								if($resupdateZonePdgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'PDG',
														  city_name 		= '".$zone_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$main_zone." Zone - Team Wise PDG Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($main_zone.' Zone - Team Wise PDG Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($main_zone.' Zone - Team Wise PDG Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['zonebdgt'])>0)
			{
				$maxdiscount			= floatval($params['zonebdgt']['maxval']);
				$maxdiscount			= number_format($maxdiscount, 2);
				$discount_eligibility	= (intval($params['zonebdgt']['eligib'])>0) ? intval($params['zonebdgt']['eligib']) : 0;
				$zonepdgdisclogstr 				= json_encode($params['zonebdgt']['L']);
				
				$zonediscmismatchpdg 			= trim($params['zonebdgt']['zonediffcitydiscpdg']);
				if(!empty($zonediscmismatchpdg)){
					$zonediscmismatchpdg_arr = array();
					$zonediscmismatchpdg_arr = explode("|",$zonediscmismatchpdg);
					$zone_exclusion_city_str = implode("','",$zonediscmismatchpdg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtZonePdgDiscRCity = $query_str;
				$resUpdtZonePdgDiscRCity = parent::execQuery($sqlUpdtZonePdgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtZonePdgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$zone_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$main_zone." Zone - PDG Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$zonepdgdisclogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateZonePdgDisc 	= $query_str;
							$resupdateZonePdgDisc 	= parent::execQuery($sqlupdateZonePdgDisc, $conn_city_local);
							if($resupdateZonePdgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$zone_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$main_zone." Zone - PDG Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($main_zone.' Zone - PDG Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($main_zone.' Zone - PDG Discount Update',$query_str);
					}
				}
			}
		}
		return $resultArr;
	}
	function updateStatePDG($params)
	{
		global $db;
		$query_str = '';
		$state_name 			= $params['state_name'];
		$statewise_cityarr 	= $this->stateWiseCityList($state_name);
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$state_city_name = $this->data_city." - ".$state_name;
		
		$resultArr = array();
		$type = trim($params['type']);
		if(strtolower($type) == 'catwise')
		{
			if(count($params['statebdgt'])>0)
			{
				$exclude_city_condn = '';
				$top_minbudget_fp	= (intval($params['statebdgt']['top200'])>0) ? intval($params['statebdgt']['top200']) : 0;
				$minbudget_fp		= (intval($params['statebdgt']['normal'])>0) ? intval($params['statebdgt']['normal']) : 0;
				$statediffcitypdg 	= trim($params['statebdgt']['statediffcitypdg']);
				$statepdgcatlogstr 	= json_encode($params['statebdgt']['L']);
				if(!empty($statediffcitypdg)){
					$statediffcitypdg_arr = array();
					$statediffcitypdg_arr = explode("|",$statediffcitypdg);
					$state_exclusion_city_str = implode("','",$statediffcitypdg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				$statewise_citystr = implode("','",$statewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtStatePdgCatRCity = $query_str;
				$resUpdtStatePdgCatRCity = parent::execQuery($sqlUpdtStatePdgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtStatePdgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$state_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$state_name." State - Category Wise PDG Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$statepdgcatlogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateStatePdgCat 	= $query_str;
							$resupdateStatePdgCat 	= parent::execQuery($sqlupdateStatePdgCat, $conn_city_local);
							if($resupdateStatePdgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$state_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$state_name." State - Category Wise PDG Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($state_name.' State - Category Wise PDG Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($state_name.' State - Category Wise PDG Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['statebdgt'])>0)
			{
				$statepdg_teambdgt_arr = array();
				foreach($params['statebdgt']['statepdgtmbdgt'] as $statepdgtmbdgtkey => $statepdgtmbdgtval){
					$statepdgtmbdgtval	= (intval($statepdgtmbdgtval)>0) ? intval($statepdgtmbdgtval) : 0;
					$statepdg_teambdgt_arr[$statepdgtmbdgtkey] = $statepdgtmbdgtval;
				}
				$statepdgteamlogstr = json_encode($params['statebdgt']['L']);
				if(count($statepdg_teambdgt_arr)>0)
				{
					$team_minbudget_fp = json_encode($statepdg_teambdgt_arr);
					$statewise_citystr = implode("','",$statewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_fp = '".$team_minbudget_fp."' WHERE city IN ('".$statewise_citystr."')";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtStatePdgTeamRCity = $query_str;
					$resUpdtStatePdgTeamRCity = parent::execQuery($sqlUpdtStatePdgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtStatePdgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'PDG',
											city_name 		= '".$state_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$state_name." State - Team Wise PDG Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$statepdgteamlogstr."'";
						$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
						$city_update_arr = array();
						try{
							$i = 0;
							$j = 0;
							foreach($this->all_cities_arr as $cityvalue)
							{
								$i ++;
								$conn_city_local	= array();
								$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
								$sqlupdateStatePdgTeam 	= $query_str;
								$resupdateStatePdgTeam 	= parent::execQuery($sqlupdateStatePdgTeam, $conn_city_local);
								if($resupdateStatePdgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'PDG',
														  city_name 		= '".$state_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$state_name." State - Team Wise PDG Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($state_name.' State - Team Wise PDG Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($state_name.' State - Team Wise PDG Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['statebdgt'])>0)
			{
				$maxdiscount			= floatval($params['statebdgt']['maxval']);
				$maxdiscount			= number_format($maxdiscount, 2);
				$discount_eligibility	= (intval($params['statebdgt']['eligib'])>0) ? intval($params['statebdgt']['eligib']) : 0;
				$statepdgdisclogstr 				= json_encode($params['statebdgt']['L']);
				
				$statediscmismatchpdg 			= trim($params['statebdgt']['statediffcitydiscpdg']);
				if(!empty($statediscmismatchpdg)){
					$statediscmismatchpdg_arr = array();
					$statediscmismatchpdg_arr = explode("|",$statediscmismatchpdg);
					$state_exclusion_city_str = implode("','",$statediscmismatchpdg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				$statewise_citystr = implode("','",$statewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtStatePdgDiscRCity = $query_str;
				$resUpdtStatePdgDiscRCity = parent::execQuery($sqlUpdtStatePdgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtStatePdgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'PDG',
										city_name 		= '".$state_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$state_name." State - PDG Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$statepdgdisclogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i ++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateStatePdgDisc 	= $query_str;
							$resupdateStatePdgDisc 	= parent::execQuery($sqlupdateStatePdgDisc, $conn_city_local);
							if($resupdateStatePdgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'PDG',
													  city_name 		= '".$state_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$state_name." State - PDG Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($state_name.' State - PDG Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($state_name.' State - PDG Discount Update',$query_str);
					}
				}
			}
		}
		return $resultArr;
	}
	function updateRemoteBudgetPdg($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		
		if(count($params['remotebdgt'])>0)
		{
			$top_minbudget_fp		= (intval($params['remotebdgt']['top200'])>0) ? intval($params['remotebdgt']['top200']) : 0;
			$minbudget_fp			= (intval($params['remotebdgt']['normal'])>0) ? intval($params['remotebdgt']['normal']) : 0;
			$maxdiscount			= floatval($params['remotebdgt']['maxval']);
			$maxdiscount			= number_format($maxdiscount, 2);
			$discount_eligibility	= (intval($params['remotebdgt']['eligib'])>0) ? intval($params['remotebdgt']['eligib']) : 0;
			$remotepdgcatlogstr = json_encode($params['remotebdgt']['L']);
				
			$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_fp = '".$top_minbudget_fp."', minbudget_fp = '".$minbudget_fp."', maxdiscount = '".$maxdiscount."', discount_eligibility = '".$discount_eligibility."' WHERE city = '".$this->data_city."'"; 
			
			$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
			$sqlUpdtRemotePdgRCity = $query_str;
			$resUpdtRemotePdgRCity = parent::execQuery($sqlUpdtRemotePdgRCity, $requested_city_local); // updating on requested city first
			
			if($resUpdtRemotePdgRCity)
			{
				$resultArr['errorcode'] = 0;
				unset($this->all_cities_arr[$requested_city]);
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'PDG',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'Remote City PDG Budget Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$remotepdgcatlogstr."'";
				$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
				$city_update_arr = array();
				try{
					$i = 0;
					$j = 0;
					foreach($this->all_cities_arr as $cityvalue)
					{
						$i ++;
						$conn_city_local	= array();
						$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
						$sqlupdateRemotePdgRest = $query_str;
						$resupdateRemotePdgRest = parent::execQuery($sqlupdateRemotePdgRest, $conn_city_local);
						if($resupdateRemotePdgRest)
						{
							$j ++;
							$city_update_arr[] = $cityvalue;
							$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
												  campaign_name 	= 'PDG',
												  city_name 		= '".$this->data_city."',
												  ucode 			= '".$params['ucode']."',
												  uname 			= '".addslashes($params['uname'])."',
												  insertdate 		= '".date("Y-m-d H:i:s")."',
												  ip_address		= '".$params['ipaddr']."',
												  query_str			= '".addslashes($query_str)."',
												  param_str			= '".$param_str."',
												  comment			= 'Remote City PDG Budget Update'";
							$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
						}
					}
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
					if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
						$this->sendErrorMsg('Remote City PDG Budget Update',$query_str);
					}
				}
				catch(Exception $e) {
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
					$this->sendErrorMsg('Remote City PDG Budget Update',$query_str);
				}
			}
		}
		return $resultArr;
	}
	function teamInfo()
	{
		$team_info_arr = array();
		$sqlTeamInfo = "SELECT team_name,team_abbr FROM online_regis1.tbl_team_info";
		$resTeamInfo = parent::execQuery($sqlTeamInfo, $this->conn_idc);
		if($resTeamInfo && parent::numRows($resTeamInfo)>0)
		{
			while($row_teaminfo = parent::fetchData($resTeamInfo))
			{
				$team_name 			= trim($row_teaminfo['team_name']);
				$team_abbr 			= trim($row_teaminfo['team_abbr']);
				$team_info_arr[$team_abbr] = $team_name;
			}
		}
		return $team_info_arr;
	}
	function tier2CityList()
	{
		$tier2_cities_arr = array();
		$sqlTier2CityList = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2city FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier2CityList = parent::execQuery($sqlTier2CityList, $this->conn_local);
		if($resTier2CityList && parent::numRows($resTier2CityList)>0)
		{
			$row_tier2_citylist = parent::fetchData($resTier2CityList);
			$tier2city 			= trim($row_tier2_citylist['tier2city']);
			$tier2_cities_arr = explode("|",$tier2city);
		}
		return $tier2_cities_arr;
		
	}
	function tier3CityList()
	{
		$tier3_cities_arr = array();
		$sqlTier3CityList = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3city FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier3CityList = parent::execQuery($sqlTier3CityList, $this->conn_local);
		if($resTier3CityList && parent::numRows($resTier3CityList)>0)
		{
			$row_tier3_citylist = parent::fetchData($resTier3CityList);
			$tier3city 			= trim($row_tier3_citylist['tier3city']);
			$tier3_cities_arr = explode("|",$tier3city);
		}
		return $tier3_cities_arr;
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
	function cityCheckInUploadRates($cityarr)
	{
		$citystr = implode("','",$cityarr);
		$uploadrates_city_arr = array();
		$sqlUploadRatesCityList = "SELECT GROUP_CONCAT(city SEPARATOR '|') as uploadrates_city FROM tbl_business_uploadrates WHERE city IN ('".$citystr."') LIMIT 1";
		$resUploadRatesCityList = parent::execQuery($sqlUploadRatesCityList, $this->conn_local);
		if($resUploadRatesCityList && parent::numRows($resUploadRatesCityList)>0)
		{
			$row_uploadrates_citylist = parent::fetchData($resUploadRatesCityList);
			$uploadrates_city 	  = trim($row_uploadrates_citylist['uploadrates_city']);
			$uploadrates_city_arr = explode("|",$uploadrates_city);
		}
		return $uploadrates_city_arr;
	}
	function zoneWiseInUploadRates($cityarr)
	{
		$citystr = implode("','",$cityarr);
		$uploadrates_city_arr = array();
		$sqlUploadRatesCityList = "SELECT GROUP_CONCAT(city SEPARATOR '|') as uploadrates_city FROM tbl_business_uploadrates WHERE city IN ('".$citystr."') LIMIT 1";
		$resUploadRatesCityList = parent::execQuery($sqlUploadRatesCityList, $this->conn_local);
		if($resUploadRatesCityList && parent::numRows($resUploadRatesCityList)>0)
		{
			$row_uploadrates_citylist = parent::fetchData($resUploadRatesCityList);
			$uploadrates_city 	  = trim($row_uploadrates_citylist['uploadrates_city']);
			$uploadrates_city_arr = explode("|",$uploadrates_city);
		}
		return $uploadrates_city_arr;
	}
	function getMismatchCityBdgtInfo($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchCityBdgtInfo = "SELECT city,top_minbudget_fp,minbudget_fp FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchCityBdgtInfo = parent::execQuery($sqlMismatchCityBdgtInfo, $this->conn_local);
		if($resMismatchCityBdgtInfo && parent::numRows($resMismatchCityBdgtInfo)>0)
		{
			while($row_mismatch_city = parent::fetchData($resMismatchCityBdgtInfo))
			{
				$city 					= trim($row_mismatch_city['city']);
				$top_minbudget_fp 		= trim($row_mismatch_city['top_minbudget_fp']);
				$minbudget_fp 			= trim($row_mismatch_city['minbudget_fp']);
				$mismatchDataArr[$city]['top'] 	= $top_minbudget_fp;
				$mismatchDataArr[$city]['norm'] = $minbudget_fp; 
			}
		}
		return $mismatchDataArr;
	}
	function getMismatchDiscountPdg($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchDiscountInfo = "SELECT city,maxdiscount,discount_eligibility FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchDiscountInfo = parent::execQuery($sqlMismatchDiscountInfo, $this->conn_local);
		if($resMismatchDiscountInfo && parent::numRows($resMismatchDiscountInfo)>0)
		{
			while($row_mismatch_disc = parent::fetchData($resMismatchDiscountInfo))
			{
				$city 				= trim($row_mismatch_disc['city']);
				$maxdiscount_pdg 	= floatval($row_mismatch_disc['maxdiscount']);
				$maxdiscount_pdg	= number_format($maxdiscount_pdg, 2);
				
				$discount_eligibility_pdg = intval($row_mismatch_disc['discount_eligibility']);
				$mismatchDataArr[$city]['maxval'] 	= $maxdiscount_pdg; 
				$mismatchDataArr[$city]['eligib'] 	= $discount_eligibility_pdg; 
			}
		}
		return $mismatchDataArr;
	}
	function arrayProcess($requestedArr)
	{
		$processedArr = array();
		if(count($requestedArr)>0){
			$processedArr = array_merge(array_unique(array_filter($requestedArr)));
		}
		return $processedArr;
	}
	private function sendErrorMsg($action,$query){
		$email_text	= '';
		$email_text .= '<br>Action : '.$action;
		$email_text .= '<br>Query : '.$query;
		$email_text .= '<br>';
		
		$link_sms	= mysql_connect('172.29.0.33','decs_app','s@myD#@mnl@sy');
		mysql_select_db('sms_email_sending', $link_sms);
		
		// insert into Tushar's table to automatically send sms from his table
		$sql_sms = "INSERT INTO tbl_common_intimations (email_id, email_subject, email_text, source) VALUES ('imteyaz.raja@justdial.com','Error In GENIO Campaign Budget Update', '".addslashes($email_text)."','cs')";
		$res_sms = mysql_query($sql_sms, $link_sms);
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['errorcode'] = 1;
		$die_msg_arr['errormsg'] = $msg;
		return $die_msg_arr;
	}
}
?>
