<?php

class caryExstngBddngData_class extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	

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
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['existCarryVersion']) != "" && $this->params['existCarryVersion'] != null)
		{
			$this->existing_version  = $this->params['existCarryVersion']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='existCarryVersion missing';
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
			$this->username  = urldecode($this->params['username']); //initialize usercode
		}
		

		if(trim($this->params['version']) != "" && $this->params['version'] != null)
		{
			$this->version  = $this->params['version']; //initialize version			
			$this->versionmod = $this->version%10;

			if($this->version==5)
			{
				// do nothing since it is old ported contract 
			}elseif(!in_array($this->versionmod,array(1,2,3)) || $this->version<11 )
			{
				$errorarray['errormsg']='Invalid version ,it should end with 1,2 or 3 only';
				echo json_encode($errorarray); exit;
			}
			
			
			
		}else
		{
			$errorarray['errormsg']='version missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		$params['source'] = 'dealclose_daemon';
		
		$this-> budgetinitecsclass_obj = new budgetinitecsclass($params);

		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
			
		$this->fin   			= $db[$data_city]['fin']['master'];		
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		$this->iro			   	= $db[$data_city]['iro']['master'];
		$this->idc   			= $db[$data_city]['idc']['master'];
		
		$this->jdbox_url = constant(strtoupper($data_city).'_CS_JDBOX_URL');
		
		//echo "<pre>";print_r($this->fin);
		//echo "<pre>";print_r($this->db_budgeting);
		
	}
	
	function caryExstngBddngData()
	{
		
		$sql_chk_exst_shd = "SELECT version FROM tbl_bidding_details_shadow WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_chk_exst_shd = parent::execQuery($sql_chk_exst_shd, $this->db_budgeting);
		
		if($this->trace)
		{
			echo '<br> sql :: '.$sql_chk_exst_shd;
			echo '<br> row :: '.mysql_num_rows($res_chk_exst_shd);
		}
		
		$sql_chk_exst = "SELECT version FROM tbl_bidding_details WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
		$res_chk_exst = parent::execQuery($sql_chk_exst, $this->fin);
		
		if($this->trace)
		{
			echo '<br> sql :: '.$sql_chk_exst;
			echo '<br> row :: '.mysql_num_rows($res_chk_exst);
		}
		
		$sql_chk_phonesearch_camp = "SELECT * FROM payment_apportioning 
									 WHERE parentid='".$this->parentid."' 
									 AND version='".$this->version."'
									 AND campaignid IN (1,2)
									 AND budget > 0";
									 
		$res_chk_phonesearch_camp = parent::execQuery($sql_chk_phonesearch_camp, $this->fin);
		
		if($this->trace)
		{
			echo '<br> sql :: '.$sql_chk_phonesearch_camp;
			echo '<br> row :: '.mysql_num_rows($res_chk_phonesearch_camp);
		}
		
		if( $res_chk_phonesearch_camp && mysql_num_rows($res_chk_phonesearch_camp) > 0 )
		{
			if( mysql_num_rows($res_chk_exst_shd) <= 0 && mysql_num_rows($res_chk_exst) <= 0  )
			{
				$sql_chk_exst_summ_entr = "SELECT version FROM tbl_bidding_details_summary WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
				$res_chk_exst_summ_entr = parent::execQuery($sql_chk_exst_summ_entr, $this->db_budgeting);
				
				if($this->trace)
				{
					echo '<br> sql :: '.$sql_chk_exst_summ_entr;
					echo '<br> row :: '.mysql_num_rows($res_chk_exst_summ_entr);
				}
				
				if( $res_chk_exst_summ_entr && mysql_num_rows($res_chk_exst_summ_entr) > 0 )
				{
					$sql_DEL_exst_summ_entr = "DELETE FROM tbl_bidding_details_summary WHERE parentid='".$this->parentid."' AND version='".$this->version."'";
					$res_DEL_exst_summ_entr = parent::execQuery($sql_DEL_exst_summ_entr, $this->db_budgeting);
					if($this->trace)
					{
						echo '<br> sql :: '.$sql_DEL_exst_summ_entr;
						echo '<br> row :: '.mysql_num_rows($res_DEL_exst_summ_entr);
					}
				}
				
				$summary_resp_arr = $this-> budgetinitecsclass_obj -> updatesummarytableNew($this->existing_version);	
				
				if($this->trace)
				{
					echo '<br>';
					echo 'summary data <pre>';
					print_r($summary_resp_arr);
				}
				if( count($summary_resp_arr) >0 && $summary_resp_arr['error']['code'] == 0 && stristr(strtolower(trim($summary_resp_arr['error']['msg'])), 'data sucessfully') )
				{
					/*$sql = "SELECT version FROM tbl_bidding_details WHERE parentid='".$this->parentid."' limit 1";
					$res = parent::execQuery($sql, $this->fin);
					if($this->trace)
					{
						echo '<br> sql :: '.$sql;
						echo '<br> row :: '.mysql_num_rows($res);
					}*/
					
					$sql_chk_activity_data = "SELECT * FROM tbl_carryfrwdversion_bd_populate WHERE parentid ='".$this->parentid."' AND carryfwdversion='".$this->existing_version."' AND new_version='".$this->version."' AND bd_entryflag > 0";
					$res_chk_activity_data = parent::execQuery($sql_chk_activity_data, $this->db_budgeting);
						if($this->trace)
						{
							echo '<br> sql :: '.$sql_chk_activity_data;
							echo '<br> row :: '.mysql_num_rows($res_chk_activity_data);
						}
					if( $res_chk_activity_data && mysql_num_rows($res_chk_activity_data) > 0 )
					{
						$entry_in_shadow_api_url = "http://".$this->jdbox_url."services/invMgmt.php?parentid=".$this->parentid."&version=".$this->version."&astatus=1&astate=8&data_city=".$this->data_city."";
						$entry_in_shadow_api_res     = $this -> curl_call_get($entry_in_shadow_api_url);
						$entry_in_shadow_api_res_arr = json_decode($entry_in_shadow_api_res, true);
						if($this->trace)
						{
							echo '<br> sql :: '.$entry_in_shadow_api_url;
							echo '<pre> res arr :: '.print_r($entry_in_shadow_api_res_arr);
						}
						if( count($entry_in_shadow_api_res_arr) > 0 && count($entry_in_shadow_api_res_arr['results']) > 0 && $entry_in_shadow_api_res_arr['error']['code'] == 0 )
						{
								$returnarr['error']['code'] = 0;
								$returnarr['error']['msg'] =  "Data inserted sucessfully in shadow";
								return $returnarr;
						}
						else
						{
							$returnarr['error']['code'] = 1;
							$returnarr['error']['url']  = $entry_in_shadow_api_url;
							$returnarr['error']['res']  = $entry_in_shadow_api_res ;
							return $returnarr;
						}
						
					}
					
					if($this->existing_version && $this->jdbox_url)
					{								
						$entry_in_shadow_api_url = "http://".$this->jdbox_url."services/invMgmt.php?parentid=".$this->parentid."&version=".$this->existing_version."&astatus=1&astate=8&data_city=".$this->data_city."";
						$entry_in_shadow_api_res     = $this -> curl_call_get($entry_in_shadow_api_url);
						$entry_in_shadow_api_res_arr = json_decode($entry_in_shadow_api_res, true);
						if($this->trace)
						{
							echo '<br> sql :: '.$entry_in_shadow_api_url;
							echo '<pre> res arr :: '.print_r($entry_in_shadow_api_res_arr);
						}
						if( count($entry_in_shadow_api_res_arr) > 0 && count($entry_in_shadow_api_res_arr['results']) > 0 && $entry_in_shadow_api_res_arr['error']['code'] == 0 )
						{
							$sql_upt = "UPDATE tbl_bidding_details_shadow SET version='".$this->version."' WHERE parentid='".$this->parentid."' AND version='".$this->existing_version."'";
							$res_upt = parent::execQuery($sql_upt, $this->db_budgeting);
							if($this->trace)
							{
								echo '<br> sql :: '.$sql_upt;
								echo '<br> res :: '.$res_upt;
							}
							if($res_upt)
							{
								$returnarr['error']['code'] = 0;
								$returnarr['error']['msg'] =  "Data inserted sucessfully in shadow";
								return $returnarr;
								
								/*$entry_in_main_from_shadow_api_url 	   = "http://".$this->jdbox_url."services/invMgmt.php?parentid=".$this->parentid."&version=".$this->version."&astatus=2&astate=3&data_city=".$this->data_city."";
								$entry_in_main_from_shadow_api_res 	   = $this -> curl_call_get($entry_in_main_from_shadow_api_url);
								$entry_in_main_from_shadow_api_res_arr = json_decode($entry_in_main_from_shadow_api_res, true);*/
								
							}
						}
						else
						{
							$returnarr['error']['code'] = 1;
							$returnarr['error']['url']  = $entry_in_shadow_api_url;
							$returnarr['error']['res']  = $entry_in_shadow_api_res ;
							return $returnarr;
						}
						
					}
					else
					{
						$returnarr['error']['code'] = 1;
						$returnarr['error']['msg'] = "No entry found in bidding details for parentid :: ".$this->parentid;
						return $returnarr;
					}
				
					
				}
				else
				{
					$returnarr['error']['code'] = 1;
					$returnarr['error']['msg'] = "Insertion in bidding summary is failed for parentid :: ".$this->parentid." AND version :: ".$this->version;
					return $returnarr;
				}
			}
			else
			{
				$returnarr['error']['code'] = 0;
				$returnarr['error']['msg'] = "Entry already exist for parentid :: ".$this->parentid." AND version :: ".$this->version;
				return $returnarr;
			}
		}
		else
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = "Entry not found in apportioning against 1,2 campaignids for parentid :: ".$this->parentid." AND version :: ".$this->version;
			return $returnarr;
		}
		/*
		$flag = $this->checkversionExist();
		
		if($flag==1)
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = "Prentid :".$this->parentid." present in tbl_bidding_details_summary for version :".$this->version;
			$this->updateUpdatedby();
			return $returnarr;
		}
		
		$flag = $this->updatedata();
		// 2 - data not in tbl_bidding_details and tbl_bidding_details_shadow,3 not in id_generator ,4- data not is payment_apportioning,10 = sucessfull
		
		if($flag==1)
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = "Prentid :".$this->parentid." present in tbl_bidding_details_summary for version :".$this->version;
			return $returnarr;
		}
		
		if($flag==2)
		{
			$returnarr['error']['code'] = 2;
			$returnarr['error']['msg'] = "Prentid neither in tbl_bidding_details nor in tbl_bidding_details_shadow ";
			return $returnarr;
		}elseif($flag==3)
		{
			$returnarr['error']['code'] = 3;
			$returnarr['error']['msg'] = "Prentid not present tbl_companymaster_generalinfo or  tbl_id_generator";
			return $returnarr;
		}elseif($flag==4)
		{
			$returnarr['error']['code'] = 4;
			$returnarr['error']['msg'] = "Data not present in payment_apportioning";
			return $returnarr;
		}elseif($flag==10)
		{
			$returnarr['error']['code'] = 0;
			$returnarr['error']['msg'] = "Data sucessfully inserted in tbl_bidding_details_summary";
			return $returnarr;
		}	
		*/	
	}

	function curl_call_get($curl_url)
	{	
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );

		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
	}
}

?>
