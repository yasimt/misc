<?php
set_time_limit(0);
ini_set('memory_limit','512M');
#######################################
/*

FILENAME : CalculateBudget.php

PURPOSE  : 1.Function to initialize the object of AreaBiz class
		   2.Function to initialize requested parameter
           3.Function to return callcnt growth rate of respective city
           4.Function to get selected pincodes 
           5.Function to set pincode details in an array with its details for calculating budget
           6.Function to set node values of a pincode in xml
           7.Function to calculate budget
 
CREATOR  : Raj Yadav (rajkumaryadav@justdial.com)

DATE     : 30th July, 2010

Note : Formula for calculating Phone search(i.e plat/diam budget)

Budget(cat,pincode) =( plat bid(cat,pincode) or diam bid(cat,pincode) ) * per day callcount(cat,pincode) * callcount growth rate * Web Factor * Tenure * available inventory
 
*/
#######################################

class CalculateBudget
{

	const WEB_FACTOR = '1.33';/*declare constant for web*/
	
	const POS_D  = '15';/*declare constant for platinum */
	
	const POS_DG = '10';/*declare constant for diamond*/
	
	const POS_B = '8';	/*declare constant for bronze*/
	
	const POS_AP = '5';/*declare constant for Package*/
	
	const POS_LD = '0';/*declare constant for lead*/
	
	const NONB2B_MIN_BID_VAL = '5';/*minimum bid value for non b2b category*/
	
	const D_B2B_MIN_BID_VAL = '100';/*minimum platinum bid value for b2b category*/
	
	const DDG_B2B_MIN_BID_VAL = '75';/*minimum diamond bid value for b2b category*/
	
	const B_B2B_MIN_BID_VAL = '70';/*minimum gold bid value for b2b category*/
	
	const MIN_INVENTORY = '0.05';/*minimum inventory to be provided*/
	
	const MIN_CALLCNT = '1';/*minimum callcount*/ 
	
	const MULTIPLIER = '5';/*multiplier in order to check inventory in the multiple of 5%*/
	
	const MIN_PLAT_BUDGET = '12000';/*minimum platinum budget */
	
	const MIN_DIAM_BUDGET = '10200';/*minimum diamond budget */
	
	const MIN_GOLD_BUDGET = '9000';/*minimum gold budget */
	
	const MIN_CITY_BUDGET = '9000';/*minimum gold budget */
	
	const NEXT_INVENTORY	= '5';		/**/
	
	const ACT_INVENTORY	= '100';		/**/
	
	const MAX_INVENTORY	= '100';		/**/

    const MIN_PACK_INCREMENT_FACTOR = '1.5';/*minimum package factor */
    
    const PLAT_INCREMENT_FACTOR = '1.3';/*incrementing platinum budget from 10% to 30% - platinum factor */
    
    const MIN_ADVANCE_FACTOR = '3';/*minimum advance factor to be multiplied with category per day*/
    
    const MIN_AVG_FACTOR = '1';/*minimum factor for matching contracts*/ 
    
    const MAX_AVG_FACTOR = '3';/*maximum factor for matching contracts*/ 
    
    const DEFAULT_MIN_FACTOR = '1'; /*min factor for check with advance amount*/
    
    const DISCOUNT_FACTOR = '0.67'; /*min DISCOUNT factor NEW-200515*/
	
	public $areab_Obj ;/*declare it for the reference of AreaBiz class */
	
	public $inventory_booking;/*declare it for the reference of inventory_booking_management class*/
	
	private $callcnt_growth_rate;/*declare callcnt growth rate */
	
	private $reqPos,$availPos;/*declare requested and available position*/
	
	private $reqInv,$availInv;/*declare requested and available inventory*/
	
	private $reqTen;/*declare requested tenure*/
	
	private $catid,$zoneid,$pincode;/*declare catid,pincode to check for availability*/
	
	private $callcnt,$bid_val,$daily_deduct_amt;/*declare callcnt,plat bid val,diam bid val*/
	
	public  $increment_factor;/*declare increment factor - will be different for mumbai and other cities(instructed by Sandipan sir)*/
	
	private $min_plat_budget,$min_diam_budget,$min_gold_budget;/*declare two variables to check for minimum platinum and diamond budget*/
	
	private $category_arr;/*declare main category array */
	
	private $totPhoneBudget;
	
	public $totPincodes,$totPerDay;/*declaring number of pincodes as public*/
	
	public $category_to_delete;/*declare array to store pure pacakge category for deletion in case of skip*/
	
	private $parentid;/*property to store 'P' parentid*/
	
	private $data_city, $remote_city_arr;
	
	private $pdgPos;
	
	public $outOfCity;
	
	private $city_minimum_budget,$cat_avg_callcnt;/*cities minimum budget*/
	
	public function __construct($dbarr,$parentid=null)
    {/*Require for INSTANTIATING object of AreaBiz class */
		
		
		$this -> dbconn_fnc	= new DB($dbarr['DB_FNC']);
		
		$this -> dbconn_decs= new DB($dbarr['DB_DECS']);
		
		$this -> dbconn_iro	= new DB($dbarr['DB_IRO']);
		
		$this -> dbconn_tme	= new DB($dbarr['DB_TME']);
		
		$this -> dbconn_idc	= new DB($dbarr['IDC']);
		
		$this -> conn_local = new DB($dbarr['LOCAL']);
		
		$this -> inventory_booking = new inventory_booking_management($dbarr);/*To get available inventory and position*/
		
		$this -> parentid   = $parentid;
		
        /*$this->sphinx_id =  getContractSphinxId ($this->parentid); 

        $genio_variables = get_company_data($this->sphinx_id);
		
		
		$this -> bform_pincode = $genio_variables['pincode'];initializing to get superzone from my pincode
        
        $this->financeObj= new company_master_finance($dbarr,$this->parentid,$this->sphinx_id); */
		
		$this -> category_to_delete = array();/*init array to store pure pacakge category for deletion in case of skip*/
		
		$this -> remote_city_arr = array("mumbai","delhi","hyderabad","kolkata","bangalore","chennai","pune","ahmedabad","jaipur","chandigarh","coimbatore");/*all major cities*/
		
		//$this -> getMyZone();/*initializing myzone */
		
		//$this -> getTotPincodes();/*function to initialize $totPincodes for count of pincodes*/
        $this->all_catid_array = array();
        //$this->all_catid_array = $this -> getCatid();
	
		//$this->outOfCity = $this->checkCityPincode();
		
		$this->cat_avg_callcnt = 0;

		$this->compmaster_obj = new companyMasterClass($this -> dbconn_iro,'',$this->parentid);
    }
	
	function InitReqParam($ReqPos,$ReqInv,$ReqTen)
	{/*function to initialize requested parameters(position,inventory,tenure)*/
		
		$this -> reqPos = $ReqPos;
		
		$this -> reqInv = $ReqInv/100;
		
		$this -> reqTen = $ReqTen;
	}
	
	function InitCalcParam($data_city,$bform_pincode,$exclusive_flag=0,$renew_budget=0){/*function which would initialize some parameters required for budget calculation*/
		$this->data_city = $data_city;
		$this->bform_pincode = $bform_pincode;
		$this -> getMyZone();/*initializing myzone */
		$this -> getTotPincodes();/*initializing total number of pincodes of a contract*/
		$this->outOfCity = $this->checkCityPincode();/*to check if its out of data_city*/
		$this -> callcnt_growth_rate = $this -> getCallcnt_Growth_Rate();/* intializing  city's minimum budget*/
		$this -> exclusive_flag = $exclusive_flag;/*setting exclusive flag*/
		$this -> renew_budget   = $renew_budget;/*setting renew_budget flag to get budget on previous position and inventory*/
	}
	
	function getCallcnt_Growth_Rate()
	{/*function to return call count growth rate for city*/
		$sql = "SELECT callcnt_per,addon_premium,minbudget  FROM tbl_business_uploadrates WHERE city='".$this-> data_city."'";
		
		$res =$this -> conn_local->query_sql($sql);
		
		if($res && mysql_num_rows($res)>0)
		{
			$row = mysql_fetch_assoc($res);
			
			$this -> increment_factor=($row['addon_premium']>0)?(1 + $row['addon_premium']/100):((defined('REMOTE_CITY_MODULE'))?'1':'0');
			if($row['minbudget']>0)
			{
				$this -> city_minimum_budget = $row['minbudget'];
			}
			else
			{
				$this -> city_minimum_budget = 5000;
			}
			return (1 + $row['callcnt_per']/100);
		}
		
	}
	
	function getMinBudget()
	{/*function which would return minimum platinum and diamond budget*/
					
		$sql = "SELECT platinum_search_rate as min_plat_budget,diamond_search_rate as min_diam_budget, bronze_search_rate as min_gold_budget FROM tbl_business_uploadrates WHERE ";
		if(!in_array(strtolower($this -> data_city),$this -> remote_city_arr))
		{
			$sql.="city not in ( '".implode("','",$this -> remote_city_arr)."') limit 1";
		}
		else
		{
			$sql.="city = '".$this -> data_city."'";
		}
		$res = $this -> areab_Obj -> query_sql($sql);
		
		if($res && mysql_num_rows($res)>0)
		{
			$row = $this -> areab_Obj -> fetchData($res);
			
			return $row;
		}
		
	}
	
	function getMyZone()
	{/*function to get super zones(near by zones) of zone to which mypincode belongs to*/
		
		$sql="SELECT zoneid FROM tbl_area_master WHERE pincode='".$this -> bform_pincode."' and data_city='".$this->data_city."' and display_flag=1 and deleted='0'";
		$res = $this -> conn_local -> query_sql($sql);
		if($res && mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			if($row['zoneid'])
			{
				$this -> myzone = $row['zoneid'];
			}
		}
	}
	
	function getTotPincodes()
	{/*function to get the total number of pincodes*/
		$sql = "SELECT count(distinct pincode) as totPincodes FROM tbl_area_master WHERE data_city='".$this->data_city."'  AND display_flag=1 AND deleted=0 ";
		if(defined('REMOTE_CITY_MODULE'))
		{
			$sql.="  AND display_flag>0 and deleted=0";
		}
		
		$res = $this -> conn_local -> query_sql($sql);
		
		if($res && mysql_num_rows($res)>0)
		{
			$row = mysql_fetch_assoc($res);
			
			$this -> totPincodes = $row['totPincodes'];
		 
		}
		
	}
	
	function getExistingDdgCategoryPincodeArr()
	{
		$category_inventory_array = array();
		$sql_bcd_ddg = "SELECT * FROM tbl_bidcatdetails_ddg WHERE PARENTID='".$this->parentid."'";
		$res_bcd_ddg = $this -> dbconn_fnc->query_sql($sql_bcd_ddg);
		if($res_bcd_ddg && mysql_num_rows($res_bcd_ddg))
		{
			while ($row_bcd_ddg = mysql_fetch_assoc($res_bcd_ddg))
			{
				$category_inventory_array[$row_bcd_ddg['bid_catid']][$row_bcd_ddg['pincode']]['position']  = $row_bcd_ddg['position_flag'];
				$category_inventory_array[$row_bcd_ddg['bid_catid']][$row_bcd_ddg['pincode']]['inventory'] = $row_bcd_ddg['partial_ddg_ratio'];
			}
		}
		return $category_inventory_array;
	}
	
