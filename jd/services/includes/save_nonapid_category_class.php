<?php
class save_nonapid_category_class extends DB
{
	  var $conn_iro    	= null;
	  var $conn_local   = null;
	  var $params  	= null;
	  var $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	  var $parentid		= null;
	  var $module		= null;
	  var $data_city		= null;

	function __construct($params)
	{	
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		$remove_catidlist 	= trim($params['remove_catidlist']);
		$usercode			= trim($params['usercode']);
		$username			= trim($params['username']);
		$nonpaid_catidlist 	= trim($params['nonpaid_catidlist']);
		$ip_address  		= trim($params['ip_address']);
		$nonpaid_catidarr	=	explode(",",$nonpaid_catidlist);
		$this->removeCats  = '';
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
		if(trim($usercode)=='')
		{
			$message = "User Code is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($username)=='')
		{
			$message = "User Name is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($ip_address)=='')
		{
			$message = "IP Address is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}

		$this->companyClass_obj = 	new companyClass();
		
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode		= $usercode;
		$this->uname 	    = $username;
		$this->ip_address   = $ip_address;
		$this->params 		= $params;
		//vars fr logs		
		$this->jdbox_url_log = '';
		
		if(strtolower($this->module) != 'cs')
		{
			$message = "Invalid Module - This Service is only applicable for CS.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
	
		$this->validationcode	=	'SAVENONPAIDCAT';
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();	
		
		$urls = $this->get_curl_url($this->data_city);
		$this->cs_url			  = $urls['url'];
		$this->jdbox_url		  = $urls['jdbox_url'];		
		$this->genInfoArr         = $this->getGenInfoShadow();
		$this->extraDetailsArr    = $this->getextradetailsInfoShadow();
		$this->interMediateTable  = $this->getIntermediateData();
		$this->business_temp_data = $this->getBusinessTemData();
		
		$temp_pincode			  = trim($this->genInfoArr['pincode']);
		$this->logFields = array();
		$this->logFields['params'] = $params;
		
		
		if(count($this->genInfoArr) <=0)
		{
			$message = "No Data Found For this Parentid in tbl_companymaster_generalinfo_shadow";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->extraDetailsArr) <=0)
		{
			$message = "No Data Found For this Parentid in tbl_companymaster_extradetails_shadow";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->interMediateTable) <=0)
		{
			$message = "No Data Found For this Parentid in tbl_temp_intermediate";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->business_temp_data) <=0)
		{
			$message = "No Data Found For this Parentid in tbl_business_temp_data";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		
		$this->genInfoArrMain         = $this->getGenInfoMain();
		$this->extraDetailsArrMain    = $this->getextradetailsInfoMain();
		
		$live_pincode			  = trim($this->genInfoArrMain['pincode']);
		
		if((intval($temp_pincode) != intval($live_pincode)) && ( $this->params['isRNEDealcose']!=1 ) )
		{
			$message = "There is a change in picode with live data. You are not allowed to change pincode with Save & Exit option.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}	
			
		$this->temp_paid_cat_arr 	= array();
		$this->temp_nonpaid_cat_arr = array();
		if(isset($this->params['isRNEDealcose']) && $this->params['isRNEDealcose']==1){
			$this->fetchTempData();
		}else{
			$this->fetchContractTempCategories();
		}
		
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
		
		$this->movie_timing_arr = array();
		
		if(is_array($params['movie_timing']) && count($params['movie_timing'])>0)
		{
			foreach($params['movie_timing'] as $moviecatid => $movietimingval)
			{
				if(!in_array($moviecatid,$this->remove_catidlist_arr))
				{
					$this->movie_timing_arr[$moviecatid] = $movietimingval;
				}
			}
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
		
		$this->remote_city_flag = 0;
		$this->is_remote = '';
		if($conn_city == 'remote')
		{
			$this->remote_city_flag = 1;
			$this->is_remote = 'REMOTE';
		}
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc			= $db[$conn_city]['idc']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		
		if(strtolower($this->module)=='cs'){
			$this->conn_temp = $this->conn_local;
		}else if(strtolower($this->module)=='me'){
			$this->conn_temp = $this->conn_idc;
		}else if(strtolower($this->module)=='tme'){
			$this->conn_temp = $this->conn_tme;
		} 
		
		
	}
	function save_category_temp_data()
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
			/*----- Paid Authorised Category Addition----*/
			/*if(count($this->paid_auth_add_catids_arr)>0)
			{
				$this->temp_paid_cat_arr = array_merge($this->temp_paid_cat_arr,$this->paid_auth_add_catids_arr);
				$this->temp_paid_cat_arr = $this->getValidCategories($this->temp_paid_cat_arr);
			}*/
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
			
			$movie_timing_catid_arr = array();
			$matched_paid_movie_catid_arr = array();
			if(count($this->movie_timing_arr)>0)
			{
				$movie_timing_catid_arr = array_keys($this->movie_timing_arr);
				$matched_paid_movie_catid_arr = array_intersect($final_temp_paid_cat_arr,$movie_timing_catid_arr);
			}
			$catids_arr = array();
			$catnames_arr = array();
			$national_catids_arr = array();
			$sloganstr = '';
			$htmldump = '';
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
					$movie_timing_val = '';
					if(array_key_exists($catid, $this->movie_timing_arr))
					{
						$movie_timing_val = $this->movie_timing_arr[$catid];
					}
					if(count($matched_paid_movie_catid_arr)>0)
					{
						$sloganstr .= $catname."~~~".$movie_timing_val."~~~".$catid."|$|";
						if($i % 2 == 1)
						{
							$htmldump .= "<tr><td width='15'></td><td width='326' class='fontA14'>".$catname;
							if($movie_timing_val)
								$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td>";
							else
								$htmldump .= "</td>";
						}
						else
						{
							$htmldump .= "<td width='30'></td><td width='326' class='fontA14'>".$catname;
							if($movie_timing_val)
								$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
							else
								$htmldump .= "</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
						}
						$i++;
					}
				}
				if(count($matched_paid_movie_catid_arr)>0)
				{
					if($i % 2 == 1) 
					{
						$htmldump .= "<td width='30'></td><td width='326'></td><td width='15'></td></tr><tr><td height='8'></td></tr>";
					}
					$htmldump = "<table cellspacing='0' cellpadding='0' border='1' width='100%' align='center' class='bgcolfee0'>".$htmldump."</table>";
					
					if($sloganstr && substr($sloganstr,0,3)!='|$|')
					{
						$sloganstr="|$|".$sloganstr;
					}
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
			$htmldump 	    = $this->slasher($htmldump);
			$slogan			= $this->slasher($sloganstr);
			 
			// Update tbl_business_temp_data
			$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET 
										categories 		= '".$categories."',
										catIds 			= '".$catids_str."',
										nationalcatIds 	= '".$national_catids_str."',
										catSelected 	= '".$catSelected."',
										htmldump		= '".$htmldump."',
										slogan			= '".$slogan."',
										categories_list	= ''
										WHERE contractid='".$this->parentid."'";
			$resUpdtBusinessTempData   = parent::execQuery($sqlUpdtBusinessTempData, $this->conn_local);
		}
		else
		{
				$message = "There Should Be Atleast One Paid Category In The Contract";
				echo json_encode($this->sendDieMessage($message));
				die();
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
		$all_catidlist_arr = array_merge($final_temp_paid_cat_arr,$final_temp_nonpaid_cat_arr);
		//adding missed categories 
		$MissingCategoryArr = array();
		
		if(count($all_catidlist_arr)>0)
		{	
			
			$catids_list = implode(",",$all_catidlist_arr);
			$categories_status_data = "&module=cs&catid=".$catids_list."&parentid=".$this->parentid;
			
			$cs_app_url = $this->cs_url."api/category_info_api.php";
			$category_info_arr  = json_decode($this->curl_call_post($cs_app_url,$categories_status_data),true);
			if(trim($category_info_arr['RestaurantTagged']['message']) == 'RestaurantTagged')
			{
				if(trim($category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_extra_msg']) == 'AddMissingCategory')
				{
					$MissingCategoryArr['RestMissingCatid'] = $category_info_arr['RestaurantTagged']['restaurant_addinfo']['missing_catid'];
				}
			}
			if(trim($category_info_arr['MissingBrandGenericCategory']['message']) == 'MissingBrandGenericCategory')
			{
				$MissingCategoryArr['BrandMissingCatid'] = $category_info_arr['MissingBrandGenericCategory']['catid'];
			}
		}	
		if(count($MissingCategoryArr)>0)
		{
			$missing_nonpaid_catid_arr 		= array();
			$rest_missing_catid_arr	= array();
			if($MissingCategoryArr['RestMissingCatid']){
				$rest_missing_catid_str 	= $MissingCategoryArr['RestMissingCatid'];
				$rest_missing_catid_arr 	= explode("|~|",$rest_missing_catid_str);
			}
			$brand_missing_catid_arr = array();
			if($MissingCategoryArr['BrandMissingCatid']){
				$brand_missing_catid_str 	= $MissingCategoryArr['BrandMissingCatid'];
				$brand_missing_catid_arr 	= explode("|~|",$brand_missing_catid_str);
			}
			$missing_nonpaid_catid_arr = array_merge($rest_missing_catid_arr,$brand_missing_catid_arr);
			$final_temp_nonpaid_cat_arr = array_merge($final_temp_nonpaid_cat_arr,$missing_nonpaid_catid_arr);
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
		//~ $premium_category_list_left = $this->audit_premium_category_paidNonpaid($temp_nonpaid_without_paid_catarr);
		if(count($temp_nonpaid_without_paid_catarr)>0)
		{
			$all_temp_nonpaid_cat_arr = $this->getCategoryDetails($temp_nonpaid_without_paid_catarr);
			$movie_timing_catid_arr = array();
			$matched_nonpaid_movie_catid_arr = array();
			if(count($this->movie_timing_arr)>0)
			{
				$movie_timing_catid_arr = array_keys($this->movie_timing_arr);
				$matched_nonpaid_movie_catid_arr = array_intersect($temp_nonpaid_without_paid_catarr,$movie_timing_catid_arr);
			}
		
			$catids_nonpaid_arr = array();
			$national_catids_nonpaid_arr = array();
			$sloganstr_np = '';
			$htmldump_np = '';
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
					$movie_timing_val = '';
					if(array_key_exists($catid, $this->movie_timing_arr))
					{
						$movie_timing_val = $this->movie_timing_arr[$catid];
					}
					if(count($matched_nonpaid_movie_catid_arr)>0)
					{
						$sloganstr_np .= $catname."~~~".$movie_timing_val."~~~".$catid."|$|";
						if($i % 2 == 1)
						{
							$htmldump_np .= "<tr><td width='15'></td><td width='326' class='fontA14'>".$catname;
							if($movie_timing_val)
								$htmldump_np .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td>";
							else
								$htmldump_np .= "</td>";
						}
						else
						{
							$htmldump_np .= "<td width='30'></td><td width='326' class='fontA14'>".$catname;
							if($movie_timing_val)
								$htmldump_np .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
							else
								$htmldump_np .= "</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
						}
						$i++;
					}
				}
				if(count($matched_nonpaid_movie_catid_arr)>0)
				{
					if($i % 2 == 1) 
					{
						$htmldump_np .= "<td width='30'></td><td width='326'></td><td width='15'></td></tr><tr><td height='8'></td></tr>";
					}
					$htmldump_np = "<table cellspacing='0' cellpadding='0' border='1' width='100%' align='center' class='bgcolfee0'>".$htmldump_np."</table>";
					
					if($sloganstr_np && substr($sloganstr_np,0,3)!='|$|')
					{
						$sloganstr_np="|$|".$sloganstr_np;
					}
				}
			}
			$htmldump_np 	= $this->slasher($htmldump_np);
			$slogan_np		= $this->slasher($sloganstr_np);
				
				
			$sqlUpdtMovieTimingNonpaid = "UPDATE tbl_business_temp_data SET 
										  htmldump_np		= '".$htmldump_np."',
										  slogan_np			= '".$slogan_np."'
										  WHERE contractid='".$this->parentid."'";
										  
			$resUpdtMovieTimingNonpaid   = parent::execQuery($sqlUpdtMovieTimingNonpaid, $this->conn_local);
			
			$catidlineage_nonpaid 			= "/".implode('/,/',$catids_nonpaid_arr)."/";
			$national_catidlineage_nonpaid 	= "/".implode('/,/',$national_catids_nonpaid_arr)."/";
			
			$catlin_nonpaid_db = '';
	
			$sqlUpdateExtraShadow = "UPDATE tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '".$catidlineage_nonpaid."', national_catidlineage_nonpaid = '".$national_catidlineage_nonpaid."' WHERE parentid = '".$this->parentid."'";
			$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_iro);
			
			if($this->remove_cats){
				$sqlMovie = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' and catid in ('".$this->remove_cats."')";
				$resMovie   = parent::execQuery($sqlMovie, $this->conn_iro);
			}
			
			
		}
		else
		{
			$catlin_nonpaid_db = '';
			$sqlUpdtMovieTimingNonpaid = "UPDATE tbl_business_temp_data SET htmldump_np	= '',slogan_np	= '' WHERE contractid='".$this->parentid."'";
			$resUpdtMovieTimingNonpaid   = parent::execQuery($sqlUpdtMovieTimingNonpaid, $this->conn_local);
			
			$sqlUpdateExtraShadow = "UPDATE tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '', national_catidlineage_nonpaid = '' WHERE parentid = '".$this->parentid."'";
			$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_iro);
			
			if($this->remove_cats){
				$sqlMovie = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' and catid in ('".$this->remove_cats."')";
				$resMovie   = parent::execQuery($sqlMovie, $this->conn_iro);
			}
		}
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
		$sql_national_temp = "select Category_city from tbl_national_listing_temp where parentid='".$this->parentid."'";
		$resnational_temp   = parent::execQuery($sql_national_temp, $this->conn_local);
		if(mysql_num_rows($resnational_temp)>0)
		{
			$row_national = mysql_fetch_assoc($resnational_temp);
			$catergory_count = count($final_national_catids_arr);
			$final_national_catids_str = implode("|P|",$final_national_catids_arr);
			$final_national_catids_str = "|P|".$final_national_catids_str."|P|";
			
			$sql_national_main = "Update db_national_listing.tbl_national_listing set Category_city='".addslashes($row_national['Category_city'])."',Category_nationalid='".$final_national_catids_str."',TotalCategoryWeight=".$catergory_count." where parentid='".$this->parentid."'";
			$resnational_main   = parent::execQuery($sql_national_main, $this->conn_idc);
		}
		//print_r($catids_arr);die;	
		//here add rest of the code		
		/**## code here for handling freez and masking starts##**/		
		$update_business_temp_data = $this->update_attr_based_on_cat();
		$this->loggingIntoTable();
		$this->handleFreezing();
		$this->RestaurantMapping();
		$this->updateMovieTimeLog($final_temp_paid_cat_arr,$temp_nonpaid_without_paid_catarr,$slogan,$slogan_np);
		$this->updateBusFacilityDump($htmldump,$slogan,$htmldump_np,$slogan_np);
		$this->updateLogMovie();
		
		$rowStd = $this->getPincode();
		if($this->interMediateTable['hiddenCon'] == '1')
		{
				$display = 0;
				$hidden  = 1;
		}else
		{
				$display = 1;
				$hidden  = 0;
		 }
			  
		if($this->interMediateTable['freez']==1||$this->interMediateTable['mask']==1)
		{
			$display=0;
		}
		$sql_serve   = "select pin_code from tbl_areas_count where pin_code like '%".$this->genInfoArr[pincode]."%' and display>0";
		$res_serve   = parent::execQuery($sql_serve, $this->conn_local);
		if(mysql_num_rows($res_serve)>0)
		{
			$alsoserve=0;
		}
		else
		{
			$alsoserve=1;
		}
				
		$final_catidlineage_search   = array();
		if(count($temp_nonpaid_without_paid_catarr)>0){
			$this->extraDetailsArr['catidlineage_nonpaid'] = '/'.implode('/,/',$temp_nonpaid_without_paid_catarr).'/';
		}else{
			$this->extraDetailsArr['catidlineage_nonpaid'] = '';
		}
		
		
		$temp_paid_catids		= implode(',',$final_temp_paid_cat_arr);
		$paid_parent_catids		= $this->getParentCategories($temp_paid_catids);	
		
		$final_catidlineage_search = array_merge($temp_nonpaid_without_paid_catarr,$final_temp_paid_cat_arr);
		$final_catidlineage_search = array_merge($final_catidlineage_search,$paid_parent_catids);
		$final_catidlineage_search = $this->getValidCategories($final_catidlineage_search);
		$this->extraDetailsArr['catidlineage_search']  = '/'.implode('/,/',$final_catidlineage_search).'/';
		
		if(count($temp_nonpaid_without_paid_catarr)>0){
			$national_catidlineage_nonpaid_res = $this->getNationalCatlineage($this->extraDetailsArr['catidlineage_nonpaid']);
			$this->extraDetailsArr['national_catidlineage_nonpaid'] = $national_catidlineage_nonpaid_res;
		}else{
			$this->extraDetailsArr['national_catidlineage_nonpaid'] = '';
		}
		
		$national_catidlineage_nonpaid_search = $this->getNationalCatlineage($this->extraDetailsArr['catidlineage_search']);
		$this->extraDetailsArr['national_catidlineage_search']  = $national_catidlineage_nonpaid_search;
		$phone_search_res = $this->phoneSearchArr();
		$mainSource = $this->getTmeSearch();
		
		/**#########Post processing starts here ########**/
		$post_arr = array();
		$areaLineage 	= '/'.$this->genInfoArr['country'].'/'.$this->genInfoArr['data_city'].'/'.addslashes($this->genInfoArr[city]).'/'.addslashes($this->genInfoArr[area]).'/';
		$address 		= 	$this->genInfoArr['full_address'].",".$this->genInfoArr['city'].",".$this->genInfoArr['state'];	
		$date			= date('Y-m-d H:i:s');
		$catidLineage 	= '/'.implode('/,/',$final_temp_paid_cat_arr).'/';
		
		$getBusiness_temp_data = $this->getBusinessTemData();
		
		// tbl_companymaster_generalinfo
		$post_arr['nationalid']				= $this->genInfoArr['nationalid'];
		$post_arr['companyname']			= $this->genInfoArr['companyname'];
		$post_arr['regionid']				= $rowStd[stdcode];
		$post_arr['country']				= $this->genInfoArr['country'];
		$post_arr['state']					= $this->genInfoArr['state'];
		$post_arr['city']					= $this->genInfoArr['city'];
		$post_arr['display_city']			= $this->genInfoArr['display_city'];
		$post_arr['area']					= $this->genInfoArr['area'];
		$post_arr['subarea']				= $this->genInfoArr['subarea'];
		$post_arr['office_no']				= $this->genInfoArr['office_no'];
		$post_arr['building_name']			= $this->genInfoArr['building_name'];
		$post_arr['street']					= $this->genInfoArr['street'];
		$post_arr['street_direction']		= $this->genInfoArr['street_direction'];
		$post_arr['street_suffix']			= $this->genInfoArr['street_suffix'];
		$post_arr['landmark']				= $this->genInfoArr['landmark'];
		$post_arr['landmark_custom']		= $this->genInfoArr['landmark_custom'];
		$post_arr['pincode']				= $this->genInfoArr['pincode'];
		$post_arr['pincode_addinfo']		= $this->genInfoArr['pincode_addinfo'];
		$post_arr['latitude']				= $this->genInfoArr['latitude'];
		$post_arr['longitude']				= $this->genInfoArr['longitude'];
		$post_arr['geocode_accuracy_level']	= $this->genInfoArr['geocode_accuracy_level'];
		$post_arr['full_address']			= $this->genInfoArr['full_address'];
		$post_arr['stdcode']				= $rowStd[stdcode];
		$post_arr['landline']				= $this->genInfoArr['landline'];
		$post_arr['landline_display']		= $this->genInfoArr['landline_display'];
		$post_arr['landline_feedback']		= $this->genInfoArr['landline_feedback'];
		$post_arr['mobile']					= $this->genInfoArr['mobile'];
		$post_arr['mobile_display']			= $this->genInfoArr['mobile_display'];
		$post_arr['mobile_feedback']		= $this->genInfoArr['mobile_feedback'];
		$post_arr['fax']					= $this->genInfoArr['fax'];
		$post_arr['tollfree']				= $this->genInfoArr['tollfree'];
		$post_arr['tollfree_display']		= $this->genInfoArr['tollfree_display'];
		$post_arr['email']					= $this->genInfoArr['email'];
		$post_arr['email_display']			= $this->genInfoArr['email_display'];
		$post_arr['email_feedback']			= $this->genInfoArr['email_feedback'];
		$post_arr['sms_scode']				= $this->genInfoArr['sms_scode'];
		$post_arr['website']				= $this->genInfoArr['website'];
		$post_arr['contact_person']			= $this->genInfoArr['contact_person'];
		$post_arr['contact_person_display']	= $this->genInfoArr['contact_person'];
		$post_arr['callconnect']			= $this->interMediateTable['callconnect'];
		$post_arr['othercity_number']		= $this->genInfoArr['othercity_number'];
		$post_arr['displayType']			= str_replace(",","~",$this->interMediateTable['displayType']);
		$post_arr['hide_address']			= $this->genInfoArr['hide_address'];
		$post_arr['data_city']				= $this->genInfoArr['data_city'];
				
		//tbl_companymaster_extradetails
		$post_arr['parentid'] 				= $this->parentid;
		$post_arr['sphinx_id']				= $this->genInfoArr['sphinx_id'];
		$post_arr['landline_addinfo'] 		= $this->extraDetailsArr['landline_addinfo'];
		$post_arr['mobile_addinfo'] 		= $this->extraDetailsArr['mobile_addinfo'];
		$post_arr['tollfree_addinfo'] 		= $this->extraDetailsArr['tollfree_addinfo'];
		$post_arr['contact_person_addinfo'] = $this->extraDetailsArr['contact_person_addinfo'];
		$post_arr['attributes'] 			= $getBusiness_temp_data['mainattr'];
		$post_arr['attributes_edit'] 		= $getBusiness_temp_data['facility'] ;
		$post_arr['attribute_search'] 		= $this->extraDetailsArr['attribute_search'] ;
		$post_arr['callconnectid']          = $this->interMediateTable['callconnectid'];
		$post_arr['virtualNumber']      	= $this->interMediateTable['virtualNumber'];
		$post_arr['virtual_mapped_number']  = $this->interMediateTable['virtual_mapped_number'];
		$post_arr['turnover'] 				= $this->extraDetailsArr['turnover'];
		$post_arr['working_time_start'] 	= $this->extraDetailsArr['working_time_start'];
		$post_arr['working_time_end'] 		= $this->extraDetailsArr['working_time_end'];
		$post_arr['payment_type'] 			= $this->extraDetailsArr['payment_type'];
		$post_arr['year_establishment'] 	= $this->extraDetailsArr['year_establishment'];
		$post_arr['accreditations'] 		= $this->extraDetailsArr['accreditations'];
		$post_arr['certificates'] 			= $this->extraDetailsArr['certificates'];
		$post_arr['no_employee'] 			= $this->extraDetailsArr['no_employee'];
		$post_arr['business_group'] 		= $this->extraDetailsArr['business_group'];
		$post_arr['email_feedback_freq'] 	= $this->extraDetailsArr['email_feedback_freq'];
		$post_arr['statement_flag'] 		= $this->extraDetailsArr['statement_flag'];
		$post_arr['alsoServeFlag'] 			= $alsoserve;
		$post_arr['contract_calltype'] 		= $this->interMediateTable['contract_calltype'];
		$post_arr['catidlineage_nonpaid']	= $this->extraDetailsArr['catidlineage_nonpaid'];
		$post_arr['catidlineage_search']	= $this->extraDetailsArr['catidlineage_search'];
		$post_arr['national_catidlineage_nonpaid'] = $this->extraDetailsArr['national_catidlineage_nonpaid'];
		$post_arr['national_catidlineage_search']  = $this->extraDetailsArr['national_catidlineage_search'];
		$post_arr['deactflg'] 				= $this->interMediateTable['deactivate'];
		$post_arr['display_flag'] 			= $display;
		$post_arr['category']				= $catidLineage;
		$post_arr['category_count']			= $this->extraDetailsArr['category_count'];
		$post_arr['hotcategory']			= $this->extraDetailsArr['hotcategory'];
		$post_arr['fmobile'] 				= $this->extraDetailsArr['fmobile'];
		$post_arr['femail'] 				= $this->extraDetailsArr['femail'];
		$post_arr['flgActive'] 				= $this->extraDetailsArr['flgActive'];
		$post_arr['freeze'] 				= $this->interMediateTable['freez'];
		$post_arr['mask'] 					= $this->interMediateTable['mask'];
		$post_arr['hidden_flag'] 			= $hidden;
		$post_arr['lockDateTime'] 			= $this->extraDetailsArr['lockDateTime'];
		$post_arr['lockedBy'] 				= $this->extraDetailsArr['lockedBy'];
		$post_arr['temp_deactive_start'] 	= $this->interMediateTable['temp_deactive_start'];
		$post_arr['temp_deactive_end'] 		= $this->interMediateTable['temp_deactive_end'];
		$post_arr['promptype'] 				= $this->extraDetailsArr['promptype'];
		$post_arr['updatedBy'] 				= $this->extraDetailsArr['updatedBy'];
		$post_arr['updatedOn'] 				= $this->extraDetailsArr['updatedOn'];
		$post_arr['map_pointer_flags']		= $this->extraDetailsArr['map_pointer_flags'];
		$post_arr['flags']					= $this->extraDetailsArr['flags'];
		$post_arr['tme_code']				= $this->interMediateTable['tme_code'];
		$post_arr['tag_line']				= $this->extraDetailsArr['tag_line'];
		$post_arr['tag_Image_path']			= $this->extraDetailsArr['tag_Image_path'];
		$post_arr['tag_description']		= $this->extraDetailsArr['tag_description'];
		$post_arr['tag_catid']				= $this->extraDetailsArr['tag_catid'];							
		$post_arr['tag_catname']			= $this->extraDetailsArr['tag_catname'];
		$post_arr['CorporateDealers']		= $this->extraDetailsArr['CorporateDealers'];
		$post_arr['closedown_flag']			= $this->extraDetailsArr['closedown_flag'];
		$post_arr['fb_prefered_language']	= $this->extraDetailsArr['fb_prefered_language'];
		$post_arr['companyname_old']        = $this->genInfoArrMain['companyname'];
		$old_closedown_flag 			    = $this->extraDetailsArrMain['closedown_flag'];
		$new_closedown_flag 				= $this->extraDetailsArr['closedown_flag'];
		if(intval(trim($old_closedown_flag)) != intval(trim($new_closedown_flag)))
		{
			$post_arr['closedown_date'] = date("Y-m-d H:i:s");
		}
		
		// tbl_tmesearch
		$post_arr['contractid'] 	= $this->extraDetailsArr['parentid'];
		$post_arr['compname'] 		= addslashes(stripslashes($this->genInfoArr['companyname']));
		$post_arr['pincode']		= $this->genInfoArr['pincode'];
		$post_arr['freez'] 			= $this->interMediateTable['freez'];
		$post_arr['mainsource'] 	= $mainSource['mainsource'];
		$post_arr['subsource'] 		= $mainSource['subsource'];
		$post_arr['datesource'] 	= $this->interMediateTable['datesource'];
		$post_arr['arealineage'] 	= $areaLineage;
		$post_arr['contact_details']= $phone_search_res;
		$post_arr['cc_status']      = $this->genInfoArr['cc_status'];
		$post_arr['is_remote'] 		= $this->is_remote;
		$post_arr['session_key'] 	= $this->generateRandomString(15);
		$post_arr['ucode']  	 	= trim($this->ucode);
		$post_arr['uname']   		= trim($this->uname);
		$post_arr['source']  		= trim($this->module);
		$post_arr['narration'] 		= $this->interMediateTable['narration'];
		$post_arr['paid'] 			= 1;
		$post_arr['validationcode'] = $this->validationcode;
		$post_arr['datasource_date']= $date;
		
		//tbl_companymaster_search
		
		$post_arr['phone_search']				= $phone_search_res;
		$post_arr['address']					= addslashes($address);
		$post_arr['length']						= strlen($this->genInfoArr['companyname']);
		$post_arr['social_media_url']			= $this->extraDetailsArr['social_media_url'];
		$post_arr['helpline_flag']				= $this->extraDetailsArr['helpline_flag'];
		
		//geocode api call here 
		$param_array 		= array();
		$arr_old_add		= array();
		$arr_new_add		= array();
		$param_array2 		= array();
		if(count($this->genInfoArrMain) > 0)
		{
			$old_company 								= $this->genInfoArrMain['companyname'];
			$param_array['building_name_old'] 			= $this->genInfoArrMain['building_name'];
			$param_array['landmark_old']				= $this->genInfoArrMain['landmark'];
			$param_array['street_old'] 					= $this->genInfoArrMain['street'];
			$param_array['area_old']					= $this->genInfoArrMain['area'];
			$param_array['pincode_old']					= $this->genInfoArrMain['pincode'];
			$param_array['city_old']					= $this->genInfoArrMain['city'];
			$param_array['geocode_accuracy_level_old']	= $this->genInfoArrMain['geocode_accuracy_level'];
			$param_array['latitude_old']				= $this->genInfoArrMain['latitude'];
			$param_array['longitude_old']				= $this->genInfoArrMain['longitude'];
			$param_array['flags']						= $this->extraDetailsArrMain['flags'];
			$param_array['map_pointer_flags']			= $this->extraDetailsArrMain['map_pointer_flags'];
			
			$arr_old_add['state'] 						= $this->genInfoArrMain['state'];
			$arr_old_add['city']						= $this->genInfoArrMain['city'];
			$arr_old_add['building_name']				= $this->genInfoArrMain['building_name'];
			$arr_old_add['landmark']					= $this->genInfoArrMain['landmark'];
			$arr_old_add['street']						= $this->genInfoArrMain['street'];
			$arr_old_add['area']						= $this->genInfoArrMain['area'];
			$arr_old_add['pincode']						= $this->genInfoArrMain['pincode'];
			$arr_old_add['latitude']					= $this->genInfoArrMain['latitude'];
			$arr_old_add['longitude']					= $this->genInfoArrMain['longitude'];
			$arr_old_add['geocode_accuracy_level'] 		= $this->genInfoArrMain['geocode_accuracy_level'];
		}
		else
		{
			$old_company = '';
		}

			if(count($this->extraDetailsArrMain) > 0)
			{	
				$old_closedown_flag					= $this->extraDetailsArrMain['closedown_flag'];
				$landline_addinfo_old 				= $this->extraDetailsArrMain['landline_addinfo'];
				$mobile_addinfo_old 				= $this->extraDetailsArrMain['mobile_addinfo'];
				$tollfree_addinfo_old 				= $this->extraDetailsArrMain['tollfree_addinfo'];
			}
			else
			{
					$landline_addinfo_old 	= '';
					$mobile_addinfo_old 	= '';
					$tollfree_addinfo_old 	= '';
			}
			$param_array['building_name']				= $this->genInfoArr['building_name'];
			$param_array['landmark']					= $this->genInfoArr['landmark'];
			$param_array['street']						= $this->genInfoArr['street']; 
			$param_array['area']						= $this->genInfoArr['area'];
			$param_array['pincode']						= $this->genInfoArr['pincode'];
			$param_array['city']						= $this->genInfoArr['city'];
			$param_array['latitude']					= $this->genInfoArr['latitude'];
			$param_array['longitude']					= $this->genInfoArr['longitude'];
			$param_array['module']						= $this->module;
			$param_array['rquest']						= "getGeocodeAccuracy";
			$param_array['parentid']					= $this->extraDetailsArr['parentid'];
			
			$arr_new_add['state'] 						= $this->genInfoArr['state'];
			$arr_new_add['city'] 						= $this->genInfoArr['city'];
			$arr_new_add['building_name']				= $this->genInfoArr['building_name'];
			$arr_new_add['landmark']					= $this->genInfoArr['landmark'];
			$arr_new_add['street']						= $this->genInfoArr['street'];
			$arr_new_add['area']						= $this->genInfoArr['area'];
			$arr_new_add['pincode']						= $this->genInfoArr['pincode'];
			$arr_new_add['latitude']					= $this->genInfoArr['latitude'];
			$arr_new_add['longitude']					= $this->genInfoArr['longitude'];
			$arr_new_add['geocode_accuracy_level']		= $this->genInfoArr['geocode_accuracy_level'];
			$url_geocode = $this->cs_url."api_services/api_geocode_accuracy.php";
			$resmsg = $this->curl_call_post($url_geocode,$param_array);
			
			$json_return_arr = json_decode($resmsg,true);
			if(strtolower($json_return_arr['status']) == 'pass')
			{
				$post_arr['geocode_accuracy_level'] 	= $json_return_arr['data']['geocode_accuracy_level'];
				$geocode_accuracy_level_old 			= $json_return_arr['data']['geocode_accuracy_level_old'];
				$post_arr['sent_to_moderation']			= $json_return_arr['data']['sent_to_moderation'];
				$post_arr['latitude']					= $json_return_arr['data']['latitude'];
				$post_arr['longitude'] 					= $json_return_arr['data']['longitude'];
				$post_arr['map_pointer_flags']			= $json_return_arr['data']['map_pointer_flags'];
				$post_arr['flags'] 						= $json_return_arr['data']['flags'];
				$sent_to_moderation						= $json_return_arr['data']['sent_to_moderation'];
			}	
			$post_arr['save_nonpaid_cat'] 	= 1;
			$post_arr['flow_module'] 		= 'CS';
			$post_arr['instantlive_flag'] 	= 1;
			
			$resjdbox_arr = $this->jdboxCurlCall($post_arr);
			$this->logFields['final_paid'] 	= implode(',',$final_temp_paid_cat_arr);
			$this->logFields['final_nonpaid'] = implode(',',$final_temp_nonpaid_cat_arr);
			if($sent_to_moderation == 'yes') // Send to geocode moderation module
			{
				if(empty($arr_old_add) && !empty($arr_new_add))
				{
					// Do not send to moderation if it is a new contract
				}
				else
				{
					$param_array2['module']				= 	$this->module;
					$param_array2['parentid']			=	$post_arr['parentid'];
					$param_array2['uname']			=	$post_arr['uname'];
					$param_array2['ucode']				=	$post_arr['ucode'];
					$param_array2['temp_latitude']		=	$post_arr['latitude'];
					$param_array2['temp_longitude']		=	$post_arr['longitude'];
					$param_array2['temp_tagging']		=	$post_arr['geocode_accuracy_level'];
					$param_array2['original_tagging']	=	$this->genInfoArrMain['geocode_accuracy_level_old'];
					$param_array2['new_address']		=	json_encode($arr_new_add);
					$param_array2['old_address']		=	json_encode($arr_old_add);
					$param_array2['rquest']				=	"insertGeocodeModeration";
					$resmsg = $this->curl_call_post($url_geocode,$param_array2);
				}
			}
			
			$comment_alert_param_arr = array();				
			$comment_alert_param_arr['parentid'] 			 = $this->parentid;
			$comment_alert_param_arr['companyname'] 		 = $this->genInfoArr['companyname'];
			$comment_alert_param_arr['data_city'] 			 = $this->data_city;
			$comment_alert_param_arr['source'] 				 = $this->module;
			$comment_alert_param_arr['ucode'] 				 = $this->ucode;
			$comment_alert_param_arr['uname'] 			     = $this->uname;
			$comment_alert_param_arr['landline_addinfo_old'] = $landline_addinfo_old;
			$comment_alert_param_arr['mobile_addinfo_old'] 	 = $mobile_addinfo_old;
			$comment_alert_param_arr['tollfree_addinfo_old'] = $tollfree_addinfo_old;
			$file_path = $this->cs_url."api/send_contact_comment_update_alert.php";
			$comment_update_alert_res = $this->curl_call_post($file_path,$comment_alert_param_arr);
		
		/**#########Post processing ends here ########**/
		$this->updateContractHavingTollFreeNum();
		$this->update_lock_company();	
		$this->tmeLog();	
		/*#######calling csgenio api for primaryCategoryInsertion, insertNonpaidBidcat  starts######*/	
		$paramforApi['parentid'] = $this->parentid;
		$paramforApi['ucode'] = $this->ucode;
		$paramforApi['data_city'] = $this->data_city;
		$paramforApi['action'] = 'before';
		$service_url = $this->cs_url."api/save_nonpaid_categoryApi.php";
		$res = $this->curl_call_post($service_url,$paramforApi);
		
		$resCallBrand = $this->callBrandMarkApi($old_company);	
	
	
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";	
		$resLog['result'] = $result_msg_arr;
		$this->functionForSendLogs($resLog);
		return $result_msg_arr;
			
			
	}
	
	function paidAuthorisedCatOperations()
	{
		//~ echo "here";
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
			$paid_auth_nonauth_natcatids_str = implode(",",$paid_auth_nonauth_natcatids_arr); // where auth_gen_ncatid in national_catid of array_keys($paid_auth_nonauth_cat_arr)
		}
		if($paid_auth_nonauth_natcatids_str !='')
		{
			//$sqlPaidAuthorisedCategory = "SELECT catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid IN ('".$paid_auth_nonauth_natcatids_str."') AND biddable_type=1 AND mask_status=0";
			//$resPaidAuthorisedCategory 	= parent::execQuery($sqlPaidAuthorisedCategory, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		= 'save_nonapid_category_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,auth_gen_ncatid';

			if($paid_auth_nonauth_natcatids_str!=''){
				$where_arr  	=	array();
				$where_arr['auth_gen_ncatid']	= $paid_auth_nonauth_natcatids_str;
				$where_arr['biddable_type']		= '1';
				$where_arr['mask_status']		= '0';				
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
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
			$nonpaid_auth_nonauth_catids_arr 	= array_keys($nonpaid_auth_nonauth_cat_arr);
			$nonpaid_auth_nonauth_natcatids_arr = $this->getCategoryDetails($nonpaid_auth_nonauth_catids_arr,1);
			$nonpaid_auth_nonauth_natcatids_str = implode(",",$nonpaid_auth_nonauth_natcatids_arr); // where auth_gen_ncatid in national_catid of array_keys($nonpaid_auth_nonauth_cat_arr)
			
		}
		if($nonpaid_auth_nonauth_natcatids_str !='')
		{
			//$sqlnonpaidAuthorisedCategory = "SELECT catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE auth_gen_ncatid IN ('".$nonpaid_auth_nonauth_natcatids_str."') AND biddable_type=1 AND mask_status= 0";
			//$resnonpaidAuthorisedCategory 	= parent::execQuery($sqlnonpaidAuthorisedCategory, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		= 'save_nonapid_category_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,auth_gen_ncatid';

			if($nonpaid_auth_nonauth_natcatids_str!=''){
				$where_arr  	=	array();
				$where_arr['auth_gen_ncatid']	= $nonpaid_auth_nonauth_natcatids_str;
				$where_arr['biddable_type']		= '1';
				$where_arr['mask_status']		= '0';				
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']))
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
		$catids_str = implode(",",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid,auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'save_nonapid_category_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,national_catid,auth_gen_ncatid';

		if($catids_str!=''){
			$where_arr  	=	array();
			$where_arr['catid']		= $catids_str;
			$cat_params['where']	= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key =>$row_catdetails)
			{
				$catid 			= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$national_catid	= trim($row_catdetails['national_catid']);
				$auth_gen_ncatid= trim($row_catdetails['auth_gen_ncatid']);
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
		$catid_arr  = array();
		if($catid!=''){
			$catid_arr =	explode(",",$catid);
		}
		$auth_gen_ncatid = 0;
		//$sqlAuthGenCatid = "SELECT auth_gen_ncatid FROM tbl_categorymaster_generalinfo WHERE catid = '".$catid."'";
		//$resAuthGenCatid 	= parent::execQuery($sqlAuthGenCatid, $this->conn_local);
		$cat_params = array();
		$cat_params['page'] 		= 'save_nonapid_category_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'auth_gen_ncatid';

		if(count($catid_arr)>0){
			$where_arr  	=	array();
			$where_arr['catid']		= implode(",", $catid_arr);
			$cat_params['where']	= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			//$row_authgen_catid  = parent::fetchData($resAuthGenCatid);

			$auth_gen_ncatid	= intval($cat_res_arr['results']['0']['auth_gen_ncatid']);
		}
		return $auth_gen_ncatid;
	}
	
	function fetchTempData(){	
		if(isset($this->business_temp_data['catIds']) && $this->business_temp_data['catIds'] != '')
		{
			$temp_catlin_arr 	= 	array();
			$temp_catlin_arr  	=   explode('|P|',$this->business_temp_data['catIds']);
			$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
			$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
			$this->temp_paid_cat_arr = $temp_catlin_arr;
		}		
	}
	function fetchContractTempCategories()
	{
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		
		$temp_category_arr = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'catidlineage,catidlineage_nonpaid';
		$cat_params['page']			= 'save_nonapid_category_class';

		$resTempCategory			= 	array();
		$resTempCategory			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($resTempCategory) && $resTempCategory['errors']['code']==0){

			$row_temp_category 		=	$resTempCategory['results']['data'][$this->parentid];
			
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr = array();
				$temp_catlin_arr = explode("/,/",trim($row_temp_category['catidlineage'],"/"));
				$temp_catlin_arr = array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr 	= 	$this->getValidCategories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}

		/*$sqlTempCategory	=	"SELECT catidlineage,catidlineage_nonpaid FROM tbl_companymaster_extradetails  WHERE parentid = '" . $this->parentid . "'";
		$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->conn_iro);
		if($resTempCategory && mysql_num_rows($resTempCategory))
		{
			$row_temp_category	=	mysql_fetch_assoc($resTempCategory);
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				
				$temp_catlin_arr = array();
				$temp_catlin_arr = explode("/,/",trim($row_temp_category['catidlineage'],"/"));
				$temp_catlin_arr = array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
				
				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr 	= 	$this->getValidCategories($temp_catlin_np_arr);
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}*/

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
	
	function audit_premium_category_paidNonpaid($temp_nonpaid_catid_arr)
	{
		$premium_cat_result_arr = array();
	
		$live_nonpaid_catid_str = $this->genInfoArrMain['catidlineage_nonpaid'];
		$live_nonpaid_catid_arr = array();
		if($live_nonpaid_catid_str)
		{
			$live_nonpaid_catid_arr = explode(',',$live_nonpaid_catid_str);    
		}
		// To get Original set of nonpaid catid from  tbl_companymaster_extradetails  -- Ends here
		// To get new added categoires    -starts here   
		
		$new_added_catid_arr = array_diff($temp_nonpaid_catid_arr,$live_nonpaid_catid_arr);  // contains both premium or normal categories
		// To get new added categoires    -----  Ends here 
		if(COUNT($new_added_catid_arr)>0)
		{
			$all_catids = implode(",",$new_added_catid_arr);
			//$sql_pre_cat = "SELECT distinct(catid) FROM tbl_categorymaster_generalinfo where catid IN ('".$all_catids."') AND premium_flag=1";
			//$res_pre_cat 	= parent::execQuery($sql_pre_cat, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		= 'save_nonapid_category_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid';

			if($all_catids!=''){
				$where_arr  	=	array();
				$where_arr['catid']			= $all_catids;
				$where_arr['premium_flag']	= '1';
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key => $row_pre_cat)
				{
					$pre_cat_arr[] = $row_pre_cat['catid']; 
				}
			}
			if(count($pre_cat_arr)>0)
			{
				$non_pre_cat_arr = array_diff($temp_nonpaid_catid_arr,$pre_cat_arr); 
				$non_pre_cat_arr = array_filter($non_pre_cat_arr);
				$non_pre_cat_arr = array_merge(array_unique($non_pre_cat_arr));
				$premium_cat_result_arr = $non_pre_cat_arr;
			}
			else
			{
				$premium_cat_result_arr = $temp_nonpaid_catid_arr;
			}
			
			if(count($pre_cat_arr)>0)
			{
				foreach($pre_cat_arr as $key=>$value)
				{
					$insert_into_premium_cat = "INSERT INTO tbl_premium_categories_audit SET
									companyname		= '".addslashes($this->companyname)."',
									parentid		= '".$this->parentid."',
									catids			= '".$value."',
									username		= '".addslashes($this->uname)."',
									Userid			= '".$this->ucode."',
									Dept			= '".$this->module."',
									City			= '".addslashes($this->data_city)."',
									updatetime		= NOW(),
									paid_status		= '1',
									approval_status = '0',
									paid_category 	= '0',
									jdfos_flag		= '0',
									updatedby	 	= '',
									updatedcity 	= ''
									
									ON DUPLICATE KEY UPDATE
									
									companyname		= '".addslashes($this->companyname)."',
									username		= '".addslashes($this->uname)."',
									Userid			= '".$this->ucode."',
									Dept			= '".$this->module."',
									City			= '".$this->data_city."',
									updatetime		= NOW(),
									paid_status		= '1',
									approval_status = '0',
									paid_category 	= '0',
									jdfos_flag		= '0',
									updatedby	 	= '',
									updatedcity 	= ''";
					$res_premium_cat 	= parent::execQuery($insert_into_premium_cat, $this->conn_local);
					$insert_into_premium_cat_log = "INSERT INTO tbl_premium_categories_audit_log SET
												companyname		= '".addslashes($this->companyname)."',
												parentid		= '".$this->parentid."',
												catids			= '".$value."',
												username		= '".addslashes($this->uname)."',
												Userid			= '".$this->ucode."',
												Dept			= '".$this->module."',
												City			= '".$this->data_city."',
												updatetime		= NOW(),
												paid_status		= '1',
												approval_status = '0',
												paid_category 	= '0'";
										
					$res_premium_cat_log 	= parent::execQuery($insert_into_premium_cat_log, $this->conn_local);
				}
				
			}
		}
		else
		{
			$premium_cat_result_arr = $temp_nonpaid_catid_arr;
			
		}
		return $premium_cat_result_arr;
	}	
	function curl_call_post($curlurl,$input_arr)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $input_arr);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		//~ echo "<pre>";print_r($content);
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
	function get_curl_url()
	{
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$url = "http://imteyazraja.jdsoftware.com/csgenio/";
			$jdbox_url 				= "http://imteyazraja.jdsoftware.com/jdbox/";
			$city_indicator 		= "main_city";
	
		}
		else
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI' :
					$url 					= "http://".MUMBAI_CS_API."/";
					$jdbox_url 				= "http://".MUMBAI_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'AHMEDABAD' :
					$url 					= "http://".AHMEDABAD_CS_API."/";
					$jdbox_url 				= "http://".AHMEDABAD_JDBOX_API."/";
					$city_indicator = "main_city";
					break;

				case 'BANGALORE' :
					$url 					= "http://".BANGALORE_CS_API."/";
					$jdbox_url 				= "http://".BANGALORE_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'CHENNAI' :
					$url 					= "http://".CHENNAI_CS_API."/";
					$jdbox_url 				= "http://".CHENNAI_JDBOX_API."/";
					$city_indicator		    = "main_city";
					break;

				case 'DELHI' :
					$url 					= "http://".DELHI_CS_API."/";
					$jdbox_url 				= "http://".DELHI_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'HYDERABAD' :
					$url 					= "http://".HYDERABAD_CS_API."/";
					$jdbox_url 				= "http://".HYDERABAD_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'KOLKATA' :
					$url 					= "http://".KOLKATA_CS_API."/";
					$jdbox_url 				= "http://".KOLKATA_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				case 'PUNE' :
					$url 					= "http://".PUNE_CS_API."/";
					$jdbox_url 				= "http://".PUNE_JDBOX_API."/";
					$city_indicator 		= "main_city";
					break;

				default:
					$url 					= "http://".REMOTE_CITIES_CS_API."/";
					$jdbox_url 				= "http://".REMOTE_CITIES_JDBOX_API."/";
					$city_indicator 		= "remote_city";
					break;
			}	
			
		}
		$urlArr['url'] 					= $url;
		$urlArr['jdbox_url'] 			= $jdbox_url;
		$urlArr['city_indicator'] 		= $city_indicator;
		return $urlArr;
	}
	 function getGenInfoShadow()
	 {
		$sql_gen_info = "select * from tbl_companymaster_generalinfo_shadow where parentid= '".$this->parentid."'";
		$res_gen_info = parent::execQuery($sql_gen_info, $this->conn_iro);	
		if ($res_gen_info && mysql_num_rows($res_gen_info) > 0)
		{
			$genInfoArr   = mysql_fetch_assoc($res_gen_info);
		}
		return $genInfoArr;
	 }
	
	function getextradetailsInfoShadow()
	{
		$sql_comp_extra	= "select * from tbl_companymaster_extradetails_shadow where parentid= '".$this->parentid."'";
		$res_comp_extra = parent::execQuery($sql_comp_extra, $this->conn_iro);	
		if ($res_comp_extra && mysql_num_rows($res_comp_extra) > 0)
		{
			$extDetArr      = mysql_fetch_assoc($res_comp_extra);
		}
		return $extDetArr;			
	}
	
	 function getGenInfoMain()
	 {

	 	$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'companyname,building_name,landmark,street,area,pincode,city,state,geocode_accuracy_level,latitude,longitude,mobile,landline,tollfree';
		$cat_params['page']			= 'save_nonapid_category_class';

		$cat_api_res		= 	array();
		$cat_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($cat_api_res) && $cat_api_res['errors']['code']==0){
			$genInfoArrMain 		=	$cat_api_res['results']['data'][$this->parentid];
			$genInfoArrMain['numrows'] = count($cat_api_res['results']['data']);
		}

		return $genInfoArrMain;
	 }
	
