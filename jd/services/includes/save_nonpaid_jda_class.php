<?php 

class saveNonPaidContract extends DB
{
	var $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		$usercode			= trim($params['usercode']);
		$username			= trim($params['username']);
		$landline 			= trim($params['landline']);
		$me_jda_flag 		= trim($params['me_jda_flag']);

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

		$this->validationcode	=	'SAVENONPAIDJDA';
		$this->data_city 		= $data_city;
		

		$this->logFields = array();
		$this->logFields['params'] = $params;

		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode		= $usercode;
		$this->uname 		= $username;
		$this->landline     = $landline;
		$this->me_jda_flag  = $me_jda_flag;
		
		$this->corrincorr	= 0;
		if(isset($params['corrincorr'])){
			$this->corrincorr = intval($params['corrincorr']);
		}
		
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj =	new categoryClass();
		$this->companyClass_obj = new companyClass();

		$this->setServers();
		$urls = $this->getCurlURL($this->data_city);
		$this->cs_url	 = $urls['url'];
		$this->jdbox_url = $urls['jdbox_url'];	
		
		$this->spinxId	 = $this->getSphinxId();
		$this->action 	 = trim($params['action']); 		
				
		if(intval($this->spinxId) <=0 ){
			$message = "Sphinx id Not Found !!!";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		$this->existing_flag = 0;
		$this->business_temp_data 	= $this->getBusinessTemData();
		
		$this->genInfoArrMain     	= $this->getGenInfoMain();
		$this->genInfoPaid			= 0;
		if($this->genInfoArrMain['numrows'] >0 ){
			$this->existing_flag = 1;
			$this->genInfoPaid = $this->genInfoArrMain['paid']; 
		}
		$this->extraDetailsArrMain    = $this->getextradetailsInfoMain();
		$this->genInfoArr         	= $this->getGenInfoShadow();
		
		if($this->landline==''){
			$this->landline = $this->genInfoArr['landline'];
		}
		
		$this->extraDetailsArr    = $this->getextradetailsInfoShadow();
		$this->interMediateTable  = $this->getIntermediateData();
		
		
		if(count($this->genInfoArr) <=0)
		{
			$message = "Data Not Saved.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['state']) == '')
		{
			$message = "State is Blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['city']) == '')
		{
			$message = "City is Blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->extraDetailsArr) <=0)
		{
			$message = "Data Not Saved..";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->interMediateTable) <=0)
		{
			$message = "Data Not Saved...";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(count($this->business_temp_data) <=0)
		{
			$message = "Data Not Saved !!!";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		$this->business_temp_catid_arr = array();
		$this->phonesearch_flag	=	$this->isPhoneSearchCampaignExists();		
		if($this->phonesearch_flag!=1){
			$paid_catids	=	trim($this->business_temp_data['catIds']);
			
			if($paid_catids!=''){				
				$catid_arr_temp_shadow	=	explode("|P|", $paid_catids);
				$this->business_temp_catid_arr  = 	$this->getValidCategories($catid_arr_temp_shadow);		
			}
		}
		else{
				$message = "Not Allowed to Proceed as this is a Paid Contract!!!";
				echo json_encode($this->sendDieMessage($message));
				die();
		}
		if(count($this->business_temp_catid_arr)<=0){
			$message = "No Category Found !!!";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['companyname']) != '')
		{
			//$this->validateGlobalAPI();
		}
		$this->mobileChecking();		
	}

	private function setServers(){	
		GLOBAL $db;

		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->remote_city_flag = 0;
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->remote_city_flag = 1;
			$this->is_remote = 'REMOTE';
		}
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_idc			= $db[$conn_city]['idc']['master'];
		$this->conn_fnc			= $db[$conn_city]['fin']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_data  		= $db[$conn_city]['data_correction']['master'];
		$this->conn_log   		= $db['db_log'];
		$this->conn_national   	= $db['db_national'];
		if(strtolower($this->module)=='cs'){
			$this->conn_temp = $this->conn_local;
		}else if(strtolower($this->module)=='me' || strtolower($this->module)=='jda'){
			$this->conn_temp = $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}else if(strtolower($this->module)=='tme'){
			$this->conn_temp = $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}

		} 
	}

	private function validateGlobalAPI(){
		$url	=	$this->jdbox_url."/services/global_company_api.php";
		$compname 	=	trim($this->genInfoArr['companyname']);

		$global_param_arr = array();
		$global_param_arr['compname'] 	= $compname;
		$global_param_arr['module'] 	= $this->module;
		$global_param_arr['data_city']	= $this->data_city;		
		
		$global_res = array();
		$global_res =	json_decode($this->curlCallPost($url,$global_param_arr));
		if($global_res['error']['code']==1){
			if($global_res['block']['msg']!=''){
				$message = $global_res['block']['msg'];
				echo json_encode($this->sendDieMessage($message));
				die();
			}
		}
	}
    
	public function finalInsert(){				
		
		$post_arr = array();
		if(strtoupper($this->action) == 'JDABFORMEXIT'){			
			
			if(count($this->extraDetailsArrMain)>0){
				$this->extraDetailsArr['catidlineage_nonpaid'] 			= $this->extraDetailsArrMain['catidlineage_nonpaid'];
				$this->extraDetailsArr['national_catidlineage_nonpaid'] = $this->extraDetailsArrMain['national_catidlineage_nonpaid'];;
				$this->extraDetailsArr['catidlineage_search']  			= $this->extraDetailsArrMain['catidlineage_search'];
				$this->extraDetailsArr['national_catidlineage_search']  = $this->extraDetailsArrMain['national_catidlineage_search'];
				$catidLineage  											=  $this->extraDetailsArrMain['catidlineage'] ;				
				
				$category_count = $this->extraDetailsArrMain['category_count'];
				$hotcategory 	= $this->extraDetailsArrMain['hotcategory'];				
				
				$paidcats_arr 		= array();
				$paidcats_arr 		= explode("/,/",trim($this->extraDetailsArrMain['catidlineage'],"/"));
				$paidcats_arr 		= $this->getValidCategories($paidcats_arr);
				$this->logFields['final_paid'] 	= implode(',',$paidcats_arr);
				
				$nonpaidcats_arr 	= array();
				$nonpaidcats_arr	= explode("/,/",trim($this->extraDetailsArrMain['catidlineage_nonpaid'],"/"));
				$nonpaidcats_arr 	= $this->getValidCategories($nonpaidcats_arr);
				$this->logFields['final_nonpaid'] 	= implode(',',$nonpaidcats_arr);
				$this->logFields['comment']			= 'JDABFORMEXIT - Entry Found In Extradetails Table';
				
			}
			else{
				$catidLineage = '';
				$post_arr['nocategory']				= 1;
				$this->extraDetailsArr['catidlineage_nonpaid'] 			= null;
				$this->extraDetailsArr['national_catidlineage_nonpaid'] = null;
				$this->extraDetailsArr['catidlineage_search']  			= null;
				$this->extraDetailsArr['national_catidlineage_search']  = null;
				$hotcategory						= null;
				$category_count						= null;
				$this->logFields['comment']			= 'JDABFORMEXIT - No Entry Found In Extradetails Table';
			}
		}else{
			## special case if existing paid is converted to non paid
			$final_paid_cat_arr = array();
			$all_catidlist_arr 	= array();
			$nonpaid_catids_arr 	= array();
			$nonpaid_catids_arr = explode("/,/",trim($this->extraDetailsArr['catidlineage_nonpaid'],"/"));					
			$nonpaid_catids_arr = 	$this->getValidCategories($nonpaid_catids_arr);		
			$all_catidlist_arr	=	array_merge($nonpaid_catids_arr,$this->business_temp_catid_arr);				
		
			$this->extraDetailsArr['catidlineage_nonpaid'] = '';

			$MissingCategoryArr = array();
			
			if(count($all_catidlist_arr)>0)
			{				
				$catids_list = implode(",",$all_catidlist_arr);
				$categories_status_data = "&module=me&catid=".$catids_list."&parentid=".$this->parentid;
				
				$cs_app_url = $this->cs_url."api/category_info_api.php";
				$category_info_arr  = json_decode($this->curlCallPost($cs_app_url,$categories_status_data),true);
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
				$all_catidlist_arr = array_merge($all_catidlist_arr,$missing_nonpaid_catid_arr);
				
			}
			$final_paid_cat_arr = $this->getValidCategories($all_catidlist_arr);
			
						
			$final_catidlineage_search   = array();
			$temp_paid_catids		= implode(',',$final_paid_cat_arr);
			$paid_parent_catids		= $this->getParentCategories($temp_paid_catids);
			$paid_parent_catids		= $this->getValidCategories($paid_parent_catids);
			
			$final_catidlineage_search = array_merge($final_paid_cat_arr,$paid_parent_catids);
			$final_catidlineage_search = $this->getValidCategories($final_catidlineage_search);
			$this->extraDetailsArr['catidlineage_search']  = '/'.implode('/,/',$final_catidlineage_search).'/';

			$this->extraDetailsArr['national_catidlineage_nonpaid'] = '';

			$national_catidlineage_nonpaid_search = $this->getNationalCatlineage($this->extraDetailsArr['catidlineage_search']);
			$this->extraDetailsArr['national_catidlineage_search']  = $national_catidlineage_nonpaid_search;
				
			$catidLineage 	= '/'.implode('/,/',$final_paid_cat_arr).'/';
			$category_count = $this->extraDetailsArr['category_count'];
			$hotcategory 	= $this->extraDetailsArr['hotcategory'];
			$this->logFields['final_paid'] 	= implode(',',$final_paid_cat_arr);
			$this->logFields['comment']		= 'JDA - Normal Case';
		}		
		$update_business_temp_data = $this->update_attr_based_on_cat();
		$this->loggingIntoTable();		
		$this->handleFreezing();
		$phone_search_res = $this->phoneSearchArr();
		
			

		/**#########Post processing starts here ########**/
		
		$areaLineage 	= '/'.$this->genInfoArr['country'].'/'.$this->genInfoArr['data_city'].'/'.addslashes($this->genInfoArr[city]).'/'.addslashes($this->genInfoArr[area]).'/';
		
		
		$address 		= 	$this->genInfoArr['full_address'].",".$this->genInfoArr['city'].",".$this->genInfoArr['state'];	
		$date			= date('Y-m-d H:i:s');
		
		
		$rowStd = $this->getPincode();

		if($this->interMediateTable['hiddenCon'] == '1'){
				$display = 0;
				$hidden  = 1;
		}else{
				$display = 1;
				$hidden  = 0;
		}
		$sql_serve   = "SELECT pin_code FROM tbl_areas_count WHERE pin_code like '%".$this->genInfoArr[pincode]."%' and display>0";
		$res_serve   = parent::execQuery($sql_serve, $this->conn_local);
		if(parent::numRows($res_serve)>0){
			$alsoserve=0;
		}
		else{
			$alsoserve=1;
		}
		$htmldump 	= stripslashes($this->business_temp_data['htmldump']);
		$sloganstr 	= stripslashes($this->business_temp_data['slogan']);	

		if(trim($this->extraDetailsArrMain['original_creator'])!=''){
			$original_creator		=	trim($this->extraDetailsArrMain['original_creator']);
		}
		else{
			$original_creator 		= $this->ucode;
		}

		// tbl_companymaster_generalinfo
		$post_arr['nationalid']				= $this->genInfoArr['nationalid'];
		$post_arr['sphinx_id']				= $this->genInfoArr['sphinx_id'];
		$post_arr['regionid']				= $rowStd[stdcode];
		$post_arr['companyname']			= $this->genInfoArr['companyname'];
		$post_arr['parentid'] 				= $this->parentid;
		
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
		$post_arr['tollfree_display']		= $this->genInfoArr['tollfree'];
		$post_arr['email']					= $this->genInfoArr['email'];
		$post_arr['email_display']			= $this->genInfoArr['email_display'];
		$post_arr['email_feedback']			= $this->genInfoArr['email_feedback'];
		$post_arr['sms_scode']				= $this->genInfoArr['sms_scode'];
		$post_arr['website']				= $this->genInfoArr['website'];
		$post_arr['contact_person']			= $this->genInfoArr['contact_person'];
		$post_arr['contact_person_display']	= $this->genInfoArr['contact_person'];
		$post_arr['callconnect']			= $this->interMediateTable['callconnect'];
		$post_arr['othercity_number']		= $this->genInfoArr['othercity_number'];
		$post_arr['mobile_admin']			= $this->genInfoArr['mobile_admin'];
		$post_arr['paid'] 					= $this->genInfoPaid;
		
		
		
		$post_arr['displayType']			= str_replace(",","~",$this->interMediateTable['displayType']);
		$post_arr['data_city']				= $this->genInfoArr['data_city'];
		
		$post_arr['companyname_old']        = $this->genInfoArrMain['companyname'];
		//tbl_companymaster_extradetails		
		
		$post_arr['landline_addinfo'] 		= $this->extraDetailsArr['landline_addinfo'];
		$post_arr['mobile_addinfo'] 		= $this->extraDetailsArr['mobile_addinfo'];
		$post_arr['tollfree_addinfo'] 		= $this->extraDetailsArr['tollfree_addinfo'];
		$post_arr['contact_person_addinfo'] = $this->extraDetailsArr['contact_person_addinfo'];
		$post_arr['attributes'] 			= $this->business_temp_data['mainattr'];
		$post_arr['attributes_edit'] 		= $this->business_temp_data['facility'] ;
		$post_arr['turnover'] 				= $this->extraDetailsArr['turnover'];	
		$post_arr['attribute_search'] 		= $this->extraDetailsArr['attribute_search'];
		
				
		$post_arr['working_time_start'] 	= $this->timingProcess($this->extraDetailsArr['working_time_start']);
		$post_arr['working_time_end'] 		= $this->timingProcess($this->extraDetailsArr['working_time_end']);
		$post_arr['payment_type'] 			= $this->extraDetailsArr['payment_type'];
		$post_arr['year_establishment'] 	= $this->extraDetailsArr['year_establishment'];
		$post_arr['accreditations'] 		= $this->extraDetailsArr['accreditations'];
		$post_arr['certificates'] 			= $this->extraDetailsArr['certificates'];
		$post_arr['no_employee'] 			= $this->extraDetailsArr['no_employee'];
		$post_arr['business_group'] 		= $this->extraDetailsArr['business_group'];
		$post_arr['email_feedback_freq'] 	= $this->extraDetailsArr['email_feedback_freq'];
		$post_arr['statement_flag'] 		= $this->extraDetailsArr['statement_flag'];
		$post_arr['alsoServeFlag'] 			= $alsoserve;
		if(isset($this->extraDetailsArr['employee_info'])){
			$post_arr['employee_info'] 	 = $this->extraDetailsArr['employee_info'];
		}

		$post_arr['guarantee'] 				= $this->interMediateTable['guarantee'];
		$post_arr['contract_calltype'] 		= $this->interMediateTable['contract_calltype'];
		$post_arr['deactflg'] 				= 'N';
		$post_arr['display_flag'] 			= $display;
		$post_arr['fmobile'] 				= $this->extraDetailsArr['fmobile'];
		$post_arr['femail'] 				= $this->extraDetailsArr['femail'];
		$post_arr['flgActive'] 				= $this->extraDetailsArr['flgActive'];
		$post_arr['freeze'] 				= 0;
		$post_arr['mask'] 					= 0;
		$post_arr['hidden_flag'] 			= $hidden;
		$post_arr['lockDateTime'] 			= $this->extraDetailsArr['lockDateTime'];
		$post_arr['lockedBy'] 				= $this->extraDetailsArr['lockedBy'];
		$post_arr['temp_deactive_start'] 	= $this->interMediateTable['temp_deactive_start'];
		$post_arr['temp_deactive_end'] 		= $this->interMediateTable['temp_deactive_end'];
		$post_arr['promptype'] 				= $this->extraDetailsArr['promptype'];
		
		$post_arr['serviceName'] 			= $this->interMediateTable['serviceName'];
		$post_arr['createdby']				= $this->extraDetailsArr['createdby'];
		$post_arr['createdtime']			= $date;
		$post_arr['original_creator']		= $original_creator;
		$post_arr['original_date']			= $date;
		$post_arr['updatedBy'] 				= $this->extraDetailsArr['updatedBy'];
		$post_arr['updatedOn'] 				= $this->extraDetailsArr['updatedOn'];
		
		$post_arr['catidlineage_nonpaid']	= $this->extraDetailsArr['catidlineage_nonpaid'];
		$post_arr['catidlineage_search']	= $this->extraDetailsArr['catidlineage_search'];
		$post_arr['national_catidlineage_nonpaid'] = $this->extraDetailsArr['national_catidlineage_nonpaid'];
		$post_arr['national_catidlineage_search']  = $this->extraDetailsArr['national_catidlineage_search'];
		
		$post_arr['category']				= $catidLineage;
		$post_arr['category_count']			= $category_count;
		$post_arr['hotcategory']			= $hotcategory;
		
		$post_arr['map_pointer_flags']		= $this->extraDetailsArr['map_pointer_flags'];
		$post_arr['flags']					= $this->extraDetailsArr['flags'];		
		$post_arr['tag_line']				= $this->extraDetailsArr['tag_line'];
		$post_arr['social_media_url']		= $this->extraDetailsArr['social_media_url'];
		$post_arr['companyname_search']		= $this->extraDetailsArr['companyname'];
		
		$post_arr['phone_search']			= $phone_search_res;//??		
		$post_arr['address']				= addslashes($address);
		$post_arr['length']					= strlen($this->genInfoArr['companyname']);
		$post_arr['prompt_flag']			= $this->interMediateTable['prompt_flag'];
		$post_arr['contractid'] 			= $this->extraDetailsArr['parentid'];
		$post_arr['compname'] 				= addslashes(stripslashes($this->genInfoArr['companyname']));		
		$post_arr['paidstatus'] 			= $this->genInfoPaid;
		$post_arr['freez'] 					= 0;
		$mainsource_arr						= $this->sourceCode();
		$mainsource							= $mainsource_arr['source_code'];
		
		if(!empty($mainsource)){
			$post_arr['mainsource'] 		= $mainsource;
			$post_arr['datesource'] 		= date("Y-m-d H:i:s");
			$post_arr['contactId']  		= $this->parentid;
		}
		$post_arr['universal_source']		= $mainsource_arr['sName'];
		$post_arr['contact_details']		= $phone_search_res;
		$post_arr['arealineage'] 			= $areaLineage;
		$post_arr['data_source']			= 'SaveNpJDA';
		$post_arr['datasource_date']		= $date;
		$post_arr['empCode']				= trim($this->ucode);
		$post_arr['session_key'] 			= $this->generateRandomString(15);
		$post_arr['source']  				= trim($this->module);
		$post_arr['ucode']  	 			= trim($this->ucode);
		$post_arr['uname']   				= trim($this->uname);
		$post_arr['htmldump'] 				= $htmldump;
		$post_arr['sloganstr'] 				= $sloganstr;			
		$post_arr['flow_module'] 			= 'saveasfreelisting';
		$post_arr['award'] 					= $this->extraDetailsArr['award'];
		$post_arr['testimonial'] 			= $this->extraDetailsArr['testimonial'];
		$post_arr['proof_establishment']	= $this->extraDetailsArr['proof_establishment'];
		$post_arr['closedown_flag']			= 0; // default value
		
		$old_closedown_flag 			    = $this->extraDetailsArrMain['closedown_flag'];
		$new_closedown_flag 				= $this->extraDetailsArr['closedown_flag'];

		if(intval(trim($old_closedown_flag)) != intval(trim($new_closedown_flag)))
		{
			$post_arr['closedown_date'] = date("Y-m-d H:i:s");
		}
		if($this->is_remote == 'REMOTE'){		
			$post_arr['is_remote'] 		= $this->is_remote;		
		}
		$post_arr['validationcode'] = $this->validationcode;		

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
		
		require 'geoCodeClass.php';

		$param_new['parentid']=$this->parentid;
		$param_new['action']=3;
		$param_new['data_city']=$this->data_city;
		$param_new['radius']=10;
		$param_new['stage']=0;
		$param_new['module']="ME";
		$updateEntry = "UPDATE online_regis.tbl_checkGeocodes SET dealclose_flag = 2 WHERE parentid = '".$this->parentid."' ";
		$condata =  parent::execQuery($updateEntry, $this->conn_idc);
		$param_pincodecheck = array();
		$param_pincodecheck['parentid']=$this->parentid;
		$param_pincodecheck['docid']=	$this->docidCreator();
		$param_pincodecheck['action']=5;
		$param_pincodecheck['data_city']=$this->data_city;
		$param_pincodecheck['module']="ME";
		$geoCodeClassObj = new geoCodeClass($param_pincodecheck);
		$curl_arr = $geoCodeClassObj->pincodeCheck();
		if($curl_arr['checkgeo']['error']['code'] == 0){
			$checkforentry = "SELECT *  FROM online_regis.tbl_checkGeocodes WHERE parentid = '".$this->parentid."' ";
			$condata =  parent::execQuery($checkforentry, $this->conn_idc);
			if($condata && mysql_num_rows($condata)>0){
				$checkdata = mysql_fetch_assoc($condata);
			}
			if(($checkdata['latitude']!= null && $checkdata['latitude']!= null) && ($checkdata['latitude']!= 0 && $checkdata['latitude']!= 0)){
				$ret_param['latitude']					= $checkdata['latitude'];
				$ret_param['longitude']					= $checkdata['longitude'];
				$ret_param['geocode_accuracy_level']		= 1;
				$ret_param['upt']		= 1;
				$ret_param['flags']					= $checkdata['flags'];
				$ret_param['map_pointer_flags']		= $checkdata['map_pointer_flags']; 
			}else{
				$ret_param['upt']		= 0;
			}
				
		}else if($curl_arr['shadow']['error']['code'] == 0){

			if(($this->genInfoArr['latitude'] != 0  && $this->genInfoArr['longitude'] != 0 &&  $this->genInfoArr['latitude'] != ''  && $this->genInfoArr['longitude'] != '' ) && $this->genInfoArr['geocode_accuracy_level'] == 1){
				$ret_param['latitude']					= $this->genInfoArr['latitude'];
				$ret_param['longitude']					= $this->genInfoArr['longitude'];
				$ret_param['geocode_accuracy_level']		= $this->genInfoArr['geocode_accuracy_level'];
				$ret_param['upt']		= 1;
				$ret_param['flags']					= $this->extraDetailsArrMain['flags'];
				$ret_param['map_pointer_flags']		= $this->extraDetailsArrMain['map_pointer_flags']; 

			}else{
				$ret_param['upt']		= 0;
			}
			
			}

		if($ret_param['upt']==1){
			$post_arr['geocode_accuracy_level'] 	= $ret_param['geocode_accuracy_level'];
			$post_arr['latitude']					= $ret_param['latitude'];
			$post_arr['longitude'] 					= $ret_param['longitude'];
			$post_arr['flags']					= $ret_param['flags'];
			$post_arr['map_pointer_flags']		= $ret_param['map_pointer_flags'];
		}
		else
		{
				
				$url_geocode = $this->cs_url."api_services/api_geocode_accuracy.php";
				$resmsg = $this->curlCallPost($url_geocode,$param_array);

				$json_return_arr = array();
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
		}
		
		
		if($post_arr['geocode_accuracy_level']=='1' || $post_arr['geocode_accuracy_level']=='2')
		{
			$params_mpf=array();
			$params_mpf['parentid']		=	$post_arr['parentid'];
			$params_mpf['data_city']	=	$post_arr['data_city'];
			$params_mpf['latitude']		=	$post_arr['latitude'];
			$params_mpf['longitude']	=	$post_arr['longitude'];
			$params_mpf['geocode_accuracy_level']= $post_arr['geocode_accuracy_level'];
			$params_mpf['rquest']		=	'map_pointer_flag';
			
			$curl_url_mpf 	= $this->jdbox_url."/services/location_api.php";
			$mpf_output =	json_decode($this->curlCallPost($curl_url_mpf,$params_mpf),true);
			
			$post_arr['map_pointer_flags']			=$mpf_output['result']['map_pointer_flags'];
			$post_arr['flags']					    =$mpf_output['result']['flags'];		
		}
		
		
		$finalvals = array();
		$finalvals['geocode_accuracy_level'] 	= $post_arr['geocode_accuracy_level'];
		$finalvals['latitude'] 	= $post_arr['latitude'];
		$finalvals['longitude'] 	= $post_arr['longitude'];
		$finalvals['flags'] 	= $post_arr['flags'];
		$finalvals['map_pointer_flags'] 	= $post_arr['map_pointer_flags'];
		$finalvals['ucode'] 	= $post_arr['ucode'];
		$finalupd = json_encode($finalvals);
		$updateEntry = "UPDATE online_regis.tbl_checkGeocodes SET finalVals = '".$finalupd."' WHERE parentid = '".$this->parentid."' ";
		$condata =  parent::execQuery($updateEntry, $this->conn_idc);
		
		$post_arr['savenonpaidjda'] = 1;

		$resjdbox_arr = $this->jdboxCurlCall($post_arr);

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
				$param_array2['uname']				=	$post_arr['uname'];
				$param_array2['ucode']				=	$post_arr['ucode'];
				$param_array2['temp_latitude']		=	$post_arr['latitude'];
				$param_array2['temp_longitude']		=	$post_arr['longitude'];
				$param_array2['temp_tagging']		=	$post_arr['geocode_accuracy_level'];
				$param_array2['original_tagging']	=	$this->genInfoArrMain['geocode_accuracy_level_old'];
				$param_array2['new_address']		=	json_encode($arr_new_add);
				$param_array2['old_address']		=	json_encode($arr_old_add);
				$param_array2['rquest']				=	"insertGeocodeModeration";
				$resmsg = $this->curlCallPost($url_geocode,$param_array2);
			}
		}
		/**#########Post processing ends here ########**/
		
