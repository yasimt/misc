<?php
class CampaignInfo_Model extends Model {
	function __construct() {
		ini_set('memory_limit', '-1');
        parent::__construct(); 
        $this->mongo_obj = new MongoClass();
    }
    
    public function getBestCampaignInfo() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$getExistingCats	=	json_decode($this->getAllCat($params['parentid'],$params['data_city'],$params['empcode']),1);
		$catString	=	"";
		if(isset($getExistingCats['data']['TEMP']['PAID']) && is_array($getExistingCats['data']['TEMP']['PAID'])) {
			foreach($getExistingCats['data']['TEMP']['PAID'] as $key=>$value) {
				$catString	.=	$key.",";
			}
		}
		
		if(isset($getExistingCats['data']['TEMP']['NONPAID']) && is_array($getExistingCats['data']['TEMP']['NONPAID'])) {
			foreach($getExistingCats['data']['TEMP']['NONPAID'] as $key2=>$value2) {
				$catString	.=	$key2.",";
			}
		}
		$catString	=	substr($catString,0,-1);
		if(isset($params['customPackage'])) {
			if($params['customPackage'] == 1) {
				$data_city			=	$params['data_city'];	
				$parentid			=	$params['parentid'];
				//$this->setAreaPincodeInfo($params['pincode'],$data_city,$parentid);
			}
		}
		
		if(isset($params['onlypackageprice'])) {
			$initBudget	=	json_decode($this->budgetInitialize($params['parentid'],$params['data_city'],MODULE,$params['empcode'],$params['username'],$params['onlypackageprice']),1);
		}else
		{
			$initBudget	=	json_decode($this->budgetInitialize($params['parentid'],$params['data_city'],MODULE,$params['empcode'],$params['username']),1);
		}
		$dataCompCompCatPin	=	array();
		if($params['tabNo'] == '6') {
			$dataCompCompCatPin	=	json_decode($this->compareCatPin($initBudget['data']['version'],$params['parentid']),1);
		} else {
			$dataCompCompCatPin['error']['code']	=	0;
		}
		