	function getextradetailsInfoMain()
	{

		$extDetArrMain = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'flags,map_pointer_flags,closedown_flag,landline_addinfo,mobile_addinfo,tollfree_addinfo,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search,national_catidlineage_search';
		$cat_params['page']			= 'save_nonapid_category_class';

		$res_comp_extra			= 	array();
		$res_comp_extra			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		
		if(!empty($res_comp_extra) && $res_comp_extra['errors']['code']==0){
			$extDetArrMain 		=	$res_comp_extra['results']['data'][$this->parentid];
		}

		/*$sql_comp_extra	= "select flags, map_pointer_flags, closedown_flag, landline_addinfo, mobile_addinfo, tollfree_addinfo,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search, national_catidlineage_search from tbl_companymaster_extradetails where parentid= '".$this->parentid."'";
		$res_comp_extra = parent::execQuery($sql_comp_extra, $this->conn_iro);	
		if ($res_comp_extra && mysql_num_rows($res_comp_extra) > 0)
		{
			$extDetArrMain      = mysql_fetch_assoc($res_comp_extra);
		}*/

		return $extDetArrMain;			
	}

	function getIntermediateData()
	{
		$sql_comp_int	= "select * from tbl_temp_intermediate where parentid= '".$this->parentid."'";
		$res_comp_int = parent::execQuery($sql_comp_int, $this->conn_local);		
		if ($res_comp_int && mysql_num_rows($res_comp_int) > 0)
		{
			$intermedArr      = mysql_fetch_assoc($res_comp_int);	
		}
		return $intermedArr;
	}
	
