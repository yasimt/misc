<?php

class fetchFinDataClass extends DB
{	
	
	var  $dbConFin    	= null;	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $parentid		= null;
	var  $data_city	= null;
	

	function __construct($params)
	{		
		$this->params = $params;	
		$this->mongo_obj 	= new MongoClass();	
		
		$parentid 		  = trim($params['parentid']);
		$data_city 		  = trim($params['data_city']);
		
		
		$errorarray['error_code'] = 1;
		
		
		if(trim($this->params['action']) != "")
		{
			$this->action  = $this->params['action']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
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
		
		if(trim($this->params['version']) != "" && $this->params['version'] != null)
		{
			$this->version  = $this->params['version']; //initialize datacity
		}
			
		
		if($this->action == 'fetchPaymtAppnData')
		{
			if(trim($this->params['version']) != "" && $this->params['version'] != null)
			{
				$this->version  = $this->params['version']; //initialize datacity
			}else
			{
				$errorarray['errormsg']='version missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		
		if($this->action == 'fetchSinglPaymtParentData')
		{
			$this-> search_by_name_o_id  = $this->params['nameOid']; //search by name or id
			
			$this-> search_by_name	     = $this->params['name']; //search by name or id
			
			$this-> search_by_id	     = $this->params['id']; //search by id
			
		}
		
		
		if(trim($this->params['act_camp_id']) != "" && $this->params['act_camp_id'] != null)
		{
			$actual_campaignids_arr = explode(",",$this->params['act_camp_id']);
			
			$actual_campaignids_arr = array_filter($actual_campaignids_arr);
			
			if(count($actual_campaignids_arr) > 0)
			{
				$actual_campaignids = implode(",", $actual_campaignids_arr);
				
				$this -> campaign_condition = " AND campaignid in (".$actual_campaignids.")";
			}
			else
			{
				$errorarray['errormsg']='Invalid Campaign Ids';
				echo json_encode($errorarray); exit;
			}
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
		$this->nationalObj   	= $db[$data_city]['idc']['master'];		
		$this->db_idc		   	= $db[$data_city]['idc']['master'];	
		## Connection made to check intermediate entry for PDG / H2L from Genio  Lite ##
		$this->dbConbudget      = $db[$data_city]['db_budgeting']['master'];

	}
	
	function fetchBalGaAmt()
	{	
		$fin_data_details['TOTAL_GA_AMT'] = 0;
		$fin_data_details['GA_AMT']		  = 0;
		$fin_data_details['FCV_AMT'] 	  = 0;
		$fin_data_details['BALANCE_AMT']  = 0;
		
		$sql_get_ga = " SELECT (surplus_amount+fcv_amount+delta_amount) AS TOTAL_GA_AMT, (surplus_amount+delta_amount) AS GA_AMT, fcv_amount as FCV_AMT  
						FROM 
						payment_general_surplus 
						WHERE parentid='".$this->parentid."' ";
		$res_get_ga =  parent::execQuery($sql_get_ga, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_ga;
			echo '<br>';
			echo '<br>'.$res_get_ga;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_ga);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if( $res_get_ga && mysql_num_rows($res_get_ga)>0 )
	    {
			$row_get_ga = mysql_fetch_assoc($res_get_ga);
			
			$fin_data_details['TOTAL_GA_AMT']	= ($row_get_ga['TOTAL_GA_AMT'] != null) ? $row_get_ga['TOTAL_GA_AMT'] : 0 ;
			
			$fin_data_details['GA_AMT']			= ($row_get_ga['GA_AMT'] != null) ? $row_get_ga['GA_AMT'] : 0;
			
			$fin_data_details['FCV_AMT']		= ($row_get_ga['FCV_AMT'] != null) ? $row_get_ga['FCV_AMT'] : 0;
			

		}
		
		$sql_get_balance = " SELECT SUM(balance) AS BALANCE_AMT
							 FROM
							 tbl_companymaster_finance
							 WHERE parentid='".$this->parentid."' ".$this -> campaign_condition." ";
		$res_get_balance =  parent::execQuery($sql_get_balance, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_balance;
			echo '<br>';
			echo '<br>'.$res_get_balance;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_balance);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if( $res_get_balance && mysql_num_rows($res_get_balance)>0 )
	    {
			$row_get_balance = mysql_fetch_assoc($res_get_balance);
			
			$fin_data_details['BALANCE_AMT']	= ($row_get_balance['BALANCE_AMT'] != null) ? $row_get_balance['BALANCE_AMT'] : 0;		

		}
		
	    $sql_get_balance_national = "SELECT SUM(balance) AS BALANCE_AMT
									 FROM
									 db_national_listing.tbl_companymaster_finance_national
									 WHERE parentid='".$this->parentid."' ".$this -> campaign_condition." ";
		$res_get_balance_national =  parent::execQuery($sql_get_balance_national, $this->nationalObj);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_balance_national;
			echo '<br>';
			echo '<br>'.$res_get_balance_national;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_balance_national);
			echo '<br>';
			echo '<br>';
	    }
		
		if( $res_get_balance_national && mysql_num_rows($res_get_balance_national)>0 )
	    {
			$row_get_balance_national = mysql_fetch_assoc($res_get_balance_national);
			
			$fin_data_details['BALANCE_AMT'] += ($row_get_balance_national['BALANCE_AMT'] != null) ? $row_get_balance_national['BALANCE_AMT'] : 0;		

		}
		
		
		$fin_data_details['campaign_wise_bal'] = $this -> fetchCampaignWiseBal();
		
		$sql_get_active_with_no_bal = " SELECT *
										FROM
										tbl_companymaster_finance
										WHERE parentid='".$this->parentid."' 
										".$this -> campaign_condition." 
										AND manual_override = 1
										AND expired = 0 ";
		$res_get_active_with_no_bal =  parent::execQuery($sql_get_active_with_no_bal, $this->fin);
		
		if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_active_with_no_bal;
			echo '<br>';
			echo '<br>'.$res_get_active_with_no_bal;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_active_with_no_bal);
			echo '<br>';
			echo '<br>';
	    }
	    
		if( ( $res_get_active_with_no_bal && mysql_num_rows($res_get_active_with_no_bal)>0 ) || $fin_data_details['BALANCE_AMT'] > 0 )
		{
			$fin_data_details['STATUS'] = 'Active';
		}else
		{
			$fin_data_details['STATUS'] = 'Expired';
		}
					
	
		return $fin_data_details;
	}
	
