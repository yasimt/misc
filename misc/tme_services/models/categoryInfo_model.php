<?php
class CategoryInfo_Model extends Model {
	function __construct() {
        parent::__construct();
        GLOBAL $parseConf;
    }
    
    public function getCategoryInfo() {
		header('Content-Type: application/json');
		
		$dbObjLocal	=	new DB($this->db['db_local']);
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsGET	=	array_merge($_POST,$_GET);
		
		$whereCond	=	'';
		if($params['catParam']	!=	'') {
			if($params['catFlag']	==	1) {
				$whereCond	=	" category_name = '".trim($params['catParam'])."'";
			} else if($params['catFlag']	==	0) {
				$whereCond	=	" catid = '".$params['catParam']."'";
			} else if($params['catFlag']	==	2) {
				$whereCond	=	" catid IN (".$params['catParam'].")";
			}
			
			$retArr	=	array();
			$query	=	"SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE ".$whereCond;
			$con	=	$dbObjLocal->query($query);
			$numRows	=	$dbObjLocal->numRows($con);
			if($numRows > 0) {			
				if($params['catFlag']	==	2) {
					while($res	=	$dbObjLocal->fetchData($con)) {
						$retArr['data'][]	=	$res;
					}
				} else {
					$res	=	$dbObjLocal->fetchData($con);
					$retArr['data']	=	$res;
				}
				
				$retArr['errorCode']	=	0;
				$retArr['errorStatus']	=	'Data Sent Successsfully';
			} else {
				$retArr['errorCode']	=	1;
				$retArr['errorStatus']	=	'Data Not Found';
			}
		} else {
			$retArr['errorCode']	=	2;
			$retArr['errorStatus']	=	'Parameters are blank';
		}
		return json_encode($retArr);
	}
	
	public function getCatAutoSuggest() {
		header('Content-Type: application/json');
		
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsGET	=	array_merge($_POST,$_GET);
		
		
		
		$searchedText	=	$params['searchText'];
		$params['national'] = '';
		$params['nationalType'] = '';
		//$curlParams['url'] 	= 	IRO_CITY."/Autosuggest/php/Main_Search_Company.php?search=".urlencode(rtrim($searchedText))."&Search_City=".urlencode($params['data_city'])."&stp=0&dtres=10&srch_type=1&module=".MODULE."&nflag=&stflag=&dflag=";
		
		$curlParams['url'] 	= 	IRO_CITY."/mvc/autosuggest/compcat?dcity=".urlencode($params['data_city'])."&scity=".urlencode($params['data_city'])."&search=".urlencode(rtrim($searchedText))."&type=1&mod=".MODULE."&nflag=".$params['national']."&stflag=".$params['nationalType']."&dflag=&debug=0";
		$curlParams['formate'] = 'basic';
		$singleCheck	=	Utility::curlCall($curlParams);
		$autosuggest_array	= json_decode($singleCheck,true);
		if($autosuggest_array['errors']['code'] == 0){
			$autosuggest_array['results']	=	$autosuggest_array['results']['data'];
			$catresult = json_encode($autosuggest_array);
		}
		return $catresult;
	}
	