	function chkTempFrzStatus()
	{
		$tempFrzArr = array();
		$sqlFrzUnFrzStatus = "SELECT parentid,update_flag,temp_deactive_start,temp_deactive_end,companyname FROM tbl_temp_deactivate_contracts WHERE parentid = '".$this->parentid."'" ;
		$resFrzUnFrzStatus = parent::execQuery($sqlFrzUnFrzStatus, $this->conn_local);		
		if($resFrzUnFrzStatus && mysql_num_rows($resFrzUnFrzStatus)>0)
		{
			$row_temp_frz = mysql_fetch_assoc($resFrzUnFrzStatus);
			$tempFrzArr['updtflg'] = $row_temp_frz['update_flag'];
			$tempFrzArr['temp_deactive_start'] = $row_temp_frz['temp_deactive_start'];
			$tempFrzArr['temp_deactive_end'] = $row_temp_frz['temp_deactive_end'];
			$tempFrzArr['companyname'] = $row_temp_frz['companyname'];
		}
		return $tempFrzArr;
	}
	
	function tempFrzContractLog($updtflg, $process)
	{
			$sqlTempFrzLog = "INSERT INTO tbl_temp_deactivate_contracts_log SET 
						  parentid = '".$this->parentid."',
						  companyname = '".$this->genInfoArr['companyname']."',
						  temp_deactive_start = '".$this->interMediateTable['temp_deactive_start']."',
						  temp_deactive_end = '".$this->interMediateTable['temp_deactive_end']."',
						  update_flag      = '".$updtflg."',
						  usercode        = '".$this->ucode."',
						  username        = '".$this->uname."',
						  updatedate      = NOW(),
						  process_name    = '".$process."'";
		$resTempFrzLog = parent::execQuery($sqlTempFrzLog, $this->conn_local);
	}
	function removeTempFrzContract()
	{
		$is_deleted = 0;
		$sqlRemoveContracts = "DELETE FROM tbl_temp_deactivate_contracts WHERE parentid = '".$this->parentid."'";
		$resRemoveContracts = parent::execQuery($sqlRemoveContracts, $this->conn_local);
		if($resRemoveContracts)
		{
			$is_deleted = 1;
		}
		return $is_deleted;
	}
	function updateMovieTimeLog($paidcatidsarr,$nonpaidcatidsarr,$slogan,$slogan_np)
	{
		$sloganstr 		= str_replace("|$||$|","|$|",$slogan.$slogan_np);
		$moviecatidsarr = array_merge($paidcatidsarr,$nonpaidcatidsarr);
		$moviecatidsarr = $this->getValidCategories($moviecatidsarr);
		
		
		$logArr = array();
		$finalLogData = array();
		$temp_catids_arr = array();
		$new_logData_arr = array();
		$old_logData_arr = array();

		$final_log_values = array();

		$slogan_arr = explode("|$|",$sloganstr);
		$slogan_arr = array_filter($slogan_arr);

	// To get Original catids & Slogan From LIVE ---- starts here
		$original_arr = array();
		$extra_moives_catid_arr = array();

		//$sql_original = "SELECT sloganstr FROM bus_facility_dump WHERE refno='".$parentid."'";
		$sql_original = "SELECT REPLACE(CONCAT(IFNULL(sloganstr,''),IFNULL(sloganstr_np,'')),'|$||$|','|$|') AS sloganstr  FROM  bus_facility_dump WHERE refno='".$this->parentid."'";
		$res_original = parent::execQuery($sql_original, $this->conn_local);
		if($res_original && mysql_num_rows($res_original)>0)
		{
			$row_original = mysql_fetch_assoc($res_original);
			$original_slogan_arr = explode("|$|",$row_original['sloganstr']);
			$original_slogan_arr = array_filter($original_slogan_arr);
		}

		$row_old_cat = array();
		$cat_params = array();
		$cat_params['data_city']	= $this->data_city;
		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'catidlineage,catidlineage_nonpaid';
		$cat_params['page']			= 'save_nonapid_category_class';

		$res_old_cat			= 	array();
		$res_old_cat			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		
		if(!empty($res_old_cat) && $res_old_cat['errors']['code']==0){
			$row_old_cat 		=	$res_old_cat['results']['data'][$this->parentid];
			$row_old_cat['catidlineage'] = $row_old_cat['catidlineage'].','.$row_old_cat['catidlineage_nonpaid'];
			$extra_catids = str_replace('/','',$row_old_cat['catidlineage']);
			$extra_catids_arr = explode(',',$extra_catids);
		}

		/*$sql_old_cat = "SELECT CONCAT(IFNULL(catidlineage,''),',',IFNULL(catidlineage_nonpaid,'')) AS catidlineage FROM db_iro.tbl_companymaster_extradetails WHERE parentid ='".$this->parentid."'";
		$res_old_cat = parent::execQuery($sql_old_cat, $this->conn_iro);
		if($res_old_cat && mysql_num_rows($res_old_cat)>0)
		{
			$row_old_cat = mysql_fetch_assoc($res_old_cat);
			$extra_catids = str_replace('/','',$row_old_cat['catidlineage']);
			$extra_catids_arr = explode(',',$extra_catids);
		}*/
		// To get Original catids & Slogan From LIVE ---- Ends here

		if(COUNT($extra_catids_arr)>0)
		{
			$moviecatidsarr = array_merge($extra_catids_arr,$moviecatidsarr);
			$moviecatidsarr = array_filter($moviecatidsarr);
			$moviecatidsarr = array_unique($moviecatidsarr);
		}

		$catids = implode(",",$moviecatidsarr);

		// To find only movies related catis  -- Starts here
		//$sql_qry = "SELECT DISTINCT(catid) as catid FROM tbl_categorymaster_generalinfo WHERE catid in ('".$catids."') AND (category_verticals & 8 = 8)";
		//$res_qry = parent::execQuery($sql_qry, $this->conn_local);
		$moives_catid_arr = array();

		$cat_params = array();
		$cat_params['page'] 		= 'save_nonapid_category_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid';

		if($catids!=''){
			$where_arr  	=	array();
			$where_arr['catid']					= $catids;
			$where_arr['category_verticals']	= '8';
			$cat_params['where']	= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key => $row_qry)
			{
				foreach ($slogan_arr as $key=>$value)
				{
					if(strstr($value,$row_qry['catid']))
					{
						$moives_catid_arr[$row_qry['catid']] = $value;
					}
				}
				if(count($extra_catids_arr)>0)
				{
					foreach ($original_slogan_arr as $key=>$value)
					{
						if(strstr($value,$row_qry['catid']))
						{
							$extra_moives_catid_arr[$row_qry['catid']] = $value;
						}
					}
				}
				$movie_catids[] = $row_qry['catid'];
			}
		}
		// To find only movies related catis  -- Ends here