	function getExistingPackageCategoryPincodeArr()
	{
		$category_inventory_array = array();
		$sql_bcd_ddg = "SELECT * FROM tbl_bidcatdetails_supreme WHERE PARENTID='".$this->parentid."'";
		$res_bcd_ddg = $this -> dbconn_fnc->query_sql($sql_bcd_ddg);
		if($res_bcd_ddg && mysql_num_rows($res_bcd_ddg))
		{
			while ($row_bcd_ddg = mysql_fetch_assoc($res_bcd_ddg))
			{
				$category_inventory_array[$row_bcd_ddg['bid_catid']][$row_bcd_ddg['pincode']]['position']  = $row_bcd_ddg['position_flag'];
			}
		}
		return $category_inventory_array;
	}
	
	
	function getSelPin($ip_status,$only_ddg,$categories_arr)
	{/*function to calculate and save details of all the pincodes of a contract in respective xml*/
			
			
		   $catType=array('A','Z','SZ','L');
		   $this -> category_arr = array();
		   $sum_contract_callcount = $this-> GetSumC2scallcnt();
		   $this->all_catid_array  = array_keys($categories_arr);
		   $call_count_array = $this->GetCatCallcount($this->all_catid_array);
		   $outCity	=	$this->outOfCity;
		   $existing_category_position_arr = $this->getExistingDdgCategoryPincodeArr();
		   $existing_pack_category_position_arr = $this->getExistingPackageCategoryPincodeArr();
		   
		   //echo 'arra data<pre>';
		   //print_r($existing_category_position_arr);
		   
		   if(count($categories_arr)>0)
		   {
		   	
		   	$category_inventory_array = array();
			foreach ($categories_arr as $catid => $zone_arr)
			{
				unset($cat_row);
				unset($pincodes_with_comma_arr);
				unset($inventory_arr);
				unset($inventory_d_arr);
				unset($inventory_dg_arr);
				unset($pincodes_with_comma);
				unset($totalInv_d_arr);
				unset($invNotPDArr);
				unset($pincodePackage);
				$this -> catid = $catid;/*set cat id property */
				$cat_row = $this -> getCategoryinfo($this -> catid);
				$pincodes_with_comma_arr = array();
				$package_value =0;			
				$plat_diam_value=0;
				if(!$outCity){
					//$category -> setAttribute("ocp","");
				}else{
						//$category -> removeAttribute("ocp");
				}
				foreach($zone_arr as $zoneid => $pincode_arr)
				{
					
					//$this -> zoneid = $zoneid;/*set zone id property by xml node value */		
						
					$pincodes_with_comma_arr = array_merge($pincodes_with_comma_arr,$pincode_arr);
                    $pincodes_with_comma_arr = array_unique($pincodes_with_comma_arr);  
                    sort($pincodes_with_comma_arr);
                      /*if(!in_array($pincodeElement -> getAttribute('id'),$significance_pincodes))
						{
							$this->removeNode[$this -> catid][] = $pincodeElement -> getAttribute('id');
						}*/
										  
				}
				if (!$this->outOfCity && in_array($this->bform_pincode, $pincodes_with_comma_arr)) 
				{ 
					unset($pincodes_with_comma_arr[array_search($this->bform_pincode,$pincodes_with_comma_arr)]);
					$pincodes_with_comma_arr = array_values($pincodes_with_comma_arr);
				}
				//echo '<br> value of check box ::  '.$this -> renew_budget;
				//echo 'ddg array<pre>';
				//print_r($existing_category_position_arr);
				//echo 'package array<pre>';
				//print_r($existing_pack_category_position_arr);
				if(!$existing_category_position_arr[$catid][$this->bform_pincode]['position'] && $existing_pack_category_position_arr[$catid][$this->bform_pincode]['position'])
				{
					$existing_category_position_arr[$catid][$this->bform_pincode]['position'] = $existing_pack_category_position_arr[$catid][$this->bform_pincode]['position'];
					$existing_category_position_arr[$catid][$this->bform_pincode]['inventory'] = $this -> reqInv;
				}
				//echo '<pre>';
				//print_r($existing_category_position_arr);
				$pincodes_with_comma= implode(',',$pincodes_with_comma_arr);
				$matching_contract_factor=$this -> Matching_Contract_Factor($this-> catid,$call_count_array,$sum_contract_callcount);
				if($this->reqPos != self :: POS_AP)
				{
					if(!in_array($this->reqPos,array(self :: POS_DG,self :: POS_B)))	{	
						$inventory_d_arr 	= $this->inventory_booking->CategoryPincodes($this -> catid,$pincodes_with_comma,$ip_status,$this -> parentid,$this -> reqInv,self :: POS_D,$cat_row['b2b'],$this -> totPincodes,$cat_row['incremented_callcnt'],$cat_row['top_flag'],$this -> exclusive_flag,$this -> renew_budget,$existing_category_position_arr);
						
					}
					if(!$this -> exclusive_flag){
						if($this->reqPos != self :: POS_B){
							$inventory_dg_arr 	= $this->inventory_booking->CategoryPincodes($this -> catid,$pincodes_with_comma,$ip_status,$this -> parentid,$this -> reqInv,self :: POS_DG,$cat_row['b2b'],$this -> totPincodes,$cat_row['incremented_callcnt'],$cat_row['top_flag'],$this -> exclusive_flag,$this -> renew_budget,$existing_category_position_arr);
						}
						
						//if($this->reqPos == self :: POS_B){
							$inventory_g_arr 	= $this->inventory_booking->CategoryPincodes($this -> catid,$pincodes_with_comma,$ip_status,$this -> parentid,$this -> reqInv,self :: POS_B,$cat_row['b2b'],$this -> totPincodes,$cat_row['incremented_callcnt'],$cat_row['top_flag'],$this -> exclusive_flag,$this -> renew_budget,$existing_category_position_arr);
						//} 
							$totalInv_d_arr 	=  $this->inventory_booking->CategoryPincodesTotal($this -> catid,$pincodes_with_comma,$ip_status,$this -> parentid,'1',self :: POS_D,$cat_row['b2b'],$this->totPincodes,$cat_row['incremented_callcnt'],$cat_row['top_flag']);
					}
					
					//$this->writeMFfile($this-> catid,$call_count_array,$sum_contract_callcount,$matching_contract_factor,$category_contribution=0);
				}
					//echo '<pre>catid :: '.$this -> catid;
					//echo 'd arra';
					//print_r($inventory_d_arr);
					//echo 'dg arra';
					//print_r($inventory_dg_arr);
					//echo 'g arra';
					//print_r($inventory_g_arr);
					$inventory_arr = $this -> ComparePDBudget($inventory_d_arr,$inventory_dg_arr,$inventory_g_arr,$this -> catid,$pincodes_with_comma_arr,'1');
					$plat_diam_value=$this -> getCategoryBudget($inventory_arr,'1',$pincodes_with_comma_arr);
					
				if($ip_status == 6)
				{
					
					$is_package = $this -> checkPackagePosition($inventory_arr,$pincodes_with_comma_arr);
                    if(count($pincodes_with_comma_arr)>0)
                    {
					    $package_value   = $this->inventory_booking->GetCategoryPackageValue($this -> catid,$ip_status,$this->parentid,$cat_row['cat_type'],$this -> myzone,$this-> data_city,$this -> reqTen,$call_count_array,$sum_contract_callcount,'2');
                    }
					if($_SERVER['REMOTE_ADDR'] == '172.29.5.85' || $_SERVER['REMOTE_ADDR'] == '172.29.5.47'){
						echo '<br><b>category id ->'.$this ->catid.' :: plat/diam value ->'.$plat_diam_value.' :: pack vale ->'.$package_value;print "</b>";
					}
					$originalPackValue =$package_value;
					
					if((($plat_diam_value>$package_value && $only_ddg != '1') || ($plat_diam_value>0 && $only_ddg == '1') || ($plat_diam_value>0 && $this -> exclusive_flag) || ($plat_diam_value>0 && $this -> renew_budget)))
					{
						$category_budget[$this->catid]['budget']=$plat_diam_value;
						foreach($inventory_arr as $catid => $catidArr){
							foreach($catidArr as $pincodes=>$pincodeArr){
								foreach($pincodeArr as $position => $positionArr){
									$positionTempArr[] = $position;
									if($position == '5'){
										$pincodePackage[] = $pincodes;
									}
								}
							}
						}
						if(in_array('15',$positionTempArr)){
							$category_budget[$this->catid]['position'] = '15';
						}elseif(in_array('10',$positionTempArr)){
							$category_budget[$this->catid]['position'] = '10';
						}elseif(in_array('8',$positionTempArr)){
							$category_budget[$this->catid]['position'] = '8';
						}elseif(in_array('5',$positionTempArr)){
							$category_budget[$this->catid]['position'] = '5';
						}
						
						if($is_package && $package_value>0 && !$this -> exclusive_flag)
						{
							/* Inventory Array for package pincodes */
							if(count($pincodePackage)>0){
								$pincodePackage = array_unique($pincodePackage);
								foreach($totalInv_d_arr as $catid => $catidArr){
									foreach($catidArr as $pincodes=>$pincodeArr){
										if(in_array($pincodes,$pincodePackage)){
											$invNotPDArr[$catid][$pincodes] = $totalInv_d_arr[$catid][$pincodes];
										}						
									}
								}
							}
							$noPDGBudget	= $this -> getTotalCategoryBudget($invNotPDArr,$matching_contract_factor);
							$plat_Total_value	= $this -> getTotalCategoryBudget($totalInv_d_arr,$matching_contract_factor);
							$package_value	= ($noPDGBudget/$plat_Total_value)*$package_value;
							$category_budget[$this->catid]['pack_budget'] 	= $package_value;
							$inventory_arr[$this->catid][$this -> bform_pincode][self :: POS_AP]['bidperday'] = max(($package_value/$this->reqTen),0.00001);
							if(!$outCity){
								//$category -> setAttribute("ocp",$this->catid."~".($package_value/$this->reqTen));
							}
						}else{
							if(!$this->outOfCity){
								unset($inventory_arr[$this->catid][$this -> bform_pincode]);
							}
						}
					}
					else if(!$this -> exclusive_flag){
						$category_budget[$this->catid]['pack_budget']=$package_value*$matching_contract_factor;
						$category_budget[$this->catid]['position'] = 5;
						
						$this -> resetInventoryArr($inventory_arr,$category_budget[$this->catid]['pack_budget']/$this->reqTen,$pincodes_with_comma_arr);
						if(!$outCity){
							//$category -> setAttribute("ocp",$this->catid."~".($package_value/$this->reqTen));
						}
					}
					if($plat_diam_value>0 && $category_budget[$this->catid]['budget'] >0){
						$originalPDBudget  = $plat_diam_value;
						$difference	= ($originalPackValue*$matching_contract_factor)-$originalPackValue;
						if($category_budget[$this->catid]['pack_budget'] > 0){
							$totalBudget= ($plat_diam_value + $package_value);
							$platRatio	= $difference*($plat_diam_value/$totalBudget);
							$packRatio	= $difference*($package_value/$totalBudget);
							$plat_diam_value = $plat_diam_value + $platRatio;
							$package_value	= $package_value + $packRatio;
						}else{
							$plat_diam_value = $plat_diam_value + $difference;
						}
						$category_budget[$this->catid]['budget'] 		= $plat_diam_value;
						$category_budget[$this->catid]['pack_budget'];
						if($category_budget[$this->catid]['pack_budget'] > 0)
							$category_budget[$this->catid]['pack_budget'] 	= $package_value;
						$incrementedFactor = $plat_diam_value/$originalPDBudget;
						$this -> getCategoryBudget($inventory_arr,$incrementedFactor,$pincodes_with_comma_arr);
					}
				}
				$category_inventory_array[$catid]=$inventory_arr;
				
				//$this -> updateXMl($category,$inventory_arr);
				
			  }
			  
             /* if(count($this->removeNode)>0)
                {
                    $this->removeUnselectNode($xmlDoc,$this->removeNode,$filename);
                }*/
			}
	  	$this -> check_set_min_budget($category_budget);
	  	if(count($category_budget)>0)
	  	{
			foreach($category_budget as $catid => $budget_arr)
			{
				$category_inventory_array[$catid]['budget_details']=$budget_arr;
			}
		}
	    return $category_inventory_array;	
	   
    }

    function removeUnselectNode($xmlDoc,$id_array,$filename)
    {
        $all_xml = $xmlDoc->documentElement;
        $xpath = new DomXPath($xmlDoc);
        $categories = $all_xml -> getElementsByTagName( "cti" );
        foreach($categories as $category)
        {
            $zoneElements = $category -> getElementsByTagName("zid");
            foreach($zoneElements as $zoneElement)
            {
                $i=0;
                $pi_id=array();
                $pincodeElements=$zoneElement -> getElementsByTagName("pin");
                foreach($pincodeElements as $pincodeElement)
                {
                    $node = '';
                    if(count($id_array[$category -> getAttribute('id')])>0)
                    {
                        if(in_array($pincodeElement -> getAttribute('id'),$id_array[$category -> getAttribute('id')]))
                        {
                            $id=$pincodeElement -> getAttribute('id');
                            
                            $nodeList = $xpath->query('//pin[@id="'.(int)$id.'"]');
                            if ($nodeList->length) {
                                $node = $nodeList->item(0);
                                array_push($pi_id,$node);
                                //print_r($nodeList->item(0));
                                //$node->parentNode->removeChild($node);
                                //$xmlDoc->save($filename);
                            }
                            
                            /*$query = "/cti/zid/pin[id='".$pincodeElement -> getAttribute('id')."']";*/
                        }
                    }
                    
                    $i++;
                }
                if(count($pi_id)>0)
                {
                    foreach($pi_id as $ids)
                    {
                        //$node->parentNode->removeChild($ids);
                        $zoneElement->removeChild($ids);
                    }
                }
            }
        }
    }
    
