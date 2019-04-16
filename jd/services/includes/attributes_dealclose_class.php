<?php
ob_start();
ini_set('max_execution_time', 0); 
class attributes_dealclose_class extends DB{
	var  $conn_local   	= null;
	var  $conn_iro   	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	function __construct($params){		
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= trim($params['data_city']);
		$action 		= trim($params['action']);
		$ucode 			= trim($params['ucode']);		
		$uname 			= trim($params['uname']);		
		$source			= trim($params['source']);		
		if(trim($parentid)==''){
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)==''){
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)==''){
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($ucode)==''){
			$message = "ucode is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($uname)==''){
			$message = "uname is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($action)==''){
			$message = "Action is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$actionArr = array("add_remove_attr");
		if(!in_array($action,$actionArr)){
			$message = "Invalid Action.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->docid ='';
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->params 		= $params;
		$this->module  	  	= strtoupper($module);
		$this->ucode		= $ucode;
		$this->uname		= $uname;
		$this->source		= $module;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		$this->docid       = trim($this->getDocid());
		$this->nonpaid_catids = array();
		$this->nonpaid_catids = $this->fetchNonpaidCategories();
		
	}		
	function setServers(){	
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];		
		if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_temp_fin 	= $this->conn_iro;
			$this->conn_temp_attr 	= $this->conn_iro;
		}elseif(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_temp_fin 	= $this->conn_tme;
			$this->conn_temp_attr 	= $this->conn_idc;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
		}elseif((strtoupper($this->module) =='ME') || (strtoupper($this->module) =='JDA')){
			$this->conn_temp		= $this->conn_idc;
			$this->conn_temp_fin 	= $this->conn_idc;			
			$this->conn_temp_attr 	= $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
		}else{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}
	}
	function getDocid(){
		$sel_docid = "SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_docid = parent::execQuery($sel_docid, $this->conn_iro);
		if($res_docid && parent::numRows($res_docid)>0){
			$row_docid= parent::fetchData($res_docid);
			if($row_docid[docid]!=''){
				$docid = $row_docid['docid'];
			}
		}
		return $docid;
	}
	function add_remove_attributes_new(){
		$contract_final_cat_arr = array();
		
		$temp_old_attr_data   = array();		
		$attribute_search_old = '';
		
		$temp_new_attr_data   = array();						
		$attribute_search_new = '';
		
		$old_attributes   	  = $this->getOldAttributes(); 
		$temp_old_attr_data   = $old_attributes['tbl_companymaster_attributes_temp'];		
		$attribute_search_old = $old_attributes['tbl_companymaster_extradetails_shadow']['attribute_search'];
		
		//get details of attributes to be added based on categories
		$row_data		  = $this->fetchBusinessTempData();	
		$paidcatids		  = $row_data['catIds'];
		$temp_catlin_arr  = array_filter(array_unique(explode('|P|',$paidcatids)));			
		$temp_catlin_arr  = $this->fetch_valid_categories($temp_catlin_arr);		
		$contract_final_cat_arr = array_unique(array_merge($temp_catlin_arr,$this->nonpaid_catids));
		if(count($contract_final_cat_arr)<=0){
			$row_data         = $this->fetchBusinessLiveData();
			$paidcatids		  = $row_data['catids'];			
			$live_catlin_arr = explode("/,/",trim($paidcatids,"/"));
			$temp_catlin_arr  = $this->fetch_valid_categories($live_catlin_arr);		
			$contract_final_cat_arr = array_unique(array_merge($temp_catlin_arr,$this->nonpaid_catids));
		}
		
		//~ echo "<pre>row_data:--";print_r($row_data);	
		
		//get details of attributes to be removed based on categories removed
		$removedCategories = $this->getRemovedCategories();		
		$categories    	   =  explode('|P|',$removedCategories['removed_categories']);					
		$categories        = $this->fetch_valid_categories($categories);		
		$ids_to_remove     = array_unique($this->getUniqueCodes($categories));
		//~ echo "<pre>ids_to_remove:--";print_r($ids_to_remove); 
		
		//deletion of attributes based on removed categories case
		if(count($ids_to_remove)>0){ 
			//echo "<pre>ids_to_remove:--inside count--";print_r($ids_to_remove);
			$ids = '';
			$ids = implode("','",$ids_to_remove);
			if($ids!=''){
				$delEntry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."' AND attribute_id IN ('".$ids."')";
				$resEntry = parent::execQuery($delEntry, $this->conn_temp);					
				if($attribute_search_old!=''){
					$updated_search_arr = array();
					$attribute_search_existing_arr = array_unique(array_filter(explode("#",$attribute_search_old)));
					foreach($attribute_search_existing_arr as $unCode=>$vvvalue){						
						if(!in_array($vvvalue, $ids_to_remove)){
							array_push($updated_search_arr,$vvvalue);
						}
					}					
					//echo "<pre>updated_search_arr:--";print_r($updated_search_arr);
					$updated_search_arr = array_unique($updated_search_arr);
					if(count($updated_search_arr)>0){
						$attr_search_imp = implode('#',$updated_search_arr);
						if($this->mongo_flag == 1 || $this->mongo_tme == 1){
							$mongo_inputs = array();
							$mongo_inputs['parentid'] 	= $this->parentid;
							$mongo_inputs['data_city'] 	= $this->data_city;
							$mongo_inputs['module']		= $this->module;
							$mongo_data  = array();									
							$extrdet_tbl = "tbl_companymaster_extradetails_shadow";									
							$extrdet_upt['attribute_search'] 	= '#'.$attr_search_imp.'#';
							$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;				
							
							$mongo_inputs['table_data'] = $mongo_data;
							$resUpdateAttributes 		= $this->mongo_obj->updateData($mongo_inputs);	
						}else{				
							$sqlUpdateAttributesExt = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='#".$attr_search_imp."#'  WHERE parentid = '".$this->parentid."'";
							$resUpdateAttributes 	= parent::execQuery($sqlUpdateAttributesExt, $this->conn_temp_fin);
						}
					}
				}
				
				$new_attributes   	  = $this->getOldAttributes(); 			
				$temp_new_attr_data   = $new_attributes['tbl_companymaster_attributes_temp'];			
				$attribute_search_new = $new_attributes['tbl_companymaster_extradetails_shadow']['attribute_search'];
			
				$attributes_search_old_json =  json_encode(addslashes(stripslashes($attribute_search_old)));	
				$attributes_seacrh_new_json =  json_encode(addslashes(stripslashes($attribute_search_new)));							
				$temp_attr_new_json 				=  addslashes(stripslashes(json_encode($temp_new_attr_data)));							
				$temp_attr_old_json 				=  addslashes(stripslashes(json_encode($temp_old_attr_data)));								
				$InsertToHistory ="INSERT INTO online_regis1.tbl_attributes_changed_log SET
													 parentid				= '".$this->parentid."',
													 data_city				= '".$this->data_city."',
													 catid 					= '".implode("|P|",$contract_final_cat_arr)."',
													 attributes_old			= '',
													 attributes_new 		= '',
													 attributes_edit_old	= '',
													 attributes_edit_new	= '', 
													 attributes_search_old	= '".$attributes_search_old_json."', 
													 attributes_search_new	= '".$attributes_seacrh_new_json."',
													 temp_attr_new			= '".$temp_attr_new_json."',
													 temp_attr_old			= '".$temp_attr_old_json."',
													 update_time			= '".date('Y-m-d H:i:s')."',
													 updated_by				= '".$this->ucode."',
													 source 			    = '".trim($this->source)."_delete_dealclose'";		
				$resLogHistory = parent::execQuery($InsertToHistory, $this->conn_idc);
				
				$updateQry = "UPDATE online_regis1.tbl_removed_categories SET removed_categories='' WHERE parentid='".$this->parentid."'"; 
				$resEntry 	 = parent::execQuery($updateQry, $this->conn_idc);
			}		
		}
		
		//addition of attributes based on categories case				
		//~ echo "<pre>contract_final_cat_arr:--";print_r($contract_final_cat_arr);
		if(count($contract_final_cat_arr)>0){			
			$data = $this->getMappedAttributes($contract_final_cat_arr);
			//~ echo "<pre>data:--";print_r($data);
			
			$mappedAttributes = $data['mapped_attributes'];
			$attribute_group  = $data['attribute_groups'];		
			$array_keys       = array_unique(array_keys(array_filter($mappedAttributes)));	
			
			//echo "<pre>array_keys:--";print_r($array_keys);
			if(count($array_keys)>0 && count($attribute_group)>0){
				$uniqueCodeFin = array();
				foreach($array_keys as $attr=>$attVal){ //comma separated mapped_attrs in categorymaster_gen_info
					$attrArr = array_unique(explode(",",$attVal));
					foreach($attrArr as $k1=>$v1){
						if(!in_array($v1,$uniqueCodeFin)){
							array_push($uniqueCodeFin,$v1);
						}							
					}						
				}
				$unique_codes 	      = implode("','",$uniqueCodeFin);										
				$attribute_group_imp  = implode("','",$attribute_group);	
				$getSubGrp = "SELECT * FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$attribute_group_imp."') ORDER BY sub_group_pos ASC";
				$resSubGrp = parent::execQuery($getSubGrp, $this->conn_idc);
				if($resSubGrp && parent::numRows($resSubGrp)>0){
					while($rowSubGrp = parent::fetchData($resSubGrp)){
						$sub_grp_final_arr[$rowSubGrp['id']] = $rowSubGrp;
						if(!in_array($rowSubGrp['id'],$subGrpArr) && $rowSubGrp['id']!=0){
							$subGrpArr[] = $rowSubGrp['id'];
						}
					}
				}
				if($unique_codes!=''){					
					if(count($mainattr_arr1)>0){
						if($whereCond!=''){
							$whereCond .= " WHERE  attribute_group IN ('".$attribute_group_imp."') AND (unique_code in ('".$unique_codes."') )";
						}else{
							$whereCond = " WHERE attribute_group IN ('".$attribute_group_imp."') AND (unique_code in ('".$unique_codes."') )";
						}
					}else{
						$whereCond = " WHERE attribute_group IN ('".$attribute_group_imp."') AND unique_code in ('".$unique_codes."') ";
					}
				}else{
					$whereCond = " WHERE attribute_group IN ('".$attribute_group_imp."')";
				}
				if(count($subGrpArr)>0){					
					$whereCond .= " AND a.selection_flag=0 AND a.active_flag=1 AND a.national_catid=0 AND a.attribute_sub_group IN ('".implode("','" , $subGrpArr)."')  ORDER BY FIELD (a.attribute_sub_group,'".implode("','" , $subGrpArr)."'),a.display_position ASC";
				}
				
				$UnCodes = $this->getMappedAttributesBasedonGrp($attribute_group_imp); //deleting diffrent attribute group uniquecodes if present any				
				if(count($UnCodes)>0){
					$UnCodesStr = implode("','",$UnCodes);					
					$delEntry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."' AND attribute_id NOT IN ('".$UnCodesStr."')";
					//~ echo "<pre>delEntry:--".$delEntry;
					$resEntry = parent::execQuery($delEntry, $this->conn_temp);
				}
				//~ die("267");
				$get_details = " select * from (
				SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,a.filter_flag,b.attribute_type,a.main_attribute_flag as main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) $whereCond 
				) t GROUP BY unique_code";		
				//~ echo "<pre>get_details:--".$get_details; 
				
				$res_details = parent::execQuery($get_details, $this->conn_local);
				$uniuqCodeArr= array();

				if($res_details && parent::numRows($res_details)>0){
					while($row_details = parent::fetchData($res_details)){												
						if(!in_array($row_details['attribute_sub_group'],$attribute_sub_grp)){
							$attribute_sub_grp[]  = $row_details['attribute_sub_group'];
						}															
						if($row_details['display_flag']==0 && $row_details['filter_flag']==1 && !in_array($row_details['unique_code'],$ids_to_remove)){													
							$attribute_search_arr[$row_details['unique_code']][$row_details['attribute_sub_group']] = $row_details['attribute_name'];
							
						}
						else if($row_details['display_flag']==1 && $row_details['filter_flag']==0 && !in_array($row_details['unique_code'],$ids_to_remove)){														
							$attribute_details[$row_details['unique_code']] = $row_details;		
							$sub_grp_details[$row_details['unique_code']]   = $row_details['attribute_sub_group'];	
							$uniuqCodeArr[$row_details['unique_code']] 			= $row_details['attribute_name'].'~~~';	
							
						}
						else if(($row_details['display_flag']==1 && $row_details['filter_flag']==1 && !in_array($row_details['unique_code'],$ids_to_remove)) || ($row_details['display_flag']==2 && $row_details['filter_flag']==1 && !in_array($row_details['unique_code'],$ids_to_remove)) ){
							$attribute_search_arr[$row_details['unique_code']][$row_details['attribute_sub_group']]    = $row_details['attribute_name'];			
							$attribute_details[$row_details['unique_code']] = $row_details;		
							$sub_grp_details[$row_details['unique_code']]   = $row_details['attribute_sub_group'];	
							$uniuqCodeArr[$row_details['unique_code']] 			= $row_details['attribute_name'].'~~~';	
						}
					}					
				}
				
				//echo "<pre>sub_grp_final_arr:--";print_r($sub_grp_final_arr);
				//~ echo "<br>";die("in file");
				if(count($attribute_details)>0 && count($sub_grp_final_arr)>0){					
					$insert = ''; $insert1 = '';	
					$insert = "INSERT IGNORE INTO tbl_companymaster_attributes_temp (docid,parentid, city, attribute_name,attribute_dname, attribute_value, attribute_type, attribute_sub_group,sub_group_name,display_flag,sub_group_position,attribute_position,attribute_id,attribute_prefix,main_attribute_flag,main_attribute_position) VALUES ";
														
					foreach($attribute_details as $unique_code=>$attr_values){
						$attr_values 	= 	$this->addslashesArray($attr_values);
						$display_flag   = '';$sub_grp_name = '';
						if($unique_code!='' && $attr_values['attribute_name']!=''){														
							$attribute_name = '';
							$attribute_dname = ''; 
							$attribute_value_arr    = explode("~~~",$uniuqCodeArr[$unique_code]);								
							$attribute_sub_group    = $attr_values['attribute_sub_group'];
							$attribute_sub_group	= $attr_values['subgroup_web'];							
							$sub_grp_name  		    = $sub_grp_final_arr[$attribute_sub_group]['subgroup_name']; 							
							if(strtolower($sub_grp_name) == 'establishment type'){
								$sub_grp_name = 'Type';
							}
							if($attr_values['attribute_dname']!=''){
								$attribute_dname	= $attr_values['attribute_dname'];
							}
							else if(($attr_values['attribute_dname']=='' || $attr_values['attribute_dname']==null) && ($attr_values['attribute_type']==2 || $attr_values['attribute_type']==3 || $attr_values['attribute_type']==4 || $attr_values['attribute_type']==5 || $attr_values['attribute_type']==6) && $attr_values['attr_type_fin']==1){
								
								if(strtolower($attr_values['attribute_name'])!=strtolower($sub_grp_name) && ($attr_values['attribute_type']!=4 || $attr_values['attribute_type']!='4')){									
									$attribute_dname = $attr_values['attribute_name'].'-'.rtrim($attribute_value_arr['1'],",");
								}else if($attr_values['attribute_type']==4){ //cehckbox
									$attribute_dname = $attr_values['attribute_name'];
								}
								else{
									$attribute_dname = rtrim($attribute_value_arr['1'],",");
								}
							}else if(($attr_values['attribute_dname']=='' || $attr_values['attribute_dname']==null) && ($attr_values['attribute_type']==2 || $attr_values['attribute_type']==3 || $attr_values['attribute_type']==4 || $attr_values['attribute_type']==5 || $attr_values['attribute_type']==6) && $attr_values['attr_type_fin']==2){
								if($attr_values['attribute_type']==4){ //checkbox
									$attribute_dname = $attr_values['attribute_name'];
								}else{
									$attribute_dname = rtrim($attribute_value_arr['1'],",");
								}								
							}else if ($attr_values['attribute_type']=='1'){
								if($attr_values['input_Value']!=''){ //this is handling is fr radio buttons only ruleset given by ratan									
									$input_value_arr = json_decode(stripslashes($attr_values['input_Value']),true);
									$input_value_lwr = array_change_key_case($input_value_arr,CASE_LOWER);										
									$attribute_dname = $input_value_lwr[strtolower($attribute_value_arr['1'])];
									
								}else{
									$attribute_dname = $attribute_value_arr['1'];
								}
							}
							else{
								$attribute_dname	= $attr_values['attribute_name'];
							}
							
							$attribute_name	= $attr_values['attribute_name'];
							if($attr_values['display_flag']!=1){
								$display_flag = 0;
							}else{
								$display_flag = $attr_values['display_flag'];
							}
							$insert1 .= "('".$this->docid."', '".$this->parentid."', '".$this->data_city."', '".$attribute_name."', '".$attribute_dname."', '".$attribute_value_arr['1']."', '".$attr_values['attr_type_fin']."', '".$attribute_sub_group."', '".$sub_grp_name."', '".$display_flag."', '".$sub_grp_final_arr[$attr_values['attribute_sub_group']]['sub_group_pos']."', '".$attr_values['attribute_web_position']."', '".$unique_code."', '".$attr_values['attribute_prefix']."' , '".$attr_values['main_attribute_flag']."' , '".$attr_values['display_position']."')" .",";							
						}
					}						
					if($insert1!=''){
						$insert1  = rtrim($insert1,",");				
						$fin      = $insert.$insert1;					
						//~ echo "<pre>fin:---".$fin; //die;
						$res_temp = parent::execQuery($fin, $this->conn_temp);
					}
				}
				$new_attributes       = array();
				$attribute_search     = array();
				$new_attributes   	  = $this->getOldAttributes(); 			
				$temp_new_attr_data   = $new_attributes['tbl_companymaster_attributes_temp'];			
				$attribute_search_new = $new_attributes['tbl_companymaster_extradetails_shadow']['attribute_search'];				
				$attributes_search_old_json =  json_encode(addslashes(stripslashes($attribute_search_old)));					
				$temp_attr_new_json 				=  addslashes(stripslashes(json_encode($temp_new_attr_data)));							
				$temp_attr_old_json 				=  addslashes(stripslashes(json_encode($temp_old_attr_data)));	
				
				if(count($temp_new_attr_data)>0){
					foreach($temp_new_attr_data as $key=>$value){
						if(!in_array($value[attribute_id],$attribute_search)){
							array_push($attribute_search,$value[attribute_id]);
						}
					}										
				}
				if(count($attribute_search_arr)>0){
					$extra_codes_to_added = array_keys($attribute_search_arr);
					//~ echo "<pre>extra_codes_to_added:--";print_r($extra_codes_to_added);
					if(count($extra_codes_to_added)>0){
						$attribute_search = array_merge($attribute_search, $extra_codes_to_added);
					} 
				}
				
				
				//~ echo "<pre>attribute_search:--";print_r($attribute_search);
				//~ echo "<pre>attribute_search_arr:--";print_r($attribute_search_arr);
				if(count($attribute_search)>0){
					$attr_group_imp = implode("','",$attribute_search);
					$getAttr = "SELECT unique_code, attribute_group, attribute_sub_group,attribute_name FROM d_jds.tbl_attribute_mapping WHERE unique_code IN ('".$attr_group_imp."')  AND active_flag=1 AND assign_to_all =1 GROUP BY unique_code";				
					//~ echo "<pre>getAttr:--".$getAttr; 		
					$resAttr = parent::execQuery($getAttr, $this->conn_local);
					if($resAttr && parent::numRows($resAttr)>0){
						$final_attr_search = array();
						while($rowAttr = parent::fetchData($resAttr)){						
							if(!in_array($rowAttr['unique_code'],$final_attr_search)){
								array_push($final_attr_search, $rowAttr['unique_code']);
							}
						}
					}
				}
				
				if(count($final_attr_search)>0){
					$attr_search_imp = implode('#',$final_attr_search);
					if($this->mongo_flag == 1 || $this->mongo_tme == 1){
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_data  = array();									
						$extrdet_tbl = "tbl_companymaster_extradetails_shadow";									
						$extrdet_upt['attribute_search'] 	= '#'.$attr_search_imp.'#';
						$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;				
						
						$mongo_inputs['table_data'] = $mongo_data;
						$resUpdateAttributes 		= $this->mongo_obj->updateData($mongo_inputs);	
					}else{				
						$sqlUpdateAttributesExt = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='#".$attr_search_imp."#'  WHERE parentid = '".$this->parentid."'";
						$resUpdateAttributes 	= parent::execQuery($sqlUpdateAttributesExt, $this->conn_temp_fin);
					}
				}		
				$attributes_seacrh_new_json =  json_encode(addslashes(stripslashes($attr_search_imp)));							//echo "<pre>attribute_search:--";print_r($attribute_search);
				$InsertToHistory ="INSERT INTO online_regis1.tbl_attributes_changed_log SET
													 parentid				= '".$this->parentid."',
													 data_city				= '".$this->data_city."',
													 catid 					= '".implode("|P|",$contract_final_cat_arr)."',
													 attributes_old			= '',
													 attributes_new 		= '',
													 attributes_edit_old	= '',
													 attributes_edit_new	= '', 
													 attributes_search_old	= '".$attributes_search_old_json."', 
													 attributes_search_new	= '".$attributes_seacrh_new_json."',
													 temp_attr_new			= '".$temp_attr_new_json."',
													 temp_attr_old			= '".$temp_attr_old_json."',
													 update_time			= '".date('Y-m-d H:i:s')."',
													 updated_by				= '".$this->ucode."',
													 source 			    = '".trim($this->source)."_addition_dealclose'";		
				$resLogHistory = parent::execQuery($InsertToHistory, $this->conn_idc);				
			}
		}else{ 
			//no categories
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data  = array();									
				$extrdet_tbl = "tbl_companymaster_extradetails_shadow";									
				$extrdet_upt['attribute_search'] 	= '';
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;				
				
				$mongo_inputs['table_data'] = $mongo_data;
				$resUpdateAttributes 		= $this->mongo_obj->updateData($mongo_inputs);	
			}else{				
				$sqlUpdateAttributesExt = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search=''  WHERE parentid = '".$this->parentid."'";
				$resUpdateAttributes 	= parent::execQuery($sqlUpdateAttributesExt, $this->conn_temp_fin);
			}
			$delEntry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."' $andCondition"; //die("620");
			$resEntry = parent::execQuery($delEntry, $this->conn_temp);	
		}
		//~ echo "<pre>sqlUpdateAttributesExt:--".$sqlUpdateAttributesExt;
		if($resUpdateAttributes){
			$respArr['errorCode'] = 0;
			$respArr['errorStatus'] = 'Success';
			$respArr['attribute_search'] = $attr_search_imp;
			return $respArr;
		}
	}
	
	
	function getMappedAttributesBasedonGrp($attrGrp){
		$unCodes = array();
		$getUnCode = "SELECT unique_code,attribute_group from tbl_attribute_mapping where attribute_group in ('".$attrGrp."') GROUP BY unique_code";		
		$resUnCode = parent::execQuery($getUnCode, $this->conn_local);
		if($resUnCode && parent::numRows($resUnCode)>0){
			while($rowUnCode = parent::fetchData($resUnCode)){				
				if(!in_array($rowUnCode['unique_code'],$unCodes)){
					array_push($unCodes,$rowUnCode['unique_code']);
				}				
			}
		}
		return $unCodes;		
	}
	
	function getRemovedCategories(){	
		$result_arr = array();
		$select     = "SELECT removed_categories, parentid FROM online_regis1.tbl_removed_categories  WHERE parentid='".$this->parentid."' AND removed_categories!=''";
		$result     = parent::execQuery($select, $this->conn_idc);
		if($result && parent::numRows($result)>0){
			$row = parent::fetchData($result);	
			$result_arr = $row;
		}
		return $result_arr;
	}	
	
	function getUniqueCodes($catid_List){			
		$data_arr = array();
		$data = $this->getMappedAttributes($catid_List);		
		$mapped_attrs      = $data['mapped_attributes'];
		$attribute_groups  = $data['attribute_groups'];				
		if(count($mapped_attrs)>0 && count($attribute_groups)>0){
			$array_keys    = array_unique(array_keys(array_filter($mapped_attrs)));	
			$uniqueCodeFin = array();
			foreach($array_keys as $attr=>$attVal){ //comma separated mapped_attrs in categorymaster_gen_info
				$attrArr = array_unique(explode(",",$attVal));
				foreach($attrArr as $k1=>$v1){
					if(!in_array($v1,$uniqueCodeFin)){
						array_push($uniqueCodeFin,$v1);
					}							
				}						
			}				
			$unique_codes 	      = implode("','",$uniqueCodeFin);			
			$attribute_group_imp  = implode("','",$attribute_groups);	
			if($unique_codes!='' || $attribute_group_imp!=''){	
				$whereCond .= " WHERE  attribute_group IN ('".$attribute_group_imp."') AND (unique_code in ('".$unique_codes."') )";				
			}
			$get_details = "SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM (SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM d_jds.tbl_attribute_mapping $whereCond ) as t group by unique_code";			
			$res_details   = parent::execQuery($get_details, $this->conn_local);
			if($res_details && parent::numRows($res_details)>0){				
				while($row_details = parent::fetchData($res_details)){											
					if(!in_array($row_details['unique_code'],$data_arr)){							
						array_push($data_arr,$row_details['unique_code']);
					}
				}
			}
		}			
		return $data_arr;
	}
	
	function getMappedAttributes($catid_List){		
		$data		= array();	
		$cat_params = array();
		$cat_params['page'] ='attribute_dealclose_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name,mapped_attribute,attribute_group';
		$cat_params['orderby']		= 'attribute_group ASC';

		$where_arr  	=	array();			
		$where_arr['catid']			= implode(",",$catid_List);
		$cat_params['where']		= json_encode($where_arr);
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}	
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}				
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
			$mapped_attrs	  = array();
			$attribute_groups = array();
			foreach($cat_res_arr['results'] as $key=>$cat_arr){					
				if($cat_arr['mapped_attribute']!=''){
					$mapped_attrs[$cat_arr['mapped_attribute']] = $cat_arr['mapped_attribute'];
				}
				if(!in_array($cat_arr['attribute_group'], $attribute_groups)){
					$attribute_groups[] = $cat_arr['attribute_group'];
				}
			}
		}
		$data['mapped_attributes'] = $mapped_attrs;
		$data['attribute_groups'] = $attribute_groups;
		return $data;
	}
	function recursiveRemoval(&$array, $val){
		if(is_array($array)){
			foreach($array as $key=>&$arrayElement)
			{
				if(is_array($arrayElement))
				{
					$this->recursiveRemoval($arrayElement, $val);
				}
				else
				{
					if($arrayElement == $val)
					{
						unset($array[$key]);
					}
				}
			}
		}		
		return $array;
	}
	function getOldAttributes(){		
		$tempdata	    = array();	
		$mainData 		= array();
		$result 		= array();
		$row_temp_data  = array();
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['fields'] 	= "parentid,data_city,attribute_search";			
			$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$getShadowEntry    = "SELECT parentid,data_city,attribute_search FROM tbl_companymaster_extradetails_shadow WHERE parentid='".$this->parentid."'";
			$resExtradetShadow = parent::execQuery($getShadowEntry, $this->conn_temp_fin);
			if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
				$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
				$row_temp_data      = $row_extrdet_shadow;
			}	
		}
				
		$getEntry = "SELECT docid, parentid, city, attribute_name, attribute_dname,attribute_value, attribute_sub_group, sub_group_name, attribute_id FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'";
		$resEntry = parent::execQuery($getEntry, $this->conn_temp);
		if($resEntry && parent::numRows($resEntry)>0){
			while($rowEnrty = parent::fetchData($resEntry)){
				$tempdata[] = $rowEnrty;
			}
			
		}		
		$result['tbl_companymaster_attributes_temp'] = $tempdata;		
		$result['tbl_companymaster_extradetails_shadow'] = $row_temp_data;
		return $result;				
	}
	function fetchNonpaidCategories(){
		$tempdata 		    = array();
		$temp_catlin_np_arr = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['module']       	= $this->module;
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_extradetails_shadow"=>"parentid,catidlineage_nonpaid",				
			));
				
			$tempdata = $this->mongo_obj->getShadowData($mongo_inputs);
		}else{						
			$sqlExtradetShadow  = "SELECT parentid,catidlineage_nonpaid FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$this->parentid."'";
			$resExtradetShadow = parent::execQuery($sqlExtradetShadow, $this->conn_temp_fin);
			if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
				$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
				$tempdata['tbl_companymaster_extradetails_shadow'] = $row_extrdet_shadow;
			}			
		}
		$temp_catlin_np_arr = explode("/,/",trim($tempdata['tbl_companymaster_extradetails_shadow']['catidlineage_nonpaid'],"/"));
		$temp_catlin_np_arr = $this->getValidCategories($temp_catlin_np_arr);		
		return $temp_catlin_np_arr;
	}
	function getValidCategories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function fetchBusinessTempData(){
		$row_temp_data = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";			
			$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlBusinessTempData = "SELECT catIds FROM tbl_business_temp_data WHERE contractid = '" . $this->parentid . "'";
			$resBusinessTempData = parent::execQuery($sqlBusinessTempData, $this->conn_temp);
			if($resBusinessTempData && parent::numRows($resBusinessTempData)>0)
			{
				$row_temp_data	=	parent::fetchData($resBusinessTempData);
			}
		}
		return $row_temp_data;
	}
	function fetchBusinessLiveData(){
		$sqlBusinessLiveData = "SELECT catidlineage as catids, attribute_search as attribute_search FROM tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		//~ echo "<pre>sqlBusinessLiveData:--".$sqlBusinessLiveData;
		$resBusinessLiveData = parent::execQuery($sqlBusinessLiveData, $this->conn_iro);
		if($resBusinessLiveData && parent::numRows($resBusinessLiveData)>0)
		{
			return $row_live_data	=	parent::fetchData($resBusinessLiveData);
		}
	}
	
	function getRemovedAttributes(){
		$attribute_search_arr = array();
		$attribute_edit_arr   = array();
		$removed_cat_arr      = array();				
		$result_arr 		  = array();
		$attribute_sub_grp	  = array();
		$select = "SELECT removed_categories, parentid FROM online_regis1.tbl_removed_categories  WHERE parentid='".$this->parentid."' AND removed_categories!=''";
		$result = parent::execQuery($select, $this->conn_idc);
		if($result && parent::numRows($result)>0){
			$removedCategories = parent::fetchData($result);	
			$removed_cat_arr =  explode('|P|',$removedCategories['removed_categories']);					
			$removed_cat_arr = 	$this->fetch_valid_categories($removed_cat_arr);
			$catid_List      = implode("','", $removed_cat_arr);
			$get_attrs = "SELECT catid, category_name, mapped_attribute,attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_List."') AND attribute_group > 0";
			$res_attrs   = parent::execQuery($get_attrs, $this->conn_local);
			if($res_attrs && parent::numRows($res_attrs)>0){
				$mapped_attrs     = array();
				$attribute_groups = array();
				while($row_attr   = parent::fetchData($res_attrs)){
					if(!in_array($row_attr['mapped_attribute'],$mapped_attrs) && $row_attr['mapped_attribute']!=''){
						$mapped_attrs[] = $row_attr['mapped_attribute'];
					}
					if(!in_array($row_attr['attribute_group'], $attribute_groups)){
						$attribute_groups[] = $row_attr['attribute_group'];
					}
				}				
				
				$uniqueCodeFin 		  = array();
				foreach($mapped_attrs as $attr=>$attVal){
					$attrArr = array_unique(explode(",",$attVal));
					foreach($attrArr as $k1=>$v1){
						if(!in_array($v1,$uniqueCodeFin)){
							array_push($uniqueCodeFin,$v1);
						}							
					}						
				}
				$unique_codes 	      = implode("','",$uniqueCodeFin);					
				
				$attribute_group_imp  = implode("','",$attribute_groups);	
				if($unique_codes!='' || $attribute_group_imp!=''){	
					$whereCond .= " WHERE  attribute_group IN ('".$attribute_group_imp."') AND (unique_code in ('".$unique_codes."') )";				
				}
				$get_details = "SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM (SELECT attribute_name, unique_code, attribute_group, attribute_sub_group, display_flag, filter_flag,active_flag FROM d_jds.tbl_attribute_mapping $whereCond ) as t group by unique_code";
				$res_details = parent::execQuery($get_details, $this->conn_local);	
				if($res_details &&  parent::numRows($res_details)>0){
					while($row_details = parent::fetchData($res_details)){						
						if(!in_array($row_details['attribute_sub_group'],$attribute_sub_grp)){
							$attribute_sub_grp[]  = $row_details['attribute_sub_group'];
						}															
						if($row_details['display_flag']==0 && $row_details['filter_flag']==1){													
							$attribute_search_arr[$row_details['unique_code']][$row_details['attribute_sub_group']] = $row_details['attribute_name'];
							
						}else if($row_details['display_flag']==1 && $row_details['filter_flag']==0){							
							$attribute_edit_arr[$row_details['attribute_sub_group']][$row_details['attribute_name']]  = $row_details['attribute_name'].'~~~';
							
						}else if(($row_details['display_flag']==1 && $row_details['filter_flag']==1) || ($row_details['display_flag']==2 && $row_details['filter_flag']==1)){							
							$attribute_edit_arr[$row_details['attribute_sub_group']][$row_details['attribute_name']]  = $row_details['attribute_name'].'~~~';
							$attribute_search_arr[$row_details['unique_code']][$row_details['attribute_sub_group']]    = $row_details['attribute_name'];			
						}						
					}
				}
			}
		}
		$result_arr['attribute_edit_arr']   = $attribute_edit_arr;
		$result_arr['attribute_search_arr'] = $attribute_search_arr;
		return $result_arr;
	}
	
	function fetch_valid_categories($total_catlin_arr){
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	function addslashesArray($resultArray){
		foreach($resultArray AS $key=>$value){
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		return $resultArray;
	}
}
