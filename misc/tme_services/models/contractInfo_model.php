<?php
///namespace etc\tmemodel;
class ContractInfo_Model extends Model {
	public function __construct() {
		parent::__construct();
		
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
		$this->main_cities 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		$this->companyClass_obj  = new companyClass();
	}
	
	public function getShadowDataGeneral($contractid='') {
		$moduleval = strtoupper(MODULE);
		switch($moduleval)
		{
			case 'CS' :
				$dbObjTme	=	new DB($this->db['db_iro']);
			break;
			case 'TME' :
				$dbObjTme	=	new DB($this->db['db_tme']);
			break;
			case 'ME' :
				$dbObjTme	=	new DB($this->db['db_idc']);
			break;
		}
		
		$retArr	=	array();
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $contractid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "companyname";
			$data_res = $this->mongo_obj->getData($mongo_inputs);
			$numRows = count($data_res);
		}
		else
		{
			$query		=	"SELECT companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$contractid."'";
			$con		=	$dbObjTme->query($query);
			$numRows	=	$dbObjTme->numRows($con);
			$data_res 	= $dbObjTme->fetchData($con);
		}
		
		if($numRows > 0) {
			$retArr['data']			=	$data_res;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function getMainTabGeneralData($contractid) {		

		$dbObjIro	=	new DB($this->db['db_iro']);
		$retArr	=	array();
		$expContId	=	explode(',',$contractid);
		/* if(count($expContId) > 1) {
			$query	=	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status FROM tbl_companymaster_generalinfo WHERE parentid IN ('".str_replace(",","','",$contractid)."')";
		} else {
			$query	=	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status FROM tbl_companymaster_generalinfo WHERE parentid = '".$contractid."'";
		} */

		$comp_params = array();
		$comp_params['data_city'] 	= SERVER_CITY;
		$comp_params['table'] 		= 'gen_info_id';		
		$comp_params['parentid'] 	= $contractid;
		$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'contractInfo_model';		

		$comp_api_arr 	= 	array();
		$comp_api_arr	=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),TRUE); 				
		/* $con	=	$dbObjIro->query($query);
		$num	=	$dbObjIro->numRows($con); */
		if($comp_api_arr['errors']['code']=='0' && $comp_api_arr['results']['status']['gen_info_id']=='1'){
			/* if(count($expContId) > 1) {

				 while($res	=	$dbObjIro->fetchData($con)) {
					$retArr['data'][$res['parentid']]	=	$res;	
					$current_paid_status  = $this->getCurrentPaidStatus($res['parentid']);					 				
					$retArr['data'][$res['parentid']]['paid'] = $current_paid_status['result']['paid'];  				
				}
			} else {
				$retArr['data']	=	$dbObjIro->fetchData($con);
				$current_paid_status  = $this->getCurrentPaidStatus($retArr['data']['parentid']);
				$retArr['data']['paid'] = $current_paid_status['result']['paid'];
			} */			
			if(count($comp_api_arr['results']['data'])>0){
				$retArr['data']			=	$comp_api_arr['results']['data'];
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Found';
			}
			else{
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';	
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function getMainTabExtraData($contractid) {		
		$dbObjIro	=	new DB($this->db['db_iro']);
		
		$retArr	=	array();
		//$query	=	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,category_count,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,data_city,contract_jd_level,display_JD_stamp,dailer_data,db_update,tag_line,tag_Image_path,tag_description,tag_catid,tag_catname,CorporateDealers,catidlineage_nonpaid,national_catidlineage_nonpaid,rating_productname,closedown_flag,std_isd_flag,num_of_photos,quick_quote_flag,dup_groupid,noduplicatecheck,area_sensitivity,block_for_sale,low_ranking,type_flag,sub_type_flag,iro_type_flag,website_type_flag,businesstags,cuisine,price_range,ref_parentid,preordering_flag,social_media_url,edit_flag,master_parentid,helpline_flag,content_present_flag,fb_prefered_language FROM tbl_companymaster_extradetails WHERE parentid = '".$contractid."'";
		/* $con	=	$dbObjIro->query($query);
		$num	=	$dbObjIro->numRows($con);
		 */
		$comp_params = array();
		$comp_params['data_city'] 	= SERVER_CITY;
		$comp_params['table'] 		= 'extra_det_id';		
		$comp_params['parentid'] 	= $contractid;
		$comp_params['fields']		= 'nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,category_count,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,data_city,contract_jd_level,display_JD_stamp,dailer_data,db_update,tag_line,tag_Image_path,tag_description,tag_catid,tag_catname,CorporateDealers,catidlineage_nonpaid,national_catidlineage_nonpaid,rating_productname,closedown_flag,std_isd_flag,num_of_photos,quick_quote_flag,dup_groupid,noduplicatecheck,area_sensitivity,block_for_sale,low_ranking,type_flag,sub_type_flag,iro_type_flag,website_type_flag,businesstags,cuisine,price_range,ref_parentid,preordering_flag,social_media_url,edit_flag,master_parentid,helpline_flag,content_present_flag,fb_prefered_language';
		$comp_params['action']		= 'fetchdata';
		$comp_params['page']		= 'contractInfo_model';		

		$comp_api_arr 	= 	array();
		$comp_api_arr	=	json_decode($this->companyClass_obj->getCompanyInfo($comp_params),TRUE);
		
		if($comp_api_arr['errors']['code']=='0' && count($comp_api_arr['results']['data'])>0) {
			$retArr['data']	=	$comp_api_arr['results']['data'][$contractid];
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function getShadowTabGeneralData($contractid) {
		
		$moduleval = strtoupper(MODULE);
		switch($moduleval)
		{
			case 'CS' :
				$dbObjTme	=	new DB($this->db['db_iro']);
			break;
			case 'TME' :
				$dbObjTme	=	new DB($this->db['db_tme']);
			break;
			case 'ME' :
				$dbObjTme	=	new DB($this->db['db_idc']);
			break;
		}
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $contractid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "nationalid,sphinx_id,regionid,mobile_admin,companyname,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status";
			$data_res = $this->mongo_obj->getData($mongo_inputs);
			$num = count($data_res);
		}else{
			$retArr	=	array();
			$query	=	"SELECT nationalid,sphinx_id,regionid,companyname,mobile_admin,parentid,country,state,city,display_city,area,subarea,office_no,building_name,street,street_direction,street_suffix,landmark,landmark_custom,pincode,pincode_addinfo,latitude,longitude,geocode_accuracy_level,full_address,stdcode,landline,dialable_landline,landline_display,dialable_landline_display,landline_feedback,mobile,dialable_mobile,mobile_display,dialable_mobile_display,mobile_feedback,fax,tollfree,tollfree_display,email,email_display,email_feedback,sms_scode,website,contact_person,contact_person_display,callconnect,virtualNumber,virtual_mapped_number,blockforvirtual,othercity_number,paid,displayType,company_callcnt,company_callcnt_rolling,data_city,hide_address,helpline,helpline_display,pri_number,cc_status FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$contractid."'";
			$con	=	$dbObjTme->query($query);
			$num	=	$dbObjTme->numRows($con);
			if($num > 0) {
				$data_res	=	$dbObjTme->fetchData($con);
				$current_paid_status  = $this->getCurrentPaidStatus($data_res['parentid']);
				$data_res['data'][$data_res['parentid']]['paid'] = $current_paid_status['result']['paid'];  
			}
		}
		if($num > 0) {
			$retArr['data']			=	$data_res;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function getShadowTabExtraData($contractid) {
		$moduleval = strtoupper(MODULE);
		switch($moduleval)
		{
			case 'CS' :
				$dbObjTme	=	new DB($this->db['db_iro']);
			break;
			case 'TME' :
				$dbObjTme	=	new DB($this->db['db_tme']);
			break;
			case 'ME' :
				$dbObjTme	=	new DB($this->db['db_idc']);
			break;
		}
		
		$retArr	=	array();
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $contractid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['fields'] 	= "nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,category_count,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,tag_line,tag_Image_path,tag_description,tag_catid,tag_catname,data_city,catidlineage_nonpaid,national_catidlineage_nonpaid,closedown_flag,quick_quote_flag,iro_type_flag,website_type_flag,preordering_flag,social_media_url,edit_flag,CorporateDealers,dup_groupid,fb_prefered_language,award,testimonial,proof_establishment";
			$data_res = $this->mongo_obj->getData($mongo_inputs);
			$num = count($data_res);
		}
		else{
		
			$query	=	"SELECT nationalid,sphinx_id,regionid,companyname,parentid,landline_addinfo,mobile_addinfo,tollfree_addinfo,contact_person_addinfo,attributes,attributes_edit,turnover,working_time_start,working_time_end,payment_type,year_establishment,accreditations,certificates,no_employee,business_group,email_feedback_freq,statement_flag,alsoServeFlag,averageRating,ratings,web_ratings,number_of_reviews,group_id,catidlineage,catidlineage_search,national_catidlineage,national_catidlineage_search,category_count,hotcategory,flags,vertical_flags,business_assoc_flags,map_pointer_flags,guarantee,Jdright,LifestyleTag,contract_calltype,batch_group,audit_status,createdby,createdtime,customerID,datavalidity_flag,deactflg,display_flag,fmobile,femail,flgActive,flgApproval,freeze,mask,future_contract_flag,hidden_flag,lockDateTime,lockedBy,temp_deactive_start,temp_deactive_end,micrcode,prompt_cat_temp,promptype,referto,serviceName,srcEmp,telComm,newbusinessflag,tme_code,original_creator,original_date,updatedBy,updatedOn,backenduptdate,tag_line,tag_Image_path,tag_description,tag_catid,tag_catname,data_city,catidlineage_nonpaid,national_catidlineage_nonpaid,closedown_flag,quick_quote_flag,iro_type_flag,website_type_flag,preordering_flag,social_media_url,edit_flag,CorporateDealers,dup_groupid,fb_prefered_language,award,testimonial,proof_establishment FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$contractid."'";
			$con	=	$dbObjTme->query($query);
			$num	=	$dbObjTme->numRows($con);
			if($num > 0) {
				$data_res	=	$dbObjTme->fetchData($con);
			}
		}
		if($num > 0) {
			$retArr['data']			=	$data_res;
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	 public function getShadowTabData(){ // no changes
            header('Content-Type: application/json');
            $params             =   json_decode(file_get_contents('php://input'),true);
            $retData            =   array();
            $conn_idc           =   new DB($this->db['db_idc']);
            $dbObjIRO           =   new DB($this->db['db_iro']);
            
            $generalInfoData    =   json_decode($this->getShadowTabGeneralData($params['contractid']),true);
            $extraInfoData      =   json_decode($this->getShadowTabExtraData($params['contractid']),true);
            if($generalInfoData['errorCode']    ==  0 || $extraInfoData['errorCode']    ==  0){
                if($generalInfoData['data'] && $extraInfoData['data']){
                    $retData['data']        =   array_merge($generalInfoData['data'],$extraInfoData['data']);
                }else if($generalInfoData['data']){
                    $retData['data']        =   $generalInfoData['data'];
                }else{
                    $retData['data']        =   $extraInfoData['data'];
                }
                //$_SESSION['parentid']   =   $generalInfoData['data']['parentid'];
                //$_SESSION['sphinx_id']  =   $generalInfoData['data']['sphinx_id'];
                if($generalInfoData['data']['mobile'] != '') {
                    $mobileArr  =   explode(",",$generalInfoData['data']['mobile']);
                    foreach($mobileArr as $key=>$mob_val) {
                        $retData['mobile']['data'][$key]['mobile']  =   trim(str_replace("|","",$mob_val));
                        $retData['mobile']['data'][$key]['mobile']  =   trim(str_replace("^","",$retData['mobile']['data'][$key]['mobile']));
                        $retData['mobile']['data'][$key]['mobile']  =   trim(str_replace("~","",$retData['mobile']['data'][$key]['mobile']));
                        //$retData['mobile']['data'][$key]['mobile']    =   trim($mob_val);
                    }
                    if($generalInfoData['data']['mobile_display'] != '') {
                        $mob_dispArr    =   explode(",",$generalInfoData['data']['mobile_display']);
                        foreach($mob_dispArr as $key=>$mob_disp) {
                            $retData['mobile']['data'][$key]['mobile_display']  =   true;
                        }
                    }else {
                        $retData['mobile']['data'][0]['mobile_display'] =   false;
                        $retData['mobile']['data'][1]['mobile_display'] =   false;
                    }
                    if($generalInfoData['data']['mobile_feedback'] != ''){
                        $mob_feedArr    =   explode(",",$generalInfoData['data']['mobile_feedback']);
                        foreach($mob_dispArr as $key=>$mob_feed) {
                            $retData['mobile']['data'][$key]['mobile_feedback'] =   true;
                        }
                    }else {
                        $retData['mobile']['data'][0]['mobile_feedback']    =   false;
                        $retData['mobile']['data'][1]['mobile_feedback']    =   false;
                    }
                }else {
                    $retData['mobile']['data'][0]['mobile'] =   '';
                    $retData['mobile']['data'][1]['mobile'] =   '';
                    $retData['mobile']['data'][0]['mobile_feedback']    =   false;
                    $retData['mobile']['data'][1]['mobile_feedback']    =   false;
                    $retData['mobile']['data'][0]['mobile_display'] =   false;
                    $retData['mobile']['data'][1]['mobile_display'] =   false;
                }
                if ($extraInfoData['data']['landline_addinfo'] != '' || $generalInfoData['data']['landline'] != '') {
                    if ($extraInfoData['data']['landline_addinfo'] != '') {
                        $landline_exp = explode('|~|', $extraInfoData['data']['landline_addinfo']);
                        $exp_int_with = '|^|';
                    }else{
                        $landline_exp = explode(',', $generalInfoData['data']['landline']);
                        $exp_int_with = ',';
                    }
                }
                foreach($landline_exp as $key=>$val) {
                    $completeLandline[] =   explode("|^|",$val);
                }
                foreach($completeLandline as $key=>$val) {
                        $retData['landline']['data'][$key]['landline']  =   trim(str_replace("|","",$val[0]));
                        $retData['landline']['data'][$key]['landline']  =   trim(str_replace("^","",$retData['landline']['data'][$key]['landline']));
                        $retData['landline']['data'][$key]['landline']  =   trim(str_replace("~","",$retData['landline']['data'][$key]['landline']));
                    if(trim($val[1]) != ''){
                        $retData['landline']['data'][$key]['landline_comment']  =   trim($val[1]);
                    }
                }
                if($generalInfoData['data']['email'] != ''){
                    $email_arr  =   explode(",",$generalInfoData['data']['email']);
                    foreach($email_arr as $key=>$value){
                        $retData['email']['data'][$key]['email']    =   $value;
                    }
                }else{
                    $retData['email']['data'][0]['email']   =   "";
                    $retData['email']['data'][1]['email']   =   "";
                }
                if($generalInfoData['data']['email_display']!=''){
                    $email_display  =   explode(',',$generalInfoData['data']['email_display']);
                    foreach($email_display as $key=>$val){
                        $retData['email']['data'][$key]['email_display']    =   true;
                    }
                }else{
                    $retData['email']['data'][0]['email_display']   =   false;
                    $retData['email']['data'][1]['email_display']   =   false;
                }
                if($generalInfoData['data']['email_feedback']!=''){
                    $email_feedback =   explode(',',$generalInfoData['data']['email_feedback']);
                    foreach($email_feedback as $key=>$val){
                        $retData['email']['data'][$key]['email_feedback']   =   true;
                    }
                }else{
                    $retData['email']['data'][0]['email_feedback']  =   false;
                    $retData['email']['data'][1]['email_feedback']  =   false;
                }
                //echo $extraInfoData['data']['social_media_url'];
                if(trim($extraInfoData['data']['social_media_url'])){
                    $retData['social_media_url']['data']['social_media_url'][]  =   explode('|~|',trim($extraInfoData['data']['social_media_url']));
                }else{
                    $retData['social_media_url']['data']['social_media_url'][0] =   '';
                    $retData['social_media_url']['data']['social_media_url'][1] =   '';
                    $retData['social_media_url']['data']['social_media_url'][2] =   '';
                    $retData['social_media_url']['data']['social_media_url'][3] =   '';
                    $retData['social_media_url']['data']['ContactType']         =   "NEWCONTACT";
                }
                if($generalInfoData['data']['othercity_number'] != "") {
                    $otherCity_exp  =   explode(",",$generalInfoData['data']['othercity_number']);
                    foreach($otherCity_exp as $key=>$val) {
                        $newOtherCity   =   explode("##",$val);
                        if($newOtherCity[1][0] == '0' || $newOtherCity[1][0] == 0) {
                            $retData['otherCity']['data']['stdCode'][$key] = str_replace('0', '', $newOtherCity[1]);
                            $retData['otherCity']['data']['stdCode'][$key] = str_replace(0, '', $newOtherCity[1]);
                        }else{
                            $retData['otherCity']['data']['stdCode'][$key] = $newOtherCity[1];
                        }
                        $retData['otherCity']['data']['othercityNumber'][$key]  = $newOtherCity[2];
                    }
                }else{
                    $retData['otherCity']['data']   =   0;
                }
                //~ return json_encode($retData['data']['contact_person']);die;
                if(!empty($retData['data']['contact_person'])){
                    if(strpos($retData['data']['contact_person'],',')){
                        $completeName   =   explode(',',$retData['data']['contact_person']);
                        $i  =   0;
                        foreach($completeName as $val){
                            $dataExp    =   explode(" ",$val);
                            $retData['per_data']['data']['selected_salutation'][]   =   $dataExp[0];
                            $strNameDesig   =   "";
                            foreach($dataExp as $keyName=>$valueName) {
                                if($keyName != 0) {
                                    $strNameDesig   .=  $valueName." ";
                                }
                            }
                            $strNameDesig   =   substr($strNameDesig,0,-1);
                            $nameDesigExp   =   explode("(",$strNameDesig);
                            $retData['per_data']['data']['personName'][]    =   $nameDesigExp[0];
                            $retData['per_data']['data']['designation'][]   =   substr($nameDesigExp[1],0,-1);
                            if($retData['per_data']['data']['designation'][$i] == false) {
                                $retData['per_data']['data']['designation'][$i] =   "";
                            }
                            $i++;
                        }
                    }else{
                        $nameArray  =   explode(' ',$retData['data']['contact_person']);
                        $retData['per_data']['data']['selected_salutation'][0]  =   $nameArray[0];
                        $strNameDesig   =   "";
                        foreach($nameArray as $keyName=>$valueName) {
                            if($keyName != 0) {
                                $strNameDesig   .=  $valueName." ";
                            }
                        }
                        $strNameDesig   =   substr($strNameDesig,0,-1);
                        $nameDesigExp   =   explode("(",$strNameDesig);
                        $retData['per_data']['data']['personName'][0]   =   $nameDesigExp[0];
                        $retData['per_data']['data']['designation'][0]  =   substr($nameDesigExp[1],0,-1);
                        if($retData['per_data']['data']['designation'][0] == false) {
                            $retData['per_data']['data']['designation'][0]  =   "";
                        }
                    }
                    $retData['errorCode']           =   0;
                    $retData['errorStatus']         =   "DF";
                }
                $retData['errorCode']   =   0;
                $retData['errorStatus'] =   'Data Found';
            }else{
                $retData['errorCode']   =   1;
                $retData['errorStatus'] =   'Data Not Found';
            }
        return json_encode($retData);    
	}
	
	//~ public function getTmeSearchData($contractid='',$empcode,$empname,$data_city) { // done
		//~ $retArr                      	= 	array();
		//~ $curlParams                     	= 	array();
		//~ $datacity            				= 	((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		//~ $url                            	= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/fetchAllDetails.php';
		//~ $postArray['parentid'] 				= 	$contractid;
		//~ $postArray['ucode'] 				= 	$empcode;
		//~ $postArray['uname'] 				= 	$empname;
		//~ $postArray['data_city'] 			= 	$data_city;
		//~ $postArray['post_data']				= 	"1";		 
		//~ $postArray['module']				= 	"TME";		 
		//~ $postArray['action']				= 	"tmesearchdata";
		//~ $dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		//~ $retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		//~ $resultArr	=	array();
		//~ if(count($retArr) > 0) {
			//~ $resultArr['data']			=	$retArr;
			//~ $resultArr['errorCode']	=	0;
			//~ $resultArr['errorStatus']	=	'Data Found';
		//~ } else {
			//~ $resultArr['errorCode']	=	1;
			//~ $resultArr['errorStatus']	=	'Data Not Found';
		//~ }
		//~ return json_encode($resultArr);
	//~ }
	public function getTmeSearchData($contractid='') {
		$dbObjLocal	=	new DB($this->db['db_local']);
		//$dbObjLocal	=	new DB($this->db['db_local_slave']);
		$retArr	=	array();
		$query	=	"SELECT contact_details,reviews,ratings FROM tbl_tmesearch WHERE parentid='".$contractid."' LIMIT 1";
		$con	=	$dbObjLocal->query($query);
		$num	=	$dbObjLocal->numRows($con);
		if($num > 0) {
			$retArr['data']	=	$dbObjLocal->fetchData($con);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function getContractCatLive($contractid='') { // No changes
		$retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= 	array();
        $curlParams 				= 	array();
        $paramsSend					= 	array();
		$paramsSend['empcode']		= 	$params['empId'];
        $paramsSend['data_city']	= 	$params['data_city'];
        $paramsSend['action']		= 	'getContractCatLive';
        $data_city					=	$params['data_city'];
        $params['data_city'] 		= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url']     	= 	$this->genioconfig['jdbox_url'][strtolower($params['data_city'])].'services/tmenewServices.php?empcode='.$params['empId'].'&data_city='.$params['data_city'].'&action=getContractCatLive&module=TME&post_data=1&contractid='.$contractid;
        $curlParams['formate']      = 	'basic';
        $curlParams['headerJson']   = 	'json';
        $singleCheck                = 	json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	public function checkTrackerRep($contractid='') { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';//$this->genioconfig['jdbox_url'][strtolower($data_city)].
		$postArray['parentid'] 				= 	$params['parid'];
		$postArray['empcode'] 				= 	$params['empId'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['fromContractInfo']		= 	"1";
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"checkTrackerRep";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function tempContract($contractid='') { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$contractid;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"tempContract";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function actEcsRetention() {// done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['flag']					= 	$params['flag'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"actEcsRetention";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function searchCompanyByNum($phone='',$fullData='') { // done
		$retArr					=	array();
		$retArr['errorCode']	=	1;
		$retArr['errorStatus']	=	'Not in use';
		return json_encode($retArr);
	}
	
	public function fetchEcsRetentionData($ecs_contact,$fullData='') { // done
		$retArr					=	array();
		$retArr['errorCode']	=	1;
		$retArr['errorStatus']	=	'Not in use';
		return json_encode($retArr);
	}
	
	public function fetchMulticityTagging() { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php';//$this->genioconfig['jdbox_url'][strtolower($data_city)].
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"fetchMulticityTagging";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	function fetchTmeRetentionComments($empcode	=	'') { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parid'];
		$postArray['empcode'] 				= 	$empcode;
		$postArray['data_city'] 			= 	$data_city;
		$postArray['flag'] 					= 	$params['flag'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"TmeRetentionComments";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function insertECSStatus() { // done
		$retArr					=	array();
		$retArr['errorCode']	=	1;
		$retArr['errorStatus']	=	'Not in Use';
		return json_encode($retArr);
	}
	
	public function insertDisposeVal() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$dbObjLocal	=	new DB($this->db['db_local']); 
		$conn_tme	=	new DB($this->db['db_tme']);
		$parentid	=	$params['parentid'];
		$stVal		=	$params['stVal'];
		$empCode	=	$params['empcode'];
		$data_city  =	$params['data_city'];
		$respArray	=	array();
		//$jdaPayoutInfo	=	json_decode($this->JDAPayOutDataIntermediate($parentid),true); 
		$currentTime	=	date("Y-m-d H:i:s");// is server time time 
		//~ $updt_tbl_tme_data_search = "UPDATE tbl_tme_data_search SET allocationtype = '".$stVal."' , allocationtime = '".$currentTime."' WHERE parentid = '".$parentid."'";
		//~ $con_updt_tbl_tme_data_search	=	$dbObjLocal->query($updt_tbl_tme_data_search); // Commenting on 2018-07-06 as tbl_tme_data_search Does not have any data
		$last_mod 	= "INSERT INTO tbl_me_tme_sink SET parentid = '".$parentid."',	
													 empId	 = '".$empCode."', 
													 mod_flag = 0,	
													 approval_flag = 0,
													 allocationType='".$stVal."'  
					        ON DUPLICATE KEY UPDATE
													empId	 = '".$empCode."',	
													mod_flag = 0,	
													approval_flag = 0, 
													allocationType='".$stVal."'";
		$conLastMod  		= $dbObjLocal->query($last_mod);
		$retArr	=	Utility::insert_return($conLastMod);
		if($retArr['results']['errorCode']	==	1) {
			
		}
		$generalMainArr	=	json_decode($this->getMainTabGeneralData($parentid),true);
		if($generalMainArr['errorCode']	==	0) {
			if($generalMainArr['data']['paid']	==	0) {
				$dataCorrectionAPI	=	$this->dumpIntoDataCorrection($parentid,$empCode,$stVal);
			}
		}
		$generalShadowData	=	json_decode($this->getShadowDataGeneral($parentid),true);
		if($generalShadowData['errorCode']	==	0) {
			$companyname	=	addslashes($generalShadowData['data']['companyname']);
		} else {
			$companyname	=	'';
		}
		$currentTime	=	date("Y-m-d H:i:s");
		
		if($stVal != '25' && $stVal != '24' && $stVal != '22') {
			// MRK FEB 15 2017 CODE ADDED TO UPDATE IN TBL CONTRACT ALLOCATION
			$queryCheckexist    = "select * from tblContractAllocation where contractCode='".$parentid."'";
			$conChecknum     	= 	$dbObjLocal->query($queryCheckexist);
			$conChecknumrows = $dbObjLocal->numRows($conChecknum);
			if($conChecknumrows>0)
			{
					 $queryUpdatecname   = "Update tblContractAllocation set compname='".addslashes(stripslashes($companyname))."' where contractCode='".$parentid."'";			
				$conUpdateCname     = 	$dbObjLocal->query($queryUpdatecname);				
				$retArr2			=	Utility::update_return($conUpdateCname);	
			}

			$queryDisposeIns 	= 	"INSERT INTO tblContractAllocation (empCode, contractCode, allocationType, allocationTime,actionTime,compname) VALUES ('".$empCode."', '".$parentid."', ".$stVal.", '".$currentTime."','".$currentTime."','".$companyname."')";
			$conDisposeIns     	= 	$dbObjLocal->query($queryDisposeIns);
			$retArr2			=	Utility::insert_return($conDisposeIns);
			
			$updAllocType 		= 	"UPDATE tbl_tmesearch SET empCode='".$empCode."',allocationType='".$stVal."',allocationTime='".$currentTime."',actionTime='".$currentTime."' WHERE parentid='".$parentid."'";
			$conUpdAlloc    	= 	$dbObjLocal->query($updAllocType);
			$retArr3			=	Utility::insert_return($conUpdAlloc);
			
			if(($stVal==1) || ($stVal==7) || ($stVal==8) || ($stVal==11) || ($stVal==12) || ($stVal==98) || ($stVal==114)) {
				$parentidNew 		= 	$parentid[0] == 'P' ? $parentid : 'P'.$parentid;
				//~ $tmeInfo			=	json_decode($this->fetchTmeInfo($empCode,$data_city),true);
				//~ $tmeSearchInfo		=	json_decode($this->getTmeSearchData($parentidNew,$empCode,$tmeInfo['results']['empName'],$data_city),true);
				$tmeSearchInfo		=	json_decode($this->getTmeSearchData($parentidNew),true);
				$tmeInfo	=	json_decode($this->fetchTmeInfo($empCode,$data_city),true);
				$queryIns 		= 	"INSERT INTO tbl_tmeFeedback_dataCorrection(contractid, companyname, empcode, empname, allocationType, allocationTime,contact_details) VALUES ('".trim($parentidNew)."', '".addslashes($companyname)."', '".$empCode."', '".$tmeInfo['results']['empName']."','".$stVal."',NOW(),'".$tmeSearchInfo['data']['contact_details']."')";
				$result_insert  = $dbObjLocal->query($queryIns);
			}
		} else {
			$retArr2['results']['errorCode']	=	0;
			$retArr3['results']['errorCode']	=	0;
		}
		
		if($retArr2['results']['errorCode']	==	0 && $retArr3['results']['errorCode']	==	0) {
			$respArray['errorCode']	=	0;
			
			$respArray['errorStatus']	=	'Dispositions Saved Successfully';
		} else {
			$respArray['errorCode']	=	1;
			$respArray['errorStatus']	=	'Dispositions Not Saved';
		}
		
		// as confirmed by RS
		
		$hotDataUpdate 		=	json_decode($this->hotDataSms($parentid),true);
		if($hotDataUpdate['errorCode'] == 0){
			$respArray['errorCode'] 	=	0;
			$respArray['errorSatus_hotData']	=	$hotDataUpdate;
		}else{
			$respArray['errorCode'] 	=	0; 
			$respArray['errorSatus_hotData']	=	$hotDataUpdate;
		}
		
		$paramSend['data']['parentid']    = $parentid;
		$paramSend['disposition'] = $stVal;
		$paramSend['empcode'] 	  = $empCode;
		$paramSend['city']		  = urlencode($data_city);
		$paramSend['request']		  = 'setDispositon';
		$result = $this->jsonInsertDisp($paramSend); 
		return json_encode($respArray);
	}
	
	private function jsonInsertDisp($paramsSend_dc){
		GLOBAL $parseConf;
		$curlParams_dc['url'] = "http://".DC_API_NEW."/data_correction/jsonInsert.php";
		$curlParams_dc['formate'] = 'basic';
		$curlParams_dc['method'] = 'post';
		$curlParams_dc['postData'] = "comp_arr=".urlencode(json_encode($paramsSend_dc));  
		if($parseConf['servicefinder']['remotecity'] == 1){
			$dc_response	=	Utility::curlCall($curlParams_dc);
		}
	}
	public function hotDataSms($parentid){
		$retArr			=	array();
		$con_local 		=	new DB($this->db['db_local']);
		$SelExitCont  					= "SELECT source_date,flag_source FROM d_jds.tbl_hotData WHERE parentid ='".$parentid."'";
		$conExitCont					=	$con_local->query($SelExitCont);
		$count							=	$con_local->numRows($conExitCont);
		$fetchExitData 					=   $con_local->fetchData($conExitCont);
		$sourceDate = date('Y-m-d', strtotime($fetchExitData['source_date']));
		
		$SelContractAll 				= "SELECT allocationTime,contractCode FROM d_jds.tblContractAllocation WHERE contractCode = '".$parentid."' ORDER BY allocationTime DESC LIMIT 1";
		$conExitContAlloc				=	$con_local->query($SelContractAll);
		$countAlloc						=	$con_local->numRows($conExitContAlloc);
		$fetchExitDataAlloc 			=   $con_local->fetchData($conExitContAlloc);
		$AllocationeDate 				= date('Y-m-d', strtotime($fetchExitDataAlloc['allocationTime']));
		if($AllocationeDate !== $sourceDate)
		{
			$UpdExit 				= "UPDATE d_jds.tbl_hotData SET source_date ='".$fetchExitDataAlloc['allocationTime']."',flag_source =0 WHERE parentid ='".$parentid."'";
			$conExitUpdSource		=	$con_local->query($UpdExit);
			
			if($conExitUpdSource == 1 && isset($conExitUpdSource)){
				$retArr['errorCode'] 		=	0;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Done";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Fail";
			}
			
			if($conExitUpdSource == 1 && isset($conExitUpdSource)){
				$retArr['errorCode'] 		=	0;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Done";
			}else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus_tbl_hotData'] 	=	"Updated Fail";
			}			
		}
		else{
				$retArr['errorCode'] 	=	1;
				$retArr['errorStatus_tbl_hotData'] 	=	"Data Not Found";
			}
		
		return json_encode($retArr);
	}
	
	public function applyJDAPayout($parentid) {
		$dbObjLocal	=	new DB($this->db['db_local']);
		$check_jda_data_source = "SELECT parentid, datasource_date, jdacode FROM tbl_dialer_data WHERE parentid = '".$parentid."' AND data_source = 'jda' AND DATEDIFF(NOW(),datasource_date) <=15";
		$result_jda_data_source = $dbObjLocal->query($check_jda_data_source);
		$num_rows = $dbObjLocal->numRows($result_jda_data_source);
		$retArr	=	array();
		if($num_rows > 0) {
			/*include_once(DOC_ROOT."/business/includes/jdapayoutclass.php");
			$jdaPayout_obj = new jdapayoutclass();// objectt for class for jda payout

			$row_jda_data_source 	= mysql_fetch_assoc($result_jda_data_source);		
			$conData 				= $jdaPayout_obj->jdaPayoutProcess($conn_local, $conn_iro, $_REQUEST['parentIdSt'],$row_jda_data_source['jdacode'], $row_jda_data_source['datasource_date']);*/
			$retArr['errorCode']	=	'0';
			$retArr['errorStatus']	=	'Data Set Successfully';
		} else {
			$retArr['errorCode']	=	'1';
			$retArr['errorStatus']	=	'Data Not Sent Successfully';
		}
		return json_encode($retArr);
	}
	
	private function fetchTmeInfo($empcode,$data_city) {		
		$curlParams2 = array();
		$paramsGET	 =	array();
		$paramsGET['data_city']	     =	$data_city;
		$paramsGET['empcode']		 =	$empcode;	
		$paramsGET['urlFlag']		 =	1;	
		$curlParams2['formate'] 	 = 'basic';
		$curlParams2['method']       = 'post';
		$curlParams2['headerJson']   = 'json';
		$curlParams2['url'] = SERVICE_IP.'/tmenewInfo/get_tmeInfo/';		
		$curlParams2['postData'] = json_encode($paramsGET);
		$tmeInfo	=	Utility::curlCall($curlParams2);
		return $tmeInfo;
	}
	
	private function dumpIntoDataCorrection($parentid,$empcode,$allocationType) {
		$curlParams2 = array();
		$curlParams2['url'] = DECS_TME.'/business/dataCorrectionIntermediate.php';
		$curlParams2['formate'] = 'basic';
		$curlParams2['method'] = 'post';
		$curlParams2['headerJson'] = 'json';
		$paramsGET	=	array();
		$paramsGET['parentid']	=	$parentid;
		$paramsGET['empcode']	=	$empcode;
		$paramsGET['allocType']	=	$allocationType;
		$curlParams2['postData'] = json_encode($paramsGET);
		$tmeInfo	=	Utility::curlCall($curlParams2);
		return $tmeInfo;
	}
	
	private function JDAPayOutDataIntermediate($parentid) { 
		$curlParams2 = array();
		$curlParams2['url'] = DECS_TME.'/business/JDAPayOutDataIntermediate.php';
		$curlParams2['formate'] = 'basic';
		$curlParams2['method'] = 'post';
		$curlParams2['headerJson'] = 'json';
		$paramsGET	=	array();
		$paramsGET['parentid']	=	$parentid;
		$curlParams2['postData'] = json_encode($paramsGET);
		$JDAPayOutdata	=	Utility::curlCall($curlParams2); 
		return $JDAPayOutdata;
	}
	
	public function SendVLC($parentid,$data_city){
		header('Content-Type: application/json');	
		$paramsGET		= json_decode(file_get_contents('php://input'),true);
		$retArr			=	array();
		$parentid	= $paramsGET['parid'];
		$createdby	= $paramsGET['tmecode'];
		$data_city	= $paramsGET['city'];
		$city		= $paramsGET['city'];
		$reminder	= $paramsGET['reminder'];
		//$up_check = 1;
		$module		= 'TME';
		if(isset($reminder)) {
			$up_check 	= json_decode($this->checkforupload($parentid,$city,$data_city,$createdby,$paramsGET['uname']),true);
		}
		if(($up_check['errorCode'] == 0 && $up_check['data'] == 0) || ($reminder == 1)){
			$stdCode	=	json_decode(Utility::get_std_code($city,$data_city,$this->db),true);
			$resultArr                      	= 	array();
			$curlParams                     	= 	array();
			$data_city            				= 	((in_array(strtolower($paramsGET['data_city']), $this->main_cities)) ? strtolower($paramsGET['data_city']) : 'remote');
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/fetchAllDetails.php';
			$postArray['parentid'] 				= 	$parentid;
			$postArray['ucode'] 				= 	$createdby;
			$postArray['uname'] 				= 	$paramsGET['uname'];
			$postArray['data_city'] 			= 	$data_city;
			$postArray['post_data']				= 	"1";		 
			$postArray['module']				= 	"TME";		 
			$postArray['action']				= 	"idgenerator";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
			$resultArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
			$urlVLC								= 	DECS_CITY."/business/vlcdataupdate.php";
			$curlParams2['formate'] 			= 'basic';
			$curlParams2['method'] 				= 'post';
			$curlParams2['url']					=	$urlVLC;
			$paramsGET							=	array();
			$paramsGET['parentid']				=	$parentid;
			$paramsGET['createdby']				=	$createdby;
			$paramsGET['module']				=	$module;
			$paramsGET['city']					=	$city;
			$paramsGET['datatype']				=	'2';
			$paramsGET['doc_id']				=	$resultArr['docid'];
			if($reminder == 0){
				$paramsGET['reminder']			= $reminder;
			}	
			$curlParams2['postData'] 			= $paramsGET;
			$content_emp 						= Utility::curlCall($curlParams2);
			$retArr['data']						=	$content_emp;
			$retArr['errorCode']				=	0;
			$retArr['errorStatus']				=	'Data Returned';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Returned';
		}
		return json_encode($retArr);
	}
	
	function checkforupload($parentid,$city,$data_city,$ucode,$uname){
		$response	=	'';
		$curlParams2	=	array();
		$retArr	=	array();
		
		$resparr	=	array();
		if(trim($parentid)!=''){
			$resultArr                      	= 	array();
			$curlParams                     	= 	array();
			$datacity            				= 	((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/fetchAllDetails.php';
			$postArray['parentid'] 				= 	$parentid;
			$postArray['ucode'] 				= 	$ucode;
			$postArray['uname'] 				= 	$uname;
			$postArray['data_city'] 			= 	$datacity;
			$postArray['post_data']				= 	"1";		 
			$postArray['module']				= 	"TME";		 
			$postArray['action']				= 	"idgenerator";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
			$resultArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
			$docid								= 	$resultArr['docid'];
			$urlVLC 							=	WEB_SERVICES."vlc_details.php";
			$curlParams2['formate'] 			= 'basic';
			$curlParams2['method'] 				= 'post';
			$curlParams2['url']					=	$urlVLC;
			$paramsGET							=	array();
			$paramsGET['docid']					=	$docid;
			$paramsGET['city']					=	$city;
			$paramsGET['mode']					=	'fn';
			$curlParams2['postData'] 			= $paramsGET;
			$resparr 							= json_decode(Utility::curlCall($curlParams2),true);
			$retArr['data']						=	$resparr;
			$retArr['errorCode']				=	0;
			$retArr['errorStatus']				=	'Data Returned';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No Parentid Found';
		}
		return json_encode($retArr);
	}
	
	public function StoreCommentECS(){ // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['Comment'] 				= 	$params['Comment'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"StoreCommentECS";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function StoreCommentretention(){ // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['Comment'] 				= 	$params['Comment'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"StoreComment";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function remindLaterCallBack() { // 1
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['allocId'] 				= 	$params['allocId'];
		$postArray['pop_flag'] 				= 	1;
		$postArray['noparentid'] 			= 	1;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"removeCallBack";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function removeAllCallBack() { // done - 2
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['allocId'] 				= 	$params['allocId'];
		$postArray['pop_flag'] 				= 	2;
		$postArray['noparentid'] 			= 	1;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"removeCallBack";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function checkNewTmeCall() { // not in use
		return 1;
	}
	
	public function showContractBalance() { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['allocId'] 				= 	$params['allocId'];
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empId'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"showContractBalance";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function getSphinxId($parentid) {
		$extraData	=	json_decode($this->getMainTabExtraData($parentid),true);
		$retArr		=	array();
		if($extraData['errorCode']	==	0) {
			if($extraData['data']['sphinx_id']	!=	'') {
				$retArr['data']	=	$extraData['data']['sphinx_id'];
			} else {
				$retrSphxId	=	$this->getSphinxIdGen($parentid);
				if($retrSphxId['errorCode']	==	0) {
					$retArr['data']	=	$this->getSphinxIdGen($parentid);
					$retArr['errorCode']	=	0;
					$retArr['errorStatus']	=	'Sphinx Id Returned';
				} else {
					$retArr['errorCode']	=	1;
					$retArr['errorStatus']	=	'Sphinx Id Not Found';
				}
			}
		} else {
			$retrSphxId	=	$this->getSphinxIdGen($parentid);
			if($retrSphxId['errorCode']	==	0) {
				$retArr['data']	=	$this->getSphinxIdGen($parentid);
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Sphinx Id Returned';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Sphinx Id Not Found';
			}
		}
		return json_encode($retArr);
	}
	
	private function getSphinxIdGen($parentid) {
		$dbObjIro  	= 	new DB($this->db['db_iro']);
		$retArr	=	array();
		$query	=	"SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '" . $parentid . "'";
		$con	=	$dbObjIro->query($query);
		if($dbObjIro->numRows($con)) {
			$retArr['data']	=	$dbObjIro->fetchData($con);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Returned Successfully';
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Returned';
		}
		return $retArr;
	}
	
	public function fetchcities() { // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/fetchAllDetails.php';
		$postArray['noparentid'] 			= 	1;
		$postArray['ucode'] 				= 	$params['empcode'];
		$postArray['uname'] 				= 	$params['empname'];
		$postArray['term'] 				= 	$params['srchData'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"cityautosuggest";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function sendJdrrMail() { // Pending
		$retArr	=	array();
		$checkReviews	=	json_decode($this->getTmeSearchData($_POST['parentid']),1);
		if($checkReviews['errorCode']	==	0) {
			if($checkReviews['data']['reviews'] >= 5 && $checkReviews['data']['ratings'] >= 3.5) {
				$curlParams2 = array();
				$curlParams2['url'] = 'http://172.29.86.27/~sumeshdubey/index.php';
				$curlParams2['formate'] = 'basic';
				$curlParams2['method'] = 'post';
				$paramsGET	=	array();
				$paramsGET['parentid']	=	$_POST['parentid'];
				$paramsGET['emailId']	=	$_POST['emailId'];
				$paramsGET['dataCity']	=	$_POST['data_city'];
				$paramsGET['ratings']	=	$_POST['ratings'];
				$params_string = '';
				foreach ($paramsGET as $key => $value) {
					$params_string .= $key . '=' . $value . '&';
				}
				rtrim($params_string, '&');
				$curlParams2['postData'] = $params_string;
				$tmeInfo	=	Utility::curlCall($curlParams2);
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Mail Sent Successfully';
				return json_encode($retArr);
			} else {
				$retArr['errorCode']	=	2;
				$retArr['errorStatus']	=	'No reviews and ratings found';
				return json_encode($retArr);
			}
		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'No reviews and ratings found';
			return json_encode($retArr);
		}
	}
	
	public function getDispositionList() { // Done
		$retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= array();
        $curlParams 				= array();
        $paramsSend					= array();
        $paramsSend['empcode']		= $params['empcode'];
        $paramsSend['data_city']	= $params['data_city'];
        $paramsSend['action']		= 'getDispositionList';
        $data_city			 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php?data_city='.$data_city.'&action=getDispositionList&module=TME&post_data=1&allocid='.$params['allocid'].'&secondary_allocid='.$params['secondary_allocid'];
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}

	public function getModuleType() {// Done
		$retArr	=	array();
		GLOBAL $parseConf;
		$retArr[]	=	$parseConf['servicefinder']['module'];
		return json_encode($retArr);
	}
	
	public function getEcsEmpcode() {// Done
		$retArr	=	array();
		if(isset($_REQUEST['urlFlag'])){
			$params	=	array_merge($_POST,$_GET);
		}else{
			header('Content-Type: application/json');
			$params	=	json_decode(file_get_contents('php://input'),true);
		}
        $resultArr  				= array();
        $curlParams 				= array();
        $paramsSend					= array();
        $paramsSend['empcode']		= $params['empcode'];
        $paramsSend['data_city']	= $params['data_city'];
        $paramsSend['action']		= 'getEcsEmpcode';
        $data_city			 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams['url']     		= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/tmenewServices.php?data_city='.$data_city.'&action=getEcsEmpcode&module=TME&post_data=1&empcode='.$params['empcode'];
        $curlParams['formate']      = 'basic';
        $curlParams['headerJson']   = 'json';
        $singleCheck                = json_decode(Utility::curlCall($curlParams),true);
        return json_encode($singleCheck);
	}
	
	public function getContractDataInfo() { // Done
		header('Content-Type: application/json');
		$params						=	json_decode(file_get_contents('php://input'),true);
		$retArr						=	array();
		$paramsSend					=	array();
		$paramsSend['parentid']		= $params['parentid'];	
		$paramsSend['data_city']	= $params['data_city'];	
		$paramsSend['module']		= MODULE;
		$paramsSend['ucode']		= $params['ucode'];
		$paramsSend['downselFlag']  =  1;
		$curlParams 				= array();
		$data_city			 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url'] 			= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/cs_edit_check.php';
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	= json_encode($paramsSend); 
		$singleCheck				= Utility::curlCall($curlParams);
		return $singleCheck;
	}

	public function fetchLiveData() { // Done
        header('Content-Type: application/json');
        $params     				= json_decode(file_get_contents('php://input'),true);
        $paramsSend 				= array();
        $paramsSend['parentid']     = $params['parentid'];
        $paramsSend['data_city']    = $params['data_city'];
        $paramsSend['ucode']   		= $params['ucode'];
        $paramsSend['uname']   		= $params['uname'];
        $paramsSend['module']       = MODULE;
        $curlParams 				= array();
        $data_city			 		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams['url'] 			= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/fetchLiveData.php';
        $curlParams['formate'] 		= 'basic';
        $curlParams['method'] 		= 'post';
        $curlParams['headerJson'] 	= 'json';
        $curlParams['postData'] 	= json_encode($paramsSend);
        $singleCheck    			= Utility::curlCall($curlParams);
        return $singleCheck;
    }
    
	public function getJdrrPath() { // done - no changes 
			header('Content-Type: application/json');
			$params					= json_decode(file_get_contents('php://input'),true);
			$retArr					= array();
			$curlParams2 			= array();
			$curlParams2['url'] 	= '172.29.86.27/~sumeshdubey/get_jdrr_image.php';
			$curlParams2['formate'] = 'basic';
			$curlParams2['method'] 	= 'post';
			$paramsGET				= array();
			$paramsGET['parentid']	= $params['parentid'];
			$paramsGET['dataCity']	= $params['data_city'];
			$paramsGET['ratings']	= 1;
			$params_string 			= '';
			foreach ($paramsGET as $key => $value) {
				$params_string .= $key . '=' . $value . '&';
			}
			rtrim($params_string, '&');
			$curlParams2['postData'] = $params_string;
			$tmeInfo				 =	Utility::curlCall($curlParams2);
			$retArr['data'] 		 = $tmeInfo;
			$retArr['errorCode']	 =	0;
			$retArr['errorStatus']	 =	'Image found';
			return json_encode($retArr);
	}
	
	public function addjdrr() { // Done
		header('Content-Type: application/json');
		$params			= 	json_decode(file_get_contents('php://input'),true);
		$retArr			=	array();
		$parentid 		= 	$params['parentid'];
		$data_city 		= 	trim($params['data_city']);
		$version 		= 	$params['version'];
		$combo 			= 	$params['combo'];
		$curlParams2 	= 	array();
		$paramsGET 		= 	array();
		$data_city		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if($data_city == 'remote'){
			$paramsGET['remote']        =   1;
		}
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($data_city)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['method'] 		= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$paramsGET['parentid'] 		=  $parentid;
		$paramsGET['data_city'] 	= $data_city;
		$paramsGET['action'] 		= 3;
		$paramsGET['module']  		= 'tme';
		$paramsGET['combo']  		= $combo;
		$curlParams2['postData'] 	= json_encode($paramsGET);
		$jdrrresp					= Utility::curlCall($curlParams2);
		return $jdrrresp;
	}

	public function addjdrrLive($params) { // Done
		if($params == '') {
			header('Content-Type: application/json');
			$params		= json_decode(file_get_contents('php://input'),true);
		}
		$retArr								=	array();
		$parentid 							= $params['parentid'];
		$data_city 							= trim($params['data_city']);
		$version 							= $params['version'];
		$ecs_flag 							= $params['ecs_flag'];
		$combo 								= $params['combo'];
		$type 								= $params['omni_type'];
		$curlParams2 						= array();
		$paramsGET 							= array();
		$datacity							= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		if($datacity == 'remote'){
			$paramsGET['remote']        	=   1;
			}
		$curlParams2['url'] 				= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 			= 	'basic';
		$curlParams2['method'] 				= 	'post';
		$curlParams2['headerJson'] 			= 	'json';
		$paramsGET['parentid'] 				=  	$parentid;
		$paramsGET['data_city'] 			= 	$data_city;
		$paramsGET['ecs_flag'] 				= 	$ecs_flag;
		$paramsGET['combo'] 				= 	$combo;
		$paramsGET['action'] 				= 	1;
		$paramsGET['type'] 					= 	$type;
		$paramsGET['module']  				= 	'tme';
		$curlParams2['postData'] 			= 	json_encode($paramsGET);
		$jdrrresp							=	Utility::curlCall($curlParams2);
		$log_data['url'] 					= 	LOG_URL.'logs.php';
	    $post_data['ID']         			= 	$parentid;                
		$post_data['PUBLISH']    			= 	'TME';         	
		$post_data['ROUTE']      			= 	'omni flow';   		
		$post_data['CRITICAL_FLAG'] 		= 	1 ;			
		$post_data['MESSAGE']       		= 	'addjdrrLive';	
		$post_data['DATA_JSON']['params'] 	=  	$curlParams2['postData'];					
		$post_data['DATA']['user'] 			= 	$params['empId'];
		$post_data['DATA']['version'] 		= 	$version;
		$post_data['DATA_JSON']['response']	= 	$jdrrresp;
		$log_data['method'] 				= 	'post';
		$log_data['formate'] 				= 	'basic';
		$log_data['postData'] 				= 	 http_build_query($post_data);
		$log_res							=	Utility::curlCall($log_data);
		return $jdrrresp;
	}

	public function addbanner() { // Pending - Serverip.php Dependent No changes
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$instruction 	=  $params['instruction'];
		$s_deptCity 	= urlencode($params['s_deptCity']);
		$combo 			= $params['combo'];
		$curlParams2 	= array();
		$curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=1&client_specification=".urlencode($instruction)."&s_deptCity=".$s_deptCity."&combo=".$combo."&no_of_rotation=".$no_of_rotation;;
		$curlParams2['formate'] = 'basic';
		$jdrrresp	=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}


	public function addbannerLive($params) { // Pending - Serverip.php Dependent
		if($params == '') {
			header('Content-Type: application/json');
			$params		= json_decode(file_get_contents('php://input'),true);
		}
		$parentid 				= trim($params['parentid']);
		$version 				= trim($params['version']);
		$instruction 			=  trim($params['instruction']);
		$ecs_flag 				=  trim($params['ecs_flag']);
		$data_city 				=  trim(urlencode($params['data_city']));
		$combo 					= $params['omni_type']; //changed to campaign ID
		$curlParams2 			= array();
		if(isset($params['user_offer_jdrr']) && intval($params['user_offer_jdrr']) >= 0 && isset($params['user_mon_offer_jdrr']) && intval($params['user_mon_offer_jdrr']) >= 0 ) {
			 $curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=11&ecs_flag=".$ecs_flag."&data_city=".$data_city."&user_price=".$params['user_offer_jdrr']."&user_price_monthly=".$params['user_mon_offer_jdrr']."&combo=".$combo."&no_of_rotation=".$params['no_of_rotation'];
		}else {
			$curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".trim($parentid)."&version=".$version."&type=11&ecs_flag=".$ecs_flag."&data_city=".$data_city."&combo=".$combo."&no_of_rotation=".$params['no_of_rotation'];		
		}
		$curlParams2['formate'] 		= 'basic';
		$jdrrresp						=	Utility::curlCall($curlParams2);
		$log_data['url'] 				= LOG_URL.'logs.php';
	    $post_data['ID']        		= $parentid;                
		$post_data['PUBLISH']   		= 'TME';         	
		$post_data['ROUTE']     		= 'omni flow';   		
		$post_data['CRITICAL_FLAG']  	= 1 ;			
		$post_data['MESSAGE']        	= 'addbannerLive';	
		$post_data['DATA_JSON']['params'] =  $curlParams2['url'];					
		$post_data['DATA']['user'] 		= 	$params['empId'];
		$post_data['DATA']['version'] 	= $version;
		$post_data['DATA_JSON']['response']	 = 	$jdrrresp;
		$log_data['method'] 			= 'post';
		$log_data['formate'] 			= 	'basic';
		$log_data['postData'] 			= 	 http_build_query($post_data);
		$log_res						=	Utility::curlCall($log_data);
		return $jdrrresp;
	}


	public function bannerlog() { // Done
        return 0;
	}

	public function jdrrlog() { // Done
        return 0;
	}


	public function checkbanner() { // Pending - Serverip.php Dependent - No changes
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$curlParams2 	= array();
		$curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=0";
		$curlParams2['formate'] = 'basic';
		$jdrrresp		=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}


	public function checkjdrr() { // done
		$resArr				=	array();
		$resArr['code']		=	1;
		$resArr['msg']		=	'Not in use';
		return json_encode($resArr);
	}


	public function deletebanner() { // Pending - Serverip.php Dependent - No change
		header('Content-Type: application/json');
		$params		= json_decode(file_get_contents('php://input'),true);
		$parentid 	= $params['parentid'];
		$version 	= $params['version'];
		$curlParams2 = array();
		$curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=-2";
		$curlParams2['formate'] = 'basic';
		$jdrrresp	=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}

	public function deletebannerLive() { // Pending - Serverip.php Dependent
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$curlParams2 			= array();
		$curlParams2['url'] 	= DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=-1";
		$curlParams2['formate'] = 'basic';
		$jdrrresp				=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}


	public function deletejdrr() { // Done
		header('Content-Type: application/json');
		$params			= 	json_decode(file_get_contents('php://input'),true);
		$retArr			=	array();
		$parentid 		= 	$params['parentid'];
		$data_city 		= 	trim($params['data_city']);
		$version 		= 	$params['version'];
		$curlParams2 	= 	array();
		$paramsGET 		= 	array();
		$datacity		= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if($datacity == 'remote'){
			$paramsGET['remote']        =   1;
		}
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['method'] 		= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$paramsGET['parentid'] 		=  $parentid;
		$paramsGET['data_city'] 	= $data_city;
		$paramsGET['action'] 		= 4;
		$paramsGET['module']  		= 'tme';
		$curlParams2['postData'] 	= json_encode($paramsGET);
		$jdrrresp					=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}

	public function deletejdrrLive() { // Done
		header('Content-Type: application/json');
		$params							= json_decode(file_get_contents('php://input'),true);
		$retArr							=	array();
		$parentid 						= $params['parentid'];
		$datacity 						= urlencode($params['data_city']);
		$version 						= $params['version'];
		$curlParams2	 				= array();
		$paramsGET 						= array();
		$dataCity						= ((in_array(strtolower($datacity), $this->main_cities)) ? strtolower($datacity) : 'remote');
		if($dataCity == 'remote'){
			$paramsGET['remote']        =   1;
		}
		$curlParams2['url'] 			= $this->genioconfig['jdbox_url'][strtolower($dataCity)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 		= 'basic';
		$curlParams2['method'] 			= 'post';
		$curlParams2['headerJson'] 		= 'json';
		$paramsGET['parentid'] 			=  $parentid;
		$paramsGET['data_city'] 		= $datacity;
		$paramsGET['action'] 			= -1;
		$paramsGET['module']  			= 'tme';
		$curlParams2['postData'] 		= json_encode($paramsGET);
		$jdrrresp						=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}

	public function get_banner_spec() { // Pending - Serverip.php Dependent - No change
		header('Content-Type: application/json');
		$params		= json_decode(file_get_contents('php://input'),true);
		$parentid 	= $params['parentid'];
		$version 	= $params['version'];
		$curlParams2 = array();
		$curlParams2['url'] = DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=2";
		$curlParams2['formate'] = 'basic';
		$jdrrresp	=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	public function addjdomni() { // Done
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$dbObjLocal		= new DB($this->db['db_local']);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$data_city  	= urlencode($params['data_city']);
		$combo 			= $params['combo'];
		$type 			= $params['type'];
		$user_price 	= $params['user_price'];
		$user_price_monthly = $params['user_price_monthly'];
		$user_price_setup = $params['user_price_setup'];
		$ecs_flag 		= $params['ecs_flag'];
		$curlParams2 = array();
		$datacity		= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if(($user_price != '' && $user_price != 0) || ($user_price_monthly != '' && $user_price_monthly != 0) || ($user_price_setup != '' && $user_price_setup != 0) ) {
			$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=3&version=".$version."&combo=".$combo."&type=".$type."&user_price=".$user_price."&user_price_monthly=".$user_price_monthly."&user_price_setup=".$user_price_setup."&ecs_flag=".$ecs_flag;
		}else {
			$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=3&version=".$version."&combo=".$combo."&type=".$type."&ecs_flag=".$ecs_flag;
		}
		$curlParams2['formate'] 		= 	'basic';
		$jdrrresp						=	Utility::curlCall($curlParams2);
		$log_data['url'] 				= 	LOG_URL.'logs.php';
	    $post_data['ID']         		= 	$parentid;                
		$post_data['PUBLISH']    		= 	'TME';         	
		$post_data['ROUTE']      		= 	'addjdomni';   		
		$post_data['CRITICAL_FLAG']  	= 	1 ;			
		$post_data['MESSAGE']        	= 	'addjdomni';	
		$post_data['DATA']['url'] 		= 	$curlParams2['url'];
		$post_data['DATA']['response']	= 	$jdrrresp;
		$log_data['method'] 			= 	'post';
		$log_data['formate'] 			= 	'basic';
		$log_data['postData'] 			= 	 http_build_query($post_data);
		$log_res						=	Utility::curlCall($log_data);
		return $jdrrresp;
	}    

	public function addjdomniLive($params) { // Done
		if($params == '') {
			header('Content-Type: application/json');
			$params		= json_decode(file_get_contents('php://input'),true);
		}
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city  			= urlencode($params['data_city']);
		$ecs_flag  				= $params['ecs_flag'];
		$combo 					= $params['combo'];
		$type 					= $params['omni_type'];
		$setup_exclude 			= $params['setup_exclude'];
		$dependent 				= $params['dependent'];
		$curlParams2 			= array();
		$dataCity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		if((isset($params['user_price']) && $params['user_price'] != '' && $params['user_price'] != undefined &&  intval($params['user_price'])>0) || (isset($params['user_price_setup']) && $params['user_price_setup'] != '' && $params['user_price_setup'] != undefined &&  intval($params['user_price_setup'])>0)) {
			$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($dataCity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=1&version=".$version."&ecs_flag=".$ecs_flag."&user_price=".$params['user_price']."&user_price_monthly=".$params['user_price_monthly']."&combo=".$combo."&type=".$type."&setup_exclude=".$setup_exclude."&user_price_setup=".$params['user_price_setup']."&dependent=".$dependent;
		}else {
			$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($dataCity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=1&version=".$version."&ecs_flag=".$ecs_flag."&combo=".$combo."&type=".$type."&setup_exclude=".$setup_exclude."&dependent=".$dependent;
		}
		$curlParams2['formate'] 			= 'basic';
		$jdrrresp							= Utility::curlCall($curlParams2);
		$log_data['url'] 					= LOG_URL.'logs.php';
	    $post_data['ID']         			= $parentid;                
		$post_data['PUBLISH']    			= 'TME';         	
		$post_data['ROUTE']      			= 'omni flow';   		
		$post_data['CRITICAL_FLAG'] 		= 1 ;			
		$post_data['MESSAGE']       		= 'addjdomniLive';	
		$post_data['DATA_JSON']['params'] 	= $curlParams2['url'];					
		$post_data['DATA']['user'] 			= $params['empId'];
		$post_data['DATA']['version'] 		= $version;
		$post_data['DATA_JSON']['response']	= $jdrrresp;
		$log_data['method'] 				= 'post';
		$log_data['formate'] 				= 'basic';
		$log_data['postData'] 				=  http_build_query($post_data);
		$log_res							=  Utility::curlCall($log_data);
		return $jdrrresp;
	}
	
	public function deletejdomni() { // Done
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$data_city 		= urlencode($params['data_city']);
		$curlParams2 	= array();
		$datacity		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=4&version=".$version;
		$curlParams2['formate'] = 'basic';
		$jdrrresp	=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}

	public function deletejdomniLive() { // Done
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$data_city 		= urlencode($params['data_city']);
		$curlParams2 	= array();
		$datacity		= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=-1&version=".$version;
		$curlParams2['formate'] = 'basic';
		$jdrrresp	=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	
	public function payment_type() { // Done
		header('Content-Type: application/json');
		$params		= json_decode(file_get_contents('php://input'),true);
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				=	$params['parentid'];
		$postArray['type'] 					=	$params['type'];
		$postArray['version'] 				=	$params['version'];
		$postArray['payment_mode'] 			= 	$params['payment_mode'];
		$postArray['campaignids'] 			= 	$params['campaignids'];
		$postArray['original_flg'] 			= 	$params['original_flg'];
		$postArray['disc_flg'] 				= 	$params['disc_flg'];
		$postArray['twoyear_flg'] 			= 	$params['twoyear_flg'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"paymenttype";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		$log_data['url'] 					= 	LOG_URL.'logs.php';
	    $post_data['ID']         			= 	$params['parentid'];                
		$post_data['PUBLISH']    			= 	'ME';         	
		$post_data['ROUTE']      			= 	'payment_type';   		
		$post_data['CRITICAL_FLAG']  		= 	0 ;			
		$post_data['MESSAGE']        		= 	'payment_type';	
		$post_data['DATA']['payment_type'] 	= 	$params['type'];
		$post_data['DATA']['version'] 		= 	$params['version'];
		$log_data['method'] 				= 	'post';
		$log_data['formate'] 				= 	'basic';
		$log_data['postData'] 				= 	http_build_query($post_data);
		$log_res							=	Utility::curlCall($log_data);
		return json_encode($retArr);
	}
	
	public function campaignpricelist() { // Done
		header('Content-Type: application/json');
		$params				= json_decode(file_get_contents('php://input'),true);
		$parentid 			= $params['parentid'];
		$version 			= $params['version'];
		$data_city 			= urlencode($params['data_city']);
		$combo 				= $params['combo'];
		$omni_type 			= $params['omni_type'];
		$camp_selected 		= implode(",",$params['camp_selected']);
		$banner_rotation 	= $params['banner_rotation'];
		$curlParams2 		= array();
		$datacity           = ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=2&module=tme&version=".$version."&combo=".$combo."&type=".$omni_type."&camp_selected=".$camp_selected."&banner_rotation=".$banner_rotation;
		$curlParams2['formate'] = 'basic';
		$jdrrresp			=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	public function checkJdrrPlus($params){ // Done
		$params['parentid'] 		=   trim($params['parentid']);
		$params['data_city'] 		= 	trim(urlencode($params['data_city']));
		$params['action'] 			= 	1;
		$params['version'] 			=	trim($params['version']);
		$params['campaignidselected'] = 5;
		$params['module']  			= 'tme';
		$curlParams2 				= array();
		$datacity           		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)].'services/jdrrPlusCampaign.php';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['method']	 	= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$curlParams2['postData'] 	= json_encode($params);
		$jdrrresp					=	Utility::curlCall($curlParams2);
	}
	
	
	public function go_to_payment_page($params){ // Done
		header('Content-Type: application/json');
		$params								= json_decode(file_get_contents('php://input'),true);
		$parentid 							= $params['parentid'];
		$datacity 							= trim($params['data_city']);
		$params['s_deptCity'] 				= trim($params['data_city']);
		$version 							= $params['version'];
		$ecs_flag 							= $params['ecs_flag']; 
		$jdomni_selected					= 0;
		$banner_selected					= 0;
		$jdrr_selected						= 0;
		$domain_selected					= 0;
		$retArr								=	array();
		$curlParams2						=	array();
		$dataCity           				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 				=	$this->genioconfig['jdbox_url'][strtolower($dataCity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$datacity."&action=48&module=tme&version=".$version."&ecs_flag=".$ecs_flag;
		$curlParams2['formate'] 			= 'basic';
		$retArr								= json_decode(Utility::curlCall($curlParams2),1);
		if($retArr['errorCode'] == 0){
			foreach($retArr['data'] as $key=>$val){
					if($val['campaignid']=='22'){
						$jdrr_selected	=	$val['selected'];
					}else if ($val['campaignid']=='72' ) { 
						$jdomni_selected	=	$val['selected'];
					}else if ($val['campaignid']=='5' ) { 
						$banner_selected	=	$val['selected'];
					}else if ($val['campaignid']=='75' ) { 
						$appios_selected	=	$val['selected'];
					}else if ($val['campaignid']=='82' ) { 
						$email_selected		=	$val['selected'];
					}else if ($val['campaignid']=='83' ) { 
						$sms_selected		=	$val['selected'];
					}else if ($val['campaignid']=='86' ) {
						$ssl_selected		=	$val['selected'];
					}
					
			}
			if($jdrr_selected=='1'){
				$this->addjdrrLive($params);
			}
			if($jdrr_selected=='-1'){
				$this->deletejdrrLive($params);
			}
			if($jdomni_selected=='1'){
				$this->addjdomniLive($params);	
			}
			if($jdomni_selected=='-1'){ 
				$this->deletejdomniLive($params);
			}					
			if($banner_selected=='1'|| $params['omni_type'] == '735' || $params['omni_type'] == '747' || $params['omni_type'] == '746'){ //## changed to campaign ID
				$this->addbannerLive($params);	
			}
			if($banner_selected=='-1'){
				$this->deletebannerLive($params);
			}
			if($appios_selected=='1'){
				$this->addomnitemplatelive($params);	
			}
			if($appios_selected=='-1'){
				$this->deleteomnitemplatelive($params);
			}
			if($email_selected=='-1'){
				$this->deleteEmailLive($params);	 
			}
			if($sms_selected=='-1'){
				$this->deleteSmsLive($params);	 
			}
			if($ssl_selected	=='-1'){
				$this->deletesslpack($params);
			}
			$resArr['error_code'] = 0;
			$resArr['error_msg'] = "success";
		}else{
			$resArr['error_code'] = 0;
			$resArr['error_msg'] = "success";
		}
		$this->checkJdrrPlus($params);
		$paramsSend	=	array();
		$paramsSend['data_city']			=	$params['s_deptCity'];	
		$paramsSend['parentid']				=	$parentid;	
		$paramsSend['version']				=	$version;
		$paramsSend['action']				=	'pdg';	
		$paramsSend['module']				=	'tme';	
		$curlParams 						= 	array();	
		$datacity           				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url'] 					= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/pincodeSelection.php';
		$curlParams['formate'] 				= 	'basic';
		$curlParams['method'] 				= 	'post';
		$curlParams['headerJson'] 			= 	'json';
		$curlParams['postData'] 			= 	json_encode($paramsSend); 
		$singleCheck						=	Utility::curlCall($curlParams);
		$this->dependentcheck($params);
		echo json_encode($resArr);
	}
	
	public function dependentcheck($params =''){ // Done
		if($params == ''){
			header('Content-Type: application/json');
			$params     = json_decode(file_get_contents('php://input'),true);
		}
		$curlParams2 		= array();
		$datacity       	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$params['parentid']."&data_city=".$params['data_city']."&action=41&module=tme&version=".$params['version'];
        $curlParams2['formate'] = 'basic';
		$jdrrresp   		=   Utility::curlCall($curlParams2);
	}
	
	public function ecspricelist() { // Done
		header('Content-Type: application/json');
		$params			= json_decode(file_get_contents('php://input'),true);
		$parentid 		= $params['parentid'];
		$version 		= $params['version'];
		$data_city 		= urlencode($params['data_city']);
		$combo 			= $params['combo'];
		$omni_type 		= $params['omni_type'];
		$camp_selected 	= implode(",",$params['camp_selected']);
		$banner_rotation= $params['banner_rotation'];
		$curlParams2 	= array();
		$datacity       = ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=4&module=tme&version=".$version."&combo=".$combo."&type=".$omni_type."&camp_selected=".$camp_selected."&banner_rotation=".$banner_rotation;
		$curlParams2['formate'] = 'basic';
		$jdrrresp		=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	
	public function payment_summary_list() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$combo 					= $params['combo'];
		$omni_type 				= $params['omni_type'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=5&module=tme&version=".$version."&combo=".$combo."&type=".$omni_type;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return  $summary_list;
	}
	
	public function deletecampaign() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$usercode 				= $params['usercode'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
	 	$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=7&module=tme&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return    $summary_list;
	}
	
	function deleteallcampaigns(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$usercode 				= $params['usercode'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=19&module=tme&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return    $summary_list;
	}
	public function delete_unchecked() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$resArr 				= array();
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= trim($params['data_city']);
		$unck_arr 				= $params['unck_arr'];
		if($unck_arr[1] == 0){
			$this->deletecampaign($params);
		}
		if($unck_arr[5] == 0) {
			$this->delete_update(5,$parentid,$data_city);
		}
		if($unck_arr[22] == 0) {
			$this->delete_update(22,$parentid,$data_city);
		}
		if($unck_arr[72] == 0) {
			$this->delete_update(72,$parentid,$data_city);
			$this->delete_update(74,$parentid,$data_city);
			$this->delete_update(75,$parentid,$data_city);
		}
		if($unck_arr[225] == 0) {
			$this->delete_update(5,$parentid,$data_city);
			$this->delete_update(22,$parentid,$data_city);
		}
		if($unck_arr[74] == 0) {
			 $this->delete_update(22,$parentid,$data_city);
		}
		if($unck_arr[75] == 0) {
			 $this->delete_update(75,$parentid,$data_city);
		}
		if($unck_arr[82] == 0) {
			 $this->delete_update(82,$parentid,$data_city);
		}
		if($unck_arr[83] == 0) {
			 $this->delete_update(83,$parentid,$data_city);
		}
		$params['ecs_flag'] 		= 0;	
		$resArr['error_code'] 		= 0;
		$resArr['error_msg'] 		= "success";
		return json_encode($resArr);
	}
	 
	public function delete_update($campaignid,$parentid,$data_city) { // Done
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$datacity            				= 	((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$parentid;
		$postArray['campaignid'] 			= 	$campaignid;
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"deleteUpdate";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function call_disc_api() { // Done
		header('Content-Type: application/json');
		$params		= json_decode(file_get_contents('php://input'),true);
		$dbObjLocal	=	new DB($this->db['db_tme']);

		$parentid  = $params['parentid'];
		$version   = $params['version'];
		$data_city = urlencode($params['data_city']);
		$discount  = $params['discount'];
		$usercode  = $params['usercode'];
		
		if($parseConf['servicefinder']['remotecity'] == 1){
			$remote_flg        =   1;
		}else{
			$remote_flg        =   0;
		}
		
		
		$curlParams2 = array();
		$curlParams2['url'] = JDBOX_API."/services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=8&module=tme&version=".$version."&discount=".$discount."&usercode=".$usercode."&remote_flag=".$remote_flg ;
		$curlParams2['formate'] = 'basic';

		$summary_list	=	Utility::curlCall($curlParams2);
		
		//~ $logsql = "insert into tbl_omni_api_log(function,parentid,version,insert_date,api,response) values('call_disc_api','".$parentid."','".$version."',now(),'".addslashes(stripslashes($curlParams2['url']))."','".addslashes(stripslashes($summary_list))."')";
//~ 
		//~ $dbObjLocal->query($logsql);
		
		
		return   $summary_list;
	}
	
	public function check_ecs() { // Done
		header('Content-Type: application/json');
		$params				= json_decode(file_get_contents('php://input'),true);
		$parentid  			= $params['parentid'];
		$version   			= $params['version'];
		$data_city 			= urlencode($params['data_city']);
		$combo 				= $params['combo'];
		$module_name 		= $params['module_name'];
		$usercode			= $params['usercode'];
		$curlParams2 		= array();
		$datacity       	= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&usercode=".$usercode."&action=9&module=tme&version=".$version."&combo=".$combo."&module_name=".$module_name;
		$curlParams2['formate'] = 'basic';
		$summary_list	=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	
	public function get_bankdetialsmicr() {
        header('Content-Type: application/json');
        $params     			= json_decode(file_get_contents('php://input'),true);
        $parentid  				= $params['parentid'];
        $version   				= $params['version'];
        $data_city 				= urlencode($params['data_city']);
        $micr  					= $params['micr'];
        $curlParams2 			= array();
        $datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=13&micr_code=".$micr."&data_city=".$data_city."&module=tme";
        $curlParams2['formate'] = 'basic';
        $summary_list   		=   Utility::curlCall($curlParams2);
        return   $summary_list;
    }
    
	public function get_bankdetials() { // Done
		header('Content-Type: application/json');
		$params				= json_decode(file_get_contents('php://input'),true);
		$parentid  			= $params['parentid'];
		$version   			= $params['version'];
		$data_city 			= urlencode($params['data_city']);
		$ifcs  				= $params['ifcs'];
		$curlParams2 		= array();
		$datacity       	= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=1&ifsc_code=".$ifcs."&data_city=".$data_city."&module=tme";
		$curlParams2['formate'] = 'basic';
		$summary_list		=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function save_bankdetials() { // Done
		header('Content-Type: application/json');
		$params				= 	json_decode(file_get_contents('php://input'),true);
		$parentid  			= 	trim($params['parentid']);
		$version   			= 	$params['version'];
		$data_city 			= 	urlencode($params['data_city']);
		$ifcs  				= 	$params['ifcs'];
		$acc_num  			= 	$params['acc_num'];
		$acc_name  			= 	$params['acc_name'];
		$acc_type  			= 	$params['acc_type'];
		$bank_name  		= 	$params['bank_name'];
		$branch_location  	= 	$params['branch_location'];
		$bank_branch  		= 	$params['bank_branch'];
		$micr  				= 	$params['micr'];
		$curlParams2 		= 	array();
		$datacity       	= 	((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] = 	$this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=3&ifsc_code=".$ifcs."&data_city=".$data_city."&module=tme&parentid=".$parentid."&version=".$version."&acc_num=".$acc_num."&acc_hld_name=".urlencode($acc_name)."&acc_type=".$acc_type."&bank_name=".urlencode($bank_name)."&branch_location=".urlencode($branch_location)."&bank_branch=".urlencode($bank_branch)."&micr_code=".$micr;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	
	public function get_accountdetials() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$version   				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=2&data_city=".$data_city."&module=tme&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function check_upfront() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$version   				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$type 					= $params['type'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=10&module=tme&version=".$version."&type=".$type;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function customjdrrhandling($params){ // Done
		header('Content-Type: application/json');
		$params					=  json_decode(file_get_contents('php://input'),true);
		$params['parentid'] 	=  trim($params['parentid']);
		$params['data_city'] 	=  trim(urlencode($params['data_city']));
		$params['action'] 		=  3;
		$params['version'] 		=	trim($params['version']);
		$params['module']  		= 	'tme';
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)].'services/jdrrPlusCampaign.php';
		$curlParams2['formate'] = 'basic';
		$curlParams2['formate'] = 'basic';
		$curlParams2['method'] 	= 'post';
		$curlParams2['headerJson'] = 'json';
		$curlParams2['postData'] = json_encode($params);
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	public function jdrrplusdiscount() { // Done
		header('Content-Type: application/json');
		$params								= json_decode(file_get_contents('php://input'),true);
		$parentid 							= $params['parentid'];
		$version 							= $params['version'];
		$data_city  						= urlencode($params['data_city']);
		$ecs_flag  							= $params['ecs_flag'];
		$curlParams2 						= array();
		$datacity       					= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 				= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/jdrrPlusCampaign.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=2&version=".$version."&ecs_flag=".$ecs_flag."&user_price=".$params['user_offer']."&user_price_monthly=".$params['user_mon_offer']; 
		$curlParams2['formate'] 			= 'basic';
		$jdrrresp							=	Utility::curlCall($curlParams2);
		$log_data['url'] 					= LOG_URL.'logs.php';
	    $post_data['ID']        			= $parentid;                
		$post_data['PUBLISH']   			= 'TME';         	
		$post_data['ROUTE']     			= 'omni flow';   		
		$post_data['CRITICAL_FLAG']  		= 1 ;			
		$post_data['MESSAGE']       		= 'jdrrdiscount';	
		$post_data['DATA_JSON']['params'] 	=  $curlParams2['url'];					
		$post_data['DATA']['user'] 			= 	$params['empId'];
		$post_data['DATA']['version'] 		= $version;
		$post_data['DATA_JSON']['response']	= 	$jdrrresp;
		$log_data['method'] 				= 'post';
		$log_data['formate'] 				= 	'basic';
		$log_data['postData'] 				= 	 http_build_query($post_data);
		$log_res							=	Utility::curlCall($log_data);
		return $jdrrresp;
	}
	
	public function tempactualbudgetupdate(){ // Done
		header('Content-Type: application/json');
		$params					= 	json_decode(file_get_contents('php://input'),true);
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2 			= 	array();
		$curlParams2['url'] 	=  $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".trim($params['parentid'])."&data_city=".trim(urlencode($params['data_city']))."&action=49&module=tme&version=".$params['version'];
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	public function checkdomainavailibilty(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
	    $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/domain_service.php?usercode=".$params['user_code']."&parentid=".$params['parentid']."&data_city=".urlencode($params['data_city'])."&module=tme&action=1&version=".$params['version']."&domainname=".urlencode($params['domain_name'])."&tlds=".rtrim($params['tlds'],',');
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	public function domainregisterauto() { // Done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['noparentid'] 			= 	1;
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"domainregisterauto";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
    }

    public function getforgetLink(){ // Done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['noparentid'] 			= 	1;
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	$params['empCode'];
		$postArray['registername'] 			= 	$params['registername'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getforgetLink";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
    }



	public function saveomnidomains(){ // Done
        header('Content-Type: application/json');
        $params     					= json_decode(file_get_contents('php://input'),true);
        $curlParams2 					= array();
        $datacity       				= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 			= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?data_city=".urlencode($params['data_city'])."&module=tme&action=2&usercode=".$params['user_code']."&parentid=".$params['parentid']."&version=".$params['version']."&website1=".urlencode($params['website1'])."&website2=".urlencode($params['website2'])."&website3=".urlencode($params['website3'])."&payment_type=".urlencode($params['payment_type'])."&own_website=".$params['own_website']."&combo=".$params['combo'].'&domain_registername='.urlencode($params['domain_registername']).'&domain_userid='.$params['domain_userid'].'&domain_pass='.$params['domain_pass'].'&domain_regiter_emailId='.$params['domain_regiter_emailId'].'&domainReg_forget_link='.urlencode($params['domainReg_forget_link']).'&action_flag_forget='.$params['action_flag_forget'].'&action_flag_forgetstatus='.urlencode($params['action_flag_forgetstatus']).'&omni_domain_option='.urlencode($params['omni_domain_option']);
        $curlParams2['formate'] 		= 'basic';
        $jdrrresp   					= Utility::curlCall($curlParams2);
        $log_data['url'] 				= LOG_URL.'logs.php';
        $post_data['ID']         		= $params['parentid'];
        $post_data['PUBLISH']    		= 'TME';
        $post_data['ROUTE']      		= 'saveomnidomains';
        $post_data['CRITICAL_FLAG']  	= 1 ;
        $post_data['MESSAGE']        	= 'saveomnidomains';
        $post_data['DATA']['user'] 		= $params['user_code'];
        $post_data['DATA']['version'] 	= $version;
        $post_data['DATA']['url']    	= $curlParams2['url'];
        $post_data['DATA']['response']  = $jdrrresp;
        $log_data['method'] 			= 'post';
        $log_data['formate']    		= 'basic';
        $log_data['postData']   		= http_build_query($post_data);
        $log_res    					= Utility::curlCall($log_data);
        return $jdrrresp;
    }
	
	public function getowndomainname(){ // Done
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag']) &&  $_REQUEST['urlFlag'] == 1){
			$params				= $_REQUEST;
		}else{
			$params				= json_decode(file_get_contents('php://input'),true);
		}
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?data_city=".urlencode($params['data_city'])."&module=tme&action=1&usercode=".$params['user_code']."&parentid=".$params['parentid']."&version=".$params['version'];
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	public function deletedomainname(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url']		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?data_city=".urlencode($params['data_city'])."&module=tme&action=5&usercode=".$params['usercode']."&parentid=".$params['parentid']."&version=".$params['version'];
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);

	}
	
	public function checkemail(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$params['parentid']."&data_city=".urlencode($params['data_city'])."&module=tme&action=5&version=".$params['version']."&other_parameter=".$params['other_parameter'];
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	public function getpricelist(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/domain_service.php?usercode=".$params['usercode']."&parentid=".$params['parentid']."&action=4&data_city=".urlencode($params['data_city'])."&module=tme&version=".$params['version'];
		$curlParams2['formate'] = 'basic';
		return $jdrrresp		=	Utility::curlCall($curlParams2);
	}
	
	
	public function addjdrrplus() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$instruction 			=  $params['instruction'];
		$s_deptCity 			= urlencode($params['s_deptCity']); // equal to data_city
		$combo 					= $params['combo'];
		$type 					= $params['type'];
		$curlParams2 			= array();
		$curlParams2['url'] 	= DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=1&client_specification=".urlencode($instruction)."&s_deptCity=".$s_deptCity."&combo=".$combo;
		$curlParams2['formate'] = 'basic';
		$jdrrresp				=	Utility::curlCall($curlParams2);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if($datacity == 'remote'){
			$paramsGET['remote']        =   1;
		}
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['method'] 		= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$paramsGET['parentid'] 		=  $parentid;
		$paramsGET['data_city'] 	= $s_deptCity;
		$paramsGET['action'] 		= 3;
		$paramsGET['module']  		= 'tme';
		$paramsGET['type']  		= $type;
		$curlParams2['postData'] 	= json_encode($paramsGET);
		$jdrrresp					=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	
	public function deletejdrrplus() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$s_deptCity 			= urlencode($params['s_deptCity']);
		$curlParams2 			= array();
		$curlParams2['url'] 	=DECS_TME."/api/banner_action.php?parentid=".$parentid."&version=".$version."&type=-2"."&s_deptCity=".$s_deptCity;
		$curlParams2['formate'] = 'basic';
		$jdrrresp				=	Utility::curlCall($curlParams2);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		if($datacity == 'remote'){
			$paramsGET['remote']        =   1;
		}
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)].'services/populate_jdrr_budget.php';
		$curlParams2['formate'] 	= 'basic';
		$curlParams2['method'] 		= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$paramsGET['parentid'] 		=  $parentid;
		$paramsGET['data_city'] 	= $s_deptCity;
		$paramsGET['action'] 		= 4;
		$paramsGET['module']  		= 'tme';
		$curlParams2['postData'] 	= json_encode($paramsGET);
		$jdrrresp					=	Utility::curlCall($curlParams2);
		return $jdrrresp;
	}
	
	public function combopackageprice() { // Done
		header('Content-Type: application/json');
		$params				= json_decode(file_get_contents('php://input'),true);
		$parentid 			= $params['parentid'];
		$version 			= $params['version'];
		$data_city 			= urlencode($params['data_city']);
		$combo 				= $params['combo'];
		$curlParams2 		= array();
		$datacity       	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] = $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=12&module=tme&version=".$version."&combo=".$combo;
		$curlParams2['formate'] = 'basic';
		$price				=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	
	public function combocustomprice() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$combo_price 			= $params['combo_price'];
		$domain_field_incl 		= $params['domain_field_incl'];
		$type 					= $params['type'];
		$custom_setup_fees 		= $params['custom_setup_fees'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=13&module=tme&version=".$version."&combo_cust_price=".$combo_price."&domain_field_incl=".$domain_field_incl."&type=".$type."&custom_setup_fees=".$custom_setup_fees;
		$curlParams2['formate'] = 'basic';
		$price					=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	public function combopricereset() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=14&module=tme&version=".$version;
		$curlParams2['formate'] = 'basic';
		$price					=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	public function comboprice() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=15&module=tme&version=".$version;
		$curlParams2['formate'] = 'basic';
		$price					=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	public function combopricelist() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=16&module=tme&version=".$version;
		$curlParams2['formate'] = 'basic';
		$price					=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	public function combopricemin() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid 				= $params['parentid'];
		$version 				= $params['version'];
		$data_city 				= urlencode($params['data_city']);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=17&module=tme&version=".$version;
		$curlParams2['formate'] = 'basic';
		$price					=	Utility::curlCall($curlParams2);
		return $price;
	}
	
	public function setTemplateId() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$template_id  			= $params['template_id'];
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&data_city=".$data_city."&action=8&module=tme&template_id=".$template_id."&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	
	public function sendomnidemo() { // Done
		header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag']) &&  $_REQUEST['urlFlag'] == 1){
			$params		= $_REQUEST;
		}else{
			$params		= json_decode(file_get_contents('php://input'),true);
		}
		$parentid  = $params['parentid'];
		$data_city = urlencode($params['data_city']);
		$version  = $params['version'];
		$usercode  = $params['usercode'];
		$mobile  = rtrim($params['mobile'],',');
		$emailid  = urlencode(rtrim($params['emailid'],','));
		$username  = urlencode($params['username']);
		$national_catid = $params['national_catid'];
		
		$getLinkVal =  $params['getLinkVal'];
		
		$dbObjTme	=	new DB($this->db['db_tme']);
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $parentid;
			$mongo_inputs['data_city'] 	= $data_city;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "contact_person";
			$rowdata = $this->mongo_obj->getData($mongo_inputs);
			$numRows = count($data_res);
		}
		else
		{
			$query		=	"SELECT companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$contractid."'";
			$con		=	$dbObjTme->query($query);
			$numRows	=	$dbObjTme->numRows($con);
			$rowdata =   $dbObjTme->fetchData($con);
		}
		
		 if(count($rowdata) > 0) {
			$contactperson = $rowdata['contact_person'];
		}
		//get categories
		$dbObjTme   =   new DB($this->db['db_iro']);
		$dbiro   =   new DB($this->db['db_iro']);
		$sql="select docid from db_iro.tbl_id_generator where parentid='".$parentid."'";
		$query=$dbiro->query($sql);
		if($dbiro->numRows($query)>0){
			while($row=$dbiro->fetchData($query)){
				$docid=$row['docid'];
		}
	}
		//~ $categories =   json_decode($this->ShowCatBform($params['parentid']),true);
		$categoriesParams['url'] = JDBOX_API.'/services/contract_category_info.php?parentid='.$parentid.'&data_city='.$data_city.'&module=TME&post_data=1';
		$categoriesParams['formate'] = 'basic';
		$categoriesParams['method'] = 'post';
		$categories	=	json_decode(Utility::curlCall($categoriesParams),1);
	if($categories["error"]["code"] == 0){
		$livepaid = array_keys($categories["data"]["LIVE"]["PAID"]);
		$livenonpaid = array_keys($categories["data"]["LIVE"]["NONPAID"]);
		$temppaid = array_keys($categories["data"]["TEMP"]["PAID"]);
		$tempnonpaid = array_keys($categories["data"]["TEMP"]["NONPAID"]);
		$livearr = array_merge( (array)$livepaid, (array)$livenonpaid);
		$temparr = array_merge( (array)$temppaid, (array)$tempnonpaid);
		$finalarr = array_merge( (array)$livearr, (array)$temparr);

			$implode_arr = implode(',',$finalarr);
			$sqlcatinfo="SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$implode_arr.");";
			$con    =   $dbObjTme->query($sqlcatinfo);
			$dbObjTme->numRows($con);
 		if($con && $dbObjTme->numRows($con)>0)
 		{	
			while($sqlcatinforow= $dbObjTme->fetchData($con)){
				$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
				$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];
			}
			$postdata=json_encode($catdata);
		}
	}
		
		$curlParamstemp = array();
		$postdataarr = array();
		$curlParamstemp['url'] = JDOMNIDEMO."?action=templateThemeInfo&docid=".$docid;//template api to be called before sendomnilink
		$curlParamstemp['formate'] = 'basic';
		$postdataarr['docid']=$docid;
		$postdataarr['data']=($postdata);
		$curlParamstemp['method'] = 'post';
		$curlParamstemp['formate']    =   'basic';
		$curlParamstemp['postData']   =    $postdataarr;
		$tmplateAPIres   =   json_decode(Utility::curlCall($curlParamstemp),1);
		
		$curlParams2 = array();
		if($getLinkVal == 0){
			$curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid."&firstname=".urlencode($contactperson)."&employee_code=".$params['usercode'];//API got changed
		}else if($getLinkVal == 1){
			$curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid."&firstname=".urlencode($contactperson)."&employee_code=".$params['usercode']."&getlink=1";//API got changed
		}	 	
        $curlParams2['formate'] = 'basic';
        $postdataarr['docid']=$docid;
		$postdataarr['firstname']= $contactperson;
		$postdataarr['employee_code']=$params['usercode'];
		$postdataarr['data']=($postdata);
		$curlParams2['postData']   =    $postdataarr;
		$curlParams2['method'] = 'post';
        $summary_list   =   json_decode(Utility::curlCall($curlParams2),1);
        $retArr = array();
        $retArr['error'] = array();
		if($summary_list['isError'] == false) {
			$retArr['error']['code']	=	0;
			$retArr['error']['msg']	=	"Link sent successfully";
		} else {
			$retArr['error']['code']	=	1;
			$retArr['error']['msg']	=	"Error in the link";
		}
		if(isset($summary_list['result'])) {
			$retArr['error']['result']	=	$summary_list['result'];
		}
		
        $log_data['url'] = LOG_URL.'logs.php';

        $post_data['ID']         = $params['parentid'];
        $post_data['PUBLISH']    = 'ME';
        $post_data['ROUTE']      = 'sendomnidemo';
        $post_data['CRITICAL_FLAG']  = 1 ;
        $post_data['MESSAGE']        = 'sendomnidemo';
        $post_data['DATA']['user'] =    $usercode;
        $post_data['DATA']['version'] = $version;
        $post_data['DATA']['url']    = $curlParams2['url'];
        $post_data['DATA']['response']   = $summary_list;

        $log_data['method'] = 'post';
        $log_data['formate']    =   'basic';
        $log_data['postData']   =    http_build_query($post_data);
        $log_res    =   Utility::curlCall($log_data);


        return   json_encode($retArr);
	}

		public function sendYOWlink() { // Done
        header('Content-Type: application/json');
		if(isset($_REQUEST['urlFlag']) &&  $_REQUEST['urlFlag'] == 1){
			$params		= $_REQUEST;
		}else{
			$params		= json_decode(file_get_contents('php://input'),true);
		}
		//~ print_r($params);
		$finalarr = array();
        $parentid  = $params['parentid'];
        $data_city = urlencode($params['data_city']);
        $version  = $params['version'];
        $usercode  = $params['usercode'];
        $mobile  = rtrim($params['mobile'],',');
        $emailid  = urlencode(rtrim($params['emailid'],','));
        $username  = urlencode($params['username']);
        $national_catid = $params['national_catid'];
        $checkflag = $params['checkflg'];
		//get docid
		//~ $docidenc		=	json_decode($this->fetch_docid($params['parentid']),true);
		//~ $docid			= 	$docidenc['data'];
		$dbiro   =   new DB($this->db['db_iro']);
		$sql="select docid from db_iro.tbl_id_generator where parentid='".$parentid."'";
		$query=$dbiro->query($sql);
		if($dbiro->numRows($query)>0){
			while($row=$dbiro->fetchData($query)){
				$docid=$row['docid'];
		}
	}
	
	$dbObjTme	=	new DB($this->db['db_tme']);
		
		$curlParams2 = array();
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $parentid;
			$mongo_inputs['data_city'] 	= $data_city;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "contact_person";
			$rowdata = $this->mongo_obj->getData($mongo_inputs);
			$numRows = count($data_res);
		}
		else
		{
			$query		=	"SELECT companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$contractid."'";
			$con		=	$dbObjTme->query($query);
			$numRows	=	$dbObjTme->numRows($con);
			$rowdata =   $dbObjTme->fetchData($con);
		}
		
		 if(count($rowdata) > 0) {
			$contactperson = $rowdata['contact_person'];
		}
	
		
	
		//api key		
		$api_key = hash('sha256', (date('Y-m-d') . $docid . 'websitestatus'));		
		//get categories
		$dbObjTme   =   new DB($this->db['db_iro']);
		//~ $categories =   json_decode($this->ShowCatBform($params['parentid']),true);
		$categoriesParams['url'] = JDBOX_API.'/services/contract_category_info.php?parentid='.$parentid.'&data_city='.$data_city.'&module=TME&post_data=1';
		$categoriesParams['formate'] = 'basic';
		$categoriesParams['method'] = 'post';
		$categories	=	json_decode(Utility::curlCall($categoriesParams),1);
	if($categories["error"]["code"] == 0){
		$livepaid = array_keys($categories["data"]["LIVE"]["PAID"]);
		$livenonpaid = array_keys($categories["data"]["LIVE"]["NONPAID"]);
		$temppaid = array_keys($categories["data"]["TEMP"]["PAID"]);
		$tempnonpaid = array_keys($categories["data"]["TEMP"]["NONPAID"]);
		$livearr = array_merge( (array)$livepaid, (array)$livenonpaid);
		$temparr = array_merge( (array)$temppaid, (array)$tempnonpaid);
		$finalarr = array_merge( (array)$livearr, (array)$temparr);

			$implode_arr = implode(',',$finalarr);
			$sqlcatinfo="SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$implode_arr.");";
			$con    =   $dbObjTme->query($sqlcatinfo);
			$dbObjTme->numRows($con);
 		if($con && $dbObjTme->numRows($con)>0)
 		{	
			while($sqlcatinforow= $dbObjTme->fetchData($con)){
				$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
				$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];
			}
			//~ print_r($catdata);
			$curlParams1 = array();
			$postdata=array();
			$postdatasend=array();
			$postdatasend['data']= $catdata; 
			$postdatasend['docid']=$docid;
			$postdata=json_encode($catdata);
			$curlParams1['url']=JDOMNIYOW."?action=getWebsiteStatus"."&docid=".$postdatasend['docid']."&key=".$api_key;
			$postdataarr['docid']=$docid;
			$postdataarr['data']=($postdata);
			$curlParams1['method'] = 'post';
			$curlParams1['formate']    =   'basic';
			$curlParams1['postData']   =    $postdataarr;
			$checkeligibility_API    =    json_decode(Utility::curlCall($curlParams1),1); 
			if($checkeligibility_API['isSuccess'] == 1)//yes condition
			{
				$curlParams2 = array();
				if($checkflag == 0){
		 $curlParams2['url'] = JDBOX_API."/services/getOmniDetails.php?data_city=".$data_city."&module=tme&action=10&usercode=".$usercode."&parentid=".$parentid."&version=".$version."&email=".$emailid."&mobile=".$mobile."&username=".$username."&national_catid=".$sqlcatinforow['national_catid']."&callFrom=";
				}else if($checkflag == 1){
		 	$curlParams2['url'] = JDBOX_API."/services/getOmniDetails.php?data_city=".$data_city."&module=tme&action=10&usercode=".$usercode."&parentid=".$parentid."&version=".$version."&email=".$emailid."&mobile=".$mobile."&username=".$username."&national_catid=".$sqlcatinforow['national_catid']."&callFrom=salesapi";
				}
				//~ $curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid;//API got changed
			 // $curlParams2['url'] = JDOMNIYOW."?action=getWebsiteStatus&docid=".$docid."&key=".$api_key;//may api
				$curlParams2['formate'] = 'basic';
				$tempresp =  Utility::curlCall($curlParams2);
				$summary_list   =   json_decode($tempresp,1);
				$retArr = array();
				$retArr['error'] = array();
				if($summary_list['error']['code'] == 0) {
					$retArr['error']['code']	=	0;
					$retArr['error']['msg']	=	"Link sent successfully";
				} else {
					$retArr['error']['code']	=	1;
					$retArr['error']['msg']	=	"Error in the link";
				}
				if(isset($summary_list['result'])) {
					$retArr['error']['result']	=	$summary_list['result'];
				}else if(isset($summary_list['data'])) {
					$retArr['error']['result']	=	$summary_list['data'];
				}
				$retArr['check'] = 1;
				$log_data['url'] = LOG_URL.'logs.php';

				$post_data['ID']         = $params['parentid'];
				$post_data['PUBLISH']    = 'ME';
				$post_data['ROUTE']      = 'sendomnidemo';
				$post_data['CRITICAL_FLAG']  = 1 ;
				$post_data['MESSAGE']        = 'sendomnidemo';
				$post_data['DATA']['user'] =    $usercode;
				$post_data['DATA']['version'] = $version;
				$post_data['DATA']['url']    = $curlParams2['url'];
				$post_data['DATA']['response']   = $summary_list;

				$log_data['method'] = 'post';
				$log_data['formate']    =   'basic';
				$log_data['postData']   =    http_build_query($post_data);
				$log_res    =   Utility::curlCall($log_data);
			}else{// no condition
				$curlParams2 = array();
				//$curlParams2['url'] = JDBOX_API."/services/getOmniDetails.php?data_city=".$data_city."&module=me&action=10&usercode=".$usercode."&parentid=".$parentid."&version=".$version."&email=".$emailid."&mobile=".$mobile."&username=".$username."&national_catid=".$national_catid;
				$dbiro   =   new DB($this->db['db_iro']);
					$sql="select docid from db_iro.tbl_id_generator where parentid='".$parentid."'";
					$query=$dbiro->query($sql);
					if($dbiro->numRows($query)>0){
						while($row=$dbiro->fetchData($query)){
							$docid=$row['docid'];
					}
				}
		//api key		
		$api_key = hash('sha256', (date('Y-m-d') . $docid . 'websitestatus'));		
		//get categories
		$dbObjTme   =   new DB($this->db['db_iro']);
		//~ $categories =   json_decode($this->ShowCatBform($params['parentid']),true);
		$categoriesParams['url'] = JDBOX_API.'/services/contract_category_info.php?parentid='.$parentid.'&data_city='.$data_city.'&module=TME&post_data=1';
		$categoriesParams['formate'] = 'basic';
		$categoriesParams['method'] = 'post';
		$categories	=	json_decode(Utility::curlCall($categoriesParams),1);
		if($categories["error"]["code"] == 0){
		$livepaid = array_keys($categories["data"]["LIVE"]["PAID"]);
		$livenonpaid = array_keys($categories["data"]["LIVE"]["NONPAID"]);
		$temppaid = array_keys($categories["data"]["TEMP"]["PAID"]);
		$tempnonpaid = array_keys($categories["data"]["TEMP"]["NONPAID"]);
		$livearr = array_merge( (array)$livepaid, (array)$livenonpaid);
		$temparr = array_merge( (array)$temppaid, (array)$tempnonpaid);
		$finalarr = array_merge( (array)$livearr, (array)$temparr);

			$implode_arr = implode(',',$finalarr);
			$sqlcatinfo="SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$implode_arr.");";
			$con    =   $dbObjTme->query($sqlcatinfo);
			$dbObjTme->numRows($con);
			if($con && $dbObjTme->numRows($con)>0)
			{	
				while($sqlcatinforow= $dbObjTme->fetchData($con)){
					$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
					$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
					$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
					$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];
				}
				$postdata=json_encode($catdata);
			}
			
		}
			
		$curlParamstemp = array();
		$postdataarr = array();
		$curlParamstemp['url'] = JDOMNIDEMO."?action=templateThemeInfo&docid=".$docid;//template api to be called before sendomnilink
		$curlParamstemp['formate'] = 'basic';
		$postdataarr['docid']=$docid;
		$postdataarr['data']=($postdata);
		$curlParamstemp['method'] = 'post';
		$curlParamstemp['formate']    =   'basic';
		$curlParamstemp['postData']   =    $postdataarr;
		$tmplateAPIres   =   json_decode(Utility::curlCall($curlParamstemp),1);
				$curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid;//API got changed
				$curlParams2['formate'] = 'basic';
				$summary_list   =   json_decode(Utility::curlCall($curlParams2),1);
				$retArr = array();
				$retArr['error'] = array();
				if($summary_list['isError'] == false) {
					$retArr['error']['code']	=	0;
					$retArr['error']['msg']	=	"Link sent successfully";
				} else {
					$retArr['error']['code']	=	1;
					$retArr['error']['msg']	=	"Error in the link";
				}
				if(isset($summary_list['result'])) {
					$retArr['error']['result']	=	$summary_list['result'];
				}
				$retArr['check'] = 0;
				$log_data['url'] = LOG_URL.'logs.php';

				$post_data['ID']         = $params['parentid'];
				$post_data['PUBLISH']    = 'ME';
				$post_data['ROUTE']      = 'sendomnidemo';
				$post_data['CRITICAL_FLAG']  = 1 ;
				$post_data['MESSAGE']        = 'sendomnidemo';
				$post_data['DATA']['user'] =    $usercode;
				$post_data['DATA']['version'] = $version;
				$post_data['DATA']['url']    = $curlParams2['url'];
				$post_data['DATA']['response']   = $summary_list;

				$log_data['method'] = 'post';
				$log_data['formate']    =   'basic';
				$log_data['postData']   =    http_build_query($post_data);
				$log_res    =   Utility::curlCall($log_data);
			}
		}else{
			//not categories present
		
				$curlParams2 = array();
				//$curlParams2['url'] = JDBOX_API."/services/getOmniDetails.php?data_city=".$data_city."&module=me&action=10&usercode=".$usercode."&parentid=".$parentid."&version=".$version."&email=".$emailid."&mobile=".$mobile."&username=".$username."&national_catid=".$national_catid;
				$dbiro   =   new DB($this->db['db_iro']);
		
		//api key		
		$api_key = hash('sha256', (date('Y-m-d') . $docid . 'websitestatus'));		
		//get categories
		$dbObjTme   =   new DB($this->db['db_iro']);
		//~ $categories =   json_decode($this->ShowCatBform($params['parentid']),true);
		$categoriesParams['url'] = JDBOX_API.'/services/contract_category_info.php?parentid='.$parentid.'&data_city='.$data_city.'&module=TME&post_data=1';
		$categoriesParams['formate'] = 'basic';
		$categoriesParams['method'] = 'post';
		$categories	=	json_decode(Utility::curlCall($categoriesParams),1);
	if($categories["error"]["code"] == 0){
		$livepaid = array_keys($categories["data"]["LIVE"]["PAID"]);
		$livenonpaid = array_keys($categories["data"]["LIVE"]["NONPAID"]);
		$temppaid = array_keys($categories["data"]["TEMP"]["PAID"]);
		$tempnonpaid = array_keys($categories["data"]["TEMP"]["NONPAID"]);
		$livearr = array_merge( (array)$livepaid, (array)$livenonpaid);
		$temparr = array_merge( (array)$temppaid, (array)$tempnonpaid);
		$finalarr = array_merge( (array)$livearr, (array)$temparr);

			$implode_arr = implode(',',$finalarr);
			$sqlcatinfo="SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$implode_arr.");";
			$con    =   $dbObjTme->query($sqlcatinfo);
			$dbObjTme->numRows($con);
 		if($con && $dbObjTme->numRows($con)>0)
 		{	
			while($sqlcatinforow= $dbObjTme->fetchData($con)){
				$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
				$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];
			}
			$postdata=json_encode($catdata);
		}
	}
		$curlParamstemp = array();
		$postdataarr = array();
		$curlParamstemp['url'] = JDOMNIDEMO."?action=templateThemeInfo&docid=".$docid;//template api to be called before sendomnilink
		$curlParamstemp['formate'] = 'basic';
		$postdataarr['docid']=$docid;
		$postdataarr['data']=($postdata);
		$curlParamstemp['method'] = 'post';
		$curlParamstemp['formate']    =   'basic';
		$curlParamstemp['postData']   =    $postdataarr;
		$tmplateAPIres   =   json_decode(Utility::curlCall($curlParamstemp),1);
		$curlParams2 = array();
	 	$curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid."&firstname=".urlencode($contactperson)."&employee_code=".$params['usercode'];;//API got changed
        $curlParams2['formate'] = 'basic';
        $postdataarr['docid']=$docid;
		$postdataarr['firstname']= $contactperson;
		$postdataarr['employee_code']=$params['usercode'];
		$postdataarr['data']=($postdata);
		$curlParams2['postData']   =    $postdataarr;
		$curlParams2['method'] = 'post';
        $summary_list   =   json_decode(Utility::curlCall($curlParams2),1);
				$retArr = array();
				$retArr['error'] = array();
				if($summary_list['isError'] == false) {
					$retArr['error']['code']	=	0;
					$retArr['error']['msg']	=	"Link sent successfully";
				} else {
					$retArr['error']['code']	=	1;
					$retArr['error']['msg']	=	"Error in the link";
				}
				if(isset($summary_list['result'])) {
					$retArr['error']['result']	=	$summary_list['result'];
				}
					$retArr['check'] = 0;
				$log_data['url'] = LOG_URL.'logs.php';

				$post_data['ID']         = $params['parentid'];
				$post_data['PUBLISH']    = 'ME';
				$post_data['ROUTE']      = 'sendomnidemo';
				$post_data['CRITICAL_FLAG']  = 1 ;
				$post_data['MESSAGE']        = 'sendomnidemo';
				$post_data['DATA']['user'] =    $usercode;
				$post_data['DATA']['version'] = $version;
				$post_data['DATA']['url']    = $curlParams2['url'];
				$post_data['DATA']['response']   = $summary_list;

				$log_data['method'] = 'post';
				$log_data['formate']    =   'basic';
				$log_data['postData']   =    http_build_query($post_data);
				$log_res    =   Utility::curlCall($log_data);
			}
		}else{
						//not categories present
				$dbiro   =   new DB($this->db['db_iro']);
		
		//api key		
		$api_key = hash('sha256', (date('Y-m-d') . $docid . 'websitestatus'));		
		//get categories
		$dbObjTme   =   new DB($this->db['db_iro']);
		//~ $categories =   json_decode($this->ShowCatBform($params['parentid']),true);
		$categoriesParams['url'] = JDBOX_API.'/services/contract_category_info.php?parentid='.$parentid.'&data_city='.$data_city.'&module=TME&post_data=1';
		$categoriesParams['formate'] = 'basic';
		$categoriesParams['method'] = 'post';
		$categories	=	json_decode(Utility::curlCall($categoriesParams),1);
	if($categories["error"]["code"] == 0){
		$livepaid = array_keys($categories["data"]["LIVE"]["PAID"]);
		$livenonpaid = array_keys($categories["data"]["LIVE"]["NONPAID"]);
		$temppaid = array_keys($categories["data"]["TEMP"]["PAID"]);
		$tempnonpaid = array_keys($categories["data"]["TEMP"]["NONPAID"]);
		$livearr = array_merge( (array)$livepaid, (array)$livenonpaid);
		$temparr = array_merge( (array)$temppaid, (array)$tempnonpaid);
		$finalarr = array_merge( (array)$livearr, (array)$temparr);

			$implode_arr = implode(',',$finalarr);
			$sqlcatinfo="SELECT catid,category_name,national_catid,template_id FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$implode_arr.");";
			$con    =   $dbObjTme->query($sqlcatinfo);
			$dbObjTme->numRows($con);
 		if($con && $dbObjTme->numRows($con)>0)
 		{	
			while($sqlcatinforow= $dbObjTme->fetchData($con)){
				$catdata[intval($sqlcatinforow['national_catid'])]['cnm']=$sqlcatinforow['category_name'];
				$catdata[intval($sqlcatinforow['national_catid'])]['cid']=$sqlcatinforow['catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['nid']=$sqlcatinforow['national_catid'];
				$catdata[intval($sqlcatinforow['national_catid'])]['vid']=$sqlcatinforow['template_id'];
			}
			$postdata=json_encode($catdata);
		}
		
	}
		
		$curlParamstemp = array();
		$postdataarr = array();
		$curlParamstemp['url'] = JDOMNIDEMO."?action=templateThemeInfo&docid=".$docid;//template api to be called before sendomnilink
		$curlParamstemp['formate'] = 'basic';
		$postdataarr['docid']=$docid;
		$postdataarr['data']=($postdata);
		$curlParamstemp['method'] = 'post';
		$curlParamstemp['formate']    =   'basic';
		$curlParamstemp['postData']   =    $postdataarr;
		$tmplateAPIres   =   json_decode(Utility::curlCall($curlParamstemp),1);
		$curlParams2 = array();
	 	$curlParams2['url'] = JDOMNIDEMO."?action=sendOmniLink&mobilenos=".$mobile."&emailids=".$emailid."&firstname=".urlencode($contactperson)."&employee_code=".$params['usercode'];;//API got changed
        $curlParams2['formate'] = 'basic';
        $postdataarr['docid']=$docid;
		$postdataarr['firstname']= $contactperson;
		$postdataarr['employee_code']=$params['usercode'];
		$postdataarr['data']=($postdata);
		$curlParams2['postData']   =    $postdataarr;
		$curlParams2['method'] = 'post';
        $summary_list   =   json_decode(Utility::curlCall($curlParams2),1);
				$retArr = array();
				$retArr['error'] = array();
				if($summary_list['isError'] == false) {
					$retArr['error']['code']	=	0;
					$retArr['error']['msg']	=	"Link sent successfully";
				} else {
					$retArr['error']['code']	=	1;
					$retArr['error']['msg']	=	"Error in the link";
				}
				if(isset($summary_list['result'])) {
					$retArr['error']['result']	=	$summary_list['result'];
				}
					$retArr['check'] = 0;
				$log_data['url'] = LOG_URL.'logs.php';

				$post_data['ID']         = $params['parentid'];
				$post_data['PUBLISH']    = 'ME';
				$post_data['ROUTE']      = 'sendomnidemo';
				$post_data['CRITICAL_FLAG']  = 1 ;
				$post_data['MESSAGE']        = 'sendomnidemo';
				$post_data['DATA']['user'] =    $usercode;
				$post_data['DATA']['version'] = $version;
				$post_data['DATA']['url']    = $curlParams2['url'];
				$post_data['DATA']['response']   = $summary_list;

				$log_data['method'] = 'post';
				$log_data['formate']    =   'basic';
				$log_data['postData']   =    http_build_query($post_data);
				$log_res    =   Utility::curlCall($log_data);
			
			
			}
        return   json_encode($retArr);
    }


	public function checkCategoryType (){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&data_city=".$data_city."&action=11&module=tme&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function insertDemoLinkDetails (){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2 			= array();
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&data_city=".$data_city."&action=12&module=tme&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function fetchDemoLinkDetails (){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&data_city=".$data_city."&action=13&module=tme&version=".$version."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function fetchdemocategories (){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?data_city=".$data_city."&module=tme&action=17&usercode=".$usercode."&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}

	public function transferaccdetailstomain() { // Done
		header('Content-Type: application/json');
		$params						= json_decode(file_get_contents('php://input'),true);		
		$paramsGET 					= array ();
		$res_arr 					= array();
		$parentid  					= $params['parentid'];
		$data_city	 				= urlencode($params['data_city']);
		$version  					= $params['version'];
		$usercode  					= $params['usercode'];
		$datacity       			= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=4&data_city=".$data_city."&module=tme&version=".$version."&parentid=".$parentid."&usercode=".$usercode;
		$summary_list2				=	Utility::curlCall($curlParams2);
		if($summary_list2['error']['code'] == 0){
			 $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/ecs_mandate_form.php?action=6&data_city=".$data_city."&module=tme&version=".$version."&parentid=".$parentid."&usercode=".$usercode;
			$summary_list2			=	Utility::curlCall($curlParams2);
			return json_encode($summary_list2);
		}else {
			return $summary_list2;
		}
	}

	public function sendjdpaylink() { // Done
		header('Content-Type: application/json');
		$params						= json_decode(file_get_contents('php://input'),true);
		$parentid  					= $params['parentid'];
		$data_city 					= urlencode($params['data_city']);
		$datacity       			= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/jdpay_service.php?data_city=".$data_city."&module=tme&parentid=".$parentid;
		$link_result				= Utility::curlCall($curlParams2);
		return json_encode($link_result);
	}
	
	public function check_one_plus_block(){ // Done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['noparentid']			= 	1;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"checkoneplusblock";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function checkemployeeeligible (){ // Done
		header('Content-Type: application/json');
		$params						= json_decode(file_get_contents('php://input'),true);
		$parentid  					= $params['parentid'];
		$data_city 					= urlencode($params['data_city']);
		$version  					= $params['version'];
		$usercode  					= $params['usercode'];
		$curlParams2 				= array();
		$datacity       			= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=6&usercode=".$usercode."&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] 	= 'basic';
		$summary_list				=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function fetchpaymentype() { // Done
		$resArr							=	array();
		$resArr['data']['error_code'] 	= '1';
		$resArr['data']['error_msg'] 	= 'Not in use';
		return json_encode($resArr);	
	}
	
	public function setecs() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".trim($params['parentid'])."&data_city=".trim(urlencode($params['data_city']))."&action=20&module=tme&version=".$params['version'];
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function sendratinglink() { // Done		
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$mob_arr 							= 	explode(',',$params['mobile']);
		$email_arr 							= 	explode(',',$params['email']);
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid']				= 	$params['parentid'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['compname'] 				= 	$params['compname'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['mob_arr'] 				= 	$mob_arr;
		$postArray['email_arr'] 			= 	$email_arr;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"sendratinglink";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		foreach($mob_arr as $key => $val) {
			if($val !=''){
				$log_data['url'] 			= LOG_URL.'logs.php';
				$post_data['ID']        	= $params['parentid'];                
				$post_data['PUBLISH']   	= 'TME';         	
				$post_data['ROUTE']     	= 'RATING_SMS_AND_EMAIL';  
				$data['USER_ID']			= 	$params['empcode']; 		
				$post_data['CRITICAL_FLAG'] = 0 ;			
				$post_data['MESSAGE']       = 'SMS SENT';	
				$post_data['DATA']['OTHER'] = $val;
				$log_data['method'] 		= 'post';
				$log_data['formate'] 		= 	'basic';
				$log_data['postData'] 		= 	 http_build_query($post_data);
				$log_res					=	Utility::curlCall($log_data);
			}
		}		
		foreach($email_arr as $key => $val) {
			if($val !=''){
				$log_data['url'] 			= LOG_URL.'logs.php';
	    		$post_data['ID']         	= $params['parentid'];                
				$post_data['PUBLISH']    	= 'TME';         	
				$post_data['ROUTE']      	= 'RATING_SMS_AND_EMAIL';  
				$data['USER_ID']			= $params['empcode']; 		
				$post_data['CRITICAL_FLAG'] = 0 ;			
				$post_data['MESSAGE']       = 'EMAIL SENT';	
				$post_data['DATA']['OTHER'] = $val;
				$log_data['method'] 		= 'post';
				$log_data['formate'] 		= 'basic';
				$log_data['postData'] 		= http_build_query($post_data);
				$log_res					= Utility::curlCall($log_data);
			}
		}
		return json_encode($retArr);
	}
	
	public function docid($params){
			$resultArr                      	= 	array();
			$curlParams                     	= 	array();
			$datacity            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
			$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/fetchAllDetails.php';
			$postArray['parentid'] 				= 	$params['parentid'];
			$postArray['ucode'] 				= 	$params['empcode'];
			$postArray['uname'] 				= 	$params['empname'];
			$postArray['data_city'] 			= 	$params['data_city'];
			$postArray['post_data']				= 	"1";		 
			$postArray['module']				= 	"TME";		 
			$postArray['action']				= 	"idgenerator";
			$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
			$resultArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
			return json_encode($resultArr);
	}
	
	public function checklive() { // Done
		header('Content-Type: application/json');
		$params					= 	json_decode(file_get_contents('php://input'),true);
		$parentid 				= 	$params['parentid'];
		$data_city 				= 	trim($params['data_city']);
		$docidRet				=	json_decode($this->docid($params),1);
		if(count($docidRet) > 0){
			$docid						=	$docidRet['docid'];
			$rec_url					=	WEB_SERVICES."CompanyDetails.php?docid=".$docid;
			$curlParams2 				= array();
			$curlParams2['url'] 		= $rec_url;
			$curlParams2['formate'] 	= 'basic';
			$curlParams2['method'] 		= 'post';
			$curlParams2['headerJson'] 	= 'json';
			$curlParams2['postData'] 	= $docid;
			$curlParams2['publish'] 	= "tme";
			$liveresp					= json_decode(Utility::curlCall($curlParams2),1);
			$error 						= $liveresp['error'];
			if($error){
				$res['errorCode'] 		= 1;
				$res['errorMsg'] 		= "data not found";
			}else{
				$res['errorCode'] 		= 0;
				$res['errorMsg'] 		= "data found";
				$res['value'] 			= $liveresp[$docid];
			}
		}else{
			$res['errorCode'] = 1;
			$res['errorMsg'] = "docid not found";
		}
		return json_encode($res);
	}
	
	public function chkRatingCat(){ // Done
		$retArr                      	= 	array();
		$curlParams                     	= 	array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$datacity            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($datacity)].'services/fetchAllDetails.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['ucode'] 				= 	$params['empcode'];
		$postArray['uname'] 				= 	$params['empname'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"chkRatingCat";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function  gettemplateurl(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&action=20&version=".$version."&data_city=".$data_city."&module=tme&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function  storeomnitemplateinfo(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$template_id 			= $params['template_id'];
		$template_name 			= $params['template_name'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?parentid=".$parentid."&action=21&version=".$version."&data_city=".$data_city."&module=tme&usercode=".$usercode."&app_template_id=".$template_id."&app_template_name=".urlencode($template_name);
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function addomnitemplatetemp() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=7&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function deleteomnitemplatetemp() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity       		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=8&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			= Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function addomnitemplatelive() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=9&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function deleteomnitemplatelive() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=10&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function deleteEmailLive() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/domain_service.php?data_city=".$data_city."&module=tme&action=9&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	public function deleteSmsLive() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=13&parentid=".$parentid."&version=".$version;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list; 
	}

	public function checkpackagedepend() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$type  					= $params['type'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?data_city=".$data_city."&module=tme&action=11&parentid=".$parentid."&version=".$version."&type=".$type;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}

	public function checkaccess() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$module  				= $params['module'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=26&module=".$module."&version=".$version."&usercode=".$usercode."&module_name=".$module;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function fetchpricechatprice (){ // Done
		$summary_list = array();
		$summary_list['error']['code'] 	= 0;
		$summary_list['error']['msg'] 	= 'sucess';
		return   $summary_list;
	}
	
	public function insert_discount(){ // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$campaignid  			= $params['campaignid'];
		$custom_value  			= $params['custom_value'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	=  $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=27&module=tme&version=".$version."&usercode=".$usercode."&campaignid=".$campaignid."&custom_value=".$custom_value;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function get_discount_info() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=28&module=tme&version=".$version ;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	} 
	
	public function deletecombolive() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&action=30&module=tme&version=".$version ;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	} 

	Public function saveemailids() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$emailid  				= rtrim($params['email'],',');
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/getOmniDetails.php?data_city=".$data_city."&module=tme&action=24&usercode=".$usercode."&parentid=".$parentid."&version=".$version."&email=".$emailid;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	
	public function emailpackageprice() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$email_type  			= $params['email_type'];
		$usercode  				= $params['usercode'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/domain_service.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=7&version=".$version."&usercode=".$usercode."&no_of_emails=0&email_type=".$email_type;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
		
	}
	
	public function emailpackagerequired() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$email_type  			= $params['email_type'];
		$usercode  				= $params['usercode'];
		$no_emailid  			= $params['no_emailid'];
		$admin_username  		= $params['admin_username'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/domain_service.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=6&version=".$version."&usercode=".$usercode."&no_of_emails=".$no_emailid."&email_type=".$email_type."&admin_username=".urlencode($admin_username); 
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function smspackagerequired() { // Done
		header('Content-Type: application/json');
		$params					= json_decode(file_get_contents('php://input'),true);
		$parentid  				= $params['parentid'];
		$data_city 				= urlencode($params['data_city']);
		$version  				= $params['version'];
		$usercode  				= $params['usercode'];
		$no_sms 	 			= $params['no_sms'];
		$curlParams2 			= array();
		$datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=12&version=".$version."&no_of_sms=".$no_sms."&usercode=".$usercode;
		$curlParams2['formate'] = 'basic';
		$summary_list			=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function SSLpackagerequired(){ // Done
		header('Content-Type: application/json');
        $params     			= json_decode(file_get_contents('php://input'),true);
        $parentid  				= $params['parentid'];
        $data_city 				= urlencode($params['data_city']);
        $version  				= $params['version'];
        $usercode  				= $params['usercode'];
        $ssl_payment_type   	= $params['ssl_payment_type'];
        $ssl_val  				= $params['ssl_val'];
        $curlParams2 			= array();
        $datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
        $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."/services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=20&version=".$version."&usercode=".$usercode."&ssl_payment_type=".$ssl_payment_type."&ssl_val=".$ssl_val;
        $curlParams2['formate'] = 'basic';
        $summary_list   		=   Utility::curlCall($curlParams2);
        return   $summary_list;
	}
	
	public function deletesslpack() { // Done
        header('Content-Type: application/json');
        $params     			= json_decode(file_get_contents('php://input'),true);
        $parentid  				= $params['parentid'];
        $data_city 				= urlencode($params['data_city']);
        $version  				= $params['version'];
        $usercode  				= $params['usercode'];
        $curlParams2 			= array();
        $datacity				= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
        $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=21&version=".$version."&usercode=".$usercode;
        $curlParams2['formate'] = 'basic';
        $summary_list   		=   Utility::curlCall($curlParams2);
        return   $summary_list;
    }

	public function deletesmspack() { // Done
		header('Content-Type: application/json');
		$params		= json_decode(file_get_contents('php://input'),true);
		
		$parentid  = $params['parentid'];
		$data_city = urlencode($params['data_city']);
		$version  = $params['version'];
		$usercode  = $params['usercode'];
		$no_sms  = $params['no_sms'];
		
		$curlParams2 = array();
		$curlParams2['url'] = JDBOX_API."/services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=13&version=".$version."&usercode=".$usercode;
		
		$curlParams2['formate'] = 'basic';
		$summary_list	=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
		
	public function smsprice() { // Done
		header('Content-Type: application/json');
		$params						=	 json_decode(file_get_contents('php://input'),true);
		$parentid  					= $params['parentid'];
		$data_city 					= urlencode($params['data_city']);
		$version  					= $params['version'];
		$curlParams2 				= array();
		$datacity					= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/updateOmniBudget.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=15&version=".$version;
		$curlParams2['formate'] 	= 'basic';
		$summary_list				=	Utility::curlCall($curlParams2);
		return   $summary_list;
	}
	
	public function newpricechatval() { // Done
		header('Content-Type: application/json');
		$params						= json_decode(file_get_contents('php://input'),true);
		$parentid  					= $params['parentid'];
		$version  					= $params['version'];
		$data_city 					= urlencode($params['data_city']);
		$curlParams2 				= array();
		$retArr 					= array();
		$datacity					= ((in_array(strtolower($data_city), $this->main_cities)) ? strtolower($data_city) : 'remote');
		$curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($datacity)]."services/finance_display.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=45&version=".$version."&usercode=".$params['usercode'];
		$curlParams2['formate'] 	= 'basic';
		$summary_list				= json_decode(Utility::curlCall($curlParams2),true);
		if(isset($summary_list['data'])){
			$retArr['data']			=	$summary_list['data'];
			$retArr['errorCode']	=	0;
			$retArr['present']		=	$summary_list['present'];
		}else{
			$retArr['errorCode']	=	1;
		}
		return json_encode($retArr);
	}
	
	public function getmaincampaignid(){ // Done
			$res 				= array();
			$res['errormsg'] 	= "Not in Use";
			$res['errorcode'] 	= "1";
			return json_encode($res);
	}
	
	public function insertAutoWrapupTime(){ // Not In Use
		header('Content-Type: application/json');
		$dbObjLocal	=	new DB($this->db['db_local']);
		$params	=	json_decode(file_get_contents('php://input'),true);
		$retArr	=	array();
		$insert	=	"insert into  tbl_auto_wrapup_log set parentid		=	'".trim($params['parentid'])."',
														  timerStart	=	'".trim($params['timerStart'])."',
														  timerEnd		=	'".trim($params['timerEnd'])."',	
														  updatedOn		=	now(),	
														  Disposition	=	'".trim($params['disposition'])."'";
		$result	=	$dbObjLocal->query($insert);
		if($result == 1){
			$retArr['errorCode']	=	0;
			$retArr['msg']	=	'Data Inserted';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['msg']	=	'Data Not Inserted';
		}
		return json_encode($result);
	}
	
	
	public function insertTimerStatus(){ // Not In Use
		$params		=	array_merge($_GET,$_POST); 	 
		$dbObjLocal	=	new DB($this->db['db_tme']);
		$retArr	=	array();
		$insert	=	"INSERT INTO tbl_timer_status SET empcode		=	'".trim($params['empcode'])."',
														  isConnected	=	'".trim($params['isConnected'])."'
								ON DUPLICATE KEY UPDATE isConnected	=	'".trim($params['isConnected'])."'";
		$result	=	$dbObjLocal->query($insert);
		if($result == 1){
			$retArr['errorCode']	=	0;
			$retArr['msg']	=	'Data Inserted';
		}else{
			$retArr['errorCode']	=	1;
			$retArr['msg']	=	'Data Not Inserted';
		}
		return json_encode($retArr);
	}
	
	public function getTimerStatus(){ // Done
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		if(count($params) <=0){
			$params	=	array_merge($_GET,$_POST);
		}
		
		$data_city 	= trim($params['data_city']);
		 
		$dbObjLocal	=	new DB($this->db['db_tme']);
		//~ $params	=	json_decode(file_get_contents('php://input'),true);
		$retArr	=	array();
		$insert	=	"SELECT isConnected FROM  tbl_timer_status where empcode = '".$params['empcode']."'";
		$result	=	$dbObjLocal->query($insert);
		$data	=	$dbObjLocal->fetchData($result);
		$count	=	$dbObjLocal->numRows($result);
		if($count != 0){
			$retArr['errorCode']	=	0;
			$retArr['data']			=	$data;
		}else{
			$retArr['errorCode']	=	1;
			$retArr['msg']	=	'No Data';
		}
		return json_encode($retArr);
	}

	
	public function getAppointLogInfo(){
		$retArr             =       array();
		header('Content-Type: application/json');
		$params =       json_decode(file_get_contents('php://input'),true);
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['appointDatefrom'] 		= 	$params['appointDatefrom'];
		$postArray['appointDateto'] 		= 	$params['appointDateto'];
		$postArray['actionFor'] 			= 	$params['actionFor'];
		$postArray['actionto'] 				= 	$params['actionto'];
		$postArray['pageShow'] 				= 	$params['pageShow'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['followup'] 				= 	$params['followup'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['srchParam'] 			= 	$params['srchParam'];
		$postArray['noparentid'] 			= 	1;
		$postArray['empcode'] 				= 	'admin';
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getAppointLogInfo";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function getalldata() { // Done
		$params							= json_decode(file_get_contents('php://input'),true);
		$userid							= $params['userid'];
		$pagevalue						= $params['pagevalue'];
		$limit							= $params['limit'];
		$curlParams2 					= array();
		$curlParams2['url'] 			= KNOWLEDGE_API."?emptype=TME&pagevalue=$pagevalue&limit=$limit";
		$curlParams2['formate'] 		= 'basic';
		$tmeInfo						= Utility::curlCall($curlParams2);
		$retarr['data']['errorcode']	= 1;
		$retarr['data']['errormessage']	='errorno data';
		$retarr['total']=0;
		return json_encode($retarr);
	}

	public function insertAutoWrapUP(){ // done
		$resArr 				= 	array();
		$resArr['errorCode']	=	1;	
		$resArr['errorStatus']	=	'NOT IN USE';	
		return json_encode($resArr);
	}
	
	public function getAutoWrapupInfo(){ // done 
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['dateFrom'] 				= 	$params['dateFrom'];
		$postArray['dateTo'] 				= 	$params['dateTo'];
		$postArray['pageShow'] 				= 	$params['pageShow'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['noparentid'] 			= 	1;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getAutoWrapupInfo";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function getAutoWrapupInfoDetail(){ // done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['dateFrom'] 				= 	$params['dateFrom'];
		$postArray['dateTo'] 				= 	$params['dateTo'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['noparentid'] 			= 	1;
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['empname'] 				= 	$params['empname'];
		$postArray['data_city'] 			= 	$data_city;
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getAutoWrapupInfoDetail";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
		
	public function update_generalinfo_shadow(){ // Done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['salute'] 				= 	$params['salute'];
		$postArray['contact_person'] 		= 	$params['contact_person'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"updategeneralinfoshadow";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	 public function getStateListings(){ // Done
        $params     				= json_decode(file_get_contents('php://input'),true);
        $retArr     				= array();
        $parentid  					= $params['parentid'];
        $data_city 					= urlencode($params['data_city']);
        $curlParams2 				= array();
        $data_city            		= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 		= $this->genioconfig['jdbox_url'][strtolower($data_city)]."services/bformmulticity.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=getStateListings2&usercode=".$params['usercode']."";
        $curlParams2['formate'] 	= 'basic';
        $summary_list   			= Utility::curlCall($curlParams2);
        return   $summary_list;
    }
	
	
	public function checkmulticity(){ // Done
        $params     			=   json_decode(file_get_contents('php://input'),true);
        $retArr     			=   array();
        $parentid  				= $params['parentid'];
        $data_city 				= urlencode($params['data_city']);
        $catidlineage_nonpaid 	= $params['catidlineage_nonpaid'];
        $curlParams2 			= array();
        $data_city            	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($data_city)]."services/bformmulticity.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=checkMultiCity&catidlineage=".$catidlineage_nonpaid."&usercode=".$params['usercode']."";
        $curlParams2['formate'] = 'basic';
        $summary_list   		=  Utility::curlCall($curlParams2);
        return   $summary_list;
    }
    
    public function saveNationallistingData(){ // Done
        $params     			=   json_decode(file_get_contents('php://input'),true);
        $retArr     			=   array();
        $parentid  				= $params['parentid'];
        $data_city 				= urlencode($params['data_city']);
        $citystr    			=   "";
        foreach($params['citystr'] as $key=>$value) {
                $citystr .= ucwords($value).",";
        }
        $citystr    				=   substr($citystr,0,-1);
        $latitude 					= 	$params['latitude'];
        $longitude 					= 	$params['longitude'];
        $type 						= 	$params['type'];
        $curlParams2 				= 	array();
        $paramsGET  				=   array();
        $paramsGET['parentid']  	=   $parentid;
        $paramsGET['data_city'] 	=   $data_city;
        $paramsGET['module']    	=   "tme";
        $paramsGET['action']    	=   "insertNationalListingval";
        $paramsGET['citystr']   	=   $citystr;
        $paramsGET['latitude']  	=   $latitude;
        $paramsGET['longitude'] 	=   $longitude;
        $paramsGET['type']  		=   'state';
        $paramsGET['usercode'] 		=   $params['usercode'];
        $curlParams2['postData']    =   $paramsGET;
        $curlParams2['method']  	=   "POST";
        $data_city            		=	 ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 		= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/bformmulticity.php";
        $curlParams2['formate'] 	= 	'basic';
        $summary_list   			=   Utility::curlCall($curlParams2);
        if($summary_list['errorCode'] == 0){
            $_SESSION['multicity_contract']     = 'multicity_contract';
            $_SESSION['multicity']              = 'multicity';
        }
        return   $summary_list;
    }

    public function insertLocalListingval(){ // Done
        $params     			=   json_decode(file_get_contents('php://input'),true);
        $retArr     			=   array();
        $parentid  				= 	$params['parentid'];
        $data_city 				= 	urlencode($params['data_city']);
        $sphinxid 				= 	$params['sphinxid'];
        $curlParams2 			= 	array();
        $data_city            	=	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/bformmulticity.php?parentid=".$parentid."&data_city=".$data_city."&module=tme&action=insertLocalListingval&sphinxid=".$sphinxid."&usercode=".$params['usercode']."";
        $curlParams2['formate'] = 'basic';
        $summary_list   		=   Utility::curlCall($curlParams2);
        if($summary_list['errorCode'] == 0){
                unset($_SESSION['multicity_contract']);
                unset($_SESSION['multicity']);
                unset($_REQUEST['multicity_cities']);
        }
        return   $summary_list;
    }
    
    public function bformvalidation(){ // Done
        $params     			=   json_decode(file_get_contents('php://input'),true);
        $retArr     			=   array();
        $parentid   			= 	$params['parentid'];
        $data_city  			= 	urlencode($params['data_city']);
        $ucode      			= 	$params['ucode'];
		$main_city_arr 			= 	array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata'); 
        if(!in_array(strtolower($params['data_city']),$main_city_arr)){
            $remotecityidentifier        =   1;
        }else {
            $remotecityidentifier        =   0;
        }

        $postArray = array();
        //$curlParams2['url'] = DECS_CITY."/api/bformValidationApi.php?parentid=".$parentid."&s_deptCity=".$data_city."&module=tme&action=bformvalidation&ucode=".$ucode."&remotecityidentifier=".$remotecityidentifier;
		$url = JDBOX_API."/services/bform_validation_api.php";
		$postArray['parentid']	=$parentid;
		$postArray['data_city']	=$data_city;
		$postArray['module']	='tme';
		$postArray['ucode']		='ucode';
		
		$dataParam              = 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');		
		$errorArr 		= array();
		$errorArr       = 	json_decode(Utility::curlCall($dataParam),1);		
        		
		if($errorArr['error']['code']==1){		
			return $this->getHtmlForBform($errorArr['error']['message']);
		}		
        //~ $curlParams2['formate'] = 'basic';
        //~ $summary_list   		=   Utility::curlCall($curlParams2);        
        //return   $summary_list;
    }
	private function getHtmlForBform($errorMsgArr){		
		if(is_array($errorMsgArr) && count($errorMsgArr)>0){
			foreach($errorMsgArr as $index=>$message){
				$final_msg.= $message."<br>";
			}			
		}
		else{
			$final_msg.= $errorMsgArr."<br>";
		}
		$commonTitle = 'Because of some system issue, data is lost so please re-enter following information';
		return $this->getHtml($final_msg,$commonTitle);
	}
	private function getHtml($commonShowContent,$commonTitle){
		$html= '';
		$html .= '<div style="display: inline-block;width:100%;vertical-align:middle;overflow:hidden;margin: 0 auto;position: relative;margin-top:2%;">';
		$html .=	'<div style="position:relative;top:50%">';
		$html .=		'<div style="width:570px;display:inline-block; background:#FFF; left:35%; border-radius:3px; ">';
									
		$html .=	'<a  href="javascript:void(0);"id="common_pop_close" style="background-position:0 0; width: 10px; height: 10px; position: absolute; right: 11px; top: 14px; opacity:0.5;background-image:url(images/close.gif);cursor:pointer;" ></a>';				
		$html .=	'<span style="width:100%; float:left; padding:5px 15px; border-bottom:1px solid #ccc; box-sizing:border-box; font-size:25px;color:red;text-align:left;">'.$commonTitle.'</span>';
		$html .= '<div style=" width:100%; float:left; padding:15px;border-bottom:1px solid #ccc; box-sizing:border-box; box-sizing:border-box;  font-size:20px;"><span style="display:block;color:#424242;float:left;">'.$commonShowContent.'</span></div>';				
		$html .=	'<div style="width:100%; float:left; padding:10px 15px; box-sizing:border-box;">';
		$html .=	'</div>';
		$html .=	'</div>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	
	public function savedetails(){ // Done
		$params					=	json_decode(file_get_contents('php://input'),true);	
		$retArr					=	array();
		$curlParams2 			= 	array();
		$data_city            	=	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/finance_display.php?data_city=".urlencode($params['data_city'])."&module=tme&action=33&usercode=".$params['ucode']."&parentid=".$params['parentid']."&version=".$params['version']."&invoice_email=".urlencode($params['email'])."&invoice_mobile=".$params['mobile']."&email_show=&mobile_show=&invoice_contact=".urlencode($params['contact_person'])."&skip_reg=1&email_disp=".$params['email_disp']."&email_feed=".$params['email_feed']."&mob_disp=".$params['mob_disp']."&mob_feed=".$params['mob_feed'];
		$curlParams2['formate'] = 'basic';
		$discount_res			=	Utility::curlCall($curlParams2);
		return   $discount_res;
	}

 	public function omnicatlog(){ // Done
		$data 				= array();
		$data['error_code'] = 1;
		$data['error_msg'] 	="Not in use";
		return json_encode($data);
	}


	public function fetchCorIncorAccuracy(){ // Done
		$retArr             =       array();
		header('Content-Type: application/json');
		$params 							=   json_decode(file_get_contents('php://input'),true);
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['noparentid'] 			= 	1;
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	'admin';
		$postArray['dateFrom'] 				= 	$params['dateFrom'];
		$postArray['dateTo'] 				= 	$params['dateTo'];
		$postArray['pageShow'] 				= 	$params['pageShow'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"fetchCorIncorAccuracy";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	
	public function fetchCorIncorAccuracyDetail(){ // Pending - Its A Report City Option Needed
		$retArr             =       array();
		header('Content-Type: application/json');
		$params 							=   json_decode(file_get_contents('php://input'),true);
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	'admin';
		$postArray['dateFrom'] 				= 	$params['dateFrom'];
		$postArray['dateTo'] 				= 	$params['dateTo'];
		$postArray['pageShow'] 				= 	$params['pageShow'];
		$postArray['srchData'] 				= 	$params['srchData'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"fetchCorIncorAccuracyDetail";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	 //~ public function checkDiscount(){ // Done
        //~ $params					=	json_decode(file_get_contents('php://input'),true);	
        //~ $retArr					=	array();
        //~ $parentid  				= 	$params['parentid'];
        //~ $data_city 				= 	urlencode($params['data_city']);
        //~ $version 				= 	$params['version'];
        //~ $curlParams2 			= 	array();
        //~ $data_city            	=	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        //~ $curlParams2['url'] 	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/getBudgetService.php?p_id=".$parentid."&vrsn=".$version."&module=TME&act=status&data_city=".urlencode($data_city);
        //~ $curlParams2['formate'] = 	'basic';
        //~ $discount_res			=	Utility::curlCall($curlParams2);
        //~ return   $discount_res;
	//~ }
	
	
	 public function checkDiscount(){ // Done
        $params					=	json_decode(file_get_contents('php://input'),true);	
        $retArr					=	array();
        $parentid  				= 	$params['parentid'];
        $data_city 				= 	urlencode($params['data_city']);
        $version 				= 	$params['version'];
        $curlParams2 			= 	array();
        $data_city            	=	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/getBudgetService.php?p_id=".$parentid."&vrsn=".$version."&module=TME&act=chk&data_city=".urlencode($data_city);
        $curlParams2['formate'] = 	'basic';
        $discount_res			=	Utility::curlCall($curlParams2);
        return   $discount_res;
	}
	
	public function freeWebsiteStatus(){ // Done
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"freeWebsiteStatus";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}

	public function fetchDcInfo($parentid){ // Not In Use
		$resarr 				= array();
		$resarr['error_code'] 	= 1;
		$resarr['error_msg'] 	= "Not In Use";
		return json_encode($resarr);
	}
	
	public function getCurrentPaidStatus($parentid)
	{		
		$curlPaidParams = array();
		$paidParamsArr	 =	array();
		$paidParamsArr['parentid']	    =	$parentid;
		$paidParamsArr['data_city']	    =	DATA_CITY;
		$paidParamsArr['rquest']		=	'get_contract_type';
		
		$curlPaidParams['formate'] 	 	=	'basic';
		$curlPaidParams['method']       =	'post';
		$curlPaidParams['headerJson']   =	'json';
		$curlPaidParams['url']			=	JDBOX_API."/services/contract_type.php";
		$curlPaidParams['postData'] 	=	json_encode($paidParamsArr);
		$paidStatusData                	=	json_decode(Utility::curlCall($curlPaidParams),true);
		
		$current_paid_status = $paidStatusData;
		return $current_paid_status;
	}
	
	public function GetContractData() { // done
		$params     			=   $_REQUEST;
        $parentid  				= $params['parentid'];
        $data_city 				= urlencode($params['data_city']);
        $ucode 	   				= urlencode($params['ucode']);
        $uname	   				= urlencode($params['uname']);
        $post_data 				= urlencode($params['post_data']);
        $curlParams2 			= array();
        $paramsGET  			=   array();
        $paramsGET['parentid']  =   $parentid;
        $paramsGET['data_city'] =   $data_city;
        $paramsGET['ucode']  	=  	$ucode;
        $paramsGET['uname'] 	=   $uname;
        $paramsGET['post_data'] =   $post_data;
        $paramsGET['module']    =   "tme";
        $curlParams2['postData']=   $paramsGET;
        $curlParams2['method']  =   "POST";
        $data_city            	= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
        $curlParams2['url'] 	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."/services/fetchLiveData.php";
        $curlParams2['formate'] = 	'basic';
        $response   			=   Utility::curlCall($curlParams2);
        return $response;
    }
    
    public function set_pack_emi(){
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['companyname'] 			= 	$params['companyname'];
		$postArray['version'] 				= 	$params['version'];
		$postArray['selected_emi'] 			= 	$params['selected_emi'];
		$postArray['budget_multiplier'] 	= 	$params['budget_multiplier'];
		$postArray['campaign'] 				= 	$params['campaign'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"setpackemi";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function get_pack_emi(){
		$retArr             =       array();
		if(isset($_REQUEST['urlFlag'])){
			  $params =       array_merge($_POST,$_GET);
		}else{
			  header('Content-Type: application/json');
			  $params =       json_decode(file_get_contents('php://input'),true);
		}
		$resultArr                      	= 	array();
		$curlParams                     	= 	array();
		$data_city            				= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$url                            	= 	$this->genioconfig['jdbox_url'][strtolower($data_city)].'services/contractServices.php';
		$postArray['parentid'] 				= 	$params['parentid'];
		$postArray['data_city'] 			= 	$params['data_city'];
		$postArray['version'] 				= 	$params['version'];
		$postArray['empcode'] 				= 	$params['empcode'];
		$postArray['post_data']				= 	"1";		 
		$postArray['module']				= 	"TME";		 
		$postArray['action']				= 	"getpackemi";
		$dataParam                      	= 	array('url' => $url, 'method' => 'post','postData' => json_encode($postArray),'formate' => 'basic','headerJson' => 'json');
		$retArr                         	= 	json_decode(Utility::curlCall($dataParam),1);
		return json_encode($retArr);
	}
	
	public function check_existing_budget(){
		header('Content-Type: application/json');
		$params             	=   json_decode(file_get_contents('php://input'),true);
		$resarr 				= 	array();
		$curlParams 			= 	array();
		$data_city            	= 	((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams['url'] 		= 	$this->genioconfig['jdbox_url'][strtolower($data_city)]."services/finance_display.php?parentid=".$params['parentid']."&action=47&module=tme&data_city=".$params['data_city']."&usercode=".$params['empcode']."&payment_type=".$params['payment_type'];
		if($_SERVER['SERVER_ADDR'] == '172.29.87.53') {
			echo 'params data city'. $params['data_city'];
			echo 'api call--'.$this->genioconfig['jdbox_url'][strtolower($data_city)] . "services/finance_display.php?parentid=" . $params['parentid'] . "&action=47&module=tme&data_city=" . $params['data_city'] . "&usercode=" . $params['empcode'] . "&payment_type=" . $params['payment_type'];
			
			$retData =  file_get_contents($this->genioconfig['jdbox_url'][strtolower($data_city)] . "services/finance_display.php?parentid=" . $params['parentid'] . "&action=47&module=tme&data_city=" . $params['data_city'] . "&usercode=" . $params['empcode'] . "&payment_type=" . $params['payment_type']);
			echo 'response--' . $retData;
		}
		
		$curlParams['formate'] 	= 	'basic';
		$res   					=   Utility::curlCall($curlParams);
		return $res;
	}
	
	public function view_tv_ad(){
		header('Content-Type: application/json');
        $params     			= json_decode(file_get_contents('php://input'),true);
        $curlParams2 			= array();
        $data_city            	= ((in_array(strtolower($params['data_city']), $this->main_cities)) ? strtolower($params['data_city']) : 'remote');
		$curlParams2['url'] 	= $this->genioconfig['jdbox_url'][strtolower($data_city)]."services/newTvcs_service.php?data_city=".urlencode($params['data_city'])."&module=".$params['module']."&action=".$params['action']."&parentid=".$params['parentid']."&ucode=".$params['ucode']."&uname=".$params['uname']."";
        $response 				= Utility::curlCall($curlParams2);
		return json_encode($response);
	}

	public function udateusereditdata(){
		header('Content-Type: application/json');
        $params     			= json_decode(file_get_contents('php://input'),true);

        $dbObjLocal	=	new DB($this->db['db_local']);


        $insertDatagen = array();

        $parentid = $params['parid'];

        $query = "select * from d_jds.tbl_correct_incorrect where parentid='".$parentid."'";

        $result	=	$dbObjLocal->query($query);
        $data	=	$dbObjLocal->fetchData($result);
		$count	=	$dbObjLocal->numRows($result);
		if($count>0){
			 $query = "select * from d_jds.tbl_companydetails_edit where parentid='".$parentid."' ORDER BY entry_date DESC LIMIT 1";

	        $result	=	$dbObjLocal->query($query);
	        $data	=	$dbObjLocal->fetchData($result);
			$count	=	$dbObjLocal->numRows($result);
			$data['edited_data'] = json_decode($data['edited_data'],1);
			$data['notvalidatedarr'] = json_decode($data['notvalidatedarr'],1);

			// ["landline","contact_person","mobile","fax","area","landmark","pincode","contact_person","payment"]

			foreach($data['notvalidatedarr'] as $key=>$value){
				if($value=="landline"){
					$insertDatagen['landline'] = $data['edited_data']['Call_1'];
					$insertDatagen['landline_display'] = $data['edited_data']['Call_1'];
					$insertDataextra['landline_addinfo'] = $data['edited_data']['Call_1'];
				}
				if($value=="contact_person"){
					$insertDatagen['contact_person'] = $data['edited_data']['Contact_Person'];
					$insertDatagen['contact_person_display'] = $data['edited_data']['Contact_Person'];
				}
				if($value=="mobile"){
					$insertDatagen['mobile'] = $data['edited_data']['Mobile'];
					$insertDataextra['mobile_addinfo'] = $data['edited_data']['Mobile'];
					$insertDatagen['mobile_display'] = $data['edited_data']['Mobile'];
				}
				if($value=="fax"){
					$insertDatagen['mobile'] = $data['edited_data']['Fax'];
				}
				if($value=="area"){
					$insertDatagen['area'] = $data['edited_data']['Area'];
				}
				if($value=="landmark"){
					$insertDatagen['landmark'] = $data['edited_data']['Landmark'];
				}
				if($value=="pincode"){
					$insertDatagen['pincode'] = $data['edited_data']['PinCode'];
				}
				if($value=="payment"){
					$insertDataextra['payment_type'] = $data['edited_data']['mode_of_payment'];
				}
				if($value=="Toll_Free"){
					$insertDataextra['tollfree_addinfo'] = $data['edited_data']['tollfree'];
					$insertDatagen['tollfree'] = $data['edited_data']['tollfree'];
					$insertDatagen['tollfree'] = $data['edited_data']['tollfree_display'];
				}
				if($value=="working_time"){
					$insertDataextra['working_time_start'] = $data['edited_data']['working_time_start'];
					$insertDataextra['working_time_end'] = $data['edited_data']['working_time_end'];
				}
				if($value=="street"){
					$insertDatagen['street'] = $data['edited_data']['Street'];
				}
				if($value=="emailid"){
					$insertDatagen['email'] = $data['edited_data']['Email_ID'];
				}
				if($value=="year"){
					$insertDataextra['year_establishment'] = $data['edited_data']['year_establishment'];
				}
				if($value=="website"){
					$insertDatagen['website'] = $data['edited_data']['Website_Address'];
				}
			}




			$mongo_data = array();

			$insertDatagen['table'] = "tbl_companymaster_generalinfo_shadow";

			$geninfo_tbl 							= "tbl_companymaster_generalinfo_shadow";

			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table_data'] = $insertDatagen;
			$mongo_data[$geninfo_tbl]['updatedata'] = $insertDatagen;
			$mongo_inputs['table_data'] 			= $mongo_data;
			$data_res = $this->mongo_obj->updateData($mongo_inputs);
			$numRows = count($data_res);



			$mongo_data = array();

			$insertDataextra['table'] = "tbl_companymaster_extradetails_shadow";

			$geninfo_tbl 							= "tbl_companymaster_extradetails_shadow";

			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table_data'] = $insertDatagen;
			$mongo_data[$geninfo_tbl]['updatedata'] = $insertDataextra;
			$mongo_inputs['table_data'] 			= $mongo_data;

			$data_res = $this->mongo_obj->updateData($mongo_inputs);
			$numRows = count($data_res);



			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $parentid;
			$mongo_inputs['data_city'] 	= SERVER_CITY;
			$mongo_inputs['module']		= 'tme';
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			// $mongo_inputs['fields'] 	= "area";
			$data_res = $this->mongo_obj->getData($mongo_inputs);
			$numRows = count($data_res);

		}

		$retArr = array();
		$retArr['ErrorCode'] = 0;
		$retArr['Msg'] = 'Success';

		return json_encode($retArr);

	}
}

