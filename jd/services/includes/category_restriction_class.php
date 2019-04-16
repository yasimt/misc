<?php
class category_restriction_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		$all_catidlist 		= trim($params['all_catidlist']);
		$remove_catidlist 	= trim($params['remove_catidlist']);
		$page 				= trim($params['page']);
		$ucode 				= trim($params['ucode']);
		$geniolite 			= intval($params['geniolite']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}			
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->page 		= $page;
		$this->ucode 		= $ucode;
		$this->geniolite 	= $geniolite;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->companyClass_obj = new companyClass();

		$this->setServers();
		$this->all_catidlist_old 	= $all_catidlist;
		$this->remove_catidlist 	= $remove_catidlist;
		$all_catidlist_arr = array();
		$all_catidlist_arr = explode(',',$all_catidlist);
		$all_catidlist_arr = array_map('trim',$all_catidlist_arr);
		$all_catidlist_arr = array_unique($all_catidlist_arr);
		
		$remove_catidlist_arr = array();
		$remove_catidlist_arr = explode(',',$remove_catidlist);
		$remove_catidlist_arr = array_map('trim',$remove_catidlist_arr);
		$remove_catidlist_arr = array_unique($remove_catidlist_arr);
		
		$catids_arr = array();
		$catids_arr = array_diff($all_catidlist_arr,$remove_catidlist_arr);
		$catids_arr = array_unique($catids_arr);
		
		if(count($catids_arr) <=0){
			$optional_response_arr['popupcount'] = 0;
			$optional_response_arr['msg'] = 'Catid List is blank';
			$resultdata['CANPROCEED'] = $optional_response_arr;
			echo json_encode($resultdata);
			die();
		}
		$this->catids_arr = $catids_arr;
		$this->isExclusionTypeContract();
		$this->companyname = '';
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
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif($this->module =='ME')
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
			echo json_encode($this->send_die_message($message));
			die();
		}		
	}
	
	
	function getCategoryRestrictedInfo()
	{
		$landline_exist_flag 	= $this->contractLandlineInfo();
		$category_info_arr = $this->getCategoryTaggingDetails();
		$approved_moderate_cat_arr = $this->checkApprovedModerationCat();
		$proceed_flag = 1;
		
		$block_response_arr = array();
		
		if((trim($category_info_arr['HotelRestRestriction']['message']) == 'HotelRestRestriction') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] 					= 'HotelRestRestriction';
			$block_response_arr['HotelTaggedCatid'] 		= $category_info_arr['HotelRestRestriction']['HotelTaggedCatid'];
			$block_response_arr['HotelTaggedCategory'] 		= $category_info_arr['HotelRestRestriction']['HotelTaggedCategory'];
			$block_response_arr['RestTaggedCatid'] 			= $category_info_arr['HotelRestRestriction']['RestTaggedCatid'];
			$block_response_arr['RestTaggedCategory'] 		= $category_info_arr['HotelRestRestriction']['RestTaggedCategory'];
		} 
		if((trim($category_info_arr['PurevegNonvegRestriction']['message']) == 'PurevegNonvegRestriction') && ($proceed_flag ==1) && ($this->module =='TME' || $this->module =='DE' || $this->module =='ME'))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] 				= 'PurevegNonvegRestriction';
			$block_response_arr['PureVegCatid'] 		= $category_info_arr['PurevegNonvegRestriction']['PureVegCatid'];
			$block_response_arr['PureVegCategory'] 		= $category_info_arr['PurevegNonvegRestriction']['PureVegCategory'];
			$block_response_arr['NonVegCatid'] 			= $category_info_arr['PurevegNonvegRestriction']['NonVegCatid'];
			$block_response_arr['NonVegCategory'] 		= $category_info_arr['PurevegNonvegRestriction']['NonVegCategory'];
		}
		if((trim($category_info_arr['RestaurantTagged']['message']) == 'RestaurantTagged') && ($proceed_flag ==1))
		{
			if(trim($category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_extra_msg']) == 'MoreThanTwoPriceFilterExist')
			{
				$proceed_flag = 0;
				$block_response_arr['message'] = 'RestPriceFilterTagged';
				$block_response_arr['RestPriceRangeCategory'] = $category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_catname'];
			}
			if($proceed_flag ==1){
				if(trim($category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_extra_msg']) == 'PriceFiltersRestriction')
				{
					$proceed_flag = 0;
					$block_response_arr['message'] = 'PriceFiltersRestriction';
					$block_response_arr['RestPriceRangeCategory'] = $category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_catname'];
				}
			}
		}
		if((trim($category_info_arr['5StarAndHomeDelivery']['message']) == '5StarAndHomeDelivery') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] = '5StarAndHomeDelivery';
			$block_response_arr['FiveStarHotelCatid'] 		= $category_info_arr['5StarAndHomeDelivery']['FiveStarHotelCatid'];
			$block_response_arr['FiveStarHotelCategory'] 	= $category_info_arr['5StarAndHomeDelivery']['FiveStarHotelCategory'];
			$block_response_arr['HomeDeliveryRestCatid'] 	= $category_info_arr['5StarAndHomeDelivery']['HomeDeliveryRestCatid'];
			$block_response_arr['HomeDeliveryRestaurant'] 	= $category_info_arr['5StarAndHomeDelivery']['HomeDeliveryRestaurant'];
		}
		if((trim($category_info_arr['PharmCatRestriction']['message']) == 'PharmCatRestriction') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] 					= 'PharmCatRestriction';
			$block_response_arr['PharmTaggedCatid'] 		= $category_info_arr['PharmCatRestriction']['PharmTaggedCatid'];
			$block_response_arr['PharmTaggedCategory'] 		= $category_info_arr['PharmCatRestriction']['PharmTaggedCategory'];
			$block_response_arr['DocHospTaggedCatid'] 		= $category_info_arr['PharmCatRestriction']['DocHospTaggedCatid'];
			$block_response_arr['DocHospTaggedCategory'] 	= $category_info_arr['PharmCatRestriction']['DocHospTaggedCategory'];
		}
		if((trim($category_info_arr['SingleBrandTagged']['message']) == 'SingleBrandTagged') && ($proceed_flag ==1))
		{
			$brand_bypass_check_flag = $this->contractByPassCheck(1);
			$single_brand_tagged_cat_arr = array();
			$single_brand_tagged_cat_arr = explode("|~|",$category_info_arr['SingleBrandTagged']['catname']);
			if((count($single_brand_tagged_cat_arr)>1) && ($brand_bypass_check_flag !=1))
			{
				$proceed_flag = 0;
				$block_response_arr['message'] = 'SingleBrandTagged';
				$block_response_arr['SingleBrandCategory'] = $category_info_arr['SingleBrandTagged']['catname'];
			}
		}
		if((trim($category_info_arr['PizzaOutletsRest']['message']) == 'PizzaOutletsRest') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] = 'PizzaOutletsRest';
			$block_response_arr['PizzaoutletsCategory'] = $category_info_arr['PizzaOutletsRest']['catname']; 
			$block_response_arr['PizzaoutletsBrand'] = $category_info_arr['PizzaOutletsRest']['brandname']; 
		}
		
		if($landline_exist_flag != 1)
		{
			if((trim($category_info_arr['LandlineMandatory']['message']) == 'LandlineMandatory') && ($proceed_flag ==1))
			{
				$proceed_flag = 0;
				$block_response_arr['message'] = 'LandlineMandatoryTagged';
				$block_response_arr['LandlineMandatoryCategory'] = $category_info_arr['LandlineMandatory']['catname'];
			}
		}
		if((trim($category_info_arr['drySateCatRest']['message']) == 'drySateCatRest') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] = 'drySateCatRest';
			$block_response_arr['drySateRestCategory'] = $category_info_arr['drySateCatRest']['catname'];
		}
		/*if((trim($category_info_arr['ExclusiveTagged']['message']) == 'ExclusiveTagged') && ($proceed_flag ==1))
		{
			$proceed_flag = 0;
			$block_response_arr['message'] = 'ExclusiveTagged';
			$block_response_arr['ExclusiveTaggedCategory'] = $category_info_arr['ExclusiveTagged']['catname'];
		}*/
		if((trim($category_info_arr['StarRatingTagged']['message']) == 'StarRatingTagged') && ($proceed_flag ==1))
		{
			$star_rating_tagged_cat_arr = array();
			$star_rating_tagged_cat_arr = explode("|~|",$category_info_arr['StarRatingTagged']['catname']);
			if(count($star_rating_tagged_cat_arr)>1)
			{
				$proceed_flag = 0;
				$block_response_arr['message'] = 'StarRatingTagged';
				$block_response_arr['StarRatingTaggedCategory'] = $category_info_arr['StarRatingTagged']['catname'];
			}
		}
		
		 if((trim($category_info_arr['moviescategoryRestriction']['message']) == 'moviescategoryRestriction') && ($proceed_flag ==1))
		 {
			 $proceed_flag = 0;
			 $block_response_arr['message'] 					= 'moviescategoryRestriction';
			 $block_response_arr['movietagdcatid'] 			= $category_info_arr['moviescategoryRestriction']['movietagdcatid'];
			 $block_response_arr['movietagdcatname'] 		= $category_info_arr['moviescategoryRestriction']['movietagdcatname'];			
		 }
		 
		 if(($this->module == 'CS') || ($this->module == 'DE') || ($this->module == 'TME') || ($this->geniolite == 1)){
			 if((trim($category_info_arr['vetNonvetRestriction']['message']) == 'vetNonvetRestriction') && ($proceed_flag ==1))
			 {
				 $proceed_flag = 0;
				 $block_response_arr['message'] 			= 'vetNonvetRestriction';
				 $block_response_arr['vet_catid'] 			= $category_info_arr['vetNonvetRestriction']['vet_catid'];
				 $block_response_arr['vet_catname'] 		= $category_info_arr['vetNonvetRestriction']['vet_catname'];			
			 }
		}
		 
		if(count($block_response_arr)>0 && $block_response_arr['message']!='')
		{
			$resultdata['BLOCK'] = $block_response_arr;
		}
		
		$optional_response_arr = array();
		$counter = 0;
		
		if((trim($category_info_arr['PremiumTagged']['message']) == 'PremiumTagged') && ($this->module != 'ME') && ($this->module != 'TME'))
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'PremiumCategory';
			$optional_response_arr['popupmsg'.$counter]['PremiumTaggedCategory'] = $category_info_arr['PremiumTagged']['catname'];
		}
		
		if(trim($category_info_arr['PromtRatingsFlagTagged']['message']) == 'PromtRatingsFlagTagged')
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'PromtRatings';
			$optional_response_arr['popupmsg'.$counter]['PromtRatingsCategory'] = $category_info_arr['PromtRatingsFlagTagged']['catname'];
		}
		
		if((trim($category_info_arr['ExclusiveTagged']['message']) == 'ExclusiveTagged') && ($this->module == 'TME' || $this->module == 'ME'))
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'ExclTagged';
			$optional_response_arr['popupmsg'.$counter]['ExclCategory'] = $category_info_arr['ExclusiveTagged']['catname'];
		}
		
		if(trim($category_info_arr['_24_hrs_cat']['message']) == '_24_hrs_cat' && (strtoupper($this->module) == 'CS' || strtoupper($this->module) == 'TME' || strtoupper($this->module) == 'ME' || strtoupper($this->module) == 'DE'))
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = '_24hrsTagged';
			$optional_response_arr['popupmsg'.$counter]['_24hrsCategory'] = $category_info_arr['_24_hrs_cat']['catname'];
		}
		if(trim($category_info_arr['cinemaHalls']['message']) == 'cinemaHalls' && (strtoupper($this->module) == 'CS' || strtoupper($this->module) == 'TME' || strtoupper($this->module) == 'ME'  || strtoupper($this->module) == 'DE'))
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'cinemaHallsTagged';
			$optional_response_arr['popupmsg'.$counter]['cinemaHallsCategory'] = $category_info_arr['cinemaHalls']['catname'];
		}
		if(count($approved_moderate_cat_arr)>0 && $approved_moderate_cat_arr['category_name']!=''){
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'ApprovedModerateCat';
			$optional_response_arr['popupmsg'.$counter]['ApprovedModerateCat'] = $approved_moderate_cat_arr['category_name'];
		}
		
		$MissingCategoryArr = array();
		
		$restaurant_missing_catid_arr = array();
		if(trim($category_info_arr['RestaurantTagged']['message']) == 'RestaurantTagged')
		{
			if(trim($category_info_arr['RestaurantTagged']['restaurant_addinfo']['pricerange_extra_msg']) == 'AddMissingCategory')
			{
				$rest_missing_catid_arr = array();
				$rest_missing_catid_arr = explode("|~|",$category_info_arr['RestaurantTagged']['restaurant_addinfo']['missing_catid']);
				
				if(count($rest_missing_catid_arr) > 0)
				{	
					foreach($rest_missing_catid_arr as $missing_catid)
					{
						if(!in_array($missing_catid,$this->catids_arr))
						{
							$restaurant_missing_catid_arr[] = $missing_catid;
						}
					}
				}
			}
		}
		$brand_missing_catid_arr = array();
		if(trim($category_info_arr['MissingBrandGenericCategory']['message']) == 'MissingBrandGenericCategory')
		{
			$brand_missing_catid_arr = explode("|~|",$category_info_arr['MissingBrandGenericCategory']['catid']);
		}
		$MissingCategoryArr = array();
		if($this->page=='CatPreviewPage'){
			$MissingCategoryArr = $restaurant_missing_catid_arr;
		}
		else
		{
			$MissingCategoryArr = array_merge($restaurant_missing_catid_arr,$brand_missing_catid_arr);
		}
		$MissingCategoryArr = array_unique($MissingCategoryArr);
		
		if(count($MissingCategoryArr)>0)
		{		
			$missing_new_data = $this->makeMissingCatinfo($MissingCategoryArr);
			$omit_generic_cat_flag = $this->contractByPassCheck(4);
			if((count($missing_new_data)>0) && ($omit_generic_cat_flag !=1))
			{
				$counter = $counter + 1;
				$optional_response_arr['popupmsg'.$counter]['message'] = 'RestMissingCategory';
				$optional_response_arr['popupmsg'.$counter]['RestaurantMissingCategory'] 	= $missing_new_data['category_name'];
				$optional_response_arr['popupmsg'.$counter]['RestaurantMissingCatid'] 		= $missing_new_data['category_id'];
				$optional_response_arr['popupmsg'.$counter]['premium_flag'] 				= $missing_new_data['premium_flag'];
			}
		}
		
		if((trim($category_info_arr['CuisinePriceFilterMissing']['message']) == 'CuisinePriceFilterMissing') && ($this->module == 'CS' || $this->module == 'TME'))
		{
			$counter = $counter + 1;
			$optional_response_arr['popupmsg'.$counter]['message'] = 'CuisinePriceMissing';
		}
		
		if($this->module == 'TME' || $this->module == 'ME')
		{
			$restrict_cat_bypass_check_flag = $this->contractByPassCheck(3);
			$restrict_cat_bypass_check_flag = 1;
			if((trim($category_info_arr['RestrictedTagged']['message']) == 'RestrictedTagged') && ($restrict_cat_bypass_check_flag !=1))
			{
				$id_proof=0;
				$add_proof=0;
				$other_proof=0;
				$docid = $this->docid_creator();
				$vlc_document_curl_url = "http://192.168.20.102:9001/web_services/vlc.php?docid=".$docid."&city=".$this->data_city."&media=d";
				$vlc_document_data_arr = json_decode($this->curlCallGet($vlc_document_curl_url),true);
				if(count($vlc_document_data_arr)>0)
				{
					foreach($vlc_document_data_arr as $docidkey=> $docidkey_val)
					{
						foreach($docidkey_val as $docidkey_val_details)
						{
							foreach($docidkey_val_details as $last_arr)
							{
								if((intval($last_arr['delete_flag']) == 0) && ((intval($last_arr['approved']) == 0) || (intval($last_arr['approved']) == 1)))
								{
									switch(trim($last_arr['file_type']))
									{
										case 1:
											$id_proof= 1;
										break;
										case 2:
											$add_proof= 1;
										break;
										case 3 :
											$other_proof= 1;
										break;
									}
								}
							}
						}
					}
				}
				$required_document_arr = array();
				if($add_proof != 1){
					$required_document_arr[] = "Address Proof";
				}
				if($id_proof != 1){
					$required_document_arr[] = "Identity Proof";
				}
				$document_needed = 0;
				if(($add_proof != 1) || ($id_proof != 1)){
					$document_needed = 1;
				}
				if($document_needed == 1)
				{
					$counter = $counter + 1;
					$checksum = $this->getChecksum($docid);
					$contractGeninfo = $this->contractGeneralInfo();
					$optional_response_arr['popupmsg'.$counter]['message'] = 'DocumentRequired';
					$optional_response_arr['popupmsg'.$counter]['DocumentList'] = implode(", ",$required_document_arr);
					$optional_response_arr['popupmsg'.$counter]['RestrictedTaggedCategory'] = $category_info_arr['RestrictedTagged']['catname'];
					$optional_response_arr['popupmsg'.$counter]['DocumentURL'] = "http://catalog.justdial.com/catalog/index.php?type=".strtolower($this->module)."&v=0&c=0&l=0&d=1&s=0&city=".urlencode($this->data_city)."&company_name=".urlencode($this->companyname)."&docid=".$docid."&userid=".$this->ucode."&ps=".$contractGeninfo['paid']."&vcode=&checksum=".$checksum."&listedon=1";
				}
			}
		}
		
		if($this->module=='CS'){
		
			if($this->remove_catidlist!=''){
				$remove_catidlist_arr = array();
				$remove_catidlist_arr = explode(',',$this->remove_catidlist);
				$remove_catidlist_arr = array_map('trim',$remove_catidlist_arr);
				$remove_catidlist_arr = array_unique($remove_catidlist_arr);
			}
			if($this->all_catidlist_old!=''){
				$all_catidlist_arr = array();
				$all_catidlist_arr = explode(',',$this->all_catidlist_old);
				$all_catidlist_arr = array_map('trim',$all_catidlist_arr);
				$all_catidlist_arr = array_unique($all_catidlist_arr);
			}
			$sql_tag_catid	=	"SELECT tag_catid,tag_catname FROM tbl_companymaster_extradetails_shadow WHERE parentid='".$this->parentid."'";
			$res_tag_catid  = parent::execQuery($sql_tag_catid,$this->conn_iro);
			if(mysql_num_rows($res_tag_catid)>0){
				$row_tag_catid 	=	mysql_fetch_assoc($res_tag_catid);
				$tag_catid 		=	trim($row_tag_catid['tag_catid']);
				$tag_catname 	=	trim($row_tag_catid['tag_catname']);
				
				if($tag_catid!='' && $tag_catname!=''){
					if(is_array($all_catidlist_arr)){
						$exist_temp_cat_flag =	in_array($tag_catid,$all_catidlist_arr);
					}
					if(is_array($remove_catidlist_arr)){
						$remove_tagcat_flag =	in_array($tag_catid,$remove_catidlist_arr);
					}
					if($exist_temp_cat_flag && $remove_tagcat_flag){
						/*$sql_upd_shadow =	"UPDATE tbl_companymaster_extradetails_shadow SET tag_catid='',tag_catname='' WHERE parentid='".$this->parentid."' ";
						$res_upd_shadow = parent::execQuery($sql_upd_shadow,$this->conn_iro);*/
						$counter = $counter + 1;
						$optional_response_arr['popupmsg'.$counter]['message'] = 'PrimaryTagCat';
						$optional_response_arr['popupmsg'.$counter]['PrimaryTagCat'] = $tag_catname;
						$optional_response_arr['popupmsg'.$counter]['PrimaryTagCatid'] = $tag_catid;				
					}
				}
			}
		}

		$optional_response_arr['popupcount'] = $counter;
		$resultdata['CANPROCEED'] = $optional_response_arr;
		return $resultdata;
	}
	function checkApprovedModerationCat(){
		$approved_moderate_cat_arr =array();
		if($this->remove_catidlist!=''){
			$remove_catidlist_arr = array();
			$remove_catidlist_arr = explode(',',$this->remove_catidlist);
			$remove_catidlist_arr = array_map('trim',$remove_catidlist_arr);
			$remove_catidlist_arr = array_unique($remove_catidlist_arr);
			if(count($remove_catidlist_arr)>0){
				if(count($remove_catidlist_arr)>0){
					$sql_remove_str = implode("','",$remove_catidlist_arr);
				}
				if($sql_remove_str!=''){
					$sql_check_cat = "SELECT catids FROM tbl_premium_categories_audit WHERE catids IN ('".$sql_remove_str."') AND approval_status=1 AND paid_status=0 AND parentid ='".$this->parentid."' ";
					
					$res_check_cat = parent::execQuery($sql_check_cat,$this->conn_local);
					if($res_check_cat && parent::numRows($res_check_cat)>0){
						while ($row_check_cat 		= parent::fetchData($res_check_cat)){
							$catids_arr[]	=	trim($row_check_cat['catids']);
						}				
					}
				}				
				if(count($catids_arr)>0){
					$approved_moderate_cat_arr	=	$this->getCategoryDetails($catids_arr);						
				}
			}
		}
		return $approved_moderate_cat_arr;
	}
	function getCategoryTaggingDetails()
	{
		if(count($this->catids_arr)>0)
		{	
			$catid_List = implode(",",$this->catids_arr);
			/*$sqlCategoryInfo = "SELECT catid,category_name,national_catid,category_type,category_addon,brand_name,service_name,master_brand_name,display_product_flag,associate_national_catid,promt_ratings_flag,
			rest_price_range,premium_flag,group_id,local_listing_flag,is_restricted,total_results,category_verticals,misc_cat_flag,template_id,cuisine_tag FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_List.")";*/
			//$resCategoryInfo 	= parent::execQuery($sqlCategoryInfo, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='category_restriction_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name,national_catid,category_type,category_addon,brand_name,service_name,master_brand_name,display_product_flag,associate_national_catid,promt_ratings_flag,rest_price_range,premium_flag,group_id,local_listing_flag,is_restricted,total_results,category_verticals,misc_cat_flag,template_id,cuisine_tag';

			$where_arr  	=	array();
			$where_arr['catid']			= $catid_List;		
			$cat_params['where']		= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				$results_array = array();
				$results_array['error_code'] = 0;
				
				$pure_veg_rest_tag_flag = 0;
				$pure_veg_rest_tag_catid_arr 	= array();
				$pure_veg_rest_tag_catname_arr 	= array();
				
				$nonveg_rest_tag_flag = 0;
				$nonveg_rest_tag_catid_arr 	= array();
				$nonveg_rest_tag_catname_arr 	= array();
				
				$paid_not_allowed_flag = 0;
				$paidnotallowed_catid_arr 	= array();
				$paidnotallowed_catname_arr = array();
				
				$brandname_exist_flag = 0;
				$brand_servicename_info_arr      = array();
				
				$single_brand_tag_flag = 0;
				$single_brand_tag_catid_arr 	= array();
				$single_brand_tag_catname_arr 	= array();
				$restrict_single_brand_name_arr = array();
				
				$five_star_hotel_tag_flag = 0;
				$five_star_hotel_catid_arr = array();
				$five_star_hotel_catname_arr = array();
				
				$landline_mand_tag_flag = 0;
				$landline_mand_catid_arr = array();
				$landline_mand_catname_arr = array();
				
				$restaurant_tag_cat_flag = 0;
				$restaurant_tag_catid_arr = array();
				$restaurant_tag_catname_arr = array();
				$restaurant_additional_info_arr = array();
				
				$premium_tag_cat_flag = 0;
				$premium_tag_catid_arr = array();
				$premium_tag_catname_arr = array();
				
				$pharmacy_tag_cat_flag 		= 0;
				$pharmacy_tag_catid_arr   	= array();
				$pharmacy_tag_catname_arr 	= array();
				
				$dochosp_tag_cat_flag 	= 0;
				$dochosp_tag_catid_arr 	= array();
				$dochosp_tag_catname_arr = array();
				
				$exclusive_tag_cat_flag = 0;
				$exclusive_tag_catid_arr = array();
				$exclusive_tag_catname_arr = array();
				
				$star_rating_catid_arr  = array();
				$star_rating_catname_arr = array();
				
				$star_hotels_catid_arr = array();
				$star_hotels_catname_arr = array();
				
				$star_banquets_catid_arr = array();
				$star_banquets_catname_arr = array();
				
				$star_resorts_catid_arr = array();
				$star_resorts_catname_arr = array();
				
				$local_listing_cat_flag = 0;
				$local_listing_catid_arr = array();
				$local_listing_catname_arr = array();
				
				$authorisation_cat_flag = 0;
				$authorisation_catid_arr = array();
				$authorisation_catname_arr = array();
				
				$liquor_tag_flag = 0;
				$liquor_catid_arr = array();
				$liquor_catname_arr = array();
				
				$restricted_cat_flag = 0;
				$restricted_catid_arr = array();
				$restricted_catname_arr = array();
				
				$category_name_info_arr= array();
				$service_name_info_arr = array();
				
				$hotel_tag_cat_flag = 0;
				$hotel_tag_catid_arr = array();
				$hotel_tag_catname_arr = array();
				
				$pizza_outlets_catname_arr = array();
				$pizza_outlets_brand_arr = array();
				
				$cinema_natcatid_arr  =  array(10099752,12143850);
			
				$cinema_halls_flag = 0;
				$movie_tagged_catids_arr = array();
				$movie_tagged_catnames_arr = array();
				
				$vet_cat_tag_flag 	= 0;
				$vet_catid_arr[]	= array();
				$vet_catname_arr[]	= array();
				
				$cuisine_tagged_flag = 0;
				$cuisine_tagged_info_arr	= array();
				
				$price_filter_cat = 0;				
				
				foreach($cat_res_arr['results'] as $key=>$row_category)
				{
					$catid 						= trim($row_category['catid']);
					$national_catid 			= trim($row_category['national_catid']);					
					$category_name 				= trim($row_category['category_name']);
					$category_type 				= trim($row_category['category_type']);
					$category_addon				= trim($row_category['category_addon']);
					$brand_name                 = trim($row_category['brand_name']);
					$service_name               = trim($row_category['service_name']);
					$master_brand_name 			= trim($row_category['master_brand_name']);
					$display_product_flag 		= trim($row_category['display_product_flag']);
					$associate_national_catid 	= trim($row_category['associate_national_catid']);
					$categoryname_display 		= trim($row_category['rest_price_range']);
					$premium_flag 				= trim($row_category['premium_flag']);
					$group_id 					= trim($row_category['group_id']);
					$local_listing_flag 		= trim($row_category['local_listing_flag']);
					$is_restricted 				= trim($row_category['is_restricted']);
					$total_results 				= trim($row_category['total_results']);
					$category_verticals 		= trim($row_category['category_verticals']);
					$promt_ratings_flag 		= trim($row_category['promt_ratings_flag']);
					$misc_cat_flag 				= trim($row_category['misc_cat_flag']);
					$template_id 				= trim($row_category['template_id']);
					$cuisine_tag 				= intval($row_category['cuisine_tag']);
					
					
					if(((int)$promt_ratings_flag & 16) == 16) // Non Ratable category
					{
						$promt_ratings_flag_tag_flag = 1;
						$promt_ratings_flag_tag_catid_arr[] 		= $catid;
						$promt_ratings_flag_tag_catname_arr[] 		= $category_name;
					}
					
					if(((int)$category_type & 4096) == 4096) // Pure Veg Restaurant Tagged Category
					{
						$pure_veg_rest_tag_flag = 1;
						$pure_veg_rest_tag_catid_arr[] 		= $catid;
						$pure_veg_rest_tag_catname_arr[] 	= $category_name;
					}
					
					if(((int)$category_type & 65536) == 65536) // Non Veg Restaurant Tagged Category
					{
						$nonveg_rest_tag_flag = 1;
						$nonveg_rest_tag_catid_arr[] 		= $catid;
						$nonveg_rest_tag_catname_arr[] 		= $category_name;
					}
					
					if(((int)$category_type & 32768) == 32768) // Restaurants In 5 Star Hotels Tagged Category
					{
						$five_star_hotel_tag_flag = 1;
						$five_star_hotel_catid_arr[] 	= $catid;
						$five_star_hotel_catname_arr[] 	= $category_name;
					}
					if(((int)$category_type & 1024) == 1024) // Paid Not Allowed Tagged Category
					{
						$paid_not_allowed_flag = 1;
						$paidnotallowed_catid_arr[] 	= $catid;
						$paidnotallowed_catname_arr[] 	= $category_name;
					}
					
					if(((int)$misc_cat_flag & 256) == 256) // 24 hours tagged category
					{
						$_24_hrs_cat			 = 1;
						$_24_hrs_cat_id_arr[] 	 = $catid;
						$_24_hrs_cat_name_arr[]  = $category_name;
					}
					
					if($national_catid == 10099752) // cinema halls tagged category
					{
						$cinema_hall		    = 1;
						$cinema_hall_id_arr[] 	= $catid;
						$cinema_hall_name_arr[]	= $category_name;
					}
					
					if((!empty($brand_name)) && (!empty($service_name)) && (((int)$category_verticals & 1) != 1))
					{
						$brandname_exist_flag = 1;
						$category_name_info_arr[$catid]=$category_name;
						$service_name_info_arr[$catid]=$service_name;	
					}
					
					if(((((int)$category_type & 8192) == 8192) && !empty($master_brand_name)) || (strtolower($service_name) == 'pizza outlets' && !empty($brand_name))) // Restrict For Single Brand
					{
						$single_brand_tag_flag = 1;
						$single_brand_tag_catid_arr[] 	= $catid;
						$single_brand_tag_catname_arr[] = $category_name;
						if(strtolower($service_name) == 'pizza outlets')
						{						
							$restrict_single_brand_name_arr[] = $brand_name;
							$pizza_outlets_catname_arr[] = $category_name;
							$pizza_outlets_brand_arr[] = $brand_name;
						}					
						else
						{						
							$restrict_single_brand_name_arr[] = $master_brand_name;						
						}
					}
					
					if(((int)$category_addon & 4) == 4) // Landline Mandatory Tagged Category
					{
						$landline_mand_tag_flag = 1;
						$landline_mand_catid_arr[] 		= $catid;
						$landline_mand_catname_arr[] 	= $category_name;
					}
					
					if(((int)$display_product_flag & 16) == 16) // Liquor Tagged Category
					{
						$liquor_tag_flag = 1;
						$liquor_catid_arr[] 	= $catid;
						$liquor_catname_arr[] 	= $category_name;
					}
					
					if($this->module =='TME' || $this->module =='ME')
					{
						if((((int)$category_type & 16) == 16) || ($total_results == 1)) // Exclusive Tagged Category
						{
							$exclusive_tag_cat_flag = 1;
							$exclusive_tag_catid_arr[] 		= $catid;
							$exclusive_tag_catname_arr[] 	= $category_name;
						}
					}
					else
					{
						if(((int)$category_type & 16) == 16) // Exclusive Tagged Category
						{
							$exclusive_tag_cat_flag = 1;
							$exclusive_tag_catid_arr[] 		= $catid;
							$exclusive_tag_catname_arr[] 	= $category_name;
						}
					}
					if((((int)$display_product_flag & 4294967296) == 4294967296) && ($group_id == 1)) // Star Hotels Rating Category
					{
						$star_hotels_catid_arr[] 	= $catid;
						$star_hotels_catname_arr[] 	= $category_name;
					}
					
					if((((int)$display_product_flag & 268435456) == 268435456) && ($group_id == 2)) // Star Banquets Rating Category
					{
						$star_banquets_catid_arr[] 	= $catid;
						$star_banquets_catname_arr[] 	= $category_name;
					}
					
					if((((int)$display_product_flag & 4294967296) == 4294967296) && ($group_id == 3)) // Star Resorts Rating Category
					{
						$star_resorts_catid_arr[] 	= $catid;
						$star_resorts_catname_arr[] 	= $category_name;
					}
					
					if($local_listing_flag == 1)
					{
						$local_listing_cat_flag = 1;
						$local_listing_catid_arr[] 		= $catid;
						$local_listing_catname_arr[] 	= $category_name;
					}
					
					if(stristr($category_name,'(Authorised)') || stristr($category_name,'(Authorized)'))
					{
						$authorisation_cat_flag = 1;
						$authorisation_catid_arr[] 		= $catid;
						$authorisation_catname_arr[] 	= $category_name;
					}
					
					if($is_restricted == 1)
					{
						$restricted_cat_flag = 1;
						$restricted_catid_arr[] 		= $catid;
						$restricted_catname_arr[] 		= $category_name;
					}
					
					if(((int)$display_product_flag & 134217728) == 134217728) // Restaurant Tagged Category
					{
						$restaurant_tag_cat_flag = 1;
						$restaurant_tag_catid_arr[] 	= $catid;
						$restaurant_tag_catname_arr[] 	= $category_name;
						
						if(intval($associate_national_catid)>0 && !empty($categoryname_display))
						{
							$restaurant_additional_info_arr[$catid]['assoc'] = $associate_national_catid;
							$restaurant_additional_info_arr[$catid]['prange'] = $categoryname_display;
							$restaurant_additional_info_arr[$catid]['misc'] = $misc_cat_flag;
							$restaurant_additional_info_arr[$catid]['cuis'] = $cuisine_tag;
							
						}
						if(!empty($categoryname_display)){
							$price_filter_cat = 1;
						}
						
					}
					if((((int)$display_product_flag & 4294967296) == 4294967296) && (((int)$display_product_flag & 134217728) != 134217728)) // Hotel Tagged Category
					{
						$hotel_tag_cat_flag = 1;
						$hotel_tag_catid_arr[] 		= $catid;
						$hotel_tag_catname_arr[] 	= $category_name;
					}
					if($premium_flag == 1)
					{
						$premium_tag_cat_flag = 1;
						$premium_tag_catid_arr[] 	= $catid;
						$premium_tag_catname_arr[] 	= $category_name;
					}
					
					if((((int)$display_product_flag & 64) == 64) && (((int)$display_product_flag & 4) != 4) && (((int)$display_product_flag & 8) != 8)) // Pharmacy Tagged Category
					{
						$pharmacy_tag_cat_flag 	= 1;
						$pharmacy_catid_arr[] 	= $catid;
						$pharmacy_catname_arr[] = $category_name;
					}
					if((((int)$display_product_flag & 4) == 4) || (((int)$display_product_flag & 8) == 8)) // Doctor / Hospital Tagged Category
					{
						$dochosp_tag_cat_flag 	= 1;
						$dochosp_catid_arr[] 	= $catid;
						$dochosp_catname_arr[] 	= $category_name;
					}
					$vet_cat_template_arr  = array('99','1216');
					$template_id_arr = array();
					if($template_id!=''){
						$template_id_arr = explode(",",$template_id);
					}
					if(count($template_id_arr)>0){
						foreach ($template_id_arr as $key => $value) {
							if(in_array($value,$vet_cat_template_arr)){
								$vet_cat_tag_flag= 1;
								$vet_catid_arr[] 	= $catid;
								$vet_catname_arr[] 	= $category_name;
							}
						}
					}
					/* if($national_catid == $cinema_natcatid){
						$cinema_halls_flag = 1;
					} */					
					if(in_array($national_catid,$cinema_natcatid_arr)){
						$cinema_halls_flag = 1;
					}
					if((((int)$display_product_flag & 1073741824)==1073741824) && (!in_array($national_catid, $cinema_natcatid_arr))){						
						$movie_tagged_catids_arr[] 		= $catid;
						$movie_tagged_catnames_arr[] 	= $category_name;
					}
					if(($cuisine_tag == 1) && (((int)$misc_cat_flag & 512) != 512)){
						$cuisine_tagged_flag = 1;
						$cuisine_tagged_info_arr[$catid]['assoc'] 	= $associate_national_catid;
						$cuisine_tagged_info_arr[$catid]['misc'] 	= $misc_cat_flag;
						$cuisine_tagged_info_arr[$catid]['cnm'] 	= $category_name;
					}
				}
				
				if($cuisine_tagged_flag == 1 && $price_filter_cat !=1){
					$message = 'CuisinePriceFilterMissing';
					$results_array[$message]['message'] 				= $message;
				}
				
				$vet_final_arr = array();
				if(is_array($vet_catid_arr)){
					$vet_catid_arr 		=	array_filter(array_unique($vet_catid_arr));
				}
				if(is_array($dochosp_catid_arr)){
					$dochosp_catid_arr 	=	array_filter(array_unique($dochosp_catid_arr));
					$vet_final_arr =	array_diff($dochosp_catid_arr,$vet_catid_arr);
				}
				else{
					$vet_final_arr = $vet_catid_arr;
				}
				$vat_cat_block_flag	=	$this->contractByPassCheck(8);
				if(count($vet_final_arr)>0 && $vat_cat_block_flag!=1){
					if($vet_cat_tag_flag==1 && $dochosp_tag_cat_flag ==1){
						$vet_catid_arr 		=	array_filter(array_unique($vet_catid_arr));
						$vet_catname_arr 	=	array_filter(array_unique($vet_catname_arr));
						$message = 'vetNonvetRestriction';
						$results_array[$message]['message'] 				= $message;
						$results_array[$message]['vet_catid'] 			= implode("|~|",$vet_catid_arr);
						$results_array[$message]['vet_catname'] 		= implode("|~|",$vet_catname_arr);
					}
				}
				if((count($movie_tagged_catids_arr)>0) && ($cinema_halls_flag !=1)){
					//$sql_dummy_catid = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE national_catid=10283919";
					//$res_dummy_catid = parent::execQuery($sql_dummy_catid,$this->conn_catmaster);
					$cat_params = array();
					$cat_params['page'] ='category_restriction_class';
					$cat_params['data_city'] 	= $this->data_city;			
					$cat_params['return']		= 'catid';

					$where_arr  	=	array();
					$where_arr['national_catid']	= '10283919';		
					$cat_params['where']			= json_encode($where_arr);

					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
						$row_dummy_catid	=	$cat_res_arr['results']['0'];
						$cinema_dummy_catid	=	$row_dummy_catid['catid'];
					}
					$cinema_dummy_flag =0;
					if($cinema_dummy_catid!='' && is_array($movie_tagged_catids_arr)){
						if(in_array($cinema_dummy_catid,$movie_tagged_catids_arr)){
							$cinema_dummy_flag =1;
						}
					}
					if($cinema_dummy_flag!=1){
						$message = 'moviescategoryRestriction';
						$results_array[$message]['message'] 				= $message;
						$results_array[$message]['movietagdcatid'] 			= implode("|~|",$movie_tagged_catids_arr);
						$results_array[$message]['movietagdcatname'] 		= implode("|~|",$movie_tagged_catnames_arr);		
					}
				}
			
				if(count($pizza_outlets_catname_arr)>0){
					$pizza_outlets_catname_arr = array_unique(array_filter($pizza_outlets_catname_arr));
					$pizza_outlets_brand_arr = array_unique(array_filter($pizza_outlets_brand_arr));
				}
				if($restaurant_tag_cat_flag == 1 && $hotel_tag_cat_flag == 1)
				{
					$message = 'HotelRestRestriction';
					$results_array[$message]['message'] 				= $message;
					$results_array[$message]['HotelTaggedCatid'] 		= implode("|~|",$hotel_tag_catid_arr);
					$results_array[$message]['HotelTaggedCategory'] 	= implode("|~|",$hotel_tag_catname_arr);
					$results_array[$message]['RestTaggedCatid'] 		= implode("|~|",$restaurant_tag_catid_arr);
					$results_array[$message]['RestTaggedCategory'] 		= implode("|~|",$restaurant_tag_catname_arr);
				}
				if($pharmacy_tag_cat_flag == 1 && $dochosp_tag_cat_flag == 1)
				{
					$message = 'PharmCatRestriction';
					$results_array[$message]['message'] 				= $message;
					$results_array[$message]['PharmTaggedCatid'] 		= implode("|~|",$pharmacy_catid_arr);
					$results_array[$message]['PharmTaggedCategory'] 	= implode("|~|",$pharmacy_catname_arr);
					$results_array[$message]['DocHospTaggedCatid'] 		= implode("|~|",$dochosp_catid_arr);
					$results_array[$message]['DocHospTaggedCategory'] 	= implode("|~|",$dochosp_catname_arr);
				} 
				if($pure_veg_rest_tag_flag == 1 && $nonveg_rest_tag_flag == 1){ // Pure veg and Non veg restriction
					$message 										= 'PurevegNonvegRestriction';
					$results_array[$message]['message'] 			= $message;
					$results_array[$message]['PureVegCatid'] 		= implode("|~|",$pure_veg_rest_tag_catid_arr);
					$results_array[$message]['PureVegCategory'] 	= implode("|~|",$pure_veg_rest_tag_catname_arr);
					$results_array[$message]['NonVegCatid'] 		= implode("|~|",$nonveg_rest_tag_catid_arr);
					$results_array[$message]['NonVegCategory'] 		= implode("|~|",$nonveg_rest_tag_catname_arr);
				}
				if($five_star_hotel_tag_flag == 1)
				{
					$home_deliv_rest_catid_arr = array();
					$home_deliv_rest_catname_arr = array();   // 11054242 - National Catid Of Home Delivery Restaurants (P) 
					//$sqlHomeDelivRestChildCat = "SELECT DISTINCT catid, category_name FROM tbl_categorymaster_parentinfo WHERE catid IN (".$catid_List.") AND parent_national_catid ='11054242'";
					//$resHomeDelivRestChildCat 	= parent::execQuery($sqlHomeDelivRestChildCat, $this->conn_catmaster);

					$cat_params = array();
					$cat_params['page'] 	='category_restriction_class';
					$cat_params['q_type'] 	='parentinfo';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'catid,category_name';	

					$where_arr  	=	array();			
					$where_arr['catid']					= $catid_List;
					$where_arr['parent_national_catid']	= '11054242';		
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}

					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
					{
						foreach($cat_res_arr['results'] as $key =>$row_homedelivrestchildcat)
						{
							$home_deliv_rest_catid_arr[] 	= $row_homedelivrestchildcat['catid'];
							$home_deliv_rest_catname_arr[] 	= $row_homedelivrestchildcat['category_name'];
						}
					}
					if((count($home_deliv_rest_catid_arr) >0) && (count($home_deliv_rest_catname_arr) >0))
					{
						$message = '5StarAndHomeDelivery';
						$results_array[$message]['message'] 		= $message;
						$results_array[$message]['FiveStarHotelCatid'] 		= implode("|~|",$five_star_hotel_catid_arr);
						$results_array[$message]['FiveStarHotelCategory'] 	= implode("|~|",$five_star_hotel_catname_arr);
						$results_array[$message]['HomeDeliveryRestCatid'] 	= implode("|~|",$home_deliv_rest_catid_arr);
						$results_array[$message]['HomeDeliveryRestaurant'] 	= implode("|~|",$home_deliv_rest_catname_arr);
					}
				}
				if($paid_not_allowed_flag == 1)
				{
					$message = 'PaidNotAllowed';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$paidnotallowed_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$paidnotallowed_catname_arr);
				}
				
				if($_24_hrs_cat == 1)
				{
					$message = '_24_hrs_cat';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$_24_hrs_cat_id_arr);
					$results_array[$message]['catname'] = implode("|~|",$_24_hrs_cat_name_arr);
				}
				
				if($cinema_hall == 1)
				{
					$message = 'cinemaHalls';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$cinema_hall_id_arr);
					$results_array[$message]['catname'] = implode("|~|",$cinema_hall_name_arr);
				}
				
				if(($brandname_exist_flag == 1) && ($this->exclusion_flag != 1))
				{
					//$brand_servicename_info_arr = $this->removeBrandExclusionCategories($brand_servicename_info_arr);
					$brand_servicename_info_arr=$this->removeAuthorisedBrandRepairAndServiceCat($category_name_info_arr,$service_name_info_arr);
					$missingBrandCatArr = $this->getMissingBrandGenericCatinfo($brand_servicename_info_arr);
					if(count($missingBrandCatArr)>0)
					{
						$message = 'MissingBrandGenericCategory';
						$results_array[$message]['message'] 	 = $message;
						$results_array[$message]['catid'] 		 = $missingBrandCatArr['catid'];
						$results_array[$message]['catname'] 	 = $missingBrandCatArr['catname'];
						$results_array[$message]['premium_flag'] = $missingBrandCatArr['premium_flag'];
					}
				}
				
				if($single_brand_tag_flag == 1)
				{
					$restrict_single_brand_name_arr = array_filter($restrict_single_brand_name_arr);
					$restrict_single_brand_name_arr = array_unique($restrict_single_brand_name_arr);
					if(count($restrict_single_brand_name_arr) > 1)
					{
						$message = 'SingleBrandTagged';
						$results_array[$message]['message'] = $message;
						$results_array[$message]['catid'] 	= implode("|~|",$single_brand_tag_catid_arr);
						$results_array[$message]['catname'] = implode("|~|",$single_brand_tag_catname_arr);
					}
					else if((count($pizza_outlets_catname_arr) == 1) && (count($pizza_outlets_brand_arr) == 1))
					{
						$pizza_outlets_brand = $pizza_outlets_brand_arr[0];
						$companyname_new = preg_replace('/[^A-Za-z0-9\-\s\'\&\.]/', '', $this->companyname);
						if((stripos($companyname_new, $pizza_outlets_brand) === false) && (strlen($companyname_new) > 0))
						{
							$message = 'PizzaOutletsRest';
							$results_array[$message]['message'] = $message;	
							$results_array[$message]['catname']		= $pizza_outlets_catname_arr[0];	
							$results_array[$message]['brandname']	= $pizza_outlets_brand_arr[0];
						}
					}
				}
				if($landline_mand_tag_flag == 1)
				{
					$message = 'LandlineMandatory';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$landline_mand_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$landline_mand_catname_arr);
				}
				if($liquor_tag_flag == 1)
				{
					$dry_state_flag = $this->dryStateInfo();
					if($dry_state_flag == 1)
					{
						$message = 'drySateCatRest';
						$results_array[$message]['message'] = $message;
						$results_array[$message]['catid'] 	= implode("|~|",$liquor_catid_arr);
						$results_array[$message]['catname'] = implode("|~|",$liquor_catname_arr);
					}
				}
				if($exclusive_tag_cat_flag == 1)
				{
					$message = 'ExclusiveTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$exclusive_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$exclusive_tag_catname_arr);
				}
				
				if(count($star_hotels_catid_arr) > 1 || count($star_banquets_catid_arr) > 1 || count($star_resorts_catid_arr) > 1)
				{
					$star_rating_catid_arr  	= array_merge($star_hotels_catid_arr,$star_banquets_catid_arr,$star_resorts_catid_arr);
					$star_rating_catname_arr  	= array_merge($star_hotels_catname_arr,$star_banquets_catname_arr,$star_resorts_catname_arr);
					$message = 'StarRatingTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$star_rating_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$star_rating_catname_arr);
				}else{
					$star_rating_catid_arr  	= array_merge($star_hotels_catid_arr,$star_banquets_catid_arr,$star_resorts_catid_arr);
					$star_rating_catname_arr = array_merge($star_hotels_catname_arr,$star_banquets_catname_arr,$star_resorts_catname_arr);
					if(count($star_rating_catname_arr)>0){
						$star_rating_num_arr = array();
						foreach($star_rating_catname_arr as $star_catname){
							$star_rating_num = preg_replace('/[^1-9]/', '', $star_catname);
							$star_rating_num_arr[] = $star_rating_num;
						}
						$star_rating_num_arr = array_unique(array_filter($star_rating_num_arr));
						if(count($star_rating_num_arr)>1){
							$message = 'StarRatingTagged';
							$results_array[$message]['message'] = $message;
							$results_array[$message]['catid'] 	= implode("|~|",$star_rating_catid_arr);
							$results_array[$message]['catname'] = implode("|~|",$star_rating_catname_arr);
						}
					}
				}
						
				if($local_listing_cat_flag == 1)
				{
					$message = 'LocalListingTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$local_listing_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$local_listing_catname_arr);
				}		
				
				if($authorisation_cat_flag == 1)
				{
					$message = 'AuthorisedTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$authorisation_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$authorisation_catname_arr);
				}
				
				if($restricted_cat_flag == 1)
				{
					$message = 'RestrictedTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$restricted_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$restricted_catname_arr);
				}
				
				
				
				
				if($pure_veg_rest_tag_flag == 1)
				{
					$message = 'PureVegTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$pure_veg_rest_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$pure_veg_rest_tag_catname_arr);
				}
				if($nonveg_rest_tag_flag == 1)
				{
					$message = 'NonVegTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$nonveg_rest_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$nonveg_rest_tag_catname_arr);
				}
				if($premium_tag_cat_flag == 1)
				{
					$message = 'PremiumTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$premium_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$premium_tag_catname_arr);
				}
				
				if($promt_ratings_flag_tag_flag == 1)
				{
					$message = 'PromtRatingsFlagTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['catid'] 	= implode("|~|",$promt_ratings_flag_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$promt_ratings_flag_tag_catname_arr);
				}
				
				/**-------------------Restaurant Price Filter Starts---------------------------------------**/
				
				if(($restaurant_tag_cat_flag == 1) && ($this->exclusion_flag != 1))
				{
					$message = 'RestaurantTagged';
					$results_array[$message]['message'] = $message;
					$results_array[$message]['count'] 	= count($restaurant_tag_catid_arr);
					$results_array[$message]['catid'] 	= implode("|~|",$restaurant_tag_catid_arr);
					$results_array[$message]['catname'] = implode("|~|",$restaurant_tag_catname_arr);
					
					if(count($restaurant_additional_info_arr)>0)
					{
						
						$pricerange_value_arr = array();
						$pricefilter_catid_arr = array();
							
						
							$rest_price_range_arr = array();
							foreach($restaurant_additional_info_arr as $catid => $catinfo_arr)
							{
								$catname_display_str = $catinfo_arr['prange'];
								$catname_display_str = preg_replace('/\s+/', '', $catname_display_str); // Removing Space From categoryname_display Field
								$rest_price_range_arr[$catid] = $catname_display_str;
							}
							$rest_price_range_arr = array_map('trim',$rest_price_range_arr);
							$rest_price_range_arr = array_filter($rest_price_range_arr);
							$rest_price_range_arr = array_map('strtolower', $rest_price_range_arr);
							
							$different_pricerange_value_arr = array('inexpensive','moderate','expensive','veryexpensive');
							if(count($rest_price_range_arr)>0)
							{
								foreach($rest_price_range_arr as $catid => $range_value)
								{
									if(in_array($range_value,$different_pricerange_value_arr))
									{
										$pricefilter_catid_arr[] = $catid;
										$pricerange_value_arr[]  = $range_value;
									}
								}
							}
							$pricerange_value_arr = array_unique(array_filter($pricerange_value_arr));
						
						$rest_price_err_flag = 0;						
						if(count($pricerange_value_arr)>0)
						{
							if(count($pricerange_value_arr)>2)
							{
								$range_info_arr = array();
								$rest_price_err_flag = 1;
								$range_info_arr['pricerange_extra_msg'] = 'MoreThanTwoPriceFilterExist';								
								$pricerange_catname_info = $this->getCategoryDetails($pricefilter_catid_arr,0);								
								$range_info_arr['pricerange'] 			= implode("|~|",$pricerange_value_arr);
								$range_info_arr['pricerange_catid'] 	= implode("|~|",$pricefilter_catid_arr);
								$range_info_arr['pricerange_catname'] 	= $pricerange_catname_info['category_name'];
							}
							else
							{
								//if($this->module=='CS'){
									$price_filter_res = $this->checkPriceFilter($rest_price_range_arr);									
									$price_cat_arr = array();
									if(is_array($rest_price_range_arr)){
										$price_cat_arr = array_keys($rest_price_range_arr);
										$price_cat_arr = array_filter(array_unique($price_cat_arr));
									}								
									$pricerange_catname_info = $this->getCategoryDetails($price_cat_arr,0);
									$range_info_arr = array();
									$range_info_arr['pricerange'] 			= $price_filter_res['pricerange_value'];
									$range_info_arr['pricerange_catid'] 	= $price_filter_res['pricefilter_catid'];
									$range_info_arr['pricerange_catname'] 	= $pricerange_catname_info['category_name'];
									//echo "<pre>";print_($price_filter_res);
									if($price_filter_res['errorCode']==1)
									{
										$rest_price_err_flag = 1;
										if($price_filter_res['msg']=='PRICE_ERR'){
											$range_info_arr['pricerange_extra_msg'] = 'PriceFiltersRestriction';
										}
									}
								//}						
							}							
						}
						
						if($rest_price_err_flag!=1){							
							$missing_cat_arr = $this->getRestMissingCategories($restaurant_additional_info_arr,$pricerange_value_arr,$cuisine_tagged_info_arr);
							
							$missing_cat_cnt = count($missing_cat_arr);
							if($missing_cat_cnt > 0)
							{
								$missing_category_info = $this->getCategoryDetails($missing_cat_arr,1);
								$range_info_arr['pricerange_extra_msg'] 	= 'AddMissingCategory';
								$range_info_arr['missing_catid'] 			= implode("|~|",$missing_cat_arr);
								$range_info_arr['missing_catname'] 			= $missing_category_info['category_name'];
								$range_info_arr['missing_national_catid'] 	= $missing_category_info['national_catid'];
								$range_info_arr['pricerange_premium_flag'] 	= $missing_category_info['premium_flag'];
							}
						}
						
					}
					else if($cuisine_tagged_flag == 1) {
						$rest_catid_info_arr = array();
						$rest_catid_info_arr = $this->getRestaurantCatidInfo();						
						$restaurant_catid = $rest_catid_info_arr['cid'];
						$restaurant_associate_national_catid = $rest_catid_info_arr['cname'];
						if((intval($restaurant_catid)>0) && (!in_array($restaurant_catid,$this->catids_arr)))
						{
							$missing_catid_arr[] = $restaurant_catid;  // Adding Restaurant Catid
						}						
						if(count($missing_catid_arr) > 0){
							$missing_category_info = $this->getCategoryDetails($missing_catid_arr,1);
							$range_info_arr['pricerange_extra_msg'] 	= 'AddMissingCategory';
							$range_info_arr['missing_catid'] 			= implode("|~|",$missing_catid_arr);
							$range_info_arr['missing_catname'] 			= $missing_category_info['category_name'];
							$range_info_arr['missing_national_catid'] 	= $missing_category_info['national_catid'];
							$range_info_arr['pricerange_premium_flag'] 	= $missing_category_info['premium_flag'];
						}
					}
					$results_array[$message]['restaurant_addinfo']	= $range_info_arr;
				}
				/**-------------------Restaurant Price Filter Ends---------------------------------------**/
				return $results_array;
			}
			else
			{
				$message = "No Result Found for the given catid list in category master table";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
		else
		{
			$message = "Catid List is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
	}
	function removeAuthorisedBrandRepairAndServiceCat($category_name_info_arr,$servicename_info_arr)
	{
		$auth_cat_associated_catname_arr =array();
		$brand_servicename_info_arr = array();

		foreach ($category_name_info_arr as $catid => $category_name) {
				if (stripos($category_name, 'Repair & Service') !== false || stripos($category_name, 'Repairs & Service') !== false || stripos($category_name, 'Repair & Services') !== false || stripos($category_name, 'Repairs & Services') !== false || stripos($category_name, 'Repair and Service') !== false || stripos($category_name, 'Repairs and Service') !== false || stripos($category_name, 'Repair and Services') !== false || stripos($category_name, 'Repairs and Services') !== false )
				{
					if(stripos($category_name, 'Authorized') !== false || stripos($category_name, 'Authorised') !== false )
					{
						unset($servicename_info_arr[$catid]);
						//echo $category_name."==========";
						$new_cat_name =str_ireplace('(Authorized)','',$category_name);
						$new_cat_name =str_ireplace('(Authorised)','',$category_name);

						$auth_cat_associated_catname_arr[$catid]=$new_cat_name;
					}		
				}		
		}
		if(count($auth_cat_associated_catname_arr)>0){
			foreach ($auth_cat_associated_catname_arr as $catid => $new_category_name) {
				if(($catid = array_search(trim($new_category_name), $category_name_info_arr))!==false )
				{
					unset($servicename_info_arr[$catid]);
				}
			}
		}
		return $servicename_info_arr;
	}
	function getMissingBrandGenericCatinfo($service_name_arr)
	{
		$missing_brand_catinfo_arr = array();
		$missing_brand_catid_arr = array();
		$missing_brand_catnm_arr = array();
		$missing_brand_cat_flag = 0;
		$premium_cat_flag = 0;
		if(count($service_name_arr)>0)
		{
			$service_name_str = implode("','",$service_name_arr);
			//$sqlMissingBrandCat = "SELECT catid,category_name,premium_flag FROM tbl_categorymaster_generalinfo WHERE category_name IN ('".$service_name_str."')";
			//$resMissingBrandCat = parent::execQuery($sqlMissingBrandCat, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='category_restriction_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name,premium_flag';

			$where_arr  	=	array();
			$where_arr['category_name']		= implode(",",$service_name_arr);	
			$cat_params['where']			= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				foreach($cat_res_arr['results'] as $key =>$row_brand_cat)
				{
					$brand_missing_catid = trim($row_brand_cat['catid']);
					$brand_missing_catnm = trim($row_brand_cat['category_name']);
					$premium_flag 		 = trim($row_brand_cat['premium_flag']);
					if((intval($brand_missing_catid)>0) && (!in_array($brand_missing_catid,$this->catids_arr)))
					{
						$missing_brand_cat_flag = 1;
						$missing_brand_catid_arr[] = $brand_missing_catid;
						$missing_brand_catnm_arr[] = $brand_missing_catnm."___".$premium_flag;
					}
					if($premium_flag == 1){
						$premium_cat_flag = 1;
					}
				}
				if($missing_brand_cat_flag == 1)
				{
					$missing_brand_catinfo_arr['catid'] 		= implode("|~|",$missing_brand_catid_arr);
					$missing_brand_catinfo_arr['catname'] 		= implode("|~|",$missing_brand_catnm_arr);
					$missing_brand_catinfo_arr['premium_flag'] 	= $premium_cat_flag;
				}
			}
		}
		return $missing_brand_catinfo_arr;
	}
	
	function checkPriceFilter($rest_price_range_arr){		
		$resarr = array();
		foreach($rest_price_range_arr as $catid => $range){			
			$resarr[$range][] = $catid;			
		}
		$only_price_arr = array();
		$only_price_arr = array_keys($resarr);
		
		$price_break = 0;
		if(count($only_price_arr)>0){
			if(in_array('inexpensive',$only_price_arr) && in_array('expensive',$only_price_arr)){
				$price_break = 1;
				$res_arr['pricerange_value'] = "inexpensive|~|expensive";
				
				$rest_filter_arr = array();
				$rest_filter_arr = array_merge($resarr['inexpensive'],$resarr['expensive']);
				$res_arr['pricefilter_catid'] = implode("|~|",$rest_filter_arr);
			}
			if(in_array('inexpensive',$only_price_arr) && in_array('veryexpensive',$only_price_arr)){
				$price_break = 1;
				$res_arr['pricerange_value'] = "inexpensive|~|expensive";
				$rest_filter_arr = array();
				$rest_filter_arr = array_merge($resarr['inexpensive'],$resarr['veryexpensive']);
				$res_arr['pricefilter_catid'] = implode("|~|",$rest_filter_arr);		
			}
			if(in_array('moderate',$only_price_arr) && in_array('veryexpensive',$only_price_arr)){
				$price_break = 1;
				$res_arr['pricerange_value'] = "moderate|~|veryexpensive";
				$rest_filter_arr = array();
				$rest_filter_arr = array_merge($resarr['moderate'],$resarr['veryexpensive']);
				$res_arr['pricefilter_catid'] = implode("|~|",$rest_filter_arr);
			}
		}
		
		if($price_break == 1){
			$res_arr['errorCode'] 	= 1;
			$res_arr['msg'] 		= "PRICE_ERR";
		}
		else{
			$res_arr['errorCode'] 	= 0;
			$res_arr['msg'] 		= "Valid filter";
		}
		return $res_arr;
	}	
	
	function getRestMissingCategories($rest_cat_chk_arr,$pricerange_value_arr,$cuisine_catdata_arr)
	{
		$omit_gen_rest = 0;
		foreach($rest_cat_chk_arr as $rest_catdel){
			$rest_misc_flag = $rest_catdel['misc'];
			if(((int)$rest_misc_flag & 512) != 512){
				$omit_gen_rest = 1;
				break;
			}
		}
		if(in_array("veryexpensive",$pricerange_value_arr)){
			$key = array_search("veryexpensive",$pricerange_value_arr);
			$pricerange_value_arr[$key] = "very expensive";
		}
		$missing_catid_arr = array();
		$rest_catid_info_arr = array();
		$rest_catid_info_arr = $this->getRestaurantCatidInfo();
		
		$restaurant_catid = $rest_catid_info_arr['cid'];
		$restaurant_associate_national_catid = $rest_catid_info_arr['cname'];
		if((intval($restaurant_catid)>0) && (!in_array($restaurant_catid,$this->catids_arr)) && ($omit_gen_rest == 1))
		{
			$missing_catid_arr[] = $restaurant_catid;  // Adding Restaurant Catid
		}
		
		if((count($pricerange_value_arr)<=2) && (intval($restaurant_associate_national_catid)>0) && ($omit_gen_rest == 1))
		{
			foreach ($pricerange_value_arr as $selected_catname_display)
			{
				$rest_generic_catid = $this->getRestaurantGenericCategory($selected_catname_display,$restaurant_associate_national_catid);
				if((intval($rest_generic_catid)>0) && (!in_array($rest_generic_catid,$this->catids_arr)))
				{
					$missing_catid_arr[] = $rest_generic_catid;  // Adding Restaurant Generic Category Based On Restaurant Category Range
				}
			}
		}
		
		foreach($rest_cat_chk_arr as $catid => $catinfo_arr)
		{
			
			$associated_nat_catid 	= $catinfo_arr['assoc'];
			$misc_cat_flag 			= $catinfo_arr['misc'];
			$price_range_val		= $catinfo_arr['prange'];

			if(((int)$misc_cat_flag & 512) != 512){
				$associated_catid_arr = array();
				$associated_catid_arr = $this->getAssociatedCatidInfo($associated_nat_catid);
				$associated_catid = $associated_catid_arr['catid'];
				if((!empty($associated_catid_arr)) && (!in_array($associated_catid,$this->catids_arr)))
				{
					$associated_catname = $associated_catid_arr['catname'];
					$associated_national_catid = $associated_catid_arr['national_catid'];
					if((intval($associated_catid)>0) && (!empty($associated_catname)) && (intval($associated_national_catid)>0))
					{
						$missing_catid_arr[] = $associated_catid; // Adding Restaurant Head Filter Category Based On Associated National Catid
						if((intval($associated_catid_arr['associate_national_catid']) >0) && (!empty($price_range_val)) && ($associated_catid_arr['cuisine_tag'] == 1)){
						
							$cuisine_catdata_arr[$associated_catid]['assoc'] = $associated_catid_arr['associate_national_catid'];
							$cuisine_catdata_arr[$associated_catid]['misc'] = $associated_catid_arr['misc_cat_flag'];
							$cuisine_catdata_arr[$associated_catid]['cnm'] = $associated_catname;
						}
					}
				}
			}
		}
		if(count($cuisine_catdata_arr)>0 && count($pricerange_value_arr)>0){
		
		// Removing Omit Generic Tagged Category 
		$cuisine_assoc_natcid_arr = array();
		foreach($cuisine_catdata_arr as $cuscat => $cuscatdetails){
			
			$assoc_nat_catid 		= $cuscatdetails['assoc'];
			$misccatflag 			= $cuscatdetails['misc'];
			
			if((((int)$misccatflag & 512) != 512) && ($assoc_nat_catid > 0)){
				$cuisine_assoc_natcid_arr[] = $assoc_nat_catid;
			}
		}
		// Finding Selecetd Price Range + Selected Cuisine Mapped Categories
		$cuisine_catid_arr = array();
		if(count($cuisine_assoc_natcid_arr)>0){
			$cuisine_catid_arr = $this->getCuisineCatidInfo($cuisine_assoc_natcid_arr,$pricerange_value_arr);
			
			// Preparing ignore array (If a category is already selected by user for a cuisine , for that cuisine auto insertion logic will not be applied. Also, missing category price range should not match with selected cuisine category price range.)
			$ignore_assoc_arr = array();
			$ignore_prange_arr = array();
			foreach($cuisine_catid_arr as $cuscid => $cusdet){
				if(in_array($cuscid,$this->catids_arr)){
					$ignore_assoc_arr [] = $cusdet['assoc'];
					$ignore_prange_arr [] = $cusdet['prange'];
				}
			}
			
			// finding missing categories - final steps
			foreach($cuisine_catid_arr as $cuisine_cid => $cuisine_details){
				$cuisine_assocnid = $cuisine_details['assoc'];
				$cuisine_prange = $cuisine_details['prange'];
				if((!in_array($cuisine_assocnid,$ignore_assoc_arr)) && ((!in_array($cuisine_prange,$ignore_prange_arr)) || (in_array($cuisine_prange,$ignore_prange_arr) && count($pricerange_value_arr) == 1)) && (!in_array($cuisine_cid,$this->catids_arr))){
					$missing_catid_arr[] = $cuisine_cid;
				}
			}
		}
	}
		if(count($missing_catid_arr)>0){
			$missing_catid_arr = array_unique(array_filter($missing_catid_arr));
		}
		
		return $missing_catid_arr;
	}
	function getRestaurantCatidInfo()
	{
		$rest_catid_info_arr = array();
		//$sqlFetchRestaurantCatid = "SELECT catid,associate_national_catid FROM tbl_categorymaster_generalinfo WHERE category_name = 'Restaurants' LIMIT 1";
		//$resFetchRestaurantCatid 	= parent::execQuery($sqlFetchRestaurantCatid, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='category_restriction_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,associate_national_catid';
			$cat_params['limit']		= '1';

			$where_arr  	=	array();
			$where_arr['category_name']		= 'Restaurants';	
			$cat_params['where']			= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			$row_restaurant_catid = $cat_res_arr['results']['0'];
			$rest_catid = $row_restaurant_catid['catid'];
			$rest_associate_national_catid = $row_restaurant_catid['associate_national_catid'];
			if(intval($rest_catid)>0 && intval($rest_associate_national_catid)>0)
			{
				$rest_catid_info_arr['cid'] = $rest_catid;
				$rest_catid_info_arr['cname'] = $rest_associate_national_catid;
			}
		}
		return $rest_catid_info_arr;
	}
	function getRestaurantGenericCategory($categoryname_display,$associate_national_catid)
	{
		$restaurant_generic_catid = 0;
		if(!empty($categoryname_display) && (intval($associate_national_catid)>0))
		{
			$catname_display = '';
			switch(strtolower(trim($categoryname_display)))
			{
				case 'inexpensive' : 
					$catname_display = 'Inexpensive';
					break;
				case 'moderate' : 
					$catname_display = 'Moderate';
					break;
				case 'expensive' : 
					$catname_display = 'Expensive';
					break;
				case 'veryexpensive' : 
					$catname_display = 'Very Expensive';
					break;
				default :
					$catname_display = $categoryname_display;
					break;
			}
			//$sqlRestaurantGenericCategory = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE rest_price_range = '".$catname_display."' AND associate_national_catid = '".$associate_national_catid."'";
			//$resRestaurantGenericCategory 	= parent::execQuery($sqlRestaurantGenericCategory, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='category_restriction_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid';

			$where_arr  	=	array();
			$where_arr['rest_price_range']			= str_replace("','", ",",$catname_display);
			$where_arr['associate_national_catid']	= str_replace("','", ",",$associate_national_catid);	
			$cat_params['where']			= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				$row_restaurant_generic_catid = $cat_res_arr['results']['0'];
				$restaurant_generic_catid = $row_restaurant_generic_catid['catid'];
			}
		}
		return $restaurant_generic_catid;
	}
	function getAssociatedCatidInfo($national_catid)
	{
		$assoc_catid_arr = array();
		if(intval($national_catid)>0)
		{
			//$sqlFetchAssocCatid = "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE national_catid = '".$national_catid."' LIMIT 1";
			//$resFetchAssocCatid 	= parent::execQuery($sqlFetchAssocCatid, $this->conn_catmaster);
			$cat_params = array();
			$cat_params['page'] ='category_restriction_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name,national_catid,associate_national_catid,misc_cat_flag,cuisine_tag';
			$cat_params['limit']		= '1';

			$where_arr  	=	array();
			$where_arr['national_catid']	= str_replace("','", ",",$national_catid);	
			$cat_params['where']			= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
			{
				$row_assoc_catid = $cat_res_arr['results']['0'];
				$assoc_catid_arr['catid'] = $row_assoc_catid['catid'];
				$assoc_catid_arr['catname'] = $row_assoc_catid['category_name'];
				$assoc_catid_arr['national_catid'] = $row_assoc_catid['national_catid'];
				$assoc_catid_arr['associate_national_catid'] = $row_assoc_catid['associate_national_catid'];
				$assoc_catid_arr['misc_cat_flag'] = intval($row_assoc_catid['misc_cat_flag']);
				$assoc_catid_arr['cuisine_tag'] = intval($row_assoc_catid['cuisine_tag']);
			}
		}
		return $assoc_catid_arr;
	}
	function getCuisineCatidInfo($assoc_natcatid_arr,$selected_price_arr)
	{
		$selected_price_str = implode(",",$selected_price_arr);
		$assoc_natcatid_str = implode(",",$assoc_natcatid_arr);
		$cuisine_catid_arr = array();
		
			
		$cat_params = array();
		$cat_params['page'] 		='category_restriction_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,rest_price_range,associate_national_catid';		

		$where_arr  				=	array();
		$where_arr['associate_national_catid']	= $assoc_natcatid_str;
		$where_arr['rest_price_range']			= $selected_price_str;	
		$cat_params['where']					= json_encode($where_arr);
		$cat_res_arr = array();			
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
			foreach($cat_res_arr['results'] as $key =>$row_cuis_catid){
				$cuisine_catid_arr[$row_cuis_catid['catid']]['cname'] = $row_cuis_catid['category_name'];
				$cuisine_catid_arr[$row_cuis_catid['catid']]['prange'] = $row_cuis_catid['rest_price_range'];
				$cuisine_catid_arr[$row_cuis_catid['catid']]['assoc'] = $row_cuis_catid['associate_national_catid'];
			}
		}
		
		return $cuisine_catid_arr;
	}
	function getCategoryDetails($catidArr,$add_premium_flag=0)
	{
		$CatinfoArr = array();
		$premium_cat_flag = 0;
		$catids_str = implode(",",$catidArr);
		//$sqlCategoryDetails = "SELECT category_name,premium_flag,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catids_str.")";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);

		$cat_params = array();
		$cat_params['page'] ='category_restriction_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'category_name,premium_flag,national_catid';		

		$where_arr  	=	array();
		$where_arr['catid']			= $catids_str;	
		$cat_params['where']		= json_encode($where_arr);
		if(count($catidArr)>0){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
		}
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			$catname_arr = array();
			$national_catid_arr = array();
			foreach($cat_res_arr['results'] as $key =>$row_catdetails)
			{
				$category_name	= trim($row_catdetails['category_name']);
				$premium_flag 	= trim($row_catdetails['premium_flag']);
				$national_catid = trim($row_catdetails['national_catid']);
				
				if($premium_flag == 1){
					$premium_cat_flag = 1;
				}
				if($add_premium_flag == 1){
					$catname_arr[] = $category_name."___".$premium_flag;
				}else{
					$catname_arr[] = $category_name;
				}
				$national_catid_arr[] = $national_catid;
			}
		}
		$CatinfoArr['premium_flag'] = $premium_cat_flag;
		$CatinfoArr['category_name'] = implode("|~|",$catname_arr);
		$CatinfoArr['national_catid'] = implode("|~|",$national_catid_arr);
		return $CatinfoArr;
	}
	function makeMissingCatinfo($catidArr)
	{
		$CatinfoArr = array();
		$premium_cat_flag = 0;
		$catid_val_str = implode(",",$catidArr);
		//$sqlCategoryDetails = "SELECT category_name,premium_flag FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_val_str.")";
		//$resCategoryDetails = parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page'] ='category_restriction_class';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,premium_flag';		

		$where_arr  	=	array();
		$where_arr['catid']			= $catid_val_str;	
		$cat_params['where']		= json_encode($where_arr);
		if(count($catidArr)>0){
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		$catname_arr 	= array();
		$categidarr 	= array();
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			
			foreach($cat_res_arr['results'] as $key => $row_catdetails)
			{
				$category_id	= trim($row_catdetails['catid']);
				$category_name	= trim($row_catdetails['category_name']);
				$premium_flag 	= trim($row_catdetails['premium_flag']);
				
				if($premium_flag == 1){
					$premium_cat_flag = 1;
				}
				$catname_arr[] 	= $category_name."___".$premium_flag;
				$categidarr[] 	= $category_id;
				
			}
		}
		$CatinfoArr['premium_flag'] = $premium_cat_flag;
		$CatinfoArr['category_name'] = implode("|~|",$catname_arr);
		$CatinfoArr['category_id'] = implode("|~|",$categidarr);
		return $CatinfoArr;
	}
	function contractLandlineInfo()
	{
		$catlin_nonpaid_db = '';
		if(($this->module == 'DE') || ($this->module == 'CS'))
		{
			$catlin_nonpaid_db = 'db_iro.';
		}	
		$landline_existing_flag = 0;
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "landline,othercity_number,companyname";
			$row_landline = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlLandlineInfo = "SELECT landline,othercity_number,companyname FROM ".$catlin_nonpaid_db."tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
			$resLandlineInfo 	= parent::execQuery($sqlLandlineInfo, $this->conn_temp);
			$num_rows 			= parent::numRows($resLandlineInfo);
			if($num_rows > 0 ){
				$row_landline = parent::fetchData($resLandlineInfo);
			}
		}
		if(count($row_landline)>0)
		{
			$this->companyname = $row_landline['companyname'];
			$landline 			= trim($row_landline['landline'],",");
			$landline_arr 		= array();
			if($landline)
			{
				$landline_arr = explode(",",$landline);
				$landline_arr = array_filter($landline_arr);
				$landline_arr = array_unique($landline_arr);
			}
			
			$other_city_number 	= array();
			$other_city_num 	= trim($row_landline['othercity_number'],",");
			if($other_city_num)
			{
				$other_city_number = explode(",",$other_city_num);
				$other_city_number = array_filter($other_city_number);
				$other_city_number = array_unique($other_city_number);
			}
			
			$other_city_num_arr = array();
			if(count($other_city_number)>0)
			{
				foreach($other_city_number as $other_number)
				{
					$other_number_arr = explode("##",$other_number);
					$other_city_num_arr[] = $other_number_arr[2];
				}
			}
			$final_contact_arr = array();
			$final_contact_arr = array_merge($landline_arr,$other_city_num_arr);
			$final_contact_arr = array_filter($final_contact_arr);
			$final_contact_arr = array_unique($final_contact_arr);
			if(count($final_contact_arr)>0 && !empty($final_contact_arr))
			{
				$landline_existing_flag = 1;
			}
		}
		return $landline_existing_flag;
	}
	function docid_creator()
	{
		$major_city_name = $this->GetMajorCity();
		if(!empty($major_city_name))
		{
			$stdcode = $this->getStdCode();
			$docid= $stdcode.$this->parentid;
		}else{
			$docid="9999".$this->parentid;
		}
		return $docid;
	}
	
	function GetMajorCity()
	{
		$city_name = '';
		$sqlMajorCityChk	= "SELECT city_name FROM tbl_major_cities WHERE city_name='".$this->data_city."'";
		$resMajorCityChk 	= parent::execQuery($sqlMajorCityChk, $this->conn_iro);
		if($resMajorCityChk && mysql_num_rows($resMajorCityChk))		
		{
			$row_major_city = mysql_fetch_assoc($resMajorCityChk);
			$city_name		= trim($row_major_city['city_name']);
		}
		return $city_name;
	}

	function getStdCode()
	{
		$sqlFetchStdCode	= "SELECT stdcode FROM  d_jds.city_master WHERE ct_name='".$this->data_city."'";
		$resFetchStdCode 	=  parent::execQuery($sqlFetchStdCode, $this->conn_local);
		if($resFetchStdCode && mysql_num_rows($resFetchStdCode)>0)
		{
			$row_stdcode 	= mysql_fetch_assoc($resFetchStdCode);			
			$stdcode		= $row_stdcode['stdcode'];
			return $stdcode;
		}
	}
	function contractByPassCheck($reasonid)
	{
		$bypasscheck_flag = 0;
		$sqlContractByPassChk = "SELECT parentid FROM tbl_contract_bypass_exclusion WHERE parentid = '".$this->parentid."' AND reasonid = '".$reasonid."'";
		$resContractByPassChk = parent::execQuery($sqlContractByPassChk, $this->conn_iro);
		if($resContractByPassChk && mysql_num_rows($resContractByPassChk)>0)
		{
			$bypasscheck_flag = 1;
		}
		return $bypasscheck_flag;
	}
	function isExclusionTypeContract()
	{
		$this->exclusion_flag = 0;
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "hiddenCon";
			$rowExclusionTypeChk = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqlExclusionTypeChk = "SELECT hiddenCon FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."' AND hiddenCon = 1";
			$resExclusionTypeChk  	= parent::execQuery($sqlExclusionTypeChk, $this->conn_temp);
			if($resExclusionTypeChk && mysql_num_rows($resExclusionTypeChk)>0)
			{
				$rowExclusionTypeChk = mysql_fetch_assoc($resExclusionTypeChk);
			}
		}
		if($rowExclusionTypeChk['hiddenCon']==1)
		{
			$this->exclusion_flag = 1;
		}
	}
	function dryStateInfo(){
		$drystate = 0;
		$dry_states_arr =  array("gujarat","bihar","nagaland","lakshadweep");
		$sqlStateName = "SELECT state_name FROM city_master WHERE ct_name = '".$this->data_city."'";
		$resStateName = parent::execQuery($sqlStateName, $this->conn_local);
		if($resStateName && parent::numRows($resStateName)>0){
			$row_state = parent::fetchData($resStateName);
			$state_name = trim($row_state['state_name']);
			if(in_array(strtolower($state_name),$dry_states_arr)){
				$drystate = 1;
			}
		}
		return $drystate;
	}
	function contractGeneralInfo()
	{
		$geninfo_arr = array();
		$geninfo_arr['paid'] = 0;

		$row_geninfo = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'paid';
		$cat_params['page']			= 'category_restriction_class';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		

		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
			$row_geninfo 		=	$res_gen_info1['results']['data'][$this->parentid];
			$geninfo_arr['paid'] = intval($row_geninfo['paid']);
		}

		/*$sqlGeneralInfo = "SELECT paid FROM tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
		$resGeneralInfo = parent::execQuery($sqlGeneralInfo, $this->conn_iro);
		if($resGeneralInfo && mysql_num_rows($resGeneralInfo)>0)
		{
			$row_geninfo = mysql_fetch_assoc($resGeneralInfo);
			$geninfo_arr['paid'] = intval($row_geninfo['paid']);
		}*/
		
		return $geninfo_arr;
	}
	function getChecksum($docid)
	{
		$secret_string = '32482345789634892564569023458356';
		$docid = strtolower($docid);
			
		return sha256($secret_string . $docid);
	}
	function curlCallGet($curl_url)
	{
		$ch = curl_init($curl_url);
		$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
	}
	
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}		
}
?>
