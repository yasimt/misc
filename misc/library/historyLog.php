<?php 
	require_once(APP_PATH.'00_Payment_Rework/company_finance_class.php');
	require_once(APP_PATH.'00_Payment_Rework/payment_app/payment_app.php');
	require_once(APP_PATH.'library/genio_functions.php');
	class contractLog
	{
			private $parentid, $module, $userid, $conn_iro, $conn_local, $conn_finance;
			private $logflag; /* 0 = reset level, 1 = taken old logs, 2 = taken new logs */
			private $showflag; /* variable for deciding whether to show the logs or the whole process to run*/
			private $oldGeneralDetails,$oldBidDetails,$newGeneralDetails,$newBidDetails;   /* details array */
			private $financeObj, $sphinx_id,$compmaster_obj;

			function __construct($pid,$mod,$uid,$dbarr,$flagValue=0) {
				$this->parentid		= $pid;
				$this->module		= $mod;
				$this->userid		= $uid;
				$this->logflag 		= $flagValue ;
				$this->conn_iro		= new DB($dbarr['DB_IRO']);	
				$this->conn_local	= new DB($dbarr['LOCAL']);
				$this->conn_local_slave	= new DB($dbarr['DB_DECS_SLAVE']);	
				$this->conn_finance	= new DB($dbarr['FINANCE']);
				$this->sphinx_id 	= getContractSphinxId($pid);
				$this->financeObj   = new company_master_finance($dbarr,$pid,$this->sphinx_id);
				if(!isset($this->compmaster_obj)){
					$this->compmaster_obj = new companyMasterClass($this->conn_iro,"", $this->parentid);
				}
				if($this->showflag!=2)
				{	
					$this->LogCurrentInfo()	;
				}
			}
			function __destruct()
			{
				if($this->logflag!=2)
				{
					$this->updateLog();
				}
				unset($this->oldGeneralDetails);
				unset($this->oldBidDetails);
				unset($this->newGeneralDetails);
				unset($this->newBidDetails);
				$this->conn_iro->close();
				$this->conn_local->close();
				$this->conn_finance->close();
				unset($this->conn_iro);
				unset($this->conn_local);
				unset($this->conn_finance);

			}
			function updateLog()
			{
				$paidstatus	= '';
				$compname	= '';
				$empty_chk_old = '';
				$empty_chk_new = '';
 				if($this->logflag==1)
				{
					$this->LogCurrentInfo();
					
				}	
				
				if($this->logflag==2)
				{
					
					$paidstatus		=$this->newGeneralDetails['paid'];
					$compname		=$this->newGeneralDetails['companyname'];
					$compname1		=$this->oldGeneralDetails['companyname'];
					$pincode_new	=$this-> newGeneralDetails['pincode'];
					$latitude_new	=$this-> newGeneralDetails['latitude'];
					$longitude_new	=$this-> newGeneralDetails['longitude'];
					
					$pincode_old	=$this-> oldGeneralDetails['pincode'];
					$latitude_old	=$this-> oldGeneralDetails['latitude'];
					$longitude_old	=$this-> oldGeneralDetails['longitude'];
					$this->removematchlog();
					$this->companyNameChangePaid($compname1,$compname, $pincode_old, $pincode_new, $latitude_old, $latitude_new, $longitude_old, $longitude_new);
					if(count($this->oldBidDetails)>0){
                        foreach($this->oldBidDetails as $key =>$value)
                        {

                            $bid_old.= "<bid_details_".$key.">".http_build_query($this->oldBidDetails[$key])."</bid_details_".$key.">";
                        }
                    }
                    if(count($this->newBidDetails)>0)
                    {
                        foreach($this->newBidDetails as $key =>$value)
                        {
                            $bid_new.= "<bid_details_".$key.">".http_build_query($this->newBidDetails[$key])."</bid_details_".$key.">";
                        }
                    }
					if(empty($this->oldGeneralDetails) && empty($this->newGeneralDetails))
					{
						foreach($this->oldBidDetails as $key => $val)
						{
							if(empty($this->oldBidDetails[$key]))
									$empty_chk_old=1;
							else
							{
									$empty_chk_old=0;
									break;
							}
						}
						foreach($this->newBidDetails as $key => $val)
						{
							if(empty($this->newBidDetails[$key]))
									$empty_chk_new=1;
							else
							{
									$empty_chk_new=0;
									break;
							}
						}
					}
					if(count($this->oldGeneralDetails) || count($this->newGeneralDetails) || trim($bid_old)!='' || trim($bid_new)!='')
					{
						$sql_insert ="INSERT INTO tbl_contract_update_trail SET
												parentid				= '".$this->parentid."',
												update_time				= '".date('Y-m-d H:i:s')."',
												updated_by				= '".$this->userid."',
												paidstatus				= '".$paidstatus."',
												compname				= '".addslashes(stripslashes($compname))."',
												business_details_old	= '".http_build_query($this->oldGeneralDetails)."',
												business_details_new	= '".http_build_query($this->newGeneralDetails)."',
												bidding_details_old		= '".$bid_old."',
												bidding_details_new		= '".$bid_new."'";
						if($empty_chk_new==0 && $empty_chk_old==0)
						{
							$res_insert =$this->conn_local->query_sql($sql_insert);					
						}
					}
					
					$this->oldGeneralDetails = array();
					$this->oldBidDetails	 = array();
					$this->newGeneralDetails = array();
					$this->newBidDetails 	 = array(); 
					
					$this->logflag =0;
				}

			}
			
			function removematchlog()
			{
				if($this->logflag==2)
				{
					if(count($this->oldGeneralDetails)>0)
					{
						foreach($this->newGeneralDetails as $key => $value)
						{
							if($key!='catList' && $key !='campaignid')
							{
								if($value==$this->oldGeneralDetails[$key] && $key !='campaignid')
								{
									unset($this->oldGeneralDetails[$key]);
									unset($this->newGeneralDetails[$key]);
								}
							}
						}
						
					}
					
					if(count($this->newBidDetails))
					{
						foreach($this->newBidDetails as $key => $value)
						{
							foreach($this->newBidDetails[$key] as $key1 => $value1)
							{
								if($key1!='campaignid')
								{
									if($this->newBidDetails[$key][$key1] == $this->oldBidDetails[$key][$key1])
									{
										unset($this->oldBidDetails[$key][$key1]);
										unset($this->newBidDetails[$key][$key1]);
									}
								}	
							}
						}
					}

					
				}
				return false;
			}
			function LogCurrentInfo()
			{
				$general_log_array = $this->LogGeneralDetails();
				$bid_log_array 	   = $this->LogBidDetails();

				switch($this->logflag)
				{
					case '0':
						$this->oldGeneralDetails = $general_log_array;
						$this->oldBidDetails	 = $bid_log_array;
						$this->logflag = 1;					
						break;
					case '1':
						$this->newGeneralDetails = $general_log_array;
						$this->newBidDetails 	 = $bid_log_array;	
						$this->logflag = 2;			

						break;
				}
			}
			function LogGeneralDetails()
			{
					$general_log_array	=	array();

					$temparr		= array();
					$fieldstr		= '';
					$fieldstr 		= "*";
					$tablename		= "tbl_companymaster_generalinfo";
					$wherecond		= "parentid='".$this->parentid."'";
					$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

					if($temparr['numrows']>0)
					{
							$genArray	=	$temparr['data']['0'];
					}

					$temparr		= array();
					$fieldstr		= '';
					$fieldstr 		= "contact_person_addinfo,attributes,attributes_edit,attribute_search, turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,catidlineage_nonpaid, national_catidlineage_nonpaid,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,createdby,createdtime,datavalidity_flag,deactflg,display_flag,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,closedown_flag as company_status,closedown_date,tag_catid as PrimaryCategoryID ,tag_catname as PrimaryCategory,low_ranking,fb_prefered_language as prefered_language,businesstags,type_flag,website_type_flag,iro_type_flag,trending_flag,misc_flag,tag_catid,tag_catname ";
					$tablename		= "tbl_companymaster_extradetails";
					$wherecond		= "parentid='".$this->parentid."'";
					$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

					if($temparr['numrows'])
					{
							$extArray	= $temparr['data']['0'];
					}
					if(trim($extArray['serviceName'])=='~~ ~~ ~~')
					{
						$extArray['serviceName']='';
					}
					if(trim($extArray['working_time_start'])==',')
					{
						$extArray['working_time_start']='';
					}
					if(trim($extArray['working_time_end'])==',')
					{
						$extArray['working_time_end']='';
					}
					
					if(trim($extArray['catidlineage'])!='') {
						$extArray['catList'] = $this -> addcatList($extArray['catidlineage']);
					}
					
					if(trim($extArray['catidlineage_nonpaid'])!='') {
						$extArray['catList_NonPaid'] = $this -> addcatList($extArray['catidlineage_nonpaid']);
					}
					
					/* Category Sponsorship/Category Text Banner/Category Filter Banner category list*/
					$catSponArr		= array();
					$catTextArr		= array();
					$catFilterArr	= array();
					$sqlSponTextCat = "SELECT cat_name,campaign_type FROM tbl_catspon_shadow WHERE parentid='".$this->parentid."'";
					$qrySponTextCat = $this->conn_local->query_sql($sqlSponTextCat);
					if($qrySponTextCat && mysql_num_rows($qrySponTextCat) == 0){
						$sqlSponTextCat = "SELECT cat_name,campaign_type FROM tbl_catspon WHERE parentid='".$this->parentid."'";
						$qrySponTextCat = $this->conn_local->query_sql($sqlSponTextCat);
					}
					if($qrySponTextCat && mysql_num_rows($qrySponTextCat) > 0){
						while($rowSponTextCat = mysql_fetch_assoc($qrySponTextCat)){
							if($rowSponTextCat['campaign_type'] == 1){
								$catSponArr[]	= $rowSponTextCat['cat_name'];
							}else if($rowSponTextCat['campaign_type'] == 3){
								$catTextArr[]	= $rowSponTextCat['cat_name'];
							}
						}
					}
					$sqlFilterCat	= "SELECT title_name FROM tbl_catfilter_shadow WHERE parentid ='".$this->parentid."'";
					$qryFilterCat	= $this->conn_local->query_sql($sqlFilterCat);
					if($qryFilterCat && mysql_num_rows($qryFilterCat) == 0){
						$sqlFilterCat	= "SELECT title_name FROM tbl_catfilter WHERE parentid ='".$this->parentid."'";
						$qryFilterCat	= $this->conn_local->query_sql($sqlFilterCat);
					}
					if($qryFilterCat && mysql_num_rows($qryFilterCat) > 0){
						while($rowFilterCat	= mysql_fetch_assoc($qryFilterCat)){
							$catFilterArr[]	= $rowFilterCat['title_name'];
						}
					}
					
					if(count($catSponArr)>0)
						$extArray['sponcatname'] =  implode(",",$catSponArr);
					if(count($catTextArr)>0)
						$extArray['textcatname'] =  implode(",",$catTextArr);
					if(count($catFilterArr)>0)
						$extArray['filtercatname'] =  implode(",",$catFilterArr);
					/* Category Sponsorship/Category Text Banner/Category Filter Banner category list*/

					if(!empty($genArray) && !empty($extArray))
					{
						$general_log_array=array_merge($genArray,$extArray);
					}
					
					return $general_log_array;
			}
			
			function LogBidDetails()
			{
				$financeArr = array();
				$finalFinArr= array();
				$version  	= fetchVersion($this->parentid);
				$financeArr = $this->financeObj->getFinanceMainData(0,$version);
				if(count($financeArr)==0)
				{
					$financeArr = $this->financeObj->getFinanceMainData();
				}

				foreach($financeArr as $key => $value)
                {
                    foreach($financeArr[$key] as $keyVal => $value)
                    {
                        if(!is_numeric($keyVal))
                        {
                            $finalFinArr[$key][$keyVal] =  $financeArr[$key][$keyVal];
                        }
                    }
                }
				return $finalFinArr;
			}
			
			function newContractLog($pid)
			{
				$this->updateLog();		
				if($pid!='')
				{		
					$this->parentid = $pid;
					$this->logflag 		= 0;
					$this->LogCurrentInfo()	;
				}
			}
			
			function showLogList()
			{
				$arrayLog=array();
				/* This is old un-optimized query which was creating problem now we are using OPtimized query by Rajeev sir
				if(!stristr($_SERVER['REMOTE_ADDR'],'172.29.5.'))
				{
					$cond=" AND (updated_by NOT LIKE '%cron%' AND updated_by NOT LIKE '%Process%' AND updated_by NOT LIKE '%CORRECT%') ";
				}
				$showSql=  "SELECT compname,updated_by,update_time,id
							FROM tbl_contract_update_trail
							WHERE
								parentid = '".$this->parentid."' 
							AND (business_details_old!='' 
							OR business_details_new!='' 
							OR bidding_details_new!='' 
							OR bidding_details_new !='')".$cond;
				*/			
				
				/*if(!stristr($_SERVER['REMOTE_ADDR'],'172.29.5.'))
				{
					$cond=" WHERE (updated_by NOT LIKE'%cron%' AND updated_by NOT LIKE '%Process%' AND updated_by NOT LIKE '%CORRECT%') ";
				}*/
				$showSql=  "SELECT * FROM
							(
								SELECT compname,updated_by,update_time,id 
								FROM  tbl_contract_update_trail 
								WHERE parentid= '".$this->parentid."' 
								AND (business_details_old!='' OR business_details_new!='' OR bidding_details_new!='' OR bidding_details_new !='') 
							)x1 ".$cond ;

				$resSql =  $this->conn_local->query_sql($showSql);
				if($resSql && mysql_num_rows($resSql)>0)
				{
					$i=0;
					while($rowSql =  mysql_fetch_assoc($resSql))
					{
						$arrayLog[$i++]=$rowSql;
					}
				}
				
				return $arrayLog;
			}
			
			function showLogDetails($id)
			{
					$sqlDetails="select * from tbl_contract_update_trail where id='".$id."'";
					$resDetails=$this->conn_local->query_sql($sql);
					if($resDetails && mysql_num_rows($resDetails))
					{
						$rowDetails=mysql_fetch_array($resDetails);
						return $rowDetails;
					}
			}
			
			function field_hide($level) /* level == 1 --- For business details;  2 --- For Bidding Details */
			{
				$hide_field = array();
				if($level == 1)
				{
					$hide_field = array('catidlineage','Catidlineage_','catidlineage_search','national_catidlineage','national_catidlineage_search','lockDateTime','flgApproval','flgActive','map_pointer_flags','business_assoc_flags','vertical_flags','flags','alsoServeFlag','newbusinessflag','hidden_flag','nationalid','sphinx_id','regionid','display_city','display_flag','national_catidlineage_nonpaid','catidlineage_nonpaid');
				}else if($level == 2)
				{
					$hide_field = array('nationalid','sphinx_id','region_id','companyname','pincode','freeze','mask','original_creator','orginal_date','referto','bid_id','lastcheck') ;
				}
				
				return $hide_field;
			}
			
			function field_abstract($level) /* level == 1 --- For business details;  2 --- For Bidding Details */
			{
				if($level == 1)
				{
					$abstractFieldArr = array(
												"nationalid"  => "National ID",
												"sphinx_id"   => "Sphinx ID",
												"regionid"    => "Region ID",
												"companyname" => "Company Name",
												"country"     => "Country",
												"display_city"=> "Display City",
												"catlist"     => "Categories",
												"catList"     => "Categories",
											"catList_NonPaid" => "Non Paid Categories",
									"geocode_accuracy_level"  => "Geocode Accuracy Level",
										  "email_feedback"    => "Email Feedback", 
									 "temp_deactive_start"    => "Temporary Deactivation Start Date", 
									   "temp_deactive_end"    => "Temporary Deactivation End Date", 
									  "working_time_start"    => "Working Hours Start Timings",
										"working_time_end"    => "Working Hours End Timings",			    		 			    
											 "serviceName"    => "Enhancements",
											 "no_employee"    => "Number of Employees",
											"full_address"    => "Full Address",
										"landline_display"    => "Landline Display",
								   "virtual_mapped_number"	  => "Virtual Mapped Number",
											   "data_city"    => "Data City",
										  "statement_flag"    => "Statement Flag",
										"original_creator"    => "Original Creator",
										   "original_date"    => "Original Date",
											   "updatedBy" 	  => "Updated By",
											   "updatedOn"    => "Updated On",
										  "backenduptdate"    => "Backend Update Date",
									   "number_of_reviews"    => "Number of Reviews",
									   "contract_calltype"    => "Contract Call Type",
											 "web_ratings"    => "Web Ratings",
											 "createdtime"    => "Created Time",
											 "sponcatname"	  => "Category Sponsorship Category",
											 "textcatname"	  => "Category Text Banner Category",
											 "filtercatname"  => "Category Filter Banner Category",
											 "compsynname"    => "Company Synonym",
											 "low_ranking"    => "Low Ranking",
											 "helpline_flag"  => "helpline contract",
											 "company_status" => "Company Status",
											 "closedown_date" => "Company Status Change Date",
											 "businesstags"   => "Business Tag",
											 "type_flag"	  => "Vertical Type Flag",
										  "website_type_flag" => "Website Type Flag",
											  "iro_type_flag" => "Iro Type Flag",
											  "trending_flag" => "Trending Flag",
											  "Test_Contract" => "Test Contract",
											  "misc_flag" => "Miscellaneous Flag"
											 
									);
				}
				else if($level == 2)
				{
				}
				return $abstractFieldArr;
			}
			
			function showLog($id, $node)
			{
				$url 	= "http://".DE_CS_APP_URL."/api/historyLogapi.php";
				$parameters = "id=".$id."&node=".$node."&reqType=2";	
				
				$log_response = json_decode($this->get_curl_resp($url, $parameters),true);
				$result = $log_response['data'];
				if($result['business_details_old'] != $result['business_details_new'])
				{
					$comparison_flag_bform = 1;
					parse_str(str_replace(" = '","=",$result['business_details_old']),$business_details_old_arr);
					parse_str(str_replace(" = '","=",$result['business_details_new']),$business_details_new_arr);
				}
				else
				{
					echo "<b align='center'> No Changes Made !</b>";
				}
								
				if($business_details_new_arr['company_status'] != '')
				{
					switch($business_details_new_arr['company_status'])
					{
						case 0 :  $business_details_new_arr['company_status'] = 'Open';break;
						case 1 :  $business_details_new_arr['company_status'] = 'Close Down';break;
						case 2 :  $business_details_new_arr['company_status'] = 'Shifted';break;
						case 3 :  $business_details_new_arr['company_status'] = 'Dotcom Companies';break;
						case 4 :  $business_details_new_arr['company_status'] = 'Std Code';break;
						case 5 :  $business_details_new_arr['company_status'] = 'Customer Care';break;
						case 6 :  $business_details_new_arr['company_status'] = 'Phone Banking';break;
						case 7 :  $business_details_new_arr['company_status'] = 'Emergency Service';break;
						case 8 :  $business_details_new_arr['company_status'] = 'Not Interested';break;
						case 9 :  $business_details_new_arr['company_status'] = 'Not Practicing';break;
						case 10 :  $business_details_new_arr['company_status'] = 'Not In Business';break;
						case 11 :  $business_details_new_arr['company_status'] = 'Mask';break;
						case 12 :  $business_details_new_arr['company_status'] = 'Admin Office';break;
						case 13 :  $business_details_new_arr['company_status'] = 'Under Renovation';break;
						case 14 :  $business_details_new_arr['company_status'] = 'Opening Shortly';break;
						case 15 :  $business_details_new_arr['company_status'] = 'Temporary Closed Down';break;
					}
				}
				
				if($business_details_old_arr['company_status'] != '')
				{
					switch($business_details_old_arr['company_status'])
					{
						case 0 :  $business_details_old_arr['company_status'] = 'Open';break;
						case 1 :  $business_details_old_arr['company_status'] = 'Close Down';break;
						case 2 :  $business_details_old_arr['company_status'] = 'Shifted';break;
						case 3 :  $business_details_old_arr['company_status'] = 'Dotcom Companies';break;
						case 4 :  $business_details_old_arr['company_status'] = 'Std Code';break;
						case 5 :  $business_details_old_arr['company_status'] = 'Customer Care';break;
						case 6 :  $business_details_old_arr['company_status'] = 'Phone Banking';break;
						case 7 :  $business_details_old_arr['company_status'] = 'Emergency Service';break;
						case 8 :  $business_details_old_arr['company_status'] = 'Not Interested';break;
						case 9 :  $business_details_old_arr['company_status'] = 'Not Practicing';break;
						case 10 :  $business_details_old_arr['company_status'] = 'Not In Business';break;
						case 11 :  $business_details_old_arr['company_status'] = 'Mask';break;
						case 12 :  $business_details_old_arr['company_status'] = 'Admin Office';break;
						case 13 :  $business_details_old_arr['company_status'] = 'Under Renovation';break;
						case 14 :  $business_details_old_arr['company_status'] = 'Opening Shortly';break;
						case 15 :  $business_details_old_arr['company_status'] = 'Temporary Closed Down';break;
					}
				}
				
				$fields_abstration = array();
				if($comparison_flag_bform)
				{
					$fields_to_ignore=$this->field_hide(1);
					$fields_abstration = $this->field_abstract(1);
					
					$i = 0;
					$keys = array_merge(array_keys($business_details_old_arr),array_keys($business_details_new_arr));
					$keys = array_unique($keys);
					if(count($business_details_old_arr))
					{
						foreach($keys as $value)
						{
							$bform_arr[$value] = $business_details_old_arr[$value];
						}
/*
						$bform_arr = $business_details_old_arr;
						if($business_details_new_arr['catList_NonPaid']!='')
						{
							if(trim($business_details_old_arr['catList_NonPaid'])==''){
								$bform_arr['catList_NonPaid']='';
							}
							
						}
*/
					}
					else
					{
						foreach($keys as $value)
						{
							$bform_arr[$value] = $business_details_new_arr[$value];
						}
/*
						$bform_arr = $business_details_new_arr;
						if($business_details_old_arr['catList_NonPaid']!='')
						{
							if(trim($business_details_new_arr['catList_NonPaid'])==''){
								$bform_arr['catList_NonPaid']='';
							}
							
						}*/
					}

					if(!array_key_exists('catList',$bform_arr) || (trim($result['updated_by'])=='web_edit' ||trim($result['updated_by'])=='webedit') )
					{
						$bform_arr['catList'] = '';
					}
					
					if(trim($business_details_old_arr['catList'],',')=='' || trim($business_details_old_arr['catlist'],',')!='' && (trim($result['updated_by'])=='web_edit' ||trim($result['updated_by'])=='webedit'))
					{
						$catNames_old = $this->addcatList($business_details_old_arr['catidlineage']);
						$catNames_new = $this->addcatList($business_details_new_arr['catidlineage']);
						if($catNames_old!='')
						{
							$business_details_old_arr['catList'] = $catNames_old;
						}
						if($catNames_new!='')
						{
							$business_details_new_arr['catList'] = $catNames_new;
						}
					}
					
					if(array_key_exists('misc_flag',$business_details_old_arr) || array_key_exists('misc_flag',$business_details_new_arr) )
					{
						
						$bform_arr['Test_Contract'] = '';
						$business_details_old_arr['Test_Contract'] = (int)$business_details_old_arr['misc_flag'] & 1;
						$business_details_new_arr['Test_Contract'] = (int)$business_details_new_arr['misc_flag'] & 1;
						
					}
					
					
					if(!array_key_exists('catList_NonPaid',$bform_arr))
					{
						$bform_arr['catList_NonPaid'] = '';
						
						$catNames_nonpaid_old = $this->addcatList($business_details_old_arr['catidlineage_nonpaid']);
						$catNames_nonpaid_new = $this->addcatList($business_details_new_arr['catidlineage_nonpaid']);
						if($catNames_nonpaid_old!='')
						{
							$business_details_old_arr['catList_NonPaid'] = $catNames_nonpaid_old;
						}
						if($catNames_nonpaid_new!='')
						{
							$business_details_new_arr['catList_NonPaid'] = $catNames_nonpaid_new;
						}
					}
					
					foreach($bform_arr as $key=>$value)
					{
						if(!in_array($key,$fields_to_ignore))
						{
							if($i==0)
							{
								$updated_by = $this->get_updated_user_name($result['updated_by']);
								echo "<br>
								<table align='center' >
									<tr>
										<td class='tableTD'>Company Name</td>
										<td> : </td>
										<th align='left'  class='tableTD'>".ucwords(strtolower($result['compname']))."</th>
									</tr>
									<tr>
										<td align='right' class='tableTD'>Contract ID</td>
										<td > : </td>
										<tD  class='tableTD'>".$result['parentid']."</tD>
									</tr>
									 <tr>
										<td align='right' class='tableTD'>Time</td>
										<td > : </td>
										<tD class='tableTD'>".$result['update_time']."</tD>
									</tr>
									 <tr>
										<td align='right' class='tableTD'>Updated By</td>
										<td > : </td>
										<tD class='tableTD'>".$updated_by."</tD>
									</tr>
									<tr>
										
									</tr>
								</table>";
								
								$sql = "SELECT narration,contractid,creationdt,createdby 
										FROM tbl_paid_narration
										WHERE
										contractid = '".$result['parentid']."' AND
										DATE_FORMAT(creationdt,'%Y-%m-%d %H-%i') = DATE_FORMAT('".$result['update_time']."','%Y-%m-%d %H-%i')										
										AND narration NOT LIKE 'contract is not eligible for virtual%'									
										ORDER BY nid DESC
										LIMIT 3";
								$res = $this->conn_local->query_sql($sql);
								
								if($res && mysql_num_rows($res))
								{
									
									
									    echo "
										<br>
										<table align='center' style='border:1px solid #405366' width='700px'>
										  <tr>
											  <td class='tableTH'> Narration </td>
											  <td class='tableTH'> Date </td>
											  <td class='tableTH'> Created By </td>
										  </tr>";
									while($row_narration = mysql_fetch_assoc($res))
									{
										echo "
										  <tr>
											  <td class='tableTD'> ".$row_narration['narration']." </td>
											  <td class='tableTD'> ".$row_narration['creationdt']." </td>
											  <td class='tableTD'> ".$row_narration['createdby']." </td>
										  </tr>";
									  }
								}
					
								echo "
								<br>
										<table align='center' style='border:1px solid #405366;table-layout:fixed' width='700px'>
										<tr>
										<td colspan='4' class='tableTH' style='font-size:12px;word-wrap:break-word;'>Contract General Details</td>
										</tr>
										  <tr>
											  <td class='tableTH' style='word-wrap:break-word;'> Sr.No </td>
											  <td class='tableTH' style='word-wrap:break-word;'> Info changed </td>
											  <td class='tableTH' style='word-wrap:break-word;'> Old Value </td>
											  <td class='tableTH' style='word-wrap:break-word;'> New Value </td>
										  </tr>
									 ";
								$i++;
							}
							
							if(strtolower(trim($business_details_old_arr[$key])) != strtolower(trim($business_details_new_arr[$key])))
							{
								
								$column = $fields_abstration[$key];
								if(!$column)
								{
									$column = str_ireplace($fields_old_replace,$fields_new_replace,$key);
								}
								$column = ucwords(strtolower($column));
								//echo "<hr>".$column."-->".$business_details_old_arr[$key]." != ".$business_details_new_arr[$key];
								
								?>
									<tr>
									  <td class='tableTD'  style='word-wrap:break-word;' align='center'> <?=$i++ ?></td>
									  <td class='tableTD'  style='word-wrap:break-word;'> <?print $column ?></td>
									  <td class='tableTd'  style='word-wrap:break-word;'> 
								<?php	if($business_details_old_arr[$key]!="") {
											if(strtolower($key) == 'catlist' || $key=='catList_NonPaid' || $key=='sponcatname' || $key =='textcatname' || $key =='filtercatname' || $key == 'compsynname'){
												echo $this->formatting($business_details_old_arr[$key], $business_details_new_arr[$key], 1,$key); 
											}else{
												echo $business_details_old_arr[$key]; 
											}
										} else { 
											print '-';
										}?> 
									  </td>
									  <td class='tableTd'  style='word-wrap:break-word;'> 
								<?php	if($business_details_new_arr[$key]!="") {
											if(strtolower($key) == 'catlist' || $key=='catList_NonPaid' || $key=='sponcatname' || $key =='textcatname' || $key =='filtercatname' || $key == 'compsynname'){
												echo $this->formatting($business_details_old_arr[$key],$business_details_new_arr[$key], 2,$key);
											}else{
												echo trim($business_details_new_arr[$key],'\\\'');
											}
										} else {
											print '-';
										} ?> 
										</td>
								    </tr>
								<?php
								
							}
							
						}
						
					}
			
				}

				if(trim($result['bidding_details_old'])!='' || trim($result['bidding_details_new'])!='')
				{
					$bidOld = array();
					$bidNew = array();
					$xml 	= simplexml_load_string("<?xml version='1.0'?>".$result['bidding_details_new']);
					
					$bidOld = $this->xmlTophp($result['bidding_details_old']);
					$bidNew = $this->xmlTophp($result['bidding_details_new']);
					
					print "<tr>
							<td colspan = 4>&nbsp;</td></tr><tr><td colspan=4>
							<table align='center' style='border:1px solid #405366' width='700px'>
							<tr>
							<td colspan='4' class='tableTH' style='font-size:12px'>Contract Bidding Details</td>
							</tr>
							<tr>
								<td class='tableTH'> Sr.No </td>
								<td class='tableTH'> Info changed </td>
								<td class='tableTH'> Old Value </td>
								<td class='tableTH'> New Value </td>
							</tr>";
					
					if(count($bidNew))
					{
						$finHistory = $bidNew;
					}
					else if(count($bidOld))
					{
						$finHistory = $bidOld;
					}
					
					$fields_to_ignore_fin  = $this->field_hide(2);
					$fields_abstration_fin = $this->field_abstract(2);
					
					foreach($finHistory as $key => $value)
					{
						if($key!='@attributes')
						{
							parse_str($bidNew[$key],$valArrNew);
							parse_str($bidOld[$key],$valArrOld);
							$finalArrToShowNew[$key] = $valArrNew;
							$finalArrToShowOld[$key] = $valArrOld;
						}
					}
					$keysNew   = array_keys($finalArrToShowNew);
					$keysOld   = array_keys($finalArrToShowOld);
					$finalKeys = array_unique(array_merge($keysOld,$keysNew));
 					foreach($finalKeys as $value)
					{
						$i= 1;?>
						<tr><td colspan=4 class='tableTd'><b>Campaign ID:- <?php echo ($finalArrToShowNew[$value]['campaignid']!='')?$finalArrToShowNew[$value]['campaignid']:$finalArrToShowOld[$value]['campaignid']?></td></tr>
			<?php		foreach($finalArrToShowNew[$value] as $key1  =>  $value1)
						{
							if(!in_array($key1,$fields_to_ignore_fin))
							{
								$columnFin = $fields_abstration_fin[$key1];
								if($columnFin=='')
								{
									$columnFin = trim($key1);
								}
								$columnFin = ucwords($columnFin);
			?>
								<tr>
									  <td class='tableTD' align='center'> <?=$i++ ?></td>
									  <td class='tableTD'> <?print $columnFin ?></td>
									  <td class='tableTd'> <?php echo (trim($finalArrToShowOld[$value][$key1])!='')?$finalArrToShowOld[$value][$key1]:'-';?> </td>
									  <td class='tableTd'> <?php echo (trim($finalArrToShowNew[$value][$key1])!='')?$finalArrToShowNew[$value][$key1]:'-';; ?></td>
								    </tr>
			<?php
							}
						}
?>							<tr><td colspan=4 class='tableTd'>&nbsp;</td></tr>
<?php				}
				}?>
			</table></td></tr></table>
<?php	}
			
			function xmlTophp($xmlstr)
			{
				$xml = array();
				if(trim($xmlstr)!='')
				{
					$xmlstr = "<document>".$xmlstr."</document>";
					$xmlstr = str_replace("&"," ",$xmlstr);
					$xml 	= simplexml_load_string($xmlstr);
					foreach($xml as $key => $value)
					{
						$xml[$key] = str_replace(" ","&",$value);
					}
				}
				return $xml;
			}
			
			function formatting($value1, $value2, $status, $key_val)
			{
				if(substr($value1,0,3)=='|~|' || substr($value2,0,3)=='|~|')
				{
					$value1=explode('|~|',$value1);
					$value2=explode('|~|',$value2);
					
					$value1=array_merge(array_filter($value1));
					$value2=array_merge(array_filter($value2));
				}
				else
				{
					$value1=explode(',',$value1);
					$value2=explode(',',$value2);
				}
				if($status == 1)
				{
					$value 		 = array_diff($value1, $value2);
					$value_exist = array_diff($value1,$value);
				}
				else if($status == 2)
				{

					$value 		 = array_diff($value2, $value1);
					$value_exist = array_diff($value2,$value);
				}
				if(strtolower($key_val) == 'compsynname')
				{
					$message = 'Synonyms';
					if(count($value_exist)>0)
					{
						$new_syname_arr = array();
						foreach($value_exist as $new_syname)
						{
							$shifted_flag = 0;
							$shifted_flg_identifier = '';
							$shifted_flg_identifier = strstr($new_syname,'<*>');
							if(!empty($shifted_flg_identifier))
							{
								$shifted_flag = str_replace("<*>","",$shifted_flg_identifier);
								$new_syname = str_replace($shifted_flg_identifier,"",$new_syname);
								if($shifted_flag == 1){
									$new_syname_arr[] = $new_syname." [Shifted Company]";
								}else{
									$new_syname_arr[] = $new_syname;
								}
							}
							else
							{
								$new_syname_arr[] = $new_syname;
							}
						}
						if(count($new_syname_arr)>0){
							unset($value_exist);
							$value_exist = $new_syname_arr;
						}
					}
					if(count($value)>0)
					{
						$old_syname_arr = array();
						foreach($value as $old_syname)
						{
							$shifted_flag = 0;
							$shifted_flg_identifier = '';
							$shifted_flg_identifier = strstr($old_syname,'<*>');
							if(!empty($shifted_flg_identifier))
							{
								$shifted_flag = str_replace("<*>","",$shifted_flg_identifier);
								$old_syname = str_replace($shifted_flg_identifier,"",$old_syname);
								if($shifted_flag == 1){
									$old_syname_arr[] = $old_syname." [Shifted Company]";
								}else{
									$old_syname_arr[] = $old_syname;
								}
								
							}
							else
							{
								$old_syname_arr[] = $old_syname;
							}
						}
						if(count($old_syname_arr)>0){
							unset($value_exist);
							$value = $old_syname_arr;
						}
					}
				}
				else
				{
					$message = 'Categories';
				}
				$text=($status==2)?"<font color='green'><b>New ".$message." Added</b><br>":"<font color='red'><b>Old ".$message." Removed</b><br>";
				$value=str_replace(',','<br>',implode(',',$value_exist))."<br>".$text.str_replace(',','<br>',implode(',',$value))."</font>";
				return $value;
			}
			
			function addcatList($catidlineage)
			{
				$catNames = '';
				if(trim($catidlineage,'/')!='')
				{
					$catidlineage = trim($catidlineage,'\\\'');
					$catids		  = preg_replace("/[^ 0-9 ]/", ' ', $catidlineage);
					$catidArray	  = explode(' ',$catids);
					$catidArray	  = array_merge(array_filter($catidArray));
					$catidString  = implode(',',$catidArray);
					
					if($catidString) {
						$sqlCatName ="SELECT GROUP_CONCAT(DISTINCT a.category_name ORDER BY a.category_name) AS catName FROM tbl_categorymaster_generalinfo a join  tbl_categorymaster_parentinfo b USING (catid)  WHERE (a.catid IN (".$catidString.") ) and   ((a.mask_status=0 ) OR a.category_source = 1 OR (a.mask_status=1 AND a.display_flag&2=2) ) AND (a.biddable_type='1' OR (a.biddable_type=0 AND b.parent_flag=1)) AND a.isdeleted = 0";
						$qryCatName = $this->conn_local->query_sql($sqlCatName);
						if($qryCatName)
						{
							$rowCatName = mysql_fetch_assoc($qryCatName);
							$catNames   = $rowCatName['catName'];
						}
					}
				}
				return $catNames;
			}
			function get_updated_user_name($empcode)
			{
				$final_empname = $empcode;
				$sqlEmplogin = "SELECT CONCAT(empFName,' ',empLName) AS empname FROM emplogin WHERE empCode='".$empcode."'";
				$resEmplogin = $this->conn_iro->query_sql($sqlEmplogin);
				if($resEmplogin && mysql_num_rows($resEmplogin)>0)
				{
					$row_emplogin = mysql_fetch_assoc($resEmplogin);
					$empname = trim(ucwords(strtolower($row_emplogin['empname'])));
					if(!empty($empname))
					{
						$final_empname = $empname."(".$empcode.")";
					}
				}
				else
				{
					$sqlMktgUser = "SELECT empName FROM mktgEmpMaster WHERE mktEmpCode='".$empcode."'";
					$resMktgUser = $this->conn_local->query_sql($sqlMktgUser);
					if($resMktgUser && mysql_num_rows($resMktgUser)>0)
					{
						$row_mktguser = mysql_fetch_assoc($resMktgUser);
						$empname = trim(ucwords(strtolower($row_mktguser['empName'])));
						if(!empty($empname))
						{
							$final_empname = $empname."(".$empcode.")";
						}
					}
				}
				return $final_empname;
			}
			function resetFlag()
            {
                $this->logflag = 0;
            }
            
           function companyNameChangePaid($old_company,$new_company, $pincode_old, $pincode_new, $latitude_old, $latitude_new, $longitude_old, $longitude_new)
			{
				$compname_new	= '';
				$doc_id = '';
				$city = '';
				$data_city = '';
				$compname_new	= trim($new_company);
				$compname_new 	= preg_replace('/\s+/', '', $compname_new);
				
				$compname_new = trim($compname_new);
				
				$compname_old	= '';
				$compname_old   = trim($old_company);
				$compname_old 	= preg_replace('/\s+/', '', $compname_old);
				
				$compname_old = trim($compname_old);
				
				$selDocid = 'SELECT docid FROM db_iro.tbl_id_generator WHERE parentid="'.$this->parentid.'" ';
				$resDocid = $this->conn_iro->query_sql($selDocid);
				if($resDocid && mysql_num_rows($resDocid) > 0)
				{
					$rowDocid = mysql_fetch_assoc($resDocid);
					if($rowDocid['docid'])
					{
						$doc_id = $rowDocid['docid'];
					}
				}
				
				$selCity = 'SELECT city,data_city FROM db_iro.tbl_companymaster_generalinfo WHERE parentid="'.$this->parentid.'" ';
				$resCity = $this->conn_iro->query_sql($selCity);
				if ($resCity && mysql_num_rows($resCity) > 0)
				{
					$rowCity = mysql_fetch_assoc($resCity);
					if ($rowCity['city'])
					{
						$city = $rowCity['city'];
					}
					if ($rowCity['data_city'])
					{
						$data_city = $rowCity['data_city'];
					}
				}
				
				
				if(((strtolower($compname_new) != strtolower($compname_old))  && ($compname_new) && ($compname_old)) ||( ($pincode_old) && ($pincode_new) && ($pincode_old != $pincode_new)) || (($latitude_old) && ($latitude_new) && (doubleval($latitude_old) != doubleval($latitude_new))) || ( ($longitude_old) && ($longitude_new) && (doubleval($longitude_old) != doubleval($longitude_new))))
				{
					$sql_insertNameChange = "INSERT INTO tbl_contract_change_details SET 
											parentid				= '".$this->parentid."',
											docid					= '".$doc_id."',
											city					= '".$city."',
											data_city				= '".$data_city."',
											update_time				= '".date('Y-m-d H:i:s')."',
											updated_by				= '".$this->userid."',
											paidstatus				= '0',
											compname_old			= '".addslashes(stripslashes($old_company))."',
											compname_new			= '".addslashes(stripcslashes($new_company))."',
											done_flag				= '0' ,
											pincode_old				= '".$pincode_old."' ,
											pincode_new				= '".$pincode_new."' ,
											latitude_old			= '".$latitude_old."' ,
											latitude_new			= '".$latitude_new."' ,
											longitude_old			= '".$longitude_old."' ,
											longitude_new			= '".$longitude_new."' 											
											";
					$res_NameChange = $this->conn_iro->query_sql($sql_insertNameChange);
				}
			}	
			
			function get_curl_resp($url,$params)
			{	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
				$con  = curl_exec($ch);
				//~ echo "<pre>curl==";print_r($con);
				$r = curl_getinfo($ch);
				curl_close($ch);
				return $con;
			}

	}
?>
