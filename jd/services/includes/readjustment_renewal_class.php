<?php
class readjustment_live_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $renewal_type	= null;
	var  $version 		= null;
	
	
	function __construct($params)
	{	
		
		global $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$version			= trim($params['version']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
		$renewal_type		= trim($params['renewal_type']);		
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($renewal_type)=='')
		{
			$message = "Renewal Type is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}		 
		
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->rquest  	  	= $rquest;
		$this->renewal_type	= $renewal_type;
				
		$this->setServers();		 
		
	}
	
	
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		
		$this->fnc    		= $db[$conn_city]['fin']['master'];	
		$this->conn_fnc_slave   = $db[$conn_city]['fin']['slave'];	
		if(strtolower($this->module)  == 'me'){
			$this->idc 		= $db[$conn_city]['idc']['master'];	
		}else{
			$this->idc 		= $db[$conn_city]['tme_jds']['master'];
		}
	}	
	function readjustment_live() {
		$func = $this->rquest;
		if((int)method_exists($this,$func) > 0)
			return $this->$func();
		else {
			$message = "Invalid Function";
			return json_encode($this->sendDieMessage($message));			
		}
	}
	
	function readjustment()
	{		   
			$sql_chkBal="SELECT COALESCE(sum(balance),0) as balance, COALESCE(sum(bid_perday),0) as bid_perday,version,campaignid, GROUP_CONCAT(campaignid) as existing_campaignid,sum(balance)/sum(bid_perday) as existing_bitper_day from tbl_companymaster_finance where parentid = '".$this->parentid."' and balance > 0 and campaignid in (1,2,7) having existing_bitper_day > 10";
			$res_chkBal= parent::execQuery($sql_chkBal, $this->fnc);		
			$row_chkBal = parent::fetchData($res_chkBal);	
			
			if($res_chkBal && round($row_chkBal['balance']) > 0)
			{
				$exist_version= $row_chkBal['version'];
				$exist_balance = $row_chkBal['balance'];
				$exist_campaignid= $row_chkBal['existing_campaignid'];
				$exist_campaignid_arry = explode(",",$exist_campaignid);
				$exist_bitperday =	$row_chkBal['bid_perday'];						
				$existing_noofdays = $row_chkBal['existing_bitper_day'];
				//ceil($row_chkBal['balance'] / $row_chkBal['bid_perday']) ; 		
				
				$sql_existing_del="delete from tbl_existing_readjustment where parentid='".$this->parentid."' and version='".$row_chkBal['version']."'";
				$res_existing_del=parent::execQuery($sql_existing_del, $this->fnc);				
				
				$sql_sum_data="SELECT COALESCE(sum(balance),0) as balance, COALESCE(sum(budget)/sum(duration),0) as bid_perday, COALESCE(sum(budget),0) as budget from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag = 1 and campaignid in (1,2,7)";				
				
				$res_sum_data=parent::execQuery($sql_sum_data, $this->idc); 
				
				if($res_sum_data && parent::numRows($res_sum_data) > 0)
				{
					$row_sum_data = parent::fetchData($res_sum_data);
					$temp_sum_budget= $row_sum_data['budget'];
					$factor  = $row_chkBal['balance'] / $temp_sum_budget;		
				}
				
				$sql_adjment="SELECT * FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND recalculate_flag = 1 and campaignid in (1,2,7)";
				$res_adjment=parent::execQuery($sql_adjment,$this->idc); 
				
				if($res_adjment && parent::numRows($res_adjment) > 0)
				{				  				  
				  $row_cnt=parent::numRows($res_adjment);
				  
				  while($row_adjment =  parent::fetchData($res_adjment))
				   {	
				 	
				 	 $bal_adjust_flag=$row_adjment['bal_adjust_flag'];
				 	  /* renewalType-->Flag 0-  meaning changing in category, pincode and postion //Differential Renewal
						renewalType--->Falg 1 - no CHANGE*/ //Exact Renewal
					  /*$bal_adjust_flag = 0 i.e. Without balance readjustment and 1=with balance adjustment */	
					
					if($this->renewal_type == 1)
					{
						$renewal_type="Exact Renewal";				

						//with balance adjustment
						if($bal_adjust_flag == 1)
						{
							$readjustment_type="with balance adjustment";
							
							$renewal_budget  = round($row_adjment['budget'],4);
							if(in_array($row_adjment['campaignid'],$exist_campaignid_arry))
							{
								$existing_balance = $row_chkBal['balance'];
							} else {
								$existing_balance = 0;
							}
							$new_budget = $renewal_budget ;
							$renewal_duration = $row_adjment['duration'];
							$existing_balance_days  = 0;
							$new_duration =  ceil($renewal_duration + $existing_balance_days);						
						 }
						
						//without balance adjustment		
						if($bal_adjust_flag == 0)
						{
							$exiting_bal=$row_chkBal['balance'];
							
							//$factor = ($exiting_bal / $temp_sum_budget);														
							$readjustment_type="Without balance readjustment";
							$renewal_budget  = round($row_adjment['budget'] ,4);
							//$existing_balance = round($row_chkBal['balance']/ $row_cnt ,4);
							$existing_balance = $row_adjment['balance'];
							
							$new_budget = ($row_adjment['budget'] * $factor) + $row_adjment['budget']; 
							//round($row_adjment['budget'] + ($row_adjment['budget'] * $factor) ,4);
							
							$renewal_duration = $row_adjment['duration'] ;
							//$existing_balance_days  = ceil($existing_noofdays / $row_cnt);
							$existing_balance_days  = $existing_noofdays;
							$new_duration = ceil($existing_noofdays + $row_adjment['duration']) ;
							//$new_duration = ceil(($existing_noofdays / $row_cnt ) + $row_adjment['duration']) ;
							// ceil(($new_budget /$row_adjment['budget']) * $row_adjment['duration']);	
							
							
						}

						
					} 
					
					if($this->renewal_type == '0')
					{
						$renewal_type="Differential Renewal";
						
						//with balance adjustment
						if($bal_adjust_flag == 1)
						{
							$readjustment_type="with balance adjustment";
							
							$renewal_budget  = round($row_adjment['budget'],4);
							if(in_array($row_adjment['campaignid'],$exist_campaignid_arry))
							{
								$existing_balance = $row_chkBal['balance'];
							}else{
								$existing_balance = 0;
							}
							$new_budget = $renewal_budget ;
							$renewal_duration = $row_adjment['duration'];
							$existing_balance_days  = 0;
							$new_duration =  ceil($renewal_duration + $existing_balance_days);		
							
						}
						
						//without balance adjustment		
						if($bal_adjust_flag == 0)
						{
							$readjustment_type="Without balance readjustment";
							
							$exiting_bal=$row_chkBal['balance'];							
							//$factor = $exiting_bal / $temp_sum_budget;
							
							$renewal_budget  = round($row_adjment['budget'],4);
							//$existing_balance = round($row_chkBal['balance'] / $row_cnt,4);
							$existing_balance = $row_chkBal['balance'];
							$new_bal =  $row_adjment['budget'] * $factor;
							$new_bitper= $row_adjment['budget'] / $row_adjment['duration'];
							$existing_new_duration = ($new_bal/$new_bitper) ; 
							$new_budget = ($row_adjment['budget'] * $factor) + $row_adjment['budget'] ; 
							//round($row_adjment['budget'] + ($row_adjment['budget'] * $factor) ,4);
							$renewal_duration = $row_adjment['duration'];
							$existing_balance_days  = $existing_noofdays;
							$new_duration = ceil($existing_new_duration + $row_adjment['duration']);
							//($existing_noofdays / $row_cnt ) + $row_adjment['duration'] ; 
							//ceil(($new_budget /$row_adjment['budget']) * $row_adjment['duration']);
													
						}
					}					
					 				 
					 
					 $newcampaignid .= $row_adjment['campaignid'] . ",";	
					 $newbudget  .= $new_budget . "," ;			 
					 $newduration .= $new_duration . "," ;
					 $newversion  .= $row_adjment['version'] . ",";
					 $readjustmentType .= $readjustment_type .",";
					 $renewalType .= $renewal_type .",";
					 
					 
					 
					 $sql_insert="insert into tbl_balance_readjustment(parentid,version,campaignid,renewal_budget,existing_balance,new_budget,renewal_duration,existing_balance_days,new_duration,readjustment_type,renewal_type,insert_dt) values('".$this->parentid."','".$row_adjment['version']."','".$row_adjment['campaignid']."','".$renewal_budget."','".$exist_balance."','".$new_budget."', '".$renewal_duration."', '".$existing_balance_days."','".$new_duration."','".$readjustment_type."','".$renewal_type."',now())";				 
				   
					  $res_insert=parent::execQuery($sql_insert, $this->fnc);
					 
				  }				  
				   			   
				   
				  $sql_insert_existing="insert into tbl_existing_readjustment(parentid,version,existing_campaignid,existing_balance,existing_bitperday,existing_noofdays,new_campaignid,new_budget,new_duration,new_version,entry_date,readjustment_type,renewal_type,data_city) values('".$this->parentid."','".$exist_version."','".$exist_campaignid."', '".$exist_balance."','".$exist_bitperday."','".$existing_noofdays."','".rtrim($newcampaignid,',')."','".rtrim($newbudget,',')."','".rtrim($newduration,',')."','".rtrim($newversion,',')."',now(),'".rtrim($readjustmentType,',')."','".rtrim($renewalType,',')."','".$this->data_city."')";
				   $res_insert_existing=parent::execQuery($sql_insert_existing, $this->fnc);			   

				   
				   $responseData = "data inserted sucessfully";
				}	
			  
			}else{
				$responseData = "No data available";
		    }
		   
		    echo json_encode($this->sendDieMessage($responseData));		    
		    
	}	
	
  private function sendDieMessage($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}	
	
}
?>
