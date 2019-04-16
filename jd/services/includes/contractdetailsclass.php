<?php

class contractdetailsclass extends DB
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
	var  $parentid		= null;
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	

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
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); 
			
		}	
		else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['campaignid']) != "" && $this->params['campaignid'] != null)
		{
			$this->campaignid  = strtolower($this->params['campaignid']);
		}

		

		if(trim($this->params['version']) != "" && $this->params['version'] != null)
		{
			$this->version  = $this->params['version'];
		}else
		{
			$errorarray['errormsg']='version missing';
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
		
		$this->companyClass_obj  = new companyClass();
		$this->setServers();
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
		$this->db_budgeting		= $db[$data_city]['db_budgeting']['master'];

	}	

	function getPinZoneMap($pincodelist=null)
	{
		// $pincodelist will be comma seprated pincode list 
		
		$data_city = $this->data_city;
		if($this->data_city == 'remote')
		{
			$sql_datacity="SELECT data_city FROM db_iro.tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
			//$res_data_city = parent::execQuery($sql_datacity, $this->dbConDjds);
			//$row_data_city = mysql_fetch_assoc($res_data_city);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'data_city';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'contractdetailsclass';

			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$row_geninfo_main 	= $comp_api_arr['results']['data'][$this->parentid];
				$data_city			= $row_geninfo_main['data_city'];
			}			
		}
		
		
		$returnarr= array();
		$pincode_cond ='';
		if($pincodelist)
		{
			$pincode_cond = " pincode in (".$pincode.") and ";
		}
		
		$sql="SELECT distinct pincode, zoneid FROM tbl_area_master WHERE ".$pincode_cond."  data_city='".$data_city."' AND display_flag>0 AND type_flag=1 AND deleted='0'";
		$res = parent::execQuery($sql, $this->dbConDjds);
		if($res && mysql_num_rows($res))
		{
			while($row = mysql_fetch_assoc($res))
			{
				$returnarr[$row['pincode']]= $row['zoneid'];
			}			
		}
		
		return $returnarr;
	}
	
	function budgetvalidation()
	{
		
		$returnarr['status']='pass';
				
		$sql = "select parentid from tbl_bidding_details_intermediate where parentid='".$this -> parentid."' and version='".$this->version."' AND cat_budget<=0";
		$res = parent::execQuery($sql, $this->db_budgeting);
		if(mysql_num_rows($res)>0)
		{
			$returnarr['status']='fail';
			$returnarr['message']="There is some issue on budget for parentid:".$this -> parentid." version:".$this->version."<br> Please contact Software Team ";
			
		}
		
		return $returnarr;
	}
	
	function GetPlatDiamCategories()
	{
		$PinZoneMap_arr = $this->getPinZoneMap();		
		$category_arr = array();	
		$sql = "select * from tbl_bidding_details_intermediate where parentid='".$this -> parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sql, $this->db_budgeting);
		if(mysql_num_rows($res)==0)
		{
			$sql = "select * from tbl_bidding_details_intermediate_archive where parentid='".$this -> parentid."' and version='".$this->version."' ";
			$res = parent::execQuery($sql, $this->db_budgeting);
		}
		
		$possible_positionarray = array(0,1,2,3,4,5,6,7,100);

		if($this->campaignid==1)
		{
			$possible_positionarray	= array(100);
		}

		if($this->campaignid==2)
		{
			$possible_positionarray	= array(0,1,2,3,4,5,6,7);
		}

		
		
		if(mysql_num_rows($res))
		{
			while($row= mysql_fetch_assoc($res))
			{				
				$catid = $row['catid'];				
				$pincode_list = json_decode($row['pincode_list'],true);
				foreach($pincode_list as $pincode =>$pincodearr)
				{
					$position = $pincodearr['pos'];
					$inventory = $pincodearr['inv'];
					$bidvalue = $pincodearr['bidvalue'];
					$zoneid=$PinZoneMap_arr[$pincode];

					if(in_array($position,$possible_positionarray))
					{
						
						$category_arr[$catid][$zoneid][$pincode][$position]['position'] = $position;
						$category_arr[$catid][$zoneid][$pincode][$position]['inventory'] = $inventory;
						$category_arr[$catid][$zoneid][$pincode][$position]['bid'] = $bidvalue;
					}
				}			
			}
		}

		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>'.$sql;						
			echo '<br><b>db_budgeting:</b>'; print_r($this->db_budgeting);
			echo '<br><b>Error:</b>'.$this->mysql_error;
			echo '<br><b>Num Rows:</b>'.mysql_num_rows($res).'<br>';
			echo '<br><b>possible position  Array:</b><br>';print_r($possible_positionarray);
			echo '<br><b>Result Array:</b><br>';print_r($category_arr);
		}
		
		return $category_arr;
	}
	
	function isflexipackagecontract()
	{		
		$returnarr['isflexipackagecontractflag']='no';
		
		//chaning the ruleset accor to -- https://tga.justdis.com/project/shreos-justdial-cs/issue/421(if not expired contract,then only restrcit category edit)
		
		# active PDG we will allow to edit 
		
		$checkExpired = "select campaignid,balance,expired from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2)"; 
		$resExpired   = parent::execQuery($checkExpired, $this->fin);
		
		if(DEBUG_MODE)
				{
					echo '<br><b>DB  Query:</b>'.$checkExpired;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.mysql_num_rows($resExpired);
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
		
		
		$packagecampaing=$fixedposcampaign=0;
		
		if(mysql_num_rows($resExpired) >0)
		{
			while($fintemprow= mysql_fetch_assoc($resExpired))
			{
				if( intval($fintemprow['campaignid'])==1 )
				{
					if($fintemprow['balance']>0 && intval($fintemprow['expired'])!=1)
					{
						$packagecampaing=1;
					}					
				}

				if( intval($fintemprow['campaignid'])==2 )
				{
					if($fintemprow['balance']>0 && intval($fintemprow['expired'])!=1)
					{
						$fixedposcampaign=1;
					}					
				}
				
				if(DEBUG_MODE)
				{
					echo '<br><b>fintemprow</b>'; print_r($fintemprow);
				}
			}
			
		}
		
		#balance >0 AND expired!=1
		
		
		if( $fixedposcampaign==0 &&  $packagecampaing==1 )
		{
			

		$sql = "select parentid from tbl_payment_type_dealclosed where parentid='".$this -> parentid."' and payment_type like '%flexi_selected_user%' limit 1";
		$res = parent::execQuery($sql, $this->fin);
		if(mysql_num_rows($res)>0)
			{
				$returnarr['isflexipackagecontractflag']='yes';
			}else
			{
				#$sql="select distinct pincode from tbl_bidding_details where parentid ='".$this->parentid."' AND campaignid = 1 ";
			
				$sql="select campaignid,count(distinct pincode) as cnt,version  from tbl_bidding_details where parentid ='".$this->parentid."' group by campaignid";
				$res 	= parent::execQuery($sql, $this->fin);
				$num	= mysql_num_rows($res);
				if(DEBUG_MODE)
				{
					echo '<br><b>DB  Query:</b>'.$sql;
					echo '<br><b>Result Set:</b>'.$res;
					echo '<br><b>Num Rows:</b>'.$num;
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
			
				$pdgversion = $packversion = 0;
				$pdgpincnt  = $packpincnt  = 0;
			
			
				if( $num>0)
				{
					while($row=mysql_fetch_assoc($res))
					{
						if(intval($row['campaignid'])==1)
						{
							$packversion = intval($row['version']);
							$packpincnt = intval($row['cnt']);
						}
						elseif(intval($row['campaignid'])==2)
						{
							$pdgversion = intval($row['version']);						
						}
					}
				
					if($packversion!=0 && $packpincnt>1 && $pdgversion==0)
					{
						$returnarr['isflexipackagecontractflag']='yes';
					}
			
				}
			}
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>'.$sql;
				echo '<br><b>$returnarr Array:</b><br>';print_r($returnarr);
			}
		}

		return $returnarr;		
	}

	function setPincode()
	{
				
		$sql="INSERT INTO tbl_contract_pincodelist set
				parentid='".$this->parentid."',
				pincodelist='".$this->params['pincodelist']."'
				ON DUPLICATE KEY UPDATE
				pincodelist='".$this->params['pincodelist']."'";
				
		parent::execQuery($sql, $this->dbConDjds);
		

		$returnarr['status']='sucessful';
		return $returnarr;
	}
	
}



?>