		if(COUNT($moives_catid_arr)>0)
		{
			foreach($moives_catid_arr as $key => $value)
			{
				if(strcmp(trim($value),trim($extra_moives_catid_arr[$key])) != 0)
				{
					$new_logData_arr[$key] = $moives_catid_arr[$key];
					$old_logData_arr[$key] = $extra_moives_catid_arr[$key];
				}
			}
		}
		//print "<pre>";print_r($new_logData_arr);exit;
		foreach($new_logData_arr as $movie_key => $movie_value)
		{
			$new_temp = array();
			$new_temp = explode('~~~',$movie_value);
			$new_logArr[$new_temp[2]] = $new_temp;
		}

		foreach($old_logData_arr as $movie_key => $movie_value)
		{
			$old_temp = array();
			$old_temp = explode('~~~',$movie_value);
			$old_logArr[$old_temp[2]] = $old_temp;
		}

		if(COUNT($new_logArr)>0)
		{
			foreach($new_logArr as $log_key => $log_value)
			{
				if($log_value[1] !='' || $old_logArr[$log_key][1] !='')
				{
					$final_log_values[$log_key][catid] = $log_key;
					$final_log_values[$log_key][catname] = $log_value[0];
					$final_log_values[$log_key][oldvlaues] = $old_logArr[$log_key][1];
					$final_log_values[$log_key][newvlaues] = $log_value[1];
				}
			}
		}

