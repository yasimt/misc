<?php

class budgetinfoClass extends DB
{
	var $dbConIro = null;
	var $dbConDjds = null;
	var $dbConTmeJds = null;
	var $dbConFin = null;
	var $dbConIdc = null;
	var $params = null;
	var $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var $arr_errors = array();
	var $is_split = null;
	var $parentid = null;
	var $mongo_obj = null;


	var $catsearch = null;
	var $data_city = null;
	var $currenttempcategories = array();




	function __construct($params)
	{
		$this->params = $params;
		$this->setServers();


		if ($this->params['is_remote'] == 'REMOTE') {
			$this->is_split = false;	 // when split table goes live then make it TRUE		
		} else {
			$this->is_split = false;
		}

		if (trim($this->params['parentid']) != "") {
			$this->parentid = strtoupper($this->params['parentid']); //initialize parentid
		} else {
			$errorarray['errormsg'] = 'parentid missing';
			echo json_encode($errorarray);
			exit;
		}

		if (trim($this->params['data_city']) != "" && $this->params['data_city'] != null) {
			$this->data_city = $this->params['data_city']; //initialize datacity
		} else {
			$errorarray['errormsg'] = 'data_city missing';
			echo json_encode($errorarray);
			exit;
		}

		$this->mongo_obj = new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		
	}
// Function to set DB connection objects
	function setServers()
	{
		global $db;

		$data_city = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');

		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		$this->finance = $db[$data_city]['fin']['master'];
		$this->dbConbudget = $db[$data_city]['db_budgeting']['master'];

	}



	function showInventory()
	{
		switch ($this->astatus) {
			case 1:	// live inventory
				//$ret['live']   = $this->live_inventory();	
				$live_array = $this->live_inventory();
				if ($live_array['0']['inv']['error']['code'] == 0 && $live_array['0']['bgt']['error']['code'] == 0) {
					$ret['live']['results'] = $live_array;
					$ret['live']['error']['code'] = "0";
					$ret['live']['error']['msg'] = "";
				} else {
					$ret['live']['results'] = $live_array;
					$ret['live']['error']['code'] = "1";
					$ret['live']['error']['msg'] = "Data Inconsistent / Data Not Found ";
				}
				break;

			case 2:	// shadow inventory
				//$ret['shadow'] = $this->shadow_inventory();
				$shadow_array = $this->shadow_inventory();

				if ($shadow_array['0']['inv']['error']['code'] == 0 && $shadow_array['0']['bgt']['error']['code'] == 0) {
					$ret['shadow']['results'] = $shadow_array;
					$ret['shadow']['error']['code'] = "0";
					$ret['shadow']['error']['msg'] = "";
				} else {
					$ret['shadow']['results'] = $shadow_array;
					$ret['shadow']['error']['code'] = "1";
					$ret['shadow']['error']['msg'] = "Data Inconsistent / Data Not Found ";
				}

				break;

			default:
				//$ret['live']   = $this->live_inventory();
				$live_array = $this->live_inventory();
				if ($live_array['0']['inv']['error']['code'] == 0 && $live_array['0']['bgt']['error']['code'] == 0) {
					$ret['live']['results'] = $live_array;
					$ret['live']['error']['code'] = "0";
					$ret['live']['error']['msg'] = "";
				} else {
					$ret['live']['results'] = $live_array;
					$ret['live']['error']['code'] = "1";
					$ret['live']['error']['msg'] = "Data Inconsistent / Data Not Found ";
				}
				
				//$ret['shadow'] = $this->shadow_inventory();	
				$shadow_array = $this->shadow_inventory();
				$max_dc_date = "";
				if ($shadow_array['0']['inv']['error']['code'] == 0 && $shadow_array['0']['bgt']['error']['code'] == 0) {
					$max_dc_date = $shadow_array['max_dc_date'];
					unset($shadow_array['max_dc_date']);

					$ret['shadow']['results'] = $shadow_array;
					$ret['shadow']['max_dc_date'] = $max_dc_date;
					$ret['shadow']['error']['code'] = "0";
					$ret['shadow']['error']['msg'] = "";
				} else {
					$ret['shadow']['results'] = $shadow_array;
					$ret['shadow']['error']['code'] = "1";
					$ret['shadow']['error']['msg'] = "Data Inconsistent / Data Not Found ";
				}
				// live & Shadow Inventory
		}

		if (!($ret['shadow']['error']['code'] == 1 || $ret['live']['error']['code'] == 1)) {
			$return_array['results'] = $ret;
			$return_array['error']['code'] = 0;
			$return_array['error']['msg'] = '';
		} else {
			$return_array['results'] = $ret;
			$return_array['error']['code'] = 1;
			$return_array['error']['msg'] = 'Inventory Not Found';
		}
		return ($return_array);
	}

