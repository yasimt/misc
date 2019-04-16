<?php
ob_start();

class attribute_page_class extends DB
{
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
		if(trim($action)==''){
			$message = "Action is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$actionArr = array("check_attr","fetchattr","updateattr");
		if(!in_array($action,$actionArr)){
			$message = "Invalid Action.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->params 		= $params;
		$this->module  	  	= strtoupper($module);
		$this->ucode		= $ucode;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		
		if($this->params['live_data'] == 1){
			$this->business_data = $this->fetchBusinessLiveData();
		}else{
			$this->business_data = $this->fetchBusinessTempData();
		}
		$this->add_catlin_nonpaid_db = 0;
		if((strtoupper($this->module) == 'DE') || (strtoupper($this->module) == 'CS')){
			$this->add_catlin_nonpaid_db = 1;
		}
		$this->contract_paid_cat_arr 	= array();		
		if($this->params['live_data'] == 1)	{
			$this->getContractLiveCategories();
		}else{
			$this->getContractTempCategories();
		}	
			
		
		
	}
		
	// Function to set DB connection objects
	function setServers(){	
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_catmaster 	= $this->conn_local;
		}elseif(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}elseif((strtoupper($this->module) =='ME') || (strtoupper($this->module) =='JDA')){
			$this->conn_temp		= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
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
		$docid = '';
		$sel_docid = "SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_docid = parent::execQuery($sel_docid, $this->conn_iro);
		if($res_docid && parent::numRows($res_docid)>0){
			$row_docid= parent::fetchData($res_docid);
			if($row_docid[docid]!=''){
				$docid = $row_docid[docid];
			}
		}
		return $docid;
	}
	function get_vertical_flag(){
		
		if(count($this->contract_paid_cat_arr) > 0){			
			$str_catids = implode("','",$this->contract_paid_cat_arr);	
			$get_attribute_grp = "SELECT DISTINCT(attribute_group) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0  ORDER BY attribute_group ASC";
			$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);
			if($res_attribute_grp && parent::numRows($res_attribute_grp)>0){
				while($row_attribute_grp  = parent::fetchData($res_attribute_grp)){
					if($row_attribute_grp['attribute_group']!='' || $row_attribute_grp['attribute_group']!=0){
						$attribute_group[] = $row_attribute_grp['attribute_group'];
					}
				}
				$attribute_group_ex = array_filter($attribute_group);
				$this->attribute_group_ex = array_unique($attribute_group_ex);
				if(count($attribute_group_ex) == 1){  
					$this->decide_flag = $attribute_group;
					$this->attribute_group = implode("','",$attribute_group_ex);					
				}else{					
					$this->decide_flag = $attribute_group;
					$this->attribute_group = implode("','",$attribute_group_ex);					
				}	
			}else{
				$this->decide_flag = 0;
			}			
		}else{
			$this->decide_flag = 0;
		}		
		$sub_group_details_arr = array();				
		$sub_grps = array();		
		$name_sub_grp_arr = array();
		$name_sub_id_arr = array();
		if(count($this->attribute_group_ex)>0){			
			$str = implode("','",$this->attribute_group_ex);
			$get_sub_heading  = "SELECT * FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$str."') ORDER BY sub_group_pos ASC"; //online_regis1
			$res_sub_heading  = parent::execQuery($get_sub_heading, $this->conn_idc); //take idc connection, only this table .$this->conn_idc 
			if($res_sub_heading && parent::numRows($res_sub_heading)>0){
				$qry_cond = '';
				while($row_sub_heading  = parent::fetchData($res_sub_heading)){										
					$attribute_group = $row_sub_heading['attribute_group'];
					if(!in_array($row_sub_heading['id'],$sub_grps)){
						$sub_grps[] = $row_sub_heading['id'];
						$name_sub_grp_arr[$row_sub_heading['subgroup_name']]  = $row_sub_heading['id'];
						$name_sub_id_arr['-'.$row_sub_heading['id']]              = $row_sub_heading['subgroup_name'];
					}
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['subgroup_name']   = $row_sub_heading['subgroup_name'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['attribute_group'] = $row_sub_heading['attribute_group'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['sub_group_pos']   = $row_sub_heading['sub_group_pos'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['id']              = $row_sub_heading['id'];
				}
				if(count($sub_group_details_arr)>0){
					$this->sub_group_details_arr = $sub_group_details_arr;
					$this->sub_grps				 = $sub_grps;
					$this->name_sub_grp_arr		 = $name_sub_grp_arr;
					$this->name_sub_id_arr		 = $name_sub_id_arr;
				}
				
			}			
		}else{
			//return no attributes found here
			if($this->params['live_data'] != 1){
				
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
					
					$bustemp_tbl 		= "tbl_business_temp_data";
					$bustemp_upt = array();
					$bustemp_upt['mainattr'] 				= '';
					$bustemp_upt['facility'] 				= '';
					$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
					
					$mongo_inputs['table_data'] 			= $mongo_data;
					$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
				}
				else
				{
					if($this->mongo_tme == 1){
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_data = array();
						
						$bustemp_tbl 		= "tbl_business_temp_data";
						$bustemp_upt = array();
						$bustemp_upt['mainattr'] 				= '';
						$bustemp_upt['facility'] 				= '';
						$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
						
						$mongo_inputs['table_data'] 			= $mongo_data;
						$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
					}
					
					$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','','') ON DUPLICATE KEY UPDATE mainattr='',facility=''";
					$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";
					$resUpdateAttributes = parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
				}
			}
			$message = "Attribute mapped category not found.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
	}	
	function check_att_pre(){
		$this->get_vertical_flag();
		if(count($this->attribute_group_ex)<=0){	
			$result_msg_arr = array();
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg']  = "Attribute Mapped categories are not found";			
			return $result_msg_arr;
		}else{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg']  = "Success";			
			return $result_msg_arr;
		}
	}
	function getAttributesInfo(){
			
		$this->get_vertical_flag();	
		$disable_array = array();
		$res		   = array();
		$resutl_array  = array();
		$data_array    = array();			
		$exists        = $this->processExistingAttributes();			
		$existing_attribute_arr = $exists['old_attributes_arr'];	
		$res = implode(',',$disable_array);
		
		$str_imp_sub = implode("','",$this->sub_grps); 	
		$get_attr_names = "SELECT a.attribute_group,a.attribute_sub_group, a.unique_code, a.attribute_name,a.unique_code, a.display_position, a.gen_sepl_type AS attr_type_fin, b.attribute_type ,a.selection_flag,a.active_flag,b.input_Value,a.attribute_sub_group,a.attribute_group FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$this->attribute_group."')  AND a.selection_flag=0 AND a.active_flag=1  AND a.assign_to_all=1  AND a.display_flag!=0 AND  a.national_catid=0  AND  a.attribute_sub_group IN ('".$str_imp_sub."') ORDER BY FIELD (a.attribute_sub_group,'".$str_imp_sub."'),a.display_position ASC";	
					
		$res_attr_names = parent::execQuery($get_attr_names, $this->conn_local);
		$count_of_total = parent::numRows($res_attr_names);
		if($res_attr_names && parent::numRows($res_attr_names)>0){
			while($row_attr_names = parent::fetchData($res_attr_names)){ //attribute_type i.e 1-radio 2-drop down 3-Time 4-Checkbox 5 -text  6-multi select				
				$attr_grp = $row_attr_names['attribute_group']; 
				$attribute_sub_group = '-'.$row_attr_names['attribute_sub_group'];								
				$get_sub_name        = $this->name_sub_id_arr[$attribute_sub_group];				
				if($attribute_sub_group!=''){					
					//$data_array[$get_sub_name][] = $row_attr_names;
					$data_array[$attribute_sub_group][] = $row_attr_names;
				}
			}
		}		
		
		$existing_attrs = array();
		$unique_array   = array();
		if(count($data_array)>0){				
			foreach($data_array as $sub_heading=>$values){		
				$sub_heading = strtolower($sub_heading);	
				
				$index=0;	
				$jIndex = 0;		
				foreach($values as $key=>$value_arr1){						
					$attribute_group_fin 	= $value_arr1['attribute_group'];
					$attribute_name 		= $value_arr1['attribute_name'];
					$unique_code			= $value_arr1['unique_code'];
					$display_position 		= $value_arr1['display_position'];
					$attribute_type			= $value_arr1['attribute_type'];
					$input_Value			= $value_arr1['input_Value'];
					$display_position_arr[]  = $display_position;
					if(!in_array($value_arr1['unique_code'], $unique_array)){
						
						switch($attribute_type){						
							case '1' : 													
								$type = 'rad';
								$selectval = '';
								$resutl_array[$sub_heading][$index]['dpos']   =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe']   			  =  $type;
								$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde']        =  $unique_code;																
								$radio_arr = json_decode($input_Value,true);
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){									
									$resutl_array[$sub_heading][$index]['selval'] 	    =  $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['selval'] 	=  $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;	
									$key_as    = array_keys($radio_arr);
									$key_as1 = array();
									foreach($key_as as $k1=>$va1){									
										$key_as1[] = strtolower($va1);
									}
									$existing_attrs[$sub_heading][$jIndex]['options']     =  $key_as1;		
									$jIndex++;
								}	
								$key_as    = array_keys($radio_arr);
								$key_as1 = array();
								foreach($key_as as $k1=>$va1){									
									$key_as1[] = strtolower($va1);
								}
								$resutl_array[$sub_heading][$index]['options'] 	 	 =  $key_as1;	
								
							break;							
							case '2' :
								$type = 'sel';								
								$resutl_array[$sub_heading][$index]['dpos']   =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe']   			  =  $type;
								$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde']        =  $unique_code;
								$input_Value_a   = json_decode($input_Value,true);
								$input_Value_a   = array_merge($input_Value_a);
								$input_Value_aRR = array_values($input_Value_a);
								$selval = '';
								$selectval = '';
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){
									$selectval = $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];		
									$existing_attrs[$sub_heading][$jIndex]['selval'] 	=  $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;							
									$existing_attrs[$sub_heading][$jIndex]['selval'] 	   =  $selectval;
									$existing_attrs[$sub_heading][$jIndex]['options']       =  $input_Value_aRR;
									$jIndex++;
								}								
								$resutl_array[$sub_heading][$index]['selval'] 	       =  $selectval;
								$resutl_array[$sub_heading][$index]['options']         =  $input_Value_aRR;
								
							break;
							
							case '3' : 								
								$input_values_arr = array();
								$input_values     = trim($input_Value);								
								$input_values_arr = explode(",",$input_values);
								$input_values_arr = array_merge(array_unique(array_filter($input_values_arr)));		
								if(count($input_values_arr)>0){
									$type = 'tfrmto';
									$resutl_array[$sub_heading][$index]['dpos'] =  $display_position;
									$resutl_array[$sub_heading][$index]['tpe'] =  $type;
									$timingfrmstr = '';
									$timingtostr = '';
									if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){ //dis is fr existing values in contract
										$timingval = $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
										$timingvalarr = array();
										//$timingvalarr = explode("TO",$timingval);
										$timingvalarr = explode("TO",$timingval);
										$timingvalarr = array_map('trim',$timingvalarr);
										$timingvalarr = array_merge(array_unique(array_filter($timingvalarr)));										
										if(count($timingvalarr) == 2){
											$timingfrmstr = $timingvalarr[0];
											$timingtostr = $timingvalarr[1];
										}										
										$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
										$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
										$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
										$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;
										$existing_attrs[$sub_heading][$jIndex]['selfrm']		=  $timingfrmstr;
										$existing_attrs[$sub_heading][$jIndex]['selto'] 		=  $timingtostr;	
										$jIndex++;
									}
									$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
									$resutl_array[$sub_heading][$index]['uCde']       =  $unique_code;
									$resutl_array[$sub_heading][$index]['selfrm'] =  $timingfrmstr;
									$resutl_array[$sub_heading][$index]['selto'] =  $timingtostr;	
								}else{									
									$type = 'tim';
									$resutl_array[$sub_heading][$index]['dpos'] =  $display_position;
									$resutl_array[$sub_heading][$index]['tpe'] =  $type;
									$timeval = '';									
									if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){ //existing values in contract
										$timeval = $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];			
										$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
										$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
										$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
										$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;
										$existing_attrs[$sub_heading][$jIndex]['selval']     = $timeval;
										$jIndex++;
									}
									$resutl_array[$sub_heading][$index]['selval']           = $timeval;
									$resutl_array[$sub_heading][$index]['aNm']   =  $attribute_name;
									$resutl_array[$sub_heading][$index]['uCde'] 	    =  $unique_code;
								}
							break;
							
							case '4' : 
								$type = 'chk';
								$singlechksel = '0ff';
								
								$resutl_array[$sub_heading][$index]['dpos']   =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe']   			  =  $type;
								$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde']        =  $unique_code;
								$disable_array1 = explode(",",$res);								
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){
									$singlechksel = 'on';
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;									
									$existing_attrs[$sub_heading][$jIndex]['selval']     =  $singlechksel;	
									$jIndex++;
								}
								$resutl_array[$sub_heading][$index]['selval'] =  $singlechksel;							
							break;							
							case '5' : 
								$type = 'txt';
								$resutl_array[$sub_heading][$index]['dpos'] =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe'] =  $type;
								$txtval = '';								
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){
									$txtval = $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$resutl_array[$sub_heading][$index]['selval'] 		     =  $txtval;
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;									
									$existing_attrs[$sub_heading][$jIndex]['selval']     =  $txtval;
									$jIndex++;
								}								
								
								$resutl_array[$sub_heading][$index]['aNm'] 	 =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde'] 	     =  $unique_code;								
							break;
							
							case '6' :								
								$resutl_array[$sub_heading][$index]['aNm'] 	 =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde'] 	     =  $unique_code;								
								$type = "mulsel";
								$multi_arr = array();
								$resutl_array[$sub_heading][$index]['dpos']  =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe']				 =  $type;								
								$multiselval = "";
								$input_values_arr = array();
								$input_values     = json_decode($input_Value,true);
								$input_values_arr = array_filter((array_unique($input_values)));								
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){
									$multiselval = $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;									
									$multi_arr = array_unique(explode(",",$multiselval));
									if(count($multi_arr) >0){
										$existing_attrs[$sub_heading][$jIndex]['selval']          =  $multi_arr;
									}
									$key_as    = array_values($input_values_arr);
									$existing_attrs[$sub_heading][$jIndex]['options'] 	 =  $key_as;		
									$jIndex++;															
									
								}								
								$multi_arr = array_unique(explode(",",$multiselval));
								if(count($multi_arr) >0){
									$resutl_array[$sub_heading][$index]['selval'] 			 =  $multi_arr;
								}
								$key_as    = array_values($input_values_arr);
								$resutl_array[$sub_heading][$index]['options'] 	 	 =  $key_as;									
								
								
							break;
							default:
								$index--;	
								//$jIndex--;					
						}
						$index++;
						
					}
					$unique_array[] = $value_arr1['unique_code'];
				}
			}
		}		
		
		if(count($resutl_array)>0){
			$result_msg_arr['data'] = array();
			$result_msg_arr['data'] 		 = $resutl_array;			
			$result_msg_arr['existing'] 	 = $existing_attrs;
			$result_msg_arr['count'] 		 = $count_of_total;			
			$result_msg_arr['time']          = $this->createTime('00:00','23:30');			
			$result_msg_arr['sub_grp_name']  = $this->name_sub_grp_arr;			
			$result_msg_arr['sub_id_name']   = $this->name_sub_id_arr;			
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg']  = "Success";			
			return $result_msg_arr;
		}else{ //no attributes found
			if($this->params['live_data'] != 1){
				
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
					$bustemp_tbl 		= "tbl_business_temp_data";
					$bustemp_upt = array();
					$bustemp_upt['mainattr'] 				= '';
					$bustemp_upt['facility'] 				= '';
					$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
					$mongo_inputs['table_data'] 			= $mongo_data;
					$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
				}
				else
				{
					if($this->mongo_tme == 1){
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_data = array();
						$bustemp_tbl 		= "tbl_business_temp_data";
						$bustemp_upt = array();
						$bustemp_upt['mainattr'] 				= '';
						$bustemp_upt['facility'] 				= '';
						$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
						$mongo_inputs['table_data'] 			= $mongo_data;
						$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
					}
					
					$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','','') ON DUPLICATE KEY UPDATE mainattr='',facility=''";
					$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";
					$resUpdateAttributes = parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
				}
			}
			$message = "Attribute mapped category not found.";
			echo json_encode($this->send_die_message($message));
			die();
		}
			
	}
	function getEligibleAttributeList()
	{
		$attrlist_str = '';
		$attrlist_arr = array();
		$attribute_mapping_arr = array(4 =>'HOTEL',8=>'MOVIE', 16=>'HOTEL', 64=>'RESORT', 128=>'HOSTEL', 256=>'HOSPITAL', 512=>'BOOK', 1024=>'GYM', 2048=>'WATER PARK');
		$restaurant_mapping_arr = array(134217728 => 'RESTAURANT',4398046511104 => 'RESTAURANT',8796093022208 => 'RESTAURANT');
		if(count($this->contract_paid_cat_arr)>0)
		{
			$catid_List = implode("','",$this->contract_paid_cat_arr);
			$sqlAttributeList = "SELECT category_verticals,display_product_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_List."')";
			$resAttributeList 	= parent::execQuery($sqlAttributeList, $this->conn_catmaster);
			
			if($resAttributeList && parent::numRows($resAttributeList)>0)
			{
				while($row_attrlist = parent::fetchData($resAttributeList))
				{	
					$display_product_flag 	= trim($row_attrlist['display_product_flag']);
					$category_verticals 	= trim($row_attrlist['category_verticals']);
					foreach($attribute_mapping_arr as $key =>$value)
					{
						if(((int)$category_verticals & $key) == $key) 
						{
							$attrlist_arr[]=	$value; 
						}
					}
					foreach($restaurant_mapping_arr as $key =>$value)
					{
						if(((int)$display_product_flag & $key) == $key) 
						{
							$attrlist_arr[]=	$value; 
						}
					}
				}
			}
		}
		if(count($attrlist_arr)>0)
		{
			$attrlist_arr = array_unique($attrlist_arr);
			$attrlist_arr = array_merge(array_filter($attrlist_arr));
			$attrlist_str = implode(",",$attrlist_arr);
		}
		return $attrlist_str;
	}
	function processExistingAttributes()
	{
		$old_attributes_arr = array();
		$old_exist_arr = array();
		
		if(isset($this->business_data['facility']) && $this->business_data['facility'] != '')
		{
			$temp1_arr	= explode("***",$this->business_data['facility']);					
			foreach($temp1_arr as $key1=> $value1){		
				$temp2_arr	= explode("@@@",$value1);				
				foreach($temp2_arr as $key=>$value){
					
					$existing_facility_name = $temp2_arr['0'];
					$temp3_arr  = explode("###",$temp2_arr[1]);
					
					foreach($temp3_arr as $key=>$value2){					
						$temp4_arr = explode("~~~",$value2);
						
						if(!empty($temp4_arr[0])){	
							$ucw_key_attrb =  trim($temp4_arr[0]);
							if(!in_array($ucw_key_attrb,$old_exist_arr)){
								$old_exist_arr[strtolower($existing_facility_name)][addslashes(stripslashes($ucw_key_attrb))] = $temp4_arr[1];
							}
						}
					}
				}
			}
			if(count($old_exist_arr) > 0){				
				foreach($old_exist_arr as $key=>$value){					
					$old_exist_arr_str = implode("','",array_keys($value));										
						$sel_disp_pos = "SELECT a.attribute_group,a.attribute_sub_group, a.unique_code, a.attribute_name,a.unique_code, a.display_position,b.attribute_type ,a.selection_flag,a.active_flag,b.input_Value FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_name IN ('".$old_exist_arr_str."')  AND a.attribute_group IN ('".$this->attribute_group."') AND a.selection_flag=0 AND a.active_flag=1  AND b.attribute_type!=0 ORDER BY a.display_position ASC";
						$res_disp_pos = parent::execQuery($sel_disp_pos, $this->conn_local);
						if($res_disp_pos && parent::numRows($res_disp_pos)>0){
							while($row_disp_pos = parent::fetchData($res_disp_pos)){
								$display_position = $row_disp_pos['display_position'];
								$attribute_name	  = $row_disp_pos['attribute_name'];
								$unique_code 	  = $row_disp_pos['unique_code'];
								$input_Value	  = $row_disp_pos['input_Value'];
								$attribute_group  = $row_disp_pos['attribute_group'];
								$attribute_sub_group  = '-'.$row_disp_pos['attribute_sub_group'];
								if($old_attributes_arr[$attribute_sub_group][$display_position][$attribute_name]!= '')
									$old_attributes_arr[$attribute_sub_group][$display_position][$attribute_name] .= '|~|'.trim($value[addslashes(stripslashes($attribute_name))]);
								else
									$old_attributes_arr[$attribute_sub_group][$display_position][$attribute_name] = trim($value[addslashes(stripslashes($attribute_name))]);
							}
							
						}
								
				
				}	
			
			}
			
		}
		
		//attributes -- based on categories mapped_attributes. 
		$get_nat_cat = "SELECT group_concat(mapped_attribute) as mapped_attribute,attribute_group  FROM tbl_categorymaster_generalinfo a  WHERE  catid IN ('".implode("','",$this->contract_paid_cat_arr)."')";		
		$res_nat_cat   = parent::execQuery($get_nat_cat, $this->conn_local);
		if($res_nat_cat && parent::numRows($res_nat_cat)>0){
			$row_nat_cat	=	parent::fetchData($res_nat_cat);
			if($row_nat_cat['mapped_attribute']){
				$mapped_attribute = $row_nat_cat['mapped_attribute'];
				$attribute_group  = $row_nat_cat['attribute_group']; 
				$mapped_attribute_str = str_replace(",","','",$mapped_attribute);				
				$get_att = "SELECT a.attribute_group,a.attribute_sub_group, a.unique_code, a.attribute_name,a.unique_code, a.display_position,b.attribute_type ,a.selection_flag,a.active_flag,b.input_Value FROM tbl_attribute_mapping a , tbl_attribute_master b WHERE a.attribute_group IN ('".$this->attribute_group."') and  a.unique_code IN ('".$mapped_attribute_str."') AND a.unique_code = b.unique_code AND b.attribute_type!=0 ORDER BY display_position ASC";
				$res_get_att   = parent::execQuery($get_att, $this->conn_local);
				if($res_get_att && parent::numRows($res_get_att)>0){
					while($row_get_att	=	parent::fetchData($res_get_att)){
						if($row_get_att['attribute_sub_group']!='' && $row_get_att['attribute_sub_group']!=0){
							$sub_heading_name = $this->name_sub_id_arr[$row_get_att['attribute_sub_group']];
							$attr_sub_grp = '-'.$row_get_att['attribute_sub_group'];
							$old_attributes_arr[$attr_sub_grp][$row_get_att['display_position']][$row_get_att['attribute_name']] = trim($row_get_att['attribute_name']);
						}
					}	
				}				
			}
		}
		
		$result_fin = array();
		$result_fin['old_attributes_arr'] = $old_attributes_arr;
		return $result_fin;
	
		
	}

	function fetchBusinessTempData()
	{
		$row_temp_data = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "facility";
			$mongo_alias = array();
			$mongo_alias['catIds']		= "catids";
			$mongo_inputs['aliaskey'] 	= $mongo_alias;
			$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlBusinessTempData = "SELECT catids,facility FROM tbl_business_temp_data WHERE contractid = '" . $this->parentid . "'";
			$resBusinessTempData = parent::execQuery($sqlBusinessTempData, $this->conn_temp);
			if($resBusinessTempData && parent::numRows($resBusinessTempData)>0)
			{
				$row_temp_data	=	parent::fetchData($resBusinessTempData);
			}
		}
		return $row_temp_data;
	}
	function fetchBusinessLiveData()
	{
		$sqlBusinessLiveData = "SELECT catidlineage as catids, attributes_edit as facility FROM tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		$resBusinessLiveData = parent::execQuery($sqlBusinessLiveData, $this->conn_iro);
		if($resBusinessLiveData && parent::numRows($resBusinessLiveData)>0)
		{
			return $row_live_data	=	parent::fetchData($resBusinessLiveData);
		}
	}
	function getContractTempCategories()
	{
		if(isset($this->business_data['catids']) && $this->business_data['catids'] != '')
		{
			$temp_catlin_arr 	= 	array();
			$temp_catlin_arr  	=   explode('|P|',$this->business_data['catids']);
			$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
			$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
			$this->contract_paid_cat_arr = $temp_catlin_arr;
		}
	}
	function getContractLiveCategories()
	{
		if(isset($this->business_data['catids']) && $this->business_data['catids'] != '')
		{
			$live_catlin_arr = array();
			$live_catlin_arr = explode("/,/",trim($this->business_data['catids'],"/"));
			$live_catlin_arr = array_filter($live_catlin_arr);
			$live_catlin_arr = 	$this->getValidCategories($live_catlin_arr);
			$this->contract_paid_cat_arr = $live_catlin_arr;
		}
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
	function updateAttributesInfo()
	{		
		
		if(is_array($this->params['attributes']) && count($this->params['attributes'])>0 && ($this->params['attrTaken'] == 1)){			
			
			$this->docid = $this->getDocid();
			
			$this->params['attrdata'] = array();
			foreach($this->params['attributes'] as $facname=>$value_arr){
				foreach($value_arr as $key => $value){
					$this->params['attrdata'][strtoupper($facname)][] = $value;
				}
			}
			$filtered_arr = array_filter($this->params['attributes']);			
			if(count($filtered_arr)>0){
				$unique_codes_values  = array();
				$sub_grp_id_arr    	 = array();
				foreach($filtered_arr as $key=>$value){					
					if(count($value) > 0){
						foreach($value as $key1=>$value1){
							$unique_codes_values[$key1] = $value1;
							$id_arr = array_merge(array_filter(explode("-",$key)));							
							$sub_grp_id_arr[$key1] = $id_arr[0];
						}						
					}
					$this->unique_codes = $unique_codes_values;					
				}						
				if(count($unique_codes_values)>0){ //get attribute_details d_jds
					$attribute_details = $this->get_attribute_details($unique_codes_values,$sub_grp_id_arr);
				}
				if(count($sub_grp_id_arr)>0){ //get sub_group_id details online_regis1
					$sub_grp_details = $this->sub_group_details($sub_grp_id_arr);
				}				
				if(count($attribute_details)>0 && count($sub_grp_details)>0){
					$insert = ''; $insert1 = '';
					$del_entry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'";
					$res_del 	= parent::execQuery($del_entry, $this->conn_temp);
					$insert = "INSERT INTO tbl_companymaster_attributes_temp (docid,parentid, city, attribute_name, attribute_value, attribute_type, attribute_sub_group,sub_group_name,display_flag,sub_group_position,attribute_position,attribute_id,attribute_prefix) VALUES ";
					foreach($attribute_details as $unique_code=>$attr_values){
						$display_flag ='';
						if($unique_code!=''){														
							$attribute_name = ''; 
							if($attr_values['attribute_dname']!=''){
								$attribute_name	= $attr_values['attribute_dname'];
							}else{
								$attribute_name	= $attr_values['attribute_name'];
							}							
							if($attr_values['display_flag']!=1){
								$display_flag = 0;
							}else{
								$display_flag = $attr_values['display_flag'];
							}
							
							$attribute_value_arr = explode("~~~",$this->unique_codes[$unique_code]);
							$insert1 .= "('".$this->docid."', '".$this->parentid."', '".$this->data_city."', '".addslashes($attribute_name)."', '".$attribute_value_arr['1']."', '".$attr_values['attr_type_fin']."', '".$attr_values['attribute_sub_group']."', '".addslashes($sub_grp_details[$attr_values['attribute_sub_group']]['subgroup_name'])."', '".$display_flag."', '".$sub_grp_details[$attr_values['attribute_sub_group']]['sub_group_pos']."', '".$attr_values['attribute_web_position']."', '".$unique_code."', '".$attr_values['attribute_prefix']."')" .",";							
						}
					}
					$insert1 = rtrim($insert1,",");				
					$fin = $insert.$insert1;					
					$res_temp 	= parent::execQuery($fin, $this->conn_temp);
				}
			}			
			if(count($this->params['attrdata'])>0)
			{
				foreach($this->params['attrdata'] as $attrname => $attrinfo)
				{
					$attrname = array_unique(explode('-',$attrname));
					$attrname = array_merge(array_filter($attrname));
					
					$facilityname = $attrname[0].'@@@';
					$attributes1 = '';
					foreach($attrinfo as $attrvalue)
					{
						if($attrvalue!='' && !is_array($attrvalue)){							
							$attrvalue = rtrim($attrvalue,",");							
							$attributes1 .= $attrvalue."###";
						}
					}
					$attributes2 		= trim($attributes1,"###");
					$mainattr 		.= str_replace("~~~", "-", $attributes2) . "|$|";
					$attributes3 	= $attributes2. "***";
					$attributes4 	.= $facilityname.$attributes3;
					
				}
				
				$facility 	= trim($attributes4, "***");
				$mainattr 		= trim($mainattr,"|$|");
				
				if(empty($mainattr)){
					$facility = '';
				}
				
				if($this->params['live_data'] == 1){
					$sqlUpdateAttributes = "UPDATE tbl_companymaster_extradetails SET attributes = '".addslashes($mainattr)."', attributes_edit = '".addslashes($facility)."' WHERE parentid = '".$this->parentid."'";
					$resUpdateAttributes 	= parent::execQuery($sqlUpdateAttributes, $this->conn_iro);
				}else{
					if($this->mongo_flag == 1){
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_data = array();
						
						$bustemp_tbl 		= "tbl_business_temp_data";
						$bustemp_upt = array();
						$bustemp_upt['mainattr'] 				= $mainattr;
						$bustemp_upt['facility'] 				= $facility;
						$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
						
						$mongo_inputs['table_data'] 			= $mongo_data;
						$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
					}
					else
					{
						if($this->mongo_tme == 1){
							$mongo_inputs = array();
							$mongo_inputs['parentid'] 	= $this->parentid;
							$mongo_inputs['data_city'] 	= $this->data_city;
							$mongo_inputs['module']		= $this->module;
							$mongo_data = array();
							
							$bustemp_tbl 		= "tbl_business_temp_data";
							$bustemp_upt = array();
							$bustemp_upt['mainattr'] 				= $mainattr;
							$bustemp_upt['facility'] 				= $facility;
							$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
							
							$mongo_inputs['table_data'] 			= $mongo_data;
							$resUpdateAttributes = $this->mongo_obj->updateData($mongo_inputs);
						}
						
						$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','".addslashes($mainattr)."','".addslashes($facility)."') ON DUPLICATE KEY UPDATE mainattr='".addslashes($mainattr)."',facility='".addslashes($facility)."'";
						$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";
						$resUpdateAttributes 	= parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
					}
					
					if(count($this->params['unique_code_str']) > 0){
						$unique_code_arr = $this->params['unique_code_str'];
						$uniue_arr = array();
						foreach($unique_code_arr as $key=>$value){			
							foreach($value as $k1=>$v1){
								if(!in_array($v1,$uniue_arr)){
									$uniue_arr[] = $v1;
								}
							}
						}
						$uniue_arr = array_unique($uniue_arr);
						$uniue_str = implode("','",$uniue_arr);						
						if($uniue_str!=''){ //filter uniquecodes base on filter_flag
							$get_filter_flag = "SELECT GROUP_CONCAT(unique_code) as unique_code, filter_flag FROM tbl_attribute_mapping WHERE unique_code IN ('".$uniue_str."') AND filter_flag=1";
							$res_filter_flag = parent::execQuery($get_filter_flag, $this->conn_local);
							if($res_filter_flag && parent::numRows($res_filter_flag)>0){
								$row_filter_flag = parent::fetchData($res_filter_flag);
								if($row_filter_flag['unique_code']!=''){
									
									$unique_code_search = $row_filter_flag['unique_code'];
									$unique_code_search = str_replace(",","#",$unique_code_search);
									
									if($this->mongo_flag == 1){										
										$mongo_inputs = array();
										$mongo_inputs['parentid'] 	= $this->parentid;
										$mongo_inputs['data_city'] 	= $this->data_city;
										$mongo_inputs['module']		= $this->module;
										$mongo_data = array();
										
										$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
										$extrdet_upt = array();
										$extrdet_upt['attribute_search'] 	= '#'.$unique_code_search.'#';
										$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
								
										$mongo_inputs['table_data'] 			= $mongo_data;
										$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
									}
									else
									{
										if($this->mongo_tme == 1){										
											$mongo_inputs = array();
											$mongo_inputs['parentid'] 	= $this->parentid;
											$mongo_inputs['data_city'] 	= $this->data_city;
											$mongo_inputs['module']		= $this->module;
											$mongo_data = array();
											
											$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
											$extrdet_upt = array();
											$extrdet_upt['attribute_search'] 	= '#'.$unique_code_search.'#';
											$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
									
											$mongo_inputs['table_data'] 			= $mongo_data;
											$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
										}
											
										$insert_attr_search = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='#".$unique_code_search."#' WHERE parentid='".$this->parentid."'";			
										if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){	
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_iro);
										}else{
											$insert_attr_search = $insert_attr_search."/* TMEMONGOQRY */";
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_temp);
										}
									}
									
								}else{
									if($this->mongo_flag == 1){
										$mongo_inputs = array();
										$mongo_inputs['parentid'] 	= $this->parentid;
										$mongo_inputs['data_city'] 	= $this->data_city;
										$mongo_inputs['module']		= $this->module;
										$mongo_data = array();
										
										$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
										$extrdet_upt = array();
										$extrdet_upt['attribute_search'] 	= '';
										$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
								
										$mongo_inputs['table_data'] 			= $mongo_data;
										$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
									}
									else
									{
										if($this->mongo_tme == 1){
											$mongo_inputs = array();
											$mongo_inputs['parentid'] 	= $this->parentid;
											$mongo_inputs['data_city'] 	= $this->data_city;
											$mongo_inputs['module']		= $this->module;
											$mongo_data = array();
											
											$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
											$extrdet_upt = array();
											$extrdet_upt['attribute_search'] 	= '';
											$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
									
											$mongo_inputs['table_data'] 			= $mongo_data;
											$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
										}
										
										$insert_attr_search = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='' WHERE parentid='".$this->parentid."'";			
										if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){	
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_iro);
										}else{
											$insert_attr_search = $insert_attr_search."/* TMEMONGOQRY */";
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_temp);
										}
									}
								}
								
							}else{
								if($this->mongo_flag == 1){
									$mongo_inputs = array();
									$mongo_inputs['parentid'] 	= $this->parentid;
									$mongo_inputs['data_city'] 	= $this->data_city;
									$mongo_inputs['module']		= $this->module;
									$mongo_data = array();
									
									$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
									$extrdet_upt = array();
									$extrdet_upt['attribute_search'] 	= '';
									$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
							
									$mongo_inputs['table_data'] 			= $mongo_data;
									$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
								}
								else
								{
									if($this->mongo_tme == 1){
										$mongo_inputs = array();
										$mongo_inputs['parentid'] 	= $this->parentid;
										$mongo_inputs['data_city'] 	= $this->data_city;
										$mongo_inputs['module']		= $this->module;
										$mongo_data = array();
										
										$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
										$extrdet_upt = array();
										$extrdet_upt['attribute_search'] 	= '';
										$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
								
										$mongo_inputs['table_data'] 			= $mongo_data;
										$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
									}
									
									$insert_attr_search = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='' WHERE parentid='".$this->parentid."'";			
									if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){	
										$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_iro);
									}else{
										$insert_attr_search = $insert_attr_search."/* TMEMONGOQRY */";
										$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_temp);
									}
								}
							}							
						}
					}
					
				}
				if($resUpdateAttributes){
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Success";
					return $result_msg_arr;
				}else{
					$message = "Error found in updating attribute.";
					echo json_encode($this->send_die_message($message));
					die();
				}
			}
			else
			{
				$message = "Error found in making attribute param.";
				echo json_encode($this->send_die_message($message));
				die();
			}
			
		}
		else
		{
			$result_msg_arr['error']['code'] = 2;
			$result_msg_arr['error']['msg'] = "No Attributes Selected.";
			return $result_msg_arr;
		}
		
	}
	
	function  get_attribute_details($unique_codes,$sub_grp_id_arr){
		$resut_arr = array();
		if(count($sub_grp_id_arr) > 0 && count($unique_codes) > 0){
			$sub_grp_imp = implode("','",array_unique($sub_grp_id_arr));
			$unique_imp = implode("','",array_keys($unique_codes));
			$get_det = "SELECT attribute_group,attribute_sub_group, attribute_name,attribute_dname,attribute_prefix,unique_code,display_position,attribute_web_position,gen_sepl_type AS attr_type_fin,display_flag FROM tbl_attribute_mapping WHERE unique_code IN ('".$unique_imp."') AND selection_flag=0 AND active_flag=1  AND assign_to_all=1  AND display_flag!=0 AND national_catid=0  AND attribute_sub_group IN ('".$sub_grp_imp."') ";
			$res_det = parent::execQuery($get_det, $this->conn_local);
			$count_of_total = parent::numRows($res_det);
			if($res_det && parent::numRows($res_det)>0){
				while($row_det = parent::fetchData($res_det)){
					$resut_arr[$row_det[unique_code]] = $row_det;
				}
				if(count($resut_arr)>0){
					return $resut_arr;
				}
			}
		}else{
			return $resut_arr;
		}		
	}
	function sub_group_details($sub_grp_id_arr){
		$sub_grp_final_arr = array();
		if(count($sub_grp_id_arr) >0){			
			$sub_imp = implode("','",array_unique($sub_grp_id_arr));
			$str_catids = implode("','",$this->contract_paid_cat_arr);	
			$get_attribute_grp = "SELECT DISTINCT(attribute_group) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0  ORDER BY attribute_group ASC";
			$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);
			if($res_attribute_grp && parent::numRows($res_attribute_grp)>0){
				while($row_attribute_grp  = parent::fetchData($res_attribute_grp)){
					if($row_attribute_grp['attribute_group']!='' || $row_attribute_grp['attribute_group']!=0){
						$attribute_group[] = $row_attribute_grp['attribute_group'];
					}
				}
				$attribute_group_imp = implode("','",array_unique($attribute_group));
				$get_sub_det = "SELECT id,subgroup_name,sub_group_pos,attribute_group FROM online_regis1.tbl_attribute_subgroup WHERE id IN('".$sub_imp."') AND attribute_group>0 AND attribute_group IN ('".$attribute_group_imp."')";
				$res_sub_det =  parent::execQuery($get_sub_det, $this->conn_idc);
				if($res_sub_det && parent::numRows($res_sub_det)>0){
					while($row_sub_det = parent::fetchData($res_sub_det)){
						$sub_grp_final_arr[$row_sub_det['id']] = $row_sub_det;
					}
				}
				if(count($sub_grp_final_arr)>0){
					return $sub_grp_final_arr;
				}
			}
		}else{
			return $sub_grp_final_arr;
		}
	}
	private function createTime($start,$end) 
	{		
		$tStart = strtotime($start);
		$tEnd = strtotime($end);
		$tNow = $tStart;
		$timeArrShow	=	array();
		while($tNow <= $tEnd){
			$timeArrShow[]	=	date("H:i",$tNow);
			$tNow = strtotime('+30 minutes',$tNow);
		}
		return $timeArrShow;
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
