<?php

class budgetupdatedealcloseclass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();

	var  $parentid		= null;
	var  $module		= null;
	var  $duration		= null;
	

	
	
	

	function __construct($params)
	{		
		$this->params = $params;		
				
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['duration']) != "" && $this->params['duration'] != null)
		{
			$this->duration  = strtolower($this->params['duration']); //initialize duration
		}else
		{
			$errorarray['errormsg']='duration missing';
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

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}


		$this->setServers();
		//$this->locationinit();
		//echo json_encode('const'); exit;
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
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];

		switch($this->module)
		{
			case 'cs':
			$this->destination = $this->fin;			
		}		
	}	

	
	function submitbudget()
	{		
		
		$sql="select catid,version,pincode_list from tbl_bidding_details_intermediate where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->destination);
		
		
		if($res && mysql_num_rows($res))
		{
			while( $array= mysql_fetch_assoc($res) )
			{
				$pincode_list = json_decode($array['pincode_list'],true);
			
				foreach($pincode_list as $pincode=>$pincodearr)
				{	
											
						//(parentid,catid,pincode,position_flag),
						//$positionarr['callcount']-- need detials how this value will be available in API

						
							$sql = "insert into tbl_bidding_details_shadow set
								parentid		='".$this->parentid."',
								catid			='".$catid."',						
								pincode 		='".$pincode."',
								position_flag 	='".$pincodearr['pos']."',
								
								national_catid 	='".$ncid."',
								inventory		='".$pincodearr['inv']."',
								bidvalue		='".$pincodearr['bidvalue']."',
								callcount		='".$pincodearr['callcount']."',
								duration		='".$this->duration."',							
								data_city		='".addslashes($this->data_city)."',
								physical_pincode='".$this->physical_pincode."',
								latitude		='".$this->latitude."',
								longitude		='".$this->longitude."',
								updatedby		='".$this->usercode."',
								updatedon=now()
								ON DUPLICATE KEY UPDATE
								national_catid 	='".$ncid."',
								inventory		='".$positionarr['inv_booked']."',
								bidvalue		='".$positionarr['bidvalue']."',
								callcount		='".$positionarr['callcount']."',
								duration		='".$this->duration."',							
								data_city		='".addslashes($this->data_city)."',
								physical_pincode='".$this->physical_pincode."',
								latitude		='".$this->latitude."',
								longitude		='".$this->longitude."',
								updatedby		='".$this->usercode."',
								updatedon=now()	";
								
								parent::execQuery($sql, $this->fin);

						
					
				}

			}
	}	


	}

	
	
}



?>