	function getlivecatpindetails()
	{
		return;
/*	
	
	$datasource=null;
	
	$sql	="select campaignid,budget,duration,balance,version,bid_perday,expired,(balance/bid_perday) as remaining_tenure  from tbl_companymaster_finance where parentid ='".$this->parentid."' and campaignid in (1,2) and balance>0 ORDER BY campaignid";
	$res 	= parent::execQuery($sql, $this->finance);
	$num	= mysql_num_rows($res);
	
	if(DEBUG_MODE)
	{
		echo '<br><b>Finance DB Query:</b>'.$sql;
		echo '<br><b>Result Set:</b>'.$res;
		echo '<br><b>Num Rows:</b>'.$num;
		echo '<br><b>Error:</b>'.$this->mysql_error;
		
	}
	
	if($res && $num > 0)
	{
		if($num==2)
		{
			
			$result_array['finance']['error']['code'] = "1";
			$result_array['finance']['error']['msg'] = "This is not a pure package contract  ";
			$result_array['error']['code'] = "1";
			$result_array['error']['msg'] = "not pure package";

			return $result_array;
			die();
		
		}elseif($num==1)
		{		
			while($row=mysql_fetch_assoc($res))
			{
				$campaignid = $row['campaignid'];
									
				$budget_array['result']['finance']['data']['campaignid']  = (int)$campaignid;
				$budget_array['result']['finance']['data']['budget'] 	 = floatval($row['budget']);
				$budget_array['result']['finance']['data']['tenure'] 	 = intval($row['duration']);
				$budget_array['result']['finance']['data']['balance'] 	 = floatval($row['balance']);
				$budget_array['result']['finance']['data']['version'] 	 = floatval($row['version']);
				$budget_array['result']['finance']['data']['bid_perday']  = floatval($row['bid_perday']);
				$budget_array['result']['finance']['data']['remaining_tenure']  = floatval($row['remaining_tenure']);
				$budget_array['result']['finance']['data']['expired'] 	 = $row['expired'];
			}
		}
	}
	//else
	//{
		
		//$result_array['error']['code'] = "1";
		//$result_array['error']['msg'] = "data not found for ".$this->params['campaignname']." campaign ";
		//return $result_array;
		//die();
	//}
	
	
	// first we will check data inside tbl_bidding_details_shadow (pending)  table if we do not get data then only we will read from live table 
	
	
	//$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' ORDER BY catid, pincode, position_flag";
	
	$sql="select catid,pincode,callcount,bidvalue,actual_budget,inventory,version from tbl_bidding_details_shadow where parentid='".$this->parentid."' ";
	$res 	= parent::execQuery($sql, $this->dbConbudget);
	$num		= mysql_num_rows($res);	
	
	if(DEBUG_MODE)
	{
		echo '<hr><b>Live DB Query:</b>'.$sql;
		echo '<br><b>Result Set:</b>'.$res;
		echo '<br><b>Num Rows:</b>'.$num;
		echo '<br><b>Error:</b>'.$this->mysql_error;
		echo '<hr>';
	}
	
	if($res && $num > 0)
	{	
		$datasource="shadow";
		$cnt = 0;
		$pendingversion=0;
		$paduration=0;
		$pabudget=0;
		$shadowtotalbudget=0;
		
		while($row=mysql_fetch_assoc($res))
		{
			//echo '<hr>';
			//print_r($row);
			$catid 		 = $row['catid'];
			$pincode	 = $row['pincode'];
			$pendingversion	= $row['version'];
			
			$budget_array['result']['c_data'][$catid]['cnm'] = "";
			//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] 		= $row['position_flag'];
			$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['cnt_f']		= $row['callcount'];
			$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidvalue'] 	= $row['bidvalue'];
			$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget']  		= $row['actual_budget'];
			$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['inv']      	= $row['inventory'];
			
			$shadowtotalbudget+=$row['actual_budget'];
			$catid_array[]   = $catid;
			$pincode_array[] = $pincode;
		}
		
		// populating bidperday
		
		// finding tennure 
		
		$version= $pendingversion;
		
		
		$sql	="select campaignId,duration,budget from payment_apportioning where parentid='".$this->parentid."' and version ='".$pendingversion."' and budget-balance>1 and campaignId in (1,2) ";		
		$res 	= parent::execQuery($sql, $this->finance);
		$num	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<hr><b>Live DB Query:</b>'.$sql;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($num==2)
		{
			
			$result_array['finance']['error']['code'] = "1";
			$result_array['finance']['error']['msg'] = "This is not a pure package contract  ";
			return $result_array;
			die();
		
		}elseif($num==1)
		{
			$pa_row = mysql_fetch_assoc($res);
			
			if($pa_row['campaignId']!=1)
			{
				$result_array['finance']['error']['code'] = "1";
				$result_array['finance']['error']['msg'] = "This is not a pure package contract  ";
				return $result_array;
				die();
				
			}else
			{
				$paduration = $pa_row['duration'];
				$pabudget	= $pa_row['budget'];
				$multiplyingfactor= $pabudget/$shadowtotalbudget;
			}			
		}
		
		if(DEBUG_MODE)
		{
			echo '<br>paduration : '.$paduration;
			echo '<br>pabudget : '.$pabudget;
			echo '<br>multiplyingfactor : '.$multiplyingfactor;
			
		}
		
		
		foreach($budget_array['result']['c_data'] as $catid => $catid_values)
		{
			if(DEBUG_MODE)
			{
				echo '<br>Catid : '.$catid;
				//echo '<br>Catid Values : ';
				print_r($catid_values);
			}
			
			
			
			foreach($catid_values['pin_data'] as $pincode => $pincode_values)
			{	
				
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] = $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget']*$multiplyingfactor;
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidperday']  = $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget']/$paduration;
					
					if(DEBUG_MODE)
					{
						//echo 'actual_budget.catid'.$catid.'--$pincode--'.$pincode. ' = '. $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'];
					}
			}
		}
		
			if(DEBUG_MODE)
			{
				echo $pabudget;
			}
			
		$budget_array['result']['tb_flexi_bgt']= $pabudget ;// budget from payment apportioning table 
	
	}else
	{
		$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' ORDER BY catid, pincode, position_flag";
		$res 	= parent::execQuery($sql, $this->finance);
		$num		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<hr><b>Live DB Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<hr>';
		}
		
		if($res && $num > 0)
		{		
			$datasource="live";
			$cnt = 0;
			while($row=mysql_fetch_assoc($res))
			{
				//echo '<hr>';
				//print_r($row);
				$catid 		 = $row['catid'];
				$pincode	 = $row['pincode'];
				$version	 = $row['version'];			
				
				$budget_array['result']['c_data'][$catid]['cnm'] = "";
				//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] 		= $row['position_flag'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['cnt_f']		= $row['callcount'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidvalue'] 	= $row['bidvalue'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget']  	= $row['actual_budget'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidperday']  = $row['bidperday'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['inv']      	= $row['inventory'];
								
				$catid_array[]   = $catid;
				$pincode_array[] = $pincode;
			}
			
		$budget_array['result']['tb_flexi_bgt']= $budget_array['result']['finance']['data']['budget'] ;// budget from main table 
			
		}
	
	}
	
	
	// now processing the cat pin data 
	
	if($datasource==null)
	{
			$result_array['error']['code'] = "1";
			$result_array['error']['msg'] = "data not found in bidding details tables campaign ";
			return $result_array;
			die();
	}else
	{
		
		if(count($catid_array)>0)
		{
			$pincode_array = array_unique($pincode_array);
			$pincode_list  = implode(",",$pincode_array);
			
			$catid_array = array_unique($catid_array);
			$catid_list = implode(",",$catid_array);
			
			$cat_array = $this->get_category_details($catid_list);
			$pin_array = $this->get_pincode_details($pincode_list);
			
			$fixedposition_arr= $this->fn_fixedposition_bidders_wraper($version);
			
			
			foreach($cat_array as $catid=>$value_array)
			{
				$budget_array['result']['c_data'][$catid]['cnm'] = $value_array['cnm'];
				
			}
			
			
			
			$package_array = $this->fn_package_bidders($catid_list,$pincode_list);
		
			$package_bidders 	= $package_array['bidder'];
			$package_bid 		= $package_array['bid'];
			$package_sc  		= $package_array['sc'];
			
			$category_bidders = $package_array['cat_bidder'];
			$category_bid = $package_array['cat_bid'];
			
			if(DEBUG_MODE)
			{
					echo '<pre>budget_array ';
					print_r($budget_array);
					
					echo '<pre>pin_array ';
					print_r($pin_array);
					
					echo '<pre>fixedposition_arr';
					print_r($fixedposition_arr);
					
			}
			
			$total_flexi_budget = 0;
			foreach($budget_array['result']['c_data'] as $catid => $catid_values)
			{
				if(DEBUG_MODE)
				{
					echo '<br>Catid : '.$catid;
					//echo '<br>Catid Values : ';
					print_r($catid_values);
				}
				$flexi_bgt_sum	=	0;
				foreach($catid_values['pin_data'] as $pincode => $pincode_values)
				{					
					
					//$flexi_bgt = round($pincode_values['best_bgt']*$flexi_factor,2);
					//$flexi_bpd = round($flexi_bgt/365,2);
					
					// for existing contracts it will be actual budget 
					
					$flexi_bgt = $pincode_values['budget'];
					$flexi_bpd = $pincode_values['bidperday'];
					
					if($package_bid[$catid][$pincode])
						$flexi_pos = $this->closest_binary($flexi_bpd,$package_bid[$catid][$pincode]);
					else
						$flexi_pos = 1;
					
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['anm'] 		 = $pin_array[$pincode]['anm'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] 		= $fixedposition_arr['result']['c_data'][$catid]['pin_data'][$pincode]['pos'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bgt'] = $flexi_bgt;
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_pos'] = $flexi_pos;
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bpd'] = $flexi_bpd;
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bidder'] = $package_bidders[$catid][$pincode];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bid'] = $package_bid[$catid][$pincode];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_sc'] = $package_sc[$catid][$pincode];
					
					$total_flexi_budget += $flexi_bgt;
					$flexi_bgt_sum	+=	$flexi_bgt;
					if(DEBUG_MODE)
					{
						echo '<br>Flexi';
						echo '<br>Pincode : '.$pincode;
						//echo '<br>Pincode Values : ';
						//print_r($pincode_values);
						echo '<br>best_bgt : '.$pincode_values['best_bgt'];
						echo '<br>best_pos : '.$pincode_values['best_flg'];
						echo '<br>flexi_bgt : '.$flexi_bgt;
						echo '<br>flexi_pos : '.$flexi_pos;
					}
					
				}
				$budget_array['result']['c_data'][$catid]['flexi_bgt']	=	$flexi_bgt_sum;
				$total_flexi_budget = round($total_flexi_budget);
			}
				
			
		}
	
		$budget_array['result']['category_bidders'] =  $category_bidders;
		$budget_array['result']['category_bid'] =  $category_bid;
		
		
		$budget_array['result']['datasource']= $datasource;
		
		$budget_array['error']['code'] = "0";
		$budget_array['error']['msg'] = "No error";
	}
	
	return($budget_array);
		 */
	}

