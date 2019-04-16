<?php 

class saveNonPaidData extends DB
{
	var $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']); 
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		$ucode				= trim($params['ucode']);
		$uname				= trim($params['uname']);
		$landline 			= trim($params['landline']);
		//$me_jda_flag 		= trim($params['me_jda_flag']);

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
			$message = "User Code is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($uname)=='')
		{
			$message = "User Name is blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}

		$this->validationcode	=	'SAVENONPAIDDATA';
		$this->data_city 		= $data_city;
		
		$this->categoryClass_obj = new categoryClass();
		$this->emptype = $params['emptype'];
		$this->logFields = array();
		$this->logFields['params'] = $params;

		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode		= $ucode;
		$this->uname 		= $uname;
		$this->landline     = $landline;
		$this->me_jda_flag  = $me_jda_flag;
		
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
		$this->companyClass_obj 	= new companyClass();

		$urls = $this->getCurlURL($this->data_city);
		
		$this->cs_url	 = $urls['url'];
		$this->jdbox_url = $urls['jdbox_url'];	
		
		$this->spinxId	 = $this->getSphinxId();
		$this->docid	 = $this->getDocId();
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
		$this->genInfoPaid	=	$this->getPaidStatus($this->parentid,$this->data_city);
		
		$this->extraDetailsArrMain    = $this->getextradetailsInfoMain();
		$this->genInfoArr         	= $this->getGenInfoShadow();
		
		if($this->landline==''){
			$this->landline = $this->genInfoArr['landline'];
		}
		
		$this->extraDetailsArr    = $this->getextradetailsInfoShadow();
		$this->interMediateTable  = $this->getIntermediateData();
		
		$web_arr = array();
		
		if($this->genInfoArr['website'] != "")
		{
			$web_arr = explode(",",$this->genInfoArr['website']);
			$web_arr = array_filter($web_arr);
		}		
		
		
		if(count($web_arr) == 0 && ((int)$this->extraDetailsArr['flags'] & 512)==512)
		{
			$message = "Please enter Website for dot com contract";
			echo json_encode($this->sendDieMessage($message));
			die();
		}		
		
		if(count($this->genInfoArr) <=0)
		{
			$message = "Data Not Saved.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['state']) == '' && !(((int)$this->extraDetailsArr['flags'] & 512)==512))
		{
			$message = "State is Blank.";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['city']) == '' && !(((int)$this->extraDetailsArr['flags'] & 512)==512))
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
		if($this->phonesearch_flag!=1 || $this->genInfoPaid == 0)
		{
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
		
		if(count($this->business_temp_catid_arr)<=0 && $this->extraDetailsArr['nocategory'] == 0){
			$message = "No Category Found !!!";
			echo json_encode($this->sendDieMessage($message));
			die();
		}
		if(trim($this->genInfoArr['companyname']) != '')
		{
			//$this->validateGlobalAPI();
		}		
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
    
	public function finalInsert()
	{				
	
		$post_arr = array();
		
	 
		if(0)//strtoupper($this->action) == 'NONPAID_DATA')
		{			
			
			if(count($this->extraDetailsArrMain)>0){
				$this->extraDetailsArr['catidlineage_nonpaid'] 			= $this->extraDetailsArrMain['catidlineage_nonpaid'];
				$this->extraDetailsArr['national_catidlineage_nonpaid'] = $this->extraDetailsArrMain['national_catidlineage_nonpaid'];;
				$this->extraDetailsArr['catidlineage_search']  			= $this->extraDetailsArrMain['catidlineage_search'];
				$this->extraDetailsArr['national_catidlineage_search']  = $this->extraDetailsArrMain['national_catidlineage_search'];
				$catidLineage  									=  $this->extraDetailsArrMain['catidlineage'] ;				
				
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
				$this->logFields['comment']			= 'NONPAID DATA - Entry Found In Extradetails Table';
				
			}
			else
			{
				$catidLineage = '';
				$post_arr['nocategory']				= 1;
				$this->extraDetailsArr['catidlineage_nonpaid'] 			= null;
				$this->extraDetailsArr['national_catidlineage_nonpaid'] = null;
				$this->extraDetailsArr['catidlineage_search']  			= null;
				$this->extraDetailsArr['national_catidlineage_search']  = null;
				$hotcategory						= null;
				$category_count						= null;
				$this->logFields['comment']			= 'BFORMEXIT - No Entry Found In Extradetails Table';
			}
		}
		else
		{
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
		//$update_business_temp_data = $this->update_attributes_withcats();
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
		if(is_array($this->business_temp_data) && count($this->business_temp_data)>0)
		{
			$catid_arr			=	explode('|P|',trim($this->business_temp_data['catIds'],'|P|'));
			$national_catid_arr	=	explode('|P|',trim($this->business_temp_data['nationalcatIds'],'|P|'));
			
			$post_arr['catidlineage']	=	"/".implode('/,/',$catid_arr)."/";
			$post_arr['national_catidlineage']	= "/".implode('/,/',$national_catid_arr)."/";
			$post_arr['ucode']	= $this->business_temp_data['updatedBy'];
			$post_arr['uname']	= $this->business_temp_data['name_code'];
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
		
		$param_attr_arr = array();
		$param_attr_arr['parentid']   = $post_arr['parentid'];
		$param_attr_arr['data_city']  = $post_arr['data_city'];		
		$param_attr_arr['module']     = 'DE';
		$param_attr_arr['source']     = 'DE';
		$param_attr_arr['ucode']      = trim($this->ucode);
		$param_attr_arr['uname']      = trim($this->uname);
		$param_attr_arr['action']     = 'add_remove_attr';
		
		$curl_url_attr 	= $this->jdbox_url."/services/attributes_dealclose.php";				
		$resmsg_attr = $this->curlCallPost($curl_url_attr,$param_attr_arr);
		
		$post_arr['landline_addinfo'] 		= $this->extraDetailsArr['landline_addinfo'];
		$post_arr['mobile_addinfo'] 		= $this->extraDetailsArr['mobile_addinfo'];
		$post_arr['tollfree_addinfo'] 		= $this->extraDetailsArr['tollfree_addinfo'];
		$post_arr['contact_person_addinfo'] = $this->extraDetailsArr['contact_person_addinfo'];
		$post_arr['attributes'] 			= $this->business_temp_data['mainattr'];
		$post_arr['attributes_edit'] 		= $this->business_temp_data['facility'] ;
		$post_arr['turnover'] 				= $this->extraDetailsArr['turnover'];	
		
 		$attr_search    					= $this->getextradetailsInfoShadow();
		$post_arr['attribute_search']		= $attr_search['attribute_search']; 

		
				
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

		$post_arr['guarantee'] 				= $this->interMediateTable['guarantee'];
		$post_arr['contract_calltype'] 		= $this->interMediateTable['contract_calltype'];
		if($this->interMediateTable['deactflg'] == '1')
			$post_arr['deactflg'] 				= 'F';
		else	
			$post_arr['deactflg'] 				= 'N';
		$post_arr['display_flag'] 			= $display;
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
		
		$post_arr['serviceName'] 			= $this->interMediateTable['serviceName'];
		$post_arr['createdby']				= $this->extraDetailsArr['createdby'];
		$post_arr['createdtime']			= $date;
		$post_arr['original_creator']		= $original_creator;
		$post_arr['original_date']			= $date;
		$post_arr['updatedBy'] 				= $this->ucode;
		$post_arr['updatedOn'] 				= $this->extraDetailsArr['updatedOn'];
		
		$cat_params = array();
		$cat_params['page'] 		= 'save_nonpaid_data_class';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,category_name';

		$where_arr  	=	array();
		//print_r($this->extraDetailsArr['misc_flag']);
		//print_r(($this->extraDetailsArr['misc_flag']&512) == 512);
		
		$where_arr['national_catid']		= "10570062";
		$cat_params['where']			= json_encode($where_arr);
		$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		
		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0)
		{
			$res['catid'] = $cat_res_arr['results'][0]['catid'];
			$catidLineage_new 	= explode("/,/",trim($catidLineage,"/"));
			if(($this->extraDetailsArr['misc_flag']&512) == 512)
			{
				if(!in_array($res['catid'],$catidLineage_new))
				{
					array_push($catidLineage_new,$res['catid']);
				}
			}
			else if(($this->extraDetailsArr['misc_flag']&512) != 512)
			{
				if (($key = array_search($res['catid'], $catidLineage_new)) !== false) {
					unset($catidLineage_new[$key]);
				}
			}
			$catidLineage = '/'.implode('/,/',$catidLineage_new).'/';
		}
		
		$post_arr['catidlineage']			= $catidLineage;
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
		$post_arr['misc_flag']				= $this->extraDetailsArr['misc_flag'];
		
		$post_arr['phone_search']			= $phone_search_res;//??		
		$post_arr['address']				= addslashes($address);
		$post_arr['length']					= strlen($this->genInfoArr['companyname']);
		$post_arr['prompt_flag']			= $this->interMediateTable['prompt_flag'];
		$post_arr['contractid'] 			= $this->extraDetailsArr['parentid'];
		$post_arr['compname'] 				= addslashes(stripslashes($this->genInfoArr['companyname']));		
		$post_arr['paidstatus'] 			= $this->genInfoPaid;
		$post_arr['freez'] 					= 0;
		//print_r($this->interMediateTable);;
		
		
		$dot_com_flag = (int) $post_arr['flags'] & 512;
		//$mainsource							= $this->sourceCode();
		$mainsource							= $this->interMediateTable['mainsource'];
		if(!empty($mainsource))
		{
			
			$post_arr['subsource'] 			= $this->interMediateTable['subsource'];
			$post_arr['datesource'] 		= date("Y-m-d H:i:s");
			$post_arr['contactId']  		= $this->parentid;
			
			$source_ino_arr 				= 	$this->getSourceInfo($mainsource);
			
			$OldSourceInfo 					= 	$this->getOldSourceInfo($source_ino_arr['SName']);
			if(!empty($OldSourceInfo) && !empty($OldSourceInfo['scode']))
			{
				$post_arr['mainsource'] 		= $OldSourceInfo['scode'];
			}
			else
				$post_arr['mainsource'] 		= $mainsource;
			$post_arr['universal_source'] 	= 	$source_ino_arr['SName'];
		}
		else
		{
			$mainsource							= $this->sourceCode();
			$post_arr['universal_source'] = 	'CS';
		}
		$post_arr['contact_details']		= $phone_search_res;
		$post_arr['arealineage'] 			= $areaLineage;
		$post_arr['data_source']			= 'SaveNpJDA';
		$post_arr['datasource_date']		= $date;
		$post_arr['empCode']				= trim($this->ucode);
		$post_arr['session_key'] 			= $this->generateRandomString(15);
		$post_arr['source']  				= trim($this->module);
		$post_arr['ucode']  	 			= trim($this->ucode);
		$post_arr['uname']   				= trim($this->uname);
		$post_arr['flow_module']   			= 'DE';
		$post_arr['module']   				= 'CS';
		$post_arr['htmldump'] 				= $htmldump;
		$post_arr['sloganstr'] 				= $sloganstr;			
		$post_arr['flow_module'] 			= 'DE';
		$post_arr['award'] 					= $this->extraDetailsArr['award'];
		$post_arr['testimonial'] 			= $this->extraDetailsArr['testimonial'];
		$post_arr['proof_establishment']	= $this->extraDetailsArr['proof_establishment'];
		$post_arr['tag_catid']				= $this->extraDetailsArr['tag_catid'];
		$post_arr['tag_catname']			= $this->extraDetailsArr['tag_catname'];
		$old_closedown_flag 			    = $this->extraDetailsArrMain['closedown_flag'];
		$new_closedown_flag 				= $this->extraDetailsArr['closedown_flag'];
		$post_arr['company_trademark_flag'] = $this->extraDetailsArr['company_trademark_flag'];

		if(intval(trim($old_closedown_flag)) != intval(trim($new_closedown_flag)))
		{
			$post_arr['closedown_date'] = date("Y-m-d H:i:s");
			$post_arr['closedown_flag'] = $this->extraDetailsArr['closedown_flag'];
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
		/*
		require 'geoCodeClass.php';

		$param_new['parentid']=$this->parentid;
		$param_new['action']=3;
		$param_new['data_city']=$this->data_city;
		$param_new['radius']=10;
		$param_new['stage']=0;
		$param_new['module']='CS';
		
		
		$updateEntry = "UPDATE online_regis.tbl_checkGeocodes SET dealclose_flag = 2 WHERE parentid = '".$this->parentid."' ";
		$condata =  parent::execQuery($updateEntry, $this->conn_idc);
		$param_pincodecheck = array();
		$param_pincodecheck['parentid']=$this->parentid;
		$param_pincodecheck['docid']=	$this->docidCreator();
		$param_pincodecheck['action']=5;
		$param_pincodecheck['data_city']=$this->data_city;
		$param_pincodecheck['module']="CS";
		$geoCodeClassObj = new geoCodeClass($param_pincodecheck);
		$curl_arr = $geoCodeClassObj->pincodeCheck();
		
		if($curl_arr['error']['code'] == 0)
		{
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
				$geoCodeClassObj = new geoCodeClass($param_new);
				$curl_arr = $geoCodeClassObj->getBestLatLong();
				if($curl_arr['error']['code']=='0'){
					$checked=true;
					$ret_param['latitude']					= $curl_arr['data']['latitude'];
					$ret_param['longitude']					= $curl_arr['data']['longitude'];
					$ret_param['geocode_accuracy_level']		= 1;
					$ret_param['upt']		= 1;
					$ret_param['flags']					= $curl_arr['data']['flags'];
					$ret_param['map_pointer_flags']		= $curl_arr['data']['map_pointer_flags']; 
					
				}
				else{
					$ret_param['upt']		= 0;
					
				}
			}
		}
		else
		{
			$geoCodeClassObj = new geoCodeClass($param_new);
			$curl_arr = $geoCodeClassObj->getBestLatLong();
			if($curl_arr['error']['code']=='0'){
				$checked=true;
				$ret_param['latitude']					= $curl_arr['data']['latitude'];
				$ret_param['longitude']					= $curl_arr['data']['longitude'];
				$ret_param['geocode_accuracy_level']		= 1;
				$ret_param['upt']		= 1;
				$ret_param['flags']					= $curl_arr['data']['flags'];
				$ret_param['map_pointer_flags']		= $curl_arr['data']['map_pointer_flags']; 
				
			}
			else
			{
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
		else*/
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
		
		
		if(((int)$post_arr['flags'] & 512) != 512)
		{
			$post_arr['flags'] = $post_arr['flags'] + $dot_com_flag;	
		}
		/*
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
		*/
		$post_arr['paid'] = 0;
		$newNarraion = trim($this->interMediateTable['narration']);
		if(trim($newNarraion)!= '')
		{
				$naration = date("l dS M, Y H:i:s")."\n  ".$newNarraion."\n -  ".$this->uname;
				$naration = nl2br($naration." \n\n");
				
				$sqlInsert = "INSERT INTO d_jds.tbl_paid_narration SET
				contractid = '".$post_arr['parentid']."',
				parentid = '".$post_arr['parentid']."',
				narration = '".addslashes($naration)."',
				data_city = '".addslashes($this->data_city)."',
				creationDt = now(),
				createdBy = '".$this->ucode."'";
				$res_sqlInsert =  parent::execQuery($sqlInsert, $this->conn_iro);
		}
	//	if(!empty($this->interMediateTable['add_infotxt']))
	//	{
			$sqlCheck = "SELECT autoid FROM d_jds.tbl_comp_addInfo WHERE contractId = '".$post_arr['parentid']."'";
			//$resCheck = $conn_decs->query_sql($sqlCheck);
			$resCheck =  parent::execQuery($sqlCheck, $this->conn_iro);
		

				if($resCheck && mysql_num_rows($resCheck)){
					$flgInsertNarration = false;
				}else{
					$flgInsertNarration = true;
				}
				unset($resCheck, $sqlCheck);
				if(!empty($this->interMediateTable['add_infotxt']))
				{
					$insert_addInfo = "INSERT INTO d_jds.tbl_comp_addInfo
					SET 
					contractId = '".$post_arr['parentid']."',
					parentid = '".$post_arr['parentid']."',
					lockDateTime = now(),
					data_city  = '".$post_arr['data_city']."',
					add_infotxt =  \"".addslashes($this->interMediateTable['add_infotxt'])."\"";
				}
				/*else{
					$insert_addInfo = "UPDATE d_jds.tbl_comp_addInfo SET lockDateTime = now(),add_infotxt=\"".addslashes($this->interMediateTable['add_infotxt'])."\" WHERE contractId = '".$post_arr['parentid']."'";
				}*/
				
				//$res_insert_add =  parent::execQuery($insert_addInfo, $this->conn_iro);	
				
			
			
	//	}
		

		//echo $curl_url_attr."?".http_build_query($param_attr_arr);
		
		$post_arr['add_infotxt'] = $this->interMediateTable['add_infotxt'];
		
		$this->insertbypassexculsion();
		$this->InsertMoviesTimeShadowToMain();
		$this->insertNationalListingDetails();
		$post_arr['sm_val'] = '1';				
		
		$post_arr['calling_source'] = 'delite';
		
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
		
		$this->update_lock_company();	
		//$this->save_freelisting_landline_info();	
		$this->updateContractHavingTollFreeNum();
		//$this->insertCompanySource($post_arr);
		//$docbformdata = $this->pendingDocBformData();
		
		
		
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";	
		$resLog['result'] = $result_msg_arr;
		//$this->functionForSendLogs($resLog);
		return $result_msg_arr;

	}
	
	function insertbypassexculsion()
	{
		$sql_exc 	 = "select * from tbl_contract_bypass_exclusion_temp where parentid = '".$this->parentid."' and reasonid=5";
		$res_exc =  parent::execQuery($sql_exc, $this->conn_iro);
		$Numsrows = mysql_num_rows($res_exc);
		
		$sql_exc_main 	 = "select * from tbl_contract_bypass_exclusion where parentid = '".$this->parentid."' and reasonid=5";
		$res_exc_main =  parent::execQuery($sql_exc_main, $this->conn_iro);
		$Numsrows_main = mysql_num_rows($res_exc_main);
		
		
		
		if($Numsrows > 0 && $Numsrows_main == 0)
		{
			$sql_excl_main =   "INSERT INTO tbl_contract_bypass_exclusion 
								SET 
								parentid='".$this->parentid."',
								reasonid =5,
								updatedby= '".$this->ucode."',
								updatedon = NOW()
								ON DUPLICATE KEY UPDATE									 		
								updatedby= '".$this->ucode."',
								updatedon = NOW() ";
			$res_main =  parent::execQuery($sql_excl_main, $this->conn_iro);					
		}
		
		if($Numsrows == 0 && $Numsrows_main > 0)
		{
				$sql_excl_delete =  "Delete from tbl_contract_bypass_exclusion where parentid='".$this->parentid."' and reasonid = 5";
				$res_excl_delete =  parent::execQuery($sql_excl_delete, $this->conn_iro);		
		}	
		
	}	
	
	function showFromTemp()
	{
		$sql = "SELECT * from d_jds.tbl_national_listing_temp where parentid = '".$this->parentid."'";
		$qry =  parent::execQuery($sql, $this->conn_iro);

		if($qry && mysql_num_rows($qry))
		{
			$row	= mysql_fetch_assoc($qry);
		}
		
		return $row;
	}
	function insertNationalListingDetails()
	{
		$row_sel	= $this->showFromTemp();
		if($this->genInfoPaid	== 1){
			$qry= ",approval_flag = 1";
			$paidflg = 0;
		}else{
			$paidflg = 1;
		}
		if(count($row_sel))
		{
			$sql="INSERT INTO tbl_national_listing 
					SET
						parentid 				='".$row_sel['parentid']."',
						Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
						Category_nationalid		='".$row_sel['Category_nationalid']."',
						TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
						totalcityweight			='".$row_sel['totalcityweight']."',
						contractCity			='".addslashes(stripslashes($this->data_city))."',
						ContractStartDate		='".$row_sel['ContractStartDate']."',
						ContractTenure			='".$row_sel['ContractTenure']."',
						dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
						WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
						latitude				='".$row_sel['latitude']."',
						longitude				='".$row_sel['longitude']."',
						iroCard					='".$row_sel['iroCard']."',
						lastupdate				='".$row_sel['lastupdate']."',
						update_flag				='0',
						data_city				='".$this->data_city."',
						state_zone				='".$row_sel['state_zone']."',
						paid					='".$paidflg."',
						short_url				='".$row_sel['short_url']."'".$qry."
						
					ON DUPLICATE KEY UPDATE
						
						Category_city			='".addslashes(stripslashes($row_sel['Category_city']))."',
						Category_nationalid		='".$row_sel['Category_nationalid']."',
						TotalCategoryWeight		='".$row_sel['TotalCategoryWeight']."',
						totalcityweight			='".$row_sel['totalcityweight']."',
						contractCity			='".addslashes(stripslashes($this->data_city))."',
						ContractStartDate		='".$row_sel['ContractStartDate']."',
						ContractTenure			='".$row_sel['ContractTenure']."',
						dailyContribution		='".addslashes(stripslashes($row_sel['dailyContribution']))."',
						WebdailyContribution	='".addslashes(stripslashes($row_sel['WebdailyContribution']))."',
						latitude				='".$row_sel['latitude']."',
						longitude				='".$row_sel['longitude']."',
						iroCard					='".$row_sel['iroCard']."',
						lastupdate				='".$row_sel['lastupdate']."',
						update_flag				='0',
						data_city				='".$this->data_city."',
						state_zone				='".$row_sel['state_zone']."',
						paid					='".$paidflg."',
						short_url				='".$row_sel['short_url']."'".$qry;
						 
			$res =  parent::execQuery($sql, $this->conn_national);
		}
	}
	private function InsertMoviesTimeShadowToMain()
	{		
		
		$movie_details_old = array();
		$movie_details_new = array();
		$resArr  		   = array();
		
		$selTiming = "SELECT parentid FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."'";
		$resTiming =  parent::execQuery($selTiming, $this->conn_iro);

		if(mysql_num_rows($resTiming) > 0)
		{
			if(strtolower($source) != 'singlepagebform')
			{
				$temp_category_arr = $this->deleteCategoriesFromMovieTable();
				if(count($temp_category_arr) > 0)
				{
					$arrImploded = "'".implode("','",$temp_category_arr)."'";
					
					$del = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE catid NOT IN ($arrImploded) AND parentid='".$this->parentid."' ";
					
					$resDel =  parent::execQuery($del, $this->conn_iro);

					
				}
			}	
		}
		
		$old_details = "SELECT GROUP_CONCAT(TIME_FORMAT(movie_timings,'%I:%i %p')  ORDER BY movie_timings SEPARATOR ', ') AS timing,movie_date,index_mv,category_name,catid FROM     db_iro.tbl_movie_timings WHERE parentid='".$this->parentid."' GROUP BY category_name, movie_date ASC";
		
		$resOld_details =  parent::execQuery($old_details, $this->conn_iro);

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
		$resnew_details =  parent::execQuery($new_details, $this->conn_iro);

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
		 if($data_city=='') {
			 $data_city = $server_city;
		 }
		
		 
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
												 city 					= '".$this->data_city."'							
										";
			$resToHistory =  parent::execQuery($InsertToHistory, $this->conn_iro);
			

		 }
		 
		 
		
		$del = "DELETE FROM db_iro.tbl_movie_timings WHERE parentid='".$this->parentid."'";
		$res =  parent::execQuery($del, $this->conn_iro);

		
		$del_TempData = "DELETE  FROM db_iro.tbl_movie_timings_shadow WHERE parentid = '".$this->parentid."' AND movie_date <DATE(NOW())";		
		$resToHistory =  parent::execQuery($del_TempData, $this->conn_iro);
		
	
		
		$selectTempData = "SELECT * FROM db_iro.tbl_movie_timings_shadow WHERE parentid = '".$this->parentid."'";
		$resTempData =  parent::execQuery($selectTempData, $this->conn_iro);
		
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
				
				if($movie_date !='' && $movie_timings !='' && $catid != '')
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
					$resToMain =  parent::execQuery($insertToMain, $this->conn_iro);

					
						
				}
				
			}
		}
	}
	function deleteCategoriesFromMovieTable()
	{
		$temp_category_arr = array();
		$sqlTempCategory    =   "SELECT catids as catidlineage,catidlineage_nonpaid FROM d_jds.tbl_business_temp_data as A JOIN db_iro.tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
		$resTempCategory =  parent::execQuery($sqlTempCategory, $this->conn_iro);							


		if($resTempCategory && mysql_num_rows($resTempCategory))
		{
			$row_temp_category    =  mysql_fetch_assoc($resTempCategory);
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr     =     array();
				$temp_catlin_arr      = explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr     =  array_filter($temp_catlin_arr);
				$temp_catlin_arr     =  $this->get_valid_categories($temp_catlin_arr);

				$temp_catlin_np_arr = array();
				$temp_catlin_np_arr = explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr = array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr =  $this->get_valid_categories($temp_catlin_np_arr);

				$total_catlin_arr = array();
				$total_catlin_arr = array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->get_valid_categories($total_catlin_arr);
			}
		}	
		return $temp_category_arr;		
	}
	function get_valid_categories($total_catlin_arr)
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
					$final_catids_arr[]    = $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
		}
		return $final_catids_arr;
	} 
	private function insertCompanySource($compData){
			if(!empty($compData))
			{
			$emp_detail	=	"";
			$emp_detail = 	$compData['ucode'].",".$compData['uname'];
			
			$sql_insert = 	"INSERT INTO tbl_company_source SET 
								contactID  		= '".$compData['parentid']."' ,
								parentid 		= '".$compData['parentid']."' ,
								mainsource 		= '".addslashes($compData['mainsource'])."',
								subsource 		= '".addslashes($compData['subsource'])."',
								datesource 		= '".$compData['datesource']."',
								data_city  		= '".$compData['data_city']."',
								emp_detail 		= '".$emp_detail."',
								paidstatus 		= '".$compData['paid']."'";
			
				/*if($compData['source_update'] == 1){
				$res_insert		=	parent::execQuery($sql_insert, $this->dbConDjds);
				}else{
					if($compData['session_tme']	== 'session_tme' && $compData['paid'] == 1){
						$res_insert		=	parent::execQuery($sql_insert, $this->dbConIdc);
					}else{
						$res_insert		=	parent::execQuery($sql_insert, $this->dbConDjds);
					}
				}*/
			}
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
					$this->web_services_api = "http://vishalvinodrana.jdsoftware.com/web_services/web_services/";	
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
		$source_code = '';
		$sName = 'CS';
		$sqlSourceCode = "SELECT source_id AS sCode FROM online_regis1.tbl_source_master where source_name = '".$sName."' AND active_flag = 1";
		$resSourceCode = parent::execQuery($sqlSourceCode, $this->conn_idc);
		if($resSourceCode && parent::numRows($resSourceCode)>0){
			$row_source 	= parent::fetchData($resSourceCode);
			$source_code 	= $row_source['sCode'];
		}
		return $source_code;
	}
	public function getSourceInfo($sCode)
	{
		$source_code = '';
		$sqlSourceCode = "SELECT source_id AS sCode,source_name as SName FROM online_regis1.tbl_source_master WHERE source_id = '".$sCode."' LIMIt 1";
		$resSourceCode = parent::execQuery($sqlSourceCode, $this->conn_idc);
		if($resSourceCode && parent::numRows($resSourceCode)>0){
			$row_source 	= parent::fetchData($resSourceCode);
			return $row_source;
		}
		return $source_code;
	}
	
	public function getOldSourceInfo($SName)
	{
		$sqlSourceCode = "SELECT scode,sname FROM source WHERE UPPER(TRIM(sname)) = '".$SName."' LIMIT 1";
		$resSourceCode = parent::execQuery($sqlSourceCode, $this->conn_local);
		if($resSourceCode && parent::numRows($resSourceCode)>0){
			$row_source 	= parent::fetchData($resSourceCode);
			return $row_source;
		}
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
	function update_attributes_withcats(){
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds,facility";
			$row_data = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sql_data = "SELECT catIds,facility FROM tbl_business_temp_data WHERE contractid='".$this->parentid."'";
			$res_data =  parent::execQuery($sql_data, $this->conn_temp);
			$row_data = mysql_fetch_assoc($res_data);
		}
		$paidcatids = $row_data['catIds'];
		$temp_catlin_arr = 	array();
		$temp_catlin_arr =  explode('|P|',$paidcatids);
		$temp_catlin_arr = 	$this->getValidCategories($temp_catlin_arr);
		if(count($temp_catlin_arr)>0)
		{
			$catid_List 	 = 	implode("','",$temp_catlin_arr);
			$attributes 	= "";	
			$facility_group_arr = array();
			if($row_data[facility]!='')
			{	
				$temp1_arr=explode("***",$row_data[facility]);
				foreach($temp1_arr as $key=>$value){					
					$temp2_arr=explode("@@@",$value);					
					$facility_group = $temp2_arr[0];		
					$facility_group_arr[] = $facility_group;
				}
				$attr_grps = array();
				$facility_group_arr = array_unique($facility_group_arr);
				if(count($facility_group_arr)>0){
					$facility_name_str = implode("','",$facility_group_arr);					
					$check_attrs = "SELECT GROUP_CONCAT(DISTINCT(attribute_group)) AS attribute_group FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_List."') AND attribute_group!=0 ";					
					$res_attrs =  parent::execQuery($check_attrs, $this->conn_local);
					if($res_attrs && mysql_num_rows($res_attrs)>0){
						$row_attrs = mysql_fetch_assoc($res_attrs);
						$attr_grps = explode(",",$row_attrs['attribute_group']);
					}
					if(count($attr_grps)>0){
						$sub_grp_name = array();
						$attr_grps_imp = implode("','",$attr_grps);
						$get_sub_grps = "SELECT * FROM online_regis1.tbl_attribute_subgroup WHERE attribute_group IN ('".$attr_grps_imp."') ORDER BY sub_group_pos ASC";
						$res_sub_grps = parent::execQuery($get_sub_grps, $this->conn_idc);
						if($res_sub_grps && mysql_num_rows($res_sub_grps)>0){
							while($row_sub_grps = mysql_fetch_assoc($res_sub_grps)){
								//$sub_grp_name[] = strtoupper($row_sub_grps['subgroup_name']);
								$sub_grp_name[] = strtoupper($row_sub_grps['id']);
							}
						}
						
					}					
					
				}
				
				$temp1_arr=explode("***",$row_data[facility]);				
				foreach($temp1_arr as $key=>$value){					
					$temp2_arr=explode("@@@",$value);				
					$facility_group = $temp2_arr[0];
					if(!in_array(strtoupper($facility_group),$sub_grp_name)){
						unset($temp1_arr[$key]);
					}else{						
						if($attributes){
							//$attributes.="###"."|$|".$temp2_arr[1];
							$attributes.="###".$temp2_arr[1];
						}else{
							$attributes=$temp2_arr[1];
						}
					}
				}
				
				$attributes=str_replace("~~~","-",$attributes);
				$attributes_list = implode("***",$temp1_arr);	
				$attributes= addslashes(stripslashes($attributes));
				$attributes_list=addslashes(stripslashes($attributes_list));
				
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
			
					$bustemp_tbl 		= "tbl_business_temp_data";
					$bustemp_upt = array();
					$bustemp_upt['mainattr'] 				= $attributes;
					$bustemp_upt['facility'] 				= $attributes_list;
					$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
				
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					$extrdet_upt['attributes'] 				= $attributes;
					$extrdet_upt['attributes_edit'] 		= $attributes_list;
					if($attributes=='' && $attributes_list==''){
						$extrdet_upt['attribute_search'] 	= '';
					}
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
					$mongo_inputs['table_data'] 			= $mongo_data;
					$res = $this->mongo_obj->updateData($mongo_inputs);
				}
				else
				{
					if($this->mongo_tme == 1)
					{
						$mongo_inputs = array();
						$mongo_inputs['parentid'] 	= $this->parentid;
						$mongo_inputs['data_city'] 	= $this->data_city;
						$mongo_inputs['module']		= $this->module;
						$mongo_data = array();
				
						$bustemp_tbl 		= "tbl_business_temp_data";
						$bustemp_upt = array();
						$bustemp_upt['mainattr'] 				= $attributes;
						$bustemp_upt['facility'] 				= $attributes_list;
						$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
					
						$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
						$extrdet_upt = array();
						$extrdet_upt['attributes'] 				= $attributes;
						$extrdet_upt['attributes_edit'] 		= $attributes_list;
						if($attributes=='' && $attributes_list==''){
							$extrdet_upt['attribute_search'] 	= '';
						}
						$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
						$mongo_inputs['table_data'] 			= $mongo_data;
						$res = $this->mongo_obj->updateData($mongo_inputs);
					}
					$attribute_search ='';
					$sqlUpdateAttributes="INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','".$attributes."','".$attributes_list."')
					ON DUPLICATE KEY UPDATE
					mainattr='".$attributes."',facility='".$attributes_list."'";
					$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";								
					$resUpdateAttributes =  parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
								
					if($attributes=='' && $attributes_list==''){
						$attribute_search = ", attribute_search=''";
					}
					$sqlUpdateAttributes = "UPDATE tbl_companymaster_extradetails_shadow SET attributes = '".$attributes."', attributes_edit = '".$attributes_list."' ".$attribute_search." WHERE parentid = '".$this->parentid."'";
					if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){				
						$resUpdateEx_det =  parent::execQuery($sqlUpdateAttributes, $this->conn_iro);
					}else{
						$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";
						$resUpdateEx_det =  parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
					}
				}
			}else{
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
				
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					$extrdet_upt['attributes'] 				= '';
					$extrdet_upt['attributes_edit'] 		= '';
					$extrdet_upt['attribute_search'] 		= '';
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
					$mongo_inputs['table_data'] 			= $mongo_data;
					$res = $this->mongo_obj->updateData($mongo_inputs);
				}
				else
				{
					if($this->mongo_tme == 1)
					{
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
					
						$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
						$extrdet_upt = array();
						$extrdet_upt['attributes'] 				= '';
						$extrdet_upt['attributes_edit'] 		= '';
						$extrdet_upt['attribute_search'] 		= '';
						$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
						$mongo_inputs['table_data'] 			= $mongo_data;
						$res = $this->mongo_obj->updateData($mongo_inputs);
					}
					$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','','') ON DUPLICATE KEY UPDATE mainattr='',facility=''";
					$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";				
					$resUpdateAttributes =  parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
					
					$sqlUpdate_extra = "UPDATE tbl_companymaster_extradetails_shadow set attributes_edit='', attributes='', attribute_search='' WHERE parentid='".$this->parentid."'";				
					if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){				
						$resUpdateEx_det =  parent::execQuery($sqlUpdate_extra, $this->conn_iro);
					}else{
						$sqlUpdate_extra = $sqlUpdate_extra."/* TMEMONGOQRY */";
						$resUpdateEx_det =  parent::execQuery($sqlUpdate_extra, $this->conn_temp);
					}
				}
			}
		}
		else
		{
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
			
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['attributes'] 				= '';
				$extrdet_upt['attributes_edit'] 		= '';
				$extrdet_upt['attribute_search'] 		= '';
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$res = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{
				if($this->mongo_tme == 1)
				{
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
				
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					$extrdet_upt['attributes'] 				= '';
					$extrdet_upt['attributes_edit'] 		= '';
					$extrdet_upt['attribute_search'] 		= '';
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
					$mongo_inputs['table_data'] 			= $mongo_data;
					$res = $this->mongo_obj->updateData($mongo_inputs);
				}
				$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','','') ON DUPLICATE KEY UPDATE mainattr='',facility=''";
				$sqlUpdateAttributes = $sqlUpdateAttributes."/* TMEMONGOQRY */";
				$resUpdateAttributes =  parent::execQuery($sqlUpdateAttributes, $this->conn_temp);
					
				$sqlUpdate_extra = "UPDATE tbl_companymaster_extradetails_shadow set attributes_edit='', attributes='', attribute_search='' WHERE parentid='".$this->parentid."'";			
				if(strtolower($this->module)=='cs' || strtolower($this->module)=='de'){				
					$resUpdateEx_det =  parent::execQuery($sqlUpdate_extra, $this->conn_iro);
				}else{
					$sqlUpdate_extra = $sqlUpdate_extra."/* TMEMONGOQRY */";
					$resUpdateEx_det =  parent::execQuery($sqlUpdate_extra, $this->conn_temp);
				}
			}
		}
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
		//parentid, landline, updated_time, ucode,uname,source
		
		if($this->landline && $this->genInfoArr['mobile']==''){
			
			$landline = explode(",",$this->landline);
			foreach($landline as $key=>$value){
			$sqlInsert = "INSERT INTO tbl_saveandexit_landline_info (parentid, landline, ucode,uname,updated_time,source)
					 values ('".$this->parentid."','".$value."', '".$this->ucode."', '".$this->uname."', '".date('Y-m-d H:i:s')."', '".$this->module."') ";

				$resInsert  = parent::execQuery($sqlInsert, $this->conn_idc);
			}
			
		}
		
		
	}
	private	function getGenInfoMain()
	{
		//$sql_gen_info = "SELECT companyname, building_name, landmark, street, area, pincode, city, state, geocode_accuracy_level, latitude, longitude,mobile,landline,tollfree,paid FROM tbl_companymaster_generalinfo where parentid= '".$this->parentid."'";
		//$res_gen_info = parent::execQuery($sql_gen_info, $this->conn_iro);	
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'companyname,building_name,landmark,street,area,pincode,city,state, geocode_accuracy_level,latitude,longitude,mobile,landline,tollfree,paid';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'save_nonpaid_data_class';
		$comp_params['skip_log']	= 1;

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1')
		{
			//$extraDetailsArr1=$comp_api_arr['results']['data'][$this->parentid];
		/* if ($res_gen_info && parent::numRows($res_gen_info) > 0)
		{
			 */
			//$genInfoArrMain   = parent::fetchData($res_gen_info);
			$genInfoArrMain 	= $comp_api_arr['results']['data'][$this->parentid];
			//$genInfoArrMain['numrows'] = parent::numRows($res_gen_info);
		}
		return $genInfoArrMain;
	}
	function getextradetailsInfoMain()
	{
		//$sql_comp_extra	= "SELECT flags, map_pointer_flags, closedown_flag, landline_addinfo, mobile_addinfo, tollfree_addinfo,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search, national_catidlineage_search,freeze,mask,original_creator FROM tbl_companymaster_extradetails where parentid= '".$this->parentid."'";
		//$res_comp_extra = parent::execQuery($sql_comp_extra, $this->conn_iro);	
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'flags,map_pointer_flags,closedown_flag,landline_addinfo, mobile_addinfo,tollfree_addinfo,catidlineage,catidlineage_nonpaid,national_catidlineage_nonpaid,catidlineage_search,national_catidlineage_search,freeze,mask,original_creator';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'save_nonpaid_data_class';
		$comp_params['skip_log']	= 1;

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
			{
			//$extDetArrMain      = parent::fetchData($res_comp_extra);
			$extDetArrMain = $comp_api_arr['results']['data'][$this->parentid];
		}
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
		}else
		{
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
			$url = "http://vishalvinodrana.jdsoftware.com/csgenio/";
			$jdbox_url 				= "http://vishalvinodrana.jdsoftware.com/jdbox/";
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
		$phonesearch_flag = 0;
		$sqlChkPhoneSrchShadow = "SELECT parentid FROM tbl_companymaster_finance_shadow WHERE parentid = '".$this->parentid."' AND campaignid IN ('1','2') LIMIT 1";
		$resChkPhoneSrchShadow =  parent::execQuery($sqlChkPhoneSrchShadow,$this->conn_fnc);
		if($resChkPhoneSrchShadow && parent::numRows($resChkPhoneSrchShadow)>0){
			$phonesearch_flag = 1;
		}else{
				$sqlChkPhoneSrchMain = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid IN ('1','2') LIMIT 1";
				$resChkPhoneSrchMain = parent::execQuery($sqlChkPhoneSrchMain,$this->conn_fnc);
				if($resChkPhoneSrchMain && parent::numRows($resChkPhoneSrchMain)>0){
					$phonesearch_flag = 1;
				}else{
					$sqlNationalListing = "SELECT parentid FROM tbl_companymaster_finance_national WHERE parentid = '".$this->parentid."'  AND (campaign_value>0 OR budget>0)";
					$resNationalListing = parent::execQuery($sqlNationalListing,$this->conn_national);
					if($resNationalListing && parent::numRows($resNationalListing)>0){
						$phonesearch_flag = 1;
					}else{
						$sqlUnApprovedChk = "SELECT parentid,approvalStatus FROM payment_instrument_summary WHERE parentid = '".$this->parentid."' AND approvalStatus = 0";
						$resUnApprovedChk = parent::execQuery($sqlUnApprovedChk,$this->conn_idc);
						if($resUnApprovedChk && parent::numRows($sqlUnApprovedChk)>0){
							$phonesearch_flag = 1;
						}
					}
				}
		}
		return $phonesearch_flag;
	}
	function getNationalCatlineage($catid)
	{
		if(!empty($catid))
		{
			$catid_list				=	str_replace("/","'",$catid);
			$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list.")";
			$res_national_catids	=	parent::execQuery($sql_national_catids, $this->conn_local);
			
			if($res_national_catids && parent::numRows($res_national_catids))
			{
				while($row_national_catids = parent::fetchData($res_national_catids))
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

	function getDocId()
	{
		$docid = 0;
		$sqlSphinxID = "SELECT docid FROM tbl_id_generator WHERE parentid = '".$this->parentid."'";
		$resSphinxID =  parent::execQuery($sqlSphinxID, $this->conn_iro);
		if($resSphinxID && parent::numRows($resSphinxID)>0){
			$row_sphinx_id  =  parent :: fetchData($resSphinxID);
			$docid = $row_sphinx_id['docid'];
		}
		return $docid;
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
			$rmv_reason  = "UnFreezing Contract - Save As Nonpaid";
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
		
		
		
		if($this->extraDetailsArrMain['freeze'] != $this->interMediateTable['freez'])
		{
			$unfrz_reason  = $this->interMediateTable['reason_text'];
			$sqlInsrtFrzLog = "INSERT INTO tbl_compfreez_details(contractid,parentid, reason, createdBy, freez, date_time) VALUE('".$this->parentid."','".$this->parentid."', '".$unfrz_reason."', '".$this->ucode."', '".$this->interMediateTable['freez']."', NOW())";
			$resInsrtFrzLog = parent::execQuery($sqlInsrtFrzLog,$this->conn_local);
			if($this->interMediateTable['freez'] == 1)
			{
				$ret = $this->freezCall();
				
				if($ret['Results'][$this->docid]['review'] > 49)
				{
					$skip_roleids = Array('db_producttester','admin');
					
					if(!(in_array(strtolower($this->emptype),$skip_roleids)))
					{
						$params_freeze = Array();
						$params_freeze['rquest'] = 'push_reseller_data';
						$params_freeze['parentid'] = $this->parentid;
						$params_freeze['data_city'] = $this->data_city;
						$params_freeze['module_type'] = 'FREEZE_DATA';
						$params_freeze['entered_date'] = date("Y-m-d H:i:s");
						$params_freeze['userid'] 	= $this->ucode;
						$params_freeze['uname'] 	= $this->uname;
						$params_freeze['source'] 	= 'DE';
						//print_r($params_freeze);
						$curl_url_freeze =	'nareshbhati.jdsoftware.com/jdbox/services/location_api.php';
						
						$freeze_data_res	=	json_decode($this->curlCallPost($curl_url_freeze,$params_freeze),true);
					}
				}
				//die;
			}
		}
		if($this->extraDetailsArrMain['mask'] !=$this->interMediateTable['mask'])
		{
			$unmsk_reason  = $this->interMediateTable['reason_text'];
			$sqlInsrtMaskLog = "INSERT INTO tbl_compMask_details(parentid, reason, createdBy, mask, date_time) VALUE('".$this->parentid."', '".$unmsk_reason."', '".$this->ucode."', '".$this->interMediateTable['mask']."', NOW())";
			$resInsrtMaskLog = parent::execQuery($sqlInsrtMaskLog,$this->conn_local);
		}
		
		if($this->extraDetailsArrMain['closedown_flag'] != $this->extraDetailsArr['closedown_flag'] && intval($this->extraDetailsArr['closedown_flag']) == 10)
		{
			$this->interMediateTable['reason_id'] 	= 11;
			$frz_action_flag 		= 0;
			$this->interMediateTable['reason_text'] = "Not In Business";
			$exact_reason			= "Not In Business";
			$this->interMediateTable['freez'] 	= 1;
			$this->interMediateTable['deactflg'] 	= 1;
			
			//print_r($this->interMediateTable);
			$sqlLogCheck = "SELECT SUBSTRING_INDEX(GROUP_CONCAT(reason ORDER BY date_time DESC ),',',1) AS reason_str FROM tbl_compfreez_details WHERE parentid = '".$this->parentid."' HAVING reason_str = 'Not In Business'";
			$resFrzLog = parent::execQuery($sqlLogCheck,$this->conn_local);
			if($resFrzLog && mysql_num_rows($resFrzLog)<=0)
			{
				$sqlContractFrzLog = "INSERT INTO tbl_compfreez_details (contractid, parentid, reason, date_time, createdBy, freez, exact_reason, action_flag) VALUES ('".$this->parentid."', '".$this->parentid."', '".addslashes(stripslashes($this->interMediateTable['reason_text']))."', NOW(), '".$this->ucode."', '".$this->interMediateTable['freez']."', '".$exact_reason."', '".$frz_action_flag."')";
				$resFrzContractFrzLog = parent::execQuery($sqlContractFrzLog,$this->conn_local);
			}
		}
		
	}
	
	function freezCall($docid_val)
	{
		$params = array();
		$curl_url= "http://192.168.20.101/10aug2016/review_rating.php?case=multiple_ratings&docid=".$this->docid;
		$ret = json_decode($this->curlCallPost($curl_url,$params),true);	
		
		return $ret;
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
						  ucode        		= '".$this->ucode."',
						  uname        		= '".$this->ucode."',
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
		//$sql_gen_info1 = "select mobile,landline,tollfree from tbl_companymaster_generalinfo where parentid= '".$this->parentid."'";
		//$res_gen_info1   = parent::execQuery($sql_gen_info1, $this->conn_iro);

		/* if($res_gen_info1 && parent::numRows($res_gen_info1))
		{
			$genralInfoArr1=parent::fetchData($res_gen_info1);		
		} */	
		
		//$sql_comp_extra1    = "select flags from tbl_companymaster_extradetails where parentid= '".$this->parentid."'"; // Old flags entry.
		//$res_comp_extra1   = parent::execQuery($sql_comp_extra1, $this->conn_iro);
		$comp_params = array();
		$comp_params['data_city'] 	= $this->data_city;
		$comp_params['table'] 		= 'extra_det_id,gen_info_id';		
		$comp_params['parentid'] 	= $this->parentid;
		$comp_params['fields']		= 'flags,mobile,landline,tollfree';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'save_nonpaid_data_class';

		$comp_api_arr	= array();
		$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
		if($comp_api_res!=''){
			$comp_api_arr 	= json_decode($comp_api_res,TRUE);
		}
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['extra_det_id']=='1')
			{
			$extraDetailsArr1=$comp_api_arr['results']['data'][$this->parentid];	
		}
		$flags = 0;		
		$extrDetail_tollfreeFlag = (($extraDetailsArr1[flags]&1024) == 1024)?'1' :'0';   
		if ($extraDetailsArr1[mobile] =='' && $extraDetailsArr1[landline] =='' && $extraDetailsArr1[tollfree] !='')
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

		$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid > 0 AND category_name != '' ";
		$res = parent::execQuery($sql, $this->conn_local);			
		
		if($res && parent::numRows($res))
		{
			$row = parent::fetchData($res);
			if($row['associate_national_catid'])
			{
				
				$associate_national_catid_arr = explode(',',$row['associate_national_catid']);			
				
				$associate_national_catid_arr = array_unique($associate_national_catid_arr);
				$associate_national_catid_arr = array_filter($associate_national_catid_arr);
				$associate_national_catid_str = implode(",",$associate_national_catid_arr);
				$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid IN (".$associate_national_catid_str.") and catid NOT IN (".$catidliststr.") AND catid > 0 AND category_name != '' ";
				$res = parent::execQuery($sql, $this->conn_local);
				
				if($res && parent::numRows($res))
				{
					$row = parent::fetchData($res);
					if($row['parent_categories'])
					{
						$parent_categories_arr = explode(',',$row['parent_categories']);
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
		curl_setopt($ch, CURLOPT_POSTFIELDS ,http_build_query($param));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
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
	
	private function getPaidStatus($parentid,$data_city)
	{
		$curlurl	=	$this->jdbox_url."services/contract_type.php";	
		$param_array=	array();
		$param_array['parentid']	=	$parentid;
		$param_array['data_city']	=	$data_city;
		$param_array['rquest']	=	'get_contract_type';		
		$paid_arr	=	json_decode($this->curlCallPost($curlurl,$param_array),true);
		return $paid_arr['result']['paid'];
	}

}//class ends here


?>
