<?php
class catpreview_submit_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;

	function __construct($params)
	{		
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$ucode 				= trim($params['ucode']);
		$data_city 			= trim($params['data_city']);
		$remove_catidlist 	= trim($params['remove_catidlist']);
		$nonpaid_catidlist 	= trim($params['nonpaid_catidlist']);
		
		$this->remove_cats = $remove_catidlist;
		$this->remove_cats = explode(",",$remove_catidlist);
		$this->removeCats  = implode("|P|" ,$this->remove_cats);
		$this->remove_cats = implode("','" ,$this->remove_cats);		
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
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj =	new categoryClass();
		$this->setServers();
		
		$this->add_catlin_nonpaid_db = 0;
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$this->add_catlin_nonpaid_db = 1;
		}
		$this->temp_paid_cat_arr 	= array();
		$this->temp_nonpaid_cat_arr = array();
		$this->fetchContractTempCategories();
		
		$this->remove_catidlist_arr = array();
		if($remove_catidlist)
		{
			$this->remove_catidlist_arr = explode(',',$remove_catidlist);
			$this->remove_catidlist_arr = array_map('trim',$this->remove_catidlist_arr);
			$this->remove_catidlist_arr = array_unique(array_filter($this->remove_catidlist_arr));
		}
		
		$this->nonpaid_catidlist_arr = array();
		if($nonpaid_catidlist)
		{
			$this->nonpaid_catidlist_arr = explode(',',$nonpaid_catidlist);
			$this->nonpaid_catidlist_arr = array_map('trim',$this->nonpaid_catidlist_arr);
			$this->nonpaid_catidlist_arr = array_diff($this->nonpaid_catidlist_arr,$this->remove_catidlist_arr);
			$this->nonpaid_catidlist_arr = array_unique(array_filter($this->nonpaid_catidlist_arr));
		}
		
		/*------------------- Authorised Category Hnadling ---------------*/
		$this->paid_auth_cat_arr = array();
		$this->nonpaid_auth_cat_arr = array();
		
		if(is_array($params['paid_auth']) && count($params['paid_auth'])>0)
		{
			foreach($params['paid_auth'] as $paid_auth_catinfostr)
			{
				if(stristr($paid_auth_catinfostr,"|~~|"))
				{
					$paid_auth_catinfoarr = explode("|~~|",$paid_auth_catinfostr);
					$paid_auth_catnameval = $paid_auth_catinfoarr[0];
					$paid_auth_catidval   = $paid_auth_catinfoarr[1];
					if((!empty($paid_auth_catnameval)) && (intval($paid_auth_catidval)>0))
					{
						if(in_array($paid_auth_catidval,$this->nonpaid_catidlist_arr))
						{
							$this->nonpaid_auth_cat_arr[$paid_auth_catidval] = $paid_auth_catnameval; 
							if(in_array($paid_auth_catidval,$this->temp_paid_cat_arr))
							{
								$key = array_search($paid_auth_catidval,$this->temp_paid_cat_arr);
								unset($this->temp_paid_cat_arr[$key]);
							}
						}
						else
						{
							$this->paid_auth_cat_arr[$paid_auth_catidval] = $paid_auth_catnameval; 
						}
					}
				}
			}
		}
		$this->paid_nonauth_cat_arr = array();
		$this->nonpaid_nonauth_cat_arr = array();
		if(is_array($params['paid_nonauth']) && count($params['paid_nonauth'])>0)
		{
			foreach($params['paid_nonauth'] as $paid_nonauth_catinfostr)
			{
				if(stristr($paid_nonauth_catinfostr,"|~~|"))
				{
					$paid_nonauth_catinfoarr = explode("|~~|",$paid_nonauth_catinfostr);
					$paid_nonauth_catnameval = $paid_nonauth_catinfoarr[0];
					$paid_nonauth_catidval   = $paid_nonauth_catinfoarr[1];
					if((!empty($paid_nonauth_catnameval)) && (intval($paid_nonauth_catidval)>0))
					{
						if(in_array($paid_nonauth_catidval,$this->nonpaid_catidlist_arr))
						{
							$this->nonpaid_nonauth_cat_arr[$paid_nonauth_catidval] = $paid_nonauth_catnameval;
							if(in_array($paid_nonauth_catidval,$this->temp_paid_cat_arr))
							{
								$key = array_search($paid_nonauth_catidval,$this->temp_paid_cat_arr);
								unset($this->temp_paid_cat_arr[$key]);
							}
						}
						else
						{
							$this->paid_nonauth_cat_arr[$paid_nonauth_catidval] = $paid_nonauth_catnameval;
						}
					}
				}
			}
		}
		
		if(is_array($params['nonpaid_auth']) && count($params['nonpaid_auth'])>0)
		{
			foreach($params['nonpaid_auth'] as $nonpaid_auth_catinfostr)
			{
				if(stristr($nonpaid_auth_catinfostr,"|~~|"))
				{
					$nonpaid_auth_catinfoarr = explode("|~~|",$nonpaid_auth_catinfostr);
					$nonpaid_auth_catnameval = $nonpaid_auth_catinfoarr[0];
					$nonpaid_auth_catidval   = $nonpaid_auth_catinfoarr[1];
					if((!empty($nonpaid_auth_catnameval)) && (intval($nonpaid_auth_catidval)>0))
					{
						if(!in_array($nonpaid_auth_catidval,$this->nonpaid_auth_cat_arr))
						{
							$this->nonpaid_auth_cat_arr[$nonpaid_auth_catidval] = $nonpaid_auth_catnameval; 
						}
					}
				}
			}
		}
		
		if(is_array($params['nonpaid_nonauth']) && count($params['nonpaid_nonauth'])>0)
		{
			foreach($params['nonpaid_nonauth'] as $nonpaid_nonauth_catinfostr)
			{
				if(stristr($nonpaid_nonauth_catinfostr,"|~~|"))
				{
					$nonpaid_nonauth_catinfoarr = explode("|~~|",$nonpaid_nonauth_catinfostr);
					$nonpaid_nonauth_catnameval = $nonpaid_nonauth_catinfoarr[0];
					$nonpaid_nonauth_catidval   = $nonpaid_nonauth_catinfoarr[1];
					if((!empty($nonpaid_nonauth_catnameval)) && (intval($nonpaid_nonauth_catidval)>0))
					{
						if(!in_array($nonpaid_nonauth_catidval,$this->nonpaid_nonauth_cat_arr))
						{
							$this->nonpaid_nonauth_cat_arr[$nonpaid_nonauth_catidval] = $nonpaid_nonauth_catnameval; 
						}
					}
				}
			}
		}
	}
	function setServers()
	{	
		GLOBAL $db;

		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		if(($this->module =='DE') || ($this->module =='CS'))
		{
			$this->conn_temp	 	= $this->conn_local;
			$this->conn_catmaster 	= $this->conn_local;
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif(($this->module =='ME') || ($this->module =='JDA'))
		{
			$this->conn_temp		= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
		}
		else
		{
			$message = "Invalid Module.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
	}
	
	function sendLogs($parentid,$data,$response){
		
		/**/
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $parentid;
		$post_data['PUBLISH']           = 'ME';
		$post_data['ROUTE']             = 'MONGOUPDATE';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= 'mongo update on shadow tables';
		$post_data['DATA']['url']       = 'catpreview_submit_class.php';
		$post_data['DATA_JSON']['paramssubmited'] = $data;
		$post_data['DATA_JSON']['response'] = $response;
		$post_data = http_build_query($post_data);
		/**/
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $log_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	
	function saveCategoryTempData()
	{
		$final_temp_paid_cat_arr = array();
		$this->deleteFromBrowsingTable();
		$this->setAuthorizationFlag();
		
		if(count($this->paid_auth_cat_arr) || count($this->paid_nonauth_cat_arr))
		{
			$this->paidAuthorisedCatOperations();
		}
		if(count($this->temp_paid_cat_arr) >0)
		{
			if($this->module =='CS'){
				$sql_tag_catid	=	"SELECT tag_catid FROM tbl_companymaster_extradetails_shadow WHERE parentid='".$this->parentid."'";
				$res_tag_catid  = parent::execQuery($sql_tag_catid,$this->conn_iro);
				if(parent::numRows($res_tag_catid)>0){
					$row_tag_catid =	parent::fetchData($res_tag_catid);
					$tag_catid =	trim($row_tag_catid['tag_catid']);
					if($tag_catid!='' && is_array($this->remove_catidlist_arr)){
						if(in_array($tag_catid,$this->remove_catidlist_arr)){
							$sql_upd_shadow =	"UPDATE tbl_companymaster_extradetails_shadow SET tag_catid='',tag_catname='' WHERE parentid='".$this->parentid."' ";
							$res_upd_shadow = parent::execQuery($sql_upd_shadow,$this->conn_iro);
						}
					}
				}
			}

			/*----- Paid Authorised Category Addition----*/
			if(count($this->paid_auth_add_catids_arr)>0)
			{
				$this->temp_paid_cat_arr = array_merge($this->temp_paid_cat_arr,$this->paid_auth_add_catids_arr);
				$this->temp_paid_cat_arr = $this->getValidCategories($this->temp_paid_cat_arr);
			}
			/*----- Paid Authorised Category Removal----*/
			if(count($this->paid_auth_remove_catids_arr)>0)
			{
				$this->remove_catidlist_arr = array_merge($this->remove_catidlist_arr,$this->paid_auth_remove_catids_arr);
				$this->remove_catidlist_arr = $this->getValidCategories($this->remove_catidlist_arr);
			}
			
			if(count($this->remove_catidlist_arr)>0)	
			{
				$matched_paid_cat_arr = array();
				$matched_paid_cat_arr = array_intersect($this->temp_paid_cat_arr,$this->remove_catidlist_arr);
				if(count($matched_paid_cat_arr)>0)
				{
					$final_temp_paid_cat_arr = array_diff($this->temp_paid_cat_arr,$matched_paid_cat_arr);
				}
				else
				{
					$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
				}
			}
			else
			{
				$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
			}
			if(count($this->nonpaid_catidlist_arr)>0)
			{
				$final_temp_paid_cat_arr = array_diff($final_temp_paid_cat_arr,$this->nonpaid_catidlist_arr);
				$final_temp_paid_cat_arr = $this->getValidCategories($final_temp_paid_cat_arr);
			}
		}
		if(count($final_temp_paid_cat_arr)>0)
		{
			$all_temp_paid_cat_arr = $this->getCategoryDetails($final_temp_paid_cat_arr);
			
			
			$catids_arr = array();
			$catnames_arr = array();
			$national_catids_arr = array();
			$i = 1;
			if(count($all_temp_paid_cat_arr)>0)
			{
				foreach($all_temp_paid_cat_arr as $catid => $catinfo_arr)
				{
					$catname 		= trim($catinfo_arr['catname']);
					$national_catid = trim($catinfo_arr['national_catid']);
					
					$catids_arr[] 			= $catid;
					$catnames_arr[] 		= $catname;
					$national_catids_arr[] 	= $national_catid;
					$final_national_catids_arr[] = $national_catid;
				}
				
			}
			$catnames_str = "|P|";
			$catnames_str .= implode("|P|",$catnames_arr);
			
			$catids_str = "|P|";
			$catids_str .= implode("|P|",$catids_arr);
			
			$national_catids_str = "|P|";
			$national_catids_str .= implode("|P|",$national_catids_arr);
			
			$catname_sel = str_ireplace("|P|","|~|",$catnames_str);
			 
			 
			$categories 	= $this->slasher($catnames_str);
			$catSelected 	= $this->slasher($catname_sel);
			 
			
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$bustemp_tbl 		= "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['categories'] 				= $categories;
				$bustemp_upt['catIds'] 					= $catids_str;
				$bustemp_upt['nationalcatIds'] 			= $national_catids_str;
				$bustemp_upt['catSelected'] 			= $catSelected;
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdtBusinessTempData = $this->mongo_obj->updateData($mongo_inputs);

				/**/ 
				if(strtolower(trim($this->module)) == 'me' || strtolower(trim($this->module)) == 'jda')
				{
					$this->sendLogs($this->parentid,$mongo_data,$resUpdtBusinessTempData);
				}
				/**/
			}
			else
			{				
				$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET 
										categories 		= '".$categories."',
										catIds 			= '".$catids_str."',
										nationalcatIds 	= '".$national_catids_str."',
										catSelected 	= '".$catSelected."',
										categories_list	= ''
										WHERE contractid='".$this->parentid."'";
				$sqlUpdtBusinessTempData = $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";
				$resUpdtBusinessTempData   = parent::execQuery($sqlUpdtBusinessTempData, $this->conn_temp);
			}
			
			//~ $delEntry = "DELETE FROM online_regis1.tbl_removed_categories WHERE parentid='".$this->parentid."'";
			//~ $resEntry = parent::execQuery($delEntry, $this->conn_idc);
			if($this->removeCats!='' && $this->removeCats!=null){
				$makeEntry = "INSERT INTO online_regis1.tbl_removed_categories
												SET
												parentid = '".$this->parentid."',
												catIds = '".$catids_str."', 
												removed_categories = '".$this->removeCats."',
												ucode = '".$this->ucode."', 
												updatedOn= '".date('Y-m-d H:i:s')."',
												module = '".$this->module."'
												ON DUPLICATE KEY UPDATE
												catIds = '".$catids_str."', 
												removed_categories = '".$this->removeCats."',
												ucode = '".$this->ucode."', 
												updatedOn= '".date('Y-m-d H:i:s')."',
												module = '".$this->module."' ";
				$resMEntry = parent::execQuery($makeEntry, $this->conn_idc);				
			}
		}
		else
		{
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				
				$bustemp_tbl 		= "tbl_business_temp_data";
				$bustemp_upt = array();
				$bustemp_upt['categories'] 				= '';
				$bustemp_upt['catIds'] 					= '';
				$bustemp_upt['nationalcatIds'] 			= '';
				$bustemp_upt['catSelected'] 			= '';
				$bustemp_upt['categories_list'] 		= '';
				$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdtBusinessTempData = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET categories ='', catIds='', nationalcatIds='', catSelected='', categories_list= '' WHERE contractid = '".$this->parentid."'";
				$sqlUpdtBusinessTempData = $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";
				$resUpdtBusinessTempData = parent::execQuery($sqlUpdtBusinessTempData, $this->conn_temp);
			}
		}
		if(count($this->nonpaid_auth_cat_arr) || count($this->nonpaid_nonauth_cat_arr))
		{
			$this->nonpaidAuthorisedCatOperations();
		}
		$final_temp_nonpaid_cat_arr = array();
		
		/*----- nonpaid Authorised Category Addition----*/
		if(count($this->nonpaid_auth_add_catids_arr)>0)
		{
			$this->temp_nonpaid_cat_arr = array_merge($this->temp_nonpaid_cat_arr,$this->nonpaid_auth_add_catids_arr);
			$this->temp_nonpaid_cat_arr = $this->getValidCategories($this->temp_nonpaid_cat_arr);
		}
		/*----- nonpaid Authorised Category Removal----*/
		if(count($this->nonpaid_auth_remove_catids_arr)>0)
		{
			$this->remove_catidlist_arr = array_merge($this->remove_catidlist_arr,$this->nonpaid_auth_remove_catids_arr);
			$this->remove_catidlist_arr = $this->getValidCategories($this->remove_catidlist_arr);
		}
		
		if(count($this->remove_catidlist_arr)>0)	
		{
			$matched_nonpaid_cat_arr = array();
			$matched_nonpaid_cat_arr = array_intersect($this->temp_nonpaid_cat_arr,$this->remove_catidlist_arr);
			if(count($matched_nonpaid_cat_arr)>0)
			{
				$final_temp_nonpaid_cat_arr = array_diff($this->temp_nonpaid_cat_arr,$matched_nonpaid_cat_arr);
			}
			else
			{
				$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
			}
		}
		else
		{
			$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
		}
		
		if(count($this->nonpaid_catidlist_arr)>0)
		{
			$final_temp_nonpaid_cat_arr = array_merge($final_temp_nonpaid_cat_arr,$this->nonpaid_catidlist_arr);
			$final_temp_nonpaid_cat_arr = $this->getValidCategories($final_temp_nonpaid_cat_arr);
		}
		$temp_nonpaid_without_paid_catarr = array(); // Cross Check Nonpaid Category Should Not Exist In Paid Category
		
		if(count($final_temp_nonpaid_cat_arr)>0)
		{
			foreach($final_temp_nonpaid_cat_arr as $temp_nonpaid_catid)
			{
				if(!in_array($temp_nonpaid_catid,$final_temp_paid_cat_arr))
				{
					$temp_nonpaid_without_paid_catarr[] = $temp_nonpaid_catid;
				}
			}
		}
		if(count($temp_nonpaid_without_paid_catarr)>0)
		{
			$all_temp_nonpaid_cat_arr = $this->getCategoryDetails($temp_nonpaid_without_paid_catarr);
			
			$catids_nonpaid_arr = array();
			$national_catids_nonpaid_arr = array();
			$i = 1;
			if(count($all_temp_nonpaid_cat_arr)>0)
			{
				foreach($all_temp_nonpaid_cat_arr as $catid => $catinfo_arr)
				{
					$catname 		= trim($catinfo_arr['catname']);
					$national_catid = trim($catinfo_arr['national_catid']);
					
					$catids_nonpaid_arr[] 			= $catid;
					$national_catids_nonpaid_arr[] 	= $national_catid;
					$final_national_catids_arr[]    = $national_catid;
				}
			}
			
			$catidlineage_nonpaid 			= "/".implode('/,/',$catids_nonpaid_arr)."/";
			$national_catidlineage_nonpaid 	= "/".implode('/,/',$national_catids_nonpaid_arr)."/";
			
			$catlin_nonpaid_db = '';
			if($this->add_catlin_nonpaid_db == 1)
			{
				$catlin_nonpaid_db = 'db_iro.';
			}
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['catidlineage_nonpaid'] 			= $catidlineage_nonpaid;
				$extrdet_upt['national_catidlineage_nonpaid'] 	= $national_catidlineage_nonpaid;
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$sqlUpdateExtraShadow = "UPDATE ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '".$catidlineage_nonpaid."', national_catidlineage_nonpaid = '".$national_catidlineage_nonpaid."' WHERE parentid = '".$this->parentid."'";
				$sqlUpdateExtraShadow = $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
				$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_temp);
			}
			
			if(($this->module =='DE') || ($this->module =='CS'))
			{
			
				$sqlMovie = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' and catid in ('".$this->remove_cats."')";
				$resMovie   = parent::execQuery($sqlMovie, $this->conn_temp);
			}
		}
		else
		{
			$catlin_nonpaid_db = '';
			if($this->add_catlin_nonpaid_db == 1)
			{
				$catlin_nonpaid_db = 'db_iro.';
			}
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['catidlineage_nonpaid'] 			= '';
				$extrdet_upt['national_catidlineage_nonpaid'] 	= '';
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{				
				$sqlUpdateExtraShadow = "UPDATE ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '', national_catidlineage_nonpaid = '' WHERE parentid = '".$this->parentid."'";
				$sqlUpdateExtraShadow = $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
				$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_temp);
			}
			if(($this->module =='DE') || ($this->module =='CS'))
			{
				$sqlMovie = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' and catid in ('".$this->remove_cats."')";
				$resMovie   = parent::execQuery($sqlMovie, $this->conn_temp);
			}
		}
		
		$final_national_catids_arr = array_unique($final_national_catids_arr);
		$catergory_count = count($final_national_catids_arr);
		
		if($catergory_count > 0)
		{
			$final_national_catids_str = implode("|P|",$final_national_catids_arr);
			$final_national_catids_str = "|P|".$final_national_catids_str."|P|";
		}
		
		
		$final_national_catids_str = trim($final_national_catids_str,"|P|");
		$final_national_catids_str = implode(",",(explode("|P|",$final_national_catids_str)));
		//print_r($final_national_catids_array);
		
		
		$alloweddotcom = 0;
		if($this->module =='CS')
		{
			$sql_intermediate = "SELECT dotcom FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."' and dotcom>0";
			$res_intermediate   = parent::execQuery($sql_intermediate, $this->conn_local);
			if($res_intermediate && mysql_num_rows($res_intermediate)>0)
			{
				$alloweddotcom = 1;
			}
		}
		
		if(!$alloweddotcom)
		{
			//$excl = "SELECT national_catid,category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . trim($final_national_catids_str) . ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
        
			//$excl_national 	= parent::execQuery($excl,$this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] 		= 'catpreview_submit_class';
			$cat_params['data_city'] 	= $this->data_city;		
			$cat_params['return']		= 'catid,national_catid,category_scope';	

			$where_arr  	=	array();			
			$where_arr['national_catid']	= $final_national_catids_str;
			$where_arr['isdeleted']			= '0';
			$where_arr['mask_status']		= '0';					
			$cat_params['where']		= json_encode($where_arr);
			
			$cat_res_arr = array();
			if($final_national_catids_str!=''){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);			
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			}
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				$i = 0;
				$final_national_catids_str = '';
				foreach($cat_res_arr['results'] as $key =>$row_excl)
				{
					$category_scope	=	trim($row_excl['category_scope']);
					if($category_scope ==1 || $category_scope ==2){ 
						$final_national_catids_str .= "|P|".$row_excl['national_catid'];
						$i++;
					}
				}
				
				$final_national_catids_str = $final_national_catids_str."|P|";
				$catergory_count = $i;
			}
			else
			{
				$final_national_catids_str = '';
				$catergory_count = 0;
			}
		}
		else
		{
			$final_national_catids_str = implode("|P|",(explode(",",$final_national_catids_str)));
			$final_national_catids_str = "|P|".$final_national_catids_str;
		}
		
		$sql_national_main = "Update tbl_national_listing_temp set Category_nationalid='".$final_national_catids_str."',TotalCategoryWeight=".$catergory_count." where parentid='".$this->parentid."'";
		$resnational_main   = parent::execQuery($sql_national_main, $this->conn_temp);
	
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
	}
	function setAuthorizationFlag()
	{
		$sqlSetAuthorisationFlag = "INSERT INTO tbl_temp_flow_status SET
									parentid ='".$this->parentid."',
									authorisation_flag='1'
									
									ON DUPLICATE KEY UPDATE
									authorisation_flag='1'";
		$resSetAuthorisationFlag = parent::execQuery($sqlSetAuthorisationFlag, $this->conn_temp);
	}
	function deleteFromBrowsingTable()
	{	
		$sqlDelBrowsingTable = "DELETE FROM tbl_business_temp_category WHERE contractid = '".$this->parentid."'";
		$resDelBrowsingTable =  parent::execQuery($sqlDelBrowsingTable, $this->conn_temp);
	}
	function paidAuthorisedCatOperations()
	{
		$paid_auth_add_catids_arr 	= array();
		$paid_auth_remove_catids_arr = array();
		
		$paid_auth_nonauth_cat_arr = $this->paid_auth_cat_arr + $this->paid_nonauth_cat_arr;
		
		$paid_auth_small = array();																					//echo "Rohit<br>";	print_r($arr);
		if(count($this->paid_auth_cat_arr))
		{
			foreach($this->paid_auth_cat_arr as $key=>$value)
			{
				$paid_auth_small[$key] = $this->getAuthGenCatid($key);
			}
		}
		$paid_nonauth_small = array();
		if(count($this->paid_nonauth_cat_arr))
		{
			foreach($this->paid_nonauth_cat_arr as $key=>$value)
			{
				$paid_nonauth_small[$key] = $this->getAuthGenCatid($key);
			}
		}
		$this->slasher($paid_auth_nonauth_cat_arr);
		
		$paid_auth_nonauth_natcatids_str = '';
		if(count($paid_auth_nonauth_cat_arr)>0){
			$paid_auth_nonauth_catids_arr 		= array_keys($paid_auth_nonauth_cat_arr);
			$paid_auth_nonauth_natcatids_arr 	= $this->getCategoryDetails($paid_auth_nonauth_catids_arr,1);
			$paid_auth_nonauth_natcatids_str = implode("','",$paid_auth_nonauth_natcatids_arr); // where auth_gen_ncatid in national_catid of array_keys($paid_auth_nonauth_cat_arr)
		}
		if($paid_auth_nonauth_natcatids_str !='')
		{
			//$sqlPaidAuthorisedCategory = "SELECT catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid IN ('".$paid_auth_nonauth_natcatids_str."') AND biddable_type=1 AND mask_status=0 ";
			//$resPaidAuthorisedCategory 	= parent::execQuery($sqlPaidAuthorisedCategory, $this->conn_catmaster);

			$cat_params = array();
			$cat_params['page'] 		= 'catpreview_submit_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,auth_gen_ncatid';

			$where_arr  	=	array();
			$where_arr['auth_gen_ncatid']	= implode(",",$paid_auth_nonauth_natcatids_arr);
			$where_arr['biddable_type']		= "1";
			$where_arr['mask_status']		= "0";
			$cat_params['where']	= json_encode($where_arr);
	
			$cat_res_arr = array();
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);			
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0')
			{
				foreach($cat_res_arr['results'] as $key =>$row_paid_authcat)
				{			
					if(!$this->paid_auth_cat_arr[$row_paid_authcat['catid']] && !$this->paid_nonauth_cat_arr[$row_paid_authcat['catid']]) //need General Version of this catid
					{			
						$auth_gen_ncatid	= intval($row_paid_authcat['auth_gen_ncatid']);
						$paid_auth_search 	= array_search($auth_gen_ncatid,$paid_auth_small);

						if($paid_auth_search) //if auth_gen_ncatid is present in authorised array
						{
							$paid_auth_add_catids_arr[] = intval($row_paid_authcat['catid']);
						}
						else
						{	
							$paid_nonauth_search = array_search($auth_gen_ncatid,$paid_nonauth_small);
							if($paid_nonauth_search) //if auth_gen_ncatid is present in non authorised array
							{
								$paid_auth_add_catids_arr[]		= intval($row_paid_authcat['catid']);
								$paid_auth_remove_catids_arr[] 	= $paid_nonauth_search;
							}
						}
					}
				}
				if(count($paid_auth_add_catids_arr)>0){
					$paid_auth_add_catids_arr = array_unique(array_filter($paid_auth_add_catids_arr));
				}
				if(count($paid_auth_remove_catids_arr)>0){
					$paid_auth_remove_catids_arr = array_unique(array_filter($paid_auth_remove_catids_arr));
				}
			}
		}
		$this->paid_auth_add_catids_arr 	= $paid_auth_add_catids_arr;
		$this->paid_auth_remove_catids_arr 	= $paid_auth_remove_catids_arr;
	}
	
	function nonpaidAuthorisedCatOperations()
	{
		$nonpaid_auth_add_catids_arr 	= array();
		$nonpaid_auth_remove_catids_arr = array();
		
		$nonpaid_auth_nonauth_cat_arr = $this->nonpaid_auth_cat_arr + $this->nonpaid_nonauth_cat_arr;
		
		$nonpaid_auth_small = array();																					//echo "Rohit<br>";	print_r($arr);
		if(count($this->nonpaid_auth_cat_arr))
		{
			foreach($this->nonpaid_auth_cat_arr as $key=>$value)
			{
				$nonpaid_auth_small[$key] = $this->getAuthGenCatid($key);
			}
		}
		$nonpaid_nonauth_small = array();
		if(count($this->nonpaid_nonauth_cat_arr))
		{
			foreach($this->nonpaid_nonauth_cat_arr as $key=>$value)
			{
				$nonpaid_nonauth_small[$key] = $this->getAuthGenCatid($key);
			}
		}
		$this->slasher($nonpaid_auth_nonauth_cat_arr);
		
		$nonpaid_auth_nonauth_natcatids_str = '';
		if(count($nonpaid_auth_nonauth_cat_arr)>0){
			$nonpaid_auth_nonauth_catids_arr 		= array_keys($nonpaid_auth_nonauth_cat_arr);
			$nonpaid_auth_nonauth_natcatids_arr 	= $this->getCategoryDetails($nonpaid_auth_nonauth_catids_arr,1);
			$nonpaid_auth_nonauth_natcatids_str = implode("','",$nonpaid_auth_nonauth_natcatids_arr); // where auth_gen_ncatid in national_catid of array_keys($nonpaid_auth_nonauth_cat_arr)
			
		}
		if($nonpaid_auth_nonauth_natcatids_str !='')
		{
			//$sqlnonpaidAuthorisedCategory = "SELECT catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid IN ('".$nonpaid_auth_nonauth_natcatids_str."') AND biddable_type=1 AND mask_status= 0 ";
			//$resnonpaidAuthorisedCategory 	= parent::execQuery($sqlnonpaidAuthorisedCategory, $this->conn_catmaster);

			$cat_params = array();
			$cat_params['page'] 		= 'catpreview_submit_class';
			$cat_params['data_city'] 	= $this->data_city;		
			$cat_params['return']		= 'catid,auth_gen_ncatid';	

			$where_arr  	=	array();			
			$where_arr['auth_gen_ncatid']	= implode(",",$nonpaid_auth_nonauth_natcatids_arr);
			$where_arr['biddable_type']		= '1';
			$where_arr['mask_status']		= '0';
			$cat_params['where']			= json_encode($where_arr);
			
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_nonpaid_authcat)
				{			
					if(!$this->nonpaid_auth_cat_arr[$row_nonpaid_authcat['catid']] && !$this->nonpaid_nonauth_cat_arr[$row_nonpaid_authcat['catid']]) //need General Version of this catid
					{			
						$auth_gen_ncatid		= intval($row_nonpaid_authcat['auth_gen_ncatid']);	
						$nonpaid_auth_search 		= array_search($auth_gen_ncatid,$nonpaid_auth_small);

						if($nonpaid_auth_search) //if auth_gen_ncatid is present in authorised array
						{
							$nonpaid_auth_add_catids_arr[] 	= $row_nonpaid_authcat['catid'];
						}
						else
						{	
							$nonpaid_nonauth_search = array_search($auth_gen_ncatid,$nonpaid_nonauth_small);
							if($nonpaid_nonauth_search) //if auth_gen_ncatid is present in non authorised array
							{
								$nonpaid_auth_add_catids_arr[]		= $row_nonpaid_authcat['catid'];
								$nonpaid_auth_remove_catids_arr[] 	= $nonpaid_nonauth_search;
							}
						}
					}
				}
				if(count($nonpaid_auth_add_catids_arr)>0){
					$nonpaid_auth_add_catids_arr = array_unique(array_filter($nonpaid_auth_add_catids_arr));
				}
				if(count($nonpaid_auth_remove_catids_arr)>0){
					$nonpaid_auth_remove_catids_arr = array_unique(array_filter($nonpaid_auth_remove_catids_arr));
				}
			}
		}
		$this->nonpaid_auth_add_catids_arr 	= $nonpaid_auth_add_catids_arr;
		$this->nonpaid_auth_remove_catids_arr 	= $nonpaid_auth_remove_catids_arr;
	}
	
	function getCategoryDetails($catids_arr,$auth_nat_flag = 0)
	{
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		
		$cat_params = array();
		$cat_params['page'] 		= 'catpreview_submit_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,national_catid,category_name,auth_gen_ncatid';

		$where_arr  			= array();
		$where_arr['catid']		= implode(",",$catids_arr);
		$cat_params['where']	= json_encode($where_arr);	

		$cat_res_arr = array();
		if(count($catids_arr)>0){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);		
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if(count($cat_res_arr['results'])>0 && $cat_res_arr['errorcode']=='0')
		{
			foreach($cat_res_arr['results'] as $key=>$cat_arr)
			{
				$catid 			= trim($cat_arr['catid']);
				$category_name	= trim($cat_arr['category_name']);
				$national_catid	= trim($cat_arr['national_catid']);
				$auth_gen_ncatid= trim($cat_arr['auth_gen_ncatid']);
				if($auth_nat_flag == 1){
					$CatinfoArr[] = $auth_gen_ncatid;
				}else{
					$CatinfoArr[$catid]['catname'] = $category_name;
					$CatinfoArr[$catid]['national_catid'] = $national_catid;
				}
			}
		}
		return $CatinfoArr;
	}
	function getAuthGenCatid($catid)
	{
		$auth_gen_ncatid = 0;

		//$sqlAuthGenCatid = "SELECT auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE catid = '".$catid."'";
		//$resAuthGenCatid 	= parent::execQuery($sqlAuthGenCatid, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] 		= 'catpreview_submit_class';
		$cat_params['data_city'] 	= $this->data_city;		
		$cat_params['return']		= 'auth_gen_ncatid';	

		$where_arr  	=	array();
		$where_arr['catid']		= str_replace("','", ",",$catid);
		$cat_params['where']	= json_encode($where_arr);

		$cat_res_arr = array();
		if($catid!=''){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);		
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
			$row_authgen_catid  = $cat_res_arr['results'];
			$auth_gen_ncatid	= intval($row_authgen_catid['auth_gen_ncatid']);
		}
		return $auth_gen_ncatid;
	}
	function fetchContractTempCategories()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		$temp_category_arr = array();
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
		}else{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_temp);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0 ){
				$row_temp_category = parent::fetchData($resTempCategory);
			}
		}
		
		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
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
	function slasher($arr)
	{
		if(is_array($arr))
		{
			foreach($arr as $key=>$value)
			{
				$arr[$key] = addslashes(stripslashes($value));
			}
		}
		else
		{
			$arr = addslashes(stripslashes($arr));
		}
		return $arr;		
	}
	private function sendDieMessage($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