	function checkInterMediateEntry(){

        $sql="select count(catid) as cat_cnt,GROUP_CONCAT(catid) as cat_id_str from tbl_bidding_details_intermediate where parentid ='".$this->parentid."' and version = '".$this->params['version']."' having cat_cnt > 0";
        $res     = parent::execQuery($sql, $this->dbConbudget);

        $num        = mysql_num_rows($res);

        $row_data_cat_data = mysql_fetch_assoc($res);

        $missingCat = array();

        if(DEBUG_MODE)
        {
            echo '<br><b>DB Inter Query:</b>'.$sql;
            echo '<br><b>Result Set:</b>'.$res;
            echo '<br><b>Num Rows:</b>'.$num;
            echo '<br><b>Error:</b>'.$this->mysql_error;
        }

        if($res && $num > 0) {

        $mongo_inputs = array();
        $mongo_inputs['parentid']     = $this->parentid;
        $mongo_inputs['data_city']     = $this->data_city;
        $mongo_inputs['module']        = "ME";
        $mongo_inputs['table']         = "tbl_business_temp_data";
        $mongo_inputs['fields']     = "catIds,nationalcatIds";
        $row_temp_data = $this->mongo_obj->getData($mongo_inputs);

        $cat_str_mongo =     $row_temp_data['catIds'];
        $cat_arr_mongo = explode("|P|",$cat_str_mongo);
        $cat_arr_mongo = array_filter($cat_arr_mongo);
        $row_data_cat_data['mongo_cat_cnt'] = $mongo_cat_cnt = count($cat_arr_mongo);
        $selected_cat_id_arr = explode(",",$row_data_cat_data['cat_id_str']);
        $selected_cat_id_arr = array_filter($selected_cat_id_arr);

        $catid_diff = array();
            if((int) $mongo_cat_cnt != (int) $row_data_cat_data['cat_cnt']){


                $result['error']['code'] = 1;
                $result['error']['msg'] = "Change in Categories Found, Kindly Re-calculate Budget";
                //~ $result['error']['tbl_business_temp_data'] = $row_temp_data;
                //~ $result['error']['tbl_bidding_details_intermediate'] = $row_data_cat_data;
                //~ $catid_diff = array_diff($selected_cat_id_arr,$cat_arr_mongo);
                //~ $catid_diff = array_diff($selected_cat_id_arr,$cat_arr_mongo);
                $catid_diff = array_merge(array_diff($selected_cat_id_arr, $cat_arr_mongo), array_diff($cat_arr_mongo, $selected_cat_id_arr));
                $result['error']['catid_diff'] = $catid_diff;
                return     $result;
            }
            $error_cnt = 0;

            foreach($selected_cat_id_arr as $key => $value){

                if(!in_array($value,$cat_arr_mongo)){
                    $missingCat[] = $value;
                    $error_cnt++;
                }
            }

            if($error_cnt > 0){

                $result['error']['code'] = 1;
                $result['error']['msg'] = "Change in Categories Found, Kindly Re-calculate Budget";
                $catid_diff = array_merge(array_diff($selected_cat_id_arr, $cat_arr_mongo), array_diff($cat_arr_mongo, $selected_cat_id_arr));
                $result['error']['catid_diff'] = $catid_diff;

                return     $result;
            }

            $result['result'] = $row_data_cat_data;
            $result['error']['code'] = 0;
            $result['error']['msg'] = "Success";
        }
        else{
            $result['result'] = array();
            $result['error']['code'] = 1;
            $result['error']['msg'] = "Error while calculating budget, Looks like the items in your cart are obsolete. Kindly recalculate the budget";

        }

        return     $result;
} 
	
