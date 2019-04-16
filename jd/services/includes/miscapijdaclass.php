<?php

class miscapijdaclass extends DB
{	
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;	
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $conn_temp = null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	var  $versionInitClass_obj	= null;
	

	
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	

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

		 /*if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}*/

		$this->setServers();
		$this->setsphinxid();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
			
		$this->fin   			= $db[$data_city]['fin']['master'];		
		$this->Idc   			= $db[$data_city]['idc']['master'];
		$this->tme_jds 			= $db[$data_city]['tme_jds']['master'];
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->conn_national   	= $db['db_national'];

		if($this->module=='tme')
		{
			$this->conn_temp =$this->tme_jds;
		}elseif($this->module=='me')
		{
			$this->conn_temp =$this->Idc;
		}

	}

	function setsphinxid()
	{
		$idgenerator_sphinxid_query = " SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '" . $this->parentid . "' ";
		$sphinxid_res = parent::execQuery($idgenerator_sphinxid_query,$this->dbConIro);

		$sphinxid_rows = mysql_num_rows($sphinxid_res);

		if($sphinxid_rows)
		{
		
			$sphinxid_values       = mysql_fetch_assoc($sphinxid_res);
			$this->sphinx_id      = $sphinxid_values['sphinx_id'];
		}
	}
	
	function updatefinancetempTable($campaignlist=null)
	{
		$this->versionInitClass_obj = new versionInitClass($this->params);
		$this->getCompanyFinanceData($campaignlist);
		
		
	}

	function getCompanyFinanceData($campaignlist=null)
	{
		$campaignlist_condtn='';
		if(trim($campaignlist)!=null)
		{
			$campaignlist_condtn = " AND campaignid in (".$campaignlist.")";
		}
		
		$shadow_num = 0;
        if (strtolower($this->module) == 'me' || strtolower($this->module) == 'tme') {

            $tbl_company_finance_temp_delete = "DELETE FROM tbl_companymaster_finance_temp WHERE sphinx_id = " . $this->sphinx_id . " ".$campaignlist_condtn;
            
            parent::execQuery($tbl_company_finance_temp_delete, $this->conn_temp);
            
            $latest_version = $this->versionInitClass_obj->fetchVersion();
            

            if ($latest_version) {

               // $company_finance_shadow_sql  = "SELECT * FROM tbl_companymaster_finance_shadow WHERE sphinx_id = " . $this->sphinx_id . " AND version = '" . $latest_version . "' AND campaignid NOT IN (22,23)".$campaignlist_condtn;
               
              $company_finance_shadow_sql  = "SELECT * FROM tbl_companymaster_finance_shadow a JOIN	payment_instrument_summary b ON a.parentid=b.parentid AND a.version = b.version WHERE a.sphinx_id='".$this->sphinx_id."' AND a.parentid='".$this->parentid."' AND a.version ='".$latest_version."' AND b.approvalstatus=0 AND a.campaignid NOT IN (22,23) ".$campaignlist_condtn;
               // die;
                
                //$company_finance_shadow_res  = $this->conn_finance->query_sql($company_finance_shadow_sql);
                $company_finance_shadow_res  = parent::execQuery($company_finance_shadow_sql, $this->fin);
                $company_finance_shadow_rows = mysql_num_rows ($company_finance_shadow_res);

                if ($company_finance_shadow_rows==0) {

                    unset($company_finance_shadow_res);

                   //$company_finance_shadow_sql  = "SELECT * FROM tbl_companymaster_finance WHERE sphinx_id = " . $this->sphinx_id . " AND version = '" . $latest_version . "' ";
                    $company_finance_shadow_sql  = "SELECT * FROM tbl_companymaster_finance WHERE sphinx_id = " . $this->sphinx_id . " AND active_campaign =1 AND campaignid NOT IN (22,23)".$campaignlist_condtn;
                    //$company_finance_shadow_res  = $this->conn_finance->query_sql($company_finance_shadow_sql);
                    $company_finance_shadow_res  = parent::execQuery($company_finance_shadow_sql, $this->fin);
                    $company_finance_shadow_rows = mysql_num_rows ($company_finance_shadow_res);
                    if($company_finance_shadow_rows==0){
                        $company_finance_shadow_sql  = "SELECT * FROM tbl_companymaster_finance WHERE sphinx_id = " . $this->sphinx_id . " AND campaignid NOT IN (22,23)".$campaignlist_condtn;
                        //$company_finance_shadow_res  = $this->conn_finance->query_sql($company_finance_shadow_sql);
                        $company_finance_shadow_res  = parent::execQuery($company_finance_shadow_sql, $this->fin);
                        $company_finance_shadow_rows = mysql_num_rows ($company_finance_shadow_res);
                    }

                } else {

					$shadow_num = 1;
                    $company_finance_enddate  = "SELECT campaignid,start_date,end_date FROM tbl_companymaster_finance WHERE sphinx_id = " . $this->sphinx_id . " ".$campaignlist_condtn;
                    //$company_finance_enddate_res  = $this->conn_finance->query_sql($company_finance_enddate);
                    $company_finance_enddate_res  = parent::execQuery($company_finance_enddate, $this->fin);
                    $company_finance_enddate_rows = mysql_num_rows ($company_finance_enddate_res);

					if ($company_finance_enddate_rows) {

						while ($edate_values = mysql_fetch_array($company_finance_enddate_res)){

							$camp_sdate_array[$edate_values['campaignid']] = $edate_values['start_date'];
							$camp_edate_array[$edate_values['campaignid']] = $edate_values['end_date'];

						}

					}

					//National Listing handling
                    $company_fin_national_enddate  = "SELECT campaignid,start_date,end_date FROM tbl_companymaster_finance_national WHERE sphinx_id = " . $this->sphinx_id . "  AND parentid = '" . $this->parentid . "' ".$campaignlist_condtn;
                    //$company_fin_national_res  = $this->conn_national->query_sql($company_fin_national_enddate);
                    $company_fin_national_res  = parent::execQuery($company_fin_national_enddate, $this->conn_national);
                    $company_fin_national_rows = mysql_num_rows ($company_fin_national_res);

					if ($company_fin_national_rows) {
						$edate_national_values = mysql_fetch_array($company_fin_national_res); 
						$camp_sdate_array[$edate_national_values['campaignid']] = $edate_national_values['start_date'];
						$camp_edate_array[$edate_national_values['campaignid']] = $edate_national_values['end_date'];

					}


				}
                $temp_camp_values = '';
				if ($company_finance_shadow_rows) {

					while ($temp_shadow_values = mysql_fetch_array($company_finance_shadow_res)) {
					    $temp_camp_values[$temp_shadow_values['campaignid']] = $temp_shadow_values;
					}
				}
				//National Listing handling
				if (!$shadow_num) {

						$company_fin_national_sql  = "SELECT * FROM tbl_companymaster_finance_national WHERE sphinx_id = " . $this->sphinx_id . " AND parentid = '" . $this->parentid . "' AND active_campaign =1".$campaignlist_condtn;
						//$company_fin_national_res  = $this->conn_national->query_sql($company_fin_national_sql);
						$company_fin_national_res  = parent::execQuery($company_fin_national_sql, $this->conn_national);
						$company_fin_national_rows = mysql_num_rows ($company_fin_national_res);

					if ($company_fin_national_rows) {
						$national_values = mysql_fetch_array($company_fin_national_res);
						$temp_camp_values[$national_values['campaignid']] = $national_values;
					}elseif($company_fin_national_rows==0){
                        $company_fin_national_sql  = "SELECT * FROM tbl_companymaster_finance_national WHERE sphinx_id = " . $this->sphinx_id . " AND parentid = '" . $this->parentid . "' ".$campaignlist_condtn;
						//$company_fin_national_res  = $this->conn_national->query_sql($company_fin_national_sql);
						$company_fin_national_res  = parent::execQuery($company_fin_national_sql, $this->conn_national);
						$company_fin_national_rows = mysql_num_rows ($company_fin_national_res);
                        if ($company_fin_national_rows) {
                            $national_values = mysql_fetch_array($company_fin_national_res);
                            $temp_camp_values[$national_values['campaignid']] = $national_values;
					    }
                    }

				}


                if (is_array($temp_camp_values) && count($temp_camp_values)>0)
                {

                   $campaign_balance = $this->initialBalanceLog($this->parentid,0,0);

                    //while ($shadow_values = mysql_fetch_array($company_finance_shadow_res))
					foreach($temp_camp_values as $camp_key =>$shadow_values)
					{

						$camp_start_date ='';
						$camp_end_date = '';
						$camp_start_date = (is_array($camp_sdate_array) ? $camp_sdate_array[$shadow_values['campaignid']]:$shadow_values['start_date']);
						$camp_end_date = (is_array($camp_edate_array) ? $camp_edate_array[$shadow_values['campaignid']]:$shadow_values['end_date']);

                        $compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                                        nationalid = '".$shadow_values['nationalid']."',
                                                        sphinx_id  = '".$shadow_values['sphinx_id']."',
                                                        regionid   = '".$shadow_values['regionid']."',
                                                        parentid   = '".$shadow_values['parentid']."',
                                                        campaignid  = '".$shadow_values['campaignid']."',
                                                        budget      = '".$shadow_values['budget']."',
                                                        original_actual_budget   = '".$shadow_values['budget']."',
                                                        duration    = '".$shadow_values['duration']."',
                                                        balance     = '".$campaign_balance[$shadow_values['campaignid']]."',
                                                        start_date    = '".$camp_start_date."',
														end_date    = '".$camp_end_date."',
                                                        smartlisting_flag    = '".$shadow_values['smartlisting_flag']."',
                                                        exclusivelisting_tag = '".$shadow_values['exclusivelisting_tag']."',
                                                        daily_threshold      = '".$shadow_values['daily_threshold']."'
                                                       ";
						if($shadow_num == 1)
						{
							$compmaster_fin_temp_insert =$compmaster_fin_temp_insert.", recalculate_flag = 1" ;
						}
                        //$this->conn_temp->query_sql($compmaster_fin_temp_insert);
                        parent::execQuery($compmaster_fin_temp_insert, $this->conn_temp);

                    }

                }

            }

        }
    }

    //function initialBalanceLog($parentid ,$version , $insertLogFlag , $jdrr=0)
    function initialBalanceLog($parentid ,$version , $insertLogFlag=0 , $jdrr=0)
	{
		
		$sphinx_id     = $this->sphinx_id;
		$Pparentid     = $this->parentid;

		if($jdrr==1 && $insertLogFlag==0){
			$whereclause = " AND campaignid=22";	
		} else if ($jdrr==0 && $insertLogFlag==0){
			$whereclause = " AND campaignid NOT IN (22) ";
		}

		$is_deleted_str = " ,isdeleted=".$jdrr;

		$campaign_row='';
		$qry = "SELECT campaignid,balance FROM tbl_companymaster_finance WHERE parentid = '" . $parentid . "' " . $whereclause;
		//$res = $conn_finance->query_sql($qry);
		$res = parent::execQuery($qry, $this->fin);
		while($row = mysql_fetch_assoc($res)){
			$campaignid  = $row['campaignid'];
			$campaign_row[$campaignid] = $row['balance'];
		}
		
		$qry_idc = "SELECT campaignid,balance FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid = '" . $parentid . "'";
		//$res_idc = $conn_national->query_sql($qry_idc);
		$res_idc = parent::execQuery($qry_idc, $this->conn_national);

		if (mysql_num_rows($res_idc)) {
		  $idc_row = mysql_fetch_assoc($res_idc);
		  $campaign_row[$idc_row['campaignid']] = $idc_row['balance'];
		}		

		return $campaign_row;
	}

}



?>
