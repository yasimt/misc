<?php

	require_once( APP_PATH."00_Payment_Rework/company_finance_class.php" );
	class guarantee_verified_stamp
	{
			const JD_DEFAULTSTAMP   = 0;
			const JD_GUARANTEE      = 1;
			const JD_VERIFIED	    = 5;
			const FRONT_SEPERATOR   ='/';
			const MID_SEPERATOR     ='/,/';
			const REPLACE_SEPERATOR = ',';

			private $contract_jd_level, $update_extradetails_guarantee,$display_JD_stamp,  $version;
			private $parentid, $module, $sphinx_id, $financeObj;
			private $paid,$conn_iro, $conn_local, $conn_finance, $conn_national;
			private $filename, $compmaster_obj;
			private $RestrictedCategory_flag,$RestrictedCategory_str,$PaidStatus,$NationalPaidStatus,$ucode,$gurantee,$catidlist,$restrict_flag;
				
			function __construct($mod, $pid, $dbarr, $sid,$ucod=null)
			{
				$this->initialize();
				$this->conn_iro		=	new DB($dbarr['DB_IRO']);
				$this->conn_local	=	new DB($dbarr['LOCAL']);
				$this->conn_finance	=	new DB($dbarr['FINANCE']);
				$this->conn_national= 	new DB($dbarr['DB_NATIONAL']);
				$this->parentid		=	$pid;
				$this->module		=   $mod;
				$this->sphinx_id    = 	$sid;
				$this->ucode		=	$ucod;
				$this->financeObj 	=   new company_master_finance($dbarr,$this->parentid,$this->sphinx_id);
				$this->filename		=   APP_PATH."logs/guarantee_logs/guarantee_".$this->parentid.".html";
				if(!isset($this->compmaster_obj)){
					$this->compmaster_obj = new companyMasterClass($this->conn_iro,"",$this->parentid);
				}
			}

			function initialize()
			{
				$this->parentid						 ='';
				$this->contract_jd_level			 ='';
				$this->update_extradetails_guarantee ='';
				$this->display_JD_stamp				 ='';
				$this->module						 ='';
				$this->sphinx_id					 ='';
			}


			function checkPaid()         /*  Function for checking the paid status of the contract*/
			{
				$financeArr 	= array();

				$financeArr		=	$this->financeObj->getFinanceMainData();

				if($financeArr['1']['budget'] > 0 || $financeArr['2']['budget'] > 0)
				{
					$activeFlag = max($financeArr['1']['active_flag'],$financeArr['2']['active_flag']);
					$expired    = min($financeArr['1']['expired'],$financeArr['2']['expired']);

					if($activeFlag==1 && $expired==0)
					{
						$approve_flag=1;
					}
					else
					{
						$approve_flag=0;
					}
				}
				return $approve_flag;
			}

			function getdeductionperday_pd()             /* Function for getting the deduction per day for the platinum/diamond contract*/
			{
				$datacity = 	$this -> getDataCity();
				$sql_jd_guarantee="SELECT (annualcost/365) as supremeperday FROM tbl_premium_listing_Justdialg WHERE print > 0 AND city='".$datacity."' AND jd_flag='1' ORDER BY monthlyinstallment LIMIT 1";
				$res_jd_guarantee = $this->conn_local->query_sql($sql_jd_guarantee);
				if($row_def_budget = mysql_fetch_assoc($res_jd_guarantee))
				{
					return floor($row_def_budget['supremeperday']);
				}
			}

			function getdeductionperday_pkg()               /* Function for getting the annual cost for the package contracts */
			{
				$datacity = 	$this -> getDataCity();
				$sqlAmount="SELECT annualcost FROM tbl_premium_listing_Justdialg WHERE print > 0 AND city='".$datacity."' AND jd_flag='1' ORDER BY monthlyinstallment LIMIT 1";
				$resultAmount = $this->conn_local->query_sql($sqlAmount);
				if($valueAmount = mysql_fetch_assoc($resultAmount))	{
					return $valueAmount['annualcost'];
				}
				else {
					return 1;
				}
			}

			function getExceptionFlag()                   /* Function for checking whether any stamp is given or removed from the contract forcefully */
			{

				$reasonSql	 = "select flag from tbl_jdgv_override where parentid='".$this->parentid."' order by curtime desc limit 1";
				$reasonResult=$this->conn_local->query_sql($reasonSql);
				if($reasonResult)
				{
					$reasonRow=mysql_fetch_assoc($reasonResult);
					if(mysql_num_rows($reasonResult)>0)
					{
						if($reasonRow['flag']==1)
						{
							$guaran	=	1;
						}
						else if($reasonRow['flag']==5)
						{
							$guaran =   5;
						}
						else if($reasonRow['flag']==0)
						{
							$guaran	=	0;
						}
					}
					else
					{
						$guaran	=  -1;
					}
				}
				return $guaran;
			}

			function updateJDgv_stamp()
			{
					if(strtoupper($this->module)=='CS')
					{
						$this->setJDgv_stamp();
					}
			}

		/* This function returns 1 if either landline, fax or tollfree is present */
			function landline_fax_check()
			{
					$landfaxSql		="SELECT landline,fax,tollfree FROM  tbl_companymaster_generalinfo WHERE sphinx_id = '".$this->sphinx_id."'";
					$landfaxResult	= $this->conn_iro->query_sql($landfaxSql);
					if($landfaxResult)
					{
						$landfaxRow		= mysql_fetch_assoc($landfaxResult);
						$phone_array 	= explode(',',$landfaxRow['landline']);
						$fax_array 		= explode(',',$landfaxRow['fax']);
						$tollfree_array = explode(',',$landfaxRow['tollfree']);
						$phone_tollfree = array_merge($phone_array,$tollfree_array);

						for($i=0;$i < count($phone_tollfree);$i++)
						{
							if((count($phone_tollfree) > 0 && $phone_tollfree[$i] !='') || (count($fax_array) > 0 && $fax_array[$i] !=''))
							{
								$guaran = 1;
								break;
							}
							else
							{
								$guaran = 0;
							}
						}
					}
					return $guaran;
			}

		/* This function returns 0 if the contract is eligible for guarantee/verified - means does not contain flagged categories */
			function category_check()
			{
				$cat_chk=array();

				$fieldStr	= '';
				$temparr	= array();
				$fieldstr	= "catidlineage";
				$tablename	= 'tbl_companymaster_extradetails';
				$wherecond	=	"sphinx_id='".$this->sphinx_id."'";
				$temparr  	=$this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

				if($temparr['numrows'])
				{
					$catRow = $temparr['data']['0'];
					$getcatids = trim($catRow['catidlineage'],self::FRONT_SEPERATOR);
					if(stristr($getcatids,self::MID_SEPERATOR))
					{
						$getcatids = str_replace(self::MID_SEPERATOR,self::REPLACE_SEPERATOR,$getcatids);
					}

					$sql_categories_guarantee="SELECT distinct(category_addon) as category_guarantee from tbl_categorymaster_generalinfo where catid in(".$getcatids.")";
					$res_categories_guarantee = $this->conn_local->query_sql($sql_categories_guarantee);
					while($row_categories_guarantee = mysql_fetch_assoc($res_categories_guarantee)){
						array_push($cat_chk,$row_categories_guarantee['category_guarantee']);
					}

					if(in_array(2,$cat_chk))
					{
						$this->contract_jd_level ='blacklisted';
						$guaran=0;
						$this->log_creation("Blacklisted category in the category",'0');
						return $guaran;
					}
					else if(in_array(4,$cat_chk))
					{
						$land_valuee=$this->landline_fax_check();
						$this->contract_jd_level ='applicable_with_landline';
						if($land_valuee==1)
						{
							$guaran=1;
							return $guaran;
						}
						else if($land_valuee==0)
						{
							$guaran=0;
							$this->log_creation("Landline compulsory category without contract landline/fax/tollfree",'1');
							return $guaran;
						}
					}
					else
					{
						$this->contract_jd_level ='applicable'; /* means paid and does not contain flagged categories*/
						$guaran=1;
						return $guaran;
					}
				}
			}

			function provideStampPD()
			{
				$financeArr			= array();
				$budgetperday 		= 0;
				$jdvEligibleBudget	= $this->getdeductionperday_pd();
				$escAmt				= $this->getEcsValue();
				if($escAmt==0)
				{
					$financeArr	 = $this->financeObj->getFinanceMainData();

					for($i=1;$i<16;$i++)
					{
						if($i== 6 || $i== 9 ||$i== 10 ||$i== 11 ||$i==12)
						{
							continue;
						}
						if($financeArr[$i]['bid_perday']>0)
						{
							$budgetperday=$budgetperday + $financeArr[$i]['bid_perday'];
						}
					}
					if($budgetperday>0)
					{
						if(round($budgetperday) < $jdvEligibleBudget)
						{
							$this->update_extradetails_guarantee = self::JD_VERIFIED;
							$this->log_creation("Proper",'5');
							return;
						}
						else
						{
							$this->update_extradetails_guarantee = self::JD_GUARANTEE;
							$this->log_creation("Proper",'1');
							return;
						}
					}
				}
				else
				{
					$budgetperday=$escAmt;
				}

				if(round($budgetperday) >= $jdvEligibleBudget)
				{
					$this->update_extradetails_guarantee = self::JD_GUARANTEE;
					$this->log_creation("Proper",'1');
				}
				else
				{
					$this->update_extradetails_guarantee = self::JD_VERIFIED;
					$this->log_creation("Proper",'5');
				}
			}

			function getEcsValue()
			{
				$sqlEcs = "SELECT b.billAmount, a.cycleSelected  FROM db_ecs.ecs_mandate a join db_ecs_billing.ecs_bill_details b ON a.billdeskid = b.billdeskid AND a.parentid = b.parentid WHERE b.parentid='".$this->parentid."' AND activeFlag=1 AND ecs_stop_flag=0 AND deactiveflag=0  ORDER BY duedate DESC LIMIT 1";
				$qryEcs = $this->conn_finance->query_sql($sqlEcs);
				if($qryEcs)
				{
					$rowEcs=mysql_fetch_assoc($qryEcs);
					if(($rowEcs['billAmount']!=''||$rowEcs['billAmount']!=0) && ($rowEcs['cycleSelected']!='' || $rowEcs['cycleSelected']!=0))
					{
						$value = $rowEcs['billAmount']/$rowEcs['cycleSelected'];
					}

				}

				if($value)
				{
					return $value;
				}
				else
				{
					return 0;
				}
			}

			function updateStamp()               /* Update the table */
			{
				$insertarr	= array();
				$insertarr['tbl_companymaster_extradetails']= array(
																"guarantee"			=> $this->update_extradetails_guarantee,
																"contract_jd_level"	=> $this->contract_jd_level,
																"display_JD_stamp"	=> $this->display_JD_stamp,
																"parentid"			=> $this->parentid,
																"sphinx_id"			=> $this->sphinx_id
															);
				$this->compmaster_obj->UpdateRow($insertarr);

				if($this->checkNational())
				{
					$updateNational="UPDATE tbl_companymaster_extradetails_national SET guarantee='".$this->update_extradetails_guarantee."', contract_jd_level='".$this->contract_jd_level."', display_JD_stamp='".$this->display_JD_stamp."' WHERE parentid='".$this->parentid."'";
					$resultNational=$this->conn_national->query_sql($updateNational);
				}
			}
			
			function getPaidStatus()
			{
				$sql="SELECT parentid from tbl_companymaster_finance where parentid='".$this->parentid."' and balance>0 and expired=0 ";
				$res = $this->conn_finance->query_sql($sql);
				
				$numberofrows = $this->conn_finance->numRows($res);
				
				if($numberofrows>0)
				{
					$this->PaidStatus=1;
					return 1;
					
				}else
				{
					$this->PaidStatus=0;
					return 0;
				}				
			}
			
			
			function hasRestrictedCategory()
			{
				$hasRestrictedCategory_flag=0;

				$fieldStr	= '';
				$temparr	= array();
				$fieldstr	= "catidlineage,catidlineage_nonpaid,tag_catid";
				$tablename	= 'tbl_companymaster_extradetails';
				$wherecond	=	"sphinx_id='".$this->sphinx_id."'";
				$temparr  	=$this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

				if($temparr['numrows'])
				{
					$catRow = $temparr['data']['0'];
					
					$catRow['catidlineage'] = str_replace("/","",$catRow['catidlineage']);
					$catRow['tag_catid'] = str_replace("/","",$catRow['tag_catid']);
					$catRow['catidlineage_nonpaid'] = str_replace("/","",$catRow['catidlineage_nonpaid']);
					
					$catidlineage_str = $catRow['catidlineage'].",".$catRow['catidlineage_nonpaid'].",".$catRow['tag_catid'];
					
					$catidlineage_arr = explode(",",$catidlineage_str);
					$catidlineage_arr = array_filter($catidlineage_arr);
					
					if(count($catidlineage_arr))
					{
						
						$catidlineage_str = implode(",",$catidlineage_arr); 
						$this->catidlist =$catidlineage_str;
						//$sql_categories_guarantee="SELECT catid from tbl_categorymaster_generalinfo where catid in(".$catidlineage_str.") and category_addon&2=2 ";
						$sql_categories_guarantee="SELECT group_concat(catid) as rescat from tbl_categorymaster_generalinfo where catid in(".$catidlineage_str.") and category_addon&2=2 ";
						
						$res_categories_guarantee = $this->conn_local->query_sql($sql_categories_guarantee);
						if($this->conn_local->numRows($res_categories_guarantee))
						{
							$res_categories_guarantee_arr= $this->conn_local->fetchData($res_categories_guarantee);
							if($res_categories_guarantee_arr['rescat']!=null)
							{
								$hasRestrictedCategory_flag=1;
								$this->RestrictedCategory_flag=1;
								$this->RestrictedCategory_str=$res_categories_guarantee_arr['rescat'];
							}
						}
					}
				}
				return $hasRestrictedCategory_flag;	
			}
			
			function checkEntryInExclusionList()
			{
				$checkEntry =0;
				// the contract has enty in your exclusion list table
				$select_query = "SELECT * FROM tbl_guarantee_exclusion_contract where parentid='".$this->parentid."'";
				$res  = $this->conn_iro->query_sql($select_query);
				//echo "<pre>"; var_dump($res);
				if(mysql_num_rows($res) >0)
				{	
					$checkEntry =1;	
				}
				
				
				return $checkEntry;
			}
			
			function processGuranteeFlag()
			{
				
				$guranteeflag=0;
				$guranteenationalflag=0;
				$PaidStatus = $this->getPaidStatus();
               
				$checkEntry= $this->checkEntryInExclusionList($parentid);
			
				
				if($checkEntry==1)
				{
					
				    $guranteeflag=0;
					$guranteenationalflag=0;
					
				}
				else
				{				
					if($PaidStatus==1)
					{
						$RestrictedCategoryval = $this->hasRestrictedCategory();
							
						if($RestrictedCategoryval)
						{
							$guranteeflag=0;
							$guranteenationalflag=0;
						}
						else // restricted category not present
						{
							$guranteeflag=1;
							$guranteenationalflag=1;
						}		
					}
						else // there is no balance in non national listing contract so checking national listing also 
						{
							$NationalPaidStatus = $this->getNationalPaidStatus();
							if($NationalPaidStatus==1)
							{
								$RestrictedCategoryval = $this->hasRestrictedCategory();
								
								if($RestrictedCategoryval)
								{
									$guranteeflag=0;
									$guranteenationalflag=0;
								}
								else // restricted category not present
								{
									$guranteeflag=1;
									$guranteenationalflag=1;
								}
							}
						}
			    }
			
				
				
			   $sql_check = "SELECT * FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";
	   	       $res_check=$this->conn_iro->query_sql($sql_check);
	   	       if($this->conn_iro->numRows($res_check)>0)
	   	       {
				$insertarr	= array();
				
				$insertarr['tbl_companymaster_extradetails']= array(
																	"guarantee"			=> $guranteeflag,
																	"parentid"			=> $this->parentid,
																	"sphinx_id"			=> $this->sphinx_id
																);
				$this->compmaster_obj->UpdateRow($insertarr);
				$this->processRestrictFlag();
				$this->gurantee = $guranteeflag;
				$this->log_message();
				
			   }
			}
			
			
			function processRestrictFlag(){
			 
			   $sql_obtain="SELECT CONCAT(COALESCE(catidlineage_nonpaid,''),',',COALESCE(catidlineage,'')) as categories FROM tbl_companymaster_extradetails WHERE parentid='".$this->parentid."'";
	   	       $res_obtain=$this->conn_iro->query_sql($sql_obtain);
			   if($this->conn_iro->numRows($res_obtain)>0){
				   $row_obtain=$this->conn_iro->fetchData($res_obtain);
				   $categories = $row_obtain['categories'];
				   $categories=str_replace('/', '', $categories);
				   $categories=explode(',',$categories);
				   $catids='';
				   for($i=0;$i<count($categories);$i++){
				   		$catids.=",'".$categories[$i]."'";
				   }
				   $catids=ltrim($catids,',');
				   $sql    = "SELECT category_name FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$catids.") and category_addon&8 = 8";

				   $resSql = $this->conn_local->query_sql($sql);
				   if($this->conn_iro->numRows($resSql)>0){
				   	$this->restrict_flag=1;
				   	$insertarr['tbl_companymaster_extradetails']= array(
				   														"restrict_display"			=> 1,
				   														"parentid"			=> $this->parentid,
																		"sphinx_id"			=> $this->sphinx_id
				   													);
				   	$this->compmaster_obj->UpdateRow($insertarr);
				   }
				   else{
								$this->restrict_flag=0;
				   		$insertarr['tbl_companymaster_extradetails']= array(
				   														"restrict_display"			=> 0,
				   														"parentid"			=> $this->parentid,
																		"sphinx_id"			=> $this->sphinx_id
				   													);
				   			$this->compmaster_obj->UpdateRow($insertarr);
				   }
			   }
			   else{
			   /* no cat */
			   }



			}


			function setJDgv_stamp()             /* Function for setting the stamp */
			{
					$this->paid=$this->checkPaid();
					if($this->paid==1)
					{
						$guarantee_flag=$this->getExceptionFlag();
						if($guarantee_flag==-1)
						{
							$this->provideStampPD();
							$catFlag	=	$this->category_check();
							if($catFlag==1)
							{
								$this->display_JD_stamp=1;
							}
						}
						else
						{
							$this->contract_jd_level ='override';
							if($guarantee_flag==1)
							{
								$this->display_JD_stamp = 1;
								$this->update_extradetails_guarantee = self::JD_GUARANTEE;
								$this->log_creation("Forcefully given",'1');
							}
							else if($guarantee_flag==5)
							{
								$this->display_JD_stamp = 1;
								$this->update_extradetails_guarantee = self::JD_VERIFIED;
								$this->log_creation("Forcefully given",'5');
							}
							else if($guarantee_flag==0)
							{
								$this->display_JD_stamp = 0;
								$this->update_extradetails_guarantee = self::JD_DEFAULTSTAMP;
								$this->log_creation("Forcefully removed",'0');
							}

						}
					}
					else
					{
						$this->contract_jd_level ='not_applicable';
						$this->update_extradetails_guarantee = self::JD_DEFAULTSTAMP;
						$this->log_creation("Not Paid",'0');
					}

				if($this->contract_jd_level == 'blacklisted' || $this->contract_jd_level == 'not_applicable') {
					$this->display_JD_stamp =0;
					$this->update_extradetails_guarantee = self::JD_DEFAULTSTAMP;
				}
				$this->updateStamp();
			}

			function getDataCity()
			{
				$fieldStr	= '';
				$temparr	= array();
				$fieldstr	= "DISTINCT data_city";
				$tablename	= 'tbl_companymaster_generalinfo';
				$wherecond	= "parentid='".$this->parentid."'";
				$temparr  	= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

				if($temparr['numrows'])
                {
                    $fetchdatacity =  $temparr['0']['data'];
					$city 		   =  $fetchdatacity['data_city'];
				}
				if(trim($city)=='')
				{
					$sql_fund_transfer="SELECT destinationCity FROM payment_funds_transfer_master where destinationParentid='".$this->parentid."'";
					$qry_fund_transfer=$this->conn_finance->query_sql($sql_fund_transfer);
					if($qry_fund_transfer)
					{
						$row_fund_transfer=mysql_fetch_array($qry_fund_transfer);
						$city=$row_fund_transfer['destinationCity'];
					}
				}
				$all_main_cities=array("mumbai","delhi","hyderabad","kolkata","bangalore","chennai","pune","ahmedabad","jaipur","chandigarh","coimbatore");
				if(in_array(strtolower($city),$all_main_cities))
				{
					$return_city_name = $city;
				}
				else
				{
					$return_city_name = "other_cities";
				}
				return $return_city_name;
			}

			function jd_override_shadow($reason,$stamp,$uid)
			{
				$fieldStr	= '';
				$temparr	= array();
				$fieldstr	= "guarantee";
				$tablename	= 'tbl_companymaster_extradetails';
				$wherecond	= "parentid='".$this->parentid."'";
				$temparr  	= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

				$row_get=$temparr['0']['data'];

				if($row_get['guarantee']!=$stamp)
				{
					$sql_override_shadow = "INSERT INTO tbl_jdgv_override_shadow SET
												parentid		 = '".$this-> parentid."',
												guarantee_reason = '".addslashes(stripslashes($reason))."',
												userid			 = '".$uid."',
												ipaddress		 = '".$_SERVER['REMOTE_ADDR']."',
												flag			 = '".$stamp."'";
					$qry_override_shadow = $this->conn_local->query_sql($sql_override_shadow);
                }
			}

			function jd_override()
			{
				$sql_get_shadow_override="SELECT parentid, guarantee_reason, curtime, userid, ipaddress, flag FROM tbl_jdgv_override_shadow WHERE parentid='".$this->parentid."' ORDER BY curtime DESC LIMIT 1";
				$qry_get_shadow_override=$this->conn_local->query_sql($sql_get_shadow_override);
				if(mysql_num_rows($qry_get_shadow_override)>0)
				{
					$row_get_shadow_override=mysql_fetch_assoc($qry_get_shadow_override);

					$sql_flag_check="SELECT flag FROM tbl_jdgv_override WHERE parentid='".$this->parentid." ORDER BY curtime DESC LIMIT 1'";
					$qry_flag_check=$this->conn_local->query_sql($sql_flag_check);
					$row_flag_check=mysql_fetch_assoc($qry_flag_check);
					if($row_get_shadow_override['flag']!=$row_flag_check['flag'])
					{
						$sql_override="INSERT INTO tbl_jdgv_override SET
											parentid		 = '".$row_get_shadow_override['parentid']."',
											guarantee_reason = '".addslashes(stripslashes($row_get_shadow_override['guarantee_reason']))."',
											curtime			 = '".$row_get_shadow_override['curtime']."',
											userid			 = '".$row_get_shadow_override['userid']."',
											ipaddress		 = '".$row_get_shadow_override['ipaddress']."',
											flag			 = '".$row_get_shadow_override['flag']."'";
						$qry_override=$this->conn_local->query_sql($sql_override);
					}
				}
			}

			function jd_override_temp_to_main()
			{
				$this->paid = $this->checkPaid();
				if(strtoupper($this->module)=='CS' && $this->paid==1)
				{
					$sql_del_override_shadow="delete from tbl_jdgv_override_shadow where parentid='".$this->parentid."'";
					$qry_del_override_shadow=$this->conn_local->query_sql($sql_del_override_shadow);

					$sql_get_override="select * from tbl_jdgv_override where parentid='".$this->parentid."' order by curtime desc limit 1";
					$qry_get_override=$this->conn_local->query_sql($sql_get_override);
					if(mysql_num_rows($qry_get_override)>0)
					{
						$row_get_override=mysql_fetch_assoc($qry_get_override);

						$sql_ins_override_shadow="insert into tbl_jdgv_override_shadow set
													parentid		 = '".$row_get_override['parentid']."',
													guarantee_reason = '".addslashes(stripslashes($row_get_override['guarantee_reason']))."',
													curtime			 = '".$row_get_override['curtime']."',
													userid			 = '".$row_get_override['userid']."',
													ipaddress		 = '".$row_get_override['ipaddress']."',
													flag			 = '".$row_get_override['flag']."'";
					    $qry_ins_override_shadow=$this->conn_local->query_sql($sql_ins_override_shadow);
					}
				}
			}

			function guarantee_intermediate()
			{
				$intermediate_value = array();

				$fieldStr	= '';
				$temparr	= array();
				$fieldstr	= "guarantee, contract_jd_level, display_JD_stamp";
				$tablename	= 'tbl_companymaster_extradetails';
				$wherecond	= "parentid='".$this->parentid."'";
				$temparr  	= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

				if($temparr['numrows'] > 0)
				{
					$row_get_inter=$temparr['0']['data'];

					$sql_get_override="select * from tbl_jdgv_override_shadow where parentid='".$this->parentid."' order by curtime desc limit 1";
					$qry_get_override=$this->conn_local->query_sql($sql_get_override);
					$row_get_override=mysql_fetch_array($qry_get_override);

					$intermediate_value = array("guarantee_flag"=>$row_get_inter['guarantee'],"guarantee_reason"=>$row_get_override['guarantee_reason']);
				}
				return $intermediate_value;
			}

			function checkNational()
			{
				$nationalArr   	= array();
				$nationalArr	= $this->financeObj->getFinanceMainData(10);
				if($nationalArr[10]['budget']>0)
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			
			
			function getNationalPaidStatus()
			{
				$nationalArr   	= array();
				$nationalArr	= $this->financeObj->getFinanceMainData(10);
				//echo "<pre>";print_r($nationalArr);
				if($nationalArr[10]['balance']>0 && $nationalArr[10]['expired']==0 )
				{
					$this->NationalPaidStatus=1;
					return 1;
				}
				else
				{
					$this->NationalPaidStatus=0;
					return 0;
				}
			}




			function setMetaShadow()
			{
				$recalFlg1 = $this -> financeObj -> getFinanceTempData('1');
				$recalFlg2 = $this -> financeObj -> getFinanceTempData('2');
				if($recalFlg2['2']['recalculate_flag'] || $recalFlg1['1']['recalculate_flag'])
				{
					$upArr['eligible_budget_jdstamp']	= $this->getdeductionperday_pd();
					$this->financeObj->metaShadowUpdate($upArr);
				}
			}

			function newParentid($pid,$sid)
			{
				if($this->parentid!=$pid)
				{
					$this->parentid=$pid;
				}
				if($this->sphinx_id!=$sid)
				{
					$this->sphinx_id=$sid;
				}
			}

			function updateTable()
			{
				$sql_remove_get="SELECT parentid, guarantee_reason, userid, ipaddress, curtime FROM log_guarantee_removal_reason WHERE parentid='".$this->parentid."'";
				$qry_remove_get=$this->conn_local->query_sql($sql_remove_get);
				if($qry_remove_get)
				{
					if(mysql_num_rows($qry_remove_get)>0)
					{
						while($row_remove_get=mysql_fetch_assoc($qry_remove_get))
						{
							$sql_new_ins="INSERT INTO tbl_jdgv_override SET
											parentid		=	'".$row_remove_get['parentid']."',
											guarantee_reason=	'".addslashes(stripslashes($row_remove_get['guarantee_reason']))."',
											curtime			=	'".$row_remove_get['curtime']."',
											userid			=	'".$row_remove_get['userid']."',
											ipaddress		=	'".$row_remove_get['ipaddress']."',
											flag			=	0";
							$qry_new_ins=$this->conn_local->query_sql($sql_ins_new);
						}
					}
					else
					{

						$sql_given_get="SELECT parentid, guarantee_reason, userid, ipaddress, curtime FROM log_guarantee_given_reason WHERE parentid='".$this->parentid."'";
						$qry_given_get=$this->conn_local->query_sql($sql_given_get);
						while($row_given_get=mysql_fetch_assoc($qry_given_get))
						{
							if(mysql_num_rows($row_given_get)>0)
							{
								$fieldStr	= '';
								$temparr	= array();
								$fieldstr	= "guarantee";
								$tablename	= 'tbl_companymaster_extradetails';
								$wherecond	= "parentid='".$row_get['parentid']."'";
								$temparr  	= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

								$row_extradetails_get=$temparr['0']['data'];
								$guarantee=$row_extradetails_get['guarantee'];

								if($guarantee==0 || $guarantee==1)
								{
									$flag=1;
								}
								else if($guarantee==5)
								{
									$flag=5;
								}

								$sql_new_ins="INSERT INTO tbl_jdgv_override SET
												parentid		=	'".$row_given_get['parentid']."',
												guarantee_reason=	'".addslashes(stripslashes($row_given_get['guarantee_reason']))."',
												curtime			=	'".$row_given_get['curtime']."',
												userid			=	'".$row_given_get['userid']."',
												ipaddress		=	'".$row_given_get['ipaddress']."',
												flag			=	'".$flag."'";
								$qry_new_ins=$this->conn_local->query_sql($sql_ins_new);
							}
						}
					}
				}
			}
			
			function log_message()
			{
				$jdgurantee_logsql = "INSERT INTO jdgurantee_log set 				
				parentid ='".$this->parentid."',
				guranteeflag = ".$this->gurantee.",
				catid = '".$this->catidlist."',								
				rescatid 	 ='".$this->RestrictedCategory_str."',
				PaidStatus   ='".$this->PaidStatus."',
				NationalPaidStatus='".$this->NationalPaidStatus."',
				restrictflag='".$this->restrict_flag."',
				updateby= '".$this->ucode."'";
				
				$this->conn_iro->query_sql($jdgurantee_logsql);				
			}
			
			function log_creation($reason,$guarantee_value)
			{
				return 1; // there is no need to log at file level
				$fileL 	= fopen($this->filename,'a+');
				$log 	= "<table cellspacing=0 cellpadding = 0>
							 <tr>
								<td>JDGV Value =</td><td>".$guarantee_value."</td>
							 </tr>
							 <tr>
								 <td>Reason =</td><td>".$reason."</td>
							</tr>
							<tr>
								 <td>IP Address =</td><td>".$_SERVER['REMOTE_ADDR']."</td>
							</tr>

							<tr>
								 <td>Date/Time =</td><td>".date("Y-m-d H:i:s")."</td>
							</tr>
							<tr>
								 <td colspan = 2> --------------------------------------------------------------------------- </td>
							</tr>
						 </table>" ;
				fwrite($fileL, $log);
				fclose($fileL);
			}
	}
?>
