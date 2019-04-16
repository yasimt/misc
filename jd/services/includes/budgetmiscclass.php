<?php
/**
 * Filename : catdetailsclass.php
 * Date		: 19/08/2013
 * Author	: pramesh
 
 * */
class budgetmiscclass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $Idc	    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	
	var  $username	= null;
	var  $module	= null;
	var  $data_city	= null;
	var  $ModuleVersion=null;
	
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 
	 	
	
	
	

	function __construct($params)
	{		
		$this->params = $params;				
		

		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			{echo json_encode('Please provide parentid'); exit; }
		}
		
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
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

		if(trim($this->params['username']) != "" && $this->params['username'] != null)
		{
			$this->username  = $this->params['username']; //initialize usercode
		}
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->setServers();		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');		
		
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->Idc   			= $db[$data_city]['idc']['master'];		
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];

		
		switch($this->module)
		{
			case 'cs':
			$this->tempconn = $this->dbConDjds;
			$this->temp_fin = $this->fin;
			break;
						
			case 'tme':
			$this->tempconn = $this->tme_jds;
			$this->temp_fin = $this->tme_jds;
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}

			break;

			case 'me':
			$this->tempconn = $this->Idc;
			$this->temp_fin = $this->Idc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
		}
	
	}

	function resetCampaign()       //$version is current version. It has to be incremented by 10
	{
	
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "version";
			$Versionarray = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqlVersion         = "SELECT version FROM tbl_temp_intermediate WHERE parentid='".$this->parentid."'";
			$resVersion        	= parent::execQuery($sqlVersion,$this->tempconn);
			$numresVersion      = mysql_num_rows($resVersion);
			if($numresVersion>0)
			{
				$Versionarray	= mysql_fetch_assoc($resVersion);
			}

			if(DEBUG_MODE)
			{
				echo '<br><b>sqlVersion'.$sqlVersion;			
				echo '<br><b>Num Rows:</b>'.mysql_num_rows($resVersion);
				echo '<br><b>Error:</b>'.mysql_error();			
			}
		}
					
		$restbl_bidding_details_summary_array_json	= null;
		$res_fin_temp_arr_final_json				= null;
		
		if(count($Versionarray)>0)
		{
			$Version		= $Versionarray['version'];
			
			$sqltbl_bidding_details_summary	 = "SELECT * from tbl_bidding_details_summary WHERE  parentid='".$this->parentid."' AND version='".$Version."'";
			$restbl_bidding_details_summary  = parent::execQuery($sqltbl_bidding_details_summary,$this->dbConbudget);

			
			if(DEBUG_MODE)
			{
				echo '<br><b>sqltbl_bidding_details_summary'.$sqltbl_bidding_details_summary;			
				echo '<br><b>Num Rows:</b>'.mysql_num_rows($restbl_bidding_details_summary);
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}

			if(mysql_num_rows($restbl_bidding_details_summary))
			{
				$restbl_bidding_details_summary_array= mysql_fetch_assoc($restbl_bidding_details_summary);
				$restbl_bidding_details_summary_array_json= json_encode($restbl_bidding_details_summary_array);

				$sqltbl_bidding_details_summary_del	 = "delete from tbl_bidding_details_summary WHERE  parentid='".$this->parentid."' AND version='".$Version."'";
				parent::execQuery($sqltbl_bidding_details_summary_del,$this->dbConbudget);			
			}


			$sel_fin_temp	 = "select * from tbl_companymaster_finance_temp WHERE  parentid='".$this->parentid."' ";
			$res_fin_temp  = parent::execQuery($sel_fin_temp,$this->temp_fin);

					
			if(DEBUG_MODE)
			{
				echo '<br><b>sel_fin_temp'.$sel_fin_temp;			
				echo '<br><b>Num Rows:</b>'.mysql_num_rows($res_fin_temp);
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			

			if(mysql_num_rows($res_fin_temp))
			{
				$res_fin_temp_arr_final = array();
				while($res_fin_temp_arr = mysql_fetch_assoc($res_fin_temp))
				{
					$res_fin_temp_arr_final[$res_fin_temp_arr['campaignid']] = $res_fin_temp_arr;
				}

				$res_fin_temp_arr_final_json= json_encode($res_fin_temp_arr_final);

				$sql_fin_temp         = "delete from tbl_companymaster_finance_temp where parentid='".$this->parentid."'";
				$res_fin_temp        	= parent::execQuery($sql_fin_temp,$this->temp_fin);

						
			if(DEBUG_MODE)
			{
				echo '<br><b>sel_fin_temp'.$sql_fin_temp;							
				echo '<br><b>Error:</b>'.mysql_error();			
			}
			}

			$sql_tbl_catspon_temp_temp         = "delete  from tbl_catspon_temp  where parentid='".$this->parentid."'";
			parent::execQuery($sql_tbl_catspon_temp_temp,$this->tempconn);

			$sql_tbl_comp_banner_temp_temp         = "delete  from tbl_comp_banner_temp  where parentid='".$this->parentid."'";
			parent::execQuery($sql_tbl_comp_banner_temp_temp,$this->tempconn);


			
			$update_sql= " INSERT INTO tbl_campaign_reset set
			parentid	='".$this->parentid."',			
			version		=".$Version.",
			module		='".$this->module."',
			biddingtbl	='".$restbl_bidding_details_summary_array_json."',
			fintbl		='".$res_fin_temp_arr_final_json."',
			updatedby		='".addslashes($this->usercode)."',
			username		='".addslashes($this->username)."',
			updatedon		='".date('Y-m-d H:i:s')."'";
			 parent::execQuery($update_sql,$this->dbConbudget);

			if(DEBUG_MODE)
			{
				echo '<br><b>update_sql'.$update_sql;							
				echo '<br><b>Error:</b><br>'.$this->mysql_error;			
			}


		
					$array['error_code']=0;
					$array['message']='Sucess';
					return $array;	
		}

		
	}
}



?>
