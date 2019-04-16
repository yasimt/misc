<?php

class invMgmtClass extends DB
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
	
	var  $b2c_catpin_minval = 1;
	var  $b2b_cat_minval = 50;
	
	function __construct($params)
	{		
		$this->params = $params;		
		$this->setServers();
		
		$this->categoryClass_obj 	= new categoryClass();
		$this->companyClass_obj 	= new companyClass();
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
			$this->astatus  = $this->params['astatus']; // initialize mode // 1-blocking 2-booking(LIVE) 3-checking
		}		
		
		if(trim($this->params['astate']) != "")
		{
			$this->astate  = $this->params['astate']; // initialize mode // 1-dealclose 2-balance readjustment 3-financial approval 4-expiry 5-release 6-part payment 7-ecs 8-carryforward 10-category/pin deletion LIve 11-category/pin deletion Shadow 15- pure package entries 16- additon & removal of categories - pure package
		}
		
		if(trim($this->params['version']) != "")
		{
			$this->version  = $this->params['version']; // version of booking to seek
		}
		
		if($this->params['i_data'] != "")
		{
			$this->idata  = json_decode($this->params['i_data'], true); // inventory release data
		}
		
		if(trim($this->params['i_reason']) != "")
		{
			$this->i_reason  = $this->params['i_reason']; // reason for inventory release
		}
		
		if(trim($this->params['i_updatedby']) != "")
		{
			$this->i_updatedby  = $this->params['i_updatedby']; // updated by inventory release
		}
		
		if(trim($this->params['bidperday']) != "")
		{
			$this->bidperday  = $this->params['bidperday']; // bidperday for pure package entries
		}
		
		if(trim($this->params['catlist']) != "")
		{
			$this->catlist  = $this->params['catlist']; // bidperday for pure package entries
		}
		
		if(trim($this->params['module']) != "")
		{
			$this->module  = $this->params['module']; // module
		}
		
		if(trim($this->params['source']) != "")
		{
			$this->source  = $this->params['source']; // source
		}
		
		if(trim($this->params['next_dealclose_version']) != "")
		{
			$this->next_dealclose_version  = $this->params['next_dealclose_version']; // source
		}
		
		//$this->source = 'approval';
		if(($this->astate == 6 ||$this->astate == 7 ||$this->astate == 8) && $this->astatus == 2)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Part payment / Ecs /Carry Forward Cannot have Status 2";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if(($this->astate == 4 ||$this->astate == 5) && $this->astatus != 2)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Expiry & Inv Release Should have Status 2";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if(($this->astate == 2 ||$this->astate == 3) && $this->astatus == 1)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Fin Approval & Balance Readjsutment Should not have Status 1";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if(($this->astate == 10 ||$this->astate == 11) && $this->astatus != 2)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Inv Deletion Should have Status 2";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if($this->astate == 11 && (count($this->idata)>0 && strtoupper($this->module)!='CS'))
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Partial Deletion not allowed";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if(($this->astate == 10 && count($this->idata)==0))
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Live inv Deletion - Inventoary data blank";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if($this->astate == 15 && $this->bidperday<=0)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Pure package Entries : Bidperday cannot be zero";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if($this->astate == 16 && ($this->bidperday<=0 || $this->catlist==""))
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Pure package Entries : Bidperday or Category Missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}

		if($this->astate == 17 && ($this->bidperday<=0 || $this->params['primarycampaignid']==0 || $this->params['dependentcampaignid']==0))
		{
			
			//echo "<br>astate". $this->astate == 17  ."--bidperday--" . $this->bidperday."---primarycampaignid --".$this->params['primarycampaignid']."----dependentcampaignid=".$this->params['dependentcampaignid'];
			
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Dependent Campaign Entries : Bidperday or primarycampaignid or dependentcampaignid missing";
			
			//$resultstr= json_encode($result);
			$resultstr= json_encode($this->params);
			print($resultstr);
			die;
		}		
		
		if($this->astate == 1 ||$this->astate == 2 || $this->astate == 3  || $this->astate == 11)
		{
			$this->summary_para();
		}
		
		if(trim($this->params['ecs_flag']) != "")
		{
			$this->ecs_flag  = $this->params['ecs_flag']; // ecs_flag
		}
		
		if(trim($this->params['fin_approval']) != "")
		{
			$this->fin_approval  = $this->params['fin_approval']; // fin_approval
		}
		
		
		if(trim($this->params['instrument_type']) != "")
		{
			$this->instrument_type  = $this->params['instrument_type']; // payu payment type - if set then inventory will be released two days after booking
		}
		
		$this->lcf_hcf_array = array();
		$this->sendmail_obj = new sendMailClass();
		$this->full_release = 0;
		$this->cnt_campaign_1 = 0;
		$this->cnt_campaign_2 = 0;
		$this->tbd_duration = 0; // tbl_bidding_details duration
		$this->stime = date('Y-m-d H:i:s');
		$this->inter_factor = array();
		$this->shadow_factor = 0;
		$this->reverse_readjust_factor =0;
		$this->live_src ="";
		//echo json_encode('const'); exit;
	}
	#### fetch data from summary table; 
	function summary_para()
	{
		if($this->astate==1)
			$xtra_cond = " AND dealclosed_flag=0";
		elseif($this->astate==3)
			$xtra_cond = " AND dealclosed_flag=1";
			
		$sql="select * from tbl_bidding_details_summary where parentid ='".$this->parentid."' and version = '".$this->version."'".$xtra_cond;
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Summary Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res && $num > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				if(DEBUG_MODE)
				{
					echo '<hr>';
					print_r($row);
			    }
				$this->tenure 		 = $row['duration'];
				$this->docid  		 = $row['docid'];
				$this->data_city 	 = $row['data_city'];
				$this->actual_budget = $row['actual_total_budget'];
				$this->sys_budget 	= $row['sys_total_budget'];
				$this->sphinx_id 	 = $row['sphinx_id'];
				$this->updatedby 	 = $row['updatedby'];
				$this->username		 = $row['username'];
				$this->latitude 	 = $row['latitude'];
				$this->longitude 	 = $row['longitude'];
				$this->physical_pincode 	 = $row['pincode'];
				$this->dealclosed_flag 	 = $row['dealclosed_flag'];
				$this->dealclosed_on 	 = $row['dealclosed_on'];
				$this->tenure_f      = $this->tenure;
				
				$this->readjust_total_budget  = $row['readjust_total_budget'];
				$this->readjust_duration      = $row['readjust_duration'];
				
				
				$tmp_tenure = $this->tenure /365;
				$tmp_tenure_f = $this->tenure /365;
				if($row['actual_fp_budget'] > 0 && $this->tenure_f  && $tmp_tenure>1)
				{
					// pdg flow
					if ($tmp_tenure > 1 && $tmp_tenure <= 2)
						$tmp_tenure_f = 2.5;
					elseif ($tmp_tenure > 2 && $tmp_tenure <= 3)
						$tmp_tenure_f = 3;
					elseif ($tmp_tenure > 3 && $tmp_tenure <= 5)
						$tmp_tenure_f = 4;
					elseif ($tmp_tenure > 5) {
						if (strtoupper($this->data_city) == 'MUMBAI')
							$tmp_tenure_f = 6;
						else
							$tmp_tenure_f = 5;
					}else
						$tmp_tenure_f = 1;
					$this->tenure_f      = $tmp_tenure_f*365;
				}
				elseif($row['actual_fp_budget'] == 0 && $tmp_tenure>1)
				{
					// pure package flow
					if ($tmp_tenure == 10) 
							$tmp_tenure_f = 1;
					$this->tenure_f      = $tmp_tenure_f*365;
				}
				
			}
		}else
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Entry in bidding Summary Not Found/Mismatch";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}

		if($this->actual_budget <= 0 || $this->sys_budget <= 0)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Entry in bidding Summary - Actual Budget Or System Budget Is null";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}			
		
		if($this->tenure<=0 || $this->dealclosed_flag<0 || $this->dealclosed_flag>1 || empty($this->physical_pincode))
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Entry in bidding Summary - Tenure/Dealclosed Flag/Pincode Invalid";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}			
		
	}	
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
				
		$this->dbConDjds	= $db[$data_city]['d_jds']['master'];
		$this->finance   		= $db[$data_city]['fin']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];		
		if(DEBUG_MODE)
		{
			echo '<br>dbConDjds:';
			print_r($this->dbConDjds);
			echo '<br>dbConbudget:';
			print_r($this->dbConbudget);
			echo '<br>finance:';
			print_r($this->finance);
		}		
	}
	
	function deltaInventory()
	{
		$pin_arr = array();
		$cat_arr = array();
		if($this->astate == 4)
		{
			$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' ORDER BY catid, pincode";
			$res 	= parent::execQuery($sql, $this->finance);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql;
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
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					//print_r($pin_array);
					$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
					$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
					$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
					$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
					$inv_seeked[$catid][$pincode][$position_flag]['oi']   = $row['inventory'];
					$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
					$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
					$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
					$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;		
				}
			}
			
			$ddg_delaclosed_cond = " AND pincode!=999999 AND(DATEDIFF(CURRENT_DATE,booked_date)<=9 or  DATEDIFF(CURRENT_DATE,ecs_booked_date)<=15) AND booked_by NOT IN ('iro0002','div001','007247','admin','004650','010393','cs00001','mkt00001') " ;
			$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' ".$ddg_delaclosed_cond." ORDER BY version, catid, pincode";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Shadow Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($res && $num > 0)
			{		

				while($row=mysql_fetch_assoc($res))
				{
					
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					
					if(DEBUG_MODE)
					{
						echo '<hr>';
						print_r($row);
						print_r($inv_seeked[$catid][$pincode]);
					}
					
					if(is_array($inv_seeked[$catid][$pincode][$position_flag])/* && $position_flag == $inv_seeked[$catid][$pincode]['p']*/)
					{
						if($row['inventory'] < $inv_seeked[$catid][$pincode][$position_flag]['i'])
						{
							if(DEBUG_MODE)
								echo '<br>Modification:';
							$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 2; // 1-release inv 2-modifiy inv  3-no action
						}
						else
						{
							if(DEBUG_MODE)
								echo '<br>No Action:';
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 3; // 1-release inv 2-modifiy inv  3-no action
						}
					}
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;		
				}
			}
		}elseif($this->astate == 5)
		{
			
			$num_of_days  = (strtolower($this->instrument_type) == 'payu')?2:9;//if payu instrument type is found then deal close inventory will be released in 2 days
			
			$ddg_delaclosed_cond = " 
			AND pincode!=999999 AND if(booked_date is not null AND booked_date != '' AND booked_date != '0000-00-00 00:00:00', DATEDIFF(CURRENT_DATE,booked_date)>".$num_of_days.",1) AND if(ecs_booked_date is not null  AND ecs_booked_date!='' AND ecs_booked_date!='0000-00-00', DATEDIFF(CURRENT_DATE,ecs_booked_date)>15,1) AND booked_by NOT IN ('iro0002','div001','007247','admin','004650','010393','cs00001','mkt00001') " ;
			
			$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' ".$ddg_delaclosed_cond." ORDER BY version, catid, pincode";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Shadow Query:</b>'.$sql;
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
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					//print_r($pin_array);
					$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
					$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
					$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
					$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
					$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
					$inv_seeked[$catid][$pincode][$position_flag]['oi'] 	  = $row['inventory'];
					$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
					$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
					$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;		
				}
			}
			//print_r($inv_seeked);
			$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=2 AND expired=0";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 0)
			{
				$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->finance);
				$num		= mysql_num_rows($res);
				
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB  Query:</b>'.$sql;
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
						$catid 	 = $row['catid']; 
						$pincode = $row['pincode']; 
						$position_flag = $row['position_flag']; 
						//print_r($pin_array);
						if(is_array($inv_seeked[$catid][$pincode][$position_flag])/* && $position_flag == $inv_seeked[$catid][$pincode]['p']*/)
						{
							if($row['inventory'] < $inv_seeked[$catid][$pincode][$position_flag]['i'])
							{
								$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 2; //1-release inv 2-modifiy inv  3-no action
							}
							else
							{
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 3; // 1-release inv 2-modifiy inv  3-no action
							}
						}
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;		
					}
				}
			} // if db finance is unexpired
		}
		
		$return_res['inv_data']  = $inv_seeked;
		$return_res['pin_data']  = array_unique($pin_arr);
		$return_res['cat_data']  = array_unique($cat_arr);
		return($return_res); 
	}
	
	function liveInventory()
	{
		
		$sql="select * from tbl_bidding_details_summary where parentid ='".$this->parentid."' and version = '".$this->version."'";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Summary Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res && $num > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				if(DEBUG_MODE)
				{
					echo '<hr>';
					print_r($row);
			    }
				
				$temp_actual_budget = $row['actual_total_budget'];
				$temp_sys_budget 	= $row['sys_total_budget'];
				$temp_readjust_total_budget  = $row['readjust_total_budget'];
				$temp_readjust_duration      = $row['readjust_duration'];
			}
		}
		
		if($temp_readjust_total_budget>0)
		{
			$this->reverse_readjust_factor = 1 + (($temp_actual_budget - $temp_readjust_total_budget)/$temp_readjust_total_budget);
		}
		else
		{
			$this->reverse_readjust_factor = 1;
		}
		$pin_arr = array();
		$cat_arr = array();
		$sql	="select * from tbl_bidding_details where parentid ='".$this->parentid."' AND version='".$this->version."' ORDER BY catid, pincode";
		$res 	= parent::execQuery($sql, $this->finance);
		$num	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;
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
				$catid 	 = $row['catid']; 
				$pincode = $row['pincode']; 
				$position_flag = $row['position_flag']; 
				$this->docid = $row['docid']; 
				//print_r($pin_array);
				$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
				$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
				$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
				$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
				$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
				$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
				$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget']*$this->reverse_readjust_factor;
				$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
				$pin_arr[] = $pincode;
				$cat_arr[] = $catid;	
				$this->live_src = "tbd";	
			}
		}else
		{
				$sql="select * from tbl_bidding_details_expired where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->finance);
				$num		= mysql_num_rows($res);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB Expired Query:</b>'.$sql;
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
						$catid 	 = $row['catid']; 
						$pincode = $row['pincode']; 
						$position_flag = $row['position_flag']; 
						$this->docid = $row['docid']; 
						//print_r($pin_array);
						$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
						$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
						$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
						$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
						$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
						$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget']*$this->reverse_readjust_factor;
						$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;	
						$this->live_src = "tbde";	
					}
				}else
				{
					$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid, pincode";
					$res 	= parent::execQuery($sql, $this->dbConbudget);
					$num		= mysql_num_rows($res);
					
					if(DEBUG_MODE)
					{
						echo '<br><b>DB Shadow Query:</b>'.$sql;
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
							$catid 	 = $row['catid']; 
							$pincode = $row['pincode']; 
							$position_flag = $row['position_flag']; 
							$this->docid = $row['docid']; 
							//print_r($pin_array);
							$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
							$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
							$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
							$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
							$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;	
							$this->live_src = "tbds";	
						}
					}else
					{
						$sql="select * from tbl_bidding_details_shadow_archive where parentid ='".$this->parentid."' and version = '".$this->version."' GROUP BY catid, pincode ORDER BY catid, pincode";
						$res 	= parent::execQuery($sql, $this->dbConbudget);
						$num		= mysql_num_rows($res);
						
						if(DEBUG_MODE)
						{
							echo '<br><b>DB Shadow Archive Query:</b>'.$sql;
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
								$catid 	 = $row['catid']; 
								$pincode = $row['pincode']; 
								$position_flag = $row['position_flag']; 
								$this->docid = $row['docid']; 
								//print_r($pin_array);
								$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
								$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
								$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
								$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
								$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
								$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
								$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
								$pin_arr[] = $pincode;
								$cat_arr[] = $catid;	
								$this->live_src = "tbdsa";	
								}
						}else
						{
							$sql="select * from tbl_bidding_details_shadow_archive_historical where parentid ='".$this->parentid."' and version = '".$this->version."' GROUP BY catid, pincode ORDER BY catid, pincode";
							$res 	= parent::execQuery($sql, $this->dbConbudget);
							$num		= mysql_num_rows($res);
							
							if(DEBUG_MODE)
							{
								echo '<br><b>DB Shadow Archive Query:</b>'.$sql;
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
									$catid 	 = $row['catid']; 
									$pincode = $row['pincode']; 
									$position_flag = $row['position_flag']; 
									$this->docid = $row['docid']; 
									//print_r($pin_array);
									$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
									$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
									$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
									$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
									$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
									$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
									$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
									$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
									$pin_arr[] = $pincode;
									$cat_arr[] = $catid;	
									$this->live_src = "tbdsah";	
									}
							}
						
						} // historical else
					} // shadow archive else
				} // bidding details shadow else
			}// bidding expired else
		
		
		$return_res['inv_data']  = $inv_seeked;
		$return_res['pin_data']  = array_unique($pin_arr);
		$return_res['cat_data']  = array_unique($cat_arr);
		return($return_res); 
	}
	
	function releaseInventory()
	{
		$pin_arr = array();
		$cat_arr = array();
		$release_inv = array();
		//print_r($this->idata);
		if(is_array($this->idata))
		{
			foreach($this->idata as $catid=>$r_data)
			{
				if(DEBUG_MODE)
				{
					echo '<br>Catid:'.$catid;
					print_r($r_data);
				}
				for($i=0;$i<count($r_data);$i++)
				{
					$pin = $r_data[$i]['pin'];
					$pos = $r_data[$i]['pos'];
					$release_inv[$catid][$pin][$pos]['s'] = 1;
					$release_inv[$catid][$pin][$pos]['f'] = 0;
				}
			}
		}		
		if($this->astate == 10)
		{
			$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid in (1,2) /*AND expired=0*/";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>Finance  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 0)
			{
				$sql	="select * from tbl_bidding_details where parentid ='".$this->parentid."' ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->finance);
				$num	= mysql_num_rows($res);
				
				if(DEBUG_MODE)
				{
					print_r($release_inv);
					echo '<br><b>(10)-DB Query:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.$num;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				if($res && $num > 0)
				{		
					while($row=mysql_fetch_assoc($res))
					{
						$catid 	 = $row['catid']; 
						$pincode = $row['pincode']; 
						$position_flag = $row['position_flag']; 
						$this->docid = $row['docid']; 
						if(DEBUG_MODE)
						{
							//echo '<hr>';
							//print_r($row);
							//print_r($release_inv[$catid][$pincode][$position_flag]);
							//echo '<br>If:'.is_array($release_inv[$catid][$pincode][$position_flag]);
						}
						
						if(is_array($release_inv[$catid][$pincode][$position_flag]))
						{
							$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
							$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
							$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
							$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['oi']   = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
							$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
							$inv_seeked[$catid][$pincode][$position_flag]['bpd']  = $row['bidperday'];
							
							$release_inv[$catid][$pincode][$position_flag]['f'] = 1;
							
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;		
						}
					}
				}
			}else
			{
				$result['result'] = $shadow_inv;
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Contract Expired- NO Fixed Position/Package Budget Availabe";
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			$shadow_inv =  array();
			$sql	="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' ORDER BY catid, pincode";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num	= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>(10)-DB Shadow Query:</b>'.$sql;
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
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					$this->docid = $row['docid']; 
					//print_r($pin_array);
					if(is_array($release_inv[$catid][$pincode][$position_flag]))
					{
						$shadow_inv[$catid][$pincode][$position_flag]['p'] 	  = $row['position_flag'];
						$shadow_inv[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
						$shadow_inv[$catid][$pincode][$position_flag]['v'] 	  = $row['version'];
						/*
						if($row['inventory'] < $inv_seeked[$catid][$pincode][$position_flag]['i'])
						{
							$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 2; //1-release inv 2-modifiy inv  3-no action
						}
						else
						{
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 3; // 1-release inv 2-modifiy inv  3-no action
						}
						*/
					}
				}
			}
			
			if(count($shadow_inv)>0)
			{
				$result['result'] = $shadow_inv;
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Entry in Shadow table avaiable- Please release Inventory from Shadow before proceeding";
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			//echo 'here';
			//print_r($inv_seeked);
		}
		elseif($this->astate == 11)
		{
			if(count($release_inv)>0)
			{
				$this->full_release = 0;
			}
			else
			{
				$this->full_release = 1;
				$r = $this->financialSanity();
				if(DEBUG_MODE)
				{
					echo '<br>Financial Sanity Check:'.$r;
				}
			}
			
			$sql	="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND '".$this->version."' ORDER BY version, catid, pincode";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num	= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Shadow Query:</b>'.$sql;
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
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					//print_r($pin_array);
					if(is_array($release_inv[$catid][$pincode][$position_flag]) || $this->full_release==1)
					{
						$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
						$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
						$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
						$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
						$inv_seeked[$catid][$pincode][$position_flag]['oi']   = $row['inventory'];
						$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
						$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
						$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['bpd']  = $row['actual_budget']/$this->tenure;
						$release_inv[$catid][$pincode][$position_flag]['f'] = 1;
						
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;		
					}
				}
			}
			
			$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=2 AND expired=0";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 0)
			{
				$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->finance);
				$num		= mysql_num_rows($res);
				
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB  Query:</b>'.$sql;
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
						$catid 	 = $row['catid']; 
						$pincode = $row['pincode']; 
						$position_flag = $row['position_flag']; 
						//print_r($pin_array);
						if(is_array($inv_seeked[$catid][$pincode][$position_flag])/* && $position_flag == $inv_seeked[$catid][$pincode]['p']*/)
						{
							
							if($row['inventory'] < $inv_seeked[$catid][$pincode][$position_flag]['i'])
							{
								$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 2; //1-release inv 2-modifiy inv  3-no action
							}
							else
							{
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 3; // 1-release inv 2-modifiy inv  3-no action
							}
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;		
						}
					}
				}
			} // if db finance is unexpired
		} // state 11
	
		$return_res['inv_data']  = $inv_seeked;
		$return_res['pin_data']  = array_unique($pin_arr);
		$return_res['cat_data']  = array_unique($cat_arr);
		return($return_res); 
	}
	
	function financialSanity()
	{
		if($this->astate == 11)
		{
			/*
			1. No Balance In Campaignid=2 AND
			2. ECS Stopped AND
			3. No Inst Pending for Approval within the last 30 days.
			*/
			// ACTIVE ECS CHECKING
			$sql="select * from db_ecs.ecs_mandate where parentid ='".$this->parentid."' AND version ='".$this->version."' AND activeflag=1 AND deactiveflag=0 AND ecs_stop_flag=0";
			$res 	= parent::execQuery($sql, $this->finance);
			$num		= mysql_num_rows($res);
			
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($res && $num > 0)
			{		

				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Active Mandate Present Against this Parentid:".$this->parentid." And Version:".$this->version;
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			// Active Instrument checking
			$sql	=  "select * from payment_instrument_summary where parentid ='".$this->parentid."' AND version ='".$this->version."' AND approvalstatus=0";
			$res 	=  parent::execQuery($sql, $this->finance);
			$num	=  mysql_num_rows($res);
			
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($res && $num > 0)
			{		

				while($row=mysql_fetch_assoc($res))
				{
					$result['result'] = array();
					$result['error']['code'] = 1;
					$result['error']['msg'] = "Active Instrument Present Against this Parentid:".$this->parentid." And Version:".$this->version;
					$resultstr= json_encode($result);
					print($resultstr);
					die;
				}
			}
			
			//  payu entry checking start
			unset($res);
			unset($num);
			global $db;
			$this->db_payment   			= $db['db_payment'];
			//$sql	=  "select * from genio_online_transactions where parentid ='".$this->parentid."' AND version ='".$this->version."'";
			$sql	=  "SELECT parentid FROM genio_online_transactions WHERE parentid ='".$this->parentid."' AND version ='".$this->version."' AND proc_flag = 0 AND dev_server = 0 AND fin_entry_flag = 0 AND  inst_delete_flag=0 AND dealclose_flag = 1;";
			$res 	=  parent::execQuery($sql, $this->db_payment);
			$num	=  mysql_num_rows($res);

			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			

			if($res && $num > 0)
			{		

				while($row=mysql_fetch_assoc($res))
				{
					$result['result'] = array();
					$result['error']['code'] = 1;
					$result['error']['msg'] = "Active Payu Instrument Present Against this Parentid:".$this->parentid." And Version:".$this->version;
					$resultstr= json_encode($result);
					print($resultstr);
					die;
				}
			}

			//  payu entry checking end
		}
		
		return 1;
	}
	
	function createEntries()
	{
		$cat_arr   = array();
		$pincode   = "";
		$ins_array = array();

		// if the same versions contains pdg also then it will not be considerd as 	pure package
		$sql="select parentid from payment_apportioning where parentid ='".$this->parentid."' AND version ='".$this->version."' AND campaignid=2 AND budget>0";
		$res 	= parent::execQuery($sql, $this->finance);
		$num	= mysql_num_rows($res);
		if(DEBUG_MODE)
		{
			echo '<br><b>DB  Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($num > 0)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "In payment_apportioning PDG found for Parentid ".$this->parentid." Versions ".$this->version;
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if($this->astate==15 || $this->astate==16)
		{
			$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=2 AND balance>0";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 0)
			{
				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Active Fixed Postion balance present for:".$this->parentid;
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			
			/*
			$sql="select distinct pincode from tbl_bidding_details where parentid ='".$this->parentid."' AND campaignid = 1 ";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 1)
			{
				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Multi Pincode Package - cannot make pure package entries for:".$this->parentid;
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			*/
			$sql="select * from tbl_payment_type_dealclosed where parentid ='".$this->parentid."' AND payment_type like '%flexi_selected_user%'";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($num > 0)
			{
				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Flexi Package - cannot make pure package entries for:".$this->parentid;
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			
		}elseif($this->astate==17)
		{
			// dependent campaign condition

			$primarycampaignid = $this->params['primarycampaignid'];
			$dependentcampaignid = $this->params['dependentcampaignid'];
			
			$primarycampaignidsql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=".$primarycampaignid." AND balance>0";
			$primarycampaignidres 	= parent::execQuery($primarycampaignidsql, $this->finance);
			$num	= mysql_num_rows($primarycampaignidres);
			if(DEBUG_MODE)
			{
				echo '<br><b>DB  Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}

			if($num == 0)
			{
				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "balance in not present on primary campaignid : ".$primarycampaignid." for:".$this->parentid;
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			
		}
		
		if($this->astate==15 || $this->astate==17)
		{
			//$sql	= "select parentid, catidlineage from db_iro.tbl_companymaster_extradetails where parentid ='".$this->parentid."'";
			//$res 	= parent::execQuery($sql, $this->dbConDjds);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'extra_det_id,gen_info_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'parentid,catidlineage,pincode,latitude,longitude,paid';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'invMgmtClass';

			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}

			//$num	= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>Company params:</b>';
				echo "<pre>";print_r($comp_params);
				echo '<br><b>Result Set:</b>'.$comp_api_res;
				//echo '<br><b>Num Rows:</b>'.$num;
				//echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
			{		
				//while($row=mysql_fetch_assoc($res))
				//{
					//echo '<hr>';
					//print_r($row);
					$row = $comp_api_arr['results']['data'][$this->parentid];
					$catidlineage =  str_replace("/","",$row['catidlineage']);
				//}
				$cat_arr = explode(",",$catidlineage);
			}
		}
		else
		{
			$cat_arr = explode(",",$this->catlist);
		}
		
		if($this->astate==15 || $this->astate==17){
			$tbl_name = "db_iro.tbl_companymaster_generalinfo";
		}
		else{
			$tbl_name = "db_iro.tbl_companymaster_generalinfo_shadow";
		}
			
		$sql	= "select parentid, pincode, paid, latitude, longitude, longitude from ".$tbl_name." where parentid ='".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>ExtrDetails Query:</b>'.$sql;
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
				$pincode =  $row['pincode'];
				$this->pincode =  $row['pincode'];
				$this->latitude =  $row['latitude'];
				$this->longitude =  $row['longitude'];
				
			}
		}
		if($this->astate==15 || $this->astate==17){
			$row = $comp_api_arr['results']['data'][$this->parentid];
			$pincode 			=  $row['pincode'];
			$this->pincode 		=  $row['pincode'];
			$this->latitude 	=  $row['latitude'];
			$this->longitude	=  $row['longitude'];
		}
		
		$sql	= "select parentid, docid from db_iro.tbl_id_generator where parentid ='".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>ExtrDetails Query:</b>'.$sql;
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
				$this->docid =  $row['docid'];
			}
		}
		if(DEBUG_MODE)
		{
			echo '<br>Category Array:';
			print_r($cat_arr);
			echo '<br>pincode:'.$this->pincode;
			echo '<br>latitude:'.$this->latitude;
			echo '<br>longitude:'.$this->longitude;
			echo '<br>docid:'.$this->docid;
		}
		
		$this->fn_pincode_mapping();
		
		
		if(count($cat_arr)>0)
		{
			$cat_arr  = array_unique($cat_arr);
			$cat_arr  = array_filter($cat_arr);
			$cat_list = implode(",",$cat_arr);
		}
		$pin_list = $this->allarea_pin_list;
		$pin_array = $this->allarea_pin_array;
		if(DEBUG_MODE)
		{
			echo 'category->';
			print_r($cat_arr);
			echo 'pincode->'.$this->allarea_pin_list;
		}
		if(count($cat_arr)==0 || $pin_list=="")
		{
			$return_array['results'] = array();
			$return_array['error']['code'] = "1";
			$return_array['error']['msg']  = "Invalid Category/Pincode";
			$resultstr= json_encode($return_array);
			print($resultstr);
			die;
		}
		
		$cat_array = $this->get_category_details($cat_list);
		
		if(count($cat_array)==0)
		{
			$return_array['results'] = array();
			$return_array['error']['code'] = "1";
			$return_array['error']['msg']  = "NO Biddable categories Available";
			$resultstr= json_encode($return_array);
			print($resultstr);
			die;
		}
		
		if(DEBUG_MODE)
		{
			echo '<br>Biddable Category Array:';
			print_r($cat_array);
			echo '<br>Catlist:'.$cat_list;
			echo '<br>pinlist:'.$pin_list;
			echo '<br>bidperday:'.$this->bidperday;
		}
		
		$sql="select * from tbl_fixedposition_factor where active_flag=1";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num_rows		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			print_r($cat_array);
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
				
				$n_position_factor[$pos] 		= $factor;
				$x_position_factor[$pos] 		= $x_factor;
				$f_position_factor[$pos] 		= $f_factor;
			}
		}
		
		$sql		="select * from tbl_business_uploadrates where city='".$this->data_city."' limit 1";
		$res 		= parent::execQuery($sql, $this->dbConDjds);
		$num_rows	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Biz Upload rates Query:</b>'.$sql;
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
				//	echo '<hr>';
				//	print_r($row);
				}
				$callcnt_growth_rate = 1 + ($row['callcnt_per']/100);
				$city_min_budget 	 = 	$row['cityminbudget'];
				$all_pincode_count   = 	$row['allpincnt'];
				$bidvalue_premium    = 1 + ($row['bidvalue_per']/100);
			}
		}
		
		if(DEBUG_MODE)
		{
			echo '<br>Pos factor';
			print_r($n_position_factor);
			echo '<br>X Pos factor';
			print_r($x_position_factor);
			echo '<br>Final Pos factor';
			print_r($f_position_factor);
			echo '<br>Alloc Summary';
			print_r($alloc_summary_array);
			echo '<br>callcnt_growth_rate:'.$callcnt_growth_rate;			
			echo '<br>bidvalue_premium :'.$bidvalue_premium ;			
			echo '<br>city_min_budget:'.$city_min_budget;
			echo '<br>count pin array:'.count($pin_array);			
		}	
		
		############## dummy values ###################
		foreach($cat_arr as $catid)
		{
			foreach($pin_array as $pincode)
			{
				if(
					(($cat_array[$catid]['cst'] == "L" || $cat_array[$catid]['cst'] == "SZ" || $cat_array[$catid]['cst'] == "Z") && in_array($pincode, $this->allarea_pin_array)) 
					|| 
					(($cat_array[$catid]['cst'] != "L" && $cat_array[$catid]['cst'] != "SZ" && $cat_array[$catid]['cst'] != "Z") && in_array($pincode, $this->non_allarea_pin_array))
				  )
				  {
					if($cat_array[$catid]['b2b_flag']==1)
					{
						$budget_array[$catid][$pincode]['bval'] = 5;
						$budget_array[$catid][$pincode]['bgt']  = 50;
					}
					else
					{
						$budget_array[$catid][$pincode]['bval'] = 1;
						$budget_array[$catid][$pincode]['bgt']  = 1;
					}
				}
			}
		}
	
		$sql		= "select catid, pincode, x_bidvalue, callcount from tbl_fixedposition_pincodewise_bid where catid in (".$cat_list.") and pincode in (".$pin_list.") ORDER BY catid, pincode";
		$res 		= parent::execQuery($sql, $this->dbConbudget);
		$num_rows	= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Inventory Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				//echo '<hr>';
				//print_r($row);
				$catid		= $row['catid'];
				$pincode	= $row['pincode'];
				//$budget_array[$catid]['cnm'] = $cat_array[$catid]['cnm'];
				//$budget_array[$catid]['cst'] = $cat_array[$catid]['cst'];
				if(
					(($cat_array[$catid]['cst'] == "L" || $cat_array[$catid]['cst'] == "SZ" || $cat_array[$catid]['cst'] == "Z") && in_array($pincode, $this->allarea_pin_array)) 
					|| 
					(($cat_array[$catid]['cst'] != "L" && $cat_array[$catid]['cst'] != "SZ" && $cat_array[$catid]['cst'] != "Z") && in_array($pincode, $this->non_allarea_pin_array))
				  )
				{
					  
					$callcount	= $row['callcount'] * $callcnt_growth_rate;
					
					$position_factor = $f_position_factor;
					$b_val = $row['x_bidvalue']*$bidvalue_premium;
						
					if($cat_array[$catid]['b2b_flag']==1)
						$bidvalue	= max(481,$b_val);
					else
						$bidvalue	= max(96,$b_val);
					
						
					$callcount = max($callcount, 1/$all_pincode_count);
					$budget_array[$catid][$pincode]['bval'] = $bidvalue * $position_factor[100];
					if($cat_array[$catid]['b2b_flag']==1)
						$budget_array[$catid][$pincode]['bgt']  = max($bidvalue * $position_factor[100] * $callcount, $this->b2b_cat_minval);
					else
						$budget_array[$catid][$pincode]['bgt']  = max($bidvalue * $position_factor[100] * $callcount, $this->b2c_catpin_minval);
					$budget_array[$catid][$pincode]['cnt']  = $callcount;
				} // If
			}
		}
		if(DEBUG_MODE)
			print_r($budget_array);
		
		$sql="select * from tbl_bidding_details_summary where parentid ='".$this->parentid."' and version = '".$this->version."'";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Summary Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res && $num > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				if(DEBUG_MODE)
				{
					echo '<hr>';
					print_r($row);
			    }
				$this->tenure 		 = $row['duration'];
			}
		}elseif($this->tbd_duration>0)
		{
			$this->tenure  = $this->tbd_duration;
		}
		else
		{
			$this->tenure 		= 365;
		}
		
		$campaignid 		= 1;
		$pos 				= 100;	
		$ip_inventory_asked = 0;
		$LCF 		  		= 0;
		$HCF 		  		= 1;
		$updatedby 			= "PurePackage Entries:".$this->updatedby;
		$updatedon 			= date('Y-m-d H:i:s');
		
		
		$tot_sys_budget = 0;
		foreach($budget_array as $catid => $cat_values_array)
		{
			foreach($cat_values_array as $pincode => $values_array)
				$tot_sys_budget += 	$values_array['bgt'];
		}
		if(DEBUG_MODE)
			echo '<br>tot_sys_budget:'.$tot_sys_budget;		
		
		foreach($budget_array as $catid => $cat_values_array)
		{
			foreach($cat_values_array as $pincode => $values_array)
			{
				$national_catid = $cat_array[$catid]['nid'];
				$bid_value 		= $values_array['bval'];
				$callcount 		= $values_array['cnt'];
				$sys_budget		= $values_array['bgt'];
				$actual_budget  = $sys_budget; 
				$bidperday 		= ($sys_budget/$tot_sys_budget) * $this->bidperday;
				$sys_budget		= $bidperday*$this->tenure;
				$actual_budget  = $bidperday*$this->tenure; 
				
				$budget_array[$catid][$pincode]['bpd'] = $bidperday;
				$budget_array[$catid][$pincode]['pin'] = $pincode;
						
				$ins_array[$catid][] = "('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$this->tenure."', '".$sys_budget."', '".$actual_budget."',  '".$bidperday."',  '".$LCF."',  '".$HCF."', '".$this->data_city."', '".$this->pincode."', '".$this->latitude."', '".$this->longitude."', '".$updatedby."','".$updatedon."')";
			}
		}

		if(count($ins_array)>0)
		{
			$sql = "delete from tbl_bidding_details where parentid='".$this->parentid."' and campaignid=1";
			$res 	= parent::execQuery($sql, $this->finance);
							
			if(DEBUG_MODE)
			{
				echo '<br><b>DB delete:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			foreach($budget_array as $catid => $cat_values_array)
			{
				$ins_str = implode(",",$ins_array[$catid]);
				$sql_str = "replace into tbl_bidding_details(parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, duration, sys_budget, actual_budget, bidperday, lcf, hcf, data_city, physical_pincode, latitude, longitude, updatedby, updatedon) 
				values ".$ins_str;
				
				$res_str 	= parent::execQuery($sql_str, $this->finance);
							
				if(DEBUG_MODE)
				{
					echo '<br><b>Live Insert Query:</b>'.$sql_str;
					echo '<br><b>Result Set:</b>'.$res_str;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			}
			$return_array['results'] = $budget_array;
			$return_array['error']['code'] = "0";
			$return_array['error']['msg']  = "PurePackage Entries done sucessfully";
			return $return_array;
			//$resultstr= json_encode($return_array);
			//print($resultstr);
			//die;
		}
		else
		{
			$return_array['results'] = array();
			$return_array['error']['code'] = "1";
			$return_array['error']['msg']  = "Couldn't make Entires";
			$resultstr= json_encode($return_array);
			print($resultstr);
			die;
		}
		die;
		
	}
	function manageInventory()	
	{		
		if($this->astate == 15 || $this->astate == 16 || $this->astate==17) // 15- pure package entries
		{
			if(DEBUG_MODE)
				echo '<br>pure package entries';
			
			$return_array = $this->createEntries();
			$this->etime = date('Y-m-d H:i:s');
			$return_array['time']['start_time'] = $this->stime;
			$return_array['time']['end_time'] = $this->etime;
			$return_array['time']['exe_sec'] = (strtotime($this->etime) -  strtotime($this->stime));
			$return_array['inp_params']	= $this->params;
			
			return $return_array;
			exit;
		}
		elseif($this->astate == 10 || $this->astate == 11) //10-category/pin deletion LIve 11-category/pin deletion Shadow
		{
			if(DEBUG_MODE)
				echo '<br>Release Inventory';

			$return_res = $this->releaseInventory();
			$inv_seeked = $return_res['inv_data'];
			$pin_arr 	= $return_res['pin_data'];
			$cat_arr	= $return_res['cat_data'];

			if(DEBUG_MODE)
				print_r($return_res);
			
		}elseif($this->astate == 6 || $this->astate == 7 || $this->astate == 8) // part payment / ecs /carry forward- blocking & checking
		{
			if(DEBUG_MODE)
				echo '<br>Fetching Live Inv:';
			$return_res = $this->liveInventory();
			$inv_seeked = $return_res['inv_data'];
			$pin_arr 	= $return_res['pin_data'];
			$cat_arr	= $return_res['cat_data'];
		}
		elseif($this->astate == 4 || $this->astate == 5) // 4-expiry & 5-inv release
		{
			$return_res = $this->deltaInventory();
			$inv_seeked = $return_res['inv_data'];
			$pin_arr 	= $return_res['pin_data'];
			$cat_arr	= $return_res['cat_data'];
			//if(DEBUG_MODE)
			//{
			//	print_r($inv_seeked);
			//	print_r($pin_arr);
			//	print_r($cat_arr);
			//}
		}elseif(($this->astate == 1 || $this->astate == 2) && $this->dealclosed_flag==0)
		{
			$sql="select * from tbl_bidding_details_intermediate where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Inter Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Num Rows:</b>'.$num;
				echo '<br><b>Error:</b>'.$this->mysql_error;
				
			}
			$tot_cat_budget_inter = 0;
			if($res && $num > 0)
			{		
				while($row=mysql_fetch_assoc($res))
				{
					//echo '<hr>';
					//print_r($row);
					$catid = $row['catid']; 
					$pin_array = json_decode($row['pincode_list'], true); 
					$tot_cat_budget_inter += $row['cat_budget'];
					//print_r($pin_array);
					foreach($pin_array as $pincode=>$pin_values)
					{
						$position_flag = $pin_values['pos'];
						$inv_seeked[$catid][$pincode][$position_flag]['p']   = $pin_values['pos'];
						$inv_seeked[$catid][$pincode][$position_flag]['c']   = $pin_values['cnt_f'];
						$inv_seeked[$catid][$pincode][$position_flag]['b']   = $pin_values['bidvalue'];
						$inv_seeked[$catid][$pincode][$position_flag]['bgt'] = $pin_values['budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $pin_values['budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['i'] 	 = $pin_values['inv'];
						$inv_seeked[$catid][$pincode][$position_flag]['s'] 	 = 1; // 1-pass 0-fail 
						$inv_seeked[$catid][$pincode][$position_flag]['aflg'] 	 = 0; // 1-release inv 2-modifiy inv  3-no action
						$pin_arr[] = $pincode;
						$tot_cat_budget_inter_catid[$catid] += $pin_values['budget'];
					}
					if(ABS($row['cat_budget']-$tot_cat_budget_inter_catid[$catid])>1)
					{
						$this->inter_factor[$catid] = 1+ (($row['cat_budget']-$tot_cat_budget_inter_catid[$catid])/$tot_cat_budget_inter_catid[$catid]);
						
						foreach($pin_array as $pincode=>$pin_values)
						{
							$position_flag = $pin_values['pos'];
							$inv_seeked[$catid][$pincode][$position_flag]['bgt'] = $pin_values['budget']*$this->inter_factor[$catid];
							$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $pin_values['budget']*$this->inter_factor[$catid];
						}
					}
					
					$cat_arr[] = $catid;	
				}
				
				if(DEBUG_MODE)
				{
					echo '<br>Inter cat_bidget & inter cat pin budget mismatch count :'.count($this->inter_factor);
					print_r($this->inter_factor);
					echo '<br>tot_cat_budget_inter:'.$tot_cat_budget_inter;
					echo '<br>this->sys_budget:'.$this->sys_budget;
					print_r($inv_seeked);
				}
				if(abs($tot_cat_budget_inter-$this->sys_budget)>50)
				{
					
					mail('prameshjha@justdial.com,sumesh.dubey@justdial.com,sunnyshende@justdial.com,srinivasthumma@justdial.com,rajkumaryadav@justdial.com,rohitkaul@justdial.com','sys_total_budget & Intermediate cat_budget Out of Sync '.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],'tot_cat_budget_inter = '.$tot_cat_budget_inter .'<br>sys_total_budget = '. $this->sys_budget.'<br>'.json_encode($this->params).'<br>Inter cat_bidget & inter cat pin budget mismatch'.json_encode($this->inter_factor));
					
					$result['result'] = array();
					$result['error']['code'] = 1;
					$result['error']['msg'] = "sys_total_budget & Intermediate cat_budget Out of Sync [Dealclose/BalanceReadjsutment]";
					$resultstr= json_encode($result);
					print($resultstr);
					die;
				}	
				
				if($this->astate==2)
				{
					$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=2 AND expired=0";
					$res 	= parent::execQuery($sql, $this->finance);
					$num	= mysql_num_rows($res);
					if(DEBUG_MODE)
					{
						echo '<br><b>DB  Query:</b>'.$sql;
						echo '<br><b>Result Set:</b>'.$res;
						echo '<br><b>Num Rows:</b>'.$num;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
					
					if($num > 0)
					{
						$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' AND inventory>0 ORDER BY catid, pincode";
						$res 	= parent::execQuery($sql, $this->finance);
						$num		= mysql_num_rows($res);
						
						
						if(DEBUG_MODE)
						{
							echo '<br><b>DB  Query:</b>'.$sql;
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
								$catid 	 = $row['catid']; 
								$pincode = $row['pincode']; 
								$position_flag = $row['position_flag']; 
								//print_r($pin_array);
								if(!(is_array($inv_seeked[$catid][$pincode][$position_flag]) /*&& $position_flag == $inv_seeked[$catid][$pincode]['p']*/)) // this live inventory needs to be released
								{
									//echo '<hr>';
									//print_r($row);
									$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
									$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
									$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
									$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
									$inv_seeked[$catid][$pincode][$position_flag]['oi']   = $row['inventory'];
									$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = 0; // release inventory
									$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
									$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
									$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
									$pin_arr[] = $pincode;
									$cat_arr[] = $catid;		
								}
								
							}
						}
					} // if db finance is unexpired or Active Live contract
				}
			}
		}elseif($this->astate == 3 && $this->dealclosed_flag==1)	
		{
			$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid, pincode";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
			$num		= mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Inter Query:</b>'.$sql;
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
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$position_flag = $row['position_flag']; 
					$tot_cat_budget_shadow += $row['actual_budget'];
					//print_r($pin_array);
					$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
					$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
					$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
					$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
					$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
					$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
					$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
					$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;		
				}
			}else
			{
                $where_date = ' AND (DATEDIFF(CURRENT_DATE,booked_date)<=30 OR  DATEDIFF(CURRENT_DATE,ecs_booked_date)<=30)';
			    if ($this->ecs_flag==1)  $where_date='';
				
				$sql="SELECT * FROM tbl_bidding_details_shadow_archive WHERE parentid ='".$this->parentid."' AND version = '".$this->version."' {$where_date} GROUP BY catid, pincode, version ORDER BY catid, pincode";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
				$num		= mysql_num_rows($res);

				if(DEBUG_MODE)
				{
					echo '<br><b>DB Inter Query:</b>'.$sql;
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
						$catid 	 = $row['catid']; 
						$pincode = $row['pincode']; 
						$position_flag = $row['position_flag']; 
						$tot_cat_budget_shadow += $row['actual_budget'];
						//print_r($pin_array);
						$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
						$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
						$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
						$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
						$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
						$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
						$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;		
					}
				}else
				{
					$where_date = ' AND (DATEDIFF(CURRENT_DATE,booked_date)<=30 OR  DATEDIFF(CURRENT_DATE,ecs_booked_date)<=30)';
					if ($this->ecs_flag==1)  $where_date='';
					
					$sql="SELECT * FROM tbl_bidding_details_shadow_archive_historical WHERE parentid ='".$this->parentid."' AND version = '".$this->version."' {$where_date} GROUP BY catid, pincode, version ORDER BY catid, pincode";
					$res 	= parent::execQuery($sql, $this->dbConbudget);
					$num		= mysql_num_rows($res);

					if(DEBUG_MODE)
					{
						echo '<br><b>DB historical Query:</b>'.$sql;
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
							$catid 	 = $row['catid']; 
							$pincode = $row['pincode']; 
							$position_flag = $row['position_flag']; 
							$tot_cat_budget_shadow += $row['actual_budget'];
							//print_r($pin_array);
							$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
							$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
							$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
							$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
							$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
							$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
							$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;		
						}
					}else if(strtolower(trim($this->source)) == 'approval')
					{
						$sql="select * from tbl_bidding_details_intermediate_archive where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid";
						$res 	= parent::execQuery($sql, $this->dbConbudget);
						$num		= mysql_num_rows($res);
						
						if(DEBUG_MODE)
						{
							echo '<br><b>DB Inter Archive Query:</b>'.$sql;
							echo '<br><b>Result Set:</b>'.$res;
							echo '<br><b>Num Rows:</b>'.$num;
							echo '<br><b>Error:</b>'.$this->mysql_error;
							
						}
						
					   $sql_cs_inv = "SELECT * FROM  tbl_cs_inventory_release_log  WHERE parentid = '".$this->parentid."' AND s_version = '".$this->version."'";
					   $res_cs_inv 	= parent::execQuery($sql_cs_inv, $this->finance);
					   $num_cs_inv		= mysql_num_rows($res_cs_inv);
						if(DEBUG_MODE)
						{
							echo '<br><b>sql_cs_inv:</b>'.$sql_cs_inv;
							echo '<br><b>res_cs_inv Set:</b>'.$res_cs_inv;
							echo '<br><b>num_cs_inv Rows:</b>'.$num_cs_inv;
							echo '<br><b>Error:</b>'.$this->mysql_error;
							
						}
						if($res && $num > 0 && $res_cs_inv &&  $num_cs_inv && !trim($this->next_dealclose_version))
						{			
							$isReleased = 1;
							while($row=mysql_fetch_assoc($res))
							{
								//echo '<hr>';
								//print_r($row);
								$catid = $row['catid']; 
								$pin_array = json_decode($row['pincode_list'], true); 
								//print_r($pin_array);
								foreach($pin_array as $pincode=>$pin_values)
								{
									$position_flag = $pin_values['pos'];
									$inv_seeked[$catid][$pincode][$position_flag]['p']   = $pin_values['pos'];
									$inv_seeked[$catid][$pincode][$position_flag]['c']   = $pin_values['cnt_f'];
									$inv_seeked[$catid][$pincode][$position_flag]['b']   = $pin_values['bidvalue'];
									$inv_seeked[$catid][$pincode][$position_flag]['bgt'] = $pin_values['budget'];
									$inv_seeked[$catid][$pincode][$position_flag]['i'] 	 = $pin_values['inv'];
									$inv_seeked[$catid][$pincode][$position_flag]['s'] 	 = 1; // 1-pass 0-fail 
									$inv_seeked[$catid][$pincode][$position_flag]['aflg'] 	 = 0; // 1-release inv 2-modifiy inv  3-no action
									$pin_arr[] = $pincode;
								}
								$cat_arr[] = $catid;		
							}
							
							  if(count($inv_seeked))
							  {
									  if(count($cat_arr)>0)
									  {
												$cat_arr  = array_unique($cat_arr);
												$cat_list = implode(",",$cat_arr);
												
												############   fetching category Starts   ############
												$cat_array = $this->get_category_details($cat_list);
												if(DEBUG_MODE)
												{
													echo '<br>Cat Array:';
													print_r($cat_array);
												}
									 }
										if(DEBUG_MODE)
										{
											echo '<br>Inventory Seeked:<br>';
											print_r($inv_seeked);
											echo '<br>Catlist :'.$cat_list;
											echo '<br>Pinlist :'.$pin_list;
										}
										
									foreach($inv_seeked as $catid => $pin_array)
									{
										if(DEBUG_MODE)
										{
											//echo '<br>Parentid :'.$this->parentid;
											echo '<br>Catid:'.$catid;
											//echo '<br>Pin array:';
											//print_r($pin_array);
										}
										$ins_array = array();
										foreach($pin_array as $pincode=>$pos_array)
										{
											if(DEBUG_MODE)
											{
												echo '<br>Pincode:'.$pincode;
												//echo '<br>Pos Array:';
												//print_r($pos_array);
											}
											foreach($pos_array as $pos=>$values_array)
											{
												if(DEBUG_MODE)
												{
													echo '<br>Position:'.$pos;
													echo '<br>Values array:';
													print_r($values_array);
												}
												
												
												$bid_value 			= $values_array['b'];
												$callcount 			= $values_array['c'];
												$sys_budget 		= $values_array['bgt'];
												$bpd		 		= $values_array['bpd'];
												$ip_inventory_asked = $values_array['i'];
												
												$factor = 1+ (($this->actual_budget - $this->sys_budget)/$this->sys_budget);
												$campaignid = ($pos!=100?2:1);
												$national_catid = $cat_array[$catid]['nid']; 
												$actual_budget = $sys_budget * $factor ;
												
												$booked_date	 = date('Y-m-d H:i:s');
												$ecs_booked_date = "";
												$updatedby		 = "SysDealClose-API".$this->updatedby;
												$updatedon		 = date('Y-m-d H:i:s');
												
												
												$ins_array[] = "('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$sys_budget."', '".$actual_budget."', '".$this->data_city."','".$updatedby."','".$updatedon."','".$booked_date."','".$ecs_booked_date."')";
												
											}
										}
										
											if(count($ins_array)>0)
											{
												$ins_str = implode(",",$ins_array);
												
												$sql_str = "replace into tbl_bidding_details_shadow (parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, sys_budget, actual_budget, data_city, updatedby, updatedon, booked_date, ecs_booked_date) 
												values ".$ins_str;
												
												$res_str 	= parent::execQuery($sql_str, $this->dbConbudget);
																
												if(DEBUG_MODE)
												{
													echo '<br><b>Shadow Insert Query:</b>'.$sql_str;
													echo '<br><b>Result Set:</b>'.$res_str;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
												if(empty($res_str))
												{
													// Query Failed
													$sql_qry_f = "insert into tbl_invMgmt_qry_f SET
																		parentid = '".$this->parentid."',
																		version  = '".$this->version."',
																		state    = '".$this->astate."',
																		astatus  = '".$this->astatus."',
																		catid    = '".$this->catid."',
																		qry_str  = '".addslashes(stripslashes($sql_str))."',
																		qry_err  = '".addslashes(stripslashes($this->mysql_error))."',
																		update_time = '".date('Y-m-d H:i:s')."',
																		qry_src     = 'DC'";
																		
													$res_qry_f 	= parent::execQuery($sql_qry_f, $this->dbConbudget);
													$shadow_entry_error = 1;
													
												}
											}else
											{
												// insert array failed
												$shadow_entry_error = 1;
												$shadow_entry_error_catid[] = $catid;
											}
										
									 }
									 
									 
									$sql="select * from tbl_bidding_details_shadow where parentid ='".$this->parentid."' and version = '".$this->version."' ORDER BY catid, pincode";
									$res 	= parent::execQuery($sql, $this->dbConbudget);
									$num		= mysql_num_rows($res);
									
									if(DEBUG_MODE)
									{
										echo '<br><b>DB Inter Query:</b>'.$sql;
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
											$catid 	 = $row['catid']; 
											$pincode = $row['pincode']; 
											$position_flag = $row['position_flag']; 
											$tot_cat_budget_shadow += $row['actual_budget'];
											//print_r($pin_array);
											$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
											$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
											$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
											$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
											$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = $row['inventory'];
											$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
											$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
											$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 0; // 1-release inv 2-modifiy inv  3-no action
											$pin_arr[] = $pincode;
											$cat_arr[] = $catid;		
										}
									} 
														 
														 
							 }
						} 
						else if($res && $num > 0 && $res_cs_inv &&  $num_cs_inv && trim($this->next_dealclose_version))
						{
							$isReleased = 1;
						}
					} // if approval
				} // else historical
			} // else bidding details shadow archive
			
			if(count($inv_seeked)>0)
			{	
				$sql="select * from tbl_companymaster_finance where parentid ='".$this->parentid."' AND campaignid=2 AND expired=0";
				$res 	= parent::execQuery($sql, $this->finance);
				$num	= mysql_num_rows($res);
				if(DEBUG_MODE)
				{
					echo '<br><b>DB  Query:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.$num;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				if($num > 0)
				{
					$sql="select * from tbl_bidding_details where parentid ='".$this->parentid."' AND inventory>0 ORDER BY catid, pincode";
					$res 	= parent::execQuery($sql, $this->finance);
					$num		= mysql_num_rows($res);
					
					
					if(DEBUG_MODE)
					{
						echo '<br><b>DB  Query:</b>'.$sql;
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
							$catid 	 = $row['catid']; 
							$pincode = $row['pincode']; 
							$position_flag = $row['position_flag']; 
							//print_r($pin_array);
							if(!(is_array($inv_seeked[$catid][$pincode][$position_flag]) /*&& $position_flag == $inv_seeked[$catid][$pincode]['p']*/)) // this live inventory needs to be released
							{
								if(DEBUG_MODE)
								{
									print_r($row);
									echo '<br>Inv To be Released';
								}
								$inv_seeked[$catid][$pincode][$position_flag]['p']    = $row['position_flag'];
								$inv_seeked[$catid][$pincode][$position_flag]['c']    = $row['callcount'];
								$inv_seeked[$catid][$pincode][$position_flag]['b']    = $row['bidvalue'];
								$inv_seeked[$catid][$pincode][$position_flag]['bgt']  = $row['sys_budget'];
								$inv_seeked[$catid][$pincode][$position_flag]['oi']   = $row['inventory'];
								$inv_seeked[$catid][$pincode][$position_flag]['i'] 	  = 0; // release inventory
								$inv_seeked[$catid][$pincode][$position_flag]['s'] 	  = 1; // 1-pass 0-fail 
								$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $row['actual_budget'];
								$inv_seeked[$catid][$pincode][$position_flag]['aflg'] = 1; // 1-release inv 2-modifiy inv  3-no action
								$pin_arr[] = $pincode;
								$cat_arr[] = $catid;		
							}
							
						}
					}
				} // if db finance is unexpired or Active Live contract
			}
			
			if(DEBUG_MODE)
			{	
				//echo '<br>Before Corerction';
				//print_r($inv_seeked);
				echo '<br>tot_cat_budget_shadow'.$tot_cat_budget_shadow;
				echo '<br>this->actual_budget'.$this->actual_budget;
			}
			######### approval budget checking #############
			if(ABS($tot_cat_budget_shadow-$this->actual_budget) > 50)
			{
				if(strtotime($this->dealclosed_on) <strtotime('2019-02-12 17:00:00'))
				{
					
					$this->shadow_factor = (1 + (($this->actual_budget - $tot_cat_budget_shadow)/$tot_cat_budget_shadow));
					if(DEBUG_MODE)
					{
						echo '<br>correcting actual budget';
						echo '<br>Shadow factor:'.$this->shadow_factor;
					}
						
					
					foreach($inv_seeked as $catid => $cat_array)
					{	
						//if(DEBUG_MODE)
						//{
							//echo '<hr>catid:'.$catid;
							////print_r($cat_array);
						//}
						foreach($cat_array as $pincode => $pincode_array)
						{
							//if(DEBUG_MODE)
							//{
								//echo '<br>pincode:'.$pincode;
								////print_r($pincode_array);
							//}
							foreach($pincode_array as $position_flag => $pos_arrray)
							{
								//if(DEBUG_MODE)
								//{
									//echo '<br>positon_flag:'.$position_flag;
									//print_r($pos_arrray);
								//}
								$inv_seeked[$catid][$pincode][$position_flag]['act_abgt'] = $pos_arrray['abgt'];
								$inv_seeked[$catid][$pincode][$position_flag]['abgt'] = $pos_arrray['abgt']*$this->shadow_factor;
							}
						}
					}
					if(DEBUG_MODE)
					{
						echo '<br>After Corerction';
						print_r($inv_seeked);
					}
				}
				else
				{
					mail('prameshjha@justdial.com,sumesh.dubey@justdial.com,sunnyshende@justdial.com,srinivasthumma@justdial.com,rajkumaryadav@justdial.com,rohitkaul@justdial.com','actual_total_budget(summary) & actual_cat_pin_budget(shadow) Out of Sync '.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],'tot_cat_budget_shadow = '.$tot_cat_budget_shadow .'<br>actual_budget = '. $this->actual_budget.'<br>'.json_encode($this->params));
					
					$result['result'] = array();
					$result['error']['code'] = 1;
					$result['error']['msg'] = "actual_total_budget & Shadow cat_pin_budget Out of Sync [Approval]";
					$resultstr= json_encode($result);
					print($resultstr);
					die;
				}
			}	
		}
		
		if(count($pin_arr)>0)
		{
			$pin_arr  = array_unique($pin_arr);
			$pin_list = implode(",",$pin_arr);
		}
		if(count($cat_arr)>0)
		{
			$cat_arr  = array_unique($cat_arr);
			$cat_list = implode(",",$cat_arr);
		}
		if(DEBUG_MODE)
		{
			echo '<br>Inventory Seeked:<br>';
			print_r($inv_seeked);
			echo '<br>Catlist :'.$cat_list;
			echo '<br>Pinlist :'.$pin_list;
		}
		
		if(count($pin_arr)==0 || count($cat_arr)==0 || count($inv_seeked)==0)
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = ($isReleased && trim($this->next_dealclose_version)) ? "Entry is released by executive (ENRL)" : "Entry in intermeidiate or shadow table Not Found";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		############   fetching category Starts   ############
		$this->cat_array = $this->get_category_details($cat_list);
		if(DEBUG_MODE)
		{
			echo '<br>Cat Array:';
			print_r($this->cat_array);
		}
		
		$sql="select * from tbl_business_uploadrates where city='".$this->data_city."' limit 1";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num_rows		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Biz Upload rates Query:</b>'.$sql;
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
				//	echo '<hr>';
				//	print_r($row);
				}
				$this->callcnt_growth_rate = 1 + ($row['callcnt_per']/100);
				$this->city_min_budget = 	$row['cityminbudget'];
				$this->all_pincode_count  = 	$row['allpincnt'];
			}
		}
		
		############   fetching category Ends     ############
		$sql="select * from tbl_fixedposition_pincodewise_bid where catid in (".$cat_list.") and pincode in (".$pin_list.") ORDER BY catid, pincode";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num_rows		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Inventory Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res))
			{
				//echo '<hr>';
				//print_r($row);
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				
				//print_r($inv_seeked[$catid][$pincode]);
				if(is_array($inv_seeked[$catid][$pincode]) /*&& $inv_seeked[$catid][$pincode]['p']!=100*/ /*&& $inv_seeked[$catid][$pincode]['i']>0*/)
				{
					foreach($inv_seeked[$catid][$pincode] as $pos=>$pos_array)
					{
						//echo '<br>pos:'.$pos;
						//print_r($pos_array);
						if($pos!=100)
						{
							$pos_str = 'pos'.$pos;
							$inv_live[$catid][$pincode][$pos]['inc'] = $row[$pos_str.'_inc'];
							$inv_live[$catid][$pincode][$pos]['bdr'] = $row[$pos_str.'_bidder'];
							$inv_live[$catid][$pincode][$pos]['inv_b'] = $row[$pos_str.'_inventory_booked'];
							$inv_live[$catid][$pincode][$pos]['inv_a'] = $row[$pos_str.'_inventory_actual'];
						}
					}
					//print_r($inv_live);
					
				}
			}
		}
		if(DEBUG_MODE)
		{
			echo '<br>Inventory LIVE:<br>';
			print_r($inv_live);
		}
		
		switch($this->astatus)
		{
			case 1: 
			case 2: 
					if($this->astate == 10 || $this->astate == 11)
					{
						$ret_book = $this->inventorybooking($inv_seeked, $inv_live);
						$return_array['results'] = $ret_book;
						$return_array['error']['msg'] = "Inventory Release Sucessful";
						$return_array['error']['code'] = "0";
					}elseif($this->astate == 6 || $this->astate == 7 || $this->astate == 8)
					{
						$ret = $this->inventoryChecking($inv_seeked, $inv_live);
						//echo '<br>'.count($ret['fail']);
						if(count($ret['fail'])>0)
						{
							// inv in some positions not available
							// return $ret
							$return_array['results'] = $ret;
							$return_array['error']['msg'] = "inventory in some positions not available";
							$return_array['error']['code'] = "1";
						}
						else
						{
							$ret_book = $this->inventorybooking($inv_seeked, $inv_live);
							$return_array['results'] = $ret_book;
							$return_array['error']['msg'] = "Inventory Checking/Blocking Sucessful";
							$return_array['error']['code'] = "0";
						}
					}elseif($this->astate == 4 || $this->astate == 5)
					{
						$ret_book = $this->inventorybooking($inv_seeked, $inv_live);
						$return_array['results'] = $ret_book;
						$return_array['error']['msg'] = "Inventory Release Sucessful";
						$return_array['error']['code'] = "0";
					}else
					{
						$ret = $this->inventoryChecking($inv_seeked, $inv_live);
						//echo '<br>'.count($ret['fail']);
						if(count($ret['fail'])>0)
						{
							// inv in some positions not available
							// return $ret
							$return_array['results'] = $ret;
							$return_array['error']['msg'] = "inventory in some positions not available";
							$return_array['error']['code'] = "1";
						}
						else
						{
							$ret_book = $this->inventorybooking($inv_seeked, $inv_live);
							if(($this->fin_approval == 1 || count($ret_book['seeked'])>0) && count($ret_book['overbooking'])==0)
							{
								$return_array['results'] = $ret_book;
								$return_array['error']['msg'] = "Inventory LIVE Sucessful";
								$return_array['error']['code'] = "0";
							}
							else
							{
								$return_array['results'] = $ret_book;
								$return_array['error']['msg'] = "Something went wrong - overbooking happened";
								$return_array['error']['code'] = "1";
							}
						}
					}
					break;
					
					
			case 3: $ret = $this->inventoryChecking($inv_seeked, $inv_live);
					$return_array['results'] = $ret;
					if(count($ret['fail'])>0)
					{						
						$return_array['error']['msg'] = "inventory in some positions not available";
						$return_array['error']['code'] = "1";
					}else
					{
						$return_array['error']['msg'] = "Inventory Checking Sucessful";
						$return_array['error']['code'] = "0";
					}
					break;
		}
		//print_r($ret);
		$this->etime = date('Y-m-d H:i:s');
		$return_array['time']['start_time'] = $this->stime;
		$return_array['time']['end_time'] = $this->etime;
		$return_array['time']['exe_sec'] = (strtotime($this->etime) -  strtotime($this->stime));
		$return_array['inp_params']	= $this->params;
		return($return_array);
		
	}
	
	function inventorybooking($seeked, $live)
	{
		$pass_array = array();
		$fail_array = array();
		$this->cnt_campaign_1 = 0;
		$this->cnt_campaign_2 = 0;
		
		if(DEBUG_MODE)
		{
			echo '<hr><H1>Inventory Booking</H1>';
		}
		
		if($this->astate == 2 || $this->astate == 3)
		{
			$remarks = "";
			if($this->astate == 2)
				$remarks = "Balance Readjustment";
			elseif($this->astate == 3)
				$remarks = "Fin Approval";	
				
			$sql = "insert into tbl_bidding_details_archive(parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, duration, sys_budget, actual_budget, bidperday, lcf, hcf, data_city, physical_pincode, latitude, longitude, updatedby, updatedon, backenduptdate, inserted_on, remarks)
			select parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, duration, sys_budget, actual_budget, bidperday, lcf, hcf, data_city, physical_pincode, latitude, longitude, updatedby, updatedon, backenduptdate, '".date('Y-m-d H:i:s')."' as inserted_on, '".$remarks."' as remarks from tbl_bidding_details where parentid='".$this->parentid."'";
			$res 	= parent::execQuery($sql, $this->finance);
							
			if(DEBUG_MODE)
			{
				echo '<br><b>DB archive:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			if($res)
			{
				$sql = "delete from tbl_bidding_details where parentid='".$this->parentid."'";
				$res 	= parent::execQuery($sql, $this->finance);
								
				if(DEBUG_MODE)
				{
					echo '<br><b>DB delete:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			}			
		}
		
		foreach($seeked as $catid => $pin_array)
		{
			if(DEBUG_MODE)
			{
				//echo '<br>Parentid :'.$this->parentid;
				echo '<br>Catid:'.$catid;
				//echo '<br>Pin array:';
				//print_r($pin_array);
			}
			$ins_array = array();
			foreach($pin_array as $pincode=>$pos_array)
			{
				if(DEBUG_MODE)
				{
					echo '<br>Pincode:'.$pincode;
					//echo '<br>Pos Array:';
					//print_r($pos_array);
				}
				foreach($pos_array as $pos=>$values_array)
				{
					if(DEBUG_MODE)
					{
						echo '<br>Position:'.$pos;
						echo '<br>Values array:';
						print_r($values_array);
					}
					$bid_value 			= $values_array['b'];
					$callcount 			= $values_array['c'];
					$sys_budget 		= $values_array['bgt'];
					$bpd		 		= $values_array['bpd'];
					$ip_parentid        = $this->parentid;
					$ip_inventory_asked = 0;
					$above_50_counter	= 0;
					$full_inv_counter   = 0;
					
					if($pos==100)
						$this->cnt_campaign_1 ++;
					else
						$this->cnt_campaign_2 ++;
						
					if($pos!=100 && !($this->astate == 5 && $values_array['aflg']==3))
					{
						$bidder_array = array(); 
						$bid_res	  = array(); 
						if($live[$catid][$pincode][$pos]['bdr'])
						{
							$bid_res =  $this->bidder_explode_array($live[$catid][$pincode][$pos]['bdr'], $this->parentid);
						
							$bidder_array 		= $bid_res['bid_array'];
							$above_50_counter   = $bid_res['50_counter'];
							$full_inv_counter   = $bid_res['full_counter'];
						}
						
						if(DEBUG_MODE)
						{
							echo '<br>Live array:';
							print_r($live[$catid][$pincode][$pos]);
							echo '<br>Bidder array:';
							print_r($bidder_array);
							echo '<br>above_50_counter:'.$above_50_counter;
						}
						
						$total_inventory = $live[$catid][$pincode][$pos]['inv_b'];
						$actual_inventory = $live[$catid][$pincode][$pos]['inv_a'];
						
						if($this->astate == 10 || $this->astate == 11)
						{ 
							// 1-release inv 2-modifiy inv  3-no action
							if($values_array['aflg']==1)
							{
								$ip_inventory_asked = 0;
								$ip_inventory_asked_actual = 0;
							}//elseif($values_array['aflg']!=2)
							elseif($values_array['aflg']==3)
							{
								$ip_inventory_asked = $values_array['i'];
								$ip_inventory_asked_actual = $values_array['i'];
							}
							//elseif($values_array['aflg']!=3)
							elseif($values_array['aflg']==2)
							{
								$ip_inventory_asked = $values_array['i'];
								$ip_inventory_asked_actual = 0;
							}
						}
						elseif($this->astate == 4 || $this->astate == 5)
						{
							if($values_array['aflg']==1)
							{
								$ip_inventory_asked = 0;
								$ip_inventory_asked_actual = 0;
							}
							//elseif($values_array['aflg']!=2)
							elseif($values_array['aflg']==3)
							{
								if($this->astate == 4)
								{
									$ip_inventory_asked = $values_array['i'];
									$ip_inventory_asked_actual = 0;
								}elseif($this->astate == 5)
								{
									$ip_inventory_asked = $values_array['i'];
									$ip_inventory_asked_actual = $values_array['i'];
								}
							}
							//elseif($values_array['aflg']!=3)
							elseif($values_array['aflg']==2)
							{
								$ip_inventory_asked = $values_array['i'];
								$ip_inventory_asked_actual = 0;
							}
						}
						else
						{
							$ip_inventory_asked = $values_array['i'];
							$ip_inventory_asked_actual = $values_array['i'];
						}
						
						
						if(DEBUG_MODE)
						{
							echo '<br>total_inventory:'.$total_inventory;
							echo '<br>actual_inventory:'.$actual_inventory;
							echo '<br>ip_inventory_asked:'.$ip_inventory_asked;
							echo '<br>ip_inventory_asked_actual:'.$ip_inventory_asked_actual;
						}
						
						if($bidder_array[$this->parentid]['existing'] == 1)
						{
							$delta_inventory = $ip_inventory_asked-$bidder_array[$ip_parentid]['inv'];
							$actual_delta_inventory = $ip_inventory_asked_actual-$bidder_array[$ip_parentid]['act_inv'];
							if(DEBUG_MODE)
							{
								echo '<br>delta_inventory'.$delta_inventory;
								echo '<br>actual_delta_inventory'.$actual_delta_inventory;
							}
							if( ($total_inventory+$delta_inventory) <= 1 || /*($forceflag == 1) ||*/ ($delta_inventory<=0) || $above_50_counter==0 || 
							($above_50_counter>0 && $ip_inventory_asked==1))
							{
								// 	block inventory	
								if($this->astatus == 2)
								{
									/*existing user inventory downgrade/upgrade during approval/balance readjustment';*/
									$total_inventory 						= max(($total_inventory + $delta_inventory),0);
									$actual_inventory						= max(($actual_inventory + $actual_delta_inventory),0);
									$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
									$bidder_array[$ip_parentid]['inv'] 		= $ip_inventory_asked;
									$bidder_array[$ip_parentid]['act_inv'] 	= $ip_inventory_asked_actual;
									$bidder_array[$ip_parentid]['cum'] 		= 0;
									
									if($total_inventory>1 && $above_50_counter>0 && $ip_inventory_asked==1 && ($this->astate==3 || $this->astate==2))
									{
										foreach($bidder_array as $r_parentid => $r_values)
										{
											$r_array = array();
											$l_version = 0;
											$s_version = 0;
											$l_bpd	   = 0;
											$s_bpd	   = 0;
											
											if($r_parentid!=$this->parentid)
											{
												if(DEBUG_MODE)
												{
													echo '<br>Release Parentid :'.$r_parentid;
													print_r($r_values);
												}
												$r_array['pin']	= $pincode;
												$r_array['pos']	= $pos;
												$r_array['inv']	= $r_values['inv'];
												$r_array['act_inv']	= $r_values['act_inv'];
												
												
												$sql_sel = "SELECT version, bidperday from tbl_bidding_details where parentid ='".$r_parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
												$res_sel 	= parent::execQuery($sql_sel, $this->finance);
												$num_rows		= mysql_num_rows($res_sel);
													
												if(DEBUG_MODE)
												{
													echo '<br><b>Select DB Query:</b>'.$sql_sel;
													echo '<br><b>Result Set:</b>'.$res_sel;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
												
												if($res_sel && $num_rows > 0)
												{		
													while($row_sel=mysql_fetch_assoc($res_sel))
													{
														$r_array['l']['version']	= $row_sel['version'];
														$r_array['l']['bpd']	    = $row_sel['bidperday'];
														$p_bpd[$r_parentid]['l'][$row_sel['version']] += $row_sel['bidperday'];
														$l_version   = $row_sel['version'];
														$l_bpd		 = $row_sel['bidperday'];
														if(DEBUG_MODE)
														{
															echo '<br>Live';
															echo '<br>l_version:'.$l_version;
															echo '<br>l_bpd:'.$l_bpd;
															//print_r($p_bpd);
														}
													}
												}
												
												$sql_sel = "SELECT a.version as version, a.actual_budget/b.duration as bidperday 
															from tbl_bidding_details_shadow a join tbl_bidding_details_summary b on a.parentid=b.parentid and a.version=b.version 
															where a.parentid ='".$r_parentid."' AND a.catid='".$catid."' AND a.pincode ='".$pincode."' AND a.position_flag='".$pos."'";
												
												$res_sel 	= parent::execQuery($sql_sel, $this->dbConbudget);
												$num_rows		= mysql_num_rows($res_sel);
													
												if(DEBUG_MODE)
												{
													echo '<br><b>Select DB Query:</b>'.$sql_sel;
													echo '<br><b>Result Set:</b>'.$res_sel;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
												
												if($res_sel && $num_rows > 0)
												{		
													while($row_sel=mysql_fetch_assoc($res_sel))
													{
														$r_array['s']['version']	= $row_sel['version'];
														$r_array['s']['bpd']	    = $row_sel['bidperday'];
														$p_bpd[$r_parentid]['s'][$row_sel['version']] += $row_sel['bidperday'];
														
														$s_version   = $row_sel['version'];
														$s_bpd		 = $row_sel['bidperday'];
														if(DEBUG_MODE)
														{
															echo '<br>Shadow';
															echo '<br>s_version:'.$s_version;
															echo '<br>s_bpd:'.$s_bpd;
															//print_r($p_bpd);
														}
													}
												}
												
												
												$inv_release_array[$r_parentid][$catid]['res'][] = $r_array;
												$inv_release_array[$r_parentid][$catid]['cnm'] = $this->cat_array[$catid]['cnm'];
												$inv_release_array[$r_parentid][$catid]['cid'] = $this->cat_array[$catid]['cid'];
												
												unset($bidder_array[$r_parentid]);
												
												$total_inventory 	-= round($r_values['inv'],2);
												$actual_inventory	-= round($r_values['act_inv'],2); 
												
												$sql_update = " update tbl_bidding_details set position_flag=100, inventory=0, campaignid=1, lcf=0, hcf=0, updatedby='Forced invReleased', updatedon='".date('Y-m-d H:i:s')."' where parentid ='".$r_parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
												$res_update 	= parent::execQuery($sql_update, $this->finance);
													
												if(DEBUG_MODE)
												{
													echo '<br><b>Update DB Query:</b>'.$sql_update;
													echo '<br><b>Result Set:</b>'.$res_update;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
												
												if(!($res_update))
												{
													$sql_delete = " DELETE from tbl_bidding_details where parentid ='".$r_parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								
													$res_delete 	= parent::execQuery($sql_delete, $this->finance);
														
													if(DEBUG_MODE)
													{
														echo '<br><b>Delete DB Query:</b>'.$sql_delete;
														echo '<br><b>Result Set:</b>'.$res_delete;
														echo '<br><b>Error:</b>'.$this->mysql_error;
													}
												}
												
												$sql_update = " update tbl_bidding_details_shadow set position_flag=100, inventory=0, campaignid=1, updatedby='Forced invReleased', updatedon='".date('Y-m-d H:i:s')."' where parentid ='".$r_parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
												$res_update 	= parent::execQuery($sql_update, $this->dbConbudget);
													
												if(DEBUG_MODE)
												{
													echo '<br><b>Update DB Shadow Query:</b>'.$sql_update;
													echo '<br><b>Result Set:</b>'.$res_update;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
												
												if(!($res_update))
												{
													$sql_delete = " DELETE from tbl_bidding_details_shadow where parentid ='".$r_parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								
													$res_delete 	= parent::execQuery($sql_delete, $this->dbConbudget);
														
													if(DEBUG_MODE)
													{
														echo '<br><b>Delete DB Query:</b>'.$sql_delete;
														echo '<br><b>Result Set:</b>'.$res_delete;
														echo '<br><b>Error:</b>'.$this->mysql_error;
													}
												}
												$sql_str = "INSERT INTO tbl_cs_inventory_release_log SET
																	parentid 			= '".$r_parentid."',
																	bid_catid 			= '".$catid."' ,
																	pincode				= '".$pincode."',
																	position_flag		= '".$pos."',
																	original_inventory	= '".$r_values['inv']."', 
																	new_inventory		= 0, 
																	status_flag			= 'Release', 
																	updateddate         = '".date('Y-m-d-H:i:s')."' , 
																	updatedby			= '".$this->updatedby."', 
																	l_version			= '".$l_version."', 
																	l_bidperday			= '".$l_bpd."', 
																	s_version			= '".$s_version."', 
																	s_bidperday			= '".$s_bpd."', 
																	remarks				= 'Forceful InvRelease Against Parentid:".$this->parentid."',
																	source				= 3 ";
					
												$res_str 	= parent::execQuery($sql_str, $this->finance);
															
												if(DEBUG_MODE)
												{
													echo '<br><b>Inv Log Insert Query:</b>'.$sql_str;
													echo '<br><b>Result Set:</b>'.$res_str;
													echo '<br><b>Error:</b>'.$this->mysql_error;
												}
											} // If
											
										} // Foreach
									} // IF tot_inv>1
								} // If astatus==2
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
								// this scenario should never come
								$seeked[$catid][$pincode][$pos]['s'] 			= 0;
								$overbooking[$catid][$pincode][$pos]['inv']     = $ip_inventory_asked;
								$overbooking[$catid][$pincode][$pos]['case']    = "Existing Bidder";
								$overbooking[$catid][$pincode][$pos]['b_array'] = $bidder_array;
								$overbooking[$catid][$pincode][$pos]['t_inv']   = $total_inventory;
								$overbooking[$catid][$pincode][$pos]['a_inv']   = $actual_inventory;
								//echo 'here';
								//return 0; // overbooking
								$sql_over = "insert into tbl_overbooking_inventory_log (parentid, catid, pincode, position_flag, inventory, version, data_city, bidder_string, total_inv, actual_inv, remarks, astate, astatus, updatedon, updatedby) values ('".$this->parentid."', '".$catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$this->version."', '".$this->data_city."', '".$live[$catid][$pincode][$pos]['bdr']."', '".$total_inventory."', '".$actual_inventory."', 'Existing Bidder - overbooking', '".$this->astate."', '".$this->astatus."', '".date('Y-m-d H:i:s')."', '".$this->updatedby."') ";
								
								$res_over 	= parent::execQuery($sql_over, $this->dbConbudget);
								if(DEBUG_MODE)
								{
									echo '<br><b>Over booking DB Query:</b>'.$sql_over;
									echo '<br><b>Result Set:</b>'.$res_over;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
							}
						}
						else
						{
							if( ($total_inventory + $ip_inventory_asked <=1) || $above_50_counter==0 || $ip_inventory_asked==0)
							{
								if($this->astatus == 2)					
								{
									$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
									$actual_inventory						= max(($actual_inventory + $ip_inventory_asked),0);
									$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
									$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
									$bidder_array[$ip_parentid]['act_inv'] 	= $ip_inventory_asked;
									$bidder_array[$ip_parentid]['cum'] 		= 0;
									$bidder_array[$ip_parentid]['existing'] = 1;
								}
								else
								{
									$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
									$actual_inventory						= $actual_inventory;
									$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
									$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
									$bidder_array[$ip_parentid]['act_inv'] 	= 0;
									$bidder_array[$ip_parentid]['cum'] 		= 0;
									$bidder_array[$ip_parentid]['existing'] = 1;
								}
							}
							else
							{
								// this scenario should never come
								$seeked[$catid][$pincode][$pos]['s'] 			= 0;
								$overbooking[$catid][$pincode][$pos]['inv']     = $ip_inventory_asked;
								$overbooking[$catid][$pincode][$pos]['case']    = "Non Existing Bidder";
								$overbooking[$catid][$pincode][$pos]['b_array'] = $bidder_array;
								$overbooking[$catid][$pincode][$pos]['t_inv']   = $total_inventory;
								$overbooking[$catid][$pincode][$pos]['a_inv']   = $actual_inventory;
								//echo 'there';
								//return 0; // overbooking
								if($this->astatus == 2)					
								{
									$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
									$actual_inventory						= max(($actual_inventory + $ip_inventory_asked),0);
									$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
									$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
									$bidder_array[$ip_parentid]['act_inv'] 	= $ip_inventory_asked;
									$bidder_array[$ip_parentid]['cum'] 		= 0;
									$bidder_array[$ip_parentid]['existing'] = 1;
								}
								else
								{
									$total_inventory 						= max(($total_inventory + $ip_inventory_asked),0);
									$actual_inventory						= $actual_inventory;
									$bidder_array[$ip_parentid]['bid'] 		= $bid_value;
									$bidder_array[$ip_parentid]['inv'] 	    = $ip_inventory_asked;
									$bidder_array[$ip_parentid]['act_inv'] 	= 0;
									$bidder_array[$ip_parentid]['cum'] 		= 0;
									$bidder_array[$ip_parentid]['existing'] = 1;
								}
								$sql_over = "insert into tbl_overbooking_inventory_log (parentid, catid, pincode, position_flag, inventory, version, data_city, bidder_string, total_inv, actual_inv, remarks, astate, astatus, updatedon, updatedby) values ('".$this->parentid."', '".$catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$this->version."', '".$this->data_city."', '".$live[$catid][$pincode][$pos]['bdr']."', '".$total_inventory."', '".$actual_inventory."', 'Non Existing Bidder-overbooking', '".$this->astate."', '".$this->astatus."', '".date('Y-m-d H:i:s')."', '".$this->updatedby."') ";
								
								$res_over 	= parent::execQuery($sql_over, $this->dbConbudget);
								if(DEBUG_MODE)
								{
									echo '<br><b>Over booking DB Query:</b>'.$sql_over;
									echo '<br><b>Result Set:</b>'.$res_over;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
								
							}
						}
						if(DEBUG_MODE)
						{
							
							echo '<br>Final Bidder array:';
							print_r($bidder_array);
							echo '<br>total_inventory:'.$total_inventory;
							echo '<br>actual_inventory:'.$actual_inventory;
							echo '<br>Inv Release array:';
							print_r($inv_release_array);
							echo '<br>Inv Release array BPD:';
							print_r($p_bpd);
						}
						
						$bidders_request_array =$this -> bidder_implode_string($bidder_array, $this->parentid, $catid, $pincode, $pos, $this->astatus,$actual_inventory);
						if(DEBUG_MODE)
						{
							echo '<br>After bidder request array';
							print_r($bidders_request_array);
						}
						
						$bidders_request = $bidders_request_array['b_string'];
						$min_inv		 = $bidders_request_array['min_inv'];
						
						$pos_str = 'pos'.$pos;
											
						$bidders_request	= $pos_str."_bidder 		 	= '".$bidders_request."'";
						$total_inventory 	= $pos_str."_inventory_booked   = '".$total_inventory."'";
						$actual_inventory	= $pos_str."_inventory_actual   = '".$actual_inventory."'";
						if($this->astatus == 2)
							$inc_inv        = ", ".$pos_str."_inc   = '".$min_inv."'";
						else
							$inc_inv        = "";				 
						
						if($this->cat_array[$catid]['b2b_flag'] == 1)
						{
							$callcount_default  = 1/$this->all_pincode_count;
							$bidvalue_default   = '50';
							$x_bidvalue_default = '50';
							$min_budget_default = '50';
						}
						else
						{
							$callcount_default  = 1/$this->all_pincode_count;
							$bidvalue_default   = '5';
							$x_bidvalue_default = '5';
							$min_budget_default = '1';
						}
						$sql_update_booking = "INSERT INTO tbl_fixedposition_pincodewise_bid SET
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
						
						if(DEBUG_MODE)
						{
							echo '<br><b>Inv Update Query:</b>'.$sql_update_booking;
							echo '<br><b>Result Set:</b>'.$res_update_booking;
							echo '<br><b>Error:</b>'.$this->mysql_error;
						}
						
						//echo '<br><b>Inv Update Query:</b>'.$sql_update_booking;
					} // if - fixed position
					
					if($seeked[$catid][$pincode][$pos]['s'] == 0)
					{
						if(DEBUG_MODE)
						{
							echo '<br>Position Already taken by someone-Action Needed Overbooking Done for catid:'.$catid.' pincode:'.$pincode.' position:'.$pos;
						}
						//$pos = 0; 
						//$ip_inventory_asked = 0;
					}
					if($this->astate == 1 || (($this->astate == 6 || $this->astate == 7 ||$this->astate == 8) /*&& $this->astatus==1*/)) // entry into shadow table // condition removed - as 6 & 7 & 8are always checking or blocking
					{
						if($this->astate == 1)
						{
							$factor = 1+ (($this->actual_budget - $this->sys_budget)/$this->sys_budget);
							$campaignid = ($pos!=100?2:1);
							$national_catid = $this->cat_array[$catid]['nid']; 
							$actual_budget = $sys_budget * $factor ;
						}
						else
						{
							$campaignid = ($pos!=100?2:1);
							$national_catid = $this->cat_array[$catid]['nid']; 
							$actual_budget = $values_array['abgt'];
						}										
						
						if($this->astate == 1)
						{
							$booked_date = date('Y-m-d H:i:s');
							$ecs_booked_date = "";
							if($this->inter_factor[$catid])
								$updatedby = "DealClose-API".$this->updatedby.' i_f-'.$this->inter_factor[$catid].' f-'.$factor;
							else
								$updatedby = "DealClose-API".$this->updatedby.' f-'.$factor;
							$updatedon = date('Y-m-d H:i:s');
						}elseif($this->astate == 6)
						{
							$booked_date = date('Y-m-d H:i:s');
							$ecs_booked_date = "";
							$updatedby = "PartPayment-Blocking".$this->updatedby.'live_src:'.$this->live_src.'r_r_f:'.$this->reverse_readjust_factor;
							$updatedon = date('Y-m-d H:i:s');
						}elseif($this->astate == 7)
						{
							$booked_date = "";
							$ecs_booked_date = date('Y-m-d H:i:s');
							$updatedby = "ECS-Blocking".$this->updatedby.'live_src:'.$this->live_src.'r_r_f:'.$this->reverse_readjust_factor;
							$updatedon = date('Y-m-d H:i:s');
						}elseif($this->astate == 8)
						{
							$booked_date = "";
							$ecs_booked_date = date('Y-m-d H:i:s');
							$updatedby = "CarryFwd-Blocking".$this->updatedby.'live_src:'.$this->live_src.'r_r_f:'.$this->reverse_readjust_factor;
							$updatedon = date('Y-m-d H:i:s');
						}
						//echo '<br>'."('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$sys_budget."', '".$actual_budget."', '".$this->data_city."','".$updatedby."','".$updatedon."','".$booked_date."','".$ecs_booked_date."')";
						
						if(DEBUG_MODE)
						{
							echo '<br>'."('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$sys_budget."', '".$actual_budget."', '".$this->data_city."','".$updatedby."','".$updatedon."','".$booked_date."','".$ecs_booked_date."')";
						}
						$ins_array[] = "('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$sys_budget."', '".$actual_budget."', '".$this->data_city."','".$updatedby."','".$updatedon."','".$booked_date."','".$ecs_booked_date."')";
					}elseif($this->astate == 2 || $this->astate == 3) // entry into main table
					{
						$campaignid = ($pos!=100?2:1);
						$national_catid = $this->cat_array[$catid]['nid']; 
						if($this->readjust_total_budget>0)
						{
							$factor = 1 + (($this->readjust_total_budget - $this->actual_budget)/$this->actual_budget);
							$this->tenure = $this->readjust_duration;
						}
						else
						{
							$factor = 1;
						}
						$actual_budget = $values_array['abgt']*$factor;
						$bidperday = $actual_budget/$this->tenure_f;
						if($this->shadow_factor)
							$x_txt = " s_f:".$this->shadow_factor;
						else
							$x_txt = "";
						if($this->astate == 2)
							$updatedby = "BalReadjustment-API".$this->updatedby." t_f-".$this->tenure_f." ".$x_txt." r_f-".$factor;
						elseif($this->astate == 3)
							$updatedby = "FinApproval-API".$this->updatedby." t_f-".$this->tenure_f." ".$x_txt." r_f-".$factor;
							
						$updatedon = date('Y-m-d H:i:s');
						$LCF = $this->lcf_hcf_array[$catid][$pincode][$pos]['l'];
						$HCF = $this->lcf_hcf_array[$catid][$pincode][$pos]['h'];
						
						if($pos==100 || $ip_inventory_asked>0)		
						{			
							$ins_array[] = "('".$this->parentid."', '".$this->docid."', '".$this->version."', '".$campaignid."', '".$catid."', '".$national_catid."', '".$pincode."', '".$pos."', '".$ip_inventory_asked."', '".$bid_value ."', '".$callcount."', '".$this->tenure."', '".$sys_budget."', '".$actual_budget."',  '".$bidperday."',  '".$LCF."',  '".$HCF."', '".$this->data_city."', '".$this->physical_pincode."', '".$this->latitude."', '".$this->longitude."', '".$updatedby."','".$updatedon."')";
						}
					}elseif($this->astate == 4 || $this->astate == 5) // entry into inventory logging table
					{
						if($values_array['aflg']==1)
							$status_flag = "Release";
						elseif($values_array['aflg']==2)
							$status_flag = "Modified";
						elseif($values_array['aflg']==3)
							$status_flag = "No Action";
						else
							$status_flag = "";
							
						if($this->astate == 4)
							$updatedby = "DAEMON EXPIRY".$this->updatedby;
						elseif($this->astate == 5)
							$updatedby = "INV RELEASE".$this->updatedby;
						else
							$updatedby = "";
						
						$ins_array[] = " ('".$this->parentid."', '".$catid."', '".$pincode."', '".$pos."', '".$values_array['oi']."', '".$ip_inventory_asked."', '".$status_flag."', '".date('Y-m-d-H:i:s')."', '".$updatedby."')";
					}elseif($this->astate == 10 || $this->astate == 11) // Entry into inv release table & delete from main table or shadow table
					{
						if($values_array['aflg']==1)
							$status_flag = "Release";
						elseif($values_array['aflg']==2)
							$status_flag = "Modified";
						elseif($values_array['aflg']==3)
							$status_flag = "No Action";
						else
							$status_flag = "";
							
						$updatedby = $this->i_updatedby;
							
						if($this->astate == 10)
						{
							if($pos==100)
							{
								$sql_delete = " DELETE from tbl_bidding_details where parentid ='".$this->parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								
								$res_delete 	= parent::execQuery($sql_delete, $this->finance);
									
								if(DEBUG_MODE)
								{
									echo '<br><b>Delete DB Query:</b>'.$sql_delete;
									echo '<br><b>Result Set:</b>'.$res_delete;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
							}
							else
							{
								$sql_update = " update tbl_bidding_details set position_flag=100, inventory=0, campaignid=1, lcf=0, hcf=0, updatedby='Forced invReleased', updatedon='".date('Y-m-d H:i:s')."' where parentid ='".$this->parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								$res_update 	= parent::execQuery($sql_update, $this->finance);
									
								if(DEBUG_MODE)
								{
									echo '<br><b>Update DB Query:</b>'.$sql_update;
									echo '<br><b>Result Set:</b>'.$res_update;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
								if(!($res_update))
								{
									$sql_delete = " DELETE from tbl_bidding_details where parentid ='".$this->parentid."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								
									$res_delete 	= parent::execQuery($sql_delete, $this->finance);
										
									if(DEBUG_MODE)
									{
										echo '<br><b>Delete DB Query:</b>'.$sql_delete;
										echo '<br><b>Result Set:</b>'.$res_delete;
										echo '<br><b>Error:</b>'.$this->mysql_error;
									}
								}
								else
								{
									$p_bpd[$this->parentid]['l'][$this->version] += $bpd;
								}
							}
							$l_version = $this->version;
							$l_bpd	   = $bpd;
							$s_version = 0;
							$s_bpd	   = 0;
							
							
							$ins_array[] = " ('".$this->parentid."', '".$catid."', '".$pincode."', '".$pos."', '".$values_array['oi']."', '".$ip_inventory_asked."', '".$status_flag."', '".date('Y-m-d-H:i:s')."', '".$updatedby."', '".$this->i_reason."', '1', '".$l_version."', '".$l_bpd."', '".$s_version."', '".$s_bpd."')";
						}elseif($this->astate == 11)
						{
							if($this->full_release==1)
							{
								$reason = "Full Release -".$this->i_reason;
								$sql_delete = " DELETE from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND version='".$this->version."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								$res_delete 	= parent::execQuery($sql_delete, $this->dbConbudget);
										
								if(DEBUG_MODE)
								{
									echo '<br><b>Delete DB Shadow Query:</b>'.$sql_delete;
									echo '<br><b>Result Set:</b>'.$res_delete;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
							}
							elseif($pos==100)
							{
								$sql_delete = " DELETE from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND version='".$this->version."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								$res_delete 	= parent::execQuery($sql_delete, $this->dbConbudget);
										
								if(DEBUG_MODE)
								{
									echo '<br><b>Delete DB Shadow Query:</b>'.$sql_delete;
									echo '<br><b>Result Set:</b>'.$res_delete;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
							}
							else
							{
								$sql_update = " update tbl_bidding_details_shadow set position_flag=100, inventory=0, campaignid=1, updatedby='Forced invReleased', updatedon='".date('Y-m-d H:i:s')."' where parentid ='".$this->parentid."' AND version='".$this->version."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
								$res_update 	= parent::execQuery($sql_update, $this->dbConbudget);
									
								if(DEBUG_MODE)
								{
									echo '<br><b>Update DB Query:</b>'.$sql_update;
									echo '<br><b>Result Set:</b>'.$res_update;
									echo '<br><b>Error:</b>'.$this->mysql_error;
								}
								
								if(!($res_update))
								{
									$sql_delete = " DELETE from tbl_bidding_details_shadow where parentid ='".$this->parentid."' AND version='".$this->version."' AND catid='".$catid."' AND pincode ='".$pincode."' AND position_flag='".$pos."'";
									$res_delete 	= parent::execQuery($sql_delete, $this->dbConbudget);
											
									if(DEBUG_MODE)
									{
										echo '<br><b>Delete DB Shadow Query:</b>'.$sql_delete;
										echo '<br><b>Result Set:</b>'.$res_delete;
										echo '<br><b>Error:</b>'.$this->mysql_error;
									}
								}
								else
								{
									$p_bpd[$this->parentid]['s'][$this->version] += $bpd;
								}
							}
							$l_version = 0;
							$l_bpd	   = 0;
							$s_version = $this->version;;
							$s_bpd	   = $bpd;
							
							
							$ins_array[] = " ('".$this->parentid."', '".$catid."', '".$pincode."', '".$pos."', '".$values_array['oi']."', '".$ip_inventory_asked."', '".$status_flag."', '".date('Y-m-d-H:i:s')."', '".$updatedby."', '".$reason."', '2', '".$l_version."', '".$l_bpd."', '".$s_version."', '".$s_bpd."')";
						}
					}
				} // foreach pos-array
			} // foreach pin-array
	
			if($this->astate == 1 || (($this->astate == 6 || $this->astate == 7 || $this->astate == 8) && $this->astatus==1))
			{
				if(count($ins_array)>0)
				{
					$ins_str = implode(",",$ins_array);
					
					$sql_str = "replace into tbl_bidding_details_shadow (parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, sys_budget, actual_budget, data_city, updatedby, updatedon, booked_date, ecs_booked_date) 
					values ".$ins_str;
					
					$res_str 	= parent::execQuery($sql_str, $this->dbConbudget);
									
					if(DEBUG_MODE)
					{
						echo '<br><b>Shadow Insert Query:</b>'.$sql_str;
						echo '<br><b>Result Set:</b>'.$res_str;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
					if(empty($res_str))
					{
						// Query Failed
						$sql_qry_f = "insert into tbl_invMgmt_qry_f SET
											parentid = '".$this->parentid."',
											version  = '".$this->version."',
											state    = '".$this->astate."',
											astatus  = '".$this->astatus."',
											catid    = '".$this->catid."',
											qry_str  = '".addslashes(stripslashes($sql_str))."',
											qry_err  = '".addslashes(stripslashes($this->mysql_error))."',
											update_time = '".date('Y-m-d H:i:s')."',
											qry_src     = 'DC'";
											
						$res_qry_f 	= parent::execQuery($sql_qry_f, $this->dbConbudget);
						$shadow_entry_error = 1;
						
					}
				}else
				{
					// insert array failed
					$shadow_entry_error = 1;
					$shadow_entry_error_catid[] = $catid;
				}
				
			}
			elseif($this->astate == 2 || $this->astate == 3)
			{
				if(count($ins_array)>0)
				{
					$ins_str = implode(",",$ins_array);
					$sql_str = "replace into tbl_bidding_details(parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, duration, sys_budget, actual_budget, bidperday, lcf, hcf, data_city, physical_pincode, latitude, longitude, updatedby, updatedon) 
					values ".$ins_str;
					
					$res_str 	= parent::execQuery($sql_str, $this->finance);
								
					if(DEBUG_MODE)
					{
						echo '<br><b>Live Insert Query:</b>'.$sql_str;
						echo '<br><b>Result Set:</b>'.$res_str;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
				}
			}
			elseif($this->astate == 4 || $this->astate == 5)
			{
				if(count($ins_array)>0)
				{
					$ins_str = implode(",",$ins_array);
					$sql_str = "INSERT INTO tbl_expiredContract_inventory_release_log(parentid, bid_catid, pincode, position_flag, original_inventory, new_inventory, status_flag, updateddate, updatedby) VALUES ".$ins_str;
					
					$res_str 	= parent::execQuery($sql_str, $this->finance);
								
					if(DEBUG_MODE)
					{
						echo '<br><b>Inv Log Insert Query:</b>'.$sql_str;
						echo '<br><b>Result Set:</b>'.$res_str;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
				}
			}
			elseif($this->astate == 10 || $this->astate == 11)
			{
				if(count($ins_array)>0)
				{
					$ins_str = implode(",",$ins_array);
					$sql_str = "INSERT INTO tbl_cs_inventory_release_log(parentid, bid_catid, pincode, position_flag, original_inventory, new_inventory, status_flag, updateddate, updatedby, remarks, source, l_version, l_bidperday, s_version, s_bidperday) VALUES ".$ins_str;
					
					$res_str 	= parent::execQuery($sql_str, $this->finance);
								
					if(DEBUG_MODE)
					{
						echo '<br><b>Inv Log Insert Query:</b>'.$sql_str;
						echo '<br><b>Result Set:</b>'.$res_str;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
				}
			}						
			
		} // for each cat-array
		
		if($this->astate == 1 || $this->astate == 2)
		{
			if($shadow_entry_error == 1)
			{
				$result['result'] = array();
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Shadow entry failed for some categories:".implode(",",$shadow_entry_error_catid);
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			else
			{
				$sql = "update  tbl_bidding_details_summary set
								dealclosed_flag = 1,
								dealclosed_on	='".date('Y-m-d H:i:s')."',
								dealclosed_by	= '".addslashes($this->updatedby)."',
								dealclosed_uname='".addslashes($this->username)."'
								where parentid='".$this->parentid."' AND version='".$this->version."'";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
								
				if(DEBUG_MODE)
				{
					echo '<br><b>Dealclose flag Update:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				$sql = "insert into tbl_bidding_details_intermediate_archive(parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate,inserted_on,remarks)
				select  parentid,version,catid,national_catid,pincode_list,cat_budget,updatedby,updatedon,backenduptdate,'".date('Y-m-d H:i:s')."' as inserted_on, 'delaclosed done' as remarks from tbl_bidding_details_intermediate where parentid='".$this->parentid."' AND version='".$this->version."'";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
								
				if(DEBUG_MODE)
				{
					echo '<br><b>DB inter archive:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				if($res)
				{
					$sql = "delete from tbl_bidding_details_intermediate where parentid='".$this->parentid."' AND version='".$this->version."'";
					$res 	= parent::execQuery($sql, $this->dbConbudget);
									
					if(DEBUG_MODE)
					{
						echo '<br><b>DB inter delete:</b>'.$sql;
						echo '<br><b>Result Set:</b>'.$res;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
				}
			}			
		}elseif($this->astate == 3 || $this->astate == 5)
		{
			$remarks = "";
			if($this->astate == 3)
			{	$tbl_archive = "tbl_bidding_details_shadow_archive_approved";
				$remarks = "fin approval done";
			}
			elseif($this->astate == 5)
			{
				$tbl_archive = "tbl_bidding_details_shadow_archive";
				$remarks = "Inventory Release";	
			}
				
			$sql = "insert into ".$tbl_archive."(parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,sys_budget,actual_budget,data_city,inventory_release_flag,updatedby,updatedon,backenduptdate,inserted_on,remarks, booked_date, booked_by, ecs_booked_date)
			select parentid,docid,version,campaignid,catid,national_catid,pincode,position_flag,inventory,bidvalue,callcount,sys_budget,actual_budget,data_city,inventory_release_flag,updatedby,updatedon,backenduptdate,'".date('Y-m-d H:i:s')."' as inserted_on, '".$remarks."' as remarks, booked_date, booked_by, ecs_booked_date from tbl_bidding_details_shadow where parentid='".$this->parentid."' AND version='".$this->version."'";
			$res 	= parent::execQuery($sql, $this->dbConbudget);
							
			if(DEBUG_MODE)
			{
				echo '<br><b>DB shadow archive:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			if($res)
			{
				$sql = "delete from tbl_bidding_details_shadow where parentid='".$this->parentid."' AND version='".$this->version."'";
				$res 	= parent::execQuery($sql, $this->dbConbudget);
								
				if(DEBUG_MODE)
				{
					echo '<br><b>DB shadow delete:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			}			
		}
		
		if(($this->astate == 10 || $this->astate == 11) && count($p_bpd)>0)
		{
			$sql_bpd = "INSERT INTO inventory_pos_package SET 
								parentid 		= '".$this->parentid."', 
								instrumentid	= 'InvRelease Module',
								inv_release_bpd = '".json_encode($p_bpd)."', 
								process_flag	= 0, 
								entry_time		= '".date('Y-m-d H:i:s')."'"; 
					
			$res_bpd 	= parent::execQuery($sql_bpd, $this->finance);
						
			if(DEBUG_MODE)
			{
				echo '<br><b>Fin Query:</b>'.$sql_bpd;
				echo '<br><b>Result Set:</b>'.$res_bpd;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
		}
		if($this->fin_approval == 1)
			$return_array['seeked'] = array();
		else
			$return_array['seeked'] = $seeked;
		$return_array['inv_release'] = $inv_release_array;
		$return_array['inv_release_bpd'] = $p_bpd;
		$return_array['overbooking'] = $overbooking;
		$return_array['cnt_campaign_1'] = $this->cnt_campaign_1;
		$return_array['cnt_campaign_2'] = $this->cnt_campaign_2;
		
		if(count($inv_release_array)>0)
		{
			$this->sendmail_obj->sendmailToCS($inv_release_array,$this->parentid,$this->data_city);
		}
		
		return($return_array);
	}
	
	function inventoryChecking($seeked, $live)
	{
		$pass_array = array();
		$fail_array = array();
		$this->cnt_campaign_1 = 0;
		$this->cnt_campaign_2 = 0;
		
		if(DEBUG_MODE)
		{
			echo '<hr>';
			echo '<h1>Inventory Checking Starts:</h1>';
		}
		foreach($seeked as $catid => $pin_array)
		{
			if(DEBUG_MODE)
			{
				echo '<hr>Catid:'.$catid;
				//echo '<br>Pin array:';
				//print_r($pin_array);
			}
			foreach($pin_array as $pincode=>$pos_array)
			{
				if(DEBUG_MODE)
				{
					echo '<br>Pincode:'.$pincode;
					//echo '<br>Pos array:';
					//print_r($pos_array);
				}
				
				foreach($pos_array as $pos=>$values_array)
				{
					//$pos = $values_array['p'];
					if(DEBUG_MODE)
					{
						echo '<hr>Position:'.$pos;
						print_r($values_array);
					}
					
					if($pos==100)
						$this->cnt_campaign_1 ++;
					else
						$this->cnt_campaign_2 ++;
					$is_bidder = 0;
					$inv_booked  = $live[$catid][$pincode][$pos]['inv_b'];
					$bidder = $live[$catid][$pincode][$pos]['bdr'];
					$inv_avail   = round((1-$inv_booked),2);
					if(DEBUG_MODE)
					{
						echo '<br>bidder:'.$bidder;
						echo '<br>strpos:'.stripos($bidder,$this->parentid.'-');
						echo '<br>LIVE array:';
						print_r($live[$catid][$pincode][$pos]);
						echo '<br>inv_avail:'.$inv_avail;
					}
								
					//if($this->parentid && (stripos($bidder,$this->parentid.'-') !== false))
					$bidder_array = array(); 
					$bid_res	  = array(); 
					$above_50_counter   = 0;
					$full_inv_counter	= 0;
					
					if($this->parentid && $bidder)
					{
						$bid_res 			=  $this->bidder_explode_array($bidder, $this->parentid);
						$bidder_array 		= $bid_res['bid_array'];
						$above_50_counter   = $bid_res['50_counter'];
						$full_inv_counter	= $bid_res['full_counter'];
						
						if($bidder_array[$this->parentid]['existing'] ==1)
						{ 
							$is_bidder = 1;
							$inv_avail += round($bidder_array[$this->parentid]['inv'],2);
						}
						
						if(DEBUG_MODE)
						{
							print_r($bid_res);	
							echo '<br>inv_avail after considering existing bidder:'.$inv_avail;
						}
					}else
					{
						$above_50_counter = 0;
					}
					
					$orig_inv_avail = $inv_avail;
					
					//if($above_50_counter==0 || ($above_50_counter>0 && $is_bidder==1 && round($bidder_array[$this->parentid]['inv'],2)==1.00))
					//if(($above_50_counter==0 && $inv_avail > 0.50) || ($above_50_counter>0 && $is_bidder==1 && $values_array['i']==1)) [seems no need let inv magmt work with old logic only]
					if($above_50_counter==0 || ($above_50_counter>0 && $is_bidder==1 && $values_array['i']==1))
					{
						$inv_avail = 1;
					}
					// used round since 0.2 is not eqal to 1-0.8 
					// condition 1: inv seeked <= inv avail [general condition] 
					// condition 2: for inv release no need to check inv 
					// condition 3: if existing bidder is clearing his payment despite of inv avail - give him his pos, when 100% guy would get fin approve, this would be converted to package. this wont have any impact in live inv function as we check inv seeked as 100% before disrupting positons
					if(round($values_array['i'],2)<=round($inv_avail,2) || 
					   round($values_array['i'],2)==0.00 || 
					   ($is_bidder==1 && round($values_array['i'],2)==round($bidder_array[$this->parentid]['inv'],2))
					  )
					{
						if(DEBUG_MODE)
							echo '<br>Inv Available';
						$pass_array[$catid][$pincode][$pos]=1;
						$seeked[$catid][$pincode][$pos]['s'] = 1;
					}
					else
					{
						if(DEBUG_MODE)
						{
							echo '<br>values_array[i]='.$values_array['i'];
							echo '<br>inv_avail='.$inv_avail;
							echo '<br>Inv Not Available';
						}
						// over booking / Inventory not available
						$fail_array[$catid][$pincode][$pos]=1;
						$seeked[$catid][$pincode][$pos]['s'] = 0;
					}
				} // pos array
			} // pin array
		} // cat array
		if($this->fin_approval==1)
			$return_array['pass'] = array();
		else						
			$return_array['pass'] = $pass_array;
			
		$return_array['fail'] = $fail_array;
		$return_array['cnt_campaign_1'] = $this->cnt_campaign_1;
		$return_array['cnt_campaign_2'] = $this->cnt_campaign_2;
		
		return($return_array);
	}
	
	function get_category_details($catids)
	{
		/*$sql="select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag 
		from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";*/
		//$res_area 	= parent::execQuery($sql, $this->dbConDjds);
		//$num_rows		= mysql_num_rows($res_area);
		
		$cat_params = array();
		$cat_params['page'] ='invMgmtClass';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,search_type,category_type';

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
		$num_rows = count($cat_res_arr['results']);
		if(DEBUG_MODE)
		{
			echo '<br><b>Category Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
		{		
			
			foreach($cat_res_arr['results'] as $key =>$row)
			{
				//print_r($row);
				$business_flag = trim($row['business_flag']);
				$category_type = trim($row['category_type']);
				if(((int)$business_flag)==1){
					$b2b_flag = 1;
				}
				else{
					$b2b_flag = 0;
				}
				if(((int)$category_type& 64)==64){
					$block_for_contract = 1;
				}
				else{
					$block_for_contract = 0;
				}
				if(((int)$category_type& 16)==16){
					$exclusive_flag = 1;
				}
				else{
					$exclusive_flag = 0;
				}

				$catid = $row['catid'];
				if ($row['search_type'] == 1)
					$cat_search_type = "A";
				elseif ($row['search_type'] == 2)
					$cat_search_type = "Z";
				elseif ($row['search_type'] == 3)
					$cat_search_type = "SZ";
				elseif ($row['search_type'] == 4)
					$cat_search_type = "NM";
				elseif ($row['search_type'] == 5)
					$cat_search_type = "VNM";
				else
					$cat_search_type = "L";
					
				$ret_array[$catid]['cnm'] 		= $row['category_name'];
				$ret_array[$catid]['cid'] 		= $row['catid'];
				$ret_array[$catid]['nid'] 		= $row['national_catid'];
				$ret_array[$catid]['b2b_flag']  = $b2b_flag;
				$ret_array[$catid]['bfc']  		= $block_for_contract;
				$ret_array[$catid]['x_flag']    = $exclusive_flag;
				$ret_array[$catid]['cst'] 		= $cat_search_type;
			}
		}
		return($ret_array);
	}
	
	function get_pincode_details($pincodes)
	{
		$sql="select pincode, substring_index(group_concat(main_area order by callcnt_perday desc SEPARATOR '#'),'#',1) as areaname
		from tbl_areamaster_consolidated_v3 where pincode in (".$pincodes.") group by pincode";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds);
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
		$biddder_above_50 = 0;
		$above_50_counter = 0;
		$full_inv_counter = 0;
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
				
				if($bid_parentid_array[$temp_bid[0]]['inv'] >=0.50)
				{
					$bid_parentid_array[$temp_bid[0]]['above_50'] = 1;
					$above_50_counter ++;
				}
				else
				{
					$bid_parentid_array[$temp_bid[0]]['above_50'] = 0;
				}
				
				if(round($bid_parentid_array[$temp_bid[0]]['inv'],2) == 1.00)
				{
					$bid_parentid_array[$temp_bid[0]]['full_inv'] = 1;
					$full_inv_counter ++;
				}
				else
				{
					$bid_parentid_array[$temp_bid[0]]['full_inv'] = 0;
				}
			}
		}
		$result['bid_array'] = $bid_parentid_array;
		$result['50_counter'] = $above_50_counter;
		$result['full_counter'] = $full_inv_counter;
		return($result);
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
												HCF = ".$attributes['cum']."
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
										
			$column_cum_ratio		= $pos_str."_cum_inventory";
			$column_contribution 	= $pos_str."_contribution";

			$sql_update_booking = "UPDATE tbl_clients_fp_contribution  SET 
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
	
	function fn_pincode_mapping()
	{
		$sql="select group_concat(distinct pincode*1 order by pincode) as pincode_list, max(duration) as tbd_duration from tbl_bidding_details where parentid = '".$this->parentid."'";
		$res_area 	= parent::execQuery($sql, $this->finance);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>bidding details Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res_area && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res_area))
			{
				#print_r($row);
				if(trim($row['pincode_list']))
				{
					
					$this->tbd_duration = $row['tbd_duration'];
					$pin_arr = $this->get_pincode_details($row['pincode_list']);
					$this->allarea_pin_array = array_keys($pin_arr);
					$this->allarea_pin_list = implode(",",$this->allarea_pin_array);
					if(!in_array($this->pincode, $this->allarea_pin_array))
					{   // physical pincode is not there in biddig details so recalculating everything
						unset($this->allarea_pin_array);
						unset($this->allarea_pin_list);
					}
				}
				
			}
			
			if( count($this->allarea_pin_array) <=0 && !$this->allarea_pin_list)
			{
				$this->allarea_pin_list = $this->pincode;
				$this->allarea_pin_array = explode(",", $this->pincode);
			}
			/*
			$radius = 2.5;
			$sql="SELECT fn_city_nearby_pincode_v2('".$this->pincode."','".$radius."','".$this->data_city."','".$this->latitude."','".$this->longitude."') as pinarea";
			$res_area 	= parent::execQuery($sql, $this->dbConDjds);
			$num_rows		= mysql_num_rows($res_area);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>function Query:</b>'.$sql;
				echo '<br><b>Result Set:</b>'.$res_area;
				echo '<br><b>Num Rows:</b>'.$num_rows;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			if($res_area && $num_rows > 0)
			{		
				
				while($row=mysql_fetch_assoc($res_area))
				{
					//print_r($row);
					$dataarray = explode('|P|',$row['pinarea']);
				}
				
				foreach($dataarray as $pinareaval)
				{
					$pinareavalarr= explode('~',$pinareaval);
					$pinarea_array[] = $pinareavalarr[0];
					
				}
				if(!in_array($this->pincode,$pinarea_array))
					$pinarea_array[] = $this->pincode;
				
				sort($pinarea_array);
			}
			*/
			$pinarea_array[] = $this->pincode;
			$pincode_array['n_a_a_p'] = array_intersect($pinarea_array, $this->allarea_pin_array);		
			
			$this->non_allarea_pin_array = $pincode_array['n_a_a_p'];
			$this->non_allarea_pin_list = implode(",",$pincode_array['n_a_a_p']);
		}
		else
		{
			$pincode_array['a_a_p'] = $pincode_array['n_a_a_p'] = $this->pincode;
			
			$this->allarea_pin_list = $pincode_array['a_a_p'];
			$this->allarea_pin_array = explode(",", $pincode_array['a_a_p']);
			$this->non_allarea_pin_list = $pincode_array['n_a_a_p'];
			$this->non_allarea_pin_array = explode(",", $pincode_array['n_a_a_p']);
		}
		if(DEBUG_MODE)
		{
			print_r($this);
		}
		
		return 1;
		
	}
	
}



?>
