<?php
ob_start();
ini_set('max_execution_time', 0); 

class buffetDetailsClass extends DB{
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
		$data			= trim($params['data']);
		
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
		$actionArr = array("check_buffet","update_buffet");
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
		$this->data			= $data;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$this->categoryClass_obj = new categoryClass();		
		$this->setServers();
		
		$this->nonpaid_catids = array();
		$this->nonpaid_catids = $this->fetchNonpaidCategories();
		
		if($this->params['live_data'] == 1){
			$this->business_data = $this->fetchBusinessLiveData();
		}else{
			$this->business_data = $this->fetchBusinessTempData();
		}		
		$this->contract_paid_cat_arr 	= array();		
		$this->contract_final_cat_arr 	= array();		
		if($this->params['live_data'] == 1)	{
			$this->getContractLiveCategories();
		}else{
			$this->getContractTempCategories();
		}	
	
		$this->getDocid();
		$this->get_vertical_flag();
		
		$this->time_in_24_format = array("00:00","00:30","01:00","01:30","02:00","02:30","03:00","03:30","04:00","04:30","05:00","05:30","06:00","06:30","07:00","07:30","08:00","08:30","09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30","20:00","20:30","21:00","21:30","22:00","22:30","23:00","23:30");
		
		$this->timings_array=array("24:00"=>"Open 24 Hrs","00:00"=>"00:00 AM(Midnight)","00:30"=>"00:30 AM (Midnight)","01:00"=>"01:00 AM","01:30"=>"01:30 AM","02:00"=>"02:00 AM","02:30"=>"02:30 AM","03:00"=>"03:00 AM","03:30"=>"03:30 AM","04:00"=>"04:00 AM","04:30"=>"04:30 AM","05:00"=>"05:00 AM","05:30"=>"05:30 AM","06:00"=>"06:00 AM","06:30"=>"06:30 AM","07:00"=>"07:00 AM","07:30"=>"07:30 AM","08:00"=>"08:00 AM","08:30"=>"08:30 AM","09:00"=>"09:00 AM","09:30"=>"09:30 AM","10:00"=>"10:00 AM","10:30"=>"10:30 AM","11:00"=>"11:00 AM","11:30"=>"11:30 AM","12:00"=>"12:00 PM (Noon)","12:30"=>"12:30 PM (Noon)","13:00"=>"01:00 PM","13:30"=>"01:30 PM","14:00"=>"02:00 PM","14:30"=>"02:30 PM","15:00"=>"03:00 PM","15:30"=>"03:30 PM","16:00"=>"04:00 PM","16:30"=>"04:30 PM","17:00"=>"05:00 PM","17:30"=>"05:30 PM","18:00"=>"06:00 PM","18:30"=>"06:30 PM","19:00"=>"07:00 PM","19:30"=>"07:30 PM","20:00"=>"08:00 PM","20:30"=>"08:30 PM","21:00"=>"09:00 PM","21:30"=>"09:30 PM","22:00"=>"10:00 PM","22:30"=>"10:30 PM","23:00"=>"11:00 PM","23:30"=>"11:30 PM","Closed"=>"Closed","Open 24 Hrs"=>"Open 24 Hrs");
		
