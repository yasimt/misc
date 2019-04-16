<?php
	require_once( APP_PATH."00_Payment_Rework/company_finance_class.php");
	class national_listing
	{
		public $parentid, $module, $remote_city,$sphinx_id, $financeObj,$paid;
		public $conn_temp, $conn_main,$conn_finance;
		private $compmaster_obj;

		function __construct($pid,$module,$dbarr,$sphinx_id)
		{
            $this->initialize($pid, $module,$sphinx_id);
            $this->setConnection($dbarr);
			$this->financeObj= new company_master_finance($dbarr,$this->parentid,$this->sphinx_id);
			if(!isset($this->compmaster_obj)){
				$this->compmaster_obj = new companyMasterClass($this->conn_server_iro,DATA_CITY,$this->parentid);
			}
		}
        function initialize($pid,$module,$sphinx_id)
        {
			$this->parentid		= $pid;
			$this->module		= $module;
			$this->sphinx_id	= $sphinx_id;
        }
		
		function setConnection($dbarr)
		{
			if(strtolower($this->module)=='cs')
			{
				$this->conn_temp 	 = new DB($dbarr['LOCAL']);
				$this->conn_finance	 = new DB($dbarr['FINANCE']);
				$this->conn_server_iro = new DB($dbarr['DB_IRO']);
			}
			else if(strtolower($this->module)=='tme')
			{
				$this->conn_temp = new DB($dbarr['DB_TME']);
				$this->conn_server_iro = new DB($dbarr['DB_IRO']);
			}
			else if(strtolower($this->module)=='me')
			{
				$this->conn_temp = new DB($dbarr['IDC']);
				$this->conn_server_iro = new DB($dbarr['DB_SERVER_IRO']);
			}
			
			$this->conn_main = new DB($dbarr['DB_NATIONAL']);
		}
		
		function createCityList($cityArr)
		{
			$citynames	= array();
			if($cityArr['zoneState']=='zone')
			{
				foreach($cityArr as $key => $values)
				{
					if(strstr($key,'checkZone'))
					{
						if($values!='' && $values!='on')
						{
							$cityNames[]=$values;
						}
					}
				}
			}else if($cityArr['zoneState']=='top')
			{
				foreach($cityArr as $key => $values)
				{
					if(strstr($key,'checkTop'))
					{
						if($values!='' && $values!='on')
						{
							$cityNames[]=$values;
						}
					}
				}
			}
			
			else if($cityArr['zoneState']=='state')
			{
				foreach($cityArr as $key => $values)
				{
					if(strstr($key,'checkState'))
					{
						if($values!='' && $values!='on')
						{
							$cityNames[]=$values;
						}
					}
				}
			}
			$i=1;
			
			if(count($cityNames)>0)
			{
				foreach($cityNames as $key=>$value)		
				{		
					$cityList .= $value . "|#|";
					$count 	=  $i;			
					$i++;				
				}
			}	
			
			$cityList=trim($cityList,'on');
			$cityList='|#|'.$cityList;
			return $cityList;
		}
		
		function insertToTempNational($cityArr,$dataCity)
		{
			$categoryCity = $this->createCityList($cityArr);
			if(trim($categoryCity)!='|#|')
			{
				$categoryCity.= $dataCity.'|#|';

				$fieldStr	= "latitude, longitude";
				$tablename	= "tbl_companymaster_generalinfo_shadow";
				$whereCond	= "parentid = '".$this->parentid."'";
				$temparr	= $this->compmaster_obj->getRow($fieldStr,$tablename,$whereCond);
				$rows		= $temparr['0']['data'];


				if($cityArr['zoneState'] == 'zone')			$statezone = 1;
				else if($cityArr['zoneState'] == 'top') 	$statezone = 3;
				else if($cityArr['zoneState'] == 'state')	$statezone = 2;
				
				$shortUrlSql = "SELECT sphinx_id, parentid, CONCAT(url_cityid, shorturl) AS url FROM tbl_id_generator WHERE shorturl IS NOT NULL AND parentid='".$this->parentid."'";
				$shortUrlQry = $this->conn_server_iro->query_sql($shortUrlSql);
				$shortUrlRow = mysql_fetch_assoc($shortUrlQry);
				
				$sqlIns = "INSERT INTO tbl_national_listing_temp SET
							parentid 			= '".$this->parentid."',					   
							Category_city 		= '".$categoryCity."',										
							contractCity 		= '".$dataCity."',
							latitude			= '".$rows['latitude']."',
							longitude			= '".$rows['longitude']."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							short_url		 	= '".$shortUrlRow['url']."'
						ON DUPLICATE KEY UPDATE
							Category_city 		= '".$categoryCity."',										
							contractCity 		= '".$dataCity."',
							latitude			= '".$rows['latitude']."',
							longitude			= '".$rows['longitude']."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							short_url		 	= '".$shortUrlRow['url']."'";
				$resIns = $this -> conn_temp -> query_sql($sqlIns);				
				return 1;
			}
			else
			{
				return 0;
			}
		}
		
		function showFromTemp($fieldArr)
		{
			$fieldStr	= implode(',',$fieldArr);
			$sql = "select ".$fieldStr." from tbl_national_listing_temp where parentid = '".$this->parentid."'";
			$qry = $this->conn_temp->query_sql($sql);
			if($qry && mysql_num_rows($qry))
			{
				$row	= mysql_fetch_assoc($qry);
 			}
			
			return $row;
 		}
		
		function showFromMain($fieldArr)
		{
			$fieldStr	= implode(',',$fieldArr);
			$sql = "select ".$fieldStr." from tbl_national_listing where parentid = '".$this->parentid."'";
			
			$qry = $this->conn_main->query_sql($sql);
			if($qry && mysql_num_rows($qry))
			{
				$row	= mysql_fetch_assoc($qry);
 			}
			
			return $row;
 		}
		
		function delFromTemp()
		{
			$budget  = $this->financeObj -> getFinanceTempData(10);
			$checkArr = array('parentid');
			$tempArr  = $this->showFromTemp($checkArr);
 			if($tempArr['parentid'] == $this->parentid)
			{
				$updateFieldArr = array();
				$updateFieldArr['budget']  = 0;
				$updateFieldArr['duration']= 0;
				$updateFieldArr['recalculate_flag']= 0;
				$this->financeObj->financeInsertUpdateTemp('10',$updateFieldArr);
			}
			
			$qrySelDel = "DELETE FROM tbl_national_listing_temp WHERE parentid='".$this->parentid."'";
			$resSelDel = $this->conn_temp->query_sql($qrySelDel);
		}
		
		function updateNationalId($categories_arr)
		{
			$fieldArr	= array();
			$valueArr	= array();
			if(count($categories_arr))
			{
				foreach($categories_arr as $key => $value)
				{
					$national_cat_arr []= $categories_arr [$key]['national_catid'];
				}
			}
			if($national_cat_arr)
			{
			$national_cat_str =implode('|P|',$national_cat_arr);
			$national_cat_str = "|P|".$national_cat_str."|P|";
			
			$totCatCnt = substr_count($national_cat_str, '|P|') - 1;
			
			$fieldArr = array('Category_nationalid','TotalCategoryWeight');
			$valueArr = array($national_cat_str,$totCatCnt);
			
			$this->updateTemp($fieldArr,$valueArr);
			
			}
		}
		
		function updateTemp($fieldArr,$valueArr)
		{
			$updateString='';
			foreach($fieldArr as $key => $value)
			{
				$updateString.= $value."='".$valueArr[$key]."',";
			}
			$updateString=trim($updateString,',');
			$sql = "UPDATE tbl_national_listing_temp SET ".$updateString." WHERE parentid = '".$this->parentid."'";
			$qry = $this->conn_temp->query_sql($sql);			 
		}
	
		function get_allocation_budget($otherCityBudget,$multiCity,$totnumdays)
		{
			$totalCallcnt 		 = array();
			$multiple_str 		 = '';
			$total_callcount_sum = 0;
			$totCount 			 = 0;
			$total_callcount_sum = 0;
			
			foreach($multiCity as $key=>$value)
			{
				$multiple_str .= ","."'".$value."'";			
			}
			$multiple_str = trim($multiple_str,',');
			if(strtolower(APP_MODULE) == 'me')
				$tableName = "city_master";
			else
				$tableName = "d_jds.city_master";
			$sql = "SELECT DISTINCT(ct_name), totalcnt FROM ".$tableName." WHERE ct_name IN (".$multiple_str.")";
			$res = $this->conn_temp->query_sql($sql);

			if($res && mysql_num_rows($res))
			{
				while($row = mysql_fetch_assoc($res))
				{
					if($row['totalcnt'] == 0 || $row['totalcnt'] == '')
					{
						$totCount = 1;
					}
					else
					{
						$totCount = $row['totalcnt'];
					}
					$total_callcount_sum += $totCount;
				}
				
				mysql_data_seek($res, 0);

				$newBuggetMC	= $otherCityBudget;
					
				while($row = mysql_fetch_assoc($res))
				{	
					$callcount_value = $this->budgetAlloc($newBuggetMC,$totnumdays);
					$cityname = ucwords(strtolower($row['ct_name']));
					$city_arr[$cityname]['budget'] = $callcount_value;
					$city_arr[$cityname]['totCityWeight'] = $total_callcount_sum;
				}
				

				return $city_arr;
			}
		}
		
		function budgetAlloc($budget,$totnumdays)
		{	
			//$contribution = round($budget/$totnumdays,4);	
			
			$contribution = round($budget/ (($totnumdays > 1095 ) ? 365 : $totnumdays ),4);	
			
			return $contribution;
		}
		
		function getCityArrayFromTable($cityStr)
		{
			$citynames 	= explode(',',trim(str_replace('|#|', ',',$cityStr),','));
			return $citynames;
		}
	
		function updateFinanceTemp($budget,$tenure,$calc=0,$version=0)
		{
			$updateFieldArr= array();
			$updateFieldArr['budget']=$budget;
			$updateFieldArr['original_budget']=$budget;
			$updateFieldArr['duration']=$tenure;
			$updateFieldArr['version']=$version;
			if($calc == 1){
				$updateFieldArr['recalculate_flag'] = 1;
			}
			$financeArr = $this->financeObj->getFinanceTempData('2');
			if($financeArr['2']['budget'] > 0)
			{
				$remPlat['budget'] = 0;
				$remPlat['duration'] = 0;
				$remPlat['original_budget'] = 0;
				$remPlat['recalculate_flag'] = 0;
				$remPlat['smartlisting_flag'] = 0;
				$this->financeObj->financeInsertUpdateTemp('2',$remPlat);
			}
			
			$this->financeObj->financeInsertUpdateTemp('10',$updateFieldArr);
		}
	
		function dataInString($seperator,$numbarray)
		{
			
			if(count($numbarray) > 0)
			{
				$numbstring=implode($seperator,$numbarray);
				return $numbstring;
			}
			else
			{
				return false;
			}
		}
		
		function insertCompDetails($genralInfoArr,$extraDetailsArr,$interMediateTable,$nonpaidflg=0)
		{
			
			$insert_into_genifo=" INSERT INTO tbl_companymaster_generalinfo_national SET
									nationalid				= '".$genralInfoArr[nationalid]."',
									sphinx_id				= '".$genralInfoArr[sphinx_id]."',
									regionid				= '".$interMediateTable[stdcode]."',
									companyname				= '".addslashes(stripslashes($genralInfoArr[companyname]))."',
									parentid				= '".$genralInfoArr[parentid]."',
									country					= '".addslashes(stripslashes($genralInfoArr[country]))."',
									state					= '".addslashes(stripslashes($genralInfoArr[state]))."',
									city					= '".addslashes(stripslashes($genralInfoArr[city]))."',
									display_city			= '".addslashes(stripslashes($genralInfoArr[display_city]))."',
									area					= '".addslashes(stripslashes($genralInfoArr[area]))."',
									subarea					= '".addslashes(stripslashes($genralInfoArr[subarea]))."',
									office_no				= '".$genralInfoArr[office_no]."',
									building_name			= '".addslashes(stripslashes($genralInfoArr[building_name]))."',
									street					= '".addslashes(stripslashes($genralInfoArr[street]))."',
									street_direction		= '".addslashes(stripslashes($genralInfoArr[street_direction]))."',
									street_suffix			= '".addslashes(stripslashes($genralInfoArr[street_suffix]))."',
									landmark				= '".addslashes(stripslashes($genralInfoArr[landmark]))."',
									landmark_custom			= '".addslashes(stripslashes($genralInfoArr[landmark_custom]))."',
									pincode					= '".$genralInfoArr[pincode]."',
									pincode_addinfo			= '".$genralInfoArr[pincode_addinfo]."',
									latitude				= '".$genralInfoArr[latitude]."',
									longitude				= '".$genralInfoArr[longitude]."',
									geocode_accuracy_level	= '".$genralInfoArr[geocode_accuracy_level]."',
									full_address			= '".addslashes(stripslashes($genralInfoArr[full_address]))."',
									stdcode					= '".$genralInfoArr[stdcode]."',
									landline				= '".$genralInfoArr[landline]."',
									landline_display		= '".$genralInfoArr[landline_display]."',
									landline_feedback		= '".addslashes(stripslashes($genralInfoArr[landline_feedback]))."',
									mobile					= '".$genralInfoArr[mobile]."',
									mobile_display			= '".$genralInfoArr[mobile_display]."',
									mobile_feedback			= '".addslashes(stripslashes($genralInfoArr[mobile_feedback]))."',
									fax						= '".$genralInfoArr[fax]."',
									tollfree				= '".$genralInfoArr[tollfree]."',
									tollfree_display		= '".$genralInfoArr[tollfree_display]."',
									email					= '".$genralInfoArr[email]."',
									email_display			= '".$genralInfoArr[email_display]."',
									email_feedback			= '".addslashes(stripslashes($genralInfoArr[email_feedback]))."',
									sms_scode				= '".addslashes(stripslashes($genralInfoArr[sms_scode]))."',
									website					= '".$genralInfoArr[website]."',
									contact_person			= '".addslashes(stripslashes($genralInfoArr[contact_person]))."',
									contact_person_display	= '".addslashes(stripslashes($genralInfoArr[contact_person_display]))."',
									othercity_number		= '".$genralInfoArr[othercity_number]."',
									paid					= '".$interMediateTable[paid]."',
									displayType				= '".$interMediateTable[displayType]."',
									data_city				= '".$genralInfoArr[data_city]."',
									mobile_admin    		= '".$genralInfoArr[mobile_admin]."'
									
								ON DUPLICATE KEY UPDATE
									
									companyname				= '".addslashes(stripslashes($genralInfoArr[companyname]))."',
									country					= '".addslashes(stripslashes($genralInfoArr[country]))."',
									state					= '".addslashes(stripslashes($genralInfoArr[state]))."',
									city					= '".addslashes(stripslashes($genralInfoArr[city]))."',
									display_city			= '".addslashes(stripslashes($genralInfoArr[display_city]))."',
									area					= '".addslashes(stripslashes($genralInfoArr[area]))."',
									subarea					= '".addslashes(stripslashes($genralInfoArr[subarea]))."',
									office_no				= '".$genralInfoArr[office_no]."',
									building_name			= '".addslashes(stripslashes($genralInfoArr[building_name]))."',
									street					= '".addslashes(stripslashes($genralInfoArr[street]))."',
									street_direction		= '".addslashes(stripslashes($genralInfoArr[street_direction]))."',
									street_suffix			= '".addslashes(stripslashes($genralInfoArr[street_suffix]))."',
									landmark				= '".addslashes(stripslashes($genralInfoArr[landmark]))."',
									landmark_custom			= '".addslashes(stripslashes($genralInfoArr[landmark_custom]))."',
									pincode					= '".$genralInfoArr[pincode]."',
									pincode_addinfo			= '".addslashes(stripslashes($genralInfoArr[pincode_addinfo]))."',
									latitude				= '".$genralInfoArr[latitude]."',
									longitude				= '".$genralInfoArr[longitude]."',
									geocode_accuracy_level	= '".$genralInfoArr[geocode_accuracy_level]."',
									full_address			= '".addslashes(stripslashes($genralInfoArr[full_address]))."',
									stdcode					= '".$genralInfoArr[stdcode]."',
									landline				= '".$genralInfoArr[landline]."',
									landline_display		= '".$genralInfoArr[landline_display]."',
									landline_feedback		= '".addslashes(stripslashes($genralInfoArr[landline_feedback]))."',
									mobile					= '".$genralInfoArr[mobile]."',
									mobile_display			= '".$genralInfoArr[mobile_display]."',
									mobile_feedback			= '".addslashes(stripslashes($genralInfoArr[mobile_feedback]))."',
									fax						= '".$genralInfoArr[fax]."',
									tollfree				= '".$genralInfoArr[tollfree]."',
									tollfree_display		= '".$genralInfoArr[tollfree_display]."',
									email					= '".$genralInfoArr[email]."',
									email_display			= '".$genralInfoArr[email_display]."',
									email_feedback			= '".addslashes(stripslashes($genralInfoArr[email_feedback]))."',
									sms_scode				= '".addslashes(stripslashes($genralInfoArr[sms_scode]))."',
									website					= '".$genralInfoArr[website]."',
									contact_person			= '".addslashes(stripslashes($genralInfoArr[contact_person]))."',
									contact_person_display	= '".addslashes(stripslashes($genralInfoArr[contact_person_display]))."',
									othercity_number		= '".$genralInfoArr[othercity_number]."',
									paid					= '".$interMediateTable[paid]."',
									displayType				= '".$interMediateTable[displayType]."',
									data_city				= '".$genralInfoArr[data_city]."',
									mobile_admin    		= '".$genralInfoArr[mobile_admin]."'" ;
			
			$result_geninfo = $this -> conn_main->query_sql($insert_into_genifo);

			$insert_into_extraDetails="INSERT INTO tbl_companymaster_extradetails_national SET
											nationalid					= '".$extraDetailsArr[nationalid]."',
											sphinx_id					= '".$extraDetailsArr[sphinx_id]."',
											regionid					= '".$interMediateTable[stdcode]."',
											companyname					= '".addslashes(stripslashes($extraDetailsArr[companyname]))."',
											parentid					= '".$extraDetailsArr[parentid]."',
											landline_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[landline_addinfo]))."',
											mobile_addinfo 				= '".addslashes(stripslashes($extraDetailsArr[mobile_addinfo]))."',
											tollfree_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[tollfree_addinfo]))."',
											contact_person_addinfo 		= '".addslashes(stripslashes($extraDetailsArr[contact_person_addinfo]))."',
											attributes 					= '".addslashes(stripslashes($interMediateTable[attributes]))."',
											attributes_edit 			= '".addslashes(stripslashes($interMediateTable[attributes_edit])) ."',
											turnover 					= '".addslashes(stripslashes($extraDetailsArr[turnover]))."',
											working_time_start 			= '".$extraDetailsArr[working_time_start]."',
											working_time_end 			= '".$extraDetailsArr[working_time_end]."',
											payment_type 				= '".$extraDetailsArr[payment_type]."',
											year_establishment 			= '".$extraDetailsArr[year_establishment]."',
											accreditations 				= '".addslashes(stripslashes($extraDetailsArr[accreditations]))."',
											certificates 				= '".$extraDetailsArr[certificates]."',
											no_employee 				= '".$extraDetailsArr[no_employee]."',
											business_group 				= '".$extraDetailsArr[business_group]."',
											email_feedback_freq 		= '".$extraDetailsArr[email_feedback_freq]."',/*Email frequency feedback*/
											statement_flag 				= '".$extraDetailsArr[statement_flag]."',
											alsoServeFlag 				= '".$interMediateTable[alsoserve]."',
											guarantee 					= '".$interMediateTable[guarantee]."',
											contract_calltype 			= '".$interMediateTable[contract_calltype] ."',
											deactflg 					= '".$interMediateTable[deactflg]."',
											display_flag 				= '".$interMediateTable[display_flag] ."',
											fmobile 					= '".$extraDetailsArr[fmobile]."',
											femail 						= '".$extraDetailsArr[femail]."',
											flgActive 					= '".$extraDetailsArr[flgActive]."',
											freeze 						= '".$interMediateTable[freez]."',
											mask 						= '".$interMediateTable[mask]."',
											hidden_flag 				= '".$interMediateTable[hidden_flag]."',
											lockDateTime 				= '".$extraDetailsArr[lockDateTime]."',
											lockedBy 					= '".addslashes(stripslashes($extraDetailsArr[lockedBy]))."',
											temp_deactive_start 		= '".$interMediateTable[temp_deactive_start]."',
											temp_deactive_end 			= '".$interMediateTable[temp_deactive_end]."',
											promptype 					= '".$extraDetailsArr[promptype]."',
											serviceName 				= '".addslashes(stripslashes($extraDetailsArr[serviceName]))."',
											createdby					= '".$extraDetailsArr[createdby]."',
											createdtime					= now(),
											original_creator			= '".$extraDetailsArr[original_creator]."',
											original_date				= now(),
											updatedBy 					= '".$extraDetailsArr[updatedBy]."',
											updatedOn 					= '".$extraDetailsArr[updatedOn]."',
											catidlineage				= '".$interMediateTable[catidLineage]."',
											catidlineage_search			= '".addslashes(stripslashes($interMediateTable[catLinSrch]))."',
											fb_prefered_language		= '".$extraDetailsArr[fb_prefered_language]."'
											
										ON DUPLICATE KEY UPDATE
											
											companyname					='".addslashes(stripslashes($extraDetailsArr[companyname]))."',
											landline_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[landline_addinfo]))."',
											mobile_addinfo 				= '".addslashes(stripslashes($extraDetailsArr[mobile_addinfo]))."',
											tollfree_addinfo 			= '".addslashes(stripslashes($extraDetailsArr[tollfree_addinfo]))."',
											contact_person_addinfo		= '".addslashes(stripslashes($extraDetailsArr[contact_person_addinfo]))."',
											attributes 					= '".addslashes(stripslashes($interMediateTable[attributes]))."',
											attributes_edit 			= '".addslashes(stripslashes($interMediateTable[attributes_edit]))."',
											turnover 					= '".$extraDetailsArr[turnover]."',
											working_time_start 			= '".$extraDetailsArr[working_time_start]."',
											working_time_end 			= '".$extraDetailsArr[working_time_end]."',
											payment_type 				= '".$extraDetailsArr[payment_type]."',
											year_establishment 			= '".$extraDetailsArr[year_establishment]."',
											accreditations 				= '".addslashes(stripslashes($extraDetailsArr[accreditations]))."',
											certificates 				= '".addslashes(stripslashes($extraDetailsArr[certificates]))."',
											no_employee 				= '".$extraDetailsArr[no_employee]."',
											business_group 				= '".addslashes(stripslashes($extraDetailsArr[business_group]))."',
											email_feedback_freq 		= '".addslashes(stripslashes($extraDetailsArr[email_feedback_freq]))."',
											statement_flag 				= '".$extraDetailsArr[statement_flag]."',
											alsoServeFlag 				= '".$interMediateTable[alsoserve]."',
											guarantee 					= '".$interMediateTable[guarantee]."',
											contract_calltype 			= '".$interMediateTable[contract_calltype] ."',
											createdby 					= '".addslashes(stripslashes($extraDetailsArr[createdby]))."',
											createdtime 				= '".$extraDetailsArr[createdtime]."',
											deactflg 					= '".$interMediateTable[deactflg]."',
											display_flag 				= '".$interMediateTable[display_flag] ."',
											fmobile 					= '".$extraDetailsArr[fmobile]."',
											femail 						= '".$extraDetailsArr[femail]."',
											flgActive 					= '".$extraDetailsArr[flgActive]."',
											freeze 						= '".$interMediateTable[freez]."',
											mask 						= '".$interMediateTable[mask]."',
											hidden_flag 				= '".$interMediateTable[hidden_flag]."',
											lockDateTime 				= '".$extraDetailsArr[lockDateTime]."',
											lockedBy 					= '".addslashes(stripslashes($extraDetailsArr[lockedBy]))."',
											temp_deactive_start 		= '".$interMediateTable[temp_deactive_start]."',
											temp_deactive_end 			= '".$interMediateTable[temp_deactive_end]."',
											promptype 					= '".$interMediateTable[promptype]."',
											serviceName 				= '".addslashes(stripslashes($interMediateTable[serviceName]))."',
											updatedBy 					= '".addslashes(stripslashes($extraDetailsArr[updatedBy]))."',
											updatedOn 					= '".$extraDetailsArr[updatedOn]."',
											catidlineage				= '".$interMediateTable[catidLineage]."',
											catidlineage_search			= '".addslashes(stripslashes($interMediateTable[catLinSrch]))."',
											fb_prefered_language		= '".$extraDetailsArr[fb_prefered_language]."'";
		
					$result_ext_details = $this -> conn_main ->query_sql($insert_into_extraDetails);	
		
			
			/*-------------- creating Array for Phone search display---------------------------*/
			if(trim($genralInfoArr[mobile_display]))
				$phone_searchArr[]=trim($genralInfoArr[mobile_display]);
			if(trim($genralInfoArr[landline_display]))
				$phone_searchArr[]=trim($genralInfoArr[landline_display]);
			if(trim($genralInfoArr[tollfree_display]))
				$phone_searchArr[]=trim($genralInfoArr[tollfree_display]);
			if(trim($genralInfoArr[fax]))
				$phone_searchArr[]=trim($genralInfoArr[fax]);
			if(trim($genralInfoArr[virtualNumber]))
				$phone_searchArr[]=trim($genralInfoArr[virtualNumber]);


			$phone_search	=	$this->dataInString(',',$phone_searchArr);
			$address 		= 	$genralInfoArr[fulladdress].",".$genralInfoArr[city].",".$genralInfoArr[state];
			
			$insert_comp_srch="INSERT INTO tbl_companymaster_search_national SET
									nationalid						= '".$extraDetailsArr[nationalid]."',
									sphinx_id						= '".$extraDetailsArr[sphinx_id]."',
									regionid						= '".$interMediateTable[stdcode]."',
									companyname						= '".addslashes($extraDetailsArr[companyname])."',
									parentid						= '".$extraDetailsArr[parentid]."',
									companyname_search				= '".addslashes($extraDetailsArr[companyname_search])."',
									companyname_search_area			= '".addslashes($extraDetailsArr[companyname_search_area])."',
									companyname_search_stem			= '".addslashes($extraDetailsArr[companyname_search_stem])."',
									companyname_search_WS			= '".addslashes($extraDetailsArr[companyname_search_WS])."',
									companyname_search_stem_WS	    = '".addslashes($extraDetailsArr[companyname_search_stem_WS])."',
									latitude						= '".$genralInfoArr[latitude]."',
									longitude						= '".$genralInfoArr[longitude]."',
									state							= '".$genralInfoArr[state]."',
									city							= '".addslashes($genralInfoArr[city])."',
									pincode							= '".$genralInfoArr[pincode]."',
									phone_search					= '".$phone_search."',
									address							= '".addslashes($address)."',
									contact_person					= '".addslashes($extraDetailsArr[contact_person_display])."',
									email							= '".$genralInfoArr[email_display]."',
									website							= '".$genralInfoArr[website]."',
									catidlineage_search				= '".$interMediateTable[catLinSrch]."',
									length							= '".strlen($genralInfoArr[companyname])."',
									display_flag					= '".$extraDetailsArr[display_flag]."',
									prompt_flag						= '".$interMediateTable[prompt_flag]."',
									paid							= '".$interMediateTable[paid]."',
									updatedBy						= '".$extraDetailsArr[updatedBy]."',
									updatedOn						= '".$extraDetailsArr[updatedOn]."'
							
							ON DUPLICATE KEY UPDATE
							
									companyname						= '".addslashes($genralInfoArr[companyname])."',
									companyname_search				= '".addslashes($extraDetailsArr[companyname_search])."',
									companyname_search_area			= '".addslashes($extraDetailsArr[companyname_search_area])."',
									companyname_search_stem			= '".addslashes($extraDetailsArr[companyname_search_stem])."',
									companyname_search_WS			= '".addslashes($extraDetailsArr[companyname_search_WS])."',
									companyname_search_stem_WS		= '".addslashes($extraDetailsArr[companyname_search_stem_WS])."',
									latitude						= '".$genralInfoArr[latitude]."',
									longitude						= '".$genralInfoArr[longitude]."',
									state							= '".$genralInfoArr[state]."',
									city							= '".$genralInfoArr[city]."',
									pincode							= '".$genralInfoArr[pincode]."',
									phone_search					= '".$phone_search."',
									address							= '".addslashes($address)."',
									contact_person					= '".$extraDetailsArr[contact_person_display]."',
									email							= '".$genralInfoArr[email_display]."',
									website							= '".$genralInfoArr[website]."',
									catidlineage_search				= '".$interMediateTable[catLinSrch]."',
									length							= '".strlen($extraDetailsArr[companyname])."',
									display_flag					= '".$extraDetailsArr[display_flag]."',
									prompt_flag						= '".$interMediateTable[prompt_flag]."',
									paid							= '".$interMediateTable[paid]."',
									updatedBy						= '".$extraDetailsArr[updatedBy]."',
									updatedOn						= '".$extraDetailsArr[updatedOn]."'";
								
				
			$result_comp_srch = $this -> conn_main->query_sql($insert_comp_srch);	
				
				
			$qry = "select * from tbl_business_temp_data where contractid='".$this->parentid."'";
			$result_bid_details	= $this -> conn_temp->query_sql($qry);
			$Row_tbl_business_temp_data = mysql_fetch_assoc($result_bid_details);
			
			if(!$Row_tbl_business_temp_data['bid_timing'])
			$Row_tbl_business_temp_data['bid_timing'] = '00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59,00:00 23:59';
			
			$bid_timing             = $Row_tbl_business_temp_data['bid_timing'];
		
			$contract_type			=	'package';
			$campaignid				=	'10';
			
