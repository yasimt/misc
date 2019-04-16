<?php

class fetch_update_gstnData_class extends DB
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
		
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$ucode 			= trim($params['ucode']);
		$uname 			= trim($params['uname']);
		
		
		$errorarray['error']['code'] = 1;
			
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['error']['msg']='parentid missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['error']['msg']='module missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['error']['msg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		if( trim($this->params['action']) == "fetchgstnData" )
		{
	
			
		}
		else if( trim($this->params['action']) == "PopulategstnData" )
		{
			if( trim($this->params['gstn_companyname']) )
			{
				$this -> gstn_companyname = $this->params['gstn_companyname'];
				if (strlen($this -> gstn_companyname) != strlen(utf8_decode($this -> gstn_companyname)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in company name';
					echo json_encode($errorarray); exit;
				}
				
			}else
			{
				$errorarray['error']['msg']='gstn company name is missing';
				echo json_encode($errorarray); exit;
			}
			
			if( trim($this->params['gstn_state']) )
			{
				$this -> gstn_state = $this->params['gstn_state'];
				
				if (strlen($this -> gstn_state) != strlen(utf8_decode($this -> gstn_state)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in state';
					echo json_encode($errorarray); exit;
				}
				
			}else
			{
				$errorarray['error']['msg']='gstn state is missing';
				echo json_encode($errorarray); exit;
			}
			
			if( trim($this->params['gstn_address']) )
			{
				$this -> gstn_address = $this->params['gstn_address'];
				if (strlen($this -> gstn_address) != strlen(utf8_decode($this -> gstn_address)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in address';
					echo json_encode($errorarray); exit;
				}
				
			}else
			{
				$errorarray['error']['msg']='gstn address is missing';
				echo json_encode($errorarray); exit;
			}
			
			if( trim($this->params['gstn_pincode']) )
			{
				$this -> gstn_pincode = $this->params['gstn_pincode'];
				if (strlen($this -> gstn_pincode) != strlen(utf8_decode($this -> gstn_pincode)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in pincode';
					echo json_encode($errorarray); exit;
				}
			}else
			{
				$errorarray['error']['msg']='gstn pincode is missing';
				echo json_encode($errorarray); exit;
			}
			
			if( trim($this->params['gstn_version']) )
			{
				$this -> gstn_version = $this->params['gstn_version'];
				if (strlen($this -> gstn_version) != strlen(utf8_decode($this -> gstn_version)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in version';
					echo json_encode($errorarray); exit;
				}
			}else
			{
				$errorarray['error']['msg']='version is missing';
				echo json_encode($errorarray); exit;
			}
			
			if( trim($this->params['gstn_number']) )
			{
				$this -> gstn_number = $this->params['gstn_number'];
				if (strlen($this -> gstn_number) != strlen(utf8_decode($this -> gstn_number)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in GSTN number';
					echo json_encode($errorarray); exit;
				}
				
			}else
			{
				$errorarray['error']['msg']='gstn number is missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if( trim($this->params['pan_no']) )
			{
				$this -> pan_no = $this->params['pan_no'];
				if (strlen($this -> pan_no) != strlen(utf8_decode($this -> pan_no)))
				{
					$errorarray['error']['msg']='Unincode charater is not as per standard of UTF8 in pan no';
					echo json_encode($errorarray); exit;
				}
			}
			else if( strtolower(trim($this->params['datasource'])) != 'jdomni' )
			{
				$errorarray['error']['msg']='pan no is missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if( trim($this->params['datasource']) )
			{
				$this -> datasource = $this->params['datasource'];	
			}
			
			
			if( trim($this->params['updated_by']) )
			{
				$this -> updated_by = $this->params['updated_by'];	
			}
			
			
			$this-> validategstnData();
			
		}else
		{
			$errorarray['error']['msg']='Invalid action passed';
			echo json_encode($errorarray); exit;
		}
		
		
		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->server_city_value= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote_cities');
		
		$this->fin   			= $db[$data_city]['fin']['master'];		
		
		if(strtoupper($this->module) == 'ME')
		{
			$this->conn_idc   	= $db[$data_city]['idc']['master'];
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp 	= $this->conn_idc;
		}
		else if(strtoupper($this->module) == 'TME')
		{
			$this->conn_tme  	= $db[$data_city]['tme_jds']['master'];
			$this->conn_temp     = $this->conn_tme;
		}
		else
		{
			$this->conn_local  	= $db[$data_city]['d_jds']['master'];
			$this->conn_temp     = $this->conn_local;
		}
			
	}
	
	function validategstnData()
	{
		
		if (strlen($this -> gstn_number) != 15)
		{
			$errorarray['error']['msg']='GSTN number should contain 15 charaters';			
		}
		
		if (strlen($this -> gstn_pincode) != 6)
		{
			$errorarray['error']['msg']='Pincode should contain 6 digits';			
		}
		
		if ( !is_numeric($this -> gstn_pincode) )
		{
			$errorarray['error']['msg']='Pincode should contain digits only';			
		}
		
		if ( !is_numeric($this -> gstn_version) )
		{
			$errorarray['error']['msg']='Version should contain digits only';			
		}
		
		$gstn_number_arr = str_split($this -> gstn_number);
		
		if( !is_numeric($gstn_number_arr[0]) || !is_numeric($gstn_number_arr[1]) )
		{
			$errorarray['error']['msg']='First two digits of GSTN number should be numeric only';	
		}
			
			$this->gstn_state_code = $gstn_number_arr[0].$gstn_number_arr[1];
			
		$_3_12_chars_arr = array_slice($gstn_number_arr, 2, 3);
		$_3_12_chars     = implode('',$_3_12_chars_arr);
		
		if(!ctype_alnum($_3_12_chars))
		{
			$errorarray['error']['msg']='Third to twelve characters in GSTN number should be alpha numeric only';	
		}
		
		if(!stristr(strtolower($this -> gstn_number), strtolower($this -> pan_no)) && strtolower(trim($this->params['datasource'])) != 'jdomni' )
		{
			$errorarray['error']['msg']='pan no should be part of gstn number only';	
		}
		
		if(strtolower($gstn_number_arr[count($gstn_number_arr)-2]) != 'z')
		{
			$errorarray['error']['msg']='Second last character in GSTN number can be z only';	
		}
		
		if(!is_numeric($gstn_number_arr[count($gstn_number_arr)-3]))
		{
			$errorarray['error']['msg']='Third last character in GSTN number should be numeric only';	
		}
		
		if(count($errorarray)>0)
		{
			echo json_encode($errorarray); 
			exit;
		}
		
	}
	
	function PopulategstnData()
	{	
		
		$sql_insert = "REPLACE INTO db_payment.tbl_gstn_contract_data 
					   SET
					   parentid			 ='".$this->parentid."',
					   version 			 ='".$this->gstn_version."',
					   gstn_contract_name='".addslashes($this->gstn_companyname)."',
					   gstn_state_code   ='".$this->gstn_state_code."',
					   gstn_state_name   ='".addslashes($this->gstn_state)."',
					   gstn_address		 ='".addslashes($this->gstn_address)."',
					   gstn_pincode		 ='".$this->gstn_pincode."',
					   gstn_no			 ='".$this->gstn_number."',
					   pan_no			 ='".$this->pan_no."',
					   data_source		 ='".$this->datasource."',
					   server_city		 ='".$this->server_city_value."',
					   doneon			 = NOW()";/*
					   ON DUPLICATE KEY UPDATE 
					   gstn_contract_name='".$this->gstn_companyname."',
					   gstn_state_code   ='".$this->parentid."',
					   gstn_state_name   ='".$this->gstn_state."',
					   gstn_address		 ='".$this->gstn_address."',
					   gstn_pincode		 ='".$this->gstn_pincode."',
					   gstn_no			 ='".$this->gstn_number."',
					   data_source		 ='".$this->datasource."',
					   server_city		 ='".$this->server_city_value."',
					   doneon			 = NOW()";*/
	   $res_insert 	= parent::execQuery($sql_insert, $this->conn_idc);
	   
	   $sql_insert_latest = "INSERT INTO db_payment.tbl_gstn_emailer_contract_data_latest 
					   SET
					   parentid			 ='".$this->parentid."',
					   gstn_contract_name='".addslashes($this->gstn_companyname)."',
					   gstn_state_code   ='".$this->gstn_state_code."',
					   gstn_state_name   ='".addslashes($this->gstn_state)."',
					   gstn_address		 ='".addslashes($this->gstn_address)."',
					   gstn_pincode		 ='".$this->gstn_pincode."',
					   gstn_no			 ='".$this->gstn_number."',
					   pan_no			 ='".$this->pan_no."',
					   data_source		 ='".$this->datasource."',
					   server_city		 ='".$this->server_city_value."',
					   done_by			 ='".$this -> updated_by."',
					   doneon			 = NOW()";/*
					   ON DUPLICATE KEY UPDATE 
					   gstn_contract_name='".$this->gstn_companyname."',
					   gstn_state_code   ='".$this->parentid."',
					   gstn_state_name   ='".$this->gstn_state."',
					   gstn_address		 ='".$this->gstn_address."',
					   gstn_pincode		 ='".$this->gstn_pincode."',
					   gstn_no			 ='".$this->gstn_number."',
					   data_source		 ='".$this->datasource."',
					   server_city		 ='".$this->server_city_value."',
					   doneon			 = NOW()";*/
	   $re_insert_latest 	= parent::execQuery($sql_insert_latest, $this->conn_idc);
	   
	    if($this->trace)
	    {
			echo '<br>';
			echo 'sql :: '.$sql_insert;
			echo '<br>';
		   echo '<br>'.$res_insert;
		   echo '<br>';
		   echo 'sql :: '.$sql_insert_latest;
			echo '<br>';
		   echo '<br>'.$re_insert_latest;
		   echo '<br>';
	    }
		if( $re_insert_latest )
		{
			$returnarr['error']['code'] = 0;
			$returnarr['error']['msg'] = 'data updated';
		}
		else
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = 'data not updated';
		}
					
	
		return $returnarr;
	}

	
	function fetchgstnData()
	{
		$sel_fetchgstnData = "SELECT *,parentid,VERSION,gstn_contract_name,gstn_state_code,gstn_state_name,gstn_address,gstn_pincode,gstn_no
						  FROM db_payment.tbl_gstn_contract_data 
						  WHERE parentid='".$this->parentid."'  
						  ORDER BY doneon DESC
						  LIMIT 1 ";
		$res_fetchgstnData 	= parent::execQuery($sel_fetchgstnData, $this->conn_temp);
		if($this->trace)
		{
			echo '<br>';
			echo '<pre>'.$sel_fetchgstnData;
			echo '<br>';
		    echo '<br>'.mysql_num_rows($res_fetchgstnData);
		    echo '<br>';
		}
		if($res_fetchgstnData && mysql_num_rows($res_fetchgstnData)>0)
		{
			if($res_fetchgstnData && mysql_num_rows($res_fetchgstnData))
			{
			   $row_fetchgstnData = mysql_fetch_assoc($res_fetchgstnData);
			   $returnarr['error']['code'] = 0;
			   $returnarr['error']['data'] = $row_fetchgstnData;
			}
			
			
		}
		else
			{
				$returnarr['error']['code'] = 1;
				$returnarr['error']['msg'] = 'no data found';
			}
			
			return $returnarr;
	}
	

}



?>
