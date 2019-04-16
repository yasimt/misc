<?php


class budgetDetailshiddenClass extends DB
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
	var  $b2c_catpin_minval = 1;
	var  $b2b_cat_minval = 50;
	var  $enable_partial = 0;
	var  $discount_f = 0.66;
	var  $budget_type = 0;
	var  $oldExpRemFxdPos=0;
	var  $expiredePackflg,$exppackday=90;
	var  $rnwcstmminpckbgt1yr=0;
	var $ExpiredDaysvalue=0;
	
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 
	 	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	var $regfeeclass_obj = null;

	function __construct($params)
	{		
		$this->params = $params;		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		/* Code for companymasterclass logic starts */
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
			$this->parentid  = strtoupper($this->params['parentid']); //initialize paretnid
		}
		
		if(trim($this->params['version']) != "")
		{
			$this->version  = $this->params['version']; //initialize version
		}
		
		if(trim($this->params['oldversion']) != "")
		{
			$this->old_version  = $this->params['oldversion']; //initialize Old version
		}
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}
		
		if(trim($this->params['mode']) != "")
		{
			$this->mode  = $this->params['mode']; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		}
		
		if(trim($this->params['option']) != "")
		{
			$this->option  = $this->params['option']; // default 1, max 7
		}
		
		

		if(trim($this->params['tenure']) != "" && $this->params['tenure'] != null)
		{
			$this->tenure_f = ($this->params['tenure']/12);
		}
		else
		{
			$this->tenure_f = 1;
			
		}
		
		$this->check_hidden();
		$this->regfeeclass_obj = new regfeeclass($params);
		
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this -> national_list_obj = new nationallistingclass($params);
			
		}

		/*
		if($this->params['mode']==3) // if it is a package then we have to check whethere its old 
		{
			$this->oldExpRemFxdPosCheck();
		}
		*/
		
	}
	function check_hidden()
	{
		$sql="select * from d_jds.tbl_contract_pincodelist_hidden where parentid ='".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num		= mysql_num_rows($res);
		//print_r($this->dbConDjds);
		//die;
		if(DEBUG_MODE)
		{
			echo '<br><b>BD Summary Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			print_r($this->dbConDjds);
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
				$this->pincode_list 		 = $row['pincodelist'];
				$this->catid_list 		 	 = $row['catlist'];
				//$this->contact_nos_list		 = $row['contact_details'];
				
			}
		}else
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Entry in tbl_contract_pincodelist_hidden Not Found";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}	
	/*	
		$this->renewal_cnt = 0;
		$sql="select count(1)  as cnt from tbl_bidding_details where parentid ='".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->finance);
		$num		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>BD Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num;
			echo '<br><b>Error:</b>'.$this->mysql_error;
			print_r($this->finance);
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
				$this->renewal_cnt 		 = $row['cnt'];
			}
		}

		if($this->renewal_cnt==0)
		{
			$sql_bde="select count(1)  as cnt from tbl_bidding_details_expired where parentid ='".$this->parentid."'";
			$res_bde 	= parent::execQuery($sql_bde, $this->finance);
			$num_bde		= mysql_num_rows($res_bde);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>BD Query:</b>'.$sql_bde;
				echo '<br><b>Result Set:</b>'.$res_bde;
				echo '<br><b>Num Rows:</b>'.$num_bde;
				echo '<br><b>Error:</b>'.$this->mysql_error;
				print_r($this->finance);
			}
			if($res_bde && $num_bde > 0)
			{		
				
				while($row_bde=mysql_fetch_assoc($res_bde))
				{
					if(DEBUG_MODE)
					{
						echo '<hr>';
						print_r($row_bde);	
					}
					$this->renewal_cnt 		 = $row_bde['cnt'];
				}
			}
			
		}*/
	}		
	
	function accrued_bid_calc($position_factor)
	{
		############## accrued bid per day #############################
		$sql	="select * from tbl_bidding_details where parentid ='".$this->parentid."' and position_flag!=100 ORDER BY catid, pincode";
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
				//die;
				$catid 	 = $row['catid']; 
				$pincode = $row['pincode']; 
				$positon_flag = $row['position_flag']; 
				//print_r($row);
				$accrued_bpd[$catid][$pincode][$positon_flag]['bpd']  = $row['bidperday'];
				if($row['inventory']>0)
				{
					$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd']= $row['bidperday']*(1/$row['inventory']);
					$accrued_p_bpd[$catid][$pincode] = $row['bidperday']*(1/$row['inventory'])*$position_factor[$positon_flag];
				}else
				{
					$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd']= 0;
					$accrued_p_bpd[$catid][$pincode] = 0;
				}
				
				//echo '<br>factor ->'.($position_factor[$positon_flag]);
				//echo '<br>bpd ->'.$row['bidperday'];
				//echo '<br>Accrued Platinum bidval ->'.$accrued_p_bpd[$catid][$pincode];
			}
		}else
		{
			$sql	="select * from tbl_bidding_details_expired where parentid ='".$this->parentid."' and position_flag!=100 ORDER BY catid, pincode";
			$res 	= parent::execQuery($sql, $this->finance);
			$num	= mysql_num_rows($res);
			
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
					//die;
					$catid 	 = $row['catid']; 
					$pincode = $row['pincode']; 
					$positon_flag = $row['position_flag']; 
					//print_r($row);
					$accrued_bpd[$catid][$pincode][$positon_flag]['bpd']  = $row['bidperday'];
					
					if($row['inventory']>0)
					{
						$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd']= $row['bidperday']*(1/$row['inventory']);
						$accrued_p_bpd[$catid][$pincode] = $row['bidperday']*(1/$row['inventory'])*$position_factor[$positon_flag];
					}				
					else
					{
						$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd']= 0;
						$accrued_p_bpd[$catid][$pincode] = 0;
					}
					//echo '<br>factor ->'.($position_factor[$positon_flag]);
					//echo '<br>bpd ->'.$row['bidperday'];
					//echo '<br>Accrued Platinum bidval ->'.$accrued_p_bpd[$catid][$pincode];
				}
			}
		}
		if(DEBUG_MODE)
		{
			echo '<hr>accrued platinum Bid Per Day';
			//print_r($accrued_bpd);
			print_r($accrued_p_bpd);
		}
		$this->accrued_bpd = $accrued_bpd;
		$this->accrued_p_bpd = $accrued_p_bpd;
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
			
		}	
	}
	
	function get_live_inventory()
	{
	
        $bidding_version = (($this->old_version) ? $this->old_version : $this->version);
        
        if($this->old_version)
			$live_bidding_version_cond = " AND version = '".$bidding_version."'";
		
		$sql	="SELECT * FROM tbl_bidding_details WHERE parentid ='".$this->parentid."' ".$live_bidding_version_cond."  ORDER BY catid, pincode";
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
				//print_r($pin_array);
				$inv_seeked[$catid][$pincode]['p']    = $row['position_flag'];
				//$inv_seeked[$catid][$pincode]['c']    = $row['callcount'];
				//$inv_seeked[$catid][$pincode]['b']    = $row['bidvalue'];
				//$inv_seeked[$catid][$pincode]['bgt']  = $row['sys_budget'];
				$inv_seeked[$catid][$pincode]['i'] 	  = $row['inventory'];
				//$inv_seeked[$catid][$pincode]['s'] 	  = 1; // 1-pass 0-fail 
				//$inv_seeked[$catid][$pincode]['abgt'] = $row['actual_budget'];
				$pin_arr[] = $pincode;
				$cat_arr[] = $catid;		
			}
			
			$this->pincode_list 		 = implode(",", array_unique($pin_arr));
			$this->catid_list 		 	 = implode(",", array_unique($cat_arr));

		} else
		{
			$sql_bde	="SELECT * FROM tbl_bidding_details_expired WHERE parentid ='".$this->parentid."' ".$live_bidding_version_cond." ORDER BY catid, pincode";
			$res_bde 	= parent::execQuery($sql_bde, $this->finance);
			$num_bde	= mysql_num_rows($res_bde);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql_bde;
				echo '<br><b>Result Set:</b>'.$res_bde;
				echo '<br><b>Num Rows:</b>'.$num_bde;
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if($res_bde && $num_bde > 0)
			{

				while($row_bde=mysql_fetch_assoc($res_bde))
				{
					//echo '<hr>';
					//print_r($row);
					$catid 	 = $row_bde['catid']; 
					$pincode = $row_bde['pincode']; 
					//print_r($pin_array);
					$inv_seeked[$catid][$pincode]['p']    = $row_bde['position_flag'];
					//$inv_seeked[$catid][$pincode]['c']    = $row['callcount'];
					//$inv_seeked[$catid][$pincode]['b']    = $row['bidvalue'];
					//$inv_seeked[$catid][$pincode]['bgt']  = $row['sys_budget'];
					$inv_seeked[$catid][$pincode]['i'] 	  = $row_bde['inventory'];
					//$inv_seeked[$catid][$pincode]['s'] 	  = 1; // 1-pass 0-fail 
					//$inv_seeked[$catid][$pincode]['abgt'] = $row['actual_budget'];
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;		
				}
				
				$this->pincode_list 		 = implode(",", array_unique($pin_arr));
				$this->catid_list 		 	 = implode(",", array_unique($cat_arr));

			
			}
            else
		    {
				$sql_bds	="SELECT * FROM tbl_bidding_details_shadow WHERE parentid ='".$this->parentid."' AND version = '".$bidding_version."' ORDER BY catid, pincode";
				$res_bds 	= parent::execQuery($sql_bds, $this->dbConbudget);
				$num_bds	= mysql_num_rows($res_bds);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB Shadow Query:</b>'.$sql_bds;
					echo '<br><b>Result Set:</b>'.$res_bds;
					echo '<br><b>Num Rows:</b>'.$num_bds;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				if($res_bds && $num_bds > 0)
				{

					while($row_bds=mysql_fetch_assoc($res_bds))
					{
						//echo '<hr>';
						//print_r($row);
						$catid 	 = $row_bds['catid']; 
						$pincode = $row_bds['pincode']; 
						$inv_seeked[$catid][$pincode]['p']    = $row_bds['position_flag'];
						$inv_seeked[$catid][$pincode]['i'] 	  = $row_bds['inventory'];
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;		
					}
					
					$this->pincode_list 		 = implode(",", array_unique($pin_arr));
					$this->catid_list 		 	 = implode(",", array_unique($cat_arr));

				
				} else {
				
					$sql_bda	="SELECT * FROM tbl_bidding_details_shadow_archive WHERE parentid ='".$this->parentid."' AND version = '".$bidding_version."' GROUP BY catid, pincode, version ORDER BY catid, pincode";
					$res_bda 	= parent::execQuery($sql_bda, $this->dbConbudget);
					$num_bda	= mysql_num_rows($res_bda);
					
					if(DEBUG_MODE)
					{
						echo '<br><b>DB Shadow Archive Query:</b>'.$sql_bda;
						echo '<br><b>Result Set:</b>'.$res_bda;
						echo '<br><b>Num Rows:</b>'.$num_bda;
						echo '<br><b>Error:</b>'.$this->mysql_error;
					}
					
					if($res_bda && $num_bda > 0)
					{

						while($row_bda=mysql_fetch_assoc($res_bda))
						{
							$catid 	 = $row_bda['catid']; 
							$pincode = $row_bda['pincode']; 

							$inv_seeked[$catid][$pincode]['p']    = $row_bda['position_flag'];
							$inv_seeked[$catid][$pincode]['i'] 	  = $row_bda['inventory'];
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;		
						}
						
						$this->pincode_list 		 = implode(",", array_unique($pin_arr));
						$this->catid_list 		 	 = implode(",", array_unique($cat_arr));

					
					}
				
				}
			}
			
		} 
		
		return($inv_seeked);
	}
	
	function getBudget()	
	{	
		/*if($this->mode==4)
		{
			###### fetching Live inventory ###########
			$live_inventory = $this->get_live_inventory();
			if(DEBUG_MODE)
			{
				echo '<br>Live Inventory';
				print_r($live_inventory);
			}

		}*/
		
		if(empty($this->catid_list) || empty($this->pincode_list))
		{
			$result['result'] 		 = array();
			$result['error']['code'] = 1;
			$result['error']['msg']  = "category list Or pincode list Empty";
			return($result);
		}
		$cat_array = $this->get_category_details($this->catid_list);
		$pin_array = $this->get_pincode_details($this->pincode_list);
		
		
		if(DEBUG_MODE)
		{
			print_r($cat_array);
			print_r($pin_array);
		}
		
		if(count($cat_array)==0)
		{
			$result['result'] 		 = array();
			$result['error']['code'] = 1;
			$result['error']['msg']  = "InValid category list";
			return($result);
		}
		
		if(count($pin_array)==0)
		{
			$result['result'] 		 = array();
			$result['error']['code'] = 1;
			$result['error']['msg']  = "InValid pincode list";
			return($result);
		}	
		// tbl_fixedposition_factor 215 db_finanace
		$sql="select * from tbl_fixedposition_factor WHERE position_flag > 0 AND position_flag < 3  /*where active_flag=1*/";/* hidden checks for first position*/
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
					$n_position_factor[$pos] 		= $factor;
					$x_position_factor[$pos] 		= $x_factor;
					$f_position_factor[$pos] 		= $f_factor;
					$alloc_summary_array[$pos] 		= 0;
				}
				
				if($f_factor>0)
					$r_position_factor[$pos] = (1/$f_factor);
				else
					$r_position_factor[$pos] = 0;
			}
			
			       $n_position_factor[2] = $n_position_factor[1];
			       $x_position_factor[2] = $x_position_factor[1];
			       $f_position_factor[2] = $f_position_factor[1];
			       $r_position_factor[2] = $r_position_factor[1];
			
		}
		//print_r($f_position_factor);
		//print_r($r_position_factor);
		
		//$this->accrued_bid_calc($r_position_factor);
		
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
				
				//if(DEBUG_MODE)
				//{
				//	echo '<hr>';
				//	print_r($row);
				//}
				
				if($this->budget_type==2)
				{
					//$package_min_budget  =  $row['minbudget']*2;
					//$package_min_budget  =  $row['top_minbudget'];
					$package_min_budget    =  $row['top_minbudget_package'];
					$fp_min_budget    	   =  $row['top_minbudget_fp'];
				}
				else
				{
					//$package_min_budget  =  $row['minbudget'];
					$package_min_budget    =  $row['minbudget_package'];
					$fp_min_budget    	   =  $row['minbudget_fp'];
				}
					
				$pinminbudget      =  $row['pinminbudget'];
				$callcnt_growth_rate =  1 + ($row['callcnt_per']/100);
				$city_min_budget     = 	$row['cityminbudget'];
				$pincity_min_budget  = 	$pinminbudget * count($pin_array);
				$all_pincode_count   = 	$row['allpincnt'];
				$bidvalue_premium    =  1 + ($row['bidvalue_per']/100);
				$disc=$combodiscount/100;
				$upldrts_minbudget		=$row['minbudget_package'];
				$upldrts_top_minbudget	=$row['top_minbudget_package'];
				$upldrts_cstm_minbudget_package	=$row['cstm_minbudget_package'];
				$weekly_package_budget	=$row['weeklypack'];
				$monthly_package_budget	=$row['monthlowvaluepack'];
				$maxrnwmini				=($row['maxrnwmini']/12);
				$maxrnwbasic	        =($row['maxrnwbasic']/12);
				$maxrnwpremium	        =($row['maxrnwpremium']/12);
				$package_mini	        =($row['package_mini']);
				$package_mini_ecs	        =($row['package_mini_ecs']);
				$package_mini_minimum	        =($row['package_mini_minimum']);
				$package_premium	    =($row['package_premium']); 
				$package_premium_upfront	    =($row['package_premium_upfront']); 
				$minbudget_national     =($row['minbudget_national']);
				$combodiscount=$row['combodiscount']; 
				if($combodiscount>0){
					$disc=$combodiscount/100;
					$package_mini_discount	=$package_mini - ($package_mini * $disc) ;
					$package_mini_two_years	=$package_mini + ($package_mini * $disc) ;
					$package_mini_minimum_discount	=$package_mini_minimum - ($package_mini_minimum * $disc) ;
					
				}
				$price_mini_upfront_discount = $package_mini_discount;
				$price_mini_upfront_minimum_discount = $package_mini_minimum_discount;
				$price_mini_upfront_two_years  = $package_mini_two_years; 


				$minimumbudget_national		= ($row['minimumbudget_national']);
				$maxbudget_national			= ($row['maxbudget_national']);
				$statebudget_national		= ($row['statebudget_national']);
				$minupfrontbudget_national	= ($row['minupfrontbudget_national']);
				$maxupfrontbudget_national	= ($row['maxupfrontbudget_national']);
				$stateupfrontbudget_national= ($row['stateupfrontbudget_national']);
				
				$this->exppackday = $row['exppackday'];
				
				
				if($this->mode==3) 
				{
					$this->oldExpRemFxdPosCheck();					
					$this->getpackagerenewcustomminbudget();
					
				}
				
				
				if($this->oldExpRemFxdPos!=0 && $row['rmfxdpackbdgt']>0)
				{
					$this->remfxdpospackbdgt= $row['rmfxdpackbdgt'];
				}

				if($this->expiredePackflg!=0 && $row['exppackval']>0)
				{
					$this->expiredePackval = $row['exppackval'];
					$this->expiredePackval_2yrs = $row['exppackval_2'];
				}				
				
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
			echo '<br> assign $this->exppackday:'.$this->exppackday;			
		}	
		//$city_min_budget = 5000; 
		
		
		############ setting default values Starts ################
		$callcount_default = 1/$all_pincode_count;
		$searchcount_default = 1/$all_pincode_count;
		$az_flag  = 0 ;
		$all_flag = 0 ;
		
		foreach($cat_array as $catid=>$cat_val_array)
		{
			//echo '<br>Catid:'.$catid;
			
			
			//########## old budgeting values ############
			//if($cat_array[$catid]['x_flag'] ==1)
				//$position_factor = $x_position_factor;
			//else
				//$position_factor = $n_position_factor;
			
			########## new budgeting values ############
			$position_factor = $f_position_factor;
			
			if($cat_array[$catid]['b2b_flag']==1)
				$bidvalue	= 1684;
			else
				$bidvalue	= 84;
			
			if($cat_array[$catid]['cst']=="NM" || $cat_array[$catid]['cst']=="VNM" || $cat_array[$catid]['cst']=="A" || $cat_array[$catid]['cst']=="Z")
			{
				$az_flag ++;
			}
			else
			{
				$all_flag ++;
			}
				
			$budget_array[$catid]['cid']   = $catid;
			$budget_array[$catid]['ncid']  = $cat_array[$catid]['nid'];
			$budget_array[$catid]['cnm']   = $cat_array[$catid]['cnm'];
			$budget_array[$catid]['cst']   = $cat_array[$catid]['cst'];
			$budget_array[$catid]['bval']  = $bidvalue;
			$budget_array[$catid]['bflg']  = $cat_array[$catid]['b2b_flag'];
			$budget_array[$catid]['c_bgt'] = 0;
			if($cat_array[$catid]['b2b_flag']==1)
			{
				$budget_array[$catid]['bm_bgt'] =  $this->b2b_cat_minval;
				$min_budget = max(0.10,round($this->b2b_cat_minval/count($pin_array),2));
				$fin_budget = $this->b2b_cat_minval;
			}
			else
			{
				$budget_array[$catid]['bm_bgt'] =  0;
				$min_budget = $this->b2c_catpin_minval;
				$fin_budget = $this->b2b_cat_minval*count($pin_array);
			}
			
			$budget_array[$catid]['f_bgt'] = $fin_budget;		
			$budget_array[$catid]['xflg'] = $cat_array[$catid]['x_flag'];		
			if($this->mode==3)
				$best_pos = 100;
			elseif($this->mode==2)
				$best_pos = $this->option;
			elseif($this->mode==5)
				$best_pos = 0;
			else
				$best_pos = 1;
			$best_budget = $min_budget;
			foreach($pin_array as $pincode=>$pin_val_array)
			{
				//echo '<br>Pincode:'.$pincode;	
				$budget_array[$catid]['pin_data'][$pincode]['anm']	= $pin_array[$pincode]['anm'];
				$budget_array[$catid]['pin_data'][$pincode]['cnt']	= $callcount_default;
				$budget_array[$catid]['pin_data'][$pincode]['cnt_f']= $callcount_default*$this->tenure_f;
				$budget_array[$catid]['pin_data'][$pincode]['srch_cnt']	 = $searchcount_default;
				$budget_array[$catid]['pin_data'][$pincode]['srch_cnt_f']= $searchcount_default*$this->tenure_f;
				$budget_array[$catid]['pin_data'][$pincode]['sbflg']= 0;
				$budget_array[$catid]['pin_data'][$pincode]['dummy']= 1;
				foreach($position_factor as $pos=>$pos_factor)
				{
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_booked'] = 0;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidder']     = "";
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidvalue']   = $bidvalue * $pos_factor;
	
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget']     = $min_budget;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail']  = 1;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['is_bidder']  = 0;
				}
				$budget_array[$catid]['pin_data'][$pincode]['best_flg'] = $best_pos;
				$budget_array[$catid]['pin_data'][$pincode]['best_bgt'] = $best_budget;
				$alloc_total += 1;
			}
			$catpin_budget_array[$catid][$pincode] = $min_budget;
			
		}
		if(DEBUG_MODE)	
		{
			echo '<br> Dummy values';
			print_r($budget_array);
		}
		
		############ setting default values Ends ################
		############ discount table ##############
		
		$sql="select * from tbl_catpin_discount_percentage where catid in (".$this->catid_list.") and pincode in (".$this->pincode_list.") ORDER BY catid, pincode";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num_rows		= mysql_num_rows($res);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Discount table Query:</b>'.$sql;
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
					echo '<hr>';
					print_r($row);
				}
				$catid		= $row['catid'];
				$pincode	= $row['pincode'];
				foreach($position_factor as $pos => $pos_factor)
				{
					$discount_array[$catid][$pincode][$pos] = $row["pos".$pos."_discount"];
				}
			}
		}
		if(DEBUG_MODE)	
		{
			echo '<br> Discount Array';
			print_r($discount_array);
		}
		
		############ Renewal percentage Slabs Starts #####################
		//if($this->mode==4)
		{
			$sql      ="select * from payment_version_approval where parentid = '".$this->parentid."' and is_pdg = 1 and disruption_flag in (0,1) ORDER BY approval_date desc limit 1";
			$res 	  = parent::execQuery($sql, $this->finance);
			$num_rows = mysql_num_rows($res);
			
			if(DEBUG_MODE)
			{
				print_r($this->finance);
				echo '<br><b>Renewal percentage Slabs Query:</b>'.$sql;
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
						echo '<hr>';
						print_r($row);
					}
					$approval_date = $row['approval_date'];
				}
				$approval_year = date('Y', strtotime($approval_date));
				$current_date  = date('Y-m-d'); 	
				$days_diff = date_diff(date_create($current_date), date_create($approval_date));
				$num_days = $days_diff->format('%a');
				$yr_idx = min((int)(($num_days-1)/365),5); // -1 days handling for exact 1 yr and so on
			}
			else
			{
				$approval_year = date('Y');
				$num_days = 0;
			}
			
			/*
			$renewal_percenatge_arr['2012'] = 2.50;
			$renewal_percenatge_arr['2013'] = 2.25;
			$renewal_percenatge_arr['2014'] = 2;
			$renewal_percenatge_arr['2015'] = 1.75;
			$renewal_percenatge_arr['2016'] = 1.50;
			$renewal_percenatge_arr['2017'] = 1.33;
			
						
			if($approval_year <= 2012)
				$renewal_precent = 2.50;
			elseif($approval_year >= 2017)
				$renewal_precent = 1.33;
			else
				$renewal_precent = $renewal_percenatge_arr[$approval_year];
			*/
			
			$renewal_percenatge_arr[5] = 2.50;
			$renewal_percenatge_arr[4] = 2.25;
			$renewal_percenatge_arr[3] = 2;
			$renewal_percenatge_arr[2] = 1.75;
			$renewal_percenatge_arr[1] = 1.50;
			$renewal_percenatge_arr[0] = 1.33;
			
						
			$renewal_precent = $renewal_percenatge_arr[$yr_idx];
				
			if(DEBUG_MODE)	
			{
				echo '<br> Approval_date:'.$approval_date;
				echo '<br> Current_date:'.$current_date;
				echo '<br> Approval_year:'.$approval_year;
				echo '<br> days_diff:'.$num_days;
				echo '<br> year_index:'.$yr_idx;
				echo '<br> renewal_percenatge_arr:'; print_r($renewal_percenatge_arr);
				echo '<br> renewal percent:'.$renewal_precent;
			}
		}
		
		############ Renewal percentage Slabs Ends   #####################
		
		############ FP table ##############
		
		$sql = "SELECT  * FROM tbl_bidcatdetails_leadandsupreme_shadow WHERE bid_catid IN (".$this->catid_list.") AND pincode IN (".$this->pincode_list.") AND campaignid=17 AND partial_ddg_ratio>0 AND DATEDIFF(CURRENT_DATE,uptdate)<15 ORDER BY uptdate DESC";
		$res = parent::execQuery($sql, $this->finance);
		if($res && mysql_num_rows($res)>0)
		{
			
			while($row = mysql_fetch_assoc($res))
			{
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['booked_inv'] = $row['partial_ddg_ratio'];
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['bidder']     = $row['parentid'].'-'.$row['bid_lead'].'-'.$row['partial_ddg_ratio'].'-'.$row['partial_ddg_ratio'].'-'.'0';
			}
		}
		
		$sql = "SELECT  a.* FROM 
				tbl_bidcatdetails_lead a JOIN tbl_companymaster_finance b
				ON a.parentid = b.parentid
				AND a.campaignid = b.campaignid 
				WHERE a.bid_catid IN (".$this->catid_list.")
				AND a.pincode IN (".$this->pincode_list.") 
				AND b.campaignid=17 AND a.partial_ddg_ratio>0 AND b.balance>0";
		$res = parent::execQuery($sql, $this->finance);
		if($res && mysql_num_rows($res)>0)
		{
			
			while($row = mysql_fetch_assoc($res))
			{
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['booked_inv'] = $row['partial_ddg_ratio'];
			    $booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['bidder']     = $row['parentid'].'-'.$row['bid_lead'].'-'.$row['partial_ddg_ratio'].'-'.$row['partial_ddg_ratio'].'-'.$row['partial_ddg_ratio'];
			}
		}
		
		
		
		$sql="select * from tbl_fixedposition_pincodewise_bid where catid in (".$this->catid_list.") and pincode in (".$this->pincode_list.") ORDER BY catid, pincode";
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
					echo '<hr>';
					print_r($row);
				}
				
				$catid		= $row['catid'];
				$pincode	= $row['pincode'];
				$callcount	= $row['callcount'] * $callcnt_growth_rate;
				$searchcount= $row['reach_count'] * $callcnt_growth_rate;
				$callcount  = max($callcount, $callcount_default);
				$searchcount= max($searchcount, $searchcount_default);

				//########## old budgeting values ############
				//if($cat_array[$catid]['x_flag'] ==1)
				//{
					//$position_factor = $x_position_factor;
					//$b_val = $row['x_bidvalue'];
				//}
				//else
				//{
					//$position_factor = $n_position_factor;
					//$b_val = $row['bidvalue'];
				//}
				
				########## new budgeting values ############	
				$position_factor = $f_position_factor;
				$b_val = $row['x_bidvalue']*$bidvalue_premium;
					
				if($cat_array[$catid]['b2b_flag']==1)
					$bidvalue	= max(1684,$b_val);
				else
					$bidvalue	= max(84,$b_val);
					
				$row['category_minbudget'] = 0; // as per instructions from ajay mail dated 23rd oct 2015
				$budget_array[$catid]['bval']  = $bidvalue;
				$budget_array[$catid]['c_bgt'] = 0;
				if($cat_array[$catid]['b2b_flag']==1)
				{
					$cat_min_budget[$catid]   = $row['category_minbudget'] * $this->tenure_f; // for b2b cat no factor to be multiplied [Note: tenure_f removed as per instruction from Ajay 31stJuly2015]
					//$cat_min_budget[$catid]   = $row['category_minbudget'];
					$budget_array[$catid]['bm_bgt'] = $cat_min_budget[$catid];
				}
				else
				{
					$cat_min_budget[$catid]   = $row['pincodefactor']*$row['category_minbudget']*$this->tenure_f; // [Note: tenure_f removed as per instruction from Ajay 31stJuly2015]
					//$cat_min_budget[$catid]   = $row['pincodefactor']*$row['category_minbudget']; 
					$budget_array[$catid]['bm_bgt'] = 0;
				}
				
				$budget_array[$catid]['f_bgt'] = 0;			
	
				//$budget_array[$catid]['pin_data'][$pincode]['anm']	= $pin_array[$pincode]['anm'];
				
				$budget_array[$catid]['pin_data'][$pincode]['cnt']		= $callcount;
				$budget_array[$catid]['pin_data'][$pincode]['cnt_f']	= $callcount*$this->tenure_f;
				$budget_array[$catid]['pin_data'][$pincode]['srch_cnt']		= $searchcount;
				$budget_array[$catid]['pin_data'][$pincode]['srch_cnt_f']	= $searchcount*$this->tenure_f;
				$budget_array[$catid]['pin_data'][$pincode]['sbflg']	= 0;
				$budget_array[$catid]['pin_data'][$pincode]['dummy']	= 0;
				$total_inv_booked = 0;
				$x_sold_flg      = 0; 
				$f_sold_flg		= 0;
				//print_r($booked_position_arr);
				
				foreach($position_factor as $pos => $pos_factor)
				{
					$is_bidder = 0;
					$inv_booked  = $booked_position_arr[$catid][$pincode][$pos]['booked_inv'];// $row["pos".$pos."_inventory_booked"];
					$bidder 	 = $booked_position_arr[$catid][$pincode][$pos]['bidder'];// $row["pos".$pos."_inventory_booked"];$row["pos".$pos."_bidder"];
					//$inv_avail   = (1-$inv_booked);
					$inv_avail   = round((1-$inv_booked),2);
					$total_inv_booked += $inv_booked;						
					$bidder_array = array(); 
					$bid_res	  = array(); 
					$above_50_counter = 0;
					$full_inv_counter = 0;
					
					//if($this->parentid && (stripos($bidder,$this->parentid.'-') !== false))
					if($this->parentid && $bidder)
					{
						$bid_res =  $this->bidder_explode_array($bidder, $this->parentid);
						
						$bidder_array 		= $bid_res['bid_array'];
						
						$above_50_counter   = $bid_res['50_counter'];
						$full_inv_counter   = $bid_res['full_counter'];
						
						if($bidder_array[$this->parentid]['existing'] ==1)
						{ 
							
							$is_bidder = 1;
							$inv_avail += $bidder_array[$this->parentid]['inv'];
						}
					}else
					{
						$above_50_counter = 0;
					}
					
					if(DEBUG_MODE)
					{
						echo '<br>Pos : '.$pos;
						print_r($bid_res);
						echo '<br>inv avail:'.$inv_avail;
						echo '<br>discount %:'.((100-$discount_array[$catid][$pincode][$pos])/100);
					}
					$orig_inv_avail = $inv_avail;
					
					//if($above_50_counter==0 || ($above_50_counter>0 && $is_bidder == 1 && $full_inv_counter==0 && $bidder_array[$this->parentid]['above_50']==1))
					if($inv_avail > 0.50 || ($is_bidder==1 && round($inv_avail,2) == 0.50))  // [disruption logic <50% for existing bidder=50%]
					{
						$inv_avail = 1;
					}
					// exlusive sold for this category pincode
					if($pos==0 && $inv_booked>0)
						$x_sold_flg  = 1; 
						
					if($pos>0 && $pos!=100 && $inv_booked>0)
						$f_sold_flg  = 1; 
						
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_booked'] = $inv_booked;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidder']     = $bidder;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidvalue']   = $bidvalue * $pos_factor;
					
					############## custom position handling #################
					/*if($this->mode==4 && $live_inventory[$catid][$pincode]['bflag'] != 1 && $x_sold_flg  != 1)
					{
						if(DEBUG_MODE)
						{
							echo '<hr>Renewal:';
							echo '<br>enable parial flag:'.$this->enable_partial;
							echo '<br>inv avail:'.$inv_avail;
							echo '<br>pos:'.$pos;
							print_r($live_inventory[$catid][$pincode]);
						}
						if(($this->enable_partial==1 && $inv_avail > 0.50) || ($this->enable_partial==0 && $inv_avail==1))
						{
							//$inv_avail = $live_inventory[$catid][$pincode]['i'];
							# consider this positon selling giving preference as best position
							if(DEBUG_MODE)
								echo '<br>Loop 1:consider this positon selling giving preference as best position';
							if($live_inventory[$catid][$pincode]['p'] == $pos && $live_inventory[$catid][$pincode]['i'] > 0)
								$live_inventory[$catid][$pincode]['bflag'] = 1;
						}
						else
						{
							if(DEBUG_MODE)
								echo '<br>Loop 2:partial inventory not allowed';

							$inv_avail = 0;
							if($live_inventory[$catid][$pincode]['p'] == $pos)	
								$live_inventory[$catid][$pincode]['bflag'] = 2;
						}
						
					}*/
				
					if(($this->enable_partial==0 && $inv_avail<1) || ($pos>0 && $x_sold_flg==1 && $pos!=100))
						$inv_avail = 0;
					
					
					if($inv_avail > 0)
					{
						if(!isset($discount_array[$catid][$pincode][$pos]))
							$discount_array[$catid][$pincode][$pos]=0;
							
						$cat_pin_budget = round($bidvalue * $pos_factor * $callcount * $this->tenure_f * $inv_avail * ((100-$discount_array[$catid][$pincode][$pos])/100));
						
						########### old yr accrued budget - catid pin pos ##############
						//if($this->accrued_bpd[$catid][$pincode][$pos]['a_bpd']>0)
						//{
								////if(DEBUG_MODE)
								////{
								////	echo '<br>I am here';
								////	print_r($this->accrued_bpd[$catid][$pincode]);
								////	echo '<br>last budget'.$this->accrued_bpd[$catid][$pincode][$pos]['a_bpd']*($this->tenure_f*360)*1.33;
								////	echo '<br>JK budget'.$cat_pin_budget;
								////}
								//$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['l_budget'] = $this->accrued_bpd[$catid][$pincode][$pos]['a_bpd']*($this->tenure_f*360)*1.33;
								//$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['n_budget'] = $cat_pin_budget;
								//$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['a_bpd'] = $this->accrued_bpd[$catid][$pincode][$pos]['a_bpd'];
								
								//$cat_pin_budget = min($cat_pin_budget, $this->accrued_bpd[$catid][$pincode][$pos]['a_bpd']*($this->tenure_f*360)*1.33);		
						//}
						########### old yr accrued budget - catid pin (recalc baed on Platinum price)##############
						
						if($this->accrued_p_bpd[$catid][$pincode]>0)
						{
								////if(DEBUG_MODE)
								////{
								////	echo '<br>I am here';
								////	print_r($this->accrued_p_bpd[$catid][$pincode]);
								////	echo '<br>last budget'.$this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*360)*1.33*($pos_factor);
								////	echo '<br>JK budget'.$cat_pin_budget;
								////}
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['l33_budget'] = $this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*365)*$renewal_precent*$pos_factor;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['n_budget'] = $cat_pin_budget;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['l0_budget'] = $this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*365)*1.00*$pos_factor;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['a_bpd']    = $this->accrued_p_bpd[$catid][$pincode]*$pos_factor;
								
								if($discount_array[$catid][$pincode][$pos]>0)
								{
									$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*365)*$renewal_precent*$pos_factor);		
								}
								else
								{
									$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*365)*$renewal_precent*$pos_factor);		
									$cat_pin_budget = max($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*365)*1.00*$pos_factor);
								}
								
								
									
						}
						##########
						
						
						//### Old logic bid values ###############
						//$min_cat_pin_budget = round($cat_min_budget[$catid] * $pos_factor * $inv_avail);
						// [removing positon fator in new logic as per mail from Ajay
						### New logic bid values ###############
						$min_cat_pin_budget = round($cat_min_budget[$catid] * $inv_avail);
						
						if($cat_array[$catid]['b2b_flag']==1)
						{
							$cat_pin_budget =  max(0.10, $cat_pin_budget);
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget']     = $cat_pin_budget;
							$b2b_cat_pin_budget[$catid]['pos'][$pos] += $cat_pin_budget;
						}
						else
						{
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget']     = max($cat_pin_budget, $min_cat_pin_budget, $this->b2c_catpin_minval);
						}
					}
					else
					{
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget'] = 0;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail'] = 0;
					}		
					/*if($f_sold_flg==1) // if any fixed position already sold- no exclusive selling can take place
					{
					$budget_array[$catid]['pin_data'][$pincode]['pos'][1]['inv_avail']  = 0;
					}
					else
					{
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail']  = $inv_avail;
					}*/
					//$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail']  = $inv_avail;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['is_bidder']  = $is_bidder;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['orig_inv_avail']  = $orig_inv_avail;
					$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['val_dis']  = ((100-$discount_array[$catid][$pincode][$pos])/100);
				}
				if($f_sold_flg==1) // if any fixed position already sold- no exclusive selling can take place
				{
						$budget_array[$catid]['pin_data'][$pincode]['pos'][0]['inv_avail']  = 0;
				}
				else
				{
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail']  = $inv_avail;
				}
				
				
				if($total_inv_booked>0)
					$budget_array[$catid]['pin_data'][$pincode]['sbflg']	= 1;
				################################ best budget calc logic ###############################
				$max_budget  = 0;
				$max_pos 	 = 0;
				$best_pos 	 = -1;
				$best_budget = 0;
				
				unset($pos_value);
				unset($mode1_bp);
				
				if(DEBUG_MODE)
				{
					echo '<hr><H1>best budget calc logic</H1></hr>';
					echo '<br>Mode:'.$this->mode;
					echo '<br>option:'.$this->option;
					echo '<br>Catid:'.$catid;
					echo '<br>Pincode:'.$pincode;
					print_r($budget_array[$catid]['pin_data'][$pincode]);
					echo '<br>';
				}
				/*if($this->mode == 5) // exclusive position
				{
					if($budget_array[$catid]['pin_data'][$pincode]['pos'][0]['inv_avail']>0)
					{
						if(DEBUG_MODE)
							echo '<br>Exclusive position - 100% inventory Available - so considering this as best pos.';
							
						$max_pos = 0;
						$max_budget =$budget_array[$catid]['pin_data'][$pincode]['pos']['0']['budget'];
					}
					else
					{
						if(DEBUG_MODE)
							echo '<br>Exclusive position - seeking max budget for all available positions';
							
						foreach($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos=>$pos_value)
						{
							if(DEBUG_MODE)
							{
								echo '<hr>max_budget:'.$max_budget;
								echo '<br>max_pos:'.$max_pos;
								echo '<br>pos:'.$pos.'<br>';
								print_r($pos_value);
							}	
							if($pos_value['budget'] > $max_budget && $pos_value['inv_avail']>0 && $pos!=0)
							{
								$max_budget = $pos_value['budget'];
								$max_pos    = $pos;
							}												
						}
						if($max_budget==-1)
						{
							$max_pos = 100;
							$max_budget =$budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
						}
					}
					
					$best_pos 	 = $max_pos;
					$best_budget = $max_budget;
				}
				elseif($this->mode == 4) // Custom position
				{
					$renew_pos = 0;
					if(DEBUG_MODE)
						print_r($live_inventory[$catid][$pincode]);
					if($live_inventory[$catid][$pincode]['bflag']==1 || $live_inventory[$catid][$pincode]['p'] == 100) // in same positon got 50% or more inventory so considering this as best pos.
					{
						if(DEBUG_MODE)
							echo 'in same positon got 50% or more inventory so considering this as best pos.';
						
						$max_pos = $live_inventory[$catid][$pincode]['p'];
						$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos'][$max_pos]['budget'];
						$renew_pos =1; 
					}
					else // best position - max budget for all all positions available
					{
						if(DEBUG_MODE)
							echo '<br>best position - seeking max budget for all available positions';
							
						foreach($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos=>$pos_value)
						{
							if(DEBUG_MODE)
							{
								echo '<hr>max_budget:'.$max_budget;
								echo '<br>max_pos:'.$max_pos;
								echo '<br>pos:'.$pos.'<br>';
								print_r($pos_value);
							}	
							if($pos_value['budget'] > $max_budget && $pos_value['inv_avail']>0 && $pos!=0)
							{
								$max_budget = $pos_value['budget'];
								$max_pos    = $pos;
							}												
						}
						if($max_budget==-1)
						{
							$max_pos = 100;
							$max_budget =$budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
						}
						$renew_pos =2;
					}
					if($renew_pos>1)
						$renew_msg = 2;
					$best_pos 	 = $max_pos;
					$best_budget = $max_budget;
				}else*/
				if($this->mode == 2) // fixed positon
				{
					//$max_pos    = 100;
					//$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos'][100]['budget'];
					// $this->option >= $pos)
					// $best_budget[$catid][$pincode]['best_flag']  = 0;
					if(DEBUG_MODE)
					{
						print_r($budget_array[$catid]['pin_data'][$pincode]['pos']);
					}
					foreach($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos=>$pos_value)
					{
						if(DEBUG_MODE)
						{
							echo '<hr>max_budget:'.$max_budget;
							echo '<br>cat id :'.$catid;
							echo '<br>pincode :'.$pincode;
							echo '<br>max_pos:'.$max_pos;
							echo '<br>pos:'.$pos.'<br>';
							print_r($pos_value);
						}						
						if($pos >= $this->option)
						{	
							//echo '<br>Here:'.$pos.' >= '.$this->option;
							//if($pos_value['budget'] > $max_budget && $pos_value['inv_avail']>0)
							if( $pos > 0 && $pos < 3 && $pos_value['budget']>0 &&  $max_budget <= 0 && $pos_value['inv_avail']>0 )
							{
								$max_budget = $pos_value['budget'];
								$max_pos    = $pos;
							}
						}
					}
					
						$best_pos 	 = $max_pos;
						$best_budget = $max_budget;
				}
				
				/*elseif($this->mode==1) //  best positon
				{
					$opt=1;
					//print_r($budget_array[$catid][$pincode]['pos']);
					foreach($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos=>$pos_value)
					{
						if($pos != 0 && $pos != 100 && $pos_value['inv_avail'] > 0)
						{
							$mode1_bp['POS'.$pos] = $pos_value['budget'];
						}
					}
					
					if(DEBUG_MODE)
					{
						print_r($mode1_bp);
					}
					if(is_array($mode1_bp))
					{
						//arsort($mode1_bp);
						//multisort($mode1_bp, array_values(SORT_ASC), array_keys(SORT_DESC)); 
						array_multisort(array_values($mode1_bp), SORT_DESC, array_keys($mode1_bp), SORT_ASC, $mode1_bp);
					}
					
					$s_mode1_bp = $mode1_bp;
					if(DEBUG_MODE)
					{
						print_r($s_mode1_bp);		
					}
					
					if(count($s_mode1_bp)>=$this->option)
						$match_option = $this->option;
					else
						$match_option = count($s_mode1_bp);
					
					if(is_array($s_mode1_bp))
					{	
						foreach($s_mode1_bp as $pos=>$pos_budget)
						{
							if($match_option == $opt)
							{
								$max_pos = $pos;
								$max_budget = $pos_budget;
							}
							$opt++;
						}
					}else
					{
						$max_pos = 100;
						$max_budget =$budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
					}
					$best_pos 	 = str_replace("POS","",$max_pos);
					$best_budget = $max_budget;
				}elseif($this->mode==3) // package position
				{
					$best_pos = 100;
					$best_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
				}*/
				
				if(DEBUG_MODE)
				{
					echo '<br>best_pos:'.$best_pos;
					echo '<br>best_budget:'.$best_budget;
				}
				$budget_array[$catid]['pin_data'][$pincode]['best_flg'] = $best_pos;
				$budget_array[$catid]['pin_data'][$pincode]['best_bgt'] = $best_budget;
				$budget_array[$catid]['pin_data'][$pincode]['renew_flg'] = $renew_pos; // 1- same positon 2-other positon
				//$cat_budget_array[$catid] += $best_budget; // this needs to be done after becasue of dummy values
				
				//$total_budget 			  += $best_budget;
				
				//$alloc_summary_array[$best_pos] = $alloc_summary_array[$best_pos] + 1; // this also need to be done after because of summy values
				
			} // while loop for catid pincode
		}
		$total_budget = 0;
		$cat_budget_array = array();
		foreach($budget_array as $catid => $catid_values)
		{
			if(DEBUG_MODE)
			{
				echo '<br>Catid : '.$catid;
				//echo '<br>Catid Values : ';
				//print_r($catid_values);
			}
			foreach($catid_values['pin_data'] as $pincode => $pincode_values)
			{
				if(DEBUG_MODE)
				{
					echo '<br>Sunz';
					echo '<br>Pincode : '.$pincode;
					//echo '<br>Pincode Values : ';
					//print_r($pincode_values);
					echo '<br>best_bgt : '.$pincode_values['best_bgt'];
					echo '<br>best_pos : '.$pincode_values['best_flg'];
					print_r($live_inventory[$catid][$pincode]);
				}
				if($this->mode == 4 && !isset($live_inventory[$catid][$pincode]['p']))
				{
					if(DEBUG_MODE)
					{
						echo '<br>Unsetting: Catid:'.$catid.' &pin:'.$pincode;
					}
					unset($budget_array[$catid]['pin_data'][$pincode]);
				}
				else
				{
					$best_bgt = $pincode_values['best_bgt'];
					$best_pos = $pincode_values['best_flg'];
					
					$cat_budget_array[$catid] += $best_bgt;
					$alloc_summary_array[$best_pos] += 1;
				}
			}
			
			$budget_array[$catid]['c_bgt'] = $cat_budget_array[$catid];
			if($cat_array[$catid]['b2b_flag']==1)
			{
				$final_bgt = max($budget_array[$catid]['c_bgt'],$budget_array[$catid]['bm_bgt'], $this->b2b_cat_minval);
				$budget_array[$catid]['bm_bgt'] = max($budget_array[$catid]['bm_bgt'], $this->b2b_cat_minval);
			}
			else
			{
				$final_bgt = max($budget_array[$catid]['c_bgt'],$budget_array[$catid]['bm_bgt']);
			}
			
			$budget_array[$catid]['f_bgt'] = $final_bgt; 
			$total_budget += $final_bgt;
		}
		if(DEBUG_MODE)
		{
			echo '<hr>';
			echo '<h1>Final Result array:/</h1>';
			print_r($alloc_summary_array);
			print_r($budget_array);
			echo '<hr>';
		}
		/*
		foreach($budget_array as $catid => $catid_values)
		{
			if(DEBUG_MODE)
			{
				echo '<br>Catid : '.$catid;
				echo '<br>Catid Values : ';
				print_r($catid_values);
			}
			$budget_array[$catid]['c_bgt'] = $cat_budget_array[$catid];
			if($cat_array[$catid]['b2b_flag']==1)
				$final_bgt = max($budget_array[$catid]['c_bgt'],$budget_array[$catid]['bm_bgt'], $this->b2b_cat_minval);
			else
				$final_bgt = max($budget_array[$catid]['c_bgt'],$budget_array[$catid]['bm_bgt']);
			//echo '<br>++2'.
			$budget_array[$catid]['f_bgt'] = $final_bgt; 
			$total_budget += $final_bgt;
		}
		*/
		foreach($alloc_summary_array as $best_pos => $pos_values)
		{
			$alloc_per_summary[$best_pos] = round($alloc_summary_array[$best_pos]/$alloc_total*100,2);
		}
		//print_r($cat_min_budget);
		//print_r($b2b_cat_pin_budget);
		//echo '<br>Allocation Array<br>';
		//print_r($alloc_summary_array);
		//print_r($budget_array);
		$cat_desc['VNM']  	= "Very Near Me";
		$cat_desc['NM']  	= "Near Me";
		$cat_desc['A']  	= "Area";
		$cat_desc['Z']  	= "Zonal";
		$cat_desc['SZ']  	= "Super Zonal";
		$cat_desc['L']  	= "All Area";
		
		$return_array['c_data']  = $budget_array;
		$return_array['pos'] 	 = $alloc_per_summary;
		$return_array['tb_bgt']  = $total_budget;
		if($alloc_per_summary[100]<100)
		{
			$campaignid=2;
		}
		else
		{
			$campaignid=1;
		}
					
		
		if($this->mode==3 || ($this->mode==4 && $alloc_per_summary[100]==100 && count($pin_array)==1))
			$final_citymin_budget = $package_min_budget;
		elseif(($all_flag/($az_flag+$all_flag))>= 0.65)
			$final_citymin_budget = $fp_min_budget;
		else
		{
			//$final_citymin_budget = max($city_min_budget,$pincity_min_budget*$this->tenure_f); [has been removed as discussed with AJAY]
			
			//$city_pincity_min_budget =  max($pinminbudget, 3000);
			//$final_citymin_budget = max($city_pincity_min_budget,$pincity_min_budget*$this->tenure_f);
			
			$final_citymin_budget = max($fp_min_budget,$pincity_min_budget*$this->tenure_f);
		}
		
		if(DEBUG_MODE)
		{
			echo '<hr>City Min Buget Calculation:';
			echo '<br>package_min_budget:'.$package_min_budget;
			echo '<br>pincity_min_budget:'.$pincity_min_budget;
			echo '<br>city_min_budget:'.$city_min_budget;
			echo '<br>final_citymin_budget:'.$final_citymin_budget;
			echo '<br>this->mode:'.$this->mode;
			echo '<br>count($pin_array):'.count($pin_array);
			echo '<br>pinminbudget:'.$pinminbudget;
			echo '<br>city_pincity_min_budget:'.$city_pincity_min_budget;
			echo '<br>az_flag:'.$az_flag;
			echo '<br>all_flag:'.$all_flag;
			echo '<br>all %:'.($all_flag/($az_flag+$all_flag));
			print_r($alloc_per_summary);
		}	
		
		//$return_array['reg_bgt']  = $this->regfeeclass_obj->getRegfee($this->contact_nos_list,$campaignid);
		
		
		//print_r($return_array);
		$return_array['tot_bgt']  		= $total_budget+$return_array['reg_bgt'];
		$return_array['city_bgt']  		= $final_citymin_budget;
		$return_array['renewal_cnt']    = $this->renewal_cnt;
		$return_array['pinmin_bgt']  	= $pinminbudget;
		$return_array['city_pincity_min_bdgt'] = $city_min_budget;
		$return_array['packagemin_bgt']	= $package_min_budget;
		$return_array['citymin_bgt']	= $city_min_budget;
		$return_array['cat_desc']  		= $cat_desc;
		$return_array['az_c'] 	 		= $az_flag;
		$return_array['all_c']  		= $all_flag;
		$return_array['bgt_type']  		= $this->budget_type;
		$return_array['upldrts_minbudget']  	= $upldrts_minbudget;
		$return_array['upldrts_top_minbudget']  = $upldrts_top_minbudget;
		$return_array['cstm_minbudget_package']  = $upldrts_cstm_minbudget_package;
		$return_array['weeklypackvalue']  = $weekly_package_budget;
		$return_array['monthlypackvalue']  = $monthly_package_budget;
		$return_array['maxrnwmini'] = $maxrnwmini;
		$return_array['maxrnwbasic'] = $maxrnwbasic;
		$return_array['maxrnwpremium'] = $maxrnwpremium; 
		$return_array['package_mini'] = $package_mini; 
		$return_array['package_mini_ecs'] = $package_mini_ecs; 
		$return_array['package_mini_minimum'] = $package_mini_minimum; 
		$return_array['package_premium'] = $package_premium; 
		$return_array['package_premium_upfront'] = $package_premium_upfront; 
		$return_array['price_mini_upfront_discount'] = $price_mini_upfront_discount; 
		$return_array['price_mini_upfront_minimum_discount'] = $price_mini_upfront_minimum_discount; 
		$return_array['price_mini_upfront_two_years'] = $price_mini_upfront_two_years; 

		

		$return_array['minbudget_national'] = $minbudget_national; 
		$return_array['active_campaign'] =$this->active_campaign;
		
		
		
		if($this->remfxdpospackbdgt!=0) 
		{
			$return_array['remfxdpospackbdgt'] = $this->remfxdpospackbdgt;
		}

		if($this->expiredePackval!=0)
		{
			$return_array['expiredePackval'] = $this->expiredePackval;
		}
		if($this->expiredePackval_2yrs!=0)
		{
			$return_array['expiredePackval_2yrs'] = $this->expiredePackval_2yrs;
		}
		
		if($this->rnwcstmminpckbgt1yr!=0)
		{
			$return_array['rnwcstmminpckbgt1yr'] = $this->rnwcstmminpckbgt1yr;
		}
		
		
		$return_array['renewal_percent_slab'] =  $renewal_precent;
		$return_array['approval_date'] =  $approval_date;
		$return_array['num_days'] =  $num_days;
		$return_array['yr_idx'] =  $yr_idx;
		
		$result['result'] = $return_array;
		$result['error']['code'] = 0;
		$result['error']['msg']  = "";
		//if($renew_msg==2 || ($alloc_total<$this->renewal_cnt))
		//{
		//	$result['error']['code'] = 2;
		//	$result['error']['msg']  = "Renewal: Category Pincode Mismatch";
		//}
		//else
		//{
		//	$result['error']['code'] = 0;
		//	$result['error']['msg']  = "";
		//}
		
		return($result);
	}
	
	function get_category_details($catids)
	{
		$sql="select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag, search_type, budget_type 
		from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";
		//$res_area 	= parent::execQuery($sql, $this->dbConDjds);
		//$num_rows		= mysql_num_rows($res_area);
		$cat_params = array();
		$cat_params['page']= 'budgetDetailshiddenClass';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,category_type,search_type,budget_type';

		$where_arr  	=	array();
		if($catids!=''){
			$where_arr['catid']			= $catids;
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if(DEBUG_MODE)
		{
			echo '<br><b>Category Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
		{		
			
			foreach($cat_res_arr['results'] as $key=>$row)
			{
				if(DEBUG_MODE)
					print_r($row);
				if($row['search_type']==1)
					$cat_search_type = "A";
				elseif($row['search_type']==2)
					$cat_search_type = "Z";
				elseif($row['search_type']==3)
					$cat_search_type = "SZ";
				elseif($row['search_type']==4)
					$cat_search_type = "NM";
				elseif($row['search_type']==5)
					$cat_search_type = "VNM";
				else
					$cat_search_type = "L";
					
				$catid 		 = $row['catid'];
				$budget_type = $row['budget_type'];
				
				if($budget_type==2)
					$this->budget_type = 2;
					
				$category_type = $row['category_type'];
				$business_flag = $row['business_flag'];

				$b2b_flag = 0;
				if((int)$business_flag ==1){
					$b2b_flag = 1;
				}
				$block_for_contract =0;
				if(((int)$category_type & 64)==64 ){
					$block_for_contract =1;
				}
				$exclusive_flag =0;
				if(((int)$category_type & 16)==16 ){
					$exclusive_flag =1;
				}

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
	
	
	function getpackagerenewcustomminbudget()	
	{
		
		$sql_compfin="select a.parentid,a.version,group_concat(a.campaignId order by campaignid asc) as campaigns,group_concat(a.budget order by campaignid asc) as bgt,b.approval_date 
						from payment_apportioning a join payment_version_approval b on a.parentid=b.parentid and a.version=b.version 
						where a.disruption_flag=0 and a.budget>0 and a.budget!=a.balance and a.campaignid in(1,2) and a.entry_date>date_sub(current_date,interval 410 day ) 
						and a.parentid='".$this->parentid."' group by a.parentid,a.version order by b.approval_date desc limit 1";
		
		
		$res_compfin 	= parent::execQuery($sql_compfin, $this->finance);
		
		if(mysql_num_rows($res_compfin))
		{
			$arr_compfin = mysql_fetch_assoc($res_compfin);
			
			if( ((string)$arr_compfin['campaigns']=='1') && ($this->ExpiredDaysvalue<45 || $this->active_campaign==1 ) )
			{			
				$this->rnwcstmminpckbgt1yr=$arr_compfin['bgt']*1.5;
				
				if($this->rnwcstmminpckbgt1yr<6000)
				$this->rnwcstmminpckbgt1yr=6000;
			}
			
			
			
			
		}
		
		
		if(DEBUG_MODE)
		{
			echo '<hr>intval '.intval($arr_compfin['campaigns']);
			echo '<hr>$arr_compfin  '.$arr_compfin['campaigns'];
			echo '<br>sql_compfin '; print($sql_compfin);
			echo '<br>arr_compfin ';print_r($arr_compfin);
			echo '<br>rnwcstmminpckbgt1yr '.$this->rnwcstmminpckbgt1yr;
			echo '<br>ExpiredDaysvalue '.$this->ExpiredDaysvalue;
			echo '<br>$this->active_campaign '.$this->active_campaign;
			
		}
		
		

	}
	
	
	function oldExpRemFxdPosCheck()
	{

		$balanceSum=0;
		$packExpiredVal=0;
		$packExpiredOnVal=null;
		$packExpiredDatediff=0;
		$this->active_campaign=0;
		//$sql_compfin="select * from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2) and balance>0";
		$sql_compfin="select campaignid,balance,expired,expired_on from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2)";
		$res_compfin 	= parent::execQuery($sql_compfin, $this->finance);

		if(DEBUG_MODE)
		{
			echo '<hr>';
			echo '<br>sql_compfin '; print($sql_compfin);
		}
					
		if(mysql_num_rows($res_compfin)) // if it is already active phone seach campagn then do not allow 
		{			
			while($arr_compfin = mysql_fetch_assoc($res_compfin))
			{
				if($arr_compfin['balance']>0)
				{
					$balanceSum+=$arr_compfin['balance'];
					$this->active_campaign=1;
				}

				if($arr_compfin['expired']==1 && ($arr_compfin['campaignid']==1 || $arr_compfin['campaignid']==2))
				{
					$packExpiredVal=1;
					$packExpiredOnVal=$arr_compfin['expired_on'];
					$todaydate  = date('Y-m-d H:i:s');
					
					if($packExpiredDatediff==0)
					{
						$packExpiredDatediff = round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400);
					}else
					{
						$packExpiredDatediff= min($packExpiredDatediff,round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400));
					}
					
					$this->ExpiredDaysvalue = $packExpiredDatediff;

					if(DEBUG_MODE)
					{
						echo '<hr>';
						echo '<br>packExpiredVal:'; print($packExpiredVal);
						echo '<br>campaignid:'. $arr_compfin['campaignid'];
						echo '<br>packExpiredOnVal:'; print($packExpiredOnVal);
						echo '<br>packExpiredDatediff: '.$packExpiredDatediff;	
						echo '<br>exppackday: '.$this->exppackday;	
					}
				}
			}


			if($balanceSum==0) // if there is no balance then checking for data in expired tables
			{
				$sql_bde="select count(1) as countval from tbl_bidding_details_expired where parentid='".$this->parentid."' and campaignid=2 and position_flag in (4,5,6,7)";
				$res_bde 	= parent::execQuery($sql_bde, $this->finance);
				$num_bde		= mysql_num_rows($res_bde);
				
				if(DEBUG_MODE)
				{
					echo '<br><b>BD Query:</b>'.$sql_bde;
					echo '<br><b>Result Set:</b>'.$res_bde;
					echo '<br><b>Num Rows:</b>'.$num_bde;
					echo '<br><b>Error:</b>'.$this->mysql_error;
					print_r($this->finance);
				}
				if($res_bde && $num_bde > 0)
				{		
					
					while($row_bde=mysql_fetch_assoc($res_bde))
					{
						if($row_bde['countval']>0)
						{
							$this->oldExpRemFxdPos=1;
						}
						
						if(DEBUG_MODE)
						{
							echo '<hr>';
							echo '<br>countval:-'; print_r($row_bde);	
							echo '<br>oldExpRemFxdPos:-'.$this->oldExpRemFxdPos;	
						}
						
						
					}
				}
				
			}

			
			if(DEBUG_MODE)
				{
					echo '<hr>';
					echo '<br> exppackday '.$this->exppackday;
				}
			
			//if($balanceSum==0 && $packExpiredVal==1 && $packExpiredDatediff>=90) // expired package value setting 
			if($balanceSum==0 && $packExpiredVal==1 && $packExpiredDatediff > $this->exppackday) // expired package value setting 
			{
				$this->expiredePackflg=1;

				if(DEBUG_MODE)
				{
					echo '<hr>';
					echo '<br> exppackday '.$this->exppackday;
					echo '<br>expiredePackflg: '.$this->expiredePackflg;
					
				}
			}
		}	
		// we will read from tbl_bidding_details_expired instead of tbl_fixedposition_pincodewise_bid because it may be released 
		
		//$sql_bde="select count(1)  as cnt from tbl_bidding_details_expired where parentid ='".$this->parentid."'";
		
	}
	
	function getBookedBidderInv()
	{
		$sql = "SELECT  * FROM tbl_bidcatdetails_leadandsupreme_shadow WHERE bid_catid IN (".$this->catid_list.") AND pincode IN (".$this->pincode_list.") AND campaignid=17 AND partial_ddg_ratio>0 AND DATEDIFF(CURRENT_DATE,uptdate)<15 ORDER BY uptdate DESC";
		$res = parent::execQuery($sql, $this->finance);
		if($res && mysql_num_rows($res)>0)
		{
			
			while($row = mysql_fetch_assoc($res))
			{
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['booked_inv'] = $row['partial_ddg_ratio'];
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['bidder']     = $row['parentid'];
			}
		}
		
		$sql = "SELECT  * FROM tbl_bidcatdetails_lead WHERE bid_catid IN (".$this->catid_list.") AND pincode IN (".$this->pincode_list.") AND campaignid=17 AND partial_ddg_ratio>0";
		$res = parent::execQuery($sql, $this->finance);
		if($res && mysql_num_rows($res)>0)
		{
			
			while($row = mysql_fetch_assoc($res))
			{
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['booked_inv'] = $row['partial_ddg_ratio'];
				$booked_position_arr [$row['bid_catid']][$row['pincode']][$row['position_flag']]['bidder']     = $row['parentid'];
			}
		}
		return $booked_position_arr;
	}
	
	
	function HiddenInventoryChecking()
	{
		
		  $live_inv = $this->getBookedBidderInv();
		  $sql_djs = "SELECT * FROM tbl_bidding_details_hidden_intermediate WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		  $res_djs 	= parent::execQuery($sql_djs, $this->dbConDjds);
		  if($res_djs &&  mysql_num_rows($res_djs))
		  {

				$unavailable_inv = array();
				while($row_djs = mysql_fetch_assoc($res_djs))
				{
				   $catid			    =  $row_djs['catid'];
				   $national_catid      =  $row_djs['national_catid'];
				   $pincode_list_arr    =  json_decode($row_djs['pincode_list'],true);
				   
				   	/*$sql_bcd_lead_shadow = '';
					$sql_bcd_lead_shadow = "INSERT INTO tbl_bidcatdetails_leadandsupreme_shadow (bid_catid,bid_zone,pincode,bid_lead,bid_bidamt,grace_amt,uptdate,createdby,physicallocation,parentid,campaignid, position_flag,partial_ddg_ratio,data_city,nationalcatid) VALUES";*/
				
				   if(count($pincode_list_arr))
				   {
					   foreach ($pincode_list_arr as $pincode => $pincode_data)
					   {
						  if($live_inv[$catid][$pincode][$pincode_data['pos']]['bidder'] != $this->parentid && $live_inv[$catid][$pincode][$pincode_data['pos']]['booked_inv'])
						  {
							  $unavailable_inv[$catid][$pincode][$pincode_data['pos']] = 1;
						  }
					   }
				   }
				}
				
		  }
			
			if(count($unavailable_inv)>0)
			{
				$return_array_message['fail'] = $unavailable_inv;
			}else
			{
				$return_array_message['success'] = 1;
			}
			
			return $return_array_message;
	}
	
	
}

?>
