<?php
ob_start();

class attributes_temp_to_main_class extends DB
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
		$actionArr = array("temp_to_main");
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
		$this->contract_paid_cat_arr = array();
		$this->validFlowModule();
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		$this->business_data = $this->fetchBusinessLiveData();
		if((isset($this->business_data['catidlineage']) && $this->business_data['catidlineage'] != '') || (isset($this->business_data['catidlineage_nonpaid']) && $this->business_data['catidlineage_nonpaid'] != '')){
			$live_catlin_arr 		 = array();
			$live_catlin_nonpaid_arr = array();
			$live_catlin_arr 		 = explode("/,/",trim($this->business_data['catidlineage'],"/"));			
			$live_catlin_nonpaid_arr = explode("/,/",trim($this->business_data['catidlineage_nonpaid'],"/"));
			$live_catlin_arr = array_filter(array_merge($live_catlin_arr,$live_catlin_nonpaid_arr));
			$live_catlin_arr = 	$this->getValidCategories($live_catlin_arr);
			$this->contract_paid_cat_arr = $live_catlin_arr;
		}		
	}
	function fetchBusinessLiveData(){
		$sqlBusinessLiveData = "SELECT catidlineage, catidlineage_nonpaid, attributes_edit as facility FROM tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		$resBusinessLiveData = parent::execQuery($sqlBusinessLiveData, $this->conn_iro);
		if($resBusinessLiveData && parent::numRows($resBusinessLiveData)>0){
			return $row_live_data	=	parent::fetchData($resBusinessLiveData);
		}
	}
	function validFlowModule(){
		$module_arr = array("saveasfreelisting","cs","tme_nonpaid","de", "approval","genio_lite");
		if(!in_array(strtolower($this->module),$module_arr)){			
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
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
		$this->conn_log   		= $db['db_log'];
		
		if((strtolower($this->module)=='de') || (strtolower($this->module) =='cs')){
			$this->conn_temp	 	= $this->conn_local;
			$this->table = "tbl_companymaster_attributes_temp";
		}elseif(strtolower($this->module)=='tme_nonpaid'){
			$this->conn_temp		= $this->conn_tme;
			$this->table = "tbl_companymaster_attributes_temp";
		}
		elseif((strtolower($this->module) =='saveasfreelisting') || (strtolower($this->module) =='genio_lite')){
			$this->conn_temp		= $this->conn_idc;
			$this->table = "tbl_companymaster_attributes_temp";
		}elseif(strtolower($this->module)=='approval'){
			$this->conn_temp		= $this->conn_idc;
			$this->table = "tbl_companymaster_attributes"; // on approval fetching from idc live table
		}
		else{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		$this->docid = $this->getDocid();		
	}
	function getAttributeGrpInfo(){
		$specialAttributes	  = array();
		$subGrp				  = array();		
		$unique_code = '';
		$final_unique_codes = '';
		$unique_code_special = '';
		$this->final_unique_codes = '';
		//$specialAttrIdentifier	  = array("4"=>"2","8796093022208"=>"8"); // display_product_flag => type_flag :: doctor / fos
		$specialAttrIdentifier	  = array("4"=>"2"); // display_product_flag => type_flag :: doctor / fos
		
		if(count($this->contract_paid_cat_arr) >0){			
			$str_catids = implode("','",$this->contract_paid_cat_arr);	
			//$get_attribute_grp = "SELECT GROUP_CONCAT(DISTINCT(attribute_group)) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND attribute_group > 0 ";
			//$res_attribute_grp  = parent::execQuery($get_attribute_grp, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		= 'attributes_temp_to_main_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'attribute_group';

			$where_arr  	=	array();			
			$where_arr['catid']			= implode(",",$this->contract_paid_cat_arr);	
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				$attribute_group_arr = array();
				foreach($cat_res_arr['results'] as $key => $cat_arr) {
					if($cat_arr['attribute_group']!=''){
						$attribute_group_arr[] = $cat_arr['attribute_group'];
					}
				}
				if(count($attribute_group_arr)>0){
					$row_attribute_grp_str = implode(",",$attribute_group_arr);
				}

				//$row_attribute_grp  = parent::fetchData($res_attribute_grp);

				if($row_attribute_grp_str!='' || $row_attribute_grp_str!=0){
					$attribute_group = $row_attribute_grp_str;	
					$this->attribute_group = $attribute_group;	
					$attribute_group_str   = 	str_replace(",","','", $attribute_group);
					$getSubGrp = "SELECT GROUP_CONCAT(DISTINCT (id)) as attribute_sub_group FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$attribute_group_str."') AND attribute_group >0 ";
					$resSubGrp =  parent::execQuery($getSubGrp, $this->conn_idc);
					$rowSubGrp = parent::fetchData($resSubGrp);
					if($rowSubGrp['attribute_sub_group']!=''){
						$attribute_sub_groups 		= $rowSubGrp['attribute_sub_group'];
						$this->attribute_sub_groups = $attribute_sub_groups;
					}				
				}			
			}
			
			$getTemp_attrs = "SELECT GROUP_CONCAT(attribute_id) as attribute_id FROM ".$this->table." WHERE parentid='".$this->parentid."' ";
			$resTemp_res   = parent::execQuery($getTemp_attrs, $this->conn_temp);
			if($resTemp_res  && parent::numRows($resTemp_res)>0){
				$rowTemp_attr = parent::fetchData($resTemp_res);
				$temp_attr = $rowTemp_attr['attribute_id'];
				if($temp_attr!='' || $temp_attr!=null){
					$unique_code = $temp_attr;
				}
			}
			
			//~ if($unique_code=='' || $unique_code==null){
				//~ if($this->attribute_group!='' && $this->attribute_sub_groups!=''){
					//~ $attr_grps = str_replace(",","','",$this->attribute_group);
					//~ $attr_sub_grps = str_replace(",","','",$this->attribute_sub_groups);
					//~ $getUniqueCode = "SELECT GROUP_CONCAT(DISTINCT(unique_code)) as unique_code FROM tbl_attribute_mapping WHERE attribute_group IN ('".$attr_grps."')  AND selection_flag=0 AND active_flag=1  AND assign_to_all=1  AND display_flag!=0 AND national_catid=0  AND attribute_sub_group IN ('".$attr_sub_grps."')";
					//~ $resUniqueCode = parent::execQuery($getUniqueCode, $this->conn_local);
					//~ if($resUniqueCode && parent::numRows($resUniqueCode)>0){
						//~ $rowUniqueCode = parent::fetchData($resUniqueCode);
						//~ if($rowUniqueCode['unique_code']){
							//~ $unique_code = $rowUniqueCode['unique_code'];					
						//~ }				
					//~ }
				//~ }
			//~ }
			
			foreach($specialAttrIdentifier as $display_product_flag=>$type_flag){
				 $display_product_flag = intval($display_product_flag);
				 $type_flag = intval($type_flag);
				//$getSpecialAttr = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$str_catids."') AND display_product_flag&$display_product_flag=$display_product_flag LIMIT 1";		
				//$resSpecialAttr = parent::execQuery($getSpecialAttr, $this->conn_local);
				$cat_params = array();
				$cat_params['page'] 		= 'attributes_temp_to_main_class';
				$cat_params['skip_log']		= '1';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid';
				$cat_params['limit']		= '1';
				$cat_params['skip_log']		= '1';

				$where_arr  	=	array();			
				$where_arr['catid']					= implode(",",$this->contract_paid_cat_arr);
				$where_arr['display_product_flag']	= $display_product_flag;
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){ 
					$getSpecailUnCode = "SELECT GROUP_CONCAT(DISTINCT(attribute_id)) as unique_code FROM tbl_attribute_id_generator WHERE type_flag&$type_flag=$type_flag";
					$resSpecailUnCode = parent::execQuery($getSpecailUnCode, $this->conn_iro);
					if($resSpecailUnCode && parent::numRows($resSpecailUnCode)>0){
						$rowSpecailUnCode = parent::fetchData($resSpecailUnCode);
						if($unique_code){
							$unique_code = $unique_code.','.$rowSpecailUnCode['unique_code'];
						}else{
							$unique_code  = $rowSpecailUnCode['unique_code'];
						}
					}
				}
			}
			
			if($unique_code!=''){						
				$this->final_unique_codes = $unique_code;
			}
		}
		
	}
	function tempToMain(){ //active_flag==1 by default	
		//$docid 				   = $this->getDocid();		
		$subGrp_acc_categories = $this->getAttributeGrpInfo();
		$get_log_before_update = array();
		$get_log_after_update = array();
		$insert_data_str = '';		
		$get_log_before_update = $this->getMainTblAttributes();
		if($this->final_unique_codes!=''){
			
			$str_replace = str_replace(",","','",$this->final_unique_codes);			
			$delete_entry1  = "DELETE FROM tbl_companymaster_attributes WHERE docid = '".$this->docid."' AND attribute_id NOT IN ('".$str_replace."') "; // not removing all as we don't have special attributes in temp table
			$res_del_entry = parent::execQuery($delete_entry1, $this->conn_iro);
			
			$sel_temp_attrs = "SELECT * FROM ".$this->table." WHERE parentid='".$this->parentid."' AND attribute_id  IN ('".$str_replace."') AND attribute_dname!=''";
			$res_temp_attrs =  parent::execQuery($sel_temp_attrs, $this->conn_temp);
			if($res_temp_attrs &&  parent::numRows($res_temp_attrs)>0 ){
				while($row_temp_attrs =  parent::fetchData($res_temp_attrs)){
					if($row_temp_attrs['parentid']!='' && $row_temp_attrs['attribute_id']!='' && $row_temp_attrs['attribute_name']!=''){
						$row_temp_attrs 	= 	$this->addslashesArray($row_temp_attrs);
						if($row_temp_attrs['attribute_name'] && $row_temp_attrs['sub_group_name']){
							$insert_data_str .= "('".$this->docid."', '".$this->parentid."', '".$this->data_city."', '".$row_temp_attrs['attribute_id']."' , '".$row_temp_attrs['attribute_name']."', '".$row_temp_attrs['attribute_dname']."', '".$row_temp_attrs['attribute_value']."', '".$row_temp_attrs['attribute_type']."', '".$row_temp_attrs['attribute_sub_group']."', '".$row_temp_attrs['sub_group_name']."','1', '".$row_temp_attrs['display_flag']."', '".$row_temp_attrs['sub_group_position']."',
							 '".$row_temp_attrs['attribute_position']."', '".$row_temp_attrs['attribute_prefix']."', '".$row_temp_attrs['main_attribute_flag']."', '".$row_temp_attrs['main_attribute_position']."', '".$this->ucode."', '".date('Y-m-d H:i:s')."'  ) ".",";
						}
					}
				}
				if($insert_data_str){
					$insert_data_str = rtrim($insert_data_str,",");
					$fin_insert = "INSERT INTO tbl_companymaster_attributes (docid,parentid, city, attribute_id,attribute_name,attribute_dname, attribute_value, attribute_type, attribute_sub_group,sub_group_name,active_flag, display_flag,sub_group_position,attribute_position,attribute_prefix,main_attribute_flag,main_attribute_position,updatedby,updatedon) VALUES $insert_data_str
					
					
					ON DUPLICATE KEY UPDATE
											docid  				= VALUES(docid),
											parentid  			= VALUES(parentid),
											city  				= VALUES(city),
											attribute_id	= VALUES(attribute_id),
											attribute_name  	= VALUES(attribute_name),
											attribute_dname  	= VALUES(attribute_dname),
											attribute_value  		= VALUES(attribute_value),
											attribute_type  		= VALUES(attribute_type),
											attribute_sub_group  = VALUES(attribute_sub_group),
											sub_group_name  	= VALUES(sub_group_name),
											active_flag  	= VALUES(active_flag),
											display_flag  	= VALUES(display_flag),
											sub_group_position  	= VALUES(sub_group_position),
											attribute_position  	= VALUES(attribute_position),
											attribute_prefix  	    = VALUES(attribute_prefix),
											main_attribute_flag  	= VALUES(main_attribute_flag),
											main_attribute_position = VALUES(main_attribute_position),
											updatedby  	= VALUES(updatedby),
											updatedon = VALUES(updatedon)";
					$res_insert = parent::execQuery($fin_insert, $this->conn_iro);					
					if(isset($this->params['isDealClose']) && $this->params['isDealClose']==1){
						$res_insert = parent::execQuery($fin_insert, $this->conn_idc);
					}
					
				}			
				if($res_insert){
					$retArr['errorCode'] 	=	0;
					$retArr['errorStatus'] 	=	"Success";
				}else{
					$retArr['errorCode'] 	=	1;
					$retArr['errorStatus'] 	=	"Query Failed";
				}			
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus'] 	=	"Failed";
			}
			$get_log_after_update = $this->getMainTblAttributes();	
			$this->attributes_log($get_log_before_update, $get_log_after_update);
		}else{
			$delete_entry  = "DELETE FROM tbl_companymaster_attributes WHERE docid = '".$this->docid."'";	
			$res_del     = parent::execQuery($delete_entry, $this->conn_iro); //commented will do if found attributes witoutt any eligible category
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"data not found in temp table";
			$get_log_after_update = $this->getMainTblAttributes();	
			$this->attributes_log($get_log_before_update, $get_log_after_update);
		}
		
		return $retArr;
	}
	function getMainTblAttributes(){
		$attrdata_str = '';
		$retArr = array();
		$getDetails = "SELECT attribute_id,attribute_name,attribute_dname, attribute_value,attribute_sub_group,sub_group_name FROM tbl_companymaster_attributes WHERE parentid ='".$this->parentid."' ";
		$resDetails = parent::execQuery($getDetails, $this->conn_iro);
		if($resDetails && parent::numRows($resDetails)>0){
			while($rowDetails = parent::fetchData($resDetails)){
				$retArr[] = $rowDetails;
			}
		}
		if(count($retArr)>0){
			return json_encode($retArr);
		}else{
			return $attrdata_str;
		}
	}
	function getDocid(){
		$docid = '';		
		$sel_docid = "SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_docid = parent::execQuery($sel_docid, $this->conn_iro);
		if($res_docid && parent::numRows($res_docid)>0){
			$row_docid= parent::fetchData($res_docid);
			if($row_docid['docid']!=''){
				$docid = $row_docid['docid'];
				//$this->docid = $docid;
			}
		}
		return $docid;
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
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
	function attributes_log($get_log_before_update, $get_log_after_update){		
		if(($get_log_before_update!='') || ($get_log_after_update!='')){
			$insert_log = "INSERT INTO online_regis1.tbl_attributes_deletion_log (parentid,updatedBy,updateOn,log_before_del,log_after_del,flow_module) VALUES ('".$this->parentid."', '".$this->ucode."', '".date('Y-m-d H:i:s')."', '".addslashes(stripslashes($get_log_before_update))."','".addslashes(stripslashes($get_log_after_update))."', '".$this->module."' )";
			$res_log    = parent::execQuery($insert_log, $this->conn_log);
		}		
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
