<?php

class check_downsellEligibility_class extends DB
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
		$version	  	  = trim($params['version']);
		$module	  	      = trim($params['module']);
		
		
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
		
		
		if(trim($this->params['version']) != "" && $this->params['version'] != null && $this->params['version']>0 )
		{
			$this->version  = $this->params['version']; //initialize datacity
		}
		else
		{
			$errorarray['errormsg']='version is missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = $this->params['module']; //initialize datacity
		}
		else
		{
			$errorarray['errormsg']='module is missing';
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
			
		$this->dbConbudget      = $db[$data_city]['db_budgeting']['master'];

	}
	
	function iseligible()
	{	
		
		$sql_get_bidding_data = "SELECT * FROM tbl_bidding_details_intermediate WHERE parentid='".$this->parentid."' AND version='".$this->version."'  ORDER BY catid";
		$res_get_bidding_data =  parent::execQuery($sql_get_bidding_data, $this->dbConbudget);
		
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_bidding_data;
			echo '<br>';
			echo '<br>'.$res_get_bidding_data;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_bidding_data);
			echo '<br>';
			echo '<br>';
			echo '<br>';
	    }
	    
	    if($res_get_bidding_data && mysql_num_rows($res_get_bidding_data))
	    {
			
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = ' All opted positions are higher than 3rd rank ! Downsell is not allowed for '.$this->parentid.' and '.$this->version.' version';
			
			while($row_get_bidding_data = mysql_fetch_assoc($res_get_bidding_data))
			{
				if($row_get_bidding_data['pincode_list'] != '' && $row_get_bidding_data['pincode_list'] != null && $row_get_bidding_data['cat_budget'] >0 )
				{
					$pincode_list_arr = json_decode($row_get_bidding_data['pincode_list'],true);
					if(count($pincode_list_arr)>0)
					{
						foreach($pincode_list_arr as $pincode => $pincode_bidding_data)
						{
							if( $pincode_bidding_data['pos'] <= 3 )
							{
								if($this->trace)
								{
									echo '<pre>';
									echo 'catid :: '.$row_get_bidding_data['catid'];
									echo 'bidding data';
									print_r($pincode_bidding_data);
									echo '<br>';
									echo '<br>';
								}
								$returnarr['error']['code'] = 0;
								$returnarr['error']['msg'] = 'Downsell is allowed for '.$this->parentid.' and '.$this->version.' version';
								break;
							}
						}
						
					}
					else
					{
					    $returnarr['error']['code'] = 1;
						$returnarr['error']['msg'] = 'No data found for either pincode_list or cat_bud in bidding_details_intermediate against '.$this->parentid.' and '.$this->version.' version';
						break;
					}
				}
				else
				{
					$returnarr['error']['code'] = 1;
					$returnarr['error']['msg'] = 'No data found for either pincode_list or cat_bud in bidding_details_intermediate against '.$this->parentid.' and '.$this->version.' version';
					break;
				}
				
			}
			

		}else
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = 'No data found in bidding details intermediate for '.$this->parentid.' and '.$this->version.'version';
		}
					
	
		return $returnarr;
	}
	

}



?>