    function Matching_Contract_Factor($catid,$call_count_array,$c2s_callcount)/*this would return registration fee factor*/
	{
		return self :: MIN_AVG_FACTOR;/*doing it on temp basis as suggested by sandy sir and ajay mohan*/
		
    	$cat_avg_callcnt =0;
            if(defined("REMOTE_CITY_MODULE"))
            {
				$sql_main = " select maincity from tbl_remotecity_maincity_mapping where remotecity = '".$this->data_city."'";
				$qry_main = $this->dbconn_fnc->query_sql($sql_main);
				if($qry_main){
					$row_main	= mysql_fetch_assoc($qry_main);
					$rmCity		= $row_main['maincity'];
				}
                $sql ="SELECT cat_avg_callcnt FROM tbl_package_perday_price_remote WHERE catid='".$catid."' AND data_city='".$rmCity."'";
                //$sql ="SELECT * FROM tbl_package_perday_price_remote WHERE catid='".$catid."' AND data_city='".$this-> data_city."'";
            }
            else
            {
                $sql ="SELECT cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND data_city='".$this-> data_city."'";
            }
			
    		$res = $this -> dbconn_fnc -> query_sql($sql);
			
    		if($res && mysql_num_rows($res)>0)
    		{
				$row =mysql_fetch_assoc($res);
				if($row['cat_avg_callcnt']>0)
				{
					$cat_avg_callcnt=$row['cat_avg_callcnt'];
					$this->cat_avg_callcnt = $row['cat_avg_callcnt'];
						if($_SERVER['REMOTE_ADDR'] == '172.29.5.85' || $_SERVER['REMOTE_ADDR'] == '172.29.5.28' || $_SERVER['REMOTE_ADDR'] == '172.29.5.47'){
						echo '<br>'.$catid.'---plat fact---(sqr root)'.sqrt((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$cat_avg_callcnt)."==> cube root -->".pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$cat_avg_callcnt),1/3);
					}
					return round(MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$row['cat_avg_callcnt']),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
				}
				else
				{
					return self :: MIN_AVG_FACTOR;
				}
			}
            else
            {
                return self :: MIN_AVG_FACTOR;
            }
	}
    
    function checkFixedPosition($inventory_arr,$category_type_pincodes_arr)
    {/*this function checks for wheather any pincode present in inventory array either with platinum or diamond*/
    	$flag=0;
    	foreach($category_type_pincodes_arr as $pincode)
    	{
    		if((is_array($inventory_arr[$this -> catid][$pincode][self :: POS_D]) || is_array($inventory_arr[$this -> catid][$pincode][self :: POS_DG])))
    		{
    			return $flag=1;
    			
			}
		}
		
	}
							
	function checkPackagePosition($inventory_arr,$category_type_pincodes_arr)
    {/*this function checks for wheather any pincode present in inventory array either with platinum or diamond*/
    	$flag=0;
    	foreach($category_type_pincodes_arr as $pincode)
    	{
    		if(is_array($inventory_arr[$this -> catid][$pincode][self :: POS_AP]))
    		{
    			return $flag=1;
    			
			}
		}
		
	}
	
    function resetInventoryArr(&$inventory_arr,$category_package_avg_val,$pincode_arr)
    {
    /*function which would unset platinum,diamond position and set for package with blank array*/	
    	if(count($pincode_arr)>0)
        {
            foreach($inventory_arr as $catid => $pincode_arr)
            {
                foreach($pincode_arr as $pincode => $position_arr)
                {
                    foreach($position_arr as $position => $pos_value)
                    {
                        if($pos_value['inv']>0 && in_array($position,array(self :: POS_D, self :: POS_DG, self :: POS_B)))
                        {
                            unset($inventory_arr[$catid][$pincode][$position]);
                        }    				
                    }
                    $inventory_arr[$catid][$pincode][5] = array();
                    if($pincode == $this -> bform_pincode)
                    {
                        $inventory_arr[$catid][$pincode][5]['bidperday'] = $category_package_avg_val;    					
                    }
                }
                
                if(!$this->outOfCity && !$inventory_arr[$catid][$this -> bform_pincode]){
                	$inventory_arr[$catid][$this -> bform_pincode][5]['bidperday'] = $category_package_avg_val;
				}
            }
        }
	}
	
	function takeDecision(&$category_budget)
	{/*This function called to decide what is to be given at the contract level*/
		if(count($category_budget)>0)
		{
			$plat_diam_budget = 0;
			$package_budget = 0;
			foreach($category_budget as $value)
			{
				$plat_diam_budget += $value['budget'];
				$package_budget   += $value['pack_budget'];
			}
			if($plat_diam_budget>$package_budget)
			{
				foreach($category_budget as $catid => $value)
				{
					if($value['budget']>0)
					{
						$category_budget[$catid]['pack_budget'] = 0;
					}
				}
			}
			if($package_budget>$plat_diam_budget)
			{
				foreach($category_budget as $catid => $value)
				{
					$category_budget[$catid]['budget'] = 0;
				}
				$this -> ChangeCategoriesPostion();
			}
		}
	}
	
	function check_set_min_budget(&$category_budget)
	{/*function to check and update minimum budget*/
		if(count($category_budget)>0)
		{
			$plat_diam_budget = 0;
			$package_budget = 0;
			foreach($category_budget as $value)
			{
				$plat_diam_budget += $value['budget'];
				$package_budget   += $value['pack_budget'];
			}
			$total_budget = $plat_diam_budget + $package_budget;
			if($total_budget>0 && ($this ->city_minimum_budget/$total_budget)>1 && $plat_diam_budget>0)
			{
				$factor = $this ->city_minimum_budget/$total_budget;
				foreach($category_budget as $catid =>$value)
				{
					if($value['budget']>0)
					$category_budget[$catid]['budget'] = $value['budget']*$factor;
					
					if($value['pack_budget']>0)
					$category_budget[$catid]['pack_budget'] = $value['pack_budget']*$factor;
				}
			}
		}
	}
	
    function getCategoryBudget(&$inventory_arr,$matching_contract_factor,$pinecode_arr)
    {
        if(count($pinecode_arr)>0)
        {
            foreach($inventory_arr as $catid => $pincode_arr)
            {
                foreach($pincode_arr as $pincode => $position_arr)
                {
                    foreach($position_arr as $position => $pos_value)
                    {
                        if($pos_value['inv']>0 && in_array($position,array(self :: POS_D, self :: POS_DG, self :: POS_B)))
                        {
                        	if($this->exclusive_flag){
                        		$ddg_budget +=($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * ($this -> reqTen) * ($pos_value['inv']);
                            	$inventory_arr[$catid][$pincode][$position]['bidperday'] = ($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * ($pos_value['inv']);
							}else{
								$ddg_budget +=($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ($pos_value['inv']) * ($matching_contract_factor) * (self :: PLAT_INCREMENT_FACTOR) * (self :: DISCOUNT_FACTOR);
								$inventory_arr[$catid][$pincode][$position]['bidperday'] = ($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($pos_value['inv']) * ($matching_contract_factor) * (self :: PLAT_INCREMENT_FACTOR)* (self :: DISCOUNT_FACTOR);
							}
							$total_bidperday += $inventory_arr[$catid][$pincode][$position]['bidperday'];
                        }else if ($this->exclusive_flag){
                        	unset($inventory_arr[$catid][$pincode][5]);
						}
                    }	
                }
            }
            return $ddg_budget;
        }
	}
	
	function getTotalCategoryBudget(&$inventory_arr,$matching_contract_factor)
	{
		
		if(count($inventory_arr)>0){
			foreach($inventory_arr as $catid => $pincode_arr)
			{
				foreach($pincode_arr as $pincode => $position_arr)
				{
					foreach($position_arr as $position => $pos_value)
					{
						$ddg_budget +=($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ($matching_contract_factor) * (self :: PLAT_INCREMENT_FACTOR)* (self :: DISCOUNT_FACTOR);
						
						$inventory_arr[$catid][$pincode][$position]['bidperday'] = ($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($matching_contract_factor) * (self :: PLAT_INCREMENT_FACTOR)* (self :: DISCOUNT_FACTOR);
						$total_bidperday += $inventory_arr[$catid][$pincode][$position]['bidperday'];
					
					}	
				}
			}
		}
		return $ddg_budget;
    	//echo "Total Budget ::".$ddg_budget;    	
	}
	
	function getCategoryPincodePositionBudget($pos_value,$matching_contract_factor)
	{
		return $budget = ($pos_value['bid_value']) * ($pos_value['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ($pos_value['inv']) * ($matching_contract_factor) * (self :: PLAT_INCREMENT_FACTOR)* (self :: DISCOUNT_FACTOR);
	}
	
	function ComparePDBudget($inventory_d_arr,$inventory_dg_arr,$inventory_g_arr,$catid,$pincode_arr,$matching_contract_factor)
	{
		
		//echo 'array1<pre>';
		//print_r($inventory_d_arr);
		//echo 'array2<pre>';
		//print_r($inventory_dg_arr);
		//echo 'array3<pre>';
		//print_r($inventory_g_arr);
	   if(count($pincode_arr)>0)
	   {
	   	if($_SERVER['REMOTE_ADDR'] == '172.29.5.85' || $_SERVER['REMOTE_ADDR'] == '172.29.5.1137' || $_SERVER['REMOTE_ADDR'] == '172.29.5.47'){
			print "<br><B>Catid::".$catid."</b>";
		}
		foreach($pincode_arr as $pincode)
		{
			$plat_budget = 0;
			$diam_budget = 0;
			$gold_budget = 0;
			
			if(count($inventory_d_arr[$catid][$pincode][self :: POS_D])>0)
			{
				$plat_budget = $this -> getCategoryPincodePositionBudget($inventory_d_arr[$catid][$pincode][self :: POS_D],$matching_contract_factor);
				
			}else if($this->renew_budget)
			{
				if(count($inventory_d_arr[$catid][$pincode][self :: POS_DG])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_d_arr[$catid][$pincode][self :: POS_DG],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_DG] = $inventory_d_arr[$catid][$pincode][self :: POS_DG];
					}
				}elseif(count($inventory_d_arr[$catid][$pincode][self :: POS_B])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_d_arr[$catid][$pincode][self :: POS_B],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_B] = $inventory_d_arr[$catid][$pincode][self :: POS_B];
					}
				}
			}
			
			if(count($inventory_dg_arr[$catid][$pincode][self :: POS_DG])>0)
			{
				$diam_budget = $this -> getCategoryPincodePositionBudget($inventory_dg_arr[$catid][$pincode][self :: POS_DG],$matching_contract_factor);
			}else if($this->renew_budget)
			{
				
				if(count($inventory_dg_arr[$catid][$pincode][self :: POS_D])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_dg_arr[$catid][$pincode][self :: POS_D],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_D] = $inventory_dg_arr[$catid][$pincode][self :: POS_D];
					}
				}elseif(count($inventory_dg_arr[$catid][$pincode][self :: POS_B])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_dg_arr[$catid][$pincode][self :: POS_B],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_B] = $inventory_dg_arr[$catid][$pincode][self :: POS_B];
					}
				}
			}
			
			if(count($inventory_g_arr[$catid][$pincode][self :: POS_B])>0)
			{
				$gold_budget = $this -> getCategoryPincodePositionBudget($inventory_g_arr[$catid][$pincode][self :: POS_B],$matching_contract_factor);
			}else if($this->renew_budget){
				
				if(count($inventory_g_arr[$catid][$pincode][self :: POS_D])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_g_arr[$catid][$pincode][self :: POS_D],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_D] = $inventory_g_arr[$catid][$pincode][self :: POS_D];
					}
				}elseif(count($inventory_g_arr[$catid][$pincode][self :: POS_DG])>0)
				{
					if(($this -> getCategoryPincodePositionBudget($inventory_g_arr[$catid][$pincode][self :: POS_DG],$matching_contract_factor))>0)
					{
						$inventory_arr[$catid][$pincode][self :: POS_DG] = $inventory_g_arr[$catid][$pincode][self :: POS_DG];
					}
				}
			}
			
			//echo '<br>'.$catid.'----------'.$pincode.'--------'.$plat_budget.'------'.$diam_budget.'------'.$gold_budget;
			if($plat_budget>0 || $diam_budget>0 || $gold_budget>0)
			{
				if(($plat_budget>$diam_budget || ($plat_budget>0 && round($plat_budget,4) == round($diam_budget,4))) && ($plat_budget>$gold_budget || ($plat_budget>0 && round($plat_budget,4) == round($gold_budget,4)))){
					$inventory_arr[$catid][$pincode][self :: POS_D] = $inventory_d_arr[$catid][$pincode][self :: POS_D];
				}else if(($diam_budget>$gold_budget) || ($diam_budget>0 && round($diam_budget,4) == round($gold_budget,4))){
					$inventory_arr[$catid][$pincode][self :: POS_DG] = $inventory_dg_arr[$catid][$pincode][self :: POS_DG];
				}else if($gold_budget>0){
					$inventory_arr[$catid][$pincode][self :: POS_B] = $inventory_g_arr[$catid][$pincode][self :: POS_B];
				}
				
			}
			elseif(!$this->renew_budget || ($this->renew_budget && !count($inventory_arr[$catid][$pincode])))
			{
				$inventory_arr[$catid][$pincode][self :: POS_AP] = array();
			}
			if($_SERVER['REMOTE_ADDR'] == '172.29.5.85' || $_SERVER['REMOTE_ADDR'] == '172.29.5.1137' || $_SERVER['REMOTE_ADDR'] == '172.29.5.47'){
				print "<BR>Pincode::".$pincode." Plat Budget::".$plat_budget." Diam Budget ::".$diam_budget." Gold Budget :: ".$gold_budget;
			}			
		}
		
		return $inventory_arr;
	  }
		
	}
	
	function TotalPackageValue($budget_arr,$tenure_factor)
	{/*function which would sum package value of all categories*/
    
        $min_pack_budget = $this->GetMinPackVal();
		$limitArr		 = $this->getLowestLimit('1');
		if(count($budget_arr))
		{
			foreach ($budget_arr as $value)
			{
				$packagebudget += ($value['pack_budget'] * $tenure_factor);
                $platbudget    += ($value['budget'] * $tenure_factor);
			}
			
			if($platbudget>0 && $packagebudget>0)
            {
				if($packagebudget < $limitArr['package']){
					$packagebudget = $limitArr['package'];
				}
                return round(($packagebudget),2);
            }
            elseif($packagebudget>0)
			{
				if($packagebudget < $limitArr['package']){
					$packagebudget = $limitArr['package'];
				}
                return MAX(round(($packagebudget),2),$this ->city_minimum_budget);
			}
		}
	}
    
	function getAskedBudget($position,$inventory,$tenure,$budget_arr,$ip_status,$conn_temp=null)
	{
		$positionArr = array();
		if(count($budget_arr)>0){
			foreach($budget_arr as $catid => $catidArr){
				$positionArr[] = $budget_arr[$catid]['position'];
			}
		}
		
		if(in_array('15',$positionArr))
			$position = '15';
		else if(in_array('10',$positionArr))
			$position = '10';
		else{
			$position = $_POST['fixed_position'];
		}
		if($conn_temp !=null){
			$upPosition = "UPDATE tbl_temp_intermediate SET pdg_position = '".$position."' WHERE parentid='".$this->parentid."'";
			$qryupPosition = $conn_temp->query_sql($upPosition);
		}
		
		$limitArr	= $this -> getLowestLimit('2',$positionArr);/*function to get minimum limit of  phone search budget*/
		if(count($budget_arr)>0)
		{
			foreach($budget_arr as $value)
			{
				$returnBudget += $value['budget'];
			}
			if($returnBudget > 0)
			{	
				if($returnBudget < $limitArr['pd']){
					$returnBudget = $limitArr['pd'];
				}
				return round($returnBudget,2);
				
			}
	  }
	}
    
    function updateXMl($category,$inventory_arr)
    {/*function to update xml nodes of a category*/
    	$catid = $category -> getAttribute('id');/*set cat id property by xml node value */
		$zoneElements = $category -> getElementsByTagName( "zid" );	
		foreach($zoneElements as $zoneElement)
		{
			$zoneid = $zoneElement -> getAttribute('id');/*set zone id property by xml node value */
			$pincodeElements=$zoneElement -> getElementsByTagName( "pin" );
			foreach($pincodeElements as $pincodeElement)
			{
			  $selAttribute 	  = $pincodeElement -> getAttribute("sel"); 
			  
			  $mypincode_to_check = $pincodeElement -> getAttribute('id');/*set pincode property by xml node value */
			  if($selAttribute == 'Y' ||  $mypincode_to_check == $_SESSION['pincode'])
			  {
			  	  if($mypincode_to_check == $_SESSION['pincode'] && $selAttribute != 'Y')
			  	  {
			  	  	 $pincodeElement -> getAttributeNode('sel')-> nodeValue='Y';//if not selected....then put the value as 'Y'
				  }
				  $pincode  = $pincodeElement -> getAttribute('id');/*set pincode property by xml node value */
				  $this -> setElementValue($pincodeElement,$inventory_arr,$pincode,$catid);/*to set xml elements for each pincode */
				  $this -> resetPrevious();/*to reset the details of previous pincode*/
			  }								  
			}
		}
				
	}
	
    function check_avail()
    {/*function to check inventory availability and calculating per day for each pincode*/
    	
    	$checkDaim = 0;/*to check for diam if plat not available*/
    	
    	$sql = "SELECT * FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '".$this ->catid."' and pincode = '".$this -> pincode."'";
    	
    	$res = $this -> areab_Obj -> conn_fnc -> query_sql($sql);
    	
    	if($res && mysql_num_rows($res))
    	{
    		$row = $this -> areab_Obj -> conn_fnc ->fetchData($res);
    		
    		$this -> callcnt  = (round(($row['callcnt'] / 365),8) > 0) ? round(($row['callcnt'] / 365),8) : round(((self :: MIN_CALLCNT/$this -> totPincodes)/365),5);/*getting per day callcnt and setting into data member*/
    		
    		$this -> callcnt_growth_rate = $this -> getCallcnt_Growth_Rate();/*calling function to get respective city growth rate */
    		
    		
    		if($this -> reqPos == self :: POS_D)/*if plat requested*/
    		{
    			
				$this -> bid_val = ($cat_row['b2b']) ? max($row['platinum_value'],self :: D_B2B_MIN_BID_VAL) : max($row['platinum_value'],self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
				
				$tot_free_plat_inv = 1 - trim($row['platinum_inventory']);/*(total free inventory = 1 - total booked inventory )*/
				
			    if($row['platinum_bidder'])/*to check for self booked inventory*/
			    {
					$plat_bid_mem = explode(",",$row['platinum_bidder']);
					
					for($i=0;$i<count($plat_bid_mem);$i++)
					{
						
						$plat_bid_inv = explode("-",$plat_bid_mem[$i]);
						if($plat_bid_inv[0] == $this -> parentid)
						{
						   $tot_free_plat_inv = $tot_free_plat_inv + $plat_bid_inv[2];/*(total free inventory = 1 - total booked inventory + inventory booked by contract)*/
						   break;
						}
						
					}
			    }
			    
			    if(trim($tot_free_plat_inv) >= self :: MIN_INVENTORY)/*if minimum of 5% plat inventory available*/
			    {
			    	if(($this -> reqInv / 100) <= trim($tot_free_plat_inv))
			    	{
			    		
			    		$this -> availPos = self :: POS_D;
			    		$this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
			    		
					}
					else
					{
						
						$this -> availPos = self :: POS_D;
						$this -> availInv = (self :: MULTIPLIER * (int)((trim($tot_free_plat_inv) * 100)/self :: MULTIPLIER))/100;/*to get the avail inventory in the multiple of 5% (to take care of partial inven like 66% or 33%)*/
						
					}
				}
				else
				{
					
					$checkDaim = 1;/*to check for diamond inventory*/
					
				}

			}
			
		if($this -> reqPos == self :: POS_DG || $checkDaim)/*if diam requested or plat not avail*/
    		{
    			
				$this -> bid_val = ($cat_row['b2b']) ? max($row['diamond_value'],self :: DDG_B2B_MIN_BID_VAL) : max($row['diamond_value'],self :: NONB2B_MIN_BID_VAL);/*setting diam bid value into bid_val property*/
				
				$tot_free_diam_inv = 1 - trim($row['diamond_inventory']);/*(total free inventory = 1 - total booked inventory )*/
				
			    if($row['diamond_bidder'])/*to check for self booked inventory*/
			    {
					$diam_bid_mem = explode(",",$row['diamond_bidder']);
					
					for($i=0;$i<count($diam_bid_mem);$i++)
					{
						
						$diam_bid_inv = explode("-",$diam_bid_mem[$i]);
						if($diam_bid_inv[0] == $this -> parentid)
						{
						   $tot_free_diam_inv = $tot_free_diam_inv + $diam_bid_inv[2];/*(total free inventory = 1 - total booked inventory + inventory booked by contract)*/
						   break;
						}
						
					}
			    }
			    
			    if(trim($tot_free_diam_inv) >= self :: MIN_INVENTORY)/*if minimum of 5% diam inventory available*/
			    {
			    	if(($this -> reqInv / 100)<= trim($tot_free_diam_inv))
			    	{
			    		
			    		$this -> availPos = self :: POS_DG;
			    		$this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
			    		
					}
					else
					{
						
						$this -> availPos = self :: POS_DG;
						$this -> availInv = (self :: MULTIPLIER * (int)((trim($tot_free_diam_inv) * 100)/self :: MULTIPLIER))/100;/*to get the avail inventory in the multiple of 5% (to take care of partial inven like 66% or 33%)*/
						
					}
				}
				else
				{				
					$checkGold = 1;
				}
		    }
		    
		    if($this -> reqPos == self :: POS_B || $checkGold)/*if gold requested or plat/ diam not avail*/
    		{    			
				$this -> bid_val = ($cat_row['b2b']) ? max($row['bronze_value'],self :: B_B2B_MIN_BID_VAL) : max($row['bronze_value'],self :: NONB2B_MIN_BID_VAL);/*setting diam bid value into bid_val property*/
				
				$tot_free_gold_inv = 1 - trim($row['bronze_inventory']);/*(total free inventory = 1 - total booked inventory )*/
				
			    if($row['bronze_bidder'])/*to check for self booked inventory*/
			    {
					$gold_bid_mem = explode(",",$row['bronze_bidder']);
					
					for($i=0;$i<count($gold_bid_mem);$i++)
					{						
						$gold_bid_inv = explode("-",$gold_bid_mem[$i]);
						
						if($gold_bid_inv[0] == $this -> parentid)
						{							
						   $tot_free_gold_inv = $tot_free_gold_inv + $gold_bid_inv[2];/*(total free inventory = 1 - total booked inventory + inventory booked by contract)*/
						   break;						   
						}						
					}
			    }
			    
			    if(trim($tot_free_gold_inv) >= self :: MIN_INVENTORY)/*if minimum of 5% diam inventory available*/
			    {
			    	if(($this -> reqInv / 100)<= trim($tot_free_gold_inv))
			    	{			    		
			    		$this -> availPos = self :: POS_B;
			    		$this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
					}
					else
					{						
						$this -> availPos = self :: POS_B;
						$this -> availInv = (self :: MULTIPLIER * (int)((trim($tot_free_diam_inv) * 100)/self :: MULTIPLIER))/100;/*to get the avail inventory in the multiple of 5% (to take care of partial inven like 66% or 33%)*/
					}
				}
				else/*if diamond not available then give package*/
				{				
					$this -> bid_val = ($cat_row['b2b']) ? max($row['diamond_value'],self :: DDG_B2B_MIN_BID_VAL) : max($row['diamond_value'],self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property for package*/
					
					$this -> availPos = self :: POS_AP;					
					$this -> availInv = '0';
				}					
		    }		    			

			if($this -> availPos != self :: POS_AP)
			{				
				$this -> daily_deduct_amt = round(((($this -> bid_val) * ($this -> callcnt) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ($this -> availInv)) / $this -> reqTen),5);/*Calculating per day (daily deduction amount for each pincode)*/
			}
						
			$this -> checkthis +=$this -> daily_deduct_amt;
		        		
		}
		else/*if category-pincode does not have entry in the pincodewise table*/
		{
			
			$this -> callcnt  = round(((self :: MIN_CALLCNT/$this -> totPincodes)/365),5);/*getting per day callcnt and setting into data member*/
    		$this -> callcnt_growth_rate = $this -> getCallcnt_Growth_Rate();/*calling function to get respective city growth rate */
    		
    		$cat_row = $this -> getCategoryinfo($this -> catid);
    		
    		if($this -> reqPos == self :: POS_D)/*if plat requested*/
    		{
    			
    		   $this -> bid_val = ($cat_row['b2b']) ? self :: D_B2B_MIN_BID_VAL: self :: NONB2B_MIN_BID_VAL;/*setting plat bid value into bid_val property*/
    		   
    		   $this -> availPos = self :: POS_D;
			   
			   $this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
    			
			}
			
			if($this -> reqPos == self :: POS_DG)/*if diamond requested*/
			{
				$this -> bid_val = ($cat_row['b2b']) ? self :: DDG_B2B_MIN_BID_VAL : self :: NONB2B_MIN_BID_VAL;/*setting diam bid value into bid_val property*/
				
				$this -> availPos = self :: POS_DG;
			    
			    $this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
			}
			
			if($this -> reqPos == self :: POS_B)/*if gold requested*/
			{
				$this -> bid_val = ($cat_row['b2b']) ? self :: B_B2B_MIN_BID_VAL : self :: NONB2B_MIN_BID_VAL;/*setting diam bid value into bid_val property*/
				
				$this -> availPos = self :: POS_B;
			    
			    $this -> availInv = $this -> reqInv / 100;/*requested inventory will always be in the multiple of 5 and minimum of 10%*/
			}
			
			if($this -> availPos != self :: POS_AP)
			{
				
				$this -> daily_deduct_amt = round(((($this -> bid_val) * ($this -> callcnt) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ($this -> availInv)) / $this -> reqTen),5);/*Calculating per day (daily deduction amount for each pincode)*/
				
			} 
				
				$this -> checkthis +=$this -> daily_deduct_amt;
			
		}
	}
    
    
    function setCatArray()/*setting required values in array to calculate budget*/
    {
    	
        $this -> category_arr[$this -> catid][$this -> zoneid][$this -> pincode]['callcnt']=$this -> callcnt;
	  
	    $this -> category_arr[$this -> catid][$this -> zoneid][$this -> pincode]['bid_value']=$this -> bid_val;
	  
	    $this -> category_arr[$this -> catid][$this -> zoneid][$this -> pincode]['position']=$this -> availPos;
	  
	    $this -> category_arr[$this -> catid][$this -> zoneid][$this -> pincode]['inventory']=$this -> availInv;
	    
	    $this -> category_arr[$this -> catid][$this -> zoneid][$this -> pincode]['daily_deduction_amt']=$this -> daily_deduct_amt;
	  
	}
    
    
    function setElementValue($pincodeElement,$inventory_arr,$pincode,$catid)/*setting value of the elements for a pincode in xml */
    {
    	$num_of_pos = array_keys($inventory_arr[$catid][$pincode]);
    	foreach($inventory_arr[$catid][$pincode] as $position => $pos_value)
    	{
    		$position_to_upt .= $position."~";
    		$bid_val_to_upt	 .= $position."~".$pos_value['bid_value']."|";
    		$inv_to_upt		 .= $position."~".$pos_value['inv']."|";
    		$dda_to_upt		 .= $position."~".$pos_value['bidperday']."|";
    		if(in_array($position,array(self :: POS_D, self :: POS_DG, self :: POS_B)))
    		{
				$callcnt_to_upt	  = $pos_value['callcnt'];
			}
		}
		$position_to_upt = trim($position_to_upt,"~");
		$bid_val_to_upt	 = trim($bid_val_to_upt,"|");
		$inv_to_upt		 = trim($inv_to_upt,"|");
		$dda_to_upt		 = trim($dda_to_upt,"|");
		
        $pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue = $bid_val_to_upt;
	  
	    $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue = $position_to_upt;
	  
	    $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue = $inv_to_upt;
	  
	    $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue = $callcnt_to_upt;
	    
	    $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $dda_to_upt;
	    
	}

    function ChangeCategoriesPostion()
    {
        /*this function would be called when total package value is greater than platinum contract-
    	purpose to change all categories from plat/diam to package*/
    	$catType=array('A','Z','SZ','L');
		$package_perday_value = 0;
		$total_category_contribution = 0;
		//$superzone_pincodes_arr = $this -> getSuperZones();
        $sum_contract_callcount = $this-> GetSumC2scallcnt();
        $call_count_array = $this->GetCatCallcount($this->all_catid_array);
		$get_out_of_city = $this->outOfCity;
		foreach ($catType as $type)
		{
			
			$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$catid = $category_id -> getAttribute('id');
					
					$category_contribution = $this -> inventory_booking -> GetCategoryPackageValue($catid,$ip_status=6,$this -> parentid,$type,$this -> myzone,$this-> data_city,$req_tenure=1,$call_count_array,$sum_contract_callcount,'false');
					/*passing false in orer to ignore matching factor*/
					
					//$this->writeMFfile($catid,$call_count_array,$sum_contract_callcount,$matching_contract_factor=0,$this-> inventory_booking->cat_avgcallcnt,$category_contribution);
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  $pincode = $pincodeElement -> getAttribute('id');
						  if($this->bform_pincode == $pincode)
								$pincodeElement -> getAttributeNode('sel')-> nodeValue='Y';
						  $selected = $pincodeElement -> getAttribute("sel");
						  
						  if($selected == 'Y' || $this -> bform_pincode == $pincode)
						  {
							  $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue = 5;
							  $pincodeElement -> getElementsByTagName( "bid" ) -> item(0) -> nodeValue = '';
							  $pincodeElement -> getElementsByTagName( "inv" ) -> item(0) -> nodeValue = '';
							  $pincodeElement -> getElementsByTagName( "cnt" ) -> item(0) -> nodeValue = '';
							  $pincodeElement -> getElementsByTagName( "dda" ) -> item(0) -> nodeValue = '';
					  	  }
						  
						  
						  if($selected == 'Y' && $this -> bform_pincode == $pincode)
						  {
						  	$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = '5'.'~'.$category_contribution;
							$total_category_contribution += $category_contribution;
						  }
  
						}
					}
					if(!$get_out_of_city)
					{
						$total_category_contribution += $category_contribution;
						$category_id -> setAttribute("ocp",$catid."~".($category_contribution));
					}
				}
				
				$xmlDoc->save($filename);
			
			}
		}
		
		return $total_category_contribution;
		
	}
	
	function resetElement($pincodeElement)
    {/*setting value of the elements to blank in xml */
    	
        $pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue = '0';
	  
	    $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue = '0';
	  
	    $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue = '0';
	  
	    $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue = '0';
	    
	    $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = '0';
	    
	}
    
    function resetPrevious()
    {/*resetting the details of current pincode*/
    	$this -> bid_val = 0;
    	
    	$this -> availPos = '';
    	
    	$this -> availInv = 0;
    	
    	$this -> callcnt = 0;
    	
    	$this -> daily_deduct_amt = 0;
	}
	
    function CalcBudget() 
	{/*calculating actual budget */
		$this -> totPhoneBudget = 0;
		foreach($this -> category_arr as $zonePinArr)
		{					
			foreach($zonePinArr as $PinArr)
			{
				foreach($PinArr as $pin => $value)
				{
					if($PinArr[$pin]['position'] != self :: POS_AP )
					{
					   
					$this -> totPhoneBudget += ( $PinArr[$pin]['bid_value'] * ($PinArr[$pin]['callcnt']) * ($this -> callcnt_growth_rate) * (self :: WEB_FACTOR) * ($this -> increment_factor) * ($this -> reqTen) * ( $PinArr[$pin]['inventory'] ) );/*calculating phone search budget by calculating it for each pincode in loop*/
		
				    }
					
				}
			}
			
		}
		$min_budget = $this -> getMinBudget();/*function to get minimum phone search budget*/				

		if($this -> totPhoneBudget > 0)/*if phone search budget is there*/
		{
			if($this -> reqPos == self :: POS_D)/*if platinum is requested*/
			{
				if(!in_array(strtolower($this -> data_city),$this -> remote_city_arr))
				{
					return $min_budget['min_plat_budget'];/*if the data city is not belongs to all major cities defined in constructor then return minimum budget*/
				}
				else{
					if($this -> totPhoneBudget < $min_budget['min_plat_budget'])
					{	
						
						$newBudget = $this -> nextInventoryPD($min_budget['min_plat_budget'], $this -> totPhoneBudget, $this -> reqInv, $this -> reqTen, $this -> reqPos);
												
						return max(round($newBudget,2),$min_budget['min_plat_budget']);
					}
					else
					{
						return max(round($this -> totPhoneBudget,2),$min_budget['min_plat_budget']);	/*returning platinum budget by checking its minimum budget*/
					}					
					
				}
			}
			elseif($this -> reqPos == self :: POS_DG)/*if diamond requested*/
			{
				if(!in_array(strtolower($this -> data_city),$this -> remote_city_arr))
				{
					return $min_budget['min_diam_budget'];/*if the data city is not belongs to all major cities defined in constructor then return minimum budget*/
				}
				else
				{
					if($this -> totPhoneBudget < $min_budget['min_diam_budget'])
					{
						$newBudget = $this -> nextInventoryPD($min_budget['min_diam_budget'], $this -> totPhoneBudget,$this -> reqInv, $this -> reqTen, $this -> reqPos);
						
						return max(round($newBudget,2),$min_budget['min_diam_budget']);
					}
					else
					{					
						return max(round($this -> totPhoneBudget,2),$min_budget['min_diam_budget']);	/*returning diamond budget by checking its minimum budget*/
					}	
				}			
			}
			elseif($this -> reqPos == self :: POS_B)/*if diamond requested*/
			{
				if(!in_array(strtolower($this -> data_city),$this -> remote_city_arr))
				{
					return $min_budget['min_gold_budget'];/*if the data city is not belongs to all major cities defined in constructor then return minimum budget*/
				}
				else
				{
								
					if($this -> totPhoneBudget < $min_budget['min_gold_budget'])
					{
						$newBudget = $this -> nextInventoryPD($min_budget['min_gold_budget'], $this -> totPhoneBudget,$this -> reqInv, $this -> reqTen, $this -> reqPos);
						return max(round($newBudget,2),$min_budget['min_gold_budget']);
					}
					else
					{					
						return max(round($this -> totPhoneBudget,2),$min_budget['min_gold_budget']);	/*returning gold budget by checking its minimum budget*/
					}
				}				
			}
		}
		 
	}
	
	function getCategoryinfo($category_id)
	{/*method to get category informaton from category_master table*/
		
		$sql = "SELECT business_flag,partial_inventory,if(category_type&128=128,1,0) AS mfrs,category_scope AS distr,callcount,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS searchtype FROM tbl_categorymaster_generalinfo WHERE catid='".$category_id."' and isdeleted=0";
		/* $sql = "SELECT imparent,imp,mfrs,distr,callcnt,searchtype	FROM tbl_category_master WHERE catid ='".$category_id."' and deleted=0";
		 */
		$res = $this -> conn_local -> query_sql($sql);
		
		if($res && mysql_num_rows($res))
		{
			
			$row =  mysql_fetch_assoc($res);
			
			if($row['partial_inventory']){
				$row['top_flag'] = $row['partial_inventory'];
			}else{
				$row['top_flag'] = 0;
			}
			$row['b2b']   = ($row['business_flag'] == '1') ? '1' : '0';
			$row['mfrs']  = $row['mfrs'] ? '1' : '0';
			$row['distr'] = $row['distr'] ? '1' : '0';
			$row['incremented_callcnt'] = ($row['callcount']>0) ? ($row['callcount'] * $this -> callcnt_growth_rate) : (self :: MIN_CALLCNT * $this -> callcnt_growth_rate);
			switch(strtoupper($row['searchtype']))
			{
				case 'NM' :
				case 'VNM':
				case 'A'  :
				$row['cat_type']= 'A';
				break;
				case 'Z':
				$row['cat_type']= 'Z';
				break;
				case 'SZ':
				$row['cat_type']= 'SZ';
				break;
				case 'L':
				$row['cat_type']= 'L';
				break;
				default:
				$row['cat_type']= 'L';
			}
			return $row;
		}
		
	}
	
	function getPackagePincodes($type)
	{/*function to return the package pincodes of one category type */
		
		$filename = $this -> areab_Obj -> getFileName($type);//getting filename
		
		$category_pack_arr = array();
		
		if(file_exists($filename))
		{
			$xmlDoc = new DOMDocument();
			
			$xmlDoc->load($filename);
			
			$category_ids = $xmlDoc->getElementsByTagName( "cti" );
			
			foreach($category_ids as $category_id)
			{
				
				$flag = 1;//flag to check for pure package by checking each pincode of category

				$catid = $category_id -> getAttribute('id');
				
				$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

				$zoneElements = $category_id -> getElementsByTagName( "zid" );
				
				foreach($zoneElements as $zoneElement)
				{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  unset($positions);
						  unset($pos_bid_arr);
						  $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  $selected = $pincodeElement -> getAttribute("sel");
						  $positions  = explode('~',$positions);
						  foreach ($positions as $position)
						  {
							  if($position == self :: POS_AP && $selected == 'Y')
							  {
								
								$pincode = $pincodeElement -> getAttribute('id');
								
								$pos_bid_arr = $this -> getExplodedArr($pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue);
								$pos_inv_arr = $this -> getExplodedArr($pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue);
								
								$callcnt = $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue;
							   
								$category_pack_arr[$catid]['catname'] = $catname;
								
								$category_pack_arr[$catid][$zoneid][$pincode]['callcnt'] = $callcnt;
								
								$category_pack_arr[$catid][$zoneid][$pincode]['bid'] = $pos_bid_arr[$position];
								$category_pack_arr[$catid][$zoneid][$pincode]['inv'] = $pos_inv_arr[$position];
								
							   
							  }
							  
							  if($selected == 'Y' && in_array($position,array(self :: POS_D,self :: POS_DG,self :: POS_B)))
							  {
								
								$flag = 0;
								
							  }	
					  	  }					  
						  	
						}
				}
				
				if($flag)
				$this -> category_to_delete[] = $catid;
			}
			
			return $category_pack_arr;
			
		}
	}
	
	function DelPackagePosition($type)
	{/*function to return the package pincodes of one category type */
		
		$filename = $this -> areab_Obj -> getFileName($type);//getting filename
		
		$category_pack_arr = array();
		
		if(file_exists($filename))
		{
			$xmlDoc = new DOMDocument();
			
			$xmlDoc->load($filename);
			
			$category_ids = $xmlDoc->getElementsByTagName( "cti" );
			
			foreach($category_ids as $category_id)
			{
				
				$flag = 1;//flag to check for pure package by checking each pincode of category

				$catid = $category_id -> getAttribute('id');
				
				$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

				$zoneElements = $category_id -> getElementsByTagName( "zid" );
				
				foreach($zoneElements as $zoneElement)
				{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  unset($positions);
						  unset($pos_bid_arr);
						  $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  $selected = $pincodeElement -> getAttribute("sel");
                          $position_dda = $pincodeElement -> getElementsByTagName( "dda" ) -> item(0) -> nodeValue;
                          $cat_bid  = $pincodeElement -> getElementsByTagName( "bid" ) -> item(0) -> nodeValue;
                          $cat_inv  = $pincodeElement -> getElementsByTagName( "inv" ) -> item(0) -> nodeValue;
						  $positions  = explode('~',$positions);
						  $pincode = $pincodeElement -> getAttribute('id');
						  if(count($positions) == 1 && trim($positions[0]) == self :: POS_AP && $selected == 'Y')
                          {
                                $pincodeElement -> getAttributeNode('sel') -> nodeValue='N';//Mark it as unselected
                                $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue='0'; 
                          }
                          elseif(count($positions) == 2 && $selected == 'Y')
                            {
                                foreach($positions as $key => $position)
                                {
                                    if($position == self::POS_AP)
                                    {
                                        unset($positions[$key]);
                                        $position_dda_arr = explode('|',$position_dda);
                                        foreach($position_dda_arr as $key => $position_key)
                                        {
                                            $position_key_arr = explode('~',$position_key);
                                            if($position_key_arr[0] == self::POS_AP)
                                            {
                                                unset($position_dda_arr[$key]);
                                                //unset($position_key_arr);
                                            }
                                            //$position_dda_arr[$key] = implode('~',$position_key_arr);
                                        }
                                        $position_dda = implode('|',$position_dda_arr);
                                        $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $position_dda;

                                        $cat_bid_arr = explode('|',$cat_bid);
                                        foreach($cat_bid_arr as $key => $bidval)
                                        {
                                            $bid_key_arr = explode('~',$bidval);
                                            if($bid_key_arr[0]== self::POS_AP)
                                            {
                                                unset($cat_bid_arr[$key]);
                                            }
                                            //$cat_bid_arr[$key] = implode('~',$bid_key_arr);
                                        }
                                        $cat_bid = implode('|',$cat_bid_arr);
                                        $pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue = $cat_bid;

                                        $cat_inv_arr = explode('|',$cat_inv);
                                        foreach($cat_inv_arr as $key => $inv_val)
                                        {
                                            $inv_key_arr = explode('~',$inv_val);
                                            if($inv_key_arr[0]== self::POS_AP)
                                            {
                                                unset($cat_inv_arr[$key]);
                                            }
                                        }
                                        $cat_inv = implode('|',$cat_inv_arr);
                                        $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue = $cat_inv;
                                    }
                                    $exml_position = implode('~',$positions);
                                    $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue = $exml_position;
                                }
                            }
					}
				}
			}
			$xmlDoc->save($filename);
		}
	}
	
	function getCategoryTypeCount()
	{
		$catType = array('A','Z','SZ','L');
		$catTypeCount = array("A"=>0,"Z"=>0,"SZ"=>0,"L"=>0);
		$category_arr = array();

		foreach ($catType as $type)
		{  	   
			$filename  = $this -> areab_Obj -> getFileName($type);/*get filename by reference of areabiz obj*/

			if (file_exists($filename))
			{
				$xmlDoc = new DOMDocument();

				$xmlDoc->load($filename);

				$categories = $xmlDoc -> getElementsByTagName( "cti" );
				foreach($categories as $category_id)
				{
					$catid = $category_id -> getAttribute('id');
					$cat_row = $this -> getCategoryinfo($catid);
					switch($cat_row['cat_type'])
					{
						case 'A'  : $catTypeCount[$cat_row['cat_type']] = 1;break;
						case 'Z'  : $catTypeCount[$cat_row['cat_type']] = 1;break;
						case 'SZ' : $catTypeCount[$cat_row['cat_type']] = 1;break;
						case 'L'  : $catTypeCount[$cat_row['cat_type']] = 1;break;
						default :   $catTypeCount['L'] = 1;break;
					}
					
				}
				
			}
		}
	return $catTypeCount;
	}
	
	function getExplodedArr($pos_element_arr)
	{
		$pos_element_arr = explode("|",$pos_element_arr);
		foreach($pos_element_arr as $pos_element)
		{
			$pos_with_val = explode('~',$pos_element);
			$array_to_return[$pos_with_val[0]] = $pos_with_val[1];
		}
		return $array_to_return;
	}
	
	function getCategoryDetails()
	{/*function to return the platinum pincodes of one category type */
		
		$catType=array('A','Z','SZ','L');
		
		$category_arr = array();
		$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B);/*array of plat/diam positions*/
		$this -> totPerDay	=0;/*total per day of selected areas*/
		foreach ($catType as $type)
		{
			
		$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$flag = 1;/*flag to check for pure package by checking each pincode of category*/

					$catid = $category_id -> getAttribute('id');
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							$platinum_dda=0;$diam_dda=0;$gold_dda=0;$total_per_day=0;
							$positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;

							$selected = $pincodeElement -> getAttribute("sel");

							$dda = $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue;
							$pos_dda_arr = explode('|',$dda);

							$positions = explode('~',$positions);

							$pos_bid_arr = $this ->getExplodedArr($pincodeElement->getElementsByTagName("bid")->item(0)->nodeValue);

							$pos_inv_arr = $this ->getExplodedArr($pincodeElement->getElementsByTagName("inv")->item(0)->nodeValue);

							foreach($positions as $position)
							{ 
                                if(trim($selected) == 'Y')
                                {

                                    $position_xml_arr = explode('~',$position);
                                    foreach($pos_dda_arr as $dda)
                                    {
                                        $pos_dda_arr =	explode('~',$dda);
                                        if($pos_dda_arr[0] == '15')
                                        {
                                            $platinum_dda = $pos_dda_arr[1];
                                        }
                                        elseif($pos_dda_arr[0] == '10')
                                        {
                                            $diam_dda = $pos_dda_arr[1];
                                        }
                                        elseif($pos_dda_arr[0] == '8')
                                        {
                                            $gold_dda = $pos_dda_arr[1];
                                        }
                                    }
                                    $total_per_day += $platinum_dda + $diam_dda + $gold_dda;
                                    $pincode = $pincodeElement -> getAttribute('id');

									$bid = $pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue;

									$inventory = $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue;

									$category_arr[$catid][$zoneid][$pincode][$position]['position'] = trim($position);

									$category_arr[$catid][$zoneid][$pincode][$position]['inventory'] = trim($pos_inv_arr[$position]);

                                    $category_arr[$catid][$zoneid][$pincode][$position]['bid'] = trim($pos_bid_arr[$position]);
                                    if($total_per_day>0)
                                    {
                                        $this -> totPerDay += $total_per_day ;
                                    }

                                }
                            }
						}
					}
					
				}
				
				$xmlDoc->save($filename);
			
			}
		}
		
		return $category_arr;
		
    }
    
    function getPlatinumCategoryDetails()
	{/*function to return the platinum pincodes of one category type */
		
		$catType=array('A','Z','SZ','L');
		$category_arr = array();
		$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B);/*array of plat/diam positions*/
		foreach ($catType as $type)
		{
			
		    $filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$flag = 1;/*flag to check for pure package by checking each pincode of category*/

					$catid = $category_id -> getAttribute('id');
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
						
						  unset($positions);
						  unset($selected);
						  unset($pos_bid_arr);
						  unset($pos_inv_arr);
						  $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  
						  $selected = $pincodeElement -> getAttribute("sel");
						  
						  $positions   = explode('~',$positions);
						  
						  $pos_bid_arr = $this ->getExplodedArr($pincodeElement->getElementsByTagName("bid")->item(0)->nodeValue);
									
						  $pos_inv_arr = $this ->getExplodedArr($pincodeElement->getElementsByTagName("inv")->item(0)->nodeValue);
						  
						  foreach($positions as $position)
						  {
							  if(trim($selected) == 'Y')
							  {
								if(in_array($position,$position_array) && $pos_inv_arr[$position]>0)
								{
									
									$pincode = $pincodeElement -> getAttribute('id');
									
							        $category_arr[$catid][$zoneid][$pincode][$position]['position']=trim($position);									
									$category_arr[$catid][$zoneid][$pincode][$position]['inventory']=trim($pos_inv_arr[$position]);
									
									$category_arr[$catid][$zoneid][$pincode][$position]['bid']=trim($pos_bid_arr[$position]);
								
								}
								
							  }
					      }
  
						}
					}
					
				}
				
				$xmlDoc->save($filename);
			
			}
		}
		
		return $category_arr;
		
    }
    
    function RedistributeBidPerDay($perdaydiff)/*function to redistribute bidperday for plat/diam pins on the weightage of (bidvalue*callcnt)*/
	{
		
		$catType=array('A','Z','SZ','L');
		
		$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B);/*array of plat/diam positions*/
		foreach ($catType as $type)
		{
			
			$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$catid = $category_id -> getAttribute('id');
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
							$selected = $pincodeElement -> getAttribute("sel");

							if($selected == 'Y')
							{

								$pincode = $pincodeElement -> getAttribute('id');
								$position = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
								$dda = $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue;
								$pos_dda_main_arr = explode('|',$dda);
								foreach($pos_dda_main_arr as $key => $dda)
								{
									$pos_dda_arr =	explode('~',$dda);
									if($pos_dda_arr[0] == '15')
									{
										$pos_dda_arr[1] = $pos_dda_arr[1]*$perdaydiff;
									}
									elseif($pos_dda_arr[0] == '10')
									{
										$pos_dda_arr[1] = $pos_dda_arr[1]*$perdaydiff;
									}
									elseif($pos_dda_arr[0] == '8')
									{
										$pos_dda_arr[1] = $pos_dda_arr[1]*$perdaydiff;
									}
									$pos_dda_main_arr[$key] = implode('~',$pos_dda_arr);
								}
								$pos_dda_xml_node = implode('|',$pos_dda_main_arr);
								$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $pos_dda_xml_node;

							}
  
						}
					}
					
				}
				
				$xmlDoc->save($filename);
			
			}
		}
    }
    
    function setCategoryContribution($set_pacakage_to_all)
    {
    	$catType=array('A','Z','SZ','L');
		$package_perday_value = 0;
		//$superzone_pincodes_arr = $this -> getSuperZones();
        $sum_contract_callcount = $this-> GetSumC2scallcnt();

        $call_count_array = $this->GetCatCallcount($this->all_catid_array);

		$get_out_of_city = $this->outOfCity;
		
		foreach ($catType as $type)
		{
			
			$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/

			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					$catid = $category_id -> getAttribute('id');
					
					$category_contribution = $this -> inventory_booking -> GetCategoryPackageValue($catid,$ip_status=6,$this -> parentid,$type,$this -> myzone,DATA_CITY,$req_tenure=1,$call_count_array,$sum_contract_callcount,'false');
					/*passing false in orer to ignore matching factor*/
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  $pincode = $pincodeElement -> getAttribute('id');
						  
						  $selected = $pincodeElement -> getAttribute("sel");
						  
						  $position = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  
						  $position_dda = $pincodeElement -> getElementsByTagName( "dda" ) -> item(0) -> nodeValue;
						  
						 /* if($set_pacakage_to_all)
						  {*/
							  if($selected == 'Y' && $this -> bform_pincode == $pincode)
							  {
							  	if(stristr($position,'~') || in_array($position,array(self::POS_D,self::POS_DG,self::POS_B)))
								{
									$position_arr = explode('~',$position);
									foreach($position_arr as $key => $position)
									{
										if($position == self::POS_AP)
										{
											$position_dda_arr = explode('|',$position_dda);
											foreach($position_dda_arr as $key => $position_key)
											{
												$position_key_arr = explode('~',$position_key);
												if($position_key_arr[0] == self::POS_AP)
												{
													$position_key_arr[1] = $category_contribution;
												}
												$position_dda_arr[$key] = implode('~',$position_key_arr);
											}
											$position_dda = implode('|',$position_dda_arr);
											$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $position_dda;
											$total_category_contribution += $category_contribution;
										}
									}
									
					
								 }
								 else
								 {
									$pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue = self::POS_AP;
									$perday_value  = self::POS_AP.'~'.$category_contribution;
									$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $perday_value;
									$total_category_contribution += $category_contribution;
								}
							
							   }
					  	  /* }
					  	   else
					  	   {
					  	   	  if($selected == 'Y' && $this -> bform_pincode == $pincode && $position == self::POS_AP)
							  {
								$perday_value  = self::POS_AP.'~'.$category_contribution;
								$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $perday_value;
								$total_category_contribution += $category_contribution;
							  }
						   }*/
						}
					}
					if(!$get_out_of_city)
                    {
                      $total_category_contribution += $category_contribution;
					  $category_id -> setAttribute("ocp",$catid."~".($category_contribution));
						//$catid."~".($category_contribution)
					}
				}
				
				$xmlDoc->save($filename);
			
			}
		}

		 return $total_category_contribution;
		
	}
	
	function GetSetPackageBidPerDay($perdaydiff=0,$get_set_flag)
	{/*method to get and set per day value of package categories in bform pincode*/
		
		$catType=array('A','Z','SZ','L');
		$outOfCity = $this->outOfCity;
		$package_perday_value = 0;
		foreach ($catType as $type)
		{
			
			$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$catid = $category_id -> getAttribute('id');
					
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  $pincode = $pincodeElement -> getAttribute('id');
						  
						  $position = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  
						  $selected = $pincodeElement -> getAttribute("sel");

                          $position_dda = $pincodeElement -> getElementsByTagName( "dda" ) -> item(0) -> nodeValue;
						  
						  if($selected == 'Y' && $this -> bform_pincode == $pincode)
						  {
						  	$perday_arr = array();
						  	/*$perday_arr = explode('~',$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue);
							if($get_set_flag == 1)
							{
								$perday_arr[1] = (($perday_arr[1]) * $perdaydiff);
								$perday_value  = $perday_arr[0].'~'.$perday_arr[1];
								$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $perday_value;
							}
                            $package_perday_value += $perday_arr[1];*/
                            if(stristr($position,'~') || in_array($position,array(self::POS_D,self::POS_DG,self::POS_B)))
                            {
                                $position_arr = explode('~',$position);
                                foreach($position_arr as $key => $position)
                                {
                                    if($position == self::POS_AP)
                                    {
                                        $position_dda_arr = explode('|',$position_dda);
                                        foreach($position_dda_arr as $key => $position_key)
                                        {
                                            $position_key_arr = explode('~',$position_key);
                                            if($position_key_arr[0] == self::POS_AP)
                                            {
                                                if($get_set_flag == 1)
							                    {
                                                    $perday_arr[1] = ($position_key_arr[1]*$perdaydiff);
                                                    $position_key_arr[1] = $perday_arr[1];
                                                }
                                                else
                                                {
                                                    $perday_arr[1] = $position_key_arr[1];
                                                    $position_key_arr[1] = $perday_arr[1];
                                                }
                                            }
                                            $package_perday_value += $perday_arr[1];
                                            $position_dda_arr[$key] = implode('~',$position_key_arr);
                                        }
                                        $position_dda = implode('|',$position_dda_arr);
                                        if($get_set_flag == 1)
                                        {
										    $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $position_dda;
                                        }
                                    }
                                }
                            }
							else
                            {
                                $perday_arr = explode('~',$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue);
                                if($get_set_flag == 1)
                                {
                                    $perday_arr[1] = (($perday_arr[1]) * $perdaydiff);
                                    $perday_value  = $perday_arr[0].'~'.$perday_arr[1];
                                    $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue = $perday_value;
                                }
                                $package_perday_value += $perday_arr[1];
                            }
														
						  }
  
						}
					}
					
					if(!$outOfCity){
					
						$ocpValue = $category_id->getAttribute('ocp');
						$ocpArr	  = explode('~',$ocpValue);
						if($get_set_flag == 1){
							$category_id->setAttribute('ocp',$ocpArr['0']."~".($ocpArr['1']*$perdaydiff));
						}
						$package_perday_value+= $ocpArr['1'];
					}
				}
				
				$xmlDoc->save($filename);
			
			}
		}
		
		return $package_perday_value;
	}

	function getCatcnt($catid,$xmlDoc)/*Function to get category callcount*/
  	{
		
			$category_ids = $xmlDoc->getElementsByTagName( "cti" );
			
			$totcallcnt = 0;
			
			$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B,self :: POS_LD);
			
			foreach($category_ids as $category_id)
			{
				$id=$category_id -> getAttribute('id');
				
				if($id == $catid)
				{
					
					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements=$zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
										  
						  $sel = $pincodeElement -> getAttribute('sel');/*get attribute nodeName*/
						
						  unset($pos_inv_arr);
						  unset($platinum_inv);
						  unset($diam_inv);
						  unset($gold_inv);
						  unset($pack_inv);
						  $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  $inv = $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue;
						  $pos_inv_arr = explode('|',$inv);
						  
							 foreach($pos_inv_arr as $invpos)
							 {
								$pos_inv_arr =	explode('~',$invpos);
								if($pos_inv_arr[0] == '15')
								{
									$platinum_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '10')
								{
									$diam_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '8')
								{
									$gold_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '5')
								{
									$pack_inv = $pos_inv_arr[1];
								}
									
							 }
							  if($sel == 'Y' && ($platinum_inv+$diam_inv+$gold_inv)>0)
							  {
								$callcnt = $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue;
								//$totcallcnt += round(($callcnt * ($platinum_inv+$diam_inv+$gold_inv)),5);
								$totcallcnt += (($callcnt * ($platinum_inv+$diam_inv+$gold_inv)));
							  }
						}
						
					}
					
				}
				
			 }
			 
			 return $totcallcnt;
		
  	}
  	
  	function getTotCatcnt($xmlDoc,$tenure,$growthrate)/*Function to get category callcount*/
  	{
		
			$category_ids = $xmlDoc->getElementsByTagName( "cti" );
			
			$totcallcnt = 0;
			
			$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B,self :: POS_LD);
			
			foreach($category_ids as $category_id)
			{
				$catcallcnt=0;
				
				$id=$category_id -> getAttribute('id');
									
				$zoneElements = $category_id -> getElementsByTagName( "zid" );
				
				foreach($zoneElements as $zoneElement)
				{
					
					$zoneid = $zoneElement -> getAttribute('id');
					
					$pincodeElements=$zoneElement -> getElementsByTagName( "pin" );
					
					foreach($pincodeElements as $pincodeElement)
					{
										  
					    $sel = $pincodeElement -> getAttribute('sel');/*get attribute nodeName*/
					    unset($pos_inv_arr);
						unset($platinum_inv);
						unset($diam_inv);
						unset($gold_inv);
						unset($pack_inv);
					    $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
					    
						$inv = $pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue;
						
					    $pos_inv_arr = explode('|',$inv);
						  
							 foreach($pos_inv_arr as $invpos)
							 {
								$pos_inv_arr =	explode('~',$invpos);
								if($pos_inv_arr[0] == '15')
								{
									$platinum_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '10')
								{
									$diam_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '8')
								{
									$gold_inv = $pos_inv_arr[1];
								}
								elseif($pos_inv_arr[0] == '5')
								{
									$pack_inv = $pos_inv_arr[1];
								}
									
							 }
						
						if($sel == 'Y' && ($platinum_inv+$diam_inv+$gold_inv)>0)
						{
							$callcnt = $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue;
							//$catcallcnt += round(($callcnt * ($platinum_inv+$diam_inv+$gold_inv)),5);
							$catcallcnt += (($callcnt * ($platinum_inv+$diam_inv+$gold_inv)));
						}						
					  
					}
				}
				$totcallcnt += ($catcallcnt>0)?((($catcallcnt*$tenure*$growthrate) >= 1)?($catcallcnt*$tenure*$growthrate):1):$catcallcnt;
			 }
			 
			 return $totcallcnt;
		
  	}
  	
	function getCityMinBudget($type=0)
	{
		$budgetMin 	= 0;
		if($type == '1'){
			$field = "platinum_search_rate as minBudget";
		}else if($type == '1'){
			$field = "diamond_search_rate as minBudget";
		}else{
			$field = "minBudget";
		}
		$sql = "SELECT ".$field." FROM tbl_business_uploadrates WHERE city='".$this-> data_city."'";
		$res =$this -> areab_Obj-> query_sql($sql);
		if($res){
			$rowMin 	= mysql_fetch_assoc($res);
			$budgetMin 	= ($rowMin['minbudget']>0)?$rowMin['minbudget']:'';
		}
		return $budgetMin;
	}
	
  	function GetAdvanceAmount($advance_duration)
  	{/*This function would return advance amount factor calculated on the basis of categories call count*/
  		
  		$catType=array('A','Z','SZ','L');
		$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B);/*array of plat/diam positions*/
		$cat_platdiam_arr = array();
		$cat_pack_arr = array();
		foreach ($catType as $type)
		{
			
			$filename = $this -> areab_Obj -> getFileName($type);/*getting filename*/
		
			if(file_exists($filename))
			{
				$xmlDoc = new DOMDocument();
				
				$xmlDoc->load($filename);
				
				$category_ids = $xmlDoc->getElementsByTagName( "cti" );
				
				foreach($category_ids as $category_id)
				{
					
					$catid = $category_id -> getAttribute('id');
					$ddg_category_bidperday = 0;
					$pack_category_bidperday = 0;
					$catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

					$zoneElements = $category_id -> getElementsByTagName( "zid" );
					
					foreach($zoneElements as $zoneElement)
					{
						
						$zoneid = $zoneElement -> getAttribute('id');
						
						$pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
						
						foreach($pincodeElements as $pincodeElement)
						{
							
						  $position = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
						  
						  $selected = $pincodeElement -> getAttribute("sel");
						  
						  $pincode = $pincodeElement -> getAttribute('id');
						  
						  unset($dda);
						  unset($dda_pos_arr);
						  unset($pack_arr);
						  if($selected == 'Y' && $position == self::POS_AP && $this -> bform_pincode == $pincode)
						  {
						  	 $pack_arr     = explode('~',$pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue);
						  	 $pack_category_bidperday += $pack_arr[1];
						  }
						  elseif($selected == 'Y')
						  {
							$dda     = $pincodeElement -> getElementsByTagName("dda") -> item(0) -> nodeValue;
							$dda_pos_arr = explode('|',$dda);
							foreach($dda_pos_arr as $dda_pos)
							{
								$dda_arr = explode('~',$dda_pos);
								if(count($dda_arr)>0 && $dda_arr[1]>0)
								{
									$ddg_category_bidperday += $dda_arr[1];
									$total_bid_per_day +=$dda_arr[1];
								}						
							}
						  }
						  
						}
					}
					if($ddg_category_bidperday>0)
					{
						$cat_platdiam_arr[$catid]['bidperday'] = $ddg_category_bidperday;
						$tot_plat_pack_cat_arr[$catid]['bidperday'] = $ddg_category_bidperday;
					}
					if($pack_category_bidperday>0)
					{
						$cat_pack_arr[$catid]['bidperday'] = $pack_category_bidperday;
						$tot_plat_pack_cat_arr[$catid]['bidperday']=$pack_category_bidperday;
					}
				}
				
				$xmlDoc->save($filename);
			
			}
		}
		/*contract's category with its bid per day*/
		if(count($cat_platdiam_arr)>0 || count($cat_pack_arr)>0)
		{
			$ddg_catids = array();
			$pack_catids = array();
			if(count($cat_platdiam_arr)>0)
			{
				$ddg_catids = array_keys($cat_platdiam_arr);
			}
			if(count($cat_pack_arr)>0)
			{
				$pack_catids= array_keys($cat_pack_arr);
			}
			$tot_catids_arr = array_merge($ddg_catids,$pack_catids);
			$tot_catids_arr = array_unique($tot_catids_arr);
			
			$cat_adv_factor_arr = $this->inventory_booking->GetAdvanceAmountFactor($tot_catids_arr,$_SESSION['s_deptCity'],$advance_duration);
			
			if(count($cat_adv_factor_arr)>0)
			{
				foreach($tot_plat_pack_cat_arr as $catid => $catid_value)
				{
					$callcnt_factor  = ($cat_adv_factor_arr[$catid]['adv_factor']>0)?$cat_adv_factor_arr[$catid]['adv_factor']:self::MIN_ADVANCE_FACTOR;
					
					$advance_factor +=((($catid_value['bidperday']*365)/365) * $callcnt_factor);					
				}
			}
			return ceil($advance_factor*30);
			
		}
		
		
	}
	
	function showBidders($catid,$pincode)/*function which would return bidder array for show bidders*/
	{
		
		$sql = "select platinum_bidder, diamond_bidder, bronze_bidder, toplead_bidder FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '".$catid."' and pincode = '".$pincode."'";
		
		$res = $this -> areab_Obj -> conn_fnc -> query_sql($sql);
		
		$array_bidder = array();/*array to store platinum and diamond bidders */
		
		if($res && mysql_num_rows($res))
		{
			
			$row = mysql_fetch_assoc($res);
			
			if($row['platinum_bidder'])
			{
				$bidders = explode(',',$row['platinum_bidder']);
				
				foreach($bidders as $values)
				{
					if($values)
					$array_bidder ['platinum'][]=explode('-',$values); 
				}
				
			}
			
			if($row['diamond_bidder'])
			{
				$bidders = explode(',',$row['diamond_bidder']);
				
				foreach($bidders as $values)
				{
					if($values)
					$array_bidder ['diamond'][]=explode('-',$values); 
				}
				
			}
			
			if($row['bronze_bidder'])
			{
				$bidders = explode(',',$row['bronze_bidder']);
				
				foreach($bidders as $values)
				{
					if($values)
					$array_bidder ['Gold'][]=explode('-',$values); 
				}
				
			}
			
			if($row['toplead_bidder'])
			{
				$bidders = explode(',',$row['toplead_bidder']);
				
				foreach($bidders as $values)
				{
					if($values)
					$array_bidder ['lead'][]=explode('-',$values); 
				}
				
			}
			
			
		}
		
		
		return $array_bidder;
		
	}
	
	function updateTempBudgetLog($budget_log_arr)
	{
		
		$sql="INSERT INTO tbl_contract_budget_log_temp(parentid,companyName,campaignid,budget,tenure,position,callcnt,updatedate,updatedby)VALUES('".$budget_log_arr[0]."','".addslashes($budget_log_arr[1])."','".$budget_log_arr[2]."','".$budget_log_arr[3]."','".$budget_log_arr[4]."','".$budget_log_arr[5]."','".$budget_log_arr[6]."',now(),'".$budget_log_arr[7]."')";

		if($_SESSION['tme'])
		{
			$res = $this -> dbconn_tme -> query_sql($sql);
		}
		else
		{
			$res = $this -> dbconn_decs -> query_sql($sql);
		}
	}
	
	function updateBudget()
	{/*function to update phone search budget */
        $platdaigold_array = array();
        //$platdaigold_array['budget'] = $_POST['budget'];
		$tempArr 	= $this ->financeObj->getFinanceTempData('2');
		if($_POST['plat_budget']>0){
			$platdaigold_array['budget'] = $_POST['plat_budget'];
			$platdaigold_array['duration'] = $_POST['tenure'];
			$platdaigold_array['searchcriteria'] = $_POST['pincodeplus'];
			if($_POST['calculate_flag']==1)
			{
				$platdaigold_array['recalculate_flag']=1;
			}
			$sql_excl = "SELECT exclusive FROM tbl_temp_intermediate WHERE parentid = '".$this -> parentid."'";
			$qry_excl = $this->dbconn_decs->query_sql($sql_excl);
			if($qry_excl)
			{
				$row_excl = mysql_fetch_assoc($qry_excl);
			}
			

			if($row_excl['exclusive'] == 1) $platdaigold_array['exclusivelisting_tag']=1;

			$insertsql= $this->financeObj->financeInsertUpdateTemp('2',$platdaigold_array);
			if($_POST['calculate_flag']==1){
				$regArr['budget'] = 0;
				$regArr['duration'] = 0;
				$regArr['recalculate_flag'] = 0;
				$this->financeObj->financeInsertUpdateTemp('7',$regArr);
			}
		}
		else if(($_POST['plat_budget']==0||$_POST['plat_budget']=='') && $tempArr['2']['budget'] >0){
			$platdaigold_array['budget'] 	= 0;
            $platdaigold_array['duration'] 	= 0;
			$platdaigold_array['recalculate_flag'] 	= 0;
			$insertsql= $this->financeObj->financeInsertUpdateTemp('2',$platdaigold_array);
			/* Update Position 
			$upPosition = "UPDATE tbl_temp_intermediate SET pdg_position = '".$_POST['fixed_position']."' WHERE parentid='".$this->parentid."'";
			$qryupPosition = $this->dbconn_decs->query_sql($upPosition);
			/* Ends */
		}
        if($_POST['pack_budget']>0 && $_POST['calculate_flag']==1)
        {
            $packBudget['budget'] = $_POST['pack_budget'];
            $packBudget['duration'] = $_POST['tenure'];
            $packBudget['recalculate_flag'] = 1;
            $packBudget['smartlisting_flag'] = 0;
            $insertsql= $this->financeObj->financeInsertUpdateTemp('1',$packBudget);
			
			if($_POST['calculate_flag']==1){
				$regArr['budget'] =  0;
				$regArr['duration'] =  0;
				$regArr['recalculate_flag'] =  0;
				$insertsql= $this->financeObj->financeInsertUpdateTemp('7',$regArr);
			}
       }
	   /* Insert into table for best budget sign up details  --  STARTS*/
	   $insertArr = array();
	   if($_POST['only_ddg'] == 1){
			$insertArr['dealType'] 	= 'PD';
			$insertArr_mix['dealType'] 	= 'PD';
	   }else{
			$insertArr['dealType'] 	= 'Best Budget';
			$insertArr_mix['dealType'] 	= 'Best Budget';
	   }
	   if($_POST['pack_budget']>0 && ($_POST['plat_budget']==0 || $_POST['plat_budget']=='')){
			$insertArr['camp']		= '1';
			$insertArr['budget']	= $_POST['pack_budget'];
			$insertArr['duration'] 	= $_POST['tenure'];
			$this->bestbudget_signup_entry($insertArr);
	   }else if($_POST['plat_budget']>0 && ($_POST['pack_budget']==0||$_POST['pack_budget']=='')){
			$insertArr['camp']		= '2';
			$insertArr['budget']	= $_POST['plat_budget'];
			$insertArr['duration'] 	= $_POST['tenure'];
			$this->bestbudget_signup_entry($insertArr);
	   }else if($_POST['pack_budget']>0 && $_POST['plat_budget']>0){
			
			$insertArr_mix['camp']	  	= '1';
			$insertArr_mix['budget']  	= $_POST['pack_budget'];
			$insertArr_mix['duration'] 		= $_POST['tenure'];
			
			$this->bestbudget_signup_entry($insertArr_mix);
			
			$insertArr_mix['camp']	  	= '2';
			$insertArr_mix['budget']  	= $_POST['plat_budget'];
			$insertArr_mix['duration'] 	= $_POST['tenure'];
			$this->bestbudget_signup_entry($insertArr_mix);
	   }
	   /* Insert into table for best budget sign up details  --  ENDS*/
	   
		/*$packBudget['budget'] = 0;
		$packBudget['duration'] = 0;
		$packBudget['recalculate_flag'] = 0;
		$packBudget['smartlisting_flag'] = 0;
        $insertsql= $this->financeObj->financeInsertUpdateTemp('1',$packBudget);
        $insertsql= $this->financeObj->financeInsertUpdateTemp('16',$packBudget);*/
	
        $budget_log_arr = array($this -> parentid,$_SESSION['compname'],'2',$_POST['plat_budget'],$_POST['tenure'],$_POST['fixed_position'],'100',$_SESSION['ucode']);
		
		$this -> updateTempBudgetLog($budget_log_arr);                                   
		
	}
	
	function getUpdatedBudget()
	{/*function to get budget details */
		
		$financeRow = $this->financeObj -> getFinanceTempData();
			
		return $financeRow;
		
	}
	
	function nextInventoryPD($minBudget, $actBudget, $actInventory, $totTenure, $reqPosition,$ip_status)
	{		
		
		if(trim($actInventory) < trim(self::MAX_INVENTORY))
		{
			
			$newInventory 		= min(($actInventory +self::NEXT_INVENTORY), self::MAX_INVENTORY);
			$this -> InitReqParam($reqPosition,$newInventory,$totTenure);
			$budget_arr=$this -> getSelPin($flag = '0',$ip_status);
			$actnewbudget = $this -> getAskedBudget($reqPosition,$newInventory,$totTenure,$budget_arr,$ip_status);	
			
		}		
		return $actnewbudget;		
	}
	
	function getBusinessCategories()
	{
		
		$sql_cat="select trim(',' FROM replace(catids,'|P|',',')) as catids from tbl_business_temp_data where contractid ='".$this -> parentid."'";
		if(strtolower(APP_MODULE)=='tme')
		{
			$res_cat = $this -> dbconn_tme -> query_sql($sql_cat);
		}
		else
		{
			$res_cat = $this -> dbconn_decs -> query_sql($sql_cat);
		}
			
		if($res_cat && mysql_num_rows($res_cat))
		{
			$row_cat = mysql_fetch_assoc($res_cat);
			$catLst  = $row_cat['catids'];
			return $catLst;
		}
	}
	
	function CheckExclusive()
	{
		$row_inter  = $this -> areab_Obj -> getIntermediateFlag($this);
		$catlist    = $this -> getBusinessCategories();
		if($row_inter['exclusive'] == 1)
		{
			$sql_cat = "SELECT category_name AS catname FROM tbl_categorymaster_generalinfo WHERE catid IN (" . $catLst . ") AND isdeleted =0 AND category_type&8!=8  AND  total_results!=1 GROUP BY category_name";
			// $sql_cat = "select catname from tbl_category_master where catid in (" . $catLst . ") and deleted =0 and exclusive!=1  and  totcompdisplay!=1 group by catname";
			$res_cat = $this -> dbconn_decs -> query_sql($sql_cat);
			if($res_cat && mysql_num_rows($res_cat))
			{
				$contract_type = "Exclusive";
				$category_type = "Non Exclusive";
				$categories ="";
				$i=1;				
				while($row_cat = mysql_fetch_assoc($res_cat))
				{
					$categories .="<tr><td><font color='Blue' size='4'>&nbsp;&nbsp;". $i . ".&nbsp;". $row_excl['catname'];"</font></td></tr>";
		            $i++;
				}
				
			}
			
			
		}
		else
		{
			$sql_cat	= "SELECT category_name AS catname FROM tbl_categorymaster_generalinfo WHERE catid in (" . $catLst . ") AND isdeleted =0 and (category_type&8=8  or total_results=1) group by category_name";
			// $sql_cat = "select catname from tbl_category_master where catid in (" . $catLst . ") and deleted =0 and (exclusive=1  or totcompdisplay=1) group by catname";
			$res_cat = $this -> dbconn_decs -> query_sql($sql_cat);
			if($res_cat && mysql_num_rows($res_cat))
			{
				$contract_type = "Non exclusive";
				$category_type = "Exclusive";
				$categories ="";
				$i=1;				
				while($row_cat = mysql_fetch_assoc($res_cat))
				{
					$categories .="<tr><td><font color='Blue' size='4'>&nbsp;&nbsp;". $i . ".&nbsp;". $row_excl['catname'];"</font></td></tr>";
		            $i++;
				}
				
			}
		}
		
		if($categories)
		{
			
			echo "<table align='center'><tr><td><br><font color='red' size='6'><blink>Type of Contract : ".$contract_type."</blink></font><br></td></tr>";
			
			echo "<tr><td><font color='Blue' size='4'><br><b>".$category_type." categories are:</b></font></td></tr><br>";
			
			echo $categories;
			
			echo "<tr><td align='center'>Click here to go <br><a href='catpreview.php?Pageno=2'>BACK</a>to delete above ".$category_type."</td></tr>";
			
			die;
			
		}
	}
	
	function paid_clients_count($module)
	{
		$cat_ids_arr = array();
		$paid_cnt = array();
		$sel_temp_catids = "SELECT trim(',' FROM replace(catids,'|P|',',')) as catids from tbl_business_temp_data WHERE contractid ='".$this -> parentid."'";
		switch(strtolower($module))
		{
			case 'cs'  : $res_temp_catids = $this -> dbconn_decs ->query_sql($sel_temp_catids); break ;
			case 'tme' : $res_temp_catids = $this -> dbconn_tme ->query_sql($sel_temp_catids); break ;
			case 'me'  : $res_temp_catids = $this -> dbconn_idc ->query_sql($sel_temp_catids); break ;
		}
		if($res_temp_catids && mysql_num_rows($res_temp_catids)>0)
		{
			$row_temp_catids =  mysql_fetch_assoc($res_temp_catids);
			$catLst  = $row_temp_catids['catids'];
		}
		$cat_ids_arr = explode(',',$catLst);

		foreach($cat_ids_arr as $key=>$value)
		{
			$sql_cnt = "SELECT cnt FROM tbl_related_categories WHERE for_bid_catid ='".$value."' AND bid_catid='".$value."'";
			$res_cnt = $this -> dbconn_decs-> query_sql($sql_cnt);
			if($res_cnt && mysql_num_rows($res_cnt)>0)
			{
				$row_cnt = mysql_fetch_assoc($res_cnt);
				$paid_cnt[] = $row_cnt['cnt'];
			}
		}
		if(count($paid_cnt)>0)
			return max($paid_cnt);
		else
			return 0;
		
	}
    
    function GetMinPackVal()
    {
        /*if(defined('REMOTE_CITY_MODULE'))
        {
            return $package_value=8000;
        }
        else
        {
            return $package_value=10429;
        }*/
    	
        if(defined('REMOTE_CITY_MODULE') && !in_array(strtolower($this-> data_city),array('jaipur','coimbatore','chandigarh')))
        {
            $qry_city = 'other_cities';
        }
        else
        {
            $qry_city = trim($this-> data_city);
        }
		 $factor= $this->getCallcnt_Growth_Rate();
		 $annualcustcost =$this -> city_minimum_budget;
        /*$sqlqry6="select annualcost from  tbl_premium_listing_Justdialg where city = '".strtolower($qry_city)."' and jd_flag = 1  order by annualcost asc limit 1";
        if(strtolower(APP_MODULE) == 'me')
            $resutlqry6 = $this -> dbconn_idc ->query_sql($sqlqry6);
        else
            $resutlqry6 = $this -> dbconn_decs->query_sql($sqlqry6);
        
        $rowqry6=mysql_fetch_assoc($resutlqry6);
        $annualcustcost=$rowqry6['annualcost'];
        $minCostCityArr = array('delhi','mumbai','bangalore','chennai');
        $cityVar = strtolower(trim($this-> data_city));

        if(in_array($cityVar,$minCostCityArr)){
            $annualcustcost = '12000';
        }*/
        return $annualcustcost;
    }

    function GetCatCallcount($catid_array)
    {
        $callcount_array = array();
        if(is_array($catid_array) && count($catid_array)>0)
        {
			$catid_array = array_merge(array_filter($catid_array));
            $catids = implode(",",$catid_array);

			
			
            $get_callcount_query = "SELECT a.catid, a.callcount from tbl_categorymaster_generalinfo a join tbl_categorymaster_parentinfo b USING (catid) WHERE a.catid IN (".$catids.") AND a.active_flag=1 AND a.mask_status=0 AND (a.biddable_type='1' OR a.biddable_type='0' OR (a.biddable_type='0' AND b.parent_flag=1)) AND a.isdeleted = 0 GROUP BY a.catid";
           // print $get_callcount_query = "SELECT catid,callcnt  FROM tbl_category_master WHERE catid IN (".$catids.") AND display_flag=1 AND mask=0 AND  (cat_type='B' OR cat_type='BT' OR (cat_type='T' AND parent_flag=1)) AND deleted = 0 GROUP BY catid";
            $res_callcount_query = $this -> dbconn_decs-> query_sql($get_callcount_query);
            if($res_callcount_query && mysql_num_rows($res_callcount_query)>0)
            {
                while($row_callcount_query = mysql_fetch_assoc($res_callcount_query))
                {
                    $callcount_array[$row_callcount_query['catid']]['callcount'] = $row_callcount_query['callcount'];
                    $sum_cat_callout += $row_callcount_query['callcount'];
                }
                $callcount_array['sum_callcount'] = ($sum_cat_callout>0)?$sum_cat_callout:'1';
            }
        }
        return $callcount_array;
    }

    function GetSumC2scallcnt()
    {
        $sum_contract_callcount = 1;
        $qry_c2snonpaid_contracts = "select contracts from tbl_temp_intermediate where parentid = '".$this->parentid."'";
        if(strtolower(APP_MODULE)=='me')
		{
            $res_c2snonpaid_contracts = $this -> dbconn_idc ->query_sql($qry_c2snonpaid_contracts);
        }
        elseif(strtolower(APP_MODULE)=='tme')
        {
            $res_c2snonpaid_contracts = $this -> dbconn_tme ->query_sql($qry_c2snonpaid_contracts);
        }
        else
        {
            $res_c2snonpaid_contracts = $this -> dbconn_decs ->query_sql($qry_c2snonpaid_contracts);
        }
        if($res_c2snonpaid_contracts && mysql_num_rows($res_c2snonpaid_contracts)>0)
        {
            $row_c2snonpaid_contracts = mysql_fetch_assoc($res_c2snonpaid_contracts);
            $all_contracts = $row_c2snonpaid_contracts['contracts'];
            if($all_contracts!='')
            {
                $contracts_array = explode(",",$all_contracts);
                $contracts_array = array_merge(array_filter($contracts_array));
                if(count($contracts_array)>0)
                {
                    $contracts_str = implode("','",$contracts_array);
                    $qry_c2s_callconnt = "SELECT parentid,company_callcnt FROM c2s_nonpaid WHERE parentid IN ('".$contracts_str."')";
                    $res_c2s_callconnt = $this -> dbconn_decs ->query_sql($qry_c2s_callconnt);
                    if($res_c2s_callconnt && mysql_num_rows($res_c2s_callconnt)>0)
                    {
                        while($row_c2s_callconnt = mysql_fetch_assoc($res_c2s_callconnt))
                        $sum_contract_callcount += $row_c2s_callconnt['company_callcnt'];
                    }
                }
            }
        }
        return $sum_contract_callcount;
    }
    
	function getParialInvMsg($ask_ivn)
    {
        if($ask_ivn!='')
        {
            $ask_ivn = ($ask_ivn/100);
        }
        $msg_true = 0;
        $msg_arr = array();
        $category_array = array();
        $category_array = $this->getCategoryDetails();//echo "<pre>";print_r($category_array[102738]);echo "</pre>";
        foreach($category_array as $cat_id => $zone_arr)
        {
            $cat_row = $this -> getCategoryinfo($cat_id);
            foreach($zone_arr as $zone_id => $pincode_arr)
            {
                foreach($pincode_arr as $pincode =>$pos_arr)
                {//print_r($pos_arr);
                    if (array_key_exists(15, $pos_arr) || array_key_exists(10, $pos_arr) )
                    {
                        $pos_key=array_keys($pos_arr);
                        
                        if(($pos_arr[$pos_key[0]]['inventory']>$ask_ivn) && (intval($cat_row['top_flag'])==0))
                        {
                            //echo "<br>catid-->".$cat_id."flag-->".$cat_row['top_flag']."-->pincode->".$pincode."Pos->".$pos_arr[$pos_key[0]]['inventory'];
                            $msg_true = 1;
                            $msg_arr[$cat_id]=$msg_true;
                        }
                    }
                }
            }
        }//echo "<br>line 2808 catid ->".$cat_id."==".$msg_true;
        /*if(!in_array($cat_id,$msg_arr))
        {
            $msg_arr[$cat_id]=$msg_true;
        }*/
        return $msg_arr;
    }

    function getIvndropdown()
    {
        $dropdwn_flag = 1;
        foreach($this->all_catid_array as $catid)
        {
            $cat_row = $this -> getCategoryinfo($catid);
            if(intval($cat_row['top_flag'])==1)
            {
                return $dropdwn_flag=0;
            }
        }
        return $dropdwn_flag;
    }

	function checkCityPincode()
    {
        //$this -> bform_pincode
        $same_city_flag=false;
        $qry = "select pincode from tbl_area_master where pincode='".$this -> bform_pincode."' and display_flag=1 and data_city='".$this-> data_city."'";
        $res = $this -> conn_local ->query_sql($qry);
        if($res && mysql_num_rows($res)>0)
        {
            $same_city_flag=true;
        }
        return $same_city_flag;
    }

    function getPackageDetails()
    {
        $catType=array('A','Z','SZ','L');
		$category_pack_arr = array();
		$position_array = array(self :: POS_D,self :: POS_DG,self :: POS_B);/*array of plat/diam positions*/
		foreach ($catType as $type)
		{
            $filename = $this -> areab_Obj -> getFileName($type);//getting filename

            if(file_exists($filename))
            {
                $xmlDoc = new DOMDocument();
                
                $xmlDoc->load($filename);
                
                $category_ids = $xmlDoc->getElementsByTagName( "cti" );
                
                foreach($category_ids as $category_id)
                {
                    $flag = 1;//flag to check for pure package by checking each pincode of category

                    $catid = $category_id -> getAttribute('id');
                    
                    $catname = $category_id -> getElementsByTagName("ctn") ->item(0) -> nodeValue;

                    $zoneElements = $category_id -> getElementsByTagName( "zid" );
                    
                    foreach($zoneElements as $zoneElement)
                    {
                        $zoneid = $zoneElement -> getAttribute('id');
                            
                        $pincodeElements = $zoneElement -> getElementsByTagName( "pin" );
                            
                        foreach($pincodeElements as $pincodeElement)
                        {
                            unset($positions);
                            unset($pos_bid_arr);
                            $positions = $pincodeElement -> getElementsByTagName( "pos" ) -> item(0) -> nodeValue;
                            $selected = $pincodeElement -> getAttribute("sel");
                            $positions  = explode('~',$positions);
                            foreach ($positions as $position)
                            {
                                  if($selected == 'Y')
                                  {
									
                                    $pincode = $pincodeElement -> getAttribute('id');
                                    									
                                    $pos_bid_arr = $this -> getExplodedArr($pincodeElement -> getElementsByTagName("bid") -> item(0) -> nodeValue);
                                    $pos_inv_arr = $this -> getExplodedArr($pincodeElement -> getElementsByTagName("inv") -> item(0) -> nodeValue);
                                    
                                    $callcnt = $pincodeElement -> getElementsByTagName("cnt") -> item(0) -> nodeValue;
                                   
                                    $category_pack_arr[$catid]['catname'] = $catname;
                                    
                                    $category_pack_arr[$catid][$zoneid][$pincode]['callcnt'] = $callcnt;
                                    
                                    $category_pack_arr[$catid][$zoneid][$pincode]['bid'] = $pos_bid_arr[$position];
                                    $category_pack_arr[$catid][$zoneid][$pincode]['inv'] = $pos_inv_arr[$position];
                                    
                                   
                                  }
                                  
                                  /*if($selected == 'Y' && in_array($position,array(self :: POS_D,self :: POS_DG,self :: POS_B)))
                                  {
                                    
                                    $flag = 0;
                                    
                                  }	*/
                              }					  
                                
                            }
                    }
                    
                    /*if($flag)
                    $this -> category_to_delete[] = $catid;*/
                }
            }
        }
		
        return $category_pack_arr;
    }

	function getLowestLimit($campaign,$positionArr=array()){
		
		$limit	  =  array();
		if($campaign == 1){
			$field = 'packlimit';
		}elseif($campaign == 2){
			if(count($positionArr)>0){
				if(in_array('15',$positionArr)){
					$field = 'platinum_search_rate AS pdlimit';
				}else if(in_array('10',$positionArr)){
					$field = 'diamond_search_rate AS pdlimit';
				}else if(in_array('8',$positionArr)){
					$field = 'bronze_search_rate AS pdlimit';
				}else{
					$field = 'platinum_search_rate AS pdlimit';
				}
			}
		}else{
			$field = 'packlimit,';
			if(count($positionArr)>0){
				if(in_array('15',$positionArr)){
					$field.= 'platinum_search_rate AS pdlimit';
				}else if(in_array('10',$positionArr)){
					$field.= 'diamond_search_rate AS pdlimit';
				}else if(in_array('8',$positionArr)){
					$field = 'bronze_search_rate AS pdlimit';
				}
			}
		}
		$field =trim($field,',');
		
		$sqlLimit = "SELECT ".$field." FROM tbl_business_uploadrates WHERE city='".$this-> data_city."'";
		$qryLimit = $this->conn_local->query_sql($sqlLimit);
		if($qryLimit){
			$rowLimit 			= mysql_fetch_assoc($qryLimit);
			$limit['package']	= $rowLimit['packlimit'];
			$limit['pd'] 		= $rowLimit['pdlimit'];
		}
		return $limit;
	}

    function getMatchFactorDiff($module,$usercode)
    {
        //echo "<br>Cat id in function fine diffr in matching factor <pre>";print_r($this->all_catid_array);echo "</pre>";
        $parent_ids = array();
        $fields		= "landline,mobile,stdcode,area,pincode";
        $tablename	= "tbl_companymaster_generalinfo";
        $where		= "parentid='".$this->parentid."'";

        $resGetContactDetails = $this->compmaster_obj->getRow($fields,$tablename,$where);
        if($resGetContactDetails['numrows']>0)
        {
            $rowGetContactDetails = $resGetContactDetails['data']['0'];
            $oldLandline = $rowGetContactDetails['landline'];
            $oldMobile= $rowGetContactDetails['mobile'];
            $std_code = $rowGetContactDetails['stdcode'];
            $pincode = $rowGetContactDetails['pincode'];
            $area = $rowGetContactDetails['area'];
            if(trim($std_code)=='' || intval(trim($std_code))=='0')
            {
                $std_code = $this->get_stdcode($pincode,$area);
            }
            $contact_details_array = array();
            $contact_details_array['landline']  = $rowGetContactDetails['landline'];
            $contact_details_array['mobile']    = $rowGetContactDetails['mobile'];
            $add_contact = $this->GetContactDiff($module,$contact_details_array);//print_r($add_contact);echo "count--".count($add_contact);
            if(count($add_contact)>0)
            {
				$oldContact_details = $oldLandline.",".$oldMobile;
				$oldContact_details = trim($oldContact_details, ",");
				$oldContact_details = str_replace(',', ' ', trim($oldContact_details));
				$oldC2sContractIDs = $this->getC2sParentid($oldContact_details,$std_code);
				$oldC2sContractIDs =  array_filter($oldC2sContractIDs);
				$oldC2sContractIDs = array_merge($oldC2sContractIDs);


				$contact_details = implode(",",$add_contact);
				$contact_details = str_replace(',', ' ', trim($contact_details));
				$addC2sContractIDs = $this->getC2sParentid($contact_details,$std_code);
				$addC2sContractIDs =  array_filter($addC2sContractIDs);
				$addC2sContractIDs = array_merge($addC2sContractIDs);
				/*if(trim($contact_details)!='')
				{
					$parent_ids = array();
					if(isset($std_code) && $std_code!='')
					{
						$stdcode_where = " AND stdcode = 0".$std_code;
					}
					echo "<br>".$qryC2sNonpaid = "SELECT parentid
					FROM c2s_nonpaid
					WHERE
					(MATCH(contact_details) AGAINST ('".$contact_details."'  IN BOOLEAN MODE)) ".$stdcode_where." GROUP BY parentid";
					$resC2sNonpaid = $this -> dbconn_decs ->query_sql($qryC2sNonpaid);
					if($resC2sNonpaid && mysql_num_rows($resC2sNonpaid)>0)
					{
						while($rowC2sNonpaid = mysql_fetch_assoc($resC2sNonpaid))
						{
							$parent_ids[]= $rowC2sNonpaid['parentid'];
						}
					}
				}*/
				if(count($addC2sContractIDs)>0)
				{
					$newCallCount = $this->getC2scallcount($addC2sContractIDs);
				}
				if(count($oldC2sContractIDs)>0)
				{
					$oldCallCount = $this-> getC2scallcount($oldC2sContractIDs);
				}
				//$newCallCount = $this-> GetSumC2scallcnt();
				$callCountDiff = ($oldCallCount>$newCallCount)?$oldCallCount - $newCallCount:$newCallCount- $oldCallCount;
				$call_count_array = $this->GetCatCallcount($this->all_catid_array);

				if(count($this->all_catid_array)>0)
				{
					foreach($this->all_catid_array as $key =>$catid)
					{
						$OldMFValue ='';
						$NewMFValue = '';
						$OldMFValue = $this -> Matching_Contract_Factor($catid,$call_count_array,$oldCallCount);
						$NewMFValue = $this -> Matching_Contract_Factor($catid,$call_count_array,$newCallCount);
						if($NewMFValue>$OldMFValue)
						{
							$this->enterLog($module,$usercode,$contact_details_array);
							echo "<div align='center' width='200px' style='margin-top:55px'><font size='5' color='red' >Pls note : You can not add this phone number at the moment as it involves budget changes.Tme will get in touch with the client soon.</font>
							<br><h2>For more Detail please click <a href='#' onclick='window.open(\"../business/update_contact_numbers.php?parentid=".$this->parentid."\")'>here</a> </h2>
							<br><h3>Click <a href='../business/bform.php'>here</a></h3></div>";
							die();
						}
					}
				}
			}
        }
    }

    function getC2scallcount($pid_arr)
    {
		$sum_contract_callcount = 1;
        if(count($pid_arr)>0)
        {
            $pid_str = implode("','",$pid_arr);
            $qry_c2s_callconnt = "SELECT parentid,company_callcnt FROM c2s_nonpaid WHERE parentid IN ('".$pid_str."')";
            $res_c2s_callconnt = $this -> dbconn_decs ->query_sql($qry_c2s_callconnt);
            if($res_c2s_callconnt && mysql_num_rows($res_c2s_callconnt)>0)
            {
                while($row_c2s_callconnt = mysql_fetch_assoc($res_c2s_callconnt))
                $sum_contract_callcount += $row_c2s_callconnt['company_callcnt'];
            }
        }
        return $sum_contract_callcount;
    }

    function enterLog($module,$usercode,$contact_details)
    {
        //$qryInsertLandline = '';
        $removeLandlinestr ='';
        $addLandlinestr ='';
        $removeMobilestr = '';
        $addMobilestr = '';
        $tempLandline_Arr = array();
        $tempMobile_Arr= array();
        $liveLandline_arr = array();
        $liveMobile_arr = array();
        if($contact_details['landline']!='')
        {
            $liveLandline_arr = explode(",",$contact_details['landline']);
        }
        if($contact_details['mobile']!='')
        {
            $liveMobile_arr = explode(",",$contact_details['mobile']);
        }

        if(strtolower($module)=='cs'){
			$fields		= "landline,mobile,stdcode,area,pincode";
			$tablename	= "tbl_companymaster_generalinfo_shadow";
			$where		= "parentid='".$this->parentid."'";
            $resGetTempValue = $this->compmaster_obj->getRow($fields,$tablename,$where);
            $remTempCount= $resGetTempValue['numrows'];
        }else if(strtolower($module)=='tme' || strtolower($module)=='me'){
			$qryGetTempValue = "select landline,mobile from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			if(strtolower($module)=='tme'){
            $resGetTempValue = $this -> dbconn_tme ->query_sql($qryGetTempValue);
			}elseif(strtolower($module)=='me'){
				$resGetTempValue = $this -> dbconn_idc ->query_sql($qryGetTempValue);
			}
			$remTempCount= mysql_num_rows($resGetTempValue);
		}

        if($remTempCount>0)
        {
			if(strtolower($module)=='cs'){
				$rowGetTempValue = $resGetTempValue['data']['0'];
			}else{
				$rowGetTempValue = mysql_fetch_assoc($resGetTempValue);
			}

            $temp_Landline = $rowGetTempValue['landline'];
            $temp_Mobile = $rowGetTempValue['mobile'];
            if($temp_Landline!='')
            {
                $tempLandline_Arr = explode(",",$temp_Landline);
            }
            if($temp_Mobile!='')
            {
                $tempMobile_Arr = explode(",",$temp_Mobile);
            }
        }
        $removeLandlineArr = array_diff($liveLandline_arr,$tempLandline_Arr);
        $removeLandlineArr = array_filter($removeLandlineArr);
        $removeLandlineArr = array_merge($removeLandlineArr);
        $addLandlineArr = array_diff($tempLandline_Arr,$liveLandline_arr);
        $addLandlineArr = array_filter($addLandlineArr);
        $addLandlineArr = array_merge($addLandlineArr);
        if(count($removeLandlineArr)>0){
            foreach($removeLandlineArr as $key => $value){
                $removeLandlinestr .= "('".$this->parentid."','".DATA_CITY."','".$value."','Remove','".$usercode."','".date("Y-m-d H:i:s")."','".$module."'),";
            }
        }
        $qryInsertLandline = "insert into tbl_number_modification_log (parentid,data_city,number_modified,modification,doneby,doneon,module) values ";

        if($removeLandlinestr)
        {
            $removeLandlinestr = trim($removeLandlinestr,",");
            $finalQuery = $qryInsertLandline.$removeLandlinestr;
            $resfinalQuery = $this -> dbconn_decs ->query_sql($finalQuery);
        }

        if(count($addLandlineArr)>0){
            foreach($addLandlineArr as $key => $value){
                $addLandlinestr .= "('".$this->parentid."','".DATA_CITY."','".$value."','Add','".$usercode."','".date("Y-m-d H:i:s")."','".$module."'),";
            }
        }
        if($addLandlinestr)
        {
            $addLandlinestr = trim($addLandlinestr,",");
            $finalQueryAddLandline = $qryInsertLandline.$addLandlinestr;
            $resfinalQueryAddLandline = $this -> dbconn_decs ->query_sql($finalQueryAddLandline);
        }
        
        $qryInsertMobile = "insert into tbl_number_modification_log (parentid,data_city,number_modified,modification,doneby,doneon,module) values ";
        $removeMobileArr = array_diff($liveMobile_arr,$tempMobile_Arr);
        $removeMobileArr = array_filter($removeMobileArr);
        $removeMobileArr = array_merge($removeMobileArr);
        $addMobileArr = array_diff($tempMobile_Arr,$liveMobile_arr);
        $addMobileArr = array_filter($addMobileArr);
        $addMobileArr = array_merge($addMobileArr);
        if(count($removeMobileArr)>0){
            foreach($removeMobileArr as $key => $value){
                $removeMobilestr .= "('".$this->parentid."','".DATA_CITY."','".$value."','Remove','".$usercode."','".date("Y-m-d H:i:s")."','".$module."'),";
            }
        }
        if($removeMobilestr)
        {
            $removeMobilestr = trim($removeMobilestr,",");
            $finalQueryMobile = $qryInsertMobile.$removeMobilestr;
            $resfinalQueryMobile = $this -> dbconn_decs ->query_sql($finalQueryMobile);
        }

        if(count($addMobileArr)>0){
            foreach($addMobileArr as $key => $value){
                $addMobilestr .= "('".$this->parentid."','".DATA_CITY."','".$value."','Add','".$usercode."','".date("Y-m-d H:i:s")."','".$module."'),";
            }
        }
        if($addMobilestr)
        {
            $addMobilestr = trim($addMobilestr,",");
            $finalQueryAddMobile = $qryInsertMobile.$addMobilestr;
            $resfinalQueryAddMobile = $this -> dbconn_decs ->query_sql($finalQueryAddMobile);
        }
    }

    function get_stdcode($pincode,$area)
    {
        $qry_get_stdcode="SELECT stdcode FROM d_jds.tbl_area_master WHERE pincode='".trim($pincode)."' AND area='".trim($area)."' AND display_flag=1 AND deleted=0 LIMIT 1";
        $res_get_stdcode = $this->dbconn_decs->query_sql($qry_get_stdcode);
        if($res_get_stdcode && mysql_num_rows($res_get_stdcode)>0)
        {
            $row_get_stdcode = mysql_fetch_assoc($res_get_stdcode);
            $new_stdcode = $row_get_stdcode['stdcode'];
        }
        return $new_stdcode;
    }
    function GetContactDiff($module,$contact_details_array)
    {
		$addLandlinestr ='';
        $addMobilestr = '';
		$liveLandline_arr = array();
		$liveMobile_arr = array();
		$tempLandline_Arr = array();
		$tempMobile_Arr = array();
        $addLandlineArr = array();
        $addMobileArr   = array();
		if($contact_details_array['landline']!='')
        {
            $liveLandline_arr = explode(",",$contact_details_array['landline']);
        }
        if($contact_details_array['mobile']!='')
        {
            $liveMobile_arr = explode(",",$contact_details_array['mobile']);
        }

        if(strtolower($module)=='cs'){
			$fields		= "landline,mobile";
			$tablename	= "tbl_companymaster_generalinfo_shadow";
			$where		= "parentid='".$this->parentid."'";
            $resGetTempValue1 = $this->compmaster_obj->getRow($fields,$tablename,$where);
            $remTempCount1= $resGetTempValue1['numrows'];

        }else if (strtolower($module)=='me' || strtolower($module)=='tme'){
			$qryGetTempValue1 = "select landline,mobile from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			if(strtolower($module)=='tme'){
				$resGetTempValue1 = $this -> dbconn_tme ->query_sql($qryGetTempValue1);
			}elseif(strtolower($module)=='me'){
				$resGetTempValue1 = $this -> dbconn_idc ->query_sql($qryGetTempValue1);
			}
			 $remTempCount1 = mysql_num_rows($resGetTempValue1);
		}
        if($remTempCount1>0)
        {
			if(strtolower($module)=='cs'){
				$rowGetTempValue1 = $resGetTempValue1['data']['0'];
			}else{
				$rowGetTempValue1 = mysql_fetch_assoc($resGetTempValue1);
			}
            $temp_Landline1 = $rowGetTempValue1['landline'];
            $temp_Mobile1 = $rowGetTempValue1['mobile'];
            if($temp_Landline1!='')
            {
                $tempLandline_Arr = explode(",",$temp_Landline1);
            }
            if($temp_Mobile1!='')
            {
                $tempMobile_Arr = explode(",",$temp_Mobile1);
            }
        }
        $addLandlineArr = array_diff($tempLandline_Arr,$liveLandline_arr);
        $addLandlineArr = array_filter($addLandlineArr);
        $addLandlineArr = array_merge($addLandlineArr);
        $addMobileArr = array_diff($tempMobile_Arr,$liveMobile_arr);
        $addMobileArr = array_filter($addMobileArr);
        $addMobileArr = array_merge($addMobileArr);
        $diff_arr = array_merge($addLandlineArr,$addMobileArr);
        
        return $diff_arr;
	}
	
	function getC2sParentid($contact_details,$std_code)
	{
		$parent_ids = array();
		if(trim($contact_details)!='')
		{
			$parent_ids = array();
			if(isset($std_code) && $std_code!='')
			{
				$stdcode_where = " AND stdcode = 0".$std_code;
			}
			$qryC2sNonpaid = "SELECT parentid
			FROM c2s_nonpaid 
			WHERE
			(MATCH(contact_details) AGAINST ('".$contact_details."'  IN BOOLEAN MODE)) ".$stdcode_where." GROUP BY parentid";
			$resC2sNonpaid = $this -> dbconn_decs ->query_sql($qryC2sNonpaid);
			if($resC2sNonpaid && mysql_num_rows($resC2sNonpaid)>0)
			{
				while($rowC2sNonpaid = mysql_fetch_assoc($resC2sNonpaid))
				{
					$parent_ids[]= $rowC2sNonpaid['parentid'];
				}
			}
		}
		return $parent_ids;
	}
	
	
	
	function getNewBudget($module)
	{
		//print_r($this->all_catid_array);
		$allBudgetArr= array();
		unset ($rowGetContactDetail);
		mysql_free_result($resGetContactDetails);
		$MainfinanceRow = $this->financeObj -> getFinanceMainData();
		$tenure_main = ($MainfinanceRow[2]['budget']>0?$MainfinanceRow[2]['duration']:($MainfinanceRow[1]['budget']>0?$MainfinanceRow[1]['duration']:365));

		$fields		= "landline,mobile,stdcode,area,pincode";
		$tablename	= "tbl_companymaster_generalinfo";
		$where		= "parentid='".$this->parentid."'";

		$resGetContactDetails = $this->compmaster_obj->getRow($fields,$tablename,$where);

        if($resGetContactDetails['numrows']>0)
        {
            $rowGetContactDetails = $resGetContactDetails['data']['0'];
            $oldLandline = $rowGetContactDetails['landline'];
            $oldMobile= $rowGetContactDetails['mobile'];
            $std_code = $rowGetContactDetails['stdcode'];
            $pincode = $rowGetContactDetails['pincode'];
            $area = $rowGetContactDetails['area'];
            if(trim($std_code)=='' || intval(trim($std_code))=='0')
            {
                $std_code = $this->get_stdcode($pincode,$area);
            }
            $contact_details_array = array(); 
            $contact_details_array['landline']  = $rowGetContactDetails['landline'];
            $contact_details_array['mobile']    = $rowGetContactDetails['mobile'];
            $add_contact = $this->GetContactDiff($module,$contact_details_array);
            $allBudgetArr[0]=$add_contact;
		}
		$contact_details_str = implode(",",$contact_details_array);
		$contact_details_str = str_replace(',', ' ', trim($contact_details_str));
		$oldC2sContractIDs = $this->getC2sParentid($contact_details_str,$std_code);
		$oldCallCount = $this-> getC2scallcount($oldC2sContractIDs);
		$sum_contract_callcount = $this-> GetSumC2scallcnt();
		$call_count_array = $this->GetCatCallcount($this->all_catid_array);
		if(count($this->all_catid_array)>0)
		{
			foreach($this->all_catid_array as $keyid =>$valueCatid)
			{
				unset($cat_row);
				$cat_row = $this -> getCategoryinfo($valueCatid);
				$package_value   = $this->inventory_booking->GetCategoryPackageValue($valueCatid,6,$this->parentid,$cat_row['cat_type'],$this -> myzone,$this-> data_city,$tenure_main,$call_count_array,$sum_contract_callcount,'2');
				$OldMFValue = $this -> Matching_Contract_Factor($valueCatid,$call_count_array,$oldCallCount);
				$NewMFValue = $this -> Matching_Contract_Factor($valueCatid,$call_count_array,$sum_contract_callcount);
				if($MainfinanceRow[2]['balance']>0 && $MainfinanceRow[1]['balance']>0)
				{
					$newDiff+=($package_value*$NewMFValue)-$package_value;
					$oldDiff+=($package_value*$OldMFValue)-$package_value;
				}
				elseif($MainfinanceRow[1]['balance']>0)
				{
					$OldtotalPackValue +=($package_value*$OldMFValue);
					$NewtotalPackValue +=($package_value*$NewMFValue);
				}
				elseif($MainfinanceRow[2]['balance']>0)
				{
					$totalOldRegfee += ($package_value*$OldMFValue)-$package_value;
					$totalNewRegFee += ($package_value*$NewMFValue)-$package_value;
				}
			}
		}
		if($MainfinanceRow[2]['balance']>0 && $MainfinanceRow[1]['balance']>0)
		{
			$totalbug = ($MainfinanceRow[1]['budget']+$MainfinanceRow[2]['budget'])-$oldDiff;
			$newTotalbug = $totalbug + $newDiff;
			($MainfinanceRow[1]['budget']+$MainfinanceRow[2]['budget']);
			$newTotalbug;
			$allBudgetArr[1] = ($MainfinanceRow[1]['budget']+$MainfinanceRow[2]['budget']);
			$allBudgetArr[2] = $newTotalbug;
		}
		elseif($MainfinanceRow[1]['balance']>0)
		{
			$allBudgetArr[1] = $MainfinanceRow[1]['budget'];
			$allBudgetArr[2] = $NewtotalPackValue;
		}
		elseif($MainfinanceRow[2]['balance']>0)
		{
			if($MainfinanceRow[7]['budget']>0){
				$allBudgetArr[1] = ($MainfinanceRow[2]['budget']+$MainfinanceRow[7]['budget']);
				$allBudgetArr[2] = ($MainfinanceRow[2]['budget']+$totalNewRegFee);
			}else{
				$allBudgetArr[1] = ($MainfinanceRow[2]['budget']);
				$allBudgetArr[2] = (($MainfinanceRow[2]['budget']-$totalOldRegfee)+$totalNewRegFee);
			}
		}
		return $allBudgetArr;
	}
   
   function bestbudget_signup_entry($insertArr){
		
		if(count($insertArr) > 0){
			$sqlIns = "INSERT INTO tbl_bestbudget_signup_details_temp SET
						parentid 	 = '".$this -> parentid."',
						Campaignid	 = '".$insertArr['camp']."',
						duration	 = '".$insertArr['duration']."',
						budget		 = '".$insertArr['budget']."',
						deal_type	 = '".$insertArr['dealType']."',
						deal_date 	 = now()";
			
			$qryIns	= $this -> dbconn_fnc->query_sql($sqlIns);
		}
	}
	
	function calcMinAdvanceAmountFactor()
	{
		$sqlQryGetCap = "select  AdvMinBudgetFactor from tbl_business_uploadrates where city = '".$this-> data_city."'";
		$sqlResGetCap =$this -> areab_Obj-> query_sql($sqlQryGetCap);
		
		if($sqlResGetCap && mysql_num_rows($sqlResGetCap)>0)
		{
			$sqlRowGetCap = $this -> areab_Obj -> fetchData($sqlResGetCap);
			//$percentValue = (($sqlRowGetCap['AdvMinBudgetFactor']>0)?$sqlRowGetCap['AdvMinBudgetFactor']:self::DEFAULT_MIN_FACTOR);
			$minbudgetCap = (($sqlRowGetCap['AdvMinBudgetFactor']>0)?$sqlRowGetCap['AdvMinBudgetFactor']:self::DEFAULT_MIN_FACTOR);

		}
		return $minbudgetCap;
	}
	
	function writeMFfile($catid,$call_count_array,$c2s_callcount,$matching_contract_factor=0,$catAvgCallcount=0,$category_contribution=0)
    {
		//deciding directory to be created
		$sNamePrefix = '../logs/matchingFactorlog/';
		
		// fetch directory for the file
		$pathToLog = dirname($sNamePrefix);
		
		if (!file_exists($pathToLog)) {
			mkdir($pathToLog, 0755, true);
		}
		if(!file_exists($sNamePrefix))
		{ 
			mkdir($sNamePrefix, 0777, true); 
		}
		// DateTime stamp.
		$dt = date('Ymd');
		
		$now = date('Y-m-d H:i:s');
		
		//append parentid 
		$parentid =$this->parentid;//initialize paretnid  with p from session 
		// Set this to whatever location the log file should reside at.
		$filename = "matchingfactor".$parentid;
		$logFile = fopen("$sNamePrefix$filename.log", 'a');
		$categoryCallcountStr = array_map(create_function('$key, $value', 'return $key.":".$value[callcount]." # ";'), array_keys($call_count_array), array_values($call_count_array));//die("line 3495-->".implode(",",$categoryCallcountStr));
		$msg = "[$now] [parentid -> ".$parentid."]  [Catid -> ".$catid."] [Matching factor -> ".$matching_contract_factor."] [Category call count -> ".implode(",",$categoryCallcountStr)."] [sum_callcount -> ".$call_count_array['sum_callcount']."] [cat avg. callcount ->".$catAvgCallcount."][ Cateory contribution-> ".$category_contribution."] [C2s Callcount -> ".$c2s_callcount."]\n";
		
		fwrite($logFile, $msg);
		fclose($logFile);
	}

	function update_vlc_package_log($yearly_budg,$tax,$vlc_flag){
		if($yearly_budg!=0 || $yearly_budg!=''){
			$package_value = $yearly_budg*$tax;
			$upfront_value = $package_value-820;
		}
		if($vlc_flag!=1){
			$sql_delete ="DELETE FROM d_jds.tbl_vlc_package_log WHERE parentid='".$this->parentid."'";
			$res_vlclog =$this -> dbconn_decs -> query_sql($sql_delete);
		}else{
			$sql_vlclog ="INSERT INTO d_jds.tbl_vlc_package_log 
				SET parentid='".$this->parentid."', package_value='".$package_value."', upfront_package_value='".$upfront_value."', vlc_flag='".$vlc_flag."', tax='".$tax."', updatedBy='".$_SESSION[ucode]."' , updatedOn=now()
				ON DUPLICATE KEY UPDATE
				package_value='".$package_value."', upfront_package_value='".$upfront_value."', vlc_flag='".$vlc_flag."', tax='".$tax."', updatedBy='".$_SESSION[ucode]."', updatedOn=now()";
			$res_vlclog =$this -> dbconn_decs -> query_sql($sql_vlclog);
		}
	}
	
}

?>
