<?php

class budgetDetailsClass extends DB
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


	var $catsearch = null;
	var $data_city = null;
	var $opt = 'ALL'; 	// area selection option 
	var $b2c_catpin_minval = 1;
	var $b2b_cat_minval = 50;
	var $enable_partial = 0;
	var $discount_f = 0.66;
	var $budget_type = 0;
	var $oldExpRemFxdPos = 0;
	var $expiredePackflg, $exppackday = 90;
	var $rnwcstmminpckbgt1yr = 0;
	var $ExpiredDaysvalue = 0;
	var $current_live_budget_arr= array();
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 


	var $optvalset = array('ALL', 'ZONE', 'NAME', 'PIN', 'DIST');
	var $regfeeclass_obj = null;

	function __construct($params)
	{
		$this->params = $params;
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		
		/* Code for companymasterclass logic starts */
		if ($this->params['is_remote'] == 'REMOTE') {
			$this->is_split = false;	 // when split table goes live then make it TRUE		
		} else {
			$this->is_split = false;
		}

		if (trim($this->params['parentid']) != "") {
			$this->parentid = strtoupper($this->params['parentid']); //initialize paretnid
		}

		if (trim($this->params['version']) != "") {
			$this->version = $this->params['version']; //initialize version
		}

		if (trim($this->params['oldversion']) != "") {
			$this->old_version = $this->params['oldversion']; //initialize Old version
		}

		if (trim($this->params['data_city']) != "" && $this->params['data_city'] != null) {
			$this->data_city = $this->params['data_city']; //initialize datacity
		}

		if (trim($this->params['mode']) != "") {
			$this->mode = $this->params['mode']; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive 6-renewal2
		}

		if (trim($this->params['onlyExclusive']) != "") {
			$this->onlyExclusive = trim($this->params['onlyExclusive']); // It can have value if mode-5 i.e exclusive is selected. If set person will get only exclusive
		}


		if (trim($this->params['option']) != "") {
			$this->option = $this->params['option']; // default 1, max 7
		}

		if ($this->parentid == 'PX422.X422.131111185238.E2I3')
			$this->remove_capping = 1;
		else
			$this->remove_capping = 0;

		$this->remove_discount = 0;
		//## as per discussion with AJAY capping to be removed for a week.	
		//$this->remove_capping = 1;	

		if (trim($this->params['tenure']) != "" && $this->params['tenure'] != null) {
			$this->tenure_f = ($this->params['tenure'] / 12);
			if ($this->tenure_f > 1 && $this->tenure_f <= 2)
				$this->tenure_f = 2.5;
			elseif ($this->tenure_f > 2 && $this->tenure_f <= 3)
				$this->tenure_f = 3;
			elseif ($this->tenure_f > 3 && $this->tenure_f <= 5)
				$this->tenure_f = 4;
			elseif ($this->tenure_f > 5) {
				$this->tenure_f = 5;
			}
		} else {
			$this->tenure_f = 1;

		}
		
		//if($this->tenure_f>1)
		//{
			//$this->remove_capping = 1; //As disccused with Ajay - last paid to be used
		//	$this->remove_discount = 1;
		//}
		$this->custompackage_f = 0;
		if (intval(trim($this->params['custompackage'])) == 1) {
			$this->custompackage_f = 1;
			$this->packagebgt_yrly = intval(trim($this->params['packagebgt_yrly']));
		}

		$this->pinbgt_f = 0;
		$this->pinview_f = 0;
		if (intval(trim($this->params['pinbgt'])) == 1) {
			$this->pinbgt_f = 1;
			$this->pinview_f = intval(trim($this->params['pinview']));
		}
		
		//if(trim($this->params['pincode_list']) != "" && $this->params['pincode_list'] != null)
		//{
			//$this->pincode_list  = $this->params['pincode_list']; //initialize pincode_list
		//}
		//if(trim($this->params['catid_list']) != "" && $this->params['catid_list'] != null)
		//{
			//$this->catid_list  = $this->params['catid_list']; //initialize catid_list
		//}

		$this->summary_para();
		$this->regfeeclass_obj = new regfeeclass($params);
		//echo 'aaaaa<pre>';print_r($params);
		if (trim($this->params['module']) != "" && $this->params['module'] != null) {
			$this->national_list_obj = new nationallistingclass($params);

		}
		
		
		/*
		if($this->params['mode']==3) // if it is a package then we have to check whethere its old 
		{
			$this->oldExpRemFxdPosCheck();
		}
		 */
		if (DEBUG_MODE) {
			echo '<H1>this object</H1>';
			print_r($this);
		}
	}
	function summary_para()
	{
		$sql = "select * from tbl_bidding_details_summary where parentid ='" . $this->parentid . "' and version = '" . $this->version . "'";
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>BD Summary Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			print_r($this->dbConbudget);
		}
		if ($res && $num > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				if (DEBUG_MODE) {
					echo '<hr>';
					print_r($row);
				}
				$this->pincode_list = $row['pincode_list'];
				$this->catid_list = $row['category_list'];
				$this->contact_nos_list = $row['contact_details'];
				$pincode_array = json_decode($row['pincodejson'], true);
				$pincode_budget_array = json_decode($row['pincodebudgetjson'], true);
				if (DEBUG_MODE) {
					print_r($pincode_array);
				}
				$this->allarea_pin_list = $pincode_array['a_a_p'];
				$this->allarea_pin_array = explode(",", $pincode_array['a_a_p']);
				$this->non_allarea_pin_list = $pincode_array['n_a_a_p'];
				$this->non_allarea_pin_array = explode(",", $pincode_array['n_a_a_p']);

				$this->pinbgt_array = $pincode_budget_array;
				if (DEBUG_MODE) {
					echo '<br>pinbgt_array:';
					print_r($this->pinbgt_array);
				}
			}
		} else {
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Entry in bidding Summary Not Found";
			$resultstr = json_encode($result);
			print($resultstr);
			die;
		}

		$this->renewal_cnt = 0;
		$sql = "select count(1)  as cnt from tbl_bidding_details where parentid ='" . $this->parentid . "'";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>BD Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			print_r($this->finance);
		}
		if ($res && $num > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				if (DEBUG_MODE) {
					echo '<hr>';
					print_r($row);
				}
				$this->renewal_cnt = $row['cnt'];
			}
		}

		if ($this->renewal_cnt == 0) {
			$sql_bde = "select count(1)  as cnt from tbl_bidding_details_expired where parentid ='" . $this->parentid . "'";
			$res_bde = parent::execQuery($sql_bde, $this->finance);
			$num_bde = mysql_num_rows($res_bde);

			if (DEBUG_MODE) {
				echo '<br><b>BD Query:</b>' . $sql_bde;
				echo '<br><b>Result Set:</b>' . $res_bde;
				echo '<br><b>Num Rows:</b>' . $num_bde;
				echo '<br><b>Error:</b>' . $this->mysql_error;
				print_r($this->finance);
			}
			if ($res_bde && $num_bde > 0) {

				while ($row_bde = mysql_fetch_assoc($res_bde)) {
					if (DEBUG_MODE) {
						echo '<hr>';
						print_r($row_bde);
					}
					$this->renewal_cnt = $row_bde['cnt'];
				}
			}

		}
	}
	
	function current_live_budget()
	{
		
		$sql = "select * from tbl_bidding_details where parentid ='" . $this->parentid . "'  ORDER BY catid, pincode";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b> current_live_budget </b>';
			echo '<br><b>DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num > 0) {
			
			while ($row = mysql_fetch_assoc($res)) {
				#echo '<hr>';
				#print_r($row);
				//die;
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$positon_flag = $row['position_flag']; 
				
				#$this->current_live_budget_arr[$catid][$pincode]['a_clbgt'] = $row['bidperday'] * $row['duration'];
				#$this->current_live_budget_arr[$catid][$pincode]['a_clbpd'] = $row['bidperday'];
				
				$this->current_live_budget_arr[$catid][$pincode]['a_clbgt'] = $row['actual_budget'];
				$this->current_live_budget_arr[$catid][$pincode]['a_clbpd'] = $row['actual_budget']/$row['duration'];
				
				
			}
		}
		
		if (DEBUG_MODE) {
			
			echo '<br><b>current_live_budget_arr</b>' ; print_r($this->current_live_budget_arr);
		}
	
	}
	
	function accrued_bid_calc($position_factor)
	{
		############## accrued bid per day #############################
		$sql = "select * from tbl_bidding_details where parentid ='" . $this->parentid . "' and position_flag!=100 ORDER BY catid, pincode";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				//echo '<hr>';
				//print_r($row);
				//die;
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$positon_flag = $row['position_flag']; 
				//print_r($row);
				$accrued_bpd[$catid][$pincode][$positon_flag]['bpd'] = $row['bidperday'];
				if ($row['inventory'] > 0) {
					$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd'] = $row['bidperday'] * (1 / $row['inventory']);
					$accrued_p_bpd[$catid][$pincode] = $row['bidperday'] * (1 / $row['inventory']) * $position_factor[$positon_flag];
				} else {
					$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd'] = 0;
					$accrued_p_bpd[$catid][$pincode] = 0;
				}
				
				//echo '<br>factor ->'.($position_factor[$positon_flag]);
				//echo '<br>bpd ->'.$row['bidperday'];
				//echo '<br>Accrued Platinum bidval ->'.$accrued_p_bpd[$catid][$pincode];
			}
		} else {
			$sql = "select * from tbl_bidding_details_expired where parentid ='" . $this->parentid . "' and position_flag!=100 and ifnull(datediff(now(),expiredon),365) <=730 ORDER BY catid, pincode";
			$res = parent::execQuery($sql, $this->finance);
			$num = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<br><b>DB Expired Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			if ($res && $num > 0) {

				while ($row = mysql_fetch_assoc($res)) {
					//echo '<hr>';
					//print_r($row);
					//die;
					$catid = $row['catid'];
					$pincode = $row['pincode'];
					$positon_flag = $row['position_flag']; 
					//print_r($row);
					$accrued_bpd[$catid][$pincode][$positon_flag]['bpd'] = $row['bidperday'];

					if ($row['inventory'] > 0) {
						$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd'] = $row['bidperday'] * (1 / $row['inventory']);
						$accrued_p_bpd[$catid][$pincode] = $row['bidperday'] * (1 / $row['inventory']) * $position_factor[$positon_flag];
					} else {
						$accrued_bpd[$catid][$pincode][$positon_flag]['a_bpd'] = 0;
						$accrued_p_bpd[$catid][$pincode] = 0;
					}
					//echo '<br>factor ->'.($position_factor[$positon_flag]);
					//echo '<br>bpd ->'.$row['bidperday'];
					//echo '<br>Accrued Platinum bidval ->'.$accrued_p_bpd[$catid][$pincode];
				}
			}
		}
		if (DEBUG_MODE) {
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

		$data_city = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		$this->finance = $db[$data_city]['fin']['master'];
		$this->dbConbudget = $db[$data_city]['db_budgeting']['master'];
		if (DEBUG_MODE) {
			echo '<br>dbConDjds:';
			print_r($this->dbConDjds);
			echo '<br>dbConbudget:';
			print_r($this->dbConbudget);

		}
	}

	function get_live_inventory()
	{

		$bidding_version = (($this->old_version) ? $this->old_version : $this->version);

		if ($this->old_version)
			$live_bidding_version_cond = " AND version = '" . $bidding_version . "'";

		$sql = "SELECT * FROM tbl_bidding_details WHERE parentid ='" . $this->parentid . "' " . $live_bidding_version_cond . "  ORDER BY catid, pincode";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				//echo '<hr>';
				//print_r($row);
				$catid = $row['catid'];
				$pincode = $row['pincode']; 
				//print_r($pin_array);
				$inv_seeked[$catid][$pincode]['p'] = $row['position_flag'];
				//$inv_seeked[$catid][$pincode]['c']    = $row['callcount'];
				//$inv_seeked[$catid][$pincode]['b']    = $row['bidvalue'];
				//$inv_seeked[$catid][$pincode]['bgt']  = $row['sys_budget'];
				$inv_seeked[$catid][$pincode]['i'] = $row['inventory'];
				//$inv_seeked[$catid][$pincode]['s'] 	  = 1; // 1-pass 0-fail 
				//$inv_seeked[$catid][$pincode]['abgt'] = $row['actual_budget'];
				$pin_arr[] = $pincode;
				$cat_arr[] = $catid;
			}

			if ($this->mode == 4) {
				$this->pincode_list = implode(",", array_unique($pin_arr));
				$this->catid_list = implode(",", array_unique($cat_arr));
			}

		} else {
			$sql_bde = "SELECT * FROM tbl_bidding_details_expired WHERE parentid ='" . $this->parentid . "' " . $live_bidding_version_cond . " ORDER BY catid, pincode";
			$res_bde = parent::execQuery($sql_bde, $this->finance);
			$num_bde = mysql_num_rows($res_bde);

			if (DEBUG_MODE) {
				echo '<br><b>DB Query:</b>' . $sql_bde;
				echo '<br><b>Result Set:</b>' . $res_bde;
				echo '<br><b>Num Rows:</b>' . $num_bde;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			if ($res_bde && $num_bde > 0) {

				while ($row_bde = mysql_fetch_assoc($res_bde)) {
					//echo '<hr>';
					//print_r($row);
					$catid = $row_bde['catid'];
					$pincode = $row_bde['pincode']; 
					//print_r($pin_array);
					$inv_seeked[$catid][$pincode]['p'] = $row_bde['position_flag'];
					//$inv_seeked[$catid][$pincode]['c']    = $row['callcount'];
					//$inv_seeked[$catid][$pincode]['b']    = $row['bidvalue'];
					//$inv_seeked[$catid][$pincode]['bgt']  = $row['sys_budget'];
					$inv_seeked[$catid][$pincode]['i'] = $row_bde['inventory'];
					//$inv_seeked[$catid][$pincode]['s'] 	  = 1; // 1-pass 0-fail 
					//$inv_seeked[$catid][$pincode]['abgt'] = $row['actual_budget'];
					$pin_arr[] = $pincode;
					$cat_arr[] = $catid;
				}
				if ($this->mode == 4) {
					$this->pincode_list = implode(",", array_unique($pin_arr));
					$this->catid_list = implode(",", array_unique($cat_arr));
				}


			} else {
				$sql_bds = "SELECT * FROM tbl_bidding_details_shadow WHERE parentid ='" . $this->parentid . "' AND version = '" . $bidding_version . "' ORDER BY catid, pincode";
				$res_bds = parent::execQuery($sql_bds, $this->dbConbudget);
				$num_bds = mysql_num_rows($res_bds);

				if (DEBUG_MODE) {
					echo '<br><b>DB Shadow Query:</b>' . $sql_bds;
					echo '<br><b>Result Set:</b>' . $res_bds;
					echo '<br><b>Num Rows:</b>' . $num_bds;
					echo '<br><b>Error:</b>' . $this->mysql_error;
				}

				if ($res_bds && $num_bds > 0) {

					while ($row_bds = mysql_fetch_assoc($res_bds)) {
						//echo '<hr>';
						//print_r($row);
						$catid = $row_bds['catid'];
						$pincode = $row_bds['pincode'];
						$inv_seeked[$catid][$pincode]['p'] = $row_bds['position_flag'];
						$inv_seeked[$catid][$pincode]['i'] = $row_bds['inventory'];
						$pin_arr[] = $pincode;
						$cat_arr[] = $catid;
					}
					if ($this->mode == 4) {
						$this->pincode_list = implode(",", array_unique($pin_arr));
						$this->catid_list = implode(",", array_unique($cat_arr));
					}


				} else {

					$sql_bda = "SELECT * FROM tbl_bidding_details_shadow_archive WHERE parentid ='" . $this->parentid . "' AND version = '" . $bidding_version . "' GROUP BY catid, pincode, version ORDER BY catid, pincode";
					$res_bda = parent::execQuery($sql_bda, $this->dbConbudget);
					$num_bda = mysql_num_rows($res_bda);

					if (DEBUG_MODE) {
						echo '<br><b>DB Shadow Archive Query:</b>' . $sql_bda;
						echo '<br><b>Result Set:</b>' . $res_bda;
						echo '<br><b>Num Rows:</b>' . $num_bda;
						echo '<br><b>Error:</b>' . $this->mysql_error;
					}

					if ($res_bda && $num_bda > 0) {

						while ($row_bda = mysql_fetch_assoc($res_bda)) {
							$catid = $row_bda['catid'];
							$pincode = $row_bda['pincode'];

							$inv_seeked[$catid][$pincode]['p'] = $row_bda['position_flag'];
							$inv_seeked[$catid][$pincode]['i'] = $row_bda['inventory'];
							$pin_arr[] = $pincode;
							$cat_arr[] = $catid;
						}
						if ($this->mode == 4) {
							$this->pincode_list = implode(",", array_unique($pin_arr));
							$this->catid_list = implode(",", array_unique($cat_arr));
						}


					} else {
						
						$sql_bdh = "SELECT * FROM tbl_bidding_details_shadow_archive_historical WHERE parentid ='" . $this->parentid . "' AND version = '" . $bidding_version . "' GROUP BY catid, pincode, version ORDER BY catid, pincode";
						$res_bdh = parent::execQuery($sql_bdh, $this->dbConbudget);
						$num_bdh = mysql_num_rows($res_bdh);

						if (DEBUG_MODE) {
							echo '<br><b>DB Shadow Historical Query:</b>' . $sql_bdh;
							echo '<br><b>Result Set:</b>' . $res_bda;
							echo '<br><b>Num Rows:</b>' . $num_bdh;
							echo '<br><b>Error:</b>' . $this->mysql_error;
						}

						if ($res_bdh && $num_bdh > 0) {

							while ($row_bdh = mysql_fetch_assoc($res_bdh)) {
								$catid = $row_bdh['catid'];
								$pincode = $row_bdh['pincode'];

								$inv_seeked[$catid][$pincode]['p'] = $row_bdh['position_flag'];
								$inv_seeked[$catid][$pincode]['i'] = $row_bdh['inventory'];
								$pin_arr[] = $pincode;
								$cat_arr[] = $catid;
							}
							if ($this->mode == 4) {
								$this->pincode_list = implode(",", array_unique($pin_arr));
								$this->catid_list = implode(",", array_unique($cat_arr));
							}
						}
					} // else historical

				} // else archive
			} // else shadow

		} // else expired

		return ($inv_seeked);
	}

	function getBudget($newCatidList = "")
	{
		if ($this->mode == 4 || $this->mode == 6) {
			###### fetching Live inventory ###########
			$live_inventory = $this->get_live_inventory();
			if (DEBUG_MODE) {
				echo '<br>Live Inventory';
				print_r($live_inventory);
				echo '<br>New catid list';
				print_r($newCatidList);
			}

		}

		if (empty($this->catid_list) || empty($this->pincode_list)) {
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "category list Or pincode list Empty";
			return ($result);
		}

		if ($newCatidList != "") {
			$this->catid_list = $this->catid_list . "," . $newCatidList;
		}

		$cat_array = $this->get_category_details($this->catid_list);
		$pin_array = $this->get_pincode_details($this->pincode_list);

		if (DEBUG_MODE) {
			print_r($cat_array);
			print_r($pin_array);
		}

		if (count($cat_array) == 0) {
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "InValid category list";
			return ($result);
		}

		if (count($pin_array) == 0) {
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "InValid pincode list";
			return ($result);
		}	
		// tbl_fixedposition_factor 215 db_finanace
		$sql = "select * from tbl_fixedposition_factor /*where active_flag=1*/";
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num_rows = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>FP factor Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				
				//if(DEBUG_MODE)
				//{
				//	echo '<hr>';
				//	print_r($row);
				//}
				$pos = $row['position_flag'];
				$factor = $row['positionfactor'];
				$x_factor = $row['exclusive_positionfactor'];
				$f_factor = $row['final_positionfactor'];

				if ($row['active_flag'] == 1) {
					$n_position_factor[$pos] = $factor;
					$x_position_factor[$pos] = $x_factor;
					$f_position_factor[$pos] = $f_factor;
					$alloc_summary_array[$pos] = 0;
				}

				if ($f_factor > 0)
					$r_position_factor[$pos] = (1 / $f_factor);
				else
					$r_position_factor[$pos] = 0;
			}
		}
		//print_r($f_position_factor);
		//print_r($r_position_factor);
		
		
		if($this->params['glcpb']==1)
		{
			$this->current_live_budget();
		}
		
		if ($this->remove_capping != 1)
			$this->accrued_bid_calc($r_position_factor);

		$cities_6X = array("KOLKATA", "CHANDIGARH", "DELHI", "MUMBAI", "HYDERABAD", "JAIPUR");
		
		$sql = "select * from tbl_business_uploadrates where city='" . $this->data_city . "' limit 1";
		$res = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>Biz Upload rates Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res)) {
				
				//if(DEBUG_MODE)
				//{
				//	echo '<hr>';
				//	print_r($row);
				//}

				$FPPinMinBdgtval = $this->getFPPincodeMinimumBudget($this->pincode_list);

				if ($this->budget_type == 2) {
					//$package_min_budget  =  $row['minbudget']*2;
					//$package_min_budget  =  $row['top_minbudget'];
					$package_min_budget = $row['top_minbudget_package'];

					if ($FPPinMinBdgtval['lowminpinbdgtelg'] == 1) {
						$fp_min_budget = $FPPinMinBdgtval['pin_top_minbudget_fp'];
					} else {
						$fp_min_budget = $row['top_minbudget_fp'];
					}


				} else {
					//$package_min_budget  =  $row['minbudget'];
					$package_min_budget = $row['minbudget_package'];


					if ($FPPinMinBdgtval['lowminpinbdgtelg'] == 1) {
						$fp_min_budget = $FPPinMinBdgtval['pin_minbudget_fp'];
					} else {
						$fp_min_budget = $row['minbudget_fp'];
					}
				}



				
				$dialer_mappedcity = trim(strtoupper($row['dialer_mappedcity']));
				if($this->tenure_f==5 && in_array($dialer_mappedcity,$cities_6X))
				{
					$this->tenure_f=6;
					if (DEBUG_MODE) {
						echo '<br>Overridding Tenure:';
						echo '<br>tenure_f:'.$this->tenure_f;
					}
				}
				
				$pinminbudget = $row['pinminbudget'];
				$callcnt_growth_rate = 1 + ($row['callcnt_per'] / 100);
				$city_min_budget = $row['cityminbudget'];
				$pincity_min_budget = $pinminbudget * count($pin_array);
				$all_pincode_count = $row['allpincnt'];
				$bidvalue_premium = 1 + ($row['bidvalue_per'] / 100);
				$disc = $combodiscount / 100;
				$upldrts_minbudget = $row['minbudget_package'];
				$upldrts_top_minbudget = $row['top_minbudget_package'];
				$upldrts_cstm_minbudget_package = $row['cstm_minbudget_package'];
				$weekly_package_budget = $row['weeklypack'];
				$monthly_package_budget = $row['monthlowvaluepack'];
				$maxrnwmini = ($row['maxrnwmini'] / 12);
				$maxrnwbasic = ($row['maxrnwbasic'] / 12);
				$maxrnwpremium = ($row['maxrnwpremium'] / 12);
				$package_mini = ($row['package_mini']);
				$package_mini_ecs = ($row['package_mini_ecs']);
				$package_mini_minimum = ($row['package_mini_minimum']);
				$package_premium = ($row['package_premium']);
				$package_premium_upfront = ($row['package_premium_upfront']);
				$minbudget_national = ($row['minbudget_national']);
				$combodiscount = $row['combodiscount'];
				$renewal_tier = $row['renewal_tier'];
				if ($combodiscount > 0) {
					$disc = $combodiscount / 100;
					$package_mini_discount = $package_mini - ($package_mini * $disc);
					$package_mini_two_years = $package_mini + ($package_mini * 0.5);
					$package_mini_minimum_discount = $package_mini_minimum - ($package_mini_minimum * $disc);

				}
				$price_mini_upfront_discount = $package_mini_discount;
				$price_mini_upfront_minimum_discount = $package_mini_minimum_discount;
				$price_mini_upfront_two_years = $package_mini_two_years;


				$minimumbudget_national = ($row['minimumbudget_national']);
				$maxbudget_national = ($row['maxbudget_national']);
				$statebudget_national = ($row['statebudget_national']);
				$minupfrontbudget_national = ($row['minupfrontbudget_national']);
				$maxupfrontbudget_national = ($row['maxupfrontbudget_national']);
				$stateupfrontbudget_national = ($row['stateupfrontbudget_national']);

				$this->exppackday = $row['exppackday'];


				if ($this->mode == 3) {
					$this->oldExpRemFxdPosCheck();
					$this->getpackagerenewcustomminbudget();

				}


				if ($this->oldExpRemFxdPos != 0 && $row['rmfxdpackbdgt'] > 0) {
					$this->remfxdpospackbdgt = $row['rmfxdpackbdgt'];
				}

				if ($this->expiredePackflg != 0 && $row['exppackval'] > 0) {
					$this->expiredePackval = $row['exppackval'];
					$this->expiredePackval_2yrs = $row['exppackval_2'];
				}

			}
		}


		if (DEBUG_MODE) {
			echo '<br>Pos factor';
			print_r($n_position_factor);
			echo '<br>X Pos factor';
			print_r($x_position_factor);
			echo '<br>Final Pos factor';
			print_r($f_position_factor);
			echo '<br>Alloc Summary';
			print_r($alloc_summary_array);
			echo '<br>callcnt_growth_rate:' . $callcnt_growth_rate;
			echo '<br>bidvalue_premium :' . $bidvalue_premium;
			echo '<br>city_min_budget:' . $city_min_budget;
			echo '<br>count pin array:' . count($pin_array);
			echo '<br> assign $this->exppackday:' . $this->exppackday;
			echo '<br> this->non_allarea_pin_array:';
			print_r($this->non_allarea_pin_array);
			echo '<br> this->allarea_pin_array:';
			print_r($this->allarea_pin_array);
		}	
		//$city_min_budget = 5000; 
		
		
		############ setting default values Starts ################
		$callcount_default = 1 / $all_pincode_count;
		$searchcount_default = 1 / $all_pincode_count;
		$az_flag = 0;
		$all_flag = 0;
		foreach ($cat_array as $catid => $cat_val_array) {
			/*if (DEBUG_MODE) {
				echo '<hr>Catid:' . $catid;
				echo '<br>cst:' . $cat_array[$catid]['cst'];
				echo '<br>cat name:' . $cat_array[$catid]['cnm'];
			}*/
			
			
			//########## old budgeting values ############
			//if($cat_array[$catid]['x_flag'] ==1)
				//$position_factor = $x_position_factor;
			//else
				//$position_factor = $n_position_factor;
			
			########## new budgeting values ############
			$position_factor = $f_position_factor;

			if ($cat_array[$catid]['b2b_flag'] == 1)
				$bidvalue = 481;
			else
				$bidvalue = 96;

			if ($cat_array[$catid]['cst'] == "NM" || $cat_array[$catid]['cst'] == "VNM" || $cat_array[$catid]['cst'] == "A" || $cat_array[$catid]['cst'] == "Z") {
				$az_flag++;
			} else {
				$all_flag++;
			}

			$budget_array[$catid]['cid'] = $catid;
			$budget_array[$catid]['ncid'] = $cat_array[$catid]['nid'];
			$budget_array[$catid]['cnm'] = $cat_array[$catid]['cnm'];
			$budget_array[$catid]['cst'] = $cat_array[$catid]['cst'];
			$budget_array[$catid]['bval'] = $bidvalue;
			$budget_array[$catid]['bflg'] = $cat_array[$catid]['b2b_flag'];
			$budget_array[$catid]['c_bgt'] = 0;
			if ($cat_array[$catid]['b2b_flag'] == 1) {
				$budget_array[$catid]['bm_bgt'] = $this->b2b_cat_minval;
				$min_budget = max(0.10, round($this->b2b_cat_minval / count($pin_array), 2));
				$fin_budget = $this->b2b_cat_minval;
			} else {
				$budget_array[$catid]['bm_bgt'] = 0;
				$min_budget = $this->b2c_catpin_minval;
				$fin_budget = $this->b2b_cat_minval * count($pin_array);
			}

			$budget_array[$catid]['f_bgt'] = $fin_budget;
			$budget_array[$catid]['xflg'] = $cat_array[$catid]['x_flag'];
			if ($this->mode == 3)
				$best_pos = 100;
			elseif ($this->mode == 2)
				$best_pos = $this->option;
			elseif ($this->mode == 5)
				$best_pos = 0;
			else
				$best_pos = 1;
			$best_budget = $min_budget;

			foreach ($pin_array as $pincode => $pin_val_array) {
				if (($this->mode == 4 || $this->mode == 6) && $live_inventory[$catid][$pincode])
					$best_pos = $live_inventory[$catid][$pincode]['p'];


				if (DEBUG_MODE)
					echo '<br>Pincode:' . $pincode;
				if (($this->custompackage_f == "0" && $this->pinbgt_f == "0") || (($this->custompackage_f == "1" || $this->pinbgt_f == "1") && (($cat_array[$catid]['cst'] == "L" || $cat_array[$catid]['cst'] == "SZ" || $cat_array[$catid]['cst'] == "Z") && in_array($pincode, $this->allarea_pin_array)) || (($cat_array[$catid]['cst'] != "L" && $cat_array[$catid]['cst'] != "SZ" && $cat_array[$catid]['cst'] != "Z") && in_array($pincode, $this->non_allarea_pin_array)))) {
					//if (DEBUG_MODE)
					//	echo '->considered';
					
					if($this->params['filterData'] != 1)
					$budget_array[$catid]['pin_data'][$pincode]['anm'] = $pin_array[$pincode]['anm'];
					
					$budget_array[$catid]['pin_data'][$pincode]['cnt'] = $callcount_default;
					$budget_array[$catid]['pin_data'][$pincode]['cnt_f'] = $callcount_default * ($this->params['tenure'] / 12);
					$budget_array[$catid]['pin_data'][$pincode]['srch_cnt'] = $searchcount_default;
					$budget_array[$catid]['pin_data'][$pincode]['srch_cnt_f'] = $searchcount_default * ($this->params['tenure'] / 12);
					$budget_array[$catid]['pin_data'][$pincode]['sbflg'] = 0;
					$budget_array[$catid]['pin_data'][$pincode]['dummy'] = 1;
					foreach ($position_factor as $pos => $pos_factor) {
					
						if( $this->params['filterData'] != 1 || ($this->params['filterData'] == 1 && $pos == 100) )
						{
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_booked'] = 0;
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidder'] = "";
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidvalue'] = $bidvalue * $pos_factor;

							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget'] = $min_budget;
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail'] = 1;
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['is_bidder'] = 0;
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['fp_type'] = 0;
						}
					}
					$budget_array[$catid]['pin_data'][$pincode]['best_flg'] = $best_pos;
					$budget_array[$catid]['pin_data'][$pincode]['best_bgt'] = $best_budget;
					$alloc_total += 1;
					$catpin_budget_array[$catid][$pincode] = $min_budget;
				} // end if
				else {
					//if (DEBUG_MODE)
					//	echo '->Not considered';

				}

			} // for each pincode
		} // for each category

		//if (DEBUG_MODE) {
		//	echo '<br> Dummy values';
		//	print_r($budget_array);
		//}
		############ setting default values Ends ################
		############ discount table ##############

		if ($this->remove_discount != 1) {
			$sql = "select * from tbl_catpin_discount_percentage where catid in (" . $this->catid_list . ") and pincode in (" . $this->pincode_list . ") ORDER BY catid, pincode";
			$res = parent::execQuery($sql, $this->dbConbudget);
			$num_rows = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<br><b>Discount table Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num_rows;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			if ($res && $num_rows > 0) {

				while ($row = mysql_fetch_assoc($res)) {

					if (DEBUG_MODE) {
						echo '<hr>';
						print_r($row);
					}
					$catid = $row['catid'];
					$pincode = $row['pincode'];
					foreach ($position_factor as $pos => $pos_factor) {
						$discount_array[$catid][$pincode][$pos] = $row["pos" . $pos . "_discount"];
					}
				}
			}
		}
		if (DEBUG_MODE) {
			echo '<br> this->remove_discount:' . $this->remove_discount;
			echo '<br> Discount Array';
			print_r($discount_array);
		}
		
		############ Renewal percentage Slabs Starts #####################
		//if($this->mode==4)
		{
			$sql = "select * from payment_version_approval where parentid = '" . $this->parentid . "' and is_pdg = 1 and disruption_flag in (0,1) ORDER BY approval_date desc limit 1";
			$res = parent::execQuery($sql, $this->finance);
			$num_rows = mysql_num_rows($res);

			if (DEBUG_MODE) {
				print_r($this->finance);
				echo '<br><b>Renewal percentage Slabs Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num_rows;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
			if ($res && $num_rows > 0) {

				while ($row = mysql_fetch_assoc($res)) {

					if (DEBUG_MODE) {
						echo '<hr>';
						print_r($row);
					}
					$approval_date = $row['approval_date'];
				}
				$approval_year = date('Y', strtotime($approval_date));
				$current_date = date('Y-m-d');
				$days_diff = date_diff(date_create($current_date), date_create($approval_date));
				$num_days = $days_diff->format('%a');
				$yr_idx = min((int)(($num_days - 1) / 365), 5); // -1 days handling for exact 1 yr and so on
			} else {
				$approval_year = date('Y');
				$num_days = 0;
				$yr_idx = 0;
			}

			if ($renewal_tier == 1) {
				$renewal_percenatge_arr[5] = 3.50;
				$renewal_percenatge_arr[4] = 3.50;
				$renewal_percenatge_arr[3] = 3.50;
				$renewal_percenatge_arr[2] = 2.00;
				$renewal_percenatge_arr[1] = 1.75;
				$renewal_percenatge_arr[0] = 1.50;
			} else {
				$renewal_percenatge_arr[5] = 3.50;
				$renewal_percenatge_arr[4] = 3.50;
				$renewal_percenatge_arr[3] = 3.50;
				$renewal_percenatge_arr[2] = 2.00;
				$renewal_percenatge_arr[1] = 1.75;
				$renewal_percenatge_arr[0] = 1.50;
			}

			if ($this->tenure_f > 1) {
				$renewal_percenatge_arr[1] = 1;
				$renewal_percenatge_arr[0] = 1;
			}

			$renewal_percent = $renewal_percenatge_arr[$yr_idx];
			
			if (DEBUG_MODE) {
				echo '<br> tenure_f:' . $this->tenure_f;
				echo '<br> Approval_date:' . $approval_date;
				echo '<br> Current_date:' . $current_date;
				echo '<br> Approval_year:' . $approval_year;
				echo '<br> days_diff:' . $num_days;
				echo '<br> year_index:' . $yr_idx;
				echo '<br> renewal_tier:' . $renewal_tier;
				echo '<br> renewal_percenatge_arr:';
				print_r($renewal_percenatge_arr);
				echo '<br> renewal percent:' . $renewal_percent;
			}
		}
		
		############ Renewal percentage Slabs Ends   #####################
		
		############ FP table ##############


		$sql = "select * from tbl_fixedposition_pincodewise_bid where catid in (" . $this->catid_list . ") and pincode in (" . $this->pincode_list . ") ORDER BY catid, pincode";
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num_rows = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>FP bid Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res)) {

				/*if (DEBUG_MODE) {
					echo '<hr>';
					print_r($row);
				}*/

				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$callcount = $row['callcount'] * $callcnt_growth_rate;
				$searchcount = $row['reach_count'] * $callcnt_growth_rate;
				$callcount = max($callcount, $callcount_default);
				$searchcount = max($searchcount, $searchcount_default);
				if (DEBUG_MODE) {
					echo '<br>catid:' . $catid;
					echo '-> pincode:' . $pincode;
				}
				if (($this->custompackage_f == "0" && $this->pinbgt_f == "0") || (($this->custompackage_f == "1" || $this->pinbgt_f == "1") && (($cat_array[$catid]['cst'] == "L" || $cat_array[$catid]['cst'] == "SZ" || $cat_array[$catid]['cst'] == "Z") && in_array($pincode, $this->allarea_pin_array)) || (($cat_array[$catid]['cst'] != "L" && $cat_array[$catid]['cst'] != "SZ" && $cat_array[$catid]['cst'] != "Z") && in_array($pincode, $this->non_allarea_pin_array)))) {
					if (DEBUG_MODE)
						echo '->Flexi Considered';
					// flexi condition
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
					$b_val = $row['x_bidvalue'] * $bidvalue_premium;

					if ($cat_array[$catid]['b2b_flag'] == 1)
						$bidvalue = max(481, $b_val);
					else
						$bidvalue = max(96, $b_val);

					$row['category_minbudget'] = 0; // as per instructions from ajay mail dated 23rd oct 2015
					$budget_array[$catid]['bval'] = $bidvalue;
					$budget_array[$catid]['c_bgt'] = 0;
					if ($cat_array[$catid]['b2b_flag'] == 1) {
						$cat_min_budget[$catid] = $row['category_minbudget'] * $this->tenure_f; // for b2b cat no factor to be multiplied [Note: tenure_f removed as per instruction from Ajay 31stJuly2015]
						//$cat_min_budget[$catid]   = $row['category_minbudget'];
						$budget_array[$catid]['bm_bgt'] = $cat_min_budget[$catid];
					} else {
						$cat_min_budget[$catid] = $row['pincodefactor'] * $row['category_minbudget'] * $this->tenure_f; // [Note: tenure_f removed as per instruction from Ajay 31stJuly2015]
						//$cat_min_budget[$catid]   = $row['pincodefactor']*$row['category_minbudget']; 
						$budget_array[$catid]['bm_bgt'] = 0;
					}

					$budget_array[$catid]['f_bgt'] = 0;			
		
					//$budget_array[$catid]['pin_data'][$pincode]['anm']	= $pin_array[$pincode]['anm'];
					$budget_array[$catid]['pin_data'][$pincode]['cnt'] = $callcount;
					$budget_array[$catid]['pin_data'][$pincode]['cnt_f'] = $callcount * ($this->params['tenure'] / 12);
					$budget_array[$catid]['pin_data'][$pincode]['srch_cnt'] = $searchcount;
					$budget_array[$catid]['pin_data'][$pincode]['srch_cnt_f'] = $searchcount * ($this->params['tenure'] / 12);
					$budget_array[$catid]['pin_data'][$pincode]['sbflg'] = 0;
					$budget_array[$catid]['pin_data'][$pincode]['dummy'] = 0;
					$total_inv_booked = 0;
					$x_sold_flg = 0;
					$f_sold_flg = 0;
					foreach ($position_factor as $pos => $pos_factor) {
					 if( $this->params['filterData'] != 1 || ($this->params['filterData'] == 1 && $pos == 100) )
					 {
						$is_bidder = 0;
						$inv_booked = $row["pos" . $pos . "_inventory_booked"];
						$bidder = $row["pos" . $pos . "_bidder"];
						//$inv_avail   = (1-$inv_booked);
						$inv_avail = round((1 - $inv_booked), 2);
						$total_inv_booked += $inv_booked;
						$bidder_array = array();
						$bid_res = array();
						$above_50_counter = 0;
						$full_inv_counter = 0;
						//if($this->parentid && (stripos($bidder,$this->parentid.'-') !== false))
						if ($this->parentid && $bidder) {
							$bid_res = $this->bidder_explode_array($bidder, $this->parentid);

							$bidder_array = $bid_res['bid_array'];
							$above_50_counter = $bid_res['50_counter'];
							$full_inv_counter = $bid_res['full_counter'];
							if ($bidder_array[$this->parentid]['existing'] == 1) {
								$is_bidder = 1;
								$inv_avail += $bidder_array[$this->parentid]['inv'];
							}
						} else {
							$above_50_counter = 0;
						}

						if (DEBUG_MODE) {
							echo '<br>Pos : ' . $pos;
							print_r($bid_res);
							echo '<br>inv avail:' . $inv_avail;
							echo '<br>discount %:' . ((100 - $discount_array[$catid][$pincode][$pos]) / 100);
						}
						$orig_inv_avail = $inv_avail;
						
						//if($above_50_counter==0 || ($above_50_counter>0 && $is_bidder == 1 && $full_inv_counter==0 && $bidder_array[$this->parentid]['above_50']==1))
						if ($inv_avail > 0.50 || ($is_bidder == 1 && round($inv_avail, 2) == 0.50))  // [disruption logic <50% for existing bidder=50%]
						{
							$inv_avail = 1;
						}
						// exlusive sold for this category pincode
						if ($pos == 0 && $inv_booked > 0)
							$x_sold_flg = 1;

						if ($pos > 0 && $pos != 100 && $inv_booked > 0)
							$f_sold_flg = 1;

						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_booked'] = $inv_booked;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidder'] = $bidder;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['bidvalue'] = $bidvalue * $pos_factor;
						
						############## custom position handling #################
						if (($this->mode == 4 || $this->mode == 6) && $live_inventory[$catid][$pincode]['bflag'] != 1 && $x_sold_flg != 1) {
							if (DEBUG_MODE) {
								echo '<hr>Renewal:';
								echo '<br>enable parial flag:' . $this->enable_partial;
								echo '<br>inv avail:' . $inv_avail;
								echo '<br>pos:' . $pos;
								print_r($live_inventory[$catid][$pincode]);
							}
							if (($this->enable_partial == 1 && $inv_avail > 0.50) || ($this->enable_partial == 0 && $inv_avail == 1)) {
								//$inv_avail = $live_inventory[$catid][$pincode]['i'];
								# consider this positon selling giving preference as best position
								if (DEBUG_MODE)
									echo '<br>Loop 1:consider this positon selling giving preference as best position';
								if ($live_inventory[$catid][$pincode]['p'] == $pos && $live_inventory[$catid][$pincode]['i'] > 0)
									$live_inventory[$catid][$pincode]['bflag'] = 1;
							} else {
								if (DEBUG_MODE)
									echo '<br>Loop 2:partial inventory not allowed';

								$inv_avail = 0;
								if ($live_inventory[$catid][$pincode]['p'] == $pos)
									$live_inventory[$catid][$pincode]['bflag'] = 2;
							}

						}

						if (($this->enable_partial == 0 && $inv_avail < 1) || ($pos > 0 && $x_sold_flg == 1 && $pos != 100))
						{
							if (DEBUG_MODE)
							{
								echo '<br>Making Avaiable inventory 0[conditon 1]';
								echo '<br>xclusive flag:'.$cat_array[$catid]['x_flag'];
								echo '<br>fp_max_cnt:'.$cat_array[$catid]['fp_max_cnt'];
							}
							$inv_avail = 0;
						}

						if($inv_avail > 0 && $cat_array[$catid]['x_flag']==1 && $cat_array[$catid]['fp_max_cnt']>0 && $pos>$cat_array[$catid]['fp_max_cnt'] && $pos != 100)
						{
							if (DEBUG_MODE)
							{
								echo '<br>Making Avaiable inventory 0[conditon 2]';
								echo '<br>pos:'.$pos;
								echo '<br>xclusive flag:'.$cat_array[$catid]['x_flag'];
								echo '<br>fp_max_cnt:'.$cat_array[$catid]['fp_max_cnt'];
							}
							$inv_avail = 0;
						}
						
						if ($inv_avail > 0) {
							if (!isset($discount_array[$catid][$pincode][$pos]))
								$discount_array[$catid][$pincode][$pos] = 0;

							$cat_pin_budget = round($bidvalue * $pos_factor * $callcount * $this->tenure_f * $inv_avail * ((100 - $discount_array[$catid][$pincode][$pos]) / 100));
							
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
							if($cat_array[$catid]['b2b_flag'] != 1)
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['b2cmin_budget'] = 20 * $this->tenure_f * $callcount;
							else
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['b2bmin_budget'] = 100 * $this->tenure_f * $callcount;
							
							if ($this->accrued_p_bpd[$catid][$pincode] > 0) {
									////if(DEBUG_MODE)
									////{
									////	echo '<br>I am here';
									////	print_r($this->accrued_p_bpd[$catid][$pincode]);
									////	echo '<br>last budget'.$this->accrued_p_bpd[$catid][$pincode]*($this->tenure_f*360)*1.33*($pos_factor);
									////	echo '<br>JK budget'.$cat_pin_budget;
									////}
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['l33_budget'] = $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['n_budget'] = $cat_pin_budget;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['l15_budget'] = $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * 1.15 * $pos_factor;
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['a_bpd'] = $this->accrued_p_bpd[$catid][$pincode] * $pos_factor;
							
								if ($this->tenure_f > 1) {
									if (($num_days / 365) <= 1.5 && $num_days>0) {
										$cat_pin_budget = $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor;
									} else {
										//if ($discount_array[$catid][$pincode][$pos] > 0) {
											//$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
										//} else {
											//$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
											//$cat_pin_budget = max($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * 1.15 * $pos_factor);
										//}
										$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
										$cat_pin_budget = max($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * 1.15 * $pos_factor);
									}
								} else {
									//if ($discount_array[$catid][$pincode][$pos] > 0) {
										//$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
									//} else {
										//$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
										//$cat_pin_budget = max($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * 1.15 * $pos_factor);
									//}
									$cat_pin_budget = min($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * $renewal_percent * $pos_factor);
									$cat_pin_budget = max($cat_pin_budget, $this->accrued_p_bpd[$catid][$pincode] * ($this->tenure_f * 365) * 1.15 * $pos_factor);
								}

							}
							##########
							
							
							//### Old logic bid values ###############
							//$min_cat_pin_budget = round($cat_min_budget[$catid] * $pos_factor * $inv_avail);
							// [removing positon fator in new logic as per mail from Ajay
							### New logic bid values ###############
							$min_cat_pin_budget = round($cat_min_budget[$catid] * $inv_avail);

							if ($cat_array[$catid]['b2b_flag'] == 1) {
								if($cat_pin_budget<(100*$this->tenure_f*$callcount))
								{
									$cat_pin_budget = max(100*$this->tenure_f*$callcount, $cat_pin_budget);
									$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['b2bmin_budget_flg'] = 1;
								}
								//$cat_pin_budget = max(0.10, $cat_pin_budget);
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget'] = $cat_pin_budget;
								$b2b_cat_pin_budget[$catid]['pos'][$pos] += $cat_pin_budget;
							} else {
								if($cat_pin_budget<(20*$this->tenure_f*$callcount))
								{
									$cat_pin_budget = max(20*$this->tenure_f*$callcount, $cat_pin_budget);
									$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['b2cmin_budget_flg'] = 1;
								}
								$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget'] = max($cat_pin_budget, $min_cat_pin_budget, $this->b2c_catpin_minval);
							}
						} else {
							$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['budget'] = 0;
						}
						
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['inv_avail'] = $inv_avail;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['is_bidder'] = $is_bidder;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['orig_inv_avail'] = $orig_inv_avail;
						$budget_array[$catid]['pin_data'][$pincode]['pos'][$pos]['val_dis'] = ((100 - $discount_array[$catid][$pincode][$pos]) / 100);
						
						if($this->current_live_budget_arr[$catid][$pincode]['a_clbgt']>=0)
						{
							$budget_array[$catid]['pin_data'][$pincode]['a_clbgt']=$this->current_live_budget_arr[$catid][$pincode]['a_clbgt'];
							$budget_array[$catid]['pin_data'][$pincode]['a_clbpd']=$this->current_live_budget_arr[$catid][$pincode]['a_clbpd'];
						
						}
					  }
					}

					if ($f_sold_flg == 1) // if any fixed position already sold- no exclusive selling can take place
					{
						$budget_array[$catid]['pin_data'][$pincode]['pos'][0]['inv_avail'] = 0;
					}
					if ($total_inv_booked > 0)
						$budget_array[$catid]['pin_data'][$pincode]['sbflg'] = 1;
					################################ best budget calc logic ###############################
					$max_budget = -1;
					$max_pos = 0;
					$best_pos = 0;
					$best_budget = 0;

					unset($pos_value);
					unset($mode1_bp);

					if (DEBUG_MODE) {
						echo '<hr><H1>best budget calc logic</H1></hr>';
						echo '<br>Mode:' . $this->mode;
						echo '<br>option:' . $this->option;
						echo '<br>onlyExclusive:' . $this->onlyExclusive;
						echo '<br>Catid:' . $catid;
						echo '<br>Pincode:' . $pincode;
						//print_r($budget_array[$catid]['pin_data'][$pincode]);
						echo '<br>';
					}
					if ($this->mode == 5) // exclusive position
					{
						if ($budget_array[$catid]['pin_data'][$pincode]['pos'][0]['inv_avail'] > 0) {
							if (DEBUG_MODE)
								echo '<br>Exclusive position - 100% inventory Available - so considering this as best pos.';

							$max_pos = 0;
							$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['0']['budget'];
						} else {
							if (DEBUG_MODE)
								echo '<br>Exclusive position - seeking max budget for all available positions';

							foreach ($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos => $pos_value) {
								if (DEBUG_MODE) {
									echo '<hr>max_budget:' . $max_budget;
									echo '<br>max_pos:' . $max_pos;
									echo '<br>pos:' . $pos . '<br>';
									print_r($pos_value);
								}
								if (!$this->onlyExclusive)//if set to false - will check for other positions
								{
									if ($pos_value['budget'] > $max_budget && $pos_value['inv_avail'] > 0 && $pos != 0) {
										$max_budget = $pos_value['budget'];
										$max_pos = $pos;
									}
								}
							}
							if ($max_budget == -1) {
								$max_pos = 100;
								$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
							}
						}

						$best_pos = $max_pos;
						$best_budget = $max_budget;
					} elseif ($this->mode == 4 || $this->mode == 6) // Custom position
					{
						$renew_pos = 0;
						if (DEBUG_MODE)
							print_r($live_inventory[$catid][$pincode]);
						if ($live_inventory[$catid][$pincode]['bflag'] == 1 || $live_inventory[$catid][$pincode]['p'] == 100) // in same positon got 50% or more inventory so considering this as best pos.
						{
							if (DEBUG_MODE)
								echo 'in same positon got 50% or more inventory so considering this as best pos.';

							$max_pos = $live_inventory[$catid][$pincode]['p'];
							$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos'][$max_pos]['budget'];
							$renew_pos = 1;
						} else // best position - max budget for all all positions available
						{
							if (DEBUG_MODE)
								echo '<br>best position - seeking max budget for all available positions';

							foreach ($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos => $pos_value) {
								if (DEBUG_MODE) {
									echo '<hr>max_budget:' . $max_budget;
									echo '<br>max_pos:' . $max_pos;
									echo '<br>pos:' . $pos . '<br>';
									print_r($pos_value);
								}
								if ($pos_value['budget'] > $max_budget && $pos_value['inv_avail'] > 0 && $pos != 0) {
									$max_budget = $pos_value['budget'];
									$max_pos = $pos;
								}
							}
							if ($max_budget == -1) {
								$max_pos = 100;
								$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
							}
							$renew_pos = 2;
						}
						if ($renew_pos > 1)
							$renew_msg = 2;
						$best_pos = $max_pos;
						$best_budget = $max_budget;
					} elseif ($this->mode == 2) // fixed positon
					{
						$max_pos = 100;
						$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos'][100]['budget'];
						// $this->option >= $pos)
						// $best_budget[$catid][$pincode]['best_flag']  = 0;
						if (DEBUG_MODE) {
							print_r($budget_array[$catid]['pin_data'][$pincode]['pos']);
						}
						foreach ($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos => $pos_value) {
							if (DEBUG_MODE) {
								echo '<hr>max_budget:' . $max_budget;
								echo '<br>max_pos:' . $max_pos;
								echo '<br>pos:' . $pos . '<br>';
								print_r($pos_value);
							}
							if ($pos >= $this->option) {	
								//echo '<br>Here:'.$pos.' >= '.$this->option;
								//if($pos_value['budget'] > $max_budget && $pos_value['inv_avail']>0)
								if ($pos < $max_pos && $pos_value['inv_avail'] > 0) {
									$max_budget = $pos_value['budget'];
									$max_pos = $pos;
								}
							}
						}
						$best_pos = $max_pos;
						$best_budget = $max_budget;
					} elseif ($this->mode == 1) //  best positon
					{
						$opt = 1;
						//print_r($budget_array[$catid][$pincode]['pos']);
						foreach ($budget_array[$catid]['pin_data'][$pincode]['pos'] as $pos => $pos_value) {
							if ($pos != 0 && $pos != 100 && $pos_value['inv_avail'] > 0) {
								$mode1_bp['POS' . $pos] = $pos_value['budget'];
							}
						}

						if (DEBUG_MODE) {
							print_r($mode1_bp);
						}
						if (is_array($mode1_bp)) {
							//arsort($mode1_bp);
							//multisort($mode1_bp, array_values(SORT_ASC), array_keys(SORT_DESC)); 
							array_multisort(array_values($mode1_bp), SORT_DESC, array_keys($mode1_bp), SORT_ASC, $mode1_bp);
						}

						$s_mode1_bp = $mode1_bp;
						if (DEBUG_MODE) {
							print_r($s_mode1_bp);
						}

						if (count($s_mode1_bp) >= $this->option)
							$match_option = $this->option;
						else
							$match_option = count($s_mode1_bp);

						if (is_array($s_mode1_bp)) {
							foreach ($s_mode1_bp as $pos => $pos_budget) {
								if ($match_option == $opt) {
									$max_pos = $pos;
									$max_budget = $pos_budget;
								}
								$opt++;
							}
						} else {
							$max_pos = 100;
							$max_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
						}
						$best_pos = str_replace("POS", "", $max_pos);
						$best_budget = $max_budget;
					} elseif ($this->mode == 3) // package position
					{
						$best_pos = 100;
						$best_budget = $budget_array[$catid]['pin_data'][$pincode]['pos']['100']['budget'];
					}

					if (DEBUG_MODE) {
						echo '<br>best_pos:' . $best_pos;
						echo '<br>best_budget:' . $best_budget;
					}
					$budget_array[$catid]['pin_data'][$pincode]['best_flg'] = $best_pos;
					$budget_array[$catid]['pin_data'][$pincode]['best_bgt'] = $best_budget;
					$budget_array[$catid]['pin_data'][$pincode]['renew_flg'] = $renew_pos; // 1- same positon 2-other positon
					//$cat_budget_array[$catid] += $best_budget; // this needs to be done after becasue of dummy values
					
					//$total_budget 			  += $best_budget;
					
					//$alloc_summary_array[$best_pos] = $alloc_summary_array[$best_pos] + 1; // this also need to be done after because of summy values
				} // flexi condition
				else {
					if (DEBUG_MODE)
						echo '->Flexi Not Considered';
				}
			} // while loop for catid pincode
		}
		$total_budget = 0;
		$cat_budget_array = array();
		foreach ($budget_array as $catid => $catid_values) {
			if (DEBUG_MODE) {
				echo '<br>Catid : ' . $catid;
				//echo '<br>Catid Values : ';
				//print_r($catid_values);
			}
			foreach ($catid_values['pin_data'] as $pincode => $pincode_values) {
				if (DEBUG_MODE) {
					echo '<br>Sunz';
					echo '<br>Pincode : ' . $pincode;
					//echo '<br>Pincode Values : ';
					//print_r($pincode_values);
					echo '<br>best_bgt : ' . $pincode_values['best_bgt'];
					echo '<br>best_pos : ' . $pincode_values['best_flg'];
					print_r($live_inventory[$catid][$pincode]);
				}
				if ($this->mode == 4 && !isset($live_inventory[$catid][$pincode]['p'])) {
					if (DEBUG_MODE) {
						echo '<br>Unsetting: Catid:' . $catid . ' &pin:' . $pincode;
					}
					unset($budget_array[$catid]['pin_data'][$pincode]);
				} else {
					$best_bgt = $pincode_values['best_bgt'];
					$best_pos = $pincode_values['best_flg'];

					$cat_budget_array[$catid] += $best_bgt;
					$alloc_summary_array[$best_pos] += 1;

					$pin_flexi_budget_array[$pincode]['bgt'] += $best_bgt;
					$pin_flexi_budget_array[$pincode]['cat_cnt'] += 1;
					$pin_flexi_budget_array[$pincode]['cat_list'] .= $catid . ",";
					$pin_flexi_budget_array[$pincode]['pin_flexi_ratio'] = 1 + (($this->pinbgt_array[$pincode] - $pin_flexi_budget_array[$pincode]['bgt']) / $pin_flexi_budget_array[$pincode]['bgt']);
				}
			}

			$budget_array[$catid]['c_bgt'] = $cat_budget_array[$catid];
			if ($cat_array[$catid]['b2b_flag'] == 1) {
				$final_bgt = max($budget_array[$catid]['c_bgt'], $budget_array[$catid]['bm_bgt'], $this->b2b_cat_minval);
				$budget_array[$catid]['bm_bgt'] = max($budget_array[$catid]['bm_bgt'], $this->b2b_cat_minval);
			} else {
				$final_bgt = max($budget_array[$catid]['c_bgt'], $budget_array[$catid]['bm_bgt']);
			}

			$budget_array[$catid]['f_bgt'] = $final_bgt;
			$total_budget += $final_bgt;
		}
		if (DEBUG_MODE) {
			echo '<hr>';
			echo '<h1>Final Result array abcd:</h1>';
			print_r($alloc_summary_array);
			print_r($budget_array);
			echo '<hr>';
			echo '<br>custompackage_f:' . $this->custompackage_f;
		}
		if ($this->custompackage_f == "1") {
			$package_array = $this->fn_package_bidders($this->catid_list, $this->pincode_list);

			$package_bidders = $package_array['bidder'];
			$package_bid = $package_array['bid'];
			$package_sc = $package_array['sc'];

			$category_bidders = $package_array['cat_bidder'];
			$category_bid = $package_array['cat_bid'];

			$flexi_factor = 1 + (($this->packagebgt_yrly - $total_budget) / $total_budget);
			if (DEBUG_MODE) {
				echo '<br>total_budget:' . $total_budget;
				echo '<br>packagebgt_yrly:' . $this->packagebgt_yrly;
				echo '<br>flexi_factor:' . $flexi_factor;
				echo '<hr>';
			//$this->closest_binary();
			}
			$total_flexi_budget = 0;
			$total_best_budget_check = 0;
			
			foreach ($budget_array as $catid => $catid_values) {
				if (DEBUG_MODE) {
					echo '<hr>Catid : ' . $catid;
					//echo '<br>Catid Values : ';
					//print_r($catid_values);
				}
				$flexi_bgt_sum = 0;
				$c_bgt = $catid_values['c_bgt'];
				$f_bgt = $catid_values['f_bgt'];
				if ($c_bgt < $f_bgt) {
					$c_f_factor = 1 + (($f_bgt - $c_bgt) / $c_bgt);
				} else {
					$c_f_factor = 1;
				}
				if (DEBUG_MODE) {
					echo '<hr>c_bgt: ' . $c_bgt;
					echo '<hr>f_bgt: ' . $f_bgt;
					echo '<hr>c_f_factor: ' . $c_f_factor;
					//echo '<br>Catid Values : ';
					//print_r($catid_values);
				}
				foreach ($catid_values['pin_data'] as $pincode => $pincode_values) {
					$flexi_bgt = $pincode_values['best_bgt'] * $flexi_factor * $c_f_factor;
					$flexi_bpd = round($flexi_bgt / ($this->tenure_f * 365), 2);
					if ($package_bid[$catid][$pincode])
						$flexi_pos = $this->closest_binary($flexi_bpd, $package_bid[$catid][$pincode]);
					else
						$flexi_pos = 1;

					$budget_array[$catid]['pin_data'][$pincode]['flexi_bgt'] = $flexi_bgt;
					$budget_array[$catid]['pin_data'][$pincode]['flexi_pos'] = $flexi_pos;
					$budget_array[$catid]['pin_data'][$pincode]['flexi_bpd'] = $flexi_bpd;
					
					if($this->params['filterData'] != 1)
					{
						$budget_array[$catid]['pin_data'][$pincode]['flexi_bidder'] = $package_bidders[$catid][$pincode];
						$budget_array[$catid]['pin_data'][$pincode]['flexi_bid'] = $package_bid[$catid][$pincode];
						$budget_array[$catid]['pin_data'][$pincode]['flexi_sc'] = $package_sc[$catid][$pincode];
					}
					$total_best_budget_check += $pincode_values['best_bgt'];
					$total_flexi_budget += $flexi_bgt;
					$flexi_bgt_sum += $flexi_bgt;
					/*if (DEBUG_MODE) {
						echo '<br>Flexi';
						echo '<br>Pincode : ' . $pincode;
						//echo '<br>Pincode Values : ';
						//print_r($pincode_values);
						echo '<br>best_bgt : ' . $pincode_values['best_bgt'];
						echo '<br>best_pos : ' . $pincode_values['best_flg'];
						echo '<br>flexi_bgt : ' . $flexi_bgt;
						echo '<br>flexi_pos : ' . $flexi_pos;
					}*/

				}
				$budget_array[$catid]['flexi_bgt'] = $flexi_bgt_sum;
				//$total_flexi_budget = round($total_flexi_budget);
			}
			if (DEBUG_MODE)
				echo '<br>Flexi Over';
		} // end of flexi package
		if (DEBUG_MODE) {
				echo '<br>total_best_budget_check:'.$total_best_budget_check;
				echo '<br>total_flexi_budget:'.$total_flexi_budget;
		}
		if ($this->pinbgt_f == "1") {
			if (DEBUG_MODE) {
				echo '<br>this->pinbgt_f condition here';
				echo '<H1>pin_flexi_budget_array:</H1>';
				print_r($pin_flexi_budget_array);
				echo '<H1>pinbgt array:</H1>';
				print_r($this->pinbgt_array);
			}
			$package_array = $this->fn_package_bidders($this->catid_list, $this->pincode_list);

			$package_bidders = $package_array['bidder'];
			$package_bid = $package_array['bid'];
			$package_sc = $package_array['sc'];

			$category_bidders = $package_array['cat_bidder'];
			$category_bid = $package_array['cat_bid'];

			$total_flexi_budget = 0;
			foreach ($budget_array as $catid => $catid_values) {
				if (DEBUG_MODE) {
					echo '<hr>Catid : ' . $catid;
					//echo '<br>Catid Values : ';
					//print_r($catid_values);
				}
				$flexi_bgt_sum = 0;
				$c_bgt = $catid_values['c_bgt'];
				$f_bgt = $catid_values['f_bgt'];
				if ($c_bgt < $f_bgt) {
					$c_f_factor = 1 + (($f_bgt - $c_bgt) / $c_bgt);
					if (DEBUG_MODE) {
					echo '<br>c_f_factor: ' . $c_f_factor;
					//echo '<br>Catid Values : ';
					//print_r($catid_values);
					}
				} else {
					$c_f_factor = 1;
				}
				$c_f_factor = 1;
				
				foreach ($catid_values['pin_data'] as $pincode => $pincode_values) {
					$flexi_bgt = $pincode_values['best_bgt'] * $pin_flexi_budget_array[$pincode]['pin_flexi_ratio'] * $c_f_factor;
					$flexi_bpd = round($flexi_bgt / ($this->tenure_f * 365), 2);
					if ($package_bid[$catid][$pincode])
						$flexi_pos = $this->closest_binary($flexi_bpd, $package_bid[$catid][$pincode]);
					else
						$flexi_pos = 1;

					$budget_array[$catid]['pin_data'][$pincode]['flexi_bgt'] = $flexi_bgt;
					$budget_array[$catid]['pin_data'][$pincode]['flexi_pos'] = $flexi_pos;
					$budget_array[$catid]['pin_data'][$pincode]['flexi_bpd'] = $flexi_bpd;
					$budget_array[$catid]['pin_data'][$pincode]['flexi_bidder'] = $package_bidders[$catid][$pincode];
					$budget_array[$catid]['pin_data'][$pincode]['flexi_bid'] = $package_bid[$catid][$pincode];
					$budget_array[$catid]['pin_data'][$pincode]['flexi_sc'] = $package_sc[$catid][$pincode];

					$total_flexi_budget += $flexi_bgt;
					$flexi_bgt_sum += $flexi_bgt;
					if (DEBUG_MODE) {
						echo '<br>Pin Flexi';
						echo '<br>Pincode : ' . $pincode;
						//echo '<br>Pincode Values : ';
						//print_r($pincode_values);
						echo '<br>best_bgt : ' . $pincode_values['best_bgt'];
						echo '<br>best_pos : ' . $pincode_values['best_flg'];
						echo '<br>flexi_bgt : ' . $flexi_bgt;
						echo '<br>flexi_pos : ' . $flexi_pos;
					}

				}
				$budget_array[$catid]['flexi_bgt'] = $flexi_bgt_sum;
				//$total_flexi_budget = round($total_flexi_budget);
			}
			if (DEBUG_MODE)
				echo '<br>Flexi Over';
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
		foreach ($alloc_summary_array as $best_pos => $pos_values) {
			$alloc_per_summary[$best_pos] = round($alloc_summary_array[$best_pos] / $alloc_total * 100, 2);
		}

		$alloc_per_4_7 = round(($alloc_per_summary[4] + $alloc_per_summary[5] + $alloc_per_summary[6] + $alloc_per_summary[7]));
		
		//print_r($cat_min_budget);
		//print_r($b2b_cat_pin_budget);
		//echo '<br>Allocation Array<br>';
		//print_r($alloc_summary_array);
		//print_r($budget_array);
		$cat_desc['VNM'] = "Very Near Me";
		$cat_desc['NM'] = "Near Me";
		$cat_desc['A'] = "Area";
		$cat_desc['Z'] = "Zonal";
		$cat_desc['SZ'] = "Super Zonal";
		$cat_desc['L'] = "All Area";

		$return_array['c_data'] = $budget_array;
		$return_array['pos'] = $alloc_per_summary;
		$return_array['tb_bgt'] = $total_budget;
		$return_array['tb_flexi_bgt'] = $total_flexi_budget;
		if ($alloc_per_summary[100] < 100) {
			$campaignid = 2;
		} else {
			$campaignid = 1;
		}
					
		//if($this->tenure_f==5)
		//	$final_citymin_budget = max(($fp_min_budget + (($fp_min_budget/3)*(min(2,($this->params['tenure']/12) - 1)))), 15000*count($pin_array));
		//else
		if ($this->mode == 3 || ($this->mode == 4 && $alloc_per_summary[100] == 100 && count($pin_array) == 1))
			$final_citymin_budget = $package_min_budget;
		elseif (($all_flag / ($az_flag + $all_flag)) >= 0.65) {
			if (DEBUG_MODE)
				echo '<br>All Area Cat:';

			if ($this->tenure_f > 1) {
					//$final_citymin_budget = $fp_min_budget*$this->tenure_f;
				$final_citymin_budget = $fp_min_budget + (($fp_min_budget / 3) * (min(3, ($this->params['tenure'] / 12) - 1)));
			} else
				$final_citymin_budget = $fp_min_budget;
		} else {
			if (DEBUG_MODE)
				echo '<br>Non All Area Cat:';		
			//$final_citymin_budget = max($city_min_budget,$pincity_min_budget*$this->tenure_f); [has been removed as discussed with AJAY]
			
			//$city_pincity_min_budget =  max($pinminbudget, 3000);
			//$final_citymin_budget = max($city_pincity_min_budget,$pincity_min_budget*$this->tenure_f);
			if ($this->tenure_f > 1) {
				//$final_citymin_budget = max($fp_min_budget,$pincity_min_budget)*$this->tenure_f;
				$final_citymin_budget = max($fp_min_budget, $pincity_min_budget) + ((max($fp_min_budget, $pincity_min_budget) / 3) * (min(3, ($this->params['tenure'] / 12) - 1)));
			} else
				$final_citymin_budget = max($fp_min_budget, $pincity_min_budget);
		}

		$min_bgt_vfl = array(2 => 15000, 3=>22500, 5=>30000, 10=>36000); 
		
		if ($alloc_per_4_7 == 100) {
			if ($this->tenure_f >= 4)
				$final_citymin_budget = 200000;
			elseif ($this->tenure_f >= 3)
				$final_citymin_budget = 170000;
			elseif ($this->tenure_f >= 2)
				$final_citymin_budget = 135000;
			else
				$final_citymin_budget = 100000;
		}else{
			$tenure_index = $this->params['tenure']/12;
			if($min_bgt_vfl[$tenure_index])
				$final_citymin_budget = max($final_citymin_budget,$min_bgt_vfl[$tenure_index]);
		}

		if (DEBUG_MODE) {
			echo '<hr>City Min Buget Calculation:';
			echo '<br>alloc_per_4_7:' . $alloc_per_4_7;
			echo '<br>package_min_budget:' . $package_min_budget;
			echo '<br>pincity_min_budget:' . $pincity_min_budget;
			echo '<br>fp_min_budget:' . $fp_min_budget;
			echo '<br>city_min_budget:' . $city_min_budget;
			echo '<br>final_citymin_budget:' . $final_citymin_budget;
			echo '<br>this->mode:' . $this->mode;
			echo '<br>count($pin_array):' . count($pin_array);
			echo '<br>pinminbudget:' . $pinminbudget;
			echo '<br>city_pincity_min_budget:' . $city_pincity_min_budget;
			echo '<br>az_flag:' . $az_flag;
			echo '<br>all_flag:' . $all_flag;
			echo '<br>all %:' . ($all_flag / ($az_flag + $all_flag));
			print_r($alloc_per_summary);
		}
		$return_array['reg_bgt'] = $this->regfeeclass_obj->getRegfee($this->contact_nos_list, $campaignid);
		
		
		//print_r($return_array);
		$return_array['tot_bgt'] = $total_budget + $return_array['reg_bgt'];
		$return_array['tot_flexi_bgt'] = $total_flexi_budget + $return_array['reg_bgt'];
		$return_array['city_bgt'] = $final_citymin_budget;
		$return_array['renewal_cnt'] = $this->renewal_cnt;
		$return_array['pinmin_bgt'] = $pinminbudget;
		$return_array['city_pincity_min_bdgt'] = $city_min_budget;
		$return_array['packagemin_bgt'] = $package_min_budget;
		$return_array['citymin_bgt'] = $city_min_budget;
		$return_array['cat_desc'] = $cat_desc;
		$return_array['az_c'] = $az_flag;
		$return_array['all_c'] = $all_flag;
		$return_array['bgt_type'] = $this->budget_type;
		$return_array['upldrts_minbudget'] = $upldrts_minbudget;
		$return_array['upldrts_top_minbudget'] = $upldrts_top_minbudget;
		$return_array['cstm_minbudget_package'] = $upldrts_cstm_minbudget_package;
		$return_array['weeklypackvalue'] = $weekly_package_budget;
		$return_array['monthlypackvalue'] = $monthly_package_budget;
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
		$return_array['active_campaign'] = $this->active_campaign;

		if ($this->national_list_obj) {
			
			//echo '<br> budget :: '.$minimumbudget_national.'---'.$maxbudget_national.'-----'.$statebudget_national.'------'.$minupfrontbudget_national.'---'.$maxupfrontbudget_national.'-----'.$stateupfrontbudget_national;
			
			//echo 'var dump<pre>';var_dump($this -> national_list_obj);die;
			//echo gettype(intval($minupfrontbudget_national)).'saasv'.$maxupfrontbudget_national.'vdbs'.$stateupfrontbudget_national;
			$national_min_budget_arr = $this->national_list_obj->getNationalListingMinBudget($min_monthly_cost = ($minimumbudget_national / 12), $max_monthly_cost = ($maxbudget_national / 12), $minupfrontbudget_national, $maxupfrontbudget_national, $state_monthly_cost = ($statebudget_national / 12), $stateupfrontbudget_national);



			$return_array['monthly_national_budget'] = $national_min_budget_arr['monthly_budget'];
			$return_array['upfront_national_budget'] = $national_min_budget_arr['upfront_budget'];
			$return_array['lifetime_monthly_national_budget'] = $national_min_budget_arr['lifetime']['monthly_budget'];
			$return_array['lifetime_upfront_national_budget'] = $national_min_budget_arr['lifetime']['upfront_budget'];
			$return_array['state_change'] = $national_min_budget_arr['state_change'];
			$return_array['increment_factor'] = $national_min_budget_arr['increment_factor'];

			$return_array['minupfrontbudget_national'] = intval($minupfrontbudget_national);
			$return_array['maxupfrontbudget_national'] = intval($maxupfrontbudget_national);
			$return_array['stateupfrontbudget_national'] = intval($stateupfrontbudget_national);


		}

		if ($this->remfxdpospackbdgt != 0) {
			$return_array['remfxdpospackbdgt'] = $this->remfxdpospackbdgt;
		}

		if ($this->expiredePackval != 0) {
			$return_array['expiredePackval'] = $this->expiredePackval;
		}
		if ($this->expiredePackval_2yrs != 0) {
			$return_array['expiredePackval_2yrs'] = $this->expiredePackval_2yrs;
		}

		if ($this->rnwcstmminpckbgt1yr != 0) {
			$return_array['rnwcstmminpckbgt1yr'] = $this->rnwcstmminpckbgt1yr;
		}


		$return_array['renewal_percent_slab'] = $renewal_percent;
		$return_array['approval_date'] = $approval_date;
		$return_array['num_days'] = $num_days;
		$return_array['yr_idx'] = $yr_idx;

		if ($renew_msg == 2)
			$exact_renewal = 0;
		else
			$exact_renewal = 1;

		$return_array['exact_renewal'] = $exact_renewal;

		$return_array['category_bidders'] = $category_bidders;
		$return_array['category_bid'] = $category_bid;
		$return_array['tenure_f'] = $this->tenure_f;
		
		$result['result'] = $return_array;
		$result['error']['code'] = 0;
		$result['error']['msg'] = "";
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

		return ($result);
	}

	function get_category_details($catids)
	{
		$cat_arr = array();
		if($catids!=''){
			$cat_arr =	explode(",",$catids);
		}
		/*$sql = "select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag, search_type, budget_type 
		from tbl_categorymaster_generalinfo where catid in (" . $catids . ") AND biddable_type=1";*/
		//$res_area = parent::execQuery($sql, $this->dbConDjds);
		//$num_rows = mysql_num_rows($res_area);

		$cat_params = array();
		$cat_params['page'] ='budgetDetailsClass';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,category_type,search_type,budget_type,fp_max_cnt';		

		$where_arr  	=	array();
		if(count($cat_arr)>0){
			$where_arr['catid']					= implode(",",$cat_arr);
			$where_arr['biddable_type']	= '1';
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if (DEBUG_MODE) {
			echo '<br><b>Category Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			//print_r($cat_params);
			//print_r($cat_res_arr);
		}

		if ($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0) {
			foreach ($cat_res_arr['results'] as $key =>$row) {
				if (DEBUG_MODE)
					print_r($row);
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

				$catid = $row['catid'];
				$budget_type = $row['budget_type'];

				if ($budget_type == 2)
					$this->budget_type = 2;

				$business_flag	=	trim($row['business_flag']);
				$category_type	=	trim($row['category_type']);

				$exclusive_flag = 0;
				if(((int)$category_type & 16) == 16){
					$exclusive_flag = 1;
				}
				$block_for_contract = 0;
				if(((int)$category_type & 64) == 64){
					$block_for_contract = 1;
				}
				$b2b_flag = 0;
				if((int)$business_flag == 1){
					$b2b_flag = 1;
				}

				$ret_array[$catid]['cnm'] 		= $row['category_name'];
				$ret_array[$catid]['cid'] 		= $row['catid'];
				$ret_array[$catid]['nid'] 		= $row['national_catid'];
				$ret_array[$catid]['b2b_flag'] 	= $b2b_flag;
				$ret_array[$catid]['bfc'] 		= $block_for_contract;
				$ret_array[$catid]['x_flag'] 	= $exclusive_flag;
				$ret_array[$catid]['cst'] 		= $cat_search_type;
				$ret_array[$catid]['fp_max_cnt']= $row['fp_max_cnt'];
			}
		}
		return ($ret_array);
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

	function fn_package_bidders($catids, $pincodes)
	{
		if (DEBUG_MODE) {
			echo '<br>fn: fn_package_bidders($cat_array,$pin_array)';
			echo '<br>fn: category list :' . $catids;
			echo '<br>fn: pincode list :' . $pincodes;
		}
		$package_bidder = array();
		$package_bid_array = array();
		$sql = "select parentid, companyname, catid, pincode, round(search_contribution,2) as search_contribution, bidperday, 
		contract_bidperday, physical_pincode, physical_area as area, active_date, category_count, pincode_count
		from db_iro.tbl_package_search where catid in (" . $catids . ") and data_city='" . $this->data_city . "' and pincode in (" . $pincodes . ") order by catid, pincode, bidperday desc";
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

		$sql = "select parentid, group_concat(distinct companyname) as companyname, catid, physical_pincode, cat_bidperday, contract_bidperday, group_concat(distinct source) as source, pincode_count, category_count from (
					select parentid, '' as companyname, catid, physical_pincode, round(category_perday,2) as cat_bidperday, round(contract_bidperday,2) as contract_bidperday,'fp' as source, pincode_count, category_count from db_iro.tbl_fp_search where catid in (" . $catids . ") and data_city='" . $this->data_city . "' group by parentid, catid 
				union 
					select parentid, companyname, catid, physical_pincode, round(category_perday,2) as cat_bidperday, round(contract_bidperday,2) as contract_bidperday,'package' as source, pincode_count, category_count from db_iro.tbl_package_search where catid in (" . $catids . ")  and data_city='" . $this->data_city . "'  group by parentid, catid )t 
				group by 
					parentid, catid 
				order by 
					catid, cat_bidperday desc";

		//$res_area = parent::execQuery($sql, $this->dbConDjds);
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

		$return_array['bidder'] = $package_bidder;
		$return_array['bid'] = $package_bid_array;
		$return_array['sc'] = $package_sc_array;
		$return_array['cat_bidder'] = $category_bidder;
		$return_array['cat_bid'] = $category_bid_array;

		return ($return_array);
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
		$biddder_above_50 = 0;
		$above_50_counter = 0;
		$full_inv_counter = 0;
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

				if ($bid_parentid_array[$temp_bid[0]]['inv'] >= 0.50) {
					$bid_parentid_array[$temp_bid[0]]['above_50'] = 1;
					$above_50_counter++;
				} else {
					$bid_parentid_array[$temp_bid[0]]['above_50'] = 0;
				}

				if (round($bid_parentid_array[$temp_bid[0]]['inv'], 2) == 1.00) {
					$bid_parentid_array[$temp_bid[0]]['full_inv'] = 1;
					$full_inv_counter++;
				} else {
					$bid_parentid_array[$temp_bid[0]]['full_inv'] = 0;
				}
			}
		}
		$result['bid_array'] = $bid_parentid_array;
		$result['50_counter'] = $above_50_counter;
		$result['full_counter'] = $full_inv_counter;
		return ($result);
	}


	function getpackagerenewcustomminbudget()
	{

		$sql_compfin = "select a.parentid,a.version,group_concat(a.campaignId order by campaignid asc) as campaigns,group_concat(a.budget order by campaignid asc) as bgt,b.approval_date 
						from payment_apportioning a join payment_version_approval b on a.parentid=b.parentid and a.version=b.version 
						where a.disruption_flag=0 and a.budget>0 and a.budget!=a.balance and a.campaignid in(1,2) and a.entry_date>date_sub(current_date,interval 410 day ) 
						and a.parentid='" . $this->parentid . "' group by a.parentid,a.version order by b.approval_date desc limit 1";


		$res_compfin = parent::execQuery($sql_compfin, $this->finance);

		if (mysql_num_rows($res_compfin)) {
			$arr_compfin = mysql_fetch_assoc($res_compfin);

			if (((string)$arr_compfin['campaigns'] == '1') && ($this->ExpiredDaysvalue < 45 || $this->active_campaign == 1)) {
				$this->rnwcstmminpckbgt1yr = $arr_compfin['bgt'] * 1.5;

				if ($this->rnwcstmminpckbgt1yr < 6000)
					$this->rnwcstmminpckbgt1yr = 6000;
			}




		}


		if (DEBUG_MODE) {
			echo '<hr>intval ' . intval($arr_compfin['campaigns']);
			echo '<hr>$arr_compfin  ' . $arr_compfin['campaigns'];
			echo '<br>sql_compfin ';
			print($sql_compfin);
			echo '<br>arr_compfin ';
			print_r($arr_compfin);
			echo '<br>rnwcstmminpckbgt1yr ' . $this->rnwcstmminpckbgt1yr;
			echo '<br>ExpiredDaysvalue ' . $this->ExpiredDaysvalue;
			echo '<br>$this->active_campaign ' . $this->active_campaign;

		}



	}


	function oldExpRemFxdPosCheck()
	{

		$balanceSum = 0;
		$packExpiredVal = 0;
		$packExpiredOnVal = null;
		$packExpiredDatediff = 0;
		$this->active_campaign = 0;
		//$sql_compfin="select * from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2) and balance>0";
		$sql_compfin = "select campaignid,balance,expired,expired_on from tbl_companymaster_finance where parentid='" . $this->parentid . "' and campaignid in (1,2)";
		$res_compfin = parent::execQuery($sql_compfin, $this->finance);

		if (DEBUG_MODE) {
			echo '<hr>';
			echo '<br>sql_compfin ';
			print($sql_compfin);
		}

		if (mysql_num_rows($res_compfin)) // if it is already active phone seach campagn then do not allow 
		{
			while ($arr_compfin = mysql_fetch_assoc($res_compfin)) {
				if ($arr_compfin['balance'] > 0) {
					$balanceSum += $arr_compfin['balance'];
					$this->active_campaign = 1;
				}

				if ($arr_compfin['expired'] == 1 && ($arr_compfin['campaignid'] == 1 || $arr_compfin['campaignid'] == 2)) {
					$packExpiredVal = 1;
					$packExpiredOnVal = $arr_compfin['expired_on'];
					$todaydate = date('Y-m-d H:i:s');

					if ($packExpiredDatediff == 0) {
						$packExpiredDatediff = round(abs(strtotime($todaydate) - strtotime($packExpiredOnVal)) / 86400);
					} else {
						$packExpiredDatediff = min($packExpiredDatediff, round(abs(strtotime($todaydate) - strtotime($packExpiredOnVal)) / 86400));
					}

					$this->ExpiredDaysvalue = $packExpiredDatediff;

					if (DEBUG_MODE) {
						echo '<hr>';
						echo '<br>packExpiredVal:';
						print($packExpiredVal);
						echo '<br>campaignid:' . $arr_compfin['campaignid'];
						echo '<br>packExpiredOnVal:';
						print($packExpiredOnVal);
						echo '<br>packExpiredDatediff: ' . $packExpiredDatediff;
						echo '<br>exppackday: ' . $this->exppackday;
					}
				}
			}


			if ($balanceSum == 0) // if there is no balance then checking for data in expired tables
			{
				$sql_bde = "select count(1) as countval from tbl_bidding_details_expired where parentid='" . $this->parentid . "' and campaignid=2 and position_flag in (4,5,6,7)";
				$res_bde = parent::execQuery($sql_bde, $this->finance);
				$num_bde = mysql_num_rows($res_bde);

				if (DEBUG_MODE) {
					echo '<br><b>BD Query:</b>' . $sql_bde;
					echo '<br><b>Result Set:</b>' . $res_bde;
					echo '<br><b>Num Rows:</b>' . $num_bde;
					echo '<br><b>Error:</b>' . $this->mysql_error;
					print_r($this->finance);
				}
				if ($res_bde && $num_bde > 0) {

					while ($row_bde = mysql_fetch_assoc($res_bde)) {
						if ($row_bde['countval'] > 0) {
							$this->oldExpRemFxdPos = 1;
						}

						if (DEBUG_MODE) {
							echo '<hr>';
							echo '<br>countval:-';
							print_r($row_bde);
							echo '<br>oldExpRemFxdPos:-' . $this->oldExpRemFxdPos;
						}


					}
				}

			}


			if (DEBUG_MODE) {
				echo '<hr>';
				echo '<br> exppackday ' . $this->exppackday;
			}
			
			//if($balanceSum==0 && $packExpiredVal==1 && $packExpiredDatediff>=90) // expired package value setting 
			if ($balanceSum == 0 && $packExpiredVal == 1 && $packExpiredDatediff > $this->exppackday) // expired package value setting 
			{
				$this->expiredePackflg = 1;

				if (DEBUG_MODE) {
					echo '<hr>';
					echo '<br> exppackday ' . $this->exppackday;
					echo '<br>expiredePackflg: ' . $this->expiredePackflg;

				}
			}
		}


		
		
		// we will read from tbl_bidding_details_expired instead of tbl_fixedposition_pincodewise_bid because it may be released 
		
		//$sql_bde="select count(1)  as cnt from tbl_bidding_details_expired where parentid ='".$this->parentid."'";

	}

	function getFPPincodeMinimumBudget($pincodelist)
	{

		$ret_array = array();

		$pincodelistarray = explode(",", $pincodelist);
		$pincodelistarray = array_unique(array_filter($pincodelistarray));

		$pincodelist = "'" . implode("','", $pincodelistarray) . "'";

		$sql = "select count(1) as cnt, max(minbudget_fp) as minbudget_fp, max(top_minbudget_fp) as top_minbudget_fp   
		from tbl_pincode_minimum_budget
		where pincode in (" . $pincodelist . ") and minbudget_fp > 0 and top_minbudget_fp > 0";

		$res = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>pincodelist:</b>' . $pincodelist;
			echo '$pincodelistarray';
			print_r($pincodelistarray);
			echo '<br><b>tbl_pincode_minimum_budget Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}


		if ($num_rows > 0) {
			$row = mysql_fetch_assoc($res);
			if ($row['cnt'] == count($pincodelistarray)) {
				// means all pincode belongs to minimum budget pincode
				$ret_array['lowminpinbdgtelg'] = 1;
				// now we will fetch the 1st row and get the data 			

				$ret_array['pin_minbudget_fp'] = $row['minbudget_fp'];
				$ret_array['pin_top_minbudget_fp'] = $row['top_minbudget_fp'];
			} else {
				$ret_array['lowminpinbdgtelg'] = 0;
			}

		} else {
			// at least one pincode does not belongs to minimum budget pincode
			$ret_array['lowminpinbdgtelg'] = 0;
		}

		if (DEBUG_MODE) {
			echo '$ret_array';
			print_r($ret_array);
		}

		return ($ret_array);

	}


}



?>
