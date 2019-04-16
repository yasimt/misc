<?php

class budgetinitecsclass extends DB
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

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
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

		$this->companyClass_obj  = new companyClass();

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

		//echo "<pre>";print_r($this->fin);
		//echo "<pre>";print_r($this->db_budgeting);
		
	}

	function checkversionExist()
	{
		$flag= 0; // 0- not exist, 1- exist in tbl_bidding_details_summary
		$summary_sql="select Parentid from tbl_bidding_details_summary where parentid='".$this->parentid."' and version='".$this->version."'";
		$res_summary 	= parent::execQuery($summary_sql,$this->db_budgeting);

		$flag		= mysql_num_rows($res_summary);
		return $flag;
		
	}


	function updateUpdatedby()
	{
		$update_sql="UPDATE tbl_bidding_details_summary SET
							updatedon		='".date('Y-m-d H:i:s')."',							
							username		='ECS Request',
							updatedby		='ECS Request'
							where parentid='".$this->parentid."' and version='".$this->version."'";		
		$res_summary 	= parent::execQuery($update_sql,$this->db_budgeting);
	}
	
	function updatesummarytableNew($existing_version)
	{
		$flag = $this->checkversionExist();
		
		if($flag==1)
		{
			$returnarr['error']['code'] = 1;
			$returnarr['error']['msg'] = "Prentid :".$this->parentid." present in tbl_bidding_details_summary for version :".$this->version;
			//$this->updateUpdatedby();
			return $returnarr;
		}
		
		$flag = $this->updatedataNew($existing_version);
		
		switch($flag)
		{
			case 2:
			$returnarr['error']['code'] = 2;
			$returnarr['error']['msg']  = "Prentid not present in tbl_bidding_details_summary for ".$existing_version."";
			return $returnarr;
			break;
			case 3:
			$returnarr['error']['code'] = 3;
			$returnarr['error']['msg']  = "Prentid not present tbl_companymaster_generalinfo or  tbl_id_generator";
			return $returnarr;
			break;
			case 4:
			$returnarr['error']['code'] = 4;
			$returnarr['error']['msg']  = "Data not present in payment_apportioning";
			return $returnarr;
			break;
			case 10:
			$returnarr['error']['code'] = 0;
			$returnarr['error']['msg']  = "Data sucessfully inserted in tbl_bidding_details_summary";
			return $returnarr;
			break;
		}
	}
	
	function updatedataNew($existing_version)
	{
		
		$flag=2;
		
		$summary_sql="select * from tbl_bidding_details_summary where parentid='".$this->parentid."' AND version='".$existing_version."'";
		$res_summary 	= parent::execQuery($summary_sql,$this->db_budgeting);
		if($this->trace)
		{
			echo '<br> sql :: '.$summary_sql;
			echo '<br> row :: '.mysql_num_rows($res_summary);
		}
		
		if($res_summary && mysql_num_rows($res_summary))
		{
			$row_summary   								  =  mysql_fetch_assoc($res_summary);
			$category_list 								  =  $row_summary['category_list'];
			$pincode_list  								  =  $row_summary['pincode_list'];
			$payment_apportioning['duration']			  =  $row_summary['duration'];
			$payment_apportioning['sys_fp_budget']		  =  $row_summary['sys_fp_budget'];
			$payment_apportioning['sys_package_budget']   =  $row_summary['sys_package_budget'];
			$payment_apportioning['sys_regfee_budget']    =  $row_summary['sys_regfee_budget'];
			$payment_apportioning['sys_total_budget'] 	  =  $row_summary['sys_total_budget'];
			$payment_apportioning['actual_fp_budget'] 	  =  $row_summary['actual_fp_budget'];
			$payment_apportioning['actual_package_budget']=  $row_summary['actual_package_budget'];
			$payment_apportioning['actual_regfee_budget'] =  $row_summary['actual_regfee_budget'];
			$payment_apportioning['actual_total_budget']  =  $row_summary['actual_total_budget'];
			$payment_apportioning['readjust_actfp_budget']=  $row_summary['readjust_actfp_budget'];
			$payment_apportioning['readjust_actpackage_budget'] =  $row_summary['readjust_actpackage_budget'];
			$payment_apportioning['readjust_actregfee_budget']  =  $row_summary['readjust_actregfee_budget'];
			$payment_apportioning['readjust_total_budget']  	=  $row_summary['readjust_total_budget'];
			$payment_apportioning['readjust_duration']  		=  $row_summary['readjust_duration'];
			$payment_apportioning['status']				=1;
			
			$flag		   =  0;
		}
		else
		{
			
			$sqlbidding_details = "select group_concat(distinct(pincode)) as pincodelist,group_concat(distinct(catid)) as catidlist,count(parentid) as numrows  from tbl_bidding_details where parentid='".$this->parentid."' AND version='".$existing_version."'";
			$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->fin);
			if($res_bidding_details)
			{			
			  $row = mysql_fetch_assoc($res_bidding_details);
			  	if($this->trace)
				{
					echo '<br> sql :: '.$sqlbidding_details;
					echo '<br> row :: '.$row['numrows'];
				}
				
			  if($row['numrows']>0)
			  {
					$pincode_list	=$row['pincodelist'];
					$category_list	=$row['catidlist'];
					$flag=0;
			   }
			   else
			   {
				    $sqlbidding_details = "select group_concat(distinct(pincode)) as pincodelist,group_concat(distinct(catid)) as catidlist,count(parentid) as numrows  from tbl_bidding_details_expired where parentid='".$this->parentid."' AND version='".$existing_version."'";
					$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->fin);
					
					if($res_bidding_details)
					{			
					  $row = mysql_fetch_assoc($res_bidding_details);
					  if($this->trace)
						{
							echo '<br> sql :: '.$sqlbidding_details;
							echo '<br> row :: '.$row['numrows'];
						}
					  if($row['numrows']>0)
					  {
							$pincode_list	=$row['pincodelist'];
							$category_list	=$row['catidlist'];
							$flag=0;
					   }
					   else
					   {
						    $sqlbidding_details = "select group_concat(distinct(pincode)) as pincodelist,group_concat(distinct(catid)) as catidlist,count(parentid) as numrows  from tbl_bidding_details_shadow where parentid='".$this->parentid."' AND version='".$existing_version."'";
							$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->db_budgeting);
							
							if($res_bidding_details)
							{			
								$row = mysql_fetch_assoc($res_bidding_details);
								if($this->trace)
								{
									echo '<br> sql :: '.$sqlbidding_details;
									echo '<br> row :: '.$row['numrows'];
								}
								if($row['numrows']>0)
								{
									$pincode_list	=$row['pincodelist'];
									$category_list	=$row['catidlist'];
									$flag=0;
								} else {
									$sqlbidding_details = "SELECT GROUP_CONCAT(distinct(pincode)) AS pincodelist,GROUP_CONCAT(distinct(catid)) AS catidlist,COUNT(parentid) AS numrows  FROM tbl_bidding_details_shadow_archive WHERE parentid='".$this->parentid."' AND version='".$existing_version."'";
									$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->db_budgeting);
									if($res_bidding_details)
									{
										$row = mysql_fetch_assoc($res_bidding_details);
										if($this->trace)
										{
											echo '<br> sql :: '.$sqlbidding_details;
											echo '<br> row :: '.$row['numrows'];
										}
										if($row['numrows']>0)
										{
											$pincode_list	=$row['pincodelist'];
											$category_list	=$row['catidlist'];
											$flag=0;
										} else {
											  $sqlbidding_details = "select GROUP_CONCAT(distinct(pincode)) AS pincodelist,GROUP_CONCAT(distinct(catid)) AS catidlist,COUNT(parentid) AS numrows FROM tbl_bidding_details_shadow_archive_historical where parentid='".$this->parentid."' AND version='".$existing_version."'";
											  $res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->db_budgeting);
											  if($res_bidding_details)
											  {
												  $row = mysql_fetch_assoc($res_bidding_details);
												  	if($this->trace)
													{
														echo '<br> sql :: '.$sqlbidding_details;
														echo '<br> row :: '.$row['numrows'];
													}
												  if($row['numrows']>0)
												  {
														$pincode_list	=$row['pincodelist'];
														$category_list	=$row['catidlist'];
														$flag=0;
												  } 
											  }										
										}
									}
								}
							}
					    }
					}
			    }
		   }
		   
		   
		   $payment_apportioning = $this->getpayment_apportioningData();//check apportioning if no entry found in summary
		   
		   
		}
		if( $flag == 2 )
		{
			$flag=2;
			return $flag;
		}
		
		$contractdetails = $this->getcontractdetails('active');
		
		if($contractdetails['sphinx_id']==0 || $contractdetails['status']==0)
		{
			$flag=3;
			return $flag;
		}
		
		switch($this->versionmod)
		{
			case 1: $module='cs'; break;
			case 5: $module='cs'; break;
			case 2: $module='tme'; break;
			case 3: $module='me'; break;
		}	
		

		
		if($payment_apportioning['status']==0)
		{
			$flag=4;
			return $flag;
		}
		
		
		$user_name = ($this->params['source'] == 'dealclose_daemon' && $this->params['username'] ) ? $this->params['username'] : 'ECS Request' ;
		$user_code = ($this->params['source'] == 'dealclose_daemon' && $this->params['usercode'] ) ? $this->params['usercode'] : 'ECS Request' ;
		$update_sql= " INSERT INTO tbl_bidding_details_summary set
					sphinx_id		='".$contractdetails['sphinx_id']."',
					parentid		='".$this->parentid."',
					docid			='".$contractdetails['docid']."',
					data_city		='".$contractdetails['data_city']."',
					pincode			='".$contractdetails['pincode']."',
					latitude		='".$contractdetails['latitude']."',
					longitude		='".$contractdetails['longitude']."',
					version			='".$this->version."',
					module			='".$module."',
					duration		='".$payment_apportioning['duration']."',
					sys_fp_budget	='".$payment_apportioning['sys_fp_budget']."',
					sys_package_budget ='".$payment_apportioning['sys_package_budget']."',
					sys_regfee_budget ='".$payment_apportioning['sys_regfee_budget']."',
					sys_total_budget ='".$payment_apportioning['sys_total_budget']."',
					actual_fp_budget ='".$payment_apportioning['actual_fp_budget']."',
					actual_package_budget ='".$payment_apportioning['actual_package_budget']."',
					actual_regfee_budget ='".$payment_apportioning['actual_regfee_budget']."',
					actual_total_budget ='".$payment_apportioning['actual_total_budget']."',
					readjust_actfp_budget ='".$payment_apportioning['readjust_actfp_budget']."',
					readjust_actpackage_budget ='".$payment_apportioning['readjust_actpackage_budget']."',
					readjust_actregfee_budget ='".$payment_apportioning['readjust_actregfee_budget']."',
					readjust_total_budget ='".$payment_apportioning['readjust_total_budget']."',
					readjust_duration ='".$payment_apportioning['readjust_duration']."',
					contact_details	='".$contractdetails['contact_details']."',
					category_list	='".$category_list."',
					pincode_list	='".$pincode_list."',
					updatedon		='".date('Y-m-d H:i:s')."',
					dealclosed_flag	=1,
					dealclosed_on	='".$payment_apportioning['entry_date']."',
					username		='".$user_name."',
					updatedby		='".$user_code."'";

			$res_update_sql = parent::execQuery($update_sql, $this->db_budgeting);
			
			if($this->trace)
			{
				echo '<br> sql :: '.$update_sql;
				echo '<br> res :: '.$res_update_sql;
			}
			
			$flag=10;
			return $flag;
					
		
		
	}
	
	function updatesummarytable()
	{
		
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
	}

	function getcontractdetails($status) //$server- 171, 233
	{
		$contractdetails= array();
		$contractdetails['status']=0 ;// 0 -fail,1-pass
		//$id_generator_sql 	= "select sphinx_id,docid from tbl_id_generator where parentid ='".$this->parentid."'";
		//$id_generator_res 	= parent::execQuery($id_generator_sql, $this->iro);
		
		//$id_generator_arr 	= mysql_fetch_assoc($id_generator_res);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'id_gen_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'sphinx_id,docid';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'budgetinitecsclass';
		
		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['id_gen_id']=='1')
		{
		   	$id_generator_arr 	= $comp_api_arr['results']['data'][$this->parentid];
			$contractdetails['sphinx_id']	= $id_generator_arr['sphinx_id'];
			$contractdetails['docid']		= $id_generator_arr['docid'];
		}
		
		$gi_sql = "select parentid,data_city,pincode,latitude,longitude,landline,mobile from tbl_companymaster_generalinfo where parentid ='".$this->parentid."'";
		if($status=='active' || $this->versionmod==1 )
		{
			//$gi_res = parent::execQuery($gi_sql, $this->iro);
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'parentid,data_city,pincode,latitude,longitude,landline,mobile';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'budgetinitecsclass';
			
			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
			{
				$gi_arr 	= $comp_api_arr['results']['data'][$this->parentid];			
				$contractdetails['parentid']	= $gi_arr['parentid'];
				$contractdetails['data_city']	= $gi_arr['data_city'];
				$contractdetails['pincode']		= $gi_arr['pincode'];
				$contractdetails['latitude']	= $gi_arr['latitude'];
				$contractdetails['longitude']	= $gi_arr['longitude'];		

				$contact_details = $gi_arr['landline'].",".$gi_arr['mobile'];
				$contact_details_array = explode(',',$contact_details);
				$contact_details_array = array_filter($contact_details_array);

				$contact_details_str='';

				if(count($contact_details_array))
				{
					$contractdetails['contact_details'] = implode(',',$contact_details_array);
					 
				}
				$contractdetails['status']=1;
			}
			
		}elseif($status=='pending')
		{
			$gi_res = parent::execQuery($gi_sql, $this->idc);
			if(mysql_num_rows($gi_res))
			{
				$gi_arr = mysql_fetch_assoc($gi_res);
				
				$contractdetails['parentid']	= $gi_arr['parentid'];
				$contractdetails['data_city']	= $gi_arr['data_city'];
				$contractdetails['pincode']		= $gi_arr['pincode'];
				$contractdetails['latitude']	= $gi_arr['latitude'];
				$contractdetails['longitude']	= $gi_arr['longitude'];		

				$contact_details = $gi_arr['landline'].",".$gi_arr['mobile'];
				$contact_details_array = explode(',',$contact_details);
				$contact_details_array = array_filter($contact_details_array);

				$contact_details_str='';

				if(count($contact_details_array))
				{
					$contractdetails['contact_details'] = implode(',',$contact_details_array);
					 
				}
				$contractdetails['status']=1;

			}
		}

		
		return $contractdetails;
	}

	
	
	
	function updatedata()
	{		
		
		$contractdetails= array();
		$summary_arr= array();
		$flag=2;	// 2 - data not in tbl_bidding_details and tbl_bidding_details_shadow,3 not in id_generator ,4- data not is payment_apportioning,10 = sucessfull
		
		$sqlbidding_details = "select group_concat(distinct(pincode)) as pincodelist,group_concat(distinct(catid)) as catidlist,count(parentid) as numrows  from tbl_bidding_details where parentid='".$this->parentid."'";
		$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->fin);
		if($res_bidding_details)
		{			
			$row = mysql_fetch_assoc($res_bidding_details);
			if($row['numrows']>0)
			{
				$summary_arr['pincodelist']	=$row['pincodelist'];
				$summary_arr['catidlist']	=$row['catidlist'];
				$contractdetails = $this->getcontractdetails('active');
				$flag=0;
			}
		}
		
		if($flag==2)
		{
			$sqlbidding_details = "select group_concat(distinct(pincode)) as pincodelist,group_concat(distinct(catid)) as catidlist,count(parentid) as numrows  from tbl_bidding_details_shadow where parentid='".$this->parentid."' AND version='".$this->version."' ";
			$res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->db_budgeting);
			if($res_bidding_details)
			{			
				$row = mysql_fetch_assoc($res_bidding_details);
				if($row['numrows']>0)
				{
					$summary_arr['pincodelist']	=$row['pincodelist'];
					$summary_arr['catidlist']	=$row['catidlist'];
					$contractdetails = $this->getcontractdetails('pending');
					$flag=0;
				} else {
                    $sqlbidding_details = "SELECT GROUP_CONCAT(distinct(pincode)) AS pincodelist,GROUP_CONCAT(distinct(catid)) AS catidlist,COUNT(parentid) AS numrows  FROM tbl_bidding_details_shadow_archive WHERE parentid='".$this->parentid."' AND version='".$this->version."' ";
                    $res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->db_budgeting);
                    if($res_bidding_details)
                    {
                        $row = mysql_fetch_assoc($res_bidding_details);
                        if($row['numrows']>0)
                        {
                            $summary_arr['pincodelist']	=$row['pincodelist'];
                            $summary_arr['catidlist']	=$row['catidlist'];
                            $contractdetails = $this->getcontractdetails('pending');
                           $flag=0;
                        } else {
                              $sqlbidding_details = "select GROUP_CONCAT(distinct(pincode)) AS pincodelist,GROUP_CONCAT(distinct(catid)) AS catidlist,COUNT(parentid) AS numrows FROM tbl_bidding_details_expired where parentid='".$this->parentid."' AND version='".$this->version."' ";
                              $res_bidding_details 	= parent::execQuery($sqlbidding_details, $this->fin);
                              if($res_bidding_details)
                              {
                                  $row = mysql_fetch_assoc($res_bidding_details);
                                  if($row['numrows']>0)
                                  {
                                      $summary_arr['pincodelist']	=$row['pincodelist'];
                                      $summary_arr['catidlist']	=$row['catidlist'];
                                      $contractdetails = $this->getcontractdetails('pending');
                                      $flag=0;
                                  } 
							  }
						
                        }
                    }
					  
				
				}
			}
		}

		if($flag==2)
		{
			return $flag;
		}
		
		if($contractdetails['sphinx_id']==0 || $contractdetails['status']==0)
		{
			$flag=3;
			return $flag;
		}
		
		
		{			
			switch($this->versionmod)
			{
				case 1: $module='cs'; break;
				case 5: $module='cs'; break;
				case 2: $module='tme'; break;
				case 3: $module='me'; break;
			}	

			$payment_apportioning = $this->getpayment_apportioningData();
			if($payment_apportioning['status']==0)
			{
				$flag=4;
				return $flag;
			}
			

			$resultflag = $this->checkversionExist();
			if($resultflag==1)
			{
				$flag=1;
				return $flag;
			}
			elseif($resultflag==0)
			{
				$user_name = ($this->params['source'] == 'dealclose_daemon' && $this->params['username'] ) ? $this->params['username'] : 'ECS Request' ;
				$user_code = ($this->params['source'] == 'dealclose_daemon' && $this->params['usercode'] ) ? $this->params['usercode'] : 'ECS Request' ;
				$update_sql= " INSERT INTO tbl_bidding_details_summary set
							sphinx_id		='".$contractdetails['sphinx_id']."',
							parentid		='".$this->parentid."',
							docid			='".$contractdetails['docid']."',
							data_city		='".$contractdetails['data_city']."',
							pincode			='".$contractdetails['pincode']."',
							latitude		='".$contractdetails['latitude']."',
							longitude		='".$contractdetails['longitude']."',
							version			='".$this->version."',
							module			='".$module."',
							duration		='".$payment_apportioning['duration']."',
							sys_fp_budget	='".$payment_apportioning['sys_fp_budget']."',
							sys_package_budget ='".$payment_apportioning['sys_package_budget']."',
							sys_regfee_budget ='".$payment_apportioning['sys_regfee_budget']."',
							sys_total_budget ='".$payment_apportioning['sys_total_budget']."',
							actual_fp_budget ='".$payment_apportioning['actual_fp_budget']."',
							actual_package_budget ='".$payment_apportioning['actual_package_budget']."',
							actual_regfee_budget ='".$payment_apportioning['actual_regfee_budget']."',
							actual_total_budget ='".$payment_apportioning['sys_total_budget']."',
							contact_details	='".$contractdetails['contact_details']."',
							category_list	='".$summary_arr['catidlist']."',
							pincode_list	='".$summary_arr['pincodelist']."',
							updatedon		='".date('Y-m-d H:i:s')."',
							dealclosed_flag	=1,
							dealclosed_on	='".$payment_apportioning['entry_date']."',
							username		='".$user_name."',
							updatedby		='".$user_code."'";

					parent::execQuery($update_sql, $this->db_budgeting);
					$flag=10;
					return $flag;
			}	
					
		}
		
	}

	function getpayment_apportioningData()
	{
		$payment_apportioning_array = array();
		$payment_apportioning_array['duration']= 0;
		$payment_apportioning_array['entry_date']= null;
		
		$payment_apportioning_array['sys_total_budget']= 0;
		$payment_apportioning_array['status']=0; //0 - fail, 1- pass
		
		/*if ($this->params['source'] == 'dealclose_daemon') 
		{
			$sqlfin_version = "select version from tbl_companymaster_finance where parentid='".$this->parentid."' and  campaignid in (1,2) limit 1";
			$resfin_version = parent::execQuery($sqlfin_version, $this->fin);
			if(mysql_num_rows($resfin_version))
			{
				$rowfin_version= mysql_fetch_assoc($resfin_version);
			}
		}*/
		
		$version = $this->version;
		
		$sqlpayment_apportioning = "select campaignId,budget,duration,entry_date from payment_apportioning where parentid='".$this->parentid."' and version='".$version."' and campaignid in (1,2,7)";
		$res_payment_apportioning 	= parent::execQuery($sqlpayment_apportioning, $this->fin);		
		if(mysql_num_rows($res_payment_apportioning))
		{
			while($arr_payment_apportioning= mysql_fetch_assoc($res_payment_apportioning))
			{
				if($arr_payment_apportioning['duration']>$payment_apportioning_array['duration'])
				{
					$payment_apportioning_array['duration'] = $arr_payment_apportioning['duration'];
				}
				
				if(intval($arr_payment_apportioning['budget'])>0 && intval($arr_payment_apportioning['campaignId'])==2)
				{
					$payment_apportioning_array['sys_fp_budget'] = $payment_apportioning_array['actual_fp_budget']=intval($arr_payment_apportioning['budget']);
					$payment_apportioning_array['sys_total_budget'] =$payment_apportioning_array['sys_total_budget']+intval($arr_payment_apportioning['budget']);
					$payment_apportioning_array['entry_date']=$arr_payment_apportioning['entry_date'] ;
				}

				if(intval($arr_payment_apportioning['budget'])>0 && intval($arr_payment_apportioning['campaignId'])==1)
				{
					$payment_apportioning_array['sys_package_budget'] = $payment_apportioning_array['actual_package_budget']=intval($arr_payment_apportioning['budget']);
					$payment_apportioning_array['sys_total_budget'] =$payment_apportioning_array['sys_total_budget']+intval($arr_payment_apportioning['budget']);
					$payment_apportioning_array['entry_date']=$arr_payment_apportioning['entry_date'] ;
				}
				if(intval($arr_payment_apportioning['budget'])>0 && intval($arr_payment_apportioning['campaignId'])==7)
				{
					$payment_apportioning_array['sys_regfee_budget'] = $payment_apportioning_array['actual_regfee_budget']=intval($arr_payment_apportioning['budget']);
					//$payment_apportioning_array['sys_total_budget'] =$payment_apportioning_array['sys_total_budget']+intval($arr_payment_apportioning['budget']);
					$payment_apportioning_array['entry_date']=		$arr_payment_apportioning['entry_date'] ;
				}				
			$payment_apportioning_array['status']=1;
			}
			
					$payment_apportioning_array['actual_total_budget'] = $payment_apportioning_array['sys_total_budget'];
		}

		return $payment_apportioning_array;
	}



}



?>
