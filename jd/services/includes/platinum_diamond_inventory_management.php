<?php
/***********************************************************************************************************************
*
*	function created by sunny on 21 March to handle following things :-
*	a)blocking of inventory by passing input status as 1 but not 2
*	b)blocking of inventory and activation of same contract by setting its ddg cumulative value
*	//http://172.29.0.197:2227/search_function/platinum_diamond_inventory_management.php
************************************************************************************************************************
*
*	These Implementation done by Praful on 1'st September 2011.
*	Modified Inventory Management file and uses GLOBAL DATABASE connection file,
*	Query Log and two different 	Text logging started,
*   Inventory logging before and after update query on ...pincodewise_bid table.
*	1 - blocking 
*	2 - blocking n making live 
*	3 - checking Inventory 
*	4 - check and give position - old logic inventory available (New)
*	5 - check and give position - best Budget deal (New)
*	6 - check and give position - best Budget deal (Another New Logic - without gold position)
************************************************************************************************************************/
set_time_limit(0);

class inventory_booking_management
{    
	private $conn_fnc;
	private $conn_iro;
	private $local;
	
	public $cat_avgcallcnt;
	
	const POS_D  = '15';/*declare constant for platinum */
	
	const POS_DG = '10';/*declare constant for diamond*/
	
	const POS_G = '8';/*declare constant for gold*/
	
	const POS_AP = '5';/*declare constant for package*/
	
	const NONB2B_MIN_BID_VAL = '5';/*minimum bid value for non b2b category*/
	
	const D_B2B_MIN_BID_VAL = '100';/*minimum platinum bid value for b2b category*/
	
	const DDG_B2B_MIN_BID_VAL = '75';/*minimum diamond bid value for b2b category*/
	
	const G_B2B_MIN_BID_VAL = '70';/*minimum Gold bid value for b2b category*/	
	
	const MIN_CALLCNT = '1';/*minimum callcount*/ 
	
	const MIN_AVG_FACTOR = '1';/*minimum callcount*/ 
	
	const MAX_AVG_FACTOR = '3';/*maximum factor for matching contracts*/ 
	
	const MIN_INVENTORY = '0.05';/*minimum inventory to be provided*/
	
	const MAX_INVENTORY = '1';/*minimum inventory to be provided*/
	
	const MULTIPLIER = '5';/*multiplier in order to check inventory in the multiple of 5%*/	
	
	const PACK_MULTIPLIER = '1.15';/*multiplier used on average bid value of package*/
	
	const MIN_PACK_BIDPERDAY  = '0.40';/*minimum bid per day of category*/
	
	const PLAT_EXCL_FACTOR = '2.5';/*increment factor for exclusive category*/

	public function __construct($dbarr)
	{		
				
		$this -> conn_fnc	= new DB($dbarr['DB_FNC'],1);												/* connection object to finance server	*/
		$this -> conn_iro 	= new DB($dbarr['DB_IRO'],1);													/* connection object to de/cs server		*/
		$this->cat_avgcallcnt = 0;
	}

	/*****************************************************************************************************************************************************************
	*	P1233048050X9Q1H5-23.62000-0.25000,PXX22.XX22.110124023806.V1Q2-31.92000-0.5,PXX22.XX22.110131113216.K5S1-31.92000-0.1,PXX22.XX22.110207120557.Z1V4-31.92000-0.15
	*	P1233048050X9Q1H5-23.62000-0.25000-0.50
	*	parentid - bid value - inventory - cummulative 
	*****************************************************************************************************************************************************************/
	
	function bidder_explode_array($bidders_string,$parentid)
	{
		$bidders_array = explode(",",$bidders_string);
		if(count($bidders_array)>0)
		{
			for($i=0;$i<count($bidders_array);$i++)
			{
				unset($temp_bid);
				$temp_bid = explode("-",$bidders_array[$i]);
				$temp_bid[0] = strtoupper($temp_bid[0]);
				$temp_bid[0] = trim($temp_bid[0]);
				$bid_parentid_array[$temp_bid[0]]['bid']     = $temp_bid[1];
				$bid_parentid_array[$temp_bid[0]]['inv']     = $temp_bid[2];
				$bid_parentid_array[$temp_bid[0]]['cum']     = ($temp_bid[3]?$temp_bid[3]:0);
				$bid_parentid_array[$temp_bid[0]]['act_inv'] = ($temp_bid[4]?$temp_bid[4]:0);
				
				if($parentid == $temp_bid[0])
				{
					$bid_parentid_array[$temp_bid[0]]['existing'] = 1;
					
				}
				else
				{
					$bid_parentid_array[$temp_bid[0]]['existing'] = 0;
				}
			}
		}
		return($bid_parentid_array);
	}


	// functionality - cum value calc, min inventory managemnet and show bidders string manipulation
	function bidder_implode_string($bidder_array,$ip_parentid,$catid,$pincode,$position_flag,$status_flag, $tbl_bidcatdetails_ddg,$tbl_compcatarea_ddg,$actual_inventory)
	{
		$cummlative 		= 0;
		$min_inventory	= 1;

		foreach($bidder_array as $parentid=>$attributes)
		{		
			if($attributes['inv'] != 0)
			{	
				$counter += 1;
				$min_inventory = min($min_inventory,$attributes['inv']);
				
				if(!($attributes['cum'] == ($cummlative+$attributes['inv'])))
				{
					$attributes['cum'] = $cummlative+$attributes['inv'];
					if($status_flag == 2)
					{
						$sql_bcd_ddg = "update ".$tbl_bidcatdetails_ddg." set partial_ddg_ratio_cum = '".$attributes['cum']."' where parentid='".$parentid."' and bid_catid = '".$catid."' and pincode='".$pincode."' and position_flag='".$position_flag."'";
						$res_bcd_ddg = $this -> conn_fnc->query_sql($sql_bcd_ddg);
						//$sql_compcat_ddg = "update ".$tbl_compcatarea_ddg." set partial_ddg_ratio_cum = '".$attributes['cum']."' where parentid='".$parentid."' and bid_catid = '".$catid."' and pincode='".$pincode."' and position_flag='".$position_flag."'";
						//$res_select =  $this -> conn_iro->query_sql($sql_compcat_ddg);
						//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_bcd_ddg);
						//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_compcat_ddg);
					}
				}
				$cummlative += $attributes['inv'];
				$pids[] = $parentid."-".$attributes['bid']."-".$attributes['inv']."-".$attributes['cum']."-".$attributes['act_inv'];				
			}
		}
		
		if($status_flag == 2)
		{	
			switch($position_flag)
			{
				case self::POS_D :
							$column_pincodewise_bid = "platinum_inc";
							$column_value_search = "d_inc";
							$column_contribution = "platinum_contribution";
							$column_cum_ratio = "platinum_cumulative_ddg_ratio";

							break;
				case self::POS_DG:
							$column_pincodewise_bid = "diamond_inc";
							$column_value_search = "dg_inc";
							$column_contribution = "diamond_contribution";
							$column_cum_ratio = "diamond_cumulative_ddg_ratio";

							break;
				case self::POS_G:
							$column_pincodewise_bid = "bronze_inc";
							$column_value_search = "br_inc";
							$column_contribution = "bronze_contribution";
							$column_cum_ratio = "bronze_cumulative_ddg_ratio";							
							break;
				default :
					die("<H1>Invalid Position flag(updation on cumulative) </H1>");
			}
			$update_min_inv = "update tbl_platinum_diamond_pincodewise_bid set ".$column_pincodewise_bid."='".$min_inventory."' where  catid = '".$catid."' and pincode='".$pincode."'";
			$res_min_inv =  $this -> conn_fnc->query_sql($update_min_inv);

			//$update_min_inv_srch = "update tbl_ddg_value_search_pin set ".$column_value_search."='".$min_inventory."' where  catid = '".$catid."' and pincode='".$pincode."'";
			//$res_min_inv_srch =  $this -> conn_iro->query_sql($update_min_inv_srch);

			//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$update_min_inv);
			//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$update_min_inv_srch);	

			
			if((1 - $actual_inventory) <= 0)
			{
					//$sql_compcat_ddg = "DELETE FROM ".$tbl_compcatarea_ddg."  where parentid='PFREEINVENTORY' and bid_catid = '".$catid."' and pincode='".$pincode."' and position_flag='".$position_flag."'";
					//$res_compcat_ddg =  $this -> conn_iro->query_sql($sql_compcat_ddg);

					$sql_clients_ddg = "UPDATE  tbl_clients_ddg_contribution SET  ".$column_cum_ratio." = 0, ".$column_contribution." = 0 where parentid='PFREEINVENTORY' and bid_catid = '".$catid."' and pincode='".$pincode."'";
					$res_clients_ddg =  $this -> conn_fnc->query_sql($sql_clients_ddg);
					//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_compcat_ddg);	
					//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_clients_ddg);	
			}			
				
		}
		
		if($pids)
		$bidders_string = implode(",",$pids);
				
		return $bidders_string;
	}