	function getcurrenttempcategories()
	{
		$rerutnarr = array();
		$mongo_inputs = array();
		$mongo_inputs['parentid'] = $this->parentid;
		$mongo_inputs['data_city'] = $this->data_city;
		$mongo_inputs['module'] = 'ME'; // currently it is for website manage campaing so we can are using ME
		$mongo_inputs['table'] = "tbl_business_temp_data";
		$mongo_inputs['fields'] = "catIds";
		$catid_arr = $this->mongo_obj->getData($mongo_inputs);

		$catid_str = '';
		$rerutnarr = explode('|P|', trim($catid_arr['catIds'], '|P|'));



		if (DEBUG_MODE) {
			echo '<br>inside getcurrenttempcategories <br> mongo_inputs';
			print_r($mongo_inputs);
			echo '<br> mongo output ';
			print_r($catid_arr);
			echo '<br> rerutnarr ';
			print_r($rerutnarr);
		}

		return $rerutnarr;
	}

	function getCompanymasterFinancedata()
	{
		$sql = "select campaignid,budget,duration,balance,version,bid_perday,expired,(balance/bid_perday) as remaining_tenure  from tbl_companymaster_finance where parentid ='" . $this->parentid . "' and campaignid in (1,2) and balance>0 ORDER BY campaignid";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		$finance_live_total_budget = 0;
		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;

		}

		if ($num > 0) {
			while ($row = mysql_fetch_assoc($res)) {

				$campaignid = (int)$row['campaignid'];
				$compmasterfinanceversion = intval($row['version']);

				$finance_array['result']['finance']['data'][$campaignid]['campaignid'] = $campaignid;
				$finance_array['result']['finance']['data'][$campaignid]['budget'] = floatval($row['budget']);
				$finance_array['result']['finance']['data'][$campaignid]['tenure'] = intval($row['duration']);
				$finance_array['result']['finance']['data'][$campaignid]['balance'] = floatval($row['balance']);
				$finance_array['result']['finance']['data'][$campaignid]['version'] = intval($row['version']);
				$finance_array['result']['finance']['data'][$campaignid]['bid_perday'] = floatval($row['bid_perday']);
				$finance_array['result']['finance']['data'][$campaignid]['remaining_tenure'] = floatval($row['remaining_tenure']);
				$finance_array['result']['finance']['data'][$campaignid]['expired'] = $row['expired'];

				$finance_array['result']['finance']['version'] = $compmasterfinanceversion;

			}

		} else {
			$sql = "select version from tbl_bidding_details_shadow where parentid='" . $this->parentid . "' order by booked_date desc limit 1";
			$res = parent::execQuery($sql, $this->dbConbudget);
			$num = mysql_num_rows($res);
			$row = mysql_fetch_assoc($res);

			if (DEBUG_MODE) {
				echo '<br><b>Finance DB Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			$finance_array['result']['finance']['version'] = $row['version'];

		}

		return $finance_array;

	}

	function paymenttypedealclosed()
	{
		$findata = $this->getCompanymasterFinancedata();
		$version = $findata['result']['finance']['version'];

		$returnarray = $this->fn_tbl_payment_type_dealclosed($version, $findata);
		return $returnarray;

	}

	
	
	
	function paymenttypedealclosedwithcampaignname()
	{
		$findata = $this->getCompanymasterFinancedata();
		$version = $findata['result']['finance']['version'];

		$returnarray = $this->fn_tbl_payment_type_dealclosed($version, $findata,1);
		return $returnarray;

	}
	
	function getlivepdgpackcatpindetails()
	{

		$datasource = null;
		$this->currenttempcategories = $this->getcurrenttempcategories();


		$sql = "select campaignid,budget,duration,balance,version,bid_perday,expired,(balance/bid_perday) as remaining_tenure  from tbl_companymaster_finance where parentid ='" . $this->parentid . "' and campaignid in (1,2) and balance>0 ORDER BY campaignid";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		$finance_live_total_budget = 0;
		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;

		}

