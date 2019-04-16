<?php

class class_fetch_update_intermediate extends DB
{	
	
	var  $dbConFin    	= null;	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $parentid		= null;
	var  $data_city	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		$parentid 		  = trim($params['parentid']);
		$data_city 		  = trim($params['data_city']);
		
		
		$errorarray['error_code'] = 1;
		
		
		if(trim($this->params['action']) != "")
		{
			$this->action  = $this->params['action']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='action missing';
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
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['action']) == "fetchSubSource")
		{
			if($this->params['main_source'])
				$this->main_source = $this->params['main_source']; //initialize main source
			else
				{
					$errorarray['errormsg']='main_source missing';
					echo json_encode($errorarray); exit;
				}
		}
		
		if(trim($this->params['action']) == "fetchExistingNarration")
		{
			if( trim($this->params['narration_start']) && trim($this->params['narration_end']) )
			{
			   	$this -> date_cond = " AND creationDt >= '".trim($this->params['narration_start'])." 00:00:00'  AND creationDt <= '".trim($this->params['narration_end'])." 23:59:59'";
			   	
			   	if(trim($this->params['narration_pattern']) != "" && $this->params['narration_pattern'] != null && strlen(trim($this->params['narration_pattern'])) > 4 )
			   	{
					$this -> pattern_cond = " AND narration like '%".trim($this->params['narration_pattern'])."%'";
				}
			}
			
		}
		
		if(trim($this->params['action']) == "updateIntermediateData")
		{
			    $this->deactivate  = trim(strtoupper($this->params['deactivate'])); 
			
			if($this->deactivate == 'FREEZ')
			{
				$this -> freeze   = 1;
				
				$this -> deactflg = 1;
				
				$this -> temp_deactive_start 	= trim($this->params['temp_deactive_start']);
				
				$this -> temp_deactive_end   	= trim($this->params['temp_deactive_end']);
				
				$this -> freeze_reason_id	 	= trim($this->params['freeze_reason_id']);
				
				$this -> freeze_reason_value  	= trim($this->params['freeze_reason_value']);
				
				$this -> freeze_reason_comment	= trim($this->params['freeze_reason_value']).trim($this->params['freeze_reason_comment']);
							
				
			}
			else
			{
				$this -> freeze = 0;
			}
						
			$this -> addInfo			  	= trim($this->params['addInfo']);
				
			$this -> narration			  	= trim($this->params['narration']);
			
			$this -> mainsource			  	= trim($this->params['mainsource']);
			
			$this -> subsource			  	= trim($this->params['subsource']);
			
				
		}
		
		
		if(trim($this->params['trace']) != "" && $this->params['trace'] != null)
		{
			$this->trace  = $this->params['trace']; //initialize usercode
		}
		
		
		$this->setServers();		
	}		
	
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		
		$this->Idc   			= $db[$data_city]['idc']['master'];		
		$this->d_jds   			= $db[$data_city]['d_jds']['master'];		
		
		$this->db_iro 			= $db[$data_city]['iro']['master'];		
		//$this->dbConbudget      = $db[$data_city]['db_budgeting']['master'];

	}
	
	function fetchIntermediateData()
	{	
		$intermediate_data  = array();
		
		$intermediate_data['existing_temp_data']   = $this -> fetchCurrentTempData();
		
		$intermediate_data['source_data']		   = $this -> fetchSource();
		
		$intermediate_data['narration_data']	   = $this -> fetchExistingNarration();
		
		$intermediate_data['freeze_reason'] 	   = $this -> fetchFreezeReasons();
		
		$intermediate_data['existing_source_data'] = $this -> fetchExistingSource($intermediate_data['source_data']);
		
		return $intermediate_data;
	}
	
	function fetchCurrentTempData()
	{
		
		
		
		$qry = "select * from tbl_temp_intermediate where parentid = '".$this->parentid."'";
		$res =  parent::execQuery($qry, $this->d_jds);
		if( $res && mysql_num_rows($res) > 0 )
		{
			$row = mysql_fetch_assoc($res);
			
		}
		$row = preg_replace('/[^(\x20-\x7F)]*/','', $row) ;

		$sql_fetch_company_shadow_data ="SELECT companyname FROM 
										 tbl_companymaster_generalinfo_shadow
										 WHERE parentid ='".$this->parentid."' ";
		$res_fetch_company_shadow_data = parent::execQuery($sql_fetch_company_shadow_data, $this->db_iro);
		
		if($res_fetch_company_shadow_data && mysql_num_rows($res_fetch_company_shadow_data)>0)
		{
			$row_fetch_company_shadow_data = mysql_fetch_assoc($res_fetch_company_shadow_data);
			$row['companyname']  		   = $row_fetch_company_shadow_data['companyname'];
		}
		
		
		return $row;
		
	}
	
	function fetchSource()
	{
		$sqlSel= "SELECT source_id AS sCode, source_name AS sName FROM online_regis1.tbl_source_master where source_name !='' AND active_flag = 1 ORDER BY sName";
		$res =  parent::execQuery($sqlSel, $this->Idc);
		if( $res && mysql_num_rows($res) > 0 )
		{
			$source_arr = array();
			$i = 0;
			while($row = mysql_fetch_assoc($res))
			{
				//$source_arr[$row['sCode']]['sname'] = $row['sName'];
				//$source_arr[]	=	$row;
				
				$source_arr[$i]['sCode'] = $row['sCode'];
				$source_arr[$i]['sName'] = preg_replace('/[^(\x20-\x7F)]*/','', $row['sName']) ;
				$i++;
			}
			
			return $source_arr;
		}
	}
	
	function fetchSubSource()
	{
		$sqlSel= "select sub_source from online_regis1.tbl_source_master where source_id='".$this->main_source."' AND active_flag = 1";
		$res =  parent::execQuery($sqlSel, $this->Idc);
		if( $res && mysql_num_rows($res) > 0 )
		{
			$arrSubSource = array();
			$row 		  = mysql_fetch_assoc($res);
			
			$arrSubSource	= explode("|~|",$row['sub_source']);
			$arrSubSource	= array_filter($arrSubSource);
			
			$arrSubSource[] = 'Others';
			//print_r($arrSubSource);
			return $arrSubSource;
		}
	}
	
	function fetchExistingNarration()
	{
		$sqlSelect = "SELECT * FROM tbl_paid_narration WHERE (contractID  = '".$this->parentid."' or parentid  = '".$this->parentid."') ".$this -> date_cond." ".$this -> pattern_cond." ORDER BY creationdt DESC limit 5000";
		$resSelect = parent::execQuery($sqlSelect, $this->d_jds);
		if($this->trace)
		{
			echo '<br>';
			echo ' sql :: '.$sqlSelect;
			echo '<br>';
			echo ' res :: '.$resSelect;
			echo '<br>';
			echo ' rows :: '.mysql_num_rows($resSelect);
		}
		if($resSelect && mysql_num_rows($resSelect))
		{
			while($rowSelect = mysql_fetch_assoc($resSelect))
			{
				$rowSelect['narration'] = preg_replace('/[^(\x20-\x7F)]*/','', $rowSelect['narration']) ;
				$existing_narration_data[] = array( $rowSelect['createdBy'], $rowSelect['creationDt'], $rowSelect['narration'] );
			}
			
		}else
		{
			$existing_narration_data['msg'] = 'no data';
			$existing_narration_data['code'] = '404';
		}
		
		return $existing_narration_data;
		
	}
	
	function fetchFreezeReasons()
	{
		$sqlFreezeReason = "SELECT reason_id, reasons FROM tbl_freeze_reasons WHERE isactive=1";
		$resFreezeReason =  parent::execQuery($sqlFreezeReason, $this->d_jds);
		if( $resFreezeReason && mysql_num_rows($resFreezeReason) > 0 )
		{
			$freeze_reason_arr = array();
			while($rowFreezeReason = mysql_fetch_assoc($resFreezeReason))
			{
				$rowFreezeReason['reasons'] = preg_replace('/[^(\x20-\x7F)]*/','', $rowFreezeReason['reasons']) ;
				
				$freeze_reason_arr[$rowFreezeReason['reason_id']]['reason'] = $rowFreezeReason['reasons'];
			}
			
			return $freeze_reason_arr;
		}
	}
	
	function fetchExistingSource($source_data_arr)
	{
		$sqlSelect = " SELECT contactID, mainsource AS id, subsource, adsize, ad_type, pageno, datesource 
					   FROM tbl_company_source
					   WHERE contactID = '".$this->parentid."'
					   ORDER BY datesource DESC";
		$resSelect = parent::execQuery($sqlSelect, $this->d_jds);
		if($resSelect && mysql_num_rows($resSelect))
		{
			$existing_source_data['column'] = array('MainSourceId', 'MainSourceNm', 'SubSource', 'adsize', 'ad_type', 'pageno', 'datesource');
			while($rowSelect = mysql_fetch_assoc($resSelect))
			{
				$existing_source_data['data'][] = array( $rowSelect['id'], $source_data_arr[$rowSelect['id']]['sname'], $rowSelect['adsize'], $rowSelect['ad_type'], $rowSelect['pageno'], $rowSelect['datesource'] );
			}
			
		}else
		{
				$existing_source_data['msg'] = 'no data';
				$existing_source_data['code'] = '404';
		}
		
		return $existing_source_data;
 
	}
	
	function reason_func($reason_id)
	{
		$freeze_reason = $this -> fetchFreezeReasons();
		
		foreach($freeze_reason as $key => $value)
		{
			if($key == $reason_id)
			{
				$frz_rsn_val = $value['reason'];
				break;
			}	
		}	
		
		$reason_txt = "";
		switch($frz_rsn_val)
		{
		case 'Blacklist' :
				$reason_txt = $frz_rsn_val."ed Number- ".trim($this -> freeze_reason_comment);
				break;
		case 'Business Owner Decision' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Caller Complaint' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Duplicate' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Invalid/Junk/Test' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Not Contactable' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Temporary' :
				$reason_txt = $frz_rsn_val." Frozen From ".trim($this->temp_deactive_start)." To ".trim($this->temp_deactive_end);
				$tempfrzflag = 1;
				break;
		case 'Others' :
				$reason_txt = trim($this -> freeze_reason_comment);
				break;
		case 'Legal Complaint' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				break;
		case 'Not In Business' :
				$reason_txt = $frz_rsn_val;
				$frz_rsn_id = 11;
			break;
		case 'Owner Freezed' :
				$reason_txt = $frz_rsn_val;
				$frz_rsn_id = 12;
			break;
		case 'Event completed' :
				$reason_txt = $frz_rsn_val;
				$frz_rsn_id = 14;
			break;
		case 'Brand audit rejected' :
				$reason_txt = $frz_rsn_val;
				$frz_rsn_id = 15;
			break;
		case 'Duplicate without Content' :
				$reason_txt = $frz_rsn_val." - ".trim($this -> freeze_reason_comment);
				$frz_rsn_id = 16;
			break;
		case 'Documents Not Submitted' :
				$reason_txt = $frz_rsn_val;
				$frz_rsn_id = 17;
				break;						
		}
		return $reason_txt;
	}	
	
	
	function updateIntermediateData()
	{
		
		if($this->freeze == 1)
		{
			$this->reason_text = $this->reason_func($this -> freeze_reason_id);
		}
		else
		{
			$this->reason_text = "";
		}		
		
			$qry = "INSERT INTO tbl_temp_intermediate SET
					parentid 		   = 	'".$this->parentid."',
					contract_calltype  = 'B',
					displayType		   = 'IRO~WEB~WIRELESS',
					deactivate		   = '".$this->deactivate."',
					deactflg   		   = '".$this -> freeze."',
					temp_deactive_start= '".$this->temp_deactive_start."',
					temp_deactive_end  = '".$this->temp_deactive_end."',
					freez 			   = '".$this -> freeze."',
					reason_id 		   = '".$this -> freeze_reason_id."',
					reason_text		   = '".addslashes($this->reason_text)."',
					add_infotxt 	   = '".addslashes($this -> addInfo)."',
					narration          = '".addslashes($this -> narration)."',
					mainsource 		   = '".addslashes($this -> mainsource)."',
					subsource 		   = '".addslashes($this -> subsource)."',
					datesource		   = NOW()
					/*empcode = '".$empNameCode1."',
					name_code = '".$iroName2."',
					txtEmp = '".$iroName2."',
					txtTE = '".$tmExec3."',
					txtM = '".$tmMgr4."',
					txtME = '".$me5."',
					assignTmeCode = '".$_POST['assignTME']."',
					tme_code   = '".$tme_code6."',
					tme_email  = '".$tme_email6."',
					tme_mobile = '".$tme_mobile6."',
					blockforvirtual = '".$hideforvirtual."',
					/*virtual_number_hidden ='".$hideforvirtual."',
					callconnect = '".$new."',
					callconnectid = '".$_POST['callconnectid']."',
					virtualNumber = '".$_POST['virtualNumber']."',
					guarantee = '".$guarantee."',
					guarantee_reason = '".$guarantee_reason."',
					virtual_mapped_number = '".$_POST['virtual_mapped_number']."'*/

				 ON DUPLICATE KEY UPDATE

					contract_calltype  = 'B',
					displayType		   = 'IRO~WEB~WIRELESS',
					deactivate		   = '".$this->deactivate."',
					deactflg   		   = '".$this -> freeze."',
					temp_deactive_start= '".$this->temp_deactive_start."',
					temp_deactive_end  = '".$this->temp_deactive_end."',
					freez 			   = '".$this -> freeze."',
					reason_id 		   = '".$this -> freeze_reason_id."',
					reason_text		   = '".addslashes($this->reason_text)."',
					add_infotxt 	   = '".addslashes($this -> addInfo)."',
					narration          = '".addslashes($this -> narration)."',
					mainsource 		   = '".addslashes($this -> mainsource)."',
					subsource 		   = '".addslashes($this -> subsource)."',
					datesource		   = NOW()
					
					/*empcode = '".$empNameCode1."',
					name_code = '".$iroName2."',
					txtEmp = '".$iroName2."',
					txtTE = '".$tmExec3."',
					txtM = '".$tmMgr4."',
					txtME = '".$me5."',
					assignTmeCode = '".$_POST['assignTME']."',
					tme_code   = '".$tme_code6."',
					tme_email  = '".$tme_email6."',
					tme_mobile = '".$tme_mobile6."',
					/*blockforvirtual = '".$hideforvirtual."',
					/*virtual_number_hidden ='".$hideforvirtual."',
					callconnect = '".$new."',
					callconnectid = '".$_POST['callconnectid']."',
					virtualNumber = '".$_POST['virtualNumber']."',
					guarantee = '".$guarantee."',
					guarantee_reason = '".$guarantee_reason."',
					virtual_mapped_number = '".$_POST['virtual_mapped_number']."'*/
					
					";

			$res = parent::execQuery($qry, $this->d_jds);
			
			if($this->trace)
			{
				echo '<br>';
				echo ' sql :: '.$qry;
				echo '<br>';
				echo ' res :: '.$res;
			}
			
			//echo "<pre>";print_r($this->params);
			if($this->deactivate == 'FREEZ' )
				$deactflg='F';
			else
				$deactflg= $this->deactivate;
			
			$sql_upt_ext = "UPDATE tbl_companymaster_extradetails_shadow SET 
							contract_calltype   = 'B', 
							deactflg 		    = '".$deactflg."', 
							freeze 			    = '".$this -> freeze."', 
							temp_deactive_start = '".$this -> temp_deactive_start."', 
							temp_deactive_end   = '".$this -> temp_deactive_end."'						
							WHERE parentid      = '".$this->parentid."' ";
			$res_upt_ext = parent::execQuery($sql_upt_ext, $this->db_iro);
			
			if($this->trace)
			{
				echo '<br>';
				echo ' sql :: '.$sql_upt_ext;
				echo '<br>';
				echo ' res :: '.$res_upt_ext;
			}
			
			$sql_upt_gen = "UPDATE tbl_companymaster_generalinfo_shadow SET 
							displayType    ='IRO~WEB~WIRELESS'						
							WHERE parentid = '".$this->parentid."' ";
			$res_upt_gen = parent::execQuery($sql_upt_gen, $this->db_iro);
			
			
			if($this->trace)
			{
				echo '<br>';
				echo ' sql :: '.$sql_upt_gen;
				echo '<br>';
				echo ' res :: '.$res_upt_gen;
			}
			
			if($res && $res_upt_ext && $res_upt_gen)
			{
				$response['code'] = 200;
				$response['msg']  = 'success';
			}
			else
			{
				$response['code'] = 503;
				$response['msg']  = 'Updated Failed';
			}
			
			return $response;
	
	}
	
	function fetchSummaryData()
	{
		$sql_fetch_company_shadow_data ="SELECT * FROM 
										 tbl_companymaster_generalinfo_shadow a JOIN tbl_companymaster_extradetails_shadow b
										 ON a.parentid = b.parentid
										 WHERE a.parentid ='".$this->parentid."' ";
		$res_fetch_company_shadow_data = parent::execQuery($sql_fetch_company_shadow_data, $this->db_iro);
		
		if($res_fetch_company_shadow_data && mysql_num_rows($res_fetch_company_shadow_data)>0)
		{
			$row_fetch_company_shadow_data = mysql_fetch_assoc($res_fetch_company_shadow_data);
			
			$summary_data['bform_data'] = $row_fetch_company_shadow_data;
		}else
		{
			$summary_data['bform_data'] = $row_fetch_company_shadow_data;
		}
		
		
		
		if($this->trace)
		{
			echo '<br>';
			echo ' sql :: '.$sql_fetch_company_shadow_data;
			echo '<br>';
			echo ' res :: '.$res_fetch_company_shadow_data;
			echo '<br>';
			echo ' rows :: '.mysql_num_rows($res_fetch_company_shadow_data);
		}
		
		$sql_business_temp_data = "SELECT catIds FROM tbl_business_temp_data WHERE contractid  = '".$this->parentid."' ";
		$res_business_temp_data = parent::execQuery($sql_business_temp_data, $this->d_jds);
		if($this->trace)
		{
			echo '<br>';
			echo ' sql :: '.$sql_business_temp_data;
			echo '<br>';
			echo ' res :: '.$res_business_temp_data;
			echo '<br>';
			echo ' rows :: '.mysql_num_rows($res_business_temp_data);
		}
		
		if($res_business_temp_data && mysql_num_rows($res_business_temp_data))
		{
			$row_business_temp_data = mysql_fetch_assoc($res_business_temp_data);
			
			$catids_arr = explode('|P|', $row_business_temp_data['catIds']);
			
			$catids_arr = array_filter($catids_arr);
			
			if(count($catids_arr)>0)
			{
				$sql_cat = "SELECT category_name  FROM tbl_categorymaster_generalinfo WHERE catid in (".implode(",",$catids_arr).")";
				$res_cat = parent::execQuery($sql_cat, $this->d_jds);
				
				if($this->trace)
				{
					echo '<br>';
					echo ' sql :: '.$sql_cat;
					echo '<br>';
					echo ' res :: '.$res_cat;
					echo '<br>';
					echo ' rows :: '.mysql_num_rows($res_cat);
				}
				
				if($res_cat && mysql_num_rows($res_cat))
				{
					while($row_cat = mysql_fetch_assoc($res_cat))
					{
					  $category_names [] = $row_cat['category_name'];
					}
					
				}
				
				$summary_data['cat_data'] = $category_names;
			}
			
		}
		
		return $summary_data;
			
	}
	
	
	function fetchSummaryMainData()
	{
		$sql_fetch_company_shadow_data ="SELECT * FROM 
										 tbl_companymaster_generalinfo a JOIN tbl_companymaster_extradetails b
										 ON a.parentid = b.parentid
										 WHERE a.parentid ='".$this->parentid."' ";
		$res_fetch_company_shadow_data = parent::execQuery($sql_fetch_company_shadow_data, $this->db_iro);
		
		if($res_fetch_company_shadow_data && mysql_num_rows($res_fetch_company_shadow_data)>0)
		{
			$row_fetch_company_shadow_data = mysql_fetch_assoc($res_fetch_company_shadow_data);
			
			$summary_data['bform_data'] = $row_fetch_company_shadow_data;
		}else
		{
			$summary_data['bform_data'] = $row_fetch_company_shadow_data;
		}
		
		
		
		if($this->trace)
		{
			echo '<br>';
			echo ' sql :: '.$sql_fetch_company_shadow_data;
			echo '<br>';
			echo ' res :: '.$res_fetch_company_shadow_data;
			echo '<br>';
			echo ' rows :: '.mysql_num_rows($res_fetch_company_shadow_data);
		}
		
		$sql_business_temp_data = "SELECT catIds FROM tbl_business_temp_data WHERE contractid  = '".$this->parentid."' ";
		$res_business_temp_data = parent::execQuery($sql_business_temp_data, $this->d_jds);
		if($this->trace)
		{
			echo '<br>';
			echo ' sql :: '.$sql_business_temp_data;
			echo '<br>';
			echo ' res :: '.$res_business_temp_data;
			echo '<br>';
			echo ' rows :: '.mysql_num_rows($res_business_temp_data);
		}
		
		if($res_business_temp_data && mysql_num_rows($res_business_temp_data))
		{
			$row_business_temp_data = mysql_fetch_assoc($res_business_temp_data);
			
			$catids_arr = explode('|P|', $row_business_temp_data['catIds']);
			
			$catids_arr = array_filter($catids_arr);
			
			if(count($catids_arr)>0)
			{
				$sql_cat = "SELECT category_name  FROM tbl_categorymaster_generalinfo WHERE catid in (".implode(",",$catids_arr).")";
				$res_cat = parent::execQuery($sql_cat, $this->d_jds);
				
				if($this->trace)
				{
					echo '<br>';
					echo ' sql :: '.$sql_cat;
					echo '<br>';
					echo ' res :: '.$res_cat;
					echo '<br>';
					echo ' rows :: '.mysql_num_rows($res_cat);
				}
				
				if($res_cat && mysql_num_rows($res_cat))
				{
					while($row_cat = mysql_fetch_assoc($res_cat))
					{
					  $category_names [] = $row_cat['category_name'];
					}
					
				}
				
				$summary_data['cat_data'] = $category_names;
			}
			
		}
		
		return $summary_data;
			
	}
	
	
}



?>
