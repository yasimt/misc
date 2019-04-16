<?php
class package_budget_update_class extends DB
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
		$this->extrapkg_id_arr = array();
		$this->extrapkg_rid_arr = array();
		$this->extra_pkg_info = $this->extraPackagDetails();
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
	function getTier1PackageBudget()
	{
		$resultArr = array();
		$sqlTier1PackageBudget = "SELECT city,team_minbudget_package,top_minbudget_package,minbudget_package,exppackday,exppackval,exppackval_2,package_mini,package_mini_ecs,package_premium,
		package_premium_upfront,cstm_minbudget_package,maxdiscount_package,discount_eligibility_package,package_mini_minimum,extra_package_details FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$resTier1PackageBudget = parent::execQuery($sqlTier1PackageBudget, $this->conn_local);
		if($resTier1PackageBudget && parent::numRows($resTier1PackageBudget)>0)
		{
			while($row_tier1_pkg = parent::fetchData($resTier1PackageBudget))
			{
				$city 					= trim($row_tier1_pkg['city']);
				$city					= ucwords(strtolower($city));
				$minbudget_package 		= intval($row_tier1_pkg['minbudget_package']);
				$top_minbudget_package 	= intval($row_tier1_pkg['top_minbudget_package']);
				$exppackday 			= intval($row_tier1_pkg['exppackday']);
				$exppackval 			= intval($row_tier1_pkg['exppackval']);
				$exppackval_2 			= intval($row_tier1_pkg['exppackval_2']);
				$package_mini 			= intval($row_tier1_pkg['package_mini']);
				$package_mini_ecs		= intval($row_tier1_pkg['package_mini_ecs']);
				$package_premium 		= intval($row_tier1_pkg['package_premium']);
				$package_prem_upfront 	= intval($row_tier1_pkg['package_premium_upfront']);
				$cstm_minbudget_package = intval($row_tier1_pkg['cstm_minbudget_package']);
				$maxdiscount_package 	= floatval($row_tier1_pkg['maxdiscount_package']);
				$maxdiscount_package	= number_format($maxdiscount_package, 2);
				$discount_eligibility	= intval($row_tier1_pkg['discount_eligibility_package']);
				$package_mini_minimum	= intval($row_tier1_pkg['package_mini_minimum']);
				$extra_package_details	= trim($row_tier1_pkg['extra_package_details']);
				
				
				$teambdgt_pkg 		= trim($row_tier1_pkg['team_minbudget_package']);
				$teambdgt_pkg_arr 	= array();
				$teambdgt_pkg_arr 	= json_decode($teambdgt_pkg,true);
				if(count($teambdgt_pkg_arr)>0)
				{
					$resultArr['data'][$city] = $teambdgt_pkg_arr;
				}
				$resultArr['catbdgt'][$city]['top200'] 		= $top_minbudget_package; // Top 200 Category Budget
				$resultArr['catbdgt'][$city]['normal'] 		= $minbudget_package; // Normal Category Budget
				$resultArr['catbdgt'][$city]['mini'] 		= $package_mini;	// Flexi Budget Upfront
				$resultArr['catbdgt'][$city]['miniecs'] 	= $package_mini_ecs; // Flexi Budget ECS Monthly
				$resultArr['catbdgt'][$city]['premium'] 	= $package_premium; // Package Premium Budget ECS
				$resultArr['catbdgt'][$city]['premupfrnt'] 	= $package_prem_upfront; // Package Premium Budget Upfront
				$resultArr['expire'][$city]['expday'] 		= $exppackday; // Package Expiry Tenure
				$resultArr['expire'][$city]['exp1yrbdgt'] 	= $exppackval; // Package Expiry One Year Budget
				$resultArr['expire'][$city]['exp2yrbdgt'] 	= $exppackval_2; // Package Expiry Two Year Budget
				$resultArr['catbdgt'][$city]['custom'] 		= $cstm_minbudget_package; // Package Custom Min Budget
				$resultArr['discount'][$city]['maxval'] 	= $maxdiscount_package; // Package Maximum Discount In Percentage
				$resultArr['discount'][$city]['eligib'] 	= $discount_eligibility; // Package Discount Eligibility
				$resultArr['catbdgt'][$city]['flxcustom'] 	= $package_mini_minimum; // Flexi Custom Min Budget
				
				//Extra Package Details
				$extrapkg_arr = array();
				$extra_package_details_arr = array();
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
					foreach($extra_package_details_arr as $campid => $campdetails){
						if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
							$extrapkg_arr[$campid] = $campdetails['package_value'];
						}
					}
				}
				if(count($extrapkg_arr)>0){
					$resultArr['extrapkg'][$city] = $extrapkg_arr;
					$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
				}
				
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
	function getTier2TeamBdgtPkg()
	{
		$resultArr = array();
		$sqlTier2TeamBdgtPkg = "SELECT team_minbudget_package FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier2TeamBdgtPkg = parent::execQuery($sqlTier2TeamBdgtPkg, $this->conn_local);
		if($resTier2TeamBdgtPkg && parent::numRows($resTier2TeamBdgtPkg)>0)
		{
			$row_tier2_bdgt_pkg 	= parent::fetchData($resTier2TeamBdgtPkg);
			$tire2_teambdgt_pkg 	= trim($row_tier2_bdgt_pkg['team_minbudget_package']);
			$tire2_teambdgt_pkg_arr = array();
			$tire2_teambdgt_pkg_arr = json_decode($tire2_teambdgt_pkg,true);
			if(count($tire2_teambdgt_pkg_arr)>0)
			{
				$resultArr['tier2data'] = $tire2_teambdgt_pkg_arr;
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
	function getTier2CatBdgtPkg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2CatBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,	cstm_minbudget_package,package_mini_minimum,concat(top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier2CatBdgtPkg = parent::execQuery($sqlTier2CatBdgtPkg, $this->conn_local);
		if($resTier2CatBdgtPkg && parent::numRows($resTier2CatBdgtPkg)>0)
		{
			$row_tier2_catbdgt_pkg 		= parent::fetchData($resTier2CatBdgtPkg);
			$tier2selcity 				= trim($row_tier2_catbdgt_pkg['tier2selcity']);
			$tire2_cat_top_minbdgt_pkg 	= intval($row_tier2_catbdgt_pkg['top_minbudget_package']);
			$tire2_cat_norm_minbdgt_pkg = intval($row_tier2_catbdgt_pkg['minbudget_package']);
			$tire2_cat_package_mini 	= intval($row_tier2_catbdgt_pkg['package_mini']);
			$tire2_cat_package_mini_ecs	= intval($row_tier2_catbdgt_pkg['package_mini_ecs']);
			$tire2_cat_package_premium 	= intval($row_tier2_catbdgt_pkg['package_premium']);
			$tire2_cat_pkg_prem_upfront = intval($row_tier2_catbdgt_pkg['package_premium_upfront']);
			$tire2_cat_pkg_cstm_minbudget = intval($row_tier2_catbdgt_pkg['cstm_minbudget_package']);
			$tire2_cat_pkg_flexi_custom 	= intval($row_tier2_catbdgt_pkg['package_mini_minimum']);
			
			$resultArr['tier2top200'] 	= $tire2_cat_top_minbdgt_pkg; // Top 200 Category Budget
			$resultArr['tier2normal'] 	= $tire2_cat_norm_minbdgt_pkg; // Normal Category Budget
			$resultArr['tier2mini'] 	= $tire2_cat_package_mini; // Flexi Budget Upfront
			$resultArr['tier2miniecs'] 	= $tire2_cat_package_mini_ecs; // Flexi Budget ECS Monthly
			$resultArr['tier2premium'] 	= $tire2_cat_package_premium; // Package Premium Budget ECS
			$resultArr['tier2premupfrnt'] = $tire2_cat_pkg_prem_upfront; // Package Premium Budget Upfront
			$resultArr['tier2custom'] 	= $tire2_cat_pkg_cstm_minbudget; // Package Custom Minimum Budget
			$resultArr['tier2flxcustom'] = $tire2_cat_pkg_flexi_custom; // Flexi Custom Minimum Budget
			
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
	
	function getTier2ExpBdgtPkg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2ExpiryBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,exppackday,exppackval,exppackval_2,concat(exppackval,exppackval_2) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier2ExpiryBdgtPkg = parent::execQuery($sqlTier2ExpiryBdgtPkg, $this->conn_local);
		if($resTier2ExpiryBdgtPkg && parent::numRows($resTier2ExpiryBdgtPkg)>0)
		{
			$row_tier2_expbdgt_pkg 	= parent::fetchData($resTier2ExpiryBdgtPkg);
			$tier2selcity 			= trim($row_tier2_expbdgt_pkg['tier2selcity']);
			$tire2_exppackday_pkg 	= intval($row_tier2_expbdgt_pkg['exppackday']);
			$tire2_exp1yrbdgt_pkg 	= intval($row_tier2_expbdgt_pkg['exppackval']);
			$tire2_exp2yrbdgt_pkg 	= intval($row_tier2_expbdgt_pkg['exppackval_2']);			
			
			$resultArr['expday'] 		= $tire2_exppackday_pkg; // Package Expiry Tenure
			$resultArr['exp1yrbdgt'] 	= $tire2_exp1yrbdgt_pkg; // Package Expiry One Year Budget
			$resultArr['exp2yrbdgt'] 	= $tire2_exp2yrbdgt_pkg; // Package Expiry Two Year Budget
			
			$tier2selcity_arr = explode("|",$tier2selcity);
			$tier2mismatch_cityarr = array_diff($this->tier2_citylist,$tier2selcity_arr);
			$tier2mismatch_cityarr = $this->arrayProcess($tier2mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier2mismatch_cityarr)>0){
				$tier2mismatch_cityinfo =  $this->getMismatchExpiryBudgetPkg($tier2mismatch_cityarr);
				$resultArr['tire2expmismatch'] = $tier2mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier2DiscountPkg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2DiscountPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,maxdiscount_package,discount_eligibility_package,concat(maxdiscount_package,discount_eligibility_package) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier2DiscountPkg = parent::execQuery($sqlTier2DiscountPkg, $this->conn_local);
		if($resTier2DiscountPkg && parent::numRows($resTier2DiscountPkg)>0)
		{
			$row_tier2_discount_pkg = parent::fetchData($resTier2DiscountPkg);
			$tier2selcity 			= trim($row_tier2_discount_pkg['tier2selcity']);
			$tire2_maxdisc_pkg 		= floatval($row_tier2_discount_pkg['maxdiscount_package']);
			$tire2_maxdisc_pkg		= number_format($tire2_maxdisc_pkg, 2);
			$tire2_disceligib_pkg 	= intval($row_tier2_discount_pkg['discount_eligibility_package']);			
			
			$resultArr['maxval'] 	= $tire2_maxdisc_pkg; 	 // Maximum Discount For Package
			$resultArr['eligib'] 	= $tire2_disceligib_pkg; // Discount Eligibility For Package
			
			$tier2selcity_arr = explode("|",$tier2selcity);
			$tier2mismatch_cityarr = array_diff($this->tier2_citylist,$tier2selcity_arr);
			$tier2mismatch_cityarr = $this->arrayProcess($tier2mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier2mismatch_cityarr)>0){
				$tier2mismatch_cityinfo =  $this->getMismatchDiscountPkg($tier2mismatch_cityarr);
				$resultArr['tire2discmismatch'] = $tier2mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier2PremAdBdgtPkg()
	{
		$resultArr = array();
		$tier2mismatch_cityarr = array();
		$sqlTier2PremAdBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier2selcity ,extra_package_details, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY extra_package_details ORDER BY cnt DESC LIMIT 1";
		$resTier2PremAdBdgtPkg = parent::execQuery($sqlTier2PremAdBdgtPkg, $this->conn_local);
		if($resTier2PremAdBdgtPkg && parent::numRows($resTier2PremAdBdgtPkg)>0)
		{
			$row_tier2_prem_ad_pkg 	= parent::fetchData($resTier2PremAdBdgtPkg);
			$tier2selcity 			= trim($row_tier2_prem_ad_pkg['tier2selcity']);
			$tire2_extra_pkg_details= trim($row_tier2_prem_ad_pkg['extra_package_details']);			
			
			//Extra Package Details
			$extrapkg_arr = array();
			$extra_package_details_arr = array();
			$extra_package_details_arr 	= json_decode($tire2_extra_pkg_details,true);
			if(count($extra_package_details_arr)>0){
				$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
				foreach($extra_package_details_arr as $campid => $campdetails){
					if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
						$extrapkg_arr[$campid] = $campdetails['package_value'];
					}
				}
			}
			if(count($extrapkg_arr)>0){
				$resultArr['extrapkg'] 	= $extrapkg_arr;
				$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
			}
			$tier2selcity_arr 		= explode("|",$tier2selcity);
			$tier2mismatch_cityarr 	= array_diff($this->tier2_citylist,$tier2selcity_arr);
			$tier2mismatch_cityarr 	= $this->arrayProcess($tier2mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier2mismatch_cityarr)>0){
				$tier2mismatch_cityinfo =  $this->getMismatchPremAdPkg($tier2mismatch_cityarr);
				$resultArr['t2premmismatch'] = $tier2mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3TeamBdgtPkg()
	{
		$resultArr = array();
		$sqlTier3TeamBdgtPkg = "SELECT team_minbudget_package FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resTier3TeamBdgtPkg = parent::execQuery($sqlTier3TeamBdgtPkg, $this->conn_local);
		if($resTier3TeamBdgtPkg && parent::numRows($resTier3TeamBdgtPkg)>0)
		{
			$row_tier3_bdgt_pkg 	= parent::fetchData($resTier3TeamBdgtPkg);
			$tire3_teambdgt_pkg 	= trim($row_tier3_bdgt_pkg['team_minbudget_package']);
			$tire3_teambdgt_pkg_arr = array();
			$tire3_teambdgt_pkg_arr = json_decode($tire3_teambdgt_pkg,true);
			if(count($tire3_teambdgt_pkg_arr)>0)
			{
				$resultArr['tier3data'] = $tire3_teambdgt_pkg_arr;
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
	function getTier3CatBdgtPkg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3CatBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,	cstm_minbudget_package,package_mini_minimum,concat(top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier3CatBdgtPkg = parent::execQuery($sqlTier3CatBdgtPkg, $this->conn_local);
		if($resTier3CatBdgtPkg && parent::numRows($resTier3CatBdgtPkg)>0)
		{
			$row_tier3_catbdgt_pkg 		= parent::fetchData($resTier3CatBdgtPkg);
			$tier3selcity 				= trim($row_tier3_catbdgt_pkg['tier3selcity']);
			$tire3_cat_top_minbdgt_pkg 	= intval($row_tier3_catbdgt_pkg['top_minbudget_package']);
			$tire3_cat_norm_minbdgt_pkg = intval($row_tier3_catbdgt_pkg['minbudget_package']);
			$tire3_cat_package_mini 	= intval($row_tier3_catbdgt_pkg['package_mini']);
			$tire3_cat_package_mini_ecs = intval($row_tier3_catbdgt_pkg['package_mini_ecs']);
			$tire3_cat_package_premium 	= intval($row_tier3_catbdgt_pkg['package_premium']);
			$tire3_cat_pkg_prem_upfront = intval($row_tier3_catbdgt_pkg['package_premium_upfront']);
			$tire3_cat_cstm_minbudget   = intval($row_tier3_catbdgt_pkg['cstm_minbudget_package']);
			$tire3_cat_flexi_custom   	= intval($row_tier3_catbdgt_pkg['package_mini_minimum']);
			
			$resultArr['tier3top200'] 	= $tire3_cat_top_minbdgt_pkg; // Top 300 Category Budget
			$resultArr['tier3normal'] 	= $tire3_cat_norm_minbdgt_pkg; // Normal Category Budget
			$resultArr['tier3mini'] 	= $tire3_cat_package_mini; // Flexi Budget Upfront
			$resultArr['tier3miniecs'] 	= $tire3_cat_package_mini_ecs; // Flexi Budget ECS Monthly
			$resultArr['tier3premium'] 	= $tire3_cat_package_premium; // Package Premium Budget ECS
			$resultArr['tier3premupfrnt'] = $tire3_cat_pkg_prem_upfront; // Package Premium Budget Upfront
			$resultArr['tier3custom'] 	= $tire3_cat_cstm_minbudget; // Package Custom Min Budget Package
			$resultArr['tier3flxcustom'] = $tire3_cat_flexi_custom; // Flexi Custom Min Budget Package
			
			
			$tier3selcity_arr = explode("|",$tier3selcity);
			$tier3mismatch_cityarr = array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr = $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				//$tier3mismatch_cityinfo =  $this->getMismatchCityBdgtInfo($tier3mismatch_cityarr);
				$tier3mismatch_cityarr = array_map('strtolower', $tier3mismatch_cityarr);
				$tier3mismatch_cityarr = array_map('ucwords', $tier3mismatch_cityarr);
				$resultArr['tire3mismatch'] = implode("|",$tier3mismatch_cityarr);
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3ExpBdgtPkg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3ExpiryBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,exppackday,exppackval,exppackval_2,concat(exppackval,exppackval_2) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier3ExpiryBdgtPkg = parent::execQuery($sqlTier3ExpiryBdgtPkg, $this->conn_local);
		if($resTier3ExpiryBdgtPkg && parent::numRows($resTier3ExpiryBdgtPkg)>0)
		{
			$row_tier3_expbdgt_pkg 	= parent::fetchData($resTier3ExpiryBdgtPkg);
			$tier3selcity 			= trim($row_tier3_expbdgt_pkg['tier3selcity']);
			$tire3_exppackday_pkg 	= intval($row_tier3_expbdgt_pkg['exppackday']);
			$tire3_exp1yrbdgt_pkg 	= intval($row_tier3_expbdgt_pkg['exppackval']);
			$tire3_exp2yrbdgt_pkg 	= intval($row_tier3_expbdgt_pkg['exppackval_2']);			
			
			$resultArr['expday'] 		= $tire3_exppackday_pkg; // Package Expiry Tenure
			$resultArr['exp1yrbdgt'] 	= $tire3_exp1yrbdgt_pkg; // Package Expiry One Year Budget
			$resultArr['exp2yrbdgt'] 	= $tire3_exp2yrbdgt_pkg; // Package Expiry Two Year Budget
			
			$tier3selcity_arr = explode("|",$tier3selcity);
			$tier3mismatch_cityarr = array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr = $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				$tier3mismatch_cityinfo =  $this->getMismatchExpiryBudgetPkg($tier3mismatch_cityarr);
				$resultArr['tire3expmismatch'] = $tier3mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3DiscountPkg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3DiscountPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,maxdiscount_package,discount_eligibility_package,concat(maxdiscount_package,discount_eligibility_package) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
		$resTier3DiscountPkg = parent::execQuery($sqlTier3DiscountPkg, $this->conn_local);
		if($resTier3DiscountPkg && parent::numRows($resTier3DiscountPkg)>0)
		{
			$row_tier3_discount_pkg = parent::fetchData($resTier3DiscountPkg);
			$tier3selcity 			= trim($row_tier3_discount_pkg['tier3selcity']);
			$tire3_maxdisc_pkg 		= floatval($row_tier3_discount_pkg['maxdiscount_package']);
			$tire3_maxdisc_pkg		= number_format($tire3_maxdisc_pkg, 2);
			$tire3_disceligib_pkg 	= intval($row_tier3_discount_pkg['discount_eligibility_package']);			
			
			$resultArr['maxval'] 	= $tire3_maxdisc_pkg; 	 // Maximum Discount For Package
			$resultArr['eligib'] 	= $tire3_disceligib_pkg; // Discount Eligibility For Package
			
			$tier3selcity_arr = explode("|",$tier3selcity);
			$tier3mismatch_cityarr = array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr = $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				$tier3mismatch_cityinfo =  $this->getMismatchDiscountPkg($tier3mismatch_cityarr);
				$resultArr['tire3discmismatch'] = $tier3mismatch_cityinfo;
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function getTier3PremAdBdgtPkg()
	{
		$resultArr = array();
		$tier3mismatch_cityarr = array();
		$sqlTier3PremAdBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as tier3selcity ,extra_package_details, count(1) as cnt FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') GROUP BY extra_package_details ORDER BY cnt DESC LIMIT 1";
		$resTier3PremAdBdgtPkg = parent::execQuery($sqlTier3PremAdBdgtPkg, $this->conn_local);
		if($resTier3PremAdBdgtPkg && parent::numRows($resTier3PremAdBdgtPkg)>0)
		{
			$row_tier3_prem_ad_pkg 	= parent::fetchData($resTier3PremAdBdgtPkg);
			$tier3selcity 			= trim($row_tier3_prem_ad_pkg['tier3selcity']);
			$tire3_extra_pkg_details= trim($row_tier3_prem_ad_pkg['extra_package_details']);			
			
			//Extra Package Details
			$extrapkg_arr = array();
			$extra_package_details_arr = array();
			$extra_package_details_arr 	= json_decode($tire3_extra_pkg_details,true);
			if(count($extra_package_details_arr)>0){
				$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
				foreach($extra_package_details_arr as $campid => $campdetails){
					if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
						$extrapkg_arr[$campid] = $campdetails['package_value'];
					}
				}
			}
			if(count($extrapkg_arr)>0){
				$resultArr['extrapkg'] 	= $extrapkg_arr;
				$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
			}
			$tier3selcity_arr 		= explode("|",$tier3selcity);
			$tier3mismatch_cityarr 	= array_diff($this->tier3_citylist,$tier3selcity_arr);
			$tier3mismatch_cityarr 	= $this->arrayProcess($tier3mismatch_cityarr);
		}
		if(count($resultArr)>0){
			if(count($tier3mismatch_cityarr)>0){
				//$tier3mismatch_cityinfo =  $this->getMismatchPremAdPkg($tier3mismatch_cityarr);
				$tier3mismatch_cityarr = array_map('strtolower', $tier3mismatch_cityarr);
				$tier3mismatch_cityarr = array_map('ucwords', $tier3mismatch_cityarr);
				$resultArr['t3premmismatch'] = implode("|",$tier3mismatch_cityarr);
				
			}
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	
	function getT3AccordDataPkg($params)
	{
		$city = trim($params['accordcity']);
		$type = trim($params['type']);
		$accordDataArr = array();
		if(strtolower($type) == 'catwise'){
			$sqlT3AccordDataPkg = "SELECT city,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum FROM tbl_business_uploadrates WHERE city = '".$city."'";
			$resT3AccordDataPkg = parent::execQuery($sqlT3AccordDataPkg, $this->conn_local);
			if($resT3AccordDataPkg && parent::numRows($resT3AccordDataPkg)>0){
				
				$row_t3_accord_pkg = parent::fetchData($resT3AccordDataPkg);
				$top_minbudget_package 		= intval($row_t3_accord_pkg['top_minbudget_package']);
				$minbudget_package 			= intval($row_t3_accord_pkg['minbudget_package']);
				$package_mini 				= intval($row_t3_accord_pkg['package_mini']);
				$package_mini_ecs 			= intval($row_t3_accord_pkg['package_mini_ecs']);
				$package_premium 			= intval($row_t3_accord_pkg['package_premium']);
				$package_premium_upfront	= intval($row_t3_accord_pkg['package_premium_upfront']);
				$cstm_minbudget_package		= intval($row_t3_accord_pkg['cstm_minbudget_package']);
				$package_mini_minimum		= intval($row_t3_accord_pkg['package_mini_minimum']);
				$accordDataArr['top'] 			= $top_minbudget_package;
				$accordDataArr['norm'] 			= $minbudget_package; 
				$accordDataArr['mini'] 			= $package_mini;
				$accordDataArr['miniecs'] 		= $package_mini_ecs;
				$accordDataArr['premium'] 		= $package_premium;
				$accordDataArr['premupfrnt'] 	= $package_premium_upfront;
				$accordDataArr['custom'] 		= $cstm_minbudget_package;
				$accordDataArr['flxcustom'] 	= $package_mini_minimum;
				$accordDataArr['errorcode']		= 0;
			}else{
				$accordDataArr['errorcode']		= 1;
			}
		}else if(strtolower($type) == 'premad'){
			
			$sqlT3AccordDataPkg = "SELECT extra_package_details FROM tbl_business_uploadrates WHERE city = '".$city."'";
			$resT3AccordDataPkg = parent::execQuery($sqlT3AccordDataPkg, $this->conn_local);
			if($resT3AccordDataPkg && parent::numRows($resT3AccordDataPkg)>0){
				
				$row_t3_accord_pkg = parent::fetchData($resT3AccordDataPkg);
				$extra_package_details 		= trim($row_t3_accord_pkg['extra_package_details']);
				
				$extrapkg_arr = array();
				$extra_package_details_arr = array();
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
					foreach($extra_package_details_arr as $campid => $campdetails){
						if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
							$campname = $this->extrapkg_id_arr[$campid];
							$extrapkg_arr[$campname] = $campdetails['package_value'];
						}
					}
				}
				if(count($extrapkg_arr)>0){
					$accordDataArr['data']		= $extrapkg_arr;
					$accordDataArr['errorcode']	= 0;
				}else{
					$accordDataArr['errorcode']	= 1;
				}
			}else{
				$accordDataArr['errorcode']		= 1;
			}
			
		}
		return $accordDataArr;
	}
	
	
	function getZoneTeamBdgtPkg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$sqlZoneTeamBdgtPkg = "SELECT team_minbudget_package FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') LIMIT 1";
			$resZoneTeamBdgtPkg = parent::execQuery($sqlZoneTeamBdgtPkg, $this->conn_local);
			if($resZoneTeamBdgtPkg && parent::numRows($resZoneTeamBdgtPkg)>0){
				$row_zone_bdgt_pkg 	= parent::fetchData($resZoneTeamBdgtPkg);
				$zone_teambdgt_pkg 	= trim($row_zone_bdgt_pkg['team_minbudget_package']);
				$zone_teambdgt_pkg_arr = array();
				$zone_teambdgt_pkg_arr = json_decode($zone_teambdgt_pkg,true);
				if(count($zone_teambdgt_pkg_arr)>0)
				{
					$resultArr['zonedata'] = $zone_teambdgt_pkg_arr;
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
	function getZoneCatBdgtPkg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();
			$sqlZoneCatBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,	package_premium_upfront,cstm_minbudget_package,package_mini_minimum,concat(top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resZoneCatBdgtPkg = parent::execQuery($sqlZoneCatBdgtPkg, $this->conn_local);
			if($resZoneCatBdgtPkg && parent::numRows($resZoneCatBdgtPkg)>0){
				$row_zone_catbdgt_pkg 		= parent::fetchData($resZoneCatBdgtPkg);
				$zoneselcity 				= trim($row_zone_catbdgt_pkg['zoneselcity']);
				$zone_cat_top_minbdgt_pkg 	= trim($row_zone_catbdgt_pkg['top_minbudget_package']);
				$zone_cat_norm_minbdgt_pkg  = trim($row_zone_catbdgt_pkg['minbudget_package']);
				$zone_package_mini  		= trim($row_zone_catbdgt_pkg['package_mini']);
				$zone_package_mini_ecs  	= trim($row_zone_catbdgt_pkg['package_mini_ecs']);
				$zone_package_premium  		= trim($row_zone_catbdgt_pkg['package_premium']);
				$zone_pkg_premium_upfront 	= trim($row_zone_catbdgt_pkg['package_premium_upfront']);
				$zone_pkg_cstm_minbudget 	= trim($row_zone_catbdgt_pkg['cstm_minbudget_package']);
				$zone_pkg_flexi_custom 		= trim($row_zone_catbdgt_pkg['package_mini_minimum']);
				
				
				$resultArr['zonetop200'] 	= $zone_cat_top_minbdgt_pkg; // Top 300 Category Budget
				$resultArr['zonenormal'] 	= $zone_cat_norm_minbdgt_pkg; // Normal Category Budget
				$resultArr['zonemini'] 		= $zone_package_mini; // Flexi Budget Upfront
				$resultArr['zoneminiecs'] 	= $zone_package_mini_ecs; // Flexi Budget ECS Monthly
				$resultArr['zonepremium'] 	= $zone_package_premium; // Package Mini Budget ECS
				$resultArr['zonepremupfrnt'] = $zone_pkg_premium_upfront; // Package Mini Budget Upfront
				$resultArr['zonecustom'] 	= $zone_pkg_cstm_minbudget; // Package Mini Budget Upfront
				$resultArr['zoneflxcustom'] = $zone_pkg_flexi_custom; // Flexi Custom Budget
				
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->cityCheckInUploadRates($zonewise_cityarr);
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
	
	function getZoneExpBdgtPkg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();
			$sqlZoneExpiryBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,exppackday,exppackval,exppackval_2,concat(exppackval,exppackval_2) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resZoneExpiryBdgtPkg = parent::execQuery($sqlZoneExpiryBdgtPkg, $this->conn_local);
			if($resZoneExpiryBdgtPkg && parent::numRows($resZoneExpiryBdgtPkg)>0){
				$row_zone_expbdgt_pkg 	= parent::fetchData($resZoneExpiryBdgtPkg);
				$zoneselcity 			= trim($row_zone_expbdgt_pkg['zoneselcity']);
				$zone_exppackday_pkg 	= intval($row_zone_expbdgt_pkg['exppackday']);
				$zone_exp1yrbdgt_pkg 	= intval($row_zone_expbdgt_pkg['exppackval']);
				$zone_exp2yrbdgt_pkg 	= intval($row_zone_expbdgt_pkg['exppackval_2']);			
				
				$resultArr['expday'] 		= $zone_exppackday_pkg; // Package Expiry Tenure
				$resultArr['exp1yrbdgt'] 	= $zone_exp1yrbdgt_pkg; // Package Expiry One Year Budget
				$resultArr['exp2yrbdgt'] 	= $zone_exp2yrbdgt_pkg; // Package Expiry Two Year Budget
				
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->cityCheckInUploadRates($zonewise_cityarr);
				$zonemismatch_cityarr = array_diff($zonewise_city_uploadrates,$zoneselcity_arr);
				$zonemismatch_cityarr = $this->arrayProcess($zonemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($zonemismatch_cityarr)>0){
					$zonemismatch_cityinfo =  $this->getMismatchExpiryBudgetPkg($zonemismatch_cityarr);
					$resultArr['zoneexpmismatch'] = $zonemismatch_cityinfo;
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
	function getZoneDiscountPkg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();
			$sqlZoneDiscountPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,maxdiscount_package,discount_eligibility_package,concat(maxdiscount_package,discount_eligibility_package) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resZoneDiscountPkg = parent::execQuery($sqlZoneDiscountPkg, $this->conn_local);
			if($resZoneDiscountPkg && parent::numRows($resZoneDiscountPkg)>0){
				$row_zone_discount_pkg 	= parent::fetchData($resZoneDiscountPkg);
				$zoneselcity 			= trim($row_zone_discount_pkg['zoneselcity']);
				$zone_maxdisc_pkg 		= floatval($row_zone_discount_pkg['maxdiscount_package']);
				$zone_maxdisc_pkg		= number_format($zone_maxdisc_pkg, 2);
				$zone_disceligib_pkg 	= intval($row_zone_discount_pkg['discount_eligibility_package']);
							
				$resultArr['maxval'] 	= $zone_maxdisc_pkg; 	// Maximum Discount For Package
				$resultArr['eligib'] 	= $zone_disceligib_pkg; // Discount Eligibility For Package
				
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->cityCheckInUploadRates($zonewise_cityarr);
				$zonemismatch_cityarr = array_diff($zonewise_city_uploadrates,$zoneselcity_arr);
				$zonemismatch_cityarr = $this->arrayProcess($zonemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($zonemismatch_cityarr)>0){
					$zonemismatch_cityinfo =  $this->getMismatchDiscountPkg($zonemismatch_cityarr);
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
	function getZonePremAdBdgtPkg($main_zone)
	{
		$resultArr = array();
		$zonewise_cityarr = $this->zoneWiseCityList($main_zone);
		if(count($zonewise_cityarr)>0){
			$zonewise_citystr = implode("','",$zonewise_cityarr);
			$zonemismatch_cityarr = array();	
			$sqlZonePremiumAdPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as zoneselcity ,extra_package_details, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$zonewise_citystr."') GROUP BY extra_package_details ORDER BY cnt DESC LIMIT 1";
			$resZonePremiumAdPkg = parent::execQuery($sqlZonePremiumAdPkg, $this->conn_local);
			if($resZonePremiumAdPkg && parent::numRows($resZonePremiumAdPkg)>0){
				$row_zone_prem_pkg 		= parent::fetchData($resZonePremiumAdPkg);
				$zoneselcity 			= trim($row_zone_prem_pkg['zoneselcity']);
				$zone_extra_pkg_details= trim($row_zone_prem_pkg['extra_package_details']);			
				
				//Extra Package Details
				$extrapkg_arr = array();
				$extra_package_details_arr = array();
				$extra_package_details_arr 	= json_decode($zone_extra_pkg_details,true);
				if(count($extra_package_details_arr)>0){
					$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
					foreach($extra_package_details_arr as $campid => $campdetails){
						if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
							$extrapkg_arr[$campid] = $campdetails['package_value'];
						}
					}
				}
				if(count($extrapkg_arr)>0){
					$resultArr['extrapkg'] 	= $extrapkg_arr;
					$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
				}
				$zoneselcity_arr = explode("|",$zoneselcity);
				$zonewise_city_uploadrates = $this->cityCheckInUploadRates($zonewise_cityarr);
				$zonemismatch_cityarr = array_diff($zonewise_city_uploadrates,$zoneselcity_arr);
				$zonemismatch_cityarr = $this->arrayProcess($zonemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($zonemismatch_cityarr)>0){
					$zonemismatch_cityinfo =  $this->getMismatchPremAdPkg($zonemismatch_cityarr);
					$resultArr['zonepremmismatch'] = $zonemismatch_cityinfo;
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
	function getStateTeamBdgtPkg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$sqlStateTeamBdgtPkg = "SELECT team_minbudget_package FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') LIMIT 1";
			$resStateTeamBdgtPkg = parent::execQuery($sqlStateTeamBdgtPkg, $this->conn_local);
			if($resStateTeamBdgtPkg && parent::numRows($resStateTeamBdgtPkg)>0){
				$row_state_bdgt_pkg = parent::fetchData($resStateTeamBdgtPkg);
				$state_teambdgt_pkg 	= trim($row_state_bdgt_pkg['team_minbudget_package']);
				$state_teambdgt_pkg_arr = array();
				$state_teambdgt_pkg_arr = json_decode($state_teambdgt_pkg,true);
				if(count($state_teambdgt_pkg_arr)>0)
				{
					$resultArr['statedata'] = $state_teambdgt_pkg_arr;
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
	function getStateCatBdgtPkg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStateCatBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs, package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum,concat(top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resStateCatBdgtPkg = parent::execQuery($sqlStateCatBdgtPkg, $this->conn_local);
			if($resStateCatBdgtPkg && parent::numRows($resStateCatBdgtPkg)>0){
				$row_state_catbdgt_pkg 		= parent::fetchData($resStateCatBdgtPkg);
				$stateselcity 				= trim($row_state_catbdgt_pkg['stateselcity']);
				$state_cat_top_minbdgt_pkg 	= trim($row_state_catbdgt_pkg['top_minbudget_package']);
				$state_cat_norm_minbdgt_pkg  = trim($row_state_catbdgt_pkg['minbudget_package']);
				$state_package_mini  		= trim($row_state_catbdgt_pkg['package_mini']);
				$state_package_mini_ecs  	= trim($row_state_catbdgt_pkg['package_mini_ecs']);
				$state_package_premium  	= trim($row_state_catbdgt_pkg['package_premium']);
				$state_pkg_premium_upfront  = trim($row_state_catbdgt_pkg['package_premium_upfront']);
				$state_cstm_minbudget_pkg 	= intval($row_state_catbdgt_pkg['cstm_minbudget_package']);
				$state_flexi_custom_pkg 	= intval($row_state_catbdgt_pkg['package_mini_minimum']);
				
				$resultArr['statetop200'] 	= $state_cat_top_minbdgt_pkg; // Top 300 Category Budget
				$resultArr['statenormal'] 	= $state_cat_norm_minbdgt_pkg; // Normal Category Budget
				$resultArr['statemini'] 	= $state_package_mini; // Flexi Budget Upfront
				$resultArr['stateminiecs'] 	= $state_package_mini_ecs; // Flexi Budget ECS Monthly
				$resultArr['statepremium'] 	= $state_package_premium; // Package Mini Budget
				$resultArr['statepremupfrnt'] = $state_pkg_premium_upfront; // Package Mini Budget
				$resultArr['statecustom'] 	= $state_cstm_minbudget_pkg; // Package Custom Min Budget
				$resultArr['stateflxcustom'] 	= $state_flexi_custom_pkg; // Flexi Custom Min Budget
				
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
	function getStateExpBdgtPkg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStateExpiryBdgtPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,exppackday,exppackval,exppackval_2,concat(exppackval,exppackval_2) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resStateExpiryBdgtPkg = parent::execQuery($sqlStateExpiryBdgtPkg, $this->conn_local);
			if($resStateExpiryBdgtPkg && parent::numRows($resStateExpiryBdgtPkg)>0){
				$row_state_expbdgt_pkg 	= parent::fetchData($resStateExpiryBdgtPkg);
				$stateselcity 			= trim($row_state_expbdgt_pkg['stateselcity']);
				$state_exppackday_pkg 	= intval($row_state_expbdgt_pkg['exppackday']);
				$state_exp1yrbdgt_pkg 	= intval($row_state_expbdgt_pkg['exppackval']);
				$state_exp2yrbdgt_pkg 	= intval($row_state_expbdgt_pkg['exppackval_2']);			
				
				$resultArr['expday'] 		= $state_exppackday_pkg; // Package Expiry Tenure
				$resultArr['exp1yrbdgt'] 	= $state_exp1yrbdgt_pkg; // Package Expiry One Year Budget
				$resultArr['exp2yrbdgt'] 	= $state_exp2yrbdgt_pkg; // Package Expiry Two Year Budget
				
				$stateselcity_arr = explode("|",$stateselcity);
				$statewise_city_uploadrates = $this->cityCheckInUploadRates($statewise_cityarr);
				$statemismatch_cityarr = array_diff($statewise_city_uploadrates,$stateselcity_arr);
				$statemismatch_cityarr = $this->arrayProcess($statemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($statemismatch_cityarr)>0){
					$statemismatch_cityinfo =  $this->getMismatchExpiryBudgetPkg($statemismatch_cityarr);
					$resultArr['stateexpmismatch'] = $statemismatch_cityinfo;
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
	function getStateDiscountPkg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStateDiscountPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,maxdiscount_package,discount_eligibility_package,concat(maxdiscount_package,discount_eligibility_package) as temp_field, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY temp_field ORDER BY cnt DESC LIMIT 1";
			$resStateDiscountPkg = parent::execQuery($sqlStateDiscountPkg, $this->conn_local);
			if($resStateDiscountPkg && parent::numRows($resStateDiscountPkg)>0){
				$row_state_discount_pkg = parent::fetchData($resStateDiscountPkg);
				$stateselcity 			= trim($row_state_discount_pkg['stateselcity']);
				$state_maxdiscount_pkg 	= floatval($row_state_discount_pkg['maxdiscount_package']);
				$state_maxdiscount_pkg	= number_format($state_maxdiscount_pkg, 2);
				$state_disc_eligib_pkg	= intval($row_state_discount_pkg['discount_eligibility_package']);
				
				$resultArr['maxval'] 	= $state_maxdiscount_pkg;
				$resultArr['eligib'] 	= $state_disc_eligib_pkg;
				
				$stateselcity_arr = explode("|",$stateselcity);
				$statewise_city_uploadrates = $this->cityCheckInUploadRates($statewise_cityarr);
				$statemismatch_cityarr = array_diff($statewise_city_uploadrates,$stateselcity_arr);
				$statemismatch_cityarr = $this->arrayProcess($statemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($statemismatch_cityarr)>0){
					$statemismatch_cityinfo =  $this->getMismatchDiscountPkg($statemismatch_cityarr);
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
	function getStatePremAdBdgtPkg($state_name)
	{
		$resultArr = array();
		$statewise_cityarr = $this->stateWiseCityList($state_name);
		if(count($statewise_cityarr)>0){
			$statewise_citystr = implode("','",$statewise_cityarr);
			$statemismatch_cityarr = array();
			$sqlStatePremiumAdPkg = "SELECT GROUP_CONCAT(city SEPARATOR '|') as stateselcity ,extra_package_details, count(1) as cnt FROM tbl_business_uploadrates WHERE city IN ('".$statewise_citystr."') GROUP BY extra_package_details ORDER BY cnt DESC LIMIT 1";
			$resStatePremiumAdPkg = parent::execQuery($sqlStatePremiumAdPkg, $this->conn_local);
			if($resStatePremiumAdPkg && parent::numRows($resStatePremiumAdPkg)>0){
				$row_state_prem_pkg 	= parent::fetchData($resStatePremiumAdPkg);
				$stateselcity 			= trim($row_state_prem_pkg['stateselcity']);
				$state_extra_pkg_details= trim($row_state_prem_pkg['extra_package_details']);			
				
				//Extra Package Details
				$extrapkg_arr = array();
				$extra_package_details_arr = array();
				$extra_package_details_arr 	= json_decode($state_extra_pkg_details,true);
				if(count($extra_package_details_arr)>0){
					$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
					foreach($extra_package_details_arr as $campid => $campdetails){
						if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
							$extrapkg_arr[$campid] = $campdetails['package_value'];
						}
					}
				}
				if(count($extrapkg_arr)>0){
					$resultArr['extrapkg'] 	= $extrapkg_arr;
					$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
				}
				$stateselcity_arr = explode("|",$stateselcity);
				$statewise_city_uploadrates = $this->cityCheckInUploadRates($statewise_cityarr);
				$statemismatch_cityarr = array_diff($statewise_city_uploadrates,$stateselcity_arr);
				$statemismatch_cityarr = $this->arrayProcess($statemismatch_cityarr);
			}
			if(count($resultArr)>0){
				if(count($statemismatch_cityarr)>0){
					$statemismatch_cityinfo =  $this->getMismatchPremAdPkg($statemismatch_cityarr);
					$resultArr['statepremmismatch'] = $statemismatch_cityinfo;
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
	function getRemoteBudgetPkg()
	{
		$resultArr = array();
		$sqlRemoteBudgetPkg = "SELECT top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,exppackday,exppackval
		,exppackval_2,maxdiscount_package,discount_eligibility_package,package_mini_minimum,extra_package_details FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$resRemoteBudgetPkg = parent::execQuery($sqlRemoteBudgetPkg, $this->conn_local);
		if($resRemoteBudgetPkg && parent::numRows($resRemoteBudgetPkg)>0)
		{
			$row_remote_bdgt_pkg 		= parent::fetchData($resRemoteBudgetPkg);
			$remote_top_minbudget_pkg 	= intval($row_remote_bdgt_pkg['top_minbudget_package']);
			$remote_minbudget_package 	= intval($row_remote_bdgt_pkg['minbudget_package']);
			$remote_package_mini 		= intval($row_remote_bdgt_pkg['package_mini']);
			$remote_package_mini_ecs 	= intval($row_remote_bdgt_pkg['package_mini_ecs']);
			$remote_package_premium 	= intval($row_remote_bdgt_pkg['package_premium']);
			$remote_pkg_premium_upfront = intval($row_remote_bdgt_pkg['package_premium_upfront']);
			$remote_cstm_minbudget 		= intval($row_remote_bdgt_pkg['cstm_minbudget_package']);
			$remote_exppackday			= intval($row_remote_bdgt_pkg['exppackday']);
			$remote_exppackval 			= intval($row_remote_bdgt_pkg['exppackval']);
			$remote_exppackval_2 		= intval($row_remote_bdgt_pkg['exppackval_2']);
			$remote_maxdiscount_pkg 	= floatval($row_remote_bdgt_pkg['maxdiscount_package']);
			$remote_maxdiscount_pkg		= number_format($remote_maxdiscount_pkg, 2);
			$remote_disc_eligib_pkg		= intval($row_remote_bdgt_pkg['discount_eligibility_package']);
			$remote_flexi_custom_pkg	= intval($row_remote_bdgt_pkg['package_mini_minimum']);
			$extra_package_details		= trim($row_remote_bdgt_pkg['extra_package_details']);
			$resultArr['remotetop']  	= $remote_top_minbudget_pkg;
			$resultArr['remotenorm']  	= $remote_minbudget_package;
			$resultArr['remotemini']  	= $remote_package_mini;
			$resultArr['remoteminiecs'] = $remote_package_mini_ecs;
			$resultArr['remotepremium'] = $remote_package_premium;
			$resultArr['remotepremupfrnt'] = $remote_pkg_premium_upfront;
			$resultArr['remotecustom'] 	= $remote_cstm_minbudget;
			$resultArr['remoteexpday'] 	= $remote_exppackday;
			$resultArr['remoteexp1'] 	= $remote_exppackval;
			$resultArr['remoteexp2'] 	= $remote_exppackval_2;
			$resultArr['remotemaxval'] 	= $remote_maxdiscount_pkg;
			$resultArr['remoteeligib'] 	= $remote_disc_eligib_pkg;
			$resultArr['remoteflxcustom'] = $remote_flexi_custom_pkg;
			
			
			//Extra Package Details
			$extrapkg_arr = array();
			$extra_package_details_arr = array();
			$extra_package_details_arr 	= json_decode($extra_package_details,true);
			if(count($extra_package_details_arr)>0){
				$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
				foreach($extra_package_details_arr as $campid => $campdetails){
					if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
						$extrapkg_arr[$campid] = $campdetails['package_value'];
					}
				}
			}
			if(count($extrapkg_arr)>0){
				$resultArr['extrapkg'] = $extrapkg_arr;
				$resultArr['pkgidinfo'] = $this->extrapkg_id_arr;
			}
		}
		
		if(count($resultArr)>0){
			$resultArr['errorcode'] = 0;
		}else{
			$resultArr['errorcode'] = 1;
		}
		return $resultArr;
	}
	function updateTier1Package($params)
	{
		global $db;
		$query_str = '';
		$param_str 		= json_encode($params);
		$time_stamp 	= date_create();
		$uniqueid 		= date_format($time_stamp, 'U');
		
		$requested_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$resultArr = array();
		$type	= $params['type'];
		if(count($params['tier1bdgt'])>0)
		{
			if($type == 'catwise'){
				
				$top_minbudget_package	= (intval($params['tier1bdgt']['top200'])>0) ? intval($params['tier1bdgt']['top200']) : 0;
				$minbudget_package		= (intval($params['tier1bdgt']['normal'])>0) ? intval($params['tier1bdgt']['normal']) : 0;
				$package_mini			= (intval($params['tier1bdgt']['mini'])>0) ? intval($params['tier1bdgt']['mini']) : 0;
				$package_mini_ecs		= (intval($params['tier1bdgt']['miniecs'])>0) ? intval($params['tier1bdgt']['miniecs']) : 0;
				$package_premium		= (intval($params['tier1bdgt']['premium'])>0) ? intval($params['tier1bdgt']['premium']) : 0;
				$package_premium_upfront= (intval($params['tier1bdgt']['premupfrnt'])>0) ? intval($params['tier1bdgt']['premupfrnt']) : 0;
				$cstm_minbudget_package	= (intval($params['tier1bdgt']['custom'])>0) ? intval($params['tier1bdgt']['custom']) : 0;
				$package_mini_minimum	= (intval($params['tier1bdgt']['flxcustom'])>0) ? intval($params['tier1bdgt']['flxcustom']) : 0;
				$t1pkglogstr			= json_encode($params['tier1bdgt']['L']);
				
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."',  package_premium = '".$package_premium."',  package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', package_mini_minimum = '".$package_mini_minimum."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier1PkgCatRCity = $query_str;
				$resUpdtTier1PkgCatRCity = parent::execQuery($sqlUpdtTier1PkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier1PkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Tier 1 Package Update - Category Wise',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t1pkglogstr."'";
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
							$sqlupdateTier1PkgCat 	= $query_str;
							$resupdateTier1PkgCat 	= parent::execQuery($sqlupdateTier1PkgCat, $conn_city_local);
							if($resupdateTier1PkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Tier 1 Package Update - Category Wise'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Tier 1 Package Update - Category Wise',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Tier 1 Package Update - Category Wise',$query_str);
					}
				}
				
			}else if($type == 'expire'){
				
				$exppackday				= (intval($params['tier1bdgt']['expday'])>0) ? intval($params['tier1bdgt']['expday']) : 0;
				$exppackval				= (intval($params['tier1bdgt']['exp1yrbdgt'])>0) ? intval($params['tier1bdgt']['exp1yrbdgt']) : 0;
				$exppackval_2			= (intval($params['tier1bdgt']['exp2yrbdgt'])>0) ? intval($params['tier1bdgt']['exp2yrbdgt']) : 0;
				$t1pkglogstr			= json_encode($params['tier1bdgt']['L']);
				
				$team_minbudget_package = json_encode($tier1pkg_teambdgt_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier1PkgCatRCity = $query_str;
				$resUpdtTier1PkgCatRCity = parent::execQuery($sqlUpdtTier1PkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier1PkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Tier 1 Package Update - Expiry Package',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t1pkglogstr."'";
					$resInsertLogIDC = parent::execQuery($sqlInsertLogIDC, $this->conn_idc);
					$city_update_arr = array();
					try{
						$i = 0;
						$j = 0;
						foreach($this->all_cities_arr as $cityvalue)
						{
							$i++;
							$conn_city_local	= array();
							$conn_city_local  	= $db[$cityvalue]['d_jds']['master'];
							$sqlupdateTier1PkgCat 	= $query_str;
							$resupdateTier1PkgCat 	= parent::execQuery($sqlupdateTier1PkgCat, $conn_city_local);
							if($resupdateTier1PkgCat)
							{
								$j++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Tier 1 Package Update - Expiry Package'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Tier 1 Package Update - Expiry Package',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Tier 1 Package Update - Expiry Package',$query_str);
					}
				}
				
			}else if($type == 'teamwise'){
				
				$t1pkglogstr			= json_encode($params['tier1bdgt']['L']);
				$tier1pkg_teambdgt_arr = array();
				foreach($params['tier1bdgt']['teamwise'] as $t1pkgtmbdgtkey => $t1pkgtmbdgtval){
					$t1pkgtmbdgtval	= (intval($t1pkgtmbdgtval)>0) ? intval($t1pkgtmbdgtval) : 0;
					$tier1pkg_teambdgt_arr[$t1pkgtmbdgtkey] = $t1pkgtmbdgtval;
				}
				if(count($tier1pkg_teambdgt_arr)>0)
				{
					$team_minbudget_package = json_encode($tier1pkg_teambdgt_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_package = '".$team_minbudget_package."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier1PkgCatRCity = $query_str;
					$resUpdtTier1PkgCatRCity = parent::execQuery($sqlUpdtTier1PkgCatRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier1PkgCatRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Tier 1 Package Update - Team Wise',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t1pkglogstr."'";
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
								$sqlupdateTier1PkgCat 	= $query_str;
								$resupdateTier1PkgCat 	= parent::execQuery($sqlupdateTier1PkgCat, $conn_city_local);
								if($resupdateTier1PkgCat)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Tier 1 Package Update - Team Wise'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Tier 1 Package Update - Team Wise',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Tier 1 Package Update - Team Wise',$query_str);
						}
					}
				}
			}else if($type == 'discount'){
				$maxdiscount_package			= floatval($params['tier1bdgt']['maxval']);
				$maxdiscount_package			= number_format($maxdiscount_package, 2);
				$discount_eligibility_package	= (intval($params['tier1bdgt']['eligib'])>0) ? intval($params['tier1bdgt']['eligib']) : 0;
				$t1pkglogstr			= json_encode($params['tier1bdgt']['L']);
				
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier1PkgCatRCity = $query_str;
				$resUpdtTier1PkgCatRCity = parent::execQuery($sqlUpdtTier1PkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier1PkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Tier 1 Package Update - Package Discount',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t1pkglogstr."'";
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
							$sqlupdateTier1PkgCat 	= $query_str;
							$resupdateTier1PkgCat 	= parent::execQuery($sqlupdateTier1PkgCat, $conn_city_local);
							if($resupdateTier1PkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Tier 1 Package Update - Package Discount'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Tier 1 Package Update - Package Discount',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Tier 1 Package Update - Package Discount',$query_str);
					}
				}
			}else if($type == 'premadpkg'){
				
				$t1pkglogstr			= json_encode($params['tier1bdgt']['L']);
				$extra_pkg_details_arr 	= array();
				$comboextradetails_arr 	= array();
				foreach($params['tier1bdgt']['premadpkg'] as $t1pkgprembdgtkey => $t1pkgprembdgtval){
					$t1pkgprembdgtval	= (intval($t1pkgprembdgtval)>0) ? intval($t1pkgprembdgtval) : 0;
					$extra_pkg_details_arr[$t1pkgprembdgtkey]['package_value'] = $t1pkgprembdgtval;
					$extra_pkg_details_arr[$t1pkgprembdgtkey]['display_upfront'] 	= 1; // passing default value as 1 , as told by pramesh & ganesh
					$extra_pkg_details_arr[$t1pkgprembdgtkey]['display_ecs'] 		= 1;
					
					
					$prem_related_cid = $this->extrapkg_rid_arr[$t1pkgprembdgtkey];
					$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $t1pkgprembdgtval * 12;
					$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $t1pkgprembdgtval;
					$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
					$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
				}
				if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
				{
					$extra_package_details 	= json_encode($extra_pkg_details_arr);
					$comboextradetails		= json_encode($comboextradetails_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET extra_package_details = '".$extra_package_details."', comboextradetails = '".$comboextradetails."' WHERE city = '".$this->data_city."'"; // Updating to selected city for Tier - 1
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier1PkgCatRCity = $query_str;
					$resUpdtTier1PkgCatRCity = parent::execQuery($sqlUpdtTier1PkgCatRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier1PkgCatRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Tier 1 Package Update - Premium Ad',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t1pkglogstr."'";
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
								$sqlupdateTier1PkgCat 	= $query_str;
								$resupdateTier1PkgCat 	= parent::execQuery($sqlupdateTier1PkgCat, $conn_city_local);
								if($resupdateTier1PkgCat)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Tier 1 Package Update - Premium Ad'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Tier 1 Package Update - Premium Ad',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Tier 1 Package Update - Premium Ad',$query_str);
						}
					}
				}
			}
		}
		return $resultArr;
	}
	function updateTier2Package($params)
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
				$top_minbudget_package	= (intval($params['tier2bdgt']['top200'])>0) ? intval($params['tier2bdgt']['top200']) : 0;
				$minbudget_package		= (intval($params['tier2bdgt']['normal'])>0) ? intval($params['tier2bdgt']['normal']) : 0;
				$package_mini           = (intval($params['tier2bdgt']['mini'])>0) ? intval($params['tier2bdgt']['mini']) : 0;
				$package_mini_ecs       = (intval($params['tier2bdgt']['miniecs'])>0) ? intval($params['tier2bdgt']['miniecs']) : 0;
				$package_premium        = (intval($params['tier2bdgt']['premium'])>0) ? intval($params['tier2bdgt']['premium']) : 0;
				$package_premium_upfront= (intval($params['tier2bdgt']['premupfrnt'])>0) ? intval($params['tier2bdgt']['premupfrnt']) : 0;
				$cstm_minbudget_package = (intval($params['tier2bdgt']['custom'])>0) ? intval($params['tier2bdgt']['custom']) : 0;
				$package_mini_minimum 	= (intval($params['tier2bdgt']['flxcustom'])>0) ? intval($params['tier2bdgt']['flxcustom']) : 0;
				$t2diffcitypkg 			= trim($params['tier2bdgt']['t2diffcitypkg']);
				if(!empty($t2diffcitypkg)){
					$t2diffcitypkg_arr = array();
					$t2diffcitypkg_arr = explode("|",$t2diffcitypkg);
					$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$t2diffcitypkg_arr);
					$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
				}
				$t2pkgcatlogstr			= json_encode($params['tier2bdgt']['L']);
				$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."',  package_premium = '".$package_premium."',  package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', package_mini_minimum = '".$package_mini_minimum."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier2PkgCatRCity = $query_str;
				$resUpdtTier2PkgCatRCity = parent::execQuery($sqlUpdtTier2PkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier2PkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 2 Category Wise Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t2pkgcatlogstr."'";
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
							$sqlupdateTier2PkgCat 	= $query_str;
							$resupdateTier2PkgCat 	= parent::execQuery($sqlupdateTier2PkgCat, $conn_city_local);
							if($resupdateTier2PkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 2 Category Wise Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 2 Category Wise Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 2 Category Wise Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$tier2pkg_teambdgt_arr = array();
				foreach($params['tier2bdgt']['t2pkgtmbdgt'] as $t2pkgtmbdgtkey => $t2pkgtmbdgtval){
					$t2pkgtmbdgtval	= (intval($t2pkgtmbdgtval)>0) ? intval($t2pkgtmbdgtval) : 0;
					$tier2pkg_teambdgt_arr[$t2pkgtmbdgtkey] = $t2pkgtmbdgtval;
				}
				$t2pkgteamlogstr			= json_encode($params['tier2bdgt']['L']);
				if(count($tier2pkg_teambdgt_arr)>0)
				{
					$team_minbudget_package = json_encode($tier2pkg_teambdgt_arr);
					
					$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_package = '".$team_minbudget_package."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
					
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier2PkgTeamRCity = $query_str;
					$resUpdtTier2PkgTeamRCity = parent::execQuery($sqlUpdtTier2PkgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier2PkgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 2 Team Wise Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t2pkgteamlogstr."'";
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
								$sqlupdateTier2PkgTeam 	= $query_str;
								$resupdateTier2PkgTeam 	= parent::execQuery($sqlupdateTier2PkgTeam, $conn_city_local);
								if($resupdateTier2PkgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 2 Team Wise Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 2 Team Wise Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 2 Team Wise Package Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'expire')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$exppackday		= (intval($params['tier2bdgt']['expday'])>0) ? intval($params['tier2bdgt']['expday']) : 0;
				$exppackval		= (intval($params['tier2bdgt']['exp1yrbdgt'])>0) ? intval($params['tier2bdgt']['exp1yrbdgt']) : 0;
				$exppackval_2	= (intval($params['tier2bdgt']['exp2yrbdgt'])>0) ? intval($params['tier2bdgt']['exp2yrbdgt']) : 0;
				$t2pkgexplogstr = json_encode($params['tier2bdgt']['L']);
				
				$tire2expmismatchpkg 			= trim($params['tier2bdgt']['t2diffcityexppkg']);
				if(!empty($tire2expmismatchpkg)){
					$tire2expmismatchpkg_arr = array();
					$tire2expmismatchpkg_arr = explode("|",$tire2expmismatchpkg);
					$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$tire2expmismatchpkg_arr);
					$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
				}
				$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier2PkgExpRCity = $query_str;
				$resUpdtTier2PkgExpRCity = parent::execQuery($sqlUpdtTier2PkgExpRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier2PkgExpRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 2 Expiry Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t2pkgexplogstr."'";
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
							$sqlupdateTier2PkgExp 	= $query_str;
							$resupdateTier2PkgExp 	= parent::execQuery($sqlupdateTier2PkgExp, $conn_city_local);
							if($resupdateTier2PkgExp)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 2 Expiry Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 2 Expiry Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 2 Expiry Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['tier2bdgt'])>0)
			{
				$maxdiscount_package			= floatval($params['tier2bdgt']['maxval']);
				$maxdiscount_package			= number_format($maxdiscount_package, 2);
				$discount_eligibility_package	= (intval($params['tier2bdgt']['eligib'])>0) ? intval($params['tier2bdgt']['eligib']) : 0;

				
				$t2pkgdisclogstr = json_encode($params['tier2bdgt']['L']);
				
				$tire2discmismatchpkg 			= trim($params['tier2bdgt']['t2diffcitydiscpkg']);
				if(!empty($tire2discmismatchpkg)){
					$tire2discmismatchpkg_arr = array();
					$tire2discmismatchpkg_arr = explode("|",$tire2discmismatchpkg);
					$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$tire2discmismatchpkg_arr);
					$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
				}
				$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
				
				$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier2PkgDiscRCity = $query_str;
				$resUpdtTier2PkgDiscRCity = parent::execQuery($sqlUpdtTier2PkgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier2PkgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 2 Package Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t2pkgdisclogstr."'";
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
							$sqlupdateTier2PkgDisc 	= $query_str;
							$resupdateTier2PkgDisc 	= parent::execQuery($sqlupdateTier2PkgDisc, $conn_city_local);
							if($resupdateTier2PkgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 2 Package Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 2 Package Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 2 Package Discount Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'premadpkg')
		{	
			if(count($params['tier2bdgt'])>0)
			{
				$t2pkgpremlogstr = json_encode($params['tier2bdgt']['L']);
				$extra_pkg_details_arr 	= array();
				$comboextradetails_arr 	= array();
				foreach($params['tier2bdgt']['t2pkgpremadbdgt'] as $t2pkgprembdgtkey => $t2pkgprembdgtval){
					$t2pkgprembdgtval	= (intval($t2pkgprembdgtval)>0) ? intval($t2pkgprembdgtval) : 0;
					$extra_pkg_details_arr[$t2pkgprembdgtkey]['package_value'] 		= $t2pkgprembdgtval;
					$extra_pkg_details_arr[$t2pkgprembdgtkey]['display_upfront'] 	= 1;
					$extra_pkg_details_arr[$t2pkgprembdgtkey]['display_ecs'] 		= 1;
					
					$prem_related_cid = $this->extrapkg_rid_arr[$t2pkgprembdgtkey];
					$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $t2pkgprembdgtval * 12;
					$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $t2pkgprembdgtval;
					$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
					$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
				}
				if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
				{
					$extra_package_details 	= json_encode($extra_pkg_details_arr);
					$comboextradetails		= json_encode($comboextradetails_arr);
					$t2premmismatchpkg 			= trim($params['tier2bdgt']['t2diffcityprempkg']);
					if(!empty($t2premmismatchpkg)){
						$t2premmismatchpkg_arr = array();
						$t2premmismatchpkg_arr = explode("|",$t2premmismatchpkg);
						$t2_exclusion_city_arr = array_merge($t2_exclusion_city_arr,$t2premmismatchpkg_arr);
						$t2_exclusion_city_arr = $this->arrayProcess($t2_exclusion_city_arr);
					}
					$t2_exclusion_city_str = implode("','",$t2_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET extra_package_details = '".$extra_package_details."', comboextradetails = '".$comboextradetails."' WHERE tier = 2 AND city NOT IN ('".$t2_exclusion_city_str."')";
					
					$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier2PkgDiscRCity = $query_str;
					$resUpdtTier2PkgDiscRCity = parent::execQuery($sqlUpdtTier2PkgDiscRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier2PkgDiscRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 2 Premium Ad Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t2pkgpremlogstr."'";
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
								$sqlupdateTier2PkgDisc 	= $query_str;
								$resupdateTier2PkgDisc 	= parent::execQuery($sqlupdateTier2PkgDisc, $conn_city_local);
								if($resupdateTier2PkgDisc)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 2 Premium Ad Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 2 Premium Ad Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 2 Premium Ad Package Update',$query_str);
						}
					}
				}
			}
		}
		return $resultArr;
	}
	function updateTier3Package($params)
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
				$top_minbudget_package	= (intval($params['tier3bdgt']['top200'])>0) ? intval($params['tier3bdgt']['top200']) : 0;
				$minbudget_package		= (intval($params['tier3bdgt']['normal'])>0) ? intval($params['tier3bdgt']['normal']) : 0;
				$package_mini           = (intval($params['tier3bdgt']['mini'])>0) ? intval($params['tier3bdgt']['mini']) : 0;
				$package_mini_ecs       = (intval($params['tier3bdgt']['miniecs'])>0) ? intval($params['tier3bdgt']['miniecs']) : 0;
				$package_premium        = (intval($params['tier3bdgt']['premium'])>0) ? intval($params['tier3bdgt']['premium']) : 0;
				$package_premium_upfront= (intval($params['tier3bdgt']['premupfrnt'])>0) ? intval($params['tier3bdgt']['premupfrnt']) : 0;
				$cstm_minbudget_package	= (intval($params['tier3bdgt']['custom'])>0) ? intval($params['tier3bdgt']['custom']) : 0;
				$package_mini_minimum	= (intval($params['tier3bdgt']['flxcustom'])>0) ? intval($params['tier3bdgt']['flxcustom']) : 0;
				$t3pkgcatlogstr			= json_encode($params['tier3bdgt']['L']);
				$t3diffcitypkg 			= trim($params['tier3bdgt']['t3diffcitypkg']);
				if(!empty($t3diffcitypkg)){
					$t3diffcitypkg_arr = array();
					$t3diffcitypkg_arr = explode("|",$t3diffcitypkg);
					$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$t3diffcitypkg_arr);
					$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
				}
				$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."',  package_premium = '".$package_premium."',  package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', package_mini_minimum = '".$package_mini_minimum."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier3PkgCatRCity = $query_str;
				$resUpdtTier3PkgCatRCity = parent::execQuery($sqlUpdtTier3PkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier3PkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 3 Category Wise Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t3pkgcatlogstr."'";
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
							$sqlupdateTier3PkgCat 	= $query_str;
							$resupdateTier3PkgCat 	= parent::execQuery($sqlupdateTier3PkgCat, $conn_city_local);
							if($resupdateTier3PkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$params['data_city']."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 3 Category Wise Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 3 Category Wise Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 3 Category Wise Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$tier3pkg_teambdgt_arr = array();
				foreach($params['tier3bdgt']['t3pkgtmbdgt'] as $t3pkgtmbdgtkey => $t3pkgtmbdgtval){
					$t3pkgtmbdgtval	= (intval($t3pkgtmbdgtval)>0) ? intval($t3pkgtmbdgtval) : 0;
					$tier3pkg_teambdgt_arr[$t3pkgtmbdgtkey] = $t3pkgtmbdgtval;
				}
				$t3pkgteamlogstr			= json_encode($params['tier3bdgt']['L']);
				if(count($tier3pkg_teambdgt_arr)>0)
				{
					$team_minbudget_package = json_encode($tier3pkg_teambdgt_arr);
					
					$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_package = '".$team_minbudget_package."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
					
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier3PkgTeamRCity = $query_str;
					$resUpdtTier3PkgTeamRCity = parent::execQuery($sqlUpdtTier3PkgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier3PkgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 3 Team Wise Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t3pkgteamlogstr."'";
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
								$sqlupdateTier3PkgTeam 	= $query_str;
								$resupdateTier3PkgTeam 	= parent::execQuery($sqlupdateTier3PkgTeam, $conn_city_local);
								if($resupdateTier3PkgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$params['data_city']."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 3 Team Wise Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 3 Team Wise Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 3 Team Wise Package Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'expire')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$exppackday		= (intval($params['tier3bdgt']['expday'])>0) ? intval($params['tier3bdgt']['expday']) : 0;
				$exppackval		= (intval($params['tier3bdgt']['exp1yrbdgt'])>0) ? intval($params['tier3bdgt']['exp1yrbdgt']) : 0;
				$exppackval_2	= (intval($params['tier3bdgt']['exp2yrbdgt'])>0) ? intval($params['tier3bdgt']['exp2yrbdgt']) : 0;
				$t3pkgexplogstr = json_encode($params['tier3bdgt']['L']);
				
				
				$tire3expmismatchpkg 			= trim($params['tier3bdgt']['t3diffcityexppkg']);
				if(!empty($tire3expmismatchpkg)){
					$tire3expmismatchpkg_arr = array();
					$tire3expmismatchpkg_arr = explode("|",$tire3expmismatchpkg);
					$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$tire3expmismatchpkg_arr);
					$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
				}
				$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier3PkgExpRCity = $query_str;
				$resUpdtTier3PkgExpRCity = parent::execQuery($sqlUpdtTier3PkgExpRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier3PkgExpRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 3 Expiry Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t3pkgexplogstr."'";
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
							$sqlupdateTier3PkgExp 	= $query_str;
							$resupdateTier3PkgExp 	= parent::execQuery($sqlupdateTier3PkgExp, $conn_city_local);
							if($resupdateTier3PkgExp)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 3 Expiry Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 3 Expiry Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 3 Expiry Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['tier3bdgt'])>0)
			{
				$maxdiscount_package			= floatval($params['tier3bdgt']['maxval']);
				$maxdiscount_package			= number_format($maxdiscount_package, 2);
				$discount_eligibility_package	= (intval($params['tier3bdgt']['eligib'])>0) ? intval($params['tier3bdgt']['eligib']) : 0;

				
				$t3pkgdisclogstr = json_encode($params['tier3bdgt']['L']);
				
				$tire3discmismatchpkg 			= trim($params['tier3bdgt']['t3diffcitydiscpkg']);
				if(!empty($tire3discmismatchpkg)){
					$tire3discmismatchpkg_arr = array();
					$tire3discmismatchpkg_arr = explode("|",$tire3discmismatchpkg);
					$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$tire3discmismatchpkg_arr);
					$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
				}
				$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
				
				$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtTier3PkgDiscRCity = $query_str;
				$resUpdtTier3PkgDiscRCity = parent::execQuery($sqlUpdtTier3PkgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtTier3PkgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$this->data_city."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= 'Remote Tier 3 Package Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$t3pkgdisclogstr."'";
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
							$sqlupdateTier3PkgDisc 	= $query_str;
							$resupdateTier3PkgDisc 	= parent::execQuery($sqlupdateTier3PkgDisc, $conn_city_local);
							if($resupdateTier3PkgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$this->data_city."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= 'Remote Tier 3 Package Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg('Remote Tier 3 Package Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg('Remote Tier 3 Package Discount Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'premadpkg')
		{	
			if(count($params['tier3bdgt'])>0)
			{
				$t3pkgpremlogstr = json_encode($params['tier3bdgt']['L']);
				$extra_pkg_details_arr 	= array();
				$comboextradetails_arr 	= array();
				foreach($params['tier3bdgt']['t3pkgpremadbdgt'] as $t3pkgprembdgtkey => $t3pkgprembdgtval){
					$t3pkgprembdgtval	= (intval($t3pkgprembdgtval)>0) ? intval($t3pkgprembdgtval) : 0;
					$extra_pkg_details_arr[$t3pkgprembdgtkey]['package_value'] 		= $t3pkgprembdgtval;
					$extra_pkg_details_arr[$t3pkgprembdgtkey]['display_upfront'] 	= 1;
					$extra_pkg_details_arr[$t3pkgprembdgtkey]['display_ecs'] 		= 1;
					
					$prem_related_cid = $this->extrapkg_rid_arr[$t3pkgprembdgtkey];
					$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $t3pkgprembdgtval * 12;
					$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $t3pkgprembdgtval;
					$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
					$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
				}
				if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
				{
					$extra_package_details 	= json_encode($extra_pkg_details_arr);
					$comboextradetails		= json_encode($comboextradetails_arr);
					$t3premmismatchpkg 			= trim($params['tier3bdgt']['t3diffcityprempkg']);
					if(!empty($t3premmismatchpkg)){
						$t3premmismatchpkg_arr = array();
						$t3premmismatchpkg_arr = explode("|",$t3premmismatchpkg);
						$t3_exclusion_city_arr = array_merge($t3_exclusion_city_arr,$t3premmismatchpkg_arr);
						$t3_exclusion_city_arr = $this->arrayProcess($t3_exclusion_city_arr);
					}
					$t3_exclusion_city_str = implode("','",$t3_exclusion_city_arr);
					$query_str = "UPDATE tbl_business_uploadrates SET extra_package_details = '".$extra_package_details."', comboextradetails = '".$comboextradetails."' WHERE tier = 3 AND city NOT IN ('".$t3_exclusion_city_str."')";
					
					$requested_city_local 	  = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtTier3PkgDiscRCity = $query_str;
					$resUpdtTier3PkgDiscRCity = parent::execQuery($sqlUpdtTier3PkgDiscRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtTier3PkgDiscRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$this->data_city."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= 'Remote Tier 3 Premium Ad Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$t3pkgpremlogstr."'";
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
								$sqlupdateTier3PkgDisc 	= $query_str;
								$resupdateTier3PkgDisc 	= parent::execQuery($sqlupdateTier3PkgDisc, $conn_city_local);
								if($resupdateTier3PkgDisc)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$this->data_city."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= 'Remote Tier 3 Premium Ad Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg('Remote Tier 3 Premium Ad Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg('Remote Tier 3 Premium Ad Package Update',$query_str);
						}
					}
				}
			}
		}
		return $resultArr;
	}
	function updateZonePackage($params)
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
				$top_minbudget_package	= (intval($params['zonebdgt']['top200'])>0) ? intval($params['zonebdgt']['top200']) : 0;
				$minbudget_package		= (intval($params['zonebdgt']['normal'])>0) ? intval($params['zonebdgt']['normal']) : 0;
				$package_mini			= (intval($params['zonebdgt']['mini'])>0) ? intval($params['zonebdgt']['mini']) : 0;
				$package_mini_ecs		= (intval($params['zonebdgt']['miniecs'])>0) ? intval($params['zonebdgt']['miniecs']) : 0;
				$package_premium		= (intval($params['zonebdgt']['premium'])>0) ? intval($params['zonebdgt']['premium']) : 0;
				$package_premium_upfront	= (intval($params['zonebdgt']['premupfrnt'])>0) ? intval($params['zonebdgt']['premupfrnt']) : 0;
				$cstm_minbudget_package		= (intval($params['zonebdgt']['custom'])>0) ? intval($params['zonebdgt']['custom']) : 0;
				$package_mini_minimum		= (intval($params['zonebdgt']['flxcustom'])>0) ? intval($params['zonebdgt']['flxcustom']) : 0;
				$zonediffcitypkg 		= trim($params['zonebdgt']['zonediffcitypkg']);
				$zonepkgcatlogstr 		= json_encode($params['zonebdgt']['L']);
				if(!empty($zonediffcitypkg)){
					$zonediffcitypkg_arr = array();
					$zonediffcitypkg_arr = explode("|",$zonediffcitypkg);
					$zone_exclusion_city_str = implode("','",$zonediffcitypkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."',  package_premium = '".$package_premium."',  package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', package_mini_minimum = '".$package_mini_minimum."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtZonePkgCatRCity = $query_str;
				$resUpdtZonePkgCatRCity = parent::execQuery($sqlUpdtZonePkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtZonePkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$zone_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$main_zone." Zone - Category Wise Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$zonepkgcatlogstr."'";
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
							$sqlupdateZonePkgCat 	= $query_str;
							$resupdateZonePkgCat 	= parent::execQuery($sqlupdateZonePkgCat, $conn_city_local);
							if($resupdateZonePkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$zone_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$main_zone." Zone - Category Wise Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($main_zone.' Zone - Category Wise Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($main_zone.' Zone - Category Wise Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['zonebdgt'])>0)
			{
				$zonepkg_teambdgt_arr = array();
				foreach($params['zonebdgt']['zonepkgtmbdgt'] as $zonepkgtmbdgtkey => $zonepkgtmbdgtval){
					$zonepkgtmbdgtval	= (intval($zonepkgtmbdgtval)>0) ? intval($zonepkgtmbdgtval) : 0;
					$zonepkg_teambdgt_arr[$zonepkgtmbdgtkey] = $zonepkgtmbdgtval;
				}
				$zonepkgteamlogstr 		= json_encode($params['zonebdgt']['L']);
				if(count($zonepkg_teambdgt_arr)>0)
				{
					$team_minbudget_package = json_encode($zonepkg_teambdgt_arr);
					$zonewise_citystr = implode("','",$zonewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_package = '".$team_minbudget_package."' WHERE city IN ('".$zonewise_citystr."')";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtZonePkgTeamRCity = $query_str;
					$resUpdtZonePkgTeamRCity = parent::execQuery($sqlUpdtZonePkgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtZonePkgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$zone_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$main_zone." Zone - Team Wise Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$zonepkgteamlogstr."'";
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
								$sqlupdateZonePkgTeam 	= $query_str;
								$resupdateZonePkgTeam 	= parent::execQuery($sqlupdateZonePkgTeam, $conn_city_local);
								if($resupdateZonePkgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$zone_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$main_zone." Zone - Team Wise Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($main_zone.' Zone - Team Wise Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($main_zone.' Zone - Team Wise Package Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'expire')
		{
			if(count($params['zonebdgt'])>0)
			{
				$exppackday		= (intval($params['zonebdgt']['expday'])>0) ? intval($params['zonebdgt']['expday']) : 0;
				$exppackval		= (intval($params['zonebdgt']['exp1yrbdgt'])>0) ? intval($params['zonebdgt']['exp1yrbdgt']) : 0;
				$exppackval_2	= (intval($params['zonebdgt']['exp2yrbdgt'])>0) ? intval($params['zonebdgt']['exp2yrbdgt']) : 0;
				$zonepkgexplogstr 	= json_encode($params['zonebdgt']['L']);
				
				$zoneexpmismatchpkg 			= trim($params['zonebdgt']['zonediffcityexppkg']);
				if(!empty($zoneexpmismatchpkg)){
					$zoneexpmismatchpkg_arr = array();
					$zoneexpmismatchpkg_arr = explode("|",$zoneexpmismatchpkg);
					$zone_exclusion_city_str = implode("','",$zoneexpmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtZonePkgExpRCity = $query_str;
				$resUpdtZonePkgExpRCity = parent::execQuery($sqlUpdtZonePkgExpRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtZonePkgExpRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$zone_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$main_zone." Zone - Expiry Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$zonepkgexplogstr."'";
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
							$sqlupdateZonePkgExp 	= $query_str;
							$resupdateZonePkgExp 	= parent::execQuery($sqlupdateZonePkgExp, $conn_city_local);
							if($resupdateZonePkgExp)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$zone_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$main_zone." Zone - Expiry Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($main_zone.' Zone - Expiry Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($main_zone.' Zone - Expiry Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['zonebdgt'])>0)
			{
				$maxdiscount_package			= floatval($params['zonebdgt']['maxval']);
				$maxdiscount_package			= number_format($maxdiscount_package, 2);
				$discount_eligibility_package	= (intval($params['zonebdgt']['eligib'])>0) ? intval($params['zonebdgt']['eligib']) : 0;
				$zonepkgdisclogstr 				= json_encode($params['zonebdgt']['L']);
				
				$zonediscmismatchpkg 			= trim($params['zonebdgt']['zonediffcitydiscpkg']);
				if(!empty($zonediscmismatchpkg)){
					$zonediscmismatchpkg_arr = array();
					$zonediscmismatchpkg_arr = explode("|",$zonediscmismatchpkg);
					$zone_exclusion_city_str = implode("','",$zonediscmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				$zonewise_citystr = implode("','",$zonewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtZonePkgDiscRCity = $query_str;
				$resUpdtZonePkgDiscRCity = parent::execQuery($sqlUpdtZonePkgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtZonePkgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$zone_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$main_zone." Zone - Package Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$zonepkgdisclogstr."'";
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
							$sqlupdateZonePkgDisc 	= $query_str;
							$resupdateZonePkgDisc 	= parent::execQuery($sqlupdateZonePkgDisc, $conn_city_local);
							if($resupdateZonePkgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$zone_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$main_zone." Zone - Package Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($main_zone.' Zone - Package Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($main_zone.' Zone - Package Discount Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'premadpkg')
		{
			if(count($params['zonebdgt'])>0)
			{
				$extra_pkg_details_arr 	= array();
				$comboextradetails_arr 	= array();
				foreach($params['zonebdgt']['znpkgpremadbdgt'] as $znpkgprembdgtkey => $znpkgprembdgtval){
					$znpkgprembdgtval	= (intval($znpkgprembdgtval)>0) ? intval($znpkgprembdgtval) : 0;
					$extra_pkg_details_arr[$znpkgprembdgtkey]['package_value'] = $znpkgprembdgtval;
					$extra_pkg_details_arr[$znpkgprembdgtkey]['display_upfront'] 	= 1; // passing default value as 1 , as told by pramesh & ganesh
					$extra_pkg_details_arr[$znpkgprembdgtkey]['display_ecs'] 		= 1;
					
					
					$prem_related_cid = $this->extrapkg_rid_arr[$znpkgprembdgtkey];
					$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $znpkgprembdgtval * 12;
					$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $znpkgprembdgtval;
					$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
					$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
				}
				$zonepkgdisclogstr 				= json_encode($params['zonebdgt']['L']);
				$zonepremmismatchpkg 			= trim($params['zonebdgt']['zonediffcityprempkg']);
				if(!empty($zonepremmismatchpkg)){
					$zonepremmismatchpkg_arr = array();
					$zonepremmismatchpkg_arr = explode("|",$zonepremmismatchpkg);
					$zone_exclusion_city_str = implode("','",$zonepremmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$zone_exclusion_city_str."') ";
				}
				if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
				{
					$extra_package_details 	= json_encode($extra_pkg_details_arr);
					$comboextradetails		= json_encode($comboextradetails_arr);
					
					$zonewise_citystr = implode("','",$zonewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET extra_package_details = '".$extra_package_details."', comboextradetails = '".$comboextradetails."' WHERE city IN ('".$zonewise_citystr."') ".$exclude_city_condn."";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtZonePkgDiscRCity = $query_str;
					$resUpdtZonePkgDiscRCity = parent::execQuery($sqlUpdtZonePkgDiscRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtZonePkgDiscRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$zone_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$main_zone." Zone - Premium Ad Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$zonepkgdisclogstr."'";
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
								$sqlupdateZonePkgDisc 	= $query_str;
								$resupdateZonePkgDisc 	= parent::execQuery($sqlupdateZonePkgDisc, $conn_city_local);
								if($resupdateZonePkgDisc)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$zone_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$main_zone." Zone - Premium Ad Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($main_zone.' Zone - Premium Ad Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($main_zone.' Zone - Premium Ad Package Update',$query_str);
						}
					}
				}
			}
		}
		return $resultArr;
	}
	
	function updateStatePackage($params)
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
				$top_minbudget_package	= (intval($params['statebdgt']['top200'])>0) ? intval($params['statebdgt']['top200']) : 0;
				$minbudget_package		= (intval($params['statebdgt']['normal'])>0) ? intval($params['statebdgt']['normal']) : 0;
				$package_mini			= (intval($params['statebdgt']['mini'])>0) ? intval($params['statebdgt']['mini']) : 0;
				$package_mini_ecs		= (intval($params['statebdgt']['miniecs'])>0) ? intval($params['statebdgt']['miniecs']) : 0;
				$package_premium		= (intval($params['statebdgt']['premium'])>0) ? intval($params['statebdgt']['premium']) : 0;
				$package_premium_upfront= (intval($params['statebdgt']['premupfrnt'])>0) ? intval($params['statebdgt']['premupfrnt']) : 0;
				$cstm_minbudget_package	= (intval($params['statebdgt']['custom'])>0) ? intval($params['statebdgt']['custom']) : 0;
				$package_mini_minimum	= (intval($params['statebdgt']['flxcustom'])>0) ? intval($params['statebdgt']['flxcustom']) : 0;
				$statediffcitypkg 		= trim($params['statebdgt']['statediffcitypkg']);
				$statepkgcatlogstr 		= json_encode($params['statebdgt']['L']);
				if(!empty($statediffcitypkg)){
					$statediffcitypkg_arr = array();
					$statediffcitypkg_arr = explode("|",$statediffcitypkg);
					$state_exclusion_city_str = implode("','",$statediffcitypkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				$statewise_citystr = implode("','",$statewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."',  package_premium = '".$package_premium."',  package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', package_mini_minimum = '".$package_mini_minimum."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtStatePkgCatRCity = $query_str;
				$resUpdtStatePkgCatRCity = parent::execQuery($sqlUpdtStatePkgCatRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtStatePkgCatRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$state_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$state_name." State - Category Wise Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$statepkgcatlogstr."'";
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
							$sqlupdateStatePkgCat 	= $query_str;
							$resupdateStatePkgCat 	= parent::execQuery($sqlupdateStatePkgCat, $conn_city_local);
							if($resupdateStatePkgCat)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$state_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$state_name." State - Category Wise Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($state_name.' State - Category Wise Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($state_name.' State - Category Wise Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'teamwise')
		{
			if(count($params['statebdgt'])>0)
			{
				$statepkg_teambdgt_arr = array();
				foreach($params['statebdgt']['statepkgtmbdgt'] as $statepkgtmbdgtkey => $statepkgtmbdgtval){
					$statepkgtmbdgtval	= (intval($statepkgtmbdgtval)>0) ? intval($statepkgtmbdgtval) : 0;
					$statepkg_teambdgt_arr[$statepkgtmbdgtkey] = $statepkgtmbdgtval;
				}
				$statepkgteamlogstr 		= json_encode($params['statebdgt']['L']);
				if(count($statepkg_teambdgt_arr)>0)
				{
					$team_minbudget_package = json_encode($statepkg_teambdgt_arr);
					$statewise_citystr = implode("','",$statewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET team_minbudget_package = '".$team_minbudget_package."' WHERE city IN ('".$statewise_citystr."')";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtStatePkgTeamRCity = $query_str;
					$resUpdtStatePkgTeamRCity = parent::execQuery($sqlUpdtStatePkgTeamRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtStatePkgTeamRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$state_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$state_name." State - Team Wise Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$statepkgteamlogstr."'";
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
								$sqlupdateStatePkgTeam 	= $query_str;
								$resupdateStatePkgTeam 	= parent::execQuery($sqlupdateStatePkgTeam, $conn_city_local);
								if($resupdateStatePkgTeam)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$state_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$state_name." State - Team Wise Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($state_name.' State - Team Wise Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($state_name.' State - Team Wise Package Update',$query_str);
						}
					}
				}
			}
		}
		else if(strtolower($type) == 'expire')
		{
			if(count($params['statebdgt'])>0)
			{
				$exppackday		= (intval($params['statebdgt']['expday'])>0) ? intval($params['statebdgt']['expday']) : 0;
				$exppackval		= (intval($params['statebdgt']['exp1yrbdgt'])>0) ? intval($params['statebdgt']['exp1yrbdgt']) : 0;
				$exppackval_2	= (intval($params['statebdgt']['exp2yrbdgt'])>0) ? intval($params['statebdgt']['exp2yrbdgt']) : 0;
				$statepkgexplogstr 	= json_encode($params['statebdgt']['L']);
				
				$stateexpmismatchpkg 			= trim($params['statebdgt']['statediffcityexppkg']);
				if(!empty($stateexpmismatchpkg)){
					$stateexpmismatchpkg_arr = array();
					$stateexpmismatchpkg_arr = explode("|",$stateexpmismatchpkg);
					$state_exclusion_city_str = implode("','",$stateexpmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				$statewise_citystr = implode("','",$statewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtStatePkgExpRCity = $query_str;
				$resUpdtStatePkgExpRCity = parent::execQuery($sqlUpdtStatePkgExpRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtStatePkgExpRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$state_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$state_name." State - Expiry Package Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$statepkgexplogstr."'";
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
							$sqlupdateStatePkgExp 	= $query_str;
							$resupdateStatePkgExp 	= parent::execQuery($sqlupdateStatePkgExp, $conn_city_local);
							if($resupdateStatePkgExp)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$state_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$state_name." State - Expiry Package Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($state_name.' State - Expiry Package Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($state_name.' State - Expiry Package Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'discount')
		{
			if(count($params['statebdgt'])>0)
			{
				$maxdiscount_package			= floatval($params['statebdgt']['maxval']);
				$maxdiscount_package			= number_format($maxdiscount_package, 2);
				$discount_eligibility_package	= (intval($params['statebdgt']['eligib'])>0) ? intval($params['statebdgt']['eligib']) : 0;
				$statepkgdisclogstr 				= json_encode($params['statebdgt']['L']);
				
				$statediscmismatchpkg 			= trim($params['statebdgt']['statediffcitydiscpkg']);
				if(!empty($statediscmismatchpkg)){
					$statediscmismatchpkg_arr = array();
					$statediscmismatchpkg_arr = explode("|",$statediscmismatchpkg);
					$state_exclusion_city_str = implode("','",$statediscmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				$statewise_citystr = implode("','",$statewise_cityarr);
				$query_str = "UPDATE tbl_business_uploadrates SET maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
				
				$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
				$sqlUpdtStatePkgDiscRCity = $query_str;
				$resUpdtStatePkgDiscRCity = parent::execQuery($sqlUpdtStatePkgDiscRCity, $requested_city_local); // updating on requested city first
				
				if($resUpdtStatePkgDiscRCity)
				{
					$resultArr['errorcode'] = 0;
					unset($this->all_cities_arr[$requested_city]);
					$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
										campaign_name 	= 'Package',
										city_name 		= '".$state_city_name."',
										ucode 			= '".$params['ucode']."',
										uname 			= '".addslashes($params['uname'])."',
										insertdate 		= '".date("Y-m-d H:i:s")."',
										ip_address		= '".$params['ipaddr']."',
										query_str		= '".addslashes($query_str)."',
										param_str		= '".$param_str."',
										comment			= '".$state_name." State - Package Discount Update',
										uniqueid		= '".$uniqueid."',
										log_str			= '".$statepkgdisclogstr."'";
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
							$sqlupdateStatePkgDisc 	= $query_str;
							$resupdateStatePkgDisc 	= parent::execQuery($sqlupdateStatePkgDisc, $conn_city_local);
							if($resupdateStatePkgDisc)
							{
								$j ++;
								$city_update_arr[] = $cityvalue;
								$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
													  campaign_name 	= 'Package',
													  city_name 		= '".$state_city_name."',
													  ucode 			= '".$params['ucode']."',
													  uname 			= '".addslashes($params['uname'])."',
													  insertdate 		= '".date("Y-m-d H:i:s")."',
													  ip_address		= '".$params['ipaddr']."',
													  query_str			= '".addslashes($query_str)."',
													  param_str			= '".$param_str."',
													  comment			= '".$state_name." State - Package Discount Update'";
								$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
							}
						}
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
						if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
							$this->sendErrorMsg($state_name.' State - Package Discount Update',$query_str);
						}
					}
					catch(Exception $e) {
						$city_update_str = implode(",",$city_update_arr);
						$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
						$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
						$this->sendErrorMsg($state_name.' State - Package Discount Update',$query_str);
					}
				}
			}
		}
		else if(strtolower($type) == 'premadpkg')
		{
			if(count($params['statebdgt'])>0)
			{
				$extra_pkg_details_arr 	= array();
				$comboextradetails_arr 	= array();
				foreach($params['statebdgt']['stpkgpremadbdgt'] as $stpkgprembdgtkey => $stpkgprembdgtval){
					$stpkgprembdgtval	= (intval($stpkgprembdgtval)>0) ? intval($stpkgprembdgtval) : 0;
					$extra_pkg_details_arr[$stpkgprembdgtkey]['package_value'] = $stpkgprembdgtval;
					$extra_pkg_details_arr[$stpkgprembdgtkey]['display_upfront'] 	= 1; // passing default value as 1 , as told by pramesh & ganesh
					$extra_pkg_details_arr[$stpkgprembdgtkey]['display_ecs'] 		= 1;
					
					
					$prem_related_cid = $this->extrapkg_rid_arr[$stpkgprembdgtkey];
					$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $stpkgprembdgtval * 12;
					$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $stpkgprembdgtval;
					$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
					$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
				}
				$statepkgpremlogstr 	= json_encode($params['statebdgt']['L']);
				$statepremmismatchpkg 	= trim($params['statebdgt']['statediffcityprempkg']);
				if(!empty($statepremmismatchpkg)){
					$statepremmismatchpkg_arr = array();
					$statepremmismatchpkg_arr = explode("|",$statepremmismatchpkg);
					$state_exclusion_city_str = implode("','",$statepremmismatchpkg_arr);
					$exclude_city_condn = " AND city NOT IN ('".$state_exclusion_city_str."') ";
				}
				if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
				{
					$extra_package_details 	= json_encode($extra_pkg_details_arr);
					$comboextradetails		= json_encode($comboextradetails_arr);
					
					$statewise_citystr = implode("','",$statewise_cityarr);
					$query_str = "UPDATE tbl_business_uploadrates SET extra_package_details = '".$extra_package_details."', comboextradetails = '".$comboextradetails."' WHERE city IN ('".$statewise_citystr."') ".$exclude_city_condn."";
					
					$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
					$sqlUpdtStatePkgPremRCity = $query_str;
					$resUpdtStatePkgPremRCity = parent::execQuery($sqlUpdtStatePkgPremRCity, $requested_city_local); // updating on requested city first
					
					if($resUpdtStatePkgPremRCity)
					{
						$resultArr['errorcode'] = 0;
						unset($this->all_cities_arr[$requested_city]);
						$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
											campaign_name 	= 'Package',
											city_name 		= '".$state_city_name."',
											ucode 			= '".$params['ucode']."',
											uname 			= '".addslashes($params['uname'])."',
											insertdate 		= '".date("Y-m-d H:i:s")."',
											ip_address		= '".$params['ipaddr']."',
											query_str		= '".addslashes($query_str)."',
											param_str		= '".$param_str."',
											comment			= '".$state_name." State - Premium Ad Package Update',
											uniqueid		= '".$uniqueid."',
											log_str			= '".$statepkgpremlogstr."'";
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
								$sqlupdateStatePkgPrem 	= $query_str;
								$resupdateStatePkgPrem 	= parent::execQuery($sqlupdateStatePkgPrem, $conn_city_local);
								if($resupdateStatePkgPrem)
								{
									$j ++;
									$city_update_arr[] = $cityvalue;
									$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
														  campaign_name 	= 'Package',
														  city_name 		= '".$state_city_name."',
														  ucode 			= '".$params['ucode']."',
														  uname 			= '".addslashes($params['uname'])."',
														  insertdate 		= '".date("Y-m-d H:i:s")."',
														  ip_address		= '".$params['ipaddr']."',
														  query_str			= '".addslashes($query_str)."',
														  param_str			= '".$param_str."',
														  comment			= '".$state_name." State - Premium Ad Package Update'";
									$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
								}
							}
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
							if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
								$this->sendErrorMsg($state_name.' State - Premium Ad Package Update',$query_str);
							}
						}
						catch(Exception $e) {
							$city_update_str = implode(",",$city_update_arr);
							$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
							$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
							$this->sendErrorMsg($state_name.' State - Premium Ad Package Update',$query_str);
						}
					}
				}
			}
		}
		return $resultArr;
	}
	
	
	function updateRemoteBudgetPkg($params)
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
			$top_minbudget_package			= (intval($params['remotebdgt']['top200'])>0) ? intval($params['remotebdgt']['top200']) : 0;
			$minbudget_package				= (intval($params['remotebdgt']['normal'])>0) ? intval($params['remotebdgt']['normal']) : 0;
			$package_mini					= (intval($params['remotebdgt']['mini'])>0) ? intval($params['remotebdgt']['mini']) : 0;
			$package_mini_ecs				= (intval($params['remotebdgt']['miniecs'])>0) ? intval($params['remotebdgt']['miniecs']) : 0;
			$package_premium				= (intval($params['remotebdgt']['premium'])>0) ? intval($params['remotebdgt']['premium']) : 0;
			$package_premium_upfront		= (intval($params['remotebdgt']['premupfrnt'])>0) ? intval($params['remotebdgt']['premupfrnt']) : 0;
			$cstm_minbudget_package			= (intval($params['remotebdgt']['custom'])>0) ? intval($params['remotebdgt']['custom']) : 0;
			$exppackday						= (intval($params['remotebdgt']['expday'])>0) ? intval($params['remotebdgt']['expday']) : 0;
			$exppackval						= (intval($params['remotebdgt']['exp1'])>0) ? intval($params['remotebdgt']['exp1']) : 0;
			$exppackval_2					= (intval($params['remotebdgt']['exp2'])>0) ? intval($params['remotebdgt']['exp2']) : 0;
			$maxdiscount_package			= floatval($params['remotebdgt']['maxval']);
			$maxdiscount_package			= number_format($maxdiscount_package, 2);
			$discount_eligibility_package	= (intval($params['remotebdgt']['eligib'])>0) ? intval($params['remotebdgt']['eligib']) : 0;
			$package_mini_minimum			= (intval($params['remotebdgt']['flxcustom'])>0) ? intval($params['remotebdgt']['flxcustom']) : 0;
			
			
			
			$extra_pkg_details_arr 	= array();
			$comboextradetails_arr 	= array();
			foreach($params['remotebdgt']['premadpkg'] as $rmtpkgprembdgtkey => $rmtpkgprembdgtval){
				$rmtpkgprembdgtval	= (intval($rmtpkgprembdgtval)>0) ? intval($rmtpkgprembdgtval) : 0;
				$extra_pkg_details_arr[$rmtpkgprembdgtkey]['package_value'] = $rmtpkgprembdgtval;
				$extra_pkg_details_arr[$rmtpkgprembdgtkey]['display_upfront'] 	= 1; // passing default value as 1 , as told by pramesh & ganesh
				$extra_pkg_details_arr[$rmtpkgprembdgtkey]['display_ecs'] 		= 1;
				
				
				$prem_related_cid = $this->extrapkg_rid_arr[$rmtpkgprembdgtkey];
				$comboextradetails_arr[$prem_related_cid]['combo_upfront'] 	= $rmtpkgprembdgtval * 12;
				$comboextradetails_arr[$prem_related_cid]['combo_ecs'] 		= $rmtpkgprembdgtval;
				$comboextradetails_arr[$prem_related_cid]['display_upfront']= 1;
				$comboextradetails_arr[$prem_related_cid]['display_ecs'] 	= 1;
			}
			if((count($extra_pkg_details_arr)>0) && (count($comboextradetails_arr) >0 ))
			{
				$extra_package_details 	= json_encode($extra_pkg_details_arr);
				$comboextradetails		= json_encode($comboextradetails_arr);
				
				$extra_package_details_str 	= " , extra_package_details = '".$extra_package_details."' ";
				$comboextradetails_str 		= " , comboextradetails = '".$comboextradetails."' ";
			}
			
			$remotepkgcatlogstr 	= json_encode($params['remotebdgt']['L']);
				
			$query_str = "UPDATE tbl_business_uploadrates SET top_minbudget_package = '".$top_minbudget_package."', minbudget_package = '".$minbudget_package."', package_mini = '".$package_mini."', package_mini_ecs = '".$package_mini_ecs."', package_premium = '".$package_premium."', package_premium_upfront = '".$package_premium_upfront."', cstm_minbudget_package = '".$cstm_minbudget_package."', exppackday = '".$exppackday."', exppackval = '".$exppackval."', exppackval_2 = '".$exppackval_2."', maxdiscount_package = '".$maxdiscount_package."', discount_eligibility_package = '".$discount_eligibility_package."', package_mini_minimum = '".$package_mini_minimum."' ".$extra_package_details_str." ".$comboextradetails_str." WHERE city = '".$this->data_city."'"; 
			
			$requested_city_local 	 = $db[$requested_city]['d_jds']['master'];
			$sqlUpdtRemotePkgCatRCity = $query_str;
			$resUpdtRemotePkgCatRCity = parent::execQuery($sqlUpdtRemotePkgCatRCity, $requested_city_local); // updating on requested city first
			
			if($resUpdtRemotePkgCatRCity)
			{
				$resultArr['errorcode'] = 0;
				unset($this->all_cities_arr[$requested_city]);
				$sqlInsertLogIDC = "INSERT INTO online_regis1.tbl_budgetchange_log_idc SET
									campaign_name 	= 'Package',
									city_name 		= '".$this->data_city."',
									ucode 			= '".$params['ucode']."',
									uname 			= '".addslashes($params['uname'])."',
									insertdate 		= '".date("Y-m-d H:i:s")."',
									ip_address		= '".$params['ipaddr']."',
									query_str		= '".addslashes($query_str)."',
									param_str		= '".$param_str."',
									comment			= 'Remote City Package Budget Update',
									uniqueid		= '".$uniqueid."',
									log_str			= '".$remotepkgcatlogstr."'";
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
						$sqlupdateRemotePkgRest = $query_str;
						$resupdateRemotePkgRest = parent::execQuery($sqlupdateRemotePkgRest, $conn_city_local);
						if($resupdateRemotePkgRest)
						{
							$j ++;
							$city_update_arr[] = $cityvalue;
							$sqlInsertLogLocal = "INSERT INTO tbl_budgetchange_log SET
												  campaign_name 	= 'Package',
												  city_name 		= '".$this->data_city."',
												  ucode 			= '".$params['ucode']."',
												  uname 			= '".addslashes($params['uname'])."',
												  insertdate 		= '".date("Y-m-d H:i:s")."',
												  ip_address		= '".$params['ipaddr']."',
												  query_str			= '".addslashes($query_str)."',
												  param_str			= '".$param_str."',
												  comment			= 'Remote City Package Budget Update'";
							$resInsertLogLocal = parent::execQuery($sqlInsertLogLocal, $conn_city_local);
						}
					}
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdate = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdate = parent::execQuery($sqlCityUpdate, $this->conn_idc);
					if((!$resCityUpdate) || ($i !=$j) || ($city_update_str == '')){
						$this->sendErrorMsg('Remote City Package Budget Update',$query_str);
					}
				}
				catch(Exception $e) {
					$city_update_str = implode(",",$city_update_arr);
					$sqlCityUpdtError = "UPDATE online_regis1.tbl_budgetchange_log_idc SET city_update_str = '".$city_update_str."' WHERE uniqueid = '".$uniqueid."'";
					$resCityUpdtError = parent::execQuery($sqlCityUpdtError, $this->conn_idc);
					$this->sendErrorMsg('Remote City Package Budget Update',$query_str);
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
	function getZoneName($city)
	{
		global $db;
		$conn_local_slave = $db['remote']['d_jds']['slave'];
		$zonename = 'ZNF';
		$sqlZoneName = "SELECT Cities,main_zone FROM tbl_zone_cities WHERE Cities = '".$city."'";
		$resZoneName = parent::execQuery($sqlZoneName, $conn_local_slave);
		if($resZoneName && parent::numRows($resZoneName)>0)
		{
			$row_zonename 	= parent::fetchData($resZoneName);
			$zonename 		= trim($row_zonename['main_zone']);
			$zonename		= ucwords(strtolower($zonename));
		}
		return $zonename;
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
	function getMismatchCityBdgtInfo($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchCityBdgtInfo = "SELECT city,top_minbudget_package,minbudget_package,package_mini,package_mini_ecs,package_premium,package_premium_upfront,cstm_minbudget_package,package_mini_minimum FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchCityBdgtInfo = parent::execQuery($sqlMismatchCityBdgtInfo, $this->conn_local);
		if($resMismatchCityBdgtInfo && parent::numRows($resMismatchCityBdgtInfo)>0)
		{
			while($row_mismatch_city = parent::fetchData($resMismatchCityBdgtInfo))
			{
				$city 						= trim($row_mismatch_city['city']);
				$top_minbudget_package 		= trim($row_mismatch_city['top_minbudget_package']);
				$minbudget_package 			= trim($row_mismatch_city['minbudget_package']);
				$package_mini 				= trim($row_mismatch_city['package_mini']);
				$package_mini_ecs 			= trim($row_mismatch_city['package_mini_ecs']);
				$package_premium 			= trim($row_mismatch_city['package_premium']);
				$package_premium_upfront	= trim($row_mismatch_city['package_premium_upfront']);
				$cstm_minbudget_package		= intval($row_mismatch_city['cstm_minbudget_package']);
				$package_mini_minimum		= intval($row_mismatch_city['package_mini_minimum']);
				$mismatchDataArr[$city]['top'] 			= $top_minbudget_package;
				$mismatchDataArr[$city]['norm'] 		= $minbudget_package; 
				$mismatchDataArr[$city]['mini'] 		= $package_mini;
				$mismatchDataArr[$city]['miniecs'] 		= $package_mini_ecs;
				$mismatchDataArr[$city]['premium'] 		= $package_premium;
				$mismatchDataArr[$city]['premupfrnt'] 	= $package_premium_upfront;
				$mismatchDataArr[$city]['custom'] 		= $cstm_minbudget_package;
				$mismatchDataArr[$city]['flxcustom'] 	= $package_mini_minimum;
			}
		}
		return $mismatchDataArr;
	}
	function getMismatchExpiryBudgetPkg($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchExpBdgtInfo = "SELECT city,exppackday,exppackval,exppackval_2 FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchExpBdgtInfo = parent::execQuery($sqlMismatchExpBdgtInfo, $this->conn_local);
		if($resMismatchExpBdgtInfo && parent::numRows($resMismatchExpBdgtInfo)>0)
		{
			while($row_mismatch_exp = parent::fetchData($resMismatchExpBdgtInfo))
			{
				$city 				= trim($row_mismatch_exp['city']);
				$exppackday 		= intval($row_mismatch_exp['exppackday']);
				$exppackval 		= intval($row_mismatch_exp['exppackval']);
				$exppackval_2 		= intval($row_mismatch_exp['exppackval_2']);
				$mismatchDataArr[$city]['tenure'] 	= $exppackday;
				$mismatchDataArr[$city]['bdgt1yr'] 	= $exppackval; 
				$mismatchDataArr[$city]['bdgt2yr'] 	= $exppackval_2; 
			}
		}
		return $mismatchDataArr;
	}
	function getMismatchDiscountPkg($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchDiscountInfo = "SELECT city,maxdiscount_package,discount_eligibility_package FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchDiscountInfo = parent::execQuery($sqlMismatchDiscountInfo, $this->conn_local);
		if($resMismatchDiscountInfo && parent::numRows($resMismatchDiscountInfo)>0)
		{
			while($row_mismatch_disc = parent::fetchData($resMismatchDiscountInfo))
			{
				$city 					  = trim($row_mismatch_disc['city']);
				$maxdiscount_package 		= floatval($row_mismatch_disc['maxdiscount_package']);
				$maxdiscount_package		= number_format($maxdiscount_package, 2);
				
				$discount_eligibility_pkg = intval($row_mismatch_disc['discount_eligibility_package']);
				$mismatchDataArr[$city]['maxval'] 	= $maxdiscount_package; 
				$mismatchDataArr[$city]['eligib'] 	= $discount_eligibility_pkg; 
			}
		}
		return $mismatchDataArr;
	}
	function getMismatchPremAdPkg($cityarr)
	{
		$mismatchDataArr = array();
		$citystr = implode("','",$cityarr);
		$sqlMismatchPremAdInfo = "SELECT city,extra_package_details FROM tbl_business_uploadrates WHERE city IN ('".$citystr."')";
		$resMismatchPremAdInfo = parent::execQuery($sqlMismatchPremAdInfo, $this->conn_local);
		if($resMismatchPremAdInfo && parent::numRows($resMismatchPremAdInfo)>0)
		{
			while($row_mismatch_prem = parent::fetchData($resMismatchPremAdInfo))
			{
				$city 					= trim($row_mismatch_prem['city']);
				$extra_package_details 	= trim($row_mismatch_prem['extra_package_details']);
				
				$extrapkg_arr = array();
				$extra_package_details_arr = array();
				$extra_package_details_arr 	= json_decode($extra_package_details,true);
				if(count($extra_package_details_arr)>0){
					$extra_pkg_cid_arr = array_keys($this->extra_pkg_info);
					foreach($extra_package_details_arr as $campid => $campdetails){
						if(in_array($campid,$extra_pkg_cid_arr) && isset($campdetails['package_value'])){
							$extrapkg_arr[$campid] = $campdetails['package_value'];
						}
					}
				}
				if(count($extrapkg_arr)>0){
					$mismatchDataArr[$city] = $extrapkg_arr;
				}
			}
		}
		return $mismatchDataArr;
	}
	function extraPackagDetails(){
		
		$extra_pkg_arr = array();
		$sqlExtraPkgDetails = "SELECT campaignid,related_campaignid,name FROM online_regis1.tbl_campaignid_mapping";
		$resExtraPkgDetails = parent::execQuery($sqlExtraPkgDetails, $this->conn_idc);
		if($resExtraPkgDetails && parent::numRows($resExtraPkgDetails)>0){
			while($row_extra_pkg = parent::fetchData($resExtraPkgDetails)){
				$campaignid 		= trim($row_extra_pkg['campaignid']);
				$related_campaignid = trim($row_extra_pkg['related_campaignid']);
				$name				= trim($row_extra_pkg['name']);
				$extra_pkg_arr[$campaignid]['rcid'] = $related_campaignid;
				$extra_pkg_arr[$campaignid]['name'] = $name;
				$this->extrapkg_id_arr[$campaignid] = $name;
				$this->extrapkg_rid_arr[$campaignid] = $related_campaignid;
			}
		}
		return $extra_pkg_arr;
	}
	function arrayProcess($requestedArr)
	{
		$processedArr = array();
		if(count($requestedArr)>0){
			$processedArr = array_merge(array_unique(array_filter($requestedArr)));
		}
		return $processedArr;
	}
	function exportToExcelPkg($params)
	{
		$request = trim($params['request']);
		if($request == 'pkgt1')
		{
			$filename="package_top11_cities";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$pkgt1_table =",,     Package Top 11 Cities Budget   ,,\r\n";
			$pkgt1_table .= "City Name, Top 200 Budget, Normal Budget, Expiry One Yr Budget, Expiry Two Yr Budget\r\n";
			
			$sqlPkgExcelTier1 = "SELECT city,top_minbudget_package,minbudget_package,exppackday,exppackval,exppackval_2 FROM tbl_business_uploadrates WHERE city IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
			$resPkgExcelTier1 = parent::execQuery($sqlPkgExcelTier1, $this->conn_local);
			if($resPkgExcelTier1 && parent::numRows($resPkgExcelTier1)>0)
			{
				$i = 0;
				while($row_tier1excelpkg 		= parent::fetchData($resPkgExcelTier1))
				{
					$city 					= trim($row_tier1excelpkg['city']);
					$city					= ucwords(strtolower($city));
					$top_minbudget_package 	= trim($row_tier1excelpkg['top_minbudget_package']);
					$minbudget_package 		= trim($row_tier1excelpkg['minbudget_package']);
					$exppackday 			= trim($row_tier1excelpkg['exppackday']);
					$exppackval 			= trim($row_tier1excelpkg['exppackval']);
					$exppackval_2 			= trim($row_tier1excelpkg['exppackval_2']);
					
					$pkgt1_table.=str_replace(","," ",$city).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$exppackval).",".str_replace(","," ",$exppackval_2).","."\r\n";
				}
				
			}
			$pkgt1_table = str_replace(",","\t",$pkgt1_table);
			echo $pkgt1_table;
		}
		else if($request == 'pkgt2')
		{
			$filename="package_remote_tier2";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$pkgt2_table  =",,     Package Remote Tier2 Budget   ,,\r\n";
			$pkgt2_table .= "City Name, Top 200 Budget, Normal Budget, Expiry One Yr Budget, Expiry Two Yr Budget\r\n";
			$sqlPkgExcelTier2 = "SELECT city,top_minbudget_package,minbudget_package,exppackday,exppackval,exppackval_2 FROM tbl_business_uploadrates WHERE tier = 2 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
			$resPkgExcelTier2 = parent::execQuery($sqlPkgExcelTier2, $this->conn_local);
			if($resPkgExcelTier2 && parent::numRows($resPkgExcelTier2)>0)
			{
				$i = 0;
				while($row_tier2excelpkg 		= parent::fetchData($resPkgExcelTier2))
				{
					$city 					= trim($row_tier2excelpkg['city']);
					$city					= ucwords(strtolower($city));
					//$zonename				= $this->getZoneName($city);
					$top_minbudget_package 	= trim($row_tier2excelpkg['top_minbudget_package']);
					$minbudget_package 		= trim($row_tier2excelpkg['minbudget_package']);
					$exppackday 			= trim($row_tier2excelpkg['exppackday']);
					$exppackval 			= trim($row_tier2excelpkg['exppackval']);
					$exppackval_2 			= trim($row_tier2excelpkg['exppackval_2']);
					
					$pkgt2_table.=str_replace(","," ",$city).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$exppackval).",".str_replace(","," ",$exppackval_2).","."\r\n";
				}
				$pkgt2_table = str_replace(",","\t",$pkgt2_table);
				echo $pkgt2_table;
			}
		}
		else if($request == 'pkgt3')
		{
			$filename="package_remote_tier3";
			header("Content-type: application/excel");
			header("Content-Disposition: attachment; filename=".$filename.".xls");
			$pkgt3_table  =",,     Package Remote Tier3 Budget   ,,\r\n";
			$pkgt3_table .= "City Name, Top 200 Budget, Normal Budget, Expiry One Yr Budget, Expiry Two Yr Budget\r\n";
			$sqlPkgExcelTier3 = "SELECT city,top_minbudget_package,minbudget_package,exppackday,exppackval,exppackval_2 FROM tbl_business_uploadrates WHERE tier = 3 AND city NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') ORDER BY city";
			$resPkgExcelTier3 = parent::execQuery($sqlPkgExcelTier3, $this->conn_local);
			if($resPkgExcelTier3 && parent::numRows($resPkgExcelTier3)>0)
			{
				$i = 0;
				while($row_tier3excelpkg 		= parent::fetchData($resPkgExcelTier3))
				{
					$city 					= trim($row_tier3excelpkg['city']);
					$city					= ucwords(strtolower($city));
					//$zonename				= $this->getZoneName($city);
					$top_minbudget_package 	= trim($row_tier3excelpkg['top_minbudget_package']);
					$minbudget_package 		= trim($row_tier3excelpkg['minbudget_package']);
					$exppackday 			= trim($row_tier3excelpkg['exppackday']);
					$exppackval 			= trim($row_tier3excelpkg['exppackval']);
					$exppackval_2 			= trim($row_tier3excelpkg['exppackval_2']);
					
					$pkgt3_table.=str_replace(","," ",$city).",".str_replace(","," ",$top_minbudget_package).",".str_replace(","," ",$minbudget_package).",".str_replace(","," ",$exppackval).",".str_replace(","," ",$exppackval_2).","."\r\n";
				}
				$pkgt3_table = str_replace(",","\t",$pkgt3_table);
				echo $pkgt3_table;
			}
		}
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
	public function fetchtmepricing(){
		$sel_sql = "SELECT * FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
		$res = parent::execQuery($sel_sql,$this->conn_local);
		$res_arr = array();
		$res_arr['data'] = array();
		if($res && mysql_num_rows($res)>0){
			$res_row=mysql_fetch_assoc($res);
			$res_arr['error']['msg'] = 'success';
			$res_arr['error']['code'] ='0';
			$res_arr['data'] =json_decode($res_row['PriceList']);
		}else{
			$res_arr['error']['msg'] = 'data not found';
			$res_arr['error']['code'] ='1';
		}
		return $res_arr;
	}
	public function updatetmepricing($param){
		$res_arr = array();
		$update_main	=	"UPDATE d_jds.pricing_citywise SET PriceList = '".$param['newjson']."' WHERE city='".$this->data_city."'";
		$main_res = parent::execQuery($update_main,$this->conn_local);
		
		$update_idc	=	"UPDATE online_regis.pricing_citywise SET PriceList = '".$param['newjson']."' WHERE city='".$this->data_city."'";
		$idc_res = parent::execQuery($update_idc,$this->conn_idc);
		
		$insert_log	=	"INSERT INTO online_regis.tbl_pricing_new SET PriceList = '".$param['logjson']."',empcode='".$param['empcode']."',updatedOn=now(),city='".$this->data_city."'";
		$log_res = parent::execQuery($insert_log,$this->conn_idc);
		
		if($main_res){
			$res_arr['error']['msg'] = 'success';
			$res_arr['error']['code'] ='0';
		}else{
			$res_arr['error']['msg'] = 'failed';
			$res_arr['error']['code'] ='1';
		}
		return $res_arr;
	}
}
?>