		$this->timings_array_reverse=array("Open 24 Hrs"=>"24:00","24:00"=>"24:00","00:00 AM(Midnight)"=>"00:00","00:00 AM"=>"00:00","00:30 AM (Midnight)"=>"00:30","00:30 AM"=>"00:30","01:00 AM"=>"01:00","01:30 AM"=>"01:30","02:00 AM"=>"02:00","02:30 AM"=>"02:30","03:00 AM"=>"03:00","03:30 AM"=>"03:30","04:00 AM"=>"04:00","04:30 AM"=>"04:30","05:00 AM"=>"05:00","05:30 AM"=>"05:30","06:00 AM"=>"06:00","06:30 AM"=>"06:30","07:00 AM"=>"07:00","07:30 AM"=>"07:30","08:00 AM"=>"08:00","08:30 AM"=>"08:30","09:00 AM"=>"09:00","09:30 AM"=>"09:30","10:00 AM"=>"10:00","10:30 AM"=>"10:30","11:00 AM"=>"11:00","11:30 AM"=>"11:30","12:00 PM (Noon)"=>"12:00","12:00 PM"=>"12:00","12:30 PM (Noon)"=>"12:30","12:30 PM"=>"12:30","01:00 PM"=>"13:00","01:30 PM"=>"13:30","02:00 PM"=>"14:00","02:30 PM"=>"14:30","03:00 PM"=>"15:00","03:30 PM"=>"15:30","04:00 PM"=>"16:00","04:30 PM"=>"16:30","05:00 PM"=>"17:00","05:30 PM"=>"17:30","06:00 PM"=>"18:00","06:30 PM"=>"18:30","07:00 PM"=>"19:00","07:30 PM"=>"19:30","08:00 PM"=>"20:00","08:30 PM"=>"20:30","09:00 PM"=>"21:00","09:30 PM"=>"21:30","10:00 PM"=>"22:00","10:30 PM"=>"22:30","11:00 PM"=>"23:00","11:30 PM"=>"23:30","Closed"=>"Closed","Open 24 Hrs"=>"Open 24 Hrs");
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
			$cat_params = array();
			$cat_params['page'] ='buffetDetailsClass';
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
					$this->attribute_group = implode("','",$attribute_group_ex);					
				}else{										
					$this->attribute_group = implode("','",$attribute_group_ex);					
				}	
			}			
		}		
		$sub_group_details_arr = array();										
		if(count($this->attribute_group_ex)>0){			
			$str = implode("','",$this->attribute_group_ex);
			$get_sub_heading  = "SELECT * FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$str."') ORDER BY sub_group_pos ASC"; //online_regis1
			$res_sub_heading  = parent::execQuery($get_sub_heading, $this->conn_idc); //take idc connection, only this table .$this->conn_idc 
			if($res_sub_heading && parent::numRows($res_sub_heading)>0){
				$qry_cond = '';
				while($row_sub_heading  = parent::fetchData($res_sub_heading)){										
					$attribute_group = $row_sub_heading['attribute_group'];					
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['subgroup_name']   = $row_sub_heading['subgroup_name'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['attribute_group'] = $row_sub_heading['attribute_group'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['sub_group_pos']   = $row_sub_heading['sub_group_pos'];
					$sub_group_details_arr[$attribute_group][$row_sub_heading['id']]['id']              = $row_sub_heading['id'];
				}
				if(count($sub_group_details_arr)>0){
					$this->sub_group_details_arr = $sub_group_details_arr;					
				}
				
			}			
		}else{
			$message = "Buffet category not found.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
	}	
	
	function checkBuffetDetails(){ 		
		
		$result_msg_arr = array();
		$eligible_cats  = array("1"=>"Restaurant"); //1->restaurant
		$eligible_cats_keys = array_keys($eligible_cats);
		if(in_array(1,$this->attribute_group_ex)){			
			$getBuffet = "SELECT * FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."' AND attribute_id='Rbuf'  AND (attribute_dname!=''  AND attribute_value!='')";
			//echo "<pre>getBuffet:--".$getBuffet;
			$resBuffet = parent::execQuery($getBuffet, $this->conn_temp);	
			if($resBuffet && parent::numRows($resBuffet)>0){
				$rowBuffet = parent::fetchData($resBuffet);
				if($rowBuffet['attribute_dname']!='' && $rowBuffet['attribute_value']!=''){					
					$attribute_value 	 = $rowBuffet['attribute_value'];
					$attribute_value 	 = str_replace("Buffet-",'',$attribute_value);
					
					$attribute_value_arr = explode("|~|",$attribute_value);
					$timingArr           = array(); $fromArr= array(); $toArr = array(); $daysFin= array(); 
					
					$day_array = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun"); 		
					$days      = array('Mon'=>'1','Tue'=>'2','Wed'=>'3','Thu'=>'4','Fri'=>'5','Sat'=>'6','Sun'=>'7');			 
					$timmingArrCount = array();
					$priceArr     = array();
					$titleArr     = array();
					$mealArr      = array();
					$mealTypeArr  = array();
					$AgeGRoupArr  = array();
														
					$attribute_value_arr = array_filter($attribute_value_arr);
					//~ echo "<pre>attribute_value_arr:--";print_r($attribute_value_arr);
					if(count($attribute_value_arr)>0){
						$mainIndex = 0;
						foreach($attribute_value_arr as $key=>$value){
							$value_arr = explode("$#$",trim($value));							
							//~ echo "<pre>value_arr:--";print_r($value_arr);
							$ageLimitArr  = array();
							$heightArr    = array();	
							foreach($value_arr as $key1=>$value1){								
								if($key1==0){ //price									
									$price = str_replace("Rs.","",$value1);
									$price = str_replace("#","",$price);
									$priceArr = $price;
								}elseif($key1==1){  //meal									
									if(strpos($value1,"(") || strpos($value1,")")){
										$val=''; $str= ''; $str1='';
										$val = explode(" ",$value1);
										if(count($val)>2){
											if($val[1]!=''){
												$str = str_replace("(","",$val[1]);
												$str = str_replace(")","",$str);
												
												$str1 = str_replace("(","",$val[2]);
												$str1 = str_replace(")","",$str1);
												
												$mealTypeArr = $str." ".$str1;
											}else{
												$mealTypeArr = "";
											}
										}else{
											if($val[1]!=''){
												$str = str_replace("(","",$val[1]);
												$mealTypeArr = str_replace(")","",$str);
											}else{
												$mealTypeArr = "";
											}
										}										
										$mealArr  = $val[0];
										
									}
								}elseif($key1==2){ //timings										
									$timeStrExp = explode("-",$value1);
									$startTime  = $timeStrExp[0];
									$endTime    = $timeStrExp[1];
									$startTimeExp = explode(",",$startTime);
									$endTimeExp   = explode(",",$endTime);
					
									foreach($startTimeExp as $key_st => $value_st){								 
										$start_time_arr = explode("-",$value_st);
										$end_time_arr    = explode('-',$endTimeExp[$key_st]);										
										$findex = 0; $tindex = 0;
										foreach($start_time_arr as $key_each =>$value_each){													
											if(strtolower($value_each)!='closed' && $key_each ==0){												
												$data['timingsArr'][$mainIndex]['from'][$findex] = $this->timings_array_reverse[$value_each];
												$findex++;
											}
											
										}
										foreach($end_time_arr as $key_each =>$value_each){										
											if(strtolower($value_each)!='closed'){
												$data['timingsArr'][$mainIndex]['to'][$tindex] = $this->timings_array_reverse[$value_each];
												$tindex++;
											}											
										}										
									}
								
								}elseif($key1==3){ //days_selected									
									$new_arr = array();
									$days_arr = explode(",",$value1);		
									//~ echo "<pre>days_arr:--";print_r($days_arr);
									if(count($days_arr)>1){
										foreach($days_arr as $k=>$val){
											$result = explode("-",$val);
											//~ echo "<pre>result:--";print_r($result);										
											if(count($result)>1){											
												$index1 = $days[$result[0]];
												$index2 = $days[$result[1]];			
												$res    = array_slice($days, ($index1-1), $index2);	
												//~ echo "<pre>res:::";print_r($res);
												foreach($res as $k=>$v){
													array_push($new_arr,$k);
												}
											}else{
												array_push($new_arr,$result[0]);
											}
										}
									}else{
										//~ array_push($new_arr,$days_arr[0]);
										$result = explode("-",$days_arr[0]);
										//~ echo "<pre>result: else--";print_r($result);										
										if(count($result)>1){											
											$index1 = $days[$result[0]];
											$index2 = $days[$result[1]];			
											$res    = array_slice($days, ($index1-1), $index2);	
											//~ echo "<pre>res:::";print_r($res);
											foreach($res as $k=>$v){
												array_push($new_arr,$k);
											}
										}else{
											array_push($new_arr,$result[0]);
										}
									}
									
									//~ echo "<pre>new_arr:--";print_r($new_arr);																		
									
									$dindex = 0;										
									foreach($day_array as $dk1=>$dv1){										
										if(!in_array($dv1,$new_arr)){
											$data['timingsArr'][$mainIndex]['days'][$dindex][$dv1] = 0;
										}else if(in_array($dv1,$new_arr)){
											$data['timingsArr'][$mainIndex]['days'][$dindex][$dv1] = 1;
										}
									}
									$dindex++;
									
								}elseif($key1==4){ //title
									$title = str_replace("[",'',$value1);
									$title = str_replace("]",'',$title);
									$titleArr = $title;
								}elseif($key1==5){ //Age Group									
									if (strpos($value1, 'Age Group-') !== false) {
										$str_arr = explode("-",$value1);												
										if(count($str_arr)>1){ 
											$age    = str_replace("[","",$str_arr[1]);		
											$age    = str_replace("]","",$age);		
											$AgeGRoupArr = $age;			
										}else{
											$AgeGRoupArr='';
										}
									}
								}elseif($key1==6){  //age
									if(strpos($value1, 'yr') !== false){
										$val = str_replace("yrs","",$value1);
										$val = trim($val);
										if($val){											
											$val  = str_replace("]","",$val);
											$val = str_replace("[","",$val);
											$ageLimitArr = $val;
										}										
										else
											$ageLimitArr = '';
									}
									if(strpos($value1, 'inch') !== false) {
										$val = str_replace("inches","",$value1);
										$val = str_replace("]","",$val);
										$val = str_replace("[","",$val);
										$val = trim($val);
										if($val) 
											$heightArr = $val;
										else
											$heightArr = '';
										
									}
								}else if($key1==7){ //height
									if(strpos($value1, 'inch') !== false) {
										$val = str_replace("inches","",$value1);
										$val = str_replace("]","",$val);
										$val = str_replace("[","",$val);
										$val = trim($val);
										if($val) 
											$heightArr = $val;
										else
											$heightArr = '';
										
									}
									if(strpos($value1, 'yr') !== false){
										$val = str_replace("yrs","",$value1);
										$val = trim($val);
										if($val){
											$val  = str_replace("]","",$val);
											$val = str_replace("[","",$val);
											$ageLimitArr = $val;
										}else
											$ageLimitArr = '';
									}
								}
								
							}							
							$mainIndex++;
							$data['price'][$key] = $priceArr;
							$data['title'][$key] = $titleArr;
							$data['meal'][$key]  = $mealArr;
							$data['mealType'][$key] = $mealTypeArr;
							$data['ageGroup'][$key] = $AgeGRoupArr;
							$data['ageLimit'][$key] = $ageLimitArr;
							$data['height'][$key]   = $heightArr;							
						}
					}
				
					$countArr = array();					
					foreach($data['timingsArr'] as $key=>$value){												
						foreach($value as $key1=>$value1){
							if($key1=='days'){
								foreach($value1 as $key2=>$value2){
									array_push($countArr, 1);
								}								
							}							
						}
					}
					if(count($countArr)>0){
						$data['timmingArrCount'] = $countArr;
					}					
					$result_msg_arr['data'] = $data;
				}else{
					$result_msg_arr['data'] = 'No Existng Buffet';
				}				
			}else{
				$result_msg_arr['data'] = 'No Existng Buffet';
			}
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg']  = "Success";			
			return $result_msg_arr;
		}else{			
			//$deleteBuffet = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."' AND attribute_id='Rbuf'";			
			//$res_del 	= parent::execQuery($deleteBuffet, $this->conn_temp);	
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg']  = "Buffet Categories Not Found!!";			
			return $result_msg_arr;
		}
	}
	
	function updateBuffetDetails(){						
		
		$eligible_cats      = array("1"=>"Restaurant");
		if(in_array(1,$this->attribute_group_ex)){			
			
			$existingData = $this->getLogs();			
			
			//$getAttributeMapping = "SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag AS main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN (1) AND a.unique_code IN ('rbuf') AND a.selection_flag=0 AND a.active_flag=1  AND a.display_flag!=0 AND a.national_catid=0  ";
			
			$getAttributeMapping = "SELECT a.attribute_group,a.attribute_sub_group, a.attribute_name,a.attribute_dname,a.attribute_prefix,a.unique_code,a.display_position,a.attribute_web_position,a.gen_sepl_type AS attr_type_fin,a.display_flag,b.attribute_type,a.main_attribute_flag AS main_attribute_flag,a.subgroup_web,b.input_Value  FROM tbl_attribute_mapping a JOIN tbl_attribute_master b USING(unique_code) WHERE a.attribute_group IN (1) AND a.unique_code IN ('rbuf') AND a.selection_flag=0  ";
			$resAttributeMapping = parent::execQuery($getAttributeMapping, $this->conn_local);
			if($resAttributeMapping && parent::numRows($resAttributeMapping) > 0){
				$rowAttributeMapping =   parent::fetchData($resAttributeMapping);				
			}
			//~ echo "<pre>updateData:--";print_r($this->params['data']);
			if(isset($this->params['type']) && trim($this->params['type'])=='no'){
				$delEntry = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".trim($this->parentid)."' AND attribute_id='Rbuf' ";				
				$resDel	= parent::execQuery($delEntry, $this->conn_temp);
				if($resDel){
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg']  = "Success";			
					return $result_msg_arr;
				}else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg']  = "Delete Query Failed!!";			
					return $result_msg_arr;
				}
				
			}else if(count($rowAttributeMapping)>0 && isset($this->params['data']) && $this->params['data']!=''){
				$dataPassed 			= $this->params['data'];
				$unique_code		 	= $rowAttributeMapping['unique_code'];
				$attribute_group		= $rowAttributeMapping['attribute_group'];
				$attribute_name  		= $rowAttributeMapping['attribute_name'];
				$attr_type_fin  		= $rowAttributeMapping['attr_type_fin'];
				$attribute_sub_group 	= $rowAttributeMapping['attribute_sub_group'];
				$attribute_web_position = $rowAttributeMapping['attribute_web_position'];
				$attribute_prefix		= $rowAttributeMapping['attribute_prefix'];
				$main_attribute_flag	= $rowAttributeMapping['main_attribute_flag'];
				$display_position		= $rowAttributeMapping['display_position'];				
				$sub_grp_name 			= $this->sub_group_details_arr[$attribute_group][$attribute_sub_group]['subgroup_name']; 
				$sub_group_pos		 	= $this->sub_group_details_arr[$attribute_group][$attribute_sub_group]['sub_group_pos'];				
				if($rowAttributeMapping['display_flag']!=1){
					$display_flag = 0;
				}else{
					$display_flag = $rowAttributeMapping['display_flag'];
				}								
							
				//~ echo "<pre>timmingArr:--";print_r($this->params['data']['timmingArr']);
				$timmingArr  = $this->params['data']['timmingArr'];
				$title 	 	 = $this->params['data']['title'];
				$price 	 	 = $this->params['data']['price'];
				$meal 		 = $this->params['data']['meal'];
				$mealType 	 = $this->params['data']['mealType'];
				$ageGroup 	 = $this->params['data']['ageGroup'];
				$ageLimit 	 = $this->params['data']['ageLimit'];
				$height 	 = $this->params['data']['height'];
				$timmingArrCount = $this->params['data']['timmingArrCount'];
				$str_name1 = ''; $str_name_final = '';			
				$days_arr 		  = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");	
				$days_arr_indexed = array("Mon"=>"0","Tue"=>"1","Wed"=>"2","Thu"=>"3","Fri"=>"4","Sat"=>"5","Sun"=>"6");	
				//~ echo "<br>timmingArrCount:--";print_r($timmingArrCount);
				
				//Breakfast (Veg), Breakfast (Non-Veg), Lunch (Veg), Lunch (Non-Veg), Dinner (Veg), Dinner (Non-Veg)
				//Price$#$type$#$time$#$Day$#$Title$#$[age group+Text for kids]
				
				for($i=0; $i<count($timmingArrCount);$i++){						
					$days_fin_str_1 = '';
					$final_time 	= '';
					$price1='';	$title1='';	$days1=''; $meal1=''; $mealType1='';	 $ageGroup1=''; $ageLimit1=''; $height1=''; $days=''; $fromTiming = ''; $toTimings='';
					$price1 	= (!empty($price[$i])) ? $price[$i] : '';
					$title1 	= (!empty($title[$i])) ? $title[$i] :"";
					$meal1 	    = (!empty($meal[$i])) ? $meal[$i] : "";
					$mealType1  = (!empty($mealType[$i])) ? $mealType[$i]: "" ;
					$ageGroup1  = (!empty($ageGroup[$i])) ? $ageGroup[$i]: "";
					$ageLimit1  = (!empty($ageLimit[$i])) ? $ageLimit[$i] : "";
					$height1    = (!empty($height[$i])) ? $height[$i] :"";
					$days 		= $timmingArr[$i]['days'];
					$fromTiming = $timmingArr[$i]['from'];
					$toTimings  = $timmingArr[$i]['to'];	
					
					for($j=0;$j<count($days);$j++){
						$days_fin_str_1 = '';						
						$days_fin_str_1 = $this->get_format_days($days[0]);
						if($this->timings_array[$fromTiming[$j]]!='')							
							$from	= $this->timings_array[$fromTiming[$j]];
						
						if($this->timings_array[$toTimings[$j]]!='')						
							$to		= $this->timings_array[$toTimings[$j]];					
						
						
						$time1  = $from."-".$to;
						if($time1!='' && $from!='' && $to!=''){
							if($final_time)
								$final_time = $final_time."|".$time1;
							else
								$final_time = $time1;
						}						
						
					}	
					$ageGroupStr = '';
					if($ageGroup1!='' && strtolower($ageGroup1)=='kids'){											
						if(!is_array($ageLimit1) && $ageLimit1!='' && !is_array($height1) && $height1!=''){							
							$ageGroupStr = "Age Group-".$ageGroup1."$#$".$ageLimit1." yrs$#$".$height1."inches";
						}else if(!is_array($ageLimit1) && $ageLimit1!=''){
							$ageGroupStr = "Age Group-".$ageGroup1."$#$".$ageLimit1." yrs";
						}else if(!is_array($height1) && $height1!=''){
							$ageGroupStr = "Age Group-".$ageGroup1."$#$".$height1."inches";							
						}
					}else{
						$ageGroupStr = "Age Group-".$ageGroup1;
					}	
					$meal_type = $meal1." (".$mealType1.")";					
					$str_name1 = "Rs.".$price1."$#$".$meal_type."$#$".$final_time."$#$".$days_fin_str_1."$#$[".$title1."]$#$[".$ageGroupStr."]$#$";					
					if($str_name_final!=''){
						$str_name_final = $str_name_final."|~|".$str_name1;
					}else{
						$str_name_final = $str_name1;
					}					
				}
				$str_name_final  = rtrim($str_name_final,"|~|");				
				$attribute_dname = 'Buffet-'.$str_name_final;
				$attribute_value = $attribute_dname;
				//~ echo "<br>attribute_dname:--".$attribute_dname;
				//~ die;
				$insert1 .= "('".$this->docid."', '".$this->parentid."', '".$this->data_city."', '".addslashes(stripslashes($attribute_name))."', '".addslashes(stripslashes($attribute_dname))."', '".addslashes(stripslashes($attribute_value))."', '".$attr_type_fin."', '".$attribute_sub_group."', '".$sub_grp_name."', '".$display_flag."', '".$sub_group_pos."', '".$attribute_web_position."', '".$unique_code."', '".$attribute_prefix."' , '".$main_attribute_flag."' , '".$display_position."')" .",";	
				
				$insert1 = rtrim($insert1,",");	
				
				$fin_insert = "INSERT INTO tbl_companymaster_attributes_temp (docid,parentid, city, attribute_name,attribute_dname, attribute_value, attribute_type, attribute_sub_group,sub_group_name,display_flag,sub_group_position,attribute_position,attribute_id,attribute_prefix,main_attribute_flag,main_attribute_position) VALUES $insert1
					
				ON DUPLICATE KEY UPDATE
				docid  				= VALUES(docid),
				parentid  			= VALUES(parentid),
				city  				= VALUES(city),
				attribute_name  	= VALUES(attribute_name),
				attribute_dname  	= VALUES(attribute_dname),
				attribute_value  		= VALUES(attribute_value),
				attribute_type  		= VALUES(attribute_type),
				attribute_sub_group  = VALUES(attribute_sub_group),
				sub_group_name  	= VALUES(sub_group_name),
				display_flag  	= VALUES(display_flag),
				sub_group_position  	= VALUES(sub_group_position),
				attribute_position  	= VALUES(attribute_position),
				attribute_id	= VALUES(attribute_id),
				attribute_prefix  	    = VALUES(attribute_prefix),
				main_attribute_flag  	= VALUES(main_attribute_flag),
				main_attribute_position = VALUES(main_attribute_position) ";
											
				if($fin_insert!=''){					
					$resUpdate	= parent::execQuery($fin_insert, $this->conn_temp);
					if($resUpdate){
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg']  = "Success";			
						return $result_msg_arr;
					}else{
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg']  = "Update Query Failed!!";			
						return $result_msg_arr;
					}
				}				
			}else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg']  = "Data Node Not Passed!!";			
				return $result_msg_arr;
			}									
		}else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg']  = "Buffet Categories Not Found!!";			
			return $result_msg_arr;
		}
	}
	
	function getLogs(){		
		$tempData = array();
		$mainData = array();
		$result   = array();
				
		$QryTemp = "SELECT * FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'";
		$resTemp = parent::execQuery($QryTemp, $this->conn_temp);
		if($resTemp && parent::numRows($resTemp)>0){			
			while($rowTemp = parent::fetchData($resTemp)){
				$tempData[] = $rowTemp;
			}
		}		
		$QryMain = "SELECT * FROM tbl_companymaster_attributes WHERE parentid='".$this->parentid."'";
		$resMain = parent::execQuery($QryMain, $this->attr_main_conn);
		if($resMain && parent::numRows($resMain)>0){
			while($rowMain = parent::fetchData($resMain)){
				$mainData[] = $rowMain;
			}
		}
		$result['tempdata'] = $tempData;
		$result['maindata'] = $mainData;
		
		return $result;
	}
	
	function get_format_days($days_arr){
		$days_arr1 	 = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");	
		$daySelected = array();
		$cnt = 0;
		foreach($days_arr as $day=>$value){
			if($value==1){
				array_push($daySelected,$day);
				$cnt++;
			}
		}
		$str    = $daySelected[0];
		$finStr = '';
		if($cnt<7){
			foreach($days_arr1 as $key=>$val){
				if(!in_array($val, $daySelected)){
					$str = ",";		
				}else{
					if($str!=''){
						$str = $val."-";		
					}else{
						$str = $val;		
					}			
				}
				$finStr .= $str;	
			}
			$finStr = rtrim($finStr,"-");		
			
			$finStrArr = array_merge(array_filter(explode(",",$finStr)));
			$final_str = '';
			foreach($finStrArr as $key=>$value){	
				$valueArr = array();
				$valueArr = array_merge(array_filter(explode("-",$value)));		
				if(count($valueArr)==2){
					if($final_str!=''){
						$final_str = $final_str.",".$valueArr[0]."-".$valueArr[1];
					}else{
						$final_str = $valueArr[0]."-".$valueArr[1];
					}		
				}
				else if(count($valueArr)>2){
					if($final_str!=''){
						$final_str = $final_str.",".$valueArr[0]."-".$valueArr[count($valueArr)-1];
					}else{
						$final_str = $valueArr[0]."-".$valueArr[count($valueArr)-1];
					}
				}else{
					if($final_str!=''){
						$final_str = $final_str.",".$valueArr[0];
					}else{
						$final_str = $valueArr[0];
					}
				}
				
			}
			$final_str = rtrim($final_str,"-");
		}else{
			$final_str = "Mon-Sun";
		}
		
		return $final_str;
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