	public function getCatData() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		
		$paramsSend	=	array();
		$paramsSend['cid']	=	$params['srchId'];	
		$paramsSend['str']	=	$params['srchStr'];	
		$paramsSend['data_city']	=	$params['srchCity'];	
		$paramsSend['stp']	=	$params['stp'];	
		$paramsSend['ntp']	=	$params['ntp'];	
		$paramsSend['nid']	=	"";	
		$paramsSend['grp']	=	"";	
		$paramsSend['off']	=	"";	
		$paramsSend['num']	=	"";	
		$paramsSend['odr']	=	"";
		$paramsSend['mrkonly']	=	1;
		$paramsSend['bfcignore']	=	1;	
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/relatedCategory.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function getExistingCatsContract() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];
		$paramsSend['data_city']	=	$params['srchCity'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/contract_category_info.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function submitCategories() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$catString	=	'';
		$exisitString	=	'';
		foreach($params['catArr'] as $key=>$value) {
			foreach($value as $key2=>$value2) {
				$catString	.=	$value2.'|P|';
			}
		}
		
		foreach($params['existArr'] as $key=>$value) {
			$exisitString	.=	$value.'|P|';
		}
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['catlist']		=	$catString;	
		$paramsSend['existlist']	=	$exisitString;
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/populate_category_temp_data.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	public function check_attribute_present(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['ucode']		=	$params['ucode'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['action']		=	'check_attr';	
		
		$curlParams = array();
		//$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';
		$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';
		//$curlParams['url'] = 'http://saritapc.jdsoftware.com/jdbox/services/attributes_page_new.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$attrHtml	=	Utility::curlCall($curlParams);
		return $attrHtml;
	}
	public function attributesPage() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['ucode']		=	$params['ucode'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['action']		=	'fetchattr';	
		
		$curlParams = array();
		//$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';
		$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';
		//$curlParams['url'] = 'http://saritapc.jdsoftware.com/jdbox/services/attributes_page_new.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$attrHtml	=	Utility::curlCall($curlParams);
		return $attrHtml;
	}
	
	public function updateAttributes() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['action']		=	'updateattr';
		$paramsSend['attrTaken']	=	$params['attrTaken'];
		$paramsSend['attributes']	=	$params['attributes'];
		$paramsSend['unique_code_str']	=	$params['unique_code_str'];
		$paramsSend['validateData']	 =	$params['validateData'];
		
		$curlParams = array();
		//$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';
		$curlParams['url'] = JDBOX_API.'/services/attribute_page.php';

		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$attrHtml	=	Utility::curlCall($curlParams);
		return $attrHtml;
	}
	
	public function catPreviewData() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/category_page.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function findMultiParentage() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['removed_catid']		=	$params['removecatidlist'];
		$paramsSend['catid_list']		=	$params['allcatidlist'];
		$paramsSend['rquest']		=	$params['rquest'];
		$paramsSend['companyname']		=	$params['companyname'];
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['uname']		=	$params['uname'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/multiparentage_check.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function sendCatsForModeration() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['removed_catid']		=	$params['removecatidlist'];
		$paramsSend['catid_selected']		=	$params['allcatidlist'];
		$paramsSend['rquest']		=	$params['rquest'];
		$paramsSend['companyname']		=	$params['companyname'];
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['uname']		=	$params['uname'];
		$paramsSend['cat_for_moderation']		=	$params['CatidToBeModerated'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/multiparentage_check.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function checkCatRestriction() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']			=	$params['parentid'];	
		$paramsSend['data_city']		= 	$params['data_city'];	
		$paramsSend['module']			= 	MODULE;
		$paramsSend['remove_catidlist']	= 	$params['removecatidlist'];
		$paramsSend['all_catidlist']	=	$params['allcatidlist'];
		$paramsSend['ucode']			=	$params['ucode'];
		//$paramsSend['page']	= 'CatPreviewPage';
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/category_restriction_check.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	
	public function isPhoneSearchCampaign(){
		
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$dbObjFin	=	new DB($this->db['db_finance']);
		$retArr	=	array();
		
		$phonesearch_flag = 0;
		 $sqlChkPhoneSrchShadow = "SELECT parentid FROM tbl_companymaster_finance_shadow WHERE parentid = '".$params['parentid']."' AND campaignid IN ('1','2') LIMIT 1";
		$resChkPhoneSrchShadow =  $dbObjFin->query($sqlChkPhoneSrchShadow);
		if($resChkPhoneSrchShadow && $dbObjFin->numRows($resChkPhoneSrchShadow)>0){
			$phonesearch_flag = 1;
		}else{
		 $sqlChkPhoneSrchMain = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid = '".$params['parentid']."' AND campaignid IN ('1','2') LIMIT 1";
			$resChkPhoneSrchMain = $dbObjFin->query($sqlChkPhoneSrchMain);
			if($resChkPhoneSrchMain && $dbObjFin->numRows($resChkPhoneSrchMain)>0){
				$phonesearch_flag = 1;
			}
		}
		return $phonesearch_flag;
		
	}
	
	public function submitCatPreview() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$dbObjLocal	=	new DB($this->db['db_local']);
		$retArr	=	array();
		
		$data		=	array();
		$data_arr	=	array();
		$data['parentid']	  	=	  $params['parentid'];
		$data['data_city']	  	=   $params['data_city'];
		$data['module']       	=   MODULE;
		$data['ucode']     	  	=   $params['ucode'];
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']			=	$params['parentid'];	
		$paramsSend['data_city']		=	$params['data_city'];	
		$paramsSend['module']			=	MODULE;
		$paramsSend['ucode']			=	$params['ucode'];
		$paramsSend['remove_catidlist']	=	$params['removecatidlist'];
		$paramsSend['movie_timing']		=	$params['movie_timing'];
		$paramsSend['paid_auth']		=	$params['paid_auth'];
		$paramsSend['paid_nonauth']		=	$params['paid_nonauth'];
		$paramsSend['nonpaid_auth']		=	$params['nonpaid_auth'];
		$paramsSend['nonpaid_nonauth']	=	$params['nonpaid_nonauth'];
		$paramsSend['nonpaid_catidlist']	=	$params['nonpaid_catlist'];
		//print_r($paramsSend);

		if($params['removecatidlist'] != ''){
			$removecat = explode(',',$params['removecatidlist']);
			foreach($removecat as $recat){
				$params['allpaidcat'] = str_replace($recat.',',"",$params['allpaidcat']);
				$params['allnonpaidcat'] =str_replace($recat.',',"",$params['allnonpaidcat']);
			}
		}
		
		if($params['nonpaid_catlist'] != ''){
			$newnonpaid = explode(',',$params['nonpaid_catlist']);
			foreach($newnonpaid as $npcat){
				$params['allpaidcat'] = str_replace($npcat.',',"",$params['allpaidcat']);
				$params['allnonpaidcat'] .= $npcat.','; 
			}
		}
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/catpreview_submit.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		
		return $singleCheck;
		
	}
	
	
	Public function save_dc_cat(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		GLOBAL $parseConf;
		
		if($params['removecatidlist'] != ''){
			$removecat = explode(',',$params['removecatidlist']);
			foreach($removecat as $recat){
				$params['allpaidcat'] = str_replace($recat.',',"",$params['allpaidcat']);
				$params['allnonpaidcat'] =str_replace($recat.',',"",$params['allnonpaidcat']);
			}
		}
		
		if($params['nonpaid_catlist'] != ''){
			$newnonpaid = explode(',',$params['nonpaid_catlist']);
			foreach($newnonpaid as $npcat){
				$params['allpaidcat'] = str_replace($npcat.',',"",$params['allpaidcat']);
				$params['allnonpaidcat'] .= $npcat.','; 
			}
		}
		
		$curlParams_dc = array();
		$paramsSend_dc = array();
		$paramsSend_dc['data'] = array();
		$paramsSend_dc['data']['parentid'] = $params['parentid'];
		$paramsSend_dc['data']['catidlineage'] = rtrim($params['allpaidcat'],",");
		$paramsSend_dc['data']['catidlineage_nonpaid'] = rtrim($params['allnonpaidcat'],",");
		$paramsSend_dc['disposition'] = 0;
		$paramsSend_dc['city']= $params['data_city'];
		$paramsSend_dc['flag']= 1;
		$paramsSend_dc['empcode']=$params['ucode'] ;
		$paramsSend_dc['request']="catid" ;
		
		$curlParams_dc['url'] = "http://".DC_API_NEW."/data_correction/jsonInsert.php";;
		$curlParams_dc['formate'] = 'basic';
		$curlParams_dc['method'] = 'post';
		$curlParams_dc['postData'] =  "comp_arr=".json_encode($paramsSend_dc); 
		if($parseConf['servicefinder']['remotecity'] == 1){
			$dc_response	=	Utility::curlCall($curlParams_dc);
		}
		
	}
	
	
	public function categoryInstantLive(){
		
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$dbObjLocal	=	new DB($this->db['db_local']);
		
		$flag	=	0;
		
		$selectFlag		=	"SELECT flag from d_jds.tbl_correct_incorrect where parentid='".$params['parentid']."'";
		$con			=	$dbObjLocal->query($selectFlag);
		$data			=	$dbObjLocal->fetchData($con);
		
		if($data['flag']	==	1){
			$flag	=	3;
		}else{
			$flag	=	2;
		}
		//~ //Instant live
		
		$insert_cor_incorrect		=	"INSERT INTO d_jds.tbl_correct_incorrect SET parentid	=	'".$params['parentid']."',
																						entry_date	=	NOW(),
																						empcode		=	'".$params['ucode']."',
																						data_city	=	'".$params['data_city']."',
																						flag		=	'".$flag."'
												ON DUPLICATE KEY UPDATE
																						entry_date	=	NOW(),
																						empcode		=	'".$params['ucode']."',
																						flag		=	'".$flag."',
																						data_city	=	'".$params['data_city']."'";
																						
			
		$con_cor_incor			=	$dbObjLocal->query($insert_cor_incorrect);
		
		
		$data		=	array();
		$data_arr	=	array();
		$data['parentid']	      =	  $params['parentid'];
		$data['data_city']	  	  =   $params['data_city'];
		$data['module']       	  =   MODULE;
		$data['usercode']     	  =  $params['ucode'];
		$data['username']     	  =   $params['uname'];
	   
		$data_arr['url'] 		  =  JDBOX_API.'/services/savenonpaid_jda.php'; 
		$data_arr['formate']      	  = 'basic';
		$data_arr['method'] 	 	  = 'post';
		$data_arr['headerJson']       = 'json';
		$data_arr['postData'] 	  	  =   json_encode($data);
		$result					  	  =	  Utility::curlCall($data_arr);
		
		
		
		if($result['error']['code']	==	0){
			
			$data		=	array();
			$data_arr	=	array();
			$data['parentid']	  =	  $params['parentid'];
			$data['data_city']	  =   $params['data_city'];
			$data['module']       =   'MODULE';
			$data['ucode']     	  =   $params['ucode'];
			$data_arr['postData'] =   http_build_query($data);
			$data_arr['method'] = 'post';
			$data_arr['url'] =  JDBOX_API.'/services/instant_live.php'; // change to constant
			$result1	=	Utility::curlCall($data_arr);
			
			
		}
		
		return $result;
	}

	public function searchPlusCampFinder() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/searchplus_eligibility_check.php ';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function docHospRedirectCheck() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['vertical_name']=	$params['vertical_id'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/doc_hosp_redirection_check.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function othersVerticalRedirect() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['module']		=	MODULE;
		$paramsSend['vertical_name']=	$params['others_vertical_name'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/others_vertical_redirection_check.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function categoryResetAPI() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);

		$paramsSend = array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$curlParams = array();
		$curlParams['url'] = JDBOX_API."/services/reset_categories.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	 public function getnationalflag(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$paramsSend = array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['action']		=	'isnationallisting';	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$curlParams = array();
		$curlParams['url'] = JDBOX_API."/services/nationallisting.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
		
	}
	public function fetchtempdatanational(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend = array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['version']		=	$params['version'];	
		$paramsSend['action']		=	'fetchtempdata';	
		$paramsSend['module']		=	MODULE;
		$curlParams = array();
		$curlParams['url'] = JDBOX_API."/services/nationallisting.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		
		return $singleCheck;
		
	}
	
	public function calcupdatedatanational(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend = array();
		$paramsSend['parentid']				=	$params['parentid'];	
		$paramsSend['data_city']			=	$params['data_city'];	
		$paramsSend['budget']				=	$params['budget'];	
		$paramsSend['tenure']				=	$params['tenure'];		
		$paramsSend['recalculate_flag']		=	$params['recalculate_flag'];		
		$paramsSend['action']				=	'calcupdatedata';	
		$paramsSend['module']				=	MODULE;
		$curlParams = array();
		$curlParams['url'] = JDBOX_API."/services/nationallisting.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		
		return $singleCheck;
		
	}
	
	public function removeLocalforNational(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend = array();
		$paramsSend['parentid']				=	$params['parentid'];	
		$paramsSend['data_city']			=	$params['data_city'];			
		$paramsSend['action']				=	'removeLocalforNational';	
		$paramsSend['module']				=	MODULE;
		$curlParams = array();
		$curlParams['url'] = JDBOX_API."/services/nationallisting.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		
		return $singleCheck;
		
	}

	public function submitRelevantCat(){
		header('Content-Type: application/json');
		
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$catString	=	'';
		$ecatString	=	'';
		
		$unique_catArr =array_unique($params['catArr']);
		$unique_ecatArr =array_unique($params['ecatArr']);
		
		foreach($unique_catArr as $key=>$value) {
			$catString	.=	$value.'|P|';
		}
		
		foreach($unique_ecatArr as $key=>$value) {
			$ecatString	.=	$value.'|P|';
		}
		
		$retArr	=	array();
		$paramsSend	=	array();
		$paramsSend['parentid']	=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];	
		$paramsSend['catlist']		=	$catString;	 
		$paramsSend['e_catlist']	=	$ecatString;	 
		$paramsSend['save_for']		=	$params['save_for'];
		$paramsSend['ucode']		=	$params['ucode'];	 
		$paramsSend['action']		=	1;	 
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/category_service.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function getPopularCat(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	 
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$params['ucode'];
		$paramsSend['action']		=	$params['category_type'];
		$paramsSend['stp']			=	$params['stp'];
		$paramsSend['ntp']			=	$params['ntp'];
		$paramsSend['ucode']		=	$params['ucode'];	 
		
		$curlParams['url'] = JDBOX_API."/services/category_service.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend);
		$singleCheck	=	Utility::curlCall($curlParams);
		//~ echo "<pre>"; print_r($singleCheck);
		return $singleCheck;
	}
	
	public function fetchEditListingEntry(){
		header('Content-Type: application/json');
		$dbObjTme	=	new DB($this->db['db_tme']);
		$params		=	json_decode(file_get_contents('php://input'),true);
		//~ $paramsGET	=	array_merge($_POST,$_GET);
		$retArr= array();
		$get_data					=	"SELECT * FROM d_jds.tbl_correct_incorrect WHERE parentid='".$params['parentid']."' ORDER BY entry_date DESC LIMIT 1";
		$con_get_data				=	$dbObjTme->query($get_data);	
		$num						=	$dbObjTme->numRows($con_get_data);
		$flag						=	$dbObjTme->fetchData($con_get_data);
		if($num > 0) {
			$retArr['num']			=	$num;
			$retArr['flag']			=	$flag['flag'];
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}
		else
		{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function fetchEditListingData(){
		header('Content-Type: application/json');
		$dbObjTme	=	new DB($this->db['db_tme']);
		$params		=	json_decode(file_get_contents('php://input'),true);
		//~ $paramsGET	=	array_merge($_POST,$_GET);
		$retArr= array();
		$get_data				=	"SELECT * FROM d_jds.tbl_companydetails_edit WHERE parentid='".$params['parentid']."' ORDER BY entry_date DESC LIMIT 1";
		$con_get_data			=	$dbObjTme->query($get_data);	
		$num					=	$dbObjTme->numRows($con_get_data);
		if($num > 0) {
			$json_edited_data	=	$dbObjTme->fetchData($con_get_data);
			$retArr['data']		=	json_decode($json_edited_data['edited_data']);
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Data Found';
		}
		else
		{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data Not Found';
		}
		return json_encode($retArr);
	}
	
	public function docVerticalCheck(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$curlParams = array();
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$params['parentid'];	
		$paramsSend['data_city']	=	$params['data_city'];	 
		$paramsSend['ucode']		=	$params['ucode'];	 
		$paramsSend['module']		=	MODULE;	
		
		$curlParams['url'] = JDBOX_API."/services/doc_vertical_check.php";
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend);
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
}
