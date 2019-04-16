<?php
//////*******************************************/////////////////////////////////////
/*
  http://vishalvinodrana.jdsoftware.com/jdbox/services/legal_cat_search.php?parentid=PXX22.XX22.170411095505.I8H3&companyname=vavdasv&ucode=10026632&module=CS&uname=Vishal%2BRana&data_city=Mumbai&catid=%5B%221000061132%22%5D&post_data=1
*/
//////*******************************************/////////////////////////////////////

class check_legal_cat extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_fnc    	= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		
		$parentid 		= trim($params['parentid']);
		$module 		= trim($params['module']);
		$data_city 		= urldecode(trim($params['data_city']));
		$catid 			= json_decode($params['catid'],true);
		$ucode 			= $params['ucode'];
		$check_other_flag 	= $params['check_other_flag'];
		$uname 			= urldecode(trim($params['uname']));
		$companyname 	= urldecode(trim($params['companyname']));

		
		$trace 			= $params['trace'];
		
		if($trace)
		{
			echo '<pre>';
			print_r($params);
			print_r($catid);
		}
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($ucode)=='')
		{
			$message = "Empcode is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		
		$this->parentid  				= $parentid;
		$this->companyname  			= $companyname;
		$this->data_city 				= $data_city;
		$this->ucode 					= $ucode;
		$this->uname 					= $uname;
		$this->module  	  				= strtoupper($module);
		$this->catid  	  				= implode(',',$catid);
		$this->catid_arr  	  			= $catid;
		$this->check_other_flag  		= $check_other_flag;
		$this->trace  					= $trace;
		
		
		if($this->catid == '')
		{
			$message = "Catid is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS') || ($this->module == 'TME'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}
		
		$this->temp_paid_cat_arr 	= array();
		$this->temp_nonpaid_cat_arr = array();
		$this->contract_existing_main_cat_arr = array();
		//$this->contract_existing_temp_cat_arr = array();
		//$this->contract_existing_temp_cat_arr = $this->getContractTempCatInfo();
		$this->contract_existing_main_cat_arr = $this->getContractMainCatInfo();
		
		$this->timingflg = 0;
		
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if(($this->module =='DE') || ($this->module =='CS'))
		{
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_catmaster 	= $this->conn_local;
			$this->conn_main	 	= $this->conn_iro;
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			$this->conn_main	 	= $this->conn_iro;
		}
		elseif($this->module =='ME')
		{
			$this->conn_temp		= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_idc;
			$this->conn_main	 	= $this->conn_idc;
		}
		else
		{
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_catmaster 	= $this->conn_local;
			$this->conn_main	 	= $this->conn_iro;
		}
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	/*function getContractTempCatInfo()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1 && ($this->module == 'CS' || $this->module == 'DE'))
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		
		$temp_category_arr = array();
		$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid,B.companyname,createdby FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
		$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);

		if($resTempCategory && parent::numRows($resTempCategory)>0)
		{
			$row_temp_category	=	parent::fetchData($resTempCategory);
			
			$this->companyname 	= $row_temp_category['companyname'];
			$this->createdby 	= $row_temp_category['createdby'];
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr = 	$this->getValidCategories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}
		return $temp_category_arr; 
	}
	*/
	
	function getContractMainCatInfo()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$main_category_arr = array();
		$sqlMainCategory	=	"SELECT companyname,catidlineage,catidlineage_nonpaid FROM ".$catlin_nonpaid_db."tbl_companymaster_extradetails WHERE parentid = '" . $this->parentid . "'";
		$resMainCategory 	= parent::execQuery($sqlMainCategory, $this->conn_main);
	
		if($resMainCategory && parent::numRows($resMainCategory)>0)
		{
			$row_main_category	=	parent::fetchData($resMainCategory);
			
			//$this->companyname  = $row_main_category['companyname'];
			
			if((isset($row_main_category['catidlineage']) && $row_main_category['catidlineage'] != '') || (isset($row_main_category['catidlineage_nonpaid']) && $row_main_category['catidlineage_nonpaid'] != ''))
			{
				$main_catlin_arr 	= 	array();
				$main_catlin_arr  	=   explode('/,/',trim($row_main_category['catidlineage'],'/'));
				
				$main_catlin_arr 	= 	array_filter($main_catlin_arr);
				$main_catlin_arr 	= 	$this->getValidCategories($main_catlin_arr);
				
				
				$main_catlin_np_arr = array();
				$main_catlin_np_arr = explode("/,/",trim($row_main_category['catidlineage_nonpaid'],"/"));
				$main_catlin_np_arr = array_filter($main_catlin_np_arr);
				$main_catlin_np_arr = 	$this->getValidCategories($main_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($main_catlin_arr,$main_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$main_category_arr = $this->getValidCategories($total_catlin_arr);
				

			}
		}
		
		return $main_category_arr; 
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
		}
		return $final_catids_arr;	
	}
	
	 function Check_legal_category($smsObj)
	{
		$catid_arr = array_filter($this->catid_arr);
		
		if(!isset($this->check_other_flag))
		{
			$diff_cat_id = array_diff($this->catid_arr,$this->contract_existing_main_cat_arr);
			$diff_catids= implode("','",$diff_cat_id);
			if(count($diff_cat_id) > 0)
			{
				//$sql_cat_check = "SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$diff_catids."') AND bfc_bifurcation_flag =1";
				//$res_cat_check 	= parent::execQuery($sql_cat_check, $this->conn_catmaster);
				$cat_params = array();
				$cat_params['page']= 'class_legal_cat';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid,category_name';
				$cat_params['skip_log']		= '1';

				$where_arr  	=	array();
				if(count($diff_cat_id)>0){
					$where_arr['catid']					= implode(",",$diff_cat_id);
					$where_arr['bfc_bifurcation_flag']	= '1';
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}		
			}
		}
		else
		{
			$diff_catids= implode("','",$this->catid_arr);
			if(count($this->catid_arr) > 0)
			{
				//$sql_cat_check =  "SELECT catid,category_name,bfc_bifurcation_flag,IF(category_type&64=64,1,0) AS block_for_contract,IF(mask_status=1,1,0) AS Mask_status, IF(biddable_type !=1,1,0) AS non_biddable_cat FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$diff_catids."')";
				//$res_cat_check 	= parent::execQuery($sql_cat_check, $this->conn_catmaster);

				$cat_params = array();
				$cat_params['page']= 'class_legal_cat';
				$cat_params['skip_log']		= '1';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid,category_name,bfc_bifurcation_flag,category_type,mask_status,biddable_type';

				$where_arr  	=	array();
				if(count($this->catid_arr)>0){
					$where_arr['catid']					= implode(",",$this->catid_arr);
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
			}
		}
		
		if(	$this->trace == 1)
			{
				echo "<prE>";
				echo "<br>SQL::";print_r($sql_cat_check);
				echo "<br>Category pass arr::";print_r($this->catid_arr);
				echo "<br>Main category arr::";print_r($this->contract_existing_main_cat_arr);
			}
		
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
		$i=0;
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_cat)
			{
				$category_arr[$i] = $row_cat['category_name'];
				
				if(isset($this->check_other_flag))
				{
					$category_type	=	$row_cat['category_type'];
					$mask_status 	=	$row_cat['mask_status'];
					$biddable_type 	=	$row_cat['biddable_type'];

					$block_for_contract = 0;					
					if(((int)$category_type & 64) == 64){
						$block_for_contract = 1;
					}
					$Mask_status = 0;
					if($mask_status==1){
						$Mask_status = 1;
					}
					$non_biddable_cat = 0;
					if($biddable_type !=1){
						$non_biddable_cat = 1;
					}

					if($block_for_contract == 1 || $Mask_status == 1 || $row_cat['bfc_bifurcation_flag'] == 1 )	
						$message['restricted_category'][$row_cat['catid']] =$category_arr[$i];	
						
					if($block_for_contract == 1)
						$message['block_for_contract'][$row_cat['catid']] =$category_arr[$i];
					if($Mask_status == 1)
						$message['Mask_status'][$row_cat['catid']] =$category_arr[$i];
					if($non_biddable_cat == 1)
						$message['non_biddable_cat'][$row_cat['catid']] =$category_arr[$i];
					if($row_cat['bfc_bifurcation_flag'] == 1)
						$message['bfc_bifurcation_flag'][$row_cat['catid']] =$category_arr[$i];							
				}
				$i++;
			}
			$category_str  = implode(',',$category_arr);
			
			$email_id = 'categorycreation@justdial.com,dbescalations@justdial.com';
			$sender_email = "noreply@justdial.com";
			$email_subject = "Legal Category";
			$email_text  .= 'Legal Category Found<br>';  
			$email_text  .='Company Name - '.$this->companyname.'<br>'; 
			$email_text  .='Contract id - '.$this->parentid.'<br>'; 
			$email_text .='City - '.$this->data_city.'<br>';
			$email_text .='Categories - '.$category_str.'<br>';
			$email_text .='source - '.$this->module.'<br>';
			$email_text .='User - '.$this->uname;
			$source = 'CS';
			if(!isset($this->check_other_flag))
			{
				//$smsObj->sendEmail($email_id, $sender_email, $email_subject, $email_text, $source);
				$insert_query = "insert into tbl_legal_cat_parentids SET 
				parentid			=	'".$this->parentid."',
				compannyname		=	'".addslashes($this->companyname)."',
				city				= 	'".addslashes($this->data_city)."',
				category			= 	'".addslashes($category_str)."',
				source 				= 	'".addslashes($this->module)."',
				user				= 	'".addslashes($this->uname)."',
				update_date			= 	NOW()";
				$res_query 	= parent::execQuery($insert_query, $this->conn_local);
					
			}
			$message['msg'] = "Legal Category found";
			if($this->trace == 1)
			{
				echo "<prE>";
				print_r($message);
			}
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		else
		{
			$message = "No Legal Category found";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
	
	}
	
	
}
?>	
