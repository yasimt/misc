<?php

class boostExistingBidsClass extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $parentid		= null;
	var  $version		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		  = trim($params['parentid']);
		$data_city 		  = trim($params['data_city']);
		$current_version  = trim($params['current_version']);
		$new_version	  = trim($params['new_version']);
		$current_bidperday= trim($params['current_bidperday']);
		$new_bidperday	  = trim($params['new_bidperday']);
		
		
		$errorarray['error_code'] = 1;
			
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		
		if(trim($this->params['current_version']) != "" && $this->params['current_version'] != null && $this->params['current_version']>0 )
		{
			$this->current_version  = $this->params['current_version']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='current version is missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['new_version']) != "" && $this->params['new_version'] != null && $this->params['new_version']>0 )
		{
			$this->new_version  = $this->params['new_version']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='new version is missing';
			echo json_encode($errorarray); exit;
		}
		
		if( trim($this->params['current_version']) == trim($this->params['new_version']) )
		{
			$errorarray['errormsg']='current and new version cant not be same';
			echo json_encode($errorarray); exit;
		}
		
		
		if( trim($this->params['current_bidperday']) != "" && $this->params['current_bidperday'] != null && $this->params['current_bidperday']>0 )
		{
			$this->current_bidperday  = $this->params['current_bidperday']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='current bid per day is missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['new_bidperday']) != "" && $this->params['new_bidperday'] != null && $this->params['new_bidperday'] > 0)
		{
			$this->new_bidperday  = $this->params['new_bidperday']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='new bid per day is missing';
			echo json_encode($errorarray); exit;
		}
		
		if( trim($this->params['new_bidperday']) <= trim($this->params['current_bidperday']) )
		{
			$errorarray['errormsg']='new per day should be greater than current per day';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		
		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		$this->dbConbudget      = $db[$data_city]['db_budgeting']['master'];

	}
	
	function boostExistingBid()
	{	
		
		$sql_get_bidding_data = "SELECT parentid, docid, VERSION, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, sys_budget, 
								 actual_budget, data_city, updatedby, updatedon FROM tbl_bidding_details WHERE parentid='".$this->parentid."' AND version='".$this->current_version."'";
		$res_get_bidding_data =  parent::execQuery($sql_get_bidding_data, $this->fin);
		
		$sql_get_bidding_data_summary = "SELECT sphinx_id,parentid,docid,companyname,data_city,pincode,latitude,longitude,VERSION,module,contact_details,category_list,pincode_list,pincodejson,pincodebudgetjson,duration,sys_fp_budget,sys_package_budget,sys_regfee_budget,sys_total_budget,actual_fp_budget,actual_package_budget,actual_regfee_budget,actual_total_budget,appfee,areazonal_count,allarea_count,option_selected,mode_selected,bestbudgetflag,exactrenewal,dealclosed_flag,dealclosed_on,dealclosed_by,dealclosed_uname,updatedby,username,updatedon FROM tbl_bidding_details_summary WHERE parentid='".$this->parentid."' AND version='".$this->current_version."'";
		$res_get_bidding_data_summary =  parent::execQuery($sql_get_bidding_data_summary, $this->dbConbudget);
		
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_bidding_data;
			echo '<br>';
			echo '<br>'.$res_get_bidding_data;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_bidding_data);
			echo '<br>';
			echo 'sql :: '.$sql_get_bidding_data_summary;
			echo '<br>';
			echo '<br>'.$res_get_bidding_data_summary;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_bidding_data_summary);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if($res_get_bidding_data && mysql_num_rows($res_get_bidding_data) && $res_get_bidding_data_summary && mysql_num_rows($res_get_bidding_data_summary))
	    {
			while($row_get_bidding_data = mysql_fetch_assoc($res_get_bidding_data))
			{
				$ins_array[] = "('".$this->parentid."', '".$row_get_bidding_data['docid']."', '".$this->new_version."', '".$row_get_bidding_data['campaignid']."', '".$row_get_bidding_data['catid']."', '".$row_get_bidding_data['national_catid']."', '".$row_get_bidding_data['pincode']."', '".$row_get_bidding_data['position_flag']."', '".$row_get_bidding_data['inventory']."', '".$row_get_bidding_data['bidvalue'] ."', '".$row_get_bidding_data['callcount']."', '".$row_get_bidding_data['sys_budget']."', '".$row_get_bidding_data['actual_budget']."', '".$this->data_city."','AddOnPay-APIboostBid','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','')";

			}
			
			while($row_get_bidding_data_summary = mysql_fetch_assoc($res_get_bidding_data_summary))
			{
				$ins_array_sum[] = "('".$row_get_bidding_data_summary['sphinx_id']."', '".$this->parentid."', '".$row_get_bidding_data_summary['docid']."', '".$row_get_bidding_data_summary['companyname']."', '".$this->data_city."', '".$row_get_bidding_data_summary['pincode']."', '".$row_get_bidding_data_summary['latitude']."', '".$row_get_bidding_data_summary['longitude']."', '".$this->new_version."', '".$row_get_bidding_data_summary['module']."', '".$row_get_bidding_data_summary['contact_details']."', '".$row_get_bidding_data_summary['category_list']."', '".$row_get_bidding_data_summary['pincode_list']."', '".$row_get_bidding_data_summary['pincodejson']."', '".$row_get_bidding_data_summary['pincodebudgetjson']."', '".$row_get_bidding_data_summary['duration']."', '".$row_get_bidding_data_summary['sys_fp_budget']."', '".$row_get_bidding_data_summary['sys_package_budget']."', '".$row_get_bidding_data_summary['sys_regfee_budget']."', '".$row_get_bidding_data_summary['sys_total_budget']."', '".$row_get_bidding_data_summary['actual_fp_budget']."', '".$row_get_bidding_data_summary['actual_package_budget']."', '".$row_get_bidding_data_summary['actual_regfee_budget']."', '".$row_get_bidding_data_summary['actual_total_budget']."', '".$row_get_bidding_data_summary['appfee']."', '".$row_get_bidding_data_summary['areazonal_count']."', '".$row_get_bidding_data_summary['allarea_count']."', '".$row_get_bidding_data_summary['option_selected']."', '".$row_get_bidding_data_summary['mode_selected']."', '".$row_get_bidding_data_summary['bestbudgetflag']."', '".$row_get_bidding_data_summary['exactrenewal']."', '".$row_get_bidding_data_summary['dealclosed_flag']."', '".$row_get_bidding_data_summary['dealclosed_on']."', '".$row_get_bidding_data_summary['dealclosed_by']."', '".$row_get_bidding_data_summary['dealclosed_uname']."', 'AddOnPay-APIboostBid', 'AddOnPay-APIboostBid', '".date('Y-m-d H:i:s')."')";

			}
			
			
			if(count($ins_array)>0 && count($ins_array_sum)>0)
			{
				$ins_str_sum = implode(",",$ins_array_sum);
				$sql_str_summary = "REPLACE INTO tbl_bidding_details_summary(sphinx_id,parentid,docid,companyname,data_city,pincode,latitude,longitude,VERSION,module,contact_details,category_list,pincode_list,pincodejson,pincodebudgetjson,duration,sys_fp_budget,sys_package_budget,sys_regfee_budget,sys_total_budget,actual_fp_budget,actual_package_budget,actual_regfee_budget,actual_total_budget,appfee,areazonal_count,allarea_count,option_selected,mode_selected,bestbudgetflag,exactrenewal,dealclosed_flag,dealclosed_on,dealclosed_by,dealclosed_uname,updatedby,username,updatedon) VALUES".$ins_str_sum;
				$res_str_summary        = parent::execQuery($sql_str_summary, $this->dbConbudget);
				
				
				 $ins_str = implode(",",$ins_array);
				 $sql_str = "replace into tbl_bidding_details_shadow (parentid, docid, version, campaignid, catid, national_catid, pincode, position_flag, inventory, bidvalue, callcount, sys_budget, actual_budget, data_city, updatedby, updatedon, booked_date, ecs_booked_date) values ".$ins_str;
				 $res_str        = parent::execQuery($sql_str, $this->dbConbudget);
				 if($this->trace)
					{
						echo '<br>';
						echo 'ins res :: '.$res_str;
						echo '<br>';
						echo '<br>';
						echo 'ins res sum :: '.$res_str_summary;
						echo '<br>';
					   echo '<br>';
					}
					
				 if($res_str && $res_str_summary)
				 {
					 $boost_ratio = $this->new_bidperday/$this->current_bidperday;
					 
					 if($boost_ratio>1)
					 {
						 $update_existing_val     = "UPDATE tbl_bidding_details_shadow set bidvalue = bidvalue * ".$boost_ratio.", sys_budget = sys_budget * ".$boost_ratio.", actual_budget = actual_budget * ".$boost_ratio." WHERE parentid='".$this->parentid."' AND version='".$this->new_version."'";
						 $res_update_existing_val = parent::execQuery($update_existing_val, $this->dbConbudget);
						  if($this->trace)
							{
								echo '<br>';
								echo 'sql :: '.$update_existing_val;
								echo '<br>';
							   echo '<br> res :: '.$res_update_existing_val;
								echo '<br>';
							   echo '<br>';
							}
						 if($res_update_existing_val)
						 {
							$returnarr['error']['code'] = 0;
							$returnarr['error']['msg'] = 'bid, sys budget, act budget  updated by '.round($boost_ratio*100).'%';
						 }else{
							$returnarr['error']['code'] = 1;
							$returnarr['error']['msg'] = 'update failed';
						 }
					 }
				 }else
				 {
					 $returnarr['error']['code'] = 1;
					 $returnarr['error']['msg'] = 'insert in shadow is failed';
				 }
			}


		}else
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = 'No data found in live bidding for '.$this->parentid.' against '.$this->current_version.'version';
		}
					
	
		return $returnarr;
	}
	

}



?>