		if ($res && $num > 0) {
			$ecs_edit = true;
			$get_ecs_status = "SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='" . $this->parentid . "' AND deactiveflag = 0 AND ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1  UNION SELECT outlet_parentid,master_billdeskid from db_ecs.ecs_mandate_outlet WHERE outlet_parentid='" . $this->parentid . "' AND outlet_status IN (0,1) AND vertical_flag=0 LIMIT 1";
			$res_ecs_status = parent::execQuery($get_ecs_status, $this->finance);
			if ($res_ecs_status && mysql_num_rows($res_ecs_status)) {
				$row_ecs_status = mysql_fetch_assoc($res_ecs_status);
				$ecs_edit = false;
			} else {
				$get_si_status = "SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='" . $this->parentid . "' and deactiveflag = 0 and ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1 ";
				$res_si_status = parent::execQuery($get_si_status, $this->finance);
				if ($res_si_status && mysql_num_rows($res_si_status)) {
					$row_si_status = mysql_fetch_assoc($res_si_status);
					$ecs_edit = false;
				}
			}
			$ecs_flag = 0;
			if ($ecs_edit) {
				$ecs_flag = 0;
			} else {
				$ecs_flag = 1;
			}
			while ($row = mysql_fetch_assoc($res)) {
			//$campaignid = (int)$row['campaignid'];								
				$campaignid = (int)$row['campaignid'];

				$budget_array['result']['finance']['data'][$campaignid]['campaignid'] = $campaignid;
				$budget_array['result']['finance']['data'][$campaignid]['budget'] = floatval($row['budget']);
				$budget_array['result']['finance']['data'][$campaignid]['tenure'] = intval($row['duration']);
				$budget_array['result']['finance']['data'][$campaignid]['balance'] = floatval($row['balance']);
				$budget_array['result']['finance']['data'][$campaignid]['version'] = floatval($row['version']);
				$budget_array['result']['finance']['data'][$campaignid]['bid_perday'] = floatval($row['bid_perday']);
				$budget_array['result']['finance']['data'][$campaignid]['remaining_tenure'] = floatval($row['remaining_tenure']);
				$budget_array['result']['finance']['data'][$campaignid]['expired'] = $row['expired'];
				$budget_array['result']['finance']['data'][$campaignid]['ecs_flag'] = $ecs_flag;
			
			
			/*
			$budget_array['result']['finance']['data']['campaignid']  = $campaignid;
			$budget_array['result']['finance']['data']['budget'] 	 = floatval($row['budget']);
			$budget_array['result']['finance']['data']['tenure'] 	 = intval($row['duration']);
			$budget_array['result']['finance']['data']['balance'] 	 = floatval($row['balance']);
			$budget_array['result']['finance']['data']['version'] 	 = floatval($row['version']);
			$budget_array['result']['finance']['data']['bid_perday']  = floatval($row['bid_perday']);
			$budget_array['result']['finance']['data']['remaining_tenure']  = floatval($row['remaining_tenure']);
			$budget_array['result']['finance']['data']['expired'] 	 = $row['expired'];
				 */
				$finance_live_total_budget += floatval($row['budget']);

			}
		}




		$sql = "select campaignid,catid,pincode,callcount,bidvalue,actual_budget,inventory,version,position_flag from tbl_bidding_details_shadow where parentid='" . $this->parentid . "' ";
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($res && $num > 0) {
			$datasource = "shadow";
			$cnt = 0;
			$pendingversion = 0;
			$paduration = 0;
			$pabudget = 0;
			$shadowtotalbudget = 0;

			while ($row = mysql_fetch_assoc($res)) {
			//echo '<hr>';
			//print_r($row);
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$pendingversion = $row['version'];


				$budget_array['result']['c_data'][$catid]['cnm'] = "";
			//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] 		= $row['position_flag'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['cnt_f'] = $row['callcount'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidvalue'] = $row['bidvalue'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] = $row['actual_budget'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['inv'] = $row['inventory'];
			//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['campaignid']   = (int)$row['campaignid'];
			//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['position_flag']   = (int)$row['position_flag'];
				$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['best_flg'] = (int)$row['position_flag'];

				$shadowtotalbudget += $row['actual_budget'];
				$catid_array[] = $catid;
				$pincode_array[] = $pincode;
			}

			$version = $pendingversion;
		// populating bidperday
		
		// finding tennure 


			$sql = "select campaignId,duration,budget from payment_apportioning where parentid='" . $this->parentid . "' and version ='" . $pendingversion . "' and budget-balance>1 and campaignId in (1,2) ";
			$res = parent::execQuery($sql, $this->finance);
			$num = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<hr><b>Live DB Query:</b>' . $sql;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			if ($num >= 1) {

				$pabudget = 0;

				while ($pa_row = mysql_fetch_assoc($res)) {
					$paduration = $pa_row['duration'];
					$pabudget += $pa_row['budget'];

				}

				$multiplyingfactor = $pabudget / $shadowtotalbudget;
			}

			if (DEBUG_MODE) {
				echo '<br>paduration : ' . $paduration;
				echo '<br>pabudget : ' . $pabudget;
				echo '<br>multiplyingfactor : ' . $multiplyingfactor;

			}


			foreach ($budget_array['result']['c_data'] as $catid => $catid_values) {
				if (DEBUG_MODE) {
					echo '<br>Catid : ' . $catid;
				//echo '<br>Catid Values : ';
					print_r($catid_values);
				}



				foreach ($catid_values['pin_data'] as $pincode => $pincode_values) {

					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] = $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] * $multiplyingfactor;
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidperday'] = $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] / $paduration;

