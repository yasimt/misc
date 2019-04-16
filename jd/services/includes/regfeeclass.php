<?php

class regfeeclass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $stdcode		= null;
	var $contactno		= null;
	


	function __construct($params)
	{		
		$this->params = $params;		
		

		$errorarray= null;	
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['contactno']) != "" && $this->params['contactno'] != null)
		{
			$this->contactno  = $this->params['contactno']; //initialize datacity
		}else
		{
			//$errorarray['errormsg']='contactno missing';
			//echo json_encode($errorarray); exit;
		}
		
		
		if(!empty($this->params['campaignid']))
		{
			$this->campaignid  = $this->params['campaignid']; //initialize campaignid
		}else
		{
			$errorarray['errormsg']='campaignid missing';	
		}

		if(!empty($this->params['tenurefactor']))
		{
			$this->tenurefactor  = $this->params['tenurefactor']; //initialize module
		}else
		{
			$this->tenurefactor=1;
		}

		if(!empty($this->params['tenure']))
		{
			
			if(!empty($this->params['mode']) && !empty($this->params['mode'])) // budgetDetails calling this  
			{
				if($this->params['tenure']==12)
				{
					$duration=365;
					
				}else
				{
					$duration = $this->params['tenure'] * 30;					
				}
				
			}
			else
			{
				$duration=$this->params['tenure'];
			}
						
			$this->tenure  = $duration; //initialize module
			
		}else
		{
			$this->tenure  =365;
		}		


		if( ! in_array(intval($this->params['campaignid']) , array(1,2,4,5,13,14,15)))
		{	
			//Header('Content-Type: application/json; charset=UTF8');
			//$errorarray['errormsg']='invalid campaignid';
			//echo json_encode($errorarray); exit;
		}

		$this->setServers();
		$this->setstdCode();
		//echo json_encode('const'); exit;
		
		$this-> minimum_reg_fee = 1000; 
		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];	
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];	
	}

	function setstdCode()
	{
		$sql="select stdcode from city_master where ct_name='".$this->data_city."'";
		$res = parent::execQuery($sql, $this->dbConDjds_slave);
		if($res && mysql_num_rows($res))
		{
			$arr= mysql_fetch_assoc($res);			
			$this->stdcode =  ltrim($arr['stdcode'],'0');
			$this->stdcode ='0'.$this->stdcode; 
		}
		
	}
	
	function getRegfee($contactnoval,$campaignid=1)
	{
		$this->contactno = $contactnoval;
		$this->campaignid = $campaignid;
		return $this->calcnewRegFee();
		//return $Regfee['regfee'];
		
	}

	function find_nonpaid_contracts_having_same_number()
	{
		//$contact_details = $this->params['contactno'];
		$contact_details = $this->contactno;

		$contact_details_Arr= explode(',',$contact_details);

		$landlinearr= array();
		$mobilearr= array();
		
		foreach($contact_details_Arr as $value)
		{
			if(strlen($value)==10)
			{
				array_push($mobilearr,$value);
			}else
			{
				array_push($landlinearr,$value);
			}
		}

		$parent_ids_list ='';
		
		if(count($landlinearr))
		{			 
			$contact_details = implode(' ',$landlinearr);
			$contact_details = preg_replace('/\s+/', ' ', trim($contact_details));
			
			$numbers = $contact_details;
			$stdcode_where = "";

			$stdcode_where = " AND stdcode = ".$this->stdcode;

			if($contact_details)
			{
				$sql = "SELECT group_concat(parentid) as plist
						FROM c2s_nonpaid 
						WHERE
						(MATCH(contact_details) AGAINST ('".$contact_details."'  IN BOOLEAN MODE))  ".$stdcode_where."";	
				$res = parent::execQuery($sql, $this->dbConDjds_slave);
				if($res && mysql_num_rows($res))
				{
					while($row = mysql_fetch_assoc($res))
					{
						$parent_ids_list = $row['plist'];
					}
				}
			}
		}
		
		if(count($mobilearr))
		{			 
			$contact_details = implode(' ',$mobilearr);
			$contact_details = preg_replace('/\s+/', ' ', trim($contact_details));
			
			$numbers = $contact_details;
			$stdcode_where = "";

			if($contact_details)
			{
				$sql = "SELECT group_concat(parentid) as plist
						FROM c2s_nonpaid 
						WHERE
						(MATCH(contact_details) AGAINST ('".$contact_details."'  IN BOOLEAN MODE))  ";
				$res = parent::execQuery($sql, $this->dbConDjds_slave);
				//echo "<br>mobile sql--".$sql;
				if($res && mysql_num_rows($res))
				{
					while($row = mysql_fetch_assoc($res))
					{
						$parent_ids_list = $parent_ids_list.','.$row['plist'];
					}
				}
			}
		}
		
		//echo "<br>mobilearr parent_ids_list -". $parent_ids_list;
		$parent_ids_list_arry = explode(',',$parent_ids_list);
		$parent_ids_list_arry = array_unique($parent_ids_list_arry);
		$parent_ids_list_arry = array_merge($parent_ids_list_arry);

		$parent_ids_list = implode(',',$parent_ids_list_arry);

		/*if(DEBUG_MODE)
		{
			echo "<br> sql:-".$sql."<br>";
			echo '<pre>parent_ids_list';
			print_r($parent_ids_list);
		}*/
		//echo $parent_ids_list;
		return($parent_ids_list);
	}


	function calcnewRegFee()
	{
		
		$tenureFactor	= $this->tenurefactor;		
		$tenure			= $this->tenure;

		$webregfee 				= 0;
		$phoneregfee 			= 0;	
		
		$parent_ids_list = $this->find_nonpaid_contracts_having_same_number();

		/*
		if(DEBUG_MODE)
		{
			echo "<br> parent_ids_list "; print_r($parent_ids_list);
			echo "<br>";
		}*/
		
		if(strlen($parent_ids_list) > 0)
		{
			$contractsArray = explode(",",$parent_ids_list);	
			
			
			foreach($contractsArray as $key=>$value)
			{
				$sqlavgbidvalue = "SELECT company_callcnt,final_bid,final_conv FROM c2s_nonpaid WHERE parentid = '".$value."'";

				$resultavgbidvalue 	= parent::execQuery($sqlavgbidvalue, $this->dbConDjds_slave);
				
				if($resultavgbidvalue)
				{
					$rowavgbidvalue    = mysql_fetch_assoc($resultavgbidvalue);
					$BID = max(10, $rowavgbidvalue['final_bid']*$rowavgbidvalue['final_conv']);
					$anualSpendSMS = $BID * $rowavgbidvalue['company_callcnt'];
					$anualSpendWeb = $anualSpendSMS*0.33;
				}
				
				$anualSpendSMS1 = $anualSpendSMS1 + $anualSpendSMS;
				$anualSpendWeb1 = $anualSpendWeb1 + $anualSpendWeb;
			}
				
			if($tenure)
			{	$TenureLead = $tenure;	}
			else
			{	$TenureLead = 365;}
			
			
			$phoneregfee = round(($anualSpendSMS1 / 365) * $TenureLead);
			
			$phoneregfee = $phoneregfee * 1.33; /* Phone reg fee is incresed by 33% as Sandy told */

			if($tenure)
			{	$TenureWeb = $tenure;	}
			else
			{	$TenureWeb = 365;	}
			
			
			$webregfee = round(($anualSpendWeb1  / 365 )* $TenureWeb);
			
			if($this->campaignid==1 ||$this->campaignid==2 || ($this->campaignid==4 && $this->campaignid==5))
				$returnValue = $phoneregfee+$webregfee;
			else if($this->campaignid==4)
				$returnValue = $phoneregfee;
			else if($this->campaignid==5 || $this->campaignid==13 || $this->campaignid==14 || $this->campaignid==15)
				$returnValue = $webregfee;
				
		}

		/*
		if(DEBUG_MODE)
		{
			echo '<br>anualSpendSMS1'.$anualSpendSMS1;
			echo '<br>anualSpendWeb1'.$anualSpendWeb1;
			echo '<br>tenure'.$tenure;
			echo '<br>phoneregfee'.$phoneregfee.'<br> **** webregfee:-'.$webregfee;
			echo '<br>campaignid:-'.$this->campaignid;
			echo "<br> returnValue:-".round($returnValue*0.5)."<br>";
		}
		*/
		
		$reg_fee_amount = ( round($returnValue*0.5) > $this-> minimum_reg_fee ) ? round($returnValue*0.5) : 0;
		return $reg_fee_amount;
		// $retarray;
		//return round($returnValue*0.5);
	}
	
}
?>