		// TME Correct Incorrect Flow
		if($this->corrincorr == 1){
			$instantdata	=	array();
			$instantdata['parentid']	=	$this->parentid;
			$instantdata['data_city']	=	$this->data_city;	
			$instantdata['module']		=	$this->module;
			$instantdata['ucode']		=	$this->ucode;	
			$instantdata['post_data']	=	1;
			
			$instant_url	=	$this->jdbox_url."/services/instant_live.php";
			$instant_res 	= $this->curlCallPost($instant_url,$instantdata);
		}
		
		$this->update_lock_company();	
		$this->save_freelisting_landline_info();	
		$this->updateContractHavingTollFreeNum();
		
		$docbformdata = $this->pendingDocBformData();
		
		
		
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";	
		$resLog['result'] = $result_msg_arr;
		$this->functionForSendLogs($resLog);
		return $result_msg_arr;

	}
	private function pendingDocBformData(){
		$sqlPendingDocData = "SELECT root_parentid,data_city,ucode,uname,docdata FROM tbl_docnew_bform_data WHERE root_parentid = '".$this->parentid."' AND active_flag = 1";
		$resPendingDocData = parent::execQuery($sqlPendingDocData, $this->conn_log);
		if($resPendingDocData && parent::numRows($resPendingDocData)){
			$row_doc_data = parent::fetchData($resPendingDocData);
			
			$this->genInfoArrMain     	= $this->getGenInfoMain();
			if(count($this->genInfoArrMain)>0){ // data exists on live server or not
				
				
				if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
					$this->web_services_api 	 	= "http://".WEB_SERVICES_API."/web_services/";
				}else{
					$this->web_services_api = "http://sunnyshende.jdsoftware.com/web_services/web_services/";	
				}
				$docid = $this->docidCreator();
				$url 	= $this->web_services_api."rsvnInfo.php";
				$data 	= "docid=".$docid."&type_flag=2";
				$curl_response 	= $this->curlCallPost($url,$data);
				$contractdata 	= json_decode($curl_response,true);
				
				if(($contractdata['results']['compdetails']['docid']!='') && ($contractdata['results']['compdetails']['parentid']!=''))
				{
					// nothing to do as bform data exists
				}else{
					$paramsSend	=	array();
					$paramsSend['root_parentid']=	$row_doc_data['root_parentid'];
					$paramsSend['data_city']	=	$row_doc_data['data_city'];	
					$paramsSend['module']		=	'ME';
					$paramsSend['ucode']		=	$row_doc_data['ucode'];	
					$paramsSend['uname']		=	$row_doc_data['uname'];
					$paramsSend['action']		=	'dbformsubmit';
					
					
					$paramsSend['docdata']		=	$row_doc_data['docdata'];
					$paramsSend['post_data']		=	1;
					$paramsSend['newconcall']		=	1;
					
					$doc_api_url	=	$this->jdbox_url."/services/doc_bform_api.php";
					$curl_response 	= $this->curlCallPost($doc_api_url,$paramsSend);
				}
			}
		}
	}
	private function docidCreator()
	{
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			switch(strtoupper($this->data_city))
			{
				case 'MUMBAI':
					$docid = "022".$this->parentid;
					break;
					
				case 'DELHI':
					$docid = "011".$this->parentid;
					break;
					
				case 'KOLKATA':
					$docid = "033".$this->parentid;
					break;
					
				case 'BANGALORE':
					$docid = "080".$this->parentid;
					break;
					
				case 'CHENNAI':
					$docid = "044".$this->parentid;
					break;
					
				case 'PUNE':
					$docid = "020".$this->parentid;
					break;
					
				case 'HYDERABAD':
					$docid = "040".$this->parentid;
					break;
					
				case 'AHMEDABAD':
					$docid = "079".$this->parentid;
					break;	
						
				default :
					$docid_stdcode 	= $this->stdcodeInfo();
					if($docid_stdcode){
						$temp_stdcode = ltrim($docid_stdcode,0);
					}
					$ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
					if(in_array(strtoupper($this->data_city),$ArrCity)){
						$sqlStdCode	= "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
						$resStdCode = parent::execQuery($sqlStdCode, $this->conn_local);
						$rowStdCode =  parent::fetchData($resStdCode);
						$cityStdCode	=  $rowStdCode['stdcode'];
						if($temp_stdcode == ""){
							$stdcode = ltrim($cityStdCode,0);
							$stdcode = "0".$stdcode;				
						}else{
							$stdcode = "0".$temp_stdcode;				
						}
						
					}else{
						$stdcode = "9999";
					}	
					$docid = $stdcode.$this->parentid;
			}
		}
		else
		{
			$docid = "022".$this->parentid;
		}
		return $docid;
	}
	private function stdcodeInfo()
	{
		$sql_stdcode = "SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
		$res_stdcode = parent::execQuery($sql_stdcode, $this->conn_local);
		if($res_stdcode){
			$row_stdcode	=	parent::fetchData($res_stdcode);
			$stdcode 		= 	$row_stdcode['stdcode'];	
			if($stdcode[0]=='0'){
				$stdcode = $stdcode;
			}else{
				$stdcode = '0'.$stdcode;
			}
		}
		return $stdcode;
	}
	public function sourceCode()
	{
		$res_arr = array();
		$source_code = '';
		if($this->me_jda_flag == 1){
			if($this->existing_flag == 1){
				$sName = 'JDA - Existing Data';
			}else{
				$sName = 'JDA - New Data';
			}
		}else{
			if($this->existing_flag == 1){
				$sName = 'ME EXISTING DATA';
			}else{
				$sName = 'ME NEW DATA';
			}
		}
		$sqlSourceCode = "SELECT sCode FROM source WHERE sName = '".$sName."'";
		$resSourceCode = parent::execQuery($sqlSourceCode, $this->conn_local);
		if($resSourceCode && parent::numRows($resSourceCode)>0){
			$row_source 	= parent::fetchData($resSourceCode);
			$source_code 	= $row_source['sCode'];
		}
		$res_arr['source_code'] = $source_code;
		$res_arr['sName'] 		= $sName;
		return $res_arr;
	}
	function timingProcess($timing){
		$newtiming = $timing;
		if($timing){
			$timing_arr = explode(",",$timing);
			$new_timing_arr = array();
			foreach($timing_arr as $timeval){
				$new_timing_arr[] = trim($timeval,'-');
			}
			if(count($new_timing_arr)>0){
				$newtiming  = implode(",",$new_timing_arr);
				$newtiming = trim($newtiming,",");
			}
		}
		return $newtiming;
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
		$paramsLog['EXTRAMSG']	= 'savenonpaidjda';
		$sendLogsApi  			= $this->curlCallPost($api_url, $paramsLog);
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
	
	function update_lock_company()
	{
		$sqlInsrtLockCompanyFlg  = "INSERT INTO tbl_lock_company SET
										parentId = '".$this->parentid."',
										updateBy = '".$this->ucode."',
										updatedDate = NOW(),
										UpdateFlag = '1',
										source='Save as Free Listing'
										ON DUPLICATE KEY UPDATE
										updateBy = '".$this->ucode."',
										updatedDate = NOW(),
										UpdateFlag = '1' ,
										source='Save as Free Listing'
										";
		$resInsrtLockCompanyFlg   = parent::execQuery($sqlInsrtLockCompanyFlg, $this->conn_local);
		
		
	}
	
	function save_freelisting_landline_info(){
		//parentid, landline, updated_time, usercode,username,source
		
		if($this->landline && $this->genInfoArr['mobile']==''){
			
			$landline = explode(",",$this->landline);
			foreach($landline as $key=>$value){
			$sqlInsert = "INSERT INTO tbl_saveandexit_landline_info (parentid, landline, usercode,username,updated_time,source)
					 values ('".$this->parentid."','".$value."', '".$this->ucode."', '".$this->uname."', '".date('Y-m-d H:i:s')."', '".$this->module."') ";

				$resInsert  = parent::execQuery($sqlInsert, $this->conn_idc);
			}
			
		}
		
		
	}
	private function mobileChecking(){
		$empty_mobile = 0 ;
		$mobile_arr   = array();
		if($this->genInfoArr['mobile']!=''){
			$mobile_arr =	explode(",",$this->genInfoArr['mobile']);
			$mobile_arr =	array_filter(array_unique($mobile_arr));
		}
		if(count($mobile_arr)<1){
			$message = "Mobile Number is mandatory, Please add atleast one mobile number.";
			echo json_encode($this->sendDieMessage($message));
			die();	
		}
	}
	private	function getGenInfoMain()
	{
		$genInfoArrMain = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['page'] 		= 'save_nonpaid_jda_class';
		$cat_params['skip_log'] 	= 1;		
		$cat_params['fields']		= 'companyname,building_name,landmark,street,area,pincode,city,state,geocode_accuracy_level,latitude,longitude,mobile,landline,tollfree,paid';

		$cat_api_res		= 	array();
		$cat_api_res		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($cat_api_res) && $cat_api_res['errors']['code']==0){
			$genInfoArrMain 		=	$cat_api_res['results']['data'][$this->parentid];
			$genInfoArrMain['numrows'] = count($cat_api_res['results']['data']);
		}

		/*$sql_gen_info = "SELECT companyname, building_name, landmark, street, area, pincode, city, state, geocode_accuracy_level, latitude, longitude,mobile,landline,tollfree,paid FROM tbl_companymaster_generalinfo where parentid= '".$this->parentid."'";
		$res_gen_info = parent::execQuery($sql_gen_info, $this->conn_iro);	
		if ($res_gen_info && parent::numRows($res_gen_info) > 0)
		{
			
			$genInfoArrMain   = parent::fetchData($res_gen_info);
			$genInfoArrMain['numrows'] = parent::numRows($res_gen_info);
		}*/
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
		$cat_params['page'] 		= 'save_nonpaid_jda_class';
		$cat_params['skip_log'] 	= 1;
		$cat_params['fields']		= 'flags,map_pointer_flags,closedown_flag,landline_addinfo,mobile_addinfo,tollfree_addinfo,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search,national_catidlineage_search,freeze,mask,original_creator';

		$res_comp_extra			= 	array();
		$res_comp_extra			=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_comp_extra) && $res_comp_extra['errors']['code']==0){
			$extDetArrMain 		=	$res_comp_extra['results']['data'][$this->parentid];
		}

		/*$sql_comp_extra	= "SELECT flags, map_pointer_flags, closedown_flag, landline_addinfo, mobile_addinfo, tollfree_addinfo,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search, national_catidlineage_search,freeze,mask,original_creator FROM tbl_companymaster_extradetails where parentid= '".$this->parentid."'";
		$res_comp_extra = parent::execQuery($sql_comp_extra, $this->conn_iro);	
		if ($res_comp_extra && parent::numRows($res_comp_extra) > 0)
		{
			$extDetArrMain      = parent::fetchData($res_comp_extra);
		}*/
		return $extDetArrMain;			
	}

	private function getGenInfoShadow()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$genInfoArr 				= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sql_gen_info = "SELECT * FROM tbl_companymaster_generalinfo_shadow where parentid= '".$this->parentid."'";		
			if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){				
				$res_gen_info =  parent::execQuery($sql_gen_info, $this->conn_iro);
			}else{
				$res_gen_info =  parent::execQuery($sql_gen_info, $this->conn_temp);
			}
			if ($res_gen_info && parent::numRows($res_gen_info) > 0)
			{
				$genInfoArr   = parent::fetchData($res_gen_info);
			}
		}
		return $genInfoArr;
	}

	private function getextradetailsInfoShadow()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
			$extDetArr 					= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sql_comp_extra	= "SELECT * FROM tbl_companymaster_extradetails_shadow where parentid= '".$this->parentid."'";		
			if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){				
				$res_comp_extra =  parent::execQuery($sql_comp_extra, $this->conn_iro);
			}else{
				$res_comp_extra =  parent::execQuery($sql_comp_extra, $this->conn_temp);
			}
			if ($res_comp_extra && parent::numRows($res_comp_extra) > 0)
			{
				$extDetArr      =  parent::fetchData($res_comp_extra);
			}
		}
		return $extDetArr;			
	}

	private function getIntermediateData()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$intermedArr 				= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sql_comp_int	= "SELECT * FROM tbl_temp_intermediate where parentid= '".$this->parentid."'";		
			$res_comp_int   =  parent::execQuery($sql_comp_int, $this->conn_temp);
			
			if ($res_comp_int && parent::numRows($res_comp_int) > 0)
			{
				$intermedArr      =  parent::fetchData($res_comp_int);	
			}
		}
		return $intermedArr;
	}

	private function getBusinessTemData()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$rowSloganCatid 			= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$selSloganCatid = "SELECT * FROM tbl_business_temp_data WHERE contractid ='".$this->parentid."'";		
			$resSloganCatid =  parent::execQuery($selSloganCatid, $this->conn_temp);
			
			if($resSloganCatid && parent::numRows($resSloganCatid)>0)
			{
				$rowSloganCatid = parent::fetchData($resSloganCatid);
			}
		}	
		return $rowSloganCatid;
	}
	
	private function getCurlURL()
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

	private function curlCallPost($curlurl,$input_arr)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $input_arr);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}

	private function getValidCategories($total_catlin_arr)
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

	private function isPhoneSearchCampaignExists()
	{
		//1 means paid
		$isPaid = 0;
		$sqlNationalListing = "SELECT parentid FROM tbl_companymaster_finance_national WHERE parentid = '".$this->parentid."'  AND balance > 0";
		$resNationalListing = parent::execQuery($sqlNationalListing,$this->conn_national);
		if($resNationalListing && parent::numRows($resNationalListing)>0){
			$isPaid = 1;
		}
		else
		{
			
			$sqlUnApprovedChk = "SELECT parentid,approvalStatus FROM payment_instrument_summary WHERE parentid = '".$this->parentid."' AND approvalStatus = 0";
			$resUnApprovedChk = parent::execQuery($sqlUnApprovedChk,$this->conn_fnc);
			if($resUnApprovedChk && parent::numRows($resUnApprovedChk)>0)
			{
				$isPaid = 1;
			}	
			else
			{
				$shadow_data = 0;
				$live_data = 0;
				$sqlPaidShadowInfo = "SELECT parentid FROM tbl_companymaster_finance_shadow WHERE parentid='".$this->parentid."' AND campaignid IN ('1','2')";
				$resPaidShadowInfo = parent::execQuery($sqlPaidShadowInfo,$this->conn_fnc);
				if($resPaidShadowInfo && parent::numRows($resPaidShadowInfo)>0)
				{
					$shadow_data = 1;
				}			
				$sqlPaidApprovedInfo = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid IN ('1','2')";
				$resPaidApprovedInfo = parent::execQuery($sqlPaidApprovedInfo,$this->conn_fnc);
				if($resPaidApprovedInfo && parent::numRows($resPaidApprovedInfo)>0)
				{
					$live_data = 1;
				}
				
				if(($shadow_data == 1) || ($live_data == 1))
				{
					$isPaid = 1;
					$sqlPaidExpiredCheck = "SELECT parentid,IF(IFNULL(mask,0) = 0 AND IFNULL(freeze,0) = 0 AND MIN(expired)=0,1,2)AS paid_flag,IF(MAX(expired_on) < DATE_SUB(CURDATE(),INTERVAL 3 MONTH),'Expired','Active' ) AS exp,MAX(expired_on) FROM tbl_companymaster_finance WHERE parentid ='".$this->parentid."'";
					$resPaidExpiredCheck = parent::execQuery($sqlPaidExpiredCheck,$this->conn_fnc);
					if($resPaidExpiredCheck && parent::numRows($resPaidExpiredCheck)>0)
					{
						$row_paid_expired = parent::fetchData($resPaidExpiredCheck);
						if($row_paid_expired['paid_flag'] == 2 && $row_paid_expired['exp'] == "Expired")
						{
							$isPaid = 0;
						}
					}
				}
			}
		}
		
		return $isPaid;
	}
	function getNationalCatlineage($catid)
	{
		if(!empty($catid))
		{
			//$catid_list				=	str_replace("/","'",$catid);
			//$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list.")";
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->conn_local);
			
			$cat_params = array();
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,national_catid';	
			$cat_params['page'] ='save_nonpaid_jda_class';
			$where_arr  	=	array();			
			$where_arr['catid']			= str_replace("/","",$catid);			
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
					$arr_national_catids[] = $cat_arr['national_catid']; 
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
		$resStd	= parent::execQuery($sqlStd, $this->conn_local);
		$rowStd = parent::fetchData($resStd);
		
		return $rowStd;
	}
	
	function getSphinxId()
	{
		$sphinxid = 0;
		$sqlSphinxID = "SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$resSphinxID =  parent::execQuery($sqlSphinxID, $this->conn_iro);
		if($resSphinxID && parent::numRows($resSphinxID)>0){
			$row_sphinx_id  =  parent :: fetchData($resSphinxID);
			$sphinxid = intval($row_sphinx_id['sphinx_id']);
		}
		return $sphinxid;
	}

	private function loggingIntoTable()
	{
		$sqlInsertLog	=	"INSERT INTO d_jds.tbl_savenonpaid_jda_log 
							SET 
							parentid = '".$this->parentid."',
							data_city = '".$this->data_city."',
							params = '".json_encode($this->logFields)."',
							updatedby='".$this->ucode."(".$this->uname.")"."',
							updatedon= '".date("Y-m-d H:i:s")."'";
		$resInsertLog 	= parent::execQuery($sqlInsertLog,$this->conn_data);
	}
	function handleFreezing()
	{
		// Temporary Freeze Handling
		
		$tempFreezArr = array();
		$tempFreezArr = $this->chkTempFrzStatus();
		if(count($tempFreezArr)>0)
		{
		
			$rmv_reason  = "UnFreezing Contract JDA - Save As Nonpaid";
			$this->removeTempFrzContract();
			$this->tempFrzContractLog($tempFreezArr['temp_deactive_start'],$tempFreezArr['temp_deactive_end'],$tempFreezArr['updtflg'],$rmv_reason);
		}
		$sqlContractReason = "SELECT reasons FROM tbl_contract_reasons WHERE contractid = '".$this->parentid."'";
		$resContractReason = parent::execQuery($sqlContractReason,$this->conn_local);
		if($resContractReason && parent :: numRows($resContractReason)>0)
		{
			$sqlDeleteReason = "DELETE FROM tbl_contract_reasons WHERE contractid='".$this->parentid."'";
			$resDeleteReason = parent::execQuery($sqlDeleteReason,$this->conn_local);
		}
		
		// Freez / Mask Old State Check and Maintain Logs as we are going to send data in unfreeze / unmask state
		
		if($this->extraDetailsArrMain['freeze'] == 1)
		{
			$unfrz_reason  = "UnFreezing Contract JDA - Save As Nonpaid";
			$sqlInsrtFrzLog = "INSERT INTO tbl_compfreez_details(parentid, reason, createdBy, freez, date_time) VALUE('".$this->parentid."', '".$unfrz_reason."', '".$this->ucode."', '0', NOW())";
			$resInsrtFrzLog = parent::execQuery($sqlInsrtFrzLog,$this->conn_local);
		}
		if($this->extraDetailsArrMain['mask'] == 1)
		{
			$unmsk_reason  = "UnMasking Contract JDA - Save As Nonpaid";
			$sqlInsrtMaskLog = "INSERT INTO tbl_compMask_details(parentid, reason, createdBy, mask, date_time) VALUE('".$this->parentid."', '".$unmsk_reason."', '".$this->ucode."', '0', NOW())";
			$resInsrtMaskLog = parent::execQuery($sqlInsrtMaskLog,$this->conn_local);
		}
		
	}
	function chkTempFrzStatus()
	{
		$tempFrzArr = array();
		$sqlFrzUnFrzStatus = "SELECT parentid,update_flag,temp_deactive_start,temp_deactive_end,companyname FROM tbl_temp_deactivate_contracts WHERE parentid = '".$this->parentid."'" ;
		$resFrzUnFrzStatus = parent::execQuery($sqlFrzUnFrzStatus,$this->conn_local);
		if($resFrzUnFrzStatus && parent :: numRows($resFrzUnFrzStatus)>0)
		{
			$row_temp_frz = parent :: fetchData($resFrzUnFrzStatus);
			$tempFrzArr['updtflg'] = $row_temp_frz['update_flag'];
			$tempFrzArr['temp_deactive_start'] = $row_temp_frz['temp_deactive_start'];
			$tempFrzArr['temp_deactive_end'] = $row_temp_frz['temp_deactive_end'];
			$tempFrzArr['companyname'] = $row_temp_frz['companyname'];
		}
		return $tempFrzArr;
	}
	function removeTempFrzContract()
	{
		$is_deleted = 0;
		$sqlRemoveContracts = "DELETE FROM tbl_temp_deactivate_contracts WHERE parentid = '".$this->parentid."'";
		$resRemoveContracts = parent::execQuery($sqlRemoveContracts,$this->conn_local);
		if($resRemoveContracts)
		{
			$is_deleted = 1;
		}
		return $is_deleted;
	}
	function tempFrzContractLog($from,$to,$updtflg,$process)
	{
		$sqlTempFrzLog = "INSERT INTO tbl_temp_deactivate_contracts_log SET 
						  parentid 				= '".$this->parentid."',
						  companyname 			= '".addslashes($this->genInfoArr['companyname'])."',
						  temp_deactive_start 	= '".$from."',
						  temp_deactive_end 	= '".$to."',
						  update_flag      		= '".$updtflg."',
						  usercode        		= '".$this->ucode."',
						  username        		= '".$this->ucode."',
						  updatedate      		= NOW(),
						  process_name    		= '".$process."'";
		$resTempFrzLog = parent::execQuery($sqlTempFrzLog,$this->conn_local);
	}
	private	function phoneSearchArr()
	{
		$phone_searchArr = array();
		if(trim($this->genInfoArr['mobile_display']))
		$phone_searchArr[]	=	trim($this->genInfoArr['mobile_display']);
		if(trim($this->genInfoArr['landline_display']))
			$phone_searchArr[]	=	trim($this->genInfoArr['landline_display']);
		if(trim($this->genInfoArr['tollfree_display']))
			$phone_searchArr[]	=	trim($this->genInfoArr['tollfree_display']);
		if(trim($this->genInfoArr['fax']))
			$phone_searchArr[]	=	trim($this->genInfoArr['fax']);
		if(trim($this->interMediateTable['virtualNumber']))
			$phone_searchArr[]	=	trim($this->interMediateTable['virtualNumber']);
		if(trim($this->genInfoArr['tollfree']))
			$phone_searchArr[]	=	trim($this->genInfoArr['tollfree']);	
		$phone_searchArr = array_merge(array_filter(array_unique($phone_searchArr)));
		$phone_search	=	implode(',',$phone_searchArr);
		return  $phone_search;
	}

	private function updateContractHavingTollFreeNum()
	{
		$genralInfoArr1 = array();
		$extraDetailsArr1 = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id,extra_det_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'mobile,landline,tollfree,flags';
		$cat_params['page'] 		= 'save_nonpaid_jda_class';

		$res_comp_gen1		= 	array();
		$res_comp_gen1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_comp_gen1) && $res_comp_gen1['errors']['code']==0){
			$genralInfoArr1 		=	$res_comp_gen1['results']['data'][$this->parentid];
		}

		/*$sql_gen_info1 = "select mobile,landline,tollfree from tbl_companymaster_generalinfo where parentid= '".$this->parentid."'";
		$res_gen_info1   = parent::execQuery($sql_gen_info1, $this->conn_iro);
		if($res_gen_info1 && parent::numRows($res_gen_info1))
		{
			$genralInfoArr1=parent::fetchData($res_gen_info1);		
		}*/	
		
		//~ $cat_params['table'] 		= 'extra_det_id';
		//~ $cat_params['fields']		= 'flags';

		//~ $res_comp_extra1		= 	array();
		//~ $res_comp_extra1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		
		$extraDetailsArr1 		=	$genralInfoArr1;
		

		/*$sql_comp_extra1    = "select flags from tbl_companymaster_extradetails where parentid= '".$this->parentid."'"; // Old flags entry.
		$res_comp_extra1   = parent::execQuery($sql_comp_extra1, $this->conn_iro);
		if($res_comp_extra1 && parent::numRows($res_comp_extra1))
		{
			$extraDetailsArr1=parent::fetchData($res_comp_extra1);
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
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['page'] 		='save_nonpaid_jda_class';		
		$cat_params['return']		= 'associate_national_catid';	

		$where_arr  	=	array();			
		$where_arr['catid']		= $catidliststr;				
		$cat_params['where']	= json_encode($where_arr);
		
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
		{
			//$row = parent::fetchData($res);
			$associate_national_catid_arr = array();
			foreach ($cat_res_arr['results'] as $key => $cat_arr) {
				if($cat_arr['associate_national_catid']!=''){
					$associate_national_catid_arr[] = $cat_arr['associate_national_catid'];
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
				$cat_params['page'] ='save_nonpaid_jda_class';
				$cat_params['data_city'] 	= $this->data_city;		
				$cat_params['return']		= 'catid';
				$cat_params['skip_log']		= '1';		

				$where_arr  	=	array();			
				$where_arr['national_catid']	= $associate_national_catid_str;
				$where_arr['catid']				= "!".$catidliststr;				
				$cat_params['where']	= json_encode($where_arr);
				
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
				{
					//$row = parent::fetchData($res);
					foreach ($cat_res_arr['results'] as $key => $cat_arr) {						
						$parent_categories = $cat_arr['catid'];
						if($parent_categories!=''){
							$parent_categories_arr[] = $parent_categories;
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
	private function generateRandomString($length = 15) 
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}

	private function jdboxCurlCall($param) 
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
	private function sendDieMessage($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}

}//class ends here


?>