					if (DEBUG_MODE) {
						//echo 'actual_budget.catid'.$catid.'--$pincode--'.$pincode. ' = '. $budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'];
					}
				}
			}

			$budget_array['result']['tb_flexi_bgt'] = $pabudget;// budget from payment apportioning table 

		} else {
			$sql = "select * from tbl_bidding_details where parentid ='" . $this->parentid . "' ORDER BY catid, pincode, position_flag";
			$res = parent::execQuery($sql, $this->finance);
			$num = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<hr><b>Live DB Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
				echo '<hr>';
			}

			if ($res && $num > 0) {
				$datasource = "live";
				$cnt = 0;
				while ($row = mysql_fetch_assoc($res)) {
				//echo '<hr>';
				//print_r($row);
					$catid = $row['catid'];
					$pincode = $row['pincode'];
					$version = $row['version'];

					$budget_array['result']['c_data'][$catid]['cnm'] = "";
				//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] 		= $row['position_flag'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['cnt_f'] = $row['callcount'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidvalue'] = $row['bidvalue'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['budget'] = $row['actual_budget'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['bidperday'] = $row['bidperday'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['inv'] = $row['inventory'];
				//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['campaignid']   = (int)$row['campaignid'];
				//$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['position_flag']   = (int)$row['position_flag'];
					$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['best_flg'] = (int)$row['position_flag'];

					$catid_array[] = $catid;
					$pincode_array[] = $pincode;
				}

				$budget_array['result']['tb_flexi_bgt'] = $finance_live_total_budget;// budget from main table 

			}

		}
	
	
	// now processing the cat pin data 

		if ($datasource == null) {
			$result_array['error']['code'] = "1";
			$result_array['error']['msg'] = "data not found in bidding details tables campaign ";
			return $result_array;
			die();
		} else {

			if (count($catid_array) > 0) {
				$pincode_array = array_unique($pincode_array);
				$pincode_list = implode(",", $pincode_array);

				$catid_array = array_unique($catid_array);
				$catid_list = implode(",", $catid_array);

				$cat_array = $this->get_category_details($catid_list);
				$pin_array = $this->get_pincode_details($pincode_list);

				$catid_added = array_diff($this->currenttempcategories, $catid_array);
				$catid_removed = array_diff($catid_array, $this->currenttempcategories);
				$newcatidList = "";

				if (count($catid_added) > 0) {
					$newcatidList = implode(",", $catid_added);
				}

			//$fixedposition_arr= $this->fn_fixedposition_bidders($catid_list,$pincode_list);
				$fixedposition_arr = $this->fn_fixedposition_bidders_wraper($version, $newcatidList);
			//print_r($fixedposition_arr);
				$payment_type_dealclosed = $this->fn_tbl_payment_type_dealclosed($version, $budget_array['result']['finance']['data']);


				foreach ($cat_array as $catid => $value_array) {
					$budget_array['result']['c_data'][$catid]['ncid'] = $value_array['nid'];
					$budget_array['result']['c_data'][$catid]['cnm'] = $value_array['cnm'];
					

				}



				$package_array = $this->fn_package_bidders(implode(",", $this->currenttempcategories), $pincode_list);

				$package_bidders = $package_array['bidder'];
				$package_bid = $package_array['bid'];
				$package_sc = $package_array['sc'];

				#$category_bidders = $package_array['cat_bidder'];
				#$category_bid = $package_array['cat_bid'];

				if (DEBUG_MODE) {
					echo '<pre>budget_array ';
					print_r($budget_array);

					echo '<pre>pin_array ';
					print_r($pin_array);

					echo '<pre>fixedposition_arr';
					print_r($fixedposition_arr);

				}

				$total_flexi_budget = 0;
				foreach ($budget_array['result']['c_data'] as $catid => $catid_values) {
					if (DEBUG_MODE) {
						echo '<br>Catid : ' . $catid;
					//echo '<br>Catid Values : ';
						print_r($catid_values);
					}


					if (!in_array($catid, $catid_removed)) // not removed 
					{

						$flexi_bgt_sum = 0;
						foreach ($catid_values['pin_data'] as $pincode => $pincode_values) {					
						
						//$flexi_bgt = round($pincode_values['best_bgt']*$flexi_factor,2);
						//$flexi_bpd = round($flexi_bgt/365,2);
						
						// for existing contracts it will be actual budget 

							$flexi_bgt = $pincode_values['budget'];
							$flexi_bpd = $pincode_values['bidperday'];

							if ($package_bid[$catid][$pincode])
								$flexi_pos = $this->closest_binary($flexi_bpd, $package_bid[$catid][$pincode]);
							else
								$flexi_pos = 1;

							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['anm'] = $pin_array[$pincode]['anm'];
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['pos'] = $fixedposition_arr['result']['c_data'][$catid]['pin_data'][$pincode]['pos'];
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bgt'] = $flexi_bgt;
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_pos'] = $flexi_pos;
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bpd'] = $flexi_bpd;
						//if($_SERVER['REMOTE_ADDR']=='')
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bidder'] = $package_bidders[$catid][$pincode];
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_bid'] = $package_bid[$catid][$pincode];
							$budget_array['result']['c_data'][$catid]['pin_data'][$pincode]['flexi_sc'] = $package_sc[$catid][$pincode];

							$total_flexi_budget += $flexi_bgt;
							$flexi_bgt_sum += $flexi_bgt;
							if (DEBUG_MODE) {
								echo '<br>Flexi';
								echo '<br>Pincode : ' . $pincode;
							//echo '<br>Pincode Values : ';
							//print_r($pincode_values);
								echo '<br>best_bgt : ' . $pincode_values['best_bgt'];
								echo '<br>best_pos : ' . $pincode_values['best_flg'];
								echo '<br>flexi_bgt : ' . $flexi_bgt;
								echo '<br>flexi_pos : ' . $flexi_pos;
							}

						}


						$budget_array['result']['reg_bgt'] = $fixedposition_arr['result']['reg_bgt'];
						$budget_array['result']['c_data'][$catid]['flexi_bgt'] = $flexi_bgt_sum;
					#$total_flexi_budget = round($total_flexi_budget);

					} else { // unset the removed category 
						unset($budget_array['result']['c_data'][$catid]);
					}

				}

				$budget_array['result']['tb_flexi_bgt'] = round($total_flexi_budget); // re assigning the tb_flexi_bgt based on the category budget 


			}
		
		
		
		// adding newly  added category node 

			if (count($catid_added) > 0) {
				$newly_added_catid_list = implode(",", $catid_added);
				$newly_added_cat_array = $this->get_category_details($newly_added_catid_list);
			//print_r($fixedposition_arr);
				foreach ($newly_added_cat_array as $ncatid => $value_array) {
					$budget_array['result']['c_data'][$ncatid]['cnm'] = $value_array['cnm'];
					$budget_array['result']['c_data'][$ncatid]['newaddedtempcat'] = '1';
					$budget_array['result']['c_data'][$ncatid]['pin_data'] = array();
					foreach ($pincode_array as $keyPin => $valuePin) {
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin] = array();
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['anm'] = $pin_array[$valuePin]['anm'];
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['pos'] = $fixedposition_arr['result']['c_data'][$ncatid]['pin_data'][$valuePin]['pos'];
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_bgt'] = 0;
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_pos'] = 101;
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_bpd'] = 0;
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_bidder'] = $package_bidders[$ncatid][$valuePin];
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_bid'] = $package_bid[$ncatid][$valuePin];
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['flexi_sc'] = $package_sc[$ncatid][$valuePin];
						$budget_array['result']['c_data'][$ncatid]['pin_data'][$valuePin]['best_flg'] = 100;
					}
					$budget_array['result']['c_data'][$ncatid]['flexi_bgt'] = '0';
					$budget_array['result']['c_data'][$ncatid]['budget'] = '0';
					$budget_array['result']['c_data'][$ncatid]['bidvalue'] = '0';
					$budget_array['result']['c_data'][$ncatid]['bidperday'] = '0';
				}
			}



			#$budget_array['result']['category_bidders'] = $category_bidders;
			#$budget_array['result']['category_bid'] = $category_bid;


			$budget_array['result']['datasource'] = $datasource;
			$budget_array['result']['currenttempcategories'] = implode(',', $this->currenttempcategories);
			$budget_array['result']['currentliveshadowcategories'] = $catid_list;

			if (count($catid_added))
				$budget_array['result']['addedtempcategories'] = implode(',', $catid_added);
			else
				$budget_array['result']['addedtempcategories'] = '';

			if (count($catid_removed))
				$budget_array['result']['removedlivecategories'] = implode(',', $catid_removed);
			else
				$budget_array['result']['removedlivecategories'] = '';


			$budget_array['result']['tbl_payment_type_dealclosed'] = $payment_type_dealclosed;
		// code goes here flexi handlig  


			$budget_array['error']['code'] = "0";
			$budget_array['error']['msg'] = "No error";
		}

		return ($budget_array);
	}

	function get_category_details($catids)
	{
		/*$sql = "select category_name, national_catid, catid, if((business_flag&1)=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract 
	from tbl_categorymaster_generalinfo where catid in (" . $catids . ") AND biddable_type=1";*/
		//$res_area = parent::execQuery($sql, $this->dbConDjds);
		$cat_params = array();
		$cat_params['page'] ='budgetinfoClass';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,category_type';

		$where_arr  	=	array();
		$where_arr['catid']			= $catids;
		$where_arr['biddable_type']	= '1';		
		$cat_params['where']	= json_encode($where_arr);
		if($catids!=''){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}	

		//$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>Category Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($cat_res_arr['errorcode']=='0'	&& count($cat_res_arr['results'])> 0) {

			foreach($cat_res_arr['results'] as $key=>$row) {
			//print_r($row);
				$catid = $row['catid'];
				$ret_array[$catid]['cnm'] = $row['category_name'];
				$ret_array[$catid]['cid'] = $row['catid'];
				$ret_array[$catid]['nid'] = $row['national_catid'];

				$business_flag = $row['business_flag'];
				$category_type = $row['category_type'];
				if($business_flag==1)
				{
					$b2b_flag = 1;
				}
				else{
					$b2b_flag = 0;
				}
				if(((int)$category_type&64)==64){
					$block_for_contract = 1;
				}
				else{
					$block_for_contract = 0;	
				}
				$ret_array[$catid]['b2b_flag'] 	= $b2b_flag;
				$ret_array[$catid]['bfc'] 		= $block_for_contract;
			}
		}
		return ($ret_array);
	}

	function get_pincode_details($pincodes)
	{
		$sql = "select pincode, substring_index(group_concat(main_area order by callcnt_perday desc SEPARATOR '#'),'#',1) as areaname
	from tbl_areamaster_consolidated_v3 where pincode in (" . $pincodes . ") group by pincode";
		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>Area Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
			//print_r($row);
				$pincode = $row['pincode'];
				$ret_array[$pincode]['pincode'] = $row['pincode'];
				$ret_array[$pincode]['anm'] = $row['areaname'];
			}
		}
		return ($ret_array);
	}


	function bidder_explode_array($bidders_string, $parentid)
	{
		$bidders_array = explode(",", $bidders_string);
		if (count($bidders_array) > 0) {
			for ($i = 0; $i < count($bidders_array); $i++) {
				unset($temp_bid);
				$temp_bid = explode("-", $bidders_array[$i]);
				$temp_bid[0] = strtoupper($temp_bid[0]);
				$temp_bid[0] = trim($temp_bid[0]);
				$bid_parentid_array[$temp_bid[0]]['bid'] = $temp_bid[1];
				$bid_parentid_array[$temp_bid[0]]['inv'] = $temp_bid[2];
				$bid_parentid_array[$temp_bid[0]]['cum'] = ($temp_bid[3] ? $temp_bid[3] : 0);
				$bid_parentid_array[$temp_bid[0]]['act_inv'] = ($temp_bid[4] ? $temp_bid[4] : 0);

				if ($parentid == $temp_bid[0]) {
					$bid_parentid_array[$temp_bid[0]]['existing'] = 1;

				} else {
					$bid_parentid_array[$temp_bid[0]]['existing'] = 0;
				}
			}
		}
		return ($bid_parentid_array);
	}


	function fn_package_bidders($catids, $pincodes)
	{
		if (DEBUG_MODE) {
			echo '<br>fn: fn_package_bidders($cat_array,$pin_array)';
			echo '<br>fn: category list :' . $catids;
			echo '<br>fn: pincode list :' . $pincodes;
		}
		$package_bidder = array();
		$package_bid_array = array();
		$sql = "select parentid, companyname, catid, pincode, round(search_contribution,2) as search_contribution, round(bidperday,2) as bidperday, 
		round(contract_bidperday,2) as contract_bidperday, physical_pincode, physical_area as area, active_date, category_count, pincode_count
		from db_iro.tbl_package_search where catid in (" . $catids . ")  and data_city='" . $this->data_city . "'  and pincode in (" . $pincodes . ") order by catid, pincode, bidperday desc";
		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>PS Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
				//print_r($row);
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$parentid = $row['parentid'];

				$data['p'] = $row['parentid'];
				$data['c'] = $row['companyname'];
				$data['sc'] = $row['search_contribution'];
				$data['bpd'] = $row['bidperday'];
				$data['c_bpd'] = $row['contract_bidperday'];
				$data['p_p'] = $row['physical_pincode'];
				$data['p_a'] = $row['area'];
				$data['c_c'] = $row['category_count'];
				$data['p_c'] = $row['pincode_count'];
				$data['a_d'] = $row['active_date'];

				$package_bidder[$catid][$pincode][] = $data;
				$package_bid_array[$catid][$pincode][] = $row['bidperday'];
				$package_sc_array[$catid][$pincode][] = $row['search_contribution'];
			}
		}

		/*
		$sql = "select parentid, group_concat(distinct companyname) as companyname, catid, physical_pincode, cat_bidperday, contract_bidperday, group_concat(distinct source) as source, pincode_count, category_count from (
					select parentid, '' as companyname, catid, physical_pincode, round(category_perday,2) as cat_bidperday, round(contract_bidperday,2) as contract_bidperday,'fp' as source, pincode_count, category_count from db_iro.tbl_fp_search where catid in (" . $catids . ") and data_city='" . $this->data_city . "' group by parentid, catid 
				union 
					select parentid, companyname, catid, physical_pincode, round(category_perday,2) as cat_bidperday, round(contract_bidperday,2) as contract_bidperday,'package' as source, pincode_count, category_count from db_iro.tbl_package_search where catid in (" . $catids . ")  and data_city='" . $this->data_city . "'  group by parentid, catid )t 
				group by 
					parentid, catid 
				order by 
					catid, cat_bidperday desc";

		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>PS Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
				$catid = $row['catid'];
				$pincode = '999999';
				$parentid = $row['parentid'];

				$data['p'] = $row['parentid'];
				$data['c'] = $row['companyname'];
				$data['cat_bpd'] = $row['cat_bidperday'];
				$data['c_bpd'] = $row['contract_bidperday'];
				$data['p_p'] = $row['physical_pincode'];
				$data['p_a'] = $row['area'];
				$data['c_c'] = $row['category_count'];
				$data['p_c'] = $row['pincode_count'];
				$data['a_d'] = $row['active_date'];

				$category_bidder[$catid][$pincode][] = $data;
				$category_bid_array[$catid][$pincode][] = $row['cat_bidperday'];
			}
		}
		*/

		$return_array['bidder'] = $package_bidder;
		$return_array['bid'] = $package_bid_array;
		$return_array['sc'] = $package_sc_array;
		#$return_array['cat_bidder'] = $category_bidder;
		#$return_array['cat_bid'] = $category_bid_array;

		return ($return_array);
	}


	function fn_fixedposition_bidders($catid_list, $pincode_list)
	{
/*
$fixedposition_arr= array();

//$sql="select * from tbl_fixedposition_factor where position_flag!=100 and active_flag=1";
$sql="select * from tbl_fixedposition_factor where active_flag=1";
$res 	= parent::execQuery($sql, $this->dbConbudget);
$num_rows		= mysql_num_rows($res);

if(DEBUG_MODE)
{
	echo '<br><b>FP factor Query:</b>'.$sql;
	echo '<br><b>Result Set:</b>'.$res;
	echo '<br><b>Num Rows:</b>'.$num_rows;
	echo '<br><b>Error:</b>'.$this->mysql_error;
}

if($res && $num_rows > 0)
{
	
	while($row=mysql_fetch_assoc($res))
	{
		
		//if(DEBUG_MODE)
		//{
		//	echo '<hr>';
		//	print_r($row);
		//}
		$pos 		= $row['position_flag'];
		$factor 	= $row['positionfactor'];
		$x_factor 	= $row['exclusive_positionfactor'];
		$f_factor 	= $row['final_positionfactor'];
		
		if($row['active_flag']==1)
		{
			$f_position_factor[$pos] 		= $f_factor;				
		}
		
		if($f_factor>0)
			$r_position_factor[$pos] = (1/$f_factor);
		else
			$r_position_factor[$pos] = 0;
	}
}

############ FP table ##############
$position_factor = $f_position_factor;

$sql="select * from tbl_fixedposition_pincodewise_bid where catid in (".$catid_list.") and pincode in (".$pincode_list.") ORDER BY catid, pincode";
$res 	= parent::execQuery($sql, $this->dbConbudget);
$num_rows		= mysql_num_rows($res);

if(DEBUG_MODE)
{
	echo '<br><b>FP bid Query:</b>'.$sql;
	echo '<br><b>Result Set:</b>'.$res;
	echo '<br><b>Num Rows:</b>'.$num_rows;
	echo '<br><b>Error:</b>'.$this->mysql_error;
}

if($res && $num_rows > 0)
{		
	
	while($row=mysql_fetch_assoc($res))
	{				
		if(DEBUG_MODE)
		{
			//echo '<hr>';
			//print_r($row);
		}
		
		$catid		=$row['catid'];
		$pincode		=$row['pincode'];
		//$budget_array[$catid]['pin_data'][$pincode]['anm']	= $pin_array[$pincode]['anm'];

		foreach($position_factor as $pos => $pos_factor)
		{
			$is_bidder = 0;
			$inv_booked  = $row["pos".$pos."_inventory_booked"];
			$bidder 	 = $row["pos".$pos."_bidder"];
			//$inv_avail   = (1-$inv_booked);
			$inv_avail   = round((1-$inv_booked),2);
			$total_inv_booked += $inv_booked;						
			$bidder_array = array();
			$bid_res	  = array();
			
			//if($this->parentid && (stripos($bidder,$this->parentid.'-') !== false))
			if($this->parentid && $bidder)
			{
				$bid_res =  $this->bidder_explode_array($bidder, $this->parentid);
				
				
				if($bidder_array[$this->parentid]['existing'] ==1)
				{ 
					$is_bidder = 1;
					$inv_avail += $bidder_array[$this->parentid]['inv'];
				}
			}else
			{
				$above_50_counter = 0;
			}
			
			$bidvalue = 84 * $pos_factor;
			
			
			$fixedposition_arr[$catid][$pincode]['pos'][$pos]['inv_booked'] = $inv_booked;
			$fixedposition_arr[$catid][$pincode]['pos'][$pos]['inv_avail'] = $inv_avail;
			$fixedposition_arr[$catid][$pincode]['pos'][$pos]['bidder']     = $bidder;
			$fixedposition_arr[$catid][$pincode]['pos'][$pos]['bidvalue']     = $bidvalue;
			
			
			############## custom position handling #################
			
		
			if(DEBUG_MODE)
			{
				//echo '<br> fixedposition_arr'; print_r($fixedposition_arr);
				
			}
		
		}
	
	}
}

return $fixedposition_arr;
		 */
	}

	function fn_fixedposition_bidders_wraper($version, $newCatidList)
	{
		$BdgtCallparams = $this->getBudgetCalculationParams($version);
		$newCatidList = $newCatidList;
	// Budget calculation object creation and calling
		$budgetDetailsClass_obj = new budgetDetailsClass($BdgtCallparams);
		$result = $budgetDetailsClass_obj->getBudget($newCatidList);

		if (DEBUG_MODE) {
			echo '<br><b>new catid list</b> <br>';
			print_r($newCatidList); // uncomment		
			echo '<br><b>budgetDetailsAPIresult</b> <br>';
			print_r($result); // uncomment		
		}	
	
	/*
	if(is_array($result['error']) && count($result['error']['code'])>0)
	{
		$result['result'] = array();
		$result['error']['code'] = 1;
		$result['error']['msg'] = "Entry found on Budget API - ".$result['error']['msg'];
		$resultstr= json_encode($result);
		print($resultstr);
		die;
	}
		 */

		return $result;

	}
	
	function tbl_payment_type_master($key_words)
	{
		$key_wordsstr = str_replace(",","','",$key_words);
		
		$sql= "select group_concat(campaign_name) as campaign_name from tbl_payment_type_master where key_words in ('".$key_wordsstr."') ";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($num > 0) {
			$resarr= mysql_fetch_assoc($res);			
			$result['campaign_name'] = $resarr['campaign_name'];
		}
		
		return $result;
	}
	
	function fn_tbl_payment_type_dealclosed($version, $financedata,$paymenttypename=0)
	{
		$result = array();
		
		$sql = 	"select ifnull(payment_type ,'') as payment_type ,ifnull(payment_type_flag,'') as payment_type_flag from (
			select group_concat(payment_type separator ',') as payment_type , group_concat(payment_type_flag separator ',') as payment_type_flag from tbl_payment_type_dealclosed where parentid='" . $this->parentid . "' 
			)a;";

		#$sql = "select group_concat(payment_type separator ',') as payment_type , group_concat(payment_type_flag separator ',') as payment_type_flag from tbl_payment_type_dealclosed where parentid='" . $this->parentid . "' ";

		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($num > 0) {
			$resarr= mysql_fetch_assoc($res);			
			$result['payment_type'] = $resarr['payment_type'];
			$result['payment_type_flag'] = $resarr['payment_type_flag'];
			
			if($paymenttypename)
			{
				$tblpaymenttypemaster = $this->tbl_payment_type_master($resarr['payment_type']);
				$result['campaign_name'] = $tblpaymenttypemaster['campaign_name'];				
			}
			
			
		}else {
				foreach ($financedata as $campaignidval => $campaignidarray) {
					if ($campaignidval == 2) {
						$result['payment_type'] = 'PDG';
						$result['payment_type_flag'] = '1';
					} elseif ($campaignidval == 1 && count($result) == 0) {
						$result['payment_type'] = 'package';
						$result['payment_type_flag'] = '1';
					}
				}

			}
		
		return $result;
		
		/*
		$sql = "select payment_type,payment_type_flag from tbl_payment_type_dealclosed where parentid ='" . $this->parentid . "' and version='" . $version . "' and (payment_type!='' and payment_type is not null) ";

		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($num > 0) {
			$result = mysql_fetch_assoc($res);

		} else {
			$sql = "select version from payment_version_approval where parentid ='" . $this->parentid . "'  order by approval_date desc limit 1";
			$res = parent::execQuery($sql, $this->finance);
			$num = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<hr><b>Live DB Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
				echo '<hr>';
			}

			if ($num > 0) {
				$row = mysql_fetch_assoc($res);

				$sql = "select payment_type,payment_type_flag from tbl_payment_type_dealclosed where parentid ='" . $this->parentid . "' and version='" . $row['version'] . "' ";

				$res = parent::execQuery($sql, $this->finance);
				$num = mysql_num_rows($res);

				if (DEBUG_MODE) {
					echo '<hr><b>Live DB Query:</b>' . $sql;
					echo '<br><b>Result Set:</b>' . $res;
					echo '<br><b>Num Rows:</b>' . $num;
					echo '<br><b>Error:</b>' . $this->mysql_error;
					echo '<hr>';
				}

				if ($num > 0) {
					$result = mysql_fetch_assoc($res);
				} else {

					$sql = "select version from payment_instrument_summary pis join payment_clearance_details pcd using (instrumentid) where  parentid ='" . $this->parentid . "'
							and instrumentAmount!=1 order by finalApprovalDate desc limit 1";
					$res = parent::execQuery($sql, $this->finance);
					$num = mysql_num_rows($res);

					if (DEBUG_MODE) {
						echo '<hr><b>Live DB Query:</b>' . $sql;
						echo '<br><b>Result Set:</b>' . $res;
						echo '<br><b>Num Rows:</b>' . $num;
						echo '<br><b>Error:</b>' . $this->mysql_error;
						echo '<hr>';
					}

					if ($num > 0) {
						$row = mysql_fetch_assoc($res);

						$sql = "select payment_type,payment_type_flag from tbl_payment_type_dealclosed where parentid ='" . $this->parentid . "' and version='" . $row['version'] . "' ";

						$res = parent::execQuery($sql, $this->finance);
						$num = mysql_num_rows($res);

						if (DEBUG_MODE) {
							echo '<hr><b>Live DB Query:</b>' . $sql;
							echo '<br><b>Result Set:</b>' . $res;
							echo '<br><b>Num Rows:</b>' . $num;
							echo '<br><b>Error:</b>' . $this->mysql_error;
							echo '<hr>';
						}

						if ($num) {
							$result = mysql_fetch_assoc($res);
						} else {
							foreach ($financedata as $campaignidval => $campaignidarray) {
								if ($campaignidval == 2) {
									$result['payment_type'] = 'PDG';
									$result['payment_type_flag'] = '1';
								} elseif ($campaignidval == 1 && count($result) == 0) {
									$result['payment_type'] = 'package';
									$result['payment_type_flag'] = '1';
								}
							}

						}

					}
				}

			}
		}


		return $result;
		*/

	}



	function getBudgetCalculationParams($version)
	{
		$BdgtCallparams['data_city'] = $this->params['data_city'];
		$BdgtCallparams['parentid'] = $this->params['parentid'];
		$BdgtCallparams['version'] = $version;
		$BdgtCallparams['tenure'] = 12;
		$BdgtCallparams['mode'] = 1; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		$BdgtCallparams['option'] = 1; // default 1, max 7


		if (DEBUG_MODE) {
			echo '<br><b>BdgtCallparams</b> <br>';
			print_r($BdgtCallparams); // uncomment
		}
		return $BdgtCallparams;

	}

	function closest_binary($v, array $vs)
	{
	// Handle lowest value.
		if (min($vs) >= $v) {
			return count($vs) + 1;
		}

	// Handle beyond highest value.
		if (max($vs) < $v) {
			return 1;
		}

		if (count($vs) === 0) return 1;
		$left = 0;
		$right = count($vs) - 1;

		while (($left + 1) < $right) {
			$mid = ceil($left + ($right - $left) / 2);
		// d($left . '---' . $right);
		// d("middle : " . $mid . " -> " . $vs[$mid]);
			if ($v > $vs[$mid]) {
				$right = $mid;
			} else {
				$left = $mid;
			}
		}
		return $left + 2;
	}

}



?>
