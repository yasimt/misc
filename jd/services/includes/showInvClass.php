<?php

class showInvClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{		
		$this->params = $params;		
		$this->setServers();
		
		
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}

		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = strtoupper($this->params['parentid']); //initialize parentid
		}
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}
		
		if(trim($this->params['astatus']) != "")
		{
			$this->astatus  = $this->params['astatus']; // initialize mode  // 0-default 1-Shadow Inv 2-LIVE Inv
		}		
		


	}
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
				
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		$this->finance   		= $db[$data_city]['fin']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];		
		if(DEBUG_MODE)
		{
			echo '<br>dbConDjds_slave:';
			print_r($this->dbConDjds_slave);
			echo '<br>dbConbudget:';
			print_r($this->dbConbudget);
			echo '<br>finance:';
			print_r($this->finance);
		}		
	}
	
	
	
	function showInventory()	
	{		
		switch($this->astatus)
		{
			case 1:	// live inventory
					//$ret['live']   = $this->live_inventory();	
					$live_array   = $this->live_inventory();	
					if($live_array['0']['inv']['error']['code'] == 0 && $live_array['0']['bgt']['error']['code'] == 0 )
					{
						$ret['live']['results'] = $live_array;
						$ret['live']['error']['code'] = "0";
						$ret['live']['error']['msg'] = "";
					}
					else
					{
						$ret['live']['results'] = $live_array;
						$ret['live']['error']['code'] = "1";
						$ret['live']['error']['msg'] = "Data Inconsistent / Data Not Found ";
					}
					break;
			
			case 2:	// shadow inventory
					//$ret['shadow'] = $this->shadow_inventory();
					$shadow_array = $this->shadow_inventory();
						
					if($shadow_array['0']['inv']['error']['code'] == 0 && $shadow_array['0']['bgt']['error']['code'] == 0 )
					{
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['error']['code'] = "0";
						$ret['shadow']['error']['msg'] = "";
					}
					else
					{
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['error']['code'] = "1";
						$ret['shadow']['error']['msg'] = "Data Inconsistent / Data Not Found ";
					}
					
					break;
					
			default	:
					//$ret['live']   = $this->live_inventory();
					$live_array   = $this->live_inventory();	
					if($live_array['0']['inv']['error']['code'] == 0 && $live_array['0']['bgt']['error']['code'] == 0 )
					{
						$ret['live']['results'] = $live_array;
						$ret['live']['error']['code'] = "0";
						$ret['live']['error']['msg'] = "";
					}
					else
					{
						$ret['live']['results'] = $live_array;
						$ret['live']['error']['code'] = "1";
						$ret['live']['error']['msg'] = "Data Inconsistent / Data Not Found ";
					}
					
					//$ret['shadow'] = $this->shadow_inventory();	
					$shadow_array = $this->shadow_inventory();
					$max_dc_date = "";	
					if($shadow_array['0']['inv']['error']['code'] == 0 && $shadow_array['0']['bgt']['error']['code'] == 0 )
					{
						$max_dc_date = $shadow_array['max_dc_date'];
						unset($shadow_array['max_dc_date']);
						
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['max_dc_date']	= 	$max_dc_date;
						$ret['shadow']['error']['code'] = "0";
						$ret['shadow']['error']['msg'] = "";
					}
					else
					{
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['error']['code'] = "1";
						$ret['shadow']['error']['msg'] = "Data Inconsistent / Data Not Found ";
					}
					// live & Shadow Inventory
		}
		
		if(!($ret['shadow']['error']['code']==1 || $ret['live']['error']['code']==1))
		{
			$return_array['results'] = $ret;
			$return_array['error']['code'] = 0;
			$return_array['error']['msg'] = '';
		}
		else
		{
			$return_array['results'] = $ret;
			$return_array['error']['code'] = 1;
			$return_array['error']['msg'] = 'Inventory Not Found';
		}
		return($return_array);
	}
	
	function live_inventory()
	{
			$version     = 0;
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
				$cnt = 0;
				while($row=mysql_fetch_assoc($res))
				{
					//echo '<hr>';
					//print_r($row);
					$catid 		 = $row['catid'];
					$pincode	 = $row['pincode'];
					
					if($orig_pincode != $pincode || $orig_catid != $catid)
					{
						$cnt = 0;
					}
					$live_result[$version]['inv']['results'][$catid]['cnm'] = "";
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['pos'] 		= $row['position_flag'];
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['cnt_f']		= $row['callcount'];
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['bidvalue'] 	= $row['bidvalue'];
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['budget']  	= $row['actual_budget'];
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['inv']      	= $row['inventory'];
					$cnt ++;
					$orig_catid 	= $catid;
					$orig_pincode 	= $pincode;
					
					$catid_array[] = $catid;
				}
				if(count($catid_array)>0)
				{
					$catid_array = array_unique($catid_array);
					$catid_list = implode(",",$catid_array);
					
					$cat_array = $this->get_category_details($catid_list);
					foreach($cat_array as $catid=>$value_array)
					{
						$live_result[$version]['inv']['results'][$catid]['cnm'] = $value_array['cnm'];
					}
				}
				$live_result[$version]['inv']['error']['code'] = "0";
				$live_result[$version]['inv']['error']['msg'] = "";
			}
			else
			{
				$live_result[$version]['inv']['results'] = array();
				$live_result[$version]['inv']['error']['code'] = "1";
				$live_result[$version]['inv']['error']['msg'] = "NO Inventory data found";
			}
			//print_r($inv_values);
			$sql	="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' ORDER BY campaignid";
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
				while($row=mysql_fetch_assoc($res))
				{
					//echo '<hr>';
					//print_r($row);
					$campaignid = $row['campaignid'];
					
					$live_result[$version]['bgt']['results'][$campaignid]['campaignid'] = $campaignid;
					$live_result[$version]['bgt']['results'][$campaignid]['budget'] 	 = $row['budget'];
					$live_result[$version]['bgt']['results'][$campaignid]['balance'] 	 = $row['balance'];
					$live_result[$version]['bgt']['results'][$campaignid]['version'] 	 = $row['version'];
					$live_result[$version]['bgt']['results'][$campaignid]['expired'] 	 = $row['expired'];
				}
				
				$live_result[$version]['bgt']['error']['code'] = "0";
				$live_result[$version]['bgt']['error']['msg'] = "";
			}else
			{
				$live_result[$version]['bgt']['results'] = array();
				$live_result[$version]['bgt']['error']['code'] = "1";
				$live_result[$version]['bgt']['error']['msg'] = "NO Budget data found";
			}
			
			return($live_result);
	}
	
	function shadow_inventory()
	{
			$dealclosed_date = "";
		    $sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' ORDER BY version, catid, pincode, position_flag";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<hr><b>Shadow DB Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
				echo '<hr>';
				
			}
			
			if($res && $num > 0)
			{		
				$cnt = 0;
				while($row=mysql_fetch_assoc($res))
				{
					//echo '<hr>';
					//print_r($row);
					$catid 		 = $row['catid'];
					$pincode	 = $row['pincode'];
					$version	 = $row['version'];
					if($orig_pincode != $pincode || $orig_catid != $catid)
					{
						$cnt = 0;
					}
					$shadow_result[$version]['inv']['results'][$catid]['cnm'] = "";
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['pos'] 		= $row['position_flag'];
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['cnt_f']	= $row['callcount'];
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['bidvalue'] = $row['bidvalue'];
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['budget']   = $row['actual_budget'];
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['inv']      = $row['inventory'];
					$cnt ++;
					$orig_catid 	= $catid;
					$orig_pincode 	= $pincode;
					$catid_array[] = $catid;
					$version_array[] = $version;
					
					$shadow_result[$version]['inv']['error']['code'] = "0";
					$shadow_result[$version]['inv']['error']['msg'] = "";
				}
				
				if(count($catid_array)>0)
				{
					$catid_array = array_unique($catid_array);
					$catid_list = implode(",",$catid_array);
					
					$cat_array = $this->get_category_details($catid_list);
					foreach($shadow_result as $version=>$version_val_array)
					{
						foreach($version_val_array['inv']['results'] as $catid=>$cat_val_array)
						{
							$shadow_result[$version]['inv']['results'][$catid]['cnm'] = $cat_array[$catid]['cnm'];
						}
					}
				}
			}else
			{
				$version =0;
				$shadow_result[$version]['inv']['results'] = array();
				$shadow_result[$version]['inv']['error']['code'] = "1";
				$shadow_result[$version]['inv']['error']['msg'] = "NO Inv data found";
			}
			
			if(count($version_array)>0)
			{
				$version_array = array_unique($version_array);
				$version_list = implode(",",$version_array);
			
				//print_r($inv_values);
				$sql="select * from tbl_bidding_details_summary where parentid ='".$this->parentid."' AND version in (".$version_list.") AND dealclosed_flag=1";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
				$num		= mysql_num_rows($res);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>Shadow Finance DB Query:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.$num;
					echo '<br><b>Error:</b>'.$this->mysql_error;
					
				}
				$max_dealclosed_date = "0000-00-00 00:00:00";
				if($res && $num > 0)
				{		
					while($row=mysql_fetch_assoc($res))
					{
						//echo '<hr>';
						//print_r($row);
						$version = $row['version'];

						$campaignid = 1;
						$shadow_result[$version]['bgt']['results'][$campaignid]['campaignid'] = $campaignid;
						$shadow_result[$version]['bgt']['results'][$campaignid]['budget'] = $row['actual_package_budget'];
						$shadow_result[$version]['bgt']['results'][$campaignid]['balance'] = 0;
						$shadow_result[$version]['bgt']['results'][$campaignid]['version'] = $version;
						$shadow_result[$version]['bgt']['results'][$campaignid]['expired'] = 0;
						
						$campaignid = 2;
						$shadow_result[$version]['bgt']['results'][$campaignid]['campaignid'] = $campaignid;
						$shadow_result[$version]['bgt']['results'][$campaignid]['budget'] = $row['actual_fp_budget'];
						$shadow_result[$version]['bgt']['results'][$campaignid]['balance'] = 0;
						$shadow_result[$version]['bgt']['results'][$campaignid]['version'] = $version;
						$shadow_result[$version]['bgt']['results'][$campaignid]['expired'] = 0;
						
						$campaignid = 7;
						$shadow_result[$version]['bgt']['results'][$campaignid]['campaignid'] = $campaignid;
						$shadow_result[$version]['bgt']['results'][$campaignid]['budget'] = $row['actual_regfee_budget'];
						$shadow_result[$version]['bgt']['results'][$campaignid]['balance'] = 0;
						$shadow_result[$version]['bgt']['results'][$campaignid]['version'] = $version;
						$shadow_result[$version]['bgt']['results'][$campaignid]['expired'] = 0;
						
						$dealclosed_date = $row['dealclosed_on'];
						$shadow_result[$version]['dc_date'] = $dealclosed_date;
						$shadow_result[$version]['updatedby'] = $row['updatedby'];
						$shadow_result[$version]['username'] = $row['username'];
						$shadow_result[$version]['bgt']['error']['code'] = "0";
						$shadow_result[$version]['bgt']['error']['msg'] = "";
						
						if(DEBUG_MODE)
						{
							echo '<br>max_dealclosed_date->'.$max_dealclosed_date;
							echo '<br>dealclosed_date->'.$dealclosed_date;
							echo '<br>strtotime(dealclosed_date)->'.strtotime($dealclosed_date);
						}
						if(strtotime($dealclosed_date)>strtotime($max_dealclosed_date))
						{
							$max_dealclosed_date = $dealclosed_date;
							if(DEBUG_MODE)
							{
								echo '<br>Here max_dealclosed_date changed->'.$max_dealclosed_date;
							}
						}
					}
					
				}else
				{
					$version =0;
					$shadow_result[$version]['bgt']['results'] = array();
					$shadow_result[$version]['bgt']['error']['code'] = "1";
					$shadow_result[$version]['bgt']['error']['msg'] = "NO Budget data found";
				}	
			}
			
			$shadow_result['max_dc_date'] = $max_dealclosed_date;
			return($shadow_result);
	}	
	function get_category_details($catids)
	{
		$sql="select category_name, national_catid, catid, if((business_flag&1)=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract 
		from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Category Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res_area && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res_area))
			{
				//print_r($row);
				$catid = $row['catid'];
				$ret_array[$catid]['cnm'] 		= $row['category_name'];
				$ret_array[$catid]['cid'] 		= $row['catid'];
				$ret_array[$catid]['nid'] 		= $row['national_catid'];
				$ret_array[$catid]['b2b_flag']  = $row['b2b_flag'];
				$ret_array[$catid]['bfc']  = $row['block_for_contract'];
			}
		}
		return($ret_array);
	}
	
	function get_pincode_details($pincodes)
	{
		$sql="select pincode, substring_index(group_concat(main_area order by callcnt_perday desc SEPARATOR '#'),'#',1) as areaname
		from tbl_areamaster_consolidated_v3 where pincode in (".$pincodes.") group by pincode";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res_area && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res_area))
			{
				//print_r($row);
				$pincode = $row['pincode'];
				$ret_array[$pincode]['pincode'] = $row['pincode'];
				$ret_array[$pincode]['anm'] 	= $row['areaname'];
			}
		}
		return($ret_array);
	}
	
	
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
	############### functionality - cum value calc, min inventory managemnet and show bidders string manipulation #####################3
	function bidder_implode_string($bidder_array, $ip_parentid, $catid, $pincode, $position_flag, $status_flag, $actual_inventory)
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
									
						$sql_bcd_ddg = "UPDATE tbl_bidding_details SET 
												LCF = ".($attributes['cum'] - $attributes['inv']).",
												HCF = ".$attributes['cum'].",
												".$actual_inventory." 
										WHERE parentid='".$this->parentid."'  AND catid='".$catid."' AND pincode='".$pincode."' AND position_flag=".$position_flag;
						$res_bcd_ddg 	= parent::execQuery($sql_bcd_ddg, $this->finance);
						if(DEBUG_MODE)
						{
							echo '<br><b>LCF/HCF Query:</b>'.$sql_bcd_ddg;
							echo '<br><b>Result Set:</b>'.$res_bcd_ddg;
							echo '<br><b>Error:</b>'.$this->mysql_error;
						}
					}
				}
				$cummlative += $attributes['inv'];
				$pids[] = $parentid."-".$attributes['bid']."-".$attributes['inv']."-".$attributes['cum']."-".$attributes['act_inv'];				
				//print_r($pids);
			}
			if($attributes['existing']==1)
			{
				$this->lcf_hcf_array[$catid][$pincode][$position_flag]['l'] = ($attributes['cum'] - $attributes['inv']);
				$this->lcf_hcf_array[$catid][$pincode][$position_flag]['h'] = $attributes['cum'];
			}
		}
		
		if($status_flag == 2 && (1 - $actual_inventory) <= 0)
		{	
			$pos_str = 'pos'.$position_flag;
										
			$column_cum_ratio		= $pos_str."_cumulative_ddg_ratio";
			$column_contribution 	= $pos_str."_contribution";

			$sql_update_booking = "UPDATE tbl_clients_ddg_contribution SET 
										".$column_cum_ratio."    = 0,
										".$column_contribution." = 0
									WHERE parentid='PFREEINVENTORY' AND catid='".$catid."' AND pincode='".$pincode."' ";
			$res_update_booking 	= parent::execQuery($sql_update_booking, $this->finance);
			if(DEBUG_MODE)
			{
				echo '<br><b>tbl_clients_ddg_contribution Query:</b>'.$sql_update_booking;
				echo '<br><b>Result Set:</b>'.$res_update_booking;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
		}
		
		if($pids)
			$bidders_string = implode(",",$pids);
		//echo '<br>bidders_string->'.$bidders_string;		
		$return_array['b_string'] = $bidders_string;
		$return_array['min_inv'] = $min_inventory;
		//echo '<br>return array->';
		//print_r($return_array);
		return $return_array;
	}

}



?>
