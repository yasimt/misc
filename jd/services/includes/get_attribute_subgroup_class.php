<?php
//get_attribute_subgroup_class.php
ob_start();
class get_attribute_subgroup_class extends DB{
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	function __construct($params){
		
		$parentid 		= trim($params['parentid']);		
		$data_city 		= trim($params['data_city']);		
		
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
		
		
			
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;				
		$this->params 		= $params;		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();		
		
		if(trim($params['key'])==''){
            $message = "Key is missing.";
            echo json_encode($this->send_die_message($message));
            die();
        }else{
			$valid_key = $this->validateSecretKey($params);
			if($valid_key !=1){
				$message = "Access Denied.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		
		$this->live_data        = $this->fetchBusinessLiveData();	
		$this->catids           = $this->getContractLiveCategories();	
		$this->attribute_groups = $this->setAttributegroup();
		
	}
	
	function setServers(){	
		GLOBAL $db;		
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');				
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
	}
	
	function fetchBusinessLiveData(){
		$sqlBusinessLiveData = "SELECT catidlineage as catids, attributes_edit as facility FROM tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		$resBusinessLiveData = parent::execQuery($sqlBusinessLiveData, $this->conn_iro);
		if($resBusinessLiveData && parent::numRows($resBusinessLiveData)>0){
			return $row_live_data	=	parent::fetchData($resBusinessLiveData);
		}
	}
	function getContractLiveCategories(){
		if(isset($this->live_data['catids']) && $this->live_data['catids'] != ''){
			$live_catlin_arr = array();
			$live_catlin_arr = explode("/,/",trim($this->live_data['catids'],"/"));
			$live_catlin_arr = array_filter($live_catlin_arr);
			$live_catlin_arr = 	$this->getValidCategories($live_catlin_arr);
			$this->contract_paid_cat_arr = $live_catlin_arr;
		}
	}
	function setAttributegroup(){		
		$str_catids = implode(",",$this->contract_paid_cat_arr);	
		//$get_attribute_grp = "SELECT DISTINCT(attribute_group) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0   ORDER BY attribute_group ASC";
		//$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		='get_attribute_subgroup_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'attribute_group';			
			$cat_params['orderby']		= 'attribute_group ASC';

			$where_arr  	=	array();
			if($str_catids!=''){
				$where_arr['catid']			= $str_catids;
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
			foreach($cat_res_arr['results']	as $key =>$row_attribute_grp){
				if($row_attribute_grp['attribute_group']!='' || $row_attribute_grp['attribute_group']!=0){
					$attribute_group[] = $row_attribute_grp['attribute_group'];
				}
			}
			$attribute_group_ex = array_filter($attribute_group);
			$this->attribute_group_ex = array_unique($attribute_group_ex);	
			if(count($this->attribute_group_ex)>0){
				$sub_names = array();
				$attribute_group   = implode("','",$this->attribute_group_ex);
				$get_sub_heading  = "SELECT * FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$attribute_group."') ORDER BY sub_group_pos ASC"; //online_regis1
				$res_sub_heading  = parent::execQuery($get_sub_heading, $this->conn_idc); //take idc connection, only this table .$this->conn_idc 								
				if($res_sub_heading && parent::numRows($res_sub_heading)>0){					
					while($row_sub_heading  = parent::fetchData($res_sub_heading)){		
						if($row_sub_heading['id']!='' && $row_sub_heading['subgroup_name']!=''){
							$sub_names[$row_sub_heading['id']] = $row_sub_heading['subgroup_name'];						
						}
					}
				}
				if(count($sub_names)>0){
					$this->sub_names = $sub_names;
				}
			}					
		}
	}	
	function returnSubgroup(){		
		$this->facility = $this->live_data['facility'];						
		if($this->facility!=''){
			$attr_to_compare = array();
			$temp1_arr	= explode("***",$this->facility);					
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
								$old_exist_arr[addslashes(stripslashes($ucw_key_attrb))] = $temp4_arr[1];
								if(!in_array(strtolower($ucw_key_attrb),$attr_to_compare)){
									$attr_to_compare[] = strtolower($ucw_key_attrb);						
								}
							}
						}
					}
				}
			}			
			if(count($old_exist_arr) > 0){				
				$result = array();				
				$attr_found_arr = array();
				$old_exist_arr_str = implode("','",array_keys($old_exist_arr));				
				$attribute_group   = implode("','",$this->attribute_group_ex);
				if($old_exist_arr_str!=''){					
				
				$get_sub_id = "SELECT attribute_group,attribute_sub_group,attribute_name,unique_code,display_position FROM d_jds.tbl_attribute_mapping WHERE attribute_name IN ('".$old_exist_arr_str."') AND attribute_group IN ('".$attribute_group."') ORDER BY attribute_sub_group ASC"; 						
					$res_sub_id  = parent::execQuery($get_sub_id, $this->conn_local);
					if($res_sub_id &&  parent::numRows($res_attribute_grp)>0){
						while($row_sub_id = parent::fetchData($res_sub_id)){
							$value = '';
							$attribute_sub_group = $row_sub_id['attribute_sub_group'];	
							$attribute_name		 = $row_sub_id['attribute_name'];								
							if(!in_array(strtolower($attribute_name),$attr_found_arr)){
								$attr_found_arr[] = strtolower($attribute_name);
								if($old_exist_arr[$attribute_name]){
									$value = $old_exist_arr[$attribute_name];
								}			
								$result[$this->sub_names[$attribute_sub_group]][] = $row_sub_id['attribute_name'].'~'.$value;
							}
						}
					}					
					$missing_attrs = array_diff($attr_to_compare,$attr_found_arr);
					if(count($result)> 0){ 
						$result_msg_arr = array();
						$result_msg_arr['data'] 		 = $result;						
						if(count($missing_attrs)>0){
							$result_msg_arr['unknown'] =  array_merge(array_filter($missing_attrs));
						}
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg']  = "Success";	
						echo json_encode($result_msg_arr);						
					}else{						
						$message  = "no Attributes";
						echo json_encode($this->send_die_message($message));
						die();
					}
				}else{
					$message  = "no Attributes";
					echo json_encode($this->send_die_message($message));
					die();
				}				
			}else{
				$message  = "no Attributes";
				echo json_encode($this->send_die_message($message));
				die();
			}				
		}else{
			$message = "No Attributes Found for given parentid";
			echo json_encode($this->send_die_message($message));
			die();
		}
	}	
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	private function validateSecretKey($params){
		$action = trim($params['action']);
		$key = trim($params['key']);
		$validate_flag = 0;
		$sqlSecretKeyChk = "SELECT secret_key FROM online_regis1.tbl_api_access_info WHERE action = '".addslashes($action)."'";
		$resSecretKeyChk = parent::execQuery($sqlSecretKeyChk, $this->conn_idc);		
		if($resSecretKeyChk && parent::numRows($resSecretKeyChk)>0){
			$row_secret_key = parent::fetchData($resSecretKeyChk);
			$secretkey		= $row_secret_key['secret_key']; 
			$original_key 	= hash_hmac('sha256', $action,($secretkey.strtolower($this->parentid)));
			$given_key 		= $key;  
			if((md5($original_key)===md5($given_key))){
				$validate_flag = 1;
			}
		}
		return $validate_flag;
	}
	function getValidCategories($total_catlin_arr){
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
}
?>
