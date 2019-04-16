<?php

class invMgmtHiddenClass extends DB
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
			$this->astate  = $this->params['astate']; // initialize mode // 1-dealclose 2-balance readjustment 3-financial approval 4-expiry 5-release 6-part payment 7-ecs 10-category/pin deletion LIve 11-category/pin deletion Shadow 15- pure package entries 16- additon & removal of categories - pure package
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
		
		
		
		if(trim($this->params['catlist']) != "")
		{
			$this->catlist  = $this->params['catlist']; // bidperday for pure package entries
		}
		
		if(trim($this->params['module']) != "")
		{
			$this->module  = $this->params['module']; // module
		}
		
		$this->lcf_hcf_array = array();
		
		
		$this->full_release = 0;
		
		
		//echo json_encode('const'); exit;
	}
	
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
	
	
	function releaseInventory()
	{
		$pin_arr = array();
		$cat_arr = array();
		$release_inv = array();
		
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
			
		
		
			if(count($release_inv)>0)
			{
				$this->full_release = 0;
			}
			else
			{
				$this->full_release = 1;
				//$r = $this->financialSanity();
				if(DEBUG_MODE)
				{
					echo '<br>Financial Sanity Check:'.$r;
				}
			}
			
			if($this->full_release==0)
			{
				$reason = "Full Release -".$this->i_reason;
				
			//	print_r($release_inv);
				$catidstr = implode($catid,",");
				foreach($release_inv as $key_del => $value_del)
				{
					foreach($value_del as $key_sub => $value_sub)
					{
					$key_sub_arr[] = $key_sub; 
					}
					$key_str = implode(",",$key_sub_arr);
					$sql_delete = " DELETE from tbl_bidcatdetails_leadandsupreme_shadow where parentid ='".$this->parentid."' and bid_catid in ('".$key_del."') AND pincode in (".$key_str.")";
					$res_delete 	= parent::execQuery($sql_delete, $this->finance);
					//print_r($this->finance);
					if(!$res_delete)
					{
				
						$return_array['results'] = $release_inv;
						$return_array['error']['msg'] = "Something went wrong Cant delete";
						$return_array['error']['code'] = "1";
						//return($return_array);
					}
					if(DEBUG_MODE)
					{
					echo "<pre>";	
					echo '<br><b>Delete DB Shadow Query:</b>'.$sql_delete;
					echo '<br><b>Result Set:</b>'.$res_delete;
					echo '<br><b>Error:</b>'.$this->mysql_error;
					}
				}	
				
			}
			else if($this->full_release == 1)
			{
				$reason = "Full Release -".$this->i_reason;
				//print_r($this->i_reason);die;
				$sql_delete = " DELETE from tbl_bidcatdetails_leadandsupreme_shadow where parentid ='".$this->parentid."'";
				$res_delete 	= parent::execQuery($sql_delete, $this->finance);
						
				if(DEBUG_MODE)
				{
					echo '<br><b>Delete DB Shadow Query:</b>'.$sql_delete;
					echo '<br><b>Result Set:</b>'.$res_delete;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			}
			
			
			$return_array['results'] = $release_inv;
			
			$return_array['error']['msg'] = "Inventory delete Sucessful";
			
			$return_array['error']['code'] = "0";
			
			return($return_array);
		 
	}
	
	
	function manageDeleteInventory()	
	{		
		
		if($this->astate == 10 || $this->astate == 11) //10-category/pin deletion LIve 11-category/pin deletion Shadow
		{
			if(DEBUG_MODE)
				echo '<br>Release Inventory';
			
			$return_res = $this->releaseInventory();

		}
					
		return($return_res);
		
	}
	
	
	function get_category_details($catids)
	{
		$sql="select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag 
		from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds);
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
				$ret_array[$catid]['bfc']  		= $row['block_for_contract'];
				$ret_array[$catid]['x_flag']    = $row['exclusive_flag'];
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
	
}



?>
