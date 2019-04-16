<?php

class fixed_position_national_class extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $intermediate 	= null;
	var  $params  	    = null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	var  $main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata' );
	
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $catsearch	= null;
	var  $data_city	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		
				
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}	
		
		if(trim($this->params['contract_data_city']) != "" && $this->params['contract_data_city'] != null)
		{
			$this->contract_data_city  = $this->params['contract_data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='contract_data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['pin_city']) != "" && $this->params['pin_city'] != null)
		{
			$this->pin_city  = $this->params['pin_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='pin_city missing';
			echo json_encode($errorarray); exit;
		}
		

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}

		$this->setServers();		
		$this->categoryClass_obj = new categoryClass();
		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['pin_city']), $this->dataservers)) ? strtolower($this->params['pin_city']) : 'remote');
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		$this->db_budget		= $db[$data_city]['db_budgeting']['master'];		
		$this->conn_idc   	    = $db[$data_city]['idc']['master'];
		$this->conn_local  	    = $db[$data_city]['d_jds']['master'];
		$this->conn_iro  	    = $db[$data_city]['iro']['master'];
			
	}

	function getRequestedPositionInventory()
	{
	   $city_condition    = in_array(strtolower($this->pin_city),$this->main_cities) ?  "IN ('".$this->pin_city."') " : " NOT IN ('".implode("','",$this->main_cities)."') ";
	   
	   $sql_get_inventory = "SELECT * FROM db_national_listing.tbl_bidding_details_national WHERE parentid='".$this->parentid."' AND pincity ".$city_condition."";
	   
	   $res_get_inventory = parent::execQuery($sql_get_inventory,$this->conn_idc);
	   
	   if(DEBUG_MODE)
		{
			echo '<br><b> Query:</b>'.$sql_get_inventory;
			echo '<br><b>Result Set:</b>'.$res_get_inventory;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($res_get_inventory);
			echo '<br><b>Error:</b>'.$this->mysql_error;
			print_r($this->conn_idc);
		}
		
	   if($res_get_inventory && mysql_num_rows($res_get_inventory)>0)
	   {
		   while($row_get_inventory = mysql_fetch_assoc($res_get_inventory))
		   {
			   $this-> req_category_booking_data[$row_get_inventory['parentid']][$row_get_inventory['catid']][$row_get_inventory['pincode']][3]['req_inv'] = 1;
			   
			   $this-> req_city_category_booking_data[$row_get_inventory['parentid']][$row_get_inventory['catid']][$row_get_inventory['pincode']]['req_city'] = strtolower(trim($row_get_inventory['pincity']));
			   $this-> national_catid[$row_get_inventory['catid']] = $row_get_inventory['national_catid'];
		   }
	   }
	}
	
	function getCurrentInventoryData()
	{
	   $city_condition    = in_array(strtolower($this->pin_city),$this->main_cities) ?  "IN ('".$this->pin_city."') " : " NOT IN ('".implode("','",$this->main_cities)."') ";
	   
	   $sql_get_inventory_data ="SELECT parentid, pincity, GROUP_CONCAT( DISTINCT catid) AS catids, GROUP_CONCAT( DISTINCT pincode) AS pincodes 
											 FROM db_national_listing.tbl_bidding_details_national 
											 WHERE 
											 parentid='".$this->parentid."' 
											 AND pincity ".$city_condition."
											 GROUP BY parentid,pincity";
	   
	   $res_get_inventory_data = parent::execQuery($sql_get_inventory_data,$this->conn_idc);
	   if($res_get_inventory_data && mysql_num_rows($res_get_inventory_data)>0)
	   {
		   while($row_get_inventory_data = mysql_fetch_assoc($res_get_inventory_data))
		   {
			   if($row_get_inventory_data['catids'] && $row_get_inventory_data['pincodes'])
			   {
				 $this->cat_array = $this-> get_category_details($row_get_inventory_data['catids']);
				 
				 if(DEBUG_MODE)
					{
						echo 'category data<pre>';
						print_r($this->cat_array);
					}
				
				 
				 $sql_inventory  = "SELECT * FROM tbl_fixedposition_pincodewise_bid WHERE catid IN ('".str_replace(",","','",$row_get_inventory_data['catids']) ."') AND pincode IN ('".str_replace(",","','",$row_get_inventory_data['pincodes'])."')";
				 $res_inventory = parent::execQuery($sql_inventory,$this->db_budget);
				 if($res_inventory && mysql_num_rows($res_inventory)>0)
				 {
				   while($row_inventory = mysql_fetch_assoc($res_inventory))
				   {
					   $this -> avail_current_inventory_data[$row_inventory['catid']][$row_inventory['pincode']] = $row_inventory;
				   }
				 }
			   }			   
		   }
	   }
	}
	
	function getBidderArray($position_bidder,$parentid)
	{
		$bidders_array = explode(",",$position_bidder);
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
	
	function getAvailableInventory($bidder_array,$inventory_asked,$total_inventory,$parentid)
	{/*function to get available inventory for requested position by passing total iventory and bidders*/

		$Max_Inventory = 1;
		if($bidder_array[$parentid]['existing'] == 1)
		{
			$avail_inventory = ($Max_Inventory - $total_inventory) + $bidder_array[$parentid]['inv'];
		}
		else
		{
			$avail_inventory = $Max_Inventory - $total_inventory;
		}

		if($inventory_asked <= $avail_inventory)
		{
			return $inventory_asked;
		}
		else
		{
			return 0;
		}

	}
	
	
	function newInventoryDetails($parentid,$catid,$pincode,$requested_position,$requested_inventory)
	{
		$available_position_inventory = array();
		if(count($this->avail_current_inventory_data[$catid][$pincode])>0)
		{
				$row_inventory = $this->avail_current_inventory_data[$catid][$pincode];
				//echo ' 180 :: <pre>';print_r($row_inventory);
				if($requested_position<100)
				{
					/*while($i=0;$i<8,$i++)
					{

					}*/
					//echo '<br>out :: '.$parentid."---".$catid."---".$pincode."---".$requested_position;
					if($requested_position > 2 && $requested_position < 6)
					{

						//echo '<br> inside :: '.$parentid."---".$catid."---".$pincode."---".$requested_position;

						for($i=$requested_position;$i<6;$i++)
						{
							$total_bidder_array[$i]['bidder_array']     =  $this->getBidderArray($row_inventory['pos'.$i.'_bidder'],$parentid);
							//$total_bidder_array[$i]['booked_inventory'] =  $row['pos'.$i.'_inventory_booked'];

							//echo $parentid."=====".$catid."===".$pincode." :: req ps :: ".$requested_position." :: curloop ps :: ".$i." booked inv :: ".$row_inventory['pos'.$i.'_inventory_booked'].'<br>';
							//echo '<pre>';print_r($total_bidder_array[$i]['bidder_array']);pos1_inventory_booked

							$available_inventory = $this->getAvailableInventory($total_bidder_array[$i]['bidder_array'],$requested_inventory,$row_inventory['pos'.$i.'_inventory_booked'],$parentid);

							if($available_inventory>0)
							{
								$available_position = $i;
								$available_position_inventory['position']  = $i;
								$available_position_inventory['inventory'] = $available_inventory;
								break;
							}

						}
					}
					if(!count($available_position_inventory))
					{
							$available_position_inventory['position']  = 100;
							$available_position_inventory['inventory'] = 0;
					}

					return $available_position_inventory;


				}
		}
		else
		{
			if($requested_position > 2 && $requested_position<6)
			{
					$available_position_inventory['position']  = $requested_position;
					$available_position_inventory['inventory'] = $requested_inventory;
			}else
			{
					$available_position_inventory['position']  = 100;
					$available_position_inventory['inventory'] = 0;
			}

			return $available_position_inventory;
		}

	}
	
	// preparing bidder string to update in inventory table
	function bidder_implode_string($bidder_array,$parentid,$catid,$pincode,$position)/*,$status_flag, $tbl_bidcatdetails_ddg,$tbl_compcatarea_ddg,$actual_inventory)*/
	{

		$cummlative 		= 0;
		$min_inventory	= 1;
		foreach($bidder_array as $parentid=>$attributes)
		{
			if($attributes['inv'] != 0)
			{
				if(!($attributes['cum'] == ($cummlative+$attributes['inv'])))
				{
					$attributes['cum'] = $cummlative+$attributes['inv'];

				}
				$cummlative += $attributes['inv'];
				$pids[] = $parentid."-".$attributes['bid']."-".$attributes['inv']."-".$attributes['cum']."-".$attributes['act_inv'];
			}
		}

		/*
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

		}*/

		if($pids)
		$bidders_string = implode(",",$pids);

		return $bidders_string;
	}
	
	
	function InventoryManagement($parentid,$catid,$pincode,$position,$asked_live_inventory,$asked_shadow_inventory= 0,$current_bid_value)
	{
		global $conn_budget,$postion_bid_factor,$cat_array,$data_city_arr;
		//echo '<br> catid - pincode :: '.$sql_inventory  = "SELECT * FROM tbl_fixedposition_pincodewise_bid WHERE catid='".trim($catid)."' AND pincode='".trim($pincode)."'";
		//echo '<br> res :: '.$res_inventory  = $conn_budget ->query_sql($sql_inventory);

		//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory);
		
		$pin_city = $this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city'];
		
		if( count($this->avail_current_inventory_data[$catid][$pincode]) > 0 )
		{

			//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory);

			$row_inventory    = $this->avail_current_inventory_data[$catid][$pincode];

			//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory.' :: existing inventory data : '.json_encode($row_inventory));


			$position_bidder  = $this->getBidderArray($row_inventory['pos'.$position.'_bidder'],$parentid);
			$total_inventory  = $row_inventory['pos'.$position.'_inventory_booked'];
			$actual_inventory = $row_inventory['pos'.$position.'_inventory_actual'];

			//echo '<br> city :: '.$this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city'];
			//print_r($this-> postion_bid_factor['business_uploadrates'][$this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city']]);
			$b_val = $row_inventory['x_bidvalue'] * $this-> postion_bid_factor['business_uploadrates'][$this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city']]['bidvalue_premium'];

			if($this->cat_array[$catid]['b2b_flag']==1)
				$bidvalue	= max(50,$b_val);
			else
				$bidvalue	= max(5,$b_val);
				
			//echo '<br> bv :: '.$bidvalue;
			$bid_value = $bidvalue * $this-> postion_bid_factor['position_factor'][$position]['f'];
				
			//$bid_value = $current_bid_value;

			//$callcount = max($callcount, 1/$all_pincode_count);
			


			if($position_bidder[$parentid]['existing'] == 1)
			{
				if($asked_live_inventory)
						$delta_inventory 		= (max($asked_live_inventory,$asked_shadow_inventory)) - $position_bidder[$parentid]['inv'];
				else if($asked_shadow_inventory)
						$delta_inventory 		= $asked_shadow_inventory - $position_bidder[$parentid]['inv'];

				$actual_delta_inventory = $asked_live_inventory - $position_bidder[$parentid]['act_inv'];

				if( ($total_inventory+$delta_inventory) <= 1 )
				{
					//if($asked_live_inventory)
					$total_inventory 						= max(($total_inventory + $delta_inventory),0);

					$actual_inventory						= ($asked_live_inventory>0) ? max(($actual_inventory + $actual_delta_inventory),0) : $actual_inventory;

					$bidder_array[$parentid]['bid'] 		= $bid_value;

					$bidder_array[$parentid]['inv'] 		= ($asked_live_inventory>0) ? max($asked_live_inventory,$asked_shadow_inventory) : ($asked_shadow_inventory<$position_bidder[$parentid]['inv'] ? $position_bidder[$parentid]['inv'] : $asked_shadow_inventory);

					$bidder_array[$parentid]['act_inv'] 	= ($asked_live_inventory>0) ? $asked_live_inventory : $position_bidder[$parentid]['act_inv'];;

					$bidder_array[$parentid]['cum'] 		= 0;
				}else
				{
					return '-1';//overbooking
				}
			}
			else
			{
				$new_inventory_asked = ($asked_live_inventory>0) ? max($asked_live_inventory,$asked_shadow_inventory) : $asked_shadow_inventory;

				if( $total_inventory + $new_inventory_asked <=1)
					{
							$total_inventory 						= max(($total_inventory + $new_inventory_asked),0);
							$actual_inventory						= ($asked_live_inventory>0) ? max(($actual_inventory + $asked_live_inventory),0) : $actual_inventory;
							$bidder_array[$parentid]['bid'] 		= $bid_value;
							$bidder_array[$parentid]['inv'] 	    = $new_inventory_asked;
							$bidder_array[$parentid]['act_inv'] 	= ($asked_live_inventory>0) ? $asked_live_inventory : 0;
							$bidder_array[$parentid]['cum'] 		= 0;
					}
					else
					{
						return '-1'; // overbooking
					}

			}
				//echo '<br> bidder inventory array <pre> ';
				//print_r($bidder_array);

				$bidders_request = $this->bidder_implode_string($bidder_array, $parentid, $catid, $pincode, $position);

				//echo '<br>parentid :: '.$parentid.' catid :: '.$catid.' pincode :: '.$pincode.' position :: '.$position.' bidder '.$bidders_request;

				 $bidders_request	= "pos".$position."_bidder = '".$bidders_request."'";
				 $total_inventory 	= "pos".$position."_inventory_booked = '".$total_inventory."'";
				 $actual_inventory	= "pos".$position."_inventory_actual = '".$actual_inventory."'";
				 
				 $inc_inventory	    = "pos".$position."_inc = '1'";

				 $sql_update_booking = "UPDATE tbl_fixedposition_pincodewise_bid SET
												".$bidders_request.",
												".$total_inventory.",
												".$actual_inventory.",
												".$inc_inventory."
										WHERE catid='".$catid."' AND pincode='".$pincode."' ";
				//$res_update_booking = $conn_budget->query_sql($sql_update_booking);
				$res_update_booking = parent::execQuery($sql_update_booking,$this->db_budget);
				
				
				if(DEBUG_MODE)
				{
					echo '<br><b> Query:</b>'.$sql_update_booking;
					echo '<br><b>Result Set:</b>'.$res_update_booking;
				}
				
				if($res_update_booking)
				{
					$this->booked_data['bidding_data'][] = "( '".$parentid."', '".$this->docid."', '".$this->contract_data_city."', '".$this->version."', '".$this->campaignid."', '".$catid."', '".$this->cat_array[$catid]['nid']."', '".$pincode."', '".$pin_city."', '".$position."', '".$bidder_array[$parentid]['inv']."', '".$bidperday."', '".$this->user_code."', NOW() ) ";
				}
				


				//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory.' :: sql : '.$sql_update_booking);

				//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory.' :: res : '.$res_update_booking);


			/*
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
			*/


		}
		else
		{
				unset($bidder_array);
				unset($position_bidder);
				// inserting entries for new categories

				if($this->cat_array[$catid]['b2b_flag']==1)
				{
					$x_bid_value	= 50;
					$bid_value	= 50;
				}
				else
				{
					$x_bid_value	= 5;
					$bid_value	= 5;
				}
				
				
				

				$total_inventory 						= ($asked_live_inventory>0) ? max($asked_live_inventory,$asked_shadow_inventory) : $asked_shadow_inventory;
				$actual_inventory						= ($asked_live_inventory>0) ? $asked_live_inventory : 0;
				$bidder_array[$parentid]['bid'] 		= $bid_value * $this-> postion_bid_factor['position_factor'][$position]['f'];
				$bidder_array[$parentid]['inv'] 	    = ($asked_live_inventory>0) ? max($asked_live_inventory,$asked_shadow_inventory) : $asked_shadow_inventory;
				$bidder_array[$parentid]['act_inv'] 	= ($asked_live_inventory>0) ? $asked_live_inventory : 0;
				$bidder_array[$parentid]['cum'] 		= 0;

				$bidders_request = $this->bidder_implode_string($bidder_array, $parentid, $catid, $pincode, $position);

				if($position<100 && ($asked_live_inventory>0 || $asked_shadow_inventory>0))
				{
					

						/*$bidders_request	= "pos".$position."_bidder = '".$bidders_request."'";
						$total_inventory 	= "pos".$position."_inventory_booked = '".$total_inventory."'";
						$actual_inventory	= "pos".$position."_inventory_actual = '".$actual_inventory."'";
						
						$pincode_bid_value	= "bidvalue	  = '".$bid_value."'";
						$pincode_x_bid_value= "x_bidvalue = '".$x_bid_value."'";
						
						//$pincode_data_city  = "data_city = '".$data_city_arr[$pincode]."'";*/
						
						if($this->cat_array[$catid]['b2b_flag'] == 1)
						{
							$callcount_default  = 1/$this-> pincode_wise_city_details[$pincode]['dc_pin_cnt'];
							$min_budget_default = '50';
						}
						else
						{
							$callcount_default  = 1/$this-> pincode_wise_city_details[$pincode]['dc_pin_cnt'];
							$min_budget_default = '1';
						}
						//echo 'city count  :: '.$this-> pincode_wise_city_details[$pincode]['dc_pin_cnt'];
						//echo '<br>parentid :: '.$parentid.' catid :: '.$catid.' pincode :: '.$pincode.' position :: '.$position.' bidder '.$bidders_request.' callcount_default '.$callcount_default;
						
						
							$this -> bulk_inventory_booking_data[$position][] = " ( '".$catid."', '".$pincode."', '".$pin_city."', '".$callcount_default."', '".$bid_value."', '".$x_bid_value."', '".$min_budget_default."', '".$bidders_request."', '".$total_inventory."', '".$actual_inventory."', '1' ) ";
							
							$this->booked_data['bidding_data'][] = "( '".$parentid."', '".$this->docid."', '".$this->contract_data_city."', '".$this->version."', '".$this->campaignid."', '".$catid."', '".$this->cat_array[$catid]['nid']."', '".$pincode."', '".$pin_city."', '".$position."', '".$bidder_array[$parentid]['inv']."', '".$bidperday."', '".$this->user_code."', NOW() ) ";
							
						
						
						//$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),$flag=0, $status_flag,$position_flag,$ip_inventory_asked);
						//$nationalCatid = $this->getNationalCatid($ip_catid);
						/*$sql_update_booking = "INSERT INTO tbl_fixedposition_pincodewise_bid SET
													catid				='".$catid."',
													pincode				='".$pincode."',
													data_city			='".$this->data_city."',
													callcount 			= '".$callcount_default."',
													bidvalue  			= '".$bidvalue_default."',
													x_bidvalue			= '".$x_bidvalue_default."',
													category_minbudget  = '".$min_budget_default."',
													".$bidders_request.",
													".$total_inventory.",
													".$actual_inventory." 
													".$inc_inv." 
												ON DUPLICATE KEY UPDATE
													".$bidders_request.",
													".$total_inventory.",
													".$actual_inventory." 
													".$inc_inv;
						
						$res_update_booking 	= parent::execQuery($sql_update_booking, $this->dbConbudget);
					echo '<br>new case ::  '.$sql_update_booking = "INSERT INTO tbl_fixedposition_pincodewise_bid SET
													catid='".$catid."',
													pincode='".$pincode."',
													".$pincode_bid_value.",
													".$pincode_x_bid_value.",
													".$pincode_data_city.",
													".$bidders_request.",
													".$total_inventory.",
													".$actual_inventory;*/
						//$res_select = $conn_budget->query_sql($sql_update_booking);
						//$res_select = parent::execQuery($sql_update_booking,$this->db_budget);
						
					//	$this ->insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$ip_parentid,'QUERY: '.$sql_update_booking);
						//$this->getCatPincodeDetails($ip_catid, $ip_pincode, $ip_parentid,date('H:i:s'),date('Y-m-d'),1, $ip_status,$ip_position_flag,$ip_inventory_asked);


						//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory.' :: sql : '.$sql_update_booking);

						//insertTimeLog_temp_catid(date('H:i:s'),date('Y-m-d'),$counter,$parentid,'catid : '.$catid.' :: pincode : '.$pincode.' :: position : '.$position.' :: asked_live_inventory : '.$asked_live_inventory.' :: asked_shadow_inventory : '.$asked_shadow_inventory.' :: res : '.$res_select);

				}



		}
	}
	
	function get_category_details($catids)
	{
		global $conn_local;
		/*$sql="select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag
		from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";*/
		//$res_catids = parent::execQuery($sql,$this->conn_local);
		$cat_params = array();
		$cat_params['page']= 'fixed_position_national_class';
		$cat_params['data_city'] 	= $this->contract_data_city;
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,category_type';			
		$where_arr  	=	array();
		if($catids!=''){
			$where_arr['catid']			= $catids;
			$where_arr['biddable_type']	= '1';
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
		{

			foreach($cat_res_arr['results'] as $key =>$row)
			{
				//print_r($row);
				$business_flag =	$row['business_flag'];
				$category_type =	$row['category_type'];

				$block_for_contract = 0;
				$exclusive_flag = 0;
				$b2b_flag		= 0;
				if(((int)$category_type & 64) == 64){
					$block_for_contract = 1;
				}				
				if(((int)$category_type & 16) == 16){
					$exclusive_flag = 1;
				}
				if($business_flag == 1){
					$b2b_flag = 1;
				}

				$catid = $row['catid'];
				$cat_array[$catid]['cnm'] 		= $row['category_name'];
				$cat_array[$catid]['cid'] 		= $row['catid'];
				$cat_array[$catid]['nid'] 		= $row['national_catid'];
				$cat_array[$catid]['b2b_flag']  = $b2b_flag;
				$cat_array[$catid]['bfc']  		= $block_for_contract;
				$cat_array[$catid]['x_flag']    = $exclusive_flag;
			}
		}
		return($cat_array);
	}
	
	
	function getPincodeWiseCityDetails()
	{
		$sql_get_pincode_details = "SELECT data_city, GROUP_CONCAT(DISTINCT pincode) AS all_pincodes, COUNT(DISTINCT pincode) AS pincount FROM tbl_areamaster_consolidated_v3 GROUP BY data_city";
		$res_get_pincode_details = parent::execQuery($sql_get_pincode_details,$this->conn_local);


		if($res_get_pincode_details && mysql_num_rows($res_get_pincode_details) > 0)
		{

			while($row_get_pincode_details = mysql_fetch_assoc($res_get_pincode_details))
			{
				//print_r($row);
				$this-> city_pincode_count[$row_get_pincode_details['data_city']] = $row_get_pincode_details['pincount'];
				
				if($row_get_pincode_details['all_pincodes'])
				{
					$all_pincodes_arr = explode(",", $row_get_pincode_details['all_pincodes']);
					foreach($all_pincodes_arr as $pincode)
					{
						$this-> pincode_wise_city_details[$pincode]['dc']         = $row_get_pincode_details['data_city'];
						$this-> pincode_wise_city_details[$pincode]['dc_pin_cnt'] = $row_get_pincode_details['pincount'];
					}
				}
			}
		}
	}
	
	function GetFixedPositionFactor()
	{
		
		$city_condition    = in_array(strtolower($this->pin_city),$this->main_cities) ?  "IN ('".$this->pin_city."') " : " NOT IN ('".implode("','",$this->main_cities)."') ";
		
		$sql ="select * from tbl_business_uploadrates where city ".$city_condition." ";
		$res  = parent::execQuery($sql,$this->conn_local);
		
		if($res && mysql_num_rows($res))
		{

			while($row=mysql_fetch_assoc($res))
			{

				if(DEBUG_MODE)
				{
				//	echo '<hr>';
				//	print_r($row);
				}
				$business_uploadrates[strtolower(trim($row['city']))]['callcnt_growth_rate'] = 1 + ($row['callcnt_per']/100);
				$business_uploadrates[strtolower(trim($row['city']))]['city_min_budget'] 	 = 	$row['cityminbudget'];
				$business_uploadrates[strtolower(trim($row['city']))]['all_pincode_count']   = 	$row['allpincnt'];
				$business_uploadrates[strtolower(trim($row['city']))]['bidvalue_premium']    = 1 + ($row['bidvalue_per']/100);
			}
		}

		$sql_factor = "select * from tbl_fixedposition_factor where active_flag=1";
		$res_factor = parent::execQuery($sql_factor,$this->db_budget);
		$num_rows		= mysql_num_rows($res);
		if($res_factor && mysql_num_rows($res))
		{

			while($row=mysql_fetch_assoc($res_factor))
			{

				$position_factor[$row['position_flag']]['n']	= $row['positionfactor'];
				$position_factor[$row['position_flag']]['x']	= $row['exclusive_positionfactor'];
				$position_factor[$row['position_flag']]['f']	= $row['final_positionfactor'];
			}
		}

		if(count($position_factor) || (count($business_uploadrates)))
		{
			$final_arr['position_factor']	   = $position_factor;
			$final_arr['business_uploadrates'] = $business_uploadrates;
			$final_arr['error'] = 0;
			$final_arr['message'] = 'no error';
		}else
		{
			$final_arr['error'] = '-1';
			$final_arr['message'] = 'no data';
		}

		return $final_arr;
	}
	
	function bulk_inventory_booking()// bulk inventory booking for new category-pincodes
	{
		if(DEBUG_MODE)
		{
			echo '<br>bulk inv booking data<pre>';
			print_r($this->bulk_inventory_booking_data);
		}
		if(count($this -> bulk_inventory_booking_data) > 0)
			{
				foreach($this -> bulk_inventory_booking_data as $position => $position_data)
				{
					$pos_str = 'pos'.$position;
					//echo '<br> booking count :: '.count($position_data);
					
					$sql_inv_book = " INSERT INTO tbl_fixedposition_pincodewise_bid(catid, pincode, data_city, callcount, bidvalue, x_bidvalue, category_minbudget, ".$pos_str."_bidder, ".$pos_str."_inventory_booked, ".$pos_str."_inventory_actual, ".$pos_str."_inc ) VALUES ";
					if(count($position_data) > 0)
					{
						//echo '<br>'.$sql_inv_book.implode(",",$position_data);
						
						$res_update_booking = parent::execQuery($sql_inv_book.implode(",",$position_data),$this->db_budget);
						
						if(DEBUG_MODE)
						{
							echo '<br>bulk inv booking res set data :: '.$res_update_booking;
						}
						
					}
					
				}
			}
	}
	
	function update_bidding_data()
	{
		if(count($this->booked_data['bidding_data']) > 0)
		{
			$city_condition    = in_array(strtolower($this->pin_city),$this->main_cities) ?  "IN ('".$this->pin_city."') " : " NOT IN ('".implode("','",$this->main_cities)."') ";
			
	   
			$sql_delete_shadow = "DELETE FROM db_national_listing.tbl_bidding_details_national_shadow WHERE parentid='".$this->parentid."' AND pincity ".$city_condition." ";
			$res_delete_shadow = parent::execQuery($sql_delete_shadow,$this->conn_idc);
			
			if(DEBUG_MODE)
			{
				echo '<br><b> Query:</b>'.$sql_delete_shadow;
				echo '<br><b>Result Set:</b>'.$res_delete_shadow;
			}
			
			$sql_insert_shadow = "INSERT INTO db_national_listing.tbl_bidding_details_national_shadow(parentid, docid, data_city, version, campaignid, catid, national_catid, pincode, pincity, position_flag, inventory, bidperday, updatedby, updatedon) VALUES";
			$sql_insert_shadow.implode(",",$this->booked_data['bidding_data']);
			$res_insert_shadow = parent::execQuery($sql_insert_shadow.implode(",",$this->booked_data['bidding_data']),$this->conn_idc);
			
			if(DEBUG_MODE)
			{
				echo '<br><b> INSERT INTO db_national_listing.tbl_bidding_details_national_shadow  Query Res:</b>'.$res_insert_shadow;
			}
			
			$sql_id_generator = "SELECT * FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
			$sql_general_info = "SELECT * FROM tbl_companymaster_generalinfo WHERE parentid='".$this->parentid."'";
			$sql_extra_info   = "SELECT * FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";
			
			if( (in_array(strtolower($this->contract_data_city),$this->main_cities) && strtolower($this->pin_city) == strtolower($this->contract_data_city)) || !in_array(strtolower($this->contract_data_city),$this->main_cities))
			{
				$res_id_generator = parent::execQuery($sql_id_generator,$this->conn_iro);
				$res_general_info = parent::execQuery($sql_general_info,$this->conn_iro);
				$res_extra_info   = parent::execQuery($sql_extra_info,$this->conn_iro);
				if(DEBUG_MODE)
				{
					
					echo '<br><b> Query:</b>'.$sql_id_generator;
					echo '<br><b>Result Set:</b>'.$res_id_generator;
					echo '<br><b>row Set:</b>'.mysql_num_rows($res_id_generator);
					
				}
				if(DEBUG_MODE)
				{
					echo '<br><b> Query:</b>'.$sql_general_info;
					echo '<br><b>Result Set:</b>'.$res_general_info;
					echo '<br><b>row Set:</b>'.mysql_num_rows($res_id_generator);
				}
				if(DEBUG_MODE)
				{
					echo '<br><b> Query:</b>'.$sql_extra_info;
					echo '<br><b>Result Set:</b>'.$res_extra_info;
					echo '<br><b>row Set:</b>'.mysql_num_rows($res_id_generator);
				}
				
				if($res_id_generator && mysql_num_rows($res_id_generator) > 0)
				{

					$row_id_generator = mysql_fetch_assoc($res_id_generator);
					$row_general_info = mysql_fetch_assoc($res_general_info);
					$row_extra_info   = mysql_fetch_assoc($res_extra_info);
				}
				
			}
			
			
			if($res_insert_shadow)
			{
					
							/*$sql_update_main_search = "UPDATE db_national_listing.tbl_fp_search_national a
									JOIN db_national_listing.tbl_bidding_details_national b
									ON  a.parentid = b.parentid
									AND a.catid    = b.catid
									AND a.pincode  = b.pincode
									SET a.position_flag = b.position_flag,
										a.inventory = b.inventory
									WHERE 
									    a.parentid = '".$this->parentid."'
									AND 
									    b.position_flag>0 AND b.position_flag < 100 
									AND 
										b.inventory>0
									";
							$res_update_main_search = parent::execQuery( $sql_update_main_search ,$this->conn_idc );*/	
							
							$log_data['url']         	 = 'http://192.168.17.109/logs/logs.php';		
							$post_data['ID']        	 = $this->parentid;                
							$post_data['PUBLISH']  		 = 'CS';         	
							$post_data['ROUTE']   	     = 'NATIONALPDG';   		
							$post_data['CRITICAL_FLAG']  = 1 ;			
							$post_data['MESSAGE']        = 'NATIONALPDG';	
							$post_data['DATA']['url'] 	 = 'services/fixed_position_national.php';		
							$post_data['DATA_JSON']['paramssubmited']	 = 	$post_data;
							$post_data['DATA_JSON']['response']	 = 	json_encode($this->avail_current_inventory_data);
							
							$log_data['method'] 		 = 'post';
							$log_data['formate'] 		 = 	'basic';
							$log_data['postData'] 		 = 	 http_build_query($post_data);
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL,$log_data['url']);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch,CURLOPT_POST, TRUE);
							curl_setopt($ch,CURLOPT_POSTFIELDS,$log_data);
							@$transferstring = curl_exec($ch);
							curl_close($ch);
							
							if(DEBUG_MODE)
							{
								echo '<br><b> ulr:</b>'.$log_data['url'];
								echo '<br><b>Result :</b>'.$transferstring;
							}
							
							if(count($this->non_fixed_position_category_pincode)>0)
							{
								$this->non_fixed_position_category_pincode[$this->parentid]['cities'] = array_unique($this->non_fixed_position_category_pincode[$this->parentid]['cities']);
								$this->non_fixed_position_category_pincode[$this->parentid]['catids'] = array_unique($this->non_fixed_position_category_pincode[$this->parentid]['catids']);
							}
							
							$return_array['error_code'] 		= 0;
							$return_array['parentid']   		= $this->parentid;
							$return_array['contract_data_city'] = $this->contract_data_city;
							$return_array['req_pin_city']       = $this->pin_city;
							if(count($row_id_generator)>0)
							{
								$return_array['compdata']['id_gen']   = $row_id_generator;
								$return_array['compdata']['comp_gen'] = $row_general_info;
								$return_array['compdata']['comp_ext'] = $row_extra_info;
							}
							$return_array['non_fixed_pos_data'] = (count($this->non_fixed_position_category_pincode) > 0) ? $this->non_fixed_position_category_pincode : 'no_non_fixed_pos_data';
							$return_array['msg'] = 'success';
							
							
							return  $return_array;
							
								
			}
			
		}
	}
	
	
	function updateinventory()
	{
		
		
		$this-> getRequestedPositionInventory();
		
		$this-> getCurrentInventoryData();
		
		
		if(DEBUG_MODE)
		{
			echo '<br> request catg data<pre>';
			print_r($this->req_category_booking_data);
		}
		
		//echo 'AVAL :: <pre>';
		//print_r($this->avail_current_inventory_data);
		
		$this-> postion_bid_factor = $this -> GetFixedPositionFactor();
		
		if(DEBUG_MODE)
		{
			echo '<br> request pos bid fac data<pre>';
			print_r($this->postion_bid_factor);
		}
		
		
		if(count($this->req_category_booking_data) > 0)
		{
			
			$this -> getPincodeWiseCityDetails();
			if(DEBUG_MODE)
			{
				echo '<br> avail_current_inventory_data <pre>';
				print_r($this->avail_current_inventory_data);
			}
			
			foreach($this->req_category_booking_data as $parentid => $category_data)
			{
					
					foreach($category_data as $catid => $pincode_data)
					{
						foreach($pincode_data as $pincode => $position_data)
						{

							foreach($position_data as $position => $inventory_data)
							{
								
								
								if($inventory_data['req_inv']>0)
								$live_positon_inv 	 = $this->newInventoryDetails($parentid,$catid,$pincode,$position,$inventory_data['req_inv']);
								
								if(DEBUG_MODE)
								{
									echo '<br>live inventory ';print_r($live_positon_inv);
								}
								
								if( count($live_positon_inv)>0 && $live_positon_inv['position']>0 && $live_positon_inv['inventory']>0 )
									$this->InventoryManagement($parentid, $catid, $pincode, $live_positon_inv['position'], $live_positon_inv['inventory'],$shadow_positon_inv['inventory'],$bid_value);
								else
								{
									$this->non_fixed_position_category_pincode[$parentid]['citywise'][$this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city']][$catid][$pincode]['pos'] = $live_positon_inv['position'];
									$this->non_fixed_position_category_pincode[$parentid]['citywise'][$this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city']][$catid][$pincode]['inv'] = $live_positon_inv['inventory'];
									$this->non_fixed_position_category_pincode[$parentid]['cities'][] = $this-> req_city_category_booking_data[$parentid][$catid][$pincode]['req_city'];
									$this->non_fixed_position_category_pincode[$parentid]['catids'][] = $this-> national_catid[$catid];
									
								}
								

								/*if ($new_dest_live_positon_inv['new_inventory'] > 0)
								{
									InventoryManagement($dest_parentid,$catid,$pincode,$new_dest_live_positon_inv['new_position'] ,$new_dest_live_positon_inv['new_inventory'],$new_dest_shadow_positon_inv['new_inventory'],$inventory_data['current_bid_value']);
								}*/


							}

						}
					}

				//die('end of checking for this parentid :: '.$parentid);
			}
			
			//echo '<br> bokeed data :: '.count($this->booked_data);
			
			//echo '<br> bidding data :: '.count($this->bulk_inventory_booking_data);
			
			//print_r($this->bulk_inventory_booking_data);die;
			
			if(count($this -> bulk_inventory_booking_data) > 0)
			{
				$this-> bulk_inventory_booking();
			}
			
			if(count($this->booked_data)>0)
			{
				
				$return_data = $this-> update_bidding_data();
				
				if(DEBUG_MODE)
				{
					echo '<pre>non fixed post data ';print_r($this->non_fixed_position_category_pincode);
					echo '</pre>';
				}
				
				return $return_data;
				
				
				//echo '<br>total count :: '.count($this->non_fixed_position_category_pincode);
				/*echo '<pre>';
				print_r($this->non_fixed_position_category_pincode);
				foreach($this->non_fixed_position_category_pincode as $parentid=> $values)
				{
					foreach($values as $pincode_data)
					echo '<br> count ::'.count($pincode_data);
				}*/
			}
	   }

	}

}



?>