	/***************************************************************************************************	
	*	ip_status - booking - 1, activating blocking -2 (readjustment - approval - expiry)
	*	booking - inventory blocking , cumm - will be given for parent in question 
	*	blocking/live - inventory blocking, cumm - calc for zip parentid
	***************************************************************************************************/
	
	function inventory_management($ip_parentid, $ip_catid, $ip_pincode, $ip_position_flag, $ip_inventory_asked, $ip_status,$tbl_bidcatdetails_ddg,$tbl_compcatarea_ddg, $ip_bid_value=0,$forceflag=0,$b2b_flag=0,$totpincodes=0,$ucode=0,$version=0,$fnc=0,$fnc_date='')
	{	
		$this->getCatPincodeDetails($ip_catid,$ip_pincode,$ip_parentid,date('H:i:s'),date('Y-m-d'),$flag=0,$ip_status,$ip_position_flag,$ip_inventory_asked);
		
		$counter 		= 0;
		$ip_parentid 	= strtoupper($ip_parentid);
		$sql_select 	= "  SELECT catid,pincode,callcnt,platinum_value,platinum_bidder,platinum_inventory,platinum_inventory_actual,diamond_value,diamond_bidder,diamond_inventory,diamond_inventory_actual,lead_value,toplead_value, toplead_bidder, bronze_value,bronze_bidder,bronze_inventory,bronze_inventory_actual FROM tbl_platinum_diamond_pincodewise_bid where catid='".$ip_catid."' and pincode='".$ip_pincode."' ";		
		$res_select 	= $this -> conn_fnc->query_sql($sql_select);
		
		if($res_select && mysql_num_rows($res_select) > 0)
		{	
			unset($bidder_array);
			$row_select 			= mysql_fetch_assoc($res_select);			 
			$platinum_bidder 		= $row_select['platinum_bidder']; 
			$platinum_inventory 	= $row_select['platinum_inventory']; 
			$plat_actual_inv		= $row_select['platinum_inventory_actual']; 
		    $diamond_bidder 		= $row_select['diamond_bidder']; 
			$diamond_inventory 		= $row_select['diamond_inventory']; 
			$diam_actual_inv		= $row_select['diamond_inventory_actual']; 
			$min_inventory_platinum	= $row_select['min_inventory_platinum']; 
			$min_inventory_diamond	= $row_select['min_inventory_diamond']; 
			$platinum_value 		= $row_select['platinum_value'];
			$diamond_value 			= $row_select['diamond_value'];			
			$bronze_bidder 			= $row_select['bronze_bidder']; 
			$bronze_inventory 		= $row_select['bronze_inventory']; 
			$gold_actual_inv		= $row_select['bronze_inventory_actual']; 
			$bronze_value 			= $row_select['bronze_value'];
			$callcnt 				= $row['callcnt'];
			$delta_inventory 		= 0;
			$inventory_arr          = array();
			
			if($ip_position_flag>0)
			{
				switch($ip_position_flag)
				{
					case self::POS_D :
					     $bidders 			= $platinum_bidder;
						 $total_inventory 	= $platinum_inventory;
						 $actual_inventory	= $plat_actual_inv;
						 $bid_value 		= $ip_bid_value;
						 break;
					case self::POS_DG:
						 $bidders 			= $diamond_bidder;
						 $total_inventory 	= $diamond_inventory;
						 $actual_inventory	= $diam_actual_inv;
						 $bid_value 		= $ip_bid_value;
						 break;
					case self::POS_G:
						 $bidders 			= $bronze_bidder;
					     $total_inventory 	= $bronze_inventory;
					     $actual_inventory	= $gold_actual_inv;
						 $bid_value 		= $ip_bid_value;
						 break;
					
					default :
						die("<H1>Invalid Position flag for :<H1><BR> parentid - $ip_parentid,<BR> catid - $ip_catid,<BR> pincode - $ip_pincode,<BR> position flag - $ip_position_flag,<BR> inventory asked - $ip_inventory_asked");
				}			
				
			}
			else
			{
				return false;
			}
			
		$bidder_array = $this -> bidder_explode_array($bidders,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
		
			
		if($ip_inventory_asked>0)
		{
						
			if($ip_status == 3)/*check available inventory for a particular parentid of passed pincode*/
			{
				
				$total_avail_inv = $this -> getBookedInventory($bidder_array,$ip_inventory_asked,$total_inventory,$ip_parentid);
				return $total_avail_inv;
			}
		}
			
			if($bidder_array[$ip_parentid]['existing'] == 1)
			{
				$delta_inventory = $ip_inventory_asked-$bidder_array[$ip_parentid]['inv'];
				$actual_delta_inventory = $ip_inventory_asked-$bidder_array[$ip_parentid]['act_inv'];
				if( ($total_inventory+$delta_inventory) <= 1 || ($forceflag == 1) || ($delta_inventory<=0))
				{
					// 	block inventory	
					if($ip_status==2)
					{
						/*existing user inventory downgrade/upgrade during approval/balance readjustment';*/
						$total_inventory 						= max(($total_inventory + $delta_inventory),0);
						$actual_inventory						= max(($actual_inventory + $actual_delta_inventory),0);
						$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
						$bidder_array[$ip_parentid]['inv'] 		= $ip_inventory_asked;
						$bidder_array[$ip_parentid]['act_inv'] 	= $ip_inventory_asked;
						$bidder_array[$ip_parentid]['cum'] 		= 0;
					}
					elseif($delta_inventory < 0 )
					{
						// during degradation booking cant release inventory unless financial transaction happens
						$total_inventory 						= $total_inventory;
						$actual_inventory						= $actual_inventory;
						$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
						$bidder_array[$ip_parentid]['inv'] 		= $bidder_array[$ip_parentid]['inv'];
						$bidder_array[$ip_parentid]['act_inv'] 	= $bidder_array[$ip_parentid]['act_inv'];
						$bidder_array[$ip_parentid]['cum']		= 0;
					}
					elseif($delta_inventory > 0 )
					{
						// during upgration booking cant release inventory unless financial transaction happens
						$total_inventory 						= max(($total_inventory + $delta_inventory),0);
						$actual_inventory						= $actual_inventory;
						$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
						$bidder_array[$ip_parentid]['inv'] 		= $ip_inventory_asked;
						$bidder_array[$ip_parentid]['act_inv'] 	= $bidder_array[$ip_parentid]['act_inv'];
						$bidder_array[$ip_parentid]['cum'] 		= 0;
					}
				}
				else
				{
					return 0; // overbooking
				}
			}
			else
			{
				if( $total_inventory + $ip_inventory_asked <=1)
				{
					if($ip_status == 2)					
					{
						$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
						$actual_inventory						= max(($actual_inventory + $ip_inventory_asked),0);
						$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
						$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
						$bidder_array[$ip_parentid]['act_inv'] 	= $ip_inventory_asked;
						$bidder_array[$ip_parentid]['cum'] 		= 0;
					}
					else
					{
						$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
						$actual_inventory						= $actual_inventory;
						$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
						$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
						$bidder_array[$ip_parentid]['act_inv'] 	= 0;
						$bidder_array[$ip_parentid]['cum'] 		= 0;
					}
				}
				else
				{
					return 0; // overbooking
				}
			}

			$counter += 1;	
			
			$bidders_request =$this -> bidder_implode_string($bidder_array, $ip_parentid, $ip_catid, $ip_pincode, $ip_position_flag,$ip_status,$tbl_bidcatdetails_ddg,$tbl_compcatarea_ddg,$actual_inventory);
			
			if($ip_position_flag>0)
			{
				switch($ip_position_flag)
				{
					case self::POS_D :
					     $bidders_request	= "platinum_bidder = '".$bidders_request."'";
						 $total_inventory 	= "platinum_inventory='".$total_inventory."'";
						 $actual_inventory	= "platinum_inventory_actual='".$actual_inventory."'";
						 break;

					case self::POS_DG:
						 $bidders_request	= "diamond_bidder = '".$bidders_request."'";
						 $total_inventory 	= "diamond_inventory='".$total_inventory."'";
						 $actual_inventory	= "diamond_inventory_actual='".$actual_inventory."'";
						 break;

					case self::POS_G:
						 $bidders_request	= "bronze_bidder = '".$bidders_request."'";
						 $total_inventory 	= "bronze_inventory='".$total_inventory."'";
						 $actual_inventory	= "bronze_inventory_actual='".$actual_inventory."'";
						 break;

					default :
						die("<H1>Invalid Position flag(updating bidder) </H1>");
				}			
								
				//$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),$flag=0, $ip_status,$ip_position_flag);				
				$sql_update_booking = "UPDATE tbl_platinum_diamond_pincodewise_bid SET 
												".$bidders_request.",
												".$total_inventory.",
												".$actual_inventory." 
										WHERE catid='".$ip_catid."' AND pincode='".$ip_pincode."' ";
				$res_select = $this -> conn_fnc->query_sql($sql_update_booking);
				$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),1, $ip_status,$ip_position_flag,$ip_inventory_asked);				
				$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_update_booking);
				if((1 - $actual_inventory<=0 ) && ($ip_status == 2))
				{
						//$sql_compcat_ddg = "DELETE FROM ".$tbl_compcatarea_ddg."  where parentid='PFREEINVENTORY' and bid_catid = '".$ip_catid."' and pincode='".$ip_pincode."' and position_flag='".$ip_position_flag."'";
						//$res_compcat_ddg =  $this -> conn_iro->query_sql($sql_compcat_ddg);

						$sql_clients_ddg = "UPDATE  tbl_clients_ddg_contribution SET  ".$column_cum_ratio." = 0, ".$column_contribution." = 0 where parentid='PFREEINVENTORY' and bid_catid = '".$ip_catid."' and pincode='".$ip_pincode."'";
						$res_clients_ddg =  $this -> conn_fnc->query_sql($sql_clients_ddg);
				}
				//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_compcat_ddg);	
				//$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_clients_ddg);	

				
			}

				
			if($ip_status == 1)			
			{				
				$contractid 	= 'D'.substr($ip_parentid,1);
				$ucode 			= $_SESSION['ucode'];
				if($fnc == 1) { 
					if($fnc_date)
					$bookedD = " ecs_booking_date = '" .$fnc_date. "',";
					else
					$bookedD = " ecs_booking_date = NOW(),";
				}
				else { 	 
					$bookedD = " booked_date = NOW(),";
				}
				
				$nationalCatid = $this->getNationalCatid($ip_catid);
				$queryInsertDealClosed = "INSERT INTO tbl_d_dg_pin_dealclosed SET
														 contractid		= '".$contractid."', 
														 bid_catid		= '".$ip_catid."',
														 national_catid = '".$nationalCatid."',
														 bid_bidamt		= '".$ip_bid_value."', 
														 pincode		= '".$ip_pincode."',
														 parentid		= '".$ip_parentid."', 
														 position_flag  = '".$ip_position_flag."',
														 partial_ddg_ratio = '". $ip_inventory_asked."', 
														 partial_ddg_ratio_cum = 0, 
														" . $bookedD . "
														 version    	= '".$version."',
														 booked_by      = '".$ucode."' 
												ON DUPLICATE KEY UPDATE 
														bid_bidamt  = '".$ip_bid_value."', 
														partial_ddg_ratio='".$ip_inventory_asked."',
														partial_ddg_ratio_cum='0',
														version    	= '".$version."',
														" . $bookedD . "
														booked_by	='".$ucode."'";
				$resBidDealClosed = $this -> conn_fnc->query_sql($queryInsertDealClosed);
	 		  	$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$queryInsertDealClosed);
			}
		}
		else/*For new category -pincode*/
		{
			unset($bidder_array);
			// inserting entries for new categories


			if($ip_status == 3)/*check available inventory for a particular parentid of passed pincode*/
			{
				return $ip_inventory_asked;
				
			}
			
			if($ip_status == 1)
			{
				$total_inventory 						= $ip_inventory_asked;
				$actual_inventory						= 0;
				$bidder_array[$ip_parentid]['bid'] 	    = $ip_bid_value;
				$bidder_array[$ip_parentid]['inv'] 		= $ip_inventory_asked;
				$bidder_array[$ip_parentid]['cum'] 	    = 0;
				$bidder_array[$ip_parentid]['act_inv'] 	= 0;
			}
			elseif($ip_status == 2)
			{
				$total_inventory 						= $ip_inventory_asked;
				$actual_inventory						= $ip_inventory_asked;
				$bidder_array[$ip_parentid]['bid'] 	    = $ip_bid_value;
				$bidder_array[$ip_parentid]['inv'] 		= $ip_inventory_asked;
				$bidder_array[$ip_parentid]['cum'] 	    = 0;
				$bidder_array[$ip_parentid]['act_inv'] 	=  $ip_inventory_asked;

			}

			$bidders_request =$this -> bidder_implode_string($bidder_array, $ip_parentid, $ip_catid, $ip_pincode, $ip_position_flag, $ip_status, $tbl_bidcatdetails_ddg, $tbl_compcatarea_ddg,$actual_inventory);
			
			if($ip_position_flag>0)
			{
				switch($ip_position_flag)
				{
					case self::POS_D :
					     $bidders_request	= "platinum_bidder = '".$bidders_request."'";
						 $total_inventory 	= "platinum_inventory='".$total_inventory."'";
 						 $actual_inventory	= "platinum_inventory_actual='".$actual_inventory."'";
						 break;

					case self::POS_DG:
						 $bidders_request	= "diamond_bidder = '".$bidders_request."'";
						 $total_inventory 	= "diamond_inventory='".$total_inventory."'";
 						 $actual_inventory	= "diamond_inventory_actual='".$actual_inventory."'";
						 break;

					case self::POS_G:
						 $bidders_request	= "bronze_bidder = '".$bidders_request."'";
						 $total_inventory 	= "bronze_inventory='".$total_inventory."'";
 						 $actual_inventory	= "bronze_inventory_actual='".$actual_inventory."'";
						 break;

					default :
						die("<H1>Invalid Position flag(updating bidder for new category-pincode) </H1>"); 
				}			
								
				//$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),$flag=0, $status_flag,$position_flag,$ip_inventory_asked);
				$nationalCatid = $this->getNationalCatid($ip_catid);
				$sql_update_booking = "INSERT INTO tbl_platinum_diamond_pincodewise_bid SET 
											national_catid = '".$nationalCatid."',
											catid='".$ip_catid."', 
											pincode='".$ip_pincode."',
											".$bidders_request.", 
											".$total_inventory.",
											".$actual_inventory;
	 		  	$res_select = $this -> conn_fnc->query_sql($sql_update_booking);
	 		  	$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_update_booking);
				$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),1, $ip_status,$ip_position_flag,$ip_inventory_asked);
		
			}
			
			if($ip_status == 1)			
			{				
				$contractid 	= 'D'.substr($ip_parentid,1);
				$ucode 			= $_SESSION['ucode'];
				if($fnc == 1) { 
					if($fnc_date)
					$bookedD = " ecs_booking_date = '" .$fnc_date. "',";
					else
					$bookedD = " ecs_booking_date = NOW(),";
				}
				else { 	 
					$bookedD = " booked_date = NOW(),";
				}
				$nationalCatid = $this->getNationalCatid($ip_catid);
				$queryInsertDealClosed = "INSERT INTO tbl_d_dg_pin_dealclosed SET
														 contractid		= '".$contractid."', 
														 bid_catid		= '".$ip_catid."',
														 national_catid = '".$nationalCatid."',
														 bid_bidamt		= '".$ip_bid_value."', 
														 pincode		= '".$ip_pincode."',
														 parentid		= '".$ip_parentid."', 
														 position_flag  = '".$ip_position_flag."',
														 partial_ddg_ratio = '". $ip_inventory_asked."', 
														 partial_ddg_ratio_cum = 0, 
														 ".$bookedD."
														 version    	= '".$version."',
														 booked_by      = '".$ucode."' 
												ON DUPLICATE KEY UPDATE 
														bid_bidamt = '".$ip_bid_value."', 
														partial_ddg_ratio='".$ip_inventory_asked."',
														partial_ddg_ratio_cum='0',
														version    	= '".$version."',
														".$bookedD."
														booked_by='".$ucode."'";
				$resBidDealClosed = $this -> conn_fnc->query_sql($queryInsertDealClosed);
	 		  	$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$queryInsertDealClosed);
			}
		}		
	}	
	
	function getAvailableInventory($bidder_array,$inventory_asked,$total_inventory,$parentid)
	{/*function to get available inventory for requested position by passing total iventory and bidders*/
		
		if($bidder_array[$parentid]['existing'] == 1)
			{
				$avail_inventory = (self :: MAX_INVENTORY - $total_inventory) + $bidder_array[$parentid]['inv'];
			}
			else
			{
				$avail_inventory = self :: MAX_INVENTORY - $total_inventory;
			}
			if($avail_inventory >= self :: MIN_INVENTORY)/*if minimum of 5%  is available*/
			{
				
				if($inventory_asked <= $avail_inventory)
				{
					return $inventory_asked;
				}
				else
				{
					$multiplier_int=(int)(self :: MULTIPLIER);
					$inventory_percentage= trim($avail_inventory) * 100;
					$average_percentage=(int)($inventory_percentage/(int)(self :: MULTIPLIER));					
					$availInv = ($multiplier_int * $average_percentage)/100;/*to get the avail inventory in the multiple of 5% (to take care of partial inven like 66% or 33%)*/
					return $availInv;
				}
			}
			else
			{
				return 0;
			}
			
	}
	
	function getBookedInventory($bidder_array,$inventory_asked,$total_inventory,$parentid)
	{/*function to get available inventory for requested position by passing total inventory and bidders*/
		
		if($bidder_array[$parentid]['existing'] == 1)
			{
				$delta_inventory = $inventory_asked-$bidder_array[$parentid]['inv'];
				if( ($total_inventory+$delta_inventory) <= 1 || ($delta_inventory<=0) )
				{
					return $inventory_asked;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				if( $total_inventory + $inventory_asked <=1)
				{	
					return $inventory_asked;
				}
				else
				{
					return 0;
				}
				
			}
	}
	function CategoryPincodes($ip_catid,$pincodes,$ip_status,$ip_parentid,$ip_inventory_asked,$ip_position_flag,$b2b_flag=0,$totpincodes=0,$category_callcnt,$top_flag,$exclusive_flag=0,$renew_budget=0,$existing_category_position_arr=array())/*function which would return category array with pincodes associated with inventory and bid values*/
	{
		if($top_flag == 0){/*if category is belongs to 95% of the categories and not from top 5% categories then check for total available inventory*/
			$ip_inventory_asked = 1;
		}
		
		if($ip_catid && $pincodes)
		{
			$tables_pin_arr = array();
			
			$sql_select 	= "SELECT catid,pincode,callcnt,platinum_value,platinum_bidder,platinum_inventory, 	diamond_value,diamond_bidder,diamond_inventory,lead_value,toplead_value, toplead_bidder, bronze_value,bronze_bidder,bronze_inventory FROM tbl_platinum_diamond_pincodewise_bid where catid='".$ip_catid."' and pincode in (".$pincodes.")";		
			$res_select 	= $this -> conn_fnc->query_sql($sql_select);
			
			if($exclusive_flag){
				$exclusive_cat_arr 		=$this->GetExclCatBidValArr($ip_catid);
			}
			
			$req_ip_position_flag   = $ip_position_flag;
			$req_ip_inventory_asked = $ip_inventory_asked;
			
			if(mysql_num_rows($res_select) > 0){
				$inventory_arr          = array();
				while($row_select = mysql_fetch_assoc($res_select)){
					unset($plat_bidder_arr);
					unset($diam_bidder_arr);
					unset($gold_bidder_arr);
					unset($pincode);
					unset($check_diam);
					unset($check_gold);
					
					$pincode 				= $row_select['pincode']; 
					$platinum_bidder 		= $row_select['platinum_bidder']; 
					$platinum_inventory 	= $row_select['platinum_inventory']; 
					$diamond_bidder 		= $row_select['diamond_bidder']; 
					$diamond_inventory 		= $row_select['diamond_inventory']; 
					$min_inventory_platinum	= $row_select['min_inventory_platinum']; 
					$min_inventory_diamond	= $row_select['min_inventory_diamond']; 
					$platinum_value 		= $row_select['platinum_value'];
					$diamond_value 			= $row_select['diamond_value'];			
					$bronze_bidder 			= $row_select['bronze_bidder']; 
					$bronze_inventory 		= $row_select['bronze_inventory']; 
					$bronze_value 			= $row_select['bronze_value'];
					$callcnt 				= $row_select['callcnt'];
					$delta_inventory 		= 0;
					$tables_pin_arr[]		= $row_select['pincode'];
					
					
					//echo 'before<br>catid :: '.$ip_catid.' -- pin :: '.$pincode.'--ip :: '.$ip_status.'--'.$ip_position_flag.' inv :: .'.$ip_inventory_asked;
					
					if($renew_budget && $existing_category_position_arr[$ip_catid][$pincode]['position'] && $existing_category_position_arr[$ip_catid][$pincode]['inventory']>0)
					{
						$ip_position_flag = $existing_category_position_arr[$ip_catid][$pincode]['position'];
						$ip_inventory_asked = $existing_category_position_arr[$ip_catid][$pincode]['inventory'];
					}
					else
					{
						$ip_position_flag   	= $req_ip_position_flag;
						$ip_inventory_asked	    = $req_ip_inventory_asked;
					}
					
					
					//echo 'after<br>catid :: '.$ip_catid.' -- pin :: '.$pincode.'--ip :: '.$ip_status.'--'.$ip_position_flag.' inv :: .'.$ip_inventory_asked;
					
					if($ip_inventory_asked>0 && $ip_position_flag>0)
					{
						$plat_bidder_arr = $this -> bidder_explode_array($platinum_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						$diam_bidder_arr = $this -> bidder_explode_array($diamond_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						$gold_bidder_arr = $this -> bidder_explode_array($bronze_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						
						if($ip_status == 4 || $ip_status == 6)/*get available inventory only for one particular position - will be used to cutomized(traditional phone search page)*/
						{
							if($ip_position_flag == self::POS_D)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] = $this -> getAvailableInventory($plat_bidder_arr,$ip_inventory_asked,$platinum_inventory,$ip_parentid);
								if($inventory_arr[$ip_catid][$pincode] [self::POS_D]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_D]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									if(!$exclusive_flag){
										$inventory_arr[$ip_catid][$pincode][self::POS_D]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
									}else{
										$inventory_arr[$ip_catid][$pincode][self::POS_D]['bid_value']=($b2b_flag) ? max(($$exclusive_cat_arr[$ip_catid][$pincode]['excl_bid']),($platinum_value* self :: PLAT_EXCL_FACTOR),(self :: D_B2B_MIN_BID_VAL* self :: PLAT_EXCL_FACTOR)) : max(($exclusive_cat_arr[$ip_catid][$pincode]['excl_bid']),($platinum_value * self :: PLAT_EXCL_FACTOR),(self :: NONB2B_MIN_BID_VAL* self :: PLAT_EXCL_FACTOR));/*setting plat bid value into bid_val property*/
									}
									
								}
								else if(!$exclusive_flag)
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_D]);
									if($ip_status == 4)
									{
										$check_diam = 1;
									}
								}
							}
							if($ip_position_flag == self::POS_DG || $check_diam>0)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv'] = $this -> getAvailableInventory($diam_bidder_arr,$ip_inventory_asked,$diamond_inventory,$ip_parentid);
								if($inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_DG]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_DG]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($diamond_value,self :: NONB2B_MIN_BID_VAL):max($diamond_value,self :: DDG_B2B_MIN_BID_VAL)) : max($diamond_value,self :: NONB2B_MIN_BID_VAL);/*setting diam bid value into array*/	
								}
								else
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_DG]);
									$check_gold = 1;
								}
							}
							if(($ip_position_flag == self::POS_G || $check_gold>0))/*keeping this condition dead for this moment- will alive in future*/
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_G]['inv'] = $this -> getAvailableInventory($gold_bidder_arr,$ip_inventory_asked,$bronze_inventory,$ip_parentid);
								if($inventory_arr[$ip_catid][$pincode][self::POS_G]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_G]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_G]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($bronze_value,self :: NONB2B_MIN_BID_VAL):max($bronze_value,self :: G_B2B_MIN_BID_VAL)) : max($bronze_value,self :: NONB2B_MIN_BID_VAL);/*setting gold bid value into array*/
									
								}
								else if(true)
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_G]);
								}
								else if(false)
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_G]);
									$inventory_arr[$ip_catid][$pincode][self::POS_AP]['inv']=$ip_inventory_asked;
									$inventory_arr[$ip_catid][$pincode][self::POS_AP]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_AP]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
									
									
									
								}
							}
								
							if($ip_position_flag == self::POS_AP)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['inv'] = 0;
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
							}
							
								
						}
						elseif($ip_status == 5 || $ip_status == 6)
						{
							unset($inv_required);
							if($ip_inventory_asked>0)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] = $this -> getAvailableInventory($plat_bidder_arr,$ip_inventory_asked,$platinum_inventory,$ip_parentid);
								if($inventory_arr[$ip_catid][$pincode][self::POS_D]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_D]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_D]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
								}
								else
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_D]);
								}
								
								$inv_required = ($inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] >0)?round(($ip_inventory_asked - $inventory_arr[$ip_catid][$pincode][self::POS_D]['inv']),5):$ip_inventory_asked;
							}
							
							if($inv_required>0)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv'] = $this -> getAvailableInventory($diam_bidder_arr,$inv_required,$diamond_inventory,$ip_parentid);
								if($inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_DG]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_DG]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($diamond_value,self :: NONB2B_MIN_BID_VAL):max($diamond_value,self :: DDG_B2B_MIN_BID_VAL)) : max($diamond_value,self :: NONB2B_MIN_BID_VAL);/*setting diam bid value into array*/	
								}
								else
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_DG]);
								}
								$inv_required = ($inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv'] >0)?round(($inv_required - $inventory_arr[$ip_catid][$pincode][self::POS_DG]['inv']),5):$inv_required;
							}
							if($inv_required>0)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_G]['inv'] = ($ip_status == 5) ? $this -> getAvailableInventory($gold_bidder_arr,$inv_required,$bronze_inventory,$ip_parentid) : 0;
								if($inventory_arr[$ip_catid][$pincode][self::POS_G]['inv']>0)
								{
									$inventory_arr[$ip_catid][$pincode][self::POS_G]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
									$inventory_arr[$ip_catid][$pincode][self::POS_G]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($bronze_value,self :: NONB2B_MIN_BID_VAL):max($bronze_value,self :: G_B2B_MIN_BID_VAL)) : max($bronze_value,self :: NONB2B_MIN_BID_VAL);/*setting gold bid value into array*/
								}
								else
								{
									unset($inventory_arr[$ip_catid][$pincode][self::POS_G]);
								}
								$inv_required = ($inventory_arr[$ip_catid][$pincode][self::POS_G]['inv'] >0)?round(($inv_required - $inventory_arr[$ip_catid][$pincode][self::POS_G]['inv']),5):$inv_required;
							}
							if($inv_required>0 && $ip_status == 5)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['inv'] = $inv_required;
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
								$inventory_arr[$ip_catid][$pincode][self::POS_AP]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
							}
						}
					}
				}
			}
			
			$main_array  = explode(',',$pincodes);
			
			if(count($main_array)>0)
			{
				$absent_pincodes = array_diff($main_array,$tables_pin_arr);				
				
				if(count($absent_pincodes)>0)
				{
					switch($ip_position_flag)
					{
						case self::POS_D :
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: D_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting plat bid value into array*/
							 break;
						case self::POS_DG:
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: DDG_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting diam bid value into array*/
							 break;
						case self::POS_G:
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: G_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting bronze  bid value into array*/
							 break;
					}
					
					foreach($absent_pincodes as $absent_pincode)
					{
						if($ip_status == 4  || $ip_status == 6)/*For customized(traditional plat/diam/gold) revert with inventory for asked position*/
						{
							if($renew_budget && $existing_category_position_arr[$ip_catid][$absent_pincode]['position'] && $existing_category_position_arr[$ip_catid][$absent_pincode]['inventory']>0)
							{
								$ip_position_flag = $existing_category_position_arr[$ip_catid][$absent_pincode]['position'];
								$ip_inventory_asked = $existing_category_position_arr[$ip_catid][$absent_pincode]['inventory'];
							}else
							{
								$ip_position_flag   	= $req_ip_position_flag;
								$ip_inventory_asked	    = $req_ip_inventory_asked;
							}
							
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['inv'] = $ip_inventory_asked;
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['callcnt']=round(((self :: MIN_CALLCNT/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['bid_value'] = $bid_value;
						}
						elseif($ip_status == 5 || $ip_status == 6)/*For best budget(plat/diam/gold) revert with platinum position*/
						{
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['inv'] = $ip_inventory_asked;
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['callcnt']=round(((self :: MIN_CALLCNT/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['bid_value'] = $bid_value;
						}
					}
				}
			}
			
			return $inventory_arr;
						
		}
	}
	
	/*function which would return package value of a category*/
	function GetCategoryPackageValue($catid,$ip_status,$parentid,$cat_type,$myzone,$data_city,$req_tenure,$call_count_array,$c2s_callcount,$camp)
	{
		if($ip_status == 6)
		{
            $catid_avg_callcnt = 0;
            if(defined("REMOTE_CITY_MODULE"))
	        {
                $sql_main = " select maincity,remotecity_strength,maincity_strength from tbl_remotecity_maincity_mapping where remotecity = '".$data_city."'";
                $res_main = $this -> conn_fnc->query_sql($sql_main);
                if($res_main && mysql_num_rows($res_main))
                {
                    $row_main = mysql_fetch_assoc($res_main);
                    $rm_main_city = $row_main['maincity'];
					$strength_ratio = ($row_main['remotecity_strength'] / $row_main['maincity_strength']);
                    $sql_remte ="select spd_city from tbl_package_perday_price_remote where catid ='".$catid."' and data_city = '".$rm_main_city."'";
                    $res_remte = $this -> conn_fnc->query_sql($sql_remte);
                    if($res_remte && mysql_num_rows($res_remte))
                    {
                        $row_remte = mysql_fetch_assoc($res_remte);
                        $spd_city = $row_remte['spd_city'];
                        $row['avg_contribution'] = $strength_ratio * $spd_city;
						
						if($catid_avg_callcnt>0){
							if($camp == 1){
								$row['avg_contribution'] = round($row['avg_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
							}else{
								$row['avg_contribution'] = round($row['avg_contribution'],4);
							}
						}else{
							$row['avg_contribution'] = round($row['avg_contribution'],4);
						}
                    }
                    else
                    {
                        $qry_getMainCity_contri = "select city_contribution,city from d_jds.tbl_business_uploadrates where city = '".$rm_main_city."'";
                        $res_getMainCity_contri = $this -> conn_iro->query_sql($qry_getMainCity_contri);
                        if($res_getMainCity_contri && mysql_num_rows($res_getMainCity_contri)>0)
                        {
                            $row_getMainCity_contri  = mysql_fetch_assoc($res_getMainCity_contri);
                            $row['avg_contribution'] = ($strength_ratio * $row_getMainCity_contri['city_contribution']);
                           
                        }
                    }
                }
                
            }
            else
            {
                if(in_array(strtoupper($cat_type),array('A','Z','NM','VNM')))/*if its area or zonal category*/
                {
                    $sql = "SELECT spd_zone as avg_contribution,spd_city AS max_contribution,cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND zoneid='".$myzone."' and data_city='".$data_city."'";
                    $res = $this -> conn_fnc->query_sql($sql);
                    if($res && mysql_num_rows($res))
                    {
                        $row = mysql_fetch_assoc($res);
                        if($row['cat_avg_callcnt']>0)
                        {
                            $catid_avg_callcnt = $row['cat_avg_callcnt'];
                        }
                        if($row['avg_contribution']>0)
                        {
							if($catid_avg_callcnt>0){
								if($camp == 1){
									$row['avg_contribution'] = round($row['avg_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
								}else{
									$row['avg_contribution'] = round($row['avg_contribution'],4);
								}
							}else{
								$row['avg_contribution'] = round($row['avg_contribution'],4);
							}
                        }
                        else
                        {
							if($catid_avg_callcnt>0){
								if($camp == 1){
									$row['avg_contribution'] = round($row['max_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
								}else
									$row['avg_contribution'] = round($row['max_contribution'],4);
							}else{
								$row['avg_contribution'] = round($row['max_contribution'],4);
							}
                        }                        
                    }
                    else
                    {
                        $new_qry = "SELECT spd_zone as avg_contribution,spd_city AS max_contribution,cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND data_city='".$data_city."' LIMIT 1";
                        $res_new_qry = $this -> conn_fnc->query_sql($new_qry);
                        if($res_new_qry && mysql_num_rows($res_new_qry)>0)
                        {
                            $row_new_qry = mysql_fetch_assoc($res_new_qry);
                            if($row_new_qry['max_contribution']>0)
                            {
								if($catid_avg_callcnt>0){
									if($camp == 1){
										$row['avg_contribution'] = round($row_new_qry['max_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
									}else{
										$row['avg_contribution'] = round($row_new_qry['max_contribution'],4);
									}
								}else{
									$row['avg_contribution'] = round($row_new_qry['max_contribution'],4);
								}
                            }
                        }
                    }
                }
                elseif(strtoupper($cat_type) == 'SZ')/*if its super zonal category*/
                {
                    $sql = "SELECT spd_superzone AS avg_contribution,spd_city AS max_contribution,cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND zoneid='".$myzone."' and data_city='".$data_city."'";
                    $res = $this -> conn_fnc->query_sql($sql);
                    if($res && mysql_num_rows($res))
                    {
                        $row = mysql_fetch_assoc($res);
                        if($row['cat_avg_callcnt']>0)
                        {
                            $catid_avg_callcnt = $row['cat_avg_callcnt'];
                        }
                        if($row['avg_contribution']>0)
                        {
                            if($catid_avg_callcnt>0){
								if($camp == 1){
									$row['avg_contribution'] = round($row['avg_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
								}else{
									$row['avg_contribution'] = round($row['avg_contribution'],4);
								}
							}else{
								$row['avg_contribution'] = round($row['avg_contribution'],4);
							}
                        }
                        else
                        {
							if($catid_avg_callcnt>0){
								if($camp == 1){
									$row['avg_contribution'] = round($row['max_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
								}else{
									$row['avg_contribution'] = round($row['max_contribution'],4);
								}
							}else{
								$row['avg_contribution'] = round($row['max_contribution'],4);
							}
                        }
                    }
                    else
                    {
                        $new_qry = "SELECT spd_superzone AS avg_contribution,spd_city AS max_contribution,cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND data_city='".$data_city."' LIMIT 1";
                        $res_new_qry = $this -> conn_fnc->query_sql($new_qry);
                        if($res_new_qry && mysql_num_rows($res_new_qry)>0)
                        {
                            $row_new_qry = mysql_fetch_assoc($res_new_qry);
                            if($row_new_qry['max_contribution']>0)
                            {
								if($catid_avg_callcnt>0){
									if($camp == 1){
										$row['avg_contribution'] = round($row_new_qry['max_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
									}else{
										$row['avg_contribution'] = round($row_new_qry['max_contribution'],4);
									}
								}else{
									$row['avg_contribution'] = round($row_new_qry['max_contribution'],4);
								}
                            }
                        }
                    }
                }
                else/*if its all area category*/
                {
                    $sql = "SELECT MAX(spd_city)  AS avg_contribution,cat_avg_callcnt FROM tbl_package_perday_price WHERE catid='".$catid."' AND data_city='".$data_city."'";
                    $res = $this -> conn_fnc->query_sql($sql);
                    if($res && mysql_num_rows($res))
                    {
                        $row = mysql_fetch_assoc($res);
                        if($row['cat_avg_callcnt']>0)
                        {
                            $catid_avg_callcnt = $row['cat_avg_callcnt'];
                            $this->cat_avgcallcnt = $row['cat_avg_callcnt'];
						}
						if($catid_avg_callcnt>0){
							if($camp == 1){
								$row['avg_contribution'] = round($row['avg_contribution'] * MAX(MIN(pow(((($call_count_array[$catid]['callcount']/$call_count_array['sum_callcount'])*$c2s_callcount)/$catid_avg_callcnt),1/3),self :: MAX_AVG_FACTOR),self :: MIN_AVG_FACTOR),4);
							}else{
								$row['avg_contribution'] = round($row['avg_contribution'],4);
							}
						}else{
							$row['avg_contribution'] = round($row['avg_contribution'],4);
						}
                    }
                }
            }
			if($row['avg_contribution']>0)
			{
				
				return $row['avg_contribution'] * self :: PACK_MULTIPLIER * $req_tenure;/*returning package value of a category*/
			}
			else
			{
				return self :: MIN_PACK_BIDPERDAY * $req_tenure;
			}
		}
	}
	function get_months($date1, $date2) 
	{
			$time1 = strtotime($date1);
			$time2 = strtotime($date2);
			$my = date('mY', $time2);
			$months = array(date('n', $time1));
			$f = '';

			while($time1 < $time2) {
			$time1 = strtotime((date('Y-m-d', $time1).' +15days'));

			if(date('F', $time1) != $f) {
			$f = date('F', $time1);

			if(date('mY', $time1) != $my && ($time1 < $time2))
			$months[] = date('n', $time1);
			}

			}
			$months[] = date('n', $time2);
			$months   = array_unique($months);
			return $months;
	} 
	function GetAdvanceAmountFactor($tot_cat_arr,$data_city,$advance_duration)/*this function would return advance amount factor*/
	{
		
        if(count($tot_cat_arr)>0 && $advance_duration>0)
        {
            $today 		 = date("Y-m-d"); 
            $end_date  	 = date("Y-m-d",mktime(0, 0, 0, date("m"),date("d")+$advance_duration,date("Y")));
            $data 		 = $this -> get_months($today,$end_date); 
            /*foreach($tot_cat_arr as $catid)
            {
                $advance_amount_factor[$catid]['adv_factor']  = 3.2;
            }*/
            $categories = implode(',',$tot_cat_arr);
            $total_months=implode(',',$data);
            foreach($tot_cat_arr as $catid)
            {
                $sql = "SELECT catid,ratio_callcnt as adv_multiply_factor FROM tbl_cat_monthwise_callcnt WHERE catid in (".$catid.") and month_name in (".$total_months.") order by ratio_callcnt desc limit 3";
                $res = $this -> conn_fnc->query_sql($sql);
                if($res && mysql_num_rows($res))
                {					
                    while($row = mysql_fetch_assoc($res))
                    {
                        $advance_amount_factor[$row['catid']]['adv_factor']  += $row['adv_multiply_factor'];
                    }
                }
            }
            //echo '<pre>';print_r($advance_amount_factor);
            /*$sql = "SELECT catid,max(adv_factor) as adv_multiply_factor FROM tbl_package_perday_price WHERE catid in (".$categories.") AND data_city='".$data_city."' group by catid";
            $res = $this -> conn_fnc->query_sql($sql);
            if($res && mysql_num_rows($res))
            {
                $advance_amount_factor = array();
                while($row = mysql_fetch_assoc($res))
                {
                    $advance_amount_factor[$row['catid']]['adv_factor']  = $row['adv_multiply_factor'];
                }
            }*/
            
            return $advance_amount_factor;
        }
	}

	/*  General Text Logging Function */
	function insertTimeLog_temp_catid ($time, $date, $cno, $parentid ,$message)
	{
		$sNamePrefix 	= APP_PATH . 'logs/log_flow/inventoryReAllocateLog'.$date.'.txt';
		$pathToLog 		= dirname($sNamePrefix);
		if (!file_exists($pathToLog)) 	{	mkdir($pathToLog, 0755, true);									}	
		$fp 			= fopen(APP_PATH . 'logs/log_flow/inventoryReAllocateLog'.$date.'.txt', 'a');
		$string	= "For Parentid:".$parentid." [Cnt No:".$cno." Date-Time:".$date."-".$time."]-[".$message."]\n";
		fwrite($fp,$string );
		fclose($fp);        
	}


	/* Snapshot for Inventory status Function */
	function getCatPincodeDetails($catid, $pincode, $parentid,$time, $date,$flag,$status_flag,$position_flag,$ip_inventory_asked)
	{
		$msg 		='';
		$qrySel	= "SELECT catid,pincode,callcnt,platinum_value,platinum_bidder,platinum_inventory,diamond_value,diamond_bidder,diamond_inventory,bronze_value,bronze_bidder,bronze_inventory,platinum_inventory_actual,diamond_inventory_actual,bronze_inventory_actual FROM tbl_platinum_diamond_pincodewise_bid WHERE catid='".$catid."' and pincode = '".$pincode."'";
		$resSel	= $this->conn_fnc->query_sql($qrySel);
		 if($resSel && mysql_num_rows($resSel)>0)
        {
            $rowSel	= mysql_fetch_assoc($resSel);
            $msg 		.= "[Call Cnt: ".$rowSel['callcnt']."] ";
            $msg 		.= "[Plat Val: ".$rowSel['platinum_value']."] ";
            $msg 		.= "[Plat Bidder: ".$rowSel['platinum_bidder']."] ";
            $msg 		.= "[Plat Inventory: ".$rowSel['platinum_inventory']."] ";
            $msg 		.= "[Plat Act INVT: ".$rowSel['platinum_inventory_actual']."]";
            $msg 		.= "[Diam Val: ".$rowSel['diamond_value']."] ";
            $msg 		.= "[Diam Bidder: ".$rowSel['diamond_bidder']."] ";
            $msg 		.= "[Diam Inventory: ".$rowSel['diamond_inventory']."] ";
            $msg 		.= "[Diam Act INVT: ".$rowSel['diamond_inventory_actual']."]";
            $msg 		.= "[Bron Val: ".$rowSel['bronze_value']."] ";
            $msg 		.= "[Bron Bidder: ".$rowSel['bronze_bidder']."] ";
            $msg 		.= "[Bron Inventory: ".$rowSel['bronze_inventory']."] ";
            $msg 		.= "[Bron Act INVT: ".$rowSel['bronze_inventory_actual']."]";
            if($rowSel)
            {
                $this-> getCatPincodeDetailsLog($catid,$pincode,$parentid,$time,$date,$msg,$flag,$status_flag,$position_flag,$ip_inventory_asked);
            }            
        }             
	}

	function CategoryPincodesTotal($ip_catid,$pincodes,$ip_status,$ip_parentid,$ip_inventory_asked,$ip_position_flag,$b2b_flag=0,$totpincodes=0,$category_callcnt,$top_flag)/*function for getting the total platinum value of the pincode irrespective of the inventory*/
	{
		
		if($top_flag == 0)
		{/*if category is belongs to 95% of the categories and not from top 5% categories then check for total available inventory*/
			$ip_inventory_asked = 1;
		}
		
		if($ip_catid && $pincodes)
		{
			$tables_pin_arr = array();
			
			$sql_select 	= "SELECT catid,pincode,callcnt,platinum_value,platinum_bidder,platinum_inventory, 	diamond_value,diamond_bidder,diamond_inventory,lead_value,toplead_value, toplead_bidder, bronze_value,bronze_bidder,bronze_inventory FROM tbl_platinum_diamond_pincodewise_bid where catid='".$ip_catid."' and pincode in (".$pincodes.")";		
			
			$res_select 	= $this -> conn_fnc->query_sql($sql_select);
		
			if(mysql_num_rows($res_select) > 0)
			{
				$inventory_arr          = array();
				while($row_select = mysql_fetch_assoc($res_select))
				{
					unset($plat_bidder_arr);
					unset($diam_bidder_arr);
					unset($gold_bidder_arr);
					unset($pincode);
					unset($check_diam);
					unset($check_gold);
					
					$pincode 				= $row_select['pincode']; 
					$platinum_bidder 		= $row_select['platinum_bidder']; 
					$platinum_inventory 	= $row_select['platinum_inventory']; 
					$diamond_bidder 		= $row_select['diamond_bidder']; 
					$diamond_inventory 		= $row_select['diamond_inventory']; 
					$min_inventory_platinum	= $row_select['min_inventory_platinum']; 
					$min_inventory_diamond	= $row_select['min_inventory_diamond']; 
					$platinum_value 		= $row_select['platinum_value'];
					$diamond_value 			= $row_select['diamond_value'];			
					$bronze_bidder 			= $row_select['bronze_bidder']; 
					$bronze_inventory 		= $row_select['bronze_inventory']; 
					$bronze_value 			= $row_select['bronze_value'];
					$callcnt 				= $row_select['callcnt'];
					$delta_inventory 		= 0;
					$tables_pin_arr[]		= $row_select['pincode'];
					
					if($ip_inventory_asked>0 && $ip_position_flag>0)
					{
						$plat_bidder_arr = $this -> bidder_explode_array($platinum_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						$diam_bidder_arr = $this -> bidder_explode_array($diamond_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						$gold_bidder_arr = $this -> bidder_explode_array($bronze_bidder,$ip_parentid);/*getting bidders in an array with parentid,bidvalue and inventory with cumulative*/
						
						if($ip_status == 4 || $ip_status == 6)/*get available inventory only for one particular position - will be used to cutomized(traditional phone search page)*/
						{
							if($ip_position_flag == self::POS_D)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] = $this -> getAvailableInventory($plat_bidder_arr,$ip_inventory_asked,$platinum_inventory,$ip_parentid);
								
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
							}
						}
						elseif($ip_status == 5 || $ip_status == 6)
						{
							unset($inv_required);
							if($ip_inventory_asked>0)
							{
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] = $this -> getAvailableInventory($plat_bidder_arr,$ip_inventory_asked,$platinum_inventory,$ip_parentid);
								
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['callcnt']=(round(($callcnt/ 365),8) > 0) ? round(($callcnt / 365),8) : round(((1/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
								$inventory_arr[$ip_catid][$pincode][self::POS_D]['bid_value']=($b2b_flag) ? (($category_callcnt>100)? max($platinum_value,self :: NONB2B_MIN_BID_VAL):max($platinum_value,self :: D_B2B_MIN_BID_VAL)) : max($platinum_value,self :: NONB2B_MIN_BID_VAL);/*setting plat bid value into bid_val property*/
								
								$inv_required = ($inventory_arr[$ip_catid][$pincode][self::POS_D]['inv'] >0)?round(($ip_inventory_asked - $inventory_arr[$ip_catid][$pincode][self::POS_D]['inv']),5):$ip_inventory_asked;
							}
						}
					}
				}
			}
			$main_array  = explode(',',$pincodes);
			
			if(count($main_array)>0)
			{
				$absent_pincodes = array_diff($main_array,$tables_pin_arr);
				if(count($absent_pincodes)>0)
				{
					switch($ip_position_flag)
					{
						case self::POS_D :
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: D_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting plat bid value into array*/
							 break;
						case self::POS_DG:
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: DDG_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting diam bid value into array*/
							 break;
						case self::POS_G:
							 $bid_value     	= ($b2b_flag) ? (($category_callcnt>100)?self :: NONB2B_MIN_BID_VAL:self :: G_B2B_MIN_BID_VAL): self :: NONB2B_MIN_BID_VAL;/*setting bronze  bid value into array*/
							 break;
					}
					
					foreach($absent_pincodes as $absent_pincode)
					{
						if($ip_status == 4  || $ip_status == 6)/*For customized(traditional plat/diam/gold) revert with inventory for asked position*/
						{
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['inv'] = $ip_inventory_asked;
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['callcnt']=round(((self :: MIN_CALLCNT/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
							$inventory_arr[$ip_catid][$absent_pincode][$ip_position_flag]['bid_value'] = $bid_value;
						}
						elseif($ip_status == 5 || $ip_status == 6)/*For best budget(plat/diam/gold) revert with platinum position*/
						{
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['inv'] = $ip_inventory_asked;
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['callcnt']=round(((self :: MIN_CALLCNT/$totpincodes)/365),5);/*getting per day callcnt and setting into array*/
							$inventory_arr[$ip_catid][$absent_pincode][self::POS_D]['bid_value'] = $bid_value;
						}
					}
				}
			}
			
			return $inventory_arr;
						
		}
	}
	
	function GetExclCatBidValArr($catid)/*function to get category bid value array*/{
		if($catid){
			$sql = "SELECT * FROM tbl_exclusive_pincodewise_bid WHERE bid_catid = '".$catid."'";
			$res = $this -> conn_fnc->query_sql($sql);
			if($res && mysql_num_rows($res)){
				$catid_bidvalue_arr = array();    		
				while($row = mysql_fetch_assoc($res)){
					$catid_bidvalue_arr[$catid][$row['pincode']]['plat_bid']= $row['platinum_value'];
					$catid_bidvalue_arr[$catid][$row['pincode']]['excl_bid']= $row['exclusive_bid_value'];
				}
				return $catid_bidvalue_arr;
			}
	    }
	}

	function getNationalCatid($catid){
		if(trim($catid)!=''){
			$sqlNatCat = "SELECT national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid = '".$catid."' LIMIT 1";
			$qryNatCat = $this -> conn_iro->query_sql($sqlNatCat);
			if($qryNatCat){
				$rowNatCat = mysql_fetch_assoc($qryNatCat);
				if($rowNatCat['national_catid']!=''){
					return $rowNatCat['national_catid'];
				}
			}
		}
	}

	/* Text Logging Function: Parentid wise... */
	function getCatPincodeDetailsLog($catid, $pincode, $parentid,$time, $date, $message,$flag=0,$status_flag,$position_flag,$ip_inventory_asked)
	{
		$sNamePrefix 	= APP_PATH . 'logs/log_flow/bookingDetails/'.$parentid.'.txt';
		$pathToLog 		= dirname($sNamePrefix);
		if (!file_exists($pathToLog))	{	mkdir($pathToLog, 0755, true);								}
		$fp 						= fopen(APP_PATH . 'logs/log_flow/bookingDetails/'.$parentid.'.txt', 'a');
		if($flag != 1) 		{	$flg	= "\tBEFORE:\n"; } else { $flg = "\tAFTER:\n"; 					}		
		$string				= "[Time:".$date." ".$time."][Cat: ".$catid."][PIN:".$pincode."][IPS:".$status_flag."][InvAsk:".$ip_inventory_asked."][Pos:".$position_flag."]".$flg."[".$message."]\n";
		fwrite($fp,$string );
		fclose($fp);			
	}	
}

?>