/*
			$qrycampndetails = "INSERT INTO tbl_companymaster_campaigns_national
									SET															
										regionid		= '".$interMediateTable[stdcode]. "',
										companyname		= '".addslashes(stripslashes($extraDetailsArr[companyname])). "',
										parentid		= '".$this->parentid. "',
										contract_type	= '".$contract_type."',
										campaignid		= '".$campaignid."',
										contractid		= '".$contractidBid. "'											
										
									ON DUPLICATE KEY UPDATE
									
										regionid		= '".$interMediateTable[stdcode]. "',
										companyname		= '".addslashes(stripslashes($extraDetailsArr[companyname])). "',
										contract_type	= '".$contract_type."',
										campaignid		= '".$campaignid."',
										contractid		= '".$contractidBid. "'";
														
			$resultcampndetails = $this -> conn_main->query_sql($qrycampndetails);
*/
			
			$this->insertCatDetails($genralInfoArr[data_city],$nonpaidflg);

		}
		
		function insertGeoCodeDetails($latitude,$longitude,$geocode_accuracy_level)
		{
			$sqlGenInfoGeoCode = "UPDATE tbl_companymaster_generalinfo_national
									SET
										latitude		 			= '".$latitude."',
										longitude		 			= '".$longitude."',
										geocode_accuracy_level 		= '".$geocode_accuracy_level."'
									WHERE 
											parentid 				= '".$this->parentid. "'";

			$resGenInfoGeoCode = $this -> conn_main->query_sql($sqlGenInfoGeoCode);

			$sqlCompMasterSearchGeoCode="UPDATE  tbl_companymaster_search_national 
										SET
											latitude		 			= '".$latitude."',
											longitude		 			= '".$longitude."'
										WHERE 
											parentid 					= '".$this->parentid. "'";

			$resCompMasterSearchGeoCode = $this -> conn_main->query_sql($sqlCompMasterSearchGeoCode);
		}

		function insertCatDetails($data_city,$nonpaid=0)
		{
			$fieldArr=array('*');
			$row_sel	= $this -> showFromTemp($fieldArr);
			if($nonpaid	== 1){
				$qry= ",approval_flag = 1";
				$paidflg = 0;
			}else{
				$paidflg = 1;
			}
			
			$sql="INSERT INTO tbl_national_listing 
						SET
							parentid 				='".$row_sel['parentid']."',
							Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
							Category_nationalid		='".$row_sel['Category_nationalid']."',
							TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
							totalcityweight			='".$row_sel['totalcityweight']."',
							contractCity			='".addslashes(stripslashes($data_city))."',
							ContractStartDate		='".$row_sel['ContractStartDate']."',
							ContractTenure			='".$row_sel['ContractTenure']."',
							dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
							WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
							latitude				='".$row_sel['latitude']."',
							longitude				='".$row_sel['longitude']."',
							iroCard					='".$row_sel['iroCard']."',
							lastupdate				='".$row_sel['lastupdate']."',
							update_flag				='0',
							data_city				='".$data_city."',
							state_zone				='".$row_sel['state_zone']."',
							paid					='".$paidflg."',
							short_url				='".$row_sel['short_url']."'".$qry."
							
						ON DUPLICATE KEY UPDATE
							
							Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
							Category_nationalid		='".$row_sel['Category_nationalid']."',
							TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
							totalcityweight			='".$row_sel['totalcityweight']."',
							contractCity			='".addslashes(stripslashes($data_city))."',
							ContractStartDate		='".$row_sel['ContractStartDate']."',
							ContractTenure			='".$row_sel['ContractTenure']."',
							dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
							WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
							latitude				='".$row_sel['latitude']."',
							longitude				='".$row_sel['longitude']."',
							iroCard					='".$row_sel['iroCard']."',
							lastupdate				='".$row_sel['lastupdate']."',
							update_flag				='0',
							data_city				='".$data_city."',
							state_zone				='".$row_sel['state_zone']."',
							paid					='".$paidflg."',
							short_url				='".$row_sel['short_url']."'".$qry;
							
			$res = $this -> conn_main ->query_sql($sql);				 
		}
		
		function insertTempCatDetails($nonpaid=0)
		{
			$sql_sel = "select * from tbl_national_listing where parentid='".$this -> parentid."'";		
			$res_sel = $this -> conn_main -> query_sql($sql_sel);
			
			$sqlDel  = "DELETE FROM tbl_national_listing_temp WHERE parentid='".$this -> parentid."'";
			$qryDel	 = $this -> conn_temp -> query_sql($sqlDel);
			//$finNationalMain = $this->financeObj->getFinanceMainData(10);
			if($res_sel && mysql_num_rows($res_sel))
			{
				$row_sel = mysql_fetch_assoc($res_sel);
				$sql="INSERT INTO tbl_national_listing_temp 
							SET
								parentid 				='".$row_sel['parentid']."',
								Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
								Category_nationalid		='".$row_sel['Category_nationalid']."',
								TotalCategoryWeight	    ='".$row_sel['TotalCategoryWeight']."',
								totalcityweight			='".$row_sel['totalcityweight']."',
								contractCity			='".addslashes(stripslashes($row_sel['contractCity']))."',
								ContractStartDate		='".$row_sel['ContractStartDate']."',
								ContractTenure			='".$row_sel['ContractTenure']."',
								dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
								WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
								latitude				='".$row_sel['latitude']."',
								longitude				='".$row_sel['longitude']."',
								iroCard					='".$row_sel['iroCard']."',
								state_zone				='".$row_sel['state_zone']."',
								lastupdate				='".$row_sel['lastupdate']."',
								short_url				='".$row_sel['short_url']."'
								
							ON DUPLICATE KEY UPDATE
								
								Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
								Category_nationalid		='".$row_sel['Category_nationalid']."',
								TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
								totalcityweight			='".$row_sel['totalcityweight']."',
								contractCity			='".addslashes(stripslashes($row_sel['contractCity']))."',
								ContractStartDate		='".$row_sel['ContractStartDate']."',
								ContractTenure			='".$row_sel['ContractTenure']."',
								dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
								WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
								latitude				='".$row_sel['latitude']."',
								longitude				='".$row_sel['longitude']."',
								iroCard					='".$row_sel['iroCard']."',
								state_zone				='".$row_sel['state_zone']."',
								lastupdate				='".$row_sel['lastupdate']."',
								short_url				='".$row_sel['short_url']."'";
								
				$res = $this -> conn_temp ->query_sql($sql);	
				
			}
		}
	
		function mainTableApproval($tablename,$fieldArr,$valueArr)
		{
			$updateString='';
			foreach($fieldArr as $key => $value)
			{
				$updateString.= $value."='".$valueArr[$key]."',";
			}
			$updateString=trim($updateString,',');
			
			$updateTab = "UPDATE ".$tablename." SET  ".$updateString." WHERE parentid = '" . $this->parentid . "'";
            $this->conn_main->query_sql($updateTab);
		}
		
		function delEntryMain()
		{
			$fieldArr[] = 'parentid';
			$count = $this->showFromTemp($fieldArr);
			if($count==0)
			{
				$sqlDel	= "DELETE FROM tbl_national_listing WHERE parentid = '".$this->parentid."'";
				$qryDel	= $this->conn_main->query_sql($sqlDel);
			}
		}
		
		function nationalskip($city,$budget,$tenure){
			
			$tempArr	= array();
			$cityArr	= array();
			$allocbudget= array();
			
			$fieldArr	= array("Category_city");
			$tempArr	= $this->showFromTemp($fieldArr);
			if(count($tempArr)>0){
				$cityArr	= explode("|#|",trim($tempArr['Category_city'],"|#|"));
				foreach($cityArr as $key => $value){
					$cityArr[$key] = strtolower($value);
				}
				$key_unset	= array_search(strtolower($city),$cityArr);
				
				unset($cityArr[$key_unset]);
				$cityArr	= array_merge(array_filter($cityArr));
				if(count($cityArr)){
					foreach($cityArr as $key => $value){
						$cityArr[$key] = ucwords($value);
					}					
					$city_str	= "|#|".implode("|#|",$cityArr)."|#|";
				}
				$allocbudget	= $this->get_allocation_budget($budget,$cityArr,$tenure);
				foreach($allocbudget as $key => $value)
				{			
					$budgetAlloc	= $value['budget'];
					$cityWeight		= $value['totCityWeight'];
				}
				$fieldArr	= array('Category_city','totalcityweight','dailyContribution','ContractTenure');
				$valueArr	= array($city_str,$cityWeight,$budgetAlloc,$tenure);
				$this -> updateTemp($fieldArr,$valueArr);
			}
		}
	}
?>
