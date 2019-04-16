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
		
		$this->categoryClass_obj = new categoryClass();

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
					if($live_array['0']['inv']['error']['code'] == 0)
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
						
					if($shadow_array['0']['inv']['error']['code'] == 0)
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
					if($live_array['0']['inv']['error']['code'] == 0)
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
					
					
					if($shadow_array['0']['inv']['error']['code'] == 0)
					{
						
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['error']['code'] = "0";
						$ret['shadow']['error']['msg'] = "";
						
					}
					else
					{
						$ret['shadow']['results'] = $shadow_array;
						$ret['shadow']['error']['code'] = "1";
						$ret['shadow']['error']['msg'] = "Data Inconsistent / Data Not Foundsaca ";
					}
					// live & Shadow Inventory
		}
		
		if(!($ret['shadow']['error']['code']==0 || $ret['live']['error']['code']==0))
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
			$sql="select * from tbl_bidcatdetails_lead where parentid ='".$this->parentid."' ORDER BY bid_catid, pincode, position_flag";
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
					$catid 		 = $row['bid_catid'];
					$pincode	 = $row['pincode'];
					$version     = 0;
					
					if($orig_pincode != $pincode || $orig_catid != $catid)
					{
						$cnt = 0;
					}
					$live_result[$version]['inv']['results'][$catid]['cnm'] = "";
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['pos'] 		= $row['position_flag'];
					
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['bidvalue'] 	= $row['bid_lead'];
					
					$live_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['inv']      	= $row['partial_ddg_ratio'];
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
		    $sql="select * from tbl_bidcatdetails_leadandsupreme_shadow where parentid ='".$this->parentid."' ORDER BY bid_catid, pincode, position_flag";
			$res 	= parent::execQuery($sql, $this->finance);
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
					$catid 		 = $row['bid_catid'];
					$pincode	 = $row['pincode'];
					$version     = 0;
					
					if($orig_pincode != $pincode || $orig_catid != $catid)
					{
						$cnt = 0;
					}
					$shadow_result[$version]['inv']['results'][$catid]['cnm'] = "";
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['pos'] 		= $row['position_flag'];
					
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['bidvalue'] = $row['bid_lead'];
					
					$shadow_result[$version]['inv']['results'][$catid]['pin_data'][$pincode][$cnt]['inv']      = $row['partial_ddg_ratio'];
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
			
			
			return($shadow_result);
	}	
	function get_category_details($catids)
	{
		//~ $sql="select category_name, national_catid, catid, if((business_flag&1)=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract 
		//~ from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";
		//$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		//$num_rows		= mysql_num_rows($res_area);
		$cat_params = array();
		$cat_params['page'] = 'showInvHiddenClass';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'category_name,national_catid,catid,business_flag,category_type';

		$where_arr  	=	array();			
		$where_arr['catid']			= $catids;
		$where_arr['biddable_type']	= "1";
		if($catids!=''){
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
		
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{		
			
			foreach($cat_res_arr['results'] as $key=>$row)
			{
				//print_r($row);
				$category_type	=	$row['category_type'];
				$business_flag	=	$row['business_flag'];

				if(((int)$category_type & 64) == 64){
					$block_for_contract	= 1; 
				}
				else{
					$block_for_contract = 0;
				}
				if((int)$business_flag == 1){
					$b2b_flag	= 1; 
				}
				else{
					$b2b_flag = 0;
				}

				$catid = $row['catid'];
				$ret_array[$catid]['cnm'] 		= $row['category_name'];
				$ret_array[$catid]['cid'] 		= $row['catid'];
				$ret_array[$catid]['nid'] 		= $row['national_catid'];
				$ret_array[$catid]['b2b_flag']  = $b2b_flag;
				$ret_array[$catid]['bfc']  		= $block_for_contract;
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
	
}



?>
