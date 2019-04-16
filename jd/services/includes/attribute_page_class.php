<?php
ob_start();
ini_set('max_execution_time', 0); 
//adding handling to show attributes for nonpaid categories also bcoz sanjay khandelwal gave req- Taiga ID : 1391 in Genio Lite(before it was only for paid cat's)
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
		
		$this->categoryClass_obj = new categoryClass();
		//~ echo "<pre>params:--";print_r($this->params);//die;
		$this->setServers();
		
		$this->nonpaid_catids = array();
		$this->nonpaid_catids = $this->fetchNonpaidCategories();
		
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
		$this->contract_final_cat_arr 	= array();		
		if($this->params['live_data'] == 1)	{
			$this->getContractLiveCategories();
		}else{
			$this->getContractTempCategories();
		}	
			
		$this->getDocid();
		
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
			$this->conn_temp_fin 	= $this->conn_iro;
			$this->conn_catmaster 	= $this->conn_local;
			$this->attr_main_conn   = $this->conn_iro;
		}elseif(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_temp_fin 	= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			$this->attr_main_conn   = $this->conn_idc;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}elseif((strtoupper($this->module) =='ME') || (strtoupper($this->module) =='JDA')){
			$this->conn_temp		= $this->conn_idc;
			$this->conn_temp_fin 	= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
			$this->attr_main_conn   = $this->conn_idc;
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
		$this->docid = '';
		$sel_docid = "SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_docid = parent::execQuery($sel_docid, $this->conn_iro);
		if($res_docid && parent::numRows($res_docid)>0){
			$row_docid= parent::fetchData($res_docid);
			if($row_docid[docid]!=''){
				$docid = $row_docid[docid];
				$this->docid  = $docid;
			}
		}
		return $docid;
	}
	function get_vertical_flag(){

		if(count($this->contract_final_cat_arr) > 0){			
			$str_catids = implode("','",$this->contract_final_cat_arr);	
			//$get_attribute_grp = "SELECT DISTINCT(attribute_group) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0  ORDER BY attribute_group ASC";
			//$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);

			$cat_params = array();
			$cat_params['page'] ='attribute_page_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'attribute_group';
			$cat_params['orderby']		= 'attribute_group ASC';

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$this->contract_final_cat_arr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				foreach($cat_res_arr['results'] as $key=>$cat_arr){
					if($cat_arr['attribute_group']!='' || $cat_arr['attribute_group']!=0){
						$attribute_group[] = $cat_arr['attribute_group'];
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
			$del_entry  = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'"; //no need to have attributes whn der is no category
			$res_del 	= parent::execQuery($del_entry, $this->conn_temp);	
			
			$message = "Attribute mapped category not found.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
	}	
	function check_att_pre(){
		$this->get_vertical_flag();
		if($this->parentid!='PXX22.XX22.120327123542.Z3U6'){
			if(count($this->attribute_group_ex)<=0){
				$del_entry  = "DELETE FROM tbl_companymaster_attributes_temp WHERE docid='".$this->docid."'"; //no need to have attributes whn der is no category
				$res_del 	= parent::execQuery($del_entry, $this->conn_temp);	
				$result_msg_arr = array();
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg']  = "Attribute Mapped categories are not found";			
				return $result_msg_arr;
			}else{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg']  = "Success";			
				return $result_msg_arr;
			}
		}else{			
			$result_msg_arr = array();
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg']  = "Attribute Mapped categories are not found";			
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
		//~ $get_attr_names = "SELECT a.attribute_group,a.attribute_sub_group, a.unique_code, a.attribute_name,a.unique_code, a.display_position, a.gen_sepl_type AS attr_type_fin, b.attribute_type ,a.selection_flag,a.active_flag,b.input_Value,a.attribute_sub_group,a.attribute_group FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$this->attribute_group."')  AND a.selection_flag=0 AND a.active_flag=1  AND a.assign_to_all=1  AND a.display_flag!=0 AND  a.national_catid=0  AND  a.attribute_sub_group IN ('".$str_imp_sub."') ORDER BY FIELD (a.attribute_sub_group,'".$str_imp_sub."'),a.display_position ASC ";	
		
		//call sp instead of query
		$str_catids 	= implode(",",$this->contract_final_cat_arr);	

		$sql_sp = "call d_jds.sp_category_based_attributes('".$str_catids."') ";		
		$res_attr_names = parent::execQuery($sql_sp, $this->conn_local);
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
					
					$attribute_group_fin='';$attribute_name='';$unique_code=''; $display_position='';
					$attribute_type='';$input_Value='';			
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
								$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
								$radio_arr = json_decode($input_Value,true);								
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){									
									$resutl_array[$sub_heading][$index]['selval'] 	    =  $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['selval'] 	=  $existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name];
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	=  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;	
									$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;	
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
								$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
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
									$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;					
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
										$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;	
										$existing_attrs[$sub_heading][$jIndex]['selfrm']		=  $timingfrmstr;
										$existing_attrs[$sub_heading][$jIndex]['selto'] 		=  $timingtostr;	
										$jIndex++;
									}
									$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
									$resutl_array[$sub_heading][$index]['uCde']       =  $unique_code;
									$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
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
										$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;	
										$existing_attrs[$sub_heading][$jIndex]['selval']     = $timeval;
										$jIndex++;
									}
									$resutl_array[$sub_heading][$index]['selval']           = $timeval;
									$resutl_array[$sub_heading][$index]['aNm']   =  $attribute_name;
									$resutl_array[$sub_heading][$index]['uCde'] 	    =  $unique_code;
									$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
								}
							break;
							
							case '4' : 
								$type = 'chk';
								$singlechksel = 'off';								
								$resutl_array[$sub_heading][$index]['dpos']   =  $display_position;
								$resutl_array[$sub_heading][$index]['tpe']   			  =  $type;
								$resutl_array[$sub_heading][$index]['aNm']     =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde']        =  $unique_code;
								$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
								$disable_array1 = explode(",",$res);					
								if(isset($existing_attribute_arr[strtolower($sub_heading)][$display_position][$attribute_name])){
									$singlechksel = 'on';
									$existing_attrs[$sub_heading][$jIndex]['dpos']	    = $display_position;
									$existing_attrs[$sub_heading][$jIndex]['tpe']   	    =  $type;
									$existing_attrs[$sub_heading][$jIndex]['aNm']        =  $attribute_name;
									$existing_attrs[$sub_heading][$jIndex]['uCde']       =  $unique_code;			
									$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;							
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
									$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;									
									$existing_attrs[$sub_heading][$jIndex]['selval']     =  $txtval;
									$jIndex++;
								}								
								
								$resutl_array[$sub_heading][$index]['aNm'] 	 =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde']  =  $unique_code;	
								$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;	
							break;
							
							case '6' :								
								$resutl_array[$sub_heading][$index]['aNm'] 	 =  $attribute_name;
								$resutl_array[$sub_heading][$index]['uCde'] 	     =  $unique_code;		
								$resutl_array[$sub_heading][$index]['attr_grp']        =  $attribute_group_fin;							
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
									$existing_attrs[$sub_heading][$jIndex]['attr_grp']        =  $attribute_group_fin;									
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
			$del_entry  = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'"; //no need to have attributes whn der is no category
			$res_del 	= parent::execQuery($del_entry, $this->conn_temp);	
			
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
		if(count($this->contract_final_cat_arr)>0)
		{
			$catid_List = implode("','",$this->contract_final_cat_arr);
			//$sqlAttributeList = "SELECT category_verticals,display_product_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_List."')";
			//$resAttributeList 	= parent::execQuery($sqlAttributeList, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='attribute_page_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_verticals,display_product_flag';			

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$this->contract_final_cat_arr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key=>$cat_arr)
				{	
					$display_product_flag 	= trim($cat_arr['display_product_flag']);
					$category_verticals 	= trim($cat_arr['category_verticals']);
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
		$old_exist_arr 		= array();
	
		$getAttributes = "SELECT * FROM tbl_companymaster_attributes_temp WHERE parentid='".trim($this->parentid)."'";
		$resAttributes = parent::execQuery($getAttributes, $this->conn_temp);
		if($resAttributes && parent::numRows($resAttributes)>0){
			$uniqueCode = array();
			$resultArr  = array();
			while($rowAttributes = parent::fetchData($resAttributes)){
				if(!in_array($rowAttributes['attribute_id'],$uniqueCode)){
					array_push($uniqueCode,$rowAttributes['attribute_id']);
				}
				$resultArr[$rowAttributes['attribute_id']] = $rowAttributes;
			}			
			if(count($uniqueCode)>0){				
				$attribute_groups = $this->attribute_group;
				$sub_grps		  = implode("','",$this->sub_grps);  
				$unique_imp		  = implode("','",$uniqueCode);			
				
				
				$get_det = "SELECT attribute_group,attribute_sub_group, attribute_name,attribute_dname,attribute_prefix,unique_code,display_position,attribute_web_position,display_flag,attribute_type,main_attribute_flag AS main_attribute_flag,subgroup_web,input_Value FROM
				(
				SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag AS main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$attribute_groups."') AND a.unique_code IN ('".$unique_imp."') AND a.selection_flag=0 AND a.active_flag=1  AND a.display_flag!=0  AND a.attribute_sub_group IN ('".$sub_grps."') ORDER BY display_position ASC
				) AS t GROUP BY unique_code ";


				//~ $get_det = " SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag as main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$attribute_groups."') AND a.unique_code IN ('".$unique_imp."') AND a.selection_flag=0 AND a.active_flag=1  AND a.display_flag!=0  AND a.attribute_sub_group IN ('".$sub_grps."') order by display_position asc";		
				
				//~ echo  "<pre>get_det:--".$get_det;				
				
				
				$res_det =  parent::execQuery($get_det, $this->conn_local);
				$count_of_total = parent::numRows($res_det);
				if($res_det && parent::numRows($res_det)>0){
					while($row_det = parent::fetchData($res_det)){
						$resut_arr[$row_det[unique_code]] = $row_det;
					}
				}
				
			}		
			foreach($resut_arr as $uniCode=>$value){
				$display_position = ''; $attribute_name=''; $attribute_sub_group='';
				$attribute_sub_group = '-'.$value['attribute_sub_group'];
				$display_position = $value['display_position'];
				$attribute_name	  = $value['attribute_name'];			
				if($resut_arr[$attribute_sub_group][$display_position][$attribute_name]!= ''){
					$value_arr = array_filter(array_unique(explode("|~|",$value[$attribute_name])));
					$old_attributes_arr[$attribute_sub_group][$display_position][$attribute_name] = trim(addslashes(stripslashes($attribute_name)));
				}								
				else
					$old_attributes_arr[$attribute_sub_group][$display_position][$attribute_name] = trim(addslashes(stripslashes($attribute_name)));
			}
			
		}		
		//attributes -- based on categories mapped_attributes. 
		//$get_nat_cat = "SELECT group_concat(mapped_attribute) as mapped_attribute,attribute_group  FROM tbl_categorymaster_generalinfo a  WHERE  catid IN ('".implode("','",$this->contract_paid_cat_arr)."')";                
		//$res_nat_cat   = parent::execQuery($get_nat_cat, $this->conn_local); 
		$cat_params = array(); 
		$cat_params['page'] ='attribute_page_class'; 
		$cat_params['data_city']        = $this->data_city;                      
		$cat_params['return']           = 'mapped_attribute,attribute_group'; 

		$where_arr      =       array(); 
		$where_arr['catid']             = implode(",",$this->contract_paid_cat_arr); 
		$cat_params['where']    = json_encode($where_arr); 

		if(count($this->contract_paid_cat_arr)>0){ 
				$cat_res        =       $this->categoryClass_obj->getCatRelatedInfo($cat_params); 
		} 
		$cat_res_arr = array(); 
		if($cat_res!=''){ 
			$cat_res_arr =  json_decode($cat_res,TRUE); 
		} 

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){ 				
			$mapped_attribute_arr = array(); 
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {                           
					if($cat_arr['mapped_attribute']!=''){ 
							$mapped_attribute_arr[] = $cat_arr['mapped_attribute']; 
					} 
			} 
			if(count($mapped_attribute_arr)>0){ 
				$mapped_attribute = implode(",", $mapped_attribute_arr); 	
				if($mapped_attribute!=''){
					$mapped_attribute_str = str_replace(",", "','",$mapped_attribute);					
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
		$all_temp_catids_arr = array();
		$all_temp_catids_arr = array_unique(array_merge($this->contract_paid_cat_arr,$this->nonpaid_catids)); 
		$this->contract_final_cat_arr = $all_temp_catids_arr;		
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
		$all_temp_catids_arr = array();
		$all_temp_catids_arr = array_unique(array_merge($this->contract_paid_cat_arr,$this->nonpaid_catids));
		$this->contract_final_cat_arr = $all_temp_catids_arr;
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
	
	function getOldAttributesTemp(){
		$result   = array(); 
		$tempdata = array();
		$tempdataExt = array();
		$mainData = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['module']       	= $this->module;
			$mongo_inputs['parentid']       = $this->parentid;
			$mongo_inputs['data_city']      = $this->data_city;
			$mongo_inputs['table']          = json_encode(array(
				"tbl_companymaster_extradetails_shadow"=>"parentid,data_city,attribute_search"
			));
			$tempdataExt = $this->mongo_obj->getShadowData($mongo_inputs);
		}
		else{			
			$sqlExtradetShadow  = "SELECT parentid,data_city,attribute_search FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$this->parentid."'";
			$resExtradetShadow = parent::execQuery($sqlExtradetShadow, $this->conn_temp_fin);
			if($resExtradetShadow && parent::numRows($resExtradetShadow)>0){
				$row_extrdet_shadow = parent::fetchData($resExtradetShadow);
				$tempdataExt = $row_extrdet_shadow;
			}			
		}
		$getEntry = "SELECT docid, parentid, city, attribute_name, attribute_dname,attribute_value, attribute_sub_group, sub_group_name, attribute_id FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'";			
		$resEntry = parent::execQuery($getEntry, $this->conn_temp);
		if($resEntry && parent::numRows($resEntry)>0){				
			while($rowEnrty = parent::fetchData($resEntry)){
				$tempdata[] = $rowEnrty;
			}
		}
		$getEntry = "SELECT docid, parentid, city, attribute_name, attribute_dname,attribute_value, attribute_sub_group, sub_group_name, attribute_id FROM tbl_companymaster_attributes WHERE parentid='".$this->parentid."'";			
		$resEntry = parent::execQuery($getEntry, $this->attr_main_conn);
		if($resEntry && parent::numRows($resEntry)>0){				
			while($rowEnrty = parent::fetchData($resEntry)){
				$mainData[] = $rowEnrty;
			}
		}
		$result['tbl_companymaster_attributes_temp']	     = $tempdata;
		$result['tbl_companymaster_attributes'] 		     = $mainData;
		$result['tbl_companymaster_extradetails_shadow'] 	 = $tempdataExt;
		return $result;
	}
	function updateAttributesInfo(){
		//~ echo "<pre>params:---";print_r($this->params);		
		$old_temp_tables = $this->getOldAttributesTemp();		
		$count_invalid = 0;		
		$attributes_search_old = '';		
		$attributes_seacrh_new = '';		
		if(count($old_temp_tables)>0){
			$attributes_search_old = $get_old_att_values['tbl_companymaster_extradetails_shadow']['attribute_search'];
		}
		
		if(isset($this->params['validateData']) && $this->params['validateData']!='' && $this->params['validateData']!='undefined'){
			$validateData = $this->params['validateData'];			
			$call_sp 	   = 'CALL  d_jds.sp_attribute_validation("'.$validateData.'")';
			$res_sp  	   = parent::execQuery($call_sp, $this->conn_local);
			$count_invalid = parent::numRows($res_sp);
		}		
		if($count_invalid ==0){
			$this->get_vertical_flag();	
			if(is_array($this->params['attributes']) && count($this->params['attributes'])>0 && ($this->params['attrTaken'] == 1)){			
				

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
						$del_entry = "DELETE FROM tbl_companymaster_attributes_temp WHERE docid='".$this->docid."' AND attribute_id!='Rbuf'";
						$res_del 	= parent::execQuery($del_entry, $this->conn_temp);
						$insert = "INSERT INTO tbl_companymaster_attributes_temp (docid,parentid, city, attribute_name,attribute_dname, attribute_value, attribute_type, attribute_sub_group,sub_group_name,display_flag,sub_group_position,attribute_position,attribute_id,attribute_prefix,main_attribute_flag,main_attribute_position) VALUES ";
						foreach($attribute_details as $unique_code=>$attr_values){
							$attr_values 	= 	$this->addslashesArray($attr_values);
							$display_flag ='';$sub_grp_name = '';
							if($unique_code!='' && $attr_values['attribute_name']!=''){														
								$attribute_name = '';
								$attribute_dname = ''; 
								$attribute_value_arr = $this->addslashesArray(explode("~~~",$this->unique_codes[$unique_code]));
								$attribute_sub_group = $attr_values['attribute_sub_group'];
								$attribute_sub_group	= $attr_values['subgroup_web'];							
								$sub_grp_name  		    = $sub_grp_details[$attribute_sub_group]['subgroup_name']; 
								if(strtolower($sub_grp_name) == 'establishment type'){
									$sub_grp_name = 'Type';
								}
								
								//take subgroup_web instead of attribute_sub_group mail given by ratan
								
								//sub_grp_name = establish_tuype chnage it to type
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
								$insert1 .= "('".$this->docid."', '".$this->parentid."', '".$this->data_city."', '".$attribute_name."', '".$attribute_dname."', '".addslashes(stripslashes($attribute_value_arr['1']))."', '".$attr_values['attr_type_fin']."', '".$attribute_sub_group."', '".$sub_grp_name."', '".$display_flag."', '".$sub_grp_details[$attr_values['attribute_sub_group']]['sub_group_pos']."', '".$attr_values['attribute_web_position']."', '".$unique_code."', '".$attr_values['attribute_prefix']."' , '".$attr_values['main_attribute_flag']."' , '".$attr_values['display_position']."')" .",";							
							}
						}						
						if($insert1!=''){
							$insert1 = rtrim($insert1,",");				
							$fin     = $insert.$insert1;			
							//~ echo "<pre>fin:--".$fin;	
							$resUpdateAttributes 	= parent::execQuery($fin, $this->conn_temp);
						}
					}
				}			
				if(count($this->params['attrdata'])>0){								
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
							$get_filter_flag = "SELECT GROUP_CONCAT(DISTINCT(unique_code)) as unique_code, filter_flag FROM tbl_attribute_mapping WHERE unique_code IN ('".$uniue_str."') AND filter_flag=1";
							$res_filter_flag = parent::execQuery($get_filter_flag, $this->conn_local);
							if($res_filter_flag && parent::numRows($res_filter_flag)>0){
								$row_filter_flag = parent::fetchData($res_filter_flag);
								if($row_filter_flag['unique_code']!=''){
									$unique_code_search = $row_filter_flag['unique_code'];
									$unique_code_search = str_replace(",","#",$unique_code_search);
									$attributes_seacrh_new = '#'.$unique_code_search.'#';
									if($this->mongo_flag == 1 || $this->mongo_tme == 1){										
										$mongo_inputs = array();
										$mongo_inputs['parentid'] 	= $this->parentid;
										$mongo_inputs['data_city'] 	= $this->data_city;
										$mongo_inputs['module']		= $this->module;
										$mongo_data = array();
										$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
										$extrdet_upt = array();
										$extrdet_upt['attribute_search'] 		= '#'.$unique_code_search.'#';
										$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
								
										$mongo_inputs['table_data'] 			= $mongo_data;
										$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
									}
									else
									{	
										$insert_attr_search = "UPDATE tbl_companymaster_extradetails_shadow SET attribute_search='#".$unique_code_search."#' WHERE parentid='".$this->parentid."'";			
										if((strtoupper($this->module) =='DE') || (strtoupper($this->module) =='CS')){	
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_iro);
										}else{
											$insert_attr_search = $insert_attr_search."/* TMEMONGOQRY */";
											$res_attr_search 	= parent::execQuery($insert_attr_search, $this->conn_temp);
										}
									}
									
								}else{
									if($this->mongo_flag == 1 || $this->mongo_tme == 1){
										$mongo_inputs = array();
										$mongo_inputs['parentid'] 	= $this->parentid;
										$mongo_inputs['data_city'] 	= $this->data_city;
										$mongo_inputs['module']		= $this->module;
										$mongo_data = array();
										$attributes_seacrh_new = '';
										$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
										$extrdet_upt = array();
										$extrdet_upt['attribute_search'] 	= '';										
										$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
								
										$mongo_inputs['table_data'] 			= $mongo_data;
										$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
									}
									else
									{
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
								if($this->mongo_flag == 1 || $this->mongo_tme == 1){
									$mongo_inputs = array();
									$mongo_inputs['parentid'] 	= $this->parentid;
									$mongo_inputs['data_city'] 	= $this->data_city;
									$mongo_inputs['module']		= $this->module;
									$mongo_data = array();
									$attributes_seacrh_new = '';
									$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
									$extrdet_upt = array();
									$extrdet_upt['attribute_search'] 	= '';									
									$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
							
									$mongo_inputs['table_data'] 			= $mongo_data;
									$res_attr_search = $this->mongo_obj->updateData($mongo_inputs);
								}
								else
								{
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
					
					//logging starts here 	
					$new_temp_tables = $this->getOldAttributesTemp();
					$temp_attr_new		  	    = $new_temp_tables;											
					$attributes_search_old_json =  stripslashes(json_encode(addslashes(stripslashes($attributes_search_old))));	
					$attributes_seacrh_new_json =  stripslashes(json_encode(addslashes(stripslashes($attributes_seacrh_new))));	 		
					$temp_attr_new_json 		=  stripslashes(json_encode(addslashes(stripslashes($temp_attr_new['tbl_companymaster_attributes_temp']))));
					$temp_attr_old_json 		=  stripslashes(json_encode(addslashes(stripslashes($old_temp_tables['tbl_companymaster_attributes_temp']))));			
					$InsertToHistory ="INSERT INTO online_regis1.tbl_attributes_changed_log SET
														 parentid				= '".$this->parentid."',
														 data_city				= '".$this->params['data_city']."',
														 catid 					= '".$this->business_data['catids']."',															
														 attributes_search_old	= '".$attributes_search_old_json."', 
														 attributes_search_new	= '".$attributes_seacrh_new_json."',
														 temp_attr_new			= '".$temp_attr_new_json."',
														 temp_attr_old			= '".$temp_attr_old_json."',
														 update_time			= '".date('Y-m-d H:i:s')."',
														 updated_by				= '".$this->ucode."',
														 source 			    = '".$this->module."_bform_sbt'	";
														 
					$resLogHistory  =  parent::execQuery($InsertToHistory, $this->conn_idc);
					//logging ends here 
					
					if($resUpdateAttributes){
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = "Success";
						return $result_msg_arr;
					}else{
						$message = "Error found in updating attribute.";
						echo json_encode($this->send_die_message($message));
						die();
					}
				}else{
					$message = "Error found in making attribute param.";
					echo json_encode($this->send_die_message($message));
					die();
				}				
			}else{
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "No Attributes Selected.";
				return $result_msg_arr;
			}			
		}else{	
			$error_arr = array();		
			if($res_sp && $count_invalid!=0){
				$data = array();
				$i= 0;
				while($row_sp_result = parent::fetchData($res_sp)){ 
					$unique_code 			     =  $row_sp_result['unique_code'];					
					$wrong_unique_code_selection =  $row_sp_result['wrong_unique_code_selection'];
					$get_attrnames               = $this->getAttributeName($unique_code, $wrong_unique_code_selection);					
					$data[$i] 					 = $get_attrnames;
					$i++;
				}				
			}
			$error_arr['error']['code'] = 3;
			$error_arr['error']['msg'] = "Invalid attributes Selected!!";			
			$error_arr['data'] 		= $data;			
			return $error_arr;
		}
	}
	
	function getAttributeName($unique_code, $wrong_unique_code_selection){
		$final_arr = array();
		$data_arr = array();
		$ucode_arr = array();
		$final_uniq_codes = $unique_code.','.$wrong_unique_code_selection;
		$final_uniq_codes = str_replace(",","','",$final_uniq_codes);
		$getNames = "SELECT attribute_name, unique_code from d_jds.tbl_attribute_mapping where unique_code in ('".$final_uniq_codes."') GROUP BY unique_code";
		$res_attr_names = parent::execQuery($getNames, $this->conn_local);
		$count_of_total = parent::numRows($res_attr_names);
		if($res_attr_names && parent::numRows($res_attr_names)>0){
			$str = '';
			while($row_names = parent::fetchData($res_attr_names)){				
				$data_arr[$row_names['unique_code']] = $row_names['attribute_name'];
				$ucode_arr[$row_names['unique_code']] = $row_names['unique_code'];
				if($str =='' && strtolower($row_names['unique_code']) != strtolower($unique_code)){					
					$str .= $row_names['attribute_name'];					
				}else if(strtolower($row_names['unique_code']) != strtolower($unique_code)){					
					$str .= ",".$row_names['attribute_name'];
				}
			}		
			$final_arr[$data_arr[$unique_code]] = $str;			
		}	
		return $final_arr;
	}
	
	function  get_attribute_details($unique_codes,$sub_grp_id_arr){
		$resut_arr = array();
		if(count($sub_grp_id_arr) > 0 && count($unique_codes) > 0){
			$sub_grp_imp = implode("','",array_unique($sub_grp_id_arr));
			$unique_imp = implode("','",array_keys($unique_codes));		
			$str = implode("','",$this->attribute_group_ex);	
			//$get_det = "SELECT attribute_group,attribute_sub_group, attribute_name,attribute_dname,attribute_prefix,unique_code,display_position,attribute_web_position,gen_sepl_type AS attr_type_fin,display_flag FROM tbl_attribute_mapping WHERE unique_code IN ('".$unique_imp."') AND selection_flag=0 AND active_flag=1  AND assign_to_all=1  AND display_flag!=0 AND national_catid=0  AND attribute_sub_group IN ('".$sub_grp_imp."') "; //adding attribute_type in 2nd query bcoz of condition fr attribute_type in (5,6)
			
			//echo "<br>get_det:---".$get_det = " SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag as main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$str."') AND a.unique_code IN ('".$unique_imp."') AND a.selection_flag=0 AND a.active_flag=1  AND a.assign_to_all=1  AND a.display_flag!=0 AND a.national_catid=0  AND a.attribute_sub_group IN ('".$sub_grp_imp."')"; //coz assign_to_all is 0 for many selected attributes
			
			$get_det = " SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag as main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN ('".$str."') AND a.unique_code IN ('".$unique_imp."') AND a.selection_flag=0 AND a.active_flag=1  AND a.display_flag!=0 AND a.national_catid=0  AND a.attribute_sub_group IN ('".$sub_grp_imp."')";
			
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
			$str_catids = implode("','",$this->contract_final_cat_arr);	
			//$get_attribute_grp = "SELECT DISTINCT(attribute_group) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0  ORDER BY attribute_group ASC";
			//$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);

			$cat_params = array();
			$cat_params['page'] ='attribute_page_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'attribute_group';
			$cat_params['orderby']		= 'attribute_group ASC';			

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$this->contract_final_cat_arr);
			$cat_params['where']		= json_encode($where_arr);
			if(count($this->contract_final_cat_arr)>0){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				foreach($cat_res_arr['results'] as $key=>$cat_arr){
					if($cat_arr['attribute_group']!='' || $cat_arr['attribute_group']!=0){
						$attribute_group[] = $cat_arr['attribute_group'];
					}
				}
				$attribute_group_imp = implode("','",array_unique($attribute_group));
				$get_sub_det = "SELECT id,subgroup_name,sub_group_pos,attribute_group FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group>0 AND attribute_group IN ('".$attribute_group_imp."')";
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
	function addslashesArray($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
}
?>