	function fetchCampaignWiseBal()
	{
		$sql_get_balance = " SELECT campaignid, balance, bid_perday, manual_override, expired, version
							 FROM
							 tbl_companymaster_finance
							 WHERE parentid='".$this->parentid."' ".$this -> campaign_condition." ";
		$res_get_balance =  parent::execQuery($sql_get_balance, $this->fin);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_balance;
			echo '<br>';
			echo '<br>'.$res_get_balance;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_balance);
			echo '<br>';
			echo '<br>';
	    }
	    
	    
	    $sql_get_balance_national = "SELECT campaignid, balance, bid_perday, manual_override, expired, version
									 FROM
									 db_national_listing.tbl_companymaster_finance_national
									 WHERE parentid='".$this->parentid."'  AND campaignid in (10) ";
		$res_get_balance_national =  parent::execQuery($sql_get_balance_national, $this->nationalObj);
		
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_balance_national;
			echo '<br>';
			echo '<br>'.$res_get_balance_national;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_balance_national);
			echo '<br>';
			echo '<br>';
	    }
	    
	    
	   $sql_get_exst_readjustment = "SELECT * FROM 
									  tbl_balance_readjustment_renewal_process
									  WHERE parentid='".$this->parentid."' AND VERSION='".$this->version."' ";
		$res_get_exst_readjustment =  parent::execQuery($sql_get_exst_readjustment, $this->fin);
		
	    
	    $campaign_wise_balance = array();
	    if( $res_get_balance_national && mysql_num_rows($res_get_balance_national)>0 )
	    {
			while($row_get_balance_national = mysql_fetch_assoc($res_get_balance_national))
			{
				$campaign_wise_balance[$row_get_balance_national['campaignid']]['bal']    		 = $row_get_balance_national['balance'];
				$campaign_wise_balance[$row_get_balance_national['campaignid']]['perday'] 		 = $row_get_balance_national['bid_perday'];
				
				if($campaign_wise_balance[$row_get_balance_national['campaignid']]['bal']>0 && $campaign_wise_balance[$row_get_balance_national['campaignid']]['perday']>0)
					$campaign_wise_balance[$row_get_balance_national['campaignid']]['remaining_days'] = round($row_get_balance_national['balance'] / $row_get_balance_national['bid_perday']) > 0 ? round($row_get_balance_national['balance'] / $row_get_balance_national['bid_perday']) : 0;
				else
					$campaign_wise_balance[$row_get_balance_national['campaignid']]['remaining_days'] = 0;
				
				$campaign_wise_balance[$row_get_balance_national['campaignid']]['status'] = ( $row_get_balance_national['balance']> 0 || ( $row_get_balance_national['manual_override'] == 1 && $row_get_balance_national['expired'] == 0 ) ) ? 'Active' : 'Expired';
				
				$campaign_wise_balance[$row_get_balance_national['campaignid']]['version'] 		 	= $row_get_balance_national['version'];
				
				$campaign_wise_balance[$row_get_balance_national['campaignid']]['readjust_started'] = mysql_num_rows($res_get_exst_readjustment);
				
			}

		}
	    
	    
	    
	    
	    
	    if( $res_get_balance && mysql_num_rows($res_get_balance)>0 )
	    {
			while($row_get_balance = mysql_fetch_assoc($res_get_balance))
			{
				$campaign_wise_balance[$row_get_balance['campaignid']]['bal']    		 = $row_get_balance['balance'];
				$campaign_wise_balance[$row_get_balance['campaignid']]['perday'] 		 = $row_get_balance['bid_perday'];
				
				if($campaign_wise_balance[$row_get_balance['campaignid']]['bal']>0 && $campaign_wise_balance[$row_get_balance['campaignid']]['perday']>0)
					$campaign_wise_balance[$row_get_balance['campaignid']]['remaining_days'] = round($row_get_balance['balance'] / $row_get_balance['bid_perday']) > 0 ? round($row_get_balance['balance'] / $row_get_balance['bid_perday']) : 0;
				else
					$campaign_wise_balance[$row_get_balance['campaignid']]['remaining_days'] = 0;
				
				$campaign_wise_balance[$row_get_balance['campaignid']]['status'] = ( $row_get_balance['balance']> 0 || ( $row_get_balance['manual_override'] == 1 && $row_get_balance['expired'] == 0 ) ) ? 'Active' : 'Expired';
				
				$campaign_wise_balance[$row_get_balance['campaignid']]['version']  		 = $row_get_balance['version'];
				
				$campaign_wise_balance[$row_get_balance['campaignid']]['readjust_started'] = mysql_num_rows($res_get_exst_readjustment);
				
			}

		}else
		{
			if(!mysql_num_rows($res_get_balance_national))
			{
				$campaign_wise_balance['error']['code'] = 1;
				$campaign_wise_balance['error']['msg']  = 'no campaign data available';
			}
		}
		
		   return $campaign_wise_balance;
	}
	
	
	function fetchSinglPaymtParentData()
	{
		if( strlen(trim($this-> search_by_name_o_id)) > 2 )
		{
			$sql_fetch = "SELECT * FROM  db_payment.multicity_contract_details WHERE parentid like '".$this-> search_by_name_o_id."%' OR companyname like '".$this-> search_by_name_o_id."%' limit 5";
			$res_fetch =  parent::execQuery($sql_fetch, $this->db_idc);
			if( $res_fetch && mysql_num_rows($res_fetch)>0 )
			{
				$i = 0;
				while( $row_fetch = mysql_fetch_assoc($res_fetch))
				{
					if( trim($row_fetch['parentid']) != trim($this->parentid) )
					{
						$parent_contract_data[$i]['parentid']  = $row_fetch['parentid'];						
						
						$parent_contract_data[$i]['comp_name'] = $row_fetch['companyname'];
						
						$parent_contract_data[$i]['data_city'] = $row_fetch['data_city'];
						
						$i++;
					}
				}
				
					$return['error']['code'] = 0;
					$return['error']['data'] = $parent_contract_data;
					
			}
			else
			{
					$return['error']['code'] = 1;
					$return['error']['msg']  = 'No matching data found !';
			}
		}
		else
		{
			$return['error']['code'] = 1;
			$return['error']['msg']  = 'Provide more than 2 characters !';
			
		}
		
		return $return;
		
	}
	
	
	function fetchPaymtAppnData()
	{
		
		$sql_get_payapp = " SELECT *
							FROM
							payment_apportioning
							WHERE parentid='".$this->parentid."' 
							AND version ='".$this->version."'";
							
		$res_get_payapp =  parent::execQuery($sql_get_payapp, $this->fin);
		if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_get_payapp;
			echo '<br>';
			echo '<br>'.$res_get_payapp;
			echo '<br>';
			echo 'rows :: '.mysql_num_rows($res_get_payapp);
			echo '<br>';
			echo '<br>';
	    }
	    
	    if(mysql_num_rows($res_get_payapp) > 0)
	    {
			$return['error']['code'] = 1;
			$return['error']['msg']  = 'Deal close is already done for the session !';
		}
		else
		{
			$return['error']['code'] = 0;
			$return['error']['msg']  = 'No Deal close Found';
		}
		
		return $return;
	}
	

}



?>