		if($initBudget['error_code']	==	0) {
			/*call to update pincode json column if column is blank*/
			$curlParams = array();
			$curlParams['url'] = JDBOX_API.'/services/pincodeSelection.php?data_city='.urlencode($params['data_city']).'&parentid='.$params['parentid'].'&module='.MODULE."&action=setlisttojson";
			$curlParams['formate'] = 'basic';
			$curlParams['method'] = 'get';
			$singleCheckPinJson	= json_decode(Utility::curlCall($curlParams),1);
			
			$retArr	=	array();
			$paramsSend	=	array();
			
			$curlParams = array();
			
			if($params['tabNo'] == '3') {
				if($params['flexiVal'] == 0) {
					$curlParams['url'] = JDBOX_API.'/services/budgetDetails.php?data_city='.urlencode($params['data_city']).'&tenure='.$params['tenure'].'&parentid='.$params['parentid'].'&mode='.$params['tabNo'].'&option='.$params['optNo'].'&ver='.$initBudget['data']['version'].'&module='.MODULE."&cpf=0&pbgtyrly=0";	
				} else {
					$curlParams['url'] = JDBOX_API.'/services/budgetDetails.php?data_city='.urlencode($params['data_city']).'&tenure='.$params['tenure'].'&parentid='.$params['parentid'].'&mode='.$params['tabNo'].'&option='.$params['optNo'].'&ver='.$initBudget['data']['version'].'&module='.MODULE."&cpf=1&pbgtyrly=".$params['flexiVal'];
				}
			} else {
				$params['tabNo'] = ($params['exact_renewal'] && $params['tabNo'] == '6') ? 4 : $params['tabNo'];
				$curlParams['url'] = JDBOX_API.'/services/budgetDetails.php?data_city='.urlencode($params['data_city']).'&tenure='.$params['tenure'].'&parentid='.$params['parentid'].'&mode='.$params['tabNo'].'&option='.$params['optNo'].'&onlyExclusive='.$params['only_exclusive'].'&exactRenewal='.$params['exact_renewal'].'&ver='.$initBudget['data']['version'].'&module='.MODULE;
			}
			//print_r($params);die;
			
			
			$curlParams['formate'] = 'basic';
			$curlParams['method'] = 'post';
			$curlParams['headerJson'] = 'json';
			$curlParams['postData'] = json_encode($paramsSend); 
			$singleCheck	= json_decode(Utility::curlCall($curlParams),1);
			
			$log_data = array();
			$post_data = array();
			
			$log_data['url'] = LOG_URL.'logs.php';
			
			$post_data['ID']         = $params['parentid'];                
			$post_data['PUBLISH']    = 'TME';         	
			$post_data['ROUTE']      = 'getBestCampaignInfo';   		
			$post_data['CRITICAL_FLAG']  = 1 ;			
			$post_data['MESSAGE']        = 'getBestCampaignInfo response';	
			$post_data['DATA']['url'] = 	JDBOX_API.'/services/budgetDetails.php?data_city='.urlencode($params['data_city']).'&tenure='.$params['tenure'].'&parentid='.$params['parentid'].'&mode='.$params['tabNo'].'&option='.$params['optNo'].'&ver='.$initBudget['data']['version'];
			$post_data['DATA_JSON']['response']	 = 	$singleCheck;
			
			$log_data['method'] = 'post';
			$log_data['formate'] 	= 	'basic';
			$log_data['timeout'] = 1;
			$log_data['postData'] 	= 	 http_build_query($post_data);
			$log_res	=	Utility::curlCall($log_data);
			
			$singleCheck['dataCompare']	=	$dataCompCompCatPin;
		} else {
			$singleCheck['error']['code']	=	1;
		}
		return json_encode($singleCheck);
	}

	public function getVersion($data_city ='', $parentid = '',$usercode = '') {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);

		$paramsSendVersion	=	array();
		
		if($data_city == ''){
			$paramsSendVersion['data_city']	=	$params['data_city'];	
		}else {
			$paramsSendVersion['data_city'] = $data_city;
		}
		
		if($parentid == ''){
			$paramsSendVersion['parentid']	=	$params['parentid'];
		}else {
			$paramsSendVersion['parentid']  = $parentid;
		}
		
		if($usercode == ''){
			$paramsSendVersion['usercode']	=	$params['usercode'];
		}else {
			$paramsSendVersion['usercode']  = $usercode;
		}
		
		
		$paramsSendVersion['action']			=	'getversion';	
		$paramsSendVersion['module']			=	MODULE;
		
		$curlParams = array();	
		$curlParams['url'] 			= JDBOX_API.'/services/versioninit.php';
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	= json_encode($paramsSendVersion); 
		$versionVal					= Utility::curlCall($curlParams);
		return $versionVal;
	}
	
	
	public function submit_flexi_value(){
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$res_arr = array();
		$initBudget	=	json_decode($this->budgetInitialize($params['parentid'],$params['data_city'],MODULE,$params['empcode'],$params['username']),1);
		
		$dbobj	=	new DB($this->db['db_finance_budget']);
		if($initBudget['error_code'] == 0 && $initBudget['data']['version'] != null || $initBudget['data']['version'] != 0){
			$curlParams['url'] = JDBOX_API.'/services/finance_display.php?parentid='.$params['parentid'].'&version='.$initBudget['data']['version'].'&action=46&module=me&data_city='.$params['data_city'].'&usercode='.$params['empcode'].'&price_arr='.$params['price_arr'].'&flexi_duration=3650';
			$paramsSend	=	array();
			$curlParams['formate'] = 'basic';
			$singleCheck	=	Utility::curlCall($curlParams);
			$singleCheck = json_decode($singleCheck,1);
			if($singleCheck['error_code'] == 0){
				$curlParams = array();
				$curlParams['url'] = JDBOX_API."/services/packagepincatupdt.php?data_city=".$params['data_city']."&parentid=".$params['parentid']."&action=packbudgetcalbypin&username=".urlencode($params['username'])."&usercode=".$params['empcode']."&source=tme&version=".$initBudget['data']['version']."&module=tme";
				$paramsSend	=	array();
				$curlParams['formate'] = 'basic';
				$singleCheck = Utility::curlCall($curlParams);
				$budgetupdateres = json_decode($singleCheck,1);		
				if($budgetupdateres['error']['code'] == 0){
					$res_arr['error_code'] = 0;
					$res_arr['error_msg'] = "success";
				}else {
					$res_arr['error_code'] = 1;
					$res_arr['error_msg'] = "budget API failed ";
				}
			}else {
				$res_arr['error_code'] = 1;
				$res_arr['error_msg'] = "Budget insertion failed";
			}
		}else{
			$res_arr['error_code'] = 1;
			$res_arr['error_msg'] = "Budget init api failed";
		}
		return json_encode($res_arr);
	}
	
	private function budgetInitialize($parentid,$data_city,$module,$ucode,$username,$onlypackageprice=null) {
		$curlParams = array();
		
		if($onlypackageprice)
		{
			$str='&onlypackageprice='.$onlypackageprice;
		}
		
		$curlParams['url'] = JDBOX_API.'/services/budgetinit.php?parentid='.trim($parentid).'&data_city='.urlencode($data_city).'&module='.MODULE.'&usercode='.$ucode.'&username='.urlencode($username).$str;
		
		$paramsSend	=	array();
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function setBudgetData() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend	=	array();
		
		$paramsSend['action']	=	'submitbudget';
		$paramsSend['parentid']	=	$params['parentid'];
		$paramsSend['module']	=	MODULE;
		$paramsSend['usercode']	=	$params['empcode'];
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['budgetjson']	=	$params['dataArr'];
		$paramsSend['duration']		=	'365';
		$paramsSend['package_10dp_2yr']	=	$params['package_10dp_2yr'];
		
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/budgetsubmit.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		
		
		$log_data = array();
		$post_data = array();
		
		$log_data['url'] = LOG_URL.'logs.php';
		
		$post_data['ID']         = $params['parentid'];                
		$post_data['PUBLISH']    = 'TME';         	
		$post_data['ROUTE']      = 'budgetsubmit';   		
		$post_data['CRITICAL_FLAG']  = 1 ;			
		$post_data['MESSAGE']        = 'budgetsubmit param response';	
		$post_data['DATA']['url'] = 	 JDBOX_API.'/services/budgetsubmit.php';		
		$post_data['DATA_JSON']['paramssubmited']	 = 	$paramsSend;
		$post_data['DATA_JSON']['response']	 = 	$singleCheck;
		
		$log_data['method'] = 'post';
		$log_data['formate'] 	= 	'basic';
		$log_data['postData'] 	= 	 http_build_query($post_data);
		$log_res	=	Utility::curlCall($log_data);

		return $singleCheck;
	}
	
	public function checkFlexiCat() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend	=	array();
		
		$paramsSend['parentid']	=	$params['parentid'];
		$paramsSend['module']	=	MODULE;
		$paramsSend['usercode']	=	$params['empcode'];
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['version']	=	$params['version'];
		$paramsSend['action']	=	32;
		
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/finance_display.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	private function getAllCat($parentid,$data_city,$ucode) {
		$paramsSend	=	array();
		$paramsSend['parentid']		=	$parentid;
		$paramsSend['srchCity']	=	$data_city;
		$paramsSend['module']		=	MODULE;
		$paramsSend['ucode']		=	$ucode;
		
		$curlParams = array();
		$curlParams['url'] = SERVICE_IP.'/categoryInfo/getExistingCatsContract';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function getDataBudgetFinal() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend	=	array();
		
		$paramsSend['action']	=	'getbudget';
		$paramsSend['parentid']	=	$params['parentid'];
		$paramsSend['module']	=	MODULE;
		$paramsSend['usercode']	=	$params['ucode'];
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['duration']		=	'365';
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/budgetsubmit.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		$retArr	=	array();
		$retArr['error_code']	=	0;
		$retArr['data']	=	json_decode($singleCheck,1);
		
		$version  = json_decode($this->getVersion($params['data_city'],$params['parentid'],$params['ucode']),1);
		
		$log_data = array();
		$post_data = array();
		
	    $log_data['url'] = LOG_URL.'logs.php';
	    
	    $post_data['ID']         = $params['parentid'];                
		$post_data['PUBLISH']    = 'TME';         	
		$post_data['ROUTE']      = 'BudgetFinal';   		
		$post_data['CRITICAL_FLAG']  = 1 ;			
		$post_data['MESSAGE']        = 'getDataBudgetFinal response';	
		$post_data['DATA_JSON']['params'] =  $curlParams['postData'];					
		$post_data['DATA']['url'] = 	JDBOX_API.'/services/budgetsubmit.php';
		$post_data['DATA']['version'] = $version['version'];
		$post_data['DATA_JSON']['response']	 = 	$singleCheck;
		
		$log_data['method'] = 'post';
		$log_data['formate'] 	= 	'basic';
		$log_data['postData'] 	= 	 http_build_query($post_data);
		$log_res	=	Utility::curlCall($log_data);
		
		return json_encode($retArr);
	}
	
	public function getExistingInventory() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend	=	array();
		
		$paramsSend['parentid']	=	$params['parentid'];
		$paramsSend['data_city']	=	$params['data_city'];
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/showInv.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function getCampaignMaster() {
		
		$dbObjLocal	=	new DB($this->db['db_finance']);
		$retArr		=	array();
		
		$query		=	"SELECT campaignid,campaignName FROM payment_campaign_master";
		$con		=	$dbObjLocal->query($query);
		$num		=	$dbObjLocal->numRows($con);
		if($num	> 0) {
			while($data	=	$dbObjLocal->fetchData($con)) {
				$retArr['results'][$data['campaignid']]	=	$data['campaignName'];
			}
			$retArr['error_code']	=	0;
		} else {
			$retArr['error_code']	=	1;
		}
		return json_encode($retArr);
	}
	
	private function compareCatPin($version,$parentid) {
		$dbObjFinance	=	new DB($this->db['db_finance']);
		
		$retArr			=	array();
		$finalRetArr	=	array();
		$queryOldSet	=	"SELECT catid,pincode FROM tbl_bidding_details WHERE parentid = '".$parentid."'";
		$conOldSet		=	$dbObjFinance->query($queryOldSet);
		$numOldSet		=	$dbObjFinance->numRows($conOldSet);
		if($numOldSet == 0) {
			$queryOldSet	=	"SELECT catid,pincode FROM tbl_bidding_details_expired WHERE parentid = '".$parentid."'";
			$conOldSet		=	$dbObjFinance->query($queryOldSet);
			$numSetShow		=	$dbObjFinance->numRows($conOldSet);
		} else {
			$numSetShow		=	$numOldSet;
		}
		if($numSetShow > 0) {
			$retArr['oldSet']['pincodes']	=	array();
			$retArr['oldSet']['pincodesTemp']	=	array();
			while($resOld	=	$dbObjFinance->fetchData($conOldSet)) {
				$retArr['oldSet'][$resOld['catid']][]	=	$resOld['pincode'];
				$retArr['completeSet']['cat_data'][$resOld['catid']][]	=	$resOld['pincode'];
				if(!in_array($resOld['pincode'],$retArr['oldSet']['pincodesTemp'])) {
					$retArr['oldSet']['pincodesTemp'][]	=	$resOld['pincode'];
					$pinStr	.=	$resOld['pincode'].',';
				}
			}
			
			$pinConfArr	=	$this->get_pincode_details(substr($pinStr,0,-1));
			foreach($retArr['oldSet']['pincodesTemp'] as $key=>$value) {
				if(array_key_exists($value,$pinConfArr)) {
					$retArr['oldSet']['pincodes'][]	=	$value;
					$retArr['completeSet']['pincodes'][]	=	$value;
				}
			}
			
			$dbObjFinanceBudget	=	new DB($this->db['db_finance_budget']);
			$queryNewSet	=	"SELECT category_list,pincode_list FROM tbl_bidding_details_summary WHERE parentid = '".$parentid."' AND version = '".$version."'";
			$conNewSet		=	$dbObjFinanceBudget->query($queryNewSet);
			$resNewSet		=	$dbObjFinanceBudget->fetchData($conNewSet);
			
			$expNewSetCats	=	explode(",",$resNewSet['category_list']);
			$expNewSetPins	=	explode(",",$resNewSet['pincode_list']);
			foreach($expNewSetCats as $value) {
				$retArr['newSet'][$value]	=	$expNewSetPins;
				if(!array_key_exists($value,$retArr['completeSet'])) {
					$retArr['completeSet']['cat_data'][$value]	=	$expNewSetPins;
				}
			}
			
			foreach($expNewSetPins as $value2) {
				if(!in_array($value2,$retArr['completeSet']['pincodes'])) {
					$retArr['completeSet']['pincodes'][]	=	$value2;
				}
			}
			
			$matchKeysArr1	=	array_diff_key($retArr['oldSet'],$retArr['newSet']);
			$matchKeysArr2	=	array_diff_key($retArr['newSet'],$retArr['oldSet']);
			
			$catString	=	"";
			
			$countError	=	0;
			$pinStr	=	"";
			foreach($retArr['completeSet']['cat_data'] as $keyCat=>$valCat) {
				$catString		.=	$keyCat.',';
				if(array_key_exists($keyCat,$matchKeysArr1)) {
					$finalRetArr['cat_data'][$keyCat]['error_code']	=	1;
					$finalRetArr['cat_data'][$keyCat]['error_msg']	=	"Category Removed";
					$countError++;
				} else if(array_key_exists($keyCat,$matchKeysArr2)) {
					$finalRetArr['cat_data'][$keyCat]['error_code']	=	2;
					$finalRetArr['cat_data'][$keyCat]['error_msg']	=	"Category added";
					$countError++;
				} else {
					$finalRetArr['cat_data'][$keyCat]['error_code']	=	0;
					$finalRetArr['cat_data'][$keyCat]['error_msg']	=	"Category Mismatch not found";
				}
			}
			
			$matchPinArr1	=	array_diff($retArr['oldSet']['pincodes'],$expNewSetPins);
			$matchPinArr2	=	array_diff($expNewSetPins,$retArr['oldSet']['pincodes']);
			
			foreach($retArr['completeSet']['pincodes'] as $keyPins=>$valuePins) {
				$pinStr		.=	$valuePins.',';
				if(in_array($valuePins,$matchPinArr1)) {
					$finalRetArr['pin_data'][$valuePins]['error_code']	=	1;
					$finalRetArr['pin_data'][$valuePins]['error_msg']	=	"Pincode Removed";
					$countError++;
				} else if(in_array($valuePins,$matchPinArr2)) {
					$finalRetArr['pin_data'][$valuePins]['error_code']	=	2;
					$finalRetArr['pin_data'][$valuePins]['error_msg']	=	"Pincode Added";
					$countError++;
				} else {
					$finalRetArr['pin_data'][$valuePins]['error_code']	=	0;
					$finalRetArr['pin_data'][$valuePins]['error_msg']	=	"Pincode Mismatch Not Found";
				}
			}
			
			if($countError > 0) {
				$finalRetArr['error']['code']	=	2;
				$finalRetArr['error']['msg']	=	'Mismatch Found in Category or Pincode';
			} else {
				$finalRetArr['error']['code']	=	0;
				$finalRetArr['error']['msg']	=	'No Mismatch Found';
			}
			
			$catString	=	$catString.substr(0,-1);
			$pinStr		=	$pinStr.substr(0,-1);
			$catArr		=	$this->get_category_details($catString);
			$pinArr		=	$this->get_pincode_details($pinStr);
			
			foreach($finalRetArr['cat_data'] as $keyCat=>$valCat) {
				$finalRetArr['cat_data'][$keyCat]['catname']	=	$catArr[$keyCat]['cnm'];
			}
			
			foreach($finalRetArr['pin_data'] as $keyPin=>$valPin) {
				$finalRetArr['pin_data'][$keyPin]['area_name']	=	$pinArr[$keyPin]['anm'];
			}
			
		} else {
			$finalRetArr['error']['code']	=	3;
			$finalRetArr['error']['msg']	=	'No Entry found in Old Tables';
		}
		return json_encode($finalRetArr);
	}
	
	private function get_category_details($catids) {
		$dbObjLocal	=	new DB($this->db['db_local']);
		
        $sql="select category_name, national_catid, catid, if(business_flag=1,1,0) as b2b_flag, if((category_type&64)=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive_flag
        from tbl_categorymaster_generalinfo where catid in (".$catids.") AND biddable_type=1";
        $res_area     = 	$dbObjLocal->query($sql);
        $num_rows     = 	$dbObjLocal->numRows($res_area);
        if($res_area && $num_rows > 0){
            while($row=$dbObjLocal->fetchData($res_area)){
                $catid = $row['catid'];
                $ret_array[$catid]['cnm']         = $row['category_name'];
                $ret_array[$catid]['cid']         = $row['catid'];
                $ret_array[$catid]['nid']         = $row['national_catid'];
                $ret_array[$catid]['b2b_flag']  = $row['b2b_flag'];
                $ret_array[$catid]['bfc']          = $row['block_for_contract'];
                $ret_array[$catid]['x_flag']    = $row['exclusive_flag'];
            }
        }
        return($ret_array);
    }

    private function get_pincode_details($pincodes) {
		$dbObjLocal	=	new DB($this->db['db_local']);
		
        $sql="select pincode, substring_index(group_concat(main_area order by callcnt_perday desc SEPARATOR '#'),'#',1) as areaname
        from tbl_areamaster_consolidated_v3 where pincode in (".$pincodes.") group by pincode";
        $res_area    	 = $dbObjLocal->query($sql);
        $num_rows        = $dbObjLocal->numRows($res_area);
        if($res_area && $num_rows > 0) {
            while($row=$dbObjLocal->fetchData($res_area)) {
                //print_r($row);
                $pincode = $row['pincode'];
                $ret_array[$pincode]['pincode'] = $row['pincode'];
                $ret_array[$pincode]['anm']     = $row['areaname'];
            }
        }
        return($ret_array);
    } 
    
    public function releaseInventory() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		$paramsSend	=	array();
		
		$paramsSend['parentid']	=	$params['parentid'];
		$paramsSend['version']	=	$params['version'];
		$paramsSend['astate']	=	$params['astate'];
		$paramsSend['astatus']	=	2;
		$paramsSend['data_city']	=	$params['data_city'];
		$paramsSend['i_reason']		=	$params['i_reason'];
		$paramsSend['i_updatedby']		=	$params['i_updatedby'];
		$paramsSend['i_data']		=	$params['i_data'];
		$paramsSend['module']		=	MODULE;
		
		$curlParams = array();
		$curlParams['url'] = JDBOX_API.'/services/invMgmt.php';
		$curlParams['formate'] = 'basic';
		$curlParams['method'] = 'post';
		$curlParams['headerJson'] = 'json';
		$curlParams['postData'] = json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}

	private function setAreaPincodeInfo($pincode,$data_city,$parentid) {
		$retArr	=	array();
		$paramsSend	=	array();
		
		$paramsSend['data_city']			=	$data_city;	
		$paramsSend['parentid']				=	$parentid;	
		$paramsSend['pincodelist']			=	$pincode;	
		$paramsSend['action']				=	'set';	
		$paramsSend['module']				=	MODULE;	
		
		//http://prameshjha.jdsoftware.com/jdbox/services/pincodeSelection.php?data_city=mumbai&parentid=PXX22.XX22.150817135140.X5R1&pincodelist=400001&action=set
		$curlParams = array();	
		$curlParams['url'] 			= JDBOX_API.'/services/pincodeSelection.php';
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	= json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams);
		return $singleCheck;
	}
	
	public function getSetBudgetData() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$retArr	=	array();
		
		$paramsSendVersion	=	array();
		$paramsSendVersion['data_city']			=	$params['data_city'];	
		$paramsSendVersion['parentid']			=	$params['parentid'];
		$paramsSendVersion['action']			=	'getversion';	
		$paramsSendVersion['module']			=	MODULE;	
		$paramsSendVersion['usercode']			=	$params['usercode'];
		
		$curlParams = array();	
		$curlParams['url'] 			= JDBOX_API.'/services/versioninit.php';
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	= json_encode($paramsSendVersion); 
		$versionVal					= json_decode(Utility::curlCall($curlParams),true);
		
		$paramsSend	=	array();
		$paramsSend['data_city']			=	$params['data_city'];	
		$paramsSend['parentid']				=	$params['parentid'];
		$paramsSend['version']				=	$versionVal['version'];
		$paramsSend['action']				=	'getBudgetCompletejson';	
		$paramsSend['module']				=	MODULE;	
		$paramsSend['duration']				=	$params['duration'];
		$paramsSend['usercode']				=	$params['usercode'];
		
		$curlParams2 = array();	
		$curlParams2['url'] 			= JDBOX_API.'/services/budgetsubmit.php?data_city='.$paramsSend['data_city'].'&parentid='.$paramsSend['parentid'].'&version='.$paramsSend['version'].'&action=getbudgetCompletejson&module='.MODULE.'&duration='.$paramsSend['duration'].'&usercode='.$paramsSend['usercode'];
		$curlParams2['formate'] 		= 'basic';
		$curlParams2['method'] 		= 'post';
		$curlParams2['headerJson'] 	= 'json';
		$curlParams2['postData'] 	= json_encode($paramsSend); 
		$singleCheck	=	Utility::curlCall($curlParams2);
		return $singleCheck;
	}
	
	public function resetCampaign() {
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		
		$paramsSendVersion	=	array();
		$paramsSendVersion['data_city']			=	$params['data_city'];	
		$paramsSendVersion['parentid']			=	$params['parentid'];
		$paramsSendVersion['action']			=	'getversion';	
		$paramsSendVersion['module']			=	MODULE;	
		$paramsSendVersion['usercode']			=	$params['usercode'];
		
		$curlParams = array();	
		$curlParams['url'] 			= JDBOX_API.'/services/versioninit.php';
		$curlParams['formate'] 		= 'basic';
		$curlParams['method'] 		= 'post';
		$curlParams['headerJson'] 	= 'json';
		$curlParams['postData'] 	= json_encode($paramsSendVersion); 
		$versionVal					= json_decode(Utility::curlCall($curlParams),true);
		
		$paramSend	=	array();
		$paramSend['parentid']	=	$params['parentid'];
		$paramSend['data_city']	=	$params['data_city'];
		$paramSend['action']	=	'resetcampaign';
		$paramSend['module']	=	MODULE;
		$paramSend['usercode']	=	$params['usercode'];
		$paramSend['username']	=	$params['username'];
		$paramSend['version']	=	$versionVal;
		
		$curlParams2 = array();	
		$curlParams2['url'] 			= 	JDBOX_API.'/services/budgetmisc.php';
		$curlParams2['formate'] 		= 	'basic';
		$curlParams2['method'] 			= 	'post';
		$curlParams2['headerJson'] 		= 	'json';
		$curlParams2['postData'] 		= 	json_encode($paramSend); 
		$singleCheck					= 	Utility::curlCall($curlParams2);
		
		return $singleCheck;
	}
	
	public function getMinimumBudgetFlexi() {
		header('Content-Type: application/json');
		if($_REQUEST['urlFlag'] == 1) {
			$params		=	array_merge($_GET,$_POST);	
		} else {
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$dbObjLocal	=	new DB($this->db['db_tme']);

		$retArr = array();
		$catArr = array();
		
		if(MONGOUSER == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $params['parentid'];
			$mongo_inputs['data_city'] 	= $params['data_city'];
			$mongo_inputs['module']		= MODULE;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$resData 					= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$selectCats	=	"SELECT catIds FROM tbl_business_temp_data WHERE contractid = '".$params['parentid']."'";
			$conSelect	=	$dbObjLocal->query($selectCats);
			$resData	=	$dbObjLocal->fetchData($conSelect);
		}
		if(count($resData) > 0) {
			//$resData	=	$dbObjLocal->fetchData($conSelect);
			$catIdStr	=	$resData['catIds'];
			$expCatId	=	explode("|P|", $catIdStr);
			$catGetStr	=	"";
			foreach ($expCatId as $key => $value) {
				if($value != "") {
					$catGetStr	.=	$value."','";
				}
			}
			
			$paramsSend	=	array();
			$paramsSend['parentid']		=	$params['parentid'];
			$paramsSend['data_city']	=	$params['data_city'];
			$paramsSend['module']		=	MODULE;
			$paramsSend['action']		=	'get';
			$curlParams = array();
			$curlParams['url'] = JDBOX_API.'/services/pincodeSelection.php';
			$curlParams['formate'] = 'basic';
			$curlParams['method'] = 'post';
			$curlParams['headerJson'] = 'json';
			$curlParams['postData'] = json_encode($paramsSend); 
			$singleCheck	=	json_decode(Utility::curlCall($curlParams),1);
			
			$dbObjFin	=	new DB($this->db['db_finance']);
			$selectTier	=	"SELECT cat_tier,pin_tier FROM tbl_catpin_tier WHERE catId IN ('".substr($catGetStr, 0,-2).") AND pincode IN ('".str_replace(",", "','", $singleCheck['pincodelist'])."') GROUP BY cat_tier,pin_tier";
			$conSelTier	=	$dbObjFin->query($selectTier);
			if($dbObjFin->numRows($conSelTier) > 0) {
				$budgetArr	=	array();
				$i = 0;
				while($resSelTier = $dbObjFin->fetchData($conSelTier)) {
					$selTierBudget	=	"SELECT min_budget FROM tbl_tier_minbudget WHERE cat_tier = '".$resSelTier['cat_tier']."' AND pin_tier = '".$resSelTier['pin_tier']."' AND data_city = '".$params['data_city']."'";
					$conSelTierBud	=	$dbObjFin->query($selTierBudget);
					$resSelTierBud	=	$dbObjFin->fetchData($conSelTierBud);
					$budgetArr[$i]	=	$resSelTierBud['min_budget'];
					$i++;
				}
				$maxBudget	=	max($budgetArr);
				$minBudget  =	$this->getRandomMinBudget($maxBudget);
				$retArr['max_bg']	=	$minBudget;
				$retArr['errorCode']	=	0;
				$retArr['errorMsg']		=	"Mapping found returning max budget";
			} else {
				$selTierBudget	=	"SELECT min_budget FROM tbl_tier_minbudget WHERE cat_tier = '5' AND pin_tier = '5' AND data_city = '".$params['data_city']."'";
				$conSelTierBud	=	$dbObjFin->query($selTierBudget);
				$numMinBudget	=	$dbObjFin->numRows($conSelTierBud);
				if($numMinBudget > 0) {
					$resSelTierBud	=	$dbObjFin->fetchData($conSelTierBud);
					$budgetArr[0]	=	$resSelTierBud['min_budget'];
					$minBudget  =	$this->getRandomMinBudget($budgetArr[0]);
					$retArr['max_bg']	=	$minBudget;
					$retArr['errorCode']	=	0;
					$retArr['errorMsg']		=	"Mapping not found returning 5 cat and pintier budget";	
				} else {
					$retArr['max_bg']	=	30;
					$retArr['errorCode']=	0;
					$retArr['errorMsg']	=	"Mapping not found returning default budget";
				}
			}
			$sugBudArr	=	array();
			$selectBudget	=	"SELECT budget FROM tbl_catpin_suggested_budget WHERE catid IN ('".substr($catGetStr, 0,-2).") AND pincode IN ('".str_replace(",", "','", $singleCheck['pincodelist'])."')";
			$conSugBud		=	$dbObjFin->query($selectBudget);
			$numSugBudget	=	$dbObjFin->numRows($conSugBud);
			if($numSugBudget > 0) {
				$j = 0;
				while($resSugBudget = $dbObjFin->fetchData($conSugBud)) {
					$sugBudArr[$j]	=	$resSugBudget['budget'];
					$j++;
				}
				$retArr['sug_bg']	=	max($sugBudArr);
			} else {
				$retArr['sug_bg']	=	30;
			}
			if($retArr['sug_bg'] < $retArr['max_bg']) {
				$newSugBudg	=	$retArr['max_bg']+($retArr['max_bg']*0.50);
				$retArr['sug_bg']	=	$newSugBudg;
				$retArr['errorMsgSug']	=	"Suggested budget lower then minimum budget";	
			}

		} else {
			$retArr['errorCode']	=	1;
			$retArr['errorMsg']		=	"No Category Found";
		}
		return json_encode($retArr);
	}
	
	private function getRandomMinBudget($value) {
		$arrayRand = array();
		$k = 0;
		for($i=$value;$i<=($value+100);$i++) {
			$arrayRand[$k]	=	$i;
			$k++;
		}//Logic changed Taiga Id #916 GENIO Lite Group
		/* for($j=$value;$j>=($value-20);$j--) {
			$arrayRand[$k]	=	$j;
			$k++;
		} */
		$randomVal = array_rand($arrayRand,1);
		return $arrayRand[$randomVal];
	}
	
	
}