		$insert_log_new_values = '';
		if(COUNT($final_log_values)>0)
		{
			foreach($final_log_values as $log_key => $log_value)
			{
				if($insert_log_new_values == '')
				{
					$insert_log_new_values = "('".$this->parentid."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d h:i:s')."','".$this->ucode."','".$this->data_city."','".$this->module."')";
				}
				else
				{
					$insert_log_new_values .= ",('".$this->parentid."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d h:i:s')."','".$this->ucode."','".$this->data_city."','".$this->module."')";
				}
				
			}
			$sql_log = "INSERT INTO tbl_movietimes_log (parentid,catid,catname,oldtimings,newtimings,updatedOn,updatedBy,city,dept) VALUES ".$insert_log_old_values.$insert_log_new_values;
			$res_log = parent::execQuery($sql_log, $this->conn_local);
		}
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

	function generateRandomString($length = 15) 
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	
	function jdboxCurlCall($param) 
	{				
		$curl_url 	= $this->jdbox_url."insert_api.php";
		$this->jdbox_url_log = $curl_url; 
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);
		//~ echo "<pre>";print_r($resmsg); 
		curl_close($ch);
	}

	function getNationalCatlineage($catid)
	{
		if(!empty($catid))
		{
			$catid_list				=	str_replace("/","",$catid);
			//$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list.")";
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] 		= 'save_nonapid_category_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,national_catid';

			if($catid_list!=''){
				$where_arr  	=	array();
				$where_arr['catid']		= $catid_list;
				$cat_params['where']	= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_national_catids)
				{
					$arr_national_catids[] = $row_national_catids['national_catid']; 
				}
			}
			
			$national_catids = '';
			
			if (is_array($arr_national_catids) && count($arr_national_catids))
			{			
				$national_catids = implode('/,/', $arr_national_catids);
			
				if (trim($national_catids) != '')
				{
					$national_catids = '/'.$national_catids.'/';
				}
			}

			return $national_catids;
		}
	}
	
	function handleFreezing()
	{
		$skip_frz_chk = 0;
		if(intval($this->extraDetailsArr['closedown_flag']) == 10)
		{
			$skip_frz_chk 			= 1;
			$frz_action_flag 		= 0;
			$this->interMediateTable['reason_id'] = 11;
			$this->interMediateTable['reason_text'] = "Not In Business";
			$this->interMediateTable['freez'] = 1;
			$this->interMediateTable['deactivate'] = 'F' ;
			$exact_reason			= "Not In Business";
			
			$sqlLogCheck = "SELECT SUBSTRING_INDEX(GROUP_CONCAT(reason ORDER BY date_time DESC ),',',1) AS reason_str FROM tbl_compfreez_details WHERE parentid = '".$this->parentid."' HAVING reason_str = 'Not In Business'";
			$resFrzLog   = parent::execQuery($sqlLogCheck, $this->conn_local);
			if($resFrzLog && mysql_num_rows($resFrzLog)<=0)
			{
				$sqlContractFrzLog = "INSERT INTO tbl_compfreez_details (contractid, parentid, reason, date_time, createdBy, freez, exact_reason, action_flag) VALUES ('".$this->parentid."', '".$this->parentid."', '".addslashes(stripslashes($this->interMediateTable['reason_text']))."', NOW(), '".$this->ucode."', '".$this->interMediateTable['freez']."', '".$exact_reason."', '".$frz_action_flag."')";
				$resFrzLog   = parent::execQuery($sqlContractFrzLog, $this->conn_local);
			}
		}
		
		
		if(($this->interMediateTable['freez'] == 1 || $this->interMediateTable['deactivate'] == 'FREEZ') && ($skip_frz_chk !=1))
		{
			$sqlGetReason = "SELECT reason_text FROM tbl_contract_reasons WHERE parentid = '".$this->parentid."'";
			$res_reason   = parent::execQuery($sqlGetReason, $this->conn_local);
			if ($res_reason && mysql_num_rows($res_reason) > 0)
			{
				$Row_tbl_contract_reasons = mysql_fetch_assoc($res_reason);
			}
			if (trim($Row_tbl_contract_reasons['reason_text']) != trim($this->interMediateTable['reason_text']))
			{
				$frz_action_flag = 0;
				$exact_reason_arr = array('Blacklist','Business Owner Decision','Caller Complaint','Duplicate','Invalid/Junk/Test','Not Contactable','Temporary','Legal Complaint');
				$reason_txt_arr = explode("-",$interMediateTable['reason_text']);
				$exact_reason_val = trim($reason_txt_arr[0]);
				$exact_reason = '';
				if(in_array($exact_reason_val,$exact_reason_arr))
				{
					$exact_reason = $exact_reason_val;
					if($exact_reason == 'Legal Complaint')
					{
						$frz_action_flag = 1;
					}
				}
				$sqlContractFrzLog = "INSERT INTO tbl_compfreez_details (contractid, parentid, reason, date_time, createdBy, freez, exact_reason, action_flag) VALUES ('".$this->parentid."', '".$this->parentid."', '".addslashes(stripslashes($this->interMediateTable['reason_text']))."', NOW(), '".$this->ucode."', '".$this->interMediateTable['freez']."', '".$exact_reason."', '".$frz_action_flag."')";
				$resFrzLog   = parent::execQuery($sqlContractFrzLog, $this->conn_local);
			}
		}
		else if(($this->interMediateTable['deactivate'] == 'N' && $this->extraDetailsArr ['deactflg'] == 'F') && ($skip_frz_chk !=1))
		{
				$sqlContractFrzLog = "INSERT INTO tbl_compfreez_details (contractid, parentid, reason, date_time, createdBy, freez) VALUES ('".$this->parentid."', '".$this->parentid."', 'UnFreezing Through Intermediate Page', NOW(), '".$this->ucode."', '".$this->interMediateTable['freez']."')";
				$resFrzLog   = parent::execQuery($sqlContractFrzLog, $this->conn_local);
				
				$sqlDeleteReason = "DELETE FROM tbl_contract_reasons WHERE contractid = '".$this->parentid."' ";
				$res_del_reason   = parent::execQuery($sqlDeleteReason, $this->conn_local);
		}
		
		/*-------------------------  Reason : Starts -------------------*/
		if($this->interMediateTable['reason_id'] != '')
		{
				$sqlFrzReason = "SELECT reasons FROM tbl_freeze_reasons WHERE reason_id = '".$this->interMediateTable['reason_id']."'";
				$resFrzReason   = parent::execQuery($sqlFrzReason, $this->conn_local);
				if ($resFrzReason && mysql_num_rows($resFrzReason) > 0)
				{
					$rowFrzReason = mysql_fetch_assoc($resFrzReason);
				}
				$sqlDelete = "DELETE FROM tbl_contract_reasons WHERE contractid='".$this->parentid."'";
				$dummy   = parent::execQuery($sqlDelete, $this->conn_local);
				
				$sqlInsertReason = "INSERT INTO tbl_contract_reasons SET
				contractid = '".$this->parentid."',
				parentid = '".$this->parentid."',
				lockedBy   = '".$this->uname." (".$this->ucode.")"."',
				lockDateTime = NOW(),
				reason_id = '".$this->interMediateTable['reason_id']."',
				reasons = '".addslashes(stripslashes($rowFrzReason['reasons']))."',
				reason_text = '".addslashes(stripslashes($this->interMediateTable['reason_text']))."'";
				$resInsrtReason   = parent::execQuery($sqlInsertReason, $this->conn_local);
		}
		/*--------------------------- Reason :End -----------------------------*/
				
		$tempFreezArr = array();
		if($this->interMediateTable['temp_deactive_start'] !='' && $this->interMediateTable['temp_deactive_end'] !='' && $this->interMediateTable['temp_deactive_start'] !='0000-00-00' && $this->interMediateTable['temp_deactive_end'] !='0000-00-00')
		{
			$tempFreezArr = $this->chkTempFrzStatus();
			if(count($tempFreezArr) >0 )
			{
				if($tempFreezArr['updtflg'] == 1)
				{
					$process = 'Update After Cron Freeze';
					$updtflg = 1;
				}
				else
				{
					if(($tempFreezArr['temp_deactive_start'] != $this->interMediateTable['temp_deactive_start']) || ($tempFreezArr['temp_deactive_end'] != $this->interMediateTable['temp_deactive_end']))
					{
						$process = 'Insert Again With Different Date Range';
						$updtflg = 0;
					}
					else
					{
						$process = 'Update';
						$updtflg = 0;
					}
				}	
			}
			else
			{
				$process = 'Insert';
				$updtflg = 0;
			}
			$InsrtTempDeactivateLog = "INSERT INTO tbl_temp_deactivate_contracts SET
									   parentid 			= '".$this->parentid."',
									   companyname 			= '".$this->genInfoArr['companyname']."',
									   temp_deactive_start 	= '".$this->interMediateTable['temp_deactive_start']."',
									   temp_deactive_end 	= '".$this->interMediateTable['temp_deactive_end']."',
									   update_flag      	= 0,
									   usercode        		= '".$this->ucode."',
									   username        		= '".$this->uname."',
									   updatedate      		= NOW(),
									   process_name    		= 'Save And Exit'
									   ON DUPLICATE KEY UPDATE
									   
									   companyname 			= '".$this->genInfoArr['companyname']."',
									   temp_deactive_start 	= '".$this->interMediateTable['temp_deactive_start']."',
									   temp_deactive_end 	= '".$this->interMediateTable['temp_deactive_end']."',
									   update_flag 			= '".$updtflg."',
									   usercode        		= '".$this->ucode."',
									   username        		= '".$this->uname."',
									   updatedate      		= NOW(),
									   process_name    		= 'Save And Exit'";
			$resInsrtDeactivateLog   = parent::execQuery($InsrtTempDeactivateLog, $this->conn_local);
			$this->tempFrzContractLog($updtflg, $process);
		}
		else
		{
			$process = "Delete";
			$tempFreezArr = $this->chkTempFrzStatus();
			if(count($tempFreezArr)>0)
			{
				$updtflg = $tempFreezArr['updtflg'];
				$this->removeTempFrzContract($this->parentid);
				$this->tempFrzContractLog($updtflg,$process);
			}
		}
	}
	
	function RestaurantMapping()
	{				
		if(($this->extraDetailsArr['closedown_flag'] == 1) || ($this->extraDetailsArr['closedown_flag'] == 2) || $this->interMediateTable['freez'] == 1)
		{	
			if($this->extraDetailsArr['closedown_flag'] == 1){
				$source = 'Closed Down';
			}else if($this->extraDetailsArr['closedown_flag'] == 2){
				$source = 'Shifted';
			}else if($this->interMediateTable['freez']){
				$source = 'Freeze';
			}
			$map_url = $this->cs_url."mapping_contract/mapping_contract_api.php";			
			$mapping_api_data 	= 	"parentid=".$this->parentid."&action=3&compname_hotel=".urlencode($this->genInfoArr['companyname'])."&usercode=".$this->ucode."&username=".urlencode($this->uname)."&userip=".$this->ip_address."&source=".$source;
			$mapping_api_res	= $this->curl_call_post($map_url,$mapping_api_data);
		}
			
	}
	
	function updateContractHavingTollFreeNum()
	{
		/*$sql_gen_info1 = "select mobile,landline,tollfree from tbl_companymaster_generalinfo where parentid= '".$this->parentid."'";
		$res_gen_info1   = parent::execQuery($sql_gen_info1, $this->conn_iro);
		if($res_gen_info1 && mysql_num_rows($res_gen_info1))
		{
			$genralInfoArr1=mysql_fetch_assoc($res_gen_info1);		
		}*/

		$genralInfoArr1 = array();
		$extraDetailsArr1 = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'mobile,landline,tollfree';
		$cat_params['page']			= 'save_nonapid_category_class';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		
		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
			$genralInfoArr1 		=	$res_gen_info1['results']['data'][$this->parentid];
		}

		$cat_params['table'] 		= 'extra_det_id';
		$cat_params['fields']		= 'flags';
		$cat_params['page']			= 'save_nonapid_category_class';

		$res_comp_extra1		= 	array();
		$res_comp_extra1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_comp_extra1) && $res_comp_extra1['errors']['code']==0){
			$extraDetailsArr1 		=	$res_comp_extra1['results']['data'][$this->parentid];
		}

			
		/*$sql_comp_extra1    = "select flags from tbl_companymaster_extradetails where parentid= '".$this->parentid."'"; // Old flags entry.
		$res_comp_extra1   = parent::execQuery($sql_comp_extra1, $this->conn_iro);
		if($res_comp_extra1 && mysql_num_rows($res_comp_extra1))
		{
			$extraDetailsArr1=mysql_fetch_assoc($res_comp_extra1);
		}*/

		$flags = 0;		
		$extrDetail_tollfreeFlag = (($extraDetailsArr1[flags]&1024) == 1024)?'1' :'0';   
		if ($genralInfoArr1[mobile] =='' && $genralInfoArr1[landline] =='' && $genralInfoArr1[tollfree] !='')
		{
			if($extrDetail_tollfreeFlag == 1)
			{
				$flags = ($extraDetailsArr1[flags] | 1024);
			}
		}
		else
		{	
			if($extrDetail_tollfreeFlag == 1)
			{
				if(($extraDetailsArr1[flags] & 1024) == 1024)
				{
					$flags =  ($extraDetailsArr1[flags] ^ 1024) ;
				}	
			}	
		}
		if($extrDetail_tollfreeFlag == 1)
		{
			$sqlUpdateTollfreeFlag = "UPDATE tbl_companymaster_extradetails SET flags = '".$flags."' WHERE parentid = '".$this->parentid."'";
			$sqlUpdateTollfreeFlag_rs   = parent::execQuery($sqlUpdateTollfreeFlag, $this->conn_iro);
		}

	}	
	function getBusinessTemData()
	{
		$selSloganCatid = "SELECT * from tbl_business_temp_data WHERE contractid ='".$this->parentid."'";
		$resSloganCatid = parent::execQuery($selSloganCatid, $this->conn_local);
		if($resSloganCatid && mysql_num_rows($resSloganCatid)>0)
		{
			$rowSloganCatid = mysql_fetch_assoc($resSloganCatid);
		}
		return $rowSloganCatid;
	}
	function getPinCode()
	{
		if($this->genInfoArr[pincode])
		{
			$sqlStd = "SELECT stdcode FROM tbl_stdcode_master WHERE pincode = '".$this->genInfoArr[pincode]."' LIMIT 1";
		}
		else
		{
			$sqlStd = "SELECT stdcode FROM tbl_stdcode_master WHERE city = '".$this->genInfoArr[city]."' LIMIT 1";
		}
		$resSelect   = parent::execQuery($sqlStd, $this->conn_local);
		$rowStd         = mysql_fetch_assoc($resSelect);
		
		return $rowStd;
	}
	
	function update_lock_company()
	{
		
		$sqlInsrtLockCompanyFlg  = "INSERT INTO tbl_lock_company SET
										parentId = '".$this->parentid."',
										updateBy = '".$this->ucode."',
										updatedDate = NOW(),
										UpdateFlag = '1'
										ON DUPLICATE KEY UPDATE
										updateBy = '".$this->ucode."',
										updatedDate = NOW(),
										UpdateFlag = '1'";
		$resInsrtLockCompanyFlg   = parent::execQuery($sqlInsrtLockCompanyFlg, $this->conn_local);
		
	}
	
	function getBrandMark()
	{
		$resultaArr = array();
		$sqlBrandmarkShadow = "SELECT parentid,action,process_flag,comment FROM tbl_company_brandmark_audit_shadow WHERE parentid = '".$this->parentid."'";
		$resBrandMarkShadow   = parent::execQuery($sqlBrandmarkShadow, $this->conn_iro);
		if($resBrandMarkShadow && mysql_num_rows($resBrandMarkShadow)>0)
		{
			$row_brandmark = mysql_fetch_assoc($resBrandMarkShadow);
			$resultaArr['action_taken'] = $row_brandmark['action'];
			$resultaArr['process_flag'] = $row_brandmark['process_flag'];
			$resultaArr['comment'] = $row_brandmark['comment'];
		}
		else
		{
			$resultaArr['process_flag'] = '-1';
			$resultaArr['action_taken'] = 'No Entry Found In Shadow Table';
		}
		return $resultaArr;
	}
	
	function updateBusFacilityDump($htmldump,$slogan,$htmldump_np,$slogan_np)
	{	
		$sqlFetchData = "SELECT refno from bus_facility_dump where refno = '".$this->parentid."'";
		$resFetchData = parent::execQuery($sqlFetchData, $this->conn_local);
		if((!mysql_num_rows($resFetchData)) && ($htmldump || $htmldump_np))
		{
			$insertqry="INSERT INTO bus_facility_dump SET
						refno= '".$this->parentid."', 
						htmldump = '".addslashes($htmldump)."',
						sloganstr = '".addslashes($slogan)."',
						htmldump_np = '".addslashes($htmldump_np)."',
						sloganstr_np = '".addslashes($slogan_np)."'";
			$resQry   = parent::execQuery($insertqry, $this->conn_local);
		}
		else
		{
			$updateData="update bus_facility_dump set htmldump = '".addslashes($htmldump)."' , sloganstr = '".addslashes($slogan)."' ,htmldump_np = '".addslashes($htmldump_np)."' , sloganstr_np = '".addslashes($slogan_np)."' where refno= '".$this->parentid."'";
			$resUpdt   = parent::execQuery($updateData, $this->conn_local);
		}
	}

	function phoneSearchArr()
	{
		if(trim($this->genInfoArr['mobile_display']))
		$phone_searchArr[]	=	trim($this->genInfoArr['mobile_display']);
		if(trim($this->genInfoArr['landline_display']))
			$phone_searchArr[]	=	trim($this->enInfoArr['landline_display']);
		if(trim($this->genInfoArr['tollfree_display']))
			$phone_searchArr[]	=	trim($this->genInfoArr['tollfree_display']);
		if(trim($this->genInfoArr['fax']))
			$phone_searchArr[]	=	trim($this->genInfoArr['fax']);
		if(trim($this->interMediateTable['virtualNumber']))
			$phone_searchArr[]	=	trim($this->interMediateTable['virtualNumber']);
		if(trim($this->genInfoArr['tollfree']))
			$phone_searchArr[]	=	trim($this->genInfoArr['tollfree']);	
			
		$phone_search	=	$this->dataInString(',',$phone_searchArr);
		
		return  $phone_search;
	}
	function getTmeSearch()
	{
		$sql_tmesearch_check = "select mainsource, subsource from tbl_tmesearch where parentid='".$extraDetailsArr[parentid]."'";
		$res_tmesearch_check   = parent::execQuery($sql_tmesearch_check, $this->conn_local);
		if($res_tmesearch_check && mysql_num_rows($res_tmesearch_check))
		{
			$row_tmesearch_check = mysql_fetch_assoc($res_tmesearch_check);
		}
		return $row_tmesearch_check;
	}

	function callBrandMarkApi($old_company)
	{
		if(trim($this->genInfoArr['mobile_feedback'])!='')
			$mobile_nums		= $this->genInfoArr['mobile_feedback'];
		else
			$mobile_nums		= $this->genInfoArr['mobile'];

		$mobile_no = '';
		if(!empty($mobile_nums))
		{
			$mobile_nums = trim($mobile_nums, ",");
			$mobile_arr = array();
			$mobile_arr = explode(",",$mobile_nums);
			if($mobile_arr[0]!='')
			{
				$mobile_no = $mobile_arr[0];
			}
		}
		$email_id = '';
		$email_ids = $this->genInfoArr['email'];
		if(!empty($email_ids))
		{
			$email_ids = trim($email_ids, ",");
			$email_arr = array();
			$email_arr = explode(",",$email_ids);
			if($email_arr[0] !='')
			{
				$email_id = $email_arr[0];
			}
		}
		
		
		
		$resBrandMark = $this->getBrandMark();		
		$brandmark_url = $this->cs_url."business/schedule_brandmark_alert.php";
		 $brandmark_data = "parentid=".$this->genInfoArr['parentid']."&new_compname=".urlencode($this->genInfoArr['companyname'])."&old_compname=".urlencode($old_company)."&ucode=".$this->ucode."&uname=".urlencode($this->uname)."&ct_name=".urlencode($this->data_city)."&contact_person=".urlencode($this->genInfoArr['contact_person'])."&source=".$this->module."&paid=1&mobile=".$mobile_no."&email_id=".urlencode($email_id)."&action=".urlencode($resBrandMark['action_taken'])."&process_flag=".$resBrandMark['process_flag']."&comment=".urlencode($resBrandMark['comment'])."&remote_city_flag=".$this->remote_city_flag;	
		$resmsg = $this->curl_call_post($brandmark_url,$brandmark_data);
	}
	
	function getParentCategories($catidlist)
	{	
		$parent_categories_arr = array();
		$catidarray		= null;
		$catidlistarr 	= explode(",",$catidlist);	
		$catidlistarr 	= array_unique($catidlistarr);
		$catidlistarr 	= array_filter($catidlistarr);
		$catidliststr 	= implode(",",$catidlistarr);

		//$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid > 0 AND category_name != '' ";
		//$res = parent::execQuery($sql, $this->conn_local);			
		$cat_params = array();
		$cat_params['page'] 		= 'save_nonapid_category_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'associate_national_catid';

		if($catidliststr!=''){
			$where_arr  	=	array();
			$where_arr['catid']		= $catidliststr;
			$cat_params['where']	= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			//$row = mysql_fetch_assoc($res);
			$associate_national_catid_arr = array();
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {
				$associate_national_catid =	$cat_arr['associate_national_catid'];
				if($associate_national_catid!=''){
					$associate_national_catid_arr[] = $associate_national_catid;
				}
			}
			if(count($associate_national_catid_arr)>0)
			{
				
				//$associate_national_catid_arr = explode(',',$row['associate_national_catid']);			
				
				$associate_national_catid_arr = array_unique($associate_national_catid_arr);
				$associate_national_catid_arr = array_filter($associate_national_catid_arr);
				$associate_national_catid_str = implode(",",$associate_national_catid_arr);
				//$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid IN (".$associate_national_catid_str.") and catid NOT IN (".$catidliststr.") AND catid > 0 AND category_name != '' ";
				//$res = parent::execQuery($sql, $this->conn_local);
				$cat_params = array();
				$cat_params['page'] 		= 'save_nonapid_category_class';
				$cat_params['data_city'] 	= $this->data_city;			
				$cat_params['return']		= 'associate_national_catid';

				if($associate_national_catid_str!=''){
					$where_arr  	=	array();
					$where_arr['national_catid']	= $associate_national_catid_str;
					$where_arr['catid']				= "!".$catidliststr;
					$cat_params['where']	= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
				
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
				{
					//$row = mysql_fetch_assoc($res);
					$parent_categories_arr = array();
					foreach ($cat_res_arr['results'] as $key => $cat_arr) {
						if($cat_arr['catid']!=''){
							$parent_categories_arr[] = $cat_arr['catid'];
						}
					}

					if(count($parent_categories_arr)>0)
					{
						//$parent_categories_arr = explode(',',$row['parent_categories']);
						
						$parent_categories_arr = array_unique($parent_categories_arr);
						$parent_categories_arr = array_filter($parent_categories_arr);					
					}
				}			
			}
		}
		return $parent_categories_arr;
	}
	function tmeLog()
	{
		if(count($this->genInfoArrMain) > 0)
		{
			if( strtolower($this->module) == 'cs' )
			{
				$qry_insert_tmeedt_flag="INSERT INTO tbl_cs_tme_edit_log SET parentid='".$this->parentid."',cs_update = now() ON DUPLICATE KEY UPDATE cs_update=now()";
				$res_insert_tmeedt_flag = parent::execQuery($qry_insert_tmeedt_flag, $this->conn_idc);	
			}
		}
	}
	
	function setAuthorizationFlag()
	{
		$sqlSetAuthorisationFlag = "INSERT INTO tbl_temp_flow_status SET
									parentid ='".$this->parentid."',
									authorisation_flag='1'
									
									ON DUPLICATE KEY UPDATE
									authorisation_flag='1'";
		$resSetAuthorisationFlag = parent::execQuery($sqlSetAuthorisationFlag, $this->conn_local);
	}
	function deleteFromBrowsingTable()
	{	
		$sqlDelBrowsingTable = "DELETE FROM tbl_business_temp_category WHERE contractid = '".$this->parentid."'";
		$resDelBrowsingTable =  parent::execQuery($sqlDelBrowsingTable, $this->conn_local);
	}
	
	function functionForSendLogs($result)
	{
		$api_url = $this->cs_url."api/save_nonpaid_categoryApi.php";
		$paramsLog['ID'] = $this->parentid;
		if($this->remote_city_flag == 1){
			$paramsLog['PUBLISH'] = "BACKEND_REMOTE";
		}else{
			$cap_data_city = strtoupper($this->data_city);
			$paramsLog['PUBLISH'] = "BACKEND_".$cap_data_city;
		}
		$paramsLog['USER_ID'] 	= $this->ucode."(".$this->uname.")";
		$paramsLog['ROUTE'] 	= strtoupper($this->module);
		$paramsLog['PARAMS'] 	= json_encode($this->logFields);		
		$paramsLog['RESULT'] 	= json_encode($result);	
		$paramsLog['action']	= 'after';
		$sendLogsApi  			= $this->curl_call_post($api_url, $paramsLog);
	}
	function update_attr_based_on_cat(){ 		
		$param = array();
		$param['parentid']   = $this->parentid;
		$param['data_city']  = $this->data_city;		
		$param['module']     = $this->module;
		$param['source']     = $this->module;
		$param['ucode']      = $this->ucode;
		$param['uname']      = $this->uname;
		$param['action']     = 'add_remove_attr';
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))	{
			$curl_url 	= "http://".$this->jdbox_url."/services/attributes_dealclose.php";
		}else{			
			$curl_url 	= "http://saritapc.jdsoftware.com/jdbox/services/attributes_dealclose.php";
		}		
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg = curl_exec($ch);		
		curl_close($ch);
	}	
	function loggingIntoTable()
	{
		$insertLog = "INSERT INTO tbl_save_exit_logs (parentid, update_time, updated_by) VALUES ('".$this->parentid."' , '".date('Y-m-d H:i:s')."' , '".$this->ucode."')";
		$resLog    = parent::execQuery($insertLog, $this->conn_iro);
	}
	
	function updateLogMovie(){
		
		$movie_details_old = array();
		$movie_details_new = array();
		$resArr  		   = array();
		
		$selTiming = "SELECT parentid FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."'";
		$resTiming   = parent::execQuery($selTiming, $this->conn_iro);
		if(mysql_num_rows($resTiming) > 0)
		{
			
			$temp_category_arr = $this->deleteCategoriesFromMovieTable();
			if(count($temp_category_arr) > 0)
			{
				$arrImploded = "'".implode("','",$temp_category_arr)."'";
				
				$del = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE catid NOT IN ($arrImploded) AND parentid='".$this->parentid."' ";
				$resDel   = parent::execQuery($del, $this->conn_iro);
			}
		}
		
		$old_details = "SELECT GROUP_CONCAT(TIME_FORMAT(movie_timings,'%I:%i %p')  ORDER BY movie_timings SEPARATOR ', ') AS timing,movie_date,index_mv,category_name,catid FROM     db_iro.tbl_movie_timings WHERE parentid='".$this->parentid."' GROUP BY category_name, movie_date ASC";
		
		$resOld_details   = parent::execQuery($old_details, $this->conn_iro);
		if(mysql_num_rows($resOld_details) > 0)
		{
			while($row_old_details = mysql_fetch_assoc($resOld_details))
			{
				$movie_date='';
				$catid 			= $row_old_details['catid'];
				$timing 		= $row_old_details['timing'];
				$category_name  = $row_old_details['category_name'];
				$movie_date 	= $row_old_details['movie_date'];
			
				$movie_details_old[$catid][$movie_date]['catid'] 		= $catid;
				$movie_details_old[$catid][$movie_date]['timing'] 		= $timing;
				$movie_details_old[$catid][$movie_date]['category_name'] = addslashes(stripslashes($category_name));
				$movie_details_old[$catid][$movie_date]['movie_date'] 	= $movie_date;
			}
		}
		
		$new_details = "SELECT GROUP_CONCAT(TIME_FORMAT(movie_timings,'%I:%i %p')  ORDER BY movie_timings SEPARATOR ', ') AS timing,movie_date,index_mv,category_name,catid FROM     db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' GROUP BY category_name, movie_date ASC";
		$resnew_details   = parent::execQuery($new_details, $this->conn_iro);
		if(mysql_num_rows($resnew_details) > 0)
		{
			while($row_new_details = mysql_fetch_assoc($resnew_details))
			{
				$movie_date='';
				$catid 			= $row_new_details['catid'];
				$timing 		= $row_new_details['timing'];
				$category_name  = $row_new_details['category_name'];
				$movie_date 	= $row_new_details['movie_date'];
				
				$movie_details_new[$catid][$movie_date]['catid'] 		= $catid;
				$movie_details_new[$catid][$movie_date]['timing'] 		= $timing;
				$movie_details_new[$catid][$movie_date]['category_name'] = addslashes(stripslashes($category_name));
				$movie_details_new[$catid][$movie_date]['movie_date'] 	= $movie_date;				
			}
		}
	
		 $data_city = strtolower($this->data_city);
		if(count($movie_details_old) >0 || count($movie_details_new)>0 ){
			
			 $json_old = stripslashes(json_encode($movie_details_old));
			 $json_new= stripslashes(json_encode($movie_details_new));
			 
			 $InsertToHistory ="INSERT INTO tbl_store_movie_history SET
												 parentid				= '".$this->parentid."',
												 catid 					= '".$catid."',
												 catname 				= '".addslashes(stripslashes($category_name))."',
												 update_time				= '".date('Y-m-d H:i:s')."',
												 updated_by				= '".$this->ucode."',
												 movie_details_old	    = '".$json_old."',
												 movie_details_new    	= '".$json_new."' ,
												 city 					= '".$data_city."'							
												";
			$resToHistory   = parent::execQuery($InsertToHistory, $this->conn_iro);
		}
		
		
		$del = "DELETE FROM db_iro.tbl_movie_timings WHERE parentid='".$this->parentid."'";
		$res   = parent::execQuery($del, $this->conn_iro);
		
		$del_TempData = "DELETE  FROM db_iro.tbl_movie_timings_shadow WHERE parentid = '".$this->parentid."' AND movie_date <DATE(NOW())";		
		$resToHistory = parent::execQuery($del_TempData, $this->conn_iro);
		
		/*$del_main_Data		 = "DELETE  FROM db_iro.tbl_movie_timings WHERE parentid = '".$this->parentid."' AND movie_date <DATE(NOW())";		
		$res_del_main_Data   = parent::execQuery($del_main_Data, $this->conn_iro);*/
		
		$selectTempData = "SELECT * FROM db_iro.tbl_movie_timings_shadow WHERE parentid = '".$this->parentid."'";		

		$resTempData   = parent::execQuery($selectTempData, $this->conn_iro);
		$countTemp 		= mysql_num_rows($resTempData);
		if($countTemp > 0)
		{
			while($rowTempData = mysql_fetch_assoc($resTempData))
			{
				$catid         = $rowTempData['catid'];
				$category_name = $rowTempData['category_name'];
				$movie_timings = $rowTempData['movie_timings'];
				$movie_date    = $rowTempData['movie_date'];
				$index_mv      = $rowTempData['index_mv'];
				
				if($movie_date !='' && $movie_timings !='' && ($catid != '' || $catid!=0))
				{
					$insertToMain = "INSERT INTO tbl_movie_timings
									SET
									parentid  = '".$this->parentid."',
									catid     = '".$catid."',
									category_name = '".addslashes(stripslashes($category_name))."' ,
									movie_timings = '".$movie_timings."',
									movie_date    = '".$movie_date."',
									index_mv      = '".$index_mv."'
									ON DUPLICATE KEY UPDATE
									category_name = '".addslashes(stripslashes($category_name))."',
									movie_timings = '".$movie_timings."',
									index_mv      = '".$index_mv."' ";									
					$resToMain   = parent::execQuery($insertToMain, $this->conn_iro);
					
						
				}
				
			}
		}
	}
	
	function deleteCategoriesFromMovieTable()
	{
		$temp_category_arr = array();
		$sqlTempCategory    =   "SELECT catids as catidlineage,catidlineage_nonpaid FROM d_jds.tbl_business_temp_data as A JOIN db_iro.tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
		$resTempCategory   = parent::execQuery($sqlTempCategory, $this->conn_iro);
		if($resTempCategory && mysql_num_rows($resTempCategory))
		{
			$row_temp_category    =  mysql_fetch_assoc($resTempCategory);
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr     =     array();
				$temp_catlin_arr      = explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr     =  array_filter($temp_catlin_arr);
				$temp_catlin_arr     =  $this->getValidCategories($temp_catlin_arr);

				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr =  $this->getValidCategories($temp_catlin_np_arr);

				$total_catlin_arr = array();
				$total_catlin_arr = array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->getValidCategories($total_catlin_arr);
			}
		}	
		return $temp_category_arr;
	}
	
}
?>
